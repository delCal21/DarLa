<?php
require_once 'auth.php';
require_admin_login();

if (!isset($_GET['file']) || empty($_GET['file'])) {
    header('Location: backup_restore.php');
    exit;
}

$filename = basename($_GET['file']);
$backupPath = __DIR__ . '/backups/' . $filename;

// Security check: ensure file exists and is a backup file
if (!file_exists($backupPath) || !preg_match('/^backup_.*\.sql$/', $filename)) {
    header('Location: backup_restore.php');
    exit;
}

// Set headers for file download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($backupPath));
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');

// Output the file
readfile($backupPath);
exit;

