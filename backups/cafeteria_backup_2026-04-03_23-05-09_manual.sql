-- ============================================================
-- Dine with Din Cafeteria POS — Database Backup
-- Generated : 2026-04-03 23:05:09 (Asia/Manila)
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` (`category_id`, `category_name`, `created_at`) VALUES
  ('1', 'Milk Tea', '2026-04-01 17:34:17'),
  ('2', 'Desserts', '2026-04-01 17:34:17');

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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  ('1', 'cancel_order', 'Customer changed mind', '1', '1', '2026-04-01 17:35:32'),
  ('2', 'cancel_order', 'Customer did not show up', '2', '1', '2026-04-01 17:35:32'),
  ('3', 'cancel_order', 'Duplicate order', '3', '1', '2026-04-01 17:35:32'),
  ('4', 'cancel_order', 'Wrong items ordered', '4', '1', '2026-04-01 17:35:32'),
  ('5', 'cancel_order', 'Item(s) unavailable', '5', '1', '2026-04-01 17:35:32'),
  ('6', 'cancel_order', 'Payment issue', '6', '1', '2026-04-01 17:35:32'),
  ('7', 'cancel_order', 'Other', '99', '1', '2026-04-01 17:35:32'),
  ('8', 'void_order', 'Customer left without paying', '1', '1', '2026-04-01 17:35:32'),
  ('9', 'void_order', 'Wrong order prepared', '2', '1', '2026-04-01 17:35:32'),
  ('10', 'void_order', 'Quality issue — item rejected', '3', '1', '2026-04-01 17:35:32'),
  ('11', 'void_order', 'Order prepared in error', '4', '1', '2026-04-01 17:35:32'),
  ('12', 'void_order', 'Customer refused order', '5', '1', '2026-04-01 17:35:32'),
  ('13', 'void_order', 'Other', '99', '1', '2026-04-01 17:35:32'),
  ('14', 'cancel_item', 'Customer changed mind', '1', '1', '2026-04-01 17:35:32'),
  ('15', 'cancel_item', 'Wrong item ordered', '2', '1', '2026-04-01 17:35:32'),
  ('16', 'cancel_item', 'Item unavailable', '3', '1', '2026-04-01 17:35:32'),
  ('17', 'cancel_item', 'Duplicate entry', '4', '1', '2026-04-01 17:35:32'),
  ('18', 'cancel_item', 'Customer allergy / dietary restriction', '5', '1', '2026-04-01 17:35:32'),
  ('19', 'cancel_item', 'Other', '99', '1', '2026-04-01 17:35:32'),
  ('20', 'void_item', 'Customer refused item', '1', '1', '2026-04-01 17:35:32'),
  ('21', 'void_item', 'Quality issue — item rejected', '2', '1', '2026-04-01 17:35:32'),
  ('22', 'void_item', 'Wrong item prepared', '3', '1', '2026-04-01 17:35:32'),
  ('23', 'void_item', 'Item prepared in error', '4', '1', '2026-04-01 17:35:32'),
  ('24', 'void_item', 'Customer left without paying', '5', '1', '2026-04-01 17:35:32'),
  ('25', 'void_item', 'Other', '99', '1', '2026-04-01 17:35:32');

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `units` (`unit_id`, `unit_code`, `unit_label`, `unit_icon`, `is_active`, `created_at`) VALUES
  ('1', 'g', 'grams', 'fa-box', '1', '2026-04-01 17:34:17'),
  ('2', 'ml', 'milliliters', 'fa-box', '1', '2026-04-01 17:34:17'),
  ('3', 'pcs', 'pieces', 'fa-box', '1', '2026-04-01 17:34:17');

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
  ('1', 'admin', '$2y$10$iCzTOKW1x6x5jKYj1KRCQenOQGji6H02/CFd0.NoDHFQiaMX7FUDG', 'Admin', 'dinshammykenan012@gmail.com', 'admin', '1', '2026-04-03 23:04:21', '2026-04-01 17:31:29', '2026-04-03 23:04:21');

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `menu_items` (`item_id`, `item_name`, `description`, `base_price`, `cost_price`, `category_id`, `image_url`, `is_available`, `created_at`) VALUES
  ('1', 'Classic Milk Tea', 'Sweet milk tea with pearls', '80.00', '40.00', '1', NULL, '1', '2026-04-01 17:34:17'),
  ('2', 'Chocolate Milk Tea', 'Chocolate flavored milk tea', '90.00', '50.00', '1', NULL, '1', '2026-04-01 17:34:17'),
  ('3', 'Waffle', 'Freshly made waffle', '50.00', '30.00', '2', NULL, '1', '2026-04-01 17:34:17'),
  ('4', 'Chocolate Cake Slice', 'Moist chocolate cake', '120.00', '70.00', '2', NULL, '1', '2026-04-01 17:34:17');

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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ingredients` (`ingredient_id`, `ingredient_name`, `stock_quantity`, `unit_id`, `low_stock_alert`, `last_updated`) VALUES
  ('1', 'Tea', '868.00', '1', '10.00', '2026-04-03 22:26:37'),
  ('2', 'Milk Powder', '1455.00', '1', '10.00', '2026-04-03 22:26:37'),
  ('3', 'Sugar', '2445.00', '1', '10.00', '2026-04-03 22:26:37'),
  ('4', 'Ice', '2350.00', '1', '10.00', '2026-04-03 22:26:37'),
  ('5', 'Tapioca Pearls', '830.00', '1', '10.00', '2026-04-03 22:26:37'),
  ('6', 'All Purpose Flour', '640.00', '1', '10.00', '2026-04-03 22:22:01'),
  ('7', 'Egg', '170.00', '3', '10.00', '2026-04-03 22:22:01'),
  ('8', 'Butter', '500.00', '1', '10.00', '2026-04-03 22:22:01'),
  ('9', 'Chocolate Syrup', '175.00', '2', '10.00', '2026-04-03 22:26:37'),
  ('10', 'Whipped Cream', '500.00', '2', '10.00', '2026-04-01 17:34:17'),
  ('11', 'Condensed Milk', '960.00', '2', '10.00', '2026-04-03 21:05:41');

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
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `activity_logs` (`log_id`, `user_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
  ('1', '1', 'forgot_password_request', 'Password reset requested for email: dinshammykenan012@gmail.com', '192.168.100.11', '2026-04-01 17:32:25'),
  ('2', '1', 'password_reset_success', 'Password successfully reset via email link.', '192.168.100.11', '2026-04-01 17:32:50'),
  ('3', '1', 'void_entire_order', '{\"order_id\":4,\"order_number\":\"SKD-20260401-61FD05\",\"reason\":\"Customer left without paying\",\"performed_by\":1,\"items\":[{\"item\":\"Waffle\",\"qty\":1,\"waste_cost\":30}],\"total_waste_cost\":30,\"stock_note\":\"Ingredients already consumed by kitchen \\u2014 stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-04-01 17:35:41'),
  ('4', '1', 'void_entire_order', '{\"order_id\":6,\"order_number\":\"SKD-20260401-923C23\",\"reason\":\"Wrong order prepared\",\"performed_by\":1,\"items\":[{\"item\":\"Chocolate Milk Tea\",\"qty\":1,\"waste_cost\":50}],\"total_waste_cost\":50,\"stock_note\":\"Ingredients already consumed by kitchen \\u2014 stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-04-01 17:44:31'),
  ('5', '1', 'void_entire_order', '{\"order_id\":8,\"order_number\":\"SKD-20260401-77FA6B\",\"reason\":\"Customer refused order\",\"performed_by\":1,\"items\":[{\"item\":\"Waffle\",\"qty\":1,\"waste_cost\":30}],\"total_waste_cost\":30,\"stock_note\":\"Ingredients already consumed by kitchen \\u2014 stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-04-01 17:46:27'),
  ('6', '1', 'void_entire_order', '{\"order_id\":10,\"order_number\":\"SKD-20260401-D30ABE\",\"reason\":\"Wrong order prepared\",\"performed_by\":1,\"items\":[{\"item\":\"Waffle\",\"qty\":1,\"base_cost\":30,\"addon_cost\":10,\"waste_cost\":40}],\"total_waste_cost\":40,\"stock_note\":\"Ingredients already consumed by kitchen \\u2014 stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-04-01 17:50:32'),
  ('7', '1', 'void_entire_order', '{\"order_id\":2,\"order_number\":\"SKD-20260401-665DD7\",\"reason\":\"Customer left without paying\",\"performed_by\":1,\"items\":[{\"item\":\"Waffle\",\"qty\":1,\"base_cost\":30,\"addon_cost\":0,\"waste_cost\":30}],\"total_waste_cost\":30,\"stock_note\":\"Ingredients already consumed by kitchen \\u2014 stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-04-01 18:08:37'),
  ('8', '1', 'void_entire_order', '{\"order_id\":22,\"order_number\":\"SKD-20260401-8B4065\",\"reason\":\"Wrong order prepared\",\"performed_by\":1,\"items\":[{\"item\":\"Waffle\",\"qty\":1,\"base_cost\":30,\"addon_cost\":0,\"waste_cost\":30}],\"total_waste_cost\":30,\"stock_note\":\"Ingredients already consumed by kitchen \\u2014 stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-04-01 20:32:38'),
  ('9', '1', 'void_entire_order', '{\"order_id\":24,\"order_number\":\"SKD-20260401-F059D0\",\"reason\":\"Order prepared in error\",\"performed_by\":1,\"items\":[{\"item\":\"Waffle\",\"qty\":1,\"base_cost\":30,\"addon_cost\":0,\"waste_cost\":30}],\"total_waste_cost\":30,\"stock_note\":\"Ingredients already consumed by kitchen \\u2014 stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-04-01 20:32:43'),
  ('10', '1', 'void_entire_order', '{\"order_id\":26,\"order_number\":\"SKD-20260401-3D55C3\",\"reason\":\"Customer left without paying\",\"performed_by\":1,\"items\":[{\"item\":\"Waffle\",\"qty\":1,\"base_cost\":30,\"addon_cost\":0,\"waste_cost\":30}],\"total_waste_cost\":30,\"stock_note\":\"Ingredients already consumed by kitchen \\u2014 stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-04-01 20:32:47'),
  ('11', '1', 'void_entire_order', '{\"order_id\":28,\"order_number\":\"SKD-20260401-58DF0D\",\"reason\":\"Order prepared in error\",\"performed_by\":1,\"items\":[{\"item\":\"Waffle\",\"qty\":1,\"base_cost\":30,\"addon_cost\":0,\"waste_cost\":30}],\"total_waste_cost\":30,\"stock_note\":\"Ingredients already consumed by kitchen \\u2014 stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-04-01 20:33:42'),
  ('12', '1', 'void_entire_order', '{\"order_id\":30,\"order_number\":\"SKD-20260401-A9A002\",\"reason\":\"Customer left without paying\",\"performed_by\":1,\"items\":[{\"item\":\"Waffle\",\"qty\":1,\"base_cost\":30,\"addon_cost\":0,\"waste_cost\":30}],\"total_waste_cost\":30,\"stock_note\":\"Ingredients already consumed by kitchen \\u2014 stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-04-01 20:33:46'),
  ('13', '1', 'void_entire_order', '{\"order_id\":32,\"order_number\":\"SKD-20260401-FAF2A1\",\"reason\":\"Quality issue \\u2014 item rejected\",\"performed_by\":1,\"items\":[{\"item\":\"Waffle\",\"qty\":1,\"base_cost\":30,\"addon_cost\":0,\"waste_cost\":30}],\"total_waste_cost\":30,\"stock_note\":\"Ingredients already consumed by kitchen \\u2014 stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-04-01 20:33:49'),
  ('14', '1', 'void_entire_order', '{\"order_id\":34,\"order_number\":\"SKD-20260401-DD8060\",\"reason\":\"idk\",\"performed_by\":1,\"items\":[{\"item\":\"Waffle\",\"qty\":1,\"base_cost\":30,\"addon_cost\":0,\"waste_cost\":30}],\"total_waste_cost\":30,\"stock_note\":\"Ingredients already consumed by kitchen \\u2014 stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-04-01 20:38:02'),
  ('15', '1', 'mark_served', '{\"order_id\":42,\"order_number\":\"SKD-20260403-6326EC\",\"performed_by\":1,\"note\":\"Order marked as served. Payment was collected at POS.\"}', '192.168.100.11', '2026-04-03 22:16:19'),
  ('16', '1', 'void_entire_order', '{\"order_id\":50,\"order_number\":\"SKD-20260403-E82935\",\"reason\":\"Customer left without paying\",\"performed_by\":1,\"items\":[{\"item\":\"Chocolate Milk Tea\",\"qty\":1,\"base_cost\":0,\"addon_cost\":10,\"waste_cost\":10}],\"total_waste_cost\":10,\"stock_note\":\"Ingredients already consumed by kitchen \\u2014 stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-04-03 22:17:26'),
  ('17', '1', 'cancel_order', '{\"order_id\":53,\"order_number\":\"SKD-20260403-3C3C5D\",\"reason\":\"Customer changed mind\",\"performed_by\":1,\"stock_note\":\"Order was pending \\u2014 no stock had been deducted. Nothing restored.\"}', '192.168.100.11', '2026-04-03 22:17:44'),
  ('18', '1', 'mark_served', '{\"order_id\":46,\"order_number\":\"SKD-20260403-614E39\",\"performed_by\":1,\"note\":\"Order marked as served. Payment was collected at POS.\"}', '192.168.100.11', '2026-04-03 22:17:53'),
  ('19', '1', 'mark_served', '{\"order_id\":55,\"order_number\":\"SKD-20260403-719F44\",\"performed_by\":1,\"note\":\"Order marked as served. Payment was collected at POS.\"}', '192.168.100.11', '2026-04-03 22:20:54'),
  ('20', '1', 'void_order_item', '{\"order_item_id\":29,\"order_id\":57,\"item\":\"Waffle\",\"qty\":1,\"reason\":\"Wrong item prepared\",\"performed_by\":1,\"waste_cost\":30,\"stock_note\":\"Item was ready \\u2014 ingredients already consumed. Stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-04-03 22:22:11'),
  ('21', '1', 'mark_served', '{\"order_id\":57,\"order_number\":\"SKD-20260403-343ECE\",\"performed_by\":1,\"note\":\"Order marked as served. Payment was collected at POS.\"}', '192.168.100.11', '2026-04-03 22:22:44'),
  ('22', '1', 'mark_served', '{\"order_id\":59,\"order_number\":\"SKD-20260403-56E361\",\"performed_by\":1,\"note\":\"Order marked as served. Payment was collected at POS.\"}', '192.168.100.11', '2026-04-03 22:26:42');

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
  ('1', '1', '431dfaa8d549fe8c661b2667d88531826f5bcae5270f4087990fc179ce11c854', '2026-04-01 18:32:23', '1', '192.168.100.11', '2026-04-01 17:32:23');

-- ----------------------------------------------------------
-- Table: `rate_limits`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `rate_limits`;
CREATE TABLE `rate_limits` (
  `limit_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `request_time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`limit_id`),
  KEY `idx_rl_user_action` (`user_id`,`action`),
  KEY `idx_rl_request_time` (`request_time`),
  CONSTRAINT `rate_limits_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `menu_variants` (`variant_id`, `item_id`, `variant_name`, `price`, `cost_price`, `is_active`, `created_at`, `updated_at`) VALUES
  ('1', '1', 'Small', '80.00', '0.00', '1', '2026-04-01 17:34:17', NULL),
  ('2', '1', 'Medium', '100.00', '0.00', '1', '2026-04-01 17:34:17', NULL),
  ('3', '1', 'Large', '120.00', '0.00', '1', '2026-04-01 17:34:17', NULL),
  ('4', '2', 'Small', '90.00', '0.00', '1', '2026-04-01 17:34:17', NULL),
  ('5', '2', 'Medium', '110.00', '0.00', '1', '2026-04-01 17:34:17', NULL),
  ('6', '2', 'Large', '130.00', '0.00', '1', '2026-04-01 17:34:17', NULL);

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
  CONSTRAINT `fk_addons_ingredient` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`ingredient_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `addons` (`addon_id`, `addon_name`, `price`, `ingredient_id`, `quantity_used`, `is_active`, `last_updated`) VALUES
  ('2', 'Extra Chocolate', '20.00', '9', '15.00', '1', '2026-04-03 20:40:21'),
  ('4', 'Extra Milk Powder', '10.00', '2', '5.00', '1', '2026-04-03 20:40:26');

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
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `recipes` (`recipe_id`, `item_id`, `variant_id`, `ingredient_id`, `quantity_required`, `variant_id_key`) VALUES
  ('1', '1', '1', '1', '5.00', '1'),
  ('2', '1', '1', '2', '20.00', '1'),
  ('3', '1', '1', '3', '15.00', '1'),
  ('4', '1', '1', '4', '100.00', '1'),
  ('5', '1', '1', '5', '30.00', '1'),
  ('6', '1', '2', '1', '7.00', '2'),
  ('7', '1', '2', '2', '30.00', '2'),
  ('8', '1', '2', '3', '20.00', '2'),
  ('9', '1', '2', '4', '150.00', '2'),
  ('10', '1', '2', '5', '40.00', '2'),
  ('11', '1', '3', '1', '10.00', '3'),
  ('12', '1', '3', '2', '40.00', '3'),
  ('13', '1', '3', '3', '25.00', '3'),
  ('14', '1', '3', '4', '200.00', '3'),
  ('15', '1', '3', '5', '50.00', '3'),
  ('16', '2', '4', '1', '5.00', '4'),
  ('17', '2', '4', '2', '20.00', '4'),
  ('18', '2', '4', '3', '15.00', '4'),
  ('19', '2', '4', '4', '100.00', '4'),
  ('20', '2', '4', '5', '30.00', '4'),
  ('21', '2', '4', '9', '20.00', '4'),
  ('22', '2', '5', '1', '7.00', '5'),
  ('23', '2', '5', '2', '30.00', '5'),
  ('24', '2', '5', '3', '20.00', '5'),
  ('25', '2', '5', '4', '150.00', '5'),
  ('26', '2', '5', '5', '40.00', '5'),
  ('27', '2', '5', '9', '30.00', '5'),
  ('28', '2', '6', '1', '10.00', '6'),
  ('29', '2', '6', '2', '40.00', '6'),
  ('30', '2', '6', '3', '25.00', '6'),
  ('31', '2', '6', '4', '200.00', '6'),
  ('32', '2', '6', '5', '50.00', '6'),
  ('33', '2', '6', '9', '40.00', '6'),
  ('34', '3', NULL, '6', '100.00', '0'),
  ('35', '3', NULL, '7', '1.00', '0'),
  ('36', '3', NULL, '8', '20.00', '0'),
  ('37', '3', NULL, '3', '10.00', '0'),
  ('38', '4', NULL, '6', '80.00', '0'),
  ('39', '4', NULL, '7', '2.00', '0'),
  ('40', '4', NULL, '8', '30.00', '0'),
  ('41', '4', NULL, '9', '40.00', '0'),
  ('42', '4', NULL, '11', '20.00', '0');

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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `order_item_addons` (`order_item_addon_id`, `order_item_id`, `addon_id`, `quantity`, `price`) VALUES
  ('1', '3', '2', '1', '20.00'),
  ('2', '4', '4', '1', '10.00'),
  ('3', '5', '4', '1', '10.00'),
  ('4', '6', '4', '2', '10.00'),
  ('5', '24', '4', '1', '10.00'),
  ('6', '25', '4', '1', '10.00'),
  ('7', '26', '4', '1', '10.00'),
  ('8', '30', '4', '1', '10.00');

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
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `order_items` (`order_item_id`, `order_id`, `item_id`, `variant_id`, `item_name_snapshot`, `variant_name_snapshot`, `quantity`, `unit_price`, `subtotal`, `waste_cost`, `item_notes`, `void_reason`, `cancel_reason`, `status`) VALUES
  ('1', '2', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', '30.00', NULL, 'Customer left without paying', NULL, 'voided'),
  ('2', '4', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', '30.00', NULL, 'Customer left without paying', NULL, 'voided'),
  ('3', '6', '2', '4', 'Chocolate Milk Tea', 'Small', '1', '90.00', '90.00', '50.00', NULL, 'Wrong order prepared', NULL, 'voided'),
  ('4', '8', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', '30.00', 'swwwss', 'Customer refused order', NULL, 'voided'),
  ('5', '10', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', '40.00', 'wssws', 'Wrong order prepared', NULL, 'voided'),
  ('6', '12', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, '22', NULL, NULL, 'completed'),
  ('7', '16', '2', '6', 'Chocolate Milk Tea', 'Large', '10', '130.00', '1300.00', NULL, NULL, NULL, NULL, 'completed'),
  ('8', '18', '3', NULL, 'Waffle', NULL, '1', '50.00', '50.00', NULL, NULL, NULL, NULL, 'completed'),
  ('9', '20', '3', NULL, 'Waffle', NULL, '1', '50.00', '50.00', NULL, NULL, NULL, NULL, 'completed'),
  ('10', '22', '3', NULL, 'Waffle', NULL, '1', '50.00', '50.00', '30.00', NULL, 'Wrong order prepared', NULL, 'voided'),
  ('11', '24', '3', NULL, 'Waffle', NULL, '1', '50.00', '50.00', '30.00', '2', 'Order prepared in error', NULL, 'voided'),
  ('12', '26', '3', NULL, 'Waffle', NULL, '1', '50.00', '50.00', '30.00', '3', 'Customer left without paying', NULL, 'voided'),
  ('13', '28', '3', NULL, 'Waffle', NULL, '1', '50.00', '50.00', '30.00', NULL, 'Order prepared in error', NULL, 'voided'),
  ('14', '30', '3', NULL, 'Waffle', NULL, '1', '50.00', '50.00', '30.00', NULL, 'Customer left without paying', NULL, 'voided'),
  ('15', '32', '3', NULL, 'Waffle', NULL, '1', '50.00', '50.00', '30.00', NULL, 'Quality issue — item rejected', NULL, 'voided'),
  ('16', '34', '3', NULL, 'Waffle', NULL, '1', '50.00', '50.00', '30.00', NULL, 'idk', NULL, 'voided'),
  ('17', '36', '3', NULL, 'Waffle', NULL, '1', '50.00', '50.00', NULL, NULL, NULL, NULL, 'completed'),
  ('18', '36', '4', NULL, 'Chocolate Cake Slice', NULL, '1', '120.00', '120.00', NULL, NULL, NULL, NULL, 'completed'),
  ('19', '38', '3', NULL, 'Waffle', NULL, '1', '50.00', '50.00', NULL, NULL, NULL, NULL, 'completed'),
  ('20', '40', '3', NULL, 'Waffle', NULL, '1', '50.00', '50.00', NULL, NULL, NULL, NULL, 'completed'),
  ('21', '42', '3', NULL, 'Waffle', NULL, '1', '50.00', '50.00', NULL, NULL, NULL, NULL, 'completed'),
  ('22', '44', '4', NULL, 'Chocolate Cake Slice', NULL, '1', '120.00', '120.00', NULL, 'haha', NULL, NULL, 'completed'),
  ('23', '46', '3', NULL, 'Waffle', NULL, '1', '50.00', '50.00', NULL, NULL, NULL, NULL, 'completed'),
  ('24', '48', '2', '5', 'Chocolate Milk Tea', 'Medium', '1', '110.00', '110.00', NULL, NULL, NULL, NULL, 'completed'),
  ('25', '50', '2', '6', 'Chocolate Milk Tea', 'Large', '1', '130.00', '130.00', '10.00', NULL, 'Customer left without paying', NULL, 'voided'),
  ('26', '53', '1', '1', 'Classic Milk Tea', 'Small', '1', '80.00', '80.00', NULL, NULL, NULL, 'Customer changed mind', 'cancelled'),
  ('27', '55', '3', NULL, 'Waffle', NULL, '1', '50.00', '50.00', NULL, 'hshs', NULL, NULL, 'completed'),
  ('28', '57', '3', NULL, 'Waffle', NULL, '1', '50.00', '50.00', NULL, NULL, NULL, NULL, 'completed'),
  ('29', '57', '3', NULL, 'Waffle', NULL, '1', '50.00', '50.00', '30.00', 'void', 'Wrong item prepared', NULL, 'voided'),
  ('30', '59', '2', '6', 'Chocolate Milk Tea', 'Large', '1', '130.00', '130.00', NULL, 'uhuhh', NULL, NULL, 'completed');

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
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `orders` (`order_id`, `order_number`, `cashier_id`, `customer_name`, `table_id`, `order_type`, `order_source`, `subtotal`, `discount`, `total_amount`, `waste_total`, `status`, `void_reason`, `cancel_reason`, `draft_cart`, `created_at`, `completed_at`) VALUES
  ('2', 'SKD-20260401-665DD7', '1', 's', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', '30.00', 'voided', 'Customer left without paying', NULL, NULL, '2026-04-01 17:34:30', NULL),
  ('4', 'SKD-20260401-61FD05', '1', 'w', '1', 'dine_in', 'walk_in', '0.00', '0.00', '0.00', '30.00', 'voided', 'Customer left without paying', NULL, NULL, '2026-04-01 17:34:46', NULL),
  ('6', 'SKD-20260401-923C23', '1', 'w', '1', 'dine_in', 'walk_in', '0.00', '0.00', '0.00', '50.00', 'voided', 'Wrong order prepared', NULL, NULL, '2026-04-01 17:36:25', NULL),
  ('8', 'SKD-20260401-77FA6B', '1', 'q', '1', 'dine_in', 'walk_in', '0.00', '0.00', '0.00', '30.00', 'voided', 'Customer refused order', NULL, NULL, '2026-04-01 17:46:15', NULL),
  ('10', 'SKD-20260401-D30ABE', '1', 'r', '1', 'dine_in', 'walk_in', '0.00', '0.00', '0.00', '40.00', 'voided', 'Wrong order prepared', NULL, NULL, '2026-04-01 17:50:21', NULL),
  ('12', 'SKD-20260401-FE73A6', '1', '2', '1', 'dine_in', 'walk_in', '80.00', '0.00', '80.00', NULL, 'completed', NULL, NULL, NULL, '2026-04-01 17:50:55', '2026-04-01 18:08:24'),
  ('16', 'SKD-20260401-3CBE12', '1', 'Shammy', NULL, 'takeout', 'walk_in', '1300.00', '0.00', '1300.00', NULL, 'completed', NULL, NULL, NULL, '2026-04-01 20:05:07', '2026-04-01 20:05:35'),
  ('18', 'SKD-20260401-E73491', '1', 'w', NULL, 'takeout', 'walk_in', '50.00', '0.00', '50.00', NULL, 'completed', NULL, NULL, NULL, '2026-04-01 20:31:26', '2026-04-01 20:32:22'),
  ('20', 'SKD-20260401-489FB8', '1', '3', NULL, 'takeout', 'walk_in', '50.00', '0.00', '50.00', NULL, 'completed', NULL, NULL, NULL, '2026-04-01 20:31:32', '2026-04-01 20:32:29'),
  ('22', 'SKD-20260401-8B4065', '1', '3', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', '30.00', 'voided', 'Wrong order prepared', NULL, NULL, '2026-04-01 20:31:36', NULL),
  ('24', 'SKD-20260401-F059D0', '1', '1', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', '30.00', 'voided', 'Order prepared in error', NULL, NULL, '2026-04-01 20:31:43', NULL),
  ('26', 'SKD-20260401-3D55C3', '1', NULL, NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', '30.00', 'voided', 'Customer left without paying', NULL, NULL, '2026-04-01 20:31:47', NULL),
  ('28', 'SKD-20260401-58DF0D', '1', '1', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', '30.00', 'voided', 'Order prepared in error', NULL, NULL, '2026-04-01 20:33:09', NULL),
  ('30', 'SKD-20260401-A9A002', '1', '3', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', '30.00', 'voided', 'Customer left without paying', NULL, NULL, '2026-04-01 20:33:14', NULL),
  ('32', 'SKD-20260401-FAF2A1', '1', '1', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', '30.00', 'voided', 'Quality issue — item rejected', NULL, NULL, '2026-04-01 20:33:19', NULL),
  ('34', 'SKD-20260401-DD8060', '1', 's', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', '30.00', 'voided', 'idk', NULL, NULL, '2026-04-01 20:37:49', NULL),
  ('36', 'SKD-20260401-BF0446', '1', 'Jbo', NULL, 'takeout', 'walk_in', '170.00', '0.00', '170.00', NULL, 'completed', NULL, NULL, NULL, '2026-04-01 21:09:15', '2026-04-01 21:29:59'),
  ('38', 'SKD-20260403-287276', '1', 'Shammy', NULL, 'takeout', 'walk_in', '50.00', '0.00', '50.00', NULL, 'completed', NULL, NULL, NULL, '2026-04-03 20:25:22', '2026-04-03 20:25:41'),
  ('40', 'SKD-20260403-E8C00D', '1', 'sham', NULL, 'takeout', 'walk_in', '50.00', '0.00', '50.00', NULL, 'completed', NULL, NULL, NULL, '2026-04-03 20:48:30', '2026-04-03 21:04:02'),
  ('42', 'SKD-20260403-6326EC', '1', 'sham', NULL, 'takeout', 'walk_in', '50.00', '0.00', '50.00', NULL, 'completed', NULL, NULL, NULL, '2026-04-03 21:03:02', '2026-04-03 22:16:19'),
  ('44', 'SKD-20260403-96ACAC', '1', 'Cash', NULL, 'takeout', 'walk_in', '120.00', '0.00', '120.00', NULL, 'completed', NULL, NULL, NULL, '2026-04-03 21:04:41', '2026-04-03 21:05:49'),
  ('46', 'SKD-20260403-614E39', '1', '1', NULL, 'takeout', 'walk_in', '50.00', '0.00', '50.00', NULL, 'completed', NULL, NULL, NULL, '2026-04-03 21:12:38', '2026-04-03 22:17:53'),
  ('48', 'SKD-20260403-08E89B', '1', 'hshuw', '1', 'dine_in', 'walk_in', '120.00', '0.00', '120.00', NULL, 'completed', NULL, NULL, NULL, '2026-04-03 21:15:28', '2026-04-03 21:17:41'),
  ('50', 'SKD-20260403-E82935', '1', 'hshuw', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', '10.00', 'voided', 'Customer left without paying', NULL, NULL, '2026-04-03 21:16:14', NULL),
  ('53', 'SKD-20260403-3C3C5D', '1', '999', '1', 'dine_in', 'walk_in', '90.00', '0.00', '90.00', NULL, 'cancelled', NULL, 'Customer changed mind', NULL, '2026-04-03 21:31:31', NULL),
  ('55', 'SKD-20260403-719F44', '1', 'sham', '1', 'dine_in', 'walk_in', '50.00', '0.00', '50.00', NULL, 'completed', NULL, NULL, NULL, '2026-04-03 22:19:51', '2026-04-03 22:20:54'),
  ('57', 'SKD-20260403-343ECE', '1', 'nwnwjns', '1', 'dine_in', 'walk_in', '50.00', '0.00', '50.00', '30.00', 'completed', NULL, NULL, NULL, '2026-04-03 22:21:23', '2026-04-03 22:22:44'),
  ('59', 'SKD-20260403-56E361', '1', 'mm', '1', 'dine_in', 'walk_in', '140.00', '0.00', '140.00', NULL, 'completed', NULL, NULL, NULL, '2026-04-03 22:23:33', '2026-04-03 22:26:42');

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
  `payment_status` enum('paid','pending','refunded') NOT NULL DEFAULT 'paid',
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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `payments` (`payment_id`, `order_id`, `payment_method`, `amount_paid`, `change_amount`, `payment_status`, `transaction_ref`, `cashier_id`, `processed_by`, `paid_at`) VALUES
  ('1', '12', 'cash', '100.00', '20.00', 'paid', NULL, '1', '1', '2026-04-01 18:08:24'),
  ('2', '16', 'cash', '1500.00', '200.00', 'paid', NULL, '1', '1', '2026-04-01 20:05:35'),
  ('3', '18', 'cash', '100.00', '50.00', 'paid', NULL, '1', '1', '2026-04-01 20:32:22'),
  ('4', '20', 'cash', '100.00', '50.00', 'paid', NULL, '1', '1', '2026-04-01 20:32:29'),
  ('5', '36', 'cash', '200.00', '30.00', 'paid', NULL, '1', '1', '2026-04-01 21:29:59'),
  ('6', '38', 'cash', '100.00', '50.00', 'paid', NULL, '1', '1', '2026-04-03 20:25:41'),
  ('7', '42', 'cash', '100.00', '50.00', 'paid', NULL, '1', '1', '2026-04-03 21:03:02'),
  ('8', '40', 'cash', '50.00', '0.00', 'paid', NULL, '1', '1', '2026-04-03 21:04:02'),
  ('9', '44', 'cash', '150.00', '30.00', 'paid', NULL, '1', '1', '2026-04-03 21:04:41'),
  ('10', '44', 'cash', '120.00', '0.00', 'paid', NULL, '1', '1', '2026-04-03 21:05:49'),
  ('11', '46', 'cash', '100.00', '50.00', 'paid', NULL, '1', '1', '2026-04-03 21:12:38'),
  ('12', '48', 'cash', '120.00', '0.00', 'paid', NULL, '1', '1', '2026-04-03 21:15:28'),
  ('13', '50', 'cash', '200.00', '60.00', 'paid', NULL, '1', '1', '2026-04-03 21:16:14'),
  ('14', '48', 'cash', '190.00', '70.00', 'paid', NULL, '1', '1', '2026-04-03 21:17:41'),
  ('15', '53', 'cash', '90.00', '0.00', 'paid', NULL, '1', '1', '2026-04-03 21:31:31'),
  ('16', '55', 'cash', '50.00', '0.00', 'paid', NULL, '1', '1', '2026-04-03 22:19:51'),
  ('17', '57', 'cash', '100.00', '0.00', 'paid', NULL, '1', '1', '2026-04-03 22:21:23'),
  ('18', '59', 'cash', '150.00', '10.00', 'paid', NULL, '1', '1', '2026-04-03 22:23:33');

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tables` (`table_id`, `table_name`, `capacity`, `status`, `current_order_id`, `updated_at`) VALUES
  ('1', 'Table 1', '4', 'available', NULL, '2026-04-03 22:26:42');

SET FOREIGN_KEY_CHECKS = 1;
