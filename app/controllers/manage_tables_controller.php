<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../core/authHelper.php';
require_once __DIR__ . '/../core/tablehelper.php';
requireRole(['admin', 'cashier']);
require_once __DIR__ . '/../core/csrfHelper.php';
csrf_require('html');
// Base URL configuration for InfinityFree
// Update this with your actual domain on InfinityFree

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

// ── Add a table (admin only) ──────────────────────────────────────────
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
        // Check for duplicate name explicitly before inserting
        $check = $pdo->prepare("SELECT COUNT(*) FROM `tables` WHERE table_name = ?");
        $check->execute([$table_name]);
        if ($check->fetchColumn() > 0) {
            $_SESSION['error'] = "A table named \"{$table_name}\" already exists. Please use a different name.";
        } else {
            try {
                createTable($pdo, $table_name, $capacity);
                $_SESSION['success'] = "Table \"{$table_name}\" added successfully.";
            } catch (Exception $e) {
                // Catch any remaining DB errors (e.g. race condition duplicate)
                $msg = $e->getMessage();
                if (str_contains($msg, 'Duplicate') || str_contains($msg, '1062')) {
                    $_SESSION['error'] = "A table named \"{$table_name}\" already exists. Please use a different name.";
                } else {
                    $_SESSION['error'] = 'Could not add table: ' . $e->getMessage();
                }
            }
        }
    }
    header("Location: $baseUrl/index.php?page=manage_tables");
    exit;
}

// ── Edit a table (admin only) ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_table'])) {
    if (!hasRole('admin')) {
        $_SESSION['error'] = 'Only admin can edit tables.';
        header("Location: $baseUrl/index.php?page=manage_tables");
        exit;
    }

    $table_id   = (int) ($_POST['table_id'] ?? 0);
    $table_name = trim($_POST['table_name'] ?? '');
    $capacity   = max(1, (int) ($_POST['capacity'] ?? 4));

    if (!$table_id || $table_name === '') {
        $_SESSION['error'] = 'Invalid table data.';
    } else {
        // Check for duplicate name, excluding this table's own current name
        $check = $pdo->prepare("
            SELECT COUNT(*) FROM `tables`
            WHERE table_name = ? AND table_id != ?
        ");
        $check->execute([$table_name, $table_id]);
        if ($check->fetchColumn() > 0) {
            $_SESSION['error'] = "A table named \"{$table_name}\" already exists. Please use a different name.";
        } else {
            try {
                updateTable($pdo, $table_id, $table_name, $capacity);
                $_SESSION['success'] = 'Table updated successfully.';
            } catch (Exception $e) {
                $msg = $e->getMessage();
                if (str_contains($msg, 'Duplicate') || str_contains($msg, '1062')) {
                    $_SESSION['error'] = "A table named \"{$table_name}\" already exists. Please use a different name.";
                } else {
                    $_SESSION['error'] = 'Could not update table: ' . $e->getMessage();
                }
            }
        }
    }
    header("Location: $baseUrl/index.php?page=manage_tables");
    exit;
}

// ── Delete a table (admin only, available tables only) ────────────────
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

// ── Fetch data for the view ───────────────────────────────────────────
$tables = getAllTables($pdo);
$stats  = getTableStats($pdo);

// Keep backward compat with view variable name
$tableStats = $stats;

require_once __DIR__ . '/../views/cashier/manage_tables.php';