<?php
// Common HTML header using SB Admin layout
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$adminUsername = $_SESSION['admin_username'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/employee.css">
    <title>DARLU HRIS Dashboard</title>
    <link rel="icon" type="image/png" sizes="50x50" href="/DarLa/BG-DAR.png">
    <link rel="shortcut icon" href="/DarLa/BG-DAR.png" type="image/x-icon">
    <meta name="theme-color" content="#0b6b3d">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet">
    <link href="startbootstrap-sb-admin-gh-pages/css/styles.css" rel="stylesheet">
    <link rel="stylesheet" href="css/employee.css">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

    <script>
    // System Clock - Update in real-time
    function updateClock() {
        const now = new Date();

        // Format time (HH:MM:SS AM/PM)
        const hours = now.getHours();
        const minutes = now.getMinutes().toString().padStart(2, '0');
        const seconds = now.getSeconds().toString().padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        const displayHours = hours % 12 || 12;
        const timeString = `${displayHours}:${minutes}:${seconds} ${ampm}`;

        // Format date (Day, Month DD, YYYY)
        const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        const months = ['January', 'February', 'March', 'April', 'May', 'June',
                       'July', 'August', 'September', 'October', 'November', 'December'];
        const dayName = days[now.getDay()];
        const monthName = months[now.getMonth()];
        const day = now.getDate();
        const year = now.getFullYear();
        const dateString = `${dayName}, ${monthName} ${day}, ${year}`;

        // Update DOM elements
        const timeElement = document.getElementById('current-time');
        const dateElement = document.getElementById('current-date');

        if (timeElement) {
            timeElement.textContent = timeString;
        }
        if (dateElement) {
            dateElement.textContent = dateString;
        }
    }

    // Update clock immediately and then every second
    updateClock();
    setInterval(updateClock, 1000);
    </script>
</head>
<body class="sb-nav-fixed">
<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
    <!-- Sidebar Toggle-->
    <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!">
        <i class="fas fa-bars"></i>
    </button>
    <div class="ms-auto me-3 d-none d-md-flex align-items-center">
        <div class="system-clock-wrapper">
            <i class="fas fa-clock me-2 text-success"></i>
            <div class="clock-display">
                <div class="clock-time" id="current-time">--:--:-- --</div>
                <div class="clock-date" id="current-date">-- --, ----</div>
            </div>
        </div>
    </div>
    <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
        <li class="nav-item dropdown">
            <a class="nav-link" id="navbarDropdown" href="#" role="button"
               data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user fa-fw"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                <li><a class="dropdown-item" href="admin_profile.php">Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
            </ul>
        </li>
    </ul>
</nav>
<div id="layoutSidenav">
    <div id="layoutSidenav_nav">
        <nav class="sb-sidenav accordion sb-sidenav-light" id="sidenavAccordion">
            <div class="sb-sidenav-brand">
                <div class="d-flex align-items-center px-3 py-3">
                    <img src="BG-DAR1-removebg-preview.png" alt="DAR Logo" class="sidebar-logo me-2">
                    <div>
                        <div class="fw-bold text-dark">DARLU HRIS</div>
                        <small class="text-muted">Department of Agrarian Reform</small>
                    </div>
                </div>
            </div>
            <div class="sb-sidenav-menu">
                <div class="nav">

                    <a class="nav-link" href="index.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                        Dashboard
                    </a>
                <div> </div>
                    <a class="nav-link" href="employee_directory.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                        Employee List
                    </a>
                    <a class="nav-link" href="employee_summary_report.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-chart-bar"></i></div>
                        Summary Report
                    </a>
                    <a class="nav-link" href="retired_employees.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-user-clock"></i></div>
                        Inactive Employee
                    </a>
               <div> </div>
                    <a class="nav-link" href="backup_restore.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-database"></i></div>
                        Backup &amp; Restore
                    </a>
                    <a class="nav-link" href="activity_log.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-history"></i></div>
                        Activity Log
                    </a>
                    <a class="nav-link" href="admin_profile.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-user-cog"></i></div>
                        Admin Profile
                    </a>
                </div>
            </div>
            <div class="sb-sidenav-footer">
                <div class="small text-muted mb-1">Logged in as:</div>
                <div class="fw-bold text-dark"><?= htmlspecialchars($adminUsername) ?></div>
            </div>
        </nav>
    </div>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
