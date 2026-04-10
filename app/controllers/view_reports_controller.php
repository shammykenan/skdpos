<?php
/**
 * reports_controller.php – v7 (adminshift tab added)
 *
 * RBAC:
 *   admin   → daily, monthly, yearly, products, staff, adminshift, audit, consumption, waste
 *   cashier → myshift, closeout, inventory, waste
 *   kitchen → blocked
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../models/reports.php';
$baseUrl = $_ENV['BASE_URL'] ?? '';

// ── 1. Auth guard ─────────────────────────────────────────────────────────────
$role = $_SESSION['role'] ?? '';
if (!in_array($role, ['admin', 'cashier'])) {
    header('Location: ' . $baseUrl . '/index.php?page=' . ($role === 'kitchen' ? 'kitchen' : 'login'));
    exit;
}

$is_admin    = ($role === 'admin');
$session_uid = (int)($_SESSION['user_id'] ?? 0);

// ── 2. Allowed tabs per role ──────────────────────────────────────────────────
$allowed_tabs = $is_admin
    ? ['daily', 'monthly', 'yearly', 'products', 'staff', 'adminshift', 'audit', 'consumption', 'waste']
    : ['myshift', 'closeout', 'inventory', 'waste'];

$default_tab = $is_admin ? 'daily' : 'myshift';

// ── 3. Resolve tab ────────────────────────────────────────────────────────────
$tab = $_GET['tab'] ?? $default_tab;
if (!in_array($tab, $allowed_tabs)) $tab = $default_tab;

// ── 4. Date params ────────────────────────────────────────────────────────────
date_default_timezone_set('Asia/Manila');

$today         = date('Y-m-d');
$current_year  = (int)date('Y');
$current_month = (int)date('m');

$filter_date  = $_GET['date']  ?? $today;
$filter_year  = (int)($_GET['year']  ?? $current_year);
$filter_month = (int)($_GET['month'] ?? $current_month);
$range_start  = $_GET['start'] ?? date('Y-m-01');
$range_end    = $_GET['end']   ?? $today;

$filter_month = max(1, min(12, $filter_month));

// ── 5. Period comparison helper ───────────────────────────────────────────────
function get_previous_period(string $start, string $end): array {
    $days       = (int)((strtotime($end) - strtotime($start)) / 86400) + 1;
    $prev_end   = date('Y-m-d', strtotime($start) - 86400);
    $prev_start = date('Y-m-d', strtotime($prev_end) - ($days - 1) * 86400);
    return ['start' => $prev_start, 'end' => $prev_end];
}

// ── 6. Load data ──────────────────────────────────────────────────────────────
$data = [];

switch ($tab) {

    // ── CASHIER: My Shift ─────────────────────────────────────────────────────
    case 'myshift':
        $data['summary']    = rpt_daily_summary($pdo, $filter_date, $session_uid);
        $data['top_items']  = rpt_top_items($pdo, $filter_date, $filter_date, 5);
        $data['peak_hours'] = rpt_peak_hours($pdo, $filter_date, $filter_date);
        $prev = get_previous_period($filter_date, $filter_date);
        $data['comparison'] = rpt_period_comparison(
            $pdo, $filter_date, $filter_date, $prev['start'], $prev['end']
        );
        break;

    // ── CASHIER: Close-out ────────────────────────────────────────────────────
    case 'closeout':
        $data['closeout'] = rpt_closeout($pdo, $filter_date, $session_uid);
        break;

    // ── SHARED: Inventory ─────────────────────────────────────────────────────
    case 'inventory':
        $data['low_stock'] = rpt_low_stock($pdo);
        break;

    // ── ADMIN: Daily ──────────────────────────────────────────────────────────
    case 'daily':
        $data['summary']     = rpt_daily_summary($pdo, $filter_date);
        $data['top_items']   = rpt_top_items($pdo, $filter_date, $filter_date, 5);
        $data['peak_hours']  = rpt_peak_hours($pdo, $filter_date, $filter_date);
        $data['order_types'] = rpt_order_type_breakdown($pdo, $filter_date, $filter_date);
        $data['cashiers']    = rpt_cashier_performance($pdo, $filter_date, $filter_date);
        $prev = get_previous_period($filter_date, $filter_date);
        $data['comparison']  = rpt_period_comparison(
            $pdo, $filter_date, $filter_date, $prev['start'], $prev['end']
        );
        break;

    // ── ADMIN: My Shift (adminshift) ──────────────────────────────────────────
    case 'adminshift':
        $data['closeout'] = rpt_closeout($pdo, $filter_date, $session_uid);
        break;

    // ── ADMIN: Monthly ────────────────────────────────────────────────────────
    case 'monthly':
        $m_ts    = mktime(0, 0, 0, $filter_month, 1, $filter_year);
        $m_start = date('Y-m-01', $m_ts);
        $m_end   = date('Y-m-t',  $m_ts);

        $prev_ts    = mktime(0, 0, 0, $filter_month - 1, 1, $filter_year);
        $prev_start = date('Y-m-01', $prev_ts);
        $prev_end   = date('Y-m-t',  $prev_ts);

        $data['summary']     = rpt_monthly_summary($pdo, $filter_year, $filter_month);
        $data['daily_chart'] = rpt_daily_breakdown($pdo, $filter_year, $filter_month);
        $data['top_items']   = rpt_top_items($pdo, $m_start, $m_end, 10);
        $data['categories']  = rpt_sales_by_category($pdo, $m_start, $m_end);
        $data['cashiers']    = rpt_cashier_performance($pdo, $m_start, $m_end);
        $data['comparison']  = rpt_period_comparison($pdo, $m_start, $m_end, $prev_start, $prev_end);
        break;

    // ── ADMIN: Yearly ─────────────────────────────────────────────────────────
    case 'yearly':
        $data['summary']         = rpt_yearly_summary($pdo, $filter_year);
        $data['categories']      = rpt_sales_by_category($pdo, "$filter_year-01-01", "$filter_year-12-31");
        $data['top_items']       = rpt_top_items($pdo, "$filter_year-01-01", "$filter_year-12-31", 10);
        $data['available_years'] = rpt_available_years($pdo);
        $data['comparison']      = rpt_period_comparison(
            $pdo,
            "$filter_year-01-01", "$filter_year-12-31",
            ($filter_year - 1) . "-01-01", ($filter_year - 1) . "-12-31"
        );
        break;

    // ── ADMIN: Products ───────────────────────────────────────────────────────
    case 'products':
        $data['top_items']  = rpt_top_items($pdo, $range_start, $range_end, 20);
        $data['categories'] = rpt_sales_by_category($pdo, $range_start, $range_end);
        $data['top_addons'] = rpt_top_addons($pdo, $range_start, $range_end);
        break;

    // ── ADMIN: Staff ──────────────────────────────────────────────────────────
    case 'staff':
        $data['cashiers']    = rpt_cashier_performance($pdo, $range_start, $range_end);
        $data['peak_hours']  = rpt_peak_hours($pdo, $range_start, $range_end);
        $data['order_types'] = rpt_order_type_breakdown($pdo, $range_start, $range_end);
        break;

    // ── ADMIN: Audit ──────────────────────────────────────────────────────────
    case 'audit':
        $data['discounts'] = rpt_discount_audit($pdo, $range_start, $range_end);
        $data['payments']  = rpt_payment_reconciliation($pdo, $range_start, $range_end);
        break;

    // ── ADMIN: Ingredient Consumption ─────────────────────────────────────────
    case 'consumption':
        $data['consumption'] = rpt_ingredient_consumption($pdo, $range_start, $range_end);
        $data['low_stock']   = rpt_low_stock($pdo);
        break;

    // ── ADMIN + CASHIER: Waste ────────────────────────────────────────────────
    case 'waste':
        $waste_cid           = $is_admin ? null : $session_uid;
        $data['summary']     = rpt_waste_summary($pdo, $range_start, $range_end, $waste_cid);
        $data['waste_items'] = rpt_waste_report($pdo,  $range_start, $range_end, $waste_cid);
        break;
}

// ── 7. Context for view ───────────────────────────────────────────────────────
$ctx = [
    'tab'          => $tab,
    'role'         => $role,
    'is_admin'     => $is_admin,
    'session_uid'  => $session_uid,
    'session_name' => $_SESSION['full_name'] ?? '',
    'allowed_tabs' => $allowed_tabs,
    'filter_date'  => $filter_date,
    'filter_year'  => $filter_year,
    'filter_month' => $filter_month,
    'range_start'  => $range_start,
    'range_end'    => $range_end,
    'today'        => $today,
    'data'         => $data,
    'baseUrl'      => $baseUrl,
];

require_once BASE_PATH . '/app/views/cashier/view_reports.php';