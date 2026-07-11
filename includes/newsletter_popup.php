<?php
require_once __DIR__ . '/newsletter_settings.php';
$newsletterSettings = sr_newsletter_settings($pdo ?? null);
?>
<!-- includes/newsletter_popup.php -->
<div id="newsletterPopup"
    class="fixed bottom-4 right-4 md:bottom-8 md:right-8 z-50 transform translate-y-[150%] transition-transform duration-500 ease-out max-w-sm w-full p-6 bg-slate-800 border border-gold-500/30 rounded-2xl shadow-2xl hidden">

    <button id="closeNewsletter" class="absolute top-3 right-3 text-slate-400 hover:text-white transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
    </button>

    <div class="text-center mb-4">
        <div class="w-12 h-12 bg-gold-500/10 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">
            &#9993;
        </div>
        <h3 class="text-xl font-serif font-bold text-white mb-1"><?php echo htmlspecialchars($newsletterSettings['newsletter_popup_title']); ?></h3>
        <p class="text-sm text-slate-400"><?php echo htmlspecialchars($newsletterSettings['newsletter_popup_description']); ?></p>
    </div>

    <form id="newsletterForm" class="space-y-3">
        <div class="relative">
            <input type="email" id="newsletterEmail" required placeholder="<?php echo htmlspecialchars($newsletterSettings['newsletter_popup_placeholder']); ?>"
                class="w-full bg-slate-900 border border-slate-700 text-white rounded-lg px-4 py-3 focus:outline-none focus:border-gold-500 transition-colors text-sm placeholder:text-slate-600">
        </div>
        <button type="submit"
            class="w-full bg-gold-500 hover:bg-gold-400 text-navy-900 font-bold py-3 rounded-lg transition-colors uppercase tracking-wider text-xs">
            <?php echo htmlspecialchars($newsletterSettings['newsletter_popup_button']); ?>
        </button>
        <p id="newsletterMessage" class="text-xs text-center min-h-[1rem] transition-colors"></p>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const popup = document.getElementById('newsletterPopup');
        const closeBtn = document.getElementById('closeNewsletter');
        const form = document.getElementById('newsletterForm');
        const msg = document.getElementById('newsletterMessage');
        const emailInput = document.getElementById('newsletterEmail');

        // Check if already subscribed or closed for session
        if (localStorage.getItem('newsletter_closed') || localStorage.getItem('newsletter_subscribed')) {
            return;
        }

        // Unhide but keep translated down
        popup.classList.remove('hidden');

        // Scroll Detection
        let shown = false;
        window.addEventListener('scroll', () => {
            if (shown) return;

            const scrollPercent = (window.scrollY + window.innerHeight) / document.documentElement.scrollHeight;

            if (scrollPercent > 0.6) { // Show at 60% scroll
                popup.classList.remove('translate-y-[150%]');
                shown = true;
            }
        });

        // Close Handler
        closeBtn.addEventListener('click', () => {
            popup.classList.add('translate-y-[150%]');
            // Don't show again for this session/user preference
            localStorage.setItem('newsletter_closed', 'true');
        });

        // Submit Handler
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = emailInput.value;
            const btn = form.querySelector('button');
            const originalText = btn.innerText;

            btn.disabled = true;
            btn.innerText = 'Subscribing...';
            msg.innerText = '';
            msg.className = 'text-xs text-center min-h-[1rem]';

            try {
                const res = await fetch('<?php echo htmlspecialchars(public_path('newsletter_subscribe.php')); ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, source: 'popup' })
                });
                const data = await res.json();

                if (data.success) {
                    msg.innerText = data.message;
                    msg.classList.add('text-green-400');
                    localStorage.setItem('newsletter_subscribed', 'true');
                    setTimeout(() => {
                        popup.classList.add('translate-y-[150%]');
                    }, 2000);
                } else {
                    msg.innerText = data.message;
                    msg.classList.add('text-red-400');
                    btn.disabled = false;
                    btn.innerText = originalText;
                }
            } catch (error) {
                msg.innerText = "Error. Please try again.";
                msg.classList.add('text-red-400');
                btn.disabled = false;
                btn.innerText = originalText;
            }
        });
    });
</script>