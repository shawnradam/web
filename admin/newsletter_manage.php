<?php
require_once 'db_connect.php';
require_once 'auth_check.php';
require_once '../includes/newsletter_settings.php';

$settingKeys = array_keys(sr_newsletter_default_settings());
$message = '';
$error = '';

function newsletter_redirect(string $status): void
{
    header('Location: newsletter_manage.php?' . $status . '=1');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'update_content') {
            $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            foreach ($settingKeys as $key) {
                $stmt->execute([$key, trim((string) ($_POST[$key] ?? ''))]);
            }
            newsletter_redirect('saved');
        }

        if ($action === 'add_subscriber') {
            $email = filter_var(trim((string) ($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
            $source = trim((string) ($_POST['source'] ?? 'admin'));
            if (!$email) {
                throw new RuntimeException('Please enter a valid email address.');
            }
            $stmt = $pdo->prepare("INSERT INTO newsletter_subscribers (email, source) VALUES (?, ?) ON DUPLICATE KEY UPDATE source = VALUES(source)");
            $stmt->execute([$email, $source !== '' ? substr($source, 0, 60) : 'admin']);
            newsletter_redirect('subscriber_saved');
        }

        if ($action === 'update_subscriber') {
            $id = (int) ($_POST['id'] ?? 0);
            $email = filter_var(trim((string) ($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
            $source = trim((string) ($_POST['source'] ?? ''));
            if ($id <= 0 || !$email) {
                throw new RuntimeException('Subscriber update requires a valid email address.');
            }
            $stmt = $pdo->prepare("UPDATE newsletter_subscribers SET email = ?, source = ? WHERE id = ?");
            $stmt->execute([$email, $source !== '' ? substr($source, 0, 60) : 'admin', $id]);
            newsletter_redirect('subscriber_saved');
        }

        if ($action === 'delete_subscriber') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new RuntimeException('Invalid subscriber.');
            }
            $stmt = $pdo->prepare("DELETE FROM newsletter_subscribers WHERE id = ?");
            $stmt->execute([$id]);
            newsletter_redirect('deleted');
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$settings = sr_newsletter_settings($pdo);
$subscribers = [];
try {
    $stmt = $pdo->query("SELECT id, email, source, subscribed_at FROM newsletter_subscribers ORDER BY subscribed_at DESC, id DESC LIMIT 300");
    $subscribers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $error = $error ?: 'Unable to load subscribers.';
}

if (isset($_GET['saved'])) $message = 'Newsletter content updated successfully.';
if (isset($_GET['subscriber_saved'])) $message = 'Subscriber saved successfully.';
if (isset($_GET['deleted'])) $message = 'Subscriber deleted successfully.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = 'Newsletter Manager'; ?>
    <?php include 'includes/admin_head.php'; ?>
</head>
<body class="bg-navy-900 text-slate-300 font-sans antialiased">
    <div class="flex min-h-screen relative" x-data="{ sidebarOpen: false, collapsed: (function(){ try { return localStorage.getItem('sidebarCollapsed') === 'true' } catch(e){ return false } })() }" x-init="$watch('collapsed', val => { try { localStorage.setItem('sidebarCollapsed', val) } catch(e){} })">
        <?php include 'dashboard_sidebar_partial.php'; ?>

        <div class="flex-1 flex flex-col min-h-screen transition-all duration-300 ease-in-out md:ml-64 overflow-x-hidden" :class="collapsed ? 'md:!ml-20' : 'md:!ml-64'">
            <?php include 'includes/admin_header_partial.php'; ?>

            <main class="flex-1 p-4 pb-24 md:p-8 md:pb-8 overflow-y-auto">
                <div class="max-w-6xl mx-auto space-y-6">
                    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
                        <div>
                            <p class="text-gold-500 uppercase tracking-[0.25em] text-xs font-bold mb-2">Newsletter</p>
                            <h1 class="text-3xl font-serif text-white">Newsletter Manager</h1>
                            <p class="text-slate-400 mt-2">Edit footer newsletter, popup newsletter, and subscriber records.</p>
                        </div>
                        <a href="../index.php" target="_blank" class="inline-flex items-center justify-center border border-gold-500/50 text-gold-500 hover:bg-gold-500 hover:text-navy-900 px-4 py-2 text-sm font-bold transition-colors">Preview Site</a>
                    </div>

                    <?php if ($message): ?>
                        <div class="bg-green-900/20 border border-green-500/50 text-green-400 px-4 py-3 rounded-lg"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="bg-red-950/40 border border-red-500/50 text-red-400 px-4 py-3 rounded-lg"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="POST" class="bg-slate-800 border border-slate-700 rounded-xl p-5 sm:p-6 space-y-6">
                        <input type="hidden" name="action" value="update_content">
                        <div>
                            <h2 class="text-xl font-bold text-white">Editable Newsletter Copy</h2>
                            <p class="text-sm text-slate-500 mt-1">These fields control all public newsletter wording in the footer and popup.</p>
                        </div>

                        <div class="grid lg:grid-cols-2 gap-6">
                            <section class="space-y-4">
                                <h3 class="text-gold-500 text-sm font-bold uppercase tracking-widest">Footer Newsletter</h3>
                                <?php foreach (['newsletter_footer_kicker' => 'Small Label', 'newsletter_footer_title' => 'Title', 'newsletter_footer_description' => 'Description', 'newsletter_footer_label' => 'Email Label', 'newsletter_footer_placeholder' => 'Placeholder', 'newsletter_footer_button' => 'Button Text', 'newsletter_footer_note' => 'Small Note'] as $key => $label): ?>
                                    <label class="block">
                                        <span class="block text-sm text-slate-400 mb-2"><?php echo htmlspecialchars($label); ?></span>
                                        <?php if (in_array($key, ['newsletter_footer_description', 'newsletter_footer_note'], true)): ?>
                                            <textarea name="<?php echo htmlspecialchars($key); ?>" rows="3" class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-3 text-white focus:border-gold-500 outline-none"><?php echo htmlspecialchars($settings[$key] ?? ''); ?></textarea>
                                        <?php else: ?>
                                            <input type="text" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($settings[$key] ?? ''); ?>" class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-3 text-white focus:border-gold-500 outline-none">
                                        <?php endif; ?>
                                    </label>
                                <?php endforeach; ?>
                            </section>

                            <section class="space-y-4">
                                <h3 class="text-gold-500 text-sm font-bold uppercase tracking-widest">Popup Newsletter</h3>
                                <?php foreach (['newsletter_popup_title' => 'Popup Title', 'newsletter_popup_description' => 'Popup Description', 'newsletter_popup_placeholder' => 'Popup Placeholder', 'newsletter_popup_button' => 'Popup Button', 'newsletter_success_message' => 'Success Message', 'newsletter_duplicate_message' => 'Already Subscribed Message'] as $key => $label): ?>
                                    <label class="block">
                                        <span class="block text-sm text-slate-400 mb-2"><?php echo htmlspecialchars($label); ?></span>
                                        <?php if ($key === 'newsletter_popup_description'): ?>
                                            <textarea name="<?php echo htmlspecialchars($key); ?>" rows="3" class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-3 text-white focus:border-gold-500 outline-none"><?php echo htmlspecialchars($settings[$key] ?? ''); ?></textarea>
                                        <?php else: ?>
                                            <input type="text" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($settings[$key] ?? ''); ?>" class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-3 text-white focus:border-gold-500 outline-none">
                                        <?php endif; ?>
                                    </label>
                                <?php endforeach; ?>
                            </section>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="bg-gold-500 hover:bg-gold-400 text-navy-900 px-6 py-3 rounded font-bold uppercase text-sm tracking-wider">Save Newsletter Copy</button>
                        </div>
                    </form>

                    <div class="grid lg:grid-cols-[0.75fr_1.25fr] gap-6">
                        <form method="POST" class="bg-slate-800 border border-slate-700 rounded-xl p-5 sm:p-6 space-y-4">
                            <input type="hidden" name="action" value="add_subscriber">
                            <div>
                                <h2 class="text-xl font-bold text-white">Add Subscriber</h2>
                                <p class="text-sm text-slate-500 mt-1">Manual entry for walk-ins or WhatsApp leads.</p>
                            </div>
                            <label class="block">
                                <span class="block text-sm text-slate-400 mb-2">Email</span>
                                <input type="email" name="email" required class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-3 text-white focus:border-gold-500 outline-none">
                            </label>
                            <label class="block">
                                <span class="block text-sm text-slate-400 mb-2">Source</span>
                                <input type="text" name="source" value="admin" class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-3 text-white focus:border-gold-500 outline-none">
                            </label>
                            <button type="submit" class="w-full bg-slate-700 hover:bg-slate-600 text-white px-5 py-3 rounded font-bold">Add Subscriber</button>
                        </form>

                        <section class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden">
                            <div class="p-5 sm:p-6 border-b border-slate-700 flex items-center justify-between gap-4">
                                <div>
                                    <h2 class="text-xl font-bold text-white">Subscribers</h2>
                                    <p class="text-sm text-slate-500 mt-1"><?php echo count($subscribers); ?> recent records shown</p>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead class="bg-slate-900 text-slate-400 uppercase text-xs tracking-wider">
                                        <tr>
                                            <th class="px-4 py-3">Email</th>
                                            <th class="px-4 py-3">Source</th>
                                            <th class="px-4 py-3">Date</th>
                                            <th class="px-4 py-3 text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-700">
                                        <?php if (empty($subscribers)): ?>
                                            <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">No subscribers yet.</td></tr>
                                        <?php endif; ?>
                                        <?php foreach ($subscribers as $subscriber): ?>
                                            <tr class="align-top hover:bg-slate-900/40">
                                                <td class="px-4 py-3 min-w-[220px]">
                                                    <form id="subscriber-<?php echo (int) $subscriber['id']; ?>" method="POST" class="space-y-2">
                                                        <input type="hidden" name="action" value="update_subscriber">
                                                        <input type="hidden" name="id" value="<?php echo (int) $subscriber['id']; ?>">
                                                        <input type="email" name="email" value="<?php echo htmlspecialchars($subscriber['email']); ?>" class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-white focus:border-gold-500 outline-none">
                                                    </form>
                                                </td>
                                                <td class="px-4 py-3 min-w-[140px]">
                                                    <input form="subscriber-<?php echo (int) $subscriber['id']; ?>" type="text" name="source" value="<?php echo htmlspecialchars($subscriber['source'] ?? 'website'); ?>" class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-white focus:border-gold-500 outline-none">
                                                </td>
                                                <td class="px-4 py-3 text-slate-400 whitespace-nowrap"><?php echo htmlspecialchars(date('M j, Y', strtotime($subscriber['subscribed_at']))); ?></td>
                                                <td class="px-4 py-3">
                                                    <div class="flex justify-end gap-2">
                                                        <button form="subscriber-<?php echo (int) $subscriber['id']; ?>" type="submit" class="px-3 py-2 rounded bg-gold-500 text-navy-900 font-bold text-xs uppercase">Save</button>
                                                        <form method="POST" onsubmit="return confirm('Delete this subscriber?');">
                                                            <input type="hidden" name="action" value="delete_subscriber">
                                                            <input type="hidden" name="id" value="<?php echo (int) $subscriber['id']; ?>">
                                                            <button type="submit" class="px-3 py-2 rounded bg-red-950/70 text-red-300 border border-red-900 font-bold text-xs uppercase">Delete</button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>