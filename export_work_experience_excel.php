<?php
require_once 'auth.php';
require_admin_login();
require_once 'db.php';

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = (int)$_GET['id'];

// Load employee data
$stmt = $pdo->prepare('SELECT * FROM employees WHERE id = :id');
$stmt->execute([':id' => $id]);
$employee = $stmt->fetch();

if (!$employee) {
    header('Location: index.php');
    exit;
}

// Load work experience
$stmt = $pdo->prepare('SELECT * FROM employee_work_experience WHERE employee_id = :id ORDER BY date_from DESC, id DESC');
$stmt->execute([':id' => $id]);
$workExperience = $stmt->fetchAll();

$fullName = trim($employee['last_name'] . ', ' . $employee['first_name'] . ($employee['middle_name'] ? ' ' . $employee['middle_name'] : ''));

// Function to escape CSV fields
function csvEscape($value) {
    if ($value === null || $value === '') {
        return '';
    }
    $value = str_replace('"', '""', $value);
    if (strpos($value, ',') !== false || strpos($value, "\n") !== false || strpos($value, '"') !== false) {
        $value = '"' . $value . '"';
    }
    return $value;
}

// Start output buffering
ob_start();

// Output UTF-8 BOM for Excel compatibility
echo "\xEF\xBB\xBF";

// Title
echo "DARLa HRIS - Work Experience\n";
echo "Employee: " . $fullName . "\n";
echo "Generated: " . date('F d, Y h:i A') . "\n\n";

// Header row
echo "From,To,Position Title,Department/Agency/Office/Company,Monthly Salary,Salary/Job/Pay Grade & Step,Status of Appointment,Gov't Service (Y/N),Description of Duties\n";

// Data rows
if (!empty($workExperience)) {
    foreach ($workExperience as $we) {
        echo csvEscape($we['date_from'] ? date('m/d/Y', strtotime($we['date_from'])) : '') . ",";
        echo csvEscape($we['date_to'] ? date('m/d/Y', strtotime($we['date_to'])) : 'Present') . ",";
        echo csvEscape($we['position_title']) . ",";
        echo csvEscape($we['department_agency']) . ",";
        echo csvEscape($we['monthly_salary'] ? number_format((float)$we['monthly_salary'], 2) : '') . ",";
        echo csvEscape($we['salary_grade_step'] ?: '') . ",";
        echo csvEscape($we['status_of_appointment'] ?: '') . ",";
        echo csvEscape($we['govt_service'] ?: 'YES') . ",";
        echo csvEscape($we['description_of_duties'] ?: '') . "\n";
    }
} else {
    echo "No work experience records found.\n";
}

// Get the output
$output = ob_get_clean();

// Set headers for Excel/CSV download
$filename = 'Work_Experience_' . preg_replace('/[^A-Za-z0-9_]/', '_', $fullName) . '_' . date('Ymd') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . strlen($output));

// Output the CSV
echo $output;
exit;

