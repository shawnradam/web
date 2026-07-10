<?php
// admin/tags_manage.php
session_start();
require_once 'auth_check.php';
require_once 'db_connect.php';

// Handle CRUD Operations
$message = '';
$error = '';

// DELETE
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM tags WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $message = "Tag deleted successfully.";
    } catch (PDOException $e) {
        $error = "Error deleting tag: " . $e->getMessage();
    }
}

// CREATE/UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']) ?: strtolower(str_replace(' ', '-', preg_replace('/[^A-Za-z0-9 ]/', '', $name)));

    try {
        if ($id) {
            // Update
            $stmt = $pdo->prepare("UPDATE tags SET name = ?, slug = ? WHERE id = ?");
            $stmt->execute([$name, $slug, $id]);
            $message = "Tag updated successfully.";
        } else {
            // Create
            $stmt = $pdo->prepare("INSERT INTO tags (name, slug) VALUES (?, ?)");
            $stmt->execute([$name, $slug]);
            $message = "Tag created successfully.";
        }
    } catch (PDOException $e) {
        $error = "Error saving tag: " . $e->getMessage();
    }
}

// Fetch all tags
$tags = $pdo->query("SELECT * FROM tags ORDER BY name ASC")->fetchAll();

// Fetch tag for editing
$editTag = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM tags WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editTag = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php $pageTitle = 'Manage Tags'; ?>
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
                <div class="max-w-6xl mx-auto">

                    <!-- Header -->
                    <div class="mb-8 flex justify-between items-center">
                        <div>
                            <h1 class="text-3xl font-serif text-white mb-2">Manage Tags</h1>
                            <p class="text-slate-400 text-sm">Create and organize tags for blog posts</p>
                        </div>
                        <button onclick="document.getElementById('tagForm').scrollIntoView({behavior: 'smooth'})"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded text-sm font-medium">
                            + Add New Tag
                        </button>
                    </div>

                    <!-- Messages -->
                    <?php if ($message): ?>
                        <div class="bg-green-900/20 border border-green-700 text-green-400 px-4 py-3 rounded mb-6">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="bg-red-900/20 border border-red-700 text-red-400 px-4 py-3 rounded mb-6">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Tags List -->
                    <div class="bg-slate-800 border border-slate-700 rounded-lg overflow-hidden mb-12">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-slate-900 border-b border-slate-700">
                                    <tr>
                                        <th
                                            class="text-left px-6 py-4 text-xs font-bold uppercase tracking-widest text-slate-400">
                                            ID</th>
                                        <th
                                            class="text-left px-6 py-4 text-xs font-bold uppercase tracking-widest text-slate-400">
                                            Name</th>
                                        <th
                                            class="text-left px-6 py-4 text-xs font-bold uppercase tracking-widest text-slate-400">
                                            Slug</th>
                                        <th
                                            class="text-right px-6 py-4 text-xs font-bold uppercase tracking-widest text-slate-400">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($tags)): ?>
                                        <tr>
                                            <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                                <svg class="w-12 h-12 mx-auto mb-4 text-slate-600" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                </svg>
                                                <p class="text-sm">No tags found. Create your first tag below.</p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($tags as $tag): ?>
                                            <tr class="border-b border-slate-700 hover:bg-slate-700/50 transition-colors">
                                                <td class="px-6 py-4 text-sm text-slate-400">
                                                    <?php echo $tag['id']; ?>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <span
                                                        class="inline-block bg-gold-500/10 text-gold-500 px-3 py-1 rounded-full text-xs font-medium border border-gold-500/20">
                                                        <?php echo htmlspecialchars($tag['name']); ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-slate-400 font-mono">
                                                    <?php echo htmlspecialchars($tag['slug']); ?>
                                                </td>
                                                <td class="px-6 py-4 text-right space-x-2">
                                                    <a href="?edit=<?php echo $tag['id']; ?>"
                                                        class="inline-block text-blue-400 hover:text-blue-300 text-sm">Edit</a>
                                                    <a href="?delete=<?php echo $tag['id']; ?>"
                                                        onclick="return confirm('Delete this tag? This will remove it from all posts.')"
                                                        class="inline-block text-red-400 hover:text-red-300 text-sm">Delete</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Create/Edit Form -->
                    <div id="tagForm" class="bg-slate-800 border border-slate-700 rounded-lg p-8">
                        <h2 class="text-xl font-serif text-white mb-6">
                            <?php echo $editTag ? 'Edit Tag' : 'Create New Tag'; ?>
                        </h2>

                        <form method="POST" action="" class="space-y-6">
                            <?php if ($editTag): ?>
                                <input type="hidden" name="id" value="<?php echo $editTag['id']; ?>">
                            <?php endif; ?>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-slate-300 mb-2">Tag Name *</label>
                                    <input type="text" name="name" required
                                        value="<?php echo $editTag ? htmlspecialchars($editTag['name']) : ''; ?>"
                                        class="w-full bg-slate-900 border border-slate-600 text-white px-4 py-2 rounded focus:outline-none focus:border-blue-500"
                                        placeholder="e.g., Land Development">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-300 mb-2">Slug
                                        (URL-friendly)</label>
                                    <input type="text" name="slug"
                                        value="<?php echo $editTag ? htmlspecialchars($editTag['slug']) : ''; ?>"
                                        class="w-full bg-slate-900 border border-slate-600 text-white px-4 py-2 rounded focus:outline-none focus:border-blue-500 font-mono text-sm"
                                        placeholder="Auto-generated if left empty">
                                    <p class="text-xs text-slate-500 mt-1">Leave empty to auto-generate from name</p>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <button type="submit"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2 rounded font-medium">
                                    <?php echo $editTag ? 'Update Tag' : 'Create Tag'; ?>
                                </button>
                                <?php if ($editTag): ?>
                                    <a href="tags_manage.php"
                                        class="bg-slate-700 hover:bg-slate-600 text-white px-8 py-2 rounded font-medium inline-block">
                                        Cancel
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>

                </div>
            </main>
        </div>

</body>

</html>