<?php
// app/views/auth/landing.php
// Requires: $baseUrl, $menu_by_category, $menu_items, $featured_items,
//           $categories, $error, $success, $pdo, $wifi_password

// ── Filter: only show items that have at least one recipe row ──
$items_with_recipe = [];
try {
    $stmt = $pdo->query("SELECT DISTINCT item_id FROM recipes");
    $items_with_recipe = array_flip(array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'item_id'));
} catch (Exception $e) {
    $items_with_recipe = null;
}

function hasRecipe($item_id, $items_with_recipe) {
    return $items_with_recipe === null || isset($items_with_recipe[$item_id]);
}

$featured_items = array_values(array_filter($featured_items, fn($fi) => hasRecipe($fi['item_id'], $items_with_recipe)));
$featured_items = array_slice($featured_items, 0, 6);

$filtered_menu_by_category = [];
foreach ($menu_by_category as $cat => $items) {
    $filtered = array_values(array_filter($items, fn($it) => hasRecipe($it['item_id'], $items_with_recipe)));
    if (!empty($filtered)) $filtered_menu_by_category[$cat] = $filtered;
}
$menu_by_category = $filtered_menu_by_category;
$total_visible    = array_sum(array_map('count', $menu_by_category));
$maps_url         = "https://maps.google.com/?q=Lias+Town+Plaza+Marilao+Bulacan+Philippines";
$wifi_pass_init   = htmlspecialchars($wifi_password ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php require_once __DIR__ . "/../../core/csrfHelper.php"; echo csrf_head($baseUrl ?? ""); ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Lounge by: CJV &amp; TEK</title>
    <link rel="icon" type="image/png" href="<?= $baseUrl ?>/assets/images/logo/logo.png">
    <link href="<?= $baseUrl ?>/assets/vendor/fonts/dm.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/vendor/fontawesome/css/all.min.css">
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
            --text-mute: #8a9bbf;
            --border:    rgba(30,47,78,0.08);
            --border-md: rgba(30,47,78,0.13);
            --nav-h:     64px;
            --serif: 'DM Serif Display', Georgia, serif;
            --sans:  'DM Sans', system-ui, sans-serif;
            --r-sm: 10px; --r-md: 16px; --r-lg: 22px;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body { font-family: var(--sans); font-optical-sizing: auto; background: var(--bg); color: var(--text); -webkit-font-smoothing: antialiased; overflow-x: hidden; font-size: 16px; line-height: 1.6; }

        /* ── NAV ── */
        .nav { position: fixed; top: 0; left: 0; right: 0; height: var(--nav-h); z-index: 1000; display: flex; align-items: center; justify-content: space-between; padding: 0 2rem; transition: background .35s, box-shadow .35s; }
        .nav.scrolled { background: rgba(253,252,249,.97); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); box-shadow: 0 1px 0 var(--border), 0 4px 24px rgba(30,47,78,.06); }
        .nav-brand { display: flex; align-items: center; gap: .7rem; text-decoration: none; }
        .nav-logo { width: 38px; height: 38px; border-radius: 50%; overflow: hidden; flex-shrink: 0; border: 2px solid rgba(244,160,36,.35); background: var(--navy); display: flex; align-items: center; justify-content: center; }
        .nav-logo img { width: 100%; height: 100%; object-fit: contain; }
        .nav-logo-fb { font-family: var(--serif); font-size: .95rem; color: var(--orange); display: none; }
        .nav-name { font-family: var(--serif); font-size: 1.1rem; color: var(--navy); line-height: 1; }
        .nav-sub  { font-size: .56rem; letter-spacing: .14em; text-transform: uppercase; color: var(--orange); font-weight: 600; margin-top: 1px; }
        .nav-links { display: flex; align-items: center; gap: 1.5rem; list-style: none; }
        .nav-links a { font-size: .875rem; font-weight: 500; color: var(--text-mid); text-decoration: none; transition: color .2s; letter-spacing: .01em; }
        .nav-links a:hover { color: var(--navy); }
        .btn-nav { font-family: var(--sans); font-size: .8rem; font-weight: 600; color: var(--white); background: var(--navy); border: none; padding: .58rem 1.35rem; border-radius: 999px; cursor: pointer; transition: background .2s; text-decoration: none; display: inline-flex; align-items: center; gap: .4rem; }
        .btn-nav:hover { background: var(--orange); color: var(--white); }
        .btn-wifi { background: var(--orange); }
        .btn-wifi:hover { background: var(--orange-dk); }
        .nav-toggle { display: none; background: none; border: 1.5px solid var(--border-md); color: var(--navy); padding: .35rem .55rem; border-radius: 8px; cursor: pointer; font-size: .85rem; align-items: center; justify-content: center; }

        /* ── HERO ── */
        .hero { min-height: 100svh; background: var(--bg); padding-top: var(--nav-h); display: flex; align-items: center; position: relative; overflow: hidden; }
        .hero::before { content: ''; position: absolute; top: -20%; right: -5%; width: 55%; height: 100%; background: radial-gradient(ellipse at 60% 40%, rgba(244,160,36,.11) 0%, transparent 65%); pointer-events: none; }
        .hero-inner { position: relative; z-index: 2; width: 100%; max-width: 1200px; margin: 0 auto; padding: 3rem 2rem 4rem; display: grid; grid-template-columns: 1fr 370px; gap: 4rem; align-items: center; }
        .hero-badge { display: inline-flex; align-items: center; gap: .5rem; background: var(--orange-lt); border: 1px solid rgba(244,160,36,.28); border-radius: 999px; padding: .3rem .9rem .3rem .55rem; margin-bottom: 1.35rem; opacity: 0; animation: fadeUp .55s .05s ease forwards; }
        .hero-badge-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--orange); box-shadow: 0 0 8px rgba(244,160,36,.5); }
        .hero-badge span { font-size: .7rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; color: var(--orange-dk); }
        .hero-title { font-family: var(--serif); font-size: clamp(2.6rem, 5vw, 4.6rem); line-height: 1.08; letter-spacing: -.01em; color: var(--navy); margin-bottom: 1.25rem; opacity: 0; animation: fadeUp .75s .15s ease forwards; }
        .hero-title em { font-style: italic; color: var(--orange); }
        .hero-body { font-size: 1rem; line-height: 1.75; color: var(--text-mid); max-width: 420px; margin-bottom: 2rem; opacity: 0; animation: fadeUp .75s .25s ease forwards; }
        .hero-cta { display: flex; gap: .75rem; align-items: center; flex-wrap: wrap; margin-bottom: 2.5rem; opacity: 0; animation: fadeUp .75s .35s ease forwards; }
        .btn-primary { font-family: var(--sans); font-size: .9rem; font-weight: 600; color: var(--white); background: var(--navy); border: none; padding: .8rem 1.85rem; border-radius: 999px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: .45rem; transition: all .22s; box-shadow: 0 4px 18px rgba(30,47,78,.16); }
        .btn-primary:hover { background: var(--orange); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(244,160,36,.28); }
        .btn-outline { font-family: var(--sans); font-size: .9rem; font-weight: 500; color: var(--navy); background: transparent; border: 1.5px solid var(--border-md); padding: .78rem 1.6rem; border-radius: 999px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: .4rem; transition: all .2s; }
        .btn-outline:hover { border-color: var(--orange); color: var(--orange); background: var(--orange-lt); }
        .hero-stats { display: flex; gap: 2rem; padding-top: 1.5rem; border-top: 1.5px solid var(--border-md); opacity: 0; animation: fadeUp .75s .45s ease forwards; }
        .hero-stat-n { font-family: var(--serif); font-size: 1.9rem; color: var(--navy); line-height: 1; }
        .hero-stat-l { font-size: .72rem; font-weight: 500; letter-spacing: .08em; text-transform: uppercase; color: var(--text-mute); margin-top: .2rem; }
        .hero-right { display: flex; flex-direction: column; gap: .85rem; opacity: 0; animation: fadeUp .85s .35s ease forwards; }
        .hours-card { background: var(--white); border: 1.5px solid var(--border-md); border-radius: var(--r-lg); padding: 1.3rem 1.4rem 1.2rem; box-shadow: 0 6px 24px rgba(30,47,78,.06); }
        .hours-card-hd { display: flex; align-items: center; gap: .6rem; margin-bottom: .9rem; }
        .hours-card-ico { width: 34px; height: 34px; border-radius: 9px; background: var(--orange-lt); border: 1px solid rgba(244,160,36,.22); display: flex; align-items: center; justify-content: center; color: var(--orange); font-size: .82rem; }
        .hours-card-title { font-family: var(--serif); font-size: 1rem; color: var(--navy); }
        .hours-row { display: flex; justify-content: space-between; align-items: center; padding: .4rem 0; border-bottom: 1px solid var(--border); }
        .hours-row:last-of-type { border-bottom: none; }
        .hours-day  { font-size: .8rem; color: var(--text-mid); }
        .hours-time { font-size: .8rem; font-weight: 600; color: var(--navy); }
        .open-pill { display: inline-flex; align-items: center; gap: .38rem; border-radius: 999px; padding: .22rem .7rem; margin-top: .85rem; font-size: .65rem; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; }
        .open-pill.is-open   { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }
        .open-pill.is-closed { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; }
        .open-pill-dot { width: 6px; height: 6px; border-radius: 50%; animation: pulse-dot 2s infinite; }
        .is-open   .open-pill-dot { background: #10b981; }
        .is-closed .open-pill-dot { background: #ef4444; }
        @keyframes pulse-dot { 0%,100%{opacity:1}50%{opacity:.35} }
        .feat-preview { background: var(--white); border: 1.5px solid var(--border-md); border-radius: var(--r-lg); overflow: hidden; box-shadow: 0 6px 22px rgba(30,47,78,.06); }
        .feat-preview-hd { background: var(--navy); padding: .65rem 1.1rem; display: flex; align-items: center; justify-content: space-between; }
        .feat-preview-label { font-size: .6rem; letter-spacing: .14em; text-transform: uppercase; color: var(--orange); font-weight: 600; }
        .feat-preview-count { font-size: .65rem; color: rgba(255,255,255,.3); }
        .feat-preview-item { display: flex; align-items: center; gap: .75rem; padding: .72rem 1.1rem; border-bottom: 1px solid var(--border); }
        .feat-preview-item:last-child { border-bottom: none; }
        .feat-preview-ico { width: 30px; height: 30px; border-radius: 7px; background: var(--orange-lt); display: flex; align-items: center; justify-content: center; color: var(--orange); font-size: .72rem; flex-shrink: 0; }
        .feat-preview-name  { font-size: .82rem; font-weight: 600; color: var(--navy); }
        .feat-preview-cat   { font-size: .65rem; color: var(--text-mute); }
        .feat-preview-price { font-family: var(--serif); font-size: .84rem; color: var(--navy); margin-left: auto; flex-shrink: 0; }

        /* ── TICKER ── */
        .ticker { background: var(--navy); padding: .6rem 0; overflow: hidden; }
        .ticker-track { display: flex; width: max-content; animation: ticker 28s linear infinite; }
        .ticker-item { display: flex; align-items: center; gap: 1.1rem; padding: 0 1.2rem; font-size: .64rem; font-weight: 600; letter-spacing: .16em; text-transform: uppercase; color: rgba(255,255,255,.5); white-space: nowrap; }
        .ticker-sep { color: var(--orange); font-size: .46rem; }
        @keyframes ticker { from{transform:translateX(0)}to{transform:translateX(-50%)} }

        /* ── SHARED ── */
        .container { max-width: 1200px; margin: 0 auto; }
        .section       { padding: 4rem 2rem; }
        .section-alt   { background: var(--bg2); padding: 4rem 2rem; }
        .section-navy  { background: var(--navy); padding: 4rem 2rem; }
        .section-stone { background: var(--bg3); padding: 3.5rem 2rem; }
        .section-hd { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem; gap: 1.5rem; flex-wrap: wrap; }
        .label { display: inline-flex; align-items: center; gap: .5rem; margin-bottom: .55rem; }
        .label-bar { width: 16px; height: 2px; background: var(--orange); border-radius: 2px; }
        .label span { font-size: .65rem; font-weight: 600; letter-spacing: .18em; text-transform: uppercase; color: var(--orange); }
        .h2 { font-family: var(--serif); font-size: clamp(1.75rem, 3vw, 2.5rem); line-height: 1.12; color: var(--navy); }
        .h2 em { font-style: italic; color: var(--orange); }
        .h2-white { color: var(--white); }
        .h2-white em { color: var(--orange); }
        .subtext { font-size: .9rem; color: var(--text-mute); line-height: 1.75; max-width: 380px; }
        .subtext-white { color: rgba(255,255,255,.4); }

        /* ── FEATURED ── */
        .feat-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 1rem; }
        .feat-card { background: var(--white); border-radius: var(--r-md); border: 1.5px solid var(--border); overflow: hidden; transition: transform .25s, box-shadow .25s; position: relative; display: flex; flex-direction: column; }
        .feat-card:hover { transform: translateY(-4px); box-shadow: 0 16px 36px rgba(30,47,78,.1); }
        .feat-img  { width: 100%; aspect-ratio: 4 / 3; height: auto; object-fit: cover; object-position: center; display: block; transition: transform .4s; }
        .feat-ph   { width: 100%; aspect-ratio: 4 / 3; height: auto; background: linear-gradient(145deg,var(--bg2),var(--bg3)); display: flex; align-items: center; justify-content: center; color: var(--text-mute); font-size: 2rem; }
        .feat-ph   { width: 100%; height: 190px; background: linear-gradient(145deg,var(--bg2),var(--bg3)); display: flex; align-items: center; justify-content: center; color: var(--text-mute); font-size: 2rem; }
        .feat-card:hover .feat-img { transform: scale(1.04); }
        .feat-ph.dim, .feat-img.dim { filter: grayscale(50%) opacity(.7); }
        .feat-body { padding: 1rem 1.15rem 1.2rem; flex: 1; display: flex; flex-direction: column; }
        .feat-cat  { font-size: .65rem; font-weight: 600; letter-spacing: .12em; text-transform: uppercase; color: var(--orange); margin-bottom: .2rem; }
        .feat-name { font-family: var(--serif); font-size: 1.05rem; color: var(--navy); line-height: 1.3; margin-bottom: .3rem; }
        .feat-desc { font-size: .8rem; color: var(--text-mute); line-height: 1.6; margin-bottom: .75rem; flex: 1; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .feat-ft { display: flex; align-items: center; justify-content: space-between; gap: .4rem; }
        .feat-price { font-family: var(--serif); font-size: 1.05rem; color: var(--navy); }
        .feat-badge { font-size: .62rem; font-weight: 600; letter-spacing: .05em; background: var(--orange-lt); color: var(--orange-dk); padding: .18rem .55rem; border-radius: 999px; }
        .stock-ribbon { position: absolute; top: .6rem; left: .6rem; font-size: .6rem; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; padding: .2rem .6rem; border-radius: 999px; z-index: 2; }
        .stock-ribbon.oos { background: rgba(239,68,68,.12); color: #dc2626; border: 1px solid rgba(239,68,68,.22); }
        .stock-ribbon.na  { background: rgba(100,116,139,.1);  color: #64748b; border: 1px solid rgba(100,116,139,.22); }
        .stock-tag { font-size: .62rem; font-weight: 600; text-transform: uppercase; padding: .16rem .5rem; border-radius: 999px; }
        .stock-tag.oos { background: rgba(239,68,68,.08); color: #dc2626; border: 1px solid rgba(239,68,68,.18); }
        .stock-tag.na  { background: rgba(100,116,139,.08); color: #64748b; border: 1px solid rgba(100,116,139,.18); }

        /* ── MENU ── */
        .menu-layout { display: grid; grid-template-columns: 210px 1fr; gap: 1.5rem; align-items: start; }
        .menu-sidebar { position: sticky; top: calc(var(--nav-h) + 1rem); background: var(--white); border: 1.5px solid var(--border-md); border-radius: var(--r-md); overflow: hidden; }
        .menu-sidebar-hd { padding: .7rem 1rem; background: var(--navy); font-size: .62rem; font-weight: 600; letter-spacing: .15em; text-transform: uppercase; color: var(--orange); }
        .menu-cat-btn { width: 100%; text-align: left; background: none; border: none; padding: .68rem 1rem; font-family: var(--sans); font-size: .85rem; font-weight: 400; color: var(--text-mid); cursor: pointer; border-bottom: 1px solid var(--border); transition: all .17s; display: flex; align-items: center; justify-content: space-between; gap: .5rem; }
        .menu-cat-btn:last-child { border-bottom: none; }
        .menu-cat-btn:hover { background: var(--bg); color: var(--navy); }
        .menu-cat-btn.active { background: var(--orange-lt); color: var(--orange-dk); font-weight: 600; }
        .menu-cat-count { font-size: .68rem; font-weight: 600; background: rgba(30,47,78,.06); color: var(--text-mute); padding: .1rem .45rem; border-radius: 999px; flex-shrink: 0; }
        .menu-cat-btn.active .menu-cat-count { background: rgba(244,160,36,.18); color: var(--orange-dk); }
        .menu-content { min-width: 0; }
        .menu-content-hd { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.1rem; gap: 1rem; flex-wrap: wrap; }
        .menu-content-title { font-family: var(--serif); font-size: 1.35rem; color: var(--navy); }
        .menu-content-count { font-size: .8rem; color: var(--text-mute); margin-top: .1rem; }
        .menu-search-wrap { position: relative; flex-shrink: 0; }
        .menu-search { width: 210px; padding: .52rem .95rem .52rem 2.15rem; border: 1.5px solid var(--border-md); border-radius: 999px; background: var(--white); font-family: var(--sans); font-size: .85rem; color: var(--text); outline: none; transition: border-color .2s; }
        .menu-search:focus { border-color: var(--navy); }
        .menu-search::placeholder { color: var(--text-mute); }
        .menu-search-ico { position: absolute; left: .78rem; top: 50%; transform: translateY(-50%); color: var(--text-mute); font-size: .7rem; pointer-events: none; }
        .menu-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: .85rem; transition: opacity .22s ease, transform .22s ease; }
        .menu-card { background: var(--white); border: 1.5px solid var(--border); border-radius: var(--r-sm); overflow: hidden; transition: box-shadow .2s, transform .2s; display: flex; flex-direction: column; }
        .menu-card:hover { box-shadow: 0 8px 22px rgba(30,47,78,.08); transform: translateY(-2px); }
        .menu-thumb    { width: 100%; aspect-ratio: 4 / 3; height: auto; object-fit: cover; object-position: center; display: block; }
        .menu-thumb-ph { width: 100%; aspect-ratio: 4 / 3; height: auto; background: var(--bg2); display: flex; align-items: center; justify-content: center; color: var(--text-mute); font-size: 1.5rem; }
        .menu-thumb.dim, .menu-thumb-ph.dim { filter: grayscale(40%) opacity(.7); }
        .menu-card-body { padding: .8rem .9rem .85rem; flex: 1; display: flex; flex-direction: column; }
        .menu-name  { font-family: var(--serif); font-size: .95rem; color: var(--navy); margin-bottom: .12rem; line-height: 1.3; }
        .menu-desc  { font-size: .78rem; color: var(--text-mute); line-height: 1.55; margin-bottom: .55rem; flex: 1; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .menu-ft    { display: flex; align-items: center; justify-content: space-between; gap: .4rem; flex-wrap: wrap; }
        .menu-price { font-family: var(--serif); font-size: .92rem; color: var(--navy); }
        .menu-price.dim { color: var(--text-mute); }
        .menu-vars  { font-size: .62rem; font-weight: 600; letter-spacing: .04em; color: var(--orange); background: var(--orange-lt); padding: .13rem .48rem; border-radius: 999px; }
        .menu-empty { text-align: center; padding: 3rem 1.5rem; color: var(--text-mute); }
        .menu-empty i { font-size: 1.8rem; color: var(--orange); opacity: .28; display: block; margin-bottom: .65rem; }
        .menu-empty p { font-size: .85rem; }

        /* ── SEE MORE ── */
        .see-more-wrap { display: flex; align-items: center; justify-content: center; margin-top: 1.5rem; }
        .btn-see-more { font-family: var(--sans); font-size: .85rem; font-weight: 600; color: var(--navy); background: var(--white); border: 1.5px solid var(--border-md); padding: .65rem 1.75rem; border-radius: 999px; cursor: pointer; display: inline-flex; align-items: center; gap: .5rem; transition: all .2s; }
        .btn-see-more:hover { background: var(--navy); color: var(--white); border-color: var(--navy); }
        .see-more-count { font-size: .75rem; color: var(--text-mute); margin-top: .65rem; text-align: center; }

        /* ── ABOUT ── */
        .about-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center; }
        .about-visual { position: relative; }
        .about-img-wrap { width: 100%; border-radius: var(--r-lg); overflow: hidden; box-shadow: 0 20px 50px rgba(30,47,78,.13); }
        .about-img-wrap img { width: 100%; height: auto; display: block; }
        .about-ph-fallback { width: 100%; aspect-ratio: 4/3; border-radius: var(--r-lg); background: linear-gradient(145deg,var(--bg2),var(--bg3)); border: 1.5px solid var(--border-md); display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 1rem; }
        .about-ph-fallback i    { font-size: 3rem; color: var(--orange); opacity: .22; }
        .about-ph-fallback span { font-family: var(--serif); font-size: 1rem; font-style: italic; color: var(--text-mute); }
        .about-chip { position: absolute; bottom: -1rem; right: -1.25rem; background: var(--white); border: 1.5px solid var(--border-md); border-radius: var(--r-md); padding: .85rem 1.15rem; box-shadow: 0 10px 28px rgba(30,47,78,.1); display: flex; align-items: center; gap: .75rem; }
        .about-chip-ico { width: 34px; height: 34px; border-radius: 9px; background: var(--orange); color: var(--white); display: flex; align-items: center; justify-content: center; font-size: .82rem; }
        .about-chip-n { font-family: var(--serif); font-size: 1.4rem; color: var(--navy); line-height: 1; }
        .about-chip-l { font-size: .62rem; font-weight: 500; letter-spacing: .1em; text-transform: uppercase; color: var(--text-mute); }
        .pillars { display: flex; flex-direction: column; gap: 1.1rem; margin-top: 1.85rem; }
        .pillar { display: flex; gap: .9rem; align-items: flex-start; }
        .pillar-ico { width: 36px; height: 36px; border-radius: 9px; flex-shrink: 0; background: var(--bg2); border: 1.5px solid var(--border-md); color: var(--navy); display: flex; align-items: center; justify-content: center; font-size: .82rem; }
        .pillar-t { font-family: var(--serif); font-size: .98rem; color: var(--navy); margin-bottom: .1rem; }
        .pillar-d { font-size: .82rem; color: var(--text-mute); line-height: 1.65; }

        /* ── HOURS ── */
        .hours-inner { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1.3fr; gap: 4rem; align-items: center; }
        .hours-grid { display: grid; grid-template-columns: repeat(2,1fr); gap: .65rem; }
        .hour-tile { background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.08); border-radius: var(--r-sm); padding: .85rem 1rem; transition: border-color .2s; }
        .hour-tile:hover { border-color: rgba(244,160,36,.28); }
        .hour-tile-day  { font-size: .65rem; font-weight: 600; letter-spacing: .12em; text-transform: uppercase; color: rgba(255,255,255,.36); margin-bottom: .28rem; }
        .hour-tile-time { font-family: var(--serif); font-size: .96rem; color: var(--white); }
        .hour-tile.closed .hour-tile-time { color: rgba(255,255,255,.25); font-style: italic; font-size: .84rem; }
        .hour-tile.today { border-color: rgba(244,160,36,.38); background: rgba(244,160,36,.08); }
        .hour-tile.today .hour-tile-day  { color: var(--orange); }
        .hour-tile.today .hour-tile-time { color: var(--orange); }
        .live-row { display: flex; align-items: center; gap: .5rem; margin-top: 1.1rem; font-size: .82rem; color: rgba(255,255,255,.45); }
        .live-dot { width: 7px; height: 7px; border-radius: 50%; background: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,.2); animation: pulse-dot 2s infinite; }
        .live-dot.closed-dot { background: #f59e0b; box-shadow: 0 0 0 3px rgba(245,158,11,.2); }

        /* ── QUOTE ── */
        .quote-wrap { text-align: center; max-width: 600px; margin: 0 auto; }
        .quote-mark { font-family: var(--serif); font-size: 6rem; line-height: .5; color: var(--orange); opacity: .2; display: block; margin-bottom: 1.75rem; }
        .quote-t { font-family: var(--serif); font-size: clamp(1.3rem, 2.5vw, 2rem); font-style: italic; color: var(--navy); line-height: 1.55; margin-bottom: 1rem; }
        .quote-t em { font-style: normal; color: var(--orange); }
        .quote-by { font-size: .7rem; font-weight: 600; letter-spacing: .18em; text-transform: uppercase; color: var(--text-mute); }

        /* ── FOOTER ── */
        footer { background: var(--navy-dk); color: rgba(255,255,255,.35); padding: 3.5rem 2rem 2rem; }
        .footer-inner { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1.8fr 1fr 1fr 1fr; gap: 2.5rem; padding-bottom: 2rem; border-bottom: 1px solid rgba(255,255,255,.06); margin-bottom: 1.75rem; }
        .footer-logo-row { display: flex; align-items: center; gap: .65rem; margin-bottom: .75rem; }
        .footer-logo { width: 34px; height: 34px; border-radius: 50%; border: 2px solid rgba(244,160,36,.28); background: rgba(255,255,255,.08); display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .footer-logo img { width: 100%; height: 100%; object-fit: contain; }
        .footer-name    { font-family: var(--serif); font-size: 1.05rem; color: var(--white); }
        .footer-sub-txt { font-size: .56rem; font-weight: 600; letter-spacing: .16em; text-transform: uppercase; color: var(--orange); margin-bottom: .75rem; }
        .footer-desc    { font-size: .82rem; line-height: 1.7; color: rgba(255,255,255,.28); margin-bottom: .85rem; }
        .footer-cta     { display: inline-flex; align-items: center; gap: .38rem; font-size: .8rem; font-weight: 600; color: var(--orange); text-decoration: none; transition: opacity .2s; }
        .footer-cta:hover { opacity: .7; }
        .footer-col-t   { font-size: .6rem; font-weight: 600; letter-spacing: .18em; text-transform: uppercase; color: var(--orange); margin-bottom: .9rem; }
        .footer-links   { list-style: none; display: flex; flex-direction: column; gap: .48rem; }
        .footer-links a { font-size: .82rem; color: rgba(255,255,255,.3); text-decoration: none; transition: color .2s; }
        .footer-links a:hover { color: var(--orange); }
        .footer-contact { font-size: .78rem; color: rgba(255,255,255,.3); display: flex; flex-direction: column; gap: .18rem; margin-bottom: .65rem; }
        .footer-map-link { display: inline-flex; align-items: center; gap: .5rem; font-size: .78rem; font-weight: 600; color: var(--orange); text-decoration: none; border: 1px solid rgba(244,160,36,.3); border-radius: 999px; padding: .32rem .9rem; margin-bottom: .9rem; width: fit-content; transition: background .2s, opacity .2s; }
        .footer-map-link:hover { background: rgba(244,160,36,.1); opacity: .85; }
        .footer-map-link i:last-child { font-size: .6rem; opacity: .65; }
        .footer-socials { display: flex; gap: .6rem; }
        .footer-socials a { width: 32px; height: 32px; border-radius: 7px; background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.07); display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,.45); font-size: .76rem; transition: all .2s; }
        .footer-socials a:hover { background: var(--orange); color: var(--white); border-color: var(--orange); }
        .footer-btm { max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; font-size: .75rem; flex-wrap: wrap; gap: .6rem; }
        .footer-btm-copy { color: rgba(255,255,255,.2); }
        .footer-btm-tag  { color: rgba(255,255,255,.25); }

        /* ── LOGIN MODAL ── */
        .overlay { position: fixed; inset: 0; z-index: 9000; background: rgba(14,20,34,.6); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); display: flex; align-items: center; justify-content: center; padding: 1.5rem; opacity: 0; pointer-events: none; transition: opacity .28s; }
        .overlay.open { opacity: 1; pointer-events: all; }
        .modal { background: var(--white); border-radius: 22px; width: 100%; max-width: 390px; overflow: hidden; transform: translateY(18px) scale(.97); transition: transform .32s cubic-bezier(.22,1,.36,1); box-shadow: 0 36px 72px rgba(14,20,34,.2); }
        .overlay.open .modal { transform: translateY(0) scale(1); }
        .modal-band { background: var(--navy); padding: 1.55rem 1.6rem 1.35rem; position: relative; overflow: hidden; }
        .modal-band::after { content:''; position: absolute; right: -12px; top: -12px; width: 100px; height: 100px; border-radius: 50%; background: rgba(244,160,36,.1); pointer-events: none; }
        .modal-close { position: absolute; top: .85rem; right: .85rem; z-index: 2; background: rgba(255,255,255,.1); border: none; width: 26px; height: 26px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,.5); font-size: .68rem; transition: background .2s; }
        .modal-close:hover { background: rgba(244,160,36,.3); color: var(--orange); }
        .modal-logo-row { display: flex; align-items: center; gap: .65rem; margin-bottom: 1rem; position: relative; z-index: 1; }
        .modal-logo { width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,.1); border: 1.5px solid rgba(255,255,255,.14); display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .modal-logo img { width: 100%; height: 100%; object-fit: contain; }
        .modal-brand   { font-family: var(--serif); font-size: .9rem; color: var(--white); }
        .modal-brand-s { font-size: .54rem; font-weight: 600; letter-spacing: .14em; text-transform: uppercase; color: var(--orange); }
        .modal-h  { font-family: var(--serif); font-size: 1.65rem; color: var(--white); margin-bottom: .12rem; position: relative; z-index: 1; }
        .modal-h em { font-style: italic; color: var(--orange); }
        .modal-s  { font-size: .8rem; color: rgba(255,255,255,.38); position: relative; z-index: 1; }
        .modal-body { padding: 1.45rem 1.6rem 1.6rem; }
        .alert { border-radius: 8px; padding: .55rem .8rem; font-size: .8rem; display: flex; gap: .45rem; align-items: flex-start; margin-bottom: .9rem; }
        .alert-err { background: rgba(220,60,60,.07); border: 1px solid rgba(220,60,60,.17); color: #dc2626; }
        .alert-ok  { background: rgba(39,174,96,.07); border: 1px solid rgba(39,174,96,.17); color: #16a34a; }
        .field { margin-bottom: .82rem; }
        .field label { display: block; font-size: .68rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; color: var(--text-mute); margin-bottom: .3rem; }
        .input-row { position: relative; }
        .input-ico { position: absolute; left: .88rem; top: 50%; transform: translateY(-50%); color: var(--orange); font-size: .68rem; pointer-events: none; }
        .field input { width: 100%; background: var(--bg); border: 1.5px solid var(--border-md); border-radius: 11px; padding: .74rem 1rem .74rem 2.25rem; font-family: var(--sans); font-size: .9rem; color: var(--text); outline: none; transition: border-color .2s, box-shadow .2s; }
        .field input::placeholder { color: rgba(148,163,184,.45); }
        .field input:focus { border-color: var(--navy); background: var(--white); box-shadow: 0 0 0 3px rgba(30,47,78,.06); }
        .field input:-webkit-autofill { -webkit-box-shadow: 0 0 0 999px var(--bg) inset !important; -webkit-text-fill-color: var(--text) !important; }
        .pass-eye { position: absolute; right: .75rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-mute); font-size: .72rem; padding: .2rem; transition: color .2s; }
        .pass-eye:hover { color: var(--navy); }
        .forgot { text-align: right; margin-bottom: 1rem; }
        .forgot a { font-size: .78rem; color: var(--text-mute); text-decoration: none; transition: color .2s; }
        .forgot a:hover { color: var(--orange); }
        .btn-submit { width: 100%; padding: .85rem; background: var(--navy); color: var(--white); border: none; border-radius: 999px; font-family: var(--sans); font-size: .875rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: .45rem; transition: all .2s; }
        .btn-submit:hover { background: var(--orange); transform: translateY(-1px); }
        .modal-note { text-align: center; margin-top: 1.1rem; padding-top: .9rem; border-top: 1px solid var(--border); font-size: .78rem; color: var(--text-mute); }

        /* ── DRAWER ── */
        .mob-bg { position: fixed; inset: 0; background: rgba(14,20,34,.28); z-index: 1090; display: none; }
        .mob-bg.open { display: block; }
        .drawer { position: fixed; top: 0; right: 0; bottom: 0; width: 255px; background: var(--white); z-index: 1100; transform: translateX(100%); transition: transform .26s ease; padding: 4.2rem 1.4rem 2rem; box-shadow: -6px 0 26px rgba(30,47,78,.1); border-left: 1.5px solid var(--border); }
        .drawer.open { transform: translateX(0); }
        .drawer-links { list-style: none; display: flex; flex-direction: column; }
        .drawer-links a { display: block; padding: .75rem 0; font-size: .9rem; color: var(--text-mid); text-decoration: none; border-bottom: 1px solid var(--border); transition: color .2s; }
        .drawer-links a:hover { color: var(--orange); }
        .drawer-close { position: absolute; top: .9rem; right: .9rem; background: none; border: 1.5px solid var(--border-md); color: var(--navy); width: 30px; height: 30px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: .78rem; }

        /* ── WIFI MODAL ── */
        .wifi-overlay { position: fixed; inset: 0; z-index: 9100; background: rgba(14,20,34,.6); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); display: flex; align-items: center; justify-content: center; padding: 1.5rem; opacity: 0; pointer-events: none; transition: opacity .28s; }
        .wifi-overlay.open { opacity: 1; pointer-events: all; }
        .wifi-modal { background: var(--white); border-radius: 22px; width: 100%; max-width: 380px; overflow: hidden; transform: translateY(18px) scale(.97); transition: transform .32s cubic-bezier(.22,1,.36,1); box-shadow: 0 36px 72px rgba(14,20,34,.2); max-height: 90svh; overflow-y: auto; }
        .wifi-overlay.open .wifi-modal { transform: translateY(0) scale(1); }
        .wifi-band { background: var(--navy); padding: 1.35rem 1.6rem 1.2rem; position: relative; }
        .wifi-band::after { content:''; position: absolute; right: -12px; top: -12px; width: 100px; height: 100px; border-radius: 50%; background: rgba(244,160,36,.1); pointer-events: none; }
        .wifi-close { position: absolute; top: .85rem; right: .85rem; z-index: 2; background: rgba(255,255,255,.1); border: none; width: 26px; height: 26px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,.5); font-size: .68rem; transition: background .2s; }
        .wifi-close:hover { background: rgba(244,160,36,.3); color: var(--orange); }
        .wifi-modal-icon { width: 40px; height: 40px; border-radius: 10px; background: rgba(244,160,36,.15); border: 1px solid rgba(244,160,36,.25); display: flex; align-items: center; justify-content: center; color: var(--orange); font-size: 1rem; margin-bottom: .85rem; position: relative; z-index: 1; }
        .wifi-modal-h { font-family: var(--serif); font-size: 1.5rem; color: var(--white); margin-bottom: .1rem; position: relative; z-index: 1; }
        .wifi-modal-h em { font-style: italic; color: var(--orange); }
        .wifi-modal-s { font-size: .78rem; color: rgba(255,255,255,.38); position: relative; z-index: 1; }
        .wifi-body { padding: 1.5rem 1.6rem 1.6rem; display: flex; flex-direction: column; align-items: center; gap: 1.1rem; }
        .wifi-qr-wrap { background: var(--white); border: 2px solid var(--border-md); border-radius: var(--r-md); padding: .85rem; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 4px 18px rgba(30,47,78,.07); }
        .wifi-qr-wrap img { width: 180px; height: 180px; display: block; }
        .wifi-details { width: 100%; background: var(--bg); border: 1.5px solid var(--border-md); border-radius: var(--r-sm); padding: .85rem 1rem; }
        .wifi-row { display: flex; justify-content: space-between; align-items: center; padding: .3rem 0; border-bottom: 1px solid var(--border); gap: .5rem; }
        .wifi-row:last-child { border-bottom: none; }
        .wifi-row-label { font-size: .68rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; color: var(--text-mute); flex-shrink: 0; }
        .wifi-row-val { font-size: .85rem; font-weight: 600; color: var(--navy); font-family: monospace; transition: color .4s; }
        .wifi-hint { font-size: .75rem; color: var(--text-mute); text-align: center; line-height: 1.6; }
        .wifi-change-btn { font-family: var(--sans); font-size: .65rem; font-weight: 600; color: var(--orange); background: var(--orange-lt); border: 1px solid rgba(244,160,36,.28); border-radius: 999px; padding: .18rem .6rem; cursor: pointer; transition: all .2s; flex-shrink: 0; margin-left: auto; }
        .wifi-change-btn:hover { background: var(--orange); color: var(--white); }

        /* ── WIFI CHANGE PANEL ── */
        .wifi-change-panel { width: 100%; background: var(--bg); border: 1.5px solid var(--border-md); border-radius: var(--r-sm); padding: 1rem; display: flex; flex-direction: column; gap: .7rem; }
        .wifi-change-field { display: flex; flex-direction: column; gap: .28rem; }
        .wifi-change-field label { font-size: .65rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; color: var(--text-mute); }
        .wifi-input-row { position: relative; }
        .wifi-input-ico { position: absolute; left: .78rem; top: 50%; transform: translateY(-50%); color: var(--orange); font-size: .65rem; pointer-events: none; }
        .wifi-change-panel input { width: 100%; background: var(--white); border: 1.5px solid var(--border-md); border-radius: 9px; padding: .62rem 2.4rem .62rem 2.1rem; font-family: var(--sans); font-size: .85rem; color: var(--text); outline: none; transition: border-color .2s, box-shadow .2s; }
        .wifi-change-panel input:focus { border-color: var(--navy); box-shadow: 0 0 0 3px rgba(30,47,78,.06); }
        .wifi-change-panel input::placeholder { color: rgba(148,163,184,.45); }
        .wifi-eye { position: absolute; right: .6rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-mute); font-size: .7rem; padding: .2rem; transition: color .2s; }
        .wifi-eye:hover { color: var(--navy); }
        .wifi-change-err { background: rgba(220,60,60,.07); border: 1px solid rgba(220,60,60,.17); color: #dc2626; border-radius: 8px; padding: .45rem .7rem; font-size: .78rem; display: flex; align-items: center; gap: .4rem; }
        .wifi-change-ok  { background: rgba(39,174,96,.07);  border: 1px solid rgba(39,174,96,.17);  color: #16a34a; border-radius: 8px; padding: .45rem .7rem; font-size: .78rem; display: flex; align-items: center; gap: .4rem; }
        .wifi-action-btn { flex: 1; font-family: var(--sans); font-size: .82rem; font-weight: 600; border: none; border-radius: 999px; padding: .6rem 1rem; cursor: pointer; transition: all .2s; display: inline-flex; align-items: center; justify-content: center; gap: .35rem; }
        .wifi-cancel-btn { background: var(--bg2); color: var(--text-mid); border: 1.5px solid var(--border-md); }
        .wifi-cancel-btn:hover { background: var(--bg3); }
        .wifi-save-btn { background: var(--navy); color: var(--white); }
        .wifi-save-btn:hover { background: var(--orange); }

        /* ── MISC ── */
        .empty { text-align: center; padding: 3.5rem 2rem; color: var(--text-mute); }
        .empty i { font-size: 1.8rem; color: var(--orange); opacity: .28; display: block; margin-bottom: .7rem; }
        .empty p { font-size: .875rem; }

        /* ── ANIMATIONS ── */
        @keyframes fadeUp { from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)} }
        .reveal { opacity: 0; transform: translateY(14px); transition: opacity .5s ease, transform .5s ease; }
        .reveal.in { opacity: 1; transform: translateY(0); }

        /* ═══════════════ RESPONSIVE ═══════════════ */
        @media(min-width:769px) and (max-width:1024px){
            .nav { padding: 0 1.5rem; }
            .nav-links { gap: 1rem; }
            .nav-links a { font-size: .8rem; }
            .btn-nav { font-size: .75rem; padding: .5rem 1.1rem; }
            .nav-name { font-size: .95rem; }
            .nav-sub { font-size: .5rem; }
        }
        @media(max-width:1024px){
            .hero-inner { grid-template-columns: 1fr; gap: 2rem; }
            .hero-right { display: none; }
            .feat-grid  { grid-template-columns: repeat(2,1fr); }
            .about-grid { grid-template-columns: 1fr; gap: 2.5rem; }
            .about-chip { right: 0; }
            .hours-inner { grid-template-columns: 1fr; gap: 2.5rem; }
            .menu-layout { grid-template-columns: 1fr; gap: 1rem; }
            .menu-sidebar { position: static; border-radius: var(--r-sm); }
            .menu-sidebar-hd { display: none; }
            .menu-sidebar-list { display: flex; flex-wrap: nowrap; overflow-x: auto; padding: .5rem .6rem; gap: .4rem; scrollbar-width: none; }
            .menu-sidebar-list::-webkit-scrollbar { display: none; }
            .menu-cat-btn { border-radius: 999px; border: 1.5px solid var(--border-md); padding: .44rem 1.05rem; white-space: nowrap; font-size: .82rem; border-bottom: none; flex-shrink: 0; width: auto; justify-content: center; }
            .menu-cat-btn .menu-cat-count { display: none; }
            .footer-inner { grid-template-columns: 1fr 1fr; gap: 1.75rem; }
            .hero-stats   { gap: 1.5rem; }
        }
        @media(max-width:768px){
            :root { --nav-h: 58px; }
            .nav { padding: 0 1rem; }
            .nav-links { display: none; }
            .nav-toggle { display: flex; }
            .nav-name   { font-size: .95rem; }
            .section, .section-alt, .section-navy, .section-stone { padding: 2.75rem 1.1rem; }
            .hero-inner { padding: 2rem 1.1rem 3rem; }
            .hero-title { font-size: clamp(2rem, 8vw, 2.8rem); }
            .hero-body  { font-size: .925rem; }
            .hero-stats { gap: 1.25rem; flex-wrap: wrap; }
            .hero-stat-n { font-size: 1.55rem; }
            .feat-grid { grid-template-columns: 1fr; gap: .9rem; }
            .menu-grid { grid-template-columns: repeat(2,1fr); gap: .75rem; }
            .menu-search { width: 100%; }
            .menu-search-wrap { width: 100%; }
            .menu-content-hd { flex-direction: column; align-items: flex-start; gap: .65rem; }
            .about-chip { right: .5rem; bottom: -.75rem; padding: .65rem .9rem; }
            .about-chip-n { font-size: 1.1rem; }
            .hours-grid { grid-template-columns: repeat(2,1fr); gap: .5rem; }
            .hour-tile  { padding: .7rem .85rem; }
            .hour-tile-time { font-size: .88rem; }
            .footer-inner { grid-template-columns: 1fr 1fr; gap: 1.5rem; }
            .footer-desc  { display: none; }
            .section-hd { flex-direction: column; align-items: flex-start; gap: .75rem; }
            .subtext[style*="text-align:right"] { text-align: left !important; }
            .btn-wifi span.wifi-label { display: none; }
            .nav-name { font-size: .9rem; }
            .nav-sub { font-size: .5rem; }
            .btn-nav { font-size: .65rem; padding: .4rem .8rem; white-space: nowrap; }
        }
        @media(max-width:540px){
            .footer-inner { grid-template-columns: 1fr; }
            .hero-cta { flex-direction: column; align-items: stretch; }
            .btn-primary, .btn-outline { justify-content: center; width: 100%; }
            .hero-stats { display: grid; grid-template-columns: repeat(2,1fr); gap: .9rem; padding-top: 1.2rem; }
            .hours-grid { grid-template-columns: 1fr; }
            .menu-grid { grid-template-columns: 1fr; }
            .about-chip { position: static; margin-top: 1.1rem; }
            .nav-sub { display: none; }
            .overlay { align-items: flex-end; padding: 0; }
            .modal { border-radius: 22px 22px 0 0; max-width: 100%; width: 100%; transform: translateY(30px) scale(1); }
            .overlay.open .modal { transform: translateY(0) scale(1); }
            .wifi-overlay { align-items: flex-end; padding: 0; }
            .wifi-modal { border-radius: 22px 22px 0 0; max-width: 100%; max-height: 92svh; }
        }
        @media(max-width:380px){
            .nav-name { font-size: .82rem; }
            .menu-cat-btn { font-size: .75rem; padding: .38rem .75rem; }
            .hero-stat-n { font-size: 1.3rem; }
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
        <li><a href="#" onclick="openWifi(event)">Connect to WiFi</a></li>
        <li><a href="#" onclick="openModal(event)">Staff Login</a></li>
    </ul>
</div>

<!-- ══ NAV ══ -->
<nav class="nav" id="mainNav">
    <a href="#home" class="nav-brand">
        <div class="nav-logo">
            <img src="<?= $baseUrl ?>/assets/images/logo/logo.png" alt="The Lounge"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
            <span class="nav-logo-fb">L</span>
        </div>
        <div>
            <div class="nav-name">The Lounge by: CJV &amp; TEK</div>
            <div class="nav-sub">Chill. Coffee. Dine.</div>
        </div>
    </a>
    <ul class="nav-links">
        <li><a href="#featured">Featured</a></li>
        <li><a href="#menu">Menu</a></li>
        <li><a href="#about">About</a></li>
        <li><a href="#contact">Contact</a></li>
    </ul>
    <div style="display:flex;align-items:center;gap:.6rem">
        <a href="javascript:void(0)" class="btn-nav btn-wifi" onclick="openWifi(event)">
            <i class="fas fa-wifi"></i> <span class="wifi-label">Connect to WiFi</span>
        </a>
        <a href="javascript:void(0)" class="btn-nav" onclick="openModal(event)">
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
                <span>Brewed to brighten your day</span>
            </div>
            <h1 class="hero-title">Where every<br>sip is <em>crafted</em><br>with love</h1>
            <p class="hero-body">From single-origin brews to kitchen-fresh bites — every item on our menu is a testament to quality ingredients and the artisans behind them.</p>
            <div class="hero-cta">
                <a href="#menu" class="btn-primary"><i class="fas fa-book-open"></i> Browse Menu</a>
                <a href="#about" class="btn-outline">Our Story <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="hero-stats">
                <div>
                    <div class="hero-stat-n"><?= count($categories) ?>+</div>
                    <div class="hero-stat-l">Categories</div>
                </div>
                <div>
                    <div class="hero-stat-n"><?= $total_visible ?>+</div>
                    <div class="hero-stat-l">Menu Items</div>
                </div>
                <div>
                    <div class="hero-stat-n">100%</div>
                    <div class="hero-stat-l">Fresh Daily</div>
                </div>
            </div>
        </div>
        <div class="hero-right">
            <div class="hours-card">
                <div class="hours-card-hd">
                    <div class="hours-card-ico"><i class="fas fa-clock"></i></div>
                    <div class="hours-card-title">Opening Hours</div>
                </div>
                <div class="hours-row"><span class="hours-day">Mon – Fri</span><span class="hours-time">11:00 AM – 9:00 PM</span></div>
                <div class="hours-row"><span class="hours-day">Saturday</span><span class="hours-time">11:00 AM – 9:00 PM</span></div>
                <div class="hours-row"><span class="hours-day">Sunday</span><span class="hours-time">11:00 AM – 9:00 PM</span></div>
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

<!-- ══ TICKER ══ -->
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
                $avail  = !isset($fi['is_available']) || (bool)$fi['is_available'];
                $stock  = !isset($fi['in_stock'])     || (bool)$fi['in_stock'];
                $sClass = !$avail ? 'na' : (!$stock ? 'oos' : '');
                $sLabel = !$avail ? 'Not Available' : (!$stock ? 'Out of Stock' : '');
            ?>
            <div class="feat-card reveal">
                <?php if ($sLabel): ?><span class="stock-ribbon <?= $sClass ?>"><?= $sLabel ?></span><?php endif; ?>
                <div class="feat-img-wrap">
                    <?php if (!empty($fi['image_url'])): ?>
                        <img src="<?= $baseUrl . '/' . htmlspecialchars($fi['image_url']) ?>"
                             alt="<?= htmlspecialchars($fi['item_name']) ?>"
                             class="feat-img<?= $sClass ? ' dim' : '' ?>"
                             onerror="this.outerHTML='<div class=\'feat-ph<?= $sClass ? " dim":"" ?>\'><i class=\'fas fa-utensils\'></i></div>'">
                    <?php else: ?>
                        <div class="feat-ph<?= $sClass ? ' dim' : '' ?>"><i class="fas fa-utensils"></i></div>
                    <?php endif; ?>
                </div>
                <div class="feat-body">
                    <div class="feat-cat"><?= htmlspecialchars($fi['category_name']) ?></div>
                    <div class="feat-name"><?= htmlspecialchars($fi['item_name']) ?></div>
                    <?php if (!empty($fi['description'])): ?>
                        <div class="feat-desc"><?= htmlspecialchars($fi['description']) ?></div>
                    <?php endif; ?>
                    <div class="feat-ft">
                        <?php if ($sClass): ?>
                            <span class="feat-price" style="color:var(--text-mute)"><?= htmlspecialchars($fi['display_price']) ?></span>
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
            echo json_encode($js, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
        ?>;
        const PER = 9;
        </script>
        <div class="menu-layout">
            <div class="menu-sidebar">
                <div class="menu-sidebar-hd">Categories</div>
                <div class="menu-sidebar-list" id="menuSidebar"></div>
            </div>
            <div class="menu-content">
                <div class="menu-content-hd">
                    <div>
                        <div class="menu-content-title" id="menuActiveTitle"></div>
                        <div class="menu-content-count" id="menuCountText"></div>
                    </div>
                    <div class="menu-search-wrap">
                        <i class="fas fa-search menu-search-ico"></i>
                        <input type="search" class="menu-search" id="menuSearch" placeholder="Search items…" autocomplete="off">
                    </div>
                </div>
                <div class="menu-grid" id="menuGrid"></div>
                <div class="menu-empty" id="menuEmpty" style="display:none">
                    <i class="fas fa-magnifying-glass"></i>
                    <p>No items match your search.</p>
                </div>
                <div class="see-more-wrap" id="seeMoreWrap" style="display:none">
                    <div style="text-align:center">
                        <div style="display:flex;align-items:center;justify-content:center;gap:.6rem">
                            <button class="btn-see-more" id="showLessBtn" style="display:none">
                                <i class="fas fa-chevron-up"></i> Show Less
                            </button>
                            <button class="btn-see-more" id="seeMoreBtn">
                                <i class="fas fa-chevron-down"></i> See More
                            </button>
                        </div>
                        <div class="see-more-count" id="seeMoreCount"></div>
                    </div>
                </div>
            </div>
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
                <div class="about-img-wrap">
                    <img src="<?= $baseUrl ?>/assets/images/logo/our-story.jpg" alt="Our Coffee Bar"
                         onerror="this.outerHTML='<div class=\'about-ph-fallback\'><i class=\'fas fa-mug-hot\'></i><span>The heart of The Lounge</span></div>'">
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
                        <div>
                            <div class="pillar-t">Fresh Ingredients</div>
                            <div class="pillar-d">We source locally and seasonally, choosing every ingredient with intention — for flavour, community, and sustainability.</div>
                        </div>
                    </div>
                    <div class="pillar reveal">
                        <div class="pillar-ico"><i class="fas fa-fire"></i></div>
                        <div>
                            <div class="pillar-t">Made to Order</div>
                            <div class="pillar-d">Nothing sits under a lamp here. Your order is prepared fresh the moment it's placed — every time, without exception.</div>
                        </div>
                    </div>
                    <div class="pillar reveal">
                        <div class="pillar-ico"><i class="fas fa-heart"></i></div>
                        <div>
                            <div class="pillar-t">Served with Warmth</div>
                            <div class="pillar-d">Great food is only half the experience. We believe hospitality is an art, and every guest deserves to feel at home.</div>
                        </div>
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
<div class="section-stone" style="text-align:center;padding:4rem 2rem">
    <div class="quote-wrap">
        <span class="quote-mark">"</span>
        <p class="quote-t">Good food is the foundation of genuine happiness — and a great cup is where every <em>morning begins.</em></p>
        <div class="quote-by">— The CJV &amp; TEK team</div>
    </div>
</div>

<!-- ══ FOOTER ══ -->
<footer id="contact">
    <div class="footer-inner">
        <div>
            <div class="footer-logo-row">
                <div class="footer-logo"><img src="<?= $baseUrl ?>/assets/images/logo/logo.png" alt="The Lounge"></div>
                <div>
                    <div class="footer-name">The Lounge</div>
                    <div class="footer-sub-txt">by CJV &amp; TEK</div>
                </div>
            </div>
            <p class="footer-desc">A modern coffee experience blending great service with seamless technology — powered by a smart menu system.</p>
            <a href="javascript:void(0)" onclick="openModal(event)" class="footer-cta">Staff Login <i class="fas fa-arrow-right"></i></a>
        </div>
        <div>
            <div class="footer-col-t">Product</div>
            <ul class="footer-links">
                <li><a href="#featured">Featured</a></li>
                <li><a href="#menu">Menu</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#hours">Hours</a></li>
            </ul>
        </div>
        <div>
            <div class="footer-col-t">Company</div>
            <ul class="footer-links">
                <li><a href="https://www.facebook.com/profile.php?id=61570742037826" target="_blank">Support</a></li>
                <li><a href="<?= $baseUrl ?>/index.php?page=privacy">Privacy</a></li>
                <li><a href="<?= $baseUrl ?>/index.php?page=terms">Terms</a></li>
            </ul>
        </div>
        <div>
            <div class="footer-col-t">Connect</div>
            <div class="footer-contact">
                <span>Lias Town Plaza, Marilao, Bulacan</span>
                <span>Philippines</span>
            </div>
            <a href="<?= htmlspecialchars($maps_url) ?>" target="_blank" rel="noopener noreferrer" class="footer-map-link">
                <i class="fas fa-location-dot"></i> Get Directions <i class="fas fa-arrow-up-right-from-square"></i>
            </a>
            <div class="footer-socials">
                <a href="https://www.facebook.com/profile.php?id=61582005555810" target="_blank" aria-label="Facebook">
                    <i class="fab fa-facebook"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="footer-btm">
        <span class="footer-btm-copy">© <?= date('Y') ?> The Lounge. All rights reserved.</span>
        <span class="footer-btm-tag">Built with precision · Powered by SKD</span>
    </div>
</footer>

<!-- ══ WIFI MODAL ══ -->
<div class="wifi-overlay" id="wifiOverlay" role="dialog" aria-modal="true" aria-label="Connect to WiFi">
    <div class="wifi-modal">
        <div class="wifi-band">
            <button class="wifi-close" id="wifiClose" aria-label="Close"><i class="fas fa-times"></i></button>
            <div class="wifi-modal-icon"><i class="fas fa-wifi"></i></div>
            <div class="wifi-modal-h">Connect to <em>WiFi</em></div>
            <div class="wifi-modal-s">Scan the QR or use the details below</div>
        </div>
        <div class="wifi-body">
            <div class="wifi-qr-wrap">
                <img id="wifiQrImg" src="" alt="WiFi QR Code">
            </div>
            <div class="wifi-details">
                <div class="wifi-row">
                    <span class="wifi-row-label">Network</span>
                    <span class="wifi-row-val" id="wifiSsidDisplay"></span>
                </div>
                <div class="wifi-row">
                    <span class="wifi-row-label">Password</span>
                    <span class="wifi-row-val" id="wifiPassDisplay"></span>
                </div>
                <div class="wifi-row">
                    <span class="wifi-row-label">Security</span>
                    <span class="wifi-row-val">WPA2</span>
                </div>
            </div>
            <p class="wifi-hint">Point your phone camera at the QR code<br>to connect instantly — no typing needed.</p>
        </div>
    </div>
</div>

<!-- ══ LOGIN MODAL ══ -->
<div class="overlay" id="loginOverlay" role="dialog" aria-modal="true" aria-label="Staff Login">
    <div class="modal">
        <div class="modal-band">
            <button class="modal-close" id="modalClose" aria-label="Close"><i class="fas fa-times"></i></button>
            <div class="modal-logo-row">
                <div class="modal-logo">
                    <img src="<?= $baseUrl ?>/assets/images/logo/logo.png" alt="The Lounge" onerror="this.style.display='none'">
                </div>
                <div>
                    <div class="modal-brand">The Lounge by: CJV &amp; TEK</div>
                    <div class="modal-brand-s">Staff Portal</div>
                </div>
            </div>
            <div class="modal-h">Welcome <em>back</em></div>
            <div class="modal-s">Sign in to manage today's service</div>
        </div>
        <div class="modal-body">
            <?php if (!empty($error)):   ?><div class="alert alert-err"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>
            <?php if (!empty($success)): ?><div class="alert alert-ok"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div><?php endif; ?>
            <form action="<?= $baseUrl ?>/index.php?page=landing" method="POST" autocomplete="on">
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

<script src="<?= $baseUrl ?>/assets/vendor/qrcode.min.js"></script>
<script>
/* ── NAV ── */
const nav = document.getElementById('mainNav');
window.addEventListener('scroll', () => nav.classList.toggle('scrolled', scrollY > 30), { passive: true });

/* ── DRAWER ── */
const drawer = document.getElementById('drawer');
const mobBg  = document.getElementById('mobBg');
const openDr  = () => { drawer.classList.add('open'); mobBg.classList.add('open'); document.body.style.overflow = 'hidden'; };
const closeDr = () => { drawer.classList.remove('open'); mobBg.classList.remove('open'); document.body.style.overflow = ''; };
document.getElementById('navToggle')?.addEventListener('click', openDr);
document.getElementById('drawerClose')?.addEventListener('click', closeDr);
mobBg?.addEventListener('click', closeDr);
drawer.querySelectorAll('a').forEach(a => a.addEventListener('click', closeDr));

/* ── LOGIN MODAL ── */
const overlay = document.getElementById('loginOverlay');
function openModal(e) {
    if (e) e.preventDefault();
    overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
    closeDr();
    setTimeout(() => document.getElementById('u_un')?.focus(), 120);
}
const closeModal = () => { overlay.classList.remove('open'); document.body.style.overflow = ''; };
document.getElementById('modalClose')?.addEventListener('click', closeModal);
overlay?.addEventListener('click', e => { if (e.target === overlay) closeModal(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeModal(); closeWifi(); } });
<?php if (!empty($error) || !empty($success)): ?>
document.addEventListener('DOMContentLoaded', openModal);
<?php endif; ?>
function togglePw() {
    const f = document.getElementById('u_pw'), ic = document.getElementById('pwIcon');
    const h = f.type === 'password';
    f.type = h ? 'text' : 'password';
    ic.classList.toggle('fa-eye', !h);
    ic.classList.toggle('fa-eye-slash', h);
}
(function(){
    const modal = document.querySelector('.modal');
    if (!modal) return;
    let sY = 0;
    modal.addEventListener('touchstart', e => { sY = e.touches[0].clientY; }, { passive: true });
    modal.addEventListener('touchend',   e => { if (e.changedTouches[0].clientY - sY > 60) closeModal(); }, { passive: true });
})();

/* ── HOURS ── */
(function () {
    const SCHED = [{ days:[0,1,2,3,4,5,6], open:11, close:21 }];
    const TILES = [
        {label:'Monday',day:1},{label:'Tuesday',day:2},{label:'Wednesday',day:3},{label:'Thursday',day:4},
        {label:'Friday',day:5},{label:'Saturday',day:6},{label:'Sunday',day:0},{label:'Holidays',day:-1},
    ];
    const now = new Date(), dow = now.getDay(), h = now.getHours() + now.getMinutes() / 60;
    const getSched = d => SCHED.find(s => s.days.includes(d));
    const cur = getSched(dow);
    const isOpen = cur && h >= cur.open && h < cur.close;
    const fmt = n => n === 12 ? '12:00 PM' : n > 12 ? (n-12)+':00 PM' : n+':00 AM';
    const pill = document.getElementById('heroPill');
    const pillTxt = document.getElementById('heroPillText');
    if (pill && !isOpen) { pill.className = 'open-pill is-closed'; pillTxt.textContent = 'Currently Closed'; }
    const liveText = document.getElementById('liveText');
    const liveDot  = document.getElementById('liveDot');
    if (liveText) {
        liveText.textContent = isOpen ? `Open right now · Closes at ${fmt(cur.close)}` : "We're currently closed";
        if (!isOpen && liveDot) liveDot.classList.add('closed-dot');
    }
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

/* ── WIFI MODAL ── */
(function () {
    const WIFI_SSID  = '<?= addslashes($wifi_ssid) ?>';
    let   currentPass = '<?= addslashes($wifi_pass_init) ?>';

    const wifiOverlay  = document.getElementById('wifiOverlay');

    function buildQr(pass) {
        // Generated locally — the WiFi password must never leave this network.
        const str = `WIFI:T:WPA;S:${WIFI_SSID};P:${pass};;`;
        try {
            const qr = qrcode(0, 'M');   // 0 = auto-size, 'M' = medium error correction
            qr.addData(str);
            qr.make();
            return qr.createDataURL(6, 0);
        } catch (e) {
            console.error('QR generation failed:', e);
            return '';
        }
    }

    function refreshDisplay(pass) {
        currentPass = pass;
        document.getElementById('wifiQrImg').src              = buildQr(pass);
        document.getElementById('wifiSsidDisplay').textContent = WIFI_SSID;
        document.getElementById('wifiPassDisplay').textContent = pass;
    }

    // Initial render from PHP-seeded value
    refreshDisplay(currentPass);

    function closeWifi() { wifiOverlay.classList.remove('open'); document.body.style.overflow = ''; }
    window.openWifi = function (e) {
        if (e) e.preventDefault();
        wifiOverlay.classList.add('open');
        document.body.style.overflow = 'hidden';
        closeDr();
    };
    window.closeWifi = closeWifi;
    document.getElementById('wifiClose')?.addEventListener('click', closeWifi);
    wifiOverlay?.addEventListener('click', e => { if (e.target === wifiOverlay) closeWifi(); });
})();

/* ── MENU WITH SEE MORE / SHOW LESS ── */
(function () {
    if (typeof MENU_DATA === 'undefined') return;
    const cats = Object.keys(MENU_DATA);

    let active = cats[0];
    let shown  = PER;
    let query  = '';

    const sidebarEl   = document.getElementById('menuSidebar');
    const gridEl      = document.getElementById('menuGrid');
    const emptyEl     = document.getElementById('menuEmpty');
    const seeMoreWrap = document.getElementById('seeMoreWrap');
    const seeMoreBtn  = document.getElementById('seeMoreBtn');
    const showLessBtn = document.getElementById('showLessBtn');
    const seeMoreCnt  = document.getElementById('seeMoreCount');
    const titleEl     = document.getElementById('menuActiveTitle');
    const countEl     = document.getElementById('menuCountText');
    const searchEl    = document.getElementById('menuSearch');

    const esc = s => String(s)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');

    function getFiltered() {
        const items = MENU_DATA[active] || [];
        if (!query) return items;
        const q = query.toLowerCase();
        return items.filter(it =>
            it.name.toLowerCase().includes(q) ||
            (it.desc && it.desc.toLowerCase().includes(q))
        );
    }

    function cardHTML(it) {
        const dim = it.status ? ' dim' : '';
        const img = it.image
            ? `<img src="${esc(it.image)}" alt="${esc(it.name)}" class="menu-thumb${dim}"
                    onerror="this.outerHTML='<div class=\\'menu-thumb-ph${dim}\\'><i class=\\'fas fa-utensils\\'></i></div>'">`
            : `<div class="menu-thumb-ph${dim}"><i class="fas fa-utensils"></i></div>`;
        const badge = it.status === 'oos' ? `<span class="stock-tag oos">Out of Stock</span>`
                    : it.status === 'na'  ? `<span class="stock-tag na">Not Available</span>`
                    : it.vars > 1         ? `<span class="menu-vars">${it.vars} options</span>` : '';
        return `<div class="menu-card">
            ${img}
            <div class="menu-card-body">
                <div class="menu-name">${esc(it.name)}</div>
                ${it.desc ? `<div class="menu-desc">${esc(it.desc)}</div>` : ''}
                <div class="menu-ft">
                    <div class="menu-price${it.status ? ' dim':''}">  ${esc(it.price)}</div>
                    ${badge}
                </div>
            </div>
        </div>`;
    }

    function updateControls(filtered) {
        const hasMore   = shown < filtered.length;
        const hasLess   = shown > PER;
        const remaining = filtered.length - shown;
        seeMoreWrap.style.display = (hasMore || hasLess) ? '' : 'none';
        seeMoreBtn.style.display  = hasMore ? '' : 'none';
        showLessBtn.style.display = hasLess ? '' : 'none';
        seeMoreCnt.textContent    = hasMore
            ? `${remaining} more item${remaining !== 1 ? 's' : ''} remaining` : '';
    }

    function render() {
        const filtered = getFiltered();
        gridEl.style.opacity   = '0';
        gridEl.style.transform = 'translateY(6px)';
        setTimeout(() => {
            gridEl.innerHTML = '';
            if (!filtered.length) {
                emptyEl.style.display     = '';
                seeMoreWrap.style.display = 'none';
            } else {
                emptyEl.style.display = 'none';
                filtered.slice(0, shown).forEach(it =>
                    gridEl.insertAdjacentHTML('beforeend', cardHTML(it))
                );
                updateControls(filtered);
            }
            countEl.textContent = filtered.length
                ? `${filtered.length} item${filtered.length !== 1 ? 's' : ''}` : '';
            gridEl.style.transition = 'opacity .2s ease, transform .2s ease';
            gridEl.style.opacity    = '1';
            gridEl.style.transform  = 'translateY(0)';
        }, 75);
    }

    seeMoreBtn?.addEventListener('click', () => {
        const filtered  = getFiltered();
        const prevShown = shown;
        shown = Math.min(shown + PER, filtered.length);
        filtered.slice(prevShown, shown).forEach(it =>
            gridEl.insertAdjacentHTML('beforeend', cardHTML(it))
        );
        updateControls(filtered);
    });

    showLessBtn?.addEventListener('click', () => {
        shown = Math.max(shown - PER, PER);
        const filtered = getFiltered();
        const cards    = gridEl.querySelectorAll('.menu-card');
        for (let i = cards.length - 1; i >= shown; i--) cards[i].remove();
        updateControls(filtered);
        const remaining = gridEl.querySelectorAll('.menu-card');
        const lastCard  = remaining[remaining.length - 1];
        if (lastCard) lastCard.scrollIntoView({ behavior: 'smooth', block: 'end' });
    });

    function switchCat(cat) {
        if (cat === active) return;
        active = cat; shown = PER;
        searchEl.value = ''; query = '';
        titleEl.textContent = cat;
        sidebarEl.querySelectorAll('.menu-cat-btn').forEach(b =>
            b.classList.toggle('active', b.dataset.cat === cat)
        );
        render();
    }

    cats.forEach(cat => {
        const btn = document.createElement('button');
        btn.className = 'menu-cat-btn' + (cat === active ? ' active' : '');
        btn.dataset.cat = cat;
        btn.innerHTML = `${esc(cat)}<span class="menu-cat-count">${MENU_DATA[cat].length}</span>`;
        btn.addEventListener('click', () => switchCat(cat));
        sidebarEl.appendChild(btn);
    });

    let st;
    searchEl?.addEventListener('input', () => {
        clearTimeout(st);
        st = setTimeout(() => { query = searchEl.value.trim(); shown = PER; render(); }, 220);
    });

    titleEl.textContent = active;
    render();
})();

/* ── SCROLL REVEAL ── */
const io = new IntersectionObserver(entries => {
    entries.forEach((e, i) => {
        if (e.isIntersecting) {
            e.target.style.transitionDelay = (i % 5) * .06 + 's';
            e.target.classList.add('in');
            io.unobserve(e.target);
        }
    });
}, { threshold: .06, rootMargin: '0px 0px -24px 0px' });
document.querySelectorAll('.reveal').forEach(el => io.observe(el));

/* ── SMOOTH ANCHORS ── */
document.querySelectorAll('a[href^="#"]:not([onclick])').forEach(a => {
    a.addEventListener('click', e => {
        const t = document.querySelector(a.getAttribute('href'));
        if (t) { e.preventDefault(); window.scrollTo({ top: t.offsetTop - 64, behavior: 'smooth' }); }
    });
});
</script>
</body>
</html>
