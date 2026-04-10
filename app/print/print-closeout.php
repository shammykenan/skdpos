<?php
require __DIR__ . '/../../vendor/autoload.php';

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

// ===== RECEIVE DATA =====
$raw = file_get_contents("php://input");

if (!$raw) {
    echo json_encode([
        "status"  => "error",
        "message" => "No data received",
        "debug"   => [
            "raw_length"   => 0,
            "content_type" => $_SERVER['CONTENT_TYPE'] ?? 'none',
            "method"       => $_SERVER['REQUEST_METHOD'],
        ]
    ]);
    exit;
}

$d = json_decode($raw, true);
if (!$d) {
    echo json_encode(["status" => "error", "message" => "JSON decode failed", "raw" => $raw]);
    exit;
}

// ===== PRINTER DETECTION =====
$printerAvailable = false;
$connector        = null;
$printer          = null;

try {
    $connector        = new WindowsPrintConnector("POS58");
    $printer          = new Printer($connector);
    $printerAvailable = true;
} catch (Exception $e) {
    error_log("Printer not available: " . $e->getMessage());
    $printerAvailable = false;
}

if (!$printerAvailable) {
    echo json_encode(["status" => "no_printer", "message" => "No printer available"]);
    exit;
}

// ===== CONFIG =====
$COL = 30;

// ===== HELPERS =====
function rpad($s, $n) {
    return strlen($s) >= $n ? substr($s, 0, $n) : $s . str_repeat(" ", $n - strlen($s));
}
function row2col($label, $value, $total = 30) {
    $maxLabel = $total - strlen($value) - 1;
    if (strlen($label) > $maxLabel) {
        $label = substr($label, 0, $maxLabel - 1) . '.';
    }
    return "    " . rpad($label, $maxLabel - 4) . " " . $value;
}
function divider($ch = '-', $width = 30) {
    return str_repeat($ch, $width);
}

date_default_timezone_set("Asia/Manila");

// ===== PULL VALUES =====
$cashier_name      = $d['cashier_name']      ?? '—';
$username          = $d['username']           ?? '—';
$shift_date        = $d['shift_date']         ?? date('F d, Y');
$printed_at        = $d['printed_at']         ?? date('h:i A') . ' (PHT)';
$total_orders      = (int)   ($d['total_orders']      ?? 0);
$cancelled_orders  = (int)   ($d['cancelled_orders']  ?? 0);
$payment_count     = (int)   ($d['payment_count']     ?? 0);
$admin_processed   = (int)   ($d['admin_processed']   ?? 0);
$gross_sales       = (float) ($d['gross_sales']       ?? 0);
$total_discounts   = (float) ($d['total_discounts']   ?? 0);
$net_sales         = (float) ($d['net_sales']         ?? 0);
$net_sales_excl    = (float) ($d['net_sales_excl']    ?? 0);
$vat_collected     = (float) ($d['vat_collected']     ?? 0);
$total_refunds     = (float) ($d['total_refunds']     ?? 0);
$cash_received     = (float) ($d['cash_received']     ?? 0);
$change_given      = (float) ($d['change_given']      ?? 0);
$cash_to_turn_over = (float) ($d['cash_to_turn_over'] ?? 0);
$total_waste_cost  = (float) ($d['total_waste_cost']  ?? 0);
$top_items         = $d['top_items'] ?? [];

// ===== PRINT =====

$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
$printer->text("The Lounge\n");
$printer->selectPrintMode();
$printer->text("by CJV & TEK\n");
$printer->setJustification(Printer::JUSTIFY_LEFT);
$printer->text(divider('=', $COL) . "\n");

$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->setEmphasis(true);
$printer->text("SHIFT CLOSE-OUT REPORT\n");
$printer->setEmphasis(false);
$printer->setJustification(Printer::JUSTIFY_LEFT);
$printer->text(divider('-', $COL) . "\n");

$printer->setEmphasis(true);
$printer->text("    CASHIER INFO\n");
$printer->setEmphasis(false);
$printer->text(row2col("Cashier:", $cashier_name, $COL) . "\n");
$printer->text(row2col("Username:", $username, $COL) . "\n");
$printer->text(row2col("Date:", $shift_date, $COL) . "\n");
$printer->text(row2col("Printed at:", $printed_at, $COL) . "\n");
$printer->text(divider('-', $COL) . "\n");

$printer->setEmphasis(true);
$printer->text("    ORDER SUMMARY\n");
$printer->setEmphasis(false);
$printer->text(row2col("Orders handled:", (string)$total_orders, $COL) . "\n");
$printer->text(row2col("Cancelled:", (string)$cancelled_orders, $COL) . "\n");
$printer->text(row2col("Transactions:", (string)$payment_count, $COL) . "\n");
if ($admin_processed > 0) {
    $printer->text(row2col("Admin-processed:", (string)$admin_processed, $COL) . "\n");
}
$printer->text(divider('-', $COL) . "\n");

$printer->setEmphasis(true);
$printer->text("    SALES BREAKDOWN\n");
$printer->setEmphasis(false);
$printer->text(row2col("Gross sales:", "PHP " . number_format($gross_sales, 2), $COL) . "\n");
if ($total_discounts > 0) {
    $printer->text(row2col("Discounts:", "- " . number_format($total_discounts, 2), $COL) . "\n");
}
$printer->text(row2col("Net sales (w/ VAT):", "PHP " . number_format($net_sales, 2), $COL) . "\n");
$printer->text(row2col("  Excl. VAT:", "PHP " . number_format($net_sales_excl, 2), $COL) . "\n");
$printer->text(row2col("  VAT (12%):", "PHP " . number_format($vat_collected, 2), $COL) . "\n");
if ($total_refunds > 0) {
    $printer->text(row2col("Refunds:", "- " . number_format($total_refunds, 2), $COL) . "\n");
}
$printer->text(divider('-', $COL) . "\n");

if (!empty($top_items)) {
    $printer->setEmphasis(true);
    $printer->text("    TOP ITEMS THIS SHIFT\n");
    $printer->setEmphasis(false);
    foreach ($top_items as $i => $item) {
        $label = ($i + 1) . ". " . $item['item_name'];
        if (!empty($item['variant_name'])) {
            $label .= " (" . $item['variant_name'] . ")";
        }
        $printer->text(row2col($label, "x" . $item['units_sold'], $COL) . "\n");
    }
    $printer->text(divider('-', $COL) . "\n");
}

$printer->setEmphasis(true);
$printer->text("    CASH RECONCILIATION\n");
$printer->setEmphasis(false);
$printer->text(row2col("Cash received:", "PHP " . number_format($cash_received, 2), $COL) . "\n");
$printer->text(row2col("Change given:", "- " . number_format($change_given, 2), $COL) . "\n");
$printer->text(divider('=', $COL) . "\n");

$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->setEmphasis(true);
$printer->text("CASH TO TURN OVER\n");
$printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
$printer->text("PHP " . number_format($cash_to_turn_over, 2) . "\n");
$printer->selectPrintMode();
$printer->setEmphasis(false);
$printer->setJustification(Printer::JUSTIFY_LEFT);
$printer->text(divider('=', $COL) . "\n");

if ($total_waste_cost > 0) {
    $printer->text(row2col("Waste written off:", "PHP " . number_format($total_waste_cost, 2), $COL) . "\n");
    $printer->text(divider('-', $COL) . "\n");
}

$printer->text("\n");
$printer->text("    Cashier signature:\n");
$printer->text("\n");
$printer->text(divider('_', $COL) . "\n");
$printer->text("\n");
$printer->text("    Received by:\n");
$printer->text("\n");
$printer->text(divider('_', $COL) . "\n");

$printer->text("\n");
$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->text("Thank you for your service today.\n");
$printer->text("Dine with Din Cafeteria\n");
$printer->text(date('Y') . "\n");
$printer->text("\n");
$printer->text("Powered by SKD Software\n");
$printer->text("\n"); // small gap before cut
$printer->text("\n"); // small gap before cut

$printer->cut();
$printer->close();

echo json_encode(["status" => "printed"]);