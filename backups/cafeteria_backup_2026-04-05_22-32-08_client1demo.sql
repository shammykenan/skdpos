-- ============================================================
-- Dine with Din Cafeteria POS — Database Backup
-- Generated : 2026-04-05 22:32:08 (Asia/Manila)
-- Host      : LAPTOP-PL0A30MQ
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET NAMES utf8mb4;

-- ----------------------------------------------------------
-- Table: `categories`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `uq_categories_name` (`category_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` (`category_id`, `category_name`, `created_at`) VALUES
  ('1', 'Coffee', '2026-04-05 22:29:13'),
  ('2', 'Tea', '2026-04-05 22:29:13'),
  ('3', 'Pastry', '2026-04-05 22:29:13'),
  ('4', 'Cold Drinks', '2026-04-05 22:29:13');

-- ----------------------------------------------------------
-- Table: `login_attempts`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE `login_attempts` (
  `attempt_id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `success` tinyint(1) NOT NULL DEFAULT 0,
  `attempt_time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`attempt_id`),
  KEY `idx_la_ip_time` (`ip_address`,`attempt_time`),
  KEY `idx_la_username_time` (`username`,`attempt_time`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Table: `order_item_reasons`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `order_item_reasons`;
CREATE TABLE `order_item_reasons` (
  `reason_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reason_type` enum('cancel_order','void_order','cancel_item','void_item') NOT NULL,
  `reason_text` varchar(255) NOT NULL,
  `sort_order` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`reason_id`),
  KEY `idx_type_active` (`reason_type`,`is_active`,`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `order_item_reasons` (`reason_id`, `reason_type`, `reason_text`, `sort_order`, `is_active`, `created_at`) VALUES
  ('1', 'cancel_order', 'Customer changed mind', '1', '1', '2026-04-05 22:30:11'),
  ('2', 'cancel_order', 'Customer did not show up', '2', '1', '2026-04-05 22:30:11'),
  ('3', 'cancel_order', 'Duplicate order', '3', '1', '2026-04-05 22:30:11'),
  ('4', 'cancel_order', 'Wrong items ordered', '4', '1', '2026-04-05 22:30:11'),
  ('5', 'cancel_order', 'Item(s) unavailable', '5', '1', '2026-04-05 22:30:11'),
  ('6', 'cancel_order', 'Payment issue', '6', '1', '2026-04-05 22:30:11'),
  ('7', 'cancel_order', 'Other', '99', '1', '2026-04-05 22:30:11'),
  ('8', 'void_order', 'Customer left without paying', '1', '1', '2026-04-05 22:30:11'),
  ('9', 'void_order', 'Wrong order prepared', '2', '1', '2026-04-05 22:30:11'),
  ('10', 'void_order', 'Quality issue — item rejected', '3', '1', '2026-04-05 22:30:11'),
  ('11', 'void_order', 'Order prepared in error', '4', '1', '2026-04-05 22:30:11'),
  ('12', 'void_order', 'Customer refused order', '5', '1', '2026-04-05 22:30:11'),
  ('13', 'void_order', 'Other', '99', '1', '2026-04-05 22:30:11'),
  ('14', 'cancel_item', 'Customer changed mind', '1', '1', '2026-04-05 22:30:11'),
  ('15', 'cancel_item', 'Wrong item ordered', '2', '1', '2026-04-05 22:30:11'),
  ('16', 'cancel_item', 'Item unavailable', '3', '1', '2026-04-05 22:30:11'),
  ('17', 'cancel_item', 'Duplicate entry', '4', '1', '2026-04-05 22:30:11'),
  ('18', 'cancel_item', 'Customer allergy / dietary restriction', '5', '1', '2026-04-05 22:30:11'),
  ('19', 'cancel_item', 'Other', '99', '1', '2026-04-05 22:30:11'),
  ('20', 'void_item', 'Customer refused item', '1', '1', '2026-04-05 22:30:11'),
  ('21', 'void_item', 'Quality issue — item rejected', '2', '1', '2026-04-05 22:30:11'),
  ('22', 'void_item', 'Wrong item prepared', '3', '1', '2026-04-05 22:30:11'),
  ('23', 'void_item', 'Item prepared in error', '4', '1', '2026-04-05 22:30:11'),
  ('24', 'void_item', 'Customer left without paying', '5', '1', '2026-04-05 22:30:11'),
  ('25', 'void_item', 'Other', '99', '1', '2026-04-05 22:30:11');

-- ----------------------------------------------------------
-- Table: `units`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `units`;
CREATE TABLE `units` (
  `unit_id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_code` varchar(20) NOT NULL,
  `unit_label` varchar(50) NOT NULL,
  `unit_icon` varchar(50) NOT NULL DEFAULT 'fa-box',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`unit_id`),
  UNIQUE KEY `uq_units_code` (`unit_code`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `units` (`unit_id`, `unit_code`, `unit_label`, `unit_icon`, `is_active`, `created_at`) VALUES
  ('1', 'g', 'Gram', 'fa-weight', '1', '2026-04-05 22:29:13'),
  ('2', 'ml', 'Milliliter', 'fa-tint', '1', '2026-04-05 22:29:13'),
  ('3', 'pcs', 'Piece', 'fa-box', '1', '2026-04-05 22:29:13'),
  ('4', 'tbsp', 'Tablespoon', 'fa-spoon', '1', '2026-04-05 22:29:13'),
  ('5', 'tsp', 'Teaspoon', 'fa-spoon', '1', '2026-04-05 22:29:13');

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
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `uq_users_username` (`username`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`user_id`, `username`, `password_hash`, `full_name`, `email`, `role`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
  ('1', 'admin', '$2y$10$eXXbJzF6O41IR13WjaKJo.Y8ctwkdHjGGk/KiB31ovMGtMcUg.XRS', 'Admin123', 'dinshammykenan012@gmail.com', 'admin', '1', '2026-04-05 22:31:50', '2026-04-05 22:29:42', '2026-04-05 22:31:50');

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
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`item_id`),
  KEY `idx_menu_items_category` (`category_id`),
  CONSTRAINT `fk_menu_items_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `menu_items` (`item_id`, `item_name`, `description`, `base_price`, `cost_price`, `category_id`, `image_url`, `is_available`, `created_at`) VALUES
  ('1', 'Espresso', 'Single shot espresso', '80.00', '20.00', '1', NULL, '1', '2026-04-05 22:29:13'),
  ('2', 'Cappuccino', 'Espresso with steamed milk and foam', '120.00', '35.00', '1', NULL, '1', '2026-04-05 22:29:13'),
  ('3', 'Latte', 'Espresso with steamed milk', '130.00', '40.00', '1', NULL, '1', '2026-04-05 22:29:13'),
  ('4', 'Iced Coffee', 'Cold coffee with ice', '140.00', '45.00', '1', NULL, '1', '2026-04-05 22:29:13'),
  ('5', 'Black Tea', 'Hot black tea', '70.00', '10.00', '2', NULL, '1', '2026-04-05 22:29:13'),
  ('6', 'Green Tea', 'Hot green tea', '80.00', '12.00', '2', NULL, '1', '2026-04-05 22:29:13'),
  ('7', 'Chocolate Muffin', 'Chocolate flavored muffin', '90.00', '30.00', '3', NULL, '1', '2026-04-05 22:29:13'),
  ('8', 'Croissant', 'Buttery pastry', '100.00', '35.00', '3', NULL, '1', '2026-04-05 22:29:13');

-- ----------------------------------------------------------
-- Table: `ingredients`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `ingredients`;
CREATE TABLE `ingredients` (
  `ingredient_id` int(11) NOT NULL AUTO_INCREMENT,
  `ingredient_name` varchar(100) NOT NULL,
  `stock_quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `unit_id` int(11) DEFAULT NULL,
  `low_stock_alert` decimal(10,2) NOT NULL DEFAULT 10.00,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`ingredient_id`),
  UNIQUE KEY `uq_ingredients_name` (`ingredient_name`),
  KEY `fk_ingredients_unit` (`unit_id`),
  CONSTRAINT `fk_ingredients_unit` FOREIGN KEY (`unit_id`) REFERENCES `units` (`unit_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ingredients` (`ingredient_id`, `ingredient_name`, `stock_quantity`, `unit_id`, `low_stock_alert`, `last_updated`) VALUES
  ('1', 'Espresso Beans', '5000.00', '1', '500.00', '2026-04-05 22:29:13'),
  ('2', 'Milk', '10000.00', '2', '1000.00', '2026-04-05 22:29:13'),
  ('3', 'Water', '20000.00', '2', '2000.00', '2026-04-05 22:29:13'),
  ('4', 'Sugar', '2000.00', '1', '200.00', '2026-04-05 22:29:13'),
  ('5', 'Tea Leaves', '1000.00', '1', '100.00', '2026-04-05 22:29:13'),
  ('6', 'Chocolate Syrup', '2000.00', '2', '200.00', '2026-04-05 22:29:13'),
  ('7', 'Whipped Cream', '1000.00', '2', '100.00', '2026-04-05 22:29:13'),
  ('8', 'Butter', '1000.00', '1', '100.00', '2026-04-05 22:29:13'),
  ('9', 'Flour', '5000.00', '1', '500.00', '2026-04-05 22:29:13');

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
  KEY `idx_activity_logs_user` (`user_id`),
  KEY `idx_activity_logs_action` (`action`),
  CONSTRAINT `fk_activity_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `activity_logs` (`log_id`, `user_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
  ('1', '1', 'forgot_password_request', 'Password reset requested for email: dinshammykenan012@gmail.com', '192.168.100.11', '2026-04-05 22:31:13'),
  ('2', '1', 'password_reset_success', 'Password successfully reset via email link.', '192.168.100.11', '2026-04-05 22:31:40');

-- ----------------------------------------------------------
-- Table: `password_resets`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `reset_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`reset_id`),
  UNIQUE KEY `uq_pr_token` (`token`),
  KEY `idx_pr_token` (`token`),
  KEY `idx_pr_user` (`user_id`),
  KEY `idx_pr_ip_created` (`ip_address`,`created_at`),
  KEY `idx_pr_expires` (`expires_at`),
  CONSTRAINT `fk_password_resets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `password_resets` (`reset_id`, `user_id`, `token`, `expires_at`, `used`, `ip_address`, `created_at`) VALUES
  ('1', '1', '370e378f8830096d5244cae9513955550cd0e1db1af0ecb52835871848ee2c5d', '2026-04-05 23:31:11', '1', '192.168.100.11', '2026-04-05 22:31:11');

-- ----------------------------------------------------------
-- Table: `menu_variants`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `menu_variants`;
CREATE TABLE `menu_variants` (
  `variant_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `variant_name` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `cost_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`variant_id`),
  UNIQUE KEY `uq_variant_per_item` (`item_id`,`variant_name`),
  KEY `idx_menu_variants_item` (`item_id`),
  CONSTRAINT `fk_menu_variants_item` FOREIGN KEY (`item_id`) REFERENCES `menu_items` (`item_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`addon_id`),
  KEY `fk_addons_ingredient` (`ingredient_id`),
  CONSTRAINT `fk_addons_ingredient` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`ingredient_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  UNIQUE KEY `uq_recipe` (`item_id`,`ingredient_id`,`variant_id_key`),
  KEY `fk_recipes_ingredient` (`ingredient_id`),
  KEY `idx_recipe_item` (`item_id`),
  KEY `idx_recipe_variant` (`variant_id`),
  CONSTRAINT `fk_recipes_ingredient` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`ingredient_id`),
  CONSTRAINT `fk_recipes_item` FOREIGN KEY (`item_id`) REFERENCES `menu_items` (`item_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_recipes_variant` FOREIGN KEY (`variant_id`) REFERENCES `menu_variants` (`variant_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `recipes` (`recipe_id`, `item_id`, `variant_id`, `ingredient_id`, `quantity_required`, `variant_id_key`) VALUES
  ('1', '1', NULL, '1', '18.00', '0'),
  ('2', '1', NULL, '3', '30.00', '0'),
  ('3', '2', NULL, '1', '18.00', '0'),
  ('4', '2', NULL, '3', '30.00', '0'),
  ('5', '2', NULL, '2', '150.00', '0'),
  ('6', '2', NULL, '4', '5.00', '0'),
  ('7', '3', NULL, '1', '18.00', '0'),
  ('8', '3', NULL, '3', '30.00', '0'),
  ('9', '3', NULL, '2', '200.00', '0'),
  ('10', '3', NULL, '4', '5.00', '0'),
  ('11', '4', NULL, '1', '18.00', '0'),
  ('12', '4', NULL, '3', '50.00', '0'),
  ('13', '4', NULL, '2', '150.00', '0'),
  ('14', '4', NULL, '4', '5.00', '0'),
  ('15', '5', NULL, '5', '5.00', '0'),
  ('16', '5', NULL, '3', '200.00', '0'),
  ('17', '5', NULL, '4', '5.00', '0'),
  ('18', '6', NULL, '5', '5.00', '0'),
  ('19', '6', NULL, '3', '200.00', '0'),
  ('20', '7', NULL, '9', '100.00', '0'),
  ('21', '7', NULL, '8', '20.00', '0'),
  ('22', '7', NULL, '6', '30.00', '0'),
  ('23', '7', NULL, '4', '10.00', '0'),
  ('24', '8', NULL, '9', '80.00', '0'),
  ('25', '8', NULL, '8', '30.00', '0');

-- ----------------------------------------------------------
-- Table: `order_item_addons`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `order_item_addons`;
CREATE TABLE `order_item_addons` (
  `order_item_addon_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_item_id` int(11) NOT NULL,
  `addon_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`order_item_addon_id`),
  KEY `fk_oia_order_item` (`order_item_id`),
  KEY `fk_oia_addon` (`addon_id`),
  CONSTRAINT `fk_oia_addon` FOREIGN KEY (`addon_id`) REFERENCES `addons` (`addon_id`),
  CONSTRAINT `fk_oia_order_item` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`order_item_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `waste_cost` decimal(10,2) DEFAULT NULL,
  `item_notes` text DEFAULT NULL,
  `void_reason` varchar(255) DEFAULT NULL,
  `cancel_reason` varchar(255) DEFAULT NULL,
  `status` enum('pending','preparing','ready','completed','cancelled','voided') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`order_item_id`),
  KEY `fk_order_items_item` (`item_id`),
  KEY `idx_order_items_order` (`order_id`),
  KEY `idx_order_items_variant` (`variant_id`),
  CONSTRAINT `fk_order_items_item` FOREIGN KEY (`item_id`) REFERENCES `menu_items` (`item_id`),
  CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_order_items_variant` FOREIGN KEY (`variant_id`) REFERENCES `menu_variants` (`variant_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Table: `orders`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_number` varchar(20) NOT NULL,
  `cashier_id` int(11) NOT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `table_id` int(11) DEFAULT NULL,
  `order_type` enum('dine_in','takeout') NOT NULL DEFAULT 'dine_in',
  `order_source` enum('walk_in','online') NOT NULL DEFAULT 'walk_in',
  `subtotal` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `waste_total` decimal(10,2) DEFAULT NULL,
  `status` enum('draft','pending','preparing','ready','completed','cancelled','voided') NOT NULL DEFAULT 'pending',
  `void_reason` varchar(255) DEFAULT NULL,
  `cancel_reason` varchar(255) DEFAULT NULL,
  `draft_cart` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  UNIQUE KEY `uq_orders_number` (`order_number`),
  KEY `fk_orders_table` (`table_id`),
  KEY `idx_orders_cashier` (`cashier_id`),
  KEY `idx_orders_status` (`status`),
  CONSTRAINT `fk_orders_cashier` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `fk_orders_table` FOREIGN KEY (`table_id`) REFERENCES `tables` (`table_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Table: `payments`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `payment_method` enum('cash') NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `change_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_status` enum('paid','pending') NOT NULL DEFAULT 'paid',
  `transaction_ref` varchar(100) DEFAULT NULL,
  `cashier_id` int(11) NOT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `paid_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`payment_id`),
  KEY `fk_payments_order` (`order_id`),
  KEY `fk_payments_cashier` (`cashier_id`),
  KEY `fk_payments_processed_by` (`processed_by`),
  CONSTRAINT `fk_payments_cashier` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `fk_payments_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  CONSTRAINT `fk_payments_processed_by` FOREIGN KEY (`processed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Table: `refunds`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `refunds`;
CREATE TABLE `refunds` (
  `refund_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `order_item_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `refund_method` enum('cash') NOT NULL DEFAULT 'cash',
  `processed_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`refund_id`),
  KEY `fk_refunds_item` (`order_item_id`),
  KEY `fk_refunds_user` (`processed_by`),
  KEY `idx_refunds_order` (`order_id`),
  KEY `idx_refunds_date` (`created_at`),
  CONSTRAINT `fk_refunds_item` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`order_item_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_refunds_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  CONSTRAINT `fk_refunds_user` FOREIGN KEY (`processed_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Table: `tables`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `tables`;
CREATE TABLE `tables` (
  `table_id` int(11) NOT NULL AUTO_INCREMENT,
  `table_name` varchar(30) NOT NULL,
  `capacity` int(11) NOT NULL DEFAULT 4,
  `status` enum('available','occupied') NOT NULL DEFAULT 'available',
  `current_order_id` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`table_id`),
  UNIQUE KEY `uq_tables_name` (`table_name`),
  KEY `fk_tables_current_order` (`current_order_id`),
  CONSTRAINT `fk_tables_current_order` FOREIGN KEY (`current_order_id`) REFERENCES `orders` (`order_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
