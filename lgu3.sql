-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 13, 2025 at 06:32 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lgu3`
--

-- --------------------------------------------------------

--
-- Table structure for table `records`
--

CREATE TABLE `records` (
  `id` int(11) NOT NULL,
  `stall_id` int(11) NOT NULL,
  `stall_number` varchar(50) DEFAULT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `action` enum('added','assigned','vacated','updated','deleted','rejected','approved') NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `records`
--

INSERT INTO `records` (`id`, `stall_id`, `stall_number`, `vendor_id`, `action`, `notes`, `created_at`) VALUES
(22, 56, 'A-1', NULL, 'added', 'New stall A-1 created at Public Market A.', '2025-11-13 03:29:20'),
(23, 56, 'A-1', 46, 'rejected', 'Stall A-1 request from Jefferson Manzano rejected.', '2025-11-13 03:29:45'),
(24, 56, 'A-1', 46, 'approved', 'Stall A-1 approved and assigned to Jefferson Manzano.', '2025-11-13 03:30:03'),
(25, 57, 'A-11', 58, 'added', 'New stall A-11 created at 123.', '2025-11-13 03:36:55'),
(26, 57, 'A-11', NULL, 'updated', 'Stall A-11 updated.', '2025-11-13 03:37:00'),
(27, 57, 'A-11', NULL, 'deleted', 'Stall A-11 deleted.', '2025-11-13 03:37:03'),
(28, 56, 'A-1', NULL, 'updated', 'Stall A-1 updated.', '2025-11-13 03:46:55'),
(29, 56, 'A-1', NULL, 'deleted', 'Stall A-1 deleted.', '2025-11-13 03:47:26'),
(30, 58, 'A-1', NULL, 'added', 'New stall A-1 created at Public Market Aq.', '2025-11-13 03:47:58'),
(31, 58, 'A-1', 42, 'assigned', 'Stall A-1 assigned to Burat.', '2025-11-13 03:48:03'),
(32, 58, 'A-1', NULL, 'updated', 'Stall A-1 updated.', '2025-11-13 03:55:18'),
(33, 58, 'A-1', 51, 'assigned', 'Stall A-1 assigned to Hotdog.', '2025-11-13 03:55:30'),
(34, 58, 'A-1', 51, 'updated', 'Stall A-1 type changed from retail to service.', '2025-11-13 03:55:47'),
(35, 58, 'A-1', 46, 'updated', 'Stall A-1 vendor changed from Hotdog to Jefferson Manzano.', '2025-11-13 03:56:03'),
(36, 58, 'A-1', 46, 'updated', 'Stall A-1 rental fee changed from 12.00 to 112.00.', '2025-11-13 03:56:21'),
(37, 58, 'A-1', 46, 'updated', 'Stall A-1 updated.', '2025-11-13 03:56:45'),
(38, 59, 'AW-1', 42, 'added', 'New stall AW-1 created at Public Market Aq (Status: occupied).', '2025-11-13 04:09:43'),
(39, 60, 'A-11', NULL, 'added', 'New stall A-11 created at a (Status: vacant).', '2025-11-13 04:10:01'),
(40, 60, 'A-11', 46, 'approved', 'Stall A-11 approved and assigned to Jefferson Manzano.', '2025-11-13 05:10:40'),
(41, 0, NULL, 70, 'approved', 'User hr (hr3.atiera@gmail.com) approved by admin.', '2025-11-13 05:22:07'),
(42, 0, NULL, 71, 'rejected', 'User HR3 Atiera (hr3.atiera@gmail.com) was rejected by admin.', '2025-11-13 05:25:37');

-- --------------------------------------------------------

--
-- Table structure for table `stalls`
--

CREATE TABLE `stalls` (
  `id` int(11) NOT NULL,
  `stall_number` varchar(50) NOT NULL,
  `location` varchar(255) NOT NULL,
  `type` varchar(100) NOT NULL,
  `status` enum('vacant','occupied','maintenance') DEFAULT 'vacant',
  `assigned_to` int(11) DEFAULT NULL,
  `rental_fee` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stalls`
--

INSERT INTO `stalls` (`id`, `stall_number`, `location`, `type`, `status`, `assigned_to`, `rental_fee`, `created_at`) VALUES
(58, 'A-1', 'Public Market Aq', 'meat', 'occupied', NULL, 1112.00, '2025-11-13 03:47:58'),
(59, 'AW-1', 'Public Market Aq', 'retail', 'occupied', NULL, 123.00, '2025-11-13 04:09:43'),
(60, 'A-11', 'a', 'retail', 'occupied', NULL, 1.00, '2025-11-13 04:10:01');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `status` enum('active','inactive','pending') NOT NULL DEFAULT 'inactive',
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff','vendor') NOT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `approved_by_admin` tinyint(1) DEFAULT 0,
  `otp_code` varchar(10) DEFAULT NULL,
  `otp_expiry` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `verification_token` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `status`, `password`, `role`, `email_verified`, `approved_by_admin`, `otp_code`, `otp_expiry`, `created_at`, `verification_token`) VALUES
(3, 'JJ Manzano', 'marenoy0121@gmail.com', 'active', '$2y$10$nFAccZ2V8vnG06IFO6cFJeFc6EohV1EA.oOGcIrwapwtl.h.WqenW', 'admin', 1, 1, NULL, NULL, '2025-11-09 20:55:54', NULL),
(72, 'Rjay', 'ratbudian@gmail.com', 'active', '$2y$10$PSvBqCbeN2Oj3EcjryfCY.wrdIwNSv.1SYhDm4WDAilq.teQbouPC', 'admin', 1, 1, NULL, NULL, '2025-11-13 05:29:39', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `vendor_request`
--

CREATE TABLE `vendor_request` (
  `id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `stall_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `request_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_by` int(11) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `records`
--
ALTER TABLE `records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_records_stall_id` (`stall_id`),
  ADD KEY `idx_records_vendor_id` (`vendor_id`);

--
-- Indexes for table `stalls`
--
ALTER TABLE `stalls`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_stall_number` (`stall_number`),
  ADD KEY `assigned_to` (`assigned_to`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vendor_request`
--
ALTER TABLE `vendor_request`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_vendor_request_vendor` (`vendor_id`),
  ADD KEY `fk_vendor_request_stall` (`stall_id`),
  ADD KEY `fk_vendor_request_approver` (`approved_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `records`
--
ALTER TABLE `records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `stalls`
--
ALTER TABLE `stalls`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `vendor_request`
--
ALTER TABLE `vendor_request`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `stalls`
--
ALTER TABLE `stalls`
  ADD CONSTRAINT `stalls_ibfk_1` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `vendor_request`
--
ALTER TABLE `vendor_request`
  ADD CONSTRAINT `fk_vendor_request_approver` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_vendor_request_stall` FOREIGN KEY (`stall_id`) REFERENCES `stalls` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_vendor_request_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
