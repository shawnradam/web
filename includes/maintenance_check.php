<?php
// includes/maintenance_check.php
// Check if maintenance mode is enabled and redirect non-admin users

session_start();
require_once __DIR__ . '/../admin/db_connect.php';

// Check if user is admin
$isAdmin = isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// If admin, skip maintenance check
if ($isAdmin) {
    return;
}

// Check maintenance mode status
try {
    $stmt = $pdo->query("SELECT setting_value FROM site_settings WHERE setting_key = 'maintenance_mode' LIMIT 1");
    $maintenanceMode = $stmt->fetchColumn();

    // If maintenance mode is ON and user is not admin, redirect to maintenance page
    if ($maintenanceMode == '1') {
        // Don't redirect if already on maintenance page
        $currentPage = basename($_SERVER['PHP_SELF']);
        if ($currentPage !== 'maintenance.php') {
            header('Location: ' . SITE_URL . '/maintenance.php');
            exit;
        }
    }
} catch (Exception $e) {
    // If table doesn't exist yet, allow access
    return;
}
?>