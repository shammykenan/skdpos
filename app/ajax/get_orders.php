<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../core/authHelper.php';

header('Content-Type: application/json');

try {
    $currentUserId = (int) ($_SESSION['user_id'] ?? 0);
    $isAdmin       = hasRole('admin');
    $isKitchen     = hasRole('kitchen');

    $limit  = max(1, (int) ($_GET['limit']  ?? 10));
    $offset = max(0, (int) ($_GET['offset'] ?? 0));
    $status = $_GET['status'] ?? 'all';
    $search = trim($_GET['search'] ?? '');

    $validStatuses = ['pending', 'preparing', 'ready', 'completed', 'cancelled'];

    // ── WHERE clauses ──────────────────────────────────────────────────
    $where  = [];
    $params = [];

    if (!$isAdmin && !$isKitchen) {
        $where[] = "o.cashier_id = :cashier_id";
        $params[':cashier_id'] = $currentUserId;
    }

    if ($status !== 'all' && in_array($status, $validStatuses)) {
        $where[] = "o.status = :status";
        $params[':status'] = $status;
    }

    if ($search !== '') {
        $where[] = "(o.order_number LIKE :search
                     OR o.customer_name LIKE :search
                     OR o.table_number  LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }

    $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    // ── Filtered total ─────────────────────────────────────────────────
    $countParams = $params;
    $countStmt   = $pdo->prepare("SELECT COUNT(*) FROM orders o $whereSQL");
    $countStmt->execute($countParams);
    $filteredTotal = (int) $countStmt->fetchColumn();

    // ── Main query: priority order ─────────────────────────────────────
    $sql = "
    SELECT o.*, u.full_name AS cashier_name
    FROM orders o
    LEFT JOIN users u ON o.cashier_id = u.user_id
    $whereSQL
    ORDER BY
        CASE o.status
            WHEN 'pending'   THEN 1
            WHEN 'preparing' THEN 2
            WHEN 'ready'     THEN 3
            WHEN 'completed' THEN 4
            WHEN 'cancelled' THEN 5
        END ASC,
        CASE
            WHEN o.status IN ('pending', 'preparing', 'ready')
            THEN o.created_at
        END ASC,
        CASE
            WHEN o.status IN ('completed', 'cancelled')
            THEN o.created_at
        END DESC
    LIMIT :limit OFFSET :offset
";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ── Grand totals per status ────────────────────────────────────────
    $cWhere  = [];
    $cParams = [];
    if (!$isAdmin && !$isKitchen) {
        $cWhere[] = "cashier_id = :cashier_id";
        $cParams[':cashier_id'] = $currentUserId;
    }
    $cWhereSQL    = $cWhere ? 'WHERE ' . implode(' AND ', $cWhere) : '';
    $countAllStmt = $pdo->prepare("SELECT status, COUNT(*) AS cnt FROM orders $cWhereSQL GROUP BY status");
    $countAllStmt->execute($cParams);
    $statusCounts = ['pending' => 0, 'preparing' => 0, 'ready' => 0, 'completed' => 0, 'cancelled' => 0];
    $total = 0;
    foreach ($countAllStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        if (isset($statusCounts[$row['status']])) {
            $statusCounts[$row['status']] = (int) $row['cnt'];
        }
        $total += (int) $row['cnt'];
    }

    // ── Attach items + individual statuses + addons ────────────────────
    foreach ($orders as &$order) {
        $itemStmt = $pdo->prepare("
            SELECT oi.order_item_id, oi.item_id, oi.quantity,
                   oi.unit_price, oi.subtotal, oi.status AS item_status,
                   oi.item_notes, mi.item_name
            FROM order_items oi
            JOIN menu_items mi ON oi.item_id = mi.item_id
            WHERE oi.order_id = ?
            ORDER BY oi.order_item_id ASC
        ");
        $itemStmt->execute([$order['order_id']]);
        $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($items as &$item) {
            $addonStmt = $pdo->prepare("
                SELECT oia.order_item_addon_id, oia.quantity, oia.price,
                       a.addon_name
                FROM order_item_addons oia
                JOIN addons a ON oia.addon_id = a.addon_id
                WHERE oia.order_item_id = ?
            ");
            $addonStmt->execute([$item['order_item_id']]);
            $item['addons'] = $addonStmt->fetchAll(PDO::FETCH_ASSOC);
        }
        unset($item);
        $order['items'] = $items;
    }
    unset($order);

    echo json_encode([
        'success'        => true,
        'data'           => $orders,
        'total'          => $total,
        'filtered_total' => $filteredTotal,
        'counts'         => $statusCounts,
        'has_more'       => ($offset + $limit) < $filteredTotal,
        'next_offset'    => $offset + $limit,
        'timestamp'      => date('Y-m-d H:i:s'),
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}