<?php
// app/ajax/mark_served.php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../core/authHelper.php';
require_once __DIR__ . '/../core/tableHelper.php';

header('Content-Type: application/json');

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

    // ── Mark order as completed ───────────────────────────────────────────────
    $pdo->prepare("
        UPDATE orders
        SET status = 'completed', completed_at = NOW()
        WHERE order_id = ?
    ")->execute([$order_id]);

    // ── Mark all non-terminal items as completed ───────────────────────────────
    // Exclude 'cancelled' and 'voided' — those are terminal and must not be overwritten.
    $pdo->prepare("
        UPDATE order_items
        SET status = 'completed'
        WHERE order_id = ?
          AND status NOT IN ('cancelled', 'voided')
    ")->execute([$order_id]);

    // ── Free table if dine-in ─────────────────────────────────────────────────
    $pdo->prepare("
        UPDATE tables
        SET status = 'available', current_order_id = NULL
        WHERE current_order_id = ?
    ")->execute([$order_id]);

    // ── Audit log ─────────────────────────────────────────────────────────────
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