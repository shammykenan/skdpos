-- ============================================================
-- Dine with Din Cafeteria POS — Database Backup
-- Generated : 2026-03-20 17:22:07 (Asia/Manila)
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categories` (`category_id`, `category_name`, `created_at`) VALUES
  ('1', 'Coffee', '2026-03-19 13:50:54'),
  ('2', 'Milk Tea', '2026-03-19 13:50:54'),
  ('3', 'Snacks', '2026-03-19 13:50:54');

-- ----------------------------------------------------------
-- Table: `ingredients`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `ingredients`;
CREATE TABLE `ingredients` (
  `ingredient_id` int(11) NOT NULL AUTO_INCREMENT,
  `ingredient_name` varchar(100) NOT NULL,
  `stock_quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `unit` varchar(20) NOT NULL,
  `low_stock_alert` decimal(10,2) DEFAULT 10.00,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`ingredient_id`),
  UNIQUE KEY `ingredient_name` (`ingredient_name`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `ingredients` (`ingredient_id`, `ingredient_name`, `stock_quantity`, `unit`, `low_stock_alert`, `last_updated`) VALUES
  ('1', 'Coffee Beans', '365.00', 'kg', '100.00', '2026-03-20 17:02:32'),
  ('2', 'Milk', '18.40', 'l', '5.00', '2026-03-20 17:21:52'),
  ('3', 'Sugar', '1000.00', 'kg', '200.00', '2026-03-20 17:21:38'),
  ('4', 'Tea Leaves', '290.00', 'kg', '50.00', '2026-03-20 17:21:28'),
  ('5', 'Tapioca Pearls', '200.00', 'kg', '50.00', '2026-03-20 17:21:44'),
  ('6', 'Chocolate Syrup', '40.00', 'ml', '10.00', '2026-03-19 18:13:51'),
  ('7', 'Potatoes', '979.00', 'g', '500.00', '2026-03-20 16:37:06');

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
  PRIMARY KEY (`attempt_id`),
  KEY `idx_ip_time` (`ip_address`,`attempt_time`),
  KEY `idx_username_time` (`username`,`attempt_time`)
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `login_attempts` (`attempt_id`, `ip_address`, `username`, `success`, `attempt_time`) VALUES
  ('3', '::1', 'wushws', '0', '2026-03-19 13:42:27'),
  ('4', '::1', 'swswsws', '0', '2026-03-19 13:42:31'),
  ('5', '::1', 'swsws', '0', '2026-03-19 13:42:35'),
  ('6', '::1', 'wswsw', '0', '2026-03-19 13:42:39'),
  ('7', '::1', 'swsws', '0', '2026-03-19 13:42:51');

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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`user_id`, `username`, `password_hash`, `full_name`, `email`, `role`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
  ('1', 'shamsham', '$2y$10$9PaVB4/ZBs/M9WuohMdsh.o1HnLRA8nVNTW0tfg0MkapcTi.ELudm', 'Shammy Kenan Din', 'dinshammykenan012@gmail.com', 'admin', '1', '2026-03-20 17:12:13', '2026-03-19 13:40:33', '2026-03-20 17:12:13'),
  ('2', 'lyn', '$2y$10$GlgA70SwXNrdS8ZEC03UiOmAdwVKp2X9fPU2NntP5Zn9vEaZAvfbi', 'Noribisco', 'dinshammykenan07@gmail.com', 'cashier', '1', '2026-03-20 16:37:45', '2026-03-19 16:46:27', '2026-03-20 16:37:45'),
  ('3', 'staff', '$2y$10$0lTWctTPdcbEaF3SVoZVr.qGJU3EonxtqCH3n3jvWqZeyRC7MObWC', 'Staff123', 'dinshammykenan999@gmail.com', 'kitchen', '1', '2026-03-20 13:04:15', '2026-03-19 16:49:48', '2026-03-20 13:04:15'),
  ('4', 'cashier', '$2y$10$bhs317UTQWeBDGzxpp4XI.kqCavrIxOtEAhVcT2UX4xF6CETxPGzq', 'Cashier', 'fixeygear@gmail.com', 'cashier', '1', '2026-03-20 13:43:15', '2026-03-19 17:36:39', '2026-03-20 13:43:15'),
  ('6', 'testCashier', '$2y$10$tl4/mMYfVMlgUL5/ua3h5uFA2b6XzDdApQjoggJjjxhrHqDY27qQq', 'TestCashier', 'test@gmail.com', 'cashier', '1', '2026-03-20 13:59:37', '2026-03-20 13:59:23', '2026-03-20 13:59:37');

-- ----------------------------------------------------------
-- Table: `menu_items`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `menu_items`;
CREATE TABLE `menu_items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `cost_price` decimal(10,2) DEFAULT 0.00,
  `category_id` int(11) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`item_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `menu_items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `menu_items` (`item_id`, `item_name`, `description`, `price`, `cost_price`, `category_id`, `image_url`, `is_available`, `created_at`) VALUES
  ('1', 'Hot Americano', 'Classic black coffee', '80.00', '30.00', '1', NULL, '1', '2026-03-19 13:51:22'),
  ('2', 'Cappuccino', 'Coffee with milk foam', '120.00', '50.00', '1', NULL, '1', '2026-03-19 13:51:22'),
  ('3', 'Milk Tea Classic', 'Sweet milk tea with pearls', '90.00', '40.00', '2', NULL, '1', '2026-03-19 13:51:22'),
  ('4', 'Chocolate Milk Tea', 'Milk tea with chocolate flavor', '110.00', '50.00', '2', NULL, '1', '2026-03-19 13:51:22'),
  ('5', 'Fries', 'Crispy potato fries', '70.00', '25.00', '3', NULL, '1', '2026-03-19 13:51:22');

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
  PRIMARY KEY (`addon_id`),
  KEY `ingredient_id` (`ingredient_id`),
  CONSTRAINT `addons_ibfk_1` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`ingredient_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `addons` (`addon_id`, `addon_name`, `price`, `ingredient_id`, `quantity_used`, `is_active`) VALUES
  ('1', 'Extra Pearl', '15.00', '5', '10.00', '1'),
  ('2', 'Extra Milk', '10.00', '2', '0.10', '1'),
  ('3', 'Chocolate Shot', '20.00', '6', '5.00', '1');

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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `activity_logs` (`log_id`, `user_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
  ('1', '1', 'forgot_password_request', 'Password reset requested for email: dinshammykenan012@gmail.com', '::1', '2026-03-19 13:40:43'),
  ('2', '1', 'password_reset_success', 'Password successfully reset via email link.', '::1', '2026-03-19 13:41:03'),
  ('3', '1', 'forgot_password_request', 'Password reset requested for email: dinshammykenan012@gmail.com', '192.168.100.106', '2026-03-19 15:12:32'),
  ('4', '1', 'forgot_password_request', 'Password reset requested for email: dinshammykenan012@gmail.com', '192.168.100.106', '2026-03-19 15:14:01'),
  ('5', '1', 'forgot_password_request', 'Password reset requested for email: dinshammykenan012@gmail.com', '192.168.100.106', '2026-03-19 15:14:08');

-- ----------------------------------------------------------
-- Table: `orders`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_number` varchar(20) NOT NULL,
  `cashier_id` int(11) NOT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `table_number` varchar(10) DEFAULT NULL,
  `order_type` enum('dine_in','takeout') DEFAULT 'dine_in',
  `order_source` enum('walk_in','online') DEFAULT 'walk_in',
  `subtotal` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','preparing','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `cashier_id` (`cashier_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `orders` (`order_id`, `order_number`, `cashier_id`, `customer_name`, `table_number`, `order_type`, `order_source`, `subtotal`, `discount`, `total_amount`, `status`, `created_at`, `completed_at`) VALUES
  ('1', 'SKD-20260319-F84376', '1', 'sham', NULL, 'takeout', 'walk_in', '130.00', '0.00', '145.60', 'completed', '2026-03-19 14:33:03', '2026-03-19 14:33:45'),
  ('2', 'SKD-20260319-8A27AE', '1', 'sham', NULL, 'takeout', 'walk_in', '70.00', '0.00', '78.40', 'completed', '2026-03-19 16:19:52', '2026-03-20 13:21:59'),
  ('3', 'SKD-20260319-8F302E', '1', 'sham', NULL, 'takeout', 'walk_in', '1.00', '0.00', '1.12', 'cancelled', '2026-03-19 16:21:12', NULL),
  ('4', 'SKD-20260319-FB85C8', '1', 'test', NULL, 'takeout', 'walk_in', '70.00', '0.00', '78.40', 'completed', '2026-03-19 16:48:31', '2026-03-19 16:50:43'),
  ('5', 'SKD-20260319-586B41', '1', 'test1', NULL, 'takeout', 'walk_in', '80.00', '0.00', '89.60', 'completed', '2026-03-19 16:52:05', '2026-03-19 16:52:53'),
  ('6', 'SKD-20260319-5785F1', '2', 'test2', NULL, 'takeout', 'walk_in', '70.00', '0.00', '78.40', 'completed', '2026-03-19 16:53:41', '2026-03-19 16:54:16'),
  ('7', 'SKD-20260319-946B2F', '2', 'test3', NULL, 'takeout', 'walk_in', '70.00', '0.00', '78.40', 'completed', '2026-03-19 16:54:49', '2026-03-19 16:55:08'),
  ('8', 'SKD-20260319-3170EC', '4', 'sham', NULL, 'takeout', 'walk_in', '70.00', '0.00', '78.40', 'completed', '2026-03-19 17:37:55', '2026-03-20 13:22:42'),
  ('9', 'SKD-20260319-01280C', '2', 'sham', NULL, 'takeout', 'walk_in', '70.00', '0.00', '78.40', 'completed', '2026-03-19 18:03:28', '2026-03-19 18:05:09'),
  ('10', 'SKD-20260319-83BD8E', '2', 'sham', NULL, 'takeout', 'walk_in', '120.00', '0.00', '134.40', 'completed', '2026-03-19 18:13:12', '2026-03-20 13:22:34'),
  ('11', 'SKD-20260319-58489C', '2', 'sham', NULL, 'takeout', 'walk_in', '70.00', '0.00', '78.40', 'completed', '2026-03-19 18:13:25', '2026-03-20 13:22:25'),
  ('12', 'SKD-20260319-0CD956', '2', 'sham', NULL, 'takeout', 'walk_in', '70.00', '0.00', '78.40', 'completed', '2026-03-19 18:13:36', '2026-03-20 13:22:17'),
  ('13', 'SKD-20260319-8335D5', '2', 'sham', NULL, 'takeout', 'walk_in', '120.00', '0.00', '134.40', 'completed', '2026-03-19 18:13:44', '2026-03-20 13:22:08'),
  ('14', 'SKD-20260319-FAA6F6', '2', 'sham', NULL, 'takeout', 'walk_in', '110.00', '0.00', '123.20', 'completed', '2026-03-19 18:13:51', '2026-03-20 13:21:47'),
  ('15', 'SKD-20260319-C04746', '2', 'sham', NULL, 'takeout', 'walk_in', '120.00', '0.00', '134.40', 'completed', '2026-03-19 18:14:04', '2026-03-20 13:11:27'),
  ('16', 'SKD-20260319-E0E7A9', '2', 'sham', NULL, 'takeout', 'walk_in', '70.00', '0.00', '78.40', 'cancelled', '2026-03-19 18:14:22', NULL),
  ('17', 'SKD-20260319-668BD5', '2', 'sham', NULL, 'takeout', 'walk_in', '70.00', '0.00', '78.40', 'completed', '2026-03-19 18:15:02', '2026-03-20 13:21:38'),
  ('18', 'SKD-20260319-A1E4A1', '2', 'sham', NULL, 'takeout', 'walk_in', '120.00', '0.00', '134.40', 'completed', '2026-03-19 18:15:22', '2026-03-20 13:21:30'),
  ('19', 'SKD-20260319-BD3B11', '2', 'sham', NULL, 'takeout', 'walk_in', '70.00', '0.00', '78.40', 'completed', '2026-03-19 18:15:39', '2026-03-20 13:21:21'),
  ('20', 'SKD-20260319-9638C3', '2', NULL, NULL, 'takeout', 'walk_in', '70.00', '0.00', '78.40', 'completed', '2026-03-19 18:26:17', '2026-03-20 13:21:14'),
  ('21', 'SKD-20260319-F14332', '2', 'sham', NULL, 'takeout', 'walk_in', '70.00', '0.00', '78.40', 'completed', '2026-03-19 18:47:43', '2026-03-20 13:21:04'),
  ('22', 'SKD-20260319-3F1D29', '2', 'sham', NULL, 'takeout', 'walk_in', '70.00', '0.00', '78.40', 'completed', '2026-03-19 18:48:03', '2026-03-20 13:20:57'),
  ('23', 'SKD-20260319-C2FE56', '2', 'sham', NULL, 'takeout', 'walk_in', '70.00', '0.00', '78.40', 'completed', '2026-03-19 19:04:44', '2026-03-20 13:20:50'),
  ('24', 'SKD-20260319-5ED192', '2', NULL, NULL, 'takeout', 'walk_in', '150.00', '0.00', '168.00', 'completed', '2026-03-19 19:05:09', '2026-03-20 13:20:42'),
  ('25', 'SKD-20260319-252C1F', '2', 'sham', NULL, 'takeout', 'walk_in', '80.00', '0.00', '89.60', 'completed', '2026-03-19 19:18:10', '2026-03-20 13:20:34'),
  ('26', 'SKD-20260319-3BCE4F', '2', 'sham', NULL, 'takeout', 'walk_in', '70.00', '0.00', '78.40', 'completed', '2026-03-19 19:34:43', '2026-03-20 13:20:16'),
  ('27', 'SKD-20260319-82DE69', '2', 'sham', NULL, 'takeout', 'walk_in', '120.00', '0.00', '134.40', 'completed', '2026-03-19 19:35:04', '2026-03-20 13:04:58'),
  ('28', 'SKD-20260319-714C81', '2', 'sham', NULL, 'takeout', 'walk_in', '70.00', '0.00', '78.40', 'cancelled', '2026-03-19 19:37:27', NULL),
  ('29', 'SKD-20260319-38DA52', '2', 'sham', NULL, 'takeout', 'walk_in', '70.00', '0.00', '78.40', 'cancelled', '2026-03-19 19:37:39', NULL),
  ('30', 'SKD-20260319-AB1713', '2', 'sham', NULL, 'takeout', 'walk_in', '70.00', '0.00', '78.40', 'cancelled', '2026-03-19 19:43:22', NULL),
  ('31', 'SKD-20260319-836EC7', '2', 'sham', NULL, 'takeout', 'walk_in', '70.00', '0.00', '78.40', 'cancelled', '2026-03-19 20:16:56', NULL),
  ('32', 'SKD-20260319-04CCCB', '4', 'test2', NULL, 'takeout', 'walk_in', '70.00', '0.00', '78.40', 'completed', '2026-03-19 20:29:20', '2026-03-20 13:19:53'),
  ('33', 'SKD-20260320-BE8676', '2', 'Shammy Kenan Din', '9', 'dine_in', 'walk_in', '140.00', '0.00', '140.00', 'completed', '2026-03-20 13:37:15', '2026-03-20 13:42:13'),
  ('34', 'SKD-20260320-4C6F8B', '6', 'Shammy', '9', 'dine_in', 'walk_in', '70.00', '0.00', '70.00', 'completed', '2026-03-20 14:00:04', '2026-03-20 14:00:38'),
  ('35', 'SKD-20260320-26EFCB', '6', 'shammy', NULL, 'takeout', 'walk_in', '70.00', '0.00', '70.00', 'cancelled', '2026-03-20 15:27:14', NULL),
  ('36', 'SKD-20260320-221CDE', '2', 'John Cena', NULL, 'takeout', 'walk_in', '70.00', '0.00', '70.00', 'completed', '2026-03-20 16:37:06', '2026-03-20 16:38:24');

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
  KEY `idx_token` (`token`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_ip_created` (`ip_address`,`created_at`),
  KEY `idx_expires` (`expires_at`),
  CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `password_resets` (`reset_id`, `user_id`, `token`, `expires_at`, `used`, `ip_address`, `created_at`) VALUES
  ('1', '1', '0dae6b32d13f2835bab101df1826182fcccd6a391e6800488057579d0c1648dd', '2026-03-19 14:40:42', '1', '::1', '2026-03-19 13:40:42'),
  ('2', '1', 'e654a28f82c4b34189c1ded8a5babce732aedbefe4e8eb6c70064cc9b57f44c9', '2026-03-19 16:12:30', '1', '192.168.100.106', '2026-03-19 15:12:30'),
  ('3', '1', 'f9095222df021e41562e2c0284c08188770740766f0a1d576efd9a20d1a42883', '2026-03-19 16:14:00', '1', '192.168.100.106', '2026-03-19 15:14:00'),
  ('4', '1', '2a3626ce34f765bb0179dbb4b111d7bb6070d7bb5c3c16e93bf9d8a11b1feaf3', '2026-03-19 16:14:07', '0', '192.168.100.106', '2026-03-19 15:14:07');

-- ----------------------------------------------------------
-- Table: `recipes`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `recipes`;
CREATE TABLE `recipes` (
  `recipe_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `ingredient_id` int(11) NOT NULL,
  `quantity_required` decimal(10,2) NOT NULL,
  PRIMARY KEY (`recipe_id`),
  KEY `item_id` (`item_id`),
  KEY `ingredient_id` (`ingredient_id`),
  CONSTRAINT `recipes_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `menu_items` (`item_id`) ON DELETE CASCADE,
  CONSTRAINT `recipes_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`ingredient_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `recipes` (`recipe_id`, `item_id`, `ingredient_id`, `quantity_required`) VALUES
  ('1', '1', '1', '15.00'),
  ('2', '2', '1', '15.00'),
  ('3', '2', '2', '0.20'),
  ('4', '3', '4', '10.00'),
  ('5', '3', '2', '0.30'),
  ('6', '3', '5', '20.00'),
  ('7', '4', '4', '10.00'),
  ('8', '4', '2', '0.30'),
  ('9', '4', '6', '10.00'),
  ('10', '5', '7', '1.00');

-- ----------------------------------------------------------
-- Table: `order_items`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `item_notes` text DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `status` enum('pending','preparing','ready') DEFAULT 'pending',
  PRIMARY KEY (`order_item_id`),
  KEY `order_id` (`order_id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `menu_items` (`item_id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `order_items` (`order_item_id`, `order_id`, `item_id`, `quantity`, `unit_price`, `item_notes`, `subtotal`, `status`) VALUES
  ('1', '1', '2', '1', '120.00', NULL, '120.00', 'pending'),
  ('2', '2', '5', '1', '70.00', NULL, '70.00', 'pending'),
  ('4', '4', '5', '1', '70.00', NULL, '70.00', 'pending'),
  ('5', '5', '1', '1', '80.00', NULL, '80.00', 'pending'),
  ('6', '6', '5', '1', '70.00', NULL, '70.00', 'pending'),
  ('7', '7', '5', '1', '70.00', NULL, '70.00', 'pending'),
  ('8', '8', '5', '1', '70.00', NULL, '70.00', 'pending'),
  ('9', '9', '5', '1', '70.00', NULL, '70.00', 'pending'),
  ('10', '10', '2', '1', '120.00', NULL, '120.00', 'pending'),
  ('11', '11', '5', '1', '70.00', NULL, '70.00', 'pending'),
  ('12', '12', '5', '1', '70.00', NULL, '70.00', 'pending'),
  ('13', '13', '2', '1', '120.00', NULL, '120.00', 'pending'),
  ('14', '14', '4', '1', '110.00', NULL, '110.00', 'pending'),
  ('15', '15', '2', '1', '120.00', NULL, '120.00', 'pending'),
  ('16', '16', '5', '1', '70.00', NULL, '70.00', 'pending'),
  ('17', '17', '5', '1', '70.00', NULL, '70.00', 'pending'),
  ('18', '18', '2', '1', '120.00', NULL, '120.00', 'pending'),
  ('19', '19', '5', '1', '70.00', NULL, '70.00', 'pending'),
  ('20', '20', '5', '1', '70.00', NULL, '70.00', 'pending'),
  ('21', '21', '5', '1', '70.00', NULL, '70.00', 'pending'),
  ('22', '22', '5', '1', '70.00', NULL, '70.00', 'pending'),
  ('23', '23', '5', '1', '70.00', NULL, '70.00', 'pending'),
  ('24', '24', '5', '1', '70.00', NULL, '70.00', 'pending'),
  ('25', '24', '1', '1', '80.00', NULL, '80.00', 'pending'),
  ('26', '25', '1', '1', '80.00', NULL, '80.00', 'pending'),
  ('27', '26', '5', '1', '70.00', NULL, '70.00', 'pending'),
  ('28', '27', '2', '1', '120.00', NULL, '120.00', 'pending'),
  ('29', '28', '5', '1', '70.00', NULL, '70.00', 'pending'),
  ('30', '29', '5', '1', '70.00', NULL, '70.00', 'pending'),
  ('31', '30', '5', '1', '70.00', NULL, '70.00', 'pending'),
  ('32', '31', '5', '1', '70.00', NULL, '70.00', 'pending'),
  ('33', '32', '5', '1', '70.00', NULL, '70.00', 'pending'),
  ('34', '33', '5', '2', '70.00', NULL, '140.00', 'pending'),
  ('35', '34', '5', '1', '70.00', NULL, '70.00', 'pending'),
  ('36', '35', '5', '1', '70.00', NULL, '70.00', 'pending'),
  ('37', '36', '5', '1', '70.00', NULL, '70.00', 'pending');

-- ----------------------------------------------------------
-- Table: `payments`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `payment_method` enum('cash') NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `change_amount` decimal(10,2) DEFAULT 0.00,
  `payment_status` enum('paid','pending','refunded') DEFAULT 'paid',
  `transaction_ref` varchar(100) DEFAULT NULL,
  `cashier_id` int(11) NOT NULL,
  `paid_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`payment_id`),
  KEY `order_id` (`order_id`),
  KEY `cashier_id` (`cashier_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `payments` (`payment_id`, `order_id`, `payment_method`, `amount_paid`, `change_amount`, `payment_status`, `transaction_ref`, `cashier_id`, `paid_at`) VALUES
  ('1', '1', 'cash', '145.60', '0.00', 'paid', NULL, '1', '2026-03-19 14:33:45'),
  ('2', '4', 'cash', '100.00', '21.60', 'paid', NULL, '2', '2026-03-19 16:50:43'),
  ('3', '5', 'cash', '100.00', '10.40', 'paid', NULL, '2', '2026-03-19 16:52:53'),
  ('4', '6', 'cash', '100.00', '21.60', 'paid', NULL, '2', '2026-03-19 16:54:16'),
  ('5', '7', 'cash', '80.00', '1.60', 'paid', NULL, '1', '2026-03-19 16:55:08'),
  ('6', '9', 'cash', '200.00', '121.60', 'paid', NULL, '2', '2026-03-19 18:05:09'),
  ('7', '27', 'cash', '150.00', '15.60', 'paid', NULL, '2', '2026-03-20 13:04:58'),
  ('8', '15', 'cash', '200.00', '65.60', 'paid', NULL, '2', '2026-03-20 13:11:27'),
  ('9', '32', 'cash', '100.00', '21.60', 'paid', NULL, '1', '2026-03-20 13:19:53'),
  ('10', '26', 'cash', '100.00', '21.60', 'paid', NULL, '1', '2026-03-20 13:20:16'),
  ('11', '25', 'cash', '100.00', '10.40', 'paid', NULL, '1', '2026-03-20 13:20:34'),
  ('12', '24', 'cash', '200.00', '32.00', 'paid', NULL, '1', '2026-03-20 13:20:42'),
  ('13', '23', 'cash', '100.00', '21.60', 'paid', NULL, '1', '2026-03-20 13:20:50'),
  ('14', '22', 'cash', '100.00', '21.60', 'paid', NULL, '1', '2026-03-20 13:20:57'),
  ('15', '21', 'cash', '100.00', '21.60', 'paid', NULL, '1', '2026-03-20 13:21:04'),
  ('16', '20', 'cash', '100.00', '21.60', 'paid', NULL, '1', '2026-03-20 13:21:14'),
  ('17', '19', 'cash', '100.00', '21.60', 'paid', NULL, '1', '2026-03-20 13:21:21'),
  ('18', '18', 'cash', '150.00', '15.60', 'paid', NULL, '1', '2026-03-20 13:21:30'),
  ('19', '17', 'cash', '100.00', '21.60', 'paid', NULL, '1', '2026-03-20 13:21:38'),
  ('20', '14', 'cash', '150.00', '26.80', 'paid', NULL, '1', '2026-03-20 13:21:47'),
  ('21', '2', 'cash', '100.00', '21.60', 'paid', NULL, '1', '2026-03-20 13:21:59'),
  ('22', '13', 'cash', '150.00', '15.60', 'paid', NULL, '1', '2026-03-20 13:22:08'),
  ('23', '12', 'cash', '100.00', '21.60', 'paid', NULL, '1', '2026-03-20 13:22:17'),
  ('24', '11', 'cash', '100.00', '21.60', 'paid', NULL, '1', '2026-03-20 13:22:25'),
  ('25', '10', 'cash', '1000.00', '865.60', 'paid', NULL, '1', '2026-03-20 13:22:34'),
  ('26', '8', 'cash', '100.00', '21.60', 'paid', NULL, '1', '2026-03-20 13:22:42'),
  ('27', '33', 'cash', '150.00', '10.00', 'paid', NULL, '2', '2026-03-20 13:42:13'),
  ('28', '34', 'cash', '100.00', '30.00', 'paid', NULL, '6', '2026-03-20 14:00:38'),
  ('29', '36', 'cash', '100.00', '30.00', 'paid', NULL, '2', '2026-03-20 16:38:24');

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `order_item_addons` (`order_item_addon_id`, `order_item_id`, `addon_id`, `quantity`, `price`) VALUES
  ('1', '1', '2', '1', '10.00');

SET FOREIGN_KEY_CHECKS = 1;
