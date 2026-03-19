<?php
// Pure view: expects $users, $user_to_edit, $success, $error, $totalUsers, $activeUsers, $cashierUsers, $kitchenUsers
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5">
    <title>User Management - Cafeteria POS</title>
    
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
        
        /* ------ STATS CARDS – modern glassmorphism ------ */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.8rem;
            margin-bottom: 2.5rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 24px;
            padding: 1.6rem 1.5rem;
            border: 1px solid rgba(203, 213, 224, 0.5);
            transition: all 0.25s;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(4px);
            box-shadow: 0 8px 20px rgba(15, 76, 92, 0.04);
            display: flex;
            align-items: center;
            gap: 1.2rem;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            opacity: 0.9;
        }
        
        .stat-card.secondary::before { background: var(--secondary); }
        .stat-card.info::before { background: var(--info); }
        .stat-card.warning::before { background: var(--warning); }
        .stat-card.success::before { background: var(--success); }
        
        .stat-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 28px rgba(15, 76, 92, 0.08);
            border-color: var(--primary-light);
        }
        
        .stat-card .icon {
            width: 60px;
            height: 60px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            background: linear-gradient(145deg, #f0f5fa, white);
            color: var(--primary);
            border: 1px solid rgba(15, 76, 92, 0.1);
        }
        
        .stat-card .label {
            font-size: 0.8rem;
            color: var(--warm-gray);
            margin-bottom: 0.25rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 700;
        }
        
        .stat-card .value {
            font-size: 1.9rem;
            font-weight: 700;
            color: var(--navy);
            line-height: 1.1;
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
        
        /* ------ USER AVATAR – modern gradient ------ */
        .user-avatar {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary), var(--navy));
            color: white;
            border-radius: 16px;
            font-weight: 600;
            font-size: 1.1rem;
            box-shadow: 0 4px 8px rgba(15, 76, 92, 0.15);
        }
        
        /* ------ ROLE BADGE – modern & consistent ------ */
        .role-badge {
            display: inline-block;
            padding: 0.45rem 1rem;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .role-badge.admin {
            background: rgba(214, 64, 69, 0.12);
            color: var(--danger);
        }
        
        .role-badge.cashier {
            background: rgba(94, 155, 158, 0.12);
            color: var(--info);
        }
        
        .role-badge.kitchen {
            background: rgba(244, 185, 66, 0.12);
            color: #b87a1a;
        }
        
        /* ------ BADGES – status indicators ------ */
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
        
        .btn-icon.btn-outline-secondary {
            background: #f2f6fc;
            color: var(--warm-gray);
            border: 1px solid rgba(100, 116, 139, 0.2);
            cursor: not-allowed;
            opacity: 0.6;
        }
        
        .btn-icon.btn-outline-secondary:hover {
            background: #f2f6fc;
            color: var(--warm-gray);
            transform: none;
        }
        
        /* ------ SEARCH BOX ------ */
        .search-box {
            position: relative;
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
        
        /* ------ ROLE INFORMATION CARD – clean & modern ------ */
        .info-card {
            background: white;
            border-radius: 24px;
            border: 1px solid rgba(203, 213, 224, 0.5);
            padding: 1.6rem;
        }
        
        .permission-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #edf2f7;
        }
        
        .permission-item:last-child {
            border-bottom: none;
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
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 767.98px) {
            .main-content { padding: 1.2rem; }
            .stats-grid { grid-template-columns: 1fr; }
            .page-header h1 { font-size: 1.8rem; }
            
            /* mobile table stacking */
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
                <a href="<?= $baseUrl ?>/index.php?page=manage_addons" class="nav-link">
                    <i class="fas fa-plus-circle"></i>
                    Add-ons
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/index.php?page=manage_users" class="nav-link active">
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
        </div>
    </div>
    
    <!-- MAIN CONTENT - clean, modal-based user management -->
    <div class="main-content" id="mainContent">
        <!-- Page Header with ADD BUTTON -->
        <div class="page-header">
            <div>
                <h1>
                    <i class="fas fa-users me-2"></i>
                    User Management
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/index.php?page=admin_dashboard">Home</a></li>
                        <li class="breadcrumb-item active">Users</li>
                    </ol>
                </nav>
            </div>
            <button type="button" class="btn-add" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fas fa-user-plus"></i> Add User
            </button>
        </div>
        
        <!-- Alerts -->
        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show fade-in" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show fade-in" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card primary fade-in">
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <div class="label">Total Users</div>
                    <div class="value"><?= $totalUsers ?? 0 ?></div>
                </div>
            </div>
            
            <div class="stat-card success fade-in" style="animation-delay: 0.1s">
                <div class="icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <div>
                    <div class="label">Active Users</div>
                    <div class="value"><?= $activeUsers ?? 0 ?></div>
                </div>
            </div>
            
            <div class="stat-card info fade-in" style="animation-delay: 0.2s">
                <div class="icon">
                    <i class="fas fa-cash-register"></i>
                </div>
                <div>
                    <div class="label">Cashiers</div>
                    <div class="value"><?= $cashierUsers ?? 0 ?></div>
                </div>
            </div>
            
            <div class="stat-card warning fade-in" style="animation-delay: 0.3s">
                <div class="icon">
                    <i class="fas fa-utensils"></i>
                </div>
                <div>
                    <div class="label">Kitchen Staff</div>
                    <div class="value"><?= $kitchenUsers ?? 0 ?></div>
                </div>
            </div>
        </div>
        
        <div class="row g-4">
            <!-- Users List - FULL WIDTH + Role Info Card -->
            <div class="col-lg-8">
                <div class="card fade-in">
                    <div class="card-header">
                        <div class="row align-items-center g-3">
                            <div class="col-md-6 col-sm-12">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-list me-2" style="color: var(--primary);"></i>
                                    All Users (<?= count($users ?? []) ?>)
                                </h5>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <div class="search-box">
                                    <i class="fas fa-search"></i>
                                    <input type="text" class="form-control" id="searchInput" placeholder="Search users...">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($users)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-users fa-4x" style="color: var(--border); margin-bottom: 1rem;"></i>
                                <h5 style="color: var(--warm-gray);">No users found</h5>
                                <p style="color: var(--warm-gray);">Click "Add User" to get started</p>
                            </div>
                        <?php else: ?>
                            <div class="table-container">
                                <table class="table mb-0">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                            <th>Joined</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users ?? [] as $user): ?>
                                            <tr>
                                                <td data-label="User">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="user-avatar">
                                                            <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                                                        </div>
                                                        <div>
                                                            <div class="fw-semibold" style="color: var(--navy);"><?= htmlspecialchars($user['full_name']) ?></div>
                                                            <small class="text-muted">@<?= htmlspecialchars($user['username']) ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td data-label="Email">
                                                    <span style="color: var(--warm-gray);">
                                                        <i class="fas fa-envelope me-2" style="color: var(--primary-light);"></i>
                                                        <?= htmlspecialchars($user['email'] ?? '—') ?>
                                                    </span>
                                                </td>
                                                <td data-label="Role">
                                                    <span class="role-badge <?= strtolower($user['role']) ?>">
                                                        <?= htmlspecialchars(ucfirst($user['role'])) ?>
                                                    </span>
                                                </td>
                                                <td data-label="Status">
                                                    <span class="badge bg-<?= $user['is_active'] ? 'success' : 'danger' ?>">
                                                        <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                                                        <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                                                    </span>
                                                </td>
                                                <td data-label="Joined">
                                                    <small class="text-muted">
                                                        <i class="far fa-calendar me-1"></i>
                                                        <?= date('M d, Y', strtotime($user['created_at'] ?? 'now')) ?>
                                                    </small>
                                                </td>
                                                <td data-label="Actions" class="text-end">
                                                    <div class="action-btns justify-content-end">
                                                        <button type="button" class="btn-icon btn-outline-warning" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#editModal<?= $user['user_id'] ?>" 
                                                                title="Edit User">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        
                                                        <?php if ($user['user_id'] != ($_SESSION['user_id'] ?? 0)): ?>
                                                        <button type="button" class="btn-icon btn-outline-danger" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#deleteModal<?= $user['user_id'] ?>" 
                                                                title="Delete User">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                        <?php else: ?>
                                                        <button type="button" class="btn-icon btn-outline-secondary" 
                                                                title="Cannot delete your own account" disabled>
                                                            <i class="fas fa-ban"></i>
                                                        </button>
                                                        <?php endif; ?>
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
            
            <!-- Role Information Card - right sidebar -->
            <div class="col-lg-4">
                <div class="card fade-in">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2" style="color: var(--info);"></i>
                            Role Permissions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="permission-item">
                            <div style="min-width: 100px;">
                                <span class="role-badge admin">Admin</span>
                            </div>
                            <small class="text-muted">Full system access, user management, reports</small>
                        </div>
                        <div class="permission-item">
                            <div style="min-width: 100px;">
                                <span class="role-badge cashier">Cashier</span>
                            </div>
                            <small class="text-muted">Process sales, handle transactions</small>
                        </div>
                        <div class="permission-item">
                            <div style="min-width: 100px;">
                                <span class="role-badge kitchen">Kitchen</span>
                            </div>
                            <small class="text-muted">View & update order status</small>
                        </div>
                        
                        <div class="mt-4 pt-3 border-top">
                            <small class="text-muted d-block mb-2">
                                <i class="fas fa-shield-alt me-1"></i> <strong>Note:</strong>
                            </small>
                            <small class="text-muted">
                                You cannot delete your own account. Users with active sessions will be logged out when deactivated.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- ========== ADD USER MODAL ========== -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">
                        <i class="fas fa-user-plus me-2" style="color: var(--secondary);"></i>
                        Add New User
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="addUserForm">
                    <div class="modal-body">
                        <input type="hidden" name="add_user" value="1">
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="add_username" class="form-label">Username <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <input type="text" class="form-control" id="add_username" name="username" 
                                           placeholder="Enter username" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="add_full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-id-card"></i>
                                    </span>
                                    <input type="text" class="form-control" id="add_full_name" name="full_name" 
                                           placeholder="Enter full name" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="add_email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" class="form-control" id="add_email" name="email" 
                                       placeholder="user@example.com">
                            </div>
                            <small class="text-muted">Email is optional</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="add_password" class="form-label">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="add_password" name="password" 
                                       placeholder="Minimum 6 characters" required>
                                <button class="btn btn-outline-secondary" type="button" id="toggleAddPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="text-muted">Password must be at least 6 characters long</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="add_role" class="form-label">User Role <span class="text-danger">*</span></label>
                            <select class="form-select" id="add_role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="admin">Administrator</option>
                                <option value="cashier">Cashier</option>
                                <option value="kitchen">Kitchen Staff</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus me-2"></i>
                            Create User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- ========== EDIT MODALS ========== -->
    <?php foreach ($users ?? [] as $user): ?>
    <div class="modal fade" id="editModal<?= $user['user_id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $user['user_id'] ?>" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel<?= $user['user_id'] ?>">
                        <i class="fas fa-user-edit me-2" style="color: var(--warning);"></i>
                        Edit User
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                        <input type="hidden" name="update_user" value="1">
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="edit_username_<?= $user['user_id'] ?>" class="form-label">Username <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <input type="text" class="form-control" id="edit_username_<?= $user['user_id'] ?>" name="username" 
                                           value="<?= htmlspecialchars($user['username']) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_full_name_<?= $user['user_id'] ?>" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-id-card"></i>
                                    </span>
                                    <input type="text" class="form-control" id="edit_full_name_<?= $user['user_id'] ?>" name="full_name" 
                                           value="<?= htmlspecialchars($user['full_name']) ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_email_<?= $user['user_id'] ?>" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" class="form-control" id="edit_email_<?= $user['user_id'] ?>" name="email" 
                                       value="<?= htmlspecialchars($user['email'] ?? '') ?>" placeholder="user@example.com">
                            </div>
                            <small class="text-muted">Email is optional</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_role_<?= $user['user_id'] ?>" class="form-label">User Role <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_role_<?= $user['user_id'] ?>" name="role" required>
                                <option value="">Select Role</option>
                                <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Administrator</option>
                                <option value="cashier" <?= $user['role'] == 'cashier' ? 'selected' : '' ?>>Cashier</option>
                                <option value="kitchen" <?= $user['role'] == 'kitchen' ? 'selected' : '' ?>>Kitchen Staff</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="edit_active_<?= $user['user_id'] ?>" name="is_active" value="1"
                                       <?= $user['is_active'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="edit_active_<?= $user['user_id'] ?>">
                                    Account Active
                                </label>
                            </div>
                            <small class="text-muted">Inactive users cannot log in to the system</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_password_<?= $user['user_id'] ?>" class="form-label">New Password (optional)</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="edit_password_<?= $user['user_id'] ?>" name="password" 
                                       placeholder="Leave blank to keep current password">
                                <button class="btn btn-outline-secondary" type="button" id="toggleEditPassword<?= $user['user_id'] ?>">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="text-muted">Minimum 6 characters. Leave empty to keep current password.</small>
                        </div>
                        
                        <div class="mb-3">
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i> 
                                Member since: <?= date('M d, Y', strtotime($user['created_at'] ?? 'now')) ?>
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn" style="background: var(--warning); color: white; border: none;">
                            <i class="fas fa-save me-2"></i>
                            Update User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- ========== DELETE MODALS – PERFECTLY CENTERED ========== -->
    <div class="modal fade" id="deleteModal<?= $user['user_id'] ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?= $user['user_id'] ?>" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel<?= $user['user_id'] ?>">
                        <i class="fas fa-exclamation-triangle me-2" style="color: var(--warning);"></i>
                        Confirm Delete
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="user-avatar mb-3" style="width: 80px; height: 80px; font-size: 2rem; margin: 0 auto; border-radius: 24px;">
                        <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                    </div>
                    <h6 style="color: var(--navy);">Delete user "<?= htmlspecialchars($user['full_name']) ?>"?</h6>
                    <p class="text-muted small mb-0">
                        This will permanently remove the user and all associated data. This action cannot be undone.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                        <input type="hidden" name="delete_user" value="1">
                        <button type="submit" class="btn" style="background: var(--danger); color: white; border: none;">
                            <i class="fas fa-trash me-2"></i>
                            Delete User
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    
    <!-- ========== EDIT MODAL – dynamically triggered if $user_to_edit is set ========== -->
    <?php if (isset($user_to_edit) && !empty($user_to_edit)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Find the edit modal for this user and show it
            const editModal = new bootstrap.Modal(document.getElementById('editModal<?= $user_to_edit['user_id'] ?>'));
            editModal.show();
        });
    </script>
    <?php endif; ?>
    
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
        
        // Password visibility toggle for Add modal
        const toggleAddPassword = document.getElementById('toggleAddPassword');
        const addPassword = document.getElementById('add_password');
        
        toggleAddPassword?.addEventListener('click', function() {
            const type = addPassword.getAttribute('type') === 'password' ? 'text' : 'password';
            addPassword.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
        
        // Password visibility toggle for Edit modals
        <?php foreach ($users ?? [] as $user): ?>
        const toggleEditPassword<?= $user['user_id'] ?> = document.getElementById('toggleEditPassword<?= $user['user_id'] ?>');
        const editPassword<?= $user['user_id'] ?> = document.getElementById('edit_password_<?= $user['user_id'] ?>');
        
        toggleEditPassword<?= $user['user_id'] ?>?.addEventListener('click', function() {
            const type = editPassword<?= $user['user_id'] ?>.getAttribute('type') === 'password' ? 'text' : 'password';
            editPassword<?= $user['user_id'] ?>.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
        <?php endforeach; ?>
        
        // Form validation for Add User
        const addUserForm = document.getElementById('addUserForm');
        addUserForm?.addEventListener('submit', function(e) {
            const email = document.getElementById('add_email').value;
            if (email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    e.preventDefault();
                    alert('Please enter a valid email address');
                    return false;
                }
            }
            
            const password = document.getElementById('add_password');
            if (password && password.value.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long');
                return false;
            }
        });
        
        // Auto-dismiss alerts
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // FIX: Ensure modal backdrop is properly removed when modals are closed
        document.addEventListener('hidden.bs.modal', function (event) {
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        });
    </script>
</body>
</html>