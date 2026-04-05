<?php
/**
 * reports_view.php  – v6 (waste-aware)
 */
extract($ctx);

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
    'daily'       => ['fas fa-calendar-day',  'Daily'],
    'monthly'     => ['fas fa-calendar-alt',  'Monthly'],
    'yearly'      => ['fas fa-calendar',      'Yearly'],
    'products'    => ['fas fa-utensils',      'Products'],
    'staff'       => ['fas fa-users',         'Staff'],
    'audit'       => ['fas fa-shield-alt',    'Audit'],
    'consumption' => ['fas fa-boxes',         'Consumption'],
    'waste'       => ['fas fa-trash-alt',     'Waste'],
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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reports &ndash; Cafeteria POS</title>
<link rel="icon" type="image/png" href="<?= $baseUrl ?>/assets/images/logo/logo.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
:root {
    --primary:#0f4c5c; --primary-dark:#0a3a47; --primary-light:#1f7a8c;
    --secondary:#ff6b4a; --accent:#fee440;
    --navy:#1e2f4e; --lavender:#e2e6f0; --light-bg:#f8faff;
    --warm-gray:#4a5568; --border:#cbd5e0;
    --success:#2c7a5a; --danger:#d64045; --warning:#f4b942; --info:#5e9b9e;
    --dark:#1a202c;
    --sidebar-width:280px; --topbar-height:72px;
}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Inter',sans-serif;background:var(--light-bg);color:var(--dark);font-size:14px;}
.topbar{position:fixed;top:0;left:0;right:0;height:var(--topbar-height);background:white;border-bottom:1px solid var(--border);z-index:1040;padding:0 2rem;box-shadow:0 4px 12px rgba(15,76,92,.04);display:flex;align-items:center;justify-content:space-between;}
.topbar .logo{font-size:1.6rem;font-weight:700;color:var(--primary-dark);text-decoration:none;display:flex;align-items:center;gap:.65rem;}
.topbar .logo i{font-size:1.9rem;color:var(--secondary);}
.topbar .logo span{background:linear-gradient(135deg,var(--primary),var(--navy));-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
.mobile-menu-btn{background:white;border:1px solid var(--border);font-size:1.4rem;color:var(--primary);cursor:pointer;padding:.5rem 1rem;border-radius:12px;transition:all .2s;}
.mobile-menu-btn:hover{background:var(--secondary);border-color:var(--secondary);color:white;}
.sidebar{position:fixed;top:var(--topbar-height);left:0;width:var(--sidebar-width);height:calc(100vh - var(--topbar-height));background:white;border-right:1px solid var(--border);overflow-y:auto;z-index:1030;transition:transform .25s cubic-bezier(.4,0,.2,1);}
.sidebar-nav{padding:2rem .75rem;}
.nav-section-title{padding:.6rem 1.2rem;font-size:.7rem;font-weight:700;text-transform:uppercase;color:var(--warm-gray);letter-spacing:1.2px;opacity:.7;margin-top:.8rem;}
.nav-item{margin:.2rem .6rem;}
.nav-link{display:flex;align-items:center;padding:.8rem 1.2rem;color:var(--warm-gray);text-decoration:none;border-radius:14px;transition:all .2s;font-weight:500;font-size:.95rem;}
.nav-link:hover{background:var(--lavender);color:var(--primary-dark);}
.nav-link.active{background:var(--primary);color:white;box-shadow:0 6px 12px rgba(15,76,92,.2);}
.nav-link i{width:24px;margin-right:.85rem;font-size:1.2rem;}
.sidebar-overlay{position:fixed;top:var(--topbar-height);left:0;right:0;bottom:0;background:rgba(15,76,92,.2);backdrop-filter:blur(3px);z-index:1025;display:none;}
.sidebar-overlay.show{display:block;}
.main-content{margin-left:var(--sidebar-width);margin-top:var(--topbar-height);padding:2.2rem;min-height:calc(100vh - var(--topbar-height));}
.page-header{margin-bottom:2rem;}
.page-header h1{font-size:2.1rem;font-weight:700;color:var(--navy);display:flex;align-items:center;gap:.7rem;}
.page-header h1 i{color:var(--secondary);}
.breadcrumb{background:none;padding:.2rem 0;margin:0;font-size:.9rem;}
.breadcrumb-item a{color:var(--warm-gray);text-decoration:none;font-weight:500;}
.breadcrumb-item.active{color:var(--primary-dark);font-weight:600;}
.report-tabs{display:flex;gap:.5rem;flex-wrap:wrap;background:white;padding:1rem 1.2rem;border-radius:20px;border:1px solid rgba(203,213,224,.5);box-shadow:0 4px 12px rgba(15,76,92,.03);margin-bottom:1.8rem;}
.report-tab{padding:.55rem 1.2rem;border-radius:40px;font-size:.85rem;font-weight:600;cursor:pointer;border:1.5px solid var(--border);color:var(--warm-gray);background:transparent;text-decoration:none;transition:all .18s;display:flex;align-items:center;gap:.4rem;}
.report-tab:hover{border-color:var(--primary-light);color:var(--primary);}
.report-tab.active{background:var(--primary);color:white;border-color:var(--primary);box-shadow:0 6px 14px rgba(15,76,92,.2);}
.report-tab.waste-tab.active{background:var(--danger);border-color:var(--danger);box-shadow:0 6px 14px rgba(214,64,69,.25);}
.report-tab.cashier-tab.active{background:var(--secondary);border-color:var(--secondary);}
.report-tab.closeout-tab.active{background:var(--navy);border-color:var(--navy);}
.filter-bar{background:white;border-radius:20px;border:1px solid rgba(203,213,224,.5);padding:1.1rem 1.5rem;margin-bottom:1.8rem;display:flex;align-items:center;gap:1rem;flex-wrap:wrap;box-shadow:0 4px 12px rgba(15,76,92,.03);}
.filter-bar label{font-size:.8rem;font-weight:700;color:var(--warm-gray);margin-bottom:0;}
.filter-bar .form-control,.filter-bar .form-select{border-radius:12px;border:1.5px solid var(--border);font-size:.9rem;padding:.45rem .9rem;color:var(--dark);}
.filter-bar .form-control:focus,.filter-bar .form-select:focus{border-color:var(--primary-light);box-shadow:0 0 0 3px rgba(15,76,92,.08);}
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:1.5rem;margin-bottom:2rem;}
.stat-card{background:white;border-radius:20px;padding:1.5rem;border:1px solid rgba(203,213,224,.5);box-shadow:0 6px 16px rgba(15,76,92,.04);position:relative;overflow:hidden;}
.stat-card::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,var(--primary),var(--secondary));}
.stat-card.c-coral::before{background:var(--secondary);}
.stat-card.c-teal::before{background:var(--info);}
.stat-card.c-green::before{background:var(--success);}
.stat-card.c-warning::before{background:var(--warning);}
.stat-card.c-danger::before{background:var(--danger);}
.stat-card.c-netprofit::before{background:linear-gradient(90deg,var(--success),var(--info));}
.s-icon{width:48px;height:48px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;margin-bottom:1rem;background:linear-gradient(145deg,#f0f5fa,white);color:var(--primary);border:1px solid rgba(15,76,92,.1);}
.s-label{font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--warm-gray);margin-bottom:.4rem;}
.s-value{font-size:1.9rem;font-weight:700;color:var(--navy);line-height:1.1;word-break:break-word;overflow-wrap:anywhere;}
.s-value.negative{color:var(--danger);}
.s-value.positive{color:var(--success);}
.s-sub{font-size:.82rem;color:var(--warm-gray);margin-top:.4rem;}
.s-compare{font-size:.78rem;margin-top:.3rem;}
.vat-breakdown{font-size:.78rem;color:var(--warm-gray);margin-top:.3rem;padding:.3rem .6rem;background:rgba(15,76,92,.04);border-radius:8px;border-left:3px solid var(--primary-light);}
.vat-breakdown span{font-weight:600;color:var(--primary-dark);}
.refund-breakdown{margin-top:8px;padding-top:8px;border-top:1px solid var(--border);}
.refund-breakdown .refund-label{font-size:0.75rem;color:var(--warm-gray);margin-bottom:2px;}
.refund-breakdown .refund-amount{font-weight:600;color:var(--danger);}
.net-revenue-breakdown{margin-top:8px;padding-top:8px;border-top:1px solid var(--border);}
.net-revenue-breakdown .net-revenue-label{font-size:0.8rem;font-weight:600;color:var(--primary-dark);}
.net-revenue-breakdown .net-revenue-amount{font-weight:700;color:var(--success);font-size:1.1rem;}
@media (max-width: 768px) {
    .refund-breakdown .refund-amount, .net-revenue-breakdown .net-revenue-amount { font-size: 1rem; }
    .net-revenue-breakdown .net-revenue-label { font-size: 0.75rem; }
    .refund-breakdown .refund-label { font-size: 0.7rem; }
}
@media (max-width: 480px) {
    .refund-breakdown .refund-amount, .net-revenue-breakdown .net-revenue-amount { font-size: 0.9rem; }
    .net-revenue-breakdown .net-revenue-label { font-size: 0.7rem; }
    .refund-breakdown .refund-label { font-size: 0.65rem; }
}
.profit-breakdown{font-size:.78rem;color:var(--warm-gray);margin-top:.3rem;padding:.3rem .6rem;background:rgba(44,122,90,.05);border-radius:8px;border-left:3px solid var(--success);}
.profit-breakdown span{font-weight:600;}
.profit-breakdown .pos{color:var(--success);}
.profit-breakdown .neg{color:var(--danger);}
.comparison-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:1.8rem;}
.compare-card{background:white;border-radius:16px;padding:1rem 1.2rem;border:1px solid rgba(203,213,224,.5);box-shadow:0 4px 10px rgba(15,76,92,.03);}
.compare-label{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--warm-gray);margin-bottom:.3rem;}
.compare-current{font-size:1.3rem;font-weight:700;color:var(--navy);}
.compare-prev{font-size:.78rem;color:var(--warm-gray);margin-top:.15rem;}
.variance-positive{color:var(--success);font-weight:700;}
.variance-negative{color:var(--danger);font-weight:700;}
.variance-zero{color:var(--warm-gray);font-weight:600;}
.admin-proc-badge{display:inline-flex;align-items:center;gap:.3rem;padding:.2rem .6rem;border-radius:20px;font-size:.7rem;font-weight:600;background:rgba(15,76,92,.08);color:var(--primary-dark);}
.rpt-card{background:white;border-radius:20px;border:1px solid rgba(203,213,224,.5);box-shadow:0 6px 16px rgba(15,76,92,.03);margin-bottom:1.8rem;}
.rpt-card-header{padding:1.2rem 1.6rem;border-bottom:1px solid rgba(203,213,224,.5);display:flex;align-items:center;justify-content:space-between;}
.rpt-card-title{font-size:1.05rem;font-weight:650;color:var(--navy);display:flex;align-items:center;gap:.5rem;}
.rpt-card-title i{color:var(--secondary);}
.rpt-card-body{padding:1.4rem 1.6rem;}
.rpt-card-body.p-0{padding:0;}
table.rpt-table{width:100%;border-collapse:separate;border-spacing:0;}
table.rpt-table thead th{background:#f2f6fc;border-bottom:2px solid var(--primary-light);font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--primary-dark);padding:1rem 1.2rem;white-space:nowrap;}
table.rpt-table tbody td{padding:1rem 1.2rem;vertical-align:middle;border-bottom:1px solid #edf2f7;color:var(--dark);background:white;}
table.rpt-table tbody tr:hover td{background:#fafcff;}
table.rpt-table tfoot td{padding:1rem 1.2rem;font-weight:700;background:#f2f6fc;border-top:2px solid var(--primary-light);}
/* waste-specific table */
table.rpt-table thead th.th-waste{background:rgba(214,64,69,.06);color:var(--danger);}
.waste-cost-cell{color:var(--danger);font-weight:600;}
.rpt-badge{padding:.35rem .9rem;border-radius:30px;font-size:.75rem;font-weight:600;display:inline-block;}
.badge-primary{background:var(--primary);color:white;}
.badge-warning{background:#fff3cd;color:#856404;}
.badge-danger{background:#f8d7da;color:#721c24;}
.badge-success{background:#d4edda;color:#155724;}
.badge-info{background:#cff4fc;color:#0c5460;}
.badge-variant{background:rgba(255,107,74,.1);color:var(--secondary);border:1px solid rgba(255,107,74,.25);padding:.2rem .55rem;border-radius:20px;font-size:.7rem;font-weight:600;}
.badge-base{background:rgba(15,76,92,.08);color:var(--primary-dark);border:1px solid rgba(15,76,92,.15);padding:.2rem .55rem;border-radius:20px;font-size:.7rem;font-weight:600;}
.mini-bar{height:5px;border-radius:6px;background:#e2e8f0;margin-top:5px;}
.mini-bar-fill{height:100%;border-radius:6px;background:var(--primary);}
.btn-apply{padding:.55rem 1.4rem;border-radius:40px;font-size:.85rem;font-weight:600;background:var(--primary);color:white;border:none;cursor:pointer;transition:all .18s;}
.btn-apply:hover{background:var(--primary-dark);transform:translateY(-1px);}
.btn-print{padding:.55rem 1.3rem;border-radius:40px;font-size:.85rem;font-weight:600;border:1.5px solid var(--primary);color:var(--primary);background:transparent;cursor:pointer;transition:all .18s;display:inline-flex;align-items:center;gap:.4rem;text-decoration:none;}
.btn-print:hover{background:var(--primary);color:white;}
.no-data{text-align:center;padding:3rem 1rem;color:var(--warm-gray);}
.no-data i{font-size:2.5rem;margin-bottom:.8rem;opacity:.3;display:block;}
.consumption-bar{height:8px;border-radius:6px;background:#e2e8f0;margin-top:4px;}
.consumption-bar-fill{height:100%;border-radius:6px;background:var(--secondary);}
.recipe-source-note{font-size:.7rem;color:var(--warm-gray);margin-top:2px;}
.closeout-wrap{max-width:480px;margin:0 auto;}
.receipt{background:white;border-radius:20px;border:1px solid rgba(203,213,224,.5);box-shadow:0 8px 24px rgba(15,76,92,.06);padding:2rem 2.2rem;}
.receipt-header{text-align:center;padding-bottom:1.2rem;border-bottom:2px dashed var(--border);margin-bottom:1.4rem;}
.receipt-header .shop-name{font-size:1.4rem;font-weight:700;color:var(--primary-dark);}
.receipt-header .shop-sub{font-size:.85rem;color:var(--warm-gray);margin-top:.2rem;}
.receipt-header .doc-title{font-size:1rem;font-weight:700;color:var(--navy);margin-top:1rem;letter-spacing:.5px;text-transform:uppercase;}
.receipt-row{display:flex;justify-content:space-between;align-items:baseline;padding:.55rem 0;border-bottom:1px solid rgba(203,213,224,.3);}
.receipt-row:last-of-type{border-bottom:none;}
.receipt-row .rl{color:var(--warm-gray);font-size:.9rem;}
.receipt-row .rv{font-weight:600;color:var(--dark);font-size:.9rem;}
.receipt-section-title{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--primary-dark);padding:.6rem 0 .2rem;border-top:1px dashed var(--border);margin-top:.4rem;}
.receipt-total{background:var(--primary);color:white;border-radius:14px;padding:1.1rem 1.4rem;margin:1.2rem 0;display:flex;justify-content:space-between;align-items:center;gap:.5rem;flex-wrap:wrap;}
.receipt-total .tl{font-size:.85rem;font-weight:600;opacity:.85;text-transform:uppercase;letter-spacing:.5px;}
.receipt-total .tv{font-size:1.8rem;font-weight:700;}
.receipt-waste-box{background:rgba(214,64,69,.06);border:1px solid rgba(214,64,69,.2);border-radius:14px;padding:.9rem 1.4rem;margin:.5rem 0;display:flex;justify-content:space-between;align-items:center;}
.receipt-waste-box .tl{font-size:.82rem;font-weight:600;color:var(--danger);text-transform:uppercase;letter-spacing:.4px;}
.receipt-waste-box .tv{font-size:1.3rem;font-weight:700;color:var(--danger);}
.receipt-sig{display:grid;grid-template-columns:1fr 1fr;gap:2rem;margin-top:1.8rem;padding-top:1.2rem;border-top:2px dashed var(--border);}
.sig-block .sig-line{border-bottom:1.5px solid var(--dark);height:40px;margin-bottom:.4rem;}
.sig-block .sig-label{font-size:.78rem;color:var(--warm-gray);text-align:center;}
.receipt-footer{text-align:center;margin-top:1.2rem;font-size:.8rem;color:var(--warm-gray);}
.site-footer{background:white;border-top:1px solid var(--border);padding:1.4rem 2.2rem;margin-left:var(--sidebar-width);color:var(--warm-gray);}
.site-footer-inner{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem;}
.filter-input-date{width:170px;}
.filter-input-month{width:150px;}
.filter-input-year{width:100px;}
.filter-input-range{width:160px;}
.filter-actions{display:flex;gap:.5rem;flex-wrap:wrap;}
/* waste reason pill */
.reason-pill{display:inline-block;padding:.2rem .6rem;border-radius:20px;font-size:.72rem;font-weight:600;background:rgba(214,64,69,.08);color:var(--danger);border:1px solid rgba(214,64,69,.2);max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
@media (max-width:991.98px){
    .sidebar{transform:translateX(-100%);}
    .sidebar.show{transform:translateX(0);}
    .main-content{margin-left:0;padding:1.5rem;}
    .site-footer{margin-left:0;}
    .page-header h1{font-size:1.7rem;}
    .s-value{font-size:1.6rem;}
    .rpt-card-header{flex-wrap:wrap;gap:.5rem;}
}
@media (max-width:767.98px){
    .main-content{padding:1rem;}
    .stats-grid{grid-template-columns:1fr 1fr;gap:1rem;}
    .stat-card{padding:1.1rem;}
    .s-icon{width:38px;height:38px;font-size:1.1rem;margin-bottom:.6rem;}
    .s-value{font-size:1.4rem;}
    .report-tabs{padding:.7rem .8rem;gap:.35rem;}
    .report-tab{padding:.4rem .75rem;font-size:.78rem;}
    .filter-bar{padding:.9rem 1rem;gap:.6rem;flex-direction:column;align-items:stretch;}
    .filter-bar label{margin-bottom:-.3rem;}
    .filter-input-date,.filter-input-month,.filter-input-year,.filter-input-range,.filter-bar .form-control,.filter-bar .form-select{width:100% !important;}
    .filter-actions{width:100%;}
    .filter-actions .btn-apply,.filter-actions .btn-print,.filter-bar .btn-apply,.filter-bar .btn-print{flex:1;justify-content:center;}
    .rpt-card-header{padding:.9rem 1rem;}
    .rpt-card-body{padding:1rem;}
    .rpt-card-body.p-0{padding:0;}
    .receipt{padding:1.2rem 1rem;}
    .receipt-total .tv{font-size:1.4rem;}
    .site-footer{padding:1rem 1.2rem;}
    .site-footer-inner{flex-direction:column;align-items:flex-start;gap:.25rem;font-size:.82rem;}
    .page-header h1{font-size:1.4rem;}
    .page-header{margin-bottom:1rem;}
    .comparison-grid{grid-template-columns:1fr 1fr;}
}
@media (max-width:480px){
    .topbar{padding:0 .75rem;}
    .topbar .logo span{display:none;}
    .stats-grid{grid-template-columns:1fr;}
    .report-tab i{display:none;}
    .report-tab{padding:.4rem .65rem;font-size:.75rem;}
    .page-header h1{font-size:1.2rem;}
    .receipt-sig{grid-template-columns:1fr;gap:1rem;}
    .comparison-grid{grid-template-columns:1fr;}
}
.fade-in{animation:fadeUp .35s ease forwards;}
@keyframes fadeUp{from{opacity:.4;transform:translateY(8px);}to{opacity:1;transform:translateY(0);}}
</style>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
    <div style="display:flex;align-items:center;gap:1rem;">
        <button class="mobile-menu-btn d-lg-none" id="menuToggle"><i class="fas fa-bars"></i></button>
        <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="logo">
            <i class="fas fa-utensils"></i>
            <span>Dine with Din - Cafeteria</span>
        </a>
    </div>
    <div>
        <div class="dropdown">
            <button class="btn btn-link text-decoration-none dropdown-toggle" type="button" data-bs-toggle="dropdown" style="color:var(--navy);font-weight:600;">
                <i class="far fa-user-circle me-2" style="font-size:1.5rem;color:var(--secondary);"></i>
                <span class="d-none d-md-inline"><?= htmlspecialchars($session_name) ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" style="border-radius:16px;padding:.5rem;">
                <li><a class="dropdown-item" href="<?= $baseUrl ?>/index.php?page=logout" style="border-radius:10px;">
                    <i class="fas fa-sign-out-alt me-2 text-danger"></i> Logout
                </a></li>
            </ul>
        </div>
    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-nav">
        <?php if (hasRole('admin')): ?>
        <div class="nav-section-title">DASHBOARD</div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></div>
        <div class="nav-section-title">MANAGEMENT</div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_menu" class="nav-link"><i class="fas fa-utensils"></i> Menu Items</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_recipes" class="nav-link"><i class="fas fa-tags"></i> Recipes</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_ingredients" class="nav-link"><i class="fas fa-boxes"></i> Ingredients</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_addons" class="nav-link"><i class="fas fa-plus-circle"></i> Add-ons</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_tables" class="nav-link"><i class="fas fa-chair"></i> Tables</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_users" class="nav-link"><i class="fas fa-users"></i> User Management</a></div>
        <div class="nav-section-title">SALES</div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_pos" class="nav-link"><i class="fas fa-cash-register"></i> POS System</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_orders" class="nav-link"><i class="fas fa-receipt"></i> Orders</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=reports" class="nav-link active"><i class="fas fa-chart-bar"></i> Reports</a></div>
        <?php endif; ?>
        <?php if (hasRole('cashier') && !hasRole('admin')): ?>
        <div class="nav-section-title">Management</div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_tables" class="nav-link"><i class="fas fa-chair"></i> Tables</a></div>
        <div class="nav-section-title">SALES</div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_pos" class="nav-link"><i class="fas fa-cash-register"></i> POS System</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_orders" class="nav-link"><i class="fas fa-receipt"></i> Orders</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=reports" class="nav-link active"><i class="fas fa-chart-bar"></i> Reports</a></div>
        <?php endif; ?>
    </div>
</div>

<!-- MAIN -->
<div class="main-content">

    <div class="page-header">
        <h1><i class="fas fa-chart-bar"></i> <?= $is_admin ? 'Reports' : 'My Reports' ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/index.php?page=<?= $is_admin ? 'admin_dashboard' : 'manage_pos' ?>">Home</a></li>
                <li class="breadcrumb-item active">Reports</li>
            </ol>
        </nav>
    </div>

    <!-- TABS -->
    <div class="report-tabs">
        <?php foreach ($tab_meta as $key => [$icon, $label]): ?>
        <a href="<?= $baseUrl ?>/index.php?page=reports&tab=<?= $key ?>"
           class="report-tab <?= $tab===$key?'active':'' ?> <?= $key==='waste'?'waste-tab':'' ?> <?= $key==='closeout'?'closeout-tab':(!$is_admin && $key!=='waste'?'cashier-tab':'') ?>">
            <i class="<?= $icon ?>"></i> <?= $label ?>
        </a>
        <?php endforeach; ?>
    </div>

    <?php
    // ── Shared helpers ────────────────────────────────────────────────────────

    function render_comparison(array $cmp, string $prev_label): void {
        if (empty($cmp['current'])) return;
        $cur  = $cmp['current'];
        $prev = $cmp['previous'];
        $chg  = $cmp['change'];
        $metrics = [
            'net_revenue'      => ['Revenue',   fn($v) => peso((float)$v)],
            'total_orders'     => ['Orders',    fn($v) => number_format((int)$v)],
            'avg_order_value'  => ['Avg Order', fn($v) => peso((float)$v)],
            'cancelled_orders' => ['Cancelled', fn($v) => number_format((int)$v)],
        ];
        echo '<div class="comparison-grid fade-in">';
        foreach ($metrics as $key => [$label, $fmt]) {
            $badge = isset($chg[$key]) ? pct_badge((float)$chg[$key]) : '';
            if ($key === 'cancelled_orders' && isset($chg[$key])) {
                $c     = (float)$chg[$key];
                $arrow = $c >= 0 ? '▲' : '▼';
                $cls   = $c >= 0 ? 'badge-danger' : 'badge-success';
                $badge = '<span class="rpt-badge ' . $cls . '" style="font-size:.7rem;">' . $arrow . ' ' . abs($c) . '%</span>';
            }
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
            if ($show_profit) echo '<td style="color:var(--success);font-weight:600;">' . peso((float)$row['gross_profit']) . '</td>';
            if ($show_excl_vat) echo '<td style="color:var(--warm-gray);font-size:.85rem;">' . peso((float)($row['revenue_excl_vat'] ?? 0)) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table></div>';
    }

    // Gross profit card — Net Revenue minus COGS of kept items
    function render_gross_profit_card(array $s): void {
        $net_revenue = (float)($s['net_revenue'] ?? 0);
        $gross_profit = (float)($s['gross_profit'] ?? 0);
        $cls = $gross_profit >= 0 ? 'positive' : 'negative';
        echo '
        <div class="stat-card c-grossprofit">
            <div class="s-icon" style="color:var(--primary);"><i class="fas fa-chart-bar"></i></div>
            <div class="s-label">Gross Profit</div>
            <div class="s-value ' . $cls . '">' . peso($gross_profit) . '</div>
            <div class="profit-breakdown">
                Net Revenue: <span class="pos">' . peso($net_revenue) . '</span>
                &nbsp;&minus;&nbsp; COGS: <span class="neg">' . peso($net_revenue - $gross_profit) . '</span>
            </div>
        </div>';
    }

    // Net profit card — Gross Profit minus additional expenses/waste
    function render_net_profit_card(array $s): void {
        $gross_profit = (float)($s['gross_profit'] ?? 0);
        $waste_cost = (float)($s['total_waste_cost'] ?? 0);
        $net_profit = (float)($s['net_profit'] ?? ($gross_profit - $waste_cost));
        $cls = $net_profit >= 0 ? 'positive' : 'negative';
        echo '
        <div class="stat-card c-netprofit">
            <div class="s-icon" style="color:var(--success);"><i class="fas fa-chart-line"></i></div>
            <div class="s-label">Net Profit</div>
            <div class="s-value ' . $cls . '">' . peso($net_profit) . '</div>
            <div class="profit-breakdown">
                Gross Profit: <span class="pos">' . peso($gross_profit) . '</span>
                &nbsp;&minus;&nbsp; Waste: <span class="neg">' . peso($waste_cost) . '</span>
            </div>
        </div>';
    }

    function render_waste_card(array $s): void {
        $waste = (float)($s['total_waste_cost'] ?? 0);
        echo '
        <div class="stat-card c-danger">
            <div class="s-icon" style="color:var(--danger);"><i class="fas fa-trash-alt"></i></div>
            <div class="s-label">Total Waste Cost</div>
            <div class="s-value" style="color:var(--danger);">' . peso($waste) . '</div>
            <div class="s-sub">From voided items only</div>
        </div>';
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
            <div class="s-label">My Sales Today</div>
            <div class="s-value"><?= peso((float)($s['net_revenue']??0)) ?></div>
            <div class="vat-breakdown">
                Excl. VAT: <span><?= peso((float)($s['net_revenue_excl_vat']??0)) ?></span>
                &nbsp;|&nbsp; VAT (12%): <span><?= peso((float)($s['total_vat_collected']??0)) ?></span>
            </div>
        </div>
        <div class="stat-card c-coral">
            <div class="s-icon"><i class="fas fa-shopping-cart"></i></div>
            <div class="s-label">Orders Handled</div>
            <div class="s-value"><?= number_format($s['total_orders']??0) ?></div>
            <div class="s-sub">Cancelled: <?= $s['cancelled_orders']??0 ?></div>
        </div>
        <div class="stat-card c-teal">
            <div class="s-icon"><i class="fas fa-receipt"></i></div>
            <div class="s-label">Avg Order Value</div>
            <div class="s-value"><?= peso((float)($s['avg_order_value']??0)) ?></div>
            <div class="s-sub">Discounts: <?= peso((float)($s['total_discounts']??0)) ?></div>
        </div>
        <?php render_waste_card($s); ?>

        <?php render_net_profit_card($s); ?>
        <div class="stat-card c-warning">
            <div class="s-icon"><i class="fas fa-cash-register"></i></div>
            <div class="s-label">Cash to Turn Over</div>
            <div class="s-value"><?= peso((float)($s['net_revenue']??0)) ?></div>
            <div class="s-sub" style="color:var(--warning);font-weight:600;">Print close-out to confirm</div>
        </div>
    </div>

    <?php if (!empty($data['comparison'])): render_comparison($data['comparison'], 'Yesterday'); endif; ?>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="rpt-card fade-in">
                <div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-star"></i> Top Items Today</div></div>
                <div class="rpt-card-body p-0">
                    <?php render_top_items_table($data['top_items'] ?? [], false, false, true); ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="rpt-card fade-in">
                <div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-clock"></i> Orders by Hour</div></div>
                <div class="rpt-card-body">
                    <?php if(empty($data['peak_hours'])): ?>
                        <div class="no-data"><i class="fas fa-inbox"></i> No data.</div>
                    <?php else: ?>
                        <canvas id="myshiftPeak" style="max-height:240px;"></canvas>
                    <?php endif; ?>
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
        $co  = $data['closeout'] ?? [];
        $tz  = new DateTimeZone('Asia/Manila');
        $now = new DateTime('now', $tz);
    ?>
    <form method="GET" action="<?= $baseUrl ?>/index.php" class="filter-bar">
        <input type="hidden" name="page" value="reports">
        <input type="hidden" name="tab"  value="closeout">
        <label>Date</label>
        <input type="date" name="date" class="form-control filter-input-date" value="<?= $filter_date ?>" max="<?= $today ?>">
        <div class="filter-actions">
            <button type="submit" class="btn-apply"><i class="fas fa-search me-1"></i> Load</button>
            <button type="button" class="btn-print" onclick="printCloseout()"><i class="fas fa-print"></i> Print Receipt</button>
        </div>
    </form>
    <div class="closeout-wrap fade-in">
        <div class="receipt">
            <div class="receipt-header">
                <div class="shop-name">Dine with Din</div>
                <div class="shop-sub">Cafeteria POS System</div>
                <div class="doc-title">Shift Close-out Report</div>
            </div>
            <div class="receipt-section-title">Cashier Info</div>
            <div class="receipt-row"><span class="rl">Cashier</span><span class="rv"><?= htmlspecialchars($co['cashier_name']??'—') ?></span></div>
            <div class="receipt-row"><span class="rl">Username</span><span class="rv"><?= htmlspecialchars($co['username']??'—') ?></span></div>
            <div class="receipt-row"><span class="rl">Date</span><span class="rv"><?= date('F d, Y', strtotime($filter_date)) ?></span></div>
            <div class="receipt-row"><span class="rl">Printed at</span><span class="rv"><?= $now->format('h:i A') ?> (PHT)</span></div>
            <div class="receipt-section-title">Order Summary</div>
            <div class="receipt-row"><span class="rl">Orders handled</span><span class="rv"><?= number_format($co['total_orders']??0) ?></span></div>
            <div class="receipt-row"><span class="rl">Cancelled orders</span><span class="rv"><?= $co['cancelled_orders']??0 ?></span></div>
            <div class="receipt-row"><span class="rl">Payment transactions</span><span class="rv"><?= $co['payment_count']??0 ?></span></div>
            <?php if(($co['admin_processed_count']??0) > 0): ?>
            <div class="receipt-row">
                <span class="rl" style="color:var(--primary-light);">Admin-processed payments</span>
                <span class="rv" style="color:var(--primary-light);"><?= $co['admin_processed_count'] ?></span>
            </div>
            <?php endif; ?>
            <div class="receipt-section-title">Sales Breakdown</div>
            <div class="receipt-row"><span class="rl">Gross sales</span><span class="rv"><?= peso((float)($co['gross_sales']??0)) ?></span></div>
            <div class="receipt-row"><span class="rl">Discounts given</span><span class="rv">&ndash; <?= peso((float)($co['total_discounts']??0)) ?></span></div>
            <div class="receipt-row"><span class="rl">Net sales (VAT-incl.)</span><span class="rv"><?= peso((float)($co['net_sales']??0)) ?></span></div>
            <div class="receipt-row"><span class="rl" style="color:var(--warm-gray);font-size:.82rem;">Excl. VAT</span><span class="rv" style="color:var(--warm-gray);font-size:.82rem;"><?= peso((float)($co['net_sales_excl_vat']??0)) ?></span></div>
            <div class="receipt-row"><span class="rl" style="color:var(--warm-gray);font-size:.82rem;">VAT (12%)</span><span class="rv" style="color:var(--warm-gray);font-size:.82rem;"><?= peso((float)($co['vat_collected']??0)) ?></span></div>
            <div class="receipt-section-title">Cash Reconciliation</div>
            <div class="receipt-row"><span class="rl">Cash received</span><span class="rv"><?= peso((float)($co['cash_received']??0)) ?></span></div>
            <div class="receipt-row"><span class="rl">Change given</span><span class="rv">&ndash; <?= peso((float)($co['change_given']??0)) ?></span></div>
            <div class="receipt-total">
                <div class="tl">Cash to Turn Over</div>
                <div class="tv"><?= peso((float)($co['cash_to_turn_over']??0)) ?></div>
            </div>
            <?php if (($co['total_waste_cost'] ?? 0) > 0): ?>
            <div class="receipt-waste-box">
                <div class="tl"><i class="fas fa-trash-alt me-1"></i> Waste Written Off</div>
                <div class="tv"><?= peso((float)$co['total_waste_cost']) ?></div>
            </div>
            <?php endif; ?>
            <div class="receipt-sig">
                <div class="sig-block"><div class="sig-line"></div><div class="sig-label">Cashier Signature</div></div>
                <div class="sig-block"><div class="sig-line"></div><div class="sig-label">Received By</div></div>
            </div>
            <div class="receipt-footer">
                Thank you for your service today.<br>Dine with Din Cafeteria &copy; <?= date('Y') ?>
            </div>
        </div>
    </div>

    <?php
    // =========================================================================
    // INVENTORY (cashier)
    // =========================================================================
    elseif ($tab === 'inventory'):
    ?>
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <span style="font-size:.9rem;color:var(--warm-gray);">Items at or below their alert threshold.</span>
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
            <div class="no-data" style="padding:3rem;">
                <i class="fas fa-check-circle" style="font-size:2.5rem;color:var(--success);opacity:.7;display:block;margin-bottom:.8rem;"></i>
                All ingredients are sufficiently stocked.
            </div>
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
                    <td>
                        <?= number_format((float)$row['stock_quantity'],2) ?>
                        <div class="mini-bar"><div class="mini-bar-fill" style="width:<?=$pct?>%;background:<?=$bclr?>;"></div></div>
                    </td>
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
            <div class="s-label">Gross Revenue</div>
            <div class="s-value"><?= peso((float)($s['gross_revenue']??0)) ?></div>
            <div class="vat-breakdown">
                <div class="vat-label">Excl. VAT:</div>
                <div class="vat-amount"><?= peso((float)($s['net_revenue_excl_vat']??0)) ?></div>
                <div class="vat-label">VAT:</div>
                <div class="vat-amount"><?= peso((float)($s['total_vat_collected']??0)) ?></div>
            </div>
            <?php if (($s['total_refunds']??0) > 0): ?>
            <div class="refund-breakdown">
                <div class="refund-label">Less: Refunds</div>
                <div class="refund-amount"><?= peso((float)($s['total_refunds']??0)) ?></div>
            </div>
            <?php endif; ?>
            <?php if (($s['net_revenue_after_refunds']??0) != ($s['net_revenue']??0)): ?>
            <div class="net-revenue-breakdown">
                <div class="net-revenue-label">Net Revenue</div>
                <div class="net-revenue-amount"><?= peso((float)($s['net_revenue_after_refunds']??0)) ?></div>
            </div>
            <?php endif; ?>
        </div>
        <div class="stat-card c-coral">
            <div class="s-icon"><i class="fas fa-shopping-cart"></i></div>
            <div class="s-label">Total Orders</div>
            <div class="s-value"><?= number_format($s['total_orders']??0) ?></div>
            <div class="s-sub">Cancelled: <?=$s['cancelled_orders']??0?></div>
        </div>
        <div class="stat-card c-teal">
            <div class="s-icon"><i class="fas fa-receipt"></i></div>
            <div class="s-label">Avg Order Value</div>
            <div class="s-value"><?= peso((float)($s['avg_order_value']??0)) ?></div>
            <div class="s-sub">Discounts: <?= peso((float)($s['total_discounts']??0)) ?></div>
        </div>
        <div class="stat-card c-green">
            <div class="s-icon"><i class="fas fa-user-tie"></i></div>
            <div class="s-label">Active Cashiers</div>
            <div class="s-value"><?=$s['active_cashiers']??0?></div>
            <div class="s-sub"><?= date('M d, Y',strtotime($filter_date)) ?></div>
        </div>
        <?php render_waste_card($s); ?>

        <?php render_net_profit_card($s); ?>
    </div>

    <?php if (!empty($data['comparison'])): render_comparison($data['comparison'], 'Yesterday'); endif; ?>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="rpt-card fade-in">
                <div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-star"></i> Top Items</div></div>
                <div class="rpt-card-body p-0">
                    <?php render_top_items_table($data['top_items'] ?? [], true, true); ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="rpt-card fade-in">
                <div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-clock"></i> Orders by Hour</div></div>
                <div class="rpt-card-body">
                    <?php if(empty($data['peak_hours'])): ?>
                        <div class="no-data"><i class="fas fa-inbox"></i> No data.</div>
                    <?php else: ?>
                        <canvas id="dailyPeak" style="max-height:240px;"></canvas>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="rpt-card fade-in">
                <div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-chart-pie"></i> Order Types</div></div>
                <div class="rpt-card-body p-0">
                <?php if(empty($data['order_types'])): ?>
                    <div class="no-data p-4"><i class="fas fa-inbox"></i> No data.</div>
                <?php else: ?>
                    <div style="overflow-x:auto;"><table class="rpt-table">
                        <thead><tr><th>Type</th><th>Source</th><th>Orders</th><th>Revenue</th><th>Excl. VAT</th></tr></thead>
                        <tbody>
                        <?php foreach($data['order_types'] as $row): ?>
                        <tr>
                            <td><?=ucfirst(str_replace('_',' ',$row['order_type']))?></td>
                            <td><?=ucfirst(str_replace('_',' ',$row['order_source']))?></td>
                            <td><?=number_format($row['total_orders'])?></td>
                            <td><?=peso((float)$row['revenue'])?></td>
                            <td style="color:var(--warm-gray);font-size:.85rem;"><?=peso((float)($row['revenue_excl_vat']??0))?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table></div>
                <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="rpt-card fade-in">
                <div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-user-tie"></i> Cashier Breakdown</div></div>
                <div class="rpt-card-body p-0">
                <?php if(empty($data['cashiers'])): ?>
                    <div class="no-data p-4"><i class="fas fa-inbox"></i> No data.</div>
                <?php else: ?>
                    <div style="overflow-x:auto;"><table class="rpt-table">
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
                        </tbody>
                    </table></div>
                <?php endif; ?>
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
            <div class="s-label">Gross Revenue</div>
            <div class="s-value"><?=peso((float)($s['gross_revenue']??0))?></div>
            <div class="vat-breakdown">
                Excl. VAT: <span><?=peso((float)($s['net_revenue_excl_vat']??0))?></span>
                &nbsp;|&nbsp; VAT: <span><?=peso((float)($s['total_vat_collected']??0))?></span>
            </div>
            <?php if (($s['total_refunds']??0) > 0): ?>
            <div class="refund-breakdown">
                <div class="refund-label">Less: Refunds</div>
                <div class="refund-amount"><?=peso((float)($s['total_refunds']??0))?></div>
            </div>
            <?php endif; ?>
            <?php if (($s['net_revenue_after_refunds']??0) != ($s['net_revenue']??0)): ?>
            <div class="net-revenue-breakdown">
                <div class="net-revenue-label">Net Revenue</div>
                <div class="net-revenue-amount"><?=peso((float)($s['net_revenue_after_refunds']??0))?></div>
            </div>
            <?php endif; ?>
        </div>
        <div class="stat-card c-coral">
            <div class="s-icon"><i class="fas fa-shopping-cart"></i></div>
            <div class="s-label">Total Orders</div>
            <div class="s-value"><?=number_format($s['total_orders']??0)?></div>
            <div class="s-sub">Cancelled: <?=$s['cancelled_orders']??0?></div>
        </div>
        <div class="stat-card c-teal">
            <div class="s-icon"><i class="fas fa-receipt"></i></div>
            <div class="s-label">Avg Order Value</div>
            <div class="s-value"><?=peso((float)($s['avg_order_value']??0))?></div>
            <div class="s-sub">Discounts: <?=peso((float)($s['total_discounts']??0))?></div>
        </div>
        <div class="stat-card c-green">
            <div class="s-icon"><i class="fas fa-users"></i></div>
            <div class="s-label">Active Cashiers</div>
            <div class="s-value"><?=$s['active_cashiers']??0?></div>
            <div class="s-sub"><?=$months_list[$filter_month]?> <?=$filter_year?></div>
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
            <div class="rpt-card-body"><canvas id="monthlyTrend" style="max-height:260px;"></canvas></div></div>
        </div>
        <div class="col-lg-4">
            <div class="rpt-card fade-in"><div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-chart-pie"></i> By Category</div></div>
            <div class="rpt-card-body"><canvas id="monthlyCat" style="max-height:260px;"></canvas></div></div>
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
                <tfoot><tr>
                    <td>Monthly Total</td><td><?=number_format($total_orders_m)?></td>
                    <td><?=peso($total_rev_m)?></td>
                    <td style="color:var(--warm-gray);"><?=peso($total_excl_m)?></td>
                    <td style="color:var(--warm-gray);"><?=peso($total_vat_m)?></td>
                </tr></tfoot>
            </table></div><?php endif; ?>
            </div></div>
        </div>
        <div class="col-12">
            <div class="rpt-card fade-in"><div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-star"></i> Top Selling Items</div></div>
            <div class="rpt-card-body p-0">
                <?php render_top_items_table($data['top_items'] ?? [], true, true); ?>
            </div></div>
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
        const dd=<?=json_encode(array_column($data['daily_chart']??[],'sale_date'))?>;
        const dr=<?=json_encode(array_column($data['daily_chart']??[],'revenue'))?>;
        if(dd.length) new Chart(document.getElementById('monthlyTrend').getContext('2d'),{type:'line',data:{labels:dd.map(d=>{const p=d.split('-');return p[2]+' '+(p[1]=='01'?'Jan':(p[1]=='02'?'Feb':(p[1]=='03'?'Mar':(p[1]=='04'?'Apr':(p[1]=='05'?'May':(p[1]=='06'?'Jun':(p[1]=='07'?'Jul':(p[1]=='08'?'Aug':(p[1]=='09'?'Sep':(p[1]=='10'?'Oct':(p[1]=='11'?'Nov':'Dec')))))))))))),datasets:[{label:'Revenue',data:dr,backgroundColor:'rgba(15,76,92,.07)',borderColor:'#0f4c5c',borderWidth:2.5,tension:.35,fill:true,pointBackgroundColor:'#ff6b4a',pointRadius:4}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,grid:{color:'rgba(15,76,92,.04)'},ticks:{callback:v=>'₱'+v.toLocaleString()}},x:{grid:{display:false}}}}});
        const cl=<?=json_encode(array_column($data['categories']??[],'category_name'))?>;
        const cv=<?=json_encode(array_column($data['categories']??[],'revenue'))?>;
        if(cl.length) new Chart(document.getElementById('monthlyCat').getContext('2d'),{type:'doughnut',data:{labels:cl,datasets:[{data:cv,backgroundColor:['#0f4c5c','#ff6b4a','#fee440','#1e2f4e','#5e9b9e','#ff8c70'],borderWidth:0,hoverOffset:8}]},options:{responsive:true,maintainAspectRatio:false,cutout:'65%',plugins:{legend:{position:'bottom',labels:{boxWidth:12,padding:14}},tooltip:{callbacks:{label:ctx=>ctx.label+': ₱'+parseFloat(ctx.raw).toLocaleString()}}}}});
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
            <div class="s-label">Gross Revenue <?=$filter_year?></div>
            <div class="s-value"><?=peso((float)($s['gross_revenue']??0))?></div>
            <div class="vat-breakdown">
                Excl. VAT: <span><?=peso((float)($s['net_revenue_excl_vat']??0))?></span>
                &nbsp;|&nbsp; VAT: <span><?=peso((float)($s['total_vat_collected']??0))?></span>
            </div>
            <?php if (($s['total_refunds']??0) > 0): ?>
            <div class="refund-breakdown">
                <div class="refund-label">Less: Refunds</div>
                <div class="refund-amount"><?=peso((float)($s['total_refunds']??0))?></div>
            </div>
            <?php endif; ?>
            <?php if (($s['net_revenue_after_refunds']??0) != ($s['net_revenue']??0)): ?>
            <div class="net-revenue-breakdown">
                <div class="net-revenue-label">Net Revenue</div>
                <div class="net-revenue-amount"><?=peso((float)($s['net_revenue_after_refunds']??0))?></div>
            </div>
            <?php endif; ?>
        </div>
        <div class="stat-card c-coral"><div class="s-icon"><i class="fas fa-shopping-cart"></i></div><div class="s-label">Total Orders</div><div class="s-value"><?=number_format($s['total_orders']??0)?></div><div class="s-sub">Discounts: <?=peso((float)($s['total_discounts']??0))?></div></div>
        <div class="stat-card c-teal"><div class="s-icon"><i class="fas fa-receipt"></i></div><div class="s-label">Avg Order Value</div><div class="s-value"><?=peso((float)($s['avg_order_value']??0))?></div></div>
        <?php render_waste_card($s); ?>

        <?php render_net_profit_card($s); ?>
    </div>

    <?php if (!empty($data['comparison'])): render_comparison($data['comparison'], ($filter_year - 1) . ''); endif; ?>

    <div id="yearly-content">
    <div class="rpt-card fade-in">
        <div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-chart-bar"></i> Monthly Revenue <?=$filter_year?></div></div>
        <div class="rpt-card-body"><canvas id="yearlyBar" style="max-height:280px;"></canvas></div>
    </div>
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="rpt-card fade-in"><div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-table"></i> Month-by-Month Breakdown</div></div>
            <div class="rpt-card-body p-0">
                <table class="rpt-table">
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
                    <tfoot><tr><td>Yearly Total</td><td><?=peso($yearly_total)?></td><td style="color:var(--warm-gray);"><?=peso($yearly_excl)?></td></tr></tfoot>
                </table>
            </div></div>
        </div>
        <div class="col-lg-7">
            <div class="rpt-card fade-in"><div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-star"></i> Top Items <?=$filter_year?></div></div>
            <div class="rpt-card-body p-0">
                <?php render_top_items_table($data['top_items'] ?? [], true, true); ?>
            </div></div>
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
                <small style="color:var(--warm-gray);font-size:.78rem;"><span class="badge-variant" style="font-size:.7rem;">variants</span> = sold in multiple sizes/types</small>
            </div>
            <div class="rpt-card-body p-0">
                <?php render_top_items_table($data['top_items'] ?? [], true, true); ?>
            </div></div>
        </div>
        <div class="col-lg-4">
            <div class="rpt-card fade-in"><div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-chart-pie"></i> By Category</div></div>
            <div class="rpt-card-body"><canvas id="prodCat" style="max-height:240px;"></canvas></div></div>
            <div class="rpt-card fade-in" style="margin-top:1.5rem;"><div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-plus-circle"></i> Top Add-ons</div></div>
            <div class="rpt-card-body p-0">
            <?php if(empty($data['top_addons'])): ?><div class="no-data p-3"><i class="fas fa-inbox"></i> No add-on data.</div>
            <?php else: ?><table class="rpt-table"><thead><tr><th>Add-on</th><th>Qty</th><th>Revenue</th></tr></thead><tbody>
            <?php foreach($data['top_addons'] as $row): ?><tr><td><?=htmlspecialchars($row['addon_name'])?></td><td><?=number_format($row['times_ordered'])?></td><td><?=peso((float)$row['total_revenue'])?></td></tr><?php endforeach; ?>
            </tbody></table><?php endif; ?>
            </div></div>
        </div>
    </div>
    </div>
    <script>(function(){
        const cl=<?=json_encode(array_column($data['categories']??[],'category_name'))?>;
        const cv=<?=json_encode(array_column($data['categories']??[],'revenue'))?>;
        if(cl.length) new Chart(document.getElementById('prodCat').getContext('2d'),{type:'doughnut',data:{labels:cl,datasets:[{data:cv,backgroundColor:['#0f4c5c','#ff6b4a','#fee440','#1e2f4e','#5e9b9e','#ff8c70'],borderWidth:0,hoverOffset:8}]},options:{responsive:true,maintainAspectRatio:false,cutout:'65%',plugins:{legend:{position:'bottom',labels:{boxWidth:12,padding:14}},tooltip:{callbacks:{label:ctx=>ctx.label+': ₱'+parseFloat(ctx.raw).toLocaleString()}}}}});
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
            <?php else: ?><canvas id="staffPeak" style="max-height:240px;"></canvas><?php endif; ?>
            </div></div>
        </div>
        <div class="col-lg-5">
            <div class="rpt-card fade-in"><div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-chart-pie"></i> Order Types</div></div>
            <div class="rpt-card-body p-0">
            <?php if(empty($data['order_types'])): ?><div class="no-data p-4"><i class="fas fa-inbox"></i> No data.</div>
            <?php else: ?><table class="rpt-table"><thead><tr><th>Type</th><th>Source</th><th>Orders</th><th>Revenue</th></tr></thead><tbody>
            <?php foreach($data['order_types'] as $row): ?><tr><td><?=ucfirst(str_replace('_',' ',$row['order_type']))?></td><td><?=ucfirst(str_replace('_',' ',$row['order_source']))?></td><td><?=number_format($row['total_orders'])?></td><td><?=peso((float)$row['revenue'])?></td></tr><?php endforeach; ?>
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
    <div class="rpt-card fade-in">
        <div class="rpt-card-header">
            <div class="rpt-card-title"><i class="fas fa-cash-register"></i> Payment Reconciliation</div>
            <small style="color:var(--warm-gray);font-size:.78rem;">Variance = Net Cash &minus; Expected. Zero is ideal.</small>
        </div>
        <div class="rpt-card-body p-0">
        <?php if(empty($data['payments'])): ?><div class="no-data"><i class="fas fa-inbox"></i> No payment data in this range.</div>
        <?php else: ?><div style="overflow-x:auto;"><table class="rpt-table">
            <thead><tr>
                <th>Date</th><th>Cashier</th><th>Txns</th>
                <th>Cash In</th><th>Change Out</th><th>Net Cash</th>
                <th>Expected</th><th>Excl. VAT</th><th>VAT</th>
                <th style="min-width:100px;">Variance</th>
                <th>Admin Proc.</th>
            </tr></thead>
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
                    <small style="color:var(--warm-gray);font-size:.78rem;">
                        Completed orders &nbsp;|&nbsp;
                        <span style="color:var(--secondary);font-weight:600;">■</span> variant recipe &nbsp;
                        <span style="color:var(--warning);font-weight:600;">■</span> base recipe fallback
                    </small>
                </div>
                <div class="rpt-card-body p-0">
                <?php if(empty($data['consumption'])): ?>
                    <div class="no-data"><i class="fas fa-inbox"></i> No consumption data for this range.</div>
                <?php else:
                    $max_consumed = max(array_column($data['consumption'], 'theoretical_consumed'));
                ?>
                    <div style="overflow-x:auto;"><table class="rpt-table">
                        <thead><tr>
                            <th>Ingredient</th><th>Unit</th><th>Consumed (est.)</th><th>Recipe Source</th><th>Current Stock</th><th>Alert</th>
                        </tr></thead>
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
                            <td>
                                <?=number_format((float)$row['theoretical_consumed'],2)?>
                                <div class="consumption-bar"><div class="consumption-bar-fill" style="width:<?=$pct?>%;background:<?=$bar_color?>;"></div></div>
                            </td>
                            <td>
                                <?php if($via_variant > 0): ?><span class="badge-variant"><?=$via_variant?> via variant</span><?php endif; ?>
                                <?php if($has_fb): ?><span style="color:var(--warning);font-weight:600;font-size:.72rem;"><i class="fas fa-exclamation-triangle" style="font-size:.65rem;"></i> <?=$via_base?> base fallback</span><?php endif; ?>
                                <?php if($via_variant === 0 && !$has_fb): ?><span class="badge-base">base</span><?php endif; ?>
                            </td>
                            <td>
                                <?=number_format((float)$row['current_stock'],2)?>
                                <?php if($row['current_stock'] <= $row['low_stock_alert']): ?><span class="rpt-badge badge-danger" style="font-size:.65rem;margin-left:.3rem;">LOW</span><?php endif; ?>
                            </td>
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
                    <div class="no-data p-3"><i class="fas fa-check-circle" style="font-size:1.8rem;color:var(--success);opacity:.7;display:block;margin-bottom:.5rem;"></i>All ingredients sufficiently stocked.</div>
                <?php else: ?>
                    <table class="rpt-table"><thead><tr><th>Ingredient</th><th>Stock</th><th>Unit</th><th>Status</th></tr></thead><tbody>
                    <?php foreach($data['low_stock'] as $row): ?>
                    <tr><td><strong><?=htmlspecialchars($row['ingredient_name'])?></strong></td><td><?=number_format((float)$row['stock_quantity'],2)?></td><td><?=htmlspecialchars($row['unit']??'—')?></td><td><span class="rpt-badge <?=$row['stock_quantity']==0?'badge-danger':'badge-warning'?>"><?=$row['stock_status']?></span></td></tr>
                    <?php endforeach; ?>
                    </tbody></table>
                <?php endif; ?>
                </div>
            </div>
            <?php if(!empty($data['consumption'])): ?>
            <?php $total_fallback = array_sum(array_column($data['consumption'], 'units_via_base_recipe')); ?>
            <?php if($total_fallback > 0): ?>
            <div class="rpt-card fade-in" style="margin-top:1.5rem;">
                <div class="rpt-card-body" style="padding:1rem 1.2rem;">
                    <div style="display:flex;align-items:flex-start;gap:.7rem;">
                        <i class="fas fa-exclamation-triangle" style="color:var(--warning);margin-top:.15rem;flex-shrink:0;"></i>
                        <div>
                            <div style="font-weight:600;font-size:.88rem;color:var(--navy);margin-bottom:.3rem;">Recipe Coverage Warning</div>
                            <div style="font-size:.82rem;color:var(--warm-gray);">
                                <?= number_format($total_fallback) ?> unit(s) fell back to base recipe — no variant recipe configured.
                                <a href="<?= $baseUrl ?>/index.php?page=manage_recipes" style="color:var(--primary-light);font-weight:600;">Set up variant recipes →</a>
                            </div>
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

    <!-- Summary cards -->
    <div class="stats-grid fade-in">
        <div class="stat-card c-danger">
            <div class="s-icon" style="color:var(--danger);"><i class="fas fa-trash-alt"></i></div>
            <div class="s-label">Total Waste Cost</div>
            <div class="s-value" style="color:var(--danger);"><?= peso((float)($ws['total_waste_cost']??0)) ?></div>
            <div class="s-sub"><?= $scope_label ?> &nbsp;·&nbsp; <?= $range_start ?> → <?= $range_end ?></div>
        </div>
        <div class="stat-card c-warning">
            <div class="s-icon" style="color:var(--warning);"><i class="fas fa-box-open"></i></div>
            <div class="s-label">Voided Items</div>
            <div class="s-value"><?= number_format($ws['voided_items']??0) ?></div>
            <div class="s-sub">Across <?= number_format($ws['voided_orders']??0) ?> order(s)</div>
        </div>
        <div class="stat-card c-coral">
            <div class="s-icon" style="color:var(--secondary);"><i class="fas fa-calculator"></i></div>
            <div class="s-label">Avg Waste / Item</div>
            <div class="s-value"><?= peso((float)($ws['avg_waste_per_item']??0)) ?></div>
            <div class="s-sub">Cost per voided item</div>
        </div>
    </div>

    <div id="waste-content">
    <div class="row g-4">

        <!-- Top wasted items -->
        <div class="col-lg-5">
            <div class="rpt-card fade-in">
                <div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-fire-alt" style="color:var(--danger);"></i> Top Wasted Items</div></div>
                <div class="rpt-card-body p-0">
                <?php if(empty($ws['top_wasted_items'])): ?>
                    <div class="no-data"><i class="fas fa-check-circle" style="color:var(--success);"></i><br>No waste in this period.</div>
                <?php else:
                    $max_w = max(array_column($ws['top_wasted_items'],'total_waste_cost'));
                ?>
                    <table class="rpt-table">
                        <thead><tr><th>#</th><th>Item</th><th>Qty Wasted</th><th class="th-waste">Waste Cost</th></tr></thead>
                        <tbody>
                        <?php foreach($ws['top_wasted_items'] as $i=>$row):
                            $pct = $max_w > 0 ? round($row['total_waste_cost']/$max_w*100) : 0;
                        ?>
                        <tr>
                            <td><?=$i+1?></td>
                            <td>
                                <strong><?=htmlspecialchars($row['item_name'])?></strong>
                                <?php if(!empty($row['variant_name'])): ?><br><small style="color:var(--warm-gray);"><?=htmlspecialchars($row['variant_name'])?></small><?php endif; ?>
                            </td>
                            <td><?=number_format($row['total_qty_wasted'])?></td>
                            <td class="waste-cost-cell">
                                <?=peso((float)$row['total_waste_cost'])?>
                                <div class="mini-bar"><div class="mini-bar-fill" style="width:<?=$pct?>%;background:var(--danger);"></div></div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
                </div>
            </div>

            <!-- Waste by reason -->
            <div class="rpt-card fade-in" style="margin-top:1.5rem;">
                <div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-comment-alt" style="color:var(--danger);"></i> Waste by Reason</div></div>
                <div class="rpt-card-body p-0">
                <?php if(empty($ws['waste_by_reason'])): ?>
                    <div class="no-data p-3"><i class="fas fa-inbox"></i> No data.</div>
                <?php else: ?>
                    <table class="rpt-table">
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

        <!-- Full void log -->
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
                            <th>Date</th>
                            <th>Order #</th>
                            <?php if($is_admin): ?><th>Cashier</th><?php endif; ?>
                            <th>Item</th>
                            <th>Qty</th>
                            <th class="th-waste">Waste Cost</th>
                            <th>Reason</th>
                        </tr></thead>
                        <tbody>
                        <?php foreach($wi as $row): ?>
                        <tr>
                            <td style="white-space:nowrap;"><?=date('M d, Y',strtotime($row['void_date']))?></td>
                            <td><strong><?=htmlspecialchars($row['order_number'])?></strong></td>
                            <?php if($is_admin): ?><td><?=htmlspecialchars($row['cashier_name'])?></td><?php endif; ?>
                            <td>
                                <?=htmlspecialchars($row['item_name'])?>
                                <?php if(!empty($row['variant_name'])): ?>
                                    <span class="badge-variant"><?=htmlspecialchars($row['variant_name'])?></span>
                                <?php endif; ?>
                            </td>
                            <td><?=$row['quantity']?></td>
                            <td class="waste-cost-cell"><?=peso((float)$row['waste_cost'])?></td>
                            <td>
                                <?php if(!empty($row['void_reason'])): ?>
                                    <span class="reason-pill" title="<?=htmlspecialchars($row['void_reason'])?>"><?=htmlspecialchars($row['void_reason'])?></span>
                                <?php else: ?>
                                    <span style="color:var(--warm-gray);font-size:.82rem;">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="<?= $is_admin ? 5 : 4 ?>">Total</td>
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
    </div><!-- /waste-content -->

    <?php endif; // end tab switch ?>

</div><!-- /main-content -->

<footer class="site-footer">
    <div class="site-footer-inner">
        <div><strong>Copyright &copy; <?= date('Y') ?> DinewithDin - Cafeteria.</strong> All rights reserved.</div>
        <div><b>Version</b> 1.0.0</div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* ── Sidebar ── */
document.getElementById('menuToggle')?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('show');
    document.getElementById('sidebarOverlay').classList.toggle('show');
});
document.getElementById('sidebarOverlay')?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.remove('show');
    document.getElementById('sidebarOverlay').classList.remove('show');
});

/* ── Date range guard ── */
(function(){
    const s = document.getElementById('rpt-start');
    const e = document.getElementById('rpt-end');
    if (!s || !e) return;
    function enforce() { if (!s.value) return; e.min = s.value; if (e.value && e.value < s.value) e.value = s.value; }
    s.addEventListener('change', enforce);
    enforce();
})();

/* ── openPrintTab ── */
function openPrintTab(html) {
    var blob = new Blob([html], { type: 'text/html;charset=utf-8' });
    var url  = URL.createObjectURL(blob);
    var tab  = window.open(url, '_blank');
    if (!tab) { alert('Please allow pop-ups for this site to enable printing.'); URL.revokeObjectURL(url); return; }
    tab.addEventListener('load', function () { URL.revokeObjectURL(url); });
}

/* ── printCloseout ── */
function printCloseout() {
    var tableRows = '';
    var allNodes  = document.querySelectorAll('.closeout-wrap .receipt-row, .closeout-wrap .receipt-section-title');
    allNodes.forEach(function(node) {
        if (node.classList.contains('receipt-section-title')) {
            tableRows += '<tr><td colspan="2" class="sec-title">' + node.textContent.trim() + '</td></tr>';
        } else {
            var lbl = node.querySelector('.rl');
            var rv  = node.querySelector('.rv');
            if (!lbl || !rv) return;
            tableRows += '<tr><td class="lbl">' + lbl.innerText.trim() + '</td><td class="val">' + rv.innerText.trim() + '</td></tr>';
        }
    });
    var turnover = (document.querySelector('.closeout-wrap .receipt-total .tv') || {innerText:''}).innerText.trim();
    var wasteBox = document.querySelector('.closeout-wrap .receipt-waste-box');
    var wasteRow = wasteBox ? '<div class="waste-row"><span>Waste Written Off</span><span style="color:#d64045;font-weight:bold;">' + (wasteBox.querySelector('.tv')||{innerText:''}).innerText.trim() + '</span></div>' : '';
    var footer   = (document.querySelector('.closeout-wrap .receipt-footer') || {innerText:''}).innerText.trim();
    if (!tableRows) { alert('No close-out data to print.'); return; }
    var html =
        '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Close-out</title>' +
        '<style>' +
        'html,body{margin:0;padding:0;background:#fff;}' +
        '@page{size:80mm auto;margin:5mm 4mm;}' +
        'body{font-family:"Courier New",Courier,monospace;font-size:11px;line-height:1.55;color:#000;width:72mm;}' +
        '.c{text-align:center;}.shop{font-size:14px;font-weight:bold;letter-spacing:.5px;}' +
        '.sub{font-size:9px;color:#555;margin-top:1px;}.title{font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:1px;margin:5px 0 4px;}' +
        'hr{border:none;border-top:1px dashed #000;margin:5px 0;}' +
        'table{width:100%;border-collapse:collapse;}td{padding:2px 0;font-size:11px;vertical-align:top;}' +
        'td.lbl{color:#333;width:58%;}td.val{text-align:right;font-weight:bold;color:#000;width:42%;}' +
        'td.sec-title{font-size:9px;font-weight:bold;text-transform:uppercase;letter-spacing:.5px;padding:5px 0 1px;border-top:1px dashed #ccc;color:#444;}' +
        '.total{border:2px solid #000;padding:5px 7px;margin:7px 0;display:flex;justify-content:space-between;align-items:center;}' +
        '.tl{font-size:9px;font-weight:bold;text-transform:uppercase;letter-spacing:.5px;}.tv{font-size:16px;font-weight:bold;}' +
        '.waste-row{display:flex;justify-content:space-between;padding:4px 7px;border:1px dashed #d64045;margin:4px 0;font-size:10px;}' +
        '.sigs{display:grid;grid-template-columns:1fr 1fr;gap:8mm;margin-top:10px;padding-top:6px;border-top:1px dashed #000;}' +
        '.sl{border-bottom:1px solid #000;height:26px;margin-bottom:3px;}.slbl{font-size:8px;text-align:center;color:#444;}' +
        '.footer{text-align:center;font-size:8px;color:#555;margin-top:7px;padding-top:5px;border-top:1px dashed #000;}' +
        '</style></head><body>' +
        '<div class="c"><div class="shop">Dine with Din</div><div class="sub">Cafeteria POS System</div><div class="title">Shift Close-out</div></div>' +
        '<hr><table>' + tableRows + '</table><hr>' +
        '<div class="total"><div class="tl">Cash to Turn Over</div><div class="tv">' + turnover + '</div></div>' +
        wasteRow +
        '<div class="sigs"><div><div class="sl"></div><div class="slbl">Cashier Signature</div></div><div><div class="sl"></div><div class="slbl">Received By</div></div></div>' +
        '<div class="footer">' + footer + '</div></body></html>';
    html = html.replace('</body></html>',
        '<script>window.addEventListener(\'load\',function(){setTimeout(function(){window.print();},400);});window.addEventListener(\'afterprint\',function(){window.close();});<\/script></body></html>'
    );
    openPrintTab(html);
}

/* ── printDaily ── */
var _dailyData = <?php
    if ($tab === 'daily'):
        $s         = $data['summary']  ?? [];
        $cashiers  = $data['cashiers'] ?? [];
        $top_items = $data['top_items']?? [];
        echo json_encode([
            'date'              => date('F d, Y', strtotime($filter_date)),
            'printed_at'        => (new DateTime('now', new DateTimeZone('Asia/Manila')))->format('M d, Y h:i A') . ' (PHT)',
            'gross_revenue'     => number_format((float)($s['net_revenue']??0), 2),
            'excl_vat'          => number_format((float)($s['net_revenue_excl_vat']??0), 2),
            'vat_collected'     => number_format((float)($s['total_vat_collected']??0), 2),
            'total_orders'      => number_format($s['total_orders']??0),
            'cancelled'         => $s['cancelled_orders']??0,
            'avg_order'         => number_format((float)($s['avg_order_value']??0), 2),
            'discounts'         => number_format((float)($s['total_discounts']??0), 2),
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
                'name'         => $r['item_name'] . (($r['has_variants']??0) ? ' [variants]' : ''),
                'sold'         => $r['units_sold'],
                'revenue'      => number_format((float)$r['total_revenue'], 2),
                'profit'       => number_format((float)$r['gross_profit'], 2),
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
        row('Report date',           d.date) +
        row('Printed at',            d.printed_at) +
        sep() +
        row('Gross revenue (VAT-incl.)', '₱' + d.gross_revenue, true) +
        row('Excl. VAT',             '₱' + d.excl_vat) +
        row('VAT collected (12%)',   '₱' + d.vat_collected) +
        row('Discounts given',       '- ₱' + d.discounts) +
        sep() +
        row('Gross profit',          '₱' + d.gross_profit) +
        row('Waste written off',     '- ₱' + d.waste_cost) +
        row('Net profit',            '₱' + d.net_profit, true) +
        sep() +
        row('Total orders',          d.total_orders) +
        row('Cancelled orders',      d.cancelled) +
        row('Avg order value',       '₱' + d.avg_order) +
        row('Active cashiers',       d.active_cashiers);

    var itemSection = '';
    if (d.top_items && d.top_items.length) {
        var itemRows = '';
        d.top_items.forEach(function(item) {
            itemRows += '<tr><td class="nm">' + esc(item.name) + '</td><td class="r">' + item.sold + '</td><td class="r">₱' + item.revenue + '</td><td class="r">₱' + item.profit + '</td></tr>';
        });
        itemSection = sep() + '<div class="sh">Top Items</div>' +
            '<table><thead><tr><th>Item</th><th class="r">Sold</th><th class="r">Revenue</th><th class="r">Gross Profit</th></tr></thead><tbody>' + itemRows + '</tbody></table>';
    }
    var cashSection = '';
    if (d.cashiers && d.cashiers.length) {
        var cashRows = '';
        d.cashiers.forEach(function(c) {
            cashRows += '<tr><td class="nm">' + esc(c.name) + '</td><td class="r">' + c.orders + '</td><td class="r">₱' + c.sales + '</td><td class="r" style="color:#d64045;">₱' + c.waste + '</td><td class="r">' + c.cancelled + '</td></tr>';
        });
        cashSection = sep() + '<div class="sh">Cashier Breakdown</div>' +
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
        'table{width:100%;border-collapse:collapse;font-size:10px;margin-top:2px;}' +
        'thead th{font-size:9px;font-weight:bold;text-transform:uppercase;letter-spacing:.3px;border-bottom:1px solid #000;padding:2px 1px;}thead th.r{text-align:right;}' +
        'tbody td{padding:2.5px 1px;border-bottom:1px dotted #ccc;vertical-align:top;}tbody td.r{text-align:right;}tbody td.nm{max-width:34mm;word-break:break-word;}' +
        '.footer{text-align:center;font-size:8px;color:#555;margin-top:8px;padding-top:5px;border-top:1px dashed #000;}' +
        '</style></head><body>' +
        '<div class="c"><div class="shop">Dine with Din</div><div class="sub">Cafeteria POS System</div><div class="title">Daily Report</div></div>' +
        '<hr class="sep">' + sumRows + itemSection + cashSection +
        '<div class="footer">Dine with Din Cafeteria &mdash; ' + d.printed_at + '</div></body></html>';
    html = html.replace('</body></html>',
        '<script>window.addEventListener(\'load\',function(){setTimeout(function(){window.print();},400);});window.addEventListener(\'afterprint\',function(){window.close();});<\/script></body></html>'
    );
    openPrintTab(html);
}

function row(label, value, highlight) {
    return '<div class="row"><span class="lb">' + label + '</span><span class="vl' + (highlight ? ' hi' : '') + '">' + value + '</span></div>';
}
function sep() { return '<hr class="sep">'; }
function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

/* ── printA4 ── */
function printA4(contentId, title) {
    var el = document.getElementById(contentId);
    if (!el) { alert('Nothing to print.'); return; }
    var html =
        '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' + title + '</title>' +
        '<style>' +
        '@page{size:A4 portrait;margin:12mm 15mm;}*{box-sizing:border-box;margin:0;padding:0;}' +
        'body{font-family:Arial,Helvetica,sans-serif;font-size:11px;color:#000;background:#fff;}' +
        '.print-title{font-size:16px;font-weight:bold;text-align:center;margin-bottom:2px;}' +
        '.print-sub{font-size:10px;text-align:center;color:#555;margin-bottom:10px;padding-bottom:8px;border-bottom:2px solid #000;}' +
        '.rpt-card{margin-bottom:12px;page-break-inside:avoid;}' +
        '.rpt-card-header{font-size:12px;font-weight:bold;padding:6px 0;border-bottom:1px solid #ccc;margin-bottom:6px;}' +
        '.rpt-card-title i{display:none;}' +
        'table{width:100%;border-collapse:collapse;font-size:10px;margin-bottom:8px;}' +
        'thead th{background:#f0f0f0;border:1px solid #ccc;padding:5px 6px;text-align:left;font-size:9px;text-transform:uppercase;letter-spacing:.5px;}' +
        'tbody td{border:1px solid #ddd;padding:5px 6px;vertical-align:top;}' +
        'tfoot td{border:1px solid #ccc;background:#f0f0f0;padding:5px 6px;font-weight:bold;}' +
        'tbody tr:nth-child(even) td{background:#fafafa;}' +
        '.mini-bar,.consumption-bar{display:none;}' +
        '.rpt-badge,.badge-variant,.badge-base{border:1px solid #999;padding:1px 4px;border-radius:2px;font-size:9px;}' +
        '.no-data{text-align:center;padding:12px;color:#888;font-style:italic;}' +
        'canvas{display:none!important;}' +
        '.comparison-grid,.stats-grid{display:none;}' +
        '.variance-positive{color:green;font-weight:bold;}.variance-negative{color:red;font-weight:bold;}.variance-zero{color:#555;}' +
        '.admin-proc-badge{font-size:9px;border:1px solid #ccc;padding:1px 4px;}' +
        '.vat-breakdown,.profit-breakdown{font-size:9px;color:#555;margin-top:2px;}' +
        '.waste-cost-cell{color:#d64045;font-weight:bold;}' +
        '.reason-pill{font-size:9px;border:1px solid #d64045;padding:1px 4px;border-radius:2px;color:#d64045;}' +
        '.receipt-waste-box{border:1px dashed #d64045;padding:4px 8px;margin:4px 0;display:flex;justify-content:space-between;font-size:10px;color:#d64045;}' +
        '</style></head><body>' +
        '<div class="print-title">Dine with Din Cafeteria</div>' +
        '<div class="print-sub">' + title + ' &nbsp;|&nbsp; Printed: <?= (new DateTime('now', new DateTimeZone('Asia/Manila')))->format('M d, Y h:i A') ?> (PHT)</div>' +
        el.innerHTML + '</body></html>';
    html = html.replace('</body></html>',
        '<script>window.addEventListener(\'load\',function(){setTimeout(function(){window.print();},400);});window.addEventListener(\'afterprint\',function(){window.close();});<\/script></body></html>'
    );
    openPrintTab(html);
}
</script>
</body>
</html>
