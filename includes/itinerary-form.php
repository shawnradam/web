<!-- Itinerary Request Form Modal -->
<div x-data="{ 
    step: 1,
    loading: false,
    success: false,
    formData: {
        name: '', email: '', phone: '', pillar: 'LIFESTYLE', details: ''
    },

    submit() {
        if (!this.formData.name || !this.formData.email) return alert('Please complete all fields.');
        
        this.loading = true;
        
        // Simulate API call
        setTimeout(() => {
            this.loading = false;
            this.success = true;
            console.log('Itinerary Request:', JSON.parse(JSON.stringify(this.formData)));
        }, 1500);
    }
}"
x-show="openItinerary" 
x-cloak
class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-navy-900/90 backdrop-blur-sm">

    <div class="bg-navy-800 border border-slate-700 w-full max-w-lg shadow-2xl rounded-lg overflow-hidden flex flex-col max-h-[90vh]" @click.away="openItinerary = false">
        
        <!-- Header -->
        <div class="flex justify-between items-center p-4 border-b border-slate-700 bg-navy-900">
           <h2 class="text-white font-serif tracking-wide">REQUEST ITINERARY</h2>
           <button @click="openItinerary = false" class="text-slate-500 hover:text-white">
             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
           </button>
        </div>

        <div class="p-6 overflow-y-auto custom-scrollbar no-scrollbar">
            
            <!-- Success Message -->
            <div x-show="success" class="text-center py-10 animate-fade-in">
                <div class="w-16 h-16 bg-forest-900 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                </div>
                <h3 class="text-2xl font-serif text-white mb-2">Request Received</h3>
                <p class="text-slate-400 mb-6">
                    We have received your itinerary request. <br/>A lifestyle manager will contact you shortly to curate your Vietnam experience.
                </p>
                <button @click="openItinerary = false" class="border border-white text-white px-4 py-2 hover:bg-white hover:text-navy-900 transition-colors uppercase text-sm tracking-widest">Return to Portal</button>
            </div>

            <!-- Form -->
            <div x-show="!success" class="space-y-4 animate-fade-in">
                
                <div>
                    <label class="block text-xs uppercase tracking-wider text-slate-400 mb-1">Full Name</label>
                    <input x-model="formData.name" class="w-full bg-navy-900 border border-slate-600 p-3 text-white focus:border-forest-800 outline-none" placeholder="Ex. Shawn Radam">
                </div>
                <div>
                    <label class="block text-xs uppercase tracking-wider text-slate-400 mb-1">WhatsApp Number</label>
                    <input x-model="formData.phone" class="w-full bg-navy-900 border border-slate-600 p-3 text-white focus:border-forest-800 outline-none" placeholder="+60 12-345 6789">
                </div>
                <div>
                    <label class="block text-xs uppercase tracking-wider text-slate-400 mb-1">Email Address</label>
                    <input x-model="formData.email" type="email" class="w-full bg-navy-900 border border-slate-600 p-3 text-white focus:border-forest-800 outline-none" placeholder="contact@domain.com">
                </div>
                <div>
                    <label class="block text-xs uppercase tracking-wider text-slate-400 mb-1">Trip Details</label>
                    <textarea x-model="formData.details" rows="4" class="w-full bg-navy-900 border border-slate-600 p-3 text-white focus:border-forest-800 outline-none" placeholder="Travel dates, group size, specific interests..."></textarea>
                </div>

                <div class="flex justify-end pt-4">
                    <button @click="submit" :disabled="loading" class="bg-forest-900 text-white px-6 py-2 uppercase tracking-widest text-xs font-bold hover:bg-forest-800 disabled:opacity-50">
                        <span x-text="loading ? 'Processing...' : 'Submit Request'"></span>
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>
