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

        // Set paper size to legal (8.5 x 14 inches)
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_LEGAL); // Legal size paper
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
        $sheet->getPageMargins()->setTop(0.75);
        $sheet->getPageMargins()->setRight(0.7);
        $sheet->getPageMargins()->setLeft(0.7);
        $sheet->getPageMargins()->setBottom(0.75);

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
        $sheet->setCellValue('A' . $row, 'PERSONAL DATA SHEET');
        $sheet->mergeCells('A' . $row . ':N' . $row);
        $sheet->getStyle('A' . $row)->applyFromArray($headerStyle);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension($row)->setRowHeight(25);
        $row++;

        // Subtitle
        $sheet->setCellValue('A' . $row, '(CS FORM 212)');
        $sheet->mergeCells('A' . $row . ':N' . $row);
        $sheet->getStyle('A' . $row)->applyFromArray($headerStyle);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension($row)->setRowHeight(20);
        $row++;

        // Blank row for spacing
        $row++;

        // Name Section (Following PDS format)
        $sheet->setCellValue('A' . $row, 'NAME');
        $sheet->mergeCells('A' . $row . ':N' . $row);
        $sheet->getStyle('A' . $row)->applyFromArray($sectionStyle);
        $row++;

        // Name fields in PDS format
        $sheet->setCellValue('A' . $row, '1. SURNAME:');
        $sheet->getStyle('A' . $row)->applyFromArray($labelStyle);
        $sheet->setCellValue('B' . $row, $employee['last_name'] ?: 'Not provided');
        $sheet->mergeCells('B' . $row . ':D' . $row);

        $sheet->setCellValue('E' . $row, '2. FIRST NAME:');
        $sheet->getStyle('E' . $row)->applyFromArray($labelStyle);
        $sheet->setCellValue('F' . $row, $employee['first_name'] ?: 'Not provided');
        $sheet->mergeCells('F' . $row . ':H' . $row);

        $sheet->setCellValue('I' . $row, '3. MIDDLE NAME:');
        $sheet->getStyle('I' . $row)->applyFromArray($labelStyle);
        $sheet->setCellValue('J' . $row, $employee['middle_name'] ?: 'Not provided');
        $sheet->mergeCells('J' . $row . ':N' . $row);
        $row++;

        // Personal Information Section (Following PDS format)
        $sheet->setCellValue('A' . $row, 'II. PERSONAL INFORMATION');
        $sheet->mergeCells('A' . $row . ':N' . $row);
        $sheet->getStyle('A' . $row)->applyFromArray($sectionStyle);
        $row++;

        // Personal info in PDS format
        $sheet->setCellValue('A' . $row, '4. DATE OF BIRTH:');
        $sheet->getStyle('A' . $row)->applyFromArray($labelStyle);
        $sheet->setCellValue('B' . $row, $employee['birthdate'] ?: 'Not provided');
        $sheet->mergeCells('B' . $row . ':D' . $row);

        $sheet->setCellValue('E' . $row, '5. PLACE OF BIRTH:');
        $sheet->getStyle('E' . $row)->applyFromArray($labelStyle);
        $sheet->setCellValue('F' . $row, 'PLACEHOLDER'); // This field is not stored in the database
        $sheet->mergeCells('F' . $row . ':N' . $row);
        $row++;

        $sheet->setCellValue('A' . $row, '6. SEX:');
        $sheet->getStyle('A' . $row)->applyFromArray($labelStyle);
        $sheet->setCellValue('B' . $row, 'PLACEHOLDER'); // This field is not stored in the database
        $sheet->mergeCells('B' . $row . ':D' . $row);

        $sheet->setCellValue('E' . $row, '7. CIVIL STATUS:');
        $sheet->getStyle('E' . $row)->applyFromArray($labelStyle);
        $sheet->setCellValue('F' . $row, $employee['civil_status'] ?: 'Not provided');
        $sheet->mergeCells('F' . $row . ':N' . $row);
        $row++;

        $sheet->setCellValue('A' . $row, '8. CITIZENSHIP:');
        $sheet->getStyle('A' . $row)->applyFromArray($labelStyle);
        $sheet->setCellValue('B' . $row, 'PLACEHOLDER'); // This field is not stored in the database
        $sheet->mergeCells('B' . $row . ':D' . $row);

        $sheet->setCellValue('E' . $row, '9. HEIGHT:');
        $sheet->getStyle('E' . $row)->applyFromArray($labelStyle);
        $sheet->setCellValue('F' . $row, 'PLACEHOLDER'); // This field is not stored in the database
        $sheet->mergeCells('F' . $row . ':N' . $row);
        $row++;

        $sheet->setCellValue('A' . $row, '10. WEIGHT:');
        $sheet->getStyle('A' . $row)->applyFromArray($labelStyle);
        $sheet->setCellValue('B' . $row, 'PLACEHOLDER'); // This field is not stored in the database
        $sheet->mergeCells('B' . $row . ':D' . $row);

        $sheet->setCellValue('E' . $row, '11. BLOOD TYPE:');
        $sheet->getStyle('E' . $row)->applyFromArray($labelStyle);
        $sheet->setCellValue('F' . $row, 'PLACEHOLDER'); // This field is not stored in the database
        $sheet->mergeCells('F' . $row . ':N' . $row);
        $row++;

        $sheet->setCellValue('A' . $row, '12. GSIS ID NO.:');
        $sheet->getStyle('A' . $row)->applyFromArray($labelStyle);
        $sheet->setCellValue('B' . $row, $employee['gsis_number'] ?: 'Not provided');
        $sheet->mergeCells('B' . $row . ':D' . $row);

        $sheet->setCellValue('E' . $row, '13. PAG-IBIG ID NO.:');
        $sheet->getStyle('E' . $row)->applyFromArray($labelStyle);
        $sheet->setCellValue('F' . $row, $employee['pagibig_number'] ?: 'Not provided');
        $sheet->mergeCells('F' . $row . ':N' . $row);
        $row++;

        $sheet->setCellValue('A' . $row, '14. PHILHEALTH NO.:');
        $sheet->getStyle('A' . $row)->applyFromArray($labelStyle);
        $sheet->setCellValue('B' . $row, $employee['philhealth_number'] ?: 'Not provided');
        $sheet->mergeCells('B' . $row . ':D' . $row);

        $sheet->setCellValue('E' . $row, '15. SSS NO.:');
        $sheet->getStyle('E' . $row)->applyFromArray($labelStyle);
        $sheet->setCellValue('F' . $row, $employee['sss_number'] ?: 'Not provided');
        $sheet->mergeCells('F' . $row . ':N' . $row);
        $row++;

        $sheet->setCellValue('A' . $row, '16. TIN NO.:');
        $sheet->getStyle('A' . $row)->applyFromArray($labelStyle);
        $sheet->setCellValue('B' . $row, $employee['tin_number'] ?: 'Not provided');
        $sheet->mergeCells('B' . $row . ':D' . $row);

        $sheet->setCellValue('E' . $row, '17. AGENCY EMPLOYEE NO.:');
        $sheet->getStyle('E' . $row)->applyFromArray($labelStyle);
        $sheet->setCellValue('F' . $row, $employee['employee_number'] ?: 'Not provided');
        $sheet->mergeCells('F' . $row . ':N' . $row);
        $row++;

        $sheet->setCellValue('A' . $row, '18. MOBILE NO.:');
        $sheet->getStyle('A' . $row)->applyFromArray($labelStyle);
        $sheet->setCellValue('B' . $row, $employee['contact_no'] ?: 'Not provided');
        $sheet->mergeCells('B' . $row . ':D' . $row);

        $sheet->setCellValue('E' . $row, '19. EMAIL ADDRESS:');
        $sheet->getStyle('E' . $row)->applyFromArray($labelStyle);
        $sheet->setCellValue('F' . $row, $employee['email'] ?: 'Not provided');
        $sheet->mergeCells('F' . $row . ':N' . $row);
        $row++;

        $sheet->setCellValue('A' . $row, '20. RESIDENTIAL ADDRESS:');
        $sheet->getStyle('A' . $row)->applyFromArray($labelStyle);
        $sheet->setCellValue('B' . $row, $employee['home_address'] ?: 'Not provided');
        $sheet->mergeCells('B' . $row . ':N' . $row);
        $row++;

        // Family Background Section (Following PDS format)
        $sheet->setCellValue('A' . $row, 'III. FAMILY BACKGROUND');
        $sheet->mergeCells('A' . $row . ':N' . $row);
        $sheet->getStyle('A' . $row)->applyFromArray($sectionStyle);
        $row++;

        $sheet->setCellValue('A' . $row, '21. SPOUSE\'S SURNAME:');
        $sheet->getStyle('A' . $row)->applyFromArray($labelStyle);
        $sheet->setCellValue('B' . $row, $employee['spouse_name'] ?: 'Not provided');
        $sheet->mergeCells('B' . $row . ':D' . $row);

        $sheet->setCellValue('E' . $row, 'FIRST NAME:');
        $sheet->setCellValue('F' . $row, 'PLACEHOLDER'); // This field is not stored in the database
        $sheet->mergeCells('F' . $row . ':H' . $row);

        $sheet->setCellValue('I' . $row, 'MIDDLE NAME:');
        $sheet->setCellValue('J' . $row, 'PLACEHOLDER'); // This field is not stored in the database
        $sheet->mergeCells('J' . $row . ':N' . $row);
        $row++;

        $sheet->setCellValue('A' . $row, 'OCCUPATION:');
        $sheet->setCellValue('B' . $row, 'PLACEHOLDER'); // This field is not stored in the database
        $sheet->mergeCells('B' . $row . ':D' . $row);

        $sheet->setCellValue('E' . $row, 'EMPLOYER/BUSINESS NAME:');
        $sheet->setCellValue('F' . $row, 'PLACEHOLDER'); // This field is not stored in the database
        $sheet->mergeCells('F' . $row . ':N' . $row);
        $row++;

        $sheet->setCellValue('A' . $row, 'BUSINESS ADDRESS:');
        $sheet->setCellValue('B' . $row, 'PLACEHOLDER'); // This field is not stored in the database
        $sheet->mergeCells('B' . $row . ':N' . $row);
        $row++;

        $sheet->setCellValue('A' . $row, 'TEL./CELLPHONE NO.:');
        $sheet->getStyle('A' . $row)->applyFromArray($labelStyle);
        $sheet->setCellValue('B' . $row, $employee['spouse_contact_no'] ?: 'Not provided');
        $sheet->mergeCells('B' . $row . ':N' . $row);
        $row++;

        // Educational Background Section (Following PDS format)
        if (!empty($educationalBackground)) {
            $sheet->setCellValue('A' . $row, 'IV. EDUCATIONAL BACKGROUND');
            $sheet->mergeCells('A' . $row . ':N' . $row);
            $sheet->getStyle('A' . $row)->applyFromArray($sectionStyle);
            $row++;

            // Header row for educational background in PDS format
            $headers = ['LEVEL', 'SCHOOL NAME', 'BASIC EDUCATION/DEGREE/COURSE (Write in full)', 'PERIOD OF ATTENDANCE', 'PERIOD OF ATTENDANCE', 'HIGHEST LEVEL/UNITS EARNED (if not graduated)', 'YEAR GRADUATED (if graduated)', 'SCHOLARSHIP/ACADEMIC HONORS RECEIVED'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . $row, $header);
                $sheet->getStyle($col . $row)->applyFromArray($sectionStyle);
                $sheet->getStyle($col . $row)->applyFromArray($borderStyle);
                $col++;
            }
            $row++;

            // Sub-header for Period of Attendance
            $sheet->setCellValue('D' . $row, 'FROM');
            $sheet->setCellValue('E' . $row, 'TO');
            $sheet->getStyle('D' . $row)->applyFromArray($sectionStyle);
            $sheet->getStyle('E' . $row)->applyFromArray($sectionStyle);
            $sheet->getStyle('D' . $row)->applyFromArray($borderStyle);
            $sheet->getStyle('E' . $row)->applyFromArray($borderStyle);

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

        // Civil Service Eligibility Section (Following PDS format)
        $sheet->setCellValue('A' . $row, 'V. CIVIL SERVICE ELIGIBILITY');
        $sheet->mergeCells('A' . $row . ':N' . $row);
        $sheet->getStyle('A' . $row)->applyFromArray($sectionStyle);
        $row++;

        // Placeholder for Civil Service Eligibility - this would need to be added to the database
        $headers = ['CAREER SERVICE/RA 1080 (BOARD/COMPETITIVE)', 'RATING', 'DATE OF EXAMINATION/TAKING', 'PLACE OF EXAMINATION', 'LICENSE (if applicable)', 'NUMBER', 'DATE OF VALIDITY'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->applyFromArray($sectionStyle);
            $sheet->getStyle($col . $row)->applyFromArray($borderStyle);
            $col++;
        }
        $row++;

        // Placeholder row for Civil Service Eligibility data
        $sheet->setCellValue('A' . $row, 'PLACEHOLDER');
        $sheet->setCellValue('B' . $row, 'PLACEHOLDER');
        $sheet->setCellValue('C' . $row, 'PLACEHOLDER');
        $sheet->setCellValue('D' . $row, 'PLACEHOLDER');
        $sheet->setCellValue('E' . $row, 'PLACEHOLDER');
        $sheet->setCellValue('F' . $row, 'PLACEHOLDER');
        $sheet->setCellValue('G' . $row, 'PLACEHOLDER');
        $sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray($borderStyle);
        $row++;
        $row++;

        // Work Experience Section (Following PDS format)
        if (!empty($workExperience)) {
            $sheet->setCellValue('A' . $row, 'VI. WORK EXPERIENCE');
            $sheet->mergeCells('A' . $row . ':N' . $row);
            $sheet->getStyle('A' . $row)->applyFromArray($sectionStyle);
            $row++;

            // Header row for work experience in PDS format
            $headers = ['INCLUSIVE DATES', 'INCLUSIVE DATES', 'POSITION TITLE', 'DEPARTMENT/AGENCY/OFFICE/COMPANY', 'MONTHLY SALARY', 'SALARY/JOB/PAY GRADE (if applicable)', 'STATUS OF APPOINTMENT', 'GOVT. SERVICE (Y/N)'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . $row, $header);
                $sheet->getStyle($col . $row)->applyFromArray($sectionStyle);
                $sheet->getStyle($col . $row)->applyFromArray($borderStyle);
                $col++;
            }
            $row++;

            // Sub-header for Inclusive Dates
            $sheet->setCellValue('A' . $row, 'FROM');
            $sheet->setCellValue('B' . $row, 'TO');
            $sheet->getStyle('A' . $row)->applyFromArray($sectionStyle);
            $sheet->getStyle('B' . $row)->applyFromArray($sectionStyle);
            $sheet->getStyle('A' . $row)->applyFromArray($borderStyle);
            $sheet->getStyle('B' . $row)->applyFromArray($borderStyle);

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
                $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray($borderStyle);
                $row++;
            }
            $row++;
        }

        // Voluntary Work Section (Following PDS format)
        $sheet->setCellValue('A' . $row, 'VII. VOLUNTARY WORK OR INVOLVEMENT IN CIVIC / NON-GOVERNMENT / PEOPLE / VOLUNTARY ORGANIZATION(S)');
        $sheet->mergeCells('A' . $row . ':N' . $row);
        $sheet->getStyle('A' . $row)->applyFromArray($sectionStyle);
        $row++;

        // Placeholder for Voluntary Work - this would need to be added to the database
        $headers = ['ORGANIZATION', 'INCLUSIVE DATES (MM/DD/YYYY)', 'INCLUSIVE DATES (MM/DD/YYYY)', 'NUMBER OF HOURS', 'POSITION/NATURE OF WORK'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->applyFromArray($sectionStyle);
            $sheet->getStyle($col . $row)->applyFromArray($borderStyle);
            $col++;
        }
        $row++;

        // Sub-header for Inclusive Dates
        $sheet->setCellValue('B' . $row, 'FROM');
        $sheet->setCellValue('C' . $row, 'TO');
        $sheet->getStyle('B' . $row)->applyFromArray($sectionStyle);
        $sheet->getStyle('C' . $row)->applyFromArray($sectionStyle);
        $sheet->getStyle('B' . $row)->applyFromArray($borderStyle);
        $sheet->getStyle('C' . $row)->applyFromArray($borderStyle);

        // Placeholder row for Voluntary Work data
        $sheet->setCellValue('A' . $row, 'PLACEHOLDER');
        $sheet->setCellValue('B' . $row, 'PLACEHOLDER');
        $sheet->setCellValue('C' . $row, 'PLACEHOLDER');
        $sheet->setCellValue('D' . $row, 'PLACEHOLDER');
        $sheet->setCellValue('E' . $row, 'PLACEHOLDER');
        $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray($borderStyle);
        $row++;
        $row++;

        // Learning and Development Section (Following PDS format)
        $sheet->setCellValue('A' . $row, 'VIII. LEARNING AND DEVELOPMENT (L&D) INTERVENTIONS/TRAINING PROGRAMS');
        $sheet->mergeCells('A' . $row . ':N' . $row);
        $sheet->getStyle('A' . $row)->applyFromArray($sectionStyle);
        $row++;

        // Header row for L&D in PDS format
        $headers = ['TITLE OF LEARNING AND DEVELOPMENT INTERVENTIONS/TRAINING PROGRAMS', 'INCLUSIVE DATES (MM/DD/YYYY)', 'INCLUSIVE DATES (MM/DD/YYYY)', 'NUMBER OF HOURS', 'TYPE OF LD (Managerial/Supervisory/Technical/etc.)', 'CONDUCTED/SPONSORED BY (Write in full)'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->applyFromArray($sectionStyle);
            $sheet->getStyle($col . $row)->applyFromArray($borderStyle);
            $col++;
        }
        $row++;

        // Sub-header for Inclusive Dates
        $sheet->setCellValue('B' . $row, 'FROM');
        $sheet->setCellValue('C' . $row, 'TO');
        $sheet->getStyle('B' . $row)->applyFromArray($sectionStyle);
        $sheet->getStyle('C' . $row)->applyFromArray($sectionStyle);
        $sheet->getStyle('B' . $row)->applyFromArray($borderStyle);
        $sheet->getStyle('C' . $row)->applyFromArray($borderStyle);

        // Data rows for training records
        if (!empty($trainingRecords)) {
            foreach ($trainingRecords as $t) {
                $sheet->setCellValue('A' . $row, $t['title']);
                $sheet->setCellValue('B' . $row, $t['date_from'] ? date('m/d/Y', strtotime($t['date_from'])) : '-');
                $sheet->setCellValue('C' . $row, $t['date_to'] ? date('m/d/Y', strtotime($t['date_to'])) : '-');
                $sheet->setCellValue('D' . $row, $t['hours'] ?: '-');
                $sheet->setCellValue('E' . $row, 'TECHNICAL'); // Default type
                $sheet->setCellValue('F' . $row, $t['provider'] ?: '-');
                $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray($borderStyle);
                $row++;
            }
        } else {
            // Placeholder row if no training records
            $sheet->setCellValue('A' . $row, 'PLACEHOLDER');
            $sheet->setCellValue('B' . $row, 'PLACEHOLDER');
            $sheet->setCellValue('C' . $row, 'PLACEHOLDER');
            $sheet->setCellValue('D' . $row, 'PLACEHOLDER');
            $sheet->setCellValue('E' . $row, 'PLACEHOLDER');
            $sheet->setCellValue('F' . $row, 'PLACEHOLDER');
            $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray($borderStyle);
            $row++;
        }
        $row++;

        // Special Skills and Hobbies Section (Following PDS format)
        $sheet->setCellValue('A' . $row, 'IX. SPECIAL SKILLS/HOBBIES');
        $sheet->mergeCells('A' . $row . ':N' . $row);
        $sheet->getStyle('A' . $row)->applyFromArray($sectionStyle);
        $row++;

        // Placeholder for Special Skills - this would need to be added to the database
        $headers = ['SPECIAL SKILLS/HOBBIES', 'REMARKS'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->applyFromArray($sectionStyle);
            $sheet->getStyle($col . $row)->applyFromArray($borderStyle);
            $col++;
        }
        $row++;

        // Placeholder row for Special Skills data
        $sheet->setCellValue('A' . $row, 'PLACEHOLDER');
        $sheet->setCellValue('B' . $row, 'PLACEHOLDER');
        $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray($borderStyle);
        $row++;
        $row++;

        // Recognition/Awards Section (Following PDS format)
        $sheet->setCellValue('A' . $row, 'X. RECOGNITION/AWARDS');
        $sheet->mergeCells('A' . $row . ':N' . $row);
        $sheet->getStyle('A' . $row)->applyFromArray($sectionStyle);
        $row++;

        // Header row for awards in PDS format
        $headers = ['RECOGNITION/AWARDS', 'YEAR CONFERRED', 'CONFERRED BY (Write in full)'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->applyFromArray($sectionStyle);
            $sheet->getStyle($col . $row)->applyFromArray($borderStyle);
            $col++;
        }
        $row++;

        // Data rows for awards records
        if (!empty($awardRecords)) {
            foreach ($awardRecords as $award) {
                $sheet->setCellValue('A' . $row, $award['title']);
                $sheet->setCellValue('B' . $row, $award['award_date'] ? date('Y', strtotime($award['award_date'])) : '-');
                $sheet->setCellValue('C' . $row, $award['awarding_body'] ?: '-');
                $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray($borderStyle);
                $row++;
            }
        } else {
            // Placeholder row if no award records
            $sheet->setCellValue('A' . $row, 'PLACEHOLDER');
            $sheet->setCellValue('B' . $row, 'PLACEHOLDER');
            $sheet->setCellValue('C' . $row, 'PLACEHOLDER');
            $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray($borderStyle);
            $row++;
        }
        $row++;

        // Other Information Section (Following PDS format)
        $sheet->setCellValue('A' . $row, 'XI. OTHER INFORMATION');
        $sheet->mergeCells('A' . $row . ':N' . $row);
        $sheet->getStyle('A' . $row)->applyFromArray($sectionStyle);
        $row++;

        // Placeholder for Other Information - this would need to be added to the database
        $headers = ['OTHER INFORMATION', 'REMARKS'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->applyFromArray($sectionStyle);
            $sheet->getStyle($col . $row)->applyFromArray($borderStyle);
            $col++;
        }
        $row++;

        // Placeholder row for Other Information data
        $sheet->setCellValue('A' . $row, 'PLACEHOLDER');
        $sheet->setCellValue('B' . $row, 'PLACEHOLDER');
        $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray($borderStyle);
        $row++;
        $row++;

        // Signature section (Following PDS format)
        $sheet->setCellValue('A' . $row, 'I CERTIFY, under oath, that I have personally accomplished this CS Form 212, the information herein is true and correct based on the original documents issued, and that I authorize the agency/head of office to verify/validate the contents hereof. I agree that any misrepresentation made in this document and/or any falsified information shall cause the cancellation of my application and shall be grounds for disciplinary and/or criminal action.');
        $sheet->mergeCells('A' . $row . ':N' . $row);
        $sheet->getStyle('A' . $row)->getAlignment()->setWrapText(true);
        $row += 3;

        $sheet->setCellValue('A' . $row, '_______________________________');
        $sheet->setCellValue('J' . $row, '_______________________________');
        $row++;

        $sheet->setCellValue('A' . $row, 'Signature over Printed Name');
        $sheet->setCellValue('J' . $row, 'Date Accomplished');
        $row += 2;

        // Additional notes section
        $sheet->setCellValue('A' . $row, 'Note: This CS Form 212 must be accomplished in a legible manner when filled out manually. Use only a black or blue ballpen. Erasures and alterations are not allowed.');
        $sheet->mergeCells('A' . $row . ':N' . $row);
        $sheet->getStyle('A' . $row)->getAlignment()->setWrapText(true);
        $row++;

        // Footer
        $sheet->setCellValue('A' . $row, 'Generated on: ' . date('F d, Y h:i A'));
        $sheet->setCellValue('F' . $row, 'DARLa HRIS - Department of Agrarian Reform La Union');
        $sheet->getStyle('A' . $row)->getFont()->setItalic(true);
        $sheet->getStyle('F' . $row)->getFont()->setItalic(true);

        // Auto-size columns
        foreach (range('A', 'N') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set column widths optimized for legal size paper (8.5 x 14 inches)
        // This allows for better utilization of the wider paper format while keeping cells appropriately sized
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(8);
        $sheet->getColumnDimension('E')->setWidth(8);
        $sheet->getColumnDimension('F')->setWidth(10);
        $sheet->getColumnDimension('G')->setWidth(10);
        $sheet->getColumnDimension('H')->setWidth(10);
        $sheet->getColumnDimension('I')->setWidth(10);
        $sheet->getColumnDimension('J')->setWidth(10);
        $sheet->getColumnDimension('K')->setWidth(10);
        $sheet->getColumnDimension('L')->setWidth(10);
        $sheet->getColumnDimension('M')->setWidth(10);
        $sheet->getColumnDimension('N')->setWidth(10);

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
