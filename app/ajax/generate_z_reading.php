<?php
// app/ajax/generate_z_reading.php

require_once BASE_PATH . '/app/models/ZReading.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$date   = trim($_POST['date'] ?? '');
$userId = (int) ($_SESSION['user_id'] ?? 0);

if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo json_encode(['success' => false, 'message' => 'Invalid or missing date.']);
    exit;
}

if ($date > date('Y-m-d')) {
    echo json_encode(['success' => false, 'message' => 'Cannot generate Z-reading for a future date.']);
    exit;
}

// ── Preflight: block generation if any paid orders are still open ─────────────
try {
    $openStmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM orders o
        WHERE DATE(o.created_at) = ?
          AND o.status IN ('pending', 'preparing', 'ready')
          AND EXISTS (
              SELECT 1 FROM payments p
              WHERE p.order_id = o.order_id
                AND p.amount_paid > 0
          )
    ");
    $openStmt->execute([$date]);
    $openCount = (int) $openStmt->fetchColumn();

    if ($openCount > 0) {
        echo json_encode([
            'success'      => false,
            'message'      => "Cannot generate Z-reading: {$openCount} paid order" . ($openCount > 1 ? 's are' : ' is') . " still open. Please complete or void all orders first.",
            'open_orders'  => $openCount,
        ]);
        exit;
    }
} catch (Throwable $e) {
    error_log('[generate_z_reading preflight] ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Could not verify open orders. Please try again.']);
    exit;
}

// ── Generate ──────────────────────────────────────────────────────────────────
try {
    $zReading = new ZReading($pdo);
    $summary  = $zReading->generate($date, $userId);
    echo json_encode(['success' => true, 'data' => $summary]);

} catch (RuntimeException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (Throwable $e) {
    error_log('[generate_z_reading] ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred. Please try again.']);
}