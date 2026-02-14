<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../models/menu_items.php';
require_once __DIR__ . '/../models/dashboard.php';
require_once __DIR__ . '/../models/users.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../core/itemsHelper.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../config');
$dotenv->load();
$baseUrl = $_ENV['BASE_URL'];

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied. Please log in as an admin.';
    header('Location: ' . $baseUrl . '/index.php?page=logout');
    exit;
}

// Get dashboard statistics
$stats = getDashboardStats($pdo);

// Get recent orders
$recentOrders = getRecentOrders($pdo, 10);

// Get top selling items
$topItems = getTopSellingItems($pdo, 5);

// Get low stock ingredients
$lowStock = getLowStockIngredients($pdo);

// Get daily sales data for chart
$dailySales = getDailySales($pdo, 7);

// Get sales by category
$salesByCategory = getSalesByCategory($pdo);

include __DIR__ . '/../views/admin/admin_dashboard.php';