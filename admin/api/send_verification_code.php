<?php
if (!headers_sent()) {
    header_remove('X-Powered-By');
}
// admin/api/send_verification_code.php
// API endpoint to send verification code via email
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once '../db_connect.php';
require_once '../config/email.config.php';
require_once '../helpers/EmailHelper.php';
require_once '../helpers/SecurityHelper.php';

$security = new SecurityHelper($pdo);
$ipAddress = $security->getClientIP();

if (!$security->checkRateLimit($ipAddress)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many attempts. Please try again later.']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$username = $data['username'] ?? '';

if (empty($username)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Username is required']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || empty($user['email'])) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiresAt = date('Y-m-d H:i:s', time() + EMAIL_VERIFICATION_EXPIRY);

    $deleteStmt = $pdo->prepare("DELETE FROM verification_codes WHERE user_id = ? AND verified = 0");
    $deleteStmt->execute([$user['id']]);

    $insertStmt = $pdo->prepare("INSERT INTO verification_codes (user_id, email, code, purpose, expires_at) VALUES (?, ?, ?, 'login', ?)");
    $insertStmt->execute([$user['id'], $user['email'], $code, $expiresAt]);

    $emailHelper = new EmailHelper();
    $emailSent = $emailHelper->sendVerificationCode($user['email'], $code, $user['username']);

    if ($emailSent) {
        $_SESSION['pending_2fa_user'] = $user['id'];
        $_SESSION['pending_2fa_email'] = $user['email'];

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Verification code sent to your email',
            'email_hint' => substr($user['email'], 0, 3) . '***@' . substr(strstr($user['email'], '@'), 1)
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to send email']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>