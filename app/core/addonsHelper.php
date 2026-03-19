<?php
require_once __DIR__ . '/../../config/db.php';

function getAllAddons(PDO $pdo): array
{
    $sql = "SELECT a.*, i.ingredient_name, i.unit
            FROM addons a
            JOIN ingredients i ON a.ingredient_id = i.ingredient_id
            ORDER BY a.addon_name";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAddonById(PDO $pdo, int $addon_id): ?array
{
    $sql = "SELECT * FROM addons WHERE addon_id = :addon_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':addon_id' => $addon_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
}

function getAddonsForItemCategory(PDO $pdo, int $category_id): array
{
    $beverageCategories = [1, 2, 3, 4, 5, 6];
    if (!in_array($category_id, $beverageCategories)) {
        return [];
    }
    $sql  = "SELECT * FROM addons WHERE is_active = 1 ORDER BY addon_name";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Returns only active add-ons whose ingredient appears in the given item's recipe.
 * This ensures fries don't show "Extra Pearl", milk tea doesn't show "Extra Ketchup", etc.
 */
function getAddonsForMenuItem(PDO $pdo, int $item_id): array
{
    $sql = "
        SELECT a.addon_id, a.addon_name, a.price, a.ingredient_id,
               a.quantity_used, a.is_active,
               i.ingredient_name, i.unit
        FROM addons a
        JOIN ingredients i ON a.ingredient_id = i.ingredient_id
        WHERE a.is_active = 1
          AND a.ingredient_id IN (
              SELECT ingredient_id
              FROM recipes
              WHERE item_id = :item_id
          )
        ORDER BY a.addon_name
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':item_id' => $item_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function deductIngredientsForAddon(PDO $pdo, int $addon_id, int $quantity): void
{
    $sql  = "SELECT ingredient_id, quantity_used FROM addons WHERE addon_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$addon_id]);
    $addon = $stmt->fetch();

    if ($addon) {
        $sql_update = "UPDATE ingredients
                       SET stock_quantity = stock_quantity - ?
                       WHERE ingredient_id = ?";
        $stmt_up = $pdo->prepare($sql_update);
        $stmt_up->execute([$addon['quantity_used'] * $quantity, $addon['ingredient_id']]);
    }
}

// Check if an add-on has sufficient ingredient stock
function checkAddonStock(PDO $pdo, int $addon_id, int $quantity = 1): array
{
    $sql  = "SELECT a.*, i.stock_quantity, i.ingredient_name
             FROM addons a
             JOIN ingredients i ON a.ingredient_id = i.ingredient_id
             WHERE a.addon_id = :addon_id AND a.is_active = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['addon_id' => $addon_id]);
    $addon = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$addon) {
        return ['success' => false, 'message' => 'Add-on not found', 'available' => 0, 'required' => 0];
    }

    $required_quantity = $addon['quantity_used'] * $quantity;
    $available         = $addon['stock_quantity'];

    if ($available < $required_quantity) {
        return [
            'success'    => false,
            'message'    => "Insufficient {$addon['ingredient_name']} for add-on",
            'available'  => $available,
            'required'   => $required_quantity,
            'ingredient' => $addon['ingredient_name'],
        ];
    }

    return ['success' => true, 'message' => 'Stock available', 'available' => $available, 'required' => $required_quantity];
}

function getAddonsStockInfo(PDO $pdo, array $addon_ids): array
{
    if (empty($addon_ids)) {
        return [];
    }

    // Clean the array: ensure plain integers and remove duplicates
    $addon_ids = array_values(array_unique(array_map('intval', $addon_ids)));

    if (empty($addon_ids)) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($addon_ids), '?'));
    $sql = "SELECT a.addon_id,
                   a.addon_name,
                   a.price,
                   a.quantity_used,
                   i.ingredient_id,
                   i.ingredient_name,
                   i.stock_quantity,
                   FLOOR(i.stock_quantity / a.quantity_used) AS max_quantity
            FROM addons a
            JOIN ingredients i ON a.ingredient_id = i.ingredient_id
            WHERE a.addon_id IN ($placeholders)
              AND a.is_active = 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_values($addon_ids));

    $result = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $maxQty = (int) floor($row['max_quantity']);

        $result[(int)$row['addon_id']] = [
            'addon_name'      => $row['addon_name'],
            'price'           => $row['price'],
            'ingredient_id'   => $row['ingredient_id'],
            'ingredient_name' => $row['ingredient_name'],
            'quantity_used'   => $row['quantity_used'],
            'stock_quantity'  => $row['stock_quantity'],
            'max_quantity'    => $maxQty,
            'has_stock'       => $maxQty > 0,
            'ingredients'     => [
                [
                    'ingredient_id'     => $row['ingredient_id'],
                    'ingredient_name'   => $row['ingredient_name'],
                    'quantity_required' => (float) $row['quantity_used'],
                    'stock_quantity'    => (float) $row['stock_quantity'],
                ]
            ],
        ];
    }

    return $result;
}
function getAddonIngredientStock(PDO $pdo, int $addon_id): array
{
    $stmt = $pdo->prepare("
        SELECT
            i.ingredient_id,
            i.ingredient_name,
            a.quantity_used   AS quantity_required,
            i.stock_quantity
        FROM addons a
        JOIN ingredients i ON i.ingredient_id = a.ingredient_id
        WHERE a.addon_id = :addon_id
    ");
    $stmt->execute([':addon_id' => $addon_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}