<?php
require_once __DIR__. '/../../config/db.php';

function getProfitSummary($pdo) {
    $sql = "SELECT 
                COALESCE(SUM(oi.subtotal), 0) as total_revenue,
                COALESCE(SUM(mi.cost_price * oi.quantity), 0) as total_cost,
                COALESCE(SUM(oi.subtotal - (mi.cost_price * oi.quantity)), 0) as total_profit
            FROM order_items oi
            JOIN menu_items mi ON oi.item_id = mi.item_id
            JOIN orders o ON oi.order_id = o.order_id
            WHERE o.status = 'completed'
            AND MONTH(o.created_at) = MONTH(CURDATE())
            AND YEAR(o.created_at) = YEAR(CURDATE())";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetch();
}
function getCashierPerformance($pdo) {
    $sql = "SELECT 
                u.full_name,
                COUNT(o.order_id) as total_orders,
                SUM(o.total_amount) as total_sales
            FROM orders o
            JOIN users u ON o.cashier_id = u.user_id
            WHERE o.status = 'completed'
            GROUP BY u.user_id
            ORDER BY total_sales DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}
function getMostProfitableItems($pdo, $limit = 5) {
    $sql = "SELECT 
                mi.item_name,
                SUM(oi.quantity) as total_sold,
                SUM(oi.subtotal - (mi.cost_price * oi.quantity)) as total_profit
            FROM order_items oi
            JOIN menu_items mi ON oi.item_id = mi.item_id
            JOIN orders o ON oi.order_id = o.order_id
            WHERE o.status = 'completed'
            GROUP BY mi.item_id
            ORDER BY total_profit DESC
            LIMIT :limit";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}
function getIngredientConsumption($pdo, $days = 7) {
    $sql = "SELECT 
                i.ingredient_name,
                SUM(r.quantity_required * oi.quantity) as total_used
            FROM order_items oi
            JOIN recipes r ON oi.item_id = r.item_id
            JOIN ingredients i ON r.ingredient_id = i.ingredient_id
            JOIN orders o ON oi.order_id = o.order_id
            WHERE o.status = 'completed'
            AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
            GROUP BY i.ingredient_id
            ORDER BY total_used DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':days', $days, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}
