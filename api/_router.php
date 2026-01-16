<?php
// api/_middleware.php - Middleware to handle routing for Vercel

// Define the router
class PHPRouter {
    private $routes = [];

    public function __construct() {
        // Define all possible routes
        $this->routes = [
            '/activity_log' => '../activity_log.php',
            '/activity_logger' => '../activity_logger.php',
            '/add_philippine_holidays' => '../add_philippine_holidays.php',
            '/admin_profile' => '../admin_profile.php',
            '/auth' => '../auth.php',
            '/auto_backup' => '../auto_backup.php',
            '/backup_restore' => '../backup_restore.php',
            '/calendar_api' => '../calendar_api.php',
            '/cleanup_activity_log' => '../cleanup_activity_log.php',
            '/db' => '../db.php',
            '/download_backup' => '../download_backup.php',
            '/employee_directory' => '../employee_directory.php',
            '/employee_summary_report' => '../employee_summary_report.php',
            '/employee' => '../employee.php',
            '/employees' => '../employees.php',
            '/health' => './health.php',
        ];
    }

    public function handleRequest() {
        $request_uri = $_SERVER['REQUEST_URI'];
        $path = parse_url($request_uri, PHP_URL_PATH);

        // Remove trailing slash
        $path = rtrim($path, '/');

        // Check if the path exists in our routes
        if (isset($this->routes[$path])) {
            $target_file = $this->routes[$path];

            // Make sure the file exists and is readable
            if (file_exists($target_file) && is_readable($target_file)) {
                // Include the requested PHP file
                include_once $target_file;
                exit();
            } else {
                // File doesn't exist
                http_response_code(404);
                echo json_encode(['error' => 'File not found: ' . $target_file]);
                exit();
            }
        }

        // Handle root path
        if ($path === '' || $path === '/') {
            include_once '../index.php';
            exit();
        }

        // If no route matches, show 404
        http_response_code(404);
        echo json_encode(['error' => 'Route not found: ' . $path]);
        exit();
    }
}

// Initialize and handle the request
$router = new PHPRouter();
$router->handleRequest();