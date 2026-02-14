<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../core/posHelper.php';

requireRole(['admin', 'cashier']);

// Check if order_id is provided
$order_id = (int) ($_GET['order_id'] ?? 0);
if ($order_id <= 0) {
    $_SESSION['error'] = 'No order selected for receipt.';
    header("Location: index.php?page=manage_orders");
    exit;
}

// Get full order details including items, addons, and payment
$orderDetails = getOrderDetails($pdo, $order_id);

if (!$orderDetails) {
    $_SESSION['error'] = 'Order not found.';
    header("Location: index.php?page=manage_orders");
    exit;
}

// Extract for easier access in view
$order   = $orderDetails['order'];
$items   = $orderDetails['items'];
$payment = $orderDetails['payment'] ?? [];

// Load receipt view
require_once BASE_PATH . '/app/views/kitchen/receipt.php';
