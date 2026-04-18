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
require_once __DIR__ . '/../core/VatHelper.php';

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
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ── AJAX: Pay-first — collect payment, create order, record payment ───────────
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['action']) &&
    $_POST['action'] === 'pay_and_submit'
) {
    header('Content-Type: application/json');

    try {
        $customer_name = trim($_POST['customer_name'] ?? '') ?: null;
        $order_type    = trim($_POST['order_type']    ?? '');
        $order_source  = 'walk_in';
        $cart_data     = json_decode($_POST['cart_data'] ?? '[]', true);
        $amount_paid   = (float)($_POST['amount_paid']   ?? 0);
        $change_amount = (float)($_POST['change_amount'] ?? 0);

        // ── Basic validation ──────────────────────────────────────────────────
        if (empty($cart_data))  throw new Exception('Cart is empty.');
        if (empty($order_type)) throw new Exception('Order type is required.');
        if ($amount_paid <= 0)  throw new Exception('Invalid payment amount.');

        $variants_map = getAllVariantsGrouped($pdo);

        // ── Table resolution ──────────────────────────────────────────────────
        $table_id     = null;
        $table_number = null;
        $table_name   = '';

        if ($order_type === 'dine_in') {
            $raw_table_id = (int)($_POST['table_id'] ?? 0);
            if ($raw_table_id <= 0) throw new Exception('Please select a table for dine-in orders.');
            $tableRow = getTableById($pdo, $raw_table_id);
            if (!$tableRow) throw new Exception('Selected table not found.');
            if ($tableRow['status'] !== 'available') {
                throw new Exception("Table \"{$tableRow['table_name']}\" is already occupied.");
            }
            $table_id     = $raw_table_id;
            $table_number = $tableRow['table_name'];
            $table_name   = $tableRow['table_name'];
        }

        // ── Zero / missing price guard ────────────────────────────────────────
        foreach ($cart_data as $cart_item) {
            $item_id       = (int)$cart_item['id'];
            $item_variants = $variants_map[$item_id] ?? [];
            $variant_id    = isset($cart_item['variant_id']) && $cart_item['variant_id']
                             ? (int)$cart_item['variant_id'] : null;

            if (empty($item_variants)) {
                $freshItem = getMenuItemById($pdo, $item_id);
                if (!$freshItem || (float)($freshItem['base_price'] ?? 0) <= 0) {
                    throw new Exception("Item \"{$cart_item['name']}\" has no valid price.");
                }
            } else {
                if ($variant_id !== null) {
                    $vRow = getVariantById($pdo, $variant_id);
                    if (!$vRow || (float)($vRow['price'] ?? 0) <= 0) {
                        throw new Exception("Selected variant for \"{$cart_item['name']}\" has no valid price.");
                    }
                }
            }
        }

        // ── Recipe existence guard ────────────────────────────────────────────
        foreach ($cart_data as $cart_item) {
            $item_id       = (int)$cart_item['id'];
            $variant_id    = isset($cart_item['variant_id']) && $cart_item['variant_id']
                             ? (int)$cart_item['variant_id'] : null;
            $item_variants = $variants_map[$item_id] ?? [];

            if (!empty($item_variants)) {
                if ($variant_id === null) {
                    throw new Exception("Item \"{$cart_item['name']}\" requires a variant.");
                }
                if (!variantHasRecipe($pdo, $item_id, $variant_id)) {
                    $vRow   = getVariantById($pdo, $variant_id);
                    $vLabel = $vRow ? " ({$vRow['variant_name']})" : '';
                    throw new Exception("No recipe for \"{$cart_item['name']}{$vLabel}\".");
                }
            } else {
                if (!baseItemHasRecipe($pdo, $item_id)) {
                    throw new Exception("No recipe for \"{$cart_item['name']}\".");
                }
            }
        }

        // ── Cumulative stock validation ───────────────────────────────────────
        $ingredient_demand = [];

        foreach ($cart_data as $cart_item) {
            $item_id    = (int)$cart_item['id'];
            $item_qty   = (int)($cart_item['quantity'] ?? 1);
            $variant_id = isset($cart_item['variant_id']) && $cart_item['variant_id']
                          ? (int)$cart_item['variant_id'] : null;

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
                throw new Exception(sprintf(
                    'Insufficient stock: "%s" needs %.2f but only %.2f available.',
                    $info['name'], $info['needed'], $info['available']
                ));
            }
        }

        // ── Build order details & compute totals ──────────────────────────────
        $subtotal      = 0;
        $order_details = [];
        $addons_map    = [];
        foreach ($cart_data as $cart_item) {
            $item_id = (int)$cart_item['id'];
            if (!isset($addons_map[$item_id])) {
                $addons_map[$item_id] = getAddonsForMenuItem($pdo, $item_id);
            }
        }

        foreach ($cart_data as $cart_item) {
            $item_id    = (int)$cart_item['id'];
            $item_qty   = (int)($cart_item['quantity'] ?? 1);
            $variant_id = isset($cart_item['variant_id']) && $cart_item['variant_id']
                          ? (int)$cart_item['variant_id'] : null;
            $item_note  = trim((string)($cart_item['note'] ?? '')) ?: null;
            if ($item_qty <= 0) continue;

            $item = getMenuItemById($pdo, $item_id);
            if (!$item) continue;

            $variant_name_snapshot = null;
            if ($variant_id !== null) {
                $variant               = getVariantById($pdo, $variant_id);
                $unit_price            = $variant ? (float)$variant['price'] : (float)$item['base_price'];
                $variant_name_snapshot = $variant['variant_name'] ?? null;
            } else {
                $unit_price = (float)$item['base_price'];
            }

            $line_total      = $unit_price * $item_qty;
            $addons_for_item = [];

            foreach ($cart_item['addons'] ?? [] as $ad) {
                $addon_id   = (int)$ad['id'];
                $addon_qty  = (int)($ad['quantity'] ?? 1);
                $addon_data = getAddonById($pdo, $addon_id);
                if (!$addon_data) continue;
                if (!in_array($addon_id, array_column($addons_map[$item_id] ?? [], 'addon_id'))) continue;
                $addon_line        = $addon_data['price'] * $addon_qty;
                $addons_for_item[] = [
                    'addon_id'    => $addon_id,
                    'quantity'    => $addon_qty,
                    'price'       => $addon_data['price'],
                    'total_price' => $addon_line,
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

        // ── Discount resolution ───────────────────────────────────────────────
        $discount_type   = trim($_POST['discount_type'] ?? 'none');
        $discount_ref    = trim($_POST['discount_ref']  ?? '');
        $discount_pct    = 0.0;
        $discount_amount = 0.0;

        if (!in_array($discount_type, ['senior', 'pwd', 'promo', 'none'])) {
            $discount_type = 'none';
        }

        if (in_array($discount_type, ['senior', 'pwd'], true) && $discount_ref === '') {
            throw new Exception('Please enter the Senior/PWD ID number before applying the discount.');
        }

        if ($discount_type === 'senior') {
            $discount_pct = VatHelper::getSeniorDiscountPct($pdo);
        } elseif ($discount_type === 'pwd') {
            $discount_pct = VatHelper::getPwdDiscountPct($pdo);
        } elseif ($discount_type === 'promo') {
            $discount_pct = (float)($_POST['discount_pct'] ?? 0);
            if ($discount_pct <= 0 || $discount_pct > 100) {
                throw new Exception('Invalid promo percentage.');
            }
        }

        // Re-validate discount server-side — never trust client-computed amounts
        if ($discount_type !== 'none') {
            $disc_result     = VatHelper::applyDiscount($discount_type, $subtotal, $discount_pct, $pdo);
            $discount_amount = $disc_result['discount_amount'];
            $total_amount    = $disc_result['total_after_discount'];
        } else {
            $discount_amount = 0.0;
            $total_amount    = $subtotal;
        }

        $discount = $discount_amount;

        if ($amount_paid < $total_amount) {
            throw new Exception(sprintf(
                'Payment (₱%.2f) is less than the order total (₱%.2f).',
                $amount_paid, $total_amount
            ));
        }

        // ── Build VAT summary before transaction ──────────────────────────────
        // Computed here so it never runs after commit where a throw would
        // attempt to roll back an already-committed transaction.
        $is_vat   = VatHelper::isVatRegistered($pdo);
        $vat_rate = VatHelper::getVatRate($pdo);
        $vat_bd   = VatHelper::orderBreakdown($total_amount, $pdo, $discount_type);

        // ── Persist — single transaction ──────────────────────────────────────
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
            $table_id,
            $discount_type,
            $discount_amount
        );

        // Fetch generated order number
        $stmt = $pdo->prepare("SELECT order_number FROM orders WHERE order_id = ?");
        $stmt->execute([$order_id]);
        $order_number = $stmt->fetchColumn();

        // assignOrNumberAndVat() uses FOR UPDATE internally — must be inside transaction
        $or_number = assignOrNumberAndVat($pdo, $order_id, $total_amount, $discount_type);

        recordOrderDiscount(
            $pdo,
            $order_id,
            $discount_type,
            $discount_ref,
            $discount_pct,
            $discount_amount,
            $_SESSION['user_id']
        );

        foreach ($order_details as $detail) {
            $order_item_id = createOrderItem(
                $pdo,
                $order_id,
                $detail['item_id'],
                $detail['item_name'],
                $detail['quantity'],
                $detail['unit_price'],
                $detail['item_note'],
                $detail['variant_id'],
                $detail['variant_name']
            );
            foreach ($detail['addons'] as $ad) {
                createOrderItemAddon($pdo, $order_item_id, $ad['addon_id'], $ad['quantity'], $ad['price']);
            }
        }

        // ── Record payment ────────────────────────────────────────────────────
        $stmt = $pdo->prepare("
            INSERT INTO payments
                (order_id, or_number, payment_method, amount_paid, change_amount,
                 payment_status, cashier_id, processed_by)
            VALUES (?, ?, 'cash', ?, ?, 'paid', ?, ?)
        ");
        $stmt->execute([
            $order_id,
            $or_number,
            $amount_paid,
            $change_amount,
            $_SESSION['user_id'],
            $_SESSION['user_id'],
        ]);

        $pdo->commit();

        // ── Clear draft after commit — not inside transaction ─────────────────
        // Draft cleanup is non-critical. If it fails the order is already saved.
        try {
            clearDraftOrder($pdo, $_SESSION['user_id']);
        } catch (Throwable $e) {
            error_log('[pos_controller] clearDraftOrder failed for user ' . $_SESSION['user_id'] . ': ' . $e->getMessage());
        }

        echo json_encode([
            'success'         => true,
            'order_id'        => $order_id,
            'order_number'    => $order_number,
            'or_number'       => $or_number,
            'table_name'      => $table_name,
            'total'           => $total_amount,
            'amount_paid'     => $amount_paid,
            'change_amount'   => $change_amount,
            'discount_type'   => $discount_type,
            'discount_amount' => $discount_amount,
            'created_at'      => date('Y-m-d H:i:s'),
            'vat' => [
                'is_registered' => $is_vat,
                'rate'          => $vat_rate,
                'vatable_sales' => $vat_bd['vatable_sales'],
                'vat_amount'    => $vat_bd['vat_amount'],
                'vat_exempt'    => $vat_bd['vat_exempt_amount'],
                'zero_rated'    => $vat_bd['zero_rated_amount'],
            ],
        ]);

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
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

$variants_map = getAllVariantsGrouped($pdo);

// ── Stock computation ─────────────────────────────────────────────────────────
$stock_data = [];
foreach ($menu_items as $item) {
    $id            = $item['item_id'];
    $item_variants = $variants_map[$id] ?? [];

    if (!empty($item_variants)) {
        $stock_data[$id]['base'] = [
            'has_stock'    => false,
            'max_quantity' => 0,
            'ingredients'  => [],
            'no_recipe'    => true,
        ];
        foreach ($item_variants as $variant) {
            $v_id = (int)$variant['variant_id'];
            $stock_data[$id]['variants'][$v_id] = buildStockDataForVariant($pdo, $id, $v_id);
        }
    } else {
        $stock_data[$id]['base']     = buildStockDataForVariant($pdo, $id, null);
        $stock_data[$id]['variants'] = [];
    }
}

foreach ($menu_items as &$item) {
    $id            = $item['item_id'];
    $item_variants = $variants_map[$id] ?? [];

    if (!empty($item_variants)) {
        $item['has_stock'] = collect_any_variant_has_stock($stock_data[$id]['variants'] ?? []);
    } else {
        $basePrice = (float)($item['base_price'] ?? 0);
        if ($basePrice <= 0) {
            $item['has_stock'] = false;
            $item['no_price']  = true;
        } else {
            $item['has_stock'] = $stock_data[$id]['base']['has_stock'] ?? false;
            $item['no_price']  = false;
        }
    }
}
unset($item);

$addon_ids = [];
foreach ($addons_map as $item_addons) {
    foreach ($item_addons as $addon) { $addon_ids[] = $addon['addon_id']; }
}
$addon_ids        = array_unique($addon_ids);
$addon_stock_data = getAddonsStockInfo($pdo, $addon_ids);

$available_tables = getAvailableTables($pdo);

$draft_data = loadDraftCart($pdo, $_SESSION['user_id']);
$draft_cart = $draft_data['cart'];
$draft_meta = $draft_data['meta'];

// ── Legacy form-submit fallback ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_order'])) {
    $_SESSION['error'] = 'Please use the payment flow to place orders.';
    header("Location: index.php?page=manage_pos");
    exit;
}

require_once __DIR__ . '/../views/cashier/manage_pos.php';