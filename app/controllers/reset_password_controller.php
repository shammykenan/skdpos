<?php
// app/controllers/reset_password_controller.php

$token       = trim(urldecode($_GET['token'] ?? ''));
$error       = '';
$success     = '';
$valid_token = false;
$reset       = null;
$baseUrl = $_ENV['BASE_URL'] ?? 'http://localhost/coffee_shop/public';
if (empty($token)) {
    $error = 'Invalid or missing reset token.';

} else {
    $stmt = $pdo->prepare(
        "SELECT pr.reset_id, pr.user_id, u.full_name, u.email
         FROM password_resets pr
         JOIN users u ON u.user_id = pr.user_id
         WHERE pr.token = ?
           AND pr.used = 0
           AND pr.expires_at > NOW()
         LIMIT 1"
    );
    $stmt->execute([$token]);
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reset) {
        $error = 'This reset link is invalid or has expired. Please request a new one.';
    } else {
        $valid_token = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {

    $new_password     = $_POST['new_password']     ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (strlen($new_password) < 8) {
        $error = 'Password must be at least 8 characters.';

    } elseif ($new_password !== $confirm_password) {
        $error = 'Passwords do not match.';

    } else {
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        // Update password
        $stmt_update = $pdo->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE user_id = ?");
        $stmt_update->execute([$hashed_password, $reset['user_id']]);

        // Mark token as used
        $stmt_used = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE reset_id = ?");
        $stmt_used->execute([$reset['reset_id']]);

        // Log the action
        $stmt_log = $pdo->prepare(
            "INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)"
        );
        $stmt_log->execute([
            $reset['user_id'],
            'password_reset_success',
            'Password successfully reset via email link.',
            $_SERVER['REMOTE_ADDR'] ?? null
        ]);

        $success     = 'Your password has been reset successfully. You can now log in.';
        $valid_token = false;
    }
}

require_once BASE_PATH . '/app/views/auth/reset_password.php';