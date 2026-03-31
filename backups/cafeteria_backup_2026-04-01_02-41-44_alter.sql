-- ============================================================
-- Dine with Din Cafeteria POS — Database Backup
-- Generated : 2026-04-01 02:41:44 (Asia/Manila)
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
  UNIQUE KEY `category_name` (`category_name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categories` (`category_id`, `category_name`, `created_at`) VALUES
  ('1', 'Milk Tea', '2026-03-29 01:05:27'),
  ('2', 'Desserts', '2026-03-29 01:05:27');

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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  ('1', 'cancel_order', 'Customer changed mind', '1', '1', '2026-03-31 05:18:53'),
  ('2', 'cancel_order', 'Customer did not show up', '2', '1', '2026-03-31 05:18:53'),
  ('3', 'cancel_order', 'Duplicate order', '3', '1', '2026-03-31 05:18:53'),
  ('4', 'cancel_order', 'Wrong items ordered', '4', '1', '2026-03-31 05:18:53'),
  ('5', 'cancel_order', 'Item(s) unavailable', '5', '1', '2026-03-31 05:18:53'),
  ('6', 'cancel_order', 'Payment issue', '6', '1', '2026-03-31 05:18:53'),
  ('7', 'cancel_order', 'Other', '99', '1', '2026-03-31 05:18:53'),
  ('8', 'void_order', 'Customer left without paying', '1', '1', '2026-03-31 05:18:55'),
  ('9', 'void_order', 'Wrong order prepared', '2', '1', '2026-03-31 05:18:55'),
  ('10', 'void_order', 'Quality issue — item rejected', '3', '1', '2026-03-31 05:18:55'),
  ('11', 'void_order', 'Order prepared in error', '4', '1', '2026-03-31 05:18:55'),
  ('12', 'void_order', 'Customer refused order', '5', '1', '2026-03-31 05:18:55'),
  ('13', 'void_order', 'Other', '99', '1', '2026-03-31 05:18:55'),
  ('14', 'cancel_item', 'Customer changed mind', '1', '1', '2026-03-31 05:18:55'),
  ('15', 'cancel_item', 'Wrong item ordered', '2', '1', '2026-03-31 05:18:55'),
  ('16', 'cancel_item', 'Item unavailable', '3', '1', '2026-03-31 05:18:55'),
  ('17', 'cancel_item', 'Duplicate entry', '4', '1', '2026-03-31 05:18:55'),
  ('18', 'cancel_item', 'Customer allergy / dietary restriction', '5', '1', '2026-03-31 05:18:55'),
  ('19', 'cancel_item', 'Other', '99', '1', '2026-03-31 05:18:55'),
  ('20', 'void_item', 'Customer refused item', '1', '1', '2026-03-31 05:18:55'),
  ('21', 'void_item', 'Quality issue — item rejected', '2', '1', '2026-03-31 05:18:55'),
  ('22', 'void_item', 'Wrong item prepared', '3', '1', '2026-03-31 05:18:55'),
  ('23', 'void_item', 'Item prepared in error', '4', '1', '2026-03-31 05:18:55'),
  ('24', 'void_item', 'Customer left without paying', '5', '1', '2026-03-31 05:18:55'),
  ('25', 'void_item', 'Other', '99', '1', '2026-03-31 05:18:55');

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
  UNIQUE KEY `unit_code` (`unit_code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `units` (`unit_id`, `unit_code`, `unit_label`, `unit_icon`, `is_active`, `created_at`) VALUES
  ('1', 'g', 'grams', 'fa-box', '1', '2026-03-29 01:05:27'),
  ('2', 'ml', 'milliliters', 'fa-box', '1', '2026-03-29 01:05:27'),
  ('3', 'pcs', 'pieces', 'fa-box', '1', '2026-03-29 01:05:27');

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
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`user_id`, `username`, `password_hash`, `full_name`, `email`, `role`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
  ('1', 'admin', '$2y$10$iwyVIsVb.vDSEVVGidRwI.TmJz4TILUsMksp0cKqfg7jGsOznyKCC', 'Admin', 'dinshammykenan012@gmail.com', 'admin', '1', '2026-04-01 01:39:46', '2026-03-29 00:27:29', '2026-04-01 01:39:46'),
  ('2', 'cashier', '$2y$10$UQ48lD/kOcCqrYA2YJ7QKOhfnysgAbPRUaVTquJiAq4tRxooZ.k8.', 'Cashier', 'dinshammykenan999@gmail.com', 'cashier', '1', '2026-03-31 05:28:43', '2026-03-29 22:47:05', '2026-03-31 05:28:43');

-- ----------------------------------------------------------
-- Table: `menu_items`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `menu_items`;
CREATE TABLE `menu_items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `cost_price` decimal(10,2) NOT NULL,
  `category_id` int(11) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`item_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `menu_items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `menu_items` (`item_id`, `item_name`, `description`, `base_price`, `cost_price`, `category_id`, `image_url`, `is_available`, `created_at`) VALUES
  ('1', 'Classic Milk Tea', 'Sweet milk tea with pearls', '80.00', '40.00', '1', NULL, '1', '2026-03-29 01:05:27'),
  ('2', 'Chocolate Milk Tea', 'Chocolate flavored milk tea', '90.00', '50.00', '1', NULL, '1', '2026-03-29 01:05:27'),
  ('3', 'Waffle', 'Freshly made waffle', '60.00', '30.00', '2', NULL, '1', '2026-03-29 01:05:27'),
  ('4', 'Chocolate Cake Slice', 'Moist chocolate cake', '120.00', '70.00', '2', NULL, '1', '2026-03-29 01:05:27');

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
  UNIQUE KEY `ingredient_name` (`ingredient_name`),
  KEY `unit_id` (`unit_id`),
  CONSTRAINT `ingredients_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`unit_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `ingredients` (`ingredient_id`, `ingredient_name`, `stock_quantity`, `unit_id`, `low_stock_alert`, `last_updated`) VALUES
  ('1', 'Tea', '879.00', '1', '10.00', '2026-04-01 02:13:10'),
  ('2', 'Milk Powder', '1080.00', '1', '10.00', '2026-04-01 02:25:16'),
  ('3', 'Sugar', '2157.00', '1', '10.00', '2026-04-01 02:25:16'),
  ('4', 'Ice', '2550.00', '1', '10.00', '2026-04-01 02:13:10'),
  ('5', 'Tapioca Pearls', '830.00', '1', '10.00', '2026-04-01 02:13:10'),
  ('6', 'All Purpose Flour', '1040.00', '1', '10.00', '2026-04-01 02:25:16'),
  ('7', 'Egg', '42.00', '3', '10.00', '2026-04-01 02:25:16'),
  ('8', 'Butter', '360.00', '1', '10.00', '2026-04-01 02:25:16'),
  ('9', 'Chocolate Syrup', '300.00', '2', '10.00', '2026-04-01 02:25:06'),
  ('10', 'Whipped Cream', '446.00', '2', '10.00', '2026-04-01 02:25:06'),
  ('11', 'Condensed Milk', '828.00', '2', '10.00', '2026-04-01 02:25:06');

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
) ENGINE=InnoDB AUTO_INCREMENT=86 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `activity_logs` (`log_id`, `user_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
  ('1', '1', 'void_order_item', '{\"order_item_id\":10,\"order_id\":22,\"item\":\"Chocolate Cake Slice\",\"qty\":1,\"waste_cost\":70,\"note\":\"Ingredients consumed \\u2014 not restored. Cost written off as waste.\"}', '192.168.100.11', '2026-03-30 21:58:59'),
  ('2', '1', 'auto_cancel_order', '{\"order_id\":22,\"reason\":\"All items were cancelled or voided.\"}', '192.168.100.11', '2026-03-30 21:58:59'),
  ('3', '1', 'void_entire_order', '{\"order_id\":26,\"order_number\":\"SKD-20260330-BE4A43\",\"items\":[{\"item\":\"Waffle\",\"qty\":1,\"waste_cost\":30},{\"item\":\"Waffle\",\"qty\":1,\"waste_cost\":30}],\"total_waste_cost\":60,\"note\":\"Entire order voided \\u2014 all items were ready. Ingredients consumed, cost written off as waste.\"}', '192.168.100.11', '2026-03-30 22:21:08'),
  ('4', '1', 'void_entire_order', '{\"order_id\":30,\"order_number\":\"SKD-20260330-E218EF\",\"items\":[{\"item\":\"Waffle\",\"qty\":1,\"waste_cost\":30},{\"item\":\"Waffle\",\"qty\":1,\"waste_cost\":30}],\"total_waste_cost\":60,\"note\":\"Entire order voided \\u2014 all items were ready. Ingredients consumed, cost written off as waste.\"}', '192.168.100.11', '2026-03-30 22:21:17'),
  ('5', '1', 'void_entire_order', '{\"order_id\":34,\"order_number\":\"SKD-20260330-90FA59\",\"items\":[{\"item\":\"Waffle\",\"qty\":1,\"waste_cost\":30}],\"total_waste_cost\":30,\"note\":\"Entire order voided \\u2014 all items were ready. Ingredients consumed, cost written off as waste.\"}', '192.168.100.11', '2026-03-30 22:21:20'),
  ('6', '1', 'void_entire_order', '{\"order_id\":36,\"order_number\":\"SKD-20260330-9BD5CB\",\"items\":[{\"item\":\"Classic Milk Tea\",\"qty\":1,\"waste_cost\":0}],\"total_waste_cost\":0,\"note\":\"Entire order voided \\u2014 all items were ready. Ingredients consumed, cost written off as waste.\"}', '192.168.100.11', '2026-03-30 22:21:23'),
  ('7', '2', 'auto_cancel_order', '{\"order_id\":42,\"reason\":\"All items were cancelled or voided.\"}', '192.168.100.11', '2026-03-31 00:15:32'),
  ('8', '2', 'auto_cancel_order', '{\"order_id\":44,\"reason\":\"All items were cancelled or voided.\"}', '192.168.100.11', '2026-03-31 00:17:06'),
  ('9', '2', 'auto_cancel_order', '{\"order_id\":46,\"reason\":\"All items were cancelled or voided.\"}', '192.168.100.11', '2026-03-31 00:28:10'),
  ('10', '1', 'void_order_item', '{\"order_item_id\":27,\"order_id\":48,\"item\":\"Waffle\",\"qty\":1,\"waste_cost\":30,\"note\":\"Ingredients consumed \\u2014 not restored. Cost written off as waste.\"}', '192.168.100.11', '2026-03-31 01:30:21'),
  ('11', '1', 'auto_cancel_order', '{\"order_id\":48,\"reason\":\"All items were cancelled or voided.\"}', '192.168.100.11', '2026-03-31 01:30:21'),
  ('12', '1', 'void_order_item', '{\"order_item_id\":28,\"order_id\":50,\"item\":\"Waffle\",\"qty\":1,\"waste_cost\":30,\"note\":\"Ingredients consumed \\u2014 not restored. Cost written off as waste.\"}', '192.168.100.11', '2026-03-31 01:47:29'),
  ('13', '1', 'auto_close_order', '{\"order_id\":50,\"final_status\":\"voided\",\"reason\":\"All items were cancelled or voided.\"}', '192.168.100.11', '2026-03-31 01:47:29'),
  ('14', '1', 'void_order_item', '{\"order_item_id\":31,\"order_id\":52,\"item\":\"Classic Milk Tea\",\"qty\":1,\"waste_cost\":0,\"note\":\"Ingredients consumed \\u2014 not restored. Cost written off as waste.\"}', '192.168.100.11', '2026-03-31 01:50:10'),
  ('15', '1', 'auto_close_order', '{\"order_id\":54,\"final_status\":\"cancelled\",\"reason\":\"All items were cancelled or voided.\"}', '192.168.100.11', '2026-03-31 01:51:09'),
  ('16', '1', 'void_entire_order', '{\"order_id\":58,\"order_number\":\"SKD-20260331-76A0B5\",\"items\":[{\"item\":\"Waffle\",\"qty\":1,\"waste_cost\":30},{\"item\":\"Waffle\",\"qty\":1,\"waste_cost\":30}],\"total_waste_cost\":60,\"note\":\"Entire order voided \\u2014 all items were ready. Ingredients consumed, cost written off as waste.\"}', '192.168.100.11', '2026-03-31 01:52:36'),
  ('17', '1', 'void_order_item', '{\"order_item_id\":39,\"order_id\":60,\"item\":\"Waffle\",\"qty\":1,\"waste_cost\":30,\"note\":\"Ingredients consumed \\u2014 not restored. Cost written off as waste.\"}', '192.168.100.11', '2026-03-31 01:58:48'),
  ('18', '1', 'void_order_item', '{\"order_item_id\":30,\"order_id\":52,\"item\":\"Waffle\",\"qty\":1,\"reason\":\"wrong item\",\"performed_by\":1,\"waste_cost\":30,\"note\":\"Ingredients consumed \\u2014 not restored. Cost written off as waste.\"}', '192.168.100.11', '2026-03-31 02:27:19'),
  ('19', '1', 'auto_close_order', '{\"order_id\":52,\"final_status\":\"voided\",\"reason\":\"All items were cancelled or voided.\"}', '192.168.100.11', '2026-03-31 02:27:19'),
  ('20', '1', 'cancel_order_item', '{\"order_item_id\":41,\"order_id\":64,\"item\":\"Waffle\",\"qty\":1,\"reason\":\"wrong item\",\"performed_by\":1}', '192.168.100.11', '2026-03-31 02:34:22'),
  ('21', '1', 'void_order_item', '{\"order_item_id\":43,\"order_id\":64,\"item\":\"Waffle\",\"qty\":1,\"reason\":\"idkh\",\"performed_by\":1,\"waste_cost\":30,\"note\":\"Ingredients consumed \\u2014 not restored. Cost written off as waste.\"}', '192.168.100.11', '2026-03-31 02:42:05'),
  ('22', '1', 'void_order_item', '{\"order_item_id\":45,\"order_id\":66,\"item\":\"Chocolate Cake Slice\",\"qty\":1,\"reason\":\"nothing\",\"performed_by\":1,\"waste_cost\":70,\"note\":\"Ingredients consumed \\u2014 not restored. Cost written off as waste.\"}', '192.168.100.11', '2026-03-31 02:46:14'),
  ('23', '1', 'cancel_order_item', '{\"order_item_id\":46,\"order_id\":66,\"item\":\"Chocolate Cake Slice\",\"qty\":1,\"reason\":\"hduhdu\",\"performed_by\":1}', '192.168.100.11', '2026-03-31 02:46:22'),
  ('24', '1', 'void_order_item', '{\"order_item_id\":33,\"order_id\":56,\"item\":\"Waffle\",\"qty\":1,\"reason\":\"no info\",\"performed_by\":1,\"waste_cost\":30,\"note\":\"Ingredients consumed \\u2014 not restored. Cost written off as waste.\"}', '192.168.100.11', '2026-03-31 03:05:10'),
  ('25', '1', 'auto_close_order', '{\"order_id\":56,\"final_status\":\"voided\",\"reason\":\"All items were cancelled or voided.\"}', '192.168.100.11', '2026-03-31 03:05:10'),
  ('26', '1', 'void_order_item', '{\"order_item_id\":38,\"order_id\":60,\"item\":\"Waffle\",\"qty\":1,\"reason\":\"sss\",\"performed_by\":1,\"waste_cost\":30,\"note\":\"Ingredients consumed \\u2014 not restored. Cost written off as waste.\"}', '192.168.100.11', '2026-03-31 03:05:30'),
  ('27', '1', 'auto_close_order', '{\"order_id\":60,\"final_status\":\"voided\",\"reason\":\"All items were cancelled or voided.\"}', '192.168.100.11', '2026-03-31 03:05:30'),
  ('28', '1', 'void_order_item', '{\"order_item_id\":44,\"order_id\":66,\"item\":\"Chocolate Cake Slice\",\"qty\":1,\"reason\":\"ww\",\"performed_by\":1,\"waste_cost\":70,\"note\":\"Ingredients consumed \\u2014 not restored. Cost written off as waste.\"}', '192.168.100.11', '2026-03-31 03:05:38'),
  ('29', '1', 'auto_close_order', '{\"order_id\":66,\"final_status\":\"voided\",\"reason\":\"All items were cancelled or voided.\"}', '192.168.100.11', '2026-03-31 03:05:38'),
  ('30', '1', 'void_order_item', '{\"order_item_id\":42,\"order_id\":64,\"item\":\"Waffle\",\"qty\":1,\"reason\":\"wwww\",\"performed_by\":1,\"waste_cost\":30,\"note\":\"Ingredients consumed \\u2014 not restored. Cost written off as waste.\"}', '192.168.100.11', '2026-03-31 03:05:46'),
  ('31', '1', 'auto_close_order', '{\"order_id\":64,\"final_status\":\"voided\",\"reason\":\"All items were cancelled or voided.\"}', '192.168.100.11', '2026-03-31 03:05:46'),
  ('32', '1', 'cancel_order_item', '{\"order_item_id\":49,\"order_id\":70,\"item\":\"Waffle\",\"qty\":1,\"reason\":\"Change of mind\",\"performed_by\":1}', '192.168.100.11', '2026-03-31 03:11:01'),
  ('33', '1', 'void_order_item', '{\"order_item_id\":50,\"order_id\":70,\"item\":\"Waffle\",\"qty\":1,\"reason\":\"Dont want anymore\",\"performed_by\":1,\"waste_cost\":30,\"note\":\"Ingredients consumed \\u2014 not restored. Cost written off as waste.\"}', '192.168.100.11', '2026-03-31 03:11:14'),
  ('34', '1', 'void_entire_order', '{\"order_id\":62,\"order_number\":\"SKD-20260331-6B2D07\",\"reason\":\"ok\",\"performed_by\":1,\"items\":[{\"item\":\"Waffle\",\"qty\":1,\"waste_cost\":30}],\"total_waste_cost\":30,\"note\":\"Entire order voided \\u2014 all items were ready. Ingredients consumed, cost written off as waste.\"}', '192.168.100.11', '2026-03-31 03:13:15'),
  ('35', '1', 'void_order_item', '{\"order_item_id\":52,\"order_id\":74,\"item\":\"Waffle\",\"qty\":1,\"reason\":\"void reason\",\"performed_by\":1,\"waste_cost\":30,\"note\":\"Ingredients consumed \\u2014 not restored. Cost written off as waste.\"}', '192.168.100.11', '2026-03-31 03:18:13'),
  ('36', '1', 'auto_close_order', '{\"order_id\":74,\"final_status\":\"voided\",\"reason\":\"All items were cancelled or voided.\"}', '192.168.100.11', '2026-03-31 03:18:13'),
  ('37', '1', 'cancel_order_item', '{\"order_item_id\":53,\"order_id\":76,\"item\":\"Waffle\",\"qty\":1,\"reason\":\"cancel reason\",\"performed_by\":1}', '192.168.100.11', '2026-03-31 03:19:28'),
  ('38', '1', 'void_order_item', '{\"order_item_id\":54,\"order_id\":76,\"item\":\"Waffle\",\"qty\":1,\"reason\":\"void pala to\",\"performed_by\":1,\"waste_cost\":30,\"note\":\"Ingredients consumed \\u2014 not restored. Cost written off as waste.\"}', '192.168.100.11', '2026-03-31 03:19:38'),
  ('39', '1', 'auto_close_order', '{\"order_id\":76,\"final_status\":\"voided\",\"reason\":\"All items were cancelled or voided.\"}', '192.168.100.11', '2026-03-31 03:19:38'),
  ('40', '2', 'cancel_order_item', '{\"order_item_id\":57,\"order_id\":80,\"item\":\"Waffle\",\"qty\":1,\"reason\":\"change of mind\",\"performed_by\":2}', '192.168.100.11', '2026-03-31 03:35:20'),
  ('41', '1', 'void_entire_order', '{\"order_id\":83,\"order_number\":\"SKD-20260331-766536\",\"reason\":\"sss\",\"performed_by\":1,\"items\":[{\"item\":\"Chocolate Cake Slice\",\"qty\":2,\"waste_cost\":140}],\"total_waste_cost\":140,\"note\":\"Entire order voided \\u2014 all items were ready. Ingredients consumed, cost written off as waste.\"}', '192.168.100.11', '2026-03-31 03:36:42'),
  ('42', '1', 'void_entire_order', '{\"order_id\":80,\"order_number\":\"SKD-20260331-8AD2B9\",\"reason\":\"hehe\",\"performed_by\":1,\"items\":[{\"item\":\"Waffle\",\"qty\":1,\"waste_cost\":30}],\"total_waste_cost\":30,\"note\":\"Entire order voided \\u2014 all items were ready. Ingredients consumed, cost written off as waste.\"}', '192.168.100.11', '2026-03-31 03:37:19'),
  ('43', '1', 'void_order_item', '{\"order_item_id\":60,\"order_id\":85,\"item\":\"Waffle\",\"qty\":1,\"reason\":\"Quality issue \\u2014 item rejected\",\"performed_by\":1,\"waste_cost\":30,\"stock_note\":\"Item was ready \\u2014 ingredients already consumed. Stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-03-31 04:34:13'),
  ('44', '1', 'cancel_order_item', '{\"order_item_id\":61,\"order_id\":85,\"item\":\"Waffle\",\"qty\":1,\"reason\":\"Wrong item ordered\",\"performed_by\":1,\"stock_note\":\"Item was pending \\u2014 no stock had been deducted. Nothing restored.\"}', '192.168.100.11', '2026-03-31 04:34:24'),
  ('45', '1', 'void_entire_order', '{\"order_id\":87,\"order_number\":\"SKD-20260331-23F0F1\",\"reason\":\"Wrong order prepared\",\"performed_by\":1,\"items\":[{\"item\":\"Waffle\",\"qty\":1,\"waste_cost\":30}],\"total_waste_cost\":30,\"stock_note\":\"Ingredients already consumed by kitchen \\u2014 stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-03-31 05:25:47'),
  ('46', '1', 'void_order_item', '{\"order_item_id\":63,\"order_id\":91,\"item\":\"Waffle\",\"qty\":1,\"reason\":\"Customer refused item\",\"performed_by\":1,\"waste_cost\":30,\"stock_note\":\"Item was ready \\u2014 ingredients already consumed. Stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-03-31 05:27:20'),
  ('47', '1', 'auto_close_order', '{\"order_id\":91,\"final_status\":\"voided\",\"reason\":\"All items were cancelled or voided.\"}', '192.168.100.11', '2026-03-31 05:27:20'),
  ('48', '1', 'void_entire_order', '{\"order_id\":93,\"order_number\":\"SKD-20260331-86E154\",\"reason\":\"Customer left without paying\",\"performed_by\":1,\"items\":[{\"item\":\"Waffle\",\"qty\":1,\"waste_cost\":30}],\"total_waste_cost\":30,\"stock_note\":\"Ingredients already consumed by kitchen \\u2014 stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-03-31 05:28:07'),
  ('49', '1', 'cancel_order', '{\"order_id\":95,\"order_number\":\"SKD-20260331-783348\",\"reason\":\"Customer changed mind\",\"performed_by\":1,\"stock_note\":\"Order was pending \\u2014 no stock had been deducted. Nothing restored.\"}', '192.168.100.11', '2026-03-31 10:02:58'),
  ('50', '1', 'cancel_order', '{\"order_id\":97,\"order_number\":\"SKD-20260331-27F678\",\"reason\":\"Item(s) unavailable\",\"performed_by\":1,\"stock_note\":\"Order was pending \\u2014 no stock had been deducted. Nothing restored.\"}', '192.168.100.11', '2026-03-31 10:04:04'),
  ('51', '1', 'cancel_order_item', '{\"order_item_id\":68,\"order_id\":99,\"item\":\"Waffle\",\"qty\":1,\"reason\":\"Duplicate entry\",\"performed_by\":1,\"stock_note\":\"Item was pending \\u2014 no stock had been deducted. Nothing restored.\"}', '192.168.100.11', '2026-03-31 10:07:24'),
  ('52', '1', 'cancel_order_item', '{\"order_item_id\":70,\"order_id\":101,\"item\":\"Waffle\",\"qty\":1,\"reason\":\"Customer changed mind\",\"performed_by\":1,\"stock_note\":\"Item was pending \\u2014 no stock had been deducted. Nothing restored.\"}', '192.168.100.11', '2026-03-31 10:08:21'),
  ('53', '1', 'cancel_order_item', '{\"order_item_id\":69,\"order_id\":101,\"item\":\"Waffle\",\"qty\":1,\"reason\":\"Customer allergy \\/ dietary restriction\",\"performed_by\":1,\"stock_note\":\"Item was pending \\u2014 no stock had been deducted. Nothing restored.\"}', '192.168.100.11', '2026-03-31 10:08:27'),
  ('54', '1', 'auto_close_order', '{\"order_id\":101,\"final_status\":\"cancelled\",\"reason\":\"All items were cancelled or voided.\"}', '192.168.100.11', '2026-03-31 10:08:27'),
  ('55', '1', 'cancel_order', '{\"order_id\":103,\"order_number\":\"SKD-20260331-F38639\",\"reason\":\"Customer changed mind\",\"performed_by\":1,\"stock_note\":\"Order was pending \\u2014 no stock had been deducted. Nothing restored.\"}', '192.168.100.11', '2026-03-31 10:09:11'),
  ('56', '1', 'cancel_order_item', '{\"order_item_id\":74,\"order_id\":105,\"item\":\"Waffle\",\"qty\":1,\"reason\":\"Item unavailable\",\"performed_by\":1,\"stock_note\":\"Item was pending \\u2014 no stock had been deducted. Nothing restored.\"}', '192.168.100.11', '2026-03-31 10:11:12'),
  ('57', '1', 'cancel_order_item', '{\"order_item_id\":75,\"order_id\":107,\"item\":\"Waffle\",\"qty\":1,\"reason\":\"Customer changed mind\",\"performed_by\":1,\"stock_note\":\"Item was pending \\u2014 no stock had been deducted. Nothing restored.\"}', '192.168.100.11', '2026-03-31 10:25:49'),
  ('58', '1', 'auto_close_order', '{\"order_id\":107,\"final_status\":\"cancelled\",\"reason\":\"All items were cancelled or voided.\"}', '192.168.100.11', '2026-03-31 10:25:49'),
  ('59', '1', 'void_entire_order', '{\"order_id\":105,\"order_number\":\"SKD-20260331-5590EC\",\"reason\":\"Customer left without paying\",\"performed_by\":1,\"items\":[{\"item\":\"Waffle\",\"qty\":1,\"waste_cost\":30}],\"total_waste_cost\":30,\"stock_note\":\"Ingredients already consumed by kitchen \\u2014 stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-03-31 10:32:29'),
  ('60', '1', 'cancel_order_item', '{\"order_item_id\":76,\"order_id\":109,\"item\":\"Waffle\",\"qty\":1,\"reason\":\"Wrong item ordered\",\"performed_by\":1,\"stock_note\":\"Item was pending \\u2014 no stock had been deducted. Nothing restored.\"}', '192.168.100.11', '2026-03-31 10:44:30'),
  ('61', '1', 'void_order_item', '{\"order_item_id\":77,\"order_id\":109,\"item\":\"Chocolate Cake Slice\",\"qty\":1,\"reason\":\"Quality issue \\u2014 item rejected\",\"performed_by\":1,\"waste_cost\":70,\"stock_note\":\"Item was ready \\u2014 ingredients already consumed. Stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-03-31 10:44:37'),
  ('62', '1', 'void_entire_order', '{\"order_id\":109,\"order_number\":\"SKD-20260331-1581F6\",\"reason\":\"Customer left without paying\",\"performed_by\":1,\"items\":[{\"item\":\"Classic Milk Tea\",\"qty\":1,\"waste_cost\":0}],\"total_waste_cost\":0,\"stock_note\":\"Ingredients already consumed by kitchen \\u2014 stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-03-31 10:44:56'),
  ('63', '1', 'cancel_order_item', '{\"order_item_id\":81,\"order_id\":111,\"item\":\"Waffle\",\"qty\":1,\"reason\":\"Item unavailable\",\"performed_by\":1,\"stock_note\":\"Item was pending \\u2014 no stock had been deducted. Nothing restored.\"}', '192.168.100.11', '2026-03-31 10:45:58'),
  ('64', '1', 'cancel_order_item', '{\"order_item_id\":80,\"order_id\":111,\"item\":\"Classic Milk Tea\",\"qty\":1,\"reason\":\"Customer changed mind\",\"performed_by\":1,\"stock_note\":\"Item was pending \\u2014 no stock had been deducted. Nothing restored.\"}', '192.168.100.11', '2026-03-31 10:46:08'),
  ('65', '1', 'cancel_order', '{\"order_id\":111,\"order_number\":\"SKD-20260331-E60624\",\"reason\":\"Customer changed mind\",\"performed_by\":1,\"stock_note\":\"Order was pending \\u2014 no stock had been deducted. Nothing restored.\"}', '192.168.100.11', '2026-03-31 10:46:21'),
  ('66', '1', 'void_order_item', '{\"order_item_id\":83,\"order_id\":113,\"item\":\"Waffle\",\"qty\":1,\"reason\":\"Customer refused item\",\"performed_by\":1,\"waste_cost\":30,\"stock_note\":\"Item was ready \\u2014 ingredients already consumed. Stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-03-31 10:47:04'),
  ('67', '1', 'void_entire_order', '{\"order_id\":113,\"order_number\":\"SKD-20260331-87A83E\",\"reason\":\"Order prepared in error\",\"performed_by\":1,\"items\":[{\"item\":\"Waffle\",\"qty\":1,\"waste_cost\":30}],\"total_waste_cost\":30,\"stock_note\":\"Ingredients already consumed by kitchen \\u2014 stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-03-31 10:47:18'),
  ('68', '1', 'void_entire_order', '{\"order_id\":117,\"order_number\":\"SKD-20260331-52BD66\",\"reason\":\"Customer left without paying\",\"performed_by\":1,\"items\":[{\"item\":\"Classic Milk Tea\",\"qty\":1,\"waste_cost\":0}],\"total_waste_cost\":0,\"stock_note\":\"Ingredients already consumed by kitchen \\u2014 stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-03-31 10:48:32'),
  ('69', '1', 'void_entire_order', '{\"order_id\":119,\"order_number\":\"SKD-20260331-1C65C3\",\"reason\":\"Customer left without paying\",\"performed_by\":1,\"items\":[{\"item\":\"Classic Milk Tea\",\"qty\":1,\"waste_cost\":0}],\"total_waste_cost\":0,\"stock_note\":\"Ingredients already consumed by kitchen \\u2014 stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-03-31 11:12:12'),
  ('70', '1', 'void_entire_order', '{\"order_id\":121,\"order_number\":\"SKD-20260331-D23088\",\"reason\":\"Customer left without paying\",\"performed_by\":1,\"items\":[{\"item\":\"Chocolate Milk Tea\",\"qty\":1,\"waste_cost\":0}],\"total_waste_cost\":0,\"stock_note\":\"Ingredients already consumed by kitchen \\u2014 stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-03-31 11:18:36'),
  ('71', '1', 'void_entire_order', '{\"order_id\":12,\"order_number\":\"SKD-20260329-44D161\",\"reason\":\"Customer left without paying\",\"performed_by\":1,\"items\":[{\"item\":\"Waffle\",\"qty\":1,\"waste_cost\":30}],\"total_waste_cost\":30,\"stock_note\":\"Ingredients already consumed by kitchen \\u2014 stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-04-01 01:41:09'),
  ('72', '1', 'void_entire_order', '{\"order_id\":123,\"order_number\":\"SKD-20260401-698E29\",\"reason\":\"Customer left without paying\",\"performed_by\":1,\"items\":[{\"item\":\"Classic Milk Tea\",\"qty\":1,\"waste_cost\":0}],\"total_waste_cost\":0,\"stock_note\":\"Ingredients already consumed by kitchen \\u2014 stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-04-01 01:47:33'),
  ('73', '1', 'void_entire_order', '{\"order_id\":125,\"order_number\":\"SKD-20260401-C1DAA8\",\"reason\":\"Customer left without paying\",\"performed_by\":1,\"items\":[{\"item\":\"Classic Milk Tea\",\"qty\":1,\"waste_cost\":0}],\"waste_total\":0,\"stock_note\":\"Ingredients already consumed by kitchen \\u2014 stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-04-01 01:51:37'),
  ('74', '1', 'void_entire_order', '{\"order_id\":127,\"order_number\":\"SKD-20260401-507228\",\"reason\":\"Customer refused order\",\"performed_by\":1,\"items\":[{\"item\":\"Classic Milk Tea\",\"qty\":1,\"waste_cost\":0}],\"total_waste_cost\":0,\"stock_note\":\"Ingredients already consumed by kitchen \\u2014 stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-04-01 02:03:33'),
  ('75', '1', 'void_entire_order', '{\"order_id\":129,\"order_number\":\"SKD-20260401-77485B\",\"reason\":\"Quality issue \\u2014 item rejected\",\"items\":[{\"item\":\"Chocolate Milk Tea\",\"qty\":1,\"waste_cost\":0}],\"total_waste_cost\":0}', '192.168.100.11', '2026-04-01 02:08:35'),
  ('76', '1', 'void_entire_order', '{\"order_id\":131,\"order_number\":\"SKD-20260401-F25619\",\"reason\":\"Customer left without paying\",\"performed_by\":1,\"items\":[{\"item\":\"Classic Milk Tea\",\"qty\":1,\"waste_cost\":40}],\"total_waste_cost\":40,\"stock_note\":\"Ingredients already consumed by kitchen \\u2014 stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-04-01 02:13:16'),
  ('77', '1', 'void_order_item', '{\"order_item_id\":94,\"order_id\":133,\"item\":\"Waffle\",\"qty\":1,\"reason\":\"Customer refused item\",\"performed_by\":1,\"waste_cost\":30,\"stock_note\":\"Item was ready \\u2014 ingredients already consumed. Stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-04-01 02:14:10'),
  ('78', '1', 'void_entire_order', '{\"order_id\":133,\"order_number\":\"SKD-20260401-595819\",\"reason\":\"Customer left without paying\",\"performed_by\":1,\"items\":[{\"item\":\"Waffle\",\"qty\":1,\"waste_cost\":30}],\"total_waste_cost\":30,\"stock_note\":\"Ingredients already consumed by kitchen \\u2014 stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-04-01 02:14:19'),
  ('79', '1', 'void_entire_order', '{\"order_id\":135,\"order_number\":\"SKD-20260401-AE15AA\",\"reason\":\"Wrong order prepared\",\"performed_by\":1,\"items\":[{\"item\":\"Waffle\",\"qty\":1,\"waste_cost\":30},{\"item\":\"Waffle\",\"qty\":1,\"waste_cost\":30}],\"total_waste_cost\":60,\"stock_note\":\"Ingredients already consumed by kitchen \\u2014 stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-04-01 02:15:05'),
  ('80', '1', 'void_order_item', '{\"order_item_id\":98,\"order_id\":137,\"item\":\"Waffle\",\"qty\":1,\"reason\":\"Quality issue \\u2014 item rejected\",\"performed_by\":1,\"waste_cost\":30,\"stock_note\":\"Item was ready \\u2014 ingredients already consumed. Stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-04-01 02:17:37'),
  ('81', '1', 'void_entire_order', '{\"order_id\":137,\"order_number\":\"SKD-20260401-DB3B8D\",\"reason\":\"Quality issue \\u2014 item rejected\",\"performed_by\":1,\"items\":[{\"item\":\"Waffle\",\"qty\":1,\"waste_cost\":30}],\"total_waste_cost\":30,\"stock_note\":\"Ingredients already consumed by kitchen \\u2014 stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-04-01 02:18:09'),
  ('82', '1', 'void_order_item', '{\"order_item_id\":99,\"order_id\":139,\"item\":\"Chocolate Cake Slice\",\"qty\":1,\"reason\":\"Customer refused item\",\"performed_by\":1,\"waste_cost\":70,\"stock_note\":\"Item was ready \\u2014 ingredients already consumed. Stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-04-01 02:20:16'),
  ('83', '1', 'void_entire_order', '{\"order_id\":139,\"order_number\":\"SKD-20260401-2460DF\",\"reason\":\"Customer left without paying\",\"performed_by\":1,\"items\":[{\"item\":\"Waffle\",\"qty\":1,\"waste_cost\":30}],\"total_waste_cost\":30,\"stock_note\":\"Ingredients already consumed by kitchen \\u2014 stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-04-01 02:20:22'),
  ('84', '1', 'void_order_item', '{\"order_item_id\":102,\"order_id\":141,\"item\":\"Chocolate Cake Slice\",\"qty\":1,\"reason\":\"Customer refused item\",\"performed_by\":1,\"waste_cost\":70,\"stock_note\":\"Item was ready \\u2014 ingredients already consumed. Stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-04-01 02:25:10'),
  ('85', '1', 'void_entire_order', '{\"order_id\":141,\"order_number\":\"SKD-20260401-C55CC2\",\"reason\":\"Customer left without paying\",\"performed_by\":1,\"items\":[{\"item\":\"Waffle\",\"qty\":1,\"waste_cost\":30}],\"total_waste_cost\":30,\"stock_note\":\"Ingredients already consumed by kitchen \\u2014 stock NOT restored. Cost written off as waste.\"}', '192.168.100.11', '2026-04-01 02:25:23');

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
  UNIQUE KEY `token` (`token`),
  KEY `idx_pr_token` (`token`),
  KEY `idx_pr_user` (`user_id`),
  KEY `idx_pr_ip_created` (`ip_address`,`created_at`),
  KEY `idx_pr_expires` (`expires_at`),
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
  UNIQUE KEY `uq_item_variant` (`item_id`,`variant_name`),
  KEY `idx_variant_item` (`item_id`),
  CONSTRAINT `menu_variants_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `menu_items` (`item_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `menu_variants` (`variant_id`, `item_id`, `variant_name`, `price`, `cost_price`, `is_active`, `created_at`, `updated_at`) VALUES
  ('1', '1', 'Small', '80.00', '0.00', '1', '2026-03-29 01:05:27', NULL),
  ('2', '1', 'Medium', '100.00', '0.00', '1', '2026-03-29 01:05:27', NULL),
  ('3', '1', 'Large', '120.00', '0.00', '1', '2026-03-29 01:05:27', NULL),
  ('4', '2', 'Small', '90.00', '0.00', '1', '2026-03-29 01:05:27', NULL),
  ('5', '2', 'Medium', '110.00', '0.00', '1', '2026-03-29 01:05:27', NULL),
  ('6', '2', 'Large', '130.00', '0.00', '1', '2026-03-29 01:05:27', NULL);

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
  PRIMARY KEY (`addon_id`),
  KEY `ingredient_id` (`ingredient_id`),
  CONSTRAINT `addons_ibfk_1` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`ingredient_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `addons` (`addon_id`, `addon_name`, `price`, `ingredient_id`, `quantity_used`, `is_active`) VALUES
  ('1', 'Extra Pearls', '15.00', '5', '20.00', '1'),
  ('2', 'Extra Chocolate', '20.00', '9', '15.00', '1'),
  ('3', 'Whipped Cream', '25.00', '10', '20.00', '1');

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
  KEY `ingredient_id` (`ingredient_id`),
  KEY `idx_recipe_item` (`item_id`),
  KEY `idx_recipe_variant` (`variant_id`),
  CONSTRAINT `recipes_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `menu_items` (`item_id`) ON DELETE CASCADE,
  CONSTRAINT `recipes_ibfk_2` FOREIGN KEY (`variant_id`) REFERENCES `menu_variants` (`variant_id`) ON DELETE CASCADE,
  CONSTRAINT `recipes_ibfk_3` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`ingredient_id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  ('42', '4', NULL, '11', '20.00', '0'),
  ('43', '3', NULL, '2', '10.00', '0'),
  ('44', '2', '4', '7', '99.00', '4'),
  ('45', '2', '4', '11', '12.00', '4'),
  ('46', '4', NULL, '3', '11.00', '0'),
  ('47', '4', NULL, '10', '1.00', '0'),
  ('48', '2', '4', '8', '1.00', '4'),
  ('49', '1', '1', '10', '9.00', '1'),
  ('50', '2', '4', '10', '1.00', '4');

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `order_item_addons` (`order_item_addon_id`, `order_item_id`, `addon_id`, `quantity`, `price`) VALUES
  ('1', '1', '1', '1', '15.00');

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
  `item_notes` text DEFAULT NULL,
  `void_reason` varchar(255) DEFAULT NULL,
  `cancel_reason` varchar(255) DEFAULT NULL,
  `waste_cost` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','preparing','ready','completed','cancelled','voided') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`order_item_id`),
  KEY `item_id` (`item_id`),
  KEY `idx_order_items_order` (`order_id`),
  KEY `idx_order_items_variant` (`variant_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `menu_items` (`item_id`),
  CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`variant_id`) REFERENCES `menu_variants` (`variant_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `order_items` (`order_item_id`, `order_id`, `item_id`, `variant_id`, `item_name_snapshot`, `variant_name_snapshot`, `quantity`, `unit_price`, `subtotal`, `item_notes`, `void_reason`, `cancel_reason`, `waste_cost`, `status`) VALUES
  ('1', '2', '1', '1', 'Classic Milk Tea', 'Small', '1', '80.00', '80.00', 'Less sugar', NULL, NULL, NULL, 'completed'),
  ('2', '4', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, NULL, NULL, NULL, 'completed'),
  ('3', '8', '2', '6', 'Chocolate Milk Tea', 'Large', '1', '130.00', '130.00', 'www', NULL, NULL, NULL, 'completed'),
  ('4', '10', '1', '1', 'Classic Milk Tea', 'Small', '1', '80.00', '80.00', 'jshwjshsbs', NULL, NULL, NULL, 'completed'),
  ('5', '12', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, 'Customer left without paying', NULL, '30.00', 'voided'),
  ('6', '14', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, NULL, NULL, NULL, 'ready'),
  ('7', '16', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, NULL, NULL, NULL, 'completed'),
  ('8', '18', '1', '2', 'Classic Milk Tea', 'Medium', '1', '100.00', '100.00', NULL, NULL, NULL, NULL, 'cancelled'),
  ('9', '20', '2', '6', 'Chocolate Milk Tea', 'Large', '1', '130.00', '130.00', NULL, NULL, NULL, NULL, 'cancelled'),
  ('10', '22', '4', NULL, 'Chocolate Cake Slice', NULL, '1', '120.00', '120.00', NULL, NULL, NULL, NULL, 'voided'),
  ('11', '24', '1', '2', 'Classic Milk Tea', 'Medium', '1', '100.00', '100.00', NULL, NULL, NULL, NULL, 'cancelled'),
  ('12', '26', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, NULL, NULL, NULL, 'voided'),
  ('13', '26', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', 'eeguhud', NULL, NULL, NULL, 'voided'),
  ('14', '28', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, NULL, NULL, NULL, 'cancelled'),
  ('15', '30', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, NULL, NULL, NULL, 'voided'),
  ('16', '30', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', 'ssss', NULL, NULL, NULL, 'voided'),
  ('17', '32', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, NULL, NULL, NULL, 'preparing'),
  ('18', '32', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', 'no egg', NULL, NULL, NULL, 'pending'),
  ('19', '34', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, NULL, NULL, NULL, 'voided'),
  ('20', '36', '1', '3', 'Classic Milk Tea', 'Large', '1', '120.00', '120.00', NULL, NULL, NULL, NULL, 'voided'),
  ('21', '38', '1', '3', 'Classic Milk Tea', 'Large', '1', '120.00', '120.00', NULL, NULL, NULL, NULL, 'cancelled'),
  ('22', '40', '2', '6', 'Chocolate Milk Tea', 'Large', '1', '130.00', '130.00', NULL, NULL, NULL, NULL, 'preparing'),
  ('23', '42', '2', '6', 'Chocolate Milk Tea', 'Large', '1', '130.00', '130.00', NULL, NULL, NULL, NULL, 'cancelled'),
  ('24', '44', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, NULL, NULL, NULL, 'cancelled'),
  ('25', '46', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, NULL, NULL, NULL, 'cancelled'),
  ('26', '46', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', 'jjjjj', NULL, NULL, NULL, 'cancelled'),
  ('27', '48', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, NULL, NULL, NULL, 'voided'),
  ('28', '50', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, NULL, NULL, NULL, 'voided'),
  ('29', '52', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, NULL, NULL, NULL, 'cancelled'),
  ('30', '52', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', 'h', NULL, NULL, NULL, 'voided'),
  ('31', '52', '1', '3', 'Classic Milk Tea', 'Large', '1', '120.00', '120.00', NULL, NULL, NULL, NULL, 'voided'),
  ('32', '54', '3', NULL, 'Waffle', NULL, '2', '60.00', '120.00', NULL, NULL, NULL, NULL, 'cancelled'),
  ('33', '56', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, NULL, NULL, NULL, 'voided'),
  ('34', '56', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', 'k', NULL, NULL, NULL, 'cancelled'),
  ('35', '58', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, NULL, NULL, NULL, 'voided'),
  ('36', '58', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', 'j', NULL, NULL, NULL, 'voided'),
  ('37', '60', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, NULL, NULL, NULL, 'cancelled'),
  ('38', '60', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', 'cancel', NULL, NULL, NULL, 'voided'),
  ('39', '60', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', 'void', NULL, NULL, NULL, 'voided'),
  ('40', '62', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, NULL, NULL, NULL, 'voided'),
  ('41', '64', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, NULL, NULL, NULL, 'cancelled'),
  ('42', '64', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', 'void', NULL, NULL, NULL, 'voided'),
  ('43', '64', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', 'cancel', NULL, NULL, NULL, 'voided'),
  ('44', '66', '4', NULL, 'Chocolate Cake Slice', NULL, '1', '120.00', '120.00', NULL, NULL, NULL, NULL, 'voided'),
  ('45', '66', '4', NULL, 'Chocolate Cake Slice', NULL, '1', '120.00', '120.00', 'void', NULL, NULL, NULL, 'voided'),
  ('46', '66', '4', NULL, 'Chocolate Cake Slice', NULL, '1', '120.00', '120.00', 'cancel', NULL, NULL, NULL, 'cancelled'),
  ('47', '68', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, NULL, NULL, NULL, 'cancelled'),
  ('48', '70', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, NULL, NULL, NULL, 'completed'),
  ('49', '70', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', 'cancel', NULL, NULL, NULL, 'cancelled'),
  ('50', '70', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', 'void', NULL, NULL, NULL, 'completed'),
  ('51', '72', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, NULL, NULL, NULL, 'cancelled'),
  ('52', '74', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, NULL, NULL, NULL, 'voided'),
  ('53', '76', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', 'cancel', NULL, NULL, NULL, 'cancelled'),
  ('54', '76', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', 'void', NULL, NULL, NULL, 'voided'),
  ('55', '78', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, NULL, NULL, NULL, 'cancelled'),
  ('56', '80', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', 'k', NULL, NULL, NULL, 'voided'),
  ('57', '80', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', 'n', NULL, NULL, NULL, 'cancelled'),
  ('58', '83', '4', NULL, 'Chocolate Cake Slice', NULL, '2', '120.00', '240.00', NULL, NULL, NULL, NULL, 'voided'),
  ('59', '85', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, NULL, NULL, NULL, 'completed'),
  ('60', '85', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', 'void', 'Quality issue — item rejected', NULL, NULL, 'voided'),
  ('61', '85', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', 'cancel', NULL, 'Wrong item ordered', NULL, 'cancelled'),
  ('62', '87', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, 'Wrong order prepared', NULL, NULL, 'voided'),
  ('63', '91', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, 'Customer refused item', NULL, NULL, 'voided'),
  ('64', '93', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, 'Customer left without paying', NULL, NULL, 'voided'),
  ('65', '95', '2', '6', 'Chocolate Milk Tea', 'Large', '1', '130.00', '130.00', NULL, NULL, 'Customer changed mind', NULL, 'cancelled'),
  ('66', '97', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, NULL, 'Item(s) unavailable', NULL, 'cancelled'),
  ('67', '99', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, NULL, NULL, NULL, 'completed'),
  ('68', '99', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', 'bbb', NULL, 'Duplicate entry', NULL, 'cancelled'),
  ('69', '101', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, NULL, 'Customer allergy / dietary restriction', NULL, 'cancelled'),
  ('70', '101', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', 'jj', NULL, 'Customer changed mind', NULL, 'cancelled'),
  ('71', '103', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, NULL, 'Customer changed mind', NULL, 'cancelled'),
  ('72', '103', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', 'mm', NULL, 'Customer changed mind', NULL, 'cancelled'),
  ('73', '105', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, 'Customer left without paying', NULL, NULL, 'voided'),
  ('74', '105', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', 'ko', NULL, 'Item unavailable', NULL, 'cancelled'),
  ('75', '107', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, NULL, 'Customer changed mind', NULL, 'cancelled'),
  ('76', '109', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, NULL, 'Wrong item ordered', NULL, 'cancelled'),
  ('77', '109', '4', NULL, 'Chocolate Cake Slice', NULL, '1', '120.00', '120.00', NULL, 'Quality issue — item rejected', NULL, NULL, 'voided'),
  ('78', '109', '1', '2', 'Classic Milk Tea', 'Medium', '1', '100.00', '100.00', NULL, 'Customer left without paying', NULL, NULL, 'voided'),
  ('79', '111', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, NULL, 'Customer changed mind', NULL, 'cancelled'),
  ('80', '111', '1', '2', 'Classic Milk Tea', 'Medium', '1', '100.00', '100.00', NULL, NULL, 'Customer changed mind', NULL, 'cancelled'),
  ('81', '111', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', 'j', NULL, 'Customer changed mind', NULL, 'cancelled'),
  ('82', '113', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, 'Order prepared in error', NULL, NULL, 'voided'),
  ('83', '113', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', 'm', 'Customer refused item', NULL, NULL, 'voided'),
  ('84', '115', '2', '4', 'Chocolate Milk Tea', 'Small', '1', '90.00', '90.00', 'jj', NULL, NULL, NULL, 'completed'),
  ('85', '117', '1', '1', 'Classic Milk Tea', 'Small', '1', '80.00', '80.00', NULL, 'Customer left without paying', NULL, NULL, 'voided'),
  ('86', '119', '1', '3', 'Classic Milk Tea', 'Large', '1', '120.00', '120.00', NULL, 'Customer left without paying', NULL, '0.00', 'voided'),
  ('87', '121', '2', '6', 'Chocolate Milk Tea', 'Large', '1', '130.00', '130.00', NULL, 'Customer left without paying', NULL, '0.00', 'voided'),
  ('88', '123', '1', '1', 'Classic Milk Tea', 'Small', '1', '80.00', '80.00', NULL, 'Customer left without paying', NULL, '0.00', 'voided'),
  ('89', '125', '1', '1', 'Classic Milk Tea', 'Small', '1', '80.00', '80.00', NULL, 'Customer left without paying', NULL, '0.00', 'voided'),
  ('90', '127', '1', '2', 'Classic Milk Tea', 'Medium', '1', '100.00', '100.00', NULL, 'Customer refused order', NULL, '0.00', 'voided'),
  ('91', '129', '2', '6', 'Chocolate Milk Tea', 'Large', '1', '130.00', '130.00', NULL, 'Quality issue — item rejected', NULL, '0.00', 'voided'),
  ('92', '131', '1', '2', 'Classic Milk Tea', 'Medium', '1', '100.00', '100.00', NULL, 'Customer left without paying', NULL, '40.00', 'voided'),
  ('93', '133', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, 'Customer left without paying', NULL, '30.00', 'voided'),
  ('94', '133', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', 'w', 'Customer refused item', NULL, '30.00', 'voided'),
  ('95', '135', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, 'Wrong order prepared', NULL, '30.00', 'voided'),
  ('96', '135', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', 'h', 'Wrong order prepared', NULL, '30.00', 'voided'),
  ('97', '137', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', 'void1', 'Quality issue — item rejected', NULL, '30.00', 'voided'),
  ('98', '137', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', 'void2', 'Quality issue — item rejected', NULL, '30.00', 'voided'),
  ('99', '139', '4', NULL, 'Chocolate Cake Slice', NULL, '1', '120.00', '120.00', NULL, 'Customer refused item', NULL, '70.00', 'voided'),
  ('100', '139', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, 'Customer left without paying', NULL, '30.00', 'voided'),
  ('101', '141', '3', NULL, 'Waffle', NULL, '1', '60.00', '60.00', NULL, 'Customer left without paying', NULL, '30.00', 'voided'),
  ('102', '141', '4', NULL, 'Chocolate Cake Slice', NULL, '1', '120.00', '120.00', NULL, 'Customer refused item', NULL, '70.00', 'voided');

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
  `status` enum('draft','pending','preparing','ready','completed','cancelled','voided') NOT NULL DEFAULT 'pending',
  `draft_cart` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`draft_cart`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  `void_reason` varchar(255) DEFAULT NULL,
  `waste_total` decimal(10,2) DEFAULT NULL,
  `cancel_reason` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `table_id` (`table_id`),
  KEY `idx_orders_cashier` (`cashier_id`),
  KEY `idx_orders_status` (`status`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`table_id`) REFERENCES `tables` (`table_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=142 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `orders` (`order_id`, `order_number`, `cashier_id`, `customer_name`, `table_id`, `order_type`, `order_source`, `subtotal`, `discount`, `total_amount`, `status`, `draft_cart`, `created_at`, `completed_at`, `void_reason`, `waste_total`, `cancel_reason`) VALUES
  ('2', 'SKD-20260329-1E8D4F', '2', 'Shammy', NULL, 'takeout', 'walk_in', '95.00', '0.00', '95.00', 'completed', NULL, '2026-03-29 23:09:37', '2026-03-29 23:10:09', NULL, NULL, NULL),
  ('4', 'SKD-20260329-C62AEF', '2', 'Shammy', NULL, 'takeout', 'walk_in', '60.00', '0.00', '60.00', 'completed', NULL, '2026-03-29 23:18:52', '2026-03-29 23:40:04', NULL, NULL, NULL),
  ('8', 'SKD-20260329-8DCA18', '1', 'www', NULL, 'takeout', 'walk_in', '130.00', '0.00', '130.00', 'completed', NULL, '2026-03-29 23:29:12', '2026-03-29 23:29:30', NULL, NULL, NULL),
  ('10', 'SKD-20260329-9A5974', '2', 'sham', NULL, 'takeout', 'walk_in', '80.00', '0.00', '80.00', 'completed', NULL, '2026-03-29 23:38:33', '2026-03-29 23:39:39', NULL, NULL, NULL),
  ('12', 'SKD-20260329-44D161', '2', 'sham', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', 'voided', NULL, '2026-03-29 23:51:32', NULL, 'Customer left without paying', '30.00', NULL),
  ('14', 'SKD-20260329-B9B953', '2', 'sham', NULL, 'takeout', 'walk_in', '60.00', '0.00', '60.00', 'ready', NULL, '2026-03-29 23:59:07', NULL, NULL, NULL, NULL),
  ('16', 'SKD-20260330-F1AB09', '2', 'sham', NULL, 'takeout', 'walk_in', '60.00', '0.00', '60.00', 'completed', NULL, '2026-03-30 00:11:43', '2026-03-30 00:12:30', NULL, NULL, NULL),
  ('18', 'SKD-20260330-8A6FE1', '1', 'sham', NULL, 'takeout', 'walk_in', '100.00', '0.00', '100.00', 'cancelled', NULL, '2026-03-30 00:36:40', NULL, NULL, NULL, NULL),
  ('20', 'SKD-20260330-006F7B', '1', 'shhshsh', NULL, 'takeout', 'walk_in', '130.00', '0.00', '130.00', 'cancelled', NULL, '2026-03-30 00:37:04', NULL, NULL, NULL, NULL),
  ('22', 'SKD-20260330-015A37', '1', 'sham', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', 'cancelled', NULL, '2026-03-30 00:37:20', NULL, NULL, NULL, NULL),
  ('24', 'SKD-20260330-D8E0D2', '1', 'Sham', '1', 'dine_in', 'walk_in', '100.00', '0.00', '100.00', 'cancelled', NULL, '2026-03-30 19:16:45', NULL, NULL, NULL, NULL),
  ('26', 'SKD-20260330-BE4A43', '1', 'sham', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', 'cancelled', NULL, '2026-03-30 19:32:11', NULL, NULL, NULL, NULL),
  ('28', 'SKD-20260330-F740EE', '1', 'ss', NULL, 'takeout', 'walk_in', '60.00', '0.00', '60.00', 'cancelled', NULL, '2026-03-30 19:33:19', NULL, NULL, NULL, NULL),
  ('30', 'SKD-20260330-E218EF', '1', 'sss', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', 'cancelled', NULL, '2026-03-30 19:50:06', NULL, NULL, NULL, NULL),
  ('32', 'SKD-20260330-A77008', '1', 'sample', NULL, 'takeout', 'walk_in', '120.00', '0.00', '120.00', 'preparing', NULL, '2026-03-30 20:33:14', NULL, NULL, NULL, NULL),
  ('34', 'SKD-20260330-90FA59', '1', 'ss', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', 'cancelled', NULL, '2026-03-30 21:57:29', NULL, NULL, NULL, NULL),
  ('36', 'SKD-20260330-9BD5CB', '2', 'hsn', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', 'cancelled', NULL, '2026-03-30 22:07:37', NULL, NULL, NULL, NULL),
  ('38', 'SKD-20260330-92613D', '2', 's', '1', 'dine_in', 'walk_in', '120.00', '0.00', '120.00', 'cancelled', NULL, '2026-03-30 22:09:13', NULL, NULL, NULL, NULL),
  ('40', 'SKD-20260330-5C2F96', '2', 'ww', NULL, 'takeout', 'walk_in', '130.00', '0.00', '130.00', 'preparing', NULL, '2026-03-30 22:23:17', NULL, NULL, NULL, NULL),
  ('42', 'SKD-20260330-892484', '2', 'ss', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', 'cancelled', NULL, '2026-03-30 22:23:52', NULL, NULL, NULL, NULL),
  ('44', 'SKD-20260331-AC39C9', '2', 'n', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', 'cancelled', NULL, '2026-03-31 00:16:58', NULL, NULL, NULL, NULL),
  ('46', 'SKD-20260331-C0A914', '2', 'j', '1', 'dine_in', 'walk_in', '0.00', '0.00', '0.00', 'cancelled', NULL, '2026-03-31 00:27:40', NULL, NULL, NULL, NULL),
  ('48', 'SKD-20260331-55274E', '1', 's', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', 'cancelled', NULL, '2026-03-31 00:37:57', NULL, NULL, NULL, NULL),
  ('50', 'SKD-20260331-9B6D0F', '1', 's', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', 'voided', NULL, '2026-03-31 01:47:05', NULL, NULL, NULL, NULL),
  ('52', 'SKD-20260331-654F6E', '1', 'j', '1', 'dine_in', 'walk_in', '0.00', '0.00', '0.00', 'voided', NULL, '2026-03-31 01:49:26', NULL, NULL, NULL, NULL),
  ('54', 'SKD-20260331-1093E2', '1', 'k', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', 'cancelled', NULL, '2026-03-31 01:50:57', NULL, NULL, NULL, NULL),
  ('56', 'SKD-20260331-6733F8', '1', 'k', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', 'voided', NULL, '2026-03-31 01:51:34', NULL, NULL, NULL, NULL),
  ('58', 'SKD-20260331-76A0B5', '1', 'h', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', 'voided', NULL, '2026-03-31 01:52:07', NULL, NULL, NULL, NULL),
  ('60', 'SKD-20260331-AEC59F', '1', 'test', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', 'voided', NULL, '2026-03-31 01:58:02', NULL, NULL, NULL, NULL),
  ('62', 'SKD-20260331-6B2D07', '2', 's', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', 'voided', NULL, '2026-03-31 02:01:26', NULL, 'ok', NULL, NULL),
  ('64', 'SKD-20260331-991D51', '1', 'hh', '1', 'dine_in', 'walk_in', '0.00', '0.00', '0.00', 'voided', NULL, '2026-03-31 02:34:01', NULL, NULL, NULL, NULL),
  ('66', 'SKD-20260331-3B7FC5', '1', 'test2', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', 'voided', NULL, '2026-03-31 02:45:55', NULL, NULL, NULL, NULL),
  ('68', 'SKD-20260331-DABF68', '1', 'j', NULL, 'takeout', 'walk_in', '60.00', '0.00', '60.00', 'cancelled', NULL, '2026-03-31 03:03:41', NULL, NULL, NULL, NULL),
  ('70', 'SKD-20260331-2C2DCA', '1', 'test3', NULL, 'takeout', 'walk_in', '60.00', '0.00', '60.00', 'completed', NULL, '2026-03-31 03:10:42', '2026-03-31 03:12:02', NULL, NULL, NULL),
  ('72', 'SKD-20260331-87CB7F', '1', 'hh', NULL, 'takeout', 'walk_in', '60.00', '0.00', '60.00', 'cancelled', NULL, '2026-03-31 03:13:28', NULL, NULL, NULL, NULL),
  ('74', 'SKD-20260331-E54CC2', '1', 's', '1', 'dine_in', 'walk_in', '0.00', '0.00', '0.00', 'voided', NULL, '2026-03-31 03:17:50', NULL, NULL, NULL, NULL),
  ('76', 'SKD-20260331-E02674', '1', 'gulo', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', 'voided', NULL, '2026-03-31 03:19:10', NULL, NULL, NULL, NULL),
  ('78', 'SKD-20260331-A6CD12', '1', 'cancel', NULL, 'takeout', 'walk_in', '60.00', '0.00', '60.00', 'cancelled', NULL, '2026-03-31 03:23:22', NULL, NULL, NULL, NULL),
  ('80', 'SKD-20260331-8AD2B9', '2', 'm', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', 'voided', NULL, '2026-03-31 03:30:00', NULL, 'hehe', NULL, NULL),
  ('83', 'SKD-20260331-766536', '2', 'd', '1', 'dine_in', 'walk_in', '0.00', '0.00', '0.00', 'voided', NULL, '2026-03-31 03:36:07', NULL, 'sss', NULL, NULL),
  ('85', 'SKD-20260331-066E97', '1', 's', NULL, 'takeout', 'walk_in', '60.00', '0.00', '60.00', 'completed', NULL, '2026-03-31 04:33:52', '2026-03-31 04:34:44', NULL, NULL, NULL),
  ('87', 'SKD-20260331-23F0F1', '1', 'yh', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', 'voided', NULL, '2026-03-31 04:35:30', NULL, 'Wrong order prepared', NULL, NULL),
  ('91', 'SKD-20260331-1836C7', '1', 'sham', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', 'voided', NULL, '2026-03-31 05:26:25', NULL, NULL, NULL, NULL),
  ('93', 'SKD-20260331-86E154', '1', 's', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', 'voided', NULL, '2026-03-31 05:27:52', NULL, 'Customer left without paying', NULL, NULL),
  ('95', 'SKD-20260331-783348', '1', 's', '1', 'dine_in', 'walk_in', '130.00', '0.00', '130.00', 'cancelled', NULL, '2026-03-31 10:02:47', NULL, NULL, NULL, 'Customer changed mind'),
  ('97', 'SKD-20260331-27F678', '1', 's', NULL, 'takeout', 'walk_in', '60.00', '0.00', '60.00', 'cancelled', NULL, '2026-03-31 10:03:14', NULL, NULL, NULL, 'Item(s) unavailable'),
  ('99', 'SKD-20260331-9C61C1', '1', 'm', '1', 'dine_in', 'walk_in', '60.00', '0.00', '60.00', 'completed', NULL, '2026-03-31 10:07:05', '2026-03-31 10:07:40', NULL, NULL, NULL),
  ('101', 'SKD-20260331-8076DF', '1', 'n', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', 'cancelled', NULL, '2026-03-31 10:08:08', NULL, NULL, NULL, NULL),
  ('103', 'SKD-20260331-F38639', '1', 'll', NULL, 'takeout', 'walk_in', '120.00', '0.00', '120.00', 'cancelled', NULL, '2026-03-31 10:09:03', NULL, NULL, NULL, 'Customer changed mind'),
  ('105', 'SKD-20260331-5590EC', '1', 'lo', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', 'voided', NULL, '2026-03-31 10:11:01', NULL, 'Customer left without paying', NULL, NULL),
  ('107', 'SKD-20260331-594137', '1', 'p', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', 'cancelled', NULL, '2026-03-31 10:25:41', NULL, NULL, NULL, NULL),
  ('109', 'SKD-20260331-1581F6', '1', 'kk', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', 'voided', NULL, '2026-03-31 10:44:17', NULL, 'Customer left without paying', NULL, NULL),
  ('111', 'SKD-20260331-E60624', '1', 'm', NULL, 'takeout', 'walk_in', '60.00', '0.00', '60.00', 'cancelled', NULL, '2026-03-31 10:45:34', NULL, NULL, NULL, 'Customer changed mind'),
  ('113', 'SKD-20260331-87A83E', '1', 'k', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', 'voided', NULL, '2026-03-31 10:46:48', NULL, 'Order prepared in error', NULL, NULL),
  ('115', 'SKD-20260331-4DC1EC', '1', 'kk', NULL, 'takeout', 'walk_in', '90.00', '0.00', '90.00', 'completed', NULL, '2026-03-31 10:47:48', '2026-03-31 10:48:07', NULL, NULL, NULL),
  ('117', 'SKD-20260331-52BD66', '1', 'p', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', 'voided', NULL, '2026-03-31 10:48:21', NULL, 'Customer left without paying', NULL, NULL),
  ('119', 'SKD-20260331-1C65C3', '1', 'v', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', 'voided', NULL, '2026-03-31 11:12:01', NULL, 'Customer left without paying', '0.00', NULL),
  ('121', 'SKD-20260331-D23088', '1', 's', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', 'voided', NULL, '2026-03-31 11:18:21', NULL, 'Customer left without paying', '0.00', NULL),
  ('123', 'SKD-20260401-698E29', '1', 'h', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', 'voided', NULL, '2026-04-01 01:47:02', NULL, 'Customer left without paying', '0.00', NULL),
  ('125', 'SKD-20260401-C1DAA8', '1', 's', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', 'voided', NULL, '2026-04-01 01:51:24', NULL, 'Customer left without paying', '0.00', NULL),
  ('127', 'SKD-20260401-507228', '1', 's', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', 'voided', NULL, '2026-04-01 02:03:17', NULL, 'Customer refused order', '0.00', NULL),
  ('129', 'SKD-20260401-77485B', '1', '3', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', 'voided', NULL, '2026-04-01 02:08:23', NULL, 'Quality issue — item rejected', '0.00', NULL),
  ('131', 'SKD-20260401-F25619', '1', 'r', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', 'voided', NULL, '2026-04-01 02:09:03', NULL, 'Customer left without paying', '40.00', NULL),
  ('133', 'SKD-20260401-595819', '1', '2', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', 'voided', NULL, '2026-04-01 02:13:57', NULL, 'Customer left without paying', '30.00', NULL),
  ('135', 'SKD-20260401-AE15AA', '1', 'q', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', 'voided', NULL, '2026-04-01 02:14:50', NULL, 'Wrong order prepared', '60.00', NULL),
  ('137', 'SKD-20260401-DB3B8D', '1', '1', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', 'voided', NULL, '2026-04-01 02:17:17', NULL, 'Quality issue — item rejected', '30.00', NULL),
  ('139', 'SKD-20260401-2460DF', '1', '3', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', 'voided', NULL, '2026-04-01 02:20:02', NULL, 'Customer left without paying', '30.00', NULL),
  ('141', 'SKD-20260401-C55CC2', '1', '1', NULL, 'takeout', 'walk_in', '0.00', '0.00', '0.00', 'voided', NULL, '2026-04-01 02:25:00', NULL, 'Customer left without paying', '30.00', NULL);

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
  KEY `order_id` (`order_id`),
  KEY `cashier_id` (`cashier_id`),
  KEY `processed_by` (`processed_by`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`processed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `payments` (`payment_id`, `order_id`, `payment_method`, `amount_paid`, `change_amount`, `payment_status`, `transaction_ref`, `cashier_id`, `processed_by`, `paid_at`) VALUES
  ('1', '2', 'cash', '100.00', '5.00', 'paid', NULL, '2', '2', '2026-03-29 23:10:09'),
  ('2', '8', 'cash', '150.00', '20.00', 'paid', NULL, '1', '1', '2026-03-29 23:29:30'),
  ('3', '10', 'cash', '100.00', '20.00', 'paid', NULL, '2', '2', '2026-03-29 23:39:39'),
  ('4', '4', 'cash', '60.00', '0.00', 'paid', NULL, '2', '2', '2026-03-29 23:40:04'),
  ('5', '16', 'cash', '60.00', '0.00', 'paid', NULL, '2', '2', '2026-03-30 00:12:30'),
  ('6', '70', 'cash', '60.00', '0.00', 'paid', NULL, '1', '1', '2026-03-31 03:12:02'),
  ('7', '85', 'cash', '60.00', '0.00', 'paid', NULL, '1', '1', '2026-03-31 04:34:44'),
  ('8', '99', 'cash', '60.00', '0.00', 'paid', NULL, '1', '1', '2026-03-31 10:07:40'),
  ('9', '115', 'cash', '100.00', '10.00', 'paid', NULL, '1', '1', '2026-03-31 10:48:07');

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
  UNIQUE KEY `table_name` (`table_name`),
  KEY `fk_tables_current_order` (`current_order_id`),
  CONSTRAINT `fk_tables_current_order` FOREIGN KEY (`current_order_id`) REFERENCES `orders` (`order_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tables` (`table_id`, `table_name`, `capacity`, `status`, `current_order_id`, `updated_at`) VALUES
  ('1', 'Table 1', '4', 'available', NULL, '2026-04-01 02:24:47');

SET FOREIGN_KEY_CHECKS = 1;
