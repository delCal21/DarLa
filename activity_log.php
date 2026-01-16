<?php
require_once 'auth.php';
require_admin_login();
require_once 'db.php';

// Check if activity_logs table exists, create if not
try {
    $pdo->query("SELECT 1 FROM activity_logs LIMIT 1");
} catch (PDOException $e) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS activity_logs (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id VARCHAR(50) NOT NULL,
            action_type VARCHAR(100) NOT NULL,
            action_description TEXT NOT NULL,
            table_name VARCHAR(100) DEFAULT NULL,
            record_id INT UNSIGNED DEFAULT NULL,
            user_agent TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_action_type (action_type),
            INDEX idx_created_at (created_at),
            INDEX idx_table_record (table_name, record_id)
        )
    ");
}

// Auto-cleanup: Delete activity logs from previous days (keep only today's logs)
// This runs automatically when the activity log page is accessed
try {
    // Check if cleanup was already done today by checking if there are any logs from previous days
    $checkStmt = $pdo->query("
        SELECT COUNT(*) as old_count
        FROM activity_logs
        WHERE DATE(created_at) < CURDATE()
    ");
    $oldCount = $checkStmt->fetchColumn();

    // If there are old logs, delete them
    if ($oldCount > 0) {
        $deleteStmt = $pdo->prepare("
            DELETE FROM activity_logs
            WHERE DATE(created_at) < CURDATE()
        ");
        $deleteStmt->execute();
        // Note: We don't log this cleanup to avoid infinite loops
    }
} catch (PDOException $e) {
    // Silently fail cleanup if there's an error (don't break the page)
    error_log("Activity log cleanup error: " . $e->getMessage());
}

// Get filter parameters
$filterType = $_GET['type'] ?? 'all';
$filterDate = $_GET['date'] ?? '';
$searchQuery = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 50;
$offset = ($page - 1) * $perPage;

// Build query
$whereConditions = [];
$params = [];

if ($filterType !== 'all') {
    $whereConditions[] = 'action_type = :type';
    $params[':type'] = $filterType;
}

if (!empty($filterDate)) {
    $whereConditions[] = 'DATE(created_at) = :date';
    $params[':date'] = $filterDate;
}

if (!empty($searchQuery)) {
    $whereConditions[] = '(action_description LIKE :search OR user_id LIKE :search)';
    $params[':search'] = '%' . $searchQuery . '%';
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Get total count
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM activity_logs $whereClause");
foreach ($params as $key => $value) {
    $countStmt->bindValue($key, $value);
}
$countStmt->execute();
$totalRecords = $countStmt->fetchColumn();
$totalPages = ceil($totalRecords / $perPage);

// Get activity logs
$stmt = $pdo->prepare("
    SELECT * FROM activity_logs
    $whereClause
    ORDER BY created_at DESC
    LIMIT :limit OFFSET :offset
");
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll();

// Get action type counts for statistics
$statsStmt = $pdo->query("
    SELECT
        action_type,
        COUNT(*) as count
    FROM activity_logs
    GROUP BY action_type
    ORDER BY count DESC
");
$stats = $statsStmt->fetchAll();

// Get recent activity summary
$recentStmt = $pdo->query("
    SELECT
        DATE(created_at) as date,
        COUNT(*) as count
    FROM activity_logs
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date DESC
");
$recentActivity = $recentStmt->fetchAll();

require_once 'header.php';
?>

<!-- Breadcrumb -->
<ol class="breadcrumb mb-3">
    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
    <li class="breadcrumb-item active">Activity Log</li>
</ol>

<!-- Professional Header Card -->
<div class="card border-0 shadow-sm mb-3 professional-header-card compact-header">
    <div class="card-body p-3">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center">
                    <div class="directory-avatar me-2">
                        <div class="avatar-circle compact-avatar">
                            <i class="fas fa-history"></i>
                        </div>
                    </div>
                    <div>
                        <h1 class="h4 mb-0 fw-bold text-dark">Activity Log</h1>
                        <p class="text-muted mb-0 small">
                            <i class="fas fa-info-circle me-1"></i>
                            Track all system activities and user actions
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end mt-2 mt-md-0">
                <div class="stat-box-compact">
                    <div class="stat-number-compact"><?= number_format($totalRecords) ?></div>
                    <div class="stat-label-compact">Total Logs</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-3 mb-4">
    <?php foreach (array_slice($stats, 0, 4) as $stat): ?>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="h4 mb-1 text-primary"><?= number_format($stat['count']) ?></div>
                    <div class="small text-muted text-capitalize"><?= htmlspecialchars(str_replace('_', ' ', $stat['action_type'])) ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Filters and Search -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="get" class="row g-3">
            <div class="col-md-3">
                <label for="type" class="form-label small">Action Type</label>
                <select class="form-select form-select-sm" id="type" name="type">
                    <option value="all" <?= $filterType === 'all' ? 'selected' : '' ?>>All Types</option>
                    <option value="login" <?= $filterType === 'login' ? 'selected' : '' ?>>Login</option>
                    <option value="logout" <?= $filterType === 'logout' ? 'selected' : '' ?>>Logout</option>
                    <option value="employee_create" <?= $filterType === 'employee_create' ? 'selected' : '' ?>>Employee Created</option>
                    <option value="employee_update" <?= $filterType === 'employee_update' ? 'selected' : '' ?>>Employee Updated</option>
                    <option value="employee_delete" <?= $filterType === 'employee_delete' ? 'selected' : '' ?>>Employee Deleted</option>
                    <option value="backup_create" <?= $filterType === 'backup_create' ? 'selected' : '' ?>>Backup Created</option>
                    <option value="backup_restore" <?= $filterType === 'backup_restore' ? 'selected' : '' ?>>Backup Restored</option>
                    <option value="backup_delete" <?= $filterType === 'backup_delete' ? 'selected' : '' ?>>Backup Deleted</option>
                    <option value="event_create" <?= $filterType === 'event_create' ? 'selected' : '' ?>>Event Created</option>
                    <option value="event_update" <?= $filterType === 'event_update' ? 'selected' : '' ?>>Event Updated</option>
                    <option value="event_delete" <?= $filterType === 'event_delete' ? 'selected' : '' ?>>Event Deleted</option>
                    <option value="profile_update" <?= $filterType === 'profile_update' ? 'selected' : '' ?>>Profile Updated</option>
                    <option value="password_change" <?= $filterType === 'password_change' ? 'selected' : '' ?>>Password Changed</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="date" class="form-label small">Date</label>
                <input type="date" class="form-control form-control-sm" id="date" name="date" value="<?= htmlspecialchars($filterDate) ?>">
            </div>
            <div class="col-md-4">
                <label for="search" class="form-label small">Search</label>
                <input type="text" class="form-control form-control-sm" id="search" name="search"
                       value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Search activities...">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Activity Log Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="fas fa-list me-2 text-primary"></i>
            Activity Logs
        </h5>
        <span class="badge bg-primary"><?= number_format($totalRecords) ?> records</span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($logs)): ?>
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                <p class="text-muted mb-0">No activity logs found.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="employee-table">
                    <thead>
                    <tr>
                        <th style="width: 150px;">Date & Time</th>
                        <th style="width: 120px;">User</th>
                        <th style="width: 150px;">Action Type</th>
                        <th>Description</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td>
                                <div class="small">
                                    <div class="fw-bold"><?= date('M d, Y', strtotime($log['created_at'])) ?></div>
                                    <div class="text-muted"><?= date('h:i A', strtotime($log['created_at'])) ?></div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?= htmlspecialchars($log['user_id']) ?></span>
                            </td>
                            <td>
                                <?php
                                $typeColors = [
                                    'login' => 'success',
                                    'logout' => 'secondary',
                                    'employee_create' => 'primary',
                                    'employee_update' => 'info',
                                    'employee_delete' => 'danger',
                                    'backup_create' => 'success',
                                    'backup_restore' => 'warning',
                                    'backup_delete' => 'danger',
                                    'event_create' => 'primary',
                                    'event_update' => 'info',
                                    'event_delete' => 'danger',
                                    'profile_update' => 'info',
                                    'password_change' => 'warning'
                                ];
                                $color = $typeColors[$log['action_type']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $color ?> text-capitalize">
                                    <?= htmlspecialchars(str_replace('_', ' ', $log['action_type'])) ?>
                                </span>
                            </td>
                            <td>
                                <div class="small">
                                    <?= htmlspecialchars($log['action_description']) ?>
                                    <?php if ($log['table_name'] && $log['record_id']): ?>
                                        <span class="text-muted">
                                            (<?= htmlspecialchars($log['table_name']) ?> #<?= $log['record_id'] ?>)
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="card-footer bg-white border-top">
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0 justify-content-center">
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">Previous</a>
                            </li>
                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                    <div class="text-center small text-muted mt-2">
                        Showing <?= number_format($offset + 1) ?> to <?= number_format(min($offset + $perPage, $totalRecords)) ?> of <?= number_format($totalRecords) ?> entries
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php
require_once 'footer.php';
?>

