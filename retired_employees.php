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

// Get unique employment statuses from database (only RETIRED and RESIGNED for this page)
$statusOptions = ['RETIRED', 'RESIGNED'];

// Get unique offices/departments from database
$officeOptions = [];
$stmt = $pdo->query("SELECT DISTINCT office FROM employees WHERE office IS NOT NULL AND TRIM(office) != '' ORDER BY office ASC");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $officeVal = trim((string)($row['office'] ?? ''));
    if ($officeVal !== '') {
        $officeOptions[] = $officeVal;
    }
}
$officeOptions = array_values(array_unique($officeOptions));
natsort($officeOptions);
$officeOptions = array_values($officeOptions);

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
            $successMessage = 'New inactive employee "' . htmlspecialchars($lastName . ', ' . $firstName) . '" has been added with status: ' . $statusText . '.';
            // Redirect to refresh the page and show the new employee
            header('Location: retired_employees.php?letter=' . $firstLetter . '&success=' . urlencode($successMessage));
            exit;
        } else {
            // If not inactive, redirect to main employees page
            $firstLetter = strtoupper(substr($lastName, 0, 1));
            header('Location: employees.php?letter=' . $firstLetter . '&success=' . urlencode('New employee "' . htmlspecialchars($lastName . ', ' . $firstName) . '" has been added.'));
            exit;
        }
    }
}

// Check for success message from redirect
if (isset($_GET['success'])) {
    $successMessage = $_GET['success'];
}

// Build query with filters
$query = 'SELECT id, last_name, first_name, middle_name, employment_status
     FROM employees
     WHERE last_name LIKE :prefix
     AND employment_status IN ("RETIRED", "RESIGNED")';
$params = [':prefix' => $letter . '%'];

if ($filterStatus !== '') {
    if ($filterStatus === 'Not Set') {
        $query .= ' AND (employment_status IS NULL OR TRIM(employment_status) = "")';
    } else {
        $query .= ' AND employment_status = :filter_status';
        $params[':filter_status'] = $filterStatus;
    }
}

if ($filterOffice !== '') {
    if ($filterOffice === 'Not Set') {
        $query .= ' AND (office IS NULL OR TRIM(office) = "")';
    } else {
        $query .= ' AND office = :filter_office';
        $params[':filter_office'] = $filterOffice;
    }
}

$query .= ' ORDER BY last_name, first_name';

$stmt = $pdo->prepare($query);
$stmt->execute($params);
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
                    <button
                        class="btn btn-primary btn-sm"
                        type="button"
                        data-bs-toggle="modal"
                        data-bs-target="#addEmployeeModal"
                    >
                        <i class="fas fa-plus-circle me-1"></i> Add Inactive Employee
                    </button>
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
        <div><?= $successMessage ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Filter Section -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white border-bottom">
        <h5 class="card-title mb-0">
            <i class="fas fa-filter me-2 text-primary"></i>Filter Employees
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" action="retired_employees.php" id="filterForm">
            <input type="hidden" name="letter" value="<?= htmlspecialchars($letter) ?>">
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
                        <option value="">All DIvision</option>
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
                        <a href="retired_employees.php?letter=<?= htmlspecialchars($letter) ?>" class="btn btn-outline-secondary btn-sm">
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
<div class="card border-0 shadow-sm mb-3">
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
            
            foreach (range('A', 'Z') as $l): 
                $letterUrl = 'retired_employees.php?letter=' . $l . $filterQuery;
            ?>
                <a class="letter-button" href="<?= htmlspecialchars($letterUrl) ?>">
                    <?= $l ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>


<!-- Add Employee Modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addEmployeeModalLabel">
                    <i class="fas fa-user-plus me-2"></i>
                    Add New Inactive Employee
                    <span class="badge bg-light text-primary ms-2">Letter <?= htmlspecialchars($letter) ?></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning mb-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Note:</strong> You are adding an inactive employee. Please select <strong>RETIRED</strong> or <strong>RESIGNED</strong> as the employment status. Last name must start with "<strong><?= htmlspecialchars($letter) ?></strong>"
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
                            class="form-select form-select-sm"
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
                        <label for="employment_status" class="form-label">Employment Status <span class="text-danger">*</span></label>
                        <select
                            class="form-select form-select-sm"
                            id="employment_status"
                            name="employment_status"
                            required
                        >
                            <option value="">-- Select Status --</option>
                            <option value="RETIRED" selected>RETIRED</option>
                            <option value="RESIGNED">RESIGNED</option>
                            <option value="PERMANENT">PERMANENT</option>
                            <option value="COS">COS</option>
                            <option value="SPLIT">SPLIT</option>
                            <option value="CTI">CTI</option>
                            <option value="PA">PA</option>
                            <option value="OTHERS">OTHERS</option>
                        </select>
                        <small class="text-muted">Default: RETIRED (recommended for inactive employees)</small>
                    </div>
                    <div class="col-md-8">
                        <label for="office" class="form-label">Department / Office</label>
                        <select
                            class="form-select form-select-sm"
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
                        Only name and employment status are required; all other fields are optional.
                    </p>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-save me-1"></i> Save Inactive Employee
                        </button>
                    </div>
                </div>
            </form>
            </div>
        </div>
    </div>
</div>

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

