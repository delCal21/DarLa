<?php
// Database connection helper for Vercel deployment
// Use environment variables for production, fallback to defaults for local

function getDatabaseConnection() {
    static $pdo = null;

    if ($pdo === null) {
        $DB_HOST = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?? 'localhost';
        $DB_NAME = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?? 'employee_db';
        $DB_USER = $_ENV['DB_USER'] ?? getenv('DB_USER') ?? 'root';
        $DB_PASS = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?? '';

        $dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";

        try {
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false, // Important for security
                PDO::ATTR_PERSISTENT => false // Disable persistent connections for serverless
            ];

            $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
        } catch (PDOException $e) {
            // Don't die here, just return null or throw exception that can be caught
            error_log('Database connection failed: ' . $e->getMessage());
            throw $e;
        }
    }

    return $pdo;
}

// For backward compatibility, try to create the connection
try {
    $pdo = getDatabaseConnection();
} catch (PDOException $e) {
    // Log the error but don't output it directly to prevent white screens
    error_log('Database connection failed: ' . $e->getMessage());
    $pdo = null;
}
