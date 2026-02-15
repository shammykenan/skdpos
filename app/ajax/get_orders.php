<?php
// app/ajax/get_orders.php

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../core/posHelper.php';
require_once __DIR__ . '/../core/itemsHelper.php';

// Tell browser this is JSON
header('Content-Type: application/json');

try {
    // Get all orders
    $orders = getAllOrders($pdo);
    
    // Add items and addons to each order
    foreach ($orders as &$order) {
        $order['items'] = getOrderItems($pdo, $order['order_id']);
        
        foreach ($order['items'] as &$item) {
            $item['addons'] = getOrderItemAddons($pdo, $item['order_item_id']);
        }
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'data' => $orders,
        'count' => count($orders),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}