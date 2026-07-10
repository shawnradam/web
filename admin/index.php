<?php
// admin/index.php
require_once 'session_bootstrap.php';

if (!empty($_SESSION['user_id']) && !empty($_SESSION['role'])) {
    header('Location: dashboard.php');
    exit;
}

if (($_SESSION['passcode_verified'] ?? false) === true) {
    header('Location: login.php');
    exit;
}

header('Location: secure.php');
exit;
?>