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
