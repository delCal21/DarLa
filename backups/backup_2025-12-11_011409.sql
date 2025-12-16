-- DARLa HRIS Database Backup
-- Generated on: 2025-12-11 01:14:09
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
  `tin_number` varchar(50) DEFAULT NULL,
  `sss_number` varchar(50) DEFAULT NULL,
  `gsis_number` varchar(50) DEFAULT NULL,
  `trainings` text DEFAULT NULL,
  `designations` text DEFAULT NULL,
  `leave_info` text DEFAULT NULL,
  `service_record` text DEFAULT NULL,
  `employment_status` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_last_name` (`last_name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `employees`
INSERT INTO `employees` VALUES
('2','TATUNAY','NEMIA THEODORA','MADARANG','1966-04-01','#2 Quirino Street Poblacion, Amligay La Union','09773222445','nemiamadarang66@gmail.com','Married','Tatunay, Constantine Q.','09178023113 / 09984540328 / 09478940436','105890306','2000132073','1280-0000-1868','100000091396','132221615','n/a','00600001868',NULL,NULL,NULL,NULL,'Permanent','2025-12-02 23:25:16','2025-12-05 11:04:33'),
('7','TABIO-HIPOL','ANNABELLE','VIDAL','1995-10-22','PUROK 3, PIAS, SAN FERNANDO, LA UNION','09567644284','tabioannabelle22@gmail.com','Married','HIPOL, ARNEL OLIVER, MARQUEZ',NULL,NULL,NULL,'916159530894','052507813879','329379117','0125475808','N/A',NULL,NULL,NULL,NULL,'Permanent','2025-12-05 11:13:54','2025-12-05 11:15:05');

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
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- No data for table `appointments`

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `employee_trainings`
INSERT INTO `employee_trainings` VALUES
('2','2','REGIONAL SUMMATIVE ASSESSMENT PERFORMANCE REVIEW FOR CY2023 AND STRATEGIC PLANNING FOR CY2024','DAR LA UNION','YNADS PLACE HOTEL AND RESORT, CITY OF SAN FERNANDO, LA UNION','2024-01-17','2024-01-18',NULL,NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `employee_leaves`
INSERT INTO `employee_leaves` VALUES
('4','2','test','2025-12-01','2025-12-02','1.00',NULL);

-- Table structure for `employee_service_records`
DROP TABLE IF EXISTS `employee_service_records`;
CREATE TABLE `employee_service_records` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL,
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
  `remarks` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_service_employee` (`employee_id`),
  KEY `idx_service_dates` (`date_from`,`date_to`),
  CONSTRAINT `fk_service_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `employee_service_records`
INSERT INTO `employee_service_records` VALUES
('3','2','JR. STAT',NULL,'DAR','NATIONAL',NULL,'-DO-',NULL,NULL,NULL,'PERMANENT','1989-01-02','1989-06-30','13032.00',NULL);

-- Table structure for `employee_work_experience`
DROP TABLE IF EXISTS `employee_work_experience`;
CREATE TABLE `employee_work_experience` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL,
  `date_from` date NOT NULL,
  `date_to` date DEFAULT NULL,
  `position_title` varchar(255) NOT NULL,
  `department_agency` varchar(255) NOT NULL,
  `monthly_salary` decimal(15,2) DEFAULT NULL,
  `salary_grade_step` varchar(50) DEFAULT NULL,
  `status_of_appointment` varchar(100) DEFAULT NULL,
  `govt_service` enum('YES','NO') DEFAULT 'YES',
  `description_of_duties` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_work_experience_employee` (`employee_id`),
  KEY `idx_work_experience_dates` (`date_from`,`date_to`),
  CONSTRAINT `fk_work_experience_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `employee_work_experience`
INSERT INTO `employee_work_experience` VALUES
('7','2','1997-01-01','1997-10-31','ASSISTANT STATISTICIAN','DEPARTMENT OF AGRARIAN REFORM','7129.00','09-3','PERMANENT','YES',NULL),
('8','7','2015-10-15','2016-01-06','ON THE JOB TRAINING','COMMISSION ON HIGHER EDUCATION',NULL,NULL,NULL,'NO',NULL),
('9','7','2016-02-02','2019-11-25','ON THE JOB TRAINING','PHILIPPINE POSTAL SAVINGS BANK',NULL,NULL,NULL,'NO',NULL),
('10','7','2017-06-30','2017-07-02','NIGHT AUDITOR','MAKATI PALACE HOTEL-MAKATI CITY','14000.00',NULL,'PROBITIONARY','NO',NULL),
('11','7','2017-09-04','2020-09-15','ACCOUNTING CLERK','TECHNOLUX SUPPLY AND EQIPMENT CORP-MAKATI CITY','15000.00',NULL,'REGULAR','NO',NULL),
('12','7','2021-09-01','2022-11-30','SPLIT-ADMINISTRATIVE (BUDGET) STAFF','DEPARTMENT OF AGRARIAN REFORM-PROVINCE OF LA UNION','25000.00',NULL,'CONTRACTUAL','NO',NULL),
('13','7','2023-12-01','2023-12-31','ADMINNISTRATIVE AIDE VI','DEPARTMENT OF AGRARIAN REFORM-PROVINCE OF LA UNION','16800.00','SG 6','REGULAR','YES',NULL),
('14','7','2023-01-01','2023-07-17','ADMINISTRATIVE VI','DEPARTMENT OF AGRARIAN REFORM-PROVINCE OF LA UNION','17553.00','SG 6','REGULAR','YES',NULL),
('15','7','2023-07-18','2025-12-05','AGRARIAN REFORM PROGRAM OFFICER I','DEPARTMENT OF AGRARIAN REFORM-PROVINCE OF LA UNION','27000.00','SG 11','REGULAR','YES',NULL);

-- Table structure for `employee_awards`
DROP TABLE IF EXISTS `employee_awards`;
CREATE TABLE `employee_awards` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `award_level` varchar(100) DEFAULT NULL,
  `awarding_body` varchar(255) DEFAULT NULL,
  `award_date` date DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_awards_employee` (`employee_id`),
  KEY `idx_awards_date` (`award_date`),
  CONSTRAINT `fk_awards_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `employee_awards`
INSERT INTO `employee_awards` VALUES
('1','2','xzgbf',NULL,'ddd','2025-12-02',NULL,NULL);

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
('1','DARLa','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','DAR LA UNION Administrator','admin@darla.gov.ph',NULL,'2025-12-03 11:03:59','2025-12-03 16:20:09');

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
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `calendar_events`
INSERT INTO `calendar_events` VALUES
('1','Happ Birthday','TEST','2025-12-05','10:00:00','reminder','1','2025-12-03 11:19:34','2025-12-04 07:48:40'),
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
('33','Black Saturday',NULL,'2026-04-04',NULL,'holiday','0','2025-12-03 11:29:08','2025-12-03 11:29:08'),
('34','Chinese New Year',NULL,'2025-01-29',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('35','Ninoy Aquino Day',NULL,'2025-08-21',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('36','Constitution Day',NULL,'2025-02-02',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('37','National Flag Day',NULL,'2025-05-28',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('38','Philippine-Spanish Friendship Day',NULL,'2025-06-30',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('39','National Teachers\' Day',NULL,'2025-10-05',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('40','United Nations Day',NULL,'2025-10-24',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('41','Araw ng Maynila (Manila Day)',NULL,'2025-06-24',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('42','Araw ng Quezon (Quezon Day)',NULL,'2025-08-19',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('43','Araw ng Davao (Davao Day)',NULL,'2025-03-16',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('44','Araw ng Cebu (Cebu Day)',NULL,'2025-04-07',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('45','Feast of the Black Nazarene',NULL,'2025-01-09',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('46','Feast of Our Lady of Lourdes',NULL,'2025-02-11',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('47','Feast of St. Joseph',NULL,'2025-03-19',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('48','Feast of the Santo Niño',NULL,'2025-01-15',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('49','Feast of Our Lady of Peñafrancia',NULL,'2025-09-08',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('50','Feast of the Immaculate Conception',NULL,'2025-12-08',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('51','Sinulog Festival (Cebu)',NULL,'2025-01-15',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('52','Ati-Atihan Festival (Aklan)',NULL,'2025-01-15',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('53','Dinagyang Festival (Iloilo)',NULL,'2025-01-25',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('54','Panagbenga Festival (Baguio)',NULL,'2025-02-01',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('55','Moriones Festival (Marinduque)',NULL,'2025-04-18',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('56','Kadayawan Festival (Davao)',NULL,'2025-08-15',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('57','MassKara Festival (Bacolod)',NULL,'2025-10-19',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('58','Higantes Festival (Angono)',NULL,'2025-11-23',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('59','Chinese New Year',NULL,'2026-01-29',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('60','Ninoy Aquino Day',NULL,'2026-08-21',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('61','Constitution Day',NULL,'2026-02-02',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('62','National Flag Day',NULL,'2026-05-28',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('63','Philippine-Spanish Friendship Day',NULL,'2026-06-30',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('64','National Teachers\' Day',NULL,'2026-10-05',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('65','United Nations Day',NULL,'2026-10-24',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('66','Araw ng Maynila (Manila Day)',NULL,'2026-06-24',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('67','Araw ng Quezon (Quezon Day)',NULL,'2026-08-19',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('68','Araw ng Davao (Davao Day)',NULL,'2026-03-16',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('69','Araw ng Cebu (Cebu Day)',NULL,'2026-04-07',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('70','Feast of the Black Nazarene',NULL,'2026-01-09',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('71','Feast of Our Lady of Lourdes',NULL,'2026-02-11',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('72','Feast of St. Joseph',NULL,'2026-03-19',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('73','Feast of the Santo Niño',NULL,'2026-01-15',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('74','Feast of Our Lady of Peñafrancia',NULL,'2026-09-08',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('75','Feast of the Immaculate Conception',NULL,'2026-12-08',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('76','Sinulog Festival (Cebu)',NULL,'2026-01-15',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('77','Ati-Atihan Festival (Aklan)',NULL,'2026-01-15',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('78','Dinagyang Festival (Iloilo)',NULL,'2026-01-25',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('79','Panagbenga Festival (Baguio)',NULL,'2026-02-01',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('80','Moriones Festival (Marinduque)',NULL,'2026-04-03',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('81','Kadayawan Festival (Davao)',NULL,'2026-08-15',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('82','MassKara Festival (Bacolod)',NULL,'2026-10-19',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48'),
('83','Higantes Festival (Angono)',NULL,'2026-11-23',NULL,'holiday','0','2025-12-03 15:17:48','2025-12-03 15:17:48');

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
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `activity_logs`
INSERT INTO `activity_logs` VALUES
('74','DARLa','login','User \'DARLa\' logged in successfully',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0','2025-12-11 07:57:53');

COMMIT;
SET FOREIGN_KEY_CHECKS=1;
