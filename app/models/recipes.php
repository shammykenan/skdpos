<?php
require_once __DIR__ . '/../../config/db.php';

function addRecipe(PDO $pdo, int $item_id, int $ingredient_id, float $quantity_required): bool
{
    $sql = "INSERT INTO recipes (item_id, ingredient_id, quantity_required) 
            VALUES (:item_id, :ingredient_id, :quantity_required)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        ':item_id' => $item_id,
        ':ingredient_id' => $ingredient_id,
        ':quantity_required' => $quantity_required
    ]);
}

/**
 * Update quantity_required of an existing recipe
 */
function updateRecipe(PDO $pdo, int $recipe_id, float $quantity_required): bool
{
    $sql = "UPDATE recipes SET quantity_required = :quantity_required WHERE recipe_id = :recipe_id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        ':quantity_required' => $quantity_required,
        ':recipe_id' => $recipe_id
    ]);
}

/**
 * Delete a recipe
 */
function deleteRecipe(PDO $pdo, int $recipe_id): bool
{
    $sql = "DELETE FROM recipes WHERE recipe_id = :recipe_id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([':recipe_id' => $recipe_id]);
}
// Keep existing addRecipe() untouched for backward compatibility.
// Add this new function:
function addRecipeForVariant(
    PDO   $pdo,
    int   $item_id,
    int   $ingredient_id,
    float $quantity_required,
    ?int  $variant_id = null
): bool {
    $stmt = $pdo->prepare("
        INSERT INTO recipes (item_id, variant_id, ingredient_id, quantity_required)
        VALUES (?, ?, ?, ?)
    ");
    return $stmt->execute([$item_id, $variant_id, $ingredient_id, $quantity_required]);
}