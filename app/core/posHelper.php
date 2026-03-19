<?php
require_once __DIR__ . '/../../config/db.php';

function generateUniqueOrderNumber($pdo) {
    do {
        $number = 'ORD' . date('Ymd') . rand(1000, 9999); // e.g. ORD202602089694
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE order_number = ?");
        $stmt->execute([$number]);
        $count = $stmt->fetchColumn();
    } while ($count > 0); // repeat if number already exists

    return $number;
}
function getAllOrders(PDO $pdo, ?int $cashierId = null): array {
    $sql = "
        SELECT 
            o.*,
            u.full_name AS cashier_name
        FROM orders o
        LEFT JOIN users u ON o.cashier_id = u.user_id
    ";

    $params = [];

    if ($cashierId !== null) {
        $sql .= " WHERE o.cashier_id = :cashier_id";
        $params[':cashier_id'] = $cashierId;
    }

    $sql .= " ORDER BY o.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function getOrderById(PDO $pdo, int $order_id) {
    $sql = "SELECT * FROM orders WHERE order_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $order_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function getOrderDetails(PDO $pdo, int $order_id): ?array {
    // Get main order info
    $stmt = $pdo->prepare("
        SELECT o.*, u.username AS cashier_name
        FROM orders o
        JOIN users u ON o.cashier_id = u.user_id
        WHERE o.order_id = ?
        LIMIT 1
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) return null;

    // Get order items
    $stmt = $pdo->prepare("
        SELECT oi.*, mi.item_name, mi.price AS unit_price
        FROM order_items oi
        JOIN menu_items mi ON oi.item_id = mi.item_id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Add addons for each item
    foreach ($items as &$item) {
        $stmt = $pdo->prepare("
            SELECT oia.*, a.addon_name, a.price AS unit_price
            FROM order_item_addons oia
            JOIN addons a ON oia.addon_id = a.addon_id
            WHERE oia.order_item_id = ?
        ");
        $stmt->execute([$item['order_item_id']]);
        $item['addons'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get payment info
    $stmt = $pdo->prepare("
        SELECT *
        FROM payments
        WHERE order_id = ?
        LIMIT 1
    ");
    $stmt->execute([$order_id]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    return [
        'order' => $order,
        'items' => $items,
        'payment' => $payment
    ];
}
