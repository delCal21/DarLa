-- DARLa HRIS Database Backup
-- Generated on: 2025-12-03 06:50:26
-- Database: employee_db

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
SET AUTOCOMMIT=0;
START TRANSACTION;

-- Table structure for `employees`
DROP TABLE IF EXISTS `employees`;
CREATE TABLE `employees` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
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
  `bp_number` varchar(50) DEFAULT NULL,
  `pagibig_number` varchar(50) DEFAULT NULL,
  `philhealth_number` varchar(50) DEFAULT NULL,
  `trainings` text DEFAULT NULL,
  `designations` text DEFAULT NULL,
  `leave_info` text DEFAULT NULL,
  `service_record` text DEFAULT NULL,
  `employment_status` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_last_name` (`last_name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `employees`
INSERT INTO `employees` VALUES
('2','TATUNAY','NEMIA THEODORA','MADARANG','1966-04-01','#2 Quirino Street Poblacion, Amligay La Union','09773222445','nemiamadarang@example.com','Married','Tatunay, Constantine Q.','09178023113 / 09984540328 / 09478940436','105890306','2000132073','1280-0000-1868','100000091396','trainings...','HR','leave info...','service record...','Permanent','2025-12-02 23:25:16','2025-12-03 13:03:15');

-- Table structure for `appointments`
DROP TABLE IF EXISTS `appointments`;
CREATE TABLE `appointments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL,
  `sequence_no` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `type_label` varchar(100) NOT NULL,
  `position` varchar(150) NOT NULL,
  `item_number` varchar(100) DEFAULT NULL,
  `salary_grade` varchar(20) DEFAULT NULL,
  `appointment_date` date DEFAULT NULL,
  `salary` decimal(15,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_employee` (`employee_id`),
  CONSTRAINT `fk_appointments_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `appointments`
INSERT INTO `appointments` VALUES
('13','2','1','TEST','HR','12','22','2025-12-03','1000000.00'),
('14','2','2','TEST','HR','12','22','2025-12-03','1000000.00');

-- Table structure for `employee_trainings`
DROP TABLE IF EXISTS `employee_trainings`;
CREATE TABLE `employee_trainings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `provider` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `date_from` date DEFAULT NULL,
  `date_to` date DEFAULT NULL,
  `hours` decimal(6,2) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_training_employee` (`employee_id`),
  KEY `idx_training_dates` (`date_from`,`date_to`),
  CONSTRAINT `fk_trainings_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `employee_trainings`
INSERT INTO `employee_trainings` VALUES
('1','2','TEST','TEST','LU','2025-12-03','2025-12-04','3.00','TEST');

-- Table structure for `employee_leaves`
DROP TABLE IF EXISTS `employee_leaves`;
CREATE TABLE `employee_leaves` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL,
  `leave_type` varchar(100) NOT NULL,
  `date_from` date DEFAULT NULL,
  `date_to` date DEFAULT NULL,
  `days` decimal(5,2) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_leave_employee` (`employee_id`),
  KEY `idx_leave_dates` (`date_from`,`date_to`),
  CONSTRAINT `fk_leaves_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `employee_leaves`
INSERT INTO `employee_leaves` VALUES
('1','2','TEST','2025-12-03','2025-12-04','2.00','TEST');

-- Table structure for `employee_service_records`
DROP TABLE IF EXISTS `employee_service_records`;
CREATE TABLE `employee_service_records` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL,
  `position` varchar(150) NOT NULL,
  `office` varchar(255) DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `date_from` date DEFAULT NULL,
  `date_to` date DEFAULT NULL,
  `salary` decimal(15,2) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_service_employee` (`employee_id`),
  KEY `idx_service_dates` (`date_from`,`date_to`),
  CONSTRAINT `fk_service_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `employee_service_records`
INSERT INTO `employee_service_records` VALUES
('1','2','HR','TEST','PR','2025-12-03','2025-12-03','100000.00','TEST');

-- Table structure for `admins`
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `idx_username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `admins`
INSERT INTO `admins` VALUES
('1','DARLa','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','DARLa Administrator','admin@darla.gov.ph',NULL,'2025-12-03 11:03:59','2025-12-03 13:27:28');

-- Table structure for `calendar_events`
DROP TABLE IF EXISTS `calendar_events`;
CREATE TABLE `calendar_events` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` date NOT NULL,
  `event_time` time DEFAULT NULL,
  `event_type` enum('activity','holiday','season','reminder') DEFAULT 'activity',
  `reminder_sent` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_event_date` (`event_date`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_reminder` (`reminder_sent`,`event_date`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `calendar_events`
INSERT INTO `calendar_events` VALUES
('1','Happ Birthday','TEST','2025-12-05','10:00:00','reminder','0','2025-12-03 11:19:34','2025-12-03 11:19:34'),
('2','New Year\'s Day',NULL,'2025-01-01',NULL,'holiday','0','2025-12-03 11:29:08','2025-12-03 11:29:08'),
('3','EDSA People Power Revolution Anniversary',NULL,'2025-02-25',NULL,'holiday','0','2025-12-03 11:29:08','2025-12-03 11:29:08'),
('4','Araw ng Kagitingan (Day of Valor)',NULL,'2025-04-09',NULL,'holiday','0','2025-12-03 11:29:08','2025-12-03 11:29:08'),
('5','Labor Day',NULL,'2025-05-01',NULL,'holiday','0','2025-12-03 11:29:08','2025-12-03 11:29:08'),
('6','Independence Day',NULL,'2025-06-12',NULL,'holiday','0','2025-12-03 11:29:08','2025-12-03 11:29:08'),
('7','National Heroes Day',NULL,'2025-08-25',NULL,'holiday','0','2025-12-03 11:29:08','2025-12-03 11:29:08'),
('8','Bonifacio Day',NULL,'2025-11-30',NULL,'holiday','0','2025-12-03 11:29:08','2025-12-03 11:29:08'),
('9','Rizal Day',NULL,'2025-12-30',NULL,'holiday','0','2025-12-03 11:29:08','2025-12-03 11:29:08'),
('10','Christmas Day',NULL,'2025-12-25',NULL,'holiday','0','2025-12-03 11:29:08','2025-12-03 11:29:08'),
('11','All Saints\' Day',NULL,'2025-11-01',NULL,'holiday','0','2025-12-03 11:29:08','2025-12-03 11:29:08'),
('12','All Souls\' Day',NULL,'2025-11-02',NULL,'holiday','0','2025-12-03 11:29:08','2025-12-03 11:29:08'),
('13','Christmas Eve',NULL,'2025-12-24',NULL,'holiday','0','2025-12-03 11:29:08','2025-12-03 11:29:08'),
('14','New Year\'s Eve',NULL,'2025-12-31',NULL,'holiday','0','2025-12-03 11:29:08','2025-12-03 11:29:08'),
('15','Maundy Thursday',NULL,'2025-04-17',NULL,'holiday','0','2025-12-03 11:29:08','2025-12-03 11:29:08'),
('16','Good Friday',NULL,'2025-04-18',NULL,'holiday','0','2025-12-03 11:29:08','2025-12-03 11:29:08'),
('17','Black Saturday',NULL,'2025-04-19',NULL,'holiday','0','2025-12-03 11:29:08','2025-12-03 11:29:08'),
('18','New Year\'s Day',NULL,'2026-01-01',NULL,'holiday','0','2025-12-03 11:29:08','2025-12-03 11:29:08'),
('19','EDSA People Power Revolution Anniversary',NULL,'2026-02-25',NULL,'holiday','0','2025-12-03 11:29:08','2025-12-03 11:29:08'),
('20','Araw ng Kagitingan (Day of Valor)',NULL,'2026-04-09',NULL,'holiday','0','2025-12-03 11:29:08','2025-12-03 11:29:08'),
('21','Labor Day',NULL,'2026-05-01',NULL,'holiday','0','2025-12-03 11:29:08','2025-12-03 11:29:08'),
('22','Independence Day',NULL,'2026-06-12',NULL,'holiday','0','2025-12-03 11:29:08','2025-12-03 11:29:08'),
('23','National Heroes Day',NULL,'2026-08-31',NULL,'holiday','0','2025-12-03 11:29:08','2025-12-03 11:29:08'),
('24','Bonifacio Day',NULL,'2026-11-30',NULL,'holiday','0','2025-12-03 11:29:08','2025-12-03 11:29:08'),
('25','Rizal Day',NULL,'2026-12-30',NULL,'holiday','0','2025-12-03 11:29:08','2025-12-03 11:29:08'),
('26','Christmas Day',NULL,'2026-12-25',NULL,'holiday','0','2025-12-03 11:29:08','2025-12-03 11:29:08'),
('27','All Saints\' Day',NULL,'2026-11-01',NULL,'holiday','0','2025-12-03 11:29:08','2025-12-03 11:29:08'),
('28','All Souls\' Day',NULL,'2026-11-02',NULL,'holiday','0','2025-12-03 11:29:08','2025-12-03 11:29:08'),
('29','Christmas Eve',NULL,'2026-12-24',NULL,'holiday','0','2025-12-03 11:29:08','2025-12-03 11:29:08'),
('30','New Year\'s Eve',NULL,'2026-12-31',NULL,'holiday','0','2025-12-03 11:29:08','2025-12-03 11:29:08'),
('31','Maundy Thursday',NULL,'2026-04-02',NULL,'holiday','0','2025-12-03 11:29:08','2025-12-03 11:29:08'),
('32','Good Friday',NULL,'2026-04-03',NULL,'holiday','0','2025-12-03 11:29:08','2025-12-03 11:29:08'),
('33','Black Saturday',NULL,'2026-04-04',NULL,'holiday','0','2025-12-03 11:29:08','2025-12-03 11:29:08');

-- Table structure for `activity_logs`
DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` varchar(50) NOT NULL,
  `action_type` varchar(100) NOT NULL,
  `action_description` text NOT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int(10) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_action_type` (`action_type`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_table_record` (`table_name`,`record_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `activity_logs`
INSERT INTO `activity_logs` VALUES
('1','DarLa','event_create','Added 32 Philippine holidays to calendar',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0','2025-12-03 11:29:08'),
('2','DarLa','event_create','Added 0 Philippine holidays to calendar',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0','2025-12-03 11:29:32'),
('3','DarLa','event_create','Added 0 Philippine holidays to calendar',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0','2025-12-03 11:29:53'),
('4','DarLa','employee_update','Employment status updated to: Pending','employees','2','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0','2025-12-03 13:02:58'),
('5','DarLa','employee_update','Employment status updated to: Permanent','employees','2','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0','2025-12-03 13:03:15'),
('6','DarLa','logout','User \'DarLa\' logged out',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0','2025-12-03 13:27:50'),
('7','DarLa','login','User \'DarLa\' logged in successfully',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0','2025-12-03 13:29:05'),
('8','DarLa','logout','User \'DarLa\' logged out',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0','2025-12-03 13:30:06'),
('9','DARLa','login','User \'DARLa\' logged in successfully',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0','2025-12-03 13:30:27');

COMMIT;
SET FOREIGN_KEY_CHECKS=1;
