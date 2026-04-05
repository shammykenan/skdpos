-- ============================================================
-- Dine with Din Cafeteria POS — Database Backup
-- Generated : 2026-04-06 05:55:42 (Asia/Manila)
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categories` (`category_id`, `category_name`, `created_at`) VALUES
  ('1', 'Coffee', '2026-04-06 03:37:55'),
  ('2', 'Non-Coffee', '2026-04-06 03:37:55'),
  ('3', 'Add-ons', '2026-04-06 03:37:55');

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
  PRIMARY KEY (`attempt_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  PRIMARY KEY (`reason_id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `order_item_reasons` (`reason_id`, `reason_type`, `reason_text`, `sort_order`, `is_active`, `created_at`) VALUES
  ('1', 'cancel_order', 'Customer changed mind', '1', '1', '2026-04-06 03:38:30'),
  ('2', 'cancel_order', 'Customer did not show up', '2', '1', '2026-04-06 03:38:30'),
  ('3', 'cancel_order', 'Duplicate order', '3', '1', '2026-04-06 03:38:30'),
  ('4', 'cancel_order', 'Wrong items ordered', '4', '1', '2026-04-06 03:38:30'),
  ('5', 'cancel_order', 'Item(s) unavailable', '5', '1', '2026-04-06 03:38:30'),
  ('6', 'cancel_order', 'Payment issue', '6', '1', '2026-04-06 03:38:30'),
  ('7', 'cancel_order', 'Other', '99', '1', '2026-04-06 03:38:30'),
  ('8', 'void_order', 'Customer left without paying', '1', '1', '2026-04-06 03:38:30'),
  ('9', 'void_order', 'Wrong order prepared', '2', '1', '2026-04-06 03:38:30'),
  ('10', 'void_order', 'Quality issue — item rejected', '3', '1', '2026-04-06 03:38:30'),
  ('11', 'void_order', 'Order prepared in error', '4', '1', '2026-04-06 03:38:30'),
  ('12', 'void_order', 'Customer refused order', '5', '1', '2026-04-06 03:38:30'),
  ('13', 'void_order', 'Other', '99', '1', '2026-04-06 03:38:30'),
  ('14', 'cancel_item', 'Customer changed mind', '1', '1', '2026-04-06 03:38:30'),
  ('15', 'cancel_item', 'Wrong item ordered', '2', '1', '2026-04-06 03:38:30'),
  ('16', 'cancel_item', 'Item unavailable', '3', '1', '2026-04-06 03:38:30'),
  ('17', 'cancel_item', 'Duplicate entry', '4', '1', '2026-04-06 03:38:30'),
  ('18', 'cancel_item', 'Customer allergy / dietary restriction', '5', '1', '2026-04-06 03:38:30'),
  ('19', 'cancel_item', 'Other', '99', '1', '2026-04-06 03:38:30'),
  ('20', 'void_item', 'Customer refused item', '1', '1', '2026-04-06 03:38:30'),
  ('21', 'void_item', 'Quality issue — item rejected', '2', '1', '2026-04-06 03:38:30'),
  ('22', 'void_item', 'Wrong item prepared', '3', '1', '2026-04-06 03:38:30'),
  ('23', 'void_item', 'Item prepared in error', '4', '1', '2026-04-06 03:38:30'),
  ('24', 'void_item', 'Customer left without paying', '5', '1', '2026-04-06 03:38:30'),
  ('25', 'void_item', 'Other', '99', '1', '2026-04-06 03:38:30');

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `units` (`unit_id`, `unit_code`, `unit_label`, `unit_icon`, `is_active`, `created_at`) VALUES
  ('1', 'shot', 'Shot', 'fa-coffee', '1', '2026-04-06 03:37:55'),
  ('2', 'ml', 'Milliliters', 'fa-tint', '1', '2026-04-06 03:37:55'),
  ('3', 'g', 'Grams', 'fa-weight', '1', '2026-04-06 03:37:55');

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`user_id`, `username`, `password_hash`, `full_name`, `email`, `role`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
  ('1', 'admin', 'auhhued', 'Admin123', 'dinshammykenan012@gmail.com', 'admin', '1', NULL, '2026-04-06 03:39:19', NULL);

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
  KEY `category_id` (`category_id`),
  CONSTRAINT `menu_items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `menu_items` (`item_id`, `item_name`, `description`, `base_price`, `cost_price`, `category_id`, `image_url`, `is_available`, `created_at`) VALUES
  ('1', 'Espresso', 'Strong single shot coffee', '80.00', '25.00', '1', NULL, '1', '2026-04-06 03:37:55'),
  ('2', 'Americano', 'Espresso with hot water', '100.00', '30.00', '1', NULL, '1', '2026-04-06 03:37:55'),
  ('3', 'Latte', 'Espresso with steamed milk', '120.00', '40.00', '1', NULL, '1', '2026-04-06 03:37:55'),
  ('4', 'Cappuccino', 'Espresso with milk foam', '130.00', '45.00', '1', NULL, '1', '2026-04-06 03:37:55'),
  ('5', 'Mocha', 'Chocolate flavored latte', '150.00', '50.00', '1', NULL, '1', '2026-04-06 03:37:55'),
  ('6', 'Caramel Macchiato', 'Milk with espresso and caramel', '160.00', '60.00', '1', NULL, '1', '2026-04-06 03:37:55'),
  ('7', 'Iced Latte', 'Cold espresso with milk and ice', '130.00', '45.00', '1', NULL, '1', '2026-04-06 03:37:55');

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
  KEY `unit_id` (`unit_id`),
  CONSTRAINT `ingredients_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`unit_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `ingredients` (`ingredient_id`, `ingredient_name`, `stock_quantity`, `unit_id`, `low_stock_alert`, `last_updated`) VALUES
  ('1', 'Espresso Shot', '480.00', '1', '50.00', '2026-04-06 05:54:55'),
  ('2', 'Fresh Milk', '7000.00', '2', '1000.00', '2026-04-06 05:54:55'),
  ('3', 'Water', '20000.00', '2', '2000.00', '2026-04-06 03:37:55'),
  ('4', 'Chocolate Syrup', '2400.00', '2', '300.00', '2026-04-06 05:54:55'),
  ('5', 'Caramel Syrup', '3000.00', '2', '300.00', '2026-04-06 03:37:55'),
  ('6', 'Sugar', '5000.00', '3', '500.00', '2026-04-06 03:37:55'),
  ('7', 'Whipped Cream', '2000.00', '2', '200.00', '2026-04-06 03:37:55'),
  ('8', 'Ice', '10000.00', '3', '1000.00', '2026-04-06 03:37:55');

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
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `activity_logs` (`log_id`, `user_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
  ('1', '1', 'void_entire_order', '{\"order_id\":3,\"order_number\":\"SKD-20260406-516E8F\",\"reason\":\"Customer left without paying\",\"performed_by\":1,\"items\":[{\"item\":\"Mocha\",\"qty\":2,\"base_cost\":110,\"addon_cost\":0,\"waste_cost\":110}],\"total_waste_cost\":110,\"refund_amount\":300,\"refund_method\":\"cash\",\"refund_note\":null,\"refund_id\":1,\"max_refund\":300,\"total_paid\":300,\"refund_cap\":300,\"stock_note\":\"Ingredients already consumed \\u2014 stock NOT restored. Written off as waste.\"}', '192.168.100.11', '2026-04-06 03:43:49'),
  ('2', '1', 'mark_served', '{\"order_id\":5,\"order_number\":\"SKD-20260406-5AABE0\",\"performed_by\":1,\"note\":\"Order marked as served. Payment was collected at POS.\"}', '192.168.100.11', '2026-04-06 03:45:14'),
  ('3', '1', 'process_refund', '{\"order_id\":5,\"order_number\":\"SKD-20260406-5AABE0\",\"refund_type\":\"full\",\"reason\":\"Customer complaint\",\"refund_method\":\"cash\",\"refunds\":[{\"refund_id\":2,\"order_item_id\":null,\"amount\":300}],\"total_refunded\":300,\"already_refunded\":0,\"order_total\":300}', '192.168.100.11', '2026-04-06 03:45:22'),
  ('4', '1', 'void_entire_order', '{\"order_id\":7,\"order_number\":\"SKD-20260406-064799\",\"reason\":\"Customer left without paying\",\"performed_by\":1,\"items\":[{\"item\":\"Mocha\",\"qty\":1,\"base_cost\":55,\"addon_cost\":0,\"waste_cost\":55}],\"total_waste_cost\":55,\"refund_amount\":150,\"refund_method\":\"cash\",\"refund_note\":null,\"refund_id\":3,\"max_refund\":150,\"total_paid\":150,\"refund_cap\":150,\"stock_note\":\"Ingredients already consumed \\u2014 stock NOT restored. Written off as waste.\"}', '192.168.100.11', '2026-04-06 04:09:40'),
  ('5', '1', 'mark_served', '{\"order_id\":9,\"order_number\":\"SKD-20260406-956DCE\",\"performed_by\":1,\"note\":\"Order marked as served. Payment was collected at POS.\"}', '192.168.100.11', '2026-04-06 04:10:12'),
  ('6', '1', 'process_refund', '{\"order_id\":9,\"order_number\":\"SKD-20260406-956DCE\",\"refund_type\":\"full\",\"reason\":\"Customer complaint\",\"refund_method\":\"cash\",\"refunds\":[{\"refund_id\":4,\"order_item_id\":null,\"amount\":300}],\"total_refunded\":300,\"already_refunded\":0,\"order_total\":300}', '192.168.100.11', '2026-04-06 04:10:25'),
  ('7', '1', 'mark_served', '{\"order_id\":11,\"order_number\":\"SKD-20260406-076748\",\"performed_by\":1,\"note\":\"Order marked as served. Payment was collected at POS.\"}', '192.168.100.11', '2026-04-06 04:19:02'),
  ('8', '1', 'process_refund', '{\"order_id\":11,\"order_number\":\"SKD-20260406-076748\",\"refund_type\":\"full\",\"reason\":\"Quality issue\",\"refund_method\":\"cash\",\"refunds\":[{\"refund_id\":5,\"order_item_id\":null,\"amount\":300}],\"total_refunded\":300,\"already_refunded\":0,\"order_total\":300}', '192.168.100.11', '2026-04-06 04:19:19'),
  ('9', '1', 'mark_served', '{\"order_id\":13,\"order_number\":\"SKD-20260406-CCCA2A\",\"performed_by\":1,\"note\":\"Order marked as served. Payment was collected at POS.\"}', '192.168.100.11', '2026-04-06 04:26:03'),
  ('10', '1', 'process_refund', '{\"order_id\":13,\"order_number\":\"SKD-20260406-CCCA2A\",\"refund_type\":\"full\",\"reason\":\"Quality issue\",\"refund_method\":\"cash\",\"refunds\":[{\"refund_id\":6,\"order_item_id\":null,\"amount\":300}],\"total_refunded\":300,\"already_refunded\":0,\"order_total\":300}', '192.168.100.11', '2026-04-06 04:26:11'),
  ('11', '1', 'mark_served', '{\"order_id\":15,\"order_number\":\"SKD-20260406-26316B\",\"performed_by\":1,\"note\":\"Order marked as served. Payment was collected at POS.\"}', '192.168.100.11', '2026-04-06 04:29:24'),
  ('12', '1', 'process_refund', '{\"order_id\":15,\"order_number\":\"SKD-20260406-26316B\",\"refund_type\":\"full\",\"reason\":\"Wrong item served\",\"refund_method\":\"cash\",\"refunds\":[{\"refund_id\":7,\"order_item_id\":null,\"amount\":300}],\"total_refunded\":300,\"already_refunded\":0,\"order_total\":300,\"waste_items\":[{\"order_item_id\":\"8\",\"item\":\"Mocha\",\"qty\":2,\"base_cost\":100,\"addon_cost\":0,\"waste_cost\":100}],\"total_waste_cost\":100,\"stock_note\":\"Ingredients already consumed \\u2014 stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-04-06 04:29:31'),
  ('13', '1', 'void_entire_order', '{\"order_id\":17,\"order_number\":\"SKD-20260406-F409D7\",\"reason\":\"Quality issue \\u2014 item rejected\",\"performed_by\":1,\"items\":[{\"item\":\"Mocha\",\"qty\":1,\"base_cost\":50,\"addon_cost\":0,\"waste_cost\":50}],\"total_waste_cost\":50,\"refund_amount\":150,\"refund_method\":\"cash\",\"refund_note\":null,\"refund_id\":8,\"max_refund\":150,\"total_paid\":150,\"refund_cap\":150,\"stock_note\":\"Ingredients already consumed \\u2014 stock NOT restored. Written off as waste.\"}', '192.168.100.11', '2026-04-06 04:30:53'),
  ('14', '1', 'mark_served', '{\"order_id\":19,\"order_number\":\"SKD-20260406-D0CE81\",\"performed_by\":1,\"note\":\"Order marked as served. Payment was collected at POS.\"}', '192.168.100.11', '2026-04-06 04:37:59'),
  ('15', '1', 'process_refund', '{\"order_id\":19,\"order_number\":\"SKD-20260406-D0CE81\",\"refund_type\":\"full\",\"reason\":\"Quality issue\",\"refund_method\":\"cash\",\"refunds\":[{\"refund_id\":9,\"order_item_id\":null,\"amount\":150}],\"total_refunded\":null,\"already_refunded\":0,\"order_total\":150,\"waste_items\":[{\"order_item_id\":\"10\",\"item\":\"Mocha\",\"qty\":1,\"base_cost\":50,\"addon_cost\":0,\"waste_cost\":50}],\"total_waste_cost\":50,\"stock_note\":\"Ingredients already consumed \\u2014 stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-04-06 04:38:27'),
  ('16', '1', 'mark_served', '{\"order_id\":21,\"order_number\":\"SKD-20260406-9E2C12\",\"performed_by\":1,\"note\":\"Order marked as served. Payment was collected at POS.\"}', '192.168.100.11', '2026-04-06 04:39:30'),
  ('17', '1', 'process_refund', '{\"order_id\":21,\"order_number\":\"SKD-20260406-9E2C12\",\"refund_type\":\"full\",\"reason\":\"Wrong item served\",\"refund_method\":\"cash\",\"refunds\":[{\"refund_id\":10,\"order_item_id\":null,\"amount\":150}],\"total_refunded\":null,\"already_refunded\":0,\"order_total\":150,\"waste_items\":[{\"order_item_id\":\"11\",\"item\":\"Mocha\",\"qty\":1,\"base_cost\":50,\"addon_cost\":0,\"waste_cost\":50}],\"total_waste_cost\":50,\"stock_note\":\"Ingredients already consumed \\u2014 stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-04-06 04:39:39'),
  ('18', '1', 'mark_served', '{\"order_id\":23,\"order_number\":\"SKD-20260406-FE3191\",\"performed_by\":1,\"note\":\"Order marked as served. Payment was collected at POS.\"}', '192.168.100.11', '2026-04-06 04:42:21'),
  ('19', '1', 'process_refund', '{\"order_id\":23,\"order_number\":\"SKD-20260406-FE3191\",\"refund_type\":\"full\",\"reason\":\"Wrong item served\",\"refund_method\":\"cash\",\"refunds\":[{\"refund_id\":11,\"order_item_id\":null,\"amount\":150}],\"total_refunded\":150,\"already_refunded\":0,\"order_total\":150,\"waste_items\":[{\"order_item_id\":\"12\",\"item\":\"Mocha\",\"qty\":1,\"base_cost\":50,\"addon_cost\":0,\"waste_cost\":50}],\"total_waste_cost\":50,\"stock_note\":\"Ingredients already consumed \\u2014 stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-04-06 04:42:30'),
  ('20', '1', 'mark_served', '{\"order_id\":25,\"order_number\":\"SKD-20260406-9807C3\",\"performed_by\":1,\"note\":\"Order marked as served. Payment was collected at POS.\"}', '192.168.100.11', '2026-04-06 04:47:14'),
  ('21', '1', 'process_refund', '{\"order_id\":25,\"order_number\":\"SKD-20260406-9807C3\",\"refund_type\":\"full\",\"reason\":\"Quality issue\",\"refund_method\":\"cash\",\"refunds\":[{\"refund_id\":12,\"order_item_id\":null,\"amount\":150}],\"total_refunded\":150,\"already_refunded\":0,\"order_total\":150,\"waste_items\":[{\"order_item_id\":\"13\",\"item\":\"Mocha\",\"qty\":1,\"base_cost\":50,\"addon_cost\":0,\"waste_cost\":50}],\"total_waste_cost\":50,\"stock_note\":\"Ingredients already consumed \\u2014 stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-04-06 04:47:27'),
  ('22', '1', 'mark_served', '{\"order_id\":27,\"order_number\":\"SKD-20260406-3C5514\",\"performed_by\":1,\"note\":\"Order marked as served. Payment was collected at POS.\"}', '192.168.100.11', '2026-04-06 05:54:58'),
  ('23', '1', 'process_refund', '{\"order_id\":27,\"order_number\":\"SKD-20260406-3C5514\",\"refund_type\":\"full\",\"reason\":\"Customer complaint\",\"refund_method\":\"cash\",\"refunds\":[{\"refund_id\":13,\"order_item_id\":null,\"amount\":300}],\"total_refunded\":300,\"already_refunded\":0,\"order_total\":300,\"waste_items\":[{\"order_item_id\":\"14\",\"item\":\"Mocha\",\"qty\":2,\"base_cost\":100,\"addon_cost\":0,\"waste_cost\":100}],\"total_waste_cost\":100,\"stock_note\":\"Ingredients already consumed \\u2014 stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-04-06 05:55:05');

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
  KEY `user_id` (`user_id`),
  CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
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
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`addon_id`),
  KEY `ingredient_id` (`ingredient_id`),
  CONSTRAINT `addons_ibfk_1` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`ingredient_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `addons` (`addon_id`, `addon_name`, `price`, `ingredient_id`, `quantity_used`, `is_active`, `last_updated`) VALUES
  ('1', 'Extra Espresso Shot', '30.00', '1', '1.00', '1', '2026-04-06 03:37:55'),
  ('2', 'Add Milk', '20.00', '2', '50.00', '1', '2026-04-06 03:37:55'),
  ('3', 'Add Chocolate Syrup', '25.00', '4', '20.00', '1', '2026-04-06 03:37:55'),
  ('4', 'Add Caramel Syrup', '25.00', '5', '20.00', '1', '2026-04-06 03:37:55'),
  ('5', 'Add Whipped Cream', '30.00', '7', '30.00', '1', '2026-04-06 03:37:55'),
  ('6', 'Extra Sugar', '10.00', '6', '10.00', '1', '2026-04-06 03:37:55'),
  ('7', 'Extra Ice', '5.00', '8', '50.00', '1', '2026-04-06 03:37:55');

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
  KEY `variant_id` (`variant_id`),
  KEY `ingredient_id` (`ingredient_id`),
  CONSTRAINT `recipes_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `menu_items` (`item_id`) ON DELETE CASCADE,
  CONSTRAINT `recipes_ibfk_2` FOREIGN KEY (`variant_id`) REFERENCES `menu_variants` (`variant_id`) ON DELETE CASCADE,
  CONSTRAINT `recipes_ibfk_3` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`ingredient_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `recipes` (`recipe_id`, `item_id`, `variant_id`, `ingredient_id`, `quantity_required`, `variant_id_key`) VALUES
  ('1', '1', NULL, '1', '1.00', '0'),
  ('2', '2', NULL, '1', '1.00', '0'),
  ('3', '2', NULL, '3', '120.00', '0'),
  ('4', '3', NULL, '1', '1.00', '0'),
  ('5', '3', NULL, '2', '180.00', '0'),
  ('6', '4', NULL, '1', '1.00', '0'),
  ('7', '4', NULL, '2', '150.00', '0'),
  ('8', '5', NULL, '1', '1.00', '0'),
  ('9', '5', NULL, '2', '150.00', '0'),
  ('10', '5', NULL, '4', '30.00', '0'),
  ('11', '6', NULL, '1', '1.00', '0'),
  ('12', '6', NULL, '2', '180.00', '0'),
  ('13', '6', NULL, '5', '20.00', '0'),
  ('14', '7', NULL, '1', '1.00', '0'),
  ('15', '7', NULL, '2', '150.00', '0'),
  ('16', '7', NULL, '8', '100.00', '0');

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
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `waste_cost` decimal(10,2) DEFAULT NULL,
  `item_notes` text DEFAULT NULL,
  `void_reason` varchar(255) DEFAULT NULL,
  `cancel_reason` varchar(255) DEFAULT NULL,
  `status` enum('pending','preparing','ready','completed','cancelled','voided') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`order_item_id`),
  KEY `order_id` (`order_id`),
  KEY `item_id` (`item_id`),
  KEY `variant_id` (`variant_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `menu_items` (`item_id`),
  CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`variant_id`) REFERENCES `menu_variants` (`variant_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `order_items` (`order_item_id`, `order_id`, `item_id`, `variant_id`, `item_name_snapshot`, `variant_name_snapshot`, `quantity`, `unit_price`, `subtotal`, `waste_cost`, `item_notes`, `void_reason`, `cancel_reason`, `status`) VALUES
  ('2', '3', '5', NULL, 'Mocha', NULL, '2', '150.00', '300.00', '110.00', NULL, 'Customer left without paying', NULL, 'voided'),
  ('3', '5', '5', NULL, 'Mocha', NULL, '2', '150.00', '300.00', NULL, NULL, NULL, NULL, 'completed'),
  ('4', '7', '5', NULL, 'Mocha', NULL, '1', '150.00', '150.00', '55.00', NULL, 'Customer left without paying', NULL, 'voided'),
  ('5', '9', '5', NULL, 'Mocha', NULL, '2', '150.00', '300.00', NULL, NULL, NULL, NULL, 'completed'),
  ('6', '11', '5', NULL, 'Mocha', NULL, '2', '150.00', '300.00', NULL, NULL, NULL, NULL, 'completed'),
  ('7', '13', '5', NULL, 'Mocha', NULL, '2', '150.00', '300.00', NULL, NULL, NULL, NULL, 'completed'),
  ('8', '15', '5', NULL, 'Mocha', NULL, '2', '150.00', '300.00', '100.00', NULL, NULL, NULL, 'completed'),
  ('9', '17', '5', NULL, 'Mocha', NULL, '1', '150.00', '150.00', '50.00', NULL, 'Quality issue — item rejected', NULL, 'voided'),
  ('10', '19', '5', NULL, 'Mocha', NULL, '1', '150.00', '150.00', '50.00', NULL, NULL, NULL, 'completed'),
  ('11', '21', '5', NULL, 'Mocha', NULL, '1', '150.00', '150.00', '50.00', NULL, NULL, NULL, 'completed'),
  ('12', '23', '5', NULL, 'Mocha', NULL, '1', '150.00', '150.00', '50.00', NULL, NULL, NULL, 'completed'),
  ('13', '25', '5', NULL, 'Mocha', NULL, '1', '150.00', '150.00', '50.00', NULL, NULL, NULL, 'completed'),
  ('14', '27', '5', NULL, 'Mocha', NULL, '2', '150.00', '300.00', '100.00', NULL, NULL, NULL, 'completed');

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
  KEY `cashier_id` (`cashier_id`),
  KEY `table_id` (`table_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`table_id`) REFERENCES `tables` (`table_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `orders` (`order_id`, `order_number`, `cashier_id`, `customer_name`, `table_id`, `order_type`, `order_source`, `subtotal`, `discount`, `total_amount`, `waste_total`, `status`, `void_reason`, `cancel_reason`, `draft_cart`, `created_at`, `completed_at`) VALUES
  ('3', 'SKD-20260406-516E8F', '1', 'sham', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', '110.00', 'voided', 'Customer left without paying', NULL, NULL, '2026-04-06 03:43:33', NULL),
  ('5', 'SKD-20260406-5AABE0', '1', 'Shammy', NULL, 'takeout', 'walk_in', '300.00', '0.00', '300.00', NULL, 'completed', NULL, NULL, NULL, '2026-04-06 03:44:53', '2026-04-06 03:45:14'),
  ('7', 'SKD-20260406-064799', '1', 'sham', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', '55.00', 'voided', 'Customer left without paying', NULL, NULL, '2026-04-06 04:09:04', NULL),
  ('9', 'SKD-20260406-956DCE', '1', 'sham', NULL, 'takeout', 'walk_in', '300.00', '0.00', '300.00', NULL, 'completed', NULL, NULL, NULL, '2026-04-06 04:10:01', '2026-04-06 04:10:12'),
  ('11', 'SKD-20260406-076748', '1', 'Shammy', NULL, 'takeout', 'walk_in', '300.00', '0.00', '300.00', NULL, 'completed', NULL, NULL, NULL, '2026-04-06 04:18:40', '2026-04-06 04:19:02'),
  ('13', 'SKD-20260406-CCCA2A', '1', 'Shammy', NULL, 'takeout', 'walk_in', '300.00', '0.00', '300.00', NULL, 'completed', NULL, NULL, NULL, '2026-04-06 04:25:48', '2026-04-06 04:26:03'),
  ('15', 'SKD-20260406-26316B', '1', 'Sham', NULL, 'takeout', 'walk_in', '300.00', '0.00', '300.00', '100.00', 'completed', NULL, NULL, NULL, '2026-04-06 04:29:06', '2026-04-06 04:29:24'),
  ('17', 'SKD-20260406-F409D7', '1', 'sham', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', '50.00', 'voided', 'Quality issue — item rejected', NULL, NULL, '2026-04-06 04:30:39', NULL),
  ('19', 'SKD-20260406-D0CE81', '1', 'sham', NULL, 'takeout', 'walk_in', '150.00', '0.00', '150.00', '50.00', 'completed', NULL, NULL, NULL, '2026-04-06 04:37:49', '2026-04-06 04:37:59'),
  ('21', 'SKD-20260406-9E2C12', '1', 'sham', NULL, 'takeout', 'walk_in', '150.00', '0.00', '150.00', '50.00', 'completed', NULL, NULL, NULL, '2026-04-06 04:39:21', '2026-04-06 04:39:30'),
  ('23', 'SKD-20260406-FE3191', '1', 'sham', NULL, 'takeout', 'walk_in', '150.00', '0.00', '150.00', '50.00', 'completed', NULL, NULL, NULL, '2026-04-06 04:42:07', '2026-04-06 04:42:21'),
  ('25', 'SKD-20260406-9807C3', '1', 'sham', NULL, 'takeout', 'walk_in', '150.00', '0.00', '150.00', '50.00', 'completed', NULL, NULL, NULL, '2026-04-06 04:47:05', '2026-04-06 04:47:14'),
  ('27', 'SKD-20260406-3C5514', '1', 'sham', NULL, 'takeout', 'walk_in', '300.00', '0.00', '300.00', '100.00', 'completed', NULL, NULL, NULL, '2026-04-06 05:54:43', '2026-04-06 05:54:58');

-- ----------------------------------------------------------
-- Table: `payments`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `payment_method` enum('cash') NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_status` enum('paid','pending','refunded') NOT NULL DEFAULT 'paid',
  `transaction_ref` varchar(100) DEFAULT NULL,
  `change_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cashier_id` int(11) NOT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `paid_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`payment_id`),
  KEY `order_id` (`order_id`),
  KEY `cashier_id` (`cashier_id`),
  KEY `processed_by` (`processed_by`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`processed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `payments` (`payment_id`, `order_id`, `payment_method`, `amount_paid`, `payment_status`, `transaction_ref`, `change_amount`, `cashier_id`, `processed_by`, `paid_at`) VALUES
  ('1', '3', 'cash', '300.00', 'paid', NULL, '0.00', '1', '1', '2026-04-06 03:43:33'),
  ('2', '5', 'cash', '300.00', 'paid', NULL, '0.00', '1', '1', '2026-04-06 03:44:53'),
  ('3', '7', 'cash', '150.00', 'paid', NULL, '0.00', '1', '1', '2026-04-06 04:09:04'),
  ('4', '9', 'cash', '300.00', 'paid', NULL, '0.00', '1', '1', '2026-04-06 04:10:01'),
  ('5', '11', 'cash', '300.00', 'paid', NULL, '0.00', '1', '1', '2026-04-06 04:18:40'),
  ('6', '13', 'cash', '300.00', 'paid', NULL, '0.00', '1', '1', '2026-04-06 04:25:48'),
  ('7', '15', 'cash', '300.00', 'paid', NULL, '0.00', '1', '1', '2026-04-06 04:29:06'),
  ('8', '17', 'cash', '150.00', 'paid', NULL, '0.00', '1', '1', '2026-04-06 04:30:39'),
  ('9', '19', 'cash', '150.00', 'paid', NULL, '0.00', '1', '1', '2026-04-06 04:37:49'),
  ('10', '21', 'cash', '150.00', 'paid', NULL, '0.00', '1', '1', '2026-04-06 04:39:21'),
  ('11', '23', 'cash', '150.00', 'paid', NULL, '0.00', '1', '1', '2026-04-06 04:42:07'),
  ('12', '25', 'cash', '150.00', 'paid', NULL, '0.00', '1', '1', '2026-04-06 04:47:05'),
  ('13', '27', 'cash', '300.00', 'paid', NULL, '0.00', '1', '1', '2026-04-06 05:54:43');

-- ----------------------------------------------------------
-- Table: `refunds`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `refunds`;
CREATE TABLE `refunds` (
  `refund_id` int(11) NOT NULL AUTO_INCREMENT,
  `refund_type` enum('void_order','void_item','post_sale') NOT NULL DEFAULT 'post_sale',
  `order_id` int(11) NOT NULL,
  `order_item_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `refund_method` enum('cash') NOT NULL DEFAULT 'cash',
  `note` varchar(200) DEFAULT NULL,
  `processed_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`refund_id`),
  KEY `order_id` (`order_id`),
  KEY `order_item_id` (`order_item_id`),
  KEY `processed_by` (`processed_by`),
  CONSTRAINT `refunds_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  CONSTRAINT `refunds_ibfk_2` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`order_item_id`) ON DELETE SET NULL,
  CONSTRAINT `refunds_ibfk_3` FOREIGN KEY (`processed_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `refunds` (`refund_id`, `refund_type`, `order_id`, `order_item_id`, `amount`, `reason`, `refund_method`, `note`, `processed_by`, `created_at`) VALUES
  ('1', 'void_order', '3', NULL, '300.00', 'Customer left without paying', 'cash', NULL, '1', '2026-04-06 03:43:49'),
  ('2', 'post_sale', '5', NULL, '300.00', 'Customer complaint', 'cash', NULL, '1', '2026-04-06 03:45:22'),
  ('3', 'void_order', '7', NULL, '150.00', 'Customer left without paying', 'cash', NULL, '1', '2026-04-06 04:09:40'),
  ('4', 'post_sale', '9', NULL, '300.00', 'Customer complaint', 'cash', NULL, '1', '2026-04-06 04:10:25'),
  ('5', 'post_sale', '11', NULL, '300.00', 'Quality issue', 'cash', NULL, '1', '2026-04-06 04:19:19'),
  ('6', 'post_sale', '13', NULL, '300.00', 'Quality issue', 'cash', NULL, '1', '2026-04-06 04:26:11'),
  ('7', 'post_sale', '15', NULL, '300.00', 'Wrong item served', 'cash', NULL, '1', '2026-04-06 04:29:31'),
  ('8', 'void_order', '17', NULL, '150.00', 'Quality issue — item rejected', 'cash', NULL, '1', '2026-04-06 04:30:53'),
  ('9', 'post_sale', '19', NULL, '150.00', 'Quality issue', 'cash', NULL, '1', '2026-04-06 04:38:27'),
  ('10', 'post_sale', '21', NULL, '150.00', 'Wrong item served', 'cash', NULL, '1', '2026-04-06 04:39:39'),
  ('11', 'post_sale', '23', NULL, '150.00', 'Wrong item served', 'cash', NULL, '1', '2026-04-06 04:42:30'),
  ('12', 'post_sale', '25', NULL, '150.00', 'Quality issue', 'cash', NULL, '1', '2026-04-06 04:47:27'),
  ('13', 'post_sale', '27', NULL, '300.00', 'Customer complaint', 'cash', NULL, '1', '2026-04-06 05:55:05');

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
  KEY `current_order_id` (`current_order_id`),
  CONSTRAINT `tables_ibfk_1` FOREIGN KEY (`current_order_id`) REFERENCES `orders` (`order_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET FOREIGN_KEY_CHECKS = 1;
