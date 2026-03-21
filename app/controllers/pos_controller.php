<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../core/itemsHelper.php';
require_once __DIR__ . '/../core/ingredientsHelper.php';
require_once __DIR__ . '/../core/addonsHelper.php';
require_once __DIR__ . '/../core/posHelper.php';
require_once __DIR__ . '/../core/authHelper.php';
require_once __DIR__ . '/../core/tableHelper.php';   // ← NEW
require_once __DIR__ . '/../models/pos.php';

requireRole(['admin', 'cashier']);
$baseUrl = $_ENV['BASE_URL'] ?? '';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'cashier'])) {
    $_SESSION['error'] = 'Access denied. Please log in as admin or cashier.';
    header("Location: index.php?page=login");
    exit;
}

// ── Load menu items, addons, stock data ──────────────────────────────────────
$menu_items = getAllAvailableItems($pdo);
foreach ($menu_items as &$item) {
    $item['ingredients'] = getIngredientsForItem($pdo, $item['item_id']);
}
unset($item);

$addons_map = [];
foreach ($menu_items as $item) {
    $addons_map[(int)$item['item_id']] = getAddonsForMenuItem($pdo, (int)$item['item_id']);
}

$stock_data = [];
foreach ($menu_items as $item) {
    $ingredients = getMenuItemIngredientStock($pdo, $item['item_id']);
    $max_qty = PHP_INT_MAX;
    $limiting_ingredient = null;
    foreach ($ingredients as $ing) {
        if ($ing['quantity_required'] <= 0) continue;
        $possible = floor($ing['stock_quantity'] / $ing['quantity_required']);
        if ($possible < $max_qty) {
            $max_qty = $possible;
            $limiting_ingredient = [
                'name'              => $ing['ingredient_name'],
                'stock'             => $ing['stock_quantity'],
                'required_per_unit' => $ing['quantity_required'],
            ];
        }
    }
    $max_qty   = ($max_qty === PHP_INT_MAX || $max_qty < 0) ? 0 : $max_qty;
    $has_stock = $max_qty > 0;
    $stock_data[$item['item_id']] = [
        'has_stock'           => $has_stock,
        'max_quantity'        => $max_qty,
        'limiting_ingredient' => $limiting_ingredient,
        'ingredients'         => $ingredients,
    ];
}

$addon_ids = [];
foreach ($addons_map as $item_addons) {
    foreach ($item_addons as $addon) { $addon_ids[] = $addon['addon_id']; }
}
$addon_ids        = array_unique($addon_ids);
$addon_stock_data = getAddonsStockInfo($pdo, $addon_ids);

// ── Load available tables for dropdown ───────────────────────────────────────
$available_tables = getAvailableTables($pdo);   // ← NEW

// ── Handle order submission ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_order'])) {

    $customer_name = trim($_POST['customer_name'] ?? '') ?: null;
    $order_type    = trim($_POST['order_type']    ?? '');
    $order_source  = trim($_POST['order_source']  ?? 'walk_in');
    $cart_data     = json_decode($_POST['cart_data'] ?? '[]', true);

    // ── Table resolution ─────────────────────────────────────────────────────
    // For dine-in we now use table_id (FK). table_name is stored for display.
    $table_id     = null;
    $table_number = null;  // label kept for receipts / legacy columns

    if ($order_type === 'dine_in') {
        $raw_table_id = (int) ($_POST['table_id'] ?? 0);
        if ($raw_table_id <= 0) {
            $_SESSION['error'] = 'Please select a table for dine-in orders.';
            header("Location: index.php?page=manage_pos");
            exit;
        }
        $tableRow = getTableById($pdo, $raw_table_id);
        if (!$tableRow) {
            $_SESSION['error'] = 'Selected table not found.';
            header("Location: index.php?page=manage_pos");
            exit;
        }
        if ($tableRow['status'] !== 'available') {
            $_SESSION['error'] = "Table \"{$tableRow['table_name']}\" is already occupied. Please choose another.";
            header("Location: index.php?page=manage_pos");
            exit;
        }
        $table_id     = $raw_table_id;
        $table_number = $tableRow['table_name'];  // e.g. "Table 3"
    }

    if (empty($cart_data)) {
        $_SESSION['error'] = 'No items selected.';
        header("Location: index.php?page=manage_pos");
        exit;
    }

    if (empty($order_type)) {
        $_SESSION['error'] = 'Please select an order type.';
        header("Location: index.php?page=manage_pos");
        exit;
    }

    // ── Cumulative stock validation ───────────────────────────────────────────
    $ingredient_demand = [];

    foreach ($cart_data as $cart_item) {
        $item_id  = $cart_item['id'];
        $item_qty = (int)($cart_item['quantity'] ?? 1);
        if ($item_qty <= 0) continue;

        foreach (getMenuItemIngredientStock($pdo, $item_id) as $ing) {
            if ($ing['quantity_required'] <= 0) continue;
            $id = $ing['ingredient_id'];
            if (!isset($ingredient_demand[$id])) {
                $ingredient_demand[$id] = ['name' => $ing['ingredient_name'], 'needed' => 0, 'available' => (float)$ing['stock_quantity']];
            }
            $ingredient_demand[$id]['needed'] += $ing['quantity_required'] * $item_qty;
        }

        foreach ($cart_item['addons'] ?? [] as $ad) {
            $addon_qty = (int)($ad['quantity'] ?? 1);
            if ($addon_qty <= 0) continue;
            foreach (getAddonIngredientStock($pdo, (int)$ad['id']) as $ing) {
                if ($ing['quantity_required'] <= 0) continue;
                $id = $ing['ingredient_id'];
                if (!isset($ingredient_demand[$id])) {
                    $ingredient_demand[$id] = ['name' => $ing['ingredient_name'], 'needed' => 0, 'available' => (float)$ing['stock_quantity']];
                }
                $ingredient_demand[$id]['needed'] += $ing['quantity_required'] * $addon_qty;
            }
        }
    }

    foreach ($ingredient_demand as $info) {
        if ($info['needed'] > $info['available']) {
            $_SESSION['error'] = sprintf(
                'Insufficient stock: "%s" needs %.2f but only %.2f available.',
                $info['name'], $info['needed'], $info['available']
            );
            header("Location: index.php?page=manage_pos");
            exit;
        }
    }

    // ── Compute totals ────────────────────────────────────────────────────────
    $subtotal      = 0;
    $order_details = [];

    foreach ($cart_data as $cart_item) {
        $item_id   = $cart_item['id'];
        $item_qty  = (int)($cart_item['quantity'] ?? 1);
        $item_note = trim((string)($cart_item['note'] ?? '')) ?: null;
        if ($item_qty <= 0) continue;

        $item = getMenuItemById($pdo, $item_id);
        if (!$item) continue;

        $unit_price = $item['price'];
        $line_total = $unit_price * $item_qty;
        $addons_for_item = [];

        foreach ($cart_item['addons'] ?? [] as $ad) {
            $addon_id   = (int)$ad['id'];
            $addon_qty  = (int)($ad['quantity'] ?? 1);
            $addon_data = getAddonById($pdo, $addon_id);
            if (!$addon_data) continue;
            if (!in_array($addon_id, array_column($addons_map[$item_id] ?? [], 'addon_id'))) continue;

            $addon_line = $addon_data['price'] * $addon_qty;
            $addons_for_item[] = ['addon_id' => $addon_id, 'quantity' => $addon_qty, 'price' => $addon_data['price'], 'total_price' => $addon_line];
            $line_total += $addon_line;
        }

        $subtotal       += $line_total;
        $order_details[] = ['item_id' => $item_id, 'quantity' => $item_qty, 'unit_price' => $unit_price, 'line_total' => $line_total, 'item_note' => $item_note, 'addons' => $addons_for_item];
    }

    $discount     = 0;
    $total_amount = $subtotal - $discount;

    // ── Persist ───────────────────────────────────────────────────────────────
    try {
        $pdo->beginTransaction();

        // createOrder now also calls occupyTable() internally
        $order_id = createOrder(
            $pdo,
            $_SESSION['user_id'],
            $customer_name,
            $table_number,     // display label
            $order_type,
            $order_source,
            $subtotal,
            $discount,
            $total_amount,
            $table_id          // FK
        );

        $stmt = $pdo->prepare("SELECT order_number FROM orders WHERE order_id = ?");
        $stmt->execute([$order_id]);
        $order_number = $stmt->fetchColumn();

        foreach ($order_details as $detail) {
            $order_item_id = createOrderItem($pdo, $order_id, $detail['item_id'], $detail['quantity'], $detail['unit_price'], $detail['item_note']);

            foreach ($detail['addons'] as $ad) {
                createOrderItemAddon($pdo, $order_item_id, $ad['addon_id'], $ad['quantity'], $ad['price']);
            }

            deductIngredientsForItem($pdo, $detail['item_id'], $detail['quantity']);

            foreach ($detail['addons'] as $ad) {
                deductIngredientsForAddon($pdo, $ad['addon_id'], $ad['quantity']);
            }
        }

        $pdo->commit();
        $_SESSION['success'] = "Order {$order_number} submitted successfully.";

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'Order failed: ' . $e->getMessage();
    }

    header("Location: index.php?page=manage_pos");
    exit;
}

require_once __DIR__ . '/../views/cashier/manage_pos.php';