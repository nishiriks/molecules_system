-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 23, 2025 at 07:01 AM
-- Server version: 8.0.43
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `molecules_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_admin_log`
--

DROP TABLE IF EXISTS `tbl_admin_log`;
CREATE TABLE IF NOT EXISTS `tbl_admin_log` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `log_action` varchar(255) NOT NULL,
  `log_date` datetime DEFAULT NULL,
  PRIMARY KEY (`log_id`),
  KEY `admin log_idx` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tbl_admin_log`
--

INSERT INTO `tbl_admin_log` (`log_id`, `user_id`, `log_action`, `log_date`) VALUES
(1, 9, 'Login', '2025-10-02 05:37:25'),
(2, 1, 'Login', '2025-10-02 07:40:31'),
(3, 1, 'Change Account Type: Angelique Mae Gabriel (ID: 8) - Changed from Faculty to Student', '2025-10-02 08:04:42'),
(4, 1, 'Add Holiday: New Year\'s Day (ID: 9)', '2025-10-02 08:47:24'),
(5, 1, 'Edit Holiday: New Year\'s Day (ID: 9)', '2025-10-02 08:47:43'),
(6, 1, 'Edit Holiday: New Year\'s Eve (ID: 8)', '2025-10-02 09:10:08'),
(7, 1, 'Edit Holiday: New Year\'s Eve (ID: 8)', '2025-10-02 09:10:12'),
(8, 1, 'Login', '2025-10-16 15:14:17'),
(9, 9, 'Login', '2025-10-16 15:58:59'),
(10, 1, 'Change Account Type: Renz Matthew Magsakay (ID: 2) - Changed from Student to Faculty', '2025-10-16 16:07:47'),
(11, 1, 'Deactivated User: Renz Matthew Magsakay (ID: 2)', '2025-10-16 17:23:19'),
(12, 1, 'Deactivated User: Krizia Lleva (ID: 3)', '2025-10-16 17:45:14'),
(13, 1, 'Activated User: Krizia Lleva (ID: 3)', '2025-10-16 17:45:38'),
(14, 1, 'Deactivated User: Krizia Lleva (ID: 3)', '2025-10-16 17:45:40'),
(15, 1, 'Activated User: Krizia Lleva (ID: 3)', '2025-10-16 17:46:06'),
(16, 1, 'Deactivated User: Renz Matthew Magsakay (ID: 2)', '2025-10-16 17:50:04'),
(17, 1, 'Deactivated User: Kevin Kaslana (ID: 11)', '2025-10-16 18:03:58'),
(18, 9, 'Login', '2025-10-16 22:21:18'),
(19, 9, 'Login', '2025-10-17 13:32:26'),
(20, 9, 'Login', '2025-10-17 13:46:57'),
(21, 9, 'Login', '2025-10-17 13:48:01'),
(22, 1, 'Login', '2025-10-17 13:49:43'),
(23, 9, 'Login', NULL),
(24, 9, 'Login', NULL),
(25, 9, 'Login', NULL),
(26, 9, 'Login', NULL),
(27, 9, 'Login', NULL),
(28, 9, 'Login', NULL),
(29, 9, 'Login', NULL),
(30, 9, 'Login', NULL),
(31, 9, 'Login', NULL),
(32, 9, 'Login', NULL),
(33, 9, 'Login', NULL),
(34, 1, 'Login', NULL),
(35, 1, 'Login', NULL),
(36, 9, 'Login', NULL),
(37, 9, 'Login', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_cart`
--

DROP TABLE IF EXISTS `tbl_cart`;
CREATE TABLE IF NOT EXISTS `tbl_cart` (
  `cart_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `cart_status` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`cart_id`),
  KEY `cart_user_id_idx` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tbl_cart`
--

INSERT INTO `tbl_cart` (`cart_id`, `user_id`, `cart_status`) VALUES
(1, 2, 'Unused'),
(2, 3, 'Unused'),
(3, 4, 'Unused'),
(4, 8, 'pending'),
(5, 8, 'pending'),
(6, 8, 'pending'),
(7, 8, 'pending'),
(8, 8, 'pending'),
(9, 8, 'pending'),
(10, 8, 'pending'),
(11, 8, 'pending'),
(12, 8, 'pending'),
(13, 8, 'pending'),
(14, 8, 'pending'),
(15, 8, 'pending'),
(16, 8, 'pending'),
(17, 8, 'pending'),
(18, 8, 'pending'),
(19, 8, 'pending'),
(20, 8, 'pending'),
(21, 8, 'pending'),
(22, 8, 'pending'),
(23, 8, 'pending'),
(24, 8, 'pending'),
(25, 8, 'pending'),
(26, 8, 'pending'),
(27, 8, 'active'),
(28, 11, 'Used'),
(29, 11, 'Used'),
(30, 11, 'Used');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_cart_items`
--

DROP TABLE IF EXISTS `tbl_cart_items`;
CREATE TABLE IF NOT EXISTS `tbl_cart_items` (
  `item_id` int NOT NULL AUTO_INCREMENT,
  `cart_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `amount` int DEFAULT NULL,
  `report_status` varchar(50) DEFAULT NULL,
  `report_qty` int DEFAULT NULL,
  PRIMARY KEY (`item_id`),
  KEY `cart_item_idx` (`cart_id`),
  KEY `item_idx` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tbl_cart_items`
--

INSERT INTO `tbl_cart_items` (`item_id`, `cart_id`, `product_id`, `amount`, `report_status`, `report_qty`) VALUES
(1, 1, 1, 10, NULL, NULL),
(2, 2, 2, 1, NULL, NULL),
(3, 1, 2, 1, NULL, NULL),
(15, 5, 6, 1, NULL, NULL),
(20, 4, 2, 1, NULL, NULL),
(22, 6, 5, 1, NULL, NULL),
(25, 9, 5, 1, NULL, NULL),
(26, 10, 5, 1, NULL, NULL),
(27, 11, 5, 1, NULL, NULL),
(28, 12, 4, 1, NULL, NULL),
(29, 13, 5, 1, NULL, NULL),
(30, 14, 5, 1, NULL, NULL),
(31, 15, 5, 1, NULL, NULL),
(32, 16, 5, 1, NULL, NULL),
(33, 17, 4, 1, NULL, NULL),
(34, 18, 4, 1, NULL, NULL),
(36, 20, 6, 1, NULL, NULL),
(37, 21, 3, 1, NULL, NULL),
(38, 22, 5, 1, NULL, NULL),
(39, 23, 5, 1, NULL, NULL),
(40, 24, 5, 1, NULL, NULL),
(41, 25, 6, 1, NULL, NULL),
(42, 26, 1, 1, NULL, NULL),
(43, 26, 1, 1, NULL, NULL),
(44, 26, 3, 1, NULL, NULL),
(45, 26, 4, 1, NULL, NULL),
(61, 28, 3, 2, NULL, NULL),
(65, 29, 3, 1, NULL, NULL),
(73, 30, 3, 5, NULL, NULL),
(74, 30, 2, 2, 'Paid', 2),
(75, 30, 5, 1, 'Paid', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_holidays`
--

DROP TABLE IF EXISTS `tbl_holidays`;
CREATE TABLE IF NOT EXISTS `tbl_holidays` (
  `holiday_id` int NOT NULL AUTO_INCREMENT,
  `holiday_name` varchar(255) NOT NULL,
  `holiday_date_from` date NOT NULL,
  `holiday_date_to` date NOT NULL,
  `holiday_type` varchar(255) NOT NULL,
  PRIMARY KEY (`holiday_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tbl_holidays`
--

INSERT INTO `tbl_holidays` (`holiday_id`, `holiday_name`, `holiday_date_from`, `holiday_date_to`, `holiday_type`) VALUES
(1, 'All Saint\'s Day', '2025-11-01', '2025-11-01', 'Recurring Holiday'),
(2, 'All Souls\' Day', '2025-11-02', '2025-11-02', 'Recurring Holiday'),
(4, 'Christmas Day', '2025-12-25', '2025-12-25', 'Recurring Holiday'),
(5, 'Christmas Eve', '2025-12-24', '2025-12-24', 'Recurring Holiday'),
(8, 'New Year\'s Eve', '2024-12-31', '2024-12-31', 'Recurring Holiday'),
(9, 'New Year\'s Day', '2026-01-01', '2026-01-01', 'Recurring Holiday');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_inventory`
--

DROP TABLE IF EXISTS `tbl_inventory`;
CREATE TABLE IF NOT EXISTS `tbl_inventory` (
  `product_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `stock` int DEFAULT NULL,
  `measure_unit` varchar(45) DEFAULT NULL,
  `product_type` varchar(45) DEFAULT NULL,
  `is_consumables` tinyint(1) NOT NULL,
  PRIMARY KEY (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tbl_inventory`
--

INSERT INTO `tbl_inventory` (`product_id`, `name`, `image_path`, `stock`, `measure_unit`, `product_type`, `is_consumables`) VALUES
(1, 'Hydrochloric Acid (37%)', './resource/img/hydrochloric-acid.jpg', 15, 'ml', 'Chemical', 1),
(2, 'Microscope', './resource/img/microscope.jpg', 4, 'units', 'Equipment', 0),
(3, 'Formaldehyde', './resource/img/formaldehyde.jpg', 98, 'ml', 'Chemical', 1),
(4, 'Blood Sample', './resource/img/blood-sample.jpg', 0, 'ml', 'Specimen', 1),
(5, 'Periodic Table', './resource/img/periodic.jpg', 15, 'units', 'Models', 0),
(6, 'Beakers', './resource/img/beaker.jpg', 0, 'units', 'Supplies', 0),
(8, 'Beaker 25ml', './resource/img/default.png', 52, 'units', 'Apparatus', 0);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_requests`
--

DROP TABLE IF EXISTS `tbl_requests`;
CREATE TABLE IF NOT EXISTS `tbl_requests` (
  `request_id` int NOT NULL AUTO_INCREMENT,
  `cart_id` int DEFAULT NULL,
  `prof_name` varchar(255) DEFAULT NULL,
  `subject` varchar(45) DEFAULT NULL,
  `date_from` datetime DEFAULT NULL,
  `date_to` datetime DEFAULT NULL,
  `time_from` varchar(45) DEFAULT NULL,
  `time_to` varchar(45) DEFAULT NULL,
  `room` varchar(45) DEFAULT NULL,
  `status` varchar(45) DEFAULT NULL,
  `request_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `remarks` mediumtext,
  PRIMARY KEY (`request_id`),
  KEY `cart_idx` (`cart_id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tbl_requests`
--

INSERT INTO `tbl_requests` (`request_id`, `cart_id`, `prof_name`, `subject`, `date_from`, `date_to`, `time_from`, `time_to`, `room`, `status`, `request_date`, `remarks`) VALUES
(1, 1, 'Gelai Gabriel', 'Biology', '2025-08-31 00:00:00', '2025-09-01 00:00:00', '10:00:00', '12:00:00', 'PHL 301', 'Submitted', '2025-09-21 23:15:21', NULL),
(4, NULL, 'ewan ko', 'Chemistry', '2025-09-08 00:00:00', '2025-09-08 00:00:00', '04:03', '05:03', '301', 'Completed', '2025-09-21 23:15:21', NULL),
(5, NULL, 'ewannn', 'Chemistry', '2025-09-09 00:00:00', '2025-09-09 00:00:00', '10:00', '11:00', '301', 'Completed', '2025-09-21 23:15:21', NULL),
(13, 9, 'dsdasdasdasd', 'dsdasdsdasdasd', '2025-10-04 00:00:00', '2025-10-06 00:00:00', '07:01', '18:01', 'dsadasdad', 'pending', '2025-10-02 11:31:27', NULL),
(14, 10, 'Aki', 'Bio', '2025-10-04 00:00:00', '2025-10-06 00:00:00', '11:37', '11:37', 'phl123', 'pending', '2025-10-02 11:37:19', NULL),
(15, 11, 'test1', 'test1', '2025-10-04 00:00:00', '2025-10-04 00:00:00', '11:38', '11:38', 'test1', 'pending', '2025-10-02 11:38:59', NULL),
(16, 12, 'test2', 'test2', '2025-12-12 00:00:00', '2025-12-12 00:00:00', '12:01', '12:01', 'test2', 'pending', '2025-10-02 12:01:11', NULL),
(17, 13, 're', 're', '2025-10-04 00:00:00', '2025-10-04 00:00:00', '12:10', '12:10', 're', 'pending', '2025-10-02 12:10:24', NULL),
(18, 14, 'rere', 'rerer', '2025-10-04 00:00:00', '2025-10-04 00:00:00', '12:14', '12:14', 'rerer', 'pending', '2025-10-02 12:14:19', NULL),
(19, 15, 'rerererr', 'errererere', '2025-10-04 00:00:00', '2025-10-04 00:00:00', '12:17', '12:17', 'rereerrer', 'pending', '2025-10-02 12:17:12', NULL),
(20, 16, 'wqqqw', 'wqqwqw', '2025-10-04 00:00:00', '2025-10-04 00:00:00', '12:18', '12:18', 'wqwq', 'Submitted', '2025-10-02 12:18:29', NULL),
(22, 18, 'dsadsa', 'dasdsadas', '2025-12-12 00:00:00', '2025-12-12 00:00:00', '12:21', '12:21', 'sdada', 'pending', '2025-10-02 12:21:53', NULL),
(24, 20, 'dsadsad', 'sadad', '2025-10-02 00:00:00', '2025-10-02 00:00:00', '12:23', '12:23', 'dsadasd', 'pending', '2025-10-02 12:23:15', NULL),
(26, 22, 'dsad', 'daasd', '2025-10-04 00:00:00', '2025-10-04 00:00:00', '12:31', '12:31', 'dsada', 'pending', '2025-10-02 12:31:22', NULL),
(27, 23, 'dsa', 'dsa', '2025-10-04 00:00:00', '2025-10-04 00:00:00', '12:40', '12:40', 'dsa', 'pending', '2025-10-02 12:40:40', NULL),
(28, 24, 'dsad', 'sad', '2025-10-04 00:00:00', '2025-10-04 00:00:00', '12:41', '12:41', 'sda', 'pending', '2025-10-02 12:41:15', NULL),
(29, 25, 'dsda', 'dsadas', '2025-10-16 00:00:00', '2025-10-18 00:00:00', '11:16', '11:16', 'dasda', 'pending', '2025-10-16 23:16:34', NULL),
(30, 26, 'asdsd', 'sdsd', '2026-01-07 00:00:00', '2026-01-08 00:00:00', '11:00', '12:00', 'sadsad', 'pending', '2025-10-17 22:22:18', NULL),
(31, 28, 'test2', 'test2', '2025-10-24 00:00:00', '2025-10-24 00:00:00', '12:54', '12:54', 'dsadas', 'Pending', '2025-10-23 00:54:56', NULL),
(32, 29, 'aki123', 'aki123', '2025-11-20 00:00:00', '2025-11-21 00:00:00', '13:32', '13:32', 'aki123', 'Submitted', '2025-10-23 01:32:17', 'dasdsddadadsadsdadsdadasddadadasdasdaddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddd'),
(33, 30, 'reptest', 'reptest', '2025-10-25 00:00:00', '2025-10-25 00:00:00', '12:39', '12:39', 'reptest', 'Completed', '2025-10-23 12:39:24', 'The following items must be replaced or paid for at the Cashier\'s office before the end of the semester: Microscope (Equipment) - 2 units, Periodic Table (Models) - 1 units.');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_request_forms`
--

DROP TABLE IF EXISTS `tbl_request_forms`;
CREATE TABLE IF NOT EXISTS `tbl_request_forms` (
  `form_id` int NOT NULL AUTO_INCREMENT,
  `request_id` int DEFAULT NULL,
  `form_img` varbinary(255) DEFAULT NULL,
  PRIMARY KEY (`form_id`),
  KEY `requestid_idx` (`request_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tbl_request_forms`
--

INSERT INTO `tbl_request_forms` (`form_id`, `request_id`, `form_img`) VALUES
(1, 1, 0x31);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_users`
--

DROP TABLE IF EXISTS `tbl_users`;
CREATE TABLE IF NOT EXISTS `tbl_users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(45) NOT NULL,
  `last_name` varchar(45) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `account_type` varchar(45) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tbl_users`
--

INSERT INTO `tbl_users` (`user_id`, `first_name`, `last_name`, `email`, `password`, `account_type`, `is_active`) VALUES
(1, 'John Rexlester', 'Ramos', 'jtramos@ceu.edu.ph', '$2y$10$UOhqxA20qxvc41ncwKw17ev0CdZTK1rjiGyfDfWg2ytDYRY9hZTJe', 'Super Admin', 1),
(2, 'Renz Matthew', 'Magsakay', 'magsaakay1234567@mls.ceu.edu.ph', 'magsakay', 'Faculty', 0),
(3, 'Krizia', 'Lleva', 'lleva1234567@mls.ceu.edu.ph', 'lleva', 'Student', 1),
(4, 'Gelai', 'Gabriel', 'gabriel1234567@mls.ceu.edu.ph', 'gelai', 'Student', 1),
(8, 'Angelique Mae', 'Gabriel', 'gabriel2231439@mls.ceu.edu.ph', '$2y$10$IemiGgCpesJqAdUdp7iKzOr8QkkgEq.qgyFmDXOaHEXI52BNvEIDC', 'Student', 1),
(9, 'Kim', 'Sacdalan', 'kbsacdalan@ceu.edu.ph', '$2y$10$HQqY.ym7ivVKb.SpIW1JYepVmxAds6Hs1JN6/N3YMC7ENkDpBx/1G', 'Admin', 1),
(11, 'Kevin', 'Kaslana', 'phainon@mls.ceu.edu.ph', '$2y$10$W6ntbzRyNU7VAHBbpWP8yOQ0v8q6F9krEKfCUn9MKIs8aV8fNPOm2', 'Student', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user_log`
--

DROP TABLE IF EXISTS `tbl_user_log`;
CREATE TABLE IF NOT EXISTS `tbl_user_log` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `log_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `user_id_idx` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tbl_user_log`
--

INSERT INTO `tbl_user_log` (`log_id`, `user_id`, `log_date`) VALUES
(1, 8, '2025-10-02 05:36:08'),
(2, 8, '2025-10-02 07:10:18'),
(3, 8, '2025-10-06 07:37:15'),
(6, 8, '2025-10-16 23:14:49'),
(7, 8, '2025-10-16 16:48:24'),
(8, 11, '2025-10-16 18:03:21'),
(9, 8, '2025-10-16 18:58:04'),
(10, 8, '2025-10-17 13:28:21'),
(11, 8, '2025-10-17 13:45:03'),
(12, 8, '2025-10-17 13:45:52'),
(13, 8, '2025-10-17 21:53:22'),
(14, 8, '2025-10-17 22:21:49'),
(15, 8, '2025-10-17 22:56:55'),
(16, 8, '2025-10-22 22:52:18'),
(17, 11, '2025-10-23 00:54:05'),
(18, 11, '2025-10-23 01:27:29'),
(19, 11, '2025-10-23 02:20:18'),
(20, 11, '2025-10-23 03:14:36'),
(21, 11, '2025-10-23 07:09:33'),
(22, 11, '2025-10-23 11:12:43'),
(23, 11, '2025-10-23 12:17:59');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_admin_log`
--
ALTER TABLE `tbl_admin_log`
  ADD CONSTRAINT `admin log` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`);

--
-- Constraints for table `tbl_cart`
--
ALTER TABLE `tbl_cart`
  ADD CONSTRAINT `cart_user_id` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`);

--
-- Constraints for table `tbl_cart_items`
--
ALTER TABLE `tbl_cart_items`
  ADD CONSTRAINT `cart_item` FOREIGN KEY (`cart_id`) REFERENCES `tbl_cart` (`cart_id`),
  ADD CONSTRAINT `item` FOREIGN KEY (`product_id`) REFERENCES `tbl_inventory` (`product_id`);

--
-- Constraints for table `tbl_requests`
--
ALTER TABLE `tbl_requests`
  ADD CONSTRAINT `cart` FOREIGN KEY (`cart_id`) REFERENCES `tbl_cart_items` (`cart_id`);

--
-- Constraints for table `tbl_request_forms`
--
ALTER TABLE `tbl_request_forms`
  ADD CONSTRAINT `requestid` FOREIGN KEY (`request_id`) REFERENCES `tbl_requests` (`request_id`);

--
-- Constraints for table `tbl_user_log`
--
ALTER TABLE `tbl_user_log`
  ADD CONSTRAINT `user_id` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
