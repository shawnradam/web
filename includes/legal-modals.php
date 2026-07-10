<!-- Legal Modals -->
<div x-data="{ openPrivacy: false, openTerms: false }" x-on:open-privacy-modal.window="openPrivacy = true"
    x-on:open-terms-modal.window="openTerms = true" class="relative z-[200]">

    <!-- Privacy Policy Modal -->
    <div x-show="openPrivacy" x-cloak
        class="fixed inset-0 flex items-center justify-center p-4 bg-navy-900/95 backdrop-blur-sm z-[201]">

        <div class="bg-navy-800 border border-slate-700 w-full max-w-4xl shadow-2xl rounded-lg overflow-hidden flex flex-col max-h-[90vh]"
            @click.away="openPrivacy = false">

            <!-- Header -->
            <div class="flex justify-between items-center p-6 border-b border-slate-700 bg-navy-900">
                <div>
                    <h2 class="text-white font-serif tracking-wide text-xl">PRIVACY & DATA PROTECTION</h2>
                    <p class="text-xs text-slate-500 uppercase tracking-widest mt-1">Compliance with PDPA</p>
                </div>
                <button @click="openPrivacy = false" class="text-slate-500 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="p-8 overflow-y-auto custom-scrollbar text-slate-300 leading-relaxed space-y-6">
                <p class="font-serif text-lg text-white">
                    At Shawn Radam | Private Advisor, we treat your data as a private asset. This policy outlines how we
                    handle information collected via shawnradam.com.
                </p>

                <div class="space-y-2">
                    <h3 class="text-gold-500 font-bold uppercase tracking-widest text-xs">Information Collection</h3>
                    <p class="text-sm">We collect personal identity data, contact information, and project/financial
                        briefings provided voluntarily through our intake forms.</p>
                </div>

                <div class="space-y-2">
                    <h3 class="text-gold-500 font-bold uppercase tracking-widest text-xs">Purpose of Use</h3>
                    <p class="text-sm">Data is used exclusively to evaluate your eligibility for property, finance, or
                        travel services and to facilitate direct communication with Shawn Radam’s private office.</p>
                </div>

                <div class="space-y-2">
                    <h3 class="text-gold-500 font-bold uppercase tracking-widest text-xs">Non-Disclosure</h3>
                    <p class="text-sm">Your project details and financial status are strictly confidential. We do not
                        sell, trade, or share your data with third-party marketing firms.</p>
                </div>

                <div class="space-y-2">
                    <h3 class="text-gold-500 font-bold uppercase tracking-widest text-xs">Third-Party Disclosure</h3>
                    <p class="text-sm">Data may only be shared with authorized third parties (e.g., banks, legal firms,
                        or land departments) with your explicit consent during the advisory process.</p>
                </div>

                <div class="space-y-2">
                    <h3 class="text-gold-500 font-bold uppercase tracking-widest text-xs">Data Security</h3>
                    <p class="text-sm">We implement industry-standard encryption to protect your briefings from
                        unauthorized access.</p>
                </div>
            </div>

            <div class="p-6 border-t border-slate-700 bg-navy-900 text-right">
                <button @click="openPrivacy = false"
                    class="text-white hover:text-gold-500 uppercase tracking-widest text-xs font-bold transition-colors">Close
                    Policy</button>
            </div>
        </div>
    </div>

    <!-- Terms of Service Modal -->
    <div x-show="openTerms" x-cloak
        class="fixed inset-0 flex items-center justify-center p-4 bg-navy-900/95 backdrop-blur-sm z-[201]">

        <div class="bg-navy-800 border border-slate-700 w-full max-w-4xl shadow-2xl rounded-lg overflow-hidden flex flex-col max-h-[90vh]"
            @click.away="openTerms = false">

            <!-- Header -->
            <div class="flex justify-between items-center p-6 border-b border-slate-700 bg-navy-900">
                <div>
                    <h2 class="text-white font-serif tracking-wide text-xl">TERMS OF SERVICE</h2>
                    <p class="text-xs text-slate-500 uppercase tracking-widest mt-1">Legal Disclaimers & Usage</p>
                </div>
                <button @click="openTerms = false" class="text-slate-500 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="p-8 overflow-y-auto custom-scrollbar text-slate-300 leading-relaxed space-y-6">
                <p class="font-serif text-lg text-white">
                    By engaging with Shawn Radam | Private Advisor ("the Advisor") and this digital platform, you
                    acknowledge and agree to the following terms.
                </p>

                <div class="space-y-2">
                    <h3 class="text-gold-500 font-bold uppercase tracking-widest text-xs">Nature of Advisory</h3>
                    <p class="text-sm">The Advisor provides strategic consultancy on property, finance, and logistics.
                        This does not constitute certified legal, tax, or investment advice unless explicitly stated by
                        retained professionals.</p>
                </div>

                <div class="space-y-2">
                    <h3 class="text-gold-500 font-bold uppercase tracking-widest text-xs">Property Listings</h3>
                    <p class="text-sm">All listings for Native Title (NT) and Country Lease (CL) lands are subject to
                        final searching and verification at the relevant Land Office. Prices and availability are
                        subject to change without prior notice.</p>
                </div>

                <div class="space-y-2">
                    <h3 class="text-gold-500 font-bold uppercase tracking-widest text-xs">Financial Services</h3>
                    <p class="text-sm">Loan facilitation and investment products are brokered through licensed
                        institutions. Final approval rests with the respective banks or funding bodies.</p>
                </div>

                <div class="space-y-2">
                    <h3 class="text-gold-500 font-bold uppercase tracking-widest text-xs">Limitation of Liability</h3>
                    <p class="text-sm">The Advisor shall not be liable for any direct, indirect, or consequential losses
                        arising from the use of information provided on this platform. Users are encouraged to conduct
                        their own due diligence.</p>
                </div>
            </div>

            <div class="p-6 border-t border-slate-700 bg-navy-900 text-right">
                <button @click="openTerms = false"
                    class="text-white hover:text-gold-500 uppercase tracking-widest text-xs font-bold transition-colors">Close
                    Terms</button>
            </div>
        </div>
    </div>

</div>