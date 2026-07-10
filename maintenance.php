<?php
if (!headers_sent()) {
    header_remove('X-Powered-By');
    header('Content-Type: text/html; charset=utf-8');
}
ini_set('default_charset', 'UTF-8');
// maintenance.php - Professional Maintenance Page
session_start();
require_once 'admin/db_connect.php';

// Fetch maintenance settings
$settings = [];
try {
    $stmt = $pdo->query("SELECT * FROM site_settings");
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {
    $settings['maintenance_message'] = 'We are currently performing scheduled maintenance. We will be back shortly!';
}

$message = $settings['maintenance_message'] ?? 'We are currently performing scheduled maintenance. We will be back shortly!';
$endTime = $settings['maintenance_end_time'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Maintenance | Shawn Radam</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;600;700&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .title-font {
            font-family: 'Playfair Display', serif;
        }
    </style>
</head>

<body
    class="bg-gradient-to-br from-slate-900 via-navy-900 to-slate-900 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-2xl w-full">
        <!-- Main Card -->
        <div class="bg-slate-800/50 backdrop-blur-xl rounded-3xl border border-slate-700 p-8 md:p-12 shadow-2xl">

            <!-- Icon -->
            <div class="flex justify-center mb-8">
                <div class="relative">
                    <div class="absolute inset-0 bg-blue-500/20 rounded-full blur-2xl"></div>
                    <div
                        class="relative w-24 h-24 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center shadow-lg">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Title -->
            <h1 class="title-font text-4xl md:text-5xl font-bold text-white text-center mb-4">
                Under Maintenance
            </h1>

            <!-- Message -->
            <p class="text-slate-300 text-center text-lg mb-8 leading-relaxed">
                <?php
echo htmlspecialchars($message); ?>
            </p>

            <?php
if (!empty($endTime)): ?>
                <!-- Countdown Timer -->
                <div class="bg-slate-900/50 rounded-xl p-6 mb-8 border border-slate-700">
                    <p class="text-slate-400 text-sm text-center mb-3">Expected to be back by:</p>
                    <div class="text-center">
                        <div id="countdown" class="text-3xl font-bold text-blue-400"></div>
                    </div>
                </div>
            <?php
endif; ?>

            <!-- Contact Info -->
            <div class="border-t border-slate-700 pt-8 mt-8">
                <p class="text-slate-400 text-center text-sm mb-4">
                    Need immediate assistance?
                </p>
                <div class="flex justify-center gap-4">
                    <a href="mailto:contact@shawnradam.com"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white rounded-lg font-semibold transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                            </path>
                        </svg>
                        Email Us
                    </a>
                </div>
            </div>

            <!-- Branding -->
            <div class="text-center mt-8 pt-8 border-t border-slate-700">
                <p class="title-font text-2xl text-white mb-1">SHAWN RADAM</p>
                <p class="text-slate-500 text-xs uppercase tracking-widest">Personal Advisor</p>
            </div>

        </div>

        <!-- Footer Note -->
        <p class="text-slate-500 text-center text-sm mt-6">
            We apologize for any inconvenience. Thank you for your patience.
        </p>
    </div>

    <?php
if (!empty($endTime)): ?>
        <script>
            // Countdown Timer
            const endTime = new Date('<?php
echo $endTime; ?>').getTime();

            function updateCountdown() {
                const now = new Date().getTime();
                const distance = endTime - now;

                if (distance < 0) {
                    document.getElementById('countdown').innerHTML = "Maintenance Complete!";
                    return;
                }

                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                document.getElementById('countdown').innerHTML =
                    hours.toString().padStart(2, '0') + ":" +
                    minutes.toString().padStart(2, '0') + ":" +
                    seconds.toString().padStart(2, '0');
            }

            updateCountdown();
            setInterval(updateCountdown, 1000);
        </script>
    <?php
endif; ?>

</body>

</html>