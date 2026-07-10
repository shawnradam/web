<?php
// admin/ads_manage.php
session_start();
require_once 'auth_check.php';
require_once 'db_connect.php';

// Handle CRUD Operations
$message = '';
$error = '';

// DELETE
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM ads WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $message = "Advertisement deleted successfully.";
    } catch (PDOException $e) {
        $error = "Error deleting ad: " . $e->getMessage();
    }
}

// TOGGLE ACTIVE STATUS
if (isset($_GET['toggle'])) {
    try {
        $stmt = $pdo->prepare("UPDATE ads SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$_GET['toggle']]);
        $message = "Ad status updated successfully.";
    } catch (PDOException $e) {
        $error = "Error updating status: " . $e->getMessage();
    }
}

// CREATE/UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $title = trim($_POST['title']);
    $image_url = trim($_POST['image_url']);
    $link_url = trim($_POST['link_url']);
    $description = trim($_POST['description']);
    $position = $_POST['position'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $sort_order = intval($_POST['sort_order']);

    try {
        if ($id) {
            // Update
            $stmt = $pdo->prepare("UPDATE ads SET title = ?, image_url = ?, link_url = ?, description = ?, position = ?, is_active = ?, sort_order = ? WHERE id = ?");
            $stmt->execute([$title, $image_url, $link_url, $description, $position, $is_active, $sort_order, $id]);
            $message = "Advertisement updated successfully.";
        } else {
            // Create
            $stmt = $pdo->prepare("INSERT INTO ads (title, image_url, link_url, description, position, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $image_url, $link_url, $description, $position, $is_active, $sort_order]);
            $message = "Advertisement created successfully.";
        }
    } catch (PDOException $e) {
        $error = "Error saving ad: " . $e->getMessage();
    }
}

// Fetch all ads
$ads = $pdo->query("SELECT * FROM ads ORDER BY position ASC, sort_order ASC, created_at DESC")->fetchAll();

// Fetch ad for editing
$editAd = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM ads WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editAd = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = 'Manage Advertisements'; ?>
    <?php include 'includes/admin_head.php'; ?>
</head>
<body class="bg-slate-900 text-slate-300 antialiased selection:bg-gold-500 selection:text-white">
    <div class="flex min-h-screen relative" 
        x-data="{ 
            sidebarOpen: false, 
            collapsed: (function(){ try { return localStorage.getItem('sidebarCollapsed') === 'true' } catch(e){ return false } })() 
        }" 
        x-init="$watch('collapsed', val => { try { localStorage.setItem('sidebarCollapsed', val) } catch(e){} })">
        
        <?php include 'dashboard_sidebar_partial.php'; ?>

        <div class="flex-1 flex flex-col min-h-screen transition-all duration-300 ease-in-out md:ml-64 overflow-x-hidden"
             :class="collapsed ? 'md:!ml-20' : 'md:!ml-64'">
             
            <?php include 'includes/admin_header_partial.php'; ?>
            
            <main class="flex-1 p-4 pb-24 md:p-8 md:pb-8 overflow-y-auto">
        <div class="max-w-6xl mx-auto">
            
            <!-- Header -->
            <div class="mb-8 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-serif text-white mb-2">Manage Advertisements</h1>
                    <p class="text-slate-400 text-sm">Create and manage ads for your website</p>
                </div>
                <button onclick="document.getElementById('adForm').scrollIntoView({behavior: 'smooth'})" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded text-sm font-medium">
                    + Add New Ad
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

            <!-- Ads List -->
            <div class="bg-slate-800 border border-slate-700 rounded-lg overflow-hidden mb-12">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-900 border-b border-slate-700">
                            <tr>
                                <th class="text-left px-6 py-4 text-xs font-bold uppercase tracking-widest text-slate-400">Preview</th>
                                <th class="text-left px-6 py-4 text-xs font-bold uppercase tracking-widest text-slate-400">Title</th>
                                <th class="text-left px-6 py-4 text-xs font-bold uppercase tracking-widest text-slate-400">Position</th>
                                <th class="text-center px-6 py-4 text-xs font-bold uppercase tracking-widest text-slate-400">Status</th>
                                <th class="text-center px-6 py-4 text-xs font-bold uppercase tracking-widest text-slate-400">Order</th>
                                <th class="text-right px-6 py-4 text-xs font-bold uppercase tracking-widest text-slate-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($ads)): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                        <svg class="w-12 h-12 mx-auto mb-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <p class="text-sm">No advertisements found. Create your first ad below.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($ads as $ad): ?>
                                    <tr class="border-b border-slate-700 hover:bg-slate-700/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <img src="<?php echo htmlspecialchars($ad['image_url']); ?>" 
                                                 alt="<?php echo htmlspecialchars($ad['title']); ?>"
                                                 class="w-16 h-16 object-cover rounded border border-slate-600"
                                                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22%3E%3Crect fill=%22%23334155%22 width=%22100%22 height=%22100%22/%3E%3Ctext fill=%22%23cbd5e1%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 font-size=%2212%22%3ENo Image%3C/text%3E%3C/svg%3E'">
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-white font-medium"><?php echo htmlspecialchars($ad['title']); ?></div>
                                            <div class="text-xs text-slate-500 mt-1 truncate max-w-xs">
                                                <?php echo htmlspecialchars($ad['description']); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-block bg-slate-700 text-slate-300 px-2 py-1 rounded text-xs">
                                                <?php echo htmlspecialchars($ad['position']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <a href="?toggle=<?php echo $ad['id']; ?>" 
                                               class="inline-block px-3 py-1 rounded text-xs font-medium <?php echo $ad['is_active'] ? 'bg-green-900/30 text-green-400 border border-green-700' : 'bg-red-900/30 text-red-400 border border-red-700'; ?>">
                                                <?php echo $ad['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 text-center text-sm text-slate-400">
                                            <?php echo $ad['sort_order']; ?>
                                        </td>
                                        <td class="px-6 py-4 text-right space-x-2">
                                            <a href="?edit=<?php echo $ad['id']; ?>" 
                                               class="inline-block text-blue-400 hover:text-blue-300 text-sm">Edit</a>
                                            <a href="?delete=<?php echo $ad['id']; ?>" 
                                               onclick="return confirm('Delete this advertisement?')"
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
            <div id="adForm" class="bg-slate-800 border border-slate-700 rounded-lg p-4 md:p-8 max-w-4xl mx-auto">
                <h2 class="text-xl font-serif text-white mb-6">
                    <?php echo $editAd ? 'Edit Advertisement' : 'Create New Advertisement'; ?>
                </h2>
                
                <form method="POST" action="" class="space-y-6">
                    <?php if ($editAd): ?>
                        <input type="hidden" name="id" value="<?php echo $editAd['id']; ?>">
                    <?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Ad Title *</label>
                            <input type="text" name="title" required
                                   value="<?php echo $editAd ? htmlspecialchars($editAd['title']) : ''; ?>"
                                   class="w-full bg-slate-900 border border-slate-600 text-white px-4 py-2 rounded focus:outline-none focus:border-blue-500"
                                   placeholder="e.g., Premium Property Listing">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Position *</label>
                            <select name="position" required
                                    class="w-full bg-slate-900 border border-slate-600 text-white px-4 py-2 rounded focus:outline-none focus:border-blue-500">
                                <option value="sidebar" <?php echo ($editAd && $editAd['position'] === 'sidebar') ? 'selected' : ''; ?>>Sidebar</option>
                                <option value="header" <?php echo ($editAd && $editAd['position'] === 'header') ? 'selected' : ''; ?>>Header</option>
                                <option value="footer" <?php echo ($editAd && $editAd['position'] === 'footer') ? 'selected' : ''; ?>>Footer</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Image URL *</label>
                        <input type="url" name="image_url" required
                               value="<?php echo $editAd ? htmlspecialchars($editAd['image_url']) : ''; ?>"
                               class="w-full bg-slate-900 border border-slate-600 text-white px-4 py-2 rounded focus:outline-none focus:border-blue-500"
                               placeholder="https://example.com/ad-image.jpg">
                        <p class="text-xs text-slate-500 mt-1">Recommended: 300x600px for sidebar ads</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Link URL *</label>
                        <input type="url" name="link_url" required
                               value="<?php echo $editAd ? htmlspecialchars($editAd['link_url']) : ''; ?>"
                               class="w-full bg-slate-900 border border-slate-600 text-white px-4 py-2 rounded focus:outline-none focus:border-blue-500"
                               placeholder="https://example.com/landing-page">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Description (shown on hover)</label>
                        <textarea name="description" rows="3"
                                  class="w-full bg-slate-900 border border-slate-600 text-white px-4 py-2 rounded focus:outline-none focus:border-blue-500"
                                  placeholder="Brief description shown when users hover over the ad..."><?php echo $editAd ? htmlspecialchars($editAd['description']) : ''; ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Sort Order</label>
                            <input type="number" name="sort_order" min="0"
                                   value="<?php echo $editAd ? $editAd['sort_order'] : '0'; ?>"
                                   class="w-full bg-slate-900 border border-slate-600 text-white px-4 py-2 rounded focus:outline-none focus:border-blue-500"
                                   placeholder="0">
                            <p class="text-xs text-slate-500 mt-1">Lower numbers appear first</p>
                        </div>

                        <div class="flex items-center pt-6">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1"
                                       <?php echo (!$editAd || $editAd['is_active']) ? 'checked' : ''; ?>
                                       class="w-4 h-4 text-blue-600 bg-slate-900 border-slate-600 rounded focus:ring-blue-500">
                                <span class="ml-2 text-sm text-slate-300">Active (show on website)</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <button type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2 rounded font-medium">
                            <?php echo $editAd ? 'Update Advertisement' : 'Create Advertisement'; ?>
                        </button>
                        <?php if ($editAd): ?>
                            <a href="ads_manage.php" 
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
