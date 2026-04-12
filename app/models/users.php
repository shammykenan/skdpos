<?php
require_once __DIR__ . '/../../config/db.php';

/**
 * Create a new user.
 */
function createUser(PDO $pdo, $username, $password, $full_name, $email = null, $role = 'cashier', $is_active = 1) {
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, password_hash, full_name, email, role, is_active)
            VALUES (:username, :password_hash, :full_name, :email, :role, :is_active)";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([
            ':username'      => $username,
            ':password_hash' => $password_hash,
            ':full_name'     => $full_name,
            ':email'         => $email,
            ':role'          => $role,
            ':is_active'     => $is_active,
        ]);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("Error creating user: " . $e->getMessage());
        return false;
    }
}

/**
 * Update user profile fields (username, full_name, email, role, is_active).
 * Password is handled separately via updateUserPassword().
 */
function updateUser(PDO $pdo, $user_id, $username, $full_name, $email, $role, $is_active) {
    $sql = "UPDATE users
            SET username   = :username,
                full_name  = :full_name,
                email      = :email,
                role       = :role,
                is_active  = :is_active,
                updated_at = CURRENT_TIMESTAMP
            WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);

    try {
        return $stmt->execute([
            ':username'  => $username,
            ':full_name' => $full_name,
            ':email'     => $email,
            ':role'      => $role,
            ':is_active' => $is_active,
            ':user_id'   => $user_id,
        ]);
    } catch (PDOException $e) {
        error_log("Error updating user: " . $e->getMessage());
        return false;
    }
}

/**
 * Update a user's password hash only.
 */
function updateUserPassword(PDO $pdo, $user_id, $new_password) {
    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    $sql = "UPDATE users
            SET password_hash = :password_hash,
                updated_at    = CURRENT_TIMESTAMP
            WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);

    try {
        return $stmt->execute([
            ':password_hash' => $password_hash,
            ':user_id'       => $user_id,
        ]);
    } catch (PDOException $e) {
        error_log("Error updating user password: " . $e->getMessage());
        return false;
    }
}

/**
 * Toggle a user's active status (activate or deactivate).
 * No hard deletes — preserves audit history.
 *
 * @param int $is_active  1 = activate, 0 = deactivate
 */
function toggleUserStatus(PDO $pdo, $user_id, $is_active) {
    $sql = "UPDATE users
            SET is_active  = :is_active,
                updated_at = CURRENT_TIMESTAMP
            WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);

    try {
        return $stmt->execute([
            ':is_active' => (int) $is_active,
            ':user_id'   => (int) $user_id,
        ]);
    } catch (PDOException $e) {
        error_log("Error toggling user status: " . $e->getMessage());
        return false;
    }
}

/*
 * NOTE: deleteUser() has been intentionally removed.
 * Users are never hard-deleted to preserve audit trails
 * (order history, payment records, void/waste logs).
 * Use toggleUserStatus($pdo, $id, 0) to deactivate instead.
 */