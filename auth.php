<?php
// Basic admin authentication helpers for serverless environments

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // In serverless environments, sessions might not work as expected
    // We'll try to start the session but handle failures gracefully
    if (!headers_sent()) {
        session_start();
    } else {
        // If headers are already sent, we can't start a session
        // Just define a placeholder for session data
        if (!isset($_SESSION)) {
            $_SESSION = [];
        }
    }
}

/**
 * Returns true if the current user is logged in as admin.
 */
function is_admin_logged_in(): bool
{
    // Check if session is available
    if (isset($_SESSION) && is_array($_SESSION)) {
        return !empty($_SESSION['admin_logged_in']);
    }
    // If session is not available, return false
    return false;
}

/**
 * Require admin login for a page. If not logged in, redirect to login page.
 */
function require_admin_login(): void
{
    if (!is_admin_logged_in()) {
        // Instead of redirecting directly, return a flag that the page can handle
        if (!headers_sent()) {
            header('Location: /?page=login');
            exit;
        } else {
            // If headers are already sent, we can't redirect
            // Display a message or handle differently
            echo '<div class="alert alert-warning">Please <a href="/?page=login">login</a> to access this page.</div>';
            exit;
        }
    }
}


