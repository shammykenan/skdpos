<?php
// Pure view: expects $menu_items, $addons_map, $categories, $_SESSION['success'], $_SESSION['error']
// Also expects $stock_data, $addon_stock_data, $available_tables, $draft_cart, $draft_meta from controller
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5">
    <title>POS - Cafeteria POS</title>
    <link rel="icon" type="image/png" href="<?= $baseUrl ?>/assets/images/logo/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0f4c5c;
            --primary-dark: #0a3a47;
            --primary-light: #1f7a8c;
            --secondary: #ff6b4a;
            --navy: #1e2f4e;
            --lavender: #e2e6f0;
            --light-bg: #f0f4f8;
            --warm-gray: #4a5568;
            --border: #cbd5e0;
            --success: #2c7a5a;
            --danger: #d64045;
            --warning: #f4b942;
            --dark: #1a202c;
            --sidebar-width: 280px;
            --topbar-height: 72px;
            --mobile-bottom-bar: 68px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--light-bg); color: var(--dark); font-size: 14px; line-height: 1.5; }

        /* ─── TOPBAR ─── */
        .topbar {
            position: fixed; top: 0; left: 0; right: 0; height: var(--topbar-height);
            background: white; border-bottom: 1px solid var(--border); z-index: 1040;
            padding: 0 1.5rem; box-shadow: 0 2px 8px rgba(15,76,92,0.06);
            display: flex; align-items: center; justify-content: space-between;
        }
        .topbar .logo { font-size: 1.6rem; font-weight: 700; color: var(--primary-dark); text-decoration: none; display: flex; align-items: center; gap: 0.65rem; }
        .topbar .logo i { font-size: 1.9rem; color: var(--secondary); transition: transform 0.2s; }
        .topbar .logo:hover i { transform: scale(1.05); }
        .topbar .logo span { background: linear-gradient(135deg, var(--primary), var(--navy)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .mobile-menu-btn { background: white; border: 1px solid var(--border); font-size: 1.4rem; color: var(--primary); cursor: pointer; padding: 0.5rem 1rem; border-radius: 12px; transition: all 0.2s; }
        .mobile-menu-btn:hover { background: var(--secondary); border-color: var(--secondary); color: white; }

        /* ─── SIDEBAR ─── */
        .sidebar {
            position: fixed; top: var(--topbar-height); left: 0;
            width: var(--sidebar-width); height: calc(100vh - var(--topbar-height));
            background: white; border-right: 1px solid var(--border);
            overflow-y: auto; transition: transform 0.25s cubic-bezier(0.4,0,0.2,1);
            z-index: 1030;
        }
        .sidebar::-webkit-scrollbar { width: 5px; }
        .sidebar::-webkit-scrollbar-thumb { background: var(--border); border-radius: 8px; }
        .sidebar-nav { padding: 2rem 0.75rem; }
        .nav-section-title { padding: 0.6rem 1.2rem; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: var(--warm-gray); letter-spacing: 1.2px; opacity: 0.7; margin-top: 0.8rem; }
        .nav-item { margin: 0.2rem 0.6rem; }
        .nav-link { display: flex; align-items: center; padding: 0.8rem 1.2rem; color: var(--warm-gray); text-decoration: none; border-radius: 14px; transition: all 0.2s; font-weight: 500; font-size: 0.95rem; gap: 0.85rem; }
        .nav-link i { width: 24px; font-size: 1.2rem; text-align: center; flex-shrink: 0; }
        .nav-link:hover { background: var(--lavender); color: var(--primary-dark); }
        .nav-link.active { background: var(--primary); color: white; box-shadow: 0 6px 12px rgba(15,76,92,0.2); }

        /* ─── MAIN CONTENT ─── */
        .main-content { margin-left: var(--sidebar-width); margin-top: var(--topbar-height); padding: 1.5rem; min-height: calc(100vh - var(--topbar-height)); }

        /* ─── PAGE HEADER ─── */
        .page-header { margin-bottom: 1.5rem; }
        .page-header h1 { font-size: 1.6rem; font-weight: 700; color: var(--navy); display: flex; align-items: center; gap: 0.6rem; }
        .page-header h1 i { color: var(--secondary); }
        .breadcrumb { background: none; padding: 0.1rem 0; margin: 0; font-size: 0.8rem; }
        .breadcrumb-item a { color: var(--warm-gray); text-decoration: none; }
        .breadcrumb-item.active { color: var(--primary); font-weight: 600; }

        /* ─── LAYOUT ─── */
        .pos-layout { display: grid; grid-template-columns: 1fr 360px; gap: 1.25rem; align-items: start; }

        /* ─── MENU PANEL ─── */
        .menu-panel { background: white; border-radius: 20px; border: 1px solid rgba(203,213,224,0.5); box-shadow: 0 4px 16px rgba(15,76,92,0.04); overflow: hidden; }
        .menu-panel-header { padding: 1.1rem 1.4rem; border-bottom: 1px solid #f0f4f8; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
        .menu-panel-title { font-size: 1rem; font-weight: 700; color: var(--navy); display: flex; align-items: center; gap: 0.5rem; }
        .menu-panel-title i { color: var(--primary); }

        /* ─── SEARCH ─── */
        .search-wrapper { position: relative; flex: 1; min-width: 180px; max-width: 280px; }
        .search-input { width: 100%; padding: 0.5rem 0.9rem 0.5rem 2.4rem; border: 1.5px solid var(--border); border-radius: 40px; font-size: 0.82rem; font-family: inherit; background: var(--light-bg); transition: all 0.2s; outline: none; }
        .search-input:focus { border-color: var(--primary); background: white; box-shadow: 0 0 0 3px rgba(15,76,92,0.08); }
        .search-icon { position: absolute; left: 0.85rem; top: 50%; transform: translateY(-50%); color: var(--warm-gray); font-size: 0.8rem; pointer-events: none; }
        .search-clear { position: absolute; right: 0.7rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--warm-gray); cursor: pointer; font-size: 0.75rem; display: none; padding: 0.1rem; }

        /* ─── CATEGORY TABS ─── */
        .category-tabs-wrapper { padding: 0.8rem 1.4rem 0; overflow-x: auto; -webkit-overflow-scrolling: touch; scrollbar-width: none; }
        .category-tabs-wrapper::-webkit-scrollbar { display: none; }
        .category-tabs { display: flex; gap: 0.4rem; white-space: nowrap; }
        .category-tab { padding: 0.38rem 1rem; border: 1.5px solid var(--border); background: white; color: var(--warm-gray); border-radius: 40px; cursor: pointer; transition: all 0.18s; font-weight: 600; font-size: 0.75rem; flex-shrink: 0; white-space: nowrap; }
        .category-tab:hover { border-color: var(--primary); color: var(--primary); background: rgba(15,76,92,0.04); }
        .category-tab.active { background: var(--primary); color: white; border-color: var(--primary); }
        .category-tab .tab-count { opacity: 0.75; font-weight: 500; margin-left: 0.25rem; }

        /* ─── MENU GRID ─── */
        .menu-grid-wrapper { padding: 1rem 1.4rem 1.4rem; }
        .menu-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(175px, 1fr)); gap: 1rem; }
        .menu-item-card { background: white; border: 1.5px solid #e8eef5; border-radius: 14px; overflow: hidden; transition: all 0.2s; cursor: pointer; position: relative; }
        .menu-item-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(15,76,92,0.1); border-color: var(--primary-light); }
        .menu-item-card:active { transform: scale(0.97); }
        .menu-item-card.out-of-stock { opacity: 0.5; cursor: not-allowed; filter: grayscale(0.5); }
        .menu-item-card.out-of-stock:hover { transform: none; box-shadow: none; }
        .menu-item-card.in-cart { border-color: var(--success); box-shadow: 0 0 0 2px rgba(44,122,90,0.15); }

        /* ─── CARD ADD FLASH ─── */
        .menu-item-card.just-added { animation: cardFlash 0.45s ease forwards; }
        @keyframes cardFlash {
            0%   { box-shadow: 0 0 0 0 rgba(44,122,90,0); transform: scale(1); }
            40%  { box-shadow: 0 0 0 6px rgba(44,122,90,0.35); transform: scale(0.97); }
            100% { box-shadow: 0 0 0 2px rgba(44,122,90,0.15); transform: scale(1); }
        }

        .menu-item-image { width: 100%; height: 130px; object-fit: cover; }
        .menu-item-placeholder { width: 100%; height: 130px; display: flex; align-items: center; justify-content: center; background: linear-gradient(145deg,#edf2f7,#f7fafc); color: #a0aec0; font-size: 2rem; }
        .menu-item-body { padding: 0.8rem; }
        .menu-item-name { font-weight: 700; font-size: 0.85rem; color: var(--navy); margin-bottom: 0.2rem; line-height: 1.3; }
        .menu-item-price { font-size: 1rem; font-weight: 700; color: var(--success); }
        .menu-item-desc { font-size: 0.68rem; color: var(--warm-gray); margin-top: 0.2rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .out-of-stock-badge { position: absolute; top: 0.6rem; right: 0.6rem; background: var(--danger); color: white; padding: 0.2rem 0.6rem; border-radius: 40px; font-size: 0.6rem; font-weight: 700; z-index: 5; }
        .in-cart-badge { position: absolute; top: 0.6rem; left: 0.6rem; background: var(--success); color: white; width: 22px; height: 22px; border-radius: 50%; font-size: 0.65rem; font-weight: 700; z-index: 5; display: flex; align-items: center; justify-content: center; }
        .addon-badge { display: inline-flex; align-items: center; gap: 0.25rem; margin-top: 0.4rem; background: rgba(15,76,92,0.08); color: var(--primary); padding: 0.2rem 0.55rem; border-radius: 40px; font-size: 0.62rem; font-weight: 700; }
        .no-results { text-align: center; padding: 3rem 1rem; color: var(--warm-gray); }
        .no-results i { font-size: 2.5rem; color: #d0d8e4; display: block; margin-bottom: 0.75rem; }

        /* ─── VARIANT ─── */
        .cart-item-variant { font-size: 0.72rem; color: var(--primary-light); font-weight: 600; margin-top: 0.1rem; display: flex; align-items: center; gap: 0.3rem; }
        .variant-option { display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.4rem; border-bottom: 1px solid #edf2f7; cursor: pointer; transition: background 0.15s; }
        .variant-option:last-child { border-bottom: none; }
        .variant-option:hover { background: #f4f7fb; }
        .variant-option.out-of-stock-variant { opacity: 0.45; cursor: not-allowed; }
        .variant-option .variant-name { font-weight: 700; color: var(--navy); font-size: 0.95rem; }
        .variant-option .variant-price { font-weight: 800; color: var(--success); font-size: 1rem; }

        /* ─── CART PANEL ─── */
        .cart-panel { background: white; border-radius: 20px; border: 1px solid rgba(203,213,224,0.5); box-shadow: 0 4px 16px rgba(15,76,92,0.04); position: sticky; top: calc(var(--topbar-height) + 1.25rem); overflow: hidden; display: flex; flex-direction: column; max-height: calc(100vh - var(--topbar-height) - 2.5rem); }
        .cart-panel-header { padding: 1rem 1.2rem; border-bottom: 1px solid #f0f4f8; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; }
        .cart-panel-title { font-size: 1rem; font-weight: 700; color: var(--navy); display: flex; align-items: center; gap: 0.5rem; }
        .cart-panel-title i { color: var(--primary); }
        .cart-item-count-pill { background: var(--primary); color: white; font-size: 0.65rem; font-weight: 700; padding: 0.15rem 0.55rem; border-radius: 40px; min-width: 22px; text-align: center; }
        .clear-cart-btn { background: rgba(214,64,69,0.08); border: 1.5px solid rgba(214,64,69,0.35); color: var(--danger); font-size: 0.78rem; font-weight: 700; padding: 0.45rem 1rem; border-radius: 10px; cursor: pointer; transition: all 0.18s; display: flex; align-items: center; gap: 0.4rem; white-space: nowrap; }
        .clear-cart-btn:hover { background: var(--danger); color: white; border-color: var(--danger); }

        /* ─── CART CHECKOUT FIELDS ─── */
        .cart-fields { padding: 1rem 1.2rem 0.75rem; flex-shrink: 0; border-bottom: 1px solid #f0f4f8; }
        .form-label { font-weight: 600; color: var(--navy); margin-bottom: 0.3rem; font-size: 0.75rem; display: block; }
        .form-control { border: 1.5px solid var(--border); border-radius: 10px; padding: 0.5rem 0.8rem; font-size: 0.83rem; transition: all 0.2s; width: 100%; font-family: inherit; }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(15,76,92,0.08); outline: none; }
        .order-type-container { display: flex; gap: 0.5rem; }
        .order-type-btn { flex: 1; padding: 0.5rem; border: 1.5px solid var(--border); background: var(--light-bg); color: var(--warm-gray); border-radius: 10px; cursor: pointer; transition: all 0.18s; font-weight: 600; font-size: 0.78rem; display: flex; align-items: center; justify-content: center; gap: 0.4rem; }
        .order-type-btn:hover { border-color: var(--primary); color: var(--primary); }
        .order-type-btn.active { background: var(--primary); color: white; border-color: var(--primary); box-shadow: 0 3px 8px rgba(15,76,92,0.2); }
        .table-select { border: 1.5px solid var(--border); border-radius: 10px; padding: 0.5rem 2rem 0.5rem 0.8rem; font-size: 0.83rem; width: 100%; background: white; color: var(--dark); transition: border-color 0.2s; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%234a5568' d='M6 8L1 3h10z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 0.8rem center; font-family: inherit; }
        .table-select:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(15,76,92,0.08); }
        .table-unavailable-notice { padding: 0.5rem 0.8rem; border-radius: 10px; background: rgba(244,185,66,0.1); border: 1.5px solid rgba(244,185,66,0.4); font-size: 0.78rem; color: #7a5c00; }
        .table-count-hint { font-size: 0.68rem; color: var(--warm-gray); margin-top: 0.25rem; display: flex; align-items: center; gap: 0.3rem; }
        .table-count-hint i { color: var(--success); }

        /* ─── DRAFT BANNER ─── */
        .draft-restored-banner { display: flex; align-items: center; gap: 0.6rem; padding: 0.6rem 1rem; margin: 0 1.2rem 0; background: rgba(254,228,64,0.15); border: 1.5px solid rgba(244,185,66,0.4); border-radius: 10px; font-size: 0.75rem; color: #7a5c00; }
        .draft-restored-banner i { color: #b87a1a; flex-shrink: 0; }
        .draft-restored-banner span { flex: 1; }
        .draft-dismiss-btn { background: none; border: none; color: #b87a1a; cursor: pointer; font-size: 0.8rem; padding: 0.1rem; opacity: 0.7; }
        .draft-dismiss-btn:hover { opacity: 1; }

        /* ─── CART ITEMS ─── */
        .cart-items-wrapper { flex: 1; overflow-y: scroll; min-height: 0; scrollbar-width: thin; scrollbar-color: #cbd5e0 #f4f7fb; }
        .cart-items-wrapper::-webkit-scrollbar { width: 6px; }
        .cart-items-wrapper::-webkit-scrollbar-track { background: #f4f7fb; }
        .cart-items-wrapper::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 8px; }
        .cart-items-wrapper::-webkit-scrollbar-thumb:hover { background: #a0aec0; }
        .cart-item { padding: 1rem 1.2rem; border-bottom: 1px solid #edf2f7; transition: background 0.15s; position: relative; }
        .cart-item:last-child { border-bottom: none; }
        .cart-item:hover { background: #fafcff; }
        .cart-item.insufficient-stock { background: rgba(214,64,69,0.03); border-left: 3px solid var(--danger); padding-left: calc(1.2rem - 3px); }
        .cart-item-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem; }
        .cart-item-info { flex: 1; min-width: 0; }
        .cart-item-name { font-weight: 700; font-size: 0.92rem; color: var(--navy); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.35; }
        .cart-item-unit-price { font-size: 0.73rem; color: var(--warm-gray); margin-top: 0.1rem; }
        .cart-item-price { font-size: 1rem; font-weight: 800; color: var(--success); margin-top: 0.2rem; }
        .cart-item-remove { background: none; border: none; color: #cbd5e0; cursor: pointer; padding: 0.2rem; font-size: 1rem; flex-shrink: 0; transition: color 0.15s; line-height: 1; margin-top: 1px; }
        .cart-item-remove:hover { color: var(--danger); }
        .addon-item { font-size: 0.73rem; color: var(--warm-gray); padding: 0.25rem 0.6rem; margin-top: 0.35rem; background: #f7f9fc; border-radius: 6px; border-left: 2px solid var(--primary-light); }
        .cart-item-qty { display: flex; align-items: center; gap: 0.5rem; margin-top: 0.65rem; justify-content: flex-end; }
        .qty-btn { width: 30px; height: 30px; border: 1.5px solid var(--border); background: white; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.15s; color: var(--warm-gray); font-size: 0.78rem; flex-shrink: 0; }
        .qty-btn:hover:not(:disabled) { border-color: var(--primary); background: var(--primary); color: white; }
        .qty-btn:disabled { opacity: 0.3; cursor: not-allowed; }
        .qty-input { width: 46px; text-align: center; border: 1.5px solid var(--border); border-radius: 8px; padding: 0.3rem 0.25rem; font-weight: 700; font-size: 0.88rem; font-family: inherit; color: var(--navy); }
        .qty-input:focus { outline: none; border-color: var(--primary); }

        /* ─── CART NOTES ─── */
        .cart-item-note { display: flex; align-items: flex-start; gap: 0.4rem; margin-top: 0.5rem; padding: 0.4rem 0.65rem; background: rgba(254,228,64,0.12); border: 1px dashed rgba(244,185,66,0.5); border-radius: 8px; font-size: 0.75rem; color: #7a5c00; cursor: pointer; transition: background 0.15s; }
        .cart-item-note:hover { background: rgba(254,228,64,0.25); }
        .cart-item-note i { font-size: 0.72rem; margin-top: 2px; flex-shrink: 0; color: #b87a1a; }
        .cart-item-note-text { flex: 1; font-style: italic; word-break: break-word; line-height: 1.4; }
        .cart-item-note-empty { display: flex; align-items: center; gap: 0.35rem; margin-top: 0.45rem; font-size: 0.73rem; color: var(--warm-gray); opacity: 0.5; cursor: pointer; transition: opacity 0.15s; }
        .cart-item-note-empty:hover { opacity: 1; color: var(--primary); }
        .cart-item-note-editor { margin-top: 0.45rem; }
        .cart-item-note-editor textarea { width: 100%; border: 1.5px solid var(--primary-light); border-radius: 9px; padding: 0.4rem 0.65rem; font-size: 0.8rem; resize: none; outline: none; font-family: inherit; color: var(--dark); background: #fafcff; line-height: 1.5; }
        .cart-item-note-editor textarea:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(15,76,92,0.08); }
        .cart-item-note-editor .note-actions { display: flex; justify-content: flex-end; gap: 0.4rem; margin-top: 0.3rem; }
        .note-save-btn { padding: 0.25rem 0.75rem; font-size: 0.72rem; border-radius: 20px; background: var(--primary); color: white; border: none; cursor: pointer; font-weight: 600; }
        .note-save-btn:hover { background: var(--primary-dark); }
        .note-cancel-btn { padding: 0.25rem 0.65rem; font-size: 0.72rem; border-radius: 20px; background: white; color: var(--warm-gray); border: 1px solid var(--border); cursor: pointer; font-weight: 600; }
        .note-cancel-btn:hover { background: var(--lavender); }
        .stock-warning-danger { display: flex; align-items: center; gap: 0.35rem; padding: 0.35rem 0.6rem; background: rgba(214,64,69,0.07); color: var(--danger); border-radius: 7px; font-size: 0.72rem; margin-top: 0.4rem; font-weight: 500; }

        /* ─── NOTE FIELD IN MODAL ─── */
        .note-field-wrapper { margin-top: 1rem; padding: 0.9rem; background: rgba(254,228,64,0.07); border: 1.5px dashed rgba(244,185,66,0.5); border-radius: 12px; }
        .note-field-label { display: flex; align-items: center; gap: 0.4rem; font-size: 0.78rem; font-weight: 700; color: #7a5c00; margin-bottom: 0.45rem; }
        .note-field-label i { color: #b87a1a; }
        .note-field-hint { font-size: 0.67rem; color: var(--warm-gray); margin-top: 0.3rem; }
        #modalItemNote { width: 100%; border: 1.5px solid rgba(244,185,66,0.45); border-radius: 9px; padding: 0.45rem 0.7rem; font-size: 0.83rem; resize: none; font-family: inherit; background: white; color: var(--dark); transition: border-color 0.15s; }
        #modalItemNote:focus { outline: none; border-color: var(--warning); box-shadow: 0 0 0 3px rgba(244,185,66,0.12); }
        #modalItemNote::placeholder { color: #b0a070; font-style: italic; }

        /* ─── EMPTY CART ─── */
        .empty-cart { text-align: center; padding: 2.5rem 1.2rem; color: var(--warm-gray); }
        .empty-cart-icon { width: 64px; height: 64px; background: var(--light-bg); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; }
        .empty-cart-icon i { font-size: 1.6rem; color: #a0aec0; }
        .empty-cart p { font-weight: 600; color: var(--navy); margin-bottom: 0.3rem; }
        .empty-cart small { font-size: 0.78rem; }

        /* ─── CART FOOTER ─── */
        .cart-footer { flex-shrink: 0; border-top: 1px solid #f0f4f8; padding: 1rem 1.2rem; }
        .cart-total-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.9rem; }
        .cart-total-label { font-size: 0.82rem; color: var(--warm-gray); font-weight: 500; }
        .cart-total-amount { font-size: 1.25rem; font-weight: 800; color: var(--navy); }

        /* ─── COLLECT PAYMENT BUTTON ─── */
        .collect-payment-btn {
            width: 100%; padding: 0.9rem; border: none; border-radius: 14px; font-size: 0.95rem;
            font-weight: 700; cursor: pointer; transition: all 0.2s;
            display: flex; align-items: center; justify-content: center; gap: 0.6rem;
            background: linear-gradient(135deg, #2c7a5a 0%, #1f5d43 100%);
            color: white; box-shadow: 0 4px 14px rgba(44,122,90,0.3);
            font-family: inherit;
        }
        .collect-payment-btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(44,122,90,0.4); }
        .collect-payment-btn:active:not(:disabled) { transform: translateY(0); }
        .collect-payment-btn:disabled { opacity: 0.45; cursor: not-allowed; transform: none; box-shadow: none; }
        .collect-payment-btn i { font-size: 1rem; }
        .submit-disabled-msg { display: flex; align-items: center; justify-content: center; gap: 0.4rem; font-size: 0.72rem; color: var(--danger); margin-top: 0.5rem; }

        /* ─── ALERTS ─── */
        .alert { border-radius: 12px; border: none; padding: 0.8rem 1.1rem; margin-bottom: 1.2rem; font-size: 0.85rem; }
        .alert-success { background: rgba(44,122,90,0.1); color: #1e4a3b; border-left: 4px solid var(--success); }
        .alert-danger { background: rgba(214,64,69,0.07); color: #9e3a34; border-left: 4px solid var(--danger); }

        /* ─── ADD TOAST ─── */
        #addToast {
            position: fixed; bottom: calc(var(--mobile-bottom-bar) + 12px); left: 50%; transform: translateX(-50%) translateY(20px);
            background: #1a202c; color: white; padding: 0.6rem 1.2rem;
            border-radius: 40px; font-size: 0.82rem; font-weight: 600;
            display: flex; align-items: center; gap: 0.5rem; z-index: 9999;
            opacity: 0; pointer-events: none; transition: opacity 0.2s, transform 0.2s;
            white-space: nowrap; box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        }
        #addToast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        #addToast i { color: #68d391; }
        @media (min-width: 992px) {
            #addToast { bottom: 24px; }
        }

        /* ─── MODALS (shared base) ─── */
        .modal { padding-right: 0 !important; }
        .modal-content { border-radius: 20px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.12); }
        .modal-header { border-bottom: 1px solid var(--border); padding: 1.2rem 1.4rem; border-radius: 20px 20px 0 0; }
        .modal-body { padding: 1.4rem; }
        .modal-footer { border-top: 1px solid var(--border); padding: 1rem 1.4rem; border-radius: 0 0 20px 20px; }
        .modal-title { font-size: 1.05rem; font-weight: 700; color: var(--navy); display: flex; align-items: center; gap: 0.45rem; }
        .modal-icon { width: 52px; height: 52px; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.9rem; font-size: 1.4rem; }
        .modal-icon.danger { background: rgba(214,64,69,0.1); color: var(--danger); }
        .modal-icon.primary { background: rgba(15,76,92,0.1); color: var(--primary); }
        .modal-icon.warning { background: rgba(244,185,66,0.15); color: #b87a1a; }
        .modal-icon.success { background: rgba(44,122,90,0.12); color: var(--success); }
        .btn { padding: 0.55rem 1.3rem; border-radius: 40px; font-weight: 600; font-size: 0.83rem; transition: all 0.18s; }
        .btn-primary { background: var(--primary); border: none; color: white; }
        .btn-primary:hover:not(:disabled) { background: var(--primary-dark); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(15,76,92,0.25); }
        .btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }
        .btn-danger { background: var(--danger); border: none; color: white; }
        .btn-danger:hover { background: #b83038; transform: translateY(-1px); }
        .btn-outline-secondary { border: 1.5px solid var(--border); color: var(--warm-gray); background: white; }
        .btn-outline-secondary:hover { background: var(--lavender); }

        /* ═══════════════════════════════════════════════════
           PAYMENT MODAL — full redesign
        ═══════════════════════════════════════════════════ */
        #paymentModal .modal-content { border-radius: 24px; overflow: hidden; }
        .pm-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: 1.5rem 1.6rem; display: flex; align-items: center; gap: 1rem;
            flex-shrink: 0;
        }
        .pm-header-icon {
            width: 52px; height: 52px; background: rgba(255,255,255,0.15); border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem; color: white; flex-shrink: 0;
        }
        .pm-header-text { flex: 1; }
        .pm-header-title { font-size: 1.1rem; font-weight: 700; color: white; line-height: 1.2; }
        .pm-header-sub { font-size: 0.8rem; color: rgba(255,255,255,0.7); margin-top: 0.2rem; }
        .pm-header .btn-close-white { filter: brightness(0) invert(1); opacity: 0.7; }
        .pm-header .btn-close-white:hover { opacity: 1; }

        .pm-body { padding: 1.6rem; }

        /* Order summary strip */
        .pm-summary {
            background: var(--light-bg); border-radius: 14px; padding: 1rem 1.2rem;
            margin-bottom: 1.4rem; border: 1px solid var(--border);
        }
        .pm-summary-label { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: var(--warm-gray); letter-spacing: 0.8px; margin-bottom: 0.6rem; }
        .pm-summary-items { font-size: 0.82rem; color: var(--navy); max-height: 100px; overflow-y: auto; scrollbar-width: thin; }
        .pm-summary-item-row { display: flex; justify-content: space-between; padding: 0.2rem 0; }
        .pm-summary-item-name { flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; padding-right: 0.5rem; }
        .pm-summary-item-price { font-weight: 600; flex-shrink: 0; }
        .pm-summary-divider { border: none; border-top: 1px dashed var(--border); margin: 0.6rem 0; }

        /* Total display */
        .pm-total-card {
            background: var(--primary); border-radius: 16px; padding: 1.2rem 1.4rem;
            margin-bottom: 1.4rem; display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 6px 16px rgba(15,76,92,0.2);
        }
        .pm-total-label { font-size: 0.82rem; color: rgba(255,255,255,0.75); font-weight: 600; }
        .pm-total-amount { font-size: 2rem; font-weight: 800; color: white; letter-spacing: -1px; }

        /* Amount input */
        .pm-input-section { margin-bottom: 1rem; }
        .pm-input-label { font-size: 0.78rem; font-weight: 700; color: var(--navy); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.4rem; }
        .pm-cash-input-wrap { position: relative; }
        .pm-currency-prefix {
            position: absolute; left: 0; top: 0; bottom: 0; width: 48px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; font-weight: 700; color: var(--warm-gray);
            background: var(--light-bg); border-radius: 14px 0 0 14px;
            border: 2px solid var(--border); border-right: none;
        }
        .pm-cash-input {
            width: 100%; padding: 0.85rem 1rem 0.85rem 3.5rem;
            border: 2px solid var(--border); border-radius: 14px; font-size: 1.6rem;
            font-weight: 800; color: var(--navy); font-family: inherit;
            transition: all 0.2s; background: white; outline: none;
        }
        .pm-cash-input:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(15,76,92,0.1); }
        .pm-cash-input.valid   { border-color: var(--success); }
        .pm-cash-input.invalid { border-color: var(--danger); }

        /* Quick amount pills */
        .pm-quick-amounts { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.75rem; }
        .pm-quick-btn {
            padding: 0.35rem 0.85rem; border: 1.5px solid var(--border); background: white;
            border-radius: 40px; font-size: 0.78rem; font-weight: 700; color: var(--warm-gray);
            cursor: pointer; transition: all 0.15s; font-family: inherit;
        }
        .pm-quick-btn:hover { border-color: var(--primary); color: var(--primary); background: rgba(15,76,92,0.05); }
        .pm-quick-btn.exact { border-color: var(--success); color: var(--success); background: rgba(44,122,90,0.06); }

        /* Change display */
        .pm-change-card {
            border-radius: 14px; padding: 1rem 1.2rem; margin-top: 1rem;
            display: flex; align-items: center; gap: 0.8rem; transition: all 0.25s;
        }
        .pm-change-card.change-ok    { background: rgba(44,122,90,0.08); border: 1.5px solid rgba(44,122,90,0.3); }
        .pm-change-card.change-exact { background: rgba(15,76,92,0.06); border: 1.5px solid rgba(15,76,92,0.25); }
        .pm-change-card.change-err   { background: rgba(214,64,69,0.07); border: 1.5px solid rgba(214,64,69,0.3); }
        .pm-change-card.change-none  { background: var(--light-bg); border: 1.5px solid var(--border); }
        .pm-change-icon { width: 38px; height: 38px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
        .change-ok   .pm-change-icon { background: rgba(44,122,90,0.15); color: var(--success); }
        .change-exact .pm-change-icon { background: rgba(15,76,92,0.1); color: var(--primary); }
        .change-err  .pm-change-icon { background: rgba(214,64,69,0.1); color: var(--danger); }
        .change-none .pm-change-icon { background: white; color: #a0aec0; }
        .pm-change-info { flex: 1; }
        .pm-change-sublabel { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: var(--warm-gray); letter-spacing: 0.6px; }
        .pm-change-value { font-size: 1.3rem; font-weight: 800; margin-top: 0.1rem; }
        .change-ok   .pm-change-value { color: var(--success); }
        .change-exact .pm-change-value { color: var(--primary); }
        .change-err  .pm-change-value { color: var(--danger); }
        .change-none .pm-change-value { color: #a0aec0; }

        .pm-footer { padding: 1.2rem 1.6rem; background: white; border-top: 1px solid var(--border); border-radius: 0 0 24px 24px; gap: 0.6rem; }
        .pm-confirm-btn {
            flex: 1; padding: 0.9rem 1.5rem; border: none; border-radius: 14px; font-size: 0.95rem;
            font-weight: 700; cursor: pointer; transition: all 0.2s; font-family: inherit;
            background: linear-gradient(135deg, var(--success) 0%, #1f5d43 100%);
            color: white; box-shadow: 0 4px 14px rgba(44,122,90,0.3);
            display: flex; align-items: center; justify-content: center; gap: 0.5rem;
        }
        .pm-confirm-btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(44,122,90,0.4); }
        .pm-confirm-btn:disabled { opacity: 0.4; cursor: not-allowed; transform: none; box-shadow: none; }
        .pm-cancel-btn {
            padding: 0.9rem 1.2rem; border: 1.5px solid var(--border); background: white;
            border-radius: 14px; font-size: 0.88rem; font-weight: 600; color: var(--warm-gray);
            cursor: pointer; transition: all 0.18s; font-family: inherit;
        }
        .pm-cancel-btn:hover { background: var(--lavender); border-color: #a0aec0; }

        /* ─── ADDON CHECKBOXES ─── */
        .addon-checkbox { display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.9rem 1rem; border: 1.5px solid var(--border); border-radius: 14px; margin-bottom: 0.6rem; transition: all 0.2s; cursor: pointer; }
        .addon-checkbox:hover { background: #f4f7fb; border-color: var(--primary-light); }
        .addon-checkbox input[type="checkbox"] { width: 18px; height: 18px; cursor: pointer; accent-color: var(--primary); flex-shrink: 0; margin-top: 2px; }
        .addon-checkbox label { flex: 1; cursor: pointer; margin: 0; font-size: 0.9rem; font-weight: 500; line-height: 1.4; }
        .addon-checkbox label .addon-name { display: block; color: var(--navy); font-weight: 600; }
        .addon-checkbox label .addon-price { display: block; color: var(--success); font-weight: 700; font-size: 0.88rem; margin-top: 0.1rem; }
        .addon-checkbox label .addon-status { display: inline-block; margin-top: 0.2rem; }
        .addon-qty-control { display: flex; align-items: center; gap: 0.4rem; flex-shrink: 0; }
        .addon-qty-control input { width: 64px; text-align: center; padding: 0.35rem 0.4rem; border: 1.5px solid var(--border); border-radius: 10px; font-size: 0.85rem; font-family: inherit; }

        /* ─── CONFIRM CLEAR MODAL ─── */
        .clear-confirm-list { background: var(--light-bg); border-radius: 10px; padding: 0.75rem 1rem; max-height: 160px; overflow-y: auto; }
        .clear-confirm-item { font-size: 0.8rem; color: var(--navy); padding: 0.2rem 0; display: flex; align-items: center; gap: 0.4rem; }
        .clear-confirm-item i { color: var(--danger); font-size: 0.6rem; }

        /* Mobile modal z-index fix - appear above cart drawer */
        @media (max-width: 991.98px) {
            .modal { z-index: 1070; }
            .modal-backdrop { z-index: 1065; }
        }

        /* ════════════════ MOBILE ════════════════ */
        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); box-shadow: 4px 0 20px rgba(0,0,0,0.1); }
            .sidebar-overlay { position: fixed; top: var(--topbar-height); left: 0; right: 0; bottom: 0; background: rgba(15,76,92,0.3); backdrop-filter: blur(2px); z-index: 1025; display: none; }
            .sidebar-overlay.show { display: block; }
            .main-content { margin-left: 0; padding: 1.1rem; padding-bottom: calc(var(--mobile-bottom-bar) + 1.1rem); }
            .pos-layout { display: block; }
            .cart-panel { display: none; }
            .mobile-bottom-bar { display: flex !important; position: fixed; bottom: 0; left: 0; right: 0; height: var(--mobile-bottom-bar); background: white; border-top: 1px solid var(--border); z-index: 1050; box-shadow: 0 -4px 16px rgba(15,76,92,0.08); align-items: center; padding: 0 1rem; gap: 0.75rem; }
            .cart-preview-btn { flex: 1; display: flex; align-items: center; gap: 0.65rem; background: none; border: none; cursor: pointer; padding: 0; text-align: left; }
            .cart-preview-icon { position: relative; width: 44px; height: 44px; background: var(--primary); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.1rem; flex-shrink: 0; }
            .cart-badge { position: absolute; top: -5px; right: -5px; background: var(--secondary); color: white; font-size: 0.6rem; font-weight: 700; min-width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid white; padding: 0 2px; }
            .cart-preview-info { flex: 1; }
            .cart-preview-label { font-size: 0.7rem; color: var(--warm-gray); font-weight: 500; line-height: 1; margin-bottom: 2px; }
            .cart-preview-total { font-size: 0.95rem; font-weight: 700; color: var(--navy); line-height: 1; }
            .mobile-place-btn {
                background: linear-gradient(135deg, var(--success), #1f5d43);
                color: white; border: none; border-radius: 12px; padding: 0.65rem 1.2rem;
                font-weight: 700; font-size: 0.83rem; cursor: pointer; transition: all 0.2s;
                white-space: nowrap; display: flex; align-items: center; gap: 0.4rem;
            }
            .mobile-place-btn:disabled { opacity: 0.5; cursor: not-allowed; }
            .mobile-cart-drawer { display: flex !important; flex-direction: column; position: fixed; bottom: 0; left: 0; right: 0; background: white; border-radius: 22px 22px 0 0; box-shadow: 0 -8px 30px rgba(15,76,92,0.12); z-index: 1060; transform: translateY(100%); transition: transform 0.3s cubic-bezier(0.4,0,0.2,1); max-height: 92vh; overflow: hidden; }
            .mobile-cart-drawer.open { transform: translateY(0); }
            .drawer-backdrop { position: fixed; inset: 0; background: rgba(15,76,92,0.35); backdrop-filter: blur(3px); z-index: 1055; display: none; }
            .drawer-backdrop.show { display: block; }
            .drawer-handle { width: 36px; height: 4px; background: #d1d9e6; border-radius: 4px; margin: 0.85rem auto 0; flex-shrink: 0; }
            .drawer-header { padding: 0.85rem 1.2rem 0.6rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; }
            .drawer-title { font-size: 1rem; font-weight: 700; color: var(--navy); display: flex; align-items: center; gap: 0.5rem; }
            .drawer-close { background: none; border: none; color: var(--warm-gray); font-size: 1.1rem; cursor: pointer; padding: 0.2rem; }
            .drawer-body { overflow-y: auto; flex: 1; }
            .drawer-body::-webkit-scrollbar { width: 3px; }
            .drawer-body::-webkit-scrollbar-thumb { background: var(--border); border-radius: 8px; }
            .drawer-footer { padding: 0.75rem 1rem; border-top: 1px solid var(--border); background: white; flex-shrink: 0; }
            .menu-grid { grid-template-columns: repeat(2, 1fr); gap: 0.7rem; }
            .menu-item-image, .menu-item-placeholder { height: 105px; }
            .menu-item-body { padding: 0.65rem; }
            .menu-item-name { font-size: 0.8rem; }
            #paymentModal .modal-dialog { 
                margin: 1rem auto; 
                max-width: calc(100% - 2rem); 
                height: auto;
            }
            #paymentModal .modal-content { 
                border-radius: 20px; 
                max-height: calc(100vh - 2rem);
                overflow-y: auto;
            }
        }
        @media (max-width: 767.98px) {
            .main-content { padding: 1rem; padding-bottom: calc(var(--mobile-bottom-bar) + 1rem); }
            .page-header h1 { font-size: 1.4rem; }
        }
        @media (max-width: 480px) {
            .topbar { padding: 0 1rem; }
            .menu-item-image, .menu-item-placeholder { height: 90px; }
        }
        @media (min-width: 992px) {
            .mobile-bottom-bar { display: none !important; }
            .mobile-cart-drawer { display: none !important; }
            .drawer-backdrop { display: none !important; }
        }
        @media (min-width: 1280px) { .menu-grid { grid-template-columns: repeat(auto-fill, minmax(185px, 1fr)); } }

        .fade-in { animation: fadeUp 0.3s ease forwards; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

<!-- ADD TOAST -->
<div id="addToast"><i class="fas fa-check-circle"></i><span id="addToastText">Added to order</span></div>

<!-- TOPBAR -->
<div class="topbar">
    <div class="d-flex align-items-center gap-3">
        <button class="mobile-menu-btn d-lg-none" id="menuToggle"><i class="fas fa-bars"></i></button>
        <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="logo d-none d-sm-flex">
            <i class="fas fa-utensils"></i><span>Dine with Din</span>
        </a>
        <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="logo d-sm-none">
            <i class="fas fa-utensils"></i>
        </a>
    </div>
    <div class="dropdown">
        <button class="btn btn-link text-decoration-none dropdown-toggle d-flex align-items-center px-2"
                type="button" data-bs-toggle="dropdown" style="color:var(--navy);font-weight:600;font-size:0.85rem;">
            <i class="far fa-user-circle me-2" style="font-size:1.4rem;color:var(--secondary);"></i>
            <span class="d-none d-md-inline"><?= htmlspecialchars($_SESSION['full_name'] ?? 'Admin') ?></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="border-radius:14px;">
            <li><hr class="dropdown-divider my-1"></li>
            <li><a class="dropdown-item text-danger py-2" href="<?= $baseUrl ?>/index.php?page=logout">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
            </a></li>
        </ul>
    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-nav">
        <?php if (hasRole('admin')): ?>
        <div class="nav-section-title">Dashboard</div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></div>
        <div class="nav-section-title">Management</div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_menu" class="nav-link"><i class="fas fa-utensils"></i> Menu Items</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_recipes" class="nav-link"><i class="fas fa-tags"></i> Recipes</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_ingredients" class="nav-link"><i class="fas fa-boxes"></i> Ingredients</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_addons" class="nav-link"><i class="fas fa-plus-circle"></i> Add-ons</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_tables" class="nav-link"><i class="fas fa-chair"></i> Tables</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_users" class="nav-link"><i class="fas fa-users"></i> User Management</a></div>
        <div class="nav-section-title">Sales</div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_pos" class="nav-link active"><i class="fas fa-cash-register"></i> POS System</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_orders" class="nav-link"><i class="fas fa-receipt"></i> Orders</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=reports" class="nav-link"><i class="fas fa-chart-bar"></i> Reports</a></div>
        <?php endif; ?>
        <?php if (hasRole('cashier') && !hasRole('admin')): ?>
        <div class="nav-section-title">Management</div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_tables" class="nav-link"><i class="fas fa-chair"></i> Tables</a></div>
        <div class="nav-section-title">Sales</div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_pos" class="nav-link active"><i class="fas fa-cash-register"></i> POS System</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_orders" class="nav-link"><i class="fas fa-receipt"></i> Orders</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=reports" class="nav-link"><i class="fas fa-chart-bar"></i> Reports</a></div>
        <?php endif; ?>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-cash-register"></i> Point of Sale</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/index.php?page=admin_dashboard">Home</a></li>
                <li class="breadcrumb-item active">POS</li>
            </ol>
        </nav>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show fade-in">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show fade-in">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="pos-layout">

        <!-- ═══ MENU PANEL ═══ -->
        <div class="menu-panel fade-in">
            <div class="menu-panel-header">
                <div class="menu-panel-title"><i class="fas fa-utensils"></i> Menu</div>
                <div class="search-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-input" id="menuSearch" placeholder="Search items...">
                    <button class="search-clear" id="searchClear" onclick="clearSearch()"><i class="fas fa-times"></i></button>
                </div>
            </div>

            <div class="category-tabs-wrapper">
                <div class="category-tabs" id="categoryTabs">
                    <button class="category-tab active" data-category="all">
                        <i class="fas fa-th me-1"></i> All
                        <span class="tab-count">(<?= count($menu_items) ?>)</span>
                    </button>
                    <?php
                    $uniqueCategories = [];
                    $categoryCounts   = [];
                    foreach ($menu_items as $item) {
                        if (!isset($uniqueCategories[$item['category_id']])) {
                            $uniqueCategories[$item['category_id']] = $item['category_name'];
                            $categoryCounts[$item['category_id']]   = 0;
                        }
                        $categoryCounts[$item['category_id']]++;
                    }
                    foreach ($uniqueCategories as $catId => $catName): ?>
                        <button class="category-tab" data-category="<?= $catId ?>">
                            <?= htmlspecialchars($catName) ?>
                            <span class="tab-count">(<?= $categoryCounts[$catId] ?>)</span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="menu-grid-wrapper">
                <div class="menu-grid" id="menuGrid">
                    <?php foreach ($menu_items as $item):
                        $hasAddons = !empty((array)($addons_map[$item['item_id']] ?? []));
                        $itemStock = $stock_data[$item['item_id']] ?? null;
                        $hasStock  = $item['has_stock'] ?? false;
                    ?>
                    <div class="menu-item-card <?= !$hasStock ? 'out-of-stock' : '' ?>"
                         data-category="<?= $item['category_id'] ?>"
                         data-item-id="<?= $item['item_id'] ?>"
                         data-item-name="<?= htmlspecialchars($item['item_name']) ?>"
                         data-item-price="<?= $item['base_price'] ?>"
                         data-category-id="<?= $item['category_id'] ?>"
                         data-search-text="<?= htmlspecialchars(strtolower($item['item_name'])) ?>"
                         data-has-stock="<?= $hasStock ? 'true' : 'false' ?>"
                         data-no-price="<?= ($item['no_price'] ?? false) ? 'true' : 'false' ?>">
                        <?php
                            $noPrice = $item['no_price'] ?? false;
                            if ($noPrice): ?>
                                <div class="out-of-stock-badge" style="background:var(--warm-gray);">Unavailable</div>
                            <?php elseif (!$hasStock): ?>
                                <div class="out-of-stock-badge">Out of Stock</div>
                            <?php endif; ?>
                        <div class="in-cart-badge" id="cartBadge_<?= $item['item_id'] ?>" style="display:none"></div>
                        <?php if (!empty($item['image_url'])): ?>
                            <img src="<?= $baseUrl.'/'.htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['item_name']) ?>" class="menu-item-image" loading="lazy">
                        <?php else: ?>
                            <div class="menu-item-placeholder"><i class="fas fa-utensils"></i></div>
                        <?php endif; ?>
                        <div class="menu-item-body">
                            <div class="menu-item-name"><?= htmlspecialchars($item['item_name']) ?></div>
                            <?php
                            $itemVariants = $variants_map[$item['item_id']] ?? [];
                            if (!empty($itemVariants)) {
                                $prices   = array_column($itemVariants, 'price');
                                $minPrice = min($prices);
                                $maxPrice = max($prices);
                                $priceDisplay = ($minPrice === $maxPrice)
                                    ? '₱' . number_format($minPrice, 2)
                                    : '₱' . number_format($minPrice, 2) . ' – ₱' . number_format($maxPrice, 2);
                            } else {
                                $priceDisplay = '₱' . number_format($item['base_price'], 2);
                            }
                            ?>
                            <div class="menu-item-price"><?= $priceDisplay ?></div>
                            <?php if (!empty($item['ingredients'])): ?>
                                <div class="menu-item-desc"><?php
                                    $ingNames = array_map(fn($i) => $i['ingredient_name'], $item['ingredients']);
                                    echo htmlspecialchars(implode(', ', array_slice($ingNames, 0, 3)));
                                    if (count($ingNames) > 3) echo '...';
                                ?></div>
                            <?php elseif (!empty($item['description'])): ?>
                                <div class="menu-item-desc"><?= htmlspecialchars($item['description']) ?></div>
                            <?php endif; ?>
                            <?php if ($hasAddons): ?>
                                <div class="addon-badge"><i class="fas fa-plus"></i> Add-ons</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="no-results d-none" id="noResults">
                    <i class="fas fa-search"></i>
                    <p>No items found</p>
                    <small>Try a different search or category</small>
                </div>
            </div>
        </div>

        <!-- ═══ CART PANEL (desktop) ═══ -->
        <div class="cart-panel fade-in" style="animation-delay:0.06s">
            <div class="cart-panel-header">
                <div class="cart-panel-title">
                    <i class="fas fa-shopping-cart"></i>
                    Order
                    <span class="cart-item-count-pill" id="cartCountPill" style="display:none">0</span>
                </div>
                <button class="clear-cart-btn d-none" id="clearCartBtn" onclick="confirmClearCart()">
                    <i class="fas fa-trash-alt"></i> Clear
                </button>
            </div>

            <div class="cart-fields">
                <div class="mb-2">
                    <label class="form-label">Order Type <span class="text-danger">*</span></label>
                    <div class="order-type-container">
                        <button type="button" class="order-type-btn active" id="dineInBtn" onclick="setOrderType('dine_in','desktop')">
                            <i class="fas fa-utensils"></i> Dine In
                        </button>
                        <button type="button" class="order-type-btn" id="takeOutBtn" onclick="setOrderType('takeout','desktop')">
                            <i class="fas fa-bag-shopping"></i> Take Out
                        </button>
                    </div>
                    <input type="hidden" id="order_type" value="dine_in">
                </div>
                <div class="mb-2">
                    <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="customer_name" placeholder="e.g. John Santos" autocomplete="off" required>
                </div>
                <div id="tableNumberContainer">
                    <label class="form-label">Table <span class="text-danger">*</span></label>
                    <?php if (!empty($available_tables)): ?>
                        <select class="table-select" id="table_id">
                            <option value="">— Choose a table —</option>
                            <?php foreach ($available_tables as $t): ?>
                                <option value="<?= $t['table_id'] ?>"><?= htmlspecialchars($t['table_name']) ?> (<?= $t['capacity'] ?> seats)</option>
                            <?php endforeach; ?>
                        </select>
                        <div class="table-count-hint"><i class="fas fa-circle-check"></i><?= count($available_tables) ?> table<?= count($available_tables) !== 1 ? 's' : '' ?> available</div>
                    <?php else: ?>
                        <div class="table-unavailable-notice"><i class="fas fa-exclamation-triangle me-1"></i>No tables available — free a table first.</div>
                        <input type="hidden" id="table_id" value="">
                    <?php endif; ?>
                </div>
            </div>

            <div id="draftBanner" class="draft-restored-banner d-none mt-2 mx-3">
                <i class="fas fa-clock-rotate-left"></i>
                <span>Cart restored from your last session.</span>
                <button class="draft-dismiss-btn" onclick="dismissDraftBanner()"><i class="fas fa-times"></i></button>
            </div>

            <div class="cart-items-wrapper" id="cartItemsWrapper">
                <div class="cart-items" id="cartItems">
                    <div class="empty-cart">
                        <div class="empty-cart-icon"><i class="fas fa-shopping-cart"></i></div>
                        <p>Cart is empty</p>
                        <small>Tap a menu item to add it</small>
                    </div>
                </div>
            </div>

            <div class="cart-footer d-none" id="cartFooter">
                <div class="cart-total-row">
                    <span class="cart-total-label">Order Total</span>
                    <span class="cart-total-amount" id="total">₱0.00</span>
                </div>
                <button type="button" class="collect-payment-btn" id="collectPaymentBtn" disabled onclick="openPaymentModal()">
                    <i class="fas fa-credit-card"></i> Collect Payment
                </button>
                <div class="submit-disabled-msg d-none" id="submitDisabledMessage">
                    <i class="fas fa-exclamation-circle"></i> Fix stock issues before proceeding
                </div>
            </div>
        </div>

    </div><!-- end pos-layout -->
</div><!-- end main-content -->

<!-- ══════════ MOBILE BOTTOM BAR ══════════ -->
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
    <button class="mobile-place-btn" id="mobileSubmitBtn" onclick="openPaymentModal()" disabled>
        <i class="fas fa-credit-card"></i> Pay
    </button>
</div>

<div class="drawer-backdrop" id="drawerBackdrop" onclick="closeCartDrawer()"></div>

<!-- ══════════ MOBILE CART DRAWER ══════════ -->
<div class="mobile-cart-drawer" id="mobileCartDrawer" style="display:none">
    <div class="drawer-handle"></div>
    <div class="drawer-header">
        <div class="drawer-title">
            <i class="fas fa-shopping-cart" style="color:var(--primary)"></i>
            Your Order
            <span class="cart-item-count-pill" id="cartCountPillM" style="display:none">0</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="clear-cart-btn d-none" id="clearCartBtnM" onclick="confirmClearCart()">
                <i class="fas fa-trash-alt"></i> Clear
            </button>
            <button class="drawer-close" onclick="closeCartDrawer()"><i class="fas fa-times"></i></button>
        </div>
    </div>
    <div class="drawer-body">
        <div class="cart-fields">
            <div class="mb-2">
                <label class="form-label">Order Type <span class="text-danger">*</span></label>
                <div class="order-type-container">
                    <button type="button" class="order-type-btn active" id="dineInBtnM" onclick="setOrderType('dine_in','mobile')">
                        <i class="fas fa-utensils"></i> Dine In
                    </button>
                    <button type="button" class="order-type-btn" id="takeOutBtnM" onclick="setOrderType('takeout','mobile')">
                        <i class="fas fa-bag-shopping"></i> Take Out
                    </button>
                </div>
            </div>
            <div class="mb-2">
                <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="customer_name_m" placeholder="e.g. John Santos" autocomplete="off" required>
            </div>
            <div id="tableNumberContainerM">
                <label class="form-label">Table <span class="text-danger">*</span></label>
                <?php if (!empty($available_tables)): ?>
                    <select class="table-select" id="table_id_m">
                        <option value="">— Choose a table —</option>
                        <?php foreach ($available_tables as $t): ?>
                            <option value="<?= $t['table_id'] ?>"><?= htmlspecialchars($t['table_name']) ?> (<?= $t['capacity'] ?> seats)</option>
                        <?php endforeach; ?>
                    </select>
                    <div class="table-count-hint"><i class="fas fa-circle-check"></i><?= count($available_tables) ?> table<?= count($available_tables) !== 1 ? 's' : '' ?> available</div>
                <?php else: ?>
                    <div class="table-unavailable-notice"><i class="fas fa-exclamation-triangle me-1"></i>No tables available.</div>
                    <input type="hidden" id="table_id_m" value="">
                <?php endif; ?>
            </div>
            <div id="draftBannerM" class="draft-restored-banner d-none mt-2">
                <i class="fas fa-clock-rotate-left"></i>
                <span>Cart restored from your last session.</span>
                <button class="draft-dismiss-btn" onclick="dismissDraftBanner()"><i class="fas fa-times"></i></button>
            </div>
        </div>
        <hr style="margin:0;border-color:#f0f4f8;">
        <div class="cart-items" id="cartItemsMobile">
            <div class="empty-cart">
                <div class="empty-cart-icon"><i class="fas fa-shopping-cart"></i></div>
                <p>Cart is empty</p>
                <small>Tap a menu item to add it</small>
            </div>
        </div>
    </div>
    <div class="drawer-footer">
        <div class="cart-total-row d-none" id="mobileTotalRow">
            <span class="cart-total-label">Order Total</span>
            <span class="cart-total-amount" id="totalM">₱0.00</span>
        </div>
        <button type="button" class="collect-payment-btn d-none mt-2" id="collectPaymentBtnM" disabled onclick="openPaymentModal()">
            <i class="fas fa-credit-card"></i> Collect Payment
        </button>
        <div class="submit-disabled-msg d-none" id="submitDisabledMessageM">
            <i class="fas fa-exclamation-circle"></i> Fix stock issues before proceeding
        </div>
    </div>
</div>

<!-- ══════════ VARIANT MODAL ══════════ -->
<div class="modal fade" id="variantModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-layer-group me-2" style="color:var(--secondary)"></i>
                    Choose Size — <span id="variantModalItemName"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" id="variantList"></div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- ══════════ ADDONS MODAL ══════════ -->
<div class="modal fade" id="addonsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addonsModalLabel">
                    <i class="fas fa-utensils" style="color:var(--secondary)"></i>
                    <span id="modalItemName"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="addonsContent"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="addToCartBtn">
                    <i class="fas fa-cart-plus me-1"></i> Add to Cart
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══════════ CLEAR CART CONFIRM MODAL ══════════ -->
<div class="modal fade" id="clearCartModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center pt-0">
                <div class="modal-icon warning mb-3"><i class="fas fa-trash-alt"></i></div>
                <h5 class="fw-bold mb-1">Clear cart?</h5>
                <p class="text-muted small mb-3">This will remove all <strong id="clearCount">0</strong> item(s) from your current order.</p>
                <div class="clear-confirm-list text-start" id="clearConfirmList"></div>
            </div>
            <div class="modal-footer border-0 pt-1 gap-2">
                <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">Keep order</button>
                <button type="button" class="btn btn-danger flex-fill" onclick="executeClearCart()">
                    <i class="fas fa-trash-alt me-1"></i> Yes, clear
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══════════ STOCK ERROR MODAL ══════════ -->
<div class="modal fade" id="stockErrorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2" style="color:var(--danger)"></i> Insufficient Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div class="modal-icon danger mb-3"><i class="fas fa-boxes"></i></div>
                <h5 class="mb-2" id="stockErrTitle"></h5>
                <p class="text-muted small mb-3">The following ingredients don't have enough stock:</p>
                <div class="text-start p-3 bg-light rounded" style="max-height:180px;overflow-y:auto">
                    <small class="text-uppercase fw-bold text-muted">Affected Ingredients</small>
                    <ul class="list-unstyled mt-2 mb-0" id="stockErrList"></ul>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-danger w-100" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ══════════ GENERIC ERROR MODAL ══════════ -->
<div class="modal fade" id="genericErrorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2" style="color:var(--danger)"></i> Error</h5>
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

<!-- ══════════════════════════════════════════════════════
     PAYMENT MODAL — Pay-first flow
══════════════════════════════════════════════════════ -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
        <div class="modal-content" style="border-radius:24px;overflow:hidden;border:none;">

            <!-- Gradient header -->
            <div class="pm-header">
                <div class="pm-header-icon"><i class="fas fa-cash-register"></i></div>
                <div class="pm-header-text">
                    <div class="pm-header-title">Collect Payment</div>
                    <div class="pm-header-sub" id="pmOrderSummaryLine">Building order…</div>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
            </div>

            <div class="pm-body">

                <!-- Order summary -->
                <div class="pm-summary">
                    <div class="pm-summary-label"><i class="fas fa-list me-1"></i> Order Summary</div>
                    <div class="pm-summary-items" id="pmSummaryItems">—</div>
                </div>

                <!-- Total due -->
                <div class="pm-total-card">
                    <div>
                        <div class="pm-total-label">Total Due</div>
                    </div>
                    <div class="pm-total-amount">₱<span id="pmTotalAmount">0.00</span></div>
                </div>

                <!-- Cash input -->
                <div class="pm-input-section">
                    <div class="pm-input-label">
                        <i class="fas fa-money-bill-wave" style="color:var(--success)"></i>
                        Cash Tendered
                    </div>
                    <div class="pm-cash-input-wrap">
                        <div class="pm-currency-prefix">₱</div>
                        <input type="number" id="pmCashInput" class="pm-cash-input"
                               placeholder="0.00" min="0" step="0.01" autocomplete="off">
                    </div>
                    <!-- Quick amount buttons — rendered by JS -->
                    <div class="pm-quick-amounts" id="pmQuickAmounts"></div>
                </div>

                <!-- Change display -->
                <div class="pm-change-card change-none" id="pmChangeCard">
                    <div class="pm-change-icon"><i class="fas fa-coins"></i></div>
                    <div class="pm-change-info">
                        <div class="pm-change-sublabel">Change</div>
                        <div class="pm-change-value" id="pmChangeValue">—</div>
                    </div>
                </div>

            </div>

            <div class="modal-footer pm-footer d-flex">
                <button type="button" class="pm-cancel-btn" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="pm-confirm-btn" id="pmConfirmBtn" disabled onclick="confirmPaymentAndSubmit()">
                    <i class="fas fa-check-circle me-1"></i> Confirm & Place Order
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const stockData      = <?= json_encode($stock_data      ?? []) ?>;
const addonStockData = <?= json_encode($addon_stock_data ?? []) ?>;
const addonsMap      = <?= json_encode($addons_map       ?? []) ?>;
const variantsMap    = <?= json_encode($variants_map     ?? []) ?>;
const DB_DRAFT_CART  = <?= json_encode($draft_cart      ?? []) ?>;
const DB_DRAFT_META  = <?= json_encode($draft_meta      ?? null) ?>;
const BASE_URL       = <?= json_encode($baseUrl) ?>;
const CASHIER_NAME   = <?= json_encode($_SESSION['full_name'] ?? 'Cashier') ?>;

let cart               = [];
let currentItem        = null;
let currentVariantId   = null;
let currentVariantName = null;
let currentOrderType   = 'dine_in';
let hasStockIssues     = false;
let lastOrderData      = null;

const isMobile = () => window.innerWidth < 992;

// ─────────────────────────────────────────────────────────────────
// TOAST
// ─────────────────────────────────────────────────────────────────
let _toastTimer = null;
function showAddToast(name) {
    const el   = document.getElementById('addToast');
    const text = document.getElementById('addToastText');
    if (!el) return;
    if (_toastTimer) clearTimeout(_toastTimer);
    text.textContent = `${name} added`;
    el.classList.add('show');
    _toastTimer = setTimeout(() => el.classList.remove('show'), 1800);
}

function flashCard(itemId) {
    const card = document.querySelector(`.menu-item-card[data-item-id="${itemId}"]`);
    if (!card) return;
    card.classList.remove('just-added');
    void card.offsetWidth;
    card.classList.add('just-added');
    setTimeout(() => card.classList.remove('just-added'), 500);
}

// ─────────────────────────────────────────────────────────────────
// DRAFT PERSISTENCE
// ─────────────────────────────────────────────────────────────────
function saveDraftToServer() {
    const mobile       = isMobile();
    const orderType    = document.getElementById('order_type').value;
    const customerName = mobile
        ? (document.getElementById('customer_name_m')?.value.trim() || document.getElementById('customer_name')?.value.trim())
        : document.getElementById('customer_name')?.value.trim();
    const tableIdEl    = mobile
        ? (document.getElementById('table_id_m') || document.getElementById('table_id'))
        : document.getElementById('table_id');

    navigator.sendBeacon('index.php?page=manage_pos', new URLSearchParams({
        action:        'save_draft',
        cart_data:     JSON.stringify(cart),
        customer_name: customerName || '',
        order_type:    orderType,
        table_id:      tableIdEl?.value || '',
    }));
}

function saveCart() {
    localStorage.setItem('pos_cart', JSON.stringify(cart));
    saveDraftToServer();
}

function loadCart() {
    try { cart = JSON.parse(localStorage.getItem('pos_cart') || '[]'); } catch(e) { cart = []; }
}

// ─────────────────────────────────────────────────────────────────
// MODAL HELPERS
// ─────────────────────────────────────────────────────────────────
function showModal(id) {
    const el = document.getElementById(id);
    const existing = bootstrap.Modal.getInstance(el);
    if (existing) existing.dispose();
    new bootstrap.Modal(el).show();
}

function hideModal(id) {
    bootstrap.Modal.getInstance(document.getElementById(id))?.hide();
}

document.addEventListener('hidden.bs.modal', () => {
    if (document.querySelector('.modal.show')) return;
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.classList.remove('modal-open');
    document.body.style.overflow    = '';
    document.body.style.paddingRight = '';
});

function showStockErrorModal(title, missing) {
    document.getElementById('stockErrTitle').textContent = title;
    const list = document.getElementById('stockErrList');
    list.innerHTML = '';
    missing.forEach(ing => {
        const li = document.createElement('li');
        li.className = 'mb-2 pb-1 border-bottom';
        li.innerHTML = `<div class="d-flex justify-content-between align-items-center flex-wrap gap-1">
            <span><i class="fas fa-leaf me-2 text-danger"></i>${ing.name}</span>
            <span class="badge bg-danger">Need: ${(+ing.required).toFixed(2)} | Have: ${(+ing.available).toFixed(2)}</span>
        </div>`;
        list.appendChild(li);
    });
    showModal('stockErrorModal');
}

function showErrorModal(title, msg) {
    document.getElementById('genericErrTitle').textContent = title;
    document.getElementById('genericErrMsg').textContent   = msg;
    showModal('genericErrorModal');
}

// ─────────────────────────────────────────────────────────────────
// CLEAR CART
// ─────────────────────────────────────────────────────────────────
function confirmClearCart() {
    if (!cart.length) return;
    document.getElementById('clearCount').textContent = cart.reduce((s, r) => s + r.quantity, 0);
    const list = document.getElementById('clearConfirmList');
    list.innerHTML = cart.slice(0, 6).map(r =>
        `<div class="clear-confirm-item"><i class="fas fa-circle"></i>${r.name} ×${r.quantity}</div>`
    ).join('') + (cart.length > 6 ? `<div class="clear-confirm-item text-muted">…and ${cart.length - 6} more</div>` : '');
    showModal('clearCartModal');
}

function executeClearCart() {
    hideModal('clearCartModal');
    setTimeout(() => {
        cart = [];
        validateAndRender();
        saveCart();
        sessionStorage.removeItem('draft_banner_shown');
    }, 200);
}

// ─────────────────────────────────────────────────────────────────
// DRAWER
// ─────────────────────────────────────────────────────────────────
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

function dismissDraftBanner() {
    document.getElementById('draftBanner')?.classList.add('d-none');
    document.getElementById('draftBannerM')?.classList.add('d-none');
}

// ─────────────────────────────────────────────────────────────────
// STOCK VALIDATION
// ─────────────────────────────────────────────────────────────────
function buildDemandMap(rows) {
    const d = {};
    for (const row of rows) {
        const itemStock = stockData[row.id];
        let ingredients;
        if (row.variant_id && itemStock?.variants?.[row.variant_id]?.ingredients?.length) {
            ingredients = itemStock.variants[row.variant_id].ingredients;
        } else {
            ingredients = itemStock?.base?.ingredients || [];
        }
        ingredients.forEach(ing => {
            if (!ing.quantity_required || ing.quantity_required <= 0) return;
            if (!d[ing.ingredient_id]) {
                d[ing.ingredient_id] = { name: ing.ingredient_name, needed: 0, available: +ing.stock_quantity };
            }
            d[ing.ingredient_id].needed += ing.quantity_required * row.quantity;
        });
        (row.addons || []).forEach(a => {
            (addonStockData[a.id]?.ingredients || []).forEach(ing => {
                if (!ing.quantity_required || ing.quantity_required <= 0) return;
                if (!d[ing.ingredient_id]) {
                    d[ing.ingredient_id] = { name: ing.ingredient_name, needed: 0, available: +ing.stock_quantity };
                }
                d[ing.ingredient_id].needed += ing.quantity_required * a.quantity;
            });
        });
    }
    return d;
}

function validateCumulative(rows) {
    const conflicts = Object.values(buildDemandMap(rows))
        .filter(i => i.needed > i.available)
        .map(i => ({ name: i.name, required: i.needed, available: i.available }));
    return { success: !conflicts.length, missingIngredients: conflicts };
}

function checkCanAdd(id, qty, addons, variantId = null) {
    return validateCumulative([...cart, { id, quantity: qty, variant_id: variantId, addons: addons || [] }]);
}

function checkCanChangeQty(idx, newQty) {
    return validateCumulative(cart.map((r, i) => i === idx ? { ...r, quantity: newQty } : r));
}

// ─────────────────────────────────────────────────────────────────
// CART MUTATIONS
// ─────────────────────────────────────────────────────────────────
function addToCartWithQuantity(item, addons, quantity, note, variantId, variantName) {
    const trimmedNote = (note || '').trim();

    if (!trimmedNote) {
        const ei = cart.findIndex(r =>
            r.id         === item.id &&
            r.variant_id === (variantId ?? null) &&
            (r.note || '') === '' &&
            r.addons.length === addons.length &&
            r.addons.map(a => `${a.id}:${a.quantity}`).sort().join('|') ===
              addons.map(a => `${a.id}:${a.quantity}`).sort().join('|')
        );
        if (ei > -1) {
            const mq = cart[ei].quantity + quantity;
            const mc = checkCanChangeQty(ei, mq);
            if (!mc.success) { showStockErrorModal(`Cannot increase "${item.name}" — exceeds stock`, mc.missingIngredients); return; }
            cart[ei].quantity = mq;
            validateAndRender(); saveCart();
            flashCard(item.id);
            showAddToast(item.name);
            return;
        }
    }

    cart.push({
        id:            item.id,
        name:          item.name,
        price:         item.price,
        quantity,
        variant_id:    variantId   ?? null,
        variantName:   variantName ?? null,
        addons:        addons.map(a => ({ ...a })),
        note:          trimmedNote,
        hasStockIssue: false
    });
    validateAndRender(); saveCart();
    flashCard(item.id);
    showAddToast(variantName ? `${item.name} (${variantName})` : item.name);
}

function updateQuantity(idx, change) {
    const nq = cart[idx].quantity + change;
    if (nq < 1) { cart.splice(idx, 1); validateAndRender(); saveCart(); return; }
    if (change > 0) {
        const c = checkCanChangeQty(idx, nq);
        if (!c.success) { showStockErrorModal(`Cannot increase "${cart[idx].name}" — exceeds stock`, c.missingIngredients); return; }
    }
    cart[idx].quantity = nq;
    cart[idx].hasStockIssue = false;
    validateAndRender(); saveCart();
}

function setQuantity(idx, val) {
    const qty = parseInt(val);
    if (isNaN(qty) || qty < 1) { cart.splice(idx, 1); validateAndRender(); saveCart(); return; }
    const c = checkCanChangeQty(idx, qty);
    if (!c.success) { showStockErrorModal(`Cannot set qty for "${cart[idx].name}" — exceeds stock`, c.missingIngredients); renderCart(); return; }
    cart[idx].quantity = qty;
    cart[idx].hasStockIssue = false;
    validateAndRender(); saveCart();
}

function removeFromCart(idx) { cart.splice(idx, 1); validateAndRender(); saveCart(); }

// ─────────────────────────────────────────────────────────────────
// NOTES
// ─────────────────────────────────────────────────────────────────
function showNoteEditor(key, idx) {
    document.querySelectorAll('.cart-item-note-editor').forEach(el => {
        if (el.getAttribute('data-note-editor') !== key) el.style.display = 'none';
    });
    document.querySelectorAll('[data-note-display]').forEach(el => {
        if (el.getAttribute('data-note-display') !== key) el.style.display = '';
    });
    const display = document.querySelector(`[data-note-display="${key}"]`);
    const editor  = document.querySelector(`[data-note-editor="${key}"]`);
    if (display) display.style.display = 'none';
    if (editor) {
        editor.style.display = 'block';
        const ta = editor.querySelector('textarea');
        if (ta) { ta.value = cart[idx]?.note || ''; ta.focus(); ta.setSelectionRange(ta.value.length, ta.value.length); }
    }
}

function saveNote(key, idx) {
    const editor = document.querySelector(`[data-note-editor="${key}"]`);
    const ta     = editor?.querySelector('textarea');
    if (!ta) return;
    cart[idx].note = ta.value.trim();
    saveCart();
    renderCart();
}

function cancelNoteEdit(key) {
    const editor  = document.querySelector(`[data-note-editor="${key}"]`);
    const display = document.querySelector(`[data-note-display="${key}"]`);
    if (editor)  editor.style.display  = 'none';
    if (display) display.style.display = '';
}

function handleNoteKeydown(e, key, idx) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); saveNote(key, idx); }
    if (e.key === 'Escape') cancelNoteEdit(key);
}

// ─────────────────────────────────────────────────────────────────
// RENDER
// ─────────────────────────────────────────────────────────────────
function escHtml(s) {
    return String(s)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

function buildCartItemHTML(row, idx, containerKey) {
    const key         = `${containerKey}_${idx}`;
    const addonsTotal = row.addons.reduce((s, a) => s + a.price * a.quantity, 0);
    const lineTotal   = (row.price + addonsTotal) * row.quantity;
    const hasIssue    = row.hasStockIssue === true;
    const note        = row.note || '';

    const variantLabel = row.variantName
        ? `<div class="cart-item-variant"><i class="fas fa-layer-group"></i> ${escHtml(row.variantName)}</div>`
        : '';

    const noteDisplay = note
        ? `<div class="cart-item-note" data-note-display="${key}" onclick="showNoteEditor('${key}',${idx})"><i class="fas fa-pen-to-square"></i><span class="cart-item-note-text">${escHtml(note)}</span></div>`
        : `<div class="cart-item-note-empty" data-note-display="${key}" onclick="showNoteEditor('${key}',${idx})"><i class="fas fa-pen-to-square"></i><span>Add note...</span></div>`;

    const noteEditor = `<div class="cart-item-note-editor" data-note-editor="${key}" style="display:none">
        <textarea rows="2" maxlength="300" placeholder="e.g. No sugar, extra hot..." onkeydown="handleNoteKeydown(event,'${key}',${idx})">${escHtml(note)}</textarea>
        <div class="note-actions">
            <button class="note-cancel-btn" onclick="cancelNoteEdit('${key}')">Cancel</button>
            <button class="note-save-btn" onclick="saveNote('${key}',${idx})"><i class="fas fa-check me-1"></i>Save</button>
        </div>
    </div>`;

    return `<div class="cart-item ${hasIssue ? 'insufficient-stock' : ''}">
        <div class="cart-item-top">
            <div class="cart-item-info">
                <div class="cart-item-name" title="${escHtml(row.name)}">${escHtml(row.name)}</div>
                ${variantLabel}
                <div class="cart-item-price">₱${lineTotal.toFixed(2)}</div>
            </div>
            <button class="cart-item-remove" onclick="removeFromCart(${idx})" title="Remove"><i class="fas fa-times-circle"></i></button>
        </div>
        ${row.addons.length ? `<div class="addon-item">${row.addons.map(a => `+${a.name}${a.quantity > 1 ? ` ×${a.quantity}` : ''} ₱${(a.price * a.quantity).toFixed(2)}`).join(' · ')}</div>` : ''}
        ${noteDisplay}
        ${noteEditor}
        ${hasIssue ? `<div class="stock-warning-danger"><i class="fas fa-exclamation-circle"></i> Exceeds available stock</div>` : ''}
        <div class="cart-item-qty">
            <button class="qty-btn" onclick="updateQuantity(${idx},-1)" ${row.quantity <= 1 ? 'disabled' : ''}><i class="fas fa-minus"></i></button>
            <input type="number" class="qty-input" value="${row.quantity}" min="1" onchange="setQuantity(${idx},this.value)">
            <button class="qty-btn" onclick="updateQuantity(${idx},1)" ${hasIssue ? 'disabled' : ''}><i class="fas fa-plus"></i></button>
        </div>
    </div>`;
}

function renderCart() {
    const totalItems = cart.reduce((s, r) => s + r.quantity, 0);
    const total      = cart.reduce((s, r) => s + (r.price + r.addons.reduce((a, b) => a + b.price * b.quantity, 0)) * r.quantity, 0);
    const emptyHTML  = `<div class="empty-cart"><div class="empty-cart-icon"><i class="fas fa-shopping-cart"></i></div><p>Cart is empty</p><small>Tap a menu item to add it</small></div>`;

    [{ id: 'cartItems', key: 'desk' }, { id: 'cartItemsMobile', key: 'mob' }].forEach(({ id, key }) => {
        const el = document.getElementById(id);
        if (!el) return;
        el.innerHTML = cart.length ? cart.map((r, i) => buildCartItemHTML(r, i, key)).join('') : emptyHTML;
    });

    const upd = (id, fn) => { const el = document.getElementById(id); if (el) fn(el); };
    const showCart = cart.length > 0;

    ['cartCountPill', 'cartCountPillM'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        el.textContent = totalItems > 99 ? '99+' : totalItems;
        el.style.display = showCart ? '' : 'none';
    });
    ['clearCartBtn', 'clearCartBtnM'].forEach(id => { upd(id, el => el.classList.toggle('d-none', !showCart)); });

    upd('cartFooter',            el => el.classList.toggle('d-none', !showCart));
    upd('collectPaymentBtn',     el => { el.disabled = hasStockIssues || !showCart; });
    upd('submitDisabledMessage', el => el.classList.toggle('d-none', !hasStockIssues || !showCart));
    if (showCart) upd('total', el => el.textContent = `₱${total.toFixed(2)}`);

    upd('mobileTotalRow',         el => el.classList.toggle('d-none', !showCart));
    upd('collectPaymentBtnM',     el => { el.classList.toggle('d-none', !showCart); el.disabled = hasStockIssues; });
    upd('submitDisabledMessageM', el => el.classList.toggle('d-none', !hasStockIssues || !showCart));
    if (showCart) upd('totalM', el => el.textContent = `₱${total.toFixed(2)}`);

    const badge  = document.getElementById('mobileBadge');
    const mCount = document.getElementById('mobileCartCount');
    const mTotal = document.getElementById('mobileCartTotal');
    const mBtn   = document.getElementById('mobileSubmitBtn');
    if (showCart) {
        if (badge)  { badge.textContent = totalItems > 99 ? '99+' : totalItems; badge.style.display = 'flex'; }
        if (mCount) mCount.textContent = `${totalItems} item${totalItems !== 1 ? 's' : ''}`;
        if (mTotal) mTotal.textContent = `₱${total.toFixed(2)}`;
    } else {
        if (badge)  badge.style.display = 'none';
        if (mCount) mCount.textContent = 'No items';
        if (mTotal) mTotal.textContent = '₱0.00';
    }
    if (mBtn) mBtn.disabled = hasStockIssues || !showCart;

    const cartCounts = {};
    cart.forEach(r => { cartCounts[r.id] = (cartCounts[r.id] || 0) + r.quantity; });
    document.querySelectorAll('.menu-item-card').forEach(card => {
        const id    = card.dataset.itemId;
        const cnt   = cartCounts[id] || 0;
        const badge = document.getElementById(`cartBadge_${id}`);
        if (badge) { badge.textContent = cnt > 0 ? cnt : ''; badge.style.display = cnt > 0 ? 'flex' : 'none'; }
        card.classList.toggle('in-cart', cnt > 0);
    });
}

function validateAndRender() {
    const check = validateCumulative(cart);
    if (!check.success) {
        const conflictNames = new Set(check.missingIngredients.map(i => i.name));
        cart.forEach(row => {
            const itemStock = stockData[row.id];
            let ingredients;
            if (row.variant_id && itemStock?.variants?.[row.variant_id]?.ingredients?.length) {
                ingredients = itemStock.variants[row.variant_id].ingredients;
            } else {
                ingredients = itemStock?.base?.ingredients || [];
            }
            const addonIngredients = (row.addons || []).flatMap(a =>
                (addonStockData[a.id]?.ingredients || []).map(i => i.ingredient_name)
            );
            const allIngredientNames = new Set([...ingredients.map(i => i.ingredient_name), ...addonIngredients]);
            row.hasStockIssue = [...allIngredientNames].some(n => conflictNames.has(n));
        });
        hasStockIssues = true;
    } else {
        cart.forEach(r => r.hasStockIssue = false);
        hasStockIssues = false;
    }
    renderCart();
}

// ─────────────────────────────────────────────────────────────────
// ORDER TYPE
// ─────────────────────────────────────────────────────────────────
function setOrderType(type, context) {
    currentOrderType = type;
    document.getElementById('order_type').value = type;
    if (context === 'desktop' || !isMobile()) {
        document.getElementById('dineInBtn').classList.toggle('active', type === 'dine_in');
        document.getElementById('takeOutBtn').classList.toggle('active', type === 'takeout');
        const box = document.getElementById('tableNumberContainer');
        if (box) box.style.display = type === 'dine_in' ? '' : 'none';
        if (type === 'takeout') { const s = document.getElementById('table_id'); if (s) s.value = ''; }
    }
    if (context === 'mobile' || isMobile()) {
        document.getElementById('dineInBtnM').classList.toggle('active', type === 'dine_in');
        document.getElementById('takeOutBtnM').classList.toggle('active', type === 'takeout');
        const boxM = document.getElementById('tableNumberContainerM');
        if (boxM) boxM.style.display = type === 'dine_in' ? '' : 'none';
        if (type === 'takeout') { const s = document.getElementById('table_id_m'); if (s) s.value = ''; }
    }
    saveDraftToServer();
}

// ─────────────────────────────────────────────────────────────────
// SEARCH & CATEGORIES
// ─────────────────────────────────────────────────────────────────
function filterMenu() {
    const query    = document.getElementById('menuSearch').value.trim().toLowerCase();
    const clearBtn = document.getElementById('searchClear');
    if (clearBtn) clearBtn.style.display = query ? 'block' : 'none';
    const activeCat = document.querySelector('.category-tab.active')?.dataset.category || 'all';
    let visible = 0;
    document.querySelectorAll('.menu-item-card').forEach(card => {
        const matchCat  = activeCat === 'all' || card.dataset.category === activeCat;
        const matchText = !query || card.dataset.searchText.includes(query);
        const show = matchCat && matchText;
        card.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    const noResults = document.getElementById('noResults');
    if (noResults) noResults.classList.toggle('d-none', visible > 0);
}

function clearSearch() {
    const input = document.getElementById('menuSearch');
    if (input) { input.value = ''; input.focus(); }
    filterMenu();
}

document.getElementById('menuSearch')?.addEventListener('input', filterMenu);

document.querySelectorAll('.category-tab').forEach(tab => {
    tab.addEventListener('click', function () {
        document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        filterMenu();
    });
});

// ─────────────────────────────────────────────────────────────────
// VARIANT PICKER
// ─────────────────────────────────────────────────────────────────
const _variantPickerData = new Map();

function openVariantModal(item, variants) {
    document.getElementById('variantModalItemName').textContent = item.name;
    _variantPickerData.clear();
    let html = '';
    variants.forEach((v, idx) => {
        _variantPickerData.set(idx, { item, variant: v });
        const vStock   = stockData[item.id]?.variants?.[v.variant_id];
        const hasStock = vStock?.has_stock  ?? false;
        const noRecipe = vStock?.no_recipe  ?? false;
        const maxQty   = vStock?.max_quantity ?? 0;
        const isAvailable = hasStock && !noRecipe;
        const outCls   = isAvailable ? '' : 'out-of-stock-variant';
        const clickAttr = isAvailable ? `onclick="selectVariant(${idx})"` : '';
        let badge = '';
        if (noRecipe)       badge = '<span class="badge bg-secondary ms-2">Unavailable</span>';
        else if (!hasStock) badge = '<span class="badge bg-danger ms-2">Out of Stock</span>';
        else if (maxQty < 10) badge = `<span class="badge bg-warning text-dark ms-2">Only ${maxQty} left</span>`;
        html += `<div class="variant-option ${outCls}" ${clickAttr}>
            <div class="variant-info"><span class="variant-name">${escHtml(v.variant_name)}</span>${badge}</div>
            <span class="variant-price">₱${parseFloat(v.price).toFixed(2)}</span>
        </div>`;
    });
    document.getElementById('variantList').innerHTML = html;
    showModal('variantModal');
}

function selectVariant(idx) {
    const entry = _variantPickerData.get(idx);
    if (!entry) return;
    const { item, variant } = entry;
    hideModal('variantModal');
    const resolvedItem = { ...item, price: parseFloat(variant.price) };
    setTimeout(() => openAddonsModal(resolvedItem, variant.variant_id, variant.variant_name), 200);
}

// ─────────────────────────────────────────────────────────────────
// ADDONS MODAL
// ─────────────────────────────────────────────────────────────────
function openAddonsModal(item, variantId, variantName) {
    currentItem        = item;
    currentVariantId   = variantId   ?? null;
    currentVariantName = variantName ?? null;

    const activeAddons = (addonsMap[item.id] || []).filter(a => a.is_active);
    const stockKey = (variantId !== null && variantId !== undefined)
        ? stockData[item.id]?.variants?.[variantId]
        : stockData[item.id]?.base;
    const maxQty = stockKey?.max_quantity ?? 0;

    let html = '';
    if (activeAddons.length > 0) {
        document.getElementById('addonsModalLabel').innerHTML =
            `<i class="fas fa-plus-circle" style="color:var(--secondary)"></i> Customize: ${escHtml(item.name)}` +
            (variantName ? ` <small class="text-muted">(${escHtml(variantName)})</small>` : '');
        html += `<div class="mb-3"><h6 class="fw-bold mb-2 small text-uppercase text-muted">Available Add-ons</h6>`;
        activeAddons.forEach(addon => {
            const as     = addonStockData[addon.addon_id] || {};
            const hasStk = (as.max_quantity ?? 0) > 0;
            const maxA   = as.max_quantity ?? 0;
            html += `<div class="addon-checkbox ${!hasStk ? 'opacity-50' : ''}">
                <input type="checkbox" id="addon_${addon.addon_id}" value="${addon.addon_id}"
                       data-name="${escHtml(addon.addon_name)}" data-price="${addon.price}"
                       onchange="toggleAddonQty(${addon.addon_id})" ${!hasStk ? 'disabled' : ''}>
                <label for="addon_${addon.addon_id}">
                    <span class="addon-name ${!hasStk ? 'text-muted' : ''}">${escHtml(addon.addon_name)}</span>
                    <span class="addon-price">+₱${parseFloat(addon.price).toFixed(2)}</span>
                    <span class="addon-status">${!hasStk ? '<span class="badge bg-danger">Out of Stock</span>' : ''}${hasStk && maxA < 10 ? `<span class="badge bg-warning text-dark">Only ${maxA} left</span>` : ''}</span>
                </label>
                <div class="addon-qty-control" id="qty_${addon.addon_id}" style="display:none">
                    <input type="number" min="1" max="${maxA}" value="1" id="qty_input_${addon.addon_id}" class="form-control form-control-sm" ${!hasStk ? 'disabled' : ''}>
                </div>
            </div>`;
        });
        html += `</div>`;
    } else {
        document.getElementById('addonsModalLabel').innerHTML =
            `<i class="fas fa-utensils" style="color:var(--secondary)"></i> ${escHtml(item.name)}` +
            (variantName ? ` <small class="text-muted">(${escHtml(variantName)})</small>` : '');
        html += `<div class="text-center py-2">
            <div class="modal-icon primary mb-2"><i class="fas fa-utensils"></i></div>
            <p class="fw-bold mb-1">${escHtml(item.name)}</p>
            ${variantName ? `<p class="text-muted small mb-1">${escHtml(variantName)}</p>` : ''}
            <p class="text-success fw-bold">₱${item.price.toFixed(2)}</p>
        </div>`;
    }

    html += `<div class="p-3 bg-light rounded-3 mb-0">
        <label class="form-label fw-bold small">Quantity</label>
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-outline-secondary btn-sm px-3" type="button" onclick="decModalQty()">−</button>
            <input type="number" id="modalItemQty" class="form-control text-center fw-bold" value="1" min="1" max="${maxQty}" style="width:75px">
            <button class="btn btn-outline-secondary btn-sm px-3" type="button" onclick="incModalQty()">+</button>
        </div>
        <div class="text-muted mt-1" style="font-size:0.72rem">Max available: ${maxQty}</div>
    </div>
    <div class="note-field-wrapper">
        <div class="note-field-label"><i class="fas fa-pen-to-square"></i> Order Note <span style="font-weight:400;opacity:0.65">(optional)</span></div>
        <textarea id="modalItemNote" rows="2" maxlength="300" placeholder="e.g. No sugar, less ice, extra hot..."></textarea>
        <div class="note-field-hint"><i class="fas fa-circle-info me-1"></i> Max 300 characters</div>
    </div>`;

    document.getElementById('addonsContent').innerHTML = html;
    showModal('addonsModal');
}

// ─────────────────────────────────────────────────────────────────
// MENU ITEM CLICK
// ─────────────────────────────────────────────────────────────────
document.querySelectorAll('.menu-item-card').forEach(card => {
    card.addEventListener('click', function () {
        if (this.classList.contains('out-of-stock')) return;
        const itemId    = this.dataset.itemId;
        const itemName  = this.dataset.itemName;
        const basePrice = parseFloat(this.dataset.itemPrice);
        const catId     = parseInt(this.dataset.categoryId);
        const variants  = variantsMap[itemId] || [];
        const item      = { id: itemId, name: itemName, price: basePrice, categoryId: catId };
        if (variants.length > 0) {
            openVariantModal(item, variants);
        } else {
            currentVariantId   = null;
            currentVariantName = null;
            openAddonsModal(item, null, null);
        }
    });
});

function decModalQty() { const i = document.getElementById('modalItemQty'); if (i && +i.value > 1) i.value = +i.value - 1; }
function incModalQty() { const i = document.getElementById('modalItemQty'); if (i && +i.value < +i.max) i.value = +i.value + 1; }
function toggleAddonQty(id) { const cb = document.getElementById(`addon_${id}`); const ctl = document.getElementById(`qty_${id}`); if (ctl) ctl.style.display = cb.checked ? 'flex' : 'none'; }

document.getElementById('addToCartBtn').addEventListener('click', () => {
    const qty  = Math.max(1, parseInt(document.getElementById('modalItemQty')?.value) || 1);
    const note = (document.getElementById('modalItemNote')?.value || '').trim();
    const adds = Array.from(document.querySelectorAll('#addonsContent input[type="checkbox"]:checked'))
        .map(cb => ({
            id:       parseInt(cb.value),
            name:     cb.dataset.name,
            price:    parseFloat(cb.dataset.price),
            quantity: parseInt(document.getElementById(`qty_input_${cb.value}`)?.value) || 1
        }));
    const c = checkCanAdd(currentItem.id, qty, adds, currentVariantId);
    if (!c.success) { showStockErrorModal(`Cannot add "${currentItem.name}" — exceeds stock`, c.missingIngredients); return; }
    addToCartWithQuantity(currentItem, adds, qty, note, currentVariantId, currentVariantName);
    hideModal('addonsModal');
});

// ─────────────────────────────────────────────────────────────────
// COLLECT ORDER DATA
// ─────────────────────────────────────────────────────────────────
function collectOrderData() {
    const mobile       = isMobile();
    const orderType    = document.getElementById('order_type').value;
    const customerName = mobile
        ? (document.getElementById('customer_name_m')?.value.trim() || document.getElementById('customer_name')?.value.trim())
        : document.getElementById('customer_name')?.value.trim();
    const tableIdEl    = mobile
        ? (document.getElementById('table_id_m') || document.getElementById('table_id'))
        : document.getElementById('table_id');
    return { orderType, customerName, tableId: tableIdEl?.value || '' };
}

// ─────────────────────────────────────────────────────────────────
// PAYMENT MODAL
// ─────────────────────────────────────────────────────────────────
let _pmTotal = 0;

function openPaymentModal() {
    if (!cart.length) { showErrorModal('Empty Cart', 'Please add items before collecting payment.'); return; }
    const fc = validateCumulative(cart);
    if (!fc.success) { showStockErrorModal('Cannot proceed — stock exceeded', fc.missingIngredients); return; }
    const { orderType, customerName, tableId } = collectOrderData();
    if (!orderType) { showErrorModal('Order Type Required', 'Please select an order type.'); return; }
    if (!customerName || customerName.trim() === '') { showErrorModal('Customer Name Required', 'Please enter a customer name.'); return; }
    if (orderType === 'dine_in' && !tableId) { showErrorModal('Table Required', 'Please select a table for dine-in orders.'); return; }

    if (isMobile()) closeCartDrawer();

    _pmTotal = cart.reduce((s, r) => s + (r.price + r.addons.reduce((a, b) => a + b.price * b.quantity, 0)) * r.quantity, 0);
    const totalItems = cart.reduce((s, r) => s + r.quantity, 0);

    document.getElementById('pmOrderSummaryLine').textContent =
        `${totalItems} item${totalItems !== 1 ? 's' : ''} · ${orderType === 'dine_in' ? 'Dine In' : 'Take Out'}`;

    const summaryEl = document.getElementById('pmSummaryItems');
    let summaryHTML = '';
    cart.forEach(row => {
        const lineTotal = (row.price + row.addons.reduce((a, b) => a + b.price * b.quantity, 0)) * row.quantity;
        const label = row.variantName ? `${row.name} (${row.variantName})` : row.name;
        summaryHTML += `<div class="pm-summary-item-row">
            <span class="pm-summary-item-name">${escHtml(label)} ×${row.quantity}</span>
            <span class="pm-summary-item-price">₱${lineTotal.toFixed(2)}</span>
        </div>`;
        row.addons.forEach(a => {
            summaryHTML += `<div class="pm-summary-item-row" style="padding-left:12px;opacity:0.7;">
                <span class="pm-summary-item-name">+ ${escHtml(a.name)}</span>
                <span class="pm-summary-item-price">₱${(a.price * a.quantity).toFixed(2)}</span>
            </div>`;
        });
    });
    summaryHTML += `<hr class="pm-summary-divider"><div class="pm-summary-item-row" style="font-weight:700;">
        <span>Total</span><span>₱${_pmTotal.toFixed(2)}</span></div>`;
    summaryEl.innerHTML = summaryHTML;

    document.getElementById('pmTotalAmount').textContent = _pmTotal.toFixed(2);

    const cashInput = document.getElementById('pmCashInput');
    cashInput.value = '';
    cashInput.className = 'pm-cash-input';
    updateChangeDisplay(0, false);
    document.getElementById('pmConfirmBtn').disabled = true;

    buildQuickAmounts(_pmTotal);

    showModal('paymentModal');
    setTimeout(() => cashInput.focus(), 350);
}

function buildQuickAmounts(total) {
    const roundUp = (n, to) => Math.ceil(n / to) * to;
    const suggestions = new Set();
    suggestions.add(total);
    [20, 50, 100, 200, 500, 1000].forEach(d => {
        const rounded = roundUp(total, d);
        if (rounded >= total && rounded <= total * 3) suggestions.add(rounded);
    });
    const sorted = [...suggestions].sort((a, b) => a - b).slice(0, 5);
    const container = document.getElementById('pmQuickAmounts');
    container.innerHTML = sorted.map(amt => {
        const isExact = amt === total;
        return `<button class="pm-quick-btn ${isExact ? 'exact' : ''}" onclick="setQuickAmount(${amt})">
            ${isExact ? '<i class="fas fa-equals me-1"></i>Exact' : ''}₱${amt.toFixed(amt % 1 === 0 ? 0 : 2)}
        </button>`;
    }).join('');
}

function setQuickAmount(amt) {
    const cashInput = document.getElementById('pmCashInput');
    cashInput.value = amt.toFixed(2);
    cashInput.dispatchEvent(new Event('input'));
}

document.getElementById('pmCashInput')?.addEventListener('input', function () {
    const val  = parseFloat(this.value) || 0;
    const valid = val >= _pmTotal && _pmTotal > 0;
    this.className = 'pm-cash-input' + (this.value === '' ? '' : (valid ? ' valid' : ' invalid'));
    updateChangeDisplay(val, this.value !== '');
    document.getElementById('pmConfirmBtn').disabled = !valid;
});

function updateChangeDisplay(amountPaid, hasInput) {
    const card     = document.getElementById('pmChangeCard');
    const valueEl  = document.getElementById('pmChangeValue');
    const iconEl   = card.querySelector('.pm-change-icon i');

    if (!hasInput || _pmTotal <= 0) {
        card.className  = 'pm-change-card change-none';
        valueEl.textContent = '—';
        iconEl.className    = 'fas fa-coins';
        return;
    }

    const change = amountPaid - _pmTotal;
    if (change < 0) {
        card.className  = 'pm-change-card change-err';
        valueEl.textContent = `₱${Math.abs(change).toFixed(2)} short`;
        iconEl.className    = 'fas fa-exclamation-triangle';
    } else if (change === 0) {
        card.className  = 'pm-change-card change-exact';
        valueEl.textContent = 'No change — exact amount';
        iconEl.className    = 'fas fa-check-circle';
    } else {
        card.className  = 'pm-change-card change-ok';
        valueEl.textContent = `₱${change.toFixed(2)}`;
        iconEl.className    = 'fas fa-hand-holding-dollar';
    }
}

// ─────────────────────────────────────────────────────────────────
// CONFIRM PAYMENT & SUBMIT
// ─────────────────────────────────────────────────────────────────
async function confirmPaymentAndSubmit() {
    const amountPaid  = parseFloat(document.getElementById('pmCashInput').value) || 0;
    const changeAmt   = Math.max(0, amountPaid - _pmTotal);

    if (amountPaid < _pmTotal) { showErrorModal('Insufficient Amount', 'Cash tendered must be ≥ the order total.'); return; }

    const { orderType, customerName, tableId } = collectOrderData();
    const btn = document.getElementById('pmConfirmBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing…';

    const payload = new URLSearchParams({
        action:        'pay_and_submit',
        customer_name: customerName || '',
        table_id:      orderType === 'dine_in' ? tableId : '',
        order_type:    orderType,
        cart_data:     JSON.stringify(cart),
        amount_paid:   amountPaid.toFixed(2),
        change_amount: changeAmt.toFixed(2),
    });

    try {
        const resp = await fetch('index.php?page=manage_pos', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body:    payload,
        });
        const data = await resp.json();

        if (!data.success) throw new Error(data.error || 'Unknown error from server.');

        lastOrderData = {
            orderNumber:  data.order_number,
            orderType,
            customerName,
            tableName:    data.table_name || '',
            items:        [...cart],
            total:        _pmTotal,
            amountPaid,
            changeAmt,
            cashierName:  CASHIER_NAME,
        };

        cart = [];
        localStorage.removeItem('pos_cart');
        sessionStorage.removeItem('draft_banner_shown');
        validateAndRender();

        hideModal('paymentModal');

        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Confirm & Place Order';

        setTimeout(() => silentPrintReceipt(lastOrderData), 300);

    } catch (err) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Confirm & Place Order';
        showErrorModal('Order Failed', err.message);
    }
}

// ─────────────────────────────────────────────────────────────────
// RECEIPT — Fixed 58mm, monospace column layout
// Cols: 32 chars total (label left, value right-aligned)
// No flexbox. Uses <pre> for guaranteed no-wrap, no-overflow.
// ─────────────────────────────────────────────────────────────────

const COL = 30; // total chars per line for 58mm @ 7pt Courier

function rpad(s, n) {
    s = String(s);
    return s.length >= n ? s.substring(0, n) : s + ' '.repeat(n - s.length);
}
function lpad(s, n) {
    s = String(s);
    return s.length >= n ? s.substring(0, n) : ' '.repeat(n - s.length) + s;
}
function row2col(label, value, total) {
    total = total || COL;
    label = String(label);
    value = String(value);
    const maxLabel = total - value.length - 1;
    if (label.length > maxLabel) label = label.substring(0, maxLabel - 1) + '.';
    // Add 4 spaces padding to push labels more to the right
    return '    ' + rpad(label, maxLabel - 4) + ' ' + value;
}
function center(s, total) {
    total = total || COL;
    s = String(s);
    if (s.length >= total) return s.substring(0, total);
    const pad = Math.floor((total - s.length) / 2);
    return ' '.repeat(pad) + s;
}
function divider(ch, total) {
    return (ch || '-').repeat(total || COL);
}

function buildReceiptText(d) {
    const now     = new Date();
    const dateStr = now.toLocaleDateString('en-PH', { month:'short', day:'numeric', year:'numeric' });
    const timeStr = now.toLocaleTimeString('en-PH', { hour:'2-digit', minute:'2-digit', hour12:true });

    const lines = [];

    // Compact header
    lines.push(center('DINE WITH DIN'));
    lines.push(center('CAFETERIA'));
    lines.push(center('Karuhatan, Valenzuela'));
    lines.push(center('(123) 456-7890'));
    lines.push(divider('-'));
    lines.push(center('SALES RECEIPT'));
    lines.push(center('[ PAID ]'));
    lines.push(divider('-'));

    // Compact order info - right side aligned
    lines.push(row2col('No:', d.orderNumber));
    lines.push(row2col('Date & Time:', `${dateStr} ${timeStr}`));
    lines.push(row2col('Cashier:', d.cashierName.substring(0, 12)));
    if (d.customerName) lines.push(row2col('Customer:', d.customerName.substring(0, 12)));
    lines.push(row2col('Type:', d.orderType === 'dine_in' ? 'Dine In' : 'Take Out'));
    if (d.tableName) lines.push(row2col('Table:', d.tableName.substring(0, 12)));

    lines.push(divider('-'));

    // Items with addons/notes below, individual item prices with quantity
    d.items.forEach(row => {
        const addonsTotal = row.addons.reduce((s, a) => s + a.price * a.quantity, 0);
        const lineTotal   = (row.price + addonsTotal) * row.quantity;
        const label       = row.variantName ? `${row.name} (${row.variantName})` : row.name;
        
        // Main item line: Name indented, Qty x Price right beside name with one space
        const qtyPrice = `x${row.quantity} ${row.price.toFixed(2)}`;
        const maxName = COL - qtyPrice.length - 1;
        const truncName = label.length > maxName ? label.substring(0, maxName - 1) + '.' : label;
        lines.push('    ' + rpad(truncName, maxName) + qtyPrice);

        // Addons below item
        row.addons.forEach(a => {
            const ap = (a.price * a.quantity).toFixed(2);
            const al = `      +${a.name}${a.quantity > 1 ? ` x${a.quantity}` : ''}`;
            lines.push(rpad(al, COL - ap.length - 1) + ap);
        });

        // Notes below item with proper word wrapping
        if (row.note) {
            const notePrefix = '      Note: ';
            const noteText = String(row.note);
            const maxLineLength = COL - notePrefix.length;
            
            // Split note into words and wrap properly
            const words = noteText.split(' ');
            let currentLine = '';
            
            words.forEach(word => {
                // If adding this word would exceed line length
                if (currentLine.length + word.length + 1 > maxLineLength) {
                    // Push current line if not empty
                    if (currentLine) {
                        lines.push(notePrefix + currentLine);
                        currentLine = '';
                    }
                    // Handle words longer than line length
                    if (word.length > maxLineLength) {
                        for (let i = 0; i < word.length; i += maxLineLength) {
                            lines.push(notePrefix + word.substring(i, i + maxLineLength));
                        }
                    } else {
                        currentLine = word;
                    }
                } else {
                    currentLine += (currentLine ? ' ' : '') + word;
                }
            });
            
            // Push remaining line
            if (currentLine) {
                lines.push(notePrefix + currentLine);
            }
        }
    });

    lines.push(divider('='));
    
    // Compact totals with bigger TOTAL font
    lines.push(row2col('TOTAL:', 'PHP ' + d.total.toFixed(2)));
    lines.push(row2col('Method:', 'CASH'));
    lines.push(row2col('Tendered:', 'PHP ' + d.amountPaid.toFixed(2)));
    lines.push(row2col('Change:', 'PHP ' + d.changeAmt.toFixed(2)));
    lines.push(divider('-'));
    lines.push(center('Thank you!'));
    lines.push(center('Please come again'));
    lines.push(divider('.'));

    return lines.join('\n');
}

function buildReceiptHTML(d) {
    const text = buildReceiptText(d);
    // Escape for HTML
    const escaped = text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');

    return `<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  /* ── Page: zero margins, 58mm width, auto height ── */
  @page {
    size: 58mm auto;
    margin: 0;
  }
  * { margin: 0; padding: 0; box-sizing: border-box; }
  html, body {
    width: 58mm;
    background: #fff;
    color: #000;
  }
  /* ── Receipt block ── */
  pre {
    font-family: "Lucida Console", Monaco, monospace;
    font-size: 8pt;
    line-height: 1.2;
    font-weight: normal;
    color: #000;
    background: #fff;
    white-space: pre;          /* never wrap */
    overflow: hidden;          /* clip if somehow over */
    width: 58mm;
    padding: 2mm 1mm 4mm 1mm; /* top/side/bottom/left */
    display: block;
    word-break: normal;
    word-wrap: normal;
    letter-spacing: 0;
  }
  @media print {
    @page { size: 58mm auto; margin: 0; }
    html, body { width: 58mm; }
    pre {
      font-size: 7.5pt;
      line-height: 1.45;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }
  }
</style>
</head>
<body><pre>${escaped}</pre></body>
</html>`;
}

function silentPrintReceipt(d) {
    const old = document.getElementById('receiptPrintFrame');
    if (old) old.remove();

    const iframe = document.createElement('iframe');
    iframe.id = 'receiptPrintFrame';
    iframe.style.cssText = 'position:fixed;top:-9999px;left:-9999px;width:58mm;height:1px;border:none;visibility:hidden;';
    document.body.appendChild(iframe);

    const doc = iframe.contentDocument || iframe.contentWindow.document;
    doc.open();
    doc.write(buildReceiptHTML(d));
    doc.close();

    setTimeout(() => {
        try {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        } catch(e) { /* print dialog closed or blocked */ }
        iframe.contentWindow.onafterprint = () => iframe.remove();
        setTimeout(() => { if (iframe.parentNode) iframe.remove(); }, 30000);
    }, 400);
}

// ─────────────────────────────────────────────────────────────────
// SIDEBAR
// ─────────────────────────────────────────────────────────────────
function setSidebarOpen(open) {
    const s = document.getElementById('sidebar');
    const o = document.getElementById('sidebarOverlay');
    const b = document.getElementById('mobileBottomBar');
    if (open) { s.classList.add('show'); o.classList.add('show'); if (b) b.style.visibility = 'hidden'; closeCartDrawer(); }
    else       { s.classList.remove('show'); o.classList.remove('show'); if (b) b.style.visibility = ''; }
}
document.getElementById('menuToggle')?.addEventListener('click', () => setSidebarOpen(!document.getElementById('sidebar').classList.contains('show')));
document.getElementById('sidebarOverlay')?.addEventListener('click', () => setSidebarOpen(false));

document.getElementById('customer_name')?.addEventListener('input',   saveDraftToServer);
document.getElementById('customer_name_m')?.addEventListener('input', saveDraftToServer);

// ─────────────────────────────────────────────────────────────────
// INIT
// ─────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {

    if (DB_DRAFT_CART?.length > 0) {
        cart = DB_DRAFT_CART;
        localStorage.setItem('pos_cart', JSON.stringify(cart));
        const bannerKey = 'draft_banner_shown';
        if (!sessionStorage.getItem(bannerKey)) {
            document.getElementById('draftBanner')?.classList.remove('d-none');
            document.getElementById('draftBannerM')?.classList.remove('d-none');
            sessionStorage.setItem(bannerKey, '1');
        }
        const ot = DB_DRAFT_META?.order_type || 'dine_in';
        setOrderType(ot, 'desktop');
        setOrderType(ot, 'mobile');
        if (DB_DRAFT_META?.customer_name) {
            ['customer_name', 'customer_name_m'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.value = DB_DRAFT_META.customer_name;
            });
        }
        if (DB_DRAFT_META?.table_id) {
            const tid = String(DB_DRAFT_META.table_id);
            ['table_id', 'table_id_m'].forEach(id => {
                const s = document.getElementById(id);
                if (s?.querySelector(`option[value="${tid}"]`)) s.value = tid;
            });
        }
    } else {
        loadCart();
        setOrderType('dine_in', 'desktop');
        setOrderType('dine_in', 'mobile');
    }

    document.querySelectorAll('.menu-item-card').forEach(card => {
        const id       = card.dataset.itemId;
        const itemData = stockData[id];
        const variants = variantsMap[id] || [];
        const noPrice  = card.dataset.noPrice === 'true';
        let hasStock;
        if (variants.length > 0) {
            hasStock = variants.some(v => itemData?.variants?.[v.variant_id]?.has_stock ?? false);
        } else {
            hasStock = !noPrice && (itemData?.base?.has_stock ?? false);
        }
        if (!hasStock) {
            card.classList.add('out-of-stock');
            card.dataset.hasStock = 'false';
            if (!card.querySelector('.out-of-stock-badge')) {
                const b = document.createElement('div');
                b.className   = 'out-of-stock-badge';
                b.textContent = noPrice ? 'Unavailable' : 'Out of Stock';
                if (noPrice) b.style.background = 'var(--warm-gray)';
                card.prepend(b);
            }
        }
    });

    validateAndRender();

    if (isMobile()) {
        const bar = document.getElementById('mobileBottomBar'); if (bar) bar.style.display = 'flex';
        const drw = document.getElementById('mobileCartDrawer'); if (drw) drw.style.display = 'flex';
    }

    const drawer = document.getElementById('mobileCartDrawer');
    if (drawer) {
        let sy = 0;
        drawer.addEventListener('touchstart', e => { sy = e.touches[0].clientY; }, { passive: true });
        drawer.addEventListener('touchend',   e => { if (e.changedTouches[0].clientY - sy > 80) closeCartDrawer(); }, { passive: true });
    }
});

window.addEventListener('resize', () => {
    const bar = document.getElementById('mobileBottomBar');
    const drw = document.getElementById('mobileCartDrawer');
    if (isMobile()) {
        if (bar) bar.style.display = 'flex';
        if (drw) drw.style.display = 'flex';
    } else {
        if (bar) bar.style.display = 'none';
        if (drw) { drw.style.display = 'none'; closeCartDrawer(); }
    }
});

setTimeout(() => {
    document.querySelectorAll('.alert').forEach(a => { try { new bootstrap.Alert(a).close(); } catch(e) {} });
}, 5000);
</script>
</body>
</html>