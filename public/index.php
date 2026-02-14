<?php
session_start();

define('BASE_PATH', realpath(__DIR__ . '/../'));

require_once BASE_PATH . '/config/db.php';
require_once BASE_PATH . '/app/core/AuthHelper.php';
$page = $_GET['page'] ?? 'login';

switch ($page) {
    case 'login':
        require_once BASE_PATH . '/app/controllers/auth_controller.php';
        break;
    case 'manage_users':
        requireRole(['admin']);
        require_once BASE_PATH . '/app/controllers/admin_manage_users_controller.php';
        break;
    case 'admin_dashboard':
        requireRole(['admin']);
        require_once BASE_PATH . '/app/controllers/admin_dashboard_controller.php';
        break;

    case 'manage_menu':
        requireRole(['admin']);
        require_once BASE_PATH . '/app/controllers/admin_manage_menu_controller.php';
        break;

    case 'manage_ingredients':
        requireRole(['admin']);
        require_once BASE_PATH . '/app/controllers/admin_manage_ingredients_controller.php';
        break;

    case 'manage_recipes':
        requireRole(['admin']);
        require_once BASE_PATH . '/app/controllers/admin_manage_recipes_controller.php';
        break;

    case 'manage_addons':
        requireRole(['admin']);
        require_once BASE_PATH . '/app/controllers/admin_manage_addons_controller.php';
        break;

    //cashier
    case 'manage_pos':
        requireRole(['cashier', 'admin']);
        require_once BASE_PATH . '/app/controllers/pos_controller.php';
        break;
    //kitchen
    case 'manage_orders':
        requireRole(['cashier', 'admin', 'kitchen']);
        require_once BASE_PATH . '/app/controllers/manage_orders_controller.php';
        break;
    case 'manage_reports':
        requireRole(['cashier', 'admin']);
        require_once BASE_PATH . '/app/controllers/manage_reports_controller.php';
        break;
    case 'logout':
        require_once BASE_PATH . '/config/logout.php';
        break;

    default:
        http_response_code(404);
        echo "Page not found";
        break;
}