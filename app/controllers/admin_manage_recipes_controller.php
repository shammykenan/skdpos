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
    $item_id = (int) ($_POST['item_id'] ?? 0);
    $ingredient_id = (int) ($_POST['ingredient_id'] ?? 0);
    $quantity_required = (float) ($_POST['quantity_required'] ?? 0);

    if ($item_id && $ingredient_id && $quantity_required > 0) {
        // VALIDATION: Check if this menu item + ingredient already exists
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
    $recipe_id = (int) ($_POST['recipe_id'] ?? 0);
    $quantity_required = (float) ($_POST['quantity_required'] ?? 0);
    
    if ($recipe_id && $quantity_required > 0) {
        // For edits, we don't need to check duplicate because item_id and ingredient_id are not being changed
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

$menu_items = getAllItems($pdo);
$ingredients = getAllIngredients($pdo);
$recipes = getAllRecipes($pdo);
require_once __DIR__ . '/../views/admin/manage_recipes.php';
?>