<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../core/authHelper.php';
require_once __DIR__ . '/../core/ingredientsHelper.php';

header('Content-Type: application/json');

if (!hasRole('admin')) {
    echo json_encode(['success' => false, 'error' => 'Access denied. Admin only.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit;
}

$order_id = (int) ($_POST['order_id'] ?? 0);
$reason   = trim($_POST['reason'] ?? '');

if (!$order_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid order ID.']);
    exit;
}

if ($reason === '') {
    echo json_encode(['success' => false, 'error' => 'A reason is required to void an order.']);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        SELECT order_id, order_number, status, subtotal, total_amount, discount, cashier_id
        FROM orders WHERE order_id = ? LIMIT 1
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Order not found.']);
        exit;
    }

    if ($order['status'] !== 'ready') {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'error'   => 'Only fully ready orders can be voided. Current status: ' . $order['status'] . '.',
        ]);
        exit;
    }

    // Verify all active items are ready
    $checkStmt = $pdo->prepare("
        SELECT COUNT(*) FROM order_items
        WHERE order_id = ?
          AND status NOT IN ('ready', 'cancelled', 'voided')
    ");
    $checkStmt->execute([$order_id]);
    if ((int) $checkStmt->fetchColumn() > 0) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'All active items must be ready before voiding the entire order.']);
        exit;
    }

    // Fetch ready items for waste audit
    $itemsStmt = $pdo->prepare("
        SELECT
            oi.order_item_id,
            oi.item_id,
            oi.variant_id,
            oi.quantity,
            oi.item_name_snapshot,
            COALESCE(mv.cost_price, mi.cost_price) AS unit_cost
        FROM order_items oi
        JOIN menu_items mi ON oi.item_id = mi.item_id
        LEFT JOIN menu_variants mv ON oi.variant_id = mv.variant_id
        WHERE oi.order_id = ? AND oi.status = 'ready'
    ");
    $itemsStmt->execute([$order_id]);
    $readyItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

    $totalWasteCost = 0;
    $itemSummary    = [];
    foreach ($readyItems as $ri) {
        $itemCost        = round((float) $ri['unit_cost'] * (int) $ri['quantity'], 2);
        $totalWasteCost += $itemCost;
        $itemSummary[]   = [
            'item'       => $ri['item_name_snapshot'] ?? ('Item #' . $ri['order_item_id']),
            'qty'        => (int) $ri['quantity'],
            'waste_cost' => $itemCost,
        ];
    }

    // Mark ready items as voided, writing void_reason per item
    // (cancel_reason stays NULL — this is a void, not a cancel)
    $pdo->prepare("
        UPDATE order_items
        SET status = 'voided', void_reason = ?
        WHERE order_id = ? AND status = 'ready'
    ")->execute([$reason, $order_id]);

    // Mark the order as voided with void_reason
    // Stock was already deducted when kitchen moved items to 'preparing' —
    // ingredients are consumed and written off as waste. Do NOT restore.
    // Sale total → 0. Waste cost tracked in activity_logs for accounting.
    $pdo->prepare("
        UPDATE orders
        SET status = 'voided', void_reason = ?, subtotal = 0, total_amount = 0
        WHERE order_id = ?
    ")->execute([$reason, $order_id]);

    // Free the table
    $pdo->prepare("
        UPDATE tables SET status = 'available', current_order_id = NULL
        WHERE current_order_id = ?
    ")->execute([$order_id]);

    // Audit log
    $pdo->prepare("
        INSERT INTO activity_logs (user_id, action, details, ip_address)
        VALUES (?, 'void_entire_order', ?, ?)
    ")->execute([
        (int) $_SESSION['user_id'],
        json_encode([
            'order_id'         => $order_id,
            'order_number'     => $order['order_number'],
            'reason'           => $reason,
            'performed_by'     => (int) $_SESSION['user_id'],
            'items'            => $itemSummary,
            'total_waste_cost' => $totalWasteCost,
            'stock_note'       => 'Ingredients already consumed by kitchen — stock NOT restored. Cost written off as waste.',
        ]),
        $_SERVER['REMOTE_ADDR'] ?? null,
    ]);

    $pdo->commit();

    echo json_encode([
        'success'          => true,
        'order_id'         => $order_id,
        'order_number'     => $order['order_number'],
        'order_status'     => 'voided',
        'new_total'        => 0,
        'new_subtotal'     => 0,
        'total_waste_cost' => $totalWasteCost,
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}