<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../core/authHelper.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Access denied.']);
    exit;
}

try {
    // ── Debug: Check today's orders ───────────────────────────────────
    $debugStmt = $pdo->prepare("
        SELECT order_id, order_number, total_amount, status, created_at
        FROM orders
        WHERE DATE(created_at) = CURDATE()
        ORDER BY created_at DESC
        LIMIT 5
    ");
    $debugStmt->execute();
    $todayOrders = $debugStmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("DEBUG: Today's orders: " . json_encode($todayOrders));

    // ── Active order counts ───────────────────────────────────────────
    $stmt = $pdo->prepare("
        SELECT
            SUM(status = 'pending')   AS pending_count,
            SUM(status = 'preparing') AS preparing_count,
            SUM(status = 'ready')     AS ready_count,
            COUNT(*)                  AS total_active
        FROM orders
        WHERE status IN ('pending','preparing','ready')
    ");
    $stmt->execute();
    $active = $stmt->fetch(PDO::FETCH_ASSOC);

    // ── Today's revenue ───────────────────────────────────────────────
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(total_amount), 0) AS total
        FROM orders
        WHERE DATE(created_at) = CURDATE() AND status = 'completed'
    ");
    $stmt->execute();
    $todayRevenue = $stmt->fetchColumn();

    // ── Monthly revenue ───────────────────────────────────────────────
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(total_amount), 0) AS total
        FROM orders
        WHERE MONTH(created_at) = MONTH(CURDATE())
          AND YEAR(created_at)  = YEAR(CURDATE())
          AND status = 'completed'
    ");
    $stmt->execute();
    $monthlyRevenue = $stmt->fetchColumn();

    // ── Today's orders + customers ────────────────────────────────────
    $stmt = $pdo->prepare("
        SELECT
            COUNT(*)                      AS today_orders,
            COUNT(DISTINCT customer_name) AS today_customers
        FROM orders
        WHERE DATE(created_at) = CURDATE()
    ");
    $stmt->execute();
    $todayStats = $stmt->fetch(PDO::FETCH_ASSOC);

    // ── Low stock count ───────────────────────────────────────────────
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS low_stock
        FROM ingredients
        WHERE stock_quantity <= low_stock_alert
    ");
    $stmt->execute();
    $lowStockCount = (int) $stmt->fetchColumn();

    // ── Low stock items — try units JOIN, fall back to bare unit col ──
    try {
        $stmt = $pdo->prepare("
            SELECT i.ingredient_name,
                   i.stock_quantity,
                   i.low_stock_alert,
                   COALESCE(u.unit_code, '') AS unit
            FROM ingredients i
            LEFT JOIN units u ON i.unit_id = u.unit_id
            WHERE i.stock_quantity <= i.low_stock_alert
            ORDER BY i.stock_quantity ASC
            LIMIT 3
        ");
        $stmt->execute();
        $lowStockItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $stmt = $pdo->prepare("
            SELECT ingredient_name,
                   stock_quantity,
                   low_stock_alert,
                   COALESCE(unit, '') AS unit
            FROM ingredients
            WHERE stock_quantity <= low_stock_alert
            ORDER BY stock_quantity ASC
            LIMIT 3
        ");
        $stmt->execute();
        $lowStockItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Recent orders ─────────────────────────────────────────────────
    $stmt = $pdo->prepare("
        SELECT o.order_id,
               o.order_number,
               o.customer_name,
               o.total_amount,
               o.status,
               o.created_at,
               COALESCE(u.full_name, 'N/A') AS cashier_name
        FROM orders o
        LEFT JOIN users u ON o.cashier_id = u.user_id
        ORDER BY o.created_at DESC
        LIMIT 10
    ");
    $stmt->execute();
    $recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ── Top selling items ─────────────────────────────────────────────
    // LEFT JOIN variants: items with no variant get NULL variant_name
    // but still show the base item price via COALESCE.
    $stmt = $pdo->prepare("
        SELECT
            mi.item_name,
            mv.variant_name                     AS variant_name,
            COALESCE(mv.price, mi.base_price, 0) AS price,
            SUM(oi.quantity)                    AS total_sold,
            SUM(oi.subtotal)                    AS total_revenue
        FROM order_items oi
        JOIN  menu_items    mi ON oi.item_id    = mi.item_id
        LEFT JOIN menu_variants mv ON oi.variant_id = mv.variant_id
        JOIN  orders        o  ON oi.order_id   = o.order_id
        WHERE o.status = 'completed'
        GROUP BY mi.item_id, mi.item_name, mv.variant_id, mv.variant_name, mv.price, mi.base_price
        ORDER BY total_sold DESC
        LIMIT 5
    ");
    $stmt->execute();
    $topItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ── Daily sales (last 7 days) ─────────────────────────────────────
    $stmt = $pdo->prepare("
        SELECT DATE(created_at) AS date,
               COUNT(*)          AS orders_count,
               SUM(total_amount) AS total_sales
        FROM orders
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
          AND status = 'completed'
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ");
    $stmt->execute();
    $dailySales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ── Sales by category ─────────────────────────────────────────────
    $stmt = $pdo->prepare("
        SELECT c.category_name,
               SUM(oi.subtotal) AS total_sales
        FROM orders o
        JOIN order_items oi ON o.order_id     = oi.order_id
        JOIN menu_items  mi ON oi.item_id     = mi.item_id
        JOIN categories  c  ON mi.category_id = c.category_id
        WHERE o.status = 'completed'
        GROUP BY c.category_id, c.category_name
        ORDER BY total_sales DESC
    ");
    $stmt->execute();
    $salesByCategory = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success'           => true,
        'pending_count'     => (int)   ($active['pending_count']   ?? 0),
        'preparing_count'   => (int)   ($active['preparing_count'] ?? 0),
        'ready_count'       => (int)   ($active['ready_count']     ?? 0),
        'total_active'      => (int)   ($active['total_active']    ?? 0),
        'today_revenue'     => (float) $todayRevenue,
        'monthly_revenue'   => (float) $monthlyRevenue,
        'today_orders'      => (int)   ($todayStats['today_orders']    ?? 0),
        'today_customers'   => (int)   ($todayStats['today_customers'] ?? 0),
        'low_stock'         => $lowStockCount,
        'low_stock_items'   => $lowStockItems,
        'recent_orders'     => $recentOrders,
        'top_items'         => $topItems,
        'daily_sales'       => $dailySales,
        'sales_by_category' => $salesByCategory,
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}