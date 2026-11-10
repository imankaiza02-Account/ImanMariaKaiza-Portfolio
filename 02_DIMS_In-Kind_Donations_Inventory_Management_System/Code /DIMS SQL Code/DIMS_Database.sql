-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 10, 2026 at 07:36 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `database_dims`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `is_perishable` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `is_perishable`) VALUES
(1, 'Food', 1),
(2, 'Medical Supplies', 1),
(3, 'Hygiene Supplies', 1),
(4, 'Beverages', 1),
(5, 'Furniture', 0),
(6, 'Clothing', 0),
(7, 'Textiles', 0),
(8, 'Medical Equipment', 0),
(9, 'Toys', 0),
(10, 'Household Supplies', 0),
(11, 'Office/Stationary Supplies', 0),
(12, 'Uncategorized', 0);

-- --------------------------------------------------------

--
-- Table structure for table `critical_items`
--

CREATE TABLE `critical_items` (
  `criticalItem_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `item_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity_needed` int(11) NOT NULL,
  `priority` enum('Low','Medium','High','Urgent') NOT NULL DEFAULT 'Medium',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `critical_items`
--

INSERT INTO `critical_items` (`criticalItem_id`, `category_id`, `item_name`, `description`, `quantity_needed`, `priority`, `created_at`) VALUES
(1, 1, 'Rice', '1KG bags of rice', 100, 'Medium', '2025-12-04 23:03:55'),
(2, 2, 'Gauze', '', 200, 'High', '2025-12-04 23:04:22'),
(3, 3, 'Toothpaste', 'Tubes of toothpaste', 40, 'Urgent', '2025-12-04 23:04:59'),
(4, 5, 'Stools', '', 76, 'Low', '2025-12-04 23:05:28'),
(5, 1, 'Eggs', '', 10, 'High', '2025-12-05 09:56:05');

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `donation_id` int(11) NOT NULL,
  `donor_id` int(11) NOT NULL,
  `donation_date` datetime NOT NULL DEFAULT current_timestamp(),
  `total_items` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donations`
--

INSERT INTO `donations` (`donation_id`, `donor_id`, `donation_date`, `total_items`, `created_at`) VALUES
(9, 43, '2025-12-04 22:47:40', 2, '2025-12-04 19:47:40'),
(10, 43, '2025-12-04 22:50:23', 3, '2025-12-04 19:50:23'),
(11, 43, '2025-12-04 22:51:31', 2, '2025-12-04 19:51:31'),
(12, 43, '2025-12-04 22:55:47', 1, '2025-12-04 19:55:47'),
(13, 43, '2025-12-04 22:57:33', 4, '2025-12-04 19:57:33'),
(14, 43, '2025-12-05 09:53:50', 1, '2025-12-05 06:53:50'),
(15, 43, '2025-12-05 10:13:53', 1, '2025-12-05 07:13:53');

-- --------------------------------------------------------

--
-- Table structure for table `donation_items`
--

CREATE TABLE `donation_items` (
  `item_id` int(11) NOT NULL,
  `donation_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `itemStatus` enum('To be received','Processing','Rejected','In Inventory','Distributed') NOT NULL DEFAULT 'To be received',
  `category_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donation_items`
--

INSERT INTO `donation_items` (`item_id`, `donation_id`, `item_name`, `quantity`, `description`, `expiry_date`, `itemStatus`, `category_id`) VALUES
(12, 9, 'Sanitary Pads', 100, '', '2028-12-23', 'Processing', 3),
(13, 9, 'Ballpoint Pens', 1000, '', NULL, 'Processing', 11),
(14, 10, 'Maize Flour', 50, 'These are 1KG bags of Maize Flour', '2026-03-24', 'In Inventory', 1),
(15, 10, 'Shirts', 10, '', NULL, 'In Inventory', 6),
(16, 10, 'Wooden Tables', 5, '', NULL, 'In Inventory', 5),
(17, 11, 'Wheel Chairs', 10, '', NULL, 'Distributed', 8),
(18, 11, 'Bars of Soap', 1000, '', '2026-02-02', 'Distributed', 3),
(19, 12, 'Cooking Pans', 15, 'Teflon Pans', NULL, 'Rejected', 10),
(20, 13, 'Sugar', 10, '5KG Bags of Sugar', '2029-03-12', 'Rejected', 1),
(21, 13, 'Bed Sheets', 500, '', NULL, 'Distributed', 7),
(22, 13, 'Walking Canes', 20, '', NULL, 'Distributed', 8),
(23, 13, 'Toy Cars', 10, '', NULL, 'To be received', 9),
(24, 14, 'can of beans', 500, '', '2026-12-12', 'In Inventory', 1),
(25, 15, 'Emad', 1, '', NULL, 'Distributed', 12);

-- --------------------------------------------------------

--
-- Table structure for table `donor_feedback`
--

CREATE TABLE `donor_feedback` (
  `feedback_id` int(11) NOT NULL,
  `donor_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `message` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `staff_reply` text DEFAULT NULL,
  `staff_reply_by` int(11) DEFAULT NULL,
  `staff_reply_date` datetime DEFAULT NULL,
  `admin_reply` text DEFAULT NULL,
  `admin_reply_by` int(11) DEFAULT NULL,
  `admin_reply_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donor_feedback`
--

INSERT INTO `donor_feedback` (`feedback_id`, `donor_id`, `rating`, `message`, `created_at`, `staff_reply`, `staff_reply_by`, `staff_reply_date`, `admin_reply`, `admin_reply_by`, `admin_reply_date`) VALUES
(1, 43, 3, 'The system works adequately!', '2025-12-04 22:45:57', 'Awesome', 41, '2025-12-04 21:18:54', 'Noted!', 40, '2025-12-04 21:21:48'),
(2, 43, 5, 'very good', '2025-12-05 09:52:24', NULL, NULL, NULL, 'Noted', 44, '2026-06-24 13:55:33');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_items`
--

CREATE TABLE `inventory_items` (
  `inventory_id` int(11) NOT NULL,
  `donation_item_id` int(11) DEFAULT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('In Inventory','Distributed') DEFAULT 'In Inventory',
  `qr_code_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `distributed_date` datetime DEFAULT NULL,
  `expiry_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_items`
--

INSERT INTO `inventory_items` (`inventory_id`, `donation_item_id`, `item_name`, `category_id`, `quantity`, `description`, `status`, `qr_code_path`, `created_at`, `distributed_date`, `expiry_date`) VALUES
(1, 14, 'Maize Flour', 1, 50, NULL, 'In Inventory', 'qr_codes/inv_1.png', '2025-12-04 20:10:17', NULL, '2026-03-24'),
(2, 15, 'Shirts', 6, 10, NULL, 'In Inventory', 'qr_codes/inv_2.png', '2025-12-04 20:10:33', NULL, NULL),
(3, 16, 'Wooden Tables', 5, 5, NULL, 'In Inventory', 'qr_codes/inv_3.png', '2025-12-04 20:10:52', NULL, NULL),
(4, 17, 'Wheel Chairs', 8, 10, NULL, 'Distributed', 'qr_codes/inv_4.png', '2025-12-04 20:12:10', '2025-12-04 23:17:31', NULL),
(5, 18, 'Bars of Soap', 3, 1000, NULL, 'Distributed', NULL, '2025-12-04 20:13:27', '2025-12-04 23:17:42', '2026-02-02'),
(6, 21, 'Bed Sheets', 7, 500, NULL, 'Distributed', 'qr_codes/inv_6.png', '2025-12-04 20:15:36', '2025-12-05 10:00:45', NULL),
(7, 22, 'Walking Canes', 8, 20, NULL, 'Distributed', NULL, '2025-12-04 20:15:53', '2025-12-04 23:18:22', NULL),
(8, 24, 'can of beans', 1, 500, NULL, 'In Inventory', 'qr_codes/inv_8.png', '2025-12-05 06:57:17', NULL, '2026-12-12'),
(9, 25, 'Emad', 12, 1, NULL, 'Distributed', 'qr_codes/inv_9.png', '2025-12-05 07:14:35', '2025-12-05 10:15:47', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `role` enum('Donor','Staff','Admin','SuperAdmin') NOT NULL,
  `firstName` varchar(50) DEFAULT NULL,
  `lastName` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `contactDetails` varchar(20) DEFAULT NULL,
  `approvalStatus` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `dateJoined` date NOT NULL,
  `employmentDate` date DEFAULT NULL,
  `validatedByAdminID` int(11) DEFAULT NULL,
  `roleModifiedByAdminID` int(11) DEFAULT NULL,
  `roleModificationDate` date DEFAULT NULL,
  `verificationToken` varchar(128) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `role`, `firstName`, `lastName`, `email`, `username`, `password_hash`, `contactDetails`, `approvalStatus`, `dateJoined`, `employmentDate`, `validatedByAdminID`, `roleModifiedByAdminID`, `roleModificationDate`, `verificationToken`) VALUES
(40, 'Admin', NULL, NULL, 'AdminUser@gmail.com', 'AdminUser', '$2y$10$YKZcbMMk6H0Bt1U9bjd.duMznir/Q6AIT3aXzJZVDnFAe7Ch/F6ge', '0725429717', 'Approved', '2025-12-04', NULL, NULL, NULL, NULL, NULL),
(41, 'Admin', NULL, NULL, 'StaffUser@gmail.com', 'StaffUser', '$2y$10$N4H2S2jgwf97Cwno/CdHtOjVLJVG2mmjBEIzFXFVIH1c0dAHo9DRC', '+254797373874', 'Approved', '2025-12-04', NULL, 40, 44, '2026-06-24', NULL),
(43, 'Donor', NULL, NULL, 'imanmaria.kaiza001@gmail.com', 'ImanisDonor', '$2y$10$e2/GO6Fpz7pcyKVf4IQQ0OEF7kQmxU6h9.Z0rJeD4rPBSqd5qk9sy', '0735429530', 'Approved', '2025-12-04', NULL, NULL, NULL, NULL, NULL),
(44, 'Admin', NULL, NULL, 'iman.kaiza@gmail.com\r\n', 'coolguy123', '$2y$10$G/7PN0YtJ.jHY5o4Xh1NLuTx5TmZHWQj1rLIfROk3FTFgUplspRS6', '0797878344', 'Approved', '2026-06-24', NULL, NULL, NULL, NULL, NULL),
(45, 'Donor', NULL, NULL, 'iman.kaiza@strathmore.edu', 'donor1', '$2y$10$xoo8dYpJX31MQFpvvzU9sOGwTLtZCGILCEA2sykpOQakkd2u0r5Aa', '0797878676', 'Pending', '2026-06-24', NULL, NULL, NULL, NULL, '402791bc54af61b937cd39a0d18c3e3b00c3af8a7e646131c6493f2b128f9005');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`),
  ADD UNIQUE KEY `uq_category_name` (`category_name`);

--
-- Indexes for table `critical_items`
--
ALTER TABLE `critical_items`
  ADD PRIMARY KEY (`criticalItem_id`),
  ADD KEY `fk_category` (`category_id`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`donation_id`),
  ADD KEY `fk_donations_donor` (`donor_id`);

--
-- Indexes for table `donation_items`
--
ALTER TABLE `donation_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `donation_id` (`donation_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `donor_feedback`
--
ALTER TABLE `donor_feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `donor_id` (`donor_id`),
  ADD KEY `staff_reply_by` (`staff_reply_by`),
  ADD KEY `admin_reply_by` (`admin_reply_by`);

--
-- Indexes for table `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD PRIMARY KEY (`inventory_id`),
  ADD KEY `fk_inventory_donation_item` (`donation_item_id`),
  ADD KEY `fk_inventory_category` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `validatedByAdminID` (`validatedByAdminID`),
  ADD KEY `roleModifiedByAdminID` (`roleModifiedByAdminID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `critical_items`
--
ALTER TABLE `critical_items`
  MODIFY `criticalItem_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `donation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `donation_items`
--
ALTER TABLE `donation_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `donor_feedback`
--
ALTER TABLE `donor_feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `inventory_items`
--
ALTER TABLE `inventory_items`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `critical_items`
--
ALTER TABLE `critical_items`
  ADD CONSTRAINT `fk_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL;

--
-- Constraints for table `donations`
--
ALTER TABLE `donations`
  ADD CONSTRAINT `donations_ibfk_1` FOREIGN KEY (`donor_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `fk_donations_donor` FOREIGN KEY (`donor_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `donation_items`
--
ALTER TABLE `donation_items`
  ADD CONSTRAINT `donation_items_ibfk_1` FOREIGN KEY (`donation_id`) REFERENCES `donations` (`donation_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `donation_items_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL;

--
-- Constraints for table `donor_feedback`
--
ALTER TABLE `donor_feedback`
  ADD CONSTRAINT `donor_feedback_ibfk_1` FOREIGN KEY (`donor_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `donor_feedback_ibfk_2` FOREIGN KEY (`staff_reply_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `donor_feedback_ibfk_3` FOREIGN KEY (`admin_reply_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD CONSTRAINT `fk_inventory_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_inventory_donation_item` FOREIGN KEY (`donation_item_id`) REFERENCES `donation_items` (`item_id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`validatedByAdminID`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`roleModifiedByAdminID`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
