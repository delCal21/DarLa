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

// Load educational background
$stmt = $pdo->prepare('SELECT * FROM employee_educational_background WHERE employee_id = :id ORDER BY 
    CASE level 
        WHEN "ELEMENTARY" THEN 1 
        WHEN "HIGH SCHOOL" THEN 2 
        WHEN "VOCATIONAL" THEN 3 
        WHEN "COLLEGE" THEN 4 
        WHEN "GRADUATE STUDIES" THEN 5 
        ELSE 6 
    END, id ASC');
$stmt->execute([':id' => $id]);
$educationalBackground = $stmt->fetchAll();

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
echo "DARLa HRIS - Educational Background\n";
echo "Employee: " . $fullName . "\n";
echo "Generated: " . date('F d, Y h:i A') . "\n\n";

// Header row
echo "Level,Name of School,Degree/Course,Period From,Period To,Highest Level/Units Earned,Year Graduated,Scholarship/Academic Honors\n";

// Data rows
if (!empty($educationalBackground)) {
    foreach ($educationalBackground as $edu) {
        echo csvEscape($edu['level']) . ",";
        echo csvEscape($edu['school_name']) . ",";
        echo csvEscape($edu['degree_course'] ?: '-') . ",";
        echo csvEscape($edu['period_from'] ?: '-') . ",";
        echo csvEscape($edu['period_to'] ?: '-') . ",";
        echo csvEscape($edu['highest_level_units'] ?: '-') . ",";
        echo csvEscape($edu['year_graduated'] ?: '-') . ",";
        echo csvEscape($edu['scholarship_honors'] ?: '-') . "\n";
    }
} else {
    echo "No educational background records found.\n";
}

// Get the output
$output = ob_get_clean();

// Set headers for Excel/CSV download
$filename = 'Educational_Background_' . preg_replace('/[^A-Za-z0-9_]/', '_', $fullName) . '_' . date('Ymd') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . strlen($output));

// Output the CSV
echo $output;
exit;

