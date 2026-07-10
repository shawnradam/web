<?php include 'includes/header.php'; ?>
<?php include 'includes/navigation.php'; ?>

<!-- Hero Section -->
<section class="relative min-h-[85vh] flex items-center justify-center overflow-hidden py-20">
    <div class="absolute inset-0 z-0">
        <img src="https://picsum.photos/seed/sabahview/1920/1080" alt="Hero Background"
            class="w-full h-full object-cover opacity-30 grayscale" />
        <div class="absolute inset-0 bg-gradient-to-b from-navy-900/80 via-navy-900/60 to-navy-900"></div>
    </div>

    <div class="relative z-10 text-center max-w-5xl px-6">
        <p class="text-forest-800 font-medium tracking-[0.3em] uppercase mb-4 animate-fade-in">Private Wealth Office</p>
        <h1 class="text-4xl md:text-6xl font-serif text-white mb-6 leading-tight animate-fade-in">
            Strategic Guidance for <br /> Properties & Financing Loans.
        </h1>
        <div class="w-24 h-1 bg-white mx-auto mb-8"></div>
        <p class="text-xl md:text-2xl text-slate-300 max-w-4xl mx-auto mb-12 font-light leading-relaxed">
            De-risking property acquisitions and securing optimal bank/LPPSA <br class="hidden md:block" /> financing solutions for your capital growth.
        </p>

        <!-- Technical Trust Bar -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-3xl mx-auto border-t border-slate-700/50 pt-8 mt-8">
            <div class="flex flex-col items-center">
                <svg class="w-8 h-8 text-gold-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-slate-400 text-sm uppercase tracking-wide font-medium">CL/NT Title Specialist</span>
            </div>
            <div class="flex flex-col items-center">
                <svg class="w-8 h-8 text-gold-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span class="text-slate-400 text-sm uppercase tracking-wide font-medium">LPPSA/Bank Liaison</span>
            </div>
            <div class="flex flex-col items-center">
                <svg class="w-8 h-8 text-gold-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-slate-400 text-sm uppercase tracking-wide font-medium">Property & Land Advisor</span>
            </div>
        </div>

        <button onclick="document.getElementById('pillars').scrollIntoView({ behavior: 'smooth'})"
            class="animate-bounce mt-12 text-slate-500 hover:text-white transition-colors">
            <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
            </svg>
        </button>
    </div>
</section>

<!-- The Pillars -->
<section id="pillars" class="py-24 px-6 bg-[#0A192F]">
    <div class="max-w-5xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

            <!-- Assets Pillar -->
            <a href="<?php echo htmlspecialchars(lang_url('properties.php')); ?>"
                class="group relative h-96 overflow-hidden border border-slate-800 hover:border-forest-900 transition-colors">
                <div class="absolute inset-0 bg-navy-800 group-hover:bg-navy-900 transition-colors"></div>
                <div class="absolute inset-0 flex flex-col justify-center items-center p-8 z-10">
                    <h3
                        class="text-3xl font-serif text-white mb-4 group-hover:translate-y-[-10px] transition-transform">
                        PROPERTIES</h3>
                    <div class="w-12 h-0.5 bg-forest-900 mb-6 group-hover:w-24 transition-all"></div>
                    <p
                        class="text-center text-slate-400 mb-6 opacity-0 group-hover:opacity-100 transition-opacity duration-500 delay-100">
                        CL/NT Land Acquisition & <br /> Property Valuation Advisory
                    </p>
                    <span class="text-xs uppercase tracking-widest text-forest-800">View Properties &rarr;</span>
                </div>
            </a>

            <!-- Capital Pillar -->
            <a href="<?php echo htmlspecialchars(lang_url('finance.php')); ?>"
                class="group relative h-96 overflow-hidden border border-slate-800 hover:border-forest-900 transition-colors">
                <div class="absolute inset-0 bg-navy-800 group-hover:bg-navy-900 transition-colors"></div>
                <div class="absolute inset-0 flex flex-col justify-center items-center p-8 z-10">
                    <h3
                        class="text-3xl font-serif text-white mb-4 group-hover:translate-y-[-10px] transition-transform">
                        LOANS FINANCING</h3>
                    <div class="w-12 h-0.5 bg-forest-900 mb-6 group-hover:w-24 transition-all"></div>
                    <p
                        class="text-center text-slate-400 mb-6 opacity-0 group-hover:opacity-100 transition-opacity duration-500 delay-100">
                        Strategic LPPSA Government Lending & <br /> Personal/Mortgage Loans Financing
                    </p>
                    <span class="text-xs uppercase tracking-widest text-forest-800">Explore Loans &rarr;</span>
                </div>
            </a>

        </div>
    </div>
</section>

<?php include 'includes/feedback_popup.php'; ?>
<?php include 'includes/footer.php'; ?>