<?php include 'includes/header.php'; ?>
<?php include 'includes/navigation.php'; ?>

<div class="min-h-screen bg-navy-900 pt-24 pb-20 px-6">
    <div class="max-w-5xl mx-auto">
        
        <!-- Header Section -->
        <header class="text-center mb-16 animate-fade-in-up">
            <h1 class="text-4xl md:text-5xl font-serif font-bold text-white mb-4">Contact The Private Office</h1>
            <div class="w-24 h-1 bg-forest-900 mx-auto"></div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-12">      

            <!-- Left Column: The Gatekeeper Sidebar -->
            <aside class="lg:col-span-2 space-y-8 animate-fade-in delay-100 bg-navy-800/50 border border-slate-800 rounded-sm p-8 h-fit">

                <!-- Office HQ -->
                <div class="pb-6 border-b border-slate-800">   
                    <label class="block text-xs uppercase tracking-widest text-slate-400 mb-2">OFFICE HEADQUARTERS</label> 
                    <p class="text-white font-sans text-lg">Kota Kinabalu, Sabah,<br/>Malaysia, Borneo</p>
                </div>

                <!-- Availability -->
                <div class="py-6 border-b border-slate-800">   
                    <label class="block text-xs uppercase tracking-widest text-slate-400 mb-2">AVAILABILITY</label>        
                    <p class="text-white font-sans text-sm mb-4">Monday — Friday<br/>09:00 — 17:00 (GMT+8)</p>

                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-forest-900/30 text-forest-500 border border-forest-900">   
                        By Appointment Only
                    </span>
                </div>

                <!-- Direct Channel -->
                <div class="pt-6">
                    <label class="block text-xs uppercase tracking-widest text-slate-400 mb-2">ACTIVE MANDATES</label>     
                    <p class="text-slate-400 text-xs leading-relaxed mb-4 font-sans">
                        For urgent matters relating to existing files or active mandates only.
                    </p>
                    <a href="mailto:office@shawnradam.com" class="text-white hover:text-forest-500 transition-colors text-sm font-medium border-b border-transparent hover:border-forest-500">
                        office@shawnradam.com
                    </a>
                </div>

            </aside>

            <!-- Right Column: The Briefing Interface -->
            <div class="lg:col-span-3">
                <div class="bg-navy-800 border border-slate-700 p-8 rounded-sm shadow-2xl">
                    <h3 class="font-serif text-2xl text-white mb-6">Secure Briefing</h3>
                    <p class="text-slate-400 text-sm mb-8 font-sans">
                        To ensure the highest level of service for our existing portfolio, new inquiries are processed via this secure form.
                    </p>
                    <?php include 'includes/briefing-form.php'; ?>     
                </div>
            </div>

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
