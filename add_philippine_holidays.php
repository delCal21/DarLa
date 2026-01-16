<?php
/**
 * Add Philippine Holidays to Calendar
 * This script adds all Philippine holidays to the calendar_events table
 */

require_once 'db.php';

// Function to calculate Easter date
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

// Complete Philippine Holidays List
$holidays = [];

// Function to add holidays for a specific year
$addYearHolidays = function($year) use (&$holidays) {
    // REGULAR HOLIDAYS (Fixed Dates)
    $holidays[] = ['title' => 'New Year\'s Day', 'date' => "$year-01-01", 'type' => 'holiday'];
    $holidays[] = ['title' => 'EDSA People Power Revolution Anniversary', 'date' => "$year-02-25", 'type' => 'holiday'];
    $holidays[] = ['title' => 'Araw ng Kagitingan (Day of Valor)', 'date' => "$year-04-09", 'type' => 'holiday'];
    $holidays[] = ['title' => 'Labor Day', 'date' => "$year-05-01", 'type' => 'holiday'];
    $holidays[] = ['title' => 'Independence Day', 'date' => "$year-06-12", 'type' => 'holiday'];
    $holidays[] = ['title' => 'National Heroes Day', 'date' => getLastMondayOfAugust($year), 'type' => 'holiday'];
    $holidays[] = ['title' => 'Bonifacio Day', 'date' => "$year-11-30", 'type' => 'holiday'];
    $holidays[] = ['title' => 'Rizal Day', 'date' => "$year-12-30", 'type' => 'holiday'];
    $holidays[] = ['title' => 'Christmas Day', 'date' => "$year-12-25", 'type' => 'holiday'];
    
    // SPECIAL NON-WORKING DAYS (Fixed Dates)
    $holidays[] = ['title' => 'All Saints\' Day', 'date' => "$year-11-01", 'type' => 'holiday'];
    $holidays[] = ['title' => 'All Souls\' Day', 'date' => "$year-11-02", 'type' => 'holiday'];
    $holidays[] = ['title' => 'Christmas Eve', 'date' => "$year-12-24", 'type' => 'holiday'];
    $holidays[] = ['title' => 'New Year\'s Eve', 'date' => "$year-12-31", 'type' => 'holiday'];
    
    // MOVABLE HOLIDAYS (Based on Easter)
    $easter = calculateEaster($year);
    $maundyThursday = clone $easter;
    $maundyThursday->modify('-3 days');
    $holidays[] = ['title' => 'Maundy Thursday', 'date' => $maundyThursday->format('Y-m-d'), 'type' => 'holiday'];
    
    $goodFriday = clone $easter;
    $goodFriday->modify('-2 days');
    $holidays[] = ['title' => 'Good Friday', 'date' => $goodFriday->format('Y-m-d'), 'type' => 'holiday'];
    
    $blackSaturday = clone $easter;
    $blackSaturday->modify('-1 days');
    $holidays[] = ['title' => 'Black Saturday', 'date' => $blackSaturday->format('Y-m-d'), 'type' => 'holiday'];
    
    // ADDITIONAL IMPORTANT DATES
    $holidays[] = ['title' => 'Ninoy Aquino Day', 'date' => "$year-08-21", 'type' => 'holiday'];
    $holidays[] = ['title' => 'Constitution Day', 'date' => "$year-02-02", 'type' => 'holiday'];
    $holidays[] = ['title' => 'National Flag Day', 'date' => "$year-05-28", 'type' => 'holiday'];
    $holidays[] = ['title' => 'Philippine-Spanish Friendship Day', 'date' => "$year-06-30", 'type' => 'holiday'];
    $holidays[] = ['title' => 'National Teachers\' Day', 'date' => "$year-10-05", 'type' => 'holiday'];
    $holidays[] = ['title' => 'United Nations Day', 'date' => "$year-10-24", 'type' => 'holiday'];
    
    // REGIONAL HOLIDAYS (Important ones)
    $holidays[] = ['title' => 'Araw ng Maynila (Manila Day)', 'date' => "$year-06-24", 'type' => 'holiday'];
    $holidays[] = ['title' => 'Araw ng Quezon (Quezon Day)', 'date' => "$year-08-19", 'type' => 'holiday'];
    $holidays[] = ['title' => 'Araw ng Davao (Davao Day)', 'date' => "$year-03-16", 'type' => 'holiday'];
    $holidays[] = ['title' => 'Araw ng Cebu (Cebu Day)', 'date' => "$year-04-07", 'type' => 'holiday'];
    
    // CULTURAL & RELIGIOUS HOLIDAYS
    $holidays[] = ['title' => 'Feast of the Black Nazarene', 'date' => "$year-01-09", 'type' => 'holiday'];
    $holidays[] = ['title' => 'Feast of Our Lady of Lourdes', 'date' => "$year-02-11", 'type' => 'holiday'];
    $holidays[] = ['title' => 'Feast of St. Joseph', 'date' => "$year-03-19", 'type' => 'holiday'];
    $holidays[] = ['title' => 'Feast of the Santo Niño', 'date' => "$year-01-15", 'type' => 'holiday'];
    $holidays[] = ['title' => 'Feast of Our Lady of Peñafrancia', 'date' => "$year-09-08", 'type' => 'holiday'];
    $holidays[] = ['title' => 'Feast of the Immaculate Conception', 'date' => "$year-12-08", 'type' => 'holiday'];
    
    // SEASONAL & CULTURAL EVENTS
    $holidays[] = ['title' => 'Sinulog Festival (Cebu)', 'date' => "$year-01-15", 'type' => 'holiday'];
    $holidays[] = ['title' => 'Ati-Atihan Festival (Aklan)', 'date' => "$year-01-15", 'type' => 'holiday'];
    $holidays[] = ['title' => 'Dinagyang Festival (Iloilo)', 'date' => "$year-01-25", 'type' => 'holiday'];
    $holidays[] = ['title' => 'Panagbenga Festival (Baguio)', 'date' => "$year-02-01", 'type' => 'holiday'];
    $holidays[] = ['title' => 'Moriones Festival (Marinduque)', 'date' => $goodFriday->format('Y-m-d'), 'type' => 'holiday'];
    $holidays[] = ['title' => 'Kadayawan Festival (Davao)', 'date' => "$year-08-15", 'type' => 'holiday'];
    $holidays[] = ['title' => 'MassKara Festival (Bacolod)', 'date' => "$year-10-19", 'type' => 'holiday'];
    $holidays[] = ['title' => 'Higantes Festival (Angono)', 'date' => "$year-11-23", 'type' => 'holiday'];
    
    // CHINESE NEW YEAR (varies by year, using approximate dates)
    // 2025: January 29, 2026: February 17, 2027: February 6
    if ($year == 2025) {
        $holidays[] = ['title' => 'Chinese New Year', 'date' => "$year-01-29", 'type' => 'holiday'];
    } elseif ($year == 2026) {
        $holidays[] = ['title' => 'Chinese New Year', 'date' => "$year-02-17", 'type' => 'holiday'];
    } elseif ($year == 2027) {
        $holidays[] = ['title' => 'Chinese New Year', 'date' => "$year-02-06", 'type' => 'holiday'];
    } else {
        // Default to late January for other years
        $holidays[] = ['title' => 'Chinese New Year', 'date' => "$year-01-29", 'type' => 'holiday'];
    }
};

// Add holidays for current year and next year
$addYearHolidays($currentYear);
$addYearHolidays($nextYear);

// Insert holidays (skip duplicates)
$inserted = 0;
$skipped = 0;

echo "Adding Philippine Holidays to Calendar...\n";
echo "==========================================\n\n";

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
        echo "✓ Added: {$holiday['title']} - {$holiday['date']}\n";
    } catch (PDOException $e) {
        $skipped++;
        echo "✗ Error adding {$holiday['title']}: " . $e->getMessage() . "\n";
    }
}

echo "\n==========================================\n";
echo "✅ COMPLETED!\n";
echo "Inserted: $inserted holidays\n";
echo "Skipped (duplicates): $skipped holidays\n";
echo "\nThe calendar now contains Philippine holidays for $currentYear and $nextYear.\n";
echo "You can view them in the dashboard calendar.\n";
