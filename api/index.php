<?php
// Enable CORS for the API
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('HTTP/1.1 200 OK');
    exit();
}

// Define the response based on the HTTP method
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Return Hello World for GET requests
    header('Content-Type: application/json');
    echo json_encode(['message' => 'Hello World']);
} else {
    // Method not allowed
    header('HTTP/1.1 405 Method Not Allowed');
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Method not allowed']);
}
?>