<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../models/pos.php';
require_once __DIR__ . '/../core/posHelper.php';
require_once __DIR__ . '/../core/itemsHelper.php';
require_once __DIR__ . '/../core/ingredientsHelper.php';
require_once __DIR__ . '/../core/authHelper.php';
requireRole(['admin', 'cashier', 'kitchen']);
$baseUrl = $_ENV['BASE_URL'] ?? '';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'cashier', 'kitchen'])) {
    $_SESSION['error'] = 'Access denied.';
    header("Location: index.php?page=login");
    exit;
}

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

        if (!isset($_POST['payment_method']) || !isset($_POST['amount_paid'])) {
            throw new Exception('Payment information is required.');
        }

        $payment_method = $_POST['payment_method'];
        $amount_paid    = (float) $_POST['amount_paid'];
        $change_amount  = (float) ($_POST['change_amount'] ?? 0);

        $stmt = $pdo->prepare("SELECT total_amount, status FROM orders WHERE order_id = ?");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) throw new Exception('Order not found.');

        if ($order['status'] !== 'ready') {
            throw new Exception('Order must be ready before it can be completed.');
        }

        if ($amount_paid < (float) $order['total_amount']) {
            throw new Exception('Payment amount is less than the order total.');
        }

        $stmt = $pdo->prepare("
            INSERT INTO payments (order_id, payment_method, amount_paid, change_amount, payment_status, cashier_id)
            VALUES (?, ?, ?, ?, 'paid', ?)
        ");
        $stmt->execute([$order_id, $payment_method, $amount_paid, $change_amount, $_SESSION['user_id']]);

        updateOrderStatus($pdo, $order_id, 'completed', date('Y-m-d H:i:s'));

        // Sync item statuses — mark all non-cancelled items as 'ready' (terminal kitchen state)
        $pdo->prepare("
            UPDATE order_items
            SET status = 'ready'
            WHERE order_id = ?
              AND status NOT IN ('ready', 'cancelled')
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

        $stmt = $pdo->prepare("SELECT status FROM orders WHERE order_id = ?");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) throw new Exception('Order not found.');
        if ($order['status'] !== 'pending') {
            throw new Exception('Only pending orders can be cancelled.');
        }

        restoreIngredientsForOrder($pdo, $order_id);
        cancelOrder($pdo, $order_id);

        // Sync item statuses — mark all items as 'cancelled'
        $pdo->prepare("
            UPDATE order_items
            SET status = 'cancelled'
            WHERE order_id = ?
        ")->execute([$order_id]);

        $pdo->commit();
        $_SESSION['success'] = 'Order cancelled and ingredients restored.';

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'Failed to cancel order: ' . $e->getMessage();
    }

    header("Location: $baseUrl/index.php?page=manage_orders");
    exit;
}

// ── Fetch orders for initial page render ─────────────────────────────────────
$currentUserId = (int) $_SESSION['user_id'];

$sql    = "SELECT o.*, u.full_name AS cashier_name FROM orders o LEFT JOIN users u ON o.cashier_id = u.user_id";
$params = [];

if (!hasRole('admin') && !hasRole('kitchen')) {
    $sql .= " WHERE o.cashier_id = :cashier_id";
    $params[':cashier_id'] = $currentUserId;
}

$sql .= " ORDER BY o.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($orders as &$order) {
    $details = getOrderDetails($pdo, $order['order_id']);
    $order['items'] = $details ? $details['items'] : [];
}
unset($order);

require_once __DIR__ . '/../views/kitchen/manage_orders.php';