<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../core/tableHelper.php';

/**
 * Create an order.
 * For dine-in orders with a valid $table_id, the table is marked
 * occupied inside the same caller transaction.
 */
function createOrder(
    PDO     $pdo,
    int     $cashier_id,
    ?string $customer_name,
    ?string $table_number,
    string  $order_type,
    string  $order_source,
    float   $subtotal,
    float   $discount,
    float   $total_amount,
    ?int    $table_id = null
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

    if ($order_type === 'dine_in' && $table_id !== null) {
        $occupied = occupyTable($pdo, $table_id, $order_id);
        if (!$occupied) {
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

// =============================================================================
// DRAFT FUNCTIONS
//
// A draft stores the cart as a JSON blob in orders.draft_cart.
// It NEVER writes to order_items or order_item_addons — those tables
// only get rows when a real order is submitted.
//
// This means:
//  - No bloat in order_items from abandoned/cleared carts
//  - Kitchen never sees draft rows
//  - Clearing a draft = one DELETE on the orders row, nothing else
//  - One cashier = one draft row at a time
// =============================================================================

/**
 * Create or overwrite the cashier's draft.
 * Cart is stored as JSON on the orders row — order_items untouched.
 *
 * Requires: ALTER TABLE orders ADD COLUMN draft_cart JSON NULL;
 */
function upsertDraftOrder(
    PDO     $pdo,
    int     $cashier_id,
    array   $cart_rows,
    ?string $customer_name = null,
    string  $order_type    = 'dine_in',
    ?int    $table_id      = null
): int {
    // Resolve display label
    $table_number = null;
    if ($table_id !== null) {
        $t = $pdo->prepare("SELECT table_name FROM `tables` WHERE table_id = ?");
        $t->execute([$table_id]);
        $table_number = $t->fetchColumn() ?: null;
    }

    $cart_json = json_encode($cart_rows, JSON_UNESCAPED_UNICODE);

    // Find existing draft
    $stmt = $pdo->prepare("
        SELECT order_id FROM orders
        WHERE cashier_id = ? AND status = 'draft'
        ORDER BY created_at DESC LIMIT 1
    ");
    $stmt->execute([$cashier_id]);
    $existing = $stmt->fetchColumn();

    if ($existing) {
        // Just update the JSON blob + meta — no child table writes at all
        $pdo->prepare("
            UPDATE orders
            SET customer_name = ?,
                order_type    = ?,
                table_id      = ?,
                table_number  = ?,
                draft_cart    = ?
            WHERE order_id = ?
        ")->execute([$customer_name, $order_type, $table_id, $table_number, $cart_json, (int)$existing]);

        return (int) $existing;
    }

    // Create a fresh draft row — nothing in order_items
    $order_number = 'DFT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    $stmt = $pdo->prepare("
        INSERT INTO orders
            (order_number, cashier_id, customer_name, table_number, table_id,
             order_type, order_source, subtotal, discount, total_amount, status, draft_cart)
        VALUES
            (:order_number, :cashier_id, :customer_name, :table_number, :table_id,
             :order_type, 'walk_in', 0, 0, 0, 'draft', :draft_cart)
    ");
    $stmt->execute([
        ':order_number'  => $order_number,
        ':cashier_id'    => $cashier_id,
        ':customer_name' => $customer_name,
        ':table_number'  => $table_number,
        ':table_id'      => $table_id,
        ':order_type'    => $order_type,
        ':draft_cart'    => $cart_json,
    ]);

    return (int) $pdo->lastInsertId();
}

/**
 * Load a cashier's draft cart.
 * Reads the JSON blob — no joins to order_items needed.
 * Returns ['cart' => [], 'meta' => null] if no draft exists.
 */
function loadDraftCart(PDO $pdo, int $cashier_id): array {
    $stmt = $pdo->prepare("
        SELECT order_id, customer_name, order_type, table_id, draft_cart
        FROM orders
        WHERE cashier_id = ? AND status = 'draft'
        ORDER BY created_at DESC LIMIT 1
    ");
    $stmt->execute([$cashier_id]);
    $draft = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$draft) {
        return ['cart' => [], 'meta' => null];
    }

    $cart = [];
    if (!empty($draft['draft_cart'])) {
        $decoded = json_decode($draft['draft_cart'], true);
        if (is_array($decoded)) {
            $cart = $decoded;
        }
    }

    return [
        'cart' => $cart,
        'meta' => [
            'customer_name' => $draft['customer_name'],
            'order_type'    => $draft['order_type'],
            'table_id'      => $draft['table_id'] ? (int) $draft['table_id'] : null,
        ],
    ];
}

/**
 * Delete the cashier's draft.
 * Since drafts never write to order_items, this is a single-row DELETE.
 * No stock to restore, no child rows to worry about.
 */
function clearDraftOrder(PDO $pdo, int $cashier_id): void {
    $pdo->prepare("
        DELETE FROM orders WHERE cashier_id = ? AND status = 'draft'
    ")->execute([$cashier_id]);
}