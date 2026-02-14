<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../core/itemsHelper.php';
require_once __DIR__ . '/../core/ingredientsHelper.php';
require_once __DIR__ . '/../core/addonsHelper.php';
require_once __DIR__ . '/../core/posHelper.php';
require_once __DIR__ . '/../models/pos.php';
require_once __DIR__ . '/../core/authHelper.php';

requireRole(['admin', 'cashier']);
$baseUrl = $_ENV['BASE_URL'] ?? '';

// -------------------------
// 1️⃣ Auth check
// -------------------------
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin','cashier'])) {
    $_SESSION['error'] = 'Access denied. Please log in as admin or cashier.';
    header("Location: index.php?page=login");
    exit;
}

$menu_items = getAllAvailableItems($pdo);
foreach ($menu_items as &$item) {
    $item['ingredients'] = getIngredientsForItem($pdo, $item['item_id']);
}


$addons_map = [];
foreach ($menu_items as $item) {
    // Pass the category_id to get addons for this specific category
    $addons_map[$item['item_id']] = getAddonsForItemCategory($pdo, $item['category_id']);
}

$stock_data = [];
foreach ($menu_items as $item) {
    $ingredients = getMenuItemIngredientStock($pdo, $item['item_id']);
    
    $max_qty = PHP_INT_MAX;
    $limiting_ingredient = null;

    foreach ($ingredients as $ing) {
        // Skip ingredients with 0 required (not used in the recipe)
        if ($ing['quantity_required'] <= 0) {
            continue;
        }

        $possible_qty = floor($ing['stock_quantity'] / $ing['quantity_required']);
        if ($possible_qty < $max_qty) {
            $max_qty = $possible_qty;
            $limiting_ingredient = [
                'name' => $ing['ingredient_name'],
                'stock' => $ing['stock_quantity'],
                'required_per_unit' => $ing['quantity_required']
            ];
        }
    }

    $max_qty = ($max_qty === PHP_INT_MAX || $max_qty < 0) ? 0 : $max_qty;
    $has_stock = $max_qty > 0;

    $stock_data[$item['item_id']] = [
        'has_stock' => $has_stock,
        'max_quantity' => $max_qty,
        'limiting_ingredient' => $limiting_ingredient,
        'ingredients' => $ingredients
    ];
}
foreach ($addons_map as $item_addons) {
    foreach ($item_addons as $addon) {
        $addon_ids[] = $addon['addon_id'];
    }
}
$addon_ids = array_unique($addon_ids);
$addon_stock_data = getAddonsStockInfo($pdo, $addon_ids);

// Also include add-on stock data in the JavaScript
$addon_stock_json = json_encode($addon_stock_data);
// -------------------------
// 3️⃣ Handle Order Submission
// -------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_order'])) {
    // ... (rest of your order submission code remains the same)
    
    // Get form data with proper trimming and defaults
    $customer_name = isset($_POST['customer_name']) ? trim($_POST['customer_name']) : null;
    $table_number  = isset($_POST['table_number']) ? trim($_POST['table_number']) : null;
    $order_type    = isset($_POST['order_type']) ? trim($_POST['order_type']) : '';
    $order_source  = isset($_POST['order_source']) ? trim($_POST['order_source']) : 'walk_in';

    // Convert empty strings to null for database
    if ($customer_name === '') $customer_name = null;
    if ($table_number === '') $table_number = null;

    $cart_data = json_decode($_POST['cart_data'] ?? '[]', true);

    if (empty($cart_data)) {
        $_SESSION['error'] = 'No items selected.';
        header("Location: index.php?page=manage_pos");
        exit;
    }

    // Validate order type
    if (empty($order_type)) {
        $_SESSION['error'] = 'Please select an order type.';
        header("Location: index.php?page=manage_pos");
        exit;
    }

    // Validate table number for dine-in orders
    if ($order_type === 'dine_in' && empty($table_number)) {
        $_SESSION['error'] = 'Table number is required for dine-in orders.';
        header("Location: index.php?page=manage_pos");
        exit;
    }

    // For take-out orders, ensure table_number is null
    if ($order_type === 'takeout') {
        $table_number = null;
    }

    // -------------------------
    // 🔎 STOCK VALIDATION FIRST
    // -------------------------
    foreach ($cart_data as $cart_item) {
        $item_id = $cart_item['id'];
        $qty     = (int)($cart_item['quantity'] ?? 1);
        if ($qty <= 0) continue;

        if (!checkIngredientsStockForItem($pdo, $item_id, $qty)) {
            $_SESSION['error'] = 'Insufficient ingredients for ' . $cart_item['name'] . '.';
            header("Location: index.php?page=manage_pos");
            exit;
        }

        if (!empty($cart_item['addons'])) {
            foreach ($cart_item['addons'] as $ad) {
                $addon_id  = $ad['id'];
                $addon_qty = (int)($ad['quantity'] ?? 1);

                if (!checkIngredientsStockForAddon($pdo, $addon_id, $addon_qty)) {
                    $_SESSION['error'] = 'Insufficient ingredients for add-on.';
                    header("Location: index.php?page=manage_pos");
                    exit;
                }
            }
        }
    }

    // -------------------------
    // 💰 Compute totals
    // -------------------------
    $subtotal = 0;
    $order_details = [];

    foreach ($cart_data as $cart_item) {
        $item_id = $cart_item['id'];
        $qty     = (int)($cart_item['quantity'] ?? 1);
        if ($qty <= 0) continue;

        $item = getMenuItemById($pdo, $item_id);
        if (!$item) continue;

        $unit_price = $item['price'];
        $line_total = $unit_price * $qty;

        $addons_for_item = [];

        if (!empty($cart_item['addons']) && !empty($addons_map[$item_id])) {
            foreach ($cart_item['addons'] as $ad) {
                $addon_id  = $ad['id'];
                $addon_qty = (int)($ad['quantity'] ?? 1);

                $addon_data = getAddonById($pdo, $addon_id);
                if (!$addon_data) continue;

                if (!in_array($addon_id, array_column($addons_map[$item_id], 'addon_id'))) {
                    continue;
                }

                $addon_total_price = $addon_data['price'] * $addon_qty;
                
                $addons_for_item[] = [
                    'addon_id' => $addon_id,
                    'quantity' => $addon_qty,
                    'price'    => $addon_data['price'],
                    'total_price' => $addon_total_price
                ];

                $line_total += $addon_total_price;
            }
        }

        $subtotal += $line_total;

        $order_details[] = [
            'item_id'     => $item_id,
            'quantity'    => $qty,
            'unit_price'  => $unit_price,
            'line_total'  => $line_total,
            'addons'      => $addons_for_item
        ];
    }

    // ============================================================
    // 🔥 TAX CALCULATION - 12% VAT
    // ============================================================
    $discount = 0;
    $vatable_sales = $subtotal;
    $vat = $vatable_sales * 0.12;
    $vat_exempt = 0;
    $vat_discount = 0;
    $total_amount = $vatable_sales + $vat - $vat_discount - $discount;
    $amount_due = $total_amount;
    $rounded_total = round($total_amount, 2);
  
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
            $total_amount
        );

        // ✅ Get the order_number for this order
        $stmt = $pdo->prepare("SELECT order_number FROM orders WHERE order_id = ?");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        $order_number = $order['order_number'];

        foreach ($order_details as $detail) {
            $order_item_id = createOrderItem(
                $pdo,
                $order_id,
                $detail['item_id'],
                $detail['quantity'],
                $detail['unit_price']
            );

            foreach ($detail['addons'] as $ad) {
                createOrderItemAddon(
                    $pdo,
                    $order_item_id,
                    $ad['addon_id'],
                    $ad['quantity'],
                    $ad['price']
                );
            }

            deductIngredientsForItem($pdo, $detail['item_id'], $detail['quantity']);

            foreach ($detail['addons'] as $ad) {
                deductIngredientsForAddon($pdo, $ad['addon_id'], $ad['quantity']);
            }
        }

        $pdo->commit();
        
        // ✅ FIXED: Now shows order_number, not order_id
        $_SESSION['success'] = 'Order ' . $order_number . ' submitted successfully.';

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'Order failed: ' . $e->getMessage();
    }

    header("Location: index.php?page=manage_pos");
    exit;
}
require_once __DIR__ . '/../views/cashier/manage_pos.php';
