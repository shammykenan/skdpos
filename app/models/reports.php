<?php
/**
 * reports_model.php – v10 (pay-first accurate)
 *
 * ═══════════════════════════════════════════════════════════════════
 * SYSTEM FACTS (pay-first cafeteria)
 * ═══════════════════════════════════════════════════════════════════
 *
 * - Payment is collected BEFORE the kitchen prepares the order.
 * - Order status at payment time = 'pending'.
 * - Order status progresses: pending → preparing → ready → completed.
 *   Revenue is earned when PAID, not when completed.
 *   Therefore ALL paid orders (pending/preparing/ready/completed) must
 *   be included in revenue queries, NOT just 'completed'.
 *
 * - orders.total_amount is ALREADY REDUCED in real-time when items are
 *   voided or cancelled (cancel_items.php recalculates it).
 *   So SUM(o.total_amount) on paid orders naturally = net sold amount.
 *
 * ═══════════════════════════════════════════════════════════════════
 * FORMULA CHAIN
 * ═══════════════════════════════════════════════════════════════════
 *
 *  Gross Revenue  = SUM(o.total_amount) WHERE status IN (active statuses)
 *                   AND payment exists  [already excludes voided/cancelled items]
 *
 *  Total Refunds  = SUM(refunds.amount) scoped to the period AND
 *                   scoped to the ORDER's cashier (not the refund processor)
 *                   — so admin-processed refunds still reduce the cashier's total
 *
 *  Net Revenue    = Gross Revenue − Total Refunds  (floor 0)
 *
 *  COGS           = SUM(cost_price × qty + addon_cost) for active items
 *                   NOT cancelled, NOT voided, NOT individually refunded
 *                   Same order status scope as Gross Revenue
 *
 *  Gross Profit   = Net Revenue − COGS
 *
 *  Waste Cost     = SUM(oi.waste_cost) WHERE oi.status='voided'
 *                   AND oi.waste_cost IS NOT NULL
 *                   (NULL = item was pending when voided, no ingredients used)
 *
 *  Net Profit     = Gross Profit − Waste Cost
 *
 * ═══════════════════════════════════════════════════════════════════
 * REFUND SCOPING RULE
 * ═══════════════════════════════════════════════════════════════════
 *
 *  Refunds are always scoped by the ORDER's cashier_id, NOT by
 *  refunds.processed_by. This is correct because:
 *  - The cashier collected the cash originally.
 *  - If an admin processes a refund on the cashier's order, the cash
 *    comes out of THAT cashier's drawer/register session.
 *  - The cashier's cash_to_turn_over must be reduced by those refunds.
 *  - refunds.processed_by tells us WHO approved it (audit trail only).
 *
 * ═══════════════════════════════════════════════════════════════════
 * ACTIVE ORDER STATUSES (used in all revenue/COGS queries)
 * ═══════════════════════════════════════════════════════════════════
 *
 *  'pending', 'preparing', 'ready', 'completed'
 *  (excludes 'draft', 'cancelled', 'voided')
 */

// ─────────────────────────────────────────────────────────────────────────────
// CONSTANT: active order statuses for revenue counting
// ─────────────────────────────────────────────────────────────────────────────
define('RPT_ACTIVE_STATUSES', "'pending','preparing','ready','completed'");

// ─────────────────────────────────────────────────────────────────────────────
// SHARED COGS + WASTE HELPER
//
// $date_filter  : SQL fragment referencing table alias 'o' (orders table)
//                 All param names MUST start with :df_
//                 e.g. "DATE(o.created_at) = :df_date"
//                      "YEAR(o.created_at) = :df_year AND MONTH(o.created_at) = :df_month"
// $params       : array of [':df_xxx' => value, ...]
//
// Returns: [float $cogs, float $waste]
// ─────────────────────────────────────────────────────────────────────────────
function _rpt_cogs_and_waste(PDO $pdo, string $date_filter, array $params): array
{
    // ── COGS ─────────────────────────────────────────────────────────────────
    // Items that were SOLD and KEPT (not cancelled, not voided, not refunded).
    // Same status scope as gross revenue — all active paid orders.
    $cogs_stmt = $pdo->prepare("
        SELECT COALESCE(SUM(
            (COALESCE(mv.cost_price, mi.cost_price) * oi.quantity)
            + COALESCE(ac.addon_cost, 0)
        ), 0)
        FROM order_items oi
        JOIN orders     o  ON oi.order_id  = o.order_id
        JOIN menu_items mi ON oi.item_id   = mi.item_id
        LEFT JOIN menu_variants mv ON oi.variant_id = mv.variant_id
        LEFT JOIN (
            SELECT oia.order_item_id,
                   COALESCE(SUM(a.price * oia.quantity), 0) AS addon_cost
            FROM order_item_addons oia
            JOIN addons a ON oia.addon_id = a.addon_id
            GROUP BY oia.order_item_id
        ) ac ON oi.order_item_id = ac.order_item_id
        WHERE o.status IN (" . RPT_ACTIVE_STATUSES . ")
          AND $date_filter
          AND EXISTS (
              SELECT 1 FROM payments px
              WHERE px.order_id = o.order_id AND px.amount_paid > 0
          )
          AND oi.status NOT IN ('cancelled', 'voided')
          AND oi.order_item_id NOT IN (
              SELECT r.order_item_id
              FROM refunds r
              WHERE r.order_item_id IS NOT NULL
                AND r.order_id = oi.order_id
          )
    ");
    $cogs_stmt->execute($params);
    $cogs = (float) $cogs_stmt->fetchColumn();

    // ── WASTE ─────────────────────────────────────────────────────────────────
    // waste_cost IS NOT NULL  → item was preparing/ready when voided, cost recorded.
    // waste_cost IS NULL      → item was still pending, no ingredients used, no waste.
    // We do NOT join payments here — waste is an item-level fact independent
    // of payment structure. We only confirm the order was a real paid order.
    $waste_stmt = $pdo->prepare("
        SELECT COALESCE(SUM(oi.waste_cost), 0)
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.order_id
        WHERE oi.status     = 'voided'
          AND oi.waste_cost IS NOT NULL
          AND $date_filter
          AND EXISTS (
              SELECT 1 FROM payments px
              WHERE px.order_id = o.order_id AND px.amount_paid > 0
          )
    ");
    $waste_stmt->execute($params);
    $waste = (float) $waste_stmt->fetchColumn();

    return [$cogs, $waste];
}

// ─────────────────────────────────────────────────────────────────────────────
// DAILY SUMMARY
// ─────────────────────────────────────────────────────────────────────────────
function rpt_daily_summary(PDO $pdo, string $date, ?int $cashier_id = null): array
{
    $cid_and = $cashier_id !== null ? 'AND o.cashier_id = :cid' : '';

    // ── Gross Revenue + order counts ──────────────────────────────────────────
    // Use SUM(o.total_amount) — already adjusted for voided/cancelled items.
    // Include ALL active statuses, not just 'completed'.
    $stmt = $pdo->prepare("
        SELECT
            COUNT(DISTINCT o.order_id)                                        AS total_orders,
            COALESCE(SUM(o.total_amount), 0)                                  AS gross_revenue,
            COALESCE(SUM(o.discount), 0)                                      AS total_discounts,
            COALESCE(SUM(o.total_amount) / NULLIF(COUNT(DISTINCT o.order_id), 0), 0) AS avg_order_value,
            COUNT(DISTINCT o.cashier_id)                                      AS active_cashiers,
            COALESCE(SUM(o.total_amount / 1.12), 0)                           AS net_revenue_excl_vat,
            COALESCE(SUM(o.total_amount - o.total_amount / 1.12), 0)          AS total_vat_collected,
            (
                SELECT COUNT(*) FROM orders cx
                WHERE cx.status = 'cancelled'
                  AND DATE(cx.created_at) = :cx_date
                  " . ($cashier_id !== null ? 'AND cx.cashier_id = :cx_cid' : '') . "
            ) AS cancelled_orders
        FROM orders o
        WHERE o.status IN (" . RPT_ACTIVE_STATUSES . ")
          AND DATE(o.created_at) = :date
          $cid_and
          AND EXISTS (
              SELECT 1 FROM payments px
              WHERE px.order_id = o.order_id AND px.amount_paid > 0
          )
    ");
    $p = [':date' => $date, ':cx_date' => $date];
    if ($cashier_id !== null) { $p[':cid'] = $cashier_id; $p[':cx_cid'] = $cashier_id; }
    $stmt->execute($p);
    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $row = array_merge([
        'total_orders'         => 0, 'gross_revenue'       => 0,
        'total_discounts'      => 0, 'net_revenue'         => 0,
        'net_revenue_excl_vat' => 0, 'total_vat_collected' => 0,
        'avg_order_value'      => 0, 'gross_profit'        => 0,
        'total_waste_cost'     => 0, 'total_refunds'       => 0,
        'active_cashiers'      => 0, 'cancelled_orders'    => 0,
        'net_profit'           => 0,
    ], $row);

    // ── Refunds scoped by ORDER's cashier (not processed_by) ─────────────────
    // If admin refunds a cashier's order, it reduces THAT cashier's net revenue.
    $r_cid_and = $cashier_id !== null ? 'AND o.cashier_id = :r_cid' : '';
    $r_p       = [':r_date' => $date];
    if ($cashier_id !== null) $r_p[':r_cid'] = $cashier_id;
    $rstmt = $pdo->prepare("
        SELECT COALESCE(SUM(r.amount), 0)
        FROM refunds r
        JOIN orders o ON r.order_id = o.order_id
        WHERE DATE(r.created_at) = :r_date
          $r_cid_and
    ");
    $rstmt->execute($r_p);
    $row['total_refunds'] = (float) $rstmt->fetchColumn();

    // Net Revenue = Gross − Refunds (floor 0)
    $row['net_revenue'] = max(0.0, (float) $row['gross_revenue'] - $row['total_refunds']);

    // ── COGS & Waste ──────────────────────────────────────────────────────────
    $cid_part    = $cashier_id !== null ? ' AND o.cashier_id = :df_cid' : '';
    $date_filter = "DATE(o.created_at) = :df_date$cid_part";
    $df_params   = [':df_date' => $date];
    if ($cashier_id !== null) $df_params[':df_cid'] = $cashier_id;

    [$cogs, $waste] = _rpt_cogs_and_waste($pdo, $date_filter, $df_params);

    $row['gross_profit']     = (float) $row['net_revenue'] - $cogs;
    $row['total_waste_cost'] = $waste;
    $row['net_profit']       = $row['gross_profit'] - $waste;

    return $row;
}

// ─────────────────────────────────────────────────────────────────────────────
// CLOSE-OUT  (cashier + admin shift)
// ─────────────────────────────────────────────────────────────────────────────
function rpt_closeout(PDO $pdo, string $date, int $cashier_id): array
{
    // Sales summary for this cashier's paid orders on this date
    $stmt = $pdo->prepare("
        SELECT
            u.full_name                                                         AS cashier_name,
            u.username,
            COUNT(DISTINCT o.order_id)                                          AS total_orders,
            COALESCE(SUM(o.subtotal), 0)                                        AS gross_sales,
            COALESCE(SUM(o.discount), 0)                                        AS total_discounts,
            COALESCE(SUM(o.total_amount), 0)                                    AS net_sales,
            COALESCE(SUM(o.total_amount / 1.12), 0)                             AS net_sales_excl_vat,
            COALESCE(SUM(o.total_amount - o.total_amount / 1.12), 0)            AS vat_collected,
            (
                SELECT COUNT(*) FROM orders cx
                WHERE cx.cashier_id       = :cid_c
                  AND DATE(cx.created_at) = :date_c
                  AND cx.status           = 'cancelled'
            ) AS cancelled_orders
        FROM users u
        LEFT JOIN orders o
               ON  o.cashier_id       = u.user_id
               AND o.status IN (" . RPT_ACTIVE_STATUSES . ")
               AND DATE(o.created_at) = :date
               AND EXISTS (
                   SELECT 1 FROM payments px
                   WHERE px.order_id = o.order_id AND px.amount_paid > 0
               )
        WHERE u.user_id = :cid
        GROUP BY u.user_id, u.full_name, u.username
    ");
    $stmt->execute([':date' => $date, ':cid' => $cashier_id,
                    ':cid_c' => $cashier_id, ':date_c' => $date]);
    $summary = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    // Cash collected through this cashier's payment records
    $stmt2 = $pdo->prepare("
        SELECT
            COALESCE(SUM(p.amount_paid), 0)                   AS cash_received,
            COALESCE(SUM(p.change_amount), 0)                 AS change_given,
            COALESCE(SUM(p.amount_paid - p.change_amount), 0) AS cash_collected,
            COUNT(p.payment_id)                               AS payment_count,
            SUM(CASE WHEN p.processed_by IS NOT NULL
                      AND p.processed_by != p.cashier_id
                     THEN 1 ELSE 0 END)                       AS admin_processed_count
        FROM payments p
        WHERE p.cashier_id     = :cid
          AND DATE(p.paid_at)  = :date
          AND p.payment_status = 'paid'
    ");
    $stmt2->execute([':cid' => $cashier_id, ':date' => $date]);
    $cash = $stmt2->fetch(PDO::FETCH_ASSOC) ?: [];

    // Refunds on THIS CASHIER's orders (scoped by order.cashier_id, NOT processed_by)
    // This is correct: if admin refunds cashier's order, cash comes from cashier's drawer.
    $stmt3 = $pdo->prepare("
        SELECT COALESCE(SUM(r.amount), 0)
        FROM refunds r
        JOIN orders o ON r.order_id = o.order_id
        WHERE o.cashier_id        = :cid
          AND DATE(r.created_at)  = :date
    ");
    $stmt3->execute([':cid' => $cashier_id, ':date' => $date]);
    $refunds = (float) $stmt3->fetchColumn();

    // Cash to turn over = cash collected − refunds paid back to customers
    $cash_collected    = (float) ($cash['cash_collected'] ?? 0);
    $cash_to_turn_over = max(0.0, $cash_collected - $refunds);

    // Waste on this cashier's voided items today
    $stmt4 = $pdo->prepare("
        SELECT COALESCE(SUM(oi.waste_cost), 0)
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.order_id
        WHERE oi.status          = 'voided'
          AND oi.waste_cost      IS NOT NULL
          AND o.cashier_id       = :cid
          AND DATE(o.created_at) = :date
    ");
    $stmt4->execute([':cid' => $cashier_id, ':date' => $date]);
    $waste = (float) $stmt4->fetchColumn();

    // Top 5 items this shift
    $stmt5 = $pdo->prepare("
        SELECT
            COALESCE(oi.item_name_snapshot, mi.item_name) AS item_name,
            oi.variant_name_snapshot                       AS variant_name,
            SUM(oi.quantity)                               AS units_sold,
            COALESCE(SUM(oi.subtotal), 0)                  AS total_revenue
        FROM order_items oi
        JOIN orders     o  ON oi.order_id = o.order_id
        JOIN menu_items mi ON oi.item_id  = mi.item_id
        WHERE o.cashier_id       = :cid
          AND DATE(o.created_at) = :date
          AND o.status IN (" . RPT_ACTIVE_STATUSES . ")
          AND oi.status NOT IN ('cancelled', 'voided')
          AND EXISTS (
              SELECT 1 FROM payments px
              WHERE px.order_id = o.order_id AND px.amount_paid > 0
          )
        GROUP BY oi.item_id, oi.variant_id,
                 oi.item_name_snapshot, mi.item_name, oi.variant_name_snapshot
        ORDER BY units_sold DESC
        LIMIT 5
    ");
    $stmt5->execute([':cid' => $cashier_id, ':date' => $date]);
    $top_items = $stmt5->fetchAll(PDO::FETCH_ASSOC);

    // Orders by hour this shift
    $stmt6 = $pdo->prepare("
        SELECT
            HOUR(o.created_at)               AS hour_of_day,
            COUNT(DISTINCT o.order_id)       AS total_orders,
            COALESCE(SUM(o.total_amount), 0) AS revenue
        FROM orders o
        WHERE o.cashier_id       = :cid
          AND DATE(o.created_at) = :date
          AND o.status IN (" . RPT_ACTIVE_STATUSES . ")
          AND EXISTS (
              SELECT 1 FROM payments px
              WHERE px.order_id = o.order_id AND px.amount_paid > 0
          )
        GROUP BY HOUR(o.created_at)
        ORDER BY hour_of_day
    ");
    $stmt6->execute([':cid' => $cashier_id, ':date' => $date]);
    $peak_hours = $stmt6->fetchAll(PDO::FETCH_ASSOC);

    $merged = array_merge($summary, $cash);
    $merged['total_refunds']           = $refunds;
    $merged['cash_to_turn_over']       = $cash_to_turn_over;
    $merged['total_waste_cost']        = $waste;
    $merged['net_sales_after_refunds'] = max(0.0, (float)($merged['net_sales'] ?? 0) - $refunds);
    $merged['top_items']               = $top_items;
    $merged['peak_hours']              = $peak_hours;

    return $merged;
}

// ─────────────────────────────────────────────────────────────────────────────
// MONTHLY SUMMARY
// ─────────────────────────────────────────────────────────────────────────────
function rpt_monthly_summary(PDO $pdo, int $year, int $month): array
{
    $stmt = $pdo->prepare("
        SELECT
            COUNT(DISTINCT o.order_id)                                        AS total_orders,
            COALESCE(SUM(o.total_amount), 0)                                  AS gross_revenue,
            COALESCE(SUM(o.discount), 0)                                      AS total_discounts,
            COALESCE(SUM(o.total_amount) / NULLIF(COUNT(DISTINCT o.order_id), 0), 0) AS avg_order_value,
            COUNT(DISTINCT o.cashier_id)                                      AS active_cashiers,
            COALESCE(SUM(o.total_amount / 1.12), 0)                           AS net_revenue_excl_vat,
            COALESCE(SUM(o.total_amount - o.total_amount / 1.12), 0)          AS total_vat_collected
        FROM orders o
        WHERE o.status IN (" . RPT_ACTIVE_STATUSES . ")
          AND YEAR(o.created_at)  = :year
          AND MONTH(o.created_at) = :month
          AND EXISTS (
              SELECT 1 FROM payments px
              WHERE px.order_id = o.order_id AND px.amount_paid > 0
          )
    ");
    $stmt->execute([':year' => $year, ':month' => $month]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    $row = array_merge([
        'total_orders'         => 0, 'gross_revenue'    => 0,
        'total_discounts'      => 0, 'net_revenue'      => 0,
        'net_revenue_excl_vat' => 0, 'total_vat_collected' => 0,
        'avg_order_value'      => 0, 'gross_profit'     => 0,
        'total_waste_cost'     => 0, 'total_refunds'    => 0,
        'active_cashiers'      => 0, 'net_profit'       => 0,
    ], $row);

    // Refunds scoped by order's cashier (join to orders)
    $rstmt = $pdo->prepare("
        SELECT COALESCE(SUM(r.amount), 0)
        FROM refunds r
        JOIN orders o ON r.order_id = o.order_id
        WHERE YEAR(r.created_at)  = :year
          AND MONTH(r.created_at) = :month
    ");
    $rstmt->execute([':year' => $year, ':month' => $month]);
    $row['total_refunds'] = (float) $rstmt->fetchColumn();
    $row['net_revenue']   = max(0.0, (float) $row['gross_revenue'] - $row['total_refunds']);

    $date_filter = 'YEAR(o.created_at) = :df_year AND MONTH(o.created_at) = :df_month';
    $df_params   = [':df_year' => $year, ':df_month' => $month];
    [$cogs, $waste] = _rpt_cogs_and_waste($pdo, $date_filter, $df_params);

    $row['gross_profit']     = (float) $row['net_revenue'] - $cogs;
    $row['total_waste_cost'] = $waste;
    $row['net_profit']       = $row['gross_profit'] - $waste;

    return $row;
}

// ─────────────────────────────────────────────────────────────────────────────
// DAILY BREAKDOWN  (day-by-day chart for monthly view)
// ─────────────────────────────────────────────────────────────────────────────
function rpt_daily_breakdown(PDO $pdo, int $year, int $month): array
{
    $stmt = $pdo->prepare("
        SELECT
            DATE(o.created_at)                                       AS sale_date,
            COUNT(DISTINCT o.order_id)                               AS total_orders,
            COALESCE(SUM(o.total_amount), 0)                         AS revenue,
            COALESCE(SUM(o.total_amount / 1.12), 0)                  AS revenue_excl_vat,
            COALESCE(SUM(o.total_amount - o.total_amount / 1.12), 0) AS vat_amount
        FROM orders o
        WHERE o.status IN (" . RPT_ACTIVE_STATUSES . ")
          AND YEAR(o.created_at)  = :year
          AND MONTH(o.created_at) = :month
          AND EXISTS (
              SELECT 1 FROM payments px
              WHERE px.order_id = o.order_id AND px.amount_paid > 0
          )
        GROUP BY DATE(o.created_at)
        ORDER BY sale_date
    ");
    $stmt->execute([':year' => $year, ':month' => $month]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ─────────────────────────────────────────────────────────────────────────────
// YEARLY SUMMARY
// ─────────────────────────────────────────────────────────────────────────────
function rpt_yearly_summary(PDO $pdo, int $year): array
{
    $stmt = $pdo->prepare("
        SELECT
            COUNT(DISTINCT o.order_id)                                        AS total_orders,
            COALESCE(SUM(o.total_amount), 0)                                  AS gross_revenue,
            COALESCE(SUM(o.discount), 0)                                      AS total_discounts,
            COALESCE(SUM(o.total_amount) / NULLIF(COUNT(DISTINCT o.order_id), 0), 0) AS avg_order_value,
            COALESCE(SUM(o.total_amount / 1.12), 0)                           AS net_revenue_excl_vat,
            COALESCE(SUM(o.total_amount - o.total_amount / 1.12), 0)          AS total_vat_collected
        FROM orders o
        WHERE o.status IN (" . RPT_ACTIVE_STATUSES . ")
          AND YEAR(o.created_at) = :year
          AND EXISTS (
              SELECT 1 FROM payments px
              WHERE px.order_id = o.order_id AND px.amount_paid > 0
          )
    ");
    $stmt->execute([':year' => $year]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    $row = array_merge([
        'total_orders'         => 0, 'gross_revenue'    => 0,
        'total_discounts'      => 0, 'net_revenue'      => 0,
        'net_revenue_excl_vat' => 0, 'total_vat_collected' => 0,
        'avg_order_value'      => 0, 'gross_profit'     => 0,
        'total_waste_cost'     => 0, 'total_refunds'    => 0,
        'net_profit'           => 0,
    ], $row);

    $rstmt = $pdo->prepare("
        SELECT COALESCE(SUM(r.amount), 0)
        FROM refunds r
        WHERE YEAR(r.created_at) = :year
    ");
    $rstmt->execute([':year' => $year]);
    $row['total_refunds'] = (float) $rstmt->fetchColumn();
    $row['net_revenue']   = max(0.0, (float) $row['gross_revenue'] - $row['total_refunds']);

    $date_filter = 'YEAR(o.created_at) = :df_year';
    $df_params   = [':df_year' => $year];
    [$cogs, $waste] = _rpt_cogs_and_waste($pdo, $date_filter, $df_params);

    $row['gross_profit']     = (float) $row['net_revenue'] - $cogs;
    $row['total_waste_cost'] = $waste;
    $row['net_profit']       = $row['gross_profit'] - $waste;

    // Monthly bar chart data
    $mstmt = $pdo->prepare("
        SELECT MONTH(o.created_at) AS month_num,
               COALESCE(SUM(o.total_amount), 0) AS monthly_revenue
        FROM orders o
        WHERE o.status IN (" . RPT_ACTIVE_STATUSES . ")
          AND YEAR(o.created_at) = :year
          AND EXISTS (
              SELECT 1 FROM payments px
              WHERE px.order_id = o.order_id AND px.amount_paid > 0
          )
        GROUP BY MONTH(o.created_at)
    ");
    $mstmt->execute([':year' => $year]);
    $mdata = [];
    foreach ($mstmt->fetchAll(PDO::FETCH_ASSOC) as $mr) {
        $mdata[(int) $mr['month_num']] = (float) $mr['monthly_revenue'];
    }
    $keys = [1=>'jan',2=>'feb',3=>'mar',4=>'apr',5=>'may',6=>'jun',
             7=>'jul',8=>'aug',9=>'sep',10=>'oct_m',11=>'nov',12=>'dec_m'];
    foreach ($keys as $mn => $key) {
        $row[$key] = $mdata[$mn] ?? 0;
    }

    return $row;
}

// ─────────────────────────────────────────────────────────────────────────────
// TOP ITEMS
// ─────────────────────────────────────────────────────────────────────────────
function rpt_top_items(PDO $pdo, string $start, string $end, int $limit = 10): array
{
    $stmt = $pdo->prepare("
        SELECT
            mi.item_id,
            mi.item_name,
            c.category_name,
            SUM(oi.quantity)                                                         AS units_sold,
            COALESCE(SUM(oi.subtotal), 0)                                            AS total_revenue,
            COALESCE(SUM((oi.unit_price - COALESCE(mv.cost_price, mi.cost_price))
                         * oi.quantity), 0)                                          AS gross_profit,
            COALESCE(SUM(oi.subtotal / 1.12), 0)                                     AS revenue_excl_vat,
            COALESCE(SUM(oi.subtotal - oi.subtotal / 1.12), 0)                       AS vat_amount,
            MAX(CASE WHEN oi.variant_id IS NOT NULL THEN 1 ELSE 0 END)               AS has_variants
        FROM order_items oi
        JOIN menu_items mi   ON oi.item_id     = mi.item_id
        JOIN categories c    ON mi.category_id = c.category_id
        JOIN orders o        ON oi.order_id    = o.order_id
        LEFT JOIN menu_variants mv ON oi.variant_id = mv.variant_id
        WHERE o.status IN (" . RPT_ACTIVE_STATUSES . ")
          AND DATE(o.created_at) BETWEEN :start AND :end
          AND oi.status NOT IN ('cancelled', 'voided')
          AND EXISTS (
              SELECT 1 FROM payments px
              WHERE px.order_id = o.order_id AND px.amount_paid > 0
          )
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

// ─────────────────────────────────────────────────────────────────────────────
// WASTE REPORT  (full void log — Waste tab)
// ─────────────────────────────────────────────────────────────────────────────
function rpt_waste_report(PDO $pdo, string $start, string $end, ?int $cashier_id = null): array
{
    $cid_and = $cashier_id !== null ? 'AND o.cashier_id = :cid' : '';
    $stmt = $pdo->prepare("
        SELECT
            o.order_id,
            o.order_number,
            DATE(o.created_at)                             AS void_date,
            u.full_name                                    AS cashier_name,
            oi.order_item_id,
            COALESCE(oi.item_name_snapshot, mi.item_name)  AS item_name,
            oi.variant_name_snapshot                       AS variant_name,
            oi.quantity,
            COALESCE(mv.cost_price, mi.cost_price)         AS unit_cost,
            oi.waste_cost,
            oi.void_reason,
            o.order_type
        FROM order_items oi
        JOIN orders     o  ON oi.order_id  = o.order_id
        JOIN users      u  ON o.cashier_id = u.user_id
        JOIN menu_items mi ON oi.item_id   = mi.item_id
        LEFT JOIN menu_variants mv ON oi.variant_id = mv.variant_id
        WHERE oi.status     = 'voided'
          AND oi.waste_cost IS NOT NULL
          AND DATE(o.created_at) BETWEEN :start AND :end
          $cid_and
        ORDER BY o.created_at DESC, oi.order_item_id ASC
    ");
    $params = [':start' => $start, ':end' => $end];
    if ($cashier_id !== null) $params[':cid'] = $cashier_id;
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ─────────────────────────────────────────────────────────────────────────────
// WASTE SUMMARY  (Waste tab summary cards)
// ─────────────────────────────────────────────────────────────────────────────
function rpt_waste_summary(PDO $pdo, string $start, string $end, ?int $cashier_id = null): array
{
    $cid_and = $cashier_id !== null ? 'AND o.cashier_id = :cid' : '';
    $params  = [':start' => $start, ':end' => $end];
    if ($cashier_id !== null) $params[':cid'] = $cashier_id;

    $stmt = $pdo->prepare("
        SELECT
            COUNT(DISTINCT o.order_id)      AS voided_orders,
            COUNT(oi.order_item_id)         AS voided_items,
            COALESCE(SUM(oi.waste_cost), 0) AS total_waste_cost,
            COALESCE(AVG(oi.waste_cost), 0) AS avg_waste_per_item
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.order_id
        WHERE oi.status     = 'voided'
          AND oi.waste_cost IS NOT NULL
          AND DATE(o.created_at) BETWEEN :start AND :end
          $cid_and
    ");
    $stmt->execute($params);
    $summary = $stmt->fetch(PDO::FETCH_ASSOC) ?: [
        'voided_orders' => 0, 'voided_items' => 0,
        'total_waste_cost' => 0, 'avg_waste_per_item' => 0,
    ];

    // Top wasted items
    $topStmt = $pdo->prepare("
        SELECT
            COALESCE(oi.item_name_snapshot, mi.item_name) AS item_name,
            oi.variant_name_snapshot                       AS variant_name,
            SUM(oi.quantity)                               AS total_qty_wasted,
            COALESCE(SUM(oi.waste_cost), 0)                AS total_waste_cost
        FROM order_items oi
        JOIN orders     o  ON oi.order_id  = o.order_id
        JOIN menu_items mi ON oi.item_id   = mi.item_id
        WHERE oi.status     = 'voided'
          AND oi.waste_cost IS NOT NULL
          AND DATE(o.created_at) BETWEEN :start AND :end
          $cid_and
        GROUP BY oi.item_id, oi.variant_id,
                 oi.item_name_snapshot, mi.item_name, oi.variant_name_snapshot
        ORDER BY total_waste_cost DESC
        LIMIT 5
    ");
    $topStmt->execute($params);
    $summary['top_wasted_items'] = $topStmt->fetchAll(PDO::FETCH_ASSOC);

    // Waste by reason
    $rStmt = $pdo->prepare("
        SELECT
            COALESCE(oi.void_reason, 'No reason given') AS reason,
            COUNT(oi.order_item_id)                      AS item_count,
            COALESCE(SUM(oi.waste_cost), 0)              AS waste_cost
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.order_id
        WHERE oi.status     = 'voided'
          AND oi.waste_cost IS NOT NULL
          AND DATE(o.created_at) BETWEEN :start AND :end
          $cid_and
        GROUP BY oi.void_reason
        ORDER BY waste_cost DESC
    ");
    $rStmt->execute($params);
    $summary['waste_by_reason'] = $rStmt->fetchAll(PDO::FETCH_ASSOC);

    return $summary;
}

// ─────────────────────────────────────────────────────────────────────────────
// REVENUE BY CATEGORY
// ─────────────────────────────────────────────────────────────────────────────
function rpt_sales_by_category(PDO $pdo, string $start, string $end): array
{
    $stmt = $pdo->prepare("
        SELECT
            c.category_name,
            SUM(oi.quantity)                                        AS units_sold,
            COALESCE(SUM(oi.subtotal), 0)                           AS revenue,
            COALESCE(SUM(oi.subtotal / 1.12), 0)                    AS revenue_excl_vat,
            COALESCE(SUM(oi.subtotal - oi.subtotal / 1.12), 0)      AS vat_amount
        FROM order_items oi
        JOIN menu_items mi ON oi.item_id     = mi.item_id
        JOIN categories c  ON mi.category_id = c.category_id
        JOIN orders o      ON oi.order_id    = o.order_id
        WHERE o.status IN (" . RPT_ACTIVE_STATUSES . ")
          AND DATE(o.created_at) BETWEEN :start AND :end
          AND oi.status NOT IN ('cancelled', 'voided')
          AND EXISTS (
              SELECT 1 FROM payments px
              WHERE px.order_id = o.order_id AND px.amount_paid > 0
          )
        GROUP BY c.category_id, c.category_name
        ORDER BY revenue DESC
    ");
    $stmt->execute([':start' => $start, ':end' => $end]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ─────────────────────────────────────────────────────────────────────────────
// CASHIER PERFORMANCE
// ─────────────────────────────────────────────────────────────────────────────
function rpt_cashier_performance(PDO $pdo, string $start, string $end): array
{
    $stmt = $pdo->prepare("
        SELECT
            u.user_id,
            u.full_name                                        AS cashier,
            u.username,
            COALESCE(c.orders_handled,    0)                   AS orders_handled,
            COALESCE(c.total_sales,       0)                   AS total_sales,
            COALESCE(c.total_sales / 1.12, 0)                  AS total_sales_excl_vat,
            COALESCE(c.total_sales - c.total_sales / 1.12, 0)  AS vat_collected,
            COALESCE(c.avg_order_value,   0)                   AS avg_order_value,
            COALESCE(c.discounts_given,   0)                   AS discounts_given,
            COALESCE(x.cancelled_orders,  0)                   AS cancelled_orders,
            COALESCE(p.admin_processed,   0)                   AS admin_processed_count,
            COALESCE(w.total_waste,       0)                   AS total_waste_cost
        FROM users u
        LEFT JOIN (
            SELECT cashier_id,
                   COUNT(DISTINCT order_id) AS orders_handled,
                   SUM(total_amount)        AS total_sales,
                   AVG(total_amount)        AS avg_order_value,
                   SUM(discount)            AS discounts_given
            FROM orders
            WHERE status IN (" . RPT_ACTIVE_STATUSES . ")
              AND DATE(created_at) BETWEEN :s1 AND :e1
              AND EXISTS (
                  SELECT 1 FROM payments px
                  WHERE px.order_id = orders.order_id AND px.amount_paid > 0
              )
            GROUP BY cashier_id
        ) c ON u.user_id = c.cashier_id
        LEFT JOIN (
            SELECT cashier_id, COUNT(order_id) AS cancelled_orders
            FROM orders
            WHERE status = 'cancelled'
              AND DATE(created_at) BETWEEN :s2 AND :e2
            GROUP BY cashier_id
        ) x ON u.user_id = x.cashier_id
        LEFT JOIN (
            SELECT cashier_id, COUNT(*) AS admin_processed
            FROM payments
            WHERE payment_status = 'paid'
              AND processed_by IS NOT NULL
              AND processed_by != cashier_id
              AND DATE(paid_at) BETWEEN :s3 AND :e3
            GROUP BY cashier_id
        ) p ON u.user_id = p.cashier_id
        LEFT JOIN (
            SELECT o.cashier_id, COALESCE(SUM(oi.waste_cost), 0) AS total_waste
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.order_id
            WHERE oi.status     = 'voided'
              AND oi.waste_cost IS NOT NULL
              AND DATE(o.created_at) BETWEEN :s4 AND :e4
            GROUP BY o.cashier_id
        ) w ON u.user_id = w.cashier_id
        WHERE u.role IN ('cashier', 'admin')
          AND u.is_active = 1
          AND (c.cashier_id IS NOT NULL OR x.cashier_id IS NOT NULL)
        ORDER BY total_sales DESC
    ");
    $stmt->execute([
        ':s1' => $start, ':e1' => $end,
        ':s2' => $start, ':e2' => $end,
        ':s3' => $start, ':e3' => $end,
        ':s4' => $start, ':e4' => $end,
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ─────────────────────────────────────────────────────────────────────────────
// PEAK HOURS
// ─────────────────────────────────────────────────────────────────────────────
function rpt_peak_hours(PDO $pdo, string $start, string $end): array
{
    $stmt = $pdo->prepare("
        SELECT
            HOUR(o.created_at)                                       AS hour_of_day,
            COUNT(DISTINCT o.order_id)                               AS total_orders,
            COALESCE(SUM(o.total_amount), 0)                         AS revenue,
            COALESCE(SUM(o.total_amount / 1.12), 0)                  AS revenue_excl_vat
        FROM orders o
        WHERE o.status IN (" . RPT_ACTIVE_STATUSES . ")
          AND DATE(o.created_at) BETWEEN :start AND :end
          AND EXISTS (
              SELECT 1 FROM payments px
              WHERE px.order_id = o.order_id AND px.amount_paid > 0
          )
        GROUP BY HOUR(o.created_at)
        ORDER BY hour_of_day
    ");
    $stmt->execute([':start' => $start, ':end' => $end]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ─────────────────────────────────────────────────────────────────────────────
// ORDER TYPE BREAKDOWN
// ─────────────────────────────────────────────────────────────────────────────
function rpt_order_type_breakdown(PDO $pdo, string $start, string $end): array
{
    $stmt = $pdo->prepare("
        SELECT
            o.order_type,
            o.order_source,
            COUNT(DISTINCT o.order_id)               AS total_orders,
            COALESCE(SUM(o.total_amount), 0)          AS revenue,
            COALESCE(SUM(o.total_amount / 1.12), 0)   AS revenue_excl_vat
        FROM orders o
        WHERE o.status IN (" . RPT_ACTIVE_STATUSES . ")
          AND DATE(o.created_at) BETWEEN :start AND :end
          AND EXISTS (
              SELECT 1 FROM payments px
              WHERE px.order_id = o.order_id AND px.amount_paid > 0
          )
        GROUP BY o.order_type, o.order_source
        ORDER BY total_orders DESC
    ");
    $stmt->execute([':start' => $start, ':end' => $end]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ─────────────────────────────────────────────────────────────────────────────
// TOP ADD-ONS
// ─────────────────────────────────────────────────────────────────────────────
function rpt_top_addons(PDO $pdo, string $start, string $end): array
{
    $stmt = $pdo->prepare("
        SELECT
            a.addon_name,
            SUM(oia.quantity)                          AS times_ordered,
            COALESCE(SUM(oia.price * oia.quantity), 0) AS total_revenue
        FROM order_item_addons oia
        JOIN addons      a ON oia.addon_id       = a.addon_id
        JOIN order_items oi ON oia.order_item_id = oi.order_item_id
        JOIN orders      o ON oi.order_id        = o.order_id
        WHERE o.status IN (" . RPT_ACTIVE_STATUSES . ")
          AND DATE(o.created_at) BETWEEN :start AND :end
          AND oi.status NOT IN ('cancelled', 'voided')
          AND EXISTS (
              SELECT 1 FROM payments px
              WHERE px.order_id = o.order_id AND px.amount_paid > 0
          )
        GROUP BY a.addon_id, a.addon_name
        ORDER BY total_revenue DESC
        LIMIT 10
    ");
    $stmt->execute([':start' => $start, ':end' => $end]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ─────────────────────────────────────────────────────────────────────────────
// DISCOUNT & CANCELLATION AUDIT
// ─────────────────────────────────────────────────────────────────────────────
function rpt_discount_audit(PDO $pdo, string $start, string $end): array
{
    $stmt = $pdo->prepare("
        SELECT
            o.order_number,
            u.full_name                                      AS cashier,
            o.subtotal,
            o.discount,
            o.total_amount,
            ROUND(o.total_amount / 1.12, 2)                  AS amount_excl_vat,
            ROUND(o.total_amount - o.total_amount / 1.12, 2) AS vat_amount,
            o.status,
            o.customer_name,
            o.created_at,
            o.completed_at
        FROM orders o
        JOIN users u ON o.cashier_id = u.user_id
        WHERE (
            (   o.discount > 0
                AND o.status IN (" . RPT_ACTIVE_STATUSES . ")
                AND DATE(o.created_at) BETWEEN :s1 AND :e1
                AND EXISTS (
                    SELECT 1 FROM payments px
                    WHERE px.order_id = o.order_id AND px.amount_paid > 0
                )
            )
            OR
            (   o.status = 'cancelled'
                AND DATE(o.created_at) BETWEEN :s2 AND :e2
            )
        )
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([':s1' => $start, ':e1' => $end, ':s2' => $start, ':e2' => $end]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ─────────────────────────────────────────────────────────────────────────────
// PAYMENT RECONCILIATION
// ─────────────────────────────────────────────────────────────────────────────
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
            SUM(CASE WHEN p.processed_by IS NOT NULL
                      AND p.processed_by != p.cashier_id
                     THEN 1 ELSE 0 END)                                  AS admin_processed_count
        FROM payments p
        JOIN users  u ON p.cashier_id = u.user_id
        JOIN orders o ON p.order_id   = o.order_id
              AND o.status IN (" . RPT_ACTIVE_STATUSES . ")
        WHERE p.payment_status = 'paid'
          AND DATE(p.paid_at) BETWEEN :start AND :end
        GROUP BY DATE(p.paid_at), u.user_id, u.full_name
        ORDER BY pay_date DESC, u.full_name
    ");
    $stmt->execute([':start' => $start, ':end' => $end]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ─────────────────────────────────────────────────────────────────────────────
// LOW STOCK
// ─────────────────────────────────────────────────────────────────────────────
function rpt_low_stock(PDO $pdo): array
{
    $stmt = $pdo->query("
        SELECT i.ingredient_id, i.ingredient_name,
               i.stock_quantity, i.low_stock_alert, i.last_updated,
               u.unit_code AS unit, u.unit_label,
               CASE WHEN i.stock_quantity = 0                  THEN 'OUT OF STOCK'
                    WHEN i.stock_quantity <= i.low_stock_alert THEN 'LOW'
                    ELSE 'OK' END AS stock_status
        FROM ingredients i
        LEFT JOIN units u ON i.unit_id = u.unit_id
        WHERE i.stock_quantity <= i.low_stock_alert
        ORDER BY i.stock_quantity ASC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ─────────────────────────────────────────────────────────────────────────────
// AVAILABLE YEARS
// ─────────────────────────────────────────────────────────────────────────────
function rpt_available_years(PDO $pdo): array
{
    $stmt = $pdo->query("
        SELECT DISTINCT YEAR(created_at) AS yr
        FROM orders
        WHERE status IN (" . RPT_ACTIVE_STATUSES . ")
          AND EXISTS (
              SELECT 1 FROM payments px
              WHERE px.order_id = orders.order_id AND px.amount_paid > 0
          )
        ORDER BY yr DESC
    ");
    return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'yr');
}

// ─────────────────────────────────────────────────────────────────────────────
// INGREDIENT CONSUMPTION
// ─────────────────────────────────────────────────────────────────────────────
function rpt_ingredient_consumption(PDO $pdo, string $start, string $end): array
{
    $stmt = $pdo->prepare("
        SELECT
            i.ingredient_name,
            u.unit_code, u.unit_label,
            i.stock_quantity  AS current_stock,
            i.low_stock_alert,
            COALESCE(SUM(
                COALESCE(r_var.quantity_required, r_base.quantity_required, 0) * oi.quantity
            ), 0) AS theoretical_consumed,
            SUM(CASE WHEN r_var.recipe_id IS NOT NULL THEN oi.quantity ELSE 0 END) AS units_via_variant_recipe,
            SUM(CASE WHEN r_var.recipe_id IS NULL
                      AND r_base.recipe_id IS NOT NULL THEN oi.quantity ELSE 0 END) AS units_via_base_recipe
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.order_id
                     AND o.status IN ('preparing', 'ready', 'completed')
                     AND DATE(o.created_at) BETWEEN :start AND :end
                     AND EXISTS (
                         SELECT 1 FROM payments px
                         WHERE px.order_id = o.order_id AND px.amount_paid > 0
                     )
        LEFT JOIN recipes r_var  ON r_var.item_id    = oi.item_id AND r_var.variant_id  = oi.variant_id
        LEFT JOIN recipes r_base ON r_base.item_id   = oi.item_id AND r_base.variant_id IS NULL
        JOIN ingredients i ON i.ingredient_id = COALESCE(r_var.ingredient_id, r_base.ingredient_id)
        LEFT JOIN units u ON i.unit_id = u.unit_id
        WHERE oi.status NOT IN ('cancelled', 'voided')
        GROUP BY i.ingredient_id, i.ingredient_name,
                 u.unit_code, u.unit_label, i.stock_quantity, i.low_stock_alert
        HAVING theoretical_consumed > 0
        ORDER BY theoretical_consumed DESC
    ");
    $stmt->execute([':start' => $start, ':end' => $end]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ─────────────────────────────────────────────────────────────────────────────
// PERIOD COMPARISON
// ─────────────────────────────────────────────────────────────────────────────
function rpt_period_comparison(PDO $pdo, string $cur_start, string $cur_end,
                                         string $prev_start, string $prev_end): array
{
    $fetch = function (PDO $pdo, string $start, string $end): array {
        $stmt = $pdo->prepare("
            SELECT
                COUNT(DISTINCT o.order_id)       AS total_orders,
                COALESCE(SUM(o.total_amount), 0) AS gross_revenue,
                COALESCE(SUM(o.discount), 0)     AS total_discounts,
                COALESCE(SUM(o.total_amount) / NULLIF(COUNT(DISTINCT o.order_id), 0), 0) AS avg_order_value,
                (SELECT COUNT(*) FROM orders
                 WHERE status = 'cancelled'
                   AND DATE(created_at) BETWEEN :s2 AND :e2) AS cancelled_orders
            FROM orders o
            WHERE o.status IN (" . RPT_ACTIVE_STATUSES . ")
              AND DATE(o.created_at) BETWEEN :start AND :end
              AND EXISTS (
                  SELECT 1 FROM payments px
                  WHERE px.order_id = o.order_id AND px.amount_paid > 0
              )
        ");
        $stmt->execute([':start' => $start, ':end' => $end, ':s2' => $start, ':e2' => $end]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        // Refunds for the period
        $rstmt = $pdo->prepare("
            SELECT COALESCE(SUM(r.amount), 0)
            FROM refunds r
            WHERE DATE(r.created_at) BETWEEN :start AND :end
        ");
        $rstmt->execute([':start' => $start, ':end' => $end]);
        $refunds = (float) $rstmt->fetchColumn();

        $gross_revenue = (float) ($row['gross_revenue'] ?? 0);
        $net_revenue   = max(0.0, $gross_revenue - $refunds);

        // COGS & Waste for accurate profit comparison
        $date_filter = 'DATE(o.created_at) BETWEEN :df_start AND :df_end';
        $df_params   = [':df_start' => $start, ':df_end' => $end];
        [$cogs, $waste] = _rpt_cogs_and_waste($pdo, $date_filter, $df_params);

        $gross_profit = $net_revenue - $cogs;
        $net_profit   = $gross_profit - $waste;

        return array_merge([
            'total_orders'     => 0, 'gross_revenue'   => 0,
            'net_revenue'      => 0, 'total_discounts' => 0,
            'avg_order_value'  => 0, 'total_refunds'   => 0,
            'gross_profit'     => 0, 'net_profit'      => 0,
            'cancelled_orders' => 0,
        ], $row, [
            'net_revenue'  => $net_revenue,
            'total_refunds'=> $refunds,
            'gross_profit' => $gross_profit,
            'net_profit'   => $net_profit,
        ]);
    };

    $current  = $fetch($pdo, $cur_start,  $cur_end);
    $previous = $fetch($pdo, $prev_start, $prev_end);

    $change = [];
    foreach ($current as $key => $val) {
        $prev         = (float) ($previous[$key] ?? 0);
        $curr         = (float) $val;
        $change[$key] = $prev > 0 ? round(($curr - $prev) / $prev * 100, 1)
                                  : ($curr > 0 ? 100.0 : 0.0);
    }

    return [
        'current'  => $current,
        'previous' => $previous,
        'change'   => $change,
        'periods'  => [
            'current'  => ['start' => $cur_start, 'end' => $cur_end],
            'previous' => ['start' => $prev_start, 'end' => $prev_end],
        ],
    ];
}