<?php
require_once __DIR__ . '/../../config/db.php';

function getAllRecipes(PDO $pdo): array
{
    $sql = "
        SELECT r.recipe_id, r.item_id, r.variant_id, r.ingredient_id, r.quantity_required,
               m.item_name,
               i.ingredient_name,
               u.unit_code, u.unit_label,
               v.variant_name
        FROM recipes r
        JOIN menu_items  m ON r.item_id       = m.item_id
        JOIN ingredients i ON r.ingredient_id = i.ingredient_id
        JOIN units       u ON i.unit_id       = u.unit_id
        LEFT JOIN menu_variants v ON r.variant_id = v.variant_id
        ORDER BY m.item_name, v.variant_name, i.ingredient_name
    ";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

function getRecipeById(PDO $pdo, int $recipe_id): ?array
{
    $sql = "SELECT * FROM recipes WHERE recipe_id = :recipe_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':recipe_id' => $recipe_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
}

function recipeExists(PDO $pdo, int $item_id, int $ingredient_id, ?int $exclude_recipe_id = null): bool
{
    $sql    = "SELECT COUNT(*) FROM recipes WHERE item_id = ? AND ingredient_id = ?";
    $params = [$item_id, $ingredient_id];

    if ($exclude_recipe_id !== null) {
        $sql     .= " AND recipe_id != ?";
        $params[] = $exclude_recipe_id;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn() > 0;
}
/**
 * Returns the effective ingredient recipe for a menu item.
 * Uses the variant's recipe if variant_id is provided and has ingredients,
 * otherwise falls back to the base item recipe.
 */
// In posHelper.php, after defining getMenuItemIngredientStockForVariant:
function getEffectiveRecipe(PDO $pdo, int $item_id, ?int $variant_id): array {
    return getMenuItemIngredientStockForVariant($pdo, $item_id, $variant_id);
}

/**
 * Check duplicate recipe row, variant-aware.
 * Uses variant_id_key logic at app level to match the DB constraint.
 */
function recipeExistsForVariant(
    PDO  $pdo,
    int  $item_id,
    int  $ingredient_id,
    ?int $variant_id        = null,
    ?int $exclude_recipe_id = null
): bool {
    if ($variant_id === null) {
        $sql    = "SELECT COUNT(*) FROM recipes WHERE item_id = ? AND ingredient_id = ? AND variant_id IS NULL";
        $params = [$item_id, $ingredient_id];
    } else {
        $sql    = "SELECT COUNT(*) FROM recipes WHERE item_id = ? AND ingredient_id = ? AND variant_id = ?";
        $params = [$item_id, $ingredient_id, $variant_id];
    }
    if ($exclude_recipe_id !== null) {
        $sql     .= " AND recipe_id != ?";
        $params[] = $exclude_recipe_id;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn() > 0;
}
