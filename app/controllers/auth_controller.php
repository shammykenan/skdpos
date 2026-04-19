<?php
// app/controllers/landing_controller.php
// Handles: POST login submission + GET landing page (with live menu data)
// Route:   case 'landing' (and optionally the default case) in index.php
// No requireRole() — this page is public.

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../models/users.php';
require_once __DIR__ . '/../models/menu_items.php';
require_once __DIR__ . '/../models/categories.php';
require_once __DIR__ . '/../core/authHelper.php';
require_once __DIR__ . '/../core/itemsHelper.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../config');
$dotenv->load();
$baseUrl = $_ENV['BASE_URL'];

$success = $_SESSION['success'] ?? '';
$error   = $_SESSION['error']   ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// ================================================================
// POST — Login form submission
// ================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $_SESSION['error'] = 'Username and password are required.';
        header('Location: ' . $baseUrl . '/index.php?page=landing');
        exit;
    }

    // IP-based rate limiting
    checkLoginRateLimit($pdo, $baseUrl);

    // Pre-record as failed (overwritten below on success)
    recordLoginAttempt($pdo, $username, false);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username AND is_active = 1");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        clearLoginAttempts($pdo, $_SERVER['REMOTE_ADDR']);

        // Mark last attempt as successful
        $pdo->prepare("
            UPDATE login_attempts
            SET success = 1
            WHERE ip_address = :ip
            ORDER BY attempt_time DESC
            LIMIT 1
        ")->execute([':ip' => $_SERVER['REMOTE_ADDR']]);

        // Set session
        $_SESSION['user_id']   = $user['user_id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['role']      = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];

        // Update last login timestamp
        $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = :id")
            ->execute([':id' => $user['user_id']]);

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
                header('Location: ' . $baseUrl . '/index.php?page=landing');
                exit;
        }

    } else {
        $_SESSION['error'] = 'Invalid username or password.';
        header('Location: ' . $baseUrl . '/index.php?page=landing');
        exit;
    }
}

// ================================================================
// GET — Already logged in? Redirect away.
// ================================================================
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'] ?? '';
    if ($role === 'admin') {
        header('Location: ' . $baseUrl . '/index.php?page=admin_dashboard');
    } elseif ($role === 'cashier') {
        header('Location: ' . $baseUrl . '/index.php?page=manage_pos');
    } else {
        header('Location: ' . $baseUrl . '/index.php?page=manage_orders');
    }
    exit;
}

// ================================================================
// GET — Build landing page data
// ================================================================

// Ensure correct fetch mode ($pdo already created by config/db.php)
if (isset($pdo)) {
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} else {
    try {
        $pdo = new PDO(
            "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8mb4",
            $_ENV['DB_USER'],
            $_ENV['DB_PASS'],
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

$raw_items  = getAllItemsWithVariants($pdo);
$categories = getAllCategories($pdo);

// Load all variants grouped by item_id
$variants_grouped = [];
try {
    $stmt = $pdo->query("SELECT * FROM menu_variants ORDER BY item_id, price ASC");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $v) {
        $variants_grouped[(int)$v['item_id']][] = $v;
    }
} catch (Exception $e) {
    $variants_grouped = [];
}

// Build display-ready items (available + priced only)
$menu_items = [];
foreach ($raw_items as $item) {
    if (!$item['is_available']) continue;

    $item_id  = (int)$item['item_id'];
    $variants = $variants_grouped[$item_id] ?? [];
    $active_v = array_values(array_filter($variants, fn($v) => $v['is_active']));
    $has_v    = !empty($variants);

    if ($has_v && !empty($active_v)) {
        $prices    = array_column($active_v, 'price');
        $price_min = min($prices);
        $price_max = max($prices);
        $display_price = $price_min == $price_max
            ? '₱' . number_format($price_min, 2)
            : '₱' . number_format($price_min, 2) . ' – ₱' . number_format($price_max, 2);
    } elseif (!$has_v && (float)$item['base_price'] > 0) {
        $display_price = '₱' . number_format((float)$item['base_price'], 2);
        $price_min     = (float)$item['base_price'];
    } else {
        continue; // skip unpriced items — they won't show on the public page
    }

    $menu_items[] = [
        'item_id'       => $item_id,
        'item_name'     => $item['item_name'],
        'description'   => $item['description'] ?? '',
        'category_id'   => $item['category_id'],
        'category_name' => $item['category_name'],
        'image_url'     => $item['image_url'],
        'display_price' => $display_price,
        'has_variants'  => $has_v,
        'variants'      => $active_v,
        'price_min'     => $price_min,
    ];
}

// Group by category (alphabetical)
$menu_by_category = [];
foreach ($menu_items as $item) {
    $menu_by_category[$item['category_name']][] = $item;
}
ksort($menu_by_category);

// Featured = first 6 available items
$featured_items = array_slice($menu_items, 0, 6);

include __DIR__ . '/../views/auth/landing.php';