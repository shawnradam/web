/**
 * Koperasi Personal Loan Calculator
 * Vanilla interactive client-side component.
 */

(function() {
  window.KoperasiCalculator = {
    defaults: {
      theme: 'cosmic', 
      calculationMode: 'flat', 
      defaultPreset: 'coopbank_pertama',
      defaultLoan: 30000,
      defaultTenure: 5,
      presets: [
        {
          id: 'coopbank_pertama',
          name: 'Co-opbank Pertama',
          interestRate: 3.85,
          minLoan: 1000,
          maxLoan: 200000,
          minTenure: 1,
          maxTenure: 10,
          processingFeePercent: 4.5,
          insurancePercent: 2.5,
          membershipFee: 50,
          advancePaymentMonths: 2
        },
        {
          id: 'koperasi_tentera',
          name: 'Koperasi Tentera',
          interestRate: 4.25,
          minLoan: 2000,
          maxLoan: 150000,
          minTenure: 1,
          maxTenure: 10,
          processingFeePercent: 3.0,
          insurancePercent: 2.0,
          membershipFee: 30,
          advancePaymentMonths: 1
        },
        {
          id: 'yir',
          name: 'Yayasan Ihsan Rakyat',
          interestRate: 6.50,
          minLoan: 1000,
          maxLoan: 150000,
          minTenure: 1,
          maxTenure: 10,
          processingFeePercent: 5.0,
          insurancePercent: 3.5,
          membershipFee: 0,
          advancePaymentMonths: 2
        },
        {
          id: 'custom',
          name: 'Custom Koperasi',
          interestRate: 4.50,
          minLoan: 1000,
          maxLoan: 250000,
          minTenure: 1,
          maxTenure: 10,
          processingFeePercent: 5.0,
          insurancePercent: 3.0,
          membershipFee: 50,
          advancePaymentMonths: 2
        }
      ]
    },

    init: function(elementId, userOptions) {
      const container = document.getElementById(elementId);
      if (!container) {
        console.error(`Target element #${elementId} not found.`);
        return;
      }

      const options = this.extend({}, this.defaults, userOptions);
      container.kopCalcInstance = new CalculatorInstance(container, options);
      return container.kopCalcInstance;
    },

    extend: function(out) {
      out = out || {};
      for (let i = 1; i < arguments.length; i++) {
        if (!arguments[i]) continue;
        for (let key in arguments[i]) {
          if (arguments[i].hasOwnProperty(key)) {
            if (typeof arguments[i][key] === 'object' && !Array.isArray(arguments[i][key]) && arguments[i][key] !== null) {
              out[key] = this.extend(out[key] || {}, arguments[i][key]);
            } else if (Array.isArray(arguments[i][key])) {
              out[key] = arguments[i][key].map(item => {
                if (typeof item === 'object' && item !== null) {
                  return this.extend({}, item);
                }
                return item;
              });
            } else {
              out[key] = arguments[i][key];
            }
          }
        }
      }
      return out;
    }
  };

  function CalculatorInstance(container, options) {
    this.container = container;
    this.options = options;
    if (options.theme === 'light') {
      options.theme = 'cosmic';
    }
    this.lang = localStorage.getItem('calcLang') || 'en';
    this.currentPresetId = options.defaultPreset;
    this.currentPreset = options.presets.find(p => p.id === this.currentPresetId) || options.presets[0];
    
    this.loanAmount = options.defaultLoan;
    this.tenureYears = options.defaultTenure;
    this.interestRate = this.currentPreset.interestRate;
    this.calculationMode = options.calculationMode;
    
    this.scheduleVisible = false;
    this.deductionsVisible = false;
    this.hasPrinted = false;
    this.userInteracted = false;
    this.whatsappTimeout = null;
    
    this.customSettings = {
      processingFeePercent: this.currentPreset.processingFeePercent,
      insurancePercent: this.currentPreset.insurancePercent,
      membershipFee: this.currentPreset.membershipFee,
      advancePaymentMonths: this.currentPreset.advancePaymentMonths
    };

    this.render();
    this.bindEvents();
    this.calculate(true);
    this.userInteracted = false;
    if (this.whatsappTimeout) clearTimeout(this.whatsappTimeout);
  }

  CalculatorInstance.prototype.t = function(key) {
    const translations = {
      en: {
        calc_title: 'Koperasi Loan Calculator',
        loan_amt: 'Loan Amount (Gross)',
        tenure: 'Financing Tenure',
        flat_rate: 'Flat Interest Rate',
        interest_type: 'Interest Balance Type',
        flat_rate_btn: 'Flat Rate',
        reducing_rate_btn: 'Reducing EIR',
        help_text: 'Flat rate calculations have constant interest costs. Reducing EIR calculates interest on monthly outstanding balances.',
        monthly_inst: 'Monthly Installment',
        period_est: 'Estimated for {years} years',
        net_payout_lbl: 'Net Payout',
        actual_net_payout: 'Actual Net Payout',
        total_interest: 'Total Interest Cost',
        total_repay: 'Total Repayable',
        total_deductions: 'Total Deductions',
        deductions_breakdown: 'Upfront Deductions Breakdown',
        btn_amortization: 'Amortization',
        btn_print: 'Print PDF',
        wa_text: 'Tanya Pembiayaan Peribadi Koperasi / Bank di WhatsApp',
        wa_btn: 'Contact Shawn Radam',
        schedule_title: 'Amortization Repayment Table',
        schedule_notes_flat: 'Flat rate model breakdown',
        schedule_notes_red: 'Reducing balance EIR breakdown',
        tbl_year: 'Year',
        tbl_principal: 'Principal Paid',
        tbl_interest: 'Interest Paid',
        tbl_total: 'Total Payments',
        tbl_balance: 'Remaining Balance',
        years_suffix: 'Years',
        year_suffix: 'Year',
        tweak_panel: 'Preset Settings Tweak Panel'
      },
      bm: {
        calc_title: 'Kalkulator Pinjaman Koperasi',
        loan_amt: 'Jumlah Pinjaman (Kasar)',
        tenure: 'Tempoh Pembiayaan',
        flat_rate: 'Kadar Faedah Rata',
        interest_type: 'Jenis Baki Faedah',
        flat_rate_btn: 'Kadar Rata',
        reducing_rate_btn: 'EIR Menurun',
        help_text: 'Pengiraan kadar rata mempunyai kos faedah tetap. Kadar EIR menurun mengira faedah atas baki bulanan yang belum dijelaskan.',
        monthly_inst: 'Ansuran Bulanan',
        period_est: 'Anggaran untuk {years} tahun',
        net_payout_lbl: 'Bayaran Bersih',
        actual_net_payout: 'Bayaran Bersih Sebenar',
        total_interest: 'Jumlah Kos Faedah',
        total_repay: 'Jumlah Bayaran Balik',
        total_deductions: 'Jumlah Potongan',
        deductions_breakdown: 'Butiran Potongan Awal',
        btn_amortization: 'Jadual Ansuran',
        btn_print: 'Cetak PDF',
        wa_text: 'Tanya Pembiayaan Peribadi Koperasi / Bank di WhatsApp',
        wa_btn: 'Hubungi Shawn Radam',
        schedule_title: 'Jadual Pelunasan Pembayaran Balik',
        schedule_notes_flat: 'Butiran model kadar rata',
        schedule_notes_red: 'Butiran model baki berkurangan (EIR)',
        tbl_year: 'Tahun',
        tbl_principal: 'Prinsipal Dibayar',
        tbl_interest: 'Faedah Dibayar',
        tbl_total: 'Jumlah Pembayaran',
        tbl_balance: 'Baki Pinjaman',
        years_suffix: 'Tahun',
        year_suffix: 'Tahun',
        tweak_panel: 'Panel Pelarasan Tetapan Preset'
      }
    };
    return (translations[this.lang] || translations['en'])[key] || key;
  };

  CalculatorInstance.prototype.render = function() {
    const presetTabs = this.options.presets.map(p => 
      `<button class="kop-calc-preset-tab ${p.id === this.currentPresetId ? 'active' : ''}" data-preset-id="${p.id}">${p.name}</button>`
    ).join('');

    const html = `
      <div class="kop-calc-wrapper theme-${this.options.theme}" id="kop-calc-wrapper-${this.container.id}">
        <div class="kop-calc-container">
          
          <div class="kop-calc-header" style="display: flex; justify-content: space-between; align-items: center; width: 100%; flex-wrap: wrap; gap: 10px;">
            <h3 class="kop-calc-title" style="margin: 0;">${this.t('calc_title')}</h3>
            <div style="display: flex; align-items: center; gap: 12px;">
              <!-- Language Select Toggle -->
              <div style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 15px; padding: 2px; display: inline-flex;">
                <button class="kop-lang-btn-en" style="border: none; border-radius: 12px; padding: 4px 12px; font-size: 10px; font-weight: 700; cursor: pointer; background: ${this.lang === 'en' ? '#d4af37' : 'transparent'}; color: ${this.lang === 'en' ? '#0a192f' : '#94a3b8'}">EN</button>
                <button class="kop-lang-btn-bm" style="border: none; border-radius: 12px; padding: 4px 12px; font-size: 10px; font-weight: 700; cursor: pointer; background: ${this.lang === 'bm' ? '#d4af37' : 'transparent'}; color: ${this.lang === 'bm' ? '#0a192f' : '#94a3b8'}">BM</button>
              </div>
              <div class="kop-calc-theme-toggle">
                <button class="kop-calc-theme-btn ${this.options.theme === 'dark' ? 'active' : ''}" data-theme="dark">Dark</button>
                <button class="kop-calc-theme-btn ${this.options.theme === 'cosmic' ? 'active' : ''}" data-theme="cosmic">Cosmic</button>
              </div>
            </div>
          </div>

          <div class="kop-calc-presets">
            ${presetTabs}
          </div>

          <div class="kop-calc-grid">
            <div class="kop-calc-inputs-column">
              
              <div class="kop-calc-card">
                <div class="kop-calc-field-header">
                  <span class="kop-calc-label">${this.t('loan_amt')}</span>
                  <div class="kop-calc-value-container">
                    <span class="kop-calc-input-prefix">RM</span>
                    <input type="number" class="kop-calc-num-input" id="kop-loan-input" value="${this.loanAmount}">
                  </div>
                </div>
                <div class="kop-calc-slider-wrapper">
                  <input type="range" class="kop-calc-range" id="kop-loan-slider" 
                    min="${this.currentPreset.minLoan}" 
                    max="${this.currentPreset.maxLoan}" 
                    step="1000" 
                    value="${this.loanAmount}">
                </div>
                <div class="kop-calc-slider-labels">
                  <span id="kop-loan-min-lbl">RM ${this.formatNumber(this.currentPreset.minLoan)}</span>
                  <span id="kop-loan-max-lbl">RM ${this.formatNumber(this.currentPreset.maxLoan)}</span>
                </div>
              </div>

              <div class="kop-calc-card">
                <div class="kop-calc-field-header">
                  <span class="kop-calc-label">${this.t('tenure')}</span>
                  <div class="kop-calc-value-container">
                    <input type="number" class="kop-calc-num-input" id="kop-tenure-input" value="${this.tenureYears}">
                    <span class="kop-calc-input-suffix" id="kop-tenure-suffix-lbl">${this.tenureYears === 1 ? this.t('year_suffix') : this.t('years_suffix')}</span>
                  </div>
                </div>
                <div class="kop-calc-slider-wrapper">
                  <input type="range" class="kop-calc-range" id="kop-tenure-slider" 
                    min="${this.currentPreset.minTenure}" 
                    max="${this.currentPreset.maxTenure}" 
                    step="1" 
                    value="${this.tenureYears}">
                </div>
                <div class="kop-calc-slider-labels">
                  <span id="kop-tenure-min-lbl">${this.currentPreset.minTenure} ${this.currentPreset.minTenure === 1 ? this.t('year_suffix') : this.t('years_suffix')}</span>
                  <span id="kop-tenure-max-lbl">${this.currentPreset.maxTenure} ${this.t('years_suffix')}</span>
                </div>
              </div>

              <div class="kop-calc-card">
                <div class="kop-calc-field-header">
                  <span class="kop-calc-label" id="kop-rate-lbl">${this.t('flat_rate')}</span>
                  <div class="kop-calc-value-container">
                    <input type="number" class="kop-calc-num-input" id="kop-rate-input" value="${this.interestRate}" step="0.01">
                    <span class="kop-calc-input-suffix">% p.a.</span>
                  </div>
                </div>
                <div class="kop-calc-slider-wrapper">
                  <input type="range" class="kop-calc-range" id="kop-rate-slider" 
                    min="1.00" 
                    max="15.00" 
                    step="0.05" 
                    value="${this.interestRate}">
                </div>
                <div class="kop-calc-slider-labels">
                  <span>1.0%</span>
                  <span>15.0%</span>
                </div>
              </div>

              <div class="kop-calc-card">
                <div class="kop-calc-row">
                  <span class="kop-calc-label">${this.t('interest_type')}</span>
                  <div class="kop-calc-theme-toggle" id="kop-calc-mode-toggle">
                    <button class="kop-calc-theme-btn ${this.calculationMode === 'flat' ? 'active' : ''}" data-mode="flat">${this.t('flat_rate_btn')}</button>
                    <button class="kop-calc-theme-btn ${this.calculationMode === 'reducing' ? 'active' : ''}" data-mode="reducing">${this.t('reducing_rate_btn')}</button>
                  </div>
                </div>
                <div class="kop-calc-help-text">${this.t('help_text')}</div>
              </div>

            </div>

            <div class="kop-calc-results-panel">
              
              <div class="kop-calc-repayment-display">
                <div class="kop-calc-repayment-label">${this.t('monthly_inst')}</div>
                <div class="kop-calc-repayment-value" id="kop-monthly-payment">RM 0.00</div>
                <div class="kop-calc-repayment-period" id="kop-summary-period-lbl">
                  ${this.lang === 'en' 
                    ? `Estimated for <span id="kop-summary-tenure">${this.tenureYears}</span> ${this.tenureYears === 1 ? 'year' : 'years'}`
                    : `Anggaran untuk <span id="kop-summary-tenure">${this.tenureYears}</span> tahun`
                  }
                </div>
              </div>

              <div class="kop-calc-visualizer">
                <div class="kop-calc-chart-container">
                  <svg class="kop-calc-donut" viewBox="0 0 120 120" width="100%" height="100%">
                    <circle class="kop-calc-donut-bg" cx="60" cy="60" r="48"></circle>
                    <circle class="kop-calc-donut-segment" id="chart-segment-payout" cx="60" cy="60" r="48" stroke="var(--kop-chart-payout)"></circle>
                    <circle class="kop-calc-donut-segment" id="chart-segment-deductions" cx="60" cy="60" r="48" stroke="var(--kop-chart-deductions)"></circle>
                    <circle class="kop-calc-donut-segment" id="chart-segment-interest" cx="60" cy="60" r="48" stroke="var(--kop-chart-interest)"></circle>
                  </svg>
                  <div class="kop-calc-chart-center">
                    <div class="kop-calc-chart-center-lbl">${this.t('net_payout_lbl')}</div>
                    <div class="kop-calc-chart-center-val" id="chart-center-percent">0%</div>
                  </div>
                </div>
              </div>

              <div class="kop-calc-metrics-grid">
                <div class="kop-calc-metric-box">
                  <div class="kop-calc-metric-lbl">${this.t('actual_net_payout')}</div>
                  <div class="kop-calc-metric-val" id="kop-net-payout" style="color: var(--kop-chart-payout);">RM 0.00</div>
                </div>
                <div class="kop-calc-metric-box">
                  <div class="kop-calc-metric-lbl">${this.t('total_interest')}</div>
                  <div class="kop-calc-metric-val" id="kop-total-interest" style="color: var(--kop-chart-interest);">RM 0.00</div>
                </div>
                <div class="kop-calc-metric-box">
                  <div class="kop-calc-metric-lbl">${this.t('total_repay')}</div>
                  <div class="kop-calc-metric-val" id="kop-total-repayment">RM 0.00</div>
                </div>
                <div class="kop-calc-metric-box">
                  <div class="kop-calc-metric-lbl">${this.t('total_deductions')}</div>
                  <div class="kop-calc-metric-val" id="kop-total-deductions" style="color: var(--kop-chart-deductions);">RM 0.00</div>
                </div>
              </div>

              <button class="kop-calc-accordion-trigger" id="kop-deductions-trigger">
                <span>${this.t('deductions_breakdown')}</span>
                <svg class="kop-calc-accordion-arrow" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z"/></svg>
              </button>
              
              <div class="kop-calc-accordion-content" id="kop-deductions-content"></div>

              <div style="display: flex; gap: 8px;">
                <button class="kop-calc-action-btn" id="kop-toggle-schedule-btn" style="flex-grow: 1; padding: 12px 10px; font-size: 13px;">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px; display: inline-block; vertical-align: middle;"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                  ${this.t('btn_amortization')}
                </button>
                <button class="kop-calc-action-btn" id="kop-print-pdf-btn" style="flex-grow: 1; padding: 12px 10px; font-size: 13px; background: #3b82f6; color: #fff; border-color: #3b82f6;">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px; display: inline-block; vertical-align: middle;"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
                  ${this.t('btn_print')}
                </button>
                <button class="kop-calc-action-btn" id="kop-toggle-config-btn" style="width: auto; padding: 12px 12px;">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
                </button>
              </div>

              <!-- WhatsApp Action Banner -->
              <div class="kop-calc-whatsapp-box" id="kop-whatsapp-container-${this.container.id}">
                <p class="kop-calc-whatsapp-text">${this.t('wa_text')}</p>
                <a href="https://wa.me/60128338639?text=Saya%20nak%20tanya%20tentang%20Pembiayaan%20Peribadi%20Koperasi%20%2F%20Bank." target="_blank" class="kop-calc-whatsapp-btn">
                  <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24" style="display: inline-block; vertical-align: middle; margin-right: 6px;"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-8.68-2.031-9.67-.272-.099-.47-.149-.669-.149-.198 0-.42.001-.643.001-.223 0-.585.085-.891.41-.307.327-1.178 1.151-1.178 2.809 0 1.657 1.205 3.257 1.374 3.482.169.224 2.373 3.623 5.748 5.082.802.347 1.428.555 1.917.71.8.254 1.528.218 2.106.132.646-.096 1.388-.568 1.585-1.116.197-.548.197-1.016.138-1.115z" /></svg>
                  ${this.t('wa_btn')}
                </a>
              </div>

              <!-- Print only Footer -->
              <div class="kop-calc-print-footer">
                <hr style="border: 0; border-top: 1px solid rgba(212, 175, 55, 0.3); margin-top: 25px; margin-bottom: 12px;">
                <div style="display: flex; justify-content: space-between; font-size: 11px; color: #94a3b8; font-family: sans-serif;">
                  <div><strong>AGENT NAME:</strong> Shawn Radam</div>
                  <div><strong>PHONE NUMBER:</strong> 012 8338 639 / 011 1633 9399 (Whatsapp)</div>
                </div>
              </div>

            </div>
          </div>

          <div class="kop-calc-schedule-section" id="kop-schedule-section">
            <div class="kop-calc-table-header">
              <h4 class="kop-calc-table-title" id="kop-schedule-title">${this.t('schedule_title')}</h4>
              <span style="font-size: 12px; color: var(--kop-text-secondary);" id="kop-schedule-notes">${this.calculationMode === 'flat' ? this.t('schedule_notes_flat') : this.t('schedule_notes_red')}</span>
            </div>
            <div class="kop-calc-scroll-container">
              <table class="kop-calc-table">
                <thead>
                  <tr>
                    <th>${this.t('tbl_year')}</th>
                    <th>${this.t('tbl_principal')}</th>
                    <th>${this.t('tbl_interest')}</th>
                    <th>${this.t('tbl_total')}</th>
                    <th>${this.t('tbl_balance')}</th>
                  </tr>
                </thead>
                <tbody id="kop-schedule-tbody"></tbody>
              </table>
            </div>
          </div>

          <div class="kop-calc-config-section" id="kop-config-section">
            <div class="kop-calc-config-title-bar">
              <h4 class="kop-calc-title" style="font-size: 16px; border: none; padding: 0; margin: 0;">${this.t('tweak_panel')}</h4>
            </div>
            <div class="kop-calc-config-fields">
              <div class="kop-calc-config-field">
                <label>Admin Fee (%)</label>
                <input type="number" step="0.1" class="kop-calc-config-input" id="cfg-processing-fee" value="${this.customSettings.processingFeePercent}">
              </div>
              <div class="kop-calc-config-field">
                <label>Takaful (%)</label>
                <input type="number" step="0.1" class="kop-calc-config-input" id="cfg-insurance-fee" value="${this.customSettings.insurancePercent}">
              </div>
              <div class="kop-calc-config-field">
                <label>Membership Entrance (RM)</label>
                <input type="number" class="kop-calc-config-input" id="cfg-member-fee" value="${this.customSettings.membershipFee}">
              </div>
              <div class="kop-calc-config-field">
                <label>Advance Payments (Mths)</label>
                <input type="number" class="kop-calc-config-input" id="cfg-advance-months" value="${this.customSettings.advancePaymentMonths}">
              </div>
              
              <div class="kop-calc-config-code-box">
                <label>Calculator Embed Code</label>
                <textarea class="kop-calc-config-textarea" id="kop-config-output" readonly></textarea>
                <div style="display: flex; justify-content: flex-end; margin-top: 8px; position: relative; gap: 10px; align-items: center;">
                  <div class="kop-calc-success-alert" id="kop-config-success-alert" style="display: none; background: rgba(16, 185, 129, 0.1); border: 1px solid var(--kop-success); color: var(--kop-success); padding: 6px 12px; border-radius: 6px; font-size: 11px;">
                    📋 Copied!
                  </div>
                  <button class="kop-calc-config-btn" id="kop-copy-config-btn" type="button">Copy Embed Code</button>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    `;

    this.container.innerHTML = html;
  };

  CalculatorInstance.prototype.bindEvents = function() {
    const wrap = document.getElementById(`kop-calc-wrapper-${this.container.id}`);
    
    // Language buttons click handlers
    const langBtnEn = wrap.querySelector('.kop-lang-btn-en');
    const langBtnBm = wrap.querySelector('.kop-lang-btn-bm');
    if (langBtnEn && langBtnBm) {
      langBtnEn.onclick = () => {
        if (this.lang !== 'en') {
          this.lang = 'en';
          localStorage.setItem('calcLang', 'en');
          window.dispatchEvent(new CustomEvent('calc-lang-changed', { detail: { lang: 'en' } }));
          this.render();
          this.bindEvents();
          this.calculate(true);
        }
      };
      langBtnBm.onclick = () => {
        if (this.lang !== 'bm') {
          this.lang = 'bm';
          localStorage.setItem('calcLang', 'bm');
          window.dispatchEvent(new CustomEvent('calc-lang-changed', { detail: { lang: 'bm' } }));
          this.render();
          this.bindEvents();
          this.calculate(true);
        }
      };
    }

    if (this._onLangChange) {
      window.removeEventListener('calc-lang-changed', this._onLangChange);
    }
    this._onLangChange = (e) => {
      if (e.detail && e.detail.lang && e.detail.lang !== this.lang) {
        this.lang = e.detail.lang;
        this.render();
        this.bindEvents();
        this.calculate(true);
      }
    };
    window.addEventListener('calc-lang-changed', this._onLangChange);

    this.loanSlider = wrap.querySelector('#kop-loan-slider');
    this.loanInput = wrap.querySelector('#kop-loan-input');
    this.tenureSlider = wrap.querySelector('#kop-tenure-slider');
    this.tenureInput = wrap.querySelector('#kop-tenure-input');
    this.rateSlider = wrap.querySelector('#kop-rate-slider');
    this.rateInput = wrap.querySelector('#kop-rate-input');

    this.updateSliderLimits();

    const presetTabs = wrap.querySelectorAll('.kop-calc-preset-tab');
    presetTabs.forEach(tab => {
      tab.addEventListener('click', (e) => {
        presetTabs.forEach(t => t.classList.remove('active'));
        e.target.classList.add('active');
        this.selectPreset(e.target.getAttribute('data-preset-id'));
      });
    });

    const themeButtons = wrap.querySelectorAll('.kop-calc-theme-toggle button[data-theme]');
    themeButtons.forEach(btn => {
      btn.addEventListener('click', (e) => {
        themeButtons.forEach(b => b.classList.remove('active'));
        e.target.classList.add('active');
        const theme = e.target.getAttribute('data-theme');
        
        wrap.classList.remove('theme-light', 'theme-dark', 'theme-cosmic');
        wrap.classList.add(`theme-${theme}`);
        this.options.theme = theme;
        this.updateConfigOutput();
      });
    });

    const modeButtons = wrap.querySelectorAll('#kop-calc-mode-toggle button');
    modeButtons.forEach(btn => {
      btn.addEventListener('click', (e) => {
        modeButtons.forEach(b => b.classList.remove('active'));
        e.target.classList.add('active');
        this.calculationMode = e.target.getAttribute('data-mode');
        
        const rateLabel = wrap.querySelector('#kop-rate-lbl');
        if (this.calculationMode === 'flat') {
          rateLabel.textContent = "Flat Interest Rate";
          wrap.querySelector('#kop-schedule-notes').textContent = "Flat rate model breakdown";
        } else {
          rateLabel.textContent = "Effective Interest Rate (EIR)";
          wrap.querySelector('#kop-schedule-notes').textContent = "Reducing balance EIR breakdown";
        }
        this.calculate(true); 
      });
    });

    const accordionBtn = wrap.querySelector('#kop-deductions-trigger');
    const accordionContent = wrap.querySelector('#kop-deductions-content');
    accordionBtn.addEventListener('click', () => {
      const isActive = accordionBtn.classList.toggle('active');
      accordionContent.style.display = isActive ? 'block' : 'none';
      this.deductionsVisible = isActive;
      if (isActive) {
        this.updateDeductionsUI();
      }
    });

    const scheduleBtn = wrap.querySelector('#kop-toggle-schedule-btn');
    const scheduleSection = wrap.querySelector('#kop-schedule-section');
    scheduleBtn.addEventListener('click', () => {
      const visible = scheduleSection.style.display === 'block';
      scheduleSection.style.display = visible ? 'none' : 'block';
      this.scheduleVisible = !visible;
      if (this.scheduleVisible) {
        this.updateAmortizationSchedule();
        scheduleSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }
    });

    const configBtn = wrap.querySelector('#kop-toggle-config-btn');
    const configSection = wrap.querySelector('#kop-config-section');
    configBtn.addEventListener('click', () => {
      const visible = configSection.style.display === 'block';
      configSection.style.display = visible ? 'none' : 'block';
      if (!visible) {
        configSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }
    });

    const printBtn = wrap.querySelector('#kop-print-pdf-btn');
    printBtn.addEventListener('click', () => {
      this.hasPrinted = true;
      const waContainer = document.getElementById(`kop-whatsapp-container-${this.container.id}`);
      if (waContainer) {
        waContainer.style.display = 'none';
      }
      window.print();
    });

    const cfgProcessing = wrap.querySelector('#cfg-processing-fee');
    const cfgInsurance = wrap.querySelector('#cfg-insurance-fee');
    const cfgMember = wrap.querySelector('#cfg-member-fee');
    const cfgAdvance = wrap.querySelector('#cfg-advance-months');

    const updateConfigSetting = () => {
      this.customSettings.processingFeePercent = parseFloat(cfgProcessing.value) || 0;
      this.customSettings.insurancePercent = parseFloat(cfgInsurance.value) || 0;
      this.customSettings.membershipFee = parseFloat(cfgMember.value) || 0;
      this.customSettings.advancePaymentMonths = parseInt(cfgAdvance.value) || 0;
      
      if (this.currentPresetId !== 'custom') {
        const customTab = wrap.querySelector('.kop-calc-preset-tab[data-preset-id="custom"]');
        if (customTab) {
          presetTabs.forEach(t => t.classList.remove('active'));
          customTab.classList.add('active');
          this.currentPresetId = 'custom';
          this.currentPreset = this.options.presets.find(p => p.id === 'custom');
        }
      }
      this.calculate(true);
    };

    cfgProcessing.addEventListener('change', updateConfigSetting);
    cfgInsurance.addEventListener('change', updateConfigSetting);
    cfgMember.addEventListener('change', updateConfigSetting);
    cfgAdvance.addEventListener('change', updateConfigSetting);

    const copyBtn = wrap.querySelector('#kop-copy-config-btn');
    const successAlert = wrap.querySelector('#kop-config-success-alert');
    copyBtn.addEventListener('click', () => {
      const textarea = wrap.querySelector('#kop-config-output');
      textarea.select();
      document.execCommand('copy');
      
      successAlert.style.display = 'flex';
      setTimeout(() => {
        successAlert.style.display = 'none';
      }, 3000);
    });
  };

  CalculatorInstance.prototype.updateSliderLimits = function() {
    const wrap = document.getElementById(`kop-calc-wrapper-${this.container.id}`);
    
    const loanSlider = wrap.querySelector('#kop-loan-slider');
    const loanInput = wrap.querySelector('#kop-loan-input');
    const tenureSlider = wrap.querySelector('#kop-tenure-slider');
    const tenureInput = wrap.querySelector('#kop-tenure-input');
    const rateSlider = wrap.querySelector('#kop-rate-slider');
    const rateInput = wrap.querySelector('#kop-rate-input');

    loanSlider.min = this.currentPreset.minLoan;
    loanSlider.max = this.currentPreset.maxLoan;
    wrap.querySelector('#kop-loan-min-lbl').textContent = `RM ${this.formatNumber(this.currentPreset.minLoan)}`;
    wrap.querySelector('#kop-loan-max-lbl').textContent = `RM ${this.formatNumber(this.currentPreset.maxLoan)}`;
    
    if (this.loanAmount < this.currentPreset.minLoan) this.loanAmount = this.currentPreset.minLoan;
    if (this.loanAmount > this.currentPreset.maxLoan) this.loanAmount = this.currentPreset.maxLoan;
    loanSlider.value = this.loanAmount;
    loanInput.value = this.loanAmount;

    tenureSlider.min = this.currentPreset.minTenure;
    tenureSlider.max = this.currentPreset.maxTenure;
    wrap.querySelector('#kop-tenure-min-lbl').textContent = `${this.currentPreset.minTenure} ${this.currentPreset.minTenure === 1 ? 'Year' : 'Years'}`;
    wrap.querySelector('#kop-tenure-max-lbl').textContent = `${this.currentPreset.maxTenure} Years`;
    
    if (this.tenureYears < this.currentPreset.minTenure) this.tenureYears = this.currentPreset.minTenure;
    if (this.tenureYears > this.currentPreset.maxTenure) this.tenureYears = this.currentPreset.maxTenure;
    tenureSlider.value = this.tenureYears;
    tenureInput.value = this.tenureYears;

    rateSlider.value = this.interestRate;
    rateInput.value = this.interestRate.toFixed(2);

    loanSlider.oninput = (e) => {
      this.loanAmount = parseInt(e.target.value) || 0;
      loanInput.value = this.loanAmount;
      this.calculate(false); 
    };
    loanSlider.onchange = (e) => {
      this.loanAmount = parseInt(e.target.value) || 0;
      loanInput.value = this.loanAmount;
      this.calculate(true); 
    };
    loanInput.onchange = (e) => {
      let val = parseInt(e.target.value) || 0;
      val = Math.max(this.currentPreset.minLoan, Math.min(val, this.currentPreset.maxLoan));
      this.loanAmount = val;
      loanInput.value = val;
      loanSlider.value = val;
      this.calculate(true);
    };

    tenureSlider.oninput = (e) => {
      this.tenureYears = parseInt(e.target.value) || 0;
      tenureInput.value = this.tenureYears;
      this.calculate(false);
    };
    tenureSlider.onchange = (e) => {
      this.tenureYears = parseInt(e.target.value) || 0;
      tenureInput.value = this.tenureYears;
      this.calculate(true);
    };
    tenureInput.onchange = (e) => {
      let val = parseInt(e.target.value) || 0;
      val = Math.max(this.currentPreset.minTenure, Math.min(val, this.currentPreset.maxTenure));
      this.tenureYears = val;
      tenureInput.value = val;
      tenureSlider.value = val;
      this.calculate(true);
    };

    rateSlider.oninput = (e) => {
      this.interestRate = parseFloat(e.target.value) || 0;
      rateInput.value = this.interestRate.toFixed(2);
      this.calculate(false);
    };
    rateSlider.onchange = (e) => {
      this.interestRate = parseFloat(e.target.value) || 0;
      rateInput.value = this.interestRate.toFixed(2);
      this.calculate(true);
    };
    rateInput.onchange = (e) => {
      let val = parseFloat(e.target.value) || 0;
      val = Math.max(1.0, Math.min(val, 15.0));
      this.interestRate = val;
      rateInput.value = val.toFixed(2);
      rateSlider.value = val;
      this.calculate(true);
    };
  };

  CalculatorInstance.prototype.selectPreset = function(presetId) {
    const wrap = document.getElementById(`kop-calc-wrapper-${this.container.id}`);
    this.currentPresetId = presetId;
    this.currentPreset = this.options.presets.find(p => p.id === presetId);
    this.interestRate = this.currentPreset.interestRate;
    
    if (presetId !== 'custom') {
      this.customSettings.processingFeePercent = this.currentPreset.processingFeePercent;
      this.customSettings.insurancePercent = this.currentPreset.insurancePercent;
      this.customSettings.membershipFee = this.currentPreset.membershipFee;
      this.customSettings.advancePaymentMonths = this.currentPreset.advancePaymentMonths;
      
      wrap.querySelector('#cfg-processing-fee').value = this.customSettings.processingFeePercent;
      wrap.querySelector('#cfg-insurance-fee').value = this.customSettings.insurancePercent;
      wrap.querySelector('#cfg-member-fee').value = this.customSettings.membershipFee;
      wrap.querySelector('#cfg-advance-months').value = this.customSettings.advancePaymentMonths;
    }

    wrap.querySelector('#kop-rate-slider').value = this.interestRate;
    wrap.querySelector('#kop-rate-input').value = this.interestRate.toFixed(2);

    this.updateSliderLimits();
    this.calculate(true);
  };

  CalculatorInstance.prototype.calculate = function(forceTableUpdate = false) {
    const wrap = document.getElementById(`kop-calc-wrapper-${this.container.id}`);
    
    this.userInteracted = true;
    this.triggerWhatsAppTimer();

    const principal = this.loanAmount;
    const rateAnn = this.interestRate;
    const years = this.tenureYears;
    const months = years * 12;

    let monthlyPayment = 0;
    let totalInterest = 0;
    let totalRepayable = 0;

    if (this.calculationMode === 'flat') {
      // Flat rate formula: Interest = Principal * Rate * Years
      totalInterest = principal * (rateAnn / 100) * years;
      totalRepayable = principal + totalInterest;
      monthlyPayment = totalRepayable / months;
    } else {
      // Reducing balance EIR formula: Payment = P * (r * (1 + r)^n) / ((1 + r)^n - 1)
      const r = (rateAnn / 100) / 12;
      monthlyPayment = principal * (r * Math.pow(1 + r, months)) / (Math.pow(1 + r, months) - 1);
      totalRepayable = monthlyPayment * months;
      totalInterest = totalRepayable - principal;
    }

    const processingFee = principal * (this.customSettings.processingFeePercent / 100);
    const insuranceFee = principal * (this.customSettings.insurancePercent / 100);
    const membershipFee = this.customSettings.membershipFee;
    const advanceInstallments = monthlyPayment * this.customSettings.advancePaymentMonths;
    
    const totalDeductions = processingFee + insuranceFee + membershipFee + advanceInstallments;
    const netPayout = Math.max(0, principal - totalDeductions);

    this.monthlyPayment = monthlyPayment;
    this.totalInterest = totalInterest;
    this.totalRepayable = totalRepayable;
    this.totalDeductions = totalDeductions;
    this.netPayout = netPayout;
    
    this.processingFee = processingFee;
    this.insuranceFee = insuranceFee;
    this.membershipFee = membershipFee;
    this.advanceInstallments = advanceInstallments;

    wrap.querySelector('#kop-monthly-payment').textContent = `RM ${this.formatMoney(monthlyPayment)}`;
    
    const summaryPeriod = wrap.querySelector('#kop-summary-period-lbl');
    if (summaryPeriod) {
      summaryPeriod.innerHTML = this.lang === 'en' 
        ? `Estimated for <span id="kop-summary-tenure">${years}</span> ${years === 1 ? 'year' : 'years'}`
        : `Anggaran untuk <span id="kop-summary-tenure">${years}</span> tahun`;
    } else {
      const summaryTenure = wrap.querySelector('#kop-summary-tenure');
      if (summaryTenure) summaryTenure.textContent = years;
    }
    
    const tenureSuffix = wrap.querySelector('#kop-tenure-suffix-lbl');
    if (tenureSuffix) {
      tenureSuffix.textContent = years === 1 ? this.t('year_suffix') : this.t('years_suffix');
    }

    wrap.querySelector('#kop-net-payout').textContent = `RM ${this.formatMoney(netPayout)}`;
    wrap.querySelector('#kop-total-interest').textContent = `RM ${this.formatMoney(totalInterest)}`;
    wrap.querySelector('#kop-total-repayment').textContent = `RM ${this.formatMoney(totalRepayable)}`;
    wrap.querySelector('#kop-total-deductions').textContent = `RM ${this.formatMoney(totalDeductions)}`;

    if (this.deductionsVisible) {
      this.updateDeductionsUI();
    }

    this.updateDonutChart(netPayout, totalDeductions, totalInterest, totalRepayable);

    if (this.scheduleVisible && forceTableUpdate) {
      this.updateAmortizationSchedule();
    }

    this.updateConfigOutput();
  };

  CalculatorInstance.prototype.updateDeductionsUI = function() {
    const wrap = document.getElementById(`kop-calc-wrapper-${this.container.id}`);
    const accordionContent = wrap.querySelector('#kop-deductions-content');
    
    const labelAdmin = this.lang === 'en' ? 'Admin / Processing Fee' : 'Yuran Pentadbiran / Pemprosesan';
    const labelInsurance = this.lang === 'en' ? 'Takaful Insurance Premium' : 'Premium Insurans Takaful';
    const labelMember = this.lang === 'en' ? 'Koperasi Membership Entrance Fee' : 'Yuran Keahlian Masuk Koperasi';
    
    let labelAdvance = '';
    if (this.lang === 'en') {
      labelAdvance = `Advance Installment (${this.customSettings.advancePaymentMonths} ${this.customSettings.advancePaymentMonths === 1 ? 'Month' : 'Months'})`;
    } else {
      labelAdvance = `Bayaran Ansuran Pendahuluan (${this.customSettings.advancePaymentMonths} Bulan)`;
    }
    
    const labelTotalDeductions = this.lang === 'en' ? 'Total Upfront Deductions' : 'Jumlah Potongan Awal';
    const labelNetPayout = this.lang === 'en' ? 'Net Cash Payout (Received Amount)' : 'Bayaran Tunai Bersih (Jumlah Diterima)';

    accordionContent.innerHTML = `
      <div class="kop-calc-deduction-item">
        <span>${labelAdmin} (${this.customSettings.processingFeePercent}%)</span>
        <span>RM ${this.formatMoney(this.processingFee)}</span>
      </div>
      <div class="kop-calc-deduction-item">
        <span>${labelInsurance} (${this.customSettings.insurancePercent}%)</span>
        <span>RM ${this.formatMoney(this.insuranceFee)}</span>
      </div>
      <div class="kop-calc-deduction-item">
        <span>${labelMember}</span>
        <span>RM ${this.formatMoney(this.membershipFee)}</span>
      </div>
      <div class="kop-calc-deduction-item">
        <span>${labelAdvance}</span>
        <span>RM ${this.formatMoney(this.advanceInstallments)}</span>
      </div>
      <div class="kop-calc-deduction-item" style="margin-top: 10px; font-weight: 700; border-top: 1px dashed var(--kop-border); padding-top: 10px;">
        <span>${labelTotalDeductions}</span>
        <span style="color: var(--kop-chart-deductions);">RM ${this.formatMoney(this.totalDeductions)}</span>
      </div>
      <div class="kop-calc-deduction-item" style="font-weight: 700;">
        <span>${labelNetPayout}</span>
        <span style="color: var(--kop-chart-payout);">RM ${this.formatMoney(this.netPayout)}</span>
      </div>
    `;
  };

  CalculatorInstance.prototype.updateDonutChart = function(payout, deductions, interest, totalRepayable) {
    const wrap = document.getElementById(`kop-calc-wrapper-${this.container.id}`);
    
    const payoutShare = payout / totalRepayable;
    const deductionsShare = deductions / totalRepayable;
    const interestShare = interest / totalRepayable;

    // Radius = 48, Circumference = 2 * PI * r = 301.59
    const circumference = 301.6;

    const payoutSize = circumference * payoutShare;
    const deductionsSize = circumference * deductionsShare;
    const interestSize = circumference * interestShare;

    const payoutSeg = wrap.querySelector('#chart-segment-payout');
    const deductionsSeg = wrap.querySelector('#chart-segment-deductions');
    const interestSeg = wrap.querySelector('#chart-segment-interest');

    payoutSeg.setAttribute('stroke-dasharray', `${payoutSize} ${circumference - payoutSize}`);
    payoutSeg.setAttribute('stroke-dashoffset', 0);

    deductionsSeg.setAttribute('stroke-dasharray', `${deductionsSize} ${circumference - deductionsSize}`);
    deductionsSeg.setAttribute('stroke-dashoffset', -payoutSize);

    interestSeg.setAttribute('stroke-dasharray', `${interestSize} ${circumference - interestSize}`);
    interestSeg.setAttribute('stroke-dashoffset', -(payoutSize + deductionsSize));

    const payoutOfLoanPercent = Math.round((payout / this.loanAmount) * 100);
    wrap.querySelector('#chart-center-percent').textContent = `${payoutOfLoanPercent}%`;
  };

  CalculatorInstance.prototype.updateAmortizationSchedule = function() {
    const tbody = document.getElementById('kop-schedule-tbody');
    if (!tbody) return;
    tbody.innerHTML = '';

    const principal = this.loanAmount;
    const rate = this.interestRate;
    const years = this.tenureYears;
    const monthlyPayment = this.monthlyPayment;
    const totalInterest = this.totalInterest;

    let remainingBalance = principal;
    const annualInstallment = monthlyPayment * 12;

    const labelYear = this.t('tbl_year');

    if (this.calculationMode === 'flat') {
      const annualPrincipalPaid = principal / years;
      const annualInterestPaid = totalInterest / years;

      for (let year = 1; year <= years; year++) {
        remainingBalance -= annualPrincipalPaid;
        if (year === years || remainingBalance < 0.01) {
          remainingBalance = 0;
        }

        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td><strong>${labelYear} ${year}</strong></td>
          <td>RM ${this.formatMoney(annualPrincipalPaid)}</td>
          <td>RM ${this.formatMoney(annualInterestPaid)}</td>
          <td>RM ${this.formatMoney(annualInstallment)}</td>
          <td>RM ${this.formatMoney(remainingBalance)}</td>
        `;
        tbody.appendChild(tr);
      }
    } else {
      const r = (rate / 100) / 12;
      
      for (let year = 1; year <= years; year++) {
        let annualPrincipalPaid = 0;
        let annualInterestPaid = 0;

        for (let m = 1; m <= 12; m++) {
          const interestPaid = remainingBalance * r;
          const principalPaid = monthlyPayment - interestPaid;
          remainingBalance -= principalPaid;
          
          annualPrincipalPaid += principalPaid;
          annualInterestPaid += interestPaid;
        }

        if (year === years || remainingBalance < 0.01) {
          remainingBalance = 0;
        }

        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td><strong>${labelYear} ${year}</strong></td>
          <td>RM ${this.formatMoney(annualPrincipalPaid)}</td>
          <td>RM ${this.formatMoney(annualInterestPaid)}</td>
          <td>RM ${this.formatMoney(annualInstallment)}</td>
          <td>RM ${this.formatMoney(remainingBalance)}</td>
        `;
        tbody.appendChild(tr);
      }
    }
  };

  CalculatorInstance.prototype.updateConfigOutput = function() {
    const textarea = document.getElementById('kop-config-output');
    if (!textarea) return;

    const config = {
      theme: this.options.theme,
      defaultLoan: this.loanAmount,
      defaultTenure: this.tenureYears,
      calculationMode: this.calculationMode,
      defaultPreset: this.currentPresetId,
      presets: this.options.presets.map(p => {
        if (p.id === 'custom') {
          return {
            ...p,
            interestRate: this.interestRate,
            processingFeePercent: this.customSettings.processingFeePercent,
            insurancePercent: this.customSettings.insurancePercent,
            membershipFee: this.customSettings.membershipFee,
            advancePaymentMonths: this.customSettings.advancePaymentMonths
          };
        }
        return p;
      })
    };

    textarea.value = `<!-- Load CSS inside <head> -->
<link rel="stylesheet" href="plugins/koperasi-loan-calculator/koperasi-calculator.css">

<!-- Target Element -->
<div id="${this.container.id}"></div>

<!-- Load JS & Initialize -->
<script src="plugins/koperasi-loan-calculator/koperasi-calculator.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    KoperasiCalculator.init('${this.container.id}', ${JSON.stringify(config, null, 2)});
  });
</script>`;
  };

  CalculatorInstance.prototype.formatNumber = function(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  };

  CalculatorInstance.prototype.formatMoney = function(num) {
    return num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  };

  CalculatorInstance.prototype.triggerWhatsAppTimer = function() {
    if (this.whatsappTimeout) clearTimeout(this.whatsappTimeout);
    this.whatsappTimeout = setTimeout(() => {
      if (!this.hasPrinted && this.userInteracted) {
        const waContainer = document.getElementById(`kop-whatsapp-container-${this.container.id}`);
        if (waContainer) {
          waContainer.style.display = 'block';
        }
      }
    }, 3000);
  };

})();
