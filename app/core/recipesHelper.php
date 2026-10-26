<?php
// app/core/recipesHelper.php

require_once __DIR__ . '/../../config/db.php';

function getAllRecipes(PDO $pdo): array
{
    try {
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
    } catch (PDOException $e) {
        error_log("Error in getAllRecipes: " . $e->getMessage());
        return [];
    }
}

function getRecipeById(PDO $pdo, int $recipe_id): ?array
{
    $sql  = "SELECT * FROM recipes WHERE recipe_id = :recipe_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':recipe_id' => $recipe_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Check if a recipe row already exists for a given item + ingredient + variant combo.
 * variant_id = null  → base recipe
 * variant_id = N     → variant-specific recipe
 */
function recipeExistsForVariant(
    PDO  $pdo,
    int  $item_id,
    int  $ingredient_id,
    ?int $variant_id = null
): bool {
    if ($variant_id === null) {
        $sql    = "SELECT COUNT(*) FROM recipes
                   WHERE item_id = ? AND ingredient_id = ? AND variant_id IS NULL";
        $params = [$item_id, $ingredient_id];
    } else {
        $sql    = "SELECT COUNT(*) FROM recipes
                   WHERE item_id = ? AND ingredient_id = ? AND variant_id = ?";
        $params = [$item_id, $ingredient_id, $variant_id];
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn() > 0;
}

/**
 * Legacy helper — checks only base recipes (no variant_id).
 * Kept for backward compatibility; prefer recipeExistsForVariant().
 */
function recipeExists(PDO $pdo, int $item_id, int $ingredient_id, ?int $exclude_recipe_id = null): bool
{
    $sql    = "SELECT COUNT(*) FROM recipes WHERE item_id = ? AND ingredient_id = ? AND variant_id IS NULL";
    $params = [$item_id, $ingredient_id];

    if ($exclude_recipe_id !== null) {
        $sql     .= " AND recipe_id != ?";
        $params[] = $exclude_recipe_id;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn() > 0;
}

// ─────────────────────────────────────────────────────────────────────────────
// STRICT RECIPE RESOLUTION
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Resolve the correct recipe for an item, strictly — no cross-mode fallback.
 *
 * Rules:
 *   • Variant item  ($variant_id !== null) → fetch ONLY rows WHERE variant_id = $variant_id.
 *                                            Returns [] if that variant has no recipe.
 *   • Base item     ($variant_id === null) → fetch ONLY rows WHERE variant_id IS NULL.
 *                                            Returns [] if the base item has no recipe.
 *
 * NEVER falls back from variant → base or base → variant.
 * Callers that receive [] must treat the item as unavailable and block the order.
 *
 * Returned row shape (matches what ingredientsHelper expects):
 *   ingredient_id, ingredient_name, quantity_required, stock_quantity
 */
function getEffectiveRecipe(PDO $pdo, int $item_id, ?int $variant_id): array
{
    if ($variant_id !== null) {
        // ── Variant item: only the variant-specific recipe ────────────────────
        $stmt = $pdo->prepare("
            SELECT
                r.ingredient_id,
                i.ingredient_name,
                r.quantity_required,
                i.stock_quantity
            FROM recipes r
            JOIN ingredients i ON i.ingredient_id = r.ingredient_id
            WHERE r.item_id    = :item_id
              AND r.variant_id = :variant_id
        ");
        $stmt->execute([
            ':item_id'    => $item_id,
            ':variant_id' => $variant_id,
        ]);
    } else {
        // ── Base item: only the base recipe (variant_id IS NULL) ──────────────
        $stmt = $pdo->prepare("
            SELECT
                r.ingredient_id,
                i.ingredient_name,
                r.quantity_required,
                i.stock_quantity
            FROM recipes r
            JOIN ingredients i ON i.ingredient_id = r.ingredient_id
            WHERE r.item_id    = :item_id
              AND r.variant_id IS NULL
        ");
        $stmt->execute([':item_id' => $item_id]);
    }

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Returns true only if the given variant has at least one recipe row configured.
 * Used by buildStockDataForVariant() to distinguish "no recipe" from "out of stock".
 */
function variantHasRecipe(PDO $pdo, int $item_id, int $variant_id): bool
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM recipes
        WHERE item_id = ? AND variant_id = ?
    ");
    $stmt->execute([$item_id, $variant_id]);
    return (int) $stmt->fetchColumn() > 0;
}

/**
 * Returns true only if the base item has at least one recipe row configured
 * (variant_id IS NULL).
 */
function baseItemHasRecipe(PDO $pdo, int $item_id): bool
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM recipes
        WHERE item_id = ? AND variant_id IS NULL
    ");
    $stmt->execute([$item_id]);
    return (int) $stmt->fetchColumn() > 0;
}