<?php
require_once __DIR__ . '/../../config/db.php';


function createOrder(PDO $pdo, int $user_id, ?string $customer_name, ?string $table_number, string $order_type, string $order_source, float $subtotal, float $discount, float $total_amount)
{
    $stmt = $pdo->prepare("SELECT user_id, role FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user || !in_array($user['role'], ['admin', 'cashier'])) {
        throw new Exception("Invalid user for creating orders.");
    }

    do {
        $order_number = 'SKD' . date('YmdHis') . rand(1000, 9999);
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE order_number = ?");
        $stmt->execute([$order_number]);
        $count = $stmt->fetchColumn();
    } while ($count > 0);

    $stmt = $pdo->prepare("
        INSERT INTO orders 
        (cashier_id, customer_name, table_number, order_type, order_source, subtotal, discount, total_amount, order_number) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$user_id, $customer_name, $table_number, $order_type, $order_source, $subtotal, $discount, $total_amount, $order_number]);

    return $pdo->lastInsertId();
}

function createOrderItem($pdo, $order_id, $item_id, $quantity, $unit_price) {
    $subtotal = $quantity * $unit_price;
    $sql = "INSERT INTO order_items (order_id, item_id, quantity, unit_price, subtotal) 
            VALUES (:order_id, :item_id, :quantity, :unit_price, :subtotal)";
    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute([
        ':order_id' => $order_id,
        ':item_id'  => $item_id,
        ':quantity' => $quantity,
        ':unit_price' => $unit_price,
        ':subtotal' => $subtotal
    ]);
    return $success ? $pdo->lastInsertId() : false;
}

function createOrderItemAddon($pdo, $order_item_id, $addon_id, $quantity, $price) {
    $sql = "INSERT INTO order_item_addons (order_item_id, addon_id, quantity, price) 
            VALUES (:order_item_id, :addon_id, :quantity, :price)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        ':order_item_id' => $order_item_id,
        ':addon_id'      => $addon_id,
        ':quantity'      => $quantity,
        ':price'         => $price
    ]);
}
function updateOrderStatus(PDO $pdo, int $order_id, string $status) {
    if ($status === 'completed') {
        $sql = "UPDATE orders 
                SET status = ?, completed_at = NOW() 
                WHERE order_id = ?";
    } else {
        $sql = "UPDATE orders 
                SET status = ? 
                WHERE order_id = ?";
    }

    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$status, $order_id]);
}

function cancelOrder(PDO $pdo, int $order_id) {
    $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE order_id = ?");
    return $stmt->execute([$order_id]);
}
