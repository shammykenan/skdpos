<?php
// app/controllers/manage_orders.php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../models/pos.php';
require_once __DIR__ . '/../core/posHelper.php';
require_once __DIR__ . '/../core/itemsHelper.php';
require_once __DIR__ . '/../core/ingredientsHelper.php';
require_once __DIR__ . '/../core/authHelper.php';
require_once __DIR__ . '/../core/tablehelper.php';

requireRole(['admin', 'cashier', 'kitchen']);
require_once __DIR__ . '/../core/csrfHelper.php';
csrf_require('html');

// Base URL configuration for InfinityFree
// Update this with your actual domain on InfinityFree

// ── Cancel order (pending orders only) ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    if (!hasRole('admin') && !hasRole('cashier')) {
        $_SESSION['error'] = 'You do not have permission to cancel orders.';
        header("Location: $baseUrl/index.php?page=manage_orders");
        exit;
    }

    $order_id      = (int) $_POST['order_id'];
    $cancel_reason = trim($_POST['cancel_reason'] ?? '');

    if ($cancel_reason === '') {
        $_SESSION['error'] = 'A cancellation reason is required.';
        header("Location: $baseUrl/index.php?page=manage_orders");
        exit;
    }

    try {
        $pdo->beginTransaction();

        $order = getOrderById($pdo, $order_id);
        if (!$order) throw new Exception('Order not found.');
        if ($order['status'] !== 'pending') {
            throw new Exception('Only pending orders can be cancelled.');
        }

        // Restore stock for any items that reached 'preparing' or 'ready'.
        // For a pending order this is a no-op (stock is deducted only at 'preparing'),
        // but we call it anyway for correctness in case the order state is mixed.
        restoreIngredientsForOrder($pdo, $order_id);

        // Write cancel_reason to the order row before calling cancelOrder()
        $pdo->prepare("
            UPDATE orders SET cancel_reason = ? WHERE order_id = ?
        ")->execute([$cancel_reason, $order_id]);

        cancelOrder($pdo, $order_id);

        // Write cancel_reason to every item row + mark all as cancelled
        $pdo->prepare("
            UPDATE order_items
            SET status = 'cancelled', cancel_reason = ?
            WHERE order_id = ?
        ")->execute([$cancel_reason, $order_id]);

        freeTableByOrderId($pdo, $order_id);

        // Audit log
        $pdo->prepare("
            INSERT INTO activity_logs (user_id, action, details, ip_address)
            VALUES (?, 'cancel_order', ?, ?)
        ")->execute([
            (int) $_SESSION['user_id'],
            json_encode([
                'order_id'      => $order_id,
                'order_number'  => $order['order_number'] ?? '',
                'reason'        => $cancel_reason,
                'performed_by'  => (int) $_SESSION['user_id'],
                'stock_note'    => 'Order was pending — no stock had been deducted. Nothing restored.',
            ]),
            $_SERVER['REMOTE_ADDR'] ?? null,
        ]);

        $pdo->commit();
        $_SESSION['success'] = 'Order cancelled successfully.';

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'Failed to cancel order: ' . $e->getMessage();
    }

    header("Location: $baseUrl/index.php?page=manage_orders");
    exit;
}

// ── Fetch orders for page render ──────────────────────────────────────────────
$currentUserId = (int) $_SESSION['user_id'];

if (hasRole('admin') || hasRole('kitchen')) {
    $orders = getAllOrdersWithItems($pdo);
} else {
    $orders = getAllOrdersWithItems($pdo, $currentUserId);
}

require_once __DIR__ . '/../views/kitchen/manage_orders.php';