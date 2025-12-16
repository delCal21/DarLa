<?php
// Basic admin authentication helpers

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Returns true if the current user is logged in as admin.
 */
function is_admin_logged_in(): bool
{
    return !empty($_SESSION['admin_logged_in']);
}

/**
 * Require admin login for a page. If not logged in, redirect to login page.
 */
function require_admin_login(): void
{
    if (!is_admin_logged_in()) {
        header('Location: login.php');
        exit;
    }
}


