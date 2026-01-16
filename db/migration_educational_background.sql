-- Migration: Create employee_educational_background table
-- Run this SQL to create the educational background table

USE employee_db;

CREATE TABLE IF NOT EXISTS employee_educational_background (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id INT UNSIGNED NOT NULL,
    level VARCHAR(50) NOT NULL,
    school_name VARCHAR(255) NOT NULL,
    degree_course VARCHAR(255) DEFAULT NULL,
    period_from VARCHAR(20) DEFAULT NULL,
    period_to VARCHAR(20) DEFAULT NULL,
    highest_level_units VARCHAR(255) DEFAULT NULL,
    year_graduated VARCHAR(20) DEFAULT NULL,
    scholarship_honors VARCHAR(255) DEFAULT NULL,

    CONSTRAINT fk_education_employee
        FOREIGN KEY (employee_id) REFERENCES employees(id)
        ON DELETE CASCADE,
    INDEX idx_education_employee (employee_id),
    INDEX idx_education_level (level)
);

