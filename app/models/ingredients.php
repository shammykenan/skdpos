<?php
require_once __DIR__ . '/../../config/db.php';

// Add new ingredient
function addIngredient(PDO $pdo, string $ingredient_name, float $stock_quantity, string $unit, float $low_stock_alert = 10): bool {
    $sql = "INSERT INTO ingredients (ingredient_name, stock_quantity, unit, low_stock_alert) 
            VALUES (:ingredient_name, :stock_quantity, :unit, :low_stock_alert)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        ':ingredient_name' => $ingredient_name,
        ':stock_quantity' => $stock_quantity,
        ':unit' => $unit,
        ':low_stock_alert' => $low_stock_alert
    ]);
}

// Update ingredient
function updateIngredient(PDO $pdo, int $ingredient_id, string $ingredient_name, float $stock_quantity, string $unit, float $low_stock_alert): bool {
    $sql = "UPDATE ingredients 
            SET ingredient_name = :ingredient_name, stock_quantity = :stock_quantity, unit = :unit, low_stock_alert = :low_stock_alert, last_updated = NOW()
            WHERE ingredient_id = :ingredient_id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        ':ingredient_name' => $ingredient_name,
        ':stock_quantity' => $stock_quantity,
        ':unit' => $unit,
        ':low_stock_alert' => $low_stock_alert,
        ':ingredient_id' => $ingredient_id
    ]);
}
function getIngredientsForAddon(PDO $pdo, int $addon_id) {
    $sql = "SELECT DISTINCT i.* 
            FROM ingredients i
            JOIN addons a ON i.ingredient_id = a.ingredient_id
            WHERE a.addon_id = ?"; 
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$addon_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function deleteIngredient(PDO $pdo, int $ingredient_id): bool {
    // 1. Check if ingredient is used in recipes
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM recipes WHERE ingredient_id = :ingredient_id");
    $stmt->execute([':ingredient_id' => $ingredient_id]);
    $inRecipes = $stmt->fetchColumn();

    // 2. Check if ingredient is used in addons
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM addons WHERE ingredient_id = :ingredient_id");
    $stmt->execute([':ingredient_id' => $ingredient_id]);
    $inAddons = $stmt->fetchColumn();

    // 3. If used anywhere, cannot delete
    if ($inRecipes > 0 || $inAddons > 0) {
        return false; // deletion not allowed
    }

    // 4. Safe to delete
    $stmt = $pdo->prepare("DELETE FROM ingredients WHERE ingredient_id = :ingredient_id");
    return $stmt->execute([':ingredient_id' => $ingredient_id]);
}