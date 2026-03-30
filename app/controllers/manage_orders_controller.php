<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../models/pos.php';
require_once __DIR__ . '/../core/posHelper.php';
require_once __DIR__ . '/../core/itemsHelper.php';
require_once __DIR__ . '/../core/ingredientsHelper.php';
require_once __DIR__ . '/../core/authHelper.php';
require_once __DIR__ . '/../core/tableHelper.php';

requireRole(['admin', 'cashier', 'kitchen']);

$baseUrl = $_ENV['BASE_URL'] ?? '';

// ── Complete order (cashier/admin only — requires payment) ────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int) $_POST['order_id'];
    $status   = $_POST['status'];

    if ($status !== 'completed') {
        $_SESSION['error'] = 'Invalid status. Kitchen manages order progress via item tracking.';
        header("Location: $baseUrl/index.php?page=manage_orders");
        exit;
    }

    if (!hasRole('cashier') && !hasRole('admin')) {
        $_SESSION['error'] = 'Only cashier or admin can complete orders.';
        header("Location: $baseUrl/index.php?page=manage_orders");
        exit;
    }

    try {
        $pdo->beginTransaction();

        if (!isset($_POST['payment_method'], $_POST['amount_paid'])) {
            throw new Exception('Payment information is required.');
        }

        $payment_method = $_POST['payment_method'];
        $amount_paid    = (float) $_POST['amount_paid'];
        $change_amount  = (float) ($_POST['change_amount'] ?? 0);

        $order = getOrderById($pdo, $order_id);
        if (!$order) throw new Exception('Order not found.');
        if ($order['status'] !== 'ready') {
            throw new Exception('Order must be ready before it can be completed.');
        }
        if ($amount_paid < (float) $order['total_amount']) {
            throw new Exception('Payment amount is less than the order total.');
        }

        $stmt = $pdo->prepare("
            INSERT INTO payments
                (order_id, payment_method, amount_paid, change_amount, payment_status, cashier_id, processed_by)
            VALUES (?, ?, ?, ?, 'paid', ?, ?)
        ");
        $stmt->execute([
            $order_id,
            $payment_method,
            $amount_paid,
            $change_amount,
            $order['cashier_id'],
            $_SESSION['user_id'],
        ]);

        updateOrderStatus($pdo, $order_id, 'completed', date('Y-m-d H:i:s'));

        // Mark all non-terminal items as completed.
        // IMPORTANT: exclude 'cancelled' AND 'voided' — both are already terminal
        // and must not be overwritten with 'completed'.
        $pdo->prepare("
            UPDATE order_items
            SET status = 'completed'
            WHERE order_id = ?
              AND status NOT IN ('cancelled', 'voided')
        ")->execute([$order_id]);

        $pdo->commit();
        $_SESSION['success'] = 'Order completed and payment processed successfully.';

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = $e->getMessage();
    }

    header("Location: $baseUrl/index.php?page=manage_orders");
    exit;
}

// ── Cancel order (pending orders only) ───────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    if (!hasRole('admin') && !hasRole('cashier')) {
        $_SESSION['error'] = 'You do not have permission to cancel orders.';
        header("Location: $baseUrl/index.php?page=manage_orders");
        exit;
    }

    $order_id     = (int) $_POST['order_id'];
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