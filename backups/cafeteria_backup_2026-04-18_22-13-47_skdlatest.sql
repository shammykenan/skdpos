-- ============================================================
-- Dine with Din Cafeteria POS — Database Backup
-- Generated : 2026-04-18 22:13:47 (Asia/Manila)
-- Host      : LAPTOP-PL0A30MQ
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET NAMES utf8mb4;

-- ----------------------------------------------------------
-- Table: `business_info`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `business_info`;
CREATE TABLE `business_info` (
  `info_id` int(11) NOT NULL AUTO_INCREMENT,
  `business_name` varchar(255) NOT NULL DEFAULT '',
  `address` text NOT NULL,
  `tin` varchar(20) NOT NULL DEFAULT '' COMMENT 'Format: XXX-XXX-XXX-XXXX',
  `vat_registered` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = VAT-registered, 0 = Non-VAT',
  `vat_rate` decimal(5,2) NOT NULL DEFAULT 12.00,
  `machine_serial_no` varchar(50) DEFAULT NULL,
  `ptu_number` varchar(50) DEFAULT NULL,
  `accreditation_no` varchar(50) DEFAULT NULL,
  `min_pwd_discount` decimal(5,2) NOT NULL DEFAULT 20.00,
  `min_senior_discount` decimal(5,2) NOT NULL DEFAULT 20.00,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`info_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `business_info` (`info_id`, `business_name`, `address`, `tin`, `vat_registered`, `vat_rate`, `machine_serial_no`, `ptu_number`, `accreditation_no`, `min_pwd_discount`, `min_senior_discount`, `created_at`, `updated_at`) VALUES
  ('1', 'Dine with Din', 'Karuhatan, Valenzuela City', '1234-5678-91011', '1', '12.00', '', '', '', '20.00', '20.00', '2026-04-18 12:29:54', '2026-04-18 20:02:39');

-- ----------------------------------------------------------
-- Table: `categories`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `category_name` (`category_name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categories` (`category_id`, `category_name`, `created_at`) VALUES
  ('1', 'Coffee', '2026-04-18 12:20:05');

-- ----------------------------------------------------------
-- Table: `login_attempts`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE `login_attempts` (
  `attempt_id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `success` tinyint(1) DEFAULT 0,
  `attempt_time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`attempt_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------
-- Table: `or_sequences`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `or_sequences`;
CREATE TABLE `or_sequences` (
  `seq_id` int(11) NOT NULL AUTO_INCREMENT,
  `seq_date` date NOT NULL,
  `last_seq` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`seq_id`),
  UNIQUE KEY `seq_date` (`seq_date`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `or_sequences` (`seq_id`, `seq_date`, `last_seq`, `created_at`) VALUES
  ('1', '2026-04-18', '15', '2026-04-18 14:04:05');

-- ----------------------------------------------------------
-- Table: `order_item_reasons`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `order_item_reasons`;
CREATE TABLE `order_item_reasons` (
  `reason_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reason_type` enum('cancel_order','void_order','cancel_item','void_item') NOT NULL,
  `reason_text` varchar(255) NOT NULL,
  `sort_order` tinyint(3) unsigned DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`reason_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `order_item_reasons` (`reason_id`, `reason_type`, `reason_text`, `sort_order`, `is_active`, `created_at`) VALUES
  ('1', 'cancel_order', 'Customer changed mind', '1', '1', '2026-04-18 22:08:50'),
  ('2', 'cancel_order', 'Wrong order encoded', '2', '1', '2026-04-18 22:08:50'),
  ('3', 'cancel_order', 'Duplicate order', '3', '1', '2026-04-18 22:08:50'),
  ('4', 'cancel_order', 'Other', '99', '1', '2026-04-18 22:08:50'),
  ('5', 'void_order', 'Customer refused order', '1', '1', '2026-04-18 22:08:50'),
  ('6', 'void_order', 'Order defect / issue', '2', '1', '2026-04-18 22:08:50'),
  ('7', 'void_order', 'Admin correction', '3', '1', '2026-04-18 22:08:50'),
  ('8', 'void_order', 'Other', '99', '1', '2026-04-18 22:08:50'),
  ('9', 'cancel_item', 'Customer changed item', '1', '1', '2026-04-18 22:08:50'),
  ('10', 'cancel_item', 'Item encoded by mistake', '2', '1', '2026-04-18 22:08:50'),
  ('11', 'cancel_item', 'Out of stock before preparation', '3', '1', '2026-04-18 22:08:50'),
  ('12', 'cancel_item', 'Other', '99', '1', '2026-04-18 22:08:50'),
  ('13', 'void_item', 'Item defect / quality issue', '1', '1', '2026-04-18 22:08:50'),
  ('14', 'void_item', 'Wrong item prepared', '2', '1', '2026-04-18 22:08:50'),
  ('15', 'void_item', 'Customer refused item', '3', '1', '2026-04-18 22:08:50'),
  ('16', 'void_item', 'Other', '99', '1', '2026-04-18 22:08:50');

-- ----------------------------------------------------------
-- Table: `units`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `units`;
CREATE TABLE `units` (
  `unit_id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_code` varchar(20) NOT NULL,
  `unit_label` varchar(50) NOT NULL,
  `unit_icon` varchar(50) DEFAULT 'fa-box',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`unit_id`),
  UNIQUE KEY `unit_code` (`unit_code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `units` (`unit_id`, `unit_code`, `unit_label`, `unit_icon`, `is_active`, `created_at`) VALUES
  ('1', 'ml', 'Milliliters', 'fa-box', '1', '2026-04-18 12:20:05'),
  ('2', 'g', 'Grams', 'fa-box', '1', '2026-04-18 12:20:05'),
  ('3', 'pcs', 'Pieces', 'fa-box', '1', '2026-04-18 12:20:05');

-- ----------------------------------------------------------
-- Table: `users`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','cashier','kitchen') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`user_id`, `username`, `password_hash`, `full_name`, `email`, `role`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
  ('1', 'admin', '$2y$10$QSBtVpo75F1WJaCVsuWbxu1qsKHav9Gaee2rpqN6ROl3iEdGpOCvC', 'Admin123', 'dinshammykenan012@gmail.com', 'admin', '1', '2026-04-18 12:20:35', '2026-04-18 12:18:31', '2026-04-18 12:20:35');

-- ----------------------------------------------------------
-- Table: `menu_items`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `menu_items`;
CREATE TABLE `menu_items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `cost_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `category_id` int(11) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`item_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `menu_items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `menu_items` (`item_id`, `item_name`, `description`, `base_price`, `cost_price`, `category_id`, `image_url`, `is_available`, `created_at`) VALUES
  ('1', 'Americano', 'Espresso diluted with hot water', '3.00', '1.20', '1', NULL, '1', '2026-04-18 12:20:05'),
  ('2', 'Cappuccino', 'Espresso with steamed milk and foam', '4.00', '1.80', '1', NULL, '1', '2026-04-18 12:20:05'),
  ('3', 'Mocha', 'Espresso with chocolate and milk', '4.50', '2.10', '1', NULL, '1', '2026-04-18 12:20:05');

-- ----------------------------------------------------------
-- Table: `ingredients`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `ingredients`;
CREATE TABLE `ingredients` (
  `ingredient_id` int(11) NOT NULL AUTO_INCREMENT,
  `ingredient_name` varchar(100) NOT NULL,
  `stock_quantity` decimal(10,2) DEFAULT 0.00,
  `unit_id` int(11) DEFAULT NULL,
  `low_stock_alert` decimal(10,2) DEFAULT 10.00,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`ingredient_id`),
  UNIQUE KEY `ingredient_name` (`ingredient_name`),
  KEY `unit_id` (`unit_id`),
  CONSTRAINT `ingredients_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`unit_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `ingredients` (`ingredient_id`, `ingredient_name`, `stock_quantity`, `unit_id`, `low_stock_alert`, `last_updated`) VALUES
  ('1', 'Espresso Beans', '5000.00', '2', '500.00', '2026-04-18 12:20:05'),
  ('2', 'Milk', '10000.00', '1', '1000.00', '2026-04-18 12:20:05'),
  ('3', 'Sugar', '3000.00', '2', '500.00', '2026-04-18 12:20:05'),
  ('4', 'Chocolate Syrup', '2000.00', '1', '300.00', '2026-04-18 12:20:05'),
  ('5', 'Caramel Syrup', '2000.00', '1', '300.00', '2026-04-18 12:20:05'),
  ('6', 'Whipped Cream', '1000.00', '1', '200.00', '2026-04-18 12:20:05');

-- ----------------------------------------------------------
-- Table: `activity_logs`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `activity_logs` (`log_id`, `user_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
  ('1', '1', 'forgot_password_request', 'Password reset requested for email: dinshammykenan012@gmail.com', '192.168.100.11', '2026-04-18 12:19:09'),
  ('2', '1', 'password_reset_success', 'Password successfully reset via email link.', '192.168.100.11', '2026-04-18 12:20:24');

-- ----------------------------------------------------------
-- Table: `password_resets`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `reset_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`reset_id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `password_resets` (`reset_id`, `user_id`, `token`, `expires_at`, `used`, `ip_address`, `created_at`) VALUES
  ('1', '1', 'cebe6fc67a99c8576fdaaea0e9ad89f7e5ebeccb8cb6e531493e6dfd70f10958', '2026-04-18 13:19:08', '1', '192.168.100.11', '2026-04-18 12:19:08');

-- ----------------------------------------------------------
-- Table: `z_readings`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `z_readings`;
CREATE TABLE `z_readings` (
  `z_id` int(11) NOT NULL AUTO_INCREMENT,
  `reading_date` date NOT NULL,
  `generated_by` int(11) NOT NULL COMMENT 'user_id',
  `generated_at` datetime NOT NULL,
  `beginning_or` varchar(20) DEFAULT NULL,
  `ending_or` varchar(20) DEFAULT NULL,
  `gross_sales` decimal(12,2) NOT NULL DEFAULT 0.00,
  `vatable_sales` decimal(12,2) NOT NULL DEFAULT 0.00,
  `vat_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `vat_exempt_sales` decimal(12,2) NOT NULL DEFAULT 0.00,
  `zero_rated_sales` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_discounts` decimal(12,2) NOT NULL DEFAULT 0.00,
  `senior_discounts` decimal(12,2) NOT NULL DEFAULT 0.00,
  `pwd_discounts` decimal(12,2) NOT NULL DEFAULT 0.00,
  `promo_discounts` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_voids` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_refunds` decimal(12,2) NOT NULL DEFAULT 0.00,
  `net_sales` decimal(12,2) NOT NULL DEFAULT 0.00,
  `void_count` int(11) NOT NULL DEFAULT 0,
  `refund_count` int(11) NOT NULL DEFAULT 0,
  `transaction_count` int(11) NOT NULL DEFAULT 0,
  `is_locked` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Locked after generation',
  PRIMARY KEY (`z_id`),
  UNIQUE KEY `reading_date` (`reading_date`),
  KEY `generated_by` (`generated_by`),
  CONSTRAINT `z_readings_ibfk_1` FOREIGN KEY (`generated_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------
-- Table: `menu_variants`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `menu_variants`;
CREATE TABLE `menu_variants` (
  `variant_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `variant_name` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `cost_price` decimal(10,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`variant_id`),
  UNIQUE KEY `item_id` (`item_id`,`variant_name`),
  CONSTRAINT `menu_variants_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `menu_items` (`item_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------
-- Table: `addons`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `addons`;
CREATE TABLE `addons` (
  `addon_id` int(11) NOT NULL AUTO_INCREMENT,
  `addon_name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `ingredient_id` int(11) NOT NULL,
  `quantity_used` decimal(10,2) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`addon_id`),
  KEY `ingredient_id` (`ingredient_id`),
  CONSTRAINT `addons_ibfk_1` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`ingredient_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `addons` (`addon_id`, `addon_name`, `price`, `ingredient_id`, `quantity_used`, `is_active`, `last_updated`) VALUES
  ('1', 'Extra Shot', '1.50', '1', '18.00', '1', '2026-04-18 12:20:05'),
  ('2', 'Caramel Syrup', '0.75', '5', '15.00', '1', '2026-04-18 12:20:05'),
  ('3', 'Whipped Cream', '0.75', '6', '20.00', '1', '2026-04-18 12:20:05');

-- ----------------------------------------------------------
-- Table: `recipes`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `recipes`;
CREATE TABLE `recipes` (
  `recipe_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `ingredient_id` int(11) NOT NULL,
  `quantity_required` decimal(10,2) NOT NULL,
  `variant_id_key` int(11) GENERATED ALWAYS AS (ifnull(`variant_id`,0)) STORED,
  PRIMARY KEY (`recipe_id`),
  UNIQUE KEY `item_id` (`item_id`,`ingredient_id`,`variant_id_key`),
  KEY `variant_id` (`variant_id`),
  KEY `ingredient_id` (`ingredient_id`),
  CONSTRAINT `recipes_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `menu_items` (`item_id`) ON DELETE CASCADE,
  CONSTRAINT `recipes_ibfk_2` FOREIGN KEY (`variant_id`) REFERENCES `menu_variants` (`variant_id`) ON DELETE CASCADE,
  CONSTRAINT `recipes_ibfk_3` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`ingredient_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `recipes` (`recipe_id`, `item_id`, `variant_id`, `ingredient_id`, `quantity_required`, `variant_id_key`) VALUES
  ('1', '1', NULL, '1', '18.00', '0'),
  ('2', '1', NULL, '2', '0.00', '0'),
  ('3', '2', NULL, '1', '18.00', '0'),
  ('4', '2', NULL, '2', '150.00', '0'),
  ('5', '3', NULL, '1', '18.00', '0'),
  ('6', '3', NULL, '2', '150.00', '0'),
  ('7', '3', NULL, '4', '20.00', '0');

-- ----------------------------------------------------------
-- Table: `product_addons`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `product_addons`;
CREATE TABLE `product_addons` (
  `product_addon_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `addon_id` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`product_addon_id`),
  UNIQUE KEY `uq_item_addon` (`item_id`,`addon_id`),
  KEY `addon_id` (`addon_id`),
  CONSTRAINT `product_addons_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `menu_items` (`item_id`) ON DELETE CASCADE,
  CONSTRAINT `product_addons_ibfk_2` FOREIGN KEY (`addon_id`) REFERENCES `addons` (`addon_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `product_addons` (`product_addon_id`, `item_id`, `addon_id`, `is_active`, `created_at`) VALUES
  ('2', '1', '1', '1', '2026-04-18 12:24:38'),
  ('3', '2', '1', '1', '2026-04-18 12:24:38'),
  ('4', '2', '2', '1', '2026-04-18 12:24:38'),
  ('5', '3', '3', '1', '2026-04-18 12:24:38');

-- ----------------------------------------------------------
-- Table: `order_discounts`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `order_discounts`;
CREATE TABLE `order_discounts` (
  `discount_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `discount_type` enum('senior','pwd','promo','other') NOT NULL,
  `reference_no` varchar(100) DEFAULT NULL COMMENT 'Senior / PWD ID number',
  `discount_pct` decimal(5,2) NOT NULL,
  `discount_amt` decimal(10,2) NOT NULL,
  `applied_by` int(11) NOT NULL COMMENT 'user_id',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`discount_id`),
  KEY `order_id` (`order_id`),
  KEY `applied_by` (`applied_by`),
  CONSTRAINT `order_discounts_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `order_discounts_ibfk_2` FOREIGN KEY (`applied_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `order_discounts` (`discount_id`, `order_id`, `discount_type`, `reference_no`, `discount_pct`, `discount_amt`, `applied_by`, `created_at`) VALUES
  ('1', '16', 'promo', NULL, '50.00', '1.79', '1', '2026-04-18 15:31:09'),
  ('2', '18', 'senior', '123456789', '20.00', '0.54', '1', '2026-04-18 15:41:59'),
  ('3', '20', 'promo', NULL, '50.00', '1.34', '1', '2026-04-18 15:46:52'),
  ('4', '22', 'senior', '9999999', '20.00', '0.71', '1', '2026-04-18 15:49:05'),
  ('5', '24', 'senior', '12345678', '20.00', '0.54', '1', '2026-04-18 18:33:02'),
  ('6', '32', 'promo', NULL, '50.00', '2.00', '1', '2026-04-18 20:26:42');

-- ----------------------------------------------------------
-- Table: `order_item_addons`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `order_item_addons`;
CREATE TABLE `order_item_addons` (
  `order_item_addon_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_item_id` int(11) NOT NULL,
  `addon_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`order_item_addon_id`),
  KEY `order_item_id` (`order_item_id`),
  KEY `addon_id` (`addon_id`),
  CONSTRAINT `order_item_addons_ibfk_1` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`order_item_id`) ON DELETE CASCADE,
  CONSTRAINT `order_item_addons_ibfk_2` FOREIGN KEY (`addon_id`) REFERENCES `addons` (`addon_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------
-- Table: `order_items`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `item_name_snapshot` varchar(100) NOT NULL,
  `variant_name_snapshot` varchar(50) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `quantity_started` int(11) NOT NULL DEFAULT 0,
  `quantity_ready` int(11) NOT NULL DEFAULT 0,
  `quantity_voided` int(11) NOT NULL DEFAULT 0,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `waste_cost` decimal(10,2) DEFAULT NULL,
  `item_notes` text DEFAULT NULL,
  `void_reason` varchar(255) DEFAULT NULL,
  `cancel_reason` varchar(255) DEFAULT NULL,
  `status` enum('pending','preparing','ready','completed','cancelled','voided') DEFAULT 'pending',
  PRIMARY KEY (`order_item_id`),
  KEY `order_id` (`order_id`),
  KEY `item_id` (`item_id`),
  KEY `variant_id` (`variant_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `menu_items` (`item_id`),
  CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`variant_id`) REFERENCES `menu_variants` (`variant_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------
-- Table: `orders`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_number` varchar(20) NOT NULL,
  `or_number` varchar(20) DEFAULT NULL,
  `vat_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `vat_exempt_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `zero_rated_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cashier_id` int(11) NOT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `table_id` int(11) DEFAULT NULL,
  `order_type` enum('dine_in','takeout') DEFAULT 'dine_in',
  `order_source` enum('walk_in','online') DEFAULT 'walk_in',
  `subtotal` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_type` enum('none','senior','pwd','promo','other') NOT NULL DEFAULT 'none',
  `total_amount` decimal(10,2) NOT NULL,
  `waste_total` decimal(10,2) DEFAULT NULL,
  `status` enum('draft','pending','preparing','ready','completed','cancelled','voided') DEFAULT 'pending',
  `void_reason` varchar(255) DEFAULT NULL,
  `cancel_reason` varchar(255) DEFAULT NULL,
  `draft_cart` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  UNIQUE KEY `order_number` (`order_number`),
  UNIQUE KEY `uq_orders_or_number` (`or_number`),
  KEY `cashier_id` (`cashier_id`),
  KEY `table_id` (`table_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`table_id`) REFERENCES `tables` (`table_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------
-- Table: `payments`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `or_number` varchar(20) DEFAULT NULL,
  `payment_method` enum('cash') NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `change_amount` decimal(10,2) DEFAULT 0.00,
  `payment_status` enum('paid','pending') DEFAULT 'paid',
  `transaction_ref` varchar(100) DEFAULT NULL,
  `cashier_id` int(11) NOT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `paid_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`payment_id`),
  KEY `order_id` (`order_id`),
  KEY `cashier_id` (`cashier_id`),
  KEY `processed_by` (`processed_by`),
  KEY `idx_payments_or_number` (`or_number`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`processed_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------
-- Table: `refunds`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `refunds`;
CREATE TABLE `refunds` (
  `refund_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `order_item_id` int(11) DEFAULT NULL,
  `refund_type` enum('void_order','void_item','post_sale') DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `reason` varchar(255) NOT NULL,
  `refund_method` enum('cash') DEFAULT 'cash',
  `note` varchar(255) DEFAULT NULL,
  `processed_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`refund_id`),
  KEY `order_id` (`order_id`),
  KEY `order_item_id` (`order_item_id`),
  KEY `processed_by` (`processed_by`),
  CONSTRAINT `refunds_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  CONSTRAINT `refunds_ibfk_2` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`order_item_id`) ON DELETE SET NULL,
  CONSTRAINT `refunds_ibfk_3` FOREIGN KEY (`processed_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------
-- Table: `tables`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `tables`;
CREATE TABLE `tables` (
  `table_id` int(11) NOT NULL AUTO_INCREMENT,
  `table_name` varchar(30) NOT NULL,
  `capacity` int(11) DEFAULT 4,
  `status` enum('available','occupied') DEFAULT 'available',
  `current_order_id` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`table_id`),
  UNIQUE KEY `table_name` (`table_name`),
  KEY `fk_tables_current_order` (`current_order_id`),
  CONSTRAINT `fk_tables_current_order` FOREIGN KEY (`current_order_id`) REFERENCES `orders` (`order_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET FOREIGN_KEY_CHECKS = 1;
