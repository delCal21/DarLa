<?php
/**
 * Activity Log Cleanup Script
 * Deletes activity logs from previous days
 * Run this script daily via cron job or manually
 */

require_once 'db.php';

try {
    // Delete all activity logs from previous days (keep only today's logs)
    $stmt = $pdo->prepare("
        DELETE FROM activity_logs 
        WHERE DATE(created_at) < CURDATE()
    ");
    
    $stmt->execute();
    $deletedCount = $stmt->rowCount();
    
    echo "Activity log cleanup completed. Deleted {$deletedCount} old log entries.\n";
    
    // Log the cleanup action
    if (function_exists('logActivity')) {
        require_once 'activity_logger.php';
        logActivity('system_cleanup', "Activity log cleanup: Deleted {$deletedCount} old entries");
    }
    
} catch (PDOException $e) {
    echo "Error during cleanup: " . $e->getMessage() . "\n";
    exit(1);
}

