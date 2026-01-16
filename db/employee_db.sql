-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 16, 2025 at 04:19 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `employee_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `action_type` varchar(100) NOT NULL,
  `action_description` text NOT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int(10) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action_type`, `action_description`, `table_name`, `record_id`, `ip_address`, `user_agent`, `created_at`) VALUES
(187, 'DARLa', 'login', 'User \'DARLa\' logged in successfully', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0', '2025-12-16 02:34:13'),
(188, 'DARLa', 'employee_update', 'Employment status updated to: PERMANENT (changed from \'\' to \'PERMANENT\')', 'employees', 53, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0', '2025-12-16 02:43:50'),
(189, 'DARLa', 'employee_update', 'Employment status updated to: PERMANENT (changed from \'\' to \'PERMANENT\')', 'employees', 51, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0', '2025-12-16 02:44:04'),
(190, 'DARLa', 'employee_update', 'Employment status updated to: PERMANENT (changed from \'\' to \'PERMANENT\')', 'employees', 49, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0', '2025-12-16 02:44:19'),
(191, 'DARLa', 'employee_update', 'Employment status updated to: PERMANENT (changed from \'\' to \'PERMANENT\')', 'employees', 50, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0', '2025-12-16 02:44:30'),
(192, 'DARLa', 'logout', 'User \'DARLa\' logged out', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0', '2025-12-16 03:17:01');

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password_hash`, `full_name`, `email`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'DARLa', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'DAR LA UNION Administrator', 'admin@darla.gov.ph', NULL, '2025-12-03 03:03:59', '2025-12-03 08:20:09');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `sequence_no` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `type_label` varchar(100) NOT NULL,
  `position` varchar(150) NOT NULL,
  `item_number` varchar(100) DEFAULT NULL,
  `salary_grade` varchar(20) DEFAULT NULL,
  `appointment_date` date DEFAULT NULL,
  `salary` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `calendar_events`
--

CREATE TABLE `calendar_events` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` date NOT NULL,
  `event_time` time DEFAULT NULL,
  `event_type` enum('activity','holiday','season','reminder') DEFAULT 'activity',
  `reminder_sent` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `calendar_events`
--

INSERT INTO `calendar_events` (`id`, `title`, `description`, `event_date`, `event_time`, `event_type`, `reminder_sent`, `created_at`, `updated_at`) VALUES
(1, 'Happ Birthday', 'TEST', '2025-12-05', '10:00:00', 'reminder', 1, '2025-12-03 03:19:34', '2025-12-03 23:48:40'),
(2, 'New Year\'s Day', NULL, '2025-01-01', NULL, 'holiday', 0, '2025-12-03 03:29:08', '2025-12-03 03:29:08'),
(3, 'EDSA People Power Revolution Anniversary', NULL, '2025-02-25', NULL, 'holiday', 0, '2025-12-03 03:29:08', '2025-12-03 03:29:08'),
(4, 'Araw ng Kagitingan (Day of Valor)', NULL, '2025-04-09', NULL, 'holiday', 0, '2025-12-03 03:29:08', '2025-12-03 03:29:08'),
(5, 'Labor Day', NULL, '2025-05-01', NULL, 'holiday', 0, '2025-12-03 03:29:08', '2025-12-03 03:29:08'),
(6, 'Independence Day', NULL, '2025-06-12', NULL, 'holiday', 0, '2025-12-03 03:29:08', '2025-12-03 03:29:08'),
(7, 'National Heroes Day', NULL, '2025-08-25', NULL, 'holiday', 0, '2025-12-03 03:29:08', '2025-12-03 03:29:08'),
(8, 'Bonifacio Day', NULL, '2025-11-30', NULL, 'holiday', 0, '2025-12-03 03:29:08', '2025-12-03 03:29:08'),
(9, 'Rizal Day', NULL, '2025-12-30', NULL, 'holiday', 0, '2025-12-03 03:29:08', '2025-12-03 03:29:08'),
(10, 'Christmas Day', NULL, '2025-12-25', NULL, 'holiday', 0, '2025-12-03 03:29:08', '2025-12-03 03:29:08'),
(11, 'All Saints\' Day', NULL, '2025-11-01', NULL, 'holiday', 0, '2025-12-03 03:29:08', '2025-12-03 03:29:08'),
(12, 'All Souls\' Day', NULL, '2025-11-02', NULL, 'holiday', 0, '2025-12-03 03:29:08', '2025-12-03 03:29:08'),
(13, 'Christmas Eve', NULL, '2025-12-24', NULL, 'holiday', 0, '2025-12-03 03:29:08', '2025-12-03 03:29:08'),
(14, 'New Year\'s Eve', NULL, '2025-12-31', NULL, 'holiday', 0, '2025-12-03 03:29:08', '2025-12-03 03:29:08'),
(15, 'Maundy Thursday', NULL, '2025-04-17', NULL, 'holiday', 0, '2025-12-03 03:29:08', '2025-12-03 03:29:08'),
(16, 'Good Friday', NULL, '2025-04-18', NULL, 'holiday', 0, '2025-12-03 03:29:08', '2025-12-03 03:29:08'),
(17, 'Black Saturday', NULL, '2025-04-19', NULL, 'holiday', 0, '2025-12-03 03:29:08', '2025-12-03 03:29:08'),
(18, 'New Year\'s Day', NULL, '2026-01-01', NULL, 'holiday', 0, '2025-12-03 03:29:08', '2025-12-03 03:29:08'),
(19, 'EDSA People Power Revolution Anniversary', NULL, '2026-02-25', NULL, 'holiday', 0, '2025-12-03 03:29:08', '2025-12-03 03:29:08'),
(20, 'Araw ng Kagitingan (Day of Valor)', NULL, '2026-04-09', NULL, 'holiday', 0, '2025-12-03 03:29:08', '2025-12-03 03:29:08'),
(21, 'Labor Day', NULL, '2026-05-01', NULL, 'holiday', 0, '2025-12-03 03:29:08', '2025-12-03 03:29:08'),
(22, 'Independence Day', NULL, '2026-06-12', NULL, 'holiday', 0, '2025-12-03 03:29:08', '2025-12-03 03:29:08'),
(23, 'National Heroes Day', NULL, '2026-08-31', NULL, 'holiday', 0, '2025-12-03 03:29:08', '2025-12-03 03:29:08'),
(24, 'Bonifacio Day', NULL, '2026-11-30', NULL, 'holiday', 0, '2025-12-03 03:29:08', '2025-12-03 03:29:08'),
(25, 'Rizal Day', NULL, '2026-12-30', NULL, 'holiday', 0, '2025-12-03 03:29:08', '2025-12-03 03:29:08'),
(26, 'Christmas Day', NULL, '2026-12-25', NULL, 'holiday', 0, '2025-12-03 03:29:08', '2025-12-03 03:29:08'),
(27, 'All Saints\' Day', NULL, '2026-11-01', NULL, 'holiday', 0, '2025-12-03 03:29:08', '2025-12-03 03:29:08'),
(28, 'All Souls\' Day', NULL, '2026-11-02', NULL, 'holiday', 0, '2025-12-03 03:29:08', '2025-12-03 03:29:08'),
(29, 'Christmas Eve', NULL, '2026-12-24', NULL, 'holiday', 0, '2025-12-03 03:29:08', '2025-12-03 03:29:08'),
(30, 'New Year\'s Eve', NULL, '2026-12-31', NULL, 'holiday', 0, '2025-12-03 03:29:08', '2025-12-03 03:29:08'),
(31, 'Maundy Thursday', NULL, '2026-04-02', NULL, 'holiday', 0, '2025-12-03 03:29:08', '2025-12-03 03:29:08'),
(32, 'Good Friday', NULL, '2026-04-03', NULL, 'holiday', 0, '2025-12-03 03:29:08', '2025-12-03 03:29:08'),
(33, 'Black Saturday', NULL, '2026-04-04', NULL, 'holiday', 0, '2025-12-03 03:29:08', '2025-12-03 03:29:08'),
(34, 'Chinese New Year', NULL, '2025-01-29', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(35, 'Ninoy Aquino Day', NULL, '2025-08-21', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(36, 'Constitution Day', NULL, '2025-02-02', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(37, 'National Flag Day', NULL, '2025-05-28', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(38, 'Philippine-Spanish Friendship Day', NULL, '2025-06-30', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(39, 'National Teachers\' Day', NULL, '2025-10-05', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(40, 'United Nations Day', NULL, '2025-10-24', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(41, 'Araw ng Maynila (Manila Day)', NULL, '2025-06-24', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(42, 'Araw ng Quezon (Quezon Day)', NULL, '2025-08-19', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(43, 'Araw ng Davao (Davao Day)', NULL, '2025-03-16', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(44, 'Araw ng Cebu (Cebu Day)', NULL, '2025-04-07', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(45, 'Feast of the Black Nazarene', NULL, '2025-01-09', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(46, 'Feast of Our Lady of Lourdes', NULL, '2025-02-11', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(47, 'Feast of St. Joseph', NULL, '2025-03-19', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(48, 'Feast of the Santo Niño', NULL, '2025-01-15', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(49, 'Feast of Our Lady of Peñafrancia', NULL, '2025-09-08', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(50, 'Feast of the Immaculate Conception', NULL, '2025-12-08', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(51, 'Sinulog Festival (Cebu)', NULL, '2025-01-15', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(52, 'Ati-Atihan Festival (Aklan)', NULL, '2025-01-15', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(53, 'Dinagyang Festival (Iloilo)', NULL, '2025-01-25', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(54, 'Panagbenga Festival (Baguio)', NULL, '2025-02-01', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(55, 'Moriones Festival (Marinduque)', NULL, '2025-04-18', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(56, 'Kadayawan Festival (Davao)', NULL, '2025-08-15', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(57, 'MassKara Festival (Bacolod)', NULL, '2025-10-19', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(58, 'Higantes Festival (Angono)', NULL, '2025-11-23', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(59, 'Chinese New Year', NULL, '2026-01-29', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(60, 'Ninoy Aquino Day', NULL, '2026-08-21', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(61, 'Constitution Day', NULL, '2026-02-02', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(62, 'National Flag Day', NULL, '2026-05-28', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(63, 'Philippine-Spanish Friendship Day', NULL, '2026-06-30', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(64, 'National Teachers\' Day', NULL, '2026-10-05', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(65, 'United Nations Day', NULL, '2026-10-24', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(66, 'Araw ng Maynila (Manila Day)', NULL, '2026-06-24', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(67, 'Araw ng Quezon (Quezon Day)', NULL, '2026-08-19', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(68, 'Araw ng Davao (Davao Day)', NULL, '2026-03-16', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(69, 'Araw ng Cebu (Cebu Day)', NULL, '2026-04-07', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(70, 'Feast of the Black Nazarene', NULL, '2026-01-09', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(71, 'Feast of Our Lady of Lourdes', NULL, '2026-02-11', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(72, 'Feast of St. Joseph', NULL, '2026-03-19', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(73, 'Feast of the Santo Niño', NULL, '2026-01-15', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(74, 'Feast of Our Lady of Peñafrancia', NULL, '2026-09-08', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(75, 'Feast of the Immaculate Conception', NULL, '2026-12-08', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(76, 'Sinulog Festival (Cebu)', NULL, '2026-01-15', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(77, 'Ati-Atihan Festival (Aklan)', NULL, '2026-01-15', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(78, 'Dinagyang Festival (Iloilo)', NULL, '2026-01-25', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(79, 'Panagbenga Festival (Baguio)', NULL, '2026-02-01', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(80, 'Moriones Festival (Marinduque)', NULL, '2026-04-03', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(81, 'Kadayawan Festival (Davao)', NULL, '2026-08-15', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(82, 'MassKara Festival (Bacolod)', NULL, '2026-10-19', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48'),
(83, 'Higantes Festival (Angono)', NULL, '2026-11-23', NULL, 'holiday', 0, '2025-12-03 07:17:48', '2025-12-03 07:17:48');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(10) UNSIGNED NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `home_address` varchar(255) DEFAULT NULL,
  `contact_no` varchar(50) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `civil_status` varchar(50) DEFAULT NULL,
  `spouse_name` varchar(150) DEFAULT NULL,
  `spouse_contact_no` varchar(50) DEFAULT NULL,
  `employee_number` varchar(50) DEFAULT NULL,
  `pagibig_number` varchar(50) DEFAULT NULL,
  `philhealth_number` varchar(50) DEFAULT NULL,
  `tin_number` varchar(50) DEFAULT NULL,
  `sss_number` varchar(50) DEFAULT NULL,
  `gsis_number` varchar(50) DEFAULT NULL,
  `trainings` text DEFAULT NULL,
  `leave_info` text DEFAULT NULL,
  `service_record` text DEFAULT NULL,
  `employment_status` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `last_name`, `first_name`, `middle_name`, `birthdate`, `home_address`, `contact_no`, `email`, `civil_status`, `spouse_name`, `spouse_contact_no`, `employee_number`, `pagibig_number`, `philhealth_number`, `tin_number`, `sss_number`, `gsis_number`, `trainings`, `leave_info`, `service_record`, `employment_status`, `created_at`, `updated_at`) VALUES
(2, 'TATUNAY', 'NEMIA THEODORA', 'MADARANG', '1966-04-01', '#2 Quirino Street Poblacion, Amligay La Union', '09773222445', 'nemiamadarang66@gmail.com', 'Married', 'Tatunay, Constantine Q.', '09178023113 / 09984540328 / 09478940436', '105890306', '1280-0000-1868', '100000091396', '132221615', NULL, '00600001868', NULL, NULL, NULL, NULL, '2025-12-02 15:25:16', '2025-12-15 03:16:22'),
(7, 'TABIO-HIPOL', 'ANNABELLE', 'VIDAL', '1995-10-22', 'PUROK 3, PIAS, SAN FERNANDO, LA UNION', '09567644284', 'tabioannabelle22@gmail.com', 'Married', 'HIPOL, ARNEL OLIVER, MARQUEZ', NULL, NULL, '916159530894', '052507813879', '329379117', '0125475808', NULL, NULL, NULL, NULL, 'PERMANENT', '2025-12-05 03:13:54', '2025-12-15 05:53:51'),
(8, 'SUGUITAN', 'CHRISTIANNE', 'C', NULL, 'BLOCK 5, LOT 3, HAVILAH RESIDENCES, PAGDARAOAN, SAN FERNANDO, LA UNION', NULL, NULL, 'Married', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-12 00:10:05', '2025-12-12 00:10:05'),
(9, 'RULLEPA', 'AGNES', 'TUMBAGA', '1969-06-28', 'SAN MARTIN, BACNOTAN, LA UNION', '09152756950', 'aptrullepa@gmail.com', 'Married', 'BENJIE N. RULLEPA', NULL, '0105040002', '01017893402', '050000117843', '915344454', '0108007170', '690622801026', NULL, NULL, NULL, 'Permanent', '2025-12-12 00:15:20', '2025-12-12 00:16:56'),
(10, 'SIOBAL', 'JAN LESTER', 'JUBILADO', '1991-08-01', '# 40 DALUMPINAS RD., LINSAT, SAN FERNANDO, LA UNION', '09122367177', 'janlestersiobal@gmail.com', 'Married', 'GILLIAN JOY C. SIOBAL', NULL, NULL, '121309771529', '05052025268', '77218585000', '0127737816', '20003391443', NULL, NULL, NULL, NULL, '2025-12-12 00:23:10', '2025-12-12 00:24:47'),
(11, 'SALTING', 'MA. MAGDALENA', 'DICTAAN', '1981-04-25', 'SITIO 5 BIDAY, SAN FERNANDO, LA UNION', '09279319968', 'mugz_81@yahoo.com', 'Single', NULL, NULL, '01015040004', '128000016199', '050000570335', '926239628', NULL, '006000184742', NULL, NULL, NULL, NULL, '2025-12-12 00:30:02', '2025-12-12 00:32:00'),
(12, 'REYES', 'ROSMIN', 'FORONDA', '1964-12-27', 'RUFINA SUBDIVISION., PARIAN, SAN FERNANDO, LA UNION', '09175662237', 'rosminreyes@yahoo.com', 'Married', 'ROMUALDO B. REYES', NULL, '0105940002', '12800001780', '10000001221', '120230910', '0208998930', '006011316143', NULL, NULL, NULL, NULL, '2025-12-12 00:38:37', '2025-12-12 00:40:23'),
(13, 'LAZAGA', 'CYRIL', 'JULIETO', '1995-11-08', 'LINGSAT, SAN FERNANDO, LA UNION', '09925905204/ 09215063230', 'cyrillazaga@gmail.com', 'Single', NULL, NULL, '105220003', '121216776261', '052524289547', '498121775', '0127604907', '2006135717', NULL, NULL, NULL, NULL, '2025-12-12 00:45:28', '2025-12-12 00:46:22'),
(14, 'RUFIN', 'JERRY', 'MASANCAY', '1997-07-30', 'ILOCANOS NORTE, SAN FERNANDO, LA UNION', '09553782124', 'jerryrufin30@gmail.com', 'Single', NULL, NULL, NULL, '121334583556', '052507810713', '769849574', '0131933473', NULL, NULL, NULL, NULL, NULL, '2025-12-12 00:50:11', '2025-12-12 00:51:04'),
(15, 'UYCHOCO', 'KONRAD LORENZ', 'MADRIAGA', '1999-07-07', 'SAN ANTONIO, AGOO, LA UNION', '09053373375', 'konradlorenzuychoco@gmail.com', 'Single', NULL, NULL, NULL, '121312333110', '052505294491', '616853176', '0130649962', NULL, NULL, NULL, NULL, NULL, '2025-12-12 00:55:11', '2025-12-12 00:55:52'),
(16, 'URSUA', 'JELLY MAE', 'FLORES', '2000-05-04', 'NAGYUBYUBAN, SAN FERNANDO, LA UNION', '09203637146', 'jellymaeursua887@gmail.ccom', 'Single', NULL, NULL, NULL, '121334596355', '052507757111', '726185492000', '0130701978', NULL, NULL, NULL, NULL, NULL, '2025-12-12 01:01:23', '2025-12-12 01:02:11'),
(17, 'VALDEZ', 'VALDEMIR', 'PAJIMOLA', '1976-07-14', 'LINSAT, SAN FERNANDO, LA UNION', '09610530870', 'valdemirpvaldez@gmail.com', 'Married', 'LIZEL P. PASARDAN', NULL, '105030003', '1280001391', '050250305866', '929346469', NULL, '76071400596', NULL, NULL, NULL, NULL, '2025-12-12 01:05:50', '2025-12-12 01:06:52'),
(18, 'VERCELES', 'GEMMA', 'MULI', '1967-11-27', 'SAN JOSE, AGOO, LA UNION', '09164154132', 'mamuverz5767@gmail.com', 'Married', 'ANASTACIO B. VERCELES', NULL, '0105040005', '128000016443', '050000581531', '188989697', '0107322982', '67112702188', NULL, NULL, NULL, 'PERMANENT', '2025-12-12 01:10:51', '2025-12-15 06:06:04'),
(19, 'VERCELES', 'JASPER', 'CADAOAS', '1976-07-06', 'PAGDARAOAN, SAN FERNANDO LA UNION', '09053194301', 'chloevince@yahoo.uk', 'Married', 'SHARON D. VERCELES', NULL, NULL, NULL, '50000690721', '911739317', NULL, '2001268762', NULL, NULL, NULL, NULL, '2025-12-12 01:15:02', '2025-12-12 01:15:43'),
(20, 'BRIONES', 'DORIS', 'RABANG', '1965-01-11', 'GUISET SUR, SAN MANUEL, PANGASINAN', '09190084507', 'doris.briones@gyahoo.com', 'Other', NULL, NULL, NULL, '12800025813', '050000072971', '164275749', NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-12 01:22:32', '2025-12-12 01:23:16'),
(21, 'MORTA', 'ELIZABETH', 'ESTACIO', '1963-02-16', 'LINGSAT, SAN FERNANDO, LA UNION', '09479226724', 'bethmorta1963@gmail.com', 'Married', 'FLORANTE B. MORTA', NULL, '105890028', '0100100088602', '10000001094', '132221166', NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-12 01:30:49', '2025-12-12 01:31:20'),
(22, 'RAMOLETE', 'EDITHA', 'MOSKITO', '1964-05-06', 'MONTEMAR VILLAGE ILI NORTE, SAN JUAN, LA UNION', '09198015547', 'emaramolete-64@yahoo.com', 'Widowed', 'CARLO E. RAMOLETE', NULL, '105050001', '128000017598', '050000016974', '131119804', '0119115569', '64050601763', NULL, NULL, NULL, NULL, '2025-12-12 01:36:28', '2025-12-12 01:37:23'),
(23, 'PACIS', 'RENE LAZARO', 'PECSON', '1964-12-17', 'CONSOLACION, AGOO, LA UNION', '09183321997', 'enersicap@gmail.com', 'Other', NULL, NULL, '105050002', '128000017587', '50000016567', '131119746', NULL, '0060005805327', NULL, NULL, NULL, NULL, '2025-12-12 01:42:25', '2025-12-12 01:43:07'),
(24, 'MENDOZA', 'ERIC WINLOVE', 'MANGAOANG', '1990-10-08', 'DON LORENZO SUBD. STA RITA WEST, ARINGAY, LA UNION', '09153700431', 'ERICWINLOVE@GMAIL.COM', 'Single', NULL, NULL, NULL, NULL, '050503045203', '342381390000', '3471528309', NULL, NULL, NULL, NULL, 'Permanent', '2025-12-12 02:06:22', '2025-12-15 03:19:54'),
(25, 'LUBIANO', 'ERICKSON', 'VALMONTE', '1992-04-26', 'ZONE 5 LINGSAT, SAN FERNANDO, LA UNION', '09281736441', 'ERICLUBIANO26DAR@YAHOO.COM', 'Single', NULL, NULL, NULL, '91002817644', '05213714878', '328128746', '012410509', NULL, NULL, NULL, NULL, NULL, '2025-12-12 02:10:47', '2025-12-12 02:11:27'),
(26, 'MERCADO', 'ARLENE', 'BAMBICO', '1966-01-31', 'CABARITAN SUR, NAGUILIAN, LA UNION', '09326014655', 'ADB.MERCADO31@YAHOO.COM', 'Married', 'GERMAN C. MERCADO', NULL, '0105890003', '128000012114', '050000060876', '132220500', '0107441614', '66013101451', NULL, NULL, NULL, NULL, '2025-12-12 02:17:12', '2025-12-12 02:19:04'),
(27, 'LARA', 'JULIE ANN', 'NAZARO', '1997-01-12', 'PANDAN, BACNOTAN, LA UNION', '09279762336', 'JULIEANNLARA03@GMAIL.COM', 'Single', NULL, NULL, '105210004', '121267593698', '050256377893', '704703943', '3502623115', '2005976310', NULL, NULL, NULL, NULL, '2025-12-12 02:24:04', '2025-12-12 02:25:06'),
(28, 'LINDLEY', 'CARLITA', 'ALCANTARA', '1968-10-28', 'VENFLOR VILLAGE, DALUMPINAS OESTE, SAN FERNANDO, LA UNION', '09497820009', 'KARLITZ10280116@GMAIL.COM', 'Married', 'JAMES W. LINDLEY', NULL, '105980001', '010102926107', '05000006072', '190000735', NULL, '68102801575', NULL, NULL, NULL, NULL, '2025-12-12 02:40:48', '2025-12-12 02:41:34'),
(29, 'LANG-AY', 'LOBAYTO', 'PADIONG', '1968-01-10', 'MARIVILLE SUBD. DALUMPINAS RD., LINGSAT, SAN FFERRNANDO, LA UION', '09436819825', 'LANGAYLOI@YAHOO.COM', 'Married', 'RELINE CLAIRE S. LANG-AY', NULL, '0100050005', '128000252450', '070000813', '196947404', NULL, '006009712553', NULL, NULL, NULL, NULL, '2025-12-12 02:45:07', '2025-12-12 02:45:54'),
(30, 'JULIAN', 'REMELY', 'ORTAL', '1967-12-26', 'BRGY 26 SAN MARCELINO, LAOG CITY, ILOCOS NORTE', '09189262838', 'RELLIEJULIANDAR@GMAIL.COM', 'Married', 'REYNALDO G. JULIAN', NULL, '0103910119', '010100345111', '050000210894', '126052276', NULL, '67122600332', NULL, NULL, NULL, NULL, '2025-12-12 02:51:09', '2025-12-12 02:51:51'),
(31, 'GALVEZ', 'PRESNELIE', 'SARMIENTO', '1963-10-31', 'ILOCANOS NORTE, SAN FERNANDO, LA UNION', '09328447298', 'ZEVLAG_NEL@GMAIL.COM.PH', 'Married', 'FERDINAN E. GALVEZ', NULL, '01058900017', '128000001491', '050000041952', '132220972', NULL, '63103101711', NULL, NULL, NULL, NULL, '2025-12-12 03:16:49', '2025-12-12 03:17:42'),
(32, 'GERANO', 'LOLITA', 'COSTALES', '1972-02-22', 'SANTA CECILIA, ARINGAY, LA UNION', '09984207735', 'LOLITA_GERANO@YAHOO.COM', 'Single', NULL, NULL, '105970004', '010102944007', '100000090969', '179398876', '3338528465', '006008109512', NULL, NULL, NULL, NULL, '2025-12-12 03:21:34', '2025-12-12 03:22:31'),
(33, 'DALAY-ON', 'JOVY ANNE', 'VALDEZ', '1997-02-27', 'DALLANGAYAN OESTE, SAN FERNANDO, LA UNION', '09473303213', 'JOVYANNED@GMAIL.COM', 'Single', NULL, NULL, '010210001', '121230685807', '050256582012', '482054974000', '3492019965', '2005874346', NULL, NULL, NULL, NULL, '2025-12-12 03:31:46', '2025-12-12 03:32:45'),
(34, 'DUQUE', 'JUVY', 'MILLORA', '1966-03-26', 'CENTRAL EAST NUMBER 1, BANGAR, LA UNION', '09381276683', 'JUVY.MILLORA@YAHOO.COM', 'Married', 'ELMER G. DUQUE', NULL, '0105890012', '128000001400', '100000090837', '132220819', NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-12 03:39:05', '2025-12-12 03:43:11'),
(35, 'DAQUEP', 'MA. CORAZON', 'CAVANEYRO', '1963-02-06', 'PANICSICAN, SAN JUAN, LA UNION', '09324312693', 'CORYDAQUEP@GMAIL.COM', 'Married', 'RODEL I. DAQUEP', NULL, '105890010', '0101293711', '050000041405', '132220743', NULL, '006011319726', NULL, NULL, NULL, NULL, '2025-12-12 03:45:45', '2025-12-12 03:46:24'),
(36, 'CUNANAN', 'IMELDA', 'IGNACIO', '1965-11-07', 'NAGUITUBAN, SAN JUAN, LA UNION', '09175640807', 'IMELDACUNANAN1012@GMAIL.COM', 'Single', NULL, NULL, '105900002', '1280000001291', '050000041359', '132220698', '3306796073', '006006803742', NULL, NULL, NULL, NULL, '2025-12-12 05:04:12', '2025-12-12 05:04:56'),
(37, 'CRUSIN', 'JESSA FAYE', 'NISPEROS', '1995-10-20', 'DALUMPINAS ESTE, SAN FERNANDO, LA UNION', '09054115582', 'CJESSAFAYE@GMAIL.COM', 'Single', NULL, NULL, NULL, '121169212439', '030260189359', '471100482', '0125476179', NULL, NULL, NULL, NULL, NULL, '2025-12-12 05:07:43', '2025-12-12 05:08:15'),
(38, 'CATBAGAN', 'EDNA', 'MOSTER', '1964-08-19', 'IPET, SUDIPEN, LA UNION', '09217668222', 'EDNAMCATBAGAN0819@GMAIL.COM', 'Widowed', 'RAYMUND A. CATBAGAN', NULL, '105890009', '128000001279', '1000000090764', '132220736', NULL, '006011320074', NULL, NULL, NULL, NULL, '2025-12-12 05:13:09', '2025-12-12 05:13:48'),
(39, 'CARPIO', 'BERNARDO', 'LIM', '1962-06-30', 'CANTORIA #3, LUNA, LA UNION', '09460717786', 'BERNARDOCARPIO16@YAHOO.COM', 'Married', 'DIGNA N. CARPIO', NULL, '105850001', '128000001291', '50000041170', '132220671', NULL, '65110701289', NULL, NULL, NULL, NULL, '2025-12-12 05:17:24', '2025-12-12 05:18:03'),
(40, 'CARAGAY', 'MICHAEL CHRISTOPHER', 'D', NULL, '21 BEACH HOMES III, BRGY. LINGSAT, SAN FERNANDO, LA UNION', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-12 05:22:58', '2025-12-12 05:22:58'),
(41, 'CARIASO', 'MARLYN', 'ESCOBIO', '1996-09-18', 'LINGSAT, SAN FERNANDO, LA UNION', '09073580524', 'MARLYNCARIASO@GMAIL.COM', 'Single', NULL, NULL, NULL, '121207374252', '05025629625', '702555062', '0126569962', '2005875056', NULL, NULL, NULL, NULL, '2025-12-12 05:26:01', '2025-12-12 05:26:59'),
(42, 'BRINGAS', 'CORAZON', 'GARCELLANO', '1973-10-05', 'SEVILLA, SAN FERNANDO, LA UNION', '09193888788', 'CORAGARCELLANO@YAHOO.COM', 'Married', 'AMANCIO O. BRINGAS', NULL, '0105970003', '913193496847', '010100934807', '166248569', '010100934807', NULL, NULL, NULL, NULL, NULL, '2025-12-12 05:32:16', '2025-12-12 05:48:02'),
(43, 'CABADING', 'MONICA ASHLEY', 'GADGADAN', '1999-02-21', 'TALOGTOG, SAN JUAN, LA UNION', '09260375592', 'CABADINGASHLEY@GMAIL.COM', 'Single', NULL, NULL, NULL, '121274203982', '050257673356', '394251130', '0127181514', NULL, NULL, NULL, NULL, 'PERMANENT', '2025-12-12 05:38:59', '2025-12-15 05:57:27'),
(44, 'CAPISTRANO', 'LORIE', 'LOPEZ', '1973-02-27', 'QUINAVITE, BAUANG, LA UNION', '09158418385', NULL, NULL, 'ROY B. CAPISTRANO', NULL, '1059600003', '12800000121', '050000061066', NULL, NULL, '00600186054', NULL, NULL, NULL, NULL, '2025-12-12 05:45:33', '2025-12-12 05:46:16'),
(45, 'BERNAS', 'JOSEPHINE', 'FLORES', '1967-12-28', 'SAGAYAD, SAN FERNANDO, LA UNION', NULL, 'josiebernas@gmail.com', 'Married', 'macario l. bernas', NULL, '0105980001', '128000001145', '050000045044', '907164147', NULL, '00600186151', NULL, NULL, NULL, NULL, '2025-12-12 05:52:36', '2025-12-12 05:53:16'),
(46, 'BARROZO', 'TITO VICENTE', 'URTULA', '1968-12-06', 'CAMP 7, BAGUIO CITY, BENGUET', '09396557764', 'VICENTEBARROZO1968@GMAIL.COM', 'Married', 'EMMA SVETLANA A. BARROZO', NULL, NULL, '0101136826209', '050500298542', '133351031', '0213183439', '006006305004', NULL, NULL, NULL, NULL, '2025-12-12 05:57:55', '2025-12-12 05:58:41'),
(47, 'BANQUIAD', 'JENIFER', 'ACOSTA', '1995-09-13', 'POBLACION, SUYO, ILOCOS SUR', '09272581690', 'JENIFERACOSTA@GMAIL.COM', 'Married', 'JONATHAN B. BANQUIAD', NULL, NULL, '121171123562', '052504159191', '481735441000', '09123410292', NULL, NULL, NULL, NULL, NULL, '2025-12-12 06:02:23', '2025-12-12 06:03:03'),
(48, 'BALDERAS', 'STEPHANIE KATE', 'ABALOS', '1998-10-18', 'AGOO, LA UNION', '09455307272', 'STEPHANIEKATEBALDERAS@GMAIL.COM', 'Single', NULL, NULL, NULL, '121280302947', '050257999146', '775574075000', '3502554941', '2006306742', NULL, NULL, NULL, NULL, '2025-12-12 06:09:34', '2025-12-12 06:10:25'),
(49, 'ANCHETA', 'LEOVIC', 'JUBILADO', '1966-06-20', 'LINGSAT, SAN FERNANDO, LA UNION', '09512816181', 'LEAANCHETA1966@YAHOO.COM', 'Married', 'ANTONIO P. ANCHETA JR.', NULL, '10505890025', '128000001078', '050000060787', '13221053', '0115262869', '66051201320', NULL, NULL, NULL, 'PERMANENT', '2025-12-12 06:14:03', '2025-12-16 02:44:19'),
(50, 'ANDRADA', 'DANIEL', 'BALCITA', '1995-04-23', 'DALLANGAYAN ESTE, SAN FERNANDO, LA UNION', '09063369895', 'DAR.ELYU.DANIEL@GMAIL.COM', 'Single', NULL, NULL, '0105210002', '121288348607', '042504026668', '496654734000', '3491338016', '2005975409', NULL, NULL, NULL, 'PERMANENT', '2025-12-12 06:17:06', '2025-12-16 02:44:30'),
(51, 'AGBUNAG', 'MARIA ELENA IVY', 'EVANGELISTA', '1977-04-15', 'TANQUI, SAN FERNANDO, LA UNION', '09307402002', 'IVIANG77@GMAIL.COM', 'Married', 'RIGIL V. AGBUNAG', NULL, '031022', NULL, '050251991372', '0941167224', '0113341148', NULL, NULL, NULL, NULL, 'PERMANENT', '2025-12-12 06:24:32', '2025-12-16 02:44:04'),
(52, 'ABACO', 'EVANGILINE', 'PLATON', '1998-02-28', 'POBLACION, SUYO, ILOCOS SUR', '09275867736', 'GELINE.ABACO@GMAIL.COM', 'Single', NULL, NULL, NULL, '121320807381', '050259390556', '777643224000', NULL, '2006306739', NULL, NULL, NULL, 'PERMANENT', '2025-12-12 06:28:27', '2025-12-15 06:06:52'),
(53, 'ABANSI', 'ADELAIDA', 'OPINALDO', '1965-12-16', 'NEW POBLACION, BURGOS, LA UNION', '09156679400', 'ABANSIADELAIDA@GMAIL.COM', 'Married', 'DOMINGO G. ABANSI', NULL, '105890001', '128000001011', '050000060663', '132221246', NULL, '9560300193016', NULL, NULL, NULL, 'PERMANENT', '2025-12-12 06:32:13', '2025-12-16 02:43:50');

-- --------------------------------------------------------

--
-- Table structure for table `employee_awards`
--

CREATE TABLE `employee_awards` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `award_level` varchar(100) DEFAULT NULL,
  `awarding_body` varchar(255) DEFAULT NULL,
  `award_date` date DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_awards`
--

INSERT INTO `employee_awards` (`id`, `employee_id`, `title`, `award_level`, `awarding_body`, `award_date`, `remarks`, `description`) VALUES
(1, 2, 'xzgbf', NULL, 'ddd', '2025-12-02', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employee_educational_background`
--

CREATE TABLE `employee_educational_background` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `level` varchar(50) NOT NULL,
  `school_name` varchar(255) NOT NULL,
  `degree_course` varchar(255) DEFAULT NULL,
  `period_from` varchar(20) DEFAULT NULL,
  `period_to` varchar(20) DEFAULT NULL,
  `highest_level_units` varchar(255) DEFAULT NULL,
  `year_graduated` varchar(20) DEFAULT NULL,
  `scholarship_honors` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_educational_background`
--

INSERT INTO `employee_educational_background` (`id`, `employee_id`, `level`, `school_name`, `degree_course`, `period_from`, `period_to`, `highest_level_units`, `year_graduated`, `scholarship_honors`) VALUES
(4, 2, 'ELEMENTARY', 'ARINGAY PILOT ELEMENTARY SCHOOL', 'PRIMARY', '1973', '1979', 'N/A', '1979', 'SALUTATORIAN'),
(5, 2, 'HIGH SCHOOL', 'NORTE DAME INSTITUTE', 'HIGH SCHOOL', '1979', '1982', '3 YEAR', '1983', 'N/A'),
(6, 2, 'HIGH SCHOOL', 'DON MARIANO MARCOS MEMORIAL STATE UNIVERSITY', 'HIGH SCHOOL', '1982', '1983', 'GRADUATE', '1983', 'N/A'),
(7, 2, 'COLLEGE', 'SAINT LUIS COLLEGE UNIVERSITY', 'BS MAJOR IN MANAGEMENT', '1984', '1987', 'GRADUATE', '1987', 'N/A'),
(8, 2, 'GRADUATE STUDIES', 'PHILIPPINE CHRISTIAN UNIVERSITY', 'MASTER IN PUBLIC ADMINISTRATION', '2018', '2019', 'GRADUATE', '2020', 'N/A'),
(9, 7, 'ELEMENTARY', 'CHRIST THE KING COLLEGE', 'ELEMENTARY', '2002', '2008', 'N/A', '2008', 'WITH HONORS'),
(10, 7, 'HIGH SCHOOL', 'CHRIST THE KING COLLEGE', 'HIGH SCHOOL', '2008', '2012', 'N/A', '2012', 'WITH HONORS'),
(11, 7, 'ELEMENTARY', 'CHRIST THE KING COLLEGE', 'ELEMENTARY', '2002', '2008', 'N/A', '2008', 'WITH HONORS'),
(12, 7, 'COLLEGE', 'SAINT LOUIS COLLEGE', 'BSBA- MAJOR IN FINANCIAL MANAGEMENT', '2012', '2016', NULL, '2016', 'ACADEMIN AND LEADERSHIP AWARD'),
(13, 7, 'GRADUATE STUDIES', 'LYCEUM DAGUPAN', 'MASTER IN BUSINESS ADMINISTRATION', '2021', '2021', '12', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employee_leaves`
--

CREATE TABLE `employee_leaves` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `leave_type` varchar(100) NOT NULL,
  `date_from` date DEFAULT NULL,
  `date_to` date DEFAULT NULL,
  `days` decimal(5,2) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_leaves`
--

INSERT INTO `employee_leaves` (`id`, `employee_id`, `leave_type`, `date_from`, `date_to`, `days`, `remarks`) VALUES
(4, 2, 'test', '2025-12-01', '2025-12-02', 1.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employee_service_records`
--

CREATE TABLE `employee_service_records` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `position` varchar(150) NOT NULL,
  `office` varchar(255) DEFAULT NULL,
  `place_of` varchar(255) DEFAULT NULL,
  `branch` varchar(255) DEFAULT NULL,
  `assignment` varchar(255) DEFAULT NULL,
  `lv_abs` varchar(255) DEFAULT NULL,
  `wo_pay` varchar(255) DEFAULT NULL,
  `separation_date` date DEFAULT NULL,
  `separation_cause` varchar(255) DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `date_from` date DEFAULT NULL,
  `date_to` date DEFAULT NULL,
  `salary` decimal(15,2) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_service_records`
--

INSERT INTO `employee_service_records` (`id`, `employee_id`, `position`, `office`, `place_of`, `branch`, `assignment`, `lv_abs`, `wo_pay`, `separation_date`, `separation_cause`, `status`, `date_from`, `date_to`, `salary`, `remarks`) VALUES
(3, 2, 'JR. STAT', NULL, 'DAR', 'NATIONAL', NULL, '-DO-', NULL, NULL, NULL, 'PERMANENT', '1989-01-02', '1989-06-30', 13032.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employee_trainings`
--

CREATE TABLE `employee_trainings` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `provider` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `date_from` date DEFAULT NULL,
  `date_to` date DEFAULT NULL,
  `hours` decimal(6,2) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_trainings`
--

INSERT INTO `employee_trainings` (`id`, `employee_id`, `title`, `provider`, `location`, `date_from`, `date_to`, `hours`, `remarks`) VALUES
(2, 2, 'REGIONAL SUMMATIVE ASSESSMENT PERFORMANCE REVIEW FOR CY2023 AND STRATEGIC PLANNING FOR CY2024', 'DAR LA UNION', 'YNADS PLACE HOTEL AND RESORT, CITY OF SAN FERNANDO, LA UNION', '2024-01-17', '2024-01-18', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employee_work_experience`
--

CREATE TABLE `employee_work_experience` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `date_from` date NOT NULL,
  `date_to` date DEFAULT NULL,
  `position_title` varchar(255) NOT NULL,
  `department_agency` varchar(255) NOT NULL,
  `monthly_salary` decimal(15,2) DEFAULT NULL,
  `salary_grade_step` varchar(50) DEFAULT NULL,
  `status_of_appointment` varchar(100) DEFAULT NULL,
  `govt_service` enum('YES','NO') DEFAULT 'YES',
  `description_of_duties` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_work_experience`
--

INSERT INTO `employee_work_experience` (`id`, `employee_id`, `date_from`, `date_to`, `position_title`, `department_agency`, `monthly_salary`, `salary_grade_step`, `status_of_appointment`, `govt_service`, `description_of_duties`) VALUES
(7, 2, '1997-01-01', '1997-10-31', 'ASSISTANT STATISTICIAN', 'DEPARTMENT OF AGRARIAN REFORM', 7129.00, '09-3', 'PERMANENT', 'YES', NULL),
(8, 7, '2015-10-15', '2016-01-06', 'ON THE JOB TRAINING', 'COMMISSION ON HIGHER EDUCATION', NULL, NULL, NULL, 'NO', NULL),
(9, 7, '2016-02-02', '2019-11-25', 'ON THE JOB TRAINING', 'PHILIPPINE POSTAL SAVINGS BANK', NULL, NULL, NULL, 'NO', NULL),
(10, 7, '2017-06-30', '2017-07-02', 'NIGHT AUDITOR', 'MAKATI PALACE HOTEL-MAKATI CITY', 14000.00, NULL, 'PROBITIONARY', 'NO', NULL),
(11, 7, '2017-09-04', '2020-09-15', 'ACCOUNTING CLERK', 'TECHNOLUX SUPPLY AND EQIPMENT CORP-MAKATI CITY', 15000.00, NULL, 'REGULAR', 'NO', NULL),
(12, 7, '2021-09-01', '2022-11-30', 'SPLIT-ADMINISTRATIVE (BUDGET) STAFF', 'DEPARTMENT OF AGRARIAN REFORM-PROVINCE OF LA UNION', 25000.00, NULL, 'CONTRACTUAL', 'NO', NULL),
(13, 7, '2023-12-01', '2023-12-31', 'ADMINNISTRATIVE AIDE VI', 'DEPARTMENT OF AGRARIAN REFORM-PROVINCE OF LA UNION', 16800.00, 'SG 6', 'REGULAR', 'YES', NULL),
(14, 7, '2023-01-01', '2023-07-17', 'ADMINISTRATIVE VI', 'DEPARTMENT OF AGRARIAN REFORM-PROVINCE OF LA UNION', 17553.00, 'SG 6', 'REGULAR', 'YES', NULL),
(15, 7, '2023-07-18', '2025-12-05', 'AGRARIAN REFORM PROGRAM OFFICER I', 'DEPARTMENT OF AGRARIAN REFORM-PROVINCE OF LA UNION', 27000.00, 'SG 11', 'REGULAR', 'YES', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_action_type` (`action_type`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_table_record` (`table_name`,`record_id`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_username` (`username`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_employee` (`employee_id`);

--
-- Indexes for table `calendar_events`
--
ALTER TABLE `calendar_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_event_date` (`event_date`),
  ADD KEY `idx_event_type` (`event_type`),
  ADD KEY `idx_reminder` (`reminder_sent`,`event_date`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_last_name` (`last_name`);

--
-- Indexes for table `employee_awards`
--
ALTER TABLE `employee_awards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_awards_employee` (`employee_id`),
  ADD KEY `idx_awards_date` (`award_date`);

--
-- Indexes for table `employee_educational_background`
--
ALTER TABLE `employee_educational_background`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_education_employee` (`employee_id`),
  ADD KEY `idx_education_level` (`level`);

--
-- Indexes for table `employee_leaves`
--
ALTER TABLE `employee_leaves`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_leave_employee` (`employee_id`),
  ADD KEY `idx_leave_dates` (`date_from`,`date_to`);

--
-- Indexes for table `employee_service_records`
--
ALTER TABLE `employee_service_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_service_employee` (`employee_id`),
  ADD KEY `idx_service_dates` (`date_from`,`date_to`);

--
-- Indexes for table `employee_trainings`
--
ALTER TABLE `employee_trainings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_training_employee` (`employee_id`),
  ADD KEY `idx_training_dates` (`date_from`,`date_to`);

--
-- Indexes for table `employee_work_experience`
--
ALTER TABLE `employee_work_experience`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_work_experience_employee` (`employee_id`),
  ADD KEY `idx_work_experience_dates` (`date_from`,`date_to`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=193;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `calendar_events`
--
ALTER TABLE `calendar_events`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `employee_awards`
--
ALTER TABLE `employee_awards`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `employee_educational_background`
--
ALTER TABLE `employee_educational_background`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `employee_leaves`
--
ALTER TABLE `employee_leaves`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `employee_service_records`
--
ALTER TABLE `employee_service_records`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `employee_trainings`
--
ALTER TABLE `employee_trainings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `employee_work_experience`
--
ALTER TABLE `employee_work_experience`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `fk_appointments_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_awards`
--
ALTER TABLE `employee_awards`
  ADD CONSTRAINT `fk_awards_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_educational_background`
--
ALTER TABLE `employee_educational_background`
  ADD CONSTRAINT `fk_education_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_leaves`
--
ALTER TABLE `employee_leaves`
  ADD CONSTRAINT `fk_leaves_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_service_records`
--
ALTER TABLE `employee_service_records`
  ADD CONSTRAINT `fk_service_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_trainings`
--
ALTER TABLE `employee_trainings`
  ADD CONSTRAINT `fk_trainings_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_work_experience`
--
ALTER TABLE `employee_work_experience`
  ADD CONSTRAINT `fk_work_experience_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
