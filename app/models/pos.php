<?php
require_once __DIR__ . '/../../config/db.php';


function createOrder(PDO $pdo, int $cashier_id, ?string $customer_name, ?string $table_number,string $order_type, string $order_source, float $subtotal, float $discount,float $total_amount): int {
    // Generate a unique order number: ORD-YYYYMMDD-XXXXXX
    $order_number = 'SKD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

    $sql = "INSERT INTO orders (order_number, cashier_id, customer_name, table_number,
                 order_type, order_source, subtotal, discount, total_amount, status) VALUES
                (:order_number, :cashier_id, :customer_name, :table_number,
                 :order_type, :order_source, :subtotal, :discount, :total_amount, 'pending')";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':order_number'  => $order_number,
        ':cashier_id'    => $cashier_id,
        ':customer_name' => $customer_name,
        ':table_number'  => $table_number,
        ':order_type'    => $order_type,
        ':order_source'  => $order_source,
        ':subtotal'      => $subtotal,
        ':discount'      => $discount,
        ':total_amount'  => $total_amount,
    ]);

    return (int) $pdo->lastInsertId();
}

function createOrderItem(PDO $pdo, int $order_id, int $item_id, int $quantity, float $unit_price, ?string $item_note = null): int {
    $subtotal = $unit_price * $quantity;

    // Sanitise: empty string → null so the column stays clean
    $note = ($item_note !== null && trim($item_note) !== '')
        ? trim($item_note)
        : null;

    $sql = "INSERT INTO order_items (order_id, item_id, quantity, unit_price, subtotal, item_notes, status) 
            VALUES (:order_id, :item_id, :quantity, :unit_price, :subtotal, :item_notes, 'pending')";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':order_id'   => $order_id,
        ':item_id'    => $item_id,
        ':quantity'   => $quantity,
        ':unit_price' => $unit_price,
        ':subtotal'   => $subtotal,
        ':item_notes' => $note,
    ]);

    return (int) $pdo->lastInsertId();
}
function createOrderItemAddon(PDO $pdo, int $order_item_id, int $addon_id, int $quantity, float $price): int {
    $sql = "INSERT INTO order_item_addons (order_item_id, addon_id, quantity, price)
            VALUES (:order_item_id, :addon_id, :quantity, :price)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':order_item_id' => $order_item_id,
        ':addon_id'      => $addon_id,
        ':quantity'      => $quantity,
        ':price'         => $price,
    ]);

    return (int) $pdo->lastInsertId();
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
