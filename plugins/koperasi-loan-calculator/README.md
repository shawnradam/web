# Koperasi Personal Loan Calculator

A modern, responsive, and completely dependency-free JavaScript/CSS personal loan calculator tailored specifically for Koperasi (cooperative) financing products in Malaysia and Indonesia.

This plugin is style-isolated, responsive, and features upfront deduction calculations (net payout), real-time SVG donut visualizations, toggleable amortization tables, and a built-in admin configuration panel.

---

## File Contents

- `koperasi-calculator.css` - Scoped styles (`.kop-calc-wrapper`) implementing Light, Dark, and Cosmic-Dark themes.
- `koperasi-calculator.js` - Logic module exposing `window.KoperasiCalculator.init()`.
- `koperasi-calculator.php` - Static PHP helper for assets registration and view rendering.
- `koperasi-calculator.html` - Interactive showcase playground, which also acts as a clean Iframe endpoint.

---

## Integration Methods

### 1. Pure JavaScript (Any HTML Page)

Add the CSS to the `<head>` of your page, add a target container div, and initialize the widget with JavaScript.

```html
<!-- Include stylesheet -->
<link rel="stylesheet" href="plugins/koperasi-loan-calculator/koperasi-calculator.css">

<!-- Container element -->
<div id="loan-calculator-widget"></div>

<!-- Include controller and initialize -->
<script src="plugins/koperasi-loan-calculator/koperasi-calculator.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    KoperasiCalculator.init('loan-calculator-widget', {
      theme: 'cosmic', // Options: 'light', 'dark', 'cosmic'
      calculationMode: 'flat', // Options: 'flat' (standard koperasi), 'reducing' (EIR)
      defaultLoan: 30000,
      defaultTenure: 5
    });
  });
</script>
```

### 2. PHP Integration (For PHP templates/widgets)

Use the provided helper class to import scripts and render the widget inline.

```php
<?php
// Include the helper file
require_once 'plugins/koperasi-loan-calculator/koperasi-calculator.php';

// Register CSS and JS assets in your header
KoperasiLoanCalculator::register_assets();
?>

<!-- Render widget anywhere in your markup -->
<?php 
echo KoperasiLoanCalculator::render('koperasi-calculator', [
    'theme' => 'cosmic',
    'defaultLoan' => 40000,
    'defaultTenure' => 7
]); 
?>
```

### 3. Iframe Embed (Guaranteed Style Isolation)

If you are worried about CSS stylesheet conflicts on your website, load the standalone HTML template directly inside an `<iframe>` by passing URL parameters.

```html
<iframe src="plugins/koperasi-loan-calculator/koperasi-calculator.html?embed=1&theme=cosmic" 
        width="100%" 
        height="700px" 
        style="border: none; overflow: hidden; border-radius: 16px;">
</iframe>
```

---

## Customizing Rates and Presets

The calculator allows complete customization of rates, fees, limits, and presets by overriding the presets array during initialization.

```javascript
KoperasiCalculator.init('loan-calculator-widget', {
  theme: 'cosmic',
  defaultPreset: 'my_koperasi',
  presets: [
    {
      id: 'my_koperasi',
      name: 'Custom Koperasi Co-op',
      interestRate: 3.99, // Flat rate % p.a.
      minLoan: 5000,
      maxLoan: 150000,
      minTenure: 2,
      maxTenure: 10,
      processingFeePercent: 4.0, // Upfront admin processing fee %
      insurancePercent: 3.0,     // Upfront Takaful insurance premium %
      membershipFee: 50,         // Entry registration fee RM (flat)
      advancePaymentMonths: 2    // Months of repayment installments deducted upfront
    }
  ]
});
```

---

## Upfront Deductions Math (Net Payout)

Koperasi loans commonly deduct fees upfront, meaning the cash payout received in the bank is less than the borrowed amount:

$$\text{Processing Fee} = \text{Gross Loan} \times \text{Processing Fee \%}$$
$$\text{Takaful Insurance} = \text{Gross Loan} \times \text{Insurance \%}$$
$$\text{Advance Installments} = \text{Monthly Repayment} \times \text{Advance Months}$$
$$\text{Total Deductions} = \text{Processing Fee} + \text{Takaful Insurance} + \text{Membership Fee} + \text{Advance Installments}$$
$$\text{Net Payout} = \text{Gross Loan} - \text{Total Deductions}$$
