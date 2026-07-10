<!-- Admin Login Modal -->
<div id="adminLoginModal"
    class="fixed inset-0 bg-navy-900/95 backdrop-blur-md z-[100] hidden items-center justify-center p-6"
    onclick="closeAdminLogin(event)">
    <div class="bg-navy-800 border-2 border-gold-500 p-8 rounded-lg shadow-2xl w-full max-w-md relative"
        onclick="event.stopPropagation()">
        <!-- Close Button -->
        <button onclick="closeAdminLogin()"
            class="absolute top-4 right-4 text-slate-500 hover:text-white transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <h2 class="text-2xl font-serif text-white mb-6 text-center">Admin Access</h2>

        <form action="admin/login.php" method="POST">
            <div class="mb-4">
                <label class="block text-slate-400 text-sm uppercase tracking-wider mb-2">Username</label>
                <input type="text" name="username" id="adminUsername"
                    class="w-full bg-slate-900 border border-slate-600 rounded p-3 text-white focus:border-gold-500 outline-none"
                    required>
            </div>

            <div class="mb-6">
                <label class="block text-slate-400 text-sm uppercase tracking-wider mb-2">Password</label>
                <input type="password" name="password" id="adminPassword"
                    class="w-full bg-slate-900 border border-slate-600 rounded p-3 text-white focus:border-gold-500 outline-none"
                    required>
            </div>

            <button type="submit"
                class="w-full bg-gold-500 hover:bg-gold-400 text-navy-900 font-bold py-3 rounded transition-colors uppercase tracking-widest text-sm">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                Unlock Dashboard
            </button>
        </form>

        <p class="text-slate-600 text-xs text-center mt-6">Secure Private Office System</p>
    </div>
</div>

<!-- Admin Login Icon (Fixed Position) -->
<button onclick="openAdminLogin()"
    class="fixed bottom-6 right-6 bg-gold-500 hover:bg-gold-400 text-navy-900 p-4 rounded-full shadow-lg hover:shadow-gold-500/50 transition-all z-40 group">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
    </svg>
    <span
        class="absolute right-full mr-3 top-1/2 transform -translate-y-1/2 bg-navy-900 text-white px-3 py-1 rounded text-sm whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
        Admin Login
    </span>
</button>

<script>
    function openAdminLogin() {
        const modal = document.getElementById('adminLoginModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.getElementById('adminUsername').focus();
        document.body.style.overflow = 'hidden';
    }

    function closeAdminLogin(event) {
        if (event && event.target !== event.currentTarget) return;
        const modal = document.getElementById('adminLoginModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeAdminLogin();
        }
    });
</script>