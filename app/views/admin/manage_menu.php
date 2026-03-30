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
    <title>Menu Management - Cafeteria POS</title>
    <link rel="icon" type="image/png" href="<?= $baseUrl ?>/assets/images/logo/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0f4c5c; --primary-dark: #0a3a47; --primary-light: #1f7a8c;
            --secondary: #ff6b4a; --accent: #fee440; --navy: #1e2f4e;
            --lavender: #e2e6f0; --light-bg: #f8faff; --warm-gray: #4a5568;
            --border: #cbd5e0; --success: #2c7a5a; --danger: #d64045;
            --warning: #f4b942; --info: #5e9b9e; --dark: #1a202c;
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

        /* ── Logo ─────────────────────────────────────────────────────────── */
        .topbar-left { display: flex; align-items: center; gap: 0.6rem; min-width: 0; flex-shrink: 1; }
        .topbar .logo {
            text-decoration: none;
            display: flex; align-items: center; gap: 0.65rem;
            min-width: 0;
        }
        .logo-img-fallback {
            width: 44px; height: 44px; border-radius: 12px; flex-shrink: 0;
            background: var(--primary);
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 1.25rem;
            box-shadow: 0 4px 10px rgba(15,76,92,0.25);
            overflow: hidden;
        }
        .logo-img-fallback img {
            width: 100%; height: 100%; object-fit: cover; display: block;
        }
        .logo-text-wrap {
            display: flex; flex-direction: column; line-height: 1.1; min-width: 0;
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
        .topbar-right { display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0; }

        /* ── User button ─────────────────────────────────────────────────── */
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
            display: block !important;
        }
        .user-btn::after { display: none !important; }
        .user-btn .user-caret { color: var(--warm-gray); font-size: 0.7rem; flex-shrink: 0; }

        /* ── SIDEBAR ─────────────────────────────────────────────────────── */
        .sidebar { position: fixed; top: var(--topbar-height); left: 0; width: var(--sidebar-width); height: calc(100vh - var(--topbar-height)); background: white; border-right: 1px solid var(--border); overflow-y: auto; transition: transform 0.25s cubic-bezier(0.4,0,0.2,1); z-index: 1030; }
        .sidebar::-webkit-scrollbar { width: 5px; }
        .sidebar::-webkit-scrollbar-thumb { background: var(--border); border-radius: 8px; }
        .sidebar-nav { padding: 2rem 0.75rem; }
        .nav-section-title { padding: 0.6rem 1.2rem; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: var(--warm-gray); letter-spacing: 1.2px; opacity: 0.7; margin-top: 0.8rem; }
        .nav-item { margin: 0.2rem 0.6rem; }
        .nav-link { display: flex; align-items: center; padding: 0.8rem 1.2rem; color: var(--warm-gray); text-decoration: none; border-radius: 14px; transition: all 0.2s; font-weight: 500; font-size: 0.95rem; }
        .nav-link:hover { background: var(--lavender); color: var(--primary-dark); }
        .nav-link.active { background: var(--primary); color: white; box-shadow: 0 6px 12px rgba(15,76,92,0.2); }
        .nav-link i { width: 24px; margin-right: 0.85rem; font-size: 1.2rem; text-align: center; }

        /* ── MAIN ─────────────────────────────────────────────────────────── */
        .main-content { margin-left: var(--sidebar-width); margin-top: var(--topbar-height); padding: 2.2rem; min-height: calc(100vh - var(--topbar-height)); }
        .page-header { margin-bottom: 2.2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
        .page-header h1 { font-size: 2.1rem; font-weight: 700; color: var(--navy); margin-bottom: 0; display: flex; align-items: center; gap: 0.7rem; }
        .page-header h1 i { color: var(--secondary); font-size: 2.1rem; }
        .breadcrumb { background: none; padding: 0.2rem 0; margin: 0; font-size: 0.9rem; }
        .breadcrumb-item a { color: var(--warm-gray); text-decoration: none; font-weight: 500; }
        .breadcrumb-item a:hover { color: var(--primary); }
        .breadcrumb-item.active { color: var(--primary-dark); font-weight: 600; }
        .header-actions { display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; }
        .btn-add { background: var(--primary); color: white; padding: 0.7rem 1.8rem; border-radius: 40px; font-weight: 600; font-size: 0.95rem; border: none; transition: all 0.2s; display: flex; align-items: center; gap: 0.6rem; cursor: pointer; text-decoration: none; }
        .btn-add:hover { background: var(--primary-dark); transform: translateY(-2px); box-shadow: 0 10px 18px rgba(15,76,92,0.25); color: white; }
        .btn-add-category { background: white; color: var(--primary); padding: 0.7rem 1.6rem; border-radius: 40px; font-weight: 600; font-size: 0.95rem; border: 2px solid var(--primary); transition: all 0.2s; display: flex; align-items: center; gap: 0.6rem; cursor: pointer; }
        .btn-add-category:hover { background: var(--primary); color: white; transform: translateY(-2px); }

        /* ── STATS ────────────────────────────────────────────────────────── */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.8rem; margin-bottom: 2.5rem; }
        .stat-card { background: white; border-radius: 24px; padding: 1.6rem 1.5rem; border: 1px solid rgba(203,213,224,0.5); transition: all 0.25s; position: relative; overflow: hidden; box-shadow: 0 8px 20px rgba(15,76,92,0.04); display: flex; align-items: center; gap: 1.2rem; }
        .stat-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg,var(--primary),var(--secondary)); }
        .stat-card.success::before { background: var(--success); }
        .stat-card.info::before { background: var(--info); }
        .stat-card.warning::before { background: var(--warning); }
        .stat-card:hover { transform: translateY(-6px); box-shadow: 0 16px 28px rgba(15,76,92,0.08); }
        .stat-card .icon { width: 60px; height: 60px; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; background: #f0f5fa; color: var(--primary); border: 1px solid rgba(15,76,92,0.1); flex-shrink: 0; }
        .stat-card .label { font-size: 0.8rem; color: var(--warm-gray); margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.8px; font-weight: 700; }
        .stat-card .value { font-size: 1.9rem; font-weight: 700; color: var(--navy); line-height: 1.1; }

        /* ── CARD ─────────────────────────────────────────────────────────── */
        .card { background: white; border-radius: 24px; border: 1px solid rgba(203,213,224,0.5); box-shadow: 0 8px 20px rgba(15,76,92,0.02); margin-bottom: 1.8rem; }
        .card:hover { box-shadow: 0 14px 26px rgba(15,76,92,0.06); }
        .card-header { background: white; border-bottom: 1px solid rgba(203,213,224,0.5); padding: 1.35rem 1.8rem; border-radius: 24px 24px 0 0; }
        .card-body { padding: 1.6rem 1.8rem; }
        .card-title { font-size: 1.18rem; font-weight: 650; color: var(--navy); display: flex; align-items: center; gap: 0.6rem; }

        /* ── TABLE ────────────────────────────────────────────────────────── */
        .table-container { overflow-x: auto; }
        .table { margin: 0; border-collapse: separate; border-spacing: 0; }
        .table thead th { background: #f2f6fc; border-bottom: 2px solid var(--primary-light); font-weight: 600; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.8px; color: var(--primary-dark); padding: 1.1rem 1.2rem; white-space: nowrap; }
        .table tbody td { padding: 1.2rem 1.2rem; vertical-align: middle; border-bottom: 1px solid #edf2f7; color: var(--dark); background: white; }
        .table tbody tr:hover td { background-color: #fafcff; }

        /* ── Variant display ─────────────────────────────────────────────── */
        .variant-prices-compact { max-height: 180px; overflow-y: auto; scrollbar-width: thin; margin-bottom: 0.5rem; }
        .variant-prices-compact::-webkit-scrollbar { width: 3px; }
        .variant-prices-compact::-webkit-scrollbar-thumb { background: var(--primary-light); border-radius: 10px; }
        .variant-prices-compact > div { transition: all 0.2s ease; cursor: default; }
        .variant-prices-compact > div:hover { background: #f8f9fa; padding-left: 5px !important; margin-left: -5px; border-radius: 6px; }
        .btn-manage-variants { transition: all 0.2s ease; }
        .btn-manage-variants:hover { background: var(--primary) !important; color: white !important; transform: translateY(-1px); }
        .btn-manage-variants:hover i { color: white !important; }

        .price-missing-badge { display: inline-flex; align-items: center; gap: 0.4rem; background: rgba(214,64,69,0.1); color: var(--danger); border: 1px solid rgba(214,64,69,0.25); border-radius: 20px; padding: 0.3rem 0.8rem; font-size: 0.75rem; font-weight: 600; }

        /* ── ITEM IMAGE ───────────────────────────────────────────────────── */
        .item-img { width: 54px; height: 54px; object-fit: cover; border-radius: 14px; border: 1px solid var(--border); }
        .item-img-placeholder { width: 54px; height: 54px; display: flex; align-items: center; justify-content: center; background: #f2f6fc; border-radius: 14px; border: 1px solid var(--border); color: var(--primary-light); font-size: 1.2rem; }

        /* ── TAGS ─────────────────────────────────────────────────────────── */
        .category-tag { display: inline-block; padding: 0.35rem 1rem; background: rgba(15,76,92,0.08); color: var(--primary-dark); border-radius: 30px; font-size: 0.75rem; font-weight: 600; }

        /* ── BADGES ───────────────────────────────────────────────────────── */
        .badge { padding: 0.45rem 1rem; border-radius: 30px; font-weight: 600; font-size: 0.75rem; letter-spacing: 0.3px; }
        .bg-success { background: var(--success) !important; }
        .bg-danger  { background: var(--danger)  !important; }
        .bg-warning { background: var(--warning) !important; color: #1a202c; }
        .bg-info    { background: var(--info)    !important; }

        /* ── ACTION BUTTONS ───────────────────────────────────────────────── */
        .action-btns { display: flex; gap: 0.5rem; justify-content: flex-end; }
        .btn-icon { width: 38px; height: 38px; padding: 0; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; background: #f2f6fc; border: none; color: var(--primary); transition: all 0.15s; cursor: pointer; }
        .btn-icon:hover { background: var(--secondary); color: white; transform: scale(0.95); }
        .btn-icon.btn-edit { background: #fff3e0; color: #b85a1a; border: 1px solid rgba(244,185,66,0.2); }
        .btn-icon.btn-edit:hover { background: var(--warning); color: white; }
        .btn-icon.btn-delete { background: rgba(214,64,69,0.08); color: var(--danger); border: 1px solid rgba(214,64,69,0.15); }
        .btn-icon.btn-delete:hover { background: var(--danger); color: white; }

        /* ── MODAL ────────────────────────────────────────────────────────── */
        .modal { padding-right: 0 !important; }
        .modal-content { border-radius: 28px; border: none; box-shadow: 0 25px 40px rgba(0,0,0,0.12); }
        .modal-header { border-bottom: 1px solid var(--border); padding: 1.6rem 1.8rem; border-radius: 28px 28px 0 0; }
        .modal-body { padding: 1.8rem; }
        .modal-footer { border-top: 1px solid var(--border); padding: 1.5rem 1.8rem; border-radius: 0 0 28px 28px; }
        .modal-title { font-size: 1.3rem; font-weight: 700; color: var(--navy); display: flex; align-items: center; gap: 0.6rem; }

        /* ── FORM ─────────────────────────────────────────────────────────── */
        .form-label { font-weight: 600; color: var(--navy); margin-bottom: 0.5rem; font-size: 0.85rem; }
        .form-control, .form-select { border: 1px solid var(--border); border-radius: 14px; padding: 0.65rem 1rem; font-size: 0.9rem; transition: all 0.2s; }
        .form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(15,76,92,0.1); }
        .input-group-text { background: #f2f6fc; border: 1px solid var(--border); border-radius: 14px 0 0 14px; color: var(--primary-dark); font-weight: 500; }
        .form-control:disabled { background: #f0f4f8; color: #8a9bb0; cursor: not-allowed; }

        /* ── PRICING MODE BANNER ──────────────────────────────────────────── */
        .pricing-mode-banner { border-radius: 14px; padding: 0.85rem 1.1rem; font-size: 0.85rem; font-weight: 600; display: flex; align-items: center; gap: 0.6rem; transition: all 0.3s; }
        .pricing-mode-banner.mode-base { background: rgba(44,122,90,0.1); color: var(--success); border: 1px solid rgba(44,122,90,0.25); }
        .pricing-mode-banner.mode-variant { background: rgba(15,76,92,0.1); color: var(--primary-dark); border: 1px solid rgba(15,76,92,0.25); }
        .base-price-locked { opacity: 0.5; pointer-events: none; }
        .base-price-locked .form-control { background: #f0f4f8 !important; }

        /* ── IMAGE PREVIEW ────────────────────────────────────────────────── */
        .image-preview-container { border: 1px solid var(--border); border-radius: 16px; padding: 0.75rem; background: #fafcff; }
        .image-preview { max-width: 100%; max-height: 140px; border-radius: 14px; display: block; margin: 0 auto; }
        .image-preview-placeholder { border: 2px dashed var(--border); border-radius: 16px; padding: 0.75rem; background: #f8faff; }

        /* ── ALERTS ───────────────────────────────────────────────────────── */
        .alert { border-radius: 18px; border: none; padding: 1rem 1.5rem; margin-bottom: 1.8rem; }
        .alert-success { background: rgba(44,122,90,0.12); color: #1e4a3b; border-left: 6px solid var(--success); }
        .alert-danger  { background: rgba(214,64,69,0.08); color: #9e3a34; border-left: 6px solid var(--danger); }
        .alert-warning { background: rgba(244,185,66,0.12); color: #7a5a10; border-left: 6px solid var(--warning); }

        /* ── CATEGORY MODAL ───────────────────────────────────────────────── */
        .category-modal-icon { width: 52px; height: 52px; border-radius: 16px; background: rgba(15,76,92,0.08); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; color: var(--primary); flex-shrink: 0; }
        .char-counter { font-size: 0.75rem; color: var(--warm-gray); text-align: right; margin-top: 0.3rem; }
        .char-counter.over { color: var(--danger); font-weight: 600; }

        /* ── MANAGE VARIANTS MODAL ────────────────────────────────────────── */
        .variant-row { display: flex; align-items: center; justify-content: space-between; padding: 0.85rem 1rem; border-bottom: 1px solid #edf2f7; gap: 0.75rem; }
        .variant-row:last-child { border-bottom: none; }
        .variant-row:hover { background: #fafcff; }
        .variant-row-name { font-weight: 700; color: var(--navy); font-size: 0.92rem; }
        .variant-row-price { font-weight: 700; color: var(--success); font-size: 0.92rem; }
        .variant-row-cost { font-size: 0.75rem; color: var(--warm-gray); }
        .variant-row-actions { display: flex; gap: 0.35rem; flex-shrink: 0; }
        .btn-icon-sm { width: 30px; height: 30px; padding: 0; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; border: none; transition: all 0.15s; cursor: pointer; font-size: 0.75rem; }
        .btn-icon-sm.btn-edit { background: #fff3e0; color: #b85a1a; }
        .btn-icon-sm.btn-edit:hover { background: var(--warning); color: white; }
        .btn-icon-sm.btn-delete { background: rgba(214,64,69,0.08); color: var(--danger); }
        .btn-icon-sm.btn-delete:hover { background: var(--danger); color: white; }

        /* ── INLINE VARIANT FORM ──────────────────────────────────────────── */
        #variantInlineForm { border-top: 2px dashed var(--border); padding-top: 1.2rem; margin-top: 0.5rem; }
        .variant-form-title { font-size: 0.85rem; font-weight: 700; color: var(--navy); margin-bottom: 0.9rem; display: flex; align-items: center; gap: 0.4rem; }

        /* ── DROPDOWN ─────────────────────────────────────────────────────── */
        .dropdown-menu { border-radius: 20px; border: 1px solid var(--border); box-shadow: 0 12px 24px rgba(0,0,0,0.04); padding: 0.6rem; }
        .dropdown-item { border-radius: 14px; padding: 0.6rem 1.2rem; font-size: 0.9rem; }
        .dropdown-item:hover { background: var(--lavender); color: var(--primary-dark); }

        /* ── RESPONSIVE ───────────────────────────────────────────────────── */
        @media (max-width: 1199.98px) { .stats-grid { grid-template-columns: repeat(2,1fr); } }

        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; padding: 1.8rem; }
            .sidebar-overlay { position: fixed; top: var(--topbar-height); left: 0; right: 0; bottom: 0; background: rgba(15,76,92,0.2); backdrop-filter: blur(3px); z-index: 1025; display: none; }
            .sidebar-overlay.show { display: block; }
            .stats-grid { grid-template-columns: repeat(2,1fr); }
            .topbar { padding: 0 1rem; }
        }

        @media (max-width: 767.98px) {
            .main-content { padding: 1.2rem; }
            .stats-grid { grid-template-columns: 1fr; }
            .page-header h1 { font-size: 1.8rem; }
            .header-actions { width: 100%; }
            .btn-add, .btn-add-category { flex: 1; justify-content: center; }
            .topbar { padding: 0 0.85rem; }
            .logo-img-fallback { width: 36px; height: 36px; font-size: 1.1rem; border-radius: 10px; }
            .logo-text-main { font-size: 1.05rem; }
            .logo-text-sub { font-size: 0.62rem; }
            .user-btn { padding: 0.4rem 0.6rem !important; max-width: 160px; gap: 0.3rem; }
            .user-btn .user-name { font-size: 0.8rem; }
            .user-btn .user-avatar { width: 26px; height: 26px; font-size: 0.72rem; }
            .table thead { display: none; }
            .table tbody tr { display: block; margin-bottom: 1.2rem; border: 1px solid var(--border); border-radius: 20px; background: white; }
            .table tbody td { display: flex; justify-content: space-between; align-items: center; padding: 0.9rem 1.2rem; border-bottom: 1px solid #edf2f7; }
            .table tbody td:last-child { border-bottom: none; }
            .table tbody td::before { content: attr(data-label); font-weight: 700; color: var(--warm-gray); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }
            .action-btns { justify-content: flex-end; }
            .modal-dialog { margin: 0.5rem; }
        }

        @media (max-width: 480px) {
            .topbar { padding: 0 0.6rem; }
            .topbar-right { gap: 0.3rem; }
            .logo-text-sub { display: none; }
            .logo-text-main { font-size: 0.95rem; }
            .user-btn { max-width: 130px; padding: 0.38rem 0.55rem !important; }
            .user-btn .user-name { font-size: 0.78rem; }
            .main-content { padding: 1rem; }
        }

        .fade-in { animation: modernFade 0.4s ease forwards; }
        @keyframes modernFade { from { opacity: 0.6; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

<!-- ── TOPBAR ─────────────────────────────────────────────────────────────── -->
<div class="topbar">
    <div class="topbar-left">
        <button class="mobile-menu-btn d-lg-none" id="menuToggle"><i class="fas fa-bars"></i></button>
        <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="logo">
            <!--
                LOGO IMAGE PLACEHOLDER
                Replace the div below with:
                <img src="<?= $baseUrl ?>/assets/images/logo/logo.png" alt="Dine with Din"
                     style="width:44px;height:44px;border-radius:12px;object-fit:cover;">
            -->
            <div class="logo-img-fallback" title="Logo placeholder">
                <img src="<?= $baseUrl ?>/assets/images/logo/logo.png" alt="Dine with Din"
                     style="width:44px;height:44px;border-radius:12px;object-fit:cover;">
            </div>
            <div class="logo-text-wrap">
                <span class="logo-text-main">Dine with Din</span>
                <span class="logo-text-sub">Restaurant Management System</span>
            </div>
        </a>
    </div>

    <div class="topbar-right">
        <!-- User — avatar + full name, always visible -->
        <div class="dropdown">
            <button class="btn user-btn dropdown-toggle"
                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
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

<!-- ── SIDEBAR ────────────────────────────────────────────────────────────── -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-nav">
        <div class="nav-section-title">DASHBOARD</div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></div>
        <div class="nav-section-title">MANAGEMENT</div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_menu" class="nav-link active"><i class="fas fa-utensils"></i> Menu Items</a></div>
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

<!-- ── MAIN CONTENT ────────────────────────────────────────────────────────── -->
<div class="main-content" id="mainContent">

    <div class="page-header">
        <div>
            <h1><i class="fas fa-utensils me-2"></i>Menu Management</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/index.php?page=admin_dashboard">Home</a></li>
                    <li class="breadcrumb-item active">Menu Items</li>
                </ol>
            </nav>
        </div>
        <div class="header-actions">
            <button type="button" class="btn-add-category" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="fas fa-folder-plus"></i> Add Category
            </button>
            <button type="button" class="btn-add" data-bs-toggle="modal" data-bs-target="#addMenuItemModal">
                <i class="fas fa-plus-circle"></i> Add New Item
            </button>
        </div>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show fade-in">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show fade-in">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card primary fade-in">
            <div class="icon"><i class="fas fa-utensils"></i></div>
            <div>
                <div class="label">Total Items</div>
                <div class="value"><?= $totalItems ?? 0 ?></div>
            </div>
        </div>
        <div class="stat-card success fade-in" style="animation-delay:.1s">
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="label">Available Items</div>
                <div class="value"><?= count($availableItems ?? []) ?></div>
            </div>
        </div>
        <div class="stat-card info fade-in" style="animation-delay:.2s">
            <div class="icon"><i class="fas fa-percentage"></i></div>
            <div>
                <div class="label">Average Margin</div>
                <div class="value"><?= number_format($avgProfitMargin ?? 0, 1) ?>%</div>
            </div>
        </div>
        <div class="stat-card warning fade-in" style="animation-delay:.3s">
            <div class="icon"><i class="fas fa-chart-line"></i></div>
            <div>
                <div class="label">Total Profit Potential</div>
                <div class="value">₱<?= number_format($totalProfit ?? 0, 2) ?></div>
            </div>
        </div>
    </div>

    <!-- Menu Table -->
    <div class="table-container">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Item</th>
                    <th>Category</th>
                    <th>Price &amp; Variants</th>
                    <th>Profit Margin</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
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
                <tr <?= $needsPrice ? 'style="background:#fff8f8;"' : '' ?>>
                    <td data-label="Image">
                        <?php if (!empty($item['image_url'])): ?>
                            <img src="<?= $baseUrl . '/' . htmlspecialchars($item['image_url']) ?>"
                                 alt="<?= htmlspecialchars($item['item_name']) ?>" class="item-img">
                        <?php else: ?>
                            <div class="item-img-placeholder"><i class="fas fa-image"></i></div>
                        <?php endif; ?>
                    </td>

                    <td data-label="Item">
                        <div class="fw-semibold" style="color:var(--navy);"><?= htmlspecialchars($item['item_name']) ?></div>
                        <?php if (!empty($item['description'])): ?>
                            <small class="text-muted d-block"><?= htmlspecialchars(substr($item['description'], 0, 50)) ?><?= strlen($item['description']) > 50 ? '…' : '' ?></small>
                        <?php endif; ?>
                        <?php if ($needsPrice): ?>
                            <span class="price-missing-badge mt-1"><i class="fas fa-exclamation-triangle"></i> Needs base price</span>
                        <?php endif; ?>
                    </td>

                    <td data-label="Category">
                        <span class="category-tag"><?= htmlspecialchars($item['category_name']) ?></span>
                    </td>

                    <td data-label="Price / Variants">
                        <?php if ($hasVariants): ?>
                            <div class="variant-info">
                                <div class="variant-prices-compact mb-2">
                                    <?php
                                    $sorted = $activeVariants;
                                    usort($sorted, fn($a,$b) => $a['price'] <=> $b['price']);
                                    foreach ($sorted as $v): ?>
                                        <div class="d-flex justify-content-between align-items-center mb-1 pb-1" style="border-bottom:1px dashed #e9ecef;">
                                            <div>
                                                <span class="fw-semibold" style="font-size:0.85rem;"><?= htmlspecialchars($v['variant_name']) ?></span>
                                                <?php if (!$v['is_active']): ?>
                                                    <span class="badge bg-danger ms-1" style="font-size:0.65rem;">Inactive</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-end">
                                                <span class="fw-bold" style="color:var(--success);font-size:0.9rem;">₱<?= number_format($v['price'], 2) ?></span>
                                                <br>
                                                <small class="text-muted" style="font-size:0.7rem;">cost: ₱<?= number_format($v['cost_price'], 2) ?></small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if (empty($activeVariants)): ?>
                                        <div class="text-muted small fst-italic">All variants inactive</div>
                                    <?php endif; ?>
                                </div>
                                <button type="button"
                                        class="btn btn-sm w-100 d-flex align-items-center justify-content-center gap-2 btn-manage-variants"
                                        style="background:rgba(15,76,92,0.08);color:var(--primary);border:1px solid rgba(15,76,92,0.15);border-radius:20px;padding:0.4rem;font-weight:600;font-size:0.75rem;"
                                        onclick="openManageVariantsModal(<?= $item['item_id'] ?>, <?= htmlspecialchars(json_encode($item['item_name'])) ?>, <?= htmlspecialchars(json_encode($variants)) ?>)">
                                    <i class="fas fa-edit"></i>
                                    Manage Variants (<?= count($variants) ?> total, <?= count($activeVariants) ?> active)
                                </button>
                                <div class="mt-1 text-muted" style="font-size:0.72rem;">
                                    <i class="fas fa-info-circle me-1"></i>Pricing via variants — base price ignored
                                </div>
                            </div>
                        <?php elseif ($needsPrice): ?>
                            <div class="mb-2">
                                <span class="price-missing-badge"><i class="fas fa-exclamation-triangle"></i> No price set</span>
                                <div class="small text-muted mt-1">Edit this item to set a base price.</div>
                            </div>
                            <button type="button"
                                    class="btn btn-sm w-100 d-flex align-items-center justify-content-center gap-2"
                                    style="background:rgba(15,76,92,0.08);color:var(--primary);border:1px solid rgba(15,76,92,0.15);border-radius:20px;padding:0.4rem;font-weight:600;font-size:0.75rem;"
                                    onclick="openManageVariantsModal(<?= $item['item_id'] ?>, <?= htmlspecialchars(json_encode($item['item_name'])) ?>, [])">
                                <i class="fas fa-plus-circle"></i> Add Variants instead
                            </button>
                        <?php else: ?>
                            <div class="mb-2">
                                <div class="fw-semibold" style="color:var(--success);">₱<?= number_format($basePrice, 2) ?></div>
                                <div class="small text-muted"><i class="fas fa-tag me-1"></i>Base price (no variants)</div>
                                <div class="small text-muted"><i class="fas fa-box me-1"></i>Cost: ₱<?= number_format($costPrice, 2) ?></div>
                            </div>
                            <button type="button"
                                    class="btn btn-sm w-100 d-flex align-items-center justify-content-center gap-2"
                                    style="background:rgba(15,76,92,0.08);color:var(--primary);border:1px solid rgba(15,76,92,0.15);border-radius:20px;padding:0.4rem;font-weight:600;font-size:0.75rem;"
                                    onclick="openManageVariantsModal(<?= $item['item_id'] ?>, <?= htmlspecialchars(json_encode($item['item_name'])) ?>, [])">
                                <i class="fas fa-plus-circle"></i> Add Variants
                            </button>
                        <?php endif; ?>
                    </td>

                    <td data-label="Profit Margin">
                        <?php if ($hasVariants && !empty($activeVariants)): ?>
                            <?php
                            $margins = array_map(fn($v) =>
                                $v['price'] > 0 ? (($v['price'] - $v['cost_price']) / $v['price']) * 100 : 0,
                            $activeVariants);
                            $minM = min($margins); $maxM = max($margins); $avgM = array_sum($margins)/count($margins);
                            ?>
                            <div class="fw-semibold" style="color:var(--success);">
                                <?= $minM == $maxM ? number_format($minM,1).'%' : number_format($minM,1).'% – '.number_format($maxM,1).'%' ?>
                            </div>
                            <div class="small text-muted">Avg: <?= number_format($avgM,1) ?>%</div>
                        <?php elseif (!$hasVariants && $basePrice > 0): ?>
                            <?php
                            $profit = $basePrice - $costPrice;
                            $margin = ($profit / $basePrice) * 100;
                            $cls    = $margin >= 30 ? 'success' : ($margin >= 20 ? 'warning' : 'danger');
                            ?>
                            <div class="fw-semibold" style="color:var(--<?= $cls ?>);"><?= number_format($margin,1) ?>%</div>
                            <div class="small text-muted">Profit: ₱<?= number_format($profit,2) ?></div>
                        <?php else: ?>
                            <span class="text-muted small fst-italic">N/A</span>
                        <?php endif; ?>
                    </td>

                    <td data-label="Status">
                        <span class="badge bg-<?= $item['is_available'] ? 'success' : 'danger' ?>">
                            <?= $item['is_available'] ? 'Available' : 'Unavailable' ?>
                        </span>
                    </td>

                    <td data-label="Actions" class="text-end">
                        <div class="action-btns">
                            <a href="<?= $baseUrl ?>/index.php?page=manage_menu&edit=<?= $item['item_id'] ?>"
                               class="btn-icon btn-edit" title="Edit Item">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="btn-icon btn-delete"
                                    onclick="confirmDeleteItem(<?= $item['item_id'] ?>, '<?= htmlspecialchars(addslashes($item['item_name'])) ?>')"
                                    title="Delete Item">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- ═══════ MANAGE VARIANTS MODAL ═══════ -->
    <div class="modal fade" id="manageVariantsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-layer-group me-2" style="color:var(--secondary);"></i>
                        Variants — <span id="mvItemName"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="mx-3 mt-3 mb-0 p-3 rounded-3" id="mvPricingNotice"
                         style="background:rgba(15,76,92,0.07);border:1px solid rgba(15,76,92,0.15);font-size:0.82rem;color:var(--primary-dark);">
                        <i class="fas fa-info-circle me-2" style="color:var(--primary);"></i>
                        <strong>Variant pricing mode.</strong>
                        Once at least one variant exists, the item's base price is ignored.
                        All pricing comes from the variants below.
                    </div>
                    <div id="mvVariantList" style="min-height:60px;"></div>
                    <div id="variantInlineForm" class="px-4 pb-4 d-none">
                        <div class="variant-form-title">
                            <i class="fas fa-plus-circle" style="color:var(--secondary);"></i>
                            <span id="variantFormTitle">Add New Variant</span>
                        </div>
                        <form method="POST" action="<?= $baseUrl ?>/index.php?page=manage_menu" id="variantForm">
                            <input type="hidden" name="item_id"        id="vf_item_id">
                            <input type="hidden" name="variant_id"     id="vf_variant_id">
                            <input type="hidden" name="variant_action" id="vf_action" value="">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Variant Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="vf_name" name="variant_name"
                                           placeholder="e.g. Small, Large, Spicy" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Price <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" class="form-control" id="vf_price" name="price"
                                               step="0.01" min="0.01" placeholder="0.00" required>
                                    </div>
                                    <small class="text-muted" style="font-size:0.72rem;">Must be &gt; 0</small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Cost</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" class="form-control" id="vf_cost" name="cost_price"
                                               step="0.01" min="0" placeholder="0.00" value="0">
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex align-items-end pb-1">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="vf_active"
                                               name="is_active" value="1" checked>
                                        <label class="form-check-label small fw-bold" for="vf_active">Active</label>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2 mt-3 justify-content-end">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cancelVariantForm()">Cancel</button>
                                <button type="submit" class="btn btn-primary btn-sm" id="vf_submit_btn">
                                    <i class="fas fa-plus me-1"></i> Add Variant
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="showAddVariantForm()">
                        <i class="fas fa-plus-circle me-2"></i>Add Variant
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- DELETE VARIANT CONFIRM MODAL -->
    <div class="modal fade" id="deleteVariantModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="border-bottom:none;padding-bottom:0;">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center pt-0">
                    <div style="width:70px;height:70px;border-radius:22px;background:rgba(214,64,69,0.1);color:var(--danger);display:flex;align-items:center;justify-content:center;font-size:2rem;margin:0 auto 1.2rem;">
                        <i class="fas fa-trash"></i>
                    </div>
                    <h4 style="color:var(--navy);font-weight:700;" class="mb-2">Delete Variant?</h4>
                    <p class="text-muted mb-1">You are about to delete:</p>
                    <p id="deleteVariantName" style="color:var(--danger);font-size:1.05rem;font-weight:600;" class="mb-3"></p>
                    <div class="p-3 rounded-3 mb-1" style="background:rgba(214,64,69,0.06);border:1px dashed rgba(214,64,69,0.3);">
                        <small class="text-muted">
                            <i class="fas fa-exclamation-triangle me-1" style="color:var(--danger)"></i>
                            If this is the last variant, the item switches back to base-price mode and
                            <strong>you will need to set a base price</strong>.
                        </small>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:none;gap:0.75rem;">
                    <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="<?= $baseUrl ?>/index.php?page=manage_menu" style="flex:1;">
                        <input type="hidden" name="delete_variant" value="1">
                        <input type="hidden" name="variant_id" id="deleteVariantId">
                        <button type="submit" class="btn w-100"
                                style="background:var(--danger);color:white;border:none;border-radius:40px;padding:0.6rem;">
                            <i class="fas fa-trash me-2"></i> Yes, Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- DELETE MENU ITEM CONFIRM MODAL -->
    <div class="modal fade" id="deleteMenuItemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="border-bottom:none;padding-bottom:0;">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center pt-0">
                    <div style="width:70px;height:70px;border-radius:22px;background:rgba(214,64,69,0.1);color:var(--danger);display:flex;align-items:center;justify-content:center;font-size:2rem;margin:0 auto 1.2rem;">
                        <i class="fas fa-trash"></i>
                    </div>
                    <h4 style="color:var(--navy);font-weight:700;" class="mb-2">Delete Menu Item?</h4>
                    <p class="text-muted mb-1">You are about to delete:</p>
                    <p id="deleteMenuItemName" style="color:var(--danger);font-size:1.05rem;font-weight:600;" class="mb-3"></p>
                    <div class="p-3 rounded-3 mb-1" style="background:rgba(214,64,69,0.06);border:1px dashed rgba(214,64,69,0.3);">
                        <small class="text-muted">
                            <i class="fas fa-exclamation-triangle me-1" style="color:var(--danger)"></i>
                            This also deletes all variants. Cannot be undone.
                        </small>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:none;gap:0.75rem;">
                    <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="<?= $baseUrl ?>/index.php?page=manage_menu" style="flex:1;">
                        <input type="hidden" name="delete_menu_item" value="1">
                        <input type="hidden" name="item_id" id="deleteMenuItemId">
                        <button type="submit" class="btn w-100"
                                style="background:var(--danger);color:white;border:none;border-radius:40px;padding:0.6rem;">
                            <i class="fas fa-trash me-2"></i> Yes, Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ADD CATEGORY MODAL -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <div class="category-modal-icon me-2"><i class="fas fa-folder-plus"></i></div>
                        Add New Category
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="<?= $baseUrl ?>/index.php?page=manage_menu" id="addCategoryForm" novalidate>
                    <div class="modal-body">
                        <input type="hidden" name="add_category" value="1">
                        <div class="mb-1">
                            <label for="modal_category_name" class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="modal_category_name" name="category_name"
                                   placeholder="e.g., Hot Beverages, Snacks…" maxlength="80" required autocomplete="off">
                            <div class="char-counter" id="catCharCounter">0 / 80</div>
                            <div class="invalid-feedback">Please enter a category name.</div>
                        </div>
                        <div id="dupWarning" class="alert alert-danger py-2 px-3 mt-3 mb-0 d-none">
                            <i class="fas fa-exclamation-triangle me-1"></i> A category with this name already exists.
                        </div>
                        <div class="mt-4">
                            <div class="form-label mb-2">Existing Categories</div>
                            <?php if (empty($categories)): ?>
                                <p class="text-muted fst-italic" style="font-size:.85rem;">No categories yet.</p>
                            <?php else: ?>
                                <div style="max-height:220px;overflow-y:auto;border:1px solid var(--border);border-radius:14px;">
                                    <?php foreach ($categories as $cat): ?>
                                        <div class="d-flex align-items-center justify-content-between px-3 py-2" style="border-bottom:1px solid #edf2f7;">
                                            <span style="font-size:.88rem;color:var(--navy);font-weight:500;"><?= htmlspecialchars($cat['category_name']) ?></span>
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn-icon btn-edit" style="width:30px;height:30px;font-size:.75rem;"
                                                        onclick="catEditInline(<?= $cat['category_id'] ?>, <?= htmlspecialchars(json_encode($cat['category_name'])) ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="POST" action="<?= $baseUrl ?>/index.php?page=manage_menu" style="margin:0;"
                                                      onsubmit="return confirm('Delete \'<?= htmlspecialchars(addslashes($cat['category_name'])) ?>\'?')">
                                                    <input type="hidden" name="delete_category" value="1">
                                                    <input type="hidden" name="category_id" value="<?= $cat['category_id'] ?>">
                                                    <button type="submit" class="btn-icon" style="width:30px;height:30px;font-size:.75rem;background:#fff0f0;color:var(--danger);border:1px solid rgba(214,64,69,0.2);">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div id="catEditSection" class="mt-3 p-3 d-none" style="background:#f8faff;border:1px solid var(--border);border-radius:14px;">
                            <form method="POST" action="<?= $baseUrl ?>/index.php?page=manage_menu">
                                <input type="hidden" name="update_category" value="1">
                                <input type="hidden" name="category_id" id="editCatId">
                                <label class="form-label mb-1" style="font-size:.82rem;">Rename Category</label>
                                <div class="d-flex gap-2">
                                    <input type="text" class="form-control" name="category_name" id="editCatName" maxlength="80" required style="border-radius:10px;">
                                    <button type="submit" class="btn btn-primary btn-sm px-3" style="border-radius:10px;white-space:nowrap;"><i class="fas fa-save me-1"></i> Save</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" style="border-radius:10px;" onclick="document.getElementById('catEditSection').classList.add('d-none')">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="saveCategoryBtn">
                            <i class="fas fa-folder-plus me-2"></i>Add Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ADD MENU ITEM MODAL -->
    <div class="modal fade" id="addMenuItemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2" style="color:var(--secondary);"></i>Add New Menu Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data" id="addItemForm">
                    <div class="modal-body">
                        <input type="hidden" name="add_menu_item" value="1">
                        <div class="mb-3">
                            <label class="form-label">Item Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="item_name" placeholder="e.g., Caramel Macchiato" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="2" placeholder="Enter item description"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories ?? [] as $category): ?>
                                    <option value="<?= $category['category_id'] ?>"><?= htmlspecialchars($category['category_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="pricing-mode-banner mode-base mb-3" id="pricingModeBanner">
                            <i class="fas fa-tag"></i>
                            <span id="pricingModeText">Base price mode — enter a price below.</span>
                        </div>
                        <div id="basePriceSection" class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Base Price <span class="text-danger" id="basePriceRequired">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" id="basePriceInput" name="base_price"
                                           step="0.01" min="0.01" placeholder="0.00" required>
                                </div>
                                <small class="text-muted" id="basePriceHint">Required — must be &gt; 0</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Base Cost</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" id="baseCostInput" name="cost_price"
                                           step="0.01" min="0" placeholder="0.00" value="">
                                </div>
                                <small class="text-muted">0 or greater</small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0 fw-semibold">
                                    <i class="fas fa-layer-group me-1" style="color:var(--primary);"></i>
                                    Variants <span class="text-muted fw-normal">(optional)</span>
                                </label>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="addVariantBtn">
                                    <i class="fas fa-plus me-1"></i> Add Variant
                                </button>
                            </div>
                            <div id="variantsContainer"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Item Image</label>
                            <input type="file" class="form-control" id="modal_image" name="image" accept="image/*">
                            <small class="text-muted d-block mt-1">Max 2MB. JPG, PNG, GIF, WebP</small>
                            <div class="image-preview-placeholder mt-2 d-none" id="modalImagePreviewContainer">
                                <img id="modalImagePreview" class="image-preview" alt="Preview">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- EDIT MENU ITEM MODAL -->
    <?php if (isset($item_to_edit) && !empty($item_to_edit)): ?>
    <?php
        $edit_variants     = array_values(array_filter($menu_items ?? [], fn($i) => $i['item_id'] == $item_to_edit['item_id']));
        $edit_has_variants = !empty($edit_variants) && ($edit_variants[0]['has_variants'] ?? false);
    ?>
    <div class="modal fade" id="editMenuItemModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2" style="color:var(--secondary);"></i>Edit Menu Item</h5>
                    <a href="<?= $baseUrl ?>/index.php?page=manage_menu" class="btn-close"></a>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="item_id" value="<?= $item_to_edit['item_id'] ?>">
                        <input type="hidden" name="update_menu_item" value="1">
                        <div class="mb-3">
                            <label class="form-label">Item Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="item_name"
                                   value="<?= htmlspecialchars($item_to_edit['item_name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="2"><?= htmlspecialchars($item_to_edit['description'] ?? '') ?></textarea>
                        </div>
                        <?php if ($edit_has_variants): ?>
                            <div class="alert alert-warning mb-3 py-2 px-3" style="font-size:0.85rem;">
                                <i class="fas fa-layer-group me-2"></i>
                                <strong>This item uses variant pricing.</strong>
                                Base price is ignored while variants exist.
                                Manage variants from the main table.
                            </div>
                        <?php endif; ?>
                        <div class="row g-3 mb-3" id="editBasePriceSection" <?= $edit_has_variants ? 'style="opacity:0.5;pointer-events:none;"' : '' ?>>
                            <div class="col-md-6">
                                <label class="form-label">Base Price <?= !$edit_has_variants ? '<span class="text-danger">*</span>' : '' ?></label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" name="base_price"
                                           value="<?= $edit_has_variants ? '' : htmlspecialchars($item_to_edit['base_price'] ?? '') ?>"
                                           step="0.01" min="0"
                                           <?= !$edit_has_variants ? 'required' : 'placeholder="N/A — variants active"' ?>>
                                </div>
                                <small class="text-muted"><?= $edit_has_variants ? 'Overridden by variants' : 'Used only if no variants exist' ?></small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Base Cost</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" name="cost_price"
                                           value="<?= $edit_has_variants ? '' : htmlspecialchars($item_to_edit['cost_price'] ?? 0) ?>"
                                           step="0.01" min="0"
                                           <?= $edit_has_variants ? 'placeholder="N/A — variants active"' : '' ?>>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories ?? [] as $category): ?>
                                    <option value="<?= $category['category_id'] ?>"
                                            <?= ($item_to_edit['category_id'] == $category['category_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['category_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Item Image</label>
                            <?php if (!empty($item_to_edit['image_url'])): ?>
                                <div class="image-preview-container mb-3">
                                    <img src="<?= $baseUrl . '/' . htmlspecialchars($item_to_edit['image_url']) ?>" alt="Current" class="image-preview">
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="delete_image" value="1" id="deleteImage">
                                    <label class="form-check-label text-danger small" for="deleteImage">Remove current image</label>
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="edit_image" name="image" accept="image/*">
                            <small class="text-muted d-block mt-1">Max 2MB. Leave empty to keep current image.</small>
                            <div class="image-preview-placeholder mt-2 d-none" id="editImagePreviewContainer">
                                <img id="editImagePreview" class="image-preview" alt="Preview">
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="edit_is_available"
                                       name="is_available" value="1"
                                       <?= $item_to_edit['is_available'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="edit_is_available">Available for ordering</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="<?= $baseUrl ?>/index.php?page=manage_menu" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Item</button>
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

</div><!-- /main-content -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ── Sidebar ───────────────────────────────────────────────────
document.getElementById('menuToggle')?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('show');
    document.getElementById('sidebarOverlay').classList.toggle('show');
});
document.getElementById('sidebarOverlay')?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.remove('show');
    document.getElementById('sidebarOverlay').classList.remove('show');
});

// ── Image previews ────────────────────────────────────────────
document.getElementById('modal_image')?.addEventListener('change', function(e) {
    const file = e.target.files[0]; if (!file) return;
    const r = new FileReader();
    r.onload = e => {
        document.getElementById('modalImagePreview').src = e.target.result;
        document.getElementById('modalImagePreviewContainer').classList.remove('d-none');
    };
    r.readAsDataURL(file);
});
document.getElementById('edit_image')?.addEventListener('change', function(e) {
    const file = e.target.files[0]; if (!file) return;
    const r = new FileReader();
    r.onload = e => {
        document.getElementById('editImagePreview').src = e.target.result;
        document.getElementById('editImagePreviewContainer').classList.remove('d-none');
    };
    r.readAsDataURL(file);
});

// ── Delete menu item ──────────────────────────────────────────
function confirmDeleteItem(id, name) {
    document.getElementById('deleteMenuItemId').value = id;
    document.getElementById('deleteMenuItemName').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteMenuItemModal')).show();
}

// ── Delete variant ────────────────────────────────────────────
function confirmDeleteVariant(id, name) {
    document.getElementById('deleteVariantId').value = id;
    document.getElementById('deleteVariantName').textContent = name;
    bootstrap.Modal.getInstance(document.getElementById('manageVariantsModal'))?.hide();
    setTimeout(() => new bootstrap.Modal(document.getElementById('deleteVariantModal')).show(), 250);
}

// ── Add Item Modal — Pricing Mode Logic ───────────────────────
let variantCounter = 0;

function setBasePriceMode(hasVariants) {
    const banner    = document.getElementById('pricingModeBanner');
    const section   = document.getElementById('basePriceSection');
    const bpInput   = document.getElementById('basePriceInput');
    const bcInput   = document.getElementById('baseCostInput');
    const bpHint    = document.getElementById('basePriceHint');
    const bpReq     = document.getElementById('basePriceRequired');
    const modeText  = document.getElementById('pricingModeText');

    if (hasVariants) {
        banner.className  = 'pricing-mode-banner mode-variant mb-3';
        banner.querySelector('i').className = 'fas fa-layer-group';
        modeText.textContent = 'Variant pricing active — base price is ignored and will be set to NULL.';
        section.classList.add('base-price-locked');
        bpInput.disabled  = true; bcInput.disabled  = true;
        bpInput.value     = ''; bcInput.value     = '';
        bpInput.removeAttribute('required'); bpInput.removeAttribute('min');
        bpReq.style.display = 'none';
        bpHint.textContent  = 'Overridden by variants — will be stored as NULL';
        bpHint.style.color  = 'var(--primary)';
    } else {
        banner.className  = 'pricing-mode-banner mode-base mb-3';
        banner.querySelector('i').className = 'fas fa-tag';
        modeText.textContent = 'Base price mode — enter a selling price below.';
        section.classList.remove('base-price-locked');
        bpInput.disabled  = false; bcInput.disabled  = false;
        bpInput.setAttribute('required', 'required');
        bpInput.setAttribute('min', '0.01');
        bpReq.style.display = '';
        bpHint.textContent  = 'Required — must be > 0';
        bpHint.style.color  = '';
    }
}

function getVariantCount() {
    return document.querySelectorAll('#variantsContainer .variant-row-add').length;
}
function refreshPricingMode() { setBasePriceMode(getVariantCount() > 0); }

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function addVariantRow(name='',price='',cost='',isActive=true) {
    const container = document.getElementById('variantsContainer');
    const rowId     = `vrow_${variantCounter++}`;
    const checked   = isActive ? 'checked' : '';
    container.insertAdjacentHTML('beforeend', `
        <div class="variant-row-add card mb-2" id="${rowId}" style="border-radius:14px;border:1px solid var(--border);">
            <div class="card-body py-2 px-3">
                <div class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <input type="text" class="form-control form-control-sm" name="variant_names[]"
                               placeholder="Name (e.g. Small)" value="${escHtml(name)}" required>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control" name="variant_prices[]"
                                   step="0.01" min="0.01" placeholder="Price" value="${escHtml(price)}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control" name="variant_costs[]"
                                   step="0.01" min="0" placeholder="Cost (opt.)" value="${escHtml(cost)}">
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-center gap-2">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" name="variant_active[]" value="1" ${checked}>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger ms-auto"
                                onclick="removeVariantRow('${rowId}')" title="Remove variant">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
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

document.getElementById('addMenuItemModal')?.addEventListener('hidden.bs.modal', function () {
    document.getElementById('variantsContainer').innerHTML = '';
    variantCounter = 0;
    document.getElementById('addItemForm').reset();
    document.getElementById('modalImagePreviewContainer').classList.add('d-none');
    refreshPricingMode();
});
document.getElementById('addMenuItemModal')?.addEventListener('shown.bs.modal', function () {
    refreshPricingMode();
});

// ── Manage Variants Modal ─────────────────────────────────────
let _mvCurrentItemId   = null;
let _mvCurrentItemName = null;

function esc(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function openManageVariantsModal(itemId, itemName, variants) {
    _mvCurrentItemId   = itemId;
    _mvCurrentItemName = itemName;
    document.getElementById('mvItemName').textContent = itemName;
    renderVariantList(variants);
    cancelVariantForm();
    new bootstrap.Modal(document.getElementById('manageVariantsModal')).show();
}

function renderVariantList(variants) {
    const container = document.getElementById('mvVariantList');
    if (!variants || variants.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4" style="color:var(--warm-gray);">
                <i class="fas fa-layer-group fa-2x mb-2" style="opacity:.3;"></i>
                <p class="mb-0 small">No variants yet. Click "Add Variant" to create size or type options.</p>
                <p class="small text-muted mt-1">Until a variant is added, the item uses its base price.</p>
            </div>`;
        return;
    }
    let html = '';
    variants.forEach(v => {
        const profit = v.price - v.cost_price;
        const margin = v.price > 0 ? ((profit / v.price) * 100).toFixed(1) : '0.0';
        const statusBadge = v.is_active
            ? `<span class="badge" style="background:rgba(44,122,90,0.12);color:var(--success);">Active</span>`
            : `<span class="badge" style="background:rgba(214,64,69,0.1);color:var(--danger);">Inactive</span>`;
        html += `
        <div class="variant-row">
            <div style="flex:1;min-width:0;">
                <div class="variant-row-name">${esc(v.variant_name)}</div>
                <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                    <span class="variant-row-price">₱${parseFloat(v.price).toFixed(2)}</span>
                    <span class="variant-row-cost">Cost: ₱${parseFloat(v.cost_price).toFixed(2)}</span>
                    <span class="variant-row-cost">Margin: ${margin}%</span>
                </div>
            </div>
            <div>${statusBadge}</div>
            <div class="variant-row-actions">
                <button type="button" class="btn-icon-sm btn-edit" title="Edit"
                        onclick="showEditVariantForm(${v.variant_id}, '${v.variant_name.replace(/'/g,"\\'")}', ${v.price}, ${v.cost_price}, ${v.is_active ? 1 : 0})">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="btn-icon-sm btn-delete" title="Delete"
                        onclick="confirmDeleteVariant(${v.variant_id}, '${v.variant_name.replace(/'/g,"\\'")}')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>`;
    });
    container.innerHTML = html;
}

function showAddVariantForm() {
    document.getElementById('variantFormTitle').textContent = 'Add New Variant';
    document.getElementById('vf_submit_btn').innerHTML = '<i class="fas fa-plus me-1"></i> Add Variant';
    document.getElementById('vf_item_id').value   = _mvCurrentItemId;
    document.getElementById('vf_variant_id').value = '';
    document.getElementById('vf_name').value  = '';
    document.getElementById('vf_price').value = '';
    document.getElementById('vf_cost').value  = '0';
    document.getElementById('vf_active').checked = true;
    document.getElementById('vf_action').value = 'add';
    document.getElementById('variantInlineForm').classList.remove('d-none');
    document.getElementById('vf_name').focus();
}

function showEditVariantForm(variantId, name, price, cost, isActive) {
    document.getElementById('variantFormTitle').textContent = 'Edit Variant';
    document.getElementById('vf_submit_btn').innerHTML = '<i class="fas fa-save me-1"></i> Update Variant';
    document.getElementById('vf_item_id').value   = _mvCurrentItemId;
    document.getElementById('vf_variant_id').value = variantId;
    document.getElementById('vf_name').value  = name;
    document.getElementById('vf_price').value = price;
    document.getElementById('vf_cost').value  = cost;
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

// ── Category modal ────────────────────────────────────────────
(function () {
    const catNameInput    = document.getElementById('modal_category_name');
    const charCounter     = document.getElementById('catCharCounter');
    const dupWarning      = document.getElementById('dupWarning');
    const saveCategoryBtn = document.getElementById('saveCategoryBtn');
    const addCategoryForm = document.getElementById('addCategoryForm');

    const existingCategories = new Set(<?php
        echo json_encode(array_map(fn($c) => strtolower($c['category_name']), $categories ?? []));
    ?>);

    catNameInput?.addEventListener('input', function () {
        const len = this.value.length;
        charCounter.textContent = `${len} / 80`;
        charCounter.classList.toggle('over', len >= 80);
        const isDup = existingCategories.has(this.value.trim().toLowerCase());
        dupWarning.classList.toggle('d-none', !isDup);
        catNameInput.classList.remove('is-invalid');
    });

    saveCategoryBtn?.addEventListener('click', function () {
        const name = catNameInput.value.trim();
        if (!name) { catNameInput.classList.add('is-invalid'); catNameInput.focus(); return; }
        if (existingCategories.has(name.toLowerCase())) { dupWarning.classList.remove('d-none'); catNameInput.focus(); return; }
        catNameInput.classList.remove('is-invalid');
        addCategoryForm.submit();
    });

    catNameInput?.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); saveCategoryBtn.click(); }
    });

    document.getElementById('addCategoryModal')?.addEventListener('hidden.bs.modal', function () {
        addCategoryForm.reset();
        catNameInput.classList.remove('is-invalid');
        dupWarning.classList.add('d-none');
        charCounter.textContent = '0 / 80';
        charCounter.classList.remove('over');
    });
})();

function catEditInline(id, name) {
    document.getElementById('editCatId').value   = id;
    document.getElementById('editCatName').value = name;
    document.getElementById('catEditSection').classList.remove('d-none');
    document.getElementById('editCatName').focus();
}

// ── Auto-dismiss alerts ───────────────────────────────────────
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(a => { try { new bootstrap.Alert(a).close(); } catch {} });
}, 6000);

// ── Clean up Bootstrap modal artefacts ───────────────────────
document.addEventListener('hidden.bs.modal', function () {
    if (document.querySelector('.modal.show')) return;
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
});
</script>
</body>
</html>