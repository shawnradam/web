<?php
// admin/config/email.config.php
// Email configuration for 2FA verification codes

define('USE_SMTP', false);

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('SMTP_FROM_EMAIL', 'noreply@shawnradam.com');
define('SMTP_FROM_NAME', 'Shawn Radam Admin');

define('EMAIL_VERIFICATION_EXPIRY', 7200);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_ATTEMPT_WINDOW', 900);
?>