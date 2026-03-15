<?php
function requireRole(array $allowed_roles) {
    if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], $allowed_roles)) {
        $_SESSION['error'] = 'Access denied.';
        header('Location: index.php?page=login');
        exit;
    }
}

// Optional: Check role without redirect
function hasRole(string $role): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}
function checkLoginRateLimit(PDO $pdo, string $baseUrl): bool
{
    $ip = $_SERVER['REMOTE_ADDR'];
    $limit = 5;
    $windowMinutes = 10;
    $timeWindow = date('Y-m-d H:i:s', time() - $windowMinutes * 60);

    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM login_attempts
        WHERE ip_address = :ip
        AND success = 0
        AND attempt_time > :time_window
    ");
    $stmt->execute([
        ':ip' => $ip,
        ':time_window' => $timeWindow
    ]);

    $attempts = (int)$stmt->fetchColumn();

    if ($attempts >= $limit) {
        $_SESSION['error'] = "Too many login attempts from your IP. Try again after $windowMinutes minutes.";
        header('Location: ' . $baseUrl . '/index.php?page=login');
        exit;
    }

    return true; // not blocked
}

function recordLoginAttempt(PDO $pdo, string $username, bool $success): void
{
    $ip = $_SERVER['REMOTE_ADDR'];
    $limit = 5;
    $windowMinutes = 10;
    $timeWindow = date('Y-m-d H:i:s', time() - $windowMinutes * 60);

    // Only record if under the limit
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM login_attempts
        WHERE ip_address = :ip
        AND success = 0
        AND attempt_time > :time_window
    ");
    $stmt->execute([
        ':ip' => $ip,
        ':time_window' => $timeWindow
    ]);
    $attempts = (int)$stmt->fetchColumn();

    if ($attempts >= $limit) {
        // Do NOT record new attempt if blocked
        return;
    }

    $stmt = $pdo->prepare("
        INSERT INTO login_attempts (username, ip_address, success)
        VALUES (:username, :ip, :success)
    ");
    $stmt->execute([
        ':username' => trim($username),
        ':ip' => $ip,
        ':success' => $success ? 1 : 0
    ]);
}
function clearLoginAttempts(PDO $pdo, string $ip): void
{
    $stmt = $pdo->prepare("
        DELETE FROM login_attempts
        WHERE ip_address = :ip
    ");
    $stmt->execute([
        ':ip' => $ip
    ]);
}