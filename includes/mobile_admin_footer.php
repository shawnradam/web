<!-- Mobile Admin Footer Navigation (Shows only when admin is logged in) -->
<div id="mobileAdminFooter"
    class="md:hidden fixed bottom-0 left-0 right-0 bg-navy-900 border-t border-gold-500 shadow-2xl z-50 hidden">
    <div class="flex items-center justify-around py-3 px-4">
        <!-- Dashboard -->
        <a href="admin/dashboard.php"
            class="flex flex-col items-center gap-1 text-slate-400 hover:text-gold-500 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
            <span class="text-xs">Dashboard</span>
        </a>

        <!-- Notifications -->
        <button onclick="toggleMobileNotifications()"
            class="flex flex-col items-center gap-1 text-slate-400 hover:text-gold-500 transition-colors relative">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span class="text-xs">Notifications</span>
            <span id="mobileNotifBadge"
                class="hidden absolute top-0 right-2 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">0</span>
        </button>

        <!-- Feedback -->
        <button onclick="toggleMobileFeedback()"
            class="flex flex-col items-center gap-1 text-slate-400 hover:text-gold-500 transition-colors relative">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
            <span class="text-xs">Feedback</span>
            <span id="mobileFeedbackBadge"
                class="hidden absolute top-0 right-2 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">0</span>
        </button>



        <!-- Profile -->
        <button onclick="toggleMobileProfile()"
            class="flex flex-col items-center gap-1 text-slate-400 hover:text-gold-500 transition-colors">
            <div
                class="w-8 h-8 rounded-full bg-gold-500 text-navy-900 flex items-center justify-center text-sm font-bold">
                <span id="mobileAdminInitials">A</span>
            </div>
            <span class="text-xs">Profile</span>
        </button>
    </div>
</div>

<!-- Mobile Notifications Modal -->
<div id="mobileNotificationsModal" class="md:hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-[60] hidden">
    <div
        class="absolute bottom-0 left-0 right-0 bg-navy-900 rounded-t-2xl shadow-2xl max-h-[80vh] overflow-hidden animate-slide-up">
        <div class="p-4 border-b border-slate-700 flex items-center justify-between">
            <h3 class="text-white font-bold">Notifications</h3>
            <button onclick="toggleMobileNotifications()" class="text-slate-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="p-3 border-b border-slate-700 flex gap-2">
            <button onclick="markAllRead()" id="mobileMarkAllBtn" disabled
                class="flex-1 text-xs bg-slate-700/30 text-slate-500 px-3 py-2 rounded transition-colors cursor-not-allowed">
                Mark All Read
            </button>
            <button onclick="clearNotifications()" id="mobileClearBtn" disabled
                class="flex-1 text-xs bg-slate-700/30 text-slate-500 px-3 py-2 rounded transition-colors cursor-not-allowed">
                Clear All
            </button>
        </div>
        <div id="mobileNotificationsList" class="overflow-y-auto max-h-[60vh]">
            <div class="p-8 text-center text-slate-500">
                <p class="text-sm">Loading...</p>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Profile Modal -->
<div id="mobileProfileModal" class="md:hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-[60] hidden">
    <div class="absolute bottom-0 left-0 right-0 bg-navy-900 rounded-t-2xl shadow-2xl animate-slide-up">
        <div class="p-4 border-b border-slate-700 flex items-center justify-between">
            <h3 class="text-white font-bold">Admin Profile</h3>
            <button onclick="toggleMobileProfile()" class="text-slate-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="p-4">
            <div class="flex items-center gap-4 mb-6">
                <div
                    class="w-16 h-16 rounded-full bg-gold-500 text-navy-900 flex items-center justify-center text-2xl font-bold">
                    <span id="mobileProfileInitials">A</span>
                </div>
                <div>
                    <p class="text-white font-bold text-lg" id="mobileAdminUsername">Admin</p>
                    <p class="text-slate-400 text-sm" id="mobileAdminRole">Administrator</p>
                </div>
            </div>
            <div class="space-y-2">
                <a href="admin/dashboard.php"
                    class="block px-4 py-3 bg-slate-800 hover:bg-slate-700 rounded-lg text-white transition-colors">
                    <svg class="w-5 h-5 inline-block mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    Dashboard
                </a>
                <a href="admin/profile.php"
                    class="block px-4 py-3 bg-slate-800 hover:bg-slate-700 rounded-lg text-white transition-colors">
                    <svg class="w-5 h-5 inline-block mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Edit Profile
                </a>
                <a href="admin/digital_card_manage.php"
                    class="block px-4 py-3 bg-slate-800 hover:bg-slate-700 rounded-lg text-white transition-colors">
                    <svg class="w-5 h-5 inline-block mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                    </svg>
                    Digital Card Manager
                </a>
                <a href="admin/logout.php"
                    class="block px-4 py-3 bg-red-900/50 hover:bg-red-900 rounded-lg text-red-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5 inline-block mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Feedback Modal -->
<div id="mobileFeedbackModal" class="md:hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-[60] hidden">
    <div class="absolute bottom-0 left-0 right-0 bg-navy-900 rounded-t-2xl shadow-2xl animate-slide-up">
        <div class="p-4 border-b border-slate-700 flex items-center justify-between">
            <h3 class="text-white font-bold">Feedback Management</h3>
            <button onclick="toggleMobileFeedback()" class="text-slate-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="p-6 text-center">
            <svg class="w-16 h-16 text-gold-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
            <h4 class="text-white text-lg font-bold mb-2">Manage Feedback</h4>
            <p class="text-slate-400 text-sm mb-6">View and respond to user feedback submissions</p>
            <a href="admin/feedback_manage.php"
                class="inline-block bg-gold-500 hover:bg-gold-400 text-navy-900 px-6 py-3 rounded-lg font-bold transition-colors">
                Open Feedback Manager
            </a>
        </div>
    </div>
</div>

<style>
    @keyframes slide-up {
        from {
            transform: translateY(100%);
        }

        to {
            transform: translateY(0);
        }
    }

    .animate-slide-up {
        animation: slide-up 0.3s ease-out;
    }
</style>

<script>
    // Mobile Admin Footer Functions
    async function checkMobileAdminSession() {
        try {
            const response = await fetch('admin/api/check_session.php');
            const data = await response.json();

            if (data.logged_in) {
                document.getElementById('mobileAdminFooter').classList.remove('hidden');
                document.body.classList.add('pb-20');

                // Shift bottom floating widgets up to prevent blocking/overlap
                const goToTopBtn = document.getElementById('goToTopBtn');
                if (goToTopBtn) {
                    goToTopBtn.classList.remove('bottom-6');
                    goToTopBtn.classList.add('bottom-20');
                }
                const visitorIcon = document.getElementById('visitorDigitalCardIcon');
                if (visitorIcon) {
                    visitorIcon.classList.remove('bottom-6');
                    visitorIcon.classList.add('bottom-20');
                }

                const initials = data.username.substring(0, 2).toUpperCase();
                document.getElementById('mobileAdminInitials').textContent = initials;
                document.getElementById('mobileProfileInitials').textContent = initials;
                document.getElementById('mobileAdminUsername').textContent = data.username;
                document.getElementById('mobileAdminRole').textContent = data.role || 'Administrator';

                loadMobileNotifications();
                setInterval(loadMobileNotifications, 30000);
            }
        } catch (error) { }
    }

    async function loadMobileNotifications() {
        try {
            const response = await fetch('admin/api/get_notifications.php');
            const data = await response.json();

            if (data.success && data.unread_count > 0) {
                document.getElementById('mobileNotifBadge').textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                document.getElementById('mobileNotifBadge').classList.remove('hidden');
                document.getElementById('mobileFeedbackBadge').textContent = data.unread_count;
                document.getElementById('mobileFeedbackBadge').classList.remove('hidden');
            } else {
                document.getElementById('mobileNotifBadge').classList.add('hidden');
                document.getElementById('mobileFeedbackBadge').classList.add('hidden');
            }

            renderMobileNotifications(data.notifications || []);
        } catch (error) { }
    }

    function renderMobileNotifications(notifications) {
        const list = document.getElementById('mobileNotificationsList');
        const markAllBtn = document.getElementById('mobileMarkAllBtn');
        const clearBtn = document.getElementById('mobileClearBtn');

        if (!list) return;

        if (notifications.length === 0) {
            list.innerHTML = '<div class="p-8 text-center text-slate-500"><p class="text-sm">No notifications</p></div>';
            if (markAllBtn) {
                markAllBtn.disabled = true;
                markAllBtn.className = 'flex-1 text-xs bg-slate-700/30 text-slate-500 px-3 py-2 rounded transition-colors cursor-not-allowed';
            }
            if (clearBtn) {
                clearBtn.disabled = true;
                clearBtn.className = 'flex-1 text-xs bg-slate-700/30 text-slate-500 px-3 py-2 rounded transition-colors cursor-not-allowed';
            }
            return;
        }

        if (markAllBtn) {
            markAllBtn.disabled = false;
            markAllBtn.className = 'flex-1 text-xs bg-slate-700/50 hover:bg-slate-600 text-white px-3 py-2 rounded transition-colors';
        }
        if (clearBtn) {
            clearBtn.disabled = false;
            clearBtn.className = 'flex-1 text-xs bg-slate-700/50 hover:bg-slate-600 text-white px-3 py-2 rounded transition-colors';
        }

        list.innerHTML = notifications.map(notif => `
        <a href="admin/feedback_manage.php" class="block p-4 border-b border-slate-700 hover:bg-slate-800 transition-colors ${notif.is_read == 0 ? 'bg-gold-500/10' : ''}">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 rounded-full bg-gold-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-gold-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-sm font-medium">${notif.name}</p>
                    <p class="text-slate-400 text-xs truncate">${notif.message_preview}...</p>
                    <p class="text-slate-500 text-xs mt-1">${timeAgo(notif.created_at)}</p>
                </div>
                ${notif.is_read == 0 ? '<div class="flex-shrink-0"><div class="w-2 h-2 bg-gold-500 rounded-full"></div></div>' : ''}
            </div>
        </a>
    `).join('');
    }

    function toggleMobileNotifications() {
        const modal = document.getElementById('mobileNotificationsModal');
        modal.classList.toggle('hidden');
        document.body.style.overflow = modal.classList.contains('hidden') ? '' : 'hidden';
    }

    function toggleMobileProfile() {
        const modal = document.getElementById('mobileProfileModal');
        modal.classList.toggle('hidden');
        document.body.style.overflow = modal.classList.contains('hidden') ? '' : 'hidden';
    }

    function toggleMobileFeedback() {
        const modal = document.getElementById('mobileFeedbackModal');
        modal.classList.toggle('hidden');
        document.body.style.overflow = modal.classList.contains('hidden') ? '' : 'hidden';
    }

    checkMobileAdminSession();
</script>