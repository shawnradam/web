<?php
// admin/feedback_manage.php
session_start();
require_once 'auth_check.php';
require_once 'db_connect.php';

// Handle Mark as Read
if (isset($_GET['mark_read'])) {
    $id = (int) $_GET['mark_read'];
    $stmt = $pdo->prepare("UPDATE feedback_submissions SET is_read = 1 WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: feedback_manage.php");
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM feedback_submissions WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: feedback_manage.php");
    exit;
}

// Fetch all feedback
$feedback = $pdo->query("SELECT * FROM feedback_submissions ORDER BY is_read ASC, created_at DESC")->fetchAll();

// Count unread
$unreadCount = $pdo->query("SELECT COUNT(*) FROM feedback_submissions WHERE is_read = 0")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php $pageTitle = 'Feedback Management'; ?>
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
                            <h1 class="text-3xl font-serif text-white mb-2">User Feedback</h1>
                            <p class="text-slate-400 text-sm">
                                <?php echo $unreadCount; ?> unread feedback
                                <?php echo $unreadCount != 1 ? 's' : ''; ?>
                            </p>
                        </div>
                    </div>

                    <!-- Feedback List -->
                    <div class="space-y-4">
                        <?php if (empty($feedback)): ?>
                            <div class="bg-slate-800 border border-slate-700 rounded-lg p-12 text-center">
                                <svg class="w-16 h-16 text-slate-600 mx-auto mb-4" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                </svg>
                                <h3 class="text-white text-xl font-serif mb-2">No Feedback Yet</h3>
                                <p class="text-slate-400">User feedback will appear here when submitted.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($feedback as $item): ?>
                                <div
                                    class="bg-slate-800 border border-slate-700 rounded-lg p-6 <?php echo !$item['is_read'] ? 'border-l-4 border-l-gold-500' : ''; ?>">
                                    <div class="flex justify-between items-start mb-4">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3 mb-2">
                                                <h3 class="text-white font-medium text-lg">
                                                    <?php echo htmlspecialchars($item['name']); ?>
                                                </h3>
                                                <?php if (!$item['is_read']): ?>
                                                    <span
                                                        class="bg-gold-500/20 text-gold-500 text-xs px-2 py-1 rounded-full border border-gold-500/30">New</span>
                                                <?php endif; ?>
                                            </div>
                                            <p class="text-slate-400 text-sm">
                                                <?php echo htmlspecialchars($item['email']); ?>
                                            </p>
                                            <p class="text-slate-500 text-xs mt-1">
                                                <?php echo date('F d, Y \a\t g:i A', strtotime($item['created_at'])); ?>
                                            </p>
                                        </div>

                                        <div class="flex gap-2">
                                            <?php if (!$item['is_read']): ?>
                                                <a href="?mark_read=<?php echo $item['id']; ?>"
                                                    class="text-blue-400 hover:text-blue-300 text-sm">
                                                    Mark Read
                                                </a>
                                            <?php endif; ?>
                                            <a href="?delete=<?php echo $item['id']; ?>"
                                                onclick="return confirm('Delete this feedback?')"
                                                class="text-red-400 hover:text-red-300 text-sm">
                                                Delete
                                            </a>
                                        </div>
                                    </div>

                                    <div class="bg-navy-900 border border-slate-700 rounded p-4">
                                        <p class="text-slate-300 leading-relaxed whitespace-pre-wrap">
                                            <?php echo htmlspecialchars($item['message']); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                </div>
            </main>
        </div>

</body>

</html>