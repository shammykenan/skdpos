<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../models/dashboard.php';
require_once __DIR__ . '/../models/reports.php';
require_once __DIR__ . '/../core/authHelper.php';

requireRole(['admin', 'cashier']);

$stats = getDashboardStats($pdo);

// =============================
// 📦 Orders & Sales
// =============================
$recentOrders = getRecentOrders($pdo, 5);
$topItems = getTopSellingItems($pdo, 5);
$dailySales = getDailySales($pdo, 7);
$salesByCategory = getSalesByCategory($pdo);

// =============================
// 🏪 Inventory
// =============================
$lowStock = getLowStockIngredients($pdo);
$ingredientConsumption = getIngredientConsumption($pdo, 7);

// =============================
// 💰 Profit & Performance
// =============================
$profitSummary = getProfitSummary($pdo);
$cashierPerformance = getCashierPerformance($pdo);
$mostProfitableItems = getMostProfitableItems($pdo, 5);

// =============================
// 📄 Load View
// =============================
require_once __DIR__ . '/../views/cashier/reports.php';
