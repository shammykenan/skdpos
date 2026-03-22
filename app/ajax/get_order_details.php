<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../core/authHelper.php';
require_once __DIR__ . '/../core/posHelper.php';
require_once __DIR__ . '/../core/itemsHelper.php';

header('Content-Type: application/json');

requireRole(['admin', 'cashier', 'kitchen']);

$order_id = (int) ($_GET['order_id'] ?? 0);

if (!$order_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID.']);
    exit;
}

$data = getOrderDetails($pdo, $order_id);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Order not found.']);
    exit;
}

echo json_encode(['success' => true, 'data' => $data]);