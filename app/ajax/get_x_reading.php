<?php
/**
 * AJAX Handler: X-reading (mid-shift snapshot)
 * Route: index.php?page=ajax_x_reading
 *
 * Read-only. Writes nothing, resets nothing, repeatable any number of times.
 * Loaded via router — session, BASE_PATH and $pdo are already available.
 */

require_once BASE_PATH . '/app/models/ZReading.php';
require_once __DIR__ . '/../core/csrfHelper.php';
csrf_require('json');

header('Content-Type: application/json');

$date = trim($_POST['date'] ?? $_GET['date'] ?? date('Y-m-d'));
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo json_encode(['success' => false, 'message' => 'Invalid or missing date.']);
    exit;
}
if ($date > date('Y-m-d')) {
    echo json_encode(['success' => false, 'message' => 'Cannot read a future date.']);
    exit;
}

try {
    $z    = new ZReading($pdo);
    $data = $z->xReading($date);
    echo json_encode(['success' => true, 'data' => $data]);
} catch (Throwable $e) {
    error_log('[get_x_reading] ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Could not build the X-reading. Please try again.']);
}
