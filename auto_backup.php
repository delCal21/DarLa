<?php
/**
 * Automatic Backup Script for DARLa HRIS
 * This script creates a database backup automatically
 * Designed to be run via Windows Task Scheduler every Friday at 4:30 PM
 */

// Set timezone
date_default_timezone_set('Asia/Manila');

// Include required files
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/activity_logger.php';

// Log file for backup operations
$logFile = __DIR__ . '/backups/auto_backup.log';

function writeLog($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] {$message}\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    echo $logMessage;
}

try {
    writeLog("=== Automatic Backup Started ===");
    
    // Get database name from connection
    $dbName = $pdo->query('SELECT DATABASE()')->fetchColumn();
    writeLog("Database: {$dbName}");
    
    // Create backups directory if it doesn't exist
    $backupDir = __DIR__ . '/backups';
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
        writeLog("Created backups directory");
    }
    
    // Generate backup filename with "auto_" prefix
    $timestamp = date('Y-m-d_His');
    $backupFile = $backupDir . '/auto_backup_' . $timestamp . '.sql';
    
    // First attempt: use mysqldump (includes routines, triggers, events)
    $useMysqldump = false;
    if (isset($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME)) {
        $mysqldumpCmd = "mysqldump --host={$DB_HOST} --user={$DB_USER} --password={$DB_PASS} --routines --triggers --events --single-transaction --quick {$DB_NAME} > \"{$backupFile}\"";
        exec($mysqldumpCmd, $cmdOut, $cmdStatus);
        if ($cmdStatus === 0 && file_exists($backupFile) && filesize($backupFile) > 0) {
            writeLog("mysqldump succeeded and created: " . basename($backupFile));
            try {
                logActivity('backup_create', "Automatic backup (mysqldump) created: " . basename($backupFile));
            } catch (Exception $e) {
                writeLog("Warning: Could not log activity: " . $e->getMessage());
            }
            writeLog("=== Automatic Backup Completed Successfully (mysqldump) ===");
            exit(0);
        } else {
            writeLog("mysqldump not available or failed, falling back to PHP dump method");
            // remove empty file if created
            if (file_exists($backupFile) && filesize($backupFile) === 0) {
                @unlink($backupFile);
            }
        }
    }

    // If mysqldump not available, fall back to PHP-based table dump (all existing tables)
    $tables = [];
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    writeLog("Starting backup of " . count($tables) . " tables");
    
    $output = "-- DARLa HRIS Automatic Database Backup\n";
    $output .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
    $output .= "-- Database: {$dbName}\n";
    $output .= "-- Backup Type: Automatic (Scheduled)\n\n";
    $output .= "SET FOREIGN_KEY_CHECKS=0;\n";
    $output .= "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n";
    $output .= "SET AUTOCOMMIT=0;\n";
    $output .= "START TRANSACTION;\n\n";
    
    $totalRows = 0;
    
    // Export each table
    foreach ($tables as $table) {
        try {
            // Check if table exists
            $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
            if ($stmt->rowCount() === 0) {
                writeLog("Table '{$table}' does not exist, skipping...");
                continue;
            }
            
            // Get table structure
            $stmt = $pdo->query("SHOW CREATE TABLE `{$table}`");
            $createTable = $stmt->fetch();
            $output .= "-- Table structure for `{$table}`\n";
            $output .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $output .= $createTable['Create Table'] . ";\n\n";
            
            // Get table data
            $stmt = $pdo->query("SELECT * FROM `{$table}`");
            $rows = $stmt->fetchAll();
            $rowCount = count($rows);
            $totalRows += $rowCount;
            
            if (!empty($rows)) {
                $output .= "-- Dumping data for table `{$table}` ({$rowCount} rows)\n";
                $output .= "INSERT INTO `{$table}` VALUES\n";
                
                $values = [];
                foreach ($rows as $row) {
                    $rowValues = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $rowValues[] = 'NULL';
                        } else {
                            $rowValues[] = $pdo->quote($value);
                        }
                    }
                    $values[] = '(' . implode(',', $rowValues) . ')';
                }
                $output .= implode(",\n", $values) . ";\n\n";
                writeLog("  - {$table}: {$rowCount} rows");
            } else {
                $output .= "-- No data for table `{$table}`\n\n";
                writeLog("  - {$table}: 0 rows");
            }
        } catch (Exception $e) {
            writeLog("  ERROR with table '{$table}': " . $e->getMessage());
            // Continue with other tables
        }
    }
    
    $output .= "COMMIT;\n";
    $output .= "SET FOREIGN_KEY_CHECKS=1;\n";
    
    // Write to file
    $bytesWritten = file_put_contents($backupFile, $output);
    
    if ($bytesWritten === false) {
        throw new Exception("Failed to write backup file");
    }
    
    $fileSize = filesize($backupFile);
    $fileSizeKB = round($fileSize / 1024, 2);
    $fileSizeMB = round($fileSize / (1024 * 1024), 2);
    
    writeLog("Backup file created: " . basename($backupFile));
    writeLog("File size: {$fileSizeKB} KB ({$fileSizeMB} MB)");
    writeLog("Total rows backed up: {$totalRows}");
    
    // Log activity in the system
    try {
        logActivity('backup_create', "Automatic backup created: " . basename($backupFile) . " ({$fileSizeKB} KB)");
    } catch (Exception $e) {
        writeLog("Warning: Could not log activity: " . $e->getMessage());
    }
    
    // Clean up old automatic backups (keep last 8 weeks = ~8 backups)
    $autoBackups = glob($backupDir . '/auto_backup_*.sql');
    if (count($autoBackups) > 8) {
        // Sort by modification time (oldest first)
        usort($autoBackups, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });
        
        // Remove oldest backups, keeping only the 8 most recent
        $toDelete = count($autoBackups) - 8;
        for ($i = 0; $i < $toDelete; $i++) {
            if (unlink($autoBackups[$i])) {
                writeLog("Deleted old backup: " . basename($autoBackups[$i]));
            }
        }
    }
    
    writeLog("=== Automatic Backup Completed Successfully ===");
    writeLog("");
    
    exit(0); // Success
    
} catch (Exception $e) {
    $errorMsg = "ERROR: " . $e->getMessage();
    writeLog($errorMsg);
    writeLog("=== Automatic Backup Failed ===");
    writeLog("");
    
    // Try to log the error in activity log
    try {
        logActivity('backup_error', "Automatic backup failed: " . $e->getMessage());
    } catch (Exception $e2) {
        // Ignore if activity logging fails
    }
    
    exit(1); // Failure
}

