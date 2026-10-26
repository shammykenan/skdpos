<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../models/ingredients.php';
require_once __DIR__ . '/recipesHelper.php';
// ─────────────────────────────────────────────────────────────────────────────
// NOTE: ingredients no longer has a `unit` column.
// Unit info is in the `units` table, joined via ingredients.unit_id (FK).
// ─────────────────────────────────────────────────────────────────────────────

// ─────────────────────────────────────────────────────────────────────────────
// INGREDIENT FETCHING
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Get a single ingredient by ID, joined with its unit.
 */
function getIngredientById(PDO $pdo, int $ingredient_id): ?array {
    $stmt = $pdo->prepare("
        SELECT
            i.ingredient_id,
            i.ingredient_name,
            i.stock_quantity,
            i.unit_id,
            i.low_stock_alert,
            i.last_updated,
            u.unit_code,
            u.unit_label,
            u.unit_icon
        FROM ingredients i
        JOIN units u ON i.unit_id = u.unit_id
        WHERE i.ingredient_id = :ingredient_id
    ");
    $stmt->execute([':ingredient_id' => $ingredient_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Get all ingredients linked to beverage menu categories, with unit info.
 */
function getIngredientsForBeverages(PDO $pdo): array {
    $stmt = $pdo->query("
        SELECT DISTINCT
            i.ingredient_id,
            i.ingredient_name,
            i.stock_quantity,
            i.unit_id,
            i.low_stock_alert,
            u.unit_code,
            u.unit_label
        FROM ingredients i
        JOIN units       u ON i.unit_id        = u.unit_id
        JOIN recipes     r ON i.ingredient_id  = r.ingredient_id
        JOIN menu_items  m ON r.item_id        = m.item_id
        WHERE m.category_id IN (1,2,3,4,5,6)
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get all ingredients for a specific add-on, with unit info.
 */
function getIngredientsForAddon(PDO $pdo, int $addon_id): array {
    $stmt = $pdo->prepare("
        SELECT DISTINCT
            i.ingredient_id,
            i.ingredient_name,
            i.stock_quantity,
            i.unit_id,
            u.unit_code,
            u.unit_label
        FROM ingredients i
        JOIN units  u ON i.unit_id        = u.unit_id
        JOIN addons a ON i.ingredient_id  = a.ingredient_id
        WHERE a.addon_id = ?
    ");
    $stmt->execute([$addon_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get all ingredients for a specific menu item (recipe), with unit info.
 */
function getIngredientsForItem(PDO $pdo, int $item_id): array {
    $stmt = $pdo->prepare("
        SELECT
            i.ingredient_id,
            i.ingredient_name,
            i.stock_quantity,
            i.unit_id,
            u.unit_code,
            u.unit_label,
            r.quantity_required
        FROM recipes r
        JOIN ingredients i ON r.ingredient_id = i.ingredient_id
        JOIN units       u ON i.unit_id        = u.unit_id
        WHERE r.item_id = ?
    ");
    $stmt->execute([$item_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get ingredient stock details for a menu item (used for stock display in POS).
 */
function getMenuItemIngredientStock(PDO $pdo, int $item_id): array {
    $stmt = $pdo->prepare("
        SELECT
            i.ingredient_id,
            i.ingredient_name,
            i.stock_quantity,
            u.unit_code,
            u.unit_label,
            r.quantity_required
        FROM recipes r
        JOIN ingredients i ON r.ingredient_id = i.ingredient_id
        JOIN units       u ON i.unit_id        = u.unit_id
        WHERE r.item_id = ?
    ");
    $stmt->execute([$item_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ─────────────────────────────────────────────────────────────────────────────
// STOCK CHECKS
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Check if a menu item has enough stock for a given order quantity.
 * Also used as checkIngredientsStockForItem — that alias was removed (duplicate).
 */
function checkMenuItemStock(PDO $pdo, int $item_id, int $quantity = 1): bool {
    $ingredients = getIngredientsForItem($pdo, $item_id);
    foreach ($ingredients as $ingredient) {
        if ($ingredient['stock_quantity'] < ($ingredient['quantity_required'] * $quantity)) {
            return false;
        }
    }
    return true;
}

/**
 * Check if an add-on has enough stock for a given order quantity.
 */
function checkIngredientsStockForAddon(PDO $pdo, int $addon_id, int $order_qty): bool {
    $ingredients = getIngredientsForAddon($pdo, $addon_id);
    foreach ($ingredients as $ing) {
        if ($ing['stock_quantity'] < ($ing['quantity_required'] * $order_qty)) {
            return false;
        }
    }
    return true;
}

// ─────────────────────────────────────────────────────────────────────────────
// STOCK MUTATIONS
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Restore ingredient stock when an order is cancelled or refunded.
 */
// FIX (BUG 7): Uses restoreIngredientsForItem() and restoreIngredientsForAddon()
// with positive quantities. No more negative-qty trick.
function restoreIngredientsForOrder(PDO $pdo, int $order_id): void {
    // ✅ Only restore items that reached 'preparing' or 'ready'
    // (those are the only ones that had stock deducted)
    $stmt = $pdo->prepare("
        SELECT item_id, variant_id, quantity
        FROM order_items
        WHERE order_id = ?
          AND status IN ('preparing', 'ready')
    ");
    $stmt->execute([$order_id]);

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        restoreIngredientsForItem(
            $pdo,
            (int)$row['item_id'],
            (int)$row['quantity'],
            $row['variant_id'] !== null ? (int)$row['variant_id'] : null
        );
    }

    // Only restore addon ingredients for those same items
    $stmt = $pdo->prepare("
        SELECT oia.addon_id, oia.quantity
        FROM order_item_addons oia
        JOIN order_items oi ON oia.order_item_id = oi.order_item_id
        WHERE oi.order_id = ?
          AND oi.status IN ('preparing', 'ready')
    ");
    $stmt->execute([$order_id]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        restoreIngredientsForAddon($pdo, (int)$row['addon_id'], (int)$row['quantity']);
    }
}
/**
 * Ingredient quantities already sold but not yet deducted from stock.
 *
 * Ingredients are only deducted when the kitchen starts preparing a unit, so an
 * order that is paid for but still queued holds no stock yet. Without counting
 * that demand, a checkout stock check reads the raw on-hand figure and happily
 * sells the same last serving twice.
 *
 * Undeducted units per order item follow the same definition the kitchen uses in
 * partial_item_action.php: quantity - started - ready - voided.
 *
 * @return array<int,float> ingredient_id => committed quantity
 */
function getCommittedIngredientDemand(PDO $pdo): array
{
    $committed = [];

    $openOrder = "o.status NOT IN ('draft', 'completed', 'cancelled', 'voided')";
    $openItem  = "oi.status NOT IN ('completed', 'cancelled', 'voided')";
    $pendingQty = "GREATEST(oi.quantity - oi.quantity_started - oi.quantity_ready - oi.quantity_voided, 0)";

    // ── Recipe ingredients ────────────────────────────────────────────────────
    $stmt = $pdo->query("
        SELECT r.ingredient_id,
               SUM(r.quantity_required * $pendingQty) AS committed
        FROM order_items oi
        JOIN orders o ON o.order_id = oi.order_id
        JOIN recipes r
          ON r.item_id = oi.item_id
         AND (r.variant_id = oi.variant_id
              OR (r.variant_id IS NULL AND oi.variant_id IS NULL))
        WHERE $openOrder
          AND $openItem
          AND r.quantity_required > 0
        GROUP BY r.ingredient_id
    ");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $id = (int)$row['ingredient_id'];
        $committed[$id] = ($committed[$id] ?? 0) + (float)$row['committed'];
    }

    // ── Add-on ingredients ────────────────────────────────────────────────────
    // Add-on consumption scales with the number of item units prepared, matching
    // how deductIngredientsForAddon() is called from the kitchen endpoints.
    $stmt = $pdo->query("
        SELECT a.ingredient_id,
               SUM(a.quantity_used * oia.quantity * $pendingQty) AS committed
        FROM order_items oi
        JOIN orders o ON o.order_id = oi.order_id
        JOIN order_item_addons oia ON oia.order_item_id = oi.order_item_id
        JOIN addons a ON a.addon_id = oia.addon_id
        WHERE $openOrder
          AND $openItem
          AND a.quantity_used > 0
        GROUP BY a.ingredient_id
    ");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $id = (int)$row['ingredient_id'];
        $committed[$id] = ($committed[$id] ?? 0) + (float)$row['committed'];
    }

    return $committed;
}

/**
 * Deduct ingredients for one ordered item, variant-aware.
 */
function deductIngredientsForItem(
    PDO  $pdo,
    int  $item_id,
    int  $quantity,
    ?int $variant_id = null
): void {
    $recipe = getEffectiveRecipe($pdo, $item_id, $variant_id);

    if (empty($recipe)) {
        return; // No recipe → nothing to deduct (item has no tracked ingredients)
    }

    // Guarded deduction — the WHERE clause makes the update a no-op when there
    // is not enough stock, so rowCount() tells us whether it actually applied.
    // Without this, stock silently goes negative and corrupts COGS/waste reports.
    $stmt = $pdo->prepare("
        UPDATE ingredients
        SET stock_quantity = stock_quantity - :deduct
        WHERE ingredient_id = :ingredient_id
          AND stock_quantity >= :deduct_min
    ");

    foreach ($recipe as $row) {
        $deduct = (float)$row['quantity_required'] * $quantity;
        if ($deduct <= 0) {
            continue;
        }
        $stmt->execute([
            ':deduct'        => $deduct,
            ':deduct_min'    => $deduct,
            ':ingredient_id' => $row['ingredient_id'],
        ]);

        if ($stmt->rowCount() === 0) {
            throw new RuntimeException(sprintf(
                'Insufficient stock for "%s": needs %.2f but not enough remains.',
                $row['ingredient_name'] ?? ('ingredient #' . $row['ingredient_id']),
                $deduct
            ));
        }
    }
}

/**
 * Restore ingredients for one item when an order is cancelled.
 * FIX (BUG 7): Uses a dedicated restore function with positive quantity,
 * instead of passing negative qty to the deduct function.
 */
function restoreIngredientsForItem(
    PDO  $pdo,
    int  $item_id,
    int  $quantity,
    ?int $variant_id = null
): void {
    $recipe = getEffectiveRecipe($pdo, $item_id, $variant_id);
    foreach ($recipe as $row) {
        $pdo->prepare("
            UPDATE ingredients
            SET stock_quantity = stock_quantity + ?
            WHERE ingredient_id = ?
        ")->execute([$row['quantity_required'] * $quantity, $row['ingredient_id']]);
    }
}

/**
 * Restore addon ingredients when an order is cancelled.
 * FIX (BUG 7): Dedicated restore function — no negative qty tricks.
 */
function restoreIngredientsForAddon(PDO $pdo, int $addon_id, int $quantity): void
{
    $stmt = $pdo->prepare("
        SELECT ingredient_id, quantity_used
        FROM addons
        WHERE addon_id = ?
    ");
    $stmt->execute([$addon_id]);
    $addon = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$addon) return;

    $pdo->prepare("
        UPDATE ingredients
        SET stock_quantity = stock_quantity + ?
        WHERE ingredient_id = ?
    ")->execute([$addon['quantity_used'] * $quantity, $addon['ingredient_id']]);
}

/**
 * Get stock info for one item+variant. Used by POS stock calculations.
 */
function getMenuItemIngredientStockForVariant(
    PDO  $pdo,
    int  $item_id,
    ?int $variant_id
): array {
    // Try variant-specific recipe first
    if ($variant_id !== null) {
        $stmt = $pdo->prepare("
            SELECT
                r.ingredient_id,
                i.ingredient_name,
                r.quantity_required,
                i.stock_quantity
            FROM recipes r
            JOIN ingredients i ON i.ingredient_id = r.ingredient_id
            WHERE r.item_id    = :item_id
              AND r.variant_id = :variant_id
        ");
        $stmt->execute([
            ':item_id'    => $item_id,
            ':variant_id' => $variant_id,
        ]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($rows)) {
            return $rows;
        }
    }

    // Fall back to base recipe
    $stmt = $pdo->prepare("
        SELECT
            r.ingredient_id,
            i.ingredient_name,
            r.quantity_required,
            i.stock_quantity
        FROM recipes r
        JOIN ingredients i ON i.ingredient_id = r.ingredient_id
        WHERE r.item_id    = :item_id
          AND r.variant_id IS NULL
    ");
    $stmt->execute([':item_id' => $item_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}