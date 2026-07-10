<?php
require_once 'includes/lang.php';
// includes/digital_card_modal.php
// Executive Profile Card - Premium "Black Card" Design

// Fetch profile data from database
require_once 'admin/db_connect.php';
try {
    $execProfile = null;
    try {
        $execProfile = $pdo->query("SELECT * FROM digital_business_cards WHERE is_default = 1 AND is_active = 1 LIMIT 1")->fetch();
        if (!$execProfile) {
            $execProfile = $pdo->query("SELECT * FROM digital_business_cards WHERE is_active = 1 LIMIT 1")->fetch();
        }
    } catch (Exception $e) {}

    if (!$execProfile) {
        $stmt = $pdo->query("SELECT * FROM executive_profile_settings LIMIT 1");
        $execProfile = $stmt->fetch();
    }
    
    if (!$execProfile) {
        throw new Exception("No profile found");
    }
} catch (Exception $e) {
    // Fallback to default values if database query fails
    $execProfile = [
        'full_name' => 'Shawn Radam',
        'title' => 'Private Advisory',
        'portrait_url' => '',
        'expertise_tag_1' => 'Asset Acquisition',
        'expertise_tag_2' => 'Structured Lending',
        'expertise_tag_3' => 'Travel Logistics',
        'years_experience' => '12+',
        'deals_closed' => '250+',
        'rating' => '5.0',
        'primary_button_text' => 'Request Briefing',
        'primary_button_link' => 'developer-briefing.php',
        'secondary_button_text' => 'Get in Touch',
        'secondary_button_link' => 'contact.php'
    ];
}
?>

<!-- Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link
    href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Inter:wght@400;500;600;700&display=swap"
    rel="stylesheet">

<!-- Executive Profile Card Modal -->
<div x-data="{ cardOpen: false }" @open-digital-card.window="cardOpen = true" x-init="$watch('cardOpen', val => { document.body.style.overflow = val ? 'hidden' : '' })">
    <div x-show="cardOpen" x-cloak x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/90 backdrop-blur-md p-4"
        @click.self="cardOpen = false">

        <!-- Executive Card Container - Optimized Size -->
        <div x-show="cardOpen" x-transition:enter="transition ease-out duration-300 delay-100"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95" class="relative w-full max-w-md">

            <!-- The Card -->
            <div class="relative bg-gradient-to-br from-[#0A192F] via-[#0D1F3C] to-[#0A192F] rounded-2xl shadow-2xl border border-white/10 max-h-[90vh] overflow-y-auto"
                style="font-family: 'Inter', sans-serif;">

                <!-- Close Button - FIXED with @click.stop -->
                <button @click.stop="cardOpen = false"
                    class="absolute top-4 right-4 z-50 w-8 h-8 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white/80 hover:text-white transition-all duration-300 backdrop-blur-sm border border-white/20 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>

                <!-- Decorative Elements -->
                <div
                    class="absolute top-0 right-0 w-48 h-48 bg-gradient-to-br from-emerald-500/10 to-transparent rounded-full blur-3xl">
                </div>
                <div
                    class="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-tr from-amber-500/10 to-transparent rounded-full blur-3xl">
                </div>

                <!-- Card Content -->
                <div class="relative z-10 p-6 md:p-8">

                    <!-- Header Section -->
                    <div class="flex flex-col items-center text-center mb-6">

                        <!-- Portrait - FIXED to show uploaded image -->
                        <div class="relative mb-4">
                            <div
                                class="absolute inset-0 bg-gradient-to-br from-amber-400 via-emerald-400 to-amber-400 rounded-full blur-md opacity-75 animate-pulse">
                            </div>
                            <div
                                class="relative w-24 h-24 rounded-full overflow-hidden border-[3px] border-white/20 shadow-2xl bg-gradient-to-br from-slate-700 to-slate-800">
                                <?php if (!empty($execProfile['portrait_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($execProfile['portrait_url']); ?>"
                                        alt="<?php echo htmlspecialchars($execProfile['full_name']); ?>"
                                        class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center text-3xl text-white/40">
                                        <?php echo strtoupper(substr($execProfile['full_name'], 0, 2)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Name with Verification Badge -->
                        <h2 class="flex items-center justify-center gap-1.5 mb-1"
                            style="font-family: 'Playfair Display', serif;">
                            <span
                                class="text-2xl md:text-3xl font-bold text-white tracking-tight"><?php echo htmlspecialchars($execProfile['full_name']); ?></span>
                            <svg class="w-5 h-5 md:w-6 md:h-6 text-blue-400 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 24 24">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </h2>

                        <!-- Title -->
                        <p class="text-xs uppercase tracking-[0.2em] text-emerald-400 font-semibold mb-4">
                            <?php echo htmlspecialchars($execProfile['title']); ?>
                        </p>

                        <!-- Expertise Tags -->
                        <div class="flex flex-wrap items-center justify-center gap-1.5 mb-5">
                            <span
                                class="px-3 py-1 rounded-full bg-white/5 border border-white/10 text-white/80 text-[10px] font-medium backdrop-blur-sm">
                                <?php echo htmlspecialchars($execProfile['expertise_tag_1']); ?>
                            </span>
                            <span
                                class="px-3 py-1 rounded-full bg-white/5 border border-white/10 text-white/80 text-[10px] font-medium backdrop-blur-sm">
                                <?php echo htmlspecialchars($execProfile['expertise_tag_2']); ?>
                            </span>
                            <span
                                class="px-3 py-1 rounded-full bg-white/5 border border-white/10 text-white/80 text-[10px] font-medium backdrop-blur-sm">
                                <?php echo htmlspecialchars($execProfile['expertise_tag_3']); ?>
                            </span>
                        </div>

                        <!-- Trust Bar / Metrics -->
                        <div class="w-full bg-white/5 backdrop-blur-sm rounded-xl border border-white/10 p-4 mb-5">
                            <div class="grid grid-cols-3 gap-3">
                                <!-- Years Experience -->
                                <div class="text-center">
                                    <div class="text-xl md:text-2xl font-bold text-white mb-0.5"
                                        style="font-family: 'Playfair Display', serif;">
                                        <?php echo htmlspecialchars($execProfile['years_experience']); ?>
                                    </div>
                                    <div
                                        class="text-[9px] md:text-[10px] uppercase tracking-wider text-white/50 font-medium">
                                        Years Exp
                                    </div>
                                </div>

                                <!-- Deals Closed -->
                                <div class="text-center border-x border-white/10">
                                    <div class="text-xl md:text-2xl font-bold text-white mb-0.5"
                                        style="font-family: 'Playfair Display', serif;">
                                        <?php echo htmlspecialchars($execProfile['deals_closed']); ?>
                                    </div>
                                    <div
                                        class="text-[9px] md:text-[10px] uppercase tracking-wider text-white/50 font-medium">
                                        Deals Closed
                                    </div>
                                </div>

                                <!-- Rating -->
                                <div class="text-center">
                                    <div class="text-xl md:text-2xl font-bold text-white mb-0.5 flex items-center justify-center gap-1"
                                        style="font-family: 'Playfair Display', serif;">
                                        <?php echo htmlspecialchars($execProfile['rating']); ?>
                                        <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    </div>
                                    <div
                                        class="text-[9px] md:text-[10px] uppercase tracking-wider text-white/50 font-medium">
                                        Rating
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <?php
                        $priLink = $execProfile['primary_button_link'] ?? '';
                        if (preg_match('/^[0-9+ ]+$/', trim($priLink))) {
                            $priLink = 'tel:' . str_replace(' ', '', trim($priLink));
                        }
                        $priLink = lang_url($priLink);

                        $secLink = $execProfile['secondary_button_link'] ?? '';
                        if (preg_match('/^[0-9+ ]+$/', trim($secLink))) {
                            $secLink = 'tel:' . str_replace(' ', '', trim($secLink));
                        }
                        $secLink = lang_url($secLink);
                        ?>
                        <div class="w-full space-y-2.5">
                            <!-- Primary: Request Briefing -->
                            <a href="<?php echo htmlspecialchars($priLink); ?>"
                                class="block w-full py-3 px-5 rounded-xl bg-gradient-to-r from-amber-500 via-amber-400 to-amber-500 hover:from-amber-400 hover:via-amber-300 hover:to-amber-400 text-[#0A192F] font-bold text-xs uppercase tracking-wider transition-all duration-300 shadow-lg hover:shadow-amber-500/50 hover:scale-[1.02] text-center">
                                <?php echo htmlspecialchars($execProfile['primary_button_text'] ?? ''); ?>
                            </a>

                            <!-- Secondary: View Credentials -->
                            <a href="<?php echo htmlspecialchars($secLink); ?>"
                                class="block w-full py-3 px-5 rounded-xl border-2 border-white/20 hover:border-white/40 text-white font-semibold text-xs uppercase tracking-wider transition-all duration-300 hover:bg-white/5 text-center backdrop-blur-sm">
                                <?php echo htmlspecialchars($execProfile['secondary_button_text'] ?? ''); ?>
                            </a>
                        </div>

                    </div>

                    <!-- Footer Badge -->
                    <div class="text-center pt-4 border-t border-white/10">
                        <p class="text-[10px] text-white/40 uppercase tracking-widest font-medium">
                            Exclusive Advisory Services
                        </p>
                    </div>

                </div>

            </div>

        </div>
    </div>
</div>

<style>
    /* Custom animations for premium feel */
    @keyframes shimmer {
        0% {
            background-position: -200% center;
        }

        100% {
            background-position: 200% center;
        }
    }

    .animate-shimmer {
        background-size: 200% auto;
        animation: shimmer 3s linear infinite;
    }
</style>
