<?php
require_once __DIR__ . '/../../config/db.php';

// ─────────────────────────────────────────────────────────────────────────────
// ORDER NUMBER
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Generate a unique order number.
 */
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

/**
 * Get all orders excluding drafts.
 * Optionally filter by cashier ID.
 */
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

/**
 * Get a single order row by ID.
 */
function getOrderById(PDO $pdo, int $order_id): ?array {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = :id");
    $stmt->execute([':id' => $order_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Get full order details including items (with addons) and payment.
 * Payment includes processed_by user info for audit display.
 * Uses getOrderItems() and getOrderItemAddons() from itemsHelper.
 */
function getOrderDetails(PDO $pdo, int $order_id): ?array {
    // Main order info + cashier name
    $stmt = $pdo->prepare("
        SELECT o.*, u.full_name AS cashier_name
        FROM orders o
        JOIN users u ON o.cashier_id = u.user_id
        WHERE o.order_id = ?
        LIMIT 1
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) return null;

    // Reuse itemsHelper functions — no inline queries
    $items = getOrderItems($pdo, $order_id);
    foreach ($items as &$item) {
        $item['addons'] = getOrderItemAddons($pdo, $item['order_item_id']);
    }
    unset($item);

    // Payment info — join processed_by user name for audit trail display
    $stmt = $pdo->prepare("
        SELECT
            p.*,
            u.full_name AS processed_by_name
        FROM payments p
        LEFT JOIN users u ON p.processed_by = u.user_id
        WHERE p.order_id = ?
        LIMIT 1
    ");
    $stmt->execute([$order_id]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    return [
        'order'   => $order,
        'items'   => $items,
        'payment' => $payment,
    ];
}

/**
 * Get paginated + filtered orders for the AJAX endpoint.
 * Handles search, status, date range, cashier view, and pagination.
 * Always excludes drafts.
 */
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

    // ── Build WHERE ────────────────────────────────────────────────────────────
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
        $where[]           = "(o.order_number LIKE :search OR o.customer_name LIKE :search OR o.table_number LIKE :search)";
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

    // ── Filtered total ─────────────────────────────────────────────────────────
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM orders o $whereSQL");
    $countStmt->execute($params);
    $filteredTotal = (int) $countStmt->fetchColumn();

    // ── Paginated orders ───────────────────────────────────────────────────────
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

    // ── Status counts for tab badges ───────────────────────────────────────────
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

    return [
        'orders'         => $orders,
        'filtered_total' => $filteredTotal,
        'status_counts'  => $statusCounts,
        'total'          => $total,
    ];
}