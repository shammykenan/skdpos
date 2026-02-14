<?php
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../core/posHelper.php';
requireRole(['admin', 'cashier']);

// Get order_id from GET
$order_id = (int) ($_GET['order_id'] ?? 0);
$orderDetails = getOrderDetails($pdo, $order_id);

if (!$orderDetails) {
    die('Order not found.');
}

$order = $orderDetails['order'];
$items = $orderDetails['items'];
$payment = $orderDetails['payment'];

// Payment variables
$payment_method = $payment['payment_method'] ?? '';
$amount_paid    = $payment['amount_paid'] ?? 0;
$change_amount  = $payment['change_amount'] ?? 0;

// Calculate subtotal without 12% tax
$subtotal = $order['total_amount'] / 1.12;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Receipt #<?= htmlspecialchars($order['order_number']) ?></title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 13px;
            margin: 0;
            padding: 0;
            background: #fff;
            color: #333;
        }
        .receipt {
            width: 360px;
            margin: auto;
            padding: 20px;
            border: 1px solid #eee;
            border-radius: 8px;
            box-shadow: 0 0 5px rgba(0,0,0,0.05);
        }
        .receipt h1 {
            font-size: 22px;
            text-align: center;
            margin-bottom: 5px;
            color: #222;
        }
        .receipt h2 {
            font-size: 14px;
            text-align: center;
            margin-top: 0;
            margin-bottom: 15px;
            font-weight: normal;
            color: #555;
        }
        .info, .items, .totals, .footer {
            margin-bottom: 15px;
        }
        .info p {
            margin: 2px 0;
            font-size: 12px;
        }
        .items .item-row, .totals .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        .items .addon-row {
            display: flex;
            justify-content: space-between;
            margin-left: 15px;
            font-size: 11px;
            color: #555;
        }
        .totals .total-row span:first-child {
            font-weight: 600;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #555;
        }
        .footer p {
            margin: 3px 0;
        }

        @media print {
            body {
                width: 360px;
                margin: 0;
                padding: 0;
            }
            .receipt {
                border: none;
                box-shadow: none;
                width: 100%;
                padding: 0;
            }
        }
    </style>
</head>
<body>
<div class="receipt">
    <h1>Dine with Din - Cafeteria</h1>
    <h2>Official Receipt</h2>

    <div class="info">
        <p>Receipt #: <?= htmlspecialchars($order['order_number']) ?></p>
        <p>Date: <?= htmlspecialchars(date('Y-m-d H:i', strtotime($order['created_at']))) ?></p>
        <p>Cashier: <?= htmlspecialchars($order['cashier_name'] ?? 'N/A') ?></p>
        <p>Customer Name: <?= htmlspecialchars($order['customer_name'] ?? 'N/A') ?></p>
        <?php if(!empty($order['table_number'])): ?>
            <p>Table #: <?= htmlspecialchars($order['table_number']) ?></p>
        <?php endif; ?>
        <p>Order Type: <?= ucfirst($order['order_type']) ?></p>
    </div>

    <div class="items">
        <?php foreach ($items as $item): ?>
            <div class="item-row">
                <span><?= htmlspecialchars($item['item_name']) ?> x<?= $item['quantity'] ?></span>
                <span><?= number_format($item['unit_price'] * $item['quantity'], 2) ?></span>
            </div>
            <?php if (!empty($item['addons'])): ?>
                <?php foreach ($item['addons'] as $addon): ?>
                    <div class="addon-row">
                        <span>+ <?= htmlspecialchars($addon['addon_name']) ?> x<?= $addon['quantity'] ?></span>
                        <span><?= number_format($addon['unit_price'] * $addon['quantity'], 2) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <div class="totals">
        <div class="total-row">
            <span>Subtotal (before 12% tax):</span>
            <span><?= number_format($subtotal, 2) ?></span>
        </div>
        <div class="total-row">
            <span>Total:</span>
            <span><?= number_format($order['total_amount'], 2) ?></span>
        </div>
        <div class="total-row">
            <span>Payment (<?= htmlspecialchars($payment_method) ?>):</span>
            <span><?= number_format($amount_paid, 2) ?></span>
        </div>
        <div class="total-row">
            <span>Change:</span>
            <span><?= number_format($change_amount, 2) ?></span>
        </div>
    </div>

    <div class="footer">
        <p>Thank you for dining with us!</p>
        <p>We look forward to serving you again!</p>
    </div>
</div>

<script>
window.onload = function() {
    window.print();
    window.onafterprint = function() {
        window.close();
    };
};
</script>
</body>
</html>
