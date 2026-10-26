<?php
/**
 * AJAX Handler: Save BIR / Business Info Settings
 * Route: index.php?page=ajax_save_bir_settings
 * Role:  admin only
 *
 * Loaded via router — session, BASE_PATH, $pdo already available.
 */


require_once BASE_PATH . '/app/models/businessinfo.php';
require_once __DIR__ . '/../core/csrfHelper.php';
csrf_require('json');

header('Content-Type: application/json');

// Auth guard
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// ------------------------------------------------------------------
// Validate required fields
// ------------------------------------------------------------------
$required = ['business_name', 'address', 'tin'];
foreach ($required as $field) {
    if (empty(trim($_POST[$field] ?? ''))) {
        echo json_encode([
            'success' => false,
            'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required.',
        ]);
        exit;
    }
}

// TIN format: loose validation — digits and dashes only, at least 9 digits
$tin = trim($_POST['tin'] ?? '');
$tinDigits = preg_replace('/[^0-9]/', '', $tin);
if (strlen($tinDigits) < 9) {
    echo json_encode(['success' => false, 'message' => 'TIN must have at least 9 digits.']);
    exit;
}

// VAT rate sanity check
$vatRate = (float)($_POST['vat_rate'] ?? 12.00);
if ($vatRate < 0 || $vatRate > 100) {
    echo json_encode(['success' => false, 'message' => 'VAT rate must be between 0 and 100.']);
    exit;
}

// Discount sanity check
$pwdDiscount    = (float)($_POST['min_pwd_discount']    ?? 20.00);
$seniorDiscount = (float)($_POST['min_senior_discount'] ?? 20.00);
if ($pwdDiscount < 0 || $pwdDiscount > 100 || $seniorDiscount < 0 || $seniorDiscount > 100) {
    echo json_encode(['success' => false, 'message' => 'Discount rates must be between 0 and 100.']);
    exit;
}

$maxPromo = (float)($_POST['max_promo_discount'] ?? 20.00);
if ($maxPromo < 0 || $maxPromo > 100) {
    echo json_encode(['success' => false, 'message' => 'Max promo discount must be between 0 and 100.']);
    exit;
}

// Printed document label — must match the sample layout approved on the PTU.
// Restricted to known BIR document types so a typo cannot reach every receipt.
$allowedLabels = ['SALES INVOICE', 'SERVICE INVOICE', 'INVOICE', 'OFFICIAL RECEIPT', 'SALES RECEIPT'];
$documentLabel = strtoupper(trim($_POST['document_label'] ?? '')) ?: 'SALES INVOICE';
if (!in_array($documentLabel, $allowedLabels, true)) {
    echo json_encode([
        'success' => false,
        'message' => 'Document label must be one of: ' . implode(', ', $allowedLabels) . '.',
    ]);
    exit;
}

// ------------------------------------------------------------------
// Save
// ------------------------------------------------------------------
$data = [
    'business_name'       => trim($_POST['business_name']),
    'address'             => trim($_POST['address']),
    'tin'                 => $tin,
    'vat_registered'      => isset($_POST['vat_registered']) ? 1 : 0,
    'vat_rate'            => $vatRate,
    'machine_serial_no'   => trim($_POST['machine_serial_no'] ?? ''),
    'min_number'          => trim($_POST['min_number']        ?? ''),
    'ptu_number'          => trim($_POST['ptu_number']        ?? ''),
    'accreditation_no'    => trim($_POST['accreditation_no']  ?? ''),
    'document_label'      => $documentLabel,
    'min_pwd_discount'    => $pwdDiscount,
    'min_senior_discount' => $seniorDiscount,
    'max_promo_discount'  => $maxPromo,
];

try {
    $saved = saveBusinessInfo($pdo, $data);

    if (!$saved) {
        echo json_encode(['success' => false, 'message' => 'No changes were saved. Please try again.']);
        exit;
    }

    // Activity log
    if (function_exists('logActivity')) {
        logActivity($pdo, $_SESSION['user_id'], 'update_bir_settings', 'Updated BIR / business info settings.');
    }

    echo json_encode(['success' => true, 'message' => 'BIR settings saved successfully.']);

} catch (Exception $e) {
    error_log('save_bir_settings error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error. Please try again.']);
}