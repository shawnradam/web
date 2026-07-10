<?php
if (!headers_sent()) {
    header_remove('X-Powered-By');
}
header('Content-Type: application/json; charset=utf-8');

require_once 'admin/db_connect.php';
require_once 'admin/helpers/EmailHelper.php';

function briefing_admin_email(PDO $pdo)
{
    try {
        $email = $pdo->query("SELECT email_address FROM executive_profile_settings WHERE email_address IS NOT NULL AND email_address <> '' ORDER BY id ASC LIMIT 1")->fetchColumn();
        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        }
    } catch (Throwable $e) {}

    return 'office@shawnradam.com';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}

$name = trim((string) ($input['name'] ?? ''));
$email = trim((string) ($input['email'] ?? ''));
$phone = trim((string) ($input['phone'] ?? ''));
$pillar = trim((string) ($input['pillar'] ?? ''));
$details = trim((string) ($input['details'] ?? ''));

if ($name === '' || $email === '' || $phone === '' || $pillar === '' || $details === '') {
    echo json_encode(['success' => false, 'message' => 'Please complete all briefing fields.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

if (strlen($details) > 5000) {
    echo json_encode(['success' => false, 'message' => 'Please shorten the briefing details.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO briefing_submissions (name, email, phone, pillar, details) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $email, $phone, $pillar, $details]);

    $recipient = briefing_admin_email($pdo);
    $subject = 'New advisor briefing request';
    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="font-family:Arial,sans-serif;color:#0f172a;">'
        . '<h2>New advisor briefing request</h2>'
        . '<p><strong>Name:</strong> ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</p>'
        . '<p><strong>Email:</strong> ' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '</p>'
        . '<p><strong>Phone:</strong> ' . htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') . '</p>'
        . '<p><strong>Pillar:</strong> ' . htmlspecialchars($pillar, ENT_QUOTES, 'UTF-8') . '</p>'
        . '<p><strong>Details:</strong></p><p style="white-space:pre-wrap;">' . nl2br(htmlspecialchars($details, ENT_QUOTES, 'UTF-8')) . '</p>'
        . '<p><strong>Time:</strong> ' . date('Y-m-d H:i:s') . '</p>'
        . '</body></html>';
    (new EmailHelper())->sendHtmlMessage($recipient, $subject, $html, $email, $name);

    echo json_encode(['success' => true, 'message' => 'Your briefing has been submitted.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Unable to submit the briefing. Please try again.']);
}
?>