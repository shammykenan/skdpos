<?php
/**
 * reports_view.php  – v11 (zreading tab added)
 */
extract($ctx);
$print_timestamp = (new DateTime('now', new DateTimeZone('Asia/Manila')))->format('M d, Y h:i A');
$months_list = [
    1=>'January',2=>'February',3=>'March',4=>'April',
    5=>'May',6=>'June',7=>'July',8=>'August',
    9=>'September',10=>'October',11=>'November',12=>'December'
];

function peso(float $v): string { return '₱' . number_format($v, 2); }
function pct_badge(float $change): string {
    $cls   = $change >= 0 ? 'badge-success' : 'badge-danger';
    $arrow = $change >= 0 ? '▲' : '▼';
    return '<span class="rpt-badge ' . $cls . '" style="font-size:.7rem;">' . $arrow . ' ' . abs($change) . '%</span>';
}

$tab_meta = $is_admin ? [
    'daily'       => ['fas fa-calendar-day',          'Daily'],
    'monthly'     => ['fas fa-calendar-alt',           'Monthly'],
    'yearly'      => ['fas fa-calendar',               'Yearly'],
    'products'    => ['fas fa-utensils',               'Products'],
    'staff'       => ['fas fa-users',                  'Staff'],
    'adminshift'  => ['fas fa-user-shield',            'My Shift'],
    'audit'       => ['fas fa-shield-alt',             'Audit'],
    'consumption' => ['fas fa-boxes',                  'Consumption'],
    'waste'       => ['fas fa-trash-alt',              'Waste'],
    'zreading'    => ['fas fa-file-invoice-dollar',    'Z-Reading'],
] : [
    'myshift'   => ['fas fa-user-clock',   'My Shift'],
    'closeout'  => ['fas fa-print',        'Close-out'],
    'inventory' => ['fas fa-boxes',        'Stock Alerts'],
    'waste'     => ['fas fa-trash-alt',    'Waste'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <?php require_once __DIR__ . "/../../core/csrfHelper.php"; echo csrf_head($baseUrl ?? ""); ?>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reports &ndash; The Lounge by: CJV & TEK</title>
<link rel="icon" type="image/png" href="<?= $baseUrl ?>/assets/images/logo/logo.png">
<link href="<?= $baseUrl ?>/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= $baseUrl ?>/assets/vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/common.css">
<link href="<?= $baseUrl ?>/assets/vendor/fonts/app.css" rel="stylesheet">
<script src="<?= $baseUrl ?>/assets/vendor/chartjs/chart.umd.min.js"></script>
<style>
/* ── TOKENS ─────────────────────────────────────────────────────────── */

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--light-bg); color: var(--dark); font-size: 14px; line-height: 1.6; -webkit-font-smoothing: antialiased; }

/* ── TOPBAR ─────────────────────────────────────────────────────────── */
.topbar {
    position: fixed; top: 0; left: 0; right: 0; height: var(--top-h);
    background: #fff; border-bottom: 1px solid var(--border);
    z-index: 1040; display: flex; align-items: center;
    padding: 0 1.5rem; gap: 1rem;
    box-shadow: 0 2px 8px rgba(15,76,92,0.06);
}
.topbar-brand { display: flex; align-items: center; gap: 0.65rem; text-decoration: none; flex-shrink: 0; }
.brand-logo {
    width: 40px; height: 40px; border-radius: var(--radius-sm);
    background: #fff; border: 1px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    overflow: hidden; flex-shrink: 0;
}
.brand-logo img { width: 100%; height: 100%; object-fit: contain; display: block; }
.brand-text-main { font-family: 'Fraunces', Georgia, serif; font-weight: 700; font-size: 1.15rem; color: var(--primary-dark); line-height: 1; white-space: nowrap; }
.brand-text-sub  { font-size: 0.65rem; color: var(--secondary); font-weight: 600; letter-spacing: 1px; text-transform: uppercase; margin-top: 2px; white-space: nowrap; }
.topbar-spacer { flex: 1; }
.mobile-toggle {
    display: none; background: none; border: 1px solid var(--border);
    border-radius: var(--radius-sm); padding: 0.4rem 0.65rem;
    color: var(--primary); cursor: pointer; font-size: 1rem;
    transition: background 0.15s;
}
.mobile-toggle:hover { background: var(--lavender); }
.user-pill {
    display: flex; align-items: center; gap: 0.5rem;
    background: var(--light-bg); border: 1px solid var(--border);
    border-radius: 40px; padding: 0.3rem 0.9rem 0.3rem 0.35rem;
    cursor: pointer; transition: all 0.15s; text-decoration: none; max-width: 220px;
}
.user-pill:hover { background: var(--lavender); border-color: var(--primary-light); }
.user-avatar {
    width: 30px; height: 30px; border-radius: 50%;
    background: var(--primary); color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.72rem; font-weight: 700; flex-shrink: 0;
}
.user-name-text { font-size: 0.82rem; font-weight: 600; color: var(--navy); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.user-caret { color: var(--warm-gray); font-size: 0.65rem; flex-shrink: 0; }

/* ── SIDEBAR ─────────────────────────────────────────────────────────── */
.sidebar {
    position: fixed; top: var(--top-h); left: 0; width: var(--sidebar-w);
    height: calc(100vh - var(--top-h)); background: #fff;
    border-right: 1px solid var(--border); overflow-y: auto;
    z-index: 1030; transition: transform 0.25s ease;
}
.sidebar::-webkit-scrollbar { width: 4px; }
.sidebar::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }
.nav-wrap { padding: 1.2rem 0.75rem 2rem; }
.nav-section { font-size: 0.65rem; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: var(--warm-gray); padding: 1.2rem 0.75rem 0.4rem; opacity: 0.7; }
.nav-link {
    display: flex; align-items: center; gap: 0.7rem;
    padding: 0.6rem 0.9rem; border-radius: var(--radius-md);
    color: var(--warm-gray); font-weight: 500; font-size: 0.88rem;
    text-decoration: none; transition: all 0.15s; margin: 0.1rem 0;
}
.nav-link i { width: 20px; text-align: center; font-size: 0.9rem; flex-shrink: 0; transition: color 0.15s; }
.nav-link:hover { background: var(--lavender); color: var(--primary-dark); }
.nav-link:hover i { color: var(--primary); }
.nav-link.active { background: var(--primary); color: #fff; font-weight: 600; box-shadow: 0 4px 12px rgba(15,76,92,0.2); }
.nav-link.active i { color: rgba(255,255,255,0.85); }
.sidebar-overlay {
    display: none; position: fixed; top: var(--top-h); left: 0; right: 0; bottom: 0;
    background: rgba(15,76,92,0.25); z-index: 1025; backdrop-filter: blur(2px);
}
.sidebar-overlay.show { display: block; }

/* ── MAIN ────────────────────────────────────────────────────────────── */
.main { margin-left: var(--sidebar-w); margin-top: var(--top-h); padding: 2rem 1.75rem; min-height: calc(100vh - var(--top-h)); }

/* ── PAGE HEADER ─────────────────────────────────────────────────────── */
.page-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; margin-bottom: 1.75rem; flex-wrap: wrap; }
.page-title { font-family: 'Fraunces', Georgia, serif; font-size: 1.75rem; font-weight: 700; color: var(--navy); line-height: 1.1; }
.breadcrumb-bar { display: flex; align-items: center; gap: 0.4rem; font-size: 0.8rem; color: var(--warm-gray); margin-top: 0.3rem; }
.breadcrumb-bar a { color: var(--primary-light); text-decoration: none; font-weight: 500; }
.breadcrumb-bar a:hover { text-decoration: underline; }
.breadcrumb-sep { color: var(--border); }

/* ── DROPDOWN ────────────────────────────────────────────────────────── */
.dropdown-menu { border-radius: var(--radius-lg); border: 1px solid var(--border); box-shadow: 0 12px 30px rgba(0,0,0,0.08); padding: 0.5rem; }
.dropdown-item { border-radius: var(--radius-sm); font-size: 0.88rem; padding: 0.55rem 1rem; }
.dropdown-item:hover { background: var(--lavender); color: var(--primary-dark); }
.dropdown-item.text-danger:hover { background: rgba(214,64,69,0.08); color: var(--danger); }

/* ── REPORT TABS ─────────────────────────────────────────────────────── */
.report-tabs-wrap {
    background: #fff; border: 1px solid rgba(203,213,224,0.6);
    border-radius: var(--radius-xl); padding: 0.75rem 1rem;
    margin-bottom: 1.75rem;
    box-shadow: 0 4px 12px rgba(15,76,92,0.03);
    display: flex; gap: 0.4rem;
}
@media (max-width: 767.98px) {
    .report-tabs-wrap {
        flex-wrap: nowrap; overflow-x: auto; white-space: nowrap;
        -webkit-overflow-scrolling: touch; scrollbar-width: thin; padding-bottom: 0.5rem;
    }
    .report-tab { flex: 0 0 auto; }
}
.report-tab {
    padding: 0.5rem 1.1rem; border-radius: 40px;
    font-size: 0.82rem; font-weight: 600; cursor: pointer;
    border: 1.5px solid transparent; color: var(--warm-gray);
    background: transparent; text-decoration: none;
    transition: all 0.18s; display: flex; align-items: center; gap: 0.4rem;
    white-space: nowrap;
}
.report-tab:hover { background: var(--lavender); color: var(--primary-dark); border-color: var(--primary-light); }
.report-tab.active { background: var(--primary); color: #fff; border-color: var(--primary); box-shadow: 0 4px 12px rgba(15,76,92,0.2); }
.report-tab.waste-tab.active    { background: var(--danger); border-color: var(--danger); box-shadow: 0 4px 12px rgba(214,64,69,0.2); }
.report-tab.zreading-tab.active { background: var(--navy);   border-color: var(--navy); }
.report-tab.closeout-tab.active,
.report-tab.adminshift-tab.active { background: var(--navy); border-color: var(--navy); }
.report-tab.cashier-tab.active  { background: var(--secondary); border-color: var(--secondary); }

/* ── FILTER BAR ──────────────────────────────────────────────────────── */
.filter-bar {
    background: #fff; border: 1px solid rgba(203,213,224,0.6);
    border-radius: var(--radius-xl); padding: 1rem 1.4rem;
    margin-bottom: 1.75rem; display: flex; align-items: center;
    gap: 0.9rem; flex-wrap: wrap;
    box-shadow: 0 4px 12px rgba(15,76,92,0.03);
}
.filter-bar label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: var(--warm-gray); margin-bottom: 0; flex-shrink: 0; }
.filter-bar .form-control,
.filter-bar .form-select {
    border: 1px solid var(--border); border-radius: var(--radius-md);
    font-size: 0.88rem; padding: 0.5rem 0.85rem; font-family: inherit;
    background: var(--light-bg); color: var(--dark);
    transition: border-color 0.15s, box-shadow 0.15s;
}
.filter-bar .form-control:focus,
.filter-bar .form-select:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(15,76,92,0.1); background: #fff; }
.filter-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-left: auto; }
.filter-input-date   { width: 165px; }
.filter-input-month  { width: 145px; }
.filter-input-year   { width: 95px; }
.filter-input-range  { width: 155px; }

/* ── BUTTONS ─────────────────────────────────────────────────────────── */
.btn-apply, .btn-print, .btn-co-print {
    padding: 0.5rem 1.3rem; border-radius: 40px; font-size: 0.85rem; font-weight: 600;
    display: inline-flex; align-items: center; gap: 0.4rem;
    transition: all 0.18s; cursor: pointer; font-family: inherit;
    white-space: nowrap; border: none;
}
.btn-apply { background: var(--primary); color: #fff; }
.btn-apply:hover { background: var(--primary-dark); transform: translateY(-1px); box-shadow: 0 6px 14px rgba(15,76,92,0.2); }
.btn-print { background: transparent; border: 1.5px solid var(--primary); color: var(--primary); }
.btn-print:hover { background: var(--primary); color: #fff; }
.btn-co-print { background: var(--primary); color: #fff; }
.btn-co-print.thermal { background: var(--navy); }
.btn-co-print:hover { background: var(--primary-dark); transform: translateY(-1px); color: #fff; }
.btn-generate { background: var(--navy); color: #fff; }
.btn-generate:hover { background: var(--primary-dark); transform: translateY(-1px); }

/* ── STAT CARDS ──────────────────────────────────────────────────────── */
.stats-grid {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1rem; margin-bottom: 1.75rem;
}
.stat-card {
    background: #fff; border: 1px solid rgba(203,213,224,0.6);
    border-radius: var(--radius-lg); padding: 1.25rem 1.35rem;
    display: flex; align-items: center; gap: 1rem;
    position: relative; overflow: hidden;
    transition: box-shadow 0.2s, transform 0.2s;
}
.stat-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: var(--primary); }
.stat-card.c-coral::before   { background: var(--secondary); }
.stat-card.c-teal::before    { background: var(--info); }
.stat-card.c-green::before   { background: var(--success); }
.stat-card.c-warning::before { background: var(--warning); }
.stat-card.c-danger::before  { background: var(--danger); }
.stat-card.c-netprofit::before { background: linear-gradient(90deg, var(--success), var(--info)); }
.stat-card:hover { box-shadow: 0 8px 24px rgba(15,76,92,0.08); transform: translateY(-2px); }
.s-icon {
    width: 46px; height: 46px; border-radius: var(--radius-md);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; flex-shrink: 0;
    background: #f0f5fa; color: var(--primary);
    border: 1px solid rgba(15,76,92,0.1);
}
.s-label { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: var(--warm-gray); margin-bottom: 0.2rem; }
.s-value {
    font-size: 1.55rem; font-weight: 800; color: var(--navy); line-height: 1.2;
    word-break: break-word; overflow-wrap: break-word; hyphens: auto;
}
.s-value.negative { color: var(--danger); }
.s-value.positive { color: var(--success); }
.s-sub { font-size: 0.78rem; color: var(--warm-gray); margin-top: 0.3rem; }
.s-compare { font-size: 0.75rem; margin-top: 0.25rem; }
.profit-breakdown {
    font-size: 0.74rem; color: var(--warm-gray); margin-top: 0.3rem;
    padding: 0.28rem 0.55rem; background: rgba(15,76,92,0.04);
    border-radius: 6px; border-left: 2px solid var(--primary-light);
}
.profit-breakdown .pos { font-weight: 600; color: var(--success); }
.profit-breakdown .neg { font-weight: 600; color: var(--danger); }
.waste-cost-cell { color: var(--danger); font-weight: 600; }

/* ── COMPARISON GRID ─────────────────────────────────────────────────── */
.comparison-grid {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
    gap: 1rem; margin-bottom: 1.75rem;
}
.compare-card {
    background: #fff; border-radius: var(--radius-lg); padding: 1rem 1.2rem;
    border: 1px solid rgba(203,213,224,0.6); box-shadow: 0 4px 10px rgba(15,76,92,0.03);
}
.compare-label { font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.7px; color: var(--warm-gray); margin-bottom: 0.3rem; }
.compare-current { font-size: 1.2rem; font-weight: 700; color: var(--navy); }
.compare-prev { font-size: 0.75rem; color: var(--warm-gray); margin-top: 0.15rem; }
.variance-positive { color: var(--success); font-weight: 700; }
.variance-negative { color: var(--danger); font-weight: 700; }
.variance-zero     { color: var(--warm-gray); font-weight: 600; }

/* ── REPORT CARDS ────────────────────────────────────────────────────── */
.rpt-card {
    background: #fff; border: 1px solid rgba(203,213,224,0.6);
    border-radius: var(--radius-xl); overflow: hidden;
    box-shadow: 0 4px 16px rgba(15,76,92,0.03);
    margin-bottom: 1.5rem;
}
.rpt-card-header {
    padding: 1rem 1.4rem; border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;
}
.rpt-card-title { font-size: 0.92rem; font-weight: 700; color: var(--navy); display: flex; align-items: center; gap: 0.5rem; }
.rpt-card-title i { color: var(--secondary); }
.rpt-card-body { padding: 1.25rem 1.4rem; }
.rpt-card-body.p-0 { padding: 0; }

/* ── TABLES ──────────────────────────────────────────────────────────── */
.rpt-table { width: 100%; border-collapse: collapse; }
.rpt-table thead th {
    background: #f2f6fc; border-bottom: 2px solid var(--primary-light);
    font-size: 0.7rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.7px; color: var(--primary-dark);
    padding: 0.85rem 1.1rem; white-space: nowrap;
}
.rpt-table tbody td {
    padding: 0.9rem 1.1rem; vertical-align: middle;
    border-bottom: 1px solid #edf2f7; color: var(--dark); background: #fff;
}
.rpt-table tbody tr:last-child td { border-bottom: none; }
.rpt-table tbody tr:hover td { background: #fafcff; }
.rpt-table tfoot td {
    padding: 0.9rem 1.1rem; font-weight: 700;
    background: #f2f6fc; border-top: 2px solid var(--primary-light);
}
.rpt-table thead th.th-waste { background: rgba(214,64,69,0.06); color: var(--danger); }
@media (max-width: 767.98px) {
    .rpt-table th:first-child, .rpt-table td:first-child { position: sticky; left: 0; background: inherit; z-index: 1; }
    .rpt-table thead th:first-child { background: #f2f6fc; z-index: 2; }
}

/* ── Z-READING SPECIFIC ──────────────────────────────────────────────── */
.z-summary-cards {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
    gap: 1rem; margin-bottom: 1.5rem;
}
.z-locked-badge {
    display: inline-flex; align-items: center; gap: 0.4rem;
    background: var(--success-soft); color: var(--success);
    border: 1px solid rgba(44,122,90,0.25); border-radius: 40px;
    padding: 0.3rem 0.9rem; font-size: 0.8rem; font-weight: 700;
}

/* ── BADGES ──────────────────────────────────────────────────────────── */
.rpt-badge { padding: 0.28rem 0.75rem; border-radius: 30px; font-size: 0.72rem; font-weight: 600; display: inline-block; }
.badge-primary { background: var(--primary); color: #fff; }
.badge-warning { background: #fff3cd; color: #856404; }
.badge-danger  { background: #f8d7da; color: #721c24; }
.badge-success { background: #d4edda; color: #155724; }
.badge-info    { background: #cff4fc; color: #0c5460; }
.badge-variant { background: rgba(255,107,74,0.1); color: var(--secondary); border: 1px solid rgba(255,107,74,0.25); padding: 0.18rem 0.5rem; border-radius: 20px; font-size: 0.68rem; font-weight: 600; }
.badge-base    { background: rgba(15,76,92,0.08); color: var(--primary-dark); border: 1px solid rgba(15,76,92,0.15); padding: 0.18rem 0.5rem; border-radius: 20px; font-size: 0.68rem; font-weight: 600; }
.admin-proc-badge { display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.18rem 0.55rem; border-radius: 20px; font-size: 0.68rem; font-weight: 600; background: rgba(15,76,92,0.08); color: var(--primary-dark); }

/* ── MISC ────────────────────────────────────────────────────────────── */
.mini-bar { height: 4px; border-radius: 6px; background: #e2e8f0; margin-top: 4px; }
.mini-bar-fill { height: 100%; border-radius: 6px; background: var(--primary); }
.no-data { text-align: center; padding: 3rem 1rem; color: var(--warm-gray); }
.no-data i { font-size: 2.2rem; margin-bottom: 0.7rem; opacity: 0.25; display: block; }
.consumption-bar { height: 6px; border-radius: 6px; background: #e2e8f0; margin-top: 3px; }
.consumption-bar-fill { height: 100%; border-radius: 6px; background: var(--secondary); }
.reason-pill { display: inline-block; padding: 0.18rem 0.55rem; border-radius: 20px; font-size: 0.7rem; font-weight: 600; background: rgba(214,64,69,0.08); color: var(--danger); border: 1px solid rgba(214,64,69,0.2); max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

/* ── CLOSE-OUT / ADMIN SHIFT ─────────────────────────────────────────── */
.co-wrap { max-width: 980px; margin: 0 auto; }
.co-hero {
    background: linear-gradient(135deg, var(--primary-dark) 0%, #0d3d50 55%, #102a37 100%);
    border-radius: var(--radius-xl); padding: 1.8rem 2.2rem; color: #fff;
    display: flex; align-items: center; justify-content: space-between;
    gap: 1.5rem; flex-wrap: wrap;
    box-shadow: 0 16px 40px rgba(15,76,92,0.25);
    position: relative; overflow: hidden; margin-bottom: 1.5rem;
}
.co-hero::after { content: '₱'; position: absolute; right: -10px; top: -20px; font-size: 9rem; font-weight: 900; color: rgba(255,255,255,0.04); line-height: 1; pointer-events: none; user-select: none; }
.co-hero-left { flex: 1; min-width: 0; }
.co-hero-label { font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1.4px; color: rgba(255,255,255,0.55); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.4rem; }
.co-hero-amount { font-size: clamp(2rem, 6vw, 3.2rem); font-weight: 800; letter-spacing: -0.5px; line-height: 1; color: #fff; word-break: break-all; }
.co-hero-sub { font-size: 0.78rem; color: rgba(255,255,255,0.5); margin-top: 0.5rem; }
.co-hero-badge { background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.18); border-radius: var(--radius-md); padding: 0.75rem 1.3rem; text-align: center; flex-shrink: 0; }
.co-hero-badge .hb-val { font-size: 1.55rem; font-weight: 700; color: var(--accent); display: block; }
.co-hero-badge .hb-lbl { font-size: 0.66rem; color: rgba(255,255,255,0.5); text-transform: uppercase; letter-spacing: 0.8px; }
.co-waste-strip { background: rgba(214,64,69,0.07); border: 1.5px solid rgba(214,64,69,0.25); border-radius: var(--radius-lg); padding: 1rem 1.4rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
.co-waste-strip .ws-label { font-size: 0.8rem; font-weight: 700; color: var(--danger); display: flex; align-items: center; gap: 0.4rem; }
.co-waste-strip .ws-amount { font-size: 1.25rem; font-weight: 700; color: var(--danger); }
.co-cols { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.5rem; }
@media (max-width: 860px) { .co-cols { grid-template-columns: 1fr; } }
.co-section { background: #fff; border-radius: var(--radius-xl); border: 1px solid rgba(203,213,224,0.6); box-shadow: 0 4px 12px rgba(15,76,92,0.04); }
.co-section-head { padding: 0.85rem 1.2rem; border-bottom: 1px solid rgba(203,213,224,0.4); font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: var(--primary-dark); display: flex; align-items: center; gap: 0.4rem; }
.co-section-head i { color: var(--secondary); font-size: 0.85rem; }
.co-section-body { padding: 0.9rem 1.2rem; }
.co-row { display: flex; align-items: baseline; justify-content: space-between; padding: 0.45rem 0; border-bottom: 1px solid rgba(203,213,224,0.25); gap: 0.5rem; }
.co-row:last-child { border-bottom: none; }
.co-row .cr-label { font-size: 0.85rem; color: var(--warm-gray); flex: 1; }
.co-row .cr-value { font-size: 0.88rem; font-weight: 600; color: var(--dark); text-align: right; white-space: nowrap; }
.co-row .cr-value.muted { color: var(--warm-gray); font-weight: 400; font-size: 0.8rem; }
.co-row .cr-value.neg   { color: var(--danger); }
.co-items-table { width: 100%; border-collapse: collapse; font-size: 0.83rem; }
.co-items-table td { padding: 0.42rem 0.2rem; border-bottom: 1px solid rgba(203,213,224,0.25); vertical-align: middle; }
.co-items-table tr:last-child td { border-bottom: none; }
.co-items-table .rank  { font-size: 0.68rem; font-weight: 700; color: var(--warm-gray); width: 18px; }
.co-items-table .iname { color: var(--dark); font-weight: 500; }
.co-items-table .iname small { display: block; font-size: 0.7rem; color: var(--warm-gray); font-weight: 400; }
.co-items-table .iqty  { text-align: right; font-weight: 700; color: var(--primary); white-space: nowrap; }
.co-items-table .irev  { text-align: right; color: var(--success); font-weight: 600; white-space: nowrap; font-size: 0.8rem; }
.co-peak-bars { display: flex; align-items: flex-end; gap: 5px; height: 78px; padding-top: 4px; }
.co-peak-bar-wrap { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 3px; height: 100%; justify-content: flex-end; }
.co-peak-bar { width: 100%; border-radius: 4px 4px 0 0; background: rgba(15,76,92,0.5); transition: background 0.2s; min-height: 3px; }
.co-peak-bar.peak { background: var(--secondary); }
.co-peak-lbl   { font-size: 0.58rem; color: var(--warm-gray); white-space: nowrap; }
.co-peak-count { font-size: 0.6rem; font-weight: 700; color: var(--primary-dark); }
.co-sig-row { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 1.5rem; padding-top: 1.2rem; border-top: 2px dashed var(--border); }
@media (max-width: 480px) { .co-sig-row { grid-template-columns: 1fr; gap: 1rem; } }
.co-sig-block .sig-line { border-bottom: 1.5px solid var(--dark); height: 34px; margin-bottom: 0.35rem; }
.co-sig-block .sig-lbl  { font-size: 0.72rem; color: var(--warm-gray); text-align: center; }

/* ── FOOTER ──────────────────────────────────────────────────────────── */
.site-footer { background: #fff; border-top: 1px solid var(--border); padding: 1.2rem 1.75rem; margin-left: var(--sidebar-w); }
.site-footer-inner { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; font-size: 0.82rem; color: var(--warm-gray); }

/* ── ANIMATIONS ──────────────────────────────────────────────────────── */
.fade-in { animation: fadeUp 0.35s ease forwards; }
@keyframes fadeUp { from { opacity: 0.4; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
@media (prefers-reduced-motion: reduce) { .fade-in, .stat-card, .report-tab { animation: none; transition: none; } }

/* ── STAT CARDS GRID ── */
.stats-grid { display: grid; gap: 1rem; margin-bottom: 1.75rem; }
@media (min-width: 1000px)  { .stats-grid { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 999.98px){ .stats-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 480px)   { .stats-grid { grid-template-columns: 1fr; } }

/* ── RESPONSIVE ──────────────────────────────────────────────────────── */
@media (max-width: 1199.98px) { .stats-grid { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 991.98px) {
    .sidebar { transform: translateX(-100%); }
    .sidebar.show { transform: translateX(0); }
    .main { margin-left: 0; padding: 1.5rem 1.25rem; }
    .mobile-toggle { display: flex; align-items: center; }
    .site-footer { margin-left: 0; }
}
@media (min-width: 768px) and (max-width: 1024px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
    .report-tab i { display: none; }
    .report-tab { font-size: 0.78rem; padding: 0.42rem 0.85rem; }
    .filter-input-date, .filter-input-range { width: 140px; }
    .filter-input-month { width: 130px; }
}
@media (max-width: 767.98px) {
    .main { padding: 1rem; }
    .stats-grid { grid-template-columns: 1fr 1fr; gap: 0.75rem; }
    .stat-card { padding: 0.9rem 1rem; gap: 0.75rem; }
    .s-value { font-size: 1.2rem; }
    .s-icon { width: 38px; height: 38px; font-size: 0.9rem; }
    .page-title { font-size: 1.4rem; }
    .filter-bar { flex-direction: column; align-items: stretch; gap: 0.6rem; padding: 0.9rem 1rem; }
    .filter-input-date, .filter-input-month, .filter-input-year, .filter-input-range,
    .filter-bar .form-control, .filter-bar .form-select { width: 100% !important; }
    .filter-actions { width: 100%; margin-left: 0; }
    .filter-actions .btn-apply, .filter-actions .btn-print, .filter-actions .btn-co-print { flex: 1; justify-content: center; }
    .rpt-card-header { padding: 0.85rem 1rem; }
    .rpt-card-body { padding: 0.9rem 1rem; }
    .rpt-card-body.p-0 { padding: 0; }
    .co-hero { padding: 1.3rem 1.4rem; }
    .co-hero::after { display: none; }
    .site-footer { padding: 1rem 1.2rem; }
    .site-footer-inner { flex-direction: column; align-items: flex-start; gap: 0.2rem; }
    .comparison-grid { grid-template-columns: 1fr 1fr; }
    .z-summary-cards { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 480px) {
    .topbar { padding: 0 1rem; }
    .brand-text-sub { display: none; }
    .stats-grid { grid-template-columns: 1fr; }
    .comparison-grid { grid-template-columns: 1fr; }
    .report-tab { padding: 0.35rem 0.6rem; font-size: 0.72rem; }
    .page-title { font-size: 1.2rem; }
    .co-hero-badge { width: 100%; display: flex; align-items: center; justify-content: space-between; padding: 0.55rem 1rem; }
    .co-hero-badge .hb-val { font-size: 1.2rem; }
    .btn-apply, .btn-print, .btn-co-print { padding: 0.6rem 1rem; min-height: 44px; }
    .report-tab { min-height: 44px; display: inline-flex; align-items: center; }
    .z-summary-cards { grid-template-columns: 1fr; }
}
@media (min-width: 992px) and (max-width: 1366px) {
    .s-value { font-size: 1.3rem; }
    .stat-card { padding: 1rem; gap: 0.8rem; }
    .s-icon { width: 40px; height: 40px; font-size: 1rem; }
}
@media (pointer: coarse) {
    .btn-apply, .btn-print, .report-tab, .filter-bar .form-control, .stat-card { cursor: pointer; touch-action: manipulation; }
}
@media (min-width: 1000px) {
    .rpt-table { table-layout: fixed; width: 100%; }
    .rpt-table td, .rpt-table th { overflow-wrap: break-word; word-break: break-word; white-space: normal; padding: 0.6rem 0.5rem; }
    .rpt-table.cashier-table th:first-child, .rpt-table.cashier-table td:first-child { width: 20%; }
    .rpt-table.cashier-table th:nth-child(2), .rpt-table.cashier-table td:nth-child(2) { width: 8%; text-align: center; }
    .rpt-table.cashier-table th:nth-child(3), .rpt-table.cashier-table td:nth-child(3) { width: 18%; }
    .rpt-table.cashier-table th:nth-child(4), .rpt-table.cashier-table td:nth-child(4) { width: 12%; }
    .rpt-table.cashier-table th:nth-child(5), .rpt-table.cashier-table td:nth-child(5) { width: 12%; }
    .rpt-table.cashier-table th:nth-child(6), .rpt-table.cashier-table td:nth-child(6) { width: 12%; }
    .rpt-table.cashier-table th:nth-child(7), .rpt-table.cashier-table td:nth-child(7) { width: 18%; }
}
.s-value { overflow-wrap: break-word; hyphens: auto; }
</style>
</head>
<body>

<!-- TOPBAR -->
<header class="topbar">
    <button class="mobile-toggle" id="menuToggle" aria-label="Toggle menu">
        <i class="fas fa-bars"></i>
    </button>
    <a href="<?= $baseUrl ?>/index.php?page=<?= $is_admin ? 'admin_dashboard' : 'manage_pos' ?>" class="topbar-brand">
        <div class="brand-logo">
            <img src="<?= $baseUrl ?>/assets/images/logo/logo.png" alt="The Lounge by: CJV & TEK Logo">
        </div>
        <div>
            <div class="brand-text-main">The Lounge by: CJV & TEK</div>
            <div class="brand-text-sub">Chill. Coffee. Dine.</div>
        </div>
    </a>
    <div class="topbar-spacer"></div>
    <div class="dropdown">
        <button class="user-pill dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border:none;">
            <?php
                $initials = 'U';
                if (!empty($session_name)) {
                    $parts    = explode(' ', trim($session_name));
                    $initials = strtoupper(substr($parts[0], 0, 1));
                    if (count($parts) > 1) $initials .= strtoupper(substr(end($parts), 0, 1));
                }
            ?>
            <span class="user-avatar"><?= htmlspecialchars($initials) ?></span>
            <span class="user-name-text"><?= htmlspecialchars($session_name) ?></span>
            <i class="fas fa-chevron-down user-caret"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li>
                <div class="px-3 py-2" style="border-bottom:1px solid var(--border);margin-bottom:0.4rem;">
                    <div style="font-weight:700;font-size:0.9rem;color:var(--navy);"><?= htmlspecialchars($session_name) ?></div>
                    <div style="font-size:0.78rem;color:var(--warm-gray);"><?= $is_admin ? 'Administrator' : 'Cashier' ?></div>
                </div>
            </li>
            <li><a class="dropdown-item text-danger" href="<?= $baseUrl ?>/index.php?page=logout">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
            </a></li>
        </ul>
    </div>
</header>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- SIDEBAR -->
<nav class="sidebar" id="sidebar" aria-label="Main navigation">
    <div class="nav-wrap">
        <?php if (hasRole('admin')): ?>
        <div class="nav-section">Dashboard</div>
        <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <div class="nav-section">Management</div>
        <a href="<?= $baseUrl ?>/index.php?page=manage_menu"        class="nav-link"><i class="fas fa-utensils"></i> Menu Items</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_recipes"     class="nav-link"><i class="fas fa-tags"></i> Recipes</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_ingredients" class="nav-link"><i class="fas fa-boxes"></i> Ingredients</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_addons"      class="nav-link"><i class="fas fa-plus-circle"></i> Add-ons</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_tables"      class="nav-link"><i class="fas fa-chair"></i> Tables</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_users"       class="nav-link"><i class="fas fa-users"></i> User Management</a>
        <div class="nav-section">Sales</div>
        <a href="<?= $baseUrl ?>/index.php?page=manage_pos"    class="nav-link"><i class="fas fa-cash-register"></i> POS System</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_orders" class="nav-link"><i class="fas fa-receipt"></i> Orders</a>
        <a href="<?= $baseUrl ?>/index.php?page=reports"       class="nav-link active"><i class="fas fa-chart-bar"></i> Reports</a>
        <div class="nav-section">Configuration</div>
        <a href="<?= $baseUrl ?>/index.php?page=bir_settings" class="nav-link"><i class="fa-solid fa-building"></i>BIR Settings</a>
        <?php endif; ?>
        <?php if (hasRole('cashier') && !hasRole('admin')): ?>
        <div class="nav-section">Management</div>
        <a href="<?= $baseUrl ?>/index.php?page=manage_tables" class="nav-link"><i class="fas fa-chair"></i> Tables</a>
        <div class="nav-section">Sales</div>
        <a href="<?= $baseUrl ?>/index.php?page=manage_pos"    class="nav-link"><i class="fas fa-cash-register"></i> POS System</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_orders" class="nav-link"><i class="fas fa-receipt"></i> Orders</a>
        <a href="<?= $baseUrl ?>/index.php?page=reports"       class="nav-link active"><i class="fas fa-chart-bar"></i> Reports</a>
        <?php endif; ?>
    </div>
</nav>

<!-- MAIN -->
<main class="main">

    <div class="page-head">
        <div>
            <h1 class="page-title"><?= $is_admin ? 'Reports' : 'My Reports' ?></h1>
            <div class="breadcrumb-bar">
                <a href="<?= $baseUrl ?>/index.php?page=<?= $is_admin ? 'admin_dashboard' : 'manage_pos' ?>">Home</a>
                <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:0.6rem;"></i></span>
                <span>Reports</span>
            </div>
        </div>
        <?php if (!$is_admin && $tab === 'myshift'): ?>
        <div class="stat-card" style="padding:0.65rem 1.1rem;min-width:185px;border-left:3px solid var(--warning);">
            <div style="display:flex;align-items:center;gap:0.55rem;">
                <div class="s-icon" style="width:36px;height:36px;font-size:0.9rem;background:var(--warning-soft);color:var(--warning);border-color:rgba(244,185,66,0.2);">
                    <i class="fas fa-cash-register"></i>
                </div>
                <div>
                    <div class="s-label" style="margin-bottom:0;">Cash to Turn Over</div>
                    <div style="font-size:1rem;font-weight:700;color:var(--dark);"><?= peso((float)($data['summary']['net_profit']??0)) ?></div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- TABS -->
    <div class="report-tabs-wrap">
        <?php foreach ($tab_meta as $key => [$icon, $label]): ?>
        <a href="<?= $baseUrl ?>/index.php?page=reports&tab=<?= $key ?>"
           class="report-tab
               <?= $tab===$key ? 'active' : '' ?>
               <?= $key==='waste'      ? 'waste-tab'      : '' ?>
               <?= $key==='closeout'   ? 'closeout-tab'   : '' ?>
               <?= $key==='adminshift' ? 'adminshift-tab' : '' ?>
               <?= $key==='zreading'   ? 'zreading-tab'   : '' ?>
               <?= (!$is_admin && !in_array($key,['waste','closeout','adminshift','zreading'])) ? 'cashier-tab' : '' ?>
           ">
            <i class="<?= $icon ?>"></i> <?= $label ?>
        </a>
        <?php endforeach; ?>
    </div>

    <?php
    // ── Shared helpers ─────────────────────────────────────────────────────

    function render_comparison(array $cmp, string $prev_label): void {
        if (empty($cmp['current'])) return;
        $cur  = $cmp['current'];
        $prev = $cmp['previous'];
        $chg  = $cmp['change'];
        $metrics = [
            'gross_revenue' => ['Gross Sales', fn($v) => peso((float)$v)],
            'net_revenue'   => ['Net Sales',   fn($v) => peso((float)$v)],
            'gross_profit'  => ['Gross Profit',  fn($v) => peso((float)$v)],
            'net_profit'    => ['Net Profit',    fn($v) => peso((float)$v)],
        ];
        echo '<div class="comparison-grid fade-in">';
        foreach ($metrics as $key => [$label, $fmt]) {
            $badge = isset($chg[$key]) ? pct_badge((float)$chg[$key]) : '';
            echo '<div class="compare-card">';
            echo '<div class="compare-label">' . $label . '</div>';
            echo '<div class="compare-current">' . $fmt($cur[$key] ?? 0) . ' ' . $badge . '</div>';
            echo '<div class="compare-prev">vs ' . $prev_label . ': ' . $fmt($prev[$key] ?? 0) . '</div>';
            echo '</div>';
        }
        echo '</div>';
    }

    function render_top_items_table(array $items, bool $show_category = true, bool $show_profit = true, bool $show_excl_vat = false): void {
        if (empty($items)) { echo '<div class="no-data"><i class="fas fa-inbox"></i> No sales data.</div>'; return; }
        echo '<div style="overflow-x:auto;"><table class="rpt-table"><thead><tr>';
        echo '<th>#</th><th>Item</th>';
        if ($show_category) echo '<th>Category</th>';
        echo '<th>Sold</th><th>Revenue</th>';
        if ($show_profit)   echo '<th>Gross Profit</th>';
        if ($show_excl_vat) echo '<th>Excl. VAT</th>';
        echo '</tr></thead><tbody>';
        foreach ($items as $i => $row) {
            $has_variants = (int)($row['has_variants'] ?? 0);
            echo '<tr>';
            echo '<td>' . ($i + 1) . '</td>';
            echo '<td><strong>' . htmlspecialchars($row['item_name']) . '</strong>';
            if ($has_variants) echo ' <span class="badge-variant">variants</span>';
            echo '</td>';
            if ($show_category) echo '<td><span class="rpt-badge badge-primary">' . htmlspecialchars($row['category_name']) . '</span></td>';
            echo '<td>' . number_format($row['units_sold']) . '</td>';
            echo '<td>' . peso((float)$row['total_revenue']) . '</td>';
            if ($show_profit)   echo '<td style="color:var(--success);font-weight:600;">' . peso((float)$row['gross_profit']) . '</td>';
            if ($show_excl_vat) echo '<td style="color:var(--warm-gray);font-size:.85rem;">' . peso((float)($row['revenue_excl_vat'] ?? 0)) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table></div>';
    }

    function render_waste_card(array $s): void {
        $waste = (float)($s['total_waste_cost'] ?? 0);
        echo '
        <div class="stat-card c-danger">
            <div class="s-icon" style="background:rgba(214,64,69,0.08);color:var(--danger);border-color:rgba(214,64,69,0.15);"><i class="fas fa-trash-alt"></i></div>
            <div>
                <div class="s-label">Total Waste Cost</div>
                <div class="s-value" style="color:var(--danger);">' . peso($waste) . '</div>
                <div class="s-sub">From voided and refunded items</div>
            </div>
        </div>';
    }

    function render_net_profit_card(array $s): void {
        $gross_profit = (float)($s['gross_profit'] ?? 0);
        $net_sales    = (float)($s['net_revenue'] ?? 0);
        $cogs         = (float)($s['cogs'] ?? 0);
        $waste_cost   = (float)($s['total_waste_cost'] ?? 0);
        $net_profit   = (float)($s['net_profit'] ?? ($net_sales - $cogs - $waste_cost));
        $cls = $net_profit >= 0 ? 'positive' : 'negative';
        echo '
        <div class="stat-card c-netprofit">
            <div class="s-icon" style="background:rgba(44,122,90,0.08);color:var(--success);border-color:rgba(44,122,90,0.15);"><i class="fas fa-chart-line"></i></div>
            <div>
                <div class="s-label">Net Profit</div>
                <div class="s-value ' . $cls . '">' . peso($net_profit) . '</div>
                <div class="profit-breakdown">
                    Net Sales: <span class="pos">' . peso($net_sales) . '</span>
                    &minus; COGS: <span class="neg">' . peso($cogs) . '</span>
                    &minus; Waste: <span class="neg">' . peso($waste_cost) . '</span>
                </div>
            </div>
        </div>';
    }

    function render_closeout_tab(array $co, array $co_items, array $co_hours, string $filter_date, string $thermal_payload_var, bool $is_admin_shift = false): void {
        $now = new DateTime('now', new DateTimeZone('Asia/Manila'));
    ?>
        <div class="co-hero">
            <div class="co-hero-left">
                <div class="co-hero-label"><i class="fas fa-hand-holding-usd"></i> Cash to Turn Over</div>
                <div class="co-hero-amount">₱<?= number_format((float)($co['cash_to_turn_over'] ?? 0), 2) ?></div>
                <div class="co-hero-sub">
                    <?= date('F d, Y', strtotime($filter_date)) ?> &nbsp;·&nbsp;
                    <?= htmlspecialchars($co['cashier_name'] ?? '—') ?>
                </div>
            </div>
            <div class="co-hero-badge">
                <span class="hb-val"><?= number_format((int)($co['total_orders'] ?? 0)) ?></span>
                <span class="hb-lbl">Orders</span>
            </div>
            <div class="co-hero-badge">
                <span class="hb-val"><?= number_format((int)($co['payment_count'] ?? 0)) ?></span>
                <span class="hb-lbl">Payments</span>
            </div>
        </div>

        <?php if ((float)($co['total_waste_cost'] ?? 0) > 0): ?>
        <div class="co-waste-strip">
            <div class="ws-label"><i class="fas fa-trash-alt"></i> Waste Written Off This Shift</div>
            <div class="ws-amount">₱<?= number_format((float)$co['total_waste_cost'], 2) ?></div>
        </div>
        <?php endif; ?>

        <div class="co-cols">
            <div class="co-section">
                <div class="co-section-head"><i class="fas fa-user-circle"></i> <?= $is_admin_shift ? 'Admin' : 'Cashier' ?> Info</div>
                <div class="co-section-body">
                    <div class="co-row"><span class="cr-label"><?= $is_admin_shift ? 'Admin' : 'Cashier' ?></span><span class="cr-value"><?= htmlspecialchars($co['cashier_name'] ?? '—') ?></span></div>
                    <div class="co-row"><span class="cr-label">Username</span><span class="cr-value muted"><?= htmlspecialchars($co['username'] ?? '—') ?></span></div>
                    <div class="co-row"><span class="cr-label">Shift Date</span><span class="cr-value"><?= date('F d, Y', strtotime($filter_date)) ?></span></div>
                    <div class="co-row"><span class="cr-label">Report Printed</span><span class="cr-value muted"><?= $now->format('h:i A') ?> (PHT)</span></div>
                </div>
            </div>
            <div class="co-section">
                <div class="co-section-head"><i class="fas fa-receipt"></i> Order Summary</div>
                <div class="co-section-body">
                    <div class="co-row"><span class="cr-label">Orders Handled</span><span class="cr-value"><?= number_format((int)($co['total_orders'] ?? 0)) ?></span></div>
                    <div class="co-row"><span class="cr-label">Cancelled Orders</span><span class="cr-value <?= ($co['cancelled_orders'] ?? 0) > 0 ? 'neg' : '' ?>"><?= (int)($co['cancelled_orders'] ?? 0) ?></span></div>
                    <div class="co-row"><span class="cr-label">Payment Transactions</span><span class="cr-value"><?= (int)($co['payment_count'] ?? 0) ?></span></div>
                    <?php if (($co['admin_processed_count'] ?? 0) > 0): ?>
                    <div class="co-row"><span class="cr-label" style="color:var(--primary-light);">Admin-Processed</span><span class="cr-value" style="color:var(--primary-light);"><i class="fas fa-shield-alt" style="font-size:.68rem;"></i> <?= (int)$co['admin_processed_count'] ?></span></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="co-cols">
            <div class="co-section">
                <div class="co-section-head"><i class="fas fa-chart-bar"></i> Sales Breakdown</div>
                <div class="co-section-body">
                    <div class="co-row"><span class="cr-label">Gross Sales</span><span class="cr-value">₱<?= number_format((float)($co['gross_sales'] ?? 0), 2) ?></span></div>
                    <?php if ((float)($co['total_discounts'] ?? 0) > 0): ?>
                    <div class="co-row"><span class="cr-label">Discounts</span><span class="cr-value neg">−₱<?= number_format((float)$co['total_discounts'], 2) ?></span></div>
                    <?php endif; ?>
                    <?php if ((float)($co['total_refunds'] ?? 0) > 0): ?>
                    <div class="co-row"><span class="cr-label">Refunds</span><span class="cr-value neg">−₱<?= number_format((float)$co['total_refunds'], 2) ?></span></div>
                    <?php endif; ?>
                    <div class="co-row"><span class="cr-label">Net Sales <small style="font-size:.7rem;">(VAT-incl.)</small></span><span class="cr-value">₱<?= number_format((float)($co['net_sales'] ?? 0), 2) ?></span></div>
                    <div class="co-row"><span class="cr-label" style="padding-left:.7rem;font-size:.8rem;">Excl. VAT</span><span class="cr-value muted">₱<?= number_format((float)($co['net_sales_excl_vat'] ?? 0), 2) ?></span></div>
                    <div class="co-row"><span class="cr-label" style="padding-left:.7rem;font-size:.8rem;">VAT (12%)</span><span class="cr-value muted">₱<?= number_format((float)($co['vat_collected'] ?? 0), 2) ?></span></div>
                </div>
            </div>
            <div class="co-section">
                <div class="co-section-head"><i class="fas fa-coins"></i> Cash Reconciliation</div>
                <div class="co-section-body">
                    <div class="co-row"><span class="cr-label">Cash Received</span><span class="cr-value">₱<?= number_format((float)($co['cash_received'] ?? 0), 2) ?></span></div>
                    <div class="co-row"><span class="cr-label">Change Given</span><span class="cr-value neg">−₱<?= number_format((float)($co['change_given'] ?? 0), 2) ?></span></div>
                    <div style="margin-top:.75rem;padding:.9rem;background:var(--light-bg);border-radius:var(--radius-md);border-left:3px solid var(--primary);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.4rem;">
                        <span style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--primary-dark);"><i class="fas fa-hand-holding-usd me-1"></i> Turn Over</span>
                        <span style="font-size:1.35rem;font-weight:800;color:var(--primary-dark);">₱<?= number_format((float)($co['cash_to_turn_over'] ?? 0), 2) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($co_items) || !empty($co_hours)): ?>
        <div class="co-cols">
            <div class="co-section">
                <div class="co-section-head"><i class="fas fa-star"></i> Top Items This Shift</div>
                <?php if (!empty($co_items)): ?>
                <div class="co-section-body" style="padding-top:.5rem;">
                    <table class="co-items-table"><tbody>
                    <?php foreach ($co_items as $i => $item): ?>
                    <tr>
                        <td class="rank"><?= $i + 1 ?></td>
                        <td class="iname"><?= htmlspecialchars($item['item_name']) ?><?php if (!empty($item['variant_name'])): ?><small><?= htmlspecialchars($item['variant_name']) ?></small><?php endif; ?></td>
                        <td class="iqty">×<?= number_format((int)$item['units_sold']) ?></td>
                        <td class="irev">₱<?= number_format((float)$item['total_revenue'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody></table>
                </div>
                <?php else: ?>
                <div class="co-section-body"><div class="no-data" style="padding:1.5rem 0;"><i class="fas fa-inbox"></i> No items sold.</div></div>
                <?php endif; ?>
            </div>
            <div class="co-section">
                <div class="co-section-head"><i class="fas fa-clock"></i> Orders by Hour</div>
                <?php if (!empty($co_hours)):
                    $max_orders = max(array_column($co_hours, 'total_orders'));
                    $peak_hour  = null;
                    foreach ($co_hours as $h) { if ((int)$h['total_orders'] === (int)$max_orders) { $peak_hour = (int)$h['hour_of_day']; break; } }
                ?>
                <div class="co-section-body">
                    <div class="co-peak-bars">
                    <?php foreach ($co_hours as $h):
                        $pct = $max_orders > 0 ? round($h['total_orders'] / $max_orders * 100) : 0;
                        $hr  = (int)$h['hour_of_day'];
                        $lbl = ($hr % 12 ?: 12) . ($hr >= 12 ? 'p' : 'a');
                        $is_peak = ($hr === $peak_hour);
                    ?>
                        <div class="co-peak-bar-wrap">
                            <span class="co-peak-count"><?= $h['total_orders'] ?></span>
                            <div class="co-peak-bar <?= $is_peak ? 'peak' : '' ?>" style="height:<?= max(3, (int)($pct * 0.70)) ?>px;" title="<?= $lbl ?>: <?= $h['total_orders'] ?> orders"></div>
                            <span class="co-peak-lbl"><?= $lbl ?></span>
                        </div>
                    <?php endforeach; ?>
                    </div>
                    <?php if ($peak_hour !== null): ?>
                    <div style="margin-top:.6rem;font-size:.75rem;color:var(--warm-gray);">
                        <i class="fas fa-fire" style="color:var(--secondary);"></i>
                        Peak: <?= ($peak_hour % 12 ?: 12) . ($peak_hour >= 12 ? ' PM' : ' AM') ?>
                        &nbsp;·&nbsp; <?= $max_orders ?> orders
                    </div>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="co-section-body"><div class="no-data" style="padding:1.5rem 0;"><i class="fas fa-inbox"></i> No data.</div></div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="co-section" style="margin-bottom:1.5rem;">
            <div class="co-section-head"><i class="fas fa-pen"></i> Acknowledgment</div>
            <div class="co-section-body">
                <div class="co-sig-row">
                    <div class="co-sig-block"><div class="sig-line"></div><div class="sig-lbl"><?= $is_admin_shift ? 'Admin' : 'Cashier' ?> Signature &nbsp;·&nbsp; <?= htmlspecialchars($co['cashier_name'] ?? '') ?></div></div>
                    <div class="co-sig-block"><div class="sig-line"></div><div class="sig-lbl">Received By (<?= $is_admin_shift ? 'Owner / Supervisor' : 'Admin / Supervisor' ?>)</div></div>
                </div>
                <div style="margin-top:1rem;font-size:.75rem;color:var(--warm-gray);text-align:center;">
                    The Lounge by: CJV & TEK Cafeteria &copy; <?= date('Y') ?> &nbsp;·&nbsp; Generated <?= $now->format('h:i A') ?> (PHT)
                </div>
            </div>
        </div>
    <?php
    }

    function build_thermal_payload(array $co, array $co_items, string $filter_date): string {
        $tz  = new DateTimeZone('Asia/Manila');
        $now = new DateTime('now', $tz);
        return json_encode([
            'cashier_name'      => $co['cashier_name']           ?? '—',
            'username'          => $co['username']               ?? '—',
            'shift_date'        => date('F d, Y', strtotime($filter_date)),
            'printed_at'        => $now->format('h:i A') . ' (PHT)',
            'total_orders'      => (int)($co['total_orders']         ?? 0),
            'cancelled_orders'  => (int)($co['cancelled_orders']      ?? 0),
            'payment_count'     => (int)($co['payment_count']         ?? 0),
            'admin_processed'   => (int)($co['admin_processed_count'] ?? 0),
            'gross_sales'       => (float)($co['gross_sales']         ?? 0),
            'total_discounts'   => (float)($co['total_discounts']     ?? 0),
            'net_sales'         => (float)($co['net_sales']           ?? 0),
            'net_sales_excl'    => (float)($co['net_sales_excl_vat']  ?? 0),
            'vat_collected'     => (float)($co['vat_collected']       ?? 0),
            'total_refunds'     => (float)($co['total_refunds']       ?? 0),
            'cash_received'     => (float)($co['cash_received']       ?? 0),
            'change_given'      => (float)($co['change_given']        ?? 0),
            'cash_to_turn_over' => (float)($co['cash_to_turn_over']   ?? 0),
            'total_waste_cost'  => (float)($co['total_waste_cost']    ?? 0),
            'top_items'         => array_map(fn($r) => [
                'item_name'     => $r['item_name'],
                'variant_name'  => $r['variant_name'] ?? '',
                'units_sold'    => (int)$r['units_sold'],
                'total_revenue' => (float)$r['total_revenue'],
            ], $co_items),
        ]);
    }
    ?>

    <?php
    // =========================================================================
    // MY SHIFT (cashier)
    // =========================================================================
    if ($tab === 'myshift'):
        $s = $data['summary'] ?? [];
    ?>
    <form method="GET" action="<?= $baseUrl ?>/index.php" class="filter-bar">
        <input type="hidden" name="page" value="reports">
        <input type="hidden" name="tab"  value="myshift">
        <label>Date</label>
        <input type="date" name="date" class="form-control filter-input-date" value="<?= $filter_date ?>" max="<?= $today ?>">
        <div class="filter-actions">
            <button type="submit" class="btn-apply"><i class="fas fa-search me-1"></i> View</button>
            <a href="<?= $baseUrl ?>/index.php?page=reports&tab=closeout&date=<?= $filter_date ?>" class="btn-print"><i class="fas fa-print"></i> Print Close-out</a>
        </div>
    </form>

    <div class="stats-grid fade-in">
        <div class="stat-card">
            <div class="s-icon"><i class="fas fa-money-bill-wave"></i></div>
            <div><div class="s-label">Gross Sales</div><div class="s-value"><?= peso((float)($s['gross_revenue']??0)) ?></div><div class="s-sub">Before discounts and refunds</div></div>
        </div>
        <div class="stat-card">
            <div class="s-icon" style="background:rgba(44,122,90,0.08);color:var(--success);border-color:rgba(44,122,90,0.15);"><i class="fas fa-chart-bar"></i></div>
            <div>
                <div class="s-label">Gross Profit</div>
                <div class="s-value"><?= peso((float)($s['gross_profit']??0)) ?></div>
                <div class="profit-breakdown">Gross Sales: <span class="pos"><?= peso((float)($s['gross_revenue']??0)) ?></span> &minus; COGS: <span class="neg"><?= peso((float)($s['cogs']??0)) ?></span></div>
            </div>
        </div>
        <div class="stat-card c-teal">
            <div class="s-icon" style="background:var(--info-soft);color:var(--info);border-color:rgba(94,155,158,0.2);"><i class="fas fa-coins"></i></div>
            <div>
                <div class="s-label">Net Sales</div>
                <div class="s-value"><?= peso((float)($s['net_revenue']??0)) ?></div>
                <div class="profit-breakdown">Gross Sales: <span class="pos"><?= peso((float)($s['gross_revenue']??0)) ?></span> &minus; Discounts: <span class="neg"><?= peso((float)($s['total_discounts']??0)) ?></span> &minus; Refunds: <span class="neg"><?= peso((float)($s['total_refunds']??0)) ?></span></div>
            </div>
        </div>
        <div class="stat-card c-green">
            <div class="s-icon" style="background:rgba(44,122,90,0.08);color:var(--success);border-color:rgba(44,122,90,0.15);"><i class="fas fa-boxes"></i></div>
            <div><div class="s-label">COGS</div><div class="s-value"><?= peso((float)($s['cogs']??0)) ?></div><div class="s-sub">Cost of items sold</div></div>
        </div>
        <?php render_waste_card($s); ?>
        <?php render_net_profit_card($s); ?>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="rpt-card fade-in">
                <div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-star"></i> Top Items Today</div></div>
                <div class="rpt-card-body p-0"><?php render_top_items_table($data['top_items'] ?? [], false, false, true); ?></div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="rpt-card fade-in">
                <div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-clock"></i> Orders by Hour</div></div>
                <div class="rpt-card-body">
                    <?php if(empty($data['peak_hours'])): ?><div class="no-data"><i class="fas fa-inbox"></i> No data.</div>
                    <?php else: ?><canvas id="myshiftPeak" style="max-height:230px;"></canvas><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php if(!empty($data['peak_hours'])): ?>
    <script>(function(){
        const h=<?=json_encode(array_column($data['peak_hours'],'hour_of_day'))?>;
        const o=<?=json_encode(array_column($data['peak_hours'],'total_orders'))?>;
        new Chart(document.getElementById('myshiftPeak').getContext('2d'),{type:'bar',data:{labels:h.map(v=>(v%12||12)+' '+(v>=12?'PM':'AM')),datasets:[{label:'Orders',data:o,backgroundColor:'rgba(255,107,74,.7)',borderRadius:6,borderSkipped:false}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,grid:{color:'rgba(15,76,92,.04)'}},x:{grid:{display:false}}}}});
    })();</script>
    <?php endif; ?>

    <?php
    // =========================================================================
    // CLOSE-OUT (cashier)
    // =========================================================================
    elseif ($tab === 'closeout'):
        $co       = $data['closeout'] ?? [];
        $co_items = $co['top_items']  ?? [];
        $co_hours = $co['peak_hours'] ?? [];
        $co_thermal_payload = build_thermal_payload($co, $co_items, $filter_date);
    ?>
    <form method="GET" action="<?= $baseUrl ?>/index.php" class="filter-bar">
        <input type="hidden" name="page" value="reports">
        <input type="hidden" name="tab"  value="closeout">
        <label>Date</label>
        <input type="date" name="date" class="form-control filter-input-date" value="<?= $filter_date ?>" max="<?= $today ?>">
        <div class="filter-actions">
            <button type="submit" class="btn-apply"><i class="fas fa-search me-1"></i> Load</button>
            <button type="button" class="btn-co-print" onclick="printCoA4()"><i class="fas fa-print"></i> Print A4</button>
            <button type="button" class="btn-co-print thermal" onclick="printCoThermal()"><i class="fas fa-receipt"></i> Thermal</button>
        </div>
    </form>
    <div class="co-wrap fade-in" id="co-content">
        <?php render_closeout_tab($co, $co_items, $co_hours, $filter_date, 'co_thermal_payload', false); ?>
    </div>

    <?php
    // =========================================================================
    // ADMIN: MY SHIFT (adminshift)
    // =========================================================================
    elseif ($tab === 'adminshift'):
        $co       = $data['closeout'] ?? [];
        $co_items = $co['top_items']  ?? [];
        $co_hours = $co['peak_hours'] ?? [];
        $co_thermal_payload = build_thermal_payload($co, $co_items, $filter_date);
    ?>
    <form method="GET" action="<?= $baseUrl ?>/index.php" class="filter-bar">
        <input type="hidden" name="page" value="reports">
        <input type="hidden" name="tab"  value="adminshift">
        <label>Date</label>
        <input type="date" name="date" class="form-control filter-input-date" value="<?= $filter_date ?>" max="<?= $today ?>">
        <div class="filter-actions">
            <button type="submit" class="btn-apply"><i class="fas fa-search me-1"></i> Load</button>
            <button type="button" class="btn-co-print" onclick="printCoA4()"><i class="fas fa-print"></i> Print A4</button>
            <button type="button" class="btn-co-print thermal" onclick="printCoThermal()"><i class="fas fa-receipt"></i> Thermal</button>
        </div>
    </form>
    <?php if (empty($co) || (int)($co['total_orders'] ?? 0) === 0): ?>
    <div class="no-data" style="padding:4rem 1rem;">
        <i class="fas fa-user-shield"></i>
        No orders handled by you on <?= date('F d, Y', strtotime($filter_date)) ?>.
    </div>
    <?php else: ?>
    <div class="co-wrap fade-in" id="co-content">
        <?php render_closeout_tab($co, $co_items, $co_hours, $filter_date, 'co_thermal_payload', true); ?>
    </div>
    <?php endif; ?>

    <?php
    // =========================================================================
    // INVENTORY (cashier)
    // =========================================================================
    elseif ($tab === 'inventory'):
    ?>
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <span style="font-size:.88rem;color:var(--warm-gray);">Items at or below their alert threshold.</span>
        <button class="btn-print" onclick="printA4('inventory-content', 'Stock Alert Report')"><i class="fas fa-print"></i> Print</button>
    </div>
    <div id="inventory-content">
    <div class="rpt-card fade-in">
        <div class="rpt-card-header">
            <div class="rpt-card-title"><i class="fas fa-exclamation-triangle"></i> Low / Out of Stock</div>
            <?php if(!empty($data['low_stock'])): ?><span class="rpt-badge badge-danger"><?= count($data['low_stock']) ?> items</span><?php endif; ?>
        </div>
        <div class="rpt-card-body p-0">
        <?php if(empty($data['low_stock'])): ?>
            <div class="no-data"><i class="fas fa-check-circle" style="opacity:.3;color:var(--success);"></i> All ingredients are sufficiently stocked.</div>
        <?php else: ?>
            <div style="overflow-x:auto;"><table class="rpt-table">
                <thead><tr><th>Ingredient</th><th>Current Stock</th><th>Alert Level</th><th>Unit</th><th>Status</th><th>Last Updated</th></tr></thead>
                <tbody>
                <?php foreach($data['low_stock'] as $row):
                    $pct  = $row['low_stock_alert']>0 ? min(100,round($row['stock_quantity']/$row['low_stock_alert']*100)) : 0;
                    $bclr = $row['stock_quantity']==0 ? 'var(--danger)' : ($pct<30 ? 'var(--warning)' : 'var(--primary)');
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($row['ingredient_name']) ?></strong></td>
                    <td><?= number_format((float)$row['stock_quantity'],2) ?><div class="mini-bar"><div class="mini-bar-fill" style="width:<?=$pct?>%;background:<?=$bclr?>;"></div></div></td>
                    <td><?= number_format((float)$row['low_stock_alert'],2) ?></td>
                    <td><?= htmlspecialchars($row['unit'] ?? '—') ?></td>
                    <td><span class="rpt-badge <?=$row['stock_status']==='OUT OF STOCK'?'badge-danger':($row['stock_status']==='LOW'?'badge-warning':'badge-success')?>"><?=$row['stock_status']?></span></td>
                    <td style="font-size:.82rem;color:var(--warm-gray);"><?= date('M d, Y H:i',strtotime($row['last_updated'])) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
        <?php endif; ?>
        </div>
    </div>
    </div>

    <?php
    // =========================================================================
    // DAILY (admin)
    // =========================================================================
    elseif ($tab === 'daily'):
        $s = $data['summary'] ?? [];
    ?>
    <form method="GET" action="<?= $baseUrl ?>/index.php" class="filter-bar">
        <input type="hidden" name="page" value="reports">
        <input type="hidden" name="tab"  value="daily">
        <label>Date</label>
        <input type="date" name="date" class="form-control filter-input-date" value="<?= $filter_date ?>" max="<?= $today ?>">
        <div class="filter-actions">
            <button type="submit" class="btn-apply"><i class="fas fa-search me-1"></i> Apply</button>
            <button type="button" class="btn-print" onclick="printDaily()"><i class="fas fa-print"></i> Print</button>
        </div>
    </form>

    <div class="stats-grid fade-in">
        <div class="stat-card">
            <div class="s-icon"><i class="fas fa-money-bill-wave"></i></div>
            <div><div class="s-label">Gross Sales</div><div class="s-value"><?= peso((float)($s['gross_revenue']??0)) ?></div><div class="s-sub">Before discounts and refunds</div></div>
        </div>
        <div class="stat-card">
            <div class="s-icon" style="background:rgba(44,122,90,0.08);color:var(--success);border-color:rgba(44,122,90,0.15);"><i class="fas fa-chart-bar"></i></div>
            <div>
                <div class="s-label">Gross Profit</div>
                <div class="s-value"><?= peso((float)($s['gross_profit']??0)) ?></div>
                <div class="profit-breakdown">Net Sales: <span class="pos"><?= peso((float)($s['gross_revenue']??0)) ?></span> &minus; COGS: <span class="neg"><?= peso((float)($s['cogs']??0)) ?></span></div>
            </div>
        </div>
        <div class="stat-card c-teal">
            <div class="s-icon" style="background:var(--info-soft);color:var(--info);border-color:rgba(94,155,158,0.2);"><i class="fas fa-coins"></i></div>
            <div>
                <div class="s-label">Net Sales</div>
                <div class="s-value"><?= peso((float)($s['net_revenue']??0)) ?></div>
                <div class="profit-breakdown">Gross Sales: <span class="pos"><?= peso((float)($s['gross_revenue']??0)) ?></span> &minus; Discounts: <span class="neg"><?= peso((float)($s['total_discounts']??0)) ?></span> &minus; Refunds: <span class="neg"><?= peso((float)($s['total_refunds']??0)) ?></span></div>
            </div>
        </div>
        <div class="stat-card c-green">
            <div class="s-icon" style="background:rgba(44,122,90,0.08);color:var(--success);border-color:rgba(44,122,90,0.15);"><i class="fas fa-boxes"></i></div>
            <div><div class="s-label">COGS</div><div class="s-value"><?= peso((float)($s['cogs']??0)) ?></div><div class="s-sub">Cost of items sold</div></div>
        </div>
        <?php render_waste_card($s); ?>
        <?php render_net_profit_card($s); ?>
    </div>

    <?php if (!empty($data['comparison'])): render_comparison($data['comparison'], 'Yesterday'); endif; ?>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="rpt-card fade-in">
                <div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-star"></i> Top Items</div></div>
                <div class="rpt-card-body p-0"><?php render_top_items_table($data['top_items'] ?? [], true, true); ?></div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="rpt-card fade-in">
                <div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-clock"></i> Orders by Hour</div></div>
                <div class="rpt-card-body">
                    <?php if(empty($data['peak_hours'])): ?><div class="no-data"><i class="fas fa-inbox"></i> No data.</div>
                    <?php else: ?><canvas id="dailyPeak" style="max-height:230px;"></canvas><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="rpt-card fade-in">
                <div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-chart-pie"></i> Order Types</div></div>
                <div class="rpt-card-body p-0">
                <?php if(empty($data['order_types'])): ?><div class="no-data p-4"><i class="fas fa-inbox"></i> No data.</div>
                <?php else: ?><div style="overflow-x:auto;"><table class="rpt-table">
                    <thead><tr><th>Type</th><th>Source</th><th>Orders</th><th>Revenue</th><th>Excl. VAT</th></tr></thead>
                    <tbody>
                    <?php foreach($data['order_types'] as $row): ?>
                    <tr><td style="word-break:keep-all;"><?=ucfirst(str_replace('_',' ',$row['order_type']))?></td><td style="word-break:keep-all;"><?=ucfirst(str_replace('_',' ',$row['order_source']))?></td><td class="text-center"><?=number_format($row['total_orders'])?></td><td class="text-end"><?=peso((float)$row['revenue'])?></td><td class="text-end text-muted"><?=peso((float)($row['revenue_excl_vat']??0))?></td></tr>
                    <?php endforeach; ?>
                    </tbody></table></div><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="rpt-card fade-in">
                <div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-user-tie"></i> Cashier Breakdown</div></div>
                <div class="rpt-card-body p-0">
                <?php if(empty($data['cashiers'])): ?><div class="no-data p-4"><i class="fas fa-inbox"></i> No data.</div>
                <?php else: ?><div style="overflow-x:auto;"><table class="rpt-table">
                    <thead><tr><th>Cashier</th><th>Orders</th><th>Sales</th><th>Discounts</th><th>Waste</th><th>Cancelled</th><th>Admin Proc.</th></tr></thead>
                    <tbody>
                    <?php foreach($data['cashiers'] as $row): ?>
                    <tr>
                        <td><strong><?=htmlspecialchars($row['cashier'])?></strong></td>
                        <td><?=$row['orders_handled']?></td>
                        <td><?=peso((float)$row['total_sales'])?></td>
                        <td><?=peso((float)$row['discounts_given'])?></td>
                        <td class="waste-cost-cell"><?= ($row['total_waste_cost']??0)>0 ? peso((float)$row['total_waste_cost']) : '<span style="color:var(--warm-gray);">—</span>' ?></td>
                        <td><?=$row['cancelled_orders']>0?'<span class="rpt-badge badge-danger">'.$row['cancelled_orders'].'</span>':'<span class="rpt-badge badge-success">0</span>'?></td>
                        <td><?= $row['admin_processed_count']>0 ? '<span class="admin-proc-badge"><i class="fas fa-shield-alt"></i> '.$row['admin_processed_count'].'</span>' : '—' ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody></table></div><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php if(!empty($data['peak_hours'])): ?>
    <script>(function(){
        const h=<?=json_encode(array_column($data['peak_hours'],'hour_of_day'))?>;
        const o=<?=json_encode(array_column($data['peak_hours'],'total_orders'))?>;
        new Chart(document.getElementById('dailyPeak').getContext('2d'),{type:'bar',data:{labels:h.map(v=>(v%12||12)+' '+(v>=12?'PM':'AM')),datasets:[{label:'Orders',data:o,backgroundColor:o.map(v=>v===Math.max(...o)?'#ff6b4a':'rgba(15,76,92,.65)'),borderRadius:6,borderSkipped:false}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,grid:{color:'rgba(15,76,92,.04)'}},x:{grid:{display:false}}}}});
    })();</script>
    <?php endif; ?>

    <?php
    // =========================================================================
    // MONTHLY (admin)
    // =========================================================================
    elseif ($tab === 'monthly'):
        $s = $data['summary'] ?? [];
    ?>
    <form method="GET" action="<?= $baseUrl ?>/index.php" class="filter-bar">
        <input type="hidden" name="page" value="reports">
        <input type="hidden" name="tab"  value="monthly">
        <label>Month</label>
        <select name="month" class="form-select filter-input-month">
            <?php foreach($months_list as $n=>$nm): ?><option value="<?=$n?>" <?=$n===$filter_month?'selected':''?>><?=$nm?></option><?php endforeach; ?>
        </select>
        <label>Year</label>
        <input type="number" name="year" class="form-control filter-input-year" value="<?=$filter_year?>" min="2020" max="<?=date('Y')?>">
        <div class="filter-actions">
            <button type="submit" class="btn-apply"><i class="fas fa-search me-1"></i> Apply</button>
            <button type="button" class="btn-print" onclick="printA4('monthly-content', 'Monthly Report &mdash; <?= $months_list[$filter_month] ?> <?= $filter_year ?>')"><i class="fas fa-print"></i> Print</button>
        </div>
    </form>

    <div class="stats-grid fade-in">
        <div class="stat-card">
            <div class="s-icon"><i class="fas fa-money-bill-wave"></i></div>
            <div><div class="s-label">Gross Sales</div><div class="s-value"><?= peso((float)($s['gross_revenue']??0)) ?></div><div class="s-sub">Before discounts and refunds</div></div>
        </div>
        <div class="stat-card">
            <div class="s-icon" style="background:rgba(44,122,90,0.08);color:var(--success);border-color:rgba(44,122,90,0.15);"><i class="fas fa-chart-bar"></i></div>
            <div>
                <div class="s-label">Gross Profit</div>
                <div class="s-value"><?= peso((float)($s['gross_profit']??0)) ?></div>
                <div class="profit-breakdown">Net Sales: <span class="pos"><?= peso((float)($s['gross_revenue']??0)) ?></span> &minus; COGS: <span class="neg"><?= peso((float)($s['cogs']??0)) ?></span></div>
            </div>
        </div>
        <div class="stat-card c-teal">
            <div class="s-icon" style="background:var(--info-soft);color:var(--info);border-color:rgba(94,155,158,0.2);"><i class="fas fa-coins"></i></div>
            <div>
                <div class="s-label">Net Sales</div>
                <div class="s-value"><?= peso((float)($s['net_revenue']??0)) ?></div>
                <div class="profit-breakdown">Gross Sales: <span class="pos"><?= peso((float)($s['gross_revenue']??0)) ?></span> &minus; Discounts: <span class="neg"><?= peso((float)($s['total_discounts']??0)) ?></span> &minus; Refunds: <span class="neg"><?= peso((float)($s['total_refunds']??0)) ?></span></div>
            </div>
        </div>
        <div class="stat-card c-green">
            <div class="s-icon" style="background:rgba(44,122,90,0.08);color:var(--success);border-color:rgba(44,122,90,0.15);"><i class="fas fa-boxes"></i></div>
            <div><div class="s-label">COGS</div><div class="s-value"><?= peso((float)($s['cogs']??0)) ?></div><div class="s-sub">Cost of items sold</div></div>
        </div>
        <?php render_waste_card($s); ?>
        <?php render_net_profit_card($s); ?>
    </div>

    <?php if (!empty($data['comparison'])):
        $pm = $filter_month - 1; $py = $filter_year;
        if ($pm < 1) { $pm = 12; $py--; }
        render_comparison($data['comparison'], $months_list[$pm] . ' ' . $py);
    endif; ?>

    <div id="monthly-content">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="rpt-card fade-in"><div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-chart-line"></i> Daily Revenue Trend</div></div>
            <div class="rpt-card-body"><canvas id="monthlyTrend" style="max-height:250px;"></canvas></div></div>
        </div>
        <div class="col-lg-4">
            <div class="rpt-card fade-in"><div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-chart-pie"></i> By Category</div></div>
            <div class="rpt-card-body"><canvas id="monthlyCat" style="max-height:250px;"></canvas></div></div>
        </div>
        <div class="col-12">
            <div class="rpt-card fade-in"><div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-table"></i> Day-by-Day Sales</div></div>
            <div class="rpt-card-body p-0">
            <?php if(empty($data['daily_chart'])): ?><div class="no-data"><i class="fas fa-inbox"></i> No data.</div>
            <?php else: ?><div style="overflow-x:auto;"><table class="rpt-table">
                <thead><tr><th>Date</th><th>Orders</th><th>Revenue (VAT-incl.)</th><th>Excl. VAT</th><th>VAT (12%)</th></tr></thead>
                <tbody>
                <?php
                $total_orders_m=0; $total_rev_m=0; $total_excl_m=0; $total_vat_m=0;
                foreach($data['daily_chart'] as $day):
                    $total_orders_m += $day['total_orders'];
                    $total_rev_m    += $day['revenue'];
                    $total_excl_m   += $day['revenue_excl_vat'] ?? 0;
                    $total_vat_m    += $day['vat_amount'] ?? 0;
                ?>
                <tr>
                    <td><?=date('M d, Y',strtotime($day['sale_date']))?></td>
                    <td><?=number_format($day['total_orders'])?></td>
                    <td><?=peso((float)$day['revenue'])?></td>
                    <td style="color:var(--warm-gray);font-size:.85rem;"><?=peso((float)($day['revenue_excl_vat']??0))?></td>
                    <td style="color:var(--warm-gray);font-size:.85rem;"><?=peso((float)($day['vat_amount']??0))?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot><tr><td>Monthly Total</th><th><?=number_format($total_orders_m)?></th><th><?=peso($total_rev_m)?></th><th style="color:var(--warm-gray);"><?=peso($total_excl_m)?></th><th style="color:var(--warm-gray);"><?=peso($total_vat_m)?></th></tr></tfoot>
            </table></div><?php endif; ?>
            </div></div>
        </div>
        <div class="col-12">
            <div class="rpt-card fade-in"><div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-star"></i> Top Selling Items</div></div>
            <div class="rpt-card-body p-0"><?php render_top_items_table($data['top_items'] ?? [], true, true); ?></div></div>
        </div>
        <div class="col-12">
            <div class="rpt-card fade-in"><div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-user-tie"></i> Cashier Performance</div></div>
            <div class="rpt-card-body p-0">
            <?php if(empty($data['cashiers'])): ?><div class="no-data"><i class="fas fa-inbox"></i> No data.</div>
            <?php else: ?>
                <?php $grand=array_sum(array_column($data['cashiers'],'total_sales')); ?>
                <div style="overflow-x:auto;"><table class="rpt-table">
                    <thead><tr><th>Cashier</th><th>Orders</th><th>Total Sales</th><th>Avg/Order</th><th>Discounts</th><th>Waste</th><th>Cancelled</th><th>Admin Proc.</th></tr></thead>
                    <tbody>
                    <?php foreach($data['cashiers'] as $row): $pct=$grand>0?round($row['total_sales']/$grand*100,1):0; ?>
                    <tr>
                        <td><strong><?=htmlspecialchars($row['cashier'])?></strong><br><small style="color:var(--warm-gray);"><?=htmlspecialchars($row['username'])?></small></td>
                        <td><?=$row['orders_handled']?></td>
                        <td><strong><?=peso((float)$row['total_sales'])?></strong> <small>(<?=$pct?>%)</small><div class="mini-bar"><div class="mini-bar-fill" style="width:<?=$pct?>%;"></div></div></td>
                        <td><?=peso((float)$row['avg_order_value'])?></td>
                        <td><?=peso((float)$row['discounts_given'])?></td>
                        <td class="waste-cost-cell"><?= ($row['total_waste_cost']??0)>0 ? peso((float)$row['total_waste_cost']) : '<span style="color:var(--warm-gray);">—</span>' ?></td>
                        <td><?=$row['cancelled_orders']>0?'<span class="rpt-badge badge-danger">'.$row['cancelled_orders'].'</span>':'<span class="rpt-badge badge-success">0</span>'?></td>
                        <td><?= $row['admin_processed_count']>0 ? '<span class="admin-proc-badge"><i class="fas fa-shield-alt"></i> '.$row['admin_processed_count'].'</span>' : '—' ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table></div>
            <?php endif; ?>
            </div></div>
        </div>
    </div>
    </div>
    <script>(function(){
    var rawDaily = <?= json_encode(array_values($data['daily_chart'] ?? [])) ?>;
    var dd = rawDaily.map(function(r){ return r.sale_date; });
    var dr = rawDaily.map(function(r){ return parseFloat(r.revenue); });
    var trendCanvas = document.getElementById('monthlyTrend');
    if(dd.length && trendCanvas) {
        new Chart(trendCanvas.getContext('2d'),{type:'line',data:{labels:dd.map(function(d){var p=d.split('-');var mn=['','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];return p[2]+' '+mn[parseInt(p[1],10)];}),datasets:[{label:'Revenue',data:dr,backgroundColor:'rgba(15,76,92,.07)',borderColor:'#0f4c5c',borderWidth:2.5,tension:.35,fill:true,pointBackgroundColor:'#ff6b4a',pointRadius:4}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,grid:{color:'rgba(15,76,92,.04)'},ticks:{callback:function(v){return '\u20b1'+v.toLocaleString();}}},x:{grid:{display:false}}}}});
    } else if(trendCanvas) {
        trendCanvas.style.display='none';
        trendCanvas.parentElement.innerHTML='<div class="no-data"><i class="fas fa-inbox"></i> No daily revenue data for this month.</div>';
    }
    var rawCat = <?= json_encode(array_values($data['categories'] ?? [])) ?>;
    var cl = rawCat.map(function(r){ return r.category_name; });
    var cv = rawCat.map(function(r){ return parseFloat(r.revenue); });
    var catCanvas = document.getElementById('monthlyCat');
    if(cl.length && catCanvas) {
        new Chart(catCanvas.getContext('2d'),{type:'doughnut',data:{labels:cl,datasets:[{data:cv,backgroundColor:['#0f4c5c','#ff6b4a','#fee440','#1e2f4e','#5e9b9e','#ff8c70'],borderWidth:0,hoverOffset:8}]},options:{responsive:true,maintainAspectRatio:false,cutout:'65%',plugins:{legend:{position:'bottom',labels:{boxWidth:12,padding:14}},tooltip:{callbacks:{label:function(ctx){return ctx.label+': \u20b1'+parseFloat(ctx.raw).toLocaleString();}}}}}});
    } else if(catCanvas) {
        catCanvas.style.display='none';
        catCanvas.parentElement.innerHTML='<div class="no-data"><i class="fas fa-inbox"></i> No category data for this month.</div>';
    }
})();</script>

    <?php
    // =========================================================================
    // YEARLY (admin)
    // =========================================================================
    elseif ($tab === 'yearly'):
        $s = $data['summary'] ?? [];
        $monthly_keys   = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct_m','nov','dec_m'];
        $monthly_labels = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    ?>
    <form method="GET" action="<?= $baseUrl ?>/index.php" class="filter-bar">
        <input type="hidden" name="page" value="reports">
        <input type="hidden" name="tab"  value="yearly">
        <label>Year</label>
        <select name="year" class="form-select filter-input-month">
            <?php foreach(($data['available_years']??[$filter_year]) as $yr): ?><option value="<?=$yr?>" <?=$yr==$filter_year?'selected':''?>><?=$yr?></option><?php endforeach; ?>
        </select>
        <div class="filter-actions">
            <button type="submit" class="btn-apply"><i class="fas fa-search me-1"></i> Apply</button>
            <button type="button" class="btn-print" onclick="printA4('yearly-content', 'Yearly Report &mdash; <?= $filter_year ?>')"><i class="fas fa-print"></i> Print</button>
        </div>
    </form>

    <div class="stats-grid fade-in">
        <div class="stat-card">
            <div class="s-icon"><i class="fas fa-money-bill-wave"></i></div>
            <div><div class="s-label">Gross Sales <?=$filter_year?></div><div class="s-value"><?=peso((float)($s['gross_revenue']??0))?></div><div class="s-sub">Before discounts and refunds</div></div>
        </div>
        <div class="stat-card">
            <div class="s-icon" style="background:rgba(44,122,90,0.08);color:var(--success);border-color:rgba(44,122,90,0.15);"><i class="fas fa-chart-bar"></i></div>
            <div>
                <div class="s-label">Gross Profit <?=$filter_year?></div>
                <div class="s-value"><?=peso((float)($s['gross_profit']??0))?></div>
                <div class="profit-breakdown">Net Sales: <span class="pos"><?=peso((float)($s['gross_revenue']??0))?></span> &minus; COGS: <span class="neg"><?=peso((float)($s['cogs']??0))?></span></div>
            </div>
        </div>
        <div class="stat-card c-teal">
            <div class="s-icon" style="background:var(--info-soft);color:var(--info);border-color:rgba(94,155,158,0.2);"><i class="fas fa-coins"></i></div>
            <div>
                <div class="s-label">Net Sales</div>
                <div class="s-value"><?=peso((float)($s['net_revenue']??0))?></div>
                <div class="profit-breakdown">Gross Sales: <span class="pos"><?= peso((float)($s['gross_revenue']??0)) ?></span> &minus; Discounts: <span class="neg"><?= peso((float)($s['total_discounts']??0)) ?></span> &minus; Refunds: <span class="neg"><?= peso((float)($s['total_refunds']??0)) ?></span></div>
            </div>
        </div>
        <div class="stat-card c-green">
            <div class="s-icon" style="background:rgba(44,122,90,0.08);color:var(--success);border-color:rgba(44,122,90,0.15);"><i class="fas fa-boxes"></i></div>
            <div><div class="s-label">COGS</div><div class="s-value"><?=peso((float)($s['cogs']??0))?></div><div class="s-sub">Cost of items sold</div></div>
        </div>
        <?php render_waste_card($s); ?>
        <?php render_net_profit_card($s); ?>
    </div>

    <?php if (!empty($data['comparison'])): render_comparison($data['comparison'], ($filter_year - 1) . ''); endif; ?>

    <div id="yearly-content">
    <div class="rpt-card fade-in">
        <div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-chart-bar"></i> Monthly Revenue <?=$filter_year?></div></div>
        <div class="rpt-card-body"><canvas id="yearlyBar" style="max-height:270px;"></canvas></div>
    </div>
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="rpt-card fade-in"><div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-table"></i> Month-by-Month Breakdown</div></div>
            <div class="rpt-card-body p-0">
                <table class="rpt-table cashier-table">
                    <thead><tr><th>Month</th><th>Revenue (VAT-incl.)</th><th>Excl. VAT</th></tr></thead>
                    <tbody>
                    <?php
                    $yearly_total=0; $yearly_excl=0;
                    foreach($monthly_keys as $mi=>$key):
                        $val  = (float)($s[$key]??0);
                        $excl = $val>0 ? round($val/1.12,2) : 0;
                        $yearly_total += $val; $yearly_excl += $excl;
                        $pct = ($s['net_revenue']??0)>0 ? min(100,round($val/($s['net_revenue']??0)*100)) : 0;
                    ?>
                    <tr>
                        <td><?=$monthly_labels[$mi]?></td>
                        <td><strong><?=peso($val)?></strong><?php if($val>0): ?><div class="mini-bar"><div class="mini-bar-fill" style="width:<?=$pct?>%;"></div></div><?php endif; ?></td>
                        <td style="color:var(--warm-gray);font-size:.85rem;"><?=peso($excl)?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot><tr><td>Yearly Total</th><th><?=peso($yearly_total)?></th><th style="color:var(--warm-gray);"><?=peso($yearly_excl)?></th></tr></tfoot>
                </table>
            </div></div>
        </div>
        <div class="col-lg-7">
            <div class="rpt-card fade-in"><div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-star"></i> Top Items <?=$filter_year?></div></div>
            <div class="rpt-card-body p-0"><?php render_top_items_table($data['top_items'] ?? [], true, true); ?></div></div>
        </div>
    </div>
    </div>
    <script>(function(){
        const ml=<?=json_encode($monthly_labels)?>;
        const md=<?=json_encode(array_map(fn($k)=>(float)($s[$k]??0),$monthly_keys))?>;
        new Chart(document.getElementById('yearlyBar').getContext('2d'),{type:'bar',data:{labels:ml,datasets:[{label:'Revenue',data:md,backgroundColor:md.map(v=>v===Math.max(...md)?'#ff6b4a':'rgba(15,76,92,.65)'),borderRadius:8,borderSkipped:false}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,grid:{color:'rgba(15,76,92,.04)'},ticks:{callback:v=>'₱'+v.toLocaleString()}},x:{grid:{display:false}}}}});
    })();</script>

    <?php
    // =========================================================================
    // PRODUCTS (admin)
    // =========================================================================
    elseif ($tab === 'products'):
    ?>
    <form method="GET" action="<?= $baseUrl ?>/index.php" class="filter-bar">
        <input type="hidden" name="page" value="reports">
        <input type="hidden" name="tab"  value="products">
        <label>From</label><input type="date" name="start" id="rpt-start" class="form-control filter-input-range" value="<?=$range_start?>" max="<?=$today?>">
        <label>To</label><input type="date" name="end" id="rpt-end" class="form-control filter-input-range" value="<?=$range_end?>" min="<?=$range_start?>" max="<?=$today?>">
        <div class="filter-actions">
            <button type="submit" class="btn-apply"><i class="fas fa-search me-1"></i> Apply</button>
            <button type="button" class="btn-print" onclick="printA4('products-content','Products Report')"><i class="fas fa-print"></i> Print</button>
        </div>
    </form>
    <div id="products-content">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="rpt-card fade-in"><div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-star"></i> Top Selling Items</div>
                <small style="color:var(--warm-gray);font-size:.75rem;"><span class="badge-variant">variants</span> = sold in multiple sizes/types</small>
            </div>
            <div class="rpt-card-body p-0"><?php render_top_items_table($data['top_items'] ?? [], true, true); ?></div></div>
        </div>
        <div class="col-lg-4">
            <div class="rpt-card fade-in"><div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-chart-pie"></i> By Category</div></div>
            <div class="rpt-card-body"><canvas id="prodCat" style="max-height:230px;"></canvas></div></div>
            <div class="rpt-card fade-in" style="margin-top:1.25rem;"><div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-plus-circle"></i> Top Add-ons</div></div>
            <div class="rpt-card-body p-0">
            <?php if(empty($data['top_addons'])): ?><div class="no-data p-3"><i class="fas fa-inbox"></i> No add-on data.</div>
            <?php else: ?><table class="rpt-table"><thead><tr><th>Add-on</th><th>Qty</th><th>Revenue</th></tr></thead><tbody>
            <?php foreach($data['top_addons'] as $row): ?><tr><td style="word-break:keep-all;"><?=htmlspecialchars($row['addon_name'])?></td><td class="text-center"><?=number_format($row['times_ordered'])?></td><td class="text-end"><?=peso((float)$row['total_revenue'])?></td></tr><?php endforeach; ?>
            </tbody></table><?php endif; ?>
            </div></div>
        </div>
    </div>
    </div>
    <script>(function(){
        const cl=<?=json_encode(array_column($data['categories']??[],'category_name'))?>;
        const cv=<?=json_encode(array_column($data['categories']??[],'revenue'))?>;
        const prodCatCanvas = document.getElementById('prodCat');
        if(cl.length) {
            new Chart(prodCatCanvas.getContext('2d'),{type:'doughnut',data:{labels:cl,datasets:[{data:cv,backgroundColor:['#0f4c5c','#ff6b4a','#fee440','#1e2f4e','#5e9b9e','#ff8c70'],borderWidth:0,hoverOffset:8}]},options:{responsive:true,maintainAspectRatio:false,cutout:'65%',plugins:{legend:{position:'bottom',labels:{boxWidth:12,padding:14}},tooltip:{callbacks:{label:ctx=>ctx.label+': ₱'+parseFloat(ctx.raw).toLocaleString()}}}}});
        } else {
            prodCatCanvas.style.display = 'none';
            prodCatCanvas.parentElement.innerHTML = '<div class="no-data"><i class="fas fa-inbox"></i> No category data for this period.</div>';
        }
    })();</script>

    <?php
    // =========================================================================
    // STAFF (admin)
    // =========================================================================
    elseif ($tab === 'staff'):
    ?>
    <form method="GET" action="<?= $baseUrl ?>/index.php" class="filter-bar">
        <input type="hidden" name="page" value="reports">
        <input type="hidden" name="tab"  value="staff">
        <label>From</label><input type="date" name="start" id="rpt-start" class="form-control filter-input-range" value="<?=$range_start?>" max="<?=$today?>">
        <label>To</label><input type="date" name="end" id="rpt-end" class="form-control filter-input-range" value="<?=$range_end?>" min="<?=$range_start?>" max="<?=$today?>">
        <div class="filter-actions">
            <button type="submit" class="btn-apply"><i class="fas fa-search me-1"></i> Apply</button>
            <button type="button" class="btn-print" onclick="printA4('staff-content','Staff Performance Report')"><i class="fas fa-print"></i> Print</button>
        </div>
    </form>
    <div id="staff-content">
    <div class="row g-4">
        <div class="col-12">
            <div class="rpt-card fade-in"><div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-user-tie"></i> Cashier Performance</div></div>
            <div class="rpt-card-body p-0">
            <?php if(empty($data['cashiers'])): ?><div class="no-data"><i class="fas fa-inbox"></i> No data for this range.</div>
            <?php else: ?>
                <?php $grand=array_sum(array_column($data['cashiers'],'total_sales')); ?>
                <div style="overflow-x:auto;"><table class="rpt-table">
                    <thead><tr><th>Cashier</th><th>Orders</th><th>Total Sales</th><th>Avg/Order</th><th>Discounts</th><th>Waste</th><th>Cancelled</th><th>Admin Proc.</th></tr></thead>
                    <tbody>
                    <?php foreach($data['cashiers'] as $row): $pct=$grand>0?round($row['total_sales']/$grand*100,1):0; ?>
                    <tr>
                        <td><strong><?=htmlspecialchars($row['cashier'])?></strong><br><small style="color:var(--warm-gray);"><?=htmlspecialchars($row['username'])?></small></td>
                        <td><?=$row['orders_handled']?></td>
                        <td><strong><?=peso((float)$row['total_sales'])?></strong> <small>(<?=$pct?>%)</small><div class="mini-bar"><div class="mini-bar-fill" style="width:<?=$pct?>%;"></div></div></td>
                        <td><?=peso((float)$row['avg_order_value'])?></td>
                        <td><?=peso((float)$row['discounts_given'])?></td>
                        <td class="waste-cost-cell"><?= ($row['total_waste_cost']??0)>0 ? peso((float)$row['total_waste_cost']) : '<span style="color:var(--warm-gray);">—</span>' ?></td>
                        <td><?=$row['cancelled_orders']>0?'<span class="rpt-badge badge-danger">'.$row['cancelled_orders'].'</span>':'<span class="rpt-badge badge-success">0</span>'?></td>
                        <td><?= $row['admin_processed_count']>0 ? '<span class="admin-proc-badge"><i class="fas fa-shield-alt"></i> '.$row['admin_processed_count'].'</span>' : '—' ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table></div>
            <?php endif; ?>
            </div></div>
        </div>
        <div class="col-lg-7">
            <div class="rpt-card fade-in"><div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-clock"></i> Peak Hours</div></div>
            <div class="rpt-card-body">
            <?php if(empty($data['peak_hours'])): ?><div class="no-data"><i class="fas fa-inbox"></i> No data.</div>
            <?php else: ?><canvas id="staffPeak" style="max-height:230px;"></canvas><?php endif; ?>
            </div></div>
        </div>
        <div class="col-lg-5">
            <div class="rpt-card fade-in"><div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-chart-pie"></i> Order Types</div></div>
            <div class="rpt-card-body p-0">
            <?php if(empty($data['order_types'])): ?><div class="no-data p-4"><i class="fas fa-inbox"></i> No data.</div>
            <?php else: ?><table class="rpt-table"><thead><tr><th>Type</th><th>Source</th><th>Orders</th><th>Revenue</th><tr></thead><tbody>
            <?php foreach($data['order_types'] as $row): ?><tr><td style="word-break:keep-all;"><?=ucfirst(str_replace('_',' ',$row['order_type']))?></td><td style="word-break:keep-all;"><?=ucfirst(str_replace('_',' ',$row['order_source']))?></td><td class="text-center"><?=number_format($row['total_orders'])?></td><td class="text-end"><?=peso((float)$row['revenue'])?></td></tr><?php endforeach; ?>
            </tbody></table><?php endif; ?>
            </div></div>
        </div>
    </div>
    </div>
    <?php if(!empty($data['peak_hours'])): ?>
    <script>(function(){const h=<?=json_encode(array_column($data['peak_hours'],'hour_of_day'))?>;const o=<?=json_encode(array_column($data['peak_hours'],'total_orders'))?>;new Chart(document.getElementById('staffPeak').getContext('2d'),{type:'bar',data:{labels:h.map(v=>(v%12||12)+' '+(v>=12?'PM':'AM')),datasets:[{label:'Orders',data:o,backgroundColor:o.map(v=>v===Math.max(...o)?'#ff6b4a':'rgba(15,76,92,.65)'),borderRadius:6,borderSkipped:false}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,grid:{color:'rgba(15,76,92,.04)'}},x:{grid:{display:false}}}}});})();</script>
    <?php endif; ?>

    <?php
    // =========================================================================
    // AUDIT (admin)
    // =========================================================================
    elseif ($tab === 'audit'):
    ?>
    <form method="GET" action="<?= $baseUrl ?>/index.php" class="filter-bar">
        <input type="hidden" name="page" value="reports">
        <input type="hidden" name="tab"  value="audit">
        <label>From</label><input type="date" name="start" id="rpt-start" class="form-control filter-input-range" value="<?=$range_start?>" max="<?=$today?>">
        <label>To</label><input type="date" name="end" id="rpt-end" class="form-control filter-input-range" value="<?=$range_end?>" min="<?=$range_start?>" max="<?=$today?>">
        <div class="filter-actions">
            <button type="submit" class="btn-apply"><i class="fas fa-search me-1"></i> Apply</button>
            <button type="button" class="btn-print" onclick="printA4('audit-content','Audit Report')"><i class="fas fa-print"></i> Print</button>
        </div>
    </form>
    <div id="audit-content">
    <div class="rpt-card fade-in">
        <div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-tag"></i> Discounts &amp; Cancellations</div></div>
        <div class="rpt-card-body p-0">
        <?php if(empty($data['discounts'])): ?><div class="no-data"><i class="fas fa-inbox"></i> No discounts or cancellations in this range.</div>
        <?php else: ?><div style="overflow-x:auto;"><table class="rpt-table">
            <thead><tr><th>Date</th><th>Completed At</th><th>Order #</th><th>Cashier</th><th>Customer</th><th>Subtotal</th><th>Discount</th><th>Collected</th><th>VAT</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach($data['discounts'] as $row): ?>
            <tr>
                <td><?=date('M d, Y',strtotime($row['created_at']))?></td>
                <td><?=$row['completed_at']?date('M d, Y H:i',strtotime($row['completed_at'])):'<span style="color:var(--warm-gray);">—</span>'?></td>
                <td><strong><?=htmlspecialchars($row['order_number'])?></strong></td>
                <td><?=htmlspecialchars($row['cashier'])?></td>
                <td><?=!empty($row['customer_name'])?htmlspecialchars($row['customer_name']):'<span style="color:var(--warm-gray);">Walk-in</span>'?></td>
                <td><?=peso((float)$row['subtotal'])?></td>
                <td><?=$row['discount']>0?'<span class="rpt-badge badge-warning">'.peso((float)$row['discount']).'</span>':'—'?></td>
                <td><?=peso((float)$row['total_amount'])?></td>
                <td style="color:var(--warm-gray);font-size:.82rem;"><?=peso((float)($row['vat_amount']??0))?></td>
                <td><span class="rpt-badge <?=$row['status']==='cancelled'?'badge-danger':($row['status']==='completed'?'badge-success':'badge-warning')?>"><?=ucfirst($row['status'])?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div><?php endif; ?>
        </div>
    </div>

    <?php if (!empty($data['discounts']) && ($data['discounts_total'] ?? 0) > $limit): ?>
    <div class="pagination-wrapper" style="margin-top: 1.5rem; display: flex; justify-content: center; align-items: center; gap: 0.5rem;">
        <?php
        $total = $data['discounts_total'] ?? 0;
        $totalPages = ceil($total / $limit);
        $currentUrl = $_SERVER['REQUEST_URI'] ?? '';
        $currentUrl = preg_replace('/[&?]page=\d+/', '', $currentUrl);
        $separator = strpos($currentUrl, '?') !== false ? '&' : '?';
        ?>
        <span style="font-size: 0.85rem; color: var(--warm-gray);">Showing <?= min($offset + 1, $total) ?>-<?= min($offset + $limit, $total) ?> of <?= $total ?> records</span>
        <?php if ($page > 1): ?>
        <a href="<?= htmlspecialchars($currentUrl . $separator . 'page=' . ($page - 1)) ?>" class="btn-apply" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;"><i class="fas fa-chevron-left"></i> Previous</a>
        <?php endif; ?>
        <span style="font-weight: 600; font-size: 0.9rem;">Page <?= $page ?> of <?= $totalPages ?></span>
        <?php if ($page < $totalPages): ?>
        <a href="<?= htmlspecialchars($currentUrl . $separator . 'page=' . ($page + 1)) ?>" class="btn-apply" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">Next <i class="fas fa-chevron-right"></i></a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="rpt-card fade-in">
        <div class="rpt-card-header">
            <div class="rpt-card-title"><i class="fas fa-cash-register"></i> Payment Reconciliation</div>
            <small style="color:var(--warm-gray);font-size:.75rem;">Variance = Net Cash &minus; Expected. Zero is ideal.</small>
        </div>
        <div class="rpt-card-body p-0">
        <?php if(empty($data['payments'])): ?><div class="no-data"><i class="fas fa-inbox"></i> No payment data in this range.</div>
        <?php else: ?><div style="overflow-x:auto;"><table class="rpt-table">
            <thead><tr><th>Date</th><th>Cashier</th><th>Txns</th><th>Cash In</th><th>Change Out</th><th>Net Cash</th><th>Expected</th><th>Excl. VAT</th><th>VAT</th><th style="min-width:100px;">Variance</th><th>Admin Proc.</th></tr></thead>
            <tbody>
            <?php foreach($data['payments'] as $row):
                $var     = (float)($row['variance'] ?? ((float)$row['net_cash'] - (float)$row['expected_from_orders']));
                $var_cls = abs($var) < 0.01 ? 'variance-zero' : ($var >= 0 ? 'variance-positive' : 'variance-negative');
                $var_str = abs($var) < 0.01 ? '✓ Balanced' : (($var > 0 ? '+' : '') . peso($var));
            ?>
            <tr>
                <td><?=date('M d, Y',strtotime($row['pay_date']))?></td>
                <td><strong><?=htmlspecialchars($row['cashier'])?></strong></td>
                <td><?=$row['transactions']?></td>
                <td><?=peso((float)$row['cash_received'])?></td>
                <td><?=peso((float)$row['change_given'])?></td>
                <td><strong><?=peso((float)$row['net_cash'])?></strong></td>
                <td><?=peso((float)$row['expected_from_orders'])?></td>
                <td style="color:var(--warm-gray);font-size:.82rem;"><?=peso((float)($row['expected_excl_vat']??0))?></td>
                <td style="color:var(--warm-gray);font-size:.82rem;"><?=peso((float)($row['vat_in_expected']??0))?></td>
                <td class="<?=$var_cls?>"><?=$var_str?></td>
                <td><?= ($row['admin_processed_count']??0)>0 ? '<span class="admin-proc-badge"><i class="fas fa-shield-alt"></i> '.$row['admin_processed_count'].'</span>' : '—' ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div><?php endif; ?>
        </div>
    </div>
    </div>

    <?php
    // =========================================================================
    // INGREDIENT CONSUMPTION (admin)
    // =========================================================================
    elseif ($tab === 'consumption'):
    ?>
    <form method="GET" action="<?= $baseUrl ?>/index.php" class="filter-bar">
        <input type="hidden" name="page" value="reports">
        <input type="hidden" name="tab"  value="consumption">
        <label>From</label><input type="date" name="start" id="rpt-start" class="form-control filter-input-range" value="<?=$range_start?>" max="<?=$today?>">
        <label>To</label><input type="date" name="end" id="rpt-end" class="form-control filter-input-range" value="<?=$range_end?>" min="<?=$range_start?>" max="<?=$today?>">
        <div class="filter-actions">
            <button type="submit" class="btn-apply"><i class="fas fa-search me-1"></i> Apply</button>
            <button type="button" class="btn-print" onclick="printA4('consumption-content','Ingredient Consumption Report')"><i class="fas fa-print"></i> Print</button>
        </div>
    </form>
    <div id="consumption-content">
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="rpt-card fade-in">
                <div class="rpt-card-header">
                    <div class="rpt-card-title"><i class="fas fa-flask"></i> Ingredient Consumption</div>
                    <small style="color:var(--warm-gray);font-size:.74rem;">
                        <span style="color:var(--secondary);font-weight:600;">■</span> variant recipe &nbsp;
                        <span style="color:var(--warning);font-weight:600;">■</span> base fallback
                    </small>
                </div>
                <div class="rpt-card-body p-0">
                <?php if(empty($data['consumption'])): ?>
                    <div class="no-data"><i class="fas fa-inbox"></i> No consumption data for this range.</div>
                <?php else:
                    $max_consumed = max(array_column($data['consumption'], 'theoretical_consumed'));
                ?>
                    <div style="overflow-x:auto;"><table class="rpt-table">
                        <thead><tr><th>Ingredient</th><th>Unit</th><th>Consumed (est.)</th><th>Recipe Source</th><th>Current Stock</th><th>Alert</th></tr></thead>
                        <tbody>
                        <?php foreach($data['consumption'] as $row):
                            $pct         = $max_consumed > 0 ? round($row['theoretical_consumed'] / $max_consumed * 100) : 0;
                            $via_variant = (int)($row['units_via_variant_recipe'] ?? 0);
                            $via_base    = (int)($row['units_via_base_recipe']    ?? 0);
                            $has_fb      = $via_base > 0;
                            $bar_color   = $has_fb ? 'var(--warning)' : 'var(--secondary)';
                        ?>
                        <tr>
                            <td><strong><?=htmlspecialchars($row['ingredient_name'])?></strong></td>
                            <td><span class="rpt-badge badge-info"><?=htmlspecialchars($row['unit_code']??'—')?></span></td>
                            <td><?=number_format((float)$row['theoretical_consumed'],2)?><div class="consumption-bar"><div class="consumption-bar-fill" style="width:<?=$pct?>%;background:<?=$bar_color?>;"></div></div></td>
                            <td>
                                <?php if($via_variant > 0): ?><span class="badge-variant"><?=$via_variant?> via variant</span><?php endif; ?>
                                <?php if($has_fb): ?><span style="color:var(--warning);font-weight:600;font-size:.7rem;"><i class="fas fa-exclamation-triangle" style="font-size:.62rem;"></i> <?=$via_base?> base fallback</span><?php endif; ?>
                                <?php if($via_variant === 0 && !$has_fb): ?><span class="badge-base">base</span><?php endif; ?>
                            </td>
                            <td><?=number_format((float)$row['current_stock'],2)?><?php if($row['current_stock'] <= $row['low_stock_alert']): ?><span class="rpt-badge badge-danger" style="font-size:.62rem;margin-left:.3rem;">LOW</span><?php endif; ?></td>
                            <td style="color:var(--warm-gray);"><?=number_format((float)$row['low_stock_alert'],2)?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table></div>
                <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="rpt-card fade-in">
                <div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-exclamation-triangle"></i> Current Stock Alerts</div></div>
                <div class="rpt-card-body p-0">
                <?php if(empty($data['low_stock'])): ?>
                    <div class="no-data p-3"><i class="fas fa-check-circle" style="font-size:1.8rem;color:var(--success);opacity:.6;display:block;margin-bottom:.5rem;"></i>All ingredients sufficiently stocked.</div>
                <?php else: ?>
                    <table class="rpt-table cashier-table"><thead><tr><th>Ingredient</th><th>Stock</th><th>Unit</th><th>Status</th></tr></thead><tbody>
                    <?php foreach($data['low_stock'] as $row): ?>
                    <tr><td style="word-break:keep-all;"><?=htmlspecialchars($row['ingredient_name'])?></td><td class="text-end"><?=number_format((float)$row['stock_quantity'],2)?></td><td><?=htmlspecialchars($row['unit']??'—')?></td><td><span class="rpt-badge <?=$row['stock_quantity']==0?'badge-danger':'badge-warning'?>"><?=$row['stock_status']?></span></td></tr>
                    <?php endforeach; ?>
                    </tbody></table>
                <?php endif; ?>
                </div>
            </div>
            <?php if(!empty($data['consumption'])): ?>
            <?php $total_fallback = array_sum(array_column($data['consumption'], 'units_via_base_recipe')); ?>
            <?php if($total_fallback > 0): ?>
            <div class="rpt-card fade-in" style="margin-top:1.25rem;">
                <div class="co-section-body" style="padding:.9rem 1.1rem;">
                    <div style="display:flex;align-items:flex-start;gap:.65rem;">
                        <i class="fas fa-exclamation-triangle" style="color:var(--warning);margin-top:.15rem;flex-shrink:0;"></i>
                        <div>
                            <div style="font-weight:700;font-size:.85rem;color:var(--navy);margin-bottom:.25rem;">Recipe Coverage Warning</div>
                            <div style="font-size:.8rem;color:var(--warm-gray);"><?= number_format($total_fallback) ?> unit(s) fell back to base recipe — no variant recipe configured. <a href="<?= $baseUrl ?>/index.php?page=manage_recipes" style="color:var(--primary-light);font-weight:600;">Set up variant recipes →</a></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; endif; ?>
        </div>
    </div>
    </div>

    <?php
    // =========================================================================
    // WASTE (admin + cashier)
    // =========================================================================
    elseif ($tab === 'waste'):
        $ws = $data['summary']     ?? [];
        $wi = $data['waste_items'] ?? [];
        $scope_label = $is_admin ? 'All Cashiers' : 'My Orders';
    ?>
    <form method="GET" action="<?= $baseUrl ?>/index.php" class="filter-bar">
        <input type="hidden" name="page" value="reports">
        <input type="hidden" name="tab"  value="waste">
        <label>From</label><input type="date" name="start" id="rpt-start" class="form-control filter-input-range" value="<?=$range_start?>" max="<?=$today?>">
        <label>To</label><input type="date" name="end" id="rpt-end" class="form-control filter-input-range" value="<?=$range_end?>" min="<?=$range_start?>" max="<?=$today?>">
        <div class="filter-actions">
            <button type="submit" class="btn-apply"><i class="fas fa-search me-1"></i> Apply</button>
            <button type="button" class="btn-print" onclick="printA4('waste-content','Waste Report')"><i class="fas fa-print"></i> Print</button>
        </div>
    </form>

    <div class="stats-grid fade-in">
        <div class="stat-card c-danger">
            <div class="s-icon" style="background:rgba(214,64,69,0.08);color:var(--danger);border-color:rgba(214,64,69,0.15);"><i class="fas fa-trash-alt"></i></div>
            <div><div class="s-label">Total Waste Cost</div><div class="s-value" style="color:var(--danger);"><?= peso((float)($ws['total_waste_cost']??0)) ?></div><div class="s-sub"><?= $scope_label ?> &nbsp;·&nbsp; <?= $range_start ?> → <?= $range_end ?></div></div>
        </div>
        <div class="stat-card c-warning">
            <div class="s-icon" style="background:var(--warning-soft);color:var(--warning);border-color:rgba(244,185,66,0.2);"><i class="fas fa-box-open"></i></div>
            <div><div class="s-label">Voided Items</div><div class="s-value"><?= number_format($ws['voided_items']??0) ?></div><div class="s-sub">Across <?= number_format($ws['voided_orders']??0) ?> order(s)</div></div>
        </div>
        <div class="stat-card c-coral">
            <div class="s-icon" style="background:rgba(255,107,74,0.08);color:var(--secondary);border-color:rgba(255,107,74,0.15);"><i class="fas fa-calculator"></i></div>
            <div><div class="s-label">Avg Waste / Item</div><div class="s-value"><?= peso((float)($ws['avg_waste_per_item']??0)) ?></div><div class="s-sub">Cost per voided item</div></div>
        </div>
    </div>

    <div id="waste-content">
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="rpt-card fade-in">
                <div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-fire-alt" style="color:var(--danger);"></i> Top Wasted Items</div></div>
                <div class="rpt-card-body p-0">
                <?php if(empty($ws['top_wasted_items'])): ?>
                    <div class="no-data"><i class="fas fa-check-circle" style="color:var(--success);"></i><br>No waste in this period.</div>
                <?php else:
                    $max_w = max(array_column($ws['top_wasted_items'],'total_waste_cost'));
                ?>
                    <table class="rpt-table cashier-table">
                        <thead><tr><th>#</th><th>Item</th><th>Qty Wasted</th><th class="th-waste">Waste Cost</th></tr></thead>
                        <tbody>
                        <?php foreach($ws['top_wasted_items'] as $i=>$row):
                            $pct = $max_w > 0 ? round($row['total_waste_cost']/$max_w*100) : 0;
                        ?>
                        <tr>
                            <td><?=$i+1?></td>
                            <td><strong><?=htmlspecialchars($row['item_name'])?></strong><?php if(!empty($row['variant_name'])): ?><br><small style="color:var(--warm-gray);"><?=htmlspecialchars($row['variant_name'])?></small><?php endif; ?></td>
                            <td><?=number_format($row['total_qty_wasted'])?></td>
                            <td class="waste-cost-cell"><?=peso((float)$row['total_waste_cost'])?><div class="mini-bar"><div class="mini-bar-fill" style="width:<?=$pct?>%;background:var(--danger);"></div></div></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
                </div>
            </div>
            <div class="rpt-card fade-in" style="margin-top:1.25rem;">
                <div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-comment-alt" style="color:var(--danger);"></i> Waste by Reason</div></div>
                <div class="rpt-card-body p-0">
                <?php if(empty($ws['waste_by_reason'])): ?>
                    <div class="no-data p-3"><i class="fas fa-inbox"></i> No data.</div>
                <?php else: ?>
                    <table class="rpt-table cashier-table">
                        <thead><tr><th>Reason</th><th>Items</th><th class="th-waste">Cost</th></tr></thead>
                        <tbody>
                        <?php foreach($ws['waste_by_reason'] as $row): ?>
                        <tr>
                            <td><span class="reason-pill" title="<?=htmlspecialchars($row['reason'])?>"><?=htmlspecialchars($row['reason'])?></span></td>
                            <td><?=$row['item_count']?></td>
                            <td class="waste-cost-cell"><?=peso((float)$row['waste_cost'])?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="rpt-card fade-in">
                <div class="rpt-card-header">
                    <div class="rpt-card-title"><i class="fas fa-list-alt" style="color:var(--danger);"></i> Void Log</div>
                    <?php if(!empty($wi)): ?><span class="rpt-badge badge-danger"><?=count($wi)?> records</span><?php endif; ?>
                </div>
                <div class="rpt-card-body p-0">
                <?php if(empty($wi)): ?>
                    <div class="no-data"><i class="fas fa-check-circle" style="color:var(--success);"></i><br>No voided items in this period.</div>
                <?php else: ?>
                    <div style="overflow-x:auto;"><table class="rpt-table">
                        <thead><tr>
                            <th>Date</th><th>Order #</th>
                            <?php if($is_admin): ?><th>Cashier</th><?php endif; ?>
                            <th>Item</th><th>Qty</th><th class="th-waste">Waste Cost</th><th>Reason</th>
                        </tr></thead>
                        <tbody>
                        <?php foreach($wi as $row): ?>
                        <tr>
                            <td style="white-space:nowrap;"><?=date('M d, Y',strtotime($row['void_date']))?></td>
                            <td><strong><?=htmlspecialchars($row['order_number'])?></strong></td>
                            <?php if($is_admin): ?><td><?=htmlspecialchars($row['cashier_name'])?></td><?php endif; ?>
                            <td><?=htmlspecialchars($row['item_name'])?><?php if(!empty($row['variant_name'])): ?> <span class="badge-variant"><?=htmlspecialchars($row['variant_name'])?></span><?php endif; ?></td>
                            <td><?=$row['quantity']?></td>
                            <td class="waste-cost-cell"><?=peso((float)$row['waste_cost'])?></td>
                            <td><?php if(!empty($row['void_reason'])): ?><span class="reason-pill" title="<?=htmlspecialchars($row['void_reason'])?>"><?=htmlspecialchars($row['void_reason'])?></span><?php else: ?><span style="color:var(--warm-gray);font-size:.82rem;">—</span><?php endif; ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="<?= $is_admin ? 5 : 4 ?>">Total</th>
                                <td class="waste-cost-cell"><?=peso((float)($ws['total_waste_cost']??0))?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table></div>
                <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    </div>

    <?php if (!empty($wi) && ($data['waste_total'] ?? 0) > $limit): ?>
    <div class="pagination-wrapper" style="margin-top: 1.5rem; display: flex; justify-content: center; align-items: center; gap: 0.5rem;">
        <?php
        $total = $data['waste_total'] ?? 0;
        $totalPages = ceil($total / $limit);
        $currentUrl = $_SERVER['REQUEST_URI'] ?? '';
        $currentUrl = preg_replace('/[&?]page=\d+/', '', $currentUrl);
        $separator = strpos($currentUrl, '?') !== false ? '&' : '?';
        ?>
        <span style="font-size: 0.85rem; color: var(--warm-gray);">Showing <?= min($offset + 1, $total) ?>-<?= min($offset + $limit, $total) ?> of <?= $total ?> records</span>
        <?php if ($page > 1): ?>
        <a href="<?= htmlspecialchars($currentUrl . $separator . 'page=' . ($page - 1)) ?>" class="btn-apply" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;"><i class="fas fa-chevron-left"></i> Previous</a>
        <?php endif; ?>
        <span style="font-weight: 600; font-size: 0.9rem;">Page <?= $page ?> of <?= $totalPages ?></span>
        <?php if ($page < $totalPages): ?>
        <a href="<?= htmlspecialchars($currentUrl . $separator . 'page=' . ($page + 1)) ?>" class="btn-apply" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">Next <i class="fas fa-chevron-right"></i></a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php
    // =========================================================================
    // Z-READING (admin)
    // =========================================================================
    elseif ($tab === 'zreading'):
        $zData        = $data['z_data']    ?? null;
        $zHistory     = $data['z_history'] ?? [];
        $selectedDate = $data['z_date']    ?? $filter_date;
    ?>
    <form method="GET" action="<?= $baseUrl ?>/index.php" class="filter-bar">
        <input type="hidden" name="page" value="reports">
        <input type="hidden" name="tab"  value="zreading">
        <label>Date</label>
        <input type="date" name="date" class="form-control filter-input-date"
               value="<?= htmlspecialchars($selectedDate) ?>" max="<?= date('Y-m-d') ?>">
        <div class="filter-actions">
            <button type="submit" class="btn-apply"><i class="fas fa-search me-1"></i> View</button>
            <?php if ($zData): ?>
            <button type="button" class="btn-print" onclick="printZReading()">
                <i class="fas fa-print me-1"></i> Print
            </button>
            <?php else: ?>
            <button type="button" id="btnGenerateZ" class="btn-apply btn-generate">
                <i class="fas fa-bolt me-1"></i> Generate
            </button>
            <?php endif; ?>
            <?php if ($zData): ?>
            <span class="z-locked-badge"><i class="fas fa-lock me-1"></i> Locked</span>
            <small class="text-muted" style="align-self:center;">
                by <?= htmlspecialchars($zData['generated_by_name']) ?>
                at <?= date('g:i A', strtotime($zData['generated_at'])) ?>
            </small>
            <?php endif; ?>
        </div>
    </form>

    <?php if ($zData): ?>
    <!-- Summary cards -->
    <div class="z-summary-cards fade-in">
        <?php
        $zcards = [
            ['Gross Sales',    $zData['gross_sales'],    'fas fa-cash-register',    '#0f4c5c'],
            ['Total Discounts',$zData['total_discounts'],'fas fa-tag',              '#e07b54'],
            ['Total Voids',    $zData['total_voids'],    'fas fa-times-circle',     '#dc3545'],
            ['Total Refunds',  $zData['total_refunds'],  'fas fa-undo',             '#6c757d'],
            ['Net Sales',      $zData['net_sales'],      'fas fa-chart-line',       '#198754'],
        ];
        foreach ($zcards as [$lbl, $val, $icon, $color]):
        ?>
        <div class="stat-card" style="--accent-color:<?= $color ?>;">
            <div class="s-icon" style="background:<?= $color ?>22;color:<?= $color ?>;border-color:<?= $color ?>33;">
                <i class="<?= $icon ?>"></i>
            </div>
            <div>
                <div class="s-label"><?= $lbl ?></div>
                <div class="s-value" style="font-size:1.2rem;color:<?= $color ?>;"><?= peso((float)$val) ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Detail tables -->
    <div class="row g-3 fade-in" id="zreading-content">
        <div class="col-md-6">
            <div class="rpt-card">
                <div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-receipt"></i> Sales Breakdown</div></div>
                <div class="rpt-card-body p-0">
                    <table class="rpt-table">
                        <tbody>
                            <tr><td class="ps-3 text-muted">Gross Sales</td>          <td class="text-end pe-3 fw-semibold"><?= peso((float)$zData['gross_sales']) ?></td></tr>
                            <tr><td class="ps-3 text-muted">VATable Sales</td>         <td class="text-end pe-3"><?= peso((float)$zData['vatable_sales']) ?></td></tr>
                            <tr><td class="ps-3 text-muted">VAT Amount (12%)</td>      <td class="text-end pe-3"><?= peso((float)$zData['vat_amount']) ?></td></tr>
                            <tr><td class="ps-3 text-muted">VAT-Exempt Sales</td>      <td class="text-end pe-3"><?= peso((float)$zData['vat_exempt_sales']) ?></td></tr>
                            <tr><td class="ps-3 text-muted">Zero-Rated Sales</td>      <td class="text-end pe-3"><?= peso((float)$zData['zero_rated_sales']) ?></td></tr>
                            <tr style="background:#f2f6fc;"><td class="ps-3 fw-bold">Net Sales</td><td class="text-end pe-3 fw-bold" style="color:#198754;"><?= peso((float)$zData['net_sales']) ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="rpt-card">
                <div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-percent"></i> Discounts &amp; Adjustments</div></div>
                <div class="rpt-card-body p-0">
                    <table class="rpt-table">
                        <tbody>
                            <tr><td class="ps-3 text-muted">Senior Citizen Discount</td><td class="text-end pe-3"><?= peso((float)$zData['senior_discounts']) ?></td></tr>
                            <tr><td class="ps-3 text-muted">PWD Discount</td>            <td class="text-end pe-3"><?= peso((float)$zData['pwd_discounts']) ?></td></tr>
                            <tr><td class="ps-3 text-muted">Promo Discount</td>          <td class="text-end pe-3"><?= peso((float)$zData['promo_discounts']) ?></td></tr>
                            <tr style="background:#f2f6fc;"><td class="ps-3 fw-semibold">Total Discounts</td><td class="text-end pe-3 fw-semibold" style="color:#e07b54;"><?= peso((float)$zData['total_discounts']) ?></td></tr>
                            <tr><td colspan="2"><hr style="margin:.4rem 1rem;"></td></tr>
                            <tr><td class="ps-3 text-muted">Void Count</td>             <td class="text-end pe-3"><?= (int)$zData['void_count'] ?> order(s)</td></tr>
                            <tr><td class="ps-3 text-muted">Total Voids</td>            <td class="text-end pe-3" style="color:#dc3545;"><?= peso((float)$zData['total_voids']) ?></td></tr>
                            <tr><td class="ps-3 text-muted">Refund Count</td>           <td class="text-end pe-3"><?= (int)$zData['refund_count'] ?> transaction(s)</td></tr>
                            <tr><td class="ps-3 text-muted">Total Refunds</td>          <td class="text-end pe-3" style="color:#6c757d;"><?= peso((float)$zData['total_refunds']) ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="rpt-card">
                <div class="rpt-card-body d-flex flex-wrap gap-4">
                    <div><div class="s-label">Beginning OR</div><div class="fw-bold font-monospace"><?= htmlspecialchars($zData['beginning_or'] ?? '—') ?></div></div>
                    <div><div class="s-label">Ending OR</div><div class="fw-bold font-monospace"><?= htmlspecialchars($zData['ending_or'] ?? '—') ?></div></div>
                    <div><div class="s-label">Total Transactions</div><div class="fw-bold"><?= number_format((int)$zData['transaction_count']) ?></div></div>
                    <div><div class="s-label">Reading Date</div><div class="fw-bold"><?= date('F j, Y', strtotime($zData['reading_date'])) ?></div></div>
                    <div><div class="s-label">Generated By</div><div class="fw-bold"><?= htmlspecialchars($zData['generated_by_name']) ?></div></div>
                    <div><div class="s-label">Generated At</div><div class="fw-bold"><?= date('M j, Y g:i A', strtotime($zData['generated_at'])) ?></div></div>
                </div>
            </div>
        </div>
    </div>

    <?php else: ?>
    <div class="no-data" style="padding:4rem 1rem;">
        <i class="fas fa-file-invoice-dollar"></i>
        No Z-reading for <?= date('F j, Y', strtotime($selectedDate)) ?>.
        <?php if ($selectedDate <= date('Y-m-d')): ?>
        <div class="small mt-2">Click <strong>Generate</strong> to create one.</div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Z-Reading History -->
    <?php if (!empty($zHistory)): ?>
    <div class="mt-4 fade-in">
        <div class="rpt-card">
            <div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-history"></i> Z-Reading History</div></div>
            <div class="rpt-card-body p-0">
                <div style="overflow-x:auto;">
                    <table class="rpt-table">
                        <thead>
                            <tr>
                                <th>Date</th><th>Generated At</th><th>By</th>
                                <th class="text-end">Gross Sales</th>
                                <th class="text-end">Net Sales</th>
                                <th class="text-end">Transactions</th>
                                <th class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($zHistory as $h): ?>
                        <tr>
                            <td class="fw-semibold"><?= date('M j, Y', strtotime($h['reading_date'])) ?></td>
                            <td class="text-muted" style="font-size:.82rem;"><?= date('g:i A', strtotime($h['generated_at'])) ?></td>
                            <td><?= htmlspecialchars($h['generated_by_name']) ?></td>
                            <td class="text-end"><?= peso((float)$h['gross_sales']) ?></td>
                            <td class="text-end fw-semibold" style="color:#198754;"><?= peso((float)$h['net_sales']) ?></td>
                            <td class="text-end"><?= number_format((int)$h['transaction_count']) ?></td>
                            <td class="text-end pe-3">
                                <a href="<?= $baseUrl ?>/index.php?page=reports&tab=zreading&date=<?= urlencode($h['reading_date']) ?>"
                                   class="btn btn-sm btn-outline-secondary">View</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Z-Reading JS payload for print -->
    <?php
    $zPayload = $zData ? json_encode([
        'reading_date'      => date('F j, Y', strtotime($zData['reading_date'])),
        'generated_by'      => $zData['generated_by_name'],
        'generated_at'      => date('M j, Y g:i A', strtotime($zData['generated_at'])),
        'printed_at'        => $print_timestamp . ' (PHT)',
        'beginning_or'      => $zData['beginning_or'] ?? 'N/A',
        'ending_or'         => $zData['ending_or']    ?? 'N/A',
        'transaction_count' => (int)$zData['transaction_count'],
        'gross_sales'       => (float)$zData['gross_sales'],
        'vatable_sales'     => (float)$zData['vatable_sales'],
        'vat_amount'        => (float)$zData['vat_amount'],
        'vat_exempt_sales'  => (float)$zData['vat_exempt_sales'],
        'zero_rated_sales'  => (float)$zData['zero_rated_sales'],
        'senior_discounts'  => (float)$zData['senior_discounts'],
        'pwd_discounts'     => (float)$zData['pwd_discounts'],
        'promo_discounts'   => (float)$zData['promo_discounts'],
        'total_discounts'   => (float)$zData['total_discounts'],
        'void_count'        => (int)$zData['void_count'],
        'total_voids'       => (float)$zData['total_voids'],
        'refund_count'      => (int)$zData['refund_count'],
        'total_refunds'     => (float)$zData['total_refunds'],
        'net_sales'         => (float)$zData['net_sales'],
    ]) : 'null';
    ?>
    <script>
    var _zPayload = <?= $zPayload ?>;
    var _zBaseUrl = <?= json_encode($baseUrl) ?>;

    // Generate button
    var btnGenZ = document.getElementById('btnGenerateZ');
    if (btnGenZ) {
        btnGenZ.addEventListener('click', async function() {
            var d = document.querySelector('input[name="date"]').value;
            if (!d) { alert('Please select a date.'); return; }
            if (!confirm('Generate Z-Reading for ' + d + '?\n\nThis is IRREVERSIBLE — locked once generated.')) return;
            btnGenZ.disabled = true;
            btnGenZ.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Generating…';
            try {
                var fd = new FormData();
                fd.append('date', d);
                var res  = await fetch(_zBaseUrl + '/?page=ajax_generate_z_reading', { method:'POST', body:fd });
                var json = await res.json();
                if (json.success) {
                    window.location.href = _zBaseUrl + '/index.php?page=reports&tab=zreading&date=' + encodeURIComponent(d);
                } else {
                    alert('Error: ' + json.message);
                    btnGenZ.disabled = false;
                    btnGenZ.innerHTML = '<i class="fas fa-bolt me-1"></i> Generate';
                }
            } catch(e) {
                alert('Network error. Please try again.');
                btnGenZ.disabled = false;
                btnGenZ.innerHTML = '<i class="fas fa-bolt me-1"></i> Generate';
            }
        });
    }
    </script>

    <?php endif; // end tab switch ?>

</main>

<footer class="site-footer">
    <div class="site-footer-inner">
        <div><strong>Copyright &copy; <?= date('Y') ?> The Lounge by: CJV & TEK &mdash; Cafeteria.</strong> All rights reserved.</div>
        <div style="font-size:0.78rem;"><b>Version</b> 1.0.0</div>
    </div>
</footer>

<script src="<?= $baseUrl ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
/* ── Sidebar toggle ───────────────────────────────────────────────── */
const menuToggle = document.getElementById('menuToggle');
const sidebar    = document.getElementById('sidebar');
const overlay    = document.getElementById('sidebarOverlay');
function closeSidebar() { sidebar?.classList.remove('show'); overlay?.classList.remove('show'); }
function toggleSidebar(){ sidebar?.classList.toggle('show'); overlay?.classList.toggle('show'); }
menuToggle?.addEventListener('click', toggleSidebar);
overlay?.addEventListener('click', closeSidebar);
document.querySelectorAll('.sidebar .nav-link').forEach(link => {
    link.addEventListener('click', () => { if (window.innerWidth < 992) closeSidebar(); });
});

/* ── Date range guard ─────────────────────────────────────────────── */
(function(){
    const s = document.getElementById('rpt-start');
    const e = document.getElementById('rpt-end');
    if (!s || !e) return;
    function enforce() { if (!s.value) return; e.min = s.value; if (e.value && e.value < s.value) e.value = s.value; }
    s.addEventListener('change', enforce);
    enforce();
})();

/* ── openPrintTab ─────────────────────────────────────────────────── */
function openPrintTab(html) {
    var blob = new Blob([html], { type: 'text/html;charset=utf-8' });
    var url  = URL.createObjectURL(blob);
    var tab  = window.open(url, '_blank');
    if (!tab) { alert('Please allow pop-ups for this site to enable printing.'); URL.revokeObjectURL(url); return; }
    tab.addEventListener('load', function () { URL.revokeObjectURL(url); });
}

/* ── printZReading ────────────────────────────────────────────────── */
function printZReading() {
    var z = _zPayload;
    if (!z) { alert('No Z-reading data to print.'); return; }

    var btn = event.currentTarget;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Printing…';

    fetch('index.php?page=print_z_reading', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(z)
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-print me-1"></i> Print';
        if (res.status === 'no_printer') {
            alert('No thermal printer detected.');
        } else if (res.status !== 'printed') {
            alert('Print error: ' + (res.message || 'Unknown'));
        }
    })
    .catch(function(err) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-print me-1"></i> Print';
        alert('Could not reach the print server.\n' + err);
    });
}
/* ── Thermal + A4 print (closeout / adminshift) ───────────────────── */
var _coPayload = <?php
    if (in_array($tab, ['closeout', 'adminshift'])) {
        echo $co_thermal_payload ?? 'null';
    } else {
        echo 'null';
    }
?>;

function printCoThermal() {
    var payload = _coPayload;
    if (!payload) { alert('Load the close-out report first.'); return; }
    var btn = event.currentTarget;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Printing\u2026';
    fetch('index.php?page=print_closeout', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-receipt"></i> Thermal';
        if (res.status === 'no_printer') {
            alert('No thermal printer detected.\nUse "Print A4" instead.');
        } else if (res.status !== 'printed') {
            alert('Print error: ' + (res.message || 'Unknown'));
        }
    })
    .catch(function(err) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-receipt"></i> Thermal';
        alert('Could not reach the print server.\n' + err);
    });
}

function printCoA4() {
    var co = _coPayload;
    if (!co) { alert('Load the close-out report first.'); return; }
    var isAdminShift = <?= $tab === 'adminshift' ? 'true' : 'false' ?>;
    var docTitle = isAdminShift ? 'Admin Shift Close-out Report' : 'Shift Close-out Report';
    var sigLabel = isAdminShift ? 'Admin Signature' : 'Cashier Signature';
    function peso(v) { return '\u20b1' + parseFloat(v).toLocaleString('en-PH', {minimumFractionDigits:2, maximumFractionDigits:2}); }
    function row(l, v, neg) { return '<tr><td class="rl">' + esc(l) + '</td><td class="rv' + (neg?' neg':'') + '">' + v + '</td></tr>'; }
    function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
    var topRows = '';
    if (co.top_items && co.top_items.length) {
        co.top_items.forEach(function(item, i) {
            var name = (i+1) + '. ' + esc(item.item_name) + (item.variant_name ? ' <small>('+esc(item.variant_name)+')</small>' : '');
            topRows += '<tr><td class="rl">' + name + '</td><td class="rv">\u00d7' + item.units_sold + ' &nbsp;<small style="color:#888;">' + peso(item.total_revenue) + '</small></td></tr>';
        });
    }
    var html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' + docTitle + '</title>'
        + '<style>@page{size:A4 portrait;margin:14mm 16mm;}*{box-sizing:border-box;margin:0;padding:0;}body{font-family:Arial,Helvetica,sans-serif;font-size:11px;color:#000;background:#fff;}.logo{text-align:center;margin-bottom:4px;}.logo .shop{font-size:18px;font-weight:900;letter-spacing:.3px;color:#0f4c5c;}.logo .sub{font-size:9px;color:#666;margin-top:1px;}.doc-title{text-align:center;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:1px;margin:6px 0 2px;}.printed{text-align:center;font-size:9px;color:#888;margin-bottom:8px;}hr{border:none;border-top:1.5px solid #000;margin:6px 0;}.hero{border:2px solid #0f4c5c;border-radius:6px;padding:10px 16px;margin:8px 0 12px;display:flex;align-items:center;justify-content:space-between;}.hero .hl{font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#0f4c5c;}.hero .hv{font-size:22px;font-weight:900;color:#0f4c5c;}.hero .hb{text-align:right;font-size:10px;color:#555;}.hero .hb strong{display:block;font-size:13px;color:#0f4c5c;}.waste-strip{border:1px solid #d64045;border-radius:4px;padding:6px 12px;margin-bottom:10px;display:flex;align-items:center;justify-content:space-between;}.waste-strip .wl{font-size:9px;font-weight:700;color:#d64045;text-transform:uppercase;}.waste-strip .wv{font-size:13px;font-weight:700;color:#d64045;}.grid2{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;}.box{border:1px solid #ccc;border-radius:4px;}.box-head{background:#f0f4f8;font-size:8.5px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#0f4c5c;padding:5px 8px;border-bottom:1px solid #ccc;}table.dt{width:100%;border-collapse:collapse;font-size:10px;}table.dt td{padding:4px 8px;border-bottom:1px dotted #e0e0e0;vertical-align:top;}table.dt tr:last-child td{border-bottom:none;}table.dt td.rl{color:#555;}table.dt td.rv{text-align:right;font-weight:600;color:#000;}table.dt td.rv.neg{color:#d64045;}.turnover-box{border:1.5px solid #0f4c5c;border-radius:3px;padding:5px 8px;display:flex;justify-content:space-between;align-items:center;margin:6px 8px;}.turnover-box .tl{font-size:8.5px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#0f4c5c;}.turnover-box .tv{font-size:14px;font-weight:900;color:#0f4c5c;}.sig-grid{display:grid;grid-template-columns:1fr 1fr;gap:24mm;margin-top:12px;padding-top:10px;border-top:1.5px dashed #999;}.sig-line{border-bottom:1px solid #000;height:28px;margin-bottom:4px;}.sig-lbl{font-size:8px;color:#666;text-align:center;}.footer{text-align:center;font-size:8px;color:#999;margin-top:12px;padding-top:8px;border-top:1px dashed #ccc;}</style></head><body>'
        + '<div class="logo"><div class="shop">The Lounge by: CJV & TEK</div><div class="sub">Cafeteria POS System</div></div>'
        + '<div class="doc-title">' + docTitle + '</div>'
        + '<div class="printed">Printed: ' + esc(co.printed_at) + ' &nbsp;|&nbsp; Shift Date: ' + esc(co.shift_date) + '</div>'
        + '<hr>'
        + '<div class="hero"><div><div class="hl">Cash to Turn Over</div><div class="hv">' + peso(co.cash_to_turn_over) + '</div></div><div class="hb"><strong>' + co.total_orders + '</strong> Orders<br><strong>' + co.payment_count + '</strong> Payments</div></div>'
        + (co.total_waste_cost > 0 ? '<div class="waste-strip"><span class="wl">\u26a0 Waste Written Off</span><span class="wv">' + peso(co.total_waste_cost) + '</span></div>' : '')
        + '<div class="grid2">'
        + '<div class="box"><div class="box-head">' + (isAdminShift ? 'Admin' : 'Cashier') + ' Info</div><table class="dt">' + row('Name', esc(co.cashier_name)) + row('Username', esc(co.username)) + row('Shift Date', esc(co.shift_date)) + '</table></div>'
        + '<div class="box"><div class="box-head">Order Summary</div><table class="dt">' + row('Orders Handled', co.total_orders) + row('Cancelled', co.cancelled_orders) + row('Transactions', co.payment_count) + (co.admin_processed > 0 ? row('Admin-Processed', co.admin_processed) : '') + '</table></div>'
        + '</div>'
        + '<div class="grid2">'
        + '<div class="box"><div class="box-head">Sales Breakdown</div><table class="dt">' + row('Gross Sales', peso(co.gross_sales)) + (co.total_discounts > 0 ? row('Discounts', '\u2212 ' + peso(co.total_discounts), true) : '') + (co.total_refunds > 0 ? row('Refunds', '\u2212 ' + peso(co.total_refunds), true) : '') + row('Net Sales (VAT-incl.)', peso(co.net_sales)) + row('Excl. VAT', peso(co.net_sales_excl)) + row('VAT (12%)', peso(co.vat_collected)) + '</table></div>'
        + '<div class="box"><div class="box-head">Cash Reconciliation</div><table class="dt">' + row('Cash Received', peso(co.cash_received)) + row('Change Given', '\u2212 ' + peso(co.change_given)) + '</table><div class="turnover-box"><span class="tl">Turn Over</span><span class="tv">' + peso(co.cash_to_turn_over) + '</span></div></div>'
        + '</div>'
        + (topRows ? '<div class="box" style="margin-bottom:12px;"><div class="box-head">Top Items This Shift</div><table class="dt">' + topRows + '</table></div>' : '')
        + '<div class="sig-grid"><div><div class="sig-line"></div><div class="sig-lbl">' + sigLabel + ' \u2014 ' + esc(co.cashier_name) + '</div></div><div><div class="sig-line"></div><div class="sig-lbl">Received By (' + (isAdminShift ? 'Owner / Supervisor' : 'Admin / Supervisor') + ')</div></div></div>'
        + '<div class="footer">The Lounge by: CJV & TEK Cafeteria &copy; ' + new Date().getFullYear() + '</div>'
        + '</body></html>';
    var blob = new Blob([html], { type: 'text/html;charset=utf-8' });
    var url  = URL.createObjectURL(blob);
    var tab  = window.open(url, '_blank');
    if (!tab) { alert('Please allow pop-ups for printing.'); URL.revokeObjectURL(url); return; }
    tab.addEventListener('load', function () { URL.revokeObjectURL(url); setTimeout(function () { tab.print(); }, 400); });
}

/* ── printDaily ───────────────────────────────────────────────────── */
var _dailyData = <?php
    if ($tab === 'daily'):
        $s         = $data['summary']  ?? [];
        $cashiers  = $data['cashiers'] ?? [];
        $top_items = $data['top_items']?? [];
        echo json_encode([
            'date'              => date('F d, Y', strtotime($filter_date)),
            'printed_at'        => $print_timestamp . ' (PHT)',
            'gross_revenue'     => number_format((float)($s['gross_revenue']??0), 2),
            'net_sales'         => number_format((float)($s['net_revenue']??0), 2),
            'excl_vat'          => number_format((float)($s['net_revenue_excl_vat']??0), 2),
            'vat_collected'     => number_format((float)($s['total_vat_collected']??0), 2),
            'total_orders'      => number_format($s['total_orders']??0),
            'cancelled'         => $s['cancelled_orders']??0,
            'avg_order'         => number_format((float)($s['avg_order_value']??0), 2),
            'discounts'         => number_format((float)($s['total_discounts']??0), 2),
            'refunds'           => number_format((float)($s['total_refunds']??0), 2),
            'active_cashiers'   => $s['active_cashiers']??0,
            'gross_profit'      => number_format((float)($s['gross_profit']??0), 2),
            'waste_cost'        => number_format((float)($s['total_waste_cost']??0), 2),
            'net_profit'        => number_format((float)($s['net_profit']??0), 2),
            'cashiers'          => array_map(fn($r) => [
                'name'            => $r['cashier'],
                'orders'          => $r['orders_handled'],
                'sales'           => number_format((float)$r['total_sales'], 2),
                'discounts'       => number_format((float)$r['discounts_given'], 2),
                'waste'           => number_format((float)($r['total_waste_cost']??0), 2),
                'cancelled'       => $r['cancelled_orders'],
                'admin_processed' => $r['admin_processed_count'] ?? 0,
            ], $cashiers),
            'top_items'         => array_map(fn($r) => [
                'name'    => $r['item_name'] . (($r['has_variants']??0) ? ' [variants]' : ''),
                'sold'    => $r['units_sold'],
                'revenue' => number_format((float)$r['total_revenue'], 2),
                'profit'  => number_format((float)$r['gross_profit'], 2),
            ], array_slice($top_items, 0, 5)),
        ]);
    else:
        echo 'null';
    endif;
?>;

function printDaily() {
    var d = _dailyData;
    if (!d) { alert('No daily data to print.'); return; }
    var sumRows =
        row2('Report date',           d.date) +
        row2('Printed at',            d.printed_at) +
        sep2() +
        row2('Gross Sales (VAT-incl.)', '\u20b1' + d.gross_revenue, true) +
        row2('Discounts',            '- \u20b1' + d.discounts) +
        row2('Refunds',              '- \u20b1' + d.refunds) +
        row2('Net Sales (VAT-incl.)', '\u20b1' + d.net_sales, true) +
        row2('Net Sales excl. VAT',  '\u20b1' + d.excl_vat) +
        row2('VAT collected (12%)',  '\u20b1' + d.vat_collected) +
        sep2() +
        row2('Gross profit',          '\u20b1' + d.gross_profit) +
        row2('Waste written off',     '- \u20b1' + d.waste_cost) +
        row2('Net profit',            '\u20b1' + d.net_profit, true) +
        sep2() +
        row2('Total orders',          d.total_orders) +
        row2('Cancelled orders',      d.cancelled) +
        row2('Avg order value',       '\u20b1' + d.avg_order) +
        row2('Active cashiers',       d.active_cashiers);
    var itemSection = '';
    if (d.top_items && d.top_items.length) {
        var itemRows = '';
        d.top_items.forEach(function(item) {
            itemRows += '<tr><td class="nm">' + esc2(item.name) + '</td><td class="r">' + item.sold + '</td><td class="r">\u20b1' + item.revenue + '</td><td class="r">\u20b1' + item.profit + '</td></tr>';
        });
        itemSection = sep2() + '<div class="sh">Top Items</div>' +
            '<table><thead><tr><th>Item</th><th class="r">Sold</th><th class="r">Revenue</th><th class="r">Gross Profit</th></tr></thead><tbody>' + itemRows + '</tbody></table>';
    }
    var cashSection = '';
    if (d.cashiers && d.cashiers.length) {
        var cashRows = '';
        d.cashiers.forEach(function(c) {
            cashRows += '<tr><td class="nm">' + esc2(c.name) + '</td><td class="r">' + c.orders + '</td><td class="r">\u20b1' + c.sales + '</td><td class="r" style="color:#d64045;">\u20b1' + c.waste + '</td><td class="r">' + c.cancelled + '</td></tr>';
        });
        cashSection = sep2() + '<div class="sh">Cashier Breakdown</div>' +
            '<table><thead><tr><th>Cashier</th><th class="r">Orders</th><th class="r">Sales</th><th class="r">Waste</th><th class="r">Cancelled</th></tr></thead><tbody>' + cashRows + '</tbody></table>';
    }
    var html =
        '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Daily Report</title>' +
        '<style>html,body{margin:0;padding:0;background:#fff;}@page{size:80mm auto;margin:5mm 4mm;}' +
        'body{font-family:"Courier New",Courier,monospace;font-size:11px;line-height:1.6;color:#000;width:72mm;}' +
        '.c{text-align:center;}.shop{font-size:14px;font-weight:bold;letter-spacing:.5px;}.sub{font-size:9px;color:#555;}.title{font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:1px;margin-top:5px;}' +
        '.sep{border:none;border-top:1px dashed #000;margin:5px 0;}' +
        '.row{display:flex;justify-content:space-between;padding:2.5px 0;}.row .lb{color:#333;font-size:11px;}.row .vl{font-weight:bold;font-size:11px;text-align:right;}.row .vl.hi{font-size:12px;}' +
        '.sh{font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.5px;margin:3px 0 2px;}' +
        'table{width:100%;border-collapse:collapse;font-size:10px;margin-top:2px;}thead th{font-size:9px;font-weight:bold;text-transform:uppercase;letter-spacing:.3px;border-bottom:1px solid #000;padding:2px 1px;}thead th.r{text-align:right;}' +
        'tbody td{padding:2.5px 1px;border-bottom:1px dotted #ccc;vertical-align:top;}tbody td.r{text-align:right;}tbody td.nm{max-width:34mm;word-break:break-word;}' +
        '.footer{text-align:center;font-size:8px;color:#555;margin-top:8px;padding-top:5px;border-top:1px dashed #000;}' +
        '</style></head><body>' +
        '<div class="c"><div class="shop">The Lounge by: CJV & TEK</div><div class="sub">Cafeteria POS System</div><div class="title">Daily Report</div></div>' +
        '<hr class="sep">' + sumRows + itemSection + cashSection +
        '<div class="footer">The Lounge by: CJV & TEK Cafeteria &mdash; ' + d.printed_at + '</div></body></html>';
    html = html.replace('</body></html>',
        '<script>window.addEventListener(\'load\',function(){setTimeout(function(){window.print();},400);});window.addEventListener(\'afterprint\',function(){window.close();});<\/script></body></html>'
    );
    openPrintTab(html);
}

function row2(label, value, highlight) { return '<div class="row"><span class="lb">' + label + '</span><span class="vl' + (highlight ? ' hi' : '') + '">' + value + '</span></div>'; }
function sep2() { return '<hr class="sep">'; }
function esc2(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

/* ── printA4 (generic) ────────────────────────────────────────────── */
function printA4(contentId, title) {
    var btn = event?.currentTarget;
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...'; }
    var el = document.getElementById(contentId);
    if (!el) { if(btn) btn.disabled=false; alert('Nothing to print.'); return; }
    var html =
        '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' + title + '</title>' +
        '<style>@page{size:A4 portrait;margin:12mm 15mm;}*{box-sizing:border-box;margin:0;padding:0;}body{font-family:Arial,Helvetica,sans-serif;font-size:11px;color:#000;background:#fff;}.print-title{font-size:16px;font-weight:bold;text-align:center;margin-bottom:2px;}.print-sub{font-size:10px;text-align:center;color:#555;margin-bottom:10px;padding-bottom:8px;border-bottom:2px solid #000;}.rpt-card{margin-bottom:12px;page-break-inside:avoid;}.rpt-card-header{font-size:12px;font-weight:bold;padding:6px 0;border-bottom:1px solid #ccc;margin-bottom:6px;}.rpt-card-title i{display:none;}table{width:100%;border-collapse:collapse;font-size:10px;margin-bottom:8px;}thead th{background:#f0f0f0;border:1px solid #ccc;padding:5px 6px;text-align:left;font-size:9px;text-transform:uppercase;letter-spacing:.5px;}tbody td{border:1px solid #ddd;padding:5px 6px;vertical-align:top;}tfoot td{border:1px solid #ccc;background:#f0f0f0;padding:5px 6px;font-weight:bold;}tbody tr:nth-child(even) td{background:#fafafa;}.mini-bar,.consumption-bar{display:none;}.rpt-badge,.badge-variant,.badge-base{border:1px solid #999;padding:1px 4px;border-radius:2px;font-size:9px;}.no-data{text-align:center;padding:12px;color:#888;font-style:italic;}canvas{display:none!important;}.comparison-grid,.stats-grid,.z-summary-cards{display:none;}.variance-positive{color:green;font-weight:bold;}.variance-negative{color:red;font-weight:bold;}.variance-zero{color:#555;}.admin-proc-badge{font-size:9px;border:1px solid #ccc;padding:1px 4px;}.profit-breakdown{font-size:9px;color:#555;margin-top:2px;}.waste-cost-cell{color:#d64045;font-weight:bold;}.reason-pill{font-size:9px;border:1px solid #d64045;padding:1px 4px;border-radius:2px;color:#d64045;}.z-locked-badge{display:none;}</style></head><body>' +
        '<div class="print-title">The Lounge by: CJV & TEK Cafeteria</div>' +
        '<div class="print-sub">' + title + ' &nbsp;|&nbsp; Printed: <?= (new DateTime('now', new DateTimeZone('Asia/Manila')))->format('M d, Y h:i A') ?> (PHT)</div>' +
        el.innerHTML + '</body></html>';
    html = html.replace('</body></html>',
        '<script>window.addEventListener(\'load\',function(){setTimeout(function(){window.print();},400);});window.addEventListener(\'afterprint\',function(){window.close();});<\/script></body></html>'
    );
    openPrintTab(html);
    if (btn) setTimeout(function() { btn.disabled = false; btn.innerHTML = '<i class="fas fa-print"></i> Print'; }, 500);
}
</script>
</body>
</html>
