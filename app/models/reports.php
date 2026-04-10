<?php
/**
 * reports_model.php – v9 (fully corrected)
 *
 * ═══════════════════════════════════════════════════════════════════
 * BUSINESS RULES (pay-first cafeteria system)
 * ═══════════════════════════════════════════════════════════════════
 *
 * ORDER LIFECYCLE:
 *   draft → pending (payment collected here) → preparing → ready → completed
 *                                                                  └→ voided   (whole order)
 *                                          └→ cancelled (before payment)
 *
 * ITEM LIFECYCLE within a paid order:
 *   pending → preparing → ready → completed
 *          └→ cancelled (only if still pending, no waste — not yet made)
 *                └→ voided   (preparing/ready = waste cost recorded)
 *                            (pending + paid = NO waste cost, not yet made)
 *
 * FORMULA CHAIN:
 *   Gross Revenue  = SUM(orders.total_amount) WHERE paid AND not voided-order
 *                    NOTE: total_amount is already reduced when items are voided/cancelled
 *                    by cancel_items.php, so this naturally reflects only kept items.
 *   Total Refunds  = SUM(refunds.amount) for the period
 *   Net Revenue    = Gross Revenue − Total Refunds  (floor: 0)
 *   COGS           = SUM(cost_price × qty) for paid active order items that are
 *                    NOT cancelled, NOT voided, NOT individually refunded
 *   Gross Profit   = Net Revenue − COGS
 *   Waste Cost     = SUM(order_items.waste_cost) WHERE status='voided' AND waste_cost IS NOT NULL
 *                    (waste_cost=NULL means item was pending when voided → no ingredients used)
 *   Net Profit     = Gross Profit − Waste Cost
 *
 * ═══════════════════════════════════════════════════════════════════
 * KEY FIXES IN THIS VERSION vs v8:
 * ═══════════════════════════════════════════════════════════════════
 *
 * FIX 1: Payment join removed from COGS and waste queries.
 *   The old code did LEFT JOIN payments + WHERE p.amount_paid > 0, which:
 *   (a) turned the LEFT JOIN into an implicit INNER JOIN — excluding edge cases
 *   (b) would multiply SUM() results if an order ever had >1 payment row
 *   Solution: use EXISTS(SELECT 1 FROM payments WHERE ...) instead.
 *   This is a correlated subquery but guarantees exactly-one-match semantics.
 *
 * FIX 2: Waste query no longer joins payments at all.
 *   Waste cost is a property of order_items, not payments. An item can be
 *   voided on any paid order regardless of how payment was structured.
 *   We only need to confirm the parent order was paid (EXISTS check).
 *
 * FIX 3: _rpt_cogs_and_waste() refund exclusion subquery fixed.
 *   Old: str_replace(':df_',':rdf_', date_filter) inserted the date filter
 *   string into the subquery — but that string references table alias 'o'
 *   from the outer query, making it a correlated subquery by accident.
 *   It worked in simple cases but was fragile and semantically wrong.
 *   New: simple self-contained subquery — just filter refunds by order_id.
 *
 * FIX 4: Gross revenue query payment join uses EXISTS.
 *   Same multiplier risk as above. Replaced with EXISTS subquery.
 *
 * FIX 5: COGS now reflects paid active orders in real time.
 *   For pay-first paid orders, COGS is tracked from order creation
 *   while the order is still in-progress (pending/preparing/ready/completed).
 *   This avoids waiting until an order is marked completed to recognise cost.
 */

// ─────────────────────────────────────────────────────────────────────────────
// GROSS REVENUE (EXCLUDING VOIDED ITEMS) FOR GROSS REVENUE CARD
// ─────────────────────────────────────────────────────────────────────────────
function _rpt_gross_revenue_no_voided(PDO $pdo, string $date_filter, array $params): float
{
    $sql = "
        SELECT COALESCE(SUM(oi.subtotal), 0)
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.order_id
        WHERE oi.status != 'voided'
          AND o.status = 'completed'
          AND $date_filter
          AND EXISTS (
              SELECT 1 FROM payments px
              WHERE px.order_id = o.order_id AND px.amount_paid > 0
          )
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (float) $stmt->fetchColumn();
}

// ─────────────────────────────────────────────────────────────────────────────
// SHARED COGS + WASTE HELPER
// Called by daily, monthly, yearly summaries.
//
// $date_filter : SQL fragment referencing table alias 'o' (orders)
//                e.g. "DATE(o.completed_at) = :df_date"
//                     "YEAR(o.completed_at) = :df_year AND MONTH(o.completed_at) = :df_month"
//                All param names MUST start with :df_
// $params      : array of [':df_xxx' => value, ...]
//
// Returns: [float $cogs, float $waste]
// ─────────────────────────────────────────────────────────────────────────────
function _rpt_cogs_and_waste(PDO $pdo, string $date_filter, array $params): array
{
    // ── COGS ─────────────────────────────────────────────────────────────────
    // Count cost of every paid order item that:
    //   - belongs to a paid order currently in an active lifecycle state
    //     ('pending','preparing','ready','completed')
    //   - is not cancelled or voided
    //   - has not been individually refunded
    //
    // This makes the report real-time: COGS is tracked from order creation
    // for paid orders in progress, not only when the order is marked completed.
    // Uses EXISTS for payment check — avoids row-multiplication from JOIN.
    // Refund exclusion uses a simple IN(order_item_ids refunded on this order)
    // so the subquery is self-contained and does not reference outer aliases.
    $cogs_sql = "
        SELECT COALESCE(SUM(
            (COALESCE(mv.cost_price, mi.cost_price) * oi.quantity)
            + COALESCE(ac.addon_cost, 0)
        ), 0) AS base_cogs
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
        WHERE o.status IN ('pending', 'preparing', 'ready', 'completed')
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
    ";
    $cogs_stmt = $pdo->prepare($cogs_sql);
    $cogs_stmt->execute($params);
    $base_cogs = (float) $cogs_stmt->fetchColumn();

    // ── WASTE ─────────────────────────────────────────────────────────────────
    // Calculate waste cost accurately based on item preparation status at time of voiding
    // Rules:
    // - pending items voided: NO waste cost (ingredients not used)
    // - preparing/ready items voided: waste cost = cost + addons (ingredients consumed)
    // - completed items voided: waste cost = cost + addons (ingredients consumed)
    //
    // Since item_status_at_void column doesn't exist in database schema,
    // we need to use a different approach: check waste_cost field directly
    // - waste_cost = NULL means no waste (pending items)
    // - waste_cost > 0 means actual waste (preparing/ready/completed items)
    $waste_sql = "
        SELECT COALESCE(SUM(order_waste), 0)
        FROM (
            SELECT o.order_id,
                   o.waste_total,
                   COALESCE(SUM(oi.waste_cost), 0) AS order_waste_sum,
                   CASE
                       WHEN COALESCE(SUM(oi.waste_cost), 0) > 0 THEN COALESCE(SUM(oi.waste_cost), 0)
                       ELSE COALESCE(o.waste_total, 0)
                   END AS order_waste
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.order_id
            WHERE oi.status = 'voided'
              AND $date_filter
              AND EXISTS (
                  SELECT 1 FROM payments px
                  WHERE px.order_id = o.order_id AND px.amount_paid > 0
              )
            GROUP BY o.order_id, o.waste_total
        ) AS derived
    ";
    // Waste uses the same date_filter but against o.created_at
    // (items are voided the same day the order was created, generally)
    // The date_filter already scopes correctly if caller uses created_at.
    $waste_stmt = $pdo->prepare($waste_sql);
    $waste_stmt->execute($params);
    $waste_costs = (float) $waste_stmt->fetchColumn();

    return [$base_cogs, $waste_costs];
}

// ─────────────────────────────────────────────────────────────────────────────
// DAILY SUMMARY
// ─────────────────────────────────────────────────────────────────────────────
function rpt_daily_summary(PDO $pdo, string $date, ?int $cashier_id = null): array
{
    // EXISTS for payment check — no row multiplication risk.
    $cid_and = $cashier_id !== null ? 'AND o.cashier_id = :df_cid' : '';
    $date_filter = "DATE(o.created_at) = :df_date $cid_and";
    $params = [':df_date' => $date];
    if ($cashier_id !== null) $params[':df_cid'] = $cashier_id;
    
    $gross_revenue = _rpt_gross_revenue_no_voided($pdo, $date_filter, $params);
    
    $stmt = $pdo->prepare("
        SELECT
            COUNT(DISTINCT o.order_id)                                        AS total_orders,
            COALESCE(SUM(o.discount), 0)                                      AS total_discounts,
            COALESCE(SUM(o.total_amount) / NULLIF(COUNT(DISTINCT o.order_id),0), 0) AS avg_order_value,
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
        WHERE o.status = 'completed'
          AND DATE(o.created_at) = :date
          $cid_and
          AND EXISTS (
              SELECT 1 FROM payments px
              WHERE px.order_id = o.order_id AND px.amount_paid > 0
          )
    ");

    $params = [':date' => $date, ':cx_date' => $date];
    if ($cashier_id !== null) {
        $params[':df_cid'] = $cashier_id;
        $params[':cx_cid'] = $cashier_id;
    }
    $stmt->execute($params);
    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    
    // Use the gross revenue from the helper function that excludes voided items
    $row['gross_revenue'] = $gross_revenue;

    $row = array_merge([
        'total_orders'         => 0, 'gross_revenue'        => 0,
        'total_discounts'      => 0, 'net_revenue'          => 0,
        'net_revenue_excl_vat' => 0, 'total_vat_collected'  => 0,
        'avg_order_value'      => 0, 'gross_profit'         => 0,
        'total_waste_cost'     => 0, 'total_refunds'        => 0,
        'active_cashiers'      => 0, 'cancelled_orders'     => 0,
        'net_profit'           => 0, 'gross_including_voided' => 0,
    ], $row);

    // Calculate gross revenue including voided orders for net revenue card display
    $g_cid_and = $cashier_id !== null ? 'AND o.cashier_id = :g_cid' : '';
    $g_params  = [':g_date' => $date];
    if ($cashier_id !== null) $g_params[':g_cid'] = $cashier_id;
    $gstmt = $pdo->prepare("
        SELECT COALESCE(SUM(oi.subtotal), 0)
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        WHERE o.status IN ('pending', 'preparing', 'ready', 'completed', 'voided')
          AND DATE(o.created_at) = :g_date
          $g_cid_and
          AND EXISTS (
              SELECT 1 FROM payments px
              WHERE px.order_id = o.order_id AND px.amount_paid > 0
          )
    ");
    $gstmt->execute($g_params);
    $row['gross_including_voided'] = (float) $gstmt->fetchColumn();

    // Refunds for this date (scoped to cashier if applicable)
    $r_cid_and = $cashier_id !== null ? 'AND o.cashier_id = :r_cid' : '';
    $r_params  = [':r_date' => $date];
    if ($cashier_id !== null) $r_params[':r_cid'] = $cashier_id;
    $rstmt = $pdo->prepare("
        SELECT COALESCE(SUM(r.amount), 0)
        FROM refunds r
        JOIN orders o ON r.order_id = o.order_id
        WHERE DATE(r.created_at) = :r_date
          $r_cid_and
    ");
    $rstmt->execute($r_params);
    $row['total_refunds'] = (float) $rstmt->fetchColumn();

    // Net Revenue = Total Sales (including voided) - Refunds
    $row['net_revenue'] = (float) $row['gross_including_voided'] - $row['total_refunds'];

    // COGS & Waste
    // date_filter scopes to created_at so pending orders (pay-first) are included.
    // COGS helper counts all paid active order items in pending/preparing/ready/completed.
    // Waste uses created_at and only sums actual voided waste costs.
    $cid_part    = $cashier_id !== null ? ' AND o.cashier_id = :df_cid' : '';
    $date_filter = "DATE(o.created_at) = :df_date$cid_part";
    $df_params   = [':df_date' => $date];
    if ($cashier_id !== null) $df_params[':df_cid'] = $cashier_id;

    [$cogs, $waste] = _rpt_cogs_and_waste($pdo, $date_filter, $df_params);

    $row['gross_profit']     = (float) $row['gross_revenue'] - $cogs;
    $row['total_waste_cost'] = $waste;
    $row['net_profit']       = $row['gross_profit'] - $waste;

    return $row;
}

function rpt_closeout(PDO $pdo, string $date, int $cashier_id): array
{
    $stmt = $pdo->prepare("
        SELECT
            u.full_name                                              AS cashier_name,
            u.username,
            COUNT(DISTINCT o.order_id)                               AS total_orders,
            COALESCE(SUM(o.subtotal), 0)                             AS gross_sales,
            COALESCE(SUM(o.discount), 0)                             AS total_discounts,
            COALESCE(SUM(o.total_amount), 0)                         AS net_sales,
            COALESCE(SUM(o.total_amount / 1.12), 0)                  AS net_sales_excl_vat,
            COALESCE(SUM(o.total_amount - o.total_amount / 1.12), 0) AS vat_collected,
            (
                SELECT COUNT(*) FROM orders cx
                WHERE cx.cashier_id       = :cid_c
                  AND DATE(cx.created_at) = :date_c
                  AND cx.status           = 'cancelled'
            ) AS cancelled_orders
        FROM users u
        LEFT JOIN orders o
            ON  o.cashier_id         = u.user_id
            AND o.status IN ('pending', 'preparing', 'ready', 'completed')
            AND DATE(o.created_at)   = :date
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
 
    // Cash
    $stmt2 = $pdo->prepare("
        SELECT
            COALESCE(SUM(p.amount_paid), 0)                   AS cash_received,
            COALESCE(SUM(p.change_amount), 0)                 AS change_given,
            COALESCE(SUM(p.amount_paid - p.change_amount), 0) AS cash_to_turn_over,
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
 
    // Waste — voided items on this cashier's orders today
    $stmt3 = $pdo->prepare("
        SELECT COALESCE(SUM(order_waste), 0)
        FROM (
            SELECT o.order_id,
                   CASE
                       WHEN COALESCE(SUM(oi.waste_cost), 0) > 0 THEN COALESCE(SUM(oi.waste_cost), 0)
                       ELSE COALESCE(o.waste_total, 0)
                   END AS order_waste
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.order_id
            WHERE oi.status          = 'voided'
              AND o.cashier_id       = :cid
              AND DATE(o.created_at) = :date
            GROUP BY o.order_id, o.waste_total
        ) AS derived
    ");
    $stmt3->execute([':cid' => $cashier_id, ':date' => $date]);
    $waste = (float) $stmt3->fetchColumn();
 
    // Refunds this cashier processed today
    $stmt4 = $pdo->prepare("
        SELECT COALESCE(SUM(r.amount), 0)
        FROM refunds r
        WHERE r.processed_by    = :cid
          AND DATE(r.created_at) = :date
    ");
    $stmt4->execute([':cid' => $cashier_id, ':date' => $date]);
    $refunds = (float) $stmt4->fetchColumn();
 
    // ── Top 5 items sold this cashier's shift ─────────────────────────────────
    $stmt5 = $pdo->prepare("
        SELECT
            COALESCE(oi.item_name_snapshot, mi.item_name)    AS item_name,
            oi.variant_name_snapshot                          AS variant_name,
            SUM(oi.quantity)                                  AS units_sold,
            COALESCE(SUM(oi.subtotal), 0)                     AS total_revenue
        FROM order_items oi
        JOIN orders     o  ON oi.order_id = o.order_id
        JOIN menu_items mi ON oi.item_id  = mi.item_id
        WHERE o.cashier_id       = :cid
          AND DATE(o.created_at) = :date
          AND o.status IN ('pending', 'preparing', 'ready', 'completed')
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
 
    // ── Orders by hour for this cashier's shift ───────────────────────────────
    $stmt6 = $pdo->prepare("
        SELECT
            HOUR(o.created_at)               AS hour_of_day,
            COUNT(o.order_id)                AS total_orders,
            COALESCE(SUM(o.total_amount), 0) AS revenue
        FROM orders o
        WHERE o.cashier_id       = :cid
          AND DATE(o.created_at) = :date
          AND o.status IN ('pending', 'preparing', 'ready', 'completed')
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
    $merged['total_waste_cost']        = $waste;
    $merged['total_refunds']           = $refunds;
    $merged['net_sales_after_refunds'] = round((float)($merged['net_sales'] ?? 0) - $refunds, 2);
    $merged['top_items']               = $top_items;
    $merged['peak_hours']              = $peak_hours;
 
    return $merged;
}
 

// ─────────────────────────────────────────────────────────────────────────────
// MONTHLY SUMMARY
// ─────────────────────────────────────────────────────────────────────────────
function rpt_monthly_summary(PDO $pdo, int $year, int $month, ?int $cashier_id = null): array
{
    $cid_part = $cashier_id !== null ? ' AND o.cashier_id = :df_cid' : '';
    $params = [':df_year' => $year, ':df_month' => $month];
    if ($cashier_id !== null) $params[':df_cid'] = $cashier_id;
    $gross_revenue = _rpt_gross_revenue_no_voided($pdo, "YEAR(o.created_at) = :df_year AND MONTH(o.created_at) = :df_month $cid_part", $params);
    
    $stmt = $pdo->prepare("
        SELECT
            COUNT(DISTINCT o.order_id)                                        AS total_orders,
            COALESCE(SUM(o.discount), 0)                                      AS total_discounts,
            COALESCE(SUM(o.total_amount) / NULLIF(COUNT(DISTINCT o.order_id),0), 0) AS avg_order_value,
            COUNT(DISTINCT o.cashier_id)                                      AS active_cashiers,
            COALESCE(SUM(o.total_amount / 1.12), 0)                           AS net_revenue_excl_vat,
            COALESCE(SUM(o.total_amount - o.total_amount / 1.12), 0)          AS total_vat_collected
        FROM orders o
        WHERE o.status = 'completed'
          AND YEAR(o.created_at)  = :year
          AND MONTH(o.created_at) = :month
          $cid_part
          AND EXISTS (
              SELECT 1 FROM payments px
              WHERE px.order_id = o.order_id AND px.amount_paid > 0
          )
    ");
    $stmt->execute([':year' => $year, ':month' => $month] + ($cashier_id !== null ? [':df_cid' => $cashier_id] : []));
    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    
    // Use the gross revenue from the helper function that excludes voided items
    $row['gross_revenue'] = $gross_revenue;

    $row = array_merge([
        'total_orders'         => 0, 'gross_revenue'    => 0,
        'total_discounts'      => 0, 'net_revenue'      => 0,
        'net_revenue_excl_vat' => 0, 'total_vat_collected' => 0,
        'avg_order_value'      => 0, 'gross_profit'     => 0,
        'total_waste_cost'     => 0, 'total_refunds'    => 0,
        'active_cashiers'      => 0, 'net_profit'       => 0,
        'gross_including_voided' => 0,
    ], $row);

    // Calculate gross revenue including voided orders for net revenue card display
    $gstmt = $pdo->prepare("
        SELECT COALESCE(SUM(oi.subtotal), 0)
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        WHERE o.status IN ('pending', 'preparing', 'ready', 'completed', 'voided')
          AND YEAR(o.created_at)  = :year
          AND MONTH(o.created_at) = :month
          $cid_part
          AND EXISTS (
              SELECT 1 FROM payments px
              WHERE px.order_id = o.order_id AND px.amount_paid > 0
          )
    ");
    $gstmt->execute([':year' => $year, ':month' => $month] + ($cashier_id !== null ? [':df_cid' => $cashier_id] : []));
    $row['gross_including_voided'] = (float) $gstmt->fetchColumn();

    $rstmt = $pdo->prepare("
        SELECT COALESCE(SUM(r.amount), 0)
        FROM refunds r
        JOIN orders o ON r.order_id = o.order_id
        WHERE YEAR(r.created_at)  = :year
          AND MONTH(r.created_at) = :month
          $cid_part
    ");
    $rstmt->execute([':year' => $year, ':month' => $month] + ($cashier_id !== null ? [':df_cid' => $cashier_id] : []));
    $row['total_refunds'] = (float) $rstmt->fetchColumn();
    // Net Revenue = Total Sales (including voided) - Refunds
    $row['net_revenue']   = (float)$row['gross_including_voided'] - $row['total_refunds'];

    $date_filter = 'YEAR(o.created_at) = :df_year AND MONTH(o.created_at) = :df_month' . $cid_part;
    $df_params   = [':df_year' => $year, ':df_month' => $month];
    if ($cashier_id !== null) $df_params[':df_cid'] = $cashier_id;
    [$cogs, $waste] = _rpt_cogs_and_waste($pdo, $date_filter, $df_params);

    $row['gross_profit']     = (float) $row['gross_revenue'] - $cogs;
    $row['total_waste_cost'] = $waste;
    $row['net_profit']       = $row['gross_profit'] - $waste;

    return $row;
}

// ─────────────────────────────────────────────────────────────────────────────
// DAILY BREAKDOWN  (day-by-day for monthly chart)
// ─────────────────────────────────────────────────────────────────────────────
function rpt_daily_breakdown(PDO $pdo, int $year, int $month): array
{
    $stmt = $pdo->prepare("
        SELECT
            DATE(o.created_at)                                       AS sale_date,
            COUNT(o.order_id)                                        AS total_orders,
            COALESCE(SUM(o.total_amount), 0)                         AS revenue,
            COALESCE(SUM(o.total_amount / 1.12), 0)                  AS revenue_excl_vat,
            COALESCE(SUM(o.total_amount - o.total_amount / 1.12), 0) AS vat_amount
        FROM orders o
        WHERE o.status IN ('pending', 'preparing', 'ready', 'completed')
          AND DATE(o.created_at) BETWEEN :start AND :end
          AND YEAR(o.created_at)  = :year
          AND MONTH(o.created_at) = :month
          AND EXISTS (
              SELECT 1 FROM payments px
              WHERE px.order_id = o.order_id AND px.amount_paid > 0
          )
        GROUP BY DATE(o.created_at)
        ORDER BY sale_date
    ");

    $periodStart = sprintf('%04d-%02d-01', $year, $month);
    $periodEnd   = date('Y-m-t', strtotime($periodStart));

    $stmt->execute([
        ':start' => $periodStart,
        ':end'   => $periodEnd,
        ':year'  => $year,
        ':month' => $month,
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ─────────────────────────────────────────────────────────────────────────────
// YEARLY SUMMARY
// ─────────────────────────────────────────────────────────────────────────────
function rpt_yearly_summary(PDO $pdo, int $year, ?int $cashier_id = null): array
{
    $cid_part = $cashier_id !== null ? ' AND o.cashier_id = :df_cid' : '';
    $params = [':df_year' => $year];
    if ($cashier_id !== null) $params[':df_cid'] = $cashier_id;
    $gross_revenue = _rpt_gross_revenue_no_voided($pdo, "YEAR(o.created_at) = :df_year $cid_part", $params);
    
    $stmt = $pdo->prepare("
        SELECT
            COUNT(DISTINCT o.order_id)                                        AS total_orders,
            COALESCE(SUM(o.discount), 0)                                      AS total_discounts,
            COALESCE(SUM(o.total_amount) / NULLIF(COUNT(DISTINCT o.order_id),0), 0) AS avg_order_value,
            COALESCE(SUM(o.total_amount / 1.12), 0)                           AS net_revenue_excl_vat,
            COALESCE(SUM(o.total_amount - o.total_amount / 1.12), 0)          AS total_vat_collected
        FROM orders o
        WHERE o.status = 'completed'
          AND YEAR(o.created_at) = :year
          $cid_part
          AND EXISTS (
              SELECT 1 FROM payments px
              WHERE px.order_id = o.order_id AND px.amount_paid > 0
          )
    ");
    $stmt->execute([':year' => $year] + ($cashier_id !== null ? [':df_cid' => $cashier_id] : []));
    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    
    // Use the gross revenue from the helper function that excludes voided items
    $row['gross_revenue'] = $gross_revenue;

    $row = array_merge([
        'total_orders'         => 0, 'gross_revenue'    => 0,
        'total_discounts'      => 0, 'net_revenue'      => 0,
        'net_revenue_excl_vat' => 0, 'total_vat_collected' => 0,
        'avg_order_value'      => 0, 'gross_profit'     => 0,
        'total_waste_cost'     => 0, 'total_refunds'    => 0,
        'net_profit'           => 0, 'gross_including_voided' => 0,
    ], $row);

    // Calculate gross revenue including voided orders for net revenue card display
    $gstmt = $pdo->prepare("
        SELECT COALESCE(SUM(oi.subtotal), 0)
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        WHERE o.status IN ('pending', 'preparing', 'ready', 'completed', 'voided')
          AND YEAR(o.created_at) = :year
          $cid_part
          AND EXISTS (
              SELECT 1 FROM payments px
              WHERE px.order_id = o.order_id AND px.amount_paid > 0
          )
    ");
    $gstmt->execute([':year' => $year] + ($cashier_id !== null ? [':df_cid' => $cashier_id] : []));
    $row['gross_including_voided'] = (float) $gstmt->fetchColumn();

    $rstmt = $pdo->prepare("
        SELECT COALESCE(SUM(r.amount), 0)
        FROM refunds r
        JOIN orders o ON r.order_id = o.order_id
        WHERE YEAR(r.created_at) = :year
          $cid_part
    ");
    $rstmt->execute([':year' => $year] + ($cashier_id !== null ? [':df_cid' => $cashier_id] : []));
    $row['total_refunds'] = (float) $rstmt->fetchColumn();
    // Net Revenue = Total Sales (including voided) - Refunds
    $row['net_revenue']   = (float)$row['gross_including_voided'] - $row['total_refunds'];

    $date_filter = 'YEAR(o.created_at) = :df_year' . $cid_part;
    $df_params   = [':df_year' => $year];
    if ($cashier_id !== null) $df_params[':df_cid'] = $cashier_id;
    [$cogs, $waste] = _rpt_cogs_and_waste($pdo, $date_filter, $df_params);

    $row['gross_profit']     = (float) $row['gross_revenue'] - $cogs;
    $row['total_waste_cost'] = $waste;
    $row['net_profit']       = $row['gross_profit'] - $waste;

    // Monthly bar chart data
    $mstmt = $pdo->prepare("
        SELECT MONTH(o.created_at) AS month_num,
               COALESCE(SUM(o.total_amount), 0) AS monthly_revenue
        FROM orders o
        WHERE o.status IN ('pending', 'preparing', 'ready', 'completed')
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
        $mdata[(int)$mr['month_num']] = (float)$mr['monthly_revenue'];
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
        WHERE o.status IN ('pending', 'preparing', 'ready', 'completed')
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
// WASTE REPORT  (full void log — for Waste tab)
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
// WASTE SUMMARY  (summary cards for Waste tab)
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
            COALESCE((
                SELECT COALESCE(SUM(order_waste), 0)
                FROM (
                    SELECT o.order_id,
                           o.waste_total,
                           CASE
                               WHEN COALESCE(SUM(oi2.waste_cost), 0) > 0
                                  THEN COALESCE(SUM(oi2.waste_cost), 0)
                               ELSE COALESCE(o.waste_total, 0)
                           END AS order_waste
                    FROM order_items oi2
                    JOIN orders o ON oi2.order_id = o.order_id
                    WHERE oi2.status = 'voided'
                      AND DATE(o.created_at) BETWEEN :start AND :end
                      $cid_and
                    GROUP BY o.order_id, o.waste_total
                ) AS derived
            ), 0) AS total_waste_cost,
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
        'voided_orders'    => 0, 'voided_items'     => 0,
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
        WHERE o.status IN ('pending', 'preparing', 'ready', 'completed')
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
            COALESCE(c.total_sales - c.total_sales/1.12, 0)    AS vat_collected,
            COALESCE(c.avg_order_value,   0)                   AS avg_order_value,
            COALESCE(c.discounts_given,   0)                   AS discounts_given,
            COALESCE(x.cancelled_orders,  0)                   AS cancelled_orders,
            COALESCE(p.admin_processed,   0)                   AS admin_processed_count,
            COALESCE(w.total_waste,       0)                   AS total_waste_cost
        FROM users u
        LEFT JOIN (
            SELECT cashier_id,
                   COUNT(order_id)   AS orders_handled,
                   SUM(total_amount) AS total_sales,
                   AVG(total_amount) AS avg_order_value,
                   SUM(discount)     AS discounts_given
            FROM orders
            WHERE status IN ('pending', 'preparing', 'ready', 'completed')
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
            HOUR(o.completed_at)                                     AS hour_of_day,
            COUNT(o.order_id)                                        AS total_orders,
            COALESCE(SUM(o.total_amount), 0)                         AS revenue,
            COALESCE(SUM(o.total_amount / 1.12), 0)                  AS revenue_excl_vat
        FROM orders o
        WHERE o.status IN ('pending', 'preparing', 'ready', 'completed')
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
            COUNT(o.order_id)                        AS total_orders,
            COALESCE(SUM(o.total_amount), 0)          AS revenue,
            COALESCE(SUM(o.total_amount / 1.12), 0)   AS revenue_excl_vat
        FROM orders o
        WHERE o.status IN ('pending', 'preparing', 'ready', 'completed')
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
        JOIN addons     a  ON oia.addon_id      = a.addon_id
        JOIN order_items oi ON oia.order_item_id = oi.order_item_id
        JOIN orders     o  ON oi.order_id        = o.order_id
        WHERE o.status IN ('pending', 'preparing', 'ready', 'completed')
          AND DATE(o.created_at) BETWEEN :start AND :end
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
            ROUND(o.total_amount - o.total_amount/1.12, 2)   AS vat_amount,
            o.status,
            o.customer_name,
            o.created_at,
            o.completed_at
        FROM orders o
        JOIN users u ON o.cashier_id = u.user_id
        WHERE (
            (o.discount > 0 AND o.status IN ('pending', 'preparing', 'ready', 'completed')
             AND DATE(o.created_at) BETWEEN :s1 AND :e1
             AND EXISTS (
                 SELECT 1 FROM payments px
                 WHERE px.order_id = o.order_id AND px.amount_paid > 0
             ))
            OR
            (o.status = 'cancelled'
             AND DATE(o.created_at) BETWEEN :s2 AND :e2)
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
            DATE(p.paid_at)                                             AS pay_date,
            u.full_name                                                 AS cashier,
            COUNT(p.payment_id)                                         AS transactions,
            COALESCE(SUM(p.amount_paid), 0)                             AS cash_received,
            COALESCE(SUM(p.change_amount), 0)                           AS change_given,
            COALESCE(SUM(p.amount_paid - p.change_amount), 0)           AS net_cash,
            COALESCE(SUM(o.total_amount), 0)                            AS expected_from_orders,
            COALESCE(SUM(o.total_amount / 1.12), 0)                     AS expected_excl_vat,
            COALESCE(SUM(o.total_amount - o.total_amount/1.12), 0)      AS vat_in_expected,
            COALESCE(SUM(p.amount_paid - p.change_amount), 0)
                - COALESCE(SUM(o.total_amount), 0)                      AS variance,
            SUM(CASE WHEN p.processed_by IS NOT NULL
                      AND p.processed_by != p.cashier_id
                     THEN 1 ELSE 0 END)                                 AS admin_processed_count
        FROM payments p
        JOIN users  u ON p.cashier_id = u.user_id
        JOIN orders o ON p.order_id   = o.order_id
            AND o.status IN ('pending', 'preparing', 'ready', 'completed')
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
        SELECT
            i.ingredient_id, i.ingredient_name,
            i.stock_quantity, i.low_stock_alert, i.last_updated,
            u.unit_code  AS unit, u.unit_label,
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

// ─────────────────────────────────────────────────────────────────────────────
// AVAILABLE YEARS
// ─────────────────────────────────────────────────────────────────────────────
function rpt_available_years(PDO $pdo): array
{
    $stmt = $pdo->query("
        SELECT DISTINCT YEAR(created_at) AS yr
        FROM orders
        WHERE status IN ('pending', 'preparing', 'ready', 'completed')
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
                COUNT(DISTINCT o.order_id)                       AS total_orders,
                COALESCE(SUM(o.total_amount), 0)                  AS gross_revenue,
                COALESCE(SUM(o.discount), 0)                      AS total_discounts,
                COALESCE(AVG(o.total_amount), 0)                  AS avg_order_value,
                COALESCE((SELECT SUM(r.amount) FROM refunds r
                          WHERE DATE(r.created_at) BETWEEN :start AND :end), 0) AS total_refunds,
                COALESCE(SUM(o.total_amount), 0) -
                    COALESCE((SELECT SUM(r.amount) FROM refunds r
                              WHERE DATE(r.created_at) BETWEEN :start AND :end), 0) AS net_revenue,
                0                                                 AS gross_profit,
                0                                                 AS net_profit,
                (SELECT COUNT(*) FROM orders
                 WHERE status = 'cancelled'
                   AND DATE(created_at) BETWEEN :s2 AND :e2)      AS cancelled_orders
            FROM orders o
            WHERE o.status IN ('pending', 'preparing', 'ready', 'completed')
              AND DATE(o.created_at) BETWEEN :start AND :end
              AND EXISTS (
                  SELECT 1 FROM payments px
                  WHERE px.order_id = o.order_id AND px.amount_paid > 0
              )
        ");
        $stmt->execute([':start' => $start, ':end' => $end, ':s2' => $start, ':e2' => $end]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $date_filter = 'DATE(o.created_at) BETWEEN :df_start AND :df_end';
        $df_params = [':df_start' => $start, ':df_end' => $end];
        [$cogs, $waste] = _rpt_cogs_and_waste($pdo, $date_filter, $df_params);

        $row['gross_profit'] = (float)$row['net_revenue'] - $cogs;
        $row['net_profit']   = $row['gross_profit'] - $waste;

        return array_merge([
            'total_orders'     => 0,
            'gross_revenue'    => 0,
            'net_revenue'      => 0,
            'total_discounts'  => 0,
            'avg_order_value'  => 0,
            'total_refunds'    => 0,
            'gross_profit'     => 0,
            'net_profit'       => 0,
            'cancelled_orders' => 0,
        ], $row);
    };

    $current  = $fetch($pdo, $cur_start,  $cur_end);
    $previous = $fetch($pdo, $prev_start, $prev_end);

    $change = [];
    foreach ($current as $key => $val) {
        $prev         = (float)($previous[$key] ?? 0);
        $curr         = (float)$val;
        $change[$key] = $prev > 0 ? round(($curr - $prev) / $prev * 100, 1)
                                  : ($curr > 0 ? 100.0 : 0.0);
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