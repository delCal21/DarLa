<?php
require_once 'auth.php';
require_admin_login();
require_once 'header.php';
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
                    <div class="directory-stats">
                        <?php
                        require_once 'db.php';
                        $stmt = $pdo->query('SELECT COUNT(*) as total FROM employees WHERE (employment_status IS NULL OR employment_status NOT IN ("Retired", "Resigned"))');
                        $totalEmployees = $stmt->fetch()['total'];
                        ?>
                        <div class="stat-box-compact">
                            <div class="stat-number-compact"><?= number_format($totalEmployees) ?></div>
                            <div class="stat-label-compact">Total Employees</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
            <?php foreach (range('A', 'Z') as $letter): ?>
                <a class="letter-button" href="employees.php?letter=<?= $letter ?>">
                    <?= $letter ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
require_once 'footer.php';
?>

