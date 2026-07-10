<?php
if (!headers_sent()) {
    header_remove('X-Powered-By');
    header('Content-Type: text/html; charset=utf-8');
}
ini_set('default_charset', 'UTF-8');
require_once 'session_bootstrap.php';
require_once 'db_connect.php';

if (!empty($_SESSION['user_id']) && !empty($_SESSION['role'])) {
    header('Location: dashboard.php');
    exit;
}
// Enforce pre-login passcode gate
$stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'security_passcode'");
$stmt->execute();
$passcode = $stmt->fetchColumn();
if ($passcode && ($_SESSION['passcode_verified'] ?? false) !== true) {
    header("Location: secure.php");
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'helpers/SecurityHelper.php';

$error = '';
$step = 'login';

$security = new SecurityHelper($pdo);
$ipAddress = $security->getClientIP();

if (!$security->checkRateLimit($ipAddress)) {
    $error = 'Too many login attempts. Please try again in 15 minutes.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT id, username, password_hash, role, email, google2fa_enabled FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {

            if ($user['google2fa_enabled']) {
                $_SESSION['pending_2fa_user'] = $user['id'];
                $_SESSION['pending_2fa_type'] = 'google';
                $step = '2fa_google';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $security->logLoginAttempt($ipAddress, $username, true);
                header("Location: dashboard.php");
                exit;
            }
        } else {
            $security->logLoginAttempt($ipAddress, $username, false);
            $error = "Invalid credentials.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}

// Handle Google 2FA Verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_2fa_google'])) {
    $code = trim($_POST['code'] ?? '');

    if (!empty($code) && isset($_SESSION['pending_2fa_user'])) {
        $userId = $_SESSION['pending_2fa_user'];

        $stmt = $pdo->prepare("SELECT id, username, role, google2fa_secret FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if ($user) {
            require_once '../vendor/autoload.php';
            $google2fa = new \PragmaRX\Google2FA\Google2FA();

            if ($google2fa->verifyKey($user['google2fa_secret'], $code, 8)) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                unset($_SESSION['pending_2fa_user']);
                unset($_SESSION['pending_2fa_type']);

                $security->logLoginAttempt($ipAddress, $user['username'], true);
                header("Location: dashboard.php");
                exit;
            } else {
                $security->logLoginAttempt($ipAddress, $user['username'], false);
                $error = "Invalid 2FA code.";
                $step = '2fa_google';
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_code'])) {
    $code = trim($_POST['code'] ?? '');

    if (!empty($code) && isset($_SESSION['pending_2fa_user'])) {
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
        $verification = $stmt->fetch();

        if ($verification) {
            $updateStmt = $pdo->prepare("UPDATE verification_codes SET verified = 1 WHERE id = ?");
            $updateStmt->execute([$verification['id']]);

            $_SESSION['user_id'] = $verification['id'];
            $_SESSION['username'] = $verification['username'];
            $_SESSION['role'] = $verification['role'];

            unset($_SESSION['pending_2fa_user']);
            unset($_SESSION['pending_2fa_email']);
            unset($_SESSION['pending_2fa_username']);

            $security->logLoginAttempt($ipAddress, $verification['username'], true);
            header("Location: dashboard.php");
            exit;
        } else {
            $security->logLoginAttempt($ipAddress, '', false);
            $error = "Invalid or expired code.";
            $step = '2fa_email';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Private Advisor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'navy': { 900: '#0a0e27', 800: '#141937' },
                        'gold': { 500: '#d4af37', 400: '#e0c158' }
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-navy-900 min-h-screen flex flex-col items-center justify-center p-6">
    <div class="w-full max-w-md">
        
        <!-- Login Card Container -->
        <div class="bg-navy-800 border border-slate-700/60 p-8 rounded-xl shadow-2xl relative overflow-hidden">
            <!-- Signature Gold Accent Border -->
            <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-gold-500/20 via-gold-500 to-gold-500/20"></div>

            <!-- Brand Header -->
            <div class="text-center mb-8">
                <h1 class="text-2xl font-serif text-white uppercase tracking-widest">SHAWN RADAM</h1>
                <p class="text-[0.55rem] text-gold-500 uppercase tracking-[0.4em] -mt-1 mb-4">Personal Advisor</p>
                <div class="h-[1px] bg-slate-700/50 w-24 mx-auto"></div>
            </div>

            <h2 class="text-xl font-serif text-white mb-6 text-center font-normal">Administrator Access</h2>

            <?php if ($error): ?>
                <div class="bg-red-950/40 border border-red-500/50 text-red-300 p-3 rounded-lg mb-6 text-xs flex items-center gap-2">
                                        <span>!</span>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($step === 'login'): ?>
                <form method="POST" class="space-y-6">
                    <div>
                        <label class="block text-slate-400 text-xs uppercase tracking-wider mb-2 font-medium">Username</label>
                        <input type="text" name="username"
                            class="w-full bg-slate-900/60 border border-slate-700 rounded-lg p-3 text-white focus:border-gold-500 focus:ring-1 focus:ring-gold-500/30 outline-none transition-all text-sm"
                            required autofocus>
                    </div>

                    <div>
                        <label class="block text-slate-400 text-xs uppercase tracking-wider mb-2 font-medium">Password</label>
                        <input type="password" name="password"
                            class="w-full bg-slate-900/60 border border-slate-700 rounded-lg p-3 text-white focus:border-gold-500 focus:ring-1 focus:ring-gold-500/30 outline-none transition-all text-sm"
                            required>
                    </div>

                    <button type="submit"
                        class="w-full bg-gold-500 hover:bg-gold-400 text-navy-900 font-bold py-3.5 rounded-lg transition-colors uppercase tracking-widest text-xs shadow-lg hover:shadow-gold-500/10">
                        Unlock Dashboard
                    </button>
                </form>
            <?php endif; ?>

            <?php if ($step === '2fa_email'): ?>
                <!-- Full Page Blur Overlay for Security TAC -->
                <div class="fixed inset-0 bg-navy-900/95 backdrop-blur-md z-50 flex items-center justify-center p-6">
                    <div class="bg-navy-800 border border-slate-700/60 p-8 rounded-xl shadow-2xl w-full max-w-md relative animate-fade-in">
                        <div class="absolute top-0 left-0 w-full h-[2px] bg-gold-500"></div>

                        <div class="text-center mb-6 mt-4">
                            <h2 class="text-2xl text-white font-serif mb-2">Security Verification</h2>
                            <p class="text-slate-400 text-sm mb-1">Check your email for the verification code</p>
                            <p class="text-gold-500 text-xs font-mono">
                                <?php echo isset($_SESSION['pending_2fa_email']) ? substr($_SESSION['pending_2fa_email'], 0, 3) . '***@' . substr(strstr($_SESSION['pending_2fa_email'], '@'), 1) : ''; ?>
                            </p>
                        </div>

                        <form method="POST" id="tacForm">
                            <input type="hidden" name="verify_code" value="1">
                            <div class="mb-6">
                                <label class="block text-gold-500 text-sm uppercase tracking-wider mb-3 text-center font-bold">Enter 6-Digit Code</label>
                                <input type="text" name="code" maxlength="6" pattern="[0-9]{6}" id="tacInput"
                                    class="w-full bg-slate-900 border-2 border-gold-500 rounded-lg p-4 text-white text-center text-3xl font-mono tracking-widest focus:border-gold-400 focus:ring-2 focus:ring-gold-500/50 outline-none transition-all"
                                    placeholder="* * * * * *" required autofocus autocomplete="off">
                            </div>

                            <button type="submit"
                                class="w-full bg-gold-500 hover:bg-gold-400 text-navy-900 font-bold py-4 rounded-lg transition-all uppercase tracking-widest text-sm shadow-lg hover:shadow-gold-500/50">
                                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Verify & Unlock
                            </button>
                        </form>

                        <div class="mt-6 text-center">
                            <a href="login.php" class="text-slate-500 hover:text-white text-sm transition-colors inline-flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Cancel & Return
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($step === '2fa_google'): ?>
                <!-- Full Page Blur Overlay for Google 2FA Security -->
                <div class="fixed inset-0 bg-navy-900/95 backdrop-blur-md z-50 flex items-center justify-center p-6">
                    <div class="bg-navy-800 border border-slate-700/60 p-8 rounded-xl shadow-2xl w-full max-w-md relative animate-fade-in">
                        <div class="absolute top-0 left-0 w-full h-[2px] bg-gold-500"></div>

                        <div class="text-center mb-6 mt-4">
                            <h2 class="text-2xl text-white font-serif mb-2">2FA Verification</h2>
                            <p class="text-slate-400 text-sm">Enter the 6-digit verification code from your Google Authenticator app.</p>
                        </div>

                        <form method="POST" id="google2faForm">
                            <input type="hidden" name="verify_2fa_google" value="1">
                            <div class="mb-6">
                                <label class="block text-gold-500 text-sm uppercase tracking-wider mb-3 text-center font-bold">Authenticator Code</label>
                                <input type="text" name="code" maxlength="6" pattern="[0-9]{6}" id="google2faInput"
                                    class="w-full bg-slate-900 border-2 border-gold-500 rounded-lg p-4 text-white text-center text-3xl font-mono tracking-widest focus:border-gold-400 focus:ring-2 focus:ring-gold-500/50 outline-none transition-all"
                                    placeholder="* * * * * *" required autofocus autocomplete="off">
                            </div>

                            <button type="submit"
                                class="w-full bg-gold-500 hover:bg-gold-400 text-navy-900 font-bold py-4 rounded-lg transition-all uppercase tracking-widest text-sm shadow-lg hover:shadow-gold-500/50 cursor-pointer">
                                Verify & Access
                            </button>
                        </form>

                        <div class="mt-6 text-center">
                            <a href="login.php" class="text-slate-500 hover:text-white text-sm transition-colors inline-flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Cancel & Return
                            </a>
                        </div>
                    </div>
                </div>
                
                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        const google2faInput = document.getElementById('google2faInput');
                        if (google2faInput) {
                            google2faInput.addEventListener('input', function (e) {
                                this.value = this.value.replace(/[^0-9]/g, '');
                                if (this.value.length === 6) {
                                    setTimeout(() => {
                                        document.getElementById('google2faForm').submit();
                                    }, 300);
                                }
                            });
                            
                            google2faInput.addEventListener('paste', function (e) {
                                e.preventDefault();
                                const pastedData = e.clipboardData.getData('text').replace(/[^0-9]/g, '').substring(0, 6);
                                this.value = pastedData;
                                if (pastedData.length === 6) {
                                    setTimeout(() => {
                                        document.getElementById('google2faForm').submit();
                                    }, 300);
                                }
                            });
                        }
                    });
                </script>
            <?php endif; ?>

        </div>

        <!-- Back to Homepage -->
        <div class="text-center mt-6">
            <a href="../index.php" class="text-slate-500 hover:text-gold-500 text-xs uppercase tracking-widest transition-colors inline-flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Back to Homepage
            </a>
        </div>

    </div>

    <script>
        const tacInput = document.getElementById('tacInput');
        if (tacInput) {
            tacInput.addEventListener('input', function (e) {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value.length === 6) {
                    setTimeout(() => {
                        document.getElementById('tacForm').submit();
                    }, 300);
                }
            });

            tacInput.addEventListener('paste', function (e) {
                e.preventDefault();
                const pastedData = e.clipboardData.getData('text').replace(/[^0-9]/g, '').substring(0, 6);
                this.value = pastedData;
                if (pastedData.length === 6) {
                    setTimeout(() => {
                        document.getElementById('tacForm').submit();
                    }, 300);
                }
            });
        }
    </script>
</body>

</html>