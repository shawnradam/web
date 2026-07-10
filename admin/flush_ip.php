<?php
if (!headers_sent()) {
    header_remove('X-Powered-By');
    header('Content-Type: text/html; charset=utf-8');
}
ini_set('default_charset', 'UTF-8');
// admin/flush_ip.php
require_once 'db_connect.php';

// Try to load SecurityHelper for IP detection
$ipAddress = '';
if (file_exists('helpers/SecurityHelper.php')) {
    require_once 'helpers/SecurityHelper.php';
    $security = new SecurityHelper($pdo);
    $ipAddress = $security->getClientIP();
} else {
    // Fallback IP detection
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipAddress = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } else {
        $ipAddress = $_SERVER['REMOTE_ADDR'];
    }
}

// 1. Delete attempts matching the current client IP
if (!empty($ipAddress)) {
    $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE ip_address = ?");
    $stmt->execute([$ipAddress]);
}

// 2. Truncate the table to clear all lockouts for safety
$pdo->exec("TRUNCATE TABLE login_attempts");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IP Lockout Flushed - Private Advisor</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#0a0e27] min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-md bg-[#141937] border border-slate-700/60 p-8 rounded-xl shadow-2xl relative overflow-hidden text-center">
        <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-yellow-500/20 via-yellow-500 to-yellow-500/20"></div>
        
        <div class="w-16 h-16 bg-yellow-500/10 border border-yellow-500/30 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
        </div>

        <h1 class="text-2xl font-serif text-white mb-2">Access Restored</h1>
        <p class="text-slate-400 text-sm mb-6">
            All failed login attempts and lockouts for IP address <span class="text-yellow-500 font-mono font-bold"><?php
echo htmlspecialchars($ipAddress); ?></span> have been cleared.
        </p>

        <a href="login.php" class="inline-block w-full bg-yellow-500 hover:bg-yellow-400 text-[#0a0e27] font-bold py-3 rounded-lg transition-colors uppercase tracking-widest text-xs shadow-lg hover:shadow-yellow-500/10">
            Go to Login
        </a>
    </div>
</body>
</html>
