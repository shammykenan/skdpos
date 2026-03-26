<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../core/itemsHelper.php';
require_once __DIR__ . '/../core/ingredientsHelper.php';
require_once __DIR__ . '/../core/recipesHelper.php';
require_once __DIR__ . '/../core/addonsHelper.php';
require_once __DIR__ . '/../core/posHelper.php';
require_once __DIR__ . '/../core/authHelper.php';
require_once __DIR__ . '/../core/tableHelper.php';
require_once __DIR__ . '/../models/pos.php';
require_once __DIR__ . '/../models/variants.php';

requireRole(['admin', 'cashier']);
$baseUrl = $_ENV['BASE_URL'] ?? '';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'cashier'])) {
    $_SESSION['error'] = 'Access denied. Please log in as admin or cashier.';
    header("Location: index.php?page=login");
    exit;
}

// ── AJAX: Auto-save draft ─────────────────────────────────────────────────────
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['action']) &&
    $_POST['action'] === 'save_draft'
) {
    header('Content-Type: application/json');
    try {
        $cart_rows     = json_decode($_POST['cart_data']     ?? '[]', true) ?? [];
        $customer_name = trim($_POST['customer_name']        ?? '') ?: null;
        $order_type    = trim($_POST['order_type']           ?? 'dine_in');
        $table_id      = (int)($_POST['table_id']            ?? 0) ?: null;

        if (empty($cart_rows)) {
            // Cart was cleared — wipe any existing draft
            clearDraftOrder($pdo, $_SESSION['user_id']);
            echo json_encode(['ok' => true, 'cleared' => true]);
        } else {
            $draft_id = upsertDraftOrder(
                $pdo,
                $_SESSION['user_id'],
                $cart_rows,
                $customer_name,
                $order_type,
                $table_id
            );
            echo json_encode(['ok' => true, 'draft_id' => $draft_id]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }
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

// After $menu_items is loaded:
$variants_map = getAllVariantsGrouped($pdo);

// FIX: Correctly nested stock calculation
$stock_data = [];
foreach ($menu_items as $item) {
    $id            = $item['item_id'];
    $item_variants = $variants_map[$id] ?? [];

    if (!empty($item_variants)) {
        // ✅ Variant item — skip base, only compute per-variant stock
        $stock_data[$id]['base'] = [
            'has_stock'    => false,
            'max_quantity' => 0,
            'ingredients'  => [],
        ];
        foreach ($item_variants as $variant) {
            $v_id = (int)$variant['variant_id'];
            $stock_data[$id]['variants'][$v_id] = buildStockDataForVariant($pdo, $id, $v_id);
        }
    } else {
        // ✅ Base-only item — no variants
        $stock_data[$id]['base']     = buildStockDataForVariant($pdo, $id, null);
        $stock_data[$id]['variants'] = [];
    }
}

// ✅ Fix menu card has_stock: variant items are available if ANY variant has stock
foreach ($menu_items as &$item) {
    $id            = $item['item_id'];
    $item_variants = $variants_map[$id] ?? [];

    if (!empty($item_variants)) {
        $item['has_stock'] = collect_any_variant_has_stock($stock_data[$id]['variants'] ?? []);
    } else {
        $item['has_stock'] = $stock_data[$id]['base']['has_stock'] ?? false;
    }
}
unset($item);

$addon_ids = [];
foreach ($addons_map as $item_addons) {
    foreach ($item_addons as $addon) { $addon_ids[] = $addon['addon_id']; }
}
$addon_ids        = array_unique($addon_ids);
$addon_stock_data = getAddonsStockInfo($pdo, $addon_ids);

// ── Load available tables for dropdown ───────────────────────────────────────
$available_tables = getAvailableTables($pdo);

// ── Load cashier's draft cart (for page restore) ─────────────────────────────
$draft_data = loadDraftCart($pdo, $_SESSION['user_id']);
$draft_cart = $draft_data['cart'];
$draft_meta = $draft_data['meta']; // customer_name, order_type, table_id — or null

// ── Handle order submission ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_order'])) {

    $customer_name = trim($_POST['customer_name'] ?? '') ?: null;
    $order_type    = trim($_POST['order_type']    ?? '');
    $order_source  = trim($_POST['order_source']  ?? 'walk_in');
    $cart_data     = json_decode($_POST['cart_data'] ?? '[]', true);

    // ── Table resolution ─────────────────────────────────────────────────────
    $table_id     = null;
    $table_number = null;

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
        $table_number = $tableRow['table_name'];
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
    $item_id    = $cart_item['id'];
    $item_qty   = (int)($cart_item['quantity'] ?? 1);
    $variant_id = isset($cart_item['variant_id']) && $cart_item['variant_id']
                  ? (int)$cart_item['variant_id'] : null;

    // Variant-aware recipe fetch
    $recipe = getEffectiveRecipe($pdo, $item_id, $variant_id);

    foreach ($recipe as $ing) {
        if ($ing['quantity_required'] <= 0) continue;
        $id = $ing['ingredient_id'];
        if (!isset($ingredient_demand[$id])) {
            $ingredient_demand[$id] = [
                'name'      => $ing['ingredient_name'],
                'needed'    => 0,
                'available' => (float)$ing['stock_quantity'],
            ];
        }
        $ingredient_demand[$id]['needed'] += $ing['quantity_required'] * $item_qty;
    }

    // Addon loop — unchanged
    foreach ($cart_item['addons'] ?? [] as $ad) {
        $addon_qty = (int)($ad['quantity'] ?? 1);
        if ($addon_qty <= 0) continue;
        foreach (getAddonIngredientStock($pdo, (int)$ad['id']) as $ing) {
            if ($ing['quantity_required'] <= 0) continue;
            $id = $ing['ingredient_id'];
            if (!isset($ingredient_demand[$id])) {
                $ingredient_demand[$id] = [
                    'name'      => $ing['ingredient_name'],
                    'needed'    => 0,
                    'available' => (float)$ing['stock_quantity'],
                ];
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
    $item_id    = $cart_item['id'];
    $item_qty   = (int)($cart_item['quantity'] ?? 1);
    $variant_id = isset($cart_item['variant_id']) && $cart_item['variant_id']
                  ? (int)$cart_item['variant_id'] : null;
    $item_note  = trim((string)($cart_item['note'] ?? '')) ?: null;
    if ($item_qty <= 0) continue;

    $item = getMenuItemById($pdo, $item_id);
    if (!$item) continue;

    // Resolve price: variant price if variant, else base item price
    $variant_name_snapshot = null;
    if ($variant_id !== null) {
        $variant               = getVariantById($pdo, $variant_id);
        $unit_price            = $variant ? (float)$variant['price'] : (float)$item['price'];
        $variant_name_snapshot = $variant['variant_name'] ?? null;
    } else {
        $unit_price = (float)$item['price'];
    }

    $line_total      = $unit_price * $item_qty;
    $addons_for_item = [];

    foreach ($cart_item['addons'] ?? [] as $ad) {
        $addon_id  = (int)$ad['id'];
        $addon_qty = (int)($ad['quantity'] ?? 1);
        $addon_data = getAddonById($pdo, $addon_id);
        if (!$addon_data) continue;
        if (!in_array($addon_id, array_column($addons_map[$item_id] ?? [], 'addon_id'))) continue;
        $addon_line       = $addon_data['price'] * $addon_qty;
        $addons_for_item[] = [
            'addon_id'   => $addon_id,
            'quantity'   => $addon_qty,
            'price'      => $addon_data['price'],
            'total_price'=> $addon_line,
        ];
        $line_total += $addon_line;
    }

    $subtotal       += $line_total;
    $order_details[] = [
        'item_id'      => $item_id,
        'item_name'    => $item['item_name'],
        'variant_id'   => $variant_id,
        'variant_name' => $variant_name_snapshot,
        'quantity'     => $item_qty,
        'unit_price'   => $unit_price,
        'line_total'   => $line_total,
        'item_note'    => $item_note,
        'addons'       => $addons_for_item,
    ];
}

    $discount     = 0;
    $total_amount = $subtotal - $discount;

    // ── Persist ───────────────────────────────────────────────────────────────
    try {
        $pdo->beginTransaction();

        $order_id = createOrder(
            $pdo,
            $_SESSION['user_id'],
            $customer_name,
            $table_number,
            $order_type,
            $order_source,
            $subtotal,
            $discount,
            $total_amount,
            $table_id
        );

        $stmt = $pdo->prepare("SELECT order_number FROM orders WHERE order_id = ?");
        $stmt->execute([$order_id]);
        $order_number = $stmt->fetchColumn();

        foreach ($order_details as $detail) {
    // Inside foreach ($order_details as $detail):
        $order_item_id = createOrderItem(
            $pdo,
            $order_id,
            $detail['item_id'],
            $detail['item_name'],        // ADD: snapshot captured when building $order_details
            $detail['quantity'],
            $detail['unit_price'],
            $detail['item_note'],
            $detail['variant_id'],
            $detail['variant_name']
        );

    foreach ($detail['addons'] as $ad) {
        createOrderItemAddon($pdo, $order_item_id, $ad['addon_id'], $ad['quantity'], $ad['price']);
    }

    // Variant-aware stock deduction
    deductIngredientsForItem($pdo, $detail['item_id'], $detail['quantity'], $detail['variant_id']);

    foreach ($detail['addons'] as $ad) {
        deductIngredientsForAddon($pdo, $ad['addon_id'], $ad['quantity']);
    }
}

        // ── Clear draft now that the real order is committed ──────────────────
        clearDraftOrder($pdo, $_SESSION['user_id']);

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