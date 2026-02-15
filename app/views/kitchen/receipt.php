<?php
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../core/posHelper.php';
requireRole(['admin', 'cashier', 'kitchen']);

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
            font-family: monospace;
            font-size: 12px;
            margin: 0;
            padding: 0;
            color: #000;
        }

        .receipt {
            width: 80mm;
            margin: 0 auto;
            padding: 5px;
        }

        h1 {
            text-align: center;
            font-size: 16px;
            margin: 5px 0 0;
        }

        .center {
            text-align: center;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
        }

        .small {
            font-size: 11px;
        }

        @media print {
            body {
                width: 80mm;
            }
        }
    </style>
</head>
<body>

<div class="receipt">

    <h1>Dine with Din</h1>
    <div class="center small">
        Cafeteria<br>
        Official Receipt
    </div>

    <div class="divider"></div>

    <div class="small">
        Receipt #: <?= htmlspecialchars($order['order_number']) ?><br>
        Date: <?= date('Y-m-d H:i', strtotime($order['created_at'])) ?><br>
        Cashier: <?= htmlspecialchars($order['cashier_name'] ?? 'N/A') ?><br>
        Customer: <?= htmlspecialchars($order['customer_name'] ?? 'N/A') ?><br>
        Order Type: <?= ucfirst($order['order_type']) ?><br>
        <?php if(!empty($order['table_number'])): ?>
            Table #: <?= htmlspecialchars($order['table_number']) ?><br>
        <?php endif; ?>
    </div>

    <div class="divider"></div>

    <?php foreach ($items as $item): ?>
        <div class="row">
            <span><?= htmlspecialchars($item['item_name']) ?> x<?= $item['quantity'] ?></span>
            <span><?= number_format($item['unit_price'] * $item['quantity'], 2) ?></span>
        </div>

        <?php if (!empty($item['addons'])): ?>
            <?php foreach ($item['addons'] as $addon): ?>
                <div class="row small" style="padding-left:10px;">
                    <span>+ <?= htmlspecialchars($addon['addon_name']) ?> x<?= $addon['quantity'] ?></span>
                    <span><?= number_format($addon['unit_price'] * $addon['quantity'], 2) ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="divider"></div>

    <?php
        $vat = $order['total_amount'] - $subtotal;
    ?>

    <div class="row">
        <span>Subtotal:</span>
        <span><?= number_format($subtotal, 2) ?></span>
    </div>

    <div class="row">
        <span>VAT (12%):</span>
        <span><?= number_format($vat, 2) ?></span>
    </div>

    <div class="row">
        <strong>Total:</strong>
        <strong><?= number_format($order['total_amount'], 2) ?></strong>
    </div>

    <div class="divider"></div>

    <div class="row">
        <span>Payment (<?= htmlspecialchars($payment_method) ?>):</span>
        <span><?= number_format($amount_paid, 2) ?></span>
    </div>

    <div class="row">
        <span>Change:</span>
        <span><?= number_format($change_amount, 2) ?></span>
    </div>

    <div class="divider"></div>

    <div class="center small">
        Thank you for dining with us!<br>
        Please come again.
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
