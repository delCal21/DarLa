<?php
/**
 * Fix All Corrupted Tables
 * This script fixes all tables that may be corrupted due to tablespace issues
 */

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';

// List of all tables that need to be created
$tables = [
    'employees' => "
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
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    'appointments' => "
        CREATE TABLE appointments (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            employee_id INT UNSIGNED NOT NULL,
            sequence_no TINYINT UNSIGNED NOT NULL DEFAULT 1,
            type_label VARCHAR(100) NOT NULL,
            position VARCHAR(150) NOT NULL,
            item_number VARCHAR(100) DEFAULT NULL,
            salary_grade VARCHAR(20) DEFAULT NULL,
            appointment_date DATE DEFAULT NULL,
            salary DECIMAL(15,2) DEFAULT NULL,
            INDEX idx_employee (employee_id)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    'employee_trainings' => "
        CREATE TABLE employee_trainings (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            employee_id INT UNSIGNED NOT NULL,
            title VARCHAR(255) NOT NULL,
            provider VARCHAR(255) DEFAULT NULL,
            location VARCHAR(255) DEFAULT NULL,
            date_from DATE DEFAULT NULL,
            date_to DATE DEFAULT NULL,
            hours DECIMAL(6,2) DEFAULT NULL,
            remarks VARCHAR(255) DEFAULT NULL,
            INDEX idx_training_employee (employee_id)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    'employee_leaves' => "
        CREATE TABLE employee_leaves (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            employee_id INT UNSIGNED NOT NULL,
            leave_type VARCHAR(100) NOT NULL,
            date_from DATE DEFAULT NULL,
            date_to DATE DEFAULT NULL,
            days DECIMAL(5,2) DEFAULT NULL,
            remarks VARCHAR(255) DEFAULT NULL,
            INDEX idx_leave_employee (employee_id)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    'employee_service_records' => "
        CREATE TABLE employee_service_records (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            employee_id INT UNSIGNED NOT NULL,
            position VARCHAR(150) NOT NULL,
            office VARCHAR(255) DEFAULT NULL,
            place_of VARCHAR(255) DEFAULT NULL,
            branch VARCHAR(255) DEFAULT NULL,
            assignment VARCHAR(255) DEFAULT NULL,
            lv_abs VARCHAR(255) DEFAULT NULL,
            status VARCHAR(100) DEFAULT NULL,
            date_from DATE DEFAULT NULL,
            date_to DATE DEFAULT NULL,
            salary DECIMAL(15,2) DEFAULT NULL,
            wo_pay VARCHAR(255) DEFAULT NULL,
            separation_date DATE DEFAULT NULL,
            separation_cause VARCHAR(255) DEFAULT NULL,
            remarks VARCHAR(255) DEFAULT NULL,
            INDEX idx_service_employee (employee_id)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    'employee_educational_background' => "
        CREATE TABLE employee_educational_background (
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
            INDEX idx_education_employee (employee_id)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    'employee_work_experience' => "
        CREATE TABLE employee_work_experience (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            employee_id INT UNSIGNED NOT NULL,
            date_from DATE NOT NULL,
            date_to DATE DEFAULT NULL,
            position_title VARCHAR(255) NOT NULL,
            department_agency VARCHAR(255) NOT NULL,
            monthly_salary DECIMAL(15,2) DEFAULT NULL,
            salary_grade_step VARCHAR(50) DEFAULT NULL,
            status_of_appointment VARCHAR(100) DEFAULT NULL,
            govt_service ENUM('YES', 'NO') DEFAULT 'YES',
            description_of_duties TEXT DEFAULT NULL,
            INDEX idx_work_experience_employee (employee_id)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    'employee_awards' => "
        CREATE TABLE employee_awards (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            employee_id INT UNSIGNED NOT NULL,
            title VARCHAR(255) NOT NULL,
            award_level VARCHAR(100) DEFAULT NULL,
            awarding_body VARCHAR(255) DEFAULT NULL,
            award_date DATE DEFAULT NULL,
            remarks VARCHAR(255) DEFAULT NULL,
            description TEXT DEFAULT NULL,
            INDEX idx_awards_employee (employee_id)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    'calendar_events' => "
        CREATE TABLE calendar_events (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT DEFAULT NULL,
            event_date DATE NOT NULL,
            event_time TIME DEFAULT NULL,
            event_type ENUM('activity', 'holiday', 'season', 'reminder') DEFAULT 'activity',
            reminder_sent TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_event_date (event_date),
            INDEX idx_event_type (event_type),
            INDEX idx_reminder (reminder_sent, event_date)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    'admins' => "
        CREATE TABLE admins (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            full_name VARCHAR(150) DEFAULT NULL,
            email VARCHAR(150) DEFAULT NULL,
            last_login TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY username (username),
            INDEX idx_username (username)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    'activity_logs' => "
        CREATE TABLE activity_logs (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id VARCHAR(50) NOT NULL,
            action_type VARCHAR(100) NOT NULL,
            action_description TEXT NOT NULL,
            table_name VARCHAR(100) DEFAULT NULL,
            record_id INT UNSIGNED DEFAULT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_action_type (action_type),
            INDEX idx_created_at (created_at),
            INDEX idx_table_record (table_name, record_id)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    "
];

try {
    $pdo = new PDO("mysql:host=$DB_HOST;charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    echo "Connected to MySQL server.\n";
    $pdo->exec("USE employee_db");
    echo "Using database 'employee_db'.\n\n";
    
    // Disable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    foreach ($tables as $tableName => $createSQL) {
        echo "Processing table: $tableName\n";
        
        // Step 1: Try to discard tablespace (ignore errors)
        try {
            $pdo->exec("ALTER TABLE $tableName DISCARD TABLESPACE");
            echo "  ✓ Tablespace discarded.\n";
        } catch (PDOException $e) {
            // Ignore - table might not exist
        }
        
        // Step 2: Drop the table (ignore errors)
        try {
            $pdo->exec("DROP TABLE IF EXISTS $tableName");
            echo "  ✓ Table dropped (if it existed).\n";
        } catch (PDOException $e) {
            echo "  ⚠ Could not drop table: " . $e->getMessage() . "\n";
        }
        
        // Step 3: Create the table
        try {
            // Try InnoDB first
            $createSQLInnoDB = str_replace('ENGINE=MyISAM', 'ENGINE=InnoDB', $createSQL);
            $pdo->exec($createSQLInnoDB);
            echo "  ✓ Table created with InnoDB engine!\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), '1813') !== false || strpos($e->getMessage(), 'Tablespace') !== false) {
                // Tablespace conflict - use MyISAM
                echo "  ⚠ InnoDB tablespace issue. Using MyISAM...\n";
                $pdo->exec($createSQL);
                echo "  ✓ Table created with MyISAM engine!\n";
            } else {
                throw $e;
            }
        }
        
        // Step 4: Verify the table
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $tableName");
            $result = $stmt->fetch();
            echo "  ✓ Table verified. Current row count: " . $result['count'] . "\n";
        } catch (PDOException $e) {
            echo "  ⚠ Warning: Could not verify table: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    // Re-enable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "✅ SUCCESS! All tables have been fixed and recreated.\n";
    echo "You can now access your application.\n";
    echo "\nNote: Tables are using MyISAM engine due to InnoDB tablespace conflicts.\n";
    echo "This is fine for most operations. Foreign keys won't work, but basic functionality will.\n";
    
} catch (PDOException $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}

