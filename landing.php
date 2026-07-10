<?php
require_once 'admin/db_connect.php';

function landing_asset_url($path)
{
    $path = (string) $path;
    if ($path === '' || preg_match('/^(https?:|data:|\/)/i', $path)) {
        return $path;
    }

    return function_exists('public_path') ? public_path($path) : $path;
}

$landing = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM landing_pages WHERE slug = ? LIMIT 1");
    $landingSlug = $landingSlug ?? preg_replace('/[^a-z0-9_-]/i', '', (string) ($_GET['slug'] ?? 'tanah-lot-selupoh'));
    if ($landingSlug === '') {
        $landingSlug = 'tanah-lot-selupoh';
    }
    $stmt->execute([$landingSlug]);
    $landing = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $landing = null;
}
if (!$landing || (int) ($landing['is_published'] ?? 0) !== 1) {
    http_response_code(404);
    $page_title = 'Page Not Found';
    include 'includes/header.php';
    include 'includes/navigation.php';
    echo '<main class="min-h-screen bg-navy-900 pt-32 px-6 text-center"><h1 class="font-serif text-4xl text-white">Page Not Found</h1></main>';
    include 'includes/footer.php';
    exit;
}

$highlights = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) ($landing['highlights'] ?? '')))));
$facilities = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) ($landing['facilities'] ?? '')))));
$landingImagePath = $landing['image_url'] ?: 'assets/landing/tanah-lot-selupoh-ads-whatsapp.jpg';
if (($landing['slug'] ?? '') === 'tanah-lot-selupoh' && $landingImagePath === 'assets/landing/tanah-lot-selupoh-ads.jpg') {
    $landingImagePath = 'assets/landing/tanah-lot-selupoh-ads-whatsapp.jpg';
}
$imageUrl = landing_asset_url($landingImagePath);
$whatsappNumber = '60128338639';
$whatsappText = rawurlencode('Saya berminat dengan Tanah Lot Selupoh, Tuaran. Boleh saya dapatkan maklumat lanjut?');
$whatsappUrl = 'https://wa.me/' . $whatsappNumber . '?text=' . $whatsappText;
$page_title = $landing['seo_title'] ?: $landing['page_title'];
$page_desc = $landing['seo_desc'] ?: $landing['hero_subtitle'];
include 'includes/header.php';
include 'includes/navigation.php';
?>

<main class="bg-slate-950 text-slate-200">
    <section class="relative overflow-hidden bg-navy-900 pt-24 md:pt-28 lg:pt-32 pb-14 md:pb-20">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(212,175,55,0.16),transparent_34%),linear-gradient(135deg,rgba(10,14,39,0.98),rgba(2,6,23,0.98))]"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 grid lg:grid-cols-[0.92fr_1.08fr] gap-8 lg:gap-12 items-center">
            <div>
                <p class="text-gold-500 uppercase tracking-[0.32em] text-xs font-bold mb-4"><?php echo htmlspecialchars($landing['eyebrow'] ?: 'Tanah Lot Tuaran'); ?></p>
                <h1 class="font-serif text-4xl sm:text-5xl lg:text-6xl text-white leading-tight mb-6"><?php echo htmlspecialchars($landing['hero_title']); ?></h1>
                <p class="text-lg text-slate-300 leading-relaxed mb-8 max-w-2xl"><?php echo htmlspecialchars($landing['hero_subtitle']); ?></p>

                <div class="grid sm:grid-cols-3 gap-3 mb-8">
                    <div class="border border-gold-500/30 bg-slate-950/60 p-4">
                        <p class="text-xs uppercase tracking-widest text-slate-500 mb-1">Location</p>
                        <p class="text-white font-bold"><?php echo htmlspecialchars($landing['location']); ?></p>
                    </div>
                    <div class="border border-gold-500/30 bg-slate-950/60 p-4">
                        <p class="text-xs uppercase tracking-widest text-slate-500 mb-1">Size</p>
                        <p class="text-white font-bold"><?php echo htmlspecialchars($landing['lot_size']); ?></p>
                    </div>
                    <div class="border border-gold-500/30 bg-slate-950/60 p-4">
                        <p class="text-xs uppercase tracking-widest text-slate-500 mb-1">Price</p>
                        <p class="text-gold-500 font-serif text-2xl"><?php echo htmlspecialchars($landing['price']); ?></p>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="<?php echo htmlspecialchars($whatsappUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center bg-gold-500 hover:bg-gold-400 text-navy-900 px-6 py-3 text-xs font-bold uppercase tracking-wider transition-colors"><?php echo htmlspecialchars($landing['cta_text'] ?: 'Hubungi Sekarang'); ?></a>
                    <button type="button" data-full-info-trigger class="inline-flex items-center justify-center border border-slate-600 hover:border-gold-500 text-white px-6 py-3 text-xs font-bold uppercase tracking-wider transition-colors">View Full Info</button>
                </div>
            </div>

            <div id="full-info" class="bg-slate-950 border border-slate-800 p-2 shadow-2xl">
                <img src="<?php echo htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($landing['page_title']); ?> advertisement" class="w-full h-auto object-contain bg-slate-950">
            </div>
        </div>
    </section>

    <section class="bg-[#f7f5ef] text-slate-900 py-14 md:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 grid lg:grid-cols-[1fr_0.9fr] gap-10 lg:gap-14 items-start">
            <div>
                <p class="text-gold-700 uppercase tracking-[0.28em] text-xs font-bold mb-4">Info Tanah</p>
                <h2 class="font-serif text-3xl sm:text-4xl md:text-5xl text-navy-900 leading-tight mb-6"><?php echo htmlspecialchars($landing['page_title']); ?></h2>
                <p class="text-slate-700 leading-relaxed text-lg mb-8"><?php echo htmlspecialchars($landing['intro_text']); ?></p>
                <div class="bg-white border border-slate-200 p-6 sm:p-8 grid sm:grid-cols-2 gap-5">
                    <div>
                        <p class="text-sm text-slate-500 mb-1">Keluasan</p>
                        <p class="text-2xl font-bold text-navy-900"><?php echo htmlspecialchars($landing['lot_size']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 mb-1">Harga</p>
                        <p class="text-2xl font-bold text-red-700"><?php echo htmlspecialchars($landing['price']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 mb-1">Ketersediaan</p>
                        <p class="font-bold text-navy-900"><?php echo htmlspecialchars($landing['availability']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 mb-1">Lokasi</p>
                        <p class="font-bold text-navy-900"><?php echo htmlspecialchars($landing['location']); ?></p>
                    </div>
                </div>
            </div>

            <aside class="bg-navy-900 text-white p-6 sm:p-8 border-t-4 border-gold-500">
                <h3 class="font-serif text-2xl mb-5">Kenapa Lot Ini Menarik</h3>
                <div class="space-y-4">
                    <?php foreach ($highlights as $highlight): ?>
                        <div class="flex gap-3">
                            <span class="mt-1 w-5 h-5 rounded-full bg-gold-500 text-navy-900 flex items-center justify-center text-xs font-bold shrink-0">&#10003;</span>
                            <p class="text-slate-200 leading-relaxed"><?php echo htmlspecialchars($highlight); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </aside>
        </div>
    </section>

    <section class="bg-white text-slate-900 py-14 md:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="text-center max-w-3xl mx-auto mb-12">
                <p class="text-gold-700 uppercase tracking-[0.28em] text-xs font-bold mb-3">Fasiliti Berdekatan</p>
                <h2 class="font-serif text-3xl sm:text-4xl md:text-5xl text-navy-900">Semua Dekat, Tak Perlu Pening</h2>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($facilities as $facility): ?>
                    <div class="border border-slate-200 bg-[#f7f5ef] p-5 flex items-center gap-4">
                        <div class="w-11 h-11 rounded-full bg-gold-500/15 text-gold-700 flex items-center justify-center font-bold">&bull;</div>
                        <p class="font-semibold text-navy-900"><?php echo htmlspecialchars($facility); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="bg-navy-900 text-white py-14 md:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 grid lg:grid-cols-[0.8fr_1fr] gap-10 items-center">
            <div>
                <p class="text-gold-500 uppercase tracking-[0.28em] text-xs font-bold mb-3">Lokasi Strategik</p>
                <h2 class="font-serif text-3xl sm:text-4xl md:text-5xl leading-tight mb-6">Kg. Selupoh, Tuaran</h2>
                <p class="text-slate-300 leading-relaxed text-lg"><?php echo htmlspecialchars($landing['map_notes']); ?></p>
            </div>
            <div class="bg-slate-950/60 border border-slate-800 p-6 sm:p-8">
                <h3 class="font-serif text-2xl mb-4"><?php echo htmlspecialchars($landing['cta_heading']); ?></h3>
                <p class="text-slate-300 mb-6">Harga hartanah sentiasa naik. Jika anda mahu semak lokasi, ketersediaan lot, atau langkah seterusnya, hubungi sekarang untuk maklumat lanjut.</p>
                <a href="<?php echo htmlspecialchars($whatsappUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="inline-flex w-full sm:w-auto items-center justify-center bg-gold-500 hover:bg-gold-400 text-navy-900 px-7 py-3 text-xs font-bold uppercase tracking-wider transition-colors"><?php echo htmlspecialchars($landing['cta_text'] ?: 'Hubungi Sekarang'); ?></a>
            </div>
        </div>
    </section>
</main>

<div id="landing-info-modal" class="fixed inset-0 z-[100] hidden bg-slate-950/90 p-4 sm:p-6" aria-hidden="true">
    <div class="absolute inset-0" data-full-info-close></div>
    <div class="relative mx-auto flex h-full max-w-5xl flex-col">
        <div class="mb-3 flex items-center justify-between gap-3 text-white">
            <p class="font-serif text-xl sm:text-2xl"><?php echo htmlspecialchars($landing['page_title']); ?></p>
            <button type="button" data-full-info-close class="border border-white/30 px-4 py-2 text-xs font-bold uppercase tracking-wider hover:border-gold-500 hover:text-gold-500">Close</button>
        </div>
        <div class="min-h-0 flex-1 overflow-auto bg-slate-950 border border-slate-700 p-2">
            <img src="<?php echo htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($landing['page_title']); ?> full advertisement" class="mx-auto h-auto w-full max-w-none sm:max-w-full object-contain">
        </div>
    </div>
</div>

<script>
(function () {
    const modal = document.getElementById('landing-info-modal');
    if (!modal) return;
    const openers = document.querySelectorAll('[data-full-info-trigger]');
    const closers = modal.querySelectorAll('[data-full-info-close]');
    const openModal = function () {
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
    };
    const closeModal = function () {
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
    };
    openers.forEach(function (button) { button.addEventListener('click', openModal); });
    closers.forEach(function (button) { button.addEventListener('click', closeModal); });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
    });
})();
</script>
<?php include 'includes/footer.php'; ?>
