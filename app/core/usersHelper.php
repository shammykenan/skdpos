<?php
require_once __DIR__ . '/../../config/db.php';

function usernameExists(PDO $pdo, $username, $exclude_user_id = null) {
    try {
        if ($exclude_user_id !== null) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username AND user_id != :user_id");
            $stmt->execute([':username' => $username, ':user_id' => $exclude_user_id]);
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
            $stmt->execute([':username' => $username]);
        }
        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log("Error checking username: " . $e->getMessage());
        return false;
    }
}

function emailExists(PDO $pdo, $email, $exclude_user_id = null) {
    // Email can be null in database, so skip check if empty
    if (empty($email)) {
        return false;
    }
    
    try {
        if ($exclude_user_id !== null) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email AND email IS NOT NULL AND user_id != :user_id");
            $stmt->execute([':email' => $email, ':user_id' => $exclude_user_id]);
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email AND email IS NOT NULL");
            $stmt->execute([':email' => $email]);
        }
        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log("Error checking email: " . $e->getMessage());
        return false;
    }
}

function isValidRole($role) {
    $valid_roles = ['admin', 'cashier', 'kitchen'];
    return in_array($role, $valid_roles, true);
}

function getRoleDisplayName($role) {
    $role_names = [
        'admin' => 'Administrator',
        'cashier' => 'Cashier',
        'kitchen' => 'Kitchen Staff'
    ];
    return $role_names[$role] ?? ucfirst($role);
}

function getRoleIcon($role) {
    $icons = [
        'admin' => 'fa-user-shield',
        'cashier' => 'fa-cash-register',
        'kitchen' => 'fa-utensils'
    ];
    return $icons[$role] ?? 'fa-user';
}

function validatePassword($password) {
    if (strlen($password) < 6) {
        return [
            'valid' => false,
            'message' => 'Password must be at least 6 characters long.'
        ];
    }
    
    return [
        'valid' => true,
        'message' => 'Password is valid.'
    ];
}

function formatLastLogin($last_login) {
    if (empty($last_login)) {
        return 'Never';
    }
    
    $timestamp = strtotime($last_login);
    $now = time();
    $diff = $now - $timestamp;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M d, Y', $timestamp);
    }
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}
function getUserById(PDO $pdo, $user_id) {
    $sql = "SELECT user_id, username, full_name, email, role, is_active, last_login, created_at, updated_at 
            FROM users 
            WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    
    try {
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting user: " . $e->getMessage());
        return false;
    }
}

function getUserByUsername(PDO $pdo, $username) {
    $sql = "SELECT * FROM users WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    
    try {
        $stmt->execute([':username' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting user by username: " . $e->getMessage());
        return false;
    }
}

function getAllUsers(PDO $pdo) {
    $sql = "SELECT user_id, username, full_name, email, role, is_active, last_login, created_at, updated_at 
            FROM users 
            ORDER BY created_at DESC";
    
    try {
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting all users: " . $e->getMessage());
        return [];
    }
}

function updateLastLogin(PDO $pdo, $user_id) {
    $sql = "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    
    try {
        return $stmt->execute([':user_id' => $user_id]);
    } catch (PDOException $e) {
        error_log("Error updating last login: " . $e->getMessage());
        return false;
    }
}