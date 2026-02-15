<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../models/recipes.php';
require_once __DIR__ . '/../core/recipesHelper.php';
require_once __DIR__ . '/../core/itemsHelper.php';
require_once __DIR__ . '/../core/ingredientsHelper.php';
$baseUrl = $_ENV['BASE_URL'] ?? '';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied. Please log in as an admin.';
    header("Location: index.php?page=logout");
    exit;
}

// Add recipe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_recipe'])) {
    $item_id           = (int)   ($_POST['item_id']           ?? 0);
    $ingredient_id     = (int)   ($_POST['ingredient_id']     ?? 0);
    $quantity_required = (float) ($_POST['quantity_required'] ?? 0);

    if ($item_id && $ingredient_id && $quantity_required > 0) {
        if (recipeExists($pdo, $item_id, $ingredient_id)) {
            $_SESSION['error'] = 'This ingredient is already assigned to the selected menu item.';
        } else {
            addRecipe($pdo, $item_id, $ingredient_id, $quantity_required);
            $_SESSION['success'] = 'Ingredient assigned to menu item.';
        }
    } else {
        $_SESSION['error'] = 'All fields are required.';
    }

    header('Location: ' . $baseUrl . '/index.php?page=manage_recipes');
    exit;
}

// Update recipe
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

// Delete recipe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_recipe'])) {
    $recipe_id = (int) ($_POST['recipe_id'] ?? 0);
    if ($recipe_id) {
        deleteRecipe($pdo, $recipe_id);
        $_SESSION['success'] = 'Recipe deleted.';
    }

    header('Location: ' . $baseUrl . '/index.php?page=manage_recipes');
    exit;
}

// Fetch data
$menu_items  = getAllItems($pdo);
$ingredients = getAllIngredients($pdo);
$recipes     = getAllRecipes($pdo);

// Build image lookup: item_id => image_url  (from $menu_items)
$menu_image_map = [];
foreach ($menu_items as $mi) {
    $menu_image_map[$mi['item_id']] = $mi['image_url'] ?? '';
}

// Group recipes by menu item, attaching image_url from the lookup
$grouped = [];
foreach ($recipes as $r) {
    $id = $r['item_id'];
    if (!isset($grouped[$id])) {
        $grouped[$id] = [
            'item_id'   => $id,
            'item_name' => $r['item_name'],
            'image_url' => $menu_image_map[$id] ?? '',
            'rows'      => [],
        ];
    }
    $grouped[$id]['rows'][] = $r;
}

require_once __DIR__ . '/../views/admin/manage_recipes.php';