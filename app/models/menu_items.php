<?php
require_once __DIR__. '/../../config/db.php';

function addMenuItem(PDO $pdo, string $item_name, string $description, float $price, float $cost_price, int $category_id, ?string $image_url = null, int $is_available = 1)
{   
    $sql = "INSERT INTO menu_items (item_name, description, price, cost_price, category_id, image_url, is_available) 
    VALUES (:item_name, :description, :price, :cost_price, :category_id, :image_url, :is_available)";

    $stmt = $pdo->prepare($sql);

    return $stmt->execute([
        ':item_name'   => $item_name,
        ':description' => $description,
        ':price'       => $price,
        ':cost_price'  => $cost_price,
        ':category_id' => $category_id,
        ':image_url'   => $image_url,
        ':is_available'=> $is_available
    ]);
}
function updateMenuItem(PDO $pdo, int $item_id, string $item_name, string $description, float $price, float $cost_price, int $category_id, ?string $image_url = null, int $is_available = 1)
{
    $sql = "UPDATE menu_items SET item_name = :item_name, description = :description, price = :price, cost_price = :cost_price, category_id = :category_id, image_url = :image_url, is_available = :is_available WHERE item_id = :item_id";

    $stmt = $pdo->prepare($sql);

    return $stmt->execute([
        ':item_id'     => $item_id,
        ':item_name'   => $item_name,
        ':description' => $description,
        ':price'       => $price,
        ':cost_price'  => $cost_price,
        ':category_id' => $category_id,
        ':image_url'   => $image_url,
        ':is_available'=> $is_available
    ]);
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