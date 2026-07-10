<!-- Investment Gateway Form Modal -->
<div x-data="{ 
    step: 1,
    loading: false,
    success: false,
    formData: {
        fullName: '', professionalTitle: '', sourceOfIncome: 'Business Operations',
        investorStatus: [], liquidityRange: 'RM 1M - RM 5M', areasOfInterest: [],
        investmentHorizon: 'Medium Term (2-5 years)', email: '', preferredCommunication: 'WhatsApp',
        timezoneLocation: '', currentChallenges: ''
    },

    toggleCheckbox(arrayName, value) {
        if (this.formData[arrayName].includes(value)) {
            this.formData[arrayName] = this.formData[arrayName].filter(item => item !== value);
        } else {
            this.formData[arrayName].push(value);
        }
    },

    submit() {
        if (!this.formData.email) return alert('Please enter a valid email address.');
        
        this.loading = true;
        
        // Simulate API call
        setTimeout(() => {
            this.loading = false;
            this.success = true;
            console.log('Investment Data:', JSON.parse(JSON.stringify(this.formData)));
        }, 1500);
    }
}"
x-show="openGateway" 
x-cloak
class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-black/95 backdrop-blur-md">

    <div class="bg-navy-900 border border-gold-500/30 w-full max-w-2xl shadow-2xl shadow-gold-900/10 rounded-none flex flex-col max-h-[90vh] relative" @click.away="openGateway = false">
        
        <!-- Header -->
        <div class="flex justify-between items-center p-6 border-b border-gold-500/20 bg-navy-900">
           <div>
             <h2 class="text-white font-serif tracking-widest text-lg">THE INVESTMENT GATEWAY</h2>
             <p class="text-xs text-gold-500 uppercase tracking-[0.2em] mt-1">Private Wealth Intake</p>
           </div>
           <button @click="openGateway = false" class="text-slate-500 hover:text-white transition-colors">
             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M6 18L18 6M6 6l12 12" /></svg>
           </button>
        </div>

        <div class="p-8 overflow-y-auto custom-scrollbar no-scrollbar flex-1">
            
            <!-- Success Message -->
            <div x-show="success" class="text-center py-16 animate-fade-in px-8">
                <div class="w-20 h-20 border-2 border-gold-500 rounded-full flex items-center justify-center mx-auto mb-8 bg-gold-500/10">
                    <svg class="w-10 h-10 text-gold-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <h3 class="text-3xl font-serif text-white mb-4">Profile Secured</h3>
                <div class="w-16 h-1 bg-gold-500 mx-auto mb-8"></div>
                <p class="text-slate-300 mb-8 leading-relaxed text-lg font-light">
                    Your investment profile has been encrypted and deposited into our secure private server. <br/>
                    A senior partner will be in touch via <span x-text="formData.preferredCommunication"></span> within 24 hours.
                </p>
                <button @click="openGateway = false" class="border border-gold-500 text-gold-400 hover:bg-gold-500 hover:text-navy-900 px-6 py-2 transition-colors uppercase tracking-widest text-sm font-bold">Return to Portal</button>
            </div>

            <!-- Form Steps -->
            <div x-show="!success" class="h-full flex flex-col justify-between">
                
                <!-- Progress Indicator -->
                <div class="flex mb-10 items-center justify-center space-x-4">
                     <div class="w-3 h-3 rounded-full" :class="step >= 1 ? 'bg-gold-500' : 'bg-slate-700'"></div>
                     <div class="w-12 h-px" :class="step >= 2 ? 'bg-gold-500' : 'bg-slate-700'"></div>
                     <div class="w-3 h-3 rounded-full" :class="step >= 2 ? 'bg-gold-500' : 'bg-slate-700'"></div>
                     <div class="w-12 h-px" :class="step >= 3 ? 'bg-gold-500' : 'bg-slate-700'"></div>
                     <div class="w-3 h-3 rounded-full" :class="step >= 3 ? 'bg-gold-500' : 'bg-slate-700'"></div>
                </div>

                <!-- Step 1: Qualification -->
                <div x-show="step === 1" class="space-y-6 animate-fade-in">
                    <h3 class="text-2xl font-serif text-white mb-6 text-center">Investor Identity</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs text-slate-400 uppercase tracking-wider mb-2">Full Name</label>
                            <input x-model="formData.fullName" class="w-full bg-navy-800 border-b border-slate-600 focus:border-gold-500 p-3 text-white outline-none transition-colors" placeholder="Legal Name" />
                        </div>
                        <div>
                            <label class="block text-xs text-slate-400 uppercase tracking-wider mb-2">Professional Title</label>
                            <input x-model="formData.professionalTitle" class="w-full bg-navy-800 border-b border-slate-600 focus:border-gold-500 p-3 text-white outline-none transition-colors" placeholder="e.g. Director, CEO" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs text-slate-400 uppercase tracking-wider mb-2">Primary Source of Income</label>
                        <select x-model="formData.sourceOfIncome" class="w-full bg-navy-800 border-b border-slate-600 focus:border-gold-500 p-3 text-white outline-none">
                            <option value="Business Operations">Business Operations</option>
                            <option value="Professional Salary">Professional Salary</option>
                            <option value="Investment Portfolio">Investment Portfolio</option>
                            <option value="Capital Gains">Capital Gains</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-slate-400 uppercase tracking-wider mb-4">Investor Status (Self-Declared) *</label>
                        <div class="space-y-3">
                            <template x-for="status in ['Sophisticated Investor (Significant Deal Experience)', 'High-Net-Worth Individual (Assets > RM 1M)', 'Institutional / Corporate Representative']">
                                <label class="flex items-center space-x-3 cursor-pointer group">
                                    <input type="checkbox" :value="status" @change="toggleCheckbox('investorStatus', status)" class="form-checkbox h-5 w-5 text-gold-500 rounded border-slate-600 bg-navy-800 focus:ring-gold-500" />
                                    <span class="text-slate-300 group-hover:text-white transition-colors text-sm" x-text="status"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Strategy -->
                <div x-show="step === 2" class="space-y-6 animate-fade-in">
                    <h3 class="text-2xl font-serif text-white mb-6 text-center">Capital & Interest</h3>

                    <div>
                        <label class="block text-xs text-slate-400 uppercase tracking-wider mb-2">Liquidity Range for Investment</label>
                        <select x-model="formData.liquidityRange" class="w-full bg-navy-800 border-b border-slate-600 focus:border-gold-500 p-3 text-white outline-none">
                            <option value="RM 100k - RM 500k">RM 100k - RM 500k</option>
                            <option value="RM 500k - RM 1M">RM 500k - RM 1M</option>
                            <option value="RM 1M - RM 5M">RM 1M - RM 5M</option>
                            <option value="RM 5M+">RM 5M+</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-slate-400 uppercase tracking-wider mb-4">Primary Areas of Interest</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <template x-for="area in ['Sabah Land Development', 'Commercial Real Estate', 'Private Lending', 'Joint Venture Opportunities']">
                                <label class="flex items-center space-x-3 cursor-pointer group p-3 border border-slate-700 hover:border-gold-500/50 transition-all">
                                    <input type="checkbox" :value="area" @change="toggleCheckbox('areasOfInterest', area)" class="form-checkbox h-4 w-4 text-gold-500 rounded border-slate-600 bg-navy-800 focus:ring-gold-500" />
                                    <span class="text-slate-300 group-hover:text-white text-sm" x-text="area"></span>
                                </label>
                            </template>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs text-slate-400 uppercase tracking-wider mb-2">Investment Horizon</label>
                        <select x-model="formData.investmentHorizon" class="w-full bg-navy-800 border-b border-slate-600 focus:border-gold-500 p-3 text-white outline-none">
                            <option value="Short Term (<2 years)">Short Term (&lt;2 years)</option>
                            <option value="Medium Term (2-5 years)">Medium Term (2-5 years)</option>
                            <option value="Long Term (5+ years)">Long Term (5+ years)</option>
                        </select>
                    </div>
                </div>

                <!-- Step 3: Logistics -->
                <div x-show="step === 3" class="space-y-6 animate-fade-in">
                    <h3 class="text-2xl font-serif text-white mb-6 text-center">Access & Logistics</h3>

                    <div>
                        <label class="block text-xs text-slate-400 uppercase tracking-wider mb-2">Email Address</label>
                        <input x-model="formData.email" type="email" class="w-full bg-navy-800 border-b border-slate-600 focus:border-gold-500 p-3 text-white outline-none transition-colors" placeholder="me@shawnradam.com" />
                    </div>

                    <div>
                        <label class="block text-xs text-slate-400 uppercase tracking-wider mb-2">Preferred Communication</label>
                        <select x-model="formData.preferredCommunication" class="w-full bg-navy-800 border-b border-slate-600 focus:border-gold-500 p-3 text-white outline-none">
                            <option value="WhatsApp">WhatsApp</option>
                            <option value="Direct Call">Direct Call</option>
                            <option value="Secure Email">Secure Email</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-slate-400 uppercase tracking-wider mb-2">Timezone / Location</label>
                        <input x-model="formData.timezoneLocation" class="w-full bg-navy-800 border-b border-slate-600 focus:border-gold-500 p-3 text-white outline-none transition-colors" placeholder="e.g. Kuala Lumpur (GMT+8)" />
                    </div>

                    <div>
                        <label class="block text-xs text-slate-400 uppercase tracking-wider mb-2">Current Goal for this Capital</label>
                        <textarea x-model="formData.currentChallenges" rows="3" class="w-full bg-navy-800 border border-slate-600 focus:border-gold-500 p-3 text-white outline-none transition-colors" placeholder="e.g. Wealth preservation, portfolio diversification in Sabah..."></textarea>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="mt-8 flex justify-between items-center">
                    <button type="button" x-show="step > 1" @click="step--" class="text-slate-400 text-sm hover:text-white uppercase tracking-wider">Back</button>
                    <div x-show="step === 1"></div> <!-- Spacer -->

                    <button type="button" x-show="step < 3" @click="step++" class="border border-gold-500 text-gold-400 hover:bg-gold-500 hover:text-navy-900 px-6 py-2 uppercase tracking-widest text-sm font-bold transition-colors">Next Step</button>
                    
                    <button type="button" x-show="step === 3" @click="submit" :disabled="loading" class="bg-gold-500 text-navy-900 hover:bg-gold-400 px-6 py-2 uppercase tracking-widest text-sm font-bold transition-colors disabled:opacity-50">
                        <span x-text="loading ? 'Securing Data...' : 'Submit Profile'"></span>
                    </button>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-center space-x-2 text-[0.65rem] uppercase tracking-widest text-slate-500 mt-6 border-t border-slate-800 pt-4">
                    <svg class="w-3 h-3 text-gold-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                    <span>Strictly Confidential | Secure Private Office Encryption</span>
                </div>

            </div>
        </div>
    </div>
</div>
