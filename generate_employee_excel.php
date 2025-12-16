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

// Load related data
$stmt = $pdo->prepare('SELECT * FROM appointments WHERE employee_id = :id ORDER BY sequence_no ASC');
$stmt->execute([':id' => $id]);
$appointments = $stmt->fetchAll();

$stmt = $pdo->prepare('SELECT * FROM employee_trainings WHERE employee_id = :id ORDER BY date_from DESC');
$stmt->execute([':id' => $id]);
$trainingRecords = $stmt->fetchAll();

$stmt = $pdo->prepare('SELECT * FROM employee_leaves WHERE employee_id = :id ORDER BY date_from DESC');
$stmt->execute([':id' => $id]);
$leaveRecords = $stmt->fetchAll();

$stmt = $pdo->prepare('SELECT * FROM employee_service_records WHERE employee_id = :id ORDER BY date_from DESC');
$stmt->execute([':id' => $id]);
$serviceRecords = $stmt->fetchAll();

$stmt = $pdo->prepare('SELECT * FROM employee_educational_background WHERE employee_id = :id ORDER BY 
    CASE level 
        WHEN "Elementary" THEN 1 
        WHEN "High School" THEN 2 
        WHEN "College" THEN 3 
        WHEN "Graduate Studies" THEN 4 
        ELSE 5 
    END, period_from ASC');
$stmt->execute([':id' => $id]);
$educationalBackground = $stmt->fetchAll();

$stmt = $pdo->prepare('SELECT * FROM employee_work_experience WHERE employee_id = :id ORDER BY date_from DESC, id DESC');
$stmt->execute([':id' => $id]);
$workExperience = $stmt->fetchAll();

$stmt = $pdo->prepare('SELECT * FROM employee_educational_background WHERE employee_id = :id ORDER BY 
    CASE level 
        WHEN "Elementary" THEN 1 
        WHEN "High School" THEN 2 
        WHEN "College" THEN 3 
        WHEN "Graduate Studies" THEN 4 
        ELSE 5 
    END, period_from ASC');
$stmt->execute([':id' => $id]);
$educationalBackground = $stmt->fetchAll();

$fullName = trim($employee['last_name'] . ', ' . $employee['first_name'] . ($employee['middle_name'] ? ' ' . $employee['middle_name'] : ''));

// Try to use PhpSpreadsheet, fallback to CSV if not available
$composerAutoload = __DIR__ . '/vendor/autoload.php';
$usePhpSpreadsheet = false;

if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
    if (class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
        $usePhpSpreadsheet = true;
    }
}

if ($usePhpSpreadsheet) {
    // Use PhpSpreadsheet for proper Excel format
    try {
        // Create new Spreadsheet object
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator('DARLa HRIS')
            ->setTitle('Employee Record - ' . $fullName)
            ->setSubject('Employee Information')
            ->setDescription('Employee record exported from DARLa HRIS');

        // Define styles - Professional styling with white headers and light gray borders
        $headerStyle = [
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => '212529'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'DCDCDC'],
                ],
            ],
        ];

        $sectionStyle = [
            'font' => [
                'bold' => true,
                'size' => 11,
                'color' => ['rgb' => '212529'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'DCDCDC'],
                ],
            ],
        ];

        $labelStyle = [
            'font' => [
                'bold' => true,
            ],
        ];

        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'DCDCDC'],
                ],
            ],
        ];

        $row = 1;

        // Title
        $sheet->setCellValue('A' . $row, 'DARLa HRIS - Employee Record');
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->getStyle('A' . $row)->applyFromArray($headerStyle);
        $sheet->getRowDimension($row)->setRowHeight(25);
        $row += 2;

        // Employee Name
        $sheet->setCellValue('A' . $row, $fullName);
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->getStyle('A' . $row)->applyFromArray($headerStyle);
        $sheet->getRowDimension($row)->setRowHeight(20);
        $row += 2;

        // Personal Information Section
        $sheet->setCellValue('A' . $row, 'Personal Information');
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->getStyle('A' . $row)->applyFromArray($sectionStyle);
        $row++;

        $personalInfo = [
            ['Birthdate', $employee['birthdate'] ?: 'Not provided'],
            ['Home Address', $employee['home_address'] ?: 'Not provided'],
            ['Contact Number', $employee['contact_no'] ?: 'Not provided'],
            ['Email Address', $employee['email'] ?: 'Not provided'],
            ['Civil Status', $employee['civil_status'] ?: 'Not provided'],
            ['Spouse Name', $employee['spouse_name'] ?: 'Not provided'],
            ['Spouse Contact', $employee['spouse_contact_no'] ?: 'Not provided'],
        ];

        foreach ($personalInfo as $info) {
            $sheet->setCellValue('A' . $row, $info[0] . ':');
            $sheet->getStyle('A' . $row)->applyFromArray($labelStyle);
            $sheet->setCellValue('B' . $row, $info[1]);
            $sheet->mergeCells('B' . $row . ':F' . $row);
            $row++;
        }

        $row++;

        // Employee Information Section
        $sheet->setCellValue('A' . $row, 'Employee Information');
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->getStyle('A' . $row)->applyFromArray($sectionStyle);
        $row++;

        $employeeInfo = [
            ['Employee ID', $employee['employee_number'] ?: 'Not assigned'],
            ['BP Number', $employee['bp_number'] ?: 'Not provided'],
            ['Pag-ibig Number', $employee['pagibig_number'] ?: 'Not provided'],
            ['PhilHealth Number', $employee['philhealth_number'] ?: 'Not provided'],
            ['TIN #', $employee['tin_number'] ?: 'Not provided'],
            ['SSS #', $employee['sss_number'] ?: 'Not provided'],
            ['GSIS #', $employee['gsis_number'] ?: 'Not provided'],
            ['Employment Status', $employee['employment_status'] ?: 'Not provided'],
        ];

        foreach ($employeeInfo as $info) {
            $sheet->setCellValue('A' . $row, $info[0] . ':');
            $sheet->getStyle('A' . $row)->applyFromArray($labelStyle);
            $sheet->setCellValue('B' . $row, $info[1]);
            $sheet->mergeCells('B' . $row . ':F' . $row);
            $row++;
        }

        if ($employee['designations']) {
            $sheet->setCellValue('A' . $row, 'Designations:');
            $sheet->getStyle('A' . $row)->applyFromArray($labelStyle);
            $sheet->setCellValue('B' . $row, $employee['designations']);
            $sheet->mergeCells('B' . $row . ':F' . $row);
            $sheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
            $row++;
        }

        $row++;

        // Educational Background
        if (!empty($educationalBackground)) {
            $sheet->setCellValue('A' . $row, 'Educational Background');
            $sheet->mergeCells('A' . $row . ':H' . $row);
            $sheet->getStyle('A' . $row)->applyFromArray($sectionStyle);
            $row++;
            
            // Header row
            $headers = ['Level', 'School Name', 'Degree/Course', 'From', 'To', 'Units Earned', 'Year Graduated', 'Honors'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . $row, $header);
                $sheet->getStyle($col . $row)->applyFromArray($sectionStyle);
                $sheet->getStyle($col . $row)->applyFromArray($borderStyle);
                $col++;
            }
            $row++;
            
            // Data rows
            foreach ($educationalBackground as $edu) {
                $sheet->setCellValue('A' . $row, $edu['level'] ?: '-');
                $sheet->setCellValue('B' . $row, $edu['school_name'] ?: '-');
                $sheet->setCellValue('C' . $row, $edu['degree_course'] ?: '-');
                $sheet->setCellValue('D' . $row, $edu['period_from'] ?: '-');
                $sheet->setCellValue('E' . $row, $edu['period_to'] ?: '-');
                $sheet->setCellValue('F' . $row, $edu['highest_level_units'] ?: '-');
                $sheet->setCellValue('G' . $row, $edu['year_graduated'] ?: '-');
                $sheet->setCellValue('H' . $row, $edu['scholarship_honors'] ?: '-');
                $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray($borderStyle);
                $row++;
            }
        $row++;
    }

        // Work Experience
        if (!empty($workExperience)) {
            $sheet->setCellValue('A' . $row, 'Work Experience');
            $sheet->mergeCells('A' . $row . ':I' . $row);
            $sheet->getStyle('A' . $row)->applyFromArray($sectionStyle);
            $row++;
            
            // Header row
            $headers = ['From', 'To', 'Position Title', 'Department/Agency/Office/Company', 'Monthly Salary', 'Salary/Job/Pay Grade & Step', 'Status of Appointment', 'Gov\'t Service (Y/N)', 'Description of Duties'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . $row, $header);
                $sheet->getStyle($col . $row)->applyFromArray($sectionStyle);
                $sheet->getStyle($col . $row)->applyFromArray($borderStyle);
                $col++;
            }
            $row++;
            
            // Data rows
            foreach ($workExperience as $we) {
                $sheet->setCellValue('A' . $row, $we['date_from'] ? date('m/d/Y', strtotime($we['date_from'])) : '-');
                $sheet->setCellValue('B' . $row, $we['date_to'] ? date('m/d/Y', strtotime($we['date_to'])) : 'Present');
                $sheet->setCellValue('C' . $row, $we['position_title'] ?: '-');
                $sheet->setCellValue('D' . $row, $we['department_agency'] ?: '-');
                $sheet->setCellValue('E' . $row, $we['monthly_salary'] ? number_format((float)$we['monthly_salary'], 2) : '-');
                $sheet->setCellValue('F' . $row, $we['salary_grade_step'] ?: '-');
                $sheet->setCellValue('G' . $row, $we['status_of_appointment'] ?: '-');
                $sheet->setCellValue('H' . $row, $we['govt_service'] ?: 'YES');
                $sheet->setCellValue('I' . $row, $we['description_of_duties'] ?: '-');
                $sheet->getStyle('A' . $row . ':I' . $row)->applyFromArray($borderStyle);
                $sheet->getStyle('I' . $row)->getAlignment()->setWrapText(true);
                $row++;
            }
            $row++;
        }

        // Additional Information Section
        if ($employee['trainings'] || $employee['leave_info'] || $employee['service_record']) {
            $sheet->setCellValue('A' . $row, 'Additional Information');
            $sheet->mergeCells('A' . $row . ':F' . $row);
            $sheet->getStyle('A' . $row)->applyFromArray($sectionStyle);
            $row++;
            
            if ($employee['trainings']) {
                $sheet->setCellValue('A' . $row, 'Trainings:');
                $sheet->getStyle('A' . $row)->applyFromArray($labelStyle);
                $sheet->setCellValue('B' . $row, $employee['trainings']);
                $sheet->mergeCells('B' . $row . ':F' . $row);
                $sheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
                $row++;
            }
            
            if ($employee['leave_info']) {
                $sheet->setCellValue('A' . $row, 'Leave Information:');
                $sheet->getStyle('A' . $row)->applyFromArray($labelStyle);
                $sheet->setCellValue('B' . $row, $employee['leave_info']);
                $sheet->mergeCells('B' . $row . ':F' . $row);
                $sheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
                $row++;
            }
            
            if ($employee['service_record']) {
                $sheet->setCellValue('A' . $row, 'Service Record:');
                $sheet->getStyle('A' . $row)->applyFromArray($labelStyle);
                $sheet->setCellValue('B' . $row, $employee['service_record']);
                $sheet->mergeCells('B' . $row . ':F' . $row);
                $sheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
                $row++;
            }
            
            $row++;
        }

        // Appointment & Promotion History
        if (!empty($appointments)) {
            $sheet->setCellValue('A' . $row, 'Appointment & Promotion History');
            $sheet->mergeCells('A' . $row . ':F' . $row);
            $sheet->getStyle('A' . $row)->applyFromArray($sectionStyle);
            $row++;
            
            // Header row
            $headers = ['Type', 'Position', 'Item #', 'Salary Grade', 'Date', 'Salary'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . $row, $header);
                $sheet->getStyle($col . $row)->applyFromArray($sectionStyle);
                $sheet->getStyle($col . $row)->applyFromArray($borderStyle);
                $col++;
            }
            $row++;
            
            // Data rows
            foreach ($appointments as $a) {
                $sheet->setCellValue('A' . $row, $a['type_label']);
                $sheet->setCellValue('B' . $row, $a['position']);
                $sheet->setCellValue('C' . $row, $a['item_number'] ?: '-');
                $sheet->setCellValue('D' . $row, $a['salary_grade'] ?: '-');
                $sheet->setCellValue('E' . $row, $a['appointment_date'] ?: '-');
                $sheet->setCellValue('F' . $row, $a['salary'] ? number_format((float)$a['salary'], 2) : '-');
                $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray($borderStyle);
                $row++;
            }
            $row++;
        }

        // Training Records
        if (!empty($trainingRecords)) {
            $sheet->setCellValue('A' . $row, 'Training Records');
            $sheet->mergeCells('A' . $row . ':G' . $row);
            $sheet->getStyle('A' . $row)->applyFromArray($sectionStyle);
            $row++;
            
            // Header row
            $headers = ['Title', 'Provider', 'Location', 'From', 'To', 'Hours', 'Remarks'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . $row, $header);
                $sheet->getStyle($col . $row)->applyFromArray($sectionStyle);
                $sheet->getStyle($col . $row)->applyFromArray($borderStyle);
                $col++;
            }
            $row++;
            
            // Data rows
            foreach ($trainingRecords as $t) {
                $sheet->setCellValue('A' . $row, $t['title']);
                $sheet->setCellValue('B' . $row, $t['provider'] ?: '-');
                $sheet->setCellValue('C' . $row, $t['location'] ?: '-');
                $sheet->setCellValue('D' . $row, $t['date_from'] ?: '-');
                $sheet->setCellValue('E' . $row, $t['date_to'] ?: '-');
                $sheet->setCellValue('F' . $row, $t['hours'] ?: '-');
                $sheet->setCellValue('G' . $row, $t['remarks'] ?: '-');
                $sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray($borderStyle);
                $row++;
            }
            $row++;
        }

        // Leave Records
        if (!empty($leaveRecords)) {
            $sheet->setCellValue('A' . $row, 'Leave Records');
            $sheet->mergeCells('A' . $row . ':E' . $row);
            $sheet->getStyle('A' . $row)->applyFromArray($sectionStyle);
            $row++;
            
            // Header row
            $headers = ['Type', 'From', 'To', 'Days', 'Remarks'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . $row, $header);
                $sheet->getStyle($col . $row)->applyFromArray($sectionStyle);
                $sheet->getStyle($col . $row)->applyFromArray($borderStyle);
                $col++;
            }
            $row++;
            
            // Data rows
            foreach ($leaveRecords as $l) {
                $sheet->setCellValue('A' . $row, $l['leave_type']);
                $sheet->setCellValue('B' . $row, $l['date_from'] ?: '-');
                $sheet->setCellValue('C' . $row, $l['date_to'] ?: '-');
                $sheet->setCellValue('D' . $row, $l['days'] ?: '-');
                $sheet->setCellValue('E' . $row, $l['remarks'] ?: '-');
                $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray($borderStyle);
                $row++;
            }
            $row++;
        }

        // Service Records
        if (!empty($serviceRecords)) {
            $sheet->setCellValue('A' . $row, 'Service Record Entries');
            $sheet->mergeCells('A' . $row . ':N' . $row);
            $sheet->getStyle('A' . $row)->applyFromArray($sectionStyle);
            $row++;
            
            // Header row
            $headers = ['From', 'To', 'Designation', 'Status', 'Salary', 'Place of', 'Branch', 'Assignment', 'LV ABS', 'W/O Pay', 'Separation Date', 'Separation Cause', 'Remarks'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . $row, $header);
                $sheet->getStyle($col . $row)->applyFromArray($sectionStyle);
                $sheet->getStyle($col . $row)->applyFromArray($borderStyle);
                $col++;
            }
            $row++;
            
            // Data rows
            foreach ($serviceRecords as $s) {
                $sheet->setCellValue('A' . $row, $s['date_from'] ?: '-');
                $sheet->setCellValue('B' . $row, $s['date_to'] ?: '-');
                $sheet->setCellValue('C' . $row, $s['position'] ?: '-');
                $sheet->setCellValue('D' . $row, $s['status'] ?: '-');
                $sheet->setCellValue('E' . $row, $s['salary'] ? number_format((float)$s['salary'], 2) : '-');
                $sheet->setCellValue('F' . $row, $s['place_of'] ?: '-');
                $sheet->setCellValue('G' . $row, $s['branch'] ?: '-');
                $sheet->setCellValue('H' . $row, $s['assignment'] ?: '-');
                $sheet->setCellValue('I' . $row, $s['lv_abs'] ?: '-');
                $sheet->setCellValue('J' . $row, $s['wo_pay'] ?: '-');
                $sheet->setCellValue('K' . $row, $s['separation_date'] ?: '-');
                $sheet->setCellValue('L' . $row, $s['separation_cause'] ?: '-');
                $sheet->setCellValue('M' . $row, $s['remarks'] ?: '-');
                $sheet->getStyle('A' . $row . ':M' . $row)->applyFromArray($borderStyle);
                $row++;
            }
            $row++;
        }

        // Footer
        $sheet->setCellValue('A' . $row, 'Generated on: ' . date('F d, Y h:i A'));
        $sheet->setCellValue('F' . $row, 'DARLa HRIS - Department of Agrarian Reform La Union');
        $sheet->getStyle('A' . $row)->getFont()->setItalic(true);
        $sheet->getStyle('F' . $row)->getFont()->setItalic(true);

        // Auto-size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set column widths for better readability
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(30);
        $sheet->getColumnDimension('H')->setWidth(30);

        // Output file
        $filename = 'Employee_Record_' . preg_replace('/[^A-Za-z0-9_]/', '_', $fullName) . '_' . date('Ymd') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    } catch (Exception $e) {
        // If PhpSpreadsheet fails, fall through to CSV
        $usePhpSpreadsheet = false;
    }
}

// Fallback to CSV if PhpSpreadsheet is not available or fails
// Function to escape CSV fields
function csvEscape($value) {
    if ($value === null || $value === '') {
        return '';
    }
    // Replace any existing quotes with double quotes
    $value = str_replace('"', '""', $value);
    // If value contains comma, newline, or quote, wrap in quotes
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
echo "DARLa HRIS - Employee Record\n";
echo $fullName . "\n\n";

// Personal Information
echo "Personal Information\n";
echo "Field,Value\n";
echo "Birthdate," . csvEscape($employee['birthdate'] ?: 'Not provided') . "\n";
echo "Home Address," . csvEscape($employee['home_address'] ?: 'Not provided') . "\n";
echo "Contact Number," . csvEscape($employee['contact_no'] ?: 'Not provided') . "\n";
echo "Email Address," . csvEscape($employee['email'] ?: 'Not provided') . "\n";
echo "Civil Status," . csvEscape($employee['civil_status'] ?: 'Not provided') . "\n";
echo "Spouse Name," . csvEscape($employee['spouse_name'] ?: 'Not provided') . "\n";
echo "Spouse Contact," . csvEscape($employee['spouse_contact_no'] ?: 'Not provided') . "\n\n";

// Employee Information
echo "Employee Information\n";
echo "Field,Value\n";
echo "Employee ID," . csvEscape($employee['employee_number'] ?: 'Not assigned') . "\n";
echo "BP Number," . csvEscape($employee['bp_number'] ?: 'Not provided') . "\n";
echo "Pag-ibig Number," . csvEscape($employee['pagibig_number'] ?: 'Not provided') . "\n";
echo "PhilHealth Number," . csvEscape($employee['philhealth_number'] ?: 'Not provided') . "\n";
echo "TIN #," . csvEscape($employee['tin_number'] ?: 'Not provided') . "\n";
echo "SSS #," . csvEscape($employee['sss_number'] ?: 'Not provided') . "\n";
echo "GSIS #," . csvEscape($employee['gsis_number'] ?: 'Not provided') . "\n";
echo "Employment Status," . csvEscape($employee['employment_status'] ?: 'Not provided') . "\n";
if ($employee['designations']) {
    echo "Designations," . csvEscape($employee['designations']) . "\n";
}
echo "\n";

// Educational Background
if (!empty($educationalBackground)) {
    echo "Educational Background\n";
    echo "Level,School Name,Degree/Course,From,To,Units Earned,Year Graduated,Honors\n";
    foreach ($educationalBackground as $edu) {
        echo csvEscape($edu['level'] ?: '-') . ",";
        echo csvEscape($edu['school_name'] ?: '-') . ",";
        echo csvEscape($edu['degree_course'] ?: '-') . ",";
        echo csvEscape($edu['period_from'] ?: '-') . ",";
        echo csvEscape($edu['period_to'] ?: '-') . ",";
        echo csvEscape($edu['highest_level_units'] ?: '-') . ",";
        echo csvEscape($edu['year_graduated'] ?: '-') . ",";
        echo csvEscape($edu['scholarship_honors'] ?: '-') . "\n";
    }
    echo "\n";
}

// Additional Information
if ($employee['trainings'] || $employee['leave_info'] || $employee['service_record']) {
    echo "Additional Information\n";
    echo "Field,Value\n";
    if ($employee['trainings']) {
        echo "Trainings," . csvEscape($employee['trainings']) . "\n";
    }
    if ($employee['leave_info']) {
        echo "Leave Information," . csvEscape($employee['leave_info']) . "\n";
    }
    if ($employee['service_record']) {
        echo "Service Record," . csvEscape($employee['service_record']) . "\n";
    }
    echo "\n";
}

// Work Experience
if (!empty($workExperience)) {
    echo "Work Experience\n";
    echo "From,To,Position Title,Department/Agency/Office/Company,Monthly Salary,Salary/Job/Pay Grade & Step,Status of Appointment,Gov't Service (Y/N),Description of Duties\n";
    foreach ($workExperience as $we) {
        echo csvEscape($we['date_from'] ? date('m/d/Y', strtotime($we['date_from'])) : '') . ",";
        echo csvEscape($we['date_to'] ? date('m/d/Y', strtotime($we['date_to'])) : 'Present') . ",";
        echo csvEscape($we['position_title'] ?: '-') . ",";
        echo csvEscape($we['department_agency'] ?: '-') . ",";
        echo csvEscape($we['monthly_salary'] ? number_format((float)$we['monthly_salary'], 2) : '-') . ",";
        echo csvEscape($we['salary_grade_step'] ?: '-') . ",";
        echo csvEscape($we['status_of_appointment'] ?: '-') . ",";
        echo csvEscape($we['govt_service'] ?: 'YES') . ",";
        echo csvEscape($we['description_of_duties'] ?: '-') . "\n";
    }
    echo "\n";
}

// Appointment & Promotion History
if (!empty($appointments)) {
    echo "Appointment & Promotion History\n";
    echo "Type,Position,Item #,Salary Grade,Date,Salary\n";
    foreach ($appointments as $a) {
        echo csvEscape($a['type_label']) . ",";
        echo csvEscape($a['position']) . ",";
        echo csvEscape($a['item_number'] ?: '-') . ",";
        echo csvEscape($a['salary_grade'] ?: '-') . ",";
        echo csvEscape($a['appointment_date'] ?: '-') . ",";
        echo csvEscape($a['salary'] ? number_format((float)$a['salary'], 2) : '-') . "\n";
    }
    echo "\n";
}

// Training Records
if (!empty($trainingRecords)) {
    echo "Training Records\n";
    echo "Title,Provider,Location,From,To,Hours,Remarks\n";
    foreach ($trainingRecords as $t) {
        echo csvEscape($t['title']) . ",";
        echo csvEscape($t['provider'] ?: '-') . ",";
        echo csvEscape($t['location'] ?: '-') . ",";
        echo csvEscape($t['date_from'] ?: '-') . ",";
        echo csvEscape($t['date_to'] ?: '-') . ",";
        echo csvEscape($t['hours'] ?: '-') . ",";
        echo csvEscape($t['remarks'] ?: '-') . "\n";
    }
    echo "\n";
}

// Leave Records
if (!empty($leaveRecords)) {
    echo "Leave Records\n";
    echo "Type,From,To,Days,Remarks\n";
    foreach ($leaveRecords as $l) {
        echo csvEscape($l['leave_type']) . ",";
        echo csvEscape($l['date_from'] ?: '-') . ",";
        echo csvEscape($l['date_to'] ?: '-') . ",";
        echo csvEscape($l['days'] ?: '-') . ",";
        echo csvEscape($l['remarks'] ?: '-') . "\n";
    }
    echo "\n";
}

// Service Records
if (!empty($serviceRecords)) {
    echo "Service Record Entries\n";
    echo "From,To,Designation,Status,Salary,Place of,Branch,Assignment,LV ABS,W/O Pay,Separation Date,Separation Cause,Remarks\n";
    foreach ($serviceRecords as $s) {
        echo csvEscape($s['date_from'] ?: '-') . ",";
        echo csvEscape($s['date_to'] ?: '-') . ",";
        echo csvEscape($s['position'] ?: '-') . ",";
        echo csvEscape($s['status'] ?: '-') . ",";
        echo csvEscape($s['salary'] ? number_format((float)$s['salary'], 2) : '-') . ",";
        echo csvEscape($s['place_of'] ?: '-') . ",";
        echo csvEscape($s['branch'] ?: '-') . ",";
        echo csvEscape($s['assignment'] ?: '-') . ",";
        echo csvEscape($s['lv_abs'] ?: '-') . ",";
        echo csvEscape($s['wo_pay'] ?: '-') . ",";
        echo csvEscape($s['separation_date'] ?: '-') . ",";
        echo csvEscape($s['separation_cause'] ?: '-') . ",";
        echo csvEscape($s['remarks'] ?: '-') . "\n";
    }
    echo "\n";
}

// Footer
echo "Generated on: " . date('F d, Y h:i A') . ",DARLa HRIS - Department of Agrarian Reform La Union\n";

// Get the output
$output = ob_get_clean();

// Set headers for Excel/CSV download
$filename = 'Employee_Record_' . preg_replace('/[^A-Za-z0-9_]/', '_', $fullName) . '_' . date('Ymd') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . strlen($output));

// Output the CSV
echo $output;
exit;
