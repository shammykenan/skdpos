<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login · Cafeteria POS</title>
    <link rel="icon" type="image/png" href="<?= $baseUrl ?>/assets/images/logo/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Geist:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/common.css">

    <style>
        :root {
            --primary:         #0f4c5c;
            --primary-dark:    #0a3a47;
            --primary-light:   #1f7a8c;
            --secondary:       #ff6b4a;
            --secondary-light: #ff8c70;
            --accent:          #fee440;
            --navy:            #1e2f4e;
            --success:         #2c7a5a;
            --danger:          #d64045;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }

        body {
            font-family: 'Geist', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 2rem 1rem;
            background: var(--primary-dark);
            position: relative;
            overflow: hidden;
        }

        /* ── coral accent bar ── */
        .top-bar {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: var(--secondary);
            z-index: 100;
        }

        /* ══════════════════════════════════════════
           BACKGROUND IMAGE
        ══════════════════════════════════════════ */
        .bg-image {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
        }

        .bg-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .bg-image::after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(8, 30, 40, 0.68);
        }

        /* ══════════════════════════════════════════
           GLASS CARD
        ══════════════════════════════════════════ */
        .glass-card {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 430px;
            border-radius: 26px;
            padding: 2.6rem 2.4rem 2.2rem;
            background: rgba(255, 255, 255, 0.10);
            backdrop-filter: blur(40px) saturate(1.8) brightness(1.05);
            -webkit-backdrop-filter: blur(40px) saturate(1.8) brightness(1.05);
            border: 1px solid rgba(255, 255, 255, 0.22);
            box-shadow:
                0 2px 0 rgba(255, 255, 255, 0.30) inset,
                0 -1px 0 rgba(0, 0, 0, 0.10) inset,
                1px 0 0 rgba(255, 255, 255, 0.08) inset;
            animation: cardIn 0.7s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        .glass-card::before {
            content: '';
            position: absolute;
            top: 0; left: 24px; right: 24px;
            height: 1px;
            background: rgba(255, 255, 255, 0.55);
            border-radius: 50%;
            pointer-events: none;
        }

        @keyframes cardIn {
            from { opacity: 0; transform: translateY(22px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0)    scale(1);    }
        }

        /* ══════════════════════════════════════════
           LOGO — centered stack
        ══════════════════════════════════════════ */
        .logo-row {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.85rem;
            margin-bottom: 1.8rem;
        }

        .logo-box {
            width: 80px;
            height: 80px;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.26);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 1px 0 rgba(255, 255, 255, 0.35) inset;
            overflow: hidden;
            position: relative;
            flex-shrink: 0;
            aspect-ratio: 1 / 1;
        }

        .logo-box::after {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 16px; height: 16px;
            background: var(--secondary);
            border-radius: 0 22px 0 14px;
            z-index: 2;
        }

        .logo-icon {
            color: rgba(255, 255, 255, 0.88);
            font-size: 1.8rem;
            position: relative;
            z-index: 1;
        }

        .logo-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center center;
            border-radius: 22px;
            display: block;
            position: relative;
            z-index: 1;
        }

        .logo-text { text-align: center; }

        .logo-name {
            font-family: 'Instrument Serif', serif;
            font-size: 1.55rem;
            font-weight: 400;
            color: #ffffff;
            letter-spacing: -0.01em;
            line-height: 1.1;
            text-shadow: 0 1px 8px rgba(0,0,0,0.35);
        }

        .logo-sub {
            font-size: 0.72rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.58);
            letter-spacing: 0.14em;
            text-transform: uppercase;
            margin-top: 0.25rem;
        }

        /* ══════════════════════════════════════════
           HEADINGS
        ══════════════════════════════════════════ */
        .card-heading {
            font-family: 'Instrument Serif', serif;
            font-size: 2.1rem;
            font-weight: 400;
            color: #ffffff;
            letter-spacing: -0.02em;
            text-align: center;
            line-height: 1.18;
            margin-bottom: 0.4rem;
            text-shadow: 0 1px 12px rgba(0,0,0,0.4);
        }

        .card-heading em {
            font-style: italic;
            color: var(--secondary-light);
        }

        .card-sub {
            font-size: 0.88rem;
            font-weight: 400;
            color: rgba(255, 255, 255, 0.58);
            text-align: center;
            margin-bottom: 2rem;
            letter-spacing: 0.01em;
        }

        /* ══════════════════════════════════════════
           ALERTS
        ══════════════════════════════════════════ */
        .alert {
            border-radius: 13px;
            padding: 0.75rem 1rem;
            font-size: 0.83rem;
            display: flex;
            gap: 0.6rem;
            align-items: flex-start;
            margin-bottom: 1.3rem;
            background: rgba(214, 64, 69, 0.18);
            border: 1px solid rgba(214, 64, 69, 0.32);
            color: #ffc5c5;
        }

        .alert-success {
            background: rgba(44, 122, 90, 0.18);
            border-color: rgba(44, 122, 90, 0.32);
            color: #a5f3d0;
        }

        /* ══════════════════════════════════════════
           FORM FIELDS
        ══════════════════════════════════════════ */
        .field-group { margin-bottom: 1.1rem; }

        .field-label {
            display: block;
            font-size: 0.74rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.72);
            letter-spacing: 0.09em;
            text-transform: uppercase;
            margin-bottom: 0.48rem;
        }

        .input-wrap { position: relative; }

        .input-ico {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.55);
            font-size: 0.8rem;
            pointer-events: none;
            transition: color 0.2s;
        }

        .input-wrap:focus-within .input-ico {
            color: rgba(255, 255, 255, 0.75);
        }

        .glass-input {
            width: 100%;
            background: rgba(255, 255, 255, 0.07);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 12px;
            padding: 0.82rem 1rem 0.82rem 2.6rem;
            font-family: 'Geist', sans-serif;
            font-size: 0.95rem;
            font-weight: 400;
            color: #ffffff;
            outline: none;
            transition: background 0.2s, border-color 0.2s, box-shadow 0.2s;
            -webkit-font-smoothing: antialiased;
        }

        .glass-input::placeholder {
            color: rgba(255, 255, 255, 0.32);
            font-weight: 300;
        }

        .glass-input:focus {
            background: rgba(0, 0, 0, 0.25);
            border-color: rgba(255, 255, 255, 0.35);
            box-shadow:
                0 0 0 3.5px rgba(31, 122, 140, 0.38),
                0 1px 0 rgba(255, 255, 255, 0.06) inset;
        }

        /* ── autofill fix — prevents browser white background ── */
        .glass-input:-webkit-autofill,
        .glass-input:-webkit-autofill:hover,
        .glass-input:-webkit-autofill:focus,
        .glass-input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 999px rgba(0, 0, 0, 0.32) inset !important;
            box-shadow: 0 0 0 999px rgba(0, 0, 0, 0.32) inset !important;
            -webkit-text-fill-color: #ffffff !important;
            caret-color: #ffffff;
            border-color: rgba(255, 255, 255, 0.35) !important;
            transition: background-color 99999s ease-in-out 0s;
        }

        .pass-toggle {
            position: absolute;
            right: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: rgba(255, 255, 255, 0.45);
            font-size: 0.88rem;
            padding: 0.2rem;
            transition: color 0.2s;
        }

        .pass-toggle:hover { color: rgba(255, 255, 255, 0.75); }

        /* ══════════════════════════════════════════
           FORGOT LINK
        ══════════════════════════════════════════ */
        .forgot-row {
            display: flex;
            justify-content: flex-end;
            margin: -0.15rem 0 1.4rem;
        }

        .glass-link {
            font-size: 0.82rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.55);
            text-decoration: none;
            transition: color 0.2s;
        }

        .glass-link:hover { color: var(--secondary-light); }

        /* ══════════════════════════════════════════
           SIGN IN BUTTON
        ══════════════════════════════════════════ */
        .btn-signin {
            width: 100%;
            padding: 0.9rem;
            border-radius: 12px;
            font-family: 'Geist', sans-serif;
            font-size: 0.93rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            position: relative;
            overflow: hidden;
            background: var(--navy);
            color: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.16);
            box-shadow:
                0 1px 0 rgba(255, 255, 255, 0.18) inset,
                0 8px 22px rgba(0, 0, 0, 0.32);
            transition: transform 0.18s, box-shadow 0.2s, background 0.2s;
            -webkit-font-smoothing: antialiased;
        }

        .btn-signin::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            background: var(--secondary);
            opacity: 0.75;
        }

        .btn-signin:hover {
            background: #263d62;
            transform: translateY(-2px);
            box-shadow:
                0 1px 0 rgba(255, 255, 255, 0.18) inset,
                0 14px 30px rgba(0, 0, 0, 0.42);
        }

        .btn-signin:active { transform: translateY(0); }

        /* ══════════════════════════════════════════
           CAFETERIA FOOTER
        ══════════════════════════════════════════ */
        .cafe-footer {
            margin-top: 1.8rem;
            padding-top: 1.4rem;
            border-top: 1px solid rgba(255, 255, 255, 0.09);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
        }

        .cafe-tagline {
            font-family: 'Instrument Serif', serif;
            font-style: italic;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.48);
            white-space: nowrap;
        }

        .cafe-icons {
            display: flex;
            gap: 0.85rem;
            color: rgba(255, 255, 255, 0.3);
            font-size: 0.88rem;
            flex-shrink: 0;
        }

        .cafe-icons i { transition: color 0.2s; }
        .cafe-icons i:hover { color: var(--secondary-light); }

        /* ══════════════════════════════════════════
           STAGGER ANIMATIONS
        ══════════════════════════════════════════ */
        .a1 { animation: fadeUp 0.55s 0.10s ease both; }
        .a2 { animation: fadeUp 0.55s 0.18s ease both; }
        .a3 { animation: fadeUp 0.55s 0.25s ease both; }
        .a4 { animation: fadeUp 0.55s 0.32s ease both; }
        .a5 { animation: fadeUp 0.55s 0.40s ease both; }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0);    }
        }

        /* ══════════════════════════════════════════
           RESPONSIVE
        ══════════════════════════════════════════ */
        @media (max-width: 500px) {
            .glass-card   { padding: 2rem 1.5rem 1.8rem; }
            .card-heading { font-size: 1.75rem; }
        }
    </style>
</head>
<body>


    <div class="bg-image">
        <img src="<?=$baseUrl?>/assets/images/logo/background.jpeg" alt="bg image">
    </div>

    <div class="glass-card">

        <!-- Logo -->
        <div class="logo-row a1">
            <div class="logo-box">
                <img src="<?=$baseUrl?>/assets/images/logo/logo.png"
                     alt="Cafeteria POS"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <span class="logo-icon" style="display:none;">
                    <i class="fas fa-utensils"></i>
                </span>
            </div>
            <div class="logo-text">
                <div class="brand-text-main">SKD Software Solutions</div>
            <div class="brand-text-sub">Web-Based POS Management System</div>
            </div>
        </div>

        <!-- Heading -->
        <h1 class="card-heading a2">Good to see<br>you <em>again</em></h1>
        <p class="card-sub a2">Sign in to manage today's service</p>

        <!-- Alerts -->
        <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <i class="fas fa-circle-check"></i>
            <?php echo htmlspecialchars($success); ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
        <div class="alert">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <!-- Form -->
        <form action="<?php echo $baseUrl; ?>/index.php?page=login" method="POST" autocomplete="on">

            <div class="field-group a3">
                <label class="field-label" for="username">Username</label>
                <div class="input-wrap">
                    <i class="fas fa-user input-ico"></i>
                    <input
                        class="glass-input"
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Enter your username"
                        value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                        autocomplete="username"
                        required>
                </div>
            </div>

            <div class="field-group a3">
                <label class="field-label" for="passwordField">Password</label>
                <div class="input-wrap">
                    <i class="fas fa-lock input-ico"></i>
                    <input
                        class="glass-input"
                        type="password"
                        id="passwordField"
                        name="password"
                        placeholder="••••••••"
                        autocomplete="current-password"
                        required>
                    <button type="button" class="pass-toggle" onclick="togglePass()" aria-label="Toggle password visibility">
                        <i class="far fa-eye" id="toggleIcon"></i>
                    </button>
                </div>
            </div>

            <div class="forgot-row a3">
                <a href="index.php?page=forgot_password" class="glass-link">Forgot password?</a>
            </div>

            <button type="submit" name="login" class="btn-signin a4">
                <i class="fas fa-arrow-right-to-bracket"></i>
                Sign In
            </button>

        </form>

        <!-- Cafeteria footer -->
        <div class="cafe-footer a5">
            <span class="cafe-tagline">Serving great meals, every day.</span>
            <div class="cafe-icons">
                <i class="fas fa-mug-saucer"></i>
                <i class="fas fa-utensils"></i>
                <i class="fas fa-bread-slice"></i>
                <i class="fas fa-carrot"></i>
                <i class="fas fa-cookie"></i>
            </div>
        </div>

    </div>

    <script src="<?= $baseUrl ?>/assets/js/common.js?v=<?= time() ?>"></script>
    <script>
        function togglePass() {
            const field = document.getElementById('passwordField');
            const icon  = document.getElementById('toggleIcon');
            const isHidden = field.type === 'password';
            field.type = isHidden ? 'text' : 'password';
            icon.classList.toggle('fa-eye',       !isHidden);
            icon.classList.toggle('fa-eye-slash',  isHidden);
        }

        // Use shared alert dismissal utility
        autoDismissAlerts(5000);
    </script>

</body>
</html>