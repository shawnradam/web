<?php
// admin/categories.php
require_once 'auth_check.php';
require_once 'db_connect.php';

$message = '';

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    if ($name) {
        // Simple slug generation
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

        try {
            $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
            $stmt->execute([$name, $slug, $description]);
            $message = "Category added successfully.";
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}

// Handle Update Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = (int) $_POST['id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    if ($name && $id) {
        // Optionally update slug or keep original? Let's keep original to avoid breaking links, 
        // or update if desired. For now, just update name/desc.
        try {
            $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
            $stmt->execute([$name, $description, $id]);
            $message = "Category updated successfully.";
            // Clear Edit Mode
            // header("Location: categories.php"); // Optional redirect to clear POST
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}

// Fetch Category for Edit
$editCategory = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $editCategory = $stmt->fetch();
}

// Handle Delete Category
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        $message = "Category deleted.";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// Fetch All Categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php $pageTitle = 'Categories'; ?>
    <?php include 'includes/admin_head.php'; ?>
</head>

<body class="bg-slate-900 text-slate-300 antialiased selection:bg-gold-500 selection:text-white">

    <!-- Sidebar Import -->
    <div class="flex min-h-screen relative" x-data="{ 
            sidebarOpen: false, 
            collapsed: (function(){ try { return localStorage.getItem('sidebarCollapsed') === 'true' } catch(e){ return false } })() 
        }" x-init="$watch('collapsed', val => { try { localStorage.setItem('sidebarCollapsed', val) } catch(e){} })">

        <?php include 'dashboard_sidebar_partial.php'; ?>

        <div class="flex-1 flex flex-col min-h-screen transition-all duration-300 ease-in-out md:ml-64 overflow-x-hidden"
            :class="collapsed ? 'md:!ml-20' : 'md:!ml-64'">

            <?php include 'includes/admin_header_partial.php'; ?>

            <main class="flex-1 p-8 pb-24 md:pb-8 overflow-y-auto">
                <header class="flex justify-between items-center mb-8">
                    <h1 class="text-3xl font-serif text-white">Categories</h1>
                    <a href="dashboard.php" class="text-blue-400 hover:text-blue-300">&larr; Back to Dashboard</a>
                </header>

                <?php if ($message): ?>
                    <div class="bg-blue-900/50 border border-blue-500 text-blue-200 p-4 rounded mb-8">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                    <!-- Add Form -->
                    <div class="lg:col-span-1">
                        <div class="bg-slate-800 p-6 rounded border border-slate-700">
                            <h2 class="text-white font-bold mb-4">
                                <?php echo $editCategory ? 'Edit Category' : 'Add New Category'; ?>
                            </h2>
                            <form method="POST">
                                <input type="hidden" name="action"
                                    value="<?php echo $editCategory ? 'update' : 'add'; ?>">
                                <?php if ($editCategory): ?>
                                    <input type="hidden" name="id" value="<?php echo $editCategory['id']; ?>">
                                <?php endif; ?>

                                <div class="mb-4">
                                    <label
                                        class="block text-xs uppercase tracking-wider text-slate-500 mb-1">Name</label>
                                    <input type="text" name="name"
                                        value="<?php echo htmlspecialchars($editCategory['name'] ?? ''); ?>"
                                        class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white focus:border-blue-500 outline-none"
                                        required>
                                </div>

                                <div class="mb-4">
                                    <label
                                        class="block text-xs uppercase tracking-wider text-slate-500 mb-1">Description</label>
                                    <textarea name="description"
                                        class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white focus:border-blue-500 outline-none"
                                        rows="3"><?php echo htmlspecialchars($editCategory['description'] ?? ''); ?></textarea>
                                </div>

                                <?php if ($editCategory): ?>
                                    <div class="flex space-x-2">
                                        <button type="submit"
                                            class="flex-1 bg-green-600 hover:bg-green-500 text-white py-2 rounded font-bold transition-colors">Update</button>
                                        <a href="categories.php"
                                            class="flex-1 bg-slate-700 hover:bg-slate-600 text-white py-2 rounded font-bold transition-colors text-center">Cancel</a>
                                    </div>
                                <?php else: ?>
                                    <button type="submit"
                                        class="w-full bg-blue-600 hover:bg-blue-500 text-white py-2 rounded font-bold transition-colors">Add
                                        Category</button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>

                    <!-- List -->
                    <div class="lg:col-span-2">
                        <div class="bg-slate-800 rounded border border-slate-700 overflow-hidden">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-900 text-slate-400 text-xs uppercase tracking-wider">
                                        <th class="p-4 border-b border-slate-700">Name</th>
                                        <th class="p-4 border-b border-slate-700">Slug</th>
                                        <th class="p-4 border-b border-slate-700">Description</th>
                                        <th class="p-4 border-b border-slate-700 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-700">
                                    <?php foreach ($categories as $cat): ?>
                                        <tr class="hover:bg-slate-700/50 transition-colors">
                                            <td class="p-4 font-bold text-white">
                                                <?php echo htmlspecialchars($cat['name']); ?>
                                            </td>
                                            <td class="p-4 font-mono text-sm text-slate-500">
                                                <?php echo htmlspecialchars($cat['slug']); ?>
                                            </td>
                                            <td class="p-4 text-sm">
                                                <?php echo htmlspecialchars($cat['description']); ?>
                                            </td>
                                            <td class="p-4 text-right">
                                                <a href="?edit=<?php echo $cat['id']; ?>"
                                                    class="text-blue-400 hover:text-blue-300 text-xs font-bold uppercase tracking-wider mr-3">Edit</a>
                                                <a href="?delete=<?php echo $cat['id']; ?>"
                                                    class="text-red-400 hover:text-red-300 text-xs font-bold uppercase tracking-wider"
                                                    onclick="return confirm('Are you sure?');">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>

                                    <?php if (empty($categories)): ?>
                                        <tr>
                                            <td colspan="4" class="p-8 text-center text-slate-500 italic">No categories
                                                found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </main>
</body>

</html>