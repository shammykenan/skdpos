<?php

/**
 * Bump this whenever the schema definitions below change. The synced version is
 * stamped into app_settings so steady-state requests skip the catalog probing
 * entirely (one indexed lookup instead of ~13 INFORMATION_SCHEMA queries).
 */
const DBS_SCHEMA_VERSION = '2026.08.22.7';


function dbs_table_exists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
    ");
    $stmt->execute([$table]);
    return (int) $stmt->fetchColumn() > 0;
}

function dbs_column_exists(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
          AND COLUMN_NAME = ?
    ");
    $stmt->execute([$table, $column]);
    return (int) $stmt->fetchColumn() > 0;
}

function dbs_index_exists(PDO $pdo, string $table, string $index): bool
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
          AND INDEX_NAME = ?
    ");
    $stmt->execute([$table, $index]);
    return (int) $stmt->fetchColumn() > 0;
}

function dbs_fk_exists(PDO $pdo, string $table, string $constraint): bool
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
          AND CONSTRAINT_NAME = ?
          AND CONSTRAINT_TYPE = 'FOREIGN KEY'
    ");
    $stmt->execute([$table, $constraint]);
    return (int) $stmt->fetchColumn() > 0;
}

function dbs_exec(PDO $pdo, string $sql, array &$changes, string $label): void
{
    $pdo->exec($sql);
    $changes[] = $label;
}

function dbs_ensure_table(PDO $pdo, string $table, string $createSql, array &$changes): void
{
    if (!dbs_table_exists($pdo, $table)) {
        dbs_exec($pdo, $createSql, $changes, "Created table `{$table}`");
    }
}

function dbs_ensure_column(PDO $pdo, string $table, string $column, string $definition, array &$changes, ?string $after = null): void
{
    if (!dbs_table_exists($pdo, $table) || dbs_column_exists($pdo, $table, $column)) {
        return;
    }

    $sql = "ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}";
    if ($after !== null && dbs_column_exists($pdo, $table, $after)) {
        $sql .= " AFTER `{$after}`";
    }

    dbs_exec($pdo, $sql, $changes, "Added column `{$table}`.`{$column}`");
}

function dbs_ensure_index(PDO $pdo, string $table, string $index, string $sql, array &$changes): void
{
    if (!dbs_table_exists($pdo, $table) || dbs_index_exists($pdo, $table, $index)) {
        return;
    }

    dbs_exec($pdo, $sql, $changes, "Added index `{$index}` on `{$table}`");
}

function dbs_ensure_fk(PDO $pdo, string $table, string $constraint, string $sql, array &$changes): void
{
    if (!dbs_table_exists($pdo, $table) || dbs_fk_exists($pdo, $table, $constraint)) {
        return;
    }

    dbs_exec($pdo, $sql, $changes, "Added foreign key `{$constraint}` on `{$table}`");
}

function dbs_seed_business_info(PDO $pdo, array &$changes): void
{
    if (!dbs_table_exists($pdo, 'business_info')) {
        return;
    }

    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM business_info WHERE info_id = 1");
        if ((int) $stmt->fetchColumn() > 0) {
            return;
        }
    } catch (PDOException $e) {
        error_log("Error in dbs_seed_business_info: " . $e->getMessage());
        return;
    }

    $pdo->exec("
        INSERT INTO business_info (
            info_id, business_name, address, tin, vat_registered, vat_rate,
            machine_serial_no, ptu_number, accreditation_no,
            min_pwd_discount, min_senior_discount
        ) VALUES (
            1, 'Dine with Din', '', '', 0, 12.00,
            '', '', '',
            20.00, 20.00
        )
    ");
    $changes[] = "Seeded `business_info` row";
}

function dbs_seed_order_item_reasons(PDO $pdo, array &$changes): void
{
    if (!dbs_table_exists($pdo, 'order_item_reasons')) {
        return;
    }

    $defaults = [
        ['cancel_order', 'Customer changed mind', 1],
        ['cancel_order', 'Wrong order encoded', 2],
        ['cancel_order', 'Duplicate order', 3],
        ['cancel_order', 'Other', 99],
        ['void_order', 'Customer refused order', 1],
        ['void_order', 'Order defect / issue', 2],
        ['void_order', 'Admin correction', 3],
        ['void_order', 'Other', 99],
        ['cancel_item', 'Customer changed item', 1],
        ['cancel_item', 'Item encoded by mistake', 2],
        ['cancel_item', 'Out of stock before preparation', 3],
        ['cancel_item', 'Other', 99],
        ['void_item', 'Item defect / quality issue', 1],
        ['void_item', 'Wrong item prepared', 2],
        ['void_item', 'Customer refused item', 3],
        ['void_item', 'Other', 99],
    ];

    $inserted = 0;
    $check = $pdo->prepare("
        SELECT COUNT(*)
        FROM order_item_reasons
        WHERE reason_type = ?
          AND reason_text = ?
    ");
    $insert = $pdo->prepare("
        INSERT INTO order_item_reasons (reason_type, reason_text, sort_order, is_active)
        VALUES (?, ?, ?, 1)
    ");

    foreach ($defaults as [$type, $text, $sort]) {
        $check->execute([$type, $text]);
        if ((int) $check->fetchColumn() > 0) {
            continue;
        }
        $insert->execute([$type, $text, $sort]);
        $inserted++;
    }

    if ($inserted > 0) {
        $changes[] = "Seeded {$inserted} default `order_item_reasons` row(s)";
    }
}

function dbs_sync_orders(PDO $pdo, array &$changes): void
{
    if (!dbs_table_exists($pdo, 'orders')) {
        return;
    }

    dbs_ensure_column($pdo, 'orders', 'or_number', "VARCHAR(20) DEFAULT NULL", $changes, 'order_number');
    dbs_ensure_column($pdo, 'orders', 'discount_amount', "DECIMAL(10,2) NOT NULL DEFAULT 0.00", $changes, 'discount');
    dbs_ensure_column(
        $pdo,
        'orders',
        'discount_type',
        "ENUM('none','senior','pwd','promo','other') NOT NULL DEFAULT 'none'",
        $changes,
        'discount_amount'
    );
    dbs_ensure_column($pdo, 'orders', 'vat_amount', "DECIMAL(10,2) NOT NULL DEFAULT 0.00", $changes, 'or_number');
    dbs_ensure_column($pdo, 'orders', 'vat_exempt_amount', "DECIMAL(10,2) NOT NULL DEFAULT 0.00", $changes, 'vat_amount');
    dbs_ensure_column($pdo, 'orders', 'zero_rated_amount', "DECIMAL(10,2) NOT NULL DEFAULT 0.00", $changes, 'vat_exempt_amount');

    dbs_ensure_index(
        $pdo,
        'orders',
        'uq_orders_or_number',
        "ALTER TABLE `orders` ADD UNIQUE KEY `uq_orders_or_number` (`or_number`)",
        $changes
    );

    if (dbs_column_exists($pdo, 'orders', 'discount_amount')) {
        $stmt = $pdo->exec("
            UPDATE orders
            SET discount_amount = COALESCE(discount, 0.00)
            WHERE COALESCE(discount, 0.00) > 0
              AND COALESCE(discount_amount, 0.00) = 0.00
        ");
        if ((int) $stmt > 0) {
            $changes[] = "Backfilled `orders.discount_amount` from legacy `discount`";
        }
    }

    if (dbs_column_exists($pdo, 'orders', 'discount_type')) {
        $stmt = $pdo->exec("
            UPDATE orders
            SET discount_type = CASE
                WHEN COALESCE(discount_amount, discount, 0.00) > 0 THEN 'other'
                ELSE 'none'
            END
            WHERE discount_type IS NULL
               OR discount_type = ''
        ");
        if ((int) $stmt > 0) {
            $changes[] = "Backfilled `orders.discount_type`";
        }
    }

    if (
        !dbs_column_exists($pdo, 'orders', 'vat_amount') ||
        !dbs_column_exists($pdo, 'orders', 'vat_exempt_amount') ||
        !dbs_column_exists($pdo, 'orders', 'zero_rated_amount')
    ) {
        return;
    }

    try {
        $vatRow = $pdo->query("
            SELECT vat_registered, vat_rate
            FROM business_info
            WHERE info_id = 1
            LIMIT 1
        ")->fetch(PDO::FETCH_ASSOC) ?: ['vat_registered' => 0, 'vat_rate' => 12.00];
    } catch (PDOException $e) {
        error_log("Error in dbs_migrate_vat_columns: " . $e->getMessage());
        $vatRow = ['vat_registered' => 0, 'vat_rate' => 12.00];
    }

    $vatRegistered = (int) ($vatRow['vat_registered'] ?? 0) === 1;
    $vatRate = (float) ($vatRow['vat_rate'] ?? 12.00);
    $divisor = 1 + ($vatRate / 100);

    if ($vatRegistered && $divisor > 0) {
        $stmt = $pdo->prepare("
            UPDATE orders
            SET
                vat_amount = ROUND(total_amount - (total_amount / :divisor), 2),
                vat_exempt_amount = 0.00,
                zero_rated_amount = 0.00
            WHERE total_amount > 0
              AND COALESCE(vat_amount, 0.00) = 0.00
              AND COALESCE(vat_exempt_amount, 0.00) = 0.00
              AND COALESCE(zero_rated_amount, 0.00) = 0.00
              AND COALESCE(discount_type, 'none') IN ('none', 'promo', 'other')
        ");
        $stmt->execute([':divisor' => $divisor]);
        if ($stmt->rowCount() > 0) {
            $changes[] = "Backfilled VAT breakdown for regular legacy orders";
        }

        $stmt = $pdo->exec("
            UPDATE orders
            SET
                vat_amount = 0.00,
                vat_exempt_amount = total_amount,
                zero_rated_amount = 0.00
            WHERE total_amount > 0
              AND COALESCE(vat_amount, 0.00) = 0.00
              AND COALESCE(vat_exempt_amount, 0.00) = 0.00
              AND COALESCE(zero_rated_amount, 0.00) = 0.00
              AND COALESCE(discount_type, 'none') IN ('senior', 'pwd')
        ");
        if ((int) $stmt > 0) {
            $changes[] = "Backfilled VAT-exempt totals for senior/PWD legacy orders";
        }
    } else {
        $stmt = $pdo->exec("
            UPDATE orders
            SET
                vat_amount = 0.00,
                vat_exempt_amount = total_amount,
                zero_rated_amount = 0.00
            WHERE total_amount > 0
              AND COALESCE(vat_amount, 0.00) = 0.00
              AND COALESCE(vat_exempt_amount, 0.00) = 0.00
              AND COALESCE(zero_rated_amount, 0.00) = 0.00
        ");
        if ((int) $stmt > 0) {
            $changes[] = "Backfilled non-VAT legacy orders as VAT-exempt";
        }
    }
}

function dbs_sync_payments(PDO $pdo, array &$changes): void
{
    if (!dbs_table_exists($pdo, 'payments')) {
        return;
    }

    dbs_ensure_column($pdo, 'payments', 'or_number', "VARCHAR(20) DEFAULT NULL", $changes, 'order_id');
    dbs_ensure_index(
        $pdo,
        'payments',
        'idx_payments_or_number',
        "ALTER TABLE `payments` ADD KEY `idx_payments_or_number` (`or_number`)",
        $changes
    );

    if (!dbs_column_exists($pdo, 'payments', 'or_number') || !dbs_column_exists($pdo, 'orders', 'or_number')) {
        return;
    }

    $stmt = $pdo->exec("
        UPDATE payments p
        JOIN orders o ON o.order_id = p.order_id
        SET p.or_number = o.or_number
        WHERE p.or_number IS NULL
          AND o.or_number IS NOT NULL
    ");
    if ((int) $stmt > 0) {
        $changes[] = "Backfilled `payments.or_number` from `orders.or_number`";
    }
}

function dbs_schema_needs_sync(PDO $pdo): bool
{
    $requiredTables = ['business_info', 'or_sequences', 'order_discounts', 'z_readings', 'order_item_reasons', 'app_settings', 'product_addons', 'refunds', 'sales_ledger'];
    foreach ($requiredTables as $table) {
        if (!dbs_table_exists($pdo, $table)) {
            return true;
        }
    }

    $requiredColumns = [
        ['order_items', 'quantity_started'],
        ['order_items', 'quantity_ready'],
        ['order_items', 'quantity_voided'],
        ['orders', 'or_number'],
        ['orders', 'discount_amount'],
        ['orders', 'discount_type'],
        ['orders', 'vat_amount'],
        ['orders', 'vat_exempt_amount'],
        ['orders', 'zero_rated_amount'],
        ['payments', 'or_number'],
    ];

    foreach ($requiredColumns as [$table, $column]) {
        if (!dbs_column_exists($pdo, $table, $column)) {
            return true;
        }
    }

    if (!dbs_index_exists($pdo, 'orders', 'uq_orders_or_number')) {
        return true;
    }

    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM business_info WHERE info_id = 1");
        if ((int) $stmt->fetchColumn() === 0) {
            return true;
        }

        $stmt = $pdo->query("SELECT COUNT(*) FROM order_item_reasons");
        return (int) $stmt->fetchColumn() === 0;
    } catch (PDOException $e) {
        error_log("Error in dbs_is_fresh_install: " . $e->getMessage());
        return true;
    }
}

function dbs_sync_database_schema(PDO $pdo): array
{
    $changes = [];

    dbs_ensure_table($pdo, 'business_info', "
        CREATE TABLE `business_info` (
            `info_id` INT AUTO_INCREMENT PRIMARY KEY,
            `business_name` VARCHAR(255) NOT NULL DEFAULT '',
            `address` TEXT NOT NULL,
            `tin` VARCHAR(20) NOT NULL DEFAULT '',
            `vat_registered` TINYINT(1) NOT NULL DEFAULT 0,
            `vat_rate` DECIMAL(5,2) NOT NULL DEFAULT 12.00,
            `machine_serial_no` VARCHAR(50) DEFAULT NULL,
            `ptu_number` VARCHAR(50) DEFAULT NULL,
            `accreditation_no` VARCHAR(50) DEFAULT NULL,
            `document_label` VARCHAR(40) NOT NULL DEFAULT 'SALES INVOICE',
            `max_promo_discount` DECIMAL(5,2) NOT NULL DEFAULT 20.00,
            `min_pwd_discount` DECIMAL(5,2) NOT NULL DEFAULT 20.00,
            `min_senior_discount` DECIMAL(5,2) NOT NULL DEFAULT 20.00,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ", $changes);

    dbs_ensure_table($pdo, 'or_sequences', "
        CREATE TABLE `or_sequences` (
            `seq_id` INT AUTO_INCREMENT PRIMARY KEY,
            `seq_date` DATE NOT NULL,
            `last_seq` INT NOT NULL DEFAULT 0,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `uq_or_sequences_seq_date` (`seq_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ", $changes);

    dbs_ensure_table($pdo, 'order_item_reasons', "
        CREATE TABLE `order_item_reasons` (
            `reason_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `reason_type` ENUM('cancel_order','void_order','cancel_item','void_item') NOT NULL,
            `reason_text` VARCHAR(255) NOT NULL,
            `sort_order` TINYINT UNSIGNED DEFAULT 0,
            `is_active` TINYINT(1) DEFAULT 1,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ", $changes);

    if (dbs_table_exists($pdo, 'orders') && dbs_table_exists($pdo, 'users')) {
        dbs_ensure_table($pdo, 'order_discounts', "
            CREATE TABLE `order_discounts` (
                `discount_id` INT AUTO_INCREMENT PRIMARY KEY,
                `order_id` INT NOT NULL,
                `discount_type` ENUM('senior','pwd','promo','other') NOT NULL,
                `reference_no` VARCHAR(100) DEFAULT NULL,
                `discount_pct` DECIMAL(5,2) NOT NULL,
                `discount_amt` DECIMAL(10,2) NOT NULL,
                `applied_by` INT NOT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                KEY `idx_order_discounts_order_id` (`order_id`),
                KEY `idx_order_discounts_applied_by` (`applied_by`),
                CONSTRAINT `order_discounts_ibfk_1`
                    FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
                CONSTRAINT `order_discounts_ibfk_2`
                    FOREIGN KEY (`applied_by`) REFERENCES `users` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ", $changes);

        dbs_ensure_table($pdo, 'z_readings', "
            CREATE TABLE `z_readings` (
                `z_id` INT AUTO_INCREMENT PRIMARY KEY,
                `reading_date` DATE NOT NULL,
                `generated_by` INT NOT NULL,
                `generated_at` DATETIME NOT NULL,
                `beginning_or` VARCHAR(20) DEFAULT NULL,
                `ending_or` VARCHAR(20) DEFAULT NULL,
                `gross_sales` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                `vatable_sales` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                `vat_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                `vat_exempt_sales` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                `zero_rated_sales` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                `total_discounts` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                `senior_discounts` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                `pwd_discounts` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                `promo_discounts` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                `total_voids` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                `total_refunds` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                `net_sales` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                `void_count` INT NOT NULL DEFAULT 0,
                `refund_count` INT NOT NULL DEFAULT 0,
                `transaction_count` INT NOT NULL DEFAULT 0,
                `is_locked` TINYINT(1) NOT NULL DEFAULT 0,
                UNIQUE KEY `uq_z_readings_reading_date` (`reading_date`),
                KEY `idx_z_readings_generated_by` (`generated_by`),
                CONSTRAINT `z_readings_ibfk_1`
                    FOREIGN KEY (`generated_by`) REFERENCES `users` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ", $changes);
    }

    dbs_ensure_table($pdo, 'product_addons', "
        CREATE TABLE `product_addons` (
            `product_addon_id` INT AUTO_INCREMENT PRIMARY KEY,
            `item_id` INT NOT NULL,
            `addon_id` INT NOT NULL,
            `is_active` TINYINT(1) DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `uq_item_addon` (`item_id`, `addon_id`),
            CONSTRAINT `fk_product_addons_item`
                FOREIGN KEY (`item_id`) REFERENCES `menu_items` (`item_id`) ON DELETE CASCADE,
            CONSTRAINT `fk_product_addons_addon`
                FOREIGN KEY (`addon_id`) REFERENCES `addons` (`addon_id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ", $changes);

    dbs_ensure_table($pdo, 'refunds', "
        CREATE TABLE `refunds` (
            `refund_id` INT AUTO_INCREMENT PRIMARY KEY,
            `order_id` INT NOT NULL,
            `order_item_id` INT DEFAULT NULL,
            `refund_type` ENUM('void_order','void_item','post_sale') DEFAULT NULL,
            `amount` DECIMAL(10,2) NOT NULL,
            `quantity` INT NOT NULL DEFAULT 1,
            `reason` VARCHAR(255) NOT NULL,
            `refund_method` ENUM('cash') DEFAULT 'cash',
            `note` VARCHAR(255) DEFAULT NULL,
            `processed_by` INT NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY `idx_refunds_order_id` (`order_id`),
            KEY `idx_refunds_order_item_id` (`order_item_id`),
            KEY `idx_refunds_processed_by` (`processed_by`),
            CONSTRAINT `fk_refunds_order`
                FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
            CONSTRAINT `fk_refunds_order_item`
                FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`order_item_id`) ON DELETE SET NULL,
            CONSTRAINT `fk_refunds_processed_by`
                FOREIGN KEY (`processed_by`) REFERENCES `users` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ", $changes);

    dbs_ensure_table($pdo, 'app_settings', "
        CREATE TABLE `app_settings` (
            `setting_id` INT AUTO_INCREMENT PRIMARY KEY,
            `setting_key` VARCHAR(100) NOT NULL,
            `setting_value` TEXT NOT NULL,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `uq_app_settings_key` (`setting_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ", $changes);

    // Printed document type must match what the PTU sample layout was approved as.
    dbs_ensure_column($pdo, 'business_info', 'document_label',
        "VARCHAR(40) NOT NULL DEFAULT 'SALES INVOICE'", $changes, 'accreditation_no');

    // Per-unit kitchen counters. partial_item_action.php and
    // getCommittedIngredientDemand() both derive "not yet prepared" from
    // quantity - started - ready - voided, so these must exist.
    dbs_ensure_column($pdo, 'order_items', 'quantity_started',
        "INT NOT NULL DEFAULT 0", $changes, 'quantity');
    dbs_ensure_column($pdo, 'order_items', 'quantity_ready',
        "INT NOT NULL DEFAULT 0", $changes, 'quantity_started');
    dbs_ensure_column($pdo, 'order_items', 'quantity_voided',
        "INT NOT NULL DEFAULT 0", $changes, 'quantity_ready');

    // Ceiling for manual promo discounts — without one a cashier can ring 99% off.
    dbs_ensure_column($pdo, 'business_info', 'max_promo_discount',
        "DECIMAL(5,2) NOT NULL DEFAULT 20.00", $changes, 'min_senior_discount');

    // ── BIR accumulators ──────────────────────────────────────────────────
    // Append-only sales ledger. The accumulated Grand Total is the running_gt
    // of the newest row; each row also carries a hash of the previous one, so
    // altering or removing any historical sale breaks every hash after it and
    // is detectable by gt_verify(). Rows are never updated or deleted.
    dbs_ensure_table($pdo, 'sales_ledger', "
        CREATE TABLE `sales_ledger` (
            `entry_id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `order_id` INT NOT NULL,
            `or_number` VARCHAR(20) NOT NULL,
            `gross_amount` DECIMAL(12,2) NOT NULL,
            `net_amount` DECIMAL(12,2) NOT NULL,
            `running_gt` DECIMAL(14,2) NOT NULL,
            `recorded_at` DATETIME NOT NULL,
            `prev_hash` CHAR(64) NOT NULL,
            `entry_hash` CHAR(64) NOT NULL,
            UNIQUE KEY `uq_sales_ledger_order` (`order_id`),
            KEY `idx_sales_ledger_or` (`or_number`),
            KEY `idx_sales_ledger_recorded` (`recorded_at`),
            CONSTRAINT `fk_sales_ledger_order`
                FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ", $changes);

    // Machine Identification Number, issued by BIR for a registered POS.
    dbs_ensure_column($pdo, 'business_info', 'min_number',
        "VARCHAR(30) NOT NULL DEFAULT ''", $changes, 'machine_serial_no');

    // Z-counter increments per Z-reading; reset counter per authorised reset.
    dbs_ensure_column($pdo, 'z_readings', 'z_counter',
        "INT NOT NULL DEFAULT 0", $changes, 'reading_date');
    dbs_ensure_column($pdo, 'z_readings', 'reset_counter',
        "INT NOT NULL DEFAULT 0", $changes, 'z_counter');
    dbs_ensure_column($pdo, 'z_readings', 'old_gt',
        "DECIMAL(14,2) NOT NULL DEFAULT 0.00", $changes, 'ending_or');
    dbs_ensure_column($pdo, 'z_readings', 'new_gt',
        "DECIMAL(14,2) NOT NULL DEFAULT 0.00", $changes, 'old_gt');
    // Head of the sales-ledger hash chain at close of day. Printed on the
    // Z-reading, so the paper copy filed with BIR anchors the chain outside
    // the database — a chain rebuilt later cannot match hashes already filed.
    dbs_ensure_column($pdo, 'z_readings', 'ledger_hash',
        "CHAR(64) NOT NULL DEFAULT ''", $changes, 'new_gt');

    dbs_seed_business_info($pdo, $changes);
    dbs_seed_order_item_reasons($pdo, $changes);
    dbs_sync_orders($pdo, $changes);
    dbs_sync_payments($pdo, $changes);

    return [
        'ran'     => !empty($changes),
        'changes' => $changes,
    ];
}

/**
 * Schema version currently stamped in the database, or null if unknown.
 * Returns null rather than throwing when app_settings does not exist yet.
 */
function dbs_stamped_version(PDO $pdo): ?string
{
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM app_settings WHERE setting_key = 'schema_version'");
        $stmt->execute();
        $v = $stmt->fetchColumn();
        return $v === false ? null : (string) $v;
    } catch (PDOException $e) {
        return null; // table missing on a fresh install
    }
}

function dbs_stamp_version(PDO $pdo): void
{
    try {
        $stmt = $pdo->prepare("
            INSERT INTO app_settings (setting_key, setting_value)
            VALUES ('schema_version', :v)
            ON DUPLICATE KEY UPDATE setting_value = :v2, updated_at = CURRENT_TIMESTAMP
        ");
        $stmt->execute([':v' => DBS_SCHEMA_VERSION, ':v2' => DBS_SCHEMA_VERSION]);
    } catch (PDOException $e) {
        error_log('[db schema sync] could not stamp version: ' . $e->getMessage());
    }
}

function dbs_sync_database_schema_if_needed(PDO $pdo): array
{
    // Fast path: one indexed lookup. Once the stamped version matches, the
    // ~13 INFORMATION_SCHEMA probes are skipped entirely on every request.
    if (dbs_stamped_version($pdo) === DBS_SCHEMA_VERSION) {
        return [
            'ran'     => false,
            'changes' => [],
        ];
    }

    // The stamp is missing or stale, so the definitions below may have changed.
    // Run the FULL sync rather than consulting dbs_schema_needs_sync(): that
    // function only knows a fixed checklist, so anything newly added would be
    // skipped and then stamped as done. Every ensure_*() tests for existence
    // first, so this is idempotent — it only acts where something is missing.
    $result = dbs_sync_database_schema($pdo);
    dbs_stamp_version($pdo);

    return $result;
}
