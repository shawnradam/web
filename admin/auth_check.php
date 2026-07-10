<?php
// admin/auth_check.php
require_once __DIR__ . '/session_bootstrap.php';

if (!headers_sent()) {
    header_remove('X-Powered-By');
    header('Content-Type: text/html; charset=utf-8');
}
ini_set('default_charset', 'UTF-8');

// Enforce pre-login passcode gate
if (($_SESSION['passcode_verified'] ?? false) !== true) {
    header("Location: secure.php");
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}
?>