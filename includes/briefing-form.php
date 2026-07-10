<div x-data="{ 
    step: 1,
    loading: false,
    success: false,
    serverMessage: '',
    formData: {
        name: '',
        email: '',
        phone: '',
        pillar: '',
        details: ''
    },

    next() {
        if (this.step === 1) {
            if (!this.formData.name || !this.formData.email || !this.formData.phone) return alert('Please complete all contact fields.');
        }
        if (this.step === 2) {
            if (!this.formData.pillar) return alert('Please select a service pillar.');
        }
        this.step++;
    },

    async submit() {
        if (!this.formData.details) return alert('Please provide briefing specifics.');

        this.loading = true;
        this.serverMessage = '';

        try {
            const response = await fetch('<?php echo htmlspecialchars(public_path('process_briefing.php')); ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(this.formData)
            });
            const result = await response.json();

            this.loading = false;
            this.serverMessage = result.message || 'Submission complete.';
            if (result.success) {
                this.success = true;
            }
        } catch (error) {
            this.loading = false;
            this.serverMessage = 'Unable to submit right now. Please try again.';
        }
    }
}">

    <div x-show="!success">
        <div class="flex justify-between items-center mb-6 border-b border-slate-700 pb-4">
            <span class="text-forest-900 text-xs uppercase tracking-widest font-bold">Inquiry Stage</span>
            <span class="text-slate-500 text-xs uppercase tracking-widest">Step <span x-text="step"></span> / 3</span>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Step 1: Identity -->
            <div x-show="step === 1" class="space-y-4 animate-fade-in">
                <div>
                    <label class="block text-xs uppercase tracking-widest text-slate-400 mb-2">Full Name</label>
                    <input x-model="formData.name"
                        class="w-full bg-navy-900 border border-slate-600 p-3 text-white focus:border-forest-900 outline-none transition-colors"
                        placeholder="Ex. Shawn Radam">
                </div>
                <div>
                    <label class="block text-xs uppercase tracking-widest text-slate-400 mb-2">WhatsApp Number</label>
                    <input x-model="formData.phone"
                        class="w-full bg-navy-900 border border-slate-600 p-3 text-white focus:border-forest-900 outline-none transition-colors"
                        placeholder="+60 12-345 6789">
                </div>
                <div>
                    <label class="block text-xs uppercase tracking-widest text-slate-400 mb-2">Email Address</label>
                    <input x-model="formData.email" type="email"
                        class="w-full bg-navy-900 border border-slate-600 p-3 text-white focus:border-forest-900 outline-none transition-colors"
                        placeholder="contact@domain.com">
                </div>
            </div>

            <!-- Step 2: Pillar Selection -->
            <div x-show="step === 2" class="grid grid-cols-1 gap-4 animate-fade-in">
                <template x-for="p in ['CAPITAL', 'ASSETS', 'LIFESTYLE']">
                    <div @click="formData.pillar = p"
                        :class="formData.pillar === p ? 'border-forest-900 bg-navy-900 text-white' : 'border-slate-600 bg-navy-900/50 text-slate-400 hover:border-slate-400 hover:text-white'"
                        class="cursor-pointer border p-4 text-center transition-all">
                        <span class="font-serif text-lg tracking-wide" x-text="p"></span>
                    </div>
                </template>
            </div>

            <!-- Step 3: Details -->
            <div x-show="step === 3" class="space-y-4 animate-fade-in">
                <div>
                    <label class="block text-xs uppercase tracking-widest text-slate-400 mb-2">Briefing
                        Specifics</label>
                    <textarea x-model="formData.details" rows="6"
                        class="w-full bg-navy-900 border border-slate-600 p-3 text-white focus:border-forest-900 outline-none transition-colors"
                        placeholder="Please describe your requirements, budget, or travel dates..."></textarea>
                </div>
            </div>

            <div class="flex justify-between pt-6 border-t border-slate-700 mt-8">
                <button type="button" x-show="step > 1" @click="step--"
                    class="text-slate-400 text-xs hover:text-white uppercase tracking-widest transition-colors">Back</button>
                <div x-show="step === 1"></div>

                <button type="button" x-show="step < 3" @click="next"
                    class="bg-white text-navy-900 px-8 py-3 uppercase tracking-widest text-xs font-bold hover:bg-slate-200 ml-auto transition-colors">Next
                    Step</button>

                <button type="button" x-show="step === 3" @click="submit" :disabled="loading"
                    class="bg-forest-900 text-white px-8 py-3 uppercase tracking-widest text-xs font-bold hover:bg-forest-800 disabled:opacity-50 ml-auto transition-colors">
                    <span x-text="loading ? 'Transmitting...' : 'Submit Briefing'"></span>
                </button>
            </div>

            <p x-show="serverMessage && !success" x-text="serverMessage" class="text-sm text-red-400 text-center"></p>

            <!-- Advisor Disclaimer -->
            <div class="mt-8 pt-6 border-t border-slate-800 text-center">
                <p class="text-[0.6rem] text-slate-500 uppercase tracking-wider">
                    Submissions are treated with strict confidentiality under Private Office protocols. <br /> This is a
                    strategic consultancy intake; not a public solicitation.
                </p>
            </div>
        </form>
    </div>

    <!-- Success Message -->
    <div x-show="success" class="text-center py-12 animate-fade-in">
        <div
            class="w-16 h-16 bg-forest-900/20 rounded-full flex items-center justify-center mx-auto mb-6 border border-forest-900">
            <svg class="w-8 h-8 text-forest-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h3 class="text-2xl font-serif text-white mb-2">Inquiry Logged</h3>
        <p class="text-slate-400 font-sans" x-text="serverMessage || 'Your briefing has been securely transmitted to the Private Office.'"></p>
    </div>
</div>