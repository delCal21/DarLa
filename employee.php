<?php
require_once 'auth.php';
require_admin_login();
require_once 'db.php';
require_once 'activity_logger.php';

/**
 * Enhanced Employee ID Parameter Validation and Processing
 *
 * This section handles the employee ID parameter with professional-grade
 * validation, security checks, and error handling.
 */

// Initialize error tracking
$validationError = null;
$employeeId = null;

// Validate and sanitize employee ID parameter
if (!isset($_GET['id']) || $_GET['id'] === '') {
    $validationError = 'Employee ID parameter is required.';
} else {
    $rawId = trim($_GET['id']);

    // Validate format: must be numeric and positive integer
    if (!ctype_digit($rawId)) {
        $validationError = 'Invalid employee ID format. ID must be a positive integer.';
    } else {
        // Convert to integer and validate range
        $employeeId = (int)$rawId;

        // Validate reasonable range (1 to PHP_INT_MAX, but cap at 999999999 for practical purposes)
        if ($employeeId <= 0) {
            $validationError = 'Employee ID must be a positive number.';
        } elseif ($employeeId > 999999999) {
            $validationError = 'Employee ID exceeds maximum allowed value.';
        } elseif (strlen($rawId) > 10) {
            // Additional check for extremely long numeric strings
            $validationError = 'Employee ID is too long.';
        }
    }
}

// If validation failed, redirect with appropriate error handling
if ($validationError !== null) {
    // Log the validation error for security monitoring
    if (function_exists('logActivity')) {
        $invalidId = $_GET['id'] ?? 'not_provided';
        logActivity(
            'INVALID_EMPLOYEE_ID_ACCESS',
            "Invalid employee ID access attempt. ID: {$invalidId}, Error: {$validationError}",
            'employees',
            null
        );
    }

    // Set proper HTTP status code and redirect
    http_response_code(400); // Bad Request
    header('Location: index.php?error=invalid_employee_id');
    exit;
}

// Assign validated ID
$id = $employeeId;

$successMessage = '';
$errorMessage = '';

// Handle updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_employee') {
        // Name
        $lastName = trim($_POST['last_name'] ?? '');
        $firstName = trim($_POST['first_name'] ?? '');
        $middleName = trim($_POST['middle_name'] ?? '');

        // Personal info
        $birthdate = trim($_POST['birthdate'] ?? '');
        $homeAddress = trim($_POST['home_address'] ?? '');
        $office = trim($_POST['office'] ?? '');
        $contactNo = trim($_POST['contact_no'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $civilStatus = trim($_POST['civil_status'] ?? '');
        $spouseName = trim($_POST['spouse_name'] ?? '');
        $spouseContactNo = trim($_POST['spouse_contact_no'] ?? '');

        // Employee info
        $employeeNumber = trim($_POST['employee_number'] ?? '');
        $pagibigNumber = trim($_POST['pagibig_number'] ?? '');
        $philhealthNumber = trim($_POST['philhealth_number'] ?? '');
        $tinNumber = trim($_POST['tin_number'] ?? '');
        $sssNumber = trim($_POST['sss_number'] ?? '');
        $gsisNumber = trim($_POST['gsis_number'] ?? '');

        // Additional
        $trainings = trim($_POST['trainings'] ?? '');
        $leaveInfo = trim($_POST['leave_info'] ?? '');
        $serviceRecord = trim($_POST['service_record'] ?? '');
        $employmentStatus = trim($_POST['employment_status'] ?? '');

        // Normalize known statuses when updating full employee record
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

        if ($lastName === '' || $firstName === '') {
            $errorMessage = 'First name and last name are required.';
        } else {
            $stmtUpdate = $pdo->prepare(
                'UPDATE employees SET
                    last_name = :last_name,
                    first_name = :first_name,
                    middle_name = :middle_name,
                    birthdate = :birthdate,
                    home_address = :home_address,
                    office = :office,
                    contact_no = :contact_no,
                    email = :email,
                    civil_status = :civil_status,
                    spouse_name = :spouse_name,
                    spouse_contact_no = :spouse_contact_no,
                    employee_number = :employee_number,
                    pagibig_number = :pagibig_number,
                    philhealth_number = :philhealth_number,
                    tin_number = :tin_number,
                    sss_number = :sss_number,
                    gsis_number = :gsis_number,
                    trainings = :trainings,
                    leave_info = :leave_info,
                    service_record = :service_record,
                    employment_status = :employment_status
                 WHERE id = :id'
            );

            // Get current status before update
            $stmtCurrent = $pdo->prepare('SELECT employment_status FROM employees WHERE id = :id');
            $stmtCurrent->execute([':id' => $id]);
            $currentEmployee = $stmtCurrent->fetch();
            $oldStatus = $currentEmployee['employment_status'] ?? '';

            $stmtUpdate->execute([
                ':id' => $id,
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

            // Log activity
            logActivity('employee_update', "Employee updated: {$lastName}, {$firstName}", 'employees', $id);

            // Check if status changed to RETIRED or RESIGNED
            if ((strcasecmp($employmentStatus, 'RETIRED') === 0 || strcasecmp($employmentStatus, 'RESIGNED') === 0) &&
                strcasecmp($oldStatus, 'RETIRED') !== 0 && strcasecmp($oldStatus, 'RESIGNED') !== 0) {
                $statusText = strcasecmp($employmentStatus, 'RETIRED') === 0 ? 'RETIRED' : 'RESIGNED';
                $successMessage = "Employee details have been updated. Employee status changed to {$statusText} and has been moved to the Inactive Employee section.";
            } else {
                $successMessage = 'Employee details have been updated.';
            }
        }
    } elseif ($action === 'update_employment_status') {
        // Accept status from hidden JS field, custom text, or select dropdown
        $statusFromHidden = trim($_POST['employment_status'] ?? '');
        $statusFromCustom = trim($_POST['employment_status_custom'] ?? '');
        $statusFromSelect = trim($_POST['employment_status_select'] ?? '');

        $newStatus = $statusFromHidden !== ''
            ? $statusFromHidden
            : ($statusFromCustom !== '' ? $statusFromCustom : $statusFromSelect);

        // Normalize known statuses (e.g., avoid "RETIRED " vs "RETIRED")
        $knownStatuses = [
            'PERMANENT', 'COS', 'SPLIT', 'CTI', 'PA', 'RESIGNED', 'RETIRED', 'OTHERS'
        ];
        foreach ($knownStatuses as $status) {
            if (strcasecmp($newStatus, $status) === 0) {
                $newStatus = $status;
                break;
            }
        }

        try {
            // Get current status before update
            $stmtCurrent = $pdo->prepare('SELECT employment_status FROM employees WHERE id = :id');
            $stmtCurrent->execute([':id' => $id]);
            $currentEmployee = $stmtCurrent->fetch();
            $oldStatus = $currentEmployee['employment_status'] ?? '';

            $stmt = $pdo->prepare('UPDATE employees SET employment_status = :status WHERE id = :id');
            $stmt->execute([
                ':status' => $newStatus !== '' ? $newStatus : null,
                ':id' => $id
            ]);

            // Log activity
            $statusChange = ($oldStatus !== $newStatus) ? " (changed from '{$oldStatus}' to '{$newStatus}')" : '';
            logActivity('employee_update', "Employment status updated to: {$newStatus}{$statusChange}", 'employees', $id);

            // Check if status changed to RETIRED or RESIGNED
            if ((strcasecmp($newStatus, 'RETIRED') === 0 || strcasecmp($newStatus, 'RESIGNED') === 0) &&
                strcasecmp($oldStatus, 'RETIRED') !== 0 && strcasecmp($oldStatus, 'RESIGNED') !== 0) {
                $statusText = strcasecmp($newStatus, 'RETIRED') === 0 ? 'RETIRED' : 'RESIGNED';
                $successMessage = "Employment status updated to {$statusText}. Employee has been moved to the Inactive Employee section.";
            } else {
                $successMessage = 'Employment status updated successfully.';
            }

            // Reload employee data
            $stmt = $pdo->prepare('SELECT * FROM employees WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $employee = $stmt->fetch();
        } catch (PDOException $e) {
            $errorMessage = 'Error updating employment status: ' . htmlspecialchars($e->getMessage());
        }
    } elseif ($action === 'update_employee_id') {
        $newEmployeeId = trim($_POST['employee_number'] ?? '');

        try {
            $stmt = $pdo->prepare('UPDATE employees SET employee_number = :employee_number WHERE id = :id');
            $stmt->execute([
                ':employee_number' => $newEmployeeId !== '' ? $newEmployeeId : null,
                ':id' => $id
            ]);

            $successMessage = $newEmployeeId !== ''
                ? 'Employee ID updated successfully.'
                : 'Employee ID removed successfully.';

            // Reload employee data
            $stmt = $pdo->prepare('SELECT * FROM employees WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $employee = $stmt->fetch();
        } catch (PDOException $e) {
            $errorMessage = 'Error updating employee ID: ' . htmlspecialchars($e->getMessage());
        }
    } elseif ($action === 'update_office') {
        $newOffice = trim($_POST['office'] ?? '');

        try {
            $stmt = $pdo->prepare('UPDATE employees SET office = :office WHERE id = :id');
            $stmt->execute([
                ':office' => $newOffice !== '' ? $newOffice : null,
                ':id' => $id
            ]);

            // Log activity
            logActivity('employee_update', "Office / Department updated to: {$newOffice}", 'employees', $id);
            $successMessage = 'Office / Department updated successfully.';

            // Reload employee data
            $stmt = $pdo->prepare('SELECT * FROM employees WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $employee = $stmt->fetch();
        } catch (PDOException $e) {
            $errorMessage = 'Error updating office / department: ' . htmlspecialchars($e->getMessage());
        }
    } elseif ($action === 'add_training') {
        $title = trim($_POST['training_title'] ?? '');
        $provider = trim($_POST['training_provider'] ?? '');
        $location = trim($_POST['training_location'] ?? '');
        $dateFrom = trim($_POST['training_date_from'] ?? '');
        $dateTo = trim($_POST['training_date_to'] ?? '');
        $hours = trim($_POST['training_hours'] ?? '');
        $remarks = trim($_POST['training_remarks'] ?? '');

        if ($title === '') {
            $errorMessage = 'Training title is required.';
        } else {
            $stmtTraining = $pdo->prepare(
                'INSERT INTO employee_trainings (
                    employee_id, title, provider, location,
                    date_from, date_to, hours, remarks
                ) VALUES (
                    :employee_id, :title, :provider, :location,
                    :date_from, :date_to, :hours, :remarks
                )'
            );

            $stmtTraining->execute([
                ':employee_id' => $id,
                ':title' => $title,
                ':provider' => $provider !== '' ? $provider : null,
                ':location' => $location !== '' ? $location : null,
                ':date_from' => $dateFrom !== '' ? $dateFrom : null,
                ':date_to' => $dateTo !== '' ? $dateTo : null,
                ':hours' => $hours !== '' ? $hours : null,
                ':remarks' => $remarks !== '' ? $remarks : null,
            ]);

            $successMessage = 'Training record has been added.';
        }
    } elseif ($action === 'add_leave') {
        $leaveType = trim($_POST['leave_type'] ?? '');
        $leaveDateFrom = trim($_POST['leave_date_from'] ?? '');
        $leaveDateTo = trim($_POST['leave_date_to'] ?? '');
        $leaveDays = trim($_POST['leave_days'] ?? '');
        $leaveRemarks = trim($_POST['leave_remarks'] ?? '');

        if ($leaveType === '') {
            $errorMessage = 'Leave type is required.';
        } else {
            $stmtLeave = $pdo->prepare(
                'INSERT INTO employee_leaves (
                    employee_id, leave_type, date_from, date_to, days, remarks
                ) VALUES (
                    :employee_id, :leave_type, :date_from, :date_to, :days, :remarks
                )'
            );

            $stmtLeave->execute([
                ':employee_id' => $id,
                ':leave_type' => $leaveType,
                ':date_from' => $leaveDateFrom !== '' ? $leaveDateFrom : null,
                ':date_to' => $leaveDateTo !== '' ? $leaveDateTo : null,
                ':days' => $leaveDays !== '' ? $leaveDays : null,
                ':remarks' => $leaveRemarks !== '' ? $leaveRemarks : null,
            ]);

            $successMessage = 'Leave record has been added.';
        }
    } elseif ($action === 'add_service_record') {
        $srPosition = trim($_POST['sr_position'] ?? '');
        $srStatus = trim($_POST['sr_status'] ?? '');
        $srSalary = trim($_POST['sr_salary'] ?? '');
        $srDateFrom = trim($_POST['sr_date_from'] ?? '');
        $srDateTo = trim($_POST['sr_date_to'] ?? '');
        $srPlaceOf = trim($_POST['sr_place_of'] ?? '');
        $srBranch = trim($_POST['sr_branch'] ?? '');
        $srAssignment = trim($_POST['sr_assignment'] ?? '');
        $srLvAbs = trim($_POST['sr_lv_abs'] ?? '');
        $srWoPay = trim($_POST['sr_wo_pay'] ?? '');
        $srSeparationDate = trim($_POST['sr_separation_date'] ?? '');
        $srSeparationCause = trim($_POST['sr_separation_cause'] ?? '');
        $srRemarks = trim($_POST['sr_remarks'] ?? '');

        if ($srPosition === '') {
            $errorMessage = 'Designation is required for a service record.';
        } else {
            $stmtSR = $pdo->prepare(
                'INSERT INTO employee_service_records (
                    employee_id, position, status, salary,
                    date_from, date_to, place_of, branch, assignment, lv_abs,
                    wo_pay, separation_date, separation_cause, remarks
                ) VALUES (
                    :employee_id, :position, :status, :salary,
                    :date_from, :date_to, :place_of, :branch, :assignment, :lv_abs,
                    :wo_pay, :separation_date, :separation_cause, :remarks
                )'
            );

            $stmtSR->execute([
                ':employee_id' => $id,
                ':position' => $srPosition,
                ':status' => $srStatus !== '' ? $srStatus : null,
                ':salary' => $srSalary !== '' ? $srSalary : null,
                ':date_from' => $srDateFrom !== '' ? $srDateFrom : null,
                ':date_to' => $srDateTo !== '' ? $srDateTo : null,
                ':place_of' => $srPlaceOf !== '' ? $srPlaceOf : null,
                ':branch' => $srBranch !== '' ? $srBranch : null,
                ':assignment' => $srAssignment !== '' ? $srAssignment : null,
                ':lv_abs' => $srLvAbs !== '' ? $srLvAbs : null,
                ':wo_pay' => $srWoPay !== '' ? $srWoPay : null,
                ':separation_date' => $srSeparationDate !== '' ? $srSeparationDate : null,
                ':separation_cause' => $srSeparationCause !== '' ? $srSeparationCause : null,
                ':remarks' => $srRemarks !== '' ? $srRemarks : null,
            ]);

            $successMessage = 'Service record has been added.';
        }
    } elseif ($action === 'add_appointment') {
        $typeLabel = trim($_POST['type_label'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $itemNumber = trim($_POST['item_number'] ?? '');
        $salaryGrade = trim($_POST['salary_grade'] ?? '');
        $appointmentDate = trim($_POST['appointment_date'] ?? '');
        $salary = trim($_POST['salary'] ?? '');

        if ($typeLabel === '' || $position === '') {
            $errorMessage = 'Type and position are required for an appointment.';
        } else {
            // Determine next sequence number
            $stmtSeq = $pdo->prepare(
                'SELECT COALESCE(MAX(sequence_no), 0) AS max_seq
                 FROM appointments
                 WHERE employee_id = :id'
            );
            $stmtSeq->execute([':id' => $id]);
            $maxSeq = (int)$stmtSeq->fetchColumn();
            $nextSeq = $maxSeq + 1;

            $stmtAppt = $pdo->prepare(
                'INSERT INTO appointments (
                    employee_id, sequence_no, type_label,
                    position, item_number, salary_grade, appointment_date, salary
                ) VALUES (
                    :employee_id, :sequence_no, :type_label,
                    :position, :item_number, :salary_grade, :appointment_date, :salary
                )'
            );

            $stmtAppt->execute([
                ':employee_id' => $id,
                ':sequence_no' => $nextSeq,
                ':type_label' => $typeLabel,
                ':position' => $position,
                ':item_number' => $itemNumber !== '' ? $itemNumber : null,
                ':salary_grade' => $salaryGrade !== '' ? $salaryGrade : null,
                ':appointment_date' => $appointmentDate !== '' ? $appointmentDate : null,
                ':salary' => $salary !== '' ? $salary : null,
            ]);

            $successMessage = 'New appointment has been added.';
        }
    } elseif ($action === 'edit_training') {
        $trainingId = (int)($_POST['training_id'] ?? 0);
        $title = trim($_POST['training_title'] ?? '');
        $provider = trim($_POST['training_provider'] ?? '');
        $location = trim($_POST['training_location'] ?? '');
        $dateFrom = trim($_POST['training_date_from'] ?? '');
        $dateTo = trim($_POST['training_date_to'] ?? '');
        $hours = trim($_POST['training_hours'] ?? '');
        $remarks = trim($_POST['training_remarks'] ?? '');

        if ($title === '' || $trainingId === 0) {
            $errorMessage = 'Training title and ID are required.';
        } else {
            $stmt = $pdo->prepare(
                'UPDATE employee_trainings SET
                    title = :title, provider = :provider, location = :location,
                    date_from = :date_from, date_to = :date_to, hours = :hours, remarks = :remarks
                WHERE id = :id AND employee_id = :employee_id'
            );
            $stmt->execute([
                ':id' => $trainingId,
                ':employee_id' => $id,
                ':title' => $title,
                ':provider' => $provider !== '' ? $provider : null,
                ':location' => $location !== '' ? $location : null,
                ':date_from' => $dateFrom !== '' ? $dateFrom : null,
                ':date_to' => $dateTo !== '' ? $dateTo : null,
                ':hours' => $hours !== '' ? $hours : null,
                ':remarks' => $remarks !== '' ? $remarks : null,
            ]);
            $successMessage = 'Training record has been updated.';
        }
    } elseif ($action === 'delete_training') {
        $trainingId = (int)($_POST['training_id'] ?? 0);
        if ($trainingId > 0) {
            $stmt = $pdo->prepare('DELETE FROM employee_trainings WHERE id = :id AND employee_id = :employee_id');
            $stmt->execute([':id' => $trainingId, ':employee_id' => $id]);
            $successMessage = 'Training record has been deleted.';
        }
    } elseif ($action === 'edit_leave') {
        $leaveId = (int)($_POST['leave_id'] ?? 0);
        $leaveType = trim($_POST['leave_type'] ?? '');
        $leaveDateFrom = trim($_POST['leave_date_from'] ?? '');
        $leaveDateTo = trim($_POST['leave_date_to'] ?? '');
        $leaveDays = trim($_POST['leave_days'] ?? '');
        $leaveRemarks = trim($_POST['leave_remarks'] ?? '');

        if ($leaveType === '' || $leaveId === 0) {
            $errorMessage = 'Leave type and ID are required.';
        } else {
            $stmt = $pdo->prepare(
                'UPDATE employee_leaves SET
                    leave_type = :leave_type, date_from = :date_from, date_to = :date_to,
                    days = :days, remarks = :remarks
                WHERE id = :id AND employee_id = :employee_id'
            );
            $stmt->execute([
                ':id' => $leaveId,
                ':employee_id' => $id,
                ':leave_type' => $leaveType,
                ':date_from' => $leaveDateFrom !== '' ? $leaveDateFrom : null,
                ':date_to' => $leaveDateTo !== '' ? $leaveDateTo : null,
                ':days' => $leaveDays !== '' ? $leaveDays : null,
                ':remarks' => $leaveRemarks !== '' ? $leaveRemarks : null,
            ]);
            $successMessage = 'Leave record has been updated.';
        }
    } elseif ($action === 'delete_leave') {
        $leaveId = (int)($_POST['leave_id'] ?? 0);
        if ($leaveId > 0) {
            $stmt = $pdo->prepare('DELETE FROM employee_leaves WHERE id = :id AND employee_id = :employee_id');
            $stmt->execute([':id' => $leaveId, ':employee_id' => $id]);
            $successMessage = 'Leave record has been deleted.';
        }
    } elseif ($action === 'edit_service_record') {
        $srId = (int)($_POST['sr_id'] ?? 0);
        $srPosition = trim($_POST['sr_position'] ?? '');
        $srStatus = trim($_POST['sr_status'] ?? '');
        $srSalary = trim($_POST['sr_salary'] ?? '');
        $srDateFrom = trim($_POST['sr_date_from'] ?? '');
        $srDateTo = trim($_POST['sr_date_to'] ?? '');
        $srPlaceOf = trim($_POST['sr_place_of'] ?? '');
        $srBranch = trim($_POST['sr_branch'] ?? '');
        $srAssignment = trim($_POST['sr_assignment'] ?? '');
        $srLvAbs = trim($_POST['sr_lv_abs'] ?? '');
        $srWoPay = trim($_POST['sr_wo_pay'] ?? '');
        $srSeparationDate = trim($_POST['sr_separation_date'] ?? '');
        $srSeparationCause = trim($_POST['sr_separation_cause'] ?? '');
        $srRemarks = trim($_POST['sr_remarks'] ?? '');

        if ($srPosition === '' || $srId === 0) {
            $errorMessage = 'Designation and ID are required.';
        } else {
            $stmt = $pdo->prepare(
                'UPDATE employee_service_records SET
                    position = :position, status = :status, salary = :salary,
                    date_from = :date_from, date_to = :date_to, place_of = :place_of, branch = :branch,
                    assignment = :assignment, lv_abs = :lv_abs, wo_pay = :wo_pay,
                    separation_date = :separation_date, separation_cause = :separation_cause, remarks = :remarks
                WHERE id = :id AND employee_id = :employee_id'
            );
            $stmt->execute([
                ':id' => $srId,
                ':employee_id' => $id,
                ':position' => $srPosition,
                ':status' => $srStatus !== '' ? $srStatus : null,
                ':salary' => $srSalary !== '' ? $srSalary : null,
                ':date_from' => $srDateFrom !== '' ? $srDateFrom : null,
                ':date_to' => $srDateTo !== '' ? $srDateTo : null,
                ':place_of' => $srPlaceOf !== '' ? $srPlaceOf : null,
                ':branch' => $srBranch !== '' ? $srBranch : null,
                ':assignment' => $srAssignment !== '' ? $srAssignment : null,
                ':lv_abs' => $srLvAbs !== '' ? $srLvAbs : null,
                ':wo_pay' => $srWoPay !== '' ? $srWoPay : null,
                ':separation_date' => $srSeparationDate !== '' ? $srSeparationDate : null,
                ':separation_cause' => $srSeparationCause !== '' ? $srSeparationCause : null,
                ':remarks' => $srRemarks !== '' ? $srRemarks : null,
            ]);
            $successMessage = 'Service record has been updated.';
        }
    } elseif ($action === 'delete_service_record') {
        $srId = (int)($_POST['sr_id'] ?? 0);
        if ($srId > 0) {
            $stmt = $pdo->prepare('DELETE FROM employee_service_records WHERE id = :id AND employee_id = :employee_id');
            $stmt->execute([':id' => $srId, ':employee_id' => $id]);
            $successMessage = 'Service record has been deleted.';
        }
    } elseif ($action === 'edit_appointment') {
        $apptId = (int)($_POST['appointment_id'] ?? 0);
        $typeLabel = trim($_POST['type_label'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $itemNumber = trim($_POST['item_number'] ?? '');
        $salaryGrade = trim($_POST['salary_grade'] ?? '');
        $appointmentDate = trim($_POST['appointment_date'] ?? '');
        $salary = trim($_POST['salary'] ?? '');

        if ($typeLabel === '' || $position === '' || $apptId === 0) {
            $errorMessage = 'Type, position, and ID are required.';
        } else {
            $stmt = $pdo->prepare(
                'UPDATE appointments SET
                    type_label = :type_label, position = :position, item_number = :item_number,
                    salary_grade = :salary_grade, appointment_date = :appointment_date, salary = :salary
                WHERE id = :id AND employee_id = :employee_id'
            );
            $stmt->execute([
                ':id' => $apptId,
                ':employee_id' => $id,
                ':type_label' => $typeLabel,
                ':position' => $position,
                ':item_number' => $itemNumber !== '' ? $itemNumber : null,
                ':salary_grade' => $salaryGrade !== '' ? $salaryGrade : null,
                ':appointment_date' => $appointmentDate !== '' ? $appointmentDate : null,
                ':salary' => $salary !== '' ? $salary : null,
            ]);
            $successMessage = 'Appointment has been updated.';
        }
    } elseif ($action === 'delete_appointment') {
        $apptId = (int)($_POST['appointment_id'] ?? 0);
        if ($apptId > 0) {
            $stmt = $pdo->prepare('DELETE FROM appointments WHERE id = :id AND employee_id = :employee_id');
            $stmt->execute([':id' => $apptId, ':employee_id' => $id]);
            $successMessage = 'Appointment has been deleted.';
        }
    } elseif ($action === 'add_education') {
        $level = trim($_POST['edu_level'] ?? '');
        $schoolName = trim($_POST['edu_school_name'] ?? '');
        $degreeCourse = trim($_POST['edu_degree_course'] ?? '');
        $periodFrom = trim($_POST['edu_period_from'] ?? '');
        $periodTo = trim($_POST['edu_period_to'] ?? '');
        $highestLevelUnits = trim($_POST['edu_highest_level_units'] ?? '');
        $yearGraduated = trim($_POST['edu_year_graduated'] ?? '');
        $scholarshipHonors = trim($_POST['edu_scholarship_honors'] ?? '');

        if ($level === '' || $schoolName === '') {
            $errorMessage = 'Level and school name are required.';
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO employee_educational_background (
                    employee_id, level, school_name, degree_course,
                    period_from, period_to, highest_level_units, year_graduated, scholarship_honors
                ) VALUES (
                    :employee_id, :level, :school_name, :degree_course,
                    :period_from, :period_to, :highest_level_units, :year_graduated, :scholarship_honors
                )'
            );
            $stmt->execute([
                ':employee_id' => $id,
                ':level' => $level,
                ':school_name' => $schoolName,
                ':degree_course' => $degreeCourse !== '' ? $degreeCourse : null,
                ':period_from' => $periodFrom !== '' ? $periodFrom : null,
                ':period_to' => $periodTo !== '' ? $periodTo : null,
                ':highest_level_units' => $highestLevelUnits !== '' ? $highestLevelUnits : null,
                ':year_graduated' => $yearGraduated !== '' ? $yearGraduated : null,
                ':scholarship_honors' => $scholarshipHonors !== '' ? $scholarshipHonors : null,
            ]);
            $successMessage = 'Educational background has been added.';
        }
    } elseif ($action === 'edit_education') {
        $eduId = (int)($_POST['edu_id'] ?? 0);
        $level = trim($_POST['edu_level'] ?? '');
        $schoolName = trim($_POST['edu_school_name'] ?? '');
        $degreeCourse = trim($_POST['edu_degree_course'] ?? '');
        $periodFrom = trim($_POST['edu_period_from'] ?? '');
        $periodTo = trim($_POST['edu_period_to'] ?? '');
        $highestLevelUnits = trim($_POST['edu_highest_level_units'] ?? '');
        $yearGraduated = trim($_POST['edu_year_graduated'] ?? '');
        $scholarshipHonors = trim($_POST['edu_scholarship_honors'] ?? '');

        if ($level === '' || $schoolName === '' || $eduId === 0) {
            $errorMessage = 'Level, school name, and ID are required.';
        } else {
            $stmt = $pdo->prepare(
                'UPDATE employee_educational_background SET
                    level = :level, school_name = :school_name, degree_course = :degree_course,
                    period_from = :period_from, period_to = :period_to,
                    highest_level_units = :highest_level_units, year_graduated = :year_graduated,
                    scholarship_honors = :scholarship_honors
                WHERE id = :id AND employee_id = :employee_id'
            );
            $stmt->execute([
                ':id' => $eduId,
                ':employee_id' => $id,
                ':level' => $level,
                ':school_name' => $schoolName,
                ':degree_course' => $degreeCourse !== '' ? $degreeCourse : null,
                ':period_from' => $periodFrom !== '' ? $periodFrom : null,
                ':period_to' => $periodTo !== '' ? $periodTo : null,
                ':highest_level_units' => $highestLevelUnits !== '' ? $highestLevelUnits : null,
                ':year_graduated' => $yearGraduated !== '' ? $yearGraduated : null,
                ':scholarship_honors' => $scholarshipHonors !== '' ? $scholarshipHonors : null,
            ]);
            $successMessage = 'Educational background has been updated.';
        }
    } elseif ($action === 'delete_education') {
        $eduId = (int)($_POST['edu_id'] ?? 0);
        if ($eduId > 0) {
            $stmt = $pdo->prepare('DELETE FROM employee_educational_background WHERE id = :id AND employee_id = :employee_id');
            $stmt->execute([':id' => $eduId, ':employee_id' => $id]);
            $successMessage = 'Educational background has been deleted.';
        }
    } elseif ($action === 'add_work_experience') {
        $weDateFrom = trim($_POST['we_date_from'] ?? '');
        $weDateTo = trim($_POST['we_date_to'] ?? '');
        $wePositionTitle = trim($_POST['we_position_title'] ?? '');
        $weDepartmentAgency = trim($_POST['we_department_agency'] ?? '');
        $weMonthlySalary = trim($_POST['we_monthly_salary'] ?? '');
        $weSalaryGradeStep = trim($_POST['we_salary_grade_step'] ?? '');
        $weStatusAppointment = trim($_POST['we_status_appointment'] ?? '');
        $weGovtService = trim($_POST['we_govt_service'] ?? 'YES');
        $weDescriptionDuties = trim($_POST['we_description_duties'] ?? '');

        if ($weDateFrom === '' || $wePositionTitle === '' || $weDepartmentAgency === '') {
            $errorMessage = 'Date From, Position Title, and Department/Agency are required.';
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO employee_work_experience (
                    employee_id, date_from, date_to, position_title, department_agency,
                    monthly_salary, salary_grade_step, status_of_appointment, govt_service, description_of_duties
                ) VALUES (
                    :employee_id, :date_from, :date_to, :position_title, :department_agency,
                    :monthly_salary, :salary_grade_step, :status_of_appointment, :govt_service, :description_of_duties
                )'
            );
            $stmt->execute([
                ':employee_id' => $id,
                ':date_from' => $weDateFrom,
                ':date_to' => $weDateTo !== '' ? $weDateTo : null,
                ':position_title' => $wePositionTitle,
                ':department_agency' => $weDepartmentAgency,
                ':monthly_salary' => $weMonthlySalary !== '' ? (float)$weMonthlySalary : null,
                ':salary_grade_step' => $weSalaryGradeStep !== '' ? $weSalaryGradeStep : null,
                ':status_of_appointment' => $weStatusAppointment !== '' ? $weStatusAppointment : null,
                ':govt_service' => $weGovtService,
                ':description_of_duties' => $weDescriptionDuties !== '' ? $weDescriptionDuties : null,
            ]);
            $successMessage = 'Work experience has been added.';
        }
    } elseif ($action === 'edit_work_experience') {
        $weId = (int)($_POST['we_id'] ?? 0);
        $weDateFrom = trim($_POST['we_date_from'] ?? '');
        $weDateTo = trim($_POST['we_date_to'] ?? '');
        $wePositionTitle = trim($_POST['we_position_title'] ?? '');
        $weDepartmentAgency = trim($_POST['we_department_agency'] ?? '');
        $weMonthlySalary = trim($_POST['we_monthly_salary'] ?? '');
        $weSalaryGradeStep = trim($_POST['we_salary_grade_step'] ?? '');
        $weStatusAppointment = trim($_POST['we_status_appointment'] ?? '');
        $weGovtService = trim($_POST['we_govt_service'] ?? 'YES');
        $weDescriptionDuties = trim($_POST['we_description_duties'] ?? '');

        if ($weDateFrom === '' || $wePositionTitle === '' || $weDepartmentAgency === '' || $weId === 0) {
            $errorMessage = 'Date From, Position Title, Department/Agency, and ID are required.';
        } else {
            $stmt = $pdo->prepare(
                'UPDATE employee_work_experience SET
                    date_from = :date_from, date_to = :date_to, position_title = :position_title,
                    department_agency = :department_agency, monthly_salary = :monthly_salary,
                    salary_grade_step = :salary_grade_step, status_of_appointment = :status_of_appointment,
                    govt_service = :govt_service, description_of_duties = :description_of_duties
                WHERE id = :id AND employee_id = :employee_id'
            );
            $stmt->execute([
                ':id' => $weId,
                ':employee_id' => $id,
                ':date_from' => $weDateFrom,
                ':date_to' => $weDateTo !== '' ? $weDateTo : null,
                ':position_title' => $wePositionTitle,
                ':department_agency' => $weDepartmentAgency,
                ':monthly_salary' => $weMonthlySalary !== '' ? (float)$weMonthlySalary : null,
                ':salary_grade_step' => $weSalaryGradeStep !== '' ? $weSalaryGradeStep : null,
                ':status_of_appointment' => $weStatusAppointment !== '' ? $weStatusAppointment : null,
                ':govt_service' => $weGovtService,
                ':description_of_duties' => $weDescriptionDuties !== '' ? $weDescriptionDuties : null,
            ]);
            $successMessage = 'Work experience has been updated.';
        }
    } elseif ($action === 'delete_work_experience') {
        $weId = (int)($_POST['we_id'] ?? 0);
        if ($weId > 0) {
            $stmt = $pdo->prepare('DELETE FROM employee_work_experience WHERE id = :id AND employee_id = :employee_id');
            $stmt->execute([':id' => $weId, ':employee_id' => $id]);
            $successMessage = 'Work experience has been deleted.';
        }
    } elseif ($action === 'add_award') {
        $awardTitle = trim($_POST['award_title'] ?? '');
        $awardLevel = trim($_POST['award_level'] ?? '');
        $awardBody = trim($_POST['award_body'] ?? '');
        $awardDate = trim($_POST['award_date'] ?? '');
        $awardRemarks = trim($_POST['award_remarks'] ?? '');
        $awardDescription = trim($_POST['award_description'] ?? '');

        if ($awardTitle === '') {
            $errorMessage = 'Award or recognition title is required.';
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO employee_awards (
                    employee_id, title, award_level, awarding_body, award_date, remarks, description
                ) VALUES (
                    :employee_id, :title, :award_level, :awarding_body, :award_date, :remarks, :description
                )'
            );
            $stmt->execute([
                ':employee_id' => $id,
                ':title' => $awardTitle,
                ':award_level' => $awardLevel !== '' ? $awardLevel : null,
                ':awarding_body' => $awardBody !== '' ? $awardBody : null,
                ':award_date' => $awardDate !== '' ? $awardDate : null,
                ':remarks' => $awardRemarks !== '' ? $awardRemarks : null,
                ':description' => $awardDescription !== '' ? $awardDescription : null,
            ]);
            $successMessage = 'Award / recognition has been added.';
        }
    } elseif ($action === 'edit_award') {
        $awardId = (int)($_POST['award_id'] ?? 0);
        $awardTitle = trim($_POST['award_title'] ?? '');
        $awardLevel = trim($_POST['award_level'] ?? '');
        $awardBody = trim($_POST['award_body'] ?? '');
        $awardDate = trim($_POST['award_date'] ?? '');
        $awardRemarks = trim($_POST['award_remarks'] ?? '');
        $awardDescription = trim($_POST['award_description'] ?? '');

        if ($awardTitle === '' || $awardId === 0) {
            $errorMessage = 'Award title and ID are required.';
        } else {
            $stmt = $pdo->prepare(
                'UPDATE employee_awards SET
                    title = :title,
                    award_level = :award_level,
                    awarding_body = :awarding_body,
                    award_date = :award_date,
                    remarks = :remarks,
                    description = :description
                WHERE id = :id AND employee_id = :employee_id'
            );
            $stmt->execute([
                ':id' => $awardId,
                ':employee_id' => $id,
                ':title' => $awardTitle,
                ':award_level' => $awardLevel !== '' ? $awardLevel : null,
                ':awarding_body' => $awardBody !== '' ? $awardBody : null,
                ':award_date' => $awardDate !== '' ? $awardDate : null,
                ':remarks' => $awardRemarks !== '' ? $awardRemarks : null,
                ':description' => $awardDescription !== '' ? $awardDescription : null,
            ]);
            $successMessage = 'Award / recognition has been updated.';
        }
    } elseif ($action === 'delete_award') {
        $awardId = (int)($_POST['award_id'] ?? 0);
        if ($awardId > 0) {
            $stmt = $pdo->prepare('DELETE FROM employee_awards WHERE id = :id AND employee_id = :employee_id');
            $stmt->execute([':id' => $awardId, ':employee_id' => $id]);
            $successMessage = 'Award / recognition has been deleted.';
        }
    }
}

/**
 * Load Employee Record with Enhanced Error Handling
 *
 * Fetches the employee record and validates its existence
 * with proper error handling and logging.
 */
try {
    $stmt = $pdo->prepare('SELECT * FROM employees WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);

    // Validate employee record exists
    if (!$employee || empty($employee)) {
        // Log access attempt to non-existent employee
        if (function_exists('logActivity')) {
            logActivity(
                'EMPLOYEE_NOT_FOUND',
                "Access attempt to non-existent employee with ID: {$id}",
                'employees',
                $id
            );
        }

        // Set proper HTTP status code and redirect
        http_response_code(404); // Not Found
        header('Location: index.php?error=employee_not_found');
        exit;
    }

    // Additional validation: ensure employee record has required fields
    if (!isset($employee['id']) || $employee['id'] != $id) {
        // Data integrity check failed
        $retrievedId = $employee['id'] ?? 'missing';
        if (function_exists('logActivity')) {
            logActivity(
                'DATA_INTEGRITY_ERROR',
                "Data integrity issue detected. Expected ID: {$id}, Retrieved ID: {$retrievedId}",
                'employees',
                $id
            );
        }

        http_response_code(500); // Internal Server Error
        header('Location: index.php?error=data_integrity_issue');
        exit;
    }

} catch (PDOException $e) {
    // Database error handling
    error_log('Database error in employee.php: ' . $e->getMessage());

    if (function_exists('logActivity')) {
        logActivity(
            'DATABASE_ERROR',
            "Database error while loading employee ID {$id}: " . $e->getMessage(),
            'employees',
            $id
        );
    }

    http_response_code(500); // Internal Server Error
    header('Location: index.php?error=database_error');
    exit;
} catch (Exception $e) {
    // General error handling
    error_log('Unexpected error in employee.php: ' . $e->getMessage());

    if (function_exists('logActivity')) {
        logActivity(
            'UNEXPECTED_ERROR',
            "Unexpected error while loading employee ID {$id}: " . $e->getMessage(),
            'employees',
            $id
        );
    }

    http_response_code(500); // Internal Server Error
    header('Location: index.php?error=unexpected_error');
    exit;
}

// Load appointment / promotion history
$stmt = $pdo->prepare(
    'SELECT * FROM appointments WHERE employee_id = :id ORDER BY sequence_no ASC'
);
$stmt->execute([':id' => $id]);
$appointments = $stmt->fetchAll();

// Load structured trainings
$stmt = $pdo->prepare(
    'SELECT * FROM employee_trainings WHERE employee_id = :id ORDER BY date_from DESC, id DESC'
);
$stmt->execute([':id' => $id]);
$trainingRecords = $stmt->fetchAll();

// Load structured leave records
$stmt = $pdo->prepare(
    'SELECT * FROM employee_leaves WHERE employee_id = :id ORDER BY date_from DESC, id DESC'
);
$stmt->execute([':id' => $id]);
$leaveRecords = $stmt->fetchAll();

// Load structured service records
$stmt = $pdo->prepare(
    'SELECT * FROM employee_service_records WHERE employee_id = :id ORDER BY date_from DESC, id DESC'
);
$stmt->execute([':id' => $id]);
$serviceRecords = $stmt->fetchAll();

// Load educational background
$stmt = $pdo->prepare(
    'SELECT * FROM employee_educational_background WHERE employee_id = :id ORDER BY
        CASE level
            WHEN "ELEMENTARY" THEN 1
            WHEN "HIGH SCHOOL" THEN 2
            WHEN "VOCATIONAL" THEN 3
            WHEN "COLLEGE" THEN 4
            WHEN "GRADUATE STUDIES" THEN 5
            ELSE 6
        END, id ASC'
);
$stmt->execute([':id' => $id]);
$educationalBackground = $stmt->fetchAll();

// Load work experience
$stmt = $pdo->prepare(
    'SELECT * FROM employee_work_experience WHERE employee_id = :id ORDER BY date_from DESC, id DESC'
);
$stmt->execute([':id' => $id]);
$workExperience = $stmt->fetchAll();

// Load awards & recognitions
$stmt = $pdo->prepare(
    'SELECT * FROM employee_awards WHERE employee_id = :id ORDER BY award_date DESC, id DESC'
);
$stmt->execute([':id' => $id]);
$awardRecords = $stmt->fetchAll();

require_once 'header.php';

$fullName = trim($employee['last_name'] . ', ' . $employee['first_name'] . ($employee['middle_name'] ? ' ' . $employee['middle_name'] : ''));
$firstLetter = strtoupper(substr($employee['last_name'], 0, 1));
?>

<!-- Breadcrumb -->
<ol class="breadcrumb mb-3">
    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="employee_directory.php">Employee List</a></li>
    <li class="breadcrumb-item"><a href="employees.php?letter=<?= $firstLetter ?>">Letter <?= $firstLetter ?></a></li>
    <li class="breadcrumb-item active"><?= htmlspecialchars($employee['last_name'] . ', ' . $employee['first_name']) ?></li>
</ol>

<!-- Professional Header Card -->
<div class="card border-0 shadow-sm mb-4 professional-header-card">
    <div class="card-body p-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center mb-3">
                    <div class="employee-avatar me-3">
                        <div class="avatar-circle">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                    <div>
                        <h1 class="h2 mb-1 fw-bold text-dark"><?= htmlspecialchars($fullName) ?></h1>
                        <div class="employee-meta">
                            <span
                                class="badge bg-primary me-2 cursor-pointer employee-id-badge"
                                data-bs-toggle="modal"
                                data-bs-target="#changeEmployeeIdModal"
                                title="Click to change employee ID"
                                style="cursor: pointer; transition: all 0.2s;"
                                onmouseover="this.style.transform='scale(1.05)'"
                                onmouseout="this.style.transform='scale(1)'"
                            >
                                <i class="fas fa-id-badge me-1"></i>
                                ID: <?= htmlspecialchars($employee['employee_number'] ?: 'Not assigned') ?>
                                <i class="fas fa-edit ms-1" style="font-size: 0.7em; opacity: 0.8;"></i>
                            </span>
                            <?php if ($employee['employment_status']): ?>
                                <span
                                    class="badge bg-success cursor-pointer employment-status-badge me-2"
                                    data-bs-toggle="modal"
                                    data-bs-target="#changeEmploymentStatusModal"
                                    title="Click to change employment status"
                                    style="cursor: pointer; transition: all 0.2s;"
                                    onmouseover="this.style.transform='scale(1.05)'"
                                    onmouseout="this.style.transform='scale(1)'"
                                >
                                    <i class="fas fa-briefcase me-1"></i>
                                    <?= htmlspecialchars($employee['employment_status']) ?>
                                    <i class="fas fa-edit ms-1" style="font-size: 0.7em; opacity: 0.8;"></i>
                                </span>
                            <?php else: ?>
                                <button
                                class="btn btn-sm btn-success text-white me-2"
                                    data-bs-toggle="modal"
                                    data-bs-target="#changeEmploymentStatusModal"
                                >
                                    <i class="fas fa-plus me-1"></i>Set Status
                                </button>
                            <?php endif; ?>
                            <span
                                class="badge bg-success cursor-pointer me-2"
                                data-bs-toggle="modal"
                                data-bs-target="#changeOfficeModal"
                                title="Click to change office / department"
                                style="cursor: pointer; transition: all 0.2s;"
                                onmouseover="this.style.transform='scale(1.05)'"
                                onmouseout="this.style.transform='scale(1)'"
                            >
                                <i class="fas fa-building me-1"></i>
                                <?= htmlspecialchars($employee['office'] ?: 'Not assigned') ?>
                                <i class="fas fa-edit ms-1" style="font-size: 0.7em; opacity: 0.8;"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <div class="d-flex flex-column flex-md-row gap-2 justify-content-md-end">
                    <a
                        href="generate_employee_pdf.php?id=<?= $id ?>"
                        class="btn btn-success btn-sm"
                        target="_blank"
                        style="color: #ffffff !important; white-space: nowrap;"
                    >
                        <i class="fas fa-file-pdf me-1"></i> PDF Report
                    </a>
                    <a
                        href="generate_employee_excel.php?id=<?= $id ?>"
                        class="btn btn-info btn-sm"
                        target="_blank"
                        style="color: #ffffff !important; white-space: nowrap;"
                    >
                        <i class="fas fa-file-excel me-1"></i> Excel Report
                    </a>
                    <button
                        class="btn btn-primary btn-sm"
                        type="button"
                        data-bs-toggle="modal"
                        data-bs-target="#editEmployeeModal"
                    >
                        <i class="fas fa-edit me-1"></i> Edit Profile
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
        <div><?= htmlspecialchars($successMessage) ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Edit Employee Modal -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-labelledby="editEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEmployeeModalLabel">
                    <i class="fas fa-user-edit me-2 text-primary"></i>
                    Edit Employee Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" autocomplete="off">
                    <input type="hidden" name="action" value="update_employee">

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
                            value="<?= htmlspecialchars($employee['last_name'] ?? '') ?>"
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
                            value="<?= htmlspecialchars($employee['first_name'] ?? '') ?>"
                        >
                    </div>
                    <div class="col-md-4">
                        <label for="middle_name" class="form-label">Middle Name</label>
                        <input
                            type="text"
                            class="form-control"
                            id="middle_name"
                            name="middle_name"
                            value="<?= htmlspecialchars($employee['middle_name'] ?? '') ?>"
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
                            value="<?= htmlspecialchars($employee['birthdate'] ?? '') ?>"
                        >
                    </div>
                    <div class="col-md-9">
                        <label for="home_address" class="form-label">Home Address</label>
                        <input
                            type="text"
                            class="form-control"
                            id="home_address"
                            name="home_address"
                            value="<?= htmlspecialchars($employee['home_address'] ?? '') ?>"
                        >
                    </div>
                    <div class="col-md-4">
                        <label for="office" class="form-label">Office / Department</label>
                        <select
                            class="form-select"
                            id="office"
                            name="office"
                        >
                            <option value="">Select Office / Department</option>
                            <option value="LTS" <?= ($employee['office'] ?? '') === 'LTS' ? 'selected' : '' ?>>LTS</option>
                            <option value="LEGAL" <?= ($employee['office'] ?? '') === 'LEGAL' ? 'selected' : '' ?>>LEGAL</option>
                            <option value="DARAB" <?= ($employee['office'] ?? '') === 'DARAB' ? 'selected' : '' ?>>DARAB</option>
                            <option value="PBDD" <?= ($employee['office'] ?? '') === 'PBDD' ? 'selected' : '' ?>>PBDD</option>
                            <option value="OPARPO" <?= ($employee['office'] ?? '') === 'OPARPO' ? 'selected' : '' ?>>OPARPO</option>
                            <option value="STOD" <?= ($employee['office'] ?? '') === 'STOD' ? 'selected' : '' ?>>STOD</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="contact_no" class="form-label">Contact #</label>
                        <input
                            type="text"
                            class="form-control"
                            id="contact_no"
                            name="contact_no"
                            value="<?= htmlspecialchars($employee['contact_no'] ?? '') ?>"
                        >
                    </div>
                    <div class="col-md-4">
                        <label for="email" class="form-label">Email Address</label>
                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            name="email"
                            value="<?= htmlspecialchars($employee['email'] ?? '') ?>"
                        >
                    </div>
                    <div class="col-md-4">
                        <label for="civil_status" class="form-label">Status</label>
                        <input
                            type="text"
                            class="form-control"
                            id="civil_status"
                            name="civil_status"
                            value="<?= htmlspecialchars($employee['civil_status'] ?? '') ?>"
                        >
                    </div>
                    <div class="col-md-6">
                        <label for="spouse_name" class="form-label">Name of Spouse</label>
                        <input
                            type="text"
                            class="form-control"
                            id="spouse_name"
                            name="spouse_name"
                            value="<?= htmlspecialchars($employee['spouse_name'] ?? '') ?>"
                        >
                    </div>
                    <div class="col-md-6">
                        <label for="spouse_contact_no" class="form-label">Spouse Contact #</label>
                        <input
                            type="text"
                            class="form-control"
                            id="spouse_contact_no"
                            name="spouse_contact_no"
                            value="<?= htmlspecialchars($employee['spouse_contact_no'] ?? '') ?>"
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
                            value="<?= htmlspecialchars($employee['employee_number'] ?? '') ?>"
                        >
                    </div>
                    <div class="col-md-3">
                        <label for="pagibig_number" class="form-label">Pag-ibig #</label>
                        <input
                            type="text"
                            class="form-control"
                            id="pagibig_number"
                            name="pagibig_number"
                            value="<?= htmlspecialchars($employee['pagibig_number'] ?? '') ?>"
                        >
                    </div>
                    <div class="col-md-3">
                        <label for="philhealth_number" class="form-label">PhilHealth #</label>
                        <input
                            type="text"
                            class="form-control"
                            id="philhealth_number"
                            name="philhealth_number"
                            value="<?= htmlspecialchars($employee['philhealth_number'] ?? '') ?>"
                        >
                    </div>
                    <div class="col-md-3">
                        <label for="tin_number" class="form-label">TIN #</label>
                        <input
                            type="text"
                            class="form-control"
                            id="tin_number"
                            name="tin_number"
                            value="<?= htmlspecialchars($employee['tin_number'] ?? '') ?>"
                        >
                    </div>
                    <div class="col-md-3">
                        <label for="sss_number" class="form-label">SSS #</label>
                        <input
                            type="text"
                            class="form-control"
                            id="sss_number"
                            name="sss_number"
                            value="<?= htmlspecialchars($employee['sss_number'] ?? '') ?>"
                        >
                    </div>
                    <div class="col-md-3">
                        <label for="gsis_number" class="form-label">GSIS #</label>
                        <input
                            type="text"
                            class="form-control"
                            id="gsis_number"
                            name="gsis_number"
                            value="<?= htmlspecialchars($employee['gsis_number'] ?? '') ?>"
                        >
                    </div>
                </div>

                <div class="mb-3 pb-2 border-bottom mt-4">
                    <h6 class="mb-0 text-primary">
                        <i class="fas fa-graduation-cap me-2"></i>Educational Background
                    </h6>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <div class="alert alert-info small mb-0">
                            <i class="fas fa-info-circle me-1"></i>
                            Educational background entries can be added after saving employee information.
                        </div>
                    </div>
                </div>

                <div class="mb-3 pb-2 border-bottom mt-4">
                    <h6 class="mb-0 text-primary">
                        <i class="fas fa-briefcase me-2"></i>Work Experience
                    </h6>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <div class="alert alert-info small mb-0">
                            <i class="fas fa-info-circle me-1"></i>
                            Work experience entries can be added after saving employee information. Include private employment. Start from your recent work.
                        </div>
                    </div>
                </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-end">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Personal Information -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom">
        <h5 class="card-title mb-0">
            <i class="fas fa-user me-2 text-primary"></i>Personal Information
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-borderless align-middle mb-0">
                <tbody>
                <tr>
                    <td class="profile-label"><i class="fas fa-birthday-cake me-2 text-muted"></i>Birthdate</td>
                    <td class="profile-value"><?= htmlspecialchars($employee['birthdate'] ?: 'Not provided') ?></td>
                </tr>
                <tr>
                    <td class="profile-label"><i class="fas fa-home me-2 text-muted"></i>Home Address</td>
                    <td class="profile-value"><?= htmlspecialchars($employee['home_address'] ?: 'Not provided') ?></td>
                </tr>
                <tr>
                    <td class="profile-label"><i class="fas fa-phone me-2 text-muted"></i>Contact #</td>
                    <td class="profile-value"><?= htmlspecialchars($employee['contact_no'] ?: 'Not provided') ?></td>
                </tr>
                <tr>
                    <td class="profile-label"><i class="fas fa-envelope me-2 text-muted"></i>Email Address</td>
                    <td class="profile-value"><?= htmlspecialchars($employee['email'] ?: 'Not provided') ?></td>
                </tr>
                <tr>
                    <td class="profile-label"><i class="fas fa-heart me-2 text-muted"></i>Civil Status</td>
                    <td class="profile-value"><?= htmlspecialchars($employee['civil_status'] ?: 'Not provided') ?></td>
                </tr>
                <tr>
                    <td class="profile-label"><i class="fas fa-user-friends me-2 text-muted"></i>Name of Spouse</td>
                    <td class="profile-value"><?= htmlspecialchars($employee['spouse_name'] ?: 'Not provided') ?></td>
                </tr>
                <tr>
                    <td class="profile-label"><i class="fas fa-phone-alt me-2 text-muted"></i>Spouse Contact #</td>
                    <td class="profile-value"><?= htmlspecialchars($employee['spouse_contact_no'] ?: 'Not provided') ?></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Employee Information -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom">
        <h5 class="card-title mb-0">
            <i class="fas fa-briefcase me-2 text-primary"></i>Employee Information
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-borderless align-middle mb-0">
                <tbody>
                <tr>
                    <td class="profile-label"><i class="fas fa-id-badge me-2 text-muted"></i>Employee ID</td>
                    <td class="profile-value"><?= htmlspecialchars($employee['employee_number'] ?: 'Not provided') ?></td>
                </tr>
                <tr>
                    <td class="profile-label"><i class="fas fa-id-card-alt me-2 text-muted"></i>Pag-ibig #</td>
                    <td class="profile-value"><?= htmlspecialchars($employee['pagibig_number'] ?: 'Not provided') ?></td>
                </tr>
                <tr>
                    <td class="profile-label"><i class="fas fa-hospital me-2 text-muted"></i>PhilHealth #</td>
                    <td class="profile-value"><?= htmlspecialchars($employee['philhealth_number'] ?: 'Not provided') ?></td>
                </tr>
                <tr>
                    <td class="profile-label"><i class="fas fa-file-invoice me-2 text-muted"></i>TIN #</td>
                    <td class="profile-value"><?= htmlspecialchars($employee['tin_number'] ?: 'Not provided') ?></td>
                </tr>
                <tr>
                    <td class="profile-label"><i class="fas fa-shield-alt me-2 text-muted"></i>SSS #</td>
                    <td class="profile-value"><?= htmlspecialchars($employee['sss_number'] ?: 'Not provided') ?></td>
                </tr>
                <tr>
                    <td class="profile-label"><i class="fas fa-building me-2 text-muted"></i>GSIS #</td>
                    <td class="profile-value"><?= htmlspecialchars($employee['gsis_number'] ?: 'Not provided') ?></td>
                </tr>
                <tr>
                    <td class="profile-label"><i class="fas fa-briefcase me-2 text-muted"></i>Employment Status</td>
                    <td class="profile-value">
                        <?php if ($employee['employment_status']): ?>
                            <span
                                class="badge bg-primary cursor-pointer employment-status-badge"
                                data-bs-toggle="modal"
                                data-bs-target="#changeEmploymentStatusModal"
                                title="Click to change employment status"
                                style="cursor: pointer;"
                            >
                                <i class="fas fa-edit me-1"></i><?= htmlspecialchars($employee['employment_status']) ?>
                            </span>
                        <?php else: ?>
                            <button
                                class="btn btn-sm btn-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#changeEmploymentStatusModal"
                            >
                                <i class="fas fa-plus me-1"></i>Set Status
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td class="profile-label"><i class="fas fa-building me-2 text-muted"></i>Office / Department</td>
                    <td class="profile-value"><?= htmlspecialchars($employee['office'] ?: 'Not provided') ?></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Educational Background -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="fas fa-graduation-cap me-2 text-primary"></i>Educational Background
        </h5>
        <div>
            <a
                href="export_education_excel.php?id=<?= $id ?>"
                class="btn btn-success btn-sm me-2"
                title="Export to Excel"
            >
                <i class="fas fa-file-excel me-1"></i> Excel
            </a>
            <button
                class="btn btn-primary btn-sm"
                type="button"
                data-bs-toggle="modal"
                data-bs-target="#addEducationModal"
            >
                <i class="fas fa-plus-circle me-1"></i> Add Education
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 service-record-table" style="border: 1px solid #e0e0e0 !important; border-collapse: collapse !important;">
                <thead style="background-color: #ffffff !important;">
                <tr>
                    <th rowspan="2" scope="col" class="fw-bold align-middle" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Level</th>
                    <th rowspan="2" scope="col" class="fw-bold align-middle" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Name of School<br><small class="fw-normal">(Write in full)</small></th>
                    <th rowspan="2" scope="col" class="fw-bold align-middle" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Basic Education/Degree/Course<br><small class="fw-normal">(Write in full)</small></th>
                    <th colspan="2" scope="col" class="text-center fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Period of Attendance</th>
                    <th rowspan="2" scope="col" class="fw-bold align-middle" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Highest Level/Units Earned<br><small class="fw-normal">(if not graduated)</small></th>
                    <th rowspan="2" scope="col" class="text-center fw-bold align-middle" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Year Graduated</th>
                    <th rowspan="2" scope="col" class="fw-bold align-middle" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Scholarship/Academic Honors Received</th>
                    <th rowspan="2" scope="col" class="text-center fw-bold align-middle" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Actions</th>
                </tr>
                <tr>
                    <th class="text-center fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">From</th>
                    <th class="text-center fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">To</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($educationalBackground)): ?>
                    <tr>
                        <td colspan="8" class="text-muted text-center py-5">
                            <i class="fas fa-inbox fa-2x mb-2 d-block text-muted"></i>
                            No educational background records yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($educationalBackground as $edu): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($edu['level']) ?></strong></td>
                            <td><?= htmlspecialchars($edu['school_name']) ?></td>
                            <td><?= htmlspecialchars($edu['degree_course'] ?: '-') ?></td>
                            <td class="text-center"><?= htmlspecialchars($edu['period_from'] ?: '-') ?></td>
                            <td class="text-center"><?= htmlspecialchars($edu['period_to'] ?: '-') ?></td>
                            <td class="text-center"><?= htmlspecialchars($edu['highest_level_units'] ?: '-') ?></td>
                            <td class="text-center"><?= htmlspecialchars($edu['year_graduated'] ?: '-') ?></td>
                            <td><?= htmlspecialchars($edu['scholarship_honors'] ?: '-') ?></td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-warning text-dark px-1 py-0 btn-action"
                                        onclick="editEducation(<?= $edu['id'] ?>, '<?= htmlspecialchars($edu['level'], ENT_QUOTES) ?>', '<?= htmlspecialchars($edu['school_name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($edu['degree_course'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($edu['period_from'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($edu['period_to'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($edu['highest_level_units'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($edu['year_graduated'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($edu['scholarship_honors'] ?? '', ENT_QUOTES) ?>')"
                                        title="Edit"
                                    >
                                        <i class="fas fa-edit fa-sm text-dark"></i>
                                    </button>
                                    <form method="post" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this educational background record?');">
                                        <input type="hidden" name="action" value="delete_education">
                                        <input type="hidden" name="edu_id" value="<?= $edu['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger px-1 py-0 btn-action" title="Delete">
                                            <i class="fas fa-trash fa-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Work Experience -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="fas fa-briefcase me-2 text-primary"></i>Work Experience
        </h5>
        <div>
            <a
                href="export_work_experience_excel.php?id=<?= $id ?>"
                class="btn btn-success btn-sm me-2"
                title="Export to Excel"
            >
                <i class="fas fa-file-excel me-1"></i> Excel
            </a>
            <button
                class="btn btn-primary btn-sm"
                type="button"
                data-bs-toggle="modal"
                data-bs-target="#addWorkExperienceModal"
            >
                <i class="fas fa-plus-circle me-1"></i> Add Work Experience
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 work-experience-table" style="font-size: 0.875rem;">
                <thead style="background-color: #ffffff !important;">
                <tr>
                    <th colspan="2" scope="col" class="text-center fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">
                        <strong>INCLUSIVE DATES<br><small>(mm/dd/yyyy)</small></strong>
                    </th>
                    <th scope="col" class="fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Position Title<br><small class="fw-normal">(Write in full/Do not abbreviate)</small></th>
                    <th scope="col" class="fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Department/Agency/Office/Company<br><small class="fw-normal">(Write in full/Do not abbreviate)</small></th>
                    <th scope="col" class="text-center fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Monthly Salary</th>
                    <th scope="col" class="text-center fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Salary/Job/Pay Grade<br><small class="fw-normal">(Format "00-0")</small></th>
                    <th scope="col" class="text-center fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Status of Appointment</th>
                    <th scope="col" class="text-center fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Gov't Service<br><small class="fw-normal">(Y/N)</small></th>
                    <th scope="col" class="text-center fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Actions</th>
                </tr>
                <tr>
                    <th scope="col" class="text-center fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">From</th>
                    <th scope="col" class="text-center fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">To</th>
                    <th scope="col" class="fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;"></th>
                    <th scope="col" class="fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;"></th>
                    <th scope="col" class="text-center fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;"></th>
                    <th scope="col" class="text-center fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;"></th>
                    <th scope="col" class="text-center fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;"></th>
                    <th scope="col" class="text-center fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;"></th>
                    <th scope="col" class="text-center fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;"></th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($workExperience)): ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="fas fa-info-circle me-2"></i>No work experience records found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($workExperience as $we): ?>
                        <tr>
                            <td class="text-center"><?= $we['date_from'] ? date('m/d/Y', strtotime($we['date_from'])) : '-' ?></td>
                            <td class="text-center"><?= $we['date_to'] ? date('m/d/Y', strtotime($we['date_to'])) : 'Present' ?></td>
                            <td><?= htmlspecialchars($we['position_title']) ?></td>
                            <td><?= htmlspecialchars($we['department_agency']) ?></td>
                            <td class="text-center"><?= $we['monthly_salary'] ? '₱' . number_format((float)$we['monthly_salary'], 2) : '-' ?></td>
                            <td class="text-center"><?= htmlspecialchars($we['salary_grade_step'] ?: '-') ?></td>
                            <td class="text-center"><?= htmlspecialchars($we['status_of_appointment'] ?: '-') ?></td>
                            <td class="text-center"><?= htmlspecialchars($we['govt_service'] ?: 'YES') ?></td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-warning text-dark px-1 py-0 btn-action"
                                        onclick="editWorkExperience(<?= $we['id'] ?>, '<?= htmlspecialchars($we['date_from'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($we['date_to'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($we['position_title'], ENT_QUOTES) ?>', '<?= htmlspecialchars($we['department_agency'], ENT_QUOTES) ?>', '<?= htmlspecialchars($we['monthly_salary'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($we['salary_grade_step'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($we['status_of_appointment'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($we['govt_service'] ?? 'YES', ENT_QUOTES) ?>', '<?= htmlspecialchars($we['description_of_duties'] ?? '', ENT_QUOTES) ?>')"
                                        title="Edit"
                                    >
                                        <i class="fas fa-edit fa-sm text-dark"></i>
                                    </button>
                                    <form method="post" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this work experience record?');">
                                        <input type="hidden" name="action" value="delete_work_experience">
                                        <input type="hidden" name="we_id" value="<?= $we['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger btn-sm px-2 py-1" title="Delete">
                                            <i class="fas fa-trash fa-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Awards & Recognitions -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="fas fa-trophy me-2 text-warning"></i>Awards &amp; Recognitions
        </h5>
        <div>
            <a
                href="export_awards_excel.php?id=<?= $id ?>"
                class="btn btn-success btn-sm me-2"
                title="Export to Excel"
            >
                <i class="fas fa-file-excel me-1"></i> Excel
            </a>
            <button
                class="btn btn-primary btn-sm"
                type="button"
                data-bs-toggle="modal"
                data-bs-target="#addAwardModal"
            >
                <i class="fas fa-plus-circle me-1"></i> Add Award
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="employee-table" style="border: 1px solid #e0e0e0 !important;">
                <thead style="background-color: #ffffff !important;">
                <tr>
                    <th scope="col" class="fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Award / Recognition</th>
                    <th scope="col" class="fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Level / Category</th>
                    <th scope="col" class="fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Awarding Body</th>
                    <th scope="col" class="text-center fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Date Received</th>
                    <th scope="col" class="fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Highlights / Remarks</th>
                    <th scope="col" class="text-center fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Actions</th>
                </tr>
                </thead>
                <tbody style="background-color: #ffffff !important;">
                <?php if (empty($awardRecords)): ?>
                    <tr style="background-color: #ffffff !important;">
                        <td colspan="6" class="text-muted text-center py-5" style="background-color: #ffffff !important;">
                            <i class="fas fa-award fa-2x mb-2 d-block text-muted"></i>
                            No awards / recognitions recorded yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($awardRecords as $award): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($award['title']) ?></strong>
                                <?php if (!empty($award['description'])): ?>
                                    <div class="text-muted small mt-1"><?= nl2br(htmlspecialchars($award['description'])) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($award['award_level'] ?: '-') ?></td>
                            <td><?= htmlspecialchars($award['awarding_body'] ?: '-') ?></td>
                            <td class="text-center">
                                <?= $award['award_date'] ? date('m/d/Y', strtotime($award['award_date'])) : '-' ?>
                            </td>
                            <td><?= htmlspecialchars($award['remarks'] ?: '-') ?></td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-warning text-dark px-1 py-0 btn-action"
                                        onclick="editAward(<?= $award['id'] ?>, '<?= htmlspecialchars($award['title'], ENT_QUOTES) ?>', '<?= htmlspecialchars($award['award_level'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($award['awarding_body'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($award['award_date'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($award['remarks'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($award['description'] ?? '', ENT_QUOTES) ?>')"
                                        title="Edit"
                                    >
                                        <i class="fas fa-edit fa-sm text-dark"></i>
                                    </button>
                                    <form method="post" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this award / recognition?');">
                                        <input type="hidden" name="action" value="delete_award">
                                        <input type="hidden" name="award_id" value="<?= $award['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger px-2 py-1" title="Delete">
                                            <i class="fas fa-trash fa-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Service records (structured) -->
<style>
    .service-record-table {
        font-size: 0.82rem;
        table-layout: fixed;
    }

    .service-record-table th,
    .service-record-table td {
        white-space: normal;
        word-break: break-word;
        padding: 0.5rem;
    }

    .btn-action {
        font-size: 0.75rem;
        padding: 0.1rem 0.35rem !important;
        line-height: 1.1;
        border: none !important;       /* remove inner button outline */
        box-shadow: none !important;   /* remove any shadow outline */
        outline: none !important;      /* remove focus outline */
    }

    @media (max-width: 1200px) {
        .service-record-table {
            font-size: 0.75rem;
        }
    }
</style>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="fas fa-file-alt me-2 text-primary"></i>Service Record Entries
        </h5>
        <div>
            <a
                href="export_service_records_excel.php?id=<?= $id ?>"
                class="btn btn-success btn-sm me-2"
                title="Export to Excel"
            >
                <i class="fas fa-file-excel me-1"></i> Excel
            </a>
            <button
                class="btn btn-primary btn-sm"
                type="button"
                data-bs-toggle="modal"
                data-bs-target="#addServiceRecordModal"
            >
                <i class="fas fa-plus-circle me-1"></i> Add Service Record Entries
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 service-record-table">
                <thead style="background-color: #ffffff !important;">
                <tr>
                    <th colspan="2" class="text-center" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">
                        <strong>SERVICE<br><small>(Inclusive Dates)</small></strong>
                    </th>
                    <th colspan="3" class="text-center" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">
                        <strong>RECORD OF APPOINTMENT</strong>
                    </th>
                    <th colspan="3" class="text-center" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">
                        <strong>OFFICE ENTITY/DIVISION</strong>
                    </th>
                    <th colspan="2" class="text-center" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">
                        <strong>SEPARATION</strong>
                    </th>
                    <th colspan="1" class="text-center" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">
                        <strong>ACTIONS</strong>
                    </th>
                </tr>
                <tr>
                    <th scope="col" class="text-center fw-bold" style="min-width: 100px; background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">From</th>
                    <th scope="col" class="text-center fw-bold" style="min-width: 100px; background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">To</th>
                    <th scope="col" class="fw-bold" style="min-width: 150px; background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Designation</th>
                    <th scope="col" class="text-center fw-bold" style="min-width: 100px; background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Status (1)</th>
                    <th scope="col" class="text-end fw-bold" style="min-width: 120px; background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Salary (2)</th>
                    <th scope="col" class="fw-bold" style="min-width: 100px; background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Place of</th>
                    <th scope="col" class="fw-bold" style="min-width: 100px; background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Branch</th>
                    <th scope="col" class="fw-bold" style="min-width: 100px; background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">LV ABS</th>
                    <th scope="col" class="text-center fw-bold" style="min-width: 100px; background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Date (4)</th>
                    <th scope="col" class="fw-bold" style="min-width: 150px; background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Cause</th>
                    <th scope="col" class="text-center fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($serviceRecords)): ?>
                    <tr>
                        <td colspan="11" class="text-muted text-center py-5">
                            <i class="fas fa-inbox fa-2x mb-2 d-block text-muted"></i>
                            No service record entries yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($serviceRecords as $s): ?>
                        <tr>
                            <td class="text-center"><?= isset($s['date_from']) && $s['date_from'] ? date('m/d/Y', strtotime($s['date_from'])) : '-' ?></td>
                            <td class="text-center"><?= isset($s['date_to']) && $s['date_to'] ? date('m/d/Y', strtotime($s['date_to'])) : '-' ?></td>
                            <td><strong><?= htmlspecialchars($s['position'] ?? '-') ?></strong></td>
                            <td class="text-center">
                                <?php if (!empty($s['status'])): ?>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($s['status']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if (!empty($s['salary']) && is_numeric($s['salary'])): ?>
                                    <span class="currency">₱<?= number_format((float)$s['salary'], 2) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($s['place_of'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($s['branch'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($s['lv_abs'] ?? '-') ?></td>
                            <td class="text-center"><?= isset($s['separation_date']) && $s['separation_date'] ? date('m/d/Y', strtotime($s['separation_date'])) : '-' ?></td>
                            <td><?= htmlspecialchars($s['separation_cause'] ?? '-') ?></td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-warning text-dark px-1 py-0 btn-action"
                                        onclick="editServiceRecord(<?= $s['id'] ?>, '<?= htmlspecialchars($s['position'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($s['status'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($s['salary'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($s['date_from'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($s['date_to'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($s['place_of'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($s['branch'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($s['assignment'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($s['lv_abs'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($s['wo_pay'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($s['separation_date'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($s['separation_cause'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($s['remarks'] ?? '', ENT_QUOTES) ?>')"
                                        title="Edit"
                                    >
                                        <i class="fas fa-edit fa-sm text-dark"></i>
                                    </button>
                                    <form method="post" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this service record?');">
                                        <input type="hidden" name="action" value="delete_service_record">
                                        <input type="hidden" name="sr_id" value="<?= $s['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger px-2 py-1" title="Delete">
                                            <i class="fas fa-trash fa-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Appointment / Promotion History -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="fas fa-history me-2 text-primary"></i>Appointment &amp; Promotion History
        </h5>
        <div>
            <a
                href="export_appointments_excel.php?id=<?= $id ?>"
                class="btn btn-success btn-sm me-2"
                title="Export to Excel"
            >
                <i class="fas fa-file-excel me-1"></i> Excel
            </a>
            <button
                class="btn btn-primary btn-sm"
                type="button"
                data-bs-toggle="modal"
                data-bs-target="#addAppointmentModal"
            >
                <i class="fas fa-plus-circle me-1"></i> Add Appointment
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="employee-table" style="border: 1px solid #e0e0e0 !important;">
                <thead style="background-color: #ffffff !important;">
                <tr>
                    <th scope="col" class="fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Type</th>
                    <th scope="col" class="fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Position</th>
                    <th scope="col" class="text-center fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Item #</th>
                    <th scope="col" class="text-center fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Salary Grade</th>
                    <th scope="col" class="text-center fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Date</th>
                    <th scope="col" class="text-end fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Salary</th>
                    <th scope="col" class="text-center fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Actions</th>
                </tr>
                </thead>
                <tbody style="background-color: #ffffff !important;">
                <?php if (empty($appointments)): ?>
                    <tr style="background-color: #ffffff !important;">
                        <td colspan="7" class="text-muted text-center py-5" style="background-color: #ffffff !important;">
                            <i class="fas fa-inbox fa-2x mb-2 d-block text-muted"></i>
                            No appointment records yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($appointments as $a): ?>
                        <tr>
                            <td>
                                <span class="badge bg-primary"><?= htmlspecialchars($a['type_label']) ?></span>
                            </td>
                            <td><strong><?= htmlspecialchars($a['position']) ?></strong></td>
                            <td class="text-center"><?= htmlspecialchars($a['item_number'] ?: '-') ?></td>
                            <td class="text-center">
                                <?php if ($a['salary_grade']): ?>
                                    <span class="badge bg-secondary">SG <?= htmlspecialchars($a['salary_grade']) ?></span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= htmlspecialchars($a['appointment_date'] ?: '-') ?></td>
                            <td class="text-end">
                                <?php if ($a['salary']): ?>
                                    <span class="currency">₱<?= number_format((float)$a['salary'], 2) ?></span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-warning text-dark px-1 py-0 btn-action"
                                        onclick="editAppointment(<?= $a['id'] ?>, '<?= htmlspecialchars($a['type_label'], ENT_QUOTES) ?>', '<?= htmlspecialchars($a['position'], ENT_QUOTES) ?>', '<?= htmlspecialchars($a['item_number'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($a['salary_grade'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($a['appointment_date'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($a['salary'] ?? '', ENT_QUOTES) ?>')"
                                        title="Edit"
                                    >
                                        <i class="fas fa-edit fa-sm text-dark"></i>
                                    </button>
                                    <form method="post" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this appointment?');">
                                        <input type="hidden" name="action" value="delete_appointment">
                                        <input type="hidden" name="appointment_id" value="<?= $a['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger px-2 py-1" title="Delete">
                                            <i class="fas fa-trash fa-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Trainings (structured records) -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="fas fa-graduation-cap me-2 text-primary"></i>Trainings (CTO)
        </h5>
        <div>
            <a
                href="export_trainings_excel.php?id=<?= $id ?>"
                class="btn btn-success btn-sm me-2"
                title="Export to Excel"
            >
                <i class="fas fa-file-excel me-1"></i> Excel
            </a>
            <button
                class="btn btn-primary btn-sm"
                type="button"
                data-bs-toggle="modal"
                data-bs-target="#addTrainingModal"
            >
                <i class="fas fa-plus-circle me-1"></i> Add Training
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="employee-table" style="border: 1px solid #e0e0e0 !important;">
                <thead style="background-color: #ffffff !important;">
                <tr>
                    <th scope="col" class="fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Title</th>
                    <th scope="col" class="fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Provider</th>
                    <th scope="col" class="fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Location</th>
                    <th scope="col" class="text-center fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">From</th>
                    <th scope="col" class="text-center fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">To</th>
                    <th scope="col" class="text-center fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Hours</th>
                    <th scope="col" class="fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Remarks</th>
                    <th scope="col" class="text-center fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Actions</th>
                </tr>
                </thead>
                <tbody style="background-color: #ffffff !important;">
                <?php if (empty($trainingRecords)): ?>
                    <tr style="background-color: #ffffff !important;">
                        <td colspan="8" class="text-muted text-center py-5" style="background-color: #ffffff !important;">
                            <i class="fas fa-inbox fa-2x mb-2 d-block text-muted"></i>
                            No training records yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($trainingRecords as $t): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($t['title']) ?></strong></td>
                            <td><?= htmlspecialchars($t['provider'] ?: '-') ?></td>
                            <td><?= htmlspecialchars($t['location'] ?: '-') ?></td>
                            <td class="text-center"><?= htmlspecialchars($t['date_from'] ?: '-') ?></td>
                            <td class="text-center"><?= htmlspecialchars($t['date_to'] ?: '-') ?></td>
                            <td class="text-center"><?= htmlspecialchars($t['hours'] ?: '-') ?></td>
                            <td><?= htmlspecialchars($t['remarks'] ?: '-') ?></td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-warning text-dark px-1 py-0 btn-action"
                                        onclick="editTraining(<?= $t['id'] ?>, '<?= htmlspecialchars($t['title'], ENT_QUOTES) ?>', '<?= htmlspecialchars($t['provider'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($t['location'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($t['date_from'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($t['date_to'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($t['hours'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($t['remarks'] ?? '', ENT_QUOTES) ?>')"
                                        title="Edit"
                                    >
                                        <i class="fas fa-edit fa-sm text-dark"></i>
                                    </button>
                                    <form method="post" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this training record?');">
                                        <input type="hidden" name="action" value="delete_training">
                                        <input type="hidden" name="training_id" value="<?= $t['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger px-2 py-1" title="Delete">
                                            <i class="fas fa-trash fa-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Leave records (structured) -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="fas fa-calendar-times me-2 text-primary"></i>Leave Records
        </h5>
        <div>
            <a
                href="export_leaves_excel.php?id=<?= $id ?>"
                class="btn btn-success btn-sm me-2"
                title="Export to Excel"
            >
                <i class="fas fa-file-excel me-1"></i> Excel
            </a>
            <button
                class="btn btn-primary btn-sm"
                type="button"
                data-bs-toggle="modal"
                data-bs-target="#addLeaveModal"
            >
                <i class="fas fa-plus-circle me-1"></i> Add Leave
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="employee-table" style="border: 1px solid #e0e0e0 !important;">
                <thead style="background-color: #ffffff !important;">
                <tr>
                    <th scope="col" class="fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Type</th>
                    <th scope="col" class="text-center fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">From</th>
                    <th scope="col" class="text-center fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">To</th>
                    <th scope="col" class="text-center fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Days</th>
                    <th scope="col" class="fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Remarks</th>
                    <th scope="col" class="text-center fw-bold" style="background-color: #ffffff !important; color: #212529 !important; border: 1px solid #e0e0e0 !important;">Actions</th>
                </tr>
                </thead>
                <tbody style="background-color: #ffffff !important;">
                <?php if (empty($leaveRecords)): ?>
                    <tr style="background-color: #ffffff !important;">
                        <td colspan="6" class="text-muted text-center py-5" style="background-color: #ffffff !important;">
                            <i class="fas fa-inbox fa-2x mb-2 d-block text-muted"></i>
                            No leave records yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($leaveRecords as $l): ?>
                        <tr>
                            <td style="background-color: white;"><strong><?= htmlspecialchars($l['leave_type']) ?></strong></td>
                            <td class="text-center" style="background-color: white;"><?= htmlspecialchars($l['date_from'] ?: '-') ?></td>
                            <td class="text-center" style="background-color: white;"><?= htmlspecialchars($l['date_to'] ?: '-') ?></td>
                            <td class="text-center" style="background-color: white;"><span class="badge bg-info"><?= htmlspecialchars($l['days'] ?: '-') ?></span></td>
                            <td style="background-color: white;"><?= htmlspecialchars($l['remarks'] ?: '-') ?></td>
                            <td class="text-center" style="background-color: white;">
                                <div class="d-flex justify-content-center gap-1">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-warning text-dark px-1 py-0 btn-action"
                                        onclick="editLeave(<?= $l['id'] ?>, '<?= htmlspecialchars($l['leave_type'], ENT_QUOTES) ?>', '<?= htmlspecialchars($l['date_from'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($l['date_to'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($l['days'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($l['remarks'] ?? '', ENT_QUOTES) ?>')"
                                        title="Edit"
                                    >
                                        <i class="fas fa-edit fa-sm text-dark"></i>
                                    </button>
                                    <form method="post" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this leave record?');">
                                        <input type="hidden" name="action" value="delete_leave">
                                        <input type="hidden" name="leave_id" value="<?= $l['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger px-2 py-1" title="Delete">
                                            <i class="fas fa-trash fa-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Award Modal -->
<div class="modal fade" id="addAwardModal" tabindex="-1" aria-labelledby="addAwardModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAwardModalLabel">Add Award / Recognition</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" autocomplete="off">
                    <input type="hidden" name="action" value="add_award">
                    <input type="hidden" name="award_id" value="">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="award_title" class="form-label">Award / Recognition <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                class="form-control"
                                id="award_title"
                                name="award_title"
                                placeholder="e.g. Outstanding Employee of the Year"
                                required
                            >
                        </div>
                        <div class="col-md-6">
                            <label for="award_body" class="form-label">Awarding Body / Organization</label>
                            <input
                                type="text"
                                class="form-control"
                                id="award_body"
                                name="award_body"
                                placeholder="e.g. Civil Service Commission"
                            >
                        </div>
                        <div class="col-md-6">
                            <label for="award_level" class="form-label">Level / Category</label>
                            <input
                                type="text"
                                class="form-control"
                                id="award_level"
                                name="award_level"
                                placeholder="e.g. Regional, National, International"
                            >
                        </div>
                        <div class="col-md-3">
                            <label for="award_date" class="form-label">Date Received</label>
                            <input
                                type="date"
                                class="form-control"
                                id="award_date"
                                name="award_date"
                            >
                        </div>
                        <div class="col-md-3">
                            <label for="award_remarks" class="form-label">Highlights / Remarks</label>
                            <input
                                type="text"
                                class="form-control"
                                id="award_remarks"
                                name="award_remarks"
                                placeholder="Key impact or citation"
                            >
                        </div>
                        <div class="col-12">
                            <label for="award_description" class="form-label">Description / Citation</label>
                            <textarea
                                class="form-control"
                                id="award_description"
                                name="award_description"
                                rows="3"
                                placeholder="Add citation notes or context for this recognition"
                            ></textarea>
                        </div>
                    </div>
                    <div class="mt-3 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Save Award
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Training Modal -->
<div class="modal fade" id="addTrainingModal" tabindex="-1" aria-labelledby="addTrainingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTrainingModalLabel">Add Training</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" autocomplete="off">
                    <input type="hidden" name="action" value="add_training">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="training_title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                class="form-control"
                                id="training_title"
                                name="training_title"
                                required
                            >
                        </div>
                        <div class="col-md-6">
                            <label for="training_provider" class="form-label">Provider</label>
                            <input
                                type="text"
                                class="form-control"
                                id="training_provider"
                                name="training_provider"
                            >
                        </div>
                        <div class="col-md-6">
                            <label for="training_location" class="form-label">Location</label>
                            <input
                                type="text"
                                class="form-control"
                                id="training_location"
                                name="training_location"
                            >
                        </div>
                        <div class="col-md-3">
                            <label for="training_date_from" class="form-label">From</label>
                            <input
                                type="date"
                                class="form-control"
                                id="training_date_from"
                                name="training_date_from"
                            >
                        </div>
                        <div class="col-md-3">
                            <label for="training_date_to" class="form-label">To</label>
                            <input
                                type="date"
                                class="form-control"
                                id="training_date_to"
                                name="training_date_to"
                            >
                        </div>
                        <div class="col-md-3">
                            <label for="training_hours" class="form-label">Hours</label>
                            <input
                                type="number"
                                step="0.5"
                                class="form-control"
                                id="training_hours"
                                name="training_hours"
                            >
                        </div>
                        <div class="col-md-9">
                            <label for="training_remarks" class="form-label">Remarks</label>
                            <input
                                type="text"
                                class="form-control"
                                id="training_remarks"
                                name="training_remarks"
                            >
                        </div>
                    </div>
                    <div class="mt-3 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            Add Training
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Educational Background Modal -->
<div class="modal fade" id="addEducationModal" tabindex="-1" aria-labelledby="addEducationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addEducationModalLabel">Add Educational Background</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" autocomplete="off">
                    <input type="hidden" name="action" value="add_education">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="edu_level" class="form-label">Level <span class="text-danger">*</span></label>
                            <select class="form-select" id="edu_level" name="edu_level" required>
                                <option value="">Select Level</option>
                                <option value="ELEMENTARY">Elementary</option>
                                <option value="HIGH SCHOOL">High School</option>
                                <option value="VOCATIONAL">Vocational</option>
                                <option value="COLLEGE">College</option>
                                <option value="GRADUATE STUDIES">Graduate Studies</option>
                            </select>
                        </div>
                        <div class="col-md-9">
                            <label for="edu_school_name" class="form-label">Name of School (Write in full) <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                class="form-control"
                                id="edu_school_name"
                                name="edu_school_name"
                                placeholder="Enter full school name"
                                required
                            >
                        </div>
                        <div class="col-md-12">
                            <label for="edu_degree_course" class="form-label">Basic Education/Degree/Course (Write in full)</label>
                            <input
                                type="text"
                                class="form-control"
                                id="edu_degree_course"
                                name="edu_degree_course"
                                placeholder="e.g. Primary Education, Bachelor of Science in Computer Science"
                            >
                        </div>
                        <div class="col-md-3">
                            <label for="edu_period_from" class="form-label">Period of Attendance - From</label>
                            <input
                                type="text"
                                class="form-control"
                                id="edu_period_from"
                                name="edu_period_from"
                                placeholder="e.g. 1973"
                            >
                        </div>
                        <div class="col-md-3">
                            <label for="edu_period_to" class="form-label">Period of Attendance - To</label>
                            <input
                                type="text"
                                class="form-control"
                                id="edu_period_to"
                                name="edu_period_to"
                                placeholder="e.g. 1979"
                            >
                        </div>
                        <div class="col-md-3">
                            <label for="edu_highest_level_units" class="form-label">Highest Level/Units Earned<br><small class="text-muted">(if not graduated)</small></label>
                            <input
                                type="text"
                                class="form-control"
                                id="edu_highest_level_units"
                                name="edu_highest_level_units"
                                placeholder="e.g. N/A, 2nd Year"
                            >
                        </div>
                        <div class="col-md-3">
                            <label for="edu_year_graduated" class="form-label">Year Graduated</label>
                            <input
                                type="text"
                                class="form-control"
                                id="edu_year_graduated"
                                name="edu_year_graduated"
                                placeholder="e.g. 1979"
                            >
                        </div>
                        <div class="col-md-12">
                            <label for="edu_scholarship_honors" class="form-label">Scholarship/Academic Honors Received</label>
                            <input
                                type="text"
                                class="form-control"
                                id="edu_scholarship_honors"
                                name="edu_scholarship_honors"
                                placeholder="e.g. Salutatorian, Dean's Lister"
                            >
                        </div>
                    </div>
                    <div class="mt-4 d-flex justify-content-end">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Add Educational Background
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Work Experience Modal -->
<div class="modal fade" id="addWorkExperienceModal" tabindex="-1" aria-labelledby="addWorkExperienceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addWorkExperienceModalLabel">Add Work Experience</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" autocomplete="off">
                    <input type="hidden" name="action" value="add_work_experience">
                    <div class="alert alert-info small mb-3">
                        <i class="fas fa-info-circle me-1"></i>
                        <strong>Note:</strong> Include private employment. Start from your recent work. Description of duties should be indicated in the attached Work Experience sheet.
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="we_date_from" class="form-label">Inclusive Dates - From (mm/dd/yyyy) <span class="text-danger">*</span></label>
                            <input
                                type="date"
                                class="form-control"
                                id="we_date_from"
                                name="we_date_from"
                                required
                            >
                        </div>
                        <div class="col-md-6">
                            <label for="we_date_to" class="form-label">Inclusive Dates - To (mm/dd/yyyy)<br><small class="text-muted">(Leave blank if present)</small></label>
                            <input
                                type="date"
                                class="form-control"
                                id="we_date_to"
                                name="we_date_to"
                            >
                        </div>
                        <div class="col-md-12">
                            <label for="we_position_title" class="form-label">Position Title (Write in full/Do not abbreviate) <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                class="form-control"
                                id="we_position_title"
                                name="we_position_title"
                                placeholder="e.g. ASSISTANT STATISTICIAN"
                                required
                            >
                        </div>
                        <div class="col-md-12">
                            <label for="we_department_agency" class="form-label">Department/Agency/Office/Company (Write in full/Do not abbreviate) <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                class="form-control"
                                id="we_department_agency"
                                name="we_department_agency"
                                placeholder="e.g. DEPARTMENT OF AGRARIAN REFORM"
                                required
                            >
                        </div>
                        <div class="col-md-4">
                            <label for="we_monthly_salary" class="form-label">Monthly Salary</label>
                            <input
                                type="number"
                                step="0.01"
                                class="form-control"
                                id="we_monthly_salary"
                                name="we_monthly_salary"
                                placeholder="0.00"
                            >
                        </div>
                        <div class="col-md-4">
                            <label for="we_salary_grade_step" class="form-label">Salary/Job/Pay Grade & Step<br><small class="text-muted">(Format "00-0")</small></label>
                            <input
                                type="text"
                                class="form-control"
                                id="we_salary_grade_step"
                                name="we_salary_grade_step"
                                placeholder="e.g. 09-3"
                            >
                        </div>
                        <div class="col-md-4">
                            <label for="we_status_appointment" class="form-label">Status of Appointment</label>
                            <input
                                type="text"
                                class="form-control"
                                id="we_status_appointment"
                                name="we_status_appointment"
                                placeholder="e.g. PERMANENT, TEMPORARY"
                            >
                        </div>
                        <div class="col-md-6">
                            <label for="we_govt_service" class="form-label">Gov't Service (Y/N) <span class="text-danger">*</span></label>
                            <select class="form-select" id="we_govt_service" name="we_govt_service" required>
                                <option value="YES">YES</option>
                                <option value="NO">NO</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label for="we_description_duties" class="form-label">Description of Duties<br><small class="text-muted">(Continue on separate sheet if necessary)</small></label>
                            <textarea
                                class="form-control"
                                id="we_description_duties"
                                name="we_description_duties"
                                rows="3"
                                placeholder="Describe the duties and responsibilities..."
                            ></textarea>
                        </div>
                    </div>
                    <div class="mt-4 d-flex justify-content-end">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Add Work Experience
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Leave Modal -->
<div class="modal fade" id="addLeaveModal" tabindex="-1" aria-labelledby="addLeaveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addLeaveModalLabel">Add Leave Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" autocomplete="off">
                    <input type="hidden" name="action" value="add_leave">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="leave_type" class="form-label">Leave Type <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                class="form-control"
                                id="leave_type"
                                name="leave_type"
                                placeholder="e.g. Vacation, Sick, Maternity"
                                required
                            >
                        </div>
                        <div class="col-md-3">
                            <label for="leave_date_from" class="form-label">From</label>
                            <input
                                type="date"
                                class="form-control"
                                id="leave_date_from"
                                name="leave_date_from"
                            >
                        </div>
                        <div class="col-md-3">
                            <label for="leave_date_to" class="form-label">To</label>
                            <input
                                type="date"
                                class="form-control"
                                id="leave_date_to"
                                name="leave_date_to"
                            >
                        </div>
                        <div class="col-md-2">
                            <label for="leave_days" class="form-label">Days</label>
                            <input
                                type="number"
                                step="0.5"
                                class="form-control"
                                id="leave_days"
                                name="leave_days"
                            >
                        </div>
                        <div class="col-md-12">
                            <label for="leave_remarks" class="form-label">Remarks</label>
                            <input
                                type="text"
                                class="form-control"
                                id="leave_remarks"
                                name="leave_remarks"
                            >
                        </div>
                    </div>
                    <div class="mt-3 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            Add Leave
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Service Record Modal -->
<div class="modal fade" id="addServiceRecordModal" tabindex="-1" aria-labelledby="addServiceRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addServiceRecordModalLabel">Add Service Record Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" autocomplete="off">
                    <input type="hidden" name="action" value="add_service_record">

                    <!-- SERVICE (Inclusive Dates) -->
                    <div class="mb-4">
                        <h6 class="text-primary border-bottom pb-2 mb-3">
                            <i class="fas fa-calendar-alt me-2"></i>SERVICE (Inclusive Dates)
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="sr_date_from" class="form-label">From <span class="text-danger">*</span></label>
                                <input
                                    type="date"
                                    class="form-control"
                                    id="sr_date_from"
                                    name="sr_date_from"
                                    required
                                >
                            </div>
                            <div class="col-md-6">
                                <label for="sr_date_to" class="form-label">To</label>
                                <input
                                    type="date"
                                    class="form-control"
                                    id="sr_date_to"
                                    name="sr_date_to"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- RECORD OF APPOINTMENT -->
                    <div class="mb-4">
                        <h6 class="text-primary border-bottom pb-2 mb-3">
                            <i class="fas fa-file-contract me-2"></i>RECORD OF APPOINTMENT
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="sr_position" class="form-label">Designation <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="sr_position"
                                    name="sr_position"
                                    placeholder="e.g. Clerk III"
                                    required
                                >
                            </div>
                            <div class="col-md-4">
                                <label for="sr_status" class="form-label">Status (1)</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="sr_status"
                                    name="sr_status"
                                    placeholder="e.g. Perm., COS, Temporary"
                                >
                            </div>
                            <div class="col-md-4">
                                <label for="sr_salary" class="form-label">Salary (2)</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    class="form-control"
                                    id="sr_salary"
                                    name="sr_salary"
                                    placeholder="0.00"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- OFFICE ENTITY/DIVISION -->
                    <div class="mb-4">
                        <h6 class="text-primary border-bottom pb-2 mb-3">
                            <i class="fas fa-building me-2"></i>OFFICE ENTITY/DIVISION
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="sr_place_of" class="form-label">Place of</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="sr_place_of"
                                    name="sr_place_of"
                                    placeholder="e.g. DAR"
                                >
                            </div>
                            <div class="col-md-3">
                                <label for="sr_branch" class="form-label">Branch</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="sr_branch"
                                    name="sr_branch"
                                    placeholder="e.g. Nat'l, Regional"
                                >
                            </div>
                            <div class="col-md-3">
                                <label for="sr_assignment" class="form-label">Assignment (3)</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="sr_assignment"
                                    name="sr_assignment"
                                    placeholder="e.g. None, (x)"
                                >
                            </div>
                            <div class="col-md-3">
                                <label for="sr_lv_abs" class="form-label">LV ABS</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="sr_lv_abs"
                                    name="sr_lv_abs"
                                    placeholder="Leave/Absence info"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- SEPARATION -->
                    <div class="mb-4">
                        <h6 class="text-primary border-bottom pb-2 mb-3">
                            <i class="fas fa-sign-out-alt me-2"></i>SEPARATION
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="sr_wo_pay" class="form-label">W/O Pay</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="sr_wo_pay"
                                    name="sr_wo_pay"
                                    placeholder="Without pay info"
                                >
                            </div>
                            <div class="col-md-4">
                                <label for="sr_separation_date" class="form-label">Date (4)</label>
                                <input
                                    type="date"
                                    class="form-control"
                                    id="sr_separation_date"
                                    name="sr_separation_date"
                                >
                            </div>
                            <div class="col-md-4">
                                <label for="sr_separation_cause" class="form-label">Cause</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="sr_separation_cause"
                                    name="sr_separation_cause"
                                    placeholder="Reason for separation"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Additional Remarks -->
                    <div class="mb-3">
                        <label for="sr_remarks" class="form-label">Remarks</label>
                        <input
                            type="text"
                            class="form-control"
                            id="sr_remarks"
                            name="sr_remarks"
                            placeholder="Additional notes"
                        >
                    </div>

                    <div class="mt-4 d-flex justify-content-end">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Add Service Record
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Appointment Modal -->
<div class="modal fade" id="addAppointmentModal" tabindex="-1" aria-labelledby="addAppointmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAppointmentModalLabel">Add Appointment / Promotion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" autocomplete="off">
                    <input type="hidden" name="action" value="add_appointment">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="type_label" class="form-label">Type <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                class="form-control"
                                id="type_label"
                                name="type_label"
                                placeholder="e.g. Original Appointment, 1st Promotion"
                                required
                            >
                        </div>
                        <div class="col-md-4">
                            <label for="position" class="form-label">Position <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                class="form-control"
                                id="position"
                                name="position"
                                required
                            >
                        </div>
                        <div class="col-md-2">
                            <label for="item_number" class="form-label">Item #</label>
                            <input
                                type="text"
                                class="form-control"
                                id="item_number"
                                name="item_number"
                            >
                        </div>
                        <div class="col-md-2">
                            <label for="salary_grade" class="form-label">Salary Grade</label>
                            <input
                                type="text"
                                class="form-control"
                                id="salary_grade"
                                name="salary_grade"
                            >
                        </div>
                        <div class="col-md-3">
                            <label for="appointment_date" class="form-label">Date</label>
                            <input
                                type="date"
                                class="form-control"
                                id="appointment_date"
                                name="appointment_date"
                            >
                        </div>
                        <div class="col-md-3">
                            <label for="salary" class="form-label">Salary</label>
                            <input
                                type="number"
                                step="0.01"
                                class="form-control"
                                id="salary"
                                name="salary"
                                placeholder="0.00"
                            >
                        </div>
                    </div>
                    <div class="mt-3 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            Add Appointment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Change Employment Status Modal -->
<div class="modal fade" id="changeEmploymentStatusModal" tabindex="-1" aria-labelledby="changeEmploymentStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeEmploymentStatusModalLabel">
                    <i class="fas fa-briefcase me-2"></i>Change Employment Status
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_employment_status">
                    <div class="mb-3">
                        <label for="employment_status_select" class="form-label">Employment Status</label>
                        <select class="form-select" id="employment_status_select" name="employment_status_select">
                            <option value="">-- Select Status --</option>
                            <option value="PERMANENT" <?= $employee['employment_status'] === 'PERMANENT' ? 'selected' : '' ?>>PERMANENT</option>
                            <option value="COS" <?= $employee['employment_status'] === 'COS' ? 'selected' : '' ?>>COS</option>
                            <option value="SPLIT" <?= $employee['employment_status'] === 'SPLIT' ? 'selected' : '' ?>>SPLIT</option>
                            <option value="CTI" <?= $employee['employment_status'] === 'CTI' ? 'selected' : '' ?>>CTI</option>
                            <option value="PA" <?= $employee['employment_status'] === 'PA' ? 'selected' : '' ?>>PA</option>
                            <option value="RESIGNED" <?= $employee['employment_status'] === 'RESIGNED' ? 'selected' : '' ?>>RESIGNED</option>
                            <option value="RETIRED" <?= $employee['employment_status'] === 'RETIRED' ? 'selected' : '' ?>>RETIRED</option>
                            <option value="OTHERS" <?= $employee['employment_status'] === 'OTHERS' ? 'selected' : '' ?>>OTHERS</option>
                        </select>
                        <small class="text-muted">Or enter custom status below</small>
                    </div>
                    <div class="mb-3">
                        <label for="employment_status_custom" class="form-label">Custom Status (Optional)</label>
                        <input
                            type="text"
                            class="form-control"
                            id="employment_status_custom"
                            name="employment_status_custom"
                            placeholder="Enter custom employment status"
                            value="<?= !in_array($employee['employment_status'] ?? '', ['PERMANENT', 'COS', 'SPLIT', 'CTI', 'PA', 'RESIGNED', 'RETIRED', 'OTHERS']) ? htmlspecialchars($employee['employment_status'] ?? '') : '' ?>"
                        >
                    </div>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <small>Select from the dropdown or enter a custom status. Custom status will override the selection.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Office / Department Modal -->
<div class="modal fade" id="changeOfficeModal" tabindex="-1" aria-labelledby="changeOfficeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeOfficeModalLabel">
                    <i class="fas fa-building me-2"></i>Change Office / Department
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_office">
                    <div class="mb-3">
                        <label for="office_select" class="form-label">Office / Department</label>
                        <select class="form-select" id="office_select" name="office">
                            <option value="">Select Office / Department</option>
                            <option value="LTS" <?= ($employee['office'] ?? '') === 'LTS' ? 'selected' : '' ?>>LTS</option>
                            <option value="LEGAL" <?= ($employee['office'] ?? '') === 'LEGAL' ? 'selected' : '' ?>>LEGAL</option>
                            <option value="DARAB" <?= ($employee['office'] ?? '') === 'DARAB' ? 'selected' : '' ?>>DARAB</option>
                            <option value="PBDD" <?= ($employee['office'] ?? '') === 'PBDD' ? 'selected' : '' ?>>PBDD</option>
                            <option value="OPARPO" <?= ($employee['office'] ?? '') === 'OPARPO' ? 'selected' : '' ?>>OPARPO</option>
                            <option value="STOD" <?= ($employee['office'] ?? '') === 'STOD' ? 'selected' : '' ?>>STOD</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update Office
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Employee ID Modal -->
<div class="modal fade" id="changeEmployeeIdModal" tabindex="-1" aria-labelledby="changeEmployeeIdModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeEmployeeIdModalLabel">
                    <i class="fas fa-id-badge me-2"></i>Change Employee ID
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_employee_id">
                    <div class="mb-3">
                        <label for="employee_id_input" class="form-label">Employee ID Number</label>
                        <input
                            type="text"
                            class="form-control"
                            id="employee_id_input"
                            name="employee_number"
                            value="<?= htmlspecialchars($employee['employee_number'] ?? '') ?>"
                            placeholder="Enter employee ID"
                            maxlength="50"
                        >
                        <div class="form-text">Leave empty to remove the employee ID.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Update ID
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script>
// Handle employment status modal form submission
document.querySelector('#changeEmploymentStatusModal form')?.addEventListener('submit', function(e) {
    const customStatus = document.getElementById('employment_status_custom')?.value.trim();
    const selectStatus = document.getElementById('employment_status_select')?.value;

    // Use custom status if provided, otherwise use selected status
    const finalStatus = customStatus || selectStatus;

    // Create hidden input with final status
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = 'employment_status';
    hiddenInput.value = finalStatus;
    this.appendChild(hiddenInput);

    // Remove the original inputs to avoid confusion
    const selectEl = document.getElementById('employment_status_select');
    const customEl = document.getElementById('employment_status_custom');
    if (selectEl) selectEl.disabled = true;
    if (customEl) customEl.disabled = true;
});

// Test modal functionality on page load
document.addEventListener('DOMContentLoaded', function() {
    // Ensure Bootstrap modal is available
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        console.log('Bootstrap Modal available');
    } else {
        console.error('Bootstrap Modal not available');
    }

    // Test if the modal element exists
    const modalEl = document.getElementById('changeEmploymentStatusModal');
    if (modalEl) {
        console.log('Employment status modal found');
    } else {
        console.error('Employment status modal not found');
    }
});

// Handle employee ID modal form submission
document.querySelector('#changeEmployeeIdModal form')?.addEventListener('submit', function(e) {
    const employeeId = document.getElementById('employee_id_input')?.value.trim();

    // Basic validation
    if (employeeId.length > 50) {
        e.preventDefault();
        alert('Employee ID cannot exceed 50 characters.');
        return;
    }

    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Updating...';
    submitBtn.disabled = true;

    // Re-enable on error (will be handled by page reload on success)
    setTimeout(() => {
        if (submitBtn.disabled) {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }, 5000);
});

// Reset Employee ID modal when closed
document.getElementById('changeEmployeeIdModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('changeEmployeeIdModalLabel').textContent = 'Change Employee ID';
    document.querySelector('#changeEmployeeIdModal form').reset();
});

// Edit Training function
function editTraining(id, title, provider, location, dateFrom, dateTo, hours, remarks) {
    document.getElementById('addTrainingModalLabel').textContent = 'Edit Training';
    document.querySelector('#addTrainingModal form input[name="action"]').value = 'edit_training';
    const form = document.querySelector('#addTrainingModal form');
    let trainingIdInput = form.querySelector('input[name="training_id"]');
    if (!trainingIdInput) {
        trainingIdInput = document.createElement('input');
        trainingIdInput.type = 'hidden';
        trainingIdInput.name = 'training_id';
        form.appendChild(trainingIdInput);
    }
    trainingIdInput.value = id;
    document.getElementById('training_title').value = title;
    document.getElementById('training_provider').value = provider;
    document.getElementById('training_location').value = location;
    document.getElementById('training_date_from').value = dateFrom;
    document.getElementById('training_date_to').value = dateTo;
    document.getElementById('training_hours').value = hours;
    document.getElementById('training_remarks').value = remarks;
    new bootstrap.Modal(document.getElementById('addTrainingModal')).show();
}

// Reset Training modal when closed
document.getElementById('addTrainingModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('addTrainingModalLabel').textContent = 'Add Training';
    document.querySelector('#addTrainingModal form input[name="action"]').value = 'add_training';
    const trainingIdInput = document.querySelector('#addTrainingModal form input[name="training_id"]');
    if (trainingIdInput) trainingIdInput.remove();
    document.querySelector('#addTrainingModal form').reset();
});

// Edit Leave function
function editLeave(id, leaveType, dateFrom, dateTo, days, remarks) {
    document.getElementById('addLeaveModalLabel').textContent = 'Edit Leave Record';
    document.querySelector('#addLeaveModal form input[name="action"]').value = 'edit_leave';
    const form = document.querySelector('#addLeaveModal form');
    let leaveIdInput = form.querySelector('input[name="leave_id"]');
    if (!leaveIdInput) {
        leaveIdInput = document.createElement('input');
        leaveIdInput.type = 'hidden';
        leaveIdInput.name = 'leave_id';
        form.appendChild(leaveIdInput);
    }
    leaveIdInput.value = id;
    document.getElementById('leave_type').value = leaveType;
    document.getElementById('leave_date_from').value = dateFrom;
    document.getElementById('leave_date_to').value = dateTo;
    document.getElementById('leave_days').value = days;
    document.getElementById('leave_remarks').value = remarks;
    new bootstrap.Modal(document.getElementById('addLeaveModal')).show();
}

// Reset Leave modal when closed
document.getElementById('addLeaveModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('addLeaveModalLabel').textContent = 'Add Leave Record';
    document.querySelector('#addLeaveModal form input[name="action"]').value = 'add_leave';
    const leaveIdInput = document.querySelector('#addLeaveModal form input[name="leave_id"]');
    if (leaveIdInput) leaveIdInput.remove();
    document.querySelector('#addLeaveModal form').reset();
});

// Edit Award function
function editAward(id, title, level, awardingBody, awardDate, remarks, description) {
    document.getElementById('addAwardModalLabel').textContent = 'Edit Award / Recognition';
    const form = document.querySelector('#addAwardModal form');
    form.querySelector('input[name="action"]').value = 'edit_award';
    form.querySelector('input[name="award_id"]').value = id;
    document.getElementById('award_title').value = title;
    document.getElementById('award_level').value = level;
    document.getElementById('award_body').value = awardingBody;
    document.getElementById('award_date').value = awardDate;
    document.getElementById('award_remarks').value = remarks;
    document.getElementById('award_description').value = description;
    new bootstrap.Modal(document.getElementById('addAwardModal')).show();
}

// Reset Award modal when closed
document.getElementById('addAwardModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('addAwardModalLabel').textContent = 'Add Award / Recognition';
    const form = document.querySelector('#addAwardModal form');
    form.querySelector('input[name="action"]').value = 'add_award';
    form.querySelector('input[name="award_id"]').value = '';
    form.reset();
});

// Edit Service Record function
function editServiceRecord(id, position, status, salary, dateFrom, dateTo, placeOf, branch, assignment, lvAbs, woPay, separationDate, separationCause, remarks) {
    document.getElementById('addServiceRecordModalLabel').textContent = 'Edit Service Record Entry';
    document.querySelector('#addServiceRecordModal form input[name="action"]').value = 'edit_service_record';
    const form = document.querySelector('#addServiceRecordModal form');
    let srIdInput = form.querySelector('input[name="sr_id"]');
    if (!srIdInput) {
        srIdInput = document.createElement('input');
        srIdInput.type = 'hidden';
        srIdInput.name = 'sr_id';
        form.appendChild(srIdInput);
    }
    srIdInput.value = id;
    document.getElementById('sr_position').value = position;
    document.getElementById('sr_status').value = status;
    document.getElementById('sr_salary').value = salary;
    document.getElementById('sr_date_from').value = dateFrom;
    document.getElementById('sr_date_to').value = dateTo;
    document.getElementById('sr_place_of').value = placeOf;
    document.getElementById('sr_branch').value = branch;
    document.getElementById('sr_assignment').value = assignment;
    document.getElementById('sr_lv_abs').value = lvAbs;
    document.getElementById('sr_wo_pay').value = woPay;
    document.getElementById('sr_separation_date').value = separationDate;
    document.getElementById('sr_separation_cause').value = separationCause;
    document.getElementById('sr_remarks').value = remarks;
    new bootstrap.Modal(document.getElementById('addServiceRecordModal')).show();
}

// Reset Service Record modal when closed
document.getElementById('addServiceRecordModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('addServiceRecordModalLabel').textContent = 'Add Service Record Entry';
    document.querySelector('#addServiceRecordModal form input[name="action"]').value = 'add_service_record';
    const srIdInput = document.querySelector('#addServiceRecordModal form input[name="sr_id"]');
    if (srIdInput) srIdInput.remove();
    document.querySelector('#addServiceRecordModal form').reset();
});

// Edit Appointment function
function editAppointment(id, typeLabel, position, itemNumber, salaryGrade, appointmentDate, salary) {
    document.getElementById('addAppointmentModalLabel').textContent = 'Edit Appointment / Promotion';
    document.querySelector('#addAppointmentModal form input[name="action"]').value = 'edit_appointment';
    const form = document.querySelector('#addAppointmentModal form');
    let appointmentIdInput = form.querySelector('input[name="appointment_id"]');
    if (!appointmentIdInput) {
        appointmentIdInput = document.createElement('input');
        appointmentIdInput.type = 'hidden';
        appointmentIdInput.name = 'appointment_id';
        form.appendChild(appointmentIdInput);
    }
    appointmentIdInput.value = id;
    document.getElementById('type_label').value = typeLabel;
    document.getElementById('position').value = position;
    document.getElementById('item_number').value = itemNumber;
    document.getElementById('salary_grade').value = salaryGrade;
    document.getElementById('appointment_date').value = appointmentDate;
    document.getElementById('salary').value = salary;
    new bootstrap.Modal(document.getElementById('addAppointmentModal')).show();
}

// Reset Appointment modal when closed
document.getElementById('addAppointmentModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('addAppointmentModalLabel').textContent = 'Add Appointment / Promotion';
    document.querySelector('#addAppointmentModal form input[name="action"]').value = 'add_appointment';
    const appointmentIdInput = document.querySelector('#addAppointmentModal form input[name="appointment_id"]');
    if (appointmentIdInput) appointmentIdInput.remove();
    document.querySelector('#addAppointmentModal form').reset();
});

// Edit Education function
function editEducation(id, level, schoolName, degreeCourse, periodFrom, periodTo, highestLevelUnits, yearGraduated, scholarshipHonors) {
    document.getElementById('addEducationModalLabel').textContent = 'Edit Educational Background';
    document.querySelector('#addEducationModal form input[name="action"]').value = 'edit_education';
    const form = document.querySelector('#addEducationModal form');
    let eduIdInput = form.querySelector('input[name="edu_id"]');
    if (!eduIdInput) {
        eduIdInput = document.createElement('input');
        eduIdInput.type = 'hidden';
        eduIdInput.name = 'edu_id';
        form.appendChild(eduIdInput);
    }
    eduIdInput.value = id;
    document.getElementById('edu_level').value = level;
    document.getElementById('edu_school_name').value = schoolName;
    document.getElementById('edu_degree_course').value = degreeCourse;
    document.getElementById('edu_period_from').value = periodFrom;
    document.getElementById('edu_period_to').value = periodTo;
    document.getElementById('edu_highest_level_units').value = highestLevelUnits;
    document.getElementById('edu_year_graduated').value = yearGraduated;
    document.getElementById('edu_scholarship_honors').value = scholarshipHonors;
    new bootstrap.Modal(document.getElementById('addEducationModal')).show();
}

// Reset Education modal when closed
document.getElementById('addEducationModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('addEducationModalLabel').textContent = 'Add Educational Background';
    document.querySelector('#addEducationModal form input[name="action"]').value = 'add_education';
    const eduIdInput = document.querySelector('#addEducationModal form input[name="edu_id"]');
    if (eduIdInput) eduIdInput.remove();
    document.querySelector('#addEducationModal form').reset();
});

// Edit Work Experience function
function editWorkExperience(id, dateFrom, dateTo, positionTitle, departmentAgency, monthlySalary, salaryGradeStep, statusAppointment, govtService, descriptionDuties) {
    document.getElementById('addWorkExperienceModalLabel').textContent = 'Edit Work Experience';
    document.querySelector('#addWorkExperienceModal form input[name="action"]').value = 'edit_work_experience';
    const form = document.querySelector('#addWorkExperienceModal form');
    let weIdInput = form.querySelector('input[name="we_id"]');
    if (!weIdInput) {
        weIdInput = document.createElement('input');
        weIdInput.type = 'hidden';
        weIdInput.name = 'we_id';
        form.appendChild(weIdInput);
    }
    weIdInput.value = id;
    document.getElementById('we_date_from').value = dateFrom;
    document.getElementById('we_date_to').value = dateTo;
    document.getElementById('we_position_title').value = positionTitle;
    document.getElementById('we_department_agency').value = departmentAgency;
    document.getElementById('we_monthly_salary').value = monthlySalary;
    document.getElementById('we_salary_grade_step').value = salaryGradeStep;
    document.getElementById('we_status_appointment').value = statusAppointment;
    document.getElementById('we_govt_service').value = govtService;
    document.getElementById('we_description_duties').value = descriptionDuties;
    new bootstrap.Modal(document.getElementById('addWorkExperienceModal')).show();
}

// Reset Work Experience modal when closed
document.getElementById('addWorkExperienceModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('addWorkExperienceModalLabel').textContent = 'Add Work Experience';
    document.querySelector('#addWorkExperienceModal form input[name="action"]').value = 'add_work_experience';
    const weIdInput = document.querySelector('#addWorkExperienceModal form input[name="we_id"]');
    if (weIdInput) weIdInput.remove();
    document.querySelector('#addWorkExperienceModal form').reset();
});

// Close any open modals if there's a success message
if (document.querySelector('.alert-success')) {
    document.querySelectorAll('.modal').forEach(modal => {
        const bsModal = bootstrap.Modal.getInstance(modal);
        if (bsModal) bsModal.hide();
    });
}
</script>

<style>
/* Professional Responsive Table Styles */
@media (max-width: 768px) {
    /* Educational Background Table - Professional Mobile Styles */
    .service-record-table {
        font-size: 0.8rem !important;
        table-layout: auto !important;
    }

    .service-record-table thead th {
        font-size: 0.75rem !important;
        padding: 0.6rem 0.4rem !important;
        white-space: normal !important;
        word-wrap: break-word !important;
        min-width: 90px !important;
    }

    /* Prevent Level column from cutting words like "elementary" */
    .service-record-table thead th:first-child,
    .service-record-table tbody td:first-child {
        white-space: nowrap !important;
        word-wrap: normal !important;
        overflow: visible !important;
        min-width: 120px !important;
        max-width: 150px !important;
    }

    .service-record-table tbody td {
        font-size: 0.75rem !important;
        padding: 0.6rem 0.4rem !important;
        word-wrap: break-word !important;
        white-space: normal !important;
    }

    /* Work Experience Table - Professional Mobile Styles */
    .work-experience-table {
        font-size: 0.8rem !important;
    }

    .work-experience-table thead th {
        font-size: 0.75rem !important;
        padding: 0.6rem 0.4rem !important;
        white-space: normal !important;
        word-wrap: break-word !important;
        min-width: 85px !important;
    }

    .work-experience-table tbody td {
        font-size: 0.75rem !important;
        padding: 0.6rem 0.4rem !important;
        word-wrap: break-word !important;
        white-space: normal !important;
    }

    /* Enhanced horizontal scrolling for both tables */
    .table-responsive {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch !important;
        border-radius: 0.375rem !important;
    }

    /* Professional action buttons for mobile */
    .btn-action {
        padding: 0.25rem 0.5rem !important;
        font-size: 0.7rem !important;
        margin: 0.1rem !important;
        border-radius: 0.25rem !important;
    }

    /* Make Add Work Experience button smaller on mobile */
    button[data-bs-target="#addWorkExperienceModal"] {
        padding: 0.25rem 0.5rem !important;
        font-size: 0.75rem !important;
        min-width: auto !important;
    }

    /* Make Add Service Record button smaller on mobile */
    button[data-bs-target="#addServiceRecordModal"] {
        padding: 0.25rem 0.5rem !important;
        font-size: 0.75rem !important;
        min-width: auto !important;
    }

    /* Make Add Appointment button smaller on mobile */
    button[data-bs-target="#addAppointmentModal"] {
        padding: 0.25rem 0.5rem !important;
        font-size: 0.75rem !important;
        min-width: auto !important;
    }

    /* Make Add Training button smaller on mobile */
    button[data-bs-target="#addTrainingModal"] {
        padding: 0.25rem 0.5rem !important;
        font-size: 0.75rem !important;
        min-width: auto !important;
    }

    /* Make Set Status button work properly on mobile */
    button[data-bs-target="#changeEmploymentStatusModal"] {
        padding: 0.4rem 0.8rem !important;
        font-size: 0.8rem !important;
        min-height: 36px !important;
        min-width: 44px !important;
        touch-action: manipulation !important;
        -webkit-tap-highlight-color: rgba(40, 167, 69, 0.2) !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        position: relative !important;
        z-index: 1 !important;
        cursor: pointer !important;
        border: 1px solid transparent !important;
        background-color: #198754 !important;
        color: white !important;
        transition: all 0.15s ease !important;
    }

    button[data-bs-target="#changeEmploymentStatusModal"]:hover {
        background-color: #157347 !important;
        transform: translateY(-1px) !important;
    }

    button[data-bs-target="#changeEmploymentStatusModal"]:active {
        transform: translateY(0) !important;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.1) !important;
    }

    /* Improve training table title readability on mobile */
    .employee-table tbody td:first-child {
        word-wrap: break-word !important;
        white-space: normal !important;
        min-width: 120px !important;
        max-width: 180px !important;
        font-weight: 600 !important;
        line-height: 1.4 !important;
        font-size: 0.8rem !important;
        padding: 0.5rem !important;
    }

    /* Ensure training table is responsive */
    .employee-table {
        table-layout: auto !important;
        font-size: 0.8rem !important;
    }

    .employee-table thead th {
        font-size: 0.75rem !important;
        padding: 0.6rem 0.4rem !important;
        white-space: normal !important;
        word-wrap: break-word !important;
    }

    .employee-table tbody td {
        font-size: 0.75rem !important;
        padding: 0.6rem 0.4rem !important;
        word-wrap: break-word !important;
        white-space: normal !important;
    }

    /* Prevent buttons from being cramped on mobile */
    .card-header .btn {
        margin-bottom: 0.25rem !important;
        margin-right: 0.25rem !important;
    }

    .card-header > div {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 0.5rem !important;
        justify-content: flex-end !important;
    }

    /* Ensure proper button spacing in card headers */
    .card-header a.btn,
    .card-header button.btn {
        flex-shrink: 0 !important;
    }

    /* Make ALL buttons responsive on mobile */
    .btn {
        padding: 0.5rem 1rem !important;
        font-size: 0.875rem !important;
        line-height: 1.5 !important;
        border-radius: 0.375rem !important;
        min-height: 44px !important; /* iOS touch target minimum */
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        transition: all 0.15s ease !important;
    }

    /* Smaller buttons on very small screens */
    @media (max-width: 576px) {
        .btn {
            padding: 0.4rem 0.8rem !important;
            font-size: 0.8rem !important;
            min-height: 40px !important; /* Maintain touch target */
            min-width: 44px !important; /* Prevent too narrow buttons */
        }

        /* Special handling for action buttons */
        .btn-action {
            padding: 0.3rem 0.6rem !important;
            font-size: 0.75rem !important;
            margin: 0.15rem !important;
            min-height: 36px !important;
            min-width: 36px !important;
        }
    }

    /* Enhanced button interactions for mobile */
    .btn:active {
        transform: scale(0.98) !important;
    }

    /* Prevent text selection on buttons */
    .btn {
        -webkit-user-select: none !important;
        -moz-user-select: none !important;
        -ms-user-select: none !important;
        user-select: none !important;
    }

    /* Fix employment status buttons for mobile */
    .employment-status-badge {
        min-height: 28px !important;
        padding: 0.4rem 0.8rem !important;
        font-size: 0.8rem !important;
        display: inline-flex !important;
        align-items: center !important;
        cursor: pointer !important;
        touch-action: manipulation !important;
        -webkit-tap-highlight-color: rgba(0, 123, 255, 0.2) !important;
    }

    .employment-status-badge:hover {
        transform: scale(1.02) !important;
    }

    /* Fix employee ID badge for mobile */
    .employee-id-badge {
        min-height: 28px !important;
        padding: 0.4rem 0.8rem !important;
        font-size: 0.8rem !important;
        display: inline-flex !important;
        align-items: center !important;
        cursor: pointer !important;
        touch-action: manipulation !important;
        -webkit-tap-highlight-color: rgba(0, 123, 255, 0.2) !important;
    }

    .employee-id-badge:hover {
        transform: scale(1.02) !important;
    }

    /* Ensure modal triggers work on mobile */
    [data-bs-toggle="modal"] {
        touch-action: manipulation !important;
        -webkit-tap-highlight-color: transparent !important;
    }

    /* Fix for small screens */
    @media (max-width: 576px) {
        .employment-status-badge {
            min-height: 32px !important;
            padding: 0.5rem 1rem !important;
            font-size: 0.75rem !important;
        }

        .employee-id-badge {
            min-height: 32px !important;
            padding: 0.5rem 1rem !important;
            font-size: 0.75rem !important;
        }

        /* Ensure Set Status button works on small screens */
        button[data-bs-target="#changeEmploymentStatusModal"] {
            min-height: 40px !important;
            padding: 0.5rem 1rem !important;
            font-size: 0.8rem !important;
        }
    }
}


require_once 'footer.php';
?>
