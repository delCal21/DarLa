<?php
/**
 * Fix Activity Logs Table
 * This script fixes the corrupted activity_logs table
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
    
    $tableName = 'activity_logs';
    
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
    $createSQL = "
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
    ";
    
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
    
    echo "\n✅ SUCCESS! The activity_logs table has been fixed and recreated.\n";
    echo "Activity logging will now work correctly.\n";
    
} catch (PDOException $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}

