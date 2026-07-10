/**
 * Property & Land Calculator
 * Vanilla interactive client-side component.
 */

(function() {
  window.PropertyCalculator = {
    defaults: {
      theme: 'cosmic',
      defaultRegion: 'kl',
      defaultKlTab: 'stamp',
      defaultSabahTab: 'sabahstamp',
      lang: localStorage.getItem('calcLang') || 'en',
      presets: [
        {
          id: 'kl_stamp_duty',
          name: 'KL Stamp Duty & Legal Fees Default',
          category: 'stamp_duty',
          region: 'kl',
          interestRateDefault: 4.50,
          minPrice: 0,
          maxPrice: 0,
          minTenure: 5,
          maxTenure: 35,
          downPaymentPct: 10,
          premiumRate: 0,
          notes: ''
        },
        {
          id: 'kl_mortgage',
          name: 'KL Mortgage Loan Default',
          category: 'mortgage',
          region: 'kl',
          interestRateDefault: 4.50,
          minPrice: 10000,
          maxPrice: 2000000,
          minTenure: 5,
          maxTenure: 35,
          downPaymentPct: 10,
          premiumRate: 0,
          notes: ''
        },
        {
          id: 'kl_rental_yield',
          name: 'KL Rental Yield Default',
          category: 'rental_yield',
          region: 'kl',
          interestRateDefault: 0,
          minPrice: 0,
          maxPrice: 0,
          minTenure: 0,
          maxTenure: 0,
          downPaymentPct: 0,
          premiumRate: 0,
          notes: ''
        },
        {
          id: 'sabah_stamp_duty',
          name: 'Sabah Stamp Duty Default',
          category: 'stamp_duty',
          region: 'sabah',
          interestRateDefault: 4.50,
          minPrice: 0,
          maxPrice: 0,
          minTenure: 5,
          maxTenure: 35,
          downPaymentPct: 10,
          premiumRate: 0,
          notes: ''
        },
        {
          id: 'sabah_land_premium',
          name: 'Sabah Land Premium Conversion Default',
          category: 'land_premium',
          region: 'sabah',
          interestRateDefault: 0,
          minPrice: 0,
          maxPrice: 0,
          minTenure: 0,
          maxTenure: 0,
          downPaymentPct: 0,
          premiumRate: 20,
          notes: ''
        },
        {
          id: 'sabah_mortgage',
          name: 'Sabah Mortgage Loan Default',
          category: 'mortgage',
          region: 'sabah',
          interestRateDefault: 4.50,
          minPrice: 10000,
          maxPrice: 2000000,
          minTenure: 5,
          maxTenure: 35,
          downPaymentPct: 10,
          premiumRate: 0,
          notes: ''
        }
      ]
    },

    init: function(elementId, userOptions) {
      const container = document.getElementById(elementId);
      if (!container) {
        console.error('Target element #' + elementId + ' not found.');
        return;
      }

      const options = this.extend({}, this.defaults, userOptions);
      container.propCalcInstance = new PropertyCalcInstance(container, options);
      return container.propCalcInstance;
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

  function PropertyCalcInstance(container, options) {
    this.container = container;
    this.options = options;
    this.lang = options.lang || 'en';
    this.region = options.defaultRegion || 'kl';
    this.klTab = options.defaultKlTab || 'stamp';
    this.sabahTab = options.defaultSabahTab || 'sabahstamp';

    // Form inputs state
    this.inputs = {
      kl: {
        stampPrice: 500000,
        stampLoan: 450000,
        propType: 'residential',
        firstHome: false,
        mortPrice: 500000,
        downPct: 10,
        mortRate: 4.5,
        mortTenure: 30,
        mortCategory: 'housing',
        rentalPrice: 500000,
        rentalIncome: 2000,
        maintPct: 1.0
      },
      sabah: {
        stampVal: 300000,
        stampLoan: 270000,
        landType: 'cl',
        premiumVal: 300000,
        fromTenure: 'nt',
        toTenure: 'freehold',
        premiumArea: 1.5,
        mortPrice: 500000,
        mortLandType: 'cl',
        mortDown: 10,
        mortRate: 4.5,
        mortTenure: 30
      }
    };

    // Override default rates/configs if preset matches
    this.applyPresets();

    this.render();
  }

  PropertyCalcInstance.prototype.applyPresets = function() {
    const presets = this.options.presets || [];
    presets.forEach(preset => {
      if (preset.region === 'kl') {
        if (preset.category === 'stamp_duty') {
          if (preset.downPaymentPct) this.inputs.kl.downPct = preset.downPaymentPct;
        } else if (preset.category === 'mortgage') {
          if (preset.interestRateDefault) this.inputs.kl.mortRate = preset.interestRateDefault;
          if (preset.downPaymentPct) this.inputs.kl.downPct = preset.downPaymentPct;
        }
      } else if (preset.region === 'sabah') {
        if (preset.category === 'mortgage') {
          if (preset.interestRateDefault) this.inputs.sabah.mortRate = preset.interestRateDefault;
          if (preset.downPaymentPct) this.inputs.sabah.mortDown = preset.downPaymentPct;
        }
      }
    });
  };

  PropertyCalcInstance.prototype.t = function(key) {
    const translations = {
      en: {
        kl_tab: 'KL / Semenanjung Malaysia', sabah_tab: 'Sabah',
        kl_stamp_label: 'Stamp Duty', kl_mortgage_label: 'Mortgage', kl_rental_label: 'Rental Yield',
        sabahstamp_label: 'Stamp Duty', premium_label: 'Land Premium', sabahmort_label: 'Mortgage',
        stamp_title: 'Stamp Duty & Legal Fees', stamp_sub: 'Estimate MOT stamp duty, loan agreement stamp duty and legal conveyancing fees for KL/Semenanjung properties.',
        mortgage_title: 'Bank Home Loan Calculator', mortgage_sub: 'Estimate your monthly bank mortgage instalment for residential and commercial properties.',
        rental_title: 'Rental Yield Calculator', rental_sub: 'Calculate gross and net rental yield and investment payback period.',
        sabahstamp_title: 'Sabah Stamp Duty', sabahstamp_sub: 'Estimate MOT and loan stamp duty for Sabah land and property transfers.',
        premium_title: 'Sabah Land Premium / Conversion', premium_sub: 'Estimate the premium payable for converting land tenure in Sabah.',
        sabahmort_title: 'Sabah Property Mortgage', sabahmort_sub: 'Estimate monthly mortgage instalments for Sabah properties.',
        prop_price: 'Property Price', prop_type: 'Property Type', loan_amount: 'Loan Amount',
        first_home: 'First Home (Stamp Duty Exemption)',
        first_home_note: '✅ Stamp duty exemption applied for properties RM500k and below under first home buyer scheme.',
        down_payment_pct: 'Down Payment', interest_rate: 'Interest Rate (p.a.)', tenure_years: 'Loan Tenure',
        years: 'yrs', prop_category: 'Property Category',
        monthly_rental: 'Monthly Rental Income', maintenance_pct: 'Maintenance Cost (% of property value per year)',
        maint_hint: 'Typical range: 1% for apartments, 0.5% for landed',
        land_value: 'Land / Property Value', land_type: 'Land Type',
        land_market_val: 'Land Market Value', current_tenure: 'Current Tenure', target_tenure: 'Target Tenure',
        land_area_acres: 'Land Area (Acres)',
        land_nt: 'Native Title (NT)', land_cl: 'Country Lease (CL)', land_fh: 'Freehold', land_state: 'State Land',
        residential: 'Residential', commercial: 'Commercial',
        cat_housing: 'Housing', cat_apartment: 'Apartment', cat_condo: 'Condominium', cat_flat: 'Flat',
        cat_landed: 'Landed House', cat_office: 'Office / Commercial',
        results: 'Calculation Results',
        mot_stamp: 'MOT Stamp Duty', loan_stamp: 'Loan Agreement Stamp Duty', legal_fee: 'Legal / Conveyancing Fee',
        total_cost: 'Total Cost', down_payment: 'Down Payment', monthly_inst: 'Monthly Instalment',
        total_interest: 'Total Interest', total_repay: 'Total Repayable',
        gross_yield: 'Gross Yield', net_yield: 'Net Yield', annual_maint: 'Annual Maintenance',
        payback_period: 'Payback Period',
        premium_rate: 'Estimated Premium Rate', est_premium: 'Estimated Premium', admin_fee: 'Admin Fee',
        print_pdf: 'Print / Save as PDF', wa_button: 'WhatsApp Shawn Radam',
        disclaimer: 'Estimates only. Actual fees may vary. Consult a licensed solicitor for official figures.',
        sabah_disclaimer: 'Sabah stamp duty uses federal tiers. Actual fees may vary by district. Consult Jabatan Tanah Sabah.',
        premium_note: '⚠️ Sabah land premium rates vary by district and zone. These are approximate estimates only.',
        premium_disclaimer: 'These are approximations only based on general Sabah JTU guidelines. Official assessment required from Jabatan Tanah & Ukur Sabah.'
      },
      bm: {
        kl_tab: 'KL / Semenanjung Malaysia', sabah_tab: 'Sabah',
        kl_stamp_label: 'Duti Setem', kl_mortgage_label: 'Pinjaman Perumahan', kl_rental_label: 'Hasil Sewa',
        sabahstamp_label: 'Duti Setem', premium_label: 'Premium Tanah', sabahmort_label: 'Pinjaman',
        stamp_title: 'Duti Setem & Yuran Guaman', stamp_sub: 'Anggaran duti setem MOT, duti perjanjian pinjaman dan yuran peguamcara untuk hartanah KL/Semenanjung.',
        mortgage_title: 'Kalkulator Pinjaman Perumahan', mortgage_sub: 'Anggaran ansuran bulanan pinjaman bank untuk hartanah kediaman dan komersial.',
        rental_title: 'Kalkulator Hasil Sewa', rental_sub: 'Kira hasil sewa kasar dan bersih serta tempoh pulangan pelaburan.',
        sabahstamp_title: 'Duti Setem Sabah', sabahstamp_sub: 'Anggaran duti setem MOT dan pinjaman untuk pindah milik tanah dan hartanah di Sabah.',
        premium_title: 'Premium / Penukaran Tanah Sabah', premium_sub: 'Anggaran premium yang perlu dibayar untuk penukaran hak milik tanah di Sabah.',
        sabahmort_title: 'Pinjaman Hartanah Sabah', sabahmort_sub: 'Anggaran ansuran bulanan pinjaman untuk hartanah di Sabah.',
        prop_price: 'Harga Hartanah', prop_type: 'Jenis Hartanah', loan_amount: 'Jumlah Pinjaman',
        first_home: 'Rumah Pertama (Pengecualian Duti Setem)',
        first_home_note: '✅ Pengecualian duti setem digunakan untuk hartanah RM500k ke bawah di bawah skim pembeli rumah pertama.',
        down_payment_pct: 'Wang Pendahuluan', interest_rate: 'Kadar Faedah (setahun)', tenure_years: 'Tempoh Pinjaman',
        years: 'thn', prop_category: 'Kategori Hartanah',
        monthly_rental: 'Pendapatan Sewa Bulanan', maintenance_pct: 'Kos Penyelenggaraan (% nilai hartanah setahun)',
        maint_hint: 'Julat biasa: 1% untuk apartmen, 0.5% untuk rumah teres',
        land_value: 'Nilai Tanah / Hartanah', land_type: 'Jenis Tanah',
        land_market_val: 'Nilai Pasaran Tanah', current_tenure: 'Hak Milik Semasa', target_tenure: 'Hak Milik Sasaran',
        land_area_acres: 'Keluasan Tanah (Ekar)',
        land_nt: 'Hak Milik Orang Asal (NT)', land_cl: 'Pajakan Negeri (CL)', land_fh: 'Hak Milik Kekal', land_state: 'Tanah Kerajaan',
        residential: 'Kediaman', commercial: 'Komersial',
        cat_housing: 'Rumah', cat_apartment: 'Apartmen', cat_condo: 'Kondominium', cat_flat: 'Flat',
        cat_landed: 'Rumah Bertanah', cat_office: 'Pejabat / Komersial',
        results: 'Keputusan Pengiraan',
        mot_stamp: 'Duti Setem MOT', loan_stamp: 'Duti Setem Perjanjian Pinjaman', legal_fee: 'Yuran Guaman / Peguamcara',
        total_cost: 'Jumlah Kos', down_payment: 'Wang Pendahuluan', monthly_inst: 'Ansuran Bulanan',
        total_interest: 'Jumlah Faedah', total_repay: 'Jumlah Bayaran Balik',
        gross_yield: 'Hasil Kasar', net_yield: 'Hasil Bersih', annual_maint: 'Penyelenggaraan Tahunan',
        payback_period: 'Tempoh Pulangan Modal',
        premium_rate: 'Kadar Premium Anggaran', est_premium: 'Premium Anggaran', admin_fee: 'Yuran Pentadbiran',
        print_pdf: 'Cetak / Simpan sebagai PDF', wa_button: 'WhatsApp Shawn Radam',
        disclaimer: 'Anggaran sahaja. Yuran sebenar mungkin berbeza. Sila rujuk peguam berlesen untuk angka rasmi.',
        sabah_disclaimer: 'Duti setem Sabah menggunakan kadar persekutuan. Yuran sebenar mungkin berbeza mengikut daerah.',
        premium_note: '⚠️ Kadar premium tanah Sabah berbeza mengikut daerah dan zon. Ini hanyalah anggaran.',
        premium_disclaimer: 'Ini adalah anggaran sahaja berdasarkan garis panduan umum JTU Sabah. Penilaian rasmi diperlukan dari Jabatan Tanah & Ukur Sabah.'
      }
    };
    return (translations[this.lang] || translations['en'])[key] || key;
  };

  PropertyCalcInstance.prototype.fmt = function(n) {
    if (!n || isNaN(n)) return '0.00';
    return parseFloat(n).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  };

  PropertyCalcInstance.prototype.render = function() {
    this.container.innerHTML = `
      <div class="prop-calc-container">
        <!-- Language toggle inside widget -->
        <div style="display:flex; justify-content:flex-end; margin-bottom:1rem;" class="print:hidden">
          <div style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:15px; padding:2px; display:inline-flex;">
            <button class="lang-btn-en" style="border:none; border-radius:12px; padding:4px 12px; font-size:10px; font-weight:700; cursor:pointer; background:${this.lang === 'en' ? '#d4af37' : 'transparent'}; color:${this.lang === 'en' ? '#0a192f' : '#94a3b8'}">EN</button>
            <button class="lang-btn-bm" style="border:none; border-radius:12px; padding:4px 12px; font-size:10px; font-weight:700; cursor:pointer; background:${this.lang === 'bm' ? '#d4af37' : 'transparent'}; color:${this.lang === 'bm' ? '#0a192f' : '#94a3b8'}">BM</button>
          </div>
        </div>

        <!-- Region Tabs -->
        <div class="prop-calc-tabs print:hidden">
          <button class="prop-calc-tab-btn region-kl ${this.region === 'kl' ? 'active' : ''}">${this.t('kl_tab')}</button>
          <button class="prop-calc-tab-btn region-sabah ${this.region === 'sabah' ? 'active' : ''}">${this.t('sabah_tab')}</button>
        </div>

        <!-- Tab Content Wrapper -->
        <div class="prop-calc-content-area"></div>
      </div>
    `;

    this.renderActiveTab();
    this.bindEvents();
  };

  PropertyCalcInstance.prototype.renderActiveTab = function() {
    const area = this.container.querySelector('.prop-calc-content-area');
    if (this.region === 'kl') {
      area.innerHTML = `
        <div class="prop-calc-sub-tabs print:hidden">
          <button class="prop-calc-sub-btn sub-stamp ${this.klTab === 'stamp' ? 'active' : ''}">${this.t('kl_stamp_label')}</button>
          <button class="prop-calc-sub-btn sub-mortgage ${this.klTab === 'mortgage' ? 'active' : ''}">${this.t('kl_mortgage_label')}</button>
          <button class="prop-calc-sub-btn sub-rental ${this.klTab === 'rental' ? 'active' : ''}">${this.t('kl_rental_label')}</button>
        </div>
        <div class="prop-calc-grid">
          <div class="prop-calc-inputs-panel"></div>
          <div class="prop-calc-results-panel"></div>
        </div>
      `;
      this.renderInputsAndResults();
    } else {
      area.innerHTML = `
        <div class="prop-calc-sub-tabs print:hidden">
          <button class="prop-calc-sub-btn sub-sabahstamp ${this.sabahTab === 'sabahstamp' ? 'active' : ''}">${this.t('sabahstamp_label')}</button>
          <button class="prop-calc-sub-btn sub-premium ${this.sabahTab === 'premium' ? 'active' : ''}">${this.t('premium_label')}</button>
          <button class="prop-calc-sub-btn sub-sabahmort ${this.sabahTab === 'sabahmort' ? 'active' : ''}">${this.t('sabahmort_label')}</button>
        </div>
        <div class="prop-calc-grid">
          <div class="prop-calc-inputs-panel"></div>
          <div class="prop-calc-results-panel"></div>
        </div>
      `;
      this.renderInputsAndResults();
    }
  };

  PropertyCalcInstance.prototype.renderInputsAndResults = function() {
    const inputsPanel = this.container.querySelector('.prop-calc-inputs-panel');
    const resultsPanel = this.container.querySelector('.prop-calc-results-panel');

    if (this.region === 'kl') {
      const state = this.inputs.kl;
      if (this.klTab === 'stamp') {
        // Stamp Duty inputs
        inputsPanel.innerHTML = `
          <h2 class="prop-calc-title">${this.t('stamp_title')}</h2>
          <p class="prop-calc-subtitle">${this.t('stamp_sub')}</p>
          <div class="prop-calc-form-group">
            <label class="prop-calc-label">${this.t('prop_price')}</label>
            <div class="prop-calc-input-wrapper">
              <span class="prop-calc-input-prefix">RM</span>
              <input type="number" class="prop-calc-input input-stamp-price" value="${state.stampPrice}">
            </div>
          </div>
          <div class="prop-calc-form-group">
            <label class="prop-calc-label">${this.t('prop_type')}</label>
            <select class="prop-calc-select select-prop-type">
              <option value="residential" ${state.propType === 'residential' ? 'selected' : ''}>${this.t('residential')}</option>
              <option value="commercial" ${state.propType === 'commercial' ? 'selected' : ''}>${this.t('commercial')}</option>
            </select>
          </div>
          <div class="prop-calc-form-group">
            <label class="prop-calc-label">${this.t('loan_amount')}</label>
            <div class="prop-calc-input-wrapper">
              <span class="prop-calc-input-prefix">RM</span>
              <input type="number" class="prop-calc-input input-stamp-loan" value="${state.stampLoan}">
            </div>
          </div>
          <div class="prop-calc-checkbox-group">
            <input type="checkbox" class="prop-calc-checkbox cb-first-home" id="cbFirstHome" ${state.firstHome ? 'checked' : ''}>
            <label class="prop-calc-checkbox-label" for="cbFirstHome">${this.t('first_home')}</label>
          </div>
        `;
        this.calculateKLStamp(resultsPanel);
      } else if (this.klTab === 'mortgage') {
        // Mortgage inputs
        inputsPanel.innerHTML = `
          <h2 class="prop-calc-title">${this.t('mortgage_title')}</h2>
          <p class="prop-calc-subtitle">${this.t('mortgage_sub')}</p>
          <div class="prop-calc-form-group">
            <label class="prop-calc-label">${this.t('prop_price')}</label>
            <div class="prop-calc-input-wrapper">
              <span class="prop-calc-input-prefix">RM</span>
              <input type="number" class="prop-calc-input input-mort-price" value="${state.mortPrice}">
            </div>
          </div>
          <div class="prop-calc-slider-group">
            <div class="prop-calc-slider-header">
              <span class="prop-calc-label">${this.t('down_payment_pct')}</span>
              <span class="prop-calc-slider-value">${state.downPct}%</span>
            </div>
            <input type="range" class="prop-calc-slider slider-down-pct" min="5" max="50" step="5" value="${state.downPct}">
          </div>
          <div class="prop-calc-slider-group">
            <div class="prop-calc-slider-header">
              <span class="prop-calc-label">${this.t('interest_rate')}</span>
              <span class="prop-calc-slider-value">${state.mortRate.toFixed(1)}%</span>
            </div>
            <input type="range" class="prop-calc-slider slider-mort-rate" min="2" max="8" step="0.1" value="${state.mortRate}">
          </div>
          <div class="prop-calc-slider-group">
            <div class="prop-calc-slider-header">
              <span class="prop-calc-label">${this.t('tenure_years')}</span>
              <span class="prop-calc-slider-value">${state.mortTenure} ${this.t('years')}</span>
            </div>
            <input type="range" class="prop-calc-slider slider-mort-tenure" min="5" max="35" step="5" value="${state.mortTenure}">
          </div>
          <div class="prop-calc-form-group">
            <label class="prop-calc-label">${this.t('prop_category')}</label>
            <select class="prop-calc-select select-mort-cat">
              <option value="housing" ${state.mortCategory === 'housing' ? 'selected' : ''}>${this.t('cat_housing')}</option>
              <option value="apartment" ${state.mortCategory === 'apartment' ? 'selected' : ''}>${this.t('cat_apartment')}</option>
              <option value="condo" ${state.mortCategory === 'condo' ? 'selected' : ''}>${this.t('cat_condo')}</option>
              <option value="flat" ${state.mortCategory === 'flat' ? 'selected' : ''}>${this.t('cat_flat')}</option>
              <option value="landed" ${state.mortCategory === 'landed' ? 'selected' : ''}>${this.t('cat_landed')}</option>
              <option value="office" ${state.mortCategory === 'office' ? 'selected' : ''}>${this.t('cat_office')}</option>
            </select>
          </div>
        `;
        this.calculateKLMortgage(resultsPanel);
      } else if (this.klTab === 'rental') {
        // Rental yield inputs
        inputsPanel.innerHTML = `
          <h2 class="prop-calc-title">${this.t('rental_title')}</h2>
          <p class="prop-calc-subtitle">${this.t('rental_sub')}</p>
          <div class="prop-calc-form-group">
            <label class="prop-calc-label">${this.t('prop_price')}</label>
            <div class="prop-calc-input-wrapper">
              <span class="prop-calc-input-prefix">RM</span>
              <input type="number" class="prop-calc-input input-rental-price" value="${state.rentalPrice}">
            </div>
          </div>
          <div class="prop-calc-form-group">
            <label class="prop-calc-label">${this.t('monthly_rental')}</label>
            <div class="prop-calc-input-wrapper">
              <span class="prop-calc-input-prefix">RM</span>
              <input type="number" class="prop-calc-input input-rental-income" value="${state.rentalIncome}">
            </div>
          </div>
          <div class="prop-calc-slider-group">
            <div class="prop-calc-slider-header">
              <span class="prop-calc-label">${this.t('maintenance_pct')}</span>
              <span class="prop-calc-slider-value">${state.maintPct.toFixed(1)}%</span>
            </div>
            <input type="range" class="prop-calc-slider slider-maint-pct" min="0.5" max="5" step="0.5" value="${state.maintPct}">
            <p class="prop-calc-disclaimer">${this.t('maint_hint')}</p>
          </div>
        `;
        this.calculateKLRental(resultsPanel);
      }
    } else {
      const state = this.inputs.sabah;
      if (this.sabahTab === 'sabahstamp') {
        // Sabah stamp
        inputsPanel.innerHTML = `
          <h2 class="prop-calc-title">${this.t('sabahstamp_title')}</h2>
          <p class="prop-calc-subtitle">${this.t('sabahstamp_sub')}</p>
          <div class="prop-calc-form-group">
            <label class="prop-calc-label">${this.t('land_value')}</label>
            <div class="prop-calc-input-wrapper">
              <span class="prop-calc-input-prefix">RM</span>
              <input type="number" class="prop-calc-input input-sabah-stamp-val" value="${state.stampVal}">
            </div>
          </div>
          <div class="prop-calc-form-group">
            <label class="prop-calc-label">${this.t('land_type')}</label>
            <select class="prop-calc-select select-sabah-land-type">
              <option value="nt" ${state.landType === 'nt' ? 'selected' : ''}>${this.t('land_nt')}</option>
              <option value="cl" ${state.landType === 'cl' ? 'selected' : ''}>${this.t('land_cl')}</option>
              <option value="freehold" ${state.landType === 'freehold' ? 'selected' : ''}>${this.t('land_fh')}</option>
              <option value="state" ${state.landType === 'state' ? 'selected' : ''}>${this.t('land_state')}</option>
            </select>
          </div>
          <div class="prop-calc-form-group">
            <label class="prop-calc-label">${this.t('loan_amount')}</label>
            <div class="prop-calc-input-wrapper">
              <span class="prop-calc-input-prefix">RM</span>
              <input type="number" class="prop-calc-input input-sabah-stamp-loan" value="${state.stampLoan}">
            </div>
          </div>
        `;
        this.calculateSabahStamp(resultsPanel);
      } else if (this.sabahTab === 'premium') {
        // Land premium
        inputsPanel.innerHTML = `
          <h2 class="prop-calc-title">${this.t('premium_title')}</h2>
          <p class="prop-calc-subtitle">${this.t('premium_sub')}</p>
          <div class="prop-calc-form-group">
            <label class="prop-calc-label">${this.t('land_market_val')}</label>
            <div class="prop-calc-input-wrapper">
              <span class="prop-calc-input-prefix">RM</span>
              <input type="number" class="prop-calc-input input-premium-val" value="${state.premiumVal}">
            </div>
          </div>
          <div class="prop-calc-form-group">
            <label class="prop-calc-label">${this.t('current_tenure')}</label>
            <select class="prop-calc-select select-from-tenure">
              <option value="nt" ${state.fromTenure === 'nt' ? 'selected' : ''}>${this.t('land_nt')}</option>
              <option value="cl" ${state.fromTenure === 'cl' ? 'selected' : ''}>${this.t('land_cl')}</option>
              <option value="state" ${state.fromTenure === 'state' ? 'selected' : ''}>${this.t('land_state')}</option>
            </select>
          </div>
          <div class="prop-calc-form-group">
            <label class="prop-calc-label">${this.t('target_tenure')}</label>
            <select class="prop-calc-select select-to-tenure">
              <option value="freehold" ${state.toTenure === 'freehold' ? 'selected' : ''}>${this.t('land_fh')}</option>
              <option value="cl" ${state.toTenure === 'cl' ? 'selected' : ''}>${this.t('land_cl')}</option>
            </select>
          </div>
          <div class="prop-calc-form-group">
            <label class="prop-calc-label">${this.t('land_area_acres')}</label>
            <input type="number" class="prop-calc-input input-premium-area" value="${state.premiumArea}" step="0.1">
          </div>
        `;
        this.calculateSabahPremium(resultsPanel);
      } else if (this.sabahTab === 'sabahmort') {
        // Sabah mortgage
        inputsPanel.innerHTML = `
          <h2 class="prop-calc-title">${this.t('sabahmort_title')}</h2>
          <p class="prop-calc-subtitle">${this.t('sabahmort_sub')}</p>
          <div class="prop-calc-form-group">
            <label class="prop-calc-label">${this.t('prop_price')}</label>
            <div class="prop-calc-input-wrapper">
              <span class="prop-calc-input-prefix">RM</span>
              <input type="number" class="prop-calc-input input-sabah-mort-price" value="${state.mortPrice}">
            </div>
          </div>
          <div class="prop-calc-form-group">
            <label class="prop-calc-label">${this.t('land_type')}</label>
            <select class="prop-calc-select select-sabah-mort-land">
              <option value="nt" ${state.mortLandType === 'nt' ? 'selected' : ''}>${this.t('land_nt')}</option>
              <option value="cl" ${state.mortLandType === 'cl' ? 'selected' : ''}>${this.t('land_cl')}</option>
              <option value="freehold" ${state.mortLandType === 'freehold' ? 'selected' : ''}>${this.t('land_fh')}</option>
            </select>
          </div>
          <div class="prop-calc-slider-group">
            <div class="prop-calc-slider-header">
              <span class="prop-calc-label">${this.t('down_payment_pct')}</span>
              <span class="prop-calc-slider-value">${state.mortDown}%</span>
            </div>
            <input type="range" class="prop-calc-slider slider-sabah-down" min="10" max="50" step="5" value="${state.mortDown}">
          </div>
          <div class="prop-calc-slider-group">
            <div class="prop-calc-slider-header">
              <span class="prop-calc-label">${this.t('interest_rate')}</span>
              <span class="prop-calc-slider-value">${state.mortRate.toFixed(1)}%</span>
            </div>
            <input type="range" class="prop-calc-slider slider-sabah-rate" min="2" max="8" step="0.1" value="${state.mortRate}">
          </div>
          <div class="prop-calc-slider-group">
            <div class="prop-calc-slider-header">
              <span class="prop-calc-label">${this.t('tenure_years')}</span>
              <span class="prop-calc-slider-value">${state.mortTenure} ${this.t('years')}</span>
            </div>
            <input type="range" class="prop-calc-slider slider-sabah-tenure" min="5" max="35" step="5" value="${state.mortTenure}">
          </div>
        `;
        this.calculateSabahMortgage(resultsPanel);
      }
    }
  };

  // Calculations
  PropertyCalcInstance.prototype.calculateKLStamp = function(panel) {
    const state = this.inputs.kl;
    const p = parseFloat(state.stampPrice) || 0;
    const loan = parseFloat(state.stampLoan) || 0;

    let mot = 0;
    if (p <= 100000) {
      mot = p * 0.01;
    } else if (p <= 500000) {
      mot = 1000 + (p - 100000) * 0.02;
    } else if (p <= 1000000) {
      mot = 9000 + (p - 500000) * 0.03;
    } else {
      mot = 24000 + (p - 1000000) * 0.04;
    }

    if (state.firstHome && p <= 500000) {
      mot = 0;
    }

    const loanDuty = loan * 0.005;

    let legalFee = 0;
    if (p <= 150000) legalFee = p * 0.01;
    else if (p <= 1000000) legalFee = 1500 + (p - 150000) * 0.007;
    else legalFee = 7450 + (p - 1000000) * 0.005;

    const total = mot + loanDuty + legalFee;

    panel.innerHTML = `
      <div class="prop-calc-results-card">
        <h3 class="prop-calc-results-title">${this.t('results')}</h3>
        <div class="prop-calc-row">
          <span class="prop-calc-row-label">${this.t('mot_stamp')}</span>
          <span class="prop-calc-row-value">RM ${this.fmt(mot)}</span>
        </div>
        <div class="prop-calc-row">
          <span class="prop-calc-row-label">${this.t('loan_stamp')}</span>
          <span class="prop-calc-row-value">RM ${this.fmt(loanDuty)}</span>
        </div>
        <div class="prop-calc-row">
          <span class="prop-calc-row-label">${this.t('legal_fee')}</span>
          <span class="prop-calc-row-value">RM ${this.fmt(legalFee)}</span>
        </div>
        <div class="prop-calc-total-row">
          <span class="prop-calc-total-label">${this.t('total_cost')}</span>
          <span class="prop-calc-total-value">RM ${this.fmt(total)}</span>
        </div>
        ${state.firstHome && p <= 500000 ? `<div class="prop-calc-note">${this.t('first_home_note')}</div>` : ''}
        <p class="prop-calc-disclaimer">${this.t('disclaimer')}</p>
        <div class="prop-calc-buttons print:hidden">
          <button class="prop-calc-btn prop-calc-btn-print btn-print-calc">🖨️ ${this.t('print_pdf')}</button>
          <a href="${this.getWhatsAppLink('stamp')}" target="_blank" class="prop-calc-btn prop-calc-btn-wa">💬 ${this.t('wa_button')}</a>
        </div>
      </div>
    `;
  };

  PropertyCalcInstance.prototype.calculateKLMortgage = function(panel) {
    const state = this.inputs.kl;
    const price = parseFloat(state.mortPrice) || 0;
    const down = price * (state.downPct / 100);
    const loan = price - down;
    const r = (state.mortRate / 100) / 12;
    const n = state.mortTenure * 12;

    let monthly = 0;
    let totalInterest = 0;
    let totalRepay = 0;

    if (r > 0 && n > 0 && loan > 0) {
      monthly = loan * (r * Math.pow(1 + r, n)) / (Math.pow(1 + r, n) - 1);
      totalRepay = monthly * n;
      totalInterest = totalRepay - loan;
    }

    panel.innerHTML = `
      <div class="prop-calc-results-card">
        <h3 class="prop-calc-results-title">${this.t('results')}</h3>
        <div class="prop-calc-row">
          <span class="prop-calc-row-label">${this.t('loan_amount')}</span>
          <span class="prop-calc-row-value">RM ${this.fmt(loan)}</span>
        </div>
        <div class="prop-calc-row">
          <span class="prop-calc-row-label">${this.t('down_payment')}</span>
          <span class="prop-calc-row-value">RM ${this.fmt(down)}</span>
        </div>
        <div class="prop-calc-row">
          <span class="prop-calc-row-label">${this.t('total_interest')}</span>
          <span class="prop-calc-row-value">RM ${this.fmt(totalInterest)}</span>
        </div>
        <div class="prop-calc-row">
          <span class="prop-calc-row-label">${this.t('total_repay')}</span>
          <span class="prop-calc-row-value">RM ${this.fmt(totalRepay)}</span>
        </div>
        <div class="prop-calc-total-row">
          <span class="prop-calc-total-label">${this.t('monthly_inst')}</span>
          <span class="prop-calc-total-value">RM ${this.fmt(monthly)}</span>
        </div>
        <p class="prop-calc-disclaimer">${this.t('disclaimer')}</p>
        <div class="prop-calc-buttons print:hidden">
          <button class="prop-calc-btn prop-calc-btn-print btn-print-calc">🖨️ ${this.t('print_pdf')}</button>
          <a href="${this.getWhatsAppLink('mortgage')}" target="_blank" class="prop-calc-btn prop-calc-btn-wa">💬 ${this.t('wa_button')}</a>
        </div>
      </div>
    `;
  };

  PropertyCalcInstance.prototype.calculateKLRental = function(panel) {
    const state = this.inputs.kl;
    const price = parseFloat(state.rentalPrice) || 0;
    const monthly = parseFloat(state.rentalIncome) || 0;

    let gross = 0;
    let net = 0;
    let maintAmt = 0;
    let payback = '0.0';

    if (price > 0 && monthly > 0) {
      const annualRent = monthly * 12;
      maintAmt = price * (state.maintPct / 100);
      gross = ((annualRent / price) * 100).toFixed(2);
      net = (((annualRent - maintAmt) / price) * 100).toFixed(2);
      payback = net > 0 ? (100 / net).toFixed(1) : 'N/A';
    }

    panel.innerHTML = `
      <div class="prop-calc-results-card">
        <h3 class="prop-calc-results-title">${this.t('results')}</h3>
        <div class="prop-calc-row">
          <span class="prop-calc-row-label">${this.t('gross_yield')}</span>
          <span class="prop-calc-row-value">${gross}%</span>
        </div>
        <div class="prop-calc-row">
          <span class="prop-calc-row-label">${this.t('annual_maint')}</span>
          <span class="prop-calc-row-value">RM ${this.fmt(maintAmt)}</span>
        </div>
        <div class="prop-calc-row">
          <span class="prop-calc-row-label">${this.t('payback_period')}</span>
          <span class="prop-calc-row-value">${payback} ${this.t('years')}</span>
        </div>
        <div class="prop-calc-total-row">
          <span class="prop-calc-total-label">${this.t('net_yield')}</span>
          <span class="prop-calc-total-value">${net}%</span>
        </div>
        <p class="prop-calc-disclaimer">${this.t('disclaimer')}</p>
        <div class="prop-calc-buttons print:hidden">
          <button class="prop-calc-btn prop-calc-btn-print btn-print-calc">🖨️ ${this.t('print_pdf')}</button>
          <a href="${this.getWhatsAppLink('rental')}" target="_blank" class="prop-calc-btn prop-calc-btn-wa">💬 ${this.t('wa_button')}</a>
        </div>
      </div>
    `;
  };

  PropertyCalcInstance.prototype.calculateSabahStamp = function(panel) {
    const state = this.inputs.sabah;
    const val = parseFloat(state.stampVal) || 0;
    const loan = parseFloat(state.stampLoan) || 0;

    let mot = 0;
    if (val <= 100000) mot = val * 0.01;
    else if (val <= 500000) mot = 1000 + (val - 100000) * 0.02;
    else if (val <= 1000000) mot = 9000 + (val - 500000) * 0.03;
    else mot = 24000 + (val - 1000000) * 0.04;

    const loanDuty = loan * 0.005;
    const total = mot + loanDuty;

    panel.innerHTML = `
      <div class="prop-calc-results-card">
        <h3 class="prop-calc-results-title">${this.t('results')}</h3>
        <div class="prop-calc-row">
          <span class="prop-calc-row-label">${this.t('mot_stamp')}</span>
          <span class="prop-calc-row-value">RM ${this.fmt(mot)}</span>
        </div>
        <div class="prop-calc-row">
          <span class="prop-calc-row-label">${this.t('loan_stamp')}</span>
          <span class="prop-calc-row-value">RM ${this.fmt(loanDuty)}</span>
        </div>
        <div class="prop-calc-total-row">
          <span class="prop-calc-total-label">${this.t('total_cost')}</span>
          <span class="prop-calc-total-value">RM ${this.fmt(total)}</span>
        </div>
        <p class="prop-calc-disclaimer">${this.t('sabah_disclaimer')}</p>
        <div class="prop-calc-buttons print:hidden">
          <button class="prop-calc-btn prop-calc-btn-print btn-print-calc">🖨️ ${this.t('print_pdf')}</button>
          <a href="${this.getWhatsAppLink('sabah_stamp')}" target="_blank" class="prop-calc-btn prop-calc-btn-wa">💬 ${this.t('wa_button')}</a>
        </div>
      </div>
    `;
  };

  PropertyCalcInstance.prototype.calculateSabahPremium = function(panel) {
    const state = this.inputs.sabah;
    const val = parseFloat(state.premiumVal) || 0;

    let rate = 20;
    // Premium rates (allow customization from db via land_premium type if custom premium rate is set)
    const dbPreset = (this.options.presets || []).find(p => p.id === 'sabah_land_premium');
    if (dbPreset && dbPreset.premiumRate) {
      rate = dbPreset.premiumRate;
    } else {
      const rateMap = {
        'nt-freehold': 25, 'nt-cl': 15,
        'cl-freehold': 20, 'cl-cl': 0,
        'state-freehold': 30, 'state-cl': 18
      };
      const key = state.fromTenure + '-' + state.toTenure;
      rate = rateMap[key] !== undefined ? rateMap[key] : 20;
    }

    const premium = val * (rate / 100);
    const admin = Math.min(Math.max(premium * 0.05, 200), 5000);
    const total = premium + admin;

    panel.innerHTML = `
      <div class="prop-calc-results-card">
        <h3 class="prop-calc-results-title">${this.t('results')}</h3>
        <div class="prop-calc-row">
          <span class="prop-calc-row-label">${this.t('premium_rate')}</span>
          <span class="prop-calc-row-value">${rate}%</span>
        </div>
        <div class="prop-calc-row">
          <span class="prop-calc-row-label">${this.t('est_premium')}</span>
          <span class="prop-calc-row-value">RM ${this.fmt(premium)}</span>
        </div>
        <div class="prop-calc-row">
          <span class="prop-calc-row-label">${this.t('admin_fee')}</span>
          <span class="prop-calc-row-value">RM ${this.fmt(admin)}</span>
        </div>
        <div class="prop-calc-total-row">
          <span class="prop-calc-total-label">${this.t('total_cost')}</span>
          <span class="prop-calc-total-value">RM ${this.fmt(total)}</span>
        </div>
        <div class="prop-calc-warning">${this.t('premium_note')}</div>
        <p class="prop-calc-disclaimer">${this.t('premium_disclaimer')}</p>
        <div class="prop-calc-buttons print:hidden">
          <button class="prop-calc-btn prop-calc-btn-print btn-print-calc">🖨️ ${this.t('print_pdf')}</button>
          <a href="${this.getWhatsAppLink('land_premium')}" target="_blank" class="prop-calc-btn prop-calc-btn-wa">💬 ${this.t('wa_button')}</a>
        </div>
      </div>
    `;
  };

  PropertyCalcInstance.prototype.calculateSabahMortgage = function(panel) {
    const state = this.inputs.sabah;
    const price = parseFloat(state.mortPrice) || 0;
    const down = price * (state.mortDown / 100);
    const loan = price - down;
    const r = (state.mortRate / 100) / 12;
    const n = state.mortTenure * 12;

    let monthly = 0;
    let totalInterest = 0;
    let totalRepay = 0;

    if (r > 0 && n > 0 && loan > 0) {
      monthly = loan * (r * Math.pow(1 + r, n)) / (Math.pow(1 + r, n) - 1);
      totalRepay = monthly * n;
      totalInterest = totalRepay - loan;
    }

    panel.innerHTML = `
      <div class="prop-calc-results-card">
        <h3 class="prop-calc-results-title">${this.t('results')}</h3>
        <div class="prop-calc-row">
          <span class="prop-calc-row-label">${this.t('loan_amount')}</span>
          <span class="prop-calc-row-value">RM ${this.fmt(loan)}</span>
        </div>
        <div class="prop-calc-row">
          <span class="prop-calc-row-label">${this.t('total_interest')}</span>
          <span class="prop-calc-row-value">RM ${this.fmt(totalInterest)}</span>
        </div>
        <div class="prop-calc-row">
          <span class="prop-calc-row-label">${this.t('total_repay')}</span>
          <span class="prop-calc-row-value">RM ${this.fmt(totalRepay)}</span>
        </div>
        <div class="prop-calc-total-row">
          <span class="prop-calc-total-label">${this.t('monthly_inst')}</span>
          <span class="prop-calc-total-value">RM ${this.fmt(monthly)}</span>
        </div>
        <p class="prop-calc-disclaimer">${this.t('disclaimer')}</p>
        <div class="prop-calc-buttons print:hidden">
          <button class="prop-calc-btn prop-calc-btn-print btn-print-calc">🖨️ ${this.t('print_pdf')}</button>
          <a href="${this.getWhatsAppLink('sabah_mortgage')}" target="_blank" class="prop-calc-btn prop-calc-btn-wa">💬 ${this.t('wa_button')}</a>
        </div>
      </div>
    `;
  };

  PropertyCalcInstance.prototype.getWhatsAppLink = function(type) {
    const phone = '601283386392';
    const msgs = {
      en: {
        stamp: 'Hi, I used the Stamp Duty Calculator on your website. I would like to enquire further about property purchase costs.',
        mortgage: 'Hi, I used the Mortgage Calculator on your website. I would like to know more about home loan options.',
        rental: 'Hi, I used the Rental Yield Calculator on your website. I would like to know more about property investment.',
        sabah_stamp: 'Hi, I used the Sabah Stamp Duty Calculator on your website. I would like to enquire about land transfer costs in Sabah.',
        land_premium: 'Hi, I used the Sabah Premium Calculator on your website. I would like to enquire about land conversion fees.',
        sabah_mortgage: 'Hi, I used the Sabah Mortgage Calculator on your website. I would like to know more about property financing in Sabah.'
      },
      bm: {
        stamp: 'Salam, saya telah menggunakan Kalkulator Duti Setem di laman web anda. Saya ingin bertanya lanjut tentang kos pembelian hartanah.',
        mortgage: 'Salam, saya telah menggunakan Kalkulator Pinjaman Perumahan. Saya ingin tahu lebih lanjut tentang pilihan pinjaman bank.',
        rental: 'Salam, saya telah menggunakan Kalkulator Hasil Sewa. Saya ingin tanya tentang pelaburan hartanah.',
        sabah_stamp: 'Salam, saya telah menggunakan Kalkulator Duti Setem Sabah. Saya ingin bertanya tentang kos pindah milik tanah di Sabah.',
        land_premium: 'Salam, saya telah menggunakan Kalkulator Premium Tanah Sabah. Saya ingin bertanya tentang yuran pertukaran hak milik tanah.',
        sabah_mortgage: 'Salam, saya telah menggunakan Kalkulator Pinjaman Sabah. Saya ingin tahu tentang pembiayaan hartanah di Sabah.'
      }
    };
    const msg = (msgs[this.lang] || msgs['en'])[type] || msgs['en'][type];
    return 'https://wa.me/' + phone + '?text=' + encodeURIComponent(msg);
  };

  PropertyCalcInstance.prototype.bindEvents = function() {
    const inst = this;

    // Sync language from window events
    if (this._onLangChange) {
      window.removeEventListener('calc-lang-changed', this._onLangChange);
    }
    this._onLangChange = function(e) {
      if (e.detail && e.detail.lang && e.detail.lang !== inst.lang) {
        inst.lang = e.detail.lang;
        inst.render();
      }
    };
    window.addEventListener('calc-lang-changed', this._onLangChange);

    // Lang toggle
    const enBtn = this.container.querySelector('.lang-btn-en');
    const bmBtn = this.container.querySelector('.lang-btn-bm');
    if (enBtn) {
      enBtn.addEventListener('click', function() {
        inst.lang = 'en';
        localStorage.setItem('calcLang', 'en');
        window.dispatchEvent(new CustomEvent('calc-lang-changed', { detail: { lang: 'en' } }));
        inst.render();
      });
    }
    if (bmBtn) {
      bmBtn.addEventListener('click', function() {
        inst.lang = 'bm';
        localStorage.setItem('calcLang', 'bm');
        window.dispatchEvent(new CustomEvent('calc-lang-changed', { detail: { lang: 'bm' } }));
        inst.render();
      });
    }

    // Region tabs
    const klTabBtn = this.container.querySelector('.region-kl');
    const sabahTabBtn = this.container.querySelector('.region-sabah');
    if (klTabBtn) {
      klTabBtn.addEventListener('click', function() {
        inst.region = 'kl';
        inst.render();
      });
    }
    if (sabahTabBtn) {
      sabahTabBtn.addEventListener('click', function() {
        inst.region = 'sabah';
        inst.render();
      });
    }

    // Sub tabs
    const subStamp = this.container.querySelector('.sub-stamp');
    const subMort = this.container.querySelector('.sub-mortgage');
    const subRental = this.container.querySelector('.sub-rental');
    if (subStamp) {
      subStamp.addEventListener('click', function() {
        inst.klTab = 'stamp';
        inst.renderActiveTab();
        inst.bindEvents();
      });
    }
    if (subMort) {
      subMort.addEventListener('click', function() {
        inst.klTab = 'mortgage';
        inst.renderActiveTab();
        inst.bindEvents();
      });
    }
    if (subRental) {
      subRental.addEventListener('click', function() {
        inst.klTab = 'rental';
        inst.renderActiveTab();
        inst.bindEvents();
      });
    }

    // Sabah Sub tabs
    const subSabahStamp = this.container.querySelector('.sub-sabahstamp');
    const subPremium = this.container.querySelector('.sub-premium');
    const subSabahMort = this.container.querySelector('.sub-sabahmort');
    if (subSabahStamp) {
      subSabahStamp.addEventListener('click', function() {
        inst.sabahTab = 'sabahstamp';
        inst.renderActiveTab();
        inst.bindEvents();
      });
    }
    if (subPremium) {
      subPremium.addEventListener('click', function() {
        inst.sabahTab = 'premium';
        inst.renderActiveTab();
        inst.bindEvents();
      });
    }
    if (subSabahMort) {
      subSabahMort.addEventListener('click', function() {
        inst.sabahTab = 'sabahmort';
        inst.renderActiveTab();
        inst.bindEvents();
      });
    }

    // Input handlers
    const resultsPanel = this.container.querySelector('.prop-calc-results-panel');

    // KL inputs bind
    const stampPrice = this.container.querySelector('.input-stamp-price');
    const stampLoan = this.container.querySelector('.input-stamp-loan');
    const propType = this.container.querySelector('.select-prop-type');
    const firstHome = this.container.querySelector('.cb-first-home');

    if (stampPrice) stampPrice.addEventListener('input', function() { inst.inputs.kl.stampPrice = this.value; inst.calculateKLStamp(resultsPanel); });
    if (stampLoan) stampLoan.addEventListener('input', function() { inst.inputs.kl.stampLoan = this.value; inst.calculateKLStamp(resultsPanel); });
    if (propType) propType.addEventListener('change', function() { inst.inputs.kl.propType = this.value; inst.calculateKLStamp(resultsPanel); });
    if (firstHome) firstHome.addEventListener('change', function() { inst.inputs.kl.firstHome = this.checked; inst.calculateKLStamp(resultsPanel); });

    // KL Mortgage sliders & inputs bind
    const mortPrice = this.container.querySelector('.input-mort-price');
    const sliderDown = this.container.querySelector('.slider-down-pct');
    const sliderRate = this.container.querySelector('.slider-mort-rate');
    const sliderTenure = this.container.querySelector('.slider-mort-tenure');
    const selectMortCat = this.container.querySelector('.select-mort-cat');

    if (mortPrice) mortPrice.addEventListener('input', function() { inst.inputs.kl.mortPrice = this.value; inst.calculateKLMortgage(resultsPanel); });
    if (sliderDown) {
      sliderDown.addEventListener('input', function() {
        inst.inputs.kl.downPct = parseInt(this.value);
        inst.container.querySelector('.slider-down-pct').previousElementSibling.querySelector('.prop-calc-slider-value').textContent = this.value + '%';
        inst.calculateKLMortgage(resultsPanel);
      });
    }
    if (sliderRate) {
      sliderRate.addEventListener('input', function() {
        inst.inputs.kl.mortRate = parseFloat(this.value);
        inst.container.querySelector('.slider-mort-rate').previousElementSibling.querySelector('.prop-calc-slider-value').textContent = parseFloat(this.value).toFixed(1) + '%';
        inst.calculateKLMortgage(resultsPanel);
      });
    }
    if (sliderTenure) {
      sliderTenure.addEventListener('input', function() {
        inst.inputs.kl.mortTenure = parseInt(this.value);
        inst.container.querySelector('.slider-mort-tenure').previousElementSibling.querySelector('.prop-calc-slider-value').textContent = this.value + ' ' + inst.t('years');
        inst.calculateKLMortgage(resultsPanel);
      });
    }
    if (selectMortCat) selectMortCat.addEventListener('change', function() { inst.inputs.kl.mortCategory = this.value; });

    // KL Rental
    const rentalPrice = this.container.querySelector('.input-rental-price');
    const rentalIncome = this.container.querySelector('.input-rental-income');
    const sliderMaint = this.container.querySelector('.slider-maint-pct');

    if (rentalPrice) rentalPrice.addEventListener('input', function() { inst.inputs.kl.rentalPrice = this.value; inst.calculateKLRental(resultsPanel); });
    if (rentalIncome) rentalIncome.addEventListener('input', function() { inst.inputs.kl.rentalIncome = this.value; inst.calculateKLRental(resultsPanel); });
    if (sliderMaint) {
      sliderMaint.addEventListener('input', function() {
        inst.inputs.kl.maintPct = parseFloat(this.value);
        inst.container.querySelector('.slider-maint-pct').previousElementSibling.querySelector('.prop-calc-slider-value').textContent = parseFloat(this.value).toFixed(1) + '%';
        inst.calculateKLRental(resultsPanel);
      });
    }

    // Sabah inputs bind
    const sabahStampVal = this.container.querySelector('.input-sabah-stamp-val');
    const selectSabahLand = this.container.querySelector('.select-sabah-land-type');
    const sabahStampLoan = this.container.querySelector('.input-sabah-stamp-loan');

    if (sabahStampVal) sabahStampVal.addEventListener('input', function() { inst.inputs.sabah.stampVal = this.value; inst.calculateSabahStamp(resultsPanel); });
    if (selectSabahLand) selectSabahLand.addEventListener('change', function() { inst.inputs.sabah.landType = this.value; inst.calculateSabahStamp(resultsPanel); });
    if (sabahStampLoan) sabahStampLoan.addEventListener('input', function() { inst.inputs.sabah.stampLoan = this.value; inst.calculateSabahStamp(resultsPanel); });

    // Sabah premium
    const premiumVal = this.container.querySelector('.input-premium-val');
    const selectFrom = this.container.querySelector('.select-from-tenure');
    const selectTo = this.container.querySelector('.select-to-tenure');
    const premiumArea = this.container.querySelector('.input-premium-area');

    if (premiumVal) premiumVal.addEventListener('input', function() { inst.inputs.sabah.premiumVal = this.value; inst.calculateSabahPremium(resultsPanel); });
    if (selectFrom) selectFrom.addEventListener('change', function() { inst.inputs.sabah.fromTenure = this.value; inst.calculateSabahPremium(resultsPanel); });
    if (selectTo) selectTo.addEventListener('change', function() { inst.inputs.sabah.toTenure = this.value; inst.calculateSabahPremium(resultsPanel); });
    if (premiumArea) premiumArea.addEventListener('input', function() { inst.inputs.sabah.premiumArea = this.value; inst.calculateSabahPremium(resultsPanel); });

    // Sabah mortgage
    const sabahMortPrice = this.container.querySelector('.input-sabah-mort-price');
    const selectSabahMortLand = this.container.querySelector('.select-sabah-mort-land');
    const sliderSabahDown = this.container.querySelector('.slider-sabah-down');
    const sliderSabahRate = this.container.querySelector('.slider-sabah-rate');
    const sliderSabahTenure = this.container.querySelector('.slider-sabah-tenure');

    if (sabahMortPrice) sabahMortPrice.addEventListener('input', function() { inst.inputs.sabah.mortPrice = this.value; inst.calculateSabahMortgage(resultsPanel); });
    if (selectSabahMortLand) selectSabahMortLand.addEventListener('change', function() { inst.inputs.sabah.mortLandType = this.value; });
    if (sliderSabahDown) {
      sliderSabahDown.addEventListener('input', function() {
        inst.inputs.sabah.mortDown = parseInt(this.value);
        inst.container.querySelector('.slider-sabah-down').previousElementSibling.querySelector('.prop-calc-slider-value').textContent = this.value + '%';
        inst.calculateSabahMortgage(resultsPanel);
      });
    }
    if (sliderSabahRate) {
      sliderSabahRate.addEventListener('input', function() {
        inst.inputs.sabah.mortRate = parseFloat(this.value);
        inst.container.querySelector('.slider-sabah-rate').previousElementSibling.querySelector('.prop-calc-slider-value').textContent = parseFloat(this.value).toFixed(1) + '%';
        inst.calculateSabahMortgage(resultsPanel);
      });
    }
    if (sliderSabahTenure) {
      sliderSabahTenure.addEventListener('input', function() {
        inst.inputs.sabah.mortTenure = parseInt(this.value);
        inst.container.querySelector('.slider-sabah-tenure').previousElementSibling.querySelector('.prop-calc-slider-value').textContent = this.value + ' ' + inst.t('years');
        inst.calculateSabahMortgage(resultsPanel);
      });
    }

    // Print button handler
    const printBtns = this.container.querySelectorAll('.btn-print-calc');
    printBtns.forEach(btn => {
      btn.addEventListener('click', function() {
        window.print();
      });
    });
  };
})();
