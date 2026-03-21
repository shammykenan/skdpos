<?php
// Pure view: expects $ingredients, $units (dropdown), $units_raw (full rows),
// $_SESSION['success'], $_SESSION['error']
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5">
    <title>Ingredient Management - Cafeteria POS</title>
    <link rel="icon" type="image/png" href="<?= $baseUrl ?>/assets/images/logo/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary:        #0f4c5c;
            --primary-dark:   #0a3a47;
            --primary-light:  #1f7a8c;
            --secondary:      #ff6b4a;
            --secondary-light:#ff8c70;
            --accent:         #fee440;
            --navy:           #1e2f4e;
            --lavender:       #e2e6f0;
            --light-bg:       #f8faff;
            --warm-gray:      #4a5568;
            --border:         #cbd5e0;
            --success:        #2c7a5a;
            --danger:         #d64045;
            --warning:        #f4b942;
            --info:           #5e9b9e;
            --dark:           #1a202c;
            --light:          #ffffff;
            --sidebar-width:  280px;
            --topbar-height:  72px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--light-bg);
            color: var(--dark);
            font-size: 14px;
            line-height: 1.5;
        }

        /* ── TOPBAR ─────────────────────────────────────────────── */
        .topbar {
            position: fixed; top: 0; left: 0; right: 0;
            height: var(--topbar-height);
            background: white; border-bottom: 1px solid var(--border);
            z-index: 1040; padding: 0 2rem;
            box-shadow: 0 4px 12px rgba(15,76,92,.04);
        }
        .topbar .logo {
            font-size: 1.6rem; font-weight: 700; color: var(--primary-dark);
            text-decoration: none; display: flex; align-items: center; gap: .65rem;
        }
        .topbar .logo i { font-size: 1.9rem; color: var(--secondary); transition: transform .2s; }
        .topbar .logo:hover i { transform: scale(1.05); }
        .topbar .logo span {
            background: linear-gradient(135deg, var(--primary), var(--navy));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        .mobile-menu-btn {
            background: white; border: 1px solid var(--border);
            font-size: 1.4rem; color: var(--primary);
            cursor: pointer; padding: .5rem 1rem; border-radius: 12px; transition: all .2s;
        }
        .mobile-menu-btn:hover { background: var(--secondary); border-color: var(--secondary); color: white; }

        /* ── SIDEBAR ─────────────────────────────────────────────── */
        .sidebar {
            position: fixed; top: var(--topbar-height); left: 0;
            width: var(--sidebar-width);
            height: calc(100vh - var(--topbar-height));
            background: white; border-right: 1px solid var(--border);
            overflow-y: auto;
            transition: transform .25s cubic-bezier(.4,0,.2,1);
            z-index: 1030; box-shadow: 2px 0 12px rgba(0,0,0,.02);
        }
        .sidebar::-webkit-scrollbar { width: 5px; }
        .sidebar::-webkit-scrollbar-thumb { background: var(--border); border-radius: 8px; }
        .sidebar-nav { padding: 2rem .75rem; }
        .nav-section-title {
            padding: .6rem 1.2rem; font-size: .7rem; font-weight: 700;
            text-transform: uppercase; color: var(--warm-gray);
            letter-spacing: 1.2px; opacity: .7; margin-top: .8rem;
        }
        .nav-item { margin: .2rem .6rem; }
        .nav-link {
            display: flex; align-items: center; padding: .8rem 1.2rem;
            color: var(--warm-gray); text-decoration: none; border-radius: 14px;
            transition: all .2s; font-weight: 500; font-size: .95rem;
        }
        .nav-link:hover { background: var(--lavender); color: var(--primary-dark); }
        .nav-link.active { background: var(--primary); color: white; box-shadow: 0 6px 12px rgba(15,76,92,.2); }
        .nav-link i { width: 24px; margin-right: .85rem; font-size: 1.2rem; text-align: center; }

        /* ── MAIN CONTENT ────────────────────────────────────────── */
        .main-content {
            margin-left: var(--sidebar-width); margin-top: var(--topbar-height);
            padding: 2.2rem; min-height: calc(100vh - var(--topbar-height));
            transition: margin-left .2s;
        }

        /* ── PAGE HEADER ─────────────────────────────────────────── */
        .page-header {
            margin-bottom: 2.2rem;
            display: flex; justify-content: space-between; align-items: center;
            flex-wrap: wrap; gap: 1rem;
        }
        .page-header h1 {
            font-size: 2.1rem; font-weight: 700; color: var(--navy);
            margin-bottom: 0; display: flex; align-items: center; gap: .7rem;
        }
        .page-header h1 i { color: var(--secondary); font-size: 2.1rem; }
        .breadcrumb { background: none; padding: .2rem 0; margin: 0; font-size: .9rem; }
        .breadcrumb-item a { color: var(--warm-gray); text-decoration: none; font-weight: 500; }
        .breadcrumb-item a:hover { color: var(--primary); }
        .breadcrumb-item.active { color: var(--primary-dark); font-weight: 600; }

        /* ── HEADER ACTIONS ──────────────────────────────────────── */
        .header-actions { display: flex; align-items: center; gap: .75rem; flex-wrap: wrap; }

        .btn-add {
            background: var(--primary); color: white;
            padding: .7rem 1.8rem; border-radius: 40px;
            font-weight: 600; font-size: .95rem; border: none;
            transition: all .2s; display: flex; align-items: center; gap: .6rem;
            cursor: pointer; text-decoration: none;
        }
        .btn-add:hover {
            background: var(--primary-dark); transform: translateY(-2px);
            box-shadow: 0 10px 18px rgba(15,76,92,.25); color: white;
        }

        .btn-add-outline {
            background: white; color: var(--primary);
            padding: .7rem 1.6rem; border-radius: 40px;
            font-weight: 600; font-size: .95rem; border: 2px solid var(--primary);
            transition: all .2s; display: flex; align-items: center; gap: .6rem;
            cursor: pointer;
        }
        .btn-add-outline:hover {
            background: var(--primary); color: white;
            transform: translateY(-2px); box-shadow: 0 10px 18px rgba(15,76,92,.18);
        }

        /* ── FILTER TABS ─────────────────────────────────────────── */
        .filter-label {
            font-weight: 600; color: var(--navy); margin-bottom: .75rem;
            font-size: .85rem; display: flex; align-items: center; gap: .5rem;
        }
        .filter-tabs { display: flex; gap: .65rem; flex-wrap: wrap; }
        .filter-tab {
            padding: .6rem 1.2rem; border: 1px solid var(--border);
            background: white; color: var(--warm-gray); border-radius: 40px;
            cursor: pointer; transition: all .2s; font-weight: 600;
            font-size: .85rem; display: flex; align-items: center; gap: .5rem;
        }
        .filter-tab i { font-size: .9rem; }
        .filter-tab:hover { border-color: var(--primary); color: var(--primary); background: #f2f6fc; }
        .filter-tab.active { background: var(--primary); color: white; border-color: var(--primary); }
        .filter-count {
            background: rgba(0,0,0,.12); padding: .15rem .6rem;
            border-radius: 30px; font-size: .7rem; font-weight: 700; margin-left: .25rem;
        }
        .filter-tab.active .filter-count { background: rgba(255,255,255,.3); }

        /* ── CARD ────────────────────────────────────────────────── */
        .card {
            background: white; border-radius: 24px;
            border: 1px solid rgba(203,213,224,.5);
            box-shadow: 0 8px 20px rgba(15,76,92,.02);
            margin-bottom: 1.8rem; transition: box-shadow .2s;
        }
        .card:hover { box-shadow: 0 14px 26px rgba(15,76,92,.06); }
        .card-header {
            background: white; border-bottom: 1px solid rgba(203,213,224,.5);
            padding: 1.35rem 1.8rem; border-radius: 24px 24px 0 0;
        }
        .card-body { padding: 1.6rem 1.8rem; }
        .card-title {
            font-size: 1.18rem; font-weight: 650; color: var(--navy);
            display: flex; align-items: center; gap: .6rem;
        }

        /* ── TABLE ───────────────────────────────────────────────── */
        .table-container { overflow-x: auto; margin: 0; }
        .table { margin: 0; border-collapse: separate; border-spacing: 0; }
        .table thead th {
            background: #f2f6fc; border-bottom: 2px solid var(--primary-light);
            font-weight: 600; font-size: .78rem; text-transform: uppercase;
            letter-spacing: .8px; color: var(--primary-dark); padding: 1.1rem 1.2rem; white-space: nowrap;
        }
        .table tbody td {
            padding: 1.2rem; vertical-align: middle;
            border-bottom: 1px solid #edf2f7; color: var(--dark); background: white;
        }
        .table tbody tr:hover td { background-color: #fafcff; }

        /* ── BADGES ──────────────────────────────────────────────── */
        .badge { padding: .45rem 1rem; border-radius: 30px; font-weight: 600; font-size: .75rem; letter-spacing: .3px; }
        .bg-success  { background: var(--success)  !important; }
        .bg-danger   { background: var(--danger)   !important; }
        .bg-warning  { background: var(--warning)  !important; color: #1a202c; }
        .bg-secondary{ background: var(--lavender) !important; color: var(--navy); }

        .stock-badge { padding: .45rem 1rem; border-radius: 30px; font-size: .75rem; font-weight: 600; display: inline-block; }
        .stock-ok       { background: rgba(44,122,90,.12);  color: var(--success); }
        .stock-low      { background: rgba(244,185,66,.12); color: #9c6f1a; }
        .stock-critical { background: rgba(214,64,69,.08);  color: var(--danger); }

        /* ── ACTION BUTTONS ──────────────────────────────────────── */
        .action-btns { display: flex; gap: .5rem; justify-content: flex-end; }
        .btn-icon {
            width: 38px; height: 38px; padding: 0; border-radius: 12px;
            display: inline-flex; align-items: center; justify-content: center;
            background: #f2f6fc; border: none; color: var(--primary);
            transition: all .15s; cursor: pointer;
        }
        .btn-icon:hover { background: var(--secondary); color: white; transform: scale(.95); }
        .btn-icon.btn-edit {
            background: #fff3e0; color: #b85a1a; border: 1px solid rgba(244,185,66,.2);
        }
        .btn-icon.btn-edit:hover { background: var(--warning); color: white; }
        .btn-icon.btn-delete {
            background: rgba(214,64,69,.08); color: var(--danger); border: 1px solid rgba(214,64,69,.15);
        }
        .btn-icon.btn-delete:hover { background: var(--danger); color: white; }

        /* ── SEARCH BOX ──────────────────────────────────────────── */
        .search-box { position: relative; max-width: 250px; }
        .search-box i { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--warm-gray); z-index: 10; }
        .search-box .form-control { padding-left: 2.8rem; border-radius: 30px; border: 1px solid var(--border); }

        /* ── MODAL ───────────────────────────────────────────────── */
        .modal { padding-right: 0 !important; }
        .modal-dialog { margin: 1.75rem auto; display: flex; align-items: center; min-height: calc(100% - 3.5rem); }
        .modal-content { border-radius: 28px; border: none; box-shadow: 0 25px 40px rgba(0,0,0,.12); width: 100%; }
        .modal-header { border-bottom: 1px solid var(--border); padding: 1.6rem 1.8rem; border-radius: 28px 28px 0 0; }
        .modal-body { padding: 1.8rem; }
        .modal-footer { border-top: 1px solid var(--border); padding: 1.5rem 1.8rem; border-radius: 0 0 28px 28px; }
        .modal-title { font-size: 1.3rem; font-weight: 700; color: var(--navy); display: flex; align-items: center; gap: .6rem; }

        /* ── FORM ────────────────────────────────────────────────── */
        .form-label { font-weight: 600; color: var(--navy); margin-bottom: .5rem; font-size: .85rem; }
        .form-control, .form-select {
            border: 1px solid var(--border); border-radius: 14px;
            padding: .65rem 1rem; font-size: .9rem; transition: all .2s;
        }
        .form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(15,76,92,.1); }
        .input-group-text { background: #f2f6fc; border: 1px solid var(--border); border-radius: 14px 0 0 14px; color: var(--primary-dark); font-weight: 500; }

        /* ── UNIT MODAL – icon picker ────────────────────────────── */
        .unit-modal-icon {
            width: 52px; height: 52px; border-radius: 16px;
            background: linear-gradient(135deg, rgba(15,76,92,.1), rgba(31,122,140,.08));
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem; color: var(--primary); flex-shrink: 0;
        }
        .char-counter { font-size: .75rem; color: var(--warm-gray); text-align: right; margin-top: .3rem; }
        .char-counter.over { color: var(--danger); font-weight: 600; }

        /* ── ALERTS ──────────────────────────────────────────────── */
        .alert { border-radius: 18px; border: none; padding: 1rem 1.5rem; margin-bottom: 1.8rem; }
        .alert-success { background: rgba(44,122,90,.12); color: #1e4a3b; border-left: 6px solid var(--success); }
        .alert-danger  { background: rgba(214,64,69,.08); color: #9e3a34; border-left: 6px solid var(--danger); }

        /* ── EMPTY STATE ─────────────────────────────────────────── */
        .empty-state { text-align: center; padding: 3rem 1rem; }
        .empty-state i { font-size: 4rem; color: var(--border); margin-bottom: 1rem; }
        .empty-state h5 { color: var(--warm-gray); margin-bottom: .5rem; }
        .empty-state p  { color: var(--border); }

        /* ── SIDEBAR OVERLAY ─────────────────────────────────────── */
        .sidebar-overlay {
            position: fixed; top: var(--topbar-height); left: 0; right: 0; bottom: 0;
            background: rgba(15,76,92,.2); backdrop-filter: blur(3px);
            z-index: 1025; display: none;
        }
        .sidebar-overlay.show { display: block; }

        /* ── RESPONSIVE ──────────────────────────────────────────── */
        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; padding: 1.8rem; }
        }
        @media (max-width: 767.98px) {
            .main-content { padding: 1.2rem; }
            .page-header h1 { font-size: 1.8rem; }
            .header-actions { width: 100%; }
            .btn-add, .btn-add-outline { flex: 1; justify-content: center; }
            .table thead { display: none; }
            .table tbody tr { display: block; margin-bottom: 1.2rem; border: 1px solid var(--border); border-radius: 20px; background: white; }
            .table tbody td { display: flex; justify-content: space-between; align-items: center; padding: .9rem 1.2rem; border-bottom: 1px solid #edf2f7; }
            .table tbody td:last-child { border-bottom: none; }
            .table tbody td::before { content: attr(data-label); font-weight: 700; color: var(--warm-gray); font-size: .75rem; text-transform: uppercase; letter-spacing: .5px; }
            .search-box { max-width: 100%; }
            .modal-dialog { margin: .5rem; min-height: calc(100% - 1rem); }
            .filter-tabs { flex-direction: column; }
            .filter-tab { width: 100%; justify-content: center; }
        }
        @media (max-width: 480px) {
            .topbar .logo span { display: none; }
            .topbar { padding: 0 1rem; }
            .main-content { padding: 1rem; }
        }

        /* ── ANIMATION ───────────────────────────────────────────── */
        .fade-in { animation: modernFade .4s ease forwards; }
        @keyframes modernFade {
            from { opacity: .6; transform: translateY(6px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <button class="mobile-menu-btn d-lg-none" id="menuToggle"><i class="fas fa-bars"></i></button>
        <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="logo d-none d-sm-flex">
            <i class="fas fa-utensils"></i><span>Dine with Din - Cafeteria</span>
        </a>
        <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="logo d-sm-none">
            <i class="fas fa-utensils"></i>
        </a>
    </div>
    <div class="dropdown">
        <button class="btn btn-link text-decoration-none dropdown-toggle d-flex align-items-center"
                type="button" data-bs-toggle="dropdown" style="color:var(--navy);font-weight:600;">
            <i class="far fa-user-circle fa-xl me-2" style="font-size:1.6rem;color:var(--secondary);"></i>
            <span class="d-none d-md-inline"><?= htmlspecialchars($_SESSION['full_name'] ?? 'Admin') ?></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="<?= $baseUrl ?>/index.php?page=logout"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
        </ul>
    </div>
</div>

<!-- Sidebar Overlay -->
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
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_ingredients" class="nav-link active"><i class="fas fa-boxes"></i> Ingredients</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_addons" class="nav-link"><i class="fas fa-plus-circle"></i> Add-ons</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_tables" class="nav-link"><i class="fas fa-chair"></i> Tables</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_users" class="nav-link"><i class="fas fa-users"></i> User Management</a></div>
        <div class="nav-section-title">SALES</div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_pos" class="nav-link"><i class="fas fa-cash-register"></i> POS System</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_orders" class="nav-link"><i class="fas fa-receipt"></i> Orders</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=reports" class="nav-link"><i class="fas fa-chart-bar"></i> Reports</a></div>
        <?php endif; ?>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">

    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1><i class="fas fa-boxes me-2"></i> Ingredient Management</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/index.php?page=admin_dashboard">Home</a></li>
                    <li class="breadcrumb-item active">Ingredients</li>
                </ol>
            </nav>
        </div>
        <div class="header-actions">
            <button type="button" class="btn-add-outline" data-bs-toggle="modal" data-bs-target="#manageUnitsModal">
                <i class="fas fa-ruler-combined"></i> Manage Units
            </button>
            <button type="button" class="btn-add" data-bs-toggle="modal" data-bs-target="#addIngredientModal">
                <i class="fas fa-plus-circle"></i> Add Ingredient
            </button>
        </div>
    </div>

    <!-- Alerts -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show fade-in" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show fade-in" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- FILTER CARD -->
    <div class="card fade-in mb-4">
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="filter-label"><i class="fas fa-flask"></i> Filter by Unit</div>
                    <div class="filter-tabs" id="unitFilterContainer">
                        <button class="filter-tab active" data-filter-type="unit" data-filter-value="all">
                            <i class="fas fa-boxes"></i> All Units
                            <span class="filter-count"><?= count($ingredients ?? []) ?></span>
                        </button>
                        <?php
                        // ingredients now JOINs units, so unit_code / unit_label are direct columns
                        $unit_counts = [];
                        foreach ($ingredients as $ing) {
                            $uc = $ing['unit_code'];
                            $unit_counts[$uc] = ($unit_counts[$uc] ?? 0) + 1;
                        }
                        $unit_icon_map  = array_column($units_raw, 'unit_icon',  'unit_code');
                        $unit_label_map = array_column($units_raw, 'unit_label', 'unit_code');
                        foreach ($unit_counts as $uc => $cnt):
                            $icon  = $unit_icon_map[$uc]  ?? 'fa-box';
                            $label = $unit_label_map[$uc] ?? strtoupper($uc);
                        ?>
                        <button class="filter-tab" data-filter-type="unit" data-filter-value="<?= htmlspecialchars($uc) ?>">
                            <i class="fas <?= htmlspecialchars($icon) ?>"></i> <?= htmlspecialchars($label) ?>
                            <span class="filter-count"><?= $cnt ?></span>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="filter-label"><i class="fas fa-exclamation-triangle"></i> Filter by Status</div>
                    <div class="filter-tabs" id="statusFilterContainer">
                        <button class="filter-tab active" data-filter-type="status" data-filter-value="all">
                            <i class="fas fa-list"></i> All Status
                            <span class="filter-count"><?= count($ingredients ?? []) ?></span>
                        </button>
                        <?php
                        $status_counts = ['critical' => 0, 'low' => 0, 'ok' => 0];
                        foreach ($ingredients as $ing) {
                            if ($ing['stock_quantity'] <= ($ing['low_stock_alert'] * 0.3))  $status_counts['critical']++;
                            elseif ($ing['stock_quantity'] <= $ing['low_stock_alert'])       $status_counts['low']++;
                            else                                                              $status_counts['ok']++;
                        }
                        ?>
                        <button class="filter-tab" data-filter-type="status" data-filter-value="critical">
                            <i class="fas fa-exclamation-circle"></i> Critical
                            <span class="filter-count"><?= $status_counts['critical'] ?></span>
                        </button>
                        <button class="filter-tab" data-filter-type="status" data-filter-value="low">
                            <i class="fas fa-exclamation-triangle"></i> Low Stock
                            <span class="filter-count"><?= $status_counts['low'] ?></span>
                        </button>
                        <button class="filter-tab" data-filter-type="status" data-filter-value="ok">
                            <i class="fas fa-check-circle"></i> In Stock
                            <span class="filter-count"><?= $status_counts['ok'] ?></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- INGREDIENT LIST TABLE -->
    <div class="row">
        <div class="col-12">
            <div class="card fade-in">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list me-2" style="color:var(--primary);"></i>
                            Ingredient List
                            <span id="ingredientCount" class="ms-2">(<?= count($ingredients ?? []) ?>)</span>
                        </h5>
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" class="form-control" id="searchInput" placeholder="Search ingredients...">
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($ingredients)): ?>
                        <div class="empty-state">
                            <i class="fas fa-boxes"></i>
                            <h5>No ingredients found</h5>
                            <p>Click "Add Ingredient" to get started</p>
                        </div>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="table mb-0" id="ingredientsTable">
                                <thead>
                                    <tr>
                                        <th>Ingredient</th>
                                        <th>Stock</th>
                                        <th>Unit</th>
                                        <th>Low Alert</th>
                                        <th>Status</th>
                                        <th>Last Updated</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="ingredientsTableBody">
                                    <?php foreach ($ingredients as $i):
                                        $stockStatus = 'ok';
                                        if ($i['stock_quantity'] <= ($i['low_stock_alert'] * 0.3)) $stockStatus = 'critical';
                                        elseif ($i['stock_quantity'] <= $i['low_stock_alert'])      $stockStatus = 'low';
                                    ?>
                                    <tr data-unit="<?= htmlspecialchars($i['unit_code']) ?>" data-status="<?= $stockStatus ?>">
                                        <td data-label="Ingredient">
                                            <div class="fw-semibold" style="color:var(--navy);"><?= htmlspecialchars($i['ingredient_name']) ?></div>
                                        </td>
                                        <td data-label="Stock"><strong><?= htmlspecialchars($i['stock_quantity']) ?></strong></td>
                                        <td data-label="Unit">
                                            <span class="badge bg-secondary" title="<?= htmlspecialchars($i['unit_label']) ?>">
                                                <?= htmlspecialchars(strtoupper($i['unit_code'])) ?>
                                            </span>
                                        </td>
                                        <td data-label="Low Alert"><?= htmlspecialchars($i['low_stock_alert']) ?></td>
                                        <td data-label="Status">
                                            <?php if ($stockStatus === 'critical'): ?>
                                                <span class="stock-badge stock-critical"><i class="fas fa-exclamation-circle"></i> Critical</span>
                                            <?php elseif ($stockStatus === 'low'): ?>
                                                <span class="stock-badge stock-low"><i class="fas fa-exclamation-triangle"></i> Low Stock</span>
                                            <?php else: ?>
                                                <span class="stock-badge stock-ok"><i class="fas fa-check-circle"></i> In Stock</span>
                                            <?php endif; ?>
                                        </td>
                                        <td data-label="Last Updated">
                                            <small class="text-muted"><?= date('M d, Y - h:i A', strtotime($i['last_updated'])) ?></small>
                                        </td>
                                        <td data-label="Actions" class="text-end">
                                            <div class="action-btns">
                                                <button type="button" class="btn-icon btn-edit"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editModal<?= $i['ingredient_id'] ?>"
                                                        title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn-icon btn-delete"
                                                        onclick="confirmDelete(<?= $i['ingredient_id'] ?>, '<?= htmlspecialchars(addslashes($i['ingredient_name'])) ?>')"
                                                        title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
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
</div><!-- /main-content -->


<!-- ═══════════════════════════════════════════════════
     MODALS
════════════════════════════════════════════════════ -->

<!-- ── Delete Ingredient Confirm ──────────────────── -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom:none;padding-bottom:0;">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center pt-0">
                <div style="width:70px;height:70px;border-radius:22px;background:rgba(214,64,69,.1);color:var(--danger);display:flex;align-items:center;justify-content:center;font-size:2rem;margin:0 auto 1.2rem;">
                    <i class="fas fa-trash"></i>
                </div>
                <h4 style="color:var(--navy);font-weight:700;" class="mb-2">Delete Ingredient?</h4>
                <p class="text-muted mb-1">You are about to delete:</p>
                <p id="deleteIngredientName" style="color:var(--danger);font-size:1.05rem;font-weight:600;" class="mb-3"></p>
                <div class="p-3 rounded-3 mb-1" style="background:rgba(214,64,69,.06);border:1px dashed rgba(214,64,69,.3);">
                    <small class="text-muted">
                        <i class="fas fa-exclamation-triangle me-1" style="color:var(--danger)"></i>
                        This cannot be undone. Ingredients used in recipes or add-ons cannot be deleted.
                    </small>
                </div>
            </div>
            <div class="modal-footer" style="border-top:none;gap:.75rem;">
                <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="index.php?page=manage_ingredients" style="flex:1;">
                    <input type="hidden" name="delete_ingredient" value="1">
                    <input type="hidden" name="ingredient_id" id="deleteIngredientId">
                    <button type="submit" class="btn w-100" style="background:var(--danger);color:white;border:none;border-radius:40px;padding:.6rem;">
                        <i class="fas fa-trash me-2"></i> Yes, Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ── Add Ingredient ──────────────────────────────── -->
<div class="modal fade" id="addIngredientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle me-2" style="color:var(--secondary);"></i>
                    Add New Ingredient
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="index.php?page=manage_ingredients">
                <div class="modal-body">
                    <input type="hidden" name="add_ingredient" value="1">
                    <div class="mb-3">
                        <label class="form-label">Ingredient Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-tag"></i></span>
                            <input type="text" class="form-control" name="name" placeholder="e.g., Coffee Beans" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-boxes"></i></span>
                                <input type="number" class="form-control" name="stock" step="0.01" min="0" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Unit <span class="text-danger">*</span></label>
                            <select class="form-select" name="unit_id" required>
                                <option value="">-- Select Unit --</option>
                                <?php foreach ($units as $uid => $ulabel): ?>
                                    <option value="<?= htmlspecialchars($uid) ?>"><?= htmlspecialchars($ulabel) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Low Stock Alert</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-exclamation-triangle"></i></span>
                            <input type="number" class="form-control" name="low_stock_alert" value="10" min="0">
                        </div>
                        <small class="text-muted">Alert when stock falls below this level</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-2"></i> Add Ingredient</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Edit Ingredient Modals (one per row) ─────────── -->
<?php foreach ($ingredients as $i): ?>
<div class="modal fade" id="editModal<?= $i['ingredient_id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2" style="color:var(--warning);"></i> Edit Ingredient
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="index.php?page=manage_ingredients">
                <div class="modal-body">
                    <input type="hidden" name="edit_ingredient" value="1">
                    <input type="hidden" name="ingredient_id" value="<?= $i['ingredient_id'] ?>">
                    <div class="mb-3">
                        <label class="form-label">Ingredient Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-tag"></i></span>
                            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($i['ingredient_name']) ?>" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-boxes"></i></span>
                                <input type="number" step="0.01" class="form-control" name="stock" value="<?= htmlspecialchars($i['stock_quantity']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Unit <span class="text-danger">*</span></label>
                            <select class="form-select" name="unit_id" required>
                                <?php foreach ($units as $uid => $ulabel): ?>
                                    <option value="<?= htmlspecialchars($uid) ?>" <?= (int)$i['unit_id'] === (int)$uid ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($ulabel) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Low Stock Alert</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-exclamation-triangle"></i></span>
                            <input type="number" class="form-control" name="low_stock_alert" value="<?= htmlspecialchars($i['low_stock_alert']) ?>" min="0">
                        </div>
                    </div>
                    <small class="text-muted"><i class="fas fa-clock me-1"></i> Last updated: <?= date('M d, Y - h:i A', strtotime($i['last_updated'])) ?></small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" style="background:var(--warning);color:white;border:none;">
                        <i class="fas fa-save me-2"></i> Update Ingredient
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<!-- ══════════════════════════════════════════════════════════
     MANAGE UNITS MODAL  — mirrors the Add Category modal
═══════════════════════════════════════════════════════════ -->
<div class="modal fade" id="manageUnitsModal" tabindex="-1" aria-labelledby="manageUnitsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="manageUnitsModalLabel">
                    <div class="unit-modal-icon me-2">
                        <i class="fas fa-ruler-combined"></i>
                    </div>
                    Manage Units
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" action="index.php?page=manage_ingredients" id="addUnitForm" novalidate>
                <div class="modal-body">
                    <input type="hidden" name="add_unit" value="1">

                    <!-- Unit Code -->
                    <div class="mb-3">
                        <label for="modal_unit_code" class="form-label">
                            Unit Code <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-code"></i></span>
                            <input type="text" class="form-control" id="modal_unit_code" name="unit_code"
                                   placeholder="e.g. kg, ml, pcs" maxlength="20"
                                   autocomplete="off" required>
                        </div>
                        <div class="char-counter" id="unitCodeCounter">0 / 20</div>
                        <div class="invalid-feedback">Please enter a unit code.</div>
                    </div>

                    <!-- Unit Label -->
                    <div class="mb-3">
                        <label for="modal_unit_label" class="form-label">
                            Unit Label <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-font"></i></span>
                            <input type="text" class="form-control" id="modal_unit_label" name="unit_label"
                                   placeholder="e.g. Kilogram, Milliliter" maxlength="50"
                                   autocomplete="off" required>
                        </div>
                        <div class="invalid-feedback">Please enter a unit label.</div>
                    </div>

                    <!-- Duplicate warning -->
                    <div id="unitDupWarning" class="alert alert-danger py-2 px-3 mt-3 mb-0 d-none" role="alert" style="font-size:.82rem;">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        A unit with this code already exists.
                    </div>

                    <!-- ── Existing Units List ──────────────────── -->
                    <div class="mt-4">
                        <div class="form-label mb-2">Existing Units</div>
                        <?php if (empty($units_raw)): ?>
                            <p class="text-muted fst-italic" style="font-size:.85rem;">No units yet.</p>
                        <?php else: ?>
                            <div style="max-height:220px;overflow-y:auto;border:1px solid var(--border);border-radius:14px;">
                                <?php foreach ($units_raw as $u): ?>
                                <div class="d-flex align-items-center justify-content-between px-3 py-2"
                                     style="border-bottom:1px solid #edf2f7;">
                                    <div class="d-flex align-items-center gap-2">
                                        <span style="font-size:.88rem;color:var(--navy);font-weight:500;">
                                            <?= htmlspecialchars($u['unit_label']) ?>
                                        </span>
                                        <span class="badge bg-secondary" style="font-size:.68rem;">
                                            <?= htmlspecialchars($u['unit_code']) ?>
                                        </span>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <!-- Edit -->
                                        <button type="button"
                                                class="btn-icon btn-edit"
                                                style="width:30px;height:30px;font-size:.75rem;"
                                                title="Edit"
                                                onclick="unitEditInline(<?= $u['unit_id'] ?>, <?= htmlspecialchars(json_encode($u['unit_code'])) ?>, <?= htmlspecialchars(json_encode($u['unit_label'])) ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <!-- Delete -->
                                        <form method="POST" action="index.php?page=manage_ingredients" style="margin:0;"
                                              onsubmit="return confirm('Delete unit \'<?= htmlspecialchars(addslashes($u['unit_label'])) ?>\'? This cannot be undone.')">
                                            <input type="hidden" name="delete_unit" value="1">
                                            <input type="hidden" name="unit_id" value="<?= $u['unit_id'] ?>">
                                            <button type="submit"
                                                    class="btn-icon"
                                                    style="width:30px;height:30px;font-size:.75rem;background:#fff0f0;color:var(--danger);border:1px solid rgba(214,64,69,.2);"
                                                    title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- ── Inline Edit Section ──────────────────── -->
                    <div id="unitEditSection" class="mt-3 p-3 d-none"
                         style="background:#f8faff;border:1px solid var(--border);border-radius:14px;">
                        <form method="POST" action="index.php?page=manage_ingredients">
                            <input type="hidden" name="edit_unit" value="1">
                            <input type="hidden" name="unit_id" id="editUnitId">
                            <label class="form-label mb-2" style="font-size:.82rem;">Edit Unit</label>
                            <div class="row g-2 mb-2">
                                <div class="col-4">
                                    <input type="text" class="form-control form-control-sm" name="unit_code"
                                           id="editUnitCode" placeholder="Code" maxlength="20" required
                                           style="border-radius:10px;">
                                    <small class="text-muted" style="font-size:.7rem;">Code</small>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control form-control-sm" name="unit_label"
                                           id="editUnitLabel" placeholder="Label" required
                                           style="border-radius:10px;">
                                    <small class="text-muted" style="font-size:.7rem;">Label</small>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm px-3" style="border-radius:10px;white-space:nowrap;">
                                    <i class="fas fa-save me-1"></i> Save
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm px-3"
                                        style="border-radius:10px;"
                                        onclick="document.getElementById('unitEditSection').classList.add('d-none')">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>

                </div><!-- /modal-body -->

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveUnitBtn">
                        <i class="fas fa-plus me-2"></i> Add Unit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// ── Mobile sidebar ──────────────────────────────────────────────────
const menuToggle     = document.getElementById('menuToggle');
const sidebar        = document.getElementById('sidebar');
const sidebarOverlay = document.getElementById('sidebarOverlay');
menuToggle?.addEventListener('click', () => { sidebar.classList.toggle('show'); sidebarOverlay.classList.toggle('show'); });
sidebarOverlay?.addEventListener('click', () => { sidebar.classList.remove('show'); sidebarOverlay.classList.remove('show'); });

// ── Ingredient filtering ────────────────────────────────────────────
let currentUnitFilter = 'all', currentStatusFilter = 'all', currentSearchTerm = '';

document.querySelectorAll('#unitFilterContainer .filter-tab').forEach(tab => {
    tab.addEventListener('click', function () {
        document.querySelectorAll('#unitFilterContainer .filter-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        currentUnitFilter = this.dataset.filterValue;
        filterTable();
    });
});
document.querySelectorAll('#statusFilterContainer .filter-tab').forEach(tab => {
    tab.addEventListener('click', function () {
        document.querySelectorAll('#statusFilterContainer .filter-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        currentStatusFilter = this.dataset.filterValue;
        filterTable();
    });
});
document.getElementById('searchInput')?.addEventListener('input', function () {
    currentSearchTerm = this.value.toLowerCase();
    filterTable();
});
function filterTable() {
    let visible = 0;
    document.querySelectorAll('#ingredientsTableBody tr').forEach(row => {
        const show = (currentUnitFilter   === 'all' || row.dataset.unit   === currentUnitFilter)
                  && (currentStatusFilter === 'all' || row.dataset.status === currentStatusFilter)
                  && (currentSearchTerm  === ''    || row.textContent.toLowerCase().includes(currentSearchTerm));
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    const el = document.getElementById('ingredientCount');
    if (el) el.textContent = `(${visible})`;
}

// ── Delete ingredient confirm ───────────────────────────────────────
function confirmDelete(id, name) {
    document.getElementById('deleteIngredientId').value        = id;
    document.getElementById('deleteIngredientName').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteConfirmModal')).show();
}

// ── Manage Units Modal ──────────────────────────────────────────────
(function () {
    const codeInput    = document.getElementById('modal_unit_code');
    const labelInput   = document.getElementById('modal_unit_label');
    const codeCounter  = document.getElementById('unitCodeCounter');
    const dupWarning   = document.getElementById('unitDupWarning');
    const saveBtn      = document.getElementById('saveUnitBtn');
    const addUnitForm  = document.getElementById('addUnitForm');

    // Existing codes for duplicate check (PHP → JS)
    const existingCodes = new Set(
        <?php echo json_encode(array_column($units_raw, 'unit_code')); ?>
    );

    // Code counter + duplicate check
    codeInput?.addEventListener('input', function () {
        const len = this.value.length;
        codeCounter.textContent = `${len} / 20`;
        codeCounter.classList.toggle('over', len >= 20);
        const isDup = existingCodes.has(this.value.trim().toLowerCase());
        dupWarning.classList.toggle('d-none', !isDup);
        codeInput.classList.remove('is-invalid');
    });

    // Save button click
    saveBtn?.addEventListener('click', function () {
        const code  = codeInput.value.trim();
        const label = labelInput.value.trim();

        if (!code) { codeInput.classList.add('is-invalid'); codeInput.focus(); return; }
        if (!label) { labelInput.classList.add('is-invalid'); labelInput.focus(); return; }
        if (existingCodes.has(code.toLowerCase())) { dupWarning.classList.remove('d-none'); codeInput.focus(); return; }

        codeInput.classList.remove('is-invalid');
        labelInput.classList.remove('is-invalid');
        addUnitForm.submit();
    });

    // Enter key support
    [codeInput, labelInput].forEach(el => {
        el?.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); saveBtn.click(); } });
    });

    // Reset form on close
    document.getElementById('manageUnitsModal')?.addEventListener('hidden.bs.modal', function () {
        addUnitForm.reset();
        codeCounter.textContent = '0 / 20';
        codeCounter.classList.remove('over');
        dupWarning.classList.add('d-none');
        codeInput.classList.remove('is-invalid');
        labelInput.classList.remove('is-invalid');
        document.getElementById('unitEditSection').classList.add('d-none');
    });
})();

// ── Unit inline edit ────────────────────────────────────────────────
function unitEditInline(id, code, label) {
    document.getElementById('editUnitId').value    = id;
    document.getElementById('editUnitCode').value  = code;
    document.getElementById('editUnitLabel').value = label;
    document.getElementById('unitEditSection').classList.remove('d-none');
    document.getElementById('editUnitCode').focus();
}

// ── Auto-dismiss alerts ─────────────────────────────────────────────
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(a => { try { new bootstrap.Alert(a).close(); } catch {} });
}, 5000);

// ── Modal backdrop cleanup ──────────────────────────────────────────
document.addEventListener('hidden.bs.modal', function () {
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.classList.remove('modal-open');
    document.body.style.overflow = document.body.style.paddingRight = '';
});
</script>
</body>
</html>