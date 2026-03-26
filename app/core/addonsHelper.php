<?php
require_once __DIR__ . '/../../config/db.php';

function getAllAddons(PDO $pdo): array
{
    $sql = "SELECT a.*, i.ingredient_name, u.unit_code, u.unit_label
            FROM addons a
            JOIN ingredients i ON a.ingredient_id = i.ingredient_id
            JOIN units u       ON i.unit_id        = u.unit_id
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
    $stmt = $pdo->query("SELECT * FROM addons WHERE is_active = 1 ORDER BY addon_name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Returns only active add-ons whose ingredient appears in the given item's recipe.
 * Joins units so callers get unit_code / unit_label instead of the old i.unit string.
 */
function getAddonsForMenuItem(PDO $pdo, int $item_id): array
{
    $sql = "
        SELECT a.addon_id, a.addon_name, a.price, a.ingredient_id,
               a.quantity_used, a.is_active,
               i.ingredient_name,
               u.unit_code, u.unit_label
        FROM addons a
        JOIN ingredients i ON a.ingredient_id = i.ingredient_id
        JOIN units u       ON i.unit_id        = u.unit_id
        WHERE a.is_active = 1
          AND a.ingredient_id IN (
              SELECT ingredient_id FROM recipes WHERE item_id = :item_id
          )
        ORDER BY a.addon_name
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':item_id' => $item_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function deductIngredientsForAddon(PDO $pdo, int $addon_id, int $addon_quantity): void
{
    $stmt = $pdo->prepare("
        SELECT ingredient_id, quantity_used
        FROM addons
        WHERE addon_id = :addon_id
    ");
    $stmt->execute([':addon_id' => $addon_id]);
    $addon = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$addon || (float)$addon['quantity_used'] <= 0) {
        return;
    }

    $deduct = (float)$addon['quantity_used'] * $addon_quantity;

    $pdo->prepare("
        UPDATE ingredients
        SET stock_quantity = stock_quantity - :deduct
        WHERE ingredient_id = :ingredient_id
    ")->execute([
        ':deduct'        => $deduct,
        ':ingredient_id' => $addon['ingredient_id'],
    ]);
}

/**
 * Check if an add-on has sufficient ingredient stock.
 * No unit column needed here — only stock quantities are compared.
 */
function checkAddonStock(PDO $pdo, int $addon_id, int $quantity = 1): array
{
    $sql = "SELECT a.*, i.stock_quantity, i.ingredient_name
            FROM addons a
            JOIN ingredients i ON a.ingredient_id = i.ingredient_id
            WHERE a.addon_id = :addon_id AND a.is_active = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':addon_id' => $addon_id]);
    $addon = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$addon) {
        return ['success' => false, 'message' => 'Add-on not found', 'available' => 0, 'required' => 0];
    }

    $required  = $addon['quantity_used'] * $quantity;
    $available = $addon['stock_quantity'];

    if ($available < $required) {
        return [
            'success'    => false,
            'message'    => "Insufficient {$addon['ingredient_name']} for add-on",
            'available'  => $available,
            'required'   => $required,
            'ingredient' => $addon['ingredient_name'],
        ];
    }

    return ['success' => true, 'message' => 'Stock available', 'available' => $available, 'required' => $required];
}

function getAddonsStockInfo(PDO $pdo, array $addon_ids): array
{
    if (empty($addon_ids)) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($addon_ids), '?'));

    $stmt = $pdo->prepare("
        SELECT
            a.addon_id,
            a.ingredient_id,
            a.quantity_used       AS quantity_required,
            i.ingredient_name,
            i.stock_quantity
        FROM addons a
        JOIN ingredients i ON i.ingredient_id = a.ingredient_id
        WHERE a.addon_id IN ($placeholders)
    ");
    $stmt->execute(array_values($addon_ids));
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = [];
    foreach ($rows as $row) {
        $id      = (int)$row['addon_id'];
        $maxQty  = ($row['quantity_required'] > 0)
                   ? (int)floor((float)$row['stock_quantity'] / (float)$row['quantity_required'])
                   : 0;

        $result[$id] = [
            'max_quantity' => $maxQty,
            'has_stock'    => $maxQty > 0,
            'ingredients'  => [[
                'ingredient_id'     => $row['ingredient_id'],
                'ingredient_name'   => $row['ingredient_name'],
                'quantity_required' => (float)$row['quantity_required'],
                'stock_quantity'    => (float)$row['stock_quantity'],
            ]],
        ];
    }
    return $result;
}

function getAddonIngredientStock(PDO $pdo, int $addon_id): array
{
    $stmt = $pdo->prepare("
        SELECT i.ingredient_id,
               i.ingredient_name,
               a.quantity_used AS quantity_required,
               i.stock_quantity
        FROM addons a
        JOIN ingredients i ON i.ingredient_id = a.ingredient_id
        WHERE a.addon_id = :addon_id
    ");
    $stmt->execute([':addon_id' => $addon_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}