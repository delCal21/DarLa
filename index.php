<?php
// Basic index page to handle requests
if (isset($_GET['page']) && $_GET['page'] === 'activity_log') {
    include_once 'activity_log.php';
} elseif (isset($_GET['page']) && $_GET['page'] === 'employees') {
    include_once 'employees.php';
} elseif (isset($_GET['page']) && $_GET['page'] === 'admin') {
    include_once 'admin_profile.php';
} else {
    // Default page - you can customize this
    echo "<h1>DarLa Application</h1>";
    echo "<p>Welcome to the DarLa application!</p>";
    echo "<ul>";
    echo "<li><a href='/?page=activity_log'>Activity Log</a></li>";
    echo "<li><a href='/?page=employees'>Employees</a></li>";
    echo "<li><a href='/?page=admin'>Admin Profile</a></li>";
    echo "</ul>";
}