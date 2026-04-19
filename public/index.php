<?php
ini_set('session.cookie_path', '/');
session_start();
date_default_timezone_set('Asia/Manila');
define('BASE_PATH', realpath(__DIR__ . '/../'));

require_once BASE_PATH . '/config/db.php';
require_once BASE_PATH . '/app/core/authHelper.php';
$page = $_GET['page'] ?? 'login';

switch ($page) {
    case 'landing':
    require_once BASE_PATH . '/app/controllers/auth_controller.php';
    break;
    case 'backup_database':
        requireRole(['admin']);
        require_once BASE_PATH . '/app/controllers/backup_database.php';
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
    case 'manage_variants':
        requireRole(['admin']);
        require_once BASE_PATH . '/app/controllers/admin_manage_variants_controller.php';
        break;
    case 'bir_settings':
    requireRole(['admin']);
    require_once BASE_PATH . '/app/controllers/admin_bir_settings_controller.php';
    break;

    case 'ajax_save_bir_settings':
        requireRole(['admin']);
        require_once BASE_PATH . '/app/ajax/save_bir_settings.php';
        exit;
    case 'ajax_generate_z_reading':
        requireRole(['admin']);
        require_once BASE_PATH . '/app/ajax/generate_z_reading.php';
        exit;
        
    case 'print_z_reading':
        requireRole(['admin']);
        require_once BASE_PATH . '/app/print/print-z-reading.php';
        exit;
    //cashier
    case 'manage_pos':
        requireRole(['cashier', 'admin']);
        require_once BASE_PATH . '/app/controllers/pos_controller.php';
        break;
    case 'manage_tables':
        requireRole(['cashier', 'admin']);
        require_once BASE_PATH . '/app/controllers/manage_tables_controller.php';
        break;
    case 'reports':
        requireRole(['cashier', 'admin']);
        require_once BASE_PATH . '/app/controllers/view_reports_controller.php';
        break;
    case 'ajax_apply_discount':
    requireRole(['admin', 'cashier']);
    require_once BASE_PATH . '/app/ajax/apply_discount.php';
    exit;
    //kitchen
    case 'manage_orders':
        requireRole(['cashier', 'admin', 'kitchen']);
        require_once BASE_PATH . '/app/controllers/manage_orders_controller.php';
        break;
    case 'manage_reports':
        requireRole(['cashier', 'admin']);
        require_once BASE_PATH . '/app/controllers/manage_reports_controller.php';
        break;
    case 'forgot_password':
        require_once BASE_PATH . '/app/controllers/forgot_password_controller.php';
        break;

    case 'reset_password':
        require_once BASE_PATH . '/app/controllers/reset_password_controller.php';
        break;
    case 'print_receipt':
        require_once BASE_PATH . '/app/print/print-receipt.php';
        exit;
    case 'print_closeout':
        require_once BASE_PATH . '/app/print/print-closeout.php';
        exit;
    case 'logout':
        require_once BASE_PATH . '/config/logout.php';
        break;

    // update default to serve the landing page instead of 404
default:
    require_once BASE_PATH . '/app/controllers/auth_controller.php';
    break;
}