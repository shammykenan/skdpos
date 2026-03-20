<?php
/**
 * reports_view.php  (v3 – RBAC + proper print per report type)
 * Close-out   → 80mm thermal popup print
 * Daily       → 80mm thermal popup print
 * Monthly     → A4 popup print  (table layout)
 * Yearly      → A4 popup print  (table layout)
 * Others      → A4 popup print
 */
extract($ctx);

$months_list = [
    1=>'January',2=>'February',3=>'March',4=>'April',
    5=>'May',6=>'June',7=>'July',8=>'August',
    9=>'September',10=>'October',11=>'November',12=>'December'
];

function peso(float $v): string { return '&#8369;' . number_format($v, 2); }

$tab_meta = $is_admin ? [
    'daily'    => ['fas fa-calendar-day',  'Daily'],
    'monthly'  => ['fas fa-calendar-alt',  'Monthly'],
    'yearly'   => ['fas fa-calendar',      'Yearly'],
    'products' => ['fas fa-utensils',      'Products'],
    'staff'    => ['fas fa-users',         'Staff'],
    'audit'    => ['fas fa-shield-alt',    'Audit'],
] : [
    'myshift'   => ['fas fa-user-clock',   'My Shift'],
    'closeout'  => ['fas fa-print',        'Close-out'],
    'inventory' => ['fas fa-boxes',        'Stock Alerts'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports &ndash; Cafeteria POS</title>
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
        .role-badge{padding:.3rem .9rem;border-radius:30px;font-size:.75rem;font-weight:700;letter-spacing:.4px;text-transform:uppercase;}
        .role-admin{background:rgba(15,76,92,.1);color:var(--primary-dark);}
        .role-cashier{background:rgba(255,107,74,.12);color:#c0421e;}
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
        .s-icon{width:48px;height:48px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;margin-bottom:1rem;background:linear-gradient(145deg,#f0f5fa,white);color:var(--primary);border:1px solid rgba(15,76,92,.1);}
        .s-label{font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--warm-gray);margin-bottom:.4rem;}
        .s-value{font-size:1.9rem;font-weight:700;color:var(--navy);line-height:1.1;}
        .s-sub{font-size:.82rem;color:var(--warm-gray);margin-top:.4rem;}
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
        .rpt-badge{padding:.35rem .9rem;border-radius:30px;font-size:.75rem;font-weight:600;display:inline-block;}
        .badge-primary{background:var(--primary);color:white;}
        .badge-warning{background:#fff3cd;color:#856404;}
        .badge-danger{background:#f8d7da;color:#721c24;}
        .badge-success{background:#d4edda;color:#155724;}
        .mini-bar{height:5px;border-radius:6px;background:#e2e8f0;margin-top:5px;}
        .mini-bar-fill{height:100%;border-radius:6px;background:var(--primary);}
        .btn-apply{padding:.55rem 1.4rem;border-radius:40px;font-size:.85rem;font-weight:600;background:var(--primary);color:white;border:none;cursor:pointer;transition:all .18s;}
        .btn-apply:hover{background:var(--primary-dark);transform:translateY(-1px);}
        .btn-print{padding:.55rem 1.3rem;border-radius:40px;font-size:.85rem;font-weight:600;border:1.5px solid var(--primary);color:var(--primary);background:transparent;cursor:pointer;transition:all .18s;display:inline-flex;align-items:center;gap:.4rem;text-decoration:none;}
        .btn-print:hover{background:var(--primary);color:white;}
        .no-data{text-align:center;padding:3rem 1rem;color:var(--warm-gray);}
        .no-data i{font-size:2.5rem;margin-bottom:.8rem;opacity:.3;display:block;}
        /* Close-out receipt card on screen */
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
        .receipt-total{background:var(--primary);color:white;border-radius:14px;padding:1.1rem 1.4rem;margin:1.2rem 0;display:flex;justify-content:space-between;align-items:center;}
        .receipt-total .tl{font-size:.85rem;font-weight:600;opacity:.85;text-transform:uppercase;letter-spacing:.5px;}
        .receipt-total .tv{font-size:1.8rem;font-weight:700;}
        .receipt-sig{display:grid;grid-template-columns:1fr 1fr;gap:2rem;margin-top:1.8rem;padding-top:1.2rem;border-top:2px dashed var(--border);}
        .sig-block .sig-line{border-bottom:1.5px solid var(--dark);height:40px;margin-bottom:.4rem;}
        .sig-block .sig-label{font-size:.78rem;color:var(--warm-gray);text-align:center;}
        .receipt-footer{text-align:center;margin-top:1.2rem;font-size:.8rem;color:var(--warm-gray);}
        @media(max-width:991.98px){.sidebar{transform:translateX(-100%);}.sidebar.show{transform:translateX(0);}.main-content{margin-left:0;padding:1.5rem;}}
        @media(max-width:767.98px){.main-content{padding:1rem;}.stats-grid{grid-template-columns:1fr 1fr;}.report-tabs{gap:.3rem;}.report-tab{padding:.45rem .9rem;font-size:.8rem;}}
        @media(max-width:480px){.stats-grid{grid-template-columns:1fr;}.topbar .logo span{display:none;}.topbar{padding:0 1rem;}}
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
    <div style="display:flex;align-items:center;gap:.8rem;">
        <span class="role-badge role-<?= $role ?>">
            <i class="fas fa-<?= $is_admin ? 'crown' : 'cash-register' ?> me-1"></i><?= ucfirst($role) ?>
        </span>
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

<!-- SIDEBAR -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<div class="sidebar" id="sidebar">
    <div class="sidebar-nav">
        <div class="nav-section-title">DASHBOARD</div>
        <?php if ($is_admin): ?>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></div>
        <div class="nav-section-title">MANAGEMENT</div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_menu" class="nav-link"><i class="fas fa-utensils"></i> Menu Items</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_recipes" class="nav-link"><i class="fas fa-tags"></i> Recipes</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_ingredients" class="nav-link"><i class="fas fa-boxes"></i> Ingredients</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_addons" class="nav-link"><i class="fas fa-plus-circle"></i> Add-ons</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_users" class="nav-link"><i class="fas fa-users"></i> User Management</a></div>
        <div class="nav-section-title">SALES</div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_pos" class="nav-link"><i class="fas fa-cash-register"></i> POS System</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_orders" class="nav-link"><i class="fas fa-receipt"></i> Orders</a></div>
        <?php else: ?>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_pos" class="nav-link"><i class="fas fa-cash-register"></i> POS System</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_orders" class="nav-link"><i class="fas fa-receipt"></i> Orders</a></div>
        <?php endif; ?>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=reports" class="nav-link active"><i class="fas fa-chart-bar"></i> Reports</a></div>
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

    <!-- Tabs -->
    <div class="report-tabs">
        <?php foreach ($tab_meta as $key => [$icon, $label]): ?>
        <a href="<?= $baseUrl ?>/index.php?page=reports&tab=<?= $key ?>"
           class="report-tab <?= $tab===$key?'active':'' ?> <?= $key==='closeout'?'closeout-tab':(!$is_admin?'cashier-tab':'') ?>">
            <i class="<?= $icon ?>"></i> <?= $label ?>
        </a>
        <?php endforeach; ?>
    </div>

    <?php
    // =====================================================================
    // MY SHIFT (cashier)
    // =====================================================================
    if ($tab === 'myshift'):
        $s = $data['summary'] ?? [];
    ?>
    <form method="GET" action="<?= $baseUrl ?>/index.php" class="filter-bar">
        <input type="hidden" name="page" value="reports">
        <input type="hidden" name="tab"  value="myshift">
        <label>Date</label>
        <input type="date" name="date" class="form-control" style="width:170px;" value="<?= $filter_date ?>" max="<?= $today ?>">
        <button type="submit" class="btn-apply"><i class="fas fa-search me-1"></i> View</button>
        <a href="<?= $baseUrl ?>/index.php?page=reports&tab=closeout&date=<?= $filter_date ?>" class="btn-print"><i class="fas fa-print"></i> Print Close-out</a>
    </form>
    <div class="stats-grid fade-in">
        <div class="stat-card"><div class="s-icon"><i class="fas fa-money-bill-wave"></i></div><div class="s-label">My Sales Today</div><div class="s-value"><?= peso((float)($s['gross_revenue']??0)) ?></div><div class="s-sub">Net: <?= peso((float)($s['net_revenue']??0)) ?></div></div>
        <div class="stat-card c-coral"><div class="s-icon"><i class="fas fa-shopping-cart"></i></div><div class="s-label">Orders Handled</div><div class="s-value"><?= number_format($s['total_orders']??0) ?></div><div class="s-sub">Cancelled: <?= $s['cancelled_orders']??0 ?></div></div>
        <div class="stat-card c-teal"><div class="s-icon"><i class="fas fa-receipt"></i></div><div class="s-label">Avg Order Value</div><div class="s-value"><?= peso((float)($s['avg_order_value']??0)) ?></div><div class="s-sub">Discounts: <?= peso((float)($s['total_discounts']??0)) ?></div></div>
        <div class="stat-card c-warning"><div class="s-icon"><i class="fas fa-cash-register"></i></div><div class="s-label">Cash to Turn Over</div><div class="s-value"><?= peso((float)($s['net_revenue']??0)) ?></div><div class="s-sub" style="color:var(--warning);font-weight:600;">Print close-out to confirm</div></div>
    </div>
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="rpt-card fade-in">
                <div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-star"></i> Top Items Today</div></div>
                <div class="rpt-card-body p-0">
                <?php if(empty($data['top_items'])): ?><div class="no-data"><i class="fas fa-inbox"></i> No sales yet for this date.</div>
                <?php else: ?><div style="overflow-x:auto;"><table class="rpt-table"><thead><tr><th>#</th><th>Item</th><th>Sold</th><th>Revenue</th></tr></thead><tbody>
                <?php foreach($data['top_items'] as $i=>$row): ?><tr><td><?=$i+1?></td><td><strong><?=htmlspecialchars($row['item_name'])?></strong></td><td><?=number_format($row['units_sold'])?></td><td><?=peso((float)$row['total_revenue'])?></td></tr><?php endforeach; ?>
                </tbody></table></div><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="rpt-card fade-in">
                <div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-clock"></i> Orders by Hour</div></div>
                <div class="rpt-card-body">
                <?php if(empty($data['peak_hours'])): ?><div class="no-data"><i class="fas fa-inbox"></i> No data.</div>
                <?php else: ?><canvas id="myshiftPeak" style="max-height:240px;"></canvas><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php if(!empty($data['peak_hours'])): ?>
    <script>(function(){const h=<?=json_encode(array_column($data['peak_hours'],'hour_of_day'))?>;const o=<?=json_encode(array_column($data['peak_hours'],'total_orders'))?>;new Chart(document.getElementById('myshiftPeak').getContext('2d'),{type:'bar',data:{labels:h.map(v=>(v%12||12)+' '+(v>=12?'PM':'AM')),datasets:[{label:'Orders',data:o,backgroundColor:'rgba(255,107,74,.7)',borderRadius:6,borderSkipped:false}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,grid:{color:'rgba(15,76,92,.04)'}},x:{grid:{display:false}}}}});})();</script>
    <?php endif; ?>

    <?php
    // =====================================================================
    // CLOSE-OUT (cashier) — 80mm thermal popup
    // =====================================================================
    elseif ($tab === 'closeout'):
        $co = $data['closeout'] ?? [];
        $tz  = new DateTimeZone('Asia/Manila');
        $now = new DateTime('now', $tz);
    ?>
    <form method="GET" action="<?= $baseUrl ?>/index.php" class="filter-bar">
        <input type="hidden" name="page" value="reports">
        <input type="hidden" name="tab"  value="closeout">
        <label>Date</label>
        <input type="date" name="date" class="form-control" style="width:170px;" value="<?= $filter_date ?>" max="<?= $today ?>">
        <button type="submit" class="btn-apply"><i class="fas fa-search me-1"></i> Load</button>
        <button type="button" class="btn-print" onclick="printCloseout()"><i class="fas fa-print"></i> Print Receipt</button>
    </form>
    <div class="closeout-wrap fade-in">
        <div class="receipt">
            <div class="receipt-header">
                <div class="shop-name">Dine with Din</div>
                <div class="shop-sub">Cafeteria POS System</div>
                <div class="doc-title">Shift Close-out Report</div>
            </div>
            <div class="receipt-row"><span class="rl">Cashier</span><span class="rv" id="co-cashier"><?= htmlspecialchars($co['cashier_name']??'&mdash;') ?></span></div>
            <div class="receipt-row"><span class="rl">Username</span><span class="rv" id="co-username"><?= htmlspecialchars($co['username']??'&mdash;') ?></span></div>
            <div class="receipt-row"><span class="rl">Date</span><span class="rv" id="co-date"><?= date('F d, Y', strtotime($filter_date)) ?></span></div>
            <div class="receipt-row"><span class="rl">Printed at</span><span class="rv" id="co-time"><?= $now->format('h:i A') ?> (PHT)</span></div>
            <div style="border-top:2px dashed var(--border);margin:1rem 0;"></div>
            <div class="receipt-row"><span class="rl">Orders handled</span><span class="rv" id="co-orders"><?= number_format($co['total_orders']??0) ?></span></div>
            <div class="receipt-row"><span class="rl">Cancelled orders</span><span class="rv" id="co-cancelled"><?= $co['cancelled_orders']??0 ?></span></div>
            <div class="receipt-row"><span class="rl">Payment transactions</span><span class="rv" id="co-txns"><?= $co['payment_count']??0 ?></span></div>
            <div style="border-top:2px dashed var(--border);margin:1rem 0;"></div>
            <div class="receipt-row"><span class="rl">Gross sales</span><span class="rv" id="co-gross"><?= peso((float)($co['gross_sales']??0)) ?></span></div>
            <div class="receipt-row"><span class="rl">Discounts given</span><span class="rv" id="co-disc">&ndash; <?= peso((float)($co['total_discounts']??0)) ?></span></div>
            <div class="receipt-row"><span class="rl">Net sales</span><span class="rv" id="co-net"><?= peso((float)($co['net_sales']??0)) ?></span></div>
            <div class="receipt-row"><span class="rl">Cash received</span><span class="rv" id="co-cash"><?= peso((float)($co['cash_received']??0)) ?></span></div>
            <div class="receipt-row"><span class="rl">Change given</span><span class="rv" id="co-change">&ndash; <?= peso((float)($co['change_given']??0)) ?></span></div>
            <div class="receipt-total"><div class="tl">Cash to Turn Over</div><div class="tv" id="co-turnover"><?= peso((float)($co['cash_to_turn_over']??0)) ?></div></div>
            <div class="receipt-sig">
                <div class="sig-block"><div class="sig-line"></div><div class="sig-label">Cashier Signature</div></div>
                <div class="sig-block"><div class="sig-line"></div><div class="sig-label">Received By</div></div>
            </div>
            <div class="receipt-footer" id="co-footer">Thank you for your service today.<br>Dine with Din Cafeteria &copy; <?= date('Y') ?></div>
        </div>
    </div>

    <?php
    // =====================================================================
    // INVENTORY (both roles)
    // =====================================================================
    elseif ($tab === 'inventory'):
    ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
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
            <div class="no-data" style="padding:3rem;"><i class="fas fa-check-circle" style="font-size:2.5rem;color:var(--success);opacity:.7;display:block;margin-bottom:.8rem;"></i>All ingredients are sufficiently stocked.</div>
        <?php else: ?>
            <div style="overflow-x:auto;"><table class="rpt-table">
                <thead><tr><th>Ingredient</th><th>Current Stock</th><th>Alert Level</th><th>Unit</th><th>Status</th><th>Last Updated</th></tr></thead>
                <tbody>
                <?php foreach($data['low_stock'] as $row):
                    $pct=$row['low_stock_alert']>0?min(100,round($row['stock_quantity']/$row['low_stock_alert']*100)):0;
                    $bar_color=$row['stock_quantity']==0?'var(--danger)':($pct<30?'var(--warning)':'var(--primary)');
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($row['ingredient_name']) ?></strong></td>
                    <td><?= number_format((float)$row['stock_quantity'],2) ?><div class="mini-bar"><div class="mini-bar-fill" style="width:<?=$pct?>%;background:<?=$bar_color?>;"></div></div></td>
                    <td><?= number_format((float)$row['low_stock_alert'],2) ?></td>
                    <td><?= htmlspecialchars($row['unit']) ?></td>
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
    // =====================================================================
    // DAILY (admin) — 80mm thermal popup
    // =====================================================================
    elseif ($tab === 'daily'):
        $s = $data['summary'] ?? [];
    ?>
    <form method="GET" action="<?= $baseUrl ?>/index.php" class="filter-bar">
        <input type="hidden" name="page" value="reports">
        <input type="hidden" name="tab"  value="daily">
        <label>Date</label>
        <input type="date" name="date" class="form-control" style="width:170px;" value="<?= $filter_date ?>" max="<?= $today ?>">
        <button type="submit" class="btn-apply"><i class="fas fa-search me-1"></i> Apply</button>
        <button type="button" class="btn-print" onclick="printDaily()"><i class="fas fa-print"></i> Print</button>
    </form>
    <div class="stats-grid fade-in">
        <div class="stat-card"><div class="s-icon"><i class="fas fa-money-bill-wave"></i></div><div class="s-label">Gross Revenue</div><div class="s-value"><?=peso((float)($s['gross_revenue']??0))?></div><div class="s-sub">Net: <?=peso((float)($s['net_revenue']??0))?></div></div>
        <div class="stat-card c-coral"><div class="s-icon"><i class="fas fa-shopping-cart"></i></div><div class="s-label">Total Orders</div><div class="s-value"><?=number_format($s['total_orders']??0)?></div><div class="s-sub">Cancelled: <?=$s['cancelled_orders']??0?></div></div>
        <div class="stat-card c-teal"><div class="s-icon"><i class="fas fa-receipt"></i></div><div class="s-label">Avg Order Value</div><div class="s-value"><?=peso((float)($s['avg_order_value']??0))?></div><div class="s-sub">Discounts: <?=peso((float)($s['total_discounts']??0))?></div></div>
        <div class="stat-card c-green"><div class="s-icon"><i class="fas fa-user-tie"></i></div><div class="s-label">Active Cashiers</div><div class="s-value"><?=$s['active_cashiers']??0?></div><div class="s-sub"><?=date('M d, Y',strtotime($filter_date))?></div></div>
    </div>
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="rpt-card fade-in"><div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-star"></i> Top Items</div></div>
            <div class="rpt-card-body p-0">
            <?php if(empty($data['top_items'])): ?><div class="no-data"><i class="fas fa-inbox"></i> No sales for this date.</div>
            <?php else: ?><div style="overflow-x:auto;"><table class="rpt-table"><thead><tr><th>#</th><th>Item</th><th>Category</th><th>Sold</th><th>Revenue</th></tr></thead><tbody>
            <?php foreach($data['top_items'] as $i=>$row): ?><tr><td><?=$i+1?></td><td><strong><?=htmlspecialchars($row['item_name'])?></strong></td><td><span class="rpt-badge badge-primary"><?=htmlspecialchars($row['category_name'])?></span></td><td><?=number_format($row['units_sold'])?></td><td><?=peso((float)$row['total_revenue'])?></td></tr><?php endforeach; ?>
            </tbody></table></div><?php endif; ?>
            </div></div>
        </div>
        <div class="col-lg-6">
            <div class="rpt-card fade-in"><div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-clock"></i> Orders by Hour</div></div>
            <div class="rpt-card-body">
            <?php if(empty($data['peak_hours'])): ?><div class="no-data"><i class="fas fa-inbox"></i> No data.</div>
            <?php else: ?><canvas id="dailyPeak" style="max-height:240px;"></canvas><?php endif; ?>
            </div></div>
        </div>
        <div class="col-lg-6">
            <div class="rpt-card fade-in"><div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-chart-pie"></i> Order Types</div></div>
            <div class="rpt-card-body p-0">
            <?php if(empty($data['order_types'])): ?><div class="no-data p-4"><i class="fas fa-inbox"></i> No data.</div>
            <?php else: ?><div style="overflow-x:auto;"><table class="rpt-table"><thead><tr><th>Type</th><th>Source</th><th>Orders</th><th>Revenue</th></tr></thead><tbody>
            <?php foreach($data['order_types'] as $row): ?><tr><td><?=ucfirst(str_replace('_',' ',$row['order_type']))?></td><td><?=ucfirst(str_replace('_',' ',$row['order_source']))?></td><td><?=number_format($row['total_orders'])?></td><td><?=peso((float)$row['revenue'])?></td></tr><?php endforeach; ?>
            </tbody></table></div><?php endif; ?>
            </div></div>
        </div>
        <div class="col-lg-6">
            <div class="rpt-card fade-in"><div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-user-tie"></i> Cashier Breakdown</div></div>
            <div class="rpt-card-body p-0">
            <?php if(empty($data['cashiers'])): ?><div class="no-data p-4"><i class="fas fa-inbox"></i> No data.</div>
            <?php else: ?><div style="overflow-x:auto;"><table class="rpt-table"><thead><tr><th>Cashier</th><th>Orders</th><th>Sales</th><th>Discounts</th></tr></thead><tbody>
            <?php foreach($data['cashiers'] as $row): ?><tr><td><strong><?=htmlspecialchars($row['cashier'])?></strong></td><td><?=$row['orders_handled']?></td><td><?=peso((float)$row['total_sales'])?></td><td><?=peso((float)$row['discounts_given'])?></td></tr><?php endforeach; ?>
            </tbody></table></div><?php endif; ?>
            </div></div>
        </div>
    </div>
    <?php if(!empty($data['peak_hours'])): ?>
    <script>(function(){const h=<?=json_encode(array_column($data['peak_hours'],'hour_of_day'))?>;const o=<?=json_encode(array_column($data['peak_hours'],'total_orders'))?>;new Chart(document.getElementById('dailyPeak').getContext('2d'),{type:'bar',data:{labels:h.map(v=>(v%12||12)+' '+(v>=12?'PM':'AM')),datasets:[{label:'Orders',data:o,backgroundColor:o.map(v=>v===Math.max(...o)?'#ff6b4a':'rgba(15,76,92,.65)'),borderRadius:6,borderSkipped:false}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,grid:{color:'rgba(15,76,92,.04)'}},x:{grid:{display:false}}}}});})();</script>
    <?php endif; ?>

    <?php
    // =====================================================================
    // MONTHLY (admin) — A4 popup
    // =====================================================================
    elseif ($tab === 'monthly'):
        $s = $data['summary'] ?? [];
    ?>
    <form method="GET" action="<?= $baseUrl ?>/index.php" class="filter-bar">
        <input type="hidden" name="page" value="reports">
        <input type="hidden" name="tab"  value="monthly">
        <label>Month</label>
        <select name="month" class="form-select" style="width:150px;">
            <?php foreach($months_list as $n=>$nm): ?><option value="<?=$n?>" <?=$n===$filter_month?'selected':''?>><?=$nm?></option><?php endforeach; ?>
        </select>
        <label>Year</label>
        <input type="number" name="year" class="form-control" style="width:100px;" value="<?=$filter_year?>" min="2020" max="<?=date('Y')?>">
        <button type="submit" class="btn-apply"><i class="fas fa-search me-1"></i> Apply</button>
        <button type="button" class="btn-print" onclick="printA4('monthly-content', 'Monthly Report &mdash; <?= $months_list[$filter_month] ?> <?= $filter_year ?>')"><i class="fas fa-print"></i> Print</button>
    </form>
    <div class="stats-grid fade-in">
        <div class="stat-card"><div class="s-icon"><i class="fas fa-money-bill-wave"></i></div><div class="s-label">Gross Revenue</div><div class="s-value"><?=peso((float)($s['gross_revenue']??0))?></div><div class="s-sub">Net: <?=peso((float)($s['net_revenue']??0))?></div></div>
        <div class="stat-card c-coral"><div class="s-icon"><i class="fas fa-shopping-cart"></i></div><div class="s-label">Total Orders</div><div class="s-value"><?=number_format($s['total_orders']??0)?></div><div class="s-sub">Cancelled: <?=$s['cancelled_orders']??0?></div></div>
        <div class="stat-card c-teal"><div class="s-icon"><i class="fas fa-receipt"></i></div><div class="s-label">Avg Order Value</div><div class="s-value"><?=peso((float)($s['avg_order_value']??0))?></div><div class="s-sub">Discounts: <?=peso((float)($s['total_discounts']??0))?></div></div>
        <div class="stat-card c-green"><div class="s-icon"><i class="fas fa-users"></i></div><div class="s-label">Active Cashiers</div><div class="s-value"><?=$s['active_cashiers']??0?></div><div class="s-sub"><?=$months_list[$filter_month]?> <?=$filter_year?></div></div>
    </div>
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
                <thead><tr><th>Date</th><th>Orders</th><th>Revenue</th></tr></thead>
                <tbody>
                <?php
                $total_orders_m = 0; $total_rev_m = 0;
                foreach($data['daily_chart'] as $day):
                    $total_orders_m += $day['total_orders'];
                    $total_rev_m    += $day['revenue'];
                ?>
                <tr><td><?=date('M d, Y',strtotime($day['sale_date']))?></td><td><?=number_format($day['total_orders'])?></td><td><?=peso((float)$day['revenue'])?></td></tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot><tr style="font-weight:700;background:#f2f6fc;">
                    <td style="padding:1rem 1.2rem;">Monthly Total</td>
                    <td style="padding:1rem 1.2rem;"><?=number_format($total_orders_m)?></td>
                    <td style="padding:1rem 1.2rem;"><?=peso($total_rev_m)?></td>
                </tr></tfoot>
            </table></div><?php endif; ?>
            </div></div>
        </div>
        <div class="col-12">
            <div class="rpt-card fade-in"><div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-star"></i> Top Selling Items</div></div>
            <div class="rpt-card-body p-0">
            <?php if(empty($data['top_items'])): ?><div class="no-data"><i class="fas fa-inbox"></i> No data.</div>
            <?php else: ?><div style="overflow-x:auto;"><table class="rpt-table"><thead><tr><th>#</th><th>Item</th><th>Category</th><th>Sold</th><th>Revenue</th><th>Profit</th></tr></thead><tbody>
            <?php foreach($data['top_items'] as $i=>$row): ?><tr><td><?=$i+1?></td><td><strong><?=htmlspecialchars($row['item_name'])?></strong></td><td><?=htmlspecialchars($row['category_name'])?></td><td><?=number_format($row['units_sold'])?></td><td><?=peso((float)$row['total_revenue'])?></td><td style="color:var(--success);font-weight:600;"><?=peso((float)$row['gross_profit'])?></td></tr><?php endforeach; ?>
            </tbody></table></div><?php endif; ?>
            </div></div>
        </div>
        <div class="col-12">
            <div class="rpt-card fade-in"><div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-user-tie"></i> Cashier Performance</div></div>
            <div class="rpt-card-body p-0">
            <?php if(empty($data['cashiers'])): ?><div class="no-data"><i class="fas fa-inbox"></i> No data.</div>
            <?php else: ?>
                <?php $grand=array_sum(array_column($data['cashiers'],'total_sales')); ?>
                <div style="overflow-x:auto;"><table class="rpt-table"><thead><tr><th>Cashier</th><th>Orders</th><th>Total Sales</th><th>Avg/Order</th><th>Discounts</th><th>Cancelled</th></tr></thead><tbody>
                <?php foreach($data['cashiers'] as $row): $pct=$grand>0?round($row['total_sales']/$grand*100,1):0; ?>
                <tr><td><strong><?=htmlspecialchars($row['cashier'])?></strong><br><small style="color:var(--warm-gray);"><?=htmlspecialchars($row['username'])?></small></td><td><?=$row['orders_handled']?></td><td><strong><?=peso((float)$row['total_sales'])?></strong> <small>(<?=$pct?>%)</small><div class="mini-bar"><div class="mini-bar-fill" style="width:<?=$pct?>%;"></div></div></td><td><?=peso((float)$row['avg_order_value'])?></td><td><?=peso((float)$row['discounts_given'])?></td><td><?=$row['cancelled_orders']>0?'<span class="rpt-badge badge-danger">'.$row['cancelled_orders'].'</span>':'<span class="rpt-badge badge-success">0</span>'?></td></tr>
                <?php endforeach; ?>
                </tbody></table></div>
            <?php endif; ?>
            </div></div>
        </div>
    </div>
    </div><!-- /monthly-content -->
    <script>(function(){
        const dd=<?=json_encode(array_column($data['daily_chart']??[],'sale_date'))?>;
        const dr=<?=json_encode(array_column($data['daily_chart']??[],'revenue'))?>;
        if(dd.length) new Chart(document.getElementById('monthlyTrend').getContext('2d'),{type:'line',data:{labels:dd.map(d=>{const p=d.split('-');return p[2]+' '+['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'][parseInt(p[1])-1];}),datasets:[{label:'Revenue',data:dr,backgroundColor:'rgba(15,76,92,.07)',borderColor:'#0f4c5c',borderWidth:2.5,tension:.35,fill:true,pointBackgroundColor:'#ff6b4a',pointRadius:4}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,grid:{color:'rgba(15,76,92,.04)'},ticks:{callback:v=>'&#8369;'+v.toLocaleString()}},x:{grid:{display:false}}}}});
        const cl=<?=json_encode(array_column($data['categories']??[],'category_name'))?>;
        const cv=<?=json_encode(array_column($data['categories']??[],'revenue'))?>;
        if(cl.length) new Chart(document.getElementById('monthlyCat').getContext('2d'),{type:'doughnut',data:{labels:cl,datasets:[{data:cv,backgroundColor:['#0f4c5c','#ff6b4a','#fee440','#1e2f4e','#5e9b9e','#ff8c70'],borderWidth:0,hoverOffset:8}]},options:{responsive:true,maintainAspectRatio:false,cutout:'65%',plugins:{legend:{position:'bottom',labels:{boxWidth:12,padding:14}},tooltip:{callbacks:{label:ctx=>ctx.label+': &#8369;'+parseFloat(ctx.raw).toLocaleString()}}}}});
    })();</script>

    <?php
    // =====================================================================
    // YEARLY (admin) — A4 popup
    // =====================================================================
    elseif ($tab === 'yearly'):
        $s = $data['summary'] ?? [];
        $monthly_keys   = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct_m','nov','dec_m'];
        $monthly_labels = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    ?>
    <form method="GET" action="<?= $baseUrl ?>/index.php" class="filter-bar">
        <input type="hidden" name="page" value="reports">
        <input type="hidden" name="tab"  value="yearly">
        <label>Year</label>
        <select name="year" class="form-select" style="width:120px;">
            <?php foreach(($data['available_years']??[$filter_year]) as $yr): ?><option value="<?=$yr?>" <?=$yr==$filter_year?'selected':''?>><?=$yr?></option><?php endforeach; ?>
        </select>
        <button type="submit" class="btn-apply"><i class="fas fa-search me-1"></i> Apply</button>
        <button type="button" class="btn-print" onclick="printA4('yearly-content', 'Yearly Report &mdash; <?= $filter_year ?>')"><i class="fas fa-print"></i> Print</button>
    </form>
    <div class="stats-grid fade-in">
        <div class="stat-card"><div class="s-icon"><i class="fas fa-money-bill-wave"></i></div><div class="s-label">Gross Revenue <?=$filter_year?></div><div class="s-value"><?=peso((float)($s['gross_revenue']??0))?></div><div class="s-sub">Net: <?=peso((float)($s['net_revenue']??0))?></div></div>
        <div class="stat-card c-coral"><div class="s-icon"><i class="fas fa-shopping-cart"></i></div><div class="s-label">Total Orders</div><div class="s-value"><?=number_format($s['total_orders']??0)?></div><div class="s-sub">Discounts: <?=peso((float)($s['total_discounts']??0))?></div></div>
        <div class="stat-card c-teal"><div class="s-icon"><i class="fas fa-receipt"></i></div><div class="s-label">Avg Order Value</div><div class="s-value"><?=peso((float)($s['avg_order_value']??0))?></div></div>
    </div>
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
                    <thead><tr><th>Month</th><th>Revenue</th></tr></thead>
                    <tbody>
                    <?php
                    $yearly_total = 0;
                    foreach($monthly_keys as $mi=>$key):
                        $val=(float)($s[$key]??0);
                        $yearly_total += $val;
                        $pct=$s['gross_revenue']>0?min(100,round($val/$s['gross_revenue']*100)):0;
                    ?>
                    <tr><td><?=$monthly_labels[$mi]?></td><td><strong><?=peso($val)?></strong><?php if($val>0): ?><div class="mini-bar"><div class="mini-bar-fill" style="width:<?=$pct?>%;"></div></div><?php endif; ?></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot><tr style="font-weight:700;background:#f2f6fc;"><td style="padding:1rem 1.2rem;">Yearly Total</td><td style="padding:1rem 1.2rem;"><?=peso($yearly_total)?></td></tr></tfoot>
                </table>
            </div></div>
        </div>
        <div class="col-lg-7">
            <div class="rpt-card fade-in"><div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-star"></i> Top Items <?=$filter_year?></div></div>
            <div class="rpt-card-body p-0">
            <?php if(empty($data['top_items'])): ?><div class="no-data"><i class="fas fa-inbox"></i> No data.</div>
            <?php else: ?><div style="overflow-x:auto;"><table class="rpt-table"><thead><tr><th>#</th><th>Item</th><th>Sold</th><th>Revenue</th><th>Profit</th></tr></thead><tbody>
            <?php foreach($data['top_items'] as $i=>$row): ?><tr><td><?=$i+1?></td><td><strong><?=htmlspecialchars($row['item_name'])?></strong></td><td><?=number_format($row['units_sold'])?></td><td><?=peso((float)$row['total_revenue'])?></td><td style="color:var(--success);font-weight:600;"><?=peso((float)$row['gross_profit'])?></td></tr><?php endforeach; ?>
            </tbody></table></div><?php endif; ?>
            </div></div>
        </div>
    </div>
    </div><!-- /yearly-content -->
    <script>(function(){
        const ml=<?=json_encode(array_slice($monthly_labels,0))?>;
        const md=<?=json_encode(array_map(fn($k)=>(float)($s[$k]??0),$monthly_keys))?>;
        new Chart(document.getElementById('yearlyBar').getContext('2d'),{type:'bar',data:{labels:ml,datasets:[{label:'Revenue',data:md,backgroundColor:md.map(v=>v===Math.max(...md)?'#ff6b4a':'rgba(15,76,92,.65)'),borderRadius:8,borderSkipped:false}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,grid:{color:'rgba(15,76,92,.04)'},ticks:{callback:v=>'&#8369;'+v.toLocaleString()}},x:{grid:{display:false}}}}});
    })();</script>

    <?php
    // =====================================================================
    // PRODUCTS (admin) — A4 popup
    // =====================================================================
    elseif ($tab === 'products'):
    ?>
    <form method="GET" action="<?= $baseUrl ?>/index.php" class="filter-bar">
        <input type="hidden" name="page" value="reports">
        <input type="hidden" name="tab"  value="products">
        <label>From</label><input type="date" name="start" id="rpt-start" class="form-control" style="width:160px;" value="<?=$range_start?>" max="<?=$today?>">
        <label>To</label><input type="date" name="end" id="rpt-end" class="form-control" style="width:160px;" value="<?=$range_end?>" min="<?=$range_start?>" max="<?=$today?>">
        <button type="submit" class="btn-apply"><i class="fas fa-search me-1"></i> Apply</button>
        <button type="button" class="btn-print" onclick="printA4('products-content','Products Report')"><i class="fas fa-print"></i> Print</button>
    </form>
    <div id="products-content">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="rpt-card fade-in"><div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-star"></i> Top Selling Items</div></div>
            <div class="rpt-card-body p-0">
            <?php if(empty($data['top_items'])): ?><div class="no-data"><i class="fas fa-inbox"></i> No data.</div>
            <?php else: ?><div style="overflow-x:auto;"><table class="rpt-table"><thead><tr><th>#</th><th>Item</th><th>Category</th><th>Price</th><th>Sold</th><th>Revenue</th><th>Profit</th></tr></thead><tbody>
            <?php foreach($data['top_items'] as $i=>$row): ?><tr><td><?=$i+1?></td><td><strong><?=htmlspecialchars($row['item_name'])?></strong></td><td><span class="rpt-badge badge-primary"><?=htmlspecialchars($row['category_name'])?></span></td><td><?=peso((float)$row['price'])?></td><td><?=number_format($row['units_sold'])?></td><td><?=peso((float)$row['total_revenue'])?></td><td style="color:var(--success);font-weight:600;"><?=peso((float)$row['gross_profit'])?></td></tr><?php endforeach; ?>
            </tbody></table></div><?php endif; ?>
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
    </div><!-- /products-content -->
    <script>(function(){const cl=<?=json_encode(array_column($data['categories']??[],'category_name'))?>;const cv=<?=json_encode(array_column($data['categories']??[],'revenue'))?>;if(cl.length) new Chart(document.getElementById('prodCat').getContext('2d'),{type:'doughnut',data:{labels:cl,datasets:[{data:cv,backgroundColor:['#0f4c5c','#ff6b4a','#fee440','#1e2f4e','#5e9b9e','#ff8c70'],borderWidth:0,hoverOffset:8}]},options:{responsive:true,maintainAspectRatio:false,cutout:'65%',plugins:{legend:{position:'bottom',labels:{boxWidth:12,padding:14}},tooltip:{callbacks:{label:ctx=>ctx.label+': &#8369;'+parseFloat(ctx.raw).toLocaleString()}}}}});})();</script>

    <?php
    // =====================================================================
    // STAFF (admin) — A4 popup
    // =====================================================================
    elseif ($tab === 'staff'):
    ?>
    <form method="GET" action="<?= $baseUrl ?>/index.php" class="filter-bar">
        <input type="hidden" name="page" value="reports">
        <input type="hidden" name="tab"  value="staff">
        <label>From</label><input type="date" name="start" id="rpt-start" class="form-control" style="width:160px;" value="<?=$range_start?>" max="<?=$today?>">
        <label>To</label><input type="date" name="end" id="rpt-end" class="form-control" style="width:160px;" value="<?=$range_end?>" min="<?=$range_start?>" max="<?=$today?>">
        <button type="submit" class="btn-apply"><i class="fas fa-search me-1"></i> Apply</button>
        <button type="button" class="btn-print" onclick="printA4('staff-content','Staff Performance Report')"><i class="fas fa-print"></i> Print</button>
    </form>
    <div id="staff-content">
    <div class="row g-4">
        <div class="col-12">
            <div class="rpt-card fade-in"><div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-user-tie"></i> Cashier Performance</div></div>
            <div class="rpt-card-body p-0">
            <?php if(empty($data['cashiers'])): ?><div class="no-data"><i class="fas fa-inbox"></i> No data for this range.</div>
            <?php else: ?>
                <?php $grand=array_sum(array_column($data['cashiers'],'total_sales')); ?>
                <div style="overflow-x:auto;"><table class="rpt-table"><thead><tr><th>Cashier</th><th>Orders</th><th>Total Sales</th><th>Avg/Order</th><th>Discounts</th><th>Cancelled</th></tr></thead><tbody>
                <?php foreach($data['cashiers'] as $row): $pct=$grand>0?round($row['total_sales']/$grand*100,1):0; ?>
                <tr><td><strong><?=htmlspecialchars($row['cashier'])?></strong><br><small style="color:var(--warm-gray);"><?=htmlspecialchars($row['username'])?></small></td><td><?=$row['orders_handled']?></td><td><strong><?=peso((float)$row['total_sales'])?></strong> <small>(<?=$pct?>%)</small><div class="mini-bar"><div class="mini-bar-fill" style="width:<?=$pct?>%;"></div></div></td><td><?=peso((float)$row['avg_order_value'])?></td><td><?=peso((float)$row['discounts_given'])?></td><td><?=$row['cancelled_orders']>0?'<span class="rpt-badge badge-danger">'.$row['cancelled_orders'].'</span>':'<span class="rpt-badge badge-success">0</span>'?></td></tr>
                <?php endforeach; ?>
                </tbody></table></div>
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
    </div><!-- /staff-content -->
    <?php if(!empty($data['peak_hours'])): ?>
    <script>(function(){const h=<?=json_encode(array_column($data['peak_hours'],'hour_of_day'))?>;const o=<?=json_encode(array_column($data['peak_hours'],'total_orders'))?>;new Chart(document.getElementById('staffPeak').getContext('2d'),{type:'bar',data:{labels:h.map(v=>(v%12||12)+' '+(v>=12?'PM':'AM')),datasets:[{label:'Orders',data:o,backgroundColor:o.map(v=>v===Math.max(...o)?'#ff6b4a':'rgba(15,76,92,.65)'),borderRadius:6,borderSkipped:false}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,grid:{color:'rgba(15,76,92,.04)'}},x:{grid:{display:false}}}}});})();</script>
    <?php endif; ?>

    <?php
    // =====================================================================
    // AUDIT (admin) — A4 popup
    // =====================================================================
    elseif ($tab === 'audit'):
    ?>
    <form method="GET" action="<?= $baseUrl ?>/index.php" class="filter-bar">
        <input type="hidden" name="page" value="reports">
        <input type="hidden" name="tab"  value="audit">
        <label>From</label><input type="date" name="start" id="rpt-start" class="form-control" style="width:160px;" value="<?=$range_start?>" max="<?=$today?>">
        <label>To</label><input type="date" name="end" id="rpt-end" class="form-control" style="width:160px;" value="<?=$range_end?>" min="<?=$range_start?>" max="<?=$today?>">
        <button type="submit" class="btn-apply"><i class="fas fa-search me-1"></i> Apply</button>
        <button type="button" class="btn-print" onclick="printA4('audit-content','Audit Report')"><i class="fas fa-print"></i> Print</button>
    </form>
    <div id="audit-content">
    <div class="rpt-card fade-in">
        <div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-tag"></i> Discounts &amp; Cancellations</div></div>
        <div class="rpt-card-body p-0">
        <?php if(empty($data['discounts'])): ?><div class="no-data"><i class="fas fa-inbox"></i> No discounts or cancellations in this range.</div>
        <?php else: ?><div style="overflow-x:auto;"><table class="rpt-table">
            <thead><tr><th>Date (Created)</th><th>Completed At</th><th>Order #</th><th>Cashier</th><th>Customer</th><th>Subtotal</th><th>Discount</th><th>Collected</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach($data['discounts'] as $row): ?>
            <tr>
                <td><?=date('M d, Y',strtotime($row['created_at']))?></td>
                <td><?=$row['completed_at']?date('M d, Y H:i',strtotime($row['completed_at'])):'<span style="color:var(--warm-gray);">&#8212;</span>'?></td>
                <td><strong><?=htmlspecialchars($row['order_number'])?></strong></td>
                <td><?=htmlspecialchars($row['cashier'])?></td>
                <td><?=!empty($row['customer_name'])?htmlspecialchars($row['customer_name']):'<span style="color:var(--warm-gray);">Walk-in</span>'?></td>
                <td><?=peso((float)$row['subtotal'])?></td>
                <td><?=$row['discount']>0?'<span class="rpt-badge badge-warning">'.peso((float)$row['discount']).'</span>':'&#8212;'?></td>
                <td><?=peso((float)$row['total_amount'])?></td>
                <td><span class="rpt-badge <?=$row['status']==='cancelled'?'badge-danger':($row['status']==='completed'?'badge-success':'badge-warning')?>"><?=ucfirst($row['status'])?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div><?php endif; ?>
        </div>
    </div>
    <div class="rpt-card fade-in">
        <div class="rpt-card-header"><div class="rpt-card-title"><i class="fas fa-cash-register"></i> Payment Reconciliation</div></div>
        <div class="rpt-card-body p-0">
        <?php if(empty($data['payments'])): ?><div class="no-data"><i class="fas fa-inbox"></i> No payment data in this range.</div>
        <?php else: ?><div style="overflow-x:auto;"><table class="rpt-table">
            <thead><tr><th>Date</th><th>Cashier</th><th>Transactions</th><th>Cash In</th><th>Change Out</th><th>Net Cash</th><th>Expected</th><th>Variance</th></tr></thead>
            <tbody>
            <?php foreach($data['payments'] as $row): $var=(float)$row['net_cash']-(float)$row['expected_from_orders']; ?>
            <tr>
                <td><?=date('M d, Y',strtotime($row['pay_date']))?></td>
                <td><strong><?=htmlspecialchars($row['cashier'])?></strong></td>
                <td><?=$row['transactions']?></td>
                <td><?=peso((float)$row['cash_received'])?></td>
                <td><?=peso((float)$row['change_given'])?></td>
                <td><strong><?=peso((float)$row['net_cash'])?></strong></td>
                <td><?=peso((float)$row['expected_from_orders'])?></td>
                <td style="color:<?=$var>=0?'var(--success)':'var(--danger)'?>;font-weight:600;"><?=($var>=0?'+':'').peso($var)?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div><?php endif; ?>
        </div>
    </div>
    </div><!-- /audit-content -->

    <?php endif; // end tab switch ?>

</div><!-- /main-content -->

<footer style="background:white;border-top:1px solid var(--border);padding:1.4rem 2.2rem;margin-left:var(--sidebar-width);color:var(--warm-gray);">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem;">
        <div><strong>Copyright &copy; <?= date('Y') ?> DinewithDin - Cafeteria.</strong> All rights reserved.</div>
        <div><b>Version</b> 1.0.0</div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* ── Sidebar ── */
const menuToggle     = document.getElementById('menuToggle');
const sidebar        = document.getElementById('sidebar');
const sidebarOverlay = document.getElementById('sidebarOverlay');
menuToggle?.addEventListener('click', () => { sidebar.classList.toggle('show'); sidebarOverlay.classList.toggle('show'); });
sidebarOverlay?.addEventListener('click', () => { sidebar.classList.remove('show'); sidebarOverlay.classList.remove('show'); });

/* ── Date range guard ── */
(function(){
    const s = document.getElementById('rpt-start');
    const e = document.getElementById('rpt-end');
    if(!s||!e) return;
    function enforce(){ if(!s.value) return; e.min=s.value; if(e.value&&e.value<s.value) e.value=s.value; }
    s.addEventListener('change', enforce);
    enforce();
})();

/* ================================================================
   SHARED: open a popup and write HTML into it, then print
   ================================================================ */
/* ================================================================
   openPrintTab(html) — opens a real blob: URL tab (not about:blank)
   Chrome treats blob: URLs as proper pages, auto-prints, then closes.
   Same behaviour as print_receipt.php but without a server round-trip.
   ================================================================ */
function openPrintTab(html) {
    // Build a Blob from the HTML string and create an object URL
    var blob = new Blob([html], { type: 'text/html;charset=utf-8' });
    var url  = URL.createObjectURL(blob);

    // Open the blob URL in a new tab — Chrome shows it as a real page
    var tab = window.open(url, '_blank');
    if (!tab) {
        alert('Please allow pop-ups for this site to enable printing.');
        URL.revokeObjectURL(url);
        return;
    }

    // Clean up the object URL after the tab loads (memory management)
    tab.addEventListener('load', function() {
        URL.revokeObjectURL(url);
    });
}

/* ================================================================
   printCloseout() — 80mm thermal receipt
   ================================================================ */
function printCloseout() {
    var rows = document.querySelectorAll('.closeout-wrap .receipt-row');
    var tableRows = '';
    rows.forEach(function(row) {
        var lbl = row.querySelector('.rl');
        var rv  = row.querySelector('.rv');
        if (!lbl || !rv) return;
        tableRows += '<tr><td class="lbl">' + lbl.innerText.trim() + '</td><td class="val">' + rv.innerText.trim() + '</td></tr>';
    });

    var turnover = (document.querySelector('.closeout-wrap .receipt-total .tv') || {innerText:''}).innerText.trim();
    var footer   = (document.querySelector('.closeout-wrap .receipt-footer')     || {innerText:''}).innerText.trim();

    if (!tableRows) { alert('No close-out data to print.'); return; }

    var html =
        '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Close-out</title>' +
        '<style>' +
        'html,body{margin:0;padding:0;background:#fff;}' +
        '@page{size:80mm auto;margin:5mm 4mm;}' +
        'body{font-family:"Courier New",Courier,monospace;font-size:11px;line-height:1.55;color:#000;width:72mm;}' +
        '.c{text-align:center;} .shop{font-size:14px;font-weight:bold;letter-spacing:.5px;}' +
        '.sub{font-size:9px;color:#555;margin-top:1px;} .title{font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:1px;margin:5px 0 4px;}' +
        'hr{border:none;border-top:1px dashed #000;margin:5px 0;}' +
        'table{width:100%;border-collapse:collapse;} td{padding:2px 0;font-size:11px;vertical-align:top;}' +
        'td.lbl{color:#333;width:58%;} td.val{text-align:right;font-weight:bold;color:#000;width:42%;}' +
        '.total{border:2px solid #000;padding:5px 7px;margin:7px 0;display:flex;justify-content:space-between;align-items:center;}' +
        '.tl{font-size:9px;font-weight:bold;text-transform:uppercase;letter-spacing:.5px;}' +
        '.tv{font-size:16px;font-weight:bold;}' +
        '.sigs{display:grid;grid-template-columns:1fr 1fr;gap:8mm;margin-top:10px;padding-top:6px;border-top:1px dashed #000;}' +
        '.sl{border-bottom:1px solid #000;height:26px;margin-bottom:3px;} .slbl{font-size:8px;text-align:center;color:#444;}' +
        '.footer{text-align:center;font-size:8px;color:#555;margin-top:7px;padding-top:5px;border-top:1px dashed #000;}' +
        '</style></head><body>' +
        '<div class="c"><div class="shop">Dine with Din</div><div class="sub">Cafeteria POS System</div><div class="title">Shift Close-out</div></div>' +
        '<hr><table>' + tableRows + '</table><hr>' +
        '<div class="total"><div class="tl">Cash to Turn Over</div><div class="tv">' + turnover + '</div></div>' +
        '<div class="sigs"><div><div class="sl"></div><div class="slbl">Cashier Signature</div></div><div><div class="sl"></div><div class="slbl">Received By</div></div></div>' +
        '<div class="footer">' + footer + '</div>' +
        '</body></html>';

    // Add auto-print + close script inside the HTML (same as print_receipt.php)
    html = html.replace('</body></html>',
        '<script>' +
        'window.addEventListener(\'load\', function() {' +
        '  setTimeout(function() { window.print(); }, 400);' +
        '});' +
        'window.addEventListener(\'afterprint\', function() {' +
        '  window.close();' +
        '});' +
        '<\/script></body></html>'
    );
    openPrintTab(html);
}

/* ================================================================
   printDaily() — 80mm thermal, data from PHP (accurate, no DOM scraping)
   ================================================================ */
var _dailyData = <?php
    if ($tab === 'daily'):
        $s = $data['summary'] ?? [];
        $cashiers = $data['cashiers'] ?? [];
        $top_items = $data['top_items'] ?? [];
        echo json_encode([
            'date'          => date('F d, Y', strtotime($filter_date)),
            'printed_at'    => (new DateTime('now', new DateTimeZone('Asia/Manila')))->format('M d, Y h:i A') . ' (PHT)',
            'gross_revenue' => number_format((float)($s['gross_revenue'] ?? 0), 2),
            'net_revenue'   => number_format((float)($s['net_revenue'] ?? 0), 2),
            'total_orders'  => number_format($s['total_orders'] ?? 0),
            'cancelled'     => $s['cancelled_orders'] ?? 0,
            'avg_order'     => number_format((float)($s['avg_order_value'] ?? 0), 2),
            'discounts'     => number_format((float)($s['total_discounts'] ?? 0), 2),
            'active_cashiers' => $s['active_cashiers'] ?? 0,
            'cashiers'      => array_map(fn($r) => [
                'name'     => $r['cashier'],
                'orders'   => $r['orders_handled'],
                'sales'    => number_format((float)$r['total_sales'], 2),
                'discounts'=> number_format((float)$r['discounts_given'], 2),
                'cancelled'=> $r['cancelled_orders'],
            ], $cashiers),
            'top_items'     => array_map(fn($r) => [
                'name'    => $r['item_name'],
                'sold'    => $r['units_sold'],
                'revenue' => number_format((float)$r['total_revenue'], 2),
            ], array_slice($top_items, 0, 5)),
        ]);
    else:
        echo 'null';
    endif;
?>;

function printDaily() {
    var d = _dailyData;
    if (!d) { alert('No daily data to print. Please load the Daily tab first.'); return; }

    // Summary rows
    var sumRows =
        row('Report date',      d.date) +
        row('Printed at',       d.printed_at) +
        sep() +
        row('Gross revenue',    '₱' + d.gross_revenue, true) +
        row('Discounts given',  '- ₱' + d.discounts) +
        row('Net revenue',      '₱' + d.net_revenue, true) +
        sep() +
        row('Total orders',     d.total_orders) +
        row('Cancelled orders', d.cancelled) +
        row('Avg order value',  '₱' + d.avg_order) +
        row('Active cashiers',  d.active_cashiers);

    // Top items section
    var itemSection = '';
    if (d.top_items && d.top_items.length) {
        var itemRows = '';
        d.top_items.forEach(function(item) {
            itemRows +=
                '<tr>' +
                '<td class="nm">' + esc(item.name) + '</td>' +
                '<td class="r">' + item.sold + '</td>' +
                '<td class="r">₱' + item.revenue + '</td>' +
                '</tr>';
        });
        itemSection =
            sep() +
            '<div class="sh">Top Items</div>' +
            '<table><thead><tr><th>Item</th><th class="r">Sold</th><th class="r">Revenue</th></tr></thead>' +
            '<tbody>' + itemRows + '</tbody></table>';
    }

    // Cashier breakdown
    var cashSection = '';
    if (d.cashiers && d.cashiers.length) {
        var cashRows = '';
        d.cashiers.forEach(function(c) {
            cashRows +=
                '<tr>' +
                '<td class="nm">' + esc(c.name) + '</td>' +
                '<td class="r">' + c.orders + '</td>' +
                '<td class="r">₱' + c.sales + '</td>' +
                '<td class="r">' + c.cancelled + '</td>' +
                '</tr>';
        });
        cashSection =
            sep() +
            '<div class="sh">Cashier Breakdown</div>' +
            '<table><thead><tr><th>Cashier</th><th class="r">Orders</th><th class="r">Sales</th><th class="r">Cancelled</th></tr></thead>' +
            '<tbody>' + cashRows + '</tbody></table>';
    }

    var html =
        '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Daily Report</title>' +
        '<style>' +
        'html,body{margin:0;padding:0;background:#fff;}' +
        '@page{size:80mm auto;margin:5mm 4mm;}' +
        'body{font-family:"Courier New",Courier,monospace;font-size:11px;line-height:1.6;color:#000;width:72mm;}' +
        '.c{text-align:center;}' +
        '.shop{font-size:14px;font-weight:bold;letter-spacing:.5px;margin-bottom:1px;}' +
        '.sub{font-size:9px;color:#555;}' +
        '.title{font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:1px;margin-top:5px;}' +
        '.sep{border:none;border-top:1px dashed #000;margin:5px 0;}' +
        '.row{display:flex;justify-content:space-between;padding:2.5px 0;}' +
        '.row .lb{color:#333;font-size:11px;}' +
        '.row .vl{font-weight:bold;font-size:11px;text-align:right;}' +
        '.row .vl.hi{font-size:12px;}' +
        '.sh{font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.5px;margin:3px 0 2px;}' +
        'table{width:100%;border-collapse:collapse;font-size:10px;margin-top:2px;}' +
        'thead th{font-size:9px;font-weight:bold;text-transform:uppercase;letter-spacing:.3px;border-bottom:1px solid #000;padding:2px 1px;}' +
        'thead th.r{text-align:right;}' +
        'tbody td{padding:2.5px 1px;border-bottom:1px dotted #ccc;vertical-align:top;}' +
        'tbody td.r{text-align:right;}' +
        'tbody td.nm{max-width:38mm;word-break:break-word;}' +
        '.footer{text-align:center;font-size:8px;color:#555;margin-top:8px;padding-top:5px;border-top:1px dashed #000;}' +
        '</style></head><body>' +
        '<div class="c">' +
        '<div class="shop">Dine with Din</div>' +
        '<div class="sub">Cafeteria POS System</div>' +
        '<div class="title">Daily Report</div>' +
        '</div>' +
        '<hr class="sep">' +
        sumRows +
        itemSection +
        cashSection +
        '<div class="footer">Dine with Din Cafeteria &mdash; ' + d.printed_at + '</div>' +
        '</body></html>';

    html = html.replace('</body></html>',
        '<script>' +
        'window.addEventListener(\'load\', function() {' +
        '  setTimeout(function() { window.print(); }, 400);' +
        '});' +
        'window.addEventListener(\'afterprint\', function() {' +
        '  window.close();' +
        '});' +
        '<\/script></body></html>'
    );
    openPrintTab(html);
}

function row(label, value, highlight) {
    return '<div class="row"><span class="lb">' + label + '</span><span class="vl' + (highlight ? ' hi' : '') + '">' + value + '</span></div>';
}
function sep() { return '<hr class="sep">'; }
function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

/* ================================================================
   printA4(contentId, title) — A4 for monthly/yearly/products/staff/audit
   ================================================================ */
function printA4(contentId, title) {
    var el = document.getElementById(contentId);
    if (!el) { alert('Nothing to print.'); return; }

    var html =
        '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' + title + '</title>' +
        '<style>' +
        '@page{size:A4 portrait;margin:12mm 15mm;}' +
        '*{box-sizing:border-box;margin:0;padding:0;}' +
        'body{font-family:Arial,Helvetica,sans-serif;font-size:11px;color:#000;background:#fff;}' +
        '.print-title{font-size:16px;font-weight:bold;text-align:center;margin-bottom:2px;}' +
        '.print-sub{font-size:10px;text-align:center;color:#555;margin-bottom:10px;padding-bottom:8px;border-bottom:2px solid #000;}' +
        /* strip screen-only chrome */
        '.rpt-card{margin-bottom:12px;page-break-inside:avoid;}' +
        '.rpt-card-header{font-size:12px;font-weight:bold;padding:6px 0;border-bottom:1px solid #ccc;margin-bottom:6px;}' +
        '.rpt-card-title i{display:none;}' +
        'table{width:100%;border-collapse:collapse;font-size:10px;margin-bottom:8px;}' +
        'thead th{background:#f0f0f0;border:1px solid #ccc;padding:5px 6px;text-align:left;font-size:9px;text-transform:uppercase;letter-spacing:.5px;}' +
        'tbody td{border:1px solid #ddd;padding:5px 6px;vertical-align:top;}' +
        'tfoot td{border:1px solid #ccc;background:#f0f0f0;padding:5px 6px;font-weight:bold;}' +
        'tbody tr:nth-child(even) td{background:#fafafa;}' +
        '.mini-bar{display:none;}' +
        '.rpt-badge{border:1px solid #999;padding:1px 4px;border-radius:2px;font-size:9px;}' +
        '.no-data{text-align:center;padding:12px;color:#888;font-style:italic;}' +
        'canvas{display:none!important;}' +  /* charts won't render in popup */
        '.s-icon,.s-label,.s-sub{display:none;}' +
        '.stats-grid{display:none;}' +
        '</style></head><body>' +
        '<div class="print-title">Dine with Din Cafeteria</div>' +
        '<div class="print-sub">' + title + ' &nbsp;|&nbsp; Printed: <?= (new DateTime('now', new DateTimeZone('Asia/Manila')))->format('M d, Y h:i A') ?> (PHT)</div>' +
        el.innerHTML +
        '</body></html>';

    html = html.replace('</body></html>',
        '<script>' +
        'window.addEventListener(\'load\', function() {' +
        '  setTimeout(function() { window.print(); }, 400);' +
        '});' +
        'window.addEventListener(\'afterprint\', function() {' +
        '  window.close();' +
        '});' +
        '<\/script></body></html>'
    );
    openPrintTab(html);
}
</script>
</body>
</html>