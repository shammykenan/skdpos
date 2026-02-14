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
function getAllOrders(PDO $pdo) {
    $sql = "SELECT o.*, u.username AS cashier_name FROM orders o JOIN users u ON o.cashier_id = u.user_id ORDER BY created_at DESC";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
