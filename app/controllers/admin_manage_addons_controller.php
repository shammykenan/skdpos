<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../models/addons.php';
require_once __DIR__ . '/../core/addonsHelper.php';
require_once __DIR__ . '/../core/ingredientsHelper.php';
require_once __DIR__ . '/../core/csrfHelper.php';
csrf_require('html');

// Base URL configuration for InfinityFree
// Update this with your actual domain on InfinityFree

// Auth check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied. Please log in as an admin.';
    header("Location: index.php?page=logout");
    exit;
}

// Use the PDO connection from config/db.php

// ── Handle Add Add-on ─────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_addon'])) {
    $addon_name    = trim($_POST['addon_name']    ?? '');
    $price         = $_POST['price']         ?? '';
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

// ── Handle Edit Add-on ────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_addon'])) {
    $addon_id      = (int) ($_POST['addon_id']      ?? 0);
    $addon_name    = trim($_POST['addon_name']    ?? '');
    $price         = $_POST['price']         ?? '';
    $ingredient_id = $_POST['ingredient_id'] ?? '';
    $quantity_used = $_POST['quantity_used'] ?? '';
    $is_active     = isset($_POST['is_active']) ? 1 : 0;

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

// ── Handle Delete Add-on ──────────────────────────────────────────────────────
// deleteAddon() in addons.php checks order_item_addons first.
// If the addon has been used in past orders it is soft-deleted (is_active = 0)
// to satisfy the FK constraint and preserve order history.
// If it has never been ordered it is hard-deleted.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_addon'])) {
    $addon_id = (int) ($_POST['addon_id'] ?? 0);

    // Check usage before calling delete so we can show the right message
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM order_item_addons WHERE addon_id = :id");
    $checkStmt->execute([':id' => $addon_id]);
    $wasUsed = (int) $checkStmt->fetchColumn() > 0;

    deleteAddon($pdo, $addon_id);

    $_SESSION['success'] = $wasUsed
        ? 'Add-on deactivated — it has order history so it was hidden rather than deleted.'
        : 'Add-on deleted successfully.';

    header('Location: index.php?page=manage_addons');
    exit;
}

// ── Handle Assign Add-ons ───────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_addons'])) {
    $item_id = (int) ($_POST['item_id'] ?? 0);
    $selected_addons = $_POST['selected_addons'] ?? [];

    if (empty($item_id)) {
        $_SESSION['error'] = 'Please select a product.';
        header('Location: index.php?page=manage_addons');
        exit;
    }

    try {
        // Start transaction
        $pdo->beginTransaction();

        // Delete existing assignments for this product
        $deleteStmt = $pdo->prepare("DELETE FROM product_addons WHERE item_id = :item_id");
        $deleteStmt->execute([':item_id' => $item_id]);

        // Insert new assignments
        if (!empty($selected_addons)) {
            $insertStmt = $pdo->prepare("INSERT INTO product_addons (item_id, addon_id) VALUES (:item_id, :addon_id)");
            foreach ($selected_addons as $addon_id) {
                $insertStmt->execute([
                    ':item_id' => $item_id,
                    ':addon_id' => (int)$addon_id
                ]);
            }
        }

        $pdo->commit();
        $_SESSION['success'] = 'Add-on assignments saved successfully.';
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'Error saving assignments: ' . $e->getMessage();
    }

    header('Location: index.php?page=manage_addons');
    exit;
}

// ── Handle Get Assignments (AJAX) ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_assignments') {
    $item_id = (int) ($_GET['item_id'] ?? 0);
    
    header('Content-Type: application/json');
    
    if (empty($item_id)) {
        echo json_encode(['assigned_addons' => []]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT addon_id FROM product_addons WHERE item_id = :item_id");
        $stmt->execute([':item_id' => $item_id]);
        $assignments = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo json_encode(['assigned_addons' => $assignments]);
    } catch (Exception $e) {
        echo json_encode(['assigned_addons' => [], 'error' => $e->getMessage()]);
    }
    exit;
}

// ── Fetch data for view ───────────────────────────────────────────────────────
$addons      = getAllAddons($pdo);
$ingredients = getAllIngredients($pdo); 

// Get menu items for the assign add-ons modal
$menuItemsStmt = $pdo->prepare("SELECT item_id, item_name FROM menu_items ORDER BY item_name");
$menuItemsStmt->execute();
$menu_items = $menuItemsStmt->fetchAll(PDO::FETCH_ASSOC);

// Load view
require_once __DIR__ . '/../views/admin/manage_addons.php';