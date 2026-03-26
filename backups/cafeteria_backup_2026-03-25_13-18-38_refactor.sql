-- ============================================================
-- Dine with Din Cafeteria POS — Database Backup
-- Generated : 2026-03-25 13:18:38 (Asia/Manila)
-- Host      : LAPTOP-PL0A30MQ
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET NAMES utf8mb4;

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
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO `activity_logs` (`log_id`, `user_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
  ('1', '1', 'forgot_password_request', 'Password reset requested for email: dinshammykenan012@gmail.com', '136.158.40.29', '2026-03-21 22:51:45'),
  ('2', '1', 'password_reset_success', 'Password successfully reset via email link.', '136.158.40.29', '2026-03-21 22:52:16'),
  ('3', '2', 'forgot_password_request', 'Password reset requested for email: dinshammykenan999@gmail.com', '136.158.40.29', '2026-03-21 23:13:06'),
  ('4', '1', 'forgot_password_request', 'Password reset requested for email: dinshammykenan012@gmail.com', '136.158.40.29', '2026-03-21 23:13:19'),
  ('5', '1', 'forgot_password_request', 'Password reset requested for email: dinshammykenan012@gmail.com', '136.158.40.29', '2026-03-21 23:24:58');

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
  KEY `ingredient_id` (`ingredient_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO `addons` (`addon_id`, `addon_name`, `price`, `ingredient_id`, `quantity_used`, `is_active`) VALUES
  ('1', 'Extra Shot', '30.00', '1', '18.00', '1'),
  ('2', 'Extra Milk', '20.00', '2', '50.00', '1'),
  ('3', 'Vanilla Syrup', '25.00', '4', '10.00', '1'),
  ('4', 'Chocolate Drizzle', '30.00', '5', '10.00', '1'),
  ('5', 'Whipped Cream', '20.00', '6', '20.00', '1'),
  ('6', 'Caramel Drizzle', '25.00', '7', '10.00', '1'),
  ('7', 'Cheese Extra', '15.00', '11', '10.00', '1'),
  ('8', 'Lettuce Extra', '10.00', '12', '10.00', '1');

-- ----------------------------------------------------------
-- Table: `categories`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `category_name` (`category_name`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO `categories` (`category_id`, `category_name`, `created_at`) VALUES
  ('1', 'Coffee', '2026-03-21 22:45:34'),
  ('2', 'Frappes', '2026-03-21 22:45:34'),
  ('3', 'Tea', '2026-03-21 22:45:34'),
  ('4', 'Pastries', '2026-03-21 22:45:34'),
  ('5', 'Sandwiches', '2026-03-21 22:45:34'),
  ('6', 'Smoothies', '2026-03-21 22:45:34'),
  ('7', 'Breakfast', '2026-03-21 22:45:34'),
  ('8', 'Snacks', '2026-03-21 22:45:34');

-- ----------------------------------------------------------
-- Table: `ingredients`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `ingredients`;
CREATE TABLE `ingredients` (
  `ingredient_id` int(11) NOT NULL AUTO_INCREMENT,
  `ingredient_name` varchar(100) NOT NULL,
  `stock_quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `unit_id` int(11) DEFAULT NULL,
  `low_stock_alert` decimal(10,2) DEFAULT 10.00,
  `last_updated` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`ingredient_id`),
  UNIQUE KEY `ingredient_name` (`ingredient_name`),
  KEY `unit_id` (`unit_id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO `ingredients` (`ingredient_id`, `ingredient_name`, `stock_quantity`, `unit_id`, `low_stock_alert`, `last_updated`) VALUES
  ('1', 'Espresso Shot', '212.00', '2', '50.00', '2026-03-23 04:04:56'),
  ('2', 'Milk', '8150.00', '3', '1000.00', '2026-03-23 04:04:56'),
  ('3', 'Sugar', '1945.00', '2', '200.00', '2026-03-23 04:04:56'),
  ('4', 'Vanilla Syrup', '1000.00', '3', '100.00', '2026-03-21 22:45:34'),
  ('5', 'Chocolate Powder', '480.00', '2', '50.00', '2026-03-23 02:15:57'),
  ('6', 'Whipped Cream', '1970.00', '3', '200.00', '2026-03-23 02:15:57'),
  ('7', 'Caramel Sauce', '970.00', '3', '100.00', '2026-03-23 02:15:57'),
  ('8', 'Ice Cubes', '4995.00', '1', '500.00', '2026-03-23 02:15:57'),
  ('9', 'Bread Slice', '496.00', '1', '50.00', '2026-03-23 02:19:55'),
  ('10', 'Egg', '200.00', '1', '20.00', '2026-03-21 22:45:34'),
  ('11', 'Cheese', '230.00', '2', '30.00', '2026-03-23 02:19:55'),
  ('12', 'Lettuce', '960.00', '2', '100.00', '2026-03-23 02:19:55');

-- ----------------------------------------------------------
-- Table: `login_attempts`
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE `login_attempts` (
  `attempt_id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `success` tinyint(1) DEFAULT 0,
  `attempt_time` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`attempt_id`),
  KEY `idx_ip_time` (`ip_address`,`attempt_time`),
  KEY `idx_username_time` (`username`,`attempt_time`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`item_id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO `menu_items` (`item_id`, `item_name`, `description`, `price`, `cost_price`, `category_id`, `image_url`, `is_available`, `created_at`) VALUES
  ('1', 'Espresso', 'Strong and bold espresso shot', '80.00', '20.00', '1', NULL, '1', '2026-03-21 22:45:34'),
  ('2', 'Cappuccino', 'Espresso with steamed milk and foam', '120.00', '40.00', '1', 'assets/images/menu/1774184454_69bfe8067286f.jpeg', '1', '2026-03-21 22:45:34'),
  ('3', 'Mocha Frappe', 'Chocolate blended frappe with coffee', '150.00', '60.00', '2', NULL, '1', '2026-03-21 22:45:34'),
  ('4', 'Caramel Macchiato', 'Espresso with milk and caramel', '140.00', '50.00', '1', NULL, '1', '2026-03-21 22:45:34'),
  ('5', 'Cheese Sandwich', 'Grilled sandwich with cheese and lettuce', '100.00', '40.00', '5', NULL, '1', '2026-03-21 22:45:34'),
  ('6', 'Scrambled Eggs', 'Eggs scrambled with milk and seasoning', '90.00', '30.00', '7', NULL, '1', '2026-03-21 22:45:34');

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
  KEY `addon_id` (`addon_id`)
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO `order_item_addons` (`order_item_addon_id`, `order_item_id`, `addon_id`, `quantity`, `price`) VALUES
  ('1', '1', '7', '1', '15.00'),
  ('2', '76', '2', '1', '20.00'),
  ('3', '78', '2', '1', '20.00'),
  ('4', '80', '2', '1', '20.00'),
  ('5', '82', '2', '1', '20.00'),
  ('6', '84', '2', '1', '20.00'),
  ('7', '86', '2', '1', '20.00'),
  ('8', '88', '2', '1', '20.00'),
  ('9', '90', '2', '1', '20.00'),
  ('10', '93', '2', '1', '20.00'),
  ('11', '97', '2', '1', '20.00'),
  ('12', '100', '1', '1', '30.00'),
  ('13', '102', '2', '1', '20.00'),
  ('14', '105', '1', '1', '30.00'),
  ('15', '107', '2', '1', '20.00'),
  ('16', '110', '1', '1', '30.00'),
  ('17', '112', '2', '1', '20.00'),
  ('18', '115', '1', '1', '30.00'),
  ('19', '117', '2', '1', '20.00'),
  ('20', '120', '1', '1', '30.00'),
  ('21', '122', '2', '1', '20.00'),
  ('22', '125', '1', '1', '30.00'),
  ('23', '127', '2', '1', '20.00'),
  ('24', '130', '1', '1', '30.00'),
  ('25', '132', '2', '1', '20.00'),
  ('26', '135', '1', '1', '30.00'),
  ('27', '137', '2', '1', '20.00'),
  ('28', '140', '1', '1', '30.00'),
  ('29', '142', '2', '1', '20.00'),
  ('30', '145', '1', '1', '30.00');

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
  KEY `item_id` (`item_id`)
) ENGINE=MyISAM AUTO_INCREMENT=161 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO `order_items` (`order_item_id`, `order_id`, `item_id`, `quantity`, `unit_price`, `item_notes`, `subtotal`, `status`) VALUES
  ('1', '1', '5', '1', '100.00', 'I want the bread to be toasted', '100.00', 'completed'),
  ('2', '2', '1', '1', '80.00', NULL, '80.00', 'completed'),
  ('3', '3', '1', '1', '80.00', NULL, '80.00', 'preparing'),
  ('4', '4', '1', '1', '80.00', NULL, '80.00', 'cancelled'),
  ('5', '5', '4', '1', '140.00', NULL, '140.00', 'completed'),
  ('20', '6', '1', '1', '80.00', 'hehe', '80.00', 'pending'),
  ('15', '7', '1', '1', '80.00', 'hehe', '80.00', 'pending'),
  ('21', '8', '1', '1', '80.00', 'hehe', '80.00', 'pending'),
  ('43', '9', '2', '1', '120.00', 'Less ice', '120.00', 'completed'),
  ('45', '10', '2', '1', '120.00', 'Less ice', '120.00', 'pending'),
  ('60', '11', '3', '1', '150.00', 'swswsswsw', '150.00', 'pending'),
  ('58', '11', '1', '1', '80.00', NULL, '80.00', 'pending'),
  ('59', '11', '5', '1', '100.00', NULL, '100.00', 'pending'),
  ('73', '12', '3', '1', '150.00', NULL, '150.00', 'pending'),
  ('72', '12', '1', '1', '80.00', NULL, '80.00', 'pending'),
  ('145', '13', '1', '1', '80.00', 'extra ice', '80.00', 'pending'),
  ('146', '14', '5', '1', '100.00', NULL, '100.00', 'pending'),
  ('144', '13', '6', '1', '90.00', 'No saltttt', '90.00', 'pending'),
  ('143', '13', '6', '1', '90.00', NULL, '90.00', 'pending'),
  ('142', '13', '4', '1', '140.00', NULL, '140.00', 'pending'),
  ('141', '13', '4', '1', '140.00', 'Extra', '140.00', 'pending'),
  ('148', '15', '4', '1', '140.00', 'super extra spicy', '140.00', 'pending'),
  ('149', '20', '3', '1', '150.00', NULL, '150.00', 'completed'),
  ('150', '20', '4', '1', '140.00', NULL, '140.00', 'completed'),
  ('151', '21', '2', '2', '120.00', NULL, '240.00', 'completed'),
  ('152', '21', '5', '1', '100.00', NULL, '100.00', 'completed'),
  ('153', '21', '1', '1', '80.00', NULL, '80.00', 'completed'),
  ('154', '23', '2', '1', '120.00', NULL, '120.00', 'completed'),
  ('155', '25', '2', '1', '120.00', 'no coffee', '120.00', 'completed'),
  ('156', '27', '2', '1', '120.00', 'hahahahwhwha', '120.00', 'completed'),
  ('157', '29', '2', '1', '120.00', 'error guysss', '120.00', 'completed'),
  ('158', '31', '2', '1', '120.00', 'sana wala na bug', '120.00', 'completed'),
  ('159', '33', '2', '1', '120.00', 'sleeepeeee', '120.00', 'completed'),
  ('160', '35', '2', '1', '120.00', 'sana wala na bugsssss', '120.00', 'completed');

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
  `table_id` int(11) DEFAULT NULL,
  `order_type` enum('dine_in','takeout') DEFAULT 'dine_in',
  `order_source` enum('walk_in','online') DEFAULT 'walk_in',
  `subtotal` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('draft','pending','preparing','ready','completed','cancelled') DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  `draft_cart` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`draft_cart`)),
  PRIMARY KEY (`order_id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `cashier_id` (`cashier_id`),
  KEY `table_id` (`table_id`)
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO `orders` (`order_id`, `order_number`, `cashier_id`, `customer_name`, `table_number`, `table_id`, `order_type`, `order_source`, `subtotal`, `discount`, `total_amount`, `status`, `created_at`, `completed_at`, `draft_cart`) VALUES
  ('1', 'SKD-20260322-4DD623', '1', NULL, 'Table 1', '1', 'dine_in', 'walk_in', '115.00', '0.00', '115.00', 'completed', '2026-03-21 23:05:56', '2026-03-21 23:08:45', NULL),
  ('2', 'SKD-20260322-B8B3A1', '2', 'Shammy', 'Table 1', '1', 'dine_in', 'walk_in', '80.00', '0.00', '80.00', 'completed', '2026-03-22 03:17:48', '2026-03-22 03:25:49', NULL),
  ('3', 'SKD-20260322-53E955', '1', 'sham', NULL, NULL, 'takeout', 'walk_in', '80.00', '0.00', '80.00', 'preparing', '2026-03-22 03:28:53', NULL, NULL),
  ('4', 'SKD-20260322-397224', '1', 'dnendeiiendk', NULL, NULL, 'takeout', 'walk_in', '80.00', '0.00', '80.00', 'cancelled', '2026-03-22 18:30:27', NULL, NULL),
  ('5', 'SKD-20260322-B8B5E9', '2', 'swsws', NULL, NULL, 'takeout', 'walk_in', '140.00', '0.00', '140.00', 'completed', '2026-03-22 18:30:51', '2026-03-22 18:32:09', NULL),
  ('9', 'DFT-20260322-A36E1E', '1', 'shammykenandin', NULL, NULL, 'dine_in', 'walk_in', '0.00', '0.00', '0.00', 'completed', '2026-03-22 21:10:18', '2026-03-22 21:13:42', NULL),
  ('8', 'SKD-20260322-6D470C', '1', 'sham', 'Table 2', '2', 'dine_in', 'walk_in', '80.00', '0.00', '80.00', 'pending', '2026-03-22 21:09:26', NULL, NULL),
  ('27', 'SKD-20260323-7AA4D4', '2', 'huwhahashaha', NULL, NULL, 'takeout', 'walk_in', '120.00', '0.00', '120.00', 'completed', '2026-03-23 03:22:47', '2026-03-23 04:00:53', NULL),
  ('29', 'SKD-20260323-E7CBAC', '2', 'errorrrre', NULL, NULL, 'takeout', 'walk_in', '120.00', '0.00', '120.00', 'completed', '2026-03-23 03:28:14', '2026-03-23 04:01:01', NULL),
  ('31', 'SKD-20260323-F04AD0', '2', 'buggggsss', NULL, NULL, 'takeout', 'walk_in', '120.00', '0.00', '120.00', 'completed', '2026-03-23 03:57:35', '2026-03-23 04:01:07', NULL),
  ('21', 'SKD-20260323-B2B2B3', '1', 'sham', NULL, NULL, 'takeout', 'walk_in', '420.00', '0.00', '420.00', 'completed', '2026-03-23 02:19:55', '2026-03-23 04:00:39', NULL),
  ('23', 'SKD-20260323-4E041C', '2', 'cashier', NULL, NULL, 'takeout', 'walk_in', '120.00', '0.00', '120.00', 'completed', '2026-03-23 02:20:20', '2026-03-23 04:00:45', NULL),
  ('33', 'SKD-20260323-BB0663', '2', 'sleep', NULL, NULL, 'takeout', 'walk_in', '120.00', '0.00', '120.00', 'completed', '2026-03-23 03:59:23', '2026-03-23 04:00:19', NULL),
  ('25', 'SKD-20260323-178800', '2', 'Shammy Kenan Din', 'Table 1', '1', 'dine_in', 'walk_in', '120.00', '0.00', '120.00', 'completed', '2026-03-23 02:40:01', '2026-03-23 02:40:45', NULL),
  ('20', 'SKD-20260323-DD7F63', '2', 'cashier1', 'Table 3', '3', 'dine_in', 'walk_in', '290.00', '0.00', '290.00', 'completed', '2026-03-23 02:15:57', '2026-03-23 02:16:38', NULL),
  ('35', 'SKD-20260323-843C33', '1', 'shambugss', NULL, NULL, 'takeout', 'walk_in', '120.00', '0.00', '120.00', 'completed', '2026-03-23 04:04:56', '2026-03-23 04:05:14', NULL);

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
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`reset_id`),
  UNIQUE KEY `token` (`token`),
  KEY `idx_token` (`token`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_ip_created` (`ip_address`,`created_at`),
  KEY `idx_expires` (`expires_at`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO `password_resets` (`reset_id`, `user_id`, `token`, `expires_at`, `used`, `ip_address`, `created_at`) VALUES
  ('1', '1', 'd50f35e1d788ec3e5eed915a081033df866c28ca8d1907af3fa33e31094ccd56', '2026-03-21 23:51:45', '1', '136.158.40.29', '2026-03-21 22:51:45'),
  ('2', '2', '6dd92901a04c89ae66235cecb59bb4bcfcadf04d3b08a6db7e7890f1593d612f', '2026-03-22 00:13:06', '0', '136.158.40.29', '2026-03-21 23:13:06'),
  ('3', '1', '207a43c1e8897b5743b26f7b5f3a72d84bc440bf7554d05239af4ff72300e792', '2026-03-22 00:13:18', '1', '136.158.40.29', '2026-03-21 23:13:18'),
  ('4', '1', '0f3995b6c1b8021ddd7220ea77050b68ec04e03413a20c128ee548601b655102', '2026-03-22 00:24:58', '0', '136.158.40.29', '2026-03-21 23:24:58');

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
  `paid_at` timestamp NULL DEFAULT current_timestamp(),
  `processed_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`payment_id`),
  KEY `order_id` (`order_id`),
  KEY `cashier_id` (`cashier_id`),
  KEY `fk_payments_processed_by` (`processed_by`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO `payments` (`payment_id`, `order_id`, `payment_method`, `amount_paid`, `change_amount`, `payment_status`, `transaction_ref`, `cashier_id`, `paid_at`, `processed_by`) VALUES
  ('1', '1', 'cash', '120.00', '5.00', 'paid', NULL, '1', '2026-03-21 23:08:45', NULL),
  ('2', '2', 'cash', '80.00', '0.00', 'paid', NULL, '2', '2026-03-22 03:25:49', NULL),
  ('3', '5', 'cash', '150.00', '10.00', 'paid', NULL, '2', '2026-03-22 18:32:09', NULL),
  ('4', '9', 'cash', '100.00', '100.00', 'paid', NULL, '1', '2026-03-22 21:13:42', NULL),
  ('5', '20', 'cash', '300.00', '10.00', 'paid', NULL, '2', '2026-03-23 02:16:38', '1'),
  ('6', '25', 'cash', '150.00', '30.00', 'paid', NULL, '2', '2026-03-23 02:40:45', '1'),
  ('7', '33', 'cash', '120.00', '0.00', 'paid', NULL, '2', '2026-03-23 04:00:19', '2'),
  ('8', '21', 'cash', '500.00', '80.00', 'paid', NULL, '1', '2026-03-23 04:00:39', '1'),
  ('9', '23', 'cash', '150.00', '30.00', 'paid', NULL, '2', '2026-03-23 04:00:45', '1'),
  ('10', '27', 'cash', '120.00', '0.00', 'paid', NULL, '2', '2026-03-23 04:00:53', '1'),
  ('11', '29', 'cash', '150.00', '30.00', 'paid', NULL, '2', '2026-03-23 04:01:01', '1'),
  ('12', '31', 'cash', '120.00', '0.00', 'paid', NULL, '2', '2026-03-23 04:01:07', '1'),
  ('13', '35', 'cash', '150.00', '30.00', 'paid', NULL, '1', '2026-03-23 04:05:14', '1');

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
  KEY `ingredient_id` (`ingredient_id`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO `recipes` (`recipe_id`, `item_id`, `ingredient_id`, `quantity_required`) VALUES
  ('1', '1', '1', '18.00'),
  ('2', '2', '1', '18.00'),
  ('3', '2', '2', '150.00'),
  ('4', '2', '3', '5.00'),
  ('5', '3', '1', '18.00'),
  ('6', '3', '5', '20.00'),
  ('7', '3', '2', '200.00'),
  ('8', '3', '8', '5.00'),
  ('9', '3', '6', '30.00'),
  ('10', '4', '1', '18.00'),
  ('11', '4', '2', '150.00'),
  ('12', '4', '7', '15.00'),
  ('13', '4', '3', '5.00'),
  ('14', '5', '9', '2.00'),
  ('15', '5', '11', '30.00'),
  ('16', '5', '12', '20.00'),
  ('17', '6', '10', '2.00'),
  ('18', '6', '2', '30.00');

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
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`table_id`),
  UNIQUE KEY `table_name` (`table_name`),
  KEY `fk_tables_current_order` (`current_order_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO `tables` (`table_id`, `table_name`, `capacity`, `status`, `current_order_id`, `updated_at`) VALUES
  ('1', 'Table 1', '2', 'occupied', '25', '2026-03-23 02:40:01'),
  ('2', 'Table 2', '2', 'occupied', '8', '2026-03-22 21:09:26'),
  ('3', 'Table 3', '4', 'occupied', '20', '2026-03-23 02:15:57');

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
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO `units` (`unit_id`, `unit_code`, `unit_label`, `unit_icon`, `is_active`, `created_at`) VALUES
  ('1', 'pcs', 'Piece', 'fa-box', '1', '2026-03-21 22:45:34'),
  ('2', 'g', 'Gram', 'fa-weight-hanging', '1', '2026-03-21 22:45:34'),
  ('3', 'ml', 'Milliliter', 'fa-flask', '1', '2026-03-21 22:45:34'),
  ('4', 'cup', 'Cup', 'fa-cup-hot', '1', '2026-03-21 22:45:34');

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
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO `users` (`user_id`, `username`, `password_hash`, `full_name`, `email`, `role`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
  ('1', 'admin', '$2y$10$iwyVIsVb.vDSEVVGidRwI.TmJz4TILUsMksp0cKqfg7jGsOznyKCC', 'Admin', 'dinshammykenan012@gmail.com', 'admin', '1', '2026-03-25 12:51:19', '2026-03-21 22:22:58', '2026-03-25 12:51:19'),
  ('2', 'cashier', '$2y$10$a2b79EaLyjzf557D1wvzvOA6Np0Ez2WkVQQL2imrWfqMg/mCg9qmq', 'Cashier', 'dinshammykenan999@gmail.com', 'cashier', '1', '2026-03-23 03:57:15', '2026-03-21 22:58:29', '2026-03-23 03:57:15'),
  ('3', 'staff', '$2y$10$rfeS3qUB8cDGPW6eEOdVoeEKhWW3ZrALCwSWBmkqtSICNGrp3asRC', 'Kitchen Staff', 'dinshammykenan07@gmail.com', 'kitchen', '1', '2026-03-23 03:23:52', '2026-03-21 22:58:59', '2026-03-23 03:23:52');

SET FOREIGN_KEY_CHECKS = 1;
