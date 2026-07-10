<?php
if (!headers_sent()) {
    header_remove('X-Powered-By');
    header('Content-Type: text/html; charset=utf-8');
}
ini_set('default_charset', 'UTF-8');
require_once 'includes/maintenance_check.php';
?>
<?php require_once 'includes/lang.php'; ?>
<!DOCTYPE html>
<html lang="<?php echo current_lang() === 'ms' ? 'ms' : 'en'; ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($page_title ?? t('site.default_title')); ?></title>
    <meta name="description"
        content="<?php echo htmlspecialchars($page_desc ?? t('site.default_description')); ?>">
    <?php if (isset($nofollow_meta))
        echo $nofollow_meta; ?>

    <!-- PWA Manifest -->
    <link rel="manifest" href="<?php echo htmlspecialchars(public_path('manifest.json')); ?>">

    <!-- Social Meta (Open Graph) -->
    <meta property="og:title"
        content="<?php echo htmlspecialchars($page_title ?? t('site.default_title')); ?>">
    <meta property="og:description"
        content="<?php echo htmlspecialchars($page_desc ?? t('site.default_description')); ?>">
    <?php if (isset($meta_image) && $meta_image): ?>
        <meta property="og:image" content="<?php echo htmlspecialchars($meta_image); ?>">
    <?php endif; ?>
    <meta property="og:url" content="<?php echo "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>">
    <meta property="og:type" content="website">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:wght@400;700&display=swap"
        rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'navy': { 900: '#0a0e27', 800: '#141937' },
                        'gold': { 500: '#d4af37', 400: '#e0c158' }
                    }
                }
            }
        }
    </script>

    <!-- Core Styles -->
    <link rel="stylesheet" href="assets/style.css">

    <!-- Alpine.js & Scripts -->
    <script src="assets/app.js" defer></script>
    <style>
        /* Custom scrollbar for executive feel */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #0A192F;
        }

        ::-webkit-scrollbar-thumb {
            background: #112240;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #064E3B;
        }

        /* Typography Overrides */
        .font-serif {
            font-family: 'Playfair Display', serif;
        }

        .font-sans {
            font-family: 'Inter', sans-serif;
        }

        /* Alpine Cloak */
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="font-sans antialiased bg-navy-900 text-slate-300">
    <div class="min-h-screen flex flex-col">


