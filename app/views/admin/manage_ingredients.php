<?php
// Pure view: expects $ingredients, $units (dropdown), $units_raw (full rows),
// $_SESSION['success'], $_SESSION['error']
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php require_once __DIR__ . "/../../core/csrfHelper.php"; echo csrf_head($baseUrl ?? ""); ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingredient Management — The Lounge by: CJV & TEK</title>
    <link rel="icon" type="image/png" href="<?= $baseUrl ?>/assets/images/logo/logo.png">
    <link href="<?= $baseUrl ?>/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/common.css">
    <link href="<?= $baseUrl ?>/assets/vendor/fonts/app.css" rel="stylesheet">
    <style>
        /* ── TOKENS ──────────────────────────────────────────────────────── */

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--light-bg); color: var(--dark); font-size: 14px; line-height: 1.6; -webkit-font-smoothing: antialiased; }

        /* ── TOPBAR ─────────────────────────────────────────────────────── */
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
            color: var(--primary); cursor: pointer; font-size: 1rem; transition: background 0.15s;
        }
        .mobile-toggle:hover { background: var(--lavender); }
        .user-pill {
            display: flex; align-items: center; gap: 0.5rem;
            background: var(--light-bg); border: 1px solid var(--border);
            border-radius: 40px; padding: 0.3rem 0.9rem 0.3rem 0.35rem;
            cursor: pointer; transition: all 0.15s; text-decoration: none; max-width: 220px;
        }
        .user-pill:hover { background: var(--lavender); border-color: var(--primary-light); }
        .user-avatar { width: 30px; height: 30px; border-radius: 50%; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.72rem; font-weight: 700; flex-shrink: 0; }
        .user-name-text { font-size: 0.82rem; font-weight: 600; color: var(--navy); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-caret { color: var(--warm-gray); font-size: 0.65rem; flex-shrink: 0; }

        /* ── SIDEBAR ────────────────────────────────────────────────────── */
        .sidebar {
            position: fixed; top: var(--top-h); left: 0; width: var(--sidebar-w);
            height: calc(100vh - var(--top-h)); background: #fff;
            border-right: 1px solid var(--border); overflow-y: auto;
            z-index: 1030; transition: transform 0.25s ease;
        }
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }
        .nav-wrap { padding: 1.2rem 0.75rem 2rem; }
        .nav-section { font-size: 0.65rem; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: var(--warm-gray); padding: 1.2rem 0.75rem 0.4rem; opacity: 0.7; }
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

        /* ── OVERLAY ─────────────────────────────────────────────────── */
        .sidebar-overlay { display: none; position: fixed; top: var(--top-h); left: 0; right: 0; bottom: 0; background: rgba(15,76,92,0.25); z-index: 1025; backdrop-filter: blur(2px); }
        .sidebar-overlay.show { display: block; }

        /* ── MAIN ────────────────────────────────────────────────────── */
        .main { margin-left: var(--sidebar-w); margin-top: var(--top-h); padding: 2rem 1.75rem; min-height: calc(100vh - var(--top-h)); }

        /* ── PAGE HEADER ─────────────────────────────────────────────── */
        .page-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; margin-bottom: 1.75rem; flex-wrap: wrap; }
        .page-title { font-family: 'Fraunces', Georgia, serif; font-size: 1.75rem; font-weight: 700; color: var(--navy); line-height: 1.1; }
        .breadcrumb-bar { display: flex; align-items: center; gap: 0.4rem; font-size: 0.8rem; color: var(--warm-gray); margin-top: 0.3rem; }
        .breadcrumb-bar a { color: var(--primary-light); text-decoration: none; font-weight: 500; }
        .breadcrumb-bar a:hover { text-decoration: underline; }
        .breadcrumb-sep { color: var(--border); }
        .head-actions { display: flex; gap: 0.6rem; flex-wrap: wrap; align-items: center; }

        /* ── BUTTONS ─────────────────────────────────────────────────── */
        .btn-primary-action {
            display: inline-flex; align-items: center; gap: 0.5rem;
            background: var(--primary); color: #fff;
            padding: 0.55rem 1.3rem; border-radius: 40px;
            font-weight: 600; font-size: 0.88rem; border: none; cursor: pointer;
            transition: all 0.2s; text-decoration: none; white-space: nowrap;
        }
        .btn-primary-action:hover { background: var(--primary-dark); color: #fff; transform: translateY(-1px); box-shadow: 0 6px 16px rgba(15,76,92,0.2); }
        .btn-secondary-action {
            display: inline-flex; align-items: center; gap: 0.5rem;
            background: #fff; color: var(--primary);
            padding: 0.5rem 1.25rem; border-radius: 40px;
            font-weight: 600; font-size: 0.88rem; border: 2px solid var(--primary);
            cursor: pointer; transition: all 0.2s; text-decoration: none; white-space: nowrap;
        }
        .btn-secondary-action:hover { background: var(--primary); color: #fff; transform: translateY(-1px); }

        /* ── ALERTS ──────────────────────────────────────────────────── */
        .alert-bar { border-radius: var(--radius-md); padding: 0.85rem 1.2rem; display: flex; align-items: center; gap: 0.65rem; font-size: 0.88rem; font-weight: 500; margin-bottom: 1.5rem; transition: opacity 0.4s; }
        .alert-bar.success { background: var(--success-soft); color: #1e4a3b; border-left: 4px solid var(--success); }
        .alert-bar.danger  { background: var(--danger-soft);  color: #9e3a34; border-left: 4px solid var(--danger); }
        .alert-bar .dismiss { margin-left: auto; cursor: pointer; background: none; border: none; color: inherit; opacity: 0.5; font-size: 1.1rem; padding: 0; }
        .alert-bar .dismiss:hover { opacity: 1; }

        /* ── FILTER CARD ─────────────────────────────────────────────── */
        .filter-card { background: #fff; border: 1px solid rgba(203,213,224,0.6); border-radius: var(--radius-xl); padding: 1.35rem 1.5rem; margin-bottom: 1.25rem; box-shadow: 0 4px 16px rgba(15,76,92,0.03); }
        .filter-section-label { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: var(--warm-gray); margin-bottom: 0.65rem; display: flex; align-items: center; gap: 0.4rem; }
        .filter-tabs { display: flex; gap: 0.5rem; flex-wrap: wrap; }
        .filter-tab {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.4rem 0.9rem; border: 1px solid var(--border);
            background: #fff; color: var(--warm-gray); border-radius: 30px;
            cursor: pointer; transition: all 0.15s; font-weight: 600; font-size: 0.78rem;
        }
        .filter-tab:hover { border-color: var(--primary); color: var(--primary); background: #f2f6fc; }
        .filter-tab.active { background: var(--primary); color: #fff; border-color: var(--primary); box-shadow: 0 3px 8px rgba(15,76,92,0.18); }
        .filter-count { background: rgba(0,0,0,0.12); padding: 0.1rem 0.5rem; border-radius: 20px; font-size: 0.68rem; font-weight: 700; }
        .filter-tab.active .filter-count { background: rgba(255,255,255,0.28); }

        /* ── TABLE CARD ──────────────────────────────────────────────── */
        .table-card { background: #fff; border: 1px solid rgba(203,213,224,0.6); border-radius: var(--radius-xl); overflow: hidden; box-shadow: 0 4px 16px rgba(15,76,92,0.03); }
        .table-card-header { padding: 1rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; }
        .table-card-title { font-weight: 700; font-size: 0.95rem; color: var(--navy); display: flex; align-items: center; gap: 0.5rem; }
        .item-count-badge { background: rgba(15,76,92,0.08); color: var(--primary-dark); font-size: 0.7rem; font-weight: 700; padding: 0.2rem 0.65rem; border-radius: 20px; }

        /* ── SEARCH ──────────────────────────────────────────────────── */
        .search-wrap { position: relative; }
        .search-wrap i { position: absolute; left: 0.9rem; top: 50%; transform: translateY(-50%); color: var(--warm-gray); font-size: 0.8rem; pointer-events: none; }
        .search-input { padding: 0.5rem 1rem 0.5rem 2.4rem; border: 1px solid var(--border); border-radius: 30px; font-size: 0.85rem; font-family: inherit; color: var(--dark); background: #fff; transition: border-color 0.15s, box-shadow 0.15s; min-width: 200px; }
        .search-input:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(15,76,92,0.1); }
        .search-input::placeholder { color: #a0aec0; }

        /* ── DATA TABLE ──────────────────────────────────────────────── */
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table thead th { padding: 0.85rem 1.25rem; text-align: left; font-size: 0.7rem; font-weight: 700; letter-spacing: 0.8px; text-transform: uppercase; color: var(--primary-dark); background: #f2f6fc; border-bottom: 2px solid var(--primary-light); white-space: nowrap; }
        .data-table thead th:last-child { text-align: right; }
        .data-table tbody td { padding: 1rem 1.25rem; border-bottom: 1px solid #edf2f7; vertical-align: middle; color: var(--dark); background: #fff; }
        .data-table tbody tr:last-child td { border-bottom: none; }
        .data-table tbody tr:hover td { background: #fafcff; }

        /* ── STOCK BADGES ────────────────────────────────────────────── */
        .stock-pill { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.72rem; font-weight: 700; padding: 0.3rem 0.75rem; border-radius: 30px; white-space: nowrap; }
        .stock-ok       { background: rgba(44,122,90,0.12);  color: var(--success); }
        .stock-low      { background: rgba(244,185,66,0.12); color: #9c6f1a; }
        .stock-critical { background: rgba(214,64,69,0.08);  color: var(--danger); }
        .dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; flex-shrink: 0; }

        /* ── UNIT CODE BADGE ─────────────────────────────────────────── */
        .unit-badge { display: inline-flex; align-items: center; background: rgba(15,76,92,0.08); color: var(--primary-dark); font-size: 0.72rem; font-weight: 700; padding: 0.25rem 0.65rem; border-radius: 20px; letter-spacing: 0.3px; }

        /* ── INGREDIENT NAME ─────────────────────────────────────────── */
        .ing-name { font-weight: 700; font-size: 0.9rem; color: var(--navy); }
        .ing-meta { font-size: 0.75rem; color: var(--warm-gray); margin-top: 1px; }

        /* ── ACTION BUTTONS ──────────────────────────────────────────── */
        .action-group { display: flex; gap: 0.4rem; justify-content: flex-end; }
        .btn-act { width: 34px; height: 34px; border-radius: var(--radius-sm); display: inline-flex; align-items: center; justify-content: center; border: none; cursor: pointer; font-size: 0.8rem; transition: all 0.15s; text-decoration: none; }
        .btn-act-edit { background: #fff3e0; color: #b85a1a; border: 1px solid rgba(244,185,66,0.2); }
        .btn-act-edit:hover  { background: var(--warning); color: #fff; }
        .btn-act-del  { background: rgba(214,64,69,0.08); color: var(--danger); border: 1px solid rgba(214,64,69,0.15); }
        .btn-act-del:hover   { background: var(--danger); color: #fff; }

        /* ── EMPTY STATE ─────────────────────────────────────────────── */
        .empty-state { text-align: center; padding: 3.5rem 1rem; color: var(--warm-gray); }
        .empty-state i { font-size: 3rem; opacity: 0.25; display: block; margin-bottom: 0.75rem; }
        .empty-state h6 { font-weight: 700; margin-bottom: 0.3rem; }
        .empty-state p { font-size: 0.85rem; opacity: 0.7; }

        /* ── MODALS ──────────────────────────────────────────────────── */
        .modal-content { border: none; border-radius: var(--radius-xl); box-shadow: 0 24px 60px rgba(0,0,0,0.12); }
        .modal-header { border-bottom: 1px solid var(--border); padding: 1.4rem 1.6rem; border-radius: var(--radius-xl) var(--radius-xl) 0 0; }
        .modal-title { font-family: 'Fraunces', Georgia, serif; font-size: 1.15rem; font-weight: 700; color: var(--navy); display: flex; align-items: center; gap: 0.5rem; }
        .modal-body { padding: 1.5rem 1.6rem; }
        .modal-footer { border-top: 1px solid var(--border); padding: 1.2rem 1.6rem; border-radius: 0 0 var(--radius-xl) var(--radius-xl); gap: 0.5rem; display: flex; align-items: center; justify-content: flex-end; flex-wrap: wrap; }

        .form-label { font-size: 0.82rem; font-weight: 700; color: var(--navy); margin-bottom: 0.4rem; }
        .form-control, .form-select { border: 1px solid var(--border); border-radius: var(--radius-md); padding: 0.6rem 0.9rem; font-size: 0.88rem; font-family: inherit; background: #fff; color: var(--dark); transition: border-color 0.15s, box-shadow 0.15s; }
        .form-control:focus, .form-select:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(15,76,92,0.12); }
        .form-control::placeholder { color: #a0aec0; }
        .input-group-text { background: #f2f6fc; border: 1px solid var(--border); border-radius: var(--radius-md) 0 0 var(--radius-md); padding: 0.6rem 0.8rem; color: var(--primary-dark); font-weight: 600; font-size: 0.85rem; }
        .input-group .form-control, .input-group .form-select { border-radius: 0 var(--radius-md) var(--radius-md) 0; }
        .form-check-input:checked { background-color: var(--primary); border-color: var(--primary); }
        .form-text { font-size: 0.75rem; color: var(--warm-gray); margin-top: 0.25rem; }

        /* ── MODAL BUTTONS ───────────────────────────────────────────── */
        .btn-modal-primary { background: var(--primary); color: #fff; border: none; padding: 0.6rem 1.4rem; border-radius: 40px; font-weight: 600; font-size: 0.88rem; cursor: pointer; transition: background 0.15s; display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; text-decoration: none; }
        .btn-modal-primary:hover { background: var(--primary-dark); color: #fff; }
        .btn-modal-secondary { background: #fff; color: var(--dark); border: 1px solid var(--border); padding: 0.6rem 1.4rem; border-radius: 40px; font-weight: 600; font-size: 0.88rem; cursor: pointer; transition: background 0.15s; display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; text-decoration: none; }
        .btn-modal-secondary:hover { background: var(--light-bg); color: var(--dark); }
        .btn-warning-solid { background: var(--warning); color: #1a202c; border: none; padding: 0.6rem 1.4rem; border-radius: 40px; font-weight: 600; font-size: 0.88rem; cursor: pointer; transition: background 0.15s; display: inline-flex; align-items: center; gap: 0.4rem; }
        .btn-warning-solid:hover { background: #e0a832; }
        .btn-danger-solid { background: var(--danger); color: #fff; border: none; padding: 0.6rem 1.4rem; border-radius: 40px; font-weight: 600; font-size: 0.88rem; cursor: pointer; width: 100%; transition: background 0.15s; display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; }
        .btn-danger-solid:hover { background: #bb3338; color: #fff; }

        /* ── CONFIRM MODAL ───────────────────────────────────────────── */
        .confirm-icon { width: 64px; height: 64px; border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; font-size: 1.75rem; margin: 0 auto 1rem; }
        .confirm-icon.danger { background: rgba(214,64,69,0.1); color: var(--danger); }
        .confirm-note { background: rgba(214,64,69,0.06); border: 1px dashed rgba(214,64,69,0.3); border-radius: var(--radius-md); padding: 0.75rem 1rem; font-size: 0.78rem; color: #9e3a34; }

        /* ── UNITS MODAL EXISTING LIST ───────────────────────────────── */
        .units-list { max-height: 210px; overflow-y: auto; border: 1px solid var(--border); border-radius: var(--radius-md); }
        .units-list-item { display: flex; align-items: center; justify-content: space-between; padding: 0.65rem 1rem; border-bottom: 1px solid #edf2f7; }
        .units-list-item:last-child { border-bottom: none; }
        .unit-edit-box { background: var(--light-bg); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 1rem; margin-top: 0.75rem; }
        .char-counter { font-size: 0.72rem; color: var(--warm-gray); text-align: right; margin-top: 0.25rem; }
        .char-counter.over { color: var(--danger); font-weight: 700; }

        /* ── DROPDOWN ────────────────────────────────────────────────── */
        .dropdown-menu { border-radius: var(--radius-lg); border: 1px solid var(--border); box-shadow: 0 12px 30px rgba(0,0,0,0.08); padding: 0.5rem; }
        .dropdown-item { border-radius: var(--radius-sm); font-size: 0.88rem; padding: 0.55rem 1rem; }
        .dropdown-item:hover { background: var(--lavender); color: var(--primary-dark); }
        .dropdown-item.text-danger:hover { background: rgba(214,64,69,0.08); color: var(--danger); }

        /* ── RESPONSIVE ──────────────────────────────────────────────── */
        @media (max-width: 1199.98px) { }
        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main { margin-left: 0; padding: 1.5rem 1.25rem; }
            .mobile-toggle { display: flex; align-items: center; }
        }
        @media (min-width: 768px) and (max-width: 1024px) {
            :root { --sidebar-w: 220px; }
            .main { padding: 1.5rem; }
            .data-table thead th, .data-table tbody td { padding: 0.8rem 1rem; }
        }
        @media (max-width: 767.98px) {
            .main { padding: 1.25rem 1rem; }
            .page-title { font-size: 1.4rem; }
            .head-actions { width: 100%; }
            .btn-primary-action, .btn-secondary-action { flex: 1; justify-content: center; }
            .data-table thead { display: none; }
            .data-table tbody tr { display: block; border: 1px solid var(--border); border-radius: var(--radius-lg); margin-bottom: 1rem; background: #fff; overflow: hidden; }
            .data-table tbody td { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid #edf2f7; padding: 0.75rem 1rem; }
            .data-table tbody td:last-child { border-bottom: none; }
            .data-table tbody td::before { content: attr(data-label); font-weight: 700; color: var(--warm-gray); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; flex-shrink: 0; margin-right: 0.5rem; padding-top: 2px; }
            .table-card { border-radius: var(--radius-lg); }
            .action-group { justify-content: flex-end; }
            .search-input { min-width: 100%; }
            .filter-tabs { gap: 0.4rem; }
            .modal-dialog { margin: 0.5rem; }
        }
        @media (max-width: 575.98px) {
            .brand-text-sub { display: none; }
            .topbar { padding: 0 1rem; }
        }

        /* ── Animations ──────────────────────────────────────────────── */
        .fade-up { animation: fadeUp 0.35s ease forwards; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        .fade-up-1 { animation-delay: 0.05s; opacity: 0; }
        .fade-up-2 { animation-delay: 0.10s; opacity: 0; }
        .fade-up-3 { animation-delay: 0.15s; opacity: 0; }
    </style>
</head>
<body>

<!-- ── TOPBAR ─────────────────────────────────────────────────────────────── -->
<header class="topbar">
    <button class="mobile-toggle" id="menuToggle" aria-label="Toggle menu"><i class="fas fa-bars"></i></button>
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
    <div class="dropdown">
        <button class="user-pill dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border:none;">
            <span class="user-avatar">
                <?php
                    $fullName = $_SESSION['full_name'] ?? 'Admin';
                    $parts    = explode(' ', trim($fullName));
                    $initials = strtoupper(substr($parts[0], 0, 1));
                    if (count($parts) > 1) $initials .= strtoupper(substr(end($parts), 0, 1));
                    echo htmlspecialchars($initials);
                ?>
            </span>
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
            <li><a class="dropdown-item text-danger" href="<?= $baseUrl ?>/index.php?page=logout"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
        </ul>
    </div>
</header>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ── SIDEBAR ────────────────────────────────────────────────────────────── -->
<nav class="sidebar" id="sidebar" aria-label="Main navigation">
    <div class="nav-wrap">
        <?php if (hasRole('admin')): ?>
        <div class="nav-section">Dashboard</div>
        <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <div class="nav-section">Management</div>
        <a href="<?= $baseUrl ?>/index.php?page=manage_menu"        class="nav-link"><i class="fas fa-utensils"></i>    Menu Items</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_recipes"     class="nav-link"><i class="fas fa-tags"></i>        Recipes</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_ingredients" class="nav-link active"><i class="fas fa-boxes"></i> Ingredients</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_addons"      class="nav-link"><i class="fas fa-plus-circle"></i> Add-ons</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_tables"      class="nav-link"><i class="fas fa-chair"></i>       Tables</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_users"       class="nav-link"><i class="fas fa-users"></i>       User Management</a>
        <div class="nav-section">Sales</div>
        <a href="<?= $baseUrl ?>/index.php?page=manage_pos"    class="nav-link"><i class="fas fa-cash-register"></i> POS System</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_orders" class="nav-link"><i class="fas fa-receipt"></i>       Orders</a>
        <a href="<?= $baseUrl ?>/index.php?page=reports"       class="nav-link"><i class="fas fa-chart-bar"></i>     Reports</a>
        <div class="nav-section">Configuration</div>
        <a href="<?= $baseUrl ?>/index.php?page=bir_settings" class="nav-link"><i class="fa-solid fa-building"></i>BIR Settings</a>
        <?php endif; ?>
    </div>
</nav>

<!-- ── MAIN ───────────────────────────────────────────────────────────────── -->
<main class="main" id="mainContent">

    <!-- Page Header -->
    <div class="page-head">
        <div>
            <h1 class="page-title">Ingredient Management</h1>
            <div class="breadcrumb-bar">
                <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard">Home</a>
                <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:0.6rem;"></i></span>
                <span>Ingredients</span>
            </div>
        </div>
        <div class="head-actions">
            <button type="button" class="btn-secondary-action" data-bs-toggle="modal" data-bs-target="#manageUnitsModal">
                <i class="fas fa-ruler-combined"></i> Manage Units
            </button>
            <button type="button" class="btn-primary-action" data-bs-toggle="modal" data-bs-target="#addIngredientModal">
                <i class="fas fa-plus"></i> Add Ingredient
            </button>
        </div>
    </div>

    <!-- Alerts -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert-bar success fade-up" role="alert">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button class="dismiss" aria-label="Close">&times;</button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert-bar danger fade-up" role="alert">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button class="dismiss" aria-label="Close">&times;</button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Filters -->
    <div class="filter-card fade-up fade-up-1">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="filter-section-label"><i class="fas fa-flask" style="font-size:0.7rem;"></i> Filter by Unit</div>
                <div class="filter-tabs" id="unitFilterContainer">
                    <button class="filter-tab active" data-filter-type="unit" data-filter-value="all">
                        <i class="fas fa-boxes" style="font-size:0.75rem;"></i> All Units
                        <span class="filter-count"><?= count($ingredients ?? []) ?></span>
                    </button>
                    <?php
                    $unit_counts    = [];
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
                        <i class="fas <?= htmlspecialchars($icon) ?>" style="font-size:0.75rem;"></i>
                        <?= htmlspecialchars($label) ?>
                        <span class="filter-count"><?= $cnt ?></span>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="filter-section-label"><i class="fas fa-signal" style="font-size:0.7rem;"></i> Filter by Status</div>
                <div class="filter-tabs" id="statusFilterContainer">
                    <?php
                    $status_counts = ['critical' => 0, 'low' => 0, 'ok' => 0];
                    foreach ($ingredients as $ing) {
                        if ($ing['stock_quantity'] <= ($ing['low_stock_alert'] * 0.3))  $status_counts['critical']++;
                        elseif ($ing['stock_quantity'] <= $ing['low_stock_alert'])       $status_counts['low']++;
                        else                                                              $status_counts['ok']++;
                    }
                    ?>
                    <button class="filter-tab active" data-filter-type="status" data-filter-value="all">
                        <i class="fas fa-list" style="font-size:0.75rem;"></i> All
                        <span class="filter-count"><?= count($ingredients ?? []) ?></span>
                    </button>
                    <button class="filter-tab" data-filter-type="status" data-filter-value="critical">
                        <i class="fas fa-circle-exclamation" style="font-size:0.75rem;"></i> Critical
                        <span class="filter-count"><?= $status_counts['critical'] ?></span>
                    </button>
                    <button class="filter-tab" data-filter-type="status" data-filter-value="low">
                        <i class="fas fa-triangle-exclamation" style="font-size:0.75rem;"></i> Low Stock
                        <span class="filter-count"><?= $status_counts['low'] ?></span>
                    </button>
                    <button class="filter-tab" data-filter-type="status" data-filter-value="ok">
                        <i class="fas fa-check-circle" style="font-size:0.75rem;"></i> In Stock
                        <span class="filter-count"><?= $status_counts['ok'] ?></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-card fade-up fade-up-2">
        <div class="table-card-header">
            <div class="table-card-title">
                <i class="fas fa-list" style="color:var(--primary-light);"></i>
                Ingredient List
                <span class="item-count-badge" id="ingredientCount"><?= count($ingredients ?? []) ?> items</span>
            </div>
            <div class="search-wrap">
                <i class="fas fa-search"></i>
                <input type="text" class="search-input" id="searchInput" placeholder="Search ingredients…">
            </div>
        </div>
        <div style="overflow-x:auto;">
            <?php if (empty($ingredients)): ?>
                <div class="empty-state">
                    <i class="fas fa-boxes"></i>
                    <h6>No ingredients found</h6>
                    <p>Click "Add Ingredient" to get started</p>
                </div>
            <?php else: ?>
                <table class="data-table" id="ingredientsTable">
                    <thead>
                        <tr>
                            <th>Ingredient</th>
                            <th>Stock</th>
                            <th>Unit</th>
                            <th>Low Alert</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                            <th style="text-align:right;">Actions</th>
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
                                <div class="ing-name"><?= htmlspecialchars($i['ingredient_name']) ?></div>
                            </td>
                            <td data-label="Stock">
                                <span style="font-weight:700;color:var(--navy);"><?= htmlspecialchars($i['stock_quantity']) ?></span>
                            </td>
                            <td data-label="Unit">
                                <span class="unit-badge" title="<?= htmlspecialchars($i['unit_label']) ?>">
                                    <?= htmlspecialchars(strtoupper($i['unit_code'])) ?>
                                </span>
                            </td>
                            <td data-label="Low Alert">
                                <span style="font-size:0.88rem;color:var(--warm-gray);"><?= htmlspecialchars($i['low_stock_alert']) ?></span>
                            </td>
                            <td data-label="Status">
                                <?php if ($stockStatus === 'critical'): ?>
                                    <span class="stock-pill stock-critical"><span class="dot"></span> Critical</span>
                                <?php elseif ($stockStatus === 'low'): ?>
                                    <span class="stock-pill stock-low"><span class="dot"></span> Low Stock</span>
                                <?php else: ?>
                                    <span class="stock-pill stock-ok"><span class="dot"></span> In Stock</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Last Updated">
                                <span style="font-size:0.78rem;color:var(--warm-gray);"><?= date('M d, Y · h:i A', strtotime($i['last_updated'])) ?></span>
                            </td>
                            <td data-label="Actions" style="text-align:right;">
                                <div class="action-group">
                                    <button type="button" class="btn-act btn-act-edit"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editModal<?= $i['ingredient_id'] ?>"
                                            title="Edit ingredient">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn-act btn-act-del"
                                            onclick="confirmDelete(<?= $i['ingredient_id'] ?>, '<?= htmlspecialchars(addslashes($i['ingredient_name'])) ?>')"
                                            title="Delete ingredient">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- ═══ DELETE CONFIRM MODAL ════════════════════════════════════════════ -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
            <div class="modal-content">
                <div class="modal-header" style="border-bottom:none;padding-bottom:0;">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center" style="padding:1rem 1.6rem 1.25rem;">
                    <div class="confirm-icon danger"><i class="fas fa-trash"></i></div>
                    <h5 style="font-family:'Fraunces',serif;font-weight:700;color:var(--navy);margin-bottom:0.5rem;">Delete Ingredient?</h5>
                    <p style="font-size:0.85rem;color:var(--warm-gray);margin-bottom:0.5rem;">You're about to delete:</p>
                    <p id="deleteIngredientName" style="color:var(--danger);font-weight:700;font-size:1rem;margin-bottom:1rem;"></p>
                    <div class="confirm-note">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        This cannot be undone. Ingredients used in recipes cannot be deleted.
                    </div>
                </div>
                <div class="modal-footer" style="border-top:none;">
                    <button type="button" class="btn-modal-secondary" style="flex:1;" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="index.php?page=manage_ingredients" style="flex:1;">
                        <input type="hidden" name="delete_ingredient" value="1">
                        <input type="hidden" name="ingredient_id" id="deleteIngredientId">
                        <button type="submit" class="btn-danger-solid"><i class="fas fa-trash me-1"></i> Yes, Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ ADD INGREDIENT MODAL ════════════════════════════════════════════ -->
    <div class="modal fade" id="addIngredientModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus-circle" style="color:var(--secondary);font-size:1rem;"></i> Add New Ingredient</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="index.php?page=manage_ingredients">
                    <div class="modal-body">
                        <input type="hidden" name="add_ingredient" value="1">
                        <div class="mb-3">
                            <label class="form-label">Ingredient Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                <input type="text" class="form-control" name="name" placeholder="e.g. Coffee Beans" required>
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
                                    <option value="">— Select Unit —</option>
                                    <?php foreach ($units as $uid => $ulabel): ?>
                                        <option value="<?= htmlspecialchars($uid) ?>"><?= htmlspecialchars($ulabel) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Low Stock Alert</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-exclamation-triangle"></i></span>
                                <input type="number" class="form-control" name="low_stock_alert" value="10" min="0">
                            </div>
                            <div class="form-text">Alert when stock falls below this level</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-modal-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-modal-primary"><i class="fas fa-plus me-1"></i> Add Ingredient</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ═══ EDIT INGREDIENT MODALS ══════════════════════════════════════════ -->
    <?php foreach ($ingredients as $i): ?>
    <div class="modal fade" id="editModal<?= $i['ingredient_id'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit" style="color:var(--warning);font-size:1rem;"></i> Edit Ingredient</h5>
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
                        <div class="mb-2">
                            <label class="form-label">Low Stock Alert</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-exclamation-triangle"></i></span>
                                <input type="number" class="form-control" name="low_stock_alert" value="<?= htmlspecialchars($i['low_stock_alert']) ?>" min="0">
                            </div>
                        </div>
                        <div class="form-text"><i class="fas fa-clock me-1"></i> Last updated: <?= date('M d, Y · h:i A', strtotime($i['last_updated'])) ?></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-modal-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-warning-solid"><i class="fas fa-save me-1"></i> Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- ═══ MANAGE UNITS MODAL ══════════════════════════════════════════════ -->
<div class="modal fade" id="manageUnitsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-ruler-combined" style="color:var(--secondary);font-size:1rem;"></i> Manage Units</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- ADD UNIT FORM — standalone, no nesting -->
            <form method="POST" action="index.php?page=manage_ingredients" id="addUnitForm" novalidate>
                <input type="hidden" name="add_unit" value="1">
                <div class="modal-body">
                    <div class="row g-3 mb-2">
                        <div class="col-4">
                            <label for="modal_unit_code" class="form-label">Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="modal_unit_code" name="unit_code"
                                   placeholder="e.g. kg" maxlength="20" autocomplete="off" required>
                            <div class="char-counter" id="unitCodeCounter">0 / 20</div>
                        </div>
                        <div class="col-8">
                            <label for="modal_unit_label" class="form-label">Label <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="modal_unit_label" name="unit_label"
                                   placeholder="e.g. Kilogram" maxlength="50" autocomplete="off" required>
                        </div>
                    </div>
                    <div id="unitDupWarning" class="alert-bar danger d-none" style="padding:0.6rem 0.9rem;font-size:0.8rem;margin-bottom:0;">
                        <i class="fas fa-exclamation-triangle me-1"></i> A unit with this code already exists.
                    </div>

                    <div style="margin-top:1.25rem;">
                        <div class="form-label">Existing Units</div>
                        <?php if (empty($units_raw)): ?>
                            <p style="font-size:0.82rem;color:var(--warm-gray);font-style:italic;">No units yet.</p>
                        <?php else: ?>
                            <div class="units-list">
                                <?php foreach ($units_raw as $u): ?>
                                <div class="units-list-item">
                                    <div style="display:flex;align-items:center;gap:0.6rem;">
                                        <span style="font-size:0.88rem;font-weight:600;color:var(--navy);"><?= htmlspecialchars($u['unit_label']) ?></span>
                                        <span class="unit-badge"><?= htmlspecialchars($u['unit_code']) ?></span>
                                    </div>
                                    <div style="display:flex;gap:0.35rem;">
                                        <button type="button" class="btn-act btn-act-edit"
                                                onclick="unitEditInline(<?= $u['unit_id'] ?>, <?= htmlspecialchars(json_encode($u['unit_code'])) ?>, <?= htmlspecialchars(json_encode($u['unit_label'])) ?>)"
                                                title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <!-- DELETE UNIT: standalone form, NOT nested inside addUnitForm -->
                                        <button type="button" class="btn-act btn-act-del" title="Delete"
                                                onclick="deleteUnit(<?= $u['unit_id'] ?>, '<?= htmlspecialchars(addslashes($u['unit_label'])) ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn-modal-primary" id="saveUnitBtn"><i class="fas fa-plus me-1"></i> Add Unit</button>
                </div>
            </form>

            <!-- EDIT UNIT FORM — sibling to addUnitForm, never nested inside it -->
            <div id="unitEditSection" class="unit-edit-box d-none" style="margin:0 1.6rem 1.2rem;">
                <form method="POST" action="index.php?page=manage_ingredients" id="editUnitForm">
                    <input type="hidden" name="edit_unit" value="1">
                    <input type="hidden" name="unit_id" id="editUnitId">
                    <label class="form-label" style="font-size:0.78rem;">Edit Unit</label>
                    <div class="row g-2 mb-2">
                        <div class="col-4">
                            <input type="text" class="form-control form-control-sm" name="unit_code" id="editUnitCode" placeholder="Code" maxlength="20" required>
                            <div class="form-text">Code</div>
                        </div>
                        <div class="col-8">
                            <input type="text" class="form-control form-control-sm" name="unit_label" id="editUnitLabel" placeholder="Label" required>
                            <div class="form-text">Label</div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn-modal-primary" style="font-size:0.82rem;padding:0.4rem 1rem;"><i class="fas fa-save me-1"></i> Save</button>
                        <button type="button" class="btn-modal-secondary" style="font-size:0.82rem;padding:0.4rem 1rem;"
                                onclick="document.getElementById('unitEditSection').classList.add('d-none')">Cancel</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<!-- DELETE UNIT hidden form — lives outside the modal entirely -->
<form method="POST" action="index.php?page=manage_ingredients" id="deleteUnitForm" style="display:none;">
    <input type="hidden" name="delete_unit" value="1">
    <input type="hidden" name="unit_id" id="deleteUnitId">
</form>

</main>

<script src="<?= $baseUrl ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
/* ── Sidebar ──────────────────────────────────────────────────────── */
document.getElementById('menuToggle')?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('show');
    document.getElementById('sidebarOverlay').classList.toggle('show');
});
document.getElementById('sidebarOverlay')?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.remove('show');
    document.getElementById('sidebarOverlay').classList.remove('show');
});

/* ── Alert dismiss ────────────────────────────────────────────────── */
document.querySelectorAll('.alert-bar .dismiss').forEach(btn => {
    btn.addEventListener('click', () => btn.closest('.alert-bar').remove());
});
setTimeout(() => {
    document.querySelectorAll('.alert-bar').forEach(a => { a.style.opacity = '0'; });
    setTimeout(() => document.querySelectorAll('.alert-bar').forEach(a => a.remove()), 400);
}, 6000);

/* ── Filtering ────────────────────────────────────────────────────── */
let currentUnitFilter = 'all', currentStatusFilter = 'all', currentSearch = '';

document.querySelectorAll('#unitFilterContainer .filter-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('#unitFilterContainer .filter-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        currentUnitFilter = this.dataset.filterValue;
        filterTable();
    });
});
document.querySelectorAll('#statusFilterContainer .filter-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('#statusFilterContainer .filter-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        currentStatusFilter = this.dataset.filterValue;
        filterTable();
    });
});
document.getElementById('searchInput')?.addEventListener('input', function() {
    currentSearch = this.value.toLowerCase();
    filterTable();
});
function filterTable() {
    let visible = 0;
    document.querySelectorAll('#ingredientsTableBody tr').forEach(row => {
        const show = (currentUnitFilter   === 'all' || row.dataset.unit   === currentUnitFilter)
                  && (currentStatusFilter === 'all' || row.dataset.status === currentStatusFilter)
                  && (currentSearch === '' || row.textContent.toLowerCase().includes(currentSearch));
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    const el = document.getElementById('ingredientCount');
    if (el) el.textContent = `${visible} item${visible !== 1 ? 's' : ''}`;
}

/* ── Delete confirm ───────────────────────────────────────────────── */
function confirmDelete(id, name) {
    document.getElementById('deleteIngredientId').value         = id;
    document.getElementById('deleteIngredientName').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteConfirmModal')).show();
}

/* ── Manage Units Modal ───────────────────────────────────────────── */
(function() {
    const codeInput   = document.getElementById('modal_unit_code');
    const labelInput  = document.getElementById('modal_unit_label');
    const codeCounter = document.getElementById('unitCodeCounter');
    const dupWarning  = document.getElementById('unitDupWarning');
    const saveBtn     = document.getElementById('saveUnitBtn');
    const form        = document.getElementById('addUnitForm');
    const existing    = new Set(<?php echo json_encode(array_column($units_raw, 'unit_code')); ?>);

    codeInput?.addEventListener('input', function() {
        const len = this.value.length;
        codeCounter.textContent = `${len} / 20`;
        codeCounter.classList.toggle('over', len >= 20);
        dupWarning.classList.toggle('d-none', !existing.has(this.value.trim().toLowerCase()));
        codeInput.classList.remove('is-invalid');
    });
    saveBtn?.addEventListener('click', function() {
        const code  = codeInput.value.trim();
        const label = labelInput.value.trim();
        if (!code)  { codeInput.classList.add('is-invalid');  codeInput.focus();  return; }
        if (!label) { labelInput.classList.add('is-invalid'); labelInput.focus(); return; }
        if (existing.has(code.toLowerCase())) { dupWarning.classList.remove('d-none'); codeInput.focus(); return; }
        codeInput.classList.remove('is-invalid');
        labelInput.classList.remove('is-invalid');
        form.submit();
    });
    [codeInput, labelInput].forEach(el => {
        el?.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); saveBtn.click(); } });
    });
    document.getElementById('manageUnitsModal')?.addEventListener('hidden.bs.modal', function() {
        form.reset();
        codeCounter.textContent = '0 / 20';
        codeCounter.classList.remove('over');
        dupWarning.classList.add('d-none');
        codeInput.classList.remove('is-invalid');
        labelInput.classList.remove('is-invalid');
        document.getElementById('unitEditSection').classList.add('d-none');
    });
})();

function unitEditInline(id, code, label) {
    document.getElementById('editUnitId').value    = id;
    document.getElementById('editUnitCode').value  = code;
    document.getElementById('editUnitLabel').value = label;
    document.getElementById('unitEditSection').classList.remove('d-none');
    document.getElementById('editUnitCode').focus();
}
function deleteUnit(id, label) {
    if (!confirm("Delete unit '" + label + "'?")) return;
    document.getElementById('deleteUnitId').value = id;
    document.getElementById('deleteUnitForm').submit();
}
/* ── Modal backdrop cleanup ───────────────────────────────────────── */
document.addEventListener('hidden.bs.modal', function() {
    if (document.querySelector('.modal.show')) return;
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
});
</script>
</body>
</html>