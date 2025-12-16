<?php
require_once 'auth.php';
require_admin_login();

// Include database connection
require_once 'db.php';

// Initialize filters
$selectedStatus = isset($_GET['status']) ? $_GET['status'] : '';
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
(SELECT MIN(appointment_date) FROM appointments WHERE employee_id = e.id) as date_hired
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

if (!empty($searchTerm)) {
    $sql .= " AND (employee_number LIKE ? OR first_name LIKE ? OR middle_name LIKE ? OR last_name LIKE ? OR position LIKE ? OR department LIKE ?)";
    $searchPattern = "%{$searchTerm}%";
    for ($i = 0; $i < 6; $i++) $params[] = $searchPattern;
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
    if (!empty($searchTerm)) {
        $filterInfo .= "- Search Term: {$searchTerm}";
    } else {
        $filterInfo .= "- Search Term: None";
    }

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, $filterInfo, 0, 1, 'L');
    $pdf->Ln(5);

    // Table header - using smaller font for more columns
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetFillColor(200, 200, 200);
    $pdf->Cell(20, 10, 'ID', 1, 0, 'C', true);
    $pdf->Cell(25, 10, 'Last Name', 1, 0, 'C', true);
    $pdf->Cell(25, 10, 'First Name', 1, 0, 'C', true);
    $pdf->Cell(20, 10, 'M. Name', 1, 0, 'C', true);
    $pdf->Cell(20, 10, 'Birthdate', 1, 0, 'C', true);
    $pdf->Cell(35, 10, 'Address', 1, 0, 'C', true);
    $pdf->Cell(25, 10, 'Contact', 1, 0, 'C', true);
    $pdf->Cell(35, 10, 'Email', 1, 0, 'C', true);
    $pdf->Cell(20, 10, 'Civil Status', 1, 0, 'C', true);
    $pdf->Cell(30, 10, 'Position', 1, 0, 'C', true);
    $pdf->Cell(25, 10, 'Department', 1, 0, 'C', true);
    $pdf->Cell(20, 10, 'Status', 1, 0, 'C', true);
    $pdf->Cell(20, 10, 'Date Hired', 1, 0, 'C', true);
    $pdf->Ln();

    // Table data
    $pdf->SetFont('helvetica', '', 7);
    foreach ($employees as $employee) {
        $pdf->Cell(20, 8, $employee['employee_number'] ?? 'N/A', 1);
        $pdf->Cell(25, 8, substr($employee['last_name'], 0, 15), 1);
        $pdf->Cell(25, 8, substr($employee['first_name'], 0, 15), 1);
        $pdf->Cell(20, 8, substr($employee['middle_name'] ?? '', 0, 10), 1);
        $pdf->Cell(20, 8, $employee['birthdate'] ?? 'N/A', 1);
        $pdf->Cell(35, 8, substr($employee['home_address'] ?? 'N/A', 0, 20), 1);
        $pdf->Cell(25, 8, substr($employee['contact_no'] ?? 'N/A', 0, 12), 1);
        $pdf->Cell(35, 8, substr($employee['email'] ?? 'N/A', 0, 20), 1);
        $pdf->Cell(20, 8, substr($employee['civil_status'] ?? 'N/A', 0, 8), 1);
        $pdf->Cell(30, 8, substr($employee['position'] ?? 'N/A', 0, 15), 1);
        $pdf->Cell(25, 8, substr($employee['department'] ?? 'N/A', 0, 12), 1);
        $pdf->Cell(20, 8, substr($employee['employment_status'] ?? 'N/A', 0, 8), 1);
        $pdf->Cell(20, 8, $employee['date_hired'] ?? 'N/A', 1);
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
                        <span class="badge bg-info ms-2">Filtered: <?= htmlspecialchars($selectedStatus) ?></span>
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
                <a class="btn btn-sm btn-success mx-1" href="<?= $_SERVER['PHP_SELF'] ?>?export=csv&status=<?= urlencode($selectedStatus) ?>&search=<?= urlencode($searchTerm) ?>">
                    <i class="fas fa-file-csv me-1"></i>Export CSV
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <form method="get" class="row g-3">
            <div class="col-md-4">
                <label for="statusFilter" class="form-label small">Employment Status</label>
                <select name="status" id="statusFilter" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <?php foreach ($statusOptions as $status): ?>
                        <option value="<?= htmlspecialchars($status) ?>"
                            <?= $selectedStatus === $status ? 'selected' : '' ?>>
                            <?= htmlspecialchars($status) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label for="searchInput" class="form-label small">Search</label>
                <input type="text" class="form-control form-control-sm" id="searchInput" name="search"
                       placeholder="Search by ID, name, position, or department..."
                       value="<?= htmlspecialchars($searchTerm) ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
            </div>
        </form>
        <?php if (!empty($selectedStatus) || !empty($searchTerm)): ?>
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
                        <th>Employee ID</th>
                        <th>Last Name</th>
                        <th>First Name</th>
                        <th>Middle Name</th>
                        <th>Birthdate</th>
                        <th>Address</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Civil Status</th>
                        <th>Status</th>
                        <th>TIN</th>
                        <th>SSS</th>
                        <th>GSIS</th>
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
    
    /* Improve table readability */
    .employee-table {
        font-size: 0.85rem;
    }
    
    .employee-table thead th {
        background-color: #f8f9fa !important;
        font-weight: 600 !important;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        padding: 0.75rem 0.5rem !important;
        border-bottom: 2px solid #dee2e6 !important;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    .employee-table tbody td {
        padding: 0.6rem 0.5rem !important;
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0 !important;
    }
    
    .employee-table tbody tr:hover {
        background-color: #f8f9fa !important;
    }
    
    .employee-table tbody tr:nth-child(even) {
        background-color: #ffffff;
    }
    
    .employee-table tbody tr:nth-child(odd) {
        background-color: #fafafa;
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

