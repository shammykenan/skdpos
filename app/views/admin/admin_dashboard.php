<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5">
    <title>Admin Dashboard - Cafeteria POS</title>
    <link rel="icon" type="image/png" href="<?= $baseUrl ?>/assets/images/logo/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #0f4c5c; --primary-dark: #0a3a47; --primary-light: #1f7a8c;
            --secondary: #ff6b4a; --secondary-light: #ff8c70; --accent: #fee440;
            --navy: #1e2f4e; --lavender: #e2e6f0; --light-bg: #f8faff;
            --warm-gray: #4a5568; --border: #cbd5e0; --success: #2c7a5a;
            --danger: #d64045; --warning: #f4b942; --info: #5e9b9e;
            --dark: #1a202c; --light: #ffffff;
            --sidebar-width: 280px; --topbar-height: 72px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--light-bg); color: var(--dark); font-size: 14px; line-height: 1.5; }

        /* ── TOPBAR ─────────────────────────────────────────────────────── */
        .topbar {
            position: fixed; top: 0; left: 0; right: 0;
            height: var(--topbar-height);
            background: white; border-bottom: 1px solid var(--border);
            z-index: 1040; padding: 0 2rem;
            box-shadow: 0 4px 12px rgba(15,76,92,0.04);
            display: flex; align-items: center; justify-content: space-between;
        }

        /* Logo */
        .topbar-left { display: flex; align-items: center; gap: 0.6rem; min-width: 0; flex-shrink: 1; }
        .topbar .logo {
            text-decoration: none;
            display: flex; align-items: center; gap: 0.65rem;
            min-width: 0;
        }
        /* Solid image placeholder — swap div for <img> in production */
        .logo-img-fallback {
            width: 44px; height: 44px; border-radius: 12px; flex-shrink: 0;
            background: var(--primary);
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 1.25rem;
            box-shadow: 0 4px 10px rgba(15,76,92,0.25);
        }
        /* Two-line logo text */
        .logo-text-wrap {
            display: flex; flex-direction: column; line-height: 1.1;
            min-width: 0;
        }
        .logo-text-main {
            font-family: 'Playfair Display', Georgia, serif;
            font-weight: 800; font-size: 1.2rem;
            color: var(--primary-dark);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            letter-spacing: -0.3px;
        }
        .logo-text-sub {
            font-family: 'DM Sans', sans-serif;
            font-weight: 500; font-size: 0.68rem;
            color: var(--secondary);
            text-transform: uppercase; letter-spacing: 1.4px;
            white-space: nowrap;
        }

        .mobile-menu-btn {
            background: white; border: 1px solid var(--border);
            font-size: 1.2rem; color: var(--primary); cursor: pointer;
            padding: 0.45rem 0.85rem; border-radius: 12px; transition: all 0.2s;
            flex-shrink: 0;
        }
        .mobile-menu-btn:hover { background: var(--secondary); border-color: var(--secondary); color: white; }

        /* ── Topbar RIGHT cluster ────────────────────────────────────────── */
        .topbar-right {
            display: flex; align-items: center;
            gap: 0.5rem; flex-shrink: 0;
        }

        .notification-btn {
            position: relative; background: white; border: 1px solid var(--border);
            font-size: 1.15rem; color: var(--primary); cursor: pointer;
            padding: 0.55rem 0.85rem; border-radius: 12px; transition: all 0.2s;
            flex-shrink: 0;
        }
        .notification-btn:hover { background: var(--lavender); border-color: var(--primary-light); color: var(--primary-dark); }
        .notification-badge {
            position: absolute; top: -6px; right: -6px;
            background: var(--secondary); color: white;
            border-radius: 50px; width: 20px; height: 20px;
            font-size: 0.68rem; display: flex; align-items: center;
            justify-content: center; font-weight: 700; border: 2px solid white;
        }

        /* ── User button — always visible, responsive ─────────────────── */
        .user-btn {
            color: var(--navy) !important;
            font-weight: 600;
            display: flex !important;
            align-items: center;
            gap: 0.35rem;
            padding: 0.45rem 0.75rem !important;
            border-radius: 12px !important;
            border: 1px solid var(--border) !important;
            background: white;
            text-decoration: none !important;
            transition: all 0.2s;
            flex-shrink: 0;
            max-width: 200px;
        }
        .user-btn:hover { background: var(--lavender); border-color: var(--primary-light) !important; }
        .user-btn .user-avatar {
            width: 30px; height: 30px; border-radius: 50%;
            background: var(--primary);
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 0.8rem; font-weight: 700;
            flex-shrink: 0;
        }
        .user-btn .user-name {
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            font-size: 0.875rem; font-weight: 600; color: var(--navy);
        }
        /* Hide the Bootstrap dropdown caret on very small screens only */
        .user-btn::after { display: none !important; }
        /* Custom caret */
        .user-btn .user-caret { color: var(--warm-gray); font-size: 0.7rem; flex-shrink: 0; }

        /* ── SIDEBAR ─────────────────────────────────────────────────────── */
        .sidebar {
            position: fixed; top: var(--topbar-height); left: 0;
            width: var(--sidebar-width); height: calc(100vh - var(--topbar-height));
            background: white; border-right: 1px solid var(--border);
            overflow-y: auto; transition: transform 0.25s cubic-bezier(0.4,0,0.2,1);
            z-index: 1030; box-shadow: 2px 0 12px rgba(0,0,0,0.02);
        }
        .sidebar::-webkit-scrollbar { width: 5px; }
        .sidebar::-webkit-scrollbar-thumb { background: var(--border); border-radius: 8px; }
        .sidebar-nav { padding: 2rem 0.75rem; }
        .nav-section-title {
            padding: 0.6rem 1.2rem; font-size: 0.7rem; font-weight: 700;
            text-transform: uppercase; color: var(--warm-gray); letter-spacing: 1.2px;
            opacity: 0.7; margin-top: 0.8rem;
        }
        .nav-item { margin: 0.2rem 0.6rem; }
        .nav-link {
            display: flex; align-items: center; padding: 0.8rem 1.2rem;
            color: var(--warm-gray); text-decoration: none;
            border-radius: 14px; transition: all 0.2s; font-weight: 500; font-size: 0.95rem;
        }
        .nav-link:hover { background: var(--lavender); color: var(--primary-dark); }
        .nav-link.active { background: var(--primary); color: white; box-shadow: 0 6px 12px rgba(15,76,92,0.2); }
        .nav-link i { width: 24px; margin-right: 0.85rem; font-size: 1.2rem; text-align: center; }

        /* ── MAIN ─────────────────────────────────────────────────────────── */
        .main-content {
            margin-left: var(--sidebar-width); margin-top: var(--topbar-height);
            padding: 2.2rem; min-height: calc(100vh - var(--topbar-height));
            transition: margin-left 0.2s;
        }
        .page-header { margin-bottom: 2.2rem; }
        .page-header h1 { font-size: 2.1rem; font-weight: 700; color: var(--navy); margin-bottom: 0.3rem; display: flex; align-items: center; gap: 0.7rem; }
        .page-header h1 i { color: var(--secondary); font-size: 2.1rem; }
        .breadcrumb { background: none; padding: 0.2rem 0; margin: 0; font-size: 0.9rem; }
        .breadcrumb-item a { color: var(--warm-gray); text-decoration: none; font-weight: 500; }
        .breadcrumb-item a:hover { color: var(--primary); }
        .breadcrumb-item.active { color: var(--primary-dark); font-weight: 600; }

        /* ── STAT CARDS ────────────────────────────────────────────────────── */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px,1fr)); gap: 1.8rem; margin-bottom: 2.5rem; }
        .stat-card { background: white; border-radius: 24px; padding: 1.6rem 1.5rem; border: 1px solid rgba(203,213,224,0.5); transition: all 0.25s; position: relative; overflow: hidden; box-shadow: 0 8px 20px rgba(15,76,92,0.04); }
        .stat-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, var(--primary), var(--secondary)); opacity: 0.9; }
        .stat-card.secondary::before { background: var(--secondary); }
        .stat-card.info::before { background: var(--info); }
        .stat-card.success::before { background: var(--success); }
        .stat-card:hover { transform: translateY(-6px); box-shadow: 0 16px 28px rgba(15,76,92,0.08); border-color: var(--primary-light); }
        .stat-card .icon { width: 56px; height: 56px; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin-bottom: 1.2rem; background: #f0f5fa; color: var(--primary); border: 1px solid rgba(15,76,92,0.1); }
        .stat-card .label { font-size: 0.8rem; color: var(--warm-gray); margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.8px; font-weight: 700; }
        .stat-card .value { font-size: 2.3rem; font-weight: 700; color: var(--navy); line-height: 1.1; margin-bottom: 0.5rem; }
        .stat-card .footer { font-size: 0.85rem; color: var(--warm-gray); border-top: 1px solid rgba(203,213,224,0.5); padding-top: 0.7rem; margin-top: 0.3rem; }

        /* ── CARDS ────────────────────────────────────────────────────────── */
        .card { background: white; border-radius: 24px; border: 1px solid rgba(203,213,224,0.5); box-shadow: 0 8px 20px rgba(15,76,92,0.02); margin-bottom: 1.8rem; transition: box-shadow 0.2s; }
        .card:hover { box-shadow: 0 14px 26px rgba(15,76,92,0.06); }
        .card-header { background: white; border-bottom: 1px solid rgba(203,213,224,0.5); padding: 1.35rem 1.8rem; border-radius: 24px 24px 0 0; }
        .card-body { padding: 1.6rem 1.8rem; }
        .card-title { font-size: 1.18rem; font-weight: 650; color: var(--navy); display: flex; align-items: center; gap: 0.6rem; }

        /* ── TABLE ────────────────────────────────────────────────────────── */
        .table-container { overflow-x: auto; margin: 0; }
        .table { margin: 0; border-collapse: separate; border-spacing: 0; }
        .table thead th { background: #f2f6fc; border-bottom: 2px solid var(--primary-light); font-weight: 600; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.8px; color: var(--primary-dark); padding: 1.1rem 1.2rem; white-space: nowrap; }
        .table tbody td { padding: 1.2rem 1.2rem; vertical-align: middle; border-bottom: 1px solid #edf2f7; color: var(--dark); background: white; }
        .table tbody tr:hover td { background-color: #fafcff; }

        /* ── BADGES / STATUS ─────────────────────────────────────────────── */
        .badge { padding: 0.45rem 1rem; border-radius: 30px; font-weight: 600; font-size: 0.75rem; letter-spacing: 0.3px; }
        .order-status { padding: 0.45rem 1rem; border-radius: 30px; font-size: 0.75rem; font-weight: 600; display: inline-block; min-width: 100px; text-align: center; }
        .status-pending   { background: #fff3cd; color: #856404; border-left: 3px solid #ffc107; }
        .status-preparing { background: #e1f0fa; color: #0f4c5c; border-left: 3px solid #0f4c5c; }
        .status-ready     { background: #d4f4e2; color: #1a5c3a; border-left: 3px solid #2c7a5a; }
        .status-completed { background: #d4edda; color: #155724; border-left: 3px solid #28a745; }
        .status-cancelled { background: #f8d7da; color: #721c24; border-left: 3px solid #dc3545; }

        /* ── STOCK ────────────────────────────────────────────────────────── */
        .low-stock-item { border-left: 6px solid var(--accent); background: #fffef7; border-radius: 16px; margin-bottom: 8px; border: 1px solid rgba(254,228,64,0.3); }
        .critical-stock { border-left: 6px solid var(--danger); background: #fff5f3; border: 1px solid rgba(214,64,69,0.2); }
        .progress { height: 8px; border-radius: 12px; background: #e2e8f0; }
        .progress-bar { border-radius: 12px; }

        /* ── BUTTONS ─────────────────────────────────────────────────────── */
        .btn { padding: 0.6rem 1.5rem; border-radius: 40px; font-weight: 600; font-size: 0.85rem; transition: all 0.18s; letter-spacing: 0.3px; }
        .btn-primary { background: var(--primary); border: none; color: white; }
        .btn-primary:hover { background: var(--primary-dark); transform: translateY(-2px); box-shadow: 0 10px 18px rgba(15,76,92,0.25); }

        /* ── DROPDOWN ────────────────────────────────────────────────────── */
        .dropdown-menu { border-radius: 20px; border: 1px solid var(--border); box-shadow: 0 12px 24px rgba(0,0,0,0.04); padding: 0.6rem; }
        .dropdown-item { border-radius: 14px; padding: 0.6rem 1.2rem; font-size: 0.9rem; }
        .dropdown-item:hover { background: var(--lavender); color: var(--primary-dark); }

        /* ── FOOTER ──────────────────────────────────────────────────────── */
        .main-footer { background: white; border-top: 1px solid var(--border); padding: 1.5rem 2.2rem; margin-left: var(--sidebar-width); color: var(--warm-gray); }

        canvas { max-height: 280px; width: 100% !important; }

        /* ── RESPONSIVE ──────────────────────────────────────────────────── */
        @media (max-width: 1199.98px) { .stats-grid { grid-template-columns: repeat(2,1fr); } }

        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; padding: 1.8rem; }
            .main-footer { margin-left: 0; }
            .sidebar-overlay {
                position: fixed; top: var(--topbar-height); left: 0; right: 0; bottom: 0;
                background: rgba(15,76,92,0.2); backdrop-filter: blur(3px);
                z-index: 1025; display: none;
            }
            .sidebar-overlay.show { display: block; }

            /* Topbar: shrink padding on tablet */
            .topbar { padding: 0 1rem; }
        }

        @media (max-width: 767.98px) {
            .main-content { padding: 1.2rem; }
            .stats-grid { grid-template-columns: 1fr; }
            .page-header h1 { font-size: 1.8rem; }
            .stat-card .value { font-size: 2rem; }
            .topbar { padding: 0 0.85rem; }

            /* Slightly smaller logo image on mobile */
            .logo-img-fallback { width: 36px; height: 36px; font-size: 1.1rem; border-radius: 10px; }
            .logo-text-main { font-size: 1.05rem; }
            .logo-text-sub { font-size: 0.62rem; }

            /* Notification / DB buttons: tighter */
            .notification-btn { padding: 0.45rem 0.65rem; font-size: 1.05rem; }

            /* User button: keep name always visible but tighter */
            .user-btn { padding: 0.4rem 0.6rem !important; max-width: 160px; gap: 0.3rem; }
            .user-btn .user-name { font-size: 0.8rem; }
            .user-btn .user-avatar { width: 26px; height: 26px; font-size: 0.72rem; }
        }

        @media (max-width: 480px) {
            .topbar { padding: 0 0.6rem; }
            .topbar-right { gap: 0.3rem; }

            /* On very small screens: hide tagline, keep main brand name */
            .logo-text-sub { display: none; }
            .logo-text-main { font-size: 0.95rem; }

            /* User name stays visible — just constrained */
            .user-btn { max-width: 130px; padding: 0.38rem 0.55rem !important; }
            .user-btn .user-name { font-size: 0.78rem; }

            /* Hide the DB button icon on tiny screens to save space */
            .db-btn { display: none !important; }

            .notification-btn { padding: 0.4rem 0.6rem; font-size: 1rem; }
        }

        /* ── Extra-small: don't hide the user name ───────────────────────── */
        /* Override: never hide .user-name at any breakpoint */
        .user-btn .user-name { display: block !important; }

        .fade-in { animation: modernFade 0.4s ease forwards; }
        @keyframes modernFade { from { opacity: 0.6; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
    <div class="topbar-left">
        <!-- Mobile hamburger -->
        <button class="mobile-menu-btn d-lg-none" id="menuToggle"><i class="fas fa-bars"></i></button>

        <!-- Logo: image placeholder + two-line brand text -->
        <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="logo">
            <!--
                LOGO IMAGE PLACEHOLDER
                Replace the div below with:
                <img src="<?= $baseUrl ?>/assets/images/logo/logo.png" alt="Dine with Din"
                     style="width:44px;height:44px;border-radius:12px;object-fit:cover;">
            -->
            <div class="logo-img-fallback" title="Logo placeholder">
                <img src="<?= $baseUrl ?>/assets/images/logo/logo.png" alt="Dine with Din"   style="width:44px;height:44px;border-radius:12px;object-fit:cover;">
            </div>
            <div class="logo-text-wrap">
                <span class="logo-text-main">Dine with Din</span>
                <span class="logo-text-sub">Restaurant Management System</span>
            </div>
        </a>
    </div>

    <div class="topbar-right">

        <!-- Notifications -->
        <div class="dropdown">
            <button class="notification-btn" type="button" id="notificationDropdown" data-bs-toggle="dropdown">
                <i class="far fa-bell"></i>
                <?php if ($stats['low_stock'] > 0 || $stats['pending_orders'] > 0): ?>
                    <span class="notification-badge"><?= $stats['low_stock'] + $stats['pending_orders'] ?></span>
                <?php endif; ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" style="min-width:240px;">
                <li><h6 class="dropdown-header">Notifications</h6></li>
                <?php if ($stats['pending_count'] > 0): ?>
                    <li><a class="dropdown-item" href="<?= $baseUrl ?>/index.php?page=manage_orders">
                        <i class="fas fa-hourglass-half me-2" style="color:var(--warning);"></i>
                        <?= $stats['pending_count'] ?> order<?= $stats['pending_count'] > 1 ? 's' : '' ?> waiting
                    </a></li>
                <?php endif; ?>
                <?php if ($stats['preparing_count'] > 0): ?>
                    <li><a class="dropdown-item" href="<?= $baseUrl ?>/index.php?page=manage_orders">
                        <i class="fas fa-bowl-food me-2" style="color:var(--primary-light);"></i>
                        <?= $stats['preparing_count'] ?> order<?= $stats['preparing_count'] > 1 ? 's' : '' ?> being prepared
                    </a></li>
                <?php endif; ?>
                <?php if ($stats['ready_count'] > 0): ?>
                    <li><a class="dropdown-item" href="<?= $baseUrl ?>/index.php?page=manage_orders">
                        <i class="fas fa-circle-check me-2" style="color:var(--success);"></i>
                        <?= $stats['ready_count'] ?> order<?= $stats['ready_count'] > 1 ? 's' : '' ?> ready for payment
                    </a></li>
                <?php endif; ?>
                <?php if ($stats['low_stock'] > 0): ?>
                    <li><a class="dropdown-item" href="<?= $baseUrl ?>/index.php?page=manage_ingredients">
                        <i class="fas fa-exclamation-triangle me-2" style="color:var(--secondary);"></i>
                        <?= $stats['low_stock'] ?> low stock item<?= $stats['low_stock'] > 1 ? 's' : '' ?>
                    </a></li>
                <?php endif; ?>
                <?php if ($stats['pending_orders'] === 0 && $stats['low_stock'] === 0): ?>
                    <li><span class="dropdown-item text-muted">
                        <i class="fas fa-check-circle me-2" style="color:var(--success);"></i>All caught up!
                    </span></li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Database — hidden on very small screens via .db-btn -->
        <div class="dropdown db-btn">
            <button class="notification-btn" type="button" data-bs-toggle="dropdown" title="Database Backup &amp; Restore">
                <i class="fas fa-database"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" style="min-width:220px;border-radius:16px;padding:.6rem;">
                <li><h6 class="dropdown-header" style="font-size:.7rem;letter-spacing:.7px;text-transform:uppercase;color:#4a5568;">Database</h6></li>
                <li><a class="dropdown-item" style="border-radius:10px;" href="<?= $baseUrl ?>/index.php?page=backup_database&action=export">
                    <i class="fas fa-download me-2" style="color:#0f4c5c;width:16px;text-align:center;"></i>Export Backup <small class="text-muted ms-1">.sql</small>
                </a></li>
                <li><a class="dropdown-item" style="border-radius:10px;" href="<?= $baseUrl ?>/index.php?page=backup_database&action=import"
                    onclick="return confirm('Importing will overwrite all existing data.\nMake sure you have a current backup.\n\nContinue?')">
                    <i class="fas fa-upload me-2" style="color:#ff6b4a;width:16px;text-align:center;"></i>Import / Restore
                </a></li>
            </ul>
        </div>

        <!-- User — avatar + full name, always visible -->
        <div class="dropdown">
            <button class="btn user-btn dropdown-toggle"
                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <!-- Avatar circle with initials -->
                <span class="user-avatar" aria-hidden="true">
                    <?php
                        $fullName = $_SESSION['full_name'] ?? 'Admin';
                        $parts    = explode(' ', trim($fullName));
                        $initials = strtoupper(substr($parts[0], 0, 1));
                        if (count($parts) > 1) $initials .= strtoupper(substr(end($parts), 0, 1));
                        echo htmlspecialchars($initials);
                    ?>
                </span>
                <span class="user-name"><?= htmlspecialchars($fullName) ?></span>
                <i class="fas fa-chevron-down user-caret ms-1"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <div class="px-3 py-2" style="border-bottom:1px solid var(--border);margin-bottom:0.4rem;">
                        <div style="font-weight:700;color:var(--navy);font-size:0.9rem;"><?= htmlspecialchars($fullName) ?></div>
                        <div style="font-size:0.78rem;color:var(--warm-gray);">Administrator</div>
                    </div>
                </li>
                <li><a class="dropdown-item text-danger" href="<?= $baseUrl ?>/index.php?page=logout">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a></li>
            </ul>
        </div>
    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-nav">
        <div class="nav-section-title">DASHBOARD</div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="nav-link active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></div>
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
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=reports" class="nav-link"><i class="fas fa-chart-bar"></i> Reports</a></div>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-tachometer-alt me-2"></i> Dashboard</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </nav>
    </div>

    <!-- STATS -->
    <div class="stats-grid">
        <div class="stat-card primary fade-in">
            <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
            <div class="label">Today's Revenue</div>
            <div class="value">₱<?= number_format($stats['today_revenue'], 2) ?></div>
            <div class="footer">Monthly: ₱<?= number_format($stats['monthly_revenue'], 2) ?></div>
        </div>
        <div class="stat-card secondary fade-in" style="animation-delay:0.05s">
            <div class="icon"><i class="fas fa-shopping-cart"></i></div>
            <div class="label">Today's Orders</div>
            <div class="value"><?= $stats['today_orders'] ?></div>
            <div class="footer"><?= $stats['today_customers'] ?> customers today</div>
        </div>
        <div class="stat-card info fade-in" style="animation-delay:0.1s">
            <div class="icon"><i class="fas fa-clock"></i></div>
            <div class="label">Active Orders</div>
            <div class="value"><?= $stats['pending_orders'] ?></div>
            <div class="footer">
                <?= $stats['ready_count'] ?> ready for payment &nbsp;·&nbsp;
                <a href="<?= $baseUrl ?>/index.php?page=manage_orders" class="text-decoration-none" style="color:var(--primary);font-weight:600;">View all</a>
            </div>
        </div>
        <div class="stat-card success fade-in" style="animation-delay:0.15s">
            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="label">Low Stock</div>
            <div class="value"><?= $stats['low_stock'] ?></div>
            <div class="footer"><?= $stats['total_items'] ?> items in menu</div>
        </div>
    </div>

    <!-- CHARTS -->
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card fade-in">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-chart-line me-2" style="color:var(--secondary);"></i>Sales Overview (Last 7 Days)</h5>
                </div>
                <div class="card-body"><canvas id="salesChart"></canvas></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card fade-in">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-chart-pie me-2" style="color:var(--primary);"></i>Sales by Category</h5>
                </div>
                <div class="card-body"><canvas id="categoryChart"></canvas></div>
            </div>
        </div>
    </div>

    <!-- TABLES -->
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card fade-in">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-history me-2" style="color:var(--primary);"></i>Recent Orders</h5>
                    <a href="<?= $baseUrl ?>/index.php?page=manage_orders" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-container">
                        <table class="table mb-0">
                            <thead>
                                <tr><th>Order #</th><th>Customer</th><th>Amount</th><th>Status</th><th>Date</th></tr>
                            </thead>
                            <tbody class="orders-tbody">
                                <?php foreach ($recentOrders as $order): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($order['order_number']) ?></strong><br>
                                            <small class="text-muted">by <?= htmlspecialchars($order['cashier_name']) ?></small></td>
                                        <td><?= !empty($order['customer_name']) ? htmlspecialchars($order['customer_name']) : '<span class="text-muted">Walk-in</span>' ?></td>
                                        <td>₱<?= number_format($order['total_amount'], 2) ?></td>
                                        <td><span class="order-status status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></td>
                                        <td><?= date('M d, H:i', strtotime($order['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <!-- TOP SELLING ITEMS -->
            <div class="card fade-in">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-star me-2" style="color:var(--accent);"></i>Top Selling Items</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush top-items-list">
                        <?php foreach ($topItems as $index => $item): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1"><?= $index + 1 ?>. <?= htmlspecialchars($item['item_name']) ?></h6>
                                    <?php if (!empty($item['variant_name'])): ?>
                                        <small style="color:var(--success);font-weight:600;"><?= htmlspecialchars($item['variant_name']) ?> Variant</small>
                                        <small class="text-muted"> &mdash; ₱<?= number_format($item['variant_price'], 2) ?> each</small>
                                    <?php else: ?>
                                        <small class="text-muted">₱<?= number_format($item['variant_price'], 2) ?> each</small>
                                    <?php endif; ?>
                                </div>
                                <div class="text-end">
                                    <span class="badge" style="background:var(--primary);color:white;"><?= $item['total_sold'] ?> sold</span><br>
                                    <small style="color:var(--success);font-weight:600;">₱<?= number_format($item['total_revenue'], 2) ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- LOW STOCK -->
            <div class="card mt-4 fade-in">
                <div class="card-header" style="background:linear-gradient(145deg,white,#fafcff);">
                    <h5 class="card-title mb-0" style="color:var(--navy);">
                        <i class="fas fa-exclamation-triangle me-2" style="color:var(--secondary);"></i>
                        Low Stock Alert
                        <span class="badge bg-warning ms-2 low-stock-badge"><?= $stats['low_stock'] ?> items</span>
                    </h5>
                </div>
                <div class="card-body low-stock-list">
                    <?php if (empty($lowStock)): ?>
                        <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i> All items are sufficiently stocked.</div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($lowStock as $ingredient):
                                $isCritical = $ingredient['stock_quantity'] <= ($ingredient['low_stock_alert'] * 0.3);
                                $class = $isCritical ? 'critical-stock' : 'low-stock-item';
                            ?>
                                <div class="list-group-item <?= $class ?> p-3 mb-2" style="border-radius:16px;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($ingredient['ingredient_name']) ?></h6>
                                            <small class="text-muted">Current: <?= $ingredient['stock_quantity'] ?> <?= htmlspecialchars($ingredient['unit'] ?? '') ?></small>
                                        </div>
                                        <span class="badge <?= $isCritical ? 'bg-danger' : 'bg-warning' ?>">
                                            <i class="fas <?= $isCritical ? 'fa-exclamation-circle' : 'fa-exclamation-triangle' ?>"></i>
                                            <?= $isCritical ? 'Critical' : 'Low' ?>
                                        </span>
                                    </div>
                                    <div class="progress mb-2">
                                        <?php $percent = min(100, ($ingredient['stock_quantity'] / $ingredient['low_stock_alert']) * 100); ?>
                                        <div class="progress-bar <?= $isCritical ? 'bg-danger' : 'bg-warning' ?>" style="width:<?= $percent ?>%;"></div>
                                    </div>
                                    <small class="text-muted">Alert level: <?= $ingredient['low_stock_alert'] ?> <?= htmlspecialchars($ingredient['unit'] ?? '') ?></small>
                                </div>
                            <?php endforeach; ?>
                            <div class="mt-2">
                                <a href="<?= $baseUrl ?>/index.php?page=manage_ingredients" class="text-decoration-none" style="color:var(--primary);font-weight:600;">View all</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="main-footer">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div><strong>Copyright &copy; <?= date('Y') ?> DinewithDin - Cafeteria.</strong> All rights reserved.</div>
        <div class="text-muted"><b>Version</b> 1.0.0</div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {

    // ── Sidebar ───────────────────────────────────────────────────────
    const menuToggle     = document.getElementById('menuToggle');
    const sidebar        = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    menuToggle?.addEventListener('click', () => { sidebar.classList.toggle('show'); sidebarOverlay.classList.toggle('show'); });
    sidebarOverlay?.addEventListener('click', () => { sidebar.classList.remove('show'); sidebarOverlay.classList.remove('show'); });

    // ── Config ────────────────────────────────────────────────────────
    const BASE = '<?= $baseUrl ?>';
    const COUNTS_URL = BASE.replace('/public', '') + '/app/ajax/get_dashboard_counts.php';
    const POLL_MS    = 3000;

    // ── Initial chart data from PHP ───────────────────────────────────
    const initSalesLabels = [<?php
        $l = []; $d = [];
        foreach ($dailySales as $s) {
            $l[] = "'" . date('M d', strtotime($s['date'])) . "'";
            $d[] = (float)$s['total_sales'];
        }
        echo implode(',', $l);
    ?>];
    const initSalesData = [<?= implode(',', $d ?? []) ?>];

    const initCatLabels = [<?php
        $cl = []; $cd = [];
        foreach ($salesByCategory as $c) {
            $cl[] = "'" . addslashes(htmlspecialchars_decode($c['category_name'])) . "'";
            $cd[] = (float)$c['total_sales'];
        }
        echo implode(',', $cl);
    ?>];
    const initCatData   = [<?= implode(',', $cd ?? []) ?>];
    const PALETTE = ['#0f4c5c','#ff6b4a','#fee440','#1e2f4e','#5e9b9e','#ff8c70'];
    const initCatColors = [<?php
        $p = ['#0f4c5c','#ff6b4a','#fee440','#1e2f4e','#5e9b9e','#ff8c70'];
        $out = [];
        for ($i = 0; $i < count($salesByCategory); $i++) { $out[] = "'" . $p[$i % count($p)] . "'"; }
        echo implode(',', $out);
    ?>];

    // ── Charts ────────────────────────────────────────────────────────
    const salesChart = new Chart(document.getElementById('salesChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: initSalesLabels,
            datasets: [{
                label: 'Daily Sales (₱)', data: initSalesData,
                backgroundColor: 'rgba(15,76,92,0.06)', borderColor: '#0f4c5c',
                borderWidth: 2.5, tension: 0.3, fill: true,
                pointBackgroundColor: '#ff6b4a', pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff', pointHoverBorderColor: '#ff6b4a',
                pointRadius: 4, pointHoverRadius: 6,
            }],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: true, position: 'top', labels: { usePointStyle: true } },
                tooltip: { backgroundColor: '#1e2f4e', padding: 12, borderRadius: 12 },
            },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(15,76,92,0.04)' }, ticks: { callback: v => '₱' + v.toLocaleString() } },
                x: { grid: { display: false } },
            },
        },
    });

    const categoryChart = new Chart(document.getElementById('categoryChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: initCatLabels,
            datasets: [{ data: initCatData, backgroundColor: initCatColors, borderWidth: 0, hoverOffset: 8 }],
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '65%',
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 16, usePointStyle: true } },
                tooltip: {
                    backgroundColor: '#1e2f4e', padding: 12, borderRadius: 12,
                    callbacks: { label: ctx => `${ctx.label}: ₱${ctx.raw.toLocaleString()}` },
                },
            },
        },
    });

    // ── Helpers ───────────────────────────────────────────────────────
    function fmt(n) {
        return parseFloat(n || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    function esc(s) {
        const d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML;
    }
    function fmtDate(s) {
        try { return new Date(s).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }); }
        catch { return s; }
    }

    // ── DOM updaters ──────────────────────────────────────────────────
    function updateStatCards(data) {
        const set     = (sel, val) => { const el = document.querySelector(sel); if (el) el.textContent = val; };
        const setHtml = (sel, val) => { const el = document.querySelector(sel); if (el) el.innerHTML  = val; };
        set('.stat-card.primary .value',    '₱' + fmt(data.today_revenue));
        set('.stat-card.primary .footer',   'Monthly: ₱' + fmt(data.monthly_revenue));
        set('.stat-card.secondary .value',  data.today_orders);
        set('.stat-card.secondary .footer', data.today_customers + ' customers today');
        set('.stat-card.info .value',       data.total_active);
        setHtml('.stat-card.info .footer',
            `${data.ready_count} ready for payment &nbsp;·&nbsp;
             <a href="${BASE}/index.php?page=manage_orders" class="text-decoration-none" style="color:var(--primary);font-weight:600;">View all</a>`);
        set('.stat-card.success .value', data.low_stock);
    }

    function updateNotifications(data) {
        const total = data.total_active + data.low_stock;
        let badge   = document.querySelector('.notification-badge');
        if (total > 0) {
            if (!badge) {
                badge = document.createElement('span');
                badge.className = 'notification-badge';
                document.getElementById('notificationDropdown').appendChild(badge);
            }
            badge.textContent = total;
        } else {
            if (badge) badge.remove();
        }

        const dd = document.querySelector('#notificationDropdown + .dropdown-menu');
        if (!dd) return;
        dd.innerHTML = '<li><h6 class="dropdown-header">Notifications</h6></li>';

        [
            { count: data.pending_count,   icon: 'fa-hourglass-half',      color: 'var(--warning)',       s: 'order waiting',           p: 'orders waiting',           href: `${BASE}/index.php?page=manage_orders` },
            { count: data.preparing_count, icon: 'fa-bowl-food',            color: 'var(--primary-light)', s: 'order being prepared',    p: 'orders being prepared',    href: `${BASE}/index.php?page=manage_orders` },
            { count: data.ready_count,     icon: 'fa-circle-check',         color: 'var(--success)',       s: 'order ready for payment', p: 'orders ready for payment', href: `${BASE}/index.php?page=manage_orders` },
            { count: data.low_stock,       icon: 'fa-exclamation-triangle', color: 'var(--secondary)',     s: 'low stock item',          p: 'low stock items',          href: `${BASE}/index.php?page=manage_ingredients` },
        ].forEach(item => {
            if (item.count <= 0) return;
            dd.innerHTML += `<li><a class="dropdown-item" href="${item.href}">
                <i class="fas ${item.icon} me-2" style="color:${item.color};"></i>
                ${item.count} ${item.count > 1 ? item.p : item.s}
            </a></li>`;
        });

        if (data.total_active === 0 && data.low_stock === 0) {
            dd.innerHTML += `<li><span class="dropdown-item text-muted">
                <i class="fas fa-check-circle me-2" style="color:var(--success);"></i>All caught up!
            </span></li>`;
        }
    }

    function updateRecentOrders(orders) {
        const tbody = document.querySelector('.orders-tbody');
        if (!tbody || !orders) return;
        tbody.innerHTML = orders.map(o => `
            <tr>
                <td><strong>${esc(o.order_number)}</strong><br>
                    <small class="text-muted">by ${esc(o.cashier_name)}</small></td>
                <td>${o.customer_name ? esc(o.customer_name) : '<span class="text-muted">Walk-in</span>'}</td>
                <td>₱${fmt(o.total_amount)}</td>
                <td><span class="order-status status-${esc(o.status)}">${esc(o.status.charAt(0).toUpperCase() + o.status.slice(1))}</span></td>
                <td>${fmtDate(o.created_at)}</td>
            </tr>`).join('');
    }

    function updateTopItems(items) {
        const list = document.querySelector('.top-items-list');
        if (!list || !items) return;
        list.innerHTML = items.map((item, i) => {
            const priceStr = '₱' + fmt(item.price) + ' each';
            const subHtml  = item.variant_name
                ? `<small style="color:var(--success);font-weight:600;">${esc(item.variant_name)} Variant</small>
                   <small class="text-muted"> &mdash; ${priceStr}</small>`
                : `<small class="text-muted">${priceStr}</small>`;

            return `<div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1">${i + 1}. ${esc(item.item_name)}</h6>
                    ${subHtml}
                </div>
                <div class="text-end">
                    <span class="badge" style="background:var(--primary);color:white;">${item.total_sold} sold</span><br>
                    <small style="color:var(--success);font-weight:600;">₱${fmt(item.total_revenue)}</small>
                </div>
            </div>`;
        }).join('');
    }

    function updateLowStockCard(items, count) {
        const badge = document.querySelector('.low-stock-badge');
        if (badge) badge.textContent = count + ' items';

        const container = document.querySelector('.low-stock-list');
        if (!container) return;

        if (!items || items.length === 0) {
            container.innerHTML = `<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i> All items are sufficiently stocked.</div>`;
            return;
        }

        container.innerHTML = `<div class="list-group list-group-flush">` +
            items.map(ing => {
                const crit = ing.stock_quantity <= (ing.low_stock_alert * 0.3);
                const pct  = Math.min(100, (ing.stock_quantity / ing.low_stock_alert) * 100);
                return `<div class="list-group-item ${crit ? 'critical-stock' : 'low-stock-item'} p-3 mb-2" style="border-radius:16px;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <h6 class="mb-1">${esc(ing.ingredient_name)}</h6>
                            <small class="text-muted">Current: ${ing.stock_quantity} ${esc(ing.unit)}</small>
                        </div>
                        <span class="badge ${crit ? 'bg-danger' : 'bg-warning'}">
                            <i class="fas ${crit ? 'fa-exclamation-circle' : 'fa-exclamation-triangle'}"></i>
                            ${crit ? 'Critical' : 'Low'}
                        </span>
                    </div>
                    <div class="progress mb-2">
                        <div class="progress-bar ${crit ? 'bg-danger' : 'bg-warning'}" style="width:${pct}%;"></div>
                    </div>
                    <small class="text-muted">Alert level: ${ing.low_stock_alert} ${esc(ing.unit)}</small>
                </div>`;
            }).join('') +
            `<div class="mt-2">
                <a href="${BASE}/index.php?page=manage_ingredients" class="text-decoration-none" style="color:var(--primary);font-weight:600;">View all</a>
            </div></div>`;
    }

    function updateCharts(daily, byCategory) {
        if (daily) {
            salesChart.data.labels = daily.map(s => {
                const d = new Date(s.date + 'T00:00:00');
                return d.toLocaleString('en-US', { month: 'short', day: 'numeric' });
            });
            salesChart.data.datasets[0].data = daily.map(s => parseFloat(s.total_sales) || 0);
            salesChart.update('none');
        }
        if (byCategory) {
            categoryChart.data.labels = byCategory.map(c => c.category_name);
            categoryChart.data.datasets[0].data            = byCategory.map(c => parseFloat(c.total_sales) || 0);
            categoryChart.data.datasets[0].backgroundColor = byCategory.map((_, i) => PALETTE[i % PALETTE.length]);
            categoryChart.update('none');
        }
    }

    // ── Polling ───────────────────────────────────────────────────────
    async function pollDashboard() {
        try {
            const res = await fetch(COUNTS_URL, { cache: 'no-store' });
            if (!res.ok) return;
            const data = await res.json();
            if (!data.success) { console.error('Dashboard error:', data.error); return; }
            updateStatCards(data);
            updateNotifications(data);
            updateRecentOrders(data.recent_orders);
            updateTopItems(data.top_items);
            updateLowStockCard(data.low_stock_items, data.low_stock);
            updateCharts(data.daily_sales, data.sales_by_category);
        } catch (err) {
            console.error('Dashboard poll failed:', err);
        }
    }

    pollDashboard();
    setInterval(pollDashboard, POLL_MS);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) pollDashboard(); });

})();
</script>
</body>
</html>