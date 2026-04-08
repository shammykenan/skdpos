<?php

require __DIR__ . '/../../vendor/autoload.php';

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

// ===== RECEIVE DATA FROM JS =====
$d = json_decode(file_get_contents("php://input"), true);

// ===== CONNECT =====
$connector = new WindowsPrintConnector("POS58");
$printer = new Printer($connector);

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

function center($text, $width = 30) {
    $pad = floor(($width - strlen($text)) / 2);
    return str_repeat(" ", max(0, $pad)) . $text;
}

function divider($ch = '-', $width = 30) {
    return str_repeat($ch, $width);
}

// ===== DATE =====
date_default_timezone_set("Asia/Manila");

// ===== START PRINT =====

// ===== HEADER =====
$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
$printer->text("The Lounge by:\n");
$printer->text("CJV & TEK\n");
$printer->selectPrintMode();
$printer->text("Lias Town Plaza, Marilao\n");
$printer->text("+63 967 217 5801\n");
$printer->setJustification(Printer::JUSTIFY_LEFT);
$printer->text(divider('=', $COL) . "\n");

// "SALES RECEIPT" centered — use JUSTIFY_CENTER, no manual center()
$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->text("SALES RECEIPT\n");

// "[ PAID ]" — bold + centered
$printer->setEmphasis(true);
$printer->text("[ PAID ]\n");
$printer->setEmphasis(false);

$printer->setJustification(Printer::JUSTIFY_LEFT);
$printer->text(divider('-', $COL) . "\n");

// ===== ORDER INFO =====
$printer->text(row2col("No:", $d["orderNumber"], $COL) . "\n");
$printer->text(row2col("Date & Time:", date("M j, Y h:i A"), $COL) . "\n");
$printer->text(row2col("Cashier:", $d["cashierName"], $COL) . "\n");
if (!empty($d["customerName"])) {
    $printer->text(row2col("Customer:", $d["customerName"], $COL) . "\n");
}
$printer->text(row2col("Type:", $d["orderType"] === 'dine_in' ? 'Dine In' : 'Take Out', $COL) . "\n");
if ($d["orderType"] === 'dine_in' && !empty($d["tableName"])) {
    $printer->text(row2col("Table:", $d["tableName"], $COL) . "\n");
}
$printer->text(divider('-', $COL) . "\n");

// ===== ITEMS =====
foreach ($d["items"] as $item) {
    $name = !empty($item["variantName"])
        ? $item["name"] . " (" . $item["variantName"] . ")"
        : $item["name"];
    $printer->text(row2col($name, "x{$item['quantity']} " . number_format($item['price'], 2), $COL) . "\n");

    foreach ($item["addons"] as $a) {
    $addonName = "  +" . $a['name'];
    $printer->text(row2col($addonName, "x{$a['quantity']} " . number_format($a['price'] * $a['quantity'], 2), $COL) . "\n");
    }

    if (!empty($item["note"])) {
        $printer->text("    Note: {$item['note']}\n");
    }
}

$printer->text(divider('=', $COL) . "\n");

// ===== TOTAL =====
$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->setEmphasis(true);
$printer->setUnderline(1);
$printer->text("TOTAL: PHP " . number_format($d["total"], 2) . "\n");
$printer->setUnderline(0);
$printer->setEmphasis(false);
$printer->setJustification(Printer::JUSTIFY_LEFT);

// ===== PAYMENT =====
$printer->text(row2col("Method:", "CASH", $COL) . "\n");
$printer->text(row2col("Tendered:", "PHP " . number_format($d["amountPaid"], 2), $COL) . "\n");
$printer->text(row2col("Change:", "PHP " . number_format($d["changeAmt"], 2), $COL) . "\n");
$printer->text(divider('-', $COL) . "\n");

// ===== FOOTER =====
$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->text("\n"); // breathing room above footer
$printer->text("Thank you!\n");
$printer->text("Please come again\n");
$printer->text("\n"); // spacing below "Please come again"
$printer->text(divider('.', $COL) . "\n");
$printer->text("\n"); // small gap before cut

$printer->cut();
$printer->close();

echo json_encode(["status" => "printed"]);