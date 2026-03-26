<?php
// app/models/variants.php

function getVariantsByItemId(PDO $pdo, int $item_id): array
{
    $stmt = $pdo->prepare("
        SELECT * FROM menu_variants
        WHERE item_id = ? AND is_active = 1
        ORDER BY price ASC
    ");
    $stmt->execute([$item_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getVariantById(PDO $pdo, int $variant_id): ?array
{
    $stmt = $pdo->prepare("SELECT * FROM menu_variants WHERE variant_id = ?");
    $stmt->execute([$variant_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Returns all active variants grouped by item_id.
 * Shape: [ item_id => [ variant_row, ... ], ... ]
 */
function getAllVariantsGrouped(PDO $pdo): array
{
    $stmt = $pdo->query("
        SELECT * FROM menu_variants
        WHERE is_active = 1
        ORDER BY item_id, price ASC
    ");
    $rows    = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $grouped = [];
    foreach ($rows as $row) {
        $grouped[(int)$row['item_id']][] = $row;
    }
    return $grouped;
}

function addVariant(PDO $pdo, int $item_id, string $name, float $price, float $cost_price, int $is_active = 1): ?int
{
    try {
        $stmt = $pdo->prepare("
            INSERT INTO menu_variants (item_id, variant_name, price, cost_price, is_active, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([$item_id, $name, $price, $cost_price, $is_active]);
        return (int) $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("Error adding variant: " . $e->getMessage());
        return null;
    }
}

function updateVariant(PDO $pdo, int $variant_id, string $name, float $price, float $cost_price, bool $is_active): bool
{
    try {
        $stmt = $pdo->prepare("
            UPDATE menu_variants
            SET variant_name = ?, price = ?, cost_price = ?, is_active = ?, updated_at = NOW()
            WHERE variant_id = ?
        ");
        return $stmt->execute([$name, $price, $cost_price, $is_active ? 1 : 0, $variant_id]);
    } catch (PDOException $e) {
        error_log("Error updating variant: " . $e->getMessage());
        return false;
    }
}

function deactivateVariant(PDO $pdo, int $variant_id): bool
{
    return $pdo->prepare("UPDATE menu_variants SET is_active = 0 WHERE variant_id = ?")
               ->execute([$variant_id]);
}

function deleteVariant(PDO $pdo, int $variant_id): bool
{
    return $pdo->prepare("DELETE FROM menu_variants WHERE variant_id = ?")
               ->execute([$variant_id]);
}

/**
 * Pricing Rule Enforcement:
 * When an item has variants, its base_price and cost_price in menu_items
 * must be NULL. Call this whenever a variant is added to an item.
 */
function nullifyItemBasePricing(PDO $pdo, int $item_id): bool
{
    try {
        $stmt = $pdo->prepare("
            UPDATE menu_items SET base_price = NULL, cost_price = NULL WHERE item_id = ?
        ");
        return $stmt->execute([$item_id]);
    } catch (PDOException $e) {
        error_log("Error nullifying base pricing for item {$item_id}: " . $e->getMessage());
        return false;
    }
}

function variantNameExists(PDO $pdo, int $item_id, string $name, ?int $exclude_id = null): bool
{
    $name = trim($name);
    $sql  = "
        SELECT COUNT(*) FROM menu_variants
        WHERE item_id = ? AND TRIM(LOWER(variant_name)) = TRIM(LOWER(?))
    ";
    $params = [$item_id, $name];

    if ($exclude_id !== null) {
        $sql   .= " AND variant_id != ?";
        $params[] = $exclude_id;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn() > 0;
}

function getAllVariantsForItem($pdo, $item_id)
{
    try {
        $stmt = $pdo->prepare("
            SELECT
                v.variant_id,
                v.item_id,
                v.variant_name,
                v.price,
                v.cost_price,
                v.is_active,
                v.created_at,
                v.updated_at,
                (v.price - v.cost_price) as profit,
                CASE
                    WHEN v.price > 0 THEN ((v.price - v.cost_price) / v.price) * 100
                    ELSE 0
                END as margin_percentage
            FROM menu_variants v
            WHERE v.item_id = ?
            ORDER BY v.is_active DESC, v.price ASC, v.variant_name ASC
        ");
        $stmt->execute([$item_id]);
        $variants = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return is_array($variants) ? $variants : [];
    } catch (PDOException $e) {
        error_log("Error in getAllVariantsForItem: " . $e->getMessage());
        return [];
    }
}

function getVariantStatsForDashboard($pdo)
{
    try {
        $stmt = $pdo->query("
            SELECT
                COUNT(*) as total_variants,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_variants,
                AVG(price) as avg_price,
                AVG(cost_price) as avg_cost
            FROM menu_variants
        ");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error in getVariantStatsForDashboard: " . $e->getMessage());
        return ['total_variants' => 0, 'active_variants' => 0, 'avg_price' => 0, 'avg_cost' => 0];
    }
}

function buildStockDataForVariant(PDO $pdo, int $item_id, ?int $variant_id): array
{
    $ingredients = getMenuItemIngredientStockForVariant($pdo, $item_id, $variant_id);
    $max_qty     = PHP_INT_MAX;
    foreach ($ingredients as $ing) {
        if ($ing['quantity_required'] <= 0) continue;
        $possible = (int) floor($ing['stock_quantity'] / $ing['quantity_required']);
        if ($possible < $max_qty) $max_qty = $possible;
    }
    $max_qty = ($max_qty === PHP_INT_MAX || $max_qty < 0) ? 0 : $max_qty;
    return [
        'has_stock'    => $max_qty > 0,
        'max_quantity' => $max_qty,
        'ingredients'  => $ingredients,
    ];
}
function collect_any_variant_has_stock(array $variants): bool {
    foreach ($variants as $v) {
        if ($v['has_stock'] ?? false) return true;
    }
    return false;
}