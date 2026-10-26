<?php
/**
 * apply_discount.php
 * Validates and returns discount figures for a given order total.
 * Does NOT write to DB — that happens in pos_controller pay_and_submit.
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../core/VatHelper.php';
require_once __DIR__ . '/../core/authHelper.php';

requireRole(['admin', 'cashier']);
require_once __DIR__ . '/../core/csrfHelper.php';
csrf_require('json');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$discount_type = trim($_POST['discount_type'] ?? '');
$reference_no  = trim($_POST['reference_no']  ?? '');
$subtotal      = (float)($_POST['subtotal']    ?? 0);

// ── Validation ────────────────────────────────────────────────────
if (!in_array($discount_type, ['senior', 'pwd', 'promo', 'none'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid discount type.']);
    exit;
}

if ($subtotal <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid subtotal.']);
    exit;
}

if (in_array($discount_type, ['senior', 'pwd']) && empty($reference_no)) {
    echo json_encode(['success' => false, 'message' => 'Please enter the Senior/PWD ID number before applying the discount.']);
    exit;
}

// ── Compute discount ──────────────────────────────────────────────
try {
    if ($discount_type === 'senior') {
        $pct = VatHelper::getSeniorDiscountPct($pdo);
    } elseif ($discount_type === 'pwd') {
        $pct = VatHelper::getPwdDiscountPct($pdo);
    } elseif ($discount_type === 'promo') {
        // promo — flat percentage sent from client
        $pct      = (float)($_POST['promo_pct'] ?? 0);
        $maxPromo = VatHelper::getMaxPromoPct($pdo);
        if ($pct <= 0 || $pct > 100) {
            echo json_encode(['success' => false, 'message' => 'Invalid promo percentage.']);
            exit;
        }
        if ($pct > $maxPromo) {
            echo json_encode([
                'success' => false,
                'message' => 'Promo discount cannot exceed ' . number_format($maxPromo, 0)
                             . '%. Ask an administrator to raise the limit in BIR settings.',
            ]);
            exit;
        }
    } else {
        $pct = 0;
    }

    $result = VatHelper::applyDiscount($discount_type, $subtotal, $pct, $pdo);

    echo json_encode([
        'success'         => true,
        'discount_type'   => $discount_type,
        'discount_pct'    => $pct,
        'discount_amount' => $result['discount_amount'],
        'total_after'     => $result['total_after_discount'],
        'vatable_sales'   => $result['vatable_sales'],
        'vat_amount'      => $result['vat_amount'],
        'vat_exempt'      => $result['vat_exempt_amount'],
        'zero_rated'      => $result['zero_rated_amount'],
        'reference_no'    => $reference_no,
    ]);

} catch (Throwable $e) {
    error_log('[apply_discount] ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error computing discount.']);
}
