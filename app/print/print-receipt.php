<?php
/**
 * print-receipt.php
 * BIR-compliant ESC/POS receipt printer.
 */

require __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../core/VatHelper.php';
require_once __DIR__ . '/../models/BusinessInfo.php';

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

// ── Receive payload ───────────────────────────────────────────────
$d = json_decode(file_get_contents("php://input"), true);
if (!$d) {
    echo json_encode(["status" => "error", "message" => "No payload."]);
    exit;
}

// ── Extract discount + total from payload ─────────────────────────
$discountType   = $d['discountType']   ?? 'none';
$discountAmount = (float)($d['discountAmount'] ?? 0);
$total          = (float)($d['total']          ?? 0);   // already discounted total

// ── Printer detection ─────────────────────────────────────────────
try {
    $connector = new WindowsPrintConnector("POS58");
    $printer   = new Printer($connector);
} catch (Exception $e) {
    error_log("Printer not available: " . $e->getMessage());
    echo json_encode(["status" => "no_printer", "message" => "No printer available — order created successfully"]);
    exit;
}

// ── Load business info ────────────────────────────────────────────
$info      = getBusinessInfo($pdo);
$isVat     = (bool)$info['vat_registered'];
$vatRate   = (float)$info['vat_rate'];
$bizName   = !empty($info['business_name']) ? $info['business_name'] : 'Dine with Din';
$bizAddr   = !empty($info['address'])       ? $info['address']       : '';
$tin       = !empty($info['tin'])           ? $info['tin']           : '';
$machineSN = !empty($info['machine_serial_no']) ? $info['machine_serial_no'] : '';
$ptuNo     = !empty($info['ptu_number'])    ? $info['ptu_number']    : '';
$accredNo  = !empty($info['accreditation_no']) ? $info['accreditation_no'] : '';

// ── VAT figures from server payload ──────────────────────────────
$vatData      = $d['vat'] ?? null;
$vatableSales = 0.00;
$vatAmount    = 0.00;
$vatExemptSales = 0.00;
$zeroRatedSales = 0.00;
if ($vatData && $isVat) {
    $vatableSales = (float)($vatData['vatable_sales'] ?? 0);
    $vatAmount    = (float)($vatData['vat_amount']    ?? 0);
    $vatExemptSales = (float)($vatData['vat_exempt']  ?? 0);
    $zeroRatedSales = (float)($vatData['zero_rated']  ?? 0);
}

// ── Config ───────────────────────────────────────────────────────
$COL = 32;

// ── Helpers ──────────────────────────────────────────────────────
function rpad(string $s, int $n): string {
    return strlen($s) >= $n ? substr($s, 0, $n) : $s . str_repeat(' ', $n - strlen($s));
}
function row2col(string $label, string $value, int $total = 32): string {
    $maxLabel = $total - strlen($value) - 1;
    if ($maxLabel < 1) $maxLabel = 1;
    if (strlen($label) > $maxLabel) $label = substr($label, 0, $maxLabel - 1) . '.';
    return rpad($label, $maxLabel) . ' ' . $value;
}
function centerText(string $text, int $width = 32): string {
    $pad = (int)floor(($width - strlen($text)) / 2);
    return str_repeat(' ', max(0, $pad)) . $text;
}
function divider(string $ch = '-', int $width = 32): string {
    return str_repeat($ch, $width);
}
function wrapText(string $text, int $width = 32): string {
    $lines = [];
    $words = explode(' ', $text);
    $line  = '';
    foreach ($words as $word) {
        if ($line !== '' && strlen($line) + 1 + strlen($word) > $width) {
            $lines[] = $line;
            $line    = $word;
        } else {
            $line .= ($line === '' ? '' : ' ') . $word;
        }
    }
    if ($line !== '') $lines[] = $line;
    return implode("\n", $lines);
}
function phpeso(float $v): string {
    return 'PHP ' . number_format($v, 2);
}

date_default_timezone_set('Asia/Manila');

// ═══════════════════════════════════════════════════════
// START PRINT
// ═══════════════════════════════════════════════════════

// ── Business header ───────────────────────────────────
$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
$printer->text($bizName . "\n");
$printer->selectPrintMode();

if (!empty($bizAddr)) {
    foreach (explode("\n", wrapText($bizAddr, $COL)) as $line) {
        $printer->text(trim($line) . "\n");
    }
}
if (!empty($tin))       $printer->text("TIN: "           . $tin       . "\n");
if (!empty($machineSN)) $printer->text("Machine SN: "    . $machineSN . "\n");
if (!empty($ptuNo))     $printer->text("PTU No.: "       . $ptuNo     . "\n");
if (!empty($accredNo))  $printer->text("Accreditation: " . $accredNo  . "\n");

$printer->setJustification(Printer::JUSTIFY_LEFT);
$printer->text(divider('=', $COL) . "\n");

// ── Receipt type header ───────────────────────────────
$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->text("OFFICIAL RECEIPT\n");
$printer->setEmphasis(true);
$printer->text("[ PAID ]\n");
$printer->setEmphasis(false);
$printer->setJustification(Printer::JUSTIFY_LEFT);
$printer->text(divider('-', $COL) . "\n");

// ── OR number + order info ────────────────────────────
$orNumber = $d['orNumber'] ?? ($d['orderNumber'] ?? 'N/A');
$printer->text(row2col("OR No.:",   $orNumber,                        $COL) . "\n");
$printer->text(row2col("Date:",     date("M j, Y  h:i A"),            $COL) . "\n");
$printer->text(row2col("Cashier:",  $d['cashierName']  ?? '',         $COL) . "\n");
if (!empty($d['customerName'])) {
    $printer->text(row2col("Customer:", $d['customerName'], $COL) . "\n");
}
$printer->text(row2col("Type:", ($d['orderType'] ?? '') === 'dine_in' ? 'Dine In' : 'Take Out', $COL) . "\n");
if (($d['orderType'] ?? '') === 'dine_in' && !empty($d['tableName'])) {
    $printer->text(row2col("Table:", $d['tableName'], $COL) . "\n");
}
$printer->text(divider('-', $COL) . "\n");

// ── Items ─────────────────────────────────────────────
$subtotalBeforeDiscount = 0.00;

foreach ($d['items'] as $item) {
    $name      = !empty($item['variantName'])
                 ? $item['name'] . ' (' . $item['variantName'] . ')'
                 : $item['name'];
    $unitPrice = (float)$item['price'];
    $qty       = (int)$item['quantity'];
    $lineTotal = $unitPrice * $qty;

    // Add-on totals for this line
    $addonLineTotal = 0.00;
    foreach ($item['addons'] ?? [] as $a) {
        $addonLineTotal += (float)$a['price'] * (int)$a['quantity'];
    }
    $lineTotal += $addonLineTotal * $qty;   // addons priced per item qty

    $subtotalBeforeDiscount += $lineTotal;

    // Item line: name left, qty × price right
    $printer->text(row2col(
        $name,
        'x' . $qty . ' ' . number_format($unitPrice, 2),
        $COL
    ) . "\n");

    // Add-on lines
    foreach ($item['addons'] ?? [] as $a) {
        $addonLine = (float)$a['price'] * (int)$a['quantity'];
        $printer->text(row2col(
            '  +' . $a['name'],
            'x' . $a['quantity'] . ' ' . number_format($a['price'], 2),
            $COL
        ) . "\n");
    }

    // Line subtotal (only if qty > 1 or has addons, so it's clear)
    if ($qty > 1 || $addonLineTotal > 0) {
        $printer->text(row2col('  Subtotal:', number_format($lineTotal, 2), $COL) . "\n");
    }

    if (!empty($item['note'])) {
        $printer->text("  Note: " . $item['note'] . "\n");
    }
}

$printer->text(divider('-', $COL) . "\n");
$printer->text(row2col("Subtotal:", phpeso($subtotalBeforeDiscount), $COL) . "\n");

// ── BIR-compliant discount + VAT breakdown ────────────
if ($discountType !== 'none' && $discountAmount > 0) {

    // Senior/PWD:
    // 1) remove VAT from tagged price
    // 2) apply discount on the VAT-exclusive amount
    // 3) final amount is VAT-exempt (do not add VAT back)
    if (in_array($discountType, ['senior', 'pwd']) && $isVat) {
        $rate              = $vatRate / 100;
        $divisor           = 1 + $rate;
        $vatExclBefore     = round($subtotalBeforeDiscount / $divisor, 2);

        $discLabel = $discountType === 'senior' ? 'SC Discount (20%)' : 'PWD Discount (20%)';

        $printer->text(row2col("VAT-Excl. Amount:", phpeso($vatExclBefore),     $COL) . "\n");
        $printer->text(row2col($discLabel . ":",    '-' . phpeso($discountAmount), $COL) . "\n");

        $vatableSales   = 0.00;
        $vatAmount      = 0.00;
        $vatExemptSales = $total;

    } elseif (in_array($discountType, ['senior', 'pwd'], true)) {
        $discLabel = $discountType === 'senior' ? 'SC Discount (20%)' : 'PWD Discount (20%)';
        $printer->text(row2col($discLabel . ":", '-' . phpeso($discountAmount), $COL) . "\n");
    } elseif ($discountType === 'promo') {
        $printer->text(row2col("Promo Discount:", '-' . phpeso($discountAmount), $COL) . "\n");
    }
}

if ($isVat) {
    if ($vatableSales > 0) {
        $printer->text(row2col("VATable Sales:", phpeso($vatableSales), $COL) . "\n");
    }
    if ($vatAmount > 0) {
        $printer->text(row2col("VAT (" . number_format($vatRate, 0) . "%):", phpeso($vatAmount), $COL) . "\n");
    }
    if ($vatExemptSales > 0) {
        $printer->text(row2col("VAT-Exempt Sales:", phpeso($vatExemptSales), $COL) . "\n");
    }
    if ($zeroRatedSales > 0) {
        $printer->text(row2col("Zero-Rated Sales:", phpeso($zeroRatedSales), $COL) . "\n");
    }
}

$printer->text(divider('=', $COL) . "\n");

// ── Grand total ───────────────────────────────────────
$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->setEmphasis(true);
$printer->setUnderline(1);
$printer->text("TOTAL: " . phpeso($total) . "\n");
$printer->setUnderline(0);
$printer->setEmphasis(false);
$printer->setJustification(Printer::JUSTIFY_LEFT);

// ── Payment ───────────────────────────────────────────
$printer->text(row2col("Method:",   "CASH",                             $COL) . "\n");
$printer->text(row2col("Tendered:", phpeso((float)($d['amountPaid'] ?? 0)), $COL) . "\n");
$printer->text(row2col("Change:",   phpeso((float)($d['changeAmt']  ?? 0)), $COL) . "\n");
$printer->text(divider('-', $COL) . "\n");

// ── Non-VAT disclaimer (BIR required) ────────────────
if (!$isVat) {
    $printer->text("\n");
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->text("THIS DOCUMENT IS NOT VALID\n");
    $printer->text("FOR CLAIMING INPUT TAXES\n");
    $printer->setJustification(Printer::JUSTIFY_LEFT);
    $printer->text(divider('-', $COL) . "\n");
}

// ── SC/PWD BIR disclaimer (RA 9994 / RA 9442) ────────
if (in_array($discountType, ['senior', 'pwd'])) {
    $printer->text("\n");
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $law = $discountType === 'senior' ? 'RA 9994' : 'RA 9442';
    $printer->text($law . " — " . ($discountType === 'senior' ? 'Senior Citizen' : 'PWD') . " Discount\n");
    $printer->setJustification(Printer::JUSTIFY_LEFT);
    $printer->text(divider('-', $COL) . "\n");
}

// ── Footer ────────────────────────────────────────────
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

echo json_encode(["status" => "printed"]);
