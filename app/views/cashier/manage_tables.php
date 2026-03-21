<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5">
    <title>Manage Tables - Cafeteria POS</title>
    <link rel="icon" type="image/png" href="<?= $baseUrl ?>/assets/images/logo/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0f4c5c; --primary-dark: #0a3a47; --primary-light: #1f7a8c;
            --secondary: #ff6b4a; --navy: #1e2f4e; --lavender: #e2e6f0;
            --light-bg: #f8faff; --warm-gray: #4a5568; --border: #cbd5e0;
            --success: #2c7a5a; --danger: #d64045; --warning: #f4b942; --info: #5e9b9e;
            --dark: #1a202c; --sidebar-width: 280px; --topbar-height: 72px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--light-bg); color: var(--dark); font-size: 14px; line-height: 1.5; }

        /* ── TOPBAR ── */
        .topbar {
            position: fixed; top: 0; left: 0; right: 0; height: var(--topbar-height);
            background: white; border-bottom: 1px solid var(--border); z-index: 1040;
            padding: 0 2rem; box-shadow: 0 4px 12px rgba(15,76,92,0.04);
            display: flex; align-items: center; justify-content: space-between;
        }
        .logo { font-size: 1.6rem; font-weight: 700; color: var(--primary-dark); text-decoration: none; display: flex; align-items: center; gap: 0.65rem; }
        .logo i { font-size: 1.9rem; color: var(--secondary); }
        .logo span { background: linear-gradient(135deg, var(--primary), var(--navy)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .mobile-menu-btn { background: white; border: 1px solid var(--border); font-size: 1.4rem; color: var(--primary); cursor: pointer; padding: 0.5rem 1rem; border-radius: 12px; transition: all 0.2s; }
        .mobile-menu-btn:hover { background: var(--secondary); border-color: var(--secondary); color: white; }

        /* ── SIDEBAR ── */
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
        .sidebar-overlay { position: fixed; top: var(--topbar-height); left: 0; right: 0; bottom: 0; background: rgba(15,76,92,0.2); backdrop-filter: blur(3px); z-index: 1025; display: none; }
        .sidebar-overlay.show { display: block; }

        /* ── MAIN ── */
        .main-content { margin-left: var(--sidebar-width); margin-top: var(--topbar-height); padding: 2.2rem; min-height: calc(100vh - var(--topbar-height)); }

        .page-header { margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; }
        .page-header h1 { font-size: 2rem; font-weight: 700; color: var(--navy); display: flex; align-items: center; gap: 0.7rem; margin-bottom: 0; }
        .page-header h1 i { color: var(--secondary); }
        .breadcrumb { background: none; padding: 0.2rem 0; margin: 0; font-size: 0.85rem; }
        .breadcrumb-item a { color: var(--warm-gray); text-decoration: none; font-weight: 500; }
        .breadcrumb-item.active { color: var(--primary-dark); font-weight: 600; }

        /* ── STAT CARDS ── */
        .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.2rem; margin-bottom: 1.8rem; }
        .stat-card { background: white; border-radius: 18px; padding: 1.3rem; border: 1px solid rgba(203,213,224,0.5); box-shadow: 0 4px 12px rgba(15,76,92,0.03); display: flex; align-items: center; gap: 1rem; }
        .stat-icon { width: 48px; height: 48px; border-radius: 13px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
        .stat-icon.total    { background: rgba(15,76,92,0.1);  color: var(--primary); }
        .stat-icon.avail    { background: rgba(44,122,90,0.1);  color: var(--success); }
        .stat-icon.occupied { background: rgba(255,107,74,0.1); color: var(--secondary); }
        .stat-label { font-size: 0.72rem; color: var(--warm-gray); font-weight: 600; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 2px; }
        .stat-value { font-size: 1.9rem; font-weight: 800; color: var(--navy); line-height: 1; }

        /* ── CARD ── */
        .card { background: white; border-radius: 22px; border: 1px solid rgba(203,213,224,0.5); box-shadow: 0 8px 20px rgba(15,76,92,0.02); margin-bottom: 1.8rem; }
        .card-header { background: white; border-bottom: 1px solid rgba(203,213,224,0.5); padding: 1.2rem 1.6rem; border-radius: 22px 22px 0 0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; }
        .card-body { padding: 1.5rem 1.6rem; }
        .card-title { font-size: 1.05rem; font-weight: 700; color: var(--navy); display: flex; align-items: center; gap: 0.5rem; margin: 0; }

        /* ── TABLES GRID ── */
        .tables-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 1.1rem; }

        .table-card {
            background: white; border: 2px solid var(--border); border-radius: 16px;
            padding: 1.2rem; transition: all 0.2s; position: relative;
            box-shadow: 0 2px 8px rgba(15,76,92,0.03);
            display: flex; flex-direction: column;
        }
        .table-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; border-radius: 16px 16px 0 0; }
        .table-card.available { border-color: rgba(44,122,90,0.28); }
        .table-card.available::before { background: var(--success); }
        .table-card.occupied  { border-color: rgba(255,107,74,0.38); background: rgba(255,107,74,0.012); }
        .table-card.occupied::before  { background: var(--secondary); }
        .table-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(15,76,92,0.08); }

        .table-card-icon { width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; margin-bottom: 0.65rem; flex-shrink: 0; }
        .table-card-icon.available { background: rgba(44,122,90,0.1); color: var(--success); }
        .table-card-icon.occupied  { background: rgba(255,107,74,0.1); color: var(--secondary); }
        .table-card-name     { font-size: 0.98rem; font-weight: 700; color: var(--navy); margin-bottom: 0.12rem; }
        .table-card-capacity { font-size: 0.74rem; color: var(--warm-gray); margin-bottom: 0.5rem; }
        .table-card-status   { display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.22rem 0.65rem; border-radius: 20px; font-size: 0.7rem; font-weight: 700; }
        .table-card-status.available { background: rgba(44,122,90,0.1);  color: var(--success); }
        .table-card-status.occupied  { background: rgba(255,107,74,0.1); color: var(--secondary); }

        .table-card-order {
            margin-top: 0.6rem; padding: 0.55rem 0.7rem;
            background: var(--light-bg); border-radius: 9px;
            font-size: 0.74rem; color: var(--warm-gray); flex: 1;
        }
        .order-row { display: flex; align-items: center; gap: 0.32rem; margin-bottom: 0.18rem; }
        .order-row:last-child { margin-bottom: 0; }
        .order-num  { font-weight: 700; color: var(--navy); }
        .order-amt  { font-weight: 700; color: var(--success); }
        .order-time { color: var(--primary-light); font-size: 0.69rem; }

        .table-awaiting-btn {
            background: rgba(244,185,66,0.1); color: #92600a;
            border: 1.5px solid rgba(244,185,66,0.35); cursor: default;
            font-size: 0.72rem; white-space: nowrap;
        }
        .table-available-btn {
            background: rgba(44,122,90,0.08); color: var(--success);
            border: 1.5px solid rgba(44,122,90,0.2); cursor: default;
        }
        .table-card-actions { display: flex; gap: 0.4rem; margin-top: 0.8rem; flex-wrap: wrap; }
        .table-card-actions .btn { flex: 1; min-width: 0; justify-content: center; white-space: nowrap; }

        /* ── BUTTONS ── */
        .btn { padding: 0.5rem 1.1rem; border-radius: 30px; font-weight: 600; font-size: 0.82rem; transition: all 0.18s; display: inline-flex; align-items: center; gap: 0.3rem; cursor: pointer; border: none; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-dark); transform: translateY(-1px); }
        .btn-danger  { background: var(--danger); color: white; }
        .btn-danger:hover  { background: #b03035; }
        .btn-warning { background: var(--warning); color: var(--dark); font-weight: 700; }
        .btn-warning:hover { background: #d4a030; }
        .btn-outline-secondary { border: 1.5px solid var(--border); color: var(--warm-gray); background: white; }
        .btn-outline-secondary:hover { background: var(--lavender); }
        .btn-sm { padding: 0.35rem 0.8rem; font-size: 0.76rem; border-radius: 8px; }

        /* ── FORMS ── */
        .form-label { font-weight: 600; color: var(--navy); margin-bottom: 0.4rem; font-size: 0.82rem; display: block; }
        .form-control { border: 1.5px solid var(--border); border-radius: 12px; padding: 0.6rem 0.9rem; font-size: 0.88rem; transition: all 0.2s; width: 100%; }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(15,76,92,0.1); outline: none; }
        .form-text { font-size: 0.75rem; color: var(--warm-gray); margin-top: 0.3rem; }

        /* ── FEEDBACK MODALS ── */
        .feedback-icon {
            width: 60px; height: 60px; border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem; margin: 0 auto;
        }
        .feedback-icon-success { background: rgba(44,122,90,0.1);  color: var(--success); }
        .feedback-icon-danger  { background: rgba(214,64,69,0.1);  color: var(--danger);  }
        .feedback-icon-warning { background: rgba(244,185,66,0.1); color: #92600a; }
        .feedback-title { font-size: 1rem; font-weight: 700; color: var(--navy); }
        .feedback-msg   { font-size: 0.85rem; color: var(--warm-gray); margin: 0; line-height: 1.5; }

        /* ── BASE MODALS ── */
        .modal { padding-right: 0 !important; }
        .modal-content { border-radius: 22px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.12); overflow: hidden; }
        .modal-header { border-bottom: 1px solid var(--border); padding: 1.25rem 1.6rem; }
        .modal-body   { padding: 1.5rem 1.6rem; }
        .modal-footer { border-top: 1px solid var(--border); padding: 1.1rem 1.6rem; }
        .modal-title  { font-size: 1.05rem; font-weight: 700; color: var(--navy); }

        /* ── ORDER DETAILS MODAL ── */
        .od-modal-content {
            border-radius: 22px; border: none; box-shadow: 0 20px 50px rgba(0,0,0,0.13);
            overflow: hidden; display: flex; flex-direction: column; max-height: 90vh;
        }
        .od-header {
            background: var(--primary); padding: 1.15rem 1.5rem;
            display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-shrink: 0;
        }
        .od-header-left { display: flex; align-items: center; gap: 0.75rem; }
        .od-header-icon { width: 34px; height: 34px; border-radius: 9px; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; font-size: 0.9rem; color: white; flex-shrink: 0; }
        .od-header-title { font-size: 0.97rem; font-weight: 700; color: white; line-height: 1.2; }
        .od-header-sub   { font-size: 0.76rem; color: rgba(255,255,255,0.7); margin-top: 1px; }

        #tableOrderDetailsModal .modal-body { overflow-y: auto; flex: 1 1 auto; padding: 0; }
        #tableOrderDetailsModal .modal-footer { flex-shrink: 0; }

        .od-meta-grid { display: grid; grid-template-columns: repeat(3,1fr); background: var(--light-bg); border-bottom: 1px solid var(--border); }
        .od-meta-item { display: flex; flex-direction: column; gap: 4px; padding: 0.8rem 1.05rem; border-right: 1px solid var(--border); border-bottom: 1px solid var(--border); }
        .od-meta-item:nth-child(3n) { border-right: none; }
        .od-meta-label { font-size: 0.63rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: var(--warm-gray); opacity: 0.75; }
        .od-meta-value { font-size: 0.84rem; font-weight: 600; color: var(--navy); line-height: 1.3; }

        .od-items-section { padding: 1.1rem 1.35rem 0; }
        .od-section-label { font-size: 0.67rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: var(--warm-gray); margin-bottom: 0.65rem; display: flex; align-items: center; gap: 0.3rem; }
        .od-section-label i { color: var(--primary); }
        .od-items-table { width: 100%; border-collapse: collapse; font-size: 0.84rem; }
        .od-items-table thead th { background: #f2f6fc; padding: 0.55rem 0.8rem; font-size: 0.67rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.7px; color: var(--primary-dark); border-bottom: 2px solid var(--primary-light); }
        .od-items-table thead th.text-center { text-align: center; }
        .od-items-table thead th.text-end    { text-align: right; }
        .od-td { padding: 0.65rem 0.8rem; vertical-align: top; border-bottom: 1px solid #edf2f7; }
        .od-td-center  { text-align: center; color: var(--warm-gray); }
        .od-td-right   { text-align: right; }
        .od-muted      { color: var(--warm-gray); }
        .od-item-name  { font-weight: 600; color: var(--navy); font-size: 0.85rem; }
        .od-item-subtotal { font-weight: 700; color: var(--navy); }
        .od-addon-row .od-td { border-bottom: 1px dashed #edf2f7; font-size: 0.78rem; color: var(--warm-gray); background: #fafcff; }
        .od-td-addon   { padding-left: 1.7rem !important; }
        .od-addon-icon { margin-right: 0.28rem; font-size: 0.6rem; opacity: 0.55; }
        .od-note-pill  { display: inline-flex; align-items: center; gap: 0.25rem; margin-top: 0.25rem; padding: 0.16rem 0.48rem; background: rgba(254,228,64,0.18); border: 1px dashed rgba(244,185,66,0.5); border-radius: 20px; font-size: 0.68rem; color: #7a5c00; font-style: italic; }
        .od-note-pill i { color: #b87a1a; font-size: 0.6rem; }

        .od-footer-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.9rem; padding: 1rem 1.35rem 1.3rem; background: var(--light-bg); border-top: 1px solid var(--border); margin-top: 0.4rem; }
        .od-payment-card { background: white; border: 1px solid rgba(44,122,90,0.2); border-radius: 11px; padding: 0.8rem 0.95rem; }
        .od-payment-title { font-size: 0.63rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.7px; color: var(--success); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.3rem; }
        .od-payment-row { display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; padding: 0.2rem 0; color: var(--warm-gray); border-bottom: 1px solid #f0f4f8; }
        .od-payment-row:last-child { border-bottom: none; }
        .od-payment-row span:last-child { font-weight: 600; color: var(--navy); }
        .od-totals-card { background: white; border: 1px solid var(--border); border-radius: 11px; padding: 0.8rem 0.95rem; }
        .od-totals-row { display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; padding: 0.2rem 0; color: var(--warm-gray); border-bottom: 1px solid #f0f4f8; }
        .od-totals-row:last-child { border-bottom: none; }
        .od-totals-row span:last-child { font-weight: 600; color: var(--navy); }
        .od-totals-total { padding-top: 0.5rem; margin-top: 0.25rem; border-top: 2px solid var(--border) !important; border-bottom: none !important; font-size: 0.9rem; font-weight: 700; }
        .od-totals-total span:last-child { font-size: 1.08rem; font-weight: 800; }

        /* ── ALERTS ── */
        .alert { border-radius: 13px; border: none; padding: 0.85rem 1.2rem; margin-bottom: 1.4rem; }
        .alert-success { background: rgba(44,122,90,0.1);  color: #1e4a3b; border-left: 5px solid var(--success); }
        .alert-danger  { background: rgba(214,64,69,0.07); color: #9e3a34; border-left: 5px solid var(--danger); }
        .alert-info    { background: rgba(94,155,158,0.1); color: #2a5f62; border-left: 5px solid var(--info); }
        .dropdown-menu { border-radius: 14px; border: 1px solid var(--border); box-shadow: 0 8px 20px rgba(0,0,0,0.06); padding: 0.4rem; }
        .dropdown-item { border-radius: 8px; padding: 0.5rem 1rem; font-size: 0.88rem; }
        .dropdown-item:hover { background: var(--lavender); }

        /* ── RESPONSIVE ── */
        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; padding: 1.5rem; }
            .tables-grid { grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); }
        }
        @media (max-width: 767.98px) {
            .main-content { padding: 1.1rem; }
            .page-header { flex-direction: column; align-items: flex-start; gap: 0.75rem; }
            .page-header h1 { font-size: 1.65rem; }
            .tables-grid { grid-template-columns: repeat(2, 1fr); gap: 0.8rem; }
            .card-body { padding: 1.1rem 1.2rem; }
            .od-meta-grid { grid-template-columns: repeat(2, 1fr); }
            .od-meta-item:nth-child(3n) { border-right: 1px solid var(--border); }
            .od-meta-item:nth-child(2n) { border-right: none; }
            .od-footer-grid { grid-template-columns: 1fr; gap: 0.7rem; padding: 0.85rem 1rem 1rem; }
        }
        @media (max-width: 575.98px) {
            .topbar { padding: 0 1rem; }
            .main-content { padding: 0.9rem; }
            .stats-row { grid-template-columns: repeat(3, 1fr); gap: 0.55rem; margin-bottom: 1.2rem; }
            .stat-card { padding: 0.8rem 0.65rem; gap: 0.6rem; border-radius: 14px; flex-direction: column; align-items: flex-start; }
            .stat-icon { width: 34px; height: 34px; border-radius: 9px; font-size: 0.9rem; }
            .stat-value { font-size: 1.45rem; }
            .stat-label { font-size: 0.6rem; letter-spacing: 0.3px; }
            .tables-grid { grid-template-columns: repeat(2, 1fr); gap: 0.6rem; }
            .table-card { padding: 0.85rem; border-radius: 13px; }
            .table-card-icon { width: 34px; height: 34px; border-radius: 9px; font-size: 0.95rem; margin-bottom: 0.5rem; }
            .table-card-name { font-size: 0.86rem; }
            .table-card-capacity { font-size: 0.7rem; }
            .table-card-status { font-size: 0.67rem; padding: 0.18rem 0.55rem; }
            .table-card-order { font-size: 0.72rem; padding: 0.5rem 0.6rem; }
            .table-card-actions { gap: 0.35rem; margin-top: 0.65rem; }
            .btn-sm { padding: 0.3rem 0.65rem; font-size: 0.72rem; }
            .card { border-radius: 16px; }
            .card-header { padding: 0.9rem 1.1rem; border-radius: 16px 16px 0 0; }
            .card-body { padding: 0.9rem 1rem; }
            .page-header { margin-bottom: 1.2rem; }
            .page-header h1 { font-size: 1.4rem; }
            .modal-dialog { margin: 0; max-width: 100%; width: 100%; position: fixed; bottom: 0; left: 0; right: 0; display: flex !important; align-items: flex-end; }
            .modal-dialog-centered { min-height: unset; align-items: flex-end; }
            .modal-content, .od-modal-content { border-radius: 20px 20px 0 0 !important; max-height: 92vh; overflow-y: auto; display: flex; flex-direction: column; }
            .od-header { padding: 1rem 1.1rem; border-radius: 20px 20px 0 0; }
            #tableOrderDetailsModal .modal-body { max-height: none; }
            .od-items-table { font-size: 0.78rem; }
            .od-td { padding: 0.5rem 0.65rem; }
            .od-meta-grid { grid-template-columns: repeat(2, 1fr); }
            .od-meta-item { padding: 0.65rem 0.85rem; }
        }
        @media (max-width: 375px) {
            .stats-row { gap: 0.4rem; }
            .stat-card { padding: 0.65rem 0.5rem; }
            .stat-value { font-size: 1.25rem; }
            .stat-label { font-size: 0.55rem; }
            .tables-grid { gap: 0.5rem; }
            .table-card { padding: 0.75rem; }
            .table-card-name { font-size: 0.8rem; }
        }

        .fade-in { animation: fadeUp 0.35s ease forwards; }
        @keyframes fadeUp { from { opacity: 0.5; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
    <div class="d-flex align-items-center gap-3">
        <button class="mobile-menu-btn d-lg-none" id="menuToggle"><i class="fas fa-bars"></i></button>
        <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="logo d-none d-sm-flex">
            <i class="fas fa-utensils"></i><span>Dine with Din - Cafeteria</span>
        </a>
        <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="logo d-sm-none">
            <i class="fas fa-utensils"></i>
        </a>
    </div>
    <div class="dropdown">
        <button class="btn btn-link text-decoration-none dropdown-toggle d-flex align-items-center"
                type="button" data-bs-toggle="dropdown" style="color:var(--navy);font-weight:600;padding:0;">
            <i class="far fa-user-circle fa-xl me-2" style="font-size:1.6rem;color:var(--secondary);"></i>
            <span class="d-none d-md-inline"><?= htmlspecialchars($_SESSION['full_name'] ?? '') ?></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="<?= $baseUrl ?>/index.php?page=logout">
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
        <div class="nav-section-title">DASHBOARD</div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></div>
        <div class="nav-section-title">MANAGEMENT</div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_menu" class="nav-link"><i class="fas fa-utensils"></i> Menu Items</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_recipes" class="nav-link"><i class="fas fa-tags"></i> Recipes</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_ingredients" class="nav-link"><i class="fas fa-boxes"></i> Ingredients</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_addons" class="nav-link"><i class="fas fa-plus-circle"></i> Add-ons</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_tables" class="nav-link active"><i class="fas fa-chair"></i> Tables</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_users" class="nav-link"><i class="fas fa-users"></i> User Management</a></div>
        <div class="nav-section-title">SALES</div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_pos" class="nav-link"><i class="fas fa-cash-register"></i> POS System</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_orders" class="nav-link"><i class="fas fa-receipt"></i> Orders</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=reports" class="nav-link"><i class="fas fa-chart-bar"></i> Reports</a></div>
        <?php endif; ?>
        <?php if (hasRole('cashier') && !hasRole('admin')): ?>
        <div class="nav-section-title">MANAGEMENT</div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_tables" class="nav-link active"><i class="fas fa-chair"></i> Tables</a></div>
        <div class="nav-section-title">SALES</div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_pos" class="nav-link"><i class="fas fa-cash-register"></i> POS System</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=manage_orders" class="nav-link"><i class="fas fa-receipt"></i> Orders</a></div>
        <div class="nav-item"><a href="<?= $baseUrl ?>/index.php?page=reports" class="nav-link"><i class="fas fa-chart-bar"></i> Reports</a></div>
        <?php endif; ?>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">

    <div class="page-header">
        <div>
            <h1><i class="fas fa-chair"></i> Table Management</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/index.php?page=admin_dashboard">Home</a></li>
                    <li class="breadcrumb-item active">Tables</li>
                </ol>
            </nav>
        </div>
        <?php if (hasRole('admin')): ?>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTableModal">
                <i class="fas fa-plus me-1"></i> Add Table
            </button>
        <?php endif; ?>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
    <div id="toastData"
         data-type="success"
         data-msg="<?= htmlspecialchars($_SESSION['success'], ENT_QUOTES) ?>"></div>
    <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
    <div id="toastData"
         data-type="error"
         data-msg="<?= htmlspecialchars($_SESSION['error'], ENT_QUOTES) ?>"></div>
    <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php /* cashier role note shown in stat bar instead */ ?>

    <!-- STAT CARDS -->
    <div class="stats-row fade-in">
        <div class="stat-card">
            <div class="stat-icon total"><i class="fas fa-chair"></i></div>
            <div>
                <div class="stat-label">Total</div>
                <div class="stat-value" id="statTotal"><?= $tableStats['total'] ?? 0 ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon avail"><i class="fas fa-circle-check"></i></div>
            <div>
                <div class="stat-label">Available</div>
                <div class="stat-value" id="statAvailable"><?= $tableStats['available'] ?? 0 ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon occupied"><i class="fas fa-user-clock"></i></div>
            <div>
                <div class="stat-label">Occupied</div>
                <div class="stat-value" id="statOccupied"><?= $tableStats['occupied'] ?? 0 ?></div>
            </div>
        </div>
    </div>

    <!-- TABLES GRID -->
    <div class="card fade-in">
        <div class="card-header">
            <h5 class="card-title">
                <i class="fas fa-th" style="color:var(--primary);"></i> All Tables
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($tables)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-chair" style="font-size:3rem;color:#d0d8e4;display:block;margin-bottom:1rem;"></i>
                    <h5>No tables yet</h5>
                    <p class="small"><?= hasRole('admin') ? 'Click "Add Table" to get started.' : 'Ask an admin to add tables.' ?></p>
                </div>
            <?php else: ?>
                <div class="tables-grid">
                    <?php foreach ($tables as $table):
                        $isOccupied = $table['status'] === 'occupied';
                        // ── 12-hour clock ──
                        $sinceTime = '';
                        if ($isOccupied && !empty($table['order_started_at'])) {
                            $sinceTime = date('g:i A', strtotime($table['order_started_at']));
                        }
                    ?>
                    <div class="table-card <?= $table['status'] ?>" data-table-id="<?= $table['table_id'] ?>">

                        <div class="table-card-icon <?= $table['status'] ?>">
                            <i class="fas fa-chair"></i>
                        </div>

                        <div class="table-card-name"><?= htmlspecialchars($table['table_name']) ?></div>
                        <div class="table-card-capacity"><i class="fas fa-users me-1"></i><?= $table['capacity'] ?> seats</div>

                        <span class="table-card-status <?= $table['status'] ?>">
                            <i class="fas <?= $isOccupied ? 'fa-circle-dot' : 'fa-circle-check' ?>" style="font-size:0.6rem;"></i>
                            <?= ucfirst($table['status']) ?>
                        </span>

                        <?php if ($isOccupied && $table['order_number']): ?>
                        <div class="table-card-order">
                            <div class="order-row">
                                <i class="fas fa-receipt" style="color:var(--primary);font-size:0.65rem;"></i>
                                <span class="order-num">#<?= htmlspecialchars($table['order_number']) ?></span>
                            </div>
                            <?php if ($table['customer_name']): ?>
                            <div class="order-row">
                                <i class="fas fa-user" style="font-size:0.65rem;"></i>
                                <span><?= htmlspecialchars($table['customer_name']) ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="order-row">
                                <i class="fas fa-coins" style="color:var(--success);font-size:0.65rem;"></i>
                                <span class="order-amt">₱<?= number_format($table['total_amount'] ?? 0, 2) ?></span>
                            </div>
                            <?php if ($sinceTime): ?>
                            <div class="order-row order-time">
                                <i class="fas fa-clock" style="font-size:0.65rem;"></i>
                                <span>Since <?= $sinceTime ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <div class="table-card-actions">
                            <?php if ($isOccupied): ?>
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                        onclick="viewTableOrder(<?= (int)$table['current_order_id'] ?>, '<?= htmlspecialchars($table['order_number'] ?? '', ENT_QUOTES) ?>')">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <?php if ($table['order_status'] === 'completed'): ?>
                                    <button type="button" class="btn btn-sm btn-warning"
                                            onclick="openFreeModal(<?= $table['table_id'] ?>, '<?= htmlspecialchars($table['table_name'], ENT_QUOTES) ?>')">
                                        <i class="fas fa-door-open"></i> Free
                                    </button>
                                <?php else: ?>
                                    <span class="btn btn-sm table-awaiting-btn"
                                          title="Collect payment first before freeing the table">
                                        <i class="fas fa-lock"></i> Awaiting Payment
                                    </span>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if (hasRole('admin')): ?>
                                    <button class="btn btn-sm btn-primary"
                                            onclick="openEditModal(<?= $table['table_id'] ?>, '<?= htmlspecialchars($table['table_name'], ENT_QUOTES) ?>', <?= $table['capacity'] ?>)">
                                        <i class="fas fa-pen"></i> Edit
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger"
                                            onclick="openDeleteModal(<?= $table['table_id'] ?>, '<?= htmlspecialchars($table['table_name'], ENT_QUOTES) ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php else: ?>
                                    <span class="btn btn-sm table-available-btn w-100">
                                        <i class="fas fa-circle-check"></i> Available
                                    </span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div><!-- /.main-content -->


<!-- ══════════════════════════════════════
     ORDER DETAILS MODAL
══════════════════════════════════════ -->
<div class="modal fade" id="tableOrderDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content od-modal-content">

            <div class="od-header">
                <div class="od-header-left">
                    <div class="od-header-icon"><i class="fas fa-receipt"></i></div>
                    <div>
                        <div class="od-header-title">Order Details</div>
                        <div class="od-header-sub" id="todOrderNumber">—</div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-0">

                <!-- Loader -->
                <div id="todLoader" class="text-center py-5">
                    <div class="spinner-border" style="color:var(--primary);width:2rem;height:2rem;" role="status"></div>
                    <p class="mt-3 text-muted mb-0" style="font-size:0.86rem;">Fetching order details…</p>
                </div>

                <!-- Content -->
                <div id="todContent" style="display:none;">

                    <div class="od-meta-grid">
                        <div class="od-meta-item">
                            <div class="od-meta-label">Customer</div>
                            <div class="od-meta-value" id="todCustomer">—</div>
                        </div>
                        <div class="od-meta-item">
                            <div class="od-meta-label">Date &amp; Time</div>
                            <div class="od-meta-value" id="todDate">—</div>
                        </div>
                        <div class="od-meta-item">
                            <div class="od-meta-label">Cashier</div>
                            <div class="od-meta-value" id="todCashier">—</div>
                        </div>
                        <div class="od-meta-item">
                            <div class="od-meta-label">Order Type</div>
                            <div class="od-meta-value" id="todOrderType">—</div>
                        </div>
                        <div class="od-meta-item">
                            <div class="od-meta-label">Status</div>
                            <div class="od-meta-value" id="todStatus">—</div>
                        </div>
                        <div class="od-meta-item">
                            <div class="od-meta-label">Table</div>
                            <div class="od-meta-value" id="todTable">—</div>
                        </div>
                    </div>

                    <div class="od-items-section">
                        <div class="od-section-label"><i class="fas fa-list"></i> Order Items</div>
                        <table class="od-items-table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="todItemsBody"></tbody>
                        </table>
                    </div>

                    <div class="od-footer-grid">

                        <div id="todPaymentSection" class="od-payment-card" style="display:none;">
                            <div class="od-payment-title"><i class="fas fa-money-bill-wave"></i> Payment</div>
                            <div class="od-payment-row"><span>Method</span><span id="todPayMethod">—</span></div>
                            <div class="od-payment-row"><span>Amount Paid</span><span>₱<span id="todAmountPaid">0.00</span></span></div>
                            <div class="od-payment-row"><span>Change</span><span>₱<span id="todChange">0.00</span></span></div>
                        </div>

                        <div class="od-totals-card">
                            <div class="od-totals-row"><span>Subtotal (excl. VAT)</span><span>₱<span id="todSubtotal">0.00</span></span></div>
                            <div class="od-totals-row"><span>VAT (12%)</span><span>₱<span id="todVat">0.00</span></span></div>
                            <div class="od-totals-row od-totals-total">
                                <span>Total</span>
                                <span>₱<span id="todTotalDisplay"><span id="todTotal">0.00</span></span></span>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Error -->
                <div id="todError" style="display:none;text-align:center;padding:3rem 1.5rem;color:var(--danger);">
                    <i class="fas fa-exclamation-circle" style="font-size:2.5rem;margin-bottom:0.75rem;display:block;"></i>
                    <p class="fw-semibold mb-0">Failed to load order details. Please try again.</p>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>


<!-- FREE TABLE CONFIRM MODAL -->
<div class="modal fade" id="freeTableModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="feedback-icon feedback-icon-warning">
                    <i class="fas fa-door-open"></i>
                </div>
                <h5 class="feedback-title mt-3 mb-1">Free this table?</h5>
                <p class="feedback-msg" id="freeTableMsg">Only do this after the customer has physically left.</p>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4 gap-2">
                <button type="button" class="btn btn-outline-secondary flex-grow-1" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <form method="POST" action="<?= $baseUrl ?>/index.php?page=manage_tables" style="flex:1;">
                    <input type="hidden" name="table_id" id="freeTableId">
                    <button type="submit" name="free_table" class="btn btn-warning w-100">
                        <i class="fas fa-door-open me-1"></i> Yes
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- DELETE TABLE CONFIRM MODAL (admin only) -->
<div class="modal fade" id="deleteTableModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="feedback-icon feedback-icon-danger">
                    <i class="fas fa-trash"></i>
                </div>
                <h5 class="feedback-title mt-3 mb-1">Delete table?</h5>
                <p class="feedback-msg" id="deleteTableMsg">This cannot be undone.</p>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4 gap-2">
                <button type="button" class="btn btn-outline-secondary flex-grow-1" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <form method="POST" action="<?= $baseUrl ?>/index.php?page=manage_tables" style="flex:1;">
                    <input type="hidden" name="table_id" id="deleteTableId">
                    <button type="submit" name="delete_table" class="btn btn-danger w-100">
                        <i class="fas fa-trash me-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- FEEDBACK MODAL (success / error after form submit) -->
<div class="modal fade" id="feedbackModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="feedback-icon" id="feedbackIcon">
                    <i id="feedbackIconI" class="fas fa-check-circle"></i>
                </div>
                <h5 class="feedback-title mt-3 mb-1" id="feedbackTitle">Success</h5>
                <p class="feedback-msg" id="feedbackMsg"></p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center pb-4">
                <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal">
                    <i class="fas fa-check me-1"></i> OK
                </button>
            </div>
        </div>
    </div>
</div>
<?php if (hasRole('admin')): ?>
<div class="modal fade" id="addTableModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus me-2" style="color:var(--primary);"></i> Add New Table</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= $baseUrl ?>/index.php?page=manage_tables">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Table Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="table_name" required
                               placeholder="e.g. Table 1, Window Seat, VIP Table" maxlength="30">
                        <div class="form-text">Max 30 characters. Must be unique.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Seating Capacity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="capacity" required min="1" max="50" value="4">
                        <div class="form-text">Number of seats at this table.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_table" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Add Table
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- EDIT TABLE MODAL -->
<div class="modal fade" id="editTableModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-pen me-2" style="color:var(--primary);"></i> Edit Table</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= $baseUrl ?>/index.php?page=manage_tables">
                <input type="hidden" name="table_id" id="editTableId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Table Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="table_name" id="editTableName" required maxlength="30">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Seating Capacity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="capacity" id="editTableCapacity" required min="1" max="50">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_table" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>

// ── Sidebar ──────────────────────────────────────────────────────────
document.getElementById('menuToggle')?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('show');
    document.getElementById('sidebarOverlay').classList.toggle('show');
});
document.getElementById('sidebarOverlay')?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.remove('show');
    document.getElementById('sidebarOverlay').classList.remove('show');
});

// ── Confirm modals ────────────────────────────────────────────────────
function openFreeModal(tableId, tableName) {
    document.getElementById('freeTableId').value = tableId;
    document.getElementById('freeTableMsg').textContent =
        `"${tableName}" — only free this after the customer has physically left.`;
    new bootstrap.Modal(document.getElementById('freeTableModal')).show();
}

function openDeleteModal(tableId, tableName) {
    document.getElementById('deleteTableId').value = tableId;
    document.getElementById('deleteTableMsg').textContent =
        `"${tableName}" will be permanently deleted. This cannot be undone.`;
    new bootstrap.Modal(document.getElementById('deleteTableModal')).show();
}

// ── Feedback modal (replaces alert banners) ───────────────────────────
function showFeedback(type, title, msg) {
    const iconEl  = document.getElementById('feedbackIcon');
    const iconI   = document.getElementById('feedbackIconI');
    const titleEl = document.getElementById('feedbackTitle');
    const msgEl   = document.getElementById('feedbackMsg');
    const btn     = document.querySelector('#feedbackModal .btn-primary');

    iconEl.className  = `feedback-icon feedback-icon-${type}`;
    iconI.className   = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
    titleEl.textContent = title;
    msgEl.textContent   = msg;

    if (btn) {
        btn.className = type === 'success'
            ? 'btn btn-primary px-4'
            : 'btn btn-danger px-4';
    }

    new bootstrap.Modal(document.getElementById('feedbackModal')).show();
}

// Auto-show feedback modal if PHP session left a message
// ── Merged init: toast + polling seed ────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    // Show feedback modal for session messages
    const toast = document.getElementById('toastData');
    if (toast) {
        const type = toast.dataset.type;
        const msg  = toast.dataset.msg;
        showFeedback(
            type === 'success' ? 'success' : 'danger',
            type === 'success' ? 'Success'  : 'Error',
            msg
        );
    }

    // Seed lastTableStates from server-rendered DOM
    document.querySelectorAll('.table-card[data-table-id]').forEach(card => {
        const id = card.getAttribute('data-table-id');
        lastTableStates[id] = {
            status:       card.classList.contains('occupied') ? 'occupied' : 'available',
            order_number: card.querySelector('.order-num')?.textContent?.replace('#','') || '',
            total_amount: card.querySelector('.order-amt')?.textContent?.replace(/[₱,]/g,'') || 0,
            order_status: '',
        };
    });
    startPolling();
});

function openEditModal(id, name, capacity) {
    document.getElementById('editTableId').value       = id;
    document.getElementById('editTableName').value     = name;
    document.getElementById('editTableCapacity').value = capacity;
    new bootstrap.Modal(document.getElementById('editTableModal')).show();
}

// ── Modal backdrop cleanup ────────────────────────────────────────────
document.addEventListener('hidden.bs.modal', (e) => {
    // Clear open order tracking when the order details modal closes
    if (e.target.id === 'tableOrderDetailsModal') {
        openModalOrderId = null;
    }
    if (document.querySelector('.modal.show')) return;
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.classList.remove('modal-open');
    document.body.style.overflow     = '';
    document.body.style.paddingRight = '';
});

// ────────────────────────────────────────────────────────────────────
// VIEW ORDER → shows order details in a modal via AJAX
// Uses the same get_order_details.php endpoint as manage_orders
// ────────────────────────────────────────────────────────────────────
function viewTableOrder(orderId, orderNumber) {
    const loaderEl  = document.getElementById('todLoader');
    const contentEl = document.getElementById('todContent');
    const errorEl   = document.getElementById('todError');
    const numEl     = document.getElementById('todOrderNumber');

    // Track which order is open for real-time refresh
    openModalOrderId = orderId;

    // Reset
    if (loaderEl)  loaderEl.style.display  = 'block';
    if (contentEl) contentEl.style.display = 'none';
    if (errorEl)   errorEl.style.display   = 'none';
    if (numEl)     numEl.textContent        = '#' + orderNumber;

    // Show modal first so user sees the spinner immediately
    const modalEl = document.getElementById('tableOrderDetailsModal');
    const modal   = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modal.show();

    // Fetch — same endpoint used by manage_orders.js
    fetch(`/coffee_shop/app/ajax/get_order_details.php?order_id=${orderId}`)
        .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
        .then(res => {
            if (loaderEl) loaderEl.style.display = 'none';
            if (!res.success) { if (errorEl) errorEl.style.display = 'block'; return; }
            populateTodModal(res.data.order || {}, res.data.items || [], res.data.payment || {});
            if (contentEl) contentEl.style.display = 'block';
        })
        .catch(() => {
            if (loaderEl) loaderEl.style.display = 'none';
            if (errorEl)  errorEl.style.display  = 'block';
        });
}

function populateTodModal(order, items, payment) {
    const set     = (id, val)  => { const el = document.getElementById(id); if (el) el.textContent = val; };
    const setHTML = (id, html) => { const el = document.getElementById(id); if (el) el.innerHTML  = html; };

    // Header order number
    set('todOrderNumber', '#' + (order.order_number || ''));
    set('todCustomer',    order.customer_name || 'Walk-in');
    set('todCashier',     order.cashier_name  || 'N/A');
    set('todTable',       order.table_number  || '—');

    // Date — 12-hour format
    if (order.created_at) {
        const d = new Date(order.created_at);
        set('todDate', d.toLocaleString('en-PH', {
            year: 'numeric', month: 'short', day: 'numeric',
            hour: 'numeric', minute: '2-digit', hour12: true
        }));
    }

    // Order type badge
    const typeMap = {
        dine_in: { icon: 'fa-utensils',     label: 'Dine In'  },
        takeout: { icon: 'fa-bag-shopping',  label: 'Takeout'  },
    };
    const t = typeMap[order.order_type] || { icon: 'fa-question', label: order.order_type || '—' };
    setHTML('todOrderType', `<span style="display:inline-flex;align-items:center;gap:0.3rem;padding:0.18rem 0.6rem;border-radius:20px;background:rgba(15,76,92,0.1);color:var(--primary);font-size:0.76rem;font-weight:600;">
        <i class="fas ${t.icon}" style="font-size:0.68rem;"></i>${t.label}</span>`);

    // Status badge
    const scMap = { pending:'#f59e0b', preparing:'#3b82f6', ready:'#10b981', completed:'var(--success)', cancelled:'var(--danger)' };
    const sc    = scMap[order.status] || 'var(--warm-gray)';
    const sLabel = (order.status || '').charAt(0).toUpperCase() + (order.status || '').slice(1);
    setHTML('todStatus', `<span style="display:inline-flex;align-items:center;gap:0.3rem;padding:0.18rem 0.6rem;border-radius:20px;background:${sc}1a;color:${sc};font-size:0.76rem;font-weight:700;">${sLabel}</span>`);

    // Items
    const tbody = document.getElementById('todItemsBody');
    if (tbody) {
        tbody.innerHTML = '';
        items.forEach(item => {
            const itemSub  = (parseFloat(item.unit_price || 0) * parseInt(item.quantity || 1)).toFixed(2);
            const notePill = (item.item_notes && item.item_notes.trim())
                ? `<div class="od-note-pill"><i class="fas fa-pen-to-square"></i> ${esc(item.item_notes)}</div>`
                : '';
            const tr = document.createElement('tr');
            tr.className = 'od-item-row';
            tr.innerHTML = `
                <td class="od-td"><span class="od-item-name">${esc(item.item_name)}</span>${notePill}</td>
                <td class="od-td od-td-center">${item.quantity || 1}</td>
                <td class="od-td od-td-right od-muted">₱${fmt(item.unit_price)}</td>
                <td class="od-td od-td-right od-item-subtotal">₱${itemSub}</td>`;
            tbody.appendChild(tr);

            (item.addons || []).forEach(addon => {
                const aSub = (parseFloat(addon.unit_price || 0) * parseInt(addon.quantity || 1)).toFixed(2);
                const atr  = document.createElement('tr');
                atr.className = 'od-addon-row';
                atr.innerHTML = `
                    <td class="od-td od-td-addon"><i class="fas fa-corner-down-right od-addon-icon"></i>${esc(addon.addon_name)}</td>
                    <td class="od-td od-td-center od-muted">${addon.quantity || 1}</td>
                    <td class="od-td od-td-right od-muted">₱${fmt(addon.unit_price)}</td>
                    <td class="od-td od-td-right od-muted">₱${aSub}</td>`;
                tbody.appendChild(atr);
            });
        });
    }

    // Totals
    const total    = parseFloat(order.total_amount || 0);
    const subtotal = total / 1.12;
    const vat      = total - subtotal;
    set('todSubtotal', subtotal.toFixed(2));
    set('todVat',      vat.toFixed(2));
    set('todTotal',    total.toFixed(2));

    const totalDisplay = document.getElementById('todTotalDisplay');
    if (totalDisplay) {
        totalDisplay.style.color = order.status === 'completed' ? 'var(--success)' : 'var(--danger)';
    }

    // Payment section
    const paySection = document.getElementById('todPaymentSection');
    if (paySection) {
        if (payment && order.status === 'completed') {
            paySection.style.display = '';
            set('todPayMethod',  payment.payment_method || '—');
            set('todAmountPaid', fmt(payment.amount_paid));
            set('todChange',     fmt(payment.change_amount));
        } else {
            paySection.style.display = 'none';
        }
    }
}

// Helpers
function esc(str) {
    return String(str || '')
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function fmt(v) {
    const n = parseFloat(v || 0);
    return isNaN(n) ? '0.00' : n.toFixed(2);
}


// ════════════════════════════════════════════════════════════════════
// REAL-TIME TABLE POLLING (every 5 seconds)
// Patches the DOM surgically — no full-page reload, no flicker
// ════════════════════════════════════════════════════════════════════
const POLL_INTERVAL   = 2000;
const GET_TABLES_URL  = '/coffee_shop/app/ajax/get_tables.php';
const userRole        = '<?= $_SESSION['role'] ?? '' ?>';
const isAdmin         = userRole === 'admin';
let   pollTimer       = null;
let   lastTableStates = {}; // table_id → status, for change detection

function startPolling() {
    stopPolling();
    pollTimer = setInterval(pollTables, POLL_INTERVAL);
}
function stopPolling() {
    if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
}

async function pollTables() {
    try {
        const res = await fetch(GET_TABLES_URL, { headers: { 'Cache-Control': 'no-cache' } });
        if (!res.ok) return;
        const data = await res.json();
        if (!data.success) return;
        patchTableGrid(data.tables || []);
        patchStats(data.stats || {});
    } catch (e) {
        // silently ignore network errors — will retry next tick
    }
}

// ── Patch stat cards without re-render ──────────────────────────────
function patchStats(stats) {
    const els = {
        total:    document.getElementById('statTotal'),
        available:document.getElementById('statAvailable'),
        occupied: document.getElementById('statOccupied'),
    };
    if (els.total)     els.total.textContent     = stats.total    || 0;
    if (els.available) els.available.textContent = stats.available || 0;
    if (els.occupied)  els.occupied.textContent  = stats.occupied  || 0;
}

// ── Patch grid: only re-render cards whose status actually changed ───
function patchTableGrid(tables) {
    tables.forEach(table => {
        const id       = String(table.table_id);
        const prev     = lastTableStates[id];
        const card     = document.querySelector(`.table-card[data-table-id="${id}"]`);

        const statusChanged      = !prev || prev.status !== table.status;
        const orderChanged       = !prev || prev.order_number !== (table.order_number || '');
        const amountChanged      = !prev || String(prev.total_amount) !== String(table.total_amount);
        const orderStatusChanged = !prev || prev.order_status !== (table.order_status || '');

        // Update tracking state
        lastTableStates[id] = {
            status:       table.status,
            order_number: table.order_number || '',
            total_amount: table.total_amount || 0,
            order_status: table.order_status || '',
        };

        if (!card) {
            // New table appeared — full grid rebuild needed
            if (statusChanged) rebuildGrid(tables);
            return;
        }

        if (!statusChanged && !orderChanged && !amountChanged && !orderStatusChanged) return;

        if (statusChanged || orderStatusChanged) {
            // Rebuild this card fully (status or payment state changed)
            const newCard = buildTableCardHTML(table);
            card.outerHTML = newCard;
        } else {
            // Only order info changed (amount, customer) — patch inline
            patchCardOrderInfo(card, table);
        }

        // If the order details modal is open for this table's order, refresh it
        refreshOpenModal(table);
    });

    // Check if any cards in DOM no longer exist in data (table deleted)
    document.querySelectorAll('.table-card[data-table-id]').forEach(card => {
        const id = card.getAttribute('data-table-id');
        if (!tables.find(t => String(t.table_id) === id)) {
            card.remove();
        }
    });
}

function patchCardOrderInfo(card, table) {
    const amtEl = card.querySelector('.order-amt');
    if (amtEl) amtEl.textContent = '₱' + parseFloat(table.total_amount || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    const sinceEl = card.querySelector('.order-time span');
    if (sinceEl && table.since_time) sinceEl.textContent = 'Since ' + table.since_time;
}

// ── Full grid rebuild (for new tables or first load) ─────────────────
function rebuildGrid(tables) {
    const grid = document.querySelector('.tables-grid');
    if (!grid) return;
    grid.innerHTML = tables.map(buildTableCardHTML).join('');
}

// ── Build a single table card HTML string ─────────────────────────────
function buildTableCardHTML(table) {
    const isOccupied  = table.status === 'occupied';
    const statusLabel = isOccupied ? 'Occupied' : 'Available';
    const dotIcon     = isOccupied ? 'fa-circle-dot' : 'fa-circle-check';
    const iconClass   = isOccupied ? 'occupied' : 'available';

    const orderInfo = (isOccupied && table.order_number) ? `
        <div class="table-card-order">
            <div class="order-row">
                <i class="fas fa-receipt" style="color:var(--primary);font-size:0.65rem;"></i>
                <span class="order-num">#${esc(table.order_number)}</span>
            </div>
            ${table.customer_name ? `
            <div class="order-row">
                <i class="fas fa-user" style="font-size:0.65rem;"></i>
                <span>${esc(table.customer_name)}</span>
            </div>` : ''}
            <div class="order-row">
                <i class="fas fa-coins" style="color:var(--success);font-size:0.65rem;"></i>
                <span class="order-amt">₱${parseFloat(table.total_amount || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}</span>
            </div>
            ${table.since_time ? `
            <div class="order-row order-time">
                <i class="fas fa-clock" style="font-size:0.65rem;"></i>
                <span>Since ${esc(table.since_time)}</span>
            </div>` : ''}
        </div>` : '';

    // Actions
    let actions = '';
    if (isOccupied) {
        const isCompleted = table.order_status === 'completed';
        const freeBtn = isCompleted
            ? `<button type="button" class="btn btn-sm btn-warning"
                        onclick="openFreeModal(${table.table_id}, '${esc(table.table_name)}')">
                   <i class="fas fa-door-open"></i> Free
               </button>`
            : `<span class="btn btn-sm table-awaiting-btn"
                     title="Collect payment first before freeing the table">
                   <i class="fas fa-lock"></i> Awaiting Payment
               </span>`;
        actions = `
            <button type="button" class="btn btn-sm btn-outline-secondary"
                    onclick="viewTableOrder(${table.current_order_id}, '${esc(table.order_number || '')}')">
                <i class="fas fa-eye"></i> View
            </button>
            ${freeBtn}`;
    } else if (isAdmin) {
        actions = `
            <button class="btn btn-sm btn-primary"
                    onclick="openEditModal(${table.table_id}, '${esc(table.table_name)}', ${table.capacity})">
                <i class="fas fa-pen"></i> Edit
            </button>
            <button type="button" class="btn btn-sm btn-danger"
                    onclick="openDeleteModal(${table.table_id}, '${esc(table.table_name)}')">
                <i class="fas fa-trash"></i>
            </button>`;
    } else {
        actions = `<span class="btn btn-sm table-available-btn w-100">
            <i class="fas fa-circle-check"></i> Available
        </span>`;
    }

    return `
    <div class="table-card ${table.status}" data-table-id="${table.table_id}">
        <div class="table-card-icon ${iconClass}"><i class="fas fa-chair"></i></div>
        <div class="table-card-name">${esc(table.table_name)}</div>
        <div class="table-card-capacity"><i class="fas fa-users me-1"></i>${table.capacity} seats</div>
        <span class="table-card-status ${table.status}">
            <i class="fas ${dotIcon}" style="font-size:0.6rem;"></i> ${statusLabel}
        </span>
        ${orderInfo}
        <div class="table-card-actions">${actions}</div>
    </div>`;
}

// ── Pause polling when tab is hidden, resume on focus ─────────────────
document.addEventListener('visibilitychange', () => {
    document.hidden ? stopPolling() : startPolling();
});

// ── Refresh the order details modal if it's open for this order ──────
let openModalOrderId = null; // tracks which order_id is currently open

function refreshOpenModal(table) {
    if (!openModalOrderId) return;
    if (String(table.current_order_id) !== String(openModalOrderId)) return;

    const modalEl = document.getElementById('tableOrderDetailsModal');
    if (!modalEl || !modalEl.classList.contains('show')) return;

    // Silently re-fetch and re-populate without showing the spinner
    fetch(`/coffee_shop/app/ajax/get_order_details.php?order_id=${openModalOrderId}`)
        .then(r => r.json())
        .then(res => {
            if (!res.success) return;
            populateTodModal(res.data.order || {}, res.data.items || [], res.data.payment || {});
            // Make sure content is visible (in case it was in error state)
            const contentEl = document.getElementById('todContent');
            const errorEl   = document.getElementById('todError');
            const loaderEl  = document.getElementById('todLoader');
            if (loaderEl)  loaderEl.style.display  = 'none';
            if (errorEl)   errorEl.style.display   = 'none';
            if (contentEl) contentEl.style.display = 'block';
        })
        .catch(() => {}); // silently ignore
}


</script>
</body>
</html>