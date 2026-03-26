<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../models/recipes.php';
require_once __DIR__ . '/../models/variants.php';         // for getAllVariantsGrouped()
require_once __DIR__ . '/../core/recipesHelper.php';
require_once __DIR__ . '/../core/itemsHelper.php';
require_once __DIR__ . '/../core/ingredientsHelper.php';
$baseUrl = $_ENV['BASE_URL'] ?? '';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied. Please log in as an admin.';
    header("Location: index.php?page=logout");
    exit;
}

// ================================================================
// ADD RECIPE (single handler — variant-aware)
// ================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_recipe'])) {
    $item_id           = (int)   ($_POST['item_id']           ?? 0);
    $ingredient_id     = (int)   ($_POST['ingredient_id']     ?? 0);
    $quantity_required = (float) ($_POST['quantity_required'] ?? 0);
    $variant_id        = !empty($_POST['variant_id']) ? (int) $_POST['variant_id'] : null;

    if ($item_id && $ingredient_id && $quantity_required > 0) {

        // ── MUTUAL EXCLUSION CHECK ──────────────────────────────
        if ($variant_id === null) {
            // Trying to add a BASE ingredient — block if any variant recipes exist
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM recipes WHERE item_id = ? AND variant_id IS NOT NULL");
            $stmt->execute([$item_id]);
            if ((int)$stmt->fetchColumn() > 0) {
                $_SESSION['error'] = 'This item uses variant recipes. Remove all variant recipes before adding a base recipe.';
                header('Location: ' . $baseUrl . '/index.php?page=manage_recipes');
                exit;
            }
        } else {
            // Trying to add a VARIANT ingredient — block if any base recipes exist
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM recipes WHERE item_id = ? AND variant_id IS NULL");
            $stmt->execute([$item_id]);
            if ((int)$stmt->fetchColumn() > 0) {
                $_SESSION['error'] = 'This item uses a base recipe. Remove all base ingredients before adding variant recipes.';
                header('Location: ' . $baseUrl . '/index.php?page=manage_recipes');
                exit;
            }
        }
        // ────────────────────────────────────────────────────────

        if (recipeExistsForVariant($pdo, $item_id, $ingredient_id, $variant_id)) {
            $_SESSION['error'] = 'This ingredient is already assigned to that recipe.';
        } else {
            addRecipeForVariant($pdo, $item_id, $ingredient_id, $quantity_required, $variant_id);
            $_SESSION['success'] = 'Ingredient assigned to recipe.';
        }
    } else {
        $_SESSION['error'] = 'All fields are required and quantity must be greater than 0.';
    }

    header('Location: ' . $baseUrl . '/index.php?page=manage_recipes');
    exit;
}

// ================================================================
// UPDATE RECIPE
// ================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_recipe'])) {
    $recipe_id         = (int)   ($_POST['recipe_id']         ?? 0);
    $quantity_required = (float) ($_POST['quantity_required'] ?? 0);

    if ($recipe_id && $quantity_required > 0) {
        updateRecipe($pdo, $recipe_id, $quantity_required);
        $_SESSION['success'] = 'Recipe updated.';
    } else {
        $_SESSION['error'] = 'Invalid input.';
    }

    header('Location: ' . $baseUrl . '/index.php?page=manage_recipes');
    exit;
}

// ================================================================
// DELETE RECIPE
// ================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_recipe'])) {
    $recipe_id = (int) ($_POST['recipe_id'] ?? 0);
    if ($recipe_id) {
        deleteRecipe($pdo, $recipe_id);
        $_SESSION['success'] = 'Recipe deleted.';
    } else {
        $_SESSION['error'] = 'Invalid recipe.';
    }

    header('Location: ' . $baseUrl . '/index.php?page=manage_recipes');
    exit;
}

// ================================================================
// FETCH DATA FOR VIEW
// ================================================================
$menu_items  = getAllItems($pdo);
$ingredients = getAllIngredients($pdo);
$recipes     = getAllRecipes($pdo);

// Build image lookup: item_id => image_url
$menu_image_map = [];
foreach ($menu_items as $mi) {
    $menu_image_map[$mi['item_id']] = $mi['image_url'] ?? '';
}

// Group recipes by menu item
// 'rows'     = flat list (card ingredient count)
// 'base'     = rows where variant_id IS NULL
// 'variants' = [ variant_name => [rows] ]
$grouped = [];
foreach ($recipes as $r) {
    $id = $r['item_id'];
    if (!isset($grouped[$id])) {
        $grouped[$id] = [
            'item_id'   => $id,
            'item_name' => $r['item_name'],
            'image_url' => $menu_image_map[$id] ?? '',
            'rows'      => [],
            'base'      => [],
            'variants'  => [],
        ];
    }
    $grouped[$id]['rows'][] = $r;
    if ($r['variant_id'] === null) {
        $grouped[$id]['base'][] = $r;
    } else {
        $vName = $r['variant_name'] ?? ('Variant #' . $r['variant_id']);
        $grouped[$id]['variants'][$vName][] = $r;
    }
}

// All variants grouped by item_id — used to populate the variant dropdown
// in the Add Recipe modal for items that don't have any recipes yet.
// getAllVariantsGrouped() is in app/models/variants.php
try {
    $all_variants_map = getAllVariantsGrouped($pdo);
} catch (Exception $e) {
    $all_variants_map = [];
}

require_once __DIR__ . '/../views/admin/manage_recipes.php';