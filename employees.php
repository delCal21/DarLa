<?php
require_once 'auth.php';
require_admin_login();
require_once 'db.php';
require_once 'activity_logger.php';

$letter = isset($_GET['letter']) && preg_match('/^[A-Z]$/', $_GET['letter'])
    ? strtoupper($_GET['letter'])
    : 'A';

// Get filter parameters
$filterStatus = isset($_GET['status']) ? trim($_GET['status']) : '';
$filterOffice = isset($_GET['office']) ? trim($_GET['office']) : '';

// Handle new employee submission
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic name fields
    $lastName = trim($_POST['last_name'] ?? '');
    $firstName = trim($_POST['first_name'] ?? '');
    $middleName = trim($_POST['middle_name'] ?? '');

    // Personal information
    $birthdate = trim($_POST['birthdate'] ?? '');
    $homeAddress = trim($_POST['home_address'] ?? '');
    $office = trim($_POST['office'] ?? '');
    $contactNo = trim($_POST['contact_no'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $civilStatus = trim($_POST['civil_status'] ?? '');
    $spouseName = trim($_POST['spouse_name'] ?? '');
    $spouseContactNo = trim($_POST['spouse_contact_no'] ?? '');

    // Employee information
    $employeeNumber = trim($_POST['employee_number'] ?? '');
    $pagibigNumber = trim($_POST['pagibig_number'] ?? '');
    $philhealthNumber = trim($_POST['philhealth_number'] ?? '');
    $tinNumber = trim($_POST['tin_number'] ?? '');
    $sssNumber = trim($_POST['sss_number'] ?? '');
    $gsisNumber = trim($_POST['gsis_number'] ?? '');

    // Additional text fields
    $trainings = trim($_POST['trainings'] ?? '');
    $leaveInfo = trim($_POST['leave_info'] ?? '');
    $serviceRecord = trim($_POST['service_record'] ?? '');
    $employmentStatus = trim($_POST['employment_status'] ?? '');

    // Normalize known statuses for new employees
    if ($employmentStatus !== '') {
        $knownStatuses = [
            'PERMANENT', 'COS', 'SPLIT', 'CTI', 'PA', 'RESIGNED', 'RETIRED', 'OTHERS'
        ];
        foreach ($knownStatuses as $status) {
            if (strcasecmp($employmentStatus, $status) === 0) {
                $employmentStatus = $status;
                break;
            }
        }
    }

    // Optional original appointment info
    $appointmentPosition = trim($_POST['appointment_position'] ?? '');
    $appointmentItemNumber = trim($_POST['appointment_item_number'] ?? '');
    $appointmentSalaryGrade = trim($_POST['appointment_salary_grade'] ?? '');
    $appointmentDate = trim($_POST['appointment_date'] ?? '');
    $appointmentSalary = trim($_POST['appointment_salary'] ?? '');

    // Educational background arrays
    $eduLevels = $_POST['edu_level'] ?? [];
    $eduSchoolNames = $_POST['edu_school_name'] ?? [];
    $eduDegreeCourses = $_POST['edu_degree_course'] ?? [];
    $eduPeriodFrom = $_POST['edu_period_from'] ?? [];
    $eduPeriodTo = $_POST['edu_period_to'] ?? [];
    $eduHighestLevelUnits = $_POST['edu_highest_level_units'] ?? [];
    $eduYearGraduated = $_POST['edu_year_graduated'] ?? [];
    $eduScholarshipHonors = $_POST['edu_scholarship_honors'] ?? [];

    // Work experience arrays
    $weDateFrom = $_POST['we_date_from'] ?? [];
    $weDateTo = $_POST['we_date_to'] ?? [];
    $wePositionTitles = $_POST['we_position_title'] ?? [];
    $weDepartmentAgencies = $_POST['we_department_agency'] ?? [];
    $weMonthlySalaries = $_POST['we_monthly_salary'] ?? [];
    $weSalaryGradeSteps = $_POST['we_salary_grade_step'] ?? [];
    $weStatusAppointments = $_POST['we_status_appointment'] ?? [];
    $weGovtServices = $_POST['we_govt_service'] ?? [];
    $weDescriptionDuties = $_POST['we_description_duties'] ?? [];

    if ($lastName === '' || $firstName === '') {
        $errorMessage = 'First name and last name are required.';
    } elseif (strtoupper(substr($lastName, 0, 1)) !== $letter) {
        $errorMessage = 'Last name must start with the letter "' . htmlspecialchars($letter) . '".';
    } else {
        // Insert employee record
        $stmtInsert = $pdo->prepare(
            'INSERT INTO employees (
                last_name, first_name, middle_name,
                birthdate, home_address, office, contact_no, email, civil_status,
                spouse_name, spouse_contact_no,
                employee_number, pagibig_number, philhealth_number,
                tin_number, sss_number, gsis_number,
                trainings, leave_info, service_record, employment_status
            ) VALUES (
                :last_name, :first_name, :middle_name,
                :birthdate, :home_address, :office, :contact_no, :email, :civil_status,
                :spouse_name, :spouse_contact_no,
                :employee_number, :pagibig_number, :philhealth_number,
                :tin_number, :sss_number, :gsis_number,
                :trainings, :leave_info, :service_record, :employment_status
            )'
        );

        $stmtInsert->execute([
            ':last_name' => $lastName,
            ':first_name' => $firstName,
            ':middle_name' => $middleName !== '' ? $middleName : null,
            ':birthdate' => $birthdate !== '' ? $birthdate : null,
            ':home_address' => $homeAddress !== '' ? $homeAddress : null,
            ':office' => $office !== '' ? $office : null,
            ':contact_no' => $contactNo !== '' ? $contactNo : null,
            ':email' => $email !== '' ? $email : null,
            ':civil_status' => $civilStatus !== '' ? $civilStatus : null,
            ':spouse_name' => $spouseName !== '' ? $spouseName : null,
            ':spouse_contact_no' => $spouseContactNo !== '' ? $spouseContactNo : null,
            ':employee_number' => $employeeNumber !== '' ? $employeeNumber : null,
            ':pagibig_number' => $pagibigNumber !== '' ? $pagibigNumber : null,
            ':philhealth_number' => $philhealthNumber !== '' ? $philhealthNumber : null,
            ':tin_number' => $tinNumber !== '' ? $tinNumber : null,
            ':sss_number' => $sssNumber !== '' ? $sssNumber : null,
            ':gsis_number' => $gsisNumber !== '' ? $gsisNumber : null,
            ':trainings' => $trainings !== '' ? $trainings : null,
            ':leave_info' => $leaveInfo !== '' ? $leaveInfo : null,
            ':service_record' => $serviceRecord !== '' ? $serviceRecord : null,
            ':employment_status' => $employmentStatus !== '' ? $employmentStatus : null,
        ]);

        $newEmployeeId = (int)$pdo->lastInsertId();

        // Log activity
        logActivity('employee_create', "Employee created: {$lastName}, {$firstName}", 'employees', $newEmployeeId);

        // If any appointment field is filled, create an "Original Appointment" record
        if (
            $appointmentPosition !== '' ||
            $appointmentItemNumber !== '' ||
            $appointmentSalaryGrade !== '' ||
            $appointmentDate !== '' ||
            $appointmentSalary !== ''
        ) {
            $stmtAppt = $pdo->prepare(
                'INSERT INTO appointments (
                    employee_id, sequence_no, type_label,
                    position, item_number, salary_grade, appointment_date, salary
                ) VALUES (
                    :employee_id, 1, :type_label,
                    :position, :item_number, :salary_grade, :appointment_date, :salary
                )'
            );

            $stmtAppt->execute([
                ':employee_id' => $newEmployeeId,
                ':type_label' => 'Original Appointment',
                ':position' => $appointmentPosition !== '' ? $appointmentPosition : null,
                ':item_number' => $appointmentItemNumber !== '' ? $appointmentItemNumber : null,
                ':salary_grade' => $appointmentSalaryGrade !== '' ? $appointmentSalaryGrade : null,
                ':appointment_date' => $appointmentDate !== '' ? $appointmentDate : null,
                ':salary' => $appointmentSalary !== '' ? $appointmentSalary : null,
            ]);
        }

        // Insert educational background records if provided
        if (!empty($eduLevels)) {
            for ($i = 0; $i < count($eduLevels); $i++) {
                $level = trim($eduLevels[$i] ?? '');
                $schoolName = trim($eduSchoolNames[$i] ?? '');

                // Only insert if at least a level and school name are provided
                if ($level !== '' && $schoolName !== '') {
                    $stmtEdu = $pdo->prepare(
                        'INSERT INTO employee_educational_background (
                            employee_id, level, school_name, degree_course, period_from, period_to,
                            highest_level_units, year_graduated, scholarship_honors
                        ) VALUES (
                            :employee_id, :level, :school_name, :degree_course, :period_from, :period_to,
                            :highest_level_units, :year_graduated, :scholarship_honors
                        )'
                    );

                    $stmtEdu->execute([
                        ':employee_id' => $newEmployeeId,
                        ':level' => $level,
                        ':school_name' => $schoolName,
                        ':degree_course' => trim($eduDegreeCourses[$i] ?? '') !== '' ? trim($eduDegreeCourses[$i]) : null,
                        ':period_from' => trim($eduPeriodFrom[$i] ?? '') !== '' ? trim($eduPeriodFrom[$i]) : null,
                        ':period_to' => trim($eduPeriodTo[$i] ?? '') !== '' ? trim($eduPeriodTo[$i]) : null,
                        ':highest_level_units' => trim($eduHighestLevelUnits[$i] ?? '') !== '' ? trim($eduHighestLevelUnits[$i]) : null,
                        ':year_graduated' => trim($eduYearGraduated[$i] ?? '') !== '' ? trim($eduYearGraduated[$i]) : null,
                        ':scholarship_honors' => trim($eduScholarshipHonors[$i] ?? '') !== '' ? trim($eduScholarshipHonors[$i]) : null,
                    ]);
                }
            }
        }

        // Insert work experience records if provided
        if (!empty($weDateFrom)) {
            for ($i = 0; $i < count($weDateFrom); $i++) {
                $dateFrom = trim($weDateFrom[$i] ?? '');
                $positionTitle = trim($wePositionTitles[$i] ?? '');
                $departmentAgency = trim($weDepartmentAgencies[$i] ?? '');

                // Only insert if at least date from, position title, and department/agency are provided
                if ($dateFrom !== '' && $positionTitle !== '' && $departmentAgency !== '') {
                    // Convert date format if needed
                    $validDateFrom = null;
                    if (strtotime($dateFrom) !== false) {
                        $validDateFrom = $dateFrom;
                    }

                    $validDateTo = null;
                    if (trim($weDateTo[$i] ?? '') !== '') {
                        if (strtotime(trim($weDateTo[$i])) !== false) {
                            $validDateTo = trim($weDateTo[$i]);
                        }
                    }

                    $stmtWork = $pdo->prepare(
                        'INSERT INTO employee_work_experience (
                            employee_id, date_from, date_to, position_title, department_agency, monthly_salary,
                            salary_grade_step, status_of_appointment, govt_service, description_of_duties
                        ) VALUES (
                            :employee_id, :date_from, :date_to, :position_title, :department_agency, :monthly_salary,
                            :salary_grade_step, :status_of_appointment, :govt_service, :description_of_duties
                        )'
                    );

                    $stmtWork->execute([
                        ':employee_id' => $newEmployeeId,
                        ':date_from' => $validDateFrom,
                        ':date_to' => $validDateTo,
                        ':position_title' => $positionTitle,
                        ':department_agency' => $departmentAgency,
                        ':monthly_salary' => trim($weMonthlySalaries[$i] ?? '') !== '' ? (float)trim($weMonthlySalaries[$i]) : null,
                        ':salary_grade_step' => trim($weSalaryGradeSteps[$i] ?? '') !== '' ? trim($weSalaryGradeSteps[$i]) : null,
                        ':status_of_appointment' => trim($weStatusAppointments[$i] ?? '') !== '' ? trim($weStatusAppointments[$i]) : null,
                        ':govt_service' => trim($weGovtServices[$i] ?? '') !== '' ? trim($weGovtServices[$i]) : 'YES',
                        ':description_of_duties' => trim($weDescriptionDuties[$i] ?? '') !== '' ? trim($weDescriptionDuties[$i]) : null,
                    ]);
                }
            }
        }

        // Check if employee was added as inactive
        if (strcasecmp($employmentStatus, 'RETIRED') === 0 || strcasecmp($employmentStatus, 'RESIGNED') === 0) {
            $statusText = strcasecmp($employmentStatus, 'RETIRED') === 0 ? 'RETIRED' : 'RESIGNED';
            $firstLetter = strtoupper(substr($lastName, 0, 1));
            $successMessage = 'New inactive employee "' . htmlspecialchars($lastName . ', ' . $firstName) . '" has been added with status: ' . $statusText . '. ';
            $successMessage .= 'You can view this employee in the <a href="retired_employees.php?letter=' . $firstLetter . '" class="alert-link">Inactive Employees section</a>.';
        } else {
            $successMessage = 'New employee "' . htmlspecialchars($lastName . ', ' . $firstName) . '" has been added.';
        }
    }
}

// Build SQL query with filters
$whereConditions = [
    'last_name LIKE :prefix',
    '(employment_status IS NULL OR employment_status NOT IN ("RETIRED", "RESIGNED"))'
];
$queryParams = [':prefix' => $letter . '%'];

// Add employment status filter
if ($filterStatus !== '') {
    if ($filterStatus === 'Not Set') {
        $whereConditions[] = '(employment_status IS NULL OR TRIM(employment_status) = "")';
    } else {
        $whereConditions[] = 'employment_status = :filter_status';
        $queryParams[':filter_status'] = $filterStatus;
    }
}

// Add office/department filter
if ($filterOffice !== '') {
    if ($filterOffice === 'Not Set') {
        $whereConditions[] = '(office IS NULL OR TRIM(office) = "")';
    } else {
        $whereConditions[] = 'office = :filter_office';
        $queryParams[':filter_office'] = $filterOffice;
    }
}

$sql = 'SELECT id, last_name, first_name, middle_name
        FROM employees
        WHERE ' . implode(' AND ', $whereConditions) . '
        ORDER BY last_name, first_name';

$stmt = $pdo->prepare($sql);
$stmt->execute($queryParams);
$employees = $stmt->fetchAll();

require_once 'header.php';
?>

<!-- Breadcrumb -->
<ol class="breadcrumb mb-3">
    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="employee_directory.php">Employee List</a></li>
    <li class="breadcrumb-item active">Letter <?= htmlspecialchars($letter) ?></li>
</ol>

<!-- Professional Header Card -->
<div class="card border-0 shadow-sm mb-3 professional-header-card compact-header">
    <div class="card-body p-3">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center">
                    <div class="letter-avatar me-2">
                        <div class="avatar-circle letter-circle compact-letter">
                            <?= htmlspecialchars($letter) ?>
                        </div>
                    </div>
                    <div>
                        <h1 class="h4 mb-0 fw-bold text-dark">Employees - Letter <?= htmlspecialchars($letter) ?></h1>
                        <p class="text-muted mb-0 small">
                            <i class="fas fa-users me-1"></i>
                            Last name starting with "<strong><?= htmlspecialchars($letter) ?></strong>"
                            <?php if ($filterStatus !== '' || $filterOffice !== ''): ?>
                                <span class="ms-2">
                                    <i class="fas fa-filter me-1"></i>Filters active
                                </span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end mt-2 mt-md-0">
                <div class="d-flex flex-column flex-md-row gap-2 justify-content-md-end">
                    <?php
                    // Build back URL with filters
                    $backUrl = 'employee_directory.php';
                    if ($filterStatus !== '' || $filterOffice !== '') {
                        $backUrl .= '?';
                        $backParams = [];
                        if ($filterStatus !== '') {
                            $backParams[] = 'status=' . urlencode($filterStatus);
                        }
                        if ($filterOffice !== '') {
                            $backParams[] = 'office=' . urlencode($filterOffice);
                        }
                        $backUrl .= implode('&', $backParams);
                    }
                    ?>
                    <a href="<?= htmlspecialchars($backUrl) ?>" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                    <button
                        class="btn btn-primary btn-sm"
                        type="button"
                        data-bs-toggle="modal"
                        data-bs-target="#addEmployeeModal"
                    >
                        <i class="fas fa-plus-circle me-1"></i> Add Employee
                    </button>
                </div>
            </div>
        </div>
        <?php if ($filterStatus !== '' || $filterOffice !== ''): ?>
            <div class="mt-3 pt-3 border-top">
                <div class="alert alert-info mb-0 py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-filter me-2"></i>
                            <strong>Active Filters:</strong>
                            <?php if ($filterStatus !== ''): ?>
                                <span class="badge bg-primary me-2">
                                    <i class="fas fa-briefcase me-1"></i>Status: <?= htmlspecialchars($filterStatus) ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($filterOffice !== ''): ?>
                                <span class="badge bg-primary me-2">
                                    <i class="fas fa-building me-1"></i>Department: <?= htmlspecialchars($filterOffice) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <a href="employees.php?letter=<?= htmlspecialchars($letter) ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Clear Filters
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php if (!empty($employees)): ?>
            <?php
            $stmtTotal = $pdo->query('SELECT COUNT(*) as total FROM employees WHERE (employment_status IS NULL OR employment_status NOT IN ("RETIRED", "RESIGNED"))');
            $totalAllEmployees = $stmtTotal->fetch()['total'];
            $percentage = $totalAllEmployees > 0 ? (count($employees) / $totalAllEmployees) * 100 : 0;
            ?>
            <div class="mt-2 pt-2 border-top">
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="stat-item-compact">
                            <div class="stat-number-compact"><?= count($employees) ?></div>
                            <div class="stat-label-compact">Employees</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-item-compact">
                            <div class="stat-number-compact"><?= number_format($percentage, 1) ?>%</div>
                            <div class="stat-label-compact">Of Total</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-item-compact">
                            <div class="stat-number-compact"><?= htmlspecialchars($letter) ?></div>
                            <div class="stat-label-compact">Letter</div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
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
        <div><?= $successMessage ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Add Employee Modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addEmployeeModalLabel">
                    <i class="fas fa-user-plus me-2"></i>
                    Add New Employee
                    <span class="badge bg-light text-primary ms-2">Letter <?= htmlspecialchars($letter) ?></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Note:</strong> Last name must start with "<strong><?= htmlspecialchars($letter) ?></strong>"
                </div>
            <form method="post" autocomplete="off">
                <div class="mb-3 pb-2 border-bottom">
                    <h6 class="mb-0 text-primary">
                        <i class="fas fa-id-card me-2"></i>Name Information
                    </h6>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            class="form-control"
                            id="last_name"
                            name="last_name"
                            required
                            placeholder="Last name (must start with <?= htmlspecialchars($letter) ?>)"
                        >
                    </div>
                    <div class="col-md-4">
                        <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            class="form-control"
                            id="first_name"
                            name="first_name"
                            required
                            placeholder="First name"
                        >
                    </div>
                    <div class="col-md-4">
                        <label for="middle_name" class="form-label">Middle Name</label>
                        <input
                            type="text"
                            class="form-control"
                            id="middle_name"
                            name="middle_name"
                            placeholder="Middle name"
                        >
                    </div>
                </div>

                <div class="mb-3 pb-2 border-bottom mt-4">
                    <h6 class="mb-0 text-primary">
                        <i class="fas fa-user me-2"></i>Personal Information
                    </h6>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label for="birthdate" class="form-label">Birthdate</label>
                        <input
                            type="date"
                            class="form-control"
                            id="birthdate"
                            name="birthdate"
                        >
                    </div>
                    <div class="col-md-9">
                        <label for="home_address" class="form-label">Home Address</label>
                        <input
                            type="text"
                            class="form-control"
                            id="home_address"
                            name="home_address"
                            placeholder="Home address"
                        >
                    </div>
                    <div class="col-md-4">
                        <label for="contact_no" class="form-label">Contact #</label>
                        <input
                            type="text"
                            class="form-control"
                            id="contact_no"
                            name="contact_no"
                            placeholder="Contact number"
                        >
                    </div>
                    <div class="col-md-4">
                        <label for="email" class="form-label">Email Address</label>
                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            name="email"
                            placeholder="Email"
                        >
                    </div>
                    <div class="col-md-4">
                        <label for="civil_status" class="form-label">Status</label>
                        <select
                            class="form-select"
                            id="civil_status"
                            name="civil_status"
                        >
                            <option value="">Select status</option>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Widowed">Widowed</option>
                            <option value="Separated">Separated</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="spouse_name" class="form-label">Name of Spouse</label>
                        <input
                            type="text"
                            class="form-control"
                            id="spouse_name"
                            name="spouse_name"
                            placeholder="Spouse name"
                        >
                    </div>
                    <div class="col-md-6">
                        <label for="spouse_contact_no" class="form-label">Spouse Contact #</label>
                        <input
                            type="text"
                            class="form-control"
                            id="spouse_contact_no"
                            name="spouse_contact_no"
                            placeholder="Spouse contact number"
                        >
                    </div>
                </div>

                <div class="mb-3 pb-2 border-bottom mt-4">
                    <h6 class="mb-0 text-primary">
                        <i class="fas fa-briefcase me-2"></i>Employee Information
                    </h6>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label for="employee_number" class="form-label">Employee ID #</label>
                        <input
                            type="text"
                            class="form-control"
                            id="employee_number"
                            name="employee_number"
                            placeholder="Employee ID"
                        >
                    </div>
                    <div class="col-md-3">
                        <label for="pagibig_number" class="form-label">Pag-ibig #</label>
                        <input
                            type="text"
                            class="form-control"
                            id="pagibig_number"
                            name="pagibig_number"
                            placeholder="Pag-ibig number"
                        >
                    </div>
                    <div class="col-md-3">
                        <label for="philhealth_number" class="form-label">PhilHealth #</label>
                        <input
                            type="text"
                            class="form-control"
                            id="philhealth_number"
                            name="philhealth_number"
                            placeholder="PhilHealth number"
                        >
                    </div>
                    <div class="col-md-3">
                        <label for="tin_number" class="form-label">TIN #</label>
                        <input
                            type="text"
                            class="form-control"
                            id="tin_number"
                            name="tin_number"
                            placeholder="TIN number"
                        >
                    </div>
                    <div class="col-md-3">
                        <label for="sss_number" class="form-label">SSS #</label>
                        <input
                            type="text"
                            class="form-control"
                            id="sss_number"
                            name="sss_number"
                            placeholder="SSS number"
                        >
                    </div>
                    <div class="col-md-3">
                        <label for="gsis_number" class="form-label">GSIS #</label>
                        <input
                            type="text"
                            class="form-control"
                            id="gsis_number"
                            name="gsis_number"
                            placeholder="GSIS number"
                        >
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label for="employment_status" class="form-label">Employment Status</label>
                        <select
                            class="form-select"
                            id="employment_status"
                            name="employment_status"
                        >
                            <option value="">-- Select Status --</option>
                            <option value="PERMANENT">PERMANENT</option>
                            <option value="COS">COS</option>
                            <option value="SPLIT">SPLIT</option>
                            <option value="CTI">CTI</option>
                            <option value="PA">PA</option>
                            <option value="RESIGNED">RESIGNED</option>
                            <option value="RETIRED">RETIRED</option>
                            <option value="OTHERS">OTHERS</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label for="office" class="form-label">Department / Office</label>
                        <select
                            class="form-select"
                            id="office"
                            name="office"
                        >
                            <option value="">-- Select Department / Office --</option>
                            <option value="LTS">LTS</option>
                            <option value="LEGAL">LEGAL</option>
                            <option value="DARAB">DARAB</option>
                            <option value="PBDD">PBDD</option>
                            <option value="OPARPO">OPARPO</option>
                            <option value="STOD">STOD</option>
                        </select>
                    </div>
                </div>



                <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                    <p class="small text-muted mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Only name is required; all other fields are optional.
                    </p>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Employee
                        </button>
                    </div>
                </div>
            </form>
            </div>
        </div>
    </div>
</div>

<!-- Employee List Table -->
<?php if (empty($employees)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-users fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No Employees Found</h5>
            <p class="text-muted mb-0">
                No employees found whose last name starts with "<strong><?= htmlspecialchars($letter) ?></strong>".
            </p>
            <button class="btn btn-primary mt-3" type="button" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                <i class="fas fa-plus-circle me-1"></i> Add First Employee
            </button>
        </div>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-2">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="fas fa-list me-2 text-primary"></i>
                    Employee Records
                </h6>
                <span class="badge bg-primary">
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
                                <a href="employee.php?id=<?= (int)$emp['id'] ?>" class="btn btn-sm btn-outline-primary" style="font-size: 0.8rem; padding: 0.25rem 0.5rem;">
                                    <i class="fas fa-eye me-1"></i> View
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

<?php
require_once 'footer.php';
?>


