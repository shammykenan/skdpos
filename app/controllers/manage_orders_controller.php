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

// ── Update order status ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int) $_POST['order_id'];
    $status   = $_POST['status'];

    try {
        $pdo->beginTransaction();

        if ($status === 'completed') {
            if (!isset($_POST['payment_method']) || !isset($_POST['amount_paid'])) {
                throw new Exception('Payment information is required.');
            }

            $payment_method = $_POST['payment_method'];
            $amount_paid    = (float) $_POST['amount_paid'];
            $change_amount  = (float) ($_POST['change_amount'] ?? 0);

            $stmt = $pdo->prepare("SELECT total_amount FROM orders WHERE order_id = ?");
            $stmt->execute([$order_id]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$order) throw new Exception('Order not found.');
            if ($amount_paid < (float) $order['total_amount']) {
                throw new Exception('Payment amount is less than the order total.');
            }

            $stmt = $pdo->prepare("
                INSERT INTO payments (order_id, payment_method, amount_paid, change_amount, payment_status, cashier_id)
                VALUES (?, ?, ?, ?, 'paid', ?)
            ");
            $stmt->execute([$order_id, $payment_method, $amount_paid, $change_amount, $_SESSION['user_id']]);

            updateOrderStatus($pdo, $order_id, 'completed', date('Y-m-d H:i:s'));
            $pdo->commit();

            $_SESSION['success'] = 'Order completed and payment processed successfully.';
            header("Location: index.php?page=manage_orders");
            exit;

        } else {
            updateOrderStatus($pdo, $order_id, $status, null);
            $pdo->commit();
            $_SESSION['success'] = 'Order status updated successfully.';
        }

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = $e->getMessage();
    }

    header("Location: $baseUrl/index.php?page=manage_orders");
    exit;
}

// ── Cancel order ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    $order_id = (int) $_POST['order_id'];

    try {
        $pdo->beginTransaction();
        restoreIngredientsForOrder($pdo, $order_id);
        cancelOrder($pdo, $order_id);
        $pdo->commit();
        $_SESSION['success'] = 'Order cancelled and ingredients restored.';
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'Failed to cancel order: ' . $e->getMessage();
    }

    header("Location: $baseUrl/index.php?page=manage_orders");
    exit;
}

// ── Fetch orders ──
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

// Attach items
foreach ($orders as &$order) {
    $details = getOrderDetails($pdo, $order['order_id']);
    $order['items'] = $details ? $details['items'] : [];
}
unset($order);

require_once __DIR__ . '/../views/kitchen/manage_orders.php';