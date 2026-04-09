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
$refund_type   = trim(        $_POST['refund_type']    ?? '');  // 'full' | 'partial'
$reason        = trim(        $_POST['reason']         ?? '');
$refund_method = trim(        $_POST['refund_method']  ?? 'cash');
$items_json    = trim(        $_POST['items']           ?? '[]');

if (!$order_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid order ID.']);
    exit;
}
if (!in_array($refund_type, ['full', 'partial'], true)) {
    echo json_encode(['success' => false, 'error' => 'Invalid refund type.']);
    exit;
}
if ($reason === '') {
    echo json_encode(['success' => false, 'error' => 'A reason is required.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // ── Fetch order ──────────────────────────────────────────────
    $stmt = $pdo->prepare("
        SELECT order_id, order_number, status, total_amount, cashier_id
        FROM orders WHERE order_id = ? LIMIT 1
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Order not found.']);
        exit;
    }

    // Allow refunds on completed orders OR pending orders with payment
    $paymentStmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount_paid), 0) AS total_paid
        FROM payments
        WHERE order_id = ?
    ");
    $paymentStmt->execute([$order_id]);
    $totalPaid = (float) $paymentStmt->fetchColumn();

    if ($order['status'] !== 'completed' && ($order['status'] !== 'pending' || $totalPaid <= 0)) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Refunds are only allowed on completed orders or pending orders with payment.']);
        exit;
    }

    // ── How much has already been refunded ──────────────────────
    $alreadyStmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount), 0) FROM refunds WHERE order_id = ?
    ");
    $alreadyStmt->execute([$order_id]);
    $alreadyRefunded = (float) $alreadyStmt->fetchColumn();

    $orderTotal   = (float) $order['total_amount'];
    $remainingCap = round($orderTotal - $alreadyRefunded, 2);

    if ($remainingCap <= 0) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'This order has already been fully refunded.']);
        exit;
    }

    $insertedRefunds = [];

    if ($refund_type === 'full') {
        // ── Full refund ──────────────────────────────
        $refundAmount = $remainingCap;

        $ins = $pdo->prepare("
            INSERT INTO refunds (order_id, order_item_id, amount, reason, refund_method, refund_type, processed_by)
            VALUES (?, NULL, ?, ?, ?, 'void_order', ?)
        ");
        $ins->execute([$order_id, $refundAmount, $reason, $refund_method, (int) $_SESSION['user_id']]);
        $insertedRefunds[] = [
            'refund_id'     => (int) $pdo->lastInsertId(),
            'order_item_id' => null,
            'amount'        => $refundAmount,
        ];

        $totalRefunded = $refundAmount;

    } else {
        // ── Partial refund ──────────────────────────────
        $selectedItems = json_decode($items_json, true);
        if (!is_array($selectedItems) || count($selectedItems) === 0) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => 'No items selected for partial refund.']);
            exit;
        }

        $itemStmt = $pdo->prepare("
            SELECT order_item_id, subtotal, status
            FROM order_items
            WHERE order_item_id = ? AND order_id = ?
            LIMIT 1
        ");
        $itemAlreadyStmt = $pdo->prepare("
            SELECT COALESCE(SUM(amount), 0)
            FROM refunds
            WHERE order_item_id = ?
        ");

        foreach ($selectedItems as $sel) {
            $oi_id  = (int)   ($sel['order_item_id'] ?? 0);
            $amount = (float) ($sel['amount'] ?? 0);

            if (!$oi_id || $amount <= 0) continue;

            $itemStmt->execute([$oi_id, $order_id]);
            $item = $itemStmt->fetch(PDO::FETCH_ASSOC);

            if (!$item) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'error' => "Item #{$oi_id} not found on this order."]);
                exit;
            }

            if (in_array($item['status'], ['cancelled', 'voided'], true)) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'error' => 'Cancelled or voided items cannot be refunded.']);
                exit;
            }

            $itemAlreadyStmt->execute([$oi_id]);
            $itemAlreadyRefunded = (float) $itemAlreadyStmt->fetchColumn();
            $itemCap = round($item['subtotal'] - $itemAlreadyRefunded, 2);

            if ($amount > $itemCap) {
                $pdo->rollBack();
                echo json_encode([
                    'success' => false,
                    'error'   => sprintf(
                        'Item #%d refund (₱%.2f) exceeds the remaining refundable amount of ₱%.2f.',
                        $oi_id, $amount, $itemCap
                    ),
                ]);
                exit;
            }

            $ins = $pdo->prepare("
                INSERT INTO refunds (order_id, order_item_id, amount, reason, refund_method, refund_type, processed_by)
                VALUES (?, ?, ?, ?, ?, 'post_sale', ?)
            ");
            $ins->execute([$order_id, $oi_id, $amount, $reason, $refund_method, (int) $_SESSION['user_id']]);

            $insertedRefunds[] = [
                'refund_id'     => (int) $pdo->lastInsertId(),
                'order_item_id' => $oi_id,
                'amount'        => $amount,
            ];
        }

        $totalRefunded = array_sum(array_column($insertedRefunds, 'amount'));
    }

    // ── Compute waste cost for refunded items ─────────────────────────────────
    //
    // WASTE COST RULES for post-sale refunds:
    //   For completed orders: items are in 'completed' status → ingredients were consumed
    //   → waste_cost = ingredient cost (base + addons). Always set for completed items.
    //
    //   For pending orders (pay-first): apply the same rule as void_item:
    //   pending   → not yet prepared → waste_cost = NULL
    //   preparing → being made       → waste_cost = cost
    //   ready     → fully prepared   → waste_cost = cost
    //
    // This ensures the waste report and COGS calculations remain accurate.
    // ─────────────────────────────────────────────────────────────────────────
    $totalWasteCost = 0;
    $wasteItems     = [];

    // Build the list of order_item_ids to process for waste
    $itemIdsForWaste = [];
    if ($refund_type === 'full') {
        // All non-cancelled/voided items
        $allItemsStmt = $pdo->prepare("
            SELECT order_item_id FROM order_items
            WHERE order_id = ? AND status NOT IN ('cancelled', 'voided')
        ");
        $allItemsStmt->execute([$order_id]);
        $itemIdsForWaste = array_column($allItemsStmt->fetchAll(PDO::FETCH_ASSOC), 'order_item_id');
    } else {
        // Only the refunded items
        $itemIdsForWaste = array_filter(
            array_column($insertedRefunds, 'order_item_id'),
            fn($id) => $id !== null
        );
    }

    if (!empty($itemIdsForWaste)) {
        // Fetch item details with current status and cost
        $placeholders = implode(',', array_fill(0, count($itemIdsForWaste), '?'));
        $wasteItemsStmt = $pdo->prepare("
            SELECT
                oi.order_item_id,
                oi.quantity,
                oi.item_name_snapshot,
                oi.status AS item_status,
                COALESCE(mv.cost_price, mi.cost_price) AS unit_cost
            FROM order_items oi
            JOIN menu_items mi ON oi.item_id = mi.item_id
            LEFT JOIN menu_variants mv ON oi.variant_id = mv.variant_id
            WHERE oi.order_item_id IN ($placeholders)
        ");
        $wasteItemsStmt->execute($itemIdsForWaste);
        $wasteItemsData = $wasteItemsStmt->fetchAll(PDO::FETCH_ASSOC);

        // Addon costs for these items
        $addonCostStmt = $pdo->prepare("
            SELECT
                oia.order_item_id,
                COALESCE(SUM(a.price * oia.quantity), 0) AS addon_cost
            FROM order_item_addons oia
            JOIN addons a ON oia.addon_id = a.addon_id
            WHERE oia.order_item_id IN ($placeholders)
            GROUP BY oia.order_item_id
        ");
        $addonCostStmt->execute($itemIdsForWaste);
        $addonCosts = [];
        foreach ($addonCostStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $addonCosts[$row['order_item_id']] = (float) $row['addon_cost'];
        }

        foreach ($wasteItemsData as $item) {
            $currentStatus = $item['item_status'];
            $wasteCost     = null; // default: no waste

            // Completed items: ingredients were consumed during order fulfillment
            // Preparing/ready items: ingredients in progress or done
            // Pending items: not yet started — no waste
            if (in_array($currentStatus, ['completed', 'preparing', 'ready'])) {
                $baseCost  = round((float) $item['unit_cost'] * (int) $item['quantity'], 2);
                $addonCost = round($addonCosts[$item['order_item_id']] ?? 0, 2);
                $wasteCost = $baseCost + $addonCost;
                $totalWasteCost += $wasteCost;
            }
            // pending → wasteCost stays null

            // Update waste_cost on the item (only if not already set, to avoid double-counting)
            $existingWaste = $pdo->prepare("SELECT waste_cost FROM order_items WHERE order_item_id = ?");
            $existingWaste->execute([$item['order_item_id']]);
            $currentWaste = $existingWaste->fetchColumn();

            if ($currentWaste === null && $wasteCost !== null) {
                $pdo->prepare("UPDATE order_items SET waste_cost = ? WHERE order_item_id = ?")
                    ->execute([$wasteCost, $item['order_item_id']]);
            }

            $wasteItems[] = [
                'order_item_id'          => $item['order_item_id'],
                'item'                   => $item['item_name_snapshot'] ?? ('Item #' . $item['order_item_id']),
                'qty'                    => (int) $item['quantity'],
                'item_status_when_refunded' => $currentStatus,
                'waste_cost'             => $wasteCost,
                'stock_note'             => $wasteCost === null
                    ? 'Item was pending — not yet prepared. No ingredients consumed. No waste.'
                    : 'Item was ' . $currentStatus . ' — ingredients already consumed. Cost written off as waste.',
            ];
        }

        // Update order waste_total
        if ($totalWasteCost > 0) {
            $pdo->prepare("
                UPDATE orders
                SET waste_total = COALESCE(waste_total, 0) + ?
                WHERE order_id = ?
            ")->execute([$totalWasteCost, $order_id]);
        }
    }

    // ── Audit log ─────────────────────────────────────
    $pdo->prepare("
        INSERT INTO activity_logs (user_id, action, details, ip_address)
        VALUES (?, 'process_refund', ?, ?)
    ")->execute([
        (int) $_SESSION['user_id'],
        json_encode([
            'order_id'         => $order_id,
            'order_number'     => $order['order_number'],
            'refund_type'      => $refund_type,
            'reason'           => $reason,
            'refund_method'    => $refund_method,
            'refunds'          => $insertedRefunds,
            'total_refunded'   => $totalRefunded,
            'already_refunded' => $alreadyRefunded,
            'order_total'      => $orderTotal,
            'waste_items'      => $wasteItems,
            'total_waste_cost' => $totalWasteCost,
        ]),
        $_SERVER['REMOTE_ADDR'] ?? null,
    ]);

    $pdo->commit();

    echo json_encode([
        'success'          => true,
        'order_id'         => $order_id,
        'order_number'     => $order['order_number'],
        'refund_type'      => $refund_type,
        'total_refunded'   => round($totalRefunded, 2),
        'already_refunded' => $alreadyRefunded,
        'order_total'      => $orderTotal,
        'refunds'          => $insertedRefunds,
        'waste_items'      => $wasteItems,
        'total_waste_cost' => $totalWasteCost,
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}