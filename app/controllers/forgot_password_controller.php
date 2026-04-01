<?php
// app/controllers/forgot_password_controller.php

require_once BASE_PATH . '/app/services/mailer.php';

$error   = '';
$success = '';
$baseUrl = $_ENV['BASE_URL'] ?? 'http://localhost/dinewithdinpos/public';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    // ----------------------------
    // RATE LIMITER (IP-based)
    // ----------------------------
    $limit = 3;
    $window_minutes = 15;

    $stmt_rate = $pdo->prepare("
        SELECT COUNT(*) 
        FROM password_resets pr
        JOIN users u ON u.user_id = pr.user_id
        WHERE pr.created_at >= DATE_SUB(NOW(), INTERVAL ? MINUTE)
        AND pr.ip_address = ?
    ");
    $stmt_rate->execute([$window_minutes, $ip]);
    $request_count = $stmt_rate->fetchColumn();

    if ($request_count >= $limit) {
        $error = "Too many reset requests. Please try again after {$window_minutes} minutes.";
    }

    // ----------------------------
    // VALIDATION
    // ----------------------------
    elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {

        $stmt = $pdo->prepare("
            SELECT user_id, full_name 
            FROM users 
            WHERE email = ? AND is_active = 1 
            LIMIT 1
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Always show same message (prevent enumeration)
        $success = 'If that email is registered, a reset link has been sent.';

        if ($user) {

            // ----------------------------
            // OPTIONAL: EMAIL RATE LIMIT
            // ----------------------------
            $stmt_email_limit = $pdo->prepare("
                SELECT COUNT(*) 
                FROM password_resets
                WHERE user_id = ?
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? MINUTE)
            ");
            $stmt_email_limit->execute([$user['user_id'], $window_minutes]);
            $email_count = $stmt_email_limit->fetchColumn();

            if ($email_count >= $limit) {
                // silently ignore (no error to avoid info leak)
                require_once BASE_PATH . '/app/views/auth/forgot_password.php';
                return;
            }

            // ----------------------------
            // INVALIDATE OLD TOKENS
            // ----------------------------
            $stmt_invalidate = $pdo->prepare("
                UPDATE password_resets 
                SET used = 1 
                WHERE user_id = ? AND used = 0
            ");
            $stmt_invalidate->execute([$user['user_id']]);

            // ----------------------------
            // GENERATE TOKEN
            // ----------------------------
            $token = bin2hex(random_bytes(32));

            $stmt_insert = $pdo->prepare("
                INSERT INTO password_resets (user_id, token, expires_at, ip_address)
                VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR), ?)
            ");
            $stmt_insert->execute([$user['user_id'], $token, $ip]);

            // ----------------------------
            // BUILD RESET LINK
            // ----------------------------
            $reset_url = $baseUrl . '/index.php?page=reset_password&token=' . $token;

            // ----------------------------
            // SEND EMAIL
            // ----------------------------
            $sent = send_password_reset_email($email, $user['full_name'], $reset_url);

            if (!$sent) {
                error_log("Failed to send reset email to: {$email}");
            }

            // ----------------------------
            // LOG ACTIVITY
            // ----------------------------
            $stmt_log = $pdo->prepare("
                INSERT INTO activity_logs (user_id, action, details, ip_address)
                VALUES (?, ?, ?, ?)
            ");
            $stmt_log->execute([
                $user['user_id'],
                'forgot_password_request',
                "Password reset requested for email: {$email}",
                $ip
            ]);
        }
    }
}

require_once BASE_PATH . '/app/views/auth/forgot_password.php';