<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../core/posHelper.php';
require_once __DIR__ . '/../core/itemsHelper.php'; // Add this for getOrderItems

header('Content-Type: application/json');

try {
    // Get all orders (basic info)
    $orders = getAllOrders($pdo);
    
    // Add items to each order (same as we did in the controller)
    foreach ($orders as &$order) {
        // Get items with notes
        $items = getOrderItems($pdo, $order['order_id']);
        $order['items'] = is_array($items) ? $items : [];
        
        // Add addons for each item
        foreach ($order['items'] as &$item) {
            $addons = getOrderItemAddons($pdo, $item['order_item_id']);
            $item['addons'] = is_array($addons) ? $addons : [];
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => $orders,
        'count' => count($orders),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}