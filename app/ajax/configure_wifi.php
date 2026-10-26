<?php
// app/ajax/configure_wifi.php
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../models/app_settings.php';
require_once __DIR__ . '/../core/csrfHelper.php';
csrf_require('json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get current WiFi settings
    $ssid = getSetting($pdo, 'wifi_ssid');
    $password = getSetting($pdo, 'wifi_password');
    echo json_encode(['success' => true, 'ssid' => $ssid, 'password' => $password]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ssid = trim($_POST['ssid'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($ssid) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'SSID and password are required']);
        exit;
    }

    $result1 = setSetting($pdo, 'wifi_ssid', $ssid);
    $result2 = setSetting($pdo, 'wifi_password', $password);

    if ($result1 && $result2) {
        echo json_encode(['success' => true, 'message' => 'WiFi configuration saved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save WiFi configuration']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
