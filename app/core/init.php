<?php
/**
 * ============================================
 * SHARED PHP INITIALIZATION
 * ============================================
 * Common initialization for all PHP files
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Common require_once statements
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/authHelper.php';

// Set common headers
header('Content-Type: application/json');

// Set timezone
date_default_timezone_set('Asia/Manila');

// Common error handling
function handleAjaxError($message, $statusCode = 400) {
    http_response_code($statusCode);
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}

// Common success response
function handleAjaxSuccess($data = null) {
    echo json_encode(['success' => true, 'data' => $data]);
    exit;
}
?>
