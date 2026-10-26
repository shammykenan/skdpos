<?php
/**
 * print-z-reading.php
 * Receives JSON from JS, prints via ESC/POS (WindowsPrintConnector).
 * Mirrors print-receipt.php pattern exactly.
 */

require __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/receipt_format.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../models/businessinfo.php';

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

$d = json_decode(file_get_contents("php://input"), true);
if (!$d) {
    echo json_encode(["status" => "error", "message" => "No payload received."]);
    exit;
}

// ── Printer detection ─────────────────────────────────────────────
$printer = null;
try {
    $connector = new WindowsPrintConnector("POS58");
    $printer   = new Printer($connector);
} catch (Exception $e) {
    error_log("Z-Reading printer not available: " . $e->getMessage());
    echo json_encode(["status" => "no_printer", "message" => "No printer available."]);
    exit;
}

// ── Business info ─────────────────────────────────────────────────
$info      = getBusinessInfo($pdo);
$bizName   = !empty($info['business_name']) ? $info['business_name'] : 'The Lounge by: CJV & TEK';
$bizAddr   = !empty($info['address'])       ? $info['address']       : '';
$tin       = !empty($info['tin'])           ? $info['tin']           : '';
$machineSN = !empty($info['machine_serial_no']) ? $info['machine_serial_no'] : '';
$minNo     = !empty($info['min_number']) ? $info['min_number'] : '';
$ptuNo     = !empty($info['ptu_number'])    ? $info['ptu_number']    : '';
$accredNo  = !empty($info['accreditation_no']) ? $info['accreditation_no'] : '';

// ── Helpers ───────────────────────────────────────────────────────
$COL = 32;

function zRow($label, $value, $col = 32) {
    $maxLabel = $col - rf_len($value) - 1;
    if ($maxLabel < 1) $maxLabel = 1;
    if (rf_len($label) > $maxLabel) $label = rf_sub($label, 0, $maxLabel - 1) . '.';
    return str_pad($label, $maxLabel) . ' ' . $value . "\n";
}
function zCenter($text, $col = 32) {
    $pad = (int)floor(($col - rf_len($text)) / 2);
    return str_repeat(' ', max(0, $pad)) . $text . "\n";
}
function zDivider($ch = '-', $col = 32) {
    return str_repeat($ch, $col) . "\n";
}
function zMoney($v) {
    return number_format((float)$v, 2);
}

date_default_timezone_set('Asia/Manila');

// ── Start printing ────────────────────────────────────────────────

// Business header
// A mid-print failure must not leave the caller hanging on the fetch.
try {
$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
$printer->text($bizName . "\n");
$printer->selectPrintMode();
if ($bizAddr)   $printer->text(wordwrap($bizAddr, $COL, "\n", true) . "\n");
if ($tin)       $printer->text("TIN: " . $tin . "\n");
if ($machineSN) $printer->text("Machine SN: " . $machineSN . "\n");
if ($ptuNo)     $printer->text("PTU No.: " . $ptuNo . "\n");
if ($accredNo)  $printer->text("Accreditation: " . $accredNo . "\n");

$printer->setJustification(Printer::JUSTIFY_LEFT);
$printer->text(zDivider('=', $COL));

// Title
$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->setEmphasis(true);
$printer->text("Z - R E A D I N G\n");
$printer->setEmphasis(false);
$printer->setJustification(Printer::JUSTIFY_LEFT);
$printer->text(zDivider('-', $COL));

// Meta
$printer->text(zRow("Date:",      $d['reading_date'],   $COL));
$printer->text(zRow("Printed:",   $d['printed_at'],     $COL));
$printer->text(zRow("By:",        $d['generated_by'],   $COL));
$printer->text(zDivider('-', $COL));

// OR Range
$printer->text(zCenter("OR RANGE", $COL));
$printer->text(zRow("Beg. OR:",      $d['beginning_or'],                   $COL));
$printer->text(zRow("End. OR:",      $d['ending_or'],                      $COL));
$printer->text(zRow("Transactions:", (string)$d['transaction_count'],      $COL));
$printer->text(zDivider('-', $COL));

// Sales Summary
$printer->text(zCenter("SALES SUMMARY", $COL));
$printer->text(zRow("Gross Sales:",      'P' . zMoney($d['gross_sales']),      $COL));
$printer->text(zRow("VATable Sales:",    'P' . zMoney($d['vatable_sales']),    $COL));
$printer->text(zRow("VAT (12%):",        'P' . zMoney($d['vat_amount']),       $COL));
$printer->text(zRow("VAT-Exempt:",       'P' . zMoney($d['vat_exempt_sales']), $COL));
$printer->text(zRow("Zero-Rated:",       'P' . zMoney($d['zero_rated_sales']), $COL));
$printer->text(zDivider('-', $COL));

// Discounts
$printer->text(zCenter("DISCOUNTS", $COL));
$printer->text(zRow("Senior Citizen:",   'P' . zMoney($d['senior_discounts']), $COL));
$printer->text(zRow("PWD:",              'P' . zMoney($d['pwd_discounts']),    $COL));
$printer->text(zRow("Promo:",            'P' . zMoney($d['promo_discounts']),  $COL));
$printer->text(zRow("Total Discounts:",  'P' . zMoney($d['total_discounts']),  $COL));
$printer->text(zDivider('-', $COL));

// Voids & Refunds
$printer->text(zCenter("VOIDS & REFUNDS", $COL));
$printer->text(zRow("Void Count:",    (string)$d['void_count'] . ' order(s)',  $COL));
$printer->text(zRow("Total Voids:",   'P' . zMoney($d['total_voids']),         $COL));
$printer->text(zRow("Refund Count:",  (string)$d['refund_count'] . ' txn(s)',  $COL));
$printer->text(zRow("Total Refunds:", 'P' . zMoney($d['total_refunds']),       $COL));
$printer->text(zDivider('=', $COL));

// Net Sales — bold
$printer->setEmphasis(true);
$printer->text(zRow("NET SALES:", 'P' . zMoney($d['net_sales']), $COL));

// ── BIR accumulators ────────────────────────────────────────────
// New GT - Old GT must equal this day's gross sales. That identity is
// what makes the figure verifiable years later.
$printer->text(zDivider('-', $COL));
$printer->text(zRow("Z Counter:",     (string)($d['z_counter'] ?? 0),     $COL));
$printer->text(zRow("Reset Counter:", (string)($d['reset_counter'] ?? 0), $COL));
$printer->text(zRow("Old GT:", 'P' . zMoney($d['old_gt'] ?? 0), $COL));
$printer->text(zRow("New GT:", 'P' . zMoney($d['new_gt'] ?? 0), $COL));

// Chain head at close of day. This paper copy is the anchor: a ledger
// rebuilt later cannot reproduce a hash that was already printed and filed.
if (!empty($d['ledger_hash'])) {
    $printer->text(zDivider('-', $COL));
    $printer->text("Ledger verification code:\n");
    // 64 hex chars split across the 32-column paper
    foreach (str_split((string)$d['ledger_hash'], 32) as $chunk) {
        $printer->text($chunk . "\n");
    }
}
$printer->setEmphasis(false);
$printer->text(zDivider('=', $COL));

// Footer
$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->text("\n");
$printer->text("** END OF Z-READING **\n");
$printer->text("System-generated report\n");
$printer->text($d['printed_at'] . "\n");
$printer->text("\n");

$printer->feed(4);
$printer->cut();
$printer->close();

} catch (Throwable $printErr) {
    error_log('[print-z-reading] print failed: ' . $printErr->getMessage());
    try { $printer->close(); } catch (Throwable $ignore) {}
    http_response_code(500);
    echo json_encode([
        'status'  => 'print_failed',
        'message' => 'Printing failed partway through. Check the printer, then reprint.',
    ]);
    exit;
}

echo json_encode(["status" => "printed"]);