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
    $sql = "DELETE FROM addons WHERE addon_id = :addon_id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([':addon_id' => $addon_id]);
}
