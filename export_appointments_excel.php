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

// Load appointments
$stmt = $pdo->prepare('SELECT * FROM appointments WHERE employee_id = :id ORDER BY sequence_no ASC');
$stmt->execute([':id' => $id]);
$appointments = $stmt->fetchAll();

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
echo "DARLa HRIS - Appointment & Promotion History\n";
echo "Employee: " . $fullName . "\n";
echo "Generated: " . date('F d, Y h:i A') . "\n\n";

// Header row
echo "Type,Position,Item #,Salary Grade,Date,Salary\n";

// Data rows
if (!empty($appointments)) {
    foreach ($appointments as $a) {
        echo csvEscape($a['type_label']) . ",";
        echo csvEscape($a['position']) . ",";
        echo csvEscape($a['item_number'] ?: '-') . ",";
        echo csvEscape($a['salary_grade'] ?: '-') . ",";
        echo csvEscape($a['appointment_date'] ?: '-') . ",";
        echo csvEscape($a['salary'] ? number_format((float)$a['salary'], 2) : '-') . "\n";
    }
} else {
    echo "No appointment records found.\n";
}

// Get the output
$output = ob_get_clean();

// Set headers for Excel/CSV download
$filename = 'Appointments_' . preg_replace('/[^A-Za-z0-9_]/', '_', $fullName) . '_' . date('Ymd') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . strlen($output));

// Output the CSV
echo $output;
exit;

