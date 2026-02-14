<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../models/addons.php';
require_once __DIR__ . '/../core/addonsHelper.php';
require_once __DIR__ . '/../core/ingredientsHelper.php';
$baseUrl = getenv('BASE_URL') ?: 'http://localhost/coffee_shop/public';
// Auth check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied. Please log in as an admin.';
    header("Location: index.php?page=logout");
    exit;
}

// Initialize PDO
$pdo = new PDO(
    "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8mb4",
    $_ENV['DB_USER'],
    $_ENV['DB_PASS'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

// Handle Add Add-on
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_addon'])) {
    $addon_name = trim($_POST['addon_name'] ?? '');
    $price = $_POST['price'] ?? '';
    $ingredient_id = $_POST['ingredient_id'] ?? '';
    $quantity_used = $_POST['quantity_used'] ?? '';

    if (empty($addon_name) || empty($price) || empty($ingredient_id) || empty($quantity_used)) {
        $_SESSION['error'] = 'Please fill in all required fields.';
        header('Location: index.php?page=manage_addons');
        exit;
    }

    if (!is_numeric($price) || !is_numeric($quantity_used)) {
        $_SESSION['error'] = 'Price and quantity must be numeric.';
        header('Location: index.php?page=manage_addons');
        exit;
    }

    addAddon($pdo, $addon_name, (float)$price, (int)$ingredient_id, (float)$quantity_used);
    $_SESSION['success'] = 'Add-on added successfully.';
    header('Location: index.php?page=manage_addons');
    exit;
}

// Handle Edit Add-on
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_addon'])) {
    $addon_id = (int) ($_POST['addon_id'] ?? 0);
    $addon_name = trim($_POST['addon_name'] ?? '');
    $price = $_POST['price'] ?? '';
    $ingredient_id = $_POST['ingredient_id'] ?? '';
    $quantity_used = $_POST['quantity_used'] ?? '';
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (empty($addon_name) || empty($price) || empty($ingredient_id) || empty($quantity_used)) {
        $_SESSION['error'] = 'Please fill in all required fields.';
        header('Location: index.php?page=manage_addons');
        exit;
    }

    updateAddon($pdo, $addon_id, $addon_name, (float)$price, (int)$ingredient_id, (float)$quantity_used, $is_active);
    $_SESSION['success'] = 'Add-on updated successfully.';
    header('Location: index.php?page=manage_addons');
    exit;
}

// Handle Delete Add-on
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_addon'])) {
    $addon_id = (int) ($_POST['addon_id'] ?? 0);
    deleteAddon($pdo, $addon_id);
    $_SESSION['success'] = 'Add-on deleted successfully.';
    header('Location: index.php?page=manage_addons');
    exit;
}

// Fetch data for view
$addons = getAllAddons($pdo);
$ingredients = getIngredientsForBeverages($pdo); // for dropdown

// Load view
require_once __DIR__ . '/../views/admin/manage_addons.php';
