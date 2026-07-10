<?php
// admin/includes/admin_header_partial.php

// 1. Fetch Profile Data (Avatar & Name) for Header
// Note: We assume $pdo is already available from the parent page
$headerProfile = null;
try {
    if (isset($pdo)) {
        $stmt = $pdo->prepare("SELECT avatar_url, COALESCE(display_name, username) as full_name FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $headerProfile = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
}

// 2. Determine Display: Avatar vs Initials
$headerAvatarUrl = $headerProfile['avatar_url'] ?? '';
$headerFullName = $headerProfile['full_name'] ?? 'Admin User';
$headerInitials = 'AU'; // Default

if (!empty($headerFullName)) {
    $names = explode(' ', trim($headerFullName));
    if (count($names) >= 2) {
        $headerInitials = strtoupper(substr($names[0], 0, 1) . substr(end($names), 0, 1));
    } else {
        $headerInitials = strtoupper(substr($names[0], 0, 2));
    }
}
?>

<header
    class="sticky top-0 z-40 flex items-center justify-between px-4 md:px-8 py-3 bg-slate-900/95 backdrop-blur-sm border-b border-slate-800">
    <div class="flex items-center gap-4 md:hidden">
        <button @click="sidebarOpen = !sidebarOpen" class="text-slate-400 hover:text-white">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                </path>
            </svg>
        </button>
    </div>

    <div class="flex items-center gap-6 ml-auto">
        <!-- Notifications (Placeholder) -->
        <button class="text-slate-400 hover:text-white relative">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                </path>
            </svg>
        </button>

        <!-- Profile Dropdown -->
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="flex items-center gap-3 focus:outline-none">
                <div
                    class="w-10 h-10 rounded-full bg-gold-500 text-navy-900 flex items-center justify-center font-bold text-sm overflow-hidden border-2 border-slate-700 hover:border-gold-500 transition-all">
                    <?php if (!empty($headerAvatarUrl) && file_exists('../' . $headerAvatarUrl)): ?>
                        <img src="../<?php echo htmlspecialchars($headerAvatarUrl); ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <span>
                            <?php echo $headerInitials; ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="hidden md:block text-left">
                    <p class="text-white text-sm font-bold leading-none mb-1">
                        <?php echo htmlspecialchars($headerFullName); ?>
                    </p>
                    <p class="text-slate-500 text-xs">Administrator</p>
                </div>
                <svg class="w-4 h-4 text-slate-500 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <!-- Dropdown Menu -->
            <div x-show="open" @click.away="open = false" x-cloak
                class="absolute right-0 top-full mt-2 w-48 bg-navy-800 border border-slate-700 rounded-lg shadow-xl z-50 overflow-hidden">
                <a href="dashboard.php"
                    class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-700 hover:text-white">Dashboard</a>
                <a href="about_manage.php"
                    class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-700 hover:text-white">About Page</a>
                <a href="executive_profile_manage.php"
                    class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-700 hover:text-white">Edit Profile</a>
                <div class="border-t border-slate-700 my-1"></div>
                <a href="logout.php"
                    class="block px-4 py-2 text-sm text-red-400 hover:bg-slate-700 hover:text-red-300">Logout</a>
            </div>
        </div>
    </div>
</header>