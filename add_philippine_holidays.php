<?php
/**
 * Script to add all Philippine holidays to the calendar
 * Run this once to populate the calendar with all Philippine holidays
 */

require_once 'auth.php';
require_admin_login();
require_once 'db.php';

// Check if calendar_events table exists, create if not
try {
    $pdo->query("SELECT 1 FROM calendar_events LIMIT 1");
} catch (PDOException $e) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS calendar_events (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT DEFAULT NULL,
            event_date DATE NOT NULL,
            event_time TIME DEFAULT NULL,
            event_type ENUM('activity', 'holiday', 'season', 'reminder') DEFAULT 'activity',
            reminder_sent BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_event_date (event_date),
            INDEX idx_event_type (event_type),
            INDEX idx_reminder (reminder_sent, event_date)
        )
    ");
}

// Function to calculate Easter date (for movable holidays)
function calculateEaster($year) {
    $a = $year % 19;
    $b = floor($year / 100);
    $c = $year % 100;
    $d = floor($b / 4);
    $e = $b % 4;
    $f = floor(($b + 8) / 25);
    $g = floor(($b - $f + 1) / 3);
    $h = (19 * $a + $b - $d - $g + 15) % 30;
    $i = floor($c / 4);
    $k = $c % 4;
    $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
    $m = floor(($a + 11 * $h + 22 * $l) / 451);
    $month = floor(($h + $l - 7 * $m + 114) / 31);
    $day = (($h + $l - 7 * $m + 114) % 31) + 1;
    return new DateTime("$year-$month-$day");
}

// Function to get last Monday of August
function getLastMondayOfAugust($year) {
    $lastDay = new DateTime("$year-08-31");
    $dayOfWeek = (int)$lastDay->format('w');
    $daysToSubtract = ($dayOfWeek === 1) ? 0 : (($dayOfWeek === 0) ? 6 : $dayOfWeek - 1);
    $lastDay->modify("-$daysToSubtract days");
    return $lastDay->format('Y-m-d');
}

$currentYear = date('Y');
$nextYear = $currentYear + 1;

// Philippine Holidays (Regular and Special Non-Working Days)
$holidays = [];

// Fixed Holidays
$holidays[] = ['title' => 'New Year\'s Day', 'date' => "$currentYear-01-01", 'type' => 'holiday'];
$holidays[] = ['title' => 'EDSA People Power Revolution Anniversary', 'date' => "$currentYear-02-25", 'type' => 'holiday'];
$holidays[] = ['title' => 'Araw ng Kagitingan (Day of Valor)', 'date' => "$currentYear-04-09", 'type' => 'holiday'];
$holidays[] = ['title' => 'Labor Day', 'date' => "$currentYear-05-01", 'type' => 'holiday'];
$holidays[] = ['title' => 'Independence Day', 'date' => "$currentYear-06-12", 'type' => 'holiday'];
$holidays[] = ['title' => 'National Heroes Day', 'date' => getLastMondayOfAugust($currentYear), 'type' => 'holiday'];
$holidays[] = ['title' => 'Bonifacio Day', 'date' => "$currentYear-11-30", 'type' => 'holiday'];
$holidays[] = ['title' => 'Rizal Day', 'date' => "$currentYear-12-30", 'type' => 'holiday'];
$holidays[] = ['title' => 'Christmas Day', 'date' => "$currentYear-12-25", 'type' => 'holiday'];

// Movable Holidays (based on Easter)
$easter = calculateEaster($currentYear);
$holidays[] = ['title' => 'Maundy Thursday', 'date' => $easter->modify('-3 days')->format('Y-m-d'), 'type' => 'holiday'];
$easter = calculateEaster($currentYear);
$holidays[] = ['title' => 'Good Friday', 'date' => $easter->modify('-2 days')->format('Y-m-d'), 'type' => 'holiday'];
$easter = calculateEaster($currentYear);
$holidays[] = ['title' => 'Black Saturday', 'date' => $easter->modify('-1 days')->format('Y-m-d'), 'type' => 'holiday'];
$easter = calculateEaster($currentYear);
$holidays[] = ['title' => 'Easter Sunday', 'date' => $easter->format('Y-m-d'), 'type' => 'holiday'];

// Special Non-Working Days
$holidays[] = ['title' => 'Chinese New Year', 'date' => "$currentYear-01-29", 'type' => 'holiday']; // Approximate, varies yearly
$holidays[] = ['title' => 'People Power Revolution Anniversary', 'date' => "$currentYear-02-25", 'type' => 'holiday'];
$holidays[] = ['title' => 'All Saints\' Day', 'date' => "$currentYear-11-01", 'type' => 'holiday'];
$holidays[] = ['title' => 'All Souls\' Day', 'date' => "$currentYear-11-02", 'type' => 'holiday'];
$holidays[] = ['title' => 'Christmas Eve', 'date' => "$currentYear-12-24", 'type' => 'holiday'];
$holidays[] = ['title' => 'New Year\'s Eve', 'date' => "$currentYear-12-31", 'type' => 'holiday'];

// Add next year's fixed holidays too
$holidays[] = ['title' => 'New Year\'s Day', 'date' => "$nextYear-01-01", 'type' => 'holiday'];
$holidays[] = ['title' => 'EDSA People Power Revolution Anniversary', 'date' => "$nextYear-02-25", 'type' => 'holiday'];
$holidays[] = ['title' => 'Araw ng Kagitingan (Day of Valor)', 'date' => "$nextYear-04-09", 'type' => 'holiday'];
$holidays[] = ['title' => 'Labor Day', 'date' => "$nextYear-05-01", 'type' => 'holiday'];
$holidays[] = ['title' => 'Independence Day', 'date' => "$nextYear-06-12", 'type' => 'holiday'];
$holidays[] = ['title' => 'National Heroes Day', 'date' => getLastMondayOfAugust($nextYear), 'type' => 'holiday'];
$holidays[] = ['title' => 'Bonifacio Day', 'date' => "$nextYear-11-30", 'type' => 'holiday'];
$holidays[] = ['title' => 'Rizal Day', 'date' => "$nextYear-12-30", 'type' => 'holiday'];
$holidays[] = ['title' => 'Christmas Day', 'date' => "$nextYear-12-25", 'type' => 'holiday'];

// Next year's movable holidays
$easterNext = calculateEaster($nextYear);
$holidays[] = ['title' => 'Maundy Thursday', 'date' => $easterNext->modify('-3 days')->format('Y-m-d'), 'type' => 'holiday'];
$easterNext = calculateEaster($nextYear);
$holidays[] = ['title' => 'Good Friday', 'date' => $easterNext->modify('-2 days')->format('Y-m-d'), 'type' => 'holiday'];
$easterNext = calculateEaster($nextYear);
$holidays[] = ['title' => 'Black Saturday', 'date' => $easterNext->modify('-1 days')->format('Y-m-d'), 'type' => 'holiday'];
$easterNext = calculateEaster($nextYear);
$holidays[] = ['title' => 'Easter Sunday', 'date' => $easterNext->format('Y-m-d'), 'type' => 'holiday'];

$holidays[] = ['title' => 'All Saints\' Day', 'date' => "$nextYear-11-01", 'type' => 'holiday'];
$holidays[] = ['title' => 'All Souls\' Day', 'date' => "$nextYear-11-02", 'type' => 'holiday'];
$holidays[] = ['title' => 'Christmas Eve', 'date' => "$nextYear-12-24", 'type' => 'holiday'];
$holidays[] = ['title' => 'New Year\'s Eve', 'date' => "$nextYear-12-31", 'type' => 'holiday'];

// Insert holidays (skip duplicates)
$inserted = 0;
$skipped = 0;

foreach ($holidays as $holiday) {
    try {
        // Check if holiday already exists
        $checkStmt = $pdo->prepare('SELECT id FROM calendar_events WHERE title = :title AND event_date = :date AND event_type = :type');
        $checkStmt->execute([
            ':title' => $holiday['title'],
            ':date' => $holiday['date'],
            ':type' => $holiday['type']
        ]);
        
        if ($checkStmt->fetch()) {
            $skipped++;
            continue;
        }
        
        // Insert holiday
        $stmt = $pdo->prepare('INSERT INTO calendar_events (title, event_date, event_type) VALUES (:title, :date, :type)');
        $stmt->execute([
            ':title' => $holiday['title'],
            ':date' => $holiday['date'],
            ':type' => $holiday['type']
        ]);
        $inserted++;
    } catch (PDOException $e) {
        // Skip on error
        $skipped++;
    }
}

echo "Philippine holidays added successfully!\n";
echo "Inserted: $inserted holidays\n";
echo "Skipped (duplicates): $skipped holidays\n";

