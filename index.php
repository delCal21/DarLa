<?php
require_once 'auth.php';
require_admin_login();
require_once 'db.php';
require_once 'header.php';

// Simple summary metrics for the dashboard
$totalEmployees = (int)$pdo->query('SELECT COUNT(*) FROM employees')->fetchColumn();

// Get employment status distribution for bar chart
// Normalize by trimming whitespace and treating NULL/empty as "Not set"
$statusStmt = $pdo->query(
    'SELECT 
        COALESCE(NULLIF(TRIM(employment_status), ""), "Not set") AS status_label,
        COUNT(*) AS total
     FROM employees
     GROUP BY status_label
     ORDER BY total DESC, status_label'
);
$statusSummary = $statusStmt->fetchAll();

// Prepare data for chart
$employmentStatuses = [
    'PERMANENT', 'COS', 'SPLIT', 'CTI', 'PA', 'RESIGNED', 'RETIRED', 'OTHERS'
];

// Color mapping for each status
$statusColorMap = [
    'PERMANENT' => ['bg' => 'rgba(25, 135, 84, 0.8)', 'border' => 'rgba(25, 135, 84, 1)'],
    'COS' => ['bg' => 'rgba(13, 202, 240, 0.8)', 'border' => 'rgba(13, 202, 240, 1)'],
    'SPLIT' => ['bg' => 'rgba(255, 193, 7, 0.8)', 'border' => 'rgba(255, 193, 7, 1)'],
    'CTI' => ['bg' => 'rgba(255, 152, 0, 0.8)', 'border' => 'rgba(255, 152, 0, 1)'],
    'PA' => ['bg' => 'rgba(108, 117, 125, 0.8)', 'border' => 'rgba(108, 117, 125, 1)'],
    'RESIGNED' => ['bg' => 'rgba(220, 53, 69, 0.5)', 'border' => 'rgba(220, 53, 69, 0.7)'],
    'RETIRED' => ['bg' => 'rgba(108, 117, 125, 0.6)', 'border' => 'rgba(108, 117, 125, 0.8)'],
    'OTHERS' => ['bg' => 'rgba(111, 66, 193, 0.8)', 'border' => 'rgba(111, 66, 193, 1)'],
    'Not set' => ['bg' => 'rgba(200, 200, 200, 0.6)', 'border' => 'rgba(200, 200, 200, 0.8)'],
];

// Create a map of status to count (normalize to uppercase for matching)
$statusMap = [];
foreach ($statusSummary as $row) {
    $statusLabel = trim($row['status_label']);
    // Normalize to uppercase for consistent matching
    $statusKey = strtoupper($statusLabel);
    $statusMap[$statusKey] = (int)$row['total'];
    // Also keep original case for "Not set"
    if ($statusLabel === 'Not set' || $statusLabel === 'Not Set') {
        $statusMap['Not set'] = (int)$row['total'];
    }
}

// Build chart data - show all statuses that have employees
$chartLabels = [];
$chartData = [];
$chartBackgroundColors = [];
$chartBorderColors = [];

foreach ($employmentStatuses as $status) {
    // Match using uppercase key
    $statusKey = strtoupper($status);
    $count = isset($statusMap[$statusKey]) ? $statusMap[$statusKey] : 0;
    
    if ($count > 0) {
        $chartLabels[] = $status;
        $chartData[] = $count;
        $chartBackgroundColors[] = $statusColorMap[$status]['bg'] ?? 'rgba(108, 117, 125, 0.8)';
        $chartBorderColors[] = $statusColorMap[$status]['border'] ?? 'rgba(108, 117, 125, 1)';
    }
}

// Add "Not set" if exists
if (isset($statusMap['Not set']) && $statusMap['Not set'] > 0) {
    $chartLabels[] = 'Not set';
    $chartData[] = $statusMap['Not set'];
    $chartBackgroundColors[] = $statusColorMap['Not set']['bg'];
    $chartBorderColors[] = $statusColorMap['Not set']['border'];
}

// Get office/department distribution for line chart
$officeStmt = $pdo->query(
    'SELECT 
        COALESCE(NULLIF(TRIM(office), ""), "Not Set") AS office_label,
        COUNT(*) AS total
     FROM employees
     GROUP BY office_label
     ORDER BY office_label ASC'
);
$officeSummary = $officeStmt->fetchAll();

// Prepare data for office/department line chart
$defaultOffices = ['LTS', 'LEGAL', 'DARAB', 'PBDD', 'OPARPO', 'STOD'];
$officeMap = [];
foreach ($officeSummary as $row) {
    $officeLabel = trim($row['office_label']);
    $officeMap[$officeLabel] = (int)$row['total'];
}

// Build chart data - include all predefined offices
$officeChartLabels = [];
$officeChartData = [];

// Always include all predefined offices (even if count is 0)
foreach ($defaultOffices as $office) {
    $officeChartLabels[] = $office;
    // Check officeMap with exact match first, then try case-insensitive
    $count = 0;
    if (isset($officeMap[$office])) {
        $count = (int)$officeMap[$office];
    } else {
        // Try case-insensitive match
        foreach ($officeMap as $key => $value) {
            if (strcasecmp(trim($key), trim($office)) === 0) {
                $count = (int)$value;
                break;
            }
        }
    }
    $officeChartData[] = $count;
}

// Add "Not Set" if exists and has employees
if (isset($officeMap['Not Set']) && $officeMap['Not Set'] > 0) {
    $officeChartLabels[] = 'Not Set';
    $officeChartData[] = (int)$officeMap['Not Set'];
}

// Add any other offices found in database (not in predefined list)
foreach ($officeSummary as $row) {
    $office = $row['office_label'];
    if (!in_array($office, $defaultOffices) && $office !== 'Not Set') {
        $officeChartLabels[] = $office;
        $officeChartData[] = (int)$row['total'];
    }
}

// Ensure we have data (at least empty arrays)
if (empty($officeChartLabels)) {
    $officeChartLabels = $defaultOffices;
    $officeChartData = array_fill(0, count($defaultOffices), 0);
}
?>

<!-- Breadcrumb -->
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item active">Dashboard</li>
    <li class="breadcrumb-item">Overview</li>
</ol>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Admin Dashboard</h1>
        <p class="text-muted mb-0">
            High-level overview of employee records in <strong>DARLU HRIS</strong>.
        </p>
    </div>
</div>

<!-- Reminder Alert -->
<div id="reminderAlert" class="alert alert-info alert-dismissible fade d-none mb-3" role="alert">
    <i class="fas fa-bell me-2"></i>
    <strong>Reminder:</strong> <span id="reminderText"></span>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="me-3 text-primary">
                    <i class="fas fa-users fa-2x"></i>
                </div>
                <div>
                    <div class="text-muted text-uppercase small mb-1">Total Employees</div>
                    <div class="h3 mb-0">
                        <?= number_format($totalEmployees) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-2">
                <div>
                    <h6 class="card-title mb-0 fw-bold" style="font-size: 0.95rem;">
                        <i class="fas fa-chart-bar me-2 text-primary"></i>Employment Status
                    </h6>
                </div>
                <div class="text-end">
                    <span class="small text-muted">Total: </span>
                    <span class="text-primary fw-bold" style="font-size: 0.95rem;"><?= number_format($totalEmployees) ?></span>
                </div>
            </div>
            <div class="card-body p-2">
                <?php if (empty($statusSummary)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-chart-bar fa-2x text-muted mb-2 d-block"></i>
                        <p class="mb-0 text-muted small">No employees recorded yet.</p>
                    </div>
                <?php else: ?>
                    <div class="chart-container" style="position: relative; height: 150px; margin-bottom: 8px;">
                        <canvas id="employmentStatusChart"></canvas>
                    </div>
                    <div class="mt-2 pt-2 border-top">
                        <div class="row g-1">
                            <?php 
                            $totalCount = array_sum(array_column($statusSummary, 'total'));
                            foreach ($statusSummary as $row): 
                                $percentage = $totalCount > 0 ? round(($row['total'] / $totalCount) * 100, 1) : 0;
                                $status = $row['status_label'];
                                $colorClass = '';
                                if ($status === 'Permanent') $colorClass = 'success';
                                elseif ($status === 'Temporary') $colorClass = 'info';
                                elseif ($status === 'Contractual') $colorClass = 'warning';
                                elseif (in_array($status, ['Retired', 'Resigned', 'Suspended'])) $colorClass = 'danger';
                                else $colorClass = 'secondary';
                            ?>
                                <div class="col-6 col-md-4 col-lg-3">
                                    <div class="d-flex align-items-center justify-content-between p-1 px-2 bg-light rounded">
                                        <span class="small text-dark" style="font-size: 0.75rem;"><?= htmlspecialchars($status) ?></span>
                                        <strong class="text-<?= $colorClass ?>" style="font-size: 0.85rem;"><?= (int)$row['total'] ?></strong>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Office / Department Line Graph -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-2">
                <div>
                    <h6 class="card-title mb-0 fw-bold" style="font-size: 0.95rem;">
                        <i class="fas fa-chart-line me-2 text-primary"></i>Employees by Office / Department
                    </h6>
                </div>
                <div class="text-end">
                    <span class="small text-muted">Total: </span>
                    <span class="text-primary fw-bold" style="font-size: 0.95rem;"><?= number_format($totalEmployees) ?></span>
                </div>
            </div>
            <div class="card-body p-3">
                <!-- Debug Info (remove after testing) -->
                <?php if (false): // Set to true to see debug info ?>
                <div class="alert alert-info small mb-2">
                    <strong>Debug Info:</strong><br>
                    Office Summary: <?= print_r($officeSummary, true) ?><br>
                    Office Map: <?= print_r($officeMap, true) ?><br>
                    Chart Labels: <?= print_r($officeChartLabels, true) ?><br>
                    Chart Data: <?= print_r($officeChartData, true) ?>
                </div>
                <?php endif; ?>
                
                <div class="chart-container" style="position: relative; height: 300px; margin-bottom: 8px;">
                    <canvas id="officeDepartmentChart"></canvas>
                </div>
                <div class="mt-3 pt-3 border-top">
                    <div class="row g-2">
                        <?php 
                        // Show all predefined offices plus any others
                        $allOffices = $defaultOffices;
                        // Add any other offices from database
                        foreach ($officeSummary as $row) {
                            $officeLabel = trim($row['office_label']);
                            if (!in_array($officeLabel, $allOffices) && $officeLabel !== 'Not Set') {
                                $allOffices[] = $officeLabel;
                            }
                        }
                        // Add "Not Set" at the end if it exists
                        if (isset($officeMap['Not Set']) && $officeMap['Not Set'] > 0) {
                            $allOffices[] = 'Not Set';
                        }
                        
                        foreach ($allOffices as $office): 
                            $count = isset($officeMap[$office]) ? (int)$officeMap[$office] : 0;
                            $percentage = $totalEmployees > 0 ? round(($count / $totalEmployees) * 100) : 0;
                        ?>
                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="d-flex align-items-center justify-content-between p-2 bg-light rounded">
                                    <span class="small text-dark" style="font-size: 0.75rem;">
                                        <i class="fas fa-building me-1 text-muted"></i>
                                        <?= htmlspecialchars($office) ?>
                                    </span>
                                    <strong class="text-primary" style="font-size: 0.85rem;">
                                        <?= $count ?> <span class="text-muted" style="font-size: 0.7rem;">(<?= $percentage ?>%)</span>
                                    </strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Calendar Section -->
<div class="row g-3">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-calendar-alt me-2 text-primary"></i>
                    Calendar & Events
                </h5>
                <div class="btn-group">
                    <button class="btn btn-sm btn-success" id="addHolidaysBtn" title="Add All Philippine Holidays">
                        <i class="fas fa-flag me-1"></i> Add Holidays
                    </button>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addEventModal">
                        <i class="fas fa-plus me-1"></i> Add Event
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-8">
                        <div id="calendar-widget"></div>
                    </div>
                    <div class="col-lg-4">
                        <div id="upcoming-events" class="mt-3 mt-lg-0">
                            <h6 class="small text-muted mb-3 fw-bold">
                                <i class="fas fa-list me-1"></i>Upcoming Events
                            </h6>
                            <div id="events-list"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Event Modal -->
<div class="modal fade" id="addEventModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="eventForm">
                <div class="modal-body">
                    <input type="hidden" id="event_id" name="id" value="">
                    <div class="mb-3">
                        <label for="event_title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="event_title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="event_description" class="form-label">Description</label>
                        <textarea class="form-control" id="event_description" name="description" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="event_date" class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="event_date" name="event_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="event_time" class="form-label">Time</label>
                            <input type="time" class="form-control" id="event_time" name="event_time">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="event_type" class="form-label">Event Type</label>
                        <select class="form-select" id="event_type" name="event_type">
                            <option value="activity">Activity</option>
                            <option value="holiday">Holiday</option>
                            <option value="season">Season</option>
                            <option value="reminder">Reminder</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Event</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Calendar functionality
(function() {
    const today = new Date();
    let currentMonth = today.getMonth();
    let currentYear = today.getFullYear();
    
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                       'July', 'August', 'September', 'October', 'November', 'December'];
    const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    
    let events = [];
    
    function loadEvents() {
        const startDate = new Date(currentYear, currentMonth, 1).toISOString().split('T')[0];
        const endDate = new Date(currentYear, currentMonth + 1, 0).toISOString().split('T')[0];
        
        fetch(`calendar_api.php?action=get_events&start=${startDate}&end=${endDate}`)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    events = data.events;
                    renderCalendar();
                    renderUpcomingEvents();
                }
            });
    }
    
    function renderCalendar() {
        const calendarEl = document.getElementById('calendar-widget');
        const firstDay = new Date(currentYear, currentMonth, 1).getDay();
        const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
        
        let html = `<div class="calendar-header mb-2 d-flex justify-content-between align-items-center">
            <button class="btn btn-sm btn-outline-secondary" onclick="changeMonth(-1)"><i class="fas fa-chevron-left"></i></button>
            <strong class="small">${monthNames[currentMonth]} ${currentYear}</strong>
            <button class="btn btn-sm btn-outline-secondary" onclick="changeMonth(1)"><i class="fas fa-chevron-right"></i></button>
        </div>`;
        
        html += '<div class="calendar-grid">';
        html += '<div class="calendar-weekdays">';
        dayNames.forEach(day => {
            html += `<div class="calendar-weekday">${day}</div>`;
        });
        html += '</div>';
        
        html += '<div class="calendar-days">';
        for (let i = 0; i < firstDay; i++) {
            html += '<div class="calendar-day empty"></div>';
        }
        
        for (let day = 1; day <= daysInMonth; day++) {
            const dateStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const dayEvents = events.filter(e => e.event_date === dateStr);
            const isToday = currentYear === today.getFullYear() && 
                           currentMonth === today.getMonth() && 
                           day === today.getDate();
            
            html += `<div class="calendar-day ${isToday ? 'today' : ''}" data-date="${dateStr}">`;
            html += `<div class="day-number">${day}</div>`;
            if (dayEvents.length > 0) {
                html += `<div class="day-events">`;
                dayEvents.slice(0, 2).forEach(event => {
                    const typeClass = event.event_type === 'holiday' ? 'holiday' : 
                                     event.event_type === 'season' ? 'season' : 'activity';
                    html += `<div class="event-dot ${typeClass}" title="${event.title}"></div>`;
                });
                if (dayEvents.length > 2) {
                    html += `<div class="event-dot more" title="${dayEvents.length - 2} more">+${dayEvents.length - 2}</div>`;
                }
                html += `</div>`;
            }
            html += `</div>`;
        }
        
        html += '</div></div>';
        calendarEl.innerHTML = html;
        
        // Add click handlers
        document.querySelectorAll('.calendar-day:not(.empty)').forEach(day => {
            day.addEventListener('click', function() {
                const date = this.dataset.date;
                document.getElementById('event_date').value = date;
                const modal = new bootstrap.Modal(document.getElementById('addEventModal'));
                modal.show();
            });
        });
    }
    
    function renderUpcomingEvents() {
        const todayStr = new Date().toISOString().split('T')[0];
        const upcoming = events
            .filter(e => e.event_date >= todayStr)
            .sort((a, b) => a.event_date.localeCompare(b.event_date))
            .slice(0, 5);
        
        const eventsList = document.getElementById('events-list');
        if (upcoming.length === 0) {
            eventsList.innerHTML = '<p class="text-muted small mb-0">No upcoming events</p>';
            return;
        }
        
        let html = '';
        upcoming.forEach(event => {
            const date = new Date(event.event_date);
            const typeBadge = event.event_type === 'holiday' ? 'danger' : 
                             event.event_type === 'season' ? 'info' : 'primary';
            html += `<div class="d-flex justify-content-between align-items-start mb-2 p-2 bg-light rounded">
                <div class="flex-grow-1">
                    <div class="small fw-bold">${event.title}</div>
                    <div class="small text-muted">${date.toLocaleDateString()}</div>
                </div>
                <span class="badge bg-${typeBadge}">${event.event_type}</span>
            </div>`;
        });
        eventsList.innerHTML = html;
    }
    
    window.changeMonth = function(delta) {
        currentMonth += delta;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        } else if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        loadEvents();
    };
    
    // Event form handling
    document.getElementById('eventForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const eventId = formData.get('id');
        const action = eventId ? 'update_event' : 'add_event';
        formData.append('action', action);
        
        fetch('calendar_api.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('addEventModal')).hide();
                this.reset();
                document.getElementById('event_id').value = '';
                loadEvents();
            } else {
                alert('Error: ' + (data.error || 'Unknown error'));
            }
        });
    });
    
    // Check for reminders
    function checkReminders() {
        fetch('calendar_api.php?action=get_reminders')
            .then(r => r.json())
            .then(data => {
                if (data.success && data.reminders.length > 0) {
                    const reminder = data.reminders[0];
                    const date = new Date(reminder.event_date);
                    document.getElementById('reminderText').textContent = 
                        `${reminder.title} is tomorrow (${date.toLocaleDateString()})`;
                    document.getElementById('reminderAlert').classList.remove('d-none');
                    
                    // Mark as sent
                    const formData = new FormData();
                    formData.append('action', 'mark_reminder_sent');
                    formData.append('id', reminder.id);
                    fetch('calendar_api.php', { method: 'POST', body: formData });
                }
            });
    }
    
    // Add Philippine Holidays button
    document.getElementById('addHolidaysBtn')?.addEventListener('click', function() {
        if (!confirm('Add all Philippine holidays to the calendar? This will add holidays for the current and next year.')) {
            return;
        }
        
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Adding...';
        
        const formData = new FormData();
        formData.append('action', 'add_philippine_holidays');
        
        fetch('calendar_api.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert(`Successfully added ${data.inserted} Philippine holidays! ${data.skipped > 0 ? `(${data.skipped} duplicates skipped)` : ''}`);
                loadEvents();
            } else {
                alert('Error: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            alert('Error adding holidays: ' + error.message);
        })
        .finally(() => {
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-flag me-1"></i> Add Holidays';
        });
    });
    
    // Initialize
    loadEvents();
    checkReminders();
    setInterval(checkReminders, 60000); // Check every minute
})();
</script>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<!-- Employment Status Bar Chart -->
<script>
(function() {
    const ctx = document.getElementById('employmentStatusChart');
    if (!ctx) return;
    
    const chartData = {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [{
            label: 'Number of Employees',
            data: <?= json_encode($chartData) ?>,
            backgroundColor: <?= json_encode($chartBackgroundColors) ?>,
            borderColor: <?= json_encode($chartBorderColors) ?>,
            borderWidth: 2.5,
            borderRadius: 8,
            borderSkipped: false,
            barThickness: 'flex',
            maxBarThickness: 60,
        }]
    };
    
    const totalEmployees = <?= $totalEmployees ?>;
    
    new Chart(ctx, {
        type: 'bar',
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    top: 5,
                    bottom: 5,
                    left: 5,
                    right: 5
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.85)',
                    padding: 14,
                    titleFont: {
                        size: 14,
                        weight: 'bold',
                        family: "'Segoe UI', system-ui, sans-serif"
                    },
                    bodyFont: {
                        size: 13,
                        family: "'Segoe UI', system-ui, sans-serif"
                    },
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: true,
                    callbacks: {
                        title: function(context) {
                            return context[0].label;
                        },
                        label: function(context) {
                            const value = context.parsed.y;
                            const percentage = totalEmployees > 0 ? Math.round((value / totalEmployees) * 100) : 0;
                            return [
                                'Employees: ' + value.toLocaleString(),
                                'Percentage: ' + percentage + '%'
                            ];
                        },
                        labelColor: function(context) {
                            return {
                                borderColor: context.dataset.borderColor[context.dataIndex],
                                backgroundColor: context.dataset.backgroundColor[context.dataIndex]
                            };
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        precision: 0,
                        font: {
                            size: 10,
                            family: "'Segoe UI', system-ui, sans-serif"
                        },
                        color: '#6c757d',
                        padding: 6,
                        callback: function(value) {
                            return value;
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.06)',
                        drawBorder: false,
                        lineWidth: 1
                    },
                    border: {
                        display: false
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 8,
                            family: "'Segoe UI', system-ui, sans-serif",
                            weight: '500'
                        },
                        color: '#495057',
                        maxRotation: 45,
                        minRotation: 35,
                        padding: 5
                    },
                    grid: {
                        display: false
                    },
                    border: {
                        display: true,
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeInOutQuart',
                onComplete: function() {
                    const chart = this.chart;
                    const ctx = chart.ctx;
                    ctx.save();
                    const meta = chart.getDatasetMeta(0);
                    meta.data.forEach((bar, index) => {
                        const value = chart.data.datasets[0].data[index];
                        const percentage = totalEmployees > 0 ? Math.round((value / totalEmployees) * 100) : 0;
                        
                        // Draw value label on top of bar (only if bar is tall enough)
                        if (bar.height > 15) {
                            ctx.fillStyle = '#212529';
                            ctx.font = 'bold 9px "Segoe UI", system-ui, sans-serif';
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'bottom';
                            ctx.fillText(value, bar.x, bar.y - 3);
                        }
                    });
                    ctx.restore();
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
})();
</script>

<!-- Office / Department Line Chart -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('officeDepartmentChart');
    if (!ctx) {
        console.error('Canvas element not found');
        return;
    }
    
    const chartLabels = <?= json_encode($officeChartLabels) ?>;
    const chartData = <?= json_encode($officeChartData) ?>;
    
    // Debug: Log the data to console
    console.log('Office Chart Labels:', chartLabels);
    console.log('Office Chart Data:', chartData);
    
    if (!chartLabels || !chartData || chartLabels.length === 0) {
        console.error('Chart data is empty');
        return;
    }
    
    if (chartLabels.length !== chartData.length) {
        console.error('Labels and data length mismatch:', chartLabels.length, 'vs', chartData.length);
        return;
    }
    
    const chartConfig = {
        labels: chartLabels,
        datasets: [{
            label: 'Number of Employees',
            data: chartData,
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            borderColor: 'rgba(13, 110, 253, 1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: 'rgba(13, 110, 253, 1)',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointRadius: 6,
            pointHoverRadius: 8,
            pointHoverBackgroundColor: 'rgba(13, 110, 253, 1)',
            pointHoverBorderColor: '#ffffff',
            pointHoverBorderWidth: 3,
        }]
    };
    
    const totalEmployees = <?= $totalEmployees ?>;
    
    new Chart(ctx, {
        type: 'line',
        data: chartConfig,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    top: 10,
                    bottom: 10,
                    left: 10,
                    right: 10
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.85)',
                    padding: 14,
                    titleFont: {
                        size: 14,
                        weight: 'bold',
                        family: "'Segoe UI', system-ui, sans-serif"
                    },
                    bodyFont: {
                        size: 13,
                        family: "'Segoe UI', system-ui, sans-serif"
                    },
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: true,
                    callbacks: {
                        title: function(context) {
                            return context[0].label;
                        },
                        label: function(context) {
                            const value = context.parsed.y;
                            const percentage = totalEmployees > 0 ? Math.round((value / totalEmployees) * 100) : 0;
                            return [
                                'Employees: ' + value.toLocaleString(),
                                'Percentage: ' + percentage + '%'
                            ];
                        },
                        labelColor: function(context) {
                            return {
                                borderColor: 'rgba(13, 110, 253, 1)',
                                backgroundColor: 'rgba(13, 110, 253, 1)'
                            };
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        precision: 0,
                        font: {
                            size: 11,
                            family: "'Segoe UI', system-ui, sans-serif"
                        },
                        color: '#6c757d',
                        padding: 8,
                        callback: function(value) {
                            return value;
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.06)',
                        drawBorder: false,
                        lineWidth: 1
                    },
                    border: {
                        display: false
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 10,
                            family: "'Segoe UI', system-ui, sans-serif",
                            weight: '500'
                        },
                        color: '#495057',
                        maxRotation: 45,
                        minRotation: 0,
                        padding: 8
                    },
                    grid: {
                        display: false
                    },
                    border: {
                        display: true,
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                }
            },
            animation: {
                duration: 1500,
                easing: 'easeInOutQuart'
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
});
</script>

<style>
.calendar-grid {
    font-size: 0.9rem;
}
.calendar-header {
    padding: 0.5rem 0;
}
.calendar-weekdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 4px;
    margin-bottom: 6px;
}
.calendar-weekday {
    text-align: center;
    font-weight: 600;
    font-size: 0.75rem;
    color: #6c757d;
    padding: 6px 4px;
    background: var(--app-green-light);
    border-radius: 4px;
}
.calendar-days {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 4px;
}
.calendar-day {
    min-height: 80px;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 6px;
    cursor: pointer;
    position: relative;
    background: white;
    transition: all 0.2s;
}
.calendar-day:hover {
    background: #f8f9fa;
    border-color: var(--app-green);
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.calendar-day.today {
    background: var(--app-green-light);
    border-color: var(--app-green);
    border-width: 2px;
    font-weight: bold;
}
.calendar-day.empty {
    background: transparent;
    border: none;
    cursor: default;
}
.day-number {
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 4px;
}
.day-events {
    display: flex;
    flex-wrap: wrap;
    gap: 3px;
    margin-top: 4px;
}
.event-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}
.event-dot.activity {
    background: var(--app-green);
}
.event-dot.holiday {
    background: #dc3545;
}
.event-dot.season {
    background: #0dcaf0;
}
.event-dot.more {
    background: #6c757d;
    font-size: 0.65rem;
    width: auto;
    height: auto;
    padding: 1px 4px;
    border-radius: 3px;
    color: white;
}
#events-list {
    max-height: 400px;
    overflow-y: auto;
}

/* Chart Styling */
.chart-container {
    background: linear-gradient(to bottom, #fafafa 0%, #ffffff 100%);
    border-radius: 4px;
    padding: 8px;
}

#employmentStatusChart {
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.05));
}

#officeDepartmentChart {
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.05));
}

/* Status Breakdown Cards */
.status-breakdown-item {
    transition: all 0.2s ease;
}

.status-breakdown-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>

<?php
require_once 'footer.php';
?>

