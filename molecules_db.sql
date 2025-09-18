-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 10, 2025 at 06:03 PM
-- Server version: 8.0.31
-- PHP Version: 8.0.26

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
  `log_date` datetime DEFAULT NULL,
  PRIMARY KEY (`log_id`),
  KEY `admin log_idx` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tbl_cart`
--

INSERT INTO `tbl_cart` (`cart_id`, `user_id`, `cart_status`) VALUES
(1, 2, 'Unused'),
(2, 3, 'Unused'),
(3, 4, 'Unused'),
(4, 8, 'active');

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
  PRIMARY KEY (`item_id`),
  KEY `cart_item_idx` (`cart_id`),
  KEY `item_idx` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tbl_cart_items`
--

INSERT INTO `tbl_cart_items` (`item_id`, `cart_id`, `product_id`, `amount`) VALUES
(1, 1, 1, 10),
(2, 2, 2, 1),
(3, 1, 2, 1),
(6, 4, 3, 5),
(9, 4, 2, 2),
(10, 4, 1, 20),
(13, 4, 3, 1),
(14, 4, 5, 1);

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
  PRIMARY KEY (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tbl_inventory`
--

INSERT INTO `tbl_inventory` (`product_id`, `name`, `image_path`, `stock`, `measure_unit`, `product_type`) VALUES
(1, 'Hydrochloric Acid (37%)', './resource/img/hydrochloric-acid.jpg', 35, 'ml', 'Chemical'),
(2, 'Microscope', './resource/img/microscope.jpg', 5, 'units', 'Equipment'),
(3, 'Formaldehyde', './resource/img/formaldehyde.jpg', 36, 'ml', 'Chemical'),
(4, 'Blood Sample', './resource/img/blood-sample.jpg', 1, 'ml', 'Specimen'),
(5, 'Periodic Table', './resource/img/periodic.jpg', 5, 'units', 'Models'),
(6, 'Beakers', './resource/img/beaker.jpg', 10, 'units', 'Supplies');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_requests`
--

DROP TABLE IF EXISTS `tbl_requests`;
CREATE TABLE IF NOT EXISTS `tbl_requests` (
  `request_id` int NOT NULL AUTO_INCREMENT,
  `cart_id` int DEFAULT NULL,
  `prof_name` varchar(255) DEFAULT NULL,
  `prof_signature` varbinary(255) DEFAULT NULL,
  `subject` varchar(45) DEFAULT NULL,
  `date_from` datetime DEFAULT NULL,
  `date_to` datetime DEFAULT NULL,
  `time_from` varchar(45) DEFAULT NULL,
  `time_to` varchar(45) DEFAULT NULL,
  `room` varchar(45) DEFAULT NULL,
  `tech_name` varchar(255) DEFAULT NULL,
  `status` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`request_id`),
  KEY `cart_idx` (`cart_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tbl_requests`
--

INSERT INTO `tbl_requests` (`request_id`, `cart_id`, `prof_name`, `prof_signature`, `subject`, `date_from`, `date_to`, `time_from`, `time_to`, `room`, `tech_name`, `status`) VALUES
(1, 1, 'Gelai Gabriel', 0x58, 'Biology', '2025-08-31 00:00:00', '2025-09-01 00:00:00', '10:00:00', '12:00:00', 'PHL 301', '', 'Received'),
(4, NULL, 'ewan ko', 0x706174682f746f2f73617665645f7369676e61747572652e706e67, 'Chemistry', '2025-09-08 00:00:00', '2025-09-08 00:00:00', '04:03', '05:03', '301', NULL, 'Pending'),
(5, NULL, 'ewannn', 0x706174682f746f2f73617665645f7369676e61747572652e706e67, 'Chemistry', '2025-09-09 00:00:00', '2025-09-09 00:00:00', '10:00', '11:00', '301', NULL, 'Pending');

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
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tbl_users`
--

INSERT INTO `tbl_users` (`user_id`, `first_name`, `last_name`, `email`, `password`, `account_type`) VALUES
(2, 'Renz Matthew', 'Magsakay', 'magsaakay1234567@mls.ceu.edu.ph', 'magsakay', 'Student'),
(3, 'Krizia', 'Lleva', 'lleva1234567@mls.ceu.edu.ph', 'lleva', 'Student'),
(4, 'Gelai', 'Gabriel', 'gabriel1234567@mls.ceu.edu.ph', 'gelai', 'Faculty'),
(8, 'Angelique Mae', 'Gabriel', 'gabriel2231439@mls.ceu.edu.ph', '$2y$10$0h6TEe81pn0W8ukAs9v5E.DSIAGk9fZHEeJGjVwYlcaD8tMN9myjq', 'Student'),
(9, 'Kim', 'Sacdalan', 'kbsacdalan@ceu.edu.ph', '$2y$10$GZNduS4LJo36412BSp5mc.PcziIS010ti5FbydMC.fHPFgFe5WpFG', 'Admin');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user_log`
--

DROP TABLE IF EXISTS `tbl_user_log`;
CREATE TABLE IF NOT EXISTS `tbl_user_log` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `log_date` datetime DEFAULT NULL,
  PRIMARY KEY (`log_id`),
  KEY `user_id_idx` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
