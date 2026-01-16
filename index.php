<?php
// Redirect to the main employees page by default, or handle routing
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
            if (file_exists('employees.php')) {
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
        default:
            // Show main application page
            if (file_exists('employees.php')) {
                include_once 'employees.php';
            } else {
                echo "<h1>DarLa Application</h1>";
                echo "<p>Welcome to the DarLa application!</p>";
                echo "<ul>";
                echo "<li><a href='/?page=activity_log'>Activity Log</a></li>";
                echo "<li><a href='/?page=employees'>Employees</a></li>";
                echo "<li><a href='/?page=admin'>Admin Profile</a></li>";
                echo "</ul>";
            }
    }
} else {
    // Default: Load the main employees page if it exists
    if (file_exists('employees.php')) {
        include_once 'employees.php';
    } else {
        // Fallback to default page
        echo "<h1>DarLa Application</h1>";
        echo "<p>Welcome to the DarLa application!</p>";
        echo "<ul>";
        echo "<li><a href='/?page=activity_log'>Activity Log</a></li>";
        echo "<li><a href='/?page=employees'>Employees</a></li>";
        echo "<li><a href='/?page=admin'>Admin Profile</a></li>";
        echo "</ul>";
    }
}