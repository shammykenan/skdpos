<?php

require_once __DIR__ . '/../../config/db.php';

// ─────────────────────────────────────────────────────────────────────────────
// ORDER NUMBER
// ─────────────────────────────────────────────────────────────────────────────

function generateUniqueOrderNumber(PDO $pdo): string {
    do {
        $number = 'ORD' . date('Ymd') . rand(1000, 9999);
        $stmt   = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE order_number = ?");
        $stmt->execute([$number]);
    } while ((int) $stmt->fetchColumn() > 0);
    return $number;
}

// ─────────────────────────────────────────────────────────────────────────────
// ORDER FETCHING
// ─────────────────────────────────────────────────────────────────────────────

function getAllOrders(PDO $pdo, ?int $cashierId = null): array {
    $where  = ["o.status != 'draft'"];
    $params = [];

    if ($cashierId !== null) {
        $where[]               = "o.cashier_id = :cashier_id";
        $params[':cashier_id'] = $cashierId;
    }

    $whereSQL = 'WHERE ' . implode(' AND ', $where);
    $sql = "
        SELECT o.*, u.full_name AS cashier_name
        FROM orders o
        LEFT JOIN users u ON o.cashier_id = u.user_id
        $whereSQL
        ORDER BY o.created_at DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getOrderById(PDO $pdo, int $order_id): ?array {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = :id");
    $stmt->execute([':id' => $order_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Get full order details: items (with addons + per-item reasons), payment,
 * and void/cancel audit for display in the Order Details modal.
 *
 * Return shape:
 * [
 *   'order'       => [...],   // orders row including void_reason, cancel_reason
 *   'items'       => [...],   // order_items rows including void_reason, cancel_reason per item
 *   'payment'     => [...],   // payments row (null if not completed)
 *   'cancel_audit'=> [...],   // populated when order status = 'cancelled'
 *   'void_audit'  => [...],   // populated when order status = 'voided'
 * ]
 */
function getOrderDetails(PDO $pdo, int $order_id): ?array {
    // ── Order row (includes void_reason + cancel_reason columns) ──────────────
    $stmt = $pdo->prepare("
        SELECT o.*, u.full_name AS cashier_name, t.table_name
        FROM orders o
        JOIN   users   u ON o.cashier_id = u.user_id
        LEFT JOIN tables t ON o.table_id = t.table_id
        WHERE o.order_id = ?
        LIMIT 1
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$order) return null;

    // ── Items (includes void_reason + cancel_reason per item) ─────────────────
    $items = getOrderItems($pdo, $order_id);
    foreach ($items as &$item) {
        $item['addons'] = getOrderItemAddons($pdo, $item['order_item_id']);
    }
    unset($item);

    // ── Payment ───────────────────────────────────────────────────────────────
    $stmt = $pdo->prepare("
        SELECT p.*, u.full_name AS processed_by_name
        FROM payments p
        LEFT JOIN users u ON p.processed_by = u.user_id
        WHERE p.order_id = ?
        LIMIT 1
    ");
    $stmt->execute([$order_id]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    // ── Cancel audit ──────────────────────────────────────────────────────────
    // Source: orders.cancel_reason (written by pos_controller.php)
    // Stock note: order was pending, so no stock was deducted — nothing restored.
    $cancelAudit = null;
    if ($order['status'] === 'cancelled') {
        $cancelAudit = [
            'reason'     => $order['cancel_reason'] ?? null,
            'stock_note' => 'Order was pending — no stock had been deducted. Nothing to restore.',
        ];
    }

    // ── Void audit ────────────────────────────────────────────────────────────
    // Source: orders.void_reason (written by void_order.php)
    // Waste cost: summed from activity_logs entry for the void event.
    // Stock note: ingredients were already consumed — NOT restored.
    $voidAudit = null;
    if ($order['status'] === 'voided') {
        // Pull waste cost from the void_entire_order log entry
        $logStmt = $pdo->prepare("
            SELECT details
            FROM activity_logs
            WHERE action = 'void_entire_order'
              AND JSON_UNQUOTE(JSON_EXTRACT(details, '$.order_id')) = CAST(? AS CHAR)
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $logStmt->execute([$order_id]);
        $logRow  = $logStmt->fetch(PDO::FETCH_ASSOC);
        $logData = $logRow ? (json_decode($logRow['details'], true) ?? []) : [];

        // If voided via item-level voids (no single order-level log), sum item waste costs
        if (empty($logData)) {
            $itemLogStmt = $pdo->prepare("
                SELECT details
                FROM activity_logs
                WHERE action = 'void_order_item'
                  AND JSON_UNQUOTE(JSON_EXTRACT(details, '$.order_id')) = CAST(? AS CHAR)
            ");
            $itemLogStmt->execute([$order_id]);
            $totalWaste = 0;
            foreach ($itemLogStmt->fetchAll(PDO::FETCH_ASSOC) as $il) {
                $d = json_decode($il['details'], true) ?? [];
                $totalWaste += (float) ($d['waste_cost'] ?? 0);
            }
            $logData = ['total_waste_cost' => $totalWaste];
        }

        $voidAudit = [
            'reason'           => $order['void_reason'] ?? null,
            'total_waste_cost' => $logData['total_waste_cost'] ?? 0,
            'stock_note'       => 'Ingredients already consumed — stock NOT restored. Cost written off as waste.',
        ];
    }

    return [
        'order'        => $order,
        'items'        => $items,
        'payment'      => $payment,
        'cancel_audit' => $cancelAudit,
        'void_audit'   => $voidAudit,
    ];
}

// ─────────────────────────────────────────────────────────────────────────────
// FILTERED ORDERS
// ─────────────────────────────────────────────────────────────────────────────

function getFilteredOrders(PDO $pdo, array $options): array {
    $currentUserId = $options['current_user_id'];
    $isAdmin       = $options['is_admin'];
    $isKitchen     = $options['is_kitchen'];
    $limit         = $options['limit'];
    $offset        = $options['offset'];
    $status        = $options['status']       ?? 'all';
    $search        = $options['search']       ?? '';
    $dateFrom      = $options['date_from']    ?? '';
    $dateTo        = $options['date_to']      ?? '';
    $cashierView   = $options['cashier_view'] ?? 'all';

    $validStatuses = ['pending', 'preparing', 'ready', 'completed', 'cancelled'];
    if ($isAdmin) $validStatuses[] = 'voided';

    $where  = ["o.status != 'draft'"];
    $params = [];

    if (!$isAdmin && !$isKitchen) {
        $where[]               = "o.cashier_id = :cashier_id";
        $params[':cashier_id'] = $currentUserId;
    }
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

    $whereSQL = 'WHERE ' . implode(' AND ', $where);

    $countStmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM orders o
        LEFT JOIN tables t ON o.table_id = t.table_id
        $whereSQL
    ");
    $countStmt->execute($params);
    $filteredTotal = (int) $countStmt->fetchColumn();

    $sql = "
        SELECT o.*, u.full_name AS cashier_name, t.table_name
        FROM orders o
        LEFT JOIN users  u ON o.cashier_id = u.user_id
        LEFT JOIN tables t ON o.table_id   = t.table_id
        $whereSQL
        ORDER BY
            CASE o.status
                WHEN 'pending'   THEN 1
                WHEN 'preparing' THEN 2
                WHEN 'ready'     THEN 3
                WHEN 'completed' THEN 4
                WHEN 'cancelled' THEN 5
                WHEN 'voided'    THEN 6
            END ASC,
            CASE WHEN o.status IN ('pending', 'preparing', 'ready') THEN o.created_at END ASC,
            CASE WHEN o.status IN ('completed', 'cancelled', 'voided') THEN o.created_at END DESC
        LIMIT :limit OFFSET :offset
    ";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $val) $stmt->bindValue($key, $val);
    $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $cWhere  = ["status != 'draft'"];
    $cParams = [];
    if (!$isAdmin && !$isKitchen) { $cWhere[] = "cashier_id = :cashier_id"; $cParams[':cashier_id'] = $currentUserId; }
    if ($isAdmin && $cashierView === 'mine') { $cWhere[] = "cashier_id = :cashier_id"; $cParams[':cashier_id'] = $currentUserId; }
    if ($dateFrom !== '') { $cWhere[] = "DATE(created_at) >= :date_from"; $cParams[':date_from'] = $dateFrom; }
    if ($dateTo   !== '') { $cWhere[] = "DATE(created_at) <= :date_to";   $cParams[':date_to']   = $dateTo;   }

    $cWhereSQL    = 'WHERE ' . implode(' AND ', $cWhere);
    $countAllStmt = $pdo->prepare("SELECT status, COUNT(*) AS cnt FROM orders $cWhereSQL GROUP BY status");
    $countAllStmt->execute($cParams);

    $statusCounts = ['pending' => 0, 'preparing' => 0, 'ready' => 0, 'completed' => 0, 'cancelled' => 0];
    if ($isAdmin) $statusCounts['voided'] = 0;
    $total = 0;
    foreach ($countAllStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        if (isset($statusCounts[$row['status']])) $statusCounts[$row['status']] = (int) $row['cnt'];
        if ($row['status'] !== 'draft') $total += (int) $row['cnt'];
    }

    return [
        'orders'         => $orders,
        'filtered_total' => $filteredTotal,
        'status_counts'  => $statusCounts,
        'total'          => $total,
    ];
}

// ─────────────────────────────────────────────────────────────────────────────
// ORDER MUTATIONS
// ─────────────────────────────────────────────────────────────────────────────

function cancelOrder(PDO $pdo, int $order_id): void {
    $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE order_id = ?");
    $stmt->execute([$order_id]);
}