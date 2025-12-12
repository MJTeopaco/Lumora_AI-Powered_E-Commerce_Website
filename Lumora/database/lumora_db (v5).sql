-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 23, 2025 at 06:47 PM
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
(3, 3, 'shop', '34 Secret St.', '', 'Bagong Ilog', 'Pasig City', 'Metro Manila', 'NCR', '1602', 0, '2025-11-22 12:08:31', '2025-11-22 12:08:31');

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
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `shop_id` int(11) DEFAULT NULL,
  `shipping_address_id` int(11) NOT NULL,
  `order_status` enum('PENDING_PAYMENT','PROCESSING','SHIPPED','DELIVERED','CANCELLED','RETURNED') NOT NULL DEFAULT 'PENDING_PAYMENT',
  `total_amount` decimal(10,2) NOT NULL,
  `shipping_fee` decimal(10,2) NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `shop_id` int(11) NOT NULL,
  `slug` varchar(150) NOT NULL,
  `name` varchar(200) NOT NULL,
  `short_description` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `cover_picture` varchar(200) DEFAULT NULL,
  `status` enum('DRAFT','PUBLISHED','UNPUBLISHED','ARCHIVED') DEFAULT 'DRAFT',
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(1, 1, 'MJ Earth', 'PRD-000001-V01', 10000.00, 1, '0', 'large', 'ratan', NULL, NULL, 1, '2025-11-22 07:48:34', '2025-11-22 07:48:34');

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
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shops`
--

INSERT INTO `shops` (`shop_id`, `user_id`, `shop_name`, `contact_email`, `contact_phone`, `address_id`, `shop_description`, `shop_banner`, `shop_profile`, `slug`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 7, 'Urban Basket', 'teopaco_markjerome@plpasig.edu.ph', '09696276320', NULL, 'Urban Basket is a modern lifestyle shop offering a curated selection of affordable, stylish, and everyday essentials. From home décor and personal care items to stationery, gadgets, and travel accessories, Urban Basket brings practical design and playful aesthetics together in one place. It’s a go-to store for shoppers who love trendy, functional products that elevate daily living without breaking the budget.', NULL, NULL, 'urban-basket-1763545597', 0, '2025-11-19 09:46:37', '2025-11-19 09:46:37'),
(4, 3, 'Rural Basket', 'programmer.samantha.siao@gmail.com', '09518579224', NULL, 'shop ni bueta puro bag', NULL, NULL, 'rural-basket', 0, '2025-11-22 12:08:31', '2025-11-22 12:08:31');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `failed_login_attempts`, `lockout_until`, `email`, `created_at`, `updated_at`) VALUES
(3, 'carl', '$2y$10$mvnBP1LphqH7mE.pPjNZH.8bSWKF0SEoZpPtt9REB1Ndu/evGU2I2', 0, NULL, 'carlronquillo05@gmail.com', '2025-10-25 11:42:38', '2025-11-07 20:04:41'),
(4, 'Aly', '$2y$10$.9RPLUFrp6Rc.ROPpx4j9OC/YEdbHl0K/hEriJewWAZb6ZnJQ6uRq', 0, NULL, 'pitogoalyssanicole@gmail.com', '2025-11-07 18:37:20', '2025-11-07 18:37:20'),
(7, 'mjteopaco', '$2y$10$YxUO3gD7OIqBzufOyCJSn.kIxw.P3oMR4WmmjAaZc7k.3mXjMfFMC', 0, NULL, 'teopaco_markjerome@plpasig.edu.ph', '2025-11-14 12:35:06', '2025-11-17 07:01:05'),
(8, 'adminMJ', '$2y$10$/q9slVigZyj5UapSMsm8Se2E7yslQSpwoL2775f.Z8aj9woqRbwTW', 0, NULL, 'mjteopaco11@gmail.com', '2025-11-14 13:55:19', '2025-11-14 13:55:19');

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
(4, 3, '4ef9aad18737650c208cac5c8d3452fc', '959a647202ec3ae70cf75e199eb92c63807836812353537de2b26ec94b7b15ee', '2025-12-07 20:09:27');

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
  ADD KEY `idx_slug` (`slug`);

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
  ADD KEY `product_id` (`product_id`);

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
  ADD UNIQUE KEY `email` (`email`);

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
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `attribute_values`
--
ALTER TABLE `attribute_values`
  MODIFY `value_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
  MODIFY `tag_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `variant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
  MODIFY `token_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
