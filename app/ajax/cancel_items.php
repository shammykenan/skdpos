<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../core/authHelper.php';
require_once __DIR__ . '/../core/ingredientsHelper.php';

header('Content-Type: application/json');

// ── Role check ────────────────────────────────────────────────────────────────
// cancel_item → admin OR cashier
// void_item   → admin only
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

$order_item_id = (int) ($_POST['order_item_id'] ?? 0);
$action        = trim($_POST['action'] ?? ''); // 'cancel_item' | 'void_item'

if (!$order_item_id || !in_array($action, ['cancel_item', 'void_item'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid parameters.']);
    exit;
}

// void_item is admin-only even if cashier somehow calls it
if ($action === 'void_item' && !$isAdmin) {
    echo json_encode(['success' => false, 'error' => 'Only admins can void items.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // ── Fetch the item and its parent order ───────────────────────────────────
    $stmt = $pdo->prepare("
        SELECT
            oi.order_item_id,
            oi.order_id,
            oi.item_id,
            oi.variant_id,
            oi.quantity,
            oi.subtotal,
            oi.status          AS item_status,
            o.status           AS order_status,
            o.subtotal         AS order_subtotal,
            o.total_amount     AS order_total,
            o.discount         AS order_discount
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

    // ── Guard: order must not already be completed or cancelled ───────────────
    if (in_array($item['order_status'], ['completed', 'cancelled'])) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'error'   => 'Cannot modify items on a ' . $item['order_status'] . ' order.',
        ]);
        exit;
    }

    // ── Guard: item must not already be in a terminal state ───────────────────
    if (in_array($item['item_status'], ['cancelled', 'voided', 'completed'])) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'error'   => 'Item is already ' . $item['item_status'] . '.',
        ]);
        exit;
    }

    // ── Action-specific validation ────────────────────────────────────────────
    if ($action === 'cancel_item' && $item['item_status'] !== 'pending') {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'error'   => 'Only pending items can be cancelled. This item is ' . $item['item_status'] . '.',
        ]);
        exit;
    }

    if ($action === 'void_item' && $item['item_status'] !== 'ready') {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'error'   => 'Only ready items can be voided. This item is ' . $item['item_status'] . '.',
        ]);
        exit;
    }

    $order_id        = (int) $item['order_id'];
    $new_item_status = $action === 'cancel_item' ? 'cancelled' : 'voided';

    // ── 1. Update item status ─────────────────────────────────────────────────
    $pdo->prepare("UPDATE order_items SET status = ? WHERE order_item_id = ?")
        ->execute([$new_item_status, $order_item_id]);

    // ── 2. Ingredient handling ────────────────────────────────────────────────
    // cancel_item (pending): ingredients were never deducted (deduction happens
    //   at 'preparing'). Nothing to restore.
    //
    // void_item (ready): ingredients were already consumed by the kitchen.
    //   Do NOT restore — food was physically prepared; it's a waste write-off.
    //   Log the cost to activity_logs for management visibility.

    if ($action === 'void_item') {
        $costStmt = $pdo->prepare("
            SELECT
                oi.item_name_snapshot,
                oi.quantity,
                COALESCE(mv.cost_price, mi.cost_price) AS unit_cost
            FROM order_items oi
            JOIN menu_items mi ON oi.item_id = mi.item_id
            LEFT JOIN menu_variants mv ON oi.variant_id = mv.variant_id
            WHERE oi.order_item_id = ?
        ");
        $costStmt->execute([$order_item_id]);
        $costRow   = $costStmt->fetch(PDO::FETCH_ASSOC);
        $wasteCost = $costRow
            ? round((float)$costRow['unit_cost'] * (int)$costRow['quantity'], 2)
            : 0.00;

        $itemLabel = $costRow['item_name_snapshot'] ?? ('Item #' . $order_item_id);

        $logStmt = $pdo->prepare("
            INSERT INTO activity_logs (user_id, action, details, ip_address)
            VALUES (?, 'void_order_item', ?, ?)
        ");
        $logStmt->execute([
            (int) $_SESSION['user_id'],
            json_encode([
                'order_item_id' => $order_item_id,
                'order_id'      => $order_id,
                'item'          => $itemLabel,
                'qty'           => (int)$item['quantity'],
                'waste_cost'    => $wasteCost,
                'note'          => 'Ingredients consumed — not restored. Cost written off as waste.',
            ]),
            $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }

    // ── 3. Recalculate order totals ───────────────────────────────────────────
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

    // ── 4. Recalculate order status based on remaining active items ───────────
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

    if ($activeTotal === 0) {
        // All items cancelled/voided — cancel the whole order
        $newOrderStatus = 'cancelled';
        $pdo->prepare("
            UPDATE orders SET status = 'cancelled' WHERE order_id = ?
        ")->execute([$order_id]);

        // Free the table
        $pdo->prepare("
            UPDATE tables SET status = 'available', current_order_id = NULL
            WHERE current_order_id = ?
        ")->execute([$order_id]);

        $pdo->prepare("
            INSERT INTO activity_logs (user_id, action, details, ip_address)
            VALUES (?, 'auto_cancel_order', ?, ?)
        ")->execute([
            (int) $_SESSION['user_id'],
            json_encode([
                'order_id' => $order_id,
                'reason'   => 'All items were cancelled or voided.',
            ]),
            $_SERVER['REMOTE_ADDR'] ?? null,
        ]);

    } elseif ($readyCount === $activeTotal) {
        $newOrderStatus = 'ready';
        $pdo->prepare("
            UPDATE orders SET status = 'ready'
            WHERE order_id = ? AND status NOT IN ('completed','cancelled')
        ")->execute([$order_id]);

    } elseif ($preparingCount > 0 || $readyCount > 0) {
        $newOrderStatus = 'preparing';
        $pdo->prepare("
            UPDATE orders SET status = 'preparing'
            WHERE order_id = ? AND status NOT IN ('completed','cancelled')
        ")->execute([$order_id]);

    } elseif ($pendingCount === $activeTotal) {
        $newOrderStatus = 'pending';
        $pdo->prepare("
            UPDATE orders SET status = 'pending'
            WHERE order_id = ? AND status NOT IN ('completed','cancelled')
        ")->execute([$order_id]);
    }

    $pdo->commit();

    echo json_encode([
        'success'        => true,
        'action'         => $action,
        'order_item_id'  => $order_item_id,
        'item_status'    => $new_item_status,
        'order_id'       => $order_id,
        'order_status'   => $newOrderStatus,
        'new_total'      => $newTotalAmount,
        'new_subtotal'   => $newSubtotal,
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}