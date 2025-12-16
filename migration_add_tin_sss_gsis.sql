-- Migration: Add TIN, SSS, and GSIS number fields to employees table
-- Run this SQL to update your existing database

USE employee_db;

ALTER TABLE employees
ADD COLUMN tin_number VARCHAR(50) DEFAULT NULL AFTER philhealth_number,
ADD COLUMN sss_number VARCHAR(50) DEFAULT NULL AFTER tin_number,
ADD COLUMN gsis_number VARCHAR(50) DEFAULT NULL AFTER sss_number;

