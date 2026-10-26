<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php require_once __DIR__ . "/../../core/csrfHelper.php"; echo csrf_head($baseUrl ?? ""); ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management — The Lounge by: CJV & TEK</title>
    <link rel="icon" type="image/png" href="<?= $baseUrl ?>/assets/images/logo/logo.png">
    <link href="<?= $baseUrl ?>/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/common.css">
    <link href="<?= $baseUrl ?>/assets/vendor/fonts/app.css" rel="stylesheet">
    <style>
        /* ── TOKENS ─────────────────────────────────────────────────── */

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--light-bg); color: var(--dark); font-size: 14px; line-height: 1.6; -webkit-font-smoothing: antialiased; }

        /* ── TOPBAR ─────────────────────────────────────────────────── */
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
        .user-avatar-top {
            width: 30px; height: 30px; border-radius: 50%;
            background: var(--primary); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.72rem; font-weight: 700; flex-shrink: 0;
        }
        .user-name-text { font-size: 0.82rem; font-weight: 600; color: var(--navy); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-caret { color: var(--warm-gray); font-size: 0.65rem; flex-shrink: 0; }

        /* ── SIDEBAR ─────────────────────────────────────────────────── */
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
        .nav-link i { width: 20px; text-align: center; font-size: 0.9rem; flex-shrink: 0; }
        .nav-link:hover { background: var(--lavender); color: var(--primary-dark); }
        .nav-link.active { background: var(--primary); color: #fff; font-weight: 600; box-shadow: 0 4px 12px rgba(15,76,92,0.2); }
        .sidebar-overlay {
            display: none; position: fixed; top: var(--top-h); left: 0; right: 0; bottom: 0;
            background: rgba(15,76,92,0.25); z-index: 1025; backdrop-filter: blur(2px);
        }
        .sidebar-overlay.show { display: block; }

        /* ── MAIN ─────────────────────────────────────────────────────── */
        .main { margin-left: var(--sidebar-w); margin-top: var(--top-h); padding: 2rem 1.75rem; min-height: calc(100vh - var(--top-h)); }

        /* ── PAGE HEADER ──────────────────────────────────────────────── */
        .page-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; margin-bottom: 1.75rem; flex-wrap: wrap; }
        .page-title { font-family: 'Fraunces', Georgia, serif; font-size: 1.75rem; font-weight: 700; color: var(--navy); line-height: 1.1; }
        .breadcrumb-bar { display: flex; align-items: center; gap: 0.4rem; font-size: 0.8rem; color: var(--warm-gray); margin-top: 0.3rem; }
        .breadcrumb-bar a { color: var(--primary-light); text-decoration: none; font-weight: 500; }
        .breadcrumb-bar a:hover { text-decoration: underline; }
        .breadcrumb-sep { color: var(--border); }

        /* ── DROPDOWN ─────────────────────────────────────────────────── */
        .dropdown-menu { border-radius: var(--radius-lg); border: 1px solid var(--border); box-shadow: 0 12px 30px rgba(0,0,0,0.08); padding: 0.5rem; }
        .dropdown-item { border-radius: var(--radius-sm); font-size: 0.88rem; padding: 0.55rem 1rem; }
        .dropdown-item:hover { background: var(--lavender); color: var(--primary-dark); }
        .dropdown-item.text-danger:hover { background: rgba(214,64,69,0.08); color: var(--danger); }

        /* ── BUTTONS ──────────────────────────────────────────────────── */
        .btn-primary-action {
            display: inline-flex; align-items: center; gap: 0.5rem;
            background: var(--primary); color: #fff;
            padding: 0.55rem 1.3rem; border-radius: 40px;
            font-weight: 600; font-size: 0.88rem; border: none; cursor: pointer;
            transition: all 0.2s; text-decoration: none; white-space: nowrap; font-family: inherit;
        }
        .btn-primary-action:hover { background: var(--primary-dark); color: #fff; transform: translateY(-1px); box-shadow: 0 6px 16px rgba(15,76,92,0.2); }

        /* ── ALERTS ───────────────────────────────────────────────────── */
        .alert-bar {
            border-radius: var(--radius-md); padding: 0.85rem 1.2rem;
            display: flex; align-items: center; gap: 0.65rem;
            font-size: 0.88rem; font-weight: 500; margin-bottom: 1.5rem;
        }
        .alert-bar.success { background: var(--success-soft); color: #1e4a3b; border-left: 4px solid var(--success); }
        .alert-bar.danger  { background: var(--danger-soft);  color: #9e3a34; border-left: 4px solid var(--danger); }
        .alert-bar .dismiss { margin-left: auto; cursor: pointer; background: none; border: none; color: inherit; opacity: 0.5; font-size: 1.1rem; padding: 0; }
        .alert-bar .dismiss:hover { opacity: 1; }

        /* ── STATS GRID ───────────────────────────────────────────────── */
        .stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.75rem; }
        .stat-card {
            background: #fff; border: 1px solid rgba(203,213,224,0.6);
            border-radius: var(--radius-lg); padding: 1.25rem 1.35rem;
            display: flex; align-items: center; gap: 1rem;
            position: relative; overflow: hidden;
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .stat-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: var(--primary); }
        .stat-card.c-success::before { background: var(--success); }
        .stat-card.c-info::before    { background: var(--info); }
        .stat-card.c-warning::before { background: var(--warning); }
        .stat-card.c-danger::before  { background: var(--danger); }
        .stat-card:hover { box-shadow: 0 8px 24px rgba(15,76,92,0.08); transform: translateY(-2px); }
        .stat-icon {
            width: 46px; height: 46px; border-radius: var(--radius-md);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; flex-shrink: 0;
            background: #f0f5fa; color: var(--primary);
            border: 1px solid rgba(15,76,92,0.1);
        }
        .stat-icon.c-success { background: var(--success-soft); color: var(--success); border-color: rgba(44,122,90,0.15); }
        .stat-icon.c-info    { background: var(--info-soft);    color: var(--info);    border-color: rgba(94,155,158,0.2); }
        .stat-icon.c-warning { background: var(--warning-soft); color: var(--warning); border-color: rgba(244,185,66,0.2); }
        .stat-icon.c-danger  { background: var(--danger-soft);  color: var(--danger);  border-color: rgba(214,64,69,0.15); }
        .stat-label { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: var(--warm-gray); margin-bottom: 0.2rem; }
        .stat-value { font-size: 1.65rem; font-weight: 800; color: var(--navy); line-height: 1; }

        /* ── TABLE CARD ───────────────────────────────────────────────── */
        .table-card {
            background: #fff; border: 1px solid rgba(203,213,224,0.6);
            border-radius: var(--radius-xl); overflow: hidden;
            box-shadow: 0 4px 16px rgba(15,76,92,0.03);
        }
        .table-card-header {
            padding: 1rem 1.5rem; border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 0.75rem;
        }
        .table-card-title { font-weight: 700; font-size: 0.95rem; color: var(--navy); display: flex; align-items: center; gap: 0.5rem; }
        .item-count-badge { background: rgba(15,76,92,0.08); color: var(--primary-dark); font-size: 0.7rem; font-weight: 700; padding: 0.2rem 0.65rem; border-radius: 20px; }

        /* ── SEARCH ───────────────────────────────────────────────────── */
        .search-wrap { position: relative; }
        .search-wrap i { position: absolute; left: 0.9rem; top: 50%; transform: translateY(-50%); color: var(--warm-gray); font-size: 0.82rem; pointer-events: none; }
        .search-wrap input {
            padding: 0.45rem 0.9rem 0.45rem 2.4rem; border-radius: 40px;
            border: 1px solid var(--border); font-size: 0.85rem; font-family: inherit;
            background: var(--light-bg); color: var(--dark); width: 220px;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .search-wrap input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(15,76,92,0.1); background: #fff; width: 260px; }

        /* ── DATA TABLE ───────────────────────────────────────────────── */
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table thead th {
            padding: 0.85rem 1.25rem; text-align: left;
            font-size: 0.7rem; font-weight: 700; letter-spacing: 0.8px;
            text-transform: uppercase; color: var(--primary-dark);
            background: #f2f6fc; border-bottom: 2px solid var(--primary-light);
            white-space: nowrap;
        }
        .data-table thead th:last-child { text-align: right; }
        .data-table tbody td {
            padding: 0.95rem 1.25rem; border-bottom: 1px solid #edf2f7;
            vertical-align: middle; color: var(--dark); background: #fff;
        }
        .data-table tbody tr:last-child td { border-bottom: none; }
        .data-table tbody tr:hover td { background: #fafcff; }
        .data-table tbody tr.row-inactive td { opacity: 0.65; }

        /* ── USER AVATAR ──────────────────────────────────────────────── */
        .user-avatar {
            width: 40px; height: 40px; border-radius: var(--radius-md);
            background: linear-gradient(135deg, var(--primary), var(--navy));
            color: #fff; display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.95rem; flex-shrink: 0;
        }
        .user-avatar.inactive { background: linear-gradient(135deg, #94a3b8, #64748b); }

        /* ── PILLS ────────────────────────────────────────────────────── */
        .pill { display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.72rem; font-weight: 700; padding: 0.28rem 0.7rem; border-radius: 30px; white-space: nowrap; }
        .pill-admin   { background: rgba(214,64,69,0.1);  color: var(--danger); }
        .pill-cashier { background: rgba(94,155,158,0.12); color: var(--info); }
        .pill-kitchen { background: rgba(244,185,66,0.12); color: #9a6c00; }
        .pill-active   { background: rgba(44,122,90,0.12); color: var(--success); }
        .pill-inactive { background: rgba(214,64,69,0.1);  color: var(--danger); }
        .dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; flex-shrink: 0; }

        /* ── ACTION BUTTONS ───────────────────────────────────────────── */
        .action-group { display: flex; gap: 0.4rem; justify-content: flex-end; }
        .btn-act { width: 34px; height: 34px; border-radius: var(--radius-sm); display: inline-flex; align-items: center; justify-content: center; border: none; cursor: pointer; font-size: 0.8rem; transition: all 0.15s; text-decoration: none; }
        .btn-act-edit     { background: #fff3e0; color: #b85a1a; border: 1px solid rgba(244,185,66,0.2); }
        .btn-act-edit:hover   { background: var(--warning); color: #fff; }
        .btn-act-deactivate { background: rgba(214,64,69,0.08); color: var(--danger); border: 1px solid rgba(214,64,69,0.15); }
        .btn-act-deactivate:hover { background: var(--danger); color: #fff; }
        .btn-act-activate   { background: rgba(44,122,90,0.08); color: var(--success); border: 1px solid rgba(44,122,90,0.15); }
        .btn-act-activate:hover { background: var(--success); color: #fff; }
        .btn-act-self { background: #f0f5fa; color: #a0aec0; border: 1px solid var(--border); cursor: not-allowed; opacity: 0.5; }

        /* ── INFO CARD (right column) ─────────────────────────────────── */
        .info-card {
            background: #fff; border: 1px solid rgba(203,213,224,0.6);
            border-radius: var(--radius-xl);
            box-shadow: 0 4px 16px rgba(15,76,92,0.03);
        }
        .info-card-head {
            padding: 1rem 1.4rem; border-bottom: 1px solid var(--border);
            font-weight: 700; font-size: 0.92rem; color: var(--navy);
            display: flex; align-items: center; gap: 0.5rem;
        }
        .info-card-head i { color: var(--secondary); }
        .info-card-body { padding: 1.1rem 1.4rem; }
        .perm-row { display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.6rem 0; border-bottom: 1px solid #edf2f7; }
        .perm-row:last-child { border-bottom: none; }
        .perm-desc { font-size: 0.8rem; color: var(--warm-gray); }

        /* ── MODALS ───────────────────────────────────────────────────── */
        .modal-content { border: none; border-radius: var(--radius-xl); box-shadow: 0 24px 60px rgba(0,0,0,0.12); }
        .modal-header { border-bottom: 1px solid var(--border); padding: 1.4rem 1.6rem; border-radius: var(--radius-xl) var(--radius-xl) 0 0; }
        .modal-title { font-family: 'Fraunces', Georgia, serif; font-size: 1.15rem; font-weight: 700; color: var(--navy); display: flex; align-items: center; gap: 0.5rem; }
        .modal-body  { padding: 1.5rem 1.6rem; }
        .modal-footer { border-top: 1px solid var(--border); padding: 1.2rem 1.6rem; border-radius: 0 0 var(--radius-xl) var(--radius-xl); gap: 0.5rem; }
        .form-label { font-size: 0.82rem; font-weight: 700; color: var(--navy); margin-bottom: 0.4rem; }
        .form-control, .form-select {
            border: 1px solid var(--border); border-radius: var(--radius-md);
            padding: 0.6rem 0.9rem; font-size: 0.88rem; font-family: inherit;
            background: #fff; color: var(--dark); transition: border-color 0.15s, box-shadow 0.15s;
        }
        .form-control:focus, .form-select:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(15,76,92,0.12); }
        .form-control::placeholder { color: #a0aec0; }
        .input-group-text { background: #f2f6fc; border: 1px solid var(--border); border-radius: var(--radius-md) 0 0 var(--radius-md); padding: 0.6rem 0.8rem; color: var(--primary-dark); font-size: 0.85rem; }
        .input-group .form-control { border-radius: 0 var(--radius-md) var(--radius-md) 0; }
        .form-text { font-size: 0.75rem; color: var(--warm-gray); margin-top: 0.25rem; }
        .form-check-input:checked { background-color: var(--primary); border-color: var(--primary); }
        .btn-modal-primary { background: var(--primary); color: #fff; border: none; padding: 0.6rem 1.4rem; border-radius: 40px; font-weight: 600; font-size: 0.88rem; cursor: pointer; transition: background 0.15s; display: inline-flex; align-items: center; gap: 0.4rem; font-family: inherit; }
        .btn-modal-primary:hover { background: var(--primary-dark); color: #fff; }
        .btn-modal-warning { background: var(--warning); color: #1a202c; border: none; padding: 0.6rem 1.4rem; border-radius: 40px; font-weight: 600; font-size: 0.88rem; cursor: pointer; transition: background 0.15s; display: inline-flex; align-items: center; gap: 0.4rem; font-family: inherit; }
        .btn-modal-warning:hover { background: #e0a830; color: #1a202c; }
        .btn-modal-secondary { background: #fff; color: var(--dark); border: 1px solid var(--border); padding: 0.6rem 1.4rem; border-radius: 40px; font-weight: 600; font-size: 0.88rem; cursor: pointer; transition: background 0.15s; font-family: inherit; }
        .btn-modal-secondary:hover { background: var(--light-bg); }
        .btn-toggle-deactivate { background: var(--danger); color: #fff; border: none; padding: 0.6rem 1.4rem; border-radius: 40px; font-weight: 600; font-size: 0.88rem; cursor: pointer; width: 100%; transition: background 0.15s; display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; font-family: inherit; }
        .btn-toggle-deactivate:hover { background: #bb3338; }
        .btn-toggle-activate { background: var(--success); color: #fff; border: none; padding: 0.6rem 1.4rem; border-radius: 40px; font-weight: 600; font-size: 0.88rem; cursor: pointer; width: 100%; transition: background 0.15s; display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; font-family: inherit; }
        .btn-toggle-activate:hover { background: #215c43; }

        /* ── TOGGLE CONFIRM MODAL ─────────────────────────────────────── */
        .confirm-icon { width: 64px; height: 64px; border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; font-size: 1.75rem; margin: 0 auto 1rem; }
        .confirm-icon.danger  { background: rgba(214,64,69,0.1);  color: var(--danger); }
        .confirm-icon.success { background: rgba(44,122,90,0.1);  color: var(--success); }
        .confirm-note { border-radius: var(--radius-md); padding: 0.75rem 1rem; font-size: 0.78rem; margin-top: 0.75rem; }
        .confirm-note.danger  { background: rgba(214,64,69,0.06); border: 1px dashed rgba(214,64,69,0.3); color: #9e3a34; }
        .confirm-note.success { background: rgba(44,122,90,0.06);  border: 1px dashed rgba(44,122,90,0.3);  color: #1e4a3b; }

        /* ── EMPTY STATE ──────────────────────────────────────────────── */
        .empty-state { text-align: center; padding: 4rem 1rem; color: var(--warm-gray); }
        .empty-state i { font-size: 2.5rem; opacity: 0.2; display: block; margin-bottom: 0.8rem; }

        /* ── ANIMATIONS ───────────────────────────────────────────────── */
        .fade-up { animation: fadeUp 0.35s ease forwards; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        .fade-up-1 { animation-delay: 0.05s; opacity: 0; }
        .fade-up-2 { animation-delay: 0.10s; opacity: 0; }
        .fade-up-3 { animation-delay: 0.15s; opacity: 0; }
        .fade-up-4 { animation-delay: 0.20s; opacity: 0; }

        /* ── RESPONSIVE ───────────────────────────────────────────────── */
        @media (max-width: 1199.98px) { .stats-row { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main { margin-left: 0; padding: 1.5rem 1.25rem; }
            .mobile-toggle { display: flex; align-items: center; }
        }
        @media (min-width: 768px) and (max-width: 1024px) {
            .stats-row { grid-template-columns: repeat(2, 1fr); }
            .data-table thead th, .data-table tbody td { padding: 0.8rem 1rem; }
        }
        @media (max-width: 767.98px) {
            .main { padding: 1.25rem 1rem; }
            .stats-row { grid-template-columns: repeat(2, 1fr); gap: 0.75rem; }
            .page-title { font-size: 1.4rem; }
            .search-wrap input { width: 100%; }
            .search-wrap input:focus { width: 100%; }
            .data-table thead { display: none; }
            .data-table tbody tr { display: block; border: 1px solid var(--border); border-radius: var(--radius-lg); margin-bottom: 1rem; background: #fff; overflow: hidden; }
            .data-table tbody tr.row-inactive { opacity: 0.65; }
            .data-table tbody td { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #edf2f7; padding: 0.75rem 1rem; }
            .data-table tbody td:last-child { border-bottom: none; }
            .data-table tbody td::before { content: attr(data-label); font-weight: 700; color: var(--warm-gray); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; flex-shrink: 0; margin-right: 0.5rem; padding-top: 2px; }
            .action-group { justify-content: flex-end; }
            .modal-dialog { margin: 0.5rem; }
        }
        @media (max-width: 575.98px) {
            .brand-text-sub { display: none; }
            .topbar { padding: 0 1rem; }
            .stats-row { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 480px) {
            .stats-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- ── TOPBAR ─────────────────────────────────────────────────────────── -->
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
        <?php
            $fullName = $_SESSION['full_name'] ?? 'Admin';
            $parts    = explode(' ', trim($fullName));
            $initials = strtoupper(substr($parts[0], 0, 1));
            if (count($parts) > 1) $initials .= strtoupper(substr(end($parts), 0, 1));
        ?>
        <button class="user-pill dropdown-toggle" type="button" data-bs-toggle="dropdown" style="border:none;">
            <span class="user-avatar-top"><?= htmlspecialchars($initials) ?></span>
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

<!-- ── SIDEBAR ───────────────────────────────────────────────────────── -->
<nav class="sidebar" id="sidebar">
    <div class="nav-wrap">
        <div class="nav-section">Dashboard</div>
        <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <div class="nav-section">Management</div>
        <a href="<?= $baseUrl ?>/index.php?page=manage_menu"        class="nav-link"><i class="fas fa-utensils"></i> Menu Items</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_recipes"     class="nav-link"><i class="fas fa-tags"></i> Recipes</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_ingredients" class="nav-link"><i class="fas fa-boxes"></i> Ingredients</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_addons"      class="nav-link"><i class="fas fa-plus-circle"></i> Add-ons</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_tables"      class="nav-link"><i class="fas fa-chair"></i> Tables</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_users"       class="nav-link active"><i class="fas fa-users"></i> User Management</a>
        <div class="nav-section">Sales</div>
        <a href="<?= $baseUrl ?>/index.php?page=manage_pos"    class="nav-link"><i class="fas fa-cash-register"></i> POS System</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_orders" class="nav-link"><i class="fas fa-receipt"></i> Orders</a>
        <a href="<?= $baseUrl ?>/index.php?page=reports"       class="nav-link"><i class="fas fa-chart-bar"></i> Reports</a>
        <div class="nav-section">Configuration</div>
        <a href="<?= $baseUrl ?>/index.php?page=bir_settings" class="nav-link"><i class="fa-solid fa-building"></i>BIR Settings</a>
    </div>
</nav>

<!-- ── MAIN ──────────────────────────────────────────────────────────── -->
<main class="main" id="mainContent">

    <div class="page-head">
        <div>
            <h1 class="page-title">User Management</h1>
            <div class="breadcrumb-bar">
                <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard">Home</a>
                <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:0.6rem;"></i></span>
                <span>Users</span>
            </div>
        </div>
        <button type="button" class="btn-primary-action" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fas fa-user-plus"></i> Add User
        </button>
    </div>

    <?php if (!empty($success)): ?>
    <div class="alert-bar success fade-up" role="alert">
        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
        <button class="dismiss">&times;</button>
    </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
    <div class="alert-bar danger fade-up" role="alert">
        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        <button class="dismiss">&times;</button>
    </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-card fade-up fade-up-1">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div><div class="stat-label">Total Users</div><div class="stat-value"><?= $totalUsers ?? 0 ?></div></div>
        </div>
        <div class="stat-card c-success fade-up fade-up-2">
            <div class="stat-icon c-success"><i class="fas fa-user-check"></i></div>
            <div><div class="stat-label">Active</div><div class="stat-value"><?= $activeUsers ?? 0 ?></div></div>
        </div>
        <div class="stat-card c-info fade-up fade-up-3">
            <div class="stat-icon c-info"><i class="fas fa-cash-register"></i></div>
            <div><div class="stat-label">Cashiers</div><div class="stat-value"><?= $cashierUsers ?? 0 ?></div></div>
        </div>
        <div class="stat-card c-warning fade-up fade-up-4">
            <div class="stat-icon c-warning"><i class="fas fa-utensils"></i></div>
            <div><div class="stat-label">Kitchen Staff</div><div class="stat-value"><?= $kitchenUsers ?? 0 ?></div></div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Users Table -->
        <div class="col-lg-8">
            <div class="table-card fade-up">
                <div class="table-card-header">
                    <div class="table-card-title">
                        <i class="fas fa-list" style="color:var(--primary-light);"></i>
                        All Users
                        <span class="item-count-badge"><?= count($users ?? []) ?> users</span>
                    </div>
                    <div class="search-wrap">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Search users…" autocomplete="off">
                    </div>
                </div>
                <div style="overflow-x:auto;">
                    <?php if (empty($users)): ?>
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <p style="font-weight:600;color:var(--navy);margin-bottom:0.3rem;">No users found</p>
                        <p style="font-size:0.85rem;">Click "Add User" to get started.</p>
                    </div>
                    <?php else: ?>
                    <table class="data-table" id="usersTable">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th style="text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($users ?? [] as $user):
                            $isCurrentUser = ($user['user_id'] == ($_SESSION['user_id'] ?? 0));
                            $isActive      = (bool)$user['is_active'];
                            $initChar      = strtoupper(substr($user['full_name'], 0, 1));
                        ?>
                            <tr class="<?= !$isActive ? 'row-inactive' : '' ?>">
                                <td data-label="User">
                                    <div style="display:flex;align-items:center;gap:0.75rem;">
                                        <div class="user-avatar <?= !$isActive ? 'inactive' : '' ?>"><?= htmlspecialchars($initChar) ?></div>
                                        <div>
                                            <div style="font-weight:700;color:var(--navy);font-size:0.9rem;"><?= htmlspecialchars($user['full_name']) ?></div>
                                            <div style="font-size:0.75rem;color:var(--warm-gray);">@<?= htmlspecialchars($user['username']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Email" style="font-size:0.85rem;color:var(--warm-gray);">
                                    <?php if (!empty($user['email'])): ?>
                                        <i class="fas fa-envelope" style="color:var(--primary-light);margin-right:0.4rem;font-size:0.75rem;"></i><?= htmlspecialchars($user['email']) ?>
                                    <?php else: ?>
                                        <span style="opacity:0.4;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Role">
                                    <span class="pill pill-<?= strtolower($user['role']) ?>">
                                        <?= ucfirst(htmlspecialchars($user['role'])) ?>
                                    </span>
                                </td>
                                <td data-label="Status">
                                    <span class="pill <?= $isActive ? 'pill-active' : 'pill-inactive' ?>">
                                        <span class="dot"></span>
                                        <?= $isActive ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td data-label="Joined" style="font-size:0.8rem;color:var(--warm-gray);">
                                    <i class="far fa-calendar" style="margin-right:0.35rem;font-size:0.72rem;"></i>
                                    <?= date('M d, Y', strtotime($user['created_at'] ?? 'now')) ?>
                                </td>
                                <td data-label="Actions" style="text-align:right;">
                                    <div class="action-group">
                                        <!-- Edit -->
                                        <button type="button" class="btn-act btn-act-edit"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editModal<?= $user['user_id'] ?>"
                                                title="Edit user">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <?php if ($isCurrentUser): ?>
                                        <!-- Cannot toggle own account -->
                                        <button type="button" class="btn-act btn-act-self" title="Cannot change your own account status" disabled>
                                            <i class="fas fa-ban"></i>
                                        </button>
                                        <?php elseif ($isActive): ?>
                                        <!-- Deactivate -->
                                        <button type="button" class="btn-act btn-act-deactivate"
                                                data-bs-toggle="modal"
                                                data-bs-target="#toggleModal<?= $user['user_id'] ?>"
                                                title="Deactivate user">
                                            <i class="fas fa-user-slash"></i>
                                        </button>
                                        <?php else: ?>
                                        <!-- Activate -->
                                        <button type="button" class="btn-act btn-act-activate"
                                                data-bs-toggle="modal"
                                                data-bs-target="#toggleModal<?= $user['user_id'] ?>"
                                                title="Activate user">
                                            <i class="fas fa-user-check"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Role Info Card -->
        <div class="col-lg-4">
            <div class="info-card fade-up">
                <div class="info-card-head"><i class="fas fa-info-circle"></i> Role Permissions</div>
                <div class="info-card-body">
                    <div class="perm-row">
                        <span class="pill pill-admin" style="min-width:80px;justify-content:center;">Admin</span>
                        <span class="perm-desc">Full system access — users, reports, menu, all settings.</span>
                    </div>
                    <div class="perm-row">
                        <span class="pill pill-cashier" style="min-width:80px;justify-content:center;">Cashier</span>
                        <span class="perm-desc">Process sales, manage tables, view own shift reports.</span>
                    </div>
                    <div class="perm-row">
                        <span class="pill pill-kitchen" style="min-width:80px;justify-content:center;">Kitchen</span>
                        <span class="perm-desc">View incoming orders and update preparation status.</span>
                    </div>
                    <div style="margin-top:1.1rem;padding-top:1rem;border-top:1px solid #edf2f7;">
                        <div style="display:flex;align-items:flex-start;gap:0.55rem;">
                            <i class="fas fa-shield-alt" style="color:var(--primary-light);margin-top:0.15rem;flex-shrink:0;font-size:0.85rem;"></i>
                            <div style="font-size:0.78rem;color:var(--warm-gray);">
                                <strong style="color:var(--navy);">No hard deletes.</strong> Users can only be deactivated to preserve audit history. Inactive users cannot log in.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ ADD USER MODAL ═══════════════════════════════════════════ -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-plus" style="color:var(--secondary);font-size:1rem;"></i> Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="addUserForm">
                    <div class="modal-body">
                        <input type="hidden" name="add_user" value="1">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Username <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" name="username" placeholder="e.g. jdelacruz" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                    <input type="text" class="form-control" name="full_name" placeholder="e.g. Juan Dela Cruz" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" name="email" placeholder="user@example.com">
                            </div>
                            <div class="form-text">Optional — used for account recovery.</div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="add_password" name="password" placeholder="Min. 6 characters" required>
                                    <button class="btn btn-outline-secondary" type="button" id="toggleAddPwd" tabindex="-1"><i class="fas fa-eye"></i></button>
                                </div>
                                <div class="form-text">At least 6 characters.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">User Role <span class="text-danger">*</span></label>
                                <select class="form-select" name="role" required>
                                    <option value="">Select role…</option>
                                    <option value="admin">Administrator</option>
                                    <option value="cashier">Cashier</option>
                                    <option value="kitchen">Kitchen Staff</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-modal-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-modal-primary"><i class="fas fa-user-plus me-1"></i> Create User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ═══ EDIT + TOGGLE MODALS per user ═══════════════════════════ -->
    <?php foreach ($users ?? [] as $user):
        $isActive = (bool)$user['is_active'];
    ?>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal<?= $user['user_id'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-edit" style="color:var(--warning);font-size:1rem;"></i> Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                        <input type="hidden" name="update_user" value="1">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Username <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                    <input type="text" class="form-control" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" placeholder="user@example.com">
                            </div>
                            <div class="form-text">Optional.</div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">User Role <span class="text-danger">*</span></label>
                                <select class="form-select" name="role" required>
                                    <option value="">Select role…</option>
                                    <option value="admin"   <?= $user['role']==='admin'   ? 'selected':'' ?>>Administrator</option>
                                    <option value="cashier" <?= $user['role']==='cashier' ? 'selected':'' ?>>Cashier</option>
                                    <option value="kitchen" <?= $user['role']==='kitchen' ? 'selected':'' ?>>Kitchen Staff</option>
                                </select>
                            </div>
                            
                            <?php
$currentUserId = $_SESSION['user_id']; // logged-in admin
$isSelf = ($user['user_id'] == $currentUserId);
?>

<div class="col-md-6 d-flex align-items-end pb-1">
    <div>
        <div class="form-check form-switch">
            <input 
                class="form-check-input" 
                type="checkbox" 
                id="ea_<?= $user['user_id'] ?>" 
                name="is_active" 
                value="1"
                <?= $isActive ? 'checked' : '' ?>
                <?= $isSelf ? 'disabled' : '' ?>
            >
            <label class="form-check-label" for="ea_<?= $user['user_id'] ?>" style="font-size:0.85rem;font-weight:600;">
                Account Active
            </label>
        </div>

        <?php if ($isSelf): ?>
            <div class="form-text text-danger">
                You cannot deactivate your own account.
            </div>
        <?php else: ?>
            <div class="form-text">
                Inactive users cannot log in.
            </div>
        <?php endif; ?>
    </div>
</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password <span style="font-weight:400;color:var(--warm-gray);">(optional)</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="ep_<?= $user['user_id'] ?>" name="password" placeholder="Leave blank to keep current">
                                <button class="btn btn-outline-secondary" type="button" data-toggle-pw="ep_<?= $user['user_id'] ?>" tabindex="-1"><i class="fas fa-eye"></i></button>
                            </div>
                            <div class="form-text">Min. 6 characters. Leave empty to keep current password.</div>
                        </div>
                        <div style="font-size:0.75rem;color:var(--warm-gray);">
                            <i class="far fa-clock me-1"></i> Member since: <?= date('M d, Y', strtotime($user['created_at'] ?? 'now')) ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-modal-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-modal-warning"><i class="fas fa-save me-1"></i> Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Activate / Deactivate Confirm Modal -->
    <div class="modal fade" id="toggleModal<?= $user['user_id'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
            <div class="modal-content">
                <div class="modal-header" style="border-bottom:none;padding-bottom:0;">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center" style="padding:1rem 1.6rem 1.25rem;">
                    <div class="confirm-icon <?= $isActive ? 'danger' : 'success' ?>">
                        <i class="fas <?= $isActive ? 'fa-user-slash' : 'fa-user-check' ?>"></i>
                    </div>
                    <h5 style="font-family:'Fraunces',serif;font-weight:700;color:var(--navy);margin-bottom:0.5rem;">
                        <?= $isActive ? 'Deactivate User?' : 'Activate User?' ?>
                    </h5>
                    <p style="font-size:0.85rem;color:var(--warm-gray);margin-bottom:0.5rem;">
                        <?= $isActive ? 'You are about to deactivate:' : 'You are about to activate:' ?>
                    </p>
                    <p style="font-weight:700;font-size:1rem;color:var(--navy);margin-bottom:0.75rem;">
                        <?= htmlspecialchars($user['full_name']) ?>
                        <span style="font-weight:400;color:var(--warm-gray);font-size:0.85rem;">(@<?= htmlspecialchars($user['username']) ?>)</span>
                    </p>
                    <div class="confirm-note <?= $isActive ? 'danger' : 'success' ?>">
                        <i class="fas <?= $isActive ? 'fa-exclamation-triangle' : 'fa-info-circle' ?> me-1"></i>
                        <?= $isActive
                            ? 'The user will immediately lose access and cannot log in. Their data and history are preserved.'
                            : 'The user will regain access and can log in with their existing credentials.' ?>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:none;">
                    <button type="button" class="btn-modal-secondary" style="flex:1;" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" style="flex:1;">
                        <input type="hidden" name="toggle_user" value="1">
                        <input type="hidden" name="user_id"     value="<?= $user['user_id'] ?>">
                        <input type="hidden" name="is_active"   value="<?= $isActive ? '0' : '1' ?>">
                        <?php if ($isActive): ?>
                        <button type="submit" class="btn-toggle-deactivate"><i class="fas fa-user-slash me-1"></i> Deactivate</button>
                        <?php else: ?>
                        <button type="submit" class="btn-toggle-activate"><i class="fas fa-user-check me-1"></i> Activate</button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php endforeach; ?>

    <!-- Auto-open edit modal if $user_to_edit is set -->
    <?php if (isset($user_to_edit) && !empty($user_to_edit)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new bootstrap.Modal(document.getElementById('editModal<?= $user_to_edit['user_id'] ?>')).show();
        });
    </script>
    <?php endif; ?>

</main>

<script src="<?= $baseUrl ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
/* ── Sidebar ──────────────────────────────────────────────────── */
document.getElementById('menuToggle')?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('show');
    document.getElementById('sidebarOverlay').classList.toggle('show');
});
document.getElementById('sidebarOverlay')?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.remove('show');
    document.getElementById('sidebarOverlay').classList.remove('show');
});

/* ── Alert dismiss ────────────────────────────────────────────── */
document.querySelectorAll('.alert-bar .dismiss').forEach(btn => {
    btn.addEventListener('click', () => btn.closest('.alert-bar').remove());
});
setTimeout(() => {
    document.querySelectorAll('.alert-bar').forEach(a => { a.style.opacity = '0'; a.style.transition = 'opacity .4s'; });
    setTimeout(() => document.querySelectorAll('.alert-bar').forEach(a => a.remove()), 400);
}, 5000);

/* ── Search ───────────────────────────────────────────────────── */
document.getElementById('searchInput')?.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#usersTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});

/* ── Password toggles ─────────────────────────────────────────── */
document.getElementById('toggleAddPwd')?.addEventListener('click', function() {
    const inp = document.getElementById('add_password');
    inp.type = inp.type === 'password' ? 'text' : 'password';
    this.querySelector('i').classList.toggle('fa-eye');
    this.querySelector('i').classList.toggle('fa-eye-slash');
});
document.querySelectorAll('[data-toggle-pw]').forEach(btn => {
    btn.addEventListener('click', function() {
        const inp = document.getElementById(this.dataset.togglePw);
        inp.type = inp.type === 'password' ? 'text' : 'password';
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });
});

/* ── Add user form validation ─────────────────────────────────── */
document.getElementById('addUserForm')?.addEventListener('submit', function(e) {
    const email = this.querySelector('[name="email"]').value;
    if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        e.preventDefault(); alert('Please enter a valid email address.'); return;
    }
    const pwd = this.querySelector('[name="password"]').value;
    if (pwd.length < 6) {
        e.preventDefault(); alert('Password must be at least 6 characters.'); return;
    }
});

/* ── Modal backdrop cleanup ───────────────────────────────────── */
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