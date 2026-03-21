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
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* ------------------------------------------------------------
           MODERN CAFETERIA PALETTE – Teal + Coral + Navy
        ------------------------------------------------------------ */
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
            --light: #ffffff;
            --sidebar-width: 280px;
            --topbar-height: 72px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--light-bg);
            color: var(--dark);
            font-size: 14px;
            line-height: 1.5;
        }
        
        /* ------ TOPBAR – modern & sleek ------ */
        .topbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--topbar-height);
            background: white;
            border-bottom: 1px solid var(--border);
            z-index: 1040;
            padding: 0 2rem;
            box-shadow: 0 4px 12px rgba(15, 76, 92, 0.04);
        }
        
        .topbar .logo {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--primary-dark);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.65rem;
        }
        
        .topbar .logo i {
            font-size: 1.9rem;
            color: var(--secondary);
            transition: transform 0.2s;
        }
        
        .topbar .logo:hover i {
            transform: scale(1.05);
        }
        
        .topbar .logo span {
            background: linear-gradient(135deg, var(--primary), var(--navy));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .mobile-menu-btn {
            background: white;
            border: 1px solid var(--border);
            font-size: 1.4rem;
            color: var(--primary);
            cursor: pointer;
            padding: 0.5rem 1rem;
            border-radius: 12px;
            transition: all 0.2s;
        }
        
        .mobile-menu-btn:hover {
            background: var(--secondary);
            border-color: var(--secondary);
            color: white;
        }
        
        /* ------ SIDEBAR – clean & minimal ------ */
        .sidebar {
            position: fixed;
            top: var(--topbar-height);
            left: 0;
            width: var(--sidebar-width);
            height: calc(100vh - var(--topbar-height));
            background: white;
            border-right: 1px solid var(--border);
            overflow-y: auto;
            transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1030;
            box-shadow: 2px 0 12px rgba(0,0,0,0.02);
        }
        
        .sidebar::-webkit-scrollbar {
            width: 5px;
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 8px;
        }
        
        .sidebar-nav {
            padding: 2rem 0.75rem;
        }
        
        .nav-section-title {
            padding: 0.6rem 1.2rem;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--warm-gray);
            letter-spacing: 1.2px;
            opacity: 0.7;
            margin-top: 0.8rem;
        }
        
        .nav-item {
            margin: 0.2rem 0.6rem;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.8rem 1.2rem;
            color: var(--warm-gray);
            text-decoration: none;
            border-radius: 14px;
            transition: all 0.2s;
            font-weight: 500;
            font-size: 0.95rem;
        }
        
        .nav-link:hover {
            background: var(--lavender);
            color: var(--primary-dark);
        }
        
        .nav-link.active {
            background: var(--primary);
            color: white;
            box-shadow: 0 6px 12px rgba(15, 76, 92, 0.2);
        }
        
        .nav-link i {
            width: 24px;
            margin-right: 0.85rem;
            font-size: 1.2rem;
            text-align: center;
        }
        
        /* ------ MAIN CONTENT – spacious & balanced ------ */
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--topbar-height);
            padding: 2.2rem 2.2rem;
            min-height: calc(100vh - var(--topbar-height));
            transition: margin-left 0.2s;
        }
        
        /* ------ PAGE HEADER – modern typography with ADD BUTTON ------ */
        .page-header {
            margin-bottom: 2.2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .page-header h1 {
            font-size: 2.1rem;
            font-weight: 700;
            color: var(--navy);
            margin-bottom: 0;
            display: flex;
            align-items: center;
            gap: 0.7rem;
        }
        
        .page-header h1 i {
            color: var(--secondary);
            font-size: 2.1rem;
        }
        
        .breadcrumb {
            background: none;
            padding: 0.2rem 0;
            margin: 0;
            font-size: 0.9rem;
        }
        
        .breadcrumb-item a {
            color: var(--warm-gray);
            text-decoration: none;
            font-weight: 500;
        }
        
        .breadcrumb-item a:hover {
            color: var(--primary);
        }
        
        .breadcrumb-item.active {
            color: var(--primary-dark);
            font-weight: 600;
        }
        
        /* ------ ADD BUTTON – prominent but clean ------ */
        .btn-add {
            background: var(--primary);
            color: white;
            padding: 0.7rem 1.8rem;
            border-radius: 40px;
            font-weight: 600;
            font-size: 0.95rem;
            border: none;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        
        .btn-add:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 10px 18px rgba(15, 76, 92, 0.25);
            color: white;
        }
        
        .btn-add i {
            font-size: 1rem;
        }
        
        /* ------ CARD – modern, clean ------ */
        .card {
            background: white;
            border-radius: 24px;
            border: 1px solid rgba(203, 213, 224, 0.5);
            box-shadow: 0 8px 20px rgba(15, 76, 92, 0.02);
            margin-bottom: 1.8rem;
            transition: box-shadow 0.2s;
        }
        
        .card:hover {
            box-shadow: 0 14px 26px rgba(15, 76, 92, 0.06);
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid rgba(203, 213, 224, 0.5);
            padding: 1.35rem 1.8rem;
            border-radius: 24px 24px 0 0;
        }
        
        .card-body {
            padding: 1.6rem 1.8rem;
        }
        
        .card-title {
            font-size: 1.18rem;
            font-weight: 650;
            color: var(--navy);
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        
        /* ------ TABLE – crisp & modern ------ */
        .table-container {
            overflow-x: auto;
            margin: 0;
        }
        
        .table {
            margin: 0;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .table thead th {
            background: #f2f6fc;
            border-bottom: 2px solid var(--primary-light);
            font-weight: 600;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--primary-dark);
            padding: 1.1rem 1.2rem;
            white-space: nowrap;
        }
        
        .table tbody td {
            padding: 1.2rem 1.2rem;
            vertical-align: middle;
            border-bottom: 1px solid #edf2f7;
            color: var(--dark);
            background: white;
        }
        
        .table tbody tr:hover td {
            background-color: #fafcff;
        }
        
        /* ------ BADGES & STATUS – modern ------ */
        .badge {
            padding: 0.45rem 1rem;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.75rem;
            letter-spacing: 0.3px;
        }
        
        .bg-success { background: var(--success) !important; }
        .bg-danger { background: var(--danger) !important; }
        .bg-warning { background: var(--warning) !important; color: #1a202c; }
        .bg-secondary { background: var(--lavender) !important; color: var(--navy); }
        
        .status-badge {
            padding: 0.45rem 1rem;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .status-active {
            background: rgba(44, 122, 90, 0.12);
            color: var(--success);
        }
        
        .status-inactive {
            background: rgba(100, 116, 139, 0.12);
            color: var(--warm-gray);
        }
        
        /* ------ PRICE DISPLAY ------ */
        .price-display {
            font-size: 1rem;
            font-weight: 700;
            color: var(--success);
        }
        
        /* ------ ACTION BUTTONS – clean icon-only ------ */
        .action-btns {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
        }
        
        .btn-icon {
            width: 38px;
            height: 38px;
            padding: 0;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f2f6fc;
            border: none;
            color: var(--primary);
            transition: all 0.15s;
        }
        
        .btn-icon:hover {
            background: var(--secondary);
            color: white;
            transform: scale(0.95);
        }
        
        .btn-icon.btn-outline-warning {
            background: #fff3e0;
            color: #b85a1a;
            border: 1px solid rgba(244, 185, 66, 0.2);
        }
        
        .btn-icon.btn-outline-warning:hover {
            background: var(--warning);
            color: white;
        }
        
        .btn-icon.btn-outline-danger {
            background: #fff5f3;
            color: var(--danger);
            border: 1px solid rgba(214, 64, 69, 0.2);
        }
        
        .btn-icon.btn-outline-danger:hover {
            background: var(--danger);
            color: white;
        }
        
        /* ------ SEARCH BOX ------ */
        .search-box {
            position: relative;
            max-width: 250px;
        }
        
        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--warm-gray);
            z-index: 10;
        }
        
        .search-box .form-control {
            padding-left: 2.8rem;
            border-radius: 30px;
            border: 1px solid var(--border);
        }
        
        /* ------ MODAL – PERFECTLY CENTERED, FULLY ACCESSIBLE ------ */
        .modal {
            padding-right: 0 !important;
        }
        
        .modal-dialog {
            margin: 1.75rem auto;
            display: flex;
            align-items: center;
            min-height: calc(100% - 3.5rem);
        }
        
        .modal-dialog-centered {
            display: flex;
            align-items: center;
            min-height: calc(100% - 3.5rem);
        }
        
        .modal-content {
            border-radius: 28px;
            border: none;
            box-shadow: 0 25px 40px rgba(0,0,0,0.12);
            width: 100%;
        }
        
        .modal-header {
            border-bottom: 1px solid var(--border);
            padding: 1.6rem 1.8rem;
            border-radius: 28px 28px 0 0;
        }
        
        .modal-body {
            padding: 1.8rem;
        }
        
        .modal-footer {
            border-top: 1px solid var(--border);
            padding: 1.5rem 1.8rem;
            border-radius: 0 0 28px 28px;
        }
        
        .modal-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--navy);
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        
        /* ------ FORM STYLING – inside modal ------ */
        .form-label {
            font-weight: 600;
            color: var(--navy);
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
        }
        
        .form-control, .form-select {
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 0.65rem 1rem;
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(15, 76, 92, 0.1);
        }
        
        .form-control:disabled {
            background: #f2f6fc;
            color: var(--warm-gray);
        }
        
        .input-group-text {
            background: #f2f6fc;
            border: 1px solid var(--border);
            border-radius: 14px 0 0 14px;
            color: var(--primary-dark);
            font-weight: 500;
        }
        
        /* ------ ALERTS ------ */
        .alert {
            border-radius: 18px;
            border: none;
            padding: 1rem 1.5rem;
            margin-bottom: 1.8rem;
        }
        
        .alert-success {
            background: rgba(44, 122, 90, 0.12);
            color: #1e4a3b;
            border-left: 6px solid var(--success);
        }
        
        .alert-danger {
            background: rgba(214, 64, 69, 0.08);
            color: #9e3a34;
            border-left: 6px solid var(--danger);
        }
        
        /* ------ EMPTY STATE ------ */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: var(--border);
            margin-bottom: 1rem;
        }
        
        .empty-state h5 {
            color: var(--warm-gray);
            margin-bottom: 0.5rem;
        }
        
        .empty-state p {
            color: var(--border);
        }
        
        /* ------ RESPONSIVE – even & clean ------ */
        @media (max-width: 1199.98px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }
        
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
                padding: 1.8rem;
            }
            .sidebar-overlay {
                position: fixed;
                top: var(--topbar-height);
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(15, 76, 92, 0.2);
                backdrop-filter: blur(3px);
                z-index: 1025;
                display: none;
            }
            .sidebar-overlay.show {
                display: block;
            }
        }
        
        @media (max-width: 767.98px) {
            .main-content { padding: 1.2rem; }
            .page-header h1 { font-size: 1.8rem; }
            
            .table thead {
                display: none;
            }
            .table tbody tr {
                display: block;
                margin-bottom: 1.2rem;
                border: 1px solid var(--border);
                border-radius: 20px;
                background: white;
            }
            .table tbody td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.9rem 1.2rem;
                border-bottom: 1px solid #edf2f7;
            }
            .table tbody td:last-child {
                border-bottom: none;
            }
            .table tbody td::before {
                content: attr(data-label);
                font-weight: 700;
                color: var(--warm-gray);
                font-size: 0.75rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .action-btns {
                justify-content: flex-end;
            }
            .search-box {
                max-width: 100%;
            }
            
            .modal-dialog {
                margin: 0.5rem;
                min-height: calc(100% - 1rem);
            }
        }
        
        @media (max-width: 480px) {
            .topbar .logo span { display: none; }
            .topbar { padding: 0 1rem; }
            .main-content { padding: 1rem; }
        }
        
        /* ------ ANIMATION ------ */
        .fade-in {
            animation: modernFade 0.4s ease forwards;
        }
        
        @keyframes modernFade {
            from { opacity: 0.6; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <!-- TOPBAR - modern cafeteria -->
    <div class="topbar d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <button class="mobile-menu-btn d-lg-none" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>
            <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="logo d-none d-sm-flex">
                <i class="fas fa-utensils"></i>
                <span>Dine with Din - Cafeteria</span>
            </a>
            <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="logo d-sm-none">
                <i class="fas fa-utensils"></i>
            </a>
        </div>
        
        <div class="dropdown">
            <button class="btn btn-link text-decoration-none dropdown-toggle d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown" style="color: var(--navy); font-weight: 600;">
                <i class="far fa-user-circle fa-xl me-2" style="font-size: 1.6rem; color: var(--secondary);"></i>
                <span class="d-none d-md-inline"><?= htmlspecialchars($_SESSION['full_name'] ?? 'Admin') ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item text-danger" href="<?= $baseUrl ?>/index.php?page=logout">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
    
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-nav">
            <?php if (hasRole('admin')): ?>
            <div class="nav-section-title">DASHBOARD</div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="nav-link">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
            </div>
            
            <div class="nav-section-title">MANAGEMENT</div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/index.php?page=manage_menu" class="nav-link">
                    <i class="fas fa-utensils"></i>
                    Menu Items
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/index.php?page=manage_recipes" class="nav-link">
                    <i class="fas fa-tags"></i>
                    Recipes
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/index.php?page=manage_ingredients" class="nav-link">
                    <i class="fas fa-boxes"></i>
                    Ingredients
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/index.php?page=manage_addons" class="nav-link active">
                    <i class="fas fa-plus-circle"></i>
                    Add-ons
                </a>
            </div>
            <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_tables" class="nav-link"><i class="fas fa-chair"></i> Tables</a></div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/index.php?page=manage_users" class="nav-link">
                    <i class="fas fa-users"></i>
                    User Management
                </a>
            </div>
            <div class="nav-section-title">SALES</div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/index.php?page=manage_pos" class="nav-link">
                    <i class="fas fa-cash-register"></i>
                    POS System
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/index.php?page=manage_orders" class="nav-link">
                    <i class="fas fa-receipt"></i>
                    Orders
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/index.php?page=reports" class="nav-link">
                    <i class="fas fa-chart-bar"></i>
                    Reports
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- MAIN CONTENT - clean, modal-based add-ons management -->
    <div class="main-content">
        <!-- Page Header with ADD BUTTON -->
        <div class="page-header">
            <div>
                <h1>
                    <i class="fas fa-plus-circle me-2"></i>
                    Add-ons Management
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/index.php?page=admin_dashboard">Home</a></li>
                        <li class="breadcrumb-item active">Add-ons</li>
                    </ol>
                </nav>
            </div>
            <button type="button" class="btn-add" data-bs-toggle="modal" data-bs-target="#addAddonModal">
                <i class="fas fa-plus-circle"></i> Add Add-on
            </button>
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
        
        <!-- Add-ons List - FULL WIDTH (no sidebar form) -->
        <div class="row">
            <div class="col-12">
                <div class="card fade-in">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-list me-2" style="color: var(--primary);"></i>
                                Add-ons List (<?= count($addons ?? []) ?>)
                            </h5>
                            <div class="search-box">
                                <i class="fas fa-search"></i>
                                <input type="text" class="form-control" id="searchInput" placeholder="Search add-ons...">
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($addons)): ?>
                            <div class="empty-state">
                                <i class="fas fa-plus-circle"></i>
                                <h5>No add-ons found</h5>
                                <p>Click "Add Add-on" to get started</p>
                            </div>
                        <?php else: ?>
                            <div class="table-container">
                                <table class="table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Price</th>
                                            <th>Ingredient</th>
                                            <th>Quantity Used</th>
                                            <th>Status</th>
                                            <th>Last Updated</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($addons as $a): ?>
                                            <tr>
                                                <td data-label="Name">
                                                    <div class="fw-semibold" style="color: var(--navy);"><?= htmlspecialchars($a['addon_name']) ?></div>
                                                </td>
                                                <td data-label="Price">
                                                    <span class="price-display">₱<?= number_format($a['price'], 2) ?></span>
                                                </td>
                                                <td data-label="Ingredient">
                                                    <span class="badge bg-secondary"><?= htmlspecialchars($a['ingredient_name']) ?></span>
                                                </td>
                                                <td data-label="Quantity Used">
                                                    <strong><?= htmlspecialchars($a['quantity_used']) . ' ' . htmlspecialchars($a['unit_code']) ?></strong>
                                                </td>
                                                <td data-label="Status">
                                                    <?php if ($a['is_active']): ?>
                                                        <span class="status-badge status-active">
                                                            <i class="fas fa-check-circle"></i> Active
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="status-badge status-inactive">
                                                            <i class="fas fa-times-circle"></i> Inactive
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td data-label="Last Updated">
                                                    <small class="text-muted">
                                                        <?= date('M d, Y - h:i A', strtotime($a['last_updated'] ?? date('Y-m-d H:i:s'))) ?>
                                                    </small>
                                                </td>
                                                <td data-label="Actions" class="text-end">
                                                    <div class="action-btns justify-content-end">
                                                        <button type="button" class="btn-icon btn-outline-warning" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#editModal<?= $a['addon_id'] ?>" 
                                                                title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn-icon btn-outline-danger" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#deleteModal<?= $a['addon_id'] ?>" 
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
    </div>

    <!-- ========== ADD ADD-ON MODAL ========== -->
    <div class="modal fade" id="addAddonModal" tabindex="-1" aria-labelledby="addAddonModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAddonModalLabel">
                        <i class="fas fa-plus-circle me-2" style="color: var(--secondary);"></i>
                        Add New Add-on
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="index.php?page=manage_addons">
                    <div class="modal-body">
                        <input type="hidden" name="add_addon" value="1">
                        
                        <div class="mb-3">
                            <label for="addon_name" class="form-label">Add-on Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-tag"></i>
                                </span>
                                <input type="text" class="form-control" id="addon_name" name="addon_name" 
                                       placeholder="e.g., Extra Shot" required>
                            </div>
                        </div>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="price" class="form-label">Price <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-dollar-sign"></i>
                                    </span>
                                    <input type="number" class="form-control" id="price" name="price" 
                                           step="0.01" min="0" placeholder="0.00" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="quantity_used" class="form-label">Quantity Used <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-balance-scale"></i>
                                    </span>
                                    <input type="number" class="form-control" id="quantity_used" name="quantity_used" 
                                           step="0.01" min="0" placeholder="0.00" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="ingredient_id" class="form-label">Ingredient <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-box"></i>
                                </span>
                                <select class="form-select" id="ingredient_id" name="ingredient_id" required>
                                    <option value="">-- Select Ingredient --</option>
                                    <?php foreach ($ingredients as $ing): ?>
                                        <option value="<?= $ing['ingredient_id'] ?>"><?= htmlspecialchars($ing['ingredient_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>
                            Add Add-on
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ========== EDIT MODALS ========== -->
    <?php foreach ($addons as $a): ?>
    <div class="modal fade" id="editModal<?= $a['addon_id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $a['addon_id'] ?>" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel<?= $a['addon_id'] ?>">
                        <i class="fas fa-edit me-2" style="color: var(--warning);"></i>
                        Edit Add-on
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="index.php?page=manage_addons">
                    <div class="modal-body">
                        <input type="hidden" name="edit_addon" value="1">
                        <input type="hidden" name="addon_id" value="<?= $a['addon_id'] ?>">
                        
                        <div class="mb-3">
                            <label for="edit_name_<?= $a['addon_id'] ?>" class="form-label">Add-on Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-tag"></i>
                                </span>
                                <input type="text" name="addon_name" 
                                       id="edit_name_<?= $a['addon_id'] ?>"
                                       value="<?= htmlspecialchars($a['addon_name']) ?>" 
                                       class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="edit_price_<?= $a['addon_id'] ?>" class="form-label">Price <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-dollar-sign"></i>
                                    </span>
                                    <input type="number" step="0.01" name="price" 
                                           id="edit_price_<?= $a['addon_id'] ?>"
                                           value="<?= htmlspecialchars($a['price']) ?>" 
                                           class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_quantity_<?= $a['addon_id'] ?>" class="form-label">Quantity Used <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-balance-scale"></i>
                                    </span>
                                    <input type="number" step="0.01" name="quantity_used" 
                                           id="edit_quantity_<?= $a['addon_id'] ?>"
                                           value="<?= htmlspecialchars($a['quantity_used']) ?>" 
                                           class="form-control" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_ingredient_<?= $a['addon_id'] ?>" class="form-label">Ingredient <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-box"></i>
                                </span>
                                <select name="ingredient_id" id="edit_ingredient_<?= $a['addon_id'] ?>" class="form-select" required>
                                    <?php foreach ($ingredients as $ing): ?>
                                        <option value="<?= $ing['ingredient_id'] ?>" 
                                            <?= $a['ingredient_id'] == $ing['ingredient_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($ing['ingredient_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" 
                                       id="edit_active_<?= $a['addon_id'] ?>"
                                       value="1" <?= $a['is_active'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="edit_active_<?= $a['addon_id'] ?>">
                                    Active
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i> Last updated: <?= date('M d, Y - h:i A', strtotime($a['last_updated'] ?? date('Y-m-d H:i:s'))) ?>
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn" style="background: var(--warning); color: white; border: none;">
                            <i class="fas fa-save me-2"></i>
                            Update Add-on
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- ========== DELETE MODALS ========== -->
    <div class="modal fade" id="deleteModal<?= $a['addon_id'] ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?= $a['addon_id'] ?>" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel<?= $a['addon_id'] ?>">
                        <i class="fas fa-exclamation-triangle me-2" style="color: var(--warning);"></i>
                        Confirm Delete
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="fas fa-trash-alt fa-3x mb-3" style="color: var(--danger);"></i>
                    <h6 style="color: var(--navy);">Delete "<?= htmlspecialchars($a['addon_name']) ?>"?</h6>
                    <p class="text-muted small mb-0">
                        This action cannot be undone.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <form method="POST" action="index.php?page=manage_addons">
                        <input type="hidden" name="delete_addon" value="1">
                        <input type="hidden" name="addon_id" value="<?= $a['addon_id'] ?>">
                        <button type="submit" class="btn" style="background: var(--danger); color: white; border: none;">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Mobile menu toggle
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        
        menuToggle?.addEventListener('click', () => {
            sidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
        });
        
        sidebarOverlay?.addEventListener('click', () => {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });
        
        // Search functionality
        const searchInput = document.getElementById('searchInput');
        searchInput?.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
        
        // Auto-dismiss alerts
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Fix for modal accessibility - ensure backdrop works properly
        document.addEventListener('hidden.bs.modal', function (event) {
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        });
    </script>
</body>
</html>