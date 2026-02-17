<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5">
    <title>Login · Cafeteria POS</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* ------------------------------------------------------------
           MODERN CAFETERIA PALETTE – Teal + Coral + Navy
        ------------------------------------------------------------ */
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
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--light-bg);
            color: var(--dark);
            font-size: 14px;
            line-height: 1.5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(15, 76, 92, 0.03) 0%, transparent 30%),
                radial-gradient(circle at 90% 80%, rgba(255, 107, 74, 0.03) 0%, transparent 35%);
        }
        
        /* ------ MAIN AUTH CARD – matches dashboard card style ------ */
        .auth-card {
            background: white;
            border-radius: 28px;
            border: 1px solid rgba(203, 213, 224, 0.5);
            box-shadow: 0 25px 40px -12px rgba(15, 76, 92, 0.2);
            padding: 3rem 3rem;
            width: 100%;
            max-width: 520px;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(4px);
            transition: transform 0.25s, box-shadow 0.25s;
        }
        
        .auth-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 30px 50px -12px rgba(15, 76, 92, 0.3);
            border-color: var(--primary-light);
        }
        
        .auth-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            opacity: 0.9;
        }
        
        /* ============================================================
           LOGO CONTAINER – LARGER, MORE PROMINENT
        ============================================================ */
        .logo-container {
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
        }
        
        /* Larger logo frame */
        .logo-frame {
            width: 180px;  /* Increased from 120px */
            height: 180px; /* Increased from 120px */
            margin: 0 auto 1.5rem;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Outer glow – larger */
        .logo-frame::before {
            content: '';
            position: absolute;
            top: -10px;
            left: -10px;
            right: -10px;
            bottom: -10px;
            border-radius: 48px; /* Increased to match larger size */
            background: linear-gradient(145deg, var(--primary-light), var(--secondary-light));
            opacity: 0.25;
            z-index: 0;
            filter: blur(2px);
        }
        
        /* Inner white border – larger */
        .logo-frame::after {
            content: '';
            position: absolute;
            top: -4px;
            left: -4px;
            right: -4px;
            bottom: -4px;
            border-radius: 44px; /* Increased */
            background: white;
            z-index: 1;
            box-shadow: 0 4px 12px rgba(0,0,0,0.02);
        }
        
        /* The actual image – larger */
        .logo-image {
            width: 160px;  /* Increased from 110px */
            height: 160px; /* Increased from 110px */
            object-fit: contain;
            border-radius: 40px; /* Increased */
            position: relative;
            z-index: 2;
            background: white;
            padding: 15px; /* Increased padding for better presence */
            box-shadow: 0 20px 30px -10px rgba(15, 76, 92, 0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 3px solid transparent;
            background-clip: padding-box;
        }
        
        /* Hover effect – more dramatic for larger logo */
        .auth-card:hover .logo-image {
            transform: scale(1.05);
            border: 3px solid var(--primary-light);
            box-shadow: 0 25px 35px -8px rgba(15, 76, 92, 0.4);
        }
        
        /* Decorative glow behind logo – larger */
        .logo-glow {
            position: absolute;
            width: 220px; /* Increased */
            height: 220px; /* Increased */
            background: radial-gradient(circle, rgba(255,107,74,0.15) 0%, transparent 70%);
            border-radius: 50%;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 0;
            filter: blur(8px);
        }
        
        /* Brand title – adjusted spacing */
        .form-title {
            font-size: 2.2rem; /* Slightly larger */
            font-weight: 700;
            color: var(--navy);
            margin-bottom: 0.25rem;
            letter-spacing: -0.02em;
            text-align: center;
        }
        
        .form-subtitle {
            color: var(--warm-gray);
            text-align: center;
            margin-bottom: 2rem;
            font-size: 1rem; /* Slightly larger */
        }
        
        /* ------ FORM STYLES – matches dashboard forms ------ */
        .form-label {
            font-weight: 600;
            color: var(--navy);
            margin-bottom: 0.5rem;
            font-size: 0.9rem; /* Slightly larger */
        }
        
        .form-control, .form-select {
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 0.85rem 1.2rem; /* Larger padding for better UX */
            font-size: 1rem;
            transition: all 0.2s;
            background: white;
            width: 100%;
            height: auto;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(15, 76, 92, 0.1);
            outline: none;
        }
        
        .form-control::placeholder {
            color: #a0aec0;
            font-size: 0.95rem;
        }
        
        /* ------ BUTTON – larger, more prominent ------ */
        .btn {
            padding: 0.9rem 1.8rem; /* Larger padding */
            border-radius: 40px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.18s;
            letter-spacing: 0.3px;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
            box-shadow: 0 8px 16px rgba(15, 76, 92, 0.15);
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 12px 20px rgba(15, 76, 92, 0.25);
        }
        
        .btn-primary:active {
            transform: translateY(0);
            box-shadow: 0 4px 8px rgba(15, 76, 92, 0.2);
        }
        
        .btn-block {
            width: 100%;
        }
        
        /* ------ ALERTS – matches dashboard alerts ------ */
        .alert {
            border-radius: 18px;
            border: none;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            position: relative;
        }
        
        .alert-success {
            background: rgba(44, 122, 90, 0.12);
            color: #1e4a3b;
            border-left: 6px solid var(--success);
        }
        
        .alert-danger {
            background: rgba(214, 64, 69, 0.08);
            color: #9e3a34;
            border-left: 6px solid var(--danger);
        }
        
        .alert i {
            margin-right: 0.5rem;
        }
        
        /* ------ PASSWORD TOGGLE ------ */
        .password-wrapper {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--warm-gray);
            cursor: pointer;
            font-size: 1.2rem;
            padding: 0.25rem;
            z-index: 5;
        }
        
        .password-toggle:hover {
            color: var(--primary);
        }
        
        /* ------ DECORATIVE ELEMENTS ------ */
        .cafeteria-icon-row {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin: 2rem 0 0.5rem;
            color: var(--primary-light);
            font-size: 1.1rem;
            opacity: 0.6;
        }
        
        .cafeteria-icon-row i {
            transition: opacity 0.2s, color 0.2s;
        }
        
        .cafeteria-icon-row i:hover {
            opacity: 1;
            color: var(--secondary);
            transform: scale(1.1);
        }
        
        hr {
            border: none;
            border-top: 1px solid var(--border);
            opacity: 0.5;
            margin: 2rem 0 0.5rem;
        }
        
        /* ------ RESPONSIVE – adjusted for larger logo ------ */
        @media (max-width: 576px) {
            .auth-card {
                padding: 2rem 1.5rem;
            }
            
            .form-title {
                font-size: 2rem;
            }
            
            .logo-frame {
                width: 140px;
                height: 140px;
            }
            
            .logo-image {
                width: 120px;
                height: 120px;
                padding: 10px;
            }
            
            .logo-glow {
                width: 180px;
                height: 180px;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row justify-content-center align-items-center min-vh-100 mx-0">
            <div class="col-12 col-md-8 col-lg-6 col-xl-5 d-flex justify-content-center">
                <div class="auth-card">
                    
                    <!-- ==================================================
                         LOGO CONTAINER - LARGER, MORE PROMINENT
                         ================================================== -->
                    <div class="logo-container">
                        <div class="logo-frame">
                            <!-- Glow effect behind logo -->
                            <div class="logo-glow"></div>
                            
                            <!-- YOUR IMAGE GOES HERE - NOW 160x160px -->
                            <img src="<?=$baseUrl?>/assets/images/logo/logo.png" 
                                 alt="Cafeteria POS" 
                                 class="logo-image"
                                 onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22160%22%20height%3D%22160%22%20viewBox%3D%220%200%20160%20160%22%3E%3Crect%20width%3D%22160%22%20height%3D%22160%22%20fill%3D%22%23f0f5fa%22%20rx%3D%2230%22%2F%3E%3Ctext%20x%3D%2280%22%20y%3D%22100%22%20font-size%3D%2260%22%20text-anchor%3D%22middle%22%20fill%3D%22%230f4c5c%22%20font-family%3D%22Inter%22%3E🍽%3C%2Ftext%3E%3C%2Fsvg%3E');">
                        </div>
                        
                        <h1 class="form-title">Cafeteria POS</h1>
                        <p class="form-subtitle">Sign in to continue to dashboard</p>
                    </div>

                    <!-- Alerts -->
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo htmlspecialchars($success); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-container">
                        <!-- Login Form -->
                        <div id="loginForm" class="auth-form <?php echo (!isset($_SESSION['activeTab']) || $_SESSION['activeTab'] === 'login') ? 'active' : ''; ?>">
                            <form action="index.php?page=login" method="POST">
                                <!-- Username field -->
                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="username" placeholder="Enter your username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                                    </div>
                                </div>
                                
                                <!-- Password field with inline toggle -->
                                <div class="mb-4">
                                    <label class="form-label">Password</label>
                                    <div class="password-wrapper">
                                        <input type="password" class="form-control" id="passwordField" name="password" placeholder="••••••••" required>
                                        <span class="password-toggle" onclick="togglePasswordVisibility()">
                                            <i class="far fa-eye" id="toggleIcon"></i>
                                        </span>
                                    </div>
                                </div>
                                
                                <button type="submit" name="login" class="btn btn-primary btn-block w-100">
                                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                                </button>
                            </form>

                            <!-- Uncomment if you want register link -->
                            <!--
                            <div class="form-footer">
                                <p>Don't have an account? <a href="#" id="showRegister">Create one</a></p>
                            </div>
                            -->
                        </div>
                    </div>
                    
                    <!-- Decorative cafeteria elements -->
                    <hr>
                    <div class="cafeteria-icon-row">
                        <i class="fas fa-mug-saucer"></i>
                        <i class="fas fa-utensils"></i>
                        <i class="fas fa-bread-slice"></i>
                        <i class="fas fa-carrot"></i>
                        <i class="fas fa-cookie"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Password visibility toggle
        function togglePasswordVisibility(fieldId = 'passwordField', iconElement = null) {
            const passwordInput = document.getElementById(fieldId);
            let toggleIcon;
            
            if (iconElement) {
                toggleIcon = iconElement.querySelector('i');
            } else {
                toggleIcon = document.getElementById('toggleIcon');
            }
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
        
        // Auto-dismiss alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>