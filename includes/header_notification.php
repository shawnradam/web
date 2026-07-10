<?php
// Public header notification bell. Reuses the existing header_notifications table/admin CRUD.
$headerNotifications = [];
try {
    if (isset($pdo)) {
        $stmt = $pdo->query("SELECT id, title, message, image_url, link_url FROM header_notifications WHERE is_active = 1 ORDER BY created_at DESC LIMIT 8");
        $headerNotifications = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
} catch (Throwable $e) {
    $headerNotifications = [];
}

$headerNotificationCount = count($headerNotifications);
$headerNotificationUid = 'publicHeaderNotifications_' . random_int(1000, 999999);
$headerNotificationIds = array_map('intval', array_column($headerNotifications, 'id'));

if (!function_exists('header_notification_link')) {
    function header_notification_link($url)
    {
        $url = trim((string) $url);
        if ($url === '') {
            return '#';
        }
        if (preg_match('/^https?:\/\//i', $url)) {
            return $url;
        }
        return function_exists('lang_url') ? lang_url($url) : $url;
    }
}

if (!function_exists('header_notification_image')) {
    function header_notification_image($path)
    {
        $path = trim((string) $path);
        if ($path === '') {
            return '';
        }
        if (preg_match('/^https?:\/\//i', $path)) {
            return $path;
        }
        return function_exists('public_path') ? public_path($path) : $path;
    }
}
?>
<div id="<?php echo htmlspecialchars($headerNotificationUid, ENT_QUOTES, 'UTF-8'); ?>" class="relative" x-data="{ open: false, toggleBell() { if (!this.open) { const first = window.srHeaderNotificationUseLargePreview ? window.srHeaderNotificationUseLargePreview(this.$el) : false; this.$el.classList.toggle('is-first-open', first); this.open = true; this.$nextTick(() => { if (window.srHeaderNotificationApplyPreviewMode) window.srHeaderNotificationApplyPreviewMode(this.$el); }); } else { this.open = false; this.$el.classList.remove('is-first-open'); } }, closeBell() { this.open = false; this.$el.classList.remove('is-first-open'); } }" data-header-notification-bell data-notification-ids="<?php echo htmlspecialchars(json_encode($headerNotificationIds), ENT_QUOTES, 'UTF-8'); ?>">
    <button type="button"
        <?php echo $headerNotificationCount > 0 ? '@click="toggleBell()"' : 'disabled aria-disabled="true"'; ?>
        class="relative inline-flex h-10 w-10 items-center justify-center rounded-full border transition-colors <?php echo $headerNotificationCount > 0 ? 'border-gold-500/50 text-gold-500 hover:bg-gold-500 hover:text-navy-900' : 'border-slate-700 text-slate-600 cursor-not-allowed opacity-60'; ?>"
        aria-label="Header notifications">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <?php if ($headerNotificationCount > 0): ?>
            <span data-notification-badge class="hidden absolute -right-1 -top-1 min-w-5 h-5 rounded-full bg-red-600 px-1.5 text-[10px] leading-5 text-white font-bold text-center"></span>
            <span data-notification-ping class="hidden absolute right-0 top-0 h-2.5 w-2.5 rounded-full bg-gold-400 animate-ping"></span>
        <?php endif; ?>
    </button>

    <?php if ($headerNotificationCount > 0): ?>
        <div x-show="open" @click.away="closeBell()" x-cloak
            class="absolute right-0 top-full mt-3 w-[min(22rem,calc(100vw-2rem))] overflow-hidden border border-gold-500/50 bg-navy-900 shadow-2xl z-[10020]">
            <div class="border-b border-slate-800 px-4 py-3 flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs uppercase tracking-[0.24em] text-gold-500 font-bold">Notifications</p>
                    <p class="text-sm text-slate-400 mt-1">Latest active announcements</p>
                </div>
                <button type="button" data-mark-all-read class="shrink-0 text-[11px] uppercase tracking-wider text-slate-400 hover:text-gold-500">Mark all read</button>
            </div>
            <div class="max-h-[70vh] overflow-y-auto">
                <?php foreach ($headerNotifications as $notification): ?>
                    <?php
                    $notificationId = (int) ($notification['id'] ?? 0);
                    $notificationLink = header_notification_link($notification['link_url'] ?? '');
                    $notificationImage = header_notification_image($notification['image_url'] ?? '');
                    $hasLink = $notificationLink !== '#';
                    ?>
                    <div data-notification-item data-notification-id="<?php echo $notificationId; ?>" class="border-b border-slate-800 px-4 py-4 transition-colors">
                        <?php if ($notificationImage !== ''): ?>
                            <img data-large-notification-image src="<?php echo htmlspecialchars($notificationImage, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($notification['title'] ?? 'Notification', ENT_QUOTES, 'UTF-8'); ?>" class="hidden mb-3 w-full max-h-72 object-contain border border-slate-700 bg-slate-950">
                        <?php endif; ?>
                        <div class="flex gap-3">
                            <?php if ($notificationImage !== ''): ?>
                                <img data-small-notification-image src="<?php echo htmlspecialchars($notificationImage, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($notification['title'] ?? 'Notification', ENT_QUOTES, 'UTF-8'); ?>" class="h-14 w-14 shrink-0 object-cover border border-slate-700 bg-slate-950">
                            <?php else: ?>
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gold-500/10 text-gold-500 border border-gold-500/30">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                                </span>
                            <?php endif; ?>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="text-sm font-semibold text-white leading-snug"><?php echo htmlspecialchars($notification['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                                    <span data-unread-dot class="mt-1 h-2 w-2 shrink-0 rounded-full bg-gold-500"></span>
                                </div>
                                <?php if (!empty($notification['message'])): ?>
                                    <p class="mt-1 text-xs text-slate-400 leading-relaxed"><?php echo htmlspecialchars($notification['message'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <?php endif; ?>
                                <div class="mt-3 flex flex-wrap items-center gap-3">
                                    <button type="button" data-mark-read="<?php echo $notificationId; ?>" class="text-[11px] uppercase tracking-wider text-gold-500 hover:text-gold-400">Mark as read</button>
                                    <?php if ($hasLink): ?>
                                        <a href="<?php echo htmlspecialchars($notificationLink, ENT_QUOTES, 'UTF-8'); ?>" <?php echo preg_match('/^https?:\/\//i', $notificationLink) ? 'target="_blank" rel="noopener noreferrer"' : ''; ?> class="text-[11px] uppercase tracking-wider text-slate-400 hover:text-white">Open</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if ($headerNotificationCount > 0): ?>
<style>
    [data-header-notification-bell].is-first-open [data-large-notification-image] {
        display: block !important;
    }
    [data-header-notification-bell].is-first-open [data-small-notification-image] {
        display: none !important;
    }
</style><script>
(function () {
    const root = document.getElementById('<?php echo addslashes($headerNotificationUid); ?>');
    if (!root) return;

    const storageKey = 'sr_header_notifications_read';
    const activeIds = JSON.parse(root.getAttribute('data-notification-ids') || '[]').map(String);

    window.srHeaderNotificationUseLargePreview = window.srHeaderNotificationUseLargePreview || function (rootElement) {
        const ids = JSON.parse(rootElement.getAttribute('data-notification-ids') || '[]').map(String);
        const key = 'sr_header_notifications_first_open_v2_' + ids.join('_');
        let shouldUseLarge = false;
        try {
            shouldUseLarge = localStorage.getItem(key) !== 'true';
            localStorage.setItem(key, 'true');
        } catch (error) {
            shouldUseLarge = !rootElement.hasAttribute('data-first-preview-used');
            rootElement.setAttribute('data-first-preview-used', 'true');
        }
        return shouldUseLarge;
    };

    window.srHeaderNotificationApplyPreviewMode = window.srHeaderNotificationApplyPreviewMode || function (targetRoot) {
        const isFirstOpen = targetRoot.classList.contains('is-first-open');
        targetRoot.querySelectorAll('[data-large-notification-image]').forEach(function (image) {
            image.classList.toggle('hidden', !isFirstOpen);
        });
        targetRoot.querySelectorAll('[data-small-notification-image]').forEach(function (image) {
            image.classList.toggle('hidden', isFirstOpen);
        });
    };

    function applyPreviewMode() {
        window.srHeaderNotificationApplyPreviewMode(root);
    }

    function readIds() {
        try {
            const saved = JSON.parse(localStorage.getItem(storageKey) || '[]');
            return Array.isArray(saved) ? saved.map(String) : [];
        } catch (error) {
            return [];
        }
    }

    function saveIds(ids) {
        try {
            localStorage.setItem(storageKey, JSON.stringify(Array.from(new Set(ids.map(String)))));
        } catch (error) {}
    }

    function applyState() {
        const read = readIds();
        const unread = activeIds.filter(function (id) { return read.indexOf(id) === -1; });
        const badge = root.querySelector('[data-notification-badge]');
        const ping = root.querySelector('[data-notification-ping]');
        if (badge) {
            if (unread.length > 0) {
                badge.textContent = unread.length > 9 ? '9+' : String(unread.length);
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }
        if (ping) {
            ping.classList.toggle('hidden', unread.length === 0);
        }
        root.querySelectorAll('[data-notification-item]').forEach(function (item) {
            const id = String(item.getAttribute('data-notification-id'));
            const isRead = read.indexOf(id) !== -1;
            item.classList.toggle('bg-gold-500/10', !isRead);
            item.classList.toggle('bg-slate-950/20', isRead);
            const dot = item.querySelector('[data-unread-dot]');
            const button = item.querySelector('[data-mark-read]');
            if (dot) dot.classList.toggle('hidden', isRead);
            if (button) {
                button.textContent = isRead ? 'Read' : 'Mark as read';
                button.disabled = isRead;
                button.classList.toggle('text-slate-600', isRead);
                button.classList.toggle('cursor-default', isRead);
                button.classList.toggle('text-gold-500', !isRead);
            }
        });
    }

    root.querySelectorAll('[data-mark-read]').forEach(function (button) {
        button.addEventListener('click', function () {
            const ids = readIds();
            ids.push(String(button.getAttribute('data-mark-read')));
            saveIds(ids);
            document.dispatchEvent(new CustomEvent('srHeaderNotificationsReadChanged'));
        });
    });

    const markAll = root.querySelector('[data-mark-all-read]');
    if (markAll) {
        markAll.addEventListener('click', function () {
            saveIds(readIds().concat(activeIds));
            document.dispatchEvent(new CustomEvent('srHeaderNotificationsReadChanged'));
        });
    }

    document.addEventListener('srHeaderNotificationsReadChanged', applyState);
    document.addEventListener('click', function () { setTimeout(applyPreviewMode, 0); });
    window.addEventListener('storage', function (event) {
        if (event.key === storageKey) applyState();
    });
    applyState();
    applyPreviewMode();
})();
</script>
<?php endif; ?>