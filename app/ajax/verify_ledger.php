<?php
/**
 * AJAX Handler: verify the sales-ledger hash chain.
 * Route: index.php?page=ajax_verify_ledger  (admin only)
 *
 * Walks every entry and confirms each one still hashes to its recorded value
 * and follows from the entry before it. Read-only.
 */

require_once BASE_PATH . '/app/core/GrandTotal.php';
require_once __DIR__ . '/../core/csrfHelper.php';
csrf_require('json');

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Admin only.']);
    exit;
}

try {
    $result = gt_verify($pdo);

    $last = gt_last_entry($pdo);
    $result['ledger_hash'] = $last ? $last['entry_hash'] : str_repeat('0', 64);

    $result['message'] = $result['ok']
        ? sprintf('Ledger intact — %d entries, Grand Total %s.',
                  $result['entries'], number_format($result['grand_total'], 2))
        : sprintf('LEDGER ALTERED at entry %s: %s', $result['broken_at'], $result['reason']);

    // A failed verification is itself worth recording.
    if (!$result['ok']) {
        try {
            $pdo->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address)
                           VALUES (?, 'ledger_verify_failed', ?, ?)")
                ->execute([
                    (int) $_SESSION['user_id'],
                    json_encode(['broken_at' => $result['broken_at'], 'reason' => $result['reason']]),
                    $_SERVER['REMOTE_ADDR'] ?? null,
                ]);
        } catch (Throwable $e) {
            error_log('[verify_ledger] could not log failure: ' . $e->getMessage());
        }
    }

    echo json_encode(['success' => true, 'data' => $result]);

} catch (Throwable $e) {
    error_log('[verify_ledger] ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Could not verify the ledger.']);
}
