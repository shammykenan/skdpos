<?php
/**
 * reports_model.php  – v4 (improved)
 *
 * KEY RULES:
 *  - Revenue recognised on DATE(o.completed_at) — moment order is completed
 *  - completed_at IS NULL for pending/preparing/cancelled — auto-excludes them
 *  - o.subtotal     = sum of line items before discount (excl. VAT in display)
 *  - o.discount     = discount applied
 *  - o.total_amount = what customer actually paid (VAT-inclusive)
 *  - VAT = total_amount - (total_amount / 1.12)  i.e. total is VAT-inclusive
 *  - gross_profit uses SUM((unit_price - cost_price) * quantity) — correct order
 *  - rpt_low_stock JOINs units table (ingredients has unit_id FK, not unit column)
 */

// ============================================================
// HELPERS
// ============================================================

/**
 * Extract VAT-exclusive amount from a VAT-inclusive total (12% VAT).
 * Use in PHP when computing display values.
 */
function vat_exclusive(float $total): float { return $total / 1.12; }
function vat_amount(float $total): float    { return $total - vat_exclusive($total); }

// ============================================================
// DAILY REVENUE SUMMARY
// ============================================================
function rpt_daily_summary(PDO $pdo, string $date, ?int $cashier_id = null): array
{
    $cid_clause = $cashier_id !== null ? 'AND o.cashier_id = :cid' : '';

    $stmt = $pdo->prepare("
        SELECT
            COUNT(o.order_id)                                       AS total_orders,
            COALESCE(SUM(o.subtotal), 0)                            AS gross_revenue,
            COALESCE(SUM(o.discount), 0)                            AS total_discounts,
            COALESCE(SUM(o.total_amount), 0)                        AS net_revenue,
            -- VAT breakdown (12% VAT-inclusive)
            COALESCE(SUM(o.total_amount / 1.12), 0)                 AS net_revenue_excl_vat,
            COALESCE(SUM(o.total_amount - o.total_amount / 1.12), 0) AS total_vat_collected,
            COALESCE(AVG(o.total_amount), 0)                        AS avg_order_value,
            COUNT(DISTINCT o.cashier_id)                            AS active_cashiers,
            (
                SELECT COUNT(*)
                FROM orders c2
                WHERE DATE(c2.created_at) = :date_c
                  AND c2.status = 'cancelled'
                  $cid_clause
            ) AS cancelled_orders
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
// ============================================================
function rpt_closeout(PDO $pdo, string $date, int $cashier_id): array
{
    $stmt = $pdo->prepare("
        SELECT
            u.full_name                                             AS cashier_name,
            u.username,
            COUNT(o.order_id)                                       AS total_orders,
            COALESCE(SUM(o.subtotal), 0)                            AS gross_sales,
            COALESCE(SUM(o.discount), 0)                            AS total_discounts,
            COALESCE(SUM(o.total_amount), 0)                        AS net_sales,
            COALESCE(SUM(o.total_amount / 1.12), 0)                 AS net_sales_excl_vat,
            COALESCE(SUM(o.total_amount - o.total_amount / 1.12), 0) AS vat_collected,
            (
                SELECT COUNT(*)
                FROM orders cx
                WHERE cx.cashier_id      = :cid_c
                  AND DATE(cx.created_at) = :date_c
                  AND cx.status          = 'cancelled'
            ) AS cancelled_orders
        FROM users u
        LEFT JOIN orders o
            ON  o.cashier_id         = u.user_id
            AND o.status             = 'completed'
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

    // Cash from payments table (cashier_id = original order owner)
    $stmt2 = $pdo->prepare("
        SELECT
            COALESCE(SUM(p.amount_paid), 0)                     AS cash_received,
            COALESCE(SUM(p.change_amount), 0)                   AS change_given,
            COALESCE(SUM(p.amount_paid - p.change_amount), 0)   AS cash_to_turn_over,
            COUNT(p.payment_id)                                 AS payment_count,
            -- Count how many payments were processed by someone else (admin)
            SUM(CASE WHEN p.processed_by IS NOT NULL
                      AND p.processed_by != p.cashier_id
                     THEN 1 ELSE 0 END)                         AS admin_processed_count
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
// ============================================================
function rpt_monthly_summary(PDO $pdo, int $year, int $month): array
{
    $stmt = $pdo->prepare("
        SELECT
            MONTHNAME(o.completed_at)                               AS month_name,
            COUNT(o.order_id)                                       AS total_orders,
            COALESCE(SUM(o.subtotal), 0)                            AS gross_revenue,
            COALESCE(SUM(o.discount), 0)                            AS total_discounts,
            COALESCE(SUM(o.total_amount), 0)                        AS net_revenue,
            COALESCE(SUM(o.total_amount / 1.12), 0)                 AS net_revenue_excl_vat,
            COALESCE(SUM(o.total_amount - o.total_amount / 1.12), 0) AS total_vat_collected,
            COALESCE(AVG(o.total_amount), 0)                        AS avg_order_value,
            COUNT(DISTINCT o.cashier_id)                            AS active_cashiers,
            (
                SELECT COUNT(*)
                FROM orders cx
                WHERE cx.status          = 'cancelled'
                  AND YEAR(cx.created_at)  = :year_c
                  AND MONTH(cx.created_at) = :month_c
            ) AS cancelled_orders
        FROM orders o
        WHERE o.status = 'completed'
          AND YEAR(o.completed_at)  = :year
          AND MONTH(o.completed_at) = :month
    ");
    $stmt->execute([
        ':year'    => $year,  ':month'   => $month,
        ':year_c'  => $year,  ':month_c' => $month,
    ]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

// ============================================================
// DAILY BREAKDOWN  (day-by-day chart within a month)
// ============================================================
function rpt_daily_breakdown(PDO $pdo, int $year, int $month): array
{
    $stmt = $pdo->prepare("
        SELECT
            DATE(o.completed_at)                                     AS sale_date,
            COUNT(o.order_id)                                        AS total_orders,
            COALESCE(SUM(o.total_amount), 0)                         AS revenue,
            COALESCE(SUM(o.total_amount / 1.12), 0)                  AS revenue_excl_vat,
            COALESCE(SUM(o.total_amount - o.total_amount / 1.12), 0) AS vat_amount
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
// ============================================================
function rpt_yearly_summary(PDO $pdo, int $year): array
{
    $stmt = $pdo->prepare("
        SELECT
            COUNT(o.order_id)                                                       AS total_orders,
            COALESCE(SUM(o.subtotal), 0)                                            AS gross_revenue,
            COALESCE(SUM(o.discount), 0)                                            AS total_discounts,
            COALESCE(SUM(o.total_amount), 0)                                        AS net_revenue,
            COALESCE(SUM(o.total_amount / 1.12), 0)                                 AS net_revenue_excl_vat,
            COALESCE(SUM(o.total_amount - o.total_amount / 1.12), 0)                AS total_vat_collected,
            COALESCE(AVG(o.total_amount), 0)                                        AS avg_order_value,
            -- Monthly columns
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
// TOP SELLING ITEMS
// Fixed: SUM((unit_price - cost_price) * quantity) — correct gross_profit
// ============================================================
function rpt_top_items(PDO $pdo, string $start, string $end, int $limit = 10): array
{
    $stmt = $pdo->prepare("
        SELECT
            mi.item_name,
            c.category_name,
            oi.unit_price                                                   AS price,
            mi.cost_price,
            SUM(oi.quantity)                                                AS units_sold,
            COALESCE(SUM(oi.subtotal), 0)                                   AS total_revenue,
            -- Fixed: SUM wraps the full expression
            COALESCE(SUM((oi.unit_price - mi.cost_price) * oi.quantity), 0) AS gross_profit,
            -- VAT breakdown per item
            COALESCE(SUM(oi.subtotal / 1.12), 0)                            AS revenue_excl_vat,
            COALESCE(SUM(oi.subtotal - oi.subtotal / 1.12), 0)              AS vat_amount
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
// REVENUE BY CATEGORY
// ============================================================
function rpt_sales_by_category(PDO $pdo, string $start, string $end): array
{
    $stmt = $pdo->prepare("
        SELECT
            c.category_name,
            SUM(oi.quantity)                                            AS units_sold,
            COALESCE(SUM(oi.subtotal), 0)                               AS revenue,
            COALESCE(SUM(oi.subtotal / 1.12), 0)                        AS revenue_excl_vat,
            COALESCE(SUM(oi.subtotal - oi.subtotal / 1.12), 0)          AS vat_amount
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
// CASHIER PERFORMANCE
// ============================================================
function rpt_cashier_performance(PDO $pdo, string $start, string $end): array
{
    $stmt = $pdo->prepare("
        SELECT
            u.user_id,
            u.full_name                                     AS cashier,
            u.username,
            COALESCE(c.orders_handled,  0)                  AS orders_handled,
            COALESCE(c.total_sales,     0)                  AS total_sales,
            COALESCE(c.total_sales / 1.12, 0)               AS total_sales_excl_vat,
            COALESCE(c.total_sales - c.total_sales / 1.12, 0) AS vat_collected,
            COALESCE(c.avg_order_value, 0)                  AS avg_order_value,
            COALESCE(c.discounts_given, 0)                  AS discounts_given,
            COALESCE(x.cancelled_orders, 0)                 AS cancelled_orders,
            -- How many of their orders were processed by admin
            COALESCE(p.admin_processed, 0)                  AS admin_processed_count
        FROM users u

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

        LEFT JOIN (
            SELECT
                cashier_id,
                COUNT(order_id)         AS cancelled_orders
            FROM orders
            WHERE status = 'cancelled'
              AND DATE(created_at) BETWEEN :start2 AND :end2
            GROUP BY cashier_id
        ) x ON u.user_id = x.cashier_id

        -- Count payments processed by someone other than the cashier
        LEFT JOIN (
            SELECT
                cashier_id,
                COUNT(*) AS admin_processed
            FROM payments
            WHERE payment_status = 'paid'
              AND processed_by IS NOT NULL
              AND processed_by != cashier_id
              AND DATE(paid_at) BETWEEN :start3 AND :end3
            GROUP BY cashier_id
        ) p ON u.user_id = p.cashier_id

        WHERE u.role IN ('cashier', 'admin')
          AND u.is_active = 1
          AND (c.cashier_id IS NOT NULL OR x.cashier_id IS NOT NULL)

        ORDER BY total_sales DESC
    ");
    $stmt->execute([
        ':start'  => $start, ':end'  => $end,
        ':start2' => $start, ':end2' => $end,
        ':start3' => $start, ':end3' => $end,
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ============================================================
// PEAK HOURS
// ============================================================
function rpt_peak_hours(PDO $pdo, string $start, string $end): array
{
    $stmt = $pdo->prepare("
        SELECT
            HOUR(o.completed_at)                                     AS hour_of_day,
            COUNT(o.order_id)                                        AS total_orders,
            COALESCE(SUM(o.total_amount), 0)                         AS revenue,
            COALESCE(SUM(o.total_amount / 1.12), 0)                  AS revenue_excl_vat
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
// ORDER TYPE BREAKDOWN
// ============================================================
function rpt_order_type_breakdown(PDO $pdo, string $start, string $end): array
{
    $stmt = $pdo->prepare("
        SELECT
            o.order_type,
            o.order_source,
            COUNT(o.order_id)                                        AS total_orders,
            COALESCE(SUM(o.total_amount), 0)                         AS revenue,
            COALESCE(SUM(o.total_amount / 1.12), 0)                  AS revenue_excl_vat
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
// TOP ADD-ONS
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
// DISCOUNT & CANCELLATION AUDIT
// ============================================================
function rpt_discount_audit(PDO $pdo, string $start, string $end): array
{
    $stmt = $pdo->prepare("
        SELECT
            o.order_number,
            u.full_name                         AS cashier,
            o.subtotal,
            o.discount,
            o.total_amount,
            -- VAT breakdown
            ROUND(o.total_amount / 1.12, 2)     AS amount_excl_vat,
            ROUND(o.total_amount - o.total_amount / 1.12, 2) AS vat_amount,
            o.status,
            o.customer_name,
            o.created_at,
            o.completed_at
        FROM orders o
        JOIN users u ON o.cashier_id = u.user_id
        WHERE (
            (o.discount > 0
             AND o.status = 'completed'
             AND DATE(o.completed_at) BETWEEN :start AND :end)
            OR
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
// PAYMENT RECONCILIATION
// Now shows processed_by info for admin-handled payments
// ============================================================
function rpt_payment_reconciliation(PDO $pdo, string $start, string $end): array
{
    $stmt = $pdo->prepare("
        SELECT
            DATE(p.paid_at)                                             AS pay_date,
            u.full_name                                                 AS cashier,
            COUNT(p.payment_id)                                         AS transactions,
            COALESCE(SUM(p.amount_paid), 0)                             AS cash_received,
            COALESCE(SUM(p.change_amount), 0)                           AS change_given,
            COALESCE(SUM(p.amount_paid - p.change_amount), 0)           AS net_cash,
            COALESCE(SUM(o.total_amount), 0)                            AS expected_from_orders,
            -- VAT on expected
            COALESCE(SUM(o.total_amount / 1.12), 0)                     AS expected_excl_vat,
            COALESCE(SUM(o.total_amount - o.total_amount / 1.12), 0)    AS vat_in_expected,
            -- Variance (positive = over-collected, negative = short)
            COALESCE(SUM(p.amount_paid - p.change_amount), 0)
                - COALESCE(SUM(o.total_amount), 0)                      AS variance,
            -- Count of admin-processed payments for this cashier
            SUM(CASE WHEN p.processed_by IS NOT NULL
                      AND p.processed_by != p.cashier_id
                     THEN 1 ELSE 0 END)                                 AS admin_processed_count
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
// LOW STOCK — Fixed: JOINs units table (no `unit` column on ingredients)
// ============================================================
function rpt_low_stock(PDO $pdo): array
{
    $stmt = $pdo->query("
        SELECT
            i.ingredient_id,
            i.ingredient_name,
            i.stock_quantity,
            i.low_stock_alert,
            i.last_updated,
            u.unit_code     AS unit,
            u.unit_label,
            CASE
                WHEN i.stock_quantity = 0                  THEN 'OUT OF STOCK'
                WHEN i.stock_quantity <= i.low_stock_alert THEN 'LOW'
                ELSE 'OK'
            END AS stock_status
        FROM ingredients i
        LEFT JOIN units u ON i.unit_id = u.unit_id
        WHERE i.stock_quantity <= i.low_stock_alert
        ORDER BY i.stock_quantity ASC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ============================================================
// AVAILABLE YEARS
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

// ============================================================
// INGREDIENT CONSUMPTION REPORT (new)
// Shows how much of each ingredient was consumed over a date range
// based on completed orders, versus actual stock movement.
// ============================================================
function rpt_ingredient_consumption(PDO $pdo, string $start, string $end): array
{
    $stmt = $pdo->prepare("
        SELECT
            i.ingredient_name,
            u.unit_code,
            u.unit_label,
            i.stock_quantity                                            AS current_stock,
            COALESCE(SUM(r.quantity_required * oi.quantity), 0)        AS theoretical_consumed,
            i.low_stock_alert
        FROM ingredients i
        LEFT JOIN units u    ON i.unit_id        = u.unit_id
        LEFT JOIN recipes r  ON i.ingredient_id  = r.ingredient_id
        LEFT JOIN order_items oi ON r.item_id    = oi.item_id
        LEFT JOIN orders o   ON oi.order_id      = o.order_id
                             AND o.status        = 'completed'
                             AND DATE(o.completed_at) BETWEEN :start AND :end
        GROUP BY i.ingredient_id, i.ingredient_name,
                 u.unit_code, u.unit_label,
                 i.stock_quantity, i.low_stock_alert
        HAVING theoretical_consumed > 0
        ORDER BY theoretical_consumed DESC
    ");
    $stmt->execute([':start' => $start, ':end' => $end]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ============================================================
// PERIOD COMPARISON (new)
// Compares two date ranges side by side — useful for
// "this week vs last week", "this month vs last month"
// ============================================================
function rpt_period_comparison(PDO $pdo, string $cur_start, string $cur_end, string $prev_start, string $prev_end): array
{
    $fetch = function(PDO $pdo, string $start, string $end) {
        $stmt = $pdo->prepare("
            SELECT
                COUNT(order_id)                                         AS total_orders,
                COALESCE(SUM(total_amount), 0)                          AS net_revenue,
                COALESCE(SUM(total_amount / 1.12), 0)                   AS net_revenue_excl_vat,
                COALESCE(SUM(discount), 0)                              AS total_discounts,
                COALESCE(AVG(total_amount), 0)                          AS avg_order_value,
                (SELECT COUNT(*) FROM orders
                 WHERE status = 'cancelled'
                   AND DATE(created_at) BETWEEN :s2 AND :e2)            AS cancelled_orders
            FROM orders
            WHERE status = 'completed'
              AND DATE(completed_at) BETWEEN :start AND :end
        ");
        $stmt->execute([':start' => $start, ':end' => $end, ':s2' => $start, ':e2' => $end]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    };

    $current  = $fetch($pdo, $cur_start,  $cur_end);
    $previous = $fetch($pdo, $prev_start, $prev_end);

    // Calculate % change for each metric
    $change = [];
    foreach ($current as $key => $val) {
        $prev = (float) ($previous[$key] ?? 0);
        $curr = (float) $val;
        $change[$key] = $prev > 0 ? round((($curr - $prev) / $prev) * 100, 1) : ($curr > 0 ? 100 : 0);
    }

    return [
        'current'  => $current,
        'previous' => $previous,
        'change'   => $change,
        'periods'  => [
            'current'  => ['start' => $cur_start,  'end' => $cur_end],
            'previous' => ['start' => $prev_start, 'end' => $prev_end],
        ],
    ];
}