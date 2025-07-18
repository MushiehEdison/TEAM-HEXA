<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/reminder_functions.php';

// Log script execution
file_put_contents(__DIR__ . '/reminder_cron.log', "[" . date('Y-m-d H:i:s') . "] Starting reminder processing\n", FILE_APPEND);

try {
    // Process pending reminders
    processPendingReminders();
    
    // Log success
    file_put_contents(__DIR__ . '/reminder_cron.log', "[" . date('Y-m-d H:i:s') . "] Reminders processed successfully\n", FILE_APPEND);
} catch (Exception $e) {
    // Log errors
    file_put_contents(__DIR__ . '/reminder_cron.log', "[" . date('Y-m-d H:i:s') . "] Error: " . $e->getMessage() . "\n", FILE_APPEND);
}
?>