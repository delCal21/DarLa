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

// Load awards
$stmt = $pdo->prepare('SELECT * FROM employee_awards WHERE employee_id = :id ORDER BY award_date DESC, id DESC');
$stmt->execute([':id' => $id]);
$awards = $stmt->fetchAll();

$fullName = trim($employee['last_name'] . ', ' . $employee['first_name'] . ($employee['middle_name'] ? ' ' . $employee['middle_name'] : ''));

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

ob_start();
echo "\xEF\xBB\xBF";

echo "DARLa HRIS - Awards & Recognitions\n";
echo "Employee: " . $fullName . "\n";
echo "Generated: " . date('F d, Y h:i A') . "\n\n";

echo "Title,Level/Category,Awarding Body,Date Received,Remarks,Description\n";

if (!empty($awards)) {
    foreach ($awards as $award) {
        echo csvEscape($award['title']) . ",";
        echo csvEscape($award['award_level'] ?: '-') . ",";
        echo csvEscape($award['awarding_body'] ?: '-') . ",";
        echo csvEscape($award['award_date'] ?: '-') . ",";
        echo csvEscape($award['remarks'] ?: '-') . ",";
        echo csvEscape($award['description'] ?: '-') . "\n";
    }
} else {
    echo "No awards/recognitions found.\n";
}

$content = ob_get_clean();
$filename = 'Awards_' . preg_replace('/[^A-Za-z0-9_]/', '_', $fullName) . '_' . date('Ymd') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($content));

echo $content;
exit;
