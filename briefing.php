<?php
// briefing.php — The Advisor Briefing Intake Form
include 'includes/header.php';
include 'includes/navigation.php';
?>

<div class="pt-24 min-h-screen bg-navy-950">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="text-center mb-16">
            <p class="text-gold-500 font-medium tracking-[0.3em] uppercase mb-4">Strategic Consultancy Intake</p>
            <h1 class="text-4xl md:text-5xl font-serif text-white mb-4">The Advisor Briefing</h1>
            <div class="w-20 h-0.5 bg-gold-500 mx-auto mb-6"></div>
            <p class="text-slate-400 max-w-xl mx-auto">Initiate a formal inquiry regarding property acquisition, Sabah land valuation, or financial loans strategy.</p>
        </div>

        <div class="bg-navy-800 p-8 border border-slate-700 shadow-2xl max-w-3xl mx-auto relative rounded-xl">
            <div class="absolute top-0 left-0 w-full h-1 bg-gold-500 rounded-t-xl"></div>

            <div class="mb-10 text-center">
                <h3 class="font-serif text-2xl text-white mb-6">Direct Access Channel</h3>

                <!-- WhatsApp Button -->
                <a href="https://wa.me/60128338639" target="_blank"
                    class="inline-flex items-center justify-center bg-[#25D366] text-white px-8 py-4 rounded-sm uppercase tracking-widest text-sm font-bold hover:bg-[#128C7E] transition-all duration-300 shadow-lg hover:shadow-[#25D366]/20 group w-full md:w-auto">
                    <svg class="w-6 h-6 mr-3" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-8.68-2.031-9.67-.272-.099-.47-.149-.669-.149-.198 0-.42.001-.643.001-.223 0-.585.085-.891.41-.307.327-1.178 1.151-1.178 2.809 0 1.657 1.205 3.257 1.374 3.482.169.224 2.373 3.623 5.748 5.082.802.347 1.428.555 1.917.71.8.254 1.528.218 2.106.132.646-.096 1.388-.568 1.585-1.116.197-.548.197-1.016.138-1.115z" />
                    </svg>
                    WhatsApp Personal Office
                </a>

                <div class="flex items-center justify-center space-x-4 mt-8">
                    <div class="h-px bg-slate-700 w-12"></div>
                    <span class="text-slate-500 text-xs uppercase tracking-widest">Or Submit Inquiry</span>
                    <div class="h-px bg-slate-700 w-12"></div>
                </div>
            </div>

            <!-- Embedded Intake Form -->
            <?php include 'includes/briefing-form.php'; ?>
        </div>
    </div>
</div>

<?php include 'includes/feedback_popup.php'; ?>
<?php include 'includes/footer.php'; ?>
