<?php
// admin/helpers/EmailHelper.php
// Email helper for site notifications and verification codes.

class EmailHelper
{
    public function sendVerificationCode($toEmail, $code, $firstName = '')
    {
        require_once __DIR__ . '/../config/email.config.php';

        $subject = 'Your Admin Login Verification Code';
        $message = $this->getVerificationEmailTemplate($code, $firstName);

        if (USE_SMTP) {
            return $this->sendViaSMTP($toEmail, $subject, $message);
        }

        return $this->sendViaMail($toEmail, $subject, $message);
    }

    public function sendHtmlMessage($toEmail, $subject, $message, $replyToEmail = '', $replyToName = '')
    {
        require_once __DIR__ . '/../config/email.config.php';

        if (USE_SMTP) {
            return $this->sendViaSMTP($toEmail, $subject, $message, $replyToEmail, $replyToName);
        }

        return $this->sendViaMail($toEmail, $subject, $message, $replyToEmail, $replyToName);
    }

    private function sendViaSMTP($to, $subject, $message, $replyToEmail = '', $replyToName = '')
    {
        return false;
    }

    private function sendViaMail($to, $subject, $message, $replyToEmail = '', $replyToName = '')
    {
        $headers = 'From: ' . SMTP_FROM_NAME . ' <' . SMTP_FROM_EMAIL . ">\r\n";
        if ($replyToEmail && filter_var($replyToEmail, FILTER_VALIDATE_EMAIL)) {
            $replyToName = trim((string) $replyToName);
            $headers .= 'Reply-To: ' . ($replyToName !== '' ? $replyToName . ' <' . $replyToEmail . '>' : $replyToEmail) . "\r\n";
        }
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        return @mail($to, $subject, $message, $headers);
    }

    private function getVerificationEmailTemplate($code, $firstName)
    {
        $greeting = $firstName ? "Hi $firstName," : "Hello,";
        $year = date('Y');

        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:20px;font-family:Arial,sans-serif;background-color:#0a0e27;">
<div style="max-width:600px;margin:0 auto;background:#141937;padding:30px;border-radius:8px;border:1px solid #d4af37;">
    <h1 style="color:#d4af37;margin:0 0 20px;font-family:serif;">Shawn Radam Admin</h1>
    <p style="color:#fff;font-size:16px;margin:0 0 20px;">{$greeting}</p>
    <p style="color:#94a3b8;font-size:14px;margin:0 0 20px;">Your admin login verification code is:</p>
    <div style="background:#0a0e27;padding:20px;text-align:center;border-radius:8px;margin:0 0 20px;border:2px solid #d4af37;">
        <span style="color:#d4af37;font-size:32px;font-weight:bold;letter-spacing:8px;font-family:monospace;">{$code}</span>
    </div>
    <p style="color:#94a3b8;font-size:14px;margin:0 0 20px;">This code expires in 2 hours.</p>
    <p style="color:#64748b;font-size:12px;margin:0;">&copy; {$year} Shawn Radam. All rights reserved.</p>
</div>
</body>
</html>
HTML;
    }
}
?>