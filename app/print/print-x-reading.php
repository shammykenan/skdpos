<?php
/**
 * print-x-reading.php
 * Mid-shift X-reading. Reads only — nothing is written or reset.
 */

require __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../core/authHelper.php';
require_once __DIR__ . '/../core/GrandTotal.php';
require_once __DIR__ . '/../models/ZReading.php';
require_once __DIR__ . '/../models/businessinfo.php';
require_once __DIR__ . '/receipt_format.php';

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

header('Content-Type: application/json');
date_default_timezone_set('Asia/Manila');

$COL = RECEIPT_COLS;

$raw  = json_decode(file_get_contents('php://input'), true) ?: [];
$date = trim($raw['date'] ?? $_POST['date'] ?? $_GET['date'] ?? date('Y-m-d'));
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid date.']);
    exit;
}

$z    = new ZReading($pdo);
$data = $z->xReading($date);

$info      = getBusinessInfo($pdo);
$isVat     = (bool) $info['vat_registered'];
$vatRate   = (float) $info['vat_rate'];
$bizName   = $info['business_name']     ?: 'The Lounge by: CJV & TEK';
$bizAddr   = $info['address']           ?: '';
$tin       = $info['tin']               ?: '';
$machineSN = $info['machine_serial_no'] ?: '';
$minNo     = $info['min_number']        ?? '';
$ptuNo     = $info['ptu_number']        ?: '';

try {
    $connector = new WindowsPrintConnector('POS58');
    $printer   = new Printer($connector);
} catch (Throwable $e) {
    error_log('Printer not available: ' . $e->getMessage());
    echo json_encode(['status' => 'no_printer', 'message' => 'No printer available.']);
    exit;
}

try {
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
    if ($minNo !== '')     $printer->text('MIN: '        . $minNo     . "\n");
    if ($machineSN !== '') $printer->text('Machine SN: ' . $machineSN . "\n");
    if ($ptuNo !== '')     $printer->text('PTU No.: '    . $ptuNo     . "\n");

    $printer->text(divider('=', $COL) . "\n");
    $printer->setEmphasis(true);
    $printer->text("X-READING\n");
    $printer->setEmphasis(false);
    $printer->text("(does not reset totals)\n");
    $printer->setJustification(Printer::JUSTIFY_LEFT);
    $printer->text(divider('-', $COL) . "\n");

    $printer->text(row2col('For date:',   $date, $COL) . "\n");
    $printer->text(row2col('Printed:',    date('M j, Y  h:i A'), $COL) . "\n");
    $printer->text(row2col('Last Z No.:', (string) $data['last_z_counter'], $COL) . "\n");
    $printer->text(divider('-', $COL) . "\n");

    $printer->text(row2col('Beginning OR:', (string) ($data['beginning_or'] ?: '-'), $COL) . "\n");
    $printer->text(row2col('Ending OR:',    (string) ($data['ending_or']    ?: '-'), $COL) . "\n");
    $printer->text(row2col('Transactions:', (string) $data['transaction_count'], $COL) . "\n");
    $printer->text(divider('-', $COL) . "\n");

    $printer->text(row2col('Gross Sales:',     phpeso((float) $data['gross_sales']),      $COL) . "\n");
    if ($isVat) {
        $printer->text(row2col('VATable Sales:',   phpeso((float) $data['vatable_sales']),    $COL) . "\n");
        $printer->text(row2col('VAT (' . number_format($vatRate, 0) . '%):',
                                                   phpeso((float) $data['vat_amount']),       $COL) . "\n");
        $printer->text(row2col('VAT-Exempt:',      phpeso((float) $data['vat_exempt_sales']), $COL) . "\n");
        $printer->text(row2col('Zero-Rated:',      phpeso((float) $data['zero_rated_sales']), $COL) . "\n");
    }
    $printer->text(divider('-', $COL) . "\n");
    $printer->text(row2col('SC Discounts:',    phpeso((float) $data['senior_discounts']), $COL) . "\n");
    $printer->text(row2col('PWD Discounts:',   phpeso((float) $data['pwd_discounts']),    $COL) . "\n");
    $printer->text(row2col('Promo Discounts:', phpeso((float) $data['promo_discounts']),  $COL) . "\n");
    $printer->text(row2col('Voids (' . $data['void_count'] . '):',
                                               phpeso((float) $data['total_voids']),      $COL) . "\n");
    $printer->text(row2col('Refunds (' . $data['refund_count'] . '):',
                                               phpeso((float) $data['total_refunds']),    $COL) . "\n");
    $printer->text(divider('=', $COL) . "\n");
    $printer->setEmphasis(true);
    $printer->text(row2col('NET SALES:', phpeso((float) $data['net_sales']), $COL) . "\n");
    $printer->setEmphasis(false);
    $printer->text(divider('-', $COL) . "\n");
    $printer->text(row2col('Grand Total:', phpeso((float) $data['grand_total']), $COL) . "\n");

    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->text("\n");
    $printer->text("*** NOT AN OFFICIAL ***\n");
    $printer->text("*** SALES DOCUMENT  ***\n");
    $printer->text("\n\n");
    $printer->feed(3);
    $printer->cut();
    $printer->close();

    echo json_encode(['status' => 'printed', 'date' => $date]);

} catch (Throwable $printErr) {
    error_log('[print-x-reading] ' . $printErr->getMessage());
    try { $printer->close(); } catch (Throwable $ignore) {}
    http_response_code(500);
    echo json_encode(['status' => 'print_failed', 'message' => 'Printing failed partway through.']);
}
