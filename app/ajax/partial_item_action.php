<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../core/authHelper.php';
require_once __DIR__ . '/../core/ingredientsHelper.php';
require_once __DIR__ . '/../core/addonsHelper.php';

header('Content-Type: application/json');

$isAdmin   = hasRole('admin');
$isCashier = hasRole('cashier');
$isKitchen = hasRole('kitchen');

if (!$isAdmin && !$isCashier && !$isKitchen) {
    echo json_encode(['success' => false, 'error' => 'Access denied.']); exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid method.']); exit;
}

$order_item_id = (int)   ($_POST['order_item_id'] ?? 0);
$action        = trim(   ($_POST['action']         ?? '')); // 'partial_start' | 'partial_ready' | 'partial_void'
$partial_qty   = (int)   ($_POST['partial_qty']    ?? 0);
$reason        = trim(   ($_POST['reason']         ?? ''));
$refund_amount = (float) ($_POST['refund_amount']  ?? 0);
$refund_method = trim(   ($_POST['refund_method']  ?? 'cash'));
$refund_note   = trim(   ($_POST['refund_note']    ?? ''));

if (!$order_item_id || !in_array($action, ['partial_start','partial_ready','partial_complete','partial_void']) || $partial_qty < 1) {
    echo json_encode(['success' => false, 'error' => 'Invalid parameters.']); exit;
}
if ($action === 'partial_void' && $reason === '') {
    echo json_encode(['success' => false, 'error' => 'A reason is required.']); exit;
}
if ($action === 'partial_void' && !$isAdmin && !$isCashier) {
    echo json_encode(['success' => false, 'error' => 'Only admin or cashier can void items.']); exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        SELECT oi.*, o.status AS order_status
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.order_id
        WHERE oi.order_item_id = ?
        FOR UPDATE
    ");
    $stmt->execute([$order_item_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Item not found.']); exit;
    }

    $total_qty   = (int) $item['quantity'];
    $qty_started = (int) $item['quantity_started'];
    $qty_ready   = (int) $item['quantity_ready'];
    $qty_voided  = (int) $item['quantity_voided'];
    $qty_pending = $total_qty - $qty_started - $qty_ready - $qty_voided;
    $order_id    = (int) $item['order_id'];
    $item_status = $item['status'];

    if (in_array($item['order_status'], ['completed','cancelled','voided'])) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Cannot modify a ' . $item['order_status'] . ' order.']); exit;
    }
    if (in_array($item_status, ['cancelled','voided','completed'])) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Item is already ' . $item_status . '.']); exit;
    }

    $wasteCost = null;
    $refundId  = null;

    // ── PARTIAL START (pending → preparing) ───────────────────────────────────
    if ($action === 'partial_start') {
        if (!$isAdmin && !$isKitchen && !$isCashier) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => 'Not permitted.']); exit;
        }
        if ($qty_pending <= 0) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => 'No pending units to start.']); exit;
        }
        if ($partial_qty > $qty_pending) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => "Cannot start {$partial_qty} — only {$qty_pending} pending."]); exit;
        }

        $new_qty_started = $qty_started + $partial_qty;

        // Determine new item status
        // If all non-voided units are now either started or ready → preparing
        // (there may still be ready units from previous partial_ready calls)
        $new_qty_pending = $qty_pending - $partial_qty;
        $new_item_status = 'preparing'; // starting always means at least some are preparing

        $pdo->prepare("
            UPDATE order_items
            SET quantity_started = ?, status = ?
            WHERE order_item_id = ?
        ")->execute([$new_qty_started, $new_item_status, $order_item_id]);

        // Deduct ingredients for the started qty
        deductIngredientsForItem(
            $pdo,
            (int)$item['item_id'],
            $partial_qty,
            $item['variant_id'] ? (int)$item['variant_id'] : null
        );

        $addonStmt = $pdo->prepare("SELECT addon_id, quantity FROM order_item_addons WHERE order_item_id = ?");
        $addonStmt->execute([$order_item_id]);
        foreach ($addonStmt->fetchAll(PDO::FETCH_ASSOC) as $ad) {
            deductIngredientsForAddon($pdo, (int)$ad['addon_id'], (int)$ad['quantity'] * $partial_qty);
        }
    }

    // ── PARTIAL READY (preparing → ready) ─────────────────────────────────────
    if ($action === 'partial_ready') {
        if (!$isAdmin && !$isKitchen && !$isCashier) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => 'Not permitted.']); exit;
        }
        if ($qty_started <= 0) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => 'No started units to mark ready.']); exit;
        }
        if ($partial_qty > $qty_started) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => "Cannot mark {$partial_qty} ready — only {$qty_started} started."]); exit;
        }

        $new_qty_started = $qty_started - $partial_qty;
        $new_qty_ready   = $qty_ready   + $partial_qty;

        // Recalculate pending after this operation (unchanged)
        $new_qty_pending = $qty_pending; // pending not affected by partial_ready

        // Determine status:
        // - still has units in kitchen → preparing
        // - no units in kitchen, but has pending → pending (mixed: some ready, some pending)
        // - no pending or started, all active are ready → ready
        $active_after = $total_qty - $qty_voided;
        if ($new_qty_started > 0) {
            $new_item_status = 'preparing';
        } elseif ($new_qty_pending > 0) {
            // Some units are ready, some are still pending — keep as preparing
            // so the kitchen knows there's still work to do
            $new_item_status = 'preparing';
        } else {
            // All active units are ready
            $new_item_status = 'ready';
        }

        $pdo->prepare("
            UPDATE order_items
            SET quantity_started = ?, quantity_ready = ?, status = ?
            WHERE order_item_id = ?
        ")->execute([$new_qty_started, $new_qty_ready, $new_item_status, $order_item_id]);
    }
    // ── PARTIAL COMPLETE (ready → completed) ──────────────────────────────────
if ($action === 'partial_complete') {
    if (!$isAdmin && !$isKitchen && !$isCashier) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Not permitted.']); exit;
    }
    if ($qty_ready <= 0) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'No ready units to complete.']); exit;
    }
    if ($partial_qty > $qty_ready) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => "Cannot complete {$partial_qty} — only {$qty_ready} ready."]); exit;
    }

    $new_qty_ready = $qty_ready - $partial_qty;
    $remaining_active = $total_qty - $qty_voided;
    $completed_after = $remaining_active - $new_qty_ready - $qty_started - $qty_pending;

    if ($new_qty_ready === 0 && $qty_started === 0 && $qty_pending === 0) {
        $new_item_status = 'completed';
    } elseif ($qty_started > 0) {
        $new_item_status = 'preparing';
    } elseif ($qty_pending > 0) {
        $new_item_status = 'preparing';
    } else {
        $new_item_status = 'ready'; // still has some ready units left
    }

    $pdo->prepare("
        UPDATE order_items
        SET quantity_ready = ?, status = ?
        WHERE order_item_id = ?
    ")->execute([$new_qty_ready, $new_item_status, $order_item_id]);
}
    // ── PARTIAL VOID ──────────────────────────────────────────────────────────
    if ($action === 'partial_void') {
        // Can void from pending or started, NOT from already-ready units
        $voidable_qty = $qty_pending + $qty_started;
        if ($partial_qty > $voidable_qty) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => "Cannot void {$partial_qty} — only {$voidable_qty} voidable (pending/preparing)."]); exit;
        }

        // Consume from pending first, then started
        $voiding_from_pending = min($partial_qty, $qty_pending);
        $voiding_from_started = $partial_qty - $voiding_from_pending;

        // Waste cost only applies to units already in kitchen (started)
        if ($voiding_from_started > 0) {
            $costStmt = $pdo->prepare("
                SELECT COALESCE(mv.cost_price, mi.cost_price) AS unit_cost
                FROM order_items oi
                JOIN menu_items mi ON oi.item_id = mi.item_id
                LEFT JOIN menu_variants mv ON oi.variant_id = mv.variant_id
                WHERE oi.order_item_id = ?
            ");
            $costStmt->execute([$order_item_id]);
            $unitCost = (float)($costStmt->fetchColumn() ?? 0);

            $addonCostStmt = $pdo->prepare("
                SELECT COALESCE(SUM(a.price * oia.quantity), 0)
                FROM order_item_addons oia
                JOIN addons a ON oia.addon_id = a.addon_id
                WHERE oia.order_item_id = ?
            ");
            $addonCostStmt->execute([$order_item_id]);
            $addonCostPerUnit = (float)$addonCostStmt->fetchColumn();
            $wasteCost = round(($unitCost + $addonCostPerUnit) * $voiding_from_started, 2);
        }

        $new_qty_voided  = $qty_voided  + $partial_qty;
        $new_qty_started = $qty_started - $voiding_from_started;
        $new_qty_pending = $qty_pending - $voiding_from_pending;
        $remaining_active = $total_qty - $new_qty_voided;

        if ($remaining_active <= 0) {
            $new_item_status = 'voided';
        } elseif ($new_qty_started > 0) {
            $new_item_status = 'preparing';
        } elseif ($new_qty_pending > 0) {
            $new_item_status = 'pending';
        } elseif ($qty_ready > 0) {
            // Only ready units remain active
            $new_item_status = 'ready';
        } else {
            $new_item_status = 'pending';
        }

        $existingWaste = (float)($item['waste_cost'] ?? 0);
        $newWasteCost  = $wasteCost !== null
            ? round($existingWaste + $wasteCost, 2)
            : ($existingWaste > 0 ? $existingWaste : null);

        $pdo->prepare("
            UPDATE order_items
            SET quantity_voided = ?, quantity_started = ?, status = ?, waste_cost = ?, void_reason = ?
            WHERE order_item_id = ?
        ")->execute([$new_qty_voided, $new_qty_started, $new_item_status, $newWasteCost, $reason, $order_item_id]);

        // Update order waste_total
        $wTotalStmt = $pdo->prepare("
            SELECT COALESCE(SUM(waste_cost), 0)
            FROM order_items
            WHERE order_id = ? AND waste_cost IS NOT NULL
        ");
        $wTotalStmt->execute([$order_id]);
        $pdo->prepare("UPDATE orders SET waste_total = ? WHERE order_id = ?")
            ->execute([$wTotalStmt->fetchColumn(), $order_id]);

        // Always record void action in refunds table (even if $0 refund for audit trail)
        $paidStmt = $pdo->prepare("SELECT COALESCE(SUM(amount_paid), 0) FROM payments WHERE order_id = ?");
        $paidStmt->execute([$order_id]);
        $totalPaid = (float)$paidStmt->fetchColumn();

        $unitSelling   = (float)$item['unit_price'];
        $addonSellStmt = $pdo->prepare("
            SELECT COALESCE(SUM(price * quantity), 0)
            FROM order_item_addons
            WHERE order_item_id = ?
        ");
        $addonSellStmt->execute([$order_item_id]);
        $addonSellPerUnit = (float)$addonSellStmt->fetchColumn() / max($total_qty, 1);
        $maxRefund        = round(($unitSelling + $addonSellPerUnit) * $partial_qty, 2);
        $refundCap        = $totalPaid > 0 ? min($maxRefund, $totalPaid) : $maxRefund;

        if ($refund_amount > $refundCap + 0.01) {
            $pdo->rollBack();
            echo json_encode([
                'success'    => false,
                'error'      => sprintf('Refund ₱%.2f exceeds cap of ₱%.2f.', $refund_amount, $refundCap),
                'refund_cap' => $refundCap,
            ]); exit;
        }

        $pdo->prepare("
            INSERT INTO refunds (refund_type, order_id, order_item_id, amount, quantity, reason, refund_method, note, processed_by)
            VALUES ('void_item', ?, ?, ?, ?, ?, ?, ?, ?)
        ")->execute([
            $order_id, $order_item_id, $refund_amount, $partial_qty,
            $reason, $refund_method, $refund_note ?: null,
            (int)$_SESSION['user_id'],
        ]);
        $refundId = (int)$pdo->lastInsertId();
    }

    // ── Recalculate order-level status ────────────────────────────────────────
    // Pull fresh counts for all non-terminal items
    $scStmt = $pdo->prepare("
        SELECT status, COUNT(*) AS cnt
        FROM order_items
        WHERE order_id = ? AND status NOT IN ('cancelled','voided')
        GROUP BY status
    ");
    $scStmt->execute([$order_id]);
    $sc = [];
    foreach ($scStmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $sc[$r['status']] = (int)$r['cnt'];
    }

    $activeTotal    = array_sum($sc);
    $pendingCount   = $sc['pending']   ?? 0;
    $preparingCount = $sc['preparing'] ?? 0;
    $readyCount     = $sc['ready']     ?? 0;
    // 'completed' items in sc are handled naturally

    $guard = "WHERE order_id = ? AND status NOT IN ('completed','cancelled','voided')";

    if ($activeTotal === 0) {
        // All items are terminal
        $vc = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE order_id = ? AND status = 'voided'");
        $vc->execute([$order_id]);
        $newOrderStatus = (int)$vc->fetchColumn() > 0 ? 'voided' : 'cancelled';
        $pdo->prepare("UPDATE orders SET status = ? WHERE order_id = ?")->execute([$newOrderStatus, $order_id]);
        $pdo->prepare("UPDATE tables SET status = 'available', current_order_id = NULL WHERE current_order_id = ?")
            ->execute([$order_id]);
    } elseif ($readyCount === $activeTotal) {
        $newOrderStatus = 'ready';
        $pdo->prepare("UPDATE orders SET status = 'ready' $guard")->execute([$order_id]);
    } elseif ($preparingCount > 0) {
        $newOrderStatus = 'preparing';
        $pdo->prepare("UPDATE orders SET status = 'preparing' $guard")->execute([$order_id]);
    } elseif ($pendingCount === $activeTotal) {
        $newOrderStatus = 'pending';
        $pdo->prepare("UPDATE orders SET status = 'pending' $guard")->execute([$order_id]);
    } else {
        // Mixed ready/pending with no preparing — keep preparing so kitchen knows
        $newOrderStatus = 'preparing';
        $pdo->prepare("UPDATE orders SET status = 'preparing' $guard")->execute([$order_id]);
    }

    // ── Activity log ──────────────────────────────────────────────────────────
    $pdo->prepare("
        INSERT INTO activity_logs (user_id, action, details, ip_address)
        VALUES (?, ?, ?, ?)
    ")->execute([
        (int)$_SESSION['user_id'],
        $action,
        json_encode([
            'order_item_id' => $order_item_id,
            'order_id'      => $order_id,
            'partial_qty'   => $partial_qty,
            'total_qty'     => $total_qty,
            'reason'        => $reason ?: null,
            'waste_cost'    => $wasteCost ?? null,
            'refund_amount' => $refund_amount ?: null,
        ]),
        $_SERVER['REMOTE_ADDR'] ?? null,
    ]);

    $pdo->commit();

    // Return fresh item state
    $upd = $pdo->prepare("
        SELECT quantity_started, quantity_ready, quantity_voided, status
        FROM order_items
        WHERE order_item_id = ?
    ");
    $upd->execute([$order_item_id]);
    $updated = $upd->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success'          => true,
        'action'           => $action,
        'order_item_id'    => $order_item_id,
        'order_id'         => $order_id,
        'order_status'     => $newOrderStatus,
        'item_status'      => $updated['status'],
        'quantity_started' => (int)$updated['quantity_started'],
        'quantity_ready'   => (int)$updated['quantity_ready'],
        'quantity_voided'  => (int)$updated['quantity_voided'],
        'waste_cost'       => $wasteCost ?? null,
        'refund_id'        => $refundId ?? null,
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}