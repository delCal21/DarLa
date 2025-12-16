<?php
// Start output buffering to prevent any output before PDF generation
ob_start();

require_once 'auth.php';
require_admin_login();
require_once 'db.php';

// Clean any output that might have been generated
ob_clean();

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

// Include TCPDF library
$composerAutoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
} else {
    $tcpdfPath = __DIR__ . '/tcpdf/tcpdf.php';
    if (file_exists($tcpdfPath)) {
        require_once $tcpdfPath;
    } else {
        require_once 'header.php';
        echo '<div class="alert alert-danger">';
        echo '<h4><i class="fas fa-exclamation-triangle me-2"></i>PDF Library Not Found</h4>';
        echo '<p>The TCPDF library is required to generate PDF reports.</p>';
        echo '<a href="employee.php?id=' . $id . '" class="btn btn-primary mt-2">Back to Employee Profile</a>';
        echo '</div>';
        require_once 'footer.php';
        exit;
    }
}

/**
 * Locate the first existing logo file from provided filenames.
 *
 * @param array $filenames
 * @return string|null
 */
function findLogoPath($filenames) {
    $basePaths = array(
        __DIR__ . '/',
        __DIR__ . '/../',
        '' // relative to current working directory
    );
    
    foreach ($filenames as $filename) {
        foreach ($basePaths as $base) {
            $fullPath = $base . $filename;
            if (file_exists($fullPath)) {
                return $fullPath;
            }
        }
    }
    return null;
}

// Professional PDF Class
class EmployeePDF extends TCPDF {
    private $headerColor = [25, 135, 84]; // Green color
    private $accentColor = [20, 108, 67]; // Darker green
    private $logoPath = '';
    
    public function setLogoPath($path) {
        $this->logoPath = $path;
    }
    
    public function Header() {
        // Increase header height to accommodate logo and text
        $headerHeight = 45;
        $this->SetFillColor(255, 255, 255);
        $this->Rect(0, 0, $this->getPageWidth(), $headerHeight, 'F');
        
        $hasImageSupport = extension_loaded('gd') || extension_loaded('imagick');
        $canRenderLogo = false;
        
        if ($this->logoPath && file_exists($this->logoPath)) {
            $extension = strtolower(pathinfo($this->logoPath, PATHINFO_EXTENSION));
            $requiresAlpha = in_array($extension, ['png', 'gif', 'webp']);
            $canRenderLogo = !$requiresAlpha || $hasImageSupport;
        }

        // Logo at the top center (only render when possible)
        if ($canRenderLogo) {
            try {
                // Center logo at the top
                $logoSize = 30;
                $xPos = ($this->getPageWidth() - $logoSize) / 2;
                // Try to load image - use mask=false to avoid alpha channel issues
                $imageInfo = @getimagesize($this->logoPath);
                if ($imageInfo !== false) {
                    // Try loading with different parameters
                    $this->Image($this->logoPath, $xPos, 3, $logoSize, $logoSize, '', '', 'T', false, 300, '', false, false, 0, false, false, false);
                }
            } catch (Exception $e) {
                // If image fails, continue without logo
            } catch (Error $e) {
                // Handle fatal errors gracefully
            }
        }
        
        // Spelled out DAR below logo
        $this->SetFont('helvetica', 'B', 14);
        $this->SetTextColor($this->headerColor[0], $this->headerColor[1], $this->headerColor[2]);
        $this->SetY(35);
        $this->Cell(0, 6, 'Department of Agrarian Reform', 0, 0, 'C');
        
        // Subtitle
        $this->SetFont('helvetica', '', 9);
        $this->SetY(41);
        $this->SetTextColor(73, 80, 87);
        $this->Cell(0, 4, 'La Union - DARLa HRIS', 0, 0, 'C');
        
        // Line separator
        $this->SetFillColor($this->accentColor[0], $this->accentColor[1], $this->accentColor[2]);
        $this->Rect(0, $headerHeight, $this->getPageWidth(), 2, 'F');
        
        $this->SetY($headerHeight + 5);
    }

    public function Footer() {
        $this->SetY(-20);
        
        // Footer line
        $this->SetFillColor($this->headerColor[0], $this->headerColor[1], $this->headerColor[2]);
        $this->Rect(0, $this->GetY(), $this->getPageWidth(), 1, 'F');
        
        $this->SetY(-18);
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(100, 100, 100);
        
        // Left: Generation date
        $this->Cell(90, 5, 'Generated on: ' . date('F d, Y h:i A'), 0, 0, 'L');
        
        // Right: Page number
        $this->Cell(0, 5, 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages(), 0, 0, 'R');
        
        // Bottom center: System name
        $this->SetY(-12);
        $this->SetFont('helvetica', '', 7);
        $this->Cell(0, 3, 'DARLa HRIS - Employee Records Management System', 0, 0, 'C');
    }
    
    // Add section header
    public function addSectionHeader($title, $icon = '') {
        // Check if we need a new page before adding section
        if ($this->GetY() > ($this->getPageHeight() - 50)) {
            $this->AddPage();
        }
        $this->Ln(8);
        $this->SetFillColor(245, 245, 245);
        $this->SetDrawColor(220, 220, 220);
        $this->SetLineWidth(0.3);
        $this->SetFont('helvetica', 'B', 12);
        $this->SetTextColor(33, 37, 41);
        $this->Cell(0, 9, $icon . ' ' . $title, 'TB', 1, 'L', true);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('helvetica', '', 10);
        $this->Ln(5);
    }
    
    // Add info row
    public function addInfoRow($label, $value, $labelWidth = 50) {
        $this->SetFont('helvetica', 'B', 9);
        $this->SetTextColor(60, 60, 60);
        $this->Cell($labelWidth, 7, $label . ':', 0, 0, 'L');
        $this->SetFont('helvetica', '', 9);
        $this->SetTextColor(0, 0, 0);
        $this->MultiCell(0, 7, $value ?: 'Not provided', 0, 'L');
    }
}

// Create PDF
$pdf = new EmployeePDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set logo path - prefer JPG versions first (no GD/Imagick required)
$hasImageSupport = extension_loaded('gd') || extension_loaded('imagick');
$headerLogoPath = findLogoPath(['BG-DAR1.jpg', 'BG-DAR.jpg', 'BG-DAR1.jpeg', 'BG-DAR.jpeg']);

if ($headerLogoPath === null) {
    $pngCandidate = findLogoPath(['BG-DAR1.png', 'BG-DAR.png']);
    if ($pngCandidate !== null && $hasImageSupport) {
        $headerLogoPath = $pngCandidate;
    }
}

if ($headerLogoPath !== null) {
    $pdf->setLogoPath($headerLogoPath);
}

// Set document information
$fullName = trim($employee['last_name'] . ', ' . $employee['first_name'] . ($employee['middle_name'] ? ' ' . $employee['middle_name'] : ''));
$pdf->SetCreator('DARLa HRIS');
$pdf->SetAuthor('DARLa HRIS');
$pdf->SetTitle('Employee Record - ' . $fullName);
$pdf->SetSubject('Employee Information');

$pdf->setPrintHeader(true);
$pdf->setPrintFooter(true);

// Set margins - professional margins to prevent overlap
// Left, Top, Right margins - increased for better presentation
$pdf->SetMargins(15, 55, 15);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(20);

// Set auto page break with sufficient bottom margin
$pdf->SetAutoPageBreak(TRUE, 30);

// Calculate available width for tables (page width - left margin - right margin)
$pageWidth = $pdf->getPageWidth();
$margins = $pdf->getMargins();
$leftMargin = $margins['left'];
$rightMargin = $margins['right'];
$availableWidth = $pageWidth - $leftMargin - $rightMargin;

// Ensure available width doesn't exceed page boundaries
if ($availableWidth > ($pageWidth - 30)) {
    $availableWidth = $pageWidth - 30;
}
$tableHeaderHeight = 8;
$tableRowHeight = 6;
$tableHeaderFontSize = 9;
$tableBodyFontSize = 7;
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Add cover page
$pdf->AddPage();

// Cover page design - white background for cleaner look
$pdf->SetFillColor(255, 255, 255);
$pdf->Rect(0, 0, $pdf->getPageWidth(), $pdf->getPageHeight(), 'F');

// Logo on cover (with error handling)
// Prefer JPG so it works even without GD/Imagick; fallback to PNG only if supported
$coverLogoPath = findLogoPath(['BG-DAR1.jpg', 'BG-DAR.jpg', 'BG-DAR1.jpeg', 'BG-DAR.jpeg']);
if ($coverLogoPath === null) {
    $pngCandidate = findLogoPath(['BG-DAR1.png', 'BG-DAR.png']);
    if ($pngCandidate !== null && $hasImageSupport) {
        $coverLogoPath = $pngCandidate;
    }
}

$logoDisplayed = false;
if ($coverLogoPath !== null) {
    try {
        $imageInfo = @getimagesize($coverLogoPath);
        if ($imageInfo !== false) {
            $logoWidth = 80;
            $logoHeight = 80;
            $xPos = ($pdf->getPageWidth() - $logoWidth) / 2;
            $yPos = 50;
            $pdf->Image($coverLogoPath, $xPos, $yPos, $logoWidth, $logoHeight, '', '', 'T', false, 300, '', false, false, 0, false, false, false);
            $logoDisplayed = true;
        }
    } catch (Exception $e) {
        $logoDisplayed = false;
    } catch (Error $e) {
        $logoDisplayed = false;
    }
}

// Fallback to text if logo didn't display
if (!$logoDisplayed) {
    $pdf->SetFont('helvetica', 'B', 24);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetY(85);
    $pdf->Cell(0, 10, 'DAR', 0, 1, 'C');
}

// Cover title
$pdf->SetY(130);
$pdf->SetFont('helvetica', 'B', 28);
$pdf->SetTextColor(25, 135, 84);
$pdf->Cell(0, 15, 'EMPLOYEE RECORD', 0, 1, 'C');

$pdf->SetFont('helvetica', '', 16);
$pdf->SetTextColor(33, 37, 41);
$pdf->Cell(0, 10, $fullName, 0, 1, 'C');

$pdf->SetY(180);
$pdf->SetFont('helvetica', '', 12);
$pdf->SetTextColor(73, 80, 87);
$pdf->Cell(0, 8, 'DARLa HRIS', 0, 1, 'C');
$pdf->Cell(0, 6, 'Department of Agrarian Reform - La Union', 0, 1, 'C');

$pdf->SetY(240);
$pdf->SetFont('helvetica', 'I', 10);
$pdf->SetTextColor(108, 117, 125);
$pdf->Cell(0, 5, date('F d, Y'), 0, 1, 'C');

// Add main content page
$pdf->AddPage();

// Employee Name Header
$pdf->SetFillColor(240, 240, 240);
$pdf->SetTextColor(33, 37, 41);
$pdf->SetDrawColor(220, 220, 220);
$pdf->SetLineWidth(0.3);
$pdf->SetFont('helvetica', 'B', 20);
$pdf->Cell(0, 15, $fullName, 'TB', 1, 'C', true);
$pdf->Ln(8);

// Personal Information Section
$pdf->addSectionHeader('Personal Information');
$pdf->SetFont('helvetica', '', 9);

$personalInfo = [
    'Birthdate' => $employee['birthdate'] ? date('F d, Y', strtotime($employee['birthdate'])) : 'Not provided',
    'Home Address' => $employee['home_address'] ?: 'Not provided',
    'Contact Number' => $employee['contact_no'] ?: 'Not provided',
    'Email Address' => $employee['email'] ?: 'Not provided',
    'Civil Status' => $employee['civil_status'] ?: 'Not provided',
    'Spouse Name' => $employee['spouse_name'] ?: 'Not provided',
    'Spouse Contact' => $employee['spouse_contact_no'] ?: 'Not provided',
];

foreach ($personalInfo as $label => $value) {
    $pdf->addInfoRow($label, $value, 50);
}

$pdf->Ln(8);

// Employee Information Section
$pdf->addSectionHeader('Employee Information');

$employeeInfo = [
    'Employee ID' => $employee['employee_number'] ?: 'Not assigned',
    'BP Number' => $employee['bp_number'] ?: 'Not provided',
    'Pag-ibig Number' => $employee['pagibig_number'] ?: 'Not provided',
    'PhilHealth Number' => $employee['philhealth_number'] ?: 'Not provided',
    'TIN #' => $employee['tin_number'] ?: 'Not provided',
    'SSS #' => $employee['sss_number'] ?: 'Not provided',
    'GSIS #' => $employee['gsis_number'] ?: 'Not provided',
    'Employment Status' => $employee['employment_status'] ?: 'Not provided',
];

foreach ($employeeInfo as $label => $value) {
    $pdf->addInfoRow($label, $value, 50);
}

if ($employee['designations']) {
    $pdf->addInfoRow('Designations', $employee['designations'], 50);
}

$pdf->Ln(8);

// Educational Background
if (!empty($educationalBackground)) {
    $pdf->addSectionHeader('Educational Background');
    
    // Use smaller font for educational background table to fit 8 columns
    $eduTableHeaderFontSize = 8;
    $eduTableBodyFontSize = 7;
    $eduTableRowHeight = 5;
    
    $eduColumns = array(
        array('label' => 'Level', 'width' => 0.11, 'align' => 'L'),
        array('label' => 'School Name', 'width' => 0.23, 'align' => 'L'),
        array('label' => 'Degree/Course', 'width' => 0.18, 'align' => 'L'),
        array('label' => 'From', 'width' => 0.09, 'align' => 'C'),
        array('label' => 'To', 'width' => 0.09, 'align' => 'C'),
        array('label' => 'Units Earned', 'width' => 0.09, 'align' => 'L'),
        array('label' => 'Year Graduated', 'width' => 0.07, 'align' => 'C'),
        array('label' => 'Honors', 'width' => 0.14, 'align' => 'L'),
    );
    
    // Verify total width equals 1.0 (100%)
    $totalWidth = 0;
    foreach ($eduColumns as $column) {
        $totalWidth += $column['width'];
    }
    
    $eduWidths = array();
    foreach ($eduColumns as $column) {
        $eduWidths[] = $availableWidth * $column['width'];
    }
    
    // Professional table styling - white header with dark text, light gray borders
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(33, 37, 41);
    $pdf->SetDrawColor(220, 220, 220);
    $pdf->SetLineWidth(0.2);
    $pdf->SetFont('helvetica', 'B', $eduTableHeaderFontSize);
    foreach ($eduColumns as $index => $column) {
        $pdf->Cell($eduWidths[$index], $tableHeaderHeight, $column['label'], 1, ($index === count($eduColumns) - 1 ? 1 : 0), 'C', true);
    }
    
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', $eduTableBodyFontSize);
    $fill = false;
    
    foreach ($educationalBackground as $edu) {
        // Check if we need a new page - leave space for header and at least 2 rows
        if ($pdf->GetY() > ($pdf->getPageHeight() - 60)) {
            $pdf->AddPage();
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetTextColor(33, 37, 41);
            $pdf->SetDrawColor(220, 220, 220);
            $pdf->SetLineWidth(0.2);
            $pdf->SetFont('helvetica', 'B', $eduTableHeaderFontSize);
            foreach ($eduColumns as $index => $column) {
                $pdf->Cell($eduWidths[$index], $tableHeaderHeight, $column['label'], 1, ($index === count($eduColumns) - 1 ? 1 : 0), 'C', true);
            }
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', '', $eduTableBodyFontSize);
        }
        
        $pdf->SetFillColor($fill ? 250 : 255, $fill ? 250 : 255, $fill ? 250 : 255);
        $pdf->Cell($eduWidths[0], $eduTableRowHeight, substr($edu['level'] ?: '-', 0, 10), 1, 0, 'L', $fill);
        $pdf->Cell($eduWidths[1], $eduTableRowHeight, substr($edu['school_name'] ?: '-', 0, 20), 1, 0, 'L', $fill);
        $pdf->Cell($eduWidths[2], $eduTableRowHeight, substr($edu['degree_course'] ?: '-', 0, 15), 1, 0, 'L', $fill);
        $pdf->Cell($eduWidths[3], $eduTableRowHeight, substr($edu['period_from'] ?: '-', 0, 8), 1, 0, 'C', $fill);
        $pdf->Cell($eduWidths[4], $eduTableRowHeight, substr($edu['period_to'] ?: '-', 0, 8), 1, 0, 'C', $fill);
        $pdf->Cell($eduWidths[5], $eduTableRowHeight, substr($edu['highest_level_units'] ?: '-', 0, 10), 1, 0, 'L', $fill);
        $pdf->Cell($eduWidths[6], $eduTableRowHeight, substr($edu['year_graduated'] ?: '-', 0, 6), 1, 0, 'C', $fill);
        $pdf->Cell($eduWidths[7], $eduTableRowHeight, substr($edu['scholarship_honors'] ?: '-', 0, 15), 1, 1, 'L', $fill);
        $fill = !$fill;
    }
    $pdf->Ln(8);
}

// Work Experience
if (!empty($workExperience)) {
    $pdf->addSectionHeader('Work Experience');
    
    // Use smaller font for work experience table to fit all columns
    $weTableHeaderFontSize = 7;
    $weTableBodyFontSize = 6;
    $weTableRowHeight = 5;
    
    $weColumns = array(
        array('label' => 'From', 'width' => 0.10, 'align' => 'C'),
        array('label' => 'To', 'width' => 0.10, 'align' => 'C'),
        array('label' => 'Position Title', 'width' => 0.18, 'align' => 'L'),
        array('label' => 'Department/Agency/Office/Company', 'width' => 0.20, 'align' => 'L'),
        array('label' => 'Monthly Salary', 'width' => 0.10, 'align' => 'R'),
        array('label' => 'Salary Grade & Step', 'width' => 0.08, 'align' => 'C'),
        array('label' => 'Status', 'width' => 0.12, 'align' => 'C'),
        array('label' => 'Gov\'t Service', 'width' => 0.12, 'align' => 'C'),
    );
    
    $weWidths = array();
    foreach ($weColumns as $column) {
        $weWidths[] = $availableWidth * $column['width'];
    }
    
    // Professional table styling - white header with dark text, light gray borders
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(33, 37, 41);
    $pdf->SetDrawColor(220, 220, 220);
    $pdf->SetLineWidth(0.2);
    $pdf->SetFont('helvetica', 'B', $weTableHeaderFontSize);
    foreach ($weColumns as $index => $column) {
        $pdf->Cell($weWidths[$index], $tableHeaderHeight, $column['label'], 1, ($index === count($weColumns) - 1 ? 1 : 0), 'C', true);
    }
    
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', $weTableBodyFontSize);
    $fill = false;
    
    foreach ($workExperience as $we) {
        // Check if we need a new page - leave space for header and at least 2 rows
        if ($pdf->GetY() > ($pdf->getPageHeight() - 60)) {
            $pdf->AddPage();
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetTextColor(33, 37, 41);
            $pdf->SetDrawColor(220, 220, 220);
            $pdf->SetLineWidth(0.2);
            $pdf->SetFont('helvetica', 'B', $weTableHeaderFontSize);
            foreach ($weColumns as $index => $column) {
                $pdf->Cell($weWidths[$index], $tableHeaderHeight, $column['label'], 1, ($index === count($weColumns) - 1 ? 1 : 0), 'C', true);
            }
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', '', $weTableBodyFontSize);
        }
        
        $pdf->SetFillColor($fill ? 250 : 255, $fill ? 250 : 255, $fill ? 250 : 255);
        $pdf->Cell($weWidths[0], $weTableRowHeight, isset($we['date_from']) && $we['date_from'] ? date('m/d/Y', strtotime($we['date_from'])) : '-', 1, 0, 'C', $fill);
        $pdf->Cell($weWidths[1], $weTableRowHeight, isset($we['date_to']) && $we['date_to'] ? date('m/d/Y', strtotime($we['date_to'])) : 'Present', 1, 0, 'C', $fill);
        $pdf->Cell($weWidths[2], $weTableRowHeight, substr(($we['position_title'] ?? '-'), 0, 18), 1, 0, 'L', $fill);
        $pdf->Cell($weWidths[3], $weTableRowHeight, substr(($we['department_agency'] ?? '-'), 0, 20), 1, 0, 'L', $fill);
        $pdf->Cell($weWidths[4], $weTableRowHeight, isset($we['monthly_salary']) && $we['monthly_salary'] ? '₱' . number_format((float)$we['monthly_salary'], 2) : '-', 1, 0, 'R', $fill);
        $pdf->Cell($weWidths[5], $weTableRowHeight, substr(($we['salary_grade_step'] ?? '-'), 0, 8), 1, 0, 'C', $fill);
        $pdf->Cell($weWidths[6], $weTableRowHeight, substr(($we['status_of_appointment'] ?? '-'), 0, 12), 1, 0, 'C', $fill);
        $pdf->Cell($weWidths[7], $weTableRowHeight, substr(($we['govt_service'] ?? 'YES'), 0, 12), 1, 1, 'C', $fill);
        
        if (!empty($we['description_of_duties'])) {
            $pdf->SetX($leftMargin);
            $pdf->SetFont('helvetica', 'I', 5);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->Cell(0, 3, 'Duties: ' . substr($we['description_of_duties'], 0, 120), 0, 1, 'L');
            $pdf->SetFont('helvetica', '', $weTableBodyFontSize);
            $pdf->SetTextColor(0, 0, 0);
        }
        $fill = !$fill;
    }
    $pdf->Ln(8);
}

// Appointment & Promotion History
if (!empty($appointments)) {
    $pdf->addSectionHeader('Appointment & Promotion History');
    
    // Table with professional styling
    $appointmentColumns = array(
        array('label' => 'Type', 'width' => 0.20, 'align' => 'L'),
        array('label' => 'Position', 'width' => 0.30, 'align' => 'L'),
        array('label' => 'Item #', 'width' => 0.12, 'align' => 'C'),
        array('label' => 'Salary Grade', 'width' => 0.12, 'align' => 'C'),
        array('label' => 'Date', 'width' => 0.13, 'align' => 'C'),
        array('label' => 'Salary', 'width' => 0.13, 'align' => 'R'),
    );
    $appointmentWidths = array();
    foreach ($appointmentColumns as $column) {
        $appointmentWidths[] = $availableWidth * $column['width'];
    }
    
    // Professional table styling - white header with dark text, light gray borders
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(33, 37, 41);
    $pdf->SetDrawColor(220, 220, 220);
    $pdf->SetLineWidth(0.2);
    $pdf->SetFont('helvetica', 'B', $tableHeaderFontSize);
    foreach ($appointmentColumns as $index => $column) {
        $pdf->Cell($appointmentWidths[$index], $tableHeaderHeight, $column['label'], 1, ($index === count($appointmentColumns) - 1 ? 1 : 0), 'C', true);
    }
    
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', $tableBodyFontSize);
    $fill = false;
    
    foreach ($appointments as $a) {
        // Check if we need a new page - leave space for header and at least 2 rows
        if ($pdf->GetY() > ($pdf->getPageHeight() - 60)) {
            $pdf->AddPage();
            // Redraw header
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetTextColor(33, 37, 41);
            $pdf->SetDrawColor(220, 220, 220);
            $pdf->SetLineWidth(0.2);
            $pdf->SetFont('helvetica', 'B', $tableHeaderFontSize);
            foreach ($appointmentColumns as $index => $column) {
                $pdf->Cell($appointmentWidths[$index], $tableHeaderHeight, $column['label'], 1, ($index === count($appointmentColumns) - 1 ? 1 : 0), 'C', true);
            }
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', '', $tableBodyFontSize);
        }
        
        $pdf->SetFillColor($fill ? 250 : 255, $fill ? 250 : 255, $fill ? 250 : 255);
        $pdf->Cell($appointmentWidths[0], $tableRowHeight, substr($a['type_label'] ?: '-', 0, 20), 1, 0, 'L', $fill);
        $pdf->Cell($appointmentWidths[1], $tableRowHeight, substr($a['position'] ?: '-', 0, 30), 1, 0, 'L', $fill);
        $pdf->Cell($appointmentWidths[2], $tableRowHeight, substr($a['item_number'] ?: '-', 0, 12), 1, 0, 'C', $fill);
        $pdf->Cell($appointmentWidths[3], $tableRowHeight, substr($a['salary_grade'] ?: '-', 0, 12), 1, 0, 'C', $fill);
        $pdf->Cell($appointmentWidths[4], $tableRowHeight, $a['appointment_date'] ? date('M d, Y', strtotime($a['appointment_date'])) : '-', 1, 0, 'C', $fill);
        $pdf->Cell($appointmentWidths[5], $tableRowHeight, $a['salary'] ? '₱' . number_format((float)$a['salary'], 2, '.', ',') : '-', 1, 1, 'R', $fill);
        $fill = !$fill;
    }
    $pdf->Ln(8);
}

// Training Records
if (!empty($trainingRecords)) {
    $pdf->addSectionHeader('Training Records');
    
    $trainingColumns = array(
        array('label' => 'Title', 'width' => 0.28, 'align' => 'L'),
        array('label' => 'Provider', 'width' => 0.22, 'align' => 'L'),
        array('label' => 'Location', 'width' => 0.20, 'align' => 'L'),
        array('label' => 'From', 'width' => 0.12, 'align' => 'C'),
        array('label' => 'To', 'width' => 0.12, 'align' => 'C'),
        array('label' => 'Hours', 'width' => 0.06, 'align' => 'C'),
    );
    $trainingWidths = array();
    foreach ($trainingColumns as $column) {
        $trainingWidths[] = $availableWidth * $column['width'];
    }
    
    // Professional table styling - white header with dark text, light gray borders
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(33, 37, 41);
    $pdf->SetDrawColor(220, 220, 220);
    $pdf->SetLineWidth(0.2);
    $pdf->SetFont('helvetica', 'B', $tableHeaderFontSize);
    foreach ($trainingColumns as $index => $column) {
        $pdf->Cell($trainingWidths[$index], $tableHeaderHeight, $column['label'], 1, ($index === count($trainingColumns) - 1 ? 1 : 0), 'C', true);
    }
    
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', $tableBodyFontSize);
    $fill = false;
    
    foreach ($trainingRecords as $t) {
        // Check if we need a new page - leave space for header and at least 2 rows
        if ($pdf->GetY() > ($pdf->getPageHeight() - 60)) {
            $pdf->AddPage();
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetTextColor(33, 37, 41);
            $pdf->SetDrawColor(220, 220, 220);
            $pdf->SetLineWidth(0.2);
            $pdf->SetFont('helvetica', 'B', $tableHeaderFontSize);
            foreach ($trainingColumns as $index => $column) {
                $pdf->Cell($trainingWidths[$index], $tableHeaderHeight, $column['label'], 1, ($index === count($trainingColumns) - 1 ? 1 : 0), 'C', true);
            }
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', '', $tableBodyFontSize);
        }
        
        $pdf->SetFillColor($fill ? 250 : 255, $fill ? 250 : 255, $fill ? 250 : 255);
        $pdf->Cell($trainingWidths[0], $tableRowHeight, substr($t['title'] ?: '-', 0, 30), 1, 0, 'L', $fill);
        $pdf->Cell($trainingWidths[1], $tableRowHeight, substr($t['provider'] ?: '-', 0, 20), 1, 0, 'L', $fill);
        $pdf->Cell($trainingWidths[2], $tableRowHeight, substr($t['location'] ?: '-', 0, 20), 1, 0, 'L', $fill);
        $pdf->Cell($trainingWidths[3], $tableRowHeight, $t['date_from'] ? date('M d, Y', strtotime($t['date_from'])) : '-', 1, 0, 'C', $fill);
        $pdf->Cell($trainingWidths[4], $tableRowHeight, $t['date_to'] ? date('M d, Y', strtotime($t['date_to'])) : '-', 1, 0, 'C', $fill);
        $pdf->Cell($trainingWidths[5], $tableRowHeight, $t['hours'] ? number_format((float)$t['hours'], 1) : '-', 1, 1, 'C', $fill);
        
        if (!empty($t['remarks'])) {
            $pdf->SetX($leftMargin);
            $pdf->SetFont('helvetica', 'I', 6);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->Cell(0, 3, 'Remarks: ' . substr($t['remarks'], 0, 100), 0, 1, 'L');
            $pdf->SetFont('helvetica', '', $tableBodyFontSize);
            $pdf->SetTextColor(0, 0, 0);
        }
        $fill = !$fill;
    }
    $pdf->Ln(8);
}

// Leave Records
if (!empty($leaveRecords)) {
    $pdf->addSectionHeader('Leave Records');
    
    // Professional table styling - white header with dark text, light gray borders
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(33, 37, 41);
    $pdf->SetDrawColor(220, 220, 220);
    $pdf->SetLineWidth(0.2);
    $pdf->SetFont('helvetica', 'B', $tableHeaderFontSize);
    
    $lcol1 = $availableWidth * 0.25; // Type
    $lcol2 = $availableWidth * 0.18; // From
    $lcol3 = $availableWidth * 0.18; // To
    $lcol4 = $availableWidth * 0.12; // Days
    $lcol5 = $availableWidth * 0.27; // Remarks
    
    $pdf->Cell($lcol1, $tableHeaderHeight, 'Type', 1, 0, 'C', true);
    $pdf->Cell($lcol2, $tableHeaderHeight, 'From', 1, 0, 'C', true);
    $pdf->Cell($lcol3, $tableHeaderHeight, 'To', 1, 0, 'C', true);
    $pdf->Cell($lcol4, $tableHeaderHeight, 'Days', 1, 0, 'C', true);
    $pdf->Cell($lcol5, $tableHeaderHeight, 'Remarks', 1, 1, 'C', true);
    
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', $tableBodyFontSize);
    $fill = false;
    
    foreach ($leaveRecords as $l) {
        // Check if we need a new page - leave space for header and at least 2 rows
        if ($pdf->GetY() > ($pdf->getPageHeight() - 60)) {
            $pdf->AddPage();
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetTextColor(33, 37, 41);
            $pdf->SetDrawColor(220, 220, 220);
            $pdf->SetLineWidth(0.2);
            $pdf->SetFont('helvetica', 'B', $tableHeaderFontSize);
            $pdf->Cell($lcol1, $tableHeaderHeight, 'Type', 1, 0, 'C', true);
            $pdf->Cell($lcol2, $tableHeaderHeight, 'From', 1, 0, 'C', true);
            $pdf->Cell($lcol3, $tableHeaderHeight, 'To', 1, 0, 'C', true);
            $pdf->Cell($lcol4, $tableHeaderHeight, 'Days', 1, 0, 'C', true);
            $pdf->Cell($lcol5, $tableHeaderHeight, 'Remarks', 1, 1, 'C', true);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', '', $tableBodyFontSize);
        }
        
        $pdf->SetFillColor($fill ? 250 : 255, $fill ? 250 : 255, $fill ? 250 : 255);
        $pdf->Cell($lcol1, $tableRowHeight, substr($l['leave_type'] ?: '-', 0, 22), 1, 0, 'L', $fill);
        $pdf->Cell($lcol2, $tableRowHeight, $l['date_from'] ? date('M d, Y', strtotime($l['date_from'])) : '-', 1, 0, 'C', $fill);
        $pdf->Cell($lcol3, $tableRowHeight, $l['date_to'] ? date('M d, Y', strtotime($l['date_to'])) : '-', 1, 0, 'C', $fill);
        $pdf->Cell($lcol4, $tableRowHeight, $l['days'] ? number_format((float)$l['days'], 1) : '-', 1, 0, 'C', $fill);
        $pdf->Cell($lcol5, $tableRowHeight, substr($l['remarks'] ?: '-', 0, 35), 1, 1, 'L', $fill);
        $fill = !$fill;
    }
    $pdf->Ln(8);
}

// Service Records
if (!empty($serviceRecords)) {
    $pdf->addSectionHeader('Service Record Entries');
    
    // Use smaller font for the complex service record table
    $serviceTableFontSize = 6;
    $serviceRowHeight = 5;
    
    // Professional table styling - white header with dark text, light gray borders
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(33, 37, 41);
    $pdf->SetDrawColor(220, 220, 220);
    $pdf->SetLineWidth(0.2);
    $pdf->SetFont('helvetica', 'B', $serviceTableFontSize);
    
    // First header row - grouped columns
    $pdf->Cell($availableWidth * 0.16, $tableHeaderHeight, 'SERVICE', 1, 0, 'C', true);
    $pdf->Cell($availableWidth * 0.20, $tableHeaderHeight, 'RECORD OF APPOINTMENT', 1, 0, 'C', true);
    $pdf->Cell($availableWidth * 0.28, $tableHeaderHeight, 'OFFICE ENTITY/DIVISION', 1, 0, 'C', true);
    $pdf->Cell($availableWidth * 0.20, $tableHeaderHeight, 'SEPARATION', 1, 0, 'C', true);
    $pdf->Cell($availableWidth * 0.16, $tableHeaderHeight, '', 1, 1, 'C', true);
    
    // Second header row - individual columns
    $pdf->SetFont('helvetica', 'B', $serviceTableFontSize);
    $pdf->Cell($availableWidth * 0.08, $tableHeaderHeight, 'From', 1, 0, 'C', true);
    $pdf->Cell($availableWidth * 0.08, $tableHeaderHeight, 'To', 1, 0, 'C', true);
    $pdf->Cell($availableWidth * 0.10, $tableHeaderHeight, 'Designation', 1, 0, 'C', true);
    $pdf->Cell($availableWidth * 0.05, $tableHeaderHeight, 'Status', 1, 0, 'C', true);
    $pdf->Cell($availableWidth * 0.05, $tableHeaderHeight, 'Salary', 1, 0, 'C', true);
    $pdf->Cell($availableWidth * 0.07, $tableHeaderHeight, 'Place of', 1, 0, 'C', true);
    $pdf->Cell($availableWidth * 0.07, $tableHeaderHeight, 'Branch', 1, 0, 'C', true);
    $pdf->Cell($availableWidth * 0.07, $tableHeaderHeight, 'Assignment', 1, 0, 'C', true);
    $pdf->Cell($availableWidth * 0.07, $tableHeaderHeight, 'LV ABS', 1, 0, 'C', true);
    $pdf->Cell($availableWidth * 0.06, $tableHeaderHeight, 'W/O Pay', 1, 0, 'C', true);
    $pdf->Cell($availableWidth * 0.07, $tableHeaderHeight, 'Date', 1, 0, 'C', true);
    $pdf->Cell($availableWidth * 0.07, $tableHeaderHeight, 'Cause', 1, 0, 'C', true);
    $pdf->Cell($availableWidth * 0.16, $tableHeaderHeight, '', 1, 1, 'C', true);
    
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', $serviceTableFontSize);
    $fill = false;
    
    foreach ($serviceRecords as $s) {
        // Check if we need a new page - leave space for header and at least 2 rows
        if ($pdf->GetY() > ($pdf->getPageHeight() - 80)) {
            $pdf->AddPage();
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetTextColor(33, 37, 41);
            $pdf->SetDrawColor(220, 220, 220);
            $pdf->SetLineWidth(0.2);
            $pdf->SetFont('helvetica', 'B', $serviceTableFontSize);
            // Redraw headers
            $pdf->Cell($availableWidth * 0.16, $tableHeaderHeight, 'SERVICE', 1, 0, 'C', true);
            $pdf->Cell($availableWidth * 0.20, $tableHeaderHeight, 'RECORD OF APPOINTMENT', 1, 0, 'C', true);
            $pdf->Cell($availableWidth * 0.28, $tableHeaderHeight, 'OFFICE ENTITY/DIVISION', 1, 0, 'C', true);
            $pdf->Cell($availableWidth * 0.20, $tableHeaderHeight, 'SEPARATION', 1, 0, 'C', true);
            $pdf->Cell($availableWidth * 0.16, $tableHeaderHeight, '', 1, 1, 'C', true);
            $pdf->Cell($availableWidth * 0.08, $tableHeaderHeight, 'From', 1, 0, 'C', true);
            $pdf->Cell($availableWidth * 0.08, $tableHeaderHeight, 'To', 1, 0, 'C', true);
            $pdf->Cell($availableWidth * 0.10, $tableHeaderHeight, 'Designation', 1, 0, 'C', true);
            $pdf->Cell($availableWidth * 0.05, $tableHeaderHeight, 'Status', 1, 0, 'C', true);
            $pdf->Cell($availableWidth * 0.05, $tableHeaderHeight, 'Salary', 1, 0, 'C', true);
            $pdf->Cell($availableWidth * 0.07, $tableHeaderHeight, 'Place of', 1, 0, 'C', true);
            $pdf->Cell($availableWidth * 0.07, $tableHeaderHeight, 'Branch', 1, 0, 'C', true);
            $pdf->Cell($availableWidth * 0.07, $tableHeaderHeight, 'Assignment', 1, 0, 'C', true);
            $pdf->Cell($availableWidth * 0.07, $tableHeaderHeight, 'LV ABS', 1, 0, 'C', true);
            $pdf->Cell($availableWidth * 0.06, $tableHeaderHeight, 'W/O Pay', 1, 0, 'C', true);
            $pdf->Cell($availableWidth * 0.07, $tableHeaderHeight, 'Date', 1, 0, 'C', true);
            $pdf->Cell($availableWidth * 0.07, $tableHeaderHeight, 'Cause', 1, 0, 'C', true);
            $pdf->Cell($availableWidth * 0.16, $tableHeaderHeight, '', 1, 1, 'C', true);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', '', $serviceTableFontSize);
        }
        
        $pdf->SetFillColor($fill ? 250 : 255, $fill ? 250 : 255, $fill ? 250 : 255);
        $pdf->Cell($availableWidth * 0.08, $serviceRowHeight, isset($s['date_from']) && $s['date_from'] ? date('M d, Y', strtotime($s['date_from'])) : '-', 1, 0, 'C', $fill);
        $pdf->Cell($availableWidth * 0.08, $serviceRowHeight, isset($s['date_to']) && $s['date_to'] ? date('M d, Y', strtotime($s['date_to'])) : '-', 1, 0, 'C', $fill);
        $pdf->Cell($availableWidth * 0.10, $serviceRowHeight, substr(($s['position'] ?? '-'), 0, 12), 1, 0, 'L', $fill);
        $pdf->Cell($availableWidth * 0.05, $serviceRowHeight, substr(($s['status'] ?? '-'), 0, 6), 1, 0, 'C', $fill);
        $pdf->Cell($availableWidth * 0.05, $serviceRowHeight, isset($s['salary']) && $s['salary'] ? '₱' . number_format((float)$s['salary'], 0, '.', ',') : '-', 1, 0, 'R', $fill);
        $pdf->Cell($availableWidth * 0.07, $serviceRowHeight, substr(($s['place_of'] ?? '-'), 0, 10), 1, 0, 'L', $fill);
        $pdf->Cell($availableWidth * 0.07, $serviceRowHeight, substr(($s['branch'] ?? '-'), 0, 10), 1, 0, 'L', $fill);
        $pdf->Cell($availableWidth * 0.07, $serviceRowHeight, substr(($s['assignment'] ?? '-'), 0, 10), 1, 0, 'L', $fill);
        $pdf->Cell($availableWidth * 0.07, $serviceRowHeight, substr(($s['lv_abs'] ?? '-'), 0, 8), 1, 0, 'C', $fill);
        $pdf->Cell($availableWidth * 0.06, $serviceRowHeight, substr(($s['wo_pay'] ?? '-'), 0, 8), 1, 0, 'C', $fill);
        $pdf->Cell($availableWidth * 0.07, $serviceRowHeight, isset($s['separation_date']) && $s['separation_date'] ? date('M d, Y', strtotime($s['separation_date'])) : '-', 1, 0, 'C', $fill);
        $pdf->Cell($availableWidth * 0.07, $serviceRowHeight, substr(($s['separation_cause'] ?? '-'), 0, 10), 1, 1, 'L', $fill);
        
        if (!empty($s['remarks'])) {
            $pdf->SetX($leftMargin);
            $pdf->SetFont('helvetica', 'I', 6);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->Cell(0, 3, 'Remarks: ' . substr($s['remarks'], 0, 100), 0, 1, 'L');
            $pdf->SetFont('helvetica', '', $tableBodyFontSize);
            $pdf->SetTextColor(0, 0, 0);
        }
        $fill = !$fill;
    }
    $pdf->Ln(8);
}

// Clean output buffer before sending PDF
ob_end_clean();

// Output PDF
$filename = 'Employee_Record_' . preg_replace('/[^A-Za-z0-9_]/', '_', $fullName) . '_' . date('Ymd') . '.pdf';
$pdf->Output($filename, 'D');
