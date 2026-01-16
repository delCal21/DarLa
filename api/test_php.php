<?php
// Simple test page to verify PHP execution
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>PHP Test Page</h1>";
echo "<p>PHP is working! Version: " . PHP_VERSION . "</p>";
echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>Current directory: " . getcwd() . "</p>";

// Test database connection if available
if (file_exists('../db.php')) {
    echo "<h2>Testing database connection...</h2>";
    try {
        require_once '../db.php';
        if (isset($pdo) && $pdo !== null) {
            echo "<p style='color: green;'>✓ Database connection successful</p>";
        } else {
            echo "<p style='color: red;'>✗ Database connection failed or unavailable</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Database error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: orange;'>Database file not found</p>";
}

echo "<h3><a href='/'>Return to Home</a> | <a href='/?page=login'>Go to Login</a></h3>";