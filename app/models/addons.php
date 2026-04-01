<?php
require_once __DIR__ . '/../../config/db.php';

function addAddon(PDO $pdo, string $addon_name, float $price, int $ingredient_id, float $quantity_used, bool $is_active = true): bool
{
    $sql = "INSERT INTO addons (addon_name, price, ingredient_id, quantity_used, is_active)
            VALUES (:addon_name, :price, :ingredient_id, :quantity_used, :is_active)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        ':addon_name' => $addon_name,
        ':price' => $price,
        ':ingredient_id' => $ingredient_id,
        ':quantity_used' => $quantity_used,
        ':is_active' => $is_active ? 1 : 0
    ]);
}

/**
 * Update an existing add-on
 */
function updateAddon(PDO $pdo, int $addon_id, string $addon_name, float $price, int $ingredient_id, float $quantity_used, bool $is_active): bool
{
    $sql = "UPDATE addons SET addon_name = :addon_name, price = :price, ingredient_id = :ingredient_id,
            quantity_used = :quantity_used, is_active = :is_active WHERE addon_id = :addon_id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        ':addon_name' => $addon_name,
        ':price' => $price,
        ':ingredient_id' => $ingredient_id,
        ':quantity_used' => $quantity_used,
        ':is_active' => $is_active ? 1 : 0,
        ':addon_id' => $addon_id
    ]);
}

/**
 * Delete an add-on
 */
function deleteAddon(PDO $pdo, int $addon_id): bool
{
    $checkStmt = $pdo->prepare("
        SELECT COUNT(*) FROM order_item_addons WHERE addon_id = :addon_id
    ");
    $checkStmt->execute([':addon_id' => $addon_id]);
    $usageCount = (int) $checkStmt->fetchColumn();
 
    if ($usageCount > 0) {
        // Has order history — soft delete only
        $stmt = $pdo->prepare("
            UPDATE addons SET is_active = 0 WHERE addon_id = :addon_id
        ");
        return $stmt->execute([':addon_id' => $addon_id]);
    }
 
    // Never used in any order — safe to hard delete
    $stmt = $pdo->prepare("DELETE FROM addons WHERE addon_id = :addon_id");
    return $stmt->execute([':addon_id' => $addon_id]);
}