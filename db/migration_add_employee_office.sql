-- Migration: Add Department/Office field to employees table
-- Run this SQL to update your existing database
--
-- Usage:
-- 1. Open your MySQL/MariaDB client (phpMyAdmin, HeidiSQL, etc.).
-- 2. Make sure you're connected to the correct server.
-- 3. Execute this script once.

USE employee_db;

ALTER TABLE employees
ADD COLUMN office VARCHAR(255) DEFAULT NULL AFTER home_address;


