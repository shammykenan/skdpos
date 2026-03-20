<?php
// Pure view: expects $menu_items, $addons_map, $categories, $_SESSION['success'], $_SESSION['error']
// Also expects $stock_data and $addon_stock_data from controller with full ingredient details
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5">
    <title>POS - Cafeteria POS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
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
            --mobile-bottom-bar: 68px;
            --cart-badge-color: #ff6b4a;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--light-bg);
            color: var(--dark);
            font-size: 14px;
            line-height: 1.5;
        }

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

        /* ─── SIDEBAR ─── */
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

        .sidebar::-webkit-scrollbar { width: 5px; }
        .sidebar::-webkit-scrollbar-thumb { background: var(--border); border-radius: 8px; }

        .sidebar-nav { padding: 2rem 0.75rem; }

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

        .nav-item { margin: 0.2rem 0.6rem; }

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

        .nav-link:hover { background: var(--lavender); color: var(--primary-dark); }
        .nav-link.active { background: var(--primary); color: white; box-shadow: 0 6px 12px rgba(15, 76, 92, 0.2); }
        .nav-link i { width: 24px; margin-right: 0.85rem; font-size: 1.2rem; text-align: center; }

        /* ─── MAIN CONTENT ─── */
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--topbar-height);
            padding: 2.2rem 2.2rem;
            min-height: calc(100vh - var(--topbar-height));
            transition: margin-left 0.2s;
        }

        /* ─── PAGE HEADER ─── */
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

        .page-header h1 i { color: var(--secondary); font-size: 2.1rem; }

        .breadcrumb { background: none; padding: 0.2rem 0; margin: 0; font-size: 0.9rem; }
        .breadcrumb-item a { color: var(--warm-gray); text-decoration: none; font-weight: 500; }
        .breadcrumb-item a:hover { color: var(--primary); }
        .breadcrumb-item.active { color: var(--primary-dark); font-weight: 600; }

        /* ─── CARDS ─── */
        .card {
            background: white;
            border-radius: 24px;
            border: 1px solid rgba(203,213,224,0.5);
            box-shadow: 0 8px 20px rgba(15,76,92,0.02);
            margin-bottom: 1.8rem;
            transition: box-shadow 0.2s;
        }
        .card:hover { box-shadow: 0 14px 26px rgba(15,76,92,0.06); }
        .card-header {
            background: white;
            border-bottom: 1px solid rgba(203,213,224,0.5);
            padding: 1.35rem 1.8rem;
            border-radius: 24px 24px 0 0;
        }
        .card-body { padding: 1.6rem 1.8rem; }
        .card-title { font-size: 1.18rem; font-weight: 650; color: var(--navy); display: flex; align-items: center; gap: 0.6rem; }

        /* ─── CATEGORY TABS ─── */
        .category-tabs-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            margin-bottom: 1.4rem;
            padding-bottom: 2px;
        }
        .category-tabs-wrapper::-webkit-scrollbar { display: none; }
        .category-tabs { display: flex; gap: 0.5rem; white-space: nowrap; flex-wrap: nowrap; min-width: max-content; }
        .category-tab {
            padding: 0.5rem 1.1rem;
            border: 1.5px solid var(--border);
            background: white; color: var(--warm-gray);
            border-radius: 40px; cursor: pointer;
            transition: all 0.2s; font-weight: 600; font-size: 0.82rem;
            flex-shrink: 0;
        }
        .category-tab:hover { border-color: var(--primary); color: var(--primary); }
        .category-tab.active { background: var(--primary); color: white; border-color: var(--primary); }

        /* ─── MENU GRID ─── */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1.2rem;
        }
        .menu-item-card {
            background: white;
            border: 1.5px solid rgba(226,232,240,0.7);
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.22s;
            cursor: pointer;
            position: relative;
            box-shadow: 0 2px 8px rgba(15,76,92,0.02);
        }
        .menu-item-card:hover { transform: translateY(-3px); box-shadow: 0 10px 22px rgba(15,76,92,0.1); border-color: var(--primary-light); }
        .menu-item-card:active { transform: scale(0.98); }
        .menu-item-card.out-of-stock { opacity: 0.55; cursor: not-allowed; filter: grayscale(0.4); }
        .menu-item-card.out-of-stock:hover { transform: none; box-shadow: 0 2px 8px rgba(15,76,92,0.02); }
        .menu-item-image { width: 100%; height: 140px; object-fit: cover; }
        .menu-item-placeholder {
            width: 100%; height: 140px;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(145deg, #edf2f7, #f7fafc);
            color: var(--primary-light); font-size: 2.5rem;
        }
        .menu-item-body { padding: 1rem; }
        .menu-item-name { font-weight: 700; font-size: 0.92rem; color: var(--navy); margin-bottom: 0.25rem; }
        .menu-item-price { font-size: 1.1rem; font-weight: 700; color: var(--success); margin-bottom: 0.4rem; }
        .menu-item-description {
            font-size: 0.72rem; color: var(--warm-gray);
            margin-bottom: 0.4rem;
            display: -webkit-box; line-clamp: 2;
            -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }
        .out-of-stock-badge {
            position: absolute; top: 0.75rem; right: 0.75rem;
            background: var(--danger); color: white;
            padding: 0.3rem 0.8rem; border-radius: 40px;
            font-size: 0.65rem; font-weight: 700; letter-spacing: 0.5px;
            z-index: 5;
        }
        .low-stock-badge {
            position: absolute; top: 0.75rem; right: 0.75rem;
            background: var(--warning); color: #1a202c;
            padding: 0.3rem 0.8rem; border-radius: 40px;
            font-size: 0.65rem; font-weight: 700; z-index: 5;
        }

        /* ─── DESKTOP CART ─── */
        .cart-container { position: sticky; top: calc(var(--topbar-height) + 1.5rem); }
        .cart-card { border-radius: 20px; overflow: hidden; }
        .cart-items { max-height: 320px; overflow-y: auto; }
        .cart-items::-webkit-scrollbar { width: 4px; }
        .cart-items::-webkit-scrollbar-thumb { background: var(--border); border-radius: 10px; }
        .cart-item {
            padding: 1rem 1.2rem;
            border-bottom: 1px solid #f0f4f8;
            transition: background-color 0.15s;
        }
        .cart-item:last-child { border-bottom: none; }
        .cart-item.insufficient-stock { background-color: rgba(214,64,69,0.04); border-left: 3px solid var(--danger); }
        .cart-item-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.4rem; }
        .cart-item-name { font-weight: 700; font-size: 0.88rem; color: var(--navy); }
        .cart-item-remove { background: none; border: none; color: var(--danger); cursor: pointer; padding: 0.2rem; font-size: 0.9rem; }
        .cart-item-remove:hover { transform: scale(1.1); }
        .cart-item-price { color: var(--success); font-weight: 700; font-size: 0.88rem; }
        .cart-item-qty { display: flex; align-items: center; gap: 0.6rem; margin-top: 0.6rem; }
        .qty-btn {
            width: 28px; height: 28px;
            border: 1.5px solid var(--border); background: white;
            border-radius: 8px; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.15s; color: var(--warm-gray); font-size: 0.8rem;
        }
        .qty-btn:hover:not(:disabled) { border-color: var(--primary); background: var(--primary); color: white; }
        .qty-btn:disabled { opacity: 0.35; cursor: not-allowed; }
        .qty-input { width: 48px; text-align: center; border: 1.5px solid var(--border); border-radius: 8px; padding: 0.3rem; font-weight: 600; font-size: 0.85rem; }
        .addon-item { font-size: 0.72rem; color: var(--warm-gray); padding-left: 0.7rem; margin-top: 0.3rem; border-left: 2px solid var(--primary-light); }

        /* ─── CART NOTE DISPLAY ─── */
        .cart-item-note {
            display: flex;
            align-items: flex-start;
            gap: 0.4rem;
            margin-top: 0.45rem;
            padding: 0.4rem 0.65rem;
            background: rgba(254, 228, 64, 0.15);
            border: 1px dashed rgba(244, 185, 66, 0.5);
            border-radius: 8px;
            font-size: 0.72rem;
            color: #7a5c00;
            cursor: pointer;
            transition: background 0.15s;
        }
        .cart-item-note:hover { background: rgba(254, 228, 64, 0.28); }
        .cart-item-note i { font-size: 0.75rem; margin-top: 1px; flex-shrink: 0; color: #b87a1a; }
        .cart-item-note-text { flex: 1; font-style: italic; word-break: break-word; }
        .cart-item-note-empty {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            margin-top: 0.4rem;
            font-size: 0.72rem;
            color: var(--warm-gray);
            opacity: 0.6;
            cursor: pointer;
            transition: opacity 0.15s;
        }
        .cart-item-note-empty:hover { opacity: 1; color: var(--primary); }

        /* ─── INLINE NOTE EDIT ─── */
        .cart-item-note-editor {
            margin-top: 0.45rem;
        }
        .cart-item-note-editor textarea {
            width: 100%;
            border: 1.5px solid var(--primary-light);
            border-radius: 10px;
            padding: 0.4rem 0.6rem;
            font-size: 0.78rem;
            resize: none;
            outline: none;
            font-family: inherit;
            color: var(--dark);
            background: #fafcff;
            transition: border-color 0.15s;
        }
        .cart-item-note-editor textarea:focus { border-color: var(--primary); }
        .cart-item-note-editor .note-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.4rem;
            margin-top: 0.3rem;
        }
        .note-save-btn {
            padding: 0.25rem 0.75rem;
            font-size: 0.72rem;
            border-radius: 20px;
            background: var(--primary);
            color: white;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.15s;
        }
        .note-save-btn:hover { background: var(--primary-dark); }
        .note-cancel-btn {
            padding: 0.25rem 0.65rem;
            font-size: 0.72rem;
            border-radius: 20px;
            background: white;
            color: var(--warm-gray);
            border: 1px solid var(--border);
            cursor: pointer;
            font-weight: 600;
            transition: all 0.15s;
        }
        .note-cancel-btn:hover { background: var(--lavender); }

        /* ─── NOTE FIELD IN MODAL ─── */
        .note-field-wrapper {
            margin-top: 1rem;
            padding: 1rem;
            background: rgba(254, 228, 64, 0.08);
            border: 1.5px dashed rgba(244, 185, 66, 0.55);
            border-radius: 14px;
        }
        .note-field-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.82rem;
            font-weight: 700;
            color: #7a5c00;
            margin-bottom: 0.5rem;
        }
        .note-field-label i { color: #b87a1a; }
        .note-field-hint {
            font-size: 0.7rem;
            color: var(--warm-gray);
            margin-top: 0.35rem;
        }
        #modalItemNote {
            width: 100%;
            border: 1.5px solid rgba(244, 185, 66, 0.5);
            border-radius: 10px;
            padding: 0.5rem 0.75rem;
            font-size: 0.85rem;
            resize: none;
            font-family: inherit;
            background: white;
            color: var(--dark);
            transition: border-color 0.15s;
        }
        #modalItemNote:focus {
            outline: none;
            border-color: var(--warning);
            box-shadow: 0 0 0 3px rgba(244,185,66,0.15);
        }
        #modalItemNote::placeholder { color: #b0a070; font-style: italic; }

        /* ─── CART SUMMARY ─── */
        .cart-summary { padding: 1rem 1.2rem; background: #f4f7fb; border-radius: 14px; margin: 0.8rem 1.2rem 1rem; }
        .cart-summary-row { display: flex; justify-content: space-between; margin-bottom: 0.4rem; font-size: 0.88rem; }
        .cart-summary-row.total { font-size: 1.05rem; font-weight: 700; color: var(--navy); padding-top: 0.6rem; border-top: 2px solid var(--border); margin-top: 0.5rem; margin-bottom: 0; }

        /* ─── EMPTY CART ─── */
        .empty-cart { text-align: center; padding: 2rem 1rem; color: var(--warm-gray); }
        .empty-cart i { font-size: 3rem; margin-bottom: 0.75rem; color: #d0d8e4; display: block; }

        /* ─── ORDER TYPE ─── */
        .order-type-container { display: flex; gap: 0.6rem; }
        .order-type-btn {
            flex: 1; padding: 0.65rem;
            border: 1.5px solid var(--border);
            background: white; color: var(--warm-gray);
            border-radius: 12px; cursor: pointer;
            transition: all 0.2s; font-weight: 600; font-size: 0.85rem;
            display: flex; align-items: center; justify-content: center; gap: 0.5rem;
        }
        .order-type-btn:hover { border-color: var(--primary); color: var(--primary); }
        .order-type-btn.active { background: var(--primary); color: white; border-color: var(--primary); }

        /* ─── FORMS ─── */
        .form-label { font-weight: 600; color: var(--navy); margin-bottom: 0.4rem; font-size: 0.82rem; }
        .form-control { border: 1.5px solid var(--border); border-radius: 12px; padding: 0.6rem 0.9rem; font-size: 0.88rem; transition: all 0.2s; }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(15,76,92,0.1); outline: none; }

        /* ─── BUTTONS ─── */
        .btn { padding: 0.6rem 1.4rem; border-radius: 40px; font-weight: 600; font-size: 0.88rem; transition: all 0.18s; }
        .btn-primary { background: var(--primary); border: none; color: white; }
        .btn-primary:hover:not(:disabled) { background: var(--primary-dark); transform: translateY(-1px); box-shadow: 0 6px 14px rgba(15,76,92,0.25); }
        .btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }
        .btn-success { background: var(--success); border: none; color: white; }
        .btn-success:hover:not(:disabled) { background: #1f5d43; transform: translateY(-1px); box-shadow: 0 6px 14px rgba(44,122,90,0.25); }
        .btn-success:disabled { opacity: 0.5; cursor: not-allowed; }
        .btn-outline-secondary { border: 1.5px solid var(--border); color: var(--warm-gray); background: white; }
        .btn-outline-secondary:hover { background: var(--lavender); border-color: var(--warm-gray); }

        /* ─── CHECKOUT SECTION ─── */
        .checkout-section { padding: 1rem 1.2rem; }

        /* ─── SUBMIT BTN ─── */
        .submit-btn-wrap { padding: 0 1.2rem 1.2rem; }
        #submitOrderBtn { border-radius: 14px; padding: 0.8rem; font-size: 0.95rem; width: 100%; }
        #submitDisabledMessage { padding: 0 1.2rem 0.8rem; }

        /* ─── STOCK WARNINGS ─── */
        .stock-warning {
            display: flex; align-items: center; gap: 0.4rem;
            padding: 0.4rem 0.6rem;
            background: rgba(244,185,66,0.12); color: #b87a1a;
            border-radius: 8px; font-size: 0.7rem; margin-top: 0.4rem;
        }
        .stock-warning-danger { background: rgba(214,64,69,0.08); color: var(--danger); }

        /* ─── ALERTS ─── */
        .alert { border-radius: 14px; border: none; padding: 0.9rem 1.3rem; margin-bottom: 1.5rem; }
        .alert-success { background: rgba(44,122,90,0.1); color: #1e4a3b; border-left: 5px solid var(--success); }
        .alert-danger { background: rgba(214,64,69,0.07); color: #9e3a34; border-left: 5px solid var(--danger); }

        /* ─── MODALS ─── */
        .modal { padding-right: 0 !important; }
        .modal-content { border-radius: 24px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.12); }
        .modal-header { border-bottom: 1px solid var(--border); padding: 1.4rem 1.6rem; border-radius: 24px 24px 0 0; }
        .modal-body { padding: 1.6rem; }
        .modal-footer { border-top: 1px solid var(--border); padding: 1.2rem 1.6rem; border-radius: 0 0 24px 24px; }
        .modal-title { font-size: 1.15rem; font-weight: 700; color: var(--navy); display: flex; align-items: center; gap: 0.5rem; }
        .modal-icon { width: 56px; height: 56px; border-radius: 18px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 1.6rem; }
        .modal-icon.danger { background: rgba(214,64,69,0.1); color: var(--danger); }
        .modal-icon.primary { background: rgba(15,76,92,0.1); color: var(--primary); }
        .modal-icon.success { background: rgba(44,122,90,0.1); color: var(--success); }

        /* ─── ADDON CHECKBOXES ─── */
        .addon-checkbox {
            display: flex; align-items: center; gap: 0.7rem;
            padding: 0.85rem; border: 1.5px solid var(--border);
            border-radius: 14px; margin-bottom: 0.6rem; transition: all 0.2s;
        }
        .addon-checkbox:hover { background: #f4f7fb; border-color: var(--primary-light); }
        .addon-checkbox input[type="checkbox"] { width: 18px; height: 18px; cursor: pointer; accent-color: var(--primary); }
        .addon-checkbox label { flex: 1; cursor: pointer; margin: 0; font-size: 0.88rem; font-weight: 500; }
        .addon-qty-control { display: flex; align-items: center; gap: 0.4rem; }
        .addon-qty-control input { width: 65px; text-align: center; padding: 0.3rem; border: 1.5px solid var(--border); border-radius: 10px; }

        /* ═══════════════════════════════════════════════
           MOBILE STYLES (≤ 991px)
        ═══════════════════════════════════════════════ */
        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .sidebar-overlay {
                position: fixed; top: var(--topbar-height); left: 0; right: 0; bottom: 0;
                background: rgba(15,76,92,0.25); backdrop-filter: blur(2px);
                z-index: 1025; display: none;
            }
            .sidebar-overlay.show { display: block; }
            .main-content {
                margin-left: 0;
                padding: 1.8rem;
                padding-bottom: calc(var(--mobile-bottom-bar) + 1.8rem);
            }

            .desktop-cart-col { display: none !important; }
            .menu-col { width: 100% !important; max-width: 100% !important; flex: 0 0 100% !important; }

            /* ─── MOBILE BOTTOM CART BAR ─── */
            .mobile-bottom-bar {
                display: flex !important;
                position: fixed; bottom: 0; left: 0; right: 0;
                height: var(--mobile-bottom-bar);
                background: white;
                border-top: 1px solid var(--border);
                z-index: 1050;
                box-shadow: 0 -4px 20px rgba(15,76,92,0.1);
                align-items: center;
                padding: 0 1rem;
                gap: 0.75rem;
            }
            .cart-preview-btn {
                flex: 1;
                display: flex; align-items: center; gap: 0.75rem;
                background: none; border: none;
                cursor: pointer; padding: 0;
                text-align: left;
            }
            .cart-preview-icon {
                position: relative;
                width: 46px; height: 46px;
                background: var(--primary);
                border-radius: 14px;
                display: flex; align-items: center; justify-content: center;
                color: white; font-size: 1.2rem;
                flex-shrink: 0;
            }
            .cart-badge {
                position: absolute; top: -5px; right: -5px;
                background: var(--secondary);
                color: white; font-size: 0.65rem; font-weight: 700;
                width: 20px; height: 20px;
                border-radius: 50%;
                display: flex; align-items: center; justify-content: center;
                border: 2px solid white;
            }
            .cart-preview-info { flex: 1; }
            .cart-preview-label { font-size: 0.75rem; color: var(--warm-gray); font-weight: 500; line-height: 1; margin-bottom: 2px; }
            .cart-preview-total { font-size: 1rem; font-weight: 700; color: var(--navy); line-height: 1; }
            .mobile-submit-btn {
                background: var(--success); color: white;
                border: none; border-radius: 14px;
                padding: 0.7rem 1.3rem;
                font-weight: 700; font-size: 0.88rem;
                cursor: pointer; transition: all 0.2s;
                white-space: nowrap;
                display: flex; align-items: center; gap: 0.5rem;
            }
            .mobile-submit-btn:hover:not(:disabled) { background: #1f5d43; }
            .mobile-submit-btn:disabled { opacity: 0.5; cursor: not-allowed; }

            /* ─── MOBILE CART DRAWER ─── */
            .mobile-cart-drawer {
                display: block !important;
                position: fixed; bottom: 0; left: 0; right: 0;
                background: white;
                border-radius: 24px 24px 0 0;
                box-shadow: 0 -8px 40px rgba(15,76,92,0.15);
                z-index: 1060;
                transform: translateY(100%);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                max-height: 90vh;
                display: flex; flex-direction: column;
                overflow: hidden;
            }
            .mobile-cart-drawer.open { transform: translateY(0); }
            .drawer-backdrop {
                position: fixed; inset: 0;
                background: rgba(15,76,92,0.3);
                backdrop-filter: blur(3px);
                z-index: 1055;
                display: none;
            }
            .drawer-backdrop.show { display: block; }
            .drawer-handle {
                width: 40px; height: 4px;
                background: var(--border);
                border-radius: 4px;
                margin: 1rem auto 0;
                flex-shrink: 0;
            }
            .drawer-header {
                padding: 1rem 1.2rem 0.75rem;
                border-bottom: 1px solid var(--border);
                display: flex; align-items: center; justify-content: space-between;
                flex-shrink: 0;
            }
            .drawer-title { font-size: 1.1rem; font-weight: 700; color: var(--navy); }
            .drawer-close { background: none; border: none; color: var(--warm-gray); font-size: 1.2rem; cursor: pointer; padding: 0.25rem; }
            .drawer-body { overflow-y: auto; flex: 1; }
            .drawer-body::-webkit-scrollbar { width: 4px; }
            .drawer-body::-webkit-scrollbar-thumb { background: var(--border); border-radius: 8px; }
            .drawer-footer {
                padding: 0.75rem 1rem;
                border-top: 1px solid var(--border);
                background: white;
                flex-shrink: 0;
            }

            .checkout-section { padding: 1rem 1.2rem 0.5rem; }
            .cart-summary { margin: 0 1.2rem 0.75rem; }
            .submit-btn-wrap { padding: 0 1.2rem 1rem; }
            #submitOrderBtn { border-radius: 14px; }

            .menu-grid { grid-template-columns: repeat(2, 1fr); gap: 0.8rem; }
            .menu-item-image, .menu-item-placeholder { height: 110px; }
            .menu-item-body { padding: 0.75rem; }
            .menu-item-name { font-size: 0.85rem; }
            .menu-item-price { font-size: 1rem; }
        }

        @media (max-width: 767.98px) {
            .main-content { padding: 1.2rem; padding-bottom: calc(var(--mobile-bottom-bar) + 1.2rem); }
            .page-header h1 { font-size: 1.8rem; }
        }

        @media (max-width: 480px) {
            .topbar { padding: 0 1rem; }
            .main-content { padding: 1rem; padding-bottom: calc(var(--mobile-bottom-bar) + 1rem); }
            .menu-grid { grid-template-columns: repeat(2, 1fr); gap: 0.6rem; }
            .menu-item-image, .menu-item-placeholder { height: 95px; }
            .page-header h1 { font-size: 1.5rem; }
        }

        @media (min-width: 992px) {
            .mobile-bottom-bar { display: none !important; }
            .mobile-cart-drawer { display: none !important; }
            .drawer-backdrop { display: none !important; }
        }

        .fade-in { animation: fadeUp 0.35s ease forwards; }
        @keyframes fadeUp { from { opacity: 0.5; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

        @media (min-width: 1200px) { .menu-grid { grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); } }
        @media (min-width: 992px) and (max-width: 1199px) { .menu-grid { grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); } }
    </style>
</head>
<body>

<!-- ════════════════════════════════════════
     TOPBAR
════════════════════════════════════════ -->
<div class="topbar d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <button class="mobile-menu-btn d-lg-none" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>
        <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="logo d-none d-sm-flex">
            <i class="fas fa-utensils"></i>
            <span>Dine with Din - Cafeteria</span>
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
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item text-danger" href="<?= $baseUrl ?>/index.php?page=logout">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-nav">
        <?php if (hasRole('admin')): ?>
        <div class="nav-section-title">DASHBOARD</div>
        <div class="nav-item">
            <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="nav-link">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </div>
        <div class="nav-section-title">MANAGEMENT</div>
        <div class="nav-item">
            <a href="<?= $baseUrl ?>/index.php?page=manage_menu" class="nav-link">
                <i class="fas fa-utensils"></i> Menu Items
            </a>
        </div>
        <div class="nav-item">
            <a href="<?= $baseUrl ?>/index.php?page=manage_recipes" class="nav-link">
                <i class="fas fa-tags"></i> Recipes
            </a>
        </div>
        <div class="nav-item">
            <a href="<?= $baseUrl ?>/index.php?page=manage_ingredients" class="nav-link">
                <i class="fas fa-boxes"></i> Ingredients
            </a>
        </div>
        <div class="nav-item">
            <a href="<?= $baseUrl ?>/index.php?page=manage_addons" class="nav-link">
                <i class="fas fa-plus-circle"></i> Add-ons
            </a>
        </div>
        <div class="nav-item">
            <a href="<?= $baseUrl ?>/index.php?page=manage_users" class="nav-link">
                <i class="fas fa-users"></i> User Management
            </a>
        </div>
        <?php endif; ?>
        <?php if (hasRole('admin') || hasRole('cashier')): ?>
        <div class="nav-section-title">SALES</div>
        <div class="nav-item">
            <a href="<?= $baseUrl ?>/index.php?page=manage_pos" class="nav-link active">
                <i class="fas fa-cash-register"></i> POS System
            </a>
        </div>
        <div class="nav-item">
            <a href="<?= $baseUrl ?>/index.php?page=manage_orders" class="nav-link">
                <i class="fas fa-receipt"></i> Orders
            </a>
        </div>
        <div class="nav-item">
                <a href="<?= $baseUrl ?>/index.php?page=reports" class="nav-link">
                    <i class="fas fa-chart-bar"></i>
                    Reports
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ════════════════════════════════════════
     MAIN CONTENT
════════════════════════════════════════ -->
<div class="main-content">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-cash-register"></i> Point of Sale</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/index.php?page=admin_dashboard">Home</a></li>
                    <li class="breadcrumb-item active">POS</li>
                </ol>
            </nav>
        </div>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show fade-in" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show fade-in" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="row g-4">
        <!-- ── Menu Items Column ── -->
        <div class="col-lg-8 menu-col">
            <div class="card fade-in">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-utensils" style="color:var(--primary)"></i> Menu Items
                    </h5>
                </div>
                <div class="card-body">
                    <div class="category-tabs-wrapper">
                        <div class="category-tabs">
                            <button class="category-tab active" data-category="all">
                                <i class="fas fa-th me-1"></i> All
                            </button>
                            <?php
                            $uniqueCategories = [];
                            foreach ($menu_items as $item) {
                                if (!isset($uniqueCategories[$item['category_id']])) {
                                    $uniqueCategories[$item['category_id']] = $item['category_name'];
                                }
                            }
                            foreach ($uniqueCategories as $catId => $catName): ?>
                                <button class="category-tab" data-category="<?= $catId ?>"><?= htmlspecialchars($catName) ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="menu-grid" id="menuGrid">
                        <?php foreach ($menu_items as $item):
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
                                    <div class="menu-item-placeholder"><i class="fas fa-utensils"></i></div>
                                <?php endif; ?>

                                <div class="menu-item-body">
                                    <div class="menu-item-name"><?= htmlspecialchars($item['item_name']) ?></div>
                                    <div class="menu-item-price">₱<?= number_format($item['price'], 2) ?></div>
                                    <?php if (!empty($item['ingredients'])): ?>
                                        <div class="menu-item-description">
                                            <?php
                                            $ingNames = array_map(fn($i) => $i['ingredient_name'], $item['ingredients']);
                                            echo htmlspecialchars(implode(', ', array_slice($ingNames, 0, 3)));
                                            if (count($ingNames) > 3) echo '...';
                                            ?>
                                        </div>
                                    <?php elseif (!empty($item['description'])): ?>
                                        <div class="menu-item-description"><?= htmlspecialchars($item['description']) ?></div>
                                    <?php endif; ?>
                                    <?php if (in_array($item['category_id'], [1,2,3,4,5,6])): ?>
                                        <div class="mt-1">
                                            <span class="badge" style="background:rgba(15,76,92,0.1);color:var(--primary);font-size:0.65rem;">
                                                <i class="fas fa-plus me-1"></i>Add-ons
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

        <!-- ── Desktop Cart Column ── -->
        <div class="col-lg-4 desktop-cart-col">
            <div class="cart-container">
                <div class="card fade-in cart-card" style="animation-delay:0.08s">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-shopping-cart" style="color:var(--primary)"></i> Current Order
                        </h5>
                    </div>

                    <div class="checkout-section">
                        <div class="mb-3">
                            <label class="form-label">Order Type <span class="text-danger">*</span></label>
                            <div class="order-type-container">
                                <button type="button" class="order-type-btn active" id="dineInBtn" onclick="setOrderType('dine_in', 'desktop')">
                                    <i class="fas fa-utensils"></i> Dine In
                                </button>
                                <button type="button" class="order-type-btn" id="takeOutBtn" onclick="setOrderType('takeout', 'desktop')">
                                    <i class="fas fa-bag-shopping"></i> Take Out
                                </button>
                            </div>
                            <input type="hidden" id="order_type" value="dine_in">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Customer Name</label>
                            <input type="text" class="form-control" id="customer_name" placeholder="e.g. John Cena">
                        </div>
                        <div id="tableNumberContainer">
                            <label class="form-label">Table Number</label>
                            <input type="text" class="form-control" id="table_number" placeholder="e.g. 5">
                        </div>
                    </div>

                    <hr class="mx-3 my-0">

                    <div class="cart-items" id="cartItems">
                        <div class="empty-cart">
                            <i class="fas fa-shopping-cart"></i>
                            <p class="fw-600" style="font-weight:600">Cart is empty</p>
                            <small class="text-muted">Tap a menu item to add</small>
                        </div>
                    </div>

                    <div class="cart-summary d-none" id="cartSummary">
                        <div class="cart-summary-row total"><span>Total:</span><span id="total">₱0.00</span></div>
                    </div>

                    <div class="submit-btn-wrap">
                        <button type="button" class="btn btn-success d-none" id="submitOrderBtn" disabled>
                            <i class="fas fa-check me-2"></i> Submit Order
                        </button>
                        <div id="submitDisabledMessage" class="text-danger small mt-2 text-center d-none">
                            <i class="fas fa-exclamation-circle me-1"></i> Fix stock issues to submit
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════
     MOBILE BOTTOM BAR
════════════════════════════════════════ -->
<div class="mobile-bottom-bar" id="mobileBottomBar" style="display:none">
    <button class="cart-preview-btn" onclick="openCartDrawer()">
        <div class="cart-preview-icon">
            <i class="fas fa-shopping-cart"></i>
            <div class="cart-badge" id="mobileBadge" style="display:none">0</div>
        </div>
        <div class="cart-preview-info">
            <div class="cart-preview-label" id="mobileCartCount">No items</div>
            <div class="cart-preview-total" id="mobileCartTotal">₱0.00</div>
        </div>
    </button>
    <button class="mobile-submit-btn" id="mobileSubmitBtn" onclick="submitOrderMobile()" disabled>
        <i class="fas fa-check"></i> Place Order
    </button>
</div>

<div class="drawer-backdrop" id="drawerBackdrop" onclick="closeCartDrawer()"></div>

<!-- ════════════════════════════════════════
     MOBILE CART DRAWER
════════════════════════════════════════ -->
<div class="mobile-cart-drawer" id="mobileCartDrawer" style="display:none">
    <div class="drawer-handle"></div>
    <div class="drawer-header">
        <div class="drawer-title"><i class="fas fa-shopping-cart me-2" style="color:var(--primary)"></i> Your Order</div>
        <button class="drawer-close" onclick="closeCartDrawer()"><i class="fas fa-times"></i></button>
    </div>
    <div class="drawer-body">
        <div class="checkout-section">
            <div class="mb-3">
                <label class="form-label">Order Type <span class="text-danger">*</span></label>
                <div class="order-type-container">
                    <button type="button" class="order-type-btn active" id="dineInBtnM" onclick="setOrderType('dine_in', 'mobile')">
                        <i class="fas fa-utensils"></i> Dine In
                    </button>
                    <button type="button" class="order-type-btn" id="takeOutBtnM" onclick="setOrderType('takeout', 'mobile')">
                        <i class="fas fa-bag-shopping"></i> Take Out
                    </button>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Customer Name</label>
                <input type="text" class="form-control" id="customer_name_m" placeholder="e.g. John Cena">
            </div>
            <div id="tableNumberContainerM">
                <label class="form-label">Table Number</label>
                <input type="text" class="form-control" id="table_number_m" placeholder="e.g. 5">
            </div>
        </div>

        <hr class="mx-3 my-0">

        <div class="cart-items" id="cartItemsMobile">
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <p style="font-weight:600">Cart is empty</p>
                <small class="text-muted">Tap a menu item to add</small>
            </div>
        </div>
    </div>

    <div class="drawer-footer">
        <div class="cart-summary d-none" id="cartSummaryMobile">
            <div class="cart-summary-row total"><span>Total:</span><span id="totalM">₱0.00</span></div>
        </div>
        <div class="mt-2">
            <button type="button" class="btn btn-success w-100 d-none" id="submitOrderBtnM" disabled style="border-radius:14px;padding:0.8rem;font-size:0.95rem;">
                <i class="fas fa-check me-2"></i> Submit Order
            </button>
            <div id="submitDisabledMessageM" class="text-danger small mt-2 text-center d-none">
                <i class="fas fa-exclamation-circle me-1"></i> Fix stock issues to submit
            </div>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════
     ADDONS / CONFIRM MODAL
════════════════════════════════════════ -->
<div class="modal fade" id="addonsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addonsModalLabel">
                    <i class="fas fa-utensils me-2" style="color:var(--secondary)"></i>
                    <span id="modalItemName"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="addonsContent"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="addToCartBtn">
                    <i class="fas fa-cart-plus me-2"></i> Add to Cart
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════
     STOCK ERROR MODAL
════════════════════════════════════════ -->
<div class="modal fade" id="stockErrorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2" style="color:var(--danger)"></i> Insufficient Stock
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div class="modal-icon danger mb-3"><i class="fas fa-boxes"></i></div>
                <h5 class="mb-2" id="stockErrTitle"></h5>
                <p class="text-muted small mb-3">The following ingredients don't have enough stock:</p>
                <div class="text-start p-3 bg-light rounded" style="max-height:200px;overflow-y:auto">
                    <small class="text-uppercase fw-bold text-muted">Affected Ingredients</small>
                    <ul class="list-unstyled mt-2 mb-0" id="stockErrList"></ul>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-danger w-100" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════
     GENERIC ERROR MODAL
════════════════════════════════════════ -->
<div class="modal fade" id="genericErrorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2" style="color:var(--danger)"></i> Error
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div class="modal-icon danger mb-3"><i class="fas fa-exclamation-circle"></i></div>
                <h5 class="mb-2" id="genericErrTitle"></h5>
                <p class="text-muted mb-0" id="genericErrMsg"></p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-danger w-100" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ============================================================
// DATA INJECTED FROM PHP
// ============================================================
const addonsMap      = <?= json_encode($addons_map      ?? []) ?>;
const stockData      = <?= json_encode($stock_data      ?? []) ?>;
const addonStockData = <?= json_encode($addon_stock_data ?? []) ?>;

let cart             = [];
let currentItem      = null;
let currentOrderType = 'dine_in';
let hasStockIssues   = false;

// ============================================================
// MOBILE DETECTION
// ============================================================
const isMobile = () => window.innerWidth < 992;

// ============================================================
// CART DRAWER
// ============================================================
function openCartDrawer() {
    document.getElementById('mobileCartDrawer').classList.add('open');
    document.getElementById('drawerBackdrop').classList.add('show');
    document.body.style.overflow = 'hidden';
}
function closeCartDrawer() {
    document.getElementById('mobileCartDrawer').classList.remove('open');
    document.getElementById('drawerBackdrop').classList.remove('show');
    document.body.style.overflow = '';
}

// ============================================================
// MODAL HELPERS
// ============================================================
function showModal(id) {
    const el = document.getElementById(id);
    const existing = bootstrap.Modal.getInstance(el);
    if (existing) existing.dispose();
    new bootstrap.Modal(el, { backdrop: true, keyboard: true }).show();
}
function hideModal(id) {
    const el = document.getElementById(id);
    const instance = bootstrap.Modal.getInstance(el);
    if (instance) instance.hide();
}
function showStockErrorModal(title, missingIngredients) {
    document.getElementById('stockErrTitle').textContent = title;
    const list = document.getElementById('stockErrList');
    list.innerHTML = '';
    missingIngredients.forEach(ing => {
        const li = document.createElement('li');
        li.className = 'mb-2 pb-1 border-bottom';
        li.innerHTML = `
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-1">
                <span><i class="fas fa-leaf me-2" style="color:var(--danger)"></i>${ing.name}</span>
                <span class="badge bg-danger">Need: ${(+ing.required).toFixed(2)} | Have: ${(+ing.available).toFixed(2)}</span>
            </div>`;
        list.appendChild(li);
    });
    showModal('stockErrorModal');
}
function showErrorModal(title, message) {
    document.getElementById('genericErrTitle').textContent = title;
    document.getElementById('genericErrMsg').textContent   = message;
    showModal('genericErrorModal');
}

document.addEventListener('hidden.bs.modal', function () {
    if (document.querySelector('.modal.show')) return;
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.classList.remove('modal-open');
    document.body.style.overflow    = '';
    document.body.style.paddingRight = '';
});

// ============================================================
// STOCK VALIDATION
// ============================================================
function buildDemandMap(cartRows) {
    const demand = {};
    for (const row of cartRows) {
        const itemStock = stockData[row.id];
        if (itemStock && itemStock.ingredients) {
            for (const ing of itemStock.ingredients) {
                if (!ing.quantity_required || ing.quantity_required <= 0) continue;
                const id = ing.ingredient_id;
                if (!demand[id]) demand[id] = { name: ing.ingredient_name, needed: 0, available: +ing.stock_quantity };
                demand[id].needed += ing.quantity_required * row.quantity;
            }
        }
        for (const addon of (row.addons || [])) {
            const as = addonStockData[addon.id];
            if (!as || !as.ingredients) continue;
            for (const ing of as.ingredients) {
                if (!ing.quantity_required || ing.quantity_required <= 0) continue;
                const id = ing.ingredient_id;
                if (!demand[id]) demand[id] = { name: ing.ingredient_name, needed: 0, available: +ing.stock_quantity };
                demand[id].needed += ing.quantity_required * addon.quantity;
            }
        }
    }
    return demand;
}
function validateCumulative(cartRows) {
    const demand    = buildDemandMap(cartRows);
    const conflicts = [];
    for (const info of Object.values(demand)) {
        if (info.needed > info.available) {
            conflicts.push({ name: info.name, required: info.needed, available: info.available });
        }
    }
    return { success: conflicts.length === 0, missingIngredients: conflicts };
}
function checkCanAdd(itemId, quantity, addons) {
    return validateCumulative([...cart, { id: itemId, quantity, addons: addons || [] }]);
}
function checkCanChangeQty(index, newQty) {
    return validateCumulative(cart.map((row, i) => i === index ? { ...row, quantity: newQty } : row));
}

// ============================================================
// CART PERSISTENCE
// ============================================================
function saveCart()  { localStorage.setItem('pos_cart', JSON.stringify(cart)); }
function loadCart()  {
    try { cart = JSON.parse(localStorage.getItem('pos_cart') || '[]'); } catch(e) { cart = []; }
}

// ============================================================
// CART OPERATIONS
// ============================================================
function addToCartWithQuantity(item, addons, quantity, note) {
    const check = checkCanAdd(item.id, quantity, addons);
    if (!check.success) {
        showStockErrorModal(`Cannot add "${item.name}" — combined order exceeds stock`, check.missingIngredients);
        return;
    }

    const trimmedNote = (note || '').trim();
    if (!trimmedNote) {
        const existingIndex = cart.findIndex(row => {
            if (row.id !== item.id) return false;
            if ((row.note || '') !== '') return false;
            if (row.addons.length !== addons.length) return false;
            const rowKey = row.addons.map(a => `${a.id}:${a.quantity}`).sort().join('|');
            const newKey = addons.map(a => `${a.id}:${a.quantity}`).sort().join('|');
            return rowKey === newKey;
        });
        if (existingIndex > -1) {
            const mergedQty  = cart[existingIndex].quantity + quantity;
            const mergeCheck = checkCanChangeQty(existingIndex, mergedQty);
            if (!mergeCheck.success) {
                showStockErrorModal(`Cannot increase "${item.name}" — combined order exceeds stock`, mergeCheck.missingIngredients);
                return;
            }
            cart[existingIndex].quantity      = mergedQty;
            cart[existingIndex].hasStockIssue = false;
            validateAndRender();
            saveCart();
            return;
        }
    }

    cart.push({
        id:           item.id,
        name:         item.name,
        price:        item.price,
        quantity,
        addons:       addons.map(a => ({ ...a })),
        note:         trimmedNote,
        hasStockIssue: false
    });
    validateAndRender();
    saveCart();
}

function updateQuantity(index, change) {
    const newQty = cart[index].quantity + change;
    if (newQty < 1) { cart.splice(index, 1); validateAndRender(); saveCart(); return; }
    if (change > 0) {
        const check = checkCanChangeQty(index, newQty);
        if (!check.success) {
            showStockErrorModal(`Cannot increase "${cart[index].name}" — combined order exceeds stock`, check.missingIngredients);
            return;
        }
    }
    cart[index].quantity      = newQty;
    cart[index].hasStockIssue = false;
    validateAndRender(); saveCart();
}

function setQuantity(index, value) {
    const qty = parseInt(value);
    if (isNaN(qty) || qty < 1) { cart.splice(index, 1); validateAndRender(); saveCart(); return; }
    const check = checkCanChangeQty(index, qty);
    if (!check.success) {
        showStockErrorModal(`Cannot set quantity for "${cart[index].name}" — combined order exceeds stock`, check.missingIngredients);
        renderCart(); return;
    }
    cart[index].quantity      = qty;
    cart[index].hasStockIssue = false;
    validateAndRender(); saveCart();
}

function removeFromCart(index) {
    cart.splice(index, 1);
    validateAndRender(); saveCart();
}

// ============================================================
// NOTE OPERATIONS (inline editing in cart)
// ============================================================
function showNoteEditor(index) {
    document.querySelectorAll('.cart-item-note-editor').forEach(el => {
        const i = parseInt(el.dataset.index);
        if (!isNaN(i)) cancelNoteEdit(i);
    });

    const noteDisplayEls = document.querySelectorAll(`[data-note-display="${index}"]`);
    noteDisplayEls.forEach(el => el.style.display = 'none');

    const editorEls = document.querySelectorAll(`[data-note-editor="${index}"]`);
    editorEls.forEach(el => {
        el.style.display = 'block';
        const textarea = el.querySelector('textarea');
        if (textarea) {
            textarea.value = cart[index].note || '';
            textarea.focus();
            textarea.setSelectionRange(textarea.value.length, textarea.value.length);
        }
    });
}

function saveNote(index) {
    const editorEls = document.querySelectorAll(`[data-note-editor="${index}"]`);
    let noteValue = '';
    editorEls.forEach(el => {
        const textarea = el.querySelector('textarea');
        if (textarea) noteValue = textarea.value.trim();
    });
    cart[index].note = noteValue;
    saveCart();
    renderCart();
}

function cancelNoteEdit(index) {
    const editorEls = document.querySelectorAll(`[data-note-editor="${index}"]`);
    editorEls.forEach(el => { el.style.display = 'none'; });
    const noteDisplayEls = document.querySelectorAll(`[data-note-display="${index}"]`);
    noteDisplayEls.forEach(el => { el.style.display = ''; });
}

// ============================================================
// VALIDATE WHOLE CART + RENDER
// ============================================================
function validateAndRender() {
    const check = validateCumulative(cart);
    if (!check.success) {
        const conflictNames = new Set(check.missingIngredients.map(i => i.name));
        cart.forEach(row => {
            const rowIngNames = new Set();
            (stockData[row.id]?.ingredients || []).forEach(ing => rowIngNames.add(ing.ingredient_name));
            (row.addons || []).forEach(addon => {
                (addonStockData[addon.id]?.ingredients || []).forEach(ing => rowIngNames.add(ing.ingredient_name));
            });
            row.hasStockIssue = [...rowIngNames].some(n => conflictNames.has(n));
        });
        hasStockIssues = true;
    } else {
        cart.forEach(row => { row.hasStockIssue = false; });
        hasStockIssues = false;
    }
    renderCart();
}

// ============================================================
// CART ITEM HTML BUILDER
// ============================================================
function buildCartItemHTML(row, index) {
    const addonsSubtotal = row.addons.reduce((s, a) => s + a.price * a.quantity, 0);
    const lineTotal      = (row.price + addonsSubtotal) * row.quantity;
    const hasIssue       = row.hasStockIssue === true;
    const note           = row.note || '';

    const noteDisplayHTML = note
        ? `<div class="cart-item-note" data-note-display="${index}" onclick="showNoteEditor(${index})">
               <i class="fas fa-pen-to-square"></i>
               <span class="cart-item-note-text">${escapeHtml(note)}</span>
           </div>`
        : `<div class="cart-item-note-empty" data-note-display="${index}" onclick="showNoteEditor(${index})">
               <i class="fas fa-pen-to-square"></i>
               <span>Add order note...</span>
           </div>`;

    const noteEditorHTML = `
        <div class="cart-item-note-editor" data-note-editor="${index}" style="display:none">
            <textarea rows="2" maxlength="300"
                      placeholder="e.g. No sugar, less ice, extra hot..."
                      onkeydown="handleNoteKeydown(event, ${index})">${escapeHtml(note)}</textarea>
            <div class="note-actions">
                <button class="note-cancel-btn" onclick="cancelNoteEdit(${index})">Cancel</button>
                <button class="note-save-btn" onclick="saveNote(${index})">
                    <i class="fas fa-check me-1"></i> Save
                </button>
            </div>
        </div>`;

    return `
        <div class="cart-item ${hasIssue ? 'insufficient-stock' : ''}">
            <div class="cart-item-header">
                <div>
                    <div class="cart-item-name">${row.name} × ${row.quantity}</div>
                    <div class="cart-item-price">₱${lineTotal.toFixed(2)}</div>
                </div>
                <button class="cart-item-remove" onclick="removeFromCart(${index})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            ${row.addons.length > 0 ? `
                <div class="addon-item">
                    ${row.addons.map(a => `+ ${a.name}${a.quantity > 1 ? ` ×${a.quantity}` : ''} (₱${(a.price * a.quantity).toFixed(2)})`).join('<br>')}
                </div>` : ''}
            ${noteDisplayHTML}
            ${noteEditorHTML}
            ${hasIssue ? `
                <div class="stock-warning stock-warning-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    Combined ingredient demand exceeds available stock
                </div>` : ''}
            <div class="cart-item-qty">
                <button class="qty-btn" onclick="updateQuantity(${index}, -1)" ${row.quantity <= 1 ? 'disabled' : ''}>
                    <i class="fas fa-minus"></i>
                </button>
                <input type="number" class="qty-input" value="${row.quantity}"
                       onchange="setQuantity(${index}, this.value)" min="1">
                <button class="qty-btn" onclick="updateQuantity(${index}, 1)" ${hasIssue ? 'disabled' : ''}>
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>`;
}

function handleNoteKeydown(e, index) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); saveNote(index); }
    if (e.key === 'Escape') { cancelNoteEdit(index); }
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

// ============================================================
// RENDER CART (updates both desktop + mobile)
// ============================================================
function renderCart() {
    const totalItems = cart.reduce((s, r) => s + r.quantity, 0);
    const total      = cart.reduce((sum, row) => {
        const addonsSubtotal = row.addons.reduce((s, a) => s + a.price * a.quantity, 0);
        return sum + (row.price + addonsSubtotal) * row.quantity;
    }, 0);

    const emptyHTML = `
        <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <p style="font-weight:600">Cart is empty</p>
            <small class="text-muted">Tap a menu item to add</small>
        </div>`;

    let itemsHTML = '';
    if (cart.length > 0) {
        cart.forEach((row, index) => { itemsHTML += buildCartItemHTML(row, index); });
    }

    // ── Desktop ──
    const dCartItems  = document.getElementById('cartItems');
    const dSummary    = document.getElementById('cartSummary');
    const dSubmitBtn  = document.getElementById('submitOrderBtn');
    const dSubmitMsg  = document.getElementById('submitDisabledMessage');
    if (dCartItems) {
        dCartItems.innerHTML = cart.length > 0 ? itemsHTML : emptyHTML;
        if (dSummary)   dSummary.classList.toggle('d-none', cart.length === 0);
        if (dSubmitBtn) { dSubmitBtn.classList.toggle('d-none', cart.length === 0); dSubmitBtn.disabled = hasStockIssues; }
        if (dSubmitMsg) dSubmitMsg.classList.toggle('d-none', !hasStockIssues || cart.length === 0);
        if (cart.length > 0) {
            document.getElementById('total').textContent = `₱${total.toFixed(2)}`;
        }
    }

    // ── Mobile Drawer ──
    const mCartItems = document.getElementById('cartItemsMobile');
    const mSummary   = document.getElementById('cartSummaryMobile');
    const mSubmitBtn = document.getElementById('submitOrderBtnM');
    const mSubmitMsg = document.getElementById('submitDisabledMessageM');
    if (mCartItems) {
        mCartItems.innerHTML = cart.length > 0 ? itemsHTML : emptyHTML;
        if (mSummary)   mSummary.classList.toggle('d-none', cart.length === 0);
        if (mSubmitBtn) { mSubmitBtn.classList.toggle('d-none', cart.length === 0); mSubmitBtn.disabled = hasStockIssues; }
        if (mSubmitMsg) mSubmitMsg.classList.toggle('d-none', !hasStockIssues || cart.length === 0);
        if (cart.length > 0) {
            document.getElementById('totalM').textContent = `₱${total.toFixed(2)}`;
        }
    }

    // ── Mobile Bottom Bar ──
    const mobileBar    = document.getElementById('mobileBottomBar');
    const mobileBadge  = document.getElementById('mobileBadge');
    const mobileCount  = document.getElementById('mobileCartCount');
    const mobileTotal  = document.getElementById('mobileCartTotal');
    const mobileSubmit = document.getElementById('mobileSubmitBtn');
    if (mobileBar) {
        if (cart.length > 0) {
            mobileBadge.textContent  = totalItems > 99 ? '99+' : totalItems;
            mobileBadge.style.display = 'flex';
            mobileCount.textContent  = `${totalItems} item${totalItems !== 1 ? 's' : ''}`;
            mobileTotal.textContent  = `₱${total.toFixed(2)}`;
        } else {
            mobileBadge.style.display = 'none';
            mobileCount.textContent  = 'No items';
            mobileTotal.textContent  = '₱0.00';
        }
        if (mobileSubmit) mobileSubmit.disabled = hasStockIssues || cart.length === 0;
    }
}

// ============================================================
// ORDER TYPE
// ============================================================
function setOrderType(type, context) {
    currentOrderType = type;
    document.getElementById('order_type').value = type;

    if (context === 'desktop' || !isMobile()) {
        const tableBox = document.getElementById('tableNumberContainer');
        document.getElementById('dineInBtn').classList.toggle('active', type === 'dine_in');
        document.getElementById('takeOutBtn').classList.toggle('active', type === 'takeout');
        if (tableBox) tableBox.style.display = type === 'dine_in' ? 'block' : 'none';
        if (type === 'takeout') document.getElementById('table_number').value = '';
    }
    if (context === 'mobile' || isMobile()) {
        const tableBoxM = document.getElementById('tableNumberContainerM');
        document.getElementById('dineInBtnM').classList.toggle('active', type === 'dine_in');
        document.getElementById('takeOutBtnM').classList.toggle('active', type === 'takeout');
        if (tableBoxM) tableBoxM.style.display = type === 'dine_in' ? 'block' : 'none';
        if (type === 'takeout' && document.getElementById('table_number_m')) {
            document.getElementById('table_number_m').value = '';
        }
    }
}

// ============================================================
// MENU ITEM CLICK → MODAL
// ============================================================
document.querySelectorAll('.menu-item-card').forEach(card => {
    card.addEventListener('click', function () {
        const itemId    = this.dataset.itemId;
        const itemName  = this.dataset.itemName;
        const itemPrice = parseFloat(this.dataset.itemPrice);
        const catId     = parseInt(this.dataset.categoryId);

        if (this.classList.contains('out-of-stock')) return;

        const soloCheck = checkCanAdd(itemId, 1, []);
        if (!soloCheck.success) {
            showStockErrorModal(`Cannot add "${itemName}" — combined order exceeds stock`, soloCheck.missingIngredients);
            return;
        }

        currentItem = { id: itemId, name: itemName, price: itemPrice, categoryId: catId };
        const activeAddons = (addonsMap[itemId] || []).filter(a => a.is_active);
        const maxQty       = stockData[itemId]?.max_quantity ?? 0;

        let html = '';
        if (activeAddons.length > 0) {
            document.getElementById('addonsModalLabel').innerHTML = `
                <i class="fas fa-plus-circle me-2" style="color:var(--secondary)"></i>
                Customize: ${itemName}`;
            html += `<div class="mb-4"><h6 class="fw-bold mb-3">Available Add-ons:</h6>`;
            activeAddons.forEach(addon => {
                const as     = addonStockData[addon.addon_id] || {};
                const hasStk = (as.max_quantity ?? 0) > 0;
                const maxA   = as.max_quantity ?? 0;
                html += `
                    <div class="addon-checkbox ${!hasStk ? 'opacity-50' : ''}">
                        <input type="checkbox" id="addon_${addon.addon_id}" value="${addon.addon_id}"
                               data-name="${addon.addon_name}" data-price="${addon.price}"
                               onchange="toggleAddonQty(${addon.addon_id})"
                               ${!hasStk ? 'disabled' : ''}>
                        <label for="addon_${addon.addon_id}" class="${!hasStk ? 'text-muted' : ''}">
                            ${addon.addon_name}
                            <span class="text-success fw-semibold">(+₱${parseFloat(addon.price).toFixed(2)})</span>
                            ${!hasStk ? '<span class="badge bg-danger ms-2">Out of Stock</span>' : ''}
                            ${hasStk && maxA < 10 ? `<small class="text-warning ms-2">(Only ${maxA} left)</small>` : ''}
                        </label>
                        <div class="addon-qty-control" id="qty_${addon.addon_id}" style="display:none">
                            <input type="number" min="1" max="${maxA}" value="1"
                                   id="qty_input_${addon.addon_id}"
                                   class="form-control form-control-sm" style="width:70px"
                                   ${!hasStk ? 'disabled' : ''}>
                        </div>
                    </div>`;
            });
            html += `</div>`;
        } else {
            document.getElementById('addonsModalLabel').innerHTML = `
                <i class="fas fa-utensils me-2" style="color:var(--secondary)"></i>
                Confirm: ${itemName}`;
            html += `
                <div class="text-center py-2">
                    <div class="modal-icon primary mb-3"><i class="fas fa-utensils"></i></div>
                    <h6 class="fw-bold mb-1">Add "${itemName}" to cart?</h6>
                    <p class="text-muted small mb-4">₱${itemPrice.toFixed(2)}</p>
                </div>`;
        }

        html += `
            <div class="mt-3 p-3 bg-light rounded-3">
                <label class="form-label fw-bold">Quantity:</label>
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-outline-secondary btn-sm" type="button" onclick="decModalQty()">−</button>
                    <input type="number" id="modalItemQty" class="form-control text-center"
                           value="1" min="1" max="${maxQty}" style="width:80px">
                    <button class="btn btn-outline-secondary btn-sm" type="button" onclick="incModalQty()">+</button>
                </div>
                <small class="text-muted mt-1 d-block">Max available (standalone): ${maxQty}</small>
            </div>

            <!-- ── ORDER NOTE FIELD ── -->
            <div class="note-field-wrapper">
                <div class="note-field-label">
                    <i class="fas fa-pen-to-square"></i>
                    Order Note <span style="font-weight:400;opacity:0.7">(optional)</span>
                </div>
                <textarea id="modalItemNote" rows="2" maxlength="300"
                          placeholder="e.g. No sugar, less ice, extra hot, lactose-free milk..."></textarea>
                <div class="note-field-hint">
                    <i class="fas fa-circle-info me-1"></i>
                    Special instructions for the kitchen — max 300 characters
                </div>
            </div>`;

        document.getElementById('addonsContent').innerHTML = html;
        showModal('addonsModal');
    });
});

function decModalQty() { const i = document.getElementById('modalItemQty'); if (i && +i.value > 1) i.value = +i.value - 1; }
function incModalQty() { const i = document.getElementById('modalItemQty'); if (i && +i.value < +i.max) i.value = +i.value + 1; }
function toggleAddonQty(addonId) {
    const cb  = document.getElementById(`addon_${addonId}`);
    const ctl = document.getElementById(`qty_${addonId}`);
    if (ctl) ctl.style.display = cb.checked ? 'flex' : 'none';
}

// ============================================================
// ADD TO CART
// ============================================================
document.getElementById('addToCartBtn').addEventListener('click', function () {
    const qtyInput = document.getElementById('modalItemQty');
    const itemQty  = Math.max(1, parseInt(qtyInput?.value) || 1);
    const noteVal  = (document.getElementById('modalItemNote')?.value || '').trim();

    const addons = Array.from(document.querySelectorAll('#addonsContent input[type="checkbox"]:checked'))
        .map(cb => ({
            id:       parseInt(cb.value),
            name:     cb.dataset.name,
            price:    parseFloat(cb.dataset.price),
            quantity: parseInt(document.getElementById(`qty_input_${cb.value}`)?.value) || 1,
        }));

    const check = checkCanAdd(currentItem.id, itemQty, addons);
    if (!check.success) {
        showStockErrorModal(`Cannot add "${currentItem.name}" — combined order exceeds stock`, check.missingIngredients);
        return;
    }
    addToCartWithQuantity(currentItem, addons, itemQty, noteVal);
    hideModal('addonsModal');

    if (isMobile() && cart.length === 1) {
        setTimeout(() => openCartDrawer(), 300);
    }
});

// ============================================================
// COLLECT ORDER DATA
// ============================================================
function collectOrderData() {
    const mobile  = isMobile();
    const orderType    = document.getElementById('order_type').value;
    const customerName = mobile
        ? (document.getElementById('customer_name_m').value.trim() || document.getElementById('customer_name').value.trim())
        : document.getElementById('customer_name').value.trim();
    const tableNumber  = mobile
        ? (document.getElementById('table_number_m').value.trim() || document.getElementById('table_number').value.trim())
        : document.getElementById('table_number').value.trim();
    return { orderType, customerName, tableNumber };
}

// ============================================================
// SUBMIT ORDER
// ============================================================
function doSubmitOrder() {
    if (cart.length === 0) { showErrorModal('Empty Cart', 'Please add items before submitting.'); return; }

    const finalCheck = validateCumulative(cart);
    if (!finalCheck.success) {
        showStockErrorModal('Order cannot be submitted — ingredient stock exceeded', finalCheck.missingIngredients);
        return;
    }

    const { orderType, customerName, tableNumber } = collectOrderData();
    if (!orderType) { showErrorModal('Order Type Required', 'Please select an order type.'); return; }
    if (orderType === 'dine_in' && !tableNumber) { showErrorModal('Table Number Required', 'Please enter a table number for dine-in orders.'); return; }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'index.php?page=manage_pos';

    [
        ['customer_name', customerName],
        ['table_number',  orderType === 'dine_in' ? tableNumber : ''],
        ['order_type',    orderType],
        ['submit_order',  '1'],
        ['cart_data',     JSON.stringify(cart)],
    ].forEach(([name, value]) => {
        const inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = name; inp.value = value;
        form.appendChild(inp);
    });

    document.body.appendChild(form);
    form.submit();
    cart = [];
    localStorage.removeItem('pos_cart');
}

function submitOrderMobile() { doSubmitOrder(); }

document.getElementById('submitOrderBtn')?.addEventListener('click',  doSubmitOrder);
document.getElementById('submitOrderBtnM')?.addEventListener('click', doSubmitOrder);

// ============================================================
// SIDEBAR TOGGLE
// ============================================================
function setSidebarOpen(open) {
    const sidebar   = document.getElementById('sidebar');
    const overlay   = document.getElementById('sidebarOverlay');
    const mobileBar = document.getElementById('mobileBottomBar');

    if (open) {
        sidebar.classList.add('show');
        overlay.classList.add('show');
        if (mobileBar) mobileBar.style.visibility = 'hidden';
        closeCartDrawer();
    } else {
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
        if (mobileBar) mobileBar.style.visibility = 'visible';
    }
}

document.getElementById('menuToggle')?.addEventListener('click', () => {
    const isOpen = document.getElementById('sidebar').classList.contains('show');
    setSidebarOpen(!isOpen);
});
document.getElementById('sidebarOverlay')?.addEventListener('click', () => {
    setSidebarOpen(false);
});

// ============================================================
// CATEGORY FILTER
// ============================================================
document.querySelectorAll('.category-tab').forEach(tab => {
    tab.addEventListener('click', function () {
        document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        const cat = this.dataset.category;
        document.querySelectorAll('.menu-item-card').forEach(card => {
            card.style.display = (cat === 'all' || card.dataset.category === cat) ? 'block' : 'none';
        });
    });
});

// ============================================================
// INIT
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    loadCart();

    document.querySelectorAll('.menu-item-card').forEach(card => {
        const id = card.dataset.itemId;
        if (!stockData[id] || stockData[id].has_stock) return;
        card.classList.add('out-of-stock');
        if (!card.querySelector('.out-of-stock-badge')) {
            const badge = document.createElement('div');
            badge.className   = 'out-of-stock-badge';
            badge.textContent = 'Out of Stock';
            card.appendChild(badge);
        }
    });

    validateAndRender();
    setOrderType('dine_in', 'desktop');
    setOrderType('dine_in', 'mobile');

    if (isMobile()) {
        const bar = document.getElementById('mobileBottomBar');
        if (bar) bar.style.display = 'flex';
        const drawer = document.getElementById('mobileCartDrawer');
        if (drawer) drawer.style.display = 'flex';
    }

    let touchStartY = 0;
    const drawer = document.getElementById('mobileCartDrawer');
    if (drawer) {
        drawer.addEventListener('touchstart', e => { touchStartY = e.touches[0].clientY; }, { passive: true });
        drawer.addEventListener('touchend', e => {
            const diff = e.changedTouches[0].clientY - touchStartY;
            if (diff > 80) closeCartDrawer();
        }, { passive: true });
    }
});

window.addEventListener('resize', () => {
    const bar    = document.getElementById('mobileBottomBar');
    const drawer = document.getElementById('mobileCartDrawer');
    if (isMobile()) {
        if (bar)    bar.style.display    = 'flex';
        if (drawer) drawer.style.display = 'flex';
    } else {
        if (bar)    bar.style.display    = 'none';
        if (drawer) { drawer.style.display = 'none'; closeCartDrawer(); }
    }
});

setTimeout(() => {
    document.querySelectorAll('.alert').forEach(a => { try { new bootstrap.Alert(a).close(); } catch(e){} });
}, 5000);
</script>
</body>
</html>