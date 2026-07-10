<?php
if (!headers_sent()) {
    header_remove('X-Powered-By');
}
// admin/api/check_session.php
// API endpoint to check if admin is logged in
header('Content-Type: application/json; charset=utf-8');
session_start();

if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
    echo json_encode([
        'success' => true,
        'logged_in' => true,
        'username' => $_SESSION['username'],
        'role' => $_SESSION['role'] ?? 'admin'
    ]);
} else {
    echo json_encode([
        'success' => true,
        'logged_in' => false
    ]);
}
?>