<?php
/**
 * Activity Logger Helper
 * Logs all system activities to the database
 */

function logActivity($actionType, $description, $tableName = null, $recordId = null) {
    global $pdo;
    
    // Get current user
    $userId = $_SESSION['admin_username'] ?? 'System';
    
    // Get IP address
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    
    // Get user agent
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    try {
        // Check if table exists
        try {
            $pdo->query("SELECT 1 FROM activity_logs LIMIT 1");
        } catch (PDOException $e) {
            // Table doesn't exist, create it
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS activity_logs (
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
                )
            ");
        }
        
        // Insert log
        $stmt = $pdo->prepare("
            INSERT INTO activity_logs 
            (user_id, action_type, action_description, table_name, record_id, ip_address, user_agent) 
            VALUES (:user_id, :action_type, :description, :table_name, :record_id, :ip_address, :user_agent)
        ");
        
        $stmt->execute([
            ':user_id' => $userId,
            ':action_type' => $actionType,
            ':description' => $description,
            ':table_name' => $tableName,
            ':record_id' => $recordId,
            ':ip_address' => $ipAddress,
            ':user_agent' => $userAgent
        ]);
        
        return true;
    } catch (PDOException $e) {
        // Silently fail if logging fails (don't break the main functionality)
        error_log("Activity log error: " . $e->getMessage());
        return false;
    }
}

