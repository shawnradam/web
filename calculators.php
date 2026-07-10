<?php
// calculators.php — Public Property & Land Calculators page
require_once 'includes/header.php';
require_once 'includes/navigation.php';
require_once 'plugins/property-calculator/property-calculator.php';

// Register the plugin styles and scripts
PropertyCalculator::register_assets();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Property & Land Calculators — Shawn Radam</title>
    <meta name="description" content="Free property stamp duty, mortgage, rental yield and Sabah land premium calculators by Shawn Radam, Personal Advisor.">
</head>
<body class="bg-navy-950 min-h-screen">

<div class="pt-10 pb-24 px-4 max-w-5xl mx-auto">
    <!-- Render the Property Calculator Widget -->
    <?php echo PropertyCalculator::render('property-calculator-widget'); ?>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
