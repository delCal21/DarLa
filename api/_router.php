<?php
// api/_router.php - Router to handle routing for Vercel

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
            '/test' => './test.php',
            '/status' => './status.php',
            '/diagnostic' => './diagnostic.php',
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

            // Adjust path for Vercel environment
            $full_path = __DIR__ . '/' . $target_file;

            // Normalize the path to remove any double dots for security
            $full_path = realpath($full_path) ?: $full_path;

            // Make sure the file exists and is readable and is within the allowed directories
            if (file_exists($full_path) && is_readable($full_path) && strpos($full_path, __DIR__) === 0) {
                // Include the requested PHP file
                include_once $full_path;
                exit();
            } else {
                // File doesn't exist or is outside allowed directories
                http_response_code(404);
                echo json_encode(['error' => 'File not found or unauthorized: ' . $target_file]);
                exit();
            }
        }

        // Handle root path - direct to main index.php which will handle the application logic
        if ($path === '' || $path === '/') {
            $index_path = __DIR__ . '/../index.php';
            if (file_exists($index_path) && is_readable($index_path)) {
                include_once $index_path;
            } else {
                echo json_encode(['error' => 'Main index.php not found']);
            }
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