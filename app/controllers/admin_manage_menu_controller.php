<?php
// app/controllers/manage_menu_controller.php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../models/menu_items.php';
require_once __DIR__ . '/../models/dashboard.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../core/itemsHelper.php';
require_once __DIR__ . '/../models/categories.php';
require_once __DIR__ . '/../models/variants.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../config');
$dotenv->load();
$baseUrl = $_ENV['BASE_URL'];

$success = $_SESSION['success'] ?? '';
$error   = $_SESSION['error']   ?? '';
unset($_SESSION['success'], $_SESSION['error']);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied. Please log in as an admin.';
    header('Location: ' . $baseUrl . '/index.php?page=logout');
    exit;
}

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
// MENU ITEM: ADD (with optional variants)
// Rule: if variants provided → base_price = 0, cost_price = 0
//       if no variants → base_price required > 0, cost_price >= 0
// ================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_menu_item'])) {
    $item_name   = trim($_POST['item_name']   ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = (int) ($_POST['category_id'] ?? 0);

    // Collect variant arrays
    $variant_names  = array_map('trim', $_POST['variant_names']  ?? []);
    $variant_prices = $_POST['variant_prices'] ?? [];
    $variant_costs  = $_POST['variant_costs']  ?? [];
    $variant_active = $_POST['variant_active'] ?? [];

    // Filter out blank variant rows
    $clean_variants = [];
    for ($i = 0; $i < count($variant_names); $i++) {
        $vname  = $variant_names[$i];
        $vprice = isset($variant_prices[$i]) ? (float) $variant_prices[$i] : 0;
        if ($vname === '') continue;
        $clean_variants[] = [
            'name'      => $vname,
            'price'     => $vprice,
            'cost'      => isset($variant_costs[$i]) ? (float) $variant_costs[$i] : 0,
            'is_active' => isset($variant_active[$i]) ? 1 : 0,
        ];
    }

    $has_variants = !empty($clean_variants);

    // --- Basic validation ---
    if (empty($item_name)) {
        $_SESSION['error'] = 'Item name is required.';
        header("Location: $baseUrl/index.php?page=manage_menu"); exit;
    }
    if ($category_id <= 0) {
        $_SESSION['error'] = 'A valid category is required.';
        header("Location: $baseUrl/index.php?page=manage_menu"); exit;
    }

    if ($has_variants) {
        // VARIANT MODE: validate each variant price > 0
        foreach ($clean_variants as $v) {
            if ($v['price'] <= 0) {
                $_SESSION['error'] = "Variant '{$v['name']}' must have a price greater than 0.";
                header("Location: $baseUrl/index.php?page=manage_menu"); exit;
            }
            if ($v['cost'] < 0) {
                $_SESSION['error'] = "Variant '{$v['name']}' cost price cannot be negative.";
                header("Location: $baseUrl/index.php?page=manage_menu"); exit;
            }
        }
        // Variant mode: store 0.00 for base fields (NOT NULL constraint satisfied)
        $base_price = 0.00;
        $cost_price = 0.00;

    } else {
        // BASE PRICE MODE: base_price required and > 0
        $raw_base = $_POST['base_price'] ?? '';
        $raw_cost = $_POST['cost_price'] ?? '';

        if ($raw_base === '' || !is_numeric($raw_base) || (float)$raw_base <= 0) {
            $_SESSION['error'] = 'Base price is required and must be greater than 0 when no variants are added.';
            header("Location: $baseUrl/index.php?page=manage_menu"); exit;
        }
        if ($raw_cost === '' || !is_numeric($raw_cost) || (float)$raw_cost < 0) {
            $_SESSION['error'] = 'Cost price must be 0 or greater.';
            header("Location: $baseUrl/index.php?page=manage_menu"); exit;
        }

        $base_price = (float) $raw_base;
        $cost_price = (float) $raw_cost;

        if ($cost_price > $base_price) {
            $_SESSION['error'] = 'Cost price cannot exceed the base price.';
            header("Location: $baseUrl/index.php?page=manage_menu"); exit;
        }
    }

    // --- Image upload ---
    $image_url = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = mime_content_type($_FILES['image']['tmp_name']);
        if (!in_array($file_type, $allowed_types)) {
            $_SESSION['error'] = 'Invalid image type. Allowed: JPG, PNG, GIF, WebP.';
            header("Location: $baseUrl/index.php?page=manage_menu"); exit;
        }
        if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $_SESSION['error'] = 'Image must be under 2MB.';
            header("Location: $baseUrl/index.php?page=manage_menu"); exit;
        }
        $ext        = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename   = 'item_' . time() . '_' . uniqid() . '.' . $ext;
        $upload_dir = __DIR__ . '/../../public/assets/images/menu/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename)) {
            $image_url = 'assets/images/menu/' . $filename;
        }
    }

    // --- Persist in transaction ---
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("
            INSERT INTO menu_items (item_name, description, base_price, cost_price, category_id, image_url, is_available)
            VALUES (?, ?, ?, ?, ?, ?, 1)
        ");
        $stmt->execute([$item_name, $description, $base_price, $cost_price, $category_id, $image_url]);
        $new_item_id = (int) $pdo->lastInsertId();

        if ($has_variants) {
            $stmt_v = $pdo->prepare("
                INSERT INTO menu_variants (item_id, variant_name, price, cost_price, is_active)
                VALUES (?, ?, ?, ?, ?)
            ");
            foreach ($clean_variants as $v) {
                $stmt_v->execute([$new_item_id, $v['name'], $v['price'], $v['cost'], $v['is_active']]);
            }
        }

        $pdo->commit();
        $_SESSION['success'] = "Menu item '{$item_name}' added successfully.";
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Failed to add menu item: " . $e->getMessage());
        $_SESSION['error'] = 'Failed to add menu item. Please try again.';
    }

    header("Location: $baseUrl/index.php?page=manage_menu");
    exit;
}

// ================================================================
// MENU ITEM: UPDATE
// Rule: after update, if item has variants → keep base_price = 0.
//       if no variants and base_price = 0 → mark unavailable.
// ================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_menu_item'])) {
    $item_id      = (int)   ($_POST['item_id']      ?? 0);
    $item_name    = trim(    $_POST['item_name']     ?? '');
    $description  = trim(    $_POST['description']   ?? '');
    $category_id  = (int)   ($_POST['category_id']   ?? 0);
    $is_available = isset($_POST['is_available']) ? 1 : 0;

    if (!$item_id || empty($item_name) || $category_id <= 0) {
        $_SESSION['error'] = 'Please fill in all required fields.';
        header("Location: $baseUrl/index.php?page=manage_menu"); exit;
    }

    $existingItem = getMenuItemById($pdo, $item_id);
    if (!$existingItem) {
        $_SESSION['error'] = 'Menu item not found.';
        header("Location: $baseUrl/index.php?page=manage_menu"); exit;
    }

    // Check if this item currently has any variants
    $stmt_vc = $pdo->prepare("SELECT COUNT(*) FROM menu_variants WHERE item_id = ?");
    $stmt_vc->execute([$item_id]);
    $variant_count     = (int) $stmt_vc->fetchColumn();
    $item_has_variants = $variant_count > 0;

    if ($item_has_variants) {
        // Variants exist → base fields must stay 0.00 (pricing comes from variants)
        $base_price   = 0.00;
        $cost_price   = 0.00;
        // Don't force unavailable — variant pricing is valid
    } else {
        // No variants → validate base_price > 0
        $raw_base = $_POST['base_price'] ?? '';
        $raw_cost = $_POST['cost_price'] ?? '';

        if ($raw_base === '' || !is_numeric($raw_base) || (float)$raw_base <= 0) {
            $_SESSION['error'] = 'Base price is required and must be greater than 0 when no variants exist.';
            header("Location: $baseUrl/index.php?page=manage_menu"); exit;
        }
        if ($raw_cost === '' || !is_numeric($raw_cost) || (float)$raw_cost < 0) {
            $_SESSION['error'] = 'Cost price must be 0 or greater.';
            header("Location: $baseUrl/index.php?page=manage_menu"); exit;
        }

        $base_price = (float) $raw_base;
        $cost_price = (float) $raw_cost;

        if ($cost_price > $base_price) {
            $_SESSION['error'] = 'Cost price cannot exceed the base price.';
            header("Location: $baseUrl/index.php?page=manage_menu"); exit;
        }

        // ── GUARD: if somehow base_price is still 0 here, force unavailable ──
        if ($base_price <= 0) {
            $is_available = 0;
        }
    }

    // --- Image handling ---
    $image_url = $existingItem['image_url'];

    if (isset($_POST['delete_image']) && $_POST['delete_image'] == '1') {
        if ($image_url && file_exists(__DIR__ . '/../../' . $image_url)) {
            unlink(__DIR__ . '/../../' . $image_url);
        }
        $image_url = null;
    }

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = mime_content_type($_FILES['image']['tmp_name']);
        if (!in_array($file_type, $allowed_types)) {
            $_SESSION['error'] = 'Invalid image type.';
            header("Location: $baseUrl/index.php?page=manage_menu"); exit;
        }
        if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $_SESSION['error'] = 'Image must be under 2MB.';
            header("Location: $baseUrl/index.php?page=manage_menu"); exit;
        }
        if ($existingItem['image_url'] && file_exists(__DIR__ . '/../../' . $existingItem['image_url'])) {
            unlink(__DIR__ . '/../../' . $existingItem['image_url']);
        }
        $ext        = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename   = 'item_' . time() . '_' . uniqid() . '.' . $ext;
        $upload_dir = __DIR__ . '/../../public/assets/images/menu/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename)) {
            $image_url = 'assets/images/menu/' . $filename;
        }
    }

    if (updateMenuItem($pdo, $item_id, $item_name, $description, $base_price, $cost_price, $category_id, $image_url, $is_available)) {
        $_SESSION['success'] = "Menu item '{$item_name}' updated successfully.";
    } else {
        $_SESSION['error'] = 'Failed to update menu item. Please try again.';
    }

    header("Location: $baseUrl/index.php?page=manage_menu");
    exit;
}

// ================================================================
// MENU ITEM: DELETE
// ================================================================
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
    header("Location: $baseUrl/index.php?page=manage_menu");
    exit;
}

// ================================================================
// VARIANT: ADD OR UPDATE
// Rule: after adding a variant → base_price stays 0.00 (variant mode)
//       item remains available (variant pricing is valid)
// ================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['variant_action'])) {
    $action     = $_POST['variant_action'];
    $item_id    = (int)   ($_POST['item_id']      ?? 0);
    $variant_id = (int)   ($_POST['variant_id']   ?? 0);
    $name       = trim(   $_POST['variant_name']  ?? '');
    $price      = (float) ($_POST['price']        ?? 0);
    $cost_price = (float) ($_POST['cost_price']   ?? 0);
    $is_active  = isset($_POST['is_active']) ? 1 : 0;

    if ($action === 'add') {
        if (!$item_id || $name === '') {
            $_SESSION['error'] = 'Variant name is required.';
        } elseif ($price <= 0) {
            $_SESSION['error'] = 'Variant price must be greater than 0.';
        } elseif ($cost_price < 0) {
            $_SESSION['error'] = 'Variant cost price cannot be negative.';
        } elseif (variantNameExists($pdo, $item_id, $name)) {
            $_SESSION['error'] = "A variant named '{$name}' already exists for this item.";
        } else {
            $new_variant_id = addVariant($pdo, $item_id, $name, $price, $cost_price, $is_active);
            if ($new_variant_id) {
                // Variant mode: set base_price = 0.00 (NOT NULL satisfied, pricing via variants)
                // Keep is_available as-is — variant pricing is valid
                $pdo->prepare("
                    UPDATE menu_items
                    SET base_price = 0.00, cost_price = 0.00
                    WHERE item_id = ?
                ")->execute([$item_id]);
                $_SESSION['success'] = "Variant '{$name}' added. Item now uses variant pricing.";
            } else {
                $_SESSION['error'] = 'Failed to add variant.';
            }
        }

    } elseif ($action === 'update') {
        if (!$variant_id || $name === '') {
            $_SESSION['error'] = 'Variant name is required.';
        } elseif ($price <= 0) {
            $_SESSION['error'] = 'Variant price must be greater than 0.';
        } elseif ($cost_price < 0) {
            $_SESSION['error'] = 'Variant cost price cannot be negative.';
        } else {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM menu_variants
                WHERE item_id = ? AND TRIM(LOWER(variant_name)) = TRIM(LOWER(?)) AND variant_id != ?
            ");
            $stmt->execute([$item_id, $name, $variant_id]);
            if ((int)$stmt->fetchColumn() > 0) {
                $_SESSION['error'] = "A variant named '{$name}' already exists for this item.";
            } else {
                if (updateVariant($pdo, $variant_id, $name, $price, $cost_price, (bool)$is_active)) {
                    $_SESSION['success'] = "Variant '{$name}' updated successfully.";
                } else {
                    $_SESSION['error'] = 'Failed to update variant.';
                }
            }
        }
    }

    header('Location: ' . $baseUrl . '/index.php?page=manage_menu');
    exit;
}

// ================================================================
// VARIANT: DELETE
// ================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_variant'])) {
    $variant_id = (int) ($_POST['variant_id'] ?? 0);
    if (!$variant_id) {
        $_SESSION['error'] = 'Invalid variant.';
        header('Location: ' . $baseUrl . '/index.php?page=manage_menu'); exit;
    }

    // Find the item_id before deleting
    $stmt = $pdo->prepare("SELECT item_id FROM menu_variants WHERE variant_id = ?");
    $stmt->execute([$variant_id]);
    $row              = $stmt->fetch();
    $affected_item_id = $row ? (int)$row['item_id'] : 0;

    if (deleteVariant($pdo, $variant_id)) {
        if ($affected_item_id) {
            $stmt2 = $pdo->prepare("SELECT COUNT(*) FROM menu_variants WHERE item_id = ?");
            $stmt2->execute([$affected_item_id]);
            $remaining = (int) $stmt2->fetchColumn();

            if ($remaining === 0) {
                // ── CRITICAL: No variants left → item switches back to base pricing mode.
                // Set base_price = 0.00 AND mark UNAVAILABLE so it cannot be ordered
                // at ₱0.00. Admin MUST edit the item and set a valid base price before
                // it can be made available again.
                $pdo->prepare("
                    UPDATE menu_items
                    SET base_price = 0.00,
                        cost_price = 0.00,
                        is_available = 0
                    WHERE item_id = ?
                ")->execute([$affected_item_id]);

                $_SESSION['success'] = "Variant deleted. The item has been marked unavailable — please set a base price before making it available again.";
            } else {
                $_SESSION['success'] = 'Variant deleted successfully.';
            }
        } else {
            $_SESSION['success'] = 'Variant deleted successfully.';
        }
    } else {
        $_SESSION['error'] = 'Failed to delete variant.';
    }

    header('Location: ' . $baseUrl . '/index.php?page=manage_menu');
    exit;
}

// ================================================================
// EDIT ITEM: pre-populate (GET)
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
// FETCH DATA FOR VIEW
// ================================================================
$menu_items = getAllItemsWithVariants($pdo) ?? [];
$categories = getAllCategories($pdo) ?? [];

// Load all variants grouped by item_id (active + inactive)
$variants_grouped = [];
try {
    $stmt = $pdo->query("SELECT * FROM menu_variants ORDER BY item_id, price ASC");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $variant) {
        $variants_grouped[(int)$variant['item_id']][] = $variant;
    }
} catch (Exception $e) {
    error_log("Error loading variants: " . $e->getMessage());
}

// Build prepared items with clear pricing source
$prepared_items         = [];
$total_profit_potential = 0;
$total_margin_sum       = 0;
$items_with_price_count = 0;

foreach ($menu_items as $item) {
    $item_id         = (int)($item['item_id'] ?? 0);
    $variants        = $variants_grouped[$item_id] ?? [];
    $active_variants = array_values(array_filter($variants, fn($v) => $v['is_active']));
    $has_variants    = !empty($variants);

    if ($has_variants) {
        $prices = array_column($active_variants ?: $variants, 'price');
        $costs  = array_column($active_variants ?: $variants, 'cost_price');

        $prepared_item = [
            'item_id'        => $item['item_id'],
            'item_name'      => $item['item_name'],
            'description'    => $item['description'] ?? '',
            'image_url'      => $item['image_url'],
            'is_available'   => $item['is_available'],
            'category_id'    => $item['category_id'],
            'category_name'  => $item['category_name'],
            'has_variants'   => true,
            'variants'       => $variants,
            'active_variants'=> $active_variants,
            'active_count'   => count($active_variants),
            'total_variants' => count($variants),
            'base_price'     => 0,   // variant mode — base price is irrelevant
            'cost_price'     => 0,
            'price_min'      => !empty($prices) ? min($prices) : 0,
            'price_max'      => !empty($prices) ? max($prices) : 0,
        ];

        foreach ($active_variants as $v) {
            $profit = $v['price'] - $v['cost_price'];
            $total_profit_potential += $profit;
            if ($v['price'] > 0) {
                $total_margin_sum += ($profit / $v['price']) * 100;
                $items_with_price_count++;
            }
        }

    } else {
        $base   = (float)($item['base_price'] ?? 0);
        $cost   = (float)($item['cost_price'] ?? 0);
        $profit = $base - $cost;
        $total_profit_potential += $profit;

        if ($base > 0) {
            $total_margin_sum += ($profit / $base) * 100;
            $items_with_price_count++;
        }

        $prepared_item = [
            'item_id'        => $item['item_id'],
            'item_name'      => $item['item_name'],
            'description'    => $item['description'] ?? '',
            'image_url'      => $item['image_url'],
            'is_available'   => $item['is_available'],
            'category_id'    => $item['category_id'],
            'category_name'  => $item['category_name'],
            'has_variants'   => false,
            'variants'       => [],
            'active_variants'=> [],
            'active_count'   => 0,
            'total_variants' => 0,
            'base_price'     => $base,
            'cost_price'     => $cost,
            // ── ZERO PRICE FLAG: used by view to show warning ──────────────
            'needs_price'    => $base <= 0,
            'price_min'      => $base,
            'price_max'      => $base,
        ];
    }

    $prepared_items[] = $prepared_item;
}

// ── Warn admin about base-only items with no price ────────────────────────────
$items_needing_price = array_filter($prepared_items, fn($i) => !$i['has_variants'] && ($i['base_price'] ?? 0) <= 0);
if (!empty($items_needing_price) && empty($error)) {
    $names = implode(', ', array_map(fn($i) => "\"{$i['item_name']}\"", $items_needing_price));
    $error = "⚠️ The following items need a base price — they are hidden from the POS until fixed: {$names}";
}

$avgProfitMargin = $items_with_price_count > 0 ? ($total_margin_sum / $items_with_price_count) : 0;
$totalItems      = count($prepared_items);
$availableItems  = array_filter($prepared_items, fn($i) => $i['is_available']);
$menu_items      = $prepared_items;
$totalProfit     = $total_profit_potential;

include __DIR__ . '/../views/admin/manage_menu.php';