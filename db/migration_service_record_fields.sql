-- Migration: Add new fields to employee_service_records table to match official service record format
-- Run this SQL to update your existing database

USE employee_db;

ALTER TABLE employee_service_records
ADD COLUMN place_of VARCHAR(255) DEFAULT NULL AFTER office,
ADD COLUMN branch VARCHAR(255) DEFAULT NULL AFTER place_of,
ADD COLUMN assignment VARCHAR(255) DEFAULT NULL AFTER branch,
ADD COLUMN lv_abs VARCHAR(255) DEFAULT NULL AFTER assignment,
ADD COLUMN wo_pay VARCHAR(255) DEFAULT NULL AFTER lv_abs,
ADD COLUMN separation_date DATE DEFAULT NULL AFTER wo_pay,
ADD COLUMN separation_cause VARCHAR(255) DEFAULT NULL AFTER separation_date;

