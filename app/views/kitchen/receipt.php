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

// Calculate subtotal without 12% VAT
$subtotal = $order['total_amount'] / 1.12;
$vat = $order['total_amount'] - $subtotal;

// Company information (customize these)
$company = [
    'name' => 'DINE WITH DIN CAFETERIA',
    'address' => '6 Pag-Asa St.,Karuhatan, Valenzuela City',
    'contact' => 'Tel: (123) 456-7890',
    'email' => 'dinshammykenan012@gmail.com',
    'website' => 'www.dinewithdin.com'
];

// Receipt number formatting
$receipt_number = str_pad($order['order_number'], 8, '0', STR_PAD_LEFT);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #<?= htmlspecialchars($receipt_number) ?></title>
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', 'Lucida Console', monospace;
            background: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        /* Receipt container - standard thermal paper width (80mm) */
        .receipt {
            width: 300px;
            max-width: 100%;
            background: white;
            padding: 20px 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            margin: 0 auto;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
        }

        /* Header styles */
        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px dashed #000;
        }

        .header h1 {
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 1px;
            margin: 0 0 5px;
            text-transform: uppercase;
        }

        .header h2 {
            font-size: 14px;
            font-weight: normal;
            margin: 0 0 3px;
        }

        .header .subtitle {
            font-size: 11px;
            color: #333;
        }

        .header .info {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }

        /* Receipt meta information */
        .meta {
            margin-bottom: 10px;
            font-size: 11px;
        }

        .meta-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .meta-item {
            width: 48%;
            margin-bottom: 3px;
        }

        .meta-label {
            font-weight: bold;
            display: inline-block;
            width: 65px;
        }

        /* Divider styles */
        .divider {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }

        .divider-double {
            border-top: 3px double #000;
            margin: 10px 0;
        }

        /* Items table */
        .items {
            margin: 10px 0;
        }

        .item-header {
            display: flex;
            font-weight: bold;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
            margin-bottom: 5px;
            font-size: 11px;
            text-transform: uppercase;
        }

        .item-row {
            display: flex;
            margin-bottom: 8px;
        }

        .item-name {
            flex: 2;
            word-break: break-word;
            padding-right: 5px;
        }

        .item-qty {
            flex: 0.5;
            text-align: center;
        }

        .item-price {
            flex: 1;
            text-align: right;
        }

        .item-total {
            flex: 1;
            text-align: right;
            font-weight: bold;
        }

        /* Addon styling */
        .addons {
            margin: 2px 0 5px 10px;
            font-size: 10px;
            color: #555;
        }

        .addon-row {
            display: flex;
            justify-content: space-between;
            padding-left: 10px;
        }

        .addon-name {
            flex: 2;
        }

        .addon-price {
            text-align: right;
            flex: 1;
        }

        /* Item notes */
        .item-notes {
            margin: 2px 0 5px 10px;
            font-size: 10px;
            color: #c00;
            font-style: italic;
            padding: 2px 5px;
            background: #fff3f3;
            border-left: 2px solid #c00;
        }

        .item-notes i {
            font-style: normal;
            font-weight: bold;
            margin-right: 3px;
        }

        /* Totals section */
        .totals {
            margin: 10px 0;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
        }

        .total-row.final {
            font-weight: bold;
            font-size: 14px;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 8px 0;
            margin: 8px 0;
        }

        .total-row.discount {
            color: #c00;
        }

        /* Payment section */
        .payment {
            margin: 10px 0;
            background: #f9f9f9;
            padding: 8px;
            border-radius: 3px;
        }

        .payment-row {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 2px dashed #000;
            font-size: 11px;
        }

        .footer .thankyou {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .footer .terms {
            font-size: 9px;
            color: #666;
            margin-top: 8px;
        }

        /* Barcode/QR placeholder */
        .barcode {
            text-align: center;
            margin: 10px 0;
            font-family: 'Libre Barcode 39', cursive;
            font-size: 24px;
        }

        .barcode-text {
            font-family: 'Courier New', monospace;
            font-size: 10px;
            letter-spacing: 2px;
        }

        /* Status badges */
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-completed {
            background: #2c7a5a;
            color: white;
        }

        .status-cancelled {
            background: #d64045;
            color: white;
        }

        .status-pending {
            background: #f4b942;
            color: #000;
        }

        /* Print styles */
        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
                min-height: auto;
            }

            .receipt {
                width: 80mm;
                box-shadow: none;
                padding: 10px 5px;
                margin: 0 auto;
            }

            .no-print {
                display: none;
            }

            /* Ensure background colors print */
            .status-badge {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .item-notes {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        /* Print button */
        .print-btn {
            text-align: center;
            margin: 20px 0;
        }

        .print-btn button {
            background: #0f4c5c;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .print-btn button:hover {
            background: #1f7a8c;
        }

        .print-btn button i {
            margin-right: 8px;
        }
    </style>
    <!-- Font Awesome for icons (optional) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <h1><?= htmlspecialchars($company['name']) ?></h1>
            <div class="subtitle"><?= htmlspecialchars($company['address']) ?></div>
            <div class="info"><?= htmlspecialchars($company['contact']) ?></div>
            <div class="info"><?= htmlspecialchars($company['email']) ?> | <?= htmlspecialchars($company['website']) ?></div>
            <div style="margin-top: 8px;">
                <span class="status-badge status-<?= strtolower($order['status']) ?>">
                    <?= strtoupper(htmlspecialchars($order['status'])) ?>
                </span>
            </div>
        </div>

        <!-- Receipt Info -->
        <div class="meta">
            <div class="meta-grid">
                <div class="meta-item"><span class="meta-label"></span>Receipt #:<?= htmlspecialchars($receipt_number)?></div>
                <div class="meta-item"><span class="meta-label">Date:</span> <?= date('Y-m-d', strtotime($order['created_at'])) ?></div>
                <div class="meta-item"><span class="meta-label">Time:</span> <?= date('h:i:s A', strtotime($order['created_at'])) ?></div>
                <div class="meta-item"><span class="meta-label">Cashier:</span> <?= htmlspecialchars($order['cashier_name'] ?? 'N/A') ?></div>
                <div class="meta-item"><span class="meta-label">Customer:</span> <?= htmlspecialchars($order['customer_name'] ?? 'Walk-in Customer') ?></div>
                <div class="meta-item"><span class="meta-label">Type:</span> <?= ucfirst(htmlspecialchars($order['order_type'] ?? 'N/A')) ?></div>
                <?php if(!empty($order['table_number'])): ?>
                <div class="meta-item"><span class="meta-label">Table:</span> #<?= htmlspecialchars($order['table_number']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="divider"></div>

        <!-- Items -->
        <div class="items">
            <div class="item-header">
                <span class="item-name">Item</span>
                <span class="item-qty">Qty</span>
                <span class="item-price">Price</span>
                <span class="item-total">Total</span>
            </div>

            <?php foreach ($items as $item): 
                $item_subtotal = $item['unit_price'] * $item['quantity'];
                $addons_total = 0;
            ?>
            <div class="item-row">
                <span class="item-name"><?= htmlspecialchars($item['item_name']) ?></span>
                <span class="item-qty"><?= $item['quantity'] ?></span>
                <span class="item-price"><?= number_format($item['unit_price'], 2) ?></span>
                <span class="item-total"><?= number_format($item_subtotal, 2) ?></span>
            </div>

            <!-- Addons -->
            <?php if (!empty($item['addons'])): ?>
                <div class="addons">
                    <?php foreach ($item['addons'] as $addon): 
                        $addon_total = $addon['unit_price'] * $addon['quantity'];
                        $addons_total += $addon_total;
                    ?>
                    <div class="addon-row">
                        <span class="addon-name">↳ <?= htmlspecialchars($addon['addon_name']) ?> x<?= $addon['quantity'] ?></span>
                        <span class="addon-price">+<?= number_format($addon_total, 2) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Item Notes -->
            <?php if (!empty($item['item_notes'])): ?>
                <div class="item-notes">
                    <i>📝 Note:</i> <?= htmlspecialchars($item['item_notes']) ?>
                </div>
            <?php endif; ?>

            <?php endforeach; ?>
        </div>

        <div class="divider-double"></div>

        <!-- Totals -->
        <div class="totals">
            <?php
            // Calculate item totals with addons
            $calculated_subtotal = 0;
            foreach ($items as $item) {
                $item_total = $item['unit_price'] * $item['quantity'];
                foreach ($item['addons'] as $addon) {
                    $item_total += $addon['unit_price'] * $addon['quantity'];
                }
                $calculated_subtotal += $item_total;
            }
            $calculated_vat = $calculated_subtotal * 0.12;
            $calculated_total = $calculated_subtotal + $calculated_vat;
            ?>
            <div class="total-row">
                <span>Subtotal (excl. VAT):</span>
                <span><?= number_format($calculated_subtotal, 2) ?></span>
            </div>
            <div class="total-row">
                <span>VAT (12%):</span>
                <span><?= number_format($calculated_vat, 2) ?></span>
            </div>
            <div class="total-row final">
                <span>TOTAL AMOUNT:</span>
                <span>₱<?= number_format($calculated_total, 2) ?></span>
            </div>
        </div>

        <!-- Payment Details -->
        <?php if ($order['status'] === 'completed' && !empty($payment)): ?>
        <div class="payment">
            <div class="payment-row">
                <span>Payment Method:</span>
                <span><strong><?= strtoupper(htmlspecialchars($payment_method)) ?></strong></span>
            </div>
            <div class="payment-row">
                <span>Amount Paid:</span>
                <span>₱<?= number_format($amount_paid, 2) ?></span>
            </div>
            <div class="payment-row">
                <span>Change:</span>
                <span>₱<?= number_format($change_amount, 2) ?></span>
            </div>
            <?php if ($change_amount > 0): ?>
            <div style="margin-top: 5px; font-size: 10px; text-align: center; color: #2c7a5a;">
                <i class="fas fa-check-circle"></i> Payment complete
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="footer">
            <div class="thankyou">THANK YOU FOR DINING WITH US!</div>
            <div>Please come again</div>
            
            <!-- Barcode/QR Code (optional - for digital receipts) -->
            <div class="barcode-text" style="margin-top: 10px;">
                <?= htmlspecialchars($receipt_number) ?>
            </div>
            
            <div class="terms">
                * This serves as your official receipt *<br>
                VAT Reg TIN: <?= htmlspecialchars($company['contact']) ?><br>
                <?= date('Y-m-d H:i:s') ?> | sham #999
            </div>
            
            <?php if (!empty($order['customer_email'])): ?>
            <div style="margin-top: 8px; font-size: 10px;">
                Receipt sent to: <?= htmlspecialchars($order['customer_email']) ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Print Button (hidden when printing)
    <div class="print-btn no-print">
        <button onclick="window.print()">
            <i class="fas fa-print"></i> Print Receipt
        </button>
        <br><br>
        <button onclick="window.close()" style="background: #6c757d;">
            <i class="fas fa-times"></i> Close
        </button>
    </div> -->

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