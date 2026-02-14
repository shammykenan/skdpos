<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../models/users.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../config');
$dotenv->load();
$baseUrl = $_ENV['BASE_URL'];

// Initialize message variables
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';

// Clear session messages after reading them
unset($_SESSION['success'], $_SESSION['error']);

// ---------- LOGIN ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $_SESSION['error'] = 'Username and password are required.';
        header('Location: ' . $baseUrl . '/index.php?page=login');
        exit;
    }

    // Authenticate user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username AND is_active = 1");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        // Set session
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];

        // Update last login
        $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = :id");
        $stmt->execute([':id' => $user['user_id']]);

        // Set login success message
        $_SESSION['login_success'] = true;

        // Redirect based on role
        switch ($user['role']) {
            case 'admin':
                header('Location: ' . $baseUrl . '/index.php?page=admin_dashboard');
                exit;
            case 'cashier':
                header('Location: ' . $baseUrl . '/index.php?page=manage_pos');
                exit;
            case 'kitchen':
                header('Location: ' . $baseUrl . '/index.php?page=manage_orders');
                exit;
            default:
                $_SESSION['error'] = 'Invalid user role.';
                header('Location: ' . $baseUrl . '/index.php?page=login');
                exit;
        }
    } else {
        $_SESSION['error'] = 'Invalid username or password.';
        header('Location: ' . $baseUrl . '/index.php?page=login');
        exit;
    }
}
/*if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])){
        $username = $_POST['username'];
        $password = $_POST['password'];
        $full_name = $_POST['full_name'];
        $email = $_POST['email'];
        $role = $_POST['role'];

        try {
            createUser( $pdo, $username, $password, $full_name, $email = null, $role = 'cashier', $is_active = 1);
        } catch (PDOException $e) {
            echo 'query failed' . $e->getMessage();
        }
}*/
require_once __DIR__.'/../views/auth/login.php';