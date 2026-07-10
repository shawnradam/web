<?php
require_once 'includes/admin_head.php';
require_once 'db_connect.php';

$menuImageFolders = [
    [
        'label' => 'Menu Folder',
        'disk_path' => __DIR__ . '/../assets/menu/',
        'url_prefix' => 'assets/menu/',
    ],
    [
        'label' => 'Uploaded Menu Images',
        'disk_path' => __DIR__ . '/../assets/uploads/menu/',
        'url_prefix' => 'assets/uploads/menu/',
    ],
];

function getMenuImageOptions(array $folders): array
{
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $images = [];

    foreach ($folders as $folder) {
        if (!is_dir($folder['disk_path'])) {
            continue;
        }

        foreach (scandir($folder['disk_path']) ?: [] as $fileName) {
            if ($fileName === '.' || $fileName === '..') {
                continue;
            }

            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            if (!in_array($extension, $allowedExtensions, true)) {
                continue;
            }

            $images[] = [
                'url' => $folder['url_prefix'] . $fileName,
                'name' => $fileName,
                'source' => $folder['label'],
            ];
        }
    }

    usort($images, static function ($a, $b) {
        return [$a['source'], $a['name']] <=> [$b['source'], $b['name']];
    });

    return $images;
}

function uniqueMenuImageName(string $directory, string $originalName): string
{
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $baseName = pathinfo($originalName, PATHINFO_FILENAME);
    $baseName = preg_replace('/[^a-zA-Z0-9_-]+/', '-', $baseName);
    $baseName = trim($baseName, '-_') ?: 'menu-image';
    $fileName = time() . '_menu_' . $baseName . '.' . $extension;
    $counter = 1;

    while (file_exists($directory . $fileName)) {
        $fileName = time() . '_menu_' . $baseName . '-' . $counter . '.' . $extension;
        $counter++;
    }

    return $fileName;
}

$menuImageOptions = getMenuImageOptions($menuImageFolders);
$allowedMenuImageUrls = array_column($menuImageOptions, 'url', 'url');

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // UPDATE SECTION
    if ($action === 'update_section') {
        $id = $_POST['section_id'];
        $label = $_POST['label'];
        $url = $_POST['url'];
        $desc = $_POST['description'];
        $imageUrl = null;
        $selectedExistingImage = trim($_POST['existing_image'] ?? '');

        if ($selectedExistingImage !== '' && isset($allowedMenuImageUrls[$selectedExistingImage])) {
            $imageUrl = $selectedExistingImage;
        }

        // PC uploads are saved into the managed menu folder so they become reusable later.
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $uploadFolder = $menuImageFolders[0];

            if (in_array($extension, $allowedExtensions, true)) {
                if (!is_dir($uploadFolder['disk_path'])) {
                    mkdir($uploadFolder['disk_path'], 0777, true);
                }

                $fileName = uniqueMenuImageName($uploadFolder['disk_path'], $_FILES['image']['name']);
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFolder['disk_path'] . $fileName)) {
                    $imageUrl = $uploadFolder['url_prefix'] . $fileName;
                }
            }
        }

        if ($imageUrl !== null) {
            $stmt = $pdo->prepare("UPDATE menu_sections SET label = ?, url = ?, description = ?, image_url = ? WHERE id = ?");
            $stmt->execute([$label, $url, $desc, $imageUrl, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE menu_sections SET label = ?, url = ?, description = ? WHERE id = ?");
            $stmt->execute([$label, $url, $desc, $id]);
        }
    }

    // ADD ITEM
    if ($action === 'add_item') {
        $section_id = $_POST['section_id'];
        $label = $_POST['label'];
        $url = $_POST['url'];
        $stmt = $pdo->prepare("INSERT INTO menu_items (section_id, label, url, display_order) VALUES (?, ?, ?, 99)");
        $stmt->execute([$section_id, $label, $url]);
    }

    // UPDATE ITEM
    if ($action === 'update_item') {
        $item_id = $_POST['item_id'];
        $label = $_POST['label'];
        $url = $_POST['url'];
        $stmt = $pdo->prepare("UPDATE menu_items SET label = ?, url = ? WHERE id = ?");
        $stmt->execute([$label, $url, $item_id]);
    }

    // DELETE ITEM
    if ($action === 'delete_item') {
        $item_id = $_POST['item_id'];
        $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
        $stmt->execute([$item_id]);
    }

    // ADD SECTION
    if ($action === 'add_section') {
        $label = $_POST['label'];
        $url = $_POST['url'] ?? '#';
        $description = $_POST['description'] ?? '';
        $stmt = $pdo->prepare("INSERT INTO menu_sections (label, url, description, display_order, is_active) VALUES (?, ?, ?, 99, 1)");
        $stmt->execute([$label, $url, $description]);
    }

    // DELETE SECTION
    if ($action === 'delete_section') {
        $section_id = $_POST['section_id'];
        $stmt = $pdo->prepare("DELETE FROM menu_items WHERE section_id = ?");
        $stmt->execute([$section_id]);
        $stmt = $pdo->prepare("DELETE FROM menu_sections WHERE id = ?");
        $stmt->execute([$section_id]);
    }

    header("Location: menu_manage.php");
    exit;
}

// Fetch Data
$sections = $pdo->query("SELECT * FROM menu_sections ORDER BY display_order ASC")->fetchAll();
$items = $pdo->query("SELECT * FROM menu_items ORDER BY display_order ASC")->fetchAll();

// Group Items by Section
$itemsBySection = [];
foreach ($items as $item) {
    $itemsBySection[$item['section_id']][] = $item;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php $pageTitle = 'Menu Management'; ?>
    <?php include 'includes/admin_head.php'; ?>
</head>

<body class="bg-navy-900 text-slate-300 font-sans antialiased selection:bg-gold-500 selection:text-white">
    <div class="flex min-h-screen relative" x-data="{
            sidebarOpen: false,
            collapsed: (function(){ try { return localStorage.getItem('sidebarCollapsed') === 'true' } catch(e){ return false } })()
        }" x-init="$watch('collapsed', val => { try { localStorage.setItem('sidebarCollapsed', val) } catch(e){} })">

        <?php include 'dashboard_sidebar_partial.php'; ?>

        <div class="flex-1 flex flex-col min-h-screen transition-all duration-300 ease-in-out md:ml-64 overflow-x-hidden"
            :class="collapsed ? 'md:!ml-20' : 'md:!ml-64'">

            <?php include 'includes/admin_header_partial.php'; ?>

            <main class="w-full flex-grow p-6 pb-24 md:pb-6 overflow-y-auto">
                <div class="max-w-7xl mx-auto">
                    <div class="flex justify-between items-end mb-8">
                        <div>
                            <h1 class="text-3xl font-serif text-white mb-2">Navigation Menu</h1>
                            <p class="text-slate-400">Manage your site's main menu, dropdowns, and mobile cards.</p>
                        </div>
                        <button onclick="document.getElementById('addSectionForm').classList.toggle('hidden')"
                            class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2 rounded font-bold uppercase text-sm">
                            + Add Section
                        </button>
                    </div>

                    <div id="addSectionForm" class="hidden mb-8 bg-slate-800 rounded-xl border border-slate-700 p-6">
                        <h3 class="text-white font-bold mb-4">Add New Menu Section</h3>
                        <form method="POST" class="space-y-4">
                            <input type="hidden" name="action" value="add_section">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-slate-500 text-xs uppercase mb-1">Section Label *</label>
                                    <input type="text" name="label" required
                                        class="w-full bg-slate-900 border border-slate-600 rounded px-3 py-2 text-white"
                                        placeholder="e.g., Services">
                                </div>
                                <div>
                                    <label class="block text-slate-500 text-xs uppercase mb-1">Main Link URL</label>
                                    <input type="text" name="url"
                                        class="w-full bg-slate-900 border border-slate-600 rounded px-3 py-2 text-white"
                                        placeholder="#">
                                </div>
                            </div>
                            <div>
                                <label class="block text-slate-500 text-xs uppercase mb-1">Description</label>
                                <textarea name="description" rows="2"
                                    class="w-full bg-slate-900 border border-slate-600 rounded px-3 py-2 text-white"
                                    placeholder="Optional description for mega menu"></textarea>
                            </div>
                            <div class="flex gap-3">
                                <button type="submit"
                                    class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2 rounded font-bold uppercase text-sm">
                                    Create Section
                                </button>
                                <button type="button"
                                    onclick="document.getElementById('addSectionForm').classList.add('hidden')"
                                    class="bg-slate-700 hover:bg-slate-600 text-white px-6 py-2 rounded font-bold uppercase text-sm">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="space-y-8">
                        <?php foreach ($sections as $section): ?>
                            <?php
                            $previewImage = $section['image_url'] ? '../' . $section['image_url'] : '';
                            $previewId = 'menuPreview_' . (int) $section['id'];
                            ?>
                            <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden"
                                x-data="{ expanded: false }">

                                <div class="p-6 bg-slate-800/50">
                                    <form method="POST" enctype="multipart/form-data" class="space-y-4">
                                        <input type="hidden" name="action" value="update_section">
                                        <input type="hidden" name="section_id" value="<?php echo (int) $section['id']; ?>">

                                        <div class="flex flex-col md:flex-row gap-6">
                                            <div class="w-full md:w-1/4">
                                                <div class="aspect-video rounded-lg overflow-hidden bg-slate-900 border border-slate-600 relative">
                                                    <?php if ($previewImage): ?>
                                                        <img id="<?php echo $previewId; ?>"
                                                            src="<?php echo htmlspecialchars($previewImage); ?>"
                                                            class="w-full h-full object-cover">
                                                    <?php else: ?>
                                                        <div id="<?php echo $previewId; ?>_empty"
                                                            class="w-full h-full flex items-center justify-center text-slate-600">
                                                            No Image
                                                        </div>
                                                        <img id="<?php echo $previewId; ?>" src="" class="hidden w-full h-full object-cover">
                                                    <?php endif; ?>
                                                </div>
                                                <p class="text-xs text-slate-500 mt-2 text-center">Menu Preview / Mobile Card BG</p>
                                            </div>

                                            <div class="flex-1 space-y-4">
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div>
                                                        <label class="block text-slate-500 text-xs uppercase mb-1">Label</label>
                                                        <input type="text" name="label"
                                                            value="<?php echo htmlspecialchars($section['label']); ?>"
                                                            class="w-full bg-slate-900 border border-slate-600 rounded px-3 py-2 text-white">
                                                    </div>
                                                    <div>
                                                        <label class="block text-slate-500 text-xs uppercase mb-1">Main Link URL</label>
                                                        <input type="text" name="url"
                                                            value="<?php echo htmlspecialchars($section['url']); ?>"
                                                            class="w-full bg-slate-900 border border-slate-600 rounded px-3 py-2 text-white">
                                                    </div>
                                                </div>

                                                <div>
                                                    <label class="block text-slate-500 text-xs uppercase mb-1">Description (Mega Menu Text)</label>
                                                    <textarea name="description" rows="2"
                                                        class="w-full bg-slate-900 border border-slate-600 rounded px-3 py-2 text-white"><?php echo htmlspecialchars($section['description']); ?></textarea>
                                                </div>

                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div>
                                                        <label class="block text-slate-500 text-xs uppercase mb-1">Choose From Menu Folder</label>
                                                        <select name="existing_image"
                                                            class="w-full bg-slate-900 border border-slate-600 rounded px-3 py-2 text-white"
                                                            onchange="previewExistingMenuImage(this, '<?php echo $previewId; ?>')">
                                                            <option value="">Keep current image</option>
                                                            <?php if (empty($menuImageOptions)): ?>
                                                                <option value="" disabled>No images found in assets/menu</option>
                                                            <?php else: ?>
                                                                <?php foreach ($menuImageOptions as $image): ?>
                                                                    <option value="<?php echo htmlspecialchars($image['url']); ?>"
                                                                        data-preview="../<?php echo htmlspecialchars($image['url']); ?>"
                                                                        <?php echo $section['image_url'] === $image['url'] ? 'selected' : ''; ?>>
                                                                        <?php echo htmlspecialchars($image['source'] . ' | ' . $image['name']); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            <?php endif; ?>
                                                        </select>
                                                        <p class="text-[11px] text-slate-500 mt-1">Detected reusable images from assets/menu and assets/uploads/menu.</p>
                                                    </div>
                                                    <div>
                                                        <label class="block text-slate-500 text-xs uppercase mb-1">Or Upload From PC</label>
                                                        <input type="file" name="image" accept="image/png,image/jpeg,image/webp,image/gif"
                                                            class="w-full text-sm text-slate-300 file:mr-4 file:rounded file:border-0 file:bg-blue-600 file:px-4 file:py-2 file:text-sm file:font-bold file:uppercase file:text-white hover:file:bg-blue-500"
                                                            onchange="previewUploadedMenuImage(this, '<?php echo $previewId; ?>')">
                                                        <p class="text-[11px] text-slate-500 mt-1">New PC uploads are saved into assets/menu for reuse.</p>
                                                    </div>
                                                </div>

                                                <div class="flex flex-col md:flex-row justify-between gap-3 pt-2">
                                                    <button type="submit"
                                                        class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded text-sm font-bold uppercase">
                                                        Save Section
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>

                                    <form method="POST" onsubmit="return confirm('Delete this entire menu section and all its sub-links?');"
                                        class="mt-4">
                                        <input type="hidden" name="action" value="delete_section">
                                        <input type="hidden" name="section_id" value="<?php echo (int) $section['id']; ?>">
                                        <button type="submit"
                                            class="text-red-500 hover:text-red-400 text-sm font-bold uppercase flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            Delete Section
                                        </button>
                                    </form>
                                </div>

                                <div class="border-t border-slate-700">
                                    <button @click="expanded = !expanded"
                                        class="w-full py-2 px-6 flex items-center justify-between text-slate-400 hover:text-white hover:bg-slate-700/50 transition-colors text-sm">
                                        <span>Manage Sub-Links (<?php echo isset($itemsBySection[$section['id']]) ? count($itemsBySection[$section['id']]) : 0; ?>)</span>
                                        <svg class="w-4 h-4 transform transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>

                                    <div x-show="expanded" x-cloak class="p-6 bg-slate-900/50 space-y-3">
                                        <?php if (isset($itemsBySection[$section['id']])): ?>
                                            <?php foreach ($itemsBySection[$section['id']] as $item): ?>
                                                <div class="bg-slate-800 p-3 rounded border border-slate-700" x-data="{ editing: false }">
                                                    <div x-show="!editing" class="flex items-center gap-3">
                                                        <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                                            <div class="text-white font-bold"><?php echo htmlspecialchars($item['label']); ?></div>
                                                            <div class="text-slate-400 text-xs font-mono truncate"><?php echo htmlspecialchars($item['url']); ?></div>
                                                        </div>
                                                        <button type="button" @click="editing = true" class="text-blue-400 hover:text-blue-300 p-2" title="Edit">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                            </svg>
                                                        </button>
                                                        <form method="POST" onsubmit="return confirm('Delete this link?');" class="inline">
                                                            <input type="hidden" name="action" value="delete_item">
                                                            <input type="hidden" name="item_id" value="<?php echo (int) $item['id']; ?>">
                                                            <button type="submit" class="text-red-500 hover:text-red-400 p-2" title="Delete">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                                </svg>
                                                            </button>
                                                        </form>
                                                    </div>

                                                    <form x-show="editing" x-cloak method="POST" class="flex flex-col md:flex-row items-center gap-3">
                                                        <input type="hidden" name="action" value="update_item">
                                                        <input type="hidden" name="item_id" value="<?php echo (int) $item['id']; ?>">
                                                        <input type="text" name="label" value="<?php echo htmlspecialchars($item['label']); ?>" required
                                                            class="w-full flex-1 bg-slate-900 border border-slate-600 rounded px-3 py-2 text-white text-sm">
                                                        <input type="text" name="url" value="<?php echo htmlspecialchars($item['url']); ?>" required
                                                            class="w-full flex-1 bg-slate-900 border border-slate-600 rounded px-3 py-2 text-white text-sm">
                                                        <div class="flex gap-2">
                                                            <button type="submit" class="text-green-400 hover:text-green-300 p-2" title="Save">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                                </svg>
                                                            </button>
                                                            <button type="button" @click="editing = false" class="text-slate-400 hover:text-slate-300 p-2" title="Cancel">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>

                                        <form method="POST" class="flex flex-col md:flex-row items-center gap-3 mt-4 pt-4 border-t border-slate-700 border-dashed">
                                            <input type="hidden" name="action" value="add_item">
                                            <input type="hidden" name="section_id" value="<?php echo (int) $section['id']; ?>">
                                            <input type="text" name="label" placeholder="New Link Label" required
                                                class="w-full flex-1 bg-slate-900 border border-slate-600 rounded px-3 py-2 text-white text-sm">
                                            <input type="text" name="url" placeholder="URL (e.g. page.php)" required
                                                class="w-full flex-1 bg-slate-900 border border-slate-600 rounded px-3 py-2 text-white text-sm">
                                            <button type="submit"
                                                class="bg-gold-500 hover:bg-gold-400 text-navy-900 font-bold px-4 py-2 rounded text-sm uppercase">Add</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        function previewExistingMenuImage(select, previewId) {
            const option = select.options[select.selectedIndex];
            const previewPath = option ? option.getAttribute('data-preview') : '';
            const preview = document.getElementById(previewId);
            const empty = document.getElementById(previewId + '_empty');

            if (!previewPath || !preview) return;

            preview.src = previewPath;
            preview.classList.remove('hidden');
            if (empty) empty.classList.add('hidden');
        }

        function previewUploadedMenuImage(input, previewId) {
            if (!input.files || !input.files[0]) return;

            const preview = document.getElementById(previewId);
            const empty = document.getElementById(previewId + '_empty');
            if (!preview) return;

            preview.src = URL.createObjectURL(input.files[0]);
            preview.classList.remove('hidden');
            if (empty) empty.classList.add('hidden');
        }
    </script>
</body>

</html>