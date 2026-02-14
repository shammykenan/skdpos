<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../models/ingredients.php';
require_once __DIR__ . '/../core/ingredientsHelper.php';
$baseUrl = $_ENV['BASE_URL'] ?? '';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied. Please log in as an admin.';
    header("Location: index.php?page=logout");
    exit;
}

// ----------------- Handle POST Requests -----------------

// 1️⃣ Add Ingredient
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_ingredient'])) {
    $name = trim($_POST['name'] ?? '');
    $stock = trim($_POST['stock'] ?? '');
    $unit = trim($_POST['unit'] ?? '');
    $low_alert = trim($_POST['low_stock_alert'] ?? 10);

    if (empty($name) || empty($stock) || empty($unit)) {
        $_SESSION['error'] = 'All fields are required.';
    } elseif (!is_numeric($stock) || $stock < 0) {
        $_SESSION['error'] = 'Stock must be a non-negative number.';
    } elseif (!in_array($unit, ['kg','g','l','ml','pcs'])) {
        $_SESSION['error'] = 'Invalid unit.';
    } else {
        addIngredient($pdo, $name, (float)$stock, $unit, (float)$low_alert);
        $_SESSION['success'] = 'Ingredient added successfully.';
    }

    header("Location: index.php?page=manage_ingredients");
    exit;
}

// 2️⃣ Update Ingredient
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_ingredient'])) {
    $ingredient_id = (int) ($_POST['ingredient_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $stock = trim($_POST['stock'] ?? '');
    $unit = trim($_POST['unit'] ?? '');
    $low_alert = trim($_POST['low_stock_alert'] ?? 10);

    if ($ingredient_id <= 0) {
        $_SESSION['error'] = 'Invalid ingredient ID.';
    } elseif (empty($name) || empty($stock) || empty($unit)) {
        $_SESSION['error'] = 'All fields are required.';
    } elseif (!is_numeric($stock) || $stock < 0) {
        $_SESSION['error'] = 'Stock must be a non-negative number.';
    } elseif (!in_array($unit, ['kg','g','l','ml','pcs'])) {
        $_SESSION['error'] = 'Invalid unit.';
    } else {
        updateIngredient($pdo, $ingredient_id, $name, (float)$stock, $unit, (float)$low_alert);
        $_SESSION['success'] = 'Ingredient updated successfully.';
    }

    header("Location: index.php?page=manage_ingredients");
    exit;
}

/*
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_ingredient'])) {
    $ingredient_id = (int) ($_POST['ingredient_id'] ?? 0);
    if ($ingredient_id > 0) {
        deleteIngredient($pdo, $ingredient_id);
        $_SESSION['success'] = 'Ingredient deleted successfully.';
    }
    header("Location: index.php?page=manage_ingredients");
    exit;
}*/

// Fetch all ingredients
$ingredients = getAllIngredients($pdo);

// Load the view
require_once __DIR__ . '/../views/admin/manage_ingredients.php';
