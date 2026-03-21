<?php
require_once __DIR__. '/../../config/db.php';

function getItemSalesStatistics($pdo, $item_id) {
    $sql = "SELECT 
                COALESCE(SUM(oi.quantity), 0) as total_sold,
                COALESCE(SUM(oi.subtotal), 0) as total_revenue
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.order_id
            WHERE oi.item_id = :item_id
            AND o.status = 'completed'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':item_id' => $item_id]);
    return $stmt->fetch();
}

function getDashboardStats($pdo) {
    $stats = [];

    // Total Revenue (Today)
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(total_amount), 0) as total
        FROM orders
        WHERE DATE(created_at) = CURDATE() AND status = 'completed'
    ");
    $stmt->execute();
    $stats['today_revenue'] = $stmt->fetch()['total'];

    // Total Orders (Today)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total FROM orders WHERE DATE(created_at) = CURDATE()
    ");
    $stmt->execute();
    $stats['today_orders'] = $stmt->fetch()['total'];

    // Total Customers (Today)
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT customer_name) as total
        FROM orders
        WHERE DATE(created_at) = CURDATE() AND customer_name IS NOT NULL
    ");
    $stmt->execute();
    $stats['today_customers'] = $stmt->fetch()['total'];

    // Active Orders — pending + preparing + ready
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total FROM orders WHERE status IN ('pending','preparing','ready')
    ");
    $stmt->execute();
    $stats['pending_orders'] = $stmt->fetch()['total'];

    // Individual active order counts for notification breakdown
    $stmt = $pdo->prepare("
        SELECT
            SUM(status = 'pending')   AS pending_count,
            SUM(status = 'preparing') AS preparing_count,
            SUM(status = 'ready')     AS ready_count
        FROM orders
        WHERE status IN ('pending','preparing','ready')
    ");
    $stmt->execute();
    $activeCounts = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['pending_count']   = (int) ($activeCounts['pending_count']   ?? 0);
    $stats['preparing_count'] = (int) ($activeCounts['preparing_count'] ?? 0);
    $stats['ready_count']     = (int) ($activeCounts['ready_count']     ?? 0);

    // Total Menu Items
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM menu_items");
    $stmt->execute();
    $stats['total_items'] = $stmt->fetch()['total'];

    // Active Categories
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM categories");
    $stmt->execute();
    $stats['total_categories'] = $stmt->fetch()['total'];

    // Low Stock Items
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total FROM ingredients WHERE stock_quantity <= low_stock_alert
    ");
    $stmt->execute();
    $stats['low_stock'] = $stmt->fetch()['total'];

    // Monthly Revenue
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(total_amount), 0) as total
        FROM orders
        WHERE MONTH(created_at) = MONTH(CURDATE())
          AND YEAR(created_at)  = YEAR(CURDATE())
          AND status = 'completed'
    ");
    $stmt->execute();
    $stats['monthly_revenue'] = $stmt->fetch()['total'];

    return $stats;
}

function getRecentOrders($pdo, $limit = 10) {
    $sql = "SELECT o.order_id, o.order_number, o.customer_name,
                   o.total_amount, o.status, o.created_at,
                   u.full_name as cashier_name
            FROM orders o
            LEFT JOIN users u ON o.cashier_id = u.user_id
            ORDER BY o.created_at DESC
            LIMIT :limit";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getTopSellingItems($pdo, $limit = 5) {
    $sql = "SELECT mi.item_name, mi.price,
                   SUM(oi.quantity) as total_sold,
                   SUM(oi.subtotal) as total_revenue
            FROM order_items oi
            JOIN menu_items mi ON oi.item_id = mi.item_id
            JOIN orders o      ON oi.order_id = o.order_id
            WHERE o.status = 'completed'
            GROUP BY mi.item_id, mi.item_name, mi.price
            ORDER BY total_sold DESC
            LIMIT :limit";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getLowStockIngredients($pdo, $limit = 3) {
    $sql = "SELECT ingredient_name, stock_quantity, unit, low_stock_alert
            FROM ingredients
            WHERE stock_quantity <= low_stock_alert
            ORDER BY stock_quantity ASC
            LIMIT :limit";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getDailySales($pdo, $days = 7) {
    $sql = "SELECT DATE(created_at) as date,
                   COUNT(*) as orders_count,
                   SUM(total_amount) as total_sales
            FROM orders
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
              AND status = 'completed'
            GROUP BY DATE(created_at)
            ORDER BY date ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':days', $days, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getSalesByCategory($pdo) {
    $sql = "SELECT c.category_name,
                   COUNT(DISTINCT o.order_id) as order_count,
                   SUM(oi.subtotal) as total_sales,
                   COUNT(oi.quantity) as items_sold
            FROM orders o
            JOIN order_items oi ON o.order_id   = oi.order_id
            JOIN menu_items mi  ON oi.item_id   = mi.item_id
            JOIN categories c   ON mi.category_id = c.category_id
            WHERE o.status = 'completed'
            GROUP BY c.category_id, c.category_name
            ORDER BY total_sales DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}