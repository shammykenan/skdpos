<?php
// Pure view: expects $addons, $ingredients, $_SESSION['success'], $_SESSION['error']
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5">
    <title>Add-ons Management - Cafeteria POS</title>
    <link rel="icon" type="image/png" href="<?= $baseUrl ?>/assets/images/logo/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #0f4c5c; --primary-dark: #0a3a47; --primary-light: #1f7a8c;
            --secondary: #ff6b4a; --navy: #1e2f4e; --lavender: #e2e6f0;
            --light-bg: #f8faff; --warm-gray: #4a5568; --border: #cbd5e0;
            --success: #2c7a5a; --danger: #d64045; --warning: #f4b942;
            --dark: #1a202c; --sidebar-width: 280px; --topbar-height: 72px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--light-bg); color: var(--dark); font-size: 14px; line-height: 1.5; }

        /* ── TOPBAR ── */
        .topbar { position: fixed; top: 0; left: 0; right: 0; height: var(--topbar-height); background: white; border-bottom: 1px solid var(--border); z-index: 1040; padding: 0 2rem; box-shadow: 0 4px 12px rgba(15,76,92,0.04); }
        .logo { font-size: 1.6rem; font-weight: 700; color: var(--primary-dark); text-decoration: none; display: flex; align-items: center; gap: 0.65rem; }
        .logo i { font-size: 1.9rem; color: var(--secondary); }
        .logo span { background: linear-gradient(135deg, var(--primary), var(--navy)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .mobile-menu-btn { background: white; border: 1px solid var(--border); font-size: 1.4rem; color: var(--primary); cursor: pointer; padding: 0.5rem 1rem; border-radius: 12px; transition: all 0.2s; }
        .mobile-menu-btn:hover { background: var(--secondary); border-color: var(--secondary); color: white; }

        /* ── SIDEBAR ── */
        .sidebar { position: fixed; top: var(--topbar-height); left: 0; width: var(--sidebar-width); height: calc(100vh - var(--topbar-height)); background: white; border-right: 1px solid var(--border); overflow-y: auto; transition: transform 0.25s cubic-bezier(0.4,0,0.2,1); z-index: 1030; }
        .sidebar::-webkit-scrollbar { width: 5px; }
        .sidebar::-webkit-scrollbar-thumb { background: var(--border); border-radius: 8px; }
        .sidebar-nav { padding: 2rem 0.75rem; }
        .nav-section-title { padding: 0.6rem 1.2rem; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: var(--warm-gray); letter-spacing: 1.2px; opacity: 0.7; margin-top: 0.8rem; }
        .nav-item { margin: 0.2rem 0.6rem; }
        .nav-link { display: flex; align-items: center; padding: 0.8rem 1.2rem; color: var(--warm-gray); text-decoration: none; border-radius: 14px; transition: all 0.2s; font-weight: 500; font-size: 0.95rem; }
        .nav-link:hover { background: var(--lavender); color: var(--primary-dark); }
        .nav-link.active { background: var(--primary); color: white; box-shadow: 0 6px 12px rgba(15,76,92,0.2); }
        .nav-link i { width: 24px; margin-right: 0.85rem; font-size: 1.2rem; text-align: center; }
        .sidebar-overlay { position: fixed; top: var(--topbar-height); left: 0; right: 0; bottom: 0; background: rgba(15,76,92,0.2); backdrop-filter: blur(3px); z-index: 1025; display: none; }
        .sidebar-overlay.show { display: block; }

        /* ── MAIN ── */
        .main-content { margin-left: var(--sidebar-width); margin-top: var(--topbar-height); padding: 2.2rem; min-height: calc(100vh - var(--topbar-height)); }

        .page-header { margin-bottom: 2.2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
        .page-header h1 { font-size: 2.1rem; font-weight: 700; color: var(--navy); display: flex; align-items: center; gap: 0.7rem; }
        .page-header h1 i { color: var(--secondary); }
        .breadcrumb { background: none; padding: 0.2rem 0; margin: 0; font-size: 0.9rem; }
        .breadcrumb-item a { color: var(--warm-gray); text-decoration: none; font-weight: 500; }
        .breadcrumb-item.active { color: var(--primary-dark); font-weight: 600; }

        .btn-add { background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: white; padding: 0.7rem 1.8rem; border-radius: 40px; font-weight: 600; font-size: 0.95rem; border: none; transition: all 0.3s ease; display: flex; align-items: center; gap: 0.6rem; cursor: pointer; box-shadow: 0 4px 15px rgba(15,76,92,0.2); }
        .btn-add:hover { background: linear-gradient(135deg, var(--primary-dark), var(--primary)); transform: translateY(-2px); box-shadow: 0 8px 25px rgba(15,76,92,0.35); color: white; }

        .btn-assign { background: linear-gradient(135deg, var(--secondary), #ff8a65); color: white; padding: 0.7rem 1.8rem; border-radius: 40px; font-weight: 600; font-size: 0.95rem; border: none; transition: all 0.3s ease; display: flex; align-items: center; gap: 0.6rem; cursor: pointer; box-shadow: 0 4px 15px rgba(255,107,74,0.2); }
        .btn-assign:hover { background: linear-gradient(135deg, #e55a3b, var(--secondary)); transform: translateY(-2px); box-shadow: 0 8px 25px rgba(255,107,74,0.35); color: white; }

        /* ── CARD ── */
        .card { background: white; border-radius: 24px; border: 1px solid rgba(203,213,224,0.5); box-shadow: 0 8px 20px rgba(15,76,92,0.02); margin-bottom: 1.8rem; }
        .card-header { background: white; border-bottom: 1px solid rgba(203,213,224,0.5); padding: 1.35rem 1.8rem; border-radius: 24px 24px 0 0; }
        .card-title { font-size: 1.1rem; font-weight: 700; color: var(--navy); display: flex; align-items: center; gap: 0.6rem; margin: 0; }

        /* ── TABLE ── */
        .table-container { overflow-x: auto; }
        .table { margin: 0; border-collapse: separate; border-spacing: 0; }
        .table thead th { background: #f2f6fc; border-bottom: 2px solid var(--primary-light); font-weight: 600; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.8px; color: var(--primary-dark); padding: 1rem 1.2rem; white-space: nowrap; }
        .table tbody td { padding: 1.1rem 1.2rem; vertical-align: middle; border-bottom: 1px solid #edf2f7; background: white; }
        .table tbody tr:hover td { background: #fafcff; }

        /* Inactive rows — dimmed */
        .row-inactive td { opacity: 0.42; }
        .row-inactive:hover td { opacity: 0.65; }

        /* ── NAME CELL ── */
        .addon-name { font-weight: 600; color: var(--navy); font-size: 0.9rem; line-height: 1.3; }
        .addon-status-pill { display: inline-flex; align-items: center; gap: 0.25rem; margin-top: 0.22rem; padding: 0.13rem 0.52rem; border-radius: 20px; font-size: 0.69rem; font-weight: 600; }
        .pill-active   { background: rgba(44,122,90,0.12);   color: var(--success); }
        .pill-inactive { background: rgba(100,116,139,0.12); color: var(--warm-gray); }

        /* ── INGREDIENT BADGE ── */
        .ingredient-badge { display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.28rem 0.7rem; border-radius: 20px; background: var(--lavender); color: var(--navy); font-size: 0.78rem; font-weight: 600; }

        /* ── PRICE ── */
        .price-display { font-size: 1rem; font-weight: 700; color: var(--success); }

        /* ── ICON ACTION BUTTONS ── */
        .action-btns { display: flex; gap: 0.4rem; justify-content: flex-end; }
        .btn-icon { width: 36px; height: 36px; padding: 0; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; border: none; font-size: 0.82rem; cursor: pointer; transition: all 0.15s; }
        .btn-edit   { background: #fff3e0; color: #b85a1a; border: 1px solid rgba(244,185,66,0.25); }
        .btn-edit:hover   { background: var(--warning); color: white; }
        .btn-delete { background: #fff5f3; color: var(--danger); border: 1px solid rgba(214,64,69,0.2); }
        .btn-delete:hover { background: var(--danger); color: white; }

        /* ── FILTER TABS ── */
        .filter-tabs { display: flex; gap: 0.4rem; flex-wrap: wrap; }
        .filter-tab { padding: 0.4rem 1.1rem; border-radius: 30px; border: 1.5px solid var(--border); background: white; font-size: 0.82rem; font-weight: 600; color: var(--warm-gray); cursor: pointer; transition: all 0.18s; display: flex; align-items: center; gap: 0.35rem; }
        .filter-tab:hover { border-color: var(--primary); color: var(--primary); }
        .filter-tab.active { background: var(--primary); border-color: var(--primary); color: white; }
        .filter-tab .tab-count { background: rgba(255,255,255,0.25); border-radius: 20px; padding: 0.05rem 0.45rem; font-size: 0.72rem; }
        .filter-tab:not(.active) .tab-count { background: var(--lavender); color: var(--navy); }

        /* ── SEARCH ── */
        .search-box { position: relative; max-width: 240px; }
        .search-box i { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--warm-gray); z-index: 10; font-size: 0.85rem; pointer-events: none; }
        .search-box input { padding: 0.5rem 0.9rem 0.5rem 2.6rem; border-radius: 30px; border: 1.5px solid var(--border); font-size: 0.85rem; width: 100%; outline: none; transition: border-color 0.2s; background: white; }
        .search-box input:focus { border-color: var(--primary); }

        /* ── ALERTS ── */
        .alert { border-radius: 16px; border: none; padding: 0.9rem 1.4rem; margin-bottom: 1.5rem; }
        .alert-success { background: rgba(44,122,90,0.1); color: #1e4a3b; border-left: 5px solid var(--success); }
        .alert-danger  { background: rgba(214,64,69,0.08); color: #9e3a34; border-left: 5px solid var(--danger); }

        /* ── EMPTY STATE ── */
        .empty-state { text-align: center; padding: 3.5rem 1rem; }
        .empty-state i { font-size: 3.5rem; color: #d0d8e4; display: block; margin-bottom: 1rem; }
        .empty-state h5 { color: var(--warm-gray); margin-bottom: 0.4rem; }
        .empty-state p  { color: #b0bec5; font-size: 0.88rem; }

        /* ── MODALS ── */
        .modal { padding-right: 0 !important; }
        .modal-content { border-radius: 24px; border: none; box-shadow: 0 25px 50px rgba(0,0,0,0.15); backdrop-filter: blur(10px); }
        .modal-header  { border-bottom: 1px solid var(--border); padding: 1.6rem 2rem; border-radius: 24px 24px 0 0; background: linear-gradient(135deg, #fafbff, #f8faff); }
        .modal-body    { padding: 2rem; background: #ffffff; }
        .modal-footer  { border-top: 1px solid var(--border); padding: 1.5rem 2rem; border-radius: 0 0 24px 24px; background: #fafbff; }
        .modal-title   { font-size: 1.15rem; font-weight: 700; color: var(--navy); display: flex; align-items: center; gap: 0.6rem; margin: 0; }

        .form-label { font-weight: 600; color: var(--navy); margin-bottom: 0.5rem; font-size: 0.85rem; display: block; }
        .form-control, .form-select { 
            border: 2px solid var(--border); 
            border-radius: 14px; 
            padding: 0.7rem 1rem; 
            font-size: 0.9rem; 
            transition: all 0.3s ease; 
            width: 100%; 
            background: #ffffff;
        }
        .form-control:focus, .form-select:focus { 
            border-color: var(--primary); 
            box-shadow: 0 0 0 4px rgba(15,76,92,0.15); 
            outline: none; 
            transform: translateY(-1px);
        }
        .input-group-text { 
            background: linear-gradient(135deg, #f2f6fc, #e8f0f7); 
            border: 2px solid var(--border); 
            border-radius: 14px 0 0 14px; 
            color: var(--primary-dark); 
            font-weight: 600; 
            padding: 0.7rem 1rem; 
            transition: all 0.3s ease;
        }
        .input-group .form-control, .input-group .form-select { 
            border-radius: 0 14px 14px 0; 
            border-left: 2px solid var(--border);
        }
        .input-group:focus-within .input-group-text {
            border-color: var(--primary);
            border-right: 2px solid var(--primary);
        }
        .input-group .form-control:focus, .input-group .form-select:focus {
            border-color: var(--primary);
            border-left: 2px solid var(--primary);
            border-radius: 0 14px 14px 0;
        }

        /* Enhanced checkboxes */
        .form-check { padding: 0.8rem; border-radius: 12px; transition: all 0.2s ease; }
        .form-check:hover { background: #f8faff; }
        .form-check-input { 
            width: 20px; height: 20px; 
            border: 2px solid var(--primary); 
            border-radius: 6px; 
            transition: all 0.2s ease;
        }
        .form-check-input:checked { 
            background: var(--primary); 
            border-color: var(--primary);
            box-shadow: 0 2px 8px rgba(15,76,92,0.3);
        }
        .form-check-label { 
            font-weight: 500; 
            color: var(--dark); 
            margin-left: 0.5rem;
            cursor: pointer;
        }

        /* Modal buttons */
        .btn-primary { 
            background: linear-gradient(135deg, var(--primary), var(--primary-light)); 
            border: none; 
            border-radius: 12px; 
            padding: 0.7rem 1.5rem; 
            font-weight: 600; 
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(15,76,92,0.2);
        }
        .btn-primary:hover { 
            background: linear-gradient(135deg, var(--primary-dark), var(--primary)); 
            transform: translateY(-2px); 
            box-shadow: 0 8px 25px rgba(15,76,92,0.35);
        }
        .btn-outline-secondary { 
            border: 2px solid var(--border); 
            color: var(--warm-gray); 
            border-radius: 12px; 
            padding: 0.7rem 1.5rem; 
            font-weight: 600; 
            transition: all 0.3s ease;
            background: white;
        }
        .btn-outline-secondary:hover { 
            border-color: var(--primary); 
            color: var(--primary); 
            background: #f8faff;
            transform: translateY(-1px);
        }

        /* delete modal */
        .delete-icon-wrap { width: 60px; height: 60px; border-radius: 18px; background: rgba(214,64,69,0.1); display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 1.5rem; color: var(--danger); }
        .info-box { background: rgba(15,76,92,0.05); border: 1px solid rgba(15,76,92,0.15); border-radius: 12px; padding: 0.65rem 0.9rem; font-size: 0.79rem; color: var(--primary-dark); display: flex; gap: 0.45rem; align-items: flex-start; margin-top: 0.85rem; text-align: left; }
        .info-box i { margin-top: 2px; flex-shrink: 0; }

        /* ── RESPONSIVE ── */
        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; padding: 1.5rem; }
        }
        @media (max-width: 767.98px) {
            .main-content { padding: 1.1rem; }
            .page-header h1 { font-size: 1.65rem; }
            .table thead { display: none; }
            .table tbody tr { display: block; margin-bottom: 1rem; border: 1px solid var(--border); border-radius: 16px; background: white; }
            .table tbody td { display: flex; justify-content: space-between; align-items: center; padding: 0.8rem 1rem; border-bottom: 1px solid #edf2f7; }
            .table tbody td:last-child { border-bottom: none; }
            .table tbody td::before { content: attr(data-label); font-weight: 700; color: var(--warm-gray); font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.4px; }
            .search-box { max-width: 100%; }
        }
        @media (max-width: 480px) {
            .topbar { padding: 0 1rem; }
            .logo span { display: none; }
            .main-content { padding: 0.9rem; }
        }

        .fade-in { animation: fadeUp 0.35s ease forwards; }
        @keyframes fadeUp { from { opacity: 0.5; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
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
                type="button" data-bs-toggle="dropdown"
                style="color:var(--navy);font-weight:600;padding:0;">
            <i class="far fa-user-circle me-2" style="font-size:1.6rem;color:var(--secondary);"></i>
            <span class="d-none d-md-inline"><?= htmlspecialchars($_SESSION['full_name'] ?? 'Admin') ?></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end"
            style="border-radius:14px;border:1px solid var(--border);box-shadow:0 8px 20px rgba(0,0,0,0.06);">
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item text-danger" href="<?= $baseUrl ?>/index.php?page=logout">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </li>
        </ul>
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
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_menu"        class="nav-link"><i class="fas fa-utensils"></i> Menu Items</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_recipes"     class="nav-link"><i class="fas fa-tags"></i> Recipes</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_ingredients" class="nav-link"><i class="fas fa-boxes"></i> Ingredients</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_addons"      class="nav-link active"><i class="fas fa-plus-circle"></i> Add-ons</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_tables"      class="nav-link"><i class="fas fa-chair"></i> Tables</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_users"       class="nav-link"><i class="fas fa-users"></i> User Management</a></div>
        <div class="nav-section-title">SALES</div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_pos"    class="nav-link"><i class="fas fa-cash-register"></i> POS System</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_orders" class="nav-link"><i class="fas fa-receipt"></i> Orders</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=reports"       class="nav-link"><i class="fas fa-chart-bar"></i> Reports</a></div>
        <?php endif; ?>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">

    <div class="page-header">
        <div>
            <h1><i class="fas fa-plus-circle"></i> Add-ons Management</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/index.php?page=admin_dashboard">Home</a></li>
                    <li class="breadcrumb-item active">Add-ons</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn-add" data-bs-toggle="modal" data-bs-target="#addAddonModal">
                <i class="fas fa-plus"></i> Add Add-on
            </button>
            <button type="button" class="btn-assign" data-bs-toggle="modal" data-bs-target="#assignAddonModal">
                <i class="fas fa-link"></i> Assign Add-ons
            </button>
        </div>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show fade-in">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show fade-in">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Add-ons table -->
    <div class="card fade-in">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <h5 class="card-title">
                    <i class="fas fa-list" style="color:var(--primary);"></i>
                    Add-ons List
                </h5>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search add-ons…">
                </div>
            </div>
            <!-- Filter tabs -->
            <div class="filter-tabs" style="margin-top:1rem;">
                <?php
                    $totalCount    = count($addons ?? []);
                    $activeCount   = count(array_filter($addons ?? [], fn($a) => $a['is_active']));
                    $inactiveCount = $totalCount - $activeCount;
                ?>
                <button type="button" class="filter-tab active" data-filter="all">
                    All <span class="tab-count"><?= $totalCount ?></span>
                </button>
                <button type="button" class="filter-tab" data-filter="active">
                    <i class="fas fa-circle" style="font-size:0.45rem;color:var(--success);"></i>
                    Active <span class="tab-count"><?= $activeCount ?></span>
                </button>
                <button type="button" class="filter-tab" data-filter="inactive">
                    <i class="fas fa-circle" style="font-size:0.45rem;color:var(--warm-gray);"></i>
                    Inactive <span class="tab-count"><?= $inactiveCount ?></span>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (empty($addons)): ?>
                <div class="empty-state">
                    <i class="fas fa-plus-circle"></i>
                    <h5>No add-ons yet</h5>
                    <p>Click "Add Add-on" to create your first one.</p>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Ingredient</th>
                                <th>Qty Used</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($addons as $a): ?>
                            <tr class="<?= $a['is_active'] ? '' : 'row-inactive' ?>"
                                data-status="<?= $a['is_active'] ? 'active' : 'inactive' ?>">

                                <!-- Name + status pill in one cell — no separate Status column -->
                                <td data-label="Name">
                                    <div class="addon-name"><?= htmlspecialchars($a['addon_name']) ?></div>
                                    <?php if ($a['is_active']): ?>
                                        <span class="addon-status-pill pill-active">
                                            <i class="fas fa-circle" style="font-size:0.42rem;"></i> Active
                                        </span>
                                    <?php else: ?>
                                        <span class="addon-status-pill pill-inactive">
                                            <i class="fas fa-circle" style="font-size:0.42rem;"></i> Inactive
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td data-label="Price">
                                    <span class="price-display">₱<?= number_format($a['price'], 2) ?></span>
                                </td>

                                <td data-label="Ingredient">
                                    <span class="ingredient-badge">
                                        <i class="fas fa-box" style="font-size:0.62rem;opacity:0.65;"></i>
                                        <?= htmlspecialchars($a['ingredient_name']) ?>
                                    </span>
                                </td>

                                <td data-label="Qty Used">
                                    <strong><?= htmlspecialchars($a['quantity_used']) ?></strong>
                                    <span style="color:var(--warm-gray);font-size:0.8rem;margin-left:0.2rem;">
                                        <?= htmlspecialchars($a['unit_code']) ?>
                                    </span>
                                </td>

                                <td data-label="Actions" class="text-end">
                                    <div class="action-btns">
                                        <button type="button" class="btn-icon btn-edit"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editModal<?= $a['addon_id'] ?>"
                                                title="Edit">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                        <button type="button" class="btn-icon btn-delete"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteModal<?= $a['addon_id'] ?>"
                                                title="Remove">
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

</div><!-- /.main-content -->


<!-- ══════════════════════════════════════════
     ADD ADD-ON MODAL
══════════════════════════════════════════ -->
<div class="modal fade" id="addAddonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle" style="color:var(--secondary);"></i> Add New Add-on
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="index.php?page=manage_addons">
                <div class="modal-body">
                    <input type="hidden" name="add_addon" value="1">

                    <div class="mb-3">
                        <label class="form-label">Add-on Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-tag"></i></span>
                            <input type="text" class="form-control" name="addon_name"
                                   placeholder="e.g., Extra Shot" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control" name="price"
                                       step="0.01" min="0" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Quantity Used <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="quantity_used"
                                       step="0.01" min="0" placeholder="0.00" required>
                                <span class="input-group-text" id="addUnitText">Unit</span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
    <label class="form-label">Ingredient <span class="text-danger">*</span></label>
    <div class="input-group">
        <span class="input-group-text"><i class="fas fa-box"></i></span>
        <select class="form-select" name="ingredient_id" required>
            <option value="">— Select ingredient —</option>
            <?php foreach ($ingredients as $ing): ?>
                <option value="<?= $ing['ingredient_id'] ?>">
                    <?= htmlspecialchars($ing['ingredient_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Add Add-on
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php foreach ($addons as $a): ?>

<!-- EDIT MODAL -->
<div class="modal fade" id="editModal<?= $a['addon_id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-pen" style="color:var(--warning);"></i> Edit Add-on
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="index.php?page=manage_addons">
                <div class="modal-body">
                    <input type="hidden" name="edit_addon" value="1">
                    <input type="hidden" name="addon_id"   value="<?= $a['addon_id'] ?>">

                    <div class="mb-3">
                        <label class="form-label">Add-on Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-tag"></i></span>
                            <input type="text" class="form-control" name="addon_name"
                                   value="<?= htmlspecialchars($a['addon_name']) ?>" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control" name="price" step="0.01"
                                       value="<?= htmlspecialchars($a['price']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Quantity Used <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="quantity_used" step="0.01"
                                       value="<?= htmlspecialchars($a['quantity_used']) ?>" required>
                                <span class="input-group-text" id="editUnitText<?= $a['addon_id'] ?>"><?= htmlspecialchars($a['unit_code']) ?></span>
                            </div>
                        </div>
                    </div>

                                    <div class="mb-3">
                        <label class="form-label">Ingredient <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-box"></i></span>
                            <select class="form-select" name="ingredient_id" required>
                                <option value="">— Select ingredient —</option>
                                <?php foreach ($ingredients as $ing): ?>
                                    <option value="<?= $ing['ingredient_id'] ?>">
                                        <?= htmlspecialchars($ing['ingredient_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Status</label>
                        <div class="form-check form-switch" style="margin-top:0.2rem;">
                            <input class="form-check-input" type="checkbox" name="is_active"
                                   id="edit_active_<?= $a['addon_id'] ?>"
                                   value="1" <?= $a['is_active'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="edit_active_<?= $a['addon_id'] ?>"
                                   style="font-weight:500;color:var(--warm-gray);font-size:0.88rem;">
                                Active — visible in POS
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" style="background:var(--warning);color:white;border:none;">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- DELETE MODAL -->
<div class="modal fade" id="deleteModal<?= $a['addon_id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-trash" style="color:var(--danger);"></i> Remove Add-on
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" style="padding:1.8rem 1.6rem 1rem;">
                <div class="delete-icon-wrap">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <h6 style="color:var(--navy);font-size:1rem;margin-bottom:0.35rem;">
                    Remove "<?= htmlspecialchars($a['addon_name']) ?>"?
                </h6>
                <p class="text-muted" style="font-size:0.84rem;margin-bottom:0;">
                    This add-on will no longer appear in the POS.
                </p>
                <div class="info-box">
                    <i class="fas fa-info-circle" style="color:var(--primary-light);margin-top:1px;"></i>
                    <span>
                        If this add-on has been used in past orders it will be
                        <strong>deactivated</strong> (hidden, not deleted) to keep your order history intact.
                        Otherwise it is permanently removed.
                    </span>
                </div>
            </div>
            <div class="modal-footer" style="gap:0.5rem;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Cancel
                </button>
                <form method="POST" action="index.php?page=manage_addons" style="margin:0;">
                    <input type="hidden" name="delete_addon" value="1">
                    <input type="hidden" name="addon_id"     value="<?= $a['addon_id'] ?>">
                    <button type="submit" class="btn"
                            style="background:var(--danger);color:white;border:none;font-weight:600;">
                        <i class="fas fa-eye-slash me-1"></i> Yes, Remove from POS
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php endforeach; ?>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- ══════════════════════════════════════════
     ASSIGN ADD-ONS MODAL
══════════════════════════════════════════ -->
<div class="modal fade" id="assignAddonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-link" style="color:var(--secondary);"></i> Assign Add-ons to Products
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="index.php?page=manage_addons">
                <div class="modal-body">
                    <input type="hidden" name="assign_addons" value="1">

                    <div class="mb-3">
                        <label class="form-label">Select Product <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-utensils"></i></span>
                            <select class="form-select" name="item_id" id="productSelect" required>
                                <option value="">— Select a product —</option>
                                <?php 
                                // Get menu items - need to add this to controller
                                if (isset($menu_items) && !empty($menu_items)): 
                                    foreach ($menu_items as $item): 
                                ?>
                                    <option value="<?= $item['item_id'] ?>">
                                        <?= htmlspecialchars($item['item_name']) ?>
                                    </option>
                                <?php 
                                    endforeach; 
                                endif; 
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Available Add-ons</label>
                        <div class="alert alert-info" style="font-size:0.85rem;">
                            <i class="fas fa-info-circle me-2"></i>
                            Select the add-ons that should be available for this product. Only these add-ons will appear in the POS for this product.
                        </div>
                        <div class="addons-checkboxes" style="max-height:300px;overflow-y:auto;border:1px solid var(--border);border-radius:12px;padding:1rem;">
                            <?php if (isset($addons) && !empty($addons)): ?>
                                <?php 
                                // Get current assignments for each product
                                $product_assignments = [];
                                if (isset($menu_items) && !empty($menu_items)) {
                                    foreach ($menu_items as $item) {
                                        $product_assignments[$item['item_id']] = [];
                                    }
                                }
                                // In a real implementation, you'd fetch this from the database
                                // For now, we'll use JavaScript to handle this dynamically
                                ?>
                                <?php foreach ($addons as $addon): ?>
                                    <?php if ($addon['is_active']): ?>
                                        <div class="form-check mb-2 addon-checkbox-item" data-addon-id="<?= $addon['addon_id'] ?>">
                                            <input class="form-check-input addon-checkbox" type="checkbox" name="selected_addons[]" 
                                                   value="<?= $addon['addon_id'] ?>" id="addon_<?= $addon['addon_id'] ?>">
                                            <label class="form-check-label" for="addon_<?= $addon['addon_id'] ?>">
                                                <strong><?= htmlspecialchars($addon['addon_name']) ?></strong>
                                                <span class="text-muted">(+₱<?= number_format($addon['price'], 2) ?>)</span>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-box"></i> <?= htmlspecialchars($addon['ingredient_name']) ?>
                                                    | Qty: <?= htmlspecialchars($addon['quantity_used']) ?> <?= htmlspecialchars($addon['unit_code']) ?>
                                                </small>
                                            </label>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">No active add-ons available. Please create add-ons first.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="info-box">
                        <i class="fas fa-lightbulb" style="color:var(--warning);"></i>
                        <div>
                            <strong>Tip:</strong> This controls which add-ons appear in the POS for each product. It doesn't affect cost calculations - those are still handled by ingredient assignments.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Assignments
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Sidebar
    document.getElementById('menuToggle')?.addEventListener('click', () => {
        document.getElementById('sidebar').classList.toggle('show');
        document.getElementById('sidebarOverlay').classList.toggle('show');
    });
    document.getElementById('sidebarOverlay')?.addEventListener('click', () => {
        document.getElementById('sidebar').classList.remove('show');
        document.getElementById('sidebarOverlay').classList.remove('show');
    });

    // ── Filter tabs ───────────────────────────────────────────────────────────
    let currentFilter = 'all';
    let currentSearch = '';

    function applyFilters() {
        document.querySelectorAll('table tbody tr').forEach(row => {
            const status  = row.getAttribute('data-status'); // 'active' | 'inactive'
            const text    = row.textContent.toLowerCase();
            const matchFilter = currentFilter === 'all' || status === currentFilter;
            const matchSearch = currentSearch === '' || text.includes(currentSearch);
            row.style.display = (matchFilter && matchSearch) ? '' : 'none';
        });
    }

    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.getAttribute('data-filter');
            applyFilters();
        });
    });

    // ── Live search ───────────────────────────────────────────────────────────
    document.getElementById('searchInput')?.addEventListener('input', function () {
        currentSearch = this.value.toLowerCase();
        applyFilters();
    });

    // ── Auto-dismiss alerts after 5 s ─────────────────────────────────────────
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(el => {
            try { new bootstrap.Alert(el).close(); } catch (e) {}
        });
    }, 5000);

    // ── Modal backdrop cleanup ────────────────────────────────────────────────
    document.addEventListener('hidden.bs.modal', () => {
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow     = '';
        document.body.style.paddingRight = '';
    });

    // ── Product Add-ons Assignment Management ───────────────────────────────────
    const productSelect = document.getElementById('productSelect');
    if (productSelect) {
        productSelect.addEventListener('change', function() {
            const itemId = this.value;
            loadAssignedAddons(itemId);
        });
    }

    // Function to load and display assigned add-ons for a product
    async function loadAssignedAddons(itemId) {
        if (!itemId) {
            // Clear all checkboxes if no product selected
            document.querySelectorAll('.addon-checkbox').forEach(cb => cb.checked = false);
            return;
        }

        try {
            // Fetch current assignments for this product
            const response = await fetch(`index.php?page=manage_addons&action=get_assignments&item_id=${itemId}`);
            const data = await response.json();
            
            // Clear all checkboxes first
            document.querySelectorAll('.addon-checkbox').forEach(cb => cb.checked = false);
            
            // Check the assigned add-ons
            if (data.assigned_addons && Array.isArray(data.assigned_addons)) {
                data.assigned_addons.forEach(addonId => {
                    const checkbox = document.getElementById(`addon_${addonId}`);
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });
            }
        } catch (error) {
            console.error('Error loading assigned add-ons:', error);
        }
    }

    // Reset checkboxes when modal is closed
    const assignModal = document.getElementById('assignAddonModal');
    if (assignModal) {
        assignModal.addEventListener('hidden.bs.modal', function() {
            document.getElementById('productSelect').value = '';
            document.querySelectorAll('.addon-checkbox').forEach(cb => cb.checked = false);
        });
    }

    // ── Unit Display for Add-on Forms ───────────────────────────────────────────────
    // Store unit data for ingredients
    const ingredientUnits = <?php 
        $units = [];
        foreach ($ingredients as $ing) {
            $units[$ing['ingredient_id']] = $ing['unit_code'];
        }
        echo json_encode($units);
    ?>;

    // Add modal unit update
    const addIngredientSelect = document.querySelector('#addAddonModal select[name="ingredient_id"]');
    const addUnitText = document.getElementById('addUnitText');
    
    if (addIngredientSelect && addUnitText) {
        addIngredientSelect.addEventListener('change', function() {
            const selectedUnit = ingredientUnits[this.value] || 'Select ingredient';
            addUnitText.textContent = selectedUnit;
        });
    }

    // Edit modals unit update
    <?php foreach ($addons as $a): ?>
    const editIngredientSelect<?= $a['addon_id'] ?> = document.querySelector('#editModal<?= $a['addon_id'] ?> select[name="ingredient_id"]');
    const editUnitText<?= $a['addon_id'] ?> = document.getElementById('editUnitText<?= $a['addon_id'] ?>');
    
    if (editIngredientSelect<?= $a['addon_id'] ?> && editUnitText<?= $a['addon_id'] ?>) {
        editIngredientSelect<?= $a['addon_id'] ?>.addEventListener('change', function() {
            const selectedUnit = ingredientUnits[this.value] || 'Select ingredient';
            editUnitText<?= $a['addon_id'] ?>.textContent = selectedUnit;
        });
    }
    <?php endforeach; ?>
</script>
</body>
</html>