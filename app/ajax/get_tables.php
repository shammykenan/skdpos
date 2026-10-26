<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../core/authHelper.php';
require_once __DIR__ . '/../core/tablehelper.php';
require_once __DIR__ . '/../core/csrfHelper.php';
csrf_require('json');

header('Content-Type: application/json');

if (!hasRole('admin') && !hasRole('cashier')) {
    echo json_encode(['success' => false, 'error' => 'Access denied.']);
    exit;
}

try {
    $tables     = getAllTables($pdo);
    $tableStats = getTableStats($pdo);

    // Format the since-time in 12-hour for JS consumption
    foreach ($tables as &$table) {
        $table['since_time'] = '';
        if ($table['status'] === 'occupied' && !empty($table['order_started_at'])) {
            $table['since_time'] = date('g:i A', strtotime($table['order_started_at']));
        }
    }
    unset($table);

    echo json_encode([
        'success' => true,
        'tables'  => $tables,
        'stats'   => $tableStats,
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}