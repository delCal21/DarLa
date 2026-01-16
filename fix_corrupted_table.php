<?php
/**
 * Fix Corrupted Employees Table
 * This script forcefully removes and recreates the corrupted employees table
 */

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';

try {
    $pdo = new PDO("mysql:host=$DB_HOST;charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    echo "Connected to MySQL server.\n";
    $pdo->exec("USE employee_db");
    echo "Using database 'employee_db'.\n\n";
    
    // Step 1: Try to discard tablespace (ignore errors)
    echo "Step 1: Attempting to discard tablespace...\n";
    try {
        $pdo->exec("ALTER TABLE employees DISCARD TABLESPACE");
        echo "  ✓ Tablespace discarded.\n";
    } catch (PDOException $e) {
        echo "  ⚠ Could not discard tablespace (this is OK if table doesn't exist): " . $e->getMessage() . "\n";
    }
    
    // Step 2: Drop the table (ignore errors)
    echo "\nStep 2: Dropping table...\n";
    try {
        $pdo->exec("DROP TABLE employees");
        echo "  ✓ Table dropped.\n";
    } catch (PDOException $e) {
        echo "  ⚠ Could not drop table: " . $e->getMessage() . "\n";
        // Try to force remove from information_schema (this is a workaround)
        echo "  Attempting alternative method...\n";
    }
    
    // Step 3: Create the table (try InnoDB first, fallback to MyISAM if needed)
    echo "\nStep 3: Creating employees table...\n";
    $tableCreated = false;
    
    // Try InnoDB first
    try {
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
        echo "  ✓ Table created successfully with InnoDB engine!\n";
        $tableCreated = true;
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), '1813') !== false || strpos($e->getMessage(), 'Tablespace') !== false) {
            echo "  ⚠ InnoDB tablespace issue detected. Trying MyISAM engine...\n";
            // Try with MyISAM
            try {
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
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                echo "  ✓ Table created with MyISAM engine!\n";
                echo "  Note: Table is using MyISAM engine (InnoDB conversion failed due to tablespace conflict).\n";
                echo "  This is fine for now - the table will work correctly.\n";
                echo "  To convert to InnoDB later, manually delete the tablespace file and run: ALTER TABLE employees ENGINE=InnoDB\n";
                $tableCreated = true;
            } catch (PDOException $e2) {
                throw $e2; // Re-throw if MyISAM also fails
            }
        } else {
            throw $e; // Re-throw if it's a different error
        }
    }
    
    if (!$tableCreated) {
        throw new Exception("Failed to create table");
    }
    
    // Step 4: Verify the table
    echo "\nStep 4: Verifying table...\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM employees");
    $result = $stmt->fetch();
    echo "  ✓ Table is accessible. Current row count: " . $result['count'] . "\n";
    
    echo "\n✅ SUCCESS! The employees table has been fixed and recreated.\n";
    echo "You can now access your application at index.php\n";
    
} catch (PDOException $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nIf the error persists, you may need to:\n";
    echo "1. Stop MySQL/MariaDB service\n";
    echo "2. Manually delete the tablespace files from the data directory\n";
    echo "3. Restart MySQL/MariaDB service\n";
    echo "4. Run this script again\n";
    echo "\nOr use phpMyAdmin to:\n";
    echo "1. Select the employee_db database\n";
    echo "2. Go to the employees table\n";
    echo "3. Click 'Operations' tab\n";
    echo "4. Click 'Drop the table (DROP)'\n";
    echo "5. Then run setup_database.php again\n";
}

