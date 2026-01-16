<?php
/**
 * Database Setup Script
 * This script will create the database and all required tables if they don't exist.
 * Run this once to initialize your database.
 */

// Database configuration (should match db.php)
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';

// Connect to MySQL server (without database)
try {
    $pdo = new PDO("mysql:host=$DB_HOST;charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    echo "Connected to MySQL server.\n";
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS employee_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database 'employee_db' created or already exists.\n";
    
    // Select the database
    $pdo->exec("USE employee_db");
    echo "Using database 'employee_db'.\n";
    
    // Disable foreign key checks temporarily
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Check if employees table exists and is valid
    $tableExists = false;
    $tableValid = false;
    
    // First, try to drop any corrupted table
    try {
        $pdo->exec("ALTER TABLE employees DISCARD TABLESPACE");
        echo "Discarded tablespace for 'employees' table.\n";
    } catch (PDOException $e) {
        // Ignore - table might not exist or tablespace already discarded
    }
    
    // Drop the table if it exists
    try {
        $pdo->exec("DROP TABLE IF EXISTS employees");
        echo "Dropped 'employees' table (if it existed).\n";
        $tableExists = false;
    } catch (PDOException $e) {
        // Table might not exist, which is fine
        echo "Note: " . $e->getMessage() . "\n";
    }
    
    // Now check if table exists by trying to query it
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'employees'");
        $tableExists = $stmt->rowCount() > 0;
        if ($tableExists) {
            $pdo->query("SELECT 1 FROM employees LIMIT 1");
            $tableValid = true;
        }
    } catch (PDOException $e) {
        $tableExists = false;
        $tableValid = false;
    }
    
    if (!$tableExists || !$tableValid) {
        // Create employees table
        $pdo->exec("
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "Table 'employees' created successfully.\n";
        echo "Column 'office' included in table creation.\n";
    } else {
        echo "Table 'employees' already exists and is valid.\n";
        
        // Check if office column exists, add it if not
        try {
            $stmt = $pdo->query("SHOW COLUMNS FROM employees LIKE 'office'");
            if ($stmt->rowCount() == 0) {
                $pdo->exec("ALTER TABLE employees ADD COLUMN office VARCHAR(255) DEFAULT NULL AFTER home_address");
                echo "Added 'office' column to employees table.\n";
            } else {
                echo "Column 'office' already exists in employees table.\n";
            }
        } catch (PDOException $e) {
            echo "Warning: Could not check/add office column: " . $e->getMessage() . "\n";
            echo "The table may be corrupted. Try dropping and recreating it.\n";
        }
    }
    
    // Create other essential tables if they don't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS appointments (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            employee_id INT UNSIGNED NOT NULL,
            sequence_no TINYINT UNSIGNED NOT NULL DEFAULT 1,
            type_label VARCHAR(100) NOT NULL,
            position VARCHAR(150) NOT NULL,
            item_number VARCHAR(100) DEFAULT NULL,
            salary_grade VARCHAR(20) DEFAULT NULL,
            appointment_date DATE DEFAULT NULL,
            salary DECIMAL(15,2) DEFAULT NULL,
            INDEX idx_employee (employee_id),
            CONSTRAINT fk_appointments_employee FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "Table 'appointments' created or already exists.\n";
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS employee_trainings (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            employee_id INT UNSIGNED NOT NULL,
            title VARCHAR(255) NOT NULL,
            provider VARCHAR(255) DEFAULT NULL,
            location VARCHAR(255) DEFAULT NULL,
            date_from DATE DEFAULT NULL,
            date_to DATE DEFAULT NULL,
            hours DECIMAL(6,2) DEFAULT NULL,
            remarks VARCHAR(255) DEFAULT NULL,
            INDEX idx_training_employee (employee_id),
            CONSTRAINT fk_trainings_employee FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "Table 'employee_trainings' created or already exists.\n";
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS employee_leaves (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            employee_id INT UNSIGNED NOT NULL,
            leave_type VARCHAR(100) NOT NULL,
            date_from DATE DEFAULT NULL,
            date_to DATE DEFAULT NULL,
            days DECIMAL(5,2) DEFAULT NULL,
            remarks VARCHAR(255) DEFAULT NULL,
            INDEX idx_leave_employee (employee_id),
            CONSTRAINT fk_leaves_employee FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "Table 'employee_leaves' created or already exists.\n";
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS employee_service_records (
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
            INDEX idx_service_employee (employee_id),
            CONSTRAINT fk_service_employee FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "Table 'employee_service_records' created or already exists.\n";
    
    $pdo->exec("
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
            INDEX idx_education_employee (employee_id),
            CONSTRAINT fk_education_employee FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "Table 'employee_educational_background' created or already exists.\n";
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS employee_work_experience (
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
            INDEX idx_work_experience_employee (employee_id),
            CONSTRAINT fk_work_experience_employee FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "Table 'employee_work_experience' created or already exists.\n";
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS employee_awards (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            employee_id INT UNSIGNED NOT NULL,
            title VARCHAR(255) NOT NULL,
            award_level VARCHAR(100) DEFAULT NULL,
            awarding_body VARCHAR(255) DEFAULT NULL,
            award_date DATE DEFAULT NULL,
            remarks VARCHAR(255) DEFAULT NULL,
            description TEXT DEFAULT NULL,
            INDEX idx_awards_employee (employee_id),
            CONSTRAINT fk_awards_employee FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "Table 'employee_awards' created or already exists.\n";
    
    // Re-enable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "\n✅ Database setup completed successfully!\n";
    echo "You can now access your application.\n";
    
} catch (PDOException $e) {
    die("❌ Error: " . $e->getMessage() . "\n");
}
?>

