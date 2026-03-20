<?php
/**
 * reports_model.php  – v3, fully corrected
 *
 * KEY RULES applied throughout:
 *  - Revenue is recognised on DATE(o.completed_at)  — the moment an order
 *    is marked completed, NOT when it was placed (created_at).
 *  - completed_at IS NULL for pending/preparing/cancelled orders, so
 *    filtering on completed_at automatically excludes those rows.
 *  - "completed" is the only terminal success status in your ENUM
 *    ('pending','preparing','completed','cancelled'). There is no 'paid'.
 *  - o.subtotal  = sum of line items before discount
 *    o.discount   = discount applied
 *    o.total_amount = subtotal - discount  (what the customer actually paid)
 *  - Status literals are always inlined, never bound as params, to avoid
 *    PDO emulation silently returning empty rows.
 */

// ============================================================
// DAILY REVENUE SUMMARY
// Reports on orders COMPLETED on $date.
// Optionally scoped to a single cashier.
// ============================================================
function rpt_daily_summary(PDO $pdo, string $date, ?int $cashier_id = null): array
{
    $cid_clause = $cashier_id !== null ? 'AND o.cashier_id = :cid' : '';

    $stmt = $pdo->prepare("
        SELECT
            -- Completed orders only (completed_at IS NOT NULL AND status='completed')
            COUNT(o.order_id)                               AS total_orders,
            COALESCE(SUM(o.subtotal), 0)                    AS gross_revenue,
            COALESCE(SUM(o.discount), 0)                    AS total_discounts,
            COALESCE(SUM(o.total_amount), 0)                AS net_revenue,
            COALESCE(AVG(o.total_amount), 0)                AS avg_order_value,
            COUNT(DISTINCT o.cashier_id)                    AS active_cashiers,
            -- Cancelled orders created on the same calendar day
            (
                SELECT COUNT(*)
                FROM orders c2
                WHERE DATE(c2.created_at) = :date_c
                  AND c2.status = 'cancelled'
                $cid_clause
            )                                               AS cancelled_orders
        FROM orders o
        WHERE o.status = 'completed'
          AND DATE(o.completed_at) = :date
        $cid_clause
    ");

    $params = [':date' => $date, ':date_c' => $date];
    if ($cashier_id !== null) {
        $params[':cid'] = $cashier_id;
    }

    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

// ============================================================
// CLOSE-OUT  (one cashier, one date)
// Uses completed_at for revenue; created_at for cancelled count.
// ============================================================
function rpt_closeout(PDO $pdo, string $date, int $cashier_id): array
{
    // Completed orders this cashier finished today
    $stmt = $pdo->prepare("
        SELECT
            u.full_name                                 AS cashier_name,
            u.username,
            COUNT(o.order_id)                           AS total_orders,
            COALESCE(SUM(o.subtotal), 0)                AS gross_sales,
            COALESCE(SUM(o.discount), 0)                AS total_discounts,
            COALESCE(SUM(o.total_amount), 0)            AS net_sales,
            -- Cancelled count by created_at (they never reach completed_at)
            (
                SELECT COUNT(*)
                FROM orders cx
                WHERE cx.cashier_id = :cid_c
                  AND DATE(cx.created_at) = :date_c
                  AND cx.status = 'cancelled'
            )                                           AS cancelled_orders
        FROM users u
        LEFT JOIN orders o
            ON  o.cashier_id   = u.user_id
            AND o.status       = 'completed'
            AND DATE(o.completed_at) = :date
        WHERE u.user_id = :cid
        GROUP BY u.user_id, u.full_name, u.username
    ");
    $stmt->execute([
        ':date'   => $date,
        ':cid'    => $cashier_id,
        ':cid_c'  => $cashier_id,
        ':date_c' => $date,
    ]);
    $summary = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    // Cash collected by this cashier today (from payments table)
    $stmt2 = $pdo->prepare("
        SELECT
            COALESCE(SUM(p.amount_paid), 0)                     AS cash_received,
            COALESCE(SUM(p.change_amount), 0)                   AS change_given,
            COALESCE(SUM(p.amount_paid - p.change_amount), 0)   AS cash_to_turn_over,
            COUNT(p.payment_id)                                 AS payment_count
        FROM payments p
        WHERE p.cashier_id     = :cid
          AND DATE(p.paid_at)  = :date
          AND p.payment_status = 'paid'
    ");
    $stmt2->execute([':cid' => $cashier_id, ':date' => $date]);
    $cash = $stmt2->fetch(PDO::FETCH_ASSOC) ?: [];

    return array_merge($summary, $cash);
}

// ============================================================
// MONTHLY SUMMARY
// Revenue from orders completed within the given month.
// ============================================================
function rpt_monthly_summary(PDO $pdo, int $year, int $month): array
{
    $stmt = $pdo->prepare("
        SELECT
            MONTHNAME(o.completed_at)               AS month_name,
            COUNT(o.order_id)                       AS total_orders,
            COALESCE(SUM(o.subtotal), 0)            AS gross_revenue,
            COALESCE(SUM(o.discount), 0)            AS total_discounts,
            COALESCE(SUM(o.total_amount), 0)        AS net_revenue,
            COALESCE(AVG(o.total_amount), 0)        AS avg_order_value,
            COUNT(DISTINCT o.cashier_id)            AS active_cashiers,
            -- Cancelled by created_at (they have no completed_at)
            (
                SELECT COUNT(*)
                FROM orders cx
                WHERE cx.status = 'cancelled'
                  AND YEAR(cx.created_at)  = :year_c
                  AND MONTH(cx.created_at) = :month_c
            )                                       AS cancelled_orders
        FROM orders o
        WHERE o.status = 'completed'
          AND YEAR(o.completed_at)  = :year
          AND MONTH(o.completed_at) = :month
    ");
    $stmt->execute([
        ':year'    => $year,  ':month'    => $month,
        ':year_c'  => $year,  ':month_c'  => $month,
    ]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

// ============================================================
// DAILY BREAKDOWN  (day-by-day chart data within a month)
// Uses completed_at so chart reflects actual revenue days.
// ============================================================
function rpt_daily_breakdown(PDO $pdo, int $year, int $month): array
{
    $stmt = $pdo->prepare("
        SELECT
            DATE(o.completed_at)            AS sale_date,
            COUNT(o.order_id)               AS total_orders,
            COALESCE(SUM(o.total_amount), 0) AS revenue
        FROM orders o
        WHERE o.status = 'completed'
          AND YEAR(o.completed_at)  = :year
          AND MONTH(o.completed_at) = :month
        GROUP BY DATE(o.completed_at)
        ORDER BY sale_date
    ");
    $stmt->execute([':year' => $year, ':month' => $month]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ============================================================
// YEARLY SUMMARY
// Revenue from orders completed within the given year.
// ============================================================
function rpt_yearly_summary(PDO $pdo, int $year): array
{
    $stmt = $pdo->prepare("
        SELECT
            COUNT(o.order_id)                                                       AS total_orders,
            COALESCE(SUM(o.subtotal), 0)                                            AS gross_revenue,
            COALESCE(SUM(o.discount), 0)                                            AS total_discounts,
            COALESCE(SUM(o.total_amount), 0)                                        AS net_revenue,
            COALESCE(AVG(o.total_amount), 0)                                        AS avg_order_value,
            -- Monthly columns use completed_at month
            SUM(CASE WHEN MONTH(o.completed_at)=1  THEN o.total_amount ELSE 0 END) AS jan,
            SUM(CASE WHEN MONTH(o.completed_at)=2  THEN o.total_amount ELSE 0 END) AS feb,
            SUM(CASE WHEN MONTH(o.completed_at)=3  THEN o.total_amount ELSE 0 END) AS mar,
            SUM(CASE WHEN MONTH(o.completed_at)=4  THEN o.total_amount ELSE 0 END) AS apr,
            SUM(CASE WHEN MONTH(o.completed_at)=5  THEN o.total_amount ELSE 0 END) AS may,
            SUM(CASE WHEN MONTH(o.completed_at)=6  THEN o.total_amount ELSE 0 END) AS jun,
            SUM(CASE WHEN MONTH(o.completed_at)=7  THEN o.total_amount ELSE 0 END) AS jul,
            SUM(CASE WHEN MONTH(o.completed_at)=8  THEN o.total_amount ELSE 0 END) AS aug,
            SUM(CASE WHEN MONTH(o.completed_at)=9  THEN o.total_amount ELSE 0 END) AS sep,
            SUM(CASE WHEN MONTH(o.completed_at)=10 THEN o.total_amount ELSE 0 END) AS oct_m,
            SUM(CASE WHEN MONTH(o.completed_at)=11 THEN o.total_amount ELSE 0 END) AS nov,
            SUM(CASE WHEN MONTH(o.completed_at)=12 THEN o.total_amount ELSE 0 END) AS dec_m
        FROM orders o
        WHERE o.status = 'completed'
          AND YEAR(o.completed_at) = :year
    ");
    $stmt->execute([':year' => $year]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

// ============================================================
// TOP SELLING ITEMS  (by completed_at date range)
// ============================================================
function rpt_top_items(PDO $pdo, string $start, string $end, int $limit = 10): array
{
    // gross_profit uses oi.unit_price (price locked at time of sale)
    // NOT mi.price (current menu price) — so historical reports stay
    // accurate even if you change prices later.
    // cost_price still comes from mi.cost_price because we don't store
    // cost at time of sale (not in the schema). Accept this minor caveat.
    $stmt = $pdo->prepare("
        SELECT
            mi.item_name,
            c.category_name,
            oi.unit_price                                               AS price,
            mi.cost_price,
            SUM(oi.quantity)                                            AS units_sold,
            COALESCE(SUM(oi.subtotal), 0)                               AS total_revenue,
            ROUND((oi.unit_price - mi.cost_price) * SUM(oi.quantity), 2) AS gross_profit
        FROM order_items oi
        JOIN menu_items mi  ON oi.item_id     = mi.item_id
        JOIN categories c   ON mi.category_id = c.category_id
        JOIN orders o       ON oi.order_id    = o.order_id
        WHERE o.status = 'completed'
          AND DATE(o.completed_at) BETWEEN :start AND :end
        GROUP BY mi.item_id, mi.item_name, c.category_name, oi.unit_price, mi.cost_price
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
// REVENUE BY CATEGORY  (by completed_at date range)
// ============================================================
function rpt_sales_by_category(PDO $pdo, string $start, string $end): array
{
    $stmt = $pdo->prepare("
        SELECT
            c.category_name,
            SUM(oi.quantity)                 AS units_sold,
            COALESCE(SUM(oi.subtotal), 0)    AS revenue
        FROM order_items oi
        JOIN menu_items mi  ON oi.item_id     = mi.item_id
        JOIN categories c   ON mi.category_id = c.category_id
        JOIN orders o       ON oi.order_id    = o.order_id
        WHERE o.status = 'completed'
          AND DATE(o.completed_at) BETWEEN :start AND :end
        GROUP BY c.category_id, c.category_name
        ORDER BY revenue DESC
    ");
    $stmt->execute([':start' => $start, ':end' => $end]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ============================================================
// CASHIER PERFORMANCE  (admin only)
// Revenue scoped to completed_at range.
// ============================================================
function rpt_cashier_performance(PDO $pdo, string $start, string $end): array
{
    // Rewritten using two clean subqueries — no duplicate PDO params,
    // no fragile CASE WHEN inside WHERE.
    //
    // completed subquery  → filters on completed_at (revenue date)
    // cancelled subquery  → filters on created_at   (only timestamp they have)
    // LEFT JOINed to users so cashiers with ZERO activity still appear.
    $stmt = $pdo->prepare("
        SELECT
            u.user_id,
            u.full_name                                     AS cashier,
            u.username,
            COALESCE(c.orders_handled,  0)                  AS orders_handled,
            COALESCE(c.total_sales,     0)                  AS total_sales,
            COALESCE(c.avg_order_value, 0)                  AS avg_order_value,
            COALESCE(c.discounts_given, 0)                  AS discounts_given,
            COALESCE(x.cancelled_orders, 0)                 AS cancelled_orders
        FROM users u

        -- Completed orders in range (revenue date = completed_at)
        LEFT JOIN (
            SELECT
                cashier_id,
                COUNT(order_id)         AS orders_handled,
                SUM(total_amount)       AS total_sales,
                AVG(total_amount)       AS avg_order_value,
                SUM(discount)           AS discounts_given
            FROM orders
            WHERE status = 'completed'
              AND DATE(completed_at) BETWEEN :start AND :end
            GROUP BY cashier_id
        ) c ON u.user_id = c.cashier_id

        -- Cancelled orders in range (date = created_at, no completed_at)
        LEFT JOIN (
            SELECT
                cashier_id,
                COUNT(order_id)         AS cancelled_orders
            FROM orders
            WHERE status = 'cancelled'
              AND DATE(created_at) BETWEEN :start2 AND :end2
            GROUP BY cashier_id
        ) x ON u.user_id = x.cashier_id

        WHERE u.role IN ('cashier', 'admin')
          AND u.is_active = 1
          -- Only show cashiers who had activity in this period
          AND (c.cashier_id IS NOT NULL OR x.cashier_id IS NOT NULL)

        ORDER BY total_sales DESC
    ");
    $stmt->execute([
        ':start'  => $start,
        ':end'    => $end,
        ':start2' => $start,
        ':end2'   => $end,
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ============================================================
// PEAK HOURS  (hour of day orders were COMPLETED)
// ============================================================
function rpt_peak_hours(PDO $pdo, string $start, string $end): array
{
    $stmt = $pdo->prepare("
        SELECT
            HOUR(o.completed_at)             AS hour_of_day,
            COUNT(o.order_id)                AS total_orders,
            COALESCE(SUM(o.total_amount), 0) AS revenue
        FROM orders o
        WHERE o.status = 'completed'
          AND DATE(o.completed_at) BETWEEN :start AND :end
        GROUP BY HOUR(o.completed_at)
        ORDER BY hour_of_day
    ");
    $stmt->execute([':start' => $start, ':end' => $end]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ============================================================
// ORDER TYPE BREAKDOWN  (completed orders only)
// ============================================================
function rpt_order_type_breakdown(PDO $pdo, string $start, string $end): array
{
    $stmt = $pdo->prepare("
        SELECT
            o.order_type,
            o.order_source,
            COUNT(o.order_id)                AS total_orders,
            COALESCE(SUM(o.total_amount), 0) AS revenue
        FROM orders o
        WHERE o.status = 'completed'
          AND DATE(o.completed_at) BETWEEN :start AND :end
        GROUP BY o.order_type, o.order_source
        ORDER BY total_orders DESC
    ");
    $stmt->execute([':start' => $start, ':end' => $end]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ============================================================
// TOP ADD-ONS  (from completed orders)
// ============================================================
function rpt_top_addons(PDO $pdo, string $start, string $end): array
{
    $stmt = $pdo->prepare("
        SELECT
            a.addon_name,
            SUM(oia.quantity)                           AS times_ordered,
            COALESCE(SUM(oia.price * oia.quantity), 0)  AS total_revenue
        FROM order_item_addons oia
        JOIN addons a       ON oia.addon_id      = a.addon_id
        JOIN order_items oi ON oia.order_item_id = oi.order_item_id
        JOIN orders o       ON oi.order_id       = o.order_id
        WHERE o.status = 'completed'
          AND DATE(o.completed_at) BETWEEN :start AND :end
        GROUP BY a.addon_id, a.addon_name
        ORDER BY total_revenue DESC
        LIMIT 10
    ");
    $stmt->execute([':start' => $start, ':end' => $end]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ============================================================
// DISCOUNT & CANCELLATION AUDIT  (admin only)
// Discounts → completed_at; cancellations → created_at.
// ============================================================
function rpt_discount_audit(PDO $pdo, string $start, string $end): array
{
    $stmt = $pdo->prepare("
        SELECT
            o.order_number,
            u.full_name                                         AS cashier,
            o.subtotal,
            o.discount,
            o.total_amount,
            o.status,
            o.customer_name,
            o.created_at,
            o.completed_at
        FROM orders o
        JOIN users u ON o.cashier_id = u.user_id
        WHERE (
            -- Discounted completed orders: filter by completed date
            (o.discount > 0
             AND o.status = 'completed'
             AND DATE(o.completed_at) BETWEEN :start AND :end)
            OR
            -- Cancelled orders: filter by created date (no completed_at)
            (o.status = 'cancelled'
             AND DATE(o.created_at) BETWEEN :start2 AND :end2)
        )
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([
        ':start'  => $start, ':end'  => $end,
        ':start2' => $start, ':end2' => $end,
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ============================================================
// PAYMENT RECONCILIATION  (admin only)
// Joins only to completed orders to avoid including refunds
// or cancelled-order phantom payments.
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
            -- Expected = sum of total_amount on completed orders paid that day
            COALESCE(SUM(o.total_amount), 0)                    AS expected_from_orders
        FROM payments p
        JOIN users u  ON p.cashier_id = u.user_id
        JOIN orders o ON p.order_id   = o.order_id
                     AND o.status     = 'completed'
        WHERE p.payment_status = 'paid'
          AND DATE(p.paid_at) BETWEEN :start AND :end
        GROUP BY DATE(p.paid_at), u.user_id, u.full_name
        ORDER BY pay_date DESC, u.full_name
    ");
    $stmt->execute([':start' => $start, ':end' => $end]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ============================================================
// LOW STOCK
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
                WHEN stock_quantity = 0                THEN 'OUT OF STOCK'
                WHEN stock_quantity <= low_stock_alert THEN 'LOW'
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
// AVAILABLE YEARS  (based on completed_at so only real revenue
// years appear in the yearly picker)
// ============================================================
function rpt_available_years(PDO $pdo): array
{
    $stmt = $pdo->query("
        SELECT DISTINCT YEAR(completed_at) AS yr
        FROM orders
        WHERE status = 'completed'
          AND completed_at IS NOT NULL
        ORDER BY yr DESC
    ");
    return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'yr');
}