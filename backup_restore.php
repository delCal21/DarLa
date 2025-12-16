<?php


require_once 'auth.php';

require_admin_login();

require_once 'db.php';

require_once 'activity_logger.php';



$successMessage = '';

$errorMessage = '';



// Handle backup creation

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'create_backup') {

        try {

            // Get database name from connection

            $dbName = $pdo->query('SELECT DATABASE()')->fetchColumn();

            

            // Create backups directory if it doesn't exist

            $backupDir = __DIR__ . '/backups';

            if (!is_dir($backupDir)) {

                mkdir($backupDir, 0755, true);

            }

            

            // Generate backup filename

            $timestamp = date('Y-m-d_His');

            $backupFile = $backupDir . '/backup_' . $timestamp . '.sql';

            

            // Get all tables

            $tables = [

                'employees',

                'appointments',

                'employee_trainings',

                'employee_leaves',

                'employee_service_records',

                'employee_work_experience',
                'employee_awards',
                'admins',

                'calendar_events',

                'activity_logs'

            ];

            

            $output = "-- DARLa HRIS Database Backup\n";

            $output .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n";

            $output .= "-- Database: {$dbName}\n\n";

            $output .= "SET FOREIGN_KEY_CHECKS=0;\n";

            $output .= "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n";

            $output .= "SET AUTOCOMMIT=0;\n";

            $output .= "START TRANSACTION;\n\n";

            

            // Export each table

            foreach ($tables as $table) {

                // Get table structure

                $stmt = $pdo->query("SHOW CREATE TABLE `{$table}`");

                $createTable = $stmt->fetch();

                $output .= "-- Table structure for `{$table}`\n";

                $output .= "DROP TABLE IF EXISTS `{$table}`;\n";

                $output .= $createTable['Create Table'] . ";\n\n";

                

                // Get table data

                $stmt = $pdo->query("SELECT * FROM `{$table}`");

                $rows = $stmt->fetchAll();

                

                if (!empty($rows)) {

                    $output .= "-- Dumping data for table `{$table}`\n";

                    $output .= "INSERT INTO `{$table}` VALUES\n";

                    

                    $values = [];

                    foreach ($rows as $row) {

                        $rowValues = [];

                        foreach ($row as $value) {

                            if ($value === null) {

                                $rowValues[] = 'NULL';

                            } else {

                                $rowValues[] = $pdo->quote($value);

                            }

                        }

                        $values[] = '(' . implode(',', $rowValues) . ')';

                    }

                    $output .= implode(",\n", $values) . ";\n\n";

                } else {

                    $output .= "-- No data for table `{$table}`\n\n";

                }

            }

            

            $output .= "COMMIT;\n";

            $output .= "SET FOREIGN_KEY_CHECKS=1;\n";

            

            // Write to file

            file_put_contents($backupFile, $output);

            

            // Log activity

            logActivity('backup_create', "Database backup created: " . basename($backupFile));

            

            $successMessage = 'Backup created successfully: ' . basename($backupFile);

        } catch (Exception $e) {

            $errorMessage = 'Error creating backup: ' . htmlspecialchars($e->getMessage());

        }

    } elseif ($_POST['action'] === 'restore_backup') {

        try {

            if (!isset($_FILES['backup_file']) || $_FILES['backup_file']['error'] !== UPLOAD_ERR_OK) {

                throw new Exception('No file uploaded or upload error occurred.');

            }

            

            $uploadedFile = $_FILES['backup_file']['tmp_name'];

            $sql = file_get_contents($uploadedFile);

            

            if ($sql === false) {

                throw new Exception('Could not read backup file.');

            }

            

            // Disable foreign key checks temporarily

            $pdo->exec('SET FOREIGN_KEY_CHECKS=0');

            

            // Split SQL into individual statements

            $statements = array_filter(

                array_map('trim', explode(';', $sql)),

                function($stmt) {

                    return !empty($stmt) && 

                           !preg_match('/^(--|SET|START|COMMIT)/i', $stmt);

                }

            );

            

            // Execute each statement

            foreach ($statements as $statement) {

                if (!empty(trim($statement))) {

                    $pdo->exec($statement);

                }

            }

            

            // Re-enable foreign key checks

            $pdo->exec('SET FOREIGN_KEY_CHECKS=1');

            

            // Log activity

            $filename = $_FILES['backup_file']['name'] ?? 'unknown';

            logActivity('backup_restore', "Database restored from backup file: {$filename}");

            

            $successMessage = 'Database restored successfully from backup file.';

        } catch (Exception $e) {

            $errorMessage = 'Error restoring backup: ' . htmlspecialchars($e->getMessage());

            // Re-enable foreign key checks in case of error

            try {

                $pdo->exec('SET FOREIGN_KEY_CHECKS=1');

            } catch (Exception $e2) {

                // Ignore

            }

        }

    } elseif ($_POST['action'] === 'delete_backup') {

        $backupFile = $_POST['backup_file'] ?? '';

        if (!empty($backupFile)) {

            $backupPath = __DIR__ . '/backups/' . basename($backupFile);

            if (file_exists($backupPath) && unlink($backupPath)) {

                // Log activity

                logActivity('backup_delete', "Backup file deleted: {$backupFile}");

                $successMessage = 'Backup file deleted successfully.';

            } else {

                $errorMessage = 'Error deleting backup file.';

            }

        }

    }

}



// Get list of existing backups

$backupDir = __DIR__ . '/backups';

$backups = [];

if (is_dir($backupDir)) {

    $files = glob($backupDir . '/backup_*.sql');

    rsort($files); // Newest first

    foreach ($files as $file) {

        $backups[] = [

            'filename' => basename($file),

            'size' => filesize($file),

            'date' => date('Y-m-d H:i:s', filemtime($file)),

            'path' => $file

        ];

    }

}



require_once 'header.php';

?>



<!-- Breadcrumb -->

<ol class="breadcrumb mb-3">

    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>

    <li class="breadcrumb-item active">Backup &amp; Restore</li>

</ol>



<!-- Professional Header Card -->

<div class="card border-0 shadow-sm mb-3 professional-header-card compact-header">

    <div class="card-body p-3">

        <div class="row align-items-center">

            <div class="col-md-8">

                <div class="d-flex align-items-center">

                    <div class="directory-avatar me-2">

                        <div class="avatar-circle compact-avatar">

                            <i class="fas fa-database"></i>

                        </div>

                    </div>

                    <div>

                        <h1 class="h4 mb-0 fw-bold text-dark">Backup &amp; Restore</h1>

                        <p class="text-muted mb-0 small">

                            <i class="fas fa-info-circle me-1"></i>

                            Create backups and restore your employee database

                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>



<!-- Alert Messages -->

<?php if (!empty($errorMessage)): ?>

    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">

        <i class="fas fa-exclamation-circle me-2"></i>

        <div><?= htmlspecialchars($errorMessage) ?></div>

        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>

    </div>

<?php endif; ?>



<?php if (!empty($successMessage)): ?>

    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">

        <i class="fas fa-check-circle me-2"></i>

        <div><?= htmlspecialchars($successMessage) ?></div>

        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>

    </div>

<?php endif; ?>



<!-- Backup Section -->

<div class="row g-3 mb-4">

    <div class="col-md-6">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-header bg-white border-bottom">

                <h5 class="card-title mb-0">

                    <i class="fas fa-download me-2 text-primary"></i>

                    Create Backup

                </h5>

            </div>

            <div class="card-body">

                <p class="text-muted mb-3">

                    Create a complete backup of your employee database. This will export all tables and data to a SQL file.

                </p>

                <form method="post">

                    <input type="hidden" name="action" value="create_backup">

                    <button type="submit" class="btn btn-primary w-100">

                        <i class="fas fa-database me-1"></i> Create Backup Now

                    </button>

                </form>

                <div class="mt-3 p-2 bg-light rounded">

                    <small class="text-muted">

                        <i class="fas fa-info-circle me-1"></i>

                        <strong>What's included:</strong> All employee records, appointments, trainings, leaves, service records, admins, calendar events, and activity logs.

                    </small>

                </div>

                <div class="mt-2 p-2 bg-success bg-opacity-10 border border-success border-opacity-25 rounded">

                    <small class="text-success">

                        <i class="fas fa-clock me-1"></i>

                        <strong>Automatic Backup:</strong> System automatically creates backups every Friday at 4:30 PM. See SETUP_AUTO_BACKUP.txt for details.

                    </small>

                </div>

            </div>

        </div>

    </div>

    

    <div class="col-md-6">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-header bg-white border-bottom">

                <h5 class="card-title mb-0">

                    <i class="fas fa-upload me-2 text-primary"></i>

                    Restore Backup

                </h5>

            </div>

            <div class="card-body">

                <p class="text-muted mb-3">

                    Restore your database from a previously created backup file. <strong class="text-danger">Warning: This will replace all current data!</strong>

                </p>

                <form method="post" enctype="multipart/form-data">

                    <input type="hidden" name="action" value="restore_backup">

                    <div class="mb-3">

                        <label for="backup_file" class="form-label">Select Backup File</label>

                        <input 

                            type="file" 

                            class="form-control" 

                            id="backup_file" 

                            name="backup_file" 

                            accept=".sql"

                            required

                        >

                        <small class="text-muted">Only .sql files are accepted</small>

                    </div>

                    <button type="submit" class="btn btn-warning w-100" onclick="return confirm('Are you sure you want to restore this backup? This will replace all current data!');">

                        <i class="fas fa-upload me-1"></i> Restore Backup

                    </button>

                </form>

            </div>

        </div>

    </div>

</div>



<!-- Existing Backups -->

<div class="card border-0 shadow-sm">

    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">

        <h5 class="card-title mb-0">

            <i class="fas fa-folder me-2 text-primary"></i>

            Existing Backups

        </h5>

        <span class="badge bg-primary"><?= count($backups) ?> backup<?= count($backups) === 1 ? '' : 's' ?></span>

    </div>

    <div class="card-body p-0">

        <?php if (empty($backups)): ?>

            <div class="text-center py-5">

                <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>

                <p class="text-muted mb-0">No backups found. Create your first backup above.</p>

            </div>

        <?php else: ?>

            <div class="table-responsive">

                <table class="employee-table">

                    <thead>

                    <tr>

                        <th>Filename</th>

                        <th class="text-center">Date Created</th>

                        <th class="text-center">Size</th>

                        <th class="text-center">Actions</th>

                    </tr>

                    </thead>

                    <tbody>

                    <?php foreach ($backups as $backup): ?>

                        <tr>

                            <td>

                                <i class="fas fa-file-code me-2 text-primary"></i>

                                <strong><?= htmlspecialchars($backup['filename']) ?></strong>

                            </td>

                            <td class="text-center">

                                <small class="text-muted"><?= htmlspecialchars($backup['date']) ?></small>

                            </td>

                            <td class="text-center">

                                <span class="badge bg-secondary">

                                    <?= number_format($backup['size'] / 1024, 2) ?> KB

                                </span>

                            </td>

                            <td class="text-center">

                                <div class="btn-group btn-group-sm" role="group">

                                    <a 

                                        href="download_backup.php?file=<?= urlencode($backup['filename']) ?>" 

                                        class="btn btn-outline-primary"

                                        title="Download"

                                    >

                                        <i class="fas fa-download"></i>

                                    </a>

                                    <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this backup?');">

                                        <input type="hidden" name="action" value="delete_backup">

                                        <input type="hidden" name="backup_file" value="<?= htmlspecialchars($backup['filename']) ?>">

                                        <button type="submit" class="btn btn-outline-danger" title="Delete">

                                            <i class="fas fa-trash"></i>

                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

    </div>

</div>



<?php

require_once 'footer.php';

?>