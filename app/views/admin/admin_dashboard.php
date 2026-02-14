<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5">
    <title>Admin Dashboard - Cafeteria POS</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        /* ------------------------------------------------------------
           MODERN CAFETERIA PALETTE – bold, fresh, energetic
           Teal + Coral + Navy + Lavender gray – contemporary & vibrant
           ------------------------------------------------------------ */
        :root {
            --primary: #0f4c5c;        /* deep teal – sophisticated */
            --primary-dark: #0a3a47;    /* darker teal */
            --primary-light: #1f7a8c;   /* bright teal */
            --secondary: #ff6b4a;       /* vibrant coral – energetic */
            --secondary-light: #ff8c70; /* light coral */
            --accent: #fee440;          /* sunny yellow */
            --navy: #1e2f4e;           /* deep navy for contrast */
            --lavender: #e2e6f0;       /* soft lavender gray */
            --light-bg: #f8faff;       /* icy blue-white background */
            --warm-gray: #4a5568;      /* modern gray */
            --border: #cbd5e0;         /* cool gray border */
            --success: #2c7a5a;        /* mint green */
            --danger: #d64045;         /* red coral */
            --warning: #f4b942;        /* golden */
            --info: #5e9b9e;           /* muted teal */
            --dark: #1a202c;           /* almost black */
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
        
        .notification-btn {
            position: relative;
            background: white;
            border: 1px solid var(--border);
            font-size: 1.25rem;
            color: var(--primary);
            cursor: pointer;
            padding: 0.6rem 1rem;
            border-radius: 12px;
            transition: all 0.2s;
        }
        
        .notification-btn:hover {
            background: var(--lavender);
            border-color: var(--primary-light);
            color: var(--primary-dark);
        }
        
        .notification-badge {
            position: absolute;
            top: -6px;
            right: -6px;
            background: var(--secondary);
            color: white;
            border-radius: 50px;
            width: 20px;
            height: 20px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            border: 2px solid white;
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
        }
        
        .page-header h1 {
            font-size: 2.1rem;
            font-weight: 700;
            color: var(--navy);
            margin-bottom: 0.3rem;
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
        
        /* ------ STATS CARDS – modern glassmorphism touch ------ */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
        .stat-card.success::before { background: var(--success); }
        
        .stat-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 28px rgba(15, 76, 92, 0.08);
            border-color: var(--primary-light);
        }
        
        .stat-card .icon {
            width: 56px;
            height: 56px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 1.2rem;
            background: linear-gradient(145deg, #f0f5fa, white);
            color: var(--primary);
            border: 1px solid rgba(15, 76, 92, 0.1);
        }
        
        .stat-card .label {
            font-size: 0.8rem;
            color: var(--warm-gray);
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 700;
        }
        
        .stat-card .value {
            font-size: 2.3rem;
            font-weight: 700;
            color: var(--navy);
            line-height: 1.1;
            margin-bottom: 0.5rem;
        }
        
        .stat-card .footer {
            font-size: 0.85rem;
            color: var(--warm-gray);
            border-top: 1px solid rgba(203, 213, 224, 0.5);
            padding-top: 0.7rem;
            margin-top: 0.3rem;
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
        
        /* ------ BADGES & STATUS – vibrant modern ------ */
        .badge {
            padding: 0.45rem 1rem;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.75rem;
            letter-spacing: 0.3px;
        }
        
        .order-status {
            padding: 0.45rem 1rem;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
            min-width: 100px;
            text-align: center;
        }
        
        .status-pending { background: #fff3cd; color: #856404; border-left: 3px solid #ffc107; }
        .status-preparing { background: #e1f0fa; color: #0f4c5c; border-left: 3px solid #0f4c5c; }
        .status-completed { background: #d4edda; color: #155724; border-left: 3px solid #28a745; }
        .status-cancelled { background: #f8d7da; color: #721c24; border-left: 3px solid #dc3545; }
        
        /* ------ LOW STOCK – modern alerts ------ */
        .low-stock-item {
            border-left: 6px solid var(--accent);
            background: #fffef7;
            border-radius: 16px;
            margin-bottom: 8px;
            border: 1px solid rgba(254, 228, 64, 0.3);
        }
        
        .critical-stock {
            border-left: 6px solid var(--danger);
            background: #fff5f3;
            border: 1px solid rgba(214, 64, 69, 0.2);
        }
        
        .progress {
            height: 8px;
            border-radius: 12px;
            background: #e2e8f0;
        }
        
        .progress-bar {
            border-radius: 12px;
        }
        
        /* ------ BUTTONS – bold & playful ------ */
        .btn {
            padding: 0.6rem 1.5rem;
            border-radius: 40px;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.18s;
            letter-spacing: 0.3px;
        }
        
        .btn-primary {
            background: var(--primary);
            border: none;
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 10px 18px rgba(15, 76, 92, 0.25);
        }
        
        .btn-outline-primary {
            border: 1.5px solid var(--primary);
            color: var(--primary-dark);
            background: transparent;
        }
        
        .btn-outline-primary:hover {
            background: var(--primary);
            color: white;
        }
        
        .btn-icon {
            width: 40px;
            height: 40px;
            padding: 0;
            border-radius: 14px;
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
        
        /* ------ DROPDOWN – modern minimal ------ */
        .dropdown-menu {
            border-radius: 20px;
            border: 1px solid var(--border);
            box-shadow: 0 12px 24px rgba(0,0,0,0.04);
            padding: 0.6rem;
        }
        
        .dropdown-item {
            border-radius: 14px;
            padding: 0.6rem 1.2rem;
            font-size: 0.9rem;
        }
        
        .dropdown-item:hover {
            background: var(--lavender);
            color: var(--primary-dark);
        }
        
        /* ------ FOOTER – modern & clean ------ */
        .main-footer {
            background: white;
            border-top: 1px solid var(--border);
            padding: 1.5rem 2.2rem;
            margin-left: var(--sidebar-width);
            color: var(--warm-gray);
        }
        
        /* ------ CHART canvas – crisp ------ */
        canvas {
            max-height: 280px;
            width: 100% !important;
        }
        
        /* ------ RESPONSIVE – perfectly even on all devices ------ */
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
            .main-footer {
                margin-left: 0;
                padding: 1.5rem 1.8rem;
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
            .stats-grid { grid-template-columns: 1fr; }
            .page-header h1 { font-size: 1.8rem; }
            .stat-card .value { font-size: 2rem; }
            .card-header { padding: 1.2rem 1.4rem; }
            .card-body { padding: 1.2rem 1.4rem; }
        }
        
        @media (max-width: 480px) {
            .topbar .logo span { display: none; }
            .topbar { padding: 0 1rem; }
            .main-content { padding: 1rem; }
        }
        
        /* ------ ANIMATION – subtle fade ------ */
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
    <!-- TOPBAR - modern cafeteria, vibrant coral & teal -->
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
        
        <div class="d-flex align-items-center gap-3">
            <!-- Notifications -->
            <div class="dropdown">
                <button class="notification-btn" type="button" id="notificationDropdown" data-bs-toggle="dropdown">
                    <i class="far fa-bell"></i>
                    <?php if ($stats['low_stock'] > 0 || $stats['pending_orders'] > 0): ?>
                        <span class="notification-badge">
                            <?= $stats['low_stock'] + $stats['pending_orders'] ?>
                        </span>
                    <?php endif; ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><h6 class="dropdown-header">Notifications</h6></li>
                    <?php if ($stats['pending_orders'] > 0): ?>
                        <li>
                            <a class="dropdown-item" href="<?= $baseUrl ?>/index.php?page=admin_dashboard&tab=orders">
                                <i class="fas fa-clock me-2"></i>
                                <?= $stats['pending_orders'] ?> pending orders
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if ($stats['low_stock'] > 0): ?>
                        <li>
                            <a class="dropdown-item" href="<?= $baseUrl ?>/index.php?page=inventory">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?= $stats['low_stock'] ?> low stock items
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <!-- User Dropdown -->
            <div class="dropdown">
                <button class="btn btn-link text-decoration-none dropdown-toggle d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown" style="color: var(--navy); font-weight: 600;">
                    <i class="far fa-user-circle fa-xl me-2" style="font-size: 1.6rem; color: var(--secondary);"></i>
                    <span class="d-none d-md-inline"><?= htmlspecialchars($_SESSION['full_name'] ?? 'Admin') ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <!--<li>
                        <a class="dropdown-item" href="<?= $baseUrl ?>/index.php?page=profile">
                            <i class="fas fa-user me-2"></i> Profile
                        </a>
                    </li>-->
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger" href="<?= $baseUrl ?>/index.php?page=logout">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Sidebar (no backend changes) -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-nav">
            <div class="nav-section-title">DASHBOARD</div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="nav-link active">
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
                <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard&tab=reports" class="nav-link">
                    <i class="fas fa-chart-bar"></i>
                    Reports
                </a>
            </div>
        </div>
    </div>
    
    <!-- MAIN CONTENT - even layout, responsive, untouched backend vars -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1>
                <i class="fas fa-tachometer-alt me-2"></i>
                Dashboard
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </nav>
        </div>
        
        <!-- STATISTICS CARDS - modern grid -->
        <div class="stats-grid">
            <div class="stat-card primary fade-in">
                <div class="icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="label">Today's Revenue</div>
                <div class="value">₱<?= number_format($stats['today_revenue'], 2) ?></div>
                <div class="footer">Monthly: ₱<?= number_format($stats['monthly_revenue'], 2) ?></div>
            </div>
            
            <div class="stat-card secondary fade-in" style="animation-delay: 0.05s">
                <div class="icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="label">Today's Orders</div>
                <div class="value"><?= $stats['today_orders'] ?></div>
                <div class="footer"><?= $stats['today_customers'] ?> customers today</div>
            </div>
            
            <div class="stat-card info fade-in" style="animation-delay: 0.1s">
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="label">Pending Orders</div>
                <div class="value"><?= $stats['pending_orders'] ?></div>
                <div class="footer">
                    <a href="<?= $baseUrl ?>/index.php?page=manage_orders" class="text-decoration-none" style="color: var(--primary); font-weight: 600;">View all</a>
                </div>
            </div>
            
            <div class="stat-card success fade-in" style="animation-delay: 0.15s">
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="label">Low Stock</div>
                <div class="value"><?= $stats['low_stock'] ?></div>
                <div class="footer"><?= $stats['total_items'] ?> items in menu</div>
            </div>
        </div>
        
        <!-- Charts Row - even columns -->
        <div class="row g-4 mb-4">
            <!-- Sales Chart -->
            <div class="col-lg-8">
                <div class="card fade-in">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-line me-2" style="color: var(--secondary);"></i>
                            Sales Overview (Last 7 Days)
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Category Sales -->
            <div class="col-lg-4">
                <div class="card fade-in">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-pie me-2" style="color: var(--primary);"></i>
                            Sales by Category
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tables Row - perfectly balanced -->
        <div class="row g-4">
            <!-- Recent Orders - ACTION BUTTON REMOVED, DATE SIMPLIFIED -->
            <div class="col-lg-7">
                <div class="card fade-in">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-history me-2" style="color: var(--primary);"></i>
                                Recent Orders
                            </h5>
                            <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard&tab=orders" class="btn btn-sm btn-primary">
                                View All
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-container">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <!-- ACTION COLUMN REMOVED -->
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentOrders as $order): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($order['order_number']) ?></strong>
                                                <br>
                                                <small class="text-muted">by <?= htmlspecialchars($order['cashier_name']) ?></small>
                                            </td>
                                            <td>
                                                <?= !empty($order['customer_name']) ? 
                                                    htmlspecialchars($order['customer_name']) : 
                                                    '<span class="text-muted">Walk-in</span>' ?>
                                            </td>
                                            <td>₱<?= number_format($order['total_amount'], 2) ?></td>
                                            <td>
                                                <span class="order-status status-<?= $order['status'] ?>">
                                                    <?= ucfirst($order['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <!-- SIMPLIFIED DATE - LESS CROWDED -->
                                                <?= date('M d, H:i', strtotime($order['created_at'])) ?>
                                            </td>
                                            <!-- ACTION BUTTON REMOVED -->
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Top Selling & Low Stock -->
            <div class="col-lg-5">
                <!-- Top Selling Items -->
                <div class="card fade-in">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-star me-2" style="color: var(--accent);"></i>
                            Top Selling Items
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php foreach ($topItems as $index => $item): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?= $index + 1 ?>. <?= htmlspecialchars($item['item_name']) ?></h6>
                                        <small class="text-muted">₱<?= number_format($item['price'], 2) ?> each</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge" style="background: var(--primary); color: white;"><?= $item['total_sold'] ?> sold</span>
                                        <br>
                                        <small style="color: var(--success); font-weight: 600;">₱<?= number_format($item['total_revenue'], 2) ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Low Stock Alert -->
                <div class="card mt-4 fade-in">
                    <div class="card-header" style="background: linear-gradient(145deg, white, #fafcff); border-bottom: 1px solid var(--border);">
                        <h5 class="card-title mb-0" style="color: var(--navy);">
                            <i class="fas fa-exclamation-triangle me-2" style="color: var(--secondary);"></i>
                            Low Stock Alert
                            <span class="badge bg-warning ms-2"><?= $stats['low_stock'] ?> items</span> 
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($lowStock)): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                All items are sufficiently stocked.
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($lowStock as $ingredient): 
                                    $isCritical = $ingredient['stock_quantity'] <= ($ingredient['low_stock_alert'] * 0.3);
                                    $class = $isCritical ? 'critical-stock' : 'low-stock-item';
                                ?>
                                    <div class="list-group-item <?= $class ?> p-3 mb-2" style="border-radius: 16px;">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div>
                                                <h6 class="mb-1"><?= htmlspecialchars($ingredient['ingredient_name']) ?></h6>
                                                <small class="text-muted">
                                                    Current: <?= $ingredient['stock_quantity'] ?> <?= $ingredient['unit'] ?>
                                                </small>
                                            </div>
                                            <div>
                                                <span class="badge <?= $isCritical ? 'bg-danger' : 'bg-warning' ?>">
                                                    <?php if ($isCritical): ?>
                                                        <i class="fas fa-exclamation-circle"></i> Critical
                                                    <?php else: ?>
                                                        <i class="fas fa-exclamation-triangle"></i> Low
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="progress mb-2">
                                            <?php 
                                            $percent = min(100, ($ingredient['stock_quantity'] / $ingredient['low_stock_alert']) * 100);
                                            ?>
                                            <div class="progress-bar <?= $isCritical ? 'bg-danger' : 'bg-warning' ?>" 
                                                 role="progressbar" 
                                                 style="width: <?= $percent ?>%;">
                                            </div>
                                         </div>
                                        <small class="text-muted">
                                            Alert level: <?= $ingredient['low_stock_alert'] ?> <?= $ingredient['unit'] ?>
                                        </small>
                                        </div>
                                <?php endforeach; ?>
                                <div class="footer">
                                          <a href="<?= $baseUrl ?>/index.php?page=manage_ingredients" class="text-decoration-none" style="color: var(--primary); font-weight: 600;">View all</a>
                                    </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="main-footer">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <strong>Copyright &copy; <?= date('Y') ?> Cafeteria POS.</strong> All rights reserved.
            </div>
            <div class="text-muted">
                <b>Version</b> 1.0.0
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Mobile menu toggle (unchanged)
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
        
        // Sales Chart (same backend data, modern colors)
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: [
                    <?php 
                    $labels = [];
                    $salesData = [];
                    foreach ($dailySales as $sale) {
                        $labels[] = "'" . date('M d', strtotime($sale['date'])) . "'";
                        $salesData[] = $sale['total_sales'];
                    }
                    echo implode(',', $labels);
                    ?>
                ],
                datasets: [{
                    label: 'Daily Sales (₱)',
                    data: [<?= implode(',', $salesData) ?>],
                    backgroundColor: 'rgba(15, 76, 92, 0.06)',
                    borderColor: '#0f4c5c',
                    borderWidth: 2.5,
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: '#ff6b4a',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#ff6b4a',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true, position: 'top', labels: { usePointStyle: true } },
                    tooltip: { backgroundColor: '#1e2f4e', padding: 12, borderRadius: 12 }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(15, 76, 92, 0.04)' },
                        ticks: { callback: function(value) { return '₱' + value.toLocaleString(); } }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
        
        // Category Chart (same backend, modern palette)
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        const categoryChart = new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: [
                    <?php 
                    $catLabels = [];
                    $catSales = [];
                    $colors = ['#0f4c5c', '#ff6b4a', '#fee440', '#1e2f4e', '#5e9b9e', '#ff8c70'];
                    foreach ($salesByCategory as $index => $category) {
                        $catLabels[] = "'" . htmlspecialchars($category['category_name']) . "'";
                        $catSales[] = $category['total_sales'];
                    }
                    echo implode(',', $catLabels);
                    ?>
                ],
                datasets: [{
                    data: [<?= implode(',', $catSales) ?>],
                    backgroundColor: [
                        <?php 
                        for ($i = 0; $i < count($salesByCategory); $i++) {
                            echo "'" . $colors[$i % count($colors)] . "',";
                        }
                        ?>
                    ],
                    borderWidth: 0,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, padding: 16, usePointStyle: true } },
                    tooltip: {
                        backgroundColor: '#1e2f4e',
                        padding: 12,
                        borderRadius: 12,
                        callbacks: { label: (ctx) => `${ctx.label}: ₱${ctx.raw.toLocaleString()}` }
                    }
                }
            }
        });
    </script>
</body>
</html>