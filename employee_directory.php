<?php
require_once 'auth.php';
require_admin_login();
require_once 'db.php';
require_once 'header.php';

// Get filter parameters
$filterStatus = isset($_GET['status']) ? trim($_GET['status']) : '';
$filterOffice = isset($_GET['office']) ? trim($_GET['office']) : '';

// Get unique employment statuses from database
$defaultStatuses = ['PERMANENT', 'COS', 'SPLIT', 'CTI', 'PA', 'RESIGNED', 'RETIRED', 'OTHERS'];
$statusOptions = [];
$stmt = $pdo->query("SELECT DISTINCT employment_status FROM employees WHERE employment_status IS NOT NULL AND TRIM(employment_status) != '' ORDER BY employment_status ASC");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $statusVal = trim((string)($row['employment_status'] ?? ''));
    if ($statusVal !== '') {
        $statusOptions[] = $statusVal;
    }
}
$statusOptions = array_values(array_unique(array_merge($defaultStatuses, $statusOptions)));
natsort($statusOptions);
$statusOptions = array_values($statusOptions);

// Get unique offices/departments from database
$defaultOffices = ['LTS', 'LEGAL', 'DARAB', 'PBDD', 'OPARPO', 'STOD'];
$officeOptions = [];
$stmt = $pdo->query("SELECT DISTINCT office FROM employees WHERE office IS NOT NULL AND TRIM(office) != '' ORDER BY office ASC");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $officeVal = trim((string)($row['office'] ?? ''));
    if ($officeVal !== '') {
        $officeOptions[] = $officeVal;
    }
}
$officeOptions = array_values(array_unique(array_merge($defaultOffices, $officeOptions)));
natsort($officeOptions);
$officeOptions = array_values($officeOptions);

// Build filter query for total count
$countWhere = ['(employment_status IS NULL OR employment_status NOT IN ("RETIRED", "RESIGNED"))'];
$countParams = [];

if ($filterStatus !== '') {
    if ($filterStatus === 'Not Set') {
        $countWhere[] = '(employment_status IS NULL OR TRIM(employment_status) = "")';
    } else {
        $countWhere[] = 'employment_status = :filter_status';
        $countParams[':filter_status'] = $filterStatus;
    }
}

if ($filterOffice !== '') {
    if ($filterOffice === 'Not Set') {
        $countWhere[] = '(office IS NULL OR TRIM(office) = "")';
    } else {
        $countWhere[] = 'office = :filter_office';
        $countParams[':filter_office'] = $filterOffice;
    }
}

$countSql = 'SELECT COUNT(*) as total FROM employees WHERE ' . implode(' AND ', $countWhere);
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($countParams);
$totalEmployees = $countStmt->fetch()['total'];
?>

<!-- Breadcrumb -->
<ol class="breadcrumb mb-3">
    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
    <li class="breadcrumb-item active">Employee Directory</li>
</ol>

<!-- Professional Header Card -->
<div class="card border-0 shadow-sm mb-3 professional-header-card compact-header">
    <div class="card-body p-2">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center">
                    <div class="directory-avatar me-2">
                        <div class="avatar-circle compact-avatar">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div>
                        <h1 class="h5 mb-0 fw-bold text-dark">Employee Directory</h1>
                        <p class="text-muted mb-0" style="font-size: 0.8rem;">
                            <i class="fas fa-info-circle me-1"></i>
                            Browse and manage employee records by selecting a letter below
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end mt-2 mt-md-0">
                <div class="d-flex flex-column flex-md-row gap-2 justify-content-md-end align-items-md-center">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white border-bottom">
        <h5 class="card-title mb-0">
            <i class="fas fa-filter me-2 text-primary"></i>Filter Employees
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" action="employee_directory.php" id="filterForm">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="filter_status" class="form-label">
                        <i class="fas fa-briefcase me-1"></i>Employment Status
                    </label>
                    <select class="form-select form-select-sm" id="filter_status" name="status">
                        <option value="">All Statuses</option>
                        <option value="Not Set" <?= $filterStatus === 'Not Set' ? 'selected' : '' ?>>Not Set</option>
                        <?php foreach ($statusOptions as $status): ?>
                            <option value="<?= htmlspecialchars($status) ?>" <?= $filterStatus === $status ? 'selected' : '' ?>>
                                <?= htmlspecialchars($status) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filter_office" class="form-label">
                        <i class="fas fa-building me-1"></i> Division / Department
                    </label>
                    <select class="form-select form-select-sm" id="filter_office" name="office">
                        <option value="">All Division</option>
                        <option value="Not Set" <?= $filterOffice === 'Not Set' ? 'selected' : '' ?>>Not Set</option>
                        <?php foreach ($officeOptions as $office): ?>
                            <option value="<?= htmlspecialchars($office) ?>" <?= $filterOffice === $office ? 'selected' : '' ?>>
                                <?= htmlspecialchars($office) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="btn-group w-100" role="group">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-search me-1"></i>Apply Filters
                        </button>
                        <a href="employee_directory.php" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-times me-1"></i>Clear
                        </a>
                    </div>
                </div>
            </div>
            <?php if ($filterStatus !== '' || $filterOffice !== ''): ?>
                <div class="mt-3">
                    <div class="alert alert-info mb-0 py-2">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Active Filters:</strong>
                        <?php if ($filterStatus !== ''): ?>
                            <span class="badge bg-primary me-2">Status: <?= htmlspecialchars($filterStatus) ?></span>
                        <?php endif; ?>
                        <?php if ($filterOffice !== ''): ?>
                            <span class="badge bg-primary me-2">Department: <?= htmlspecialchars($filterOffice) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Letter Navigation -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="fas fa-list-alpha me-2 text-primary"></i>Browse by Last Name
        </h5>
        <span class="text-muted small">A – Z quick navigation</span>
    </div>
    <div class="card-body">
        <div class="letter-grid">
            <?php 
            // Build query string for filters
            $filterQuery = '';
            if ($filterStatus !== '') {
                $filterQuery .= '&status=' . urlencode($filterStatus);
            }
            if ($filterOffice !== '') {
                $filterQuery .= '&office=' . urlencode($filterOffice);
            }
            
            foreach (range('A', 'Z') as $letter): 
                $letterUrl = 'employees.php?letter=' . $letter . $filterQuery;
            ?>
                <a class="letter-button" href="<?= htmlspecialchars($letterUrl) ?>">
                    <?= $letter ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
require_once 'footer.php';
?>

