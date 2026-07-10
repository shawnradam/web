<?php
// admin/dashboard_sidebar_partial.php

// Fetch Profile Data (Avatar & Name) for Sidebar
$sidebarProfile = null;
try {
    if (isset($pdo)) {
        $stmt = $pdo->prepare("SELECT avatar_url, COALESCE(display_name, username) as full_name FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $sidebarProfile = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
}

// Determine Display: Avatar vs Initials
$sidebarAvatarUrl = $sidebarProfile['avatar_url'] ?? '';
$sidebarFullName = $sidebarProfile['full_name'] ?? $_SESSION['username'] ?? 'Admin User';
$sidebarInitials = 'A'; // Default

if (!empty($sidebarFullName)) {
    $names = explode(' ', trim($sidebarFullName));
    if (count($names) >= 2) {
        $sidebarInitials = strtoupper(substr($names[0], 0, 1) . substr(end($names), 0, 1));
    } else {
        $sidebarInitials = strtoupper(substr($names[0], 0, 2));
    }
}

// Fetch unread feedback count (existing logic)
try {
    $unreadFeedback = $pdo->query("SELECT COUNT(*) FROM feedback_submissions WHERE is_read = 0")->fetchColumn();
} catch (PDOException $e) {
    $unreadFeedback = 0;
}
?>
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<style>
    .admin-sidebar-scroll { scrollbar-width: thin; scrollbar-color: rgba(212,175,55,.5) rgba(15,23,42,.25); }
    .admin-sidebar-scroll::-webkit-scrollbar { width: 6px; }
    .admin-sidebar-scroll::-webkit-scrollbar-track { background: rgba(15,23,42,.25); }
    .admin-sidebar-scroll::-webkit-scrollbar-thumb { background: rgba(212,175,55,.5); border-radius: 999px; }
    .admin-sidebar-scroll::-webkit-scrollbar-thumb:hover { background: rgba(212,175,55,.85); }
</style>
<!-- Mobile Toggle Button (Fixed Position) -->
<!-- Sidebar Container (x-data provided by parent layout) -->
<aside
    class="hidden md:flex fixed top-0 inset-y-0 left-0 bg-slate-950 border-r border-slate-800 transition-all duration-300 ease-in-out z-50 flex-col"
    :class="collapsed ? 'w-20' : 'w-64'">

    <div class="p-4 border-b border-slate-800 flex items-center justify-between h-16">
        <div class="flex items-center gap-3 overflow-hidden whitespace-nowrap"
            :class="collapsed ? 'justify-center w-full' : ''">
            <!-- Logo Icon -->
            <div
                class="w-8 h-8 rounded bg-gradient-to-br from-blue-600 to-blue-800 flex items-center justify-center shrink-0">
                <span class="text-white font-serif font-bold">A</span>
            </div>
            <!-- Logo Text -->
            <div x-show="!collapsed" class="transition-opacity duration-200">
                <h2 class="text-white font-serif text-lg leading-none">Advisor<span class="text-blue-500">CMS</span>
                </h2>
            </div>
        </div>
        <!-- Toggle -->
        <button @click="collapsed = !collapsed" class="text-slate-500 hover:text-white transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                :class="collapsed ? 'rotate-180' : ''">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
            </svg>
        </button>
    </div>

    <nav class="admin-sidebar-scroll flex-1 overflow-y-auto overflow-x-hidden p-3 space-y-1">

        <!-- Home -->
        <div class="pt-1 pb-2 px-3 text-xs font-bold uppercase tracking-widest text-slate-600 truncate"
            x-show="!collapsed">Home</div>
        <div class="my-2 border-t border-slate-800" x-show="collapsed"></div>

        <a href="../index.php" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors group text-slate-400 hover:text-white"
            :class="collapsed ? 'justify-center' : ''" title="Home">
            <div class="text-gold-500 group-hover:text-gold-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 11l9-8 9 8M5 10v10h5v-6h4v6h5V10" />
                </svg>
            </div>
            <span x-show="!collapsed" class="whitespace-nowrap text-slate-300 group-hover:text-white">Home</span>
        </a>
        <!-- Dashboard Child -->
        <a href="dashboard.php" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors group relative"
            :class="collapsed ? 'justify-center' : 'pl-7'" title="Dashboard">
            <div
                class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'text-blue-400' : 'text-slate-400 group-hover:text-white'; ?>">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
            </div>
            <span x-show="!collapsed"
                class="whitespace-nowrap <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'text-white' : 'text-slate-300 group-hover:text-white'; ?>">Dashboard</span>
        </a>

        <!-- Divider Label -->
        <div class="pt-4 pb-2 px-3 text-xs font-bold uppercase tracking-widest text-slate-600 truncate"
            x-show="!collapsed">Blog Module</div>
        <div class="my-2 border-t border-slate-800" x-show="collapsed"></div>

        <!-- Posts -->
        <a href="posts.php" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors group"
            :class="collapsed ? 'justify-center' : ''" title="All Posts">
            <div
                class="<?php echo basename($_SERVER['PHP_SELF']) == 'posts.php' ? 'text-purple-400' : 'text-slate-400 group-hover:text-white'; ?>">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                </svg>
            </div>
            <span x-show="!collapsed"
                class="whitespace-nowrap <?php echo basename($_SERVER['PHP_SELF']) == 'posts.php' ? 'text-white' : 'text-slate-300 group-hover:text-white'; ?>">All
                Posts</span>
        </a>

        <!-- Add Post -->
        <a href="post_create.php" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors group"
            :class="collapsed ? 'justify-center' : ''" title="Add New Post">
            <div
                class="<?php echo basename($_SERVER['PHP_SELF']) == 'post_create.php' ? 'text-green-400' : 'text-slate-400 group-hover:text-white'; ?>">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </div>
            <span x-show="!collapsed"
                class="whitespace-nowrap <?php echo basename($_SERVER['PHP_SELF']) == 'post_create.php' ? 'text-white' : 'text-slate-300 group-hover:text-white'; ?>">Add
                Post</span>
        </a>

        <!-- Categories -->
        <a href="categories.php" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors group"
            :class="collapsed ? 'justify-center' : ''" title="Categories">
            <div
                class="<?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'text-yellow-400' : 'text-slate-400 group-hover:text-white'; ?>">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
            </div>
            <span x-show="!collapsed"
                class="whitespace-nowrap <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'text-white' : 'text-slate-300 group-hover:text-white'; ?>">Categories</span>
        </a>

        <!-- Divider Label -->
        <div class="pt-4 pb-2 px-3 text-xs font-bold uppercase tracking-widest text-slate-600 truncate"
            x-show="!collapsed">CMS Core</div>
        <div class="my-2 border-t border-slate-800" x-show="collapsed"></div>


        <!-- About Page -->
        <a href="about_manage.php" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors group"
            :class="collapsed ? 'justify-center' : ''" title="About Page">
            <div
                class="<?php echo basename($_SERVER['PHP_SELF']) == 'about_manage.php' ? 'text-cyan-400' : 'text-slate-400 group-hover:text-white'; ?>">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <span x-show="!collapsed"
                class="whitespace-nowrap <?php echo basename($_SERVER['PHP_SELF']) == 'about_manage.php' ? 'text-white' : 'text-slate-300 group-hover:text-white'; ?>">About Page</span>
        </a>
        <!-- TLS Landing Pages -->
        <a href="landing_pages_manage.php" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors group"
            :class="collapsed ? 'justify-center' : ''" title="TLS Landing Pages">
            <div
                class="<?php echo basename($_SERVER['PHP_SELF']) == 'landing_pages_manage.php' ? 'text-gold-500' : 'text-slate-400 group-hover:text-white'; ?>">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5h16M4 12h16M4 19h10" />
                </svg>
            </div>
            <span x-show="!collapsed"
                class="whitespace-nowrap <?php echo basename($_SERVER['PHP_SELF']) == 'landing_pages_manage.php' ? 'text-white' : 'text-slate-300 group-hover:text-white'; ?>">TLS Landing Pages</span>
        </a>
        <!-- Navigation Menu -->
        <a href="menu_manage.php"
            class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors group <?php echo basename($_SERVER['PHP_SELF']) == 'menu_manage.php' ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white'; ?>"
            :class="collapsed ? 'justify-center' : ''" title="Navigation Menu">
            <div
                class="<?php echo basename($_SERVER['PHP_SELF']) == 'menu_manage.php' ? 'text-blue-500' : 'text-slate-400 group-hover:text-white'; ?>">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                    </path>
                </svg>
            </div>
            <span x-show="!collapsed"
                class="whitespace-nowrap <?php echo basename($_SERVER['PHP_SELF']) == 'menu_manage.php' ? 'text-white' : 'text-slate-300 group-hover:text-white'; ?>">Navigation
                Menu</span>
        </a>

        <!-- Koperasi Loan Calculator Settings -->
        <a href="koperasi_calculator_manage.php" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors group"
            :class="collapsed ? 'justify-center' : ''" title="Loan Calculator">
            <div
                class="<?php echo basename($_SERVER['PHP_SELF']) == 'koperasi_calculator_manage.php' ? 'text-teal-400' : 'text-slate-400 group-hover:text-white'; ?>">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <rect x="4" y="2" width="16" height="20" rx="2" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                    <line x1="8" y1="6" x2="16" y2="6" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                    <line x1="8" y1="10" x2="16" y2="10" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                    <circle cx="8" cy="14" r="1"/>
                    <circle cx="12" cy="14" r="1"/>
                    <circle cx="16" cy="14" r="1"/>
                    <circle cx="8" cy="18" r="1"/>
                    <circle cx="12" cy="18" r="1"/>
                    <circle cx="16" cy="18" r="1"/>
                </svg>
            </div>
            <span x-show="!collapsed"
                class="whitespace-nowrap <?php echo basename($_SERVER['PHP_SELF']) == 'koperasi_calculator_manage.php' ? 'text-white' : 'text-slate-300 group-hover:text-white'; ?>">Loan Calculator</span>
        </a>

        <!-- Property Calculator Presets -->
        <a href="property_calculator_manage.php" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors group"
            :class="collapsed ? 'justify-center' : ''" title="Property Calculator">
            <div
                class="<?php echo basename($_SERVER['PHP_SELF']) == 'property_calculator_manage.php' ? 'text-gold-500' : 'text-slate-400 group-hover:text-white'; ?>">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <span x-show="!collapsed"
                class="whitespace-nowrap <?php echo basename($_SERVER['PHP_SELF']) == 'property_calculator_manage.php' ? 'text-white' : 'text-slate-300 group-hover:text-white'; ?>">Prop Calculator</span>
        </a>

        <!-- Advertisements -->
        <a href="ads_manage.php" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors group"
            :class="collapsed ? 'justify-center' : ''" title="Advertisements">
            <div
                class="<?php echo basename($_SERVER['PHP_SELF']) == 'ads_manage.php' ? 'text-pink-400' : 'text-slate-400 group-hover:text-white'; ?>">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                </svg>
            </div>
            <span x-show="!collapsed"
                class="whitespace-nowrap <?php echo basename($_SERVER['PHP_SELF']) == 'ads_manage.php' ? 'text-white' : 'text-slate-300 group-hover:text-white'; ?>">Advertisements</span>
        </a>

        <!-- Digital Cards -->
        <a href="business_cards_manage.php" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors group"
            :class="collapsed ? 'justify-center' : ''" title="Digital Cards">
            <div
                class="<?php echo basename($_SERVER['PHP_SELF']) == 'business_cards_manage.php' ? 'text-blue-400' : 'text-slate-400 group-hover:text-white'; ?>">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                </svg>
            </div>
            <span x-show="!collapsed"
                class="whitespace-nowrap <?php echo basename($_SERVER['PHP_SELF']) == 'business_cards_manage.php' ? 'text-white' : 'text-slate-300 group-hover:text-white'; ?>">Digital Cards</span>
        </a>
        <!-- Header Notifications -->
        <a href="header_notifications_manage.php"
            class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors group"
            :class="collapsed ? 'justify-center' : ''" title="Header Notifications">
            <div
                class="<?php echo basename($_SERVER['PHP_SELF']) == 'header_notifications_manage.php' ? 'text-orange-400' : 'text-slate-400 group-hover:text-white'; ?>">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </div>
            <span x-show="!collapsed"
                class="whitespace-nowrap <?php echo basename($_SERVER['PHP_SELF']) == 'header_notifications_manage.php' ? 'text-white' : 'text-slate-300 group-hover:text-white'; ?>">Header
                Notifications</span>
        </a>

        <!-- Divider Label -->
        <div class="pt-4 pb-2 px-3 text-xs font-bold uppercase tracking-widest text-slate-600 truncate"
            x-show="!collapsed">System</div>
        <div class="my-2 border-t border-slate-800" x-show="collapsed"></div>

        <!-- Frontend Translations -->
        <a href="translations_manage.php" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors group"
            :class="collapsed ? 'justify-center' : ''" title="Frontend Translations">
            <div
                class="<?php echo basename($_SERVER['PHP_SELF']) == 'translations_manage.php' ? 'text-gold-500' : 'text-slate-400 group-hover:text-white'; ?>">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M14 21l4-9 4 9m-6.5-3h5M12.5 5C11.7 8.6 9.6 11.7 6.5 14" />
                </svg>
            </div>
            <span x-show="!collapsed"
                class="whitespace-nowrap <?php echo basename($_SERVER['PHP_SELF']) == 'translations_manage.php' ? 'text-white' : 'text-slate-300 group-hover:text-white'; ?>">Translations</span>
        </a>
        <!-- Site Settings -->
        <a href="site_settings.php" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors group"
            :class="collapsed ? 'justify-center' : ''" title="Site Settings">
            <div
                class="<?php echo basename($_SERVER['PHP_SELF']) == 'site_settings.php' ? 'text-green-400' : 'text-slate-400 group-hover:text-white'; ?>">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <span x-show="!collapsed"
                class="whitespace-nowrap <?php echo basename($_SERVER['PHP_SELF']) == 'site_settings.php' ? 'text-white' : 'text-slate-300 group-hover:text-white'; ?>">Site
                Settings</span>
        </a>

        <!-- Feedback -->
        <a href="feedback_manage.php"
            class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors group relative"
            :class="collapsed ? 'justify-center' : ''" title="Feedback">
            <div
                class="<?php echo basename($_SERVER['PHP_SELF']) == 'feedback_manage.php' ? 'text-cyan-400' : 'text-slate-400 group-hover:text-white'; ?>">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
            </div>
            <span x-show="!collapsed"
                class="whitespace-nowrap <?php echo basename($_SERVER['PHP_SELF']) == 'feedback_manage.php' ? 'text-white' : 'text-slate-300 group-hover:text-white'; ?>">Feedback</span>
            <?php if ($unreadFeedback > 0): ?>
                <span class="absolute top-2 right-2 flex h-3 w-3">
                    <span
                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-gold-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-gold-500"></span>
                </span>
            <?php endif; ?>
        </a>

    </nav>

    <!-- User Section at Bottom -->
    <div class="p-3 border-t border-slate-800 bg-slate-900/50">
        <a href="executive_profile_manage.php"
            class="flex items-center gap-3 p-2 rounded hover:bg-slate-800 transition-colors group"
            :class="collapsed ? 'justify-center' : ''">
            <!-- Avatar / Initials Logic -->
            <div
                class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-slate-300 shrink-0 overflow-hidden border border-slate-600">
                <?php if (!empty($sidebarAvatarUrl) && file_exists('../' . $sidebarAvatarUrl)): ?>
                    <img src="../<?php echo htmlspecialchars($sidebarAvatarUrl); ?>" class="w-full h-full object-cover">
                <?php else: ?>
                    <span class="text-xs font-bold"><?php echo $sidebarInitials; ?></span>
                <?php endif; ?>
            </div>

            <div class="overflow-hidden" x-show="!collapsed">
                <p class="text-sm font-medium text-white truncate"><?php echo htmlspecialchars($sidebarFullName); ?>
                </p>
                <p class="text-xs text-slate-500 truncate">Admin</p>
            </div>
        </a>

        <a href="logout.php"
            class="mt-1 flex items-center gap-3 p-2 rounded hover:bg-red-900/20 text-red-400 hover:text-red-300 transition-colors group"
            :class="collapsed ? 'justify-center' : ''" title="Logout">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            <span x-show="!collapsed" class="text-sm whitespace-nowrap">Logout</span>
        </a>
    </div>
</aside>

<!-- Mobile Bottom Navigation (Native App Style) -->
<?php include 'mobile_nav.php'; ?>