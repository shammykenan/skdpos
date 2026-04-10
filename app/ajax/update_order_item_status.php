<?php
ini_set('display_errors', 1); 
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ob_start(); // buffer ANY stray output

session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../core/authHelper.php';
require_once __DIR__ . '/../core/ingredientsHelper.php';
require_once __DIR__ . '/../core/addonsHelper.php';

ob_clean(); // discard any HTML that snuck out from the requires above
header('Content-Type: application/json');

if (!hasRole('kitchen') && !hasRole('admin')) {
    echo json_encode(['success' => false, 'error' => 'Access denied.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit;
}

$order_item_id = (int) ($_POST['order_item_id'] ?? 0);
$new_status    = trim($_POST['status'] ?? '');
$valid         = ['preparing', 'ready'];

if (!$order_item_id || !in_array($new_status, $valid)) {
    echo json_encode(['success' => false, 'error' => 'Invalid item or status.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // ── 1. Fetch current item + order state ───────────────────────────────────
    $stmt = $pdo->prepare("
        SELECT oi.order_item_id, oi.order_id, oi.status AS current_status,
               o.status AS order_status
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.order_id
        WHERE oi.order_item_id = ?
    ");
    $stmt->execute([$order_item_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Order item not found.']);
        exit;
    }

    // ── Lock the order row to prevent concurrent modifications ─────────────────
    $lockStmt = $pdo->prepare("
        SELECT order_id FROM orders WHERE order_id = ? FOR UPDATE
    ");
    $lockStmt->execute([$item['order_id']]);

    if (in_array($item['order_status'], ['completed', 'cancelled', 'voided'])) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Cannot update items on a ' . $item['order_status'] . ' order.']);
        exit;
    }

    // ── 2. Forward-only check ─────────────────────────────────────────────────
    $progression = ['pending' => 0, 'preparing' => 1, 'ready' => 2];
    $currentRank = $progression[$item['current_status']] ?? -1;
    $newRank     = $progression[$new_status] ?? -1;

    if ($newRank <= $currentRank) {
        $pdo->rollBack();
        echo json_encode([
            'success'       => true,
            'order_item_id' => $order_item_id,
            'item_status'   => $item['current_status'],
            'order_id'      => $item['order_id'],
            'order_status'  => $item['order_status'],
            'already_done'  => true,
        ]);
        exit;
    }

    // ── 3. Update item status ─────────────────────────────────────────────────
    $pdo->prepare("UPDATE order_items SET status = ? WHERE order_item_id = ?")
        ->execute([$new_status, $order_item_id]);

    // ── 3b. Deduct ingredients when kitchen starts preparing ──────────────────
    if ($new_status === 'preparing') {
        $stmt = $pdo->prepare("SELECT item_id, variant_id, quantity FROM order_items WHERE order_item_id = ?");
        $stmt->execute([$order_item_id]);
        $orderItem = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($orderItem) {
            deductIngredientsForItem(
                $pdo,
                (int) $orderItem['item_id'],
                (int) $orderItem['quantity'],
                $orderItem['variant_id'] !== null ? (int) $orderItem['variant_id'] : null
            );

            $addonStmt = $pdo->prepare("SELECT addon_id, quantity FROM order_item_addons WHERE order_item_id = ?");
            $addonStmt->execute([$order_item_id]);
            $itemQty = (int) $orderItem['quantity'];
            foreach ($addonStmt->fetchAll(PDO::FETCH_ASSOC) as $ad) {
                try {
                    deductIngredientsForAddon($pdo, (int) $ad['addon_id'], (int) $ad['quantity'] * $itemQty);
                } catch (Exception $e) {
                    error_log('Addon deduction failed: addon_id=' . $ad['addon_id'] . ' err=' . $e->getMessage());
                    throw $e; // re-throw so the transaction still rolls back properly
                }
            }
        }
    }

    $order_id = $item['order_id'];

    // ── 4. Recalculate order status ───────────────────────────────────────────
    // CRITICAL: Only count items that are NOT cancelled or voided.
    // Cancelled/voided items are invisible to the status logic.
    $statusCountStmt = $pdo->prepare("
        SELECT status, COUNT(*) AS cnt
        FROM order_items
        WHERE order_id = ?
          AND status NOT IN ('cancelled', 'voided')
        GROUP BY status
    ");
    $statusCountStmt->execute([$order_id]);
    $counts = [];
    foreach ($statusCountStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $counts[$row['status']] = (int) $row['cnt'];
    }

    $activeTotal    = array_sum($counts);
    $pendingCount   = $counts['pending']   ?? 0;
    $preparingCount = $counts['preparing'] ?? 0;
    $readyCount     = $counts['ready']     ?? 0;

    $guard = "WHERE order_id = ? AND status NOT IN ('completed', 'cancelled', 'voided')";

    if ($activeTotal === 0) {
        // Defensive: all active items gone — shouldn't happen from this endpoint
        $voidCheck = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE order_id = ? AND status = 'voided'");
        $voidCheck->execute([$order_id]);
        $terminal = (int) $voidCheck->fetchColumn() > 0 ? 'voided' : 'cancelled';
        $pdo->prepare("UPDATE orders SET status = ? $guard")->execute([$terminal, $order_id]);

    } elseif ($readyCount === $activeTotal) {
        // Every active item is ready
        $pdo->prepare("UPDATE orders SET status = 'ready' $guard")->execute([$order_id]);

    } elseif ($preparingCount > 0 || ($readyCount > 0 && $pendingCount > 0)) {
        // Mix of states
        $pdo->prepare("UPDATE orders SET status = 'preparing' $guard")->execute([$order_id]);

    } elseif ($pendingCount === $activeTotal) {
        // All active items are pending
        $pdo->prepare("UPDATE orders SET status = 'pending' $guard")->execute([$order_id]);
    }

    // ── 5. Fetch final order status ───────────────────────────────────────────
    $stmt = $pdo->prepare("SELECT status FROM orders WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $orderStatus = $stmt->fetchColumn();

    $pdo->commit();

    echo json_encode([
        'success'       => true,
        'order_item_id' => $order_item_id,
        'item_status'   => $new_status,
        'order_id'      => $order_id,
        'order_status'  => $orderStatus,
        'already_done'  => false,
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}