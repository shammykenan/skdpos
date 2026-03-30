<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../core/authHelper.php';

header('Content-Type: application/json');

requireRole(['admin', 'cashier', 'kitchen']);

$validTypes = ['cancel_order', 'void_order', 'cancel_item', 'void_item'];
$type       = trim($_GET['type'] ?? '');

if (!in_array($type, $validTypes)) {
    echo json_encode(['success' => false, 'error' => 'Invalid reason type.']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT reason_id, reason_text, sort_order
        FROM   order_item_reasons
        WHERE  reason_type = ?
          AND  is_active   = 1
        ORDER BY sort_order ASC, reason_text ASC
    ");
    $stmt->execute([$type]);
    $reasons = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $reasons]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}