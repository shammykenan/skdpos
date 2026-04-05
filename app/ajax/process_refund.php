<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../core/authHelper.php';

header('Content-Type: application/json');

if (!hasRole('admin')) {
    echo json_encode(['success' => false, 'error' => 'Access denied. Admin only.']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit;
}

$order_id      = (int)   ($_POST['order_id']      ?? 0);
$refund_type   = trim(        $_POST['refund_type']    ?? '');  // 'full' | 'partial'
$reason        = trim(        $_POST['reason']         ?? '');
$refund_method = trim(        $_POST['refund_method']  ?? 'cash');
$items_json    = trim(        $_POST['items']           ?? '[]');

if (!$order_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid order ID.']);
    exit;
}
if (!in_array($refund_type, ['full', 'partial'], true)) {
    echo json_encode(['success' => false, 'error' => 'Invalid refund type.']);
    exit;
}
if ($reason === '') {
    echo json_encode(['success' => false, 'error' => 'A reason is required.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // ── Fetch order ──────────────────────────────────────────────
    $stmt = $pdo->prepare("
        SELECT order_id, order_number, status, total_amount, cashier_id
        FROM orders WHERE order_id = ? LIMIT 1
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Order not found.']);
        exit;
    }
    if ($order['status'] !== 'completed') {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Refunds are only allowed on completed orders.']);
        exit;
    }

    // ── How much has already been refunded ──────────────────────
    $alreadyStmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount), 0) FROM refunds WHERE order_id = ?
    ");
    $alreadyStmt->execute([$order_id]);
    $alreadyRefunded = (float) $alreadyStmt->fetchColumn();

    $orderTotal   = (float) $order['total_amount'];
    $remainingCap = round($orderTotal - $alreadyRefunded, 2);

    if ($remainingCap <= 0) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'This order has already been fully refunded.']);
        exit;
    }

    $insertedRefunds = [];

    if ($refund_type === 'full') {
        // ── Full refund ──────────────────────────────
        $refundAmount = $remainingCap;

        $ins = $pdo->prepare("
            INSERT INTO refunds (order_id, order_item_id, amount, reason, refund_method, processed_by)
            VALUES (?, NULL, ?, ?, ?, ?)
        ");
        $ins->execute([$order_id, $refundAmount, $reason, $refund_method, (int) $_SESSION['user_id']]);
        $insertedRefunds[] = [
            'refund_id'     => (int) $pdo->lastInsertId(),
            'order_item_id' => null,
            'amount'        => $refundAmount,
        ];
    } else {
        // ── Partial refund ──────────────────────────────
        $selectedItems = json_decode($items_json, true);
        if (!is_array($selectedItems) || count($selectedItems) === 0) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => 'No items selected for partial refund.']);
            exit;
        }

        $itemStmt = $pdo->prepare("
            SELECT order_item_id, subtotal, status
            FROM order_items
            WHERE order_item_id = ? AND order_id = ?
            LIMIT 1
        ");
        $itemAlreadyStmt = $pdo->prepare("
            SELECT COALESCE(SUM(amount), 0)
            FROM refunds
            WHERE order_item_id = ?
        ");

        foreach ($selectedItems as $sel) {
            $oi_id  = (int)   ($sel['order_item_id'] ?? 0);
            $amount = (float) ($sel['amount'] ?? 0);

            if (!$oi_id || $amount <= 0) continue;

            // Fetch item details
            $itemStmt->execute([$oi_id, $order_id]);
            $item = $itemStmt->fetch(PDO::FETCH_ASSOC);

            if (!$item) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'error' => "Item #{$oi_id} not found on this order."]);
                exit;
            }

            if (in_array($item['status'], ['cancelled', 'voided'], true)) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'error' => "Cancelled or voided items cannot be refunded."]);
                exit;
            }

            // Remaining refundable for this item
            $itemAlreadyStmt->execute([$oi_id]);
            $itemAlreadyRefunded = (float) $itemAlreadyStmt->fetchColumn();
            $itemCap = round($item['subtotal'] - $itemAlreadyRefunded, 2);

            if ($amount > $itemCap) {
                $pdo->rollBack();
                echo json_encode([
                    'success' => false,
                    'error'   => sprintf(
                        'Item #%d refund (₱%.2f) exceeds the remaining refundable amount of ₱%.2f.',
                        $oi_id, $amount, $itemCap
                    ),
                ]);
                exit;
            }

            // Insert refund
            $ins = $pdo->prepare("
                INSERT INTO refunds (order_id, order_item_id, amount, reason, refund_method, processed_by)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $ins->execute([$order_id, $oi_id, $amount, $reason, $refund_method, (int) $_SESSION['user_id']]);

            $insertedRefunds[] = [
                'refund_id'     => (int) $pdo->lastInsertId(),
                'order_item_id' => $oi_id,
                'amount'        => $amount,
            ];
        }

        $totalRefunded = array_sum(array_column($insertedRefunds, 'amount'));
    }

    if (empty($totalRefunded)) {
        $totalRefunded = array_sum(array_column($insertedRefunds, 'amount'));
    }

    // ── Audit log ─────────────────────────────────────
    $logStmt = $pdo->prepare("
        INSERT INTO activity_logs (user_id, action, details, ip_address)
        VALUES (?, 'process_refund', ?, ?)
    ");
    $logStmt->execute([
        (int) $_SESSION['user_id'],
        json_encode([
            'order_id'         => $order_id,
            'order_number'     => $order['order_number'],
            'refund_type'      => $refund_type,
            'reason'           => $reason,
            'refund_method'    => $refund_method,
            'refunds'          => $insertedRefunds,
            'total_refunded'   => $totalRefunded,
            'already_refunded' => $alreadyRefunded,
            'order_total'      => $orderTotal,
        ]),
        $_SERVER['REMOTE_ADDR'] ?? null,
    ]);

    $pdo->commit();

    echo json_encode([
        'success'          => true,
        'order_id'         => $order_id,
        'order_number'     => $order['order_number'],
        'refund_type'      => $refund_type,
        'total_refunded'   => round($totalRefunded, 2),
        'already_refunded' => $alreadyRefunded,
        'order_total'      => $orderTotal,
        'refunds'          => $insertedRefunds,
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}