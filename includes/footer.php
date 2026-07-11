<?php
require_once 'includes/lang.php';
require_once 'includes/newsletter_settings.php';
require_once 'plugins/koperasi-loan-calculator/koperasi-calculator.php';
require_once 'plugins/property-calculator/property-calculator.php';
KoperasiLoanCalculator::register_assets();
PropertyCalculator::register_assets();
$newsletterSettings = sr_newsletter_settings($pdo ?? null);
?>
<footer class="bg-navy-900 border-t border-slate-800 pt-16 pb-8 px-6">
    <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
        <div class="col-span-1 md:col-span-2">
            <h4 class="font-serif text-2xl text-white mb-4">Shawn Radam</h4>
            <p class="text-slate-500 max-w-sm mb-6"><?php echo e_t('footer.brand_description'); ?></p>
            <div class="text-forest-800 font-medium"><?php echo e_t('footer.brand_label'); ?></div>
        </div>

        <div>
            <h5 class="text-white font-serif mb-4"><?php echo e_t('footer.services'); ?></h5>
            <ul class="space-y-2 text-sm text-slate-400">
                <li><a href="<?php echo htmlspecialchars(lang_url('properties.php')); ?>" class="hover:text-gold-500 transition-colors"><?php echo e_t('footer.service_property'); ?></a></li>
                <li><a href="<?php echo htmlspecialchars(lang_url('finance.php')); ?>" class="hover:text-gold-500 transition-colors"><?php echo e_t('footer.service_loans'); ?></a></li>
                <li><a href="<?php echo htmlspecialchars(lang_url('finance.php')); ?>" class="hover:text-gold-500 transition-colors"><?php echo e_t('footer.service_eligibility'); ?></a></li>
                <li><a href="<?php echo htmlspecialchars(lang_url('calculators.php')); ?>" class="hover:text-gold-500 transition-colors"><?php echo e_t('footer.service_calculators'); ?></a></li>
            </ul>
        </div>

        <div>
            <h5 class="text-white font-serif mb-4"><?php echo e_t('footer.legal'); ?></h5>
            <ul class="space-y-2 text-sm text-slate-400">
                <li><button @click="$dispatch('open-privacy-modal')"
                        class="text-left hover:text-white transition-colors"><?php echo e_t('footer.privacy'); ?></button></li>
                <li><button @click="$dispatch('open-terms-modal')"
                        class="text-left hover:text-white transition-colors"><?php echo e_t('footer.terms'); ?></button></li>
            </ul>
        </div>
    </div>
    <div class="relative overflow-hidden border border-gold-500/25 bg-slate-950/70 mb-12">
        <div class="absolute inset-y-0 right-0 w-1/2 bg-[radial-gradient(circle_at_top_right,rgba(212,175,55,0.18),transparent_40%)] pointer-events-none"></div>
        <div class="relative grid lg:grid-cols-[1fr_0.95fr] gap-8 p-6 sm:p-8 lg:p-10 items-center">
            <div>
                <p class="text-gold-500 uppercase tracking-[0.32em] text-xs font-bold mb-3"><?php echo htmlspecialchars($newsletterSettings['newsletter_footer_kicker']); ?></p>
                <h4 class="font-serif text-3xl sm:text-4xl text-white mb-4"><?php echo htmlspecialchars($newsletterSettings['newsletter_footer_title']); ?></h4>
                <p class="text-slate-400 max-w-2xl leading-relaxed"><?php echo htmlspecialchars($newsletterSettings['newsletter_footer_description']); ?></p>
            </div>
            <form id="footerNewsletterForm" class="bg-navy-900/80 border border-slate-700 p-4 sm:p-5" novalidate>
                <label for="footerNewsletterEmail" class="block text-xs uppercase tracking-widest text-slate-400 mb-2"><?php echo htmlspecialchars($newsletterSettings['newsletter_footer_label']); ?></label>
                <div class="grid sm:grid-cols-[1fr_auto] gap-3">
                    <input id="footerNewsletterEmail" name="email" type="email" required placeholder="<?php echo htmlspecialchars($newsletterSettings['newsletter_footer_placeholder']); ?>" class="w-full bg-slate-950 border border-slate-700 text-white px-4 py-3 outline-none focus:border-gold-500 placeholder:text-slate-600">
                    <button type="submit" class="bg-gold-500 hover:bg-gold-400 text-navy-900 px-6 py-3 text-xs font-bold uppercase tracking-wider transition-colors whitespace-nowrap"><?php echo htmlspecialchars($newsletterSettings['newsletter_footer_button']); ?></button>
                </div>
                <p id="footerNewsletterMessage" class="min-h-[1.25rem] mt-3 text-sm text-slate-400"></p>
                <p class="text-[11px] text-slate-500 leading-relaxed"><?php echo htmlspecialchars($newsletterSettings['newsletter_footer_note']); ?></p>
            </form>
        </div>
    </div>

    <div class="border-t border-slate-800 pt-8 space-y-4">
        <!-- Business Address -->
        <div class="text-center text-sm text-slate-500">
            <p class="mb-1">Lot 3, Second Floor, Kg. Batangan, 3-Storey Shoplot</p>
            <p>Jalan Bolong, 89200 Tuaran, Sabah, Malaysia</p>
        </div>

        <!-- Association Link -->
        <div class="text-center text-xs text-slate-600">
            <p>
                <a href="https://tanahlotsabah.com" target="_blank" rel="noopener noreferrer"
                    class="hover:text-gold-500 transition-colors">
                    shawnradam.com
                </a>
                <?php echo e_t('footer.associate_text'); ?>
                <a href="https://tanahlotsabah.com" target="_blank" rel="noopener noreferrer"
                    class="hover:text-gold-500 transition-colors font-medium">
                    tanahlotsabah.com
                </a>
            </p>
        </div>

        <!-- Copyright -->
        <div class="flex flex-col md:flex-row justify-between items-center text-xs text-slate-600 pt-4 border-t border-slate-800">
            <div class="flex items-center gap-2">
                <span>&copy; <?php echo date('Y'); ?> Shawn Radam. <?php echo e_t('footer.rights'); ?></span>
                <button onclick="window.dispatchEvent(new CustomEvent('open-digital-card'))" class="text-gold-500 hover:text-white transition-all cursor-pointer flex items-center gap-1.5 bg-gold-500/10 hover:bg-gold-500/20 px-2 py-1 rounded border border-gold-500/30" title="<?php echo e_t('footer.digital_card_title'); ?>">
                    <svg class="w-4.5 h-4.5 inline-block" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M3 3h8v8H3zm2 2v4h4V5zm8-2h8v8h-8zm2 2v4h4V5zM3 13h8v8H3zm2 2v4h4v-4zm13 0h3v3h-3zm-2-2h3v3h-3zm2 4h3v3h-3zm-4-4h3v3h-3zm2-2h3v3h-3zm-4 4h3v3h-3zm2 2h3v3h-3zm-2-6h3v3h-3zm4 0h3v3h-3z"/>
                    </svg>
                    <span class="text-[10px] font-bold uppercase tracking-wider"><?php echo e_t('footer.digital_card'); ?></span>
                </button>
            </div>
            <p><?php echo e_t('footer.location'); ?></p>
        </div>
    </div>
</footer>
</div> <!-- Close min-h-screen -->

<?php
// Check if show_floating_calculator is enabled
$showFloatingCalc = true;
$floatingCalcText = t('footer.floating_calculator');
try {
    if (!isset($pdo)) {
        require_once 'admin/db_connect.php';
    }
    $stmt = $pdo->query("SELECT setting_value FROM site_settings WHERE setting_key = 'show_floating_calculator' LIMIT 1");
    $dbVal = $stmt->fetchColumn();
    if ($dbVal !== false) {
        $showFloatingCalc = ($dbVal == '1');
    }
    
    $stmt = $pdo->query("SELECT setting_value FROM site_settings WHERE setting_key = 'floating_calculator_text' LIMIT 1");
    $dbText = $stmt->fetchColumn();
    if ($dbText !== false && !empty(trim($dbText))) {
        $floatingCalcText = trim($dbText);
    }
} catch (Exception $e) {}
?>

<?php if ($showFloatingCalc): ?>
<!-- Visitor Floating Loan Calculator Icon (Bottom Left) -->
<div id="visitorDigitalCardIcon" x-data
    class="fixed bottom-6 left-6 z-[60] animate-bounce-subtle transition-opacity duration-500">
    <button @click="$dispatch('open-loan-calculator')"
        class="group flex items-center justify-center w-16 h-16 rounded-full bg-navy-900 border border-gold-500 text-gold-500 shadow-2xl hover:bg-gold-500 hover:text-navy-900 transition-all duration-300 hover:scale-110 relative"
        title="<?php echo htmlspecialchars($floatingCalcText); ?>">
        <!-- SVG Calculator Icon -->
        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <rect x="4" y="2" width="16" height="20" rx="2" stroke-linecap="round" stroke-linejoin="round" />
            <line x1="8" y1="6" x2="16" y2="6" stroke-linecap="round" stroke-linejoin="round" />
            <line x1="8" y1="10" x2="16" y2="10" stroke-linecap="round" stroke-linejoin="round" />
            <circle cx="8" cy="14" r="1" />
            <circle cx="12" cy="14" r="1" />
            <circle cx="16" cy="14" r="1" />
            <circle cx="8" cy="18" r="1" />
            <circle cx="12" cy="18" r="1" />
            <circle cx="16" cy="18" r="1" />
        </svg>
        <span
            class="absolute left-full ml-4 bg-navy-900 text-gold-500 text-xs font-bold px-3 py-2 rounded-lg border border-gold-500/30 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none shadow-xl transform translate-x-2 group-hover:translate-x-0 duration-300">
            <?php echo htmlspecialchars($floatingCalcText); ?>
        </span>

        <!-- Pulse Effect -->
        <span class="absolute inset-0 rounded-full bg-gold-500 opacity-20 animate-ping pointer-events-none"></span>
    </button>
</div>
<?php endif; ?>

<?php include 'includes/disclaimer-modal.php'; ?>
<?php include 'includes/legal-modals.php'; ?>
<?php include 'includes/loan_calculator_modal.php'; ?>

<!-- Go to Top Button (Executive Gold, matching site theme) -->
<button id="goToTopBtn" onclick="window.scrollTo({top: 0, behavior: 'smooth'})"
    class="fixed bottom-6 right-6 z-[60] flex items-center justify-center w-12 h-12 rounded-full bg-gold-500 text-navy-900 shadow-2xl hover:bg-gold-400 transition-all duration-300 hover:scale-110 opacity-0 pointer-events-none translate-y-4 transform border border-gold-500/30"
    title="<?php echo e_t('footer.go_to_top'); ?>">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
    </svg>
</button>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('footerNewsletterForm');
        if (!form) return;

        const email = document.getElementById('footerNewsletterEmail');
        const message = document.getElementById('footerNewsletterMessage');
        const button = form.querySelector('button[type="submit"]');
        const originalText = button ? button.textContent : 'Subscribe';

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            if (!email || !button || !message) return;

            if (!email.validity.valid) {
                message.textContent = 'Please enter a valid email address.';
                message.className = 'min-h-[1.25rem] mt-3 text-sm text-red-400';
                return;
            }

            button.disabled = true;
            button.textContent = 'Subscribing...';
            message.textContent = '';
            message.className = 'min-h-[1.25rem] mt-3 text-sm text-slate-400';

            try {
                const response = await fetch('<?php echo htmlspecialchars(public_path('newsletter_subscribe.php')); ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: email.value, source: 'footer' })
                });
                const result = await response.json();
                message.textContent = result.message || 'Subscription received.';
                message.className = result.success
                    ? 'min-h-[1.25rem] mt-3 text-sm text-green-400'
                    : 'min-h-[1.25rem] mt-3 text-sm text-red-400';

                if (result.success) {
                    form.reset();
                }
            } catch (error) {
                message.textContent = 'Unable to subscribe right now. Please try again.';
                message.className = 'min-h-[1.25rem] mt-3 text-sm text-red-400';
            } finally {
                button.disabled = false;
                button.textContent = originalText;
            }
        });
    });
</script>
<script>
    // Show "Go to Top" button when user scrolls down and reaches the footer
    window.addEventListener('scroll', function () {
        const btn = document.getElementById('goToTopBtn');
        const footer = document.querySelector('footer');
        if (!btn || !footer) return;

        const footerRect = footer.getBoundingClientRect();
        
        // Triggers visibility when the footer is scrolling into view
        if (footerRect.top < window.innerHeight) {
            btn.classList.remove('opacity-0', 'pointer-events-none', 'translate-y-4');
            btn.classList.add('opacity-100', 'pointer-events-auto', 'translate-y-0');
        } else {
            btn.classList.add('opacity-0', 'pointer-events-none', 'translate-y-4');
            btn.classList.remove('opacity-100', 'pointer-events-auto', 'translate-y-0');
        }
    });
</script>

<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('<?php echo htmlspecialchars(public_path("sw.js")); ?>', { scope: '<?php echo htmlspecialchars(public_path()); ?>' })
                .then(reg => console.log('Service Worker registered.'))
                .catch(err => console.error('Service Worker registration failed.', err));
        });
    }
</script>

</body>

</html>


