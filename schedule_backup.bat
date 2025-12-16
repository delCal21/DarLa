@echo off
REM Windows Batch Script to Run Automatic Backup
REM This script is called by Windows Task Scheduler

REM Change to the script directory
cd /d "C:\xampp1\htdocs\DarLa"

REM Run the PHP backup script using XAMPP's PHP
"C:\xampp1\php\php.exe" auto_backup.php

REM Optional: Uncomment the line below to see the output
REM pause

