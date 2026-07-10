<?php
// admin/header_notifications_manage.php
session_start();
require_once 'auth_check.php';
require_once 'db_connect.php';

$message = '';

// Handle DELETE
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM header_notifications WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $message = "Notification deleted.";
}

// Handle TOGGLE ACTIVE
if (isset($_GET['toggle'])) {
    $stmt = $pdo->prepare("UPDATE header_notifications SET is_active = NOT is_active WHERE id = ?");
    $stmt->execute([$_GET['toggle']]);
    $message = "Status updated.";
}

// Handle CREATE/UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $title = trim($_POST['title']);
    $message_text = trim($_POST['message']);
    $image_url = trim($_POST['image_url']);
    $link_url = trim($_POST['link_url']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Handle Image Upload
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/uploads/notifications/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $fileName = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $_FILES['image_file']['name']);
        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $uploadDir . $fileName)) {
            $image_url = 'assets/uploads/notifications/' . $fileName;
        }
    }

    if ($id) {
        $stmt = $pdo->prepare("UPDATE header_notifications SET title = ?, message = ?, image_url = ?, link_url = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$title, $message_text, $image_url, $link_url, $is_active, $id]);
        $message = "Notification updated.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO header_notifications (title, message, image_url, link_url, is_active) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $message_text, $image_url, $link_url, $is_active]);
        $message = "Notification created.";
    }
}

// Fetch all notifications
$notifications = $pdo->query("SELECT * FROM header_notifications ORDER BY created_at DESC")->fetchAll();

// Fetch for edit
$editNotif = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM header_notifications WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editNotif = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = 'Header Bell Notifications'; ?>
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
            
            <div class="mb-8 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-serif text-white mb-2">Header Bell Notifications</h1>
                    <p class="text-slate-400 text-sm">Manage bell notifications shown in the website header</p>
                </div>
                <button onclick="document.getElementById('notifForm').scrollIntoView({behavior: 'smooth'})" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded text-sm font-medium">
                    + Add Notification
                </button>
            </div>

            <?php if ($message): ?>
                <div class="bg-green-900/20 border border-green-700 text-green-400 px-4 py-3 rounded mb-6">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Notifications List -->
            <div class="bg-slate-800 border border-slate-700 rounded-lg overflow-hidden mb-12">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-900 border-b border-slate-700">
                            <tr>
                                <th class="text-left px-6 py-4 text-xs font-bold uppercase tracking-widest text-slate-400">Preview</th>
                                <th class="text-left px-6 py-4 text-xs font-bold uppercase tracking-widest text-slate-400">Title</th>
                                <th class="text-center px-6 py-4 text-xs font-bold uppercase tracking-widest text-slate-400">Status</th>
                                <th class="text-right px-6 py-4 text-xs font-bold uppercase tracking-widest text-slate-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($notifications)): ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                        No notifications yet. Create one below.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($notifications as $notif): ?>
                                    <tr class="border-b border-slate-700 hover:bg-slate-700/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <?php if ($notif['image_url']): ?>
                                                <?php 
                                                    $displayPath = (strpos($notif['image_url'], 'http') === 0) ? $notif['image_url'] : '../' . $notif['image_url'];
                                                ?>
                                                <img src="<?php echo htmlspecialchars($displayPath); ?>" 
                                                     alt="Preview" class="h-12 w-auto rounded border border-slate-600">
                                            <?php else: ?>
                                                <div class="h-12 w-24 bg-slate-700 rounded flex items-center justify-center text-xs text-slate-500">
                                                    No Image
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-white font-medium"><?php echo htmlspecialchars($notif['title']); ?></div>
                                            <div class="text-xs text-slate-500 mt-1 truncate max-w-md">
                                                <?php echo htmlspecialchars($notif['message']); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <a href="?toggle=<?php echo $notif['id']; ?>" 
                                               class="inline-block px-3 py-1 rounded text-xs font-medium <?php echo $notif['is_active'] ? 'bg-green-900/30 text-green-400 border border-green-700' : 'bg-red-900/30 text-red-400 border border-red-700'; ?>">
                                                <?php echo $notif['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 text-right space-x-2">
                                            <a href="?edit=<?php echo $notif['id']; ?>" class="text-blue-400 hover:text-blue-300 text-sm">Edit</a>
                                            <a href="?delete=<?php echo $notif['id']; ?>" onclick="return confirm('Delete this notification?')"
                                               class="text-red-400 hover:text-red-300 text-sm">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Create/Edit Form -->
            <div id="notifForm" class="bg-slate-800 border border-slate-700 rounded-lg p-4 md:p-8 max-w-4xl mx-auto">
                <h2 class="text-xl font-serif text-white mb-6">
                    <?php echo $editNotif ? 'Edit Notification' : 'Create Notification'; ?>
                </h2>
                
                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <?php if ($editNotif): ?>
                        <input type="hidden" name="id" value="<?php echo $editNotif['id']; ?>">
                    <?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Title *</label>
                            <input type="text" name="title" required
                                   value="<?php echo $editNotif ? htmlspecialchars($editNotif['title']) : ''; ?>"
                                   class="w-full bg-slate-900 border border-slate-600 text-white px-4 py-2 rounded focus:outline-none focus:border-blue-500"
                                   placeholder="Special Offer">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Link URL</label>
                            <input type="url" name="link_url"
                                   value="<?php echo $editNotif ? htmlspecialchars($editNotif['link_url']) : ''; ?>"
                                   class="w-full bg-slate-900 border border-slate-600 text-white px-4 py-2 rounded focus:outline-none focus:border-blue-500"
                                   placeholder="https://example.com">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Message</label>
                        <textarea name="message" rows="2"
                                  class="w-full bg-slate-900 border border-slate-600 text-white px-4 py-2 rounded focus:outline-none focus:border-blue-500"
                                  placeholder="Limited time offer - Get 20% off!"><?php echo $editNotif ? htmlspecialchars($editNotif['message']) : ''; ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Image File (Upload)</label>
                            <input type="file" name="image_file" accept="image/*"
                                   class="w-full bg-slate-900 border border-slate-600 text-white px-4 py-2 rounded focus:outline-none focus:border-blue-500">
                            <p class="text-xs text-slate-500 mt-1">Or enter a URL below</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Image URL (Manual)</label>
                            <input type="text" name="image_url"
                                   value="<?php echo $editNotif ? htmlspecialchars($editNotif['image_url']) : ''; ?>"
                                   class="w-full bg-slate-900 border border-slate-600 text-white px-4 py-2 rounded focus:outline-none focus:border-blue-500"
                                   placeholder="https://example.com/banner.jpg">
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" id="is_active"
                               <?php echo (!$editNotif || $editNotif['is_active']) ? 'checked' : ''; ?>
                               class="w-4 h-4 text-blue-600 bg-slate-900 border-slate-600 rounded focus:ring-blue-500">
                        <label for="is_active" class="ml-2 text-sm text-slate-300">Active (show in website bell)</label>
                    </div>

                    <div class="flex gap-4">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2 rounded font-medium">
                            <?php echo $editNotif ? 'Update' : 'Create'; ?>
                        </button>
                        <?php if ($editNotif): ?>
                            <a href="header_notifications_manage.php" class="bg-slate-700 hover:bg-slate-600 text-white px-8 py-2 rounded font-medium inline-block">
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
