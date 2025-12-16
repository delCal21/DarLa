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

// Load training records
$stmt = $pdo->prepare('SELECT * FROM employee_trainings WHERE employee_id = :id ORDER BY date_from DESC, id DESC');
$stmt->execute([':id' => $id]);
$trainingRecords = $stmt->fetchAll();

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
echo "DARLa HRIS - Training Records\n";
echo "Employee: " . $fullName . "\n";
echo "Generated: " . date('F d, Y h:i A') . "\n\n";

// Header row
echo "Title,Provider,Location,From,To,Hours,Remarks\n";

// Data rows
if (!empty($trainingRecords)) {
    foreach ($trainingRecords as $t) {
        echo csvEscape($t['title']) . ",";
        echo csvEscape($t['provider'] ?: '-') . ",";
        echo csvEscape($t['location'] ?: '-') . ",";
        echo csvEscape($t['date_from'] ?: '-') . ",";
        echo csvEscape($t['date_to'] ?: '-') . ",";
        echo csvEscape($t['hours'] ?: '-') . ",";
        echo csvEscape($t['remarks'] ?: '-') . "\n";
    }
} else {
    echo "No training records found.\n";
}

// Get the output
$output = ob_get_clean();

// Set headers for Excel/CSV download
$filename = 'Trainings_' . preg_replace('/[^A-Za-z0-9_]/', '_', $fullName) . '_' . date('Ymd') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . strlen($output));

// Output the CSV
echo $output;
exit;

