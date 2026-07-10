<?php
if (!headers_sent()) {
    header_remove('X-Powered-By');
}
// admin/api/verify_code.php
// API endpoint to verify email TAC code
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once '../db_connect.php';
require_once '../helpers/SecurityHelper.php';

$security = new SecurityHelper($pdo);
$ipAddress = $security->getClientIP();

if (!$security->checkRateLimit($ipAddress)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many attempts. Please try again later.']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$code = $data['code'] ?? '';

if (empty($code) || !isset($_SESSION['pending_2fa_user'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

try {
    $userId = $_SESSION['pending_2fa_user'];

    $stmt = $pdo->prepare("
        SELECT vc.*, u.id, u.username, u.role 
        FROM verification_codes vc
        JOIN users u ON vc.user_id = u.id
        WHERE vc.user_id = ? 
        AND vc.code = ? 
        AND vc.verified = 0 
        AND vc.expires_at > NOW()
        LIMIT 1
    ");
    $stmt->execute([$userId, $code]);
    $verification = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$verification) {
        $security->logLoginAttempt($ipAddress, '', false);
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid or expired code']);
        exit;
    }

    $updateStmt = $pdo->prepare("UPDATE verification_codes SET verified = 1 WHERE id = ?");
    $updateStmt->execute([$verification['id']]);

    $_SESSION['user_id'] = $verification['id'];
    $_SESSION['username'] = $verification['username'];
    $_SESSION['role'] = $verification['role'];

    unset($_SESSION['pending_2fa_user']);
    unset($_SESSION['pending_2fa_email']);

    $security->logLoginAttempt($ipAddress, $verification['username'], true);

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'redirect' => 'dashboard.php'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>