<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../core/tableHelper.php';
require_once __DIR__ . '/../core/OrNumberHelper.php';
require_once __DIR__ . '/../core/VatHelper.php';

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
    ?int    $table_id      = null,
    string  $discount_type = 'none',   // ← new
    float   $discount_amount = 0.0     // ← new
): int {
    $order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

    $stmt = $pdo->prepare("
        INSERT INTO orders
            (order_number, cashier_id, customer_name, table_id,
             order_type, order_source, subtotal, discount, total_amount,
             discount_type, discount_amount, status)
        VALUES
            (:order_number, :cashier_id, :customer_name, :table_id,
             :order_type, :order_source, :subtotal, :discount, :total_amount,
             :discount_type, :discount_amount, 'pending')
    ");

    $stmt->execute([
        ':order_number'    => $order_number,
        ':cashier_id'      => $cashier_id,
        ':customer_name'   => $customer_name,
        ':table_id'        => $table_id,
        ':order_type'      => $order_type,
        ':order_source'    => $order_source,
        ':subtotal'        => $subtotal,
        ':discount'        => $discount,
        ':total_amount'    => $total_amount,
        ':discount_type'   => $discount_type,
        ':discount_amount' => $discount_amount,
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

/**
 * Assign an OR number to an order and update all VAT fields atomically.
 * Must be called INSIDE an active transaction (the pay_and_submit transaction).
 *
 * @param  PDO   $pdo
 * @param  int   $order_id
 * @param  float  $total_amount   Final order total after discounts.
 * @param  string $discount_type  Needed because Senior/PWD totals are VAT-exempt.
 * @return string               The assigned OR number.
 */
function assignOrNumberAndVat(PDO $pdo, int $order_id, float $total_amount, string $discount_type = 'none'): string
{
    // Generate next OR number — getNextOrNumber() uses FOR UPDATE internally,
    // so we must already be inside a transaction when calling this.
    $or_number = getNextOrNumber($pdo);

    // Compute VAT breakdown
    $vat = VatHelper::orderBreakdown($total_amount, $pdo, $discount_type);

    $stmt = $pdo->prepare("
        UPDATE orders SET
            or_number          = :or_number,
            vat_amount         = :vat_amount,
            vat_exempt_amount  = :vat_exempt_amount,
            zero_rated_amount  = :zero_rated_amount
        WHERE order_id = :order_id
    ");
    $stmt->execute([
        ':or_number'         => $or_number,
        ':vat_amount'        => $vat['vat_amount'],
        ':vat_exempt_amount' => $vat['vat_exempt_amount'],
        ':zero_rated_amount' => $vat['zero_rated_amount'],
        ':order_id'          => $order_id,
    ]);

    return $or_number;
}

function createOrderItem(
    PDO     $pdo,
    int     $order_id,
    int     $item_id,
    string  $item_name_snapshot,
    int     $quantity,
    float   $unit_price,
    ?string $item_note    = null,
    ?int    $variant_id   = null,
    ?string $variant_name = null
): int {
    $subtotal = $unit_price * $quantity;
    $note     = ($item_note !== null && trim($item_note) !== '') ? trim($item_note) : null;

    $stmt = $pdo->prepare("
        INSERT INTO order_items
            (order_id, item_id, variant_id, item_name_snapshot, variant_name_snapshot,
             quantity, unit_price, subtotal, item_notes, status)
        VALUES
            (:order_id, :item_id, :variant_id, :item_name_snapshot, :variant_name_snapshot,
             :quantity, :unit_price, :subtotal, :item_notes, 'pending')
    ");
    $stmt->execute([
        ':order_id'               => $order_id,
        ':item_id'                => $item_id,
        ':variant_id'             => $variant_id,
        ':item_name_snapshot'     => $item_name_snapshot,
        ':variant_name_snapshot'  => $variant_name,
        ':quantity'               => $quantity,
        ':unit_price'             => $unit_price,
        ':subtotal'               => $subtotal,
        ':item_notes'             => $note,
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
 */
function upsertDraftOrder(
    PDO     $pdo,
    int     $cashier_id,
    array   $cart_rows,
    ?string $customer_name = null,
    string  $order_type    = 'dine_in',
    ?int    $table_id      = null
): int {
    $cart_json = json_encode($cart_rows, JSON_UNESCAPED_UNICODE);

    $stmt = $pdo->prepare("
        SELECT order_id FROM orders
        WHERE cashier_id = ? AND status = 'draft'
        ORDER BY created_at DESC LIMIT 1
    ");
    $stmt->execute([$cashier_id]);
    $existing = $stmt->fetchColumn();

    if ($existing) {
        $pdo->prepare("
            UPDATE orders
            SET customer_name = ?,
                order_type    = ?,
                table_id      = ?,
                draft_cart    = ?
            WHERE order_id = ?
        ")->execute([$customer_name, $order_type, $table_id, $cart_json, (int)$existing]);

        return (int) $existing;
    }

    $order_number = 'DFT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    $stmt = $pdo->prepare("
        INSERT INTO orders
            (order_number, cashier_id, customer_name, table_id,
             order_type, order_source, subtotal, discount, total_amount, status, draft_cart)
        VALUES
            (:order_number, :cashier_id, :customer_name, :table_id,
             :order_type, 'walk_in', 0, 0, 0, 'draft', :draft_cart)
    ");
    $stmt->execute([
        ':order_number'  => $order_number,
        ':cashier_id'    => $cashier_id,
        ':customer_name' => $customer_name,
        ':table_id'      => $table_id,
        ':order_type'    => $order_type,
        ':draft_cart'    => $cart_json,
    ]);

    return (int) $pdo->lastInsertId();
}

/**
 * Load a cashier's draft cart.
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
 */
function clearDraftOrder(PDO $pdo, int $cashier_id): void {
    $pdo->prepare("
        DELETE FROM orders WHERE cashier_id = ? AND status = 'draft'
    ")->execute([$cashier_id]);
}
/**
 * Record a discount entry in order_discounts.
 * Call inside the pay_and_submit transaction after createOrder().
 */
function recordOrderDiscount(
    PDO    $pdo,
    int    $order_id,
    string $discount_type,
    string $reference_no,
    float  $discount_pct,
    float  $discount_amt,
    int    $applied_by
): void {
    if ($discount_type === 'none' || $discount_amt <= 0) return;

    $stmt = $pdo->prepare("
        INSERT INTO order_discounts
            (order_id, discount_type, reference_no, discount_pct, discount_amt, applied_by)
        VALUES
            (:order_id, :discount_type, :reference_no, :discount_pct, :discount_amt, :applied_by)
    ");
    $stmt->execute([
        ':order_id'      => $order_id,
        ':discount_type' => $discount_type,
        ':reference_no'  => $reference_no ?: null,
        ':discount_pct'  => $discount_pct,
        ':discount_amt'  => $discount_amt,
        ':applied_by'    => $applied_by,
    ]);
}
