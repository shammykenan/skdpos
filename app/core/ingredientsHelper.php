<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../models/ingredients.php';

// ─────────────────────────────────────────────────────────────────────────────
// NOTE: ingredients no longer has a `unit` column.
// Unit info is in the `units` table, joined via ingredients.unit_id (FK).
// ─────────────────────────────────────────────────────────────────────────────

// ─────────────────────────────────────────────────────────────────────────────
// INGREDIENT FETCHING
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Get a single ingredient by ID, joined with its unit.
 */
function getIngredientById(PDO $pdo, int $ingredient_id): ?array {
    $stmt = $pdo->prepare("
        SELECT
            i.ingredient_id,
            i.ingredient_name,
            i.stock_quantity,
            i.unit_id,
            i.low_stock_alert,
            i.last_updated,
            u.unit_code,
            u.unit_label,
            u.unit_icon
        FROM ingredients i
        JOIN units u ON i.unit_id = u.unit_id
        WHERE i.ingredient_id = :ingredient_id
    ");
    $stmt->execute([':ingredient_id' => $ingredient_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Get all ingredients linked to beverage menu categories, with unit info.
 */
function getIngredientsForBeverages(PDO $pdo): array {
    $stmt = $pdo->query("
        SELECT DISTINCT
            i.ingredient_id,
            i.ingredient_name,
            i.stock_quantity,
            i.unit_id,
            i.low_stock_alert,
            u.unit_code,
            u.unit_label
        FROM ingredients i
        JOIN units       u ON i.unit_id        = u.unit_id
        JOIN recipes     r ON i.ingredient_id  = r.ingredient_id
        JOIN menu_items  m ON r.item_id        = m.item_id
        WHERE m.category_id IN (1,2,3,4,5,6)
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get all ingredients for a specific add-on, with unit info.
 */
function getIngredientsForAddon(PDO $pdo, int $addon_id): array {
    $stmt = $pdo->prepare("
        SELECT DISTINCT
            i.ingredient_id,
            i.ingredient_name,
            i.stock_quantity,
            i.unit_id,
            u.unit_code,
            u.unit_label
        FROM ingredients i
        JOIN units  u ON i.unit_id        = u.unit_id
        JOIN addons a ON i.ingredient_id  = a.ingredient_id
        WHERE a.addon_id = ?
    ");
    $stmt->execute([$addon_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get all ingredients for a specific menu item (recipe), with unit info.
 */
function getIngredientsForItem(PDO $pdo, int $item_id): array {
    $stmt = $pdo->prepare("
        SELECT
            i.ingredient_id,
            i.ingredient_name,
            i.stock_quantity,
            i.unit_id,
            u.unit_code,
            u.unit_label,
            r.quantity_required
        FROM recipes r
        JOIN ingredients i ON r.ingredient_id = i.ingredient_id
        JOIN units       u ON i.unit_id        = u.unit_id
        WHERE r.item_id = ?
    ");
    $stmt->execute([$item_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get ingredient stock details for a menu item (used for stock display in POS).
 */
function getMenuItemIngredientStock(PDO $pdo, int $item_id): array {
    $stmt = $pdo->prepare("
        SELECT
            i.ingredient_id,
            i.ingredient_name,
            i.stock_quantity,
            u.unit_code,
            u.unit_label,
            r.quantity_required
        FROM recipes r
        JOIN ingredients i ON r.ingredient_id = i.ingredient_id
        JOIN units       u ON i.unit_id        = u.unit_id
        WHERE r.item_id = ?
    ");
    $stmt->execute([$item_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ─────────────────────────────────────────────────────────────────────────────
// STOCK CHECKS
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Check if a menu item has enough stock for a given order quantity.
 * Also used as checkIngredientsStockForItem — that alias was removed (duplicate).
 */
function checkMenuItemStock(PDO $pdo, int $item_id, int $quantity = 1): bool {
    $ingredients = getIngredientsForItem($pdo, $item_id);
    foreach ($ingredients as $ingredient) {
        if ($ingredient['stock_quantity'] < ($ingredient['quantity_required'] * $quantity)) {
            return false;
        }
    }
    return true;
}

/**
 * Check if an add-on has enough stock for a given order quantity.
 */
function checkIngredientsStockForAddon(PDO $pdo, int $addon_id, int $order_qty): bool {
    $ingredients = getIngredientsForAddon($pdo, $addon_id);
    foreach ($ingredients as $ing) {
        if ($ing['stock_quantity'] < ($ing['quantity_required'] * $order_qty)) {
            return false;
        }
    }
    return true;
}

// ─────────────────────────────────────────────────────────────────────────────
// STOCK MUTATIONS
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Deduct ingredient stock when a menu item is ordered.
 */
function deductIngredientsForItem(PDO $pdo, int $item_id, int $quantity): void {
    $ingredients = getIngredientsForItem($pdo, $item_id);
    foreach ($ingredients as $ing) {
        $stmt = $pdo->prepare("
            UPDATE ingredients
            SET stock_quantity = stock_quantity - ?
            WHERE ingredient_id = ?
        ");
        $stmt->execute([$ing['quantity_required'] * $quantity, $ing['ingredient_id']]);
    }
}

/**
 * Restore ingredient stock when an order is cancelled or refunded.
 */
function restoreIngredientsForOrder(PDO $pdo, int $order_id): void {
    $stmt = $pdo->prepare("SELECT order_item_id, item_id, quantity FROM order_items WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($order_items as $item) {
        // Restore base item ingredients
        $ingredients = getIngredientsForItem($pdo, $item['item_id']);
        foreach ($ingredients as $ing) {
            $stmt2 = $pdo->prepare("
                UPDATE ingredients
                SET stock_quantity = stock_quantity + ?
                WHERE ingredient_id = ?
            ");
            $stmt2->execute([$ing['quantity_required'] * $item['quantity'], $ing['ingredient_id']]);
        }

        // Restore add-on ingredients
        $stmt3 = $pdo->prepare("
            SELECT oia.addon_id, oia.quantity, a.quantity_used
            FROM order_item_addons oia
            JOIN addons a ON oia.addon_id = a.addon_id
            WHERE oia.order_item_id = ?
        ");
        $stmt3->execute([$item['order_item_id']]);
        $addons = $stmt3->fetchAll(PDO::FETCH_ASSOC);

        foreach ($addons as $ad) {
            $stmt4 = $pdo->prepare("
                UPDATE ingredients
                SET stock_quantity = stock_quantity + ?
                WHERE ingredient_id = (SELECT ingredient_id FROM addons WHERE addon_id = ?)
            ");
            $stmt4->execute([$ad['quantity_used'] * $ad['quantity'], $ad['addon_id']]);
        }
    }
}