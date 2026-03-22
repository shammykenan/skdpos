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

        // Use model — no raw query in controller
        $order = getOrderById($pdo, $order_id);

        if (!$order) throw new Exception('Order not found.');

        if ($order['status'] !== 'ready') {
            throw new Exception('Order must be ready before it can be completed.');
        }

        if ($amount_paid < (float) $order['total_amount']) {
            throw new Exception('Payment amount is less than the order total.');
        }

        // ── Insert payment ────────────────────────────────────────────────────
        // cashier_id  = original order owner (keeps shift reports accurate)
        // processed_by = who physically processed the payment (audit trail)
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
            $order['cashier_id'],   // original cashier — used in shift reports
            $_SESSION['user_id'],   // who processed the payment — used for audit
        ]);

        updateOrderStatus($pdo, $order_id, 'completed', date('Y-m-d H:i:s'));

        // Sync item statuses — mark all non-cancelled items as 'completed'
        $pdo->prepare("
            UPDATE order_items
            SET status = 'completed'
            WHERE order_id = ? AND status != 'cancelled'
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

// ── Cancel order (cashier/admin, pending orders only) ────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    if (!hasRole('admin') && !hasRole('cashier')) {
        $_SESSION['error'] = 'You do not have permission to cancel orders.';
        header("Location: $baseUrl/index.php?page=manage_orders");
        exit;
    }

    $order_id = (int) $_POST['order_id'];

    try {
        $pdo->beginTransaction();

        // Use model — no raw query in controller
        $order = getOrderById($pdo, $order_id);

        if (!$order) throw new Exception('Order not found.');
        if ($order['status'] !== 'pending') {
            throw new Exception('Only pending orders can be cancelled.');
        }

        restoreIngredientsForOrder($pdo, $order_id);
        cancelOrder($pdo, $order_id);

        // Sync item statuses — mark all items as 'cancelled'
        $pdo->prepare("
            UPDATE order_items SET status = 'cancelled' WHERE order_id = ?
        ")->execute([$order_id]);

        // Free the table immediately — customer isn't coming back
        freeTableByOrderId($pdo, $order_id);

        $pdo->commit();
        $_SESSION['success'] = 'Order cancelled and ingredients restored.';

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'Failed to cancel order: ' . $e->getMessage();
    }

    header("Location: $baseUrl/index.php?page=manage_orders");
    exit;
}

// ── Fetch orders for page render — use helper, no raw query ──────────────────
$currentUserId = (int) $_SESSION['user_id'];

// getAllOrdersWithItems() handles draft exclusion + cashier filter via getAllOrders()
if (hasRole('admin') || hasRole('kitchen')) {
    $orders = getAllOrdersWithItems($pdo);
} else {
    $orders = getAllOrdersWithItems($pdo, $currentUserId);
}

require_once __DIR__ . '/../views/kitchen/manage_orders.php';