-- ============================================================
-- Dine with Din Cafeteria POS — Database Backup
-- Generated : 2026-03-21 03:42:20 (Asia/Manila)
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
  ('1', 'Espresso Shot', '500.00', 'ml', '50.00', '2026-03-21 03:35:40'),
  ('2', 'Arabica Coffee Beans', '5000.00', 'g', '500.00', '2026-03-21 03:35:40'),
  ('3', 'Fresh Milk', '10000.00', 'ml', '1000.00', '2026-03-21 03:35:40'),
  ('4', 'Condensed Milk', '5000.00', 'ml', '500.00', '2026-03-21 03:35:40'),
  ('5', 'Heavy Cream', '3000.00', 'ml', '300.00', '2026-03-21 03:35:40'),
  ('6', 'Oat Milk', '5000.00', 'ml', '500.00', '2026-03-21 03:35:40'),
  ('7', 'White Sugar', '5000.00', 'g', '500.00', '2026-03-21 03:35:40'),
  ('8', 'Brown Sugar', '5000.00', 'g', '500.00', '2026-03-21 03:35:40'),
  ('9', 'Vanilla Syrup', '2000.00', 'ml', '200.00', '2026-03-21 03:35:40'),
  ('10', 'Caramel Syrup', '2000.00', 'ml', '200.00', '2026-03-21 03:35:40'),
  ('11', 'Hazelnut Syrup', '2000.00', 'ml', '200.00', '2026-03-21 03:35:40'),
  ('12', 'Chocolate Syrup', '2000.00', 'ml', '200.00', '2026-03-21 03:35:40'),
  ('13', 'Matcha Powder', '2000.00', 'g', '200.00', '2026-03-21 03:35:40'),
  ('14', 'Chocolate Powder', '2000.00', 'g', '200.00', '2026-03-21 03:35:40'),
  ('15', 'Taro Powder', '2000.00', 'g', '200.00', '2026-03-21 03:35:40'),
  ('16', 'Strawberry Powder', '2000.00', 'g', '200.00', '2026-03-21 03:35:40'),
  ('17', 'Ice', '20000.00', 'g', '2000.00', '2026-03-21 03:35:40'),
  ('18', 'Whipped Cream', '3000.00', 'ml', '300.00', '2026-03-21 03:35:40'),
  ('19', 'Tea Bag (Black)', '299.00', 'pc', '50.00', '2026-03-21 03:37:12'),
  ('20', 'Tea Bag (Green)', '300.00', 'pc', '50.00', '2026-03-21 03:35:40'),
  ('21', 'All-Purpose Flour', '9900.00', 'g', '1000.00', '2026-03-21 03:37:12'),
  ('22', 'Butter', '4950.00', 'g', '500.00', '2026-03-21 03:37:12'),
  ('23', 'Eggs', '99.00', 'pc', '20.00', '2026-03-21 03:37:12'),
  ('24', 'Cream Cheese', '3000.00', 'g', '300.00', '2026-03-21 03:35:40');

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  ('1', 'admin123', '$2y$10$tFeOYK8CmrFMzzymlu0bsOop/Zas3UstA.gSkWoU0HqoIC2edHkCK', 'Shammy Kenan Din', 'dinshammykenan012@gmail.com', 'admin', '1', '2026-03-19 13:48:08', '2026-03-19 13:47:16', '2026-03-19 13:48:08'),
  ('2', 'lyn', '$2y$10$CxznWbBPvrc3qgAQTHN3kuOpHcJatwWiSoFSWugnSYtwZu7KdH2x6', 'Noribisco', 'dinshammykenan999@gmail.com', 'cashier', '1', NULL, '2026-03-21 03:28:25', NULL),
  ('3', 'staff', '$2y$10$iGq0mYjiVbDdcvKMRxUS0uqvIUuzEeDBvNHdehdJOlCTV6yUvqJWK', 'Staff123', 'dinshammykenan07@gmail.com', 'kitchen', '1', NULL, '2026-03-21 03:28:48', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `activity_logs` (`log_id`, `user_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
  ('1', '1', 'forgot_password_request', 'Password reset requested for email: dinshammykenan012@gmail.com', '::1', '2026-03-19 13:47:26'),
  ('2', '1', 'password_reset_success', 'Password successfully reset via email link.', '::1', '2026-03-19 13:47:56');

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `orders` (`order_id`, `order_number`, `cashier_id`, `customer_name`, `table_number`, `order_type`, `order_source`, `subtotal`, `discount`, `total_amount`, `status`, `created_at`, `completed_at`) VALUES
  ('1', 'SKD-20260321-82DF5B', '2', 'sham', NULL, 'takeout', 'walk_in', '148.00', '0.00', '148.00', 'completed', '2026-03-21 03:37:12', '2026-03-21 03:37:51');

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `password_resets` (`reset_id`, `user_id`, `token`, `expires_at`, `used`, `ip_address`, `created_at`) VALUES
  ('1', '1', 'bb732af1dd6b27e8c8117495b521d4815daa7b3092a747ae51e72e2d93b0d3aa', '2026-03-19 14:47:25', '1', '::1', '2026-03-19 13:47:25');

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
  `status` enum('pending','preparing','ready') DEFAULT 'pending',
  PRIMARY KEY (`order_item_id`),
  KEY `order_id` (`order_id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `menu_items` (`item_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `order_items` (`order_item_id`, `order_id`, `item_id`, `quantity`, `unit_price`, `item_notes`, `subtotal`, `status`) VALUES
  ('1', '1', '14', '1', '79.00', NULL, '79.00', 'ready'),
  ('2', '1', '17', '1', '69.00', NULL, '69.00', 'ready');

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `payments` (`payment_id`, `order_id`, `payment_method`, `amount_paid`, `change_amount`, `payment_status`, `transaction_ref`, `cashier_id`, `paid_at`) VALUES
  ('1', '1', 'cash', '150.00', '2.00', 'paid', NULL, '2', '2026-03-21 03:37:51');

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

SET FOREIGN_KEY_CHECKS = 1;
