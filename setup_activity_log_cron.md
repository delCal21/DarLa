# Activity Log Daily Reset Setup

The activity log will automatically reset (delete old entries) at the start of each new day.

## Automatic Cleanup

The activity log page (`activity_log.php`) now automatically deletes logs from previous days when accessed. This ensures the log only contains today's activities.

## Manual Cleanup Script

You can also run the cleanup manually by executing:
```bash
php cleanup_activity_log.php
```

## Scheduled Daily Cleanup (Recommended)

For automatic daily cleanup at midnight, set up a cron job:

### On Linux/Unix:

1. Open crontab:
```bash
crontab -e
```

2. Add this line to run cleanup at midnight every day:
```bash
0 0 * * * /usr/bin/php /path/to/your/project/cleanup_activity_log.php
```

Replace `/path/to/your/project/` with your actual project path.

### On Windows (Task Scheduler):

1. Open Task Scheduler
2. Create a new task
3. Set trigger: Daily at 12:00 AM
4. Set action: Start a program
5. Program: `C:\xampp\php\php.exe` (or your PHP path)
6. Arguments: `C:\xampp\htdocs\DarLa\cleanup_activity_log.php`
7. Start in: `C:\xampp\htdocs\DarLa`

### Using XAMPP on Windows:

If you're using XAMPP, you can also create a batch file:

**cleanup_activity_log.bat:**
```batch
@echo off
C:\xampp\php\php.exe C:\xampp\htdocs\DarLa\cleanup_activity_log.php
```

Then schedule this batch file to run daily using Windows Task Scheduler.

## How It Works

- The cleanup script deletes all activity logs where `DATE(created_at) < CURDATE()`
- This means only today's logs are kept
- The cleanup runs automatically when the activity log page is accessed
- For better performance, schedule the cron job to run at midnight

## Notes

- The automatic cleanup in `activity_log.php` ensures logs are cleaned even if the cron job fails
- Old logs are permanently deleted (not archived)
- If you need to keep logs longer, modify the cleanup query in `cleanup_activity_log.php`

