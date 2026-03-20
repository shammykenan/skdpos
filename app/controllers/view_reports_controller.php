<?php
/**
 * reports_controller.php  – v3
 *
 * RBAC:
 *   admin   → daily, monthly, yearly, products, staff, audit
 *   cashier → myshift, closeout, inventory
 *   kitchen → blocked
 *
 * Wire into router:
 *   case 'reports': require 'reports_controller.php'; break;
 *
 * Requires in scope: $pdo, $baseUrl, $_SESSION
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../models/reports.php';
$baseUrl = $_ENV['BASE_URL'] ?? '';
// ── 1. Auth guard ────────────────────────────────────────────
$role = $_SESSION['role'] ?? '';
if (!in_array($role, ['admin', 'cashier'])) {
    header('Location: ' . $baseUrl . '/index.php?page=' . ($role === 'kitchen' ? 'kitchen' : 'login'));
    exit;
}

$is_admin    = ($role === 'admin');
$session_uid = (int) ($_SESSION['user_id'] ?? 0);

// ── 2. Allowed tabs per role ─────────────────────────────────
$allowed_tabs = $is_admin
    ? ['daily', 'monthly', 'yearly', 'products', 'staff', 'audit']
    : ['myshift', 'closeout', 'inventory'];

$default_tab = $is_admin ? 'daily' : 'myshift';

// ── 3. Resolve tab ───────────────────────────────────────────
$tab = $_GET['tab'] ?? $default_tab;
if (!in_array($tab, $allowed_tabs)) {
    $tab = $default_tab;
}

// ── 4. Date params ───────────────────────────────────────────
// Always use Asia/Manila so dates match the server's business day
date_default_timezone_set('Asia/Manila');

$today         = date('Y-m-d');
$current_year  = (int) date('Y');
$current_month = (int) date('m');

$filter_date  = $_GET['date']  ?? $today;
$filter_year  = (int) ($_GET['year']  ?? $current_year);
$filter_month = (int) ($_GET['month'] ?? $current_month);
$range_start  = $_GET['start'] ?? date('Y-m-01');   // first of current month
$range_end    = $_GET['end']   ?? $today;

$filter_month = max(1, min(12, $filter_month));

// ── 5. Load data ─────────────────────────────────────────────
$data = [];

switch ($tab) {

    // ── CASHIER: My Shift ────────────────────────────────────
    case 'myshift':
        $data['summary']    = rpt_daily_summary($pdo, $filter_date, $session_uid);
        $data['top_items']  = rpt_top_items($pdo, $filter_date, $filter_date, 5);
        $data['peak_hours'] = rpt_peak_hours($pdo, $filter_date, $filter_date);
        break;

    // ── CASHIER: Close-out ───────────────────────────────────
    case 'closeout':
        $data['closeout'] = rpt_closeout($pdo, $filter_date, $session_uid);
        break;

    // ── SHARED: Inventory ────────────────────────────────────
    case 'inventory':
        $data['low_stock'] = rpt_low_stock($pdo);
        break;

    // ── ADMIN: Daily ─────────────────────────────────────────
    case 'daily':
        $data['summary']     = rpt_daily_summary($pdo, $filter_date);
        $data['top_items']   = rpt_top_items($pdo, $filter_date, $filter_date, 5);
        $data['peak_hours']  = rpt_peak_hours($pdo, $filter_date, $filter_date);
        $data['order_types'] = rpt_order_type_breakdown($pdo, $filter_date, $filter_date);
        $data['cashiers']    = rpt_cashier_performance($pdo, $filter_date, $filter_date);
        break;

    // ── ADMIN: Monthly ───────────────────────────────────────
    case 'monthly':
        // Build first/last day of the selected month
        $m_ts    = mktime(0, 0, 0, $filter_month, 1, $filter_year);
        $m_start = date('Y-m-01', $m_ts);
        $m_end   = date('Y-m-t',  $m_ts);

        $data['summary']     = rpt_monthly_summary($pdo, $filter_year, $filter_month);
        $data['daily_chart'] = rpt_daily_breakdown($pdo, $filter_year, $filter_month);
        $data['top_items']   = rpt_top_items($pdo, $m_start, $m_end, 10);
        $data['categories']  = rpt_sales_by_category($pdo, $m_start, $m_end);
        $data['cashiers']    = rpt_cashier_performance($pdo, $m_start, $m_end);
        break;

    // ── ADMIN: Yearly ────────────────────────────────────────
    case 'yearly':
        $data['summary']         = rpt_yearly_summary($pdo, $filter_year);
        $data['categories']      = rpt_sales_by_category($pdo, "$filter_year-01-01", "$filter_year-12-31");
        $data['top_items']       = rpt_top_items($pdo, "$filter_year-01-01", "$filter_year-12-31", 10);
        $data['available_years'] = rpt_available_years($pdo);
        break;

    // ── ADMIN: Products ──────────────────────────────────────
    case 'products':
        $data['top_items']  = rpt_top_items($pdo, $range_start, $range_end, 20);
        $data['categories'] = rpt_sales_by_category($pdo, $range_start, $range_end);
        $data['top_addons'] = rpt_top_addons($pdo, $range_start, $range_end);
        break;

    // ── ADMIN: Staff ─────────────────────────────────────────
    case 'staff':
        $data['cashiers']    = rpt_cashier_performance($pdo, $range_start, $range_end);
        $data['peak_hours']  = rpt_peak_hours($pdo, $range_start, $range_end);
        $data['order_types'] = rpt_order_type_breakdown($pdo, $range_start, $range_end);
        break;

    // ── ADMIN: Audit ─────────────────────────────────────────
    case 'audit':
        $data['discounts'] = rpt_discount_audit($pdo, $range_start, $range_end);
        $data['payments']  = rpt_payment_reconciliation($pdo, $range_start, $range_end);
        break;
}

// ── 6. Context for view ──────────────────────────────────────
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