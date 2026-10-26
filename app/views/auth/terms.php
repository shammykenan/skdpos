<!DOCTYPE html>
<html lang="en">
<head>
<link rel="icon" type="image/png" href="<?= $baseUrl ?>/assets/images/logo/logo.png">
<meta charset="UTF-8">
    <?php require_once __DIR__ . "/../../core/csrfHelper.php"; echo csrf_head($baseUrl ?? ""); ?>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Terms of Service · The Lounge</title>

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
    <h1>Terms of Service</h1>
    <div class="meta">Last updated: <?= date('F Y') ?></div>

    <p>
        These Terms govern the use of the POS system provided by SKD Software Solutions.
    </p>

    <h2>1. Use of the System</h2>
    <p>
        The system is intended for managing orders, tracking sales, and supporting business operations.
    </p>

    <h2>2. User Responsibilities</h2>
    <p>
        Users must maintain the confidentiality of their login credentials and use the system responsibly.
    </p>

    <h2>3. Availability</h2>
    <p>
        The system is provided as-is. Continuous availability is not guaranteed.
    </p>

    <h2>4. Limitation of Liability</h2>
    <p>
        We are not liable for losses resulting from downtime, errors, or misuse of the system.
    </p>

    <h2>5. Ownership</h2>
    <p>
        The software remains the property of the developer. Business data belongs to the client.
    </p>

    <h2>6. Changes</h2>
    <p>
        These terms may be updated without prior notice.
    </p>

</div>

</body>
</html>