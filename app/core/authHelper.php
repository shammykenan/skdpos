<?php

// ─────────────────────────────────────────────────────────────────────────────
// ROLE HELPERS
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Require one of the allowed roles — redirects to login if not authorized.
 */
function requireRole(array $allowed_roles): void {
    if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], $allowed_roles)) {
        $_SESSION['error'] = 'Access denied.';
        header('Location: index.php?page=login');
        exit;
    }
}

/**
 * Check if the current session user has a specific role.
 */
function hasRole(string $role): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// ─────────────────────────────────────────────────────────────────────────────
// LOGIN RATE LIMITING
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Block the request if the IP has exceeded the failed login limit.
 */
function checkLoginRateLimit(PDO $pdo, string $baseUrl): bool {
    $ip            = $_SERVER['REMOTE_ADDR'];
    $limit         = 5;
    $windowMinutes = 10;
    $timeWindow    = date('Y-m-d H:i:s', time() - $windowMinutes * 60);

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM login_attempts
        WHERE ip_address = :ip
          AND success = 0
          AND attempt_time > :time_window
    ");
    $stmt->execute([':ip' => $ip, ':time_window' => $timeWindow]);

    if ((int) $stmt->fetchColumn() >= $limit) {
        $_SESSION['error'] = "Too many login attempts from your IP. Try again after $windowMinutes minutes.";
        header('Location: ' . $baseUrl . '/index.php?page=login');
        exit;
    }

    return true;
}

/**
 * Record a login attempt (success or failure) for the given IP.
 * Does not record if the IP is already blocked.
 */
function recordLoginAttempt(PDO $pdo, string $username, bool $success): void {
    $ip            = $_SERVER['REMOTE_ADDR'];
    $limit         = 5;
    $windowMinutes = 10;
    $timeWindow    = date('Y-m-d H:i:s', time() - $windowMinutes * 60);

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM login_attempts
        WHERE ip_address = :ip
          AND success = 0
          AND attempt_time > :time_window
    ");
    $stmt->execute([':ip' => $ip, ':time_window' => $timeWindow]);

    if ((int) $stmt->fetchColumn() >= $limit) {
        return; // already blocked — do not record
    }

    $stmt = $pdo->prepare("
        INSERT INTO login_attempts (username, ip_address, success)
        VALUES (:username, :ip, :success)
    ");
    $stmt->execute([
        ':username' => trim($username),
        ':ip'       => $ip,
        ':success'  => $success ? 1 : 0,
    ]);
}

/**
 * Clear all login attempts for a given IP (called on successful login).
 */
function clearLoginAttempts(PDO $pdo, string $ip): void {
    $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE ip_address = :ip");
    $stmt->execute([':ip' => $ip]);
}