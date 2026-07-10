<?php
// admin/cron/database_cleanup.php
// Automated database cleanup - Run via cron job every 30 days at 3 AM

require_once '../db_connect.php';
require_once '../config/email.config.php';

$maintenanceFile = __DIR__ . '/../../maintenance.lock';
$logFile = __DIR__ . '/cleanup.log';

function logMessage($message)
{
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

try {
    logMessage("=== Database Cleanup Started ===");

    file_put_contents($maintenanceFile, time());
    logMessage("Maintenance mode enabled");

    $pdo->exec("DELETE FROM login_attempts WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 30 DAY)");
    logMessage("Cleaned old login attempts");

    $pdo->exec("DELETE FROM verification_codes WHERE created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)");
    logMessage("Cleaned old verification codes");

    $pdo->exec("DELETE FROM user_sessions WHERE expires_at < NOW()");
    logMessage("Cleaned expired sessions");

    $pdo->exec("DELETE FROM feedback_submissions WHERE is_read = 1 AND created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)");
    logMessage("Cleaned old read feedback");

    $pdo->exec("OPTIMIZE TABLE login_attempts, verification_codes, user_sessions, feedback_submissions, page_views");
    logMessage("Optimized database tables");

    sleep(300);

    if (file_exists($maintenanceFile)) {
        unlink($maintenanceFile);
        logMessage("Maintenance mode disabled");
    }

    logMessage("=== Database Cleanup Completed Successfully ===");

} catch (Exception $e) {
    logMessage("ERROR: " . $e->getMessage());

    if (file_exists($maintenanceFile)) {
        unlink($maintenanceFile);
    }
}
?>