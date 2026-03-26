<?php
require_once __DIR__ . '/../../config/db.php';
function getAllCategories(PDO $pdo) {
    $sql = "SELECT category_id, category_name FROM categories ORDER BY category_name";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function getAllItems(PDO $pdo) {
    $sql = "SELECT mi.*, c.category_name FROM menu_items as mi JOIN categories as c ON mi.category_id = c.category_id ORDER BY item_name";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllItemsWithVariants(PDO $pdo): array
{
    $stmt = $pdo->query("
        SELECT 
            mi.item_id,
            mi.item_name,
            mi.description,
            mi.base_price,
            mi.cost_price,
            mi.image_url,
            mi.is_available,
            mi.category_id,
            c.category_name
        FROM menu_items mi
        LEFT JOIN categories c ON mi.category_id = c.category_id
        ORDER BY mi.item_name
    ");
    return $stmt->fetchAll();
}

function getAllAvailableItems(PDO $pdo) {
    $sql = "SELECT mi.*, c.category_name FROM menu_items as mi JOIN categories as c ON mi.category_id = c.category_id WHERE mi.is_available = 1 ORDER BY item_name";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function getMenuItemById(PDO $pdo, $item_id) {
$stmt = $pdo->prepare("
        SELECT 
            mi.item_id,
            mi.item_name,
            mi.description,
            mi.base_price,
            mi.cost_price,
            mi.image_url,
            mi.is_available,
            mi.category_id,
            c.category_name
        FROM menu_items mi
        LEFT JOIN categories c ON mi.category_id = c.category_id
        WHERE mi.item_id = ?
    ");
    $stmt->execute([$item_id]);
    return $stmt->fetch();
}
function getTotalSalesByItem(PDO $pdo, $item_id) {
    $sql = "SELECT SUM(quantity) as total_sold FROM order_items WHERE item_id = :item_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':item_id' => $item_id]);
    return (int)$stmt->fetchColumn();
}
// FIX (BUG 8): Add variant_id and variant_name_snapshot to SELECT.
// ENHANCEMENT: Join with menu_variants to get current variant details
function getOrderItems(PDO $pdo, int $order_id): array
{
    $stmt = $pdo->prepare("
        SELECT oi.order_item_id,
               oi.item_id,
               oi.variant_id,
               oi.variant_name_snapshot,
               oi.quantity,
               oi.unit_price,
               oi.subtotal,
               oi.status,
               oi.item_notes,
               mi.item_name,
               mv.variant_name as current_variant_name,
               mv.price as current_variant_price,
               mv.cost_price as current_variant_cost_price
        FROM order_items oi
        JOIN menu_items mi ON oi.item_id = mi.item_id
        LEFT JOIN menu_variants mv ON oi.variant_id = mv.variant_id
        WHERE oi.order_id = ?
        ORDER BY oi.order_item_id ASC
    ");
    $stmt->execute([$order_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch all addons for a given order item
function getOrderItemAddons(PDO $pdo, int $order_item_id): array {
    $stmt = $pdo->prepare("
        SELECT oia.*, a.addon_name
        FROM order_item_addons oia
        JOIN addons a ON oia.addon_id = a.addon_id
        WHERE oia.order_item_id = ?
    ");
    $stmt->execute([$order_item_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function getMaxOrderQuantityForItem($pdo, $item_id) {
    $sql = "SELECT i.stock_quantity, r.quantity_required
            FROM recipes r
            JOIN ingredients i ON r.ingredient_id = i.ingredient_id
            WHERE r.item_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$item_id]);
    $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $max_qty = PHP_INT_MAX;
    foreach ($ingredients as $ing) {
        $possible_qty = floor($ing['stock_quantity'] / $ing['quantity_required']);
        if ($possible_qty < $max_qty) {
            $max_qty = $possible_qty;
        }
    }
    return $max_qty > 0 ? $max_qty : 0;
}
function getAllOrdersWithItems(PDO $pdo, ?int $cashierId = null): array {
    $orders = getAllOrders($pdo, $cashierId);  // pass filter down

    foreach ($orders as &$order) {
        $details = getOrderDetails($pdo, $order['order_id']);
        $order['items'] = $details ? $details['items'] : [];
    }

    return $orders;
}