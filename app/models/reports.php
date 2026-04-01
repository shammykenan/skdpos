<?php
/**
 * reports_model.php  – v6 (waste-aware)
 *
 * KEY RULES & FORMULAS:
 *  - Revenue = orders.total_amount (recalculated after items cancelled/voided to exclude them)
 *  - COGS = SUM((unit_price - cost_price) * qty) for sold items (status NOT IN 'cancelled','voided')
 *  - Gross Profit = Revenue - COGS (always positive if calculated on sold items only)
 *  - Waste = SUM(waste_cost) for voided items only (NULL for cancelled items)
 *  - Net Profit = Gross Profit - Waste (after accounting for spoilage/waste)
 *  - VAT = total_amount - (total_amount / 1.12) [12% inclusive VAT, separate from profit]
 *
 * ITEM STATUS LOGIC:
 *  - "cancelled" items: were pending (never prepared) → no waste recorded (waste_cost = NULL)
 *  - "voided" items:    were ready (prepared) → waste cost recorded including addon costs
 *
 * IMPORTANT: orders.total_amount is dynamically updated when items are voided/cancelled.
 * This ensures Revenue is always calculated from sold items only, never from deleted items.
 */

// ============================================================
// HELPERS
// ============================================================

function vat_exclusive(float $total): float { return $total / 1.12; }
function vat_amount(float $total): float    { return $total - vat_exclusive($total); }

// ============================================================
// DAILY REVENUE SUMMARY  (waste + net_profit added)
// ============================================================
function rpt_daily_summary(PDO $pdo, string $date, ?int $cashier_id = null): array
{
    $cid_clause  = $cashier_id !== null ? 'AND o.cashier_id = :cid'   : '';
    $cid_clause2 = $cashier_id !== null ? 'AND cashier_id   = :cid_w' : '';

    $stmt = $pdo->prepare("
        SELECT
            COUNT(o.order_id)                                        AS total_orders,
            COALESCE(SUM(o.subtotal), 0)                             AS gross_revenue,
            COALESCE(SUM(o.discount), 0)                             AS total_discounts,
            COALESCE(SUM(o.total_amount), 0)                         AS net_revenue,
            COALESCE(SUM(o.total_amount / 1.12), 0)                  AS net_revenue_excl_vat,
            COALESCE(SUM(o.total_amount - o.total_amount / 1.12), 0) AS total_vat_collected,
            COALESCE(AVG(o.total_amount), 0)                         AS avg_order_value,
            COUNT(DISTINCT o.cashier_id)                             AS active_cashiers,
            /* gross_profit = revenue − COGS on completed items */
            COALESCE(SUM(
                (oi_c.unit_price - COALESCE(mv_c.cost_price, mi_c.cost_price))
                * oi_c.quantity
            ), 0)                                                    AS gross_profit,
            (
                SELECT COUNT(*)
                FROM orders c2
                WHERE DATE(c2.created_at) = :date_c
                  AND c2.status = 'cancelled'
                  $cid_clause
            ) AS cancelled_orders,
            /* waste = sum of ingredient cost for voided items on this date */
            (
                SELECT COALESCE(SUM(oi_w.waste_cost), 0)
                FROM order_items oi_w
                JOIN orders o_w ON oi_w.order_id = o_w.order_id
                WHERE oi_w.status    = 'voided'
                  AND oi_w.waste_cost IS NOT NULL
                  AND DATE(o_w.created_at) = :date_w
                  $cid_clause2
            ) AS total_waste_cost
        FROM orders o
        LEFT JOIN order_items oi_c
            ON  oi_c.order_id = o.order_id
            AND oi_c.status NOT IN ('cancelled', 'voided')
        LEFT JOIN menu_items    mi_c ON oi_c.item_id    = mi_c.item_id
        LEFT JOIN menu_variants mv_c ON oi_c.variant_id = mv_c.variant_id
        WHERE o.status = 'completed'
          AND DATE(o.completed_at) = :date
          $cid_clause
    ");

    $params = [':date' => $date, ':date_c' => $date, ':date_w' => $date];
    if ($cashier_id !== null) {
        $params[':cid']   = $cashier_id;
        $params[':cid_w'] = $cashier_id;
    }

    $stmt->execute($params);
    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    // net_profit = gross_profit − waste
    $row['net_profit'] = round(
        (float)($row['gross_profit'] ?? 0) - (float)($row['total_waste_cost'] ?? 0),
        2
    );

    return $row;
}

// ============================================================
// CLOSE-OUT  (waste + net_profit added)
// ============================================================
function rpt_closeout(PDO $pdo, string $date, int $cashier_id): array
{
    $stmt = $pdo->prepare("
        SELECT
            u.full_name                                              AS cashier_name,
            u.username,
            COUNT(o.order_id)                                        AS total_orders,
            COALESCE(SUM(o.subtotal), 0)                             AS gross_sales,
            COALESCE(SUM(o.discount), 0)                             AS total_discounts,
            COALESCE(SUM(o.total_amount), 0)                         AS net_sales,
            COALESCE(SUM(o.total_amount / 1.12), 0)                  AS net_sales_excl_vat,
            COALESCE(SUM(o.total_amount - o.total_amount / 1.12), 0) AS vat_collected,
            (
                SELECT COUNT(*)
                FROM orders cx
                WHERE cx.cashier_id       = :cid_c
                  AND DATE(cx.created_at) = :date_c
                  AND cx.status           = 'cancelled'
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
        ':date'   => $date,        ':cid'    => $cashier_id,
        ':cid_c'  => $cashier_id,  ':date_c' => $date,
    ]);
    $summary = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    // Cash reconciliation
    $stmt2 = $pdo->prepare("
        SELECT
            COALESCE(SUM(p.amount_paid), 0)                   AS cash_received,
            COALESCE(SUM(p.change_amount), 0)                 AS change_given,
            COALESCE(SUM(p.amount_paid - p.change_amount), 0) AS cash_to_turn_over,
            COUNT(p.payment_id)                               AS payment_count,
            SUM(
                CASE WHEN p.processed_by IS NOT NULL
                      AND p.processed_by != p.cashier_id
                     THEN 1 ELSE 0 END
            )                                                 AS admin_processed_count
        FROM payments p
        WHERE p.cashier_id     = :cid
          AND DATE(p.paid_at)  = :date
          AND p.payment_status = 'paid'
    ");
    $stmt2->execute([':cid' => $cashier_id, ':date' => $date]);
    $cash = $stmt2->fetch(PDO::FETCH_ASSOC) ?: [];

    // Waste: voided items on this cashier's orders for this date
    $stmt3 = $pdo->prepare("
        SELECT COALESCE(SUM(oi.waste_cost), 0) AS total_waste_cost
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.order_id
        WHERE oi.status      = 'voided'
          AND oi.waste_cost   IS NOT NULL
          AND o.cashier_id   = :cid
          AND DATE(o.created_at) = :date
    ");
    $stmt3->execute([':cid' => $cashier_id, ':date' => $date]);
    $waste = (float) $stmt3->fetchColumn();

    $merged                    = array_merge($summary, $cash);
    $merged['total_waste_cost'] = $waste;

    return $merged;
}

// ============================================================
// MONTHLY SUMMARY  (waste + net_profit added)
// ============================================================
function rpt_monthly_summary(PDO $pdo, int $year, int $month): array
{
    $stmt = $pdo->prepare("
        SELECT
            COUNT(o.order_id)                                        AS total_orders,
            COALESCE(SUM(o.subtotal), 0)                             AS gross_revenue,
            COALESCE(SUM(o.discount), 0)                             AS total_discounts,
            COALESCE(SUM(o.total_amount), 0)                         AS net_revenue,
            COALESCE(SUM(o.total_amount / 1.12), 0)                  AS net_revenue_excl_vat,
            COALESCE(SUM(o.total_amount - o.total_amount / 1.12), 0) AS total_vat_collected,
            COALESCE(AVG(o.total_amount), 0)                         AS avg_order_value,
            COUNT(DISTINCT o.cashier_id)                             AS active_cashiers,
            COALESCE(SUM(
                (oi_c.unit_price - COALESCE(mv_c.cost_price, mi_c.cost_price))
                * oi_c.quantity
            ), 0)                                                    AS gross_profit,
            (
                SELECT COUNT(*)
                FROM orders cx
                WHERE cx.status           = 'cancelled'
                  AND YEAR(cx.created_at)  = :year_c
                  AND MONTH(cx.created_at) = :month_c
            ) AS cancelled_orders,
            (
                SELECT COALESCE(SUM(oi_w.waste_cost), 0)
                FROM order_items oi_w
                JOIN orders o_w ON oi_w.order_id = o_w.order_id
                WHERE oi_w.status      = 'voided'
                  AND oi_w.waste_cost   IS NOT NULL
                  AND YEAR(o_w.created_at)  = :year_w
                  AND MONTH(o_w.created_at) = :month_w
            ) AS total_waste_cost
        FROM orders o
        LEFT JOIN order_items oi_c
            ON  oi_c.order_id = o.order_id
            AND oi_c.status NOT IN ('cancelled', 'voided')
        LEFT JOIN menu_items    mi_c ON oi_c.item_id    = mi_c.item_id
        LEFT JOIN menu_variants mv_c ON oi_c.variant_id = mv_c.variant_id
        WHERE o.status = 'completed'
          AND YEAR(o.completed_at)  = :year
          AND MONTH(o.completed_at) = :month
    ");
    $stmt->execute([
        ':year'    => $year,  ':month'   => $month,
        ':year_c'  => $year,  ':month_c' => $month,
        ':year_w'  => $year,  ':month_w' => $month,
    ]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    $row['net_profit'] = round(
        (float)($row['gross_profit'] ?? 0) - (float)($row['total_waste_cost'] ?? 0),
        2
    );
    return $row;
}

// ============================================================
// DAILY BREAKDOWN  (day-by-day chart within a month)
// ============================================================
function rpt_daily_breakdown(PDO $pdo, int $year, int $month): array
{
    $stmt = $pdo->prepare("
        SELECT
            DATE(o.completed_at)                                      AS sale_date,
            COUNT(o.order_id)                                         AS total_orders,
            COALESCE(SUM(o.total_amount), 0)                          AS revenue,
            COALESCE(SUM(o.total_amount / 1.12), 0)                   AS revenue_excl_vat,
            COALESCE(SUM(o.total_amount - o.total_amount / 1.12), 0)  AS vat_amount
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
// YEARLY SUMMARY  (waste + net_profit added)
// ============================================================
function rpt_yearly_summary(PDO $pdo, int $year): array
{
    $stmt = $pdo->prepare("
        SELECT
            COUNT(o.order_id)                                                        AS total_orders,
            COALESCE(SUM(o.subtotal), 0)                                             AS gross_revenue,
            COALESCE(SUM(o.discount), 0)                                             AS total_discounts,
            COALESCE(SUM(o.total_amount), 0)                                         AS net_revenue,
            COALESCE(SUM(o.total_amount / 1.12), 0)                                  AS net_revenue_excl_vat,
            COALESCE(SUM(o.total_amount - o.total_amount / 1.12), 0)                 AS total_vat_collected,
            COALESCE(AVG(o.total_amount), 0)                                         AS avg_order_value,
            COALESCE(SUM(
                (oi_c.unit_price - COALESCE(mv_c.cost_price, mi_c.cost_price))
                * oi_c.quantity
            ), 0)                                                                    AS gross_profit,
            SUM(CASE WHEN MONTH(o.completed_at)=1  THEN o.total_amount ELSE 0 END)  AS jan,
            SUM(CASE WHEN MONTH(o.completed_at)=2  THEN o.total_amount ELSE 0 END)  AS feb,
            SUM(CASE WHEN MONTH(o.completed_at)=3  THEN o.total_amount ELSE 0 END)  AS mar,
            SUM(CASE WHEN MONTH(o.completed_at)=4  THEN o.total_amount ELSE 0 END)  AS apr,
            SUM(CASE WHEN MONTH(o.completed_at)=5  THEN o.total_amount ELSE 0 END)  AS may,
            SUM(CASE WHEN MONTH(o.completed_at)=6  THEN o.total_amount ELSE 0 END)  AS jun,
            SUM(CASE WHEN MONTH(o.completed_at)=7  THEN o.total_amount ELSE 0 END)  AS jul,
            SUM(CASE WHEN MONTH(o.completed_at)=8  THEN o.total_amount ELSE 0 END)  AS aug,
            SUM(CASE WHEN MONTH(o.completed_at)=9  THEN o.total_amount ELSE 0 END)  AS sep,
            SUM(CASE WHEN MONTH(o.completed_at)=10 THEN o.total_amount ELSE 0 END)  AS oct_m,
            SUM(CASE WHEN MONTH(o.completed_at)=11 THEN o.total_amount ELSE 0 END)  AS nov,
            SUM(CASE WHEN MONTH(o.completed_at)=12 THEN o.total_amount ELSE 0 END)  AS dec_m
        FROM orders o
        LEFT JOIN order_items oi_c
            ON  oi_c.order_id = o.order_id
            AND oi_c.status NOT IN ('cancelled', 'voided')
        LEFT JOIN menu_items    mi_c ON oi_c.item_id    = mi_c.item_id
        LEFT JOIN menu_variants mv_c ON oi_c.variant_id = mv_c.variant_id
        WHERE o.status = 'completed'
          AND YEAR(o.completed_at) = :year
    ");
    $stmt->execute([':year' => $year]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    // Yearly waste
    $wstmt = $pdo->prepare("
        SELECT COALESCE(SUM(oi.waste_cost), 0)
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.order_id
        WHERE oi.status = 'voided'
          AND oi.waste_cost IS NOT NULL
          AND YEAR(o.created_at) = :year
    ");
    $wstmt->execute([':year' => $year]);
    $row['total_waste_cost'] = (float) $wstmt->fetchColumn();
    $row['net_profit']       = round(
        (float)($row['gross_profit'] ?? 0) - $row['total_waste_cost'],
        2
    );
    return $row;
}

// ============================================================
// TOP SELLING ITEMS  — variant-aware, gross_profit only (no waste per item)
// ============================================================
function rpt_top_items(PDO $pdo, string $start, string $end, int $limit = 10): array
{
    $stmt = $pdo->prepare("
        SELECT
            mi.item_id,
            mi.item_name,
            c.category_name,
            SUM(oi.quantity)                                                              AS units_sold,
            COALESCE(SUM(oi.subtotal), 0)                                                 AS total_revenue,
            COALESCE(SUM(
                (oi.unit_price - COALESCE(mv.cost_price, mi.cost_price)) * oi.quantity
            ), 0)                                                                         AS gross_profit,
            COALESCE(SUM(oi.subtotal / 1.12), 0)                                          AS revenue_excl_vat,
            COALESCE(SUM(oi.subtotal - oi.subtotal / 1.12), 0)                            AS vat_amount,
            MAX(CASE WHEN oi.variant_id IS NOT NULL THEN 1 ELSE 0 END)                   AS has_variants
        FROM order_items oi
        JOIN menu_items mi   ON oi.item_id     = mi.item_id
        JOIN categories c    ON mi.category_id = c.category_id
        JOIN orders o        ON oi.order_id    = o.order_id
        LEFT JOIN menu_variants mv ON oi.variant_id = mv.variant_id
        WHERE o.status = 'completed'
          AND DATE(o.completed_at) BETWEEN :start AND :end
          AND oi.status NOT IN ('cancelled', 'voided')
        GROUP BY mi.item_id, mi.item_name, c.category_name
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
// WASTE REPORT  — full detail, scoped per role
//
// $cashier_id = null  → admin sees ALL voided items across all cashiers
// $cashier_id = int   → cashier sees only their own orders' voided items
// ============================================================
function rpt_waste_report(PDO $pdo, string $start, string $end, ?int $cashier_id = null): array
{
    $cid_clause = $cashier_id !== null ? 'AND o.cashier_id = :cid' : '';

    $stmt = $pdo->prepare("
        SELECT
            o.order_id,
            o.order_number,
            DATE(o.created_at)                                        AS void_date,
            o.created_at                                              AS order_created_at,
            u.full_name                                               AS cashier_name,
            oi.order_item_id,
            COALESCE(oi.item_name_snapshot, mi.item_name)             AS item_name,
            oi.variant_name_snapshot                                  AS variant_name,
            oi.quantity,
            COALESCE(mv.cost_price, mi.cost_price)                    AS unit_cost,
            oi.waste_cost,
            oi.void_reason,
            o.order_type
        FROM order_items oi
        JOIN orders o      ON oi.order_id  = o.order_id
        JOIN users u       ON o.cashier_id = u.user_id
        JOIN menu_items mi ON oi.item_id   = mi.item_id
        LEFT JOIN menu_variants mv ON oi.variant_id = mv.variant_id
        WHERE oi.status      = 'voided'
          AND oi.waste_cost   IS NOT NULL
          AND DATE(o.created_at) BETWEEN :start AND :end
          $cid_clause
        ORDER BY o.created_at DESC, oi.order_item_id ASC
    ");
    $params = [':start' => $start, ':end' => $end];
    if ($cashier_id !== null) $params[':cid'] = $cashier_id;
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ============================================================
// WASTE SUMMARY  — aggregated for summary cards
// ============================================================
function rpt_waste_summary(PDO $pdo, string $start, string $end, ?int $cashier_id = null): array
{
    $cid_clause = $cashier_id !== null ? 'AND o.cashier_id = :cid' : '';

    $stmt = $pdo->prepare("
        SELECT
            COUNT(DISTINCT o.order_id)           AS voided_orders,
            COUNT(oi.order_item_id)              AS voided_items,
            COALESCE(SUM(oi.waste_cost), 0)      AS total_waste_cost,
            COALESCE(AVG(oi.waste_cost), 0)      AS avg_waste_per_item
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.order_id
        WHERE oi.status      = 'voided'
          AND oi.waste_cost   IS NOT NULL
          AND DATE(o.created_at) BETWEEN :start AND :end
          $cid_clause
    ");
    $params = [':start' => $start, ':end' => $end];
    if ($cashier_id !== null) $params[':cid'] = $cashier_id;
    $stmt->execute($params);
    $summary = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    // Top wasted items
    $topStmt = $pdo->prepare("
        SELECT
            COALESCE(oi.item_name_snapshot, mi.item_name) AS item_name,
            oi.variant_name_snapshot                       AS variant_name,
            SUM(oi.quantity)                               AS total_qty_wasted,
            COALESCE(SUM(oi.waste_cost), 0)                AS total_waste_cost
        FROM order_items oi
        JOIN orders o      ON oi.order_id  = o.order_id
        JOIN menu_items mi ON oi.item_id   = mi.item_id
        WHERE oi.status      = 'voided'
          AND oi.waste_cost   IS NOT NULL
          AND DATE(o.created_at) BETWEEN :start AND :end
          $cid_clause
        GROUP BY oi.item_id, oi.variant_id, oi.item_name_snapshot, mi.item_name, oi.variant_name_snapshot
        ORDER BY total_waste_cost DESC
        LIMIT 5
    ");
    $topStmt->execute($params);
    $summary['top_wasted_items'] = $topStmt->fetchAll(PDO::FETCH_ASSOC);

    // Waste by reason
    $reasonStmt = $pdo->prepare("
        SELECT
            COALESCE(oi.void_reason, 'No reason given') AS reason,
            COUNT(oi.order_item_id)                      AS item_count,
            COALESCE(SUM(oi.waste_cost), 0)              AS waste_cost
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.order_id
        WHERE oi.status      = 'voided'
          AND oi.waste_cost   IS NOT NULL
          AND DATE(o.created_at) BETWEEN :start AND :end
          $cid_clause
        GROUP BY oi.void_reason
        ORDER BY waste_cost DESC
    ");
    $reasonStmt->execute($params);
    $summary['waste_by_reason'] = $reasonStmt->fetchAll(PDO::FETCH_ASSOC);

    return $summary;
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
          AND oi.status NOT IN ('cancelled', 'voided')
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
            u.full_name                                       AS cashier,
            u.username,
            COALESCE(c.orders_handled,   0)                   AS orders_handled,
            COALESCE(c.total_sales,      0)                   AS total_sales,
            COALESCE(c.total_sales / 1.12, 0)                 AS total_sales_excl_vat,
            COALESCE(c.total_sales - c.total_sales / 1.12, 0) AS vat_collected,
            COALESCE(c.avg_order_value,  0)                   AS avg_order_value,
            COALESCE(c.discounts_given,  0)                   AS discounts_given,
            COALESCE(x.cancelled_orders, 0)                   AS cancelled_orders,
            COALESCE(p.admin_processed,  0)                   AS admin_processed_count,
            COALESCE(w.total_waste,      0)                   AS total_waste_cost
        FROM users u

        LEFT JOIN (
            SELECT
                cashier_id,
                COUNT(order_id)   AS orders_handled,
                SUM(total_amount) AS total_sales,
                AVG(total_amount) AS avg_order_value,
                SUM(discount)     AS discounts_given
            FROM orders
            WHERE status = 'completed'
              AND DATE(completed_at) BETWEEN :start AND :end
            GROUP BY cashier_id
        ) c ON u.user_id = c.cashier_id

        LEFT JOIN (
            SELECT cashier_id, COUNT(order_id) AS cancelled_orders
            FROM orders
            WHERE status = 'cancelled'
              AND DATE(created_at) BETWEEN :start2 AND :end2
            GROUP BY cashier_id
        ) x ON u.user_id = x.cashier_id

        LEFT JOIN (
            SELECT cashier_id, COUNT(*) AS admin_processed
            FROM payments
            WHERE payment_status = 'paid'
              AND processed_by IS NOT NULL
              AND processed_by != cashier_id
              AND DATE(paid_at) BETWEEN :start3 AND :end3
            GROUP BY cashier_id
        ) p ON u.user_id = p.cashier_id

        LEFT JOIN (
            SELECT o.cashier_id, COALESCE(SUM(oi.waste_cost), 0) AS total_waste
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.order_id
            WHERE oi.status = 'voided'
              AND oi.waste_cost IS NOT NULL
              AND DATE(o.created_at) BETWEEN :start4 AND :end4
            GROUP BY o.cashier_id
        ) w ON u.user_id = w.cashier_id

        WHERE u.role IN ('cashier', 'admin')
          AND u.is_active = 1
          AND (c.cashier_id IS NOT NULL OR x.cashier_id IS NOT NULL)

        ORDER BY total_sales DESC
    ");
    $stmt->execute([
        ':start'  => $start, ':end'  => $end,
        ':start2' => $start, ':end2' => $end,
        ':start3' => $start, ':end3' => $end,
        ':start4' => $start, ':end4' => $end,
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
            HOUR(o.completed_at)                                      AS hour_of_day,
            COUNT(o.order_id)                                         AS total_orders,
            COALESCE(SUM(o.total_amount), 0)                          AS revenue,
            COALESCE(SUM(o.total_amount / 1.12), 0)                   AS revenue_excl_vat
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
            COUNT(o.order_id)                                         AS total_orders,
            COALESCE(SUM(o.total_amount), 0)                          AS revenue,
            COALESCE(SUM(o.total_amount / 1.12), 0)                   AS revenue_excl_vat
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
            SUM(oia.quantity)                          AS times_ordered,
            COALESCE(SUM(oia.price * oia.quantity), 0) AS total_revenue
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
            u.full_name                                         AS cashier,
            o.subtotal,
            o.discount,
            o.total_amount,
            ROUND(o.total_amount / 1.12, 2)                     AS amount_excl_vat,
            ROUND(o.total_amount - o.total_amount / 1.12, 2)    AS vat_amount,
            o.status,
            o.customer_name,
            o.created_at,
            o.completed_at
        FROM orders o
        JOIN users u ON o.cashier_id = u.user_id
        WHERE (
            (   o.discount > 0
                AND o.status = 'completed'
                AND DATE(o.completed_at) BETWEEN :start AND :end
            )
            OR
            (   o.status = 'cancelled'
                AND DATE(o.created_at) BETWEEN :start2 AND :end2
            )
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
// ============================================================
function rpt_payment_reconciliation(PDO $pdo, string $start, string $end): array
{
    $stmt = $pdo->prepare("
        SELECT
            DATE(p.paid_at)                                              AS pay_date,
            u.full_name                                                  AS cashier,
            COUNT(p.payment_id)                                          AS transactions,
            COALESCE(SUM(p.amount_paid), 0)                              AS cash_received,
            COALESCE(SUM(p.change_amount), 0)                            AS change_given,
            COALESCE(SUM(p.amount_paid - p.change_amount), 0)            AS net_cash,
            COALESCE(SUM(o.total_amount), 0)                             AS expected_from_orders,
            COALESCE(SUM(o.total_amount / 1.12), 0)                      AS expected_excl_vat,
            COALESCE(SUM(o.total_amount - o.total_amount / 1.12), 0)     AS vat_in_expected,
            COALESCE(SUM(p.amount_paid - p.change_amount), 0)
                - COALESCE(SUM(o.total_amount), 0)                       AS variance,
            SUM(
                CASE WHEN p.processed_by IS NOT NULL
                      AND p.processed_by != p.cashier_id
                     THEN 1 ELSE 0 END
            )                                                            AS admin_processed_count
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
            i.ingredient_id,
            i.ingredient_name,
            i.stock_quantity,
            i.low_stock_alert,
            i.last_updated,
            u.unit_code  AS unit,
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
// INGREDIENT CONSUMPTION REPORT  — variant-aware
// ============================================================
function rpt_ingredient_consumption(PDO $pdo, string $start, string $end): array
{
    $stmt = $pdo->prepare("
        SELECT
            i.ingredient_name,
            u.unit_code,
            u.unit_label,
            i.stock_quantity                                                  AS current_stock,
            i.low_stock_alert,
            COALESCE(SUM(
                COALESCE(r_var.quantity_required, r_base.quantity_required, 0)
                * oi.quantity
            ), 0)                                                              AS theoretical_consumed,
            SUM(CASE WHEN r_var.recipe_id  IS NOT NULL THEN oi.quantity ELSE 0 END) AS units_via_variant_recipe,
            SUM(CASE WHEN r_var.recipe_id  IS NULL
                      AND r_base.recipe_id IS NOT NULL THEN oi.quantity ELSE 0 END) AS units_via_base_recipe
        FROM order_items oi
        JOIN orders o          ON oi.order_id       = o.order_id
                               AND o.status         = 'completed'
                               AND DATE(o.completed_at) BETWEEN :start AND :end
        LEFT JOIN recipes r_var
            ON  r_var.item_id    = oi.item_id
            AND r_var.variant_id = oi.variant_id
        LEFT JOIN recipes r_base
            ON  r_base.item_id    = oi.item_id
            AND r_base.variant_id IS NULL
        JOIN ingredients i
            ON i.ingredient_id = COALESCE(r_var.ingredient_id, r_base.ingredient_id)
        LEFT JOIN units u ON i.unit_id = u.unit_id
        WHERE oi.status NOT IN ('cancelled', 'voided')
        GROUP BY
            i.ingredient_id, i.ingredient_name,
            u.unit_code, u.unit_label,
            i.stock_quantity, i.low_stock_alert
        HAVING theoretical_consumed > 0
        ORDER BY theoretical_consumed DESC
    ");
    $stmt->execute([':start' => $start, ':end' => $end]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ============================================================
// PERIOD COMPARISON
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
                (
                    SELECT COUNT(*) FROM orders
                    WHERE status = 'cancelled'
                      AND DATE(created_at) BETWEEN :s2 AND :e2
                )                                                       AS cancelled_orders
            FROM orders
            WHERE status = 'completed'
              AND DATE(completed_at) BETWEEN :start AND :end
        ");
        $stmt->execute([':start' => $start, ':end' => $end, ':s2' => $start, ':e2' => $end]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    };

    $current  = $fetch($pdo, $cur_start,  $cur_end);
    $previous = $fetch($pdo, $prev_start, $prev_end);

    $change = [];
    foreach ($current as $key => $val) {
        $prev = (float)($previous[$key] ?? 0);
        $curr = (float)$val;
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