<?php
// Main application entry point
// Check if user is requesting a specific page
if (isset($_GET['page'])) {
    switch ($_GET['page']) {
        case 'activity_log':
            if (file_exists('activity_log.php')) {
                include_once 'activity_log.php';
            } else {
                echo "Page not found";
            }
            break;
        case 'employees':
            // employees.php might require authentication, handle gracefully
            if (file_exists('employees.php')) {
                // Temporarily disable auth check for demo, or include with error handling
                include_once 'employees.php';
            } else {
                echo "Page not found";
            }
            break;
        case 'admin':
            if (file_exists('admin_profile.php')) {
                include_once 'admin_profile.php';
            } else {
                echo "Page not found";
            }
            break;
        case 'employee':
            if (file_exists('employee.php')) {
                include_once 'employee.php';
            } else {
                echo "Page not found";
            }
            break;
        case 'login':
            if (file_exists('login.php')) {
                include_once 'login.php';
            } else {
                echo "Login page not found";
            }
            break;
        default:
            // For unknown pages, show a menu
            echo "<h1>DarLa Application</h1>";
            echo "<p>Welcome to the DarLa application!</p>";
            echo "<ul>";
            echo "<li><a href='/?page=employees'>Employees</a></li>";
            echo "<li><a href='/?page=activity_log'>Activity Log</a></li>";
            echo "<li><a href='/?page=admin'>Admin Profile</a></li>";
            echo "<li><a href='/?page=login'>Login</a></li>";
            echo "</ul>";
    }
} else {
    // Default landing page - show login link or main page based on auth status
    if (file_exists('auth.php')) {
        require_once 'auth.php';
        if (is_admin_logged_in()) {
            // If logged in, show employees page
            if (file_exists('employees.php')) {
                include_once 'employees.php';
            } else {
                echo "<h1>DarLa Application</h1>";
                echo "<p>You are logged in. Employees page not found.</p>";
                echo "<a href='/?page=login'>Go to Login</a>";
            }
        } else {
            // If not logged in, redirect to login
            if (file_exists('login.php')) {
                include_once 'login.php';
            } else {
                echo "<h1>DarLa Application</h1>";
                echo "<p>Please <a href='/?page=login'>login</a> to access the application.</p>";
            }
        }
    } else {
        // Auth system not available, show basic menu
        echo "<h1>DarLa Application</h1>";
        echo "<p>Welcome to the DarLa application!</p>";
        echo "<ul>";
        echo "<li><a href='/?page=employees'>Employees</a></li>";
        echo "<li><a href='/?page=activity_log'>Activity Log</a></li>";
        echo "<li><a href='/?page=admin'>Admin Profile</a></li>";
        echo "<li><a href='/?page=login'>Login</a></li>";
        echo "</ul>";
    }
}