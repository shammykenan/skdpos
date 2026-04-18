<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5">
    <title>BIR Settings — Dine with Din</title>
    <link rel="icon" type="image/png" href="<?= $baseUrl ?>/assets/images/logo/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fraunces:opsz,wght@9..144,700;9..144,900&display=swap" rel="stylesheet">

    <?php
        require_once BASE_PATH . '/app/models/BusinessInfo.php';
        $info = getBusinessInfo($pdo);
    ?>

    <style>
        /* ── TOKENS ── */
        :root {
            --primary:       #0f4c5c;
            --primary-dark:  #0a3a47;
            --primary-light: #1f7a8c;
            --secondary:     #ff6b4a;
            --navy:          #1e2f4e;
            --lavender:      #e2e6f0;
            --light-bg:      #f8faff;
            --warm-gray:     #4a5568;
            --border:        #cbd5e0;
            --success:       #2c7a5a;
            --danger:        #d64045;
            --warning:       #f4b942;
            --info:          #5e9b9e;
            --dark:          #1a202c;
            --success-soft:  #eaf7ef;
            --danger-soft:   #fdecea;
            --warning-soft:  #fef8e7;
            --info-soft:     rgba(94,155,158,0.1);
            --sidebar-w:     260px;
            --top-h:         64px;
            --radius-sm:     8px;
            --radius-md:     12px;
            --radius-lg:     18px;
            --radius-xl:     24px;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
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
            position: fixed; top: 0; left: 0; right: 0; height: var(--top-h);
            background: #fff; border-bottom: 1px solid var(--border);
            z-index: 1040; display: flex; align-items: center;
            padding: 0 1.5rem; gap: 1rem;
            box-shadow: 0 2px 8px rgba(15,76,92,0.06);
        }
        .topbar-brand { display: flex; align-items: center; gap: 0.65rem; text-decoration: none; flex-shrink: 0; }
        .brand-logo {
            width: 40px; height: 40px; border-radius: var(--radius-sm);
            background: #fff; border: 1px solid var(--border);
            display: flex; align-items: center; justify-content: center;
            overflow: hidden; flex-shrink: 0;
        }
        .brand-logo img { width: 100%; height: 100%; object-fit: contain; display: block; }
        .brand-text-main { font-family: 'Fraunces', Georgia, serif; font-weight: 700; font-size: 1.15rem; color: var(--primary-dark); line-height: 1; white-space: nowrap; }
        .brand-text-sub  { font-size: 0.65rem; color: var(--secondary); font-weight: 600; letter-spacing: 1px; text-transform: uppercase; margin-top: 2px; white-space: nowrap; }
        .topbar-spacer { flex: 1; }

        .mobile-toggle {
            display: none; background: none; border: 1px solid var(--border);
            border-radius: var(--radius-sm); padding: 0.4rem 0.65rem;
            color: var(--primary); cursor: pointer; font-size: 1rem;
            transition: background 0.15s;
        }
        .mobile-toggle:hover { background: var(--lavender); }

        .user-pill {
            display: flex; align-items: center; gap: 0.5rem;
            background: var(--light-bg); border: 1px solid var(--border);
            border-radius: 40px; padding: 0.3rem 0.9rem 0.3rem 0.35rem;
            cursor: pointer; transition: all 0.15s; text-decoration: none; max-width: 220px;
        }
        .user-pill:hover { background: var(--lavender); border-color: var(--primary-light); }
        .user-avatar {
            width: 30px; height: 30px; border-radius: 50%;
            background: var(--primary); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.72rem; font-weight: 700; flex-shrink: 0;
        }
        .user-name-text { font-size: 0.82rem; font-weight: 600; color: var(--navy); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-caret { color: var(--warm-gray); font-size: 0.65rem; flex-shrink: 0; }

        /* ── SIDEBAR ── */
        .sidebar {
            position: fixed; top: var(--top-h); left: 0; width: var(--sidebar-w);
            height: calc(100vh - var(--top-h)); background: #fff;
            border-right: 1px solid var(--border); overflow-y: auto;
            z-index: 1030; transition: transform 0.25s ease;
        }
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }
        .nav-wrap { padding: 1.2rem 0.75rem 2rem; }
        .nav-section {
            font-size: 0.65rem; font-weight: 700; letter-spacing: 1.2px;
            text-transform: uppercase; color: var(--warm-gray);
            padding: 1.2rem 0.75rem 0.4rem; opacity: 0.7;
        }
        .nav-link {
            display: flex; align-items: center; gap: 0.7rem;
            padding: 0.6rem 0.9rem; border-radius: var(--radius-md);
            color: var(--warm-gray); font-weight: 500; font-size: 0.88rem;
            text-decoration: none; transition: all 0.15s; margin: 0.1rem 0;
        }
        .nav-link i { width: 20px; text-align: center; font-size: 0.9rem; flex-shrink: 0; transition: color 0.15s; }
        .nav-link:hover { background: var(--lavender); color: var(--primary-dark); }
        .nav-link:hover i { color: var(--primary); }
        .nav-link.active { background: var(--primary); color: #fff; font-weight: 600; box-shadow: 0 4px 12px rgba(15,76,92,0.2); }
        .nav-link.active i { color: rgba(255,255,255,0.85); }

        .sidebar-overlay {
            display: none; position: fixed; top: var(--top-h); left: 0; right: 0; bottom: 0;
            background: rgba(15,76,92,0.25); z-index: 1025; backdrop-filter: blur(2px);
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
            display: flex; align-items: flex-start; justify-content: space-between;
            gap: 1rem; margin-bottom: 1.75rem; flex-wrap: wrap;
        }
        .page-title { font-family: 'Fraunces', Georgia, serif; font-size: 1.75rem; font-weight: 700; color: var(--navy); line-height: 1.1; }
        .breadcrumb-bar { display: flex; align-items: center; gap: 0.4rem; font-size: 0.8rem; color: var(--warm-gray); margin-top: 0.3rem; }
        .breadcrumb-bar a { color: var(--primary-light); text-decoration: none; font-weight: 500; }
        .breadcrumb-bar a:hover { text-decoration: underline; }
        .breadcrumb-sep { color: var(--border); }

        /* ── ALERT BANNER ── */
        .alert-bar {
            border-radius: var(--radius-md); padding: 0.9rem 1.2rem;
            display: flex; align-items: flex-start; gap: 0.75rem;
            font-size: 0.875rem; font-weight: 500; margin-bottom: 1.75rem;
        }
        .alert-bar i { font-size: 1rem; flex-shrink: 0; margin-top: 1px; }
        .alert-bar.success { background: var(--success-soft); color: #1e4a3b; border-left: 4px solid var(--success); }
        .alert-bar.warning { background: var(--warning-soft); color: #7a5500; border-left: 4px solid var(--warning); }
        .alert-bar strong { font-weight: 700; }

        /* ── SECTION CARD ── */
        .section-card {
            background: #fff;
            border: 1px solid rgba(203,213,224,0.6);
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(15,76,92,0.03);
            margin-bottom: 1.5rem;
        }
        .section-card-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: 0.75rem;
        }
        .section-card-icon {
            width: 36px; height: 36px;
            border-radius: var(--radius-md);
            display: flex; align-items: center; justify-content: center;
            font-size: 0.9rem; flex-shrink: 0;
        }
        .icon-primary  { background: rgba(15,76,92,0.1);   color: var(--primary); }
        .icon-success  { background: var(--success-soft);  color: var(--success); }
        .icon-warning  { background: var(--warning-soft);  color: #b87a00; }
        .icon-info     { background: var(--info-soft);     color: var(--info); }
        .section-card-title { font-weight: 700; font-size: 0.95rem; color: var(--navy); }
        .section-card-body  { padding: 1.5rem; }

        /* ── FORM ELEMENTS ── */
        .form-label { font-size: 0.82rem; font-weight: 700; color: var(--navy); margin-bottom: 0.4rem; display: block; }
        .form-text  { font-size: 0.75rem; color: var(--warm-gray); margin-top: 0.25rem; }
        .form-control, .form-select {
            border: 1px solid var(--border); border-radius: var(--radius-md);
            padding: 0.6rem 0.9rem; font-size: 0.88rem; font-family: inherit;
            background: #fff; color: var(--dark);
            transition: border-color 0.15s, box-shadow 0.15s; width: 100%;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary); outline: none;
            box-shadow: 0 0 0 3px rgba(15,76,92,0.12);
        }
        .form-control::placeholder { color: #a0aec0; }
        textarea.form-control { resize: vertical; min-height: 80px; }

        .input-group { display: flex; }
        .input-group .form-control { border-radius: 0 var(--radius-md) var(--radius-md) 0; flex: 1; }
        .input-group-text {
            background: #f2f6fc; border: 1px solid var(--border);
            border-right: none;
            border-radius: var(--radius-md) 0 0 var(--radius-md);
            padding: 0.6rem 0.85rem; color: var(--primary-dark);
            font-weight: 600; font-size: 0.85rem; display: flex; align-items: center;
            white-space: nowrap;
        }
        .input-group .input-group-text:last-child {
            border-radius: 0 var(--radius-md) var(--radius-md) 0;
            border-right: 1px solid var(--border);
            border-left: none;
        }
        .input-group .form-control:not(:first-child):not(:last-child) { border-radius: 0; }

        /* toggle switch */
        .toggle-row {
            display: flex; align-items: flex-start; gap: 0.75rem;
            padding: 1rem 1.1rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            background: var(--light-bg);
        }
        .form-check-input[type="checkbox"] {
            width: 38px; height: 22px; cursor: pointer;
            accent-color: var(--primary);
            border-radius: 11px; flex-shrink: 0; margin-top: 2px;
        }
        .form-check-input[type="checkbox"]:focus { box-shadow: 0 0 0 3px rgba(15,76,92,0.15); outline: none; }
        .toggle-label { font-size: 0.9rem; font-weight: 700; color: var(--navy); }
        .toggle-sub   { font-size: 0.78rem; color: var(--warm-gray); margin-top: 2px; }

        /* ── SAVE BUTTON ── */
        .btn-save {
            display: inline-flex; align-items: center; gap: 0.5rem;
            background: var(--primary); color: #fff;
            padding: 0.7rem 2rem; border-radius: 40px;
            font-weight: 700; font-size: 0.9rem; border: none; cursor: pointer;
            transition: all 0.2s; font-family: inherit;
        }
        .btn-save:hover { background: var(--primary-dark); transform: translateY(-1px); box-shadow: 0 6px 16px rgba(15,76,92,0.25); }
        .btn-save:disabled { opacity: 0.55; cursor: not-allowed; transform: none; box-shadow: none; }

        /* ── DROPDOWN ── */
        .dropdown-menu { border-radius: var(--radius-lg); border: 1px solid var(--border); box-shadow: 0 12px 30px rgba(0,0,0,0.08); padding: 0.5rem; }
        .dropdown-item { border-radius: var(--radius-sm); font-size: 0.88rem; padding: 0.55rem 1rem; }
        .dropdown-item:hover { background: var(--lavender); color: var(--primary-dark); }
        .dropdown-item.text-danger:hover { background: rgba(214,64,69,0.08); color: var(--danger); }

        /* ── TOAST ── */
        #toastContainer {
            position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999;
            display: flex; flex-direction: column; gap: 0.5rem;
            pointer-events: none;
        }
        .toast-msg {
            display: flex; align-items: center; gap: 0.55rem;
            padding: 0.75rem 1.2rem; border-radius: var(--radius-md);
            font-size: 0.875rem; font-weight: 600; color: #fff;
            box-shadow: 0 8px 24px rgba(0,0,0,0.18);
            opacity: 0; transform: translateY(12px);
            transition: opacity 0.22s, transform 0.22s;
            pointer-events: auto;
        }
        .toast-msg.show   { opacity: 1; transform: translateY(0); }
        .toast-msg.success { background: #1a3a2a; }
        .toast-msg.danger  { background: #3a1a1a; }
        .toast-msg i.ok  { color: #4ade80; }
        .toast-msg i.err { color: #f87171; }

        /* ── RESPONSIVE ── */
        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main { margin-left: 0; padding: 1.5rem 1.25rem; }
            .mobile-toggle { display: flex; align-items: center; }
        }
        @media (min-width: 768px) and (max-width: 1024px) {
            :root { --sidebar-w: 220px; }
            .main { padding: 1.5rem; }
        }
        @media (max-width: 767.98px) {
            .main { padding: 1.25rem 1rem; }
            .page-title { font-size: 1.4rem; }
            .modal-dialog { margin: 0.5rem; }
        }
        @media (max-width: 575.98px) {
            .brand-text-sub { display: none; }
            .topbar { padding: 0 1rem; }
        }

        /* ── ANIMATIONS ── */
        .fade-up { animation: fadeUp 0.35s ease forwards; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        .fade-up-1 { animation-delay: 0.05s; opacity: 0; }
        .fade-up-2 { animation-delay: 0.10s; opacity: 0; }
        .fade-up-3 { animation-delay: 0.15s; opacity: 0; }
        .fade-up-4 { animation-delay: 0.20s; opacity: 0; }
        .fade-up-5 { animation-delay: 0.25s; opacity: 0; }
    </style>
</head>
<body>

<!-- ── TOPBAR ── -->
<header class="topbar">
    <button class="mobile-toggle" id="menuToggle" aria-label="Toggle menu">
        <i class="fas fa-bars"></i>
    </button>
    <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="topbar-brand">
        <div class="brand-logo">
            <img src="<?= $baseUrl ?>/assets/images/logo/logo.png" alt="Dine with Din Logo">
        </div>
        <div>
            <div class="brand-text-main">SKD Software Solutions</div>
            <div class="brand-text-sub">POS with Recipe-Based Inventory</div>
        </div>
    </a>
    <div class="topbar-spacer"></div>
    <div class="dropdown">
        <?php
            $fullName = $_SESSION['full_name'] ?? 'Admin';
            $parts    = explode(' ', trim($fullName));
            $initials = strtoupper(substr($parts[0], 0, 1));
            if (count($parts) > 1) $initials .= strtoupper(substr(end($parts), 0, 1));
        ?>
        <button class="user-pill dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border:none;">
            <span class="user-avatar"><?= htmlspecialchars($initials) ?></span>
            <span class="user-name-text"><?= htmlspecialchars($fullName) ?></span>
            <i class="fas fa-chevron-down user-caret"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li>
                <div class="px-3 py-2" style="border-bottom:1px solid var(--border);margin-bottom:0.4rem;">
                    <div style="font-weight:700;font-size:0.9rem;color:var(--navy);"><?= htmlspecialchars($fullName) ?></div>
                    <div style="font-size:0.78rem;color:var(--warm-gray);">Administrator</div>
                </div>
            </li>
            <li><a class="dropdown-item text-danger" href="<?= $baseUrl ?>/index.php?page=logout">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
            </a></li>
        </ul>
    </div>
</header>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ── SIDEBAR ── -->
<nav class="sidebar" id="sidebar" aria-label="Main navigation">
    <div class="nav-wrap">
        <div class="nav-section">Dashboard</div>
        <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="nav-link"><i class="fas fa-tachometer-alt"></i>Dashboard</a>

        <div class="nav-section">Management</div>
        <a href="<?= $baseUrl ?>/index.php?page=manage_menu"        class="nav-link"><i class="fas fa-utensils"></i>Menu Items</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_recipes"     class="nav-link"><i class="fas fa-tags"></i>Recipes</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_ingredients" class="nav-link"><i class="fas fa-boxes"></i>Ingredients</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_addons"      class="nav-link"><i class="fas fa-plus-circle"></i>Add-ons</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_tables"      class="nav-link"><i class="fas fa-chair"></i>Tables</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_users"       class="nav-link"><i class="fas fa-users"></i>User Management</a>

        <div class="nav-section">Sales</div>
        <a href="<?= $baseUrl ?>/index.php?page=manage_pos"         class="nav-link"><i class="fas fa-cash-register"></i>POS System</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_orders"      class="nav-link"><i class="fas fa-receipt"></i>Orders</a>
        <a href="<?= $baseUrl ?>/index.php?page=reports"            class="nav-link"><i class="fas fa-chart-bar"></i>Reports</a>

        <div class="nav-section">Configuration</div>
        <a href="<?= $baseUrl ?>/index.php?page=bir_settings" class="nav-link active"><i class="fas fa-building"></i>BIR Settings</a>
    </div>
</nav>

<!-- ── MAIN ── -->
<main class="main" id="mainContent">

    <!-- Page head -->
    <div class="page-head">
        <div>
            <h1 class="page-title">BIR &amp; Business Settings</h1>
            <div class="breadcrumb-bar">
                <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard">Home</a>
                <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:0.6rem;"></i></span>
                <span>BIR Settings</span>
            </div>
        </div>
    </div>

    <!-- VAT Status Banner -->
    <?php if ($info['vat_registered']): ?>
    <div class="alert-bar success fade-up">
        <i class="fas fa-check-circle"></i>
        <div>
            <strong>VAT-Registered Business</strong> —
            VAT (<?= number_format((float)$info['vat_rate'], 2) ?>%) will be computed and itemized on all official receipts.
        </div>
    </div>
    <?php else: ?>
    <div class="alert-bar warning fade-up">
        <i class="fas fa-exclamation-triangle"></i>
        <div>
            <strong>Non-VAT / Exempt Business</strong> —
            Receipts will include the BIR non-VAT disclaimer as required by RR 10-2021.
        </div>
    </div>
    <?php endif; ?>

    <form id="birSettingsForm">

        <!-- 1. Business Information -->
        <div class="section-card fade-up fade-up-1">
            <div class="section-card-header">
                <div class="section-card-icon icon-primary">
                    <i class="fas fa-store"></i>
                </div>
                <div>
                    <div class="section-card-title">Business Information</div>
                </div>
            </div>
            <div class="section-card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Business Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="business_name"
                               value="<?= htmlspecialchars($info['business_name']) ?>"
                               placeholder="e.g. Dine with Din Cafeteria" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tax Identification Number (TIN) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="tin"
                               value="<?= htmlspecialchars($info['tin']) ?>"
                               placeholder="XXX-XXX-XXX-XXXX" required>
                        <div class="form-text">Your BIR-issued Tax Identification Number.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Business Address <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="address" rows="2"
                                  placeholder="Complete registered business address" required><?= htmlspecialchars($info['address']) ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. POS Machine Registration -->
        <div class="section-card fade-up fade-up-2">
            <div class="section-card-header">
                <div class="section-card-icon icon-info">
                    <i class="fas fa-desktop"></i>
                </div>
                <div>
                    <div class="section-card-title">POS Machine Registration</div>
                </div>
            </div>
            <div class="section-card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Machine Serial No.</label>
                        <input type="text" class="form-control" name="machine_serial_no"
                               value="<?= htmlspecialchars($info['machine_serial_no'] ?? '') ?>"
                               placeholder="From BIR machine registration">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">PTU Number</label>
                        <input type="text" class="form-control" name="ptu_number"
                               value="<?= htmlspecialchars($info['ptu_number'] ?? '') ?>"
                               placeholder="Permit To Use number">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">BIR Accreditation No.</label>
                        <input type="text" class="form-control" name="accreditation_no"
                               value="<?= htmlspecialchars($info['accreditation_no'] ?? '') ?>"
                               placeholder="POS accreditation number">
                    </div>
                </div>
                <div class="form-text mt-2">
                    <i class="fas fa-circle-info me-1" style="color:var(--info);"></i>
                    These fields appear on every official receipt. Leave blank if not yet registered with BIR.
                </div>
            </div>
        </div>

        <!-- 3. VAT Configuration -->
        <div class="section-card fade-up fade-up-3">
            <div class="section-card-header">
                <div class="section-card-icon icon-success">
                    <i class="fas fa-percent"></i>
                </div>
                <div>
                    <div class="section-card-title">VAT Configuration</div>
                </div>
            </div>
            <div class="section-card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="toggle-row">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   id="vatRegisteredToggle" name="vat_registered" value="1"
                                   <?= $info['vat_registered'] ? 'checked' : '' ?>>
                            <div>
                                <div class="toggle-label">Business is VAT-Registered</div>
                                <div class="toggle-sub">When enabled, 12% VAT is computed and itemized on all receipts. When disabled, receipts display the BIR non-VAT disclaimer.</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4" id="vatRateWrapper" style="<?= $info['vat_registered'] ? '' : 'display:none;' ?>">
                        <label class="form-label">VAT Rate</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="vat_rate"
                                   value="<?= number_format((float)$info['vat_rate'], 2) ?>"
                                   min="0" max="100" step="0.01">
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-text">Standard BIR rate is 12.00%. Only change if BIR updates the rate.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Discount Configuration -->
        <div class="section-card fade-up fade-up-4">
            <div class="section-card-header">
                <div class="section-card-icon icon-warning">
                    <i class="fas fa-tag"></i>
                </div>
                <div>
                    <div class="section-card-title">Discount Configuration</div>
                </div>
            </div>
            <div class="section-card-body">
                <p style="font-size:0.84rem;color:var(--warm-gray);margin-bottom:1.25rem;line-height:1.6;">
                    These rates are applied when cashiers select Senior Citizen or PWD discounts at checkout.
                    Per BIR rules, discounts are computed on the VAT-exclusive amount.
                </p>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Senior Citizen Discount</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="min_senior_discount"
                                   value="<?= number_format((float)$info['min_senior_discount'], 2) ?>"
                                   min="0" max="100" step="0.01">
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-text">Standard rate: 20%</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">PWD Discount</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="min_pwd_discount"
                                   value="<?= number_format((float)$info['min_pwd_discount'], 2) ?>"
                                   min="0" max="100" step="0.01">
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-text">Standard rate: 20%</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Save -->
        <div class="fade-up fade-up-5" style="display:flex;justify-content:flex-end;margin-bottom:2.5rem;">
            <button type="submit" class="btn-save" id="saveBtn">
                <i class="fas fa-floppy-disk"></i>Save BIR Settings
            </button>
        </div>

    </form>

</main>

<!-- ── TOAST ── -->
<div id="toastContainer"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* ── Sidebar ── */
document.getElementById('menuToggle')?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('show');
    document.getElementById('sidebarOverlay').classList.toggle('show');
});
document.getElementById('sidebarOverlay')?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.remove('show');
    document.getElementById('sidebarOverlay').classList.remove('show');
});

/* ── VAT rate field toggle ── */
document.getElementById('vatRegisteredToggle').addEventListener('change', function () {
    const wrapper = document.getElementById('vatRateWrapper');
    wrapper.style.display = this.checked ? '' : 'none';
});

/* ── Toast helper ── */
function showToast(message, type) {
    const container = document.getElementById('toastContainer');
    const el = document.createElement('div');
    el.className = `toast-msg ${type}`;
    el.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle ok' : 'fa-exclamation-circle err'}"></i><span>${message}</span>`;
    container.appendChild(el);
    requestAnimationFrame(() => requestAnimationFrame(() => el.classList.add('show')));
    setTimeout(() => {
        el.classList.remove('show');
        setTimeout(() => el.remove(), 300);
    }, 3500);
}

/* ── Form submit ── */
document.getElementById('birSettingsForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const btn  = document.getElementById('saveBtn');
    const orig = btn.innerHTML;
    btn.disabled  = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>Saving…';

    const fd = new FormData(this);
    if (!document.getElementById('vatRegisteredToggle').checked) {
        fd.set('vat_registered', '0');
    }

    fetch('index.php?page=ajax_save_bir_settings', {
        method: 'POST',
        body:   fd,
    })
    .then(r => r.json())
    .then(data => {
        showToast(data.message, data.success ? 'success' : 'danger');
        if (data.success) setTimeout(() => location.reload(), 1200);
    })
    .catch(() => showToast('Network error. Please try again.', 'danger'))
    .finally(() => {
        btn.disabled  = false;
        btn.innerHTML = orig;
    });
});
</script>
</body>
</html>