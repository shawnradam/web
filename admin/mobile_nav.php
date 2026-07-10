<?php
$currentAdminPage = basename($_SERVER['PHP_SELF']);
$adminMenuGroups = [
    'Home' => [
        ['href' => '../index.php', 'title' => 'Home', 'desc' => 'Open website home', 'color' => 'gold', 'icon' => 'home'],
        ['href' => 'dashboard.php', 'title' => 'Dashboard', 'desc' => 'Admin overview', 'color' => 'blue', 'icon' => 'dashboard'],
    ],
    'Content' => [
        ['href' => 'posts.php', 'title' => 'Posts', 'desc' => 'Manage articles', 'color' => 'purple', 'icon' => 'posts'],
        ['href' => 'post_create.php', 'title' => 'Create Post', 'desc' => 'Write new article', 'color' => 'green', 'icon' => 'create'],
        ['href' => 'categories.php', 'title' => 'Categories', 'desc' => 'Organize topics', 'color' => 'yellow', 'icon' => 'categories'],
        ['href' => 'about_manage.php', 'title' => 'About Page', 'desc' => 'Public about content', 'color' => 'cyan', 'icon' => 'about'],
    ],
    'TLS Landing Pages' => [
        ['href' => 'landing_pages_manage.php', 'title' => 'TLS Landing Pages', 'desc' => 'Manage land lot pages', 'color' => 'gold', 'icon' => 'landing'],
        ['href' => 'menu_manage.php', 'title' => 'Navigation Menu', 'desc' => 'Website menu items', 'color' => 'blue', 'icon' => 'menu'],
    ],
    'Tools' => [
        ['href' => 'koperasi_calculator_manage.php', 'title' => 'Loan Calculator', 'desc' => 'Koperasi presets', 'color' => 'teal', 'icon' => 'calculator'],
        ['href' => 'property_calculator_manage.php', 'title' => 'Property Calculator', 'desc' => 'Property presets', 'color' => 'emerald', 'icon' => 'building'],
        ['href' => 'business_cards_manage.php', 'title' => 'Digital Cards', 'desc' => 'Business cards', 'color' => 'blue', 'icon' => 'card'],
    ],
    'Marketing' => [
        ['href' => 'ads_manage.php', 'title' => 'Advertisements', 'desc' => 'Campaign ads', 'color' => 'pink', 'icon' => 'megaphone'],
        ['href' => 'header_notifications_manage.php', 'title' => 'Header Alerts', 'desc' => 'Bell notifications', 'color' => 'orange', 'icon' => 'bell'],
        ['href' => 'feedback_manage.php', 'title' => 'Feedback', 'desc' => 'Visitor messages', 'color' => 'cyan', 'icon' => 'feedback'],
    ],
    'System' => [
        ['href' => 'translations_manage.php', 'title' => 'Translations', 'desc' => 'Frontend language text', 'color' => 'gold', 'icon' => 'translations'],
        ['href' => 'site_settings.php', 'title' => 'Site Settings', 'desc' => 'Global configuration', 'color' => 'slate', 'icon' => 'settings'],
        ['href' => 'profile.php', 'title' => 'Profile', 'desc' => 'Admin account', 'color' => 'slate', 'icon' => 'profile'],
    ],
];

function admin_mobile_color_classes($color)
{
    $map = [
        'purple' => 'bg-purple-500/15 text-purple-300 group-hover:bg-purple-500 group-hover:text-white',
        'green' => 'bg-green-500/15 text-green-300 group-hover:bg-green-500 group-hover:text-white',
        'yellow' => 'bg-yellow-500/15 text-yellow-300 group-hover:bg-yellow-500 group-hover:text-slate-950',
        'cyan' => 'bg-cyan-500/15 text-cyan-300 group-hover:bg-cyan-500 group-hover:text-white',
        'gold' => 'bg-gold-500/15 text-gold-500 group-hover:bg-gold-500 group-hover:text-navy-900',
        'blue' => 'bg-blue-500/15 text-blue-300 group-hover:bg-blue-500 group-hover:text-white',
        'teal' => 'bg-teal-500/15 text-teal-300 group-hover:bg-teal-500 group-hover:text-white',
        'emerald' => 'bg-emerald-500/15 text-emerald-300 group-hover:bg-emerald-500 group-hover:text-white',
        'pink' => 'bg-pink-500/15 text-pink-300 group-hover:bg-pink-500 group-hover:text-white',
        'orange' => 'bg-orange-500/15 text-orange-300 group-hover:bg-orange-500 group-hover:text-white',
        'slate' => 'bg-slate-500/15 text-slate-300 group-hover:bg-slate-500 group-hover:text-white',
    ];
    return $map[$color] ?? $map['slate'];
}
function admin_mobile_icon_svg($icon)
{
    $paths = [
        'home' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 11l9-8 9 8M5 10v10h5v-6h4v6h5V10" />',
        'dashboard' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h3a2 2 0 012 2v3a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM13 6a2 2 0 012-2h3a2 2 0 012 2v3a2 2 0 01-2 2h-3a2 2 0 01-2-2V6zM4 15a2 2 0 012-2h3a2 2 0 012 2v3a2 2 0 01-2 2H6a2 2 0 01-2-2v-3zM13 15a2 2 0 012-2h3a2 2 0 012 2v3a2 2 0 01-2 2h-3a2 2 0 01-2-2v-3z" />',
        'posts' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4h7l5 5v11H7a2 2 0 01-2-2V6a2 2 0 012-2zM14 4v5h5M8 13h8M8 17h6" />',
        'create' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-6M16.5 3.5a2.121 2.121 0 113 3L12 14l-4 1 1-4 7.5-7.5z" />',
        'categories' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M4 5a2 2 0 012-2h5l9 9a2 2 0 010 2.83l-5.17 5.17a2 2 0 01-2.83 0L4 11V5z" />',
        'about' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11v5m0-8h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
        'landing' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5h16v14H4V5zM4 10h16M8 14h5M8 17h8" />',
        'menu' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h10M4 17h16" />',
        'calculator' => '<rect x="6" y="3" width="12" height="18" rx="2" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6M9 11h.01M12 11h.01M15 11h.01M9 15h.01M12 15h.01M15 15h.01M9 18h6" />',
        'building' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 21h16M6 21V5a2 2 0 012-2h8a2 2 0 012 2v16M9 7h1M14 7h1M9 11h1M14 11h1M9 15h1M14 15h1" />',
        'card' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V7zM4 10h16M8 15h4M15 15h2" />',
        'megaphone' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 8a3 3 0 010 6M6 14l2 5h3l-2-5M5 14a3 3 0 010-6h3l10-4v14L8 14H5z" />',
        'bell' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0a3 3 0 01-6 0" />',
        'feedback' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M5 5h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 4v-4H5a2 2 0 01-2-2V7a2 2 0 012-2z" />',
        'translations' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5h9M9 3v2m1.5 10A15 15 0 016 9m1-4a15 15 0 006 10M14 21l4-9 4 9M16 17h4" />',
        'settings' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.3 4.3c.4-1.7 2.9-1.7 3.4 0a1.7 1.7 0 002.5 1.1c1.5-.9 3.3.8 2.4 2.4a1.7 1.7 0 001.1 2.5c1.7.4 1.7 2.9 0 3.4a1.7 1.7 0 00-1.1 2.5c.9 1.5-.8 3.3-2.4 2.4a1.7 1.7 0 00-2.5 1.1c-.4 1.7-2.9 1.7-3.4 0a1.7 1.7 0 00-2.5-1.1c-1.5.9-3.3-.8-2.4-2.4a1.7 1.7 0 00-1.1-2.5c-1.7-.4-1.7-2.9 0-3.4a1.7 1.7 0 001.1-2.5c-.9-1.5.8-3.3 2.4-2.4a1.7 1.7 0 002.5-1.1z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />',
        'profile' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12a4 4 0 100-8 4 4 0 000 8zM4 21a8 8 0 0116 0" />',
    ];
    $path = $paths[$icon] ?? $paths['menu'];
    return '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">' . $path . '</svg>';
}
?>

<div id="mobileAdminFooter"
    class="md:hidden fixed bottom-0 left-0 right-0 bg-navy-900 border-t border-slate-800 shadow-2xl z-50 flex items-center justify-center gap-1 h-[72px] safe-padding-bottom">
    <a href="dashboard.php" class="flex flex-col items-center justify-center h-full gap-1 <?php echo $currentAdminPage === 'dashboard.php' ? 'text-gold-500' : 'text-slate-400 hover:text-white'; ?>">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
        <span class="text-[9px] font-medium">Dashboard</span>
    </a>
    <a href="posts.php" class="flex flex-col items-center justify-center h-full gap-1 <?php echo in_array($currentAdminPage, ['posts.php', 'post_create.php'], true) ? 'text-gold-500' : 'text-slate-400 hover:text-white'; ?>">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10l6 6v8a2 2 0 01-2 2zM14 4v6h6M8 13h8M8 17h8" /></svg>
        <span class="text-[9px] font-medium">Posts</span>
    </a>
    <a href="landing_pages_manage.php" class="flex flex-col items-center justify-center h-full gap-1 <?php echo $currentAdminPage === 'landing_pages_manage.php' ? 'text-gold-500' : 'text-slate-400 hover:text-white'; ?>">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7l8-4 6 4v14M9 21v-6h6v6M8 10h.01M12 10h.01M16 10h.01" /></svg>
        <span class="text-[9px] font-medium">TLS</span>
    </a>
    <a href="feedback_manage.php" class="flex flex-col items-center justify-center h-full gap-1 relative <?php echo $currentAdminPage === 'feedback_manage.php' ? 'text-gold-500' : 'text-slate-400 hover:text-white'; ?>">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
        <span class="text-[9px] font-medium">Feedback</span>
        <?php if (isset($unreadFeedback) && $unreadFeedback > 0): ?>
            <span class="absolute top-2 right-3 h-4 min-w-4 px-1 rounded-full bg-red-500 text-[9px] text-white flex items-center justify-center border border-navy-900"><?php echo $unreadFeedback > 9 ? '9+' : $unreadFeedback; ?></span>
        <?php endif; ?>
    </a>
    <a href="site_settings.php" class="flex flex-col items-center justify-center h-full gap-1 <?php echo $currentAdminPage === 'site_settings.php' ? 'text-gold-500' : 'text-slate-400 hover:text-white'; ?>">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
        <span class="text-[9px] font-medium">Settings</span>
    </a>
</div>
<div x-show="sidebarOpen" class="md:hidden fixed inset-0 z-[60] pointer-events-none" style="display: none;" x-cloak>
    <div x-show="sidebarOpen" x-data="{ mobileMenuCollapsed: (function(){ try { return localStorage.getItem('mobileAdminMenuCollapsed') === 'true' } catch(e){ return false } })() }" x-init="$watch('mobileMenuCollapsed', val => { try { localStorage.setItem('mobileAdminMenuCollapsed', val) } catch(e){} })"
        x-transition:enter="transform transition ease-out duration-300" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transform transition ease-in duration-250" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
        class="pointer-events-auto absolute inset-y-0 left-0 bg-[#101517] flex flex-col pb-safe shadow-[12px_0_40px_rgba(0,0,0,0.55)] transition-all duration-300" :class="mobileMenuCollapsed ? 'w-20' : 'w-[78vw] max-w-[300px]'">
        <div class="px-3 py-3 border-b border-slate-800 flex items-center bg-[#151a1d]" :class="mobileMenuCollapsed ? 'justify-center gap-1' : 'justify-between'">
            <div x-show="!mobileMenuCollapsed" x-cloak>
                <h3 class="text-white text-sm font-semibold tracking-wide">Admin Menu</h3>
                <p class="text-slate-500 text-[11px] mt-0.5">Management console</p>
            </div>
            <div class="flex items-center gap-1">
                <button @click="mobileMenuCollapsed = !mobileMenuCollapsed" class="text-slate-400 hover:text-white p-2 rounded hover:bg-white/10 transition-colors" :title="mobileMenuCollapsed ? 'Expand menu' : 'Collapse menu'">
                    <svg class="w-5 h-5 transition-transform" :class="mobileMenuCollapsed ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                </button>
                <button @click="sidebarOpen = false" class="text-slate-400 hover:text-white p-2 rounded hover:bg-white/10 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </div>

        <div class="admin-mobile-menu-scroll flex-1 overflow-y-auto py-2">
            <?php foreach ($adminMenuGroups as $groupLabel => $items): ?>
                <section>
                    <h4 x-show="!mobileMenuCollapsed" x-cloak class="px-4 pt-4 pb-1 text-[10px] uppercase tracking-[0.18em] text-slate-500 font-bold"><?php echo htmlspecialchars($groupLabel); ?></h4>
                    <div class="space-y-0.5">
                        <?php foreach ($items as $item): ?>
                            <?php $active = $currentAdminPage === basename($item['href']); ?>
                            <a href="<?php echo htmlspecialchars($item['href']); ?>" title="<?php echo htmlspecialchars($item['title']); ?>" class="group flex h-10 items-center gap-3 border-l-2 text-sm transition-colors <?php echo $active ? 'border-gold-500 bg-slate-800/70 text-white' : 'border-transparent text-slate-300 hover:bg-slate-800/60 hover:text-white'; ?>" :class="mobileMenuCollapsed ? 'justify-center px-0' : 'px-4'">
                                <span class="w-5 shrink-0 <?php echo $active ? 'text-gold-500' : 'text-slate-500 group-hover:text-gold-500'; ?>">
                                    <?php echo admin_mobile_icon_svg($item['icon'] ?? 'menu'); ?>
                                </span>
                                <span x-show="!mobileMenuCollapsed" x-cloak class="min-w-0 flex-1 truncate"><?php echo htmlspecialchars($item['title']); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
            <div class="h-4"></div>
        </div>
    
        <div class="border-t border-slate-800 bg-slate-950/55 p-3">
            <a href="profile.php" class="flex items-center gap-3 rounded-lg p-2 text-slate-300 hover:bg-slate-800 hover:text-white transition-colors" :class="mobileMenuCollapsed ? 'justify-center' : ''" title="Profile">
                <div class="w-9 h-9 rounded-full bg-gold-500 text-navy-900 flex items-center justify-center text-xs font-bold overflow-hidden border border-gold-500/40 shrink-0">
                    <?php if (!empty($sidebarAvatarUrl) && file_exists('../' . $sidebarAvatarUrl)): ?>
                        <img src="../<?php echo htmlspecialchars($sidebarAvatarUrl); ?>" class="w-full h-full object-cover" alt="Admin profile">
                    <?php else: ?>
                        <span><?php echo htmlspecialchars($sidebarInitials); ?></span>
                    <?php endif; ?>
                </div>
                <div x-show="!mobileMenuCollapsed" x-cloak class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-white"><?php echo htmlspecialchars($sidebarFullName); ?></p>
                    <p class="truncate text-[11px] text-slate-500">Administrator</p>
                </div>
            </a>
            <a href="logout.php" class="mt-1 flex items-center gap-3 rounded-lg p-2 text-red-400 hover:bg-red-950/40 hover:text-red-300 transition-colors" :class="mobileMenuCollapsed ? 'justify-center' : ''" title="Logout">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                <span x-show="!mobileMenuCollapsed" x-cloak class="text-sm font-medium">Logout</span>
            </a>
        </div>    </div>
</div>

<style>
    .pb-safe { padding-bottom: env(safe-area-inset-bottom); }
    .safe-padding-bottom { padding-bottom: env(safe-area-inset-bottom); min-height: calc(4.5rem + env(safe-area-inset-bottom)); }
    #mobileAdminFooter > a { width: min(19vw, 76px); gap: .2rem; }
    #mobileAdminFooter svg { width: 1.55rem; height: 1.55rem; }
    #mobileAdminFooter span { font-size: 10px; line-height: 1; }
    .admin-mobile-menu-scroll { scrollbar-width: thin; scrollbar-color: rgba(212,175,55,.55) rgba(15,23,42,.35); }
    .admin-mobile-menu-scroll::-webkit-scrollbar { width: 6px; }
    .admin-mobile-menu-scroll::-webkit-scrollbar-track { background: rgba(15,23,42,.35); }
    .admin-mobile-menu-scroll::-webkit-scrollbar-thumb { background: rgba(212,175,55,.55); border-radius: 999px; }
    .admin-mobile-menu-scroll::-webkit-scrollbar-thumb:hover { background: rgba(212,175,55,.85); }
    [x-cloak] { display: none !important; }
</style>

<script>
    function adjustMobilePadding() {
        document.body.style.paddingBottom = window.innerWidth < 768 ? '100px' : '0px';
    }
    adjustMobilePadding();
    window.addEventListener('resize', adjustMobilePadding);
</script>
