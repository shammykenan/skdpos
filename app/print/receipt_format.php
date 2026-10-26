<?php
/**
 * Shared ESC/POS text formatting helpers for 58mm receipt paper.
 *
 * All width math is multibyte-aware: strlen()/substr() count BYTES, so a single
 * "n with tilde" (2 bytes in UTF-8) would shift every column on that line.
 * Filipino item and business names hit this constantly.
 */

if (!defined('RECEIPT_COLS')) {
    define('RECEIPT_COLS', 32); // 58mm printer, Font A
}

function rf_len(string $s): int
{
    return function_exists('mb_strlen') ? mb_strlen($s, 'UTF-8') : strlen($s);
}

function rf_sub(string $s, int $start, ?int $len = null): string
{
    if (function_exists('mb_substr')) {
        return $len === null ? mb_substr($s, $start, null, 'UTF-8')
                             : mb_substr($s, $start, $len, 'UTF-8');
    }
    return $len === null ? substr($s, $start) : substr($s, $start, $len);
}

function rpad(string $s, int $n): string
{
    $l = rf_len($s);
    return $l >= $n ? rf_sub($s, 0, $n) : $s . str_repeat(' ', $n - $l);
}

function row2col(string $label, string $value, int $total = RECEIPT_COLS): string
{
    $maxLabel = $total - rf_len($value) - 1;
    if ($maxLabel < 1) {
        $maxLabel = 1;
    }
    if (rf_len($label) > $maxLabel) {
        $label = rf_sub($label, 0, max(1, $maxLabel - 1)) . '.';
    }
    return rpad($label, $maxLabel) . ' ' . $value;
}

function centerText(string $text, int $width = RECEIPT_COLS): string
{
    $pad = (int) floor(($width - rf_len($text)) / 2);
    return str_repeat(' ', max(0, $pad)) . $text;
}

function divider(string $ch = '-', int $width = RECEIPT_COLS): string
{
    return str_repeat($ch, $width);
}

function wrapText(string $text, int $width = RECEIPT_COLS): string
{
    $lines = [];
    $line  = '';
    foreach (explode(' ', $text) as $word) {
        if ($line !== '' && rf_len($line) + 1 + rf_len($word) > $width) {
            $lines[] = $line;
            $line    = $word;
        } else {
            $line .= ($line === '' ? '' : ' ') . $word;
        }
    }
    if ($line !== '') {
        $lines[] = $line;
    }
    return implode("\n", $lines);
}

function phpeso(float $v): string
{
    return 'PHP ' . number_format($v, 2);
}
