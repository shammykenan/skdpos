<?php

require_once __DIR__ . '/../core/init.php';

require_once __DIR__ . '/../core/itemsHelper.php';



try {

    $currentUserId = (int) ($_SESSION['user_id'] ?? 0);

    $isAdmin       = hasRole('admin');

    $isKitchen     = hasRole('kitchen');



    $limit       = max(1, (int) ($_GET['limit']       ?? 10));

    $offset      = max(0, (int) ($_GET['offset']      ?? 0));

    $status      = $_GET['status']        ?? 'all';

    $search      = trim($_GET['search']   ?? '');

    $dateFrom    = trim($_GET['date_from'] ?? '');

    $dateTo      = trim($_GET['date_to']   ?? '');

    $cashierView = $_GET['cashier_view']  ?? 'all';

    $processedBy = (int) ($_GET['processed_by'] ?? 0); // 0 = all



    $validStatuses = ['pending', 'preparing', 'ready', 'completed', 'cancelled'];



    // ── WHERE clauses ──────────────────────────────────────────────────────────

    $where  = ["o.status != 'draft'"];

    $params = [];



    // Non-admin/non-kitchen: always restrict to own orders

    if (!$isAdmin && !$isKitchen) {

        $where[]               = "o.cashier_id = :cashier_id";

        $params[':cashier_id'] = $currentUserId;

    }



    // Admin viewing only their own orders

    if ($isAdmin && $cashierView === 'mine') {

        $where[]               = "o.cashier_id = :cashier_id";

        $params[':cashier_id'] = $currentUserId;

    }



    if ($status !== 'all' && in_array($status, $validStatuses)) {

        $where[]           = "o.status = :status";

        $params[':status'] = $status;

    }



    if ($search !== '') {

        $where[]           = "(o.order_number LIKE :search OR o.customer_name LIKE :search OR t.table_name LIKE :search)";

        $params[':search'] = '%' . $search . '%';

    }



    if ($dateFrom !== '') {

        $where[]              = "DATE(o.created_at) >= :date_from";

        $params[':date_from'] = $dateFrom;

    }



    if ($dateTo !== '') {

        $where[]            = "DATE(o.created_at) <= :date_to";

        $params[':date_to'] = $dateTo;

    }



    // ── Processed by filter ────────────────────────────────────────────────────

    // Only meaningful on completed orders (payments only exist for completed ones)

    // Only admins can use this filter

    if ($isAdmin && $processedBy > 0 && $status === 'completed') {

        $where[] = "EXISTS (

            SELECT 1 FROM payments p

            WHERE p.order_id    = o.order_id

              AND p.processed_by = :processed_by

              AND p.payment_status = 'paid'

        )";

        $params[':processed_by'] = $processedBy;

    }



    $whereSQL = 'WHERE ' . implode(' AND ', $where);



    // ── Filtered total ─────────────────────────────────────────────────────────

    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM orders o $whereSQL");

    $countStmt->execute($params);

    $filteredTotal = (int) $countStmt->fetchColumn();



    // ── Main query ─────────────────────────────────────────────────────────────

    $sql = "

        SELECT o.*, u.full_name AS cashier_name, t.table_name

        FROM orders o

        LEFT JOIN users u ON o.cashier_id = u.user_id

        LEFT JOIN tables t ON o.table_id = t.table_id

        $whereSQL

        ORDER BY

            CASE o.status

                WHEN 'pending'   THEN 1

                WHEN 'preparing' THEN 2

                WHEN 'ready'     THEN 3

                WHEN 'completed' THEN 4

                WHEN 'cancelled' THEN 5

            END ASC,

            CASE WHEN o.status IN ('pending', 'preparing', 'ready') THEN o.created_at END ASC,

            CASE WHEN o.status IN ('completed', 'cancelled')        THEN o.created_at END DESC

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



    // ── Status counts (tab badges) — also exclude drafts ──────────────────────

    // Note: processed_by filter intentionally NOT applied to counts

    // because it's a sub-filter of completed only — tabs should still

    // show full counts for context

    $cWhere  = ["status != 'draft'"];

    $cParams = [];



    if (!$isAdmin && !$isKitchen) {

        $cWhere[]               = "cashier_id = :cashier_id";

        $cParams[':cashier_id'] = $currentUserId;

    }

    if ($isAdmin && $cashierView === 'mine') {

        $cWhere[]               = "cashier_id = :cashier_id";

        $cParams[':cashier_id'] = $currentUserId;

    }

    if ($dateFrom !== '') {

        $cWhere[]              = "DATE(created_at) >= :date_from";

        $cParams[':date_from'] = $dateFrom;

    }

    if ($dateTo !== '') {

        $cWhere[]            = "DATE(created_at) <= :date_to";

        $cParams[':date_to'] = $dateTo;

    }



    $cWhereSQL    = 'WHERE ' . implode(' AND ', $cWhere);

    $countAllStmt = $pdo->prepare("SELECT status, COUNT(*) AS cnt FROM orders $cWhereSQL GROUP BY status");

    $countAllStmt->execute($cParams);



    $statusCounts = ['pending' => 0, 'preparing' => 0, 'ready' => 0, 'completed' => 0, 'cancelled' => 0];

    $total        = 0;

    foreach ($countAllStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {

        if (isset($statusCounts[$row['status']])) {

            $statusCounts[$row['status']] = (int) $row['cnt'];

        }

        $total += (int) $row['cnt'];

    }



    // ── Attach items + addons via helpers ──────────────────────────────────────

    foreach ($orders as &$order) {

        $items = getOrderItems($pdo, $order['order_id']);

        foreach ($items as &$item) {

            $item['addons'] = getOrderItemAddons($pdo, $item['order_item_id']);

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