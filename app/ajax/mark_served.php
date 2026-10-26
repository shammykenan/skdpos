<?php
// app/ajax/mark_served.php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../core/authHelper.php';
require_once __DIR__ . '/../core/tablehelper.php';
require_once __DIR__ . '/../core/csrfHelper.php';
csrf_require('json');

header('Content-Type: application/json');

// Ensure PDO exceptions are enabled (critical for error catching)
// If your db.php already does this, you can remove the next line.
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (!hasRole('cashier') && !hasRole('admin')) {
    echo json_encode(['success' => false, 'error' => 'Access denied. Cashier or admin only.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit;
}

$order_id = (int) ($_POST['order_id'] ?? 0);

if (!$order_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid order ID.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // ── Fetch order ───────────────────────────────────────────────────────────
    $stmt = $pdo->prepare("
        SELECT order_id, order_number, status, cashier_id
        FROM orders WHERE order_id = ? LIMIT 1
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Order not found.']);
        exit;
    }

    if ($order['status'] !== 'ready') {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'error'   => 'Only ready orders can be marked as served. Current status: ' . $order['status'] . '.',
        ]);
        exit;
    }

    // ── Mark order as completed (only if status is still 'ready') ─────────────
    $stmt = $pdo->prepare("
        UPDATE orders 
        SET status = 'completed', completed_at = NOW() 
        WHERE order_id = ? AND status = 'ready'
    ");
    $stmt->execute([$order_id]);

    // CRITICAL FIX: verify that the update actually changed a row
    if ($stmt->rowCount() === 0) {
        throw new Exception("Order status could not be updated. The order may have already been completed or is no longer 'ready'.");
    }

    // ── Mark all non‑terminal items as completed ─────────────────────────────
    $pdo->prepare("
        UPDATE order_items
        SET status = 'completed'
        WHERE order_id = ? AND status NOT IN ('cancelled', 'voided')
    ")->execute([$order_id]);

    // ── Audit log ────────────────────────────────────────────────────────────
    $pdo->prepare("
        INSERT INTO activity_logs (user_id, action, details, ip_address)
        VALUES (?, 'mark_served', ?, ?)
    ")->execute([
        (int) $_SESSION['user_id'],
        json_encode([
            'order_id'     => $order_id,
            'order_number' => $order['order_number'],
            'performed_by' => (int) $_SESSION['user_id'],
            'note'         => 'Order marked as served. Payment was collected at POS.',
        ]),
        $_SERVER['REMOTE_ADDR'] ?? null,
    ]);

    $pdo->commit();

    echo json_encode([
        'success'      => true,
        'order_id'     => $order_id,
        'order_number' => $order['order_number'],
        'order_status' => 'completed',
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}