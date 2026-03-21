<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../core/authHelper.php';
require_once __DIR__ . '/../core/tableHelper.php';
requireRole(['admin', 'cashier']);
$baseUrl = $_ENV['BASE_URL'] ?? '';

// ── Free a table (customer has left) — order must be completed first ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['free_table'])) {
    $table_id = (int) ($_POST['table_id'] ?? 0);
    if (!$table_id) {
        $_SESSION['error'] = 'Invalid table.';
        header("Location: $baseUrl/index.php?page=manage_tables");
        exit;
    }

    $tableRow = getTableById($pdo, $table_id);
    if (!$tableRow) {
        $_SESSION['error'] = 'Table not found.';
    } elseif ($tableRow['status'] !== 'occupied') {
        $_SESSION['error'] = 'Table is already available.';
    } else {
        // Block freeing until payment is collected
        $stmt = $pdo->prepare("SELECT status FROM orders WHERE order_id = ?");
        $stmt->execute([$tableRow['current_order_id']]);
        $orderStatus = $stmt->fetchColumn();

        if ($orderStatus !== 'completed') {
            $_SESSION['error'] = 'Cannot free table — collect payment first before freeing.';
        } else {
            $freed = freeTable($pdo, $table_id);
            $_SESSION[$freed ? 'success' : 'error'] = $freed
                ? 'Table is now available.'
                : 'Could not free table. Please try again.';
        }
    }
    header("Location: $baseUrl/index.php?page=manage_tables");
    exit;
}

// ── Add a table (admin only) ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_table'])) {
    if (!hasRole('admin')) {
        $_SESSION['error'] = 'Only admin can add tables.';
        header("Location: $baseUrl/index.php?page=manage_tables");
        exit;
    }
    $table_name = trim($_POST['table_name'] ?? '');
    $capacity   = max(1, (int) ($_POST['capacity'] ?? 4));
    if ($table_name === '') {
        $_SESSION['error'] = 'Table name cannot be empty.';
    } else {
        try {
            createTable($pdo, $table_name, $capacity);
            $_SESSION['success'] = "Table \"$table_name\" added.";
        } catch (Exception $e) {
            $_SESSION['error'] = 'Could not add table: ' . $e->getMessage();
        }
    }
    header("Location: $baseUrl/index.php?page=manage_tables");
    exit;
}

// ── Edit a table (admin only) ─────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_table'])) {
    if (!hasRole('admin')) {
        $_SESSION['error'] = 'Only admin can edit tables.';
        header("Location: $baseUrl/index.php?page=manage_tables");
        exit;
    }
    $table_id   = (int) ($_POST['table_id'] ?? 0);
    $table_name = trim($_POST['table_name'] ?? '');
    $capacity   = max(1, (int) ($_POST['capacity'] ?? 4));
    if ($table_id && $table_name !== '') {
        try {
            updateTable($pdo, $table_id, $table_name, $capacity);
            $_SESSION['success'] = 'Table updated.';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Could not update table: ' . $e->getMessage();
        }
    }
    header("Location: $baseUrl/index.php?page=manage_tables");
    exit;
}

// ── Delete a table (admin only, available tables only) ────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_table'])) {
    if (!hasRole('admin')) {
        $_SESSION['error'] = 'Only admin can delete tables.';
        header("Location: $baseUrl/index.php?page=manage_tables");
        exit;
    }
    $table_id = (int) ($_POST['table_id'] ?? 0);
    if ($table_id) {
        $deleted = deleteTable($pdo, $table_id);
        $_SESSION[$deleted ? 'success' : 'error'] = $deleted
            ? 'Table deleted.'
            : 'Cannot delete an occupied table.';
    }
    header("Location: $baseUrl/index.php?page=manage_tables");
    exit;
}

// ── Fetch data for the view ───────────────────────────────────
$tables = getAllTables($pdo);
$stats  = getTableStats($pdo);

require_once __DIR__ . '/../views/cashier/manage_tables.php';