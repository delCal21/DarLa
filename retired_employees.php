<?php
require_once 'auth.php';
require_admin_login();
require_once 'db.php';
require_once 'activity_logger.php';

$letter = isset($_GET['letter']) && preg_match('/^[A-Z]$/', $_GET['letter'])
    ? strtoupper($_GET['letter'])
    : 'A';

$successMessage = '';
$errorMessage = '';

// Check for success message from redirect
if (isset($_GET['success'])) {
    $successMessage = $_GET['success'];
}

$stmt = $pdo->prepare(
    'SELECT id, last_name, first_name, middle_name, employment_status
     FROM employees
     WHERE last_name LIKE :prefix
     AND employment_status IN ("RETIRED", "RESIGNED")
     ORDER BY last_name, first_name'
);
$stmt->execute([':prefix' => $letter . '%']);
$employees = $stmt->fetchAll();

require_once 'header.php';
?>

<!-- Breadcrumb -->
<ol class="breadcrumb mb-3">
    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="employee_directory.php">Employee List</a></li>
    <li class="breadcrumb-item active">Inactive Employee - Letter <?= htmlspecialchars($letter) ?></li>
</ol>

<!-- Professional Header Card -->
<div class="card border-0 shadow-sm mb-3 professional-header-card compact-header">
    <div class="card-body p-3">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center">
                    <div class="letter-avatar me-2">
                        <div class="avatar-circle letter-circle compact-letter" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%);">
                            <i class="fas fa-user-clock"></i>
                        </div>
                    </div>
                    <div>
                        <h1 class="h4 mb-0 fw-bold text-dark">Inactive Employee - Letter <?= htmlspecialchars($letter) ?></h1>
                        <p class="text-muted mb-0 small">
                            <i class="fas fa-users me-1"></i>
                            Inactive employees (Retired/Resigned) with last name starting with "<strong><?= htmlspecialchars($letter) ?></strong>"
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end mt-2 mt-md-0">
                <div class="d-flex flex-column flex-md-row gap-2 justify-content-md-end">
                    <a href="employee_directory.php" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Back to Directory
                    </a>
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

<!-- Letter Navigation -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="fas fa-list-alpha me-2 text-primary"></i>Browse by Last Name
        </h5>
        <span class="text-muted small">A – Z quick navigation</span>
    </div>
    <div class="card-body">
        <div class="letter-grid">
            <?php foreach (range('A', 'Z') as $l): ?>
                <a class="letter-button" href="retired_employees.php?letter=<?= $l ?>">
                    <?= $l ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php if (empty($employees)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-user-clock fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No Inactive Employees Found</h5>
            <p class="text-muted mb-0">
                No inactive employees (Retired/Resigned) found whose last name starts with "<strong><?= htmlspecialchars($letter) ?></strong>".
            </p>
            <a href="employee_directory.php" class="btn btn-primary mt-3">
                <i class="fas fa-arrow-left me-1"></i> Back to Employee Directory
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-2">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="fas fa-list me-2 text-primary"></i>
                    Inactive Employee Records
                </h6>
                <span class="badge bg-secondary">
                    <i class="fas fa-users me-1"></i>
                    <?= count($employees) ?> record<?= count($employees) === 1 ? '' : 's' ?>
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="employee-table">
                    <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 40%;">
                            <i class="fas fa-user me-1"></i> Full Name
                        </th>
                        <th style="width: 25%;">
                            <i class="fas fa-id-card me-1"></i> Last Name
                        </th>
                        <th style="width: 30%;" class="text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $rowNumber = 1; ?>
                    <?php foreach ($employees as $emp): ?>
                        <?php
                        $fullName = trim($emp['last_name'] . ', ' . $emp['first_name'] . ($emp['middle_name'] ? ' ' . $emp['middle_name'] : ''));
                        ?>
                        <tr>
                            <td class="text-center text-muted" style="background-color: white;"><?= $rowNumber++ ?></td>
                            <td style="background-color: white;">
                                <strong><?= htmlspecialchars($fullName) ?></strong>
                            </td>
                            <td class="text-muted" style="background-color: white;"><?= htmlspecialchars($emp['last_name'] ?? '') ?></td>
                            <td class="text-center" style="background-color: white;">
                                <a href="employee.php?id=<?= (int)$emp['id'] ?>" class="btn btn-sm btn-outline-primary" style="font-size: 0.8rem; padding: 0.25rem 0.5rem;" title="View">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
// Ensure letter buttons navigate to new page and scroll to top
document.addEventListener('DOMContentLoaded', function() {
    // Scroll to top when page loads
    window.scrollTo({ top: 0, behavior: 'instant' });
    
    // Ensure all letter buttons navigate properly (full page navigation)
    document.querySelectorAll('.letter-button').forEach(function(button) {
        button.addEventListener('click', function(e) {
            // Allow default navigation behavior - full page reload
            // This ensures the page navigates to the new URL
            const href = this.getAttribute('href');
            if (href) {
                window.location.href = href;
            }
        });
    });
});
</script>

<?php
require_once 'footer.php';
?>

