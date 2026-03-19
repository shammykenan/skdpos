<?php
/**
 * reports_view.php
 * All variables come from $ctx (set in reports_controller.php).
 */

extract($ctx);   // tab, filter_date, filter_year, filter_month, range_start, range_end, data, baseUrl

$months_list = [
    1=>'January',2=>'February',3=>'March',4=>'April',
    5=>'May',6=>'June',7=>'July',8=>'August',
    9=>'September',10=>'October',11=>'November',12=>'December'
];

// Helper: currency format
function peso(float $v): string { return '₱' . number_format($v, 2); }
// Helper: badge colour for stock status
function stock_badge(string $s): string {
    $s = strtoupper(trim($s));

    switch ($s) {
        case 'OUT OF STOCK':
            return 'bg-danger';
        case 'LOW':
            return 'bg-warning text-dark';
        case 'IN STOCK':
            return 'bg-success';
        default:
            return 'bg-secondary';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Cafeteria POS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* ── Reuse your dashboard CSS variables ── */
        :root {
            --primary: #0f4c5c;
            --primary-dark: #0a3a47;
            --primary-light: #1f7a8c;
            --secondary: #ff6b4a;
            --secondary-light: #ff8c70;
            --accent: #fee440;
            --navy: #1e2f4e;
            --lavender: #e2e6f0;
            --light-bg: #f8faff;
            --warm-gray: #4a5568;
            --border: #cbd5e0;
            --success: #2c7a5a;
            --danger: #d64045;
            --warning: #f4b942;
            --info: #5e9b9e;
            --dark: #1a202c;
            --sidebar-width: 280px;
            --topbar-height: 72px;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--light-bg);
            color: var(--dark);
            font-size: 14px;
        }

        /* ── Topbar (copied from dashboard) ── */
        .topbar {
            position: fixed; top:0; left:0; right:0;
            height: var(--topbar-height);
            background: white; border-bottom: 1px solid var(--border);
            z-index: 1040; padding: 0 2rem;
            box-shadow: 0 4px 12px rgba(15,76,92,.04);
            display:flex; align-items:center; justify-content:space-between;
        }
        .topbar .logo {
            font-size:1.6rem; font-weight:700; color:var(--primary-dark);
            text-decoration:none; display:flex; align-items:center; gap:.65rem;
        }
        .topbar .logo i { font-size:1.9rem; color:var(--secondary); }
        .topbar .logo span {
            background: linear-gradient(135deg,var(--primary),var(--navy));
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;
        }
        .mobile-menu-btn {
            background:white; border:1px solid var(--border);
            font-size:1.4rem; color:var(--primary); cursor:pointer;
            padding:.5rem 1rem; border-radius:12px; transition:all .2s;
        }
        .mobile-menu-btn:hover { background:var(--secondary); border-color:var(--secondary); color:white; }

        /* ── Sidebar (copied from dashboard) ── */
        .sidebar {
            position:fixed; top:var(--topbar-height); left:0;
            width:var(--sidebar-width);
            height:calc(100vh - var(--topbar-height));
            background:white; border-right:1px solid var(--border);
            overflow-y:auto; z-index:1030;
            transition:transform .25s cubic-bezier(.4,0,.2,1);
        }
        .sidebar-nav { padding:2rem .75rem; }
        .nav-section-title {
            padding:.6rem 1.2rem; font-size:.7rem; font-weight:700;
            text-transform:uppercase; color:var(--warm-gray);
            letter-spacing:1.2px; opacity:.7; margin-top:.8rem;
        }
        .nav-item { margin:.2rem .6rem; }
        .nav-link {
            display:flex; align-items:center; padding:.8rem 1.2rem;
            color:var(--warm-gray); text-decoration:none; border-radius:14px;
            transition:all .2s; font-weight:500; font-size:.95rem;
        }
        .nav-link:hover { background:var(--lavender); color:var(--primary-dark); }
        .nav-link.active { background:var(--primary); color:white; box-shadow:0 6px 12px rgba(15,76,92,.2); }
        .nav-link i { width:24px; margin-right:.85rem; font-size:1.2rem; }
        .sidebar-overlay {
            position:fixed; top:var(--topbar-height); left:0; right:0; bottom:0;
            background:rgba(15,76,92,.2); backdrop-filter:blur(3px);
            z-index:1025; display:none;
        }
        .sidebar-overlay.show { display:block; }

        /* ── Main content ── */
        .main-content {
            margin-left:var(--sidebar-width);
            margin-top:var(--topbar-height);
            padding:2.2rem; min-height:calc(100vh - var(--topbar-height));
        }

        /* ── Page header ── */
        .page-header { margin-bottom:2rem; }
        .page-header h1 {
            font-size:2.1rem; font-weight:700; color:var(--navy);
            display:flex; align-items:center; gap:.7rem;
        }
        .page-header h1 i { color:var(--secondary); }
        .breadcrumb { background:none; padding:.2rem 0; margin:0; font-size:.9rem; }
        .breadcrumb-item a { color:var(--warm-gray); text-decoration:none; font-weight:500; }
        .breadcrumb-item.active { color:var(--primary-dark); font-weight:600; }

        /* ── Report tabs ── */
        .report-tabs {
            display:flex; gap:.5rem; flex-wrap:wrap;
            background:white; padding:1rem 1.2rem;
            border-radius:20px; border:1px solid rgba(203,213,224,.5);
            box-shadow:0 4px 12px rgba(15,76,92,.03);
            margin-bottom:1.8rem;
        }
        .report-tab {
            padding:.55rem 1.2rem; border-radius:40px;
            font-size:.85rem; font-weight:600; cursor:pointer;
            border:1.5px solid var(--border); color:var(--warm-gray);
            background:transparent; text-decoration:none; transition:all .18s;
            display:flex; align-items:center; gap:.4rem;
        }
        .report-tab:hover { border-color:var(--primary-light); color:var(--primary); }
        .report-tab.active {
            background:var(--primary); color:white;
            border-color:var(--primary);
            box-shadow:0 6px 14px rgba(15,76,92,.2);
        }

        /* ── Filter bar ── */
        .filter-bar {
            background:white; border-radius:20px;
            border:1px solid rgba(203,213,224,.5);
            padding:1.1rem 1.5rem; margin-bottom:1.8rem;
            display:flex; align-items:center; gap:1rem; flex-wrap:wrap;
            box-shadow:0 4px 12px rgba(15,76,92,.03);
        }
        .filter-bar label { font-size:.8rem; font-weight:700; color:var(--warm-gray); margin-bottom:0; }
        .filter-bar .form-control, .filter-bar .form-select {
            border-radius:12px; border:1.5px solid var(--border);
            font-size:.9rem; padding:.45rem .9rem; color:var(--dark);
        }
        .filter-bar .form-control:focus, .filter-bar .form-select:focus {
            border-color:var(--primary-light); box-shadow:0 0 0 3px rgba(15,76,92,.08);
        }

        /* ── Stats cards ── */
        .stats-grid {
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
            gap:1.5rem; margin-bottom:2rem;
        }
        .stat-card {
            background:white; border-radius:20px; padding:1.5rem;
            border:1px solid rgba(203,213,224,.5);
            box-shadow:0 6px 16px rgba(15,76,92,.04);
            position:relative; overflow:hidden;
        }
        .stat-card::before {
            content:''; position:absolute; top:0; left:0; right:0; height:4px;
            background:linear-gradient(90deg,var(--primary),var(--secondary));
        }
        .stat-card.c-coral::before { background:var(--secondary); }
        .stat-card.c-teal::before  { background:var(--info); }
        .stat-card.c-green::before { background:var(--success); }
        .stat-card .s-icon {
            width:50px; height:50px; border-radius:16px;
            display:flex; align-items:center; justify-content:center;
            font-size:1.5rem; margin-bottom:1rem;
            background:linear-gradient(145deg,#f0f5fa,white);
            color:var(--primary); border:1px solid rgba(15,76,92,.1);
        }
        .stat-card .s-label {
            font-size:.75rem; font-weight:700; text-transform:uppercase;
            letter-spacing:.8px; color:var(--warm-gray); margin-bottom:.4rem;
        }
        .stat-card .s-value {
            font-size:2rem; font-weight:700; color:var(--navy); line-height:1.1;
        }
        .stat-card .s-sub {
            font-size:.82rem; color:var(--warm-gray); margin-top:.4rem;
        }

        /* ── Cards / tables ── */
        .rpt-card {
            background:white; border-radius:20px;
            border:1px solid rgba(203,213,224,.5);
            box-shadow:0 6px 16px rgba(15,76,92,.03);
            margin-bottom:1.8rem;
        }
        .rpt-card-header {
            padding:1.2rem 1.6rem;
            border-bottom:1px solid rgba(203,213,224,.5);
            display:flex; align-items:center; justify-content:space-between;
        }
        .rpt-card-title {
            font-size:1.05rem; font-weight:650; color:var(--navy);
            display:flex; align-items:center; gap:.5rem;
        }
        .rpt-card-title i { color:var(--secondary); }
        .rpt-card-body { padding:1.4rem 1.6rem; }
        .rpt-card-body.p-0 { padding:0; }

        table.rpt-table { width:100%; border-collapse:separate; border-spacing:0; }
        table.rpt-table thead th {
            background:#f2f6fc;
            border-bottom:2px solid var(--primary-light);
            font-size:.75rem; font-weight:700;
            text-transform:uppercase; letter-spacing:.7px;
            color:var(--primary-dark); padding:1rem 1.2rem; white-space:nowrap;
        }
        table.rpt-table tbody td {
            padding:1rem 1.2rem; vertical-align:middle;
            border-bottom:1px solid #edf2f7; color:var(--dark); background:white;
        }
        table.rpt-table tbody tr:hover td { background:#fafcff; }

        /* ── Badge ── */
        .rpt-badge {
            padding:.35rem .9rem; border-radius:30px;
            font-size:.75rem; font-weight:600; display:inline-block;
        }
        .badge-primary { background:var(--primary); color:white; }
        .badge-coral   { background:var(--secondary); color:white; }
        .badge-warning { background:#fff3cd; color:#856404; }
        .badge-danger  { background:#f8d7da; color:#721c24; }
        .badge-success { background:#d4edda; color:#155724; }

        /* ── Progress bar ── */
        .mini-bar { height:6px; border-radius:6px; background:#e2e8f0; margin-top:4px; }
        .mini-bar-fill { height:100%; border-radius:6px; background:var(--primary); }

        /* ── Print button ── */
        .btn-print {
            padding:.55rem 1.3rem; border-radius:40px;
            font-size:.85rem; font-weight:600; cursor:pointer;
            border:1.5px solid var(--primary); color:var(--primary);
            background:transparent; transition:all .18s;
            display:inline-flex; align-items:center; gap:.45rem;
        }
        .btn-print:hover {
            background:var(--primary); color:white;
            box-shadow:0 6px 14px rgba(15,76,92,.2);
        }
        .btn-apply {
            padding:.55rem 1.4rem; border-radius:40px;
            font-size:.85rem; font-weight:600;
            background:var(--primary); color:white; border:none;
            cursor:pointer; transition:all .18s;
        }
        .btn-apply:hover { background:var(--primary-dark); transform:translateY(-1px); }

        /* ── No data state ── */
        .no-data {
            text-align:center; padding:3rem 1rem; color:var(--warm-gray);
        }
        .no-data i { font-size:2.5rem; margin-bottom:.8rem; opacity:.3; display:block; }

        /* ── Responsive ── */
        @media(max-width:991.98px){
            .sidebar { transform:translateX(-100%); }
            .sidebar.show { transform:translateX(0); }
            .main-content { margin-left:0; padding:1.5rem; }
        }
        @media(max-width:767.98px){
            .main-content { padding:1rem; }
            .stats-grid { grid-template-columns:1fr 1fr; }
            .report-tabs { gap:.3rem; }
            .report-tab { padding:.45rem .9rem; font-size:.8rem; }
        }
        @media(max-width:480px){
            .stats-grid { grid-template-columns:1fr; }
            .topbar .logo span { display:none; }
        }

        /* ── PRINT STYLES ── */
        @media print {
            .topbar, .sidebar, .sidebar-overlay,
            .report-tabs, .filter-bar, .btn-print,
            .mobile-menu-btn, .no-print { display:none !important; }
            .main-content { margin:0 !important; padding:1rem !important; }
            .rpt-card { box-shadow:none !important; border:1px solid #ccc !important; page-break-inside:avoid; }
            body { background:white !important; }
            .print-header { display:block !important; }
        }
        .print-header {
            display:none;
            text-align:center; margin-bottom:1.5rem;
            border-bottom:2px solid var(--primary); padding-bottom:1rem;
        }
        .print-header h2 { color:var(--primary); margin-bottom:.2rem; }
        .print-header p  { color:var(--warm-gray); font-size:.9rem; }

        /* ── fade in ── */
        .fade-in { animation:fadeUp .35s ease forwards; }
        @keyframes fadeUp {
            from { opacity:.5; transform:translateY(6px); }
            to   { opacity:1;  transform:translateY(0); }
        }
    </style>
</head>
<body>

<!-- ============================================================
     TOPBAR
     ============================================================ -->
<div class="topbar">
    <div style="display:flex;align-items:center;gap:1rem;">
        <button class="mobile-menu-btn d-lg-none" id="menuToggle"><i class="fas fa-bars"></i></button>
        <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="logo">
            <i class="fas fa-utensils"></i>
            <span>Dine with Din - Cafeteria</span>
        </a>
    </div>
    <div style="display:flex;align-items:center;gap:1rem;">
        <div class="dropdown">
            <button class="btn btn-link text-decoration-none dropdown-toggle d-flex align-items:center"
                    type="button" data-bs-toggle="dropdown"
                    style="color:var(--navy);font-weight:600;">
                <i class="far fa-user-circle fa-xl me-2" style="font-size:1.6rem;color:var(--secondary);"></i>
                <span class="d-none d-md-inline"><?= htmlspecialchars($_SESSION['full_name'] ?? 'Admin') ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" style="border-radius:16px;border:1px solid var(--border);box-shadow:0 12px 24px rgba(0,0,0,.06);padding:.5rem;">
                <li><a class="dropdown-item" href="<?= $baseUrl ?>/index.php?page=logout" style="border-radius:10px;">
                    <i class="fas fa-sign-out-alt me-2 text-danger"></i> Logout
                </a></li>
            </ul>
        </div>
    </div>
</div>

<!-- ============================================================
     SIDEBAR
     ============================================================ -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<div class="sidebar" id="sidebar">
    <div class="sidebar-nav">
        <div class="nav-section-title">DASHBOARD</div>
        <div class="nav-item">
            <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="nav-link">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </div>
        <div class="nav-section-title">MANAGEMENT</div>
        <div class="nav-item">
            <a href="<?= $baseUrl ?>/index.php?page=manage_menu" class="nav-link">
                <i class="fas fa-utensils"></i> Menu Items
            </a>
        </div>
        <div class="nav-item">
            <a href="<?= $baseUrl ?>/index.php?page=manage_recipes" class="nav-link">
                <i class="fas fa-tags"></i> Recipes
            </a>
        </div>
        <div class="nav-item">
            <a href="<?= $baseUrl ?>/index.php?page=manage_ingredients" class="nav-link">
                <i class="fas fa-boxes"></i> Ingredients
            </a>
        </div>
        <div class="nav-item">
            <a href="<?= $baseUrl ?>/index.php?page=manage_addons" class="nav-link">
                <i class="fas fa-plus-circle"></i> Add-ons
            </a>
        </div>
        <div class="nav-item">
            <a href="<?= $baseUrl ?>/index.php?page=manage_users" class="nav-link">
                <i class="fas fa-users"></i> User Management
            </a>
        </div>
        <div class="nav-section-title">SALES</div>
        <div class="nav-item">
            <a href="<?= $baseUrl ?>/index.php?page=manage_pos" class="nav-link">
                <i class="fas fa-cash-register"></i> POS System
            </a>
        </div>
        <div class="nav-item">
            <a href="<?= $baseUrl ?>/index.php?page=manage_orders" class="nav-link">
                <i class="fas fa-receipt"></i> Orders
            </a>
        </div>
        <div class="nav-item">
                <a href="<?= $baseUrl ?>/index.php?page=reports" class="nav-link">
                    <i class="fas fa-chart-bar"></i>
                    Reports
                </a>
            </div>
        
    </div>
</div>

<!-- ============================================================
     MAIN CONTENT
     ============================================================ -->
<div class="main-content">

    <!-- Page Header -->
    <div class="page-header">
        <h1><i class="fas fa-chart-bar"></i> Reports</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/index.php?page=admin_dashboard">Home</a></li>
                <li class="breadcrumb-item active">Reports</li>
            </ol>
        </nav>
    </div>

    <!-- Print header (visible only when printing) -->
    <div class="print-header">
        <h2>Dine with Din – Cafeteria</h2>
        <p><?= ucfirst($tab) ?> Report &nbsp;|&nbsp; Generated: <?= date('F d, Y h:i A') ?></p>
    </div>

    <!-- ── Report Tabs ── -->
    <div class="report-tabs no-print">
        <?php
        $tabs = [
            'daily'     => ['fas fa-calendar-day',   'Daily'],
            'monthly'   => ['fas fa-calendar-alt',   'Monthly'],
            'yearly'    => ['fas fa-calendar',        'Yearly'],
            'products'  => ['fas fa-utensils',        'Products'],
            'staff'     => ['fas fa-users',           'Staff'],
            'orders'    => ['fas fa-receipt',         'Orders'],
            'inventory' => ['fas fa-boxes',           'Inventory'],
            'audit'     => ['fas fa-shield-alt',      'Audit'],
        ];
        foreach ($tabs as $key => [$icon, $label]):
        ?>
        <a href="<?= $baseUrl ?>/index.php?page=reports&tab=<?= $key ?>"
           class="report-tab <?= $tab === $key ? 'active' : '' ?>">
            <i class="<?= $icon ?>"></i> <?= $label ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- ================================================================
         DAILY REPORT
         ================================================================ -->
    <?php if ($tab === 'daily'): ?>

    <!-- Filter -->
    <form method="GET" action="<?= $baseUrl ?>/index.php" class="filter-bar no-print">
        <input type="hidden" name="page" value="reports">
        <input type="hidden" name="tab"  value="daily">
        <label>Date</label>
        <input type="date" name="date" class="form-control" style="width:175px;"
               value="<?= htmlspecialchars($filter_date) ?>" max="<?= $today ?>">
        <button type="submit" class="btn-apply">
            <i class="fas fa-search me-1"></i> Apply
        </button>
        <button type="button" class="btn-print" onclick="window.print()">
            <i class="fas fa-print"></i> Print
        </button>
    </form>

    <?php $s = $data['summary'] ?? []; ?>

    <!-- Summary cards -->
    <div class="stats-grid fade-in">
        <div class="stat-card">
            <div class="s-icon"><i class="fas fa-money-bill-wave"></i></div>
            <div class="s-label">Gross Revenue</div>
            <div class="s-value"><?= peso((float)($s['gross_revenue'] ?? 0)) ?></div>
            <div class="s-sub">Net: <?= peso((float)($s['net_revenue'] ?? 0)) ?></div>
        </div>
        <div class="stat-card c-coral">
            <div class="s-icon"><i class="fas fa-shopping-cart"></i></div>
            <div class="s-label">Total Orders</div>
            <div class="s-value"><?= number_format($s['total_orders'] ?? 0) ?></div>
            <div class="s-sub">Cancelled: <?= $s['cancelled_orders'] ?? 0 ?></div>
        </div>
        <div class="stat-card c-teal">
            <div class="s-icon"><i class="fas fa-receipt"></i></div>
            <div class="s-label">Avg Order Value</div>
            <div class="s-value"><?= peso((float)($s['avg_order_value'] ?? 0)) ?></div>
            <div class="s-sub">Discounts given: <?= peso((float)($s['total_discounts'] ?? 0)) ?></div>
        </div>
        <div class="stat-card c-green">
            <div class="s-icon"><i class="fas fa-user-tie"></i></div>
            <div class="s-label">Active Cashiers</div>
            <div class="s-value"><?= $s['active_cashiers'] ?? 0 ?></div>
            <div class="s-sub">On <?= date('M d, Y', strtotime($filter_date)) ?></div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Top items today -->
        <div class="col-lg-6">
            <div class="rpt-card fade-in">
                <div class="rpt-card-header">
                    <div class="rpt-card-title"><i class="fas fa-star"></i> Top Items Today</div>
                </div>
                <div class="rpt-card-body p-0">
                    <?php if (empty($data['top_items'])): ?>
                        <div class="no-data"><i class="fas fa-inbox"></i> No sales data for this date.</div>
                    <?php else: ?>
                    <div style="overflow-x:auto;">
                    <table class="rpt-table">
                        <thead><tr>
                            <th>#</th><th>Item</th><th>Category</th>
                            <th>Qty Sold</th><th>Revenue</th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($data['top_items'] as $i => $row): ?>
                        <tr>
                            <td><strong><?= $i+1 ?></strong></td>
                            <td><?= htmlspecialchars($row['item_name']) ?></td>
                            <td><span class="rpt-badge badge-primary"><?= htmlspecialchars($row['category_name']) ?></span></td>
                            <td><?= number_format($row['units_sold']) ?></td>
                            <td><strong><?= peso((float)$row['total_revenue']) ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Peak hours -->
        <div class="col-lg-6">
            <div class="rpt-card fade-in">
                <div class="rpt-card-header">
                    <div class="rpt-card-title"><i class="fas fa-clock"></i> Orders by Hour</div>
                </div>
                <div class="rpt-card-body">
                    <?php if (empty($data['peak_hours'])): ?>
                        <div class="no-data"><i class="fas fa-inbox"></i> No data.</div>
                    <?php else: ?>
                    <canvas id="peakChart" style="max-height:240px;"></canvas>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Order type -->
        <div class="col-lg-6">
            <div class="rpt-card fade-in">
                <div class="rpt-card-header">
                    <div class="rpt-card-title"><i class="fas fa-chart-pie"></i> Order Type Breakdown</div>
                </div>
                <div class="rpt-card-body p-0">
                    <?php if (empty($data['order_types'])): ?>
                        <div class="no-data p-4"><i class="fas fa-inbox"></i> No data.</div>
                    <?php else: ?>
                    <div style="overflow-x:auto;">
                    <table class="rpt-table">
                        <thead><tr><th>Type</th><th>Source</th><th>Orders</th><th>Revenue</th></tr></thead>
                        <tbody>
                        <?php foreach ($data['order_types'] as $row): ?>
                        <tr>
                            <td><?= ucfirst(str_replace('_',' ',$row['order_type'])) ?></td>
                            <td><?= ucfirst(str_replace('_',' ',$row['order_source'])) ?></td>
                            <td><?= number_format($row['total_orders']) ?></td>
                            <td><?= peso((float)$row['revenue']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Cashier perf today -->
        <div class="col-lg-6">
            <div class="rpt-card fade-in">
                <div class="rpt-card-header">
                    <div class="rpt-card-title"><i class="fas fa-user-tie"></i> Cashier Performance</div>
                </div>
                <div class="rpt-card-body p-0">
                    <?php if (empty($data['cashiers'])): ?>
                        <div class="no-data p-4"><i class="fas fa-inbox"></i> No data.</div>
                    <?php else: ?>
                    <div style="overflow-x:auto;">
                    <table class="rpt-table">
                        <thead><tr><th>Cashier</th><th>Orders</th><th>Sales</th><th>Discounts</th></tr></thead>
                        <tbody>
                        <?php foreach ($data['cashiers'] as $row): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['cashier']) ?></strong></td>
                            <td><?= $row['orders_handled'] ?></td>
                            <td><?= peso((float)$row['total_sales']) ?></td>
                            <td><?= peso((float)$row['discounts_given']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($data['peak_hours'])): ?>
    <script>
    (function(){
        const hours = <?= json_encode(array_column($data['peak_hours'],'hour_of_day')) ?>;
        const orders= <?= json_encode(array_column($data['peak_hours'],'total_orders')) ?>;
        const labels= hours.map(h => {
            const ampm = h >= 12 ? 'PM' : 'AM';
            return (h % 12 || 12) + ' ' + ampm;
        });
        new Chart(document.getElementById('peakChart').getContext('2d'),{
            type:'bar',
            data:{
                labels,
                datasets:[{
                    label:'Orders',
                    data: orders,
                    backgroundColor:'rgba(15,76,92,.7)',
                    borderRadius:6, borderSkipped:false
                }]
            },
            options:{
                responsive:true, maintainAspectRatio:false,
                plugins:{ legend:{display:false} },
                scales:{
                    y:{ beginAtZero:true, grid:{color:'rgba(15,76,92,.04)'} },
                    x:{ grid:{display:false} }
                }
            }
        });
    })();
    </script>
    <?php endif; ?>

    <!-- ================================================================
         MONTHLY REPORT
         ================================================================ -->
    <?php elseif ($tab === 'monthly'): ?>

    <!-- Filter -->
    <form method="GET" action="<?= $baseUrl ?>/index.php" class="filter-bar no-print">
        <input type="hidden" name="page" value="reports">
        <input type="hidden" name="tab"  value="monthly">
        <label>Month</label>
        <select name="month" class="form-select" style="width:150px;">
            <?php foreach ($months_list as $num => $name): ?>
            <option value="<?= $num ?>" <?= $num === $filter_month ? 'selected' : '' ?>><?= $name ?></option>
            <?php endforeach; ?>
        </select>
        <label>Year</label>
        <input type="number" name="year" class="form-control" style="width:100px;"
               value="<?= $filter_year ?>" min="2020" max="<?= date('Y') ?>">
        <button type="submit" class="btn-apply"><i class="fas fa-search me-1"></i> Apply</button>
        <button type="button" class="btn-print" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
    </form>

    <?php $s = $data['summary'] ?? []; ?>
    <div class="stats-grid fade-in">
        <div class="stat-card">
            <div class="s-icon"><i class="fas fa-money-bill-wave"></i></div>
            <div class="s-label">Gross Revenue</div>
            <div class="s-value"><?= peso((float)($s['gross_revenue'] ?? 0)) ?></div>
            <div class="s-sub">Net: <?= peso((float)($s['net_revenue'] ?? 0)) ?></div>
        </div>
        <div class="stat-card c-coral">
            <div class="s-icon"><i class="fas fa-shopping-cart"></i></div>
            <div class="s-label">Total Orders</div>
            <div class="s-value"><?= number_format($s['total_orders'] ?? 0) ?></div>
            <div class="s-sub">Cancelled: <?= $s['cancelled_orders'] ?? 0 ?></div>
        </div>
        <div class="stat-card c-teal">
            <div class="s-icon"><i class="fas fa-receipt"></i></div>
            <div class="s-label">Avg Order Value</div>
            <div class="s-value"><?= peso((float)($s['avg_order_value'] ?? 0)) ?></div>
            <div class="s-sub">Discounts: <?= peso((float)($s['total_discounts'] ?? 0)) ?></div>
        </div>
        <div class="stat-card c-green">
            <div class="s-icon"><i class="fas fa-user-tie"></i></div>
            <div class="s-label">Active Cashiers</div>
            <div class="s-value"><?= $s['active_cashiers'] ?? 0 ?></div>
            <div class="s-sub"><?= $months_list[$filter_month] ?> <?= $filter_year ?></div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Daily trend chart -->
        <div class="col-lg-8">
            <div class="rpt-card fade-in">
                <div class="rpt-card-header">
                    <div class="rpt-card-title"><i class="fas fa-chart-line"></i> Daily Revenue Trend</div>
                </div>
                <div class="rpt-card-body">
                    <canvas id="monthlyTrend" style="max-height:260px;"></canvas>
                </div>
            </div>
        </div>
        <!-- Category doughnut -->
        <div class="col-lg-4">
            <div class="rpt-card fade-in">
                <div class="rpt-card-header">
                    <div class="rpt-card-title"><i class="fas fa-chart-pie"></i> By Category</div>
                </div>
                <div class="rpt-card-body">
                    <canvas id="catDoughnut" style="max-height:260px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Top items -->
        <div class="col-12">
            <div class="rpt-card fade-in">
                <div class="rpt-card-header">
                    <div class="rpt-card-title"><i class="fas fa-star"></i> Top Selling Items</div>
                </div>
                <div class="rpt-card-body p-0">
                    <?php if (empty($data['top_items'])): ?>
                        <div class="no-data"><i class="fas fa-inbox"></i> No sales data.</div>
                    <?php else: ?>
                    <div style="overflow-x:auto;">
                    <table class="rpt-table">
                        <thead><tr>
                            <th>#</th><th>Item</th><th>Category</th>
                            <th>Price</th><th>Units Sold</th>
                            <th>Revenue</th><th>Gross Profit</th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($data['top_items'] as $i => $row): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><strong><?= htmlspecialchars($row['item_name']) ?></strong></td>
                            <td><?= htmlspecialchars($row['category_name']) ?></td>
                            <td><?= peso((float)$row['price']) ?></td>
                            <td><?= number_format($row['units_sold']) ?></td>
                            <td><?= peso((float)$row['total_revenue']) ?></td>
                            <td style="color:var(--success);font-weight:600;"><?= peso((float)$row['gross_profit']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Cashier monthly -->
        <div class="col-12">
            <div class="rpt-card fade-in">
                <div class="rpt-card-header">
                    <div class="rpt-card-title"><i class="fas fa-user-tie"></i> Cashier Performance</div>
                </div>
                <div class="rpt-card-body p-0">
                    <?php if (empty($data['cashiers'])): ?>
                        <div class="no-data"><i class="fas fa-inbox"></i> No data.</div>
                    <?php else: ?>
                    <div style="overflow-x:auto;">
                    <table class="rpt-table">
                        <thead><tr>
                            <th>Cashier</th><th>Orders</th><th>Total Sales</th>
                            <th>Avg/Order</th><th>Discounts Given</th><th>Cancelled</th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($data['cashiers'] as $row): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['cashier']) ?></strong><br>
                                <small style="color:var(--warm-gray);"><?= htmlspecialchars($row['username']) ?></small></td>
                            <td><?= $row['orders_handled'] ?></td>
                            <td><strong><?= peso((float)$row['total_sales']) ?></strong></td>
                            <td><?= peso((float)$row['avg_order_value']) ?></td>
                            <td><?= peso((float)$row['discounts_given']) ?></td>
                            <td><?= $row['cancelled_orders'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function(){
        // Daily trend
        const dDates = <?= json_encode(array_column($data['daily_chart'] ?? [], 'sale_date')) ?>;
        const dRev   = <?= json_encode(array_column($data['daily_chart'] ?? [], 'revenue')) ?>;
        if(document.getElementById('monthlyTrend') && dDates.length){
            new Chart(document.getElementById('monthlyTrend').getContext('2d'),{
                type:'line',
                data:{
                    labels: dDates.map(d => { const p=d.split('-'); return p[2]+' '+['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'][parseInt(p[1])-1]; }),
                    datasets:[{
                        label:'Revenue (₱)', data:dRev,
                        backgroundColor:'rgba(15,76,92,.07)',
                        borderColor:'#0f4c5c', borderWidth:2.5,
                        tension:.35, fill:true,
                        pointBackgroundColor:'#ff6b4a', pointRadius:4
                    }]
                },
                options:{
                    responsive:true, maintainAspectRatio:false,
                    plugins:{ legend:{display:false} },
                    scales:{
                        y:{ beginAtZero:true, grid:{color:'rgba(15,76,92,.04)'},
                            ticks:{ callback:v => '₱'+v.toLocaleString() } },
                        x:{ grid:{display:false} }
                    }
                }
            });
        }
        // Category doughnut
        const cLabels = <?= json_encode(array_column($data['categories'] ?? [], 'category_name')) ?>;
        const cData   = <?= json_encode(array_column($data['categories'] ?? [], 'revenue')) ?>;
        if(document.getElementById('catDoughnut') && cLabels.length){
            new Chart(document.getElementById('catDoughnut').getContext('2d'),{
                type:'doughnut',
                data:{
                    labels:cLabels,
                    datasets:[{
                        data:cData,
                        backgroundColor:['#0f4c5c','#ff6b4a','#fee440','#1e2f4e','#5e9b9e','#ff8c70'],
                        borderWidth:0, hoverOffset:8
                    }]
                },
                options:{
                    responsive:true, maintainAspectRatio:false, cutout:'65%',
                    plugins:{
                        legend:{ position:'bottom', labels:{ boxWidth:12, padding:14 } },
                        tooltip:{ callbacks:{ label:ctx => ctx.label+': ₱'+parseFloat(ctx.raw).toLocaleString() } }
                    }
                }
            });
        }
    })();
    </script>

    <!-- ================================================================
         YEARLY REPORT
         ================================================================ -->
    <?php elseif ($tab === 'yearly'): ?>

    <form method="GET" action="<?= $baseUrl ?>/index.php" class="filter-bar no-print">
        <input type="hidden" name="page" value="reports">
        <input type="hidden" name="tab"  value="yearly">
        <label>Year</label>
        <select name="year" class="form-select" style="width:120px;">
            <?php foreach (($data['available_years'] ?? [$filter_year]) as $yr): ?>
            <option value="<?= $yr ?>" <?= $yr == $filter_year ? 'selected' : '' ?>><?= $yr ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn-apply"><i class="fas fa-search me-1"></i> Apply</button>
        <button type="button" class="btn-print" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
    </form>

    <?php $s = $data['summary'] ?? []; ?>
    <div class="stats-grid fade-in">
        <div class="stat-card">
            <div class="s-icon"><i class="fas fa-money-bill-wave"></i></div>
            <div class="s-label">Gross Revenue <?= $filter_year ?></div>
            <div class="s-value"><?= peso((float)($s['gross_revenue'] ?? 0)) ?></div>
            <div class="s-sub">Net: <?= peso((float)($s['net_revenue'] ?? 0)) ?></div>
        </div>
        <div class="stat-card c-coral">
            <div class="s-icon"><i class="fas fa-shopping-cart"></i></div>
            <div class="s-label">Total Orders</div>
            <div class="s-value"><?= number_format($s['total_orders'] ?? 0) ?></div>
            <div class="s-sub">Discounts: <?= peso((float)($s['total_discounts'] ?? 0)) ?></div>
        </div>
        <div class="stat-card c-teal">
            <div class="s-icon"><i class="fas fa-receipt"></i></div>
            <div class="s-label">Avg Order Value</div>
            <div class="s-value"><?= peso((float)($s['avg_order_value'] ?? 0)) ?></div>
        </div>
    </div>

    <!-- Monthly breakdown bar chart -->
    <div class="rpt-card fade-in">
        <div class="rpt-card-header">
            <div class="rpt-card-title"><i class="fas fa-chart-bar"></i> Monthly Revenue <?= $filter_year ?></div>
        </div>
        <div class="rpt-card-body">
            <canvas id="yearlyBar" style="max-height:280px;"></canvas>
        </div>
    </div>

    <!-- Monthly table -->
    <div class="rpt-card fade-in">
        <div class="rpt-card-header">
            <div class="rpt-card-title"><i class="fas fa-table"></i> Month-by-Month Breakdown</div>
        </div>
        <div class="rpt-card-body p-0">
            <div style="overflow-x:auto;">
            <table class="rpt-table">
                <thead><tr><th>Month</th><th>Revenue</th></tr></thead>
                <tbody>
                <?php
                $monthly_keys = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct_m','nov','dec_m'];
                $monthly_labels = ['January','February','March','April','May','June','July','August','September','October','November','December'];
                foreach ($monthly_keys as $mi => $key):
                    $val = (float)($s[$key] ?? 0);
                ?>
                <tr>
                    <td><?= $monthly_labels[$mi] ?></td>
                    <td><strong><?= peso($val) ?></strong>
                        <?php if ($val > 0): ?>
                        <div class="mini-bar"><div class="mini-bar-fill" style="width:<?= min(100,round($val/max(1,(float)($s['gross_revenue']??1))*100)) ?>%;"></div></div>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>

    <!-- Top items yearly -->
    <div class="rpt-card fade-in">
        <div class="rpt-card-header">
            <div class="rpt-card-title"><i class="fas fa-star"></i> Top Selling Items <?= $filter_year ?></div>
        </div>
        <div class="rpt-card-body p-0">
            <?php if (empty($data['top_items'])): ?>
                <div class="no-data"><i class="fas fa-inbox"></i> No sales data.</div>
            <?php else: ?>
            <div style="overflow-x:auto;">
            <table class="rpt-table">
                <thead><tr><th>#</th><th>Item</th><th>Category</th><th>Units Sold</th><th>Revenue</th><th>Gross Profit</th></tr></thead>
                <tbody>
                <?php foreach ($data['top_items'] as $i => $row): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><strong><?= htmlspecialchars($row['item_name']) ?></strong></td>
                    <td><?= htmlspecialchars($row['category_name']) ?></td>
                    <td><?= number_format($row['units_sold']) ?></td>
                    <td><?= peso((float)$row['total_revenue']) ?></td>
                    <td style="color:var(--success);font-weight:600;"><?= peso((float)$row['gross_profit']) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    (function(){
        const mLabels = <?= json_encode(array_slice($monthly_labels,0)) ?>;
        const mData   = <?= json_encode(array_map(fn($k)=>(float)($s[$k]??0), $monthly_keys)) ?>;
        new Chart(document.getElementById('yearlyBar').getContext('2d'),{
            type:'bar',
            data:{
                labels:mLabels,
                datasets:[{
                    label:'Revenue (₱)', data:mData,
                    backgroundColor: mData.map(v => v===Math.max(...mData)?'#ff6b4a':'rgba(15,76,92,.65)'),
                    borderRadius:8, borderSkipped:false
                }]
            },
            options:{
                responsive:true, maintainAspectRatio:false,
                plugins:{ legend:{display:false} },
                scales:{
                    y:{ beginAtZero:true, grid:{color:'rgba(15,76,92,.04)'},
                        ticks:{ callback:v=>'₱'+v.toLocaleString() } },
                    x:{ grid:{display:false} }
                }
            }
        });
    })();
    </script>

    <!-- ================================================================
         PRODUCTS REPORT
         ================================================================ -->
    <?php elseif ($tab === 'products'): ?>

    <form method="GET" action="<?= $baseUrl ?>/index.php" class="filter-bar no-print">
        <input type="hidden" name="page" value="reports">
        <input type="hidden" name="tab"  value="products">
        <label>From</label>
        <input type="date" name="start" class="form-control" style="width:160px;" value="<?= $range_start ?>">
        <label>To</label>
        <input type="date" name="end" class="form-control" style="width:160px;" value="<?= $range_end ?>" max="<?= $today ?>">
        <button type="submit" class="btn-apply"><i class="fas fa-search me-1"></i> Apply</button>
        <button type="button" class="btn-print" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
    </form>

    <div class="row g-4">
        <!-- Top items -->
        <div class="col-lg-8">
            <div class="rpt-card fade-in">
                <div class="rpt-card-header">
                    <div class="rpt-card-title"><i class="fas fa-star"></i> Top Selling Items</div>
                </div>
                <div class="rpt-card-body p-0">
                    <?php if (empty($data['top_items'])): ?>
                        <div class="no-data"><i class="fas fa-inbox"></i> No data for this range.</div>
                    <?php else: ?>
                    <div style="overflow-x:auto;">
                    <table class="rpt-table">
                        <thead><tr><th>#</th><th>Item</th><th>Category</th><th>Price</th><th>Sold</th><th>Revenue</th><th>Profit</th></tr></thead>
                        <tbody>
                        <?php foreach ($data['top_items'] as $i => $row): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><strong><?= htmlspecialchars($row['item_name']) ?></strong></td>
                            <td><span class="rpt-badge badge-primary"><?= htmlspecialchars($row['category_name']) ?></span></td>
                            <td><?= peso((float)$row['price']) ?></td>
                            <td><?= number_format($row['units_sold']) ?></td>
                            <td><?= peso((float)$row['total_revenue']) ?></td>
                            <td style="color:var(--success);font-weight:600;"><?= peso((float)$row['gross_profit']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Category chart -->
        <div class="col-lg-4">
            <div class="rpt-card fade-in">
                <div class="rpt-card-header">
                    <div class="rpt-card-title"><i class="fas fa-chart-pie"></i> By Category</div>
                </div>
                <div class="rpt-card-body">
                    <canvas id="prodCat" style="max-height:260px;"></canvas>
                </div>
            </div>
            <!-- Top add-ons -->
            <div class="rpt-card fade-in mt-4">
                <div class="rpt-card-header">
                    <div class="rpt-card-title"><i class="fas fa-plus-circle"></i> Top Add-ons</div>
                </div>
                <div class="rpt-card-body p-0">
                    <?php if (empty($data['top_addons'])): ?>
                        <div class="no-data p-3"><i class="fas fa-inbox"></i> No add-on data.</div>
                    <?php else: ?>
                    <table class="rpt-table">
                        <thead><tr><th>Add-on</th><th>Qty</th><th>Revenue</th></tr></thead>
                        <tbody>
                        <?php foreach ($data['top_addons'] as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['addon_name']) ?></td>
                            <td><?= number_format($row['times_ordered']) ?></td>
                            <td><?= peso((float)$row['total_revenue']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function(){
        const cl = <?= json_encode(array_column($data['categories']??[],'category_name')) ?>;
        const cv = <?= json_encode(array_column($data['categories']??[],'revenue')) ?>;
        if(cl.length) new Chart(document.getElementById('prodCat').getContext('2d'),{
            type:'doughnut',
            data:{ labels:cl, datasets:[{ data:cv, backgroundColor:['#0f4c5c','#ff6b4a','#fee440','#1e2f4e','#5e9b9e','#ff8c70'], borderWidth:0, hoverOffset:8 }] },
            options:{ responsive:true, maintainAspectRatio:false, cutout:'65%',
                plugins:{ legend:{position:'bottom',labels:{boxWidth:12,padding:14}},
                          tooltip:{callbacks:{label:ctx=>ctx.label+': ₱'+parseFloat(ctx.raw).toLocaleString()}} } }
        });
    })();
    </script>

    <!-- ================================================================
         STAFF REPORT
         ================================================================ -->
    <?php elseif ($tab === 'staff'): ?>

    <form method="GET" action="<?= $baseUrl ?>/index.php" class="filter-bar no-print">
        <input type="hidden" name="page" value="reports">
        <input type="hidden" name="tab"  value="staff">
        <label>From</label>
        <input type="date" name="start" class="form-control" style="width:160px;" value="<?= $range_start ?>">
        <label>To</label>
        <input type="date" name="end"   class="form-control" style="width:160px;" value="<?= $range_end ?>" max="<?= $today ?>">
        <button type="submit" class="btn-apply"><i class="fas fa-search me-1"></i> Apply</button>
        <button type="button" class="btn-print" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
    </form>

    <div class="rpt-card fade-in">
        <div class="rpt-card-header">
            <div class="rpt-card-title"><i class="fas fa-user-tie"></i> Cashier Performance</div>
        </div>
        <div class="rpt-card-body p-0">
            <?php if (empty($data['cashiers'])): ?>
                <div class="no-data"><i class="fas fa-inbox"></i> No data for this range.</div>
            <?php else: ?>
            <div style="overflow-x:auto;">
            <table class="rpt-table">
                <thead><tr>
                    <th>Cashier</th><th>Username</th><th>Orders</th>
                    <th>Total Sales</th><th>Avg/Order</th><th>Discounts Given</th><th>Cancelled</th>
                </tr></thead>
                <tbody>
                <?php
                $grand_sales = array_sum(array_column($data['cashiers'],'total_sales'));
                foreach ($data['cashiers'] as $row):
                    $pct = $grand_sales > 0 ? round($row['total_sales']/$grand_sales*100,1) : 0;
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($row['cashier']) ?></strong></td>
                    <td style="color:var(--warm-gray);"><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= $row['orders_handled'] ?></td>
                    <td>
                        <strong><?= peso((float)$row['total_sales']) ?></strong>
                        <small style="color:var(--warm-gray);"> (<?= $pct ?>%)</small>
                        <div class="mini-bar"><div class="mini-bar-fill" style="width:<?= $pct ?>%;"></div></div>
                    </td>
                    <td><?= peso((float)$row['avg_order_value']) ?></td>
                    <td><?= peso((float)$row['discounts_given']) ?></td>
                    <td><?= $row['cancelled_orders'] > 0
                            ? '<span class="rpt-badge badge-danger">'.$row['cancelled_orders'].'</span>'
                            : '<span class="rpt-badge badge-success">0</span>' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ================================================================
         ORDERS REPORT
         ================================================================ -->
    <?php elseif ($tab === 'orders'): ?>

    <form method="GET" action="<?= $baseUrl ?>/index.php" class="filter-bar no-print">
        <input type="hidden" name="page" value="reports">
        <input type="hidden" name="tab"  value="orders">
        <label>From</label>
        <input type="date" name="start" class="form-control" style="width:160px;" value="<?= $range_start ?>">
        <label>To</label>
        <input type="date" name="end"   class="form-control" style="width:160px;" value="<?= $range_end ?>" max="<?= $today ?>">
        <button type="submit" class="btn-apply"><i class="fas fa-search me-1"></i> Apply</button>
        <button type="button" class="btn-print" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
    </form>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="rpt-card fade-in">
                <div class="rpt-card-header">
                    <div class="rpt-card-title"><i class="fas fa-clock"></i> Peak Hours</div>
                </div>
                <div class="rpt-card-body">
                    <?php if (empty($data['peak_hours'])): ?>
                        <div class="no-data"><i class="fas fa-inbox"></i> No data.</div>
                    <?php else: ?>
                    <canvas id="peakHoursRange" style="max-height:260px;"></canvas>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="rpt-card fade-in">
                <div class="rpt-card-header">
                    <div class="rpt-card-title"><i class="fas fa-chart-pie"></i> Order Types</div>
                </div>
                <div class="rpt-card-body p-0">
                    <?php if (empty($data['order_types'])): ?>
                        <div class="no-data p-4"><i class="fas fa-inbox"></i> No data.</div>
                    <?php else: ?>
                    <table class="rpt-table">
                        <thead><tr><th>Type</th><th>Source</th><th>Orders</th><th>Revenue</th></tr></thead>
                        <tbody>
                        <?php foreach ($data['order_types'] as $row): ?>
                        <tr>
                            <td><?= ucfirst(str_replace('_',' ',$row['order_type'])) ?></td>
                            <td><?= ucfirst(str_replace('_',' ',$row['order_source'])) ?></td>
                            <td><?= number_format($row['total_orders']) ?></td>
                            <td><?= peso((float)$row['revenue']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($data['peak_hours'])): ?>
    <script>
    (function(){
        const h = <?= json_encode(array_column($data['peak_hours'],'hour_of_day')) ?>;
        const o = <?= json_encode(array_column($data['peak_hours'],'total_orders')) ?>;
        new Chart(document.getElementById('peakHoursRange').getContext('2d'),{
            type:'bar',
            data:{
                labels: h.map(v=>{ const ap=v>=12?'PM':'AM'; return (v%12||12)+' '+ap; }),
                datasets:[{ label:'Orders', data:o,
                    backgroundColor: o.map(v=>v===Math.max(...o)?'#ff6b4a':'rgba(15,76,92,.65)'),
                    borderRadius:6, borderSkipped:false }]
            },
            options:{
                responsive:true, maintainAspectRatio:false,
                plugins:{legend:{display:false}},
                scales:{ y:{beginAtZero:true,grid:{color:'rgba(15,76,92,.04)'}}, x:{grid:{display:false}} }
            }
        });
    })();
    </script>
    <?php endif; ?>

    <!-- ================================================================
         INVENTORY REPORT
         ================================================================ -->
    <?php elseif ($tab === 'inventory'): ?>

    <div class="d-flex justify-content-end mb-3 no-print">
        <button type="button" class="btn-print" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
    </div>

    <div class="rpt-card fade-in">
        <div class="rpt-card-header">
            <div class="rpt-card-title"><i class="fas fa-exclamation-triangle"></i> Low / Out of Stock Ingredients</div>
        </div>
        <div class="rpt-card-body p-0">
            <?php if (empty($data['low_stock'])): ?>
                <div class="no-data" style="padding:3rem;">
                    <i class="fas fa-check-circle" style="font-size:2.5rem;color:var(--success);opacity:.7;display:block;margin-bottom:.8rem;"></i>
                    All ingredients are sufficiently stocked.
                </div>
            <?php else: ?>
            <div style="overflow-x:auto;">
            <table class="rpt-table">
                <thead><tr>
                    <th>Ingredient</th><th>Current Stock</th><th>Alert Level</th>
                    <th>Unit</th><th>Status</th><th>Last Updated</th>
                </tr></thead>
                <tbody>
                <?php foreach ($data['low_stock'] as $row):
                    $pct = $row['low_stock_alert'] > 0
                         ? min(100, round($row['stock_quantity']/$row['low_stock_alert']*100))
                         : 0;
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($row['ingredient_name']) ?></strong></td>
                    <td>
                        <?= number_format((float)$row['stock_quantity'],2) ?>
                        <div class="mini-bar"><div class="mini-bar-fill" style="width:<?= $pct ?>%;background:<?= $row['stock_quantity']==0?'var(--danger)':($pct<30?'var(--warning)':'var(--primary)') ?>;"></div></div>
                    </td>
                    <td><?= number_format((float)$row['low_stock_alert'],2) ?></td>
                    <td><?= htmlspecialchars($row['unit']) ?></td>
                    <td><span class="rpt-badge <?= $row['stock_status']==='OUT OF STOCK'?'badge-danger':($row['stock_status']==='LOW'?'badge-warning':'badge-success') ?>"><?= $row['stock_status'] ?></span></td>
                    <td><?= date('M d, Y H:i', strtotime($row['last_updated'])) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ================================================================
         AUDIT REPORT
         ================================================================ -->
    <?php elseif ($tab === 'audit'): ?>

    <form method="GET" action="<?= $baseUrl ?>/index.php" class="filter-bar no-print">
        <input type="hidden" name="page" value="reports">
        <input type="hidden" name="tab"  value="audit">
        <label>From</label>
        <input type="date" name="start" class="form-control" style="width:160px;" value="<?= $range_start ?>">
        <label>To</label>
        <input type="date" name="end"   class="form-control" style="width:160px;" value="<?= $range_end ?>" max="<?= $today ?>">
        <button type="submit" class="btn-apply"><i class="fas fa-search me-1"></i> Apply</button>
        <button type="button" class="btn-print" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
    </form>

    <!-- Discounts & Cancellations -->
    <div class="rpt-card fade-in">
        <div class="rpt-card-header">
            <div class="rpt-card-title"><i class="fas fa-tag"></i> Discounts & Cancellations</div>
        </div>
        <div class="rpt-card-body p-0">
            <?php if (empty($data['discounts'])): ?>
                <div class="no-data"><i class="fas fa-inbox"></i> No discounts or cancellations in this range.</div>
            <?php else: ?>
            <div style="overflow-x:auto;">
            <table class="rpt-table">
                <thead><tr>
                    <th>Date</th><th>Order #</th><th>Cashier</th>
                    <th>Customer</th><th>Amount</th><th>Discount</th><th>Status</th>
                </tr></thead>
                <tbody>
                <?php foreach ($data['discounts'] as $row): ?>
                <tr>
                    <td><?= date('M d, Y', strtotime($row['sale_date'])) ?></td>
                    <td><strong><?= htmlspecialchars($row['order_number']) ?></strong></td>
                    <td><?= htmlspecialchars($row['cashier']) ?></td>
                    <td><?= !empty($row['customer_name']) ? htmlspecialchars($row['customer_name']) : '<span style="color:var(--warm-gray);">Walk-in</span>' ?></td>
                    <td><?= peso((float)$row['total_amount']) ?></td>
                    <td><?= $row['discount']>0 ? '<span class="rpt-badge badge-warning">'.peso((float)$row['discount']).'</span>' : '—' ?></td>
                    <td><span class="rpt-badge <?= $row['status']==='cancelled'?'badge-danger':($row['status']==='completed'?'badge-success':'badge-warning') ?>"><?= ucfirst($row['status']) ?></span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Payment Reconciliation -->
    <div class="rpt-card fade-in">
        <div class="rpt-card-header">
            <div class="rpt-card-title"><i class="fas fa-cash-register"></i> Payment Reconciliation</div>
        </div>
        <div class="rpt-card-body p-0">
            <?php if (empty($data['payments'])): ?>
                <div class="no-data"><i class="fas fa-inbox"></i> No payment data in this range.</div>
            <?php else: ?>
            <div style="overflow-x:auto;">
            <table class="rpt-table">
                <thead><tr>
                    <th>Date</th><th>Cashier</th><th>Transactions</th>
                    <th>Cash Received</th><th>Change Given</th>
                    <th>Net Cash</th><th>Expected</th><th>Variance</th>
                </tr></thead>
                <tbody>
                <?php foreach ($data['payments'] as $row):
                    $variance = (float)$row['net_cash'] - (float)$row['expected_from_orders'];
                ?>
                <tr>
                    <td><?= date('M d, Y', strtotime($row['pay_date'])) ?></td>
                    <td><strong><?= htmlspecialchars($row['cashier']) ?></strong></td>
                    <td><?= $row['transactions'] ?></td>
                    <td><?= peso((float)$row['cash_received']) ?></td>
                    <td><?= peso((float)$row['change_given']) ?></td>
                    <td><strong><?= peso((float)$row['net_cash']) ?></strong></td>
                    <td><?= peso((float)$row['expected_from_orders']) ?></td>
                    <td style="color:<?= $variance>=0?'var(--success)':'var(--danger)' ?>;font-weight:600;">
                        <?= ($variance >= 0 ? '+' : '') . peso($variance) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php endif; // end tab switch ?>

</div><!-- /main-content -->

<!-- Footer -->
<footer style="background:white;border-top:1px solid var(--border);padding:1.4rem 2.2rem;margin-left:var(--sidebar-width);color:var(--warm-gray);">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem;">
        <div><strong>Copyright &copy; <?= date('Y') ?> DinewithDin - Cafeteria.</strong> All rights reserved.</div>
        <div><b>Version</b> 1.0.0</div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const menuToggle     = document.getElementById('menuToggle');
    const sidebar        = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    menuToggle?.addEventListener('click', () => {
        sidebar.classList.toggle('show');
        sidebarOverlay.classList.toggle('show');
    });
    sidebarOverlay?.addEventListener('click', () => {
        sidebar.classList.remove('show');
        sidebarOverlay.classList.remove('show');
    });
</script>
</body>
</html>