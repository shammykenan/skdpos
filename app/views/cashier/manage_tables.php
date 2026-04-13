<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5">
    <title>Table Management — Dine with Din</title>
    <link rel="icon" type="image/png" href="<?= $baseUrl ?>/assets/images/logo/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fraunces:opsz,wght@9..144,700;9..144,900&display=swap" rel="stylesheet">
    <style>
        /* ── TOKENS ─────────────────────────────────────────────────── */
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
            --sidebar-w:     260px;
            --top-h:         64px;
            --radius-sm:     8px;
            --radius-md:     12px;
            --radius-lg:     18px;
            --radius-xl:     24px;
        }

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

        /* ── STAT CARDS ───────────────────────────────────────────────── */
        .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.75rem; }
        .stat-card {
            background: #fff; border: 1px solid rgba(203,213,224,0.6);
            border-radius: var(--radius-lg); padding: 1.25rem 1.35rem;
            display: flex; align-items: center; gap: 1rem;
            position: relative; overflow: hidden;
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .stat-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; }
        .stat-card.c-total::before    { background: var(--primary); }
        .stat-card.c-avail::before    { background: var(--success); }
        .stat-card.c-occupied::before { background: var(--secondary); }
        .stat-card:hover { box-shadow: 0 8px 24px rgba(15,76,92,0.08); transform: translateY(-2px); }
        .stat-icon {
            width: 46px; height: 46px; border-radius: var(--radius-md);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; flex-shrink: 0;
        }
        .stat-icon.c-total    { background: rgba(15,76,92,0.08);  color: var(--primary);   border: 1px solid rgba(15,76,92,0.12); }
        .stat-icon.c-avail    { background: var(--success-soft);   color: var(--success);   border: 1px solid rgba(44,122,90,0.15); }
        .stat-icon.c-occupied { background: rgba(255,107,74,0.08); color: var(--secondary); border: 1px solid rgba(255,107,74,0.15); }
        .stat-label { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: var(--warm-gray); margin-bottom: 0.2rem; }
        .stat-value { font-size: 1.65rem; font-weight: 800; color: var(--navy); line-height: 1; }

        /* ── TABLE GRID CARD WRAPPER ──────────────────────────────────── */
        .grid-card {
            background: #fff; border: 1px solid rgba(203,213,224,0.6);
            border-radius: var(--radius-xl); overflow: hidden;
            box-shadow: 0 4px 16px rgba(15,76,92,0.03);
        }
        .grid-card-header {
            padding: 1rem 1.5rem; border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;
        }
        .grid-card-title { font-weight: 700; font-size: 0.95rem; color: var(--navy); display: flex; align-items: center; gap: 0.5rem; }
        .grid-card-title i { color: var(--primary-light); }
        .grid-card-body { padding: 1.4rem; }
        .count-badge { background: rgba(15,76,92,0.08); color: var(--primary-dark); font-size: 0.7rem; font-weight: 700; padding: 0.2rem 0.65rem; border-radius: 20px; }

        /* ── TABLE CARDS GRID ─────────────────────────────────────────── */
        .tables-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 1.1rem; }

        .table-card {
            background: #fff; border: 1.5px solid var(--border); border-radius: var(--radius-lg);
            padding: 1.15rem; transition: all 0.2s; position: relative;
            box-shadow: 0 2px 8px rgba(15,76,92,0.03);
            display: flex; flex-direction: column; gap: 0;
        }
        .table-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; border-radius: var(--radius-lg) var(--radius-lg) 0 0; }
        .table-card.available { border-color: rgba(44,122,90,0.25); }
        .table-card.available::before { background: var(--success); }
        .table-card.occupied  { border-color: rgba(255,107,74,0.3); background: rgba(255,107,74,0.012); }
        .table-card.occupied::before  { background: var(--secondary); }
        .table-card:hover { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(15,76,92,0.09); }

        .tc-icon {
            width: 40px; height: 40px; border-radius: var(--radius-md);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.05rem; margin-bottom: 0.65rem; flex-shrink: 0;
        }
        .tc-icon.available { background: rgba(44,122,90,0.1); color: var(--success); }
        .tc-icon.occupied  { background: rgba(255,107,74,0.1); color: var(--secondary); }

        .tc-name     { font-size: 0.95rem; font-weight: 700; color: var(--navy); margin-bottom: 0.1rem; }
        .tc-capacity { font-size: 0.72rem; color: var(--warm-gray); margin-bottom: 0.5rem; }
        .tc-status   { display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.22rem 0.65rem; border-radius: 20px; font-size: 0.7rem; font-weight: 700; }
        .tc-status.available { background: rgba(44,122,90,0.1);  color: var(--success); }
        .tc-status.occupied  { background: rgba(255,107,74,0.1); color: var(--secondary); }
        .tc-dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; flex-shrink: 0; }

        .tc-order-box {
            margin-top: 0.65rem; padding: 0.6rem 0.75rem;
            background: var(--light-bg); border-radius: var(--radius-md);
            font-size: 0.75rem; color: var(--warm-gray); flex: 1;
            border: 1px solid rgba(203,213,224,0.4);
        }
        .tc-order-row { display: flex; align-items: center; gap: 0.35rem; margin-bottom: 0.2rem; }
        .tc-order-row:last-child { margin-bottom: 0; }
        .tc-order-num  { font-weight: 700; color: var(--navy); }
        .tc-order-amt  { font-weight: 700; color: var(--success); }
        .tc-order-time { color: var(--primary-light); font-size: 0.7rem; }

        .tc-actions { display: flex; gap: 0.4rem; margin-top: 0.8rem; flex-wrap: wrap; }
        .tc-actions .tc-btn { flex: 1; min-width: 0; justify-content: center; white-space: nowrap; }

        /* ── SMALL BUTTONS inside table cards ─────────────────────────── */
        .tc-btn {
            padding: 0.38rem 0.85rem; border-radius: 30px; font-weight: 600;
            font-size: 0.78rem; display: inline-flex; align-items: center;
            gap: 0.3rem; cursor: pointer; border: none; transition: all 0.15s;
            font-family: inherit;
        }
        .tc-btn-view     { background: #f0f5fa; color: var(--primary); border: 1px solid rgba(15,76,92,0.15); }
        .tc-btn-view:hover { background: var(--lavender); }
        .tc-btn-edit     { background: #fff3e0; color: #b85a1a; border: 1px solid rgba(244,185,66,0.2); }
        .tc-btn-edit:hover { background: var(--warning); color: #1a202c; }
        .tc-btn-delete   { background: var(--danger-soft); color: var(--danger); border: 1px solid rgba(214,64,69,0.2); }
        .tc-btn-delete:hover { background: var(--danger); color: #fff; }
        .tc-btn-free     { background: rgba(244,185,66,0.12); color: #92600a; border: 1.5px solid rgba(244,185,66,0.35); }
        .tc-btn-free:hover { background: var(--warning); color: #1a202c; }
        .tc-btn-awaiting { background: rgba(244,185,66,0.08); color: #92600a; border: 1.5px solid rgba(244,185,66,0.25); cursor: default; font-size: 0.72rem; }
        .tc-btn-avail-static { background: rgba(44,122,90,0.08); color: var(--success); border: 1.5px solid rgba(44,122,90,0.2); cursor: default; }

        /* ── MODALS ───────────────────────────────────────────────────── */
        .modal-content { border: none; border-radius: var(--radius-xl); box-shadow: 0 24px 60px rgba(0,0,0,0.12); }
        .modal-header { border-bottom: 1px solid var(--border); padding: 1.4rem 1.6rem; border-radius: var(--radius-xl) var(--radius-xl) 0 0; }
        .modal-title { font-family: 'Fraunces', Georgia, serif; font-size: 1.15rem; font-weight: 700; color: var(--navy); display: flex; align-items: center; gap: 0.5rem; }
        .modal-body  { padding: 1.5rem 1.6rem; }
        .modal-footer { border-top: 1px solid var(--border); padding: 1.2rem 1.6rem; border-radius: 0 0 var(--radius-xl) var(--radius-xl); gap: 0.5rem; }
        .form-label { font-size: 0.82rem; font-weight: 700; color: var(--navy); margin-bottom: 0.4rem; }
        .form-control {
            border: 1px solid var(--border); border-radius: var(--radius-md);
            padding: 0.6rem 0.9rem; font-size: 0.88rem; font-family: inherit;
            background: #fff; color: var(--dark); transition: border-color 0.15s, box-shadow 0.15s;
            width: 100%;
        }
        .form-control:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(15,76,92,0.1); }
        .form-text { font-size: 0.75rem; color: var(--warm-gray); margin-top: 0.25rem; }
        .btn-modal-primary   { background: var(--primary); color: #fff; border: none; padding: 0.6rem 1.4rem; border-radius: 40px; font-weight: 600; font-size: 0.88rem; cursor: pointer; transition: background 0.15s; display: inline-flex; align-items: center; gap: 0.4rem; font-family: inherit; }
        .btn-modal-primary:hover { background: var(--primary-dark); }
        .btn-modal-warning   { background: var(--warning); color: #1a202c; border: none; padding: 0.6rem 1.4rem; border-radius: 40px; font-weight: 700; font-size: 0.88rem; cursor: pointer; transition: background 0.15s; display: inline-flex; align-items: center; gap: 0.4rem; font-family: inherit; }
        .btn-modal-warning:hover { background: #e0a830; }
        .btn-modal-danger    { background: var(--danger); color: #fff; border: none; padding: 0.6rem 1.4rem; border-radius: 40px; font-weight: 600; font-size: 0.88rem; cursor: pointer; width: 100%; transition: background 0.15s; display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; font-family: inherit; }
        .btn-modal-danger:hover { background: #bb3338; }
        .btn-modal-secondary { background: #fff; color: var(--dark); border: 1px solid var(--border); padding: 0.6rem 1.4rem; border-radius: 40px; font-weight: 600; font-size: 0.88rem; cursor: pointer; transition: background 0.15s; font-family: inherit; }
        .btn-modal-secondary:hover { background: var(--light-bg); }

        /* ── CONFIRM MODALS ───────────────────────────────────────────── */
        .confirm-icon { width: 64px; height: 64px; border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; font-size: 1.75rem; margin: 0 auto 1rem; }
        .confirm-icon.warning { background: rgba(244,185,66,0.12); color: #92600a; }
        .confirm-icon.danger  { background: rgba(214,64,69,0.1);   color: var(--danger); }
        .confirm-note { border-radius: var(--radius-md); padding: 0.75rem 1rem; font-size: 0.78rem; margin-top: 0.75rem; }
        .confirm-note.warning { background: rgba(244,185,66,0.08); border: 1px dashed rgba(244,185,66,0.4); color: #7a5200; }
        .confirm-note.danger  { background: var(--danger-soft); border: 1px dashed rgba(214,64,69,0.3); color: #9e3a34; }

        /* ── FEEDBACK MODAL ───────────────────────────────────────────── */
        .feedback-icon { width: 64px; height: 64px; border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; font-size: 1.75rem; margin: 0 auto 1rem; }
        .feedback-icon.success { background: var(--success-soft); color: var(--success); }
        .feedback-icon.danger  { background: var(--danger-soft);  color: var(--danger); }

        /* ── ORDER DETAILS MODAL ──────────────────────────────────────── */
        .od-modal-content { border-radius: var(--radius-xl); border: none; box-shadow: 0 24px 60px rgba(0,0,0,0.13); overflow: hidden; display: flex; flex-direction: column; max-height: 90vh; }
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
        .od-td-center { text-align: center; color: var(--warm-gray); }
        .od-td-right  { text-align: right; }
        .od-muted     { color: var(--warm-gray); }
        .od-item-name { font-weight: 600; color: var(--navy); font-size: 0.85rem; }
        .od-item-subtotal { font-weight: 700; color: var(--navy); }
        .od-addon-row .od-td { border-bottom: 1px dashed #edf2f7; font-size: 0.78rem; color: var(--warm-gray); background: #fafcff; }
        .od-td-addon  { padding-left: 1.7rem !important; }
        .od-addon-icon { margin-right: 0.28rem; font-size: 0.6rem; opacity: 0.55; }
        .od-note-pill { display: inline-flex; align-items: center; gap: 0.25rem; margin-top: 0.25rem; padding: 0.16rem 0.48rem; background: rgba(254,228,64,0.18); border: 1px dashed rgba(244,185,66,0.5); border-radius: 20px; font-size: 0.68rem; color: #7a5c00; font-style: italic; }
        .od-note-pill i { color: #b87a1a; font-size: 0.6rem; }
        .od-footer-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.9rem; padding: 1rem 1.35rem 1.3rem; background: var(--light-bg); border-top: 1px solid var(--border); margin-top: 0.4rem; }
        .od-payment-card { background: white; border: 1px solid rgba(44,122,90,0.2); border-radius: var(--radius-md); padding: 0.8rem 0.95rem; }
        .od-payment-title { font-size: 0.63rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.7px; color: var(--success); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.3rem; }
        .od-payment-row { display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; padding: 0.2rem 0; color: var(--warm-gray); border-bottom: 1px solid #f0f4f8; }
        .od-payment-row:last-child { border-bottom: none; }
        .od-payment-row span:last-child { font-weight: 600; color: var(--navy); }
        .od-totals-card { background: white; border: 1px solid var(--border); border-radius: var(--radius-md); padding: 0.8rem 0.95rem; }
        .od-totals-row { display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; padding: 0.2rem 0; color: var(--warm-gray); border-bottom: 1px solid #f0f4f8; }
        .od-totals-row:last-child { border-bottom: none; }
        .od-totals-row span:last-child { font-weight: 600; color: var(--navy); }
        .od-totals-total { padding-top: 0.5rem; margin-top: 0.25rem; border-top: 2px solid var(--border) !important; border-bottom: none !important; font-size: 0.9rem; font-weight: 700; }
        .od-totals-total span:last-child { font-size: 1.08rem; font-weight: 800; }

        /* ── EMPTY STATE ──────────────────────────────────────────────── */
        .empty-state { text-align: center; padding: 4rem 1rem; color: var(--warm-gray); }
        .empty-state i { font-size: 2.5rem; opacity: 0.2; display: block; margin-bottom: 0.8rem; }

        /* ── ANIMATIONS ───────────────────────────────────────────────── */
        .fade-up { animation: fadeUp 0.35s ease forwards; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        .fade-up-1 { animation-delay: 0.05s; opacity: 0; }
        .fade-up-2 { animation-delay: 0.10s; opacity: 0; }
        .fade-up-3 { animation-delay: 0.15s; opacity: 0; }

        /* ── RESPONSIVE ───────────────────────────────────────────────── */
        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main { margin-left: 0; padding: 1.5rem 1.25rem; }
            .mobile-toggle { display: flex; align-items: center; }
        }
        @media (min-width: 768px) and (max-width: 1024px) {
            .tables-grid { grid-template-columns: repeat(auto-fill, minmax(185px, 1fr)); gap: 0.9rem; }
            .stats-row { gap: 0.85rem; }
        }
        @media (max-width: 767.98px) {
            .main { padding: 1.1rem; }
            .page-title { font-size: 1.4rem; }
            .stats-row { gap: 0.65rem; }
            .tables-grid { grid-template-columns: repeat(2, 1fr); gap: 0.8rem; }
            .grid-card-body { padding: 1rem; }
            .od-meta-grid { grid-template-columns: repeat(2,1fr); }
            .od-meta-item:nth-child(3n) { border-right: 1px solid var(--border); }
            .od-meta-item:nth-child(2n) { border-right: none; }
            .od-footer-grid { grid-template-columns: 1fr; gap: 0.7rem; padding: 0.85rem 1rem 1rem; }
            .modal-dialog { margin: 0.5rem; }
        }
        @media (max-width: 575.98px) {
            .topbar { padding: 0 1rem; }
            .brand-text-sub { display: none; }
            .stats-row { grid-template-columns: repeat(3,1fr); gap: 0.5rem; }
            .stat-card { padding: 0.85rem 0.75rem; flex-direction: column; align-items: flex-start; gap: 0.5rem; }
            .stat-icon { width: 36px; height: 36px; font-size: 0.85rem; }
            .stat-value { font-size: 1.4rem; }
            .stat-label { font-size: 0.62rem; }
            .tables-grid { grid-template-columns: repeat(2,1fr); gap: 0.6rem; }
            .table-card { padding: 0.9rem; }
            .tc-icon { width: 34px; height: 34px; font-size: 0.9rem; }
            .tc-name { font-size: 0.88rem; }
            .modal-dialog { margin: 0; max-width: 100%; position: fixed; bottom: 0; left: 0; right: 0; }
            .modal-dialog-centered { min-height: unset; align-items: flex-end; }
            .modal-content, .od-modal-content { border-radius: var(--radius-xl) var(--radius-xl) 0 0 !important; max-height: 92vh; overflow-y: auto; }
            .od-header { border-radius: var(--radius-xl) var(--radius-xl) 0 0; }
        }
        @media (max-width: 375px) {
            .stat-value { font-size: 1.2rem; }
            .tables-grid { gap: 0.45rem; }
            .table-card { padding: 0.75rem; }
            .tc-name { font-size: 0.82rem; }
        }
    </style>
</head>
<body>

<!-- ── TOPBAR ─────────────────────────────────────────────────────────── -->
<header class="topbar">
    <button class="mobile-toggle" id="menuToggle" aria-label="Toggle menu"><i class="fas fa-bars"></i></button>
    <a href="<?= $baseUrl ?>/index.php?page=<?= hasRole('admin') ? 'admin_dashboard' : 'manage_pos' ?>" class="topbar-brand">
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
        <?php
            $fullName = $_SESSION['full_name'] ?? 'User';
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
                    <div style="font-size:0.78rem;color:var(--warm-gray);"><?= hasRole('admin') ? 'Administrator' : 'Cashier' ?></div>
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
        <?php if (hasRole('admin')): ?>
        <div class="nav-section">Dashboard</div>
        <a href="<?= $baseUrl ?>/index.php?page=admin_dashboard" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <div class="nav-section">Management</div>
        <a href="<?= $baseUrl ?>/index.php?page=manage_menu"        class="nav-link"><i class="fas fa-utensils"></i> Menu Items</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_recipes"     class="nav-link"><i class="fas fa-tags"></i> Recipes</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_ingredients" class="nav-link"><i class="fas fa-boxes"></i> Ingredients</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_addons"      class="nav-link"><i class="fas fa-plus-circle"></i> Add-ons</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_tables"      class="nav-link active"><i class="fas fa-chair"></i> Tables</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_users"       class="nav-link"><i class="fas fa-users"></i> User Management</a>
        <div class="nav-section">Sales</div>
        <a href="<?= $baseUrl ?>/index.php?page=manage_pos"    class="nav-link"><i class="fas fa-cash-register"></i> POS System</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_orders" class="nav-link"><i class="fas fa-receipt"></i> Orders</a>
        <a href="<?= $baseUrl ?>/index.php?page=reports"       class="nav-link"><i class="fas fa-chart-bar"></i> Reports</a>
        <?php endif; ?>
        <?php if (hasRole('cashier') && !hasRole('admin')): ?>
        <div class="nav-section">Management</div>
        <a href="<?= $baseUrl ?>/index.php?page=manage_tables" class="nav-link active"><i class="fas fa-chair"></i> Tables</a>
        <div class="nav-section">Sales</div>
        <a href="<?= $baseUrl ?>/index.php?page=manage_pos"    class="nav-link"><i class="fas fa-cash-register"></i> POS System</a>
        <a href="<?= $baseUrl ?>/index.php?page=manage_orders" class="nav-link"><i class="fas fa-receipt"></i> Orders</a>
        <a href="<?= $baseUrl ?>/index.php?page=reports"       class="nav-link"><i class="fas fa-chart-bar"></i> Reports</a>
        <?php endif; ?>
    </div>
</nav>

<!-- ── MAIN ──────────────────────────────────────────────────────────── -->
<main class="main" id="mainContent">

    <div class="page-head">
        <div>
            <h1 class="page-title">Table Management</h1>
            <div class="breadcrumb-bar">
                <a href="<?= $baseUrl ?>/index.php?page=<?= hasRole('admin') ? 'admin_dashboard' : 'manage_pos' ?>">Home</a>
                <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:0.6rem;"></i></span>
                <span>Tables</span>
            </div>
        </div>
        <?php if (hasRole('admin')): ?>
        <button type="button" class="btn-primary-action" data-bs-toggle="modal" data-bs-target="#addTableModal">
            <i class="fas fa-plus"></i> Add Table
        </button>
        <?php endif; ?>
    </div>

    <!-- Session flash rendered to JS -->
    <?php if (!empty($_SESSION['success'])): ?>
    <div id="toastData" data-type="success" data-msg="<?= htmlspecialchars($_SESSION['success'], ENT_QUOTES) ?>"></div>
    <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
    <div id="toastData" data-type="error"   data-msg="<?= htmlspecialchars($_SESSION['error'],   ENT_QUOTES) ?>"></div>
    <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-card c-total fade-up fade-up-1">
            <div class="stat-icon c-total"><i class="fas fa-chair"></i></div>
            <div><div class="stat-label">Total</div><div class="stat-value" id="statTotal"><?= $tableStats['total'] ?? 0 ?></div></div>
        </div>
        <div class="stat-card c-avail fade-up fade-up-2">
            <div class="stat-icon c-avail"><i class="fas fa-circle-check"></i></div>
            <div><div class="stat-label">Available</div><div class="stat-value" id="statAvailable"><?= $tableStats['available'] ?? 0 ?></div></div>
        </div>
        <div class="stat-card c-occupied fade-up fade-up-3">
            <div class="stat-icon c-occupied"><i class="fas fa-user-clock"></i></div>
            <div><div class="stat-label">Occupied</div><div class="stat-value" id="statOccupied"><?= $tableStats['occupied'] ?? 0 ?></div></div>
        </div>
    </div>

    <!-- Tables Grid Card -->
    <div class="grid-card fade-up">
        <div class="grid-card-header">
            <div class="grid-card-title">
                <i class="fas fa-th"></i> All Tables
                <span class="count-badge"><?= count($tables ?? []) ?> tables</span>
            </div>
        </div>
        <div class="grid-card-body">
            <?php if (empty($tables)): ?>
            <div class="empty-state">
                <i class="fas fa-chair"></i>
                <p style="font-weight:600;color:var(--navy);margin-bottom:0.3rem;">No tables yet</p>
                <p style="font-size:0.85rem;"><?= hasRole('admin') ? 'Click "Add Table" to get started.' : 'Ask an admin to add tables.' ?></p>
            </div>
            <?php else: ?>
            <div class="tables-grid" id="tablesGrid">
                <?php foreach ($tables as $table):
                    $isOccupied = $table['status'] === 'occupied';
                    $sinceTime  = '';
                    if ($isOccupied && !empty($table['order_started_at'])) {
                        $sinceTime = date('g:i A', strtotime($table['order_started_at']));
                    }
                ?>
                <div class="table-card <?= $table['status'] ?>" data-table-id="<?= $table['table_id'] ?>">
                    <div class="tc-icon <?= $table['status'] ?>"><i class="fas fa-chair"></i></div>
                    <div class="tc-name"><?= htmlspecialchars($table['table_name']) ?></div>
                    <div class="tc-capacity"><i class="fas fa-users" style="font-size:0.65rem;margin-right:0.3rem;"></i><?= $table['capacity'] ?> seats</div>
                    <span class="tc-status <?= $table['status'] ?>">
                        <span class="tc-dot"></span>
                        <?= ucfirst($table['status']) ?>
                    </span>

                    <?php if ($isOccupied && $table['order_number']): ?>
                    <div class="tc-order-box">
                        <div class="tc-order-row">
                            <i class="fas fa-receipt" style="color:var(--primary);font-size:0.62rem;"></i>
                            <span class="tc-order-num">#<?= htmlspecialchars($table['order_number']) ?></span>
                        </div>
                        <?php if ($table['customer_name']): ?>
                        <div class="tc-order-row">
                            <i class="fas fa-user" style="font-size:0.62rem;"></i>
                            <span><?= htmlspecialchars($table['customer_name']) ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="tc-order-row">
                            <i class="fas fa-coins" style="color:var(--success);font-size:0.62rem;"></i>
                            <span class="tc-order-amt">₱<?= number_format($table['total_amount'] ?? 0, 2) ?></span>
                        </div>
                        <?php if ($sinceTime): ?>
                        <div class="tc-order-row tc-order-time">
                            <i class="fas fa-clock" style="font-size:0.62rem;"></i>
                            <span>Since <?= $sinceTime ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <div class="tc-actions">
                        <?php if ($isOccupied): ?>
                            <button type="button" class="tc-btn tc-btn-view tc-btn"
                                    onclick="viewTableOrder(<?= (int)$table['current_order_id'] ?>, '<?= htmlspecialchars($table['order_number'] ?? '', ENT_QUOTES) ?>')">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <?php if ($table['order_status'] === 'completed'): ?>
                                <button type="button" class="tc-btn tc-btn-free tc-btn"
                                        onclick="openFreeModal(<?= $table['table_id'] ?>, '<?= htmlspecialchars($table['table_name'], ENT_QUOTES) ?>')">
                                    <i class="fas fa-door-open"></i> Free
                                </button>
                            <?php else: ?>
                                <span class="tc-btn tc-btn-awaiting tc-btn" title="Order still in progress">
                                    <i class="fas fa-utensils"></i> Dining
                                </span>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php if (hasRole('admin')): ?>
                                <button type="button" class="tc-btn tc-btn-edit tc-btn"
                                        onclick="openEditModal(<?= $table['table_id'] ?>, '<?= htmlspecialchars($table['table_name'], ENT_QUOTES) ?>', <?= $table['capacity'] ?>)">
                                    <i class="fas fa-pen"></i> Edit
                                </button>
                                <button type="button" class="tc-btn tc-btn-delete tc-btn"
                                        onclick="openDeleteModal(<?= $table['table_id'] ?>, '<?= htmlspecialchars($table['table_name'], ENT_QUOTES) ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            <?php else: ?>
                                <span class="tc-btn tc-btn-avail-static tc-btn" style="width:100%;">
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

</main>

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
                <div id="todLoader" class="text-center py-5">
                    <div class="spinner-border" style="color:var(--primary);width:2rem;height:2rem;" role="status"></div>
                    <p class="mt-3 text-muted mb-0" style="font-size:0.86rem;">Fetching order details…</p>
                </div>
                <div id="todContent" style="display:none;">
                    <div class="od-meta-grid">
                        <div class="od-meta-item"><div class="od-meta-label">Customer</div><div class="od-meta-value" id="todCustomer">—</div></div>
                        <div class="od-meta-item"><div class="od-meta-label">Date &amp; Time</div><div class="od-meta-value" id="todDate">—</div></div>
                        <div class="od-meta-item"><div class="od-meta-label">Cashier</div><div class="od-meta-value" id="todCashier">—</div></div>
                        <div class="od-meta-item"><div class="od-meta-label">Order Type</div><div class="od-meta-value" id="todOrderType">—</div></div>
                        <div class="od-meta-item"><div class="od-meta-label">Status</div><div class="od-meta-value" id="todStatus">—</div></div>
                        <div class="od-meta-item"><div class="od-meta-label">Table</div><div class="od-meta-value" id="todTable">—</div></div>
                    </div>
                    <div class="od-items-section">
                        <div class="od-section-label"><i class="fas fa-list"></i> Order Items</div>
                        <table class="od-items-table">
                            <thead><tr>
                                <th>Item</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Subtotal</th>
                            </tr></thead>
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
                            <div class="od-totals-row od-totals-total"><span>Total</span><span>₱<span id="todTotalDisplay"><span id="todTotal">0.00</span></span></span></div>
                        </div>
                    </div>
                </div>
                <div id="todError" style="display:none;text-align:center;padding:3rem 1.5rem;color:var(--danger);">
                    <i class="fas fa-exclamation-circle" style="font-size:2.5rem;margin-bottom:0.75rem;display:block;"></i>
                    <p class="fw-semibold mb-0">Failed to load order details. Please try again.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Close</button>
            </div>
        </div>
    </div>
</div>

<!-- FREE TABLE CONFIRM MODAL -->
<div class="modal fade" id="freeTableModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom:none;padding-bottom:0;">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" style="padding:1rem 1.6rem 1.25rem;">
                <div class="confirm-icon warning"><i class="fas fa-door-open"></i></div>
                <h5 style="font-family:'Fraunces',serif;font-weight:700;color:var(--navy);margin-bottom:0.5rem;">Free this table?</h5>
                <p id="freeTableMsg" style="font-size:0.85rem;color:var(--warm-gray);margin-bottom:0.5rem;"></p>
                <div class="confirm-note warning"><i class="fas fa-exclamation-triangle me-1"></i> Only do this after the customer has physically left.</div>
            </div>
            <div class="modal-footer" style="border-top:none;">
                <button type="button" class="btn-modal-secondary" style="flex:1;" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="<?= $baseUrl ?>/index.php?page=manage_tables" style="flex:1;">
                    <input type="hidden" name="table_id" id="freeTableId">
                    <button type="submit" name="free_table" class="btn-modal-warning" style="width:100%;"><i class="fas fa-door-open me-1"></i> Yes, Free</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- DELETE TABLE CONFIRM MODAL -->
<?php if (hasRole('admin')): ?>
<div class="modal fade" id="deleteTableModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom:none;padding-bottom:0;">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" style="padding:1rem 1.6rem 1.25rem;">
                <div class="confirm-icon danger"><i class="fas fa-trash"></i></div>
                <h5 style="font-family:'Fraunces',serif;font-weight:700;color:var(--navy);margin-bottom:0.5rem;">Delete Table?</h5>
                <p id="deleteTableMsg" style="font-size:0.85rem;color:var(--warm-gray);margin-bottom:0.5rem;"></p>
                <div class="confirm-note danger"><i class="fas fa-exclamation-triangle me-1"></i> This cannot be undone.</div>
            </div>
            <div class="modal-footer" style="border-top:none;">
                <button type="button" class="btn-modal-secondary" style="flex:1;" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="<?= $baseUrl ?>/index.php?page=manage_tables" style="flex:1;">
                    <input type="hidden" name="table_id" id="deleteTableId">
                    <button type="submit" name="delete_table" class="btn-modal-danger"><i class="fas fa-trash me-1"></i> Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ADD TABLE MODAL -->
<div class="modal fade" id="addTableModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus" style="color:var(--secondary);font-size:1rem;"></i> Add New Table</h5>
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
                    <button type="button" class="btn-modal-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_table" class="btn-modal-primary"><i class="fas fa-plus me-1"></i> Add Table</button>
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
                <h5 class="modal-title"><i class="fas fa-pen" style="color:var(--warning);font-size:1rem;"></i> Edit Table</h5>
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
                    <button type="button" class="btn-modal-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_table" class="btn-modal-warning"><i class="fas fa-save me-1"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- FEEDBACK MODAL -->
<div class="modal fade" id="feedbackModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:380px;">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom:none;padding-bottom:0;">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" style="padding:1rem 1.6rem 1.25rem;">
                <div class="feedback-icon" id="feedbackIcon"><i id="feedbackIconI" class="fas fa-check-circle"></i></div>
                <h5 style="font-family:'Fraunces',serif;font-weight:700;color:var(--navy);margin-bottom:0.4rem;" id="feedbackTitle">Success</h5>
                <p style="font-size:0.85rem;color:var(--warm-gray);margin:0;" id="feedbackMsg"></p>
            </div>
            <div class="modal-footer" style="border-top:none;justify-content:center;padding-bottom:1.4rem;">
                <button type="button" class="btn-modal-primary px-5" id="feedbackBtn" data-bs-dismiss="modal"><i class="fas fa-check me-1"></i> OK</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const baseUrl         = '<?= $baseUrl ?>';
const POLL_INTERVAL   = 2000;
const GET_TABLES_URL  = `/dinewithdinpos/app/ajax/get_tables.php`;
const userRole        = '<?= $_SESSION['role'] ?? '' ?>';
const isAdmin         = userRole === 'admin';
let   pollTimer       = null;
let   lastTableStates = {};

/* ── Sidebar ──────────────────────────────────────────────────── */
document.getElementById('menuToggle')?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('show');
    document.getElementById('sidebarOverlay').classList.toggle('show');
});
document.getElementById('sidebarOverlay')?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.remove('show');
    document.getElementById('sidebarOverlay').classList.remove('show');
});

/* ── Confirm modals ───────────────────────────────────────────── */
function openFreeModal(tableId, tableName) {
    document.getElementById('freeTableId').value = tableId;
    document.getElementById('freeTableMsg').textContent = `"${tableName}" will be marked as available.`;
    new bootstrap.Modal(document.getElementById('freeTableModal')).show();
}
function openDeleteModal(tableId, tableName) {
    document.getElementById('deleteTableId').value = tableId;
    document.getElementById('deleteTableMsg').textContent = `"${tableName}" will be permanently deleted.`;
    new bootstrap.Modal(document.getElementById('deleteTableModal')).show();
}
function openEditModal(id, name, capacity) {
    document.getElementById('editTableId').value       = id;
    document.getElementById('editTableName').value     = name;
    document.getElementById('editTableCapacity').value = capacity;
    new bootstrap.Modal(document.getElementById('editTableModal')).show();
}

/* ── Feedback modal ───────────────────────────────────────────── */
function showFeedback(type, title, msg) {
    const iconEl  = document.getElementById('feedbackIcon');
    const iconI   = document.getElementById('feedbackIconI');
    const titleEl = document.getElementById('feedbackTitle');
    const msgEl   = document.getElementById('feedbackMsg');
    const btn     = document.getElementById('feedbackBtn');
    iconEl.className  = `feedback-icon ${type}`;
    iconI.className   = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
    titleEl.textContent = title;
    msgEl.textContent   = msg;
    if (btn) btn.style.background = type === 'success' ? 'var(--primary)' : 'var(--danger)';
    new bootstrap.Modal(document.getElementById('feedbackModal')).show();
}

/* ── Init ─────────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    const toast = document.getElementById('toastData');
    if (toast) {
        const type = toast.dataset.type;
        const msg  = toast.dataset.msg;
        showFeedback(type === 'success' ? 'success' : 'danger', type === 'success' ? 'Success' : 'Error', msg);
    }
    document.querySelectorAll('.table-card[data-table-id]').forEach(card => {
        const id = card.getAttribute('data-table-id');
        lastTableStates[id] = {
            status:       card.classList.contains('occupied') ? 'occupied' : 'available',
            order_number: card.querySelector('.tc-order-num')?.textContent?.replace('#','') || '',
            total_amount: card.querySelector('.tc-order-amt')?.textContent?.replace(/[₱,]/g,'') || 0,
            order_status: '',
        };
    });
    startPolling();
});

/* ── Modal backdrop cleanup ───────────────────────────────────── */
document.addEventListener('hidden.bs.modal', (e) => {
    if (e.target.id === 'tableOrderDetailsModal') openModalOrderId = null;
    if (document.querySelector('.modal.show')) return;
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.classList.remove('modal-open');
    document.body.style.overflow     = '';
    document.body.style.paddingRight = '';
});

/* ── View order details ───────────────────────────────────────── */
function viewTableOrder(orderId, orderNumber) {
    const loaderEl  = document.getElementById('todLoader');
    const contentEl = document.getElementById('todContent');
    const errorEl   = document.getElementById('todError');
    const numEl     = document.getElementById('todOrderNumber');
    openModalOrderId = orderId;
    if (loaderEl)  loaderEl.style.display  = 'block';
    if (contentEl) contentEl.style.display = 'none';
    if (errorEl)   errorEl.style.display   = 'none';
    if (numEl)     numEl.textContent        = '#' + orderNumber;
    const modalEl = document.getElementById('tableOrderDetailsModal');
    const modal   = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modal.show();
    fetch(`/dinewithdinpos/app/ajax/get_order_details.php?order_id=${orderId}`)
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
    set('todOrderNumber', '#' + (order.order_number || ''));
    set('todCustomer',    order.customer_name || 'Walk-in');
    set('todCashier',     order.cashier_name  || 'N/A');
    set('todTable',       order.table_name    || '—');
    if (order.created_at) {
        const d = new Date(order.created_at);
        set('todDate', d.toLocaleString('en-PH', { year:'numeric', month:'short', day:'numeric', hour:'numeric', minute:'2-digit', hour12:true }));
    }
    const typeMap = { dine_in:{ icon:'fa-utensils', label:'Dine In' }, takeout:{ icon:'fa-bag-shopping', label:'Takeout' } };
    const t = typeMap[order.order_type] || { icon:'fa-question', label:order.order_type || '—' };
    setHTML('todOrderType', `<span style="display:inline-flex;align-items:center;gap:0.3rem;padding:0.18rem 0.6rem;border-radius:20px;background:rgba(15,76,92,0.1);color:var(--primary);font-size:0.76rem;font-weight:600;"><i class="fas ${t.icon}" style="font-size:0.68rem;"></i>${t.label}</span>`);
    const scMap = { pending:'#f59e0b', preparing:'#3b82f6', ready:'#10b981', completed:'var(--success)', cancelled:'var(--danger)' };
    const sc    = scMap[order.status] || 'var(--warm-gray)';
    const sLabel = (order.status || '').charAt(0).toUpperCase() + (order.status || '').slice(1);
    setHTML('todStatus', `<span style="display:inline-flex;align-items:center;gap:0.3rem;padding:0.18rem 0.6rem;border-radius:20px;background:${sc}1a;color:${sc};font-size:0.76rem;font-weight:700;">${sLabel}</span>`);
    const tbody = document.getElementById('todItemsBody');
    if (tbody) {
        tbody.innerHTML = '';
        items.forEach(item => {
            const itemSub  = (parseFloat(item.unit_price || 0) * parseInt(item.quantity || 1)).toFixed(2);
            const notePill = (item.item_notes && item.item_notes.trim()) ? `<div class="od-note-pill"><i class="fas fa-pen-to-square"></i> ${esc(item.item_notes)}</div>` : '';
            const variantName = item.variant_name_snapshot || item.current_variant_name || item.variant_name || null;
            const variantBit  = variantName ? `<span style="opacity:0.65;font-weight:400;"> · ${esc(variantName)}</span>` : '';
            const tr = document.createElement('tr');
            tr.className = 'od-item-row';
            tr.innerHTML = `<td class="od-td"><span class="od-item-name">${esc(item.item_name)}${variantBit}</span>${notePill}</td><td class="od-td od-td-center">${item.quantity || 1}</td><td class="od-td od-td-right od-muted">₱${fmt(item.unit_price)}</td><td class="od-td od-td-right od-item-subtotal">₱${itemSub}</td>`;
            tbody.appendChild(tr);
            (item.addons || []).forEach(addon => {
                const aSub = (parseFloat(addon.price || 0) * parseInt(addon.quantity || 1)).toFixed(2);
                const atr  = document.createElement('tr');
                atr.className = 'od-addon-row';
                atr.innerHTML = `<td class="od-td od-td-addon"><i class="fas fa-corner-down-right od-addon-icon"></i>${esc(addon.addon_name)}</td><td class="od-td od-td-center od-muted">${addon.quantity || 1}</td><td class="od-td od-td-right od-muted">₱${fmt(addon.price)}</td><td class="od-td od-td-right od-muted">₱${aSub}</td>`;
                tbody.appendChild(atr);
            });
        });
    }
    const total    = parseFloat(order.total_amount || 0);
    const subtotal = total / 1.12;
    const vat      = total - subtotal;
    set('todSubtotal', subtotal.toFixed(2));
    set('todVat',      vat.toFixed(2));
    set('todTotal',    total.toFixed(2));
    const totalDisplay = document.getElementById('todTotalDisplay');
    if (totalDisplay) totalDisplay.style.color = order.status === 'completed' ? 'var(--success)' : 'var(--danger)';
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

function esc(str) { return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function fmt(v)   { const n = parseFloat(v || 0); return isNaN(n) ? '0.00' : n.toFixed(2); }

/* ── Real-time polling ────────────────────────────────────────── */
function startPolling() { stopPolling(); pollTimer = setInterval(pollTables, POLL_INTERVAL); }
function stopPolling()  { if (pollTimer) { clearInterval(pollTimer); pollTimer = null; } }

async function pollTables() {
    try {
        const res = await fetch(GET_TABLES_URL, { headers: { 'Cache-Control': 'no-cache' } });
        if (!res.ok) return;
        const data = await res.json();
        if (!data.success) return;
        patchTableGrid(data.tables || []);
        patchStats(data.stats || {});
    } catch (e) { /* silently ignore */ }
}

function patchStats(stats) {
    const els = { total: document.getElementById('statTotal'), available: document.getElementById('statAvailable'), occupied: document.getElementById('statOccupied') };
    if (els.total)     els.total.textContent     = stats.total     || 0;
    if (els.available) els.available.textContent = stats.available || 0;
    if (els.occupied)  els.occupied.textContent  = stats.occupied  || 0;
}

function patchTableGrid(tables) {
    tables.forEach(table => {
        const id   = String(table.table_id);
        const prev = lastTableStates[id];
        const card = document.querySelector(`.table-card[data-table-id="${id}"]`);
        const statusChanged      = !prev || prev.status !== table.status;
        const orderChanged       = !prev || prev.order_number !== (table.order_number || '');
        const amountChanged      = !prev || String(prev.total_amount) !== String(table.total_amount);
        const orderStatusChanged = !prev || prev.order_status !== (table.order_status || '');
        lastTableStates[id] = { status: table.status, order_number: table.order_number || '', total_amount: table.total_amount || 0, order_status: table.order_status || '' };
        if (!card) { if (statusChanged) rebuildGrid(tables); return; }
        if (!statusChanged && !orderChanged && !amountChanged && !orderStatusChanged) return;
        if (statusChanged || orderStatusChanged) {
            card.outerHTML = buildTableCardHTML(table);
        } else {
            patchCardOrderInfo(card, table);
        }
        refreshOpenModal(table);
    });
    document.querySelectorAll('.table-card[data-table-id]').forEach(card => {
        const id = card.getAttribute('data-table-id');
        if (!tables.find(t => String(t.table_id) === id)) card.remove();
    });
}

function patchCardOrderInfo(card, table) {
    const amtEl = card.querySelector('.tc-order-amt');
    if (amtEl) amtEl.textContent = '₱' + parseFloat(table.total_amount || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    const sinceEl = card.querySelector('.tc-order-time span');
    if (sinceEl && table.since_time) sinceEl.textContent = 'Since ' + table.since_time;
}

function rebuildGrid(tables) {
    const grid = document.querySelector('.tables-grid');
    if (!grid) return;
    grid.innerHTML = tables.map(buildTableCardHTML).join('');
}

function buildTableCardHTML(table) {
    const isOccupied  = table.status === 'occupied';
    const statusLabel = isOccupied ? 'Occupied' : 'Available';
    const iconClass   = isOccupied ? 'occupied' : 'available';
    const orderInfo = (isOccupied && table.order_number) ? `
        <div class="tc-order-box">
            <div class="tc-order-row"><i class="fas fa-receipt" style="color:var(--primary);font-size:0.62rem;"></i><span class="tc-order-num">#${esc(table.order_number)}</span></div>
            ${table.customer_name ? `<div class="tc-order-row"><i class="fas fa-user" style="font-size:0.62rem;"></i><span>${esc(table.customer_name)}</span></div>` : ''}
            <div class="tc-order-row"><i class="fas fa-coins" style="color:var(--success);font-size:0.62rem;"></i><span class="tc-order-amt">₱${parseFloat(table.total_amount || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}</span></div>
            ${table.since_time ? `<div class="tc-order-row tc-order-time"><i class="fas fa-clock" style="font-size:0.62rem;"></i><span>Since ${esc(table.since_time)}</span></div>` : ''}
        </div>` : '';
    let actions = '';
    if (isOccupied) {
        const isCompleted = table.order_status === 'completed';
        const freeBtn = isCompleted
            ? `<button type="button" class="tc-btn tc-btn-free tc-btn" onclick="openFreeModal(${table.table_id}, '${esc(table.table_name)}')"><i class="fas fa-door-open"></i> Free</button>`
            : `<span class="tc-btn tc-btn-awaiting tc-btn" title="Order still in progress"><i class="fas fa-utensils"></i> Dining</span>`;
        actions = `<button type="button" class="tc-btn tc-btn-view tc-btn" onclick="viewTableOrder(${table.current_order_id}, '${esc(table.order_number || '')}')"><i class="fas fa-eye"></i> View</button>${freeBtn}`;
    } else if (isAdmin) {
        actions = `<button type="button" class="tc-btn tc-btn-edit tc-btn" onclick="openEditModal(${table.table_id}, '${esc(table.table_name)}', ${table.capacity})"><i class="fas fa-pen"></i> Edit</button><button type="button" class="tc-btn tc-btn-delete tc-btn" onclick="openDeleteModal(${table.table_id}, '${esc(table.table_name)}')"><i class="fas fa-trash"></i></button>`;
    } else {
        actions = `<span class="tc-btn tc-btn-avail-static tc-btn" style="width:100%;"><i class="fas fa-circle-check"></i> Available</span>`;
    }
    return `
    <div class="table-card ${table.status}" data-table-id="${table.table_id}">
        <div class="tc-icon ${iconClass}"><i class="fas fa-chair"></i></div>
        <div class="tc-name">${esc(table.table_name)}</div>
        <div class="tc-capacity"><i class="fas fa-users" style="font-size:0.65rem;margin-right:0.3rem;"></i>${table.capacity} seats</div>
        <span class="tc-status ${table.status}"><span class="tc-dot"></span> ${statusLabel}</span>
        ${orderInfo}
        <div class="tc-actions">${actions}</div>
    </div>`;
}

document.addEventListener('visibilitychange', () => { document.hidden ? stopPolling() : startPolling(); });

/* ── Refresh open modal on polling update ─────────────────────── */
let openModalOrderId = null;
function refreshOpenModal(table) {
    if (!openModalOrderId) return;
    if (String(table.current_order_id) !== String(openModalOrderId)) return;
    const modalEl = document.getElementById('tableOrderDetailsModal');
    if (!modalEl || !modalEl.classList.contains('show')) return;
    fetch(`/dinewithdinpos/app/ajax/get_order_details.php?order_id=${openModalOrderId}`)
        .then(r => r.json())
        .then(res => {
            if (!res.success) return;
            populateTodModal(res.data.order || {}, res.data.items || [], res.data.payment || {});
            const contentEl = document.getElementById('todContent');
            const errorEl   = document.getElementById('todError');
            const loaderEl  = document.getElementById('todLoader');
            if (loaderEl)  loaderEl.style.display  = 'none';
            if (errorEl)   errorEl.style.display   = 'none';
            if (contentEl) contentEl.style.display = 'block';
        })
        .catch(() => {});
}
</script>
</body>
</html>