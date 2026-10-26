<!DOCTYPE html>
<html lang="en">
<head>
<link rel="icon" type="image/png" href="<?= $baseUrl ?>/assets/images/logo/logo.png">
<meta charset="UTF-8">
    <?php require_once __DIR__ . "/../../core/csrfHelper.php"; echo csrf_head($baseUrl ?? ""); ?>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Privacy Policy · The Lounge</title>

<link href="<?= $baseUrl ?>/assets/vendor/fonts/playfair.css" rel="stylesheet">

<style>
:root {
    --navy:#1e2f4e; --orange:#f4a024; --bg:#fdfcf9;
    --text:#1a1a2e; --text-mute:#6b7280; --border:#e5e7eb;
}
body {
    font-family:'Plus Jakarta Sans',sans-serif;
    background:var(--bg); color:var(--text);
    margin:0;
}

/* HEADER */
.header {
    display:flex; align-items:center; justify-content:space-between;
    padding:1rem 1.5rem; border-bottom:1px solid var(--border);
    background:white;
}
.brand {
    display:flex; align-items:center; gap:.6rem;
    text-decoration:none;
}
.logo {
    width:36px; height:36px; border-radius:50%;
    overflow:hidden; border:2px solid rgba(244,160,36,.3);
}
.logo img { width:100%; height:100%; object-fit:contain; }
.brand-name {
    font-family:'Playfair Display',serif;
    font-size:1rem; color:var(--navy); font-weight:700;
}

/* CONTENT */
.container {
    max-width:900px; margin:0 auto; padding:4rem 1.5rem;
}
h1 {
    font-family:'Playfair Display',serif;
    font-size:2.4rem; margin-bottom:.5rem; color:var(--navy);
}
h2 {
    margin-top:2rem; font-size:1.05rem; color:var(--navy);
}
p {
    font-size:.9rem; line-height:1.8; color:var(--text-mute);
}
.meta {
    font-size:.75rem; color:var(--text-mute); margin-bottom:1.5rem;
}

/* BACK */
.back {
    font-size:.9rem; text-decoration:none; color:var(--orange);
}
.back:hover { opacity:.7; }
</style>
</head>
<body>
<!-- CONTENT -->
<div class="container">
    <a href="<?= $baseUrl ?>/" class="back">← Back To Home</a>
    <h1>Privacy Policy</h1>
    <div class="meta">Last updated: <?= date('F Y') ?></div>

    <p>
        This Privacy Policy applies to the point-of-sale (POS) system provided by SKD Software Solutions. 
        It explains how system data is collected, used, and protected.
    </p>

    <h2>1. Information We Collect</h2>
    <p>
        The system may collect staff account credentials, transaction records, and operational data necessary 
        for processing orders and generating reports.
    </p>

    <h2>2. Use of Data</h2>
    <p>
        Data is used strictly for business operations including sales processing, reporting, and system management.
    </p>

    <h2>3. Data Ownership</h2>
    <p>
        All business data entered into the system remains the property of the client operating the POS.
    </p>

    <h2>4. Data Security</h2>
    <p>
        Reasonable security measures are implemented to protect system data, though absolute security cannot be guaranteed.
    </p>

    <h2>5. Data Sharing</h2>
    <p>
        We do not sell or share system data with third parties unless required by law.
    </p>

    <h2>6. Changes</h2>
    <p>
        This policy may be updated as the system evolves.
    </p>

</div>

</body>
</html>