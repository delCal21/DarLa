-- Admin table for DARLa HRIS
-- Run this SQL to create the admin table

USE employee_db;

CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(150) DEFAULT NULL,
    email VARCHAR(150) DEFAULT NULL,
    last_login TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_username (username)
);

-- Insert default admin (password: DARLU2025)
-- Password hash is for 'DARLU2025' using password_hash() with PASSWORD_DEFAULT
INSERT INTO admins (username, password_hash, full_name, email) VALUES
('DarLa', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'DARLa Administrator', 'admin@darla.gov.ph')
ON DUPLICATE KEY UPDATE username=username;

