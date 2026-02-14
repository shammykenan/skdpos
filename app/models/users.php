<?php
require_once __DIR__. '/../../config/db.php';

function createUser(PDO $pdo, $username, $password, $full_name, $email = null, $role = 'cashier', $is_active = 1) {
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, password_hash, full_name, email, role, is_active) 
            VALUES (:username, :password_hash, :full_name, :email, :role, :is_active)";
    $stmt = $pdo->prepare($sql);
    
    try {
        $stmt->execute([
            ':username' => $username,
            ':password_hash' => $password_hash,
            ':full_name' => $full_name,
            ':email' => $email,
            ':role' => $role,
            ':is_active' => $is_active
        ]);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("Error creating user: " . $e->getMessage());
        return false;
    }
}

function updateUser(PDO $pdo, $user_id, $username, $full_name, $email, $role, $is_active) {
    $sql = "UPDATE users 
            SET username = :username, 
                full_name = :full_name, 
                email = :email, 
                role = :role, 
                is_active = :is_active,
                updated_at = CURRENT_TIMESTAMP
            WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    
    try {
        return $stmt->execute([
            ':username' => $username,
            ':full_name' => $full_name,
            ':email' => $email,
            ':role' => $role,
            ':is_active' => $is_active,
            ':user_id' => $user_id
        ]);
    } catch (PDOException $e) {
        error_log("Error updating user: " . $e->getMessage());
        return false;
    }
}

function deleteUser(PDO $pdo, $user_id) {
    $sql = "DELETE FROM users WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    
    try {
        return $stmt->execute([':user_id' => $user_id]);
    } catch (PDOException $e) {
        error_log("Error deleting user: " . $e->getMessage());
        return false;
    }
}
