<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../core/authHelper.php';
require_once __DIR__ . '/../core/posHelper.php';
require_once __DIR__ . '/../core/itemsHelper.php';
require_once __DIR__ . '/../core/refundHelper.php';

header('Content-Type: application/json');
requireRole(['admin', 'cashier', 'kitchen']);

$order_id = (int) ($_GET['order_id'] ?? 0);
if (!$order_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID.']);
    exit;
}

$data = getOrderDetails($pdo, $order_id);
if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Order not found.']);
    exit;
}

// ── All refunds now live in the refunds table ─────────────────────────────────
// refund_type distinguishes the source:
//   'void_order' — entire order voided (pre-completion)
//   'void_item'  — single item voided  (pre-completion)
//   'post_sale'  — post-completion refund issued by admin
//
// The JS uses refunds.rows to populate:
//   - Order Details modal  → detailRefundsHistorySection card (all statuses)
//   - Receipt              → REFUNDS ISSUED footer (completed orders)
//
// $data['refund'] (the old single-row payments lookup) is intentionally removed.
// All consumers in manage_orders.js now read from d.refunds only.

$data['refunds'] = null;
$totalRefunded = 0.0;
try {
    $refundsStmt = $pdo->prepare("
        SELECT
            r.refund_id,
            r.refund_type,
            r.order_item_id,
            r.amount,
            r.quantity,
            r.reason,
            r.refund_method,
            r.note,
            r.created_at,
            u.full_name AS processed_by_name
        FROM refunds r
        LEFT JOIN users u ON r.processed_by = u.user_id
        WHERE r.order_id = ?
        ORDER BY r.refund_id ASC
    ");
    $refundsStmt->execute([$order_id]);
    $refundRows = $refundsStmt->fetchAll(PDO::FETCH_ASSOC);

    if ($refundRows) {
        $totalRefunded    = array_sum(array_column($refundRows, 'amount'));
        $data['refunds']  = [
            'rows'           => $refundRows,
            'total_refunded' => round((float) $totalRefunded, 2),
            'count'          => count($refundRows),
        ];
    }
} catch (Exception $e) {
    // Non-fatal — refunds table may not exist on older installs
}

if (!empty($data['items']) && !empty($data['order'])) {
    $data['items'] = refund_hydrate_order_items($pdo, $data['order'], $data['items']);
}

if (!empty($data['order'])) {
    $data['order']['effective_discount_total'] = max(
        0.0,
        round(
            (float) ($data['order']['subtotal'] ?? 0) - (float) ($data['order']['total_amount'] ?? 0),
            2
        )
    );
    $data['order']['refund_remaining'] = max(
        0.0,
        round((float) ($data['order']['total_amount'] ?? 0) - $totalRefunded, 2)
    );
}

echo json_encode(['success' => true, 'data' => $data]);
