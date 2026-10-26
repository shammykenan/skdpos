SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

DROP TABLE IF EXISTS z_readings;
DROP TABLE IF EXISTS order_item_reasons;
DROP TABLE IF EXISTS login_attempts;
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS refunds;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS order_discounts;
DROP TABLE IF EXISTS order_item_addons;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS product_addons;
DROP TABLE IF EXISTS addons;
DROP TABLE IF EXISTS recipes;
DROP TABLE IF EXISTS or_sequences;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS tables;
DROP TABLE IF EXISTS menu_variants;
DROP TABLE IF EXISTS menu_items;
DROP TABLE IF EXISTS ingredients;
DROP TABLE IF EXISTS business_info;
DROP TABLE IF EXISTS units;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    role ENUM('admin','cashier','kitchen') NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE units (
    unit_id INT AUTO_INCREMENT PRIMARY KEY,
    unit_code VARCHAR(20) NOT NULL UNIQUE,
    unit_label VARCHAR(50) NOT NULL,
    unit_icon VARCHAR(50) DEFAULT 'fa-box',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE business_info (
    info_id INT AUTO_INCREMENT PRIMARY KEY,
    business_name VARCHAR(255) NOT NULL DEFAULT '',
    address TEXT NOT NULL,
    tin VARCHAR(20) NOT NULL DEFAULT '',
    vat_registered TINYINT(1) NOT NULL DEFAULT 0,
    vat_rate DECIMAL(5,2) NOT NULL DEFAULT 12.00,
    machine_serial_no VARCHAR(50) DEFAULT NULL,
    ptu_number VARCHAR(50) DEFAULT NULL,
    accreditation_no VARCHAR(50) DEFAULT NULL,
    document_label VARCHAR(40) NOT NULL DEFAULT 'SALES INVOICE',
    min_pwd_discount DECIMAL(5,2) NOT NULL DEFAULT 20.00,
    min_senior_discount DECIMAL(5,2) NOT NULL DEFAULT 20.00,
    max_promo_discount DECIMAL(5,2) NOT NULL DEFAULT 20.00,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE ingredients (
    ingredient_id INT AUTO_INCREMENT PRIMARY KEY,
    ingredient_name VARCHAR(100) NOT NULL UNIQUE,
    stock_quantity DECIMAL(10,2) DEFAULT 0.00,
    unit_id INT NULL,
    low_stock_alert DECIMAL(10,2) DEFAULT 10.00,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_ingredients_unit
        FOREIGN KEY (unit_id) REFERENCES units(unit_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE menu_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(100) NOT NULL,
    description TEXT,
    base_price DECIMAL(10,2) NOT NULL,
    cost_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    category_id INT NOT NULL,
    image_url VARCHAR(255),
    is_available TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_menu_items_category_id (category_id),
    CONSTRAINT fk_menu_items_category
        FOREIGN KEY (category_id) REFERENCES categories(category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE menu_variants (
    variant_id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    variant_name VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    cost_price DECIMAL(10,2) DEFAULT 0.00,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_menu_variants_item_variant (item_id, variant_name),
    CONSTRAINT fk_menu_variants_item
        FOREIGN KEY (item_id) REFERENCES menu_items(item_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE tables (
    table_id INT AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(30) NOT NULL UNIQUE,
    capacity INT DEFAULT 4,
    status ENUM('available','occupied') DEFAULT 'available',
    current_order_id INT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(20) NOT NULL UNIQUE,
    or_number VARCHAR(20) DEFAULT NULL,
    cashier_id INT NOT NULL,
    customer_name VARCHAR(100),
    table_id INT NULL,
    order_type ENUM('dine_in','takeout') DEFAULT 'dine_in',
    order_source ENUM('walk_in','online') DEFAULT 'walk_in',
    subtotal DECIMAL(10,2) NOT NULL,
    discount DECIMAL(10,2) DEFAULT 0.00,
    discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    discount_type ENUM('none','senior','pwd','promo','other') NOT NULL DEFAULT 'none',
    total_amount DECIMAL(10,2) NOT NULL,
    vat_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    vat_exempt_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    zero_rated_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    waste_total DECIMAL(10,2) DEFAULT NULL,
    status ENUM('draft','pending','preparing','ready','completed','cancelled','voided') DEFAULT 'pending',
    void_reason VARCHAR(255),
    cancel_reason VARCHAR(255),
    draft_cart LONGTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY uq_orders_or_number (or_number),
    KEY idx_orders_cashier_id (cashier_id),
    KEY idx_orders_table_id (table_id),
    CONSTRAINT fk_orders_cashier
        FOREIGN KEY (cashier_id) REFERENCES users(user_id),
    CONSTRAINT fk_orders_table
        FOREIGN KEY (table_id) REFERENCES tables(table_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE tables
ADD CONSTRAINT fk_tables_current_order
FOREIGN KEY (current_order_id)
REFERENCES orders(order_id)
ON DELETE SET NULL;

CREATE TABLE or_sequences (
    seq_id INT AUTO_INCREMENT PRIMARY KEY,
    seq_date DATE NOT NULL,
    last_seq INT NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_or_sequences_seq_date (seq_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE recipes (
    recipe_id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    variant_id INT NULL,
    ingredient_id INT NOT NULL,
    quantity_required DECIMAL(10,2) NOT NULL,
    variant_id_key INT GENERATED ALWAYS AS (IFNULL(variant_id, 0)) STORED,
    UNIQUE KEY uq_recipes_item_ingredient_variant (item_id, ingredient_id, variant_id_key),
    CONSTRAINT fk_recipes_item
        FOREIGN KEY (item_id) REFERENCES menu_items(item_id) ON DELETE CASCADE,
    CONSTRAINT fk_recipes_variant
        FOREIGN KEY (variant_id) REFERENCES menu_variants(variant_id) ON DELETE CASCADE,
    CONSTRAINT fk_recipes_ingredient
        FOREIGN KEY (ingredient_id) REFERENCES ingredients(ingredient_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE addons (
    addon_id INT AUTO_INCREMENT PRIMARY KEY,
    addon_name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    ingredient_id INT NOT NULL,
    quantity_used DECIMAL(10,2) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_addons_ingredient
        FOREIGN KEY (ingredient_id) REFERENCES ingredients(ingredient_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE product_addons (
    product_addon_id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    addon_id INT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_item_addon (item_id, addon_id),
    CONSTRAINT fk_product_addons_item
        FOREIGN KEY (item_id) REFERENCES menu_items(item_id) ON DELETE CASCADE,
    CONSTRAINT fk_product_addons_addon
        FOREIGN KEY (addon_id) REFERENCES addons(addon_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    item_id INT NOT NULL,
    variant_id INT NULL,
    item_name_snapshot VARCHAR(100) NOT NULL,
    variant_name_snapshot VARCHAR(50),
    quantity INT NOT NULL,
    quantity_started INT NOT NULL DEFAULT 0,
    quantity_ready INT NOT NULL DEFAULT 0,
    quantity_voided INT NOT NULL DEFAULT 0,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    waste_cost DECIMAL(10,2) DEFAULT NULL,
    item_notes TEXT,
    void_reason VARCHAR(255),
    cancel_reason VARCHAR(255),
    status ENUM('pending','preparing','ready','completed','cancelled','voided') DEFAULT 'pending',
    CONSTRAINT fk_order_items_order
        FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_item
        FOREIGN KEY (item_id) REFERENCES menu_items(item_id),
    CONSTRAINT fk_order_items_variant
        FOREIGN KEY (variant_id) REFERENCES menu_variants(variant_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE order_item_addons (
    order_item_addon_id INT AUTO_INCREMENT PRIMARY KEY,
    order_item_id INT NOT NULL,
    addon_id INT NOT NULL,
    quantity INT DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_order_item_addons_order_item
        FOREIGN KEY (order_item_id) REFERENCES order_items(order_item_id) ON DELETE CASCADE,
    CONSTRAINT fk_order_item_addons_addon
        FOREIGN KEY (addon_id) REFERENCES addons(addon_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE order_discounts (
    discount_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    discount_type ENUM('senior','pwd','promo','other') NOT NULL,
    reference_no VARCHAR(100) DEFAULT NULL,
    discount_pct DECIMAL(5,2) NOT NULL,
    discount_amt DECIMAL(10,2) NOT NULL,
    applied_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_order_discounts_order_id (order_id),
    KEY idx_order_discounts_applied_by (applied_by),
    CONSTRAINT fk_order_discounts_order
        FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    CONSTRAINT fk_order_discounts_applied_by
        FOREIGN KEY (applied_by) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    or_number VARCHAR(20) DEFAULT NULL,
    payment_method ENUM('cash') NOT NULL,
    amount_paid DECIMAL(10,2) NOT NULL,
    change_amount DECIMAL(10,2) DEFAULT 0.00,
    payment_status ENUM('paid','pending') DEFAULT 'paid',
    transaction_ref VARCHAR(100) DEFAULT NULL,
    cashier_id INT NOT NULL,
    processed_by INT DEFAULT NULL,
    paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_payments_order_id (order_id),
    KEY idx_payments_or_number (or_number),
    KEY idx_payments_cashier_id (cashier_id),
    KEY idx_payments_processed_by (processed_by),
    CONSTRAINT fk_payments_order
        FOREIGN KEY (order_id) REFERENCES orders(order_id),
    CONSTRAINT fk_payments_cashier
        FOREIGN KEY (cashier_id) REFERENCES users(user_id),
    CONSTRAINT fk_payments_processed_by
        FOREIGN KEY (processed_by) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE refunds (
    refund_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    order_item_id INT DEFAULT NULL,
    refund_type ENUM('void_order','void_item','post_sale') DEFAULT NULL,
    amount DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    reason VARCHAR(255) NOT NULL,
    refund_method ENUM('cash') DEFAULT 'cash',
    note VARCHAR(255) DEFAULT NULL,
    processed_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_refunds_order_id (order_id),
    KEY idx_refunds_order_item_id (order_item_id),
    KEY idx_refunds_processed_by (processed_by),
    CONSTRAINT fk_refunds_order
        FOREIGN KEY (order_id) REFERENCES orders(order_id),
    CONSTRAINT fk_refunds_order_item
        FOREIGN KEY (order_item_id) REFERENCES order_items(order_item_id) ON DELETE SET NULL,
    CONSTRAINT fk_refunds_processed_by
        FOREIGN KEY (processed_by) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE activity_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_activity_logs_user
        FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE password_resets (
    reset_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_password_resets_user
        FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE login_attempts (
    attempt_id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    username VARCHAR(100),
    success TINYINT(1) DEFAULT 0,
    attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE order_item_reasons (
    reason_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reason_type ENUM('cancel_order','void_order','cancel_item','void_item') NOT NULL,
    reason_text VARCHAR(255) NOT NULL,
    sort_order TINYINT UNSIGNED DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE z_readings (
    z_id INT AUTO_INCREMENT PRIMARY KEY,
    reading_date DATE NOT NULL,
    generated_by INT NOT NULL,
    generated_at DATETIME NOT NULL,
    beginning_or VARCHAR(20) DEFAULT NULL,
    ending_or VARCHAR(20) DEFAULT NULL,
    gross_sales DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    vatable_sales DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    vat_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    vat_exempt_sales DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    zero_rated_sales DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total_discounts DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    senior_discounts DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    pwd_discounts DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    promo_discounts DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total_voids DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total_refunds DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    net_sales DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    void_count INT NOT NULL DEFAULT 0,
    refund_count INT NOT NULL DEFAULT 0,
    transaction_count INT NOT NULL DEFAULT 0,
    is_locked TINYINT(1) NOT NULL DEFAULT 0,
    UNIQUE KEY uq_z_readings_reading_date (reading_date),
    KEY idx_z_readings_generated_by (generated_by),
    CONSTRAINT fk_z_readings_generated_by
        FOREIGN KEY (generated_by) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO business_info (
    info_id,
    business_name,
    address,
    tin,
    vat_registered,
    vat_rate,
    machine_serial_no,
    ptu_number,
    accreditation_no,
    min_pwd_discount,
    min_senior_discount
) VALUES (
    1,
    'Dine with Din',
    '',
    '',
    0,
    12.00,
    '',
    '',
    '',
    20.00,
    20.00
);

INSERT INTO order_item_reasons (reason_type, reason_text, sort_order, is_active) VALUES
('cancel_order', 'Customer changed mind', 1, 1),
('cancel_order', 'Wrong order encoded', 2, 1),
('cancel_order', 'Duplicate order', 3, 1),
('cancel_order', 'Other', 99, 1),
('void_order', 'Customer refused order', 1, 1),
('void_order', 'Order defect / issue', 2, 1),
('void_order', 'Admin correction', 3, 1),
('void_order', 'Other', 99, 1),
('cancel_item', 'Customer changed item', 1, 1),
('cancel_item', 'Item encoded by mistake', 2, 1),
('cancel_item', 'Out of stock before preparation', 3, 1),
('cancel_item', 'Other', 99, 1),
('void_item', 'Item defect / quality issue', 1, 1),
('void_item', 'Wrong item prepared', 2, 1),
('void_item', 'Customer refused item', 3, 1),
('void_item', 'Other', 99, 1);

SET FOREIGN_KEY_CHECKS = 1;
