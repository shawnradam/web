<?php include 'includes/header.php'; ?>
<?php include 'includes/navigation.php'; ?>


<div class="pt-24 pb-20 px-6 min-h-screen bg-navy-900"
  x-data="{ openLoan: false, openGateway: false, selectedIdentity: null }">

  <?php include 'includes/smart-loan-form.php'; ?>
  <?php include 'includes/investment-gateway-form.php'; ?>

  <div class="max-w-6xl mx-auto">
    <h1 class="text-4xl font-serif text-white mb-4 text-center">Strategic Finance</h1>
    <p class="text-slate-400 text-center max-w-2xl mx-auto mb-16">
      Bridging the gap between institutional lending and private capital deployment.
    </p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-12">

      <!-- Public/Gov Loans -->
      <div class="bg-[#0D2137] p-10 border border-slate-700 hover:border-slate-500 transition-colors">
        <div class="w-12 h-12 bg-slate-700 flex items-center justify-center mb-6">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
          </svg>
        </div>
        <h2 class="text-2xl font-serif text-white mb-4">Lending Solutions</h2>
        <ul class="space-y-4 text-slate-300 mb-8 list-disc pl-5">
          <li>LPPSA Government Loan Facilitation</li>
          <li>SME Business Financing Strategy</li>
          <li>Mortgage Restructuring & Refinancing</li>
        </ul>
        <p class="text-sm text-slate-400 mb-8">
          We navigate the bureaucratic complexities of Malaysian banking and government sectors to secure optimal terms.
        </p>
        <button @click="selectedIdentity = null; openLoan = true"
          class="border border-white text-white px-6 py-2 uppercase tracking-widest text-sm font-bold hover:bg-white hover:text-navy-900 transition-colors">Check
          Eligibility</button>
      </div>

      <!-- HNWI Investment -->
      <div
        class="bg-forest-900/10 p-10 border border-gold-500/30 relative overflow-hidden hover:border-gold-500/60 transition-colors shadow-lg shadow-black/20">
        <div class="absolute top-0 right-0 p-4 opacity-10">
          <svg class="w-32 h-32 text-gold-500" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
          </svg>
        </div>

        <div class="w-12 h-12 bg-gold-500 flex items-center justify-center mb-6">
          <svg class="w-6 h-6 text-navy-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
          </svg>
        </div>
        <h2 class="text-2xl font-serif text-white mb-4">Investment Gateway</h2>
        <div class="flex items-center mb-4 space-x-2">
          <span class="w-2 h-2 rounded-full bg-gold-500 animate-pulse"></span>
          <p class="text-gold-500 text-xs uppercase tracking-widest font-bold">Private & Confidential</p>
        </div>

        <ul class="space-y-4 text-slate-300 mb-8 list-disc pl-5">
          <li>Private Credit & Bridging Finance</li>
          <li>Land Banking Co-Investment</li>
          <li>High-Yield Fixed Returns</li>
        </ul>
        <p class="text-sm text-slate-400 mb-8 font-light italic">
          "Private Investment Access: High-income professionals and HNWIs may request a private briefing for capital
          growth opportunities in the Sabah property market."
        </p>
        <button @click="openGateway = true"
          class="w-full bg-gold-500 text-navy-900 hover:bg-gold-400 border-transparent px-6 py-4 uppercase tracking-widest text-sm font-bold transition-colors shadow-lg shadow-gold-500/20">
          <span class="flex items-center justify-center">
            Request Strategic Briefing <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
          </span>
        </button>
      </div>

    </div>
    


  </div>
</div>

<?php include 'includes/footer.php'; ?>