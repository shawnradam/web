<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>
    <?php echo isset($pageTitle) ? $pageTitle . ' - AdvisorCMS' : 'AdvisorCMS'; ?>
</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
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