<?php
require_once __DIR__ . '/../../config/db.php';

// =============================================================
// UNIT FUNCTIONS
// =============================================================

/**
 * Fetch all active units as full rows (for view rendering)
 */
function getAllUnits(PDO $pdo): array {
    $stmt = $pdo->query("SELECT unit_id, unit_code, unit_label FROM units WHERE is_active = 1 ORDER BY unit_label ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Fetch units as [unit_id => unit_label] assoc array (for dropdowns)
 */
function getUnitsForDropdown(PDO $pdo): array {
    $stmt = $pdo->query("SELECT unit_id, unit_label FROM units WHERE is_active = 1 ORDER BY unit_label ASC");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $result = [];
    foreach ($rows as $row) {
        $result[$row['unit_id']] = $row['unit_label'];
    }
    return $result;
}

/**
 * Fetch valid unit IDs only (for controller validation)
 */
function getValidUnitIds(PDO $pdo): array {
    $stmt = $pdo->query("SELECT unit_id FROM units WHERE is_active = 1");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Add a new unit
 */
function addUnit(PDO $pdo, string $unit_code, string $unit_label): bool {
    $sql  = "INSERT INTO units (unit_code, unit_label) VALUES (:code, :label)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        ':code'  => strtolower(trim($unit_code)),
        ':label' => trim($unit_label),
    ]);
}

/**
 * Update an existing unit
 */
function updateUnit(PDO $pdo, int $unit_id, string $unit_code, string $unit_label): bool {
    $stmt = $pdo->prepare("UPDATE units SET unit_code = :code, unit_label = :label WHERE unit_id = :id");
    return $stmt->execute([
        ':code'  => strtolower(trim($unit_code)),
        ':label' => trim($unit_label),
        ':id'    => $unit_id,
    ]);
}

/**
 * Delete a unit — only allowed if no ingredients are currently referencing it
 */
function deleteUnit(PDO $pdo, int $unit_id): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM ingredients WHERE unit_id = :unit_id");
    $stmt->execute([':unit_id' => $unit_id]);
    if ($stmt->fetchColumn() > 0) return false;

    $stmt = $pdo->prepare("DELETE FROM units WHERE unit_id = :id");
    return $stmt->execute([':id' => $unit_id]);
}

// =============================================================
// INGREDIENT CRUD
// =============================================================

/**
 * Fetch all ingredients joined with their unit
 */
function getAllIngredients(PDO $pdo): array {
    $sql = "SELECT
                i.ingredient_id,
                i.ingredient_name,
                i.stock_quantity,
                i.unit_id,
                i.low_stock_alert,
                i.last_updated,
                u.unit_code,
                u.unit_label
            FROM ingredients i
            JOIN units u ON i.unit_id = u.unit_id
            ORDER BY i.ingredient_name ASC";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Add a new ingredient
 */
function addIngredient(PDO $pdo, string $ingredient_name, float $stock_quantity, int $unit_id, float $low_stock_alert = 10): bool {
    $sql  = "INSERT INTO ingredients (ingredient_name, stock_quantity, unit_id, low_stock_alert)
             VALUES (:ingredient_name, :stock_quantity, :unit_id, :low_stock_alert)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        ':ingredient_name' => $ingredient_name,
        ':stock_quantity'  => $stock_quantity,
        ':unit_id'         => $unit_id,
        ':low_stock_alert' => $low_stock_alert,
    ]);
}

/**
 * Update an existing ingredient
 */
function updateIngredient(PDO $pdo, int $ingredient_id, string $ingredient_name, float $stock_quantity, int $unit_id, float $low_stock_alert): bool {
    $sql  = "UPDATE ingredients
             SET ingredient_name = :ingredient_name,
                 stock_quantity  = :stock_quantity,
                 unit_id         = :unit_id,
                 low_stock_alert = :low_stock_alert,
                 last_updated    = NOW()
             WHERE ingredient_id = :ingredient_id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        ':ingredient_name' => $ingredient_name,
        ':stock_quantity'  => $stock_quantity,
        ':unit_id'         => $unit_id,
        ':low_stock_alert' => $low_stock_alert,
        ':ingredient_id'   => $ingredient_id,
    ]);
}

/**
 * Delete an ingredient — blocked if used in recipes or add-ons
 */
function deleteIngredient(PDO $pdo, int $ingredient_id): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM recipes WHERE ingredient_id = :ingredient_id");
    $stmt->execute([':ingredient_id' => $ingredient_id]);
    if ($stmt->fetchColumn() > 0) return false;

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM addons WHERE ingredient_id = :ingredient_id");
    $stmt->execute([':ingredient_id' => $ingredient_id]);
    if ($stmt->fetchColumn() > 0) return false;

    $stmt = $pdo->prepare("DELETE FROM ingredients WHERE ingredient_id = :ingredient_id");
    return $stmt->execute([':ingredient_id' => $ingredient_id]);
}