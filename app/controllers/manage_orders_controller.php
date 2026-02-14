<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../models/pos.php';
require_once __DIR__ . '/../core/itemsHelper.php';
require_once __DIR__ . '/../core/posHelper.php';
require_once __DIR__ . '/../core/ingredientsHelper.php';
require_once __DIR__ . '/../core/authHelper.php';
requireRole(['admin', 'cashier', 'kitchen']);
$baseUrl = $_ENV['BASE_URL'] ?? '';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin','cashier', 'kitchen'])) {
    $_SESSION['error'] = 'Access denied please log in as an admin or cashier.';
    header("Location: index.php?page=login");
    exit;
}

// Update order status with payment processing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int) $_POST['order_id'];
    $status   = $_POST['status'];

    try {
        $pdo->beginTransaction();

        // If status is completed, process payment
        if ($status === 'completed') {
            // Validate payment fields
            if (!isset($_POST['payment_method']) || !isset($_POST['amount_paid'])) {
                throw new Exception('Payment information is required to complete the order.');
            }

            $payment_method = $_POST['payment_method'];
            $amount_paid = (float) $_POST['amount_paid'];
            $change_amount = (float) ($_POST['change_amount'] ?? 0);

            // Get order total to validate
            $stmt = $pdo->prepare("SELECT total_amount FROM orders WHERE order_id = ?");
            $stmt->execute([$order_id]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$order) {
                throw new Exception('Order not found.');
            }

            $total_amount = (float) $order['total_amount'];

            // Validate payment amount
            if ($amount_paid < $total_amount) {
                throw new Exception('Payment amount is less than the order total.');
            }

            // Insert payment record
            $stmt = $pdo->prepare("
                INSERT INTO payments (
                    order_id, 
                    payment_method, 
                    amount_paid, 
                    change_amount, 
                    payment_status, 
                    cashier_id
                ) VALUES (?, ?, ?, ?, 'paid', ?)
            ");
            
            $stmt->execute([
                $order_id,
                $payment_method,
                $amount_paid,
                $change_amount,
                $_SESSION['user_id']
            ]);

            // Update order status to completed
            updateOrderStatus($pdo, $order_id, 'completed', date('Y-m-d H:i:s'));

            $pdo->commit();
            $_SESSION['success'] = 'Order completed and payment processed successfully.';
        } else {
            // For non-completed status updates, just update the status
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

// Cancel order and restore ingredients
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

// Fetch all orders
$orders = getAllOrders($pdo);

// Fetch items and addons for each order
foreach ($orders as &$order) {
    $order['items'] = getOrderItems($pdo, $order['order_id']);
    foreach ($order['items'] as &$item) {
        $item['addons'] = getOrderItemAddons($pdo, $item['order_item_id']);
    }
}

require_once __DIR__ . '/../views/kitchen/manage_orders.php';