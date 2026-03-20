<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../core/authHelper.php';

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

    // ── 1. Get the item and its parent order ──────────────────────────
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
        throw new Exception('Order item not found.');
    }

    if (in_array($item['order_status'], ['completed', 'cancelled'])) {
        throw new Exception('Cannot update items on a ' . $item['order_status'] . ' order.');
    }

    // ── 2. Enforce forward-only: pending→preparing→ready ─────────────
    $progression = ['pending' => 0, 'preparing' => 1, 'ready' => 2];
    $currentRank = $progression[$item['current_status']] ?? -1;
    $newRank     = $progression[$new_status] ?? -1;

    if ($newRank <= $currentRank) {
        throw new Exception('Cannot move item backwards in status.');
    }

    // ── 3. Update the item status ─────────────────────────────────────
    $stmt = $pdo->prepare("
        UPDATE order_items SET status = ? WHERE order_item_id = ?
    ");
    $stmt->execute([$new_status, $order_item_id]);

    $order_id = $item['order_id'];

    // ── 4. Recalculate order status using SQL ─────────────────────────

    // FIRST: If at least one item is preparing/ready → set order to 'preparing'
    $pdo->prepare("
        UPDATE orders o
        SET o.status = 'preparing'
        WHERE o.order_id = :order_id
          AND o.status NOT IN ('completed', 'cancelled')
          AND EXISTS (
              SELECT 1 FROM order_items oi
              WHERE oi.order_id = o.order_id
                AND oi.status IN ('preparing', 'ready')
          )
    ")->execute([':order_id' => $order_id]);

    // SECOND: If ALL items are ready → upgrade order to 'ready' (overwrites 'preparing')
    $pdo->prepare("
        UPDATE orders o
        SET o.status = 'ready'
        WHERE o.order_id = :order_id
          AND o.status NOT IN ('completed', 'cancelled')
          AND NOT EXISTS (
              SELECT 1 FROM order_items oi
              WHERE oi.order_id = o.order_id
                AND oi.status != 'ready'
          )
    ")->execute([':order_id' => $order_id]);

    // ── 5. Fetch the final order status to return to JS ───────────────
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
        'message'       => 'Item updated. Order is now: ' . $orderStatus,
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}