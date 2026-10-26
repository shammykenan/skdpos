<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php require_once __DIR__ . "/../../core/csrfHelper.php"; echo csrf_head($baseUrl ?? ""); ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — The Lounge by: CJV & TEK</title>
    <link rel="icon" type="image/png" href="<?= $baseUrl ?>/assets/images/logo/logo.png">
    <link href="<?= $baseUrl ?>/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/common.css">
    <link href="<?= $baseUrl ?>/assets/vendor/fonts/app.css" rel="stylesheet">
    <script src="<?= $baseUrl ?>/assets/vendor/chartjs/chart.umd.min.js"></script>
    <style>
        /* ── TOKENS (matching manage menu / manage addons exactly) ── */

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--light-bg);
            color: var(--dark);
            font-size: 14px;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        /* ── TOPBAR ── */
        .topbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: var(--top-h);
            background: #fff;
            border-bottom: 1px solid var(--border);
            z-index: 1040;
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            gap: 1rem;
            box-shadow: 0 2px 8px rgba(15,76,92,0.06);
        }

        .topbar-brand {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            text-decoration: none;
            flex-shrink: 0;
        }

        .brand-logo {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-sm);
            background: #fff;
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }

        .brand-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        .brand-text-main {
            font-family: 'Fraunces', Georgia, serif;
            font-weight: 700;
            font-size: 1.15rem;
            color: var(--primary-dark);
            line-height: 1;
            white-space: nowrap;
        }

        .brand-text-sub {
            font-size: 0.65rem;
            color: var(--secondary);
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-top: 2px;
            white-space: nowrap;
        }

        .topbar-spacer { flex: 1; }

        .mobile-toggle {
            display: none;
            background: none;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 0.4rem 0.65rem;
            color: var(--primary);
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.15s;
        }
        .mobile-toggle:hover { background: var(--lavender); }

        .icon-btn {
            background: white;
            border: 1px solid var(--border);
            border-radius: 40px;
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 1rem;
            transition: all 0.15s;
            cursor: pointer;
            position: relative;
        }
        .icon-btn:hover { background: var(--lavender); border-color: var(--primary-light); }

        .badge-notify {
            position: absolute;
            top: -5px; right: -5px;
            background: var(--secondary);
            color: white;
            font-size: 0.62rem;
            font-weight: 700;
            width: 18px; height: 18px;
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
        }

        .user-pill {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--light-bg);
            border: 1px solid var(--border);
            border-radius: 40px;
            padding: 0.3rem 0.9rem 0.3rem 0.35rem;
            cursor: pointer;
            transition: all 0.15s;
            text-decoration: none;
            max-width: 220px;
        }
        .user-pill:hover { background: var(--lavender); border-color: var(--primary-light); }

        .user-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: var(--primary);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.72rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .user-name-text {
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--navy);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-caret { color: var(--warm-gray); font-size: 0.65rem; flex-shrink: 0; }

        /* ── SIDEBAR ── */
        .sidebar {
            position: fixed;
            top: var(--top-h); left: 0;
            width: var(--sidebar-w);
            height: calc(100vh - var(--top-h));
            background: #fff;
            border-right: 1px solid var(--border);
            overflow-y: auto;
            z-index: 1030;
            transition: transform 0.25s ease;
        }
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

        .nav-wrap { padding: 1.2rem 0.75rem 2rem; }

        .nav-section {
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: var(--warm-gray);
            padding: 1.2rem 0.75rem 0.4rem;
            opacity: 0.7;
        }

        .nav-link-custom {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            padding: 0.6rem 0.9rem;
            border-radius: var(--radius-md);
            color: var(--warm-gray);
            font-weight: 500;
            font-size: 0.88rem;
            text-decoration: none;
            transition: all 0.15s;
            margin: 0.1rem 0;
        }
        .nav-link-custom i { width: 20px; text-align: center; font-size: 0.9rem; flex-shrink: 0; }
        .nav-link-custom:hover { background: var(--lavender); color: var(--primary-dark); }
        .nav-link-custom.active {
            background: var(--primary);
            color: #fff;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(15,76,92,0.2);
        }

        /* ── SIDEBAR OVERLAY ── */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: var(--top-h); left: 0; right: 0; bottom: 0;
            background: rgba(15,76,92,0.25);
            z-index: 1025;
            backdrop-filter: blur(2px);
        }
        .sidebar-overlay.show { display: block; }

        /* ── MAIN ── */
        .main {
            margin-left: var(--sidebar-w);
            margin-top: var(--top-h);
            padding: 2rem 1.75rem;
            min-height: calc(100vh - var(--top-h));
        }

        /* ── PAGE HEADER ── */
        .page-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.75rem;
            flex-wrap: wrap;
        }
        .page-title {
            font-family: 'Fraunces', Georgia, serif;
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--navy);
            line-height: 1.1;
        }
        .breadcrumb-bar {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.8rem;
            color: var(--warm-gray);
            margin-top: 0.3rem;
        }
        .breadcrumb-bar a { color: var(--primary-light); text-decoration: none; font-weight: 500; }
        .breadcrumb-bar a:hover { text-decoration: underline; }
        .breadcrumb-sep { color: var(--border); }

        /* ── STATS ROW ── */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.75rem;
        }

        .stat-card {
            background: #fff;
            border: 1px solid rgba(203,213,224,0.6);
            border-radius: var(--radius-lg);
            padding: 1.25rem 1.35rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: box-shadow 0.2s, transform 0.2s;
            position: relative;
            overflow: hidden;
        }
        .stat-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: var(--primary); }
        .stat-card.c-success::before { background: var(--success); }
        .stat-card.c-info::before    { background: var(--info); }
        .stat-card.c-warning::before { background: var(--warning); }
        .stat-card:hover { box-shadow: 0 8px 24px rgba(15,76,92,0.08); transform: translateY(-2px); }

        .stat-icon {
            width: 46px; height: 46px;
            border-radius: var(--radius-md);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; flex-shrink: 0;
            background: #f0f5fa; color: var(--primary);
            border: 1px solid rgba(15,76,92,0.1);
        }
        .stat-icon.c-success { background: var(--success-soft); color: var(--success); border-color: rgba(44,122,90,0.15); }
        .stat-icon.c-info    { background: var(--info-soft);    color: var(--info);    border-color: rgba(94,155,158,0.2); }
        .stat-icon.c-warning { background: var(--warning-soft); color: var(--warning); border-color: rgba(244,185,66,0.2); }

        .stat-label { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: var(--warm-gray); margin-bottom: 0.2rem; }
        .stat-value { font-size: 1.65rem; font-weight: 800; color: var(--navy); line-height: 1; }

        /* ── GLASS CARD ── */
        .glass-card {
            background: #fff;
            border: 1px solid rgba(203,213,224,0.6);
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: var(--card-shadow);
            margin-bottom: 1.8rem;
        }

        .card-header-custom {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .card-title {
            font-weight: 700;
            font-size: 0.95rem;
            color: var(--navy);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* ── DATA TABLE ── */
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table thead th {
            padding: 0.85rem 1.25rem;
            text-align: left;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: var(--primary-dark);
            background: #f2f6fc;
            border-bottom: 2px solid var(--primary-light);
        }
        .data-table tbody td {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #edf2f7;
            vertical-align: middle;
        }
        .data-table tbody tr:hover td { background: #fafcff; }

        /* ── STATUS BADGES ── */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.3rem 0.9rem;
            border-radius: 40px;
            font-size: 0.7rem;
            font-weight: 700;
        }
        .status-pending   { background: #fff3e0; color: #b85a1a; }
        .status-preparing { background: #e0f2fe; color: #0f4c5c; }
        .status-ready     { background: #dcfce7; color: #166534; }
        /* ── FIXED: completed is now green (was blue #155799) ── */
        .status-completed { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #fee2e2; color: #b91c1c; }

        /* low stock */
        .stock-item {
            border-left: 4px solid var(--warning);
            background: #fffef7;
            border-radius: var(--radius-lg);
            padding: 0.9rem;
            margin-bottom: 0.8rem;
        }
        .stock-critical { border-left-color: var(--danger); background: #fff8f6; }

        .progress-custom { height: 6px; background: #e2e8f0; border-radius: 12px; overflow: hidden; margin: 0.5rem 0; }
        .progress-fill { background: var(--warning); height: 100%; border-radius: 12px; }
        .progress-fill.danger { background: var(--danger); }

        .top-item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.9rem 1rem;
            border-bottom: 1px solid var(--border);
        }
        .top-item-row:last-child { border-bottom: none; }

        .badge-count { background: var(--primary); color: white; border-radius: 30px; padding: 0.2rem 0.6rem; font-size: 0.7rem; font-weight: 600; }

        /* dropdown */
        .dropdown-menu { border-radius: var(--radius-lg); border: 1px solid var(--border); box-shadow: 0 12px 30px rgba(0,0,0,0.08); padding: 0.5rem; }
        .dropdown-item { border-radius: var(--radius-sm); font-size: 0.88rem; padding: 0.55rem 1rem; }
        .dropdown-item:hover { background: var(--lavender); color: var(--primary-dark); }
        .dropdown-item.text-danger:hover { background: rgba(214,64,69,0.08); color: var(--danger); }

        /* footer */
        .main-footer {
            margin-left: var(--sidebar-w);
            background: white;
            border-top: 1px solid var(--border);
            padding: 1.2rem 2rem;
            font-size: 0.8rem;
            color: var(--warm-gray);
        }

        /* ── TOAST STACK ── */
        .toast-stack {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
            pointer-events: none;
        }

        .toast-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 0.9rem 1.1rem;
            min-width: 280px;
            max-width: 360px;
            box-shadow: 0 8px 24px rgba(15,76,92,0.12), 0 0 0 1px rgba(15,76,92,0.04);
            pointer-events: all;
            animation: toastIn 0.28s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
            border-left: 4px solid var(--primary);
        }
        .toast-item.toast-success { border-left-color: var(--success); }
        .toast-item.toast-error   { border-left-color: var(--danger); }
        .toast-item.toast-warning { border-left-color: var(--warning); }
        .toast-item.toast-info    { border-left-color: var(--info); }

        .toast-item.toast-out {
            animation: toastOut 0.22s ease forwards;
        }

        .toast-icon {
            width: 32px; height: 32px;
            border-radius: var(--radius-sm);
            display: flex; align-items: center; justify-content: center;
            font-size: 0.85rem;
            flex-shrink: 0;
        }
        .toast-success .toast-icon { background: var(--success-soft); color: var(--success); }
        .toast-error   .toast-icon { background: var(--danger-soft);  color: var(--danger); }
        .toast-warning .toast-icon { background: var(--warning-soft); color: var(--warning); }
        .toast-info    .toast-icon { background: var(--info-soft);    color: var(--info); }
        .toast-item:not([class*="toast-success"]):not([class*="toast-error"]):not([class*="toast-warning"]):not([class*="toast-info"]) .toast-icon {
            background: rgba(15,76,92,0.08); color: var(--primary);
        }

        .toast-body { flex: 1; min-width: 0; }
        .toast-title {
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--navy);
            line-height: 1.2;
            margin-bottom: 0.15rem;
        }
        .toast-msg {
            font-size: 0.78rem;
            color: var(--warm-gray);
            line-height: 1.4;
        }

        .toast-close {
            background: none;
            border: none;
            color: var(--warm-gray);
            font-size: 0.75rem;
            cursor: pointer;
            padding: 0.1rem;
            flex-shrink: 0;
            opacity: 0.6;
            transition: opacity 0.15s;
            line-height: 1;
            margin-top: 1px;
        }
        .toast-close:hover { opacity: 1; }

        @keyframes toastIn {
            from { opacity: 0; transform: translateX(24px) scale(0.96); }
            to   { opacity: 1; transform: translateX(0)    scale(1); }
        }
        @keyframes toastOut {
            from { opacity: 1; transform: translateX(0)    scale(1); max-height: 120px; margin-bottom: 0; }
            to   { opacity: 0; transform: translateX(24px) scale(0.96); max-height: 0; padding: 0; margin: 0; overflow: hidden; }
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 1199.98px) {
            .stats-row { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main { margin-left: 0; padding: 1.5rem; }
            .mobile-toggle { display: flex; align-items: center; }
            .main-footer { margin-left: 0; }
        }

        @media (min-width: 768px) and (max-width: 1024px) {
            :root { --sidebar-w: 220px; }
            .stats-row { grid-template-columns: repeat(2, 1fr); }
            .main { padding: 1.5rem; }
        }

        @media (max-width: 767.98px) {
            .main { padding: 1.25rem 1rem; }
            .stats-row { grid-template-columns: repeat(2, 1fr); gap: 0.75rem; }
            .brand-text-sub { display: none; }
            .topbar { padding: 0 1rem; }
            .toast-stack { bottom: 1rem; right: 1rem; left: 1rem; }
            .toast-item { min-width: 0; max-width: 100%; }
        }

        @media (max-width: 480px) {
            .topbar-brand .brand-text-main,
            .topbar-brand .brand-text-sub { display: none; }
            .brand-logo { width: 36px; height: 36px; }
            .topbar { padding: 0 0.75rem; gap: 0.5rem; }
            .user-pill { padding: 0.2rem 0.6rem 0.2rem 0.25rem; max-width: 160px; }
            .user-name-text { font-size: 0.7rem; }
            .icon-btn { width: 32px; height: 32px; font-size: 0.85rem; }
            .stats-row { grid-template-columns: 1fr; }
        }

        canvas { max-height: 280px; width: 100%; }

        .fade-up { animation: fadeUp 0.35s ease forwards; }
        .fade-up-1 { animation-delay: 0.05s; opacity: 0; }
        .fade-up-2 { animation-delay: 0.10s; opacity: 0; }
        .fade-up-3 { animation-delay: 0.15s; opacity: 0; }
        .fade-up-4 { animation-delay: 0.20s; opacity: 0; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

<!-- TOPBAR -->
<header class="topbar">
    <button class="mobile-toggle" id="menuToggle" aria-label="Toggle menu">
        <i class="fas fa-bars"></i>
    </button>
    <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="topbar-brand">
        <div class="brand-logo">
            <img src="<?= $baseUrl ?>/assets/images/logo/logo.png" alt="The Lounge by: CJV & TEK Logo">
        </div>
        <div>
            <div class="brand-text-main">The Lounge by: CJV & TEK</div>
            <div class="brand-text-sub">Chill. Coffee. Dine.</div>
        </div>
    </a>
    <div class="topbar-spacer"></div>
    <div class="d-flex gap-2 align-items-center">
        <!-- Notifications -->
        <div class="dropdown">
            <button class="icon-btn" id="notifDropdownBtn" data-bs-toggle="dropdown">
                <i class="far fa-bell"></i>
                <?php if ($stats['low_stock'] > 0 || $stats['pending_orders'] > 0): ?>
                    <span class="badge-notify"><?= $stats['low_stock'] + $stats['pending_orders'] ?></span>
                <?php endif; ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" id="notifDropdownMenu">
                <li><h6 class="dropdown-header">Notifications</h6></li>
                <li><span class="dropdown-item-text">Loading...</span></li>
            </ul>
        </div>

        <!-- WiFi Config -->
        <button class="icon-btn" data-bs-toggle="modal" data-bs-target="#wifiConfigModal" title="Configure WiFi">
            <i class="fas fa-wifi"></i>
        </button>

        <!-- Database tools -->
        <div class="dropdown">
            <button class="icon-btn" data-bs-toggle="dropdown"><i class="fas fa-database"></i></button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="<?= $baseUrl ?>/index.php?page=backup_database&action=export"><i class="fas fa-download me-2"></i>Export Backup</a></li>
                <li><a class="dropdown-item" href="<?= $baseUrl ?>/index.php?page=backup_database&action=import" onclick="return confirm('Import will overwrite all data. Continue?')"><i class="fas fa-upload me-2"></i>Import / Restore</a></li>
            </ul>
        </div>

        <!-- User dropdown -->
        <div class="dropdown">
            <div class="user-pill" data-bs-toggle="dropdown">
                <div class="user-avatar">
                    <?php
                        $fullName = $_SESSION['full_name'] ?? 'Admin';
                        $parts = explode(' ', trim($fullName));
                        $initials = strtoupper(substr($parts[0], 0, 1));
                        if (count($parts) > 1) $initials .= strtoupper(substr(end($parts), 0, 1));
                        echo htmlspecialchars($initials);
                    ?>
                </div>
                <span class="user-name-text"><?= htmlspecialchars($fullName) ?></span>
                <i class="fas fa-chevron-down user-caret"></i>
            </div>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <div class="px-3 py-2" style="border-bottom:1px solid var(--border);margin-bottom:0.4rem;">
                        <div style="font-weight:700;font-size:0.9rem;color:var(--navy);"><?= htmlspecialchars($fullName) ?></div>
                        <div style="font-size:0.78rem;color:var(--warm-gray);">Administrator</div>
                    </div>
                </li>
                <li><a class="dropdown-item text-danger" href="<?= $baseUrl ?>/index.php?page=logout"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</header>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- SIDEBAR -->
<nav class="sidebar" id="sidebar">
    <div class="nav-wrap">
        <div class="nav-section">DASHBOARD</div>
        <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="nav-link-custom active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>

        <div class="nav-section">MANAGEMENT</div>
        <a href="<?= $baseUrl ?>/index.php?page=manage_menu"        class="nav-link-custom"><i class="fas fa-utensils"></i> Menu Items</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_recipes"     class="nav-link-custom"><i class="fas fa-tags"></i> Recipes</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_ingredients" class="nav-link-custom"><i class="fas fa-boxes"></i> Ingredients</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_addons"      class="nav-link-custom"><i class="fas fa-plus-circle"></i> Add-ons</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_tables"      class="nav-link-custom"><i class="fas fa-chair"></i> Tables</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_users"       class="nav-link-custom"><i class="fas fa-users"></i> User Management</a>

        <div class="nav-section">SALES</div>
        <a href="<?= $baseUrl ?>/index.php?page=manage_pos"    class="nav-link-custom"><i class="fas fa-cash-register"></i> POS System</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_orders" class="nav-link-custom"><i class="fas fa-receipt"></i> Orders</a>
        <a href="<?= $baseUrl ?>/index.php?page=reports"       class="nav-link-custom"><i class="fas fa-chart-bar"></i> Reports</a>
        <div class="nav-section">Configuration</div>
        <a href="<?= $baseUrl ?>/index.php?page=bir_settings" class="nav-link-custom"><i class="fa-solid fa-building"></i>BIR Settings</a>
    </div>
</nav>

<!-- MAIN CONTENT -->
<main class="main" id="mainContent">

    <div class="page-head">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <div class="breadcrumb-bar">
                <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard">Home</a>
                <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:0.6rem;"></i></span>
                <span>Dashboard</span>
            </div>
        </div>
    </div>

    <!-- STATS ROW -->
    <div class="stats-row">
        <div class="stat-card fade-up fade-up-1">
            <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
            <div>
                <div class="stat-label">Today's Revenue</div>
                <div class="stat-value" id="statRevenue">₱<?= number_format($stats['today_revenue'], 2) ?></div>
                <div class="small text-muted mt-1">Monthly: ₱<?= number_format($stats['monthly_revenue'], 2) ?></div>
            </div>
        </div>
        <div class="stat-card c-success fade-up fade-up-2">
            <div class="stat-icon c-success"><i class="fas fa-shopping-cart"></i></div>
            <div>
                <div class="stat-label">Today's Orders</div>
                <div class="stat-value" id="statOrders"><?= $stats['today_orders'] ?></div>
                <div class="small text-muted mt-1" id="statCustomersFooter"><?= $stats['today_customers'] ?> customers today</div>
            </div>
        </div>
        <div class="stat-card c-info fade-up fade-up-3">
            <div class="stat-icon c-info"><i class="fas fa-clock"></i></div>
            <div>
                <div class="stat-label">Active Orders</div>
                <div class="stat-value" id="statActiveOrders"><?= $stats['pending_orders'] ?></div>
                <div class="small text-muted mt-1" id="activeOrdersFooter"><?= $stats['ready_count'] ?> ready · <a href="<?= $baseUrl ?>/index.php?page=manage_orders" class="text-decoration-none fw-semibold">View all</a></div>
            </div>
        </div>
        <div class="stat-card c-warning fade-up fade-up-4">
            <div class="stat-icon c-warning"><i class="fas fa-exclamation-triangle"></i></div>
            <div>
                <div class="stat-label">Low Stock</div>
                <div class="stat-value" id="statLowStock"><?= $stats['low_stock'] ?></div>
                <div class="small text-muted mt-1"><?= $stats['total_items'] ?> menu items</div>
            </div>
        </div>
    </div>

    <!-- CHARTS ROW -->
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="glass-card">
                <div class="card-header-custom">
                    <div class="card-title"><i class="fas fa-chart-line" style="color:var(--secondary);"></i> Sales Overview (Last 7 Days)</div>
                </div>
                <div class="p-3"><canvas id="salesChartCanvas" style="width:100%; height:280px;"></canvas></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="glass-card">
                <div class="card-header-custom">
                    <div class="card-title"><i class="fas fa-chart-pie" style="color:var(--primary);"></i> Sales by Category</div>
                </div>
                <div class="p-3"><canvas id="categoryChartCanvas" style="width:100%; height:280px;"></canvas></div>
            </div>
        </div>
    </div>

    <!-- RECENT ORDERS & RIGHT COLUMN -->
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="glass-card">
                <div class="card-header-custom">
                    <div class="card-title"><i class="fas fa-history"></i> Recent Orders</div>
                    <a href="<?= $baseUrl ?>/index.php?page=manage_orders" class="btn btn-sm" style="background:var(--primary);color:white;border-radius:30px;padding:0.3rem 1rem;">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr><th>Order #</th><th>Customer</th><th>Amount</th><th>Status</th><th>Date</th></tr>
                        </thead>
                        <tbody id="recentOrdersBody">
                            <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($order['order_number']) ?></strong><br><small class="text-muted">by <?= htmlspecialchars($order['cashier_name']) ?></small></td>
                                <td><?= !empty($order['customer_name']) ? htmlspecialchars($order['customer_name']) : '<span class="text-muted">Walk-in</span>' ?></td>
                                <td>₱<?= number_format($order['total_amount'], 2) ?></td>
                                <td><span class="status-badge status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></td>
                                <td><?= date('M d, H:i', strtotime($order['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="glass-card">
                <div class="card-header-custom">
                    <div class="card-title"><i class="fas fa-star" style="color:var(--accent);"></i> Top Selling Items</div>
                </div>
                <div id="topItemsList">
                    <?php foreach ($topItems as $index => $item): ?>
                    <div class="top-item-row">
                        <div><span class="fw-bold"><?= $index+1 ?>.</span> <?= htmlspecialchars($item['item_name']) ?><br><small class="text-muted">₱<?= number_format($item['variant_price'],2) ?> each</small></div>
                        <div class="text-end"><span class="badge-count"><?= $item['total_sold'] ?> sold</span><br><small class="fw-bold text-success">₱<?= number_format($item['total_revenue'],2) ?></small></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="glass-card mt-4">
                <div class="card-header-custom">
                    <div class="card-title"><i class="fas fa-exclamation-triangle" style="color:var(--danger);"></i> Low Stock Alert <span class="badge-count ms-2" id="lowStockBadgeCount"><?= $stats['low_stock'] ?></span></div>
                </div>
                <div id="lowStockContainer">
                    <?php if (empty($lowStock)): ?>
                        <div class="p-3 text-center text-success"><i class="fas fa-check-circle me-1"></i> All ingredients well stocked</div>
                    <?php else: ?>
                        <?php foreach ($lowStock as $ingredient):
                            $critical = $ingredient['stock_quantity'] <= ($ingredient['low_stock_alert'] * 0.3);
                            $percent  = min(100, ($ingredient['stock_quantity'] / $ingredient['low_stock_alert']) * 100);
                        ?>
                        <div class="stock-item <?= $critical ? 'stock-critical' : '' ?>">
                            <div class="d-flex justify-content-between"><strong><?= htmlspecialchars($ingredient['ingredient_name']) ?></strong><span class="badge <?= $critical ? 'bg-danger' : 'bg-warning' ?>"><?= $critical ? 'Critical' : 'Low' ?></span></div>
                            <div class="small">Current: <?= $ingredient['stock_quantity'] ?> <?= htmlspecialchars($ingredient['unit'] ?? '') ?> / Alert: <?= $ingredient['low_stock_alert'] ?></div>
                            <div class="progress-custom"><div class="progress-fill <?= $critical ? 'danger' : '' ?>" style="width:<?= $percent ?>%;"></div></div>
                        </div>
                        <?php endforeach; ?>
                        <div class="p-2 text-center"><a href="<?= $baseUrl ?>/index.php?page=manage_ingredients" class="text-decoration-none fw-semibold">Manage ingredients →</a></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</main>

<footer class="main-footer">
    <div class="d-flex justify-content-between flex-wrap">
        <span><strong>Copyright &copy; <?= date('Y') ?> The Lounge by: CJV & TEK</strong>. All rights reserved.</span>
        <span class="text-muted">Version 2.0.0</span>
    </div>
</footer>

<!-- WiFi Configuration Modal -->
<div class="modal fade" id="wifiConfigModal" tabindex="-1" aria-labelledby="wifiConfigModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: var(--radius-xl); border: 1px solid var(--border); overflow: hidden;">
            <div class="modal-header" style="background: #f8faff; border-bottom: 1px solid var(--border); padding: 1.1rem 1.5rem;">
                <h5 class="modal-title" id="wifiConfigModalLabel" style="font-weight: 700; color: var(--navy); display: flex; align-items: center; gap: 0.5rem;">
                    <span style="width:32px;height:32px;background:rgba(15,76,92,0.08);border-radius:var(--radius-sm);display:inline-flex;align-items:center;justify-content:center;color:var(--primary);font-size:0.9rem;">
                        <i class="fas fa-wifi"></i>
                    </span>
                    Configure WiFi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 1.5rem;">
                <div id="wifiLoadingState" class="text-center py-3" style="display:none;">
                    <i class="fas fa-spinner fa-spin" style="color:var(--primary);font-size:1.5rem;"></i>
                    <div class="mt-2 text-muted" style="font-size:0.85rem;">Loading current settings…</div>
                </div>
                <div id="wifiFormBody">
                    <div class="mb-3">
                        <label for="wifiSSID" class="form-label" style="font-weight:600;font-size:0.85rem;color:var(--navy);">Network Name (SSID)</label>
                        <input type="text" class="form-control" id="wifiSSID" name="wifi_ssid"
                               placeholder="e.g., The Lounge"
                               style="border-radius:var(--radius-md);border-color:var(--border);font-size:0.9rem;">
                    </div>
                    <div class="mb-1">
                        <label for="wifiPassword" class="form-label" style="font-weight:600;font-size:0.85rem;color:var(--navy);">Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="wifiPassword" name="wifi_password"
                                   placeholder="Enter WiFi password"
                                   style="border-radius:var(--radius-md) 0 0 var(--radius-md);border-color:var(--border);font-size:0.9rem;">
                            <button class="btn btn-outline-secondary" type="button" id="toggleWifiPw"
                                    style="border-color:var(--border);border-radius:0 var(--radius-md) var(--radius-md) 0;color:var(--warm-gray);">
                                <i class="fas fa-eye" id="toggleWifiPwIcon"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="background:#f8faff;border-top:1px solid var(--border);padding:1rem 1.5rem;gap:0.6rem;">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                        style="border-radius:30px;border:1px solid var(--border);font-size:0.85rem;padding:0.45rem 1.2rem;">
                    Cancel
                </button>
                <button type="button" class="btn" id="saveWifiConfig"
                        style="background:var(--primary);color:white;border-radius:30px;font-size:0.85rem;padding:0.45rem 1.4rem;font-weight:600;border:none;">
                    <i class="fas fa-save me-2"></i>Save Configuration
                </button>
            </div>
        </div>
    </div>
</div>

<!-- TOAST STACK -->
<div class="toast-stack" id="toastStack"></div>

<script src="<?= $baseUrl ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
    /* ── TOAST SYSTEM ─────────────────────────────────────────────────────── */
    const TOAST_ICONS = {
        success: 'fa-check-circle',
        error:   'fa-times-circle',
        warning: 'fa-exclamation-triangle',
        info:    'fa-info-circle',
    };
    const TOAST_TITLES = {
        success: 'Success',
        error:   'Error',
        warning: 'Warning',
        info:    'Info',
    };

    /**
     * showToast(message, type, duration)
     * type: 'success' | 'error' | 'warning' | 'info'  (default 'info')
     * duration: ms before auto-dismiss  (default 4000; 0 = sticky)
     */
    window.showToast = function(message, type = 'info', duration = 4000) {
        const stack = document.getElementById('toastStack');
        const icon  = TOAST_ICONS[type]  || 'fa-info-circle';
        const title = TOAST_TITLES[type] || 'Notice';

        const el = document.createElement('div');
        el.className = `toast-item toast-${type}`;
        el.innerHTML = `
            <div class="toast-icon"><i class="fas ${icon}"></i></div>
            <div class="toast-body">
                <div class="toast-title">${title}</div>
                <div class="toast-msg">${escapeHtml(message)}</div>
            </div>
            <button class="toast-close" aria-label="Close"><i class="fas fa-times"></i></button>
        `;

        const dismiss = () => {
            el.classList.add('toast-out');
            el.addEventListener('animationend', () => el.remove(), { once: true });
        };

        el.querySelector('.toast-close').addEventListener('click', dismiss);
        stack.appendChild(el);

        if (duration > 0) setTimeout(dismiss, duration);
        return el;
    };

    /* ── SHARED UTILS ─────────────────────────────────────────────────────── */
    window.escapeHtml = function(text) {
        if (text === null || text === undefined) return '';
        const div = document.createElement('div');
        div.textContent = String(text);
        return div.innerHTML;
    };
    window.formatPrice = function(amount) {
        const n = parseFloat(amount || 0);
        return isNaN(n) ? '0.00' : n.toFixed(2);
    };
    window.autoDismissAlerts = function(timeout) {
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(a => {
                try { bootstrap.Alert.getOrCreateInstance(a).close(); } catch(e) {}
            });
        }, timeout || 3000);
    };
    window.setupSidebarToggle = function() {
        const toggle  = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        if (toggle && sidebar && overlay) {
            toggle.addEventListener('click', () => {
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
                document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : '';
            });
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
                document.body.style.overflow = '';
            });
        }
    };
    window.setupModalBackdropCleanup = function() {
        document.addEventListener('hidden.bs.modal', function() {
            if (document.querySelector('.modal.show')) return;
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        });
    };

    setupSidebarToggle();
    setupModalBackdropCleanup();
    autoDismissAlerts(5000);

    /* ── CHARTS ───────────────────────────────────────────────────────────── */
    const dailyLabels = [<?php foreach ($dailySales as $s) { echo "'" . date('M d', strtotime($s['date'])) . "',"; } ?>];
    const dailyData   = [<?php foreach ($dailySales as $s) { echo (float)$s['total_sales'] . ","; } ?>];
    const catLabels   = [<?php foreach ($salesByCategory as $c) { echo "'" . addslashes($c['category_name']) . "',"; } ?>];
    const catData     = [<?php foreach ($salesByCategory as $c) { echo (float)$c['total_sales'] . ","; } ?>];
    const colorPalette = ['#0f4c5c','#ff6b4a','#fee440','#1e2f4e','#5e9b9e','#ff8c70'];

    const salesCtx = document.getElementById('salesChartCanvas').getContext('2d');
    const salesChart = new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: dailyLabels,
            datasets: [{
                label: 'Daily Sales (₱)',
                data: dailyData,
                borderColor: '#0f4c5c',
                backgroundColor: 'rgba(15,76,92,0.05)',
                borderWidth: 2.5,
                tension: 0.3,
                fill: true,
                pointBackgroundColor: '#ff6b4a',
                pointRadius: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { tooltip: { callbacks: { label: ctx => `₱${ctx.raw.toLocaleString()}` } } }
        }
    });

    const catCtx = document.getElementById('categoryChartCanvas').getContext('2d');
    const categoryChart = new Chart(catCtx, {
        type: 'doughnut',
        data: {
            labels: catLabels,
            datasets: [{
                data: catData,
                backgroundColor: catLabels.map((_,i) => colorPalette[i % colorPalette.length]),
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            cutout: '65%',
            plugins: {
                legend: { position: 'bottom' },
                tooltip: { callbacks: { label: ctx => `${ctx.label}: ₱${ctx.raw.toLocaleString()}` } }
            }
        }
    });

    /* ── DASHBOARD POLLING ────────────────────────────────────────────────── */
    const BASE_URL = "<?= rtrim(str_replace('/public', '', $baseUrl), '/') ?>";
    const POLL_URL = BASE_URL.replace('/public', '') + '/app/ajax/get_dashboard_counts.php';

    async function refreshDashboard() {
        try {
            const resp = await fetch(POLL_URL, { cache: 'no-store' });
            const data = await resp.json();
            if (!data.success) return;

            document.getElementById('statRevenue').innerText = '₱' + formatPrice(data.today_revenue);
            document.querySelector('.stat-card .small.text-muted').innerText = 'Monthly: ₱' + formatPrice(data.monthly_revenue);
            document.getElementById('statOrders').innerText = data.today_orders;
            document.getElementById('statCustomersFooter').innerHTML = data.today_customers + ' customers today';
            document.getElementById('statActiveOrders').innerText = data.total_active;
            document.getElementById('activeOrdersFooter').innerHTML = `${data.ready_count} ready · <a href="${BASE_URL}/index.php?page=manage_orders" class="text-decoration-none fw-semibold">View all</a>`;
            document.getElementById('statLowStock').innerText = data.low_stock;
            document.getElementById('lowStockBadgeCount').innerText = data.low_stock;

            const notifMenu = document.getElementById('notifDropdownMenu');
            if (notifMenu) {
                let html = '<li><h6 class="dropdown-header">Notifications</h6></li>';
                if (data.pending_count > 0)   html += `<li><a class="dropdown-item" href="${BASE_URL}/index.php?page=manage_orders"><i class="fas fa-hourglass-half me-2"></i> ${data.pending_count} order(s) waiting</a></li>`;
                if (data.preparing_count > 0) html += `<li><a class="dropdown-item" href="${BASE_URL}/index.php?page=manage_orders"><i class="fas fa-bowl-food me-2"></i> ${data.preparing_count} being prepared</a></li>`;
                if (data.ready_count > 0)     html += `<li><a class="dropdown-item" href="${BASE_URL}/index.php?page=manage_orders"><i class="fas fa-circle-check me-2"></i> ${data.ready_count} ready for payment</a></li>`;
                if (data.low_stock > 0)       html += `<li><a class="dropdown-item" href="${BASE_URL}/index.php?page=manage_ingredients"><i class="fas fa-exclamation-triangle me-2"></i> ${data.low_stock} low stock item(s)</a></li>`;
                if (data.total_active === 0 && data.low_stock === 0) html += `<li><span class="dropdown-item-text text-muted"><i class="fas fa-check-circle me-2"></i>All caught up!</span></li>`;
                notifMenu.innerHTML = html;
            }

            const totalNotif = (data.pending_count||0)+(data.preparing_count||0)+(data.ready_count||0)+(data.low_stock||0);
            const badgeSpan  = document.querySelector('#notifDropdownBtn .badge-notify');
            if (totalNotif > 0) {
                if (!badgeSpan) document.getElementById('notifDropdownBtn').insertAdjacentHTML('beforeend','<span class="badge-notify"></span>');
                const nb = document.querySelector('#notifDropdownBtn .badge-notify');
                if (nb) nb.innerText = totalNotif;
            } else if (badgeSpan) {
                badgeSpan.remove();
            }

            if (data.recent_orders) {
                let rows = '';
                data.recent_orders.forEach(o => {
                    rows += `<tr>
                        <td><strong>${escapeHtml(o.order_number)}</strong><br><small class="text-muted">by ${escapeHtml(o.cashier_name)}</small></td>
                        <td>${o.customer_name ? escapeHtml(o.customer_name) : '<span class="text-muted">Walk-in</span>'}</td>
                        <td>₱${formatPrice(o.total_amount)}</td>
                        <td><span class="status-badge status-${o.status}">${o.status.charAt(0).toUpperCase()+o.status.slice(1)}</span></td>
                        <td>${new Date(o.created_at).toLocaleString(undefined,{month:'short',day:'numeric',hour:'2-digit',minute:'2-digit'})}</td>
                    </tr>`;
                });
                document.getElementById('recentOrdersBody').innerHTML = rows;
            }

            if (data.top_items) {
                let itemsHtml = '';
                data.top_items.forEach((it, idx) => {
                    itemsHtml += `<div class="top-item-row">
                        <div><span class="fw-bold">${idx+1}.</span> ${escapeHtml(it.item_name)}<br><small class="text-muted">₱${formatPrice(it.variant_price)} each</small></div>
                        <div class="text-end"><span class="badge-count">${it.total_sold} sold</span><br><small class="fw-bold text-success">₱${formatPrice(it.total_revenue)}</small></div>
                    </div>`;
                });
                document.getElementById('topItemsList').innerHTML = itemsHtml;
            }

            if (data.low_stock_items) {
                let stockHtml = '';
                if (data.low_stock_items.length === 0) {
                    stockHtml = '<div class="p-3 text-center text-success"><i class="fas fa-check-circle me-1"></i> All ingredients well stocked</div>';
                } else {
                    data.low_stock_items.forEach(ing => {
                        const critical = ing.stock_quantity <= (ing.low_stock_alert * 0.3);
                        const percent  = Math.min(100, (ing.stock_quantity / ing.low_stock_alert) * 100);
                        stockHtml += `<div class="stock-item ${critical ? 'stock-critical' : ''}">
                            <div class="d-flex justify-content-between"><strong>${escapeHtml(ing.ingredient_name)}</strong><span class="badge ${critical ? 'bg-danger' : 'bg-warning'}">${critical ? 'Critical' : 'Low'}</span></div>
                            <div class="small">Current: ${ing.stock_quantity} ${escapeHtml(ing.unit)} / Alert: ${ing.low_stock_alert}</div>
                            <div class="progress-custom"><div class="progress-fill ${critical ? 'danger' : ''}" style="width:${percent}%;"></div></div>
                        </div>`;
                    });
                    stockHtml += `<div class="p-2 text-center"><a href="${BASE_URL}/index.php?page=manage_ingredients" class="text-decoration-none fw-semibold">Manage ingredients →</a></div>`;
                }
                document.getElementById('lowStockContainer').innerHTML = stockHtml;
            }

            if (data.daily_sales) {
                salesChart.data.labels = data.daily_sales.map(s => new Date(s.date+'T00:00:00').toLocaleString('en-US',{month:'short',day:'numeric'}));
                salesChart.data.datasets[0].data = data.daily_sales.map(s => parseFloat(s.total_sales));
                salesChart.update('none');
            }
            if (data.sales_by_category) {
                categoryChart.data.labels = data.sales_by_category.map(c => c.category_name);
                categoryChart.data.datasets[0].data = data.sales_by_category.map(c => parseFloat(c.total_sales));
                categoryChart.data.datasets[0].backgroundColor = data.sales_by_category.map((_,i) => colorPalette[i % colorPalette.length]);
                categoryChart.update('none');
            }
        } catch(e) { console.warn(e); }
    }

    refreshDashboard();
    setInterval(refreshDashboard, 5000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) refreshDashboard(); });

    /* ── WIFI CONFIGURATION ───────────────────────────────────────────────── */
    const WIFI_URL      = BASE_URL.replace('/public', '') + '/app/ajax/configure_wifi.php';
    const wifiSSIDInput = document.getElementById('wifiSSID');
    const wifiPwInput   = document.getElementById('wifiPassword');
    const saveWifiBtn   = document.getElementById('saveWifiConfig');
    const wifiFormBody  = document.getElementById('wifiFormBody');
    const wifiLoading   = document.getElementById('wifiLoadingState');

    // Password show/hide toggle
    document.getElementById('toggleWifiPw').addEventListener('click', function() {
        const isHidden = wifiPwInput.type === 'password';
        wifiPwInput.type = isHidden ? 'text' : 'password';
        document.getElementById('toggleWifiPwIcon').className = isHidden ? 'fas fa-eye-slash' : 'fas fa-eye';
    });

    // Load current WiFi settings when modal opens
    document.getElementById('wifiConfigModal').addEventListener('show.bs.modal', async function() {
        wifiSSIDInput.value = '';
        wifiPwInput.value   = '';
        wifiPwInput.type    = 'password';
        document.getElementById('toggleWifiPwIcon').className = 'fas fa-eye';
        wifiFormBody.style.display = 'none';
        wifiLoading.style.display  = 'block';

        try {
            const resp = await fetch(WIFI_URL, { cache: 'no-store' });
            const data = await resp.json();
            if (data.success) {
                wifiSSIDInput.value = data.ssid     || '';
                wifiPwInput.value   = data.password || '';
            } else {
                showToast(data.message || 'Could not load WiFi settings.', 'warning');
            }
        } catch(e) {
            showToast('Failed to load WiFi settings.', 'error');
        } finally {
            wifiLoading.style.display  = 'none';
            wifiFormBody.style.display = 'block';
        }
    });

    // Save WiFi configuration
    saveWifiBtn.addEventListener('click', async function() {
        const ssid     = wifiSSIDInput.value.trim();
        const password = wifiPwInput.value.trim();

        if (!ssid) {
            showToast('Please enter the network name (SSID).', 'warning');
            wifiSSIDInput.focus();
            return;
        }
        if (!password) {
            showToast('Please enter the WiFi password.', 'warning');
            wifiPwInput.focus();
            return;
        }

        saveWifiBtn.disabled     = true;
        saveWifiBtn.innerHTML    = '<i class="fas fa-spinner fa-spin me-2"></i>Saving…';

        try {
            const formData = new FormData();
            formData.append('ssid',     ssid);
            formData.append('password', password);

            const resp = await fetch(WIFI_URL, { method: 'POST', body: formData });
            const data = await resp.json();

            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('wifiConfigModal')).hide();
                showToast('WiFi configuration saved successfully!', 'success');
            } else {
                showToast(data.message || 'Failed to save WiFi configuration.', 'error');
            }
        } catch(e) {
            showToast('An error occurred while saving. Please try again.', 'error');
            console.error(e);
        } finally {
            saveWifiBtn.disabled  = false;
            saveWifiBtn.innerHTML = '<i class="fas fa-save me-2"></i>Save Configuration';
        }
    });
</script>
</body>
</html>