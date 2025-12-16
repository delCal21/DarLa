<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Log logout activity before destroying session
$username = $_SESSION['admin_username'] ?? 'Unknown';
if (!empty($username)) {
    require_once 'db.php';
    require_once 'activity_logger.php';
    logActivity('logout', "User '{$username}' logged out");
}

// Clear all session data and destroy the session
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

session_destroy();

header('Location: login.php');
exit;


