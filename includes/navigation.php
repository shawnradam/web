<?php
require_once 'includes/ad_helper.php';
require_once 'includes/lang.php';
require_once 'admin/db_connect.php'; // Ensure DB connection

// Fetch Menu Data
$menuSections = [];
$menuItems = [];
try {
    $stmt = $pdo->query("SELECT * FROM menu_sections WHERE is_active = 1 ORDER BY display_order ASC");
    $menuSections = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("SELECT * FROM menu_items ORDER BY display_order ASC");
    $rawItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rawItems as $item) {
        $menuItems[$item['section_id']][] = $item;
    }
} catch (Exception $e) {
    // Fallback menu if tables don't exist
    $menuSections = [
        ['id' => 1, 'label' => 'Home', 'url' => 'index.php', 'description' => '', 'image_url' => ''],
        ['id' => 2, 'label' => 'Properties', 'url' => 'properties.php', 'description' => '', 'image_url' => ''],
        ['id' => 3, 'label' => 'Loans Financing', 'url' => 'finance.php', 'description' => '', 'image_url' => ''],
        ['id' => 4, 'label' => 'Calculators', 'url' => 'calculators.php', 'description' => '', 'image_url' => ''],
        ['id' => 5, 'label' => 'Blog', 'url' => 'blog.php', 'description' => '', 'image_url' => ''],
        ['id' => 6, 'label' => 'Contact', 'url' => 'contact.php', 'description' => '', 'image_url' => '']
    ];
}

$defaultMenuImages = [
    'home' => 'assets/menu/home-mega-menu.png',
    'properties' => 'assets/menu/properties-mega-menu.png',
    'loans financing' => 'assets/menu/loans-financing-mega-menu.png',
    'land lot' => 'assets/menu/land-lot-mega-menu.png',
    'land lots' => 'assets/menu/land-lot-mega-menu.png',
    'blog' => 'assets/menu/blog-mega-menu.png',
    'contact' => 'assets/menu/contact-mega-menu.png',
];

foreach ($menuSections as &$section) {
    $labelKey = strtolower(trim($section['label'] ?? ''));
    if (empty($section['image_url']) && isset($defaultMenuImages[$labelKey])) {
        $section['image_url'] = $defaultMenuImages[$labelKey];
    }
}
unset($section);
?>

<!-- Fixed Header Wrapper to prevent overlap -->
<div id="siteHeader" class="fixed top-0 left-0 w-full flex flex-col transition-all duration-300" style="z-index: 9999;">

    <!-- 1. Header Ads (Top) -->
    <?php if (isset($pdo))
        renderAds($pdo, 'header'); ?>

    <!-- 2. Navigation Bar (Bottom of Header) -->
    <nav x-data="{ 
        mobileOpen: false, 
        activeDesktopMenu: null, 
        isScrolled: false, 
        hoverTimeout: null,
        
        init() {
            window.addEventListener('scroll', () => {
                this.isScrolled = window.scrollY > 20;
                this.adjustBodyPadding();
            });
            this.$nextTick(() => this.adjustBodyPadding());
            window.addEventListener('resize', () => this.adjustBodyPadding());
        },

        adjustBodyPadding() {
            const header = document.getElementById('siteHeader');
            if(header) {
                document.documentElement.style.setProperty('--header-height', header.offsetHeight + 'px');
            }
        },

        handleMouseEnter(id) {
            if (this.hoverTimeout) clearTimeout(this.hoverTimeout);
            this.activeDesktopMenu = id;
        },

        handleMouseLeave() {
            this.hoverTimeout = setTimeout(() => {
                this.activeDesktopMenu = null;
            }, 200);
        }
    }" class="w-full transition-all duration-500 relative"
        :class="isScrolled || mobileOpen || activeDesktopMenu ? 'bg-navy-900 border-b border-white/5' : 'bg-navy-900/90 backdrop-blur-sm'"
        @mouseleave="handleMouseLeave">

        <div class="max-w-7xl mx-auto px-6 h-20 flex justify-between items-center relative bg-navy-900/0" style="z-index: 10001;">
            <!-- Logo -->
            <a href="<?php echo htmlspecialchars(lang_url('index.php')); ?>"
                class="flex flex-col z-50 relative group items-start md:items-center text-left md:text-center transition-opacity duration-300"
                :class="mobileOpen ? 'opacity-0 pointer-events-none' : 'opacity-100'">
                <span
                    class="text-xl md:text-2xl font-serif text-white tracking-wider group-hover:text-gold-500 transition-colors uppercase">SHAWN
                    RADAM</span>
                <span
                    class="text-[0.4rem] md:text-[0.5rem] text-slate-400 uppercase tracking-[0.4em] md:tracking-[0.55em] -mt-1 w-full text-left md:text-center"><?php echo e_t('site.tagline'); ?></span>
            </a>

            <!-- Desktop Nav Items (Dynamic) -->
            <div class="hidden md:flex space-x-10 h-full items-center">
                <?php foreach ($menuSections as $section):
                    $hasSubItems = !empty($menuItems[$section['id']]);
                    $sectionIdStr = 'section_' . $section['id'];
                    ?>
                    <div class="h-full flex items-center" <?php if ($hasSubItems): ?>
                            @mouseenter="handleMouseEnter('<?php echo $sectionIdStr; ?>')" <?php else: ?>
                            @mouseenter="activeDesktopMenu = null" <?php endif; ?>>
                        <a href="<?php echo htmlspecialchars(lang_url($section['url'])); ?>"
                            class="text-sm uppercase tracking-widest transition-colors py-2 border-b-2"
                            :class="activeDesktopMenu === '<?php echo $sectionIdStr; ?>' ? 'text-white border-gold-500' : 'text-slate-300 border-transparent hover:text-white'">
                            <?php echo htmlspecialchars($section['label']); ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Public Language Switcher (Desktop) -->
            <div class="hidden md:flex items-center gap-1 ml-4 text-[11px] font-bold uppercase tracking-widest">
                <a href="<?php echo htmlspecialchars(switch_lang_url('en')); ?>"
                    class="px-2 py-1 rounded transition-colors <?php echo current_lang() === 'en' ? 'bg-gold-500 text-navy-900' : 'text-slate-400 hover:text-white'; ?>">
                    EN
                </a>
                <span class="text-slate-700">/</span>
                <a href="<?php echo htmlspecialchars(switch_lang_url('ms')); ?>"
                    class="px-2 py-1 rounded transition-colors <?php echo current_lang() === 'ms' ? 'bg-gold-500 text-navy-900' : 'text-slate-400 hover:text-white'; ?>">
                    BM
                </a>
            </div>
            <div class="hidden md:flex items-center ml-3">
                <?php include 'includes/header_notification.php'; ?>
            </div>

            <!-- Admin Controls (Desktop) -->
            <div class="hidden md:flex items-center ml-4">
                <div id="navLoginIcon" class="hidden"></div>

                <!-- Admin Profile -->
                <div id="navAdminControls" class="hidden relative" x-data="{ adminOpen: false }">
                    <div class="flex items-center gap-3">
                        <!-- Notification Bell (Simplified for brevity, full logic in script below) -->
                        <div class="relative" x-data="{ notifOpen: false }">
                            <button @click="notifOpen = !notifOpen" class="relative">
                                <svg class="w-5 h-5 text-slate-400 hover:text-gold-500 transition-colors" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <span id="navNotifBadge"
                                    class="hidden absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full w-4 h-4 flex items-center justify-center">0</span>
                            </button>
                            <!-- Notif Dropdown (Using existing JS structure) -->
                            <div x-show="notifOpen" @click.away="notifOpen = false" x-cloak
                                class="absolute right-0 top-full mt-2 w-80 bg-navy-800 border border-gold-500 rounded-lg shadow-2xl overflow-hidden z-50">
                                <div class="p-3 border-b border-slate-700 flex items-center justify-between">
                                    <h3 class="text-white font-bold text-sm">Notifications</h3>
                                </div>
                                <div id="navNotificationsList" class="overflow-y-auto max-h-96"></div>
                            </div>
                        </div>

                        <!-- Profile Button -->
                        <button @click="adminOpen = !adminOpen" class="relative">
                            <div
                                class="w-8 h-8 rounded-full bg-gold-500 text-navy-900 flex items-center justify-center text-sm font-bold hover:bg-gold-400 transition-colors">
                                <span id="navAdminInitials">A</span>
                            </div>
                        </button>
                    </div>

                    <!-- Dropdown Menu -->
                    <div x-show="adminOpen" @click.away="adminOpen = false" x-cloak
                        class="absolute right-0 top-full mt-2 w-64 bg-navy-800 border border-gold-500 rounded-lg shadow-2xl overflow-hidden z-50">
                        <div class="p-3 border-b border-slate-700">
                            <p class="text-white font-bold text-sm" id="navAdminUsername">Admin</p>
                            <p class="text-slate-400 text-xs" id="navAdminRole">Administrator</p>
                        </div>
                        <div class="py-1">
                            <a href="admin/dashboard.php"
                                class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-700 hover:text-white transition-colors">Dashboard</a>
                            <a href="admin/feedback_manage.php"
                                class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-700 hover:text-white transition-colors">Feedback</a>
                            <a href="admin/menu_manage.php"
                                class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-700 hover:text-white transition-colors">Navigation
                                Menu</a>
                            <div class="border-t border-slate-700 my-1"></div>
                            <a href="admin/logout.php"
                                class="block px-4 py-2 text-sm text-red-400 hover:bg-slate-700 hover:text-red-300 transition-colors">Logout</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Header Actions -->
            <div class="md:hidden flex items-center gap-3 relative" style="z-index: 10000;">
                <div x-show="!mobileOpen" x-cloak>
                    <?php include 'includes/header_notification.php'; ?>
                </div>
                <button @click="mobileOpen = !mobileOpen" class="text-white relative focus:outline-none">
                <svg x-show="!mobileOpen" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 6h16M4 12h16m-7 6h7" />
                </svg>
                <svg x-show="mobileOpen" x-cloak class="w-8 h-8 transition-transform duration-300 rotate-90" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            </div>
        </div>

        <!-- Desktop Mega Menu Dropdown (Dynamic) -->
        <div class="absolute top-20 left-0 w-full bg-navy-900 border-b border-gold-500/30 shadow-2xl overflow-hidden transition-all duration-500 ease-in-out origin-top"
            :class="activeDesktopMenu ? 'max-h-[400px] opacity-100 translate-y-0' : 'max-h-0 opacity-0 -translate-y-4'"
            style="z-index: 10000;"
            @mouseenter="if(hoverTimeout) clearTimeout(hoverTimeout)" @mouseleave="handleMouseLeave">

            <div class="max-w-7xl mx-auto flex h-80">
                <?php foreach ($menuSections as $section):
                    $sectionIdStr = 'section_' . $section['id'];
                    $subItems = $menuItems[$section['id']] ?? [];
                    if (empty($subItems))
                        continue; // Don't create dropdown for empty sections
                    ?>
                    <div x-show="activeDesktopMenu === '<?php echo $sectionIdStr; ?>'" class="w-full flex h-full"
                        style="display: none;">
                        <div class="w-1/3 p-10 flex flex-col justify-center border-r border-slate-800 animate-fade-in">
                            <h3 class="text-3xl font-serif text-white mb-2">
                                <?php echo htmlspecialchars($section['label']); ?>
                            </h3>
                            <div class="w-12 h-1 bg-gold-500 mb-6"></div>
                            <p class="text-slate-400 mb-8 leading-relaxed font-light">
                                <?php echo htmlspecialchars($section['description']); ?>
                            </p>
                            <ul class="space-y-3">
                                <?php foreach ($subItems as $item): ?>
                                    <li>
                                        <a href="<?php echo htmlspecialchars(lang_url($item['url'])); ?>"
                                            class="text-sm text-slate-300 hover:text-gold-500 hover:translate-x-1 transition-all inline-block uppercase tracking-wider">
                                            &rarr; <?php echo htmlspecialchars($item['label']); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="w-2/3 relative overflow-hidden">
                            <div
                                class="absolute inset-0 bg-gradient-to-r from-navy-900 via-navy-900/20 to-transparent z-10">
                            </div>
                            <?php if (!empty($section['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($section['image_url']); ?>"
                                    class="w-full h-full object-cover opacity-80 animate-fade-in">
                            <?php else: ?>
                                <div class="w-full h-full bg-slate-800"></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Mobile Full Screen Menu (Dynamic) -->
        <div class="fixed inset-0 bg-navy-900 transform transition-transform duration-500 ease-in-out md:hidden translate-x-full"
            :class="mobileOpen ? '!translate-x-0' : ''" style="top: 0; z-index: 9999;">

            <div id="mobileMenuScrollContainer" class="flex flex-col h-full pt-24 pb-8 px-6 overflow-y-auto" style="scrollbar-width: none; -ms-overflow-style: none;">
                <style>
                    #mobileMenuScrollContainer::-webkit-scrollbar {
                        display: none;
                    }
                </style>
                <h2 class="text-xs text-slate-500 uppercase tracking-widest mb-8 text-center"><?php echo e_t('nav.mobile_prompt'); ?></h2>
                <div class="flex justify-center gap-2 mb-8 text-xs font-bold uppercase tracking-widest">
                    <a href="<?php echo htmlspecialchars(switch_lang_url('en')); ?>"
                        class="px-4 py-2 rounded-full border transition-colors <?php echo current_lang() === 'en' ? 'border-gold-500 bg-gold-500 text-navy-900' : 'border-slate-700 text-slate-400'; ?>">
                        EN
                    </a>
                    <a href="<?php echo htmlspecialchars(switch_lang_url('ms')); ?>"
                        class="px-4 py-2 rounded-full border transition-colors <?php echo current_lang() === 'ms' ? 'border-gold-500 bg-gold-500 text-navy-900' : 'border-slate-700 text-slate-400'; ?>">
                        BM
                    </a>
                </div>
                <div class="space-y-6 flex-1">
                    <?php
                    $counter = 1;
                    foreach ($menuSections as $section):
                        $num = str_pad($counter++, 2, '0', STR_PAD_LEFT);
                        $subItems = $menuItems[$section['id']] ?? [];
                        ?>
                        <div class="space-y-4">
                            <a href="<?php echo htmlspecialchars(lang_url($section['url'])); ?>"
                                class="block group relative h-32 w-full overflow-hidden rounded border border-slate-700 hover:border-gold-500 transition-colors">
                                <div class="absolute inset-0">
                                    <?php if (!empty($section['image_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($section['image_url']); ?>"
                                            class="w-full h-full object-cover opacity-40 group-hover:scale-105 transition-transform duration-700" />
                                    <?php else: ?>
                                        <div class="w-full h-full bg-slate-800 opacity-40"></div>
                                    <?php endif; ?>
                                    <div class="absolute inset-0 bg-gradient-to-r from-navy-900/90 to-transparent"></div>
                                </div>
                                <div class="absolute inset-0 flex items-center px-8">
                                    <div>
                                        <span
                                            class="text-gold-500 text-xs font-bold uppercase tracking-widest mb-1 block"><?php echo $num; ?></span>
                                        <h3 class="text-2xl font-serif text-white">
                                            <?php echo htmlspecialchars($section['label']); ?>
                                        </h3>
                                        <?php if (!empty($section['description'])): ?>
                                            <p
                                                class="text-slate-400 text-[10px] mt-1 line-clamp-1 opacity-60 uppercase tracking-tighter">
                                                <?php echo htmlspecialchars($section['description']); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </a>

                            <?php if (!empty($subItems)): ?>
                                <ul class="pl-8 space-y-4 border-l border-slate-800 ml-4 mb-4">
                                    <?php foreach ($subItems as $item): ?>
                                        <li>
                                            <a href="<?php echo htmlspecialchars(lang_url($item['url'])); ?>"
                                                class="text-sm text-slate-400 hover:text-gold-500 transition-colors block uppercase tracking-wider">
                                                <span class="text-gold-500/50 mr-2">&bull;</span>
                                                <?php echo htmlspecialchars($item['label']); ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <div id="mobileNavAdminControls" class="hidden border-t border-slate-800 pt-8 mt-4">
                        <h3 class="text-xs text-gold-500 uppercase tracking-widest mb-6 text-center">Management Console
                        </h3>
                        <div class="space-y-3">
                            <a href="admin/dashboard.php"
                                class="flex items-center gap-4 px-6 py-4 rounded bg-slate-800/50 border border-slate-700 text-white active:bg-gold-500/10 transition-colors">
                                <svg class="w-5 h-5 text-gold-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                                <span class="text-sm font-medium uppercase tracking-wider">Analytics Overview</span>
                            </a>
                            <a href="admin/posts.php"
                                class="flex items-center gap-4 px-6 py-4 rounded bg-slate-800/50 border border-slate-700 text-white active:bg-gold-500/10 transition-colors">
                                <svg class="w-5 h-5 text-gold-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10l4 4v10a2 2 0 01-2 2z" />
                                </svg>
                                <span class="text-sm font-medium uppercase tracking-wider">Article Content</span>
                            </a>
                            <a href="admin/categories.php"
                                class="flex items-center gap-4 px-6 py-4 rounded bg-slate-800/50 border border-slate-700 text-white active:bg-gold-500/10 transition-colors">
                                <svg class="w-5 h-5 text-gold-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                </svg>
                                <span class="text-sm font-medium uppercase tracking-wider">Categories</span>
                            </a>
                            <a href="admin/tags_manage.php"
                                class="flex items-center gap-4 px-6 py-4 rounded bg-slate-800/50 border border-slate-700 text-white active:bg-gold-500/10 transition-colors">
                                <svg class="w-5 h-5 text-gold-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                <span class="text-sm font-medium uppercase tracking-wider">Tags & Keywords</span>
                            </a>
                            <a href="admin/feedback_manage.php"
                                class="flex items-center gap-4 px-6 py-4 rounded bg-slate-800/50 border border-slate-700 text-white active:bg-gold-500/10 transition-colors relative">
                                <svg class="w-5 h-5 text-gold-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                </svg>
                                <span class="text-sm font-medium uppercase tracking-wider">Feedback & Inquiries</span>
                                <span id="mobileNotifBadge"
                                    class="hidden absolute right-4 bg-red-500 text-white text-[10px] font-bold rounded-full px-2 py-0.5">0</span>
                            </a>
                            <a href="admin/header_notifications_manage.php"
                                class="flex items-center gap-4 px-6 py-4 rounded bg-slate-800/50 border border-slate-700 text-white active:bg-gold-500/10 transition-colors">
                                <svg class="w-5 h-5 text-gold-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                                </svg>
                                <span class="text-sm font-medium uppercase tracking-wider">Header Alerts</span>
                            </a>
                            <a href="admin/ads_manage.php"
                                class="flex items-center gap-4 px-6 py-4 rounded bg-slate-800/50 border border-slate-700 text-white active:bg-gold-500/10 transition-colors">
                                <svg class="w-5 h-5 text-gold-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                <span class="text-sm font-medium uppercase tracking-wider">Advertisements</span>
                            </a>
                            <a href="admin/executive_profile_manage.php"
                                class="flex items-center gap-4 px-6 py-4 rounded bg-slate-800/50 border border-slate-700 text-white active:bg-gold-500/10 transition-colors">
                                <svg class="w-5 h-5 text-gold-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                </svg>
                                <span class="text-sm font-medium uppercase tracking-wider">Executive Profile</span>
                            </a>
                            <a href="admin/site_settings.php"
                                class="flex items-center gap-4 px-6 py-4 rounded bg-slate-800/50 border border-slate-700 text-white active:bg-gold-500/10 transition-colors">
                                <svg class="w-5 h-5 text-gold-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span class="text-sm font-medium uppercase tracking-wider">Site Configuration</span>
                            </a>
                            <a href="admin/logout.php"
                                class="flex items-center gap-4 px-6 py-4 rounded bg-red-900/10 border border-red-900/20 text-red-400 active:bg-red-900/20 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                <span class="text-sm font-bold uppercase tracking-widest text-center flex-1">Terminate
                                    Session</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="mt-8 border-t border-slate-800 pt-8 flex flex-col items-center space-y-6">
                    <!-- Digital Card Trigger (Mobile) -->
                    <button @click="mobileOpen = false; $dispatch('open-digital-card')"
                        class="flex items-center gap-3 px-6 py-3 rounded-full bg-slate-800 text-gold-500 border border-gold-500/30 shadow-lg active:scale-95 transition-all text-sm font-bold uppercase tracking-widest">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                        </svg>
                        <span><?php echo e_t('nav.digital_card'); ?></span>
                    </button>

                    <div class="pt-4 text-center">
                        <p class="text-xs text-slate-600"><?php echo e_t('nav.brand_suffix'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</div>
<!-- Spacer -->
<div style="height: var(--header-height, 80px);"></div>

<script>
    // Include the original JS logic for Notifications Check and Auth
    // Copied from original file for continuity
    async function checkNavAdminSession() {
        try {
            const response = await fetch('admin/api/check_session.php');
            const data = await response.json();

            if (data.logged_in) {
                document.getElementById('navAdminControls').classList.remove('hidden');
                document.getElementById('mobileNavAdminControls').classList.remove('hidden');
                document.getElementById('navLoginIcon').classList.add('hidden');
                const initials = data.username.substring(0, 2).toUpperCase();
                document.getElementById('navAdminInitials').textContent = initials;
                document.getElementById('navAdminUsername').textContent = data.username;
                document.getElementById('navAdminRole').textContent = data.role || 'Administrator';
                loadNavNotifications();
                setInterval(loadNavNotifications, 30000);
            } else {
                document.getElementById('navAdminControls').classList.add('hidden');
                document.getElementById('mobileNavAdminControls').classList.add('hidden');
                document.getElementById('navLoginIcon').classList.remove('hidden');
            }
        } catch (error) {
            document.getElementById('navLoginIcon').classList.remove('hidden');
        }
    }

    async function loadNavNotifications() {
        try {
            const response = await fetch('admin/api/get_notifications.php');
            const data = await response.json();
            if (data.success && data.unread_count > 0) {
                document.getElementById('navNotifBadge').textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                document.getElementById('navNotifBadge').classList.remove('hidden');
                if (document.getElementById('mobileNotifBadge')) {
                    document.getElementById('mobileNotifBadge').textContent = data.unread_count;
                    document.getElementById('mobileNotifBadge').classList.remove('hidden');
                }
            } else {
                document.getElementById('navNotifBadge').classList.add('hidden');
                if (document.getElementById('mobileNotifBadge')) {
                    document.getElementById('mobileNotifBadge').classList.add('hidden');
                }
            }
            renderNavNotifications(data.notifications || []);
        } catch (error) { }
    }

    function renderNavNotifications(notifications) {
        const list = document.getElementById('navNotificationsList');
        if (!list) return;
        if (notifications.length === 0) {
            list.innerHTML = '<div class="p-6 text-center text-slate-500"><p class="text-xs">No notifications</p></div>';
            return;
        }
        list.innerHTML = notifications.map(notif => `
            <a href="admin/feedback_manage.php" class="block p-3 border-b border-slate-700 hover:bg-slate-700/50 transition-colors ${notif.is_read == 0 ? 'bg-gold-500/10' : ''}">
                <div class="flex-1 min-w-0">
                    <p class="text-white text-xs font-medium truncate">${notif.name}</p>
                    <p class="text-slate-400 text-xs truncate">${notif.message_preview}...</p>
                </div>
            </a>
        `).join('');
    }

    checkNavAdminSession();
</script>
<?php include 'includes/digital_card_modal.php'; ?>
