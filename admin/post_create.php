<?php
// admin/post_create.php
require_once 'auth_check.php';
require_once 'db_connect.php';

$message = '';
$action = 'create';
$postData = [
    'title' => '',
    'slug' => '',
    'summary' => '',
    'content' => '',
    'image_url' => '',
    'category_id' => '',
    'tags' => '',
    'status' => 'draft',
    'is_nofollow' => 0,
    'seo_title' => '',
    'seo_desc' => ''
];

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postData = $_POST;

    // Auto-generate slug if empty
    if (empty($postData['slug'])) {
        $postData['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $postData['title'])));
    }

    // Handle Image Upload
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image_file']['tmp_name'];
        $fileName = $_FILES['image_file']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Sanitize filename
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;

        // Directory Structure: assets/uploads/{MonthName}/{DD-HHmm}/
        $monthDir = date('F');
        $dateTimeDir = date('d-Hi');

        // Determine upload path relative to admin folder (Use absolute path for reliability in XAMPP)
        // Root is advisor
        $uploadBaseDir = '../assets/uploads/';
        $targetDir = $uploadBaseDir . $monthDir . '/' . $dateTimeDir . '/';

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $dest_path = $targetDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            // Store DB path (relative to advisor root, not admin)
            // Since admin is in /admin/, and upload is in ../assets, 
            // the frontend link (from root) should be assets/uploads/...
            $postData['image_url'] = 'assets/uploads/' . $monthDir . '/' . $dateTimeDir . '/' . $newFileName;
        } else {
            $message = "Error moving uploaded file. Check permissions.";
        }
    }

    try {
        if (isset($_POST['action']) && $_POST['action'] === 'update') {
            // UPDATE Logic
            $sql = "UPDATE posts SET title=:title, slug=:slug, summary=:summary, content=:content, image_url=:image_url, category_id=:category_id, status=:status, is_nofollow=:is_nofollow, seo_title=:seo_title, seo_desc=:seo_desc WHERE id=:id";
            $params = [
                ':title' => $postData['title'],
                ':slug' => $postData['slug'],
                ':summary' => $postData['summary'],
                ':content' => $postData['content'],
                ':image_url' => $postData['image_url'],
                ':category_id' => $postData['category_id'] ?: NULL,
                ':status' => $postData['status'],
                ':is_nofollow' => isset($postData['is_nofollow']) ? 1 : 0,
                ':seo_title' => $postData['seo_title'],
                ':seo_desc' => $postData['seo_desc'],
                ':id' => $_POST['post_id']
            ];
            $pdo->prepare($sql)->execute($params);
            $post_id = $_POST['post_id'];
            $message = "Post updated successfully!";

            // Clear existing tags to re-add them (simple approach)
            $pdo->prepare("DELETE FROM post_tags WHERE post_id = ?")->execute([$post_id]);

        } else {
            // INSERT Logic
            $sql = "INSERT INTO posts (title, slug, summary, content, image_url, category_id, status, is_nofollow, seo_title, seo_desc, author_id) 
                    VALUES (:title, :slug, :summary, :content, :image_url, :category_id, :status, :is_nofollow, :seo_title, :seo_desc, :author_id)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':title' => $postData['title'],
                ':slug' => $postData['slug'],
                ':summary' => $postData['summary'],
                ':content' => $postData['content'],
                ':image_url' => $postData['image_url'],
                ':category_id' => $postData['category_id'] ?: NULL,
                ':status' => $postData['status'],
                ':is_nofollow' => isset($postData['is_nofollow']) ? 1 : 0,
                ':seo_title' => $postData['seo_title'],
                ':seo_desc' => $postData['seo_desc'],
                ':author_id' => $_SESSION['user_id']
            ]);
            $post_id = $pdo->lastInsertId();
            $message = "Post created successfully!";
        }

        // Handle Tags (Shared Logic)

        // Handle Tags
        if (!empty($postData['tags'])) {
            $tags = explode(',', $postData['tags']);
            foreach ($tags as $tagName) {
                $tagName = trim($tagName);
                if (empty($tagName))
                    continue;

                $tagSlug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $tagName));

                // Check or Insert Tag
                $stmtTag = $pdo->prepare("SELECT id FROM tags WHERE slug = ?");
                $stmtTag->execute([$tagSlug]);
                $tagId = $stmtTag->fetchColumn();

                if (!$tagId) {
                    $pdo->prepare("INSERT INTO tags (name, slug) VALUES (?, ?)")->execute([$tagName, $tagSlug]);
                    $tagId = $pdo->lastInsertId();
                }

                // Link Post to Tag
                $pdo->prepare("INSERT IGNORE INTO post_tags (post_id, tag_id) VALUES (?, ?)")->execute([$post_id, $tagId]);
            }
        }

        $message = "Post created successfully!";
        // Reset form or redirect
        // header("Location: posts.php"); 
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// Handle Edit Fetch
$action = 'create';
$editId = null;
if (isset($_GET['edit'])) {
    $action = 'update';
    $editId = (int) $_GET['edit'];

    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$editId]);
    $existingPost = $stmt->fetch();

    if ($existingPost) {
        $postData = $existingPost;

        // Fetch Tags
        $stmtTags = $pdo->prepare("SELECT t.name FROM tags t JOIN post_tags pt ON t.id = pt.tag_id WHERE pt.post_id = ?");
        $stmtTags->execute([$editId]);
        $tags = $stmtTags->fetchAll(PDO::FETCH_COLUMN);
        $postData['tags'] = implode(', ', $tags);
    }
}

// Fetch Categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php $pageTitle = $action === 'update' ? 'Edit Post' : 'Create Post'; ?>
    <?php include 'includes/admin_head.php'; ?>
    <style>
        .editor-area {
            min-height: 400px;
        }
    </style>
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

            <main class="flex-1 p-8 pb-24 md:pb-8 overflow-y-auto w-full">
                <header class="flex justify-between items-center mb-8">
                    <h1 class="text-3xl font-serif text-white">
                        <?php echo $action === 'update' ? 'Edit Entry' : 'Write New Entry'; ?>
                    </h1>
                    <div>
                        <a href="posts.php" class="text-slate-400 hover:text-white mr-4">Cancel</a>
                        <!-- Optional Save Draft Button Logic could go here -->
                    </div>
                </header>

                <?php if ($message): ?>
                    <div class="bg-blue-900/50 border border-blue-500 text-blue-200 p-4 rounded mb-8">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <input type="hidden" name="action" value="<?php echo htmlspecialchars($action); ?>">
                    <?php if ($editId): ?>
                        <input type="hidden" name="post_id" value="<?php echo $editId; ?>">
                    <?php endif; ?>

                    <!-- Main Content (Left Col) -->
                    <div class="lg:col-span-2 space-y-6">

                        <!-- Title -->
                        <div>
                            <label class="block text-slate-400 text-xs uppercase tracking-wider mb-2">Title (H1)</label>
                            <input type="text" name="title" value="<?php echo htmlspecialchars($postData['title']); ?>"
                                class="w-full bg-slate-800 border border-slate-700 p-4 text-xl text-white rounded focus:border-blue-500 outline-none"
                                required placeholder="Enter article title...">
                        </div>

                        <!-- Slug (Manual override) -->
                        <div>
                            <label class="block text-slate-500 text-xs uppercase tracking-wider mb-2">Slug (URL
                                Friendly)</label>
                            <input type="text" name="slug" value="<?php echo htmlspecialchars($postData['slug']); ?>"
                                class="w-full bg-slate-900/50 border border-slate-800 p-2 text-sm text-slate-400 rounded focus:border-blue-500 outline-none"
                                placeholder="auto-generated-from-title">
                        </div>

                        <!-- WYSIWYG / Content Area -->
                        <div>
                            <label class="block text-slate-400 text-xs uppercase tracking-wider mb-2">Article
                                Content</label>
                            <p class="text-[0.65rem] text-slate-500 mb-2">Supports HTML. Use &lt;h2&gt; for subtitles.
                            </p>
                            <textarea name="content"
                                class="w-full bg-slate-800 border border-slate-700 p-4 text-slate-300 rounded focus:border-blue-500 outline-none editor-area font-mono text-sm leading-relaxed"
                                required
                                placeholder="Write your masterpiece..."><?php echo htmlspecialchars($postData['content']); ?></textarea>
                        </div>

                        <!-- SEO Section -->
                        <div class="bg-slate-800 p-6 rounded border border-slate-700">
                            <h3 class="text-white font-bold mb-4">SEO Configuration</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-slate-500 text-xs uppercase tracking-wider mb-1">Meta
                                        Title</label>
                                    <input type="text" name="seo_title"
                                        value="<?php echo htmlspecialchars($postData['seo_title']); ?>"
                                        class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white">
                                </div>
                                <div>
                                    <label class="block text-slate-500 text-xs uppercase tracking-wider mb-1">Meta
                                        Description</label>
                                    <textarea name="seo_desc"
                                        class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white"
                                        rows="2"><?php echo htmlspecialchars($postData['seo_desc']); ?></textarea>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <input type="checkbox" name="is_nofollow" value="1" <?php echo $postData['is_nofollow'] ? 'checked' : ''; ?>
                                        class="w-4 h-4 bg-slate-900 border-slate-600">
                                    <span class="text-sm text-slate-400">Enable "No Follow" on links</span>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Sidebar Settings (Right Col) -->
                    <div class="lg:col-span-1 space-y-6">

                        <!-- Publish Action -->
                        <div class="bg-slate-800 p-6 rounded border border-slate-700 sticky top-4">
                            <div class="mb-4">
                                <label class="block text-slate-500 text-xs uppercase tracking-wider mb-2">Status</label>
                                <select name="status"
                                    class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white">
                                    <option value="draft" <?php echo $postData['status'] === 'draft' ? 'selected' : ''; ?>>Draft
                                    </option>
                                    <option value="published" <?php echo $postData['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                                </select>
                            </div>
                            <button type="submit"
                                class="w-full bg-blue-600 hover:bg-blue-500 text-white py-3 rounded font-bold uppercase tracking-widest text-sm transition-colors">Save
                                Post</button>

                            <div class="mt-4 text-xs text-slate-500 text-center">
                                Author: <span class="text-white">
                                    <?php echo $_SESSION['username']; ?>
                                </span>
                            </div>
                        </div>

                        <!-- Category -->
                        <div class="bg-slate-800 p-6 rounded border border-slate-700">
                            <h3 class="text-white font-bold mb-4">Category & Tags</h3>
                            <div class="mb-4">
                                <label
                                    class="block text-slate-500 text-xs uppercase tracking-wider mb-2">Category</label>
                                <select name="category_id"
                                    class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white">
                                    <option value="">-- Uncategorized --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo $postData['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-slate-500 text-xs uppercase tracking-wider mb-2">Tags</label>
                                <input type="text" name="tags"
                                    value="<?php echo htmlspecialchars($postData['tags']); ?>"
                                    class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white text-sm"
                                    placeholder="Separated by commas...">
                            </div>
                        </div>

                        <!-- Image -->
                        <div class="bg-slate-800 p-6 rounded border border-slate-700">
                            <h3 class="text-white font-bold mb-4">Featured Image</h3>
                            <div class="mb-2">
                                <label class="block text-slate-500 text-xs uppercase tracking-wider mb-2">Upload
                                    Image</label>
                                <input type="file" name="image_file" accept="image/*"
                                    class="w-full text-slate-400 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-900 file:text-blue-400 hover:file:bg-blue-800">
                                <input type="hidden" name="image_url"
                                    value="<?php echo htmlspecialchars($postData['image_url']); ?>">

                                <?php if ($postData['image_url']): ?>
                                    <div class="mt-4">
                                        <p class="text-xs text-slate-500 mb-1">Current Image:</p>
                                        <img src="<?php echo htmlspecialchars($postData['image_url']); ?>"
                                            class="w-full h-auto rounded border border-slate-600">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Short Summary -->
                        <div class="bg-slate-800 p-6 rounded border border-slate-700">
                            <h3 class="text-white font-bold mb-4">Short Info</h3>
                            <p class="text-[0.65rem] text-slate-500 mb-2">~30 words max. Displayed on grid cards.</p>
                            <textarea name="summary"
                                class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white h-24 text-sm"
                                placeholder="Brief summary..."><?php echo htmlspecialchars($postData['summary']); ?></textarea>
                        </div>

                    </div>

                </form>
            </main>
</body>

</html>