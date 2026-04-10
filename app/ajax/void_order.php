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

    // ── Lock the order row to prevent concurrent modifications ─────────────────
    $lockStmt = $pdo->prepare("
        SELECT order_id FROM orders WHERE order_id = ? FOR UPDATE
    ");
    $lockStmt->execute([$order_id]);

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

    $allowedStatuses = ['pending', 'ready'];
    if (!in_array($order['status'], $allowedStatuses)) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'error'   => 'Only pending or ready orders can be voided. Current status: ' . $order['status'] . '.',
        ]);
        exit;
    }

    // ── Validate item statuses ─────────────────────────────────────────────────
    if ($order['status'] === 'pending') {
        $checkStmt = $pdo->prepare("
            SELECT COUNT(*) FROM order_items
            WHERE order_id = ?
              AND status NOT IN ('pending', 'preparing', 'ready', 'cancelled', 'voided')
        ");
        $checkStmt->execute([$order_id]);
        if ((int) $checkStmt->fetchColumn() > 0) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => 'Invalid item status found in order.']);
            exit;
        }
    } else {
        // ready orders — verify all active items are ready
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
    }

    // ── Compute max refund ────────────────────────────────────────────────────
    $paidStmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount_paid), 0) AS total_paid
        FROM payments
        WHERE order_id = ?
    ");
    $paidStmt->execute([$order_id]);
    $totalPaid = (float) $paidStmt->fetchColumn();

    // Max refund is based on active (non-cancelled, non-voided) items
    $maxRefundStmt = $pdo->prepare("
        SELECT COALESCE(SUM(oi.subtotal), 0) AS items_subtotal
        FROM order_items oi
        WHERE oi.order_id = ? AND oi.status NOT IN ('cancelled', 'voided')
    ");
    $maxRefundStmt->execute([$order_id]);
    $itemsSubtotal = (float) $maxRefundStmt->fetchColumn();

    $addonSubtotalStmt = $pdo->prepare("
        SELECT COALESCE(SUM(oia.price * oia.quantity), 0) AS addon_total
        FROM order_item_addons oia
        JOIN order_items oi ON oia.order_item_id = oi.order_item_id
        WHERE oi.order_id = ? AND oi.status NOT IN ('cancelled', 'voided')
    ");
    $addonSubtotalStmt->execute([$order_id]);
    $addonSubtotal = (float) $addonSubtotalStmt->fetchColumn();

    $maxRefund  = round($itemsSubtotal + $addonSubtotal, 2);
    $refundCap  = $totalPaid > 0 ? min($maxRefund, $totalPaid) : $maxRefund;

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

    // ── Fetch ALL active items (pending + preparing + ready) ──────────────────
    $itemsStmt = $pdo->prepare("
        SELECT
            oi.order_item_id,
            oi.item_id,
            oi.variant_id,
            oi.quantity,
            oi.item_name_snapshot,
            oi.status AS item_status,
            COALESCE(mv.cost_price, mi.cost_price) AS unit_cost
        FROM order_items oi
        JOIN menu_items mi ON oi.item_id = mi.item_id
        LEFT JOIN menu_variants mv ON oi.variant_id = mv.variant_id
        WHERE oi.order_id = ? AND oi.status NOT IN ('cancelled', 'voided')
    ");
    $itemsStmt->execute([$order_id]);
    $activeItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Addon costs keyed by order_item_id
    $addonCostStmt = $pdo->prepare("
        SELECT
            oia.order_item_id,
            COALESCE(SUM(a.price * oia.quantity), 0) AS addon_cost
        FROM order_item_addons oia
        JOIN addons a ON oia.addon_id = a.addon_id
        JOIN order_items oi ON oia.order_item_id = oi.order_item_id
        WHERE oi.order_id = ? AND oi.status NOT IN ('cancelled', 'voided')
        GROUP BY oia.order_item_id
    ");
    $addonCostStmt->execute([$order_id]);
    $addonCosts = [];
    foreach ($addonCostStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $addonCosts[$row['order_item_id']] = (float) $row['addon_cost'];
    }

    // ── Process each item with correct waste cost logic ───────────────────────
    //
    // WASTE COST RULES (whole-order void):
    //   pending   → item was NEVER prepared → no ingredients used → waste_cost = NULL
    //   preparing → actively being made     → ingredients consumed → waste_cost = cost
    //   ready     → fully prepared          → ingredients consumed → waste_cost = cost
    //
    // This is the same logic as cancel_items.php (void_item action).
    // ─────────────────────────────────────────────────────────────────────────
    $totalWasteCost = 0;
    $itemSummary    = [];

    foreach ($activeItems as $ri) {
        $currentStatus = $ri['item_status'];
        $itemCost      = null; // NULL = no waste (pending)

        if (in_array($currentStatus, ['preparing', 'ready'])) {
            // Ingredients were consumed — record as waste
            $baseCost = round((float) $ri['unit_cost'] * (int) $ri['quantity'], 2);
            $addCost  = round($addonCosts[$ri['order_item_id']] ?? 0, 2);
            $itemCost = $baseCost + $addCost;
            $totalWasteCost += $itemCost;
        }
        // pending → $itemCost stays NULL → no waste

        $upStmt = $pdo->prepare("
            UPDATE order_items
            SET status = 'voided', void_reason = ?, waste_cost = ?
            WHERE order_item_id = ?
        ");
        $upStmt->execute([$reason, $itemCost, $ri['order_item_id']]);

        $itemSummary[] = [
            'item'             => $ri['item_name_snapshot'] ?? ('Item #' . $ri['order_item_id']),
            'qty'              => (int) $ri['quantity'],
            'item_status_when_voided' => $currentStatus,
            'waste_cost'       => $itemCost,
            'stock_note'       => $itemCost === null
                ? 'Item was pending — not yet prepared. No ingredients consumed. No waste.'
                : 'Item was ' . $currentStatus . ' — ingredients already consumed. Cost written off as waste.',
        ];
    }

    // ── Update order ──────────────────────────────────────────────────────────
    $ordStmt = $pdo->prepare("
        UPDATE orders
        SET status = 'voided', void_reason = ?, subtotal = 0, total_amount = 0,
            waste_total = ?
        WHERE order_id = ?
    ");
    $ordStmt->execute([$reason, $totalWasteCost, $order_id]);

    // ── Free the table ────────────────────────────────────────────────────────
    $pdo->prepare("
        UPDATE tables SET status = 'available', current_order_id = NULL
        WHERE current_order_id = ?
    ")->execute([$order_id]);

    // ── Record refund if amount > 0 ───────────────────────────────────────────
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
            'refund_amount'    => $refund_amount,
            'refund_method'    => $refund_amount > 0 ? $refund_method : null,
            'refund_note'      => $refund_note ?: null,
            'refund_id'        => $refundId,
            'max_refund'       => $maxRefund,
            'total_paid'       => $totalPaid,
            'refund_cap'       => $refundCap,
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