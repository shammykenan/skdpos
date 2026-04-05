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

$order_id      = (int)   ($_POST['order_id']      ?? 0);
$reason        = trim(        $_POST['reason']         ?? '');
$refund_amount = (float) ($_POST['refund_amount']  ?? 0);
$refund_method = trim(        $_POST['refund_method']  ?? 'cash');
$refund_note   = trim(        $_POST['refund_note']    ?? '');

if (!$order_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid order ID.']);
    exit;
}

if ($reason === '') {
    echo json_encode(['success' => false, 'error' => 'A reason is required to void an order.']);
    exit;
}

if ($refund_amount < 0) {
    echo json_encode(['success' => false, 'error' => 'Refund amount cannot be negative.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // ── Fetch order ───────────────────────────────────────────────────────────
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

    // ── Verify all active items are ready ─────────────────────────────────────
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

    // ── Compute max refund ────────────────────────────────────────────────────
    $maxRefundStmt = $pdo->prepare("
        SELECT COALESCE(SUM(oi.subtotal), 0) AS items_subtotal
        FROM order_items oi
        WHERE oi.order_id = ? AND oi.status = 'ready'
    ");
    $maxRefundStmt->execute([$order_id]);
    $itemsSubtotal = (float) $maxRefundStmt->fetchColumn();

    $addonSubtotalStmt = $pdo->prepare("
        SELECT COALESCE(SUM(oia.price * oia.quantity), 0) AS addon_total
        FROM order_item_addons oia
        JOIN order_items oi ON oia.order_item_id = oi.order_item_id
        WHERE oi.order_id = ? AND oi.status = 'ready'
    ");
    $addonSubtotalStmt->execute([$order_id]);
    $addonSubtotal = (float) $addonSubtotalStmt->fetchColumn();

    $maxRefund = round($itemsSubtotal + $addonSubtotal, 2);

    // Amount actually paid (no payment_status filter needed — all remaining rows are 'paid')
    $paidStmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount_paid), 0) AS total_paid
        FROM payments
        WHERE order_id = ?
    ");
    $paidStmt->execute([$order_id]);
    $totalPaid = (float) $paidStmt->fetchColumn();

    $refundCap = min($maxRefund, $totalPaid);

    if ($refund_amount > $refundCap + 0.01) {
        $pdo->rollBack();
        echo json_encode([
            'success'    => false,
            'error'      => sprintf(
                'Refund amount (₱%.2f) exceeds the maximum allowed refund of ₱%.2f.',
                $refund_amount,
                $refundCap
            ),
            'refund_cap' => $refundCap,
            'total_paid' => $totalPaid,
            'max_refund' => $maxRefund,
        ]);
        exit;
    }

    // ── Fetch ready items for waste cost calculation ───────────────────────────
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

    // ── Pre-fetch addon costs ─────────────────────────────────────────────────
    $addonCostStmt = $pdo->prepare("
        SELECT
            oia.order_item_id,
            COALESCE(SUM(a.price * oia.quantity), 0) AS addon_cost
        FROM order_item_addons oia
        JOIN addons a ON oia.addon_id = a.addon_id
        WHERE oia.order_item_id IN (
            SELECT order_item_id FROM order_items
            WHERE order_id = ? AND status = 'ready'
        )
        GROUP BY oia.order_item_id
    ");
    $addonCostStmt->execute([$order_id]);
    $addonCosts = [];
    foreach ($addonCostStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $addonCosts[$row['order_item_id']] = (float) $row['addon_cost'];
    }

    $totalWasteCost = 0;
    $itemSummary    = [];
    foreach ($readyItems as $ri) {
        $baseCost  = round((float) $ri['unit_cost'] * (int) $ri['quantity'], 2);
        $addonCost = round($addonCosts[$ri['order_item_id']] ?? 0, 2);
        $itemCost  = $baseCost + $addonCost;
        $totalWasteCost += $itemCost;
        $itemSummary[]   = [
            'item'       => $ri['item_name_snapshot'] ?? ('Item #' . $ri['order_item_id']),
            'qty'        => (int) $ri['quantity'],
            'base_cost'  => $baseCost,
            'addon_cost' => $addonCost,
            'waste_cost' => $itemCost,
        ];
    }

    // ── Mark ready items as voided ────────────────────────────────────────────
    foreach ($readyItems as $ri) {
        $baseCost  = round((float) $ri['unit_cost'] * (int) $ri['quantity'], 2);
        $addonCost = round($addonCosts[$ri['order_item_id']] ?? 0, 2);
        $itemCost  = $baseCost + $addonCost;

        $upStmt = $pdo->prepare("
            UPDATE order_items
            SET status = 'voided', void_reason = ?, waste_cost = ?
            WHERE order_item_id = ?
        ");
        $upStmt->execute([$reason, $itemCost, $ri['order_item_id']]);
    }

    // ── Update order ──────────────────────────────────────────────────────────
    $ordStmt = $pdo->prepare("
        UPDATE orders
        SET status = 'voided', void_reason = ?, subtotal = 0, total_amount = 0, waste_total = ?
        WHERE order_id = ?
    ");
    $ordStmt->execute([$reason, $totalWasteCost, $order_id]);

    // ── Free the table ────────────────────────────────────────────────────────
    $tblStmt = $pdo->prepare("
        UPDATE tables SET status = 'available', current_order_id = NULL
        WHERE current_order_id = ?
    ");
    $tblStmt->execute([$order_id]);

    // ── Record refund in refunds table if amount > 0 ──────────────────────────
    // All refunds — void, item, post-sale — now live in the refunds table.
    // refund_type = 'void_order' distinguishes this from post-completion refunds.
    $refundId = null;
    if ($refund_amount > 0) {
        $refStmt = $pdo->prepare("
            INSERT INTO refunds
                (refund_type, order_id, order_item_id, amount, reason, refund_method, note, processed_by)
            VALUES ('void_order', ?, NULL, ?, ?, ?, ?, ?)
        ");
        $refStmt->execute([
            $order_id,
            $refund_amount,
            $reason,
            $refund_method,
            $refund_note ?: null,
            (int) $_SESSION['user_id'],
        ]);
        $refundId = (int) $pdo->lastInsertId();
    }

    // ── Audit log ─────────────────────────────────────────────────────────────
    $logStmt = $pdo->prepare("
        INSERT INTO activity_logs (user_id, action, details, ip_address)
        VALUES (?, 'void_entire_order', ?, ?)
    ");
    $logStmt->execute([
        (int) $_SESSION['user_id'],
        json_encode([
            'order_id'         => $order_id,
            'order_number'     => $order['order_number'],
            'reason'           => $reason,
            'performed_by'     => (int) $_SESSION['user_id'],
            'items'            => $itemSummary,
            'total_waste_cost' => $totalWasteCost,
            'refund_amount'    => $refund_amount,
            'refund_method'    => $refund_amount > 0 ? $refund_method : null,
            'refund_note'      => $refund_note ?: null,
            'refund_id'        => $refundId,
            'max_refund'       => $maxRefund,
            'total_paid'       => $totalPaid,
            'refund_cap'       => $refundCap,
            'stock_note'       => 'Ingredients already consumed — stock NOT restored. Written off as waste.',
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
        'refund_amount'    => $refund_amount,
        'refund_method'    => $refund_amount > 0 ? $refund_method : null,
        'refund_id'        => $refundId,
        'total_paid'       => $totalPaid,
        'max_refund'       => $maxRefund,
        'refund_cap'       => $refundCap,
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}