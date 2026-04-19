<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../models/users.php';      // Model functions
require_once __DIR__ . '/../core/usersHelper.php';  // Helper functions (usernameExists, emailExists)
$baseUrl = $_ENV['BASE_URL'] ?? '';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied. Admin privileges required.';
    header('Location: ' . $baseUrl . '/index.php?page=login');
    exit;
}

// Initialize variables
$users        = [];
$user_to_edit = null;
$success      = $_SESSION['success'] ?? '';
$error        = $_SESSION['error']   ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// Fetch all users
try {
    $stmt = $pdo->query("
        SELECT user_id, username, full_name, email, role, is_active, created_at
        FROM users
        ORDER BY created_at DESC
    ");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Failed to fetch users: ' . $e->getMessage();
}

// Calculate statistics
$totalUsers   = count($users);
$activeUsers  = count(array_filter($users, fn($u) => $u['is_active'] == 1));
$cashierUsers = count(array_filter($users, fn($u) => $u['role'] === 'cashier'));
$kitchenUsers = count(array_filter($users, fn($u) => $u['role'] === 'kitchen'));

// ── ADD USER ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username  = trim($_POST['username']  ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email']     ?? '');
    $password  = $_POST['password']       ?? '';
    $role      = $_POST['role']           ?? 'cashier';

    if (empty($username) || empty($full_name) || empty($password)) {
        $_SESSION['error'] = 'Username, full name, and password are required.';
        header('Location: ' . $baseUrl . '/index.php?page=manage_users');
        exit;
    }

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Please enter a valid email address.';
        header('Location: ' . $baseUrl . '/index.php?page=manage_users');
        exit;
    }

    if (strlen($password) < 6) {
        $_SESSION['error'] = 'Password must be at least 6 characters long.';
        header('Location: ' . $baseUrl . '/index.php?page=manage_users');
        exit;
    }

    if (usernameExists($pdo, $username)) {
        $_SESSION['error'] = 'Username already exists.';
        header('Location: ' . $baseUrl . '/index.php?page=manage_users');
        exit;
    }

    if (!empty($email) && emailExists($pdo, $email)) {
        $_SESSION['error'] = 'Email address already exists.';
        header('Location: ' . $baseUrl . '/index.php?page=manage_users');
        exit;
    }

    $userId = createUser($pdo, $username, $password, $full_name, !empty($email) ? $email : null, $role, 1);
    if ($userId) {
        $_SESSION['success'] = 'User "' . htmlspecialchars($full_name) . '" added successfully.';
    } else {
        $_SESSION['error'] = 'Failed to add user.';
    }

    header('Location: ' . $baseUrl . '/index.php?page=manage_users');
    exit;
}

// ── UPDATE USER ───────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $user_id   = (int) $_POST['user_id'];
    $username  = trim($_POST['username']  ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email']     ?? '');
    $role      = $_POST['role']           ?? 'cashier';
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (empty($username) || empty($full_name)) {
        $_SESSION['error'] = 'Username and full name are required.';
        header('Location: ' . $baseUrl . '/index.php?page=manage_users&edit=' . $user_id);
        exit;
    }

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Please enter a valid email address.';
        header('Location: ' . $baseUrl . '/index.php?page=manage_users&edit=' . $user_id);
        exit;
    }

    if (usernameExists($pdo, $username, $user_id)) {
        $_SESSION['error'] = 'Username already exists.';
        header('Location: ' . $baseUrl . '/index.php?page=manage_users&edit=' . $user_id);
        exit;
    }

    if (!empty($email) && emailExists($pdo, $email, $user_id)) {
        $_SESSION['error'] = 'Email address already exists.';
        header('Location: ' . $baseUrl . '/index.php?page=manage_users&edit=' . $user_id);
        exit;
    }

    // Handle optional password change
    $password = trim($_POST['password'] ?? '');
    if (!empty($password)) {
        if (strlen($password) < 6) {
            $_SESSION['error'] = 'New password must be at least 6 characters.';
            header('Location: ' . $baseUrl . '/index.php?page=manage_users&edit=' . $user_id);
            exit;
        }
        updateUserPassword($pdo, $user_id, $password);
    }

    if (updateUser($pdo, $user_id, $username, $full_name, !empty($email) ? $email : null, $role, $is_active)) {
        $_SESSION['success'] = 'User "' . htmlspecialchars($full_name) . '" updated successfully.';
    } else {
        $_SESSION['error'] = 'Failed to update user.';
    }

    header('Location: ' . $baseUrl . '/index.php?page=manage_users');
    exit;
}

// ── TOGGLE USER STATUS (activate / deactivate) ────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_user'])) {

    $user_id = (int) $_POST['user_id'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $currentUserId = (int) ($_SESSION['user_id'] ?? 0);

    // ── SAFETY: cannot change own account status ───────────────────────────
    if ($user_id === $currentUserId) {
        $_SESSION['error'] = 'You cannot change the status of your own account.';
        header('Location: ' . $baseUrl . '/index.php?page=manage_users');
        exit;
    }

    // ── UPDATE USER STATUS ────────────────────────────────────────────────
    $stmt = $pdo->prepare("
        UPDATE users 
        SET is_active = :is_active 
        WHERE user_id = :user_id
    ");

    $result = $stmt->execute([
        ':is_active' => $is_active,
        ':user_id'   => $user_id
    ]);

    // ── RESPONSE ───────────────────────────────────────────────────────────
    if ($result) {
        $action = $is_active ? 'activated' : 'deactivated';
        $_SESSION['success'] = "User has been {$action} successfully.";
    } else {
        $_SESSION['error'] = 'Failed to update user status.';
    }

    header('Location: ' . $baseUrl . '/index.php?page=manage_users');
    exit;
}

// ── HANDLE EDIT REQUEST ───────────────────────────────────────────────────
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $edit_id]);
        $user_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user_to_edit) {
            $_SESSION['error'] = 'User not found.';
            header('Location: ' . $baseUrl . '/index.php?page=manage_users');
            exit;
        }
    } catch (PDOException $e) {
        $error = 'Failed to fetch user: ' . $e->getMessage();
    }
}

// Include the view
require_once __DIR__ . '/../views/admin/manage_users.php';