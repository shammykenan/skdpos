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