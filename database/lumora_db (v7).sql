-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 03, 2025 at 09:42 AM
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
-- Database: `lumora_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `address_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `address_type` enum('shipping','billing','pickup','shop') NOT NULL,
  `address_line_1` varchar(255) DEFAULT NULL,
  `address_line_2` varchar(255) DEFAULT NULL,
  `barangay` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`address_id`, `user_id`, `address_type`, `address_line_1`, `address_line_2`, `barangay`, `city`, `province`, `region`, `postal_code`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 7, 'shipping', '33 Tawi-tawi St.', '', 'Santolan', 'Pasig', 'Metro Manila', 'NCR', '1608', 0, '2025-11-17 07:06:26', '2025-11-17 07:06:26'),
(2, 7, 'shop', '36 Molave St.', '', 'Sta. Lucia', 'Pasig City', 'Metro Manila', 'NCR', '1608', 0, '2025-11-19 09:58:04', '2025-11-19 09:58:04'),
(3, 3, 'shop', '34 Secret St.', '', 'Bagong Ilog', 'Pasig City', 'Metro Manila', 'NCR', '1602', 0, '2025-11-22 12:08:31', '2025-11-22 12:08:31'),
(4, 3, 'shipping', '32 Juan Luna St. Montevillas HOA', '', 'Pinagbuhatan', 'Pasig', 'Metro Manila', 'NCR', '1602', 1, '2025-11-29 00:41:00', '2025-11-29 00:41:00');

-- --------------------------------------------------------

--
-- Table structure for table `attribute_values`
--

CREATE TABLE `attribute_values` (
  `value_id` int(11) NOT NULL,
  `attribute_id` int(11) NOT NULL,
  `value` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_history`
--

CREATE TABLE `cart_history` (
  `history_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` enum('ADDED','REMOVED','UPDATED','CLEARED','CHECKED_OUT','ABANDONED') NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cart_history`
--

INSERT INTO `cart_history` (`history_id`, `user_id`, `action`, `variant_id`, `quantity`, `created_at`) VALUES
(1, 3, 'ADDED', 6, 1, '2025-11-28 12:08:32'),
(2, 3, 'CLEARED', NULL, NULL, '2025-11-28 12:08:49'),
(3, 3, 'ADDED', 6, 1, '2025-11-28 12:28:03'),
(4, 3, 'UPDATED', 6, 2, '2025-11-28 12:28:04'),
(5, 3, 'UPDATED', 6, 3, '2025-11-28 12:33:44'),
(6, 3, 'CLEARED', NULL, NULL, '2025-11-28 12:33:51'),
(7, 3, 'ADDED', 6, 1, '2025-11-28 12:33:57'),
(8, 3, 'UPDATED', 6, 2, '2025-11-28 12:34:02'),
(9, 3, 'UPDATED', 6, 1, '2025-11-28 12:59:02'),
(10, 3, 'UPDATED', 6, 2, '2025-11-28 12:59:57'),
(11, 3, 'ADDED', 8, 1, '2025-11-28 13:23:41'),
(12, 3, 'ADDED', 7, 1, '2025-11-28 15:27:15'),
(13, 3, 'CLEARED', NULL, NULL, '2025-11-29 00:46:49'),
(14, 3, 'CHECKED_OUT', NULL, NULL, '2025-11-29 00:46:49'),
(15, 3, 'ADDED', 6, 1, '2025-11-29 03:30:59'),
(16, 3, 'UPDATED', 6, 2, '2025-11-29 04:01:15'),
(17, 3, 'UPDATED', 6, 3, '2025-11-29 04:01:36'),
(18, 3, 'UPDATED', 6, 2, '2025-11-29 04:05:06'),
(19, 3, 'UPDATED', 6, 1, '2025-11-29 04:05:07'),
(20, 3, 'UPDATED', 6, 2, '2025-11-29 04:05:09'),
(21, 3, 'UPDATED', 6, 1, '2025-11-29 04:05:13'),
(22, 3, 'UPDATED', 6, 2, '2025-11-29 04:27:33'),
(23, 3, 'UPDATED', 6, 1, '2025-11-29 04:27:38'),
(24, 3, 'UPDATED', 6, 2, '2025-11-29 04:27:39'),
(25, 3, 'UPDATED', 6, 3, '2025-11-29 04:27:41'),
(26, 3, 'UPDATED', 6, 2, '2025-11-29 04:27:42'),
(27, 3, 'UPDATED', 6, 1, '2025-11-29 04:27:43'),
(28, 3, 'UPDATED', 6, 2, '2025-11-29 04:27:46'),
(29, 3, 'UPDATED', 6, 3, '2025-11-29 04:27:46'),
(30, 3, 'UPDATED', 6, 2, '2025-11-29 04:27:47'),
(31, 3, 'UPDATED', 6, 1, '2025-11-29 04:27:48'),
(32, 3, 'UPDATED', 6, 2, '2025-11-29 04:27:59'),
(33, 3, 'UPDATED', 6, 1, '2025-11-29 04:28:01'),
(34, 3, 'UPDATED', 6, 2, '2025-11-29 04:32:07'),
(35, 3, 'UPDATED', 6, 1, '2025-11-29 04:32:25'),
(36, 3, 'UPDATED', 6, 2, '2025-11-29 04:32:26'),
(37, 3, 'UPDATED', 6, 3, '2025-11-29 04:32:30'),
(38, 3, 'UPDATED', 6, 2, '2025-11-29 04:32:31'),
(39, 3, 'UPDATED', 6, 1, '2025-11-29 04:32:31'),
(40, 3, 'UPDATED', 6, 2, '2025-11-29 04:32:33'),
(41, 3, 'UPDATED', 6, 3, '2025-11-29 04:39:24'),
(42, 3, 'UPDATED', 6, 2, '2025-11-29 04:39:25'),
(43, 3, 'UPDATED', 6, 1, '2025-11-29 04:39:26'),
(44, 3, 'UPDATED', 6, 2, '2025-11-29 04:39:51'),
(45, 3, 'UPDATED', 6, 3, '2025-11-29 04:39:54'),
(46, 3, 'UPDATED', 6, 4, '2025-11-29 04:39:54'),
(47, 3, 'UPDATED', 6, 5, '2025-11-29 04:39:55'),
(48, 3, 'UPDATED', 6, 6, '2025-11-29 04:39:56'),
(49, 3, 'UPDATED', 6, 7, '2025-11-29 04:39:56'),
(50, 3, 'UPDATED', 6, 8, '2025-11-29 04:39:57'),
(51, 3, 'UPDATED', 6, 9, '2025-11-29 04:39:59'),
(52, 3, 'UPDATED', 6, 8, '2025-11-29 04:40:03'),
(53, 3, 'UPDATED', 6, 7, '2025-11-29 04:40:03'),
(54, 3, 'UPDATED', 6, 6, '2025-11-29 04:40:03'),
(55, 3, 'UPDATED', 6, 5, '2025-11-29 04:40:03'),
(56, 3, 'UPDATED', 6, 4, '2025-11-29 04:40:03'),
(57, 3, 'UPDATED', 6, 3, '2025-11-29 04:40:03'),
(58, 3, 'UPDATED', 6, 2, '2025-11-29 04:40:04'),
(59, 3, 'UPDATED', 6, 1, '2025-11-29 04:40:04'),
(60, 3, 'UPDATED', 6, 2, '2025-11-29 04:40:05'),
(61, 3, 'UPDATED', 6, 3, '2025-11-29 04:40:05'),
(62, 3, 'UPDATED', 6, 4, '2025-11-29 04:40:05'),
(63, 3, 'UPDATED', 6, 5, '2025-11-29 04:40:05'),
(64, 3, 'UPDATED', 6, 6, '2025-11-29 04:40:06'),
(65, 3, 'UPDATED', 6, 7, '2025-11-29 04:40:06'),
(66, 3, 'UPDATED', 6, 8, '2025-11-29 04:40:06'),
(67, 3, 'UPDATED', 6, 9, '2025-11-29 04:40:06'),
(68, 3, 'UPDATED', 6, 10, '2025-11-29 04:45:44'),
(69, 3, 'UPDATED', 6, 9, '2025-11-29 04:45:50'),
(70, 3, 'UPDATED', 6, 8, '2025-11-29 04:45:51'),
(71, 3, 'UPDATED', 6, 7, '2025-11-29 04:45:52'),
(72, 3, 'UPDATED', 6, 6, '2025-11-29 04:45:53'),
(73, 3, 'UPDATED', 6, 5, '2025-11-29 04:45:53'),
(74, 3, 'UPDATED', 6, 4, '2025-11-29 04:45:54'),
(75, 3, 'UPDATED', 6, 3, '2025-11-29 04:45:54'),
(76, 3, 'UPDATED', 6, 2, '2025-11-29 04:45:55'),
(77, 3, 'UPDATED', 6, 1, '2025-11-29 04:45:55'),
(78, 3, 'UPDATED', 6, 2, '2025-11-29 04:46:00'),
(79, 3, 'UPDATED', 6, 3, '2025-11-29 04:46:01'),
(80, 3, 'UPDATED', 6, 4, '2025-11-29 04:46:01'),
(81, 3, 'UPDATED', 6, 5, '2025-11-29 04:46:02'),
(82, 3, 'UPDATED', 6, 6, '2025-11-29 04:46:02'),
(83, 3, 'UPDATED', 6, 7, '2025-11-29 04:46:03'),
(84, 3, 'UPDATED', 6, 8, '2025-11-29 04:46:03'),
(85, 3, 'UPDATED', 6, 9, '2025-11-29 04:46:05'),
(86, 3, 'UPDATED', 6, 8, '2025-11-29 04:47:57'),
(87, 3, 'UPDATED', 6, 7, '2025-11-29 04:47:57'),
(88, 3, 'UPDATED', 6, 6, '2025-11-29 04:47:58'),
(89, 3, 'UPDATED', 6, 5, '2025-11-29 04:47:59'),
(90, 3, 'UPDATED', 6, 4, '2025-11-29 04:47:59'),
(91, 3, 'UPDATED', 6, 3, '2025-11-29 04:47:59'),
(92, 3, 'UPDATED', 6, 2, '2025-11-29 04:47:59'),
(93, 3, 'UPDATED', 6, 1, '2025-11-29 04:47:59'),
(94, 3, 'UPDATED', 6, 2, '2025-11-29 04:48:03'),
(95, 3, 'UPDATED', 6, 3, '2025-11-29 04:48:04'),
(96, 3, 'UPDATED', 6, 4, '2025-11-29 04:48:04'),
(97, 3, 'UPDATED', 6, 5, '2025-11-29 04:48:04'),
(98, 3, 'UPDATED', 6, 6, '2025-11-29 04:48:05'),
(99, 3, 'UPDATED', 6, 7, '2025-11-29 04:48:05'),
(100, 3, 'UPDATED', 6, 8, '2025-11-29 04:48:06'),
(101, 3, 'UPDATED', 6, 9, '2025-11-29 04:48:07'),
(102, 3, 'UPDATED', 6, 8, '2025-11-29 04:48:08'),
(103, 3, 'UPDATED', 6, 7, '2025-11-29 04:48:08'),
(104, 3, 'UPDATED', 6, 8, '2025-11-29 04:54:38'),
(105, 3, 'UPDATED', 6, 7, '2025-11-29 04:54:40'),
(106, 3, 'UPDATED', 6, 6, '2025-11-29 04:54:41'),
(107, 3, 'UPDATED', 6, 5, '2025-11-29 04:54:42'),
(108, 3, 'UPDATED', 6, 4, '2025-11-29 04:54:42'),
(109, 3, 'CLEARED', NULL, NULL, '2025-11-29 04:58:50'),
(110, 3, 'CHECKED_OUT', NULL, NULL, '2025-11-29 04:58:50'),
(111, 3, 'ADDED', 6, 2, '2025-11-29 05:02:22'),
(112, 3, 'UPDATED', 6, 3, '2025-11-29 05:02:33'),
(113, 3, 'UPDATED', 6, 4, '2025-11-29 05:02:34'),
(114, 3, 'UPDATED', 6, 5, '2025-11-29 05:02:34'),
(115, 3, 'UPDATED', 6, 6, '2025-11-29 05:02:35'),
(116, 3, 'UPDATED', 6, 7, '2025-11-29 05:02:36'),
(117, 3, 'UPDATED', 6, 8, '2025-11-29 05:02:36'),
(118, 3, 'UPDATED', 6, 7, '2025-11-29 05:02:39'),
(119, 3, 'UPDATED', 6, 8, '2025-11-29 05:02:44'),
(120, 3, 'UPDATED', 6, 9, '2025-11-29 05:02:54'),
(121, 3, 'CLEARED', NULL, NULL, '2025-11-29 05:03:57'),
(122, 3, 'CHECKED_OUT', NULL, NULL, '2025-11-29 05:03:57'),
(123, 3, 'ADDED', 6, 1, '2025-11-30 03:09:14'),
(124, 3, 'UPDATED', 6, 2, '2025-11-30 03:16:47'),
(125, 3, 'UPDATED', 6, 1, '2025-11-30 03:16:53'),
(126, 3, 'CLEARED', NULL, NULL, '2025-11-30 03:20:58'),
(127, 3, 'CHECKED_OUT', NULL, NULL, '2025-11-30 03:20:58'),
(128, 3, 'ADDED', 6, 1, '2025-11-30 03:46:51'),
(129, 3, 'CLEARED', NULL, NULL, '2025-11-30 03:47:09'),
(130, 3, 'CHECKED_OUT', NULL, NULL, '2025-11-30 03:47:09'),
(131, 3, 'ADDED', 6, 1, '2025-11-30 04:22:09'),
(132, 3, 'CLEARED', NULL, NULL, '2025-11-30 04:22:42'),
(133, 3, 'CHECKED_OUT', NULL, NULL, '2025-11-30 04:22:42'),
(134, 3, 'ADDED', 6, 1, '2025-11-30 05:45:31'),
(135, 3, 'CLEARED', NULL, NULL, '2025-11-30 05:45:57'),
(136, 3, 'CHECKED_OUT', NULL, NULL, '2025-11-30 05:45:57'),
(137, 3, 'ADDED', 6, 1, '2025-11-30 06:20:27'),
(138, 3, 'CLEARED', NULL, NULL, '2025-11-30 06:20:57'),
(139, 3, 'CHECKED_OUT', NULL, NULL, '2025-11-30 06:20:57'),
(140, 3, 'ADDED', 6, 1, '2025-11-30 07:32:29'),
(141, 3, 'CLEARED', NULL, NULL, '2025-11-30 07:32:52'),
(142, 3, 'CHECKED_OUT', NULL, NULL, '2025-11-30 07:32:52'),
(143, 3, 'ADDED', 6, 1, '2025-11-30 07:39:22'),
(144, 3, 'CLEARED', NULL, NULL, '2025-11-30 07:39:48'),
(145, 3, 'CHECKED_OUT', NULL, NULL, '2025-11-30 07:39:48'),
(146, 3, 'ADDED', 6, 2, '2025-11-30 14:56:18'),
(147, 3, 'CLEARED', NULL, NULL, '2025-11-30 14:57:04'),
(148, 3, 'CHECKED_OUT', NULL, NULL, '2025-11-30 14:57:04'),
(149, 3, 'ADDED', 6, 1, '2025-12-01 12:14:37'),
(150, 3, 'CLEARED', NULL, NULL, '2025-12-01 12:15:01'),
(151, 3, 'CHECKED_OUT', NULL, NULL, '2025-12-01 12:15:01'),
(152, 3, 'CLEARED', NULL, NULL, '2025-12-01 12:15:10'),
(153, 3, 'CHECKED_OUT', NULL, NULL, '2025-12-01 12:15:10'),
(154, 3, 'ADDED', 6, 1, '2025-12-01 12:16:08'),
(155, 3, 'CLEARED', NULL, NULL, '2025-12-01 12:16:56'),
(156, 3, 'CHECKED_OUT', NULL, NULL, '2025-12-01 12:16:56'),
(157, 3, 'ADDED', 6, 1, '2025-12-01 12:41:09'),
(158, 3, 'CLEARED', NULL, NULL, '2025-12-01 12:42:55'),
(159, 3, 'CHECKED_OUT', NULL, NULL, '2025-12-01 12:42:55'),
(160, 3, 'ADDED', 6, 1, '2025-12-01 14:20:44'),
(161, 3, 'CLEARED', NULL, NULL, '2025-12-01 14:21:43'),
(162, 3, 'CHECKED_OUT', NULL, NULL, '2025-12-01 14:21:43'),
(163, 3, 'ADDED', 6, 1, '2025-12-01 14:23:47'),
(164, 3, 'CLEARED', NULL, NULL, '2025-12-01 14:24:12'),
(165, 3, 'CHECKED_OUT', NULL, NULL, '2025-12-01 14:24:12'),
(166, 3, 'ADDED', 6, 1, '2025-12-01 15:36:32'),
(167, 3, 'CLEARED', NULL, NULL, '2025-12-01 15:36:50'),
(168, 3, 'CHECKED_OUT', NULL, NULL, '2025-12-01 15:36:50'),
(169, 3, 'ADDED', 6, 1, '2025-12-01 16:12:41'),
(170, 3, 'CLEARED', NULL, NULL, '2025-12-01 16:13:17'),
(171, 3, 'CHECKED_OUT', NULL, NULL, '2025-12-01 16:13:17'),
(172, 3, 'ADDED', 6, 1, '2025-12-02 19:44:07'),
(173, 3, 'CLEARED', NULL, NULL, '2025-12-02 19:45:06'),
(174, 3, 'CHECKED_OUT', NULL, NULL, '2025-12-02 19:45:06'),
(175, 3, 'ADDED', 6, 1, '2025-12-03 06:17:26'),
(176, 3, 'CLEARED', NULL, NULL, '2025-12-03 06:17:45'),
(177, 3, 'CHECKED_OUT', NULL, NULL, '2025-12-03 06:17:45'),
(178, 3, 'ADDED', 6, 1, '2025-12-03 06:30:30'),
(179, 3, 'CLEARED', NULL, NULL, '2025-12-03 06:30:50'),
(180, 3, 'CHECKED_OUT', NULL, NULL, '2025-12-03 06:30:50'),
(181, 3, 'ADDED', 6, 1, '2025-12-03 06:35:03'),
(182, 3, 'CLEARED', NULL, NULL, '2025-12-03 06:35:21'),
(183, 3, 'CHECKED_OUT', NULL, NULL, '2025-12-03 06:35:21'),
(184, 3, 'ADDED', 6, 1, '2025-12-03 07:04:07'),
(185, 3, 'CLEARED', NULL, NULL, '2025-12-03 07:04:28'),
(186, 3, 'CHECKED_OUT', NULL, NULL, '2025-12-03 07:04:28'),
(187, 3, 'ADDED', 6, 1, '2025-12-03 07:08:19'),
(188, 3, 'CLEARED', NULL, NULL, '2025-12-03 07:08:36'),
(189, 3, 'CHECKED_OUT', NULL, NULL, '2025-12-03 07:08:36'),
(190, 3, 'ADDED', 6, 1, '2025-12-03 07:41:15'),
(191, 3, 'CLEARED', NULL, NULL, '2025-12-03 07:41:35'),
(192, 3, 'CHECKED_OUT', NULL, NULL, '2025-12-03 07:41:35'),
(193, 3, 'ADDED', 6, 1, '2025-12-03 08:24:21'),
(194, 3, 'CLEARED', NULL, NULL, '2025-12-03 08:24:37'),
(195, 3, 'CHECKED_OUT', NULL, NULL, '2025-12-03 08:24:37'),
(196, 3, 'ADDED', 6, 1, '2025-12-03 08:27:14'),
(197, 3, 'CLEARED', NULL, NULL, '2025-12-03 08:28:11'),
(198, 3, 'CHECKED_OUT', NULL, NULL, '2025-12-03 08:28:11');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `shop_id` int(11) DEFAULT NULL,
  `shipping_address_id` int(11) NOT NULL,
  `order_status` enum('PENDING_PAYMENT','PROCESSING','SHIPPED','DELIVERED','CANCELLED','RETURNED','REFUND_REQUESTED') NOT NULL DEFAULT 'PENDING_PAYMENT',
  `total_amount` decimal(10,2) NOT NULL,
  `shipping_fee` decimal(10,2) NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `shop_id`, `shipping_address_id`, `order_status`, `total_amount`, `shipping_fee`, `is_deleted`, `created_at`, `updated_at`) VALUES
(38, 3, 4, 4, 'SHIPPED', 749.00, 50.00, 0, '2025-12-03 07:04:09', '2025-12-03 07:04:59'),
(39, 3, 4, 4, 'CANCELLED', 749.00, 50.00, 0, '2025-12-03 07:08:21', '2025-12-03 07:37:23'),
(40, 3, 4, 4, 'CANCELLED', 749.00, 50.00, 0, '2025-12-03 07:41:17', '2025-12-03 08:07:35'),
(41, 3, 4, 4, 'CANCELLED', 749.00, 50.00, 0, '2025-12-03 08:24:23', '2025-12-03 08:26:09'),
(42, 3, 4, 4, 'PROCESSING', 749.00, 50.00, 0, '2025-12-03 08:27:56', '2025-12-03 08:28:10');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `variant_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_at_purchase` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `personalized_notes` text DEFAULT NULL,
  `status` enum('PENDING','PROCESSING','SHIPPED','DELIVERED','COMPLETED','CANCELLED','RETURNED','REFUNDED') DEFAULT 'PENDING',
  `review_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `variant_id`, `quantity`, `price_at_purchase`, `total_price`, `personalized_notes`, `status`, `review_id`) VALUES
(1, 1, 6, 2, 699.00, 1398.00, NULL, 'PENDING', NULL),
(2, 2, 6, 2, 699.00, 1398.00, NULL, 'PENDING', NULL),
(3, 3, 6, 1, 699.00, 699.00, NULL, 'PENDING', NULL),
(4, 4, 6, 1, 699.00, 699.00, NULL, 'PENDING', NULL),
(5, 5, 6, 3, 699.00, 2097.00, NULL, 'PENDING', NULL),
(6, 6, 6, 3, 699.00, 2097.00, NULL, 'PENDING', NULL),
(7, 7, 6, 3, 699.00, 2097.00, NULL, 'PENDING', NULL),
(8, 8, 6, 1, 699.00, 699.00, NULL, 'PENDING', NULL),
(9, 9, 6, 1, 699.00, 699.00, NULL, 'PENDING', NULL),
(10, 10, 6, 1, 699.00, 699.00, NULL, 'PENDING', NULL),
(11, 11, 6, 1, 699.00, 699.00, NULL, 'PENDING', NULL),
(12, 12, 6, 1, 699.00, 699.00, NULL, 'PENDING', NULL),
(13, 13, 6, 9, 699.00, 6291.00, NULL, 'PENDING', NULL),
(14, 14, 6, 4, 699.00, 2796.00, NULL, 'PENDING', NULL),
(15, 15, 6, 4, 699.00, 2796.00, NULL, 'PENDING', NULL),
(16, 16, 6, 9, 699.00, 6291.00, NULL, 'PENDING', NULL),
(17, 17, 6, 1, 699.00, 699.00, NULL, 'PENDING', NULL),
(18, 18, 6, 1, 699.00, 699.00, NULL, 'PENDING', NULL),
(19, 19, 6, 1, 699.00, 699.00, NULL, 'PENDING', NULL),
(20, 20, 6, 1, 699.00, 699.00, NULL, 'PENDING', NULL),
(21, 21, 6, 1, 699.00, 699.00, NULL, 'PENDING', NULL),
(22, 22, 6, 1, 699.00, 699.00, NULL, 'PENDING', NULL),
(23, 23, 6, 1, 699.00, 699.00, NULL, 'PENDING', NULL),
(24, 24, 6, 1, 699.00, 699.00, NULL, 'PENDING', NULL),
(25, 25, 6, 2, 699.00, 1398.00, NULL, 'PENDING', NULL),
(26, 26, 6, 1, 699.00, 699.00, NULL, 'PENDING', NULL),
(27, 27, 6, 1, 699.00, 699.00, NULL, 'PENDING', NULL),
(28, 28, 6, 1, 699.00, 699.00, NULL, 'PENDING', NULL),
(29, 29, 6, 1, 699.00, 699.00, NULL, 'PENDING', NULL),
(30, 30, 6, 1, 699.00, 699.00, NULL, 'PENDING', NULL),
(31, 31, 6, 1, 699.00, 699.00, NULL, 'PENDING', NULL),
(32, 32, 6, 1, 699.00, 699.00, NULL, 'PROCESSING', NULL),
(33, 33, 6, 1, 699.00, 699.00, NULL, 'PROCESSING', NULL),
(34, 34, 6, 1, 699.00, 699.00, NULL, 'PENDING', NULL),
(35, 35, 6, 1, 699.00, 699.00, NULL, 'PENDING', NULL),
(36, 36, 6, 1, 699.00, 699.00, NULL, 'PROCESSING', NULL),
(37, 37, 6, 1, 699.00, 699.00, NULL, 'PROCESSING', NULL),
(38, 38, 6, 1, 699.00, 699.00, NULL, 'PROCESSING', NULL),
(39, 39, 6, 1, 699.00, 699.00, NULL, 'PROCESSING', NULL),
(40, 40, 6, 1, 699.00, 699.00, NULL, 'PROCESSING', NULL),
(41, 41, 6, 1, 699.00, 699.00, NULL, 'PROCESSING', NULL),
(42, 42, 6, 1, 699.00, 699.00, NULL, 'PROCESSING', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment_method` enum('CREDIT_CARD','PAYPAL','E_WALLET','COD') NOT NULL,
  `payment_gateway` varchar(100) DEFAULT NULL,
  `transaction_id` varchar(100) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('PENDING','COMPLETED','FAILED','REFUNDED') DEFAULT 'PENDING',
  `processed_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `order_id`, `payment_method`, `payment_gateway`, `transaction_id`, `amount_paid`, `payment_date`, `status`, `processed_by`, `updated_at`) VALUES
(1, 2, 'E_WALLET', 'PayMongo', 'cs_tUvHKFzchv4L1HACLaeaSdUc', 1448.00, '2025-11-29 00:46:49', 'PENDING', NULL, '2025-11-29 00:46:49'),
(2, 3, 'E_WALLET', 'PayMongo', 'cs_YneZy4UyYoqdBTbxaCUDShE1', 749.00, '2025-11-29 03:31:17', 'PENDING', NULL, '2025-11-29 03:31:17'),
(3, 4, 'E_WALLET', 'PayMongo', 'cs_nBi57PdDGPYRSDARbtJnLMgx', 749.00, '2025-11-29 03:35:41', 'PENDING', NULL, '2025-11-29 03:35:41'),
(4, 8, 'E_WALLET', 'PayMongo', 'cs_vwqdmiGr8ZVPhXpT45TewpW5', 749.00, '2025-11-29 04:05:23', 'PENDING', NULL, '2025-11-29 04:05:23'),
(5, 9, 'E_WALLET', 'PayMongo', 'cs_keLj1mdrQekeASUgRKcpGRCF', 749.00, '2025-11-29 04:21:56', 'PENDING', NULL, '2025-11-29 04:21:56'),
(6, 10, 'E_WALLET', 'PayMongo', 'cs_B3CPZMiP31QEEkoSLNTpeCju', 749.00, '2025-11-29 04:22:05', 'PENDING', NULL, '2025-11-29 04:22:05'),
(7, 12, 'E_WALLET', 'PayMongo', 'cs_bLQT4DiwQrArj1TWs331sE41', 749.00, '2025-11-29 04:31:43', 'PENDING', NULL, '2025-11-29 04:31:43'),
(8, 13, 'E_WALLET', 'PayMongo', 'cs_q6QWRAqwXpMfKgv4NpvKwy3S', 6341.00, '2025-11-29 04:40:49', 'PENDING', NULL, '2025-11-29 04:40:49'),
(9, 14, 'E_WALLET', 'PayMongo', 'cs_5ERdzBrrFioygB434EDgcWez', 2846.00, '2025-11-29 04:56:30', 'PENDING', NULL, '2025-11-29 04:56:30'),
(10, 15, 'E_WALLET', 'PayMongo', 'cs_8zdtYRmPbgHgCUWV7Tr4boZG', 2846.00, '2025-11-29 04:58:32', 'PENDING', NULL, '2025-11-29 04:58:32'),
(11, 16, 'E_WALLET', 'PayMongo', 'cs_kgD1YsjJkWHu8VFYRUkCMHW7', 6341.00, '2025-11-29 05:03:20', 'PENDING', NULL, '2025-11-29 05:03:20'),
(12, 17, 'E_WALLET', 'PayMongo', 'cs_2XFmurogRTvr6kjVwHQYWpyx', 749.00, '2025-11-30 03:09:30', 'PENDING', NULL, '2025-11-30 03:09:30'),
(13, 18, 'E_WALLET', 'PayMongo', 'cs_L6DnVWRB2dfUc9dwzB5LZeWv', 749.00, '2025-11-30 03:17:13', 'PENDING', NULL, '2025-11-30 03:17:13'),
(14, 19, 'E_WALLET', 'PayMongo', 'cs_V37gsr4FzjYz3uNoNAGJnxAM', 749.00, '2025-11-30 03:46:54', 'PENDING', NULL, '2025-11-30 03:46:54'),
(15, 20, 'E_WALLET', 'PayMongo', 'cs_aboqhnQf5DKSkDryVtr8otjB', 749.00, '2025-11-30 04:22:13', 'PENDING', NULL, '2025-11-30 04:22:13'),
(16, 21, 'E_WALLET', 'PayMongo', 'cs_QKPSLymKKPjpqRJ6WqhJ3csN', 749.00, '2025-11-30 05:45:35', 'PENDING', NULL, '2025-11-30 05:45:35'),
(17, 22, 'E_WALLET', 'PayMongo', 'cs_wFoGphqQuBynbYqtEwh18qAV', 749.00, '2025-11-30 06:20:32', 'PENDING', NULL, '2025-11-30 06:20:32'),
(18, 23, 'E_WALLET', 'PayMongo', 'cs_8Qk5kPuBjMuBH2qy8B8h4U5h', 749.00, '2025-11-30 07:32:32', 'PENDING', NULL, '2025-11-30 07:32:32'),
(19, 24, 'E_WALLET', 'PayMongo', 'cs_ZksVZS8uWXUdCPD7BSrWMy8Q', 749.00, '2025-11-30 07:39:29', 'PENDING', NULL, '2025-11-30 07:39:29'),
(20, 25, 'E_WALLET', 'PayMongo', 'cs_oY7cXG9nwABx3ayZ6VF3JBD3', 1448.00, '2025-11-30 14:56:38', 'PENDING', NULL, '2025-11-30 14:56:38'),
(21, 26, 'E_WALLET', 'PayMongo', 'cs_jZt21PZNjewTE2uCszmCt6Bk', 749.00, '2025-12-01 12:14:43', 'PENDING', NULL, '2025-12-01 12:14:43'),
(22, 27, 'E_WALLET', 'PayMongo', 'cs_NK4SiXZQHWV5LXXmfmu6t7iE', 749.00, '2025-12-01 12:16:17', 'PENDING', NULL, '2025-12-01 12:16:17'),
(23, 28, 'E_WALLET', 'PayMongo', 'cs_PNGL32BNtXrg1vXAbC9cfLjg', 749.00, '2025-12-01 12:41:16', 'PENDING', NULL, '2025-12-01 12:41:16'),
(24, 29, 'E_WALLET', 'PayMongo', 'cs_SgHj52xHj9q86m9mrEPVU5nD', 749.00, '2025-12-01 12:42:36', 'PENDING', NULL, '2025-12-01 12:42:36'),
(25, 30, 'E_WALLET', 'PayMongo', 'cs_SPMjFZhKyUQiGRA3kucKQYCE', 749.00, '2025-12-01 14:20:52', 'PENDING', NULL, '2025-12-01 14:20:52'),
(26, 31, 'E_WALLET', 'PayMongo', 'cs_prsS4j8Buo8qsCA5crBwmxqs', 749.00, '2025-12-01 14:23:50', 'PENDING', NULL, '2025-12-01 14:23:50'),
(27, 32, 'E_WALLET', 'PayMongo', 'cs_jDQEj98bB5TwW8z7UAx8GdBD', 749.00, '2025-12-01 15:36:33', 'COMPLETED', NULL, '2025-12-01 15:36:49'),
(28, 33, 'E_WALLET', 'PayMongo', 'cs_bi19nNkUbwDpM4mJrkoicq6X', 749.00, '2025-12-01 16:12:42', 'COMPLETED', NULL, '2025-12-01 16:13:12'),
(29, 34, 'E_WALLET', 'PayMongo', 'cs_9Sn5kfQkT7x3fhTKMcArbkcm', 749.00, '2025-12-02 19:44:10', 'PENDING', NULL, '2025-12-02 19:44:10'),
(30, 35, 'E_WALLET', 'PayMongo', 'cs_Y5BoGMW8HDd2AQRK7Pw8SsxQ', 749.00, '2025-12-03 06:17:29', 'PENDING', NULL, '2025-12-03 06:17:29'),
(31, 36, 'E_WALLET', 'PayMongo', 'cs_GV8xKFTRS4T31TLgBk7hVfWm', 749.00, '2025-12-03 06:30:33', 'COMPLETED', NULL, '2025-12-03 06:30:49'),
(32, 37, 'E_WALLET', 'PayMongo', 'cs_49p9LowyaAHZKPiycQeLtrW3', 749.00, '2025-12-03 06:35:06', 'COMPLETED', NULL, '2025-12-03 06:35:20'),
(33, 38, 'E_WALLET', 'PayMongo', 'cs_Uh4mqhTn2xpP1DvgMznKDCGF', 749.00, '2025-12-03 07:04:10', 'COMPLETED', NULL, '2025-12-03 07:04:26'),
(34, 39, 'E_WALLET', 'PayMongo', 'cs_pU7q3EEX2N5yfMPjWkkcpAfb', 749.00, '2025-12-03 07:08:21', 'COMPLETED', NULL, '2025-12-03 07:08:34'),
(35, 40, 'E_WALLET', 'PayMongo', 'cs_jRRLxhqwaK893pcRy3ggFHo1', 749.00, '2025-12-03 07:41:17', 'COMPLETED', NULL, '2025-12-03 07:41:33'),
(36, 41, 'E_WALLET', 'PayMongo', 'cs_WsLKFe3qEchcvGMRaHebfqzz', 749.00, '2025-12-03 08:24:23', 'COMPLETED', NULL, '2025-12-03 08:24:36'),
(37, 42, 'E_WALLET', 'PayMongo', 'cs_d71SkiZQ1Uj95P872TCJRApX', 749.00, '2025-12-03 08:27:56', 'COMPLETED', NULL, '2025-12-03 08:28:10');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `shop_id` int(11) NOT NULL,
  `slug` varchar(150) NOT NULL,
  `name` varchar(200) NOT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `short_description` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `cover_picture` varchar(200) DEFAULT NULL,
  `status` enum('DRAFT','PUBLISHED','UNPUBLISHED','ARCHIVED') DEFAULT 'DRAFT',
  `is_deleted` tinyint(1) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `shop_id`, `slug`, `name`, `meta_title`, `short_description`, `meta_description`, `description`, `cover_picture`, `status`, `is_deleted`, `is_featured`, `created_at`, `updated_at`) VALUES
(5, 4, 'onitsu-1764292574', 'Onitsu', NULL, 'premium leather bag', NULL, 'leather bag made with premium leather', 'uploads/products/4/products/product_1764292574_1647.png', 'PUBLISHED', 0, 0, '2025-11-28 01:16:14', '2025-11-28 01:16:14'),
(6, 4, 'tenenen-1764335274', 'Tenenen', NULL, 'tenenenenen', NULL, 'teneneneneenenenenen', 'uploads/products/4/products/product_1764335274_9599.jpg', 'PUBLISHED', 1, 0, '2025-11-28 13:07:54', '2025-11-28 15:48:11');

-- --------------------------------------------------------

--
-- Table structure for table `product_attributes`
--

CREATE TABLE `product_attributes` (
  `attribute_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(150) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_categories`
--

INSERT INTO `product_categories` (`category_id`, `name`, `slug`, `created_at`) VALUES
(1, 'Bags', 'bags', '2025-11-21 15:22:20'),
(2, 'Jewelry', 'jewelry', '2025-11-23 16:52:42'),
(3, 'Vintage', 'vintage', '2025-11-23 16:52:52'),
(4, 'Ornaments', 'ornaments', '2025-11-23 16:53:04'),
(5, 'Sticker', 'sticker', '2025-11-23 16:53:31'),
(6, 'Clothing', 'clothing', '2025-11-23 16:53:35');

-- --------------------------------------------------------

--
-- Table structure for table `product_category_links`
--

CREATE TABLE `product_category_links` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_category_links`
--

INSERT INTO `product_category_links` (`product_id`, `category_id`) VALUES
(5, 1),
(6, 2);

-- --------------------------------------------------------

--
-- Table structure for table `product_reviews`
--

CREATE TABLE `product_reviews` (
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_tags`
--

CREATE TABLE `product_tags` (
  `tag_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_tags`
--

INSERT INTO `product_tags` (`tag_id`, `name`) VALUES
(2, 'bag'),
(3, 'black'),
(1, 'leather');

-- --------------------------------------------------------

--
-- Table structure for table `product_tag_links`
--

CREATE TABLE `product_tag_links` (
  `product_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  `confidence_score` decimal(5,2) DEFAULT NULL,
  `is_auto_generated` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_tag_links`
--

INSERT INTO `product_tag_links` (`product_id`, `tag_id`, `confidence_score`, `is_auto_generated`) VALUES
(5, 1, NULL, 0),
(5, 2, NULL, 0),
(5, 3, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `variant_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_name` varchar(100) DEFAULT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `color` varchar(50) DEFAULT NULL,
  `size` varchar(50) DEFAULT NULL,
  `material` varchar(100) DEFAULT NULL,
  `product_picture` varchar(255) DEFAULT NULL,
  `short_description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`variant_id`, `product_id`, `variant_name`, `sku`, `price`, `quantity`, `color`, `size`, `material`, `product_picture`, `short_description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'MJ Earth', 'PRD-000001-V01', 10000.00, 1, '0', 'large', 'ratan', NULL, NULL, 1, '2025-11-22 07:48:34', '2025-11-22 07:48:34'),
(6, 5, 'Onitsu', 'BAG-001', 699.00, 50, '0', 'Medium', 'Leather', NULL, NULL, 1, '2025-11-28 01:16:14', '2025-11-28 01:16:14'),
(7, 6, 'Upper VIew', 'view-001', 99999.00, 1, '0', 'Small', 'Leather', NULL, NULL, 1, '2025-11-28 13:07:54', '2025-11-28 13:07:54'),
(8, 6, 'Tiles', 'view-002', 99999.00, 1, '0', 'small', 'leather', NULL, NULL, 1, '2025-11-28 13:07:54', '2025-11-28 13:07:54');

-- --------------------------------------------------------

--
-- Table structure for table `refunds`
--

CREATE TABLE `refunds` (
  `refund_id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('PENDING','PROCESSED','REJECTED') DEFAULT 'PENDING',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `name`, `description`) VALUES
(1, 'buyer', 'Standard customer role'),
(2, 'seller', 'User with permissions to manage products/shop'),
(3, 'admin', 'Full system access');

-- --------------------------------------------------------

--
-- Table structure for table `shipments`
--

CREATE TABLE `shipments` (
  `shipment_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `courier_name` varchar(100) DEFAULT NULL,
  `estimated_delivery_date` date DEFAULT NULL,
  `shipment_status` enum('PICKUP_PENDING','IN_TRANSIT','OUT_FOR_DELIVERY','DELIVERED','FAILED_ATTEMPT') NOT NULL DEFAULT 'PICKUP_PENDING',
  `ship_from_address_id` int(11) NOT NULL,
  `ship_to_address_id` int(11) NOT NULL,
  `shipped_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shopping_cart`
--

CREATE TABLE `shopping_cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `variant_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shops`
--

CREATE TABLE `shops` (
  `shop_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `shop_name` varchar(100) NOT NULL,
  `contact_email` varchar(100) NOT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `address_id` int(11) DEFAULT NULL,
  `shop_description` text DEFAULT NULL,
  `shop_banner` varchar(255) DEFAULT NULL,
  `shop_profile` varchar(255) DEFAULT NULL,
  `slug` varchar(150) NOT NULL,
  `status` enum('active','suspended','pending') DEFAULT 'active',
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shops`
--

INSERT INTO `shops` (`shop_id`, `user_id`, `shop_name`, `contact_email`, `contact_phone`, `address_id`, `shop_description`, `shop_banner`, `shop_profile`, `slug`, `status`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 7, 'Urban Basket', 'teopaco_markjerome@plpasig.edu.ph', '09696276320', NULL, 'Urban Basket is a modern lifestyle shop offering a curated selection of affordable, stylish, and everyday essentials. From home décor and personal care items to stationery, gadgets, and travel accessories, Urban Basket brings practical design and playful aesthetics together in one place. It’s a go-to store for shoppers who love trendy, functional products that elevate daily living without breaking the budget.', NULL, NULL, 'urban-basket-1763545597', 'active', 0, '2025-11-19 09:46:37', '2025-11-19 09:46:37'),
(4, 3, 'Rural Basket', 'programmer.samantha.siao@gmail.com', '09518579224', NULL, 'shop ni bueta puro bag', NULL, NULL, 'rural-basket', 'active', 0, '2025-11-22 12:08:31', '2025-11-22 12:08:31');

-- --------------------------------------------------------

--
-- Table structure for table `shop_earnings`
--

CREATE TABLE `shop_earnings` (
  `earning_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `shop_id` int(11) NOT NULL,
  `item_revenue` decimal(10,2) NOT NULL,
  `platform_commission` decimal(10,2) NOT NULL,
  `net_payout_amount` decimal(10,2) NOT NULL,
  `payout_status` enum('PENDING','PAID','FAILED','CANCELLED') DEFAULT 'PENDING',
  `payout_reference` varchar(100) DEFAULT NULL,
  `payout_date` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `failed_login_attempts` int(11) NOT NULL DEFAULT 0,
  `lockout_until` timestamp NULL DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(64) DEFAULT NULL,
  `verification_token_expires` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `failed_login_attempts`, `lockout_until`, `email`, `email_verified`, `verification_token`, `verification_token_expires`, `created_at`, `updated_at`) VALUES
(3, 'carl', '$2y$10$mvnBP1LphqH7mE.pPjNZH.8bSWKF0SEoZpPtt9REB1Ndu/evGU2I2', 0, NULL, 'carlronquillo05@gmail.com', 0, NULL, NULL, '2025-10-25 11:42:38', '2025-11-26 01:14:28'),
(4, 'Aly', '$2y$10$.9RPLUFrp6Rc.ROPpx4j9OC/YEdbHl0K/hEriJewWAZb6ZnJQ6uRq', 3, '2025-11-25 18:29:09', 'pitogoalyssanicole@gmail.com', 0, NULL, NULL, '2025-11-07 18:37:20', '2025-11-26 01:14:09'),
(7, 'mjteopaco', '$2y$10$YxUO3gD7OIqBzufOyCJSn.kIxw.P3oMR4WmmjAaZc7k.3mXjMfFMC', 0, NULL, 'teopaco_markjerome@plpasig.edu.ph', 0, NULL, NULL, '2025-11-14 12:35:06', '2025-11-17 07:01:05'),
(8, 'adminMJ', '$2y$10$/q9slVigZyj5UapSMsm8Se2E7yslQSpwoL2775f.Z8aj9woqRbwTW', 0, NULL, 'mjteopaco11@gmail.com', 0, NULL, NULL, '2025-11-14 13:55:19', '2025-11-14 13:55:19');

-- --------------------------------------------------------

--
-- Table structure for table `user_audit_logs`
--

CREATE TABLE `user_audit_logs` (
  `log_id` bigint(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `target_user_id` int(11) DEFAULT NULL,
  `action_type` enum('LOGIN','LOGOUT','REGISTER','PASSWORD_CHANGE','PROFILE_UPDATE','ROLE_UPDATE','ADDRESS_UPDATE') NOT NULL,
  `status` enum('SUCCESS','FAILURE') DEFAULT 'SUCCESS',
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

CREATE TABLE `user_profiles` (
  `profile_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`profile_id`, `user_id`, `full_name`, `phone_number`, `gender`, `birth_date`, `profile_pic`, `created_at`, `updated_at`) VALUES
(1, 7, 'MJ Teopaco', '09696276320', 'Male', '2003-12-06', 'uploads/profiles/profile_7_1763363033.jpg', '2025-11-17 07:03:53', '2025-11-17 07:04:58');

-- --------------------------------------------------------

--
-- Table structure for table `user_remember_tokens`
--

CREATE TABLE `user_remember_tokens` (
  `token_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `selector` varchar(64) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_remember_tokens`
--

INSERT INTO `user_remember_tokens` (`token_id`, `user_id`, `selector`, `token_hash`, `expires_at`) VALUES
(1, 3, '0657771854a7e2658ba414b3044dfc4b', '230d4eafcd0b645b97644cfe6217509fd71ce26e70640b7143835f1143c0c16a', '2025-12-07 13:02:38'),
(2, 3, '3c0d27b47eb203ba0f0b852a9af46336', '17cb88185f106b19b04d70e345e4c546a293aaa8bc38ab91c6c7c943efa04161', '2025-12-07 13:03:47'),
(3, 3, '3a152f29aeda98957a4a6959a3e46edf', '5ea724231dea0d81e54e6d249e662fc8e0cb055190cf6d048463255230eac4e6', '2025-12-07 13:04:55'),
(4, 3, '4ef9aad18737650c208cac5c8d3452fc', '959a647202ec3ae70cf75e199eb92c63807836812353537de2b26ec94b7b15ee', '2025-12-07 20:09:27'),
(20, 3, '79a70f9ffc2f836e5919c098021037b5', '3a71b02ac38333265bff7a269765e4ee0551e87c0a6516e00144b535f2f2522b', '2026-01-01 23:17:15');

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_approved` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`user_id`, `role_id`, `assigned_at`, `is_approved`) VALUES
(3, 1, '2025-11-14 12:32:26', 1),
(3, 2, '2025-11-22 12:08:31', 1),
(4, 1, '2025-11-14 12:32:26', 1),
(7, 1, '2025-11-14 12:35:06', 1),
(7, 2, '2025-11-19 09:58:04', 1),
(8, 3, '2025-11-14 13:55:19', 1);

-- --------------------------------------------------------

--
-- Table structure for table `variant_attribute_links`
--

CREATE TABLE `variant_attribute_links` (
  `variant_id` int(11) NOT NULL,
  `value_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `attribute_values`
--
ALTER TABLE `attribute_values`
  ADD PRIMARY KEY (`value_id`),
  ADD KEY `attribute_id` (`attribute_id`);

--
-- Indexes for table `cart_history`
--
ALTER TABLE `cart_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `variant_id` (`variant_id`),
  ADD KEY `action` (`action`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `shop_id` (`shop_id`),
  ADD KEY `shipping_address_id` (`shipping_address_id`),
  ADD KEY `idx_order_status` (`order_status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD UNIQUE KEY `unique_order_variant` (`order_id`,`variant_id`),
  ADD KEY `variant_id` (`variant_id`),
  ADD KEY `review_id` (`review_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD UNIQUE KEY `order_id` (`order_id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`),
  ADD KEY `processed_by` (`processed_by`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_shop_id` (`shop_id`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_shop_status` (`shop_id`,`status`),
  ADD KEY `idx_is_deleted` (`is_deleted`);

--
-- Indexes for table `product_attributes`
--
ALTER TABLE `product_attributes`
  ADD PRIMARY KEY (`attribute_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `product_category_links`
--
ALTER TABLE `product_category_links`
  ADD PRIMARY KEY (`product_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_tags`
--
ALTER TABLE `product_tags`
  ADD PRIMARY KEY (`tag_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `product_tag_links`
--
ALTER TABLE `product_tag_links`
  ADD PRIMARY KEY (`product_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`variant_id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_product_active` (`product_id`,`is_active`);

--
-- Indexes for table `refunds`
--
ALTER TABLE `refunds`
  ADD PRIMARY KEY (`refund_id`),
  ADD KEY `payment_id` (`payment_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `name_unique` (`name`);

--
-- Indexes for table `shipments`
--
ALTER TABLE `shipments`
  ADD PRIMARY KEY (`shipment_id`),
  ADD UNIQUE KEY `order_id` (`order_id`),
  ADD UNIQUE KEY `tracking_number` (`tracking_number`),
  ADD KEY `ship_from_address_id` (`ship_from_address_id`),
  ADD KEY `ship_to_address_id` (`ship_to_address_id`);

--
-- Indexes for table `shopping_cart`
--
ALTER TABLE `shopping_cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD UNIQUE KEY `unique_user_variant` (`user_id`,`variant_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `variant_id` (`variant_id`),
  ADD KEY `idx_cart_user_created` (`user_id`,`created_at`),
  ADD KEY `idx_cart_updated` (`updated_at`);

--
-- Indexes for table `shops`
--
ALTER TABLE `shops`
  ADD PRIMARY KEY (`shop_id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `address_id` (`address_id`);

--
-- Indexes for table `shop_earnings`
--
ALTER TABLE `shop_earnings`
  ADD PRIMARY KEY (`earning_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `shop_id` (`shop_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_verification_token` (`verification_token`);

--
-- Indexes for table `user_audit_logs`
--
ALTER TABLE `user_audit_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `target_user_id` (`target_user_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action_type` (`action_type`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`profile_id`),
  ADD UNIQUE KEY `phone_number` (`phone_number`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_remember_tokens`
--
ALTER TABLE `user_remember_tokens`
  ADD PRIMARY KEY (`token_id`),
  ADD UNIQUE KEY `selector` (`selector`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `fk_userroles_role` (`role_id`);

--
-- Indexes for table `variant_attribute_links`
--
ALTER TABLE `variant_attribute_links`
  ADD PRIMARY KEY (`variant_id`,`value_id`),
  ADD KEY `value_id` (`value_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `attribute_values`
--
ALTER TABLE `attribute_values`
  MODIFY `value_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart_history`
--
ALTER TABLE `cart_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=199;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `product_attributes`
--
ALTER TABLE `product_attributes`
  MODIFY `attribute_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_tags`
--
ALTER TABLE `product_tags`
  MODIFY `tag_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `variant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `refunds`
--
ALTER TABLE `refunds`
  MODIFY `refund_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `shipments`
--
ALTER TABLE `shipments`
  MODIFY `shipment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shopping_cart`
--
ALTER TABLE `shopping_cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `shops`
--
ALTER TABLE `shops`
  MODIFY `shop_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `shop_earnings`
--
ALTER TABLE `shop_earnings`
  MODIFY `earning_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_audit_logs`
--
ALTER TABLE `user_audit_logs`
  MODIFY `log_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `profile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_remember_tokens`
--
ALTER TABLE `user_remember_tokens`
  MODIFY `token_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `product_category_links`
--
ALTER TABLE `product_category_links`
  ADD CONSTRAINT `product_category_links_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_category_links_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`category_id`) ON DELETE CASCADE;

--
-- Constraints for table `shopping_cart`
--
ALTER TABLE `shopping_cart`
  ADD CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cart_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`variant_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_audit_logs`
--
ALTER TABLE `user_audit_logs`
  ADD CONSTRAINT `user_audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_audit_logs_ibfk_2` FOREIGN KEY (`target_user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_remember_tokens`
--
ALTER TABLE `user_remember_tokens`
  ADD CONSTRAINT `user_remember_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `fk_userroles_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_userroles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
