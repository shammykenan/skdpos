<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../core/tableHelper.php';

/**
 * Create an order.
 * For dine-in orders with a valid $table_id, the table is marked
 * occupied inside the same caller transaction — do NOT commit before
 * calling this if you need atomicity.
 */
function createOrder(
    PDO     $pdo,
    int     $cashier_id,
    ?string $customer_name,
    ?string $table_number,   // display label (kept for backward compat / receipts)
    string  $order_type,
    string  $order_source,
    float   $subtotal,
    float   $discount,
    float   $total_amount,
    ?int    $table_id = null // FK to tables.table_id
): int {
    $order_number = 'SKD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

    $stmt = $pdo->prepare("
        INSERT INTO orders
            (order_number, cashier_id, customer_name, table_number, table_id,
             order_type, order_source, subtotal, discount, total_amount, status)
        VALUES
            (:order_number, :cashier_id, :customer_name, :table_number, :table_id,
             :order_type, :order_source, :subtotal, :discount, :total_amount, 'pending')
    ");

    $stmt->execute([
        ':order_number'  => $order_number,
        ':cashier_id'    => $cashier_id,
        ':customer_name' => $customer_name,
        ':table_number'  => $table_number,
        ':table_id'      => $table_id,
        ':order_type'    => $order_type,
        ':order_source'  => $order_source,
        ':subtotal'      => $subtotal,
        ':discount'      => $discount,
        ':total_amount'  => $total_amount,
    ]);

    $order_id = (int) $pdo->lastInsertId();

    // ── Occupy the table immediately for dine-in orders ──────────────
    if ($order_type === 'dine_in' && $table_id !== null) {
        $occupied = occupyTable($pdo, $table_id, $order_id);
        if (!$occupied) {
            // Table was grabbed by another order between the dropdown load and now
            throw new Exception('That table was just taken. Please select another table.');
        }
    }

    return $order_id;
}

function createOrderItem(
    PDO     $pdo,
    int     $order_id,
    int     $item_id,
    int     $quantity,
    float   $unit_price,
    ?string $item_note = null
): int {
    $subtotal = $unit_price * $quantity;
    $note     = ($item_note !== null && trim($item_note) !== '') ? trim($item_note) : null;

    $stmt = $pdo->prepare("
        INSERT INTO order_items
            (order_id, item_id, quantity, unit_price, subtotal, item_notes, status)
        VALUES
            (:order_id, :item_id, :quantity, :unit_price, :subtotal, :item_notes, 'pending')
    ");
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

function createOrderItemAddon(
    PDO   $pdo,
    int   $order_item_id,
    int   $addon_id,
    int   $quantity,
    float $price
): int {
    $stmt = $pdo->prepare("
        INSERT INTO order_item_addons (order_item_id, addon_id, quantity, price)
        VALUES (:order_item_id, :addon_id, :quantity, :price)
    ");
    $stmt->execute([
        ':order_item_id' => $order_item_id,
        ':addon_id'      => $addon_id,
        ':quantity'      => $quantity,
        ':price'         => $price,
    ]);

    return (int) $pdo->lastInsertId();
}

function updateOrderStatus(PDO $pdo, int $order_id, string $status): bool {
    $sql = $status === 'completed'
        ? "UPDATE orders SET status = ?, completed_at = NOW() WHERE order_id = ?"
        : "UPDATE orders SET status = ?                           WHERE order_id = ?";

    return $pdo->prepare($sql)->execute([$status, $order_id]);
}

function cancelOrder(PDO $pdo, int $order_id): bool {
    return $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE order_id = ?")
               ->execute([$order_id]);
}