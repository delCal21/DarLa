<?php
/**
 * Fix Calendar Events Table
 * This script fixes the corrupted calendar_events table
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
    
    $tableName = 'calendar_events';
    
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
    
    echo "\n✅ SUCCESS! The calendar_events table has been fixed and recreated.\n";
    echo "You can now add events to the calendar.\n";
    echo "Note: The table is empty. You can use the 'Add Holidays' button in the dashboard to add Philippine holidays.\n";
    
} catch (PDOException $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}

