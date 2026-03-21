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

function getAllItemsWithIngredients(PDO $pdo): array
{
    $sql = "SELECT
                mi.*,
                c.category_name,
                GROUP_CONCAT(
                    CONCAT(
                        i.ingredient_name, ':',
                        IFNULL(r.quantity_required, 0), ':',
                        IFNULL(u.unit_code, '')
                    )
                    ORDER BY i.ingredient_name
                    SEPARATOR '|'
                ) AS recipe_details
            FROM menu_items mi
            JOIN categories   c  ON mi.category_id    = c.category_id
            LEFT JOIN recipes  r  ON mi.item_id         = r.item_id
            LEFT JOIN ingredients i ON r.ingredient_id  = i.ingredient_id
            LEFT JOIN units    u  ON i.unit_id           = u.unit_id
            GROUP BY mi.item_id, c.category_name
            ORDER BY mi.item_name";

    $results = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as &$item) {
        $item['ingredients_list'] = [];
        if (!empty($item['recipe_details'])) {
            foreach (explode('|', $item['recipe_details']) as $ing) {
                $parts = explode(':', $ing);
                $item['ingredients_list'][] = [
                    'name'     => $parts[0] ?? '',
                    'quantity' => isset($parts[1]) ? (float)$parts[1] : 0,
                    'unit'     => $parts[2] ?? '',  // unit_code e.g. "kg"
                ];
            }
        }
    }
    unset($item);

    return $results;
}

function getAllAvailableItems(PDO $pdo) {
    $sql = "SELECT mi.*, c.category_name FROM menu_items as mi JOIN categories as c ON mi.category_id = c.category_id WHERE mi.is_available = 1 ORDER BY item_name";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function getMenuItemById(PDO $pdo, $item_id) {
    $sql = "SELECT * FROM menu_items WHERE item_id = :item_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':item_id' => $item_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function getTotalSalesByItem(PDO $pdo, $item_id) {
    $sql = "SELECT SUM(quantity) as total_sold FROM order_items WHERE item_id = :item_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':item_id' => $item_id]);
    return (int)$stmt->fetchColumn();
}
function getOrderItems(PDO $pdo, int $order_id): array {
    $stmt = $pdo->prepare("
        SELECT oi.order_item_id,
               oi.item_id,
               oi.quantity,
               oi.unit_price,
               oi.subtotal,
               oi.status,
               oi.item_notes,        -- ← ADD THIS
               mi.item_name
        FROM order_items oi
        JOIN menu_items mi ON oi.item_id = mi.item_id
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