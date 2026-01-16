<?php
require_once 'auth.php';
require_admin_login();

// Include database connection
require_once 'db.php';

// Initialize filters
$selectedStatus = isset($_GET['status']) ? $_GET['status'] : '';
$selectedOffice = isset($_GET['office']) ? $_GET['office'] : '';
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get unique employment statuses from the database and merge with predefined categories
$defaultStatuses = ['PERMANENT', 'COS', 'SPLIT', 'CTI', 'PA', 'RESIGNED', 'RETIRED', 'OTHERS', 'Not Set'];
$statusOptions = [];
$stmt = $pdo->query("SELECT DISTINCT employment_status FROM employees");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $statusVal = trim((string)($row['employment_status'] ?? ''));
    $statusOptions[] = $statusVal === '' ? 'Not Set' : $statusVal;
}
$statusOptions = array_values(array_unique(array_merge($defaultStatuses, $statusOptions)));
natsort($statusOptions);
$statusOptions = array_values($statusOptions);

// Get unique offices/departments from the database and merge with predefined departments
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

// Get statistics by employment status
$statsStmt = $pdo->query("
    SELECT 
        COALESCE(NULLIF(TRIM(employment_status), ''), 'Not Set') AS status_label,
        COUNT(*) as count
    FROM employees
    GROUP BY status_label
    ORDER BY count DESC
");
$statusStats = $statsStmt->fetchAll();


// Build the SQL query with filters
$sql = "SELECT e.*,
(SELECT position FROM appointments WHERE employee_id = e.id ORDER BY appointment_date DESC LIMIT 1) as position,
(SELECT item_number FROM appointments WHERE employee_id = e.id ORDER BY appointment_date DESC LIMIT 1) as department,
(SELECT MIN(appointment_date) FROM appointments WHERE employee_id = e.id) as date_hired,
COALESCE(NULLIF(TRIM(e.office), ''), 'Not Set') as office_display
FROM employees e WHERE 1=1";
$params = [];

if (!empty($selectedStatus)) {
    if (strcasecmp($selectedStatus, 'Not Set') === 0) {
        $sql .= " AND (employment_status IS NULL OR TRIM(employment_status) = '')";
    } else {
        $sql .= " AND employment_status = ?";
        $params[] = $selectedStatus;
    }
}

if (!empty($selectedOffice)) {
    if (strcasecmp($selectedOffice, 'Not Set') === 0) {
        $sql .= " AND (e.office IS NULL OR TRIM(e.office) = '')";
    } else {
        $sql .= " AND e.office = ?";
        $params[] = $selectedOffice;
    }
}

if (!empty($searchTerm)) {
    $sql .= " AND (employee_number LIKE ? OR first_name LIKE ? OR middle_name LIKE ? OR last_name LIKE ? OR position LIKE ? OR department LIKE ? OR e.office LIKE ?)";
    $searchPattern = "%{$searchTerm}%";
    for ($i = 0; $i < 7; $i++) $params[] = $searchPattern;
}

$sql .= " ORDER BY last_name ASC, first_name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Export to CSV
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: application/csv');
    header('Content-Disposition: attachment; filename="employee_summary_report_' . date('Ymd_His') . '.csv"');
    header('Pragma: no-cache');

    $output = fopen('php://output', 'w');

    // Add BOM for UTF-8 to handle special characters in Excel
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Headers
    fputcsv($output, [
        'Employee ID',
        'Last Name',
        'First Name',
        'Middle Name',
        'Birthdate',
        'Address',
        'Contact Number',
        'Email',
        'Civil Status',
        'Position',
        'Department',
        'Office / Department',
        'Employment Status',
        'Date Hired',
        'TIN Number',
        'SSS Number',
        'GSIS Number',
        'Pag-IBIG Number',
        'PhilHealth Number'
    ]);

    // Data rows
    foreach ($employees as $employee) {
        fputcsv($output, [
            $employee['employee_number'],
            $employee['last_name'],
            $employee['first_name'],
            $employee['middle_name'],
            $employee['birthdate'],
            $employee['home_address'],
            $employee['contact_no'],
            $employee['email'],
            $employee['civil_status'],
            $employee['position'],
            $employee['department'],
            $employee['office'] ?? 'Not Set',
            $employee['employment_status'],
            $employee['date_hired'],
            $employee['tin_number'],
            $employee['sss_number'],
            $employee['gsis_number'],
            $employee['pagibig_number'],
            $employee['philhealth_number']
        ]);
    }

    fclose($output);
    exit;
}

// Export to PDF
if (isset($_GET['export']) && $_GET['export'] == 'pdf') {
    require_once 'vendor/autoload.php';

    $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetTitle('Employee Summary Report');
    $pdf->SetHeaderData('', 0, 'Employee Summary Report', '');
    $pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->AddPage();

    // Add filter information to PDF
    $filterInfo = "Filters Applied:\n";
    if (!empty($selectedStatus)) {
        $filterInfo .= "- Employment Status: {$selectedStatus}\n";
    } else {
        $filterInfo .= "- Employment Status: All\n";
    }
    if (!empty($selectedOffice)) {
        $filterInfo .= "- Office / Department: {$selectedOffice}\n";
    } else {
        $filterInfo .= "- Office / Department: All\n";
    }
    if (!empty($searchTerm)) {
        $filterInfo .= "- Search Term: {$searchTerm}";
    } else {
        $filterInfo .= "- Search Term: None";
    }

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, $filterInfo, 0, 1, 'L');
    $pdf->Ln(5);

    // Table header - using smaller font for more columns
    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetFillColor(200, 200, 200);
    $pdf->Cell(18, 10, 'ID', 1, 0, 'C', true);
    $pdf->Cell(22, 10, 'Last Name', 1, 0, 'C', true);
    $pdf->Cell(22, 10, 'First Name', 1, 0, 'C', true);
    $pdf->Cell(18, 10, 'M. Name', 1, 0, 'C', true);
    $pdf->Cell(18, 10, 'Birthdate', 1, 0, 'C', true);
    $pdf->Cell(30, 10, 'Address', 1, 0, 'C', true);
    $pdf->Cell(22, 10, 'Contact', 1, 0, 'C', true);
    $pdf->Cell(30, 10, 'Email', 1, 0, 'C', true);
    $pdf->Cell(18, 10, 'Civil Status', 1, 0, 'C', true);
    $pdf->Cell(25, 10, 'Position', 1, 0, 'C', true);
    $pdf->Cell(22, 10, 'Department', 1, 0, 'C', true);
    $pdf->Cell(18, 10, 'Office', 1, 0, 'C', true);
    $pdf->Cell(18, 10, 'Status', 1, 0, 'C', true);
    $pdf->Cell(18, 10, 'Date Hired', 1, 0, 'C', true);
    $pdf->Cell(20, 10, 'TIN', 1, 0, 'C', true);
    $pdf->Cell(20, 10, 'SSS', 1, 0, 'C', true);
    $pdf->Cell(20, 10, 'GSIS', 1, 0, 'C', true);
    $pdf->Cell(20, 10, 'Pag-ibig', 1, 0, 'C', true);
    $pdf->Cell(20, 10, 'PhilHealth', 1, 0, 'C', true);
    $pdf->Ln();

    // Table data
    $pdf->SetFont('helvetica', '', 6);
    foreach ($employees as $employee) {
        $pdf->Cell(18, 8, substr($employee['employee_number'] ?? 'N/A', 0, 10), 1);
        $pdf->Cell(22, 8, substr($employee['last_name'], 0, 12), 1);
        $pdf->Cell(22, 8, substr($employee['first_name'], 0, 12), 1);
        $pdf->Cell(18, 8, substr($employee['middle_name'] ?? '', 0, 8), 1);
        $pdf->Cell(18, 8, $employee['birthdate'] ? date('m/d/Y', strtotime($employee['birthdate'])) : 'N/A', 1);
        $pdf->Cell(30, 8, substr($employee['home_address'] ?? 'N/A', 0, 18), 1);
        $pdf->Cell(22, 8, substr($employee['contact_no'] ?? 'N/A', 0, 10), 1);
        $pdf->Cell(30, 8, substr($employee['email'] ?? 'N/A', 0, 18), 1);
        $pdf->Cell(18, 8, substr($employee['civil_status'] ?? 'N/A', 0, 7), 1);
        $pdf->Cell(25, 8, substr($employee['position'] ?? 'N/A', 0, 13), 1);
        $pdf->Cell(22, 8, substr($employee['department'] ?? 'N/A', 0, 10), 1);
        $pdf->Cell(18, 8, substr($employee['office'] ?? 'N/A', 0, 7), 1);
        $pdf->Cell(18, 8, substr($employee['employment_status'] ?? 'N/A', 0, 7), 1);
        $pdf->Cell(18, 8, $employee['date_hired'] ? date('m/d/Y', strtotime($employee['date_hired'])) : 'N/A', 1);
        $pdf->Cell(20, 8, substr($employee['tin_number'] ?? 'N/A', 0, 12), 1);
        $pdf->Cell(20, 8, substr($employee['sss_number'] ?? 'N/A', 0, 12), 1);
        $pdf->Cell(20, 8, substr($employee['gsis_number'] ?? 'N/A', 0, 12), 1);
        $pdf->Cell(20, 8, substr($employee['pagibig_number'] ?? 'N/A', 0, 12), 1);
        $pdf->Cell(20, 8, substr($employee['philhealth_number'] ?? 'N/A', 0, 12), 1);
        $pdf->Ln();
    }

    $pdf->Output('employee_summary_report_' . date('Ymd_His') . '.pdf', 'D');
    exit;
}

require_once 'header.php';
?>
<link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">

<!-- Breadcrumb -->
<ol class="breadcrumb mb-3">
    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
    <li class="breadcrumb-item active">Employee Summary Report</li>
</ol>

<!-- Professional Header Card -->
<div class="card border-0 shadow-sm mb-3 professional-header-card compact-header">
    <div class="card-body p-3">
        <div class="d-flex align-items-center">
            <div class="directory-avatar me-2">
                <div class="avatar-circle compact-avatar">
                    <i class="fas fa-chart-bar"></i>
                </div>
            </div>
            <div>
                <h1 class="h4 mb-0 fw-bold text-dark">Employee Summary Report</h1>
                <p class="text-muted mb-0 small">
                    <i class="fas fa-info-circle me-1"></i>
                    Comprehensive overview of all employees with filtering options
                    <?php if (!empty($selectedStatus)): ?>
                        <span class="badge bg-info ms-2">Status: <?= htmlspecialchars($selectedStatus) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($selectedOffice)): ?>
                        <span class="badge bg-info ms-2">Office: <?= htmlspecialchars($selectedOffice) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($searchTerm)): ?>
                        <span class="badge bg-secondary ms-2">Search: "<?= htmlspecialchars($searchTerm) ?>"</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Export Options -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white border-bottom">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-filter me-2 text-primary"></i>
                Filters & Export
            </h5>
            <div>
                <a class="btn btn-sm btn-success mx-1" href="<?= $_SERVER['PHP_SELF'] ?>?export=csv&status=<?= urlencode($selectedStatus) ?>&office=<?= urlencode($selectedOffice) ?>&search=<?= urlencode($searchTerm) ?>">
                    <i class="fas fa-file-csv me-1"></i>Export CSV
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <form method="get" class="row g-3">
            <div class="col-md-3">
                <label for="statusFilter" class="form-label small">Employment Status</label>
                <select name="status" id="statusFilter" class="form-select form-select-sm" style="margin-left: 15px; margin-right: 15px;">
                    <option value="">All Statuses</option>
                    <?php foreach ($statusOptions as $status): ?>
                        <option value="<?= htmlspecialchars($status) ?>"
                            <?= $selectedStatus === $status ? 'selected' : '' ?>>
                            <?= htmlspecialchars($status) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="officeFilter" class="form-label small">Division / Department</label>
                <select name="office" id="officeFilter" class="form-select form-select-sm" style="margin-left: 15px; margin-right: 15px;">
                    <option value="">All Division</option>
                    <option value="Not Set" <?= $selectedOffice === 'Not Set' ? 'selected' : '' ?>>Not Set</option>
                    <?php foreach ($officeOptions as $office): ?>
                        <option value="<?= htmlspecialchars($office) ?>"
                            <?= $selectedOffice === $office ? 'selected' : '' ?>>
                            <?= htmlspecialchars($office) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="searchInput" class="form-label small">Search</label>
                <input type="text" class="form-control form-control-sm" id="searchInput" name="search"
                       placeholder="Search by ID, name, position, department, or office..."
                       value="<?= htmlspecialchars($searchTerm) ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-sm w-100" style="margin-left: 15px; margin-right: 15px;">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
            </div>
        </form>
        <?php if (!empty($selectedStatus) || !empty($selectedOffice) || !empty($searchTerm)): ?>
            <div class="mt-3">
                <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-times me-1"></i> Clear Filters
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Report Results -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="fas fa-table me-2 text-primary"></i>
            Employee Records
        </h5>
        <span class="badge bg-primary"><?= count($employees) ?> record<?= count($employees) === 1 ? '' : 's' ?></span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($employees)): ?>
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                <p class="text-muted mb-0">No employees found matching the criteria.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table id="employeeTable" class="employee-table" style="font-size: 0.85rem; width:100%;">
                    <thead>
                    <tr>
                        <th><i class="fas fa-id-card me-1"></i> Employee ID</th>
                        <th><i class="fas fa-user me-1"></i> Last Name</th>
                        <th><i class="fas fa-user me-1"></i> First Name</th>
                        <th><i class="fas fa-user me-1"></i> Middle Name</th>
                        <th><i class="fas fa-calendar-alt me-1"></i> Birthdate</th>
                        <th><i class="fas fa-map-marker-alt me-1"></i> Address</th>
                        <th><i class="fas fa-phone me-1"></i> Contact</th>
                        <th><i class="fas fa-envelope me-1"></i> Email</th>
                        <th><i class="fas fa-heart me-1"></i> Civil Status</th>
                        <th><i class="fas fa-building me-1"></i> Division</th>
                        <th><i class="fas fa-briefcase me-1"></i> Status</th>
                        <th><i class="fas fa-file-invoice me-1"></i> TIN</th>
                        <th><i class="fas fa-file-invoice me-1"></i> SSS</th>
                        <th><i class="fas fa-file-invoice me-1"></i> GSIS</th>
                        <th><i class="fas fa-file-invoice me-1"></i> Pag-ibig</th>
                        <th><i class="fas fa-file-invoice me-1"></i> PhilHealth</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($employees as $employee): ?>
                        <tr>
                            <td class="text-nowrap fw-bold"><?= htmlspecialchars($employee['employee_number'] ?? 'N/A') ?></td>
                            <td class="fw-semibold"><?= htmlspecialchars($employee['last_name']) ?></td>
                            <td><?= htmlspecialchars($employee['first_name']) ?></td>
                            <td><?= htmlspecialchars($employee['middle_name'] ?? '') ?></td>
                            <td class="text-nowrap"><?= $employee['birthdate'] ? date('M d, Y', strtotime($employee['birthdate'])) : 'N/A' ?></td>
                            <td class="text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($employee['home_address'] ?? '') ?>">
                                <?= htmlspecialchars($employee['home_address'] ?? 'N/A') ?>
                            </td>
                            <td class="text-nowrap"><?= htmlspecialchars($employee['contact_no'] ?? 'N/A') ?></td>
                            <td class="text-truncate" style="max-width: 150px;" title="<?= htmlspecialchars($employee['email'] ?? '') ?>">
                                <?= htmlspecialchars($employee['email'] ?? 'N/A') ?>
                            </td>
                            <td class="text-nowrap"><?= htmlspecialchars($employee['civil_status'] ?? 'N/A') ?></td>
                            <td class="text-nowrap">
                                <span class="badge bg-info" style="font-size: 0.7rem; padding: 0.3em 0.6em; font-weight: 600;">
                                    <?= htmlspecialchars($employee['office'] ?? 'Not Set') ?>
                                </span>
                            </td>
                            <td class="text-nowrap">
                                <span class="badge
                                    <?php
                                        switch(strtoupper($employee['employment_status'] ?? '')) {
                                            case 'PERMANENT':
                                                echo 'bg-success';
                                                break;
                                            case 'COS':
                                            case 'CTI':
                                            case 'SPLIT':
                                            case 'PA':
                                                echo 'bg-info';
                                                break;
                                            case 'RESIGNED':
                                            case 'RETIRED':
                                                echo 'bg-danger';
                                                break;
                                            default:
                                                echo 'bg-secondary';
                                        }
                                    ?>
                                " style="font-size: 0.7rem; padding: 0.3em 0.6em; font-weight: 600;">
                                    <?= htmlspecialchars($employee['employment_status'] ?? 'Not Set') ?>
                                </span>
                            </td>
                            <td class="text-nowrap small"><?= htmlspecialchars($employee['tin_number'] ?? 'N/A') ?></td>
                            <td class="text-nowrap small"><?= htmlspecialchars($employee['sss_number'] ?? 'N/A') ?></td>
                            <td class="text-nowrap small"><?= htmlspecialchars($employee['gsis_number'] ?? 'N/A') ?></td>
                            <td class="text-nowrap small"><?= htmlspecialchars($employee['pagibig_number'] ?? 'N/A') ?></td>
                            <td class="text-nowrap small"><?= htmlspecialchars($employee['philhealth_number'] ?? 'N/A') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    /* Allow page scrolling */
    html, body {
        overflow: auto;
    }
    
    #layoutSidenav {
        min-height: 100vh;
    }
    
    #layoutSidenav_content {
        min-height: 100vh;
    }
    
    #layoutSidenav_content > main {
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
    }
    
    #layoutSidenav_content > main > .container-fluid {
        padding: 0 20px 20px 20px !important;
        margin: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }
    
    /* Override Bootstrap container-fluid padding for this page only */
    #layoutSidenav_content > main .container-fluid {
        padding-left: 20px !important;
        padding-right: 20px !important;
        padding-top: 0 !important;
        padding-bottom: 20px !important;
    }
    
    /* Make table card extend to edges with margins */
    .card.border-0.shadow-sm {
        margin: 0 !important;
        border-radius: 0.375rem !important;
        width: 100% !important;
        max-width: 100% !important;
    }
    
    /* Remove all padding from card body */
    .card.border-0.shadow-sm .card-body {
        padding: 0 !important;
        margin: 0 !important;
    }
    
    /* Remove padding from table-responsive */
    .card.border-0.shadow-sm .table-responsive {
        padding: 0 !important;
        margin: 0 !important;
        overflow-x: auto;
    }
    
    /* Professional table styling */
    .employee-table {
        font-size: 0.875rem;
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        background: #ffffff;
    }
    
    .employee-table thead {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .employee-table thead th {
        background: transparent !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.8px;
        padding: 1rem 0.75rem !important;
        border: none !important;
        border-right: 1px solid rgba(255,255,255,0.2) !important;
        position: sticky;
        top: 0;
        z-index: 10;
        white-space: nowrap;
        text-align: left;
    }
    
    .employee-table thead th:last-child {
        border-right: none !important;
    }
    
    .employee-table thead th:first-child {
        padding-left: 1rem !important;
    }
    
    .employee-table tbody {
        background: #ffffff;
    }
    
    .employee-table tbody td {
        padding: 0.875rem 0.75rem !important;
        vertical-align: middle;
        border-bottom: 1px solid #e9ecef !important;
        border-right: 1px solid #f0f0f0 !important;
        color: #495057;
        font-size: 0.85rem;
        transition: all 0.2s ease;
    }
    
    .employee-table tbody td:first-child {
        padding-left: 1rem !important;
    }
    
    .employee-table tbody td:last-child {
        border-right: none !important;
    }
    
    .employee-table tbody tr {
        transition: all 0.2s ease;
        border-left: 3px solid transparent;
    }
    
    .employee-table tbody tr:hover {
        background-color: #f0fdf4 !important;
        border-left-color: #28a745;
        box-shadow: 0 2px 8px rgba(40, 167, 69, 0.15);
        transform: translateX(2px);
    }
    
    .employee-table tbody tr:nth-child(even) {
        background-color: #ffffff;
    }
    
    .employee-table tbody tr:nth-child(odd) {
        background-color: #f8f9fa;
    }
    
    .employee-table tbody tr:hover td {
        color: #212529;
    }
    
    /* Professional badge styling */
    .employee-table .badge {
        font-size: 0.7rem;
        padding: 0.4em 0.75em;
        font-weight: 600;
        border-radius: 0.375rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        letter-spacing: 0.3px;
    }
    
    .employee-table .badge.bg-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
        color: #ffffff;
    }
    
    .employee-table .badge.bg-success {
        background: linear-gradient(135deg, #28a745 0%, #218838 100%) !important;
        color: #ffffff;
    }
    
    .employee-table .badge.bg-danger {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
        color: #ffffff;
    }
    
    .employee-table .badge.bg-secondary {
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%) !important;
        color: #ffffff;
    }
    
    /* Enhanced text styling */
    .employee-table tbody td.fw-bold {
        color: #28a745;
        font-weight: 700 !important;
    }
    
    .employee-table tbody td.fw-semibold {
        color: #495057;
        font-weight: 600 !important;
    }
    
    .employee-table tbody td.small {
        color: #6c757d;
        font-size: 0.8rem;
    }
    
    /* Table container enhancements */
    .table-responsive {
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    /* Card header enhancement */
    .card-header.bg-white {
        background: linear-gradient(to bottom, #ffffff 0%, #f8f9fa 100%) !important;
        border-bottom: 2px solid #e9ecef !important;
    }
    
    /* Professional card styling */
    .card.border-0.shadow-sm {
        border: 1px solid #e9ecef !important;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
        transition: box-shadow 0.3s ease;
    }
    
    .card.border-0.shadow-sm:hover {
        box-shadow: 0 0.25rem 2rem 0 rgba(58, 59, 69, 0.2) !important;
    }
    
    /* Enhanced empty state */
    .text-center.py-5 {
        padding: 3rem 1rem !important;
    }
    
    /* Better scrollbar for table */
    .table-responsive::-webkit-scrollbar {
        height: 8px;
    }
    
    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border-radius: 10px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, #218838 0%, #1aa179 100%);
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .employee-table {
            font-size: 0.75rem;
        }
        
        .employee-table thead th {
            font-size: 0.65rem;
            padding: 0.75rem 0.5rem !important;
        }
        
        .employee-table tbody td {
            padding: 0.65rem 0.5rem !important;
            font-size: 0.75rem;
        }
    }
    
    /* Loading state animation */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .employee-table tbody tr {
        animation: fadeIn 0.3s ease-in;
    }
    
    /* Professional focus states */
    .employee-table tbody tr:focus-within {
        background-color: #d4edda !important;
        border-left-color: #28a745;
    }
    
    /* Ensure footer is visible and stays at bottom */
    #layoutSidenav_content > footer {
        flex-shrink: 0 !important;
        margin-top: auto !important;
    }
    
    /* Statistics cards spacing */
    .row.g-3.mb-4 {
        margin-left: 0 !important;
        margin-right: 0 !important;
        margin-top: 0 !important;
        margin-bottom: 1rem !important;
    }
    
    /* Filter card spacing */
    .card.border-0.shadow-sm.mb-3 {
        margin-left: 0 !important;
        margin-right: 0 !important;
        margin-top: 0 !important;
        margin-bottom: 1rem !important;
    }
    
    /* Header card spacing */
    .professional-header-card {
        margin-left: 0 !important;
        margin-right: 0 !important;
        margin-top: 0 !important;
        margin-bottom: 1rem !important;
    }
    
    /* Breadcrumb spacing */
    .breadcrumb {
        margin-left: 0 !important;
        margin-right: 0 !important;
        margin-top: 0 !important;
        margin-bottom: 1rem !important;
    }
</style>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('#employeeTable').DataTable({
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            "order": [[1, "asc"]], // Sort by Last Name
            "responsive": true,
            "autoWidth": false,
            "searching": false,
            "dom": '<"row"<"col-sm-6"l><"col-sm-6"f>>rt<"row"<"col-sm-6"i><"col-sm-6"p>>',
            "language": {
                "lengthMenu": "Show _MENU_ entries",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "infoEmpty": "Showing 0 to 0 of 0 entries",
                "infoFiltered": "(filtered from _MAX_ total entries)",
                "search": "Search:",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                }
            }
        });
    });
</script>

<?php
require_once 'footer.php';
?>

