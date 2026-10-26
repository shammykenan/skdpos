<?php
require_once __DIR__ . '/../../config/db.php';

function getAllCategories(PDO $pdo) {
    try {
        $stmt = $pdo->query("SELECT category_id, category_name FROM categories ORDER BY category_name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error in getAllCategories: " . $e->getMessage());
        return [];
    }
}

function getAllItems(PDO $pdo) {
    try {
        $stmt = $pdo->query("SELECT mi.*, c.category_name FROM menu_items as mi JOIN categories as c ON mi.category_id = c.category_id ORDER BY item_name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error in getAllItems: " . $e->getMessage());
        return [];
    }
}

function getAllItemsWithVariants(PDO $pdo): array {
    try {
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
    } catch (PDOException $e) {
        error_log("Error in getAllItemsWithVariants: " . $e->getMessage());
        return [];
    }
}

function getAllAvailableItems(PDO $pdo) {
    try {
        $stmt = $pdo->query("SELECT mi.*, c.category_name FROM menu_items as mi JOIN categories as c ON mi.category_id = c.category_id WHERE mi.is_available = 1 ORDER BY item_name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error in getAllAvailableItems: " . $e->getMessage());
        return [];
    }
}

function getMenuItemById(PDO $pdo, $item_id) {
    try {
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
    } catch (PDOException $e) {
        error_log("Error in getMenuItemById: " . $e->getMessage());
        return null;
    }
}

function getTotalSalesByItem($pdo, $item_id) {
    $stmt = $pdo->prepare("SELECT SUM(quantity) as total_sold FROM order_items WHERE item_id = :item_id");
    $stmt->execute([':item_id' => $item_id]);
    return (int) $stmt->fetchColumn();
}

/**
 * Get all items for a given order.
 *
 * Columns returned:
 *   - item_name_snapshot / variant_name_snapshot : names stored at order time (never change)
 *   - item_name                                  : resolved display name (snapshot → live fallback)
 *   - current_variant_name / current_variant_price: live variant data for price-change comparison
 *   - void_reason / cancel_reason                : per-item audit reasons
 *   - waste_cost                                 : NULL for non-voided or cancelled items;
 *                                                  DECIMAL for voided items (cost of ingredients used)
 */
function getOrderItems(PDO $pdo, int $order_id): array {
    $stmt = $pdo->prepare("
        SELECT
            oi.order_item_id,
            oi.item_id,
            oi.variant_id,
            oi.item_name_snapshot,
            oi.variant_name_snapshot,
            oi.quantity,
            oi.quantity_started,
            oi.quantity_ready,
            oi.quantity_voided,
            oi.unit_price,
            oi.subtotal,
            oi.status          AS item_status,
            oi.item_notes,
            oi.void_reason,
            oi.cancel_reason,
            oi.waste_cost,

            mi.item_name       AS current_item_name,

            mv.variant_name    AS current_variant_name,
            mv.price           AS current_variant_price,
            mv.cost_price      AS current_variant_cost_price

        FROM order_items oi
        JOIN  menu_items    mi ON oi.item_id    = mi.item_id
        LEFT JOIN menu_variants mv ON oi.variant_id = mv.variant_id
        WHERE oi.order_id = ?
        ORDER BY oi.order_item_id ASC
    ");
    $stmt->execute([$order_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as &$row) {
        // Always prefer the snapshot taken at order time
        $row['item_name'] = !empty($row['item_name_snapshot'])
            ? $row['item_name_snapshot']
            : $row['current_item_name'];
    }
    unset($row);

    return $rows;
}

/**
 * Fetch all addons for a given order item.
 */
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
    $stmt = $pdo->prepare("
        SELECT i.stock_quantity, r.quantity_required
        FROM recipes r
        JOIN ingredients i ON r.ingredient_id = i.ingredient_id
        WHERE r.item_id = ?
    ");
    $stmt->execute([$item_id]);
    $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $max_qty = PHP_INT_MAX;
    foreach ($ingredients as $ing) {
        $possible_qty = floor($ing['stock_quantity'] / $ing['quantity_required']);
        if ($possible_qty < $max_qty) $max_qty = $possible_qty;
    }
    return $max_qty > 0 ? $max_qty : 0;
}

function getAllOrdersWithItems(PDO $pdo, ?int $cashierId = null): array {
    $orders = getAllOrders($pdo, $cashierId);
    foreach ($orders as &$order) {
        $details        = getOrderDetails($pdo, $order['order_id']);
        $order['items'] = $details ? $details['items'] : [];
    }
    return $orders;
}