<?php
require_once 'db_connect.php';
require_once 'auth_check.php';

function about_manage_redirect($status)
{
    header('Location: about_manage.php?status=' . urlencode($status));
    exit;
}

function about_manage_upload($field, $currentPath = '')
{
    if (empty($_FILES[$field]['name']) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        return $currentPath;
    }

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $tmp = $_FILES[$field]['tmp_name'];
    $mime = mime_content_type($tmp);
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Portrait must be JPG, PNG, or WEBP.');
    }

    $uploadDir = '../assets/uploads/about/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = time() . '_about_portrait.' . $allowed[$mime];
    $target = $uploadDir . $fileName;
    if (!move_uploaded_file($tmp, $target)) {
        throw new RuntimeException('Unable to upload portrait image.');
    }

    return 'assets/uploads/about/' . $fileName;
}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS about_page (
        id INT AUTO_INCREMENT PRIMARY KEY,
        page_title VARCHAR(255) NOT NULL DEFAULT 'About Shawn Radam',
        page_subtitle VARCHAR(255) DEFAULT 'Professional Advisory Services',
        hero_label VARCHAR(100) DEFAULT 'Personal Advisor',
        profile_name VARCHAR(150) DEFAULT 'Shawn Radam',
        profile_title VARCHAR(150) DEFAULT 'Personal Advisor',
        portrait_url VARCHAR(255) DEFAULT NULL,
        intro_text TEXT,
        cta_heading VARCHAR(255) DEFAULT 'Ready to discuss your goals?',
        cta_button_text VARCHAR(100) DEFAULT 'Get in Touch',
        cta_button_link VARCHAR(255) DEFAULT 'contact.php',
        seo_title VARCHAR(255) DEFAULT 'About | Shawn Radam',
        seo_desc VARCHAR(255) DEFAULT 'Learn more about Shawn Radam and his professional advisory background.',
        is_published TINYINT(1) DEFAULT 1,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS about_expertise_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(150) NOT NULL,
        description TEXT,
        icon_key VARCHAR(50) DEFAULT 'briefcase',
        accent_color VARCHAR(30) DEFAULT 'blue',
        display_order INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    $count = (int) $pdo->query("SELECT COUNT(*) FROM about_page")->fetchColumn();
    if ($count === 0) {
        $stmt = $pdo->prepare("INSERT INTO about_page (page_title, page_subtitle, hero_label, profile_name, profile_title, intro_text, cta_heading, cta_button_text, cta_button_link, seo_title, seo_desc, is_published) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            'About Shawn Radam',
            'Professional Advisory Services',
            'Personal Advisor',
            'Shawn Radam',
            'Personal Advisor',
            'Experienced professional providing expert advisory services in real estate, finance, and business development. Committed to helping clients achieve their goals through strategic guidance and personalized solutions.',
            'Ready to discuss your goals?',
            'Get in Touch',
            'contact.php',
            'About | Shawn Radam',
            'Learn more about Shawn Radam and his professional advisory background.',
            1
        ]);
    }

    $count = (int) $pdo->query("SELECT COUNT(*) FROM about_expertise_items")->fetchColumn();
    if ($count === 0) {
        $stmt = $pdo->prepare("INSERT INTO about_expertise_items (title, description, icon_key, accent_color, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ([
            ['Real Estate', 'Property investment and development consulting', 'home', 'blue', 1, 1],
            ['Finance', 'Financial planning and investment strategies', 'currency', 'green', 2, 1],
            ['Business', 'Strategic business development and consulting', 'briefcase', 'purple', 3, 1]
        ] as $item) {
            $stmt->execute($item);
        }
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'update_page') {
            $currentPortrait = $_POST['current_portrait_url'] ?? '';
            $portraitUrl = about_manage_upload('portrait', $currentPortrait);
            if (isset($_POST['remove_portrait'])) {
                $portraitUrl = '';
            }

            $stmt = $pdo->prepare("UPDATE about_page SET page_title = ?, page_subtitle = ?, hero_label = ?, profile_name = ?, profile_title = ?, portrait_url = ?, intro_text = ?, cta_heading = ?, cta_button_text = ?, cta_button_link = ?, seo_title = ?, seo_desc = ?, is_published = ? WHERE id = ?");
            $stmt->execute([
                trim($_POST['page_title'] ?? ''),
                trim($_POST['page_subtitle'] ?? ''),
                trim($_POST['hero_label'] ?? ''),
                trim($_POST['profile_name'] ?? ''),
                trim($_POST['profile_title'] ?? ''),
                $portraitUrl,
                trim($_POST['intro_text'] ?? ''),
                trim($_POST['cta_heading'] ?? ''),
                trim($_POST['cta_button_text'] ?? ''),
                trim($_POST['cta_button_link'] ?? 'contact.php'),
                trim($_POST['seo_title'] ?? ''),
                trim($_POST['seo_desc'] ?? ''),
                isset($_POST['is_published']) ? 1 : 0,
                (int) ($_POST['id'] ?? 1),
            ]);
            about_manage_redirect('page_saved');
        }

        if ($action === 'save_item') {
            $id = (int) ($_POST['id'] ?? 0);
            $data = [
                trim($_POST['title'] ?? ''),
                trim($_POST['description'] ?? ''),
                trim($_POST['icon_key'] ?? 'briefcase'),
                trim($_POST['accent_color'] ?? 'blue'),
                (int) ($_POST['display_order'] ?? 0),
                isset($_POST['is_active']) ? 1 : 0,
            ];

            if ($data[0] === '') {
                throw new RuntimeException('Expertise title is required.');
            }

            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE about_expertise_items SET title = ?, description = ?, icon_key = ?, accent_color = ?, display_order = ?, is_active = ? WHERE id = ?");
                $stmt->execute(array_merge($data, [$id]));
                about_manage_redirect('item_updated');
            }

            $stmt = $pdo->prepare("INSERT INTO about_expertise_items (title, description, icon_key, accent_color, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute($data);
            about_manage_redirect('item_added');
        }

        if ($action === 'delete_item') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                $stmt = $pdo->prepare("DELETE FROM about_expertise_items WHERE id = ?");
                $stmt->execute([$id]);
            }
            about_manage_redirect('item_deleted');
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$about = $pdo->query("SELECT * FROM about_page ORDER BY id ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$items = $pdo->query("SELECT * FROM about_expertise_items ORDER BY display_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
$editItem = null;
if (!empty($_GET['edit_item'])) {
    $stmt = $pdo->prepare("SELECT * FROM about_expertise_items WHERE id = ?");
    $stmt->execute([(int) $_GET['edit_item']]);
    $editItem = $stmt->fetch(PDO::FETCH_ASSOC);
}

$statusMessages = [
    'page_saved' => 'About page updated successfully.',
    'item_added' => 'Expertise item added successfully.',
    'item_updated' => 'Expertise item updated successfully.',
    'item_deleted' => 'Expertise item deleted successfully.',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = 'About Page'; ?>
    <?php include 'includes/admin_head.php'; ?>
</head>
<body class="bg-navy-900 text-slate-300 font-sans antialiased">
    <div class="flex min-h-screen relative" x-data="{ sidebarOpen: false, collapsed: (function(){ try { return localStorage.getItem('sidebarCollapsed') === 'true' } catch(e){ return false } })() }" x-init="$watch('collapsed', val => { try { localStorage.setItem('sidebarCollapsed', val) } catch(e){} })">
        <?php include 'dashboard_sidebar_partial.php'; ?>

        <div class="flex-1 flex flex-col min-h-screen transition-all duration-300 ease-in-out md:ml-64 overflow-x-hidden" :class="collapsed ? 'md:!ml-20' : 'md:!ml-64'">
            <?php include 'includes/admin_header_partial.php'; ?>

            <main class="flex-1 p-4 pb-24 md:p-8 md:pb-8 overflow-y-auto">
                <div class="max-w-7xl mx-auto space-y-6">
                    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-3 mb-3">
                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-cyan-500/15 text-cyan-300 border border-cyan-500/25">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </span>
                                <div>
                                    <h1 class="text-3xl font-serif text-white leading-tight">About Page</h1>
                                    <p class="text-slate-500 text-sm">Frontend content manager</p>
                                </div>
                            </div>
                            <p class="text-slate-400 max-w-2xl">Edit the public About page, profile summary, call-to-action, SEO metadata, and expertise cards from one screen.</p>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <a href="../about.php" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white rounded-lg text-sm font-bold transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                Preview
                            </a>
                            <button type="submit" form="aboutPageForm" class="inline-flex items-center gap-2 px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-sm font-bold transition-colors shadow-lg shadow-blue-950/30">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                Save Page
                            </button>
                        </div>
                    </div>

                    <?php if (!empty($_GET['status']) && isset($statusMessages[$_GET['status']])): ?>
                        <div class="bg-green-950/40 border border-green-500/40 text-green-300 px-4 py-3 rounded-lg flex items-start gap-3">
                            <span class="mt-0.5 h-2.5 w-2.5 rounded-full bg-green-400"></span>
                            <span><?php echo htmlspecialchars($statusMessages[$_GET['status']]); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($error)): ?>
                        <div class="bg-red-950/50 border border-red-500/50 text-red-300 px-4 py-3 rounded-lg flex items-start gap-3">
                            <span class="mt-0.5 h-2.5 w-2.5 rounded-full bg-red-400"></span>
                            <span>Error: <?php echo htmlspecialchars($error); ?></span>
                        </div>
                    <?php endif; ?>

                    <form id="aboutPageForm" method="POST" enctype="multipart/form-data" class="grid xl:grid-cols-[minmax(0,1fr)_360px] gap-6">
                        <input type="hidden" name="action" value="update_page">
                        <input type="hidden" name="id" value="<?php echo (int) $about['id']; ?>">
                        <input type="hidden" name="current_portrait_url" value="<?php echo htmlspecialchars($about['portrait_url'] ?? ''); ?>">

                        <div class="space-y-6">
                            <section class="bg-slate-800/80 border border-slate-700 rounded-lg overflow-hidden">
                                <div class="px-5 py-4 border-b border-slate-700 bg-slate-900/40 flex items-center justify-between gap-4">
                                    <div>
                                        <h2 class="text-white font-bold">Hero and Profile Copy</h2>
                                        <p class="text-slate-500 text-xs mt-1">This content appears at the top of the public About page.</p>
                                    </div>
                                    <label class="inline-flex items-center gap-2 text-sm text-slate-300 whitespace-nowrap">
                                        <input type="checkbox" name="is_published" value="1" <?php echo !empty($about['is_published']) ? 'checked' : ''; ?> class="rounded bg-slate-900 border-slate-600 text-blue-600">
                                        Published
                                    </label>
                                </div>
                                <div class="p-5 space-y-5">
                                    <div class="grid md:grid-cols-2 gap-4">
                                        <label class="block">
                                            <span class="block text-slate-400 text-sm mb-2">Page Title</span>
                                            <input type="text" name="page_title" required value="<?php echo htmlspecialchars($about['page_title'] ?? ''); ?>" class="w-full bg-slate-950/80 border border-slate-600 rounded-lg px-4 py-2.5 text-white focus:border-blue-500 outline-none">
                                        </label>
                                        <label class="block">
                                            <span class="block text-slate-400 text-sm mb-2">Page Subtitle</span>
                                            <input type="text" name="page_subtitle" value="<?php echo htmlspecialchars($about['page_subtitle'] ?? ''); ?>" class="w-full bg-slate-950/80 border border-slate-600 rounded-lg px-4 py-2.5 text-white focus:border-blue-500 outline-none">
                                        </label>
                                        <label class="block">
                                            <span class="block text-slate-400 text-sm mb-2">Hero Label</span>
                                            <input type="text" name="hero_label" value="<?php echo htmlspecialchars($about['hero_label'] ?? ''); ?>" class="w-full bg-slate-950/80 border border-slate-600 rounded-lg px-4 py-2.5 text-white focus:border-blue-500 outline-none">
                                        </label>
                                        <label class="block">
                                            <span class="block text-slate-400 text-sm mb-2">Profile Title</span>
                                            <input type="text" name="profile_title" value="<?php echo htmlspecialchars($about['profile_title'] ?? ''); ?>" class="w-full bg-slate-950/80 border border-slate-600 rounded-lg px-4 py-2.5 text-white focus:border-blue-500 outline-none">
                                        </label>
                                    </div>
                                    <label class="block">
                                        <span class="block text-slate-400 text-sm mb-2">Profile Name</span>
                                        <input type="text" name="profile_name" required value="<?php echo htmlspecialchars($about['profile_name'] ?? ''); ?>" class="w-full bg-slate-950/80 border border-slate-600 rounded-lg px-4 py-2.5 text-white focus:border-blue-500 outline-none">
                                    </label>
                                    <label class="block">
                                        <span class="block text-slate-400 text-sm mb-2">Intro Text</span>
                                        <textarea name="intro_text" rows="7" class="w-full bg-slate-950/80 border border-slate-600 rounded-lg px-4 py-3 text-white focus:border-blue-500 outline-none leading-relaxed"><?php echo htmlspecialchars($about['intro_text'] ?? ''); ?></textarea>
                                    </label>
                                </div>
                            </section>

                            <section class="bg-slate-800/80 border border-slate-700 rounded-lg overflow-hidden">
                                <div class="px-5 py-4 border-b border-slate-700 bg-slate-900/40">
                                    <h2 class="text-white font-bold">Call-to-Action</h2>
                                    <p class="text-slate-500 text-xs mt-1">Control the final prompt and button at the bottom of the About page.</p>
                                </div>
                                <div class="p-5 grid md:grid-cols-3 gap-4">
                                    <label class="block md:col-span-1">
                                        <span class="block text-slate-400 text-sm mb-2">CTA Heading</span>
                                        <input type="text" name="cta_heading" value="<?php echo htmlspecialchars($about['cta_heading'] ?? ''); ?>" class="w-full bg-slate-950/80 border border-slate-600 rounded-lg px-4 py-2.5 text-white focus:border-blue-500 outline-none">
                                    </label>
                                    <label class="block">
                                        <span class="block text-slate-400 text-sm mb-2">Button Text</span>
                                        <input type="text" name="cta_button_text" value="<?php echo htmlspecialchars($about['cta_button_text'] ?? ''); ?>" class="w-full bg-slate-950/80 border border-slate-600 rounded-lg px-4 py-2.5 text-white focus:border-blue-500 outline-none">
                                    </label>
                                    <label class="block">
                                        <span class="block text-slate-400 text-sm mb-2">Button Link</span>
                                        <input type="text" name="cta_button_link" value="<?php echo htmlspecialchars($about['cta_button_link'] ?? 'contact.php'); ?>" class="w-full bg-slate-950/80 border border-slate-600 rounded-lg px-4 py-2.5 text-white focus:border-blue-500 outline-none">
                                    </label>
                                </div>
                            </section>

                            <section class="bg-slate-800/80 border border-slate-700 rounded-lg overflow-hidden">
                                <div class="px-5 py-4 border-b border-slate-700 bg-slate-900/40">
                                    <h2 class="text-white font-bold">Search Preview</h2>
                                    <p class="text-slate-500 text-xs mt-1">Metadata used by the About page browser title and description.</p>
                                </div>
                                <div class="p-5 grid md:grid-cols-2 gap-4">
                                    <label class="block">
                                        <span class="block text-slate-400 text-sm mb-2">SEO Title</span>
                                        <input type="text" name="seo_title" value="<?php echo htmlspecialchars($about['seo_title'] ?? ''); ?>" class="w-full bg-slate-950/80 border border-slate-600 rounded-lg px-4 py-2.5 text-white focus:border-blue-500 outline-none">
                                    </label>
                                    <label class="block">
                                        <span class="block text-slate-400 text-sm mb-2">SEO Description</span>
                                        <input type="text" name="seo_desc" value="<?php echo htmlspecialchars($about['seo_desc'] ?? ''); ?>" class="w-full bg-slate-950/80 border border-slate-600 rounded-lg px-4 py-2.5 text-white focus:border-blue-500 outline-none">
                                    </label>
                                </div>
                            </section>
                        </div>

                        <aside class="space-y-6">
                            <section class="bg-slate-800/80 border border-slate-700 rounded-lg overflow-hidden">
                                <div class="px-5 py-4 border-b border-slate-700 bg-slate-900/40">
                                    <h2 class="text-white font-bold">Portrait</h2>
                                </div>
                                <div class="p-5 space-y-4">
                                    <div class="flex items-center gap-4">
                                        <?php if (!empty($about['portrait_url'])): ?>
                                            <img src="../<?php echo htmlspecialchars($about['portrait_url']); ?>" class="w-20 h-20 rounded-lg object-cover border border-slate-600 bg-slate-950" alt="About portrait">
                                        <?php else: ?>
                                            <div class="w-20 h-20 rounded-lg bg-slate-950 border border-slate-700 flex items-center justify-center text-slate-500 font-serif text-3xl"><?php echo htmlspecialchars(strtoupper(substr(trim($about['profile_name'] ?? 'A'), 0, 1))); ?></div>
                                        <?php endif; ?>
                                        <div class="min-w-0">
                                            <p class="text-white font-semibold truncate"><?php echo htmlspecialchars($about['profile_name'] ?? ''); ?></p>
                                            <p class="text-slate-500 text-sm truncate"><?php echo htmlspecialchars($about['profile_title'] ?? ''); ?></p>
                                        </div>
                                    </div>
                                    <input type="file" name="portrait" accept="image/jpeg,image/png,image/webp" class="w-full bg-slate-950/80 border border-slate-600 rounded-lg px-3 py-2 text-sm text-white">
                                    <?php if (!empty($about['portrait_url'])): ?>
                                        <label class="inline-flex items-center gap-2 text-xs text-slate-400">
                                            <input type="checkbox" name="remove_portrait" value="1" class="rounded bg-slate-900 border-slate-600">
                                            Remove current portrait
                                        </label>
                                    <?php endif; ?>
                                    <p class="text-slate-500 text-xs">Use a square JPG, PNG, or WEBP image for best results.</p>
                                </div>
                            </section>

                            <section class="bg-slate-800/80 border border-slate-700 rounded-lg p-5">
                                <h2 class="text-white font-bold mb-3">Page Health</h2>
                                <div class="space-y-3 text-sm">
                                    <div class="flex justify-between gap-3"><span class="text-slate-500">Status</span><span class="<?php echo !empty($about['is_published']) ? 'text-green-400' : 'text-slate-400'; ?>"><?php echo !empty($about['is_published']) ? 'Published' : 'Hidden'; ?></span></div>
                                    <div class="flex justify-between gap-3"><span class="text-slate-500">Cards</span><span class="text-white"><?php echo count($items); ?></span></div>
                                    <div class="flex justify-between gap-3"><span class="text-slate-500">Updated</span><span class="text-slate-300 text-right"><?php echo htmlspecialchars($about['updated_at'] ?? 'Not saved'); ?></span></div>
                                </div>
                            </section>
                        </aside>
                    </form>

                    <div class="grid xl:grid-cols-[minmax(0,1fr)_390px] gap-6">
                        <section class="bg-slate-800/80 border border-slate-700 rounded-lg overflow-hidden">
                            <div class="px-5 py-4 border-b border-slate-700 bg-slate-900/40 flex items-center justify-between gap-4">
                                <div>
                                    <h2 class="text-white font-bold">Expertise Cards</h2>
                                    <p class="text-slate-500 text-xs mt-1">These cards appear in the middle of the public About page.</p>
                                </div>
                                <a href="about_manage.php" class="text-xs text-slate-400 hover:text-white">Clear form</a>
                            </div>
                            <div class="divide-y divide-slate-700/70">
                                <?php foreach ($items as $item): ?>
                                    <div class="p-5 flex flex-col md:flex-row md:items-center gap-4 hover:bg-slate-900/30 transition-colors">
                                        <div class="flex items-start gap-4 flex-1 min-w-0">
                                            <div class="w-11 h-11 rounded-lg bg-slate-950 border border-slate-700 flex items-center justify-center text-slate-300 font-bold shrink-0"><?php echo (int) $item['display_order']; ?></div>
                                            <div class="min-w-0">
                                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                                    <h3 class="text-white font-semibold"><?php echo htmlspecialchars($item['title']); ?></h3>
                                                    <span class="text-[10px] uppercase tracking-wider px-2 py-0.5 rounded border <?php echo !empty($item['is_active']) ? 'border-green-500/30 text-green-300 bg-green-500/10' : 'border-slate-600 text-slate-500 bg-slate-900'; ?>"><?php echo !empty($item['is_active']) ? 'Active' : 'Hidden'; ?></span>
                                                </div>
                                                <p class="text-slate-400 text-sm line-clamp-2"><?php echo htmlspecialchars($item['description']); ?></p>
                                                <p class="text-slate-600 text-xs mt-2">Icon: <?php echo htmlspecialchars($item['icon_key']); ?> · Color: <?php echo htmlspecialchars($item['accent_color']); ?></p>
                                            </div>
                                        </div>
                                        <div class="flex md:justify-end gap-2 shrink-0">
                                            <a href="about_manage.php?edit_item=<?php echo (int) $item['id']; ?>" class="px-3 py-2 bg-blue-600/15 hover:bg-blue-600/30 border border-blue-600/30 text-blue-300 rounded-lg text-xs font-bold transition-colors">Edit</a>
                                            <form method="POST" onsubmit="return confirm('Delete this expertise card?');">
                                                <input type="hidden" name="action" value="delete_item">
                                                <input type="hidden" name="id" value="<?php echo (int) $item['id']; ?>">
                                                <button type="submit" class="px-3 py-2 bg-red-600/15 hover:bg-red-600/30 border border-red-600/30 text-red-300 rounded-lg text-xs font-bold transition-colors">Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </section>

                        <form method="POST" class="bg-slate-800/80 border border-slate-700 rounded-lg p-5 space-y-4 h-fit">
                            <input type="hidden" name="action" value="save_item">
                            <input type="hidden" name="id" value="<?php echo (int) ($editItem['id'] ?? 0); ?>">
                            <div>
                                <h2 class="text-white font-bold text-lg"><?php echo $editItem ? 'Edit Expertise Card' : 'Add Expertise Card'; ?></h2>
                                <p class="text-slate-500 text-xs mt-1">Keep card copy short so the public layout stays balanced.</p>
                            </div>
                            <label class="block">
                                <span class="block text-slate-400 text-sm mb-2">Title</span>
                                <input type="text" name="title" required value="<?php echo htmlspecialchars($editItem['title'] ?? ''); ?>" class="w-full bg-slate-950/80 border border-slate-600 rounded-lg px-4 py-2.5 text-white focus:border-blue-500 outline-none">
                            </label>
                            <label class="block">
                                <span class="block text-slate-400 text-sm mb-2">Description</span>
                                <textarea name="description" rows="4" class="w-full bg-slate-950/80 border border-slate-600 rounded-lg px-4 py-3 text-white focus:border-blue-500 outline-none"><?php echo htmlspecialchars($editItem['description'] ?? ''); ?></textarea>
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="block">
                                    <span class="block text-slate-400 text-sm mb-2">Icon</span>
                                    <select name="icon_key" class="w-full bg-slate-950/80 border border-slate-600 rounded-lg px-3 py-2.5 text-white focus:border-blue-500 outline-none">
                                        <?php foreach (['home', 'currency', 'briefcase', 'chart', 'shield'] as $icon): ?>
                                            <option value="<?php echo $icon; ?>" <?php echo (($editItem['icon_key'] ?? '') === $icon) ? 'selected' : ''; ?>><?php echo ucfirst($icon); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <label class="block">
                                    <span class="block text-slate-400 text-sm mb-2">Color</span>
                                    <select name="accent_color" class="w-full bg-slate-950/80 border border-slate-600 rounded-lg px-3 py-2.5 text-white focus:border-blue-500 outline-none">
                                        <?php foreach (['blue', 'green', 'purple', 'gold', 'cyan'] as $color): ?>
                                            <option value="<?php echo $color; ?>" <?php echo (($editItem['accent_color'] ?? '') === $color) ? 'selected' : ''; ?>><?php echo ucfirst($color); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                            </div>
                            <div class="grid grid-cols-2 gap-3 items-end">
                                <label class="block">
                                    <span class="block text-slate-400 text-sm mb-2">Order</span>
                                    <input type="number" name="display_order" value="<?php echo (int) ($editItem['display_order'] ?? 0); ?>" class="w-full bg-slate-950/80 border border-slate-600 rounded-lg px-4 py-2.5 text-white focus:border-blue-500 outline-none">
                                </label>
                                <label class="inline-flex items-center gap-2 text-sm text-slate-300 pb-3">
                                    <input type="checkbox" name="is_active" value="1" <?php echo !isset($editItem) || !empty($editItem['is_active']) ? 'checked' : ''; ?> class="rounded bg-slate-900 border-slate-600 text-blue-600">
                                    Active
                                </label>
                            </div>
                            <div class="flex gap-3 pt-2">
                                <button type="submit" class="flex-1 px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg font-bold transition-colors"><?php echo $editItem ? 'Update Card' : 'Add Card'; ?></button>
                                <?php if ($editItem): ?><a href="about_manage.php" class="px-4 py-2.5 bg-slate-700 hover:bg-slate-600 text-white rounded-lg font-bold transition-colors">Cancel</a><?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>