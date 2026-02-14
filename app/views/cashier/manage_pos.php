<?php
// Pure view: expects $menu_items, $addons_map, $categories, $_SESSION['success'], $_SESSION['error']
// Also expects $stock_data from controller with detailed ingredient info
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5">
    <title>POS - Cafeteria POS</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* Your existing styles remain the same */
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
        
        /* ------ CATEGORY TABS – modern & clean ------ */
        .category-tabs {
            display: flex;
            gap: 0.65rem;
            margin-bottom: 1.8rem;
            flex-wrap: wrap;
        }
        
        .category-tab {
            padding: 0.6rem 1.2rem;
            border: 1px solid var(--border);
            background: white;
            color: var(--warm-gray);
            border-radius: 40px;
            cursor: pointer;
            transition: all 0.2s;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .category-tab:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: #f2f6fc;
        }
        
        .category-tab.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        /* ------ MENU GRID – clean card design ------ */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .menu-item-card {
            background: white;
            border: 1px solid rgba(203, 213, 224, 0.5);
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.25s;
            cursor: pointer;
            position: relative;
            box-shadow: 0 4px 12px rgba(15, 76, 92, 0.02);
        }
        
        .menu-item-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(15, 76, 92, 0.08);
            border-color: var(--primary-light);
        }
        
        .menu-item-card.out-of-stock {
            opacity: 0.6;
            cursor: not-allowed;
            filter: grayscale(0.5);
        }
        
        .menu-item-card.out-of-stock:hover {
            transform: none;
            box-shadow: 0 4px 12px rgba(15, 76, 92, 0.02);
        }
        
        .menu-item-image {
            width: 100%;
            height: 160px;
            object-fit: cover;
            background: #f2f6fc;
        }
        
        .menu-item-placeholder {
            width: 100%;
            height: 160px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(145deg, #f2f6fc, white);
            color: var(--primary-light);
            font-size: 3rem;
        }
        
        .menu-item-body {
            padding: 1.2rem;
        }
        
        .menu-item-name {
            font-weight: 700;
            font-size: 1rem;
            color: var(--navy);
            margin-bottom: 0.35rem;
        }
        
        .menu-item-price {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--success);
            margin-bottom: 0.5rem;
        }
        
        .menu-item-description {
            font-size: 0.75rem;
            color: var(--warm-gray);
            margin-bottom: 0.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .out-of-stock-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--danger);
            color: white;
            padding: 0.35rem 1rem;
            border-radius: 40px;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 8px rgba(214, 64, 69, 0.2);
            z-index: 5;
        }
        
        .low-stock-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--warning);
            color: #1a202c;
            padding: 0.35rem 1rem;
            border-radius: 40px;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 8px rgba(244, 185, 66, 0.2);
            z-index: 5;
        }
        
        /* ------ CART – sticky, clean ------ */
        .cart-container {
            position: sticky;
            top: calc(var(--topbar-height) + 2rem);
        }
        
        .cart-items {
            max-height: 350px;
            overflow-y: auto;
            margin-bottom: 1rem;
        }
        
        .cart-items::-webkit-scrollbar {
            width: 5px;
        }
        
        .cart-items::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 10px;
        }
        
        .cart-item {
            padding: 1.2rem;
            border-bottom: 1px solid #edf2f7;
            transition: background-color 0.2s;
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        .cart-item.insufficient-stock {
            background-color: rgba(214, 64, 69, 0.04);
            border-left: 4px solid var(--danger);
        }
        
        .cart-item-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 0.5rem;
        }
        
        .cart-item-name {
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--navy);
        }
        
        .cart-item-remove {
            background: none;
            border: none;
            color: var(--danger);
            cursor: pointer;
            padding: 0.25rem;
            font-size: 1rem;
            transition: all 0.15s;
        }
        
        .cart-item-remove:hover {
            transform: scale(1.1);
            color: #b83b41;
        }
        
        .cart-item-price {
            color: var(--success);
            font-weight: 700;
            font-size: 0.9rem;
        }
        
        .cart-item-qty {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-top: 0.75rem;
        }
        
        .qty-btn {
            width: 30px;
            height: 30px;
            border: 1px solid var(--border);
            background: white;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s;
            color: var(--warm-gray);
        }
        
        .qty-btn:hover:not(:disabled) {
            border-color: var(--primary);
            background: var(--primary);
            color: white;
        }
        
        .qty-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }
        
        .qty-input {
            width: 55px;
            text-align: center;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 0.35rem;
            font-weight: 600;
        }
        
        .qty-input:disabled {
            background: #f2f6fc;
            opacity: 0.7;
        }
        
        .addon-item {
            font-size: 0.75rem;
            color: var(--warm-gray);
            padding-left: 0.75rem;
            margin-top: 0.35rem;
            border-left: 2px solid var(--primary-light);
        }
        
        .cart-summary {
            padding: 1.2rem;
            background: #f2f6fc;
            border-radius: 18px;
            margin-bottom: 1.2rem;
        }
        
        .cart-summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .cart-summary-row.total {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--navy);
            padding-top: 0.75rem;
            border-top: 2px solid var(--border);
            margin-top: 0.75rem;
            margin-bottom: 0;
        }
        
        .empty-cart {
            text-align: center;
            padding: 2.5rem 1rem;
            color: var(--warm-gray);
        }
        
        .empty-cart i {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            color: var(--border);
        }
        
        /* ------ ORDER TYPE SELECTOR – modern toggle ------ */
        .order-type-container {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }
        
        .order-type-btn {
            flex: 1;
            padding: 0.75rem;
            border: 1.5px solid var(--border);
            background: white;
            color: var(--warm-gray);
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.2s;
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
        }
        
        .order-type-btn i {
            font-size: 1.1rem;
        }
        
        .order-type-btn:hover {
            border-color: var(--primary);
            background: #f2f6fc;
            color: var(--primary);
        }
        
        .order-type-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .order-type-btn.active i {
            color: white;
        }
        
        /* ------ FORM STYLING – inside cart ------ */
        .form-label {
            font-weight: 600;
            color: var(--navy);
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
        }
        
        .form-control {
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 0.65rem 1rem;
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(15, 76, 92, 0.1);
        }
        
        /* ------ BUTTONS – modern & clean ------ */
        .btn {
            padding: 0.65rem 1.5rem;
            border-radius: 40px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.18s;
            letter-spacing: 0.3px;
        }
        
        .btn-primary {
            background: var(--primary);
            border: none;
            color: white;
        }
        
        .btn-primary:hover:not(:disabled) {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(15, 76, 92, 0.2);
        }
        
        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .btn-success {
            background: var(--success);
            border: none;
            color: white;
        }
        
        .btn-success:hover:not(:disabled) {
            background: #1f5d43;
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(44, 122, 90, 0.2);
        }
        
        .btn-success:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .btn-outline-secondary {
            border: 1.5px solid var(--border);
            color: var(--warm-gray);
        }
        
        .btn-outline-secondary:hover {
            background: var(--lavender);
            border-color: var(--warm-gray);
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
        
        /* ------ ADDON CHECKBOX – clean & modern ------ */
        .addon-checkbox {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.9rem;
            border: 1px solid var(--border);
            border-radius: 16px;
            margin-bottom: 0.75rem;
            transition: all 0.2s;
        }
        
        .addon-checkbox:hover {
            background: #f2f6fc;
            border-color: var(--primary-light);
        }
        
        .addon-checkbox input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: var(--primary);
        }
        
        .addon-checkbox label {
            flex: 1;
            cursor: pointer;
            margin: 0;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .addon-qty-control {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .addon-qty-control input {
            width: 70px;
            text-align: center;
            padding: 0.35rem;
            border: 1px solid var(--border);
            border-radius: 12px;
        }
        
        /* ------ STOCK WARNING – inside cart ------ */
        .stock-warning {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            background: rgba(244, 185, 66, 0.12);
            color: #b87a1a;
            border-radius: 12px;
            font-size: 0.75rem;
            margin-top: 0.5rem;
        }
        
        .stock-warning i {
            font-size: 0.875rem;
        }
        
        .stock-warning-danger {
            background: rgba(214, 64, 69, 0.08);
            color: var(--danger);
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
        
        /* ------ RESPONSIVE – even & clean ------ */
        @media (max-width: 1199.98px) {
            .menu-grid { grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); }
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
            .cart-container {
                position: static;
                margin-top: 2rem;
            }
        }
        
        @media (max-width: 767.98px) {
            .main-content { padding: 1.2rem; }
            .page-header h1 { font-size: 1.8rem; }
            .menu-grid { grid-template-columns: 1fr; }
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
                <a href="<?= $baseUrl ?>/index.php?page=manage_pos" class="nav-link active">
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
                <a href="<?= $baseUrl ?>/index.php?page=manage_reports" class="nav-link">
                    <i class="fas fa-chart-bar"></i>
                    Reports
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- MAIN CONTENT -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1>
                    <i class="fas fa-cash-register me-2"></i>
                    Point of Sale
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/index.php?page=admin_dashboard">Home</a></li>
                        <li class="breadcrumb-item active">POS</li>
                    </ol>
                </nav>
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
        
        <div class="row g-4">
            <!-- Menu Items -->
            <div class="col-lg-8">
                <div class="card fade-in">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-utensils me-2" style="color: var(--primary);"></i>
                            Menu Items
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Category Filter -->
                        <div class="category-tabs">
                            <button class="category-tab active" data-category="all">
                                <i class="fas fa-th me-1"></i> All Items
                            </button>
                            <?php 
                            $uniqueCategories = [];
                            foreach ($menu_items as $item) {
                                if (!isset($uniqueCategories[$item['category_id']])) {
                                    $uniqueCategories[$item['category_id']] = $item['category_name'];
                                }
                            }
                            foreach ($uniqueCategories as $catId => $catName): 
                            ?>
                                <button class="category-tab" data-category="<?= $catId ?>">
                                    <?= htmlspecialchars($catName) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Menu Grid -->
                        <div class="menu-grid" id="menuGrid">
                            <?php foreach ($menu_items as $item): 
                                // Check initial stock status for display
                                $hasStock = checkMenuItemStock($pdo, $item['item_id'], 1);
                            ?>
                                <div class="menu-item-card <?= !$hasStock ? 'out-of-stock' : '' ?>" 
                                     data-category="<?= $item['category_id'] ?>"
                                     data-item-id="<?= $item['item_id'] ?>"
                                     data-item-name="<?= htmlspecialchars($item['item_name']) ?>"
                                     data-item-price="<?= $item['price'] ?>"
                                     data-category-id="<?= $item['category_id'] ?>"
                                     data-has-stock="<?= $hasStock ? 'true' : 'false' ?>">
                                    
                                    <?php if (!$hasStock): ?>
                                        <div class="out-of-stock-badge">Out of Stock</div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($item['image_url'])): ?>
                                        <img src="<?= $baseUrl . '/' . htmlspecialchars($item['image_url']) ?>" 
                                             alt="<?= htmlspecialchars($item['item_name']) ?>" 
                                             class="menu-item-image">
                                    <?php else: ?>
                                        <div class="menu-item-placeholder">
                                            <i class="fas fa-utensils"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="menu-item-body">
                                        <div class="menu-item-name"><?= htmlspecialchars($item['item_name']) ?></div>
                                        <div class="menu-item-price">₱<?= number_format($item['price'], 2) ?></div>
                                        
                                        <?php if (!empty($item['ingredients'])): ?>
                                            <div class="menu-item-description">
                                                <i class="fas fa-leaf me-1"></i>
                                                <?php 
                                                $ingredientNames = array_map(function($ing) {
                                                    return $ing['ingredient_name'];
                                                }, $item['ingredients']);
                                                echo htmlspecialchars(implode(', ', array_slice($ingredientNames, 0, 3)));
                                                if (count($ingredientNames) > 3) echo '...';
                                                ?>
                                            </div>
                                        <?php elseif (!empty($item['description'])): ?>
                                            <div class="menu-item-description"><?= htmlspecialchars($item['description']) ?></div>
                                        <?php endif; ?>
                                        
                                        <!-- Show beverage icon for categories that can have add-ons -->
                                        <?php if (in_array($item['category_id'], [1,2,3,4,5,6])): ?>
                                            <div class="mt-2">
                                                <span class="badge bg-info text-white">
                                                    <i class="fas fa-coffee me-1"></i> Add-ons available
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Cart & Checkout -->
            <div class="col-lg-4">
                <div class="cart-container">
                    <div class="card fade-in" style="animation-delay: 0.1s">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-shopping-cart me-2" style="color: var(--primary);"></i>
                                Current Order
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- ORDER TYPE - Dine In / Take Out -->
                            <div class="mb-4">
                                <label class="form-label">Order Type <span class="text-danger">*</span></label>
                                <div class="order-type-container">
                                    <button type="button" class="order-type-btn active" id="dineInBtn" onclick="setOrderType('dine_in')">
                                        <i class="fas fa-utensils"></i>
                                        Dine In
                                    </button>
                                    <button type="button" class="order-type-btn" id="takeOutBtn" onclick="setOrderType('takeout')">
                                        <i class="fas fa-bag-shopping"></i>
                                        Take Out
                                    </button>
                                </div>
                                <input type="hidden" id="order_type" name="order_type" value="dine_in">
                            </div>
                            
                            <!-- Customer Info -->
                            <div class="mb-3">
                                <label for="customer_name" class="form-label">Customer Name</label>
                                <input type="text" class="form-control" id="customer_name" placeholder="e.g. John Cena">
                            </div>
                            
                            <div class="mb-3" id="tableNumberContainer">
                                <label for="table_number" class="form-label">Table Number</label>
                                <input type="text" class="form-control" id="table_number" placeholder="e.g. 5">
                            </div>
                            <hr>
                            
                            <!-- Cart Items -->
                            <div class="cart-items" id="cartItems">
                                <div class="empty-cart">
                                    <i class="fas fa-shopping-cart"></i>
                                    <p>No items in cart</p>
                                    <small class="text-muted">Click on a menu item to start</small>
                                </div>
                            </div>
                            
                            <!-- Cart Summary -->
                            <div class="cart-summary d-none" id="cartSummary">
                                <div class="cart-summary-row">
                                    <span>Subtotal:</span>
                                    <span id="subtotal">₱0.00</span>
                                </div>
                                <div class="cart-summary-row">
                                    <span>VAT (12%):</span>
                                    <span id="vatAmount">₱0.00</span>
                                </div>
                                <div class="cart-summary-row total">
                                    <span>Total:</span>
                                    <span id="total">₱0.00</span>
                                </div>
                            </div>
                            
                            <!-- Submit Button -->
                            <button type="button" class="btn btn-success w-100 d-none" id="submitOrderBtn" disabled>
                                <i class="fas fa-check me-2"></i>
                                Submit Order
                            </button>
                            <div id="submitDisabledMessage" class="text-danger small mt-2 text-center d-none">
                                <i class="fas fa-exclamation-circle me-1"></i>
                                Fix stock issues to submit order
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add-ons Modal - Now available for ALL menu items -->
    <div class="modal fade" id="addonsModal" tabindex="-1" aria-labelledby="addonsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addonsModalLabel">
                        <i class="fas fa-utensils me-2" style="color: var(--secondary);"></i>
                        <span id="modalItemName"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="addonsContent">
                    <!-- Add-ons will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="addToCartBtn">
                        <i class="fas fa-cart-plus me-2"></i>
                        Add to Cart
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
// ============================================================
// POS SYSTEM - FULL STOCK VALIDATION WITH DETAILED INGREDIENT INFO
// ============================================================

// Available add-ons data (mapped by item_id)
const addonsMap = <?= json_encode($addons_map ?? []) ?>;

// ============================================================
// REAL STOCK DATA FROM PHP - INCLUDES DETAILED INGREDIENT INFO
// ============================================================
const stockData = <?= json_encode($stock_data ?? []) ?>;

// ============================================================
// ADD-ON STOCK DATA
// ============================================================
const addonStockData = <?= json_encode($addon_stock_data ?? []) ?>;

// Cart state
let cart = [];
let currentItem = null;
let selectedAddons = [];
let currentOrderType = 'dine_in';

// Track which items have stock issues
let hasStockIssues = false;

// ============================================================
// ERROR MODAL FUNCTIONS - NO ALERTS, ONLY MODALS
// ============================================================

/**
 * Show error modal with custom message and ingredient details
 */
function showStockErrorModal(itemName, missingIngredients) {
    // Check if error modal exists, if not create it
    let errorModalEl = document.getElementById('globalErrorModal');
    
    if (!errorModalEl) {
        // Create the modal HTML
        const modalHTML = `
            <div class="modal fade" id="globalErrorModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-exclamation-triangle me-2" style="color: var(--danger);"></i>
                                Insufficient Stock
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center">
                            <div class="modal-icon danger mb-3">
                                <i class="fas fa-boxes"></i>
                            </div>
                            <h5 class="mb-3" id="errorModalTitle">Cannot add to cart</h5>
                            <p class="text-muted mb-2" id="errorModalMessage"></p>
                            <div class="text-start mt-3 p-3 bg-light rounded" id="ingredientListContainer" style="max-height: 200px; overflow-y: auto;">
                                <small class="text-uppercase fw-bold text-muted">Missing Ingredients:</small>
                                <ul class="list-unstyled mt-2" id="ingredientList"></ul>
                            </div>
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
        `;
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        errorModalEl = document.getElementById('globalErrorModal');
    }
    
    // Set the title and message
    document.getElementById('errorModalTitle').textContent = `Cannot add "${itemName}"`;
    document.getElementById('errorModalMessage').innerHTML = `This item cannot be added due to insufficient ingredients:`;
    
    // Build ingredient list
    const ingredientList = document.getElementById('ingredientList');
    ingredientList.innerHTML = '';
    
    missingIngredients.forEach(ing => {
        const li = document.createElement('li');
        li.className = 'mb-2 pb-1 border-bottom';
        li.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <span><i class="fas fa-leaf me-2" style="color: var(--danger);"></i> ${ing.name}</span>
                <span class="badge bg-danger">Need: ${ing.required} | Available: ${ing.available}</span>
            </div>
        `;
        ingredientList.appendChild(li);
    });
    
    // Show the modal
    const errorModal = new bootstrap.Modal(errorModalEl);
    errorModal.show();
}

/**
 * Show generic error modal (for non-stock errors)
 */
function showErrorModal(title, message) {
    // Check if error modal exists, if not create it
    let errorModalEl = document.getElementById('globalErrorModal');
    
    if (!errorModalEl) {
        // Create the modal HTML
        const modalHTML = `
            <div class="modal fade" id="globalErrorModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-exclamation-triangle me-2" style="color: var(--danger);"></i>
                                Error
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center">
                            <div class="modal-icon danger mb-3">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                            <h5 class="mb-3" id="errorModalTitle">Error</h5>
                            <p class="text-muted mb-0" id="errorModalMessage"></p>
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
        `;
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        errorModalEl = document.getElementById('globalErrorModal');
    }
    
    // Hide ingredient list container if it exists
    const ingredientContainer = document.getElementById('ingredientListContainer');
    if (ingredientContainer) {
        ingredientContainer.style.display = 'none';
    }
    
    // Set the title and message
    document.getElementById('errorModalTitle').textContent = title;
    document.getElementById('errorModalMessage').textContent = message;
    
    // Show the modal
    const errorModal = new bootstrap.Modal(errorModalEl);
    errorModal.show();
}

/**
 * Show add-on stock error modal
 */
function showAddonStockErrorModal(itemName, insufficientAddons) {
    // Check if error modal exists, if not create it
    let errorModalEl = document.getElementById('addonErrorModal');
    
    if (!errorModalEl) {
        // Create the modal HTML
        const modalHTML = `
            <div class="modal fade" id="addonErrorModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-exclamation-triangle me-2" style="color: var(--danger);"></i>
                                Add-on Stock Issue
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center">
                            <div class="modal-icon danger mb-3">
                                <i class="fas fa-boxes"></i>
                            </div>
                            <h5 class="mb-3" id="addonErrorTitle">Cannot add add-ons</h5>
                            <p class="text-muted mb-2" id="addonErrorMessage"></p>
                            <div class="text-start mt-3 p-3 bg-light rounded" id="addonListContainer" style="max-height: 200px; overflow-y: auto;">
                                <small class="text-uppercase fw-bold text-muted">Insufficient Add-ons:</small>
                                <ul class="list-unstyled mt-2" id="addonErrorList"></ul>
                            </div>
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
        `;
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        errorModalEl = document.getElementById('addonErrorModal');
    }
    
    // Set the title and message
    document.getElementById('addonErrorTitle').textContent = `Cannot add add-ons to "${itemName}"`;
    document.getElementById('addonErrorMessage').innerHTML = `The following add-ons have insufficient ingredients:`;
    
    // Build add-on list
    const addonList = document.getElementById('addonErrorList');
    addonList.innerHTML = '';
    
    insufficientAddons.forEach(addon => {
        const li = document.createElement('li');
        li.className = 'mb-2 pb-1 border-bottom';
        li.innerHTML = `
            <div class="d-flex flex-column">
                <span><i class="fas fa-leaf me-2" style="color: var(--danger);"></i> <strong>${addon.name}</strong></span>
                <small class="text-danger mt-1">${addon.message || `Need: ${addon.required} | Available: ${addon.available}`}</small>
            </div>
        `;
        addonList.appendChild(li);
    });
    
    // Show the modal
    const errorModal = new bootstrap.Modal(errorModalEl);
    errorModal.show();
}

// ============================================================
// STOCK CHECKING FUNCTIONS - WITH DETAILED INGREDIENT INFO
// ============================================================

/**
 * Check if a specific quantity of an item is available
 * Returns { success: boolean, missingIngredients: array }
 */
function checkItemStockAvailability(itemId, quantity) {
    if (!stockData[itemId]) {
        console.error('No stock data for item:', itemId);
        return { 
            success: false, 
            missingIngredients: [{ name: 'Unknown', required: 0, available: 0 }] 
        };
    }
    
    const itemStock = stockData[itemId];
    
    // Check if requested quantity exceeds maximum available
    if (quantity > itemStock.max_quantity) {
        // Find which ingredient is limiting
        const limitingIngredient = itemStock.limiting_ingredient || { 
            name: 'Stock limit', 
            required: quantity, 
            available: itemStock.max_quantity 
        };
        
        return { 
            success: false, 
            missingIngredients: [{
                name: limitingIngredient.name || 'Multiple ingredients',
                required: quantity,
                available: itemStock.max_quantity
            }] 
        };
    }
    
    // Check each ingredient
    if (itemStock.ingredients) {
        const missing = [];
        for (const ing of itemStock.ingredients) {
            const required = ing.quantity_required * quantity;
            if (ing.stock_quantity < required) {
                missing.push({
                    name: ing.ingredient_name,
                    required: required,
                    available: ing.stock_quantity
                });
            }
        }
        
        if (missing.length > 0) {
            return { success: false, missingIngredients: missing };
        }
    }
    
    return { success: true, missingIngredients: [] };
}

/**
 * Check if add-ons have sufficient stock
 */
function checkAddonsStock(addons) {
    const insufficientAddons = [];
    
    for (const addon of addons) {
        const addonStock = addonStockData[addon.id];
        
        if (!addonStock) {
            insufficientAddons.push({
                name: addon.name,
                required: addon.quantity,
                available: 0,
                message: 'Add-on not found'
            });
            continue;
        }
        
        if (addon.quantity > addonStock.max_quantity) {
            insufficientAddons.push({
                name: addon.name,
                required: addon.quantity,
                available: addonStock.max_quantity,
                ingredient: addonStock.ingredient_name,
                message: `Only ${addonStock.max_quantity} available (needs ${addonStock.quantity_used} ${addonStock.ingredient_name} each)`
            });
        }
    }
    
    return {
        success: insufficientAddons.length === 0,
        insufficientAddons: insufficientAddons
    };
}

/**
 * Check if we can increase the quantity of an existing cart item
 */
function canIncreaseQuantity(cartItem, newQuantity) {
    return checkItemStockAvailability(cartItem.id, newQuantity).success;
}

/**
 * Get the maximum available quantity for an item
 */
function getMaxQuantity(itemId) {
    if (!stockData[itemId]) return 0;
    return stockData[itemId].max_quantity;
}

/**
 * Calculate total for a single cart item including add-ons
 */
function calculateItemTotal(item) {
    const itemTotal = item.price * item.quantity;
    const addonsTotal = item.addons.reduce((sum, addon) => {
        return sum + (addon.price * addon.quantity * item.quantity);
    }, 0);
    return itemTotal + addonsTotal;
}

/**
 * Calculate subtotal of all items in cart
 */
function calculateSubtotal() {
    return cart.reduce((sum, item) => {
        return sum + calculateItemTotal(item);
    }, 0);
}

/**
 * Calculate VAT (12%) and total
 */
function calculateTotals() {
    const subtotal = calculateSubtotal();
    const vat = subtotal * 0.12;
    const total = subtotal + vat;
    return { subtotal, vat, total };
}

/**
 * Validate all items in cart and update UI
 */
function validateCartStock() {
    let issues = false;
    
    cart.forEach((item) => {
        const check = checkItemStockAvailability(item.id, item.quantity);
        if (!check.success) {
            issues = true;
            item.hasStockIssue = true;
        } else {
            item.hasStockIssue = false;
        }
    });
    
    hasStockIssues = issues;
    
    // Update submit button state
    const submitBtn = document.getElementById('submitOrderBtn');
    const submitMsg = document.getElementById('submitDisabledMessage');
    
    if (cart.length === 0) {
        submitBtn.disabled = true;
        submitBtn.classList.add('d-none');
        submitMsg.classList.add('d-none');
    } else {
        submitBtn.classList.remove('d-none');
        if (hasStockIssues) {
            submitBtn.disabled = true;
            submitMsg.classList.remove('d-none');
        } else {
            submitBtn.disabled = false;
            submitMsg.classList.add('d-none');
        }
    }
    
    renderCart();
}

// ============================================================
// ORDER TYPE FUNCTIONS
// ============================================================

// Set order type
function setOrderType(type) {
    currentOrderType = type;
    document.getElementById('order_type').value = type;
    
    const dineInBtn = document.getElementById('dineInBtn');
    const takeOutBtn = document.getElementById('takeOutBtn');
    const tableNumberContainer = document.getElementById('tableNumberContainer');
    
    if (type === 'dine_in') {
        dineInBtn.classList.add('active');
        takeOutBtn.classList.remove('active');
        tableNumberContainer.style.display = 'block';
    } else {
        takeOutBtn.classList.add('active');
        dineInBtn.classList.remove('active');
        tableNumberContainer.style.display = 'none';
        document.getElementById('table_number').value = '';
    }
}

// ============================================================
// CART OPERATIONS WITH IMMEDIATE STOCK VALIDATION
// ============================================================

// Load cart from localStorage on page load
document.addEventListener('DOMContentLoaded', () => {
    const savedCart = localStorage.getItem('cart');
    if (savedCart) {
        cart = JSON.parse(savedCart);
        // Recalculate stock issues on load
        cart.forEach(item => {
            const check = checkItemStockAvailability(item.id, item.quantity);
            item.hasStockIssue = !check.success;
        });
        validateCartStock();
    }
    setOrderType('dine_in');
    
    // Update all menu cards with real stock data
    document.querySelectorAll('.menu-item-card').forEach(card => {
        const itemId = card.dataset.itemId;
        if (stockData[itemId]) {
            card.dataset.hasStock = stockData[itemId].has_stock ? 'true' : 'false';
            if (!stockData[itemId].has_stock) {
                card.classList.add('out-of-stock');
                // Add badge if not already present
                if (!card.querySelector('.out-of-stock-badge')) {
                    const badge = document.createElement('div');
                    badge.className = 'out-of-stock-badge';
                    badge.textContent = 'Out of Stock';
                    card.appendChild(badge);
                }
            }
        }
    });
});

// Save cart to localStorage
function saveCart() {
    localStorage.setItem('cart', JSON.stringify(cart));
}

// Mobile menu toggle
const menuToggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');
const sidebarOverlay = document.getElementById('sidebarOverlay');

if (menuToggle) {
    menuToggle.addEventListener('click', () => {
        sidebar.classList.toggle('show');
        sidebarOverlay.classList.toggle('show');
    });
}

if (sidebarOverlay) {
    sidebarOverlay.addEventListener('click', () => {
        sidebar.classList.remove('show');
        sidebarOverlay.classList.remove('show');
    });
}

// Category filter
document.querySelectorAll('.category-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        
        const category = this.dataset.category;
        document.querySelectorAll('.menu-item-card').forEach(card => {
            card.style.display = (category === 'all' || card.dataset.category === category) ? 'block' : 'none';
        });
    });
});

// ============================================================
// MENU ITEM CLICK - ALWAYS SHOW MODAL WITH STOCK CHECK
// ============================================================
document.querySelectorAll('.menu-item-card').forEach(card => {
    card.addEventListener('click', function(e) {
        const itemId = this.dataset.itemId;
        const itemName = this.dataset.itemName;
        const itemPrice = parseFloat(this.dataset.itemPrice);
        const categoryId = parseInt(this.dataset.categoryId);
        
        // Define beverage categories (must match PHP categories 1-6)
        const beverageCategories = [1, 2, 3, 4, 5, 6];
        const isBeverage = beverageCategories.includes(categoryId);
        
        // STOCK VALIDATION: Check REAL stock data with detailed info
        const stockCheck = checkItemStockAvailability(itemId, 1);
        
        if (!stockCheck.success) {
            e.preventDefault();
            e.stopPropagation();
            
            // Show detailed stock error modal with missing ingredients
            showStockErrorModal(itemName, stockCheck.missingIngredients);
            return;
        }
        
        // Update card's has-stock attribute to match real data
        this.dataset.hasStock = stockData[itemId]?.has_stock ? 'true' : 'false';
        
        currentItem = {
            id: itemId,
            name: itemName,
            price: itemPrice,
            maxQty: stockData[itemId]?.max_quantity || 0,
            categoryId: categoryId,
            isBeverage: isBeverage
        };
        selectedAddons = [];

        // Get add-ons for this item
        const itemAddons = addonsMap[itemId] || [];
        const activeAddons = itemAddons.filter(addon => addon.is_active);
        
        // Update modal title
        const modalTitle = document.getElementById('addonsModalLabel');
        const modalItemNameSpan = document.getElementById('modalItemName');
        modalItemNameSpan.textContent = itemName;
        
        if (activeAddons.length > 0) {
            // Item has add-ons - show add-on selection
            modalTitle.innerHTML = `
                <i class="fas fa-plus-circle me-2" style="color: var(--secondary);"></i>
                Customize <span id="modalItemName">${itemName}</span>
            `;
            
            // Build add-ons HTML with stock info
            let addonsHTML = `
                <div class="mb-4">
                    <h6 class="fw-bold mb-3">Available Add-ons:</h6>
            `;
            
            activeAddons.forEach(addon => {
                const addonStock = addonStockData[addon.addon_id] || { max_quantity: 0, has_stock: false };
                const hasStock = addonStock.max_quantity > 0;
                const maxQty = addonStock.max_quantity || 0;
                
                addonsHTML += `
                    <div class="addon-checkbox ${!hasStock ? 'opacity-50' : ''}">
                        <input type="checkbox" id="addon_${addon.addon_id}" value="${addon.addon_id}" 
                               data-name="${addon.addon_name}" data-price="${addon.price}"
                               onchange="toggleAddonQty(${addon.addon_id})"
                               ${!hasStock ? 'disabled' : ''}>
                        <label for="addon_${addon.addon_id}" class="${!hasStock ? 'text-muted' : ''}">
                            ${addon.addon_name} 
                            <span class="text-success fw-semibold">(+₱${parseFloat(addon.price).toFixed(2)})</span>
                            ${!hasStock ? '<span class="badge bg-danger ms-2">Out of Stock</span>' : ''}
                            ${hasStock && maxQty < 10 ? `<small class="text-warning ms-2">(Only ${maxQty} left)</small>` : ''}
                        </label>
                        <div class="addon-qty-control" id="qty_${addon.addon_id}" style="display: none;">
                            <input type="number" min="1" max="${maxQty}" value="1" 
                                   id="qty_input_${addon.addon_id}" 
                                   class="form-control form-control-sm" style="width: 70px;"
                                   ${!hasStock ? 'disabled' : ''}>
                        </div>
                    </div>
                `;
            });
            
            // Add quantity selector for the main item
            addonsHTML += `
                </div>
                <div class="mt-4 p-3 bg-light rounded">
                    <label class="form-label fw-bold">Quantity:</label>
                    <div class="d-flex align-items-center gap-3">
                        <button class="btn btn-outline-secondary btn-sm" type="button" onclick="decrementItemQuantity()">-</button>
                        <input type="number" id="modalItemQuantity" class="form-control text-center" value="1" min="1" max="${stockData[itemId]?.max_quantity || 0}" style="width: 80px;">
                        <button class="btn btn-outline-secondary btn-sm" type="button" onclick="incrementItemQuantity()">+</button>
                    </div>
                    <small class="text-muted mt-2 d-block">Max available: ${stockData[itemId]?.max_quantity || 0}</small>
                </div>
            `;
            
            document.getElementById('addonsContent').innerHTML = addonsHTML;
        } else {
            // Item has no add-ons - show confirmation modal
            modalTitle.innerHTML = `
                <i class="fas fa-utensils me-2" style="color: var(--secondary);"></i>
                Confirm Item: <span id="modalItemName">${itemName}</span>
            `;
            
            let message = '';
            if (isBeverage) {
                message = 'This beverage has no add-ons configured.';
            } else {
                message = 'This item does not have add-ons available.';
            }
            
            const addonsHTML = `
                <div class="text-center py-4">
                    <div class="modal-icon primary mb-3">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <h6 class="fw-bold mb-3">Add "${itemName}" to cart?</h6>
                    <p class="text-muted small mb-4">Price: ₱${itemPrice.toFixed(2)}</p>
                    
                    <div class="bg-light p-4 rounded">
                        <label class="form-label fw-bold">Quantity:</label>
                        <div class="d-flex align-items-center justify-content-center gap-3">
                            <button class="btn btn-outline-secondary" type="button" onclick="decrementItemQuantity()">-</button>
                            <input type="number" id="modalItemQuantity" class="form-control text-center" value="1" min="1" max="${stockData[itemId]?.max_quantity || 0}" style="width: 100px;">
                            <button class="btn btn-outline-secondary" type="button" onclick="incrementItemQuantity()">+</button>
                        </div>
                        <small class="text-muted mt-2 d-block">Max available: ${stockData[itemId]?.max_quantity || 0}</small>
                    </div>
                    
                    <div class="alert alert-info bg-light border-0 text-start mt-4">
                        <i class="fas fa-info-circle me-2"></i>
                        ${message}
                    </div>
                </div>
            `;
            
            document.getElementById('addonsContent').innerHTML = addonsHTML;
        }
        
        // Always show the modal
        const modal = new bootstrap.Modal(document.getElementById('addonsModal'));
        modal.show();
    });
});

// Helper functions for quantity adjustment in modal
function decrementItemQuantity() {
    const qtyInput = document.getElementById('modalItemQuantity');
    if (qtyInput) {
        const currentVal = parseInt(qtyInput.value) || 1;
        if (currentVal > 1) {
            qtyInput.value = currentVal - 1;
        }
    }
}

function incrementItemQuantity() {
    const qtyInput = document.getElementById('modalItemQuantity');
    if (qtyInput) {
        const currentVal = parseInt(qtyInput.value) || 1;
        const maxVal = parseInt(qtyInput.max) || 999;
        if (currentVal < maxVal) {
            qtyInput.value = currentVal + 1;
        }
    }
}

// Toggle addon quantity input
function toggleAddonQty(addonId) {
    const checkbox = document.getElementById(`addon_${addonId}`);
    const qtyControl = document.getElementById(`qty_${addonId}`);
    if (qtyControl) {
        qtyControl.style.display = checkbox.checked ? 'flex' : 'none';
    }
}

// ============================================================
// ADD TO CART - STOCK VALIDATION AT ADD TIME
// ============================================================
document.getElementById('addToCartBtn').addEventListener('click', function() {
    // Get quantity from modal
    const qtyInput = document.getElementById('modalItemQuantity');
    const itemQuantity = qtyInput ? parseInt(qtyInput.value) || 1 : 1;
    
    // Check stock for the selected quantity
    const stockCheck = checkItemStockAvailability(currentItem.id, itemQuantity);
    if (!stockCheck.success) {
        showStockErrorModal(currentItem.name, stockCheck.missingIngredients);
        return;
    }
    
    // Get selected add-ons
    const checkboxes = document.querySelectorAll('#addonsContent input[type="checkbox"]:checked');
    selectedAddons = Array.from(checkboxes).map(cb => {
        const addonId = cb.value;
        const qtyInput = document.getElementById(`qty_input_${addonId}`);
        const quantity = parseInt(qtyInput?.value) || 1;
        return {
            id: parseInt(addonId),
            name: cb.dataset.name,
            price: parseFloat(cb.dataset.price),
            quantity: quantity
        };
    });

    // Check add-on stock before adding to cart
    if (selectedAddons.length > 0) {
        const addonStockCheck = checkAddonsStock(selectedAddons);
        if (!addonStockCheck.success) {
            showAddonStockErrorModal(currentItem.name, addonStockCheck.insufficientAddons);
            return;
        }
    }

    // Add item to cart with selected quantity
    addToCartWithQuantity(currentItem, selectedAddons, itemQuantity);
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('addonsModal'));
    modal.hide();
});

// Add to cart with specific quantity
function addToCartWithQuantity(item, addons, quantity) {
    // Check add-on stock before adding
    if (addons.length > 0) {
        const addonStockCheck = checkAddonsStock(addons);
        if (!addonStockCheck.success) {
            showAddonStockErrorModal(item.name, addonStockCheck.insufficientAddons);
            return;
        }
    }
    
    const existingIndex = cart.findIndex(c => {
        if (c.id !== item.id) return false;
        if (c.addons.length !== addons.length) return false;

        const cAddonIds = c.addons.map(a => `${a.id}_${a.quantity}`).sort().join(',');
        const newAddonIds = addons.map(a => `${a.id}_${a.quantity}`).sort().join(',');
        return cAddonIds === newAddonIds;
    });

    if (existingIndex > -1) {
        // Item exists - update quantity
        const newQty = cart[existingIndex].quantity + quantity;
        const increaseCheck = checkItemStockAvailability(item.id, newQty);
        
        if (!increaseCheck.success) {
            showStockErrorModal(item.name, increaseCheck.missingIngredients);
            return;
        }
        cart[existingIndex].quantity = newQty;
        cart[existingIndex].hasStockIssue = false;
    } else {
        // New item
        cart.push({
            id: item.id,
            name: item.name,
            price: item.price,
            quantity: quantity,
            addons: addons.map(a => ({...a})),
            hasStockIssue: false,
            maxQty: item.maxQty || getMaxQuantity(item.id)
        });
    }

    validateCartStock();
    saveCart();
}

// Backward compatibility
function addToCart(item, addons) {
    addToCartWithQuantity(item, addons, 1);
}

// Render cart with stock warnings and totals
function renderCart() {
    const cartItems = document.getElementById('cartItems');
    const cartSummary = document.getElementById('cartSummary');
    const submitBtn = document.getElementById('submitOrderBtn');
    const submitMsg = document.getElementById('submitDisabledMessage');

    if (cart.length === 0) {
        cartItems.innerHTML = `
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <p>No items in cart</p>
                <small class="text-muted">Click on a menu item to start</small>
            </div>
        `;
        cartSummary.classList.add('d-none');
        submitBtn.classList.add('d-none');
        submitMsg.classList.add('d-none');
        return;
    }

    let html = '';
    
    cart.forEach((item, index) => {
        const itemTotal = item.price * item.quantity;
        const addonsTotal = item.addons.reduce((sum, addon) => sum + (addon.price * addon.quantity * item.quantity), 0);
        const total = itemTotal + addonsTotal;
        
        // Determine if this item has stock issues
        const hasIssue = item.hasStockIssue === true;

        html += `
            <div class="cart-item ${hasIssue ? 'insufficient-stock' : ''}">
                <div class="cart-item-header">
                    <div>
                        <div class="cart-item-name">${item.name} × ${item.quantity}</div>
                        <div class="cart-item-price">₱${total.toFixed(2)}</div>
                    </div>
                    <button class="cart-item-remove" onclick="removeFromCart(${index})">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                ${item.addons.length > 0 ? `
                    <div class="addon-item">
                        ${item.addons.map(a => `+ ${a.name} ${a.quantity > 1 ? `(×${a.quantity})` : ''} (₱${(a.price * a.quantity).toFixed(2)})`).join('<br>')}
                    </div>
                ` : ''}
                ${hasIssue ? `
                    <div class="stock-warning stock-warning-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        Insufficient stock - please remove or reduce quantity
                    </div>
                ` : ''}
                <div class="cart-item-qty">
                    <button class="qty-btn" onclick="updateQuantity(${index}, -1)" 
                            ${item.quantity <= 1 ? 'disabled' : ''}>
                        <i class="fas fa-minus"></i>
                    </button>
                    <input type="number" class="qty-input" value="${item.quantity}" 
                           onchange="setQuantity(${index}, this.value)" min="1" 
                           ${hasIssue ? 'disabled' : ''}>
                    <button class="qty-btn" onclick="updateQuantity(${index}, 1)"
                            ${hasIssue ? 'disabled' : ''}>
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
        `;
    });

    cartItems.innerHTML = html;
    
    // Calculate and display totals with VAT
    const totals = calculateTotals();
    document.getElementById('subtotal').textContent = `₱${totals.subtotal.toFixed(2)}`;
    document.getElementById('vatAmount').textContent = `₱${totals.vat.toFixed(2)}`;
    document.getElementById('total').textContent = `₱${totals.total.toFixed(2)}`;

    cartSummary.classList.remove('d-none');
}

// Update quantity with stock validation
function updateQuantity(index, change) {
    const newQty = cart[index].quantity + change;
    if (newQty < 1) {
        cart.splice(index, 1);
        validateCartStock();
        saveCart();
        return;
    }
    
    // STOCK VALIDATION: Check if we can increase to new quantity
    if (change > 0) {
        const check = checkItemStockAvailability(cart[index].id, newQty);
        if (!check.success) {
            showStockErrorModal(cart[index].name, check.missingIngredients);
            return;
        }
        
        // Also check if add-ons have enough stock for the increased quantity
        if (cart[index].addons.length > 0) {
            // Calculate total add-on quantities needed
            const addonNeeds = {};
            cart[index].addons.forEach(addon => {
                addonNeeds[addon.id] = (addonNeeds[addon.id] || 0) + (addon.quantity * (newQty - cart[index].quantity));
            });
            
            const addonsToCheck = Object.entries(addonNeeds).map(([id, qty]) => ({
                id: parseInt(id),
                name: cart[index].addons.find(a => a.id == id)?.name || 'Unknown',
                quantity: qty
            }));
            
            const addonStockCheck = checkAddonsStock(addonsToCheck);
            if (!addonStockCheck.success) {
                showAddonStockErrorModal(cart[index].name, addonStockCheck.insufficientAddons);
                return;
            }
        }
    }
    
    cart[index].quantity = newQty;
    cart[index].hasStockIssue = false;
    validateCartStock();
    saveCart();
}

// Set quantity with stock validation
function setQuantity(index, value) {
    const qty = parseInt(value);
    if (qty < 1) {
        cart.splice(index, 1);
        validateCartStock();
        saveCart();
        return;
    }
    
    // STOCK VALIDATION: Check if the new quantity is available
    const check = checkItemStockAvailability(cart[index].id, qty);
    if (!check.success) {
        showStockErrorModal(cart[index].name, check.missingIngredients);
        // Reset input to current quantity
        const input = document.querySelector(`.cart-item:nth-child(${index + 1}) .qty-input`);
        if (input) input.value = cart[index].quantity;
        return;
    }
    
    cart[index].quantity = qty;
    cart[index].hasStockIssue = false;
    validateCartStock();
    saveCart();
}

// Remove from cart
function removeFromCart(index) {
    cart.splice(index, 1);
    validateCartStock();
    saveCart();
}

// Submit order with final validation
document.getElementById('submitOrderBtn').addEventListener('click', function() {
    if (cart.length === 0) {
        showErrorModal('Empty Cart', 'Your cart is empty. Please add items before submitting.');
        return;
    }

    // FINAL STOCK VALIDATION: Check all items before submitting
    for (let item of cart) {
        const check = checkItemStockAvailability(item.id, item.quantity);
        if (!check.success) {
            showStockErrorModal(item.name, check.missingIngredients);
            return;
        }
    }

    const customerName = document.getElementById('customer_name').value.trim();
    const tableNumber = document.getElementById('table_number').value.trim();
    const orderType = document.getElementById('order_type').value;

    if (!orderType) {
        showErrorModal('Order Type Required', 'Please select an order type.');
        return;
    }

    if (orderType === 'dine_in' && !tableNumber) {
        showErrorModal('Table Number Required', 'Please enter a table number for dine-in orders.');
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'index.php?page=manage_pos';

    const customerInput = document.createElement('input');
    customerInput.type = 'hidden';
    customerInput.name = 'customer_name';
    customerInput.value = customerName;
    form.appendChild(customerInput);

    const tableInput = document.createElement('input');
    tableInput.type = 'hidden';
    tableInput.name = 'table_number';
    tableInput.value = orderType === 'dine_in' ? tableNumber : '';
    form.appendChild(tableInput);

    const orderTypeInput = document.createElement('input');
    orderTypeInput.type = 'hidden';
    orderTypeInput.name = 'order_type';
    orderTypeInput.value = orderType;
    form.appendChild(orderTypeInput);

    const submitInput = document.createElement('input');
    submitInput.type = 'hidden';
    submitInput.name = 'submit_order';
    submitInput.value = '1';
    form.appendChild(submitInput);

    const cartInput = document.createElement('input');
    cartInput.type = 'hidden';
    cartInput.name = 'cart_data';
    cartInput.value = JSON.stringify(cart);
    form.appendChild(cartInput);

    document.body.appendChild(form);
    form.submit();
    
    cart = [];
    localStorage.removeItem('cart');
});

// Auto-dismiss alerts (only for success messages)
setTimeout(() => {
    const alerts = document.querySelectorAll('.alert:not(.modal-alert)');
    alerts.forEach(alert => {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    });
}, 5000);

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