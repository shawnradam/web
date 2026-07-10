<?php
require_once 'db_connect.php';
require_once 'auth_check.php';

function landing_manage_redirect($params = [])
{
    $query = $params ? '?' . http_build_query($params) : '';
    header('Location: landing_pages_manage.php' . $query);
    exit;
}

function landing_manage_upload($field, $currentPath = '')
{
    if (empty($_FILES[$field]['name']) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        return $currentPath;
    }

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $tmp = $_FILES[$field]['tmp_name'];
    $mime = mime_content_type($tmp);
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Landing image must be JPG, PNG, or WEBP.');
    }

    $uploadDir = __DIR__ . '/../assets/uploads/landing/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $safeBase = preg_replace('/[^a-z0-9_-]+/i', '-', pathinfo($_FILES[$field]['name'], PATHINFO_FILENAME));
    $fileName = time() . '_landing_' . trim($safeBase, '-_') . '.' . $allowed[$mime];
    $target = $uploadDir . $fileName;
    if (!move_uploaded_file($tmp, $target)) {
        throw new RuntimeException('Unable to upload landing image.');
    }

    return 'assets/uploads/landing/' . $fileName;
}

function landing_manage_image_library()
{
    $roots = [
        'assets/landing' => __DIR__ . '/../assets/landing',
        'assets/uploads/landing' => __DIR__ . '/../assets/uploads/landing',
    ];
    $images = [];
    foreach ($roots as $publicRoot => $diskRoot) {
        if (!is_dir($diskRoot)) {
            @mkdir($diskRoot, 0777, true);
        }
        $files = is_dir($diskRoot) ? (scandir($diskRoot) ?: []) : [];
        foreach ($files as $name) {
            if (!preg_match('/\.(jpe?g|png|webp)$/i', $name)) {
                continue;
            }
            $file = rtrim($diskRoot, '/\\') . DIRECTORY_SEPARATOR . $name;
            if (!is_file($file)) {
                continue;
            }
            $images[] = [
                'path' => $publicRoot . '/' . $name,
                'name' => $name,
                'size' => filesize($file) ?: 0,
                'updated' => filemtime($file) ?: 0,
            ];
        }
    }
    usort($images, function ($a, $b) {
        return ($b['updated'] <=> $a['updated']) ?: strcmp($a['name'], $b['name']);
    });
    return $images;
}

function landing_manage_existing_image($path, $library)
{
    $path = trim((string) $path);
    if ($path === '') {
        return '';
    }
    foreach ($library as $image) {
        if ($image['path'] === $path) {
            return $path;
        }
    }
    throw new RuntimeException('Selected hosted image was not found in the landing image folders.');
}

function landing_blank_record()
{
    return [
        'id' => 0,
        'slug' => '',
        'page_title' => '',
        'eyebrow' => '',
        'hero_title' => '',
        'hero_subtitle' => '',
        'location' => '',
        'lot_size' => '',
        'price' => '',
        'availability' => '',
        'image_url' => '',
        'intro_text' => '',
        'highlights' => '',
        'facilities' => '',
        'map_notes' => '',
        'cta_heading' => '',
        'cta_text' => 'Hubungi Sekarang',
        'cta_link' => 'contact.php',
        'seo_title' => '',
        'seo_desc' => '',
        'is_published' => 1,
    ];
}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS landing_pages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        slug VARCHAR(150) NOT NULL UNIQUE,
        page_title VARCHAR(255) NOT NULL,
        eyebrow VARCHAR(120) DEFAULT NULL,
        hero_title VARCHAR(255) NOT NULL,
        hero_subtitle TEXT,
        location VARCHAR(180) DEFAULT NULL,
        lot_size VARCHAR(80) DEFAULT NULL,
        price VARCHAR(80) DEFAULT NULL,
        availability VARCHAR(160) DEFAULT NULL,
        image_url VARCHAR(255) DEFAULT NULL,
        intro_text TEXT,
        highlights TEXT,
        facilities TEXT,
        map_notes TEXT,
        cta_heading VARCHAR(255) DEFAULT NULL,
        cta_text VARCHAR(120) DEFAULT NULL,
        cta_link VARCHAR(255) DEFAULT NULL,
        seo_title VARCHAR(255) DEFAULT NULL,
        seo_desc VARCHAR(255) DEFAULT NULL,
        is_published TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
} catch (Throwable $e) {}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = $_POST['action'] ?? 'save';

    try {
        if ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                $stmt = $pdo->prepare('DELETE FROM landing_pages WHERE id = ?');
                $stmt->execute([$id]);
            }
            landing_manage_redirect(['status' => 'deleted']);
        }

        $id = (int) ($_POST['id'] ?? 0);
        $slug = strtolower(trim((string) ($_POST['slug'] ?? '')));
        $slug = preg_replace('/[^a-z0-9_-]+/', '-', $slug);
        $slug = trim($slug, '-_');
        if ($slug === '') {
            throw new RuntimeException('Slug is required.');
        }

        $imageLibrary = landing_manage_image_library();
        $currentImage = $_POST['current_image_url'] ?? '';
        $selectedImage = landing_manage_existing_image($_POST['selected_image_url'] ?? '', $imageLibrary);
        $imageUrl = $selectedImage !== '' ? $selectedImage : $currentImage;
        $imageUrl = landing_manage_upload('image', $imageUrl);
        if (isset($_POST['remove_image'])) {
            $imageUrl = '';
        }

        $values = [
            $slug,
            trim($_POST['page_title'] ?? ''),
            trim($_POST['eyebrow'] ?? ''),
            trim($_POST['hero_title'] ?? ''),
            trim($_POST['hero_subtitle'] ?? ''),
            trim($_POST['location'] ?? ''),
            trim($_POST['lot_size'] ?? ''),
            trim($_POST['price'] ?? ''),
            trim($_POST['availability'] ?? ''),
            $imageUrl,
            trim($_POST['intro_text'] ?? ''),
            trim($_POST['highlights'] ?? ''),
            trim($_POST['facilities'] ?? ''),
            trim($_POST['map_notes'] ?? ''),
            trim($_POST['cta_heading'] ?? ''),
            trim($_POST['cta_text'] ?? ''),
            trim($_POST['cta_link'] ?? ''),
            trim($_POST['seo_title'] ?? ''),
            trim($_POST['seo_desc'] ?? ''),
            isset($_POST['is_published']) ? 1 : 0,
        ];

        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE landing_pages SET slug = ?, page_title = ?, eyebrow = ?, hero_title = ?, hero_subtitle = ?, location = ?, lot_size = ?, price = ?, availability = ?, image_url = ?, intro_text = ?, highlights = ?, facilities = ?, map_notes = ?, cta_heading = ?, cta_text = ?, cta_link = ?, seo_title = ?, seo_desc = ?, is_published = ? WHERE id = ?");
            $stmt->execute([...$values, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO landing_pages (slug, page_title, eyebrow, hero_title, hero_subtitle, location, lot_size, price, availability, image_url, intro_text, highlights, facilities, map_notes, cta_heading, cta_text, cta_link, seo_title, seo_desc, is_published) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute($values);
            $id = (int) $pdo->lastInsertId();
        }

        landing_manage_redirect(['id' => $id, 'status' => 'saved']);
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$pages = [];
try {
    $pages = $pdo->query("SELECT id, slug, page_title, price, location, is_published, updated_at FROM landing_pages ORDER BY updated_at DESC, id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {}

$isNew = isset($_GET['new']) || empty($pages);
$selectedId = (int) ($_GET['id'] ?? 0);
if (!$isNew && $selectedId <= 0 && !empty($pages)) {
    foreach ($pages as $page) {
        if ($page['slug'] === 'tanah-lot-selupoh') {
            $selectedId = (int) $page['id'];
            break;
        }
    }
    if ($selectedId <= 0) {
        $selectedId = (int) $pages[0]['id'];
    }
}

$landing = landing_blank_record();
if (!$isNew && $selectedId > 0) {
    try {
        $stmt = $pdo->prepare('SELECT * FROM landing_pages WHERE id = ? LIMIT 1');
        $stmt->execute([$selectedId]);
        $found = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($found) {
            $landing = array_merge($landing, $found);
        }
    } catch (Throwable $e) {}
}

$imageLibrary = landing_manage_image_library();

$publicUrl = '../landing.php?slug=' . rawurlencode($landing['slug']);
if ($landing['slug'] === 'tanah-lot-selupoh') {
    $publicUrl = '../tanah-lot-selupoh.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = 'TLS Landing Pages'; ?>
    <?php include 'includes/admin_head.php'; ?>
</head>
<body class="bg-slate-900 text-slate-300 antialiased">
<div class="flex min-h-screen relative" x-data="{ sidebarOpen: false, collapsed: (function(){ try { return localStorage.getItem('sidebarCollapsed') === 'true' } catch(e){ return false } })() }" x-init="$watch('collapsed', val => { try { localStorage.setItem('sidebarCollapsed', val) } catch(e){} })">
    <?php include 'dashboard_sidebar_partial.php'; ?>
    <div class="flex-1 flex flex-col min-h-screen transition-all duration-300 ease-in-out md:ml-64 overflow-x-hidden" :class="collapsed ? 'md:!ml-20' : 'md:!ml-64'">
        <?php include 'includes/admin_header_partial.php'; ?>
        <main class="flex-1 p-4 sm:p-6 lg:p-8 pb-24 md:pb-8 overflow-y-auto">
            <div class="max-w-7xl mx-auto">
                <div class="mb-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-serif text-white mb-2">TLS Landing Pages</h1>
                        <p class="text-slate-400 text-sm">Manage Tanah Lot Sabah landing pages. Selupoh is the first page; add more pages later with different slugs and images.</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <?php if (!$isNew && !empty($landing['slug'])): ?>
                            <a href="<?php echo htmlspecialchars($publicUrl); ?>" target="_blank" class="inline-flex items-center justify-center border border-slate-600 hover:border-gold-500 text-white px-4 py-2 text-xs font-bold uppercase tracking-wider">View Page</a>
                        <?php endif; ?>
                        <a href="landing_pages_manage.php?new=1" class="inline-flex items-center justify-center bg-gold-500 hover:bg-gold-400 text-navy-900 px-4 py-2 text-xs font-bold uppercase tracking-wider">Add Landing Page</a>
                    </div>
                </div>

                <?php if (isset($_GET['status']) && $_GET['status'] === 'saved'): ?>
                    <div class="mb-6 bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 px-4 py-3">Landing page saved successfully.</div>
                <?php elseif (isset($_GET['status']) && $_GET['status'] === 'deleted'): ?>
                    <div class="mb-6 bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 px-4 py-3">Landing page deleted.</div>
                <?php endif; ?>
                <?php if (!empty($error)): ?>
                    <div class="mb-6 bg-red-500/10 border border-red-500/30 text-red-300 px-4 py-3"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <div class="grid xl:grid-cols-[320px_minmax(0,1fr)] gap-6">
                    <aside class="bg-slate-800/80 border border-slate-700 p-4 h-fit">
                        <h2 class="font-serif text-xl text-white mb-4">Landing Pages</h2>
                        <div class="space-y-2">
                            <?php if (empty($pages)): ?>
                                <p class="text-sm text-slate-500">No landing pages yet.</p>
                            <?php endif; ?>
                            <?php foreach ($pages as $page): ?>
                                <a href="landing_pages_manage.php?id=<?php echo (int) $page['id']; ?>" class="block border p-3 transition-colors <?php echo (int) $landing['id'] === (int) $page['id'] ? 'border-gold-500 bg-gold-500/10' : 'border-slate-700 bg-slate-950/50 hover:border-slate-600'; ?>">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="font-semibold text-white truncate"><?php echo htmlspecialchars($page['page_title']); ?></p>
                                        <span class="text-[10px] uppercase tracking-wider <?php echo (int) $page['is_published'] === 1 ? 'text-emerald-400' : 'text-slate-500'; ?>"><?php echo (int) $page['is_published'] === 1 ? 'Live' : 'Draft'; ?></span>
                                    </div>
                                    <p class="text-xs text-slate-500 truncate mt-1">/<?php echo htmlspecialchars($page['slug']); ?></p>
                                    <p class="text-xs text-gold-500 mt-2"><?php echo htmlspecialchars(trim(($page['price'] ?? '') . ' ' . ($page['location'] ?? ''))); ?></p>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </aside>

                    <form method="POST" enctype="multipart/form-data" class="grid xl:grid-cols-[minmax(0,1fr)_360px] gap-6">
                        <input type="hidden" name="id" value="<?php echo (int) $landing['id']; ?>">
                        <input type="hidden" name="current_image_url" value="<?php echo htmlspecialchars($landing['image_url']); ?>">

                        <section class="bg-slate-800/80 border border-slate-700 p-5 sm:p-6 space-y-6 min-w-0">
                            <div class="grid md:grid-cols-2 gap-5">
                                <label class="block"><span class="block text-sm text-slate-400 mb-2">Slug</span><input name="slug" value="<?php echo htmlspecialchars($landing['slug']); ?>" placeholder="example: tanah-lot-tuaran" class="w-full bg-slate-950 border border-slate-700 px-4 py-3 text-white outline-none focus:border-gold-500"></label>
                                <label class="block"><span class="block text-sm text-slate-400 mb-2">Eyebrow</span><input name="eyebrow" value="<?php echo htmlspecialchars($landing['eyebrow']); ?>" class="w-full bg-slate-950 border border-slate-700 px-4 py-3 text-white outline-none focus:border-gold-500"></label>
                            </div>
                            <label class="block"><span class="block text-sm text-slate-400 mb-2">Page Title</span><input name="page_title" value="<?php echo htmlspecialchars($landing['page_title']); ?>" class="w-full bg-slate-950 border border-slate-700 px-4 py-3 text-white outline-none focus:border-gold-500"></label>
                            <label class="block"><span class="block text-sm text-slate-400 mb-2">Hero Title</span><input name="hero_title" value="<?php echo htmlspecialchars($landing['hero_title']); ?>" class="w-full bg-slate-950 border border-slate-700 px-4 py-3 text-white outline-none focus:border-gold-500"></label>
                            <label class="block"><span class="block text-sm text-slate-400 mb-2">Hero Subtitle</span><textarea name="hero_subtitle" rows="3" class="w-full bg-slate-950 border border-slate-700 px-4 py-3 text-white outline-none focus:border-gold-500"><?php echo htmlspecialchars($landing['hero_subtitle']); ?></textarea></label>
                            <div class="grid md:grid-cols-4 gap-5">
                                <label class="block"><span class="block text-sm text-slate-400 mb-2">Location</span><input name="location" value="<?php echo htmlspecialchars($landing['location']); ?>" class="w-full bg-slate-950 border border-slate-700 px-4 py-3 text-white outline-none focus:border-gold-500"></label>
                                <label class="block"><span class="block text-sm text-slate-400 mb-2">Lot Size</span><input name="lot_size" value="<?php echo htmlspecialchars($landing['lot_size']); ?>" class="w-full bg-slate-950 border border-slate-700 px-4 py-3 text-white outline-none focus:border-gold-500"></label>
                                <label class="block"><span class="block text-sm text-slate-400 mb-2">Price</span><input name="price" value="<?php echo htmlspecialchars($landing['price']); ?>" class="w-full bg-slate-950 border border-slate-700 px-4 py-3 text-white outline-none focus:border-gold-500"></label>
                                <label class="block"><span class="block text-sm text-slate-400 mb-2">Availability</span><input name="availability" value="<?php echo htmlspecialchars($landing['availability']); ?>" class="w-full bg-slate-950 border border-slate-700 px-4 py-3 text-white outline-none focus:border-gold-500"></label>
                            </div>
                            <label class="block"><span class="block text-sm text-slate-400 mb-2">Intro Text</span><textarea name="intro_text" rows="4" class="w-full bg-slate-950 border border-slate-700 px-4 py-3 text-white outline-none focus:border-gold-500"><?php echo htmlspecialchars($landing['intro_text']); ?></textarea></label>
                            <div class="grid md:grid-cols-2 gap-5">
                                <label class="block"><span class="block text-sm text-slate-400 mb-2">Highlights (one per line)</span><textarea name="highlights" rows="7" class="w-full bg-slate-950 border border-slate-700 px-4 py-3 text-white outline-none focus:border-gold-500"><?php echo htmlspecialchars($landing['highlights']); ?></textarea></label>
                                <label class="block"><span class="block text-sm text-slate-400 mb-2">Facilities (one per line)</span><textarea name="facilities" rows="7" class="w-full bg-slate-950 border border-slate-700 px-4 py-3 text-white outline-none focus:border-gold-500"><?php echo htmlspecialchars($landing['facilities']); ?></textarea></label>
                            </div>
                            <label class="block"><span class="block text-sm text-slate-400 mb-2">Map / Location Notes</span><textarea name="map_notes" rows="3" class="w-full bg-slate-950 border border-slate-700 px-4 py-3 text-white outline-none focus:border-gold-500"><?php echo htmlspecialchars($landing['map_notes']); ?></textarea></label>
                            <div class="grid md:grid-cols-3 gap-5">
                                <label class="block"><span class="block text-sm text-slate-400 mb-2">CTA Heading</span><input name="cta_heading" value="<?php echo htmlspecialchars($landing['cta_heading']); ?>" class="w-full bg-slate-950 border border-slate-700 px-4 py-3 text-white outline-none focus:border-gold-500"></label>
                                <label class="block"><span class="block text-sm text-slate-400 mb-2">CTA Text</span><input name="cta_text" value="<?php echo htmlspecialchars($landing['cta_text']); ?>" class="w-full bg-slate-950 border border-slate-700 px-4 py-3 text-white outline-none focus:border-gold-500"></label>
                                <label class="block"><span class="block text-sm text-slate-400 mb-2">CTA Link</span><input name="cta_link" value="<?php echo htmlspecialchars($landing['cta_link']); ?>" class="w-full bg-slate-950 border border-slate-700 px-4 py-3 text-white outline-none focus:border-gold-500"></label>
                            </div>
                            <div class="grid md:grid-cols-2 gap-5">
                                <label class="block"><span class="block text-sm text-slate-400 mb-2">SEO Title</span><input name="seo_title" value="<?php echo htmlspecialchars($landing['seo_title']); ?>" class="w-full bg-slate-950 border border-slate-700 px-4 py-3 text-white outline-none focus:border-gold-500"></label>
                                <label class="block"><span class="block text-sm text-slate-400 mb-2">SEO Description</span><input name="seo_desc" value="<?php echo htmlspecialchars($landing['seo_desc']); ?>" class="w-full bg-slate-950 border border-slate-700 px-4 py-3 text-white outline-none focus:border-gold-500"></label>
                            </div>
                        </section>

                        <aside class="space-y-6">
                            <section class="bg-slate-800/80 border border-slate-700 p-5 space-y-5">
                                <div>
                                    <h2 class="font-serif text-xl text-white">Landing Image</h2>
                                    <p class="text-xs text-slate-500 mt-1">Choose an image already on the hosting first, or upload another image below.</p>
                                </div>
                                <?php if (!empty($landing['image_url'])): ?>
                                    <div class="bg-slate-950 border border-slate-700 p-2">
                                        <img src="../<?php echo htmlspecialchars($landing['image_url']); ?>" class="w-full h-auto object-contain" alt="Current landing image">
                                        <p class="mt-2 text-[11px] text-slate-500 break-all"><?php echo htmlspecialchars($landing['image_url']); ?></p>
                                    </div>
                                <?php endif; ?>

                                <div class="space-y-3">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="text-sm font-semibold text-white">Choose From Hosting</p>
                                        <span class="text-[11px] uppercase tracking-wider text-slate-500"><?php echo count($imageLibrary); ?> files</span>
                                    </div>
                                    <?php if (empty($imageLibrary)): ?>
                                        <div class="border border-dashed border-slate-600 bg-slate-950/60 p-4 text-sm text-slate-500">No hosted landing images found yet. Upload one below to create the first file.</div>
                                    <?php else: ?>
                                        <div class="max-h-80 overflow-y-auto pr-1 grid grid-cols-2 gap-3">
                                            <?php foreach ($imageLibrary as $image): ?>
                                                <?php $isSelectedImage = $landing['image_url'] === $image['path']; ?>
                                                <label class="group cursor-pointer border p-2 transition-colors <?php echo $isSelectedImage ? 'border-gold-500 bg-gold-500/10' : 'border-slate-700 bg-slate-950/60 hover:border-slate-500'; ?>">
                                                    <input type="radio" name="selected_image_url" value="<?php echo htmlspecialchars($image['path']); ?>" class="sr-only peer" <?php echo $isSelectedImage ? 'checked' : ''; ?>>
                                                    <img src="../<?php echo htmlspecialchars($image['path']); ?>" alt="<?php echo htmlspecialchars($image['name']); ?>" class="aspect-[4/5] w-full object-cover bg-slate-900 border border-slate-800 peer-checked:border-gold-500">
                                                    <span class="mt-2 block text-[11px] text-slate-400 break-all group-hover:text-white"><?php echo htmlspecialchars($image['name']); ?></span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="border-t border-slate-700 pt-4 space-y-3">
                                    <p class="text-sm font-semibold text-white">Upload Another Image</p>
                                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm text-slate-300 file:mr-4 file:border-0 file:bg-gold-500 file:text-navy-900 file:px-4 file:py-2 file:font-bold">
                                    <p class="text-xs text-slate-500">New uploads are saved into <span class="text-slate-300">assets/uploads/landing</span> and will appear in the hosting list next time.</p>
                                </div>

                                <label class="flex items-center gap-2 text-sm text-slate-400"><input type="checkbox" name="remove_image" value="1" class="accent-gold-500"> Remove current image</label>
                            </section>
                            <section class="bg-slate-800/80 border border-slate-700 p-5 space-y-4">
                                <label class="flex items-center gap-3 text-white"><input type="checkbox" name="is_published" value="1" class="accent-gold-500" <?php echo (int) $landing['is_published'] === 1 ? 'checked' : ''; ?>> Published</label>
                                <button type="submit" class="w-full bg-gold-500 hover:bg-gold-400 text-navy-900 px-5 py-3 text-xs font-bold uppercase tracking-wider">Save Landing Page</button>
                                <?php if ((int) $landing['id'] > 0): ?>
                                    <button type="submit" name="action" value="delete" onclick="return confirm('Delete this landing page?');" class="w-full border border-red-500/50 text-red-300 hover:bg-red-500/10 px-5 py-3 text-xs font-bold uppercase tracking-wider">Delete Landing Page</button>
                                <?php endif; ?>
                            </section>
                        </aside>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>