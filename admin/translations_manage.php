<?php
require_once 'db_connect.php';
require_once 'auth_check.php';

$success_message = '';
$error_message = '';

function normalizeTranslationKey(string $key): string
{
    $key = strtolower(trim($key));
    $key = preg_replace('/[^a-z0-9._-]+/', '.', $key);
    return trim($key, '.-_');
}

function syncDefaultTranslationKeys(PDO $pdo): int
{
    $en = file_exists(__DIR__ . '/../lang/en.php') ? require __DIR__ . '/../lang/en.php' : [];
    $ms = file_exists(__DIR__ . '/../lang/ms.php') ? require __DIR__ . '/../lang/ms.php' : [];
    $keys = array_unique(array_merge(array_keys($en), array_keys($ms)));
    sort($keys);

    $findStmt = $pdo->prepare("SELECT id, text_en, text_ms FROM translations WHERE translation_key = ? LIMIT 1");
    $insertStmt = $pdo->prepare("INSERT INTO translations (translation_key, section, text_en, text_ms, notes) VALUES (?, ?, ?, ?, ?)");
    $updateStmt = $pdo->prepare("UPDATE translations SET text_en = ?, text_ms = ? WHERE id = ?");
    $count = 0;

    foreach ($keys as $key) {
        $sectionParts = explode('.', $key);
        $section = $sectionParts[0] ?? 'general';
        $defaultEn = $en[$key] ?? '';
        $defaultMs = $ms[$key] ?? '';

        $findStmt->execute([$key]);
        $existing = $findStmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $nextEn = trim((string) ($existing['text_en'] ?? '')) === '' ? $defaultEn : $existing['text_en'];
            $nextMs = trim((string) ($existing['text_ms'] ?? '')) === '' ? $defaultMs : $existing['text_ms'];
            $updateStmt->execute([$nextEn, $nextMs, $existing['id']]);
        } else {
            $insertStmt->execute([$key, $section, $defaultEn, $defaultMs, 'Synced from language fallback files.']);
        }

        $count++;
    }

    return $count;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'save_translation') {
            $id = (int) ($_POST['id'] ?? 0);
            $key = normalizeTranslationKey($_POST['translation_key'] ?? '');
            $section = trim($_POST['section'] ?? '');
            $textEn = trim($_POST['text_en'] ?? '');
            $textMs = trim($_POST['text_ms'] ?? '');
            $notes = trim($_POST['notes'] ?? '');

            if ($key === '') {
                throw new RuntimeException('Translation key is required.');
            }

            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE translations SET translation_key = ?, section = ?, text_en = ?, text_ms = ?, notes = ? WHERE id = ?");
                $stmt->execute([$key, $section, $textEn, $textMs, $notes, $id]);
                $success_message = 'Translation updated successfully.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO translations (translation_key, section, text_en, text_ms, notes) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE section = VALUES(section), text_en = VALUES(text_en), text_ms = VALUES(text_ms), notes = VALUES(notes)");
                $stmt->execute([$key, $section, $textEn, $textMs, $notes]);
                $success_message = 'Translation saved successfully.';
            }
        }

        if ($action === 'delete_translation') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                $stmt = $pdo->prepare("DELETE FROM translations WHERE id = ?");
                $stmt->execute([$id]);
                $success_message = 'Translation deleted successfully.';
            }
        }

        if ($action === 'sync_defaults') {
            $count = syncDefaultTranslationKeys($pdo);
            $success_message = "Synced {$count} default translation keys.";
        }
    } catch (Throwable $e) {
        $error_message = $e->getMessage();
    }
}

$search = trim($_GET['search'] ?? '');
$sectionFilter = trim($_GET['section'] ?? '');
$params = [];
$where = [];

if ($search !== '') {
    $where[] = "(translation_key LIKE ? OR text_en LIKE ? OR text_ms LIKE ? OR notes LIKE ?)";
    $term = '%' . $search . '%';
    array_push($params, $term, $term, $term, $term);
}

if ($sectionFilter !== '') {
    $where[] = "section = ?";
    $params[] = $sectionFilter;
}

$sql = "SELECT * FROM translations";
if (!empty($where)) {
    $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY section ASC, translation_key ASC LIMIT 300";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$translations = $stmt->fetchAll(PDO::FETCH_ASSOC);
$sections = $pdo->query("SELECT DISTINCT section FROM translations WHERE section IS NOT NULL AND section != '' ORDER BY section ASC")->fetchAll(PDO::FETCH_COLUMN);
$totalTranslations = (int) $pdo->query("SELECT COUNT(*) FROM translations")->fetchColumn();
$missingBahasa = (int) $pdo->query("SELECT COUNT(*) FROM translations WHERE text_ms IS NULL OR text_ms = ''")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = 'Translations'; ?>
    <?php include 'includes/admin_head.php'; ?>
</head>
<body class="bg-navy-900 text-slate-300 font-sans antialiased">
    <div class="flex min-h-screen relative" x-data="{
            sidebarOpen: false,
            collapsed: (function(){ try { return localStorage.getItem('sidebarCollapsed') === 'true' } catch(e){ return false } })(),
            editingId: null
        }" x-init="$watch('collapsed', val => { try { localStorage.setItem('sidebarCollapsed', val) } catch(e){} })">

        <?php include 'dashboard_sidebar_partial.php'; ?>

        <div class="flex-1 flex flex-col min-h-screen transition-all duration-300 ease-in-out md:ml-64 overflow-x-hidden"
            :class="collapsed ? 'md:!ml-20' : 'md:!ml-64'">
            <?php include 'includes/admin_header_partial.php'; ?>

            <main class="flex-1 p-4 pb-24 md:p-8 md:pb-8 overflow-y-auto">
                <div class="max-w-7xl mx-auto">
                    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
                        <div>
                            <h1 class="text-3xl font-serif text-white mb-2">Frontend Translations</h1>
                            <p class="text-slate-400">Manage English and Bahasa Malaysia text used by the public website. Admin screens remain English.</p>
                        </div>
                        <form method="POST">
                            <input type="hidden" name="action" value="sync_defaults">
                            <button type="submit" class="bg-gold-500 hover:bg-gold-400 text-navy-900 px-5 py-2 rounded font-bold uppercase text-sm">
                                Sync Default Keys
                            </button>
                        </form>
                    </div>

                    <?php if ($success_message): ?>
                        <div class="mb-6 bg-green-900/20 border border-green-500/50 text-green-300 px-4 py-3 rounded-lg">
                            <?php echo htmlspecialchars($success_message); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($error_message): ?>
                        <div class="mb-6 bg-red-950/40 border border-red-500/50 text-red-300 px-4 py-3 rounded-lg">
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                        <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
                            <p class="text-slate-500 text-xs uppercase tracking-widest mb-2">Total Keys</p>
                            <p class="text-3xl text-white font-bold"><?php echo $totalTranslations; ?></p>
                        </div>
                        <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
                            <p class="text-slate-500 text-xs uppercase tracking-widest mb-2">Missing Bahasa</p>
                            <p class="text-3xl text-gold-500 font-bold"><?php echo $missingBahasa; ?></p>
                        </div>
                        <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
                            <p class="text-slate-500 text-xs uppercase tracking-widest mb-2">Sections</p>
                            <p class="text-3xl text-white font-bold"><?php echo count($sections); ?></p>
                        </div>
                    </div>

                    <div class="bg-slate-800 rounded-xl border border-slate-700 p-6 mb-8">
                        <h2 class="text-xl font-bold text-white mb-4">Add Translation Key</h2>
                        <form method="POST" class="space-y-4">
                            <input type="hidden" name="action" value="save_translation">
                            <input type="hidden" name="id" value="0">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-slate-500 text-xs uppercase mb-1">Translation Key</label>
                                    <input type="text" name="translation_key" required placeholder="home.hero.title"
                                        class="w-full bg-slate-900 border border-slate-600 rounded px-3 py-2 text-white">
                                </div>
                                <div>
                                    <label class="block text-slate-500 text-xs uppercase mb-1">Section</label>
                                    <input type="text" name="section" placeholder="home"
                                        class="w-full bg-slate-900 border border-slate-600 rounded px-3 py-2 text-white">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-slate-500 text-xs uppercase mb-1">English Text</label>
                                    <textarea name="text_en" rows="3" class="w-full bg-slate-900 border border-slate-600 rounded px-3 py-2 text-white"></textarea>
                                </div>
                                <div>
                                    <label class="block text-slate-500 text-xs uppercase mb-1">Bahasa Malaysia Text</label>
                                    <textarea name="text_ms" rows="3" class="w-full bg-slate-900 border border-slate-600 rounded px-3 py-2 text-white"></textarea>
                                </div>
                            </div>
                            <div>
                                <label class="block text-slate-500 text-xs uppercase mb-1">Notes</label>
                                <input type="text" name="notes" placeholder="Where this key appears or translation reminder"
                                    class="w-full bg-slate-900 border border-slate-600 rounded px-3 py-2 text-white">
                            </div>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-5 py-2 rounded font-bold uppercase text-sm">
                                Save Translation
                            </button>
                        </form>
                    </div>

                    <div class="bg-slate-800 rounded-xl border border-slate-700 p-6 mb-6">
                        <form method="GET" class="grid grid-cols-1 md:grid-cols-[1fr_220px_auto] gap-3">
                            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search key, English, Bahasa, or notes"
                                class="bg-slate-900 border border-slate-600 rounded px-3 py-2 text-white">
                            <select name="section" class="bg-slate-900 border border-slate-600 rounded px-3 py-2 text-white">
                                <option value="">All sections</option>
                                <?php foreach ($sections as $section): ?>
                                    <option value="<?php echo htmlspecialchars($section); ?>" <?php echo $sectionFilter === $section ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($section); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="bg-slate-700 hover:bg-slate-600 text-white px-5 py-2 rounded font-bold uppercase text-sm">Filter</button>
                        </form>
                    </div>

                    <div class="space-y-4">
                        <?php if (empty($translations)): ?>
                            <div class="bg-slate-800 border border-slate-700 rounded-xl p-8 text-center text-slate-500">
                                No translation keys found. Use Sync Default Keys or add one above.
                            </div>
                        <?php endif; ?>

                        <?php foreach ($translations as $row): ?>
                            <div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden" x-data="{ open: false }">
                                <div class="p-4 flex flex-col md:flex-row md:items-center gap-4">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-3 mb-2">
                                            <span class="text-xs uppercase tracking-widest text-gold-500"><?php echo htmlspecialchars($row['section'] ?: 'general'); ?></span>
                                            <?php if (trim((string) $row['text_ms']) === ''): ?>
                                                <span class="text-[10px] uppercase tracking-widest bg-gold-500/10 text-gold-400 px-2 py-1 rounded">Missing Bahasa</span>
                                            <?php endif; ?>
                                        </div>
                                        <h3 class="text-white font-mono text-sm truncate"><?php echo htmlspecialchars($row['translation_key']); ?></h3>
                                        <p class="text-slate-400 text-sm mt-1 line-clamp-2"><?php echo htmlspecialchars($row['text_en'] ?? ''); ?></p>
                                    </div>
                                    <button type="button" @click="open = !open" class="bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded text-sm font-bold uppercase">
                                        Edit
                                    </button>
                                </div>

                                <form x-show="open" x-cloak method="POST" class="border-t border-slate-700 p-4 space-y-4 bg-slate-900/40">
                                    <input type="hidden" name="action" value="save_translation">
                                    <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-slate-500 text-xs uppercase mb-1">Translation Key</label>
                                            <input type="text" name="translation_key" required value="<?php echo htmlspecialchars($row['translation_key']); ?>"
                                                class="w-full bg-slate-950 border border-slate-600 rounded px-3 py-2 text-white font-mono text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-slate-500 text-xs uppercase mb-1">Section</label>
                                            <input type="text" name="section" value="<?php echo htmlspecialchars($row['section'] ?? ''); ?>"
                                                class="w-full bg-slate-950 border border-slate-600 rounded px-3 py-2 text-white">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-slate-500 text-xs uppercase mb-1">English Text</label>
                                            <textarea name="text_en" rows="4" class="w-full bg-slate-950 border border-slate-600 rounded px-3 py-2 text-white"><?php echo htmlspecialchars($row['text_en'] ?? ''); ?></textarea>
                                        </div>
                                        <div>
                                            <label class="block text-slate-500 text-xs uppercase mb-1">Bahasa Malaysia Text</label>
                                            <textarea name="text_ms" rows="4" class="w-full bg-slate-950 border border-slate-600 rounded px-3 py-2 text-white"><?php echo htmlspecialchars($row['text_ms'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-slate-500 text-xs uppercase mb-1">Notes</label>
                                        <input type="text" name="notes" value="<?php echo htmlspecialchars($row['notes'] ?? ''); ?>"
                                            class="w-full bg-slate-950 border border-slate-600 rounded px-3 py-2 text-white">
                                    </div>
                                    <div class="flex flex-col md:flex-row justify-between gap-3">
                                        <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-5 py-2 rounded font-bold uppercase text-sm">Save Changes</button>
                                    </div>
                                </form>

                                <form method="POST" onsubmit="return confirm('Delete this translation key?');" class="px-4 pb-4" x-show="open" x-cloak>
                                    <input type="hidden" name="action" value="delete_translation">
                                    <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                                    <button type="submit" class="text-red-400 hover:text-red-300 text-xs font-bold uppercase">Delete Translation</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
