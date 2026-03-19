<?php
/**
 * reports_model.php
 * All database query functions for the Reports module.
 * Requires $pdo (PDO connection) to be available in scope.
 */

// ============================================================
// DAILY SUMMARY
// ============================================================
function rpt_daily_summary(PDO $pdo, string $date): array
{
    $stmt = $pdo->prepare("
        SELECT
            DATE(o.created_at)                              AS report_date,
            COUNT(DISTINCT o.order_id)                      AS total_orders,
            COALESCE(SUM(o.total_amount), 0)                AS gross_revenue,
            COALESCE(SUM(o.discount), 0)                    AS total_discounts,
            COALESCE(SUM(o.total_amount - o.discount), 0)   AS net_revenue,
            COALESCE(AVG(o.total_amount), 0)                AS avg_order_value,
            COUNT(DISTINCT o.cashier_id)                    AS active_cashiers,
            SUM(CASE WHEN o.status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_orders
        FROM orders o
        WHERE DATE(o.created_at) = :date
          AND o.status != 'cancelled'
    ");
    $stmt->execute([':date' => $date]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

// ============================================================
// MONTHLY SUMMARY
// ============================================================
function rpt_monthly_summary(PDO $pdo, int $year, int $month): array
{
    $stmt = $pdo->prepare("
        SELECT
            YEAR(o.created_at)                              AS year,
            MONTH(o.created_at)                             AS month,
            MONTHNAME(o.created_at)                         AS month_name,
            COUNT(DISTINCT o.order_id)                      AS total_orders,
            COALESCE(SUM(o.total_amount), 0)                AS gross_revenue,
            COALESCE(SUM(o.discount), 0)                    AS total_discounts,
            COALESCE(SUM(o.total_amount - o.discount), 0)   AS net_revenue,
            COALESCE(AVG(o.total_amount), 0)                AS avg_order_value,
            COUNT(DISTINCT o.cashier_id)                    AS active_cashiers,
            SUM(CASE WHEN o.status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_orders
        FROM orders o
        WHERE YEAR(o.created_at)  = :year
          AND MONTH(o.created_at) = :month
          AND o.status != 'cancelled'
    ");
    $stmt->execute([':year' => $year, ':month' => $month]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

// ============================================================
// YEARLY SUMMARY
// ============================================================
function rpt_yearly_summary(PDO $pdo, int $year): array
{
    $stmt = $pdo->prepare("
        SELECT
            YEAR(o.created_at)                              AS year,
            COUNT(DISTINCT o.order_id)                      AS total_orders,
            COALESCE(SUM(o.total_amount), 0)                AS gross_revenue,
            COALESCE(SUM(o.discount), 0)                    AS total_discounts,
            COALESCE(SUM(o.total_amount - o.discount), 0)   AS net_revenue,
            COALESCE(AVG(o.total_amount), 0)                AS avg_order_value,
            SUM(CASE WHEN MONTH(o.created_at)=1  THEN o.total_amount ELSE 0 END) AS jan,
            SUM(CASE WHEN MONTH(o.created_at)=2  THEN o.total_amount ELSE 0 END) AS feb,
            SUM(CASE WHEN MONTH(o.created_at)=3  THEN o.total_amount ELSE 0 END) AS mar,
            SUM(CASE WHEN MONTH(o.created_at)=4  THEN o.total_amount ELSE 0 END) AS apr,
            SUM(CASE WHEN MONTH(o.created_at)=5  THEN o.total_amount ELSE 0 END) AS may,
            SUM(CASE WHEN MONTH(o.created_at)=6  THEN o.total_amount ELSE 0 END) AS jun,
            SUM(CASE WHEN MONTH(o.created_at)=7  THEN o.total_amount ELSE 0 END) AS jul,
            SUM(CASE WHEN MONTH(o.created_at)=8  THEN o.total_amount ELSE 0 END) AS aug,
            SUM(CASE WHEN MONTH(o.created_at)=9  THEN o.total_amount ELSE 0 END) AS sep,
            SUM(CASE WHEN MONTH(o.created_at)=10 THEN o.total_amount ELSE 0 END) AS oct_m,
            SUM(CASE WHEN MONTH(o.created_at)=11 THEN o.total_amount ELSE 0 END) AS nov,
            SUM(CASE WHEN MONTH(o.created_at)=12 THEN o.total_amount ELSE 0 END) AS dec_m
        FROM orders o
        WHERE YEAR(o.created_at) = :year
          AND o.status != 'cancelled'
    ");
    $stmt->execute([':year' => $year]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

// ============================================================
// MONTHLY BREAKDOWN (day by day) – for daily trend chart
// ============================================================
function rpt_daily_breakdown(PDO $pdo, int $year, int $month): array
{
    $stmt = $pdo->prepare("
        SELECT
            DATE(o.created_at)              AS sale_date,
            COUNT(DISTINCT o.order_id)      AS total_orders,
            COALESCE(SUM(o.total_amount), 0) AS revenue
        FROM orders o
        WHERE YEAR(o.created_at)  = :year
          AND MONTH(o.created_at) = :month
          AND o.status != 'cancelled'
        GROUP BY DATE(o.created_at)
        ORDER BY sale_date
    ");
    $stmt->execute([':year' => $year, ':month' => $month]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ============================================================
// TOP SELLING ITEMS
// ============================================================
function rpt_top_items(PDO $pdo, string $start, string $end, int $limit = 10): array
{
    $stmt = $pdo->prepare("
        SELECT
            mi.item_name,
            c.category_name,
            mi.price,
            mi.cost_price,
            SUM(oi.quantity)                                                    AS units_sold,
            COALESCE(SUM(oi.subtotal), 0)                                       AS total_revenue,
            ROUND((mi.price - mi.cost_price) * SUM(oi.quantity), 2)            AS gross_profit
        FROM order_items oi
        JOIN menu_items mi ON oi.item_id   = mi.item_id
        JOIN categories c  ON mi.category_id = c.category_id
        JOIN orders o      ON oi.order_id  = o.order_id
        WHERE o.status = 'completed'
          AND DATE(o.created_at) BETWEEN :start AND :end
        GROUP BY mi.item_id, mi.item_name, c.category_name, mi.price, mi.cost_price
        ORDER BY units_sold DESC
        LIMIT :lim
    ");
    $stmt->bindValue(':start', $start);
    $stmt->bindValue(':end',   $end);
    $stmt->bindValue(':lim',   $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ============================================================
// REVENUE BY CATEGORY
// ============================================================
function rpt_sales_by_category(PDO $pdo, string $start, string $end): array
{
    $stmt = $pdo->prepare("
        SELECT
            c.category_name,
            SUM(oi.quantity)                AS units_sold,
            COALESCE(SUM(oi.subtotal), 0)   AS revenue
        FROM order_items oi
        JOIN menu_items mi ON oi.item_id     = mi.item_id
        JOIN categories c  ON mi.category_id = c.category_id
        JOIN orders o      ON oi.order_id    = o.order_id
        WHERE o.status = 'completed'
          AND DATE(o.created_at) BETWEEN :start AND :end
        GROUP BY c.category_id, c.category_name
        ORDER BY revenue DESC
    ");
    $stmt->execute([':start' => $start, ':end' => $end]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ============================================================
// CASHIER PERFORMANCE
// ============================================================
function rpt_cashier_performance(PDO $pdo, string $start, string $end): array
{
    $stmt = $pdo->prepare("
        SELECT
            u.full_name                                                         AS cashier,
            u.username,
            COUNT(DISTINCT o.order_id)                                          AS orders_handled,
            COALESCE(SUM(o.total_amount), 0)                                    AS total_sales,
            COALESCE(AVG(o.total_amount), 0)                                    AS avg_order_value,
            COALESCE(SUM(o.discount), 0)                                        AS discounts_given,
            SUM(CASE WHEN o.status = 'cancelled' THEN 1 ELSE 0 END)            AS cancelled_orders
        FROM users u
        JOIN orders o ON u.user_id = o.cashier_id
        WHERE u.role IN ('cashier', 'admin')
          AND DATE(o.created_at) BETWEEN :start AND :end
        GROUP BY u.user_id, u.full_name, u.username
        ORDER BY total_sales DESC
    ");
    $stmt->execute([':start' => $start, ':end' => $end]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ============================================================
// HOURLY PEAK HOURS
// ============================================================
function rpt_peak_hours(PDO $pdo, string $start, string $end): array
{
    $stmt = $pdo->prepare("
        SELECT
            HOUR(o.created_at)              AS hour_of_day,
            COUNT(o.order_id)               AS total_orders,
            COALESCE(SUM(o.total_amount), 0) AS revenue
        FROM orders o
        WHERE o.status = 'completed'
          AND DATE(o.created_at) BETWEEN :start AND :end
        GROUP BY HOUR(o.created_at)
        ORDER BY hour_of_day
    ");
    $stmt->execute([':start' => $start, ':end' => $end]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ============================================================
// ORDER TYPE BREAKDOWN
// ============================================================
function rpt_order_type_breakdown(PDO $pdo, string $start, string $end): array
{
    $stmt = $pdo->prepare("
        SELECT
            o.order_type,
            o.order_source,
            COUNT(o.order_id)               AS total_orders,
            COALESCE(SUM(o.total_amount), 0) AS revenue
        FROM orders o
        WHERE o.status = 'completed'
          AND DATE(o.created_at) BETWEEN :start AND :end
        GROUP BY o.order_type, o.order_source
        ORDER BY total_orders DESC
    ");
    $stmt->execute([':start' => $start, ':end' => $end]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ============================================================
// TOP ADD-ONS
// ============================================================
function rpt_top_addons(PDO $pdo, string $start, string $end): array
{
    $stmt = $pdo->prepare("
        SELECT
            a.addon_name,
            SUM(oia.quantity)                               AS times_ordered,
            COALESCE(SUM(oia.price * oia.quantity), 0)      AS total_revenue
        FROM order_item_addons oia
        JOIN addons a       ON oia.addon_id      = a.addon_id
        JOIN order_items oi ON oia.order_item_id = oi.order_item_id
        JOIN orders o       ON oi.order_id       = o.order_id
        WHERE o.status = 'completed'
          AND DATE(o.created_at) BETWEEN :start AND :end
        GROUP BY a.addon_id, a.addon_name
        ORDER BY total_revenue DESC
        LIMIT 10
    ");
    $stmt->execute([':start' => $start, ':end' => $end]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ============================================================
// DISCOUNT & CANCELLATION AUDIT
// ============================================================
function rpt_discount_audit(PDO $pdo, string $start, string $end): array
{
    $stmt = $pdo->prepare("
        SELECT
            DATE(o.created_at)      AS sale_date,
            o.order_number,
            u.full_name             AS cashier,
            o.total_amount,
            o.discount,
            o.status,
            o.customer_name
        FROM orders o
        JOIN users u ON o.cashier_id = u.user_id
        WHERE (o.discount > 0 OR o.status = 'cancelled')
          AND DATE(o.created_at) BETWEEN :start AND :end
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([':start' => $start, ':end' => $end]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ============================================================
// PAYMENT RECONCILIATION
// ============================================================
function rpt_payment_reconciliation(PDO $pdo, string $start, string $end): array
{
    $stmt = $pdo->prepare("
        SELECT
            DATE(p.paid_at)                                     AS pay_date,
            u.full_name                                         AS cashier,
            COUNT(p.payment_id)                                 AS transactions,
            COALESCE(SUM(p.amount_paid), 0)                     AS cash_received,
            COALESCE(SUM(p.change_amount), 0)                   AS change_given,
            COALESCE(SUM(p.amount_paid - p.change_amount), 0)   AS net_cash,
            COALESCE(SUM(o.total_amount), 0)                    AS expected_from_orders
        FROM payments p
        JOIN users u  ON p.cashier_id = u.user_id
        JOIN orders o ON p.order_id   = o.order_id
        WHERE p.payment_status = 'paid'
          AND DATE(p.paid_at) BETWEEN :start AND :end
        GROUP BY DATE(p.paid_at), u.user_id, u.full_name
        ORDER BY pay_date DESC, u.full_name
    ");
    $stmt->execute([':start' => $start, ':end' => $end]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ============================================================
// LOW STOCK SNAPSHOT
// ============================================================
function rpt_low_stock(PDO $pdo): array
{
    $stmt = $pdo->query("
        SELECT
            ingredient_name,
            stock_quantity,
            unit,
            low_stock_alert,
            CASE
                WHEN stock_quantity = 0                        THEN 'OUT OF STOCK'
                WHEN stock_quantity <= low_stock_alert         THEN 'LOW'
                ELSE 'OK'
            END AS stock_status,
            last_updated
        FROM ingredients
        WHERE stock_quantity <= low_stock_alert
        ORDER BY stock_quantity ASC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ============================================================
// HELPER – available years for the year picker
// ============================================================
function rpt_available_years(PDO $pdo): array
{
    $stmt = $pdo->query("SELECT DISTINCT YEAR(created_at) AS yr FROM orders ORDER BY yr DESC");
    return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'yr');
}