<?php
// api/health.php - Health check endpoint for Vercel

header('Content-Type: application/json');

// Basic health check
$health_status = [
    'status' => 'healthy',
    'timestamp' => date('c'),
    'service' => 'DarLa PHP Application',
    'version' => '1.0.0'
];

echo json_encode($health_status);