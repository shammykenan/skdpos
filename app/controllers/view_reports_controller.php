<?php
/**
 * reports_controller.php
 * Handles input, calls model functions, passes data to the view.
 *
 * Usage in your router/index.php:
 *   case 'reports': require 'reports_controller.php'; break;
 *
 * Requires:
 *   $pdo        – PDO database connection
 *   $baseUrl    – base URL string
 *   $_SESSION   – with 'role' and 'full_name'
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../models/reports.php';
// ----------------------------------------------------------
// Auth guard – only admin can access reports
// ----------------------------------------------------------
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . $baseUrl . '/index.php?page=login');
    exit;
}
$baseUrl = $_ENV['BASE_URL'];
// ----------------------------------------------------------
// Active tab (defaults to 'daily')
// ----------------------------------------------------------
$tab = $_GET['tab'] ?? 'daily';
$allowed_tabs = ['daily', 'monthly', 'yearly', 'products', 'staff', 'orders', 'inventory', 'audit'];
if (!in_array($tab, $allowed_tabs)) $tab = 'daily';

// ----------------------------------------------------------
// Date parameters
// ----------------------------------------------------------
$today        = date('Y-m-d');
$current_year = (int) date('Y');
$current_month= (int) date('m');

$filter_date  = $_GET['date']  ?? $today;
$filter_year  = (int)($_GET['year']  ?? $current_year);
$filter_month = (int)($_GET['month'] ?? $current_month);

// Range used by multi-day reports (products, staff, orders, audit)
$range_start  = $_GET['start'] ?? date('Y-m-01');   // first of current month
$range_end    = $_GET['end']   ?? $today;

// Clamp month to valid range
if ($filter_month < 1)  $filter_month = 1;
if ($filter_month > 12) $filter_month = 12;

// ----------------------------------------------------------
// Load data based on active tab
// ----------------------------------------------------------
$data = [];   // main payload passed to view

switch ($tab) {

    case 'daily':
        $data['summary']       = rpt_daily_summary($pdo, $filter_date);
        $data['top_items']     = rpt_top_items($pdo, $filter_date, $filter_date, 5);
        $data['peak_hours']    = rpt_peak_hours($pdo, $filter_date, $filter_date);
        $data['order_types']   = rpt_order_type_breakdown($pdo, $filter_date, $filter_date);
        $data['cashiers']      = rpt_cashier_performance($pdo, $filter_date, $filter_date);
        break;

    case 'monthly':
        $data['summary']       = rpt_monthly_summary($pdo, $filter_year, $filter_month);
        $data['daily_chart']   = rpt_daily_breakdown($pdo, $filter_year, $filter_month);
        $data['top_items']     = rpt_top_items($pdo, 
                                    date('Y-m-01', mktime(0,0,0,$filter_month,1,$filter_year)),
                                    date('Y-m-t',  mktime(0,0,0,$filter_month,1,$filter_year)), 10);
        $data['categories']    = rpt_sales_by_category($pdo,
                                    date('Y-m-01', mktime(0,0,0,$filter_month,1,$filter_year)),
                                    date('Y-m-t',  mktime(0,0,0,$filter_month,1,$filter_year)));
        $data['cashiers']      = rpt_cashier_performance($pdo,
                                    date('Y-m-01', mktime(0,0,0,$filter_month,1,$filter_year)),
                                    date('Y-m-t',  mktime(0,0,0,$filter_month,1,$filter_year)));
        break;

    case 'yearly':
        $data['summary']       = rpt_yearly_summary($pdo, $filter_year);
        $data['categories']    = rpt_sales_by_category($pdo,
                                    $filter_year . '-01-01',
                                    $filter_year . '-12-31');
        $data['top_items']     = rpt_top_items($pdo,
                                    $filter_year . '-01-01',
                                    $filter_year . '-12-31', 10);
        $data['available_years'] = rpt_available_years($pdo);
        break;

    case 'products':
        $data['top_items']     = rpt_top_items($pdo, $range_start, $range_end, 20);
        $data['categories']    = rpt_sales_by_category($pdo, $range_start, $range_end);
        $data['top_addons']    = rpt_top_addons($pdo, $range_start, $range_end);
        break;

    case 'staff':
        $data['cashiers']      = rpt_cashier_performance($pdo, $range_start, $range_end);
        break;

    case 'orders':
        $data['peak_hours']    = rpt_peak_hours($pdo, $range_start, $range_end);
        $data['order_types']   = rpt_order_type_breakdown($pdo, $range_start, $range_end);
        break;

    case 'inventory':
        $data['low_stock']     = rpt_low_stock($pdo);
        break;

    case 'audit':
        $data['discounts']     = rpt_discount_audit($pdo, $range_start, $range_end);
        $data['payments']      = rpt_payment_reconciliation($pdo, $range_start, $range_end);
        break;
}

// ----------------------------------------------------------
// Pass context vars to view
// ----------------------------------------------------------
$ctx = [
    'tab'           => $tab,
    'filter_date'   => $filter_date,
    'filter_year'   => $filter_year,
    'filter_month'  => $filter_month,
    'range_start'   => $range_start,
    'range_end'     => $range_end,
    'data'          => $data,
    'baseUrl'       => $baseUrl,
];

// ----------------------------------------------------------
// Render view
// ----------------------------------------------------------
require_once __DIR__ . '/../views/cashier/view_reports.php';