<?php
require_once __DIR__ . '/../php/env.php';

if (session_status() === PHP_SESSION_NONE) {
    $secure = defined('SECURE_COOKIES') ? SECURE_COOKIES : (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $lifetime = defined('SESSION_LIFETIME') ? (int) SESSION_LIFETIME : 28800;

    session_set_cookie_params([
        'lifetime' => $lifetime,
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    ini_set('session.gc_maxlifetime', (string) $lifetime);
    session_start();
}
?>