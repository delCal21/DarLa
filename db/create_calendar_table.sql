-- Calendar events table for DARLa HRIS
-- Run this SQL to create the calendar table

USE employee_db;

CREATE TABLE IF NOT EXISTS calendar_events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    event_date DATE NOT NULL,
    event_time TIME DEFAULT NULL,
    event_type ENUM('activity', 'holiday', 'season', 'reminder') DEFAULT 'activity',
    reminder_sent BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_event_date (event_date),
    INDEX idx_event_type (event_type),
    INDEX idx_reminder (reminder_sent, event_date)
);

