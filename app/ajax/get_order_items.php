<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../core/authHelper.php';

header('Content-Type: application/json');

requireRole(['admin', 'cashier', 'kitchen']);

$order_id = (int) ($_GET['order_id'] ?? 0);

if (!$order_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID.']);
    exit;
}

try {
    // Fetch order info
    $stmt = $pdo->prepare("
        SELECT order_id, order_number, status, order_type
        FROM orders
        WHERE order_id = ?
        LIMIT 1
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found.']);
        exit;
    }

    // Fetch items with fresh status
    $stmt = $pdo->prepare("
        SELECT
            oi.order_item_id,
            oi.item_id,
            oi.quantity,
            oi.unit_price,
            oi.subtotal,
            oi.status        AS item_status,
            oi.item_notes,
            mi.item_name
        FROM order_items oi
        JOIN menu_items mi ON oi.item_id = mi.item_id
        WHERE oi.order_id = ?
        ORDER BY oi.order_item_id ASC
    ");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch addons for each item
    foreach ($items as &$item) {
        $stmt = $pdo->prepare("
            SELECT oia.order_item_addon_id, oia.quantity, oia.price,
                   a.addon_name
            FROM order_item_addons oia
            JOIN addons a ON oia.addon_id = a.addon_id
            WHERE oia.order_item_id = ?
        ");
        $stmt->execute([$item['order_item_id']]);
        $item['addons'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($item);

    echo json_encode([
        'success' => true,
        'order'   => $order,
        'items'   => $items,
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}