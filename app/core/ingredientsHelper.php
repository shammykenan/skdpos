<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../models/ingredients.php';
// Fetch all ingredients
function getAllIngredients(PDO $pdo): array {
    $stmt = $pdo->query("SELECT * FROM ingredients ORDER BY ingredient_name ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch single ingredient by ID
function getIngredientById(PDO $pdo, int $ingredient_id): ?array {
    $stmt = $pdo->prepare("SELECT * FROM ingredients WHERE ingredient_id = ingredient_idd");
    $stmt->execute([':ingredient_id' => $ingredient_id]);
    $ingredient = $stmt->fetch(PDO::FETCH_ASSOC);
    return $ingredient ?: null;
}
function getIngredientsForBeverages(PDO $pdo) {
    $sql = "SELECT DISTINCT i.* 
            FROM ingredients i
            JOIN recipes r ON i.ingredient_id = r.ingredient_id
            JOIN menu_items m ON r.item_id = m.item_id
            WHERE m.category_id IN (1,2,3,4,5,6)"; 
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

//Check if a menu item has enough stock for a given quantity
function checkMenuItemStock(PDO $pdo, $item_id, $quantity = 1) {
    $sql = "SELECT i.stock_quantity, r.quantity_required
            FROM recipes r
            JOIN ingredients i ON r.ingredient_id = i.ingredient_id
            WHERE r.item_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$item_id]);
    $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($ingredients as $ingredient) {
        $required = $ingredient['quantity_required'] * $quantity;
        if ($ingredient['stock_quantity'] < $required) {
            return false;
        }
    }
    return true;
}
function deductIngredientsForItem($pdo, $item_id, $quantity) {
    // Fetch recipe
    $sql = "SELECT ingredient_id, quantity_required FROM recipes WHERE item_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$item_id]);
    $ingredients = $stmt->fetchAll();

    foreach ($ingredients as $ing) {
        $sql_update = "UPDATE ingredients 
                       SET stock_quantity = stock_quantity - ? 
                       WHERE ingredient_id = ?";
        $stmt_up = $pdo->prepare($sql_update);
        $stmt_up->execute([$ing['quantity_required'] * $quantity, $ing['ingredient_id']]);
    }
}
function restoreIngredientsForOrder(PDO $pdo, int $order_id) {
    // 1. Get all order items for this order
    $stmt = $pdo->prepare("SELECT order_item_id, item_id, quantity FROM order_items WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($order_items as $item) {
        $item_id = $item['item_id'];
        $qty = $item['quantity'];

        // 2. Restore base drink ingredients
        $stmt2 = $pdo->prepare("
            SELECT ingredient_id, quantity_required 
            FROM recipes 
            WHERE item_id = ?
        ");
        $stmt2->execute([$item_id]);
        $ingredients = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        foreach ($ingredients as $ing) {
            $ingredient_id = $ing['ingredient_id'];
            $restore_qty = $ing['quantity_required'] * $qty;

            $update = $pdo->prepare("
                UPDATE ingredients 
                SET stock_quantity = stock_quantity + ? 
                WHERE ingredient_id = ?
            ");
            $update->execute([$restore_qty, $ingredient_id]);
        }

        // 3. Restore add-ons for this order item
        $stmt3 = $pdo->prepare("
            SELECT oia.addon_id, oia.quantity, a.quantity_used
            FROM order_item_addons oia
            JOIN addons a ON oia.addon_id = a.addon_id
            WHERE oia.order_item_id = ?
        ");
        $stmt3->execute([$item['order_item_id']]);
        $addons = $stmt3->fetchAll(PDO::FETCH_ASSOC);

        foreach ($addons as $ad) {
            $restore_qty = $ad['quantity_used'] * $ad['quantity'];
            $update = $pdo->prepare("
                UPDATE ingredients 
                SET stock_quantity = stock_quantity + ? 
                WHERE ingredient_id = (SELECT ingredient_id FROM addons WHERE addon_id = ?)
            ");
            $update->execute([$restore_qty, $ad['addon_id']]);
        }
    }
}
function checkIngredientsStockForItem($pdo, $item_id, $order_qty) {
    $ingredients = getIngredientsForItem($pdo, $item_id);

    foreach ($ingredients as $ing) {
        $required_qty = $ing['quantity_required'] * $order_qty;

        if ($ing['stock_quantity'] < $required_qty) {
            return false;
        }
    }

    return true;
}

function checkIngredientsStockForAddon($pdo, $addon_id, $order_qty) {
    $ingredients = getIngredientsForAddon($pdo, $addon_id);

    foreach ($ingredients as $ing) {
        $required_qty = $ing['quantity_required'] * $order_qty;

        if ($ing['stock_quantity'] < $required_qty) {
            return false;
        }
    }

    return true;
}
function getMenuItemIngredientStock($pdo, $item_id) {
    $sql = "SELECT 
                i.ingredient_id,
                i.ingredient_name, 
                i.stock_quantity, 
                r.quantity_required
            FROM recipes r
            JOIN ingredients i ON r.ingredient_id = i.ingredient_id
            WHERE r.item_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$item_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}