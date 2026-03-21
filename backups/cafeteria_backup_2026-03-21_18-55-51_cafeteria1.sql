-- ============================================================
-- Dine with Din Cafeteria POS — Database Backup
-- Generated : 2026-03-21 18:55:51 (Asia/Manila)
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categories` (`category_id`, `category_name`, `created_at`) VALUES
  ('1', 'Coffee', '2026-03-21 03:35:40'),
  ('2', 'Non-Coffee', '2026-03-21 03:35:40'),
  ('3', 'Frappe', '2026-03-21 03:35:40'),
  ('4', 'Tea', '2026-03-21 03:35:40'),
  ('5', 'Pastries', '2026-03-21 03:35:40');

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
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `ingredients` (`ingredient_id`, `ingredient_name`, `stock_quantity`, `unit`, `low_stock_alert`, `last_updated`) VALUES
  ('1', 'Espresso Shot', '500.00', 'ml', '50.00', '2026-03-21 18:12:30'),
  ('2', 'Arabica Coffee Beans', '5000.00', 'g', '500.00', '2026-03-21 03:35:40'),
  ('3', 'Fresh Milk', '5290.00', 'ml', '1000.00', '2026-03-21 17:37:13'),
  ('4', 'Condensed Milk', '5000.00', 'ml', '500.00', '2026-03-21 03:35:40'),
  ('5', 'Heavy Cream', '3000.00', 'ml', '300.00', '2026-03-21 03:35:40'),
  ('6', 'Oat Milk', '5000.00', 'ml', '500.00', '2026-03-21 03:35:40'),
  ('7', 'White Sugar', '4730.00', 'g', '500.00', '2026-03-21 16:38:19'),
  ('8', 'Brown Sugar', '4780.00', 'g', '500.00', '2026-03-21 17:37:13'),
  ('9', 'Vanilla Syrup', '2000.00', 'ml', '200.00', '2026-03-21 03:35:40'),
  ('10', 'Caramel Syrup', '1910.00', 'ml', '200.00', '2026-03-21 16:38:19'),
  ('11', 'Hazelnut Syrup', '2000.00', 'ml', '200.00', '2026-03-21 03:35:40'),
  ('12', 'Chocolate Syrup', '1915.00', 'ml', '200.00', '2026-03-21 16:09:04'),
  ('13', 'Matcha Powder', '1955.00', 'g', '200.00', '2026-03-21 14:10:59'),
  ('14', 'Chocolate Powder', '1940.00', 'g', '200.00', '2026-03-21 16:09:04'),
  ('15', 'Taro Powder', '1940.00', 'g', '200.00', '2026-03-21 14:16:24'),
  ('16', 'Strawberry Powder', '1940.00', 'g', '200.00', '2026-03-21 13:42:45'),
  ('17', 'Ice', '18950.00', 'g', '2000.00', '2026-03-21 16:26:23'),
  ('18', 'Whipped Cream', '3000.00', 'ml', '300.00', '2026-03-21 03:35:40'),
  ('19', 'Tea Bag (Black)', '284.00', 'pc', '50.00', '2026-03-21 18:12:08'),
  ('20', 'Tea Bag (Green)', '300.00', 'pc', '50.00', '2026-03-21 03:35:40'),
  ('21', 'All-Purpose Flour', '9150.00', 'g', '1000.00', '2026-03-21 18:11:52'),
  ('22', 'Butter', '4550.00', 'g', '500.00', '2026-03-21 18:11:52'),
  ('23', 'Eggs', '89.00', 'pc', '20.00', '2026-03-21 18:11:52'),
  ('24', 'Cream Cheese', '2500.00', 'g', '300.00', '2026-03-21 16:38:19');

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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`user_id`, `username`, `password_hash`, `full_name`, `email`, `role`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
  ('1', 'admin123', '$2y$10$1u334hI.oAWfJoY.KsfBK.j8fsmSUsQGXnxY9pPGTW8z0r0/V7xny', 'Shammy Kenan Din', 'dinshammykenan012@gmail.com', 'admin', '1', '2026-03-21 13:08:18', '2026-03-19 13:47:16', '2026-03-21 13:08:18'),
  ('2', 'cashier123', '$2y$10$CxznWbBPvrc3qgAQTHN3kuOpHcJatwWiSoFSWugnSYtwZu7KdH2x6', 'Noribisco', 'dinshammykenan999@gmail.com', 'cashier', '1', '2026-03-21 17:26:00', '2026-03-21 03:28:25', '2026-03-21 17:26:00'),
  ('3', 'staff123', '$2y$10$iGq0mYjiVbDdcvKMRxUS0uqvIUuzEeDBvNHdehdJOlCTV6yUvqJWK', 'Lyn', 'dinshammykenan07@gmail.com', 'kitchen', '1', '2026-03-21 15:16:15', '2026-03-21 03:28:48', '2026-03-21 15:16:15');

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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `menu_items` (`item_id`, `item_name`, `description`, `price`, `cost_price`, `category_id`, `image_url`, `is_available`, `created_at`) VALUES
  ('1', 'Americano', 'Espresso shots with hot water', '89.00', '30.00', '1', NULL, '1', '2026-03-21 03:35:40'),
  ('2', 'Cappuccino', 'Espresso with steamed and foamed milk', '109.00', '40.00', '1', NULL, '1', '2026-03-21 03:35:40'),
  ('3', 'Caramel Latte', 'Espresso with milk and caramel syrup', '129.00', '45.00', '1', NULL, '1', '2026-03-21 03:35:40'),
  ('4', 'Hazelnut Latte', 'Espresso with milk and hazelnut syrup', '129.00', '45.00', '1', NULL, '1', '2026-03-21 03:35:40'),
  ('5', 'Brown Sugar Latte', 'Espresso with fresh milk and brown sugar', '119.00', '42.00', '1', NULL, '1', '2026-03-21 03:35:40'),
  ('6', 'Matcha Latte', 'Japanese matcha with steamed fresh milk', '129.00', '45.00', '2', NULL, '1', '2026-03-21 03:35:40'),
  ('7', 'Taro Latte', 'Creamy taro with fresh milk', '129.00', '45.00', '2', NULL, '1', '2026-03-21 03:35:40'),
  ('8', 'Strawberry Milk', 'Fresh milk blended with strawberry', '109.00', '38.00', '2', NULL, '1', '2026-03-21 03:35:40'),
  ('9', 'Hot Chocolate', 'Rich chocolate with steamed milk', '109.00', '38.00', '2', NULL, '1', '2026-03-21 03:35:40'),
  ('10', 'Mocha Frappe', 'Blended espresso with chocolate and milk over ice', '149.00', '55.00', '3', NULL, '1', '2026-03-21 03:35:40'),
  ('11', 'Caramel Frappe', 'Blended espresso with caramel and milk over ice', '149.00', '55.00', '3', NULL, '1', '2026-03-21 03:35:40'),
  ('12', 'Matcha Frappe', 'Blended matcha with milk and ice', '149.00', '55.00', '3', NULL, '1', '2026-03-21 03:35:40'),
  ('13', 'Strawberry Frappe', 'Blended strawberry with milk and ice', '139.00', '50.00', '3', NULL, '1', '2026-03-21 03:35:40'),
  ('14', 'Black Tea', 'Classic hot brewed black tea', '79.00', '20.00', '4', NULL, '1', '2026-03-21 03:35:40'),
  ('15', 'Green Tea', 'Light and refreshing hot green tea', '79.00', '20.00', '4', NULL, '1', '2026-03-21 03:35:40'),
  ('16', 'Milk Tea', 'Black tea with fresh milk and brown sugar', '99.00', '30.00', '4', NULL, '1', '2026-03-21 03:35:40'),
  ('17', 'Butter Croissant', 'Flaky buttery croissant baked fresh', '69.00', '25.00', '5', NULL, '1', '2026-03-21 03:35:40'),
  ('18', 'Cheesecake Slice', 'Creamy classic New York style cheesecake', '99.00', '40.00', '5', NULL, '1', '2026-03-21 03:35:40');

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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `addons` (`addon_id`, `addon_name`, `price`, `ingredient_id`, `quantity_used`, `is_active`) VALUES
  ('1', 'Extra Espresso Shot', '20.00', '1', '30.00', '1'),
  ('2', 'Whipped Cream', '15.00', '18', '30.00', '1'),
  ('3', 'Caramel Drizzle', '15.00', '10', '10.00', '1'),
  ('4', 'Chocolate Drizzle', '15.00', '12', '10.00', '1'),
  ('5', 'Hazelnut Syrup', '15.00', '11', '15.00', '1'),
  ('6', 'Vanilla Syrup', '15.00', '9', '15.00', '1'),
  ('7', 'Extra Caramel Syrup', '15.00', '10', '15.00', '1'),
  ('8', 'Oat Milk Swap', '20.00', '6', '180.00', '1'),
  ('9', 'Extra Ice', '0.00', '17', '50.00', '1'),
  ('10', 'Extra Fresh Milk', '15.00', '3', '60.00', '1'),
  ('11', 'Brown Sugar Syrup', '15.00', '8', '15.00', '1'),
  ('12', 'Extra Matcha', '20.00', '13', '10.00', '1'),
  ('13', 'Extra Chocolate', '20.00', '14', '10.00', '1'),
  ('14', 'Condensed Milk', '15.00', '4', '20.00', '1'),
  ('15', 'Extra Whipped Cream', '15.00', '18', '50.00', '1');

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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `activity_logs` (`log_id`, `user_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
  ('1', '1', 'forgot_password_request', 'Password reset requested for email: dinshammykenan012@gmail.com', '::1', '2026-03-19 13:47:26'),
  ('2', '1', 'password_reset_success', 'Password successfully reset via email link.', '::1', '2026-03-19 13:47:56'),
  ('3', '2', 'forgot_password_request', 'Password reset requested for email: dinshammykenan999@gmail.com', '192.168.100.11', '2026-03-21 03:43:14'),
  ('4', '1', 'forgot_password_request', 'Password reset requested for email: dinshammykenan012@gmail.com', '192.168.100.11', '2026-03-21 03:45:47'),
  ('5', '1', 'forgot_password_request', 'Password reset requested for email: dinshammykenan012@gmail.com', '192.168.100.11', '2026-03-21 03:45:48'),
  ('6', '1', 'forgot_password_request', 'Password reset requested for email: dinshammykenan012@gmail.com', '192.168.100.11', '2026-03-21 13:07:03'),
  ('7', '1', 'password_reset_success', 'Password successfully reset via email link.', '192.168.100.11', '2026-03-21 13:07:41');

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
  `status` enum('pending','preparing','ready','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `cashier_id` (`cashier_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `orders` (`order_id`, `order_number`, `cashier_id`, `customer_name`, `table_number`, `order_type`, `order_source`, `subtotal`, `discount`, `total_amount`, `status`, `created_at`, `completed_at`) VALUES
  ('1', 'SKD-20260321-82DF5B', '2', 'sham', NULL, 'takeout', 'walk_in', '148.00', '0.00', '148.00', 'completed', '2026-03-21 03:37:12', '2026-03-21 03:37:51'),
  ('2', 'SKD-20260321-18CD79', '2', 'Shammy Kenan Din', '9', 'dine_in', 'walk_in', '148.00', '0.00', '148.00', 'completed', '2026-03-21 13:30:41', '2026-03-21 13:31:29'),
  ('3', 'SKD-20260321-D066BA', '2', 'Shammy Kenan Din', NULL, 'takeout', 'walk_in', '99.00', '0.00', '99.00', 'cancelled', '2026-03-21 13:33:33', NULL),
  ('4', 'SKD-20260321-734805', '2', 'Shammy', NULL, 'takeout', 'walk_in', '109.00', '0.00', '109.00', 'completed', '2026-03-21 13:37:43', '2026-03-21 13:39:29'),
  ('5', 'SKD-20260321-521397', '2', 'Shamsham', NULL, 'takeout', 'walk_in', '1081.00', '0.00', '1081.00', 'completed', '2026-03-21 13:42:45', '2026-03-21 14:48:34'),
  ('6', 'SKD-20260321-BB05DD', '2', 'sham', NULL, 'takeout', 'walk_in', '109.00', '0.00', '109.00', 'completed', '2026-03-21 13:58:19', '2026-03-21 14:48:40'),
  ('7', 'SKD-20260321-6AD804', '2', 'sham', NULL, 'takeout', 'walk_in', '69.00', '0.00', '69.00', 'cancelled', '2026-03-21 13:59:02', NULL),
  ('8', 'SKD-20260321-319F5C', '2', 'sham', NULL, 'takeout', 'walk_in', '188.00', '0.00', '188.00', 'completed', '2026-03-21 14:01:39', '2026-03-21 14:48:47'),
  ('9', 'SKD-20260321-3BB659', '1', 'sham', NULL, 'takeout', 'walk_in', '516.00', '0.00', '516.00', 'completed', '2026-03-21 14:10:59', '2026-03-21 14:48:53'),
  ('10', 'SKD-20260321-FC3272', '1', 'sham', NULL, 'takeout', 'walk_in', '188.00', '0.00', '188.00', 'completed', '2026-03-21 14:11:27', '2026-03-21 14:49:00'),
  ('11', 'SKD-20260321-8A980F', '1', 'sham', NULL, 'takeout', 'walk_in', '168.00', '0.00', '168.00', 'completed', '2026-03-21 14:12:08', '2026-03-21 14:49:06'),
  ('12', 'SKD-20260321-DC6C1D', '1', 'sham', NULL, 'takeout', 'walk_in', '198.00', '0.00', '198.00', 'cancelled', '2026-03-21 14:13:01', NULL),
  ('13', 'SKD-20260321-DF0D57', '1', 'sham', NULL, 'takeout', 'walk_in', '79.00', '0.00', '79.00', 'completed', '2026-03-21 14:13:33', '2026-03-21 14:49:11'),
  ('14', 'SKD-20260321-8838E1', '1', 'sham', NULL, 'takeout', 'walk_in', '560.00', '0.00', '560.00', 'completed', '2026-03-21 14:16:24', '2026-03-21 14:49:20'),
  ('15', 'SKD-20260321-954616', '1', 'sham', NULL, 'takeout', 'walk_in', '158.00', '0.00', '158.00', 'completed', '2026-03-21 14:18:33', '2026-03-21 14:49:26'),
  ('16', 'SKD-20260321-F88B98', '2', 'Sham', '9', 'dine_in', 'walk_in', '79.00', '0.00', '79.00', 'completed', '2026-03-21 14:45:19', '2026-03-21 14:49:34'),
  ('17', 'SKD-20260321-331959', '1', 'hello', '9', 'dine_in', 'walk_in', '119.00', '0.00', '119.00', 'completed', '2026-03-21 15:10:59', '2026-03-21 15:15:27'),
  ('18', 'SKD-20260321-523565', '2', 'uwhshws', '9', 'dine_in', 'walk_in', '119.00', '0.00', '119.00', 'completed', '2026-03-21 15:49:57', '2026-03-21 15:50:35'),
  ('19', 'SKD-20260321-0B0813', '2', 'hahahaha', '9', 'dine_in', 'walk_in', '684.00', '0.00', '684.00', 'completed', '2026-03-21 16:09:04', '2026-03-21 16:10:53'),
  ('20', 'SKD-20260321-F2F9C3', '2', 'sham', NULL, 'takeout', 'walk_in', '630.00', '0.00', '630.00', 'completed', '2026-03-21 16:26:23', '2026-03-21 16:28:03'),
  ('21', 'SKD-20260321-BDBC52', '1', 'sham', NULL, 'takeout', 'walk_in', '475.00', '0.00', '475.00', 'completed', '2026-03-21 16:38:19', '2026-03-21 16:39:17'),
  ('22', 'SKD-20260321-3095AF', '1', 'sham', NULL, 'takeout', 'walk_in', '89.00', '0.00', '89.00', 'cancelled', '2026-03-21 16:39:31', NULL),
  ('23', 'SKD-20260321-2E1CDC', '1', 'sham', NULL, 'takeout', 'walk_in', '89.00', '0.00', '89.00', 'cancelled', '2026-03-21 16:41:54', NULL),
  ('24', 'SKD-20260321-7AFE01', '2', 'sham', NULL, 'takeout', 'walk_in', '119.00', '0.00', '119.00', 'cancelled', '2026-03-21 17:20:39', NULL),
  ('25', 'SKD-20260321-7C14E8', '2', 's', NULL, 'takeout', 'walk_in', '119.00', '0.00', '119.00', 'completed', '2026-03-21 17:21:43', '2026-03-21 17:31:12'),
  ('26', 'SKD-20260321-A3F4CE', '2', 'sham', NULL, 'takeout', 'walk_in', '119.00', '0.00', '119.00', 'cancelled', '2026-03-21 17:31:38', NULL),
  ('27', 'SKD-20260321-DEC37E', '2', 'wsnsiwhahshs', NULL, 'takeout', 'walk_in', '119.00', '0.00', '119.00', 'completed', '2026-03-21 17:36:45', '2026-03-21 18:16:27'),
  ('28', 'SKD-20260321-9B519B', '2', 'oshshs', NULL, 'takeout', 'walk_in', '119.00', '0.00', '119.00', 'completed', '2026-03-21 17:37:13', '2026-03-21 18:16:39'),
  ('29', 'SKD-20260321-A4BA7A', '2', 'isjahw', NULL, 'takeout', 'walk_in', '89.00', '0.00', '89.00', 'completed', '2026-03-21 17:38:18', '2026-03-21 18:16:48'),
  ('30', 'SKD-20260321-C2510B', '2', 'isjshw', NULL, 'takeout', 'walk_in', '79.00', '0.00', '79.00', 'completed', '2026-03-21 17:41:16', '2026-03-21 18:17:03'),
  ('31', 'SKD-20260321-39FFAB', '2', 'uahahaha', NULL, 'takeout', 'walk_in', '79.00', '0.00', '79.00', 'completed', '2026-03-21 17:41:39', '2026-03-21 18:17:09'),
  ('32', 'SKD-20260321-71F016', '2', 'jahahahw', NULL, 'takeout', 'walk_in', '79.00', '0.00', '79.00', 'completed', '2026-03-21 17:42:15', '2026-03-21 18:17:16'),
  ('33', 'SKD-20260321-DC129C', '2', 'iajwjw', NULL, 'takeout', 'walk_in', '79.00', '0.00', '79.00', 'completed', '2026-03-21 17:54:37', '2026-03-21 18:17:24'),
  ('34', 'SKD-20260321-1A4D12', '2', 'jsjwjwjw', NULL, 'takeout', 'walk_in', '79.00', '0.00', '79.00', 'completed', '2026-03-21 18:11:13', '2026-03-21 18:17:30'),
  ('35', 'SKD-20260321-8211F3', '2', 'izjajs', NULL, 'takeout', 'walk_in', '69.00', '0.00', '69.00', 'completed', '2026-03-21 18:11:52', '2026-03-21 18:17:36'),
  ('36', 'SKD-20260321-81FE8C', '2', 'isuwuwuw', NULL, 'takeout', 'walk_in', '79.00', '0.00', '79.00', 'completed', '2026-03-21 18:12:08', '2026-03-21 18:17:50');

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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `password_resets` (`reset_id`, `user_id`, `token`, `expires_at`, `used`, `ip_address`, `created_at`) VALUES
  ('1', '1', 'bb732af1dd6b27e8c8117495b521d4815daa7b3092a747ae51e72e2d93b0d3aa', '2026-03-19 14:47:25', '1', '::1', '2026-03-19 13:47:25'),
  ('2', '2', 'a033027d305e2099173a5d259e74ad8de188853ff5ba0c683ba085b868223153', '2026-03-21 04:43:13', '0', '192.168.100.11', '2026-03-21 03:43:13'),
  ('3', '1', '7d085cd82d1569c1b8e1cddc0bc865612e871cf481f6d26f146f7b919330f436', '2026-03-21 04:45:45', '1', '192.168.100.11', '2026-03-21 03:45:45'),
  ('5', '1', 'fc83d2cf0a53e450f028751db988f061c56d7e69891cc7af7b24142c2ef7086a', '2026-03-21 14:07:01', '1', '192.168.100.11', '2026-03-21 13:07:01');

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
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `recipes` (`recipe_id`, `item_id`, `ingredient_id`, `quantity_required`) VALUES
  ('1', '1', '1', '60.00'),
  ('2', '2', '1', '30.00'),
  ('3', '2', '3', '120.00'),
  ('4', '3', '1', '30.00'),
  ('5', '3', '3', '150.00'),
  ('6', '3', '10', '20.00'),
  ('7', '4', '1', '30.00'),
  ('8', '4', '3', '150.00'),
  ('9', '4', '11', '20.00'),
  ('10', '5', '1', '30.00'),
  ('11', '5', '3', '150.00'),
  ('12', '5', '8', '20.00'),
  ('13', '6', '13', '15.00'),
  ('14', '6', '3', '180.00'),
  ('15', '6', '7', '10.00'),
  ('16', '7', '15', '30.00'),
  ('17', '7', '3', '180.00'),
  ('18', '7', '7', '10.00'),
  ('19', '8', '16', '30.00'),
  ('20', '8', '3', '180.00'),
  ('21', '8', '7', '10.00'),
  ('22', '9', '14', '20.00'),
  ('23', '9', '3', '180.00'),
  ('24', '9', '12', '15.00'),
  ('25', '10', '1', '30.00'),
  ('26', '10', '3', '120.00'),
  ('27', '10', '12', '20.00'),
  ('28', '10', '17', '150.00'),
  ('29', '11', '1', '30.00'),
  ('30', '11', '3', '120.00'),
  ('31', '11', '10', '20.00'),
  ('32', '11', '17', '150.00'),
  ('33', '12', '13', '15.00'),
  ('34', '12', '3', '120.00'),
  ('35', '12', '7', '10.00'),
  ('36', '12', '17', '150.00'),
  ('37', '13', '16', '30.00'),
  ('38', '13', '3', '120.00'),
  ('39', '13', '7', '10.00'),
  ('40', '13', '17', '150.00'),
  ('41', '14', '19', '1.00'),
  ('42', '15', '20', '1.00'),
  ('43', '16', '19', '1.00'),
  ('44', '16', '3', '100.00'),
  ('45', '16', '8', '15.00'),
  ('46', '17', '21', '100.00'),
  ('47', '17', '22', '50.00'),
  ('48', '17', '23', '1.00'),
  ('49', '18', '24', '100.00'),
  ('50', '18', '23', '1.00'),
  ('51', '18', '21', '50.00'),
  ('52', '18', '7', '40.00'),
  ('53', '18', '22', '30.00');

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
  `status` enum('pending','preparing','ready','completed','cancelled') DEFAULT 'pending',
  PRIMARY KEY (`order_item_id`),
  KEY `order_id` (`order_id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `menu_items` (`item_id`)
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `order_items` (`order_item_id`, `order_id`, `item_id`, `quantity`, `unit_price`, `item_notes`, `subtotal`, `status`) VALUES
  ('1', '1', '14', '1', '79.00', NULL, '79.00', 'completed'),
  ('2', '1', '17', '1', '69.00', NULL, '69.00', 'completed'),
  ('3', '2', '14', '1', '79.00', NULL, '79.00', 'completed'),
  ('4', '2', '17', '1', '69.00', NULL, '69.00', 'completed'),
  ('5', '3', '18', '1', '99.00', NULL, '99.00', 'pending'),
  ('6', '4', '9', '1', '109.00', NULL, '109.00', 'completed'),
  ('7', '5', '1', '1', '89.00', NULL, '89.00', 'completed'),
  ('8', '5', '14', '1', '79.00', NULL, '79.00', 'completed'),
  ('9', '5', '9', '1', '109.00', NULL, '109.00', 'completed'),
  ('10', '5', '10', '1', '149.00', NULL, '149.00', 'completed'),
  ('11', '5', '12', '1', '149.00', NULL, '149.00', 'completed'),
  ('12', '5', '13', '1', '139.00', NULL, '139.00', 'completed'),
  ('13', '5', '7', '1', '129.00', NULL, '129.00', 'completed'),
  ('14', '5', '8', '1', '109.00', NULL, '109.00', 'completed'),
  ('15', '5', '6', '1', '129.00', NULL, '129.00', 'completed'),
  ('16', '6', '2', '1', '109.00', NULL, '109.00', 'completed'),
  ('17', '7', '17', '1', '69.00', NULL, '69.00', 'pending'),
  ('18', '8', '5', '1', '119.00', NULL, '119.00', 'completed'),
  ('19', '8', '17', '1', '69.00', NULL, '69.00', 'completed'),
  ('20', '9', '5', '2', '119.00', NULL, '238.00', 'completed'),
  ('21', '9', '3', '1', '129.00', NULL, '129.00', 'completed'),
  ('22', '9', '12', '1', '149.00', NULL, '149.00', 'completed'),
  ('23', '10', '17', '1', '69.00', NULL, '69.00', 'completed'),
  ('24', '10', '5', '1', '119.00', NULL, '119.00', 'completed'),
  ('25', '11', '1', '1', '89.00', NULL, '89.00', 'completed'),
  ('26', '11', '14', '1', '79.00', NULL, '79.00', 'completed'),
  ('27', '12', '14', '1', '79.00', NULL, '79.00', 'pending'),
  ('28', '12', '5', '1', '119.00', NULL, '119.00', 'pending'),
  ('29', '13', '14', '1', '79.00', NULL, '79.00', 'completed'),
  ('30', '14', '14', '1', '79.00', 'no sugar', '79.00', 'completed'),
  ('31', '14', '1', '1', '89.00', 'less ice', '89.00', 'completed'),
  ('32', '14', '18', '1', '99.00', 'nothing', '99.00', 'completed'),
  ('33', '14', '10', '1', '149.00', 'extra ice', '149.00', 'completed'),
  ('34', '14', '7', '1', '129.00', 'no sugar', '129.00', 'completed'),
  ('35', '15', '14', '1', '79.00', 'no sugar', '79.00', 'completed'),
  ('36', '15', '14', '1', '79.00', NULL, '79.00', 'completed'),
  ('37', '16', '14', '1', '79.00', NULL, '79.00', 'completed'),
  ('38', '17', '5', '1', '119.00', NULL, '119.00', 'completed'),
  ('39', '18', '5', '1', '119.00', 'no sugar', '119.00', 'completed'),
  ('40', '19', '5', '1', '119.00', 'no sugar', '119.00', 'completed'),
  ('41', '19', '2', '1', '109.00', NULL, '109.00', 'completed'),
  ('42', '19', '11', '1', '149.00', 'nshahahs', '149.00', 'completed'),
  ('43', '19', '18', '2', '99.00', 'nabahaha', '198.00', 'completed'),
  ('44', '19', '9', '1', '109.00', 'hahahaha', '109.00', 'completed'),
  ('45', '20', '5', '1', '119.00', 'jshwhsyshw', '119.00', 'completed'),
  ('46', '20', '11', '1', '149.00', 'jshsusuauw', '149.00', 'completed'),
  ('47', '20', '2', '3', '109.00', 'uwhwhsysys', '327.00', 'completed'),
  ('48', '21', '17', '1', '69.00', NULL, '69.00', 'completed'),
  ('49', '21', '18', '2', '99.00', NULL, '198.00', 'completed'),
  ('50', '21', '3', '1', '129.00', NULL, '129.00', 'completed'),
  ('51', '21', '14', '1', '79.00', NULL, '79.00', 'completed'),
  ('52', '22', '1', '1', '89.00', NULL, '89.00', 'cancelled'),
  ('53', '23', '1', '1', '89.00', NULL, '89.00', 'cancelled'),
  ('54', '24', '5', '1', '119.00', NULL, '119.00', 'cancelled'),
  ('55', '25', '5', '1', '119.00', NULL, '119.00', 'completed'),
  ('56', '26', '5', '1', '119.00', NULL, '119.00', 'cancelled'),
  ('57', '27', '5', '1', '119.00', NULL, '119.00', 'completed'),
  ('58', '28', '5', '1', '119.00', 'hshshs', '119.00', 'completed'),
  ('59', '29', '1', '1', '89.00', NULL, '89.00', 'completed'),
  ('60', '30', '14', '1', '79.00', NULL, '79.00', 'completed'),
  ('61', '31', '14', '1', '79.00', NULL, '79.00', 'completed'),
  ('62', '32', '14', '1', '79.00', NULL, '79.00', 'completed'),
  ('63', '33', '14', '1', '79.00', NULL, '79.00', 'completed'),
  ('64', '34', '14', '1', '79.00', NULL, '79.00', 'completed'),
  ('65', '35', '17', '1', '69.00', NULL, '69.00', 'completed'),
  ('66', '36', '14', '1', '79.00', NULL, '79.00', 'completed');

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
  ('1', '1', 'cash', '150.00', '2.00', 'paid', NULL, '2', '2026-03-21 03:37:51'),
  ('2', '2', 'cash', '150.00', '2.00', 'paid', NULL, '2', '2026-03-21 13:31:29'),
  ('3', '4', 'cash', '110.00', '1.00', 'paid', NULL, '1', '2026-03-21 13:39:29'),
  ('4', '5', 'cash', '1081.00', '0.00', 'paid', NULL, '1', '2026-03-21 14:48:34'),
  ('5', '6', 'cash', '109.00', '0.00', 'paid', NULL, '1', '2026-03-21 14:48:40'),
  ('6', '8', 'cash', '188.00', '0.00', 'paid', NULL, '1', '2026-03-21 14:48:47'),
  ('7', '9', 'cash', '516.00', '0.00', 'paid', NULL, '1', '2026-03-21 14:48:53'),
  ('8', '10', 'cash', '188.00', '0.00', 'paid', NULL, '1', '2026-03-21 14:49:00'),
  ('9', '11', 'cash', '200.00', '32.00', 'paid', NULL, '1', '2026-03-21 14:49:06'),
  ('10', '13', 'cash', '80.00', '1.00', 'paid', NULL, '1', '2026-03-21 14:49:11'),
  ('11', '14', 'cash', '600.00', '40.00', 'paid', NULL, '1', '2026-03-21 14:49:20'),
  ('12', '15', 'cash', '160.00', '2.00', 'paid', NULL, '1', '2026-03-21 14:49:26'),
  ('13', '16', 'cash', '80.00', '1.00', 'paid', NULL, '1', '2026-03-21 14:49:34'),
  ('14', '17', 'cash', '119.00', '0.00', 'paid', NULL, '1', '2026-03-21 15:15:27'),
  ('15', '18', 'cash', '200.00', '81.00', 'paid', NULL, '2', '2026-03-21 15:50:35'),
  ('16', '19', 'cash', '700.00', '16.00', 'paid', NULL, '2', '2026-03-21 16:10:53'),
  ('17', '20', 'cash', '700.00', '70.00', 'paid', NULL, '2', '2026-03-21 16:28:03'),
  ('18', '21', 'cash', '500.00', '25.00', 'paid', NULL, '1', '2026-03-21 16:39:17'),
  ('19', '25', 'cash', '200.00', '81.00', 'paid', NULL, '2', '2026-03-21 17:31:12'),
  ('20', '27', 'cash', '120.00', '1.00', 'paid', NULL, '2', '2026-03-21 18:16:27'),
  ('21', '28', 'cash', '120.00', '1.00', 'paid', NULL, '2', '2026-03-21 18:16:39'),
  ('22', '29', 'cash', '100.00', '11.00', 'paid', NULL, '2', '2026-03-21 18:16:48'),
  ('23', '30', 'cash', '80.00', '1.00', 'paid', NULL, '2', '2026-03-21 18:17:03'),
  ('24', '31', 'cash', '80.00', '1.00', 'paid', NULL, '2', '2026-03-21 18:17:09'),
  ('25', '32', 'cash', '80.00', '1.00', 'paid', NULL, '2', '2026-03-21 18:17:16'),
  ('26', '33', 'cash', '80.00', '1.00', 'paid', NULL, '2', '2026-03-21 18:17:24'),
  ('27', '34', 'cash', '80.00', '1.00', 'paid', NULL, '2', '2026-03-21 18:17:30'),
  ('28', '35', 'cash', '70.00', '1.00', 'paid', NULL, '2', '2026-03-21 18:17:36'),
  ('29', '36', 'cash', '100.00', '21.00', 'paid', NULL, '2', '2026-03-21 18:17:50');

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `order_item_addons` (`order_item_addon_id`, `order_item_id`, `addon_id`, `quantity`, `price`) VALUES
  ('1', '34', '10', '1', '15.00'),
  ('2', '46', '3', '1', '15.00'),
  ('3', '47', '1', '1', '20.00');

SET FOREIGN_KEY_CHECKS = 1;
