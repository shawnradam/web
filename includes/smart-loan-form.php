<!-- Smart Loan Form Modal -->
<div x-data="{ 
    step: 1,
    identity: null,
    loading: false,
    success: false,
    formData: {
        ministry: '', empStatus: 'Permanent', yearsService: '', basicSalary: '', allowance: '', lppsaLoans: 'None', purpose: 'Purchase Land',
        company: '', grossSalary: '', epf: 'Yes', empDuration: '2-5 Years', commitments: '',
        sourceWealth: 'Business Ownership', annualIncome: 'RM 200k-500k', investInterest: 'Sabah Land', liquidity: 'Immediate',
        name: '', email: '', whatsapp: '', privacyAgreed: false
    },
    
    init() {
        this.$watch('openLoan', value => {
            if (value) {
                this.step = this.identity ? 2 : 1;
                if (!this.identity) this.formData.identity = null;
            }
        });
        this.$watch('selectedIdentity', value => {
            if(value) {
                this.identity = value;
                this.formData.identity = value;
                this.step = 2;
            }
        });
    },

    selectIdentity(type) {
        this.identity = type;
        this.formData.identity = type;
        this.step = 2;
    },

    submit() {
        if (!this.formData.privacyAgreed) return alert('You must acknowledge the Data Privacy Policy.');
        
        this.loading = true;
        
        // Simulate API call
        setTimeout(() => {
            this.loading = false;
            this.success = true;
            console.log('Loan Form Data:', JSON.parse(JSON.stringify(this.formData)));
        }, 1500);
    }
}"
x-show="openLoan" 
x-cloak
class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-navy-900/90 backdrop-blur-sm">

    <div class="bg-navy-800 border border-slate-700 w-full max-w-lg shadow-2xl rounded-lg overflow-hidden flex flex-col max-h-[90vh]" @click.away="openLoan = false">
        
        <!-- Header -->
        <div class="flex justify-between items-center p-4 border-b border-slate-700 bg-navy-900">
           <h2 class="text-white font-serif tracking-wide">SMART ELIGIBILITY</h2>
           <button @click="openLoan = false" class="text-slate-500 hover:text-white">
             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
           </button>
        </div>

        <div class="p-6 overflow-y-auto custom-scrollbar no-scrollbar">
            
            <!-- Success Message -->
            <div x-show="success" class="text-center py-10 animate-fade-in">
                <div class="w-16 h-16 bg-forest-900 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                </div>
                <h3 class="text-2xl font-serif text-white mb-2">Assessment Received</h3>
                <p class="text-slate-400 mb-6">
                    Your profile has been securely archived in our Private Office database.
                    <br/>Our team will review your eligibility and contact you via WhatsApp shortly.
                </p>
                <button @click="openLoan = false" class="border border-white text-white px-4 py-2 hover:bg-white hover:text-navy-900 transition-colors uppercase text-sm tracking-widest">Return to Portal</button>
            </div>

            <!-- Form Steps -->
            <div x-show="!success">
                
                <!-- Progress Bar -->
                <div class="flex mb-6 gap-1">
                    <div class="h-1 flex-1 rounded" :class="step >= 1 ? 'bg-forest-500' : 'bg-slate-700'"></div>
                    <div class="h-1 flex-1 rounded" :class="step >= 2 ? 'bg-forest-500' : 'bg-slate-700'"></div>
                    <div class="h-1 flex-1 rounded" :class="step >= 3 ? 'bg-forest-500' : 'bg-slate-700'"></div>
                </div>

                <!-- Step 1: Select Profile -->
                <div x-show="step === 1" class="space-y-4">
                    <h3 class="text-xl font-serif text-white text-center mb-6">Select Your Profile</h3>
                    
                    <div @click="selectIdentity('GOV')" class="p-4 border border-slate-600 hover:border-forest-500 hover:bg-forest-900/20 cursor-pointer transition-all rounded">
                        <div class="font-bold text-white">Government Servant</div>
                        <div class="text-xs text-slate-400">LPPSA Eligibility Focus</div>
                    </div>
                    
                    <div @click="selectIdentity('PRIVATE')" class="p-4 border border-slate-600 hover:border-forest-500 hover:bg-forest-900/20 cursor-pointer transition-all rounded">
                        <div class="font-bold text-white">Private Sector Employee</div>
                        <div class="text-xs text-slate-400">Bank & Private Loan Focus</div>
                    </div>
                    
                    <div @click="selectIdentity('BUSINESS')" class="p-4 border border-slate-600 hover:border-gold-500 hover:bg-gold-500/10 cursor-pointer transition-all rounded">
                        <div class="font-bold text-white">Business Owner / Investor</div>
                        <div class="text-xs text-slate-400">Capital & Wealth Management</div>
                    </div>
                </div>

                <!-- Step 2: Details -->
                <div x-show="step === 2" class="space-y-4 animate-fade-in">
                    <h3 class="text-xl font-serif text-white mb-4" x-text="identity === 'GOV' ? 'LPPSA Assessment Details' : (identity === 'PRIVATE' ? 'Credit Profile' : 'Investor Intake')"></h3>

                    <!-- GOV -->
                    <template x-if="identity === 'GOV'">
                        <div class="space-y-4">
                            <input x-model="formData.ministry" placeholder="Ministry / Department" class="w-full bg-navy-900 border border-slate-600 p-3 text-white rounded focus:border-forest-500 outline-none" />
                            <div class="grid grid-cols-2 gap-4">
                                <select x-model="formData.empStatus" class="bg-navy-900 border border-slate-600 p-3 text-white rounded focus:border-forest-500 outline-none">
                                    <option value="Permanent">Permanent</option>
                                    <option value="Contract">Contract</option>
                                </select>
                                <input x-model="formData.yearsService" type="number" placeholder="Years of Service" class="bg-navy-900 border border-slate-600 p-3 text-white rounded focus:border-forest-500 outline-none" />
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <input x-model="formData.basicSalary" placeholder="Basic Salary (RM)" class="bg-navy-900 border border-slate-600 p-3 text-white rounded focus:border-forest-500 outline-none" />
                                <input x-model="formData.allowance" placeholder="Fixed Allowance (RM)" class="bg-navy-900 border border-slate-600 p-3 text-white rounded focus:border-forest-500 outline-none" />
                            </div>
                            <select x-model="formData.lppsaLoans" class="w-full bg-navy-900 border border-slate-600 p-3 text-white rounded focus:border-forest-500 outline-none">
                                <option value="None">No Existing LPPSA Loan</option>
                                <option value="First">One Existing Loan</option>
                                <option value="Second">Two Existing Loans</option>
                            </select>
                        </div>
                    </template>

                    <!-- PRIVATE -->
                    <template x-if="identity === 'PRIVATE'">
                        <div class="space-y-4">
                            <input x-model="formData.company" placeholder="Company Name" class="w-full bg-navy-900 border border-slate-600 p-3 text-white rounded focus:border-forest-500 outline-none" />
                            <div class="grid grid-cols-2 gap-4">
                                <input x-model="formData.grossSalary" placeholder="Monthly Gross (RM)" class="bg-navy-900 border border-slate-600 p-3 text-white rounded focus:border-forest-500 outline-none" />
                                <select x-model="formData.epf" class="bg-navy-900 border border-slate-600 p-3 text-white rounded focus:border-forest-500 outline-none">
                                    <option value="Yes">EPF Contributor</option>
                                    <option value="No">No EPF</option>
                                </select>
                            </div>
                            <input x-model="formData.commitments" placeholder="Est. Monthly Bank Commitments (RM)" class="w-full bg-navy-900 border border-slate-600 p-3 text-white rounded focus:border-forest-500 outline-none" />
                        </div>
                    </template>

                    <!-- BUSINESS -->
                    <template x-if="identity === 'BUSINESS'">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs text-slate-400 uppercase tracking-wider">Source of Wealth</label>
                                <select x-model="formData.sourceWealth" class="w-full bg-navy-900 border border-slate-600 p-3 text-white rounded focus:border-forest-500 outline-none">
                                    <option value="Business Ownership">Business Ownership</option>
                                    <option value="Investments">Investments / Dividends</option>
                                    <option value="Inheritance">Inheritance / Trust</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-slate-400 uppercase tracking-wider">Annual Income Bracket</label>
                                <select x-model="formData.annualIncome" class="w-full bg-navy-900 border border-slate-600 p-3 text-white rounded focus:border-forest-500 outline-none">
                                    <option value="RM 200k-500k">RM 200k - 500k</option>
                                    <option value="RM 500k-1M">RM 500k - 1 Million</option>
                                    <option value="RM 1M+">Above RM 1 Million</option>
                                </select>
                            </div>
                        </div>
                    </template>

                    <div class="flex justify-between pt-4">
                        <button type="button" @click="step = 1" class="text-slate-400 text-sm hover:text-white">Change Profile</button>
                        <button type="button" @click="step = 3" class="bg-white text-navy-900 px-6 py-2 uppercase tracking-widest text-xs font-bold hover:bg-slate-200">Next: Contact Info</button>
                    </div>
                </div>

                <!-- Step 3: Final Validation -->
                <div x-show="step === 3" class="space-y-4 animate-fade-in">
                    <h3 class="text-xl font-serif text-white mb-4">Final Validation</h3>
                    <input x-model="formData.name" placeholder="Full Legal Name" class="w-full bg-navy-900 border border-slate-600 p-3 text-white rounded focus:border-forest-500 outline-none" />
                    <input x-model="formData.email" type="email" placeholder="Email Address" class="w-full bg-navy-900 border border-slate-600 p-3 text-white rounded focus:border-forest-500 outline-none" />
                    <input x-model="formData.whatsapp" placeholder="WhatsApp Number (e.g. +60123456789)" class="w-full bg-navy-900 border border-slate-600 p-3 text-white rounded focus:border-forest-500 outline-none" />
                    
                    <div class="pt-2">
                        <label class="flex items-start gap-3 cursor-pointer group">
                            <input type="checkbox" x-model="formData.privacyAgreed" class="mt-1 w-4 h-4 border-slate-600 rounded bg-navy-900 text-forest-500 focus:ring-forest-500" />
                            <span class="text-xs text-slate-400 group-hover:text-slate-300 leading-relaxed">
                                I acknowledge Shawn Radam's <span class="underline">Data Privacy Policy</span>.
                            </span>
                        </label>
                    </div>

                    <div class="flex justify-between pt-6">
                        <button type="button" @click="step = 2" class="text-slate-400 text-sm hover:text-white">Back</button>
                        <button type="button" @click="submit" :disabled="loading" class="bg-forest-900 text-white px-6 py-2 uppercase tracking-widest text-xs font-bold hover:bg-forest-800 disabled:opacity-50">
                            <span x-text="loading ? 'Processing...' : 'Submit Assessment'"></span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
