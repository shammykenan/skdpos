<?php
/**
 * print_receipt.php
 * 80mm thermal receipt — only real data from your schema.
 */
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../core/posHelper.php';
require_once __DIR__ . '/../../core/authHelper.php';
requireRole(['admin', 'cashier', 'kitchen']);

date_default_timezone_set('Asia/Manila');

$order_id     = (int) ($_GET['order_id'] ?? 0);
$orderDetails = getOrderDetails($pdo, $order_id);

if (!$orderDetails) {
    die('<p style="font-family:monospace;padding:20px;">Order not found.</p>');
}

$order   = $orderDetails['order'];
$items   = $orderDetails['items'];
$payment = $orderDetails['payment'] ?? [];

// Payment
$payment_method = strtoupper($payment['payment_method'] ?? '');
$amount_paid    = (float) ($payment['amount_paid']   ?? 0);
$change_amount  = (float) ($payment['change_amount'] ?? 0);
$paid_at        = $payment['paid_at'] ?? null;

// Order totals — straight from DB, no recalculation
$order_subtotal = (float) $order['subtotal'];
$order_discount = (float) $order['discount'];
$order_total    = (float) $order['total_amount'];

$company = [
    'name'    => 'DINE WITH DIN CAFETERIA',
    'address' => 'Karuhatan, Valenzuela City',
    'contact' => '(123) 456-7890',
    'email'   => 'demo123@gmail.com',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<link rel="icon" type="image/png" href="<?= $baseUrl ?>/assets/images/logo/logo.png">
<title>Receipt #<?= htmlspecialchars($order['order_number']) ?></title>
<style>
*{margin:0;padding:0;box-sizing:border-box;}

body{
    font-family:'Courier New',Courier,monospace;
    background:#ccc;
    display:flex;
    flex-direction:column;
    align-items:center;
    min-height:100vh;
    padding:16px;
    font-size:11px;
    line-height:1.6;
    color:#000;
}

.receipt{
    width:80mm;
    background:#fff;
    padding:6mm 5mm;
}

/* Header */
.r-header{
    text-align:center;
    padding-bottom:6px;
    border-bottom:1px dashed #000;
    margin-bottom:8px;
}
.shop-name{
    font-size:14px;
    font-weight:bold;
    letter-spacing:1px;
    text-transform:uppercase;
    margin-bottom:2px;
}
.shop-addr,
.shop-contact{font-size:10px;margin-bottom:1px;}
.doc-type{
    font-size:11px;font-weight:bold;
    text-transform:uppercase;
    letter-spacing:.5px;
    margin-top:6px;
}
.status-line{
    font-size:10px;
    font-weight:bold;
    text-transform:uppercase;
    margin-top:3px;
}

/* Meta rows */
.r-meta{font-size:11px;margin-bottom:6px;}
.r-meta-row{
    display:flex;
    justify-content:space-between;
    padding:1.5px 0;
}
.ml{color:#333;}
.mv{font-weight:bold;text-align:right;}

/* Dividers */
.div-dash  {border-top:1px dashed #000;margin:6px 0;}
.div-solid {border-top:1px solid #000;margin:6px 0;}
.div-double{border-top:3px double #000;margin:6px 0;}

/* Items */
.item-header{
    display:flex;
    font-weight:bold;
    font-size:10px;
    text-transform:uppercase;
    border-bottom:1px solid #000;
    padding-bottom:3px;
    margin-bottom:5px;
}
.item-row{display:flex;margin-bottom:5px;font-size:11px;}
.col-name {flex:2.5;word-break:break-word;padding-right:3px;}
.col-qty  {flex:.5;text-align:center;}
.col-price{flex:1;text-align:right;}
.col-total{flex:1;text-align:right;font-weight:bold;}

/* Addons */
.addon-row{
    display:flex;
    justify-content:space-between;
    font-size:10px;
    padding:1px 0 1px 10px;
}

/* Item notes */
.item-note{
    font-size:10px;
    font-style:italic;
    margin:1px 0 4px 10px;
    padding:1px 4px;
    border-left:1px solid #000;
}

/* Totals */
.totals{font-size:11px;margin:4px 0;}
.total-row{
    display:flex;
    justify-content:space-between;
    padding:2px 0;
}
.total-row.grand{
    font-weight:bold;
    font-size:13px;
    border-top:2px solid #000;
    border-bottom:2px solid #000;
    padding:5px 0;
    margin:5px 0;
}

/* Payment */
.payment-box{
    border:1px solid #000;
    padding:5px 6px;
    margin:6px 0;
    font-size:11px;
}
.pay-row{
    display:flex;
    justify-content:space-between;
    padding:2px 0;
}
.pay-row.paid-at{
    font-size:10px;
    border-bottom:1px dashed #000;
    margin-bottom:4px;
    padding-bottom:4px;
}

/* Footer */
.r-footer{
    text-align:center;
    margin-top:10px;
    padding-top:8px;
    border-top:1px dashed #000;
    font-size:10px;
    line-height:1.7;
}
.r-footer .thankyou{
    font-size:12px;font-weight:bold;
    text-transform:uppercase;
    margin-bottom:3px;
}
.r-footer .trans-id{
    font-size:9px;
    margin-top:5px;
    padding-top:4px;
    border-top:1px dashed #000;
}

/* Print button */
.print-btn{
    width:80mm;
    text-align:center;
    margin-top:12px;
}
.print-btn button{
    background:#0f4c5c;color:#fff;border:none;
    padding:9px 24px;border-radius:50px;
    font-size:13px;font-weight:bold;cursor:pointer;
    margin:3px;
}
.print-btn .btn-close{background:#6c757d;}

/* Print */
@media print{
    @page{size:80mm auto;margin:4mm 3mm;}
    body{
        background:#fff;padding:0;margin:0;
        display:block;min-height:auto;
        font-size:11px;line-height:1.6;
        -webkit-print-color-adjust:exact;
        print-color-adjust:exact;
    }
    .receipt{width:74mm;padding:0;margin:0;}
    .print-btn{display:none;}
}
</style>
</head>
<body>
<div class="receipt">

    <!-- Header -->
    <div class="r-header">
        <div class="shop-name"><?= htmlspecialchars($company['name']) ?></div>
        <div class="shop-addr"><?= htmlspecialchars($company['address']) ?></div>
        <div class="shop-contact">Tel: <?= htmlspecialchars($company['contact']) ?></div>
        <div class="shop-contact"><?= htmlspecialchars($company['email']) ?></div>
        <div class="doc-type">Sales Receipt</div>
        <div class="status-line">[ <?= strtoupper(htmlspecialchars($order['status'])) ?> ]</div>
    </div>

    <!-- Order info — only fields that exist in your schema -->
    <div class="r-meta">
        <div class="r-meta-row">
            <span class="ml">Receipt No.:</span>
            <span class="mv"><?= htmlspecialchars($order['order_number']) ?></span>
        </div>
        <div class="r-meta-row">
            <span class="ml">Date:</span>
            <span class="mv"><?= date('M d, Y', strtotime($order['created_at'])) ?></span>
        </div>
        <div class="r-meta-row">
            <span class="ml">Time:</span>
            <span class="mv"><?= date('h:i A', strtotime($order['created_at'])) ?></span>
        </div>
        <div class="r-meta-row">
            <span class="ml">Cashier:</span>
            <span class="mv"><?= htmlspecialchars($order['cashier_name'] ?? 'N/A') ?></span>
        </div>
        <?php if (!empty($order['customer_name'])): ?>
        <div class="r-meta-row">
            <span class="ml">Customer:</span>
            <span class="mv"><?= htmlspecialchars($order['customer_name']) ?></span>
        </div>
        <?php endif; ?>
        <div class="r-meta-row">
            <span class="ml">Order type:</span>
            <span class="mv"><?= ucfirst(str_replace('_', ' ', $order['order_type'] ?? '')) ?></span>
        </div>
        <?php if (!empty($order['table_number'])): ?>
        <div class="r-meta-row">
            <span class="ml">Table:</span>
            <span class="mv">#<?= htmlspecialchars($order['table_number']) ?></span>
        </div>
        <?php endif; ?>
    </div>

    <div class="div-dash"></div>

    <!-- Items -->
    <div class="item-header">
        <span class="col-name">Item</span>
        <span class="col-qty">Qty</span>
        <span class="col-price">Price</span>
        <span class="col-total">Total</span>
    </div>

    <?php foreach ($items as $item):
        $line_total = (float)$item['unit_price'] * (int)$item['quantity'];
    ?>
        <div class="item-row">
            <span class="col-name"><?= htmlspecialchars($item['item_name']) ?></span>
            <span class="col-qty"><?= (int)$item['quantity'] ?></span>
            <span class="col-price"><?= number_format((float)$item['unit_price'], 2) ?></span>
            <span class="col-total"><?= number_format($line_total, 2) ?></span>
        </div>

        <?php if (!empty($item['addons'])): ?>
            <?php foreach ($item['addons'] as $addon):
                $addon_price = (float)($addon['unit_price'] ?? $addon['price'] ?? 0);
                $addon_qty   = (int)($addon['quantity'] ?? 1);
                $addon_total = $addon_price * $addon_qty;
            ?>
            <div class="addon-row">
                <span>+ <?= htmlspecialchars($addon['addon_name']) ?> x<?= $addon_qty ?></span>
                <span><?= number_format($addon_total, 2) ?></span>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($item['item_notes'])): ?>
        <div class="item-note">Note: <?= htmlspecialchars($item['item_notes']) ?></div>
        <?php endif; ?>

    <?php endforeach; ?>

    <div class="div-double"></div>

    <!-- Totals -->
    <div class="totals">
        <div class="total-row">
            <span>Subtotal:</span>
            <span>PHP <?= number_format($order_subtotal, 2) ?></span>
        </div>
        <?php if ($order_discount > 0): ?>
        <div class="total-row">
            <span>Discount (-):</span>
            <span>PHP <?= number_format($order_discount, 2) ?></span>
        </div>
        <?php endif; ?>
        <div class="total-row grand">
            <span>TOTAL:</span>
            <span>PHP <?= number_format($order_total, 2) ?></span>
        </div>
    </div>

    <!-- Payment — only shown when order is completed -->
    <?php if ($order['status'] === 'completed' && !empty($payment)): ?>
    <div class="payment-box">
        <?php if ($paid_at): ?>
        <div class="pay-row paid-at">
            <span>Paid at:</span>
            <span><?= date('M d, Y h:i A', strtotime($paid_at)) ?></span>
        </div>
        <?php endif; ?>
        <div class="pay-row">
            <span>Method:</span>
            <span><?= htmlspecialchars($payment_method) ?></span>
        </div>
        <div class="pay-row">
            <span>Cash tendered:</span>
            <span>PHP <?= number_format($amount_paid, 2) ?></span>
        </div>
        <div class="pay-row">
            <span>Change:</span>
            <span>PHP <?= number_format($change_amount, 2) ?></span>
        </div>
    </div>
    <?php endif; ?>

    <!-- Footer -->
    <div class="r-footer">
        <div class="thankyou">Thank you for dining with us!</div>
        <div>Please come again.</div>
        <div class="trans-id">
            Transaction: <?= htmlspecialchars($order['order_number']) ?><br>
            Printed: <?= date('M d, Y h:i A') ?>
        </div>
    </div>

</div>

<div class="print-btn">
    <button onclick="window.print()">Print Receipt</button>
    <button class="btn-close" onclick="window.close()">Close</button>
</div>

<script>
window.addEventListener('load', function() {
    setTimeout(function() { window.print(); }, 400);
});
window.addEventListener('afterprint', function() {
    window.close();
});
</script>
</body>
</html>