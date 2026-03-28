<?php
require_once __DIR__ . '/../../config/db.php';

function addRecipe(PDO $pdo, int $item_id, int $ingredient_id, float $quantity_required): bool
{
    $sql = "INSERT INTO recipes (item_id, ingredient_id, quantity_required)
            VALUES (:item_id, :ingredient_id, :quantity_required)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        ':item_id'             => $item_id,
        ':ingredient_id'       => $ingredient_id,
        ':quantity_required'   => $quantity_required,
    ]);
}

function updateRecipe(PDO $pdo, int $recipe_id, float $quantity_required): bool
{
    $sql = "UPDATE recipes SET quantity_required = :quantity_required WHERE recipe_id = :recipe_id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        ':quantity_required' => $quantity_required,
        ':recipe_id'         => $recipe_id,
    ]);
}

function deleteRecipe(PDO $pdo, int $recipe_id): bool
{
    $sql = "DELETE FROM recipes WHERE recipe_id = :recipe_id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([':recipe_id' => $recipe_id]);
}

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

/**
 * Copy all base recipes (variant_id IS NULL) for an item into a specific variant.
 * Skips ingredients already assigned to that variant (merge behaviour).
 *
 * Returns an array:
 *   [
 *     'copied'  => int,   // number of rows inserted
 *     'skipped' => int,   // number skipped (already existed)
 *   ]
 *
 * Throws on DB error.
 */
function copyBaseRecipeToVariant(PDO $pdo, int $item_id, int $variant_id): array
{
    // 1. Fetch all base ingredients for this item
    $stmt = $pdo->prepare("
        SELECT ingredient_id, quantity_required
        FROM   recipes
        WHERE  item_id    = ?
          AND  variant_id IS NULL
    ");
    $stmt->execute([$item_id]);
    $baseRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($baseRows)) {
        return ['copied' => 0, 'skipped' => 0];
    }

    // 2. Fetch ingredient_ids already assigned to this variant (to skip them)
    $stmt = $pdo->prepare("
        SELECT ingredient_id
        FROM   recipes
        WHERE  item_id    = ?
          AND  variant_id = ?
    ");
    $stmt->execute([$item_id, $variant_id]);
    $existingIds = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'ingredient_id');
    $existingSet = array_flip($existingIds); // hash set for O(1) lookup

    // 3. Insert missing ones
    $insertStmt = $pdo->prepare("
        INSERT INTO recipes (item_id, variant_id, ingredient_id, quantity_required)
        VALUES (?, ?, ?, ?)
    ");

    $copied  = 0;
    $skipped = 0;

    foreach ($baseRows as $row) {
        if (isset($existingSet[$row['ingredient_id']])) {
            $skipped++;
            continue;
        }
        $insertStmt->execute([
            $item_id,
            $variant_id,
            $row['ingredient_id'],
            $row['quantity_required'],
        ]);
        $copied++;
    }

    return ['copied' => $copied, 'skipped' => $skipped];
}
