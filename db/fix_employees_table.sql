-- Fix corrupted employees table
-- Run this in MySQL/MariaDB command line or phpMyAdmin

USE employee_db;

-- Try to discard tablespace if table exists
SET @table_exists = 0;
SELECT COUNT(*) INTO @table_exists 
FROM information_schema.tables 
WHERE table_schema = 'employee_db' 
AND table_name = 'employees';

SET @sql = IF(@table_exists > 0, 
    'ALTER TABLE employees DISCARD TABLESPACE', 
    'SELECT "Table does not exist" AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Drop the table
DROP TABLE IF EXISTS employees;

-- Create the employees table
CREATE TABLE employees (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    last_name VARCHAR(100) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) DEFAULT NULL,
    birthdate DATE DEFAULT NULL,
    home_address VARCHAR(255) DEFAULT NULL,
    office VARCHAR(255) DEFAULT NULL,
    contact_no VARCHAR(50) DEFAULT NULL,
    email VARCHAR(150) DEFAULT NULL,
    civil_status VARCHAR(50) DEFAULT NULL,
    spouse_name VARCHAR(150) DEFAULT NULL,
    spouse_contact_no VARCHAR(50) DEFAULT NULL,
    employee_number VARCHAR(50) DEFAULT NULL,
    bp_number VARCHAR(50) DEFAULT NULL,
    pagibig_number VARCHAR(50) DEFAULT NULL,
    philhealth_number VARCHAR(50) DEFAULT NULL,
    tin_number VARCHAR(50) DEFAULT NULL,
    sss_number VARCHAR(50) DEFAULT NULL,
    gsis_number VARCHAR(50) DEFAULT NULL,
    trainings TEXT NULL,
    designations TEXT NULL,
    leave_info TEXT NULL,
    service_record TEXT NULL,
    employment_status VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_last_name (last_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'Employees table created successfully!' AS message;

