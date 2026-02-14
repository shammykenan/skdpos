<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?=$baseUrl?>/assets/css/login.css">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-12 col-md-10 col-lg-8 col-xl-6">
                <div class="auth-card">
                    <div class="logo-container">
                        <img src="<?=$baseUrl?>/assets/images/logo/brand.png" alt="Logo" class="logo">
                    </div>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($success); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-container">
                        <!-- Login Form (Default) -->
                        <div id="loginForm" class="auth-form <?php echo (!isset($_SESSION['activeTab']) || $_SESSION['activeTab'] === 'login') ? 'active' : ''; ?>">
                            <h2 class="form-title" id="title">CELESTIAL BREWS</h2>
                            <form action="index.php?page=login" method="POST">
                                <div class="mb-3">
                                    <input type="text" class="form-control" name="username" placeholder="Username" required>
                                </div>
                                <div class="mb-4">
                                    <input type="password" class="form-control" name="password" placeholder="Password" required>
                                </div>
                                <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
                            </form>

                            <!--<div class="form-footer">
                                <p>Don't have an account? <a href="#" id="showRegister">Create one</a></p>
                            </div>
                        </div>

                       Register Form
                        <div id="registerForm" class="auth-form <?php echo (isset($_SESSION['activeTab']) && $_SESSION['activeTab'] === 'register') ? 'active' : ''; ?>">
                            <h2 class="form-title">Create Account</h2>
                            <form action="index.php?page=login" method="POST">
                                <div class="mb-3">
                                    <input type="text" class="form-control" name="username" placeholder="Username" required>
                                </div>
                                <div class="mb-3">
                                    <input type="password" class="form-control" name="password" placeholder="Password" required>
                                </div>
                                <div class="mb-3">
                                    <input type="text" class="form-control" name="full_name" placeholder="Full Name" required>
                                </div>
                                <div class="mb-3">
                                    <input type="email" class="form-control" name="email" placeholder="Email" required>
                                </div>
                                <div class="mb-4">
                                    <select class="form-select" name="role" id="role">
                                        <option value="admin">Admin</option>
                                        <option value="cashier">Cashier</option>
                                        <option value="kitchen">Kitchen Staff</option>
                                    </select>
                                </div>
                                <button type="submit" name="register" class="btn btn-primary w-100">Create Account</button>
                            </form>
                            <div class="form-footer">
                                <p>Already have an account? <a href="#" id="showLogin">Login</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?=$baseUrl?>/assets/js/login.js"></script>
</body>
</html>