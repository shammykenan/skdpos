<?php
require_once __DIR__. '/../../config/db.php';

function addMenuItem($pdo, $item_name, $description, $base_price, $cost_price, $category_id, $image_url = null) {
    $stmt = $pdo->prepare("
        INSERT INTO menu_items (item_name, description, base_price, cost_price, category_id, image_url, is_available)
        VALUES (?, ?, ?, ?, ?, ?, 1)
    ");
    return $stmt->execute([$item_name, $description, $base_price, $cost_price, $category_id, $image_url]);
}

function updateMenuItem($pdo, $item_id, $item_name, $description, $base_price, $cost_price, $category_id, $image_url, $is_available) {
    $stmt = $pdo->prepare("
        UPDATE menu_items 
        SET item_name = ?, description = ?, base_price = ?, cost_price = ?, category_id = ?, image_url = ?, is_available = ?
        WHERE item_id = ?
    ");
    return $stmt->execute([$item_name, $description, $base_price, $cost_price, $category_id, $image_url, $is_available, $item_id]);
}

function deleteMenuItem(PDO $pdo, int $item_id): bool {
    // 1. Check if menu item exists in any order
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE item_id = :item_id");
    $stmt->execute([':item_id' => $item_id]);
    $inOrders = $stmt->fetchColumn();

    if ($inOrders > 0) {
        // Cannot delete
        return false;
    }

    // 2. Safe to delete; recipes will be deleted automatically (ON DELETE CASCADE)
    $stmt = $pdo->prepare("DELETE FROM menu_items WHERE item_id = :item_id");
    return $stmt->execute([':item_id' => $item_id]);
}
/**
 * Get all menu items (simple version without ingredients)
 */
function getAllMenuItems(PDO $pdo): array {
    try {
        $sql = "
            SELECT mi.*, c.category_name 
            FROM menu_items mi
            LEFT JOIN categories c ON mi.category_id = c.category_id
            ORDER BY c.category_name, mi.item_name
        ";
        
        $stmt = $pdo->query($sql);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return is_array($result) ? $result : [];
        
    } catch (PDOException $e) {
        error_log("Error in getAllMenuItems: " . $e->getMessage());
        return [];
    }
}

/**
 * Get menu items by category
 */
function getMenuItemsByCategory(PDO $pdo, int $category_id): array {
    try {
        $sql = "
            SELECT mi.*, c.category_name 
            FROM menu_items mi
            LEFT JOIN categories c ON mi.category_id = c.category_id
            WHERE mi.category_id = :category_id
            ORDER BY mi.item_name
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':category_id' => $category_id]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return is_array($result) ? $result : [];
        
    } catch (PDOException $e) {
        error_log("Error in getMenuItemsByCategory: " . $e->getMessage());
        return [];
    }
}

/**
 * Toggle menu item availability
 */
function toggleMenuItemAvailability(PDO $pdo, int $item_id, int $is_available): bool {
    try {
        $sql = "UPDATE menu_items SET is_available = :is_available WHERE item_id = :item_id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':item_id' => $item_id,
            ':is_available' => $is_available
        ]);
    } catch (PDOException $e) {
        error_log("Error in toggleMenuItemAvailability: " . $e->getMessage());
        return false;
    }
}

/**
 * Search menu items by name or description
 */
function searchMenuItems(PDO $pdo, string $search_term): array {
    try {
        $sql = "
            SELECT mi.*, c.category_name 
            FROM menu_items mi
            LEFT JOIN categories c ON mi.category_id = c.category_id
            WHERE mi.item_name LIKE :search 
               OR mi.description LIKE :search
            ORDER BY mi.item_name
        ";
        
        $stmt = $pdo->prepare($sql);
        $search_pattern = '%' . $search_term . '%';
        $stmt->execute([':search' => $search_pattern]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return is_array($result) ? $result : [];
        
    } catch (PDOException $e) {
        error_log("Error in searchMenuItems: " . $e->getMessage());
        return [];
    }
}

/**
 * Get menu items that are running low on stock (based on recipe requirements)
 */
function getLowStockMenuItems(PDO $pdo, int $threshold = 5): array {
    try {
        $sql = "
            SELECT DISTINCT 
                mi.item_id,
                mi.item_name,
                mi.base_price,
                mi.is_available
            FROM menu_items mi
            JOIN recipes r ON mi.item_id = r.item_id AND r.variant_id IS NULL
            JOIN ingredients i ON r.ingredient_id = i.ingredient_id
            WHERE i.stock_quantity <= :threshold
            ORDER BY mi.item_name
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':threshold' => $threshold]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return is_array($result) ? $result : [];
        
    } catch (PDOException $e) {
        error_log("Error in getLowStockMenuItems: " . $e->getMessage());
        return [];
    }
}

/**
 * Get menu item count by category
 */
function getMenuItemCountByCategory(PDO $pdo): array {
    try {
        $sql = "
            SELECT 
                c.category_id,
                c.category_name,
                COUNT(mi.item_id) as item_count
            FROM categories c
            LEFT JOIN menu_items mi ON c.category_id = mi.category_id
            GROUP BY c.category_id
            ORDER BY c.category_name
        ";
        
        $stmt = $pdo->query($sql);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return is_array($result) ? $result : [];
        
    } catch (PDOException $e) {
        error_log("Error in getMenuItemCountByCategory: " . $e->getMessage());
        return [];
    }
}

/**
 * Check if a menu item exists
 */
function menuItemExists(PDO $pdo, int $item_id): bool {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM menu_items WHERE item_id = :item_id");
        $stmt->execute([':item_id' => $item_id]);
        return (int)$stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log("Error in menuItemExists: " . $e->getMessage());
        return false;
    }
}

/**
 * Get total profit from all menu items
 */
function getTotalMenuProfit(PDO $pdo): float {
    try {
        $stmt = $pdo->query("SELECT SUM(base_price - cost_price) as total_profit FROM menu_items");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float)($result['total_profit'] ?? 0);
    } catch (PDOException $e) {
        error_log("Error in getTotalMenuProfit: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get average profit margin
 */
function getAverageProfitMargin(PDO $pdo): float {
    try {
        $stmt = $pdo->query("
            SELECT AVG(((base_price - cost_price) / base_price) * 100) as avg_margin 
            FROM menu_items 
            WHERE base_price > 0
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float)($result['avg_margin'] ?? 0);
    } catch (PDOException $e) {
        error_log("Error in getAverageProfitMargin: " . $e->getMessage());
        return 0;
    }
}