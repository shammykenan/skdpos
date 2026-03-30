<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../core/authHelper.php';
require_once __DIR__ . '/../core/ingredientsHelper.php';

header('Content-Type: application/json');

// ── Admin only ────────────────────────────────────────────────────────────────
if (!hasRole('admin')) {
    echo json_encode(['success' => false, 'error' => 'Access denied. Admin only.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit;
}

$order_id = (int) ($_POST['order_id'] ?? 0);

if (!$order_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid order ID.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // ── Fetch the order ───────────────────────────────────────────────────────
    $stmt = $pdo->prepare("
        SELECT order_id, order_number, status, subtotal, total_amount, discount, cashier_id
        FROM orders
        WHERE order_id = ?
        LIMIT 1
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Order not found.']);
        exit;
    }

    // ── Guard: only void a 'ready' order ──────────────────────────────────────
    // A "void order" means the entire order is ready and we're voiding everything.
    if ($order['status'] !== 'ready') {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'error'   => 'Only orders with all items ready can be voided. Current status: ' . $order['status'] . '.',
        ]);
        exit;
    }

    // ── Verify ALL items are 'ready' ──────────────────────────────────────────
    $checkStmt = $pdo->prepare("
        SELECT COUNT(*) FROM order_items
        WHERE order_id = ?
          AND status != 'ready'
          AND status NOT IN ('cancelled', 'voided')
    ");
    $checkStmt->execute([$order_id]);
    $nonReadyCount = (int) $checkStmt->fetchColumn();

    if ($nonReadyCount > 0) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'error'   => 'All active items must be in ready status before voiding the entire order.',
        ]);
        exit;
    }

    // ── Fetch all ready items for cost audit log ───────────────────────────────
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
        WHERE oi.order_id = ?
          AND oi.status = 'ready'
    ");
    $itemsStmt->execute([$order_id]);
    $readyItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

    // ── Calculate total waste cost for log ────────────────────────────────────
    $totalWasteCost = 0;
    $itemSummary    = [];
    foreach ($readyItems as $ri) {
        $itemCost        = round((float)$ri['unit_cost'] * (int)$ri['quantity'], 2);
        $totalWasteCost += $itemCost;
        $itemSummary[]   = [
            'item'       => $ri['item_name_snapshot'] ?? ('Item #' . $ri['order_item_id']),
            'qty'        => (int)$ri['quantity'],
            'waste_cost' => $itemCost,
        ];
    }

    // ── Mark all ready items as 'voided' ──────────────────────────────────────
    $pdo->prepare("
        UPDATE order_items
        SET status = 'voided'
        WHERE order_id = ?
          AND status = 'ready'
    ")->execute([$order_id]);

    // ── Mark the order as 'cancelled' ─────────────────────────────────────────
    // Revenue = 0 (customer doesn't pay), ingredients consumed = write-off
    $pdo->prepare("
        UPDATE orders
        SET status = 'cancelled', subtotal = 0, total_amount = 0
        WHERE order_id = ?
    ")->execute([$order_id]);

    // ── Free the table if dine-in ─────────────────────────────────────────────
    $pdo->prepare("
        UPDATE tables
        SET status = 'available', current_order_id = NULL
        WHERE current_order_id = ?
    ")->execute([$order_id]);

    // ── Log the waste for management reports ──────────────────────────────────
    $logStmt = $pdo->prepare("
        INSERT INTO activity_logs (user_id, action, details, ip_address)
        VALUES (?, 'void_entire_order', ?, ?)
    ");
    $logStmt->execute([
        (int) $_SESSION['user_id'],
        json_encode([
            'order_id'        => $order_id,
            'order_number'    => $order['order_number'],
            'items'           => $itemSummary,
            'total_waste_cost'=> $totalWasteCost,
            'note'            => 'Entire order voided — all items were ready. Ingredients consumed, cost written off as waste.',
        ]),
        $_SERVER['REMOTE_ADDR'] ?? null,
    ]);

    $pdo->commit();

    echo json_encode([
        'success'          => true,
        'order_id'         => $order_id,
        'order_number'     => $order['order_number'],
        'order_status'     => 'cancelled',
        'new_total'        => 0,
        'new_subtotal'     => 0,
        'total_waste_cost' => $totalWasteCost,
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}