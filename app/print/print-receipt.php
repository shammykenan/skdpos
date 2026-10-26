<?php
/**
 * print-receipt.php
 * BIR-compliant ESC/POS receipt printer.
 *
 * Takes an order_id and prints from the RECORDED SALE. Every figure on the
 * paper comes from the database — the same rows the Z-reading sums — so the
 * customer's copy can never disagree with what was filed. Nothing on the
 * receipt is taken from the caller except which order to print.
 */

require __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../core/authHelper.php';
require_once __DIR__ . '/../core/posHelper.php';
require_once __DIR__ . '/../core/itemsHelper.php';
require_once __DIR__ . '/../core/VatHelper.php';
require_once __DIR__ . '/../models/businessinfo.php';
require_once __DIR__ . '/receipt_format.php';

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

header('Content-Type: application/json');
date_default_timezone_set('Asia/Manila');

$COL = RECEIPT_COLS;

// ── Which order? (the only caller-supplied value) ─────────────────
$raw      = json_decode(file_get_contents('php://input'), true) ?: [];
$order_id = (int) ($raw['order_id'] ?? $_POST['order_id'] ?? $_GET['order_id'] ?? 0);

if ($order_id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'A valid order_id is required.']);
    exit;
}

// ── Load the recorded sale ────────────────────────────────────────
$details = getOrderDetails($pdo, $order_id);
if (!$details || empty($details['order'])) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Order not found.']);
    exit;
}

$order   = $details['order'];
$items   = $details['items']   ?? [];
$payment = $details['payment'] ?? null;

// A cashier may print their own sales; admins may print any.
if (!hasRole('admin') && (int) $order['cashier_id'] !== (int) ($_SESSION['user_id'] ?? 0)) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'You can only print your own receipts.']);
    exit;
}

// ── Discount record (carries the SC/PWD ID number) ────────────────
$stmt = $pdo->prepare("
    SELECT discount_type, reference_no, discount_pct, discount_amt
    FROM order_discounts
    WHERE order_id = ?
    ORDER BY discount_id DESC
    LIMIT 1
");
$stmt->execute([$order_id]);
$discountRow = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

// ── Business info ─────────────────────────────────────────────────
$info      = getBusinessInfo($pdo);
$isVat     = (bool) $info['vat_registered'];
$vatRate   = (float) $info['vat_rate'];
$bizName   = $info['business_name']     ?: 'The Lounge by: CJV & TEK';
$bizAddr   = $info['address']           ?: '';
$tin       = $info['tin']               ?: '';
$machineSN = $info['machine_serial_no'] ?: '';
$ptuNo     = $info['ptu_number']        ?: '';
$accredNo  = $info['accreditation_no']  ?: '';
// Heading must match the layout approved on the PTU — configured in BIR settings.
$docLabel  = $info['document_label']    ?: 'SALES INVOICE';
// Keep the number's caption consistent with the document type.
$numLabel  = strpos($docLabel, 'RECEIPT') !== false ? 'OR No.:' : 'Invoice No.:';

// ── Stored figures — authoritative, never recomputed here ─────────
$subtotal       = (float) $order['subtotal'];
$discountType   = $order['discount_type']   ?? 'none';
$discountAmount = (float) ($order['discount_amount'] ?? 0);
$total          = (float) $order['total_amount'];
$vatableSales   = (float) ($order['total_amount'] - ($order['vat_amount'] ?? 0));
$vatAmount      = (float) ($order['vat_amount']         ?? 0);
$vatExemptSales = (float) ($order['vat_exempt_amount']  ?? 0);
$zeroRatedSales = (float) ($order['zero_rated_amount']  ?? 0);

// VATable sales is the taxed portion only: total less VAT, less exempt/zero-rated.
$vatableSales = round($total - $vatAmount - $vatExemptSales - $zeroRatedSales, 2);
if ($vatableSales < 0) {
    $vatableSales = 0.00;
}

$amountPaid = (float) ($payment['amount_paid']   ?? 0);
$changeAmt  = (float) ($payment['change_amount'] ?? 0);
$payMethod  = strtoupper($payment['payment_method'] ?? 'CASH');
$orNumber   = $order['or_number'] ?: ($order['order_number'] ?? 'N/A');
$issuedAt   = !empty($order['created_at'])
              ? date('M j, Y  h:i A', strtotime($order['created_at']))
              : date('M j, Y  h:i A');

// ── Printer ───────────────────────────────────────────────────────
try {
    $connector = new WindowsPrintConnector('POS58');
    $printer   = new Printer($connector);
} catch (Throwable $e) {
    error_log('Printer not available: ' . $e->getMessage());
    echo json_encode([
        'status'  => 'no_printer',
        'message' => 'No printer available — the sale is recorded and can be reprinted.',
    ]);
    exit;
}

// A mid-print failure must not leave the caller hanging on the fetch.
try {

    // ── Business header ───────────────────────────────────────────
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
    $printer->text($bizName . "\n");
    $printer->selectPrintMode();

    if ($bizAddr !== '') {
        foreach (explode("\n", wrapText($bizAddr, $COL)) as $line) {
            $printer->text(trim($line) . "\n");
        }
    }
    if ($tin !== '')       $printer->text(($isVat ? 'VAT REG TIN: ' : 'NON-VAT TIN: ') . $tin . "\n");
    if ($minNo !== '')     $printer->text('MIN: '           . $minNo     . "\n");
    if ($machineSN !== '') $printer->text('Machine SN: '    . $machineSN . "\n");
    if ($ptuNo !== '')     $printer->text('PTU No.: '       . $ptuNo     . "\n");
    if ($accredNo !== '')  $printer->text('Accreditation: ' . $accredNo  . "\n");

    $printer->setJustification(Printer::JUSTIFY_LEFT);
    $printer->text(divider('=', $COL) . "\n");

    // ── Document type ─────────────────────────────────────────────
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->text($docLabel . "\n");
    $printer->setEmphasis(true);
    $printer->text("[ PAID ]\n");
    $printer->setEmphasis(false);
    $printer->setJustification(Printer::JUSTIFY_LEFT);
    $printer->text(divider('-', $COL) . "\n");

    // ── Transaction info ──────────────────────────────────────────
    $printer->text(row2col($numLabel, $orNumber, $COL) . "\n");
    $printer->text(row2col('Date:',        $issuedAt, $COL) . "\n");
    $printer->text(row2col('Cashier:',     (string) ($order['cashier_name'] ?? ''), $COL) . "\n");
    if (!empty($order['customer_name'])) {
        $printer->text(row2col('Customer:', $order['customer_name'], $COL) . "\n");
    }
    $printer->text(row2col('Type:', ($order['order_type'] ?? '') === 'dine_in' ? 'Dine In' : 'Take Out', $COL) . "\n");
    if (($order['order_type'] ?? '') === 'dine_in' && !empty($order['table_name'])) {
        $printer->text(row2col('Table:', $order['table_name'], $COL) . "\n");
    }
    $printer->text(divider('-', $COL) . "\n");

    // ── Items ─────────────────────────────────────────────────────
    foreach ($items as $item) {
        if (in_array($item['item_status'] ?? '', ['cancelled', 'voided'], true)) {
            continue;
        }

        $name = !empty($item['variant_name_snapshot'])
                ? $item['item_name_snapshot'] . ' (' . $item['variant_name_snapshot'] . ')'
                : $item['item_name_snapshot'];

        $qty       = (int) $item['quantity'];
        $unitPrice = (float) $item['unit_price'];

        $printer->text(row2col($name, 'x' . $qty . ' ' . number_format($unitPrice, 2), $COL) . "\n");

        $addonTotal = 0.00;
        foreach ($item['addons'] ?? [] as $a) {
            $addonTotal += (float) $a['price'] * (int) $a['quantity'];
            $printer->text(row2col(
                '  +' . $a['addon_name'],
                'x' . $a['quantity'] . ' ' . number_format((float) $a['price'], 2),
                $COL
            ) . "\n");
        }

        $lineTotal = (float) $item['subtotal'] + ($addonTotal * $qty);
        if ($qty > 1 || $addonTotal > 0) {
            $printer->text(row2col('  Subtotal:', number_format($lineTotal, 2), $COL) . "\n");
        }
        if (!empty($item['item_notes'])) {
            $printer->text('  Note: ' . $item['item_notes'] . "\n");
        }
    }

    $printer->text(divider('-', $COL) . "\n");
    $printer->text(row2col('Subtotal:', phpeso($subtotal), $COL) . "\n");

    // ── Discount ──────────────────────────────────────────────────
    if ($discountType !== 'none' && $discountAmount > 0) {
        $pct   = $discountRow ? (float) $discountRow['discount_pct'] : 0.0;
        $label = [
            'senior' => 'SC Discount',
            'pwd'    => 'PWD Discount',
            'promo'  => 'Promo Discount',
        ][$discountType] ?? 'Discount';

        if ($pct > 0) {
            $label .= ' (' . number_format($pct, 0) . '%)';
        }
        $printer->text(row2col($label . ':', '-' . phpeso($discountAmount), $COL) . "\n");
    }

    // ── VAT breakdown (stored values) ─────────────────────────────
    if ($isVat) {
        if ($vatableSales > 0) {
            $printer->text(row2col('VATable Sales:', phpeso($vatableSales), $COL) . "\n");
        }
        if ($vatAmount > 0) {
            $printer->text(row2col('VAT (' . number_format($vatRate, 0) . '%):', phpeso($vatAmount), $COL) . "\n");
        }
        if ($vatExemptSales > 0) {
            $printer->text(row2col('VAT-Exempt Sales:', phpeso($vatExemptSales), $COL) . "\n");
        }
        if ($zeroRatedSales > 0) {
            $printer->text(row2col('Zero-Rated Sales:', phpeso($zeroRatedSales), $COL) . "\n");
        }
    }

    $printer->text(divider('=', $COL) . "\n");

    // ── Total ─────────────────────────────────────────────────────
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->setEmphasis(true);
    $printer->setUnderline(1);
    $printer->text('TOTAL: ' . phpeso($total) . "\n");
    $printer->setUnderline(0);
    $printer->setEmphasis(false);
    $printer->setJustification(Printer::JUSTIFY_LEFT);

    // ── Payment ───────────────────────────────────────────────────
    $printer->text(row2col('Method:',   $payMethod,           $COL) . "\n");
    $printer->text(row2col('Tendered:', phpeso($amountPaid),  $COL) . "\n");
    $printer->text(row2col('Change:',   phpeso($changeAmt),   $COL) . "\n");
    $printer->text(divider('-', $COL) . "\n");

    // ── Non-VAT disclaimer ────────────────────────────────────────
    if (!$isVat) {
        $printer->text("\n");
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("THIS DOCUMENT IS NOT VALID\n");
        $printer->text("FOR CLAIMING INPUT TAXES\n");
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text(divider('-', $COL) . "\n");
    }

    // ── SC/PWD block: name, ID and signature (RA 9994 / RA 9442) ──
    if (in_array($discountType, ['senior', 'pwd'], true)) {
        $printer->text("\n");
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text(($discountType === 'senior' ? 'RA 9994' : 'RA 9442')
            . ' - ' . ($discountType === 'senior' ? 'Senior Citizen' : 'PWD') . " Discount\n");
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("\n");

        $holder = $order['customer_name'] ?: '';
        $idNo   = $discountRow['reference_no'] ?? '';

        $printer->text('Name: ' . ($holder !== '' ? $holder : str_repeat('_', $COL - 6)) . "\n");
        $printer->text("\n");
        $printer->text(($discountType === 'senior' ? 'OSCA/SC ID' : 'PWD ID') . ' No.: '
            . ($idNo !== '' ? $idNo : str_repeat('_', 12)) . "\n");
        $printer->text("\n");
        $printer->text('Signature: ' . str_repeat('_', $COL - 11) . "\n");
        $printer->text(divider('-', $COL) . "\n");
    }

    // ── Footer ────────────────────────────────────────────────────
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->text("\n");
    $printer->text("Thank you!\n");
    $printer->text("Please come again\n");
    $printer->text("\n");
    $printer->text(divider('.', $COL) . "\n");
    $printer->text("Powered by SKD Software\n");
    $printer->text("\n\n");

    $printer->feed(3);
    $printer->cut();
    $printer->close();

    echo json_encode(['status' => 'printed', 'or_number' => $orNumber]);

} catch (Throwable $printErr) {
    error_log('[print-receipt] print failed for order ' . $order_id . ': ' . $printErr->getMessage());
    try { $printer->close(); } catch (Throwable $ignore) {}
    http_response_code(500);
    echo json_encode([
        'status'  => 'print_failed',
        'message' => 'Printing failed partway through. The sale is recorded — check the printer and reprint.',
    ]);
}
