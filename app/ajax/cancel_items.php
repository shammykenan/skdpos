<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../core/authHelper.php';
require_once __DIR__ . '/../core/ingredientsHelper.php';

header('Content-Type: application/json');

$isAdmin   = hasRole('admin');
$isCashier = hasRole('cashier');

if (!$isAdmin && !$isCashier) {
    echo json_encode(['success' => false, 'error' => 'Access denied.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit;
}

$order_item_id = (int)   ($_POST['order_item_id'] ?? 0);
$action        = trim(        $_POST['action']         ?? '');  // 'cancel_item' | 'void_item'
$reason        = trim(        $_POST['reason']         ?? '');
$refund_amount = (float) ($_POST['refund_amount']  ?? 0);       // optional, void_item only
$refund_method = trim(        $_POST['refund_method']  ?? 'cash');
$refund_note   = trim(        $_POST['refund_note']    ?? '');

if (!$order_item_id || !in_array($action, ['cancel_item', 'void_item'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid parameters.']);
    exit;
}

if ($action === 'void_item' && !$isAdmin) {
    echo json_encode(['success' => false, 'error' => 'Only admins can void items.']);
    exit;
}

if ($reason === '') {
    echo json_encode(['success' => false, 'error' => 'A reason is required.']);
    exit;
}

if ($refund_amount < 0) {
    echo json_encode(['success' => false, 'error' => 'Refund amount cannot be negative.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // ── Fetch order_id first to lock it ─────────────────────────────────────────
    $orderIdStmt = $pdo->prepare("SELECT order_id FROM order_items WHERE order_item_id = ?");
    $orderIdStmt->execute([$order_item_id]);
    $order_id = $orderIdStmt->fetchColumn();

    if (!$order_id) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Order item not found.']);
        exit;
    }

    // ── Lock the order row to prevent concurrent modifications ─────────────────
    $lockStmt = $pdo->prepare("
        SELECT order_id FROM orders WHERE order_id = ? FOR UPDATE
    ");
    $lockStmt->execute([$order_id]);

    // ── Fetch item + parent order in one query ────────────────────────────────
    $stmt = $pdo->prepare("
        SELECT
            oi.order_item_id,
            oi.order_id,
            oi.item_id,
            oi.variant_id,
            oi.quantity,
            oi.subtotal,
            oi.status          AS item_status,
            oi.item_name_snapshot,
            o.status           AS order_status,
            o.discount         AS order_discount,
            o.cashier_id
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.order_id
        WHERE oi.order_item_id = ?
        LIMIT 1
    ");
    $stmt->execute([$order_item_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Order item not found.']);
        exit;
    }

    if (in_array($item['order_status'], ['completed', 'cancelled', 'voided'])) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Cannot modify items on a ' . $item['order_status'] . ' order.']);
        exit;
    }

    if (in_array($item['item_status'], ['cancelled', 'voided', 'completed'])) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Item is already ' . $item['item_status'] . '.']);
        exit;
    }

    if ($action === 'cancel_item' && $item['item_status'] !== 'pending') {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Only pending items can be cancelled. This item is ' . $item['item_status'] . '.']);
        exit;
    }

    // ── Check payment status for order ────────────────────────────────────────
    $paymentStmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount_paid), 0) AS total_paid
        FROM payments
        WHERE order_id = ?
    ");
    $paymentStmt->execute([$item['order_id']]);
    $totalPaid = (float) $paymentStmt->fetchColumn();

    // For void_item: validate the item status is voidable
    if ($action === 'void_item') {
        $allowedVoidStatuses = ['pending', 'preparing', 'ready'];
        if (!in_array($item['item_status'], $allowedVoidStatuses)) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => 'Cannot void item with status: ' . $item['item_status'] . '.']);
            exit;
        }
        // If no payment exists, only ready items can be voided (unpaid orders)
        if ($totalPaid <= 0 && $item['item_status'] !== 'ready') {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => 'Only ready items can be voided for unpaid orders. This item is ' . $item['item_status'] . '.']);
            exit;
        }
    }

    $new_item_status = $action === 'cancel_item' ? 'cancelled' : 'voided';
    $itemLabel       = $item['item_name_snapshot'] ?? ('Item #' . $order_item_id);

    // ── 1. Calculate waste cost ───────────────────────────────────────────────
    //
    // WASTE COST RULES (item-level void):
    //   - pending   → item was NEVER prepared, no ingredients used → waste_cost = NULL
    //   - preparing → item is being made, ingredients already consumed → waste_cost = cost
    //   - ready     → item is fully prepared, ingredients consumed → waste_cost = cost
    //   - cancel    → always NULL (only pending items can be cancelled)
    //
    // NOTE: In a pay-first system, payment existing does NOT change whether
    // ingredients were consumed. Only the kitchen status determines that.
    // ─────────────────────────────────────────────────────────────────────────
    $wasteCost = null;

    if ($action === 'void_item') {
        $currentItemStatus = $item['item_status'];

        if (in_array($currentItemStatus, ['preparing', 'ready'])) {
            // Ingredients have been consumed — record waste cost
            $costStmt = $pdo->prepare("
                SELECT
                    oi.quantity,
                    COALESCE(mv.cost_price, mi.cost_price) AS unit_cost
                FROM order_items oi
                JOIN menu_items mi ON oi.item_id = mi.item_id
                LEFT JOIN menu_variants mv ON oi.variant_id = mv.variant_id
                WHERE oi.order_item_id = ?
            ");
            $costStmt->execute([$order_item_id]);
            $costRow = $costStmt->fetch(PDO::FETCH_ASSOC);

            $itemCost = $costRow
                ? round((float) $costRow['unit_cost'] * (int) $costRow['quantity'], 2)
                : 0.00;

            $addonStmt = $pdo->prepare("
                SELECT COALESCE(SUM(a.price * oia.quantity), 0) AS addon_total
                FROM order_item_addons oia
                JOIN addons a ON oia.addon_id = a.addon_id
                WHERE oia.order_item_id = ?
            ");
            $addonStmt->execute([$order_item_id]);
            $addonCost = (float) ($addonStmt->fetchColumn() ?? 0);

            $wasteCost = round($itemCost + $addonCost, 2);

        } else {
            // pending → not prepared yet, no ingredients used → no waste cost
            $wasteCost = null;
        }

        // ── Validate refund amount cap ────────────────────────────────────────
        if ($refund_amount > 0) {
            $sellingSubtotal = (float) $item['subtotal'];

            $addonSellingStmt = $pdo->prepare("
                SELECT COALESCE(SUM(oia.price * oia.quantity), 0) AS addon_selling
                FROM order_item_addons oia
                WHERE oia.order_item_id = ?
            ");
            $addonSellingStmt->execute([$order_item_id]);
            $addonSelling     = (float) $addonSellingStmt->fetchColumn();
            $itemSellingTotal = round($sellingSubtotal + $addonSelling, 2);

            $refundCap = $totalPaid > 0
                ? min($itemSellingTotal, $totalPaid)
                : $itemSellingTotal;

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
                ]);
                exit;
            }
        }
    }

    // ── 2. Update item status ─────────────────────────────────────────────────
    if ($action === 'cancel_item') {
        $pdo->prepare("
            UPDATE order_items
            SET status = 'cancelled', cancel_reason = ?, waste_cost = NULL
            WHERE order_item_id = ?
        ")->execute([$reason, $order_item_id]);

    } else {
        $pdo->prepare("
            UPDATE order_items
            SET status = 'voided', void_reason = ?, waste_cost = ?
            WHERE order_item_id = ?
        ")->execute([$reason, $wasteCost, $order_item_id]);
    }

    // ── 3. Recalculate orders.waste_total ─────────────────────────────────────
    $wasteTotalStmt = $pdo->prepare("
        SELECT COALESCE(SUM(waste_cost), 0)
        FROM order_items
        WHERE order_id = ?
          AND status = 'voided'
          AND waste_cost IS NOT NULL
    ");
    $wasteTotalStmt->execute([$order_id]);
    $newWasteTotal = (float) $wasteTotalStmt->fetchColumn();

    $pdo->prepare("
        UPDATE orders SET waste_total = ? WHERE order_id = ?
    ")->execute([$newWasteTotal, $order_id]);

    // ── 4. Record refund in refunds table (void_item only, amount > 0) ────────
    $refundId = null;
    if ($action === 'void_item' && $refund_amount > 0) {
        $refStmt = $pdo->prepare("
            INSERT INTO refunds
                (refund_type, order_id, order_item_id, amount, reason, refund_method, note, processed_by)
            VALUES ('void_item', ?, ?, ?, ?, ?, ?, ?)
        ");
        $refStmt->execute([
            $order_id,
            $order_item_id,
            $refund_amount,
            $reason,
            $refund_method,
            $refund_note ?: null,
            (int) $_SESSION['user_id'],
        ]);
        $refundId = (int) $pdo->lastInsertId();
    }

    // ── 5. Audit log ──────────────────────────────────────────────────────────
    $logAction  = $action === 'cancel_item' ? 'cancel_order_item' : 'void_order_item';
    $logDetails = [
        'order_item_id' => $order_item_id,
        'order_id'      => $order_id,
        'item'          => $itemLabel,
        'qty'           => (int) $item['quantity'],
        'reason'        => $reason,
        'performed_by'  => (int) $_SESSION['user_id'],
        'item_status_at_void' => $item['item_status'],
    ];
    if ($action === 'cancel_item') {
        $logDetails['stock_note'] = 'Item was pending — not yet prepared. No ingredients consumed. No waste.';
    } else {
        $logDetails['waste_cost']    = $wasteCost;
        $logDetails['stock_note']    = $item['item_status'] === 'pending'
            ? 'Item was pending (paid, not yet prepared) — no ingredients consumed. No waste cost.'
            : 'Item was ' . $item['item_status'] . ' — ingredients already consumed. Cost written off as waste.';
        $logDetails['refund_amount'] = $refund_amount;
        $logDetails['refund_method'] = $refund_amount > 0 ? $refund_method : null;
        $logDetails['refund_note']   = $refund_note ?: null;
        $logDetails['refund_id']     = $refundId;
    }

    $pdo->prepare("
        INSERT INTO activity_logs (user_id, action, details, ip_address)
        VALUES (?, ?, ?, ?)
    ")->execute([
        (int) $_SESSION['user_id'],
        $logAction,
        json_encode($logDetails),
        $_SERVER['REMOTE_ADDR'] ?? null,
    ]);

    // ── 6. Recalculate order totals (cancel only) ─────────────────────────────
    // Void does NOT reduce total_amount — gross revenue must stay intact.
    // Only cancellations reduce the order total (item never paid for / refunded).
    if ($action === 'cancel_item') {
        $totalsStmt = $pdo->prepare("
            SELECT COALESCE(SUM(subtotal), 0) AS active_subtotal
            FROM order_items
            WHERE order_id = ?
              AND status NOT IN ('cancelled', 'voided')
        ");
        $totalsStmt->execute([$order_id]);
        $activeSubtotal = (float) $totalsStmt->fetchColumn();

        $addonTotalsStmt = $pdo->prepare("
            SELECT COALESCE(SUM(oia.price * oia.quantity), 0) AS addon_total
            FROM order_item_addons oia
            JOIN order_items oi ON oia.order_item_id = oi.order_item_id
            WHERE oi.order_id = ?
              AND oi.status NOT IN ('cancelled', 'voided')
        ");
        $addonTotalsStmt->execute([$order_id]);
        $addonTotal = (float) $addonTotalsStmt->fetchColumn();

        $newSubtotal    = round($activeSubtotal + $addonTotal, 2);
        $discount       = (float) $item['order_discount'];
        $newTotalAmount = round(max(0, $newSubtotal - $discount), 2);

        $pdo->prepare("
            UPDATE orders SET subtotal = ?, total_amount = ? WHERE order_id = ?
        ")->execute([$newSubtotal, $newTotalAmount, $order_id]);
    } else {
        // void_item: read current totals unchanged for the JSON response
        $newSubtotal    = (float) $item['subtotal']; // not updated, just for response
        $newTotalAmount = $newSubtotal - (float) $item['order_discount'];
    }

    // ── 7. Recalculate order status ───────────────────────────────────────────
    $statusCountStmt = $pdo->prepare("
        SELECT status, COUNT(*) AS cnt
        FROM order_items
        WHERE order_id = ?
          AND status NOT IN ('cancelled', 'voided')
        GROUP BY status
    ");
    $statusCountStmt->execute([$order_id]);
    $statusCounts = [];
    foreach ($statusCountStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $statusCounts[$row['status']] = (int) $row['cnt'];
    }

    $activeTotal    = array_sum($statusCounts);
    $pendingCount   = $statusCounts['pending']   ?? 0;
    $preparingCount = $statusCounts['preparing'] ?? 0;
    $readyCount     = $statusCounts['ready']     ?? 0;

    $newOrderStatus = $item['order_status'];
    $guard          = "WHERE order_id = ? AND status NOT IN ('completed', 'cancelled', 'voided')";

    if ($activeTotal === 0) {
        $voidedCheck = $pdo->prepare("
            SELECT COUNT(*) FROM order_items WHERE order_id = ? AND status = 'voided'
        ");
        $voidedCheck->execute([$order_id]);
        $hasVoided      = (int) $voidedCheck->fetchColumn() > 0;
        $newOrderStatus = $hasVoided ? 'voided' : 'cancelled';

        $pdo->prepare("UPDATE orders SET status = ? $guard")->execute([$newOrderStatus, $order_id]);

        $pdo->prepare("
            UPDATE tables SET status = 'available', current_order_id = NULL
            WHERE current_order_id = ?
        ")->execute([$order_id]);

        $pdo->prepare("
            INSERT INTO activity_logs (user_id, action, details, ip_address)
            VALUES (?, 'auto_close_order', ?, ?)
        ")->execute([
            (int) $_SESSION['user_id'],
            json_encode([
                'order_id'     => $order_id,
                'final_status' => $newOrderStatus,
                'reason'       => 'All items were cancelled or voided.',
            ]),
            $_SERVER['REMOTE_ADDR'] ?? null,
        ]);

    } elseif ($readyCount === $activeTotal) {
        $newOrderStatus = 'ready';
        $pdo->prepare("UPDATE orders SET status = 'ready' $guard")->execute([$order_id]);

    } elseif ($preparingCount > 0 || ($readyCount > 0 && $pendingCount > 0)) {
        $newOrderStatus = 'preparing';
        $pdo->prepare("UPDATE orders SET status = 'preparing' $guard")->execute([$order_id]);

    } elseif ($pendingCount === $activeTotal) {
        $newOrderStatus = 'pending';
        $pdo->prepare("UPDATE orders SET status = 'pending' $guard")->execute([$order_id]);
    }

    $pdo->commit();

    echo json_encode([
        'success'       => true,
        'action'        => $action,
        'order_item_id' => $order_item_id,
        'item_status'   => $new_item_status,
        'order_id'      => $order_id,
        'order_status'  => $newOrderStatus,
        'new_total'     => $newTotalAmount,
        'new_subtotal'  => $newSubtotal,
        'waste_cost'    => $wasteCost,
        'waste_total'   => $newWasteTotal,
        'refund_amount' => $action === 'void_item' ? $refund_amount : null,
        'refund_method' => ($action === 'void_item' && $refund_amount > 0) ? $refund_method : null,
        'refund_id'     => $refundId,
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}