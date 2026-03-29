<?php

require_once __DIR__ . '/../../config/db.php';

require_once __DIR__ . '/../models/recipes.php';

require_once __DIR__ . '/../models/variants.php';

require_once __DIR__ . '/../core/recipesHelper.php';

require_once __DIR__ . '/../core/itemsHelper.php';

require_once __DIR__ . '/../core/ingredientsHelper.php';

$baseUrl = $_ENV['BASE_URL'] ?? '';



if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {

    // If this is an AJAX request, return JSON instead of redirecting

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) || !empty($_POST['ajax'])) {

        header('Content-Type: application/json');

        echo json_encode(['ok' => false, 'error' => 'Access denied.']);

        exit;

    }

    $_SESSION['error'] = 'Access denied. Please log in as an admin.';

    header("Location: index.php?page=logout");

    exit;

}



// ================================================================

// AJAX: ADD RECIPE

// POST: ajax=1, add_recipe=1, item_id, ingredient_id,

//       quantity_required, [variant_id]

// Returns JSON {ok, message} — modal stays open, no page reload

// ================================================================

if (

    $_SERVER['REQUEST_METHOD'] === 'POST' &&

    isset($_POST['add_recipe']) &&

    !empty($_POST['ajax'])

) {

    header('Content-Type: application/json');



    $item_id           = (int)   ($_POST['item_id']           ?? 0);

    $ingredient_id     = (int)   ($_POST['ingredient_id']     ?? 0);

    $quantity_required = (float) ($_POST['quantity_required'] ?? 0);

    $variant_id        = !empty($_POST['variant_id']) ? (int) $_POST['variant_id'] : null;



    if (!$item_id || !$ingredient_id || $quantity_required <= 0) {

        echo json_encode(['ok' => false, 'error' => 'All fields are required and quantity must be greater than 0.']);

        exit;

    }



    if (recipeExistsForVariant($pdo, $item_id, $ingredient_id, $variant_id)) {

        echo json_encode(['ok' => false, 'error' => 'This ingredient is already assigned to that recipe.']);

        exit;

    }



    try {

        addRecipeForVariant($pdo, $item_id, $ingredient_id, $quantity_required, $variant_id);

        echo json_encode(['ok' => true, 'message' => 'Ingredient added to recipe successfully.']);

    } catch (Exception $e) {

        echo json_encode(['ok' => false, 'error' => 'Database error: ' . $e->getMessage()]);

    }

    exit;

}



// ================================================================

// COPY BASE RECIPE → VARIANT

// POST: copy_base_to_variant=1, item_id, variant_id

// ================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['copy_base_to_variant'])) {

    $item_id    = (int) ($_POST['item_id']    ?? 0);

    $variant_id = (int) ($_POST['variant_id'] ?? 0);



    if (!$item_id || !$variant_id) {

        $_SESSION['error'] = 'Invalid item or variant.';

        header('Location: ' . $baseUrl . '/index.php?page=manage_recipes');

        exit;

    }



    $stmt = $pdo->prepare("SELECT variant_id FROM menu_variants WHERE variant_id = ? AND item_id = ? LIMIT 1");

    $stmt->execute([$variant_id, $item_id]);

    if (!$stmt->fetch()) {

        $_SESSION['error'] = 'Variant does not belong to this menu item.';

        header('Location: ' . $baseUrl . '/index.php?page=manage_recipes');

        exit;

    }



    try {

        $result = copyBaseRecipeToVariant($pdo, $item_id, $variant_id);



        if ($result['copied'] === 0 && $result['skipped'] === 0) {

            $_SESSION['error'] = 'No base ingredients found to copy.';

        } elseif ($result['copied'] === 0) {

            $_SESSION['success'] = "All base ingredients are already assigned to this variant ({$result['skipped']} skipped).";

        } elseif ($result['skipped'] > 0) {

            $_SESSION['success'] = "{$result['copied']} ingredient(s) copied; {$result['skipped']} already existed and were skipped.";

        } else {

            $_SESSION['success'] = "{$result['copied']} ingredient(s) copied to variant successfully.";

        }

    } catch (Exception $e) {

        $_SESSION['error'] = 'Copy failed: ' . $e->getMessage();

    }



    header('Location: ' . $baseUrl . '/index.php?page=manage_recipes');

    exit;

}



// ================================================================

// ADD RECIPE (non-AJAX fallback — kept for safety)

// ================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_recipe'])) {

    $item_id           = (int)   ($_POST['item_id']           ?? 0);

    $ingredient_id     = (int)   ($_POST['ingredient_id']     ?? 0);

    $quantity_required = (float) ($_POST['quantity_required'] ?? 0);

    $variant_id        = !empty($_POST['variant_id']) ? (int) $_POST['variant_id'] : null;



    if ($item_id && $ingredient_id && $quantity_required > 0) {

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

    $recipe_id = (int) ($_POST['delete_recipe'] ?? $_POST['recipe_id'] ?? 0);

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



$menu_image_map = [];

foreach ($menu_items as $mi) {

    $menu_image_map[$mi['item_id']] = $mi['image_url'] ?? '';

}



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



try {

    $all_variants_map = getAllVariantsGrouped($pdo);

} catch (Exception $e) {

    $all_variants_map = [];

}



$item_variants_for_copy = [];

foreach ($grouped as $gid => $group) {

    if (!empty($group['base']) && !empty($all_variants_map[$gid])) {

        $item_variants_for_copy[$gid] = $all_variants_map[$gid];

    }

}



require_once __DIR__ . '/../views/admin/manage_recipes.php';