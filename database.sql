-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 20, 2025 at 05:59 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `garviterp`
--

-- --------------------------------------------------------

--
-- Table structure for table `account_types`
--

CREATE TABLE `account_types` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `category` enum('Asset','Liability','Equity','Income','Expense') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` int(11) NOT NULL,
  `activity_type` enum('Call','Email','Meeting','Task','Note') NOT NULL,
  `subject` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `related_to_type` varchar(50) DEFAULT NULL,
  `related_to_id` int(11) DEFAULT NULL,
  `scheduled_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  `status` enum('Scheduled','Completed','Cancelled') DEFAULT 'Scheduled',
  `assigned_to` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` bigint(20) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `check_in` time DEFAULT NULL,
  `check_out` time DEFAULT NULL,
  `status` enum('Present','Absent','Half Day','Leave','Holiday') DEFAULT 'Present',
  `working_hours` decimal(4,2) DEFAULT NULL,
  `overtime_hours` decimal(4,2) DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `action`, `table_name`, `record_id`, `old_values`, `new_values`, `ip_address`, `created_at`) VALUES
(1, NULL, 1, 'logout', 'users', 1, NULL, NULL, '::1', '2025-12-14 12:07:33'),
(2, NULL, 1, 'login', 'users', 1, NULL, NULL, '::1', '2025-12-14 12:07:40'),
(3, NULL, 1, 'logout', 'users', 1, NULL, NULL, '::1', '2025-12-14 12:08:33'),
(4, NULL, 1, 'login', 'users', 1, NULL, NULL, '::1', '2025-12-14 12:08:38'),
(5, NULL, 1, 'logout', 'users', 1, NULL, NULL, '::1', '2025-12-14 12:11:19'),
(6, NULL, 1, 'login', 'users', 1, NULL, NULL, '::1', '2025-12-14 12:11:25'),
(7, NULL, 1, 'logout', 'users', 1, NULL, NULL, '::1', '2025-12-14 12:11:29'),
(8, NULL, 1, 'login', 'users', 1, NULL, NULL, '::1', '2025-12-14 12:23:02'),
(9, NULL, 1, 'logout', 'users', 1, NULL, NULL, '::1', '2025-12-14 12:46:56'),
(10, NULL, NULL, 'login', 'users', 14, NULL, NULL, '::1', '2025-12-14 12:47:02'),
(11, NULL, NULL, 'logout', 'users', 14, NULL, NULL, '::1', '2025-12-14 12:47:30'),
(12, NULL, NULL, 'login', 'users', 14, NULL, NULL, '::1', '2025-12-14 12:47:36'),
(13, NULL, NULL, 'logout', 'users', 14, NULL, NULL, '::1', '2025-12-14 12:47:57'),
(14, NULL, 1, 'login', 'users', 1, NULL, NULL, '::1', '2025-12-14 12:48:02'),
(15, NULL, 1, 'logout', 'users', 1, NULL, NULL, '::1', '2025-12-14 14:17:26'),
(16, NULL, 1, 'login', 'users', 1, NULL, NULL, '::1', '2025-12-14 14:17:32'),
(17, NULL, 1, 'logout', 'users', 1, NULL, NULL, '::1', '2025-12-14 17:16:15'),
(18, NULL, 1, 'login', 'users', 1, NULL, NULL, '::1', '2025-12-15 05:19:15'),
(19, NULL, 1, 'logout', 'users', 1, NULL, NULL, '::1', '2025-12-15 05:19:23'),
(20, NULL, NULL, 'login', 'users', 17, NULL, NULL, '::1', '2025-12-15 05:19:28'),
(21, NULL, NULL, 'logout', 'users', 17, NULL, NULL, '::1', '2025-12-15 05:23:04'),
(22, NULL, NULL, 'login', 'users', 18, NULL, NULL, '::1', '2025-12-15 05:23:08'),
(23, NULL, 1, 'login', 'users', 1, NULL, NULL, '::1', '2025-12-15 05:25:33'),
(24, NULL, 1, 'logout', 'users', 1, NULL, NULL, '::1', '2025-12-15 05:43:41'),
(25, NULL, NULL, 'login', 'users', 18, NULL, NULL, '::1', '2025-12-15 05:43:46'),
(26, NULL, NULL, 'logout', 'users', 18, NULL, NULL, '::1', '2025-12-15 05:44:18'),
(27, NULL, 1, 'login', 'users', 1, NULL, NULL, '::1', '2025-12-15 05:44:25'),
(28, NULL, NULL, 'logout', 'users', 18, NULL, NULL, '::1', '2025-12-15 07:17:55'),
(29, NULL, NULL, 'login', 'users', 18, NULL, NULL, '::1', '2025-12-15 07:18:04'),
(30, NULL, NULL, 'logout', 'users', 18, NULL, NULL, '::1', '2025-12-15 07:18:18'),
(31, NULL, 1, 'login', 'users', 1, NULL, NULL, '::1', '2025-12-15 07:18:23'),
(32, NULL, 1, 'logout', 'users', 1, NULL, NULL, '::1', '2025-12-15 07:24:04'),
(33, NULL, NULL, 'login', 'users', 18, NULL, NULL, '::1', '2025-12-15 07:24:11'),
(34, NULL, NULL, 'logout', 'users', 18, NULL, NULL, '::1', '2025-12-15 16:56:22'),
(35, NULL, NULL, 'login', 'users', 18, NULL, NULL, '::1', '2025-12-15 16:56:30'),
(36, NULL, NULL, 'logout', 'users', 18, NULL, NULL, '::1', '2025-12-16 07:21:21'),
(37, NULL, 1, 'login', 'users', 1, NULL, NULL, '::1', '2025-12-16 07:24:39'),
(38, NULL, NULL, 'logout', 'users', 19, NULL, NULL, '::1', '2025-12-16 07:24:55'),
(39, NULL, NULL, 'login', 'users', 19, NULL, NULL, '::1', '2025-12-16 07:25:05'),
(42, NULL, 1, 'login', 'users', 1, NULL, NULL, '::1', '2025-12-16 18:17:14'),
(43, NULL, 1, 'logout', 'users', 1, NULL, NULL, '::1', '2025-12-18 07:55:03'),
(44, NULL, 1, 'login', 'users', 1, NULL, NULL, '::1', '2025-12-18 13:26:48'),
(45, NULL, 1, 'logout', 'users', 1, NULL, NULL, '::1', '2025-12-18 15:43:46'),
(46, NULL, 1, 'password_reset_via_token', 'users', 1, NULL, NULL, '::1', '2025-12-18 15:57:09'),
(47, NULL, 1, 'login', 'users', 1, NULL, NULL, '::1', '2025-12-18 15:57:25'),
(48, NULL, 1, 'logout', 'users', 1, NULL, NULL, '::1', '2025-12-18 18:15:37'),
(49, NULL, 1, 'login', 'users', 1, NULL, NULL, '::1', '2025-12-19 13:34:33'),
(50, NULL, 1, 'logout', 'users', 1, NULL, NULL, '::1', '2025-12-19 14:41:32'),
(51, NULL, 1, 'login', 'users', 1, NULL, NULL, '::1', '2025-12-19 16:32:22'),
(52, NULL, 1, 'logout', 'users', 1, NULL, NULL, '::1', '2025-12-19 17:01:54'),
(53, NULL, 1, 'login', 'users', 1, NULL, NULL, '::1', '2025-12-19 17:02:04'),
(54, NULL, 1, 'logout', 'users', 1, NULL, NULL, '::1', '2025-12-19 17:02:11'),
(55, NULL, 21, 'login', 'users', 21, NULL, NULL, '::1', '2025-12-19 17:04:41'),
(56, NULL, 21, 'login', 'users', 21, NULL, NULL, '::1', '2025-12-19 17:05:07'),
(57, NULL, 21, 'login', 'users', 21, NULL, NULL, '::1', '2025-12-19 17:08:08'),
(58, NULL, 1, 'login', 'users', 1, NULL, NULL, '::1', '2025-12-20 02:58:45'),
(59, NULL, 1, 'login', 'users', 1, NULL, NULL, '::1', '2025-12-20 05:20:43'),
(60, NULL, 1, 'logout', 'users', 1, NULL, NULL, '::1', '2025-12-20 06:28:34'),
(61, NULL, 21, 'password_reset_via_token', 'users', 21, NULL, NULL, '::1', '2025-12-20 06:31:01'),
(62, NULL, 21, 'login', 'users', 21, NULL, NULL, '::1', '2025-12-20 06:31:14'),
(63, NULL, 21, 'logout', 'users', 21, NULL, NULL, '::1', '2025-12-20 12:41:27'),
(64, NULL, 1, 'login', 'users', 1, NULL, NULL, '::1', '2025-12-20 12:41:37');

-- --------------------------------------------------------

--
-- Table structure for table `bank_accounts`
--

CREATE TABLE `bank_accounts` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `account_name` varchar(100) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `account_number` varchar(50) NOT NULL,
  `ifsc_code` varchar(20) DEFAULT NULL,
  `branch` varchar(100) DEFAULT NULL,
  `account_type` enum('Savings','Current','Cash Credit','Overdraft') DEFAULT 'Current',
  `opening_balance` decimal(15,2) DEFAULT 0.00,
  `current_balance` decimal(15,2) DEFAULT 0.00,
  `chart_account_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bill_of_materials`
--

CREATE TABLE `bill_of_materials` (
  `id` int(11) NOT NULL,
  `bom_number` varchar(50) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` decimal(10,2) DEFAULT 1.00,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bom_items`
--

CREATE TABLE `bom_items` (
  `id` int(11) NOT NULL,
  `bom_id` int(11) NOT NULL,
  `component_id` int(11) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `scrap_percentage` decimal(5,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chart_of_accounts`
--

CREATE TABLE `chart_of_accounts` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `account_code` varchar(20) NOT NULL,
  `account_name` varchar(200) NOT NULL,
  `account_type_id` int(11) NOT NULL,
  `parent_account_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `company_settings`
--

CREATE TABLE `company_settings` (
  `id` int(11) NOT NULL,
  `company_name` varchar(200) NOT NULL,
  `industry_type` varchar(100) DEFAULT NULL,
  `business_type` varchar(100) DEFAULT NULL,
  `app_name` varchar(100) DEFAULT NULL,
  `theme_color` varchar(20) DEFAULT '#3b82f6',
  `address_line1` varchar(200) DEFAULT NULL,
  `address_line2` varchar(200) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'India',
  `postal_code` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `website` varchar(100) DEFAULT NULL,
  `gstin` varchar(20) DEFAULT NULL,
  `pan` varchar(20) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `bank_account_number` varchar(50) DEFAULT NULL,
  `bank_ifsc` varchar(20) DEFAULT NULL,
  `bank_branch` varchar(100) DEFAULT NULL,
  `bank_account_holder` varchar(200) DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `terms_conditions` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `financial_year_start` int(11) DEFAULT 4 COMMENT 'Month number (1-12) when financial year starts',
  `currency_code` varchar(10) DEFAULT 'INR',
  `currency_symbol` varchar(10) DEFAULT '₹',
  `date_format` varchar(20) DEFAULT 'd-m-Y',
  `timezone` varchar(50) DEFAULT 'Asia/Kolkata',
  `invoice_prefix` varchar(20) DEFAULT 'INV',
  `quotation_prefix` varchar(20) DEFAULT 'QT',
  `invoice_footer` text DEFAULT NULL COMMENT 'Footer text for invoices',
  `company_registration_number` varchar(100) DEFAULT NULL,
  `tax_registration_date` date DEFAULT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `facebook_url` varchar(255) DEFAULT NULL,
  `twitter_url` varchar(255) DEFAULT NULL,
  `instagram_url` varchar(255) DEFAULT NULL,
  `smtp_host` varchar(100) DEFAULT NULL,
  `smtp_port` int(11) DEFAULT 587,
  `smtp_username` varchar(100) DEFAULT NULL,
  `smtp_password` varchar(255) DEFAULT NULL,
  `smtp_encryption` varchar(10) DEFAULT 'tls',
  `email_from_name` varchar(100) DEFAULT NULL,
  `email_from_address` varchar(100) DEFAULT NULL,
  `enable_email_notifications` tinyint(1) DEFAULT 1,
  `invoice_due_days` int(11) DEFAULT 30 COMMENT 'Default payment terms in days',
  `low_stock_threshold` int(11) DEFAULT 10,
  `enable_multi_currency` tinyint(1) DEFAULT 0,
  `enable_barcode` tinyint(1) DEFAULT 1,
  `backup_frequency` varchar(20) DEFAULT 'daily',
  `last_backup_date` datetime DEFAULT NULL,
  `print_logo_on_invoice` tinyint(1) DEFAULT 1,
  `is_gst_registered` tinyint(1) DEFAULT 0,
  `enable_einvoicing` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `company_settings`
--

INSERT INTO `company_settings` (`id`, `company_name`, `industry_type`, `business_type`, `app_name`, `theme_color`, `address_line1`, `address_line2`, `city`, `state`, `country`, `postal_code`, `phone`, `email`, `website`, `gstin`, `pan`, `bank_name`, `bank_account_number`, `bank_ifsc`, `bank_branch`, `bank_account_holder`, `logo_path`, `terms_conditions`, `created_at`, `updated_at`, `financial_year_start`, `currency_code`, `currency_symbol`, `date_format`, `timezone`, `invoice_prefix`, `quotation_prefix`, `invoice_footer`, `company_registration_number`, `tax_registration_date`, `linkedin_url`, `facebook_url`, `twitter_url`, `instagram_url`, `smtp_host`, `smtp_port`, `smtp_username`, `smtp_password`, `smtp_encryption`, `email_from_name`, `email_from_address`, `enable_email_notifications`, `invoice_due_days`, `low_stock_threshold`, `enable_multi_currency`, `enable_barcode`, `backup_frequency`, `last_backup_date`, `print_logo_on_invoice`, `is_gst_registered`, `enable_einvoicing`) VALUES
(1, 'Acculynce Systems Ltd', 'Retail', 'Private Limited Company', NULL, '#3b82f6', 'Near SDM Court, Dhampur', NULL, 'Dhampur', 'Uttar Pradesh', 'India', '246761', '9878989878', 'garvit@acculynce.com', 'www.acculynce.com', '09EOVPR9458Q1X3', 'EOVPR9458Q', '', '', '', '', NULL, 'public/uploads/logos/logo_1_1766209716.png', '', '2025-12-14 12:23:37', '2025-12-20 06:13:13', 4, 'INR', '₹', 'd-m-Y', 'Asia/Kolkata', 'INV', 'QT', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 587, '', NULL, 'tls', NULL, NULL, 0, 30, 10, 0, 1, 'daily', NULL, 0, 1, 0),
(7, 'Lavit LLT', 'Wholesale', 'Individual', NULL, '#3b82f6', '3/54 EWS AVC JHUNSI', NULL, 'Anini', 'Arunachal Pradesh', 'India', '792101', '9878665456', 'lavit.ltt@acculynce.com', '', '09BMAPK5506J1Z1', 'BMAPK5506J', 'ICICI BANK LTD', '17857767868768`', 'ICIC001785', 'ICICI DHAMPUR', NULL, 'public/uploads/logos/logo_7_1766228908.svg', '', '2025-12-19 17:02:45', '2025-12-20 12:09:48', 4, 'INR', '₹', 'd-m-Y', 'Asia/Kolkata', 'INV', 'QT', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 587, NULL, NULL, 'tls', NULL, NULL, 0, 30, 10, 0, 1, 'daily', NULL, 0, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `contact_replies`
--

CREATE TABLE `contact_replies` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_replies`
--

INSERT INTO `contact_replies` (`id`, `request_id`, `user_id`, `message`, `created_at`) VALUES
(1, 4, 1, 'Hi, we have received your query', '2025-12-19 14:06:27'),
(2, 4, 1, 'Hi, we have received your query', '2025-12-19 14:07:29'),
(3, 4, 1, 'Ok', '2025-12-19 14:09:12');

-- --------------------------------------------------------

--
-- Table structure for table `contact_requests`
--

CREATE TABLE `contact_requests` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `marketing_opt_in` tinyint(1) DEFAULT 0,
  `status` enum('New','Replied') DEFAULT 'New',
  `replied_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_requests`
--

INSERT INTO `contact_requests` (`id`, `name`, `email`, `subject`, `message`, `marketing_opt_in`, `status`, `replied_at`, `created_at`) VALUES
(1, 'Pulkit', 'garvitrajput223@gmail.com', 'I want your product', 'Hi, i want ur product', 1, 'New', NULL, '2025-12-18 18:19:44'),
(2, 'Pulkit', 'garvitrajput223@gmail.com', 'I want your product', 'Hi, i want ur product', 1, 'New', NULL, '2025-12-18 18:21:14'),
(3, 'Lakshmi', 'rajputgarvit223@gmail.com', 'Test', 'Ghjg gj hgjh ghg yg uy', 1, 'New', NULL, '2025-12-18 18:22:44'),
(4, 'Lavit', 'lakshmisabharwal23oct@gmail.com', 'Test', 'Test', 1, 'Replied', '2025-12-19 14:09:12', '2025-12-18 18:24:55');

-- --------------------------------------------------------

--
-- Table structure for table `cost_centers`
--

CREATE TABLE `cost_centers` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `id` int(11) NOT NULL,
  `country_name` varchar(100) NOT NULL,
  `country_code` varchar(5) NOT NULL,
  `currency_code` varchar(10) DEFAULT NULL,
  `currency_symbol` varchar(10) DEFAULT NULL,
  `phone_code` varchar(10) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `customer_code` varchar(20) NOT NULL,
  `company_name` varchar(200) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `website` varchar(100) DEFAULT NULL,
  `gstin` varchar(20) DEFAULT NULL,
  `pan` varchar(20) DEFAULT NULL,
  `credit_limit` decimal(12,2) DEFAULT 0.00,
  `payment_terms` int(11) DEFAULT 0,
  `customer_type` enum('Individual','Company') DEFAULT 'Company',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `customer_segment` enum('VIP','Premium','Regular','New') DEFAULT 'Regular',
  `last_purchase_date` date DEFAULT NULL,
  `total_purchases` decimal(15,2) DEFAULT 0.00,
  `outstanding_balance` decimal(12,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `company_id`, `customer_code`, `company_name`, `contact_person`, `email`, `phone`, `mobile`, `website`, `gstin`, `pan`, `credit_limit`, `payment_terms`, `customer_type`, `is_active`, `created_at`, `updated_at`, `customer_segment`, `last_purchase_date`, `total_purchases`, `outstanding_balance`) VALUES
(1, 1, 'CUST001', '', 'Garvit', '', '', '9520447284', NULL, '', '', 0.00, 0, 'Individual', 1, '2025-12-18 13:36:48', '2025-12-18 13:36:48', 'Regular', NULL, 0.00, 0.00),
(2, 7, 'CUST-2512-9502', 'Garvit Rajput', 'Garvit Rajput', 'garvitrajput223@gmail.com', '9520447284', NULL, NULL, '', NULL, 0.00, 0, 'Company', 1, '2025-12-20 12:34:53', '2025-12-20 12:34:53', 'Regular', NULL, 0.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `customer_addresses`
--

CREATE TABLE `customer_addresses` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `address_type` enum('Billing','Shipping','Both') DEFAULT 'Both',
  `address_line1` varchar(200) DEFAULT NULL,
  `address_line2` varchar(200) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT 'India',
  `postal_code` varchar(10) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customer_addresses`
--

INSERT INTO `customer_addresses` (`id`, `customer_id`, `address_type`, `address_line1`, `address_line2`, `city`, `state`, `country`, `postal_code`, `is_default`) VALUES
(1, 1, 'Both', 'Near SDM Court', '', 'Dhampur', 'Uttar Pradesh', 'India', '246761', 1),
(2, 2, 'Billing', 'Near SDM Court', NULL, 'Dhampur', 'Uttar Pradesh', 'India', '246761', 1);

-- --------------------------------------------------------

--
-- Table structure for table `customer_notes`
--

CREATE TABLE `customer_notes` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `note` text NOT NULL,
  `note_type` enum('General','Follow-up','Complaint','Feedback') DEFAULT 'General',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `designations`
--

CREATE TABLE `designations` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT 1,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `employee_code` varchar(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `marital_status` enum('Single','Married','Divorced','Widowed') DEFAULT NULL,
  `blood_group` varchar(5) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `designation_id` int(11) DEFAULT NULL,
  `reporting_to` int(11) DEFAULT NULL,
  `date_of_joining` date NOT NULL,
  `date_of_leaving` date DEFAULT NULL,
  `employment_type` enum('Permanent','Contract','Intern','Temporary') DEFAULT 'Permanent',
  `status` enum('Active','Inactive','Terminated','Resigned') DEFAULT 'Active',
  `bank_name` varchar(100) DEFAULT NULL,
  `bank_account_number` varchar(50) DEFAULT NULL,
  `bank_ifsc` varchar(20) DEFAULT NULL,
  `pan_number` varchar(20) DEFAULT NULL,
  `aadhar_number` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_salary_structure`
--

CREATE TABLE `employee_salary_structure` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `component_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `effective_from` date NOT NULL,
  `effective_to` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fiscal_years`
--

CREATE TABLE `fiscal_years` (
  `id` int(11) NOT NULL,
  `year_name` varchar(20) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_closed` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `goods_received_notes`
--

CREATE TABLE `goods_received_notes` (
  `id` int(11) NOT NULL,
  `grn_number` varchar(50) NOT NULL,
  `po_id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `received_date` date NOT NULL,
  `received_by` int(11) DEFAULT NULL,
  `status` enum('Draft','Completed') DEFAULT 'Draft',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grn_items`
--

CREATE TABLE `grn_items` (
  `id` int(11) NOT NULL,
  `grn_id` int(11) NOT NULL,
  `po_item_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity_received` decimal(10,2) NOT NULL,
  `quantity_accepted` decimal(10,2) NOT NULL,
  `quantity_rejected` decimal(10,2) DEFAULT 0.00,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `indian_states`
--

CREATE TABLE `indian_states` (
  `id` int(11) NOT NULL,
  `state_name` varchar(100) NOT NULL,
  `state_code` varchar(10) NOT NULL,
  `gst_code` varchar(5) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `indian_states`
--

INSERT INTO `indian_states` (`id`, `state_name`, `state_code`, `gst_code`, `is_active`, `created_at`) VALUES
(1, 'Andaman and Nicobar Islands', 'AN', '35', 1, '2025-11-22 22:58:44'),
(2, 'Andhra Pradesh', 'AP', '37', 1, '2025-11-22 22:58:44'),
(3, 'Arunachal Pradesh', 'AR', '12', 1, '2025-11-22 22:58:44'),
(4, 'Assam', 'AS', '18', 1, '2025-11-22 22:58:44'),
(5, 'Bihar', 'BR', '10', 1, '2025-11-22 22:58:44'),
(6, 'Chandigarh', 'CH', '04', 1, '2025-11-22 22:58:44'),
(7, 'Chhattisgarh', 'CG', '22', 1, '2025-11-22 22:58:44'),
(8, 'Dadra and Nagar Haveli and Daman and Diu', 'DD', '26', 1, '2025-11-22 22:58:44'),
(9, 'Delhi', 'DL', '07', 1, '2025-11-22 22:58:44'),
(10, 'Goa', 'GA', '30', 1, '2025-11-22 22:58:44'),
(11, 'Gujarat', 'GJ', '24', 1, '2025-11-22 22:58:44'),
(12, 'Haryana', 'HR', '06', 1, '2025-11-22 22:58:44'),
(13, 'Himachal Pradesh', 'HP', '02', 1, '2025-11-22 22:58:44'),
(14, 'Jammu and Kashmir', 'JK', '01', 1, '2025-11-22 22:58:44'),
(15, 'Jharkhand', 'JH', '20', 1, '2025-11-22 22:58:44'),
(16, 'Karnataka', 'KA', '29', 1, '2025-11-22 22:58:44'),
(17, 'Kerala', 'KL', '32', 1, '2025-11-22 22:58:44'),
(18, 'Ladakh', 'LA', '38', 1, '2025-11-22 22:58:44'),
(19, 'Lakshadweep', 'LD', '31', 1, '2025-11-22 22:58:44'),
(20, 'Madhya Pradesh', 'MP', '23', 1, '2025-11-22 22:58:44'),
(21, 'Maharashtra', 'MH', '27', 1, '2025-11-22 22:58:44'),
(22, 'Manipur', 'MN', '14', 1, '2025-11-22 22:58:44'),
(23, 'Meghalaya', 'ML', '17', 1, '2025-11-22 22:58:44'),
(24, 'Mizoram', 'MZ', '15', 1, '2025-11-22 22:58:44'),
(25, 'Nagaland', 'NL', '13', 1, '2025-11-22 22:58:44'),
(26, 'Odisha', 'OR', '21', 1, '2025-11-22 22:58:44'),
(27, 'Puducherry', 'PY', '34', 1, '2025-11-22 22:58:44'),
(28, 'Punjab', 'PB', '03', 1, '2025-11-22 22:58:44'),
(29, 'Rajasthan', 'RJ', '08', 1, '2025-11-22 22:58:44'),
(30, 'Sikkim', 'SK', '11', 1, '2025-11-22 22:58:44'),
(31, 'Tamil Nadu', 'TN', '33', 1, '2025-11-22 22:58:44'),
(32, 'Telangana', 'TS', '36', 1, '2025-11-22 22:58:44'),
(33, 'Tripura', 'TR', '16', 1, '2025-11-22 22:58:44'),
(34, 'Uttar Pradesh', 'UP', '09', 1, '2025-11-22 22:58:44'),
(35, 'Uttarakhand', 'UK', '05', 1, '2025-11-22 22:58:44'),
(36, 'West Bengal', 'WB', '19', 1, '2025-11-22 22:58:44');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `sales_order_id` int(11) DEFAULT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `payment_status` enum('Unpaid','Partially Paid','Paid','Overdue') DEFAULT 'Unpaid',
  `status` enum('Draft','Sent','Paid','Partially Paid','Overdue','Cancelled') DEFAULT 'Draft',
  `subtotal` decimal(12,2) DEFAULT 0.00,
  `tax_amount` decimal(12,2) DEFAULT 0.00,
  `round_off_amount` decimal(10,2) DEFAULT 0.00,
  `discount_amount` decimal(12,2) DEFAULT 0.00,
  `total_amount` decimal(12,2) DEFAULT 0.00,
  `paid_amount` decimal(12,2) DEFAULT 0.00,
  `balance_amount` decimal(12,2) GENERATED ALWAYS AS (`total_amount` - `paid_amount`) STORED,
  `notes` text DEFAULT NULL,
  `courier_name` varchar(255) DEFAULT NULL,
  `tracking_id` varchar(255) DEFAULT NULL,
  `shipping_charges` decimal(10,2) DEFAULT 0.00,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `company_id`, `invoice_number`, `customer_id`, `sales_order_id`, `invoice_date`, `due_date`, `payment_status`, `status`, `subtotal`, `tax_amount`, `round_off_amount`, `discount_amount`, `total_amount`, `paid_amount`, `notes`, `courier_name`, `tracking_id`, `shipping_charges`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'INV-2025-001', 1, NULL, '2025-12-18', '2025-12-25', 'Unpaid', 'Paid', 805.08, 144.92, 0.00, 50.00, 1010.00, 1010.00, '', 'Delhivery', '79868768768', 60.00, 1, '2025-12-18 13:37:15', '2025-12-18 13:41:40'),
(2, 1, 'INV-2025-002', 1, NULL, '2025-12-18', '2025-12-25', 'Unpaid', 'Paid', 952.38, 47.62, 0.00, 0.00, 1200.00, 1200.00, '', 'Delhivery', '897836837', 200.00, 1, '2025-12-18 13:56:15', '2025-12-19 16:39:55'),
(4, 1, 'INV-2025-003', 1, NULL, '2025-12-18', '2025-12-25', 'Paid', 'Paid', 847.46, 152.54, 0.00, 0.00, 1200.00, 1200.00, '', 'Delhivery', '897836837', 200.00, 1, '2025-12-18 14:02:02', '2025-12-18 14:02:02'),
(5, 1, 'INV-2025-004', 1, NULL, '2025-12-18', '2025-12-25', 'Paid', 'Paid', 847.46, 152.54, 0.00, 0.00, 1200.00, 1200.00, '', 'Delhivery', '897836837', 200.00, 1, '2025-12-18 14:04:10', '2025-12-18 14:04:10'),
(6, 1, 'INV-2025-005', 1, NULL, '2025-12-20', '2025-12-27', 'Paid', 'Paid', 0.00, 0.00, 0.00, 100.00, 100.00, 900.00, '', '', '', 200.00, 1, '2025-12-20 12:49:14', '2025-12-20 12:49:14'),
(7, 1, 'INV-2025-006', 1, NULL, '2025-12-20', '2025-12-27', 'Unpaid', 'Draft', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, '', '', '', 0.00, 1, '2025-12-20 12:55:26', '2025-12-20 12:55:26'),
(8, 1, 'INV-2025-007', 1, NULL, '2025-12-20', '2025-12-27', 'Unpaid', 'Paid', 847.46, 152.54, 0.00, 0.00, 1000.00, 1000.00, '', '', '', 0.00, 1, '2025-12-20 13:00:43', '2025-12-20 16:57:30');

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

CREATE TABLE `invoice_items` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `tax_rate` decimal(5,2) DEFAULT 0.00,
  `discount_percent` decimal(5,2) DEFAULT 0.00,
  `line_total` decimal(12,2) DEFAULT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `warranty_period` varchar(100) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoice_items`
--

INSERT INTO `invoice_items` (`id`, `invoice_id`, `company_id`, `product_id`, `description`, `quantity`, `unit_price`, `tax_rate`, `discount_percent`, `line_total`, `serial_number`, `warranty_period`, `expiry_date`) VALUES
(1, 1, 1, 1, 'Wireless Mouse', 1.00, 1000.00, 18.00, 5.00, 950.00, '', '', NULL),
(2, 2, 1, 1, 'Wireless Mouse', 1.00, 1000.00, 5.00, 0.00, 1000.00, '', '', NULL),
(4, 4, 1, 1, 'Wireless Mouse', 1.00, 1000.00, 18.00, 0.00, 1000.00, '', '', NULL),
(5, 5, 1, 1, 'Wireless Mouse', 1.00, 1000.00, 18.00, 0.00, 1000.00, '', '', NULL),
(6, 6, 1, 1, 'Wireless Mouse', 1.00, 0.00, 18.00, 20.00, 0.00, '', '', NULL),
(7, 7, 1, 1, 'Wireless Mouse', 1.00, 0.00, 18.00, 20.00, 0.00, '', '', NULL),
(8, 8, 1, 1, 'Wireless Mouse', 1.00, 1000.00, 18.00, 0.00, 1000.00, '', '', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `journal_entries`
--

CREATE TABLE `journal_entries` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `entry_number` varchar(50) NOT NULL,
  `entry_date` date NOT NULL,
  `fiscal_year_id` int(11) NOT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `total_debit` decimal(15,2) DEFAULT 0.00,
  `total_credit` decimal(15,2) DEFAULT 0.00,
  `status` enum('Draft','Posted','Cancelled') DEFAULT 'Draft',
  `created_by` int(11) DEFAULT NULL,
  `posted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `journal_entry_lines`
--

CREATE TABLE `journal_entry_lines` (
  `id` int(11) NOT NULL,
  `journal_entry_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `debit_amount` decimal(15,2) DEFAULT 0.00,
  `credit_amount` decimal(15,2) DEFAULT 0.00,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leads`
--

CREATE TABLE `leads` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `lead_number` varchar(50) NOT NULL,
  `company_name` varchar(200) DEFAULT NULL,
  `contact_person` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `source_id` int(11) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `expected_revenue` decimal(12,2) DEFAULT NULL,
  `probability` int(11) DEFAULT 0,
  `expected_close_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lead_sources`
--

CREATE TABLE `lead_sources` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lead_statuses`
--

CREATE TABLE `lead_statuses` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `color` varchar(7) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_applications`
--

CREATE TABLE `leave_applications` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `employee_id` int(11) NOT NULL,
  `leave_type_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_days` decimal(4,1) NOT NULL,
  `reason` text NOT NULL,
  `status` enum('Pending','Approved','Rejected','Cancelled') DEFAULT 'Pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_types`
--

CREATE TABLE `leave_types` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `days_per_year` int(11) DEFAULT 0,
  `is_paid` tinyint(1) DEFAULT 1,
  `requires_approval` tinyint(1) DEFAULT 1,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `opportunities`
--

CREATE TABLE `opportunities` (
  `id` int(11) NOT NULL,
  `opportunity_number` varchar(50) NOT NULL,
  `lead_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `probability` int(11) DEFAULT 0,
  `stage` enum('Qualification','Needs Analysis','Proposal','Negotiation','Closed Won','Closed Lost') DEFAULT 'Qualification',
  `expected_close_date` date DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `payment_number` varchar(50) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `invoice_id` int(11) NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_method` enum('Cash','Cheque','Bank Transfer','UPI','Card','Other') DEFAULT 'Cash',
  `reference_number` varchar(100) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `payment_number`, `company_id`, `invoice_id`, `payment_date`, `amount`, `payment_method`, `reference_number`, `bank_name`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'PAY-0001', 1, 4, '2025-12-18', 1200.00, 'Cash', NULL, NULL, '', 1, '2025-12-18 14:02:02', '2025-12-18 14:02:02'),
(2, 'PAY-0002', 1, 5, '2025-12-18', 1200.00, 'Cash', NULL, NULL, 'PAID BY CASH', 1, '2025-12-18 14:04:10', '2025-12-18 14:04:10'),
(3, 'PAY-0003', 1, 6, '2025-12-20', 900.00, 'Cash', NULL, NULL, 'cash', 1, '2025-12-20 12:49:14', '2025-12-20 12:49:14');

-- --------------------------------------------------------

--
-- Table structure for table `payments_made`
--

CREATE TABLE `payments_made` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `payment_number` varchar(50) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_mode` enum('Cash','Cheque','Bank Transfer','Credit Card','UPI','Other') DEFAULT 'Bank Transfer',
  `reference_number` varchar(100) DEFAULT NULL,
  `bank_account_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments_received`
--

CREATE TABLE `payments_received` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `payment_number` varchar(50) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_mode` enum('Cash','Cheque','Bank Transfer','Credit Card','UPI','Other') DEFAULT 'Bank Transfer',
  `reference_number` varchar(100) DEFAULT NULL,
  `bank_account_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments_received`
--

INSERT INTO `payments_received` (`id`, `company_id`, `payment_number`, `customer_id`, `payment_date`, `amount`, `payment_mode`, `reference_number`, `bank_account_id`, `notes`, `created_by`, `created_at`) VALUES
(1, 1, 'PAY-0001-1766065300', 1, '2025-12-18', 1010.00, 'Cash', '', NULL, '', 1, '2025-12-18 13:41:40'),
(2, 1, 'PAY-0002-1766162395', 1, '2025-12-19', 1200.00, 'Cash', '', NULL, '', 1, '2025-12-19 16:39:55'),
(3, 1, 'PAY-0008-1766249850', 1, '2025-12-20', 1000.00, 'Cash', '', NULL, '', 1, '2025-12-20 16:57:30');

-- --------------------------------------------------------

--
-- Table structure for table `payment_allocations`
--

CREATE TABLE `payment_allocations` (
  `id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `allocated_amount` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_allocations`
--

INSERT INTO `payment_allocations` (`id`, `payment_id`, `invoice_id`, `company_id`, `allocated_amount`) VALUES
(1, 1, 1, 1, 1010.00),
(2, 1, 4, 1, 1200.00),
(3, 2, 5, 1, 1200.00),
(4, 2, 2, 1, 1200.00),
(5, 3, 6, 1, 900.00),
(6, 3, 8, 1, 1000.00);

-- --------------------------------------------------------

--
-- Table structure for table `payment_made_allocations`
--

CREATE TABLE `payment_made_allocations` (
  `id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `bill_id` int(11) NOT NULL,
  `allocated_amount` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `method_name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_reminders`
--

CREATE TABLE `payment_reminders` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `reminder_date` date NOT NULL,
  `reminder_type` enum('Email','SMS','Both') DEFAULT 'Email',
  `status` enum('Pending','Sent','Failed') DEFAULT 'Pending',
  `sent_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_transactions`
--

CREATE TABLE `payment_transactions` (
  `id` int(11) NOT NULL,
  `subscription_id` int(11) NOT NULL,
  `razorpay_payment_id` varchar(100) DEFAULT NULL,
  `razorpay_order_id` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'INR',
  `status` enum('pending','success','failed') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_date` datetime DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll`
--

CREATE TABLE `payroll` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `month` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `gross_salary` decimal(10,2) NOT NULL,
  `total_deductions` decimal(10,2) DEFAULT 0.00,
  `net_salary` decimal(10,2) NOT NULL,
  `payment_date` date DEFAULT NULL,
  `payment_status` enum('Pending','Processed','Paid') DEFAULT 'Pending',
  `payment_method` enum('Bank Transfer','Cash','Cheque') DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_components`
--

CREATE TABLE `payroll_components` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('Earning','Deduction') NOT NULL,
  `calculation_type` enum('Fixed','Percentage','Formula') DEFAULT 'Fixed',
  `formula` text DEFAULT NULL,
  `is_taxable` tinyint(1) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_details`
--

CREATE TABLE `payroll_details` (
  `id` int(11) NOT NULL,
  `payroll_id` int(11) NOT NULL,
  `component_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `module` varchar(50) NOT NULL,
  `action` varchar(50) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `module`, `action`, `description`) VALUES
(1, 'hrm', 'view', 'View Hrm module'),
(2, 'hrm', 'create', 'Create Hrm module'),
(3, 'hrm', 'edit', 'Edit Hrm module'),
(4, 'hrm', 'delete', 'Delete Hrm module'),
(5, 'inventory', 'view', 'View Inventory module'),
(6, 'inventory', 'create', 'Create Inventory module'),
(7, 'inventory', 'edit', 'Edit Inventory module'),
(8, 'inventory', 'delete', 'Delete Inventory module'),
(9, 'sales', 'view', 'View Sales module'),
(10, 'sales', 'create', 'Create Sales module'),
(11, 'sales', 'edit', 'Edit Sales module'),
(12, 'sales', 'delete', 'Delete Sales module'),
(13, 'purchases', 'view', 'View Purchases module'),
(14, 'purchases', 'create', 'Create Purchases module'),
(15, 'purchases', 'edit', 'Edit Purchases module'),
(16, 'purchases', 'delete', 'Delete Purchases module'),
(17, 'accounting', 'view', 'View Accounting module'),
(18, 'accounting', 'create', 'Create Accounting module'),
(19, 'accounting', 'edit', 'Edit Accounting module'),
(20, 'accounting', 'delete', 'Delete Accounting module'),
(21, 'crm', 'view', 'View Crm module'),
(22, 'crm', 'create', 'Create Crm module'),
(23, 'crm', 'edit', 'Edit Crm module'),
(24, 'crm', 'delete', 'Delete Crm module'),
(25, 'production', 'view', 'View Production module'),
(26, 'production', 'create', 'Create Production module'),
(27, 'production', 'edit', 'Edit Production module'),
(28, 'production', 'delete', 'Delete Production module'),
(29, 'settings', 'view', 'View Settings module'),
(30, 'settings', 'edit', 'Edit Settings module'),
(31, 'reports', 'view', 'View Reports module');

-- --------------------------------------------------------

--
-- Table structure for table `production_entries`
--

CREATE TABLE `production_entries` (
  `id` int(11) NOT NULL,
  `entry_number` varchar(50) NOT NULL,
  `work_order_id` int(11) NOT NULL,
  `production_date` date NOT NULL,
  `quantity_produced` decimal(10,2) NOT NULL,
  `quantity_rejected` decimal(10,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `product_code` varchar(50) NOT NULL,
  `name` varchar(200) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `uom_id` int(11) NOT NULL,
  `product_type` enum('Goods','Service','Raw Material','Finished Goods') DEFAULT 'Goods',
  `hsn_code` varchar(20) DEFAULT NULL,
  `barcode` varchar(100) DEFAULT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `reorder_level` decimal(10,2) DEFAULT 0.00,
  `reorder_quantity` decimal(10,2) DEFAULT 0.00,
  `standard_cost` decimal(10,2) DEFAULT 0.00,
  `selling_price` decimal(10,2) DEFAULT 0.00,
  `tax_rate` decimal(5,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `has_serial_number` tinyint(1) DEFAULT 0,
  `has_warranty` tinyint(1) DEFAULT 0,
  `has_expiry_date` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `company_id`, `product_code`, `name`, `category_id`, `description`, `uom_id`, `product_type`, `hsn_code`, `barcode`, `sku`, `reorder_level`, `reorder_quantity`, `standard_cost`, `selling_price`, `tax_rate`, `is_active`, `has_serial_number`, `has_warranty`, `has_expiry_date`, `created_at`, `updated_at`) VALUES
(1, 1, 'PRD001', 'Wireless Mouse', 1, '', 1, 'Goods', '22222', '', NULL, 0.00, 0.00, 0.00, 1000.00, 18.00, 1, 0, 0, 0, '2025-12-18 13:35:32', '2025-12-18 13:35:32'),
(2, 7, 'PRD002', 'Test 1 Product', 1, '', 1, 'Goods', '7878', '', NULL, 0.00, 0.00, 0.00, 200.00, 18.00, 0, 1, 1, 0, '2025-12-20 12:23:30', '2025-12-20 12:38:45'),
(3, 7, 'PRD003', 'Test', 1, '', 1, 'Goods', '3124', '', NULL, 0.00, 0.00, 0.00, 200.00, 18.00, 0, 1, 1, 1, '2025-12-20 12:35:30', '2025-12-20 12:38:43'),
(4, 7, 'PRD004', 'Test Product', 1, '', 1, 'Goods', '78798', '', NULL, 0.00, 0.00, 0.00, 2000.00, 18.00, 0, 1, 1, 0, '2025-12-20 12:37:32', '2025-12-20 12:38:41');

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_categories`
--

INSERT INTO `product_categories` (`id`, `company_id`, `name`, `parent_id`, `description`, `is_active`, `created_at`) VALUES
(1, 1, 'Test', NULL, '', 1, '2025-12-18 13:34:01'),
(2, 7, 'Smartphone', NULL, '', 1, '2025-12-20 12:39:34');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_invoices`
--

CREATE TABLE `purchase_invoices` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `bill_number` varchar(50) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `po_id` int(11) DEFAULT NULL,
  `bill_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `status` enum('Draft','Submitted','Paid','Partially Paid','Overdue') DEFAULT 'Draft',
  `subtotal` decimal(12,2) DEFAULT 0.00,
  `tax_amount` decimal(12,2) DEFAULT 0.00,
  `discount_amount` decimal(12,2) DEFAULT 0.00,
  `total_amount` decimal(12,2) DEFAULT 0.00,
  `paid_amount` decimal(12,2) DEFAULT 0.00,
  `balance_amount` decimal(12,2) GENERATED ALWAYS AS (`total_amount` - `paid_amount`) STORED,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_invoice_items`
--

CREATE TABLE `purchase_invoice_items` (
  `id` int(11) NOT NULL,
  `bill_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `tax_rate` decimal(5,2) DEFAULT 0.00,
  `discount_percent` decimal(5,2) DEFAULT 0.00,
  `line_total` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `po_number` varchar(50) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `order_date` date NOT NULL,
  `expected_delivery_date` date DEFAULT NULL,
  `status` enum('Draft','Sent','Confirmed','Partially Received','Received','Cancelled') DEFAULT 'Draft',
  `subtotal` decimal(12,2) DEFAULT 0.00,
  `tax_amount` decimal(12,2) DEFAULT 0.00,
  `discount_amount` decimal(12,2) DEFAULT 0.00,
  `shipping_charges` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(12,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_items`
--

CREATE TABLE `purchase_order_items` (
  `id` int(11) NOT NULL,
  `po_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `received_quantity` decimal(10,2) DEFAULT 0.00,
  `unit_price` decimal(10,2) NOT NULL,
  `tax_rate` decimal(5,2) DEFAULT 0.00,
  `discount_percent` decimal(5,2) DEFAULT 0.00,
  `line_total` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quotations`
--

CREATE TABLE `quotations` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `quotation_number` varchar(50) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `quotation_date` date NOT NULL,
  `valid_until` date DEFAULT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `status` enum('Draft','Sent','Accepted','Rejected','Expired') DEFAULT 'Draft',
  `subtotal` decimal(12,2) DEFAULT 0.00,
  `tax_amount` decimal(12,2) DEFAULT 0.00,
  `round_off_amount` decimal(10,2) DEFAULT 0.00,
  `discount_amount` decimal(12,2) DEFAULT 0.00,
  `total_amount` decimal(12,2) DEFAULT 0.00,
  `terms_conditions` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `pipeline_stage` enum('Lead','Quotation','Negotiation','Won','Lost') DEFAULT 'Quotation',
  `expected_close_date` date DEFAULT NULL,
  `win_probability` int(11) DEFAULT 50,
  `lost_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quotation_items`
--

CREATE TABLE `quotation_items` (
  `id` int(11) NOT NULL,
  `quotation_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL DEFAULT 1,
  `product_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `tax_rate` decimal(5,2) DEFAULT 0.00,
  `discount_percent` decimal(5,2) DEFAULT 0.00,
  `line_total` decimal(12,2) GENERATED ALWAYS AS (`quantity` * `unit_price` * (1 - `discount_percent` / 100) * (1 + `tax_rate` / 100)) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `company_id`, `name`, `description`, `created_at`) VALUES
(1, NULL, 'Super Admin', 'Full Access', '2025-12-14 12:15:55'),
(2, NULL, 'Admin', 'Company Administrator', '2025-12-14 12:15:55'),
(3, NULL, 'Employee', 'Standard User', '2025-12-14 12:15:55'),
(6, 1, 'Sales', '', '2025-12-17 15:03:20');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(2, 5),
(2, 6),
(2, 7),
(2, 8),
(2, 9),
(2, 10),
(2, 11),
(2, 12),
(2, 13),
(2, 14),
(2, 15),
(2, 16),
(2, 17),
(2, 18),
(2, 19),
(2, 20),
(2, 21),
(2, 22),
(2, 23),
(2, 24),
(2, 25),
(2, 26),
(2, 27),
(2, 28),
(2, 29),
(2, 30),
(2, 31),
(3, 5),
(3, 9),
(3, 10),
(3, 11),
(3, 13),
(3, 14),
(3, 17),
(3, 18),
(3, 19),
(3, 21),
(3, 22),
(3, 23),
(3, 24),
(3, 31);

-- --------------------------------------------------------

--
-- Table structure for table `sales_orders`
--

CREATE TABLE `sales_orders` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `order_number` varchar(50) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `quotation_id` int(11) DEFAULT NULL,
  `order_date` date NOT NULL,
  `expected_delivery_date` date DEFAULT NULL,
  `status` enum('Draft','Confirmed','In Progress','Completed','Cancelled') DEFAULT 'Draft',
  `subtotal` decimal(12,2) DEFAULT 0.00,
  `tax_amount` decimal(12,2) DEFAULT 0.00,
  `round_off_amount` decimal(10,2) DEFAULT 0.00,
  `discount_amount` decimal(12,2) DEFAULT 0.00,
  `shipping_charges` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(12,2) DEFAULT 0.00,
  `payment_status` enum('Unpaid','Partially Paid','Paid') DEFAULT 'Unpaid',
  `notes` text DEFAULT NULL,
  `courier_name` varchar(255) DEFAULT NULL,
  `tracking_id` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sales_orders`
--

INSERT INTO `sales_orders` (`id`, `company_id`, `order_number`, `customer_id`, `quotation_id`, `order_date`, `expected_delivery_date`, `status`, `subtotal`, `tax_amount`, `round_off_amount`, `discount_amount`, `shipping_charges`, `total_amount`, `payment_status`, `notes`, `courier_name`, `tracking_id`, `created_by`, `created_at`, `updated_at`) VALUES
(2, 1, 'SO-2025-001', 1, NULL, '2025-12-18', '2025-12-25', 'Confirmed', 1000.00, 180.00, 0.00, 0.00, 200.00, 1380.00, 'Unpaid', '', 'Delhivery', '897836837', 1, '2025-12-18 13:42:53', '2025-12-18 13:42:53');

-- --------------------------------------------------------

--
-- Table structure for table `sales_order_items`
--

CREATE TABLE `sales_order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `delivered_quantity` decimal(10,2) DEFAULT 0.00,
  `unit_price` decimal(10,2) NOT NULL,
  `tax_rate` decimal(5,2) DEFAULT 0.00,
  `discount_percent` decimal(5,2) DEFAULT 0.00,
  `line_total` decimal(12,2) DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sales_order_items`
--

INSERT INTO `sales_order_items` (`id`, `order_id`, `product_id`, `description`, `quantity`, `delivered_quantity`, `unit_price`, `tax_rate`, `discount_percent`, `line_total`, `company_id`) VALUES
(1, 2, 1, 'Wireless Mouse', 1.00, 0.00, 1000.00, 18.00, 0.00, 1180.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `sales_targets`
--

CREATE TABLE `sales_targets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `target_period` enum('Monthly','Quarterly','Yearly') DEFAULT 'Monthly',
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `target_amount` decimal(15,2) NOT NULL,
  `achieved_amount` decimal(15,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_balance`
--

CREATE TABLE `stock_balance` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `quantity` decimal(10,2) DEFAULT 0.00,
  `reserved_quantity` decimal(10,2) DEFAULT 0.00,
  `available_quantity` decimal(10,2) GENERATED ALWAYS AS (`quantity` - `reserved_quantity`) STORED,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_balance`
--

INSERT INTO `stock_balance` (`id`, `company_id`, `product_id`, `warehouse_id`, `quantity`, `reserved_quantity`, `last_updated`) VALUES
(1, 1, 1, 1, 93.00, 0.00, '2025-12-20 13:00:43'),
(2, 7, 4, 4, 100.00, 0.00, '2025-12-20 12:37:32');

-- --------------------------------------------------------

--
-- Table structure for table `stock_transactions`
--

CREATE TABLE `stock_transactions` (
  `id` bigint(20) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `transaction_type` enum('IN','OUT','TRANSFER','ADJUSTMENT') NOT NULL,
  `product_id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `remarks` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_transactions`
--

INSERT INTO `stock_transactions` (`id`, `company_id`, `transaction_type`, `product_id`, `warehouse_id`, `quantity`, `reference_type`, `reference_id`, `transaction_date`, `remarks`, `created_by`) VALUES
(1, 1, 'IN', 1, 1, 100.00, 'Opening Stock', 0, '2025-12-18 13:35:32', 'Initial stock from Quick Add', NULL),
(2, 1, 'OUT', 1, 1, 1.00, 'invoice', 1, '2025-12-18 13:37:15', 'Sale from invoice INV-2025-001', 1),
(3, 1, 'OUT', 1, 1, 1.00, 'invoice', 2, '2025-12-18 13:56:15', 'Sale from invoice INV-2025-002', 1),
(5, 1, 'OUT', 1, 1, 1.00, 'invoice', 4, '2025-12-18 14:02:02', 'Sale from invoice INV-2025-003', 1),
(6, 1, 'OUT', 1, 1, 1.00, 'invoice', 5, '2025-12-18 14:04:10', 'Sale from invoice INV-2025-004', 1),
(7, 7, 'IN', 4, 4, 100.00, 'Opening Stock', 0, '2025-12-20 12:37:32', 'Initial stock from Quick Add', NULL),
(8, 1, 'OUT', 1, 1, 1.00, 'invoice', 6, '2025-12-20 12:49:14', 'Sale from invoice INV-2025-005', 1),
(9, 1, 'OUT', 1, 1, 1.00, 'invoice', 7, '2025-12-20 12:55:26', 'Sale from invoice INV-2025-006', 1),
(10, 1, 'OUT', 1, 1, 1.00, 'invoice', 8, '2025-12-20 13:00:43', 'Sale from invoice INV-2025-007', 1);

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `plan_name` varchar(50) NOT NULL,
  `plan_price` decimal(10,2) NOT NULL,
  `billing_cycle` enum('monthly','annual') DEFAULT 'monthly',
  `status` enum('trial','active','cancelled','expired') DEFAULT 'trial',
  `cancelled_at` datetime DEFAULT NULL,
  `trial_ends_at` datetime DEFAULT NULL,
  `current_period_start` datetime DEFAULT NULL,
  `current_period_end` datetime DEFAULT NULL,
  `razorpay_subscription_id` varchar(100) DEFAULT NULL,
  `razorpay_customer_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `user_id`, `company_id`, `plan_name`, `plan_price`, `billing_cycle`, `status`, `cancelled_at`, `trial_ends_at`, `current_period_start`, `current_period_end`, `razorpay_subscription_id`, `razorpay_customer_id`, `created_at`, `updated_at`) VALUES
(4, 1, 1, 'Enterprise', 0.00, 'monthly', 'cancelled', NULL, NULL, '2025-12-17 19:22:00', '2025-12-17 19:23:48', NULL, NULL, '2025-12-17 13:33:34', '2025-12-17 13:53:48'),
(5, 1, 1, 'Starter', 0.00, 'monthly', 'cancelled', NULL, NULL, '2025-12-17 19:23:00', '2025-12-17 19:24:15', NULL, NULL, '2025-12-17 13:53:53', '2025-12-17 13:54:15'),
(6, 1, 1, 'Starter', 0.00, 'monthly', 'cancelled', '2025-12-17 20:38:43', NULL, '2025-12-17 19:24:00', '2026-01-17 19:24:00', NULL, NULL, '2025-12-17 13:54:20', '2025-12-17 15:08:43'),
(7, 1, 1, 'Enterprise', 0.00, 'monthly', 'cancelled', '2025-12-17 20:38:43', NULL, '2025-12-17 20:35:00', '2026-01-17 20:35:00', NULL, NULL, '2025-12-17 15:05:34', '2025-12-17 15:08:43'),
(8, 1, 1, 'Professional', 0.00, 'monthly', 'cancelled', '2025-12-17 20:38:43', NULL, '2025-12-17 20:35:00', '2026-01-17 20:35:00', NULL, NULL, '2025-12-17 15:05:56', '2025-12-17 15:08:43'),
(9, 1, 1, 'Starter', 0.00, 'monthly', 'cancelled', '2025-12-20 18:36:49', NULL, '2025-12-17 20:37:00', '2026-01-17 20:37:00', NULL, NULL, '2025-12-17 15:08:43', '2025-12-20 13:06:49'),
(10, 21, 7, 'Starter', 2999.00, 'monthly', 'cancelled', '2025-12-20 11:58:22', '2026-01-02 22:33:07', '2025-12-19 22:33:07', '2026-01-19 22:33:07', NULL, NULL, '2025-12-19 17:03:07', '2025-12-20 06:28:22'),
(11, 21, 7, 'Professional', 0.00, 'monthly', 'active', NULL, NULL, '2025-12-20 11:58:00', '2026-01-20 11:58:00', NULL, NULL, '2025-12-20 06:28:22', '2025-12-20 06:28:22'),
(12, 1, 1, 'Starter', 0.00, 'monthly', 'active', NULL, NULL, '2025-12-20 18:36:00', '2050-01-01 18:36:00', NULL, NULL, '2025-12-20 13:06:49', '2025-12-20 13:06:49');

-- --------------------------------------------------------

--
-- Table structure for table `subscription_plans`
--

CREATE TABLE `subscription_plans` (
  `id` int(11) NOT NULL,
  `plan_name` varchar(50) NOT NULL,
  `plan_code` varchar(20) NOT NULL,
  `monthly_price` decimal(10,2) NOT NULL,
  `annual_price` decimal(10,2) NOT NULL,
  `max_users` int(11) NOT NULL,
  `storage_gb` int(11) NOT NULL,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subscription_plans`
--

INSERT INTO `subscription_plans` (`id`, `plan_name`, `plan_code`, `monthly_price`, `annual_price`, `max_users`, `storage_gb`, `features`, `is_active`, `display_order`, `created_at`) VALUES
(1, 'Starter', 'starter', 2999.00, 29990.00, 5, 5, '[\"Up to 5 Users\",\"Basic Reporting\",\"5GB Storage\",\"Email Support\"]', 1, 1, '2025-12-15 04:32:37'),
(2, 'Professional', 'professional', 5999.00, 59990.00, 20, 50, '[\"Up to 20 Users\",\"Advanced Reporting\",\"50GB Storage\",\"Priority Support\",\"API Access\"]', 1, 2, '2025-12-15 04:32:37'),
(3, 'Enterprise', 'enterprise', 14999.00, 149990.00, 999, 999, '[\"Unlimited Users\",\"Custom Reporting\",\"Unlimited Storage\",\"24\\/7 Phone Support\",\"Dedicated Account Manager\"]', 1, 3, '2025-12-15 04:32:37');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `supplier_code` varchar(20) NOT NULL,
  `company_name` varchar(200) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `website` varchar(100) DEFAULT NULL,
  `gstin` varchar(20) DEFAULT NULL,
  `pan` varchar(20) DEFAULT NULL,
  `credit_limit` decimal(12,2) DEFAULT 0.00,
  `payment_terms` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier_addresses`
--

CREATE TABLE `supplier_addresses` (
  `id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `address_type` enum('Billing','Shipping','Both') DEFAULT 'Both',
  `address_line1` varchar(200) DEFAULT NULL,
  `address_line2` varchar(200) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT 'India',
  `postal_code` varchar(10) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_categories`
--

CREATE TABLE `support_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `support_categories`
--

INSERT INTO `support_categories` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Technical Issue', NULL, '2025-12-18 23:18:21'),
(2, 'Billing & Payments', NULL, '2025-12-18 23:18:21'),
(3, 'General Inquiry', NULL, '2025-12-18 23:18:21'),
(4, 'Feature Request', NULL, '2025-12-18 23:18:21');

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` int(11) NOT NULL,
  `ticket_number` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `priority` enum('Low','Medium','High','Critical') DEFAULT 'Medium',
  `status` enum('Open','In Progress','Awaiting Reply','Resolved','Closed') DEFAULT 'Open',
  `assigned_to` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `support_tickets`
--

INSERT INTO `support_tickets` (`id`, `ticket_number`, `user_id`, `category_id`, `subject`, `priority`, `status`, `assigned_to`, `created_at`, `updated_at`) VALUES
(1, 'TKT-2025-99AA19', 1, 3, 'Test Enquiry', 'Medium', 'Closed', NULL, '2025-12-18 23:25:54', '2025-12-19 19:20:34');

-- --------------------------------------------------------

--
-- Table structure for table `support_ticket_replies`
--

CREATE TABLE `support_ticket_replies` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `support_ticket_replies`
--

INSERT INTO `support_ticket_replies` (`id`, `ticket_id`, `user_id`, `message`, `attachment_path`, `created_at`) VALUES
(1, 1, 1, 'Hi This is test enquiry', NULL, '2025-12-18 23:25:54'),
(2, 1, 1, 'Hi', NULL, '2025-12-18 23:33:33'),
(3, 1, 1, 'Hi', NULL, '2025-12-18 23:34:27'),
(4, 1, 1, 'Not close', NULL, '2025-12-18 23:39:55'),
(5, 1, 1, 'Hi', NULL, '2025-12-19 19:11:36'),
(6, 1, 1, 'Close This', NULL, '2025-12-19 19:14:21');

-- --------------------------------------------------------

--
-- Table structure for table `system_broadcasts`
--

CREATE TABLE `system_broadcasts` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','warning','danger','success') DEFAULT 'info',
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `target_company_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
(1, 'maintenance_mode', '1', '2025-12-17 15:09:21', '2025-12-20 16:10:38');

-- --------------------------------------------------------

--
-- Table structure for table `tax_rates`
--

CREATE TABLE `tax_rates` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `tax_name` varchar(50) NOT NULL,
  `tax_rate` decimal(5,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `units_of_measure`
--

CREATE TABLE `units_of_measure` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `name` varchar(50) NOT NULL,
  `symbol` varchar(10) NOT NULL,
  `type` enum('Weight','Length','Volume','Quantity','Other') DEFAULT 'Quantity'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `units_of_measure`
--

INSERT INTO `units_of_measure` (`id`, `company_id`, `name`, `symbol`, `type`) VALUES
(1, 1, 'Pcs', 'P', 'Quantity');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `avatar_path` varchar(255) DEFAULT NULL,
  `company_name` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `email_verification_token` varchar(255) DEFAULT NULL,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `password_reset_expires_at` datetime DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `onboarding_completed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `company_id`, `username`, `email`, `avatar_path`, `company_name`, `password_hash`, `full_name`, `is_active`, `email_verification_token`, `password_reset_token`, `password_reset_expires_at`, `email_verified`, `last_login`, `created_at`, `updated_at`, `onboarding_completed`) VALUES
(1, 1, 'admin', 'garvitrajput223@gmail.com', NULL, '', '$2y$10$rKI/TwNJK2/ewk0r4aaBSOc1gvk4opdmF8.qCHxa5Ua8w15.srUKO', 'System Administrator', 1, NULL, NULL, NULL, 0, '2025-12-20 12:41:37', '2025-11-22 15:41:13', '2025-12-20 12:41:37', 0),
(21, 7, 'rajputgarvit223_1766163765', 'rajputgarvit223@gmail.com', NULL, 'Lavit LLT', '$2y$10$XWpuO8RjbEuYjK8YmGg6Ze4.hf9CKOLgd1VlMzMF740omNQitlHeC', 'Lakshmi Sabharwal', 1, NULL, NULL, NULL, 1, '2025-12-20 06:31:14', '2025-12-19 17:02:45', '2025-12-20 11:04:28', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_module_access`
--

CREATE TABLE `user_module_access` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `module` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_module_access`
--

INSERT INTO `user_module_access` (`id`, `user_id`, `module`, `created_at`) VALUES
(5, 1, 'inventory', '2025-12-14 12:49:22'),
(6, 1, 'sales', '2025-12-14 12:49:22'),
(7, 1, 'purchases', '2025-12-14 12:49:22'),
(8, 1, 'accounting', '2025-12-14 12:49:22'),
(9, 1, 'reports', '2025-12-14 12:49:22'),
(10, 1, 'hrm', '2025-12-14 12:49:22'),
(11, 1, 'crm', '2025-12-14 12:49:22');

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`user_id`, `role_id`, `assigned_at`) VALUES
(1, 1, '2025-12-14 12:15:55'),
(21, 2, '2025-12-19 17:02:45');

-- --------------------------------------------------------

--
-- Table structure for table `vw_purchase_summary`
--

CREATE TABLE `vw_purchase_summary` (
  `id` int(11) DEFAULT NULL,
  `bill_number` varchar(50) DEFAULT NULL,
  `bill_date` date DEFAULT NULL,
  `supplier` varchar(200) DEFAULT NULL,
  `total_amount` decimal(12,2) DEFAULT NULL,
  `paid_amount` decimal(12,2) DEFAULT NULL,
  `balance_amount` decimal(12,2) DEFAULT NULL,
  `status` enum('Draft','Submitted','Paid','Partially Paid','Overdue') DEFAULT NULL,
  `days_overdue` int(7) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vw_sales_summary`
--

CREATE TABLE `vw_sales_summary` (
  `id` int(11) DEFAULT NULL,
  `invoice_number` varchar(50) DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `customer` varchar(200) DEFAULT NULL,
  `total_amount` decimal(12,2) DEFAULT NULL,
  `paid_amount` decimal(12,2) DEFAULT NULL,
  `balance_amount` decimal(12,2) DEFAULT NULL,
  `status` enum('Draft','Sent','Paid','Partially Paid','Overdue','Cancelled') DEFAULT NULL,
  `days_overdue` int(7) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vw_stock_summary`
--

CREATE TABLE `vw_stock_summary` (
  `id` int(11) DEFAULT NULL,
  `product_code` varchar(50) DEFAULT NULL,
  `product_name` varchar(200) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `warehouse` varchar(100) DEFAULT NULL,
  `quantity` decimal(10,2) DEFAULT NULL,
  `reserved_quantity` decimal(10,2) DEFAULT NULL,
  `available_quantity` decimal(10,2) DEFAULT NULL,
  `reorder_level` decimal(10,2) DEFAULT NULL,
  `stock_status` varchar(9) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `v_customer_sales`
--

CREATE TABLE `v_customer_sales` (
  `id` int(11) DEFAULT NULL,
  `customer_code` varchar(20) DEFAULT NULL,
  `company_name` varchar(200) DEFAULT NULL,
  `customer_segment` enum('VIP','Premium','Regular','New') DEFAULT NULL,
  `total_orders` bigint(21) DEFAULT NULL,
  `total_revenue` decimal(34,2) DEFAULT NULL,
  `total_paid` decimal(34,2) DEFAULT NULL,
  `outstanding_balance` decimal(34,2) DEFAULT NULL,
  `last_purchase_date` date DEFAULT NULL,
  `avg_order_value` decimal(16,6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `v_product_sales`
--

CREATE TABLE `v_product_sales` (
  `id` int(11) DEFAULT NULL,
  `product_code` varchar(50) DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `total_quantity_sold` decimal(32,2) DEFAULT NULL,
  `total_sales` decimal(42,4) DEFAULT NULL,
  `times_sold` bigint(21) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `v_sales_summary`
--

CREATE TABLE `v_sales_summary` (
  `period` varchar(7) DEFAULT NULL,
  `total_invoices` bigint(21) DEFAULT NULL,
  `total_sales` decimal(34,2) DEFAULT NULL,
  `total_tax` decimal(34,2) DEFAULT NULL,
  `total_revenue` decimal(34,2) DEFAULT NULL,
  `total_collected` decimal(34,2) DEFAULT NULL,
  `total_outstanding` decimal(34,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `warehouses`
--

CREATE TABLE `warehouses` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(20) NOT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `warehouses`
--

INSERT INTO `warehouses` (`id`, `company_id`, `name`, `code`, `address`, `city`, `state`, `country`, `postal_code`, `manager_id`, `is_active`, `created_at`) VALUES
(1, 1, 'IN_HOUSE', 'IN_HOUSE', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2025-12-18 13:35:32'),
(4, 7, 'In House', 'IN_HOUSE_7', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2025-12-20 12:37:32');

-- --------------------------------------------------------

--
-- Table structure for table `work_orders`
--

CREATE TABLE `work_orders` (
  `id` int(11) NOT NULL,
  `wo_number` varchar(50) NOT NULL,
  `product_id` int(11) NOT NULL,
  `bom_id` int(11) DEFAULT NULL,
  `quantity_to_produce` decimal(10,2) NOT NULL,
  `quantity_produced` decimal(10,2) DEFAULT 0.00,
  `warehouse_id` int(11) NOT NULL,
  `planned_start_date` date DEFAULT NULL,
  `planned_end_date` date DEFAULT NULL,
  `actual_start_date` date DEFAULT NULL,
  `actual_end_date` date DEFAULT NULL,
  `status` enum('Draft','Released','In Progress','Completed','Cancelled') DEFAULT 'Draft',
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account_types`
--
ALTER TABLE `account_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_related` (`related_to_type`,`related_to_id`),
  ADD KEY `idx_scheduled` (`scheduled_at`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_attendance` (`employee_id`,`attendance_date`),
  ADD KEY `idx_date` (`attendance_date`),
  ADD KEY `idx_employee_date` (`employee_id`,`attendance_date`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_table` (`table_name`,`record_id`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `bank_accounts`
--
ALTER TABLE `bank_accounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chart_account_id` (`chart_account_id`),
  ADD KEY `idx_bank_accounts_company` (`company_id`);

--
-- Indexes for table `bill_of_materials`
--
ALTER TABLE `bill_of_materials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bom_number` (`bom_number`),
  ADD KEY `idx_product` (`product_id`);

--
-- Indexes for table `bom_items`
--
ALTER TABLE `bom_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bom_id` (`bom_id`),
  ADD KEY `component_id` (`component_id`);

--
-- Indexes for table `chart_of_accounts`
--
ALTER TABLE `chart_of_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `account_code` (`account_code`),
  ADD KEY `parent_account_id` (`parent_account_id`),
  ADD KEY `idx_code` (`account_code`),
  ADD KEY `idx_type` (`account_type_id`),
  ADD KEY `idx_chart_of_accounts_company` (`company_id`);

--
-- Indexes for table `company_settings`
--
ALTER TABLE `company_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_replies`
--
ALTER TABLE `contact_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_id` (`request_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `contact_requests`
--
ALTER TABLE `contact_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cost_centers`
--
ALTER TABLE `cost_centers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `country_name` (`country_name`),
  ADD UNIQUE KEY `country_code` (`country_code`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customer_code` (`customer_code`),
  ADD KEY `idx_code` (`customer_code`),
  ADD KEY `idx_company` (`company_name`),
  ADD KEY `idx_customers_company` (`company_id`);

--
-- Indexes for table `customer_addresses`
--
ALTER TABLE `customer_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customer` (`customer_id`);

--
-- Indexes for table `customer_notes`
--
ALTER TABLE `customer_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_customer` (`customer_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_parent` (`parent_id`),
  ADD KEY `manager_id` (`manager_id`),
  ADD KEY `idx_departments_company` (`company_id`);

--
-- Indexes for table `designations`
--
ALTER TABLE `designations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `idx_designations_company` (`company_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_code` (`employee_code`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `designation_id` (`designation_id`),
  ADD KEY `reporting_to` (`reporting_to`),
  ADD KEY `idx_dept` (`department_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_employees_company` (`company_id`);

--
-- Indexes for table `employee_salary_structure`
--
ALTER TABLE `employee_salary_structure`
  ADD PRIMARY KEY (`id`),
  ADD KEY `component_id` (`component_id`),
  ADD KEY `idx_employee` (`employee_id`),
  ADD KEY `idx_effective` (`effective_from`,`effective_to`);

--
-- Indexes for table `fiscal_years`
--
ALTER TABLE `fiscal_years`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `goods_received_notes`
--
ALTER TABLE `goods_received_notes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `grn_number` (`grn_number`),
  ADD KEY `po_id` (`po_id`),
  ADD KEY `warehouse_id` (`warehouse_id`),
  ADD KEY `received_by` (`received_by`);

--
-- Indexes for table `grn_items`
--
ALTER TABLE `grn_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `grn_id` (`grn_id`),
  ADD KEY `po_item_id` (`po_item_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `indian_states`
--
ALTER TABLE `indian_states`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `state_name` (`state_name`),
  ADD UNIQUE KEY `state_code` (`state_code`),
  ADD UNIQUE KEY `gst_code` (`gst_code`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `sales_order_id` (`sales_order_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_date` (`invoice_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_invoices_company` (`company_id`);

--
-- Indexes for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_invoice` (`invoice_id`);

--
-- Indexes for table `journal_entries`
--
ALTER TABLE `journal_entries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `entry_number` (`entry_number`),
  ADD KEY `fiscal_year_id` (`fiscal_year_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_date` (`entry_date`),
  ADD KEY `idx_reference` (`reference_type`,`reference_id`),
  ADD KEY `idx_journal_entries_company` (`company_id`);

--
-- Indexes for table `journal_entry_lines`
--
ALTER TABLE `journal_entry_lines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_journal` (`journal_entry_id`),
  ADD KEY `idx_account` (`account_id`);

--
-- Indexes for table `leads`
--
ALTER TABLE `leads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lead_number` (`lead_number`),
  ADD KEY `source_id` (`source_id`),
  ADD KEY `idx_status` (`status_id`),
  ADD KEY `idx_assigned` (`assigned_to`),
  ADD KEY `idx_leads_company` (`company_id`);

--
-- Indexes for table `lead_sources`
--
ALTER TABLE `lead_sources`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lead_statuses`
--
ALTER TABLE `lead_statuses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leave_applications`
--
ALTER TABLE `leave_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `leave_type_id` (`leave_type_id`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `idx_employee` (`employee_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_dates` (`start_date`,`end_date`),
  ADD KEY `idx_leave_applications_company` (`company_id`);

--
-- Indexes for table `leave_types`
--
ALTER TABLE `leave_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `opportunities`
--
ALTER TABLE `opportunities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `opportunity_number` (`opportunity_number`),
  ADD KEY `lead_id` (`lead_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `assigned_to` (`assigned_to`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_invoice` (`invoice_id`),
  ADD KEY `idx_payment_date` (`payment_date`),
  ADD KEY `idx_payments_company` (`company_id`);

--
-- Indexes for table `payments_made`
--
ALTER TABLE `payments_made`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payment_number` (`payment_number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_supplier` (`supplier_id`),
  ADD KEY `idx_date` (`payment_date`),
  ADD KEY `idx_payments_made_company` (`company_id`);

--
-- Indexes for table `payments_received`
--
ALTER TABLE `payments_received`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payment_number` (`payment_number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_date` (`payment_date`),
  ADD KEY `idx_payments_received_company` (`company_id`);

--
-- Indexes for table `payment_allocations`
--
ALTER TABLE `payment_allocations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_id` (`payment_id`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- Indexes for table `payment_made_allocations`
--
ALTER TABLE `payment_made_allocations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_id` (`payment_id`),
  ADD KEY `bill_id` (`bill_id`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `method_name` (`method_name`),
  ADD KEY `idx_payment_methods_company` (`company_id`);

--
-- Indexes for table `payment_reminders`
--
ALTER TABLE `payment_reminders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_invoice` (`invoice_id`),
  ADD KEY `idx_reminder_date` (`reminder_date`);

--
-- Indexes for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_subscription_id` (`subscription_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_razorpay_payment` (`razorpay_payment_id`),
  ADD KEY `idx_razorpay_order` (`razorpay_order_id`);

--
-- Indexes for table `payroll`
--
ALTER TABLE `payroll`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_payroll` (`employee_id`,`month`,`year`),
  ADD KEY `idx_period` (`year`,`month`),
  ADD KEY `idx_status` (`payment_status`);

--
-- Indexes for table `payroll_components`
--
ALTER TABLE `payroll_components`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payroll_details`
--
ALTER TABLE `payroll_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payroll_id` (`payroll_id`),
  ADD KEY `component_id` (`component_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_permission` (`module`,`action`);

--
-- Indexes for table `production_entries`
--
ALTER TABLE `production_entries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `entry_number` (`entry_number`),
  ADD KEY `work_order_id` (`work_order_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_code` (`product_code`),
  ADD KEY `uom_id` (`uom_id`),
  ADD KEY `idx_code` (`product_code`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_barcode` (`barcode`),
  ADD KEY `idx_products_company` (`company_id`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_parent` (`parent_id`),
  ADD KEY `idx_product_categories_company` (`company_id`);

--
-- Indexes for table `purchase_invoices`
--
ALTER TABLE `purchase_invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bill_number` (`bill_number`),
  ADD KEY `po_id` (`po_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_supplier` (`supplier_id`),
  ADD KEY `idx_date` (`bill_date`),
  ADD KEY `idx_purchase_invoices_company` (`company_id`);

--
-- Indexes for table `purchase_invoice_items`
--
ALTER TABLE `purchase_invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bill_id` (`bill_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `po_number` (`po_number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_supplier` (`supplier_id`),
  ADD KEY `idx_date` (`order_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_purchase_orders_company` (`company_id`);

--
-- Indexes for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `po_id` (`po_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `quotations`
--
ALTER TABLE `quotations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `quotation_number` (`quotation_number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_date` (`quotation_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_quotations_company` (`company_id`);

--
-- Indexes for table `quotation_items`
--
ALTER TABLE `quotation_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_quotation` (`quotation_id`),
  ADD KEY `idx_quotation_items_company` (`company_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `sales_orders`
--
ALTER TABLE `sales_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `quotation_id` (`quotation_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_date` (`order_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_sales_orders_company` (`company_id`);

--
-- Indexes for table `sales_order_items`
--
ALTER TABLE `sales_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_order` (`order_id`);

--
-- Indexes for table `sales_targets`
--
ALTER TABLE `sales_targets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_period` (`user_id`,`period_start`);

--
-- Indexes for table `stock_balance`
--
ALTER TABLE `stock_balance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_stock` (`product_id`,`warehouse_id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_warehouse` (`warehouse_id`),
  ADD KEY `idx_stock_balance_company` (`company_id`);

--
-- Indexes for table `stock_transactions`
--
ALTER TABLE `stock_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_warehouse` (`warehouse_id`),
  ADD KEY `idx_date` (`transaction_date`),
  ADD KEY `idx_reference` (`reference_type`,`reference_id`),
  ADD KEY `idx_stock_transactions_company` (`company_id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_company_id` (`company_id`);

--
-- Indexes for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plan_name` (`plan_name`),
  ADD UNIQUE KEY `plan_code` (`plan_code`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `supplier_code` (`supplier_code`),
  ADD KEY `idx_code` (`supplier_code`),
  ADD KEY `idx_suppliers_company` (`company_id`);

--
-- Indexes for table `supplier_addresses`
--
ALTER TABLE `supplier_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `support_categories`
--
ALTER TABLE `support_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ticket_number` (`ticket_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `assigned_to` (`assigned_to`);

--
-- Indexes for table `support_ticket_replies`
--
ALTER TABLE `support_ticket_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `system_broadcasts`
--
ALTER TABLE `system_broadcasts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `tax_rates`
--
ALTER TABLE `tax_rates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tax_rates_company` (`company_id`);

--
-- Indexes for table `units_of_measure`
--
ALTER TABLE `units_of_measure`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_units_of_measure_company` (`company_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email_verification` (`email_verification_token`),
  ADD KEY `idx_users_company` (`company_id`);

--
-- Indexes for table `user_module_access`
--
ALTER TABLE `user_module_access`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_module` (`user_id`,`module`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `warehouses`
--
ALTER TABLE `warehouses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `manager_id` (`manager_id`),
  ADD KEY `idx_warehouses_company` (`company_id`);

--
-- Indexes for table `work_orders`
--
ALTER TABLE `work_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `wo_number` (`wo_number`),
  ADD KEY `bom_id` (`bom_id`),
  ADD KEY `warehouse_id` (`warehouse_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_product` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account_types`
--
ALTER TABLE `account_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `bank_accounts`
--
ALTER TABLE `bank_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bill_of_materials`
--
ALTER TABLE `bill_of_materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bom_items`
--
ALTER TABLE `bom_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chart_of_accounts`
--
ALTER TABLE `chart_of_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `company_settings`
--
ALTER TABLE `company_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `contact_replies`
--
ALTER TABLE `contact_replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `contact_requests`
--
ALTER TABLE `contact_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `cost_centers`
--
ALTER TABLE `cost_centers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `countries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `customer_addresses`
--
ALTER TABLE `customer_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `customer_notes`
--
ALTER TABLE `customer_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `designations`
--
ALTER TABLE `designations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_salary_structure`
--
ALTER TABLE `employee_salary_structure`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fiscal_years`
--
ALTER TABLE `fiscal_years`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `goods_received_notes`
--
ALTER TABLE `goods_received_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grn_items`
--
ALTER TABLE `grn_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `indian_states`
--
ALTER TABLE `indian_states`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `journal_entries`
--
ALTER TABLE `journal_entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `journal_entry_lines`
--
ALTER TABLE `journal_entry_lines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leads`
--
ALTER TABLE `leads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lead_sources`
--
ALTER TABLE `lead_sources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lead_statuses`
--
ALTER TABLE `lead_statuses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leave_applications`
--
ALTER TABLE `leave_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leave_types`
--
ALTER TABLE `leave_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `opportunities`
--
ALTER TABLE `opportunities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payments_made`
--
ALTER TABLE `payments_made`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments_received`
--
ALTER TABLE `payments_received`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payment_allocations`
--
ALTER TABLE `payment_allocations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `payment_made_allocations`
--
ALTER TABLE `payment_made_allocations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_reminders`
--
ALTER TABLE `payment_reminders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll`
--
ALTER TABLE `payroll`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll_components`
--
ALTER TABLE `payroll_components`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll_details`
--
ALTER TABLE `payroll_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `production_entries`
--
ALTER TABLE `production_entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `purchase_invoices`
--
ALTER TABLE `purchase_invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_invoice_items`
--
ALTER TABLE `purchase_invoice_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quotations`
--
ALTER TABLE `quotations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quotation_items`
--
ALTER TABLE `quotation_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `sales_orders`
--
ALTER TABLE `sales_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sales_order_items`
--
ALTER TABLE `sales_order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sales_targets`
--
ALTER TABLE `sales_targets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_balance`
--
ALTER TABLE `stock_balance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `stock_transactions`
--
ALTER TABLE `stock_transactions`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplier_addresses`
--
ALTER TABLE `supplier_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `support_categories`
--
ALTER TABLE `support_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `support_ticket_replies`
--
ALTER TABLE `support_ticket_replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `system_broadcasts`
--
ALTER TABLE `system_broadcasts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tax_rates`
--
ALTER TABLE `tax_rates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `units_of_measure`
--
ALTER TABLE `units_of_measure`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `user_module_access`
--
ALTER TABLE `user_module_access`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `warehouses`
--
ALTER TABLE `warehouses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `work_orders`
--
ALTER TABLE `work_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `activities_ibfk_1` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `activities_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `bank_accounts`
--
ALTER TABLE `bank_accounts`
  ADD CONSTRAINT `bank_accounts_ibfk_1` FOREIGN KEY (`chart_account_id`) REFERENCES `chart_of_accounts` (`id`);

--
-- Constraints for table `bill_of_materials`
--
ALTER TABLE `bill_of_materials`
  ADD CONSTRAINT `bill_of_materials_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `bom_items`
--
ALTER TABLE `bom_items`
  ADD CONSTRAINT `bom_items_ibfk_1` FOREIGN KEY (`bom_id`) REFERENCES `bill_of_materials` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bom_items_ibfk_2` FOREIGN KEY (`component_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `chart_of_accounts`
--
ALTER TABLE `chart_of_accounts`
  ADD CONSTRAINT `chart_of_accounts_ibfk_1` FOREIGN KEY (`account_type_id`) REFERENCES `account_types` (`id`),
  ADD CONSTRAINT `chart_of_accounts_ibfk_2` FOREIGN KEY (`parent_account_id`) REFERENCES `chart_of_accounts` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `contact_replies`
--
ALTER TABLE `contact_replies`
  ADD CONSTRAINT `contact_replies_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `contact_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `contact_replies_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_addresses`
--
ALTER TABLE `customer_addresses`
  ADD CONSTRAINT `customer_addresses_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_notes`
--
ALTER TABLE `customer_notes`
  ADD CONSTRAINT `customer_notes_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customer_notes_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `departments_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `departments_ibfk_2` FOREIGN KEY (`manager_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `designations`
--
ALTER TABLE `designations`
  ADD CONSTRAINT `designations_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_ibfk_3` FOREIGN KEY (`designation_id`) REFERENCES `designations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_ibfk_4` FOREIGN KEY (`reporting_to`) REFERENCES `employees` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `employee_salary_structure`
--
ALTER TABLE `employee_salary_structure`
  ADD CONSTRAINT `employee_salary_structure_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_salary_structure_ibfk_2` FOREIGN KEY (`component_id`) REFERENCES `payroll_components` (`id`);

--
-- Constraints for table `goods_received_notes`
--
ALTER TABLE `goods_received_notes`
  ADD CONSTRAINT `goods_received_notes_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`),
  ADD CONSTRAINT `goods_received_notes_ibfk_2` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`),
  ADD CONSTRAINT `goods_received_notes_ibfk_3` FOREIGN KEY (`received_by`) REFERENCES `employees` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `grn_items`
--
ALTER TABLE `grn_items`
  ADD CONSTRAINT `grn_items_ibfk_1` FOREIGN KEY (`grn_id`) REFERENCES `goods_received_notes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grn_items_ibfk_2` FOREIGN KEY (`po_item_id`) REFERENCES `purchase_order_items` (`id`),
  ADD CONSTRAINT `grn_items_ibfk_3` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`sales_order_id`) REFERENCES `sales_orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `invoices_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `invoice_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `journal_entries`
--
ALTER TABLE `journal_entries`
  ADD CONSTRAINT `journal_entries_ibfk_1` FOREIGN KEY (`fiscal_year_id`) REFERENCES `fiscal_years` (`id`),
  ADD CONSTRAINT `journal_entries_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `journal_entry_lines`
--
ALTER TABLE `journal_entry_lines`
  ADD CONSTRAINT `journal_entry_lines_ibfk_1` FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `journal_entry_lines_ibfk_2` FOREIGN KEY (`account_id`) REFERENCES `chart_of_accounts` (`id`);

--
-- Constraints for table `leads`
--
ALTER TABLE `leads`
  ADD CONSTRAINT `leads_ibfk_1` FOREIGN KEY (`source_id`) REFERENCES `lead_sources` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leads_ibfk_2` FOREIGN KEY (`status_id`) REFERENCES `lead_statuses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leads_ibfk_3` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `leave_applications`
--
ALTER TABLE `leave_applications`
  ADD CONSTRAINT `leave_applications_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leave_applications_ibfk_2` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`),
  ADD CONSTRAINT `leave_applications_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `employees` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `opportunities`
--
ALTER TABLE `opportunities`
  ADD CONSTRAINT `opportunities_ibfk_1` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `opportunities_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `opportunities_ibfk_3` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payments_made`
--
ALTER TABLE `payments_made`
  ADD CONSTRAINT `payments_made_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `payments_made_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payments_received`
--
ALTER TABLE `payments_received`
  ADD CONSTRAINT `payments_received_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `payments_received_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payment_made_allocations`
--
ALTER TABLE `payment_made_allocations`
  ADD CONSTRAINT `payment_made_allocations_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payments_made` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_made_allocations_ibfk_2` FOREIGN KEY (`bill_id`) REFERENCES `purchase_invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_reminders`
--
ALTER TABLE `payment_reminders`
  ADD CONSTRAINT `payment_reminders_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD CONSTRAINT `payment_transactions_ibfk_1` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payroll`
--
ALTER TABLE `payroll`
  ADD CONSTRAINT `payroll_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payroll_details`
--
ALTER TABLE `payroll_details`
  ADD CONSTRAINT `payroll_details_ibfk_1` FOREIGN KEY (`payroll_id`) REFERENCES `payroll` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payroll_details_ibfk_2` FOREIGN KEY (`component_id`) REFERENCES `payroll_components` (`id`);

--
-- Constraints for table `production_entries`
--
ALTER TABLE `production_entries`
  ADD CONSTRAINT `production_entries_ibfk_1` FOREIGN KEY (`work_order_id`) REFERENCES `work_orders` (`id`),
  ADD CONSTRAINT `production_entries_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`uom_id`) REFERENCES `units_of_measure` (`id`);

--
-- Constraints for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD CONSTRAINT `product_categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `product_categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `purchase_invoices`
--
ALTER TABLE `purchase_invoices`
  ADD CONSTRAINT `purchase_invoices_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `purchase_invoices_ibfk_2` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_invoices_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `purchase_invoice_items`
--
ALTER TABLE `purchase_invoice_items`
  ADD CONSTRAINT `purchase_invoice_items_ibfk_1` FOREIGN KEY (`bill_id`) REFERENCES `purchase_invoices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_invoice_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `purchase_orders_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD CONSTRAINT `purchase_order_items_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `quotations`
--
ALTER TABLE `quotations`
  ADD CONSTRAINT `quotations_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `quotations_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales_orders`
--
ALTER TABLE `sales_orders`
  ADD CONSTRAINT `sales_orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `sales_orders_ibfk_2` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_orders_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sales_targets`
--
ALTER TABLE `sales_targets`
  ADD CONSTRAINT `sales_targets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_balance`
--
ALTER TABLE `stock_balance`
  ADD CONSTRAINT `stock_balance_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_balance_ibfk_2` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_transactions`
--
ALTER TABLE `stock_transactions`
  ADD CONSTRAINT `stock_transactions_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `stock_transactions_ibfk_2` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`),
  ADD CONSTRAINT `stock_transactions_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `supplier_addresses`
--
ALTER TABLE `supplier_addresses`
  ADD CONSTRAINT `supplier_addresses_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD CONSTRAINT `support_tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `support_tickets_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `support_categories` (`id`),
  ADD CONSTRAINT `support_tickets_ibfk_3` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `support_ticket_replies`
--
ALTER TABLE `support_ticket_replies`
  ADD CONSTRAINT `support_ticket_replies_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `support_ticket_replies_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_module_access`
--
ALTER TABLE `user_module_access`
  ADD CONSTRAINT `user_module_access_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `warehouses`
--
ALTER TABLE `warehouses`
  ADD CONSTRAINT `warehouses_ibfk_1` FOREIGN KEY (`manager_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `work_orders`
--
ALTER TABLE `work_orders`
  ADD CONSTRAINT `work_orders_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `work_orders_ibfk_2` FOREIGN KEY (`bom_id`) REFERENCES `bill_of_materials` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `work_orders_ibfk_3` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`),
  ADD CONSTRAINT `work_orders_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
