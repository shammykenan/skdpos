<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../core/authHelper.php';

header('Content-Type: application/json');

try {
    $currentUserId = (int) ($_SESSION['user_id'] ?? 0);
    $isAdmin       = hasRole('admin');
    $isKitchen     = hasRole('kitchen');

    $sql    = "SELECT o.*, u.full_name AS cashier_name FROM orders o LEFT JOIN users u ON o.cashier_id = u.user_id";
    $params = [];

    if (!$isAdmin && !$isKitchen) {
        $sql .= " WHERE o.cashier_id = :cashier_id";
        $params[':cashier_id'] = $currentUserId;
    }

    $sql .= " ORDER BY o.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Status counts
    $countSql    = "SELECT status, COUNT(*) as cnt FROM orders";
    $countParams = [];
    if (!$isAdmin && !$isKitchen) {
        $countSql .= " WHERE cashier_id = :cashier_id";
        $countParams[':cashier_id'] = $currentUserId;
    }
    $countSql .= " GROUP BY status";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($countParams);
    $statusCounts = ['pending' => 0, 'preparing' => 0, 'completed' => 0, 'cancelled' => 0];
    $total = 0;
    foreach ($countStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $statusCounts[$row['status']] = (int) $row['cnt'];
        $total += (int) $row['cnt'];
    }

    // Attach items and addons inline
    foreach ($orders as &$order) {
        $itemStmt = $pdo->prepare("
            SELECT oi.order_item_id, oi.item_id, oi.quantity,
                   oi.unit_price, oi.subtotal, oi.status, oi.item_notes,
                   mi.item_name
            FROM order_items oi
            JOIN menu_items mi ON oi.item_id = mi.item_id
            WHERE oi.order_id = ?
            ORDER BY oi.order_item_id ASC
        ");
        $itemStmt->execute([$order['order_id']]);
        $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($items as &$item) {
            $addonStmt = $pdo->prepare("
                SELECT oia.*, a.addon_name
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
        'success'   => true,
        'data'      => $orders,
        'total'     => $total,
        'counts'    => $statusCounts,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}