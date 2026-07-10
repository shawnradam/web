<?php
// admin/profile.php
require_once 'auth_check.php';
require_once 'db_connect.php';

$message = '';
$error = '';
$user_id = $_SESSION['user_id'];

// Fetch Current User
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $display_name = trim($_POST['display_name']);
    $bio = trim($_POST['bio']);
    $avatar_url = $user['avatar_url'];

    if (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/uploads/profiles/';
        if (!is_dir($uploadDir))
            mkdir($uploadDir, 0777, true);

        $fileName = time() . '_' . $_FILES['avatar_file']['name'];
        $destPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['avatar_file']['tmp_name'], $destPath)) {
            $avatar_url = 'assets/uploads/profiles/' . $fileName;
        }
    }

    try {
        $updateSql = "UPDATE users SET display_name = ?, bio = ?, avatar_url = ? WHERE id = ?";
        $pdo->prepare($updateSql)->execute([$display_name, $bio, $avatar_url, $user_id]);
        $message = "Profile updated successfully.";
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Handle Account Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_account'])) {
    $email = trim($_POST['email']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    try {
        if (!empty($new_password)) {
            if ($new_password !== $confirm_password) {
                $error = "Passwords do not match.";
            } elseif (strlen($new_password) < 8) {
                $error = "Password must be at least 8 characters.";
            } else {
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $pdo->prepare("UPDATE users SET email = ?, password_hash = ? WHERE id = ?")
                    ->execute([$email, $hashed, $user_id]);
                $message = "Account updated successfully.";
            }
        } else {
            $pdo->prepare("UPDATE users SET email = ? WHERE id = ?")
                ->execute([$email, $user_id]);
            $message = "Email updated successfully.";
        }
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Handle 2FA Enable
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enable_2fa'])) {
    require_once '../vendor/autoload.php';
    $google2fa = new \PragmaRX\Google2FA\Google2FA();

    $secret = $google2fa->generateSecretKey();
    $qrCodeUrl = $google2fa->getQRCodeUrl(
        'Shawn Radam Advisory',
        $user['username'],
        $secret
    );

    try {
        $pdo->prepare("UPDATE users SET google2fa_secret = ? WHERE id = ?")
            ->execute([$secret, $user_id]);
        $message = "2FA secret generated. Scan the QR code below.";
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Handle 2FA Verify and Activate
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_2fa'])) {
    require_once '../vendor/autoload.php';
    $google2fa = new \PragmaRX\Google2FA\Google2FA();

    $code = trim($_POST['verification_code']);

    if ($google2fa->verifyKey($user['google2fa_secret'], $code, 8)) {
        $pdo->prepare("UPDATE users SET google2fa_enabled = 1 WHERE id = ?")
            ->execute([$user_id]);
        $message = "2FA enabled successfully!";
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
    } else {
        $error = "Invalid verification code. Please try again.";
    }
}

// Handle 2FA Disable
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['disable_2fa'])) {
    $pdo->prepare("UPDATE users SET google2fa_enabled = 0, google2fa_secret = NULL WHERE id = ?")
        ->execute([$user_id]);
    $message = "2FA disabled successfully.";
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php $pageTitle = 'Profile'; ?>
    <?php include 'includes/admin_head.php'; ?>
</head>

<body class="bg-slate-900 text-slate-300 antialiased selection:bg-gold-500 selection:text-white">

    <div class="flex min-h-screen relative" x-data="{ 
            sidebarOpen: false, 
            collapsed: (function(){ try { return localStorage.getItem('sidebarCollapsed') === 'true' } catch(e){ return false } })() 
        }" x-init="$watch('collapsed', val => { try { localStorage.setItem('sidebarCollapsed', val) } catch(e){} })">

        <?php include 'dashboard_sidebar_partial.php'; ?>

        <div class="flex-1 flex flex-col min-h-screen transition-all duration-300 ease-in-out md:ml-64 overflow-x-hidden"
            :class="collapsed ? 'md:!ml-20' : 'md:!ml-64'">

            <?php include 'includes/admin_header_partial.php'; ?>

            <main class="flex-1 p-8 pb-24 md:pb-8 overflow-y-auto">
                <header class="flex justify-between items-center mb-8">
                    <h1 class="text-3xl font-serif text-white">Author Profile</h1>
                </header>

                <?php if ($message): ?>
                    <div class="bg-green-900/50 border border-green-500 text-green-200 p-4 rounded mb-8">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="bg-red-900/50 border border-red-500 text-red-200 p-4 rounded mb-8">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <div class="bg-slate-800 p-8 rounded border border-slate-700 max-w-2xl">
                    <h2 class="text-xl font-serif text-white mb-6">Author Profile</h2>
                    <form method="POST" enctype="multipart/form-data" class="space-y-6">
                        <input type="hidden" name="update_profile" value="1">

                        <div class="flex items-start space-x-6">
                            <div class="shrink-0">
                                <?php if ($user['avatar_url']): ?>
                                    <img src="../<?php echo htmlspecialchars($user['avatar_url']); ?>"
                                        class="h-24 w-24 object-cover rounded-full border-2 border-slate-600">
                                <?php else: ?>
                                    <div
                                        class="h-24 w-24 rounded-full bg-slate-700 flex items-center justify-center text-slate-500 text-2xl font-bold">
                                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1">
                                <label class="block text-slate-500 text-xs uppercase tracking-wider mb-2">Profile
                                    Photo</label>
                                <input type="file" name="avatar_file" accept="image/*"
                                    class="w-full text-slate-400 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-900 file:text-blue-400 hover:file:bg-blue-800">
                            </div>
                        </div>

                        <div>
                            <label class="block text-slate-500 text-xs uppercase tracking-wider mb-2">Display
                                Name</label>
                            <input type="text" name="display_name"
                                value="<?php echo htmlspecialchars($user['display_name'] ?? ''); ?>"
                                class="w-full bg-slate-900 border border-slate-600 rounded p-3 text-white focus:border-blue-500 outline-none">
                            <p class="text-xs text-slate-500 mt-1">This name will appear on your blog posts.</p>
                        </div>

                        <div>
                            <label class="block text-slate-500 text-xs uppercase tracking-wider mb-2">Bio /
                                About</label>
                            <textarea name="bio" rows="4"
                                class="w-full bg-slate-900 border border-slate-600 rounded p-3 text-white focus:border-blue-500 outline-none"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                            <p class="text-xs text-slate-500 mt-1">A short description appearing in the author card.</p>
                        </div>

                        <div class="pt-4 border-t border-slate-700">
                            <button type="submit"
                                class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2 rounded font-bold uppercase text-xs tracking-widest transition-colors">Update
                                Profile</button>
                        </div>

                    </form>
                </div>

                <!-- Account Settings -->
                <div class="bg-slate-800 p-8 rounded border border-slate-700 max-w-2xl mt-6">
                    <h2 class="text-xl font-serif text-white mb-6">Account Settings</h2>
                    
                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="update_account" value="1">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-slate-400 text-xs uppercase tracking-wider mb-2">Username</label>
                                <input type="text" readonly disabled
                                    value="<?php echo htmlspecialchars($user['username']); ?>"
                                    class="w-full bg-slate-900 border border-slate-700 rounded p-3 text-slate-500 cursor-not-allowed outline-none">
                            </div>
                            
                            <div>
                                <label class="block text-slate-500 text-xs uppercase tracking-wider mb-2">Email Address</label>
                                <input type="email" name="email" required
                                    value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>"
                                    class="w-full bg-slate-900 border border-slate-600 rounded p-3 text-white focus:border-blue-500 outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-700/50">
                            <div>
                                <label class="block text-slate-400 text-xs uppercase tracking-wider mb-2">New Password</label>
                                <input type="password" name="new_password" placeholder="Leave blank to keep current"
                                    class="w-full bg-slate-900 border border-slate-600 rounded p-3 text-white focus:border-blue-500 outline-none">
                            </div>
                            
                            <div>
                                <label class="block text-slate-400 text-xs uppercase tracking-wider mb-2">Confirm New Password</label>
                                <input type="password" name="confirm_password" placeholder="Confirm new password"
                                    class="w-full bg-slate-900 border border-slate-600 rounded p-3 text-white focus:border-blue-500 outline-none">
                            </div>
                        </div>

                        <div class="pt-4 flex justify-between items-center border-t border-slate-700/50">
                            <button type="submit"
                                class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2.5 rounded font-bold uppercase text-xs tracking-widest transition-colors cursor-pointer">
                                Update Account
                            </button>
                            <div class="text-right">
                                <span class="text-xs text-slate-500 block">
                                    Role: <span class="text-gold-500 capitalize font-medium"><?php echo htmlspecialchars($user['role'] ?? 'Admin'); ?></span>
                                </span>
                                <span class="text-xs text-slate-500 block mt-1">
                                    2FA: <span class="<?php echo $user['google2fa_enabled'] ? 'text-green-500' : 'text-slate-400'; ?> font-medium">
                                        <?php echo $user['google2fa_enabled'] ? 'Enabled' : 'Disabled'; ?>
                                    </span>
                                </span>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Two-Factor Authentication (2FA) -->
                <div class="bg-slate-800 p-8 rounded border border-slate-700 max-w-2xl mt-6">
                    <h2 class="text-xl font-serif text-white mb-6">Two-Factor Authentication (2FA)</h2>
                    
                    <?php if ($user['google2fa_enabled']): ?>
                        <!-- 2FA is Active -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-3 p-4 bg-green-950/30 border border-green-500/30 rounded-lg">
                                <span class="text-green-500 text-2xl">🛡️</span>
                                <div>
                                    <p class="text-white font-medium">Google 2FA is Active</p>
                                    <p class="text-xs text-slate-400">Two-factor authentication is protecting your administrator account.</p>
                                </div>
                            </div>
                            <form method="POST">
                                <button type="submit" name="disable_2fa"
                                    class="bg-red-600 hover:bg-red-500 text-white px-6 py-2.5 rounded font-bold uppercase text-xs tracking-widest transition-colors cursor-pointer">
                                    Disable 2FA
                                </button>
                            </form>
                        </div>
                    <?php elseif (!empty($user['google2fa_secret'])): ?>
                        <!-- 2FA Setup in Progress -->
                        <div class="space-y-6">
                            <div class="p-4 bg-blue-950/30 border border-blue-500/30 rounded-lg">
                                <p class="text-white font-medium text-sm mb-1">Step 1: Scan the QR Code</p>
                                <p class="text-xs text-slate-400">Scan this code using Google Authenticator, Authy, or any TOTP app.</p>
                            </div>
                            
                            <div class="flex flex-col items-center justify-center p-6 bg-slate-900 rounded-xl border border-slate-700/50">
                                <?php
                                $google2fa = new \PragmaRX\Google2FA\Google2FA();
                                $qrCodeUrl = $google2fa->getQRCodeUrl('Shawn Radam Advisory', $user['username'], $user['google2fa_secret']);
                                $qrImageUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($qrCodeUrl);
                                ?>
                                <img src="<?php echo $qrImageUrl; ?>" alt="QR Code" class="bg-white p-3 rounded-lg border border-slate-700 mb-4">
                                <p class="text-xs text-slate-400 font-mono select-all">Secret Key: <?php echo htmlspecialchars($user['google2fa_secret']); ?></p>
                            </div>

                            <form method="POST" class="space-y-4">
                                <div class="p-4 bg-blue-950/30 border border-blue-500/30 rounded-lg">
                                    <p class="text-white font-medium text-sm mb-1">Step 2: Enter Verification Code</p>
                                    <p class="text-xs text-slate-400">Enter the 6-digit code shown in your authenticator app to verify setup.</p>
                                </div>
                                <div class="flex gap-4 items-end">
                                    <div class="flex-1 max-w-xs">
                                        <input type="text" name="verification_code" placeholder="000000" maxlength="6" required pattern="\d{6}"
                                            class="w-full bg-slate-900 border border-slate-600 rounded p-3 text-white font-mono tracking-widest text-lg text-center focus:border-blue-500 outline-none">
                                    </div>
                                    <button type="submit" name="verify_2fa"
                                        class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-3.5 rounded font-bold uppercase text-xs tracking-widest transition-colors cursor-pointer">
                                        Verify & Activate
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php else: ?>
                        <!-- 2FA is Disabled -->
                        <div class="space-y-4">
                            <p class="text-slate-400 text-sm">Two-Factor Authentication (2FA) adds an extra layer of protection by requiring a temporary verification code from your phone when logging in.</p>
                            <form method="POST">
                                <button type="submit" name="enable_2fa"
                                    class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2.5 rounded font-bold uppercase text-xs tracking-widest transition-colors cursor-pointer">
                                    Enable 2FA Setup
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Quick Actions -->
                <div class="bg-slate-800 p-8 rounded border border-slate-700 max-w-2xl mt-6">
                    <h2 class="text-xl font-serif text-white mb-6">Quick Actions</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <a href="dashboard.php"
                            class="flex items-center gap-3 p-4 bg-slate-900 hover:bg-slate-700 rounded-lg transition-colors">
                            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            <div>
                                <p class="text-white font-medium">Dashboard</p>
                                <p class="text-xs text-slate-400">View analytics</p>
                            </div>
                        </a>

                        <a href="feedback_manage.php"
                            class="flex items-center gap-3 p-4 bg-slate-900 hover:bg-slate-700 rounded-lg transition-colors">
                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                            </svg>
                            <div>
                                <p class="text-white font-medium">Feedback</p>
                                <p class="text-xs text-slate-400">Manage submissions</p>
                            </div>
                        </a>

                        <a href="posts.php"
                            class="flex items-center gap-3 p-4 bg-slate-900 hover:bg-slate-700 rounded-lg transition-colors">
                            <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <div>
                                <p class="text-white font-medium">Blog Posts</p>
                                <p class="text-xs text-slate-400">Create & edit</p>
                            </div>
                        </a>

                        <a href="logout.php"
                            class="flex items-center gap-3 p-4 bg-red-900/30 hover:bg-red-900/50 rounded-lg transition-colors">
                            <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <div>
                                <p class="text-red-400 font-medium">Logout</p>
                                <p class="text-xs text-red-400/60">End session</p>
                            </div>
                        </a>
                    </div>
                </div>

            </main>
</body>

</html>