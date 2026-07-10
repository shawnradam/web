<?php
$is_local = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1']);

if ($is_local) {
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_NAME', getenv('DB_NAME') ?: 'tradvisor_cms');
    define('DB_USER', getenv('DB_USER') ?: 'root');
    define('DB_PASS', getenv('DB_PASS') ?: '');
    define('SITE_URL', getenv('SITE_URL') ?: 'http://localhost/projects/shawnradam/tradvisor/advisor');
    define('ENVIRONMENT', 'development');
} else {
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_NAME', getenv('DB_NAME') ?: 'CHANGE_ME');
    define('DB_USER', getenv('DB_USER') ?: 'CHANGE_ME');
    define('DB_PASS', getenv('DB_PASS') ?: 'CHANGE_ME');
    define('SITE_URL', getenv('SITE_URL') ?: 'https://shawnradam.com');
    define('ENVIRONMENT', 'production');
}

define('DB_CHARSET', 'utf8mb4');
define('SITE_NAME', 'Shawn Radam | Personal Advisor');

if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

define('SESSION_LIFETIME', 28800);
define('SECURE_COOKIES', ENVIRONMENT === 'production');
define('MAX_UPLOAD_SIZE', 5242880);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);