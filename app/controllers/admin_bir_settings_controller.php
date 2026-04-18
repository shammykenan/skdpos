<?php
/**
 * Controller: BIR Settings
 * Route: index.php?page=bir_settings
 * Role:  admin only
 */

requireRole(['admin']);

$baseUrl = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');

require_once BASE_PATH . '/app/views/admin/bir_settings.php';