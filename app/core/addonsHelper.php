<?php
require_once __DIR__ . '/../../config/db.php';

function getAllAddons(PDO $pdo): array
{
    $sql = "SELECT a.*, i.ingredient_name, i.unit
            FROM addons a
            JOIN ingredients i ON a.ingredient_id = i.ingredient_id
            ORDER BY a.addon_name";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAddonById(PDO $pdo, int $addon_id): ?array
{
    $sql = "SELECT * FROM addons WHERE addon_id = :addon_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':addon_id' => $addon_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
}
function getAddonsForItemCategory(PDO $pdo, int $category_id) {
    $beverageCategories = [1, 2, 3, 4, 5, 6];
    if (!in_array($category_id, $beverageCategories)) {
        return [];
    }
    // For beverage categories, return all active add-ons
    $sql = "SELECT * FROM addons WHERE is_active = 1 ORDER BY addon_name";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function deductIngredientsForAddon($pdo, $addon_id, $quantity) {
    $sql = "SELECT ingredient_id, quantity_used FROM addons WHERE addon_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$addon_id]);
    $addon = $stmt->fetch();

    if ($addon) {
        $sql_update = "UPDATE ingredients 
                       SET stock_quantity = stock_quantity - ? 
                       WHERE ingredient_id = ?";
        $stmt_up = $pdo->prepare($sql_update);
        $stmt_up->execute([$addon['quantity_used'] * $quantity, $addon['ingredient_id']]);
    }
}
/**
 * Check if an add-on has sufficient ingredient stock
 */
function checkAddonStock(PDO $pdo, int $addon_id, int $quantity = 1) {
    $sql = "SELECT a.*, i.stock_quantity, i.ingredient_name 
            FROM addons a
            JOIN ingredients i ON a.ingredient_id = i.ingredient_id
            WHERE a.addon_id = :addon_id AND a.is_active = 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['addon_id' => $addon_id]);
    $addon = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$addon) {
        return [
            'success' => false,
            'message' => 'Add-on not found',
            'available' => 0,
            'required' => 0
        ];
    }
    
    $required_quantity = $addon['quantity_used'] * $quantity;
    $available = $addon['stock_quantity'];
    
    if ($available < $required_quantity) {
        return [
            'success' => false,
            'message' => "Insufficient {$addon['ingredient_name']} for add-on",
            'available' => $available,
            'required' => $required_quantity,
            'ingredient' => $addon['ingredient_name']
        ];
    }
    
    return [
        'success' => true,
        'message' => 'Stock available',
        'available' => $available,
        'required' => $required_quantity
    ];
}

/**
 * Get stock information for multiple add-ons
 */
function getAddonsStockInfo(PDO $pdo, array $addon_ids) {
    if (empty($addon_ids)) {
        return [];
    }
    
    $placeholders = implode(',', array_fill(0, count($addon_ids), '?'));
    $sql = "SELECT a.*, i.stock_quantity, i.ingredient_name,
            i.stock_quantity / a.quantity_used as max_quantity
            FROM addons a
            JOIN ingredients i ON a.ingredient_id = i.ingredient_id
            WHERE a.addon_id IN ($placeholders) AND a.is_active = 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($addon_ids);
    
    $result = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $result[$row['addon_id']] = [
            'addon_name' => $row['addon_name'],
            'price' => $row['price'],
            'ingredient_name' => $row['ingredient_name'],
            'quantity_used' => $row['quantity_used'],
            'stock_quantity' => $row['stock_quantity'],
            'max_quantity' => floor($row['max_quantity']),
            'has_stock' => $row['stock_quantity'] >= $row['quantity_used']
        ];
    }
    
    return $result;
}