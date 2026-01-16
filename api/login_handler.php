<?php
// api/login_handler.php - Direct handler for login page

// Make sure we're in the right directory context
chdir(__DIR__ . '/../');

// Include the login page directly
if (file_exists('login.php')) {
    include 'login.php';
} else {
    http_response_code(404);
    echo "Login page not found";
}