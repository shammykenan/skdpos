<?php
// Pure view: expects $addons, $ingredients, $_SESSION['success'], $_SESSION['error']
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5">
    <title>Add-ons Management — Dine with Din</title>
    <link rel="icon" type="image/png" href="<?= $baseUrl ?>/assets/images/logo/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fraunces:opsz,wght@9..144,700;9..144,900&display=swap" rel="stylesheet">

    <style>
        /* ── TOKENS ──────────────────────────────────────────────────────── */
        :root {
            --primary:       #0f4c5c;
            --primary-dark:  #0a3a47;
            --primary-light: #1f7a8c;
            --secondary:     #ff6b4a;
            --accent:        #fee440;
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

            --sidebar-w: 260px;
            --top-h:     64px;
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 18px;
            --radius-xl: 24px;
        }

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

        /* ── OVERLAY ──────────────────────────────────────────────────── */
        .sidebar-overlay {
            display: none; position: fixed; top: var(--top-h); left: 0; right: 0; bottom: 0;
            background: rgba(15,76,92,0.25); z-index: 1025; backdrop-filter: blur(2px);
        }
        .sidebar-overlay.show { display: block; }

        /* ── MAIN ──────────────────────────────────────────────────────── */
        .main { margin-left: var(--sidebar-w); margin-top: var(--top-h); padding: 2rem 1.75rem; min-height: calc(100vh - var(--top-h)); }

        /* ── PAGE HEADER ────────────────────────────────────────────────── */
        .page-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; margin-bottom: 1.75rem; flex-wrap: wrap; }
        .page-title { font-family: 'Fraunces', Georgia, serif; font-size: 1.75rem; font-weight: 700; color: var(--navy); line-height: 1.1; }
        .breadcrumb-bar { display: flex; align-items: center; gap: 0.4rem; font-size: 0.8rem; color: var(--warm-gray); margin-top: 0.3rem; }
        .breadcrumb-bar a { color: var(--primary-light); text-decoration: none; font-weight: 500; }
        .breadcrumb-bar a:hover { text-decoration: underline; }
        .breadcrumb-sep { color: var(--border); }

        /* ── HEADER ACTIONS ─────────────────────────────────────────────── */
        .head-actions { display: flex; gap: 0.6rem; flex-wrap: wrap; align-items: center; }
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

        /* ── ALERTS ──────────────────────────────────────────────────────── */
        .alert-bar {
            border-radius: var(--radius-md); padding: 0.85rem 1.2rem;
            display: flex; align-items: center; gap: 0.65rem;
            font-size: 0.88rem; font-weight: 500; margin-bottom: 1.5rem;
            transition: opacity 0.4s;
        }
        .alert-bar.success { background: var(--success-soft); color: #1e4a3b; border-left: 4px solid var(--success); }
        .alert-bar.danger  { background: var(--danger-soft);  color: #9e3a34; border-left: 4px solid var(--danger); }
        .alert-bar .dismiss { margin-left: auto; cursor: pointer; background: none; border: none; color: inherit; opacity: 0.5; font-size: 1.1rem; padding: 0; }
        .alert-bar .dismiss:hover { opacity: 1; }

        /* ── STATS GRID ──────────────────────────────────────────────────── */
        .stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.75rem; }
        .stat-card {
            background: #fff; border: 1px solid rgba(203,213,224,0.6);
            border-radius: var(--radius-lg); padding: 1.25rem 1.35rem;
            display: flex; align-items: center; gap: 1rem;
            transition: box-shadow 0.2s, transform 0.2s;
            position: relative; overflow: hidden;
        }
        .stat-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: var(--primary); }
        .stat-card.c-success::before { background: var(--success); }
        .stat-card.c-info::before    { background: var(--info); }
        .stat-card.c-warning::before { background: var(--warning); }
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
        .stat-label { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: var(--warm-gray); margin-bottom: 0.2rem; }
        .stat-value { font-size: 1.65rem; font-weight: 800; color: var(--navy); line-height: 1; }

        /* ── TABLE CARD ──────────────────────────────────────────────────── */
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
        .item-count-badge {
            background: rgba(15,76,92,0.08); color: var(--primary-dark);
            font-size: 0.7rem; font-weight: 700; padding: 0.2rem 0.65rem; border-radius: 20px;
        }

        /* ── FILTER + SEARCH ROW ─────────────────────────────────────────── */
        .filter-search-row {
            padding: 0.75rem 1.5rem; border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 0.75rem;
            background: #fafcff;
        }
        .filter-tabs { display: flex; gap: 0.4rem; flex-wrap: wrap; }
        .filter-tab {
            display: inline-flex; align-items: center; gap: 0.35rem;
            padding: 0.35rem 0.9rem; border-radius: 30px;
            border: 1.5px solid var(--border); background: #fff;
            font-size: 0.78rem; font-weight: 600; color: var(--warm-gray);
            cursor: pointer; transition: all 0.15s;
        }
        .filter-tab:hover { border-color: var(--primary); color: var(--primary); }
        .filter-tab.active { background: var(--primary); border-color: var(--primary); color: #fff; }
        .tab-count {
            background: rgba(255,255,255,0.25); border-radius: 20px;
            padding: 0.05rem 0.45rem; font-size: 0.7rem;
        }
        .filter-tab:not(.active) .tab-count { background: var(--lavender); color: var(--navy); }

        /* ── SEARCH BOX ──────────────────────────────────────────────────── */
        .search-box { position: relative; }
        .search-box i { position: absolute; left: 0.85rem; top: 50%; transform: translateY(-50%); color: var(--warm-gray); font-size: 0.8rem; pointer-events: none; z-index: 1; }
        .search-box input {
            padding: 0.45rem 0.9rem 0.45rem 2.3rem; border-radius: 30px;
            border: 1.5px solid var(--border); font-size: 0.82rem; width: 200px;
            outline: none; transition: border-color 0.15s, width 0.3s; background: #fff;
            font-family: inherit;
        }
        .search-box input:focus { border-color: var(--primary); width: 240px; }

        /* ── DATA TABLE ──────────────────────────────────────────────────── */
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
            padding: 1rem 1.25rem; border-bottom: 1px solid #edf2f7;
            vertical-align: middle; color: var(--dark); background: #fff;
        }
        .data-table tbody tr:last-child td { border-bottom: none; }
        .data-table tbody tr:hover td { background: #fafcff; }
        .data-table tbody tr.row-inactive td { opacity: 0.45; }
        .data-table tbody tr.row-inactive:hover td { opacity: 0.7; }

        /* ── ADDON NAME CELL ─────────────────────────────────────────────── */
        .addon-name { font-weight: 700; font-size: 0.9rem; color: var(--navy); }
        .addon-desc { font-size: 0.78rem; color: var(--warm-gray); margin-top: 2px; }

        /* ── PILLS ───────────────────────────────────────────────────────── */
        .pill { display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.72rem; font-weight: 700; padding: 0.3rem 0.75rem; border-radius: 30px; white-space: nowrap; }
        .pill-active   { background: rgba(44,122,90,0.12); color: var(--success); }
        .pill-inactive { background: rgba(100,116,139,0.12); color: var(--warm-gray); }
        .pill-ingredient { background: rgba(15,76,92,0.08); color: var(--primary-dark); }
        .dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; flex-shrink: 0; }

        /* ── PRICE CELL ──────────────────────────────────────────────────── */
        .price-main { font-size: 0.95rem; font-weight: 700; color: var(--success); }
        .qty-sub    { font-size: 0.72rem; color: var(--warm-gray); margin-top: 1px; }

        /* ── ACTION BUTTONS ──────────────────────────────────────────────── */
        .action-group { display: flex; gap: 0.4rem; justify-content: flex-end; }
        .btn-act { width: 34px; height: 34px; border-radius: var(--radius-sm); display: inline-flex; align-items: center; justify-content: center; border: none; cursor: pointer; font-size: 0.8rem; transition: all 0.15s; text-decoration: none; }
        .btn-act-edit { background: #fff3e0; color: #b85a1a; border: 1px solid rgba(244,185,66,0.2); }
        .btn-act-edit:hover  { background: var(--warning); color: #fff; }
        .btn-act-del  { background: rgba(214,64,69,0.08); color: var(--danger); border: 1px solid rgba(214,64,69,0.15); }
        .btn-act-del:hover   { background: var(--danger); color: #fff; }

        /* ── EMPTY STATE ─────────────────────────────────────────────────── */
        .empty-state { text-align: center; padding: 4rem 1.5rem; }
        .empty-state i { font-size: 3.5rem; color: #d0d8e4; display: block; margin-bottom: 1rem; }
        .empty-state h5 { color: var(--warm-gray); margin-bottom: 0.4rem; font-weight: 700; }
        .empty-state p  { color: #b0bec5; font-size: 0.88rem; }

        /* ── MODALS ──────────────────────────────────────────────────────── */
        .modal-content { border: none; border-radius: var(--radius-xl); box-shadow: 0 24px 60px rgba(0,0,0,0.12); }
        .modal-header { border-bottom: 1px solid var(--border); padding: 1.4rem 1.6rem; border-radius: var(--radius-xl) var(--radius-xl) 0 0; }
        .modal-title { font-family: 'Fraunces', Georgia, serif; font-size: 1.15rem; font-weight: 700; color: var(--navy); display: flex; align-items: center; gap: 0.5rem; }
        .modal-body  { padding: 1.5rem 1.6rem; }
        .modal-footer { border-top: 1px solid var(--border); padding: 1.2rem 1.6rem; border-radius: 0 0 var(--radius-xl) var(--radius-xl); gap: 0.5rem; }

        .form-label { font-size: 0.82rem; font-weight: 700; color: var(--navy); margin-bottom: 0.4rem; display: block; }
        .form-control, .form-select {
            border: 1px solid var(--border); border-radius: var(--radius-md);
            padding: 0.6rem 0.9rem; font-size: 0.88rem; font-family: inherit;
            background: #fff; color: var(--dark); width: 100%;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .form-control:focus, .form-select:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(15,76,92,0.12); }
        .form-control::placeholder { color: #a0aec0; }
        .input-group-text { background: #f2f6fc; border: 1px solid var(--border); border-radius: var(--radius-md) 0 0 var(--radius-md); padding: 0.6rem 0.8rem; color: var(--primary-dark); font-weight: 600; font-size: 0.85rem; }
        .input-group .form-control, .input-group .form-select { border-radius: 0 var(--radius-md) var(--radius-md) 0; }
        .input-group .form-control:focus, .input-group .form-select:focus { border-left-color: var(--primary); }
        .form-check-input:checked { background-color: var(--primary); border-color: var(--primary); }
        .form-switch .form-check-input { width: 2.25em; }
        .form-text { font-size: 0.75rem; color: var(--warm-gray); margin-top: 0.25rem; }

        /* Modal buttons */
        .btn-modal-primary { background: var(--primary); color: #fff; border: none; padding: 0.6rem 1.4rem; border-radius: 40px; font-weight: 600; font-size: 0.88rem; cursor: pointer; transition: background 0.15s; display: inline-flex; align-items: center; gap: 0.4rem; }
        .btn-modal-primary:hover { background: var(--primary-dark); color: #fff; }
        .btn-modal-secondary { background: #fff; color: var(--dark); border: 1px solid var(--border); padding: 0.6rem 1.4rem; border-radius: 40px; font-weight: 600; font-size: 0.88rem; cursor: pointer; transition: background 0.15s; }
        .btn-modal-secondary:hover { background: var(--light-bg); }
        .btn-modal-warning { background: var(--warning); color: #fff; border: none; padding: 0.6rem 1.4rem; border-radius: 40px; font-weight: 600; font-size: 0.88rem; cursor: pointer; transition: background 0.15s; display: inline-flex; align-items: center; gap: 0.4rem; }
        .btn-modal-warning:hover { background: #dda030; color: #fff; }
        .btn-danger-solid { background: var(--danger); color: #fff; border: none; padding: 0.6rem 1.4rem; border-radius: 40px; font-weight: 600; font-size: 0.88rem; cursor: pointer; transition: background 0.15s; display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; width: 100%; }
        .btn-danger-solid:hover { background: #bb3338; color: #fff; }

        /* Confirm modal */
        .confirm-icon { width: 64px; height: 64px; border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; font-size: 1.75rem; margin: 0 auto 1rem; }
        .confirm-icon.danger { background: rgba(214,64,69,0.1); color: var(--danger); }
        .confirm-note { background: rgba(214,64,69,0.06); border: 1px dashed rgba(214,64,69,0.3); border-radius: var(--radius-md); padding: 0.75rem 1rem; font-size: 0.78rem; color: #9e3a34; }

        /* Info note in modal */
        .info-note { background: rgba(15,76,92,0.06); border: 1px solid rgba(15,76,92,0.15); border-radius: var(--radius-md); padding: 0.7rem 1rem; font-size: 0.8rem; color: var(--primary-dark); display: flex; gap: 0.5rem; align-items: flex-start; }
        .info-note i { margin-top: 2px; flex-shrink: 0; color: var(--primary-light); }

        /* ── ASSIGN MODAL — checkbox list ─────────────────────────────────── */
        .addon-checklist { max-height: 280px; overflow-y: auto; border: 1px solid var(--border); border-radius: var(--radius-md); }
        .addon-checklist::-webkit-scrollbar { width: 4px; }
        .addon-checklist::-webkit-scrollbar-thumb { background: var(--primary-light); border-radius: 4px; }
        .addon-check-item { display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.8rem 1rem; border-bottom: 1px solid #edf2f7; transition: background 0.12s; cursor: pointer; }
        .addon-check-item:last-child { border-bottom: none; }
        .addon-check-item:hover { background: #fafcff; }
        .addon-check-item input[type="checkbox"] { margin-top: 3px; flex-shrink: 0; width: 16px; height: 16px; accent-color: var(--primary); cursor: pointer; }
        .addon-check-item label { cursor: pointer; flex: 1; }
        .addon-check-name  { font-weight: 700; font-size: 0.88rem; color: var(--navy); }
        .addon-check-meta  { font-size: 0.76rem; color: var(--warm-gray); margin-top: 2px; }

        /* ── DROPDOWN ───────────────────────────────────────────────────── */
        .dropdown-menu { border-radius: var(--radius-lg); border: 1px solid var(--border); box-shadow: 0 12px 30px rgba(0,0,0,0.08); padding: 0.5rem; }
        .dropdown-item { border-radius: var(--radius-sm); font-size: 0.88rem; padding: 0.55rem 1rem; }
        .dropdown-item:hover { background: var(--lavender); color: var(--primary-dark); }
        .dropdown-item.text-danger:hover { background: rgba(214,64,69,0.08); color: var(--danger); }

        /* ── RESPONSIVE ─────────────────────────────────────────────────── */
        @media (max-width: 1199.98px) {
            .stats-row { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main { margin-left: 0; padding: 1.5rem 1.25rem; }
            .mobile-toggle { display: flex; align-items: center; }
        }
        /* iPad-specific (768–1024) */
        @media (min-width: 768px) and (max-width: 1024px) {
            :root { --sidebar-w: 220px; }
            .main { padding: 1.5rem; }
            .stats-row { grid-template-columns: repeat(2, 1fr); }
            .data-table thead th, .data-table tbody td { padding: 0.8rem 1rem; }
            .search-box input { width: 170px; }
            .search-box input:focus { width: 200px; }
        }
        @media (max-width: 767.98px) {
            .main { padding: 1.25rem 1rem; }
            .stats-row { grid-template-columns: repeat(2, 1fr); gap: 0.75rem; }
            .page-title { font-size: 1.4rem; }
            .head-actions { width: 100%; }
            .btn-primary-action, .btn-secondary-action { flex: 1; justify-content: center; }
            .search-box input { width: 100%; }
            .search-box { flex: 1; }
            /* Card-stack table */
            .data-table thead { display: none; }
            .data-table tbody tr { display: block; border: 1px solid var(--border); border-radius: var(--radius-lg); margin-bottom: 1rem; background: #fff; overflow: hidden; }
            .data-table tbody tr.row-inactive { opacity: 0.55; }
            .data-table tbody td { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid #edf2f7; padding: 0.75rem 1rem; }
            .data-table tbody td:last-child { border-bottom: none; }
            .data-table tbody td::before { content: attr(data-label); font-weight: 700; color: var(--warm-gray); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; flex-shrink: 0; margin-right: 0.5rem; padding-top: 2px; }
            .table-card { border-radius: var(--radius-lg); }
            .action-group { justify-content: flex-end; }
            .filter-search-row { flex-direction: column; align-items: stretch; }
            .modal-dialog { margin: 0.5rem; }
        }
        @media (max-width: 575.98px) {
            .brand-text-sub { display: none; }
            .topbar { padding: 0 1rem; }
            .stat-card { padding: 1rem; gap: 0.75rem; }
            .stat-value { font-size: 1.35rem; }
        }

        /* ── Animations ─────────────────────────────────────────────────── */
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

<!-- ── TOPBAR ─────────────────────────────────────────────────────────────── -->
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
            <div class="brand-text-sub">Web-Based POS Management System</div>
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
            <li><a class="dropdown-item text-danger" href="<?= $baseUrl ?>/index.php?page=logout">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
            </a></li>
        </ul>
    </div>
</header>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ── SIDEBAR ────────────────────────────────────────────────────────────── -->
<nav class="sidebar" id="sidebar" aria-label="Main navigation">
    <div class="nav-wrap">
        <div class="nav-section">Dashboard</div>
        <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>

        <div class="nav-section">Management</div>
        <a href="<?= $baseUrl ?>/index.php?page=manage_menu"        class="nav-link"><i class="fas fa-utensils"></i> Menu Items</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_recipes"     class="nav-link"><i class="fas fa-tags"></i> Recipes</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_ingredients" class="nav-link"><i class="fas fa-boxes"></i> Ingredients</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_addons"      class="nav-link active"><i class="fas fa-plus-circle"></i> Add-ons</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_tables"      class="nav-link"><i class="fas fa-chair"></i> Tables</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_users"       class="nav-link"><i class="fas fa-users"></i> User Management</a>

        <div class="nav-section">Sales</div>
        <a href="<?= $baseUrl ?>/index.php?page=manage_pos"    class="nav-link"><i class="fas fa-cash-register"></i> POS System</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_orders" class="nav-link"><i class="fas fa-receipt"></i> Orders</a>
        <a href="<?= $baseUrl ?>/index.php?page=reports"       class="nav-link"><i class="fas fa-chart-bar"></i> Reports</a>
    </div>
</nav>

<!-- ── MAIN ───────────────────────────────────────────────────────────────── -->
<main class="main" id="mainContent">

    <div class="page-head">
        <div>
            <h1 class="page-title">Add-ons Management</h1>
            <div class="breadcrumb-bar">
                <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard">Home</a>
                <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:0.6rem;"></i></span>
                <span>Add-ons</span>
            </div>
        </div>
        <div class="head-actions">
            <button type="button" class="btn-secondary-action" data-bs-toggle="modal" data-bs-target="#assignAddonModal">
                <i class="fas fa-link"></i> Assign Add-ons
            </button>
            <button type="button" class="btn-primary-action" data-bs-toggle="modal" data-bs-target="#addAddonModal">
                <i class="fas fa-plus"></i> Add Add-on
            </button>
        </div>
    </div>

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

    <!-- Stats -->
    <?php
        $totalCount    = count($addons ?? []);
        $activeCount   = count(array_filter($addons ?? [], fn($a) => $a['is_active']));
        $inactiveCount = $totalCount - $activeCount;
        $uniqueIngr    = count(array_unique(array_column($addons ?? [], 'ingredient_name')));
    ?>
    <div class="stats-row">
        <div class="stat-card fade-up fade-up-1">
            <div class="stat-icon"><i class="fas fa-plus-circle"></i></div>
            <div><div class="stat-label">Total Add-ons</div><div class="stat-value"><?= $totalCount ?></div></div>
        </div>
        <div class="stat-card c-success fade-up fade-up-2">
            <div class="stat-icon c-success"><i class="fas fa-check-circle"></i></div>
            <div><div class="stat-label">Active</div><div class="stat-value"><?= $activeCount ?></div></div>
        </div>
        <div class="stat-card c-warning fade-up fade-up-3">
            <div class="stat-icon c-warning"><i class="fas fa-eye-slash"></i></div>
            <div><div class="stat-label">Inactive</div><div class="stat-value"><?= $inactiveCount ?></div></div>
        </div>
        <div class="stat-card c-info fade-up fade-up-4">
            <div class="stat-icon c-info"><i class="fas fa-boxes"></i></div>
            <div><div class="stat-label">Ingredients Used</div><div class="stat-value"><?= $uniqueIngr ?></div></div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="table-card fade-up fade-up-5">
        <div class="table-card-header">
            <div class="table-card-title">
                <i class="fas fa-list" style="color:var(--primary-light);"></i>
                All Add-ons
                <span class="item-count-badge"><?= $totalCount ?> total</span>
            </div>
        </div>

        <!-- Filter + Search -->
        <div class="filter-search-row">
            <div class="filter-tabs">
                <button type="button" class="filter-tab active" data-filter="all">
                    All <span class="tab-count"><?= $totalCount ?></span>
                </button>
                <button type="button" class="filter-tab" data-filter="active">
                    <span class="dot" style="background:var(--success);"></span>
                    Active <span class="tab-count"><?= $activeCount ?></span>
                </button>
                <button type="button" class="filter-tab" data-filter="inactive">
                    <span class="dot" style="background:var(--warm-gray);"></span>
                    Inactive <span class="tab-count"><?= $inactiveCount ?></span>
                </button>
            </div>
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search add-ons…">
            </div>
        </div>

        <div style="overflow-x:auto;">
            <?php if (empty($addons)): ?>
                <div class="empty-state">
                    <i class="fas fa-plus-circle"></i>
                    <h5>No add-ons yet</h5>
                    <p>Click "Add Add-on" to create your first one.</p>
                </div>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Add-on</th>
                            <th>Price</th>
                            <th>Ingredient</th>
                            <th>Qty Used</th>
                            <th>Status</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($addons as $a): ?>
                        <tr class="<?= $a['is_active'] ? '' : 'row-inactive' ?>"
                            data-status="<?= $a['is_active'] ? 'active' : 'inactive' ?>">

                            <td data-label="Add-on">
                                <div class="addon-name"><?= htmlspecialchars($a['addon_name']) ?></div>
                            </td>

                            <td data-label="Price">
                                <div class="price-main">₱<?= number_format($a['price'], 2) ?></div>
                            </td>

                            <td data-label="Ingredient">
                                <span class="pill pill-ingredient">
                                    <i class="fas fa-box" style="font-size:0.6rem;opacity:0.7;"></i>
                                    <?= htmlspecialchars($a['ingredient_name']) ?>
                                </span>
                            </td>

                            <td data-label="Qty Used">
                                <div style="font-weight:700;font-size:0.9rem;color:var(--navy);"><?= htmlspecialchars($a['quantity_used']) ?></div>
                                <div class="qty-sub"><?= htmlspecialchars($a['unit_code']) ?></div>
                            </td>

                            <td data-label="Status">
                                <span class="pill <?= $a['is_active'] ? 'pill-active' : 'pill-inactive' ?>">
                                    <span class="dot"></span>
                                    <?= $a['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>

                            <td data-label="Actions" style="text-align:right;">
                                <div class="action-group">
                                    <button type="button" class="btn-act btn-act-edit"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editModal<?= $a['addon_id'] ?>"
                                            title="Edit"><i class="fas fa-edit"></i></button>
                                    <button type="button" class="btn-act btn-act-del"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteModal<?= $a['addon_id'] ?>"
                                            title="Remove"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

</main>


<!-- ══════════════════════════════════════════
     ADD ADD-ON MODAL
══════════════════════════════════════════ -->
<div class="modal fade" id="addAddonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle" style="color:var(--secondary);font-size:1rem;"></i>
                    Add New Add-on
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="index.php?page=manage_addons">
                <div class="modal-body">
                    <input type="hidden" name="add_addon" value="1">

                    <div class="mb-3">
                        <label class="form-label">Add-on Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-tag"></i></span>
                            <input type="text" class="form-control" name="addon_name"
                                   placeholder="e.g., Extra Shot" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control" name="price"
                                       step="0.01" min="0" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Quantity Used <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="quantity_used"
                                       step="0.01" min="0" placeholder="0.00" required>
                                <span class="input-group-text" id="addUnitText">Unit</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ingredient <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-box"></i></span>
                            <select class="form-select" name="ingredient_id" required>
                                <option value="">— Select ingredient —</option>
                                <?php foreach ($ingredients as $ing): ?>
                                    <option value="<?= $ing['ingredient_id'] ?>">
                                        <?= htmlspecialchars($ing['ingredient_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-text">The ingredient this add-on consumes when ordered.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-modal-primary">
                        <i class="fas fa-plus"></i> Add Add-on
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- ══════════════════════════════════════════
     EDIT & DELETE MODALS (per add-on)
══════════════════════════════════════════ -->
<?php foreach ($addons as $a): ?>

<!-- EDIT MODAL -->
<div class="modal fade" id="editModal<?= $a['addon_id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit" style="color:var(--warning);font-size:1rem;"></i>
                    Edit Add-on
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="index.php?page=manage_addons">
                <div class="modal-body">
                    <input type="hidden" name="edit_addon" value="1">
                    <input type="hidden" name="addon_id"   value="<?= $a['addon_id'] ?>">

                    <div class="mb-3">
                        <label class="form-label">Add-on Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-tag"></i></span>
                            <input type="text" class="form-control" name="addon_name"
                                   value="<?= htmlspecialchars($a['addon_name']) ?>" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control" name="price" step="0.01"
                                       value="<?= htmlspecialchars($a['price']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Quantity Used <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="quantity_used" step="0.01"
                                       value="<?= htmlspecialchars($a['quantity_used']) ?>" required>
                                <span class="input-group-text" id="editUnitText<?= $a['addon_id'] ?>"><?= htmlspecialchars($a['unit_code']) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ingredient <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-box"></i></span>
                            <select class="form-select" name="ingredient_id" required>
                                <option value="">— Select ingredient —</option>
                                <?php foreach ($ingredients as $ing): ?>
                                    <option value="<?= $ing['ingredient_id'] ?>"
                                        <?= $ing['ingredient_id'] == $a['ingredient_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($ing['ingredient_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Status</label>
                        <div class="form-check form-switch" style="margin-top:0.2rem;">
                            <input class="form-check-input" type="checkbox" name="is_active"
                                   id="edit_active_<?= $a['addon_id'] ?>"
                                   value="1" <?= $a['is_active'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="edit_active_<?= $a['addon_id'] ?>"
                                   style="font-weight:500;color:var(--warm-gray);font-size:0.88rem;">
                                Active — visible in POS
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-modal-warning">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- DELETE MODAL -->
<div class="modal fade" id="deleteModal<?= $a['addon_id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom:none;padding-bottom:0;">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" style="padding:1rem 1.6rem 1.25rem;">
                <div class="confirm-icon danger"><i class="fas fa-trash-alt"></i></div>
                <h5 style="font-family:'Fraunces',serif;font-weight:700;color:var(--navy);margin-bottom:0.5rem;">Remove Add-on?</h5>
                <p style="font-size:0.85rem;color:var(--warm-gray);margin-bottom:0.5rem;">You're about to remove:</p>
                <p style="color:var(--danger);font-weight:700;font-size:1rem;margin-bottom:1rem;">
                    "<?= htmlspecialchars($a['addon_name']) ?>"
                </p>
                <div class="confirm-note">
                    <i class="fas fa-info-circle me-1"></i>
                    If this add-on has been used in past orders it will be <strong>deactivated</strong> (hidden, not deleted) to keep your order history intact. Otherwise it is permanently removed.
                </div>
            </div>
            <div class="modal-footer" style="border-top:none;gap:0.5rem;">
                <button type="button" class="btn-modal-secondary" style="flex:1;" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="index.php?page=manage_addons" style="flex:1;margin:0;">
                    <input type="hidden" name="delete_addon" value="1">
                    <input type="hidden" name="addon_id"     value="<?= $a['addon_id'] ?>">
                    <button type="submit" class="btn-danger-solid">
                        <i class="fas fa-eye-slash"></i> Yes, Remove
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php endforeach; ?>


<!-- ══════════════════════════════════════════
     ASSIGN ADD-ONS MODAL
══════════════════════════════════════════ -->
<div class="modal fade" id="assignAddonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-link" style="color:var(--secondary);font-size:1rem;"></i>
                    Assign Add-ons to Products
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="index.php?page=manage_addons">
                <div class="modal-body">
                    <input type="hidden" name="assign_addons" value="1">

                    <div class="mb-4">
                        <label class="form-label">Select Product <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-utensils"></i></span>
                            <select class="form-select" name="item_id" id="productSelect" required>
                                <option value="">— Select a product —</option>
                                <?php
                                if (isset($menu_items) && !empty($menu_items)):
                                    foreach ($menu_items as $item):
                                ?>
                                    <option value="<?= $item['item_id'] ?>">
                                        <?= htmlspecialchars($item['item_name']) ?>
                                    </option>
                                <?php
                                    endforeach;
                                endif;
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Available Add-ons</label>
                        <div class="info-note mb-2">
                            <i class="fas fa-info-circle"></i>
                            <span>Select the add-ons that should appear in the POS for this product.</span>
                        </div>
                        <div class="addon-checklist">
                            <?php if (isset($addons) && !empty($addons)): ?>
                                <?php foreach ($addons as $addon): ?>
                                    <?php if ($addon['is_active']): ?>
                                        <div class="addon-check-item">
                                            <input class="addon-checkbox" type="checkbox"
                                                   name="selected_addons[]"
                                                   value="<?= $addon['addon_id'] ?>"
                                                   id="addon_<?= $addon['addon_id'] ?>">
                                            <label for="addon_<?= $addon['addon_id'] ?>">
                                                <div class="addon-check-name"><?= htmlspecialchars($addon['addon_name']) ?> <span style="color:var(--success);font-weight:700;">+₱<?= number_format($addon['price'], 2) ?></span></div>
                                                <div class="addon-check-meta">
                                                    <i class="fas fa-box"></i>
                                                    <?= htmlspecialchars($addon['ingredient_name']) ?> &nbsp;·&nbsp;
                                                    Qty: <?= htmlspecialchars($addon['quantity_used']) ?> <?= htmlspecialchars($addon['unit_code']) ?>
                                                </div>
                                            </label>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div style="padding:1.5rem;text-align:center;font-size:0.85rem;color:var(--warm-gray);">
                                    No active add-ons available. Please create add-ons first.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-modal-primary">
                        <i class="fas fa-save"></i> Save Assignments
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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

/* ── Alert dismiss ─────────────────────────────────────────────────── */
document.querySelectorAll('.alert-bar .dismiss').forEach(btn => {
    btn.addEventListener('click', () => btn.closest('.alert-bar').remove());
});
setTimeout(() => {
    document.querySelectorAll('.alert-bar').forEach(a => { a.style.opacity = '0'; });
    setTimeout(() => document.querySelectorAll('.alert-bar').forEach(a => a.remove()), 400);
}, 6000);

/* ── Filter tabs ───────────────────────────────────────────────────── */
let currentFilter = 'all';
let currentSearch = '';

function applyFilters() {
    document.querySelectorAll('table tbody tr').forEach(row => {
        const status      = row.getAttribute('data-status');
        const text        = row.textContent.toLowerCase();
        const matchFilter = currentFilter === 'all' || status === currentFilter;
        const matchSearch = currentSearch === '' || text.includes(currentSearch);
        row.style.display = (matchFilter && matchSearch) ? '' : 'none';
    });
}
document.querySelectorAll('.filter-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        currentFilter = this.getAttribute('data-filter');
        applyFilters();
    });
});

/* ── Live search ───────────────────────────────────────────────────── */
document.getElementById('searchInput')?.addEventListener('input', function() {
    currentSearch = this.value.toLowerCase();
    applyFilters();
});

/* ── Modal backdrop cleanup ─────────────────────────────────────────── */
document.addEventListener('hidden.bs.modal', function() {
    if (document.querySelector('.modal.show')) return;
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.classList.remove('modal-open');
    document.body.style.overflow     = '';
    document.body.style.paddingRight = '';
});

/* ── Assign modal: load current assignments ─────────────────────────── */
const productSelect = document.getElementById('productSelect');
if (productSelect) {
    productSelect.addEventListener('change', async function() {
        const itemId = this.value;
        document.querySelectorAll('.addon-checkbox').forEach(cb => cb.checked = false);
        if (!itemId) return;
        try {
            const response = await fetch(`index.php?page=manage_addons&action=get_assignments&item_id=${itemId}`);
            const data     = await response.json();
            if (data.assigned_addons && Array.isArray(data.assigned_addons)) {
                data.assigned_addons.forEach(addonId => {
                    const cb = document.getElementById(`addon_${addonId}`);
                    if (cb) cb.checked = true;
                });
            }
        } catch (e) { console.error('Error loading assigned add-ons:', e); }
    });
}
document.getElementById('assignAddonModal')?.addEventListener('hidden.bs.modal', function() {
    document.getElementById('productSelect').value = '';
    document.querySelectorAll('.addon-checkbox').forEach(cb => cb.checked = false);
});

/* ── Unit label updates ─────────────────────────────────────────────── */
const ingredientUnits = <?php
    $units = [];
    foreach ($ingredients as $ing) {
        $units[$ing['ingredient_id']] = $ing['unit_code'];
    }
    echo json_encode($units);
?>;

/* Add modal */
const addIngSel  = document.querySelector('#addAddonModal select[name="ingredient_id"]');
const addUnitTxt = document.getElementById('addUnitText');
if (addIngSel && addUnitTxt) {
    addIngSel.addEventListener('change', function() {
        addUnitTxt.textContent = ingredientUnits[this.value] || 'Unit';
    });
}

/* Edit modals */
<?php foreach ($addons as $a): ?>
(function() {
    const sel  = document.querySelector('#editModal<?= $a['addon_id'] ?> select[name="ingredient_id"]');
    const unit = document.getElementById('editUnitText<?= $a['addon_id'] ?>');
    if (sel && unit) {
        sel.addEventListener('change', function() {
            unit.textContent = ingredientUnits[this.value] || 'Unit';
        });
    }
})();
<?php endforeach; ?>
</script>
</body>
</html>