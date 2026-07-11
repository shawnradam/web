<?php
if (!headers_sent()) {
    header_remove('X-Powered-By');
}
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

require_once 'admin/db_connect.php';
require_once 'admin/helpers/EmailHelper.php';
require_once 'includes/newsletter_settings.php';

function newsletter_admin_email(PDO $pdo)
{
    try {
        $email = $pdo->query("SELECT email_address FROM executive_profile_settings WHERE email_address IS NOT NULL AND email_address <> '' ORDER BY id ASC LIMIT 1")->fetchColumn();
        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        }
    } catch (Throwable $e) {}

    return 'office@shawnradam.com';
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}

$email = filter_var(trim((string) ($input['email'] ?? '')), FILTER_VALIDATE_EMAIL);
$newsletterSettings = sr_newsletter_settings($pdo ?? null);
$source = preg_replace('/[^a-z0-9_-]/i', '', (string) ($input['source'] ?? 'website'));
$source = $source !== '' ? substr($source, 0, 60) : 'website';

if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id FROM newsletter_subscribers WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        echo json_encode(['success' => true, 'message' => $newsletterSettings['newsletter_duplicate_message']]);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO newsletter_subscribers (email, source) VALUES (?, ?)");
    $stmt->execute([$email, $source]);

    $recipient = newsletter_admin_email($pdo);
    $subject = 'New newsletter subscriber';
    $message = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="font-family:Arial,sans-serif;color:#0f172a;">'
        . '<h2>New newsletter subscriber</h2>'
        . '<p><strong>Email:</strong> ' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '</p>'
        . '<p><strong>Source:</strong> ' . htmlspecialchars($source, ENT_QUOTES, 'UTF-8') . '</p>'
        . '<p><strong>Time:</strong> ' . date('Y-m-d H:i:s') . '</p>'
        . '</body></html>';
    (new EmailHelper())->sendHtmlMessage($recipient, $subject, $message, $email);

    echo json_encode(['success' => true, 'message' => $newsletterSettings['newsletter_success_message']]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later.']);
}
?>