<?php
if (!headers_sent()) {
    header_remove('X-Powered-By');
    header('Content-Type: text/html; charset=utf-8');
}
ini_set('default_charset', 'UTF-8');
// admin/secure.php
require_once 'session_bootstrap.php';
require_once 'db_connect.php';

function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

// If already logged in or verified, direct to the right next page
if (!empty($_SESSION['user_id']) && !empty($_SESSION['role'])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SESSION['passcode_verified'] ?? false) {
    header("Location: login.php");
    exit;
}
$error = '';

// Handle POST PIN verification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pin = trim($_POST['pin'] ?? '');
    
    // Fetch passcode from database
    $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'security_passcode'");
    $stmt->execute();
    $dbPasscode = $stmt->fetchColumn();
    
    if (!$dbPasscode) {
        $dbPasscode = '123456';
    }
    
    if ($pin === $dbPasscode) {
        $_SESSION['passcode_verified'] = true;
        
        // Clear rate limiter log for this IP to prevent login lockout messages
        $ip = getClientIP();
        $clearStmt = $pdo->prepare("DELETE FROM login_attempts WHERE ip_address = ?");
        $clearStmt->execute([$ip]);
        
        header("Location: login.php");
        exit;
    } else {
        $error = "Incorrect 6-digit passcode. Access denied.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shawn Radam | Secure Portal Gatekeeper</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
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
    <style>
        [x-cloak] { display: none !important; }
        body {
            background-color: #0a0e27;
            background-image: radial-gradient(circle at 10% 20%, rgba(59, 130, 246, 0.08) 0%, transparent 60%),
                              radial-gradient(circle at 90% 80%, rgba(212, 175, 55, 0.05) 0%, transparent 60%);
        }
    </style>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="text-slate-300 font-sans flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-md bg-navy-800/80 border border-slate-700/60 rounded-2xl shadow-2xl p-8 backdrop-blur-md relative"
         x-data="{
            pin: '',
            keys: [],
            shuffle() {
                let digits = ['0','1','2','3','4','5','6','7','8','9'];
                for (let i = digits.length - 1; i > 0; i--) {
                    const j = Math.floor(Math.random() * (i + 1));
                    [digits[i], digits[j]] = [digits[j], digits[i]];
                }
                this.keys = digits;
            },
            addDigit(num) {
                if (this.pin.length < 6) {
                    this.pin += num;
                    this.shuffle();
                    if (this.pin.length === 6) {
                        this.$nextTick(() => {
                            this.$refs.pinForm.submit();
                        });
                    }
                }
            },
            backspace() {
                if (this.pin.length > 0) {
                    this.pin = this.pin.slice(0, -1);
                    this.shuffle();
                }
            },
            clear() {
                this.pin = '';
                this.shuffle();
            }
         }"
         x-init="shuffle()"
         @keydown.window="
            if ($event.key >= '0' && $event.key <= '9') addDigit($event.key);
            if ($event.key === 'Backspace') backspace();
            if ($event.key === 'Escape') clear();
         ">

        <!-- Decorative Blurs -->
        <div class="absolute top-0 right-0 w-24 h-24 bg-gold-500/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 bg-blue-500/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="text-center mb-8">
            <h1 class="text-2xl font-serif text-white mb-2">Secure Portal Gatekeeper</h1>
            <p class="text-slate-400 text-xs tracking-wider uppercase">Authentication Required</p>
        </div>

        <?php if ($error): ?>
            <div class="mb-6 bg-red-950/40 border border-red-500/50 text-red-400 text-sm px-4 py-3 rounded-lg text-center">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Hidden input form for native posting -->
        <form method="POST" x-ref="pinForm" style="display: none;">
            <input type="hidden" name="pin" :value="pin">
        </form>

        <!-- PIN Dots Display -->
        <div class="flex justify-center gap-3 mb-8">
            <template x-for="i in [0,1,2,3,4,5]">
                <div class="w-12 h-14 bg-slate-900 border border-slate-700/80 rounded-xl flex items-center justify-center text-lg font-bold text-white transition-all"
                     :class="pin.length > i ? 'border-gold-500 shadow-[0_0_10px_rgba(212,175,55,0.2)]' : ''">
                    <span x-show="pin.length > i" class="w-3.5 h-3.5 bg-gold-500 rounded-full"></span>
                </div>
            </template>
        </div>

        <!-- Numeric keypad grid - digits shuffle on every press -->
        <div class="grid grid-cols-3 gap-3 max-w-[280px] mx-auto">
            <!-- First 9 shuffled digits -->
            <template x-for="n in keys.slice(0,9)" :key="n">
                <button type="button" @click="addDigit(n)"
                        class="h-16 bg-slate-900/60 hover:bg-slate-900 border border-slate-700/40 hover:border-slate-600 rounded-xl text-xl font-medium text-white flex items-center justify-center transition-all active:scale-95 cursor-pointer select-none">
                    <span x-text="n"></span>
                </button>
            </template>

            <!-- Clear (fixed position) -->
            <button type="button" @click="clear()"
                    class="h-16 bg-slate-950 hover:bg-slate-900/80 border border-slate-800 rounded-xl text-sm font-semibold text-slate-400 flex items-center justify-center transition-all active:scale-95 cursor-pointer select-none">
                CLR
            </button>

            <!-- 10th shuffled digit -->
            <button type="button" @click="addDigit(keys[9])"
                    class="h-16 bg-slate-900/60 hover:bg-slate-900 border border-slate-700/40 hover:border-slate-600 rounded-xl text-xl font-medium text-white flex items-center justify-center transition-all active:scale-95 cursor-pointer select-none">
                <span x-text="keys[9]"></span>
            </button>

            <!-- Backspace (fixed position) -->
            <button type="button" @click="backspace()"
                    class="h-16 bg-slate-950 hover:bg-slate-900/80 border border-slate-800 rounded-xl text-sm font-semibold text-slate-400 flex items-center justify-center transition-all active:scale-95 cursor-pointer select-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414-6.414A2 2 0 0010.828 5H20a2 2 0 012 2v10a2 2 0 01-2 2h-9.172a2 2 0 01-1.414-.586L3 12z" />
                </svg>
            </button>
        </div>

        <div class="text-center mt-8 text-xs text-slate-500">
                <a href="../index.php" class="hover:text-gold-500 transition-colors">&larr; Back to Homepage</a>
        </div>

    </div>

</body>
</html>
