<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../models/ingredients.php';
require_once __DIR__ . '/../core/ingredientsHelper.php';

$baseUrl = $_ENV['BASE_URL'] ?? '';

// ── Auth Guard ────────────────────────────────────────────────────────────────
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied. Please log in as an admin.';
    header("Location: index.php?page=logout");
    exit;
}

// =============================================================================
// POST HANDLERS
// =============================================================================

// ── 1. Add Ingredient ─────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_ingredient'])) {
    $name      = trim($_POST['name']            ?? '');
    $stock     = trim($_POST['stock']           ?? '');
    $unit_id   = (int)($_POST['unit_id']        ?? 0);   // FK — now an integer
    $low_alert = trim($_POST['low_stock_alert'] ?? 10);

    $valid_ids = getValidUnitIds($pdo);

    if (empty($name) || $stock === '' || $unit_id <= 0) {
        $_SESSION['error'] = 'All fields are required.';
    } elseif (!is_numeric($stock) || (float)$stock < 0) {
        $_SESSION['error'] = 'Stock must be a non-negative number.';
    } elseif (!in_array($unit_id, $valid_ids)) {
        $_SESSION['error'] = 'Invalid unit selected.';
    } else {
        if (addIngredient($pdo, $name, (float)$stock, $unit_id, (float)$low_alert)) {
            $_SESSION['success'] = "Ingredient '$name' added successfully.";
        } else {
            $_SESSION['error'] = 'Failed to add ingredient. Name may already exist.';
        }
    }

    header("Location: index.php?page=manage_ingredients");
    exit;
}

// ── 2. Edit Ingredient ────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_ingredient'])) {
    $ingredient_id = (int)($_POST['ingredient_id']  ?? 0);
    $name          = trim($_POST['name']             ?? '');
    $stock         = trim($_POST['stock']            ?? '');
    $unit_id       = (int)($_POST['unit_id']         ?? 0);   // FK — now an integer
    $low_alert     = trim($_POST['low_stock_alert']  ?? 10);

    $valid_ids = getValidUnitIds($pdo);

    if ($ingredient_id <= 0) {
        $_SESSION['error'] = 'Invalid ingredient ID.';
    } elseif (empty($name) || $stock === '' || $unit_id <= 0) {
        $_SESSION['error'] = 'All fields are required.';
    } elseif (!is_numeric($stock) || (float)$stock < 0) {
        $_SESSION['error'] = 'Stock must be a non-negative number.';
    } elseif (!in_array($unit_id, $valid_ids)) {
        $_SESSION['error'] = 'Invalid unit selected.';
    } else {
        if (updateIngredient($pdo, $ingredient_id, $name, (float)$stock, $unit_id, (float)$low_alert)) {
            $_SESSION['success'] = "Ingredient '$name' updated successfully.";
        } else {
            $_SESSION['error'] = 'Failed to update ingredient.';
        }
    }

    header("Location: index.php?page=manage_ingredients");
    exit;
}

// ── 3. Delete Ingredient ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_ingredient'])) {
    $ingredient_id = (int)($_POST['ingredient_id'] ?? 0);

    if ($ingredient_id <= 0) {
        $_SESSION['error'] = 'Invalid ingredient selected.';
    } elseif (deleteIngredient($pdo, $ingredient_id)) {
        $_SESSION['success'] = 'Ingredient deleted successfully.';
    } else {
        $_SESSION['error'] = 'Cannot delete ingredient: it is currently in use in recipes or add-ons.';
    }

    header("Location: index.php?page=manage_ingredients");
    exit;
}

// ── 4. Add Unit ───────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_unit'])) {
    $code  = strtolower(trim($_POST['unit_code']  ?? ''));
    $label = trim($_POST['unit_label'] ?? '');

    if (empty($code) || empty($label)) {
        $_SESSION['error'] = 'Unit code and label are required.';
    } elseif (!preg_match('/^[a-z0-9_]{1,20}$/', $code)) {
        $_SESSION['error'] = 'Unit code must be lowercase letters/numbers/underscores only (max 20 chars).';
    } else {
        if (addUnit($pdo, $code, $label)) {
            $_SESSION['success'] = "Unit '$label' added successfully.";
        } else {
            $_SESSION['error'] = "Unit code '$code' already exists.";
        }
    }

    header("Location: index.php?page=manage_ingredients");
    exit;
}

// ── 5. Edit Unit ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_unit'])) {
    $unit_id = (int)($_POST['unit_id']   ?? 0);
    $code    = strtolower(trim($_POST['unit_code']  ?? ''));
    $label   = trim($_POST['unit_label'] ?? '');

    if ($unit_id <= 0 || empty($code) || empty($label)) {
        $_SESSION['error'] = 'All unit fields are required.';
    } elseif (!preg_match('/^[a-z0-9_]{1,20}$/', $code)) {
        $_SESSION['error'] = 'Unit code must be lowercase letters/numbers/underscores only.';
    } else {
        if (updateUnit($pdo, $unit_id, $code, $label)) {
            $_SESSION['success'] = "Unit '$label' updated successfully.";
        } else {
            $_SESSION['error'] = 'Failed to update unit. The code may already be taken.';
        }
    }

    header("Location: index.php?page=manage_ingredients");
    exit;
}

// ── 6. Delete Unit ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_unit'])) {
    $unit_id = (int)($_POST['unit_id'] ?? 0);

    if ($unit_id <= 0) {
        $_SESSION['error'] = 'Invalid unit selected.';
    } elseif (deleteUnit($pdo, $unit_id)) {
        $_SESSION['success'] = 'Unit deleted successfully.';
    } else {
        $_SESSION['error'] = 'Cannot delete unit: it is currently assigned to one or more ingredients.';
    }

    header("Location: index.php?page=manage_ingredients");
    exit;
}

// =============================================================================
// DATA FOR VIEW
// =============================================================================

$ingredients = getAllIngredients($pdo);          // now includes unit_code, unit_label via JOIN
$units_raw   = getAllUnits($pdo);                // full unit rows with icon — for Manage Units modal
$units       = getUnitsForDropdown($pdo);        // [unit_id => unit_label] — for <select> dropdowns

// Load the view
require_once __DIR__ . '/../views/admin/manage_ingredients.php';