<?php
require_once __DIR__ . '/../core/init.php';

requireRole(['admin']);

try {
    $stmt = $pdo->query("
        SELECT DISTINCT
            u.user_id,
            u.full_name
        FROM users u
        INNER JOIN payments p ON p.processed_by = u.user_id
        WHERE u.role = 'admin'
          AND u.is_active = 1
        ORDER BY u.full_name ASC
    ");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $admins]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}