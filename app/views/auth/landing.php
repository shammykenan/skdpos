<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SKD · Fine Dining & Café</title>
    <link rel="icon" type="image/png" href="<?= $baseUrl ?>/assets/images/logo/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,700&family=Plus+Jakarta+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --navy:      #1e2f4e;
            --navy-dk:   #141f35;
            --orange:    #f4a024;
            --orange-dk: #d4841a;
            --orange-lt: #fff4e0;
            --white:     #ffffff;
            --bg:        #fdfcf9;
            --bg2:       #f5f2eb;
            --bg3:       #ede8de;
            --text:      #1a1a2e;
            --text-mid:  #4a5568;
            --text-mute: #94a3b8;
            --border:    rgba(30,47,78,0.08);
            --border-md: rgba(30,47,78,0.13);
            --nav-h:     72px;
            --serif: 'Playfair Display', Georgia, serif;
            --sans:  'Plus Jakarta Sans', system-ui, sans-serif;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body { font-family: var(--sans); background: var(--bg); color: var(--text); -webkit-font-smoothing: antialiased; overflow-x: hidden; }

        /* ─── NAV ─────────────────────────────── */
        .nav {
            position: fixed; top: 0; left: 0; right: 0; height: var(--nav-h);
            z-index: 1000; display: flex; align-items: center;
            justify-content: space-between; padding: 0 2.5rem;
            transition: background .4s, box-shadow .4s;
        }
        .nav.scrolled {
            background: rgba(253,252,249,.95);
            backdrop-filter: blur(18px);
            box-shadow: 0 1px 0 var(--border), 0 4px 20px rgba(30,47,78,.05);
        }
        .nav-brand { display: flex; align-items: center; gap: .8rem; text-decoration: none; }
        .nav-logo {
            width: 44px; height: 44px; border-radius: 50%;
            overflow: hidden; flex-shrink: 0;
            border: 2px solid rgba(244,160,36,.35);
            background: var(--navy);
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 2px 10px rgba(30,47,78,.15);
        }
        .nav-logo img { width: 100%; height: 100%; object-fit: contain; display: block; }
        .nav-logo-fb { font-family: var(--serif); font-size: 1rem; font-weight: 700; color: var(--orange); display: none; }
        .nav-name-wrap { display: flex; flex-direction: column; gap: 1px; }
        .nav-name { font-family: var(--serif); font-size: 1.1rem; color: var(--navy); font-weight: 700; line-height: 1; }
        .nav-sub  { font-size: .57rem; letter-spacing: .14em; text-transform: uppercase; color: var(--orange); font-weight: 500; }
        .nav-links { display: flex; align-items: center; gap: 1.8rem; list-style: none; }
        .nav-links a { font-size: .83rem; font-weight: 400; color: var(--text-mid); text-decoration: none; transition: color .2s; }
        .nav-links a:hover { color: var(--navy); }
        .btn-nav {
    font-family: var(--sans); font-size: .85rem; font-weight: 600;
    color: var(--white); background: var(--navy); border: none;
    padding: .7rem 1.6rem; border-radius: 999px; cursor: pointer;
    transition: background .2s, transform .15s, box-shadow .2s; text-decoration: none;
    display: inline-flex; align-items: center; gap: .45rem;
    box-shadow: 0 2px 10px rgba(30,47,78,.18);
}
.btn-nav:hover { background: var(--orange); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(244,160,36,.35); color: var(--white); }
        .nav-toggle { display: none; background: none; border: 1.5px solid var(--border-md); color: var(--navy); padding: .38rem .65rem; border-radius: 8px; cursor: pointer; font-size: .9rem; }

        /* ─── HERO ────────────────────────────── */
        .hero {
            min-height: 100svh; background: var(--bg);
            padding-top: var(--nav-h); display: flex; align-items: center;
            position: relative; overflow: hidden;
        }
        .hero::before {
            content: ''; position: absolute; top: -15%; right: -10%;
            width: 60%; height: 90%;
            background: radial-gradient(ellipse at 55% 35%, var(--orange-lt) 0%, transparent 68%);
            pointer-events: none;
        }
        .hero-inner {
            position: relative; z-index: 2; width: 100%; max-width: 1200px; margin: 0 auto;
            padding: 5rem 2.5rem; display: grid;
            grid-template-columns: 1fr 400px; gap: 5rem; align-items: center;
        }
        .hero-badge {
            display: inline-flex; align-items: center; gap: .55rem;
            background: var(--orange-lt); border: 1px solid rgba(244,160,36,.3);
            border-radius: 999px; padding: .38rem 1rem .38rem .65rem;
            margin-bottom: 1.75rem;
            opacity: 0; animation: fadeUp .6s .05s ease forwards;
        }
        .hero-badge-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--orange); box-shadow: 0 0 8px rgba(244,160,36,.5); }
        .hero-badge span { font-size: .68rem; font-weight: 500; letter-spacing: .1em; text-transform: uppercase; color: var(--orange-dk); }
        .hero-title {
            font-family: var(--serif); font-size: clamp(3rem, 5.5vw, 5.5rem);
            font-weight: 700; line-height: 1.05; letter-spacing: -.02em; color: var(--navy);
            margin-bottom: 1.5rem; opacity: 0; animation: fadeUp .8s .15s ease forwards;
        }
        .hero-title em { font-style: italic; color: var(--orange); }
        .hero-body {
            font-size: .97rem; font-weight: 300; line-height: 1.85; color: var(--text-mid);
            max-width: 420px; margin-bottom: 2.5rem;
            opacity: 0; animation: fadeUp .8s .28s ease forwards;
        }
        .hero-cta {
            display: flex; gap: .85rem; align-items: center; flex-wrap: wrap;
            margin-bottom: 3rem; opacity: 0; animation: fadeUp .8s .4s ease forwards;
        }
        .btn-primary {
            font-family: var(--sans); font-size: .87rem; font-weight: 600;
            color: var(--white); background: var(--navy); border: none;
            padding: .88rem 2rem; border-radius: 999px; cursor: pointer;
            text-decoration: none; display: inline-flex; align-items: center; gap: .5rem;
            transition: all .22s; box-shadow: 0 4px 18px rgba(30,47,78,.18);
        }
        .btn-primary:hover { background: var(--orange); transform: translateY(-2px); box-shadow: 0 8px 28px rgba(244,160,36,.3); }
        .btn-outline {
            font-family: var(--sans); font-size: .87rem; font-weight: 500;
            color: var(--navy); background: var(--white);
            border: 1.5px solid var(--border-md); padding: .86rem 1.75rem; border-radius: 999px;
            cursor: pointer; text-decoration: none;
            display: inline-flex; align-items: center; gap: .45rem; transition: all .2s;
        }
        .btn-outline:hover { border-color: var(--orange); color: var(--orange); background: var(--orange-lt); }
        .hero-stats {
            display: flex; gap: 2.2rem; padding-top: 1.8rem;
            border-top: 1.5px solid var(--border-md);
            opacity: 0; animation: fadeUp .8s .52s ease forwards;
        }
        .hero-stat-n { font-family: var(--serif); font-size: 2rem; font-weight: 700; color: var(--navy); line-height: 1; }
        .hero-stat-l { font-size: .62rem; letter-spacing: .1em; text-transform: uppercase; color: var(--text-mute); margin-top: .2rem; }

        /* Hero right panel */
        .hero-right { display: flex; flex-direction: column; gap: 1rem; opacity: 0; animation: fadeUp .9s .4s ease forwards; }
        .hours-card {
            background: var(--white); border: 1.5px solid var(--border-md);
            border-radius: 20px; padding: 1.6rem 1.6rem 1.4rem;
            box-shadow: 0 8px 30px rgba(30,47,78,.06);
        }
        .hours-card-hd { display: flex; align-items: center; gap: .7rem; margin-bottom: 1.3rem; }
        .hours-card-ico { width: 38px; height: 38px; border-radius: 10px; background: var(--orange-lt); border: 1px solid rgba(244,160,36,.25); display: flex; align-items: center; justify-content: center; color: var(--orange); font-size: .9rem; }
        .hours-card-title { font-family: var(--serif); font-size: 1rem; color: var(--navy); font-weight: 700; }
        .hours-row { display: flex; justify-content: space-between; align-items: center; padding: .52rem 0; border-bottom: 1px solid var(--border); }
        .hours-row:last-of-type { border-bottom: none; }
        .hours-day { font-size: .79rem; font-weight: 500; color: var(--text-mid); }
        .hours-time { font-size: .79rem; font-weight: 600; color: var(--navy); }
        .open-pill {
            display: inline-flex; align-items: center; gap: .4rem;
            border-radius: 999px; padding: .25rem .75rem; margin-top: 1rem;
            font-size: .63rem; font-weight: 600; letter-spacing: .06em; text-transform: uppercase;
        }
        .open-pill.is-open { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }
        .open-pill.is-closed { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; }
        .open-pill-dot { width: 6px; height: 6px; border-radius: 50%; animation: pulse-dot 2s infinite; }
        .is-open .open-pill-dot { background: #10b981; }
        .is-closed .open-pill-dot { background: #ef4444; }
        @keyframes pulse-dot { 0%,100%{opacity:1} 50%{opacity:.4} }

        .feat-preview { background: var(--white); border: 1.5px solid var(--border-md); border-radius: 20px; overflow: hidden; box-shadow: 0 8px 28px rgba(30,47,78,.06); }
        .feat-preview-hd { background: var(--navy); padding: .75rem 1.15rem; display: flex; align-items: center; justify-content: space-between; }
        .feat-preview-label { font-size: .6rem; letter-spacing: .14em; text-transform: uppercase; color: var(--orange); font-weight: 500; }
        .feat-preview-count { font-size: .68rem; color: rgba(255,255,255,.3); }
        .feat-preview-item { display: flex; align-items: center; gap: .8rem; padding: .8rem 1.15rem; border-bottom: 1px solid var(--border); }
        .feat-preview-item:last-child { border-bottom: none; }
        .feat-preview-ico { width: 34px; height: 34px; border-radius: 8px; background: var(--orange-lt); display: flex; align-items: center; justify-content: center; color: var(--orange); font-size: .8rem; flex-shrink: 0; }
        .feat-preview-name { font-size: .81rem; font-weight: 600; color: var(--navy); }
        .feat-preview-cat  { font-size: .65rem; color: var(--text-mute); }
        .feat-preview-price { font-family: var(--serif); font-size: .86rem; font-weight: 700; color: var(--navy); margin-left: auto; flex-shrink: 0; }

        /* ─── TICKER ──────────────────────────── */
        .ticker { background: var(--navy); padding: .7rem 0; overflow: hidden; }
        .ticker-track { display: flex; width: max-content; animation: ticker 28s linear infinite; }
        .ticker-item { display: flex; align-items: center; gap: 1.2rem; padding: 0 1.4rem; font-size: .65rem; font-weight: 600; letter-spacing: .16em; text-transform: uppercase; color: rgba(255,255,255,.55); white-space: nowrap; }
        .ticker-sep { color: var(--orange); font-size: .5rem; }
        @keyframes ticker { from{transform:translateX(0)} to{transform:translateX(-50%)} }

        /* ─── SHARED LAYOUT ───────────────────── */
        .container { max-width: 1200px; margin: 0 auto; }
        .section { padding: 5.5rem 2.5rem; }
        .section-alt { background: var(--bg2); padding: 5.5rem 2.5rem; }
        .section-navy { background: var(--navy); padding: 5.5rem 2.5rem; }
        .section-stone { background: var(--bg3); padding: 5.5rem 2.5rem; }
        .section-hd { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2.75rem; gap: 2rem; flex-wrap: wrap; }
        .label { display: inline-flex; align-items: center; gap: .55rem; margin-bottom: .75rem; }
        .label-bar { width: 18px; height: 2px; background: var(--orange); border-radius: 2px; }
        .label span { font-size: .62rem; font-weight: 600; letter-spacing: .18em; text-transform: uppercase; color: var(--orange); }
        .h2 { font-family: var(--serif); font-size: clamp(1.85rem, 3.2vw, 2.8rem); font-weight: 700; line-height: 1.1; letter-spacing: -.02em; color: var(--navy); }
        .h2 em { font-style: italic; color: var(--orange); }
        .h2-white { color: var(--white); }
        .h2-white em { color: var(--orange); }
        .subtext { font-size: .87rem; font-weight: 300; color: var(--text-mute); line-height: 1.8; max-width: 420px; }
        .subtext-white { color: rgba(255,255,255,.45); }

        /* ─── FEATURED ────────────────────────── */
        .feat-grid { display: grid; grid-template-columns: 1.1fr 1fr 1fr; gap: 1.15rem; }
        .feat-card { background: var(--white); border-radius: 20px; border: 1.5px solid var(--border); overflow: hidden; transition: transform .28s, box-shadow .28s; position: relative; }
        .feat-card:hover { transform: translateY(-5px); box-shadow: 0 20px 45px rgba(30,47,78,.09); }
        .feat-card:first-child { grid-row: span 2; }
        .feat-img, .feat-ph { width: 100%; height: 180px; object-fit: cover; display: block; }
        .feat-card:first-child .feat-img, .feat-card:first-child .feat-ph { height: 265px; }
        .feat-ph { background: linear-gradient(145deg, var(--bg2), var(--bg3)); display: flex; align-items: center; justify-content: center; color: var(--text-mute); font-size: 2.2rem; }
        .feat-ph.dim, .feat-img.dim { filter: grayscale(45%) opacity(.72); }
        .feat-body { padding: 1.15rem 1.25rem 1.35rem; }
        .feat-cat { font-size: .59rem; letter-spacing: .14em; text-transform: uppercase; color: var(--orange); margin-bottom: .28rem; }
        .feat-name { font-family: var(--serif); font-size: 1.08rem; font-weight: 700; color: var(--navy); line-height: 1.25; margin-bottom: .32rem; }
        .feat-desc { font-size: .76rem; color: var(--text-mute); line-height: 1.6; margin-bottom: .8rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .feat-ft { display: flex; align-items: center; justify-content: space-between; gap: .5rem; flex-wrap: wrap; }
        .feat-price { font-family: var(--serif); font-size: 1.08rem; font-weight: 700; color: var(--navy); }
        .feat-badge { font-size: .59rem; font-weight: 600; letter-spacing: .06em; background: var(--orange-lt); color: var(--orange-dk); padding: .2rem .6rem; border-radius: 999px; }

        /* stock ribbon top-left on card */
        .stock-ribbon { position: absolute; top: .7rem; left: .7rem; font-size: .58rem; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; padding: .22rem .65rem; border-radius: 999px; }
        .stock-ribbon.oos { background: rgba(239,68,68,.1); color: #dc2626; border: 1px solid rgba(239,68,68,.2); }
        .stock-ribbon.na  { background: rgba(100,116,139,.1); color: #64748b; border: 1px solid rgba(100,116,139,.22); }

        /* inline stock tag (menu cards + feat footer) */
        .stock-tag { font-size: .59rem; font-weight: 600; letter-spacing: .04em; text-transform: uppercase; padding: .18rem .55rem; border-radius: 999px; }
        .stock-tag.oos { background: rgba(239,68,68,.08); color: #dc2626; border: 1px solid rgba(239,68,68,.18); }
        .stock-tag.na  { background: rgba(100,116,139,.08); color: #64748b; border: 1px solid rgba(100,116,139,.18); }

        /* ─── MENU ────────────────────────────── */
        .menu-nav { display: flex; gap: .45rem; flex-wrap: nowrap; overflow-x: auto; -webkit-overflow-scrolling: touch; scrollbar-width: none; padding-bottom: 3px; margin-bottom: 2rem; }
        .menu-nav::-webkit-scrollbar { display: none; }
        .menu-tab { font-family: var(--sans); font-size: .79rem; font-weight: 500; color: var(--text-mute); background: var(--white); border: 1.5px solid var(--border-md); padding: .5rem 1.1rem; border-radius: 999px; cursor: pointer; white-space: nowrap; transition: all .18s; flex-shrink: 0; }
        .menu-tab:hover { color: var(--navy); border-color: rgba(30,47,78,.22); }
        .menu-tab.active { background: var(--navy); color: var(--white); border-color: var(--navy); }
        .menu-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(262px, 1fr)); gap: 1rem; }
        .menu-card { background: var(--white); border: 1.5px solid var(--border); border-radius: 14px; display: flex; gap: .85rem; padding: .9rem 1rem; align-items: flex-start; transition: box-shadow .22s, transform .22s; }
        .menu-card:hover { box-shadow: 0 8px 24px rgba(30,47,78,.07); transform: translateY(-2px); }
        .menu-thumb, .menu-thumb-ph { width: 66px; height: 66px; border-radius: 10px; flex-shrink: 0; object-fit: cover; }
        .menu-thumb-ph { background: var(--bg2); display: flex; align-items: center; justify-content: center; color: var(--text-mute); font-size: 1.15rem; }
        .menu-thumb.dim { filter: grayscale(45%) opacity(.72); }
        .menu-info { flex: 1; min-width: 0; }
        .menu-name { font-family: var(--serif); font-size: .93rem; font-weight: 700; color: var(--navy); margin-bottom: .16rem; line-height: 1.3; }
        .menu-desc { font-size: .71rem; color: var(--text-mute); line-height: 1.5; margin-bottom: .42rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .menu-ft { display: flex; align-items: center; justify-content: space-between; gap: .4rem; flex-wrap: wrap; }
        .menu-price { font-family: var(--serif); font-size: .9rem; font-weight: 700; color: var(--navy); }
        .menu-price.dim { color: var(--text-mute); }
        .menu-vars { font-size: .59rem; letter-spacing: .05em; color: var(--orange); background: var(--orange-lt); padding: .15rem .5rem; border-radius: 999px; }
        .load-wrap { text-align: center; margin-top: 2rem; }
        .btn-load { font-family: var(--sans); font-size: .82rem; font-weight: 500; color: var(--navy); background: var(--white); border: 1.5px solid var(--border-md); padding: .7rem 2rem; border-radius: 999px; cursor: pointer; display: inline-flex; align-items: center; gap: .5rem; transition: all .2s; }
        .btn-load:hover { background: var(--bg2); }
        .btn-load:disabled { opacity: .35; cursor: default; pointer-events: none; }
        .load-info { font-size: .73rem; color: var(--text-mute); margin-top: .6rem; }

        /* ─── ABOUT ───────────────────────────── */
        .about-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6rem; align-items: center; }
        .about-visual { position: relative; }
        /* illustrated placeholder */
        .about-ph {
            width: 100%; aspect-ratio: 4/5; border-radius: 26px;
            background: linear-gradient(145deg, var(--bg2) 0%, var(--bg3) 100%);
            border: 1.5px solid var(--border-md);
            display: flex; flex-direction: column;
            align-items: center; justify-content: center; gap: 1rem;
            position: relative; overflow: hidden;
        }
        .about-ph-icon { font-size: 4.5rem; color: var(--orange); opacity: .3; position: relative; z-index: 1; }
        .about-ph-text { font-family: var(--serif); font-size: 1.1rem; font-style: italic; color: var(--text-mute); position: relative; z-index: 1; }
        /* concentric ring decoration */
        .about-ph::before { content: ''; position: absolute; width: 260px; height: 260px; border-radius: 50%; border: 1.5px solid rgba(244,160,36,.12); top: 50%; left: 50%; transform: translate(-50%,-50%); }
        .about-ph::after  { content: ''; position: absolute; width: 380px; height: 380px; border-radius: 50%; border: 1px solid rgba(244,160,36,.07); top: 50%; left: 50%; transform: translate(-50%,-50%); }
        .about-chip { position: absolute; bottom: -1.2rem; right: -1.5rem; background: var(--white); border: 1.5px solid var(--border-md); border-radius: 16px; padding: 1rem 1.35rem; box-shadow: 0 12px 32px rgba(30,47,78,.1); display: flex; align-items: center; gap: .85rem; }
        .about-chip-ico { width: 38px; height: 38px; border-radius: 10px; background: var(--orange); color: var(--white); display: flex; align-items: center; justify-content: center; font-size: .88rem; }
        .about-chip-n { font-family: var(--serif); font-size: 1.5rem; color: var(--navy); line-height: 1; }
        .about-chip-l { font-size: .6rem; letter-spacing: .1em; text-transform: uppercase; color: var(--text-mute); }
        .pillars { display: flex; flex-direction: column; gap: 1.35rem; margin-top: 2.2rem; }
        .pillar { display: flex; gap: 1rem; align-items: flex-start; }
        .pillar-ico { width: 40px; height: 40px; border-radius: 10px; flex-shrink: 0; background: var(--bg2); border: 1.5px solid var(--border-md); color: var(--navy); display: flex; align-items: center; justify-content: center; font-size: .85rem; }
        .pillar-t { font-family: var(--serif); font-size: .97rem; font-weight: 700; color: var(--navy); margin-bottom: .14rem; }
        .pillar-d { font-size: .79rem; color: var(--text-mute); line-height: 1.7; }

        /* ─── HOURS SECTION ───────────────────── */
        .hours-inner { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1.2fr; gap: 5rem; align-items: center; }
        .hours-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; }
        .hour-tile { background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.09); border-radius: 16px; padding: 1rem 1.15rem; transition: border-color .2s; }
        .hour-tile:hover { border-color: rgba(244,160,36,.3); }
        .hour-tile-day { font-size: .63rem; font-weight: 600; letter-spacing: .12em; text-transform: uppercase; color: rgba(255,255,255,.38); margin-bottom: .38rem; }
        .hour-tile-time { font-family: var(--serif); font-size: 1rem; color: var(--white); }
        .hour-tile.closed .hour-tile-time { color: rgba(255,255,255,.28); font-style: italic; font-size: .88rem; }
        .hour-tile.today { border-color: rgba(244,160,36,.4); background: rgba(244,160,36,.08); }
        .hour-tile.today .hour-tile-day { color: var(--orange); }
        .hour-tile.today .hour-tile-time { color: var(--orange); }
        .live-row { display: flex; align-items: center; gap: .5rem; margin-top: 1.25rem; font-size: .78rem; color: rgba(255,255,255,.5); }
        .live-dot { width: 8px; height: 8px; border-radius: 50%; background: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,.2); animation: pulse-dot 2s infinite; }
        .live-dot.closed-dot { background: #f59e0b; box-shadow: 0 0 0 3px rgba(245,158,11,.2); }

        /* ─── QUOTE ───────────────────────────── */
        .quote-t { font-family: var(--serif); font-size: clamp(1.45rem, 2.8vw, 2.25rem); font-weight: 400; font-style: italic; color: var(--navy); line-height: 1.5; max-width: 640px; margin: 0 auto 1.25rem; letter-spacing: -.015em; text-align: center; }
        .quote-t em { font-style: normal; color: var(--orange); }
        .quote-mark { font-family: var(--serif); font-size: 8rem; line-height: .5; color: var(--orange); opacity: .22; display: block; margin-bottom: 2rem; text-align: center; }
        .quote-by { font-size: .67rem; letter-spacing: .18em; text-transform: uppercase; color: var(--text-mute); text-align: center; }

        /* ─── FOOTER ──────────────────────────── */
        footer { background: var(--navy-dk); color: rgba(255,255,255,.35); padding: 4rem 2.5rem 2.5rem; }
        .footer-inner { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 3rem; padding-bottom: 2.5rem; border-bottom: 1px solid rgba(255,255,255,.06); margin-bottom: 2rem; }
        .footer-logo-row { display: flex; align-items: center; gap: .75rem; margin-bottom: .85rem; }
        .footer-logo { width: 38px; height: 38px; border-radius: 50%; border: 2px solid rgba(244,160,36,.28); background: rgba(255,255,255,.08); display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .footer-logo img { width: 100%; height: 100%; object-fit: contain; }
        .footer-name { font-family: var(--serif); font-size: 1.15rem; color: var(--white); }
        .footer-sub-txt { font-size: .57rem; letter-spacing: .16em; text-transform: uppercase; color: var(--orange); margin-bottom: .85rem; }
        .footer-desc { font-size: .79rem; line-height: 1.75; color: rgba(255,255,255,.3); }
        .footer-col-t { font-size: .59rem; font-weight: 600; letter-spacing: .18em; text-transform: uppercase; color: var(--orange); margin-bottom: 1.1rem; }
        .footer-links { list-style: none; display: flex; flex-direction: column; gap: .55rem; }
        .footer-links a { font-size: .81rem; color: rgba(255,255,255,.33); text-decoration: none; transition: color .2s; }
        .footer-links a:hover { color: var(--orange); }
        .footer-btm { max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; font-size: .73rem; flex-wrap: wrap; gap: .5rem; }
        /* ─── FOOTER LOCATION ─────────────────── */
.footer-location-card {
    display: flex; gap: .85rem; align-items: flex-start;
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 14px; padding: .9rem 1rem;
    margin-bottom: .5rem; transition: border-color .2s;
}
.footer-location-card:hover { border-color: rgba(244,160,36,.25); }
.footer-location-map {
    width: 40px; height: 40px; border-radius: 10px; flex-shrink: 0;
    background: rgba(244,160,36,.12); border: 1px solid rgba(244,160,36,.22);
    display: flex; align-items: center; justify-content: center;
    color: var(--orange); font-size: .95rem;
}
.footer-location-name { font-size: .8rem; font-weight: 600; color: rgba(255,255,255,.7); margin-bottom: .2rem; }
.footer-location-addr { font-size: .74rem; color: rgba(255,255,255,.3); line-height: 1.6; margin-bottom: .5rem; }
.footer-location-link {
    font-size: .67rem; font-weight: 600; letter-spacing: .06em;
    color: var(--orange); text-decoration: none;
    display: inline-flex; align-items: center; gap: .3rem;
    transition: opacity .2s;
}
.footer-location-link:hover { opacity: .75; }

/* ─── FOOTER BOTTOM ───────────────────── */
.footer-btm {
    max-width: 1200px; margin: 0 auto;
    display: flex; justify-content: space-between; align-items: center;
    font-size: .74rem; flex-wrap: wrap; gap: .75rem;
    padding-top: .25rem;
}
.footer-btm-copy { color: rgba(255,255,255,.22); }
.footer-btm-tag  { color: rgba(255,255,255,.3); font-size: .72rem; }
        /* ─── MODAL ───────────────────────────── */
        .overlay { position: fixed; inset: 0; z-index: 9000; background: rgba(14,20,34,.65); backdrop-filter: blur(12px); display: flex; align-items: center; justify-content: center; padding: 1.5rem; opacity: 0; pointer-events: none; transition: opacity .3s; }
        .overlay.open { opacity: 1; pointer-events: all; }
        .modal { background: var(--white); border-radius: 24px; width: 100%; max-width: 400px; overflow: hidden; transform: translateY(20px) scale(.97); transition: transform .35s cubic-bezier(.22,1,.36,1); box-shadow: 0 40px 80px rgba(14,20,34,.2); }
        .overlay.open .modal { transform: translateY(0) scale(1); }
        .modal-band { background: var(--navy); padding: 1.7rem 1.7rem 1.45rem; position: relative; overflow: hidden; }
        .modal-band::after { content: ''; position: absolute; right: -15px; top: -15px; width: 110px; height: 110px; border-radius: 50%; background: rgba(244,160,36,.1); pointer-events: none; }
        .modal-close { position: absolute; top: .9rem; right: .9rem; z-index: 2; background: rgba(255,255,255,.1); border: none; width: 28px; height: 28px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,.55); font-size: .7rem; transition: background .2s; }
        .modal-close:hover { background: rgba(244,160,36,.3); color: var(--orange); }
        .modal-logo-row { display: flex; align-items: center; gap: .7rem; margin-bottom: 1.1rem; position: relative; z-index: 1; }
        .modal-logo { width: 36px; height: 36px; border-radius: 50%; background: rgba(255,255,255,.1); border: 1.5px solid rgba(255,255,255,.15); display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .modal-logo img { width: 100%; height: 100%; object-fit: contain; }
        .modal-brand { font-family: var(--serif); font-size: .93rem; color: var(--white); font-weight: 700; }
        .modal-brand-s { font-size: .54rem; letter-spacing: .14em; text-transform: uppercase; color: var(--orange); }
        .modal-h { font-family: var(--serif); font-size: 1.75rem; color: var(--white); font-weight: 700; margin-bottom: .15rem; position: relative; z-index: 1; }
        .modal-h em { font-style: italic; color: var(--orange); }
        .modal-s { font-size: .77rem; color: rgba(255,255,255,.4); position: relative; z-index: 1; }
        .modal-body { padding: 1.55rem 1.7rem 1.7rem; }
        .alert { border-radius: 8px; padding: .6rem .85rem; font-size: .77rem; display: flex; gap: .5rem; align-items: flex-start; margin-bottom: 1rem; }
        .alert-err { background: rgba(220,60,60,.07); border: 1px solid rgba(220,60,60,.18); color: #dc2626; }
        .alert-ok  { background: rgba(39,174,96,.07); border: 1px solid rgba(39,174,96,.18); color: #16a34a; }
        .field { margin-bottom: .9rem; }
        .field label { display: block; font-size: .66rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; color: var(--text-mute); margin-bottom: .35rem; }
        .input-row { position: relative; }
        .input-ico { position: absolute; left: .9rem; top: 50%; transform: translateY(-50%); color: var(--orange); font-size: .7rem; pointer-events: none; }
        .field input { width: 100%; background: var(--bg); border: 1.5px solid var(--border-md); border-radius: 12px; padding: .78rem 1rem .78rem 2.3rem; font-family: var(--sans); font-size: .88rem; color: var(--text); outline: none; transition: border-color .2s, box-shadow .2s; }
        .field input::placeholder { color: rgba(148,163,184,.5); }
        .field input:focus { border-color: var(--navy); background: var(--white); box-shadow: 0 0 0 3px rgba(30,47,78,.07); }
        .field input:-webkit-autofill { -webkit-box-shadow: 0 0 0 999px var(--bg) inset !important; -webkit-text-fill-color: var(--text) !important; }
        .pass-eye { position: absolute; right: .8rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-mute); font-size: .75rem; padding: .2rem; transition: color .2s; }
        .pass-eye:hover { color: var(--navy); }
        .forgot { text-align: right; margin-bottom: 1.1rem; }
        .forgot a { font-size: .74rem; color: var(--text-mute); text-decoration: none; transition: color .2s; }
        .forgot a:hover { color: var(--orange); }
        .btn-submit { width: 100%; padding: .88rem; background: var(--navy); color: var(--white); border: none; border-radius: 999px; font-family: var(--sans); font-size: .85rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: .5rem; transition: all .2s; }
        .btn-submit:hover { background: var(--orange); transform: translateY(-1px); }
        .modal-note { text-align: center; margin-top: 1.2rem; padding-top: 1rem; border-top: 1px solid var(--border); font-size: .74rem; color: var(--text-mute); }

        /* ─── DRAWER ──────────────────────────── */
        .mob-bg { position: fixed; inset: 0; background: rgba(14,20,34,.3); z-index: 1090; display: none; }
        .mob-bg.open { display: block; }
        .drawer { position: fixed; top: 0; right: 0; bottom: 0; width: 265px; background: var(--white); z-index: 1100; transform: translateX(100%); transition: transform .28s ease; padding: 4.5rem 1.5rem 2rem; box-shadow: -6px 0 28px rgba(30,47,78,.1); border-left: 1.5px solid var(--border); }
        .drawer.open { transform: translateX(0); }
        .drawer-links { list-style: none; display: flex; flex-direction: column; }
        .drawer-links a { display: block; padding: .8rem 0; font-size: .87rem; color: var(--text-mid); text-decoration: none; border-bottom: 1px solid var(--border); transition: color .2s; }
        .drawer-links a:hover { color: var(--orange); }
        .drawer-close { position: absolute; top: 1rem; right: 1rem; background: none; border: 1.5px solid var(--border-md); color: var(--navy); width: 32px; height: 32px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: .8rem; }

        /* ─── EMPTY ───────────────────────────── */
        .empty { text-align: center; padding: 3.5rem 2rem; color: var(--text-mute); }
        .empty i { font-size: 2rem; color: var(--orange); opacity: .3; display: block; margin-bottom: .75rem; }
        .empty p { font-size: .84rem; }

        /* ─── ANIMATIONS ──────────────────────── */
        @keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
        .reveal { opacity: 0; transform: translateY(15px); transition: opacity .55s ease, transform .55s ease; }
        .reveal.in { opacity: 1; transform: translateY(0); }

        /* ─── RESPONSIVE ──────────────────────── */
        @media(max-width:1100px){
            .hero-inner{grid-template-columns:1fr; gap:2rem;}
            .hero-right{display:none;}
            .feat-grid{grid-template-columns:1fr 1fr;}
            .feat-card:first-child{grid-row:auto;}
            .feat-card:first-child .feat-img,.feat-card:first-child .feat-ph{height:180px;}
            .about-grid{grid-template-columns:1fr; gap:3rem;}
            .hours-inner{grid-template-columns:1fr; gap:3rem;}
            .about-chip{right:0;}
        }
        @media(max-width:768px){
            .nav{padding:0 1.25rem;}
            .nav-links{display:none;}
            .nav-toggle{display:flex;}
            .section,.section-alt,.section-navy,.section-stone{padding:4rem 1.5rem;}
            .feat-grid{grid-template-columns:1fr;}
            .hero-inner{padding:3.5rem 1.5rem 4rem;}
            .footer-inner{grid-template-columns:1fr 1fr; gap:2rem;}
        }
        @media(max-width:540px){
            .footer-inner{grid-template-columns:1fr;}
            .hero-cta{flex-direction:column; align-items:stretch;}
            .btn-primary,.btn-outline{justify-content:center;}
            .hours-grid{grid-template-columns:1fr;}
        }
    </style>
</head>
<body>

<div class="mob-bg" id="mobBg"></div>
<div class="drawer" id="drawer">
    <button class="drawer-close" id="drawerClose"><i class="fas fa-times"></i></button>
    <ul class="drawer-links">
        <li><a href="#featured">Featured</a></li>
        <li><a href="#menu">Menu</a></li>
        <li><a href="#about">About</a></li>
        <li><a href="#hours">Hours</a></li>
        <li><a href="#contact">Contact</a></li>
        <li><a href="#" onclick="openModal(event)">Staff Login</a></li>
    </ul>
</div>

<!-- NAV -->
<nav class="nav" id="mainNav">
    <a href="#" class="nav-brand">
        <div class="nav-logo">
            <img src="<?= $baseUrl ?>/assets/images/logo/logo.png" alt="SKD"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
            <span class="nav-logo-fb">S</span>
        </div>
        <div class="nav-name-wrap">
            <span class="nav-name">SKD</span>
            <span class="nav-sub">Fine Dining · Café</span>
        </div>
    </a>
    <ul class="nav-links">
        <li><a href="#featured">Featured</a></li>
        <li><a href="#menu">Menu</a></li>
        <li><a href="#about">About</a></li>
        <li><a href="#hours">Hours</a></li>
        <li><a href="#contact">Contact</a></li>
    </ul>
    <div style="display:flex;align-items:center;gap:.65rem">
        <a href="#" class="btn-nav" onclick="openModal(event)">
            <i class="fas fa-arrow-right-to-bracket"></i> Staff Login
        </a>
        <button class="nav-toggle" id="navToggle"><i class="fas fa-bars"></i></button>
    </div>
</nav>

<!-- ══ HERO ══ -->
<section class="hero" id="home">
    <div class="hero-inner">
        <div>
            <div class="hero-badge">
                <span class="hero-badge-dot"></span>
                <span>Est. with passion · Served with care</span>
            </div>
            <h1 class="hero-title">Where every<br>sip is <em>crafted</em><br>with love</h1>
            <p class="hero-body">From single-origin brews to kitchen-fresh bites — every item on our menu is a testament to quality ingredients and the artisans behind them.</p>
            <div class="hero-cta">
                <a href="#menu" class="btn-primary"><i class="fas fa-book-open"></i> Browse Menu</a>
                <a href="#about" class="btn-outline">Our Story <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="hero-stats">
                <div><div class="hero-stat-n"><?= count($categories) ?>+</div><div class="hero-stat-l">Categories</div></div>
                <div><div class="hero-stat-n"><?= count($menu_items) ?>+</div><div class="hero-stat-l">Menu Items</div></div>
                <div><div class="hero-stat-n">100%</div><div class="hero-stat-l">Fresh Daily</div></div>
            </div>
        </div>
        <div class="hero-right">
            <div class="hours-card">
                <div class="hours-card-hd">
                    <div class="hours-card-ico"><i class="fas fa-clock"></i></div>
                    <div class="hours-card-title">Opening Hours</div>
                </div>
                <div class="hours-row"><span class="hours-day">Mon – Fri</span><span class="hours-time">9:00 AM – 11:00 PM</span></div>
                <div class="hours-row"><span class="hours-day">Saturday</span><span class="hours-time">9:00 AM – 11:00 PM</span></div>
                <div class="hours-row"><span class="hours-day">Sunday</span><span class="hours-time">10:00 AM – 10:00 PM</span></div>
                <span class="open-pill is-open" id="heroPill">
                    <span class="open-pill-dot"></span>
                    <span id="heroPillText">Open Now</span>
                </span>
            </div>
            <?php if (!empty($featured_items)): ?>
            <div class="feat-preview">
                <div class="feat-preview-hd">
                    <span class="feat-preview-label">Today's picks</span>
                    <span class="feat-preview-count"><?= count($featured_items) ?> featured</span>
                </div>
                <?php foreach (array_slice($featured_items, 0, 3) as $fp): ?>
                <div class="feat-preview-item">
                    <div class="feat-preview-ico"><i class="fas fa-utensils"></i></div>
                    <div>
                        <div class="feat-preview-name"><?= htmlspecialchars($fp['item_name']) ?></div>
                        <div class="feat-preview-cat"><?= htmlspecialchars($fp['category_name']) ?></div>
                    </div>
                    <div class="feat-preview-price"><?= htmlspecialchars($fp['display_price']) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- TICKER -->
<div class="ticker">
    <div class="ticker-track">
        <?php
        $ticks = ['Fresh Brews','Daily Specials','Artisan Bakes','Seasonal Drinks','House Blends','Farm to Table','Signature Dishes','Premium Beans'];
        foreach (array_merge($ticks, $ticks) as $t):
        ?><span class="ticker-item"><?= htmlspecialchars($t) ?><span class="ticker-sep">◆</span></span><?php endforeach; ?>
    </div>
</div>

<!-- ══ FEATURED ══ -->
<div class="section" id="featured">
    <div class="container">
        <div class="section-hd">
            <div>
                <div class="label reveal"><span class="label-bar"></span><span>Curated Selection</span></div>
                <h2 class="h2 reveal">Our <em>signature</em> favourites</h2>
            </div>
            <p class="subtext reveal" style="text-align:right">Hand-picked from our kitchen — the items our guests keep coming back for.</p>
        </div>
        <?php if (!empty($featured_items)): ?>
        <div class="feat-grid">
            <?php foreach ($featured_items as $fi):
                $avail = !isset($fi['is_available']) || (bool)$fi['is_available'];
                $stock = !isset($fi['in_stock'])     || (bool)$fi['in_stock'];
                $sClass = !$avail ? 'na' : (!$stock ? 'oos' : '');
                $sLabel = !$avail ? 'Not Available' : (!$stock ? 'Out of Stock' : '');
            ?>
            <div class="feat-card reveal">
                <?php if ($sLabel): ?><span class="stock-ribbon <?= $sClass ?>"><?= $sLabel ?></span><?php endif; ?>
                <?php if (!empty($fi['image_url'])): ?>
                    <img src="<?= $baseUrl . '/' . htmlspecialchars($fi['image_url']) ?>"
                         alt="<?= htmlspecialchars($fi['item_name']) ?>"
                         class="feat-img<?= $sClass ? ' dim' : '' ?>"
                         onerror="this.outerHTML='<div class=\'feat-ph<?= $sClass ? " dim" : "" ?>\'><i class=\'fas fa-utensils\'></i></div>'">
                <?php else: ?>
                    <div class="feat-ph<?= $sClass ? ' dim' : '' ?>"><i class="fas fa-utensils"></i></div>
                <?php endif; ?>
                <div class="feat-body">
                    <div class="feat-cat"><?= htmlspecialchars($fi['category_name']) ?></div>
                    <div class="feat-name"><?= htmlspecialchars($fi['item_name']) ?></div>
                    <?php if (!empty($fi['description'])): ?>
                        <div class="feat-desc"><?= htmlspecialchars($fi['description']) ?></div>
                    <?php endif; ?>
                    <div class="feat-ft">
                        <?php if ($sClass): ?>
                            <span class="feat-price" style="color:var(--text-mute);font-size:.9rem"><?= htmlspecialchars($fi['display_price']) ?></span>
                            <span class="stock-tag <?= $sClass ?>"><?= $sLabel ?></span>
                        <?php else: ?>
                            <div class="feat-price"><?= htmlspecialchars($fi['display_price']) ?></div>
                            <span class="feat-badge">Featured</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty"><i class="fas fa-bowl-food"></i><p>Our kitchen is being set up — check back soon!</p></div>
        <?php endif; ?>
    </div>
</div>

<!-- ══ MENU ══ -->
<div class="section-alt" id="menu">
    <div class="container">
        <div class="section-hd">
            <div>
                <div class="label reveal"><span class="label-bar"></span><span>Full Menu</span></div>
                <h2 class="h2 reveal">Everything we <em>serve</em></h2>
            </div>
            <p class="subtext reveal" style="text-align:right">Browse by category, 8 items at a time.</p>
        </div>
        <?php if (!empty($menu_by_category)): ?>
        <script>
        const MENU_DATA = <?php
            $js = [];
            foreach ($menu_by_category as $cat => $items) {
                $js[$cat] = array_map(function($it) use ($baseUrl) {
                    $avail  = !isset($it['is_available']) || (bool)$it['is_available'];
                    $stock  = !isset($it['in_stock'])     || (bool)$it['in_stock'];
                    $status = !$avail ? 'na' : (!$stock ? 'oos' : '');
                    return [
                        'name'   => $it['item_name'],
                        'desc'   => $it['description'] ?? '',
                        'price'  => $it['display_price'],
                        'image'  => !empty($it['image_url']) ? $baseUrl.'/'.$it['image_url'] : '',
                        'vars'   => ($it['has_variants'] ?? false) ? count($it['variants'] ?? []) : 0,
                        'status' => $status,
                    ];
                }, $items);
            }
            echo json_encode($js, JSON_HEX_TAG|JSON_HEX_AMP|JSON_UNESCAPED_UNICODE);
        ?>;
        const PER = 8;
        </script>
        <div class="menu-nav" id="menuNav"></div>
        <div class="menu-grid" id="menuGrid"></div>
        <div class="load-wrap">
            <button class="btn-load" id="btnMore"><i class="fas fa-plus"></i> Show more items</button>
            <div class="load-info" id="loadInfo"></div>
        </div>
        <?php else: ?>
        <div class="empty"><i class="fas fa-bowl-food"></i><p>Menu coming soon.</p></div>
        <?php endif; ?>
    </div>
</div>

<!-- ══ ABOUT ══ -->
<div class="section" id="about">
    <div class="container">
        <div class="about-grid">
            <div class="about-visual reveal">
                <div class="about-ph">
                    <i class="fas fa-mug-hot about-ph-icon"></i>
                    <span class="about-ph-text">Our Story</span>
                </div>
                <div class="about-chip">
                    <div class="about-chip-ico"><i class="fas fa-leaf"></i></div>
                    <div>
                        <div class="about-chip-n">100%</div>
                        <div class="about-chip-l">Fresh Ingredients</div>
                    </div>
                </div>
            </div>
            <div>
                <div class="label reveal"><span class="label-bar"></span><span>Our Philosophy</span></div>
                <h2 class="h2 reveal">Crafted with<br>purpose &amp; <em>heart</em></h2>
                <p class="subtext reveal" style="max-width:100%">Every dish and drink carries the story of the people who made it — from the farmers who grew the ingredients to the hands that prepared your cup.</p>
                <div class="pillars">
                    <div class="pillar reveal">
                        <div class="pillar-ico"><i class="fas fa-leaf"></i></div>
                        <div><div class="pillar-t">Fresh Ingredients</div><div class="pillar-d">We source locally and seasonally, choosing every ingredient with intention — for flavour, community, and sustainability.</div></div>
                    </div>
                    <div class="pillar reveal">
                        <div class="pillar-ico"><i class="fas fa-fire"></i></div>
                        <div><div class="pillar-t">Made to Order</div><div class="pillar-d">Nothing sits under a lamp here. Your order is prepared fresh the moment it's placed — every time, without exception.</div></div>
                    </div>
                    <div class="pillar reveal">
                        <div class="pillar-ico"><i class="fas fa-heart"></i></div>
                        <div><div class="pillar-t">Served with Warmth</div><div class="pillar-d">Great food is only half the experience. We believe hospitality is an art, and every guest deserves to feel at home.</div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ══ HOURS ══ -->
<div class="section-navy" id="hours">
    <div class="hours-inner">
        <div>
            <div class="label reveal"><span class="label-bar"></span><span>Visit Us</span></div>
            <h2 class="h2 h2-white reveal">We're open <em>almost</em><br>every day</h2>
            <p class="subtext subtext-white reveal">Drop by anytime within our hours — we'd love to have you. Grab a cup, stay a while.</p>
            <div class="live-row" id="liveRow">
                <span class="live-dot" id="liveDot"></span>
                <span id="liveText">Checking hours…</span>
            </div>
        </div>
        <div class="hours-grid" id="hoursGrid"></div>
    </div>
</div>

<!-- ══ QUOTE ══ -->
<div class="section-stone" style="text-align:center; padding:6rem 2.5rem">
    <span class="quote-mark">"</span>
    <p class="quote-t">Good food is the foundation of genuine happiness — and a great cup is where every <em>morning begins.</em></p>
    <div class="quote-by">— The SKD Kitchen Team</div>
</div>

<!-- ══ FOOTER ══ -->
<footer id="contact">
    <div class="footer-inner">
        <div>
            <div class="footer-logo-row">
                <div class="footer-logo">
                    <img src="<?= $baseUrl ?>/assets/images/logo/logo.png" alt="SKD" onerror="this.style.display='none'">
                </div>
                <div>
                    <div class="footer-name">SKD Software Solutions</div>
                    <div class="footer-sub-txt">Fine Dining · Café Experience</div>
                </div>
            </div>
            <div class="footer-desc">A modern point-of-sale experience built for cafés, restaurants, and milk tea shops — designed to delight both operators and guests.</div>
        </div>
        <div>
            <div class="footer-col-t">Explore</div>
            <ul class="footer-links">
                <li><a href="#featured">Featured Items</a></li>
                <li><a href="#menu">Full Menu</a></li>
                <li><a href="#about">About Us</a></li>
                <li><a href="#hours">Opening Hours</a></li>
            </ul>
        </div>
        <div>
            <div class="footer-col-t">Find Us</div>
            <div class="footer-location-card">
                <div class="footer-location-map">
                    <i class="fas fa-map-pin"></i>
                </div>
                <div class="footer-location-info">
                    <div class="footer-location-name">SKD Fine Dining & Café</div>
                    <div class="footer-location-addr">123 Placeholder Street<br>Marilao, Bulacan, PH</div>
                    <a href="https://maps.google.com" target="_blank" class="footer-location-link">
                        Get Directions <i class="fas fa-arrow-up-right-from-square"></i>
                    </a>
                </div>
            </div>
            <div class="footer-col-t" style="margin-top:1.5rem">Staff Access</div>
            <ul class="footer-links">
                <li><a href="#" onclick="openModal(event)">Staff Login</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-btm">
        <span class="footer-btm-copy">&copy; <?= date('Y') ?> SKD Software Solutions. All rights reserved.</span>
        <span class="footer-btm-tag">Served with <span style="color:var(--orange)">♥</span> in Marilao</span>
    </div>
</footer>

<!-- ══ LOGIN MODAL ══ -->
<div class="overlay" id="loginOverlay" role="dialog" aria-modal="true" aria-label="Staff Login">
    <div class="modal">
        <div class="modal-band">
            <button class="modal-close" id="modalClose" aria-label="Close"><i class="fas fa-times"></i></button>
            <div class="modal-logo-row">
                <div class="modal-logo">
                    <img src="<?= $baseUrl ?>/assets/images/logo/logo.png" alt="SKD" onerror="this.style.display='none'">
                </div>
                <div>
                    <div class="modal-brand">SKD Software Solutions</div>
                    <div class="modal-brand-s">Staff Portal</div>
                </div>
            </div>
            <div class="modal-h">Welcome <em>back</em></div>
            <div class="modal-s">Sign in to manage today's service</div>
        </div>
        <div class="modal-body">
            <?php if (!empty($error)): ?><div class="alert alert-err"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>
            <?php if (!empty($success)): ?><div class="alert alert-ok"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div><?php endif; ?>
            <form action="<?= $baseUrl ?>/index.php?page=login" method="POST" autocomplete="on">
                <input type="hidden" name="from_landing" value="1">
                <div class="field">
                    <label for="u_un">Username</label>
                    <div class="input-row">
                        <i class="fas fa-user input-ico"></i>
                        <input type="text" id="u_un" name="username" placeholder="Your username"
                               value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
                               autocomplete="username" required>
                    </div>
                </div>
                <div class="field">
                    <label for="u_pw">Password</label>
                    <div class="input-row">
                        <i class="fas fa-lock input-ico"></i>
                        <input type="password" id="u_pw" name="password" placeholder="••••••••" autocomplete="current-password" required>
                        <button type="button" class="pass-eye" onclick="togglePw()"><i class="far fa-eye" id="pwIcon"></i></button>
                    </div>
                </div>
                <div class="forgot"><a href="<?= $baseUrl ?>/index.php?page=forgot_password">Forgot password?</a></div>
                <button type="submit" name="login" class="btn-submit"><i class="fas fa-arrow-right-to-bracket"></i> Sign In</button>
            </form>
            <div class="modal-note">Serving great meals, every day.</div>
        </div>
    </div>
</div>

<script>
/* NAV SCROLL */
const nav = document.getElementById('mainNav');
window.addEventListener('scroll', () => nav.classList.toggle('scrolled', scrollY > 30), { passive: true });

/* DRAWER */
const drawer = document.getElementById('drawer');
const mobBg  = document.getElementById('mobBg');
const openDr  = () => { drawer.classList.add('open'); mobBg.classList.add('open'); document.body.style.overflow = 'hidden'; };
const closeDr = () => { drawer.classList.remove('open'); mobBg.classList.remove('open'); document.body.style.overflow = ''; };
document.getElementById('navToggle')?.addEventListener('click', openDr);
document.getElementById('drawerClose')?.addEventListener('click', closeDr);
mobBg?.addEventListener('click', closeDr);
drawer.querySelectorAll('a').forEach(a => a.addEventListener('click', closeDr));

/* MODAL */
const overlay = document.getElementById('loginOverlay');
function openModal(e) {
    if (e) e.preventDefault();
    overlay.classList.add('open'); document.body.style.overflow = 'hidden';
    closeDr();
    setTimeout(() => document.getElementById('u_un')?.focus(), 120);
}
const closeModal = () => { overlay.classList.remove('open'); document.body.style.overflow = ''; };
document.getElementById('modalClose')?.addEventListener('click', closeModal);
overlay?.addEventListener('click', e => { if (e.target === overlay) closeModal(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
<?php if (!empty($error) || !empty($success)): ?>
document.addEventListener('DOMContentLoaded', openModal);
<?php endif; ?>
function togglePw() {
    const f = document.getElementById('u_pw'), ic = document.getElementById('pwIcon');
    const h = f.type === 'password'; f.type = h ? 'text' : 'password';
    ic.classList.toggle('fa-eye', !h); ic.classList.toggle('fa-eye-slash', h);
}

/* HOURS */
(function() {
    const SCHED = [
        { days:[1,2,3,4,5], label:'Mon – Fri',  open:9,  close:23 },
        { days:[6],          label:'Saturday',   open:9,  close:23 },
        { days:[0],          label:'Sunday',     open:10, close:22 },
    ];
    const TILES = [
        {label:'Monday',day:1},{label:'Tuesday',day:2},{label:'Wednesday',day:3},{label:'Thursday',day:4},
        {label:'Friday',day:5},{label:'Saturday',day:6},{label:'Sunday',day:0},{label:'Holidays',day:-1},
    ];
    const now = new Date(), dow = now.getDay(), h = now.getHours() + now.getMinutes()/60;
    const getSched = d => SCHED.find(s => s.days.includes(d));
    const curSched = getSched(dow);
    const isOpen   = curSched && h >= curSched.open && h < curSched.close;
    const fmt      = n => n === 12 ? '12:00 PM' : n > 12 ? (n-12)+':00 PM' : n+':00 AM';

    /* Hero pill */
    const pill = document.getElementById('heroPill');
    const pillTxt = document.getElementById('heroPillText');
    if (pill && pillTxt) {
        if (!isOpen) {
            pill.className = 'open-pill is-closed';
            pillTxt.textContent = 'Currently Closed';
        }
    }

    /* Live status row */
    const liveText = document.getElementById('liveText');
    const liveDot  = document.getElementById('liveDot');
    if (liveText) {
        if (isOpen) {
            liveText.textContent = `Open right now · Closes at ${fmt(curSched.close)}`;
        } else {
            liveText.textContent = "We're currently closed";
            if (liveDot) { liveDot.classList.add('closed-dot'); }
        }
    }

    /* Hours grid */
    const grid = document.getElementById('hoursGrid');
    if (!grid) return;
    TILES.forEach(tile => {
        const s = tile.day === -1 ? null : getSched(tile.day);
        const isToday = tile.day === dow;
        const div = document.createElement('div');
        div.className = 'hour-tile' + (isToday ? ' today' : '') + (!s ? ' closed' : '');
        div.innerHTML = `<div class="hour-tile-day">${tile.label}${isToday ? ' · Today' : ''}</div>
            <div class="hour-tile-time">${s ? fmt(s.open)+' – '+fmt(s.close) : 'Closed'}</div>`;
        grid.appendChild(div);
    });
})();

/* MENU PAGINATION */
(function() {
    if (typeof MENU_DATA === 'undefined') return;
    const cats = Object.keys(MENU_DATA);
    if (!cats.length) return;
    let active = cats[0], page = 0;
    const navEl = document.getElementById('menuNav');
    const gEl   = document.getElementById('menuGrid');
    const btn   = document.getElementById('btnMore');
    const info  = document.getElementById('loadInfo');
    const esc   = s => String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');

    function cardHTML(it) {
        const dim = it.status ? ' dim' : '';
        const img = it.image
            ? `<img src="${esc(it.image)}" alt="${esc(it.name)}" class="menu-thumb${dim}" onerror="this.outerHTML='<div class=\\'menu-thumb-ph\\'><i class=\\'fas fa-utensils\\'></i></div>'">`
            : `<div class="menu-thumb-ph"><i class="fas fa-utensils"></i></div>`;
        const desc = it.desc ? `<div class="menu-desc">${esc(it.desc)}</div>` : '';
        const right = it.status === 'oos' ? `<span class="stock-tag oos">Out of Stock</span>`
                    : it.status === 'na'  ? `<span class="stock-tag na">Not Available</span>`
                    : it.vars > 1 ? `<span class="menu-vars">${it.vars} options</span>` : '';
        return `<div class="menu-card reveal">
            ${img}
            <div class="menu-info">
                <div class="menu-name">${esc(it.name)}</div>
                ${desc}
                <div class="menu-ft">
                    <div class="menu-price${it.status ? ' dim' : ''}">${esc(it.price)}</div>
                    ${right}
                </div>
            </div>
        </div>`;
    }

    function render() {
        const items = MENU_DATA[active] || [];
        const chunk = items.slice(page * PER, (page + 1) * PER);
        chunk.forEach(it => gEl.insertAdjacentHTML('beforeend', cardHTML(it)));
        gEl.querySelectorAll('.reveal:not(.in)').forEach((el, i) => {
            setTimeout(() => { el.classList.add('in'); }, i * 50);
        });
        const shown = Math.min((page + 1) * PER, items.length);
        info.textContent = shown < items.length ? `Showing ${shown} of ${items.length} items` : '';
        btn.disabled = shown >= items.length;
        btn.style.display = items.length <= PER ? 'none' : '';
        page++;
    }

    cats.forEach(cat => {
        const t = document.createElement('button');
        t.className = 'menu-tab' + (cat === active ? ' active' : '');
        t.textContent = cat;
        t.addEventListener('click', () => {
            if (cat === active) return;
            active = cat; page = 0;
            navEl.querySelectorAll('.menu-tab').forEach(x => x.classList.remove('active'));
            t.classList.add('active');
            gEl.innerHTML = '';
            render();
        });
        navEl.appendChild(t);
    });
    btn?.addEventListener('click', render);
    render();
})();

/* SCROLL REVEAL */
const io = new IntersectionObserver(entries => {
    entries.forEach((e, i) => {
        if (e.isIntersecting) {
            e.target.style.transitionDelay = (i % 5) * .07 + 's';
            e.target.classList.add('in');
            io.unobserve(e.target);
        }
    });
}, { threshold: .08, rootMargin: '0px 0px -28px 0px' });
document.querySelectorAll('.reveal').forEach(el => io.observe(el));

/* SMOOTH ANCHORS */
document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
        const t = document.querySelector(a.getAttribute('href'));
        if (t) { e.preventDefault(); window.scrollTo({ top: t.offsetTop - 72, behavior: 'smooth' }); }
    });
});
</script>
</body>
</html>