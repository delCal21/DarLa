<?php
// Diagnostic page to troubleshoot white screen issues
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>Diagnostic Information</h1>";

echo "<h2>Environment Variables:</h2>";
echo "<pre>";
var_dump($_ENV);
echo "</pre>";

echo "<h2>Server Info:</h2>";
echo "<p>PHP Version: " . PHP_VERSION . "</p>";
echo "<p>Current Directory: " . getcwd() . "</p>";

echo "<h2>File System Check:</h2>";
$files_to_check = [
    'auth.php',
    'db.php',
    'employees.php',
    'login.php',
    'activity_logger.php'
];

foreach ($files_to_check as $file) {
    $exists = file_exists($file) ? 'YES' : 'NO';
    $readable = is_readable($file) ? 'YES' : 'NO';
    echo "<p>{$file}: Exists: {$exists}, Readable: {$readable}</p>";
}

echo "<h2>Session Status:</h2>";
echo "<p>Session Status: " . session_status() . "</p>";

echo "<h2>Try to initialize session:</h2>";
if (session_status() === PHP_SESSION_NONE) {
    echo "<p>Attempting to start session...</p>";
    if (@session_start()) {
        echo "<p>Session started successfully</p>";
    } else {
        echo "<p>Failed to start session</p>";
    }
} else {
    echo "<p>Session already active</p>";
}

echo "<h2>Database Connection Test:</h2>";
try {
    require_once 'db.php';
    if (isset($pdo)) {
        echo "<p>Database connection successful</p>";
        echo "<p>Connection info: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . "</p>";
    } else {
        echo "<p>Database connection variable not set</p>";
    }
} catch (Exception $e) {
    echo "<p>Database connection failed: " . $e->getMessage() . "</p>";
}

echo "<h2>Authentication Check:</h2>";
try {
    require_once 'auth.php';
    if (function_exists('is_admin_logged_in')) {
        echo "<p>Auth functions loaded successfully</p>";
        echo "<p>Admin logged in: " . (is_admin_logged_in() ? 'YES' : 'NO') . "</p>";
    } else {
        echo "<p>Auth functions not available</p>";
    }
} catch (Exception $e) {
    echo "<p>Auth check failed: " . $e->getMessage() . "</p>";
}

echo "<hr><p><a href='/'>Return to Home</a> | <a href='/?page=login'>Go to Login</a></p>";