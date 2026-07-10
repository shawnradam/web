<?php
/**
 * Interactive Personal Loan & Property Calculator Modal (Fullscreen Dedicated View)
 */
?>

<div x-data="{ cardOpen: false, activeTab: 'loan' }" @open-loan-calculator.window="cardOpen = true" x-init="$watch('cardOpen', val => { document.body.style.overflow = val ? 'hidden' : '' })" x-cloak>
    <div x-show="cardOpen" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" 
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" 
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[9999] overflow-y-auto no-scrollbar"
        style="background-color: #070d19;">

        <div x-show="cardOpen" 
            x-transition:enter="transition ease-out duration-300 delay-100"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200" 
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" 
            class="relative w-full min-h-screen p-6 md:p-12 pb-24"
            style="background-color: #070d19;">

            <!-- Exit Button -->
            <button @click.stop="cardOpen = false"
                class="absolute top-6 right-6 md:top-8 md:right-8 z-50 w-10 h-10 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white/80 hover:text-white transition-all duration-300 backdrop-blur-sm border border-white/20 cursor-pointer"
                title="Exit Calculator">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <!-- Decorative Glow Background Effects -->
            <div class="absolute top-0 right-0 w-96 h-96 bg-gradient-to-br from-blue-500/10 to-transparent rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-gradient-to-tr from-cyan-500/10 to-transparent rounded-full blur-3xl pointer-events-none"></div>

            <!-- Centered Layout Container -->
            <div class="max-w-5xl mx-auto pt-8 md:pt-4 relative z-10">

                <!-- Tab Headers -->
                <div class="flex justify-center border-b border-slate-700 mb-8">
                    <button @click="activeTab = 'loan'" 
                        :class="activeTab === 'loan' ? 'border-gold-500 text-white font-bold' : 'border-transparent text-slate-400'" 
                        class="px-6 py-3 border-b-2 text-xs md:text-sm uppercase tracking-wider transition-all duration-300 cursor-pointer bg-transparent border-0 outline-none">
                        Personal Loan
                    </button>
                    <button @click="activeTab = 'property'" 
                        :class="activeTab === 'property' ? 'border-gold-500 text-white font-bold' : 'border-transparent text-slate-400'" 
                        class="px-6 py-3 border-b-2 text-xs md:text-sm uppercase tracking-wider transition-all duration-300 cursor-pointer bg-transparent border-0 outline-none">
                        Property Calculator
                    </button>
                </div>

                <!-- Tab 1: Personal Loan -->
                <div x-show="activeTab === 'loan'">
                    <div class="text-center mb-8">
                        <h3 class="text-3xl font-serif text-white mb-2">Personal Loan Calculator</h3>
                        <p class="text-slate-400 text-sm font-light max-w-md mx-auto">
                            Determine your financing installments, upfront fee deductions, and net cash payout in real-time.
                        </p>
                    </div>
                    <div>
                        <?php echo KoperasiLoanCalculator::render('modal-loan-calculator', [
                            'theme' => 'cosmic',
                            'defaultLoan' => 30000,
                            'defaultTenure' => 5,
                            'calculationMode' => 'flat'
                        ]); ?>
                    </div>
                </div>

                <!-- Tab 2: Property Calculator -->
                <div x-show="activeTab === 'property'" x-cloak>
                    <div class="text-center mb-8">
                        <h3 class="text-3xl font-serif text-white mb-2">Property & Land Calculator</h3>
                        <p class="text-slate-400 text-sm font-light max-w-md mx-auto">
                            Calculate stamp duties, mortgage instalments, and Sabah land premium conversion rates.
                        </p>
                    </div>
                    <div>
                        <?php 
                        require_once 'plugins/property-calculator/property-calculator.php';
                        echo PropertyCalculator::render('modal-property-calculator'); 
                        ?>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>
