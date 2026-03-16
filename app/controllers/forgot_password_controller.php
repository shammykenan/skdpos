<?php
// app/controllers/forgot_password_controller.php

require_once BASE_PATH . '/app/services/mailer.php';

$error   = '';
$success = '';
$baseUrl = $_ENV['BASE_URL'] ?? 'http://localhost/coffee_shop/public';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';

    } else {
        $stmt = $pdo->prepare("SELECT user_id, full_name FROM users WHERE email = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Always show the same message to prevent user enumeration
        $success = 'If that email is registered, a reset link has been sent.';

        if ($user) {
            // Invalidate existing unused tokens
            $stmt_invalidate = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE user_id = ? AND used = 0");
            $stmt_invalidate->execute([$user['user_id']]);

            // Generate secure token
            // Use MySQL DATE_ADD(NOW()) so expires_at is in the same timezone
            // as the NOW() used in the validation query — avoids PHP/MySQL timezone mismatch
            $token = bin2hex(random_bytes(32));

            $stmt_insert = $pdo->prepare(
                "INSERT INTO password_resets (user_id, token, expires_at)
                 VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))"
            );
            $stmt_insert->execute([$user['user_id'], $token]);

            // Build reset URL
            $reset_url = ($_ENV['BASE_URL'] ?? 'http://localhost/coffee_shop/public') . '/index.php?page=reset_password&token=' . $token;

            // Send email
            $sent = send_password_reset_email($email, $user['full_name'], $reset_url);

            if (!$sent) {
                error_log("Failed to send reset email to: {$email}");
            }

            // Log the action
            $stmt_log = $pdo->prepare(
                "INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)"
            );
            $stmt_log->execute([
                $user['user_id'],
                'forgot_password_request',
                "Password reset requested for email: {$email}",
                $_SERVER['REMOTE_ADDR'] ?? null
            ]);
        }
    }
}

require_once BASE_PATH . '/app/views/auth/forgot_password.php';