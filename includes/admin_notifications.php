<!-- Admin Notifications & Login - Below Header -->
<div id="adminNotificationBar" class="fixed top-24 right-4 z-40 hidden">
    <!-- Shows when logged in -->
    <!-- Notification Bell -->
    <div class="relative inline-block">
        <button onclick="toggleNotifications()"
            class="bg-navy-800/90 backdrop-blur-sm hover:bg-navy-700 border border-gold-500 text-gold-500 p-2.5 rounded-full shadow-lg transition-all relative">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span id="notifBadge"
                class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1 hidden">0</span>
        </button>

        <!-- Notification Dropdown -->
        <div id="notificationDropdown"
            class="hidden absolute top-full right-0 mt-2 w-80 sm:w-96 bg-navy-800/95 backdrop-blur-md border border-gold-500 rounded-lg shadow-2xl max-h-[70vh] overflow-hidden">
            <!-- Header -->
            <div class="p-3 border-b border-slate-700 flex items-center justify-between bg-navy-900/50">
                <h3 class="text-white font-bold text-sm">Notifications</h3>
                <button onclick="openNotificationSettings()"
                    class="text-slate-400 hover:text-gold-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </button>
            </div>

            <!-- Actions -->
            <div class="p-2 border-b border-slate-700 flex gap-2 bg-navy-900/30">
                <button id="markAllBtn" onclick="markAllRead()" disabled
                    class="flex-1 text-xs bg-slate-700/30 text-slate-500 px-2 py-1.5 rounded transition-colors cursor-not-allowed">
                    Mark All Read
                </button>
                <button id="clearBtn" onclick="clearNotifications()" disabled
                    class="flex-1 text-xs bg-slate-700/30 text-slate-500 px-2 py-1.5 rounded transition-colors cursor-not-allowed">
                    Clear Notifications
                </button>
            </div>

            <!-- Notifications List -->
            <div id="notificationsList" class="overflow-y-auto max-h-[50vh]">
                <div class="p-8 text-center text-slate-500">
                    <svg class="w-10 h-10 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <p class="text-xs">Loading...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Profile Menu Icon -->
    <div class="relative inline-block ml-2">
        <button onclick="toggleAdminMenu()"
            class="bg-gold-500 hover:bg-gold-400 text-navy-900 p-2.5 rounded-full shadow-lg transition-all flex items-center justify-center w-10 h-10 font-bold text-sm">
            <span id="adminInitials">A</span>
        </button>

        <!-- Admin Dropdown Menu -->
        <div id="adminDropdown"
            class="hidden absolute top-full right-0 mt-2 w-48 bg-navy-800/95 backdrop-blur-md border border-gold-500 rounded-lg shadow-2xl overflow-hidden">
            <div class="p-3 border-b border-slate-700 bg-navy-900/50">
                <p class="text-white font-bold text-sm truncate" id="adminUsername">Admin</p>
                <p class="text-slate-400 text-xs" id="adminRole">Administrator</p>
            </div>
            <div class="py-1">
                <a href="admin/profile.php"
                    class="block px-3 py-2 text-xs text-slate-300 hover:bg-slate-700 hover:text-white transition-colors">
                    <svg class="w-3.5 h-3.5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Profile
                </a>
                <a href="admin/dashboard.php"
                    class="block px-3 py-2 text-xs text-slate-300 hover:bg-slate-700 hover:text-white transition-colors">
                    <svg class="w-3.5 h-3.5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    Dashboard
                </a>
                <a href="admin/feedback_manage.php"
                    class="block px-3 py-2 text-xs text-slate-300 hover:bg-slate-700 hover:text-white transition-colors">
                    <svg class="w-3.5 h-3.5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                    Feedback
                    <span id="feedbackBadge"
                        class="float-right bg-red-500 text-white text-xs px-1.5 py-0.5 rounded-full hidden">0</span>
                </a>
                <div class="border-t border-slate-700 my-1"></div>
                <a href="admin/logout.php"
                    class="block px-3 py-2 text-xs text-red-400 hover:bg-slate-700 hover:text-red-300 transition-colors">
                    <svg class="w-3.5 h-3.5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Login Icon - Shows when NOT logged in -->
<div id="loginIcon" class="fixed top-24 right-4 z-40 hidden">
    <a href="admin/login.php"
        class="bg-gold-500 hover:bg-gold-400 text-navy-900 p-3 rounded-full shadow-lg transition-all flex items-center justify-center w-12 h-12 group">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        </svg>
        <span
            class="absolute right-full mr-3 top-1/2 transform -translate-y-1/2 bg-navy-900 text-white px-3 py-1 rounded text-sm whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
            Admin Login
        </span>
    </a>
</div>

<script>
    let notificationsOpen = false;
    let adminMenuOpen = false;
    let notificationInterval;

    async function checkAdminSession() {
        try {
            const response = await fetch('admin/api/check_session.php');
            const data = await response.json();

            if (data.logged_in) {
                // Show admin controls, hide login icon
                document.getElementById('adminNotificationBar').classList.remove('hidden');
                document.getElementById('adminNotificationBar').classList.add('flex', 'items-center');
                document.getElementById('loginIcon').classList.add('hidden');

                const initials = data.username.substring(0, 2).toUpperCase();
                document.getElementById('adminInitials').textContent = initials;
                document.getElementById('adminUsername').textContent = data.username;
                document.getElementById('adminRole').textContent = data.role || 'Administrator';

                loadNotifications();

                if (!notificationInterval) {
                    notificationInterval = setInterval(loadNotifications, 30000);
                }
            } else {
                // Show login icon, hide admin controls
                document.getElementById('adminNotificationBar').classList.add('hidden');
                document.getElementById('loginIcon').classList.remove('hidden');
                
                if (notificationInterval) {
                    clearInterval(notificationInterval);
                    notificationInterval = null;
                }
            }
        } catch (error) {
            // Silent fail - show login icon by default
            document.getElementById('loginIcon').classList.remove('hidden');
        }
    }

    async function loadNotifications() {
        try {
            const response = await fetch('admin/api/get_notifications.php');
            const data = await response.json();

            if (data.success) {
                const badge = document.getElementById('notifBadge');
                const feedbackBadge = document.getElementById('feedbackBadge');

                if (data.unread_count > 0) {
                    badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                    badge.classList.remove('hidden');
                    feedbackBadge.textContent = data.unread_count;
                    feedbackBadge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                    feedbackBadge.classList.add('hidden');
                }

                renderNotifications(data.notifications);
            }
        } catch (error) {
            // Silent fail
        }
    }

    function renderNotifications(notifications) {
        const list = document.getElementById('notificationsList');
        const markAllBtn = document.getElementById('markAllBtn');
        const clearBtn = document.getElementById('clearBtn');

        if (notifications.length === 0) {
            list.innerHTML = `
            <div class="p-6 text-center text-slate-500">
                <svg class="w-10 h-10 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p class="text-xs">No notifications</p>
            </div>
        `;
            markAllBtn.disabled = true;
            markAllBtn.className = 'flex-1 text-xs bg-slate-700/30 text-slate-500 px-2 py-1.5 rounded transition-colors cursor-not-allowed';
            clearBtn.disabled = true;
            clearBtn.className = 'flex-1 text-xs bg-slate-700/30 text-slate-500 px-2 py-1.5 rounded transition-colors cursor-not-allowed';
            return;
        }

        markAllBtn.disabled = false;
        markAllBtn.className = 'flex-1 text-xs bg-slate-700/50 hover:bg-slate-600 text-white px-2 py-1.5 rounded transition-colors';
        clearBtn.disabled = false;
        clearBtn.className = 'flex-1 text-xs bg-slate-700/50 hover:bg-slate-600 text-white px-2 py-1.5 rounded transition-colors';

        list.innerHTML = notifications.map(notif => `
        <div class="p-3 border-b border-slate-700 hover:bg-slate-700/50 transition-colors cursor-pointer ${notif.is_read == 0 ? 'bg-gold-500/10' : ''}"
             onclick="markSingleRead(${notif.id})">
            <div class="flex items-start gap-2">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 rounded-full bg-gold-500/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-gold-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-xs font-medium truncate">${notif.name}</p>
                    <p class="text-slate-400 text-xs truncate">${notif.message_preview}...</p>
                    <p class="text-slate-500 text-xs mt-0.5">${timeAgo(notif.created_at)}</p>
                </div>
                ${notif.is_read == 0 ? '<div class="flex-shrink-0"><div class="w-2 h-2 bg-gold-500 rounded-full"></div></div>' : ''}
            </div>
        </div>
    `).join('');
    }

    function timeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const seconds = Math.floor((now - date) / 1000);

        if (seconds < 60) return 'Just now';
        if (seconds < 3600) return Math.floor(seconds / 60) + 'm ago';
        if (seconds < 86400) return Math.floor(seconds / 3600) + 'h ago';
        return Math.floor(seconds / 86400) + 'd ago';
    }

    function toggleNotifications() {
        const dropdown = document.getElementById('notificationDropdown');
        notificationsOpen = !notificationsOpen;

        if (notificationsOpen) {
            dropdown.classList.remove('hidden');
            if (adminMenuOpen) {
                document.getElementById('adminDropdown').classList.add('hidden');
                adminMenuOpen = false;
            }
        } else {
            dropdown.classList.add('hidden');
        }
    }

    function toggleAdminMenu() {
        const dropdown = document.getElementById('adminDropdown');
        adminMenuOpen = !adminMenuOpen;

        if (adminMenuOpen) {
            dropdown.classList.remove('hidden');
            if (notificationsOpen) {
                document.getElementById('notificationDropdown').classList.add('hidden');
                notificationsOpen = false;
            }
        } else {
            dropdown.classList.add('hidden');
        }
    }

    async function markAllRead() {
        try {
            const response = await fetch('admin/api/mark_notifications_read.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'mark_all_read' })
            });

            if (response.ok) loadNotifications();
        } catch (error) { }
    }

    async function clearNotifications() {
        if (!confirm('Clear all read notifications?')) return;

        try {
            const response = await fetch('admin/api/mark_notifications_read.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'clear_all' })
            });

            if (response.ok) loadNotifications();
        } catch (error) { }
    }

    async function markSingleRead(id) {
        try {
            await fetch('admin/api/mark_notifications_read.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'mark_single_read', id: id })
            });

            loadNotifications();
            window.location.href = 'admin/feedback_manage.php';
        } catch (error) { }
    }

    function openNotificationSettings() {
        alert('Notification settings coming soon!');
    }

    document.addEventListener('click', function (e) {
        const bar = document.getElementById('adminNotificationBar');
        if (bar && !bar.contains(e.target)) {
            if (notificationsOpen) {
                document.getElementById('notificationDropdown').classList.add('hidden');
                notificationsOpen = false;
            }
            if (adminMenuOpen) {
                document.getElementById('adminDropdown').classList.add('hidden');
                adminMenuOpen = false;
            }
        }
    });

    checkAdminSession();
</script>