<?php
// admin/posts.php
require_once 'auth_check.php';
require_once 'db_connect.php';

// Prepare Query
$sql = "SELECT p.*, c.name as category_name, u.username as author_name 
        FROM posts p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN users u ON p.author_id = u.id 
        ORDER BY p.created_at DESC";
$stmt = $pdo->query($sql);
$posts = $stmt->fetchAll();

// Handle Delete Request
if (isset($_GET['delete'])) {
    $deleteId = (int) $_GET['delete'];
    try {
        // Delete tags associations first (Good practice, though CASCADE might handle it)
        $pdo->prepare("DELETE FROM post_tags WHERE post_id = ?")->execute([$deleteId]);
        // Delete post
        $pdo->prepare("DELETE FROM posts WHERE id = ?")->execute([$deleteId]);
        // Redirect to avoid resubmission
        header("Location: posts.php?msg=deleted");
        exit;
    } catch (PDOException $e) {
        $error = "Error deleting post: " . $e->getMessage();
    }
}
?>
<?php $pageTitle = 'All Posts'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
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

            <main class="flex-1 p-4 pb-24 md:p-8 md:pb-8 overflow-y-auto">
                <header class="flex justify-between items-center mb-8">
                    <h1 class="text-3xl font-serif text-white">All Posts</h1>
                    <a href="post_create.php"
                        class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2 rounded font-bold uppercase text-xs tracking-widest transition-colors">Add
                        New</a>
                </header>

                <!-- Posts List Container -->
                <div class="bg-slate-800 rounded border border-slate-700 overflow-hidden">

                    <!-- Desktop Table View (Hidden on Mobile) -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-900 text-slate-400 text-xs uppercase tracking-wider">
                                    <th class="p-4 border-b border-slate-700 w-1/2">Title</th>
                                    <th class="p-4 border-b border-slate-700">Category</th>
                                    <th class="p-4 border-b border-slate-700">Author</th>
                                    <th class="p-4 border-b border-slate-700">Status</th>
                                    <th class="p-4 border-b border-slate-700">Views</th>
                                    <th class="p-4 border-b border-slate-700 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700">
                                <?php foreach ($posts as $post): ?>
                                    <tr class="hover:bg-slate-700/50 transition-colors">
                                        <td class="p-4">
                                            <div class="font-bold text-white text-lg">
                                                <?php echo htmlspecialchars($post['title']); ?>
                                            </div>
                                            <div class="text-xs text-slate-500 mt-1">
                                                <?php echo htmlspecialchars($post['slug']); ?>
                                            </div>
                                        </td>
                                        <td class="p-4 text-sm text-blue-400">
                                            <?php echo htmlspecialchars($post['category_name'] ?? 'Uncategorized'); ?>
                                        </td>
                                        <td class="p-4 text-sm">
                                            <?php echo htmlspecialchars($post['author_name']); ?>
                                        </td>
                                        <td class="p-4">
                                            <span
                                                class="px-2 py-1 rounded text-[10px] uppercase font-bold tracking-wider <?php echo $post['status'] === 'published' ? 'bg-green-900 text-green-400' : 'bg-slate-700 text-slate-400'; ?>">
                                                <?php echo $post['status']; ?>
                                            </span>
                                        </td>
                                        <td class="p-4 font-mono text-sm">
                                            <?php echo number_format($post['views']); ?>
                                        </td>
                                        <td class="p-4 text-right">
                                            <a href="post_create.php?edit=<?php echo $post['id']; ?>"
                                                class="text-blue-400 hover:text-blue-300 mr-3 text-xs uppercase font-bold">Edit</a>
                                            <a href="?delete=<?php echo $post['id']; ?>"
                                                onclick="return confirm('Are you sure you want to delete this post?');"
                                                class="text-red-500 hover:text-red-400 text-xs uppercase font-bold">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                                <?php if (empty($posts)): ?>
                                    <tr>
                                        <td colspan="6" class="p-12 text-center text-slate-500">
                                            <p class="mb-4">No posts found.</p>
                                            <a href="post_create.php" class="text-blue-400 hover:underline">Create your
                                                first
                                                entry</a>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View (Visible ONLY on Mobile) -->
                    <div class="md:hidden space-y-4 p-4">
                        <?php foreach ($posts as $post): ?>
                            <div class="bg-slate-900/50 rounded-lg p-4 border border-slate-700/50 shadow-lg">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h3 class="font-bold text-white text-lg leading-tight mb-1">
                                            <?php echo htmlspecialchars($post['title']); ?>
                                        </h3>
                                        <p class="text-xs text-slate-500 font-mono truncate max-w-[200px]">
                                            <?php echo htmlspecialchars($post['slug']); ?>
                                        </p>
                                    </div>
                                    <span
                                        class="px-2 py-1 rounded text-[10px] uppercase font-bold tracking-wider <?php echo $post['status'] === 'published' ? 'bg-green-900 text-green-400' : 'bg-slate-700 text-slate-400'; ?>">
                                        <?php echo $post['status']; ?>
                                    </span>
                                </div>

                                <div
                                    class="flex justify-between items-center text-sm text-slate-400 mb-4 border-b border-slate-700/50 pb-3">
                                    <span><?php echo htmlspecialchars($post['category_name'] ?? 'Uncategorized'); ?></span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <?php echo number_format($post['views']); ?>
                                    </span>
                                </div>

                                <div class="flex gap-2">
                                    <a href="post_create.php?edit=<?php echo $post['id']; ?>"
                                        class="flex-1 bg-blue-900/40 text-blue-400 py-2 rounded text-center text-sm font-bold uppercase tracking-wider hover:bg-blue-900/60 transition-colors">Edit</a>
                                    <a href="?delete=<?php echo $post['id']; ?>"
                                        onclick="return confirm('Delete this post?');"
                                        class="flex-1 bg-red-900/40 text-red-400 py-2 rounded text-center text-sm font-bold uppercase tracking-wider hover:bg-red-900/60 transition-colors">Delete</a>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php if (empty($posts)): ?>
                            <div class="text-center p-8 text-slate-500">
                                No posts found.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </main>
</body>

</html>