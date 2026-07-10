<!-- Disclaimer Modal -->
<div x-data="{ openDisclaimer: false }" x-show="openDisclaimer"
    x-on:open-disclaimer-modal.window="openDisclaimer = true" x-cloak
    class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-navy-900/90 backdrop-blur-sm">

    <div class="bg-navy-800 border border-slate-700 w-full max-w-lg shadow-2xl p-8 relative"
        @click.away="openDisclaimer = false">
        <button @click="openDisclaimer = false" class="absolute top-4 right-4 text-slate-500 hover:text-white">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <h3 class="text-xl font-serif text-white mb-4">Consultancy Disclaimer</h3>
        <p class="text-slate-300 text-sm leading-relaxed mb-6">
            Native Title (NT) and Country Lease (CL) vetting are professional consultancy services provided by Shawn
            Radam's private office.
        </p>
        <p class="text-slate-300 text-sm leading-relaxed mb-8">
            This is <strong>not legal advice</strong> but strategic property guidance. All final transactions are
            subject to Malaysian Land Ordinance verification by a retained solicitor.
        </p>

        <button @click="openDisclaimer = false"
            class="w-full bg-slate-700 text-white py-2 uppercase tracking-widest text-xs font-bold hover:bg-slate-600 transition-colors">Acknowledge</button>
    </div>
</div>