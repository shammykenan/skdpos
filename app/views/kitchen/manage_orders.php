<?php
// Pure view: expects $orders, $_SESSION['success'], $_SESSION['error']
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5">
    <title>Manage Orders - Cafeteria POS</title>
    
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
        
        /* ------ PAGE HEADER – modern typography ------ */
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
        
        /* ------ FILTER TABS – modern & clean ------ */
        .filter-tabs {
            display: flex;
            gap: 0.65rem;
            flex-wrap: wrap;
        }
        
        .filter-tab {
            padding: 0.6rem 1.2rem;
            border: 1px solid var(--border);
            background: white;
            color: var(--warm-gray);
            border-radius: 40px;
            cursor: pointer;
            transition: all 0.2s;
            font-weight: 600;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .filter-tab:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: #f2f6fc;
        }
        
        .filter-tab.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .filter-count {
            background: rgba(255,255,255,0.2);
            padding: 0.15rem 0.6rem;
            border-radius: 30px;
            font-size: 0.7rem;
            font-weight: 700;
        }
        
        .filter-tab.active .filter-count {
            background: rgba(255,255,255,0.3);
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
            border-radius: 40px;
            border: 1px solid var(--border);
        }
        
        /* ------ TABLE – crisp & modern ------ */
        .table-container {
            overflow-x: auto;
            margin: 0;
        }
        
        .orders-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .orders-table thead th {
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
        
        .orders-table tbody td {
            padding: 1.2rem 1.2rem;
            vertical-align: top;
            border-bottom: 1px solid #edf2f7;
            color: var(--dark);
            background: white;
        }
        
        .orders-table tbody tr:hover td {
            background-color: #fafcff;
        }
        
        /* ------ ORDER NUMBER BADGE ------ */
        .order-number {
            font-weight: 700;
            color: var(--navy);
            font-size: 1rem;
        }
        
        /* ------ STATUS BADGES – modern ------ */
        .status-badge {
            display: inline-block;
            padding: 0.45rem 1rem;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.3px;
        }
        
        .status-pending {
            background: rgba(244, 185, 66, 0.12);
            color: #b87a1a;
        }
        
        .status-preparing {
            background: rgba(94, 155, 158, 0.12);
            color: var(--info);
        }
        
        .status-completed {
            background: rgba(44, 122, 90, 0.12);
            color: var(--success);
        }
        
        .status-cancelled {
            background: rgba(214, 64, 69, 0.08);
            color: var(--danger);
        }
        
        /* ------ ORDER TYPE BADGE – matches DB: dine_in / takeout ------ */
        .order-type-badge {
            display: inline-block;
            padding: 0.35rem 0.9rem;
            border-radius: 30px;
            font-size: 0.7rem;
            font-weight: 600;
            background: #f2f6fc;
            color: var(--navy);
        }
        
        .order-type-badge i {
            margin-right: 0.3rem;
        }
        
        .order-type-badge.dine_in i {
            color: var(--info);
        }
        
        .order-type-badge.takeout i {
            color: var(--secondary);
        }
        
        .order-type-missing {
            color: var(--warm-gray);
            font-style: italic;
        }
        
        /* ------ ORDER ITEMS LIST ------ */
        .order-items {
            margin: 0;
            padding: 0;
            list-style: none;
        }
        
        .order-items > li {
            padding: 0.35rem 0;
            border-bottom: 1px dashed #edf2f7;
        }
        
        .order-items > li:last-child {
            border-bottom: none;
        }
        
        .item-name {
            font-weight: 600;
            color: var(--navy);
            font-size: 0.85rem;
        }
        
        .item-qty {
            color: var(--warm-gray);
            font-size: 0.8rem;
            margin-left: 0.25rem;
        }
        
        .addon-list {
            margin: 0.25rem 0 0 1rem;
            padding: 0;
            list-style: none;
        }
        
        .addon-list li {
            color: var(--warm-gray);
            font-size: 0.75rem;
            padding: 0.1rem 0;
        }
        
        .addon-list li:before {
            content: "↳ ";
            color: var(--primary-light);
            font-weight: bold;
        }
        
        /* ------ PRICE DISPLAY ------ */
        .price {
            font-weight: 700;
            color: var(--success);
            font-size: 1rem;
        }
        
        /* ------ FORM ELEMENTS – inside table ------ */
        .form-select {
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 0.5rem 0.75rem;
            font-size: 0.8rem;
            transition: all 0.2s;
            min-width: 130px;
        }
        
        .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(15, 76, 92, 0.1);
        }
        
        .form-select:disabled {
            background: #f2f6fc;
            color: var(--warm-gray);
            opacity: 0.7;
            cursor: not-allowed;
        }
        
        /* ------ BUTTONS – modern & clean ------ */
        .btn {
            padding: 0.6rem 1.2rem;
            border-radius: 40px;
            font-weight: 600;
            font-size: 0.8rem;
            transition: all 0.18s;
            letter-spacing: 0.3px;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover:not(:disabled) {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(15, 76, 92, 0.2);
        }
        
        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .btn-danger {
            background: var(--danger);
            color: white;
        }
        
        .btn-danger:hover:not(:disabled) {
            background: #b83b41;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(214, 64, 69, 0.2);
        }
        
        .btn-danger:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .btn-sm {
            padding: 0.4rem 1rem;
            font-size: 0.75rem;
        }
        
        .btn-secondary {
            background: var(--lavender);
            color: var(--navy);
        }
        
        .btn-secondary:hover {
            background: #d1d9e6;
        }
        
        .btn-outline-secondary {
            border: 1.5px solid var(--border);
            color: var(--warm-gray);
            background: transparent;
        }
        
        .btn-outline-secondary:hover {
            background: var(--lavender);
            border-color: var(--warm-gray);
        }
        
        /* ------ ACTION BUTTONS CONTAINER ------ */
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
        }
        
        .status-form {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        /* ------ MODAL – modern, centered ------ */
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
        
        .modal-icon {
            width: 60px;
            height: 60px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.8rem;
        }
        
        .modal-icon.danger {
            background: rgba(214, 64, 69, 0.1);
            color: var(--danger);
        }
        
        .modal-icon.primary {
            background: rgba(15, 76, 92, 0.1);
            color: var(--primary);
        }
        
        .modal-icon.success {
            background: rgba(44, 122, 90, 0.1);
            color: var(--success);
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
        
        .alert-info {
            background: rgba(94, 155, 158, 0.08);
            color: var(--info);
            border-left: 6px solid var(--info);
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
        
        .empty-state h3 {
            font-size: 1.3rem;
            color: var(--warm-gray);
            margin-bottom: 0.5rem;
        }
        
        .empty-state p {
            color: var(--border);
        }
        
        /* ------ RESPONSIVE – even & clean ------ */
        @media (max-width: 1199.98px) {
            .orders-table { font-size: 0.85rem; }
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
            
            .orders-table thead {
                display: none;
            }
            .orders-table tbody tr {
                display: block;
                margin-bottom: 1.2rem;
                border: 1px solid var(--border);
                border-radius: 20px;
                background: white;
            }
            .orders-table tbody td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.9rem 1.2rem;
                border-bottom: 1px solid #edf2f7;
            }
            .orders-table tbody td:last-child {
                border-bottom: none;
            }
            .orders-table tbody td::before {
                content: attr(data-label);
                font-weight: 700;
                color: var(--warm-gray);
                font-size: 0.75rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            
            .filter-tabs {
                flex-direction: column;
            }
            .filter-tab {
                width: 100%;
                justify-content: center;
            }
            .search-box {
                max-width: 100%;
                margin-top: 0.5rem;
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
                <span>Cafeteria POS</span>
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
                <li>
                    <a class="dropdown-item" href="<?= $baseUrl ?>/index.php?page=profile">
                        <i class="fas fa-user me-2"></i> Profile
                    </a>
                </li>
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
                <a href="<?= $baseUrl ?>/index.php?page=manage_addons" class="nav-link">
                    <i class="fas fa-plus-circle"></i>
                    Add-ons
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/index.php?page=manage_users" class="nav-link">
                    <i class="fas fa-users"></i>
                    User Management
                </a>
            </div>
            <?php endif; ?>
            <?php if (hasRole('admin') || hasRole('cashier')): ?>
            <div class="nav-section-title">SALES</div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/index.php?page=manage_pos" class="nav-link">
                    <i class="fas fa-cash-register"></i>
                    POS System
                </a>
            </div>
            <?php endif; ?>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/index.php?page=manage_orders" class="nav-link active">
                    <i class="fas fa-receipt"></i>
                    Orders
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/index.php?page=manage_reports" class="nav-link">
                    <i class="fas fa-chart-bar"></i>
                    Reports
                </a>
            </div>
        </div>
    </div>
    
    <!-- MAIN CONTENT -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1>
                    <i class="fas fa-receipt me-2"></i>
                    Manage Orders
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/index.php?page=admin_dashboard">Home</a></li>
                        <li class="breadcrumb-item active">Orders</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <!-- Filter Tabs & Search -->
        <div class="card fade-in mb-3">
            <div class="card-body">
                <div class="row g-3 align-items-center">
                    <div class="col-lg-8 col-md-12">
                        <div class="filter-tabs">
                            <button class="filter-tab active" data-status="all">
                                <i class="fas fa-th me-1"></i>
                                All Orders
                                <span class="filter-count"><?= count($orders) ?></span>
                            </button>
                            <button class="filter-tab" data-status="pending">
                                <i class="fas fa-clock me-1"></i>
                                Pending
                                <span class="filter-count"><?= count(array_filter($orders, fn($o) => $o['status'] == 'pending')) ?></span>
                            </button>
                            <button class="filter-tab" data-status="preparing">
                                <i class="fas fa-utensils me-1"></i>
                                Preparing
                                <span class="filter-count"><?= count(array_filter($orders, fn($o) => $o['status'] == 'preparing')) ?></span>
                            </button>
                            <button class="filter-tab" data-status="completed">
                                <i class="fas fa-check-circle me-1"></i>
                                Completed
                                <span class="filter-count"><?= count(array_filter($orders, fn($o) => $o['status'] == 'completed')) ?></span>
                            </button>
                            <button class="filter-tab" data-status="cancelled">
                                <i class="fas fa-times-circle me-1"></i>
                                Cancelled
                                <span class="filter-count"><?= count(array_filter($orders, fn($o) => $o['status'] == 'cancelled')) ?></span>
                            </button>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" class="form-control" id="searchInput" placeholder="Search orders by #, customer, table...">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Orders Table -->
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2" style="color: var(--primary);"></i>
                    <span id="currentFilterLabel">All Orders</span>
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-container">
                    <?php if (!empty($orders)): ?>
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Order Type</th>
                                    <th>Items</th>
                                    <th>Subtotal</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="ordersTableBody">
                                <?php foreach ($orders as $order): ?>
                                    <?php 
                                        // Handle NULL order_type - show dash instead of default value
                                        // Database uses ENUM('dine_in', 'takeout')
                                        $orderType = isset($order['order_type']) && !is_null($order['order_type']) ? $order['order_type'] : null;
                                        $orderTypeLabel = '';
                                        $orderTypeIcon = '';
                                        
                                        if ($orderType === 'dine_in') {
                                            $orderTypeLabel = 'Dine In';
                                            $orderTypeIcon = 'fa-utensils';
                                        } elseif ($orderType === 'takeout') {
                                            $orderTypeLabel = 'Takeout'; // exactly as in DB
                                            $orderTypeIcon = 'fa-bag-shopping';
                                        }
                                    ?>
                                    <tr data-status="<?= strtolower($order['status']) ?>" 
                                        data-search-text="<?= strtolower($order['order_number'] . ' ' . ($order['customer_name'] ?? 'walk-in') . ' ' . ($order['table_number'] ?? '')) ?>"
                                        data-order-total="<?= htmlspecialchars($order['total_amount']) ?>">
                                        
                                        <td data-label="Order #">
                                            <span class="order-number">#<?= htmlspecialchars($order['order_number']) ?></span>
                                        </td>
                                        
                                        <td data-label="Customer">
                                            <div class="fw-medium" style="color: var(--navy);"><?= htmlspecialchars($order['customer_name'] ?? 'Walk-in') ?></div>
                                            <small class="text-muted"><?= date('M d, Y h:i A', strtotime($order['created_at'] ?? 'now')) ?></small>
                                            <?php if (!empty($order['table_number'])): ?>
                                                <div><small class="text-muted"><i class="fas fa-chair me-1"></i> Table <?= htmlspecialchars($order['table_number']) ?></small></div>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <td data-label="Order Type">
                                            <?php if ($orderType): ?>
                                                <span class="order-type-badge <?= $orderType ?>">
                                                    <i class="fas <?= $orderTypeIcon ?>"></i>
                                                    <?= $orderTypeLabel ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="order-type-missing">—</span>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <td data-label="Items">
                                            <ul class="order-items">
                                                <?php foreach ($order['items'] as $item): ?>
                                                    <li>
                                                        <span class="item-name"><?= htmlspecialchars($item['item_name']) ?></span>
                                                        <span class="item-qty">×<?= $item['quantity'] ?></span>
                                                        <?php if (!empty($item['addons'])): ?>
                                                            <ul class="addon-list">
                                                                <?php foreach ($item['addons'] as $addon): ?>
                                                                    <li><?= htmlspecialchars($addon['addon_name']) ?> ×<?= $addon['quantity'] ?></li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        <?php endif; ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </td>
                                        
                                        <td data-label="Subtotal">
                                            <span class="price">₱<?= number_format($order['subtotal'], 2) ?></span>
                                        </td>
                                        
                                        <td data-label="Total">
                                            <span class="price">₱<?= number_format($order['total_amount'], 2) ?></span>
                                        </td>
                                        
                                        <td data-label="Status">
                                            <span class="status-badge status-<?= strtolower($order['status']) ?>">
                                                <?= ucfirst(htmlspecialchars($order['status'])) ?>
                                            </span>
                                        </td>
                                        
                                        <td data-label="Actions">
                                            <div class="action-buttons">
                                                <button type="button"
                                                        class="btn btn-sm view-order-btn"
                                                        style="background: var(--lavender); color: var(--navy);"
                                                        data-order-id="<?= $order['order_id'] ?>"
                                                        data-order-number="<?= htmlspecialchars($order['order_number']) ?>">
                                                    <i class="fas fa-eye"></i> View Details
                                                </button>
                                                <?php if ($order['status'] !== 'cancelled' && $order['status'] !== 'completed'): ?>
                                                    
                                                    <?php
                                                    // =============================================
                                                    // ROLE-BASED STATUS RESTRICTIONS
                                                    // =============================================
                                                    // 1. NO ONE can change status from preparing back to pending
                                                    // 2. Kitchen can ONLY update from pending -> preparing
                                                    // 3. Kitchen CANNOT update to completed
                                                    // 4. Cashier can update from preparing -> completed (via payment)
                                                    // 5. Cashier CANNOT update from pending -> preparing
                                                    // 6. Completed orders CANNOT be cancelled
                                                    // 7. PREPARING orders CANNOT be cancelled (by ANYONE - cashier/admin)
                                                    // 8. Admin has full access but still cannot revert preparing to pending
                                                    // =============================================
                                                    
                                                    $currentStatus = $order['status'];
                                                    $canUpdate = true;
                                                    $disabledReason = '';
                                                    $allowedOptions = [];
                                                    
                                                    // Rule 1: NO ONE can change from preparing back to pending (universal)
                                                    if ($currentStatus === 'preparing') {
                                                        // Only allow completed, never allow pending
                                                        $allowedOptions = ['completed'];
                                                        if (!hasRole('admin') && !hasRole('cashier')) {
                                                            $canUpdate = false;
                                                            $disabledReason = 'Only cashier/admin can complete orders';
                                                        }
                                                    }
                                                    
                                                    // Rule 2 & 3: Kitchen restrictions
                                                    if (hasRole('kitchen') && !hasRole('admin')) {
                                                        if ($currentStatus === 'pending') {
                                                            // Kitchen can only change pending -> preparing
                                                            $allowedOptions = ['preparing'];
                                                        } else {
                                                            // Kitchen cannot modify orders that are already preparing
                                                            $canUpdate = false;
                                                            $disabledReason = 'Kitchen can only start pending orders';
                                                        }
                                                    }
                                                    
                                                    // Rule 4 & 5: Cashier restrictions (non-admin)
                                                    if (hasRole('cashier') && !hasRole('admin')) {
                                                        if ($currentStatus === 'preparing') {
                                                            // Cashier can complete preparing orders
                                                            $allowedOptions = ['completed'];
                                                        } elseif ($currentStatus === 'pending') {
                                                            // Cashier cannot modify pending orders (kitchen handles this)
                                                            $canUpdate = false;
                                                            $disabledReason = 'Cashier cannot modify pending orders';
                                                        }
                                                    }
                                                    
                                                    // Admin: can do almost everything but still cannot revert preparing to pending
                                                    if (hasRole('admin')) {
                                                        if ($currentStatus === 'preparing') {
                                                            // Admin cannot revert to pending (Rule 1)
                                                            $allowedOptions = ['completed'];
                                                        } elseif ($currentStatus === 'pending') {
                                                            // Admin can do both preparing and completed
                                                            $allowedOptions = ['preparing', 'completed'];
                                                        }
                                                    }
                                                    ?>
                                                    
                                                    <div class="status-form">
                                                        <?php if ($canUpdate && !empty($allowedOptions)): ?>
                                                            <select class="form-select status-select" 
                                                                    data-order-id="<?= $order['order_id'] ?>" 
                                                                    data-current-status="<?= $order['status'] ?>">
                                                                <?php if (in_array('preparing', $allowedOptions)): ?>
                                                                    <option value="preparing" <?= $currentStatus=='preparing'?'selected':'' ?>>Preparing</option>
                                                                <?php endif; ?>
                                                                <?php if (in_array('completed', $allowedOptions)): ?>
                                                                    <option value="completed" <?= $currentStatus=='completed'?'selected':'' ?>>Completed</option>
                                                                <?php endif; ?>
                                                            </select>
                                                            
                                                            <button type="button" class="btn btn-primary btn-sm update-status-btn" 
                                                                    data-order-id="<?= $order['order_id'] ?>" 
                                                                    data-order-number="<?= htmlspecialchars($order['order_number']) ?>">
                                                                <i class="fas fa-check"></i>
                                                                Update
                                                            </button>
                                                        <?php else: ?>
                                                            <button type="button" class="btn btn-secondary btn-sm" disabled 
                                                                    title="<?= htmlspecialchars($disabledReason) ?>">
                                                                <i class="fas fa-ban"></i>
                                                                <?= $disabledReason ?: 'Action not available' ?>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <?php 
                                                    // Rule 6: Completed orders CANNOT be cancelled
                                                    // Rule 7: PREPARING orders CANNOT be cancelled (by ANYONE - cashier/admin)
                                                    // Only show cancel button for PENDING orders
                                                    if ((hasRole('admin') || hasRole('cashier')) && $order['status'] === 'pending'): 
                                                    ?>
                                                    <button type="button" class="btn btn-danger btn-sm w-100 cancel-order-btn" 
                                                            data-order-id="<?= $order['order_id'] ?>" 
                                                            data-order-number="<?= htmlspecialchars($order['order_number']) ?>">
                                                        <i class="fas fa-times"></i>
                                                        Cancel Order
                                                    </button>
                                                    <?php elseif ($order['status'] === 'preparing'): ?>
                                                        <span class="badge bg-warning" style="padding: 0.5rem 1rem; border-radius: 30px; color: #1a202c;">
                                                            <i class="fas fa-info-circle me-1"></i> Cannot cancel - in preparation
                                                        </span>
                                                    <?php endif; ?>
                                                    
                                                <?php elseif ($order['status'] === 'completed'): ?>
                                                    <span class="badge bg-success" style="padding: 0.5rem 1rem; border-radius: 30px;">
                                                        <i class="fas fa-check-circle me-1"></i> Payment Received
                                                    </span>
                                                    <!-- CANNOT CANCEL COMPLETED ORDERS - no button shown -->
                                                <?php elseif ($order['status'] === 'cancelled'): ?>
                                                    <span class="badge bg-secondary" style="padding: 0.5rem 1rem; border-radius: 30px;">
                                                        <i class="fas fa-ban me-1"></i> Order Cancelled
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <div class="empty-state d-none" id="noResultsMessage">
                            <i class="fas fa-search"></i>
                            <h3>No Orders Found</h3>
                            <p>Try adjusting your filters or search terms.</p>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-receipt"></i>
                            <h3>No Orders Yet</h3>
                            <p>Orders will appear here once customers start placing them.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Payment Modal (for completing orders) -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">
                        <i class="fas fa-cash-register me-2" style="color: var(--secondary);"></i>
                        Process Payment
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="" id="paymentForm">
                    <div class="modal-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Order Number</h6>
                                <h4 class="order-number mb-0">#<span id="paymentOrderNumber"></span></h4>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <h6 class="text-muted mb-2">Total Amount</h6>
                                <h3 class="price mb-0">₱<span id="paymentTotalAmount">0.00</span></h3>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="paymentMethod" class="form-label fw-semibold">
                                    <i class="fas fa-money-bill-wave me-1"></i>
                                    Payment Method
                                </label>
                                <select class="form-select" id="paymentMethod" name="payment_method" required>
                                    <option value="cash" selected>Cash</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="amountPaid" class="form-label fw-semibold">
                                    <i class="fas fa-hand-holding-usd me-1"></i>
                                    Amount Paid <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" 
                                           class="form-control" 
                                           id="amountPaid" 
                                           name="amount_paid" 
                                           step="0.01" 
                                           min="0" 
                                           required
                                           placeholder="0.00">
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="alert alert-info d-flex align-items-center" role="alert" id="changeAlert" style="display: none !important;">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <div>
                                        <strong>Change to return:</strong> ₱<span id="changeAmount">0.00</span>
                                    </div>
                                </div>
                                <div class="alert alert-danger d-flex align-items-center" role="alert" id="insufficientAlert" style="display: none !important;">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <div>
                                        <strong>Insufficient amount!</strong> Please enter an amount greater than or equal to the total.
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <input type="hidden" name="order_id" id="paymentOrderId">
                        <input type="hidden" name="status" value="completed">
                        <input type="hidden" name="change_amount" id="changeAmountHidden">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>
                            Cancel
                        </button>
                        <button type="submit" name="update_status" class="btn btn-primary" id="confirmPaymentBtn" disabled>
                            <i class="fas fa-check-circle me-1"></i>
                            Confirm Payment & Complete Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Update Status Modal (for non-completed status changes) -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateStatusModalLabel">
                        <i class="fas fa-edit me-2" style="color: var(--warning);"></i>
                        Update Order Status
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="modal-icon primary">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <h5 class="mb-3">Confirm Status Update</h5>
                    <p class="text-muted mb-0">
                        Are you sure you want to update the status of <strong>Order <span id="updateOrderNumber"></span></strong> to <strong><span id="updateNewStatus"></span></strong>?
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        Cancel
                    </button>
                    <form method="POST" action="" id="updateStatusForm">
                        <input type="hidden" name="order_id" id="updateOrderId">
                        <input type="hidden" name="status" id="updateStatusValue">
                        <button type="submit" name="update_status" class="btn btn-primary">
                            <i class="fas fa-check me-1"></i>
                            Confirm Update
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Cancel Order Modal -->
    <div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelOrderModalLabel">
                        <i class="fas fa-exclamation-triangle me-2" style="color: var(--warning);"></i>
                        Cancel Order
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="modal-icon danger">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <h5 class="mb-3">Are you sure?</h5>
                    <p class="text-muted mb-0">
                        Do you really want to cancel <strong>Order <span id="cancelOrderNumber"></span></strong>? This action cannot be undone.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-arrow-left me-1"></i>
                        Go Back
                    </button>
                    <form method="POST" action="" id="cancelOrderForm">
                        <input type="hidden" name="order_id" id="cancelOrderId">
                        <button type="submit" name="cancel_order" class="btn btn-danger">
                            <i class="fas fa-times-circle me-1"></i>
                            Yes, Cancel Order
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- =============================================
     ORDER DETAILS MODAL
============================================= -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 28px; border: none; box-shadow: 0 25px 50px rgba(0,0,0,0.15);">

            <!-- Header -->
            <div class="modal-header" style="border-bottom: 1px solid var(--border); padding: 1.5rem 1.8rem; border-radius: 28px 28px 0 0; background: var(--primary); color: white;">
                <h5 class="modal-title d-flex align-items-center gap-2" style="color: white; font-size: 1.2rem; font-weight: 700;">
                    <i class="fas fa-receipt"></i>
                    Order Details — <span id="detailOrderNumber"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Body -->
            <div class="modal-body p-0">

                <!-- Loading spinner -->
                <div id="orderDetailsLoader" class="text-center py-5">
                    <div class="spinner-border" style="color: var(--primary); width: 2.5rem; height: 2.5rem;" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Fetching order details…</p>
                </div>

                <!-- Content (hidden until loaded) -->
                <div id="orderDetailsContent" style="display: none;">

                    <!-- Order Meta Row -->
                    <div style="background: var(--light-bg); padding: 1.4rem 1.8rem; border-bottom: 1px solid var(--border); display: flex; flex-wrap: wrap; gap: 1.5rem;">
                        <div>
                            <div style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--warm-gray); margin-bottom: 3px;">Customer</div>
                            <div style="font-weight: 600; color: var(--navy);" id="detailCustomer">—</div>
                        </div>
                        <div>
                            <div style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--warm-gray); margin-bottom: 3px;">Date & Time</div>
                            <div style="font-weight: 600; color: var(--navy);" id="detailDate">—</div>
                        </div>
                        <div>
                            <div style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--warm-gray); margin-bottom: 3px;">Cashier</div>
                            <div style="font-weight: 600; color: var(--navy);" id="detailCashier">—</div>
                        </div>
                        <div>
                            <div style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--warm-gray); margin-bottom: 3px;">Order Type</div>
                            <div id="detailOrderType">—</div>
                        </div>
                        <div>
                            <div style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--warm-gray); margin-bottom: 3px;">Status</div>
                            <div id="detailStatus">—</div>
                        </div>
                        <div id="detailTableWrap" style="display:none">
                            <div style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--warm-gray); margin-bottom: 3px;">Table</div>
                            <div style="font-weight: 600; color: var(--navy);" id="detailTable">—</div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div style="padding: 1.6rem 1.8rem 0;">
                        <div style="font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--warm-gray); margin-bottom: 0.9rem;">
                            <i class="fas fa-list me-1" style="color: var(--primary);"></i> Order Items
                        </div>
                        <table style="width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.88rem;">
                            <thead>
                                <tr>
                                    <th style="background: #f2f6fc; padding: 0.75rem 1rem; text-align: left; color: var(--primary-dark); font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.8px; border-bottom: 2px solid var(--primary-light); border-radius: 10px 0 0 0;">Item</th>
                                    <th style="background: #f2f6fc; padding: 0.75rem 1rem; text-align: center; color: var(--primary-dark); font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.8px; border-bottom: 2px solid var(--primary-light);">Qty</th>
                                    <th style="background: #f2f6fc; padding: 0.75rem 1rem; text-align: right; color: var(--primary-dark); font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.8px; border-bottom: 2px solid var(--primary-light);">Unit Price</th>
                                    <th style="background: #f2f6fc; padding: 0.75rem 1rem; text-align: right; color: var(--primary-dark); font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.8px; border-bottom: 2px solid var(--primary-light); border-radius: 0 10px 0 0;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="detailItemsBody">
                                <!-- injected by JS -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals & Payment -->
                    <div style="padding: 1.2rem 1.8rem 1.6rem; display: flex; flex-wrap: wrap; gap: 1.5rem; justify-content: flex-end;">

                        <!-- Payment info (only if paid) -->
                        <div id="detailPaymentSection" style="display:none; background: rgba(44,122,90,0.07); border: 1px solid rgba(44,122,90,0.18); border-radius: 18px; padding: 1rem 1.4rem; min-width: 200px;">
                            <div style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--success); margin-bottom: 0.6rem;">
                                <i class="fas fa-money-bill-wave me-1"></i> Payment
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 4px; font-size: 0.85rem;">
                                <span style="color: var(--warm-gray);">Method</span>
                                <span style="font-weight: 600; color: var(--navy);" id="detailPayMethod">—</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 4px; font-size: 0.85rem;">
                                <span style="color: var(--warm-gray);">Amount Paid</span>
                                <span style="font-weight: 600; color: var(--navy);">₱<span id="detailAmountPaid">0.00</span></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.85rem;">
                                <span style="color: var(--warm-gray);">Change</span>
                                <span style="font-weight: 600; color: var(--navy);">₱<span id="detailChange">0.00</span></span>
                            </div>
                        </div>

                        <!-- Totals box -->
                        <div style="background: var(--light-bg); border: 1px solid var(--border); border-radius: 18px; padding: 1rem 1.4rem; min-width: 230px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 0.85rem;">
                                <span style="color: var(--warm-gray);">Subtotal (excl. VAT)</span>
                                <span style="font-weight: 600; color: var(--navy);">₱<span id="detailSubtotal">0.00</span></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 0.85rem;">
                                <span style="color: var(--warm-gray);">VAT (12%)</span>
                                <span style="font-weight: 600; color: var(--navy);">₱<span id="detailVat">0.00</span></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding-top: 8px; border-top: 2px solid var(--border); font-size: 1rem;">
                                <span style="font-weight: 700; color: var(--navy);">Total</span>
                                <span style="font-weight: 700; color: var(--success); font-size: 1.1rem;">₱<span id="detailTotal">0.00</span></span>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Error state -->
                <div id="orderDetailsError" style="display: none; text-align: center; padding: 3rem 1.5rem; color: var(--danger);">
                    <i class="fas fa-exclamation-circle" style="font-size: 2.5rem; margin-bottom: 0.8rem; display: block;"></i>
                    <p class="fw-semibold mb-0">Failed to load order details. Please try again.</p>
                </div>

            </div>

            <!-- Footer -->
            <div class="modal-footer" style="border-top: 1px solid var(--border); padding: 1.2rem 1.8rem; border-radius: 0 0 28px 28px;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Close
                </button>
                <a id="printReceiptBtn"
                   href="#"
                   target="_blank"
                   class="btn btn-primary"
                   style="display:none;">
                    <i class="fas fa-print me-1"></i> Print Receipt
                </a>
            </div>

        </div>
    </div>
</div>
    <!-- Success Modal -->
    <?php if (!empty($_SESSION['success'])): ?>
    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <div class="modal-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h5 class="mb-3">Success!</h5>
                    <p class="text-muted mb-0"><?= htmlspecialchars($_SESSION['success']) ?></p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal">
                        <i class="fas fa-check me-1"></i>
                        OK
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <!-- Error Modal -->
    <?php if (!empty($_SESSION['error'])): ?>
    <div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <div class="modal-icon danger">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <h5 class="mb-3">Error</h5>
                    <p class="text-muted mb-0"><?= htmlspecialchars($_SESSION['error']) ?></p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-danger w-100" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // =============================================
// ORDER DETAILS MODAL – fetch via AJAX
// =============================================
const orderDetailsModal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));

const detailOrderNumber  = document.getElementById('detailOrderNumber');
const detailCustomer     = document.getElementById('detailCustomer');
const detailDate         = document.getElementById('detailDate');
const detailCashier      = document.getElementById('detailCashier');
const detailOrderType    = document.getElementById('detailOrderType');
const detailStatus       = document.getElementById('detailStatus');
const detailTableWrap    = document.getElementById('detailTableWrap');
const detailTable        = document.getElementById('detailTable');
const detailItemsBody    = document.getElementById('detailItemsBody');
const detailSubtotal     = document.getElementById('detailSubtotal');
const detailVat          = document.getElementById('detailVat');
const detailTotal        = document.getElementById('detailTotal');
const detailPaySection   = document.getElementById('detailPaymentSection');
const detailPayMethod    = document.getElementById('detailPayMethod');
const detailAmountPaid   = document.getElementById('detailAmountPaid');
const detailChange       = document.getElementById('detailChange');
const printReceiptBtn    = document.getElementById('printReceiptBtn');

const loaderEl  = document.getElementById('orderDetailsLoader');
const contentEl = document.getElementById('orderDetailsContent');
const errorEl   = document.getElementById('orderDetailsError');

function fmt(n) {
    return parseFloat(n || 0).toFixed(2);
}

function showLoader() {
    loaderEl.style.display  = 'block';
    contentEl.style.display = 'none';
    errorEl.style.display   = 'none';
}

function showContent() {
    loaderEl.style.display  = 'none';
    contentEl.style.display = 'block';
    errorEl.style.display   = 'none';
}

function showError() {
    loaderEl.style.display  = 'none';
    contentEl.style.display = 'none';
    errorEl.style.display   = 'block';
}

// Status badge helper
function statusBadgeHtml(status) {
    const map = {
        pending:   'status-pending',
        preparing: 'status-preparing',
        completed: 'status-completed',
        cancelled: 'status-cancelled'
    };
    const cls = map[status] || '';
    return `<span class="status-badge ${cls}">${status.charAt(0).toUpperCase() + status.slice(1)}</span>`;
}

// Order type badge helper
function orderTypeBadgeHtml(type) {
    if (!type) return '—';
    const icons = { dine_in: 'fa-utensils', takeout: 'fa-bag-shopping' };
    const labels = { dine_in: 'Dine In', takeout: 'Takeout' };
    return `<span class="order-type-badge ${type}">
                <i class="fas ${icons[type] || 'fa-question'}"></i>
                ${labels[type] || type}
            </span>`;
}

document.querySelectorAll('.view-order-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const orderId     = this.dataset.orderId;
        const orderNumber = this.dataset.orderNumber;

        // Reset modal
        showLoader();
        printReceiptBtn.style.display = 'none';
        detailOrderNumber.textContent = '#' + orderNumber;
        orderDetailsModal.show();

        // Fetch details — update the URL to match your routing
        fetch(`/coffee_shop/app/controllers/get_order_details.php?order_id=${orderId}`)
            .then(r => r.json())
            .then(res => {
                if (!res.success) { showError(); return; }

                const { order, items, payment } = res.data;

                // Meta
                detailCustomer.textContent  = order.customer_name || 'Walk-in';
                detailDate.textContent      = new Date(order.created_at).toLocaleString('en-PH', {
                    year: 'numeric', month: 'short', day: 'numeric',
                    hour: '2-digit', minute: '2-digit'
                });
                detailCashier.textContent   = order.cashier_name || 'N/A';
                detailOrderType.innerHTML   = orderTypeBadgeHtml(order.order_type);
                detailStatus.innerHTML      = statusBadgeHtml(order.status);

                if (order.table_number) {
                    detailTable.textContent  = 'Table ' + order.table_number;
                    detailTableWrap.style.display = '';
                } else {
                    detailTableWrap.style.display = 'none';
                }

                // Items
                detailItemsBody.innerHTML = '';
                items.forEach(item => {
                    const itemSub = (parseFloat(item.unit_price) * parseInt(item.quantity)).toFixed(2);
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td style="padding: 0.85rem 1rem; border-bottom: 1px solid #edf2f7; font-weight: 600; color: var(--navy);">${item.item_name}</td>
                        <td style="padding: 0.85rem 1rem; border-bottom: 1px solid #edf2f7; text-align: center; color: var(--warm-gray);">${item.quantity}</td>
                        <td style="padding: 0.85rem 1rem; border-bottom: 1px solid #edf2f7; text-align: right; color: var(--warm-gray);">₱${fmt(item.unit_price)}</td>
                        <td style="padding: 0.85rem 1rem; border-bottom: 1px solid #edf2f7; text-align: right; font-weight: 700; color: var(--success);">₱${itemSub}</td>
                    `;
                    detailItemsBody.appendChild(tr);

                    if (item.addons && item.addons.length > 0) {
                        item.addons.forEach(addon => {
                            const addonSub = (parseFloat(addon.unit_price) * parseInt(addon.quantity)).toFixed(2);
                            const addonTr = document.createElement('tr');
                            addonTr.innerHTML = `
                                <td style="padding: 0.5rem 1rem 0.5rem 2rem; border-bottom: 1px dashed #edf2f7; color: var(--warm-gray); font-size: 0.82rem;">
                                    <i class="fas fa-level-up-alt fa-rotate-90 me-1" style="color: var(--primary-light); font-size: 0.7rem;"></i>
                                    ${addon.addon_name}
                                </td>
                                <td style="padding: 0.5rem 1rem; border-bottom: 1px dashed #edf2f7; text-align: center; color: var(--warm-gray); font-size: 0.82rem;">${addon.quantity}</td>
                                <td style="padding: 0.5rem 1rem; border-bottom: 1px dashed #edf2f7; text-align: right; color: var(--warm-gray); font-size: 0.82rem;">₱${fmt(addon.unit_price)}</td>
                                <td style="padding: 0.5rem 1rem; border-bottom: 1px dashed #edf2f7; text-align: right; color: var(--warm-gray); font-size: 0.82rem;">₱${addonSub}</td>
                            `;
                            detailItemsBody.appendChild(addonTr);
                        });
                    }
                });

                // Totals
                const total    = parseFloat(order.total_amount || 0);
                const subtotal = total / 1.12;
                const vat      = total - subtotal;

                detailSubtotal.textContent = subtotal.toFixed(2);
                detailVat.textContent      = vat.toFixed(2);
                detailTotal.textContent    = total.toFixed(2);

                // Payment
                if (payment && order.status === 'completed') {
                    detailPaySection.style.display = '';
                    detailPayMethod.textContent    = payment.payment_method || '—';
                    detailAmountPaid.textContent   = fmt(payment.amount_paid);
                    detailChange.textContent       = fmt(payment.change_amount);
                } else {
                    detailPaySection.style.display = 'none';
                }

                // Print button – only for completed orders
                if (order.status === 'completed') {
                    printReceiptBtn.href          = `${window.location.pathname}?page=print_receipt&order_id=${orderId}`;
                    printReceiptBtn.style.display = '';
                } else {
                    printReceiptBtn.style.display = 'none';
                }

                showContent();
            })
            .catch(() => showError());
    });
});
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
        
        // Filter functionality
        let currentStatus = 'all';
        let currentSearch = '';
        
        document.querySelectorAll('.filter-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                currentStatus = this.dataset.status;
                updateFilterLabel();
                filterOrders();
            });
        });
        
        // Search functionality
        const searchInput = document.getElementById('searchInput');
        searchInput?.addEventListener('input', function() {
            currentSearch = this.value.toLowerCase();
            filterOrders();
        });
        
        // Filter orders based on status and search
        function filterOrders() {
            const rows = document.querySelectorAll('#ordersTableBody tr');
            let visibleCount = 0;
            
            rows.forEach(row => {
                const status = row.dataset.status;
                const searchText = row.dataset.searchText;
                
                const statusMatch = currentStatus === 'all' || status === currentStatus;
                const searchMatch = currentSearch === '' || searchText.includes(currentSearch);
                
                if (statusMatch && searchMatch) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            const table = document.querySelector('.orders-table');
            const noResults = document.getElementById('noResultsMessage');
            
            if (table && noResults) {
                if (visibleCount === 0) {
                    table.style.display = 'none';
                    noResults.classList.remove('d-none');
                } else {
                    table.style.display = '';
                    noResults.classList.add('d-none');
                }
            }
        }
        
        // Update filter label
        function updateFilterLabel() {
            const labelMap = {
                'all': 'All Orders',
                'pending': 'Pending Orders',
                'preparing': 'Preparing Orders',
                'completed': 'Completed Orders',
                'cancelled': 'Cancelled Orders'
            };
            document.getElementById('currentFilterLabel').textContent = labelMap[currentStatus] || 'Orders';
        }
        
        // Payment Modal functionality
        const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
        const amountPaidInput = document.getElementById('amountPaid');
        const changeAlert = document.getElementById('changeAlert');
        const insufficientAlert = document.getElementById('insufficientAlert');
        const changeAmountSpan = document.getElementById('changeAmount');
        const changeAmountHidden = document.getElementById('changeAmountHidden');
        const confirmPaymentBtn = document.getElementById('confirmPaymentBtn');
        
        let currentOrderTotal = 0;
        
        amountPaidInput?.addEventListener('input', function() {
            const amountPaid = parseFloat(this.value) || 0;
            
            if (amountPaid >= currentOrderTotal) {
                const change = amountPaid - currentOrderTotal;
                changeAmountSpan.textContent = change.toFixed(2);
                changeAmountHidden.value = change.toFixed(2);
                
                changeAlert.style.display = 'flex';
                insufficientAlert.style.display = 'none';
                confirmPaymentBtn.disabled = false;
            } else {
                changeAlert.style.display = 'none';
                insufficientAlert.style.display = 'flex';
                confirmPaymentBtn.disabled = true;
            }
        });
        
        // Update Status Modal
        const updateStatusModal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
        
        document.querySelectorAll('.update-status-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const orderId = this.dataset.orderId;
                const orderNumber = this.dataset.orderNumber;
                const select = document.querySelector(`.status-select[data-order-id="${orderId}"]`);
                const newStatus = select.value;
                const currentStatus = select.dataset.currentStatus;
                
                if (newStatus === currentStatus) {
                    return;
                }
                
                if (newStatus === 'completed') {
                    const orderRow = this.closest('tr');
                    const orderTotal = parseFloat(orderRow.dataset.orderTotal);
                    
                    currentOrderTotal = orderTotal;
                    
                    document.getElementById('paymentOrderNumber').textContent = orderNumber;
                    document.getElementById('paymentTotalAmount').textContent = orderTotal.toFixed(2);
                    document.getElementById('paymentOrderId').value = orderId;
                    
                    amountPaidInput.value = '';
                    changeAlert.style.display = 'none';
                    insufficientAlert.style.display = 'none';
                    confirmPaymentBtn.disabled = true;
                    
                    paymentModal.show();
                } else {
                    const newStatusText = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                    
                    document.getElementById('updateOrderNumber').textContent = orderNumber;
                    document.getElementById('updateNewStatus').textContent = newStatusText;
                    document.getElementById('updateOrderId').value = orderId;
                    document.getElementById('updateStatusValue').value = newStatus;
                    
                    updateStatusModal.show();
                }
            });
        });
        
        // Cancel Order Modal
        const cancelOrderModal = new bootstrap.Modal(document.getElementById('cancelOrderModal'));
        
        document.querySelectorAll('.cancel-order-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const orderId = this.dataset.orderId;
                const orderNumber = this.dataset.orderNumber;
                
                document.getElementById('cancelOrderNumber').textContent = orderNumber;
                document.getElementById('cancelOrderId').value = orderId;
                
                cancelOrderModal.show();
            });
        });
        
        // Show success/error modals if they exist
        <?php if (!empty($_SESSION['success']) || !empty($_SESSION['error'])): ?>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (!empty($_SESSION['success'])): ?>
            const successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();
            <?php endif; ?>
            <?php if (!empty($_SESSION['error'])): ?>
            const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
            errorModal.show();
            <?php endif; ?>
        });
        <?php endif; ?>
        
        // Ensure modal backdrop is properly removed
        document.addEventListener('hidden.bs.modal', function () {
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        });
    </script>
</body>
</html>