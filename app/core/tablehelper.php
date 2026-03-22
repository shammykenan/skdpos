<?php

// ─────────────────────────────────────────────────────────────────────────────
// TABLE FETCHING
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Get all tables with their current order info if occupied.
 */
function getAllTables(PDO $pdo): array {
    $stmt = $pdo->query("
        SELECT t.*,
               o.order_number,
               o.customer_name,
               o.created_at   AS order_started_at,
               o.total_amount,
               o.status       AS order_status
        FROM `tables` t
        LEFT JOIN orders o ON t.current_order_id = o.order_id
        ORDER BY t.table_name ASC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get a single table by ID.
 */
function getTableById(PDO $pdo, int $table_id): ?array {
    $stmt = $pdo->prepare("SELECT * FROM `tables` WHERE table_id = ?");
    $stmt->execute([$table_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Get only available tables — used for POS dropdown.
 */
function getAvailableTables(PDO $pdo): array {
    $stmt = $pdo->query("
        SELECT table_id, table_name, capacity
        FROM `tables`
        WHERE status = 'available'
        ORDER BY table_name ASC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get table stats: total, available, occupied counts.
 */
function getTableStats(PDO $pdo): array {
    $stmt = $pdo->query("
        SELECT
            COUNT(*)                  AS total,
            SUM(status = 'available') AS available,
            SUM(status = 'occupied')  AS occupied
        FROM `tables`
    ");
    return $stmt->fetch(PDO::FETCH_ASSOC)
        ?: ['total' => 0, 'available' => 0, 'occupied' => 0];
}

// ─────────────────────────────────────────────────────────────────────────────
// TABLE MUTATIONS
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Occupy a table. Uses status = 'available' guard to prevent double-booking.
 */
function occupyTable(PDO $pdo, int $table_id, int $order_id): bool {
    $stmt = $pdo->prepare("
        UPDATE `tables`
        SET    status = 'occupied', current_order_id = ?
        WHERE  table_id = ? AND status = 'available'
    ");
    $stmt->execute([$order_id, $table_id]);
    return $stmt->rowCount() > 0;
}

/**
 * Free a table by ID.
 */
function freeTable(PDO $pdo, int $table_id): bool {
    $stmt = $pdo->prepare("
        UPDATE `tables`
        SET    status = 'available', current_order_id = NULL
        WHERE  table_id = ?
    ");
    $stmt->execute([$table_id]);
    return $stmt->rowCount() > 0;
}

/**
 * Free whatever table is linked to a given order.
 * Used on order cancellation so the table is released immediately.
 */
function freeTableByOrderId(PDO $pdo, int $order_id): bool {
    $stmt = $pdo->prepare("
        UPDATE `tables`
        SET    status = 'available', current_order_id = NULL
        WHERE  current_order_id = ?
    ");
    $stmt->execute([$order_id]);
    return $stmt->rowCount() > 0;
}

/**
 * Create a new table.
 */
function createTable(PDO $pdo, string $table_name, int $capacity = 4): int {
    $stmt = $pdo->prepare("
        INSERT INTO `tables` (table_name, capacity, status)
        VALUES (?, ?, 'available')
    ");
    $stmt->execute([trim($table_name), $capacity]);
    return (int) $pdo->lastInsertId();
}

/**
 * Update a table's name and capacity.
 */
function updateTable(PDO $pdo, int $table_id, string $table_name, int $capacity): bool {
    $stmt = $pdo->prepare("
        UPDATE `tables` SET table_name = ?, capacity = ? WHERE table_id = ?
    ");
    $stmt->execute([trim($table_name), $capacity, $table_id]);
    return $stmt->rowCount() > 0;
}

/**
 * Delete a table — only allowed if it is available.
 */
function deleteTable(PDO $pdo, int $table_id): bool {
    $stmt = $pdo->prepare("
        DELETE FROM `tables` WHERE table_id = ? AND status = 'available'
    ");
    $stmt->execute([$table_id]);
    return $stmt->rowCount() > 0;
}