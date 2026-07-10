<?php
require_once 'db_connect.php';
require_once 'auth_check.php';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_settings') {
        $maintenance_mode = isset($_POST['maintenance_mode']) ? '1' : '0';
        $maintenance_message = $_POST['maintenance_message'];
        $maintenance_end_time = $_POST['maintenance_end_time'];
        $security_passcode = trim($_POST['security_passcode'] ?? '');
        $show_floating_calculator = isset($_POST['show_floating_calculator']) ? '1' : '0';
        $floating_calculator_text = trim($_POST['floating_calculator_text'] ?? 'Personal Loan Calculator');

        if ($security_passcode !== '' && !preg_match('/^\d{6}$/', $security_passcode)) {
            $error_message = "Passcode must be exactly 6 numeric digits when changing it.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            $stmt->execute(['maintenance_mode', $maintenance_mode]);
            $stmt->execute(['maintenance_message', $maintenance_message]);
            $stmt->execute(['maintenance_end_time', $maintenance_end_time]);
            if ($security_passcode !== '') {
                $stmt->execute(['security_passcode', $security_passcode]);
            }
            $stmt->execute(['show_floating_calculator', $show_floating_calculator]);
            $stmt->execute(['floating_calculator_text', $floating_calculator_text]);
            
            header("Location: site_settings.php?success=1");
            exit;
        }
    }
}

// Fetch current settings
$settings = [];
$stmt = $pdo->query("SELECT * FROM site_settings");
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = 'Site Settings'; ?>
    <?php include 'includes/admin_head.php'; ?>
</head>

<body class="bg-navy-900 text-slate-300 font-sans antialiased">
    <div class="flex min-h-screen relative" x-data="{ 
            sidebarOpen: false, 
            collapsed: (function(){ try { return localStorage.getItem('sidebarCollapsed') === 'true' } catch(e){ return false } })() 
        }" x-init="$watch('collapsed', val => { try { localStorage.setItem('sidebarCollapsed', val) } catch(e){} })">

        <?php include 'dashboard_sidebar_partial.php'; ?>

        <div class="flex-1 flex flex-col min-h-screen transition-all duration-300 ease-in-out md:ml-64 overflow-x-hidden"
            :class="collapsed ? 'md:!ml-20' : 'md:!ml-64'">

            <?php include 'includes/admin_header_partial.php'; ?>

            <main class="flex-1 p-4 pb-24 md:p-8 md:pb-8 overflow-y-auto">
                <div class="max-w-4xl mx-auto">

                    <!-- Header -->
                    <div class="flex justify-between items-end mb-8">
                        <div>
                            <h1 class="text-3xl font-serif text-white mb-2">Site Settings</h1>
                            <p class="text-slate-400">Manage maintenance mode and site configuration</p>
                        </div>
                    </div>

                    <?php if (isset($_GET['success'])): ?>
                        <div class="mb-6 bg-green-900/20 border border-green-500/50 text-green-400 px-4 py-3 rounded-lg">
                            Settings updated successfully!
                        </div>
                    <?php endif; ?>

                    <?php if (isset($error_message)): ?>
                        <div class="mb-6 bg-red-950/40 border border-red-500/50 text-red-400 px-4 py-3 rounded-lg">
                            Error: <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Form -->
                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="action" value="update_settings">

                        <!-- Maintenance Mode -->
                        <div class="bg-slate-800 rounded-xl border border-slate-700 p-6">
                            <h2 class="text-xl font-bold text-white mb-4">Maintenance Mode</h2>
                            
                            <div class="space-y-4">
                                <!-- Toggle Switch -->
                                <div class="flex items-center justify-between p-4 bg-slate-900 rounded-lg">
                                    <div>
                                        <h3 class="text-white font-bold mb-1">Enable Maintenance Mode</h3>
                                        <p class="text-slate-400 text-sm">When enabled, visitors will see a maintenance page. Admins can still access the site.</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="maintenance_mode" class="sr-only peer" 
                                            <?php echo ($settings['maintenance_mode'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                        <div class="w-14 h-7 bg-slate-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                </div>

                                <!-- Maintenance Message -->
                                <div>
                                    <label class="block text-slate-400 text-sm mb-2">Maintenance Message</label>
                                    <textarea name="maintenance_message" rows="3"
                                        class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white"><?php echo htmlspecialchars($settings['maintenance_message'] ?? ''); ?></textarea>
                                    <p class="text-slate-500 text-xs mt-1">This message will be displayed on the maintenance page</p>
                                </div>

                                <!-- End Time (Optional) -->
                                <div>
                                    <label class="block text-slate-400 text-sm mb-2">Expected End Time (Optional)</label>
                                    <input type="datetime-local" name="maintenance_end_time"
                                        value="<?php echo htmlspecialchars($settings['maintenance_end_time'] ?? ''); ?>"
                                        class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                    <p class="text-slate-500 text-xs mt-1">Leave empty if no specific end time</p>
                                </div>
                            </div>
                        </div>

                        <!-- Floating Calculator Widget Toggle -->
                        <div class="bg-slate-800 rounded-xl border border-slate-700 p-6">
                            <h2 class="text-xl font-bold text-white mb-4">Floating Calculator Widget</h2>
                            
                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-4 bg-slate-900 rounded-lg">
                                    <div>
                                        <h3 class="text-white font-bold mb-1">Show Floating Loan & Prop Calculator</h3>
                                        <p class="text-slate-400 text-sm">When enabled, visitors will see the interactive floating calculator icon at the bottom-left of public pages.</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="show_floating_calculator" class="sr-only peer" 
                                            <?php echo ($settings['show_floating_calculator'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                        <div class="w-14 h-7 bg-slate-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                </div>

                                <!-- Hover Text Input -->
                                <div class="mt-4 pt-4 border-t border-slate-700/50">
                                    <label class="block text-slate-400 text-sm mb-2">Floating Icon Hover Label / Tooltip</label>
                                    <input type="text" name="floating_calculator_text" required
                                        value="<?php echo htmlspecialchars($settings['floating_calculator_text'] ?? 'Personal Loan Calculator'); ?>"
                                        class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white focus:border-blue-500 outline-none">
                                    <p class="text-slate-500 text-xs mt-1">This text appears when visitors hover their mouse cursor over the floating calculator icon on the bottom-left of the page.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Portal Security -->
                        <div class="bg-slate-800 rounded-xl border border-slate-700 p-6">
                            <h2 class="text-xl font-bold text-white mb-4">Portal Security Gate</h2>
                            
                            <div class="space-y-4">
                                <div x-data="{ showPasscode: false }">
                                    <label class="block text-slate-400 text-sm mb-2">6-Digit Pre-Login Passcode</label>
                                    <div class="flex flex-col sm:flex-row gap-3 sm:items-center">
                                        <input type="password" x-bind:type="showPasscode ? 'text' : 'password'" name="security_passcode" maxlength="6" pattern="\d{6}"
                                            inputmode="numeric" autocomplete="new-password" placeholder="******"
                                            class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white font-mono tracking-widest text-lg max-w-xs focus:border-blue-500 outline-none">
                                        <button type="button" @click="showPasscode = !showPasscode"
                                            class="inline-flex items-center justify-center px-4 py-2 rounded border border-slate-600 bg-slate-900 text-slate-300 hover:text-white hover:border-blue-500 transition-colors text-sm font-bold min-w-[92px]"
                                            :aria-label="showPasscode ? 'Hide passcode' : 'Show passcode'">
                                            <span x-text="showPasscode ? 'Hide' : 'Show'"></span>
                                        </button>
                                    </div>
                                    <p class="text-slate-500 text-xs mt-1">Leave blank to keep the current passcode. Enter a new 6-digit PIN only when you want to change it.</p>
                                    <p class="text-slate-500 text-xs mt-1">This passcode acts as a gatekeeper PIN that anyone must enter at <strong>secure.php</strong> before reaching the login screen.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Preview -->
                        <div class="bg-slate-800 rounded-xl border border-slate-700 p-6">
                            <h2 class="text-xl font-bold text-white mb-4">Preview</h2>
                            <a href="../maintenance.php" target="_blank"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                View Maintenance Page
                            </a>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end gap-4">
                            <a href="dashboard.php" 
                                class="px-6 py-3 bg-slate-700 hover:bg-slate-600 text-white rounded-lg font-bold transition-colors">
                                Cancel
                            </a>
                            <button type="submit"
                                class="px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white rounded-lg font-bold transition-colors">
                                Save Settings
                            </button>
                        </div>

                    </form>

                </div>
            </main>
        </div>
    </div>
</body>
</html>



