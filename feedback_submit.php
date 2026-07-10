<?php
if (!headers_sent()) {
    header_remove('X-Powered-By');
}
header('Content-Type: application/json; charset=utf-8');

require_once 'admin/db_connect.php';
require_once 'admin/helpers/EmailHelper.php';

function feedback_admin_email(PDO $pdo)
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

$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));

if ($name === '' || $email === '' || $message === '') {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO feedback_submissions (name, email, message) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $message]);

    $recipient = feedback_admin_email($pdo);
    $subject = 'New website feedback';
    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="font-family:Arial,sans-serif;color:#0f172a;">'
        . '<h2>New website feedback</h2>'
        . '<p><strong>Name:</strong> ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</p>'
        . '<p><strong>Email:</strong> ' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '</p>'
        . '<p><strong>Message:</strong></p><p style="white-space:pre-wrap;">' . nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')) . '</p>'
        . '<p><strong>Time:</strong> ' . date('Y-m-d H:i:s') . '</p>'
        . '</body></html>';
    (new EmailHelper())->sendHtmlMessage($recipient, $subject, $html, $email, $name);

    echo json_encode(['success' => true, 'message' => 'Thank you for your feedback!']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error submitting feedback. Please try again.']);
}
?>