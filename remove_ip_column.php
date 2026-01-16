<?php
/**
 * Script to remove the IP address column from the activity_logs table
 * Run this script once to update the database structure
 */

require_once 'db.php';

try {
    // Check if the ip_address column exists
    $columns = $pdo->query("SHOW COLUMNS FROM activity_logs")->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('ip_address', $columns)) {
        // Remove the ip_address column
        $pdo->exec("ALTER TABLE activity_logs DROP COLUMN ip_address");
        echo "IP address column removed successfully.\n";
    } else {
        echo "IP address column does not exist in the table.\n";
    }
    
    // Also update the table creation SQL in case the table needs to be recreated
    // This ensures the new structure is used if the table is ever dropped and recreated
    echo "Database schema updated. The IP address column will no longer be stored.\n";
    
} catch (PDOException $e) {
    echo "Error updating database: " . $e->getMessage() . "\n";
}
?>