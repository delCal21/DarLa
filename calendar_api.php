<?php
require_once 'auth.php';
require_admin_login();
require_once 'db.php';
require_once 'activity_logger.php';

header('Content-Type: application/json');

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

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get_events') {
    $startDate = $_GET['start'] ?? date('Y-m-01');
    $endDate = $_GET['end'] ?? date('Y-m-t');
    
    try {
        $stmt = $pdo->prepare('SELECT * FROM calendar_events WHERE event_date BETWEEN :start AND :end ORDER BY event_date, event_time');
        $stmt->execute([':start' => $startDate, ':end' => $endDate]);
        $events = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'events' => $events]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get_reminders') {
    // Get events that need reminders (1 day before)
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    
    try {
        $stmt = $pdo->prepare('SELECT * FROM calendar_events WHERE event_date = :date AND reminder_sent = FALSE');
        $stmt->execute([':date' => $tomorrow]);
        $reminders = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'reminders' => $reminders]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add_philippine_holidays') {
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
            $holidays[] = ['title' => 'Chinese New Year', 'date' => "$year-01-29", 'type' => 'holiday']; // Approximate, varies
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
        };
        
        // Add holidays for current year and next year
        $addYearHolidays($currentYear);
        $addYearHolidays($nextYear);
        
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
                $skipped++;
            }
        }
        
        // Log activity
        logActivity('event_create', "Added {$inserted} Philippine holidays to calendar");
        
        echo json_encode(['success' => true, 'inserted' => $inserted, 'skipped' => $skipped]);
        exit;
    }
    
    if ($action === 'add_event') {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $eventDate = $_POST['event_date'] ?? '';
        $eventTime = $_POST['event_time'] ?? null;
        $eventType = $_POST['event_type'] ?? 'activity';
        
        if (empty($title) || empty($eventDate)) {
            echo json_encode(['success' => false, 'error' => 'Title and date are required']);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare('INSERT INTO calendar_events (title, description, event_date, event_time, event_type) VALUES (:title, :description, :event_date, :event_time, :event_type)');
            $stmt->execute([
                ':title' => $title,
                ':description' => $description ?: null,
                ':event_date' => $eventDate,
                ':event_time' => $eventTime ?: null,
                ':event_type' => $eventType
            ]);
            
            $eventId = $pdo->lastInsertId();
            
            // Log activity
            logActivity('event_create', "Calendar event created: {$title} ({$eventDate})", 'calendar_events', $eventId);
            
            echo json_encode(['success' => true, 'id' => $eventId]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    
    if ($action === 'update_event') {
        $id = $_POST['id'] ?? 0;
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $eventDate = $_POST['event_date'] ?? '';
        $eventTime = $_POST['event_time'] ?? null;
        $eventType = $_POST['event_type'] ?? 'activity';
        
        if (empty($title) || empty($eventDate)) {
            echo json_encode(['success' => false, 'error' => 'Title and date are required']);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare('UPDATE calendar_events SET title = :title, description = :description, event_date = :event_date, event_time = :event_time, event_type = :event_type WHERE id = :id');
            $stmt->execute([
                ':id' => $id,
                ':title' => $title,
                ':description' => $description ?: null,
                ':event_date' => $eventDate,
                ':event_time' => $eventTime ?: null,
                ':event_type' => $eventType
            ]);
            
            // Log activity
            logActivity('event_update', "Calendar event updated: {$title} ({$eventDate})", 'calendar_events', $id);
            
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    
    if ($action === 'delete_event') {
        $id = $_POST['id'] ?? 0;
        
        try {
            // Get event details before deleting for logging
            $getStmt = $pdo->prepare('SELECT title, event_date FROM calendar_events WHERE id = :id');
            $getStmt->execute([':id' => $id]);
            $event = $getStmt->fetch();
            
            $stmt = $pdo->prepare('DELETE FROM calendar_events WHERE id = :id');
            $stmt->execute([':id' => $id]);
            
            // Log activity
            if ($event) {
                logActivity('event_delete', "Calendar event deleted: {$event['title']} ({$event['event_date']})", 'calendar_events', $id);
            }
            
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    
    if ($action === 'mark_reminder_sent') {
        $id = $_POST['id'] ?? 0;
        
        try {
            $stmt = $pdo->prepare('UPDATE calendar_events SET reminder_sent = TRUE WHERE id = :id');
            $stmt->execute([':id' => $id]);
            
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}

echo json_encode(['success' => false, 'error' => 'Invalid action']);

