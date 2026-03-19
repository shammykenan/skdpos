<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../models/menu_items.php';
require_once __DIR__ . '/../models/dashboard.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../core/itemsHelper.php';
require_once __DIR__ . '/../models/categories.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../config');
$dotenv->load();
$baseUrl = $_ENV['BASE_URL'];

// Initialize message variables
$success = $_SESSION['success'] ?? '';
$error   = $_SESSION['error']   ?? '';

// Clear session messages after reading them
unset($_SESSION['success'], $_SESSION['error']);

// Check authentication and authorization
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied. Please log in as an admin.';
    header('Location: ' . $baseUrl . '/index.php?page=logout');
    exit;
}

// Initialize database connection
try {
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8mb4",
        $_ENV['DB_USER'],
        $_ENV['DB_PASS'],
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// ================================================================
// CATEGORY: ADD
// ================================================================
if (isset($_POST['add_category'])) {
    $name = trim($_POST['category_name'] ?? '');
    if (empty($name)) {
        $_SESSION['error'] = "Category name is required.";
    } elseif (categoryNameExists($pdo, $name)) {
        $_SESSION['error'] = "A category named '{$name}' already exists.";
    } else {
        try {
            addCategory($pdo, $name);
            $_SESSION['success'] = "Category '{$name}' added successfully.";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Failed to add category. Please try again.";
        }
    }
    header("Location: $baseUrl/index.php?page=manage_menu");
    exit;
}

// ================================================================
// CATEGORY: UPDATE
// ================================================================
if (isset($_POST['update_category'])) {
    $id   = (int) ($_POST['category_id']  ?? 0);
    $name = trim($_POST['category_name']  ?? '');
    if (!$id) {
        $_SESSION['error'] = "Invalid category.";
    } elseif (empty($name)) {
        $_SESSION['error'] = "Category name cannot be empty.";
    } elseif (categoryNameExists($pdo, $name, $id)) {
        $_SESSION['error'] = "A category named '{$name}' already exists.";
    } else {
        try {
            updateCategory($pdo, $id, $name);
            $_SESSION['success'] = "Category updated to '{$name}' successfully.";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Failed to update category. Please try again.";
        }
    }
    header("Location: $baseUrl/index.php?page=manage_menu");
    exit;
}

// ================================================================
// CATEGORY: DELETE
// ================================================================
if (isset($_POST['delete_category'])) {
    $id = (int) ($_POST['category_id'] ?? 0);
    if (!$id) {
        $_SESSION['error'] = "Invalid category.";
    } elseif (categoryHasItems($pdo, $id)) {
        $_SESSION['error'] = "Cannot delete: this category is assigned to one or more menu items.";
    } else {
        try {
            deleteCategory($pdo, $id);
            $_SESSION['success'] = "Category deleted successfully.";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Failed to delete category. Please try again.";
        }
    }
    header("Location: $baseUrl/index.php?page=manage_menu");
    exit;
}

// ================================================================
// MENU ITEM: ADD
// ================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_menu_item'])) {
    $item_name   = trim($_POST['item_name']   ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = $_POST['price']            ?? '';
    $cost_price  = $_POST['cost_price']       ?? '';
    $category_id = $_POST['category_id']      ?? '';

    if (empty($item_name) || empty($price) || empty($cost_price) || empty($category_id)) {
        $_SESSION['error'] = 'Please fill in all required fields.';
        header("Location: $baseUrl/index.php?page=manage_menu");
        exit;
    }

    if (!is_numeric($price) || !is_numeric($cost_price)) {
        $_SESSION['error'] = 'Price and cost price must be numeric.';
        header("Location: $baseUrl/index.php?page=manage_menu");
        exit;
    }

    $price       = (float) $price;
    $cost_price  = (float) $cost_price;
    $category_id = (int)   $category_id;

    if ($cost_price > $price) {
        $_SESSION['error'] = 'Cost price cannot be higher than selling price.';
        header("Location: $baseUrl/index.php?page=manage_menu");
        exit;
    }

    $image_url = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir  = __DIR__ . '/../../public/assets/images/menu/';
        $publicPath = 'assets/images/menu/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileTmp  = $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $fileSize = $_FILES['image']['size'];
        $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($fileExt, $allowedExt)) {
            $_SESSION['error'] = 'Invalid image type. Allowed: jpg, jpeg, png, gif, webp.';
            header("Location: $baseUrl/index.php?page=manage_menu");
            exit;
        }

        if ($fileSize > 2 * 1024 * 1024) {
            $_SESSION['error'] = 'Image size exceeds 2MB limit.';
            header("Location: $baseUrl/index.php?page=manage_menu");
            exit;
        }

        $newFileName = time() . '_' . uniqid() . '.' . $fileExt;
        $targetPath  = $uploadDir . $newFileName;

        $check = getimagesize($fileTmp);
        if ($check === false) {
            $_SESSION['error'] = 'File is not a valid image.';
            header("Location: $baseUrl/index.php?page=manage_menu");
            exit;
        }

        if (move_uploaded_file($fileTmp, $targetPath)) {
            $image_url = $publicPath . $newFileName;
        } else {
            $_SESSION['error'] = 'Failed to upload image.';
            header("Location: $baseUrl/index.php?page=manage_menu");
            exit;
        }
    }

    $result = addMenuItem($pdo, $item_name, $description, $price, $cost_price, $category_id, $image_url);

    if ($result) {
        $_SESSION['success'] = 'Menu item added successfully.';
    } else {
        $_SESSION['error'] = 'Failed to add menu item. Please try again.';
        error_log('Failed to add menu item: ' . json_encode($pdo->errorInfo()));
    }

    header("Location: $baseUrl/index.php?page=manage_menu");
    exit;
}

// ================================================================
// MENU ITEM: UPDATE
// ================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_menu_item'])) {
    $item_id      = (int) ($_POST['item_id']     ?? 0);
    $item_name    = trim($_POST['item_name']      ?? '');
    $description  = trim($_POST['description']    ?? '');
    $price        = $_POST['price']               ?? '';
    $cost_price   = $_POST['cost_price']          ?? '';
    $category_id  = $_POST['category_id']         ?? '';
    $is_available = isset($_POST['is_available']) ? 1 : 0;

    if (empty($item_id) || empty($item_name) || empty($price) || empty($cost_price) || empty($category_id)) {
        $_SESSION['error'] = 'Please fill in all required fields.';
        header("Location: $baseUrl/index.php?page=manage_menu");
        exit;
    }

    if (!is_numeric($price) || !is_numeric($cost_price)) {
        $_SESSION['error'] = 'Price and cost price must be numeric.';
        header("Location: $baseUrl/index.php?page=manage_menu");
        exit;
    }

    $price       = (float) $price;
    $cost_price  = (float) $cost_price;
    $category_id = (int)   $category_id;

    if ($cost_price > $price) {
        $_SESSION['error'] = 'Cost price cannot be higher than selling price.';
        header("Location: $baseUrl/index.php?page=manage_menu");
        exit;
    }

    $existingItem = getMenuItemById($pdo, $item_id);
    if (!$existingItem) {
        $_SESSION['error'] = 'Menu item not found.';
        header("Location: $baseUrl/index.php?page=manage_menu");
        exit;
    }

    $image_url = $existingItem['image_url'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir  = __DIR__ . '/../../public/assets/images/menu/';
        $publicPath = 'assets/images/menu/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileTmp  = $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $fileSize = $_FILES['image']['size'];
        $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($fileExt, $allowedExt)) {
            $_SESSION['error'] = 'Invalid image type. Allowed: jpg, jpeg, png, gif, webp.';
            header("Location: $baseUrl/index.php?page=manage_menu");
            exit;
        }

        if ($fileSize > 2 * 1024 * 1024) {
            $_SESSION['error'] = 'Image size exceeds 2MB limit.';
            header("Location: $baseUrl/index.php?page=manage_menu");
            exit;
        }

        $newFileName = time() . '_' . uniqid() . '.' . $fileExt;
        $targetPath  = $uploadDir . $newFileName;

        $check = getimagesize($fileTmp);
        if ($check === false) {
            $_SESSION['error'] = 'File is not a valid image.';
            header("Location: $baseUrl/index.php?page=manage_menu");
            exit;
        }

        if (move_uploaded_file($fileTmp, $targetPath)) {
            if (!empty($existingItem['image_url']) &&
                !str_contains($existingItem['image_url'], 'default') &&
                file_exists(__DIR__ . '/../../public/' . $existingItem['image_url'])) {
                unlink(__DIR__ . '/../../public/' . $existingItem['image_url']);
            }
            $image_url = $publicPath . $newFileName;
        } else {
            $_SESSION['error'] = 'Failed to upload image.';
            header("Location: $baseUrl/index.php?page=manage_menu");
            exit;
        }
    }

    if (isset($_POST['delete_image']) && $_POST['delete_image'] == '1') {
        if (!empty($existingItem['image_url']) &&
            !str_contains($existingItem['image_url'], 'default') &&
            file_exists(__DIR__ . '/../../public/' . $existingItem['image_url'])) {
            unlink(__DIR__ . '/../../public/' . $existingItem['image_url']);
        }
        $image_url = null;
    }

    $result = updateMenuItem($pdo, $item_id, $item_name, $description, $price, $cost_price, $category_id, $image_url, $is_available);

    if ($result) {
        $_SESSION['success'] = 'Menu item updated successfully.';
    } else {
        $_SESSION['error'] = 'Failed to update menu item. Please try again.';
        error_log('Failed to update menu item: ' . json_encode($pdo->errorInfo()));
    }

    header("Location: $baseUrl/index.php?page=manage_menu");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_menu_item'])) {
    $item_id = (int) ($_POST['item_id'] ?? 0);

    if ($item_id > 0) {
        if (deleteMenuItem($pdo, $item_id)) {
            $_SESSION['success'] = 'Menu item deleted successfully.';
        } else {
            $_SESSION['error'] = 'Cannot delete menu item: it has been ordered before.';
        }
    } else {
        $_SESSION['error'] = 'Invalid menu item.';
    }

    header("Location: index.php?page=manage_menu");
    exit;
}
// ================================================================
// MENU ITEM: EDIT pre-populate (GET)
// ================================================================
$item_to_edit = null;
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['edit'])) {
    $edit_item_id = (int) $_GET['edit'];
    $item_to_edit = getMenuItemById($pdo, $edit_item_id);

    if (!$item_to_edit) {
        $_SESSION['error'] = 'Menu item not found.';
        header("Location: $baseUrl/index.php?page=manage_menu");
        exit;
    }
}

// ================================================================
// MENU ITEM: DELETE (GET)
// ================================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete'])) {
    $delete_item_id = (int) $_GET['delete'];

    $item_to_delete = getMenuItemById($pdo, $delete_item_id);
    if ($item_to_delete) {
        if (!empty($item_to_delete['image_url']) &&
            !str_contains($item_to_delete['image_url'], 'default') &&
            file_exists(__DIR__ . '/../../public/' . $item_to_delete['image_url'])) {
            unlink(__DIR__ . '/../../public/' . $item_to_delete['image_url']);
        }

        $sql    = "DELETE FROM menu_items WHERE item_id = :item_id";
        $stmt   = $pdo->prepare($sql);
        $result = $stmt->execute([':item_id' => $delete_item_id]);

        if ($result) {
            $_SESSION['success'] = 'Menu item deleted successfully.';
        } else {
            $_SESSION['error'] = 'Failed to delete menu item.';
        }
    } else {
        $_SESSION['error'] = 'Menu item not found.';
    }

    header("Location: $baseUrl/index.php?page=manage_menu");
    exit;
}

// ================================================================
// FETCH DATA FOR VIEW
// ================================================================
$menu_items = getAllItemsWithIngredients($pdo) ?? [];
$categories = getAllCategories($pdo)           ?? [];

$availableItems  = [];
$totalItems      = 0;
$totalProfit     = 0;
$avgProfitMargin = 0;

if (!empty($menu_items)) {
    $availableItems = array_filter($menu_items, function($item) {
        return ($item['is_available'] ?? 0) == 1;
    });

    $totalItems = count($menu_items);

    $totalProfit = array_reduce($menu_items, function($carry, $item) {
        return $carry + (($item['price'] ?? 0) - ($item['cost_price'] ?? 0));
    }, 0);

    $totalMargin    = 0;
    $itemsWithPrice = 0;

    foreach ($menu_items as $item) {
        $price = $item['price'] ?? 0;
        if ($price > 0) {
            $cost   = $item['cost_price'] ?? 0;
            $profit = $price - $cost;
            $margin = ($profit / $price) * 100;
            $totalMargin += $margin;
            $itemsWithPrice++;
        }
    }

    $avgProfitMargin = $itemsWithPrice > 0 ? ($totalMargin / $itemsWithPrice) : 0;
}

// ================================================================
// LOAD VIEW
// ================================================================
include __DIR__ . '/../views/admin/manage_menu.php';