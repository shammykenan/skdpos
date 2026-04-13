<?php
// Pure view: expects $menu_items, $categories, $item_to_edit, $success, $error,
//            $totalItems, $availableItems, $avgProfitMargin, $totalProfit
// Each $item has: has_variants, variants[], active_variants[], base_price, cost_price, etc.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5">
    <title>Menu Management — Dine with Din</title>
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
        .data-table tbody tr.needs-price td { background: #fff8f8; }

        /* ── ITEM IMAGE ──────────────────────────────────────────────────── */
        .item-img { width: 50px; height: 50px; object-fit: cover; border-radius: var(--radius-md); border: 1px solid var(--border); flex-shrink: 0; }
        .item-img-ph { width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; background: #f2f6fc; border-radius: var(--radius-md); border: 1px solid var(--border); color: var(--primary-light); font-size: 1.1rem; flex-shrink: 0; }
        .item-name { font-weight: 700; font-size: 0.9rem; color: var(--navy); }
        .item-desc { font-size: 0.78rem; color: var(--warm-gray); margin-top: 2px; }

        /* ── PILLS ───────────────────────────────────────────────────────── */
        .pill { display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.72rem; font-weight: 700; padding: 0.3rem 0.75rem; border-radius: 30px; white-space: nowrap; }
        .pill-category    { background: rgba(15,76,92,0.08);  color: var(--primary-dark); }
        .pill-available   { background: rgba(44,122,90,0.12); color: var(--success); }
        .pill-unavailable { background: rgba(214,64,69,0.1);  color: var(--danger); }
        .pill-inactive    { background: rgba(214,64,69,0.1);  color: var(--danger); }
        .dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; flex-shrink: 0; }

        /* ── PRICE CELL ──────────────────────────────────────────────────── */
        .price-main { font-size: 0.95rem; font-weight: 700; color: var(--success); }
        .price-cost { font-size: 0.72rem; color: var(--warm-gray); margin-top: 1px; }
        .needs-price-flag { display: flex; align-items: center; gap: 0.35rem; font-size: 0.72rem; font-weight: 600; color: var(--danger); margin-top: 4px; }
        .btn-variants {
            display: inline-flex; align-items: center; gap: 0.4rem;
            background: rgba(15,76,92,0.07); color: var(--primary);
            border: 1px solid rgba(15,76,92,0.15); border-radius: 20px;
            padding: 0.3rem 0.8rem; font-size: 0.75rem; font-weight: 600;
            cursor: pointer; transition: all 0.15s; white-space: nowrap; margin-top: 0.4rem;
        }
        .btn-variants:hover { background: var(--primary); color: #fff; border-color: var(--primary); }

        /* ── VARIANT COMPACT LIST ─────────────────────────────────────────── */
        .variant-list-compact { max-height: 130px; overflow-y: auto; margin-bottom: 0.4rem; }
        .variant-list-compact::-webkit-scrollbar { width: 3px; }
        .variant-list-compact::-webkit-scrollbar-thumb { background: var(--primary-light); border-radius: 3px; }
        .variant-row-item { display: flex; justify-content: space-between; align-items: center; padding: 0.3rem 0; border-bottom: 1px dashed #edf2f7; font-size: 0.8rem; }
        .variant-row-item:last-child { border-bottom: none; }
        .variant-row-name  { font-weight: 600; color: var(--dark); }
        .variant-row-price { font-weight: 700; color: var(--success); }

        /* ── MARGIN CELL ─────────────────────────────────────────────────── */
        .margin-val  { font-size: 0.9rem; font-weight: 700; }
        .margin-ok   { color: var(--success); }
        .margin-warn { color: var(--warning); }
        .margin-bad  { color: var(--danger); }
        .margin-sub  { font-size: 0.72rem; color: var(--warm-gray); margin-top: 1px; }

        /* ── ACTION BUTTONS ──────────────────────────────────────────────── */
        .action-group { display: flex; gap: 0.4rem; justify-content: flex-end; }
        .btn-act { width: 34px; height: 34px; border-radius: var(--radius-sm); display: inline-flex; align-items: center; justify-content: center; border: none; cursor: pointer; font-size: 0.8rem; transition: all 0.15s; text-decoration: none; }
        .btn-act-edit { background: #fff3e0; color: #b85a1a; border: 1px solid rgba(244,185,66,0.2); }
        .btn-act-edit:hover  { background: var(--warning); color: #fff; }
        .btn-act-del  { background: rgba(214,64,69,0.08); color: var(--danger); border: 1px solid rgba(214,64,69,0.15); }
        .btn-act-del:hover   { background: var(--danger); color: #fff; }

        /* ── MODALS ──────────────────────────────────────────────────────── */
        .modal-content { border: none; border-radius: var(--radius-xl); box-shadow: 0 24px 60px rgba(0,0,0,0.12); }
        .modal-header { border-bottom: 1px solid var(--border); padding: 1.4rem 1.6rem; border-radius: var(--radius-xl) var(--radius-xl) 0 0; }
        .modal-title { font-family: 'Fraunces', Georgia, serif; font-size: 1.15rem; font-weight: 700; color: var(--navy); display: flex; align-items: center; gap: 0.5rem; }
        .modal-body { padding: 1.5rem 1.6rem; }
        .modal-footer { border-top: 1px solid var(--border); padding: 1.2rem 1.6rem; border-radius: 0 0 var(--radius-xl) var(--radius-xl); gap: 0.5rem; display: flex; align-items: center; justify-content: flex-end; flex-wrap: wrap; }

        .form-label { font-size: 0.82rem; font-weight: 700; color: var(--navy); margin-bottom: 0.4rem; }
        .form-control, .form-select {
            border: 1px solid var(--border); border-radius: var(--radius-md);
            padding: 0.6rem 0.9rem; font-size: 0.88rem; font-family: inherit;
            background: #fff; color: var(--dark);
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .form-control:focus, .form-select:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(15,76,92,0.12); }
        .form-control:disabled { background: #f0f4f8; color: #8a9bb0; cursor: not-allowed; }
        .form-control::placeholder { color: #a0aec0; }
        .input-group-text { background: #f2f6fc; border: 1px solid var(--border); border-radius: var(--radius-md) 0 0 var(--radius-md); padding: 0.6rem 0.8rem; color: var(--primary-dark); font-weight: 600; font-size: 0.85rem; }
        .input-group .form-control { border-radius: 0 var(--radius-md) var(--radius-md) 0; }
        .form-check-input:checked { background-color: var(--primary); border-color: var(--primary); }
        .form-text { font-size: 0.75rem; color: var(--warm-gray); margin-top: 0.25rem; }

        /* ── PRICING BANNERS ────────────────────────────────────────────── */
        .pricing-banner { border-radius: var(--radius-md); padding: 0.7rem 1rem; font-size: 0.82rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; transition: all 0.2s; }
        .pricing-banner.base    { background: rgba(44,122,90,0.1);  color: var(--success);      border: 1px solid rgba(44,122,90,0.2); }
        .pricing-banner.variant { background: rgba(15,76,92,0.08); color: var(--primary-dark); border: 1px solid rgba(15,76,92,0.2); }

        /* ── MODAL BUTTONS ──────────────────────────────────────────────── */
        .btn-modal-primary { background: var(--primary); color: #fff; border: none; padding: 0.6rem 1.4rem; border-radius: 40px; font-weight: 600; font-size: 0.88rem; cursor: pointer; transition: background 0.15s; display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; text-decoration: none; }
        .btn-modal-primary:hover { background: var(--primary-dark); color: #fff; }
        .btn-modal-secondary { background: #fff; color: var(--dark); border: 1px solid var(--border); padding: 0.6rem 1.4rem; border-radius: 40px; font-weight: 600; font-size: 0.88rem; cursor: pointer; transition: background 0.15s; display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; text-decoration: none; }
        .btn-modal-secondary:hover { background: var(--light-bg); color: var(--dark); }
        .btn-danger-solid { background: var(--danger); color: #fff; border: none; padding: 0.6rem 1.4rem; border-radius: 40px; font-weight: 600; font-size: 0.88rem; cursor: pointer; width: 100%; transition: background 0.15s; display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; }
        .btn-danger-solid:hover { background: #bb3338; color: #fff; }

        /* ── MANAGE VARIANTS MODAL ──────────────────────────────────────── */
        .mv-variant-row { display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 1.25rem; border-bottom: 1px solid #edf2f7; }
        .mv-variant-row:last-child { border-bottom: none; }
        .mv-variant-row:hover { background: #fafcff; }
        .mv-vname  { font-weight: 700; font-size: 0.88rem; color: var(--navy); }
        .mv-vprice { font-weight: 700; color: var(--success); font-size: 0.88rem; }
        .mv-vcost  { font-size: 0.72rem; color: var(--warm-gray); }
        .mv-act { display: flex; gap: 0.35rem; flex-shrink: 0; }
        .btn-mv-edit { width: 28px; height: 28px; background: #fff3e0; color: #b85a1a; border: none; border-radius: 6px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 0.72rem; transition: all 0.15s; }
        .btn-mv-edit:hover { background: var(--warning); color: #fff; }
        .btn-mv-del  { width: 28px; height: 28px; background: rgba(214,64,69,0.08); color: var(--danger); border: none; border-radius: 6px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 0.72rem; transition: all 0.15s; }
        .btn-mv-del:hover { background: var(--danger); color: #fff; }

        #variantInlineForm { border-top: 2px dashed var(--border); padding: 1.25rem; margin-top: 0.5rem; }
        .vform-title { font-size: 0.82rem; font-weight: 700; color: var(--navy); margin-bottom: 0.9rem; display: flex; align-items: center; gap: 0.4rem; }

        /* ── IMAGE PREVIEW ──────────────────────────────────────────────── */
        .img-preview-wrap { border: 1px solid var(--border); border-radius: var(--radius-md); padding: 0.6rem; background: #fafcff; }
        .img-preview { max-width: 100%; max-height: 130px; border-radius: var(--radius-sm); display: block; margin: 0 auto; }
        .img-preview-ph { border: 2px dashed var(--border); border-radius: var(--radius-md); padding: 0.75rem; background: var(--light-bg); }
        /* Make edit modal body scrollable and footer always visible */
#editMenuItemModal .modal-dialog {
    max-height: 90vh;
    display: flex;
    flex-direction: column;
}
#editMenuItemModal .modal-content {
    max-height: 100%;
    display: flex;
    flex-direction: column;
}
#editMenuItemModal .modal-body {
    overflow-y: auto;
    max-height: 70vh;          /* adjust as needed */
}
        /* ── CONFIRM MODAL ──────────────────────────────────────────────── */
        .confirm-icon { width: 64px; height: 64px; border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; font-size: 1.75rem; margin: 0 auto 1rem; }
        .confirm-icon.danger { background: rgba(214,64,69,0.1); color: var(--danger); }
        .confirm-note { background: rgba(214,64,69,0.06); border: 1px dashed rgba(214,64,69,0.3); border-radius: var(--radius-md); padding: 0.75rem 1rem; font-size: 0.78rem; color: #9e3a34; }

        /* ── CATEGORY MODAL ─────────────────────────────────────────────── */
        .cat-list { max-height: 210px; overflow-y: auto; border: 1px solid var(--border); border-radius: var(--radius-md); }
        .cat-list-item { display: flex; align-items: center; justify-content: space-between; padding: 0.65rem 1rem; border-bottom: 1px solid #edf2f7; }
        .cat-list-item:last-child { border-bottom: none; }
        .cat-edit-box { background: var(--light-bg); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 1rem; margin-top: 0.75rem; }
        .char-counter { font-size: 0.72rem; color: var(--warm-gray); text-align: right; margin-top: 0.25rem; }
        .char-counter.over { color: var(--danger); font-weight: 700; }

        /* ── ADD-ITEM VARIANT ROW ─────────────────────────────────────────── */
        .vadd-row { background: var(--light-bg); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 0.75rem 1rem; margin-bottom: 0.5rem; }

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
        /* ── iPad-specific (768–1024): sidebar hidden, table still visible but tightened ── */
        @media (min-width: 768px) and (max-width: 1024px) {
            :root { --sidebar-w: 220px; }
            .main { padding: 1.5rem; }
            .stats-row { grid-template-columns: repeat(2, 1fr); }

            /* Tighten table cells so all columns fit without horizontal scroll */
            .data-table thead th,
            .data-table tbody td { padding: 0.75rem 0.65rem; }

            /* Hide the image column on iPad — saves ~60px */
            .data-table thead th:first-child,
            .data-table tbody td:first-child { display: none; }

            /* Shrink item description to save vertical space */
            .item-desc { display: none; }

            /* Compact variant list */
            .variant-list-compact { max-height: 100px; }
            .variant-row-item { font-size: 0.75rem; }

            /* Compress the margin column text */
            .margin-val { font-size: 0.82rem; }
            .margin-sub { font-size: 0.68rem; }

            /* Shrink action buttons slightly */
            .btn-act { width: 30px; height: 30px; font-size: 0.75rem; }

            /* Keep Add Variant button readable */
            .btn-variants { font-size: 0.72rem; padding: 0.25rem 0.65rem; }

            /* Status pill compact */
            .pill { font-size: 0.68rem; padding: 0.25rem 0.6rem; }
        }
        @media (max-width: 767.98px) {
            .main { padding: 1.25rem 1rem; }
            .stats-row { grid-template-columns: repeat(2, 1fr); gap: 0.75rem; }
            .page-title { font-size: 1.4rem; }
            .head-actions { width: 100%; }
            .btn-primary-action, .btn-secondary-action { flex: 1; justify-content: center; }
            /* Card-stack table */
            .data-table thead { display: none; }
            .data-table tbody tr { display: block; border: 1px solid var(--border); border-radius: var(--radius-lg); margin-bottom: 1rem; background: #fff; overflow: hidden; }
            .data-table tbody tr.needs-price { background: #fff8f8; }
            .data-table tbody td { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid #edf2f7; padding: 0.75rem 1rem; }
            .data-table tbody td:last-child { border-bottom: none; }
            .data-table tbody td::before { content: attr(data-label); font-weight: 700; color: var(--warm-gray); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; flex-shrink: 0; margin-right: 0.5rem; padding-top: 2px; }
            .table-card { border-radius: var(--radius-lg); }
            .action-group { justify-content: flex-end; }
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
            <div class="brand-text-sub">POS with Recipe-Based Inventory Tracking</div>
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
        <a href="<?= $baseUrl ?>/index.php?page=manage_menu" class="nav-link active"><i class="fas fa-utensils"></i> Menu Items</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_recipes" class="nav-link"><i class="fas fa-tags"></i> Recipes</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_ingredients" class="nav-link"><i class="fas fa-boxes"></i> Ingredients</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_addons" class="nav-link"><i class="fas fa-plus-circle"></i> Add-ons</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_tables" class="nav-link"><i class="fas fa-chair"></i> Tables</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_users" class="nav-link"><i class="fas fa-users"></i> User Management</a>

        <div class="nav-section">Sales</div>
        <a href="<?= $baseUrl ?>/index.php?page=manage_pos" class="nav-link"><i class="fas fa-cash-register"></i> POS System</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_orders" class="nav-link"><i class="fas fa-receipt"></i> Orders</a>
        <a href="<?= $baseUrl ?>/index.php?page=reports" class="nav-link"><i class="fas fa-chart-bar"></i> Reports</a>
    </div>
</nav>

<!-- ── MAIN ───────────────────────────────────────────────────────────────── -->
<main class="main" id="mainContent">

    <div class="page-head">
        <div>
            <h1 class="page-title">Menu Management</h1>
            <div class="breadcrumb-bar">
                <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard">Home</a>
                <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:0.6rem;"></i></span>
                <span>Menu Items</span>
            </div>
        </div>
        <div class="head-actions">
            <button type="button" class="btn-secondary-action" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="fas fa-folder-plus"></i> Add Category
            </button>
            <button type="button" class="btn-primary-action" data-bs-toggle="modal" data-bs-target="#addMenuItemModal">
                <i class="fas fa-plus"></i> Add Item
            </button>
        </div>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert-bar success fade-up" role="alert">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($success) ?>
            <button class="dismiss" aria-label="Close">&times;</button>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert-bar danger fade-up" role="alert">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($error) ?>
            <button class="dismiss" aria-label="Close">&times;</button>
        </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-card fade-up fade-up-1">
            <div class="stat-icon"><i class="fas fa-utensils"></i></div>
            <div><div class="stat-label">Total Items</div><div class="stat-value"><?= $totalItems ?? 0 ?></div></div>
        </div>
        <div class="stat-card c-success fade-up fade-up-2">
            <div class="stat-icon c-success"><i class="fas fa-check-circle"></i></div>
            <div><div class="stat-label">Available</div><div class="stat-value"><?= count($availableItems ?? []) ?></div></div>
        </div>
        <div class="stat-card c-info fade-up fade-up-3">
            <div class="stat-icon c-info"><i class="fas fa-percentage"></i></div>
            <div><div class="stat-label">Avg. Margin</div><div class="stat-value"><?= number_format($avgProfitMargin ?? 0, 1) ?>%</div></div>
        </div>
        <div class="stat-card c-warning fade-up fade-up-4">
            <div class="stat-icon c-warning"><i class="fas fa-chart-line"></i></div>
            <div><div class="stat-label">Profit Potential</div><div class="stat-value" style="font-size:1.35rem;">₱<?= number_format($totalProfit ?? 0, 0) ?></div></div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-card fade-up fade-up-5">
        <div class="table-card-header">
            <div class="table-card-title">
                <i class="fas fa-list" style="color:var(--primary-light);"></i>
                All Menu Items
                <span class="item-count-badge"><?= $totalItems ?? 0 ?> items</span>
            </div>
        </div>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Item</th>
                        <th>Category</th>
                        <th>Price &amp; Variants</th>
                        <th>Margin</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($menu_items ?? [] as $item):
                    $variants       = $item['variants']        ?? [];
                    $activeVariants = $item['active_variants'] ?? [];
                    $hasVariants    = $item['has_variants']    ?? false;
                    $basePrice      = $item['base_price']      ?? 0;
                    $costPrice      = $item['cost_price']      ?? 0;
                    $needsPrice     = !$hasVariants && $basePrice <= 0;
                ?>
                    <tr <?= $needsPrice ? 'class="needs-price"' : '' ?>>
                        <td data-label="Image">
                            <?php if (!empty($item['image_url'])): ?>
                                <img src="<?= $baseUrl . '/' . htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['item_name']) ?>" class="item-img">
                            <?php else: ?>
                                <div class="item-img-ph"><i class="fas fa-image"></i></div>
                            <?php endif; ?>
                        </td>
                        <td data-label="Item">
                            <div class="item-name"><?= htmlspecialchars($item['item_name']) ?></div>
                            <?php if (!empty($item['description'])): ?>
                                <div class="item-desc"><?= htmlspecialchars(substr($item['description'], 0, 55)) ?><?= strlen($item['description']) > 55 ? '…' : '' ?></div>
                            <?php endif; ?>
                            <?php if ($needsPrice): ?><div class="needs-price-flag"><i class="fas fa-exclamation-triangle"></i> Needs price</div><?php endif; ?>
                        </td>
                        <td data-label="Category">
                            <span class="pill pill-category"><?= htmlspecialchars($item['category_name']) ?></span>
                        </td>
                        <td data-label="Price / Variants">
                            <?php if ($hasVariants): ?>
                                <div class="variant-list-compact">
                                    <?php
                                    $sorted = $activeVariants;
                                    usort($sorted, fn($a,$b) => $a['price'] <=> $b['price']);
                                    foreach ($sorted as $v): ?>
                                        <div class="variant-row-item">
                                            <span class="variant-row-name">
                                                <?= htmlspecialchars($v['variant_name']) ?>
                                                <?php if (!$v['is_active']): ?><span class="pill pill-inactive" style="font-size:0.65rem;padding:0.15rem 0.5rem;margin-left:4px;">Off</span><?php endif; ?>
                                            </span>
                                            <span class="variant-row-price">₱<?= number_format($v['price'], 2) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if (empty($activeVariants)): ?><div style="font-size:0.78rem;color:var(--warm-gray);font-style:italic;">All inactive</div><?php endif; ?>
                                </div>
                                <button type="button" class="btn-variants"
                                        onclick="openManageVariantsModal(<?= $item['item_id'] ?>, <?= htmlspecialchars(json_encode($item['item_name'])) ?>, <?= htmlspecialchars(json_encode($variants)) ?>)">
                                    <i class="fas fa-layer-group"></i> Manage (<?= count($variants) ?> / <?= count($activeVariants) ?> active)
                                </button>
                            <?php elseif ($needsPrice): ?>
                                <div class="needs-price-flag" style="margin-bottom:0.35rem;"><i class="fas fa-exclamation-triangle"></i> No price set</div>
                                <button type="button" class="btn-variants"
                                        onclick="openManageVariantsModal(<?= $item['item_id'] ?>, <?= htmlspecialchars(json_encode($item['item_name'])) ?>, [])">
                                    <i class="fas fa-plus"></i> Add variants
                                </button>
                            <?php else: ?>
                                <div class="price-main">₱<?= number_format($basePrice, 2) ?></div>
                                <div class="price-cost">Cost: ₱<?= number_format($costPrice, 2) ?></div>
                                <button type="button" class="btn-variants"
                                        onclick="openManageVariantsModal(<?= $item['item_id'] ?>, <?= htmlspecialchars(json_encode($item['item_name'])) ?>, [])">
                                    <i class="fas fa-plus"></i> Add variants
                                </button>
                            <?php endif; ?>
                        </td>
                        <td data-label="Margin">
                            <?php if ($hasVariants && !empty($activeVariants)): ?>
                                <?php
                                $margins = array_map(fn($v) => $v['price'] > 0 ? (($v['price'] - $v['cost_price']) / $v['price']) * 100 : 0, $activeVariants);
                                $minM = min($margins); $maxM = max($margins); $avgM = array_sum($margins)/count($margins);
                                $cls  = $avgM >= 30 ? 'margin-ok' : ($avgM >= 20 ? 'margin-warn' : 'margin-bad');
                                ?>
                                <div class="margin-val <?= $cls ?>"><?= $minM == $maxM ? number_format($minM,1).'%' : number_format($minM,1).'–'.number_format($maxM,1).'%' ?></div>
                                <div class="margin-sub">avg <?= number_format($avgM,1) ?>%</div>
                            <?php elseif (!$hasVariants && $basePrice > 0): ?>
                                <?php $profit = $basePrice - $costPrice; $margin = ($profit / $basePrice) * 100; $cls = $margin >= 30 ? 'margin-ok' : ($margin >= 20 ? 'margin-warn' : 'margin-bad'); ?>
                                <div class="margin-val <?= $cls ?>"><?= number_format($margin,1) ?>%</div>
                                <div class="margin-sub">₱<?= number_format($profit,2) ?></div>
                            <?php else: ?>
                                <span style="font-size:0.78rem;color:var(--warm-gray);font-style:italic;">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Status">
                            <span class="pill <?= $item['is_available'] ? 'pill-available' : 'pill-unavailable' ?>">
                                <span class="dot"></span>
                                <?= $item['is_available'] ? 'Available' : 'Unavailable' ?>
                            </span>
                        </td>
                        <td data-label="Actions" style="text-align:right;">
                            <div class="action-group">
                                <a href="<?= $baseUrl ?>/index.php?page=manage_menu&edit=<?= $item['item_id'] ?>" class="btn-act btn-act-edit" title="Edit item"><i class="fas fa-edit"></i></a>
                                <button type="button" class="btn-act btn-act-del"
                                        onclick="confirmDeleteItem(<?= $item['item_id'] ?>, '<?= htmlspecialchars(addslashes($item['item_name'])) ?>')"
                                        title="Delete item"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ═══ MANAGE VARIANTS MODAL ═══════════════════════════════════════════ -->
    <div class="modal fade" id="manageVariantsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-layer-group" style="color:var(--secondary);font-size:1rem;"></i> Variants — <span id="mvItemName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="mx-3 mt-3 p-3 rounded-3" style="background:rgba(15,76,92,0.07);border:1px solid rgba(15,76,92,0.15);font-size:0.82rem;color:var(--primary-dark);">
                        <i class="fas fa-info-circle me-1" style="color:var(--primary);"></i>
                        <strong>Variant pricing mode.</strong> Once at least one variant exists, the item's base price is ignored. All pricing comes from variants below.
                    </div>
                    <div id="mvVariantList" style="min-height:60px;"></div>
                    <div id="variantInlineForm" class="d-none">
                        <div class="vform-title"><i class="fas fa-plus-circle" style="color:var(--secondary);"></i><span id="variantFormTitle">Add New Variant</span></div>
                        <form method="POST" action="<?= $baseUrl ?>/index.php?page=manage_menu" id="variantForm">
                            <input type="hidden" name="item_id"        id="vf_item_id">
                            <input type="hidden" name="variant_id"     id="vf_variant_id">
                            <input type="hidden" name="variant_action" id="vf_action" value="">
                            <div class="row g-2">
                                <div class="col-sm-4">
                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="vf_name" name="variant_name" placeholder="e.g. Small, Spicy" required>
                                </div>
                                <div class="col-sm-3">
                                    <label class="form-label">Price <span class="text-danger">*</span></label>
                                    <div class="input-group"><span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" id="vf_price" name="price" step="0.01" min="0.01" placeholder="0.00" required></div>
                                </div>
                                <div class="col-sm-3">
                                    <label class="form-label">Cost</label>
                                    <div class="input-group"><span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" id="vf_cost" name="cost_price" step="0.01" min="0" placeholder="0.00" value="0"></div>
                                </div>
                                <div class="col-sm-2 d-flex align-items-end pb-1">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" id="vf_active" name="is_active" value="1" checked>
                                        <label class="form-check-label" for="vf_active" style="font-size:0.8rem;font-weight:600;">Active</label>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2 mt-3 justify-content-end">
                                <button type="button" class="btn-modal-secondary" onclick="cancelVariantForm()">Cancel</button>
                                <button type="submit" class="btn-modal-primary" id="vf_submit_btn"><i class="fas fa-plus me-1"></i> Add Variant</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn-modal-primary" onclick="showAddVariantForm()"><i class="fas fa-plus me-1"></i> Add Variant</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ DELETE VARIANT MODAL ════════════════════════════════════════════ -->
    <div class="modal fade" id="deleteVariantModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
            <div class="modal-content">
                <div class="modal-header" style="border-bottom:none;padding-bottom:0;">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center" style="padding:1rem 1.6rem 1.25rem;">
                    <div class="confirm-icon danger"><i class="fas fa-trash"></i></div>
                    <h5 style="font-family:'Fraunces',serif;font-weight:700;color:var(--navy);margin-bottom:0.5rem;">Delete Variant?</h5>
                    <p style="font-size:0.85rem;color:var(--warm-gray);margin-bottom:0.5rem;">You're about to delete:</p>
                    <p id="deleteVariantName" style="color:var(--danger);font-weight:700;font-size:1rem;margin-bottom:1rem;"></p>
                    <div class="confirm-note"><i class="fas fa-exclamation-triangle me-1"></i>If this is the last variant, the item reverts to base-price mode and you'll need to set a base price.</div>
                </div>
                <div class="modal-footer" style="border-top:none;">
                    <button type="button" class="btn-modal-secondary" style="flex:1;" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="<?= $baseUrl ?>/index.php?page=manage_menu" style="flex:1;">
                        <input type="hidden" name="delete_variant" value="1">
                        <input type="hidden" name="variant_id" id="deleteVariantId">
                        <button type="submit" class="btn-danger-solid"><i class="fas fa-trash me-1"></i> Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ DELETE MENU ITEM MODAL ══════════════════════════════════════════ -->
    <div class="modal fade" id="deleteMenuItemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
            <div class="modal-content">
                <div class="modal-header" style="border-bottom:none;padding-bottom:0;">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center" style="padding:1rem 1.6rem 1.25rem;">
                    <div class="confirm-icon danger"><i class="fas fa-trash"></i></div>
                    <h5 style="font-family:'Fraunces',serif;font-weight:700;color:var(--navy);margin-bottom:0.5rem;">Delete Menu Item?</h5>
                    <p style="font-size:0.85rem;color:var(--warm-gray);margin-bottom:0.5rem;">You're about to delete:</p>
                    <p id="deleteMenuItemName" style="color:var(--danger);font-weight:700;font-size:1rem;margin-bottom:1rem;"></p>
                    <div class="confirm-note"><i class="fas fa-exclamation-triangle me-1"></i>This also removes all variants and cannot be undone.</div>
                </div>
                <div class="modal-footer" style="border-top:none;">
                    <button type="button" class="btn-modal-secondary" style="flex:1;" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="<?= $baseUrl ?>/index.php?page=manage_menu" style="flex:1;">
                        <input type="hidden" name="delete_menu_item" value="1">
                        <input type="hidden" name="item_id" id="deleteMenuItemId">
                        <button type="submit" class="btn-danger-solid"><i class="fas fa-trash me-1"></i> Yes, Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ ADD CATEGORY MODAL ══════════════════════════════════════════════ -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-folder-plus" style="color:var(--secondary);font-size:1rem;"></i> Categories</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="<?= $baseUrl ?>/index.php?page=manage_menu" id="addCategoryForm" novalidate>
                    <div class="modal-body">
                        <input type="hidden" name="add_category" value="1">
                        <label for="modal_category_name" class="form-label">New Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="modal_category_name" name="category_name"
                               placeholder="e.g. Hot Beverages, Snacks…" maxlength="80" required autocomplete="off">
                        <div class="char-counter" id="catCharCounter">0 / 80</div>
                        <div id="dupWarning" class="alert-bar danger d-none mt-2" style="padding:0.6rem 0.9rem;font-size:0.8rem;margin-bottom:0;">
                            <i class="fas fa-exclamation-triangle me-1"></i> A category with this name already exists.
                        </div>
                        <div style="margin-top:1.25rem;">
                            <div class="form-label">Existing Categories</div>
                            <?php if (empty($categories)): ?>
                                <p style="font-size:0.82rem;color:var(--warm-gray);font-style:italic;">No categories yet.</p>
                            <?php else: ?>
                                <div class="cat-list">
                                    <?php foreach ($categories as $cat): ?>
                                        <div class="cat-list-item">
                                            <span style="font-size:0.88rem;font-weight:600;color:var(--navy);"><?= htmlspecialchars($cat['category_name']) ?></span>
                                            <div style="display:flex;gap:0.35rem;">
                                                <button type="button" class="btn-act btn-act-edit"
                                                        onclick="catEditInline(<?= $cat['category_id'] ?>, <?= htmlspecialchars(json_encode($cat['category_name'])) ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="POST" action="<?= $baseUrl ?>/index.php?page=manage_menu" style="margin:0;"
                                                      onsubmit="return confirm('Delete \'<?= htmlspecialchars(addslashes($cat['category_name'])) ?>\'?')">
                                                    <input type="hidden" name="delete_category" value="1">
                                                    <input type="hidden" name="category_id" value="<?= $cat['category_id'] ?>">
                                                    <button type="submit" class="btn-act btn-act-del"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div id="catEditSection" class="cat-edit-box d-none">
                            <form method="POST" action="<?= $baseUrl ?>/index.php?page=manage_menu">
                                <input type="hidden" name="update_category" value="1">
                                <input type="hidden" name="category_id" id="editCatId">
                                <label class="form-label" style="font-size:0.78rem;">Rename Category</label>
                                <div class="d-flex gap-2">
                                    <input type="text" class="form-control" name="category_name" id="editCatName" maxlength="80" required>
                                    <button type="submit" class="btn-modal-primary" style="white-space:nowrap;"><i class="fas fa-save me-1"></i> Save</button>
                                    <button type="button" class="btn-modal-secondary" onclick="document.getElementById('catEditSection').classList.add('d-none')">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-modal-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn-modal-primary" id="saveCategoryBtn"><i class="fas fa-folder-plus me-1"></i> Add Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ═══ ADD MENU ITEM MODAL ═════════════════════════════════════════════ -->
    <div class="modal fade" id="addMenuItemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus-circle" style="color:var(--secondary);font-size:1rem;"></i> Add New Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="<?= $baseUrl ?>/index.php?page=manage_menu" enctype="multipart/form-data" id="addItemForm">
                    <div class="modal-body">
                        <input type="hidden" name="add_menu_item" value="1">
                        <div class="row g-3 mb-3">
                            <div class="col-md-8">
                                <label class="form-label">Item Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="item_name" placeholder="e.g. Caramel Macchiato" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-select" name="category_id" required>
                                    <option value="">Select…</option>
                                    <?php foreach ($categories ?? [] as $category): ?>
                                        <option value="<?= $category['category_id'] ?>"><?= htmlspecialchars($category['category_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="2" placeholder="Short description…"></textarea>
                        </div>
                        <div class="pricing-banner base" id="pricingModeBanner">
                            <i class="fas fa-tag"></i>
                            <span id="pricingModeText">Base price mode — enter a selling price below.</span>
                        </div>
                        <div id="basePriceSection" class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Base Price <span class="text-danger" id="basePriceRequired">*</span></label>
                                <div class="input-group"><span class="input-group-text">₱</span>
                                <input type="number" class="form-control" id="basePriceInput" name="base_price" step="0.01" min="0.01" placeholder="0.00" required></div>
                                <div class="form-text" id="basePriceHint">Required — must be &gt; 0</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Base Cost</label>
                                <div class="input-group"><span class="input-group-text">₱</span>
                                <input type="number" class="form-control" id="baseCostInput" name="cost_price" step="0.01" min="0" placeholder="0.00"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0">Variants <span style="font-weight:500;color:var(--warm-gray);">(optional)</span></label>
                                <button type="button" class="btn-secondary-action" style="padding:0.35rem 0.9rem;font-size:0.8rem;" id="addVariantBtn"><i class="fas fa-plus me-1"></i> Add Variant</button>
                            </div>
                            <div id="variantsContainer"></div>
                        </div>
                        <div>
                            <label class="form-label">Item Image</label>
                            <input type="file" class="form-control" id="modal_image" name="image" accept="image/*">
                            <div class="form-text">Max 2 MB — JPG, PNG, GIF, WebP</div>
                            <div class="img-preview-ph mt-2 d-none" id="modalImagePreviewContainer">
                                <img id="modalImagePreview" class="img-preview" alt="Preview">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-modal-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-modal-primary"><i class="fas fa-plus me-1"></i> Add Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ═══ EDIT MENU ITEM MODAL ════════════════════════════════════════════ -->
    <?php if (isset($item_to_edit) && !empty($item_to_edit)): ?>
    <?php
        $edit_variants     = array_values(array_filter($menu_items ?? [], fn($i) => $i['item_id'] == $item_to_edit['item_id']));
        $edit_has_variants = !empty($edit_variants) && ($edit_variants[0]['has_variants'] ?? false);
    ?>
    <div class="modal fade" id="editMenuItemModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">   
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit" style="color:var(--secondary);font-size:1rem;"></i> Edit Item</h5>
                    <a href="<?= $baseUrl ?>/index.php?page=manage_menu" class="btn-close"></a>
                </div>
                <form method="POST" action="<?= $baseUrl ?>/index.php?page=manage_menu" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="item_id" value="<?= $item_to_edit['item_id'] ?>">
                        <input type="hidden" name="update_menu_item" value="1">
                        <div class="row g-3 mb-3">
                            <div class="col-md-8">
                                <label class="form-label">Item Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="item_name" value="<?= htmlspecialchars($item_to_edit['item_name']) ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-select" name="category_id" required>
                                    <option value="">Select…</option>
                                    <?php foreach ($categories ?? [] as $category): ?>
                                        <option value="<?= $category['category_id'] ?>" <?= ($item_to_edit['category_id'] == $category['category_id']) ? 'selected' : '' ?>><?= htmlspecialchars($category['category_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="2"><?= htmlspecialchars($item_to_edit['description'] ?? '') ?></textarea>
                        </div>
                        <?php if ($edit_has_variants): ?>
                            <div class="pricing-banner variant mb-3">
                                <i class="fas fa-layer-group"></i>
                                <strong>Variant pricing active.</strong> Base price is ignored while variants exist. Manage variants from the table.
                            </div>
                        <?php endif; ?>
                        <div class="row g-3 mb-3" id="editBasePriceSection" <?= $edit_has_variants ? 'style="opacity:0.45;pointer-events:none;"' : '' ?>>
                            <div class="col-md-6">
                                <label class="form-label">Base Price <?= !$edit_has_variants ? '<span class="text-danger">*</span>' : '' ?></label>
                                <div class="input-group"><span class="input-group-text">₱</span>
                                <input type="number" class="form-control" name="base_price"
                                       value="<?= $edit_has_variants ? '' : htmlspecialchars($item_to_edit['base_price'] ?? '') ?>"
                                       step="0.01" min="0"
                                       <?= !$edit_has_variants ? 'required' : 'placeholder="N/A — variants active"' ?>></div>
                                <div class="form-text"><?= $edit_has_variants ? 'Overridden by variants' : 'Used only if no variants exist' ?></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Base Cost</label>
                                <div class="input-group"><span class="input-group-text">₱</span>
                                <input type="number" class="form-control" name="cost_price"
                                       value="<?= $edit_has_variants ? '' : htmlspecialchars($item_to_edit['cost_price'] ?? 0) ?>"
                                       step="0.01" min="0"
                                       <?= $edit_has_variants ? 'placeholder="N/A — variants active"' : '' ?>></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Item Image</label>
                            <?php if (!empty($item_to_edit['image_url'])): ?>
                                <div class="img-preview-wrap mb-2">
                                    <img src="<?= $baseUrl . '/' . htmlspecialchars($item_to_edit['image_url']) ?>" alt="Current" class="img-preview">
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="delete_image" value="1" id="deleteImage">
                                    <label class="form-check-label" style="font-size:0.82rem;color:var(--danger);" for="deleteImage">Remove current image</label>
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="edit_image" name="image" accept="image/*">
                            <div class="form-text">Max 2 MB. Leave empty to keep current image.</div>
                            <div class="img-preview-ph mt-2 d-none" id="editImagePreviewContainer">
                                <img id="editImagePreview" class="img-preview" alt="Preview">
                            </div>
                        </div>
                        <div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="edit_is_available" name="is_available" value="1" <?= $item_to_edit['is_available'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="edit_is_available" style="font-size:0.88rem;font-weight:600;">Available for ordering</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="<?= $baseUrl ?>/index.php?page=manage_menu" class="btn-modal-secondary">Cancel</a>
                        <button type="submit" class="btn-modal-primary"><i class="fas fa-save me-1"></i> Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new bootstrap.Modal(document.getElementById('editMenuItemModal')).show();
        });
    </script>
    <?php endif; ?>

</main>

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

/* ── Alert dismiss ────────────────────────────────────────────────── */
document.querySelectorAll('.alert-bar .dismiss').forEach(btn => {
    btn.addEventListener('click', () => btn.closest('.alert-bar').remove());
});
setTimeout(() => {
    document.querySelectorAll('.alert-bar').forEach(a => { a.style.opacity = '0'; });
    setTimeout(() => document.querySelectorAll('.alert-bar').forEach(a => a.remove()), 400);
}, 6000);

/* ── Image previews ───────────────────────────────────────────────── */
function wirePreview(inputId, previewId, wrapperId) {
    document.getElementById(inputId)?.addEventListener('change', function(e) {
        const file = e.target.files[0]; if (!file) return;
        const r = new FileReader();
        r.onload = ev => {
            document.getElementById(previewId).src = ev.target.result;
            document.getElementById(wrapperId).classList.remove('d-none');
        };
        r.readAsDataURL(file);
    });
}
wirePreview('modal_image', 'modalImagePreview', 'modalImagePreviewContainer');
wirePreview('edit_image',  'editImagePreview',  'editImagePreviewContainer');

/* ── Delete modals ────────────────────────────────────────────────── */
function confirmDeleteItem(id, name) {
    document.getElementById('deleteMenuItemId').value = id;
    document.getElementById('deleteMenuItemName').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteMenuItemModal')).show();
}
function confirmDeleteVariant(id, name) {
    document.getElementById('deleteVariantId').value = id;
    document.getElementById('deleteVariantName').textContent = name;
    bootstrap.Modal.getInstance(document.getElementById('manageVariantsModal'))?.hide();
    setTimeout(() => new bootstrap.Modal(document.getElementById('deleteVariantModal')).show(), 260);
}

/* ── Add Item — Pricing Mode ──────────────────────────────────────── */
let variantCounter = 0;

function setBasePriceMode(hasVariants) {
    const banner   = document.getElementById('pricingModeBanner');
    const section  = document.getElementById('basePriceSection');
    const bpInput  = document.getElementById('basePriceInput');
    const bcInput  = document.getElementById('baseCostInput');
    const bpHint   = document.getElementById('basePriceHint');
    const bpReq    = document.getElementById('basePriceRequired');
    const modeText = document.getElementById('pricingModeText');
    if (hasVariants) {
        banner.className = 'pricing-banner variant';
        banner.querySelector('i').className = 'fas fa-layer-group';
        modeText.textContent = 'Variant pricing active — base price will be set to NULL.';
        section.style.opacity = '0.45'; section.style.pointerEvents = 'none';
        bpInput.disabled = true; bcInput.disabled = true;
        bpInput.value = ''; bcInput.value = '';
        bpInput.removeAttribute('required'); bpInput.removeAttribute('min');
        bpReq.style.display = 'none';
        bpHint.textContent = 'Overridden by variants';
    } else {
        banner.className = 'pricing-banner base';
        banner.querySelector('i').className = 'fas fa-tag';
        modeText.textContent = 'Base price mode — enter a selling price below.';
        section.style.opacity = ''; section.style.pointerEvents = '';
        bpInput.disabled = false; bcInput.disabled = false;
        bpInput.setAttribute('required', 'required');
        bpInput.setAttribute('min', '0.01');
        bpReq.style.display = '';
        bpHint.textContent = 'Required — must be > 0';
    }
}
function getVariantCount() { return document.querySelectorAll('#variantsContainer .vadd-row').length; }
function refreshPricingMode() { setBasePriceMode(getVariantCount() > 0); }

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function addVariantRow(name='', price='', cost='', isActive=true) {
    const container = document.getElementById('variantsContainer');
    const rowId = `vrow_${variantCounter++}`;
    container.insertAdjacentHTML('beforeend', `
        <div class="vadd-row" id="${rowId}">
            <div class="row g-2 align-items-center">
                <div class="col-sm-4">
                    <input type="text" class="form-control form-control-sm" name="variant_names[]"
                           placeholder="Name (e.g. Small)" value="${escHtml(name)}" required>
                </div>
                <div class="col-sm-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">₱</span>
                        <input type="number" class="form-control" name="variant_prices[]"
                               step="0.01" min="0.01" placeholder="Price" value="${escHtml(price)}" required>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">₱</span>
                        <input type="number" class="form-control" name="variant_costs[]"
                               step="0.01" min="0" placeholder="Cost" value="${escHtml(cost)}">
                    </div>
                </div>
                <div class="col-sm-2 d-flex align-items-center gap-2">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="variant_active[]" value="1" ${isActive ? 'checked' : ''}>
                    </div>
                    <button type="button" class="btn-act btn-act-del ms-auto" onclick="removeVariantRow('${rowId}')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    `);
    refreshPricingMode();
}
function removeVariantRow(rowId) {
    document.getElementById(rowId)?.remove();
    refreshPricingMode();
}
document.getElementById('addVariantBtn')?.addEventListener('click', () => addVariantRow());
document.getElementById('addMenuItemModal')?.addEventListener('hidden.bs.modal', function() {
    document.getElementById('variantsContainer').innerHTML = '';
    variantCounter = 0;
    document.getElementById('addItemForm').reset();
    document.getElementById('modalImagePreviewContainer').classList.add('d-none');
    refreshPricingMode();
});
document.getElementById('addMenuItemModal')?.addEventListener('shown.bs.modal', () => refreshPricingMode());

/* ── Manage Variants Modal ────────────────────────────────────────── */
let _mvItemId = null;
function esc(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function openManageVariantsModal(itemId, itemName, variants) {
    _mvItemId = itemId;
    document.getElementById('mvItemName').textContent = itemName;
    renderVariantList(variants);
    cancelVariantForm();
    new bootstrap.Modal(document.getElementById('manageVariantsModal')).show();
}
function renderVariantList(variants) {
    const c = document.getElementById('mvVariantList');
    if (!variants || variants.length === 0) {
        c.innerHTML = `<div style="text-align:center;padding:2rem 1.5rem;color:var(--warm-gray);">
            <i class="fas fa-layer-group" style="font-size:2rem;opacity:.2;display:block;margin-bottom:0.5rem;"></i>
            <p style="font-size:0.85rem;margin:0;">No variants yet. Click "Add Variant" to create size or type options.</p>
            <p style="font-size:0.78rem;opacity:.7;margin-top:0.25rem;">Until a variant is added, the item uses its base price.</p>
        </div>`;
        return;
    }
    let html = '';
    variants.forEach(v => {
        const profit = v.price - v.cost_price;
        const margin = v.price > 0 ? ((profit / v.price) * 100).toFixed(1) : '0.0';
        const statusHtml = v.is_active
            ? `<span class="pill pill-available" style="font-size:0.68rem;padding:0.2rem 0.55rem;"><span class="dot"></span>Active</span>`
            : `<span class="pill pill-unavailable" style="font-size:0.68rem;padding:0.2rem 0.55rem;"><span class="dot"></span>Inactive</span>`;
        html += `
        <div class="mv-variant-row">
            <div style="flex:1;min-width:0;">
                <div class="mv-vname">${esc(v.variant_name)}</div>
                <div style="display:flex;gap:0.75rem;margin-top:3px;flex-wrap:wrap;">
                    <span class="mv-vprice">₱${parseFloat(v.price).toFixed(2)}</span>
                    <span class="mv-vcost">Cost ₱${parseFloat(v.cost_price).toFixed(2)}</span>
                    <span class="mv-vcost">Margin ${margin}%</span>
                </div>
            </div>
            <div>${statusHtml}</div>
            <div class="mv-act">
                <button type="button" class="btn-mv-edit"
                        onclick="showEditVariantForm(${v.variant_id}, '${v.variant_name.replace(/'/g,"\\'")}', ${v.price}, ${v.cost_price}, ${v.is_active ? 1 : 0})">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="btn-mv-del"
                        onclick="confirmDeleteVariant(${v.variant_id}, '${v.variant_name.replace(/'/g,"\\'")}')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>`;
    });
    c.innerHTML = html;
}
function showAddVariantForm() {
    document.getElementById('variantFormTitle').textContent = 'Add New Variant';
    document.getElementById('vf_submit_btn').innerHTML = '<i class="fas fa-plus me-1"></i> Add Variant';
    document.getElementById('vf_item_id').value    = _mvItemId;
    document.getElementById('vf_variant_id').value = '';
    document.getElementById('vf_name').value   = '';
    document.getElementById('vf_price').value  = '';
    document.getElementById('vf_cost').value   = '0';
    document.getElementById('vf_active').checked = true;
    document.getElementById('vf_action').value = 'add';
    document.getElementById('variantInlineForm').classList.remove('d-none');
    document.getElementById('vf_name').focus();
}
function showEditVariantForm(variantId, name, price, cost, isActive) {
    document.getElementById('variantFormTitle').textContent = 'Edit Variant';
    document.getElementById('vf_submit_btn').innerHTML = '<i class="fas fa-save me-1"></i> Update Variant';
    document.getElementById('vf_item_id').value    = _mvItemId;
    document.getElementById('vf_variant_id').value = variantId;
    document.getElementById('vf_name').value   = name;
    document.getElementById('vf_price').value  = price;
    document.getElementById('vf_cost').value   = cost;
    document.getElementById('vf_active').checked = isActive == 1;
    document.getElementById('vf_action').value = 'update';
    document.getElementById('variantInlineForm').classList.remove('d-none');
    document.getElementById('vf_name').focus();
}
function cancelVariantForm() {
    document.getElementById('variantInlineForm').classList.add('d-none');
    document.getElementById('vf_action').value = '';
    document.getElementById('vf_variant_id').value = '';
}

/* ── Category modal ───────────────────────────────────────────────── */
(function() {
    const catInput = document.getElementById('modal_category_name');
    const counter  = document.getElementById('catCharCounter');
    const dupWarn  = document.getElementById('dupWarning');
    const saveBtn  = document.getElementById('saveCategoryBtn');
    const form     = document.getElementById('addCategoryForm');
    const existing = new Set(<?php echo json_encode(array_map(fn($c) => strtolower($c['category_name']), $categories ?? [])); ?>);

    catInput?.addEventListener('input', function() {
        const len = this.value.length;
        counter.textContent = `${len} / 80`;
        counter.classList.toggle('over', len >= 80);
        dupWarn.classList.toggle('d-none', !existing.has(this.value.trim().toLowerCase()));
        catInput.classList.remove('is-invalid');
    });
    saveBtn?.addEventListener('click', function() {
        const name = catInput.value.trim();
        if (!name) { catInput.classList.add('is-invalid'); catInput.focus(); return; }
        if (existing.has(name.toLowerCase())) { dupWarn.classList.remove('d-none'); catInput.focus(); return; }
        catInput.classList.remove('is-invalid');
        form.submit();
    });
    catInput?.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); saveBtn.click(); } });
    document.getElementById('addCategoryModal')?.addEventListener('hidden.bs.modal', function() {
        form.reset(); catInput.classList.remove('is-invalid');
        dupWarn.classList.add('d-none'); counter.textContent = '0 / 80'; counter.classList.remove('over');
    });
})();

function catEditInline(id, name) {
    document.getElementById('editCatId').value   = id;
    document.getElementById('editCatName').value = name;
    document.getElementById('catEditSection').classList.remove('d-none');
    document.getElementById('editCatName').focus();
}

/* ── Clean up Bootstrap modal artifacts ───────────────────────────── */
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