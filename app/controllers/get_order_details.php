<?php
// TEMPORARY DEBUG VERSION - remove error suppression to see what's wrong
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Try each require and report if it fails
$db_path = __DIR__ . '/../../config/db.php';
$helper_path = __DIR__ . '/../core/posHelper.php';

if (!file_exists($db_path)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'db.php not found at: ' . $db_path]);
    exit;
}

if (!file_exists($helper_path)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'posHelper.php not found at: ' . $helper_path]);
    exit;
}

require_once $db_path;
require_once $helper_path;

// Comment out requireRole temporarily to rule out session issues
// requireRole(['admin', 'cashier', 'kitchen']);

header('Content-Type: application/json');

$order_id = (int)($_GET['order_id'] ?? 0);
if (!$order_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID - got: ' . ($_GET['order_id'] ?? 'nothing')]);
    exit;
}

// Check if getOrderDetails function exists
if (!function_exists('getOrderDetails')) {
    echo json_encode(['success' => false, 'message' => 'getOrderDetails() function not found in posHelper.php']);
    exit;
}

$data = getOrderDetails($pdo, $order_id);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Order not found for ID: ' . $order_id]);
    exit;
}

echo json_encode(['success' => true, 'data' => $data]);