<?php
require_once 'admin/db_connect.php';

$about = null;
$expertiseItems = [];
try {
    $stmt = $pdo->query("SELECT * FROM about_page ORDER BY id ASC LIMIT 1");
    $about = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("SELECT * FROM about_expertise_items WHERE is_active = 1 ORDER BY display_order ASC, id ASC");
    $expertiseItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $about = null;
    $expertiseItems = [];
}

$about = array_merge([
    'page_title' => 'About Shawn Radam',
    'page_subtitle' => 'Professional Advisory Services',
    'hero_label' => 'Personal Advisor',
    'profile_name' => 'Shawn Radam',
    'profile_title' => 'Personal Advisor',
    'portrait_url' => '',
    'intro_text' => 'Experienced professional providing expert advisory services in real estate, finance, and business development. Committed to helping clients achieve their goals through strategic guidance and personalized solutions.',
    'cta_heading' => 'Ready to discuss your goals?',
    'cta_button_text' => 'Get in Touch',
    'cta_button_link' => 'contact.php',
    'seo_title' => 'About | Shawn Radam',
    'seo_desc' => 'Learn more about Shawn Radam and his professional advisory background.',
    'is_published' => 1,
], $about ?: []);

if (empty($expertiseItems)) {
    $expertiseItems = [
        ['title' => 'Real Estate', 'description' => 'Property investment and development consulting', 'icon_key' => 'home', 'accent_color' => 'blue'],
        ['title' => 'Finance', 'description' => 'Financial planning and investment strategies', 'icon_key' => 'currency', 'accent_color' => 'green'],
        ['title' => 'Business', 'description' => 'Strategic business development and consulting', 'icon_key' => 'briefcase', 'accent_color' => 'purple'],
    ];
}

function about_icon_path($iconKey)
{
    $icons = [
        'home' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        'currency' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'briefcase' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        'chart' => 'M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z',
        'shield' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
    ];
    return $icons[$iconKey] ?? $icons['briefcase'];
}

$heroImage = !empty($about['portrait_url']) ? $about['portrait_url'] : 'assets/menu/home-mega-menu.png';
$storyImage = 'assets/menu/properties-mega-menu.png';
$financeImage = 'assets/menu/loans-financing-mega-menu.png';
$landImage = 'assets/menu/land-lot-mega-menu.png';
$contactImage = 'assets/menu/contact-mega-menu.png';

function about_asset_url($path)
{
    $path = (string) $path;
    if ($path === '' || preg_match('/^(https?:|data:|\/)/i', $path)) {
        return $path;
    }

    return function_exists('public_path') ? public_path($path) : $path;
}

$page_title = $about['seo_title'] ?: $about['page_title'];
$page_desc = $about['seo_desc'] ?: $about['intro_text'];
include 'includes/header.php';
include 'includes/navigation.php';
?>

<main class="bg-slate-950 text-slate-200">
    <section class="relative pt-10 md:pt-20 lg:pt-28 overflow-hidden bg-navy-900">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(212,175,55,0.14),transparent_32%),linear-gradient(135deg,rgba(10,14,39,0.98),rgba(2,6,23,0.98))]"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 grid lg:grid-cols-[1.05fr_0.95fr] gap-8 lg:gap-10 items-center lg:min-h-[620px] pb-12 md:pb-16">
            <div class="pt-3 pb-7 md:pt-8 md:pb-10 lg:pt-10 lg:pb-12 lg:col-start-1 lg:row-start-1">
                <p class="text-gold-500 uppercase tracking-[0.35em] text-xs font-bold mb-5"><?php echo htmlspecialchars($about['hero_label']); ?></p>
                <h1 class="font-serif text-4xl sm:text-5xl md:text-6xl lg:text-7xl leading-[1.02] lg:leading-[0.95] text-white max-w-3xl mb-6 md:mb-7"><?php echo htmlspecialchars($about['page_title']); ?></h1>
                <p class="text-base sm:text-lg md:text-xl text-slate-300 leading-relaxed max-w-2xl mb-7 md:mb-9"><?php echo htmlspecialchars($about['page_subtitle']); ?></p>

            </div>
            <div class="relative lg:self-end max-w-xl mx-auto lg:max-w-none w-full lg:col-start-2 lg:row-start-1 lg:row-span-2">
                <div class="aspect-[16/11] sm:aspect-[4/3] lg:aspect-[4/5] lg:max-h-[590px] bg-slate-900 border border-slate-700 overflow-hidden shadow-2xl">
                    <img src="<?php echo htmlspecialchars(about_asset_url($heroImage), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($about['profile_name']); ?>" class="w-full h-full object-cover">
                </div>
                <div class="relative sm:absolute sm:-bottom-6 sm:left-6 sm:right-6 bg-slate-950/95 border border-gold-500/30 p-4 sm:p-5 shadow-xl mt-0">
                    <p class="text-gold-500 font-serif text-2xl mb-1"><?php echo htmlspecialchars($about['profile_name']); ?></p>
                    <p class="text-slate-400 text-sm uppercase tracking-wider"><?php echo htmlspecialchars($about['profile_title']); ?></p>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row sm:flex-wrap gap-3 sm:gap-4 lg:col-start-1 lg:row-start-2 lg:self-start">
                <a href="<?php echo htmlspecialchars(lang_url($about['cta_button_link'] ?: 'contact.php')); ?>" class="inline-flex items-center justify-center gap-3 bg-gold-500 hover:bg-gold-400 text-navy-900 px-5 sm:px-6 py-3 text-xs sm:text-sm font-bold uppercase tracking-wider transition-colors">
                    <?php echo htmlspecialchars($about['cta_button_text']); ?>
                    <span aria-hidden="true">&rarr;</span>
                </a>
                <a href="<?php echo htmlspecialchars(lang_url('properties.php')); ?>" class="inline-flex items-center justify-center gap-3 border border-slate-600 hover:border-gold-500 text-white px-5 sm:px-6 py-3 text-xs sm:text-sm font-bold uppercase tracking-wider transition-colors">
                    View Advisory Areas
                </a>
            </div></div>
    </section>

    <section class="bg-[#f7f5ef] text-slate-900 py-14 md:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 grid lg:grid-cols-[0.85fr_1.15fr] gap-14 items-center">
            <div class="relative max-w-sm md:max-w-md mx-auto md:mx-0">
                <div class="aspect-[4/5] overflow-hidden border-8 border-white shadow-xl bg-slate-200 rotate-[-2deg]">
                    <img src="<?php echo htmlspecialchars(about_asset_url($storyImage), ENT_QUOTES, 'UTF-8'); ?>" alt="Property advisory" class="w-full h-full object-cover">
                </div>
                <div class="absolute -bottom-7 -right-7 w-28 h-28 border border-gold-500/40 rounded-full hidden md:block"></div>
            </div>
            <div>
                <p class="text-gold-700 uppercase tracking-[0.28em] text-xs font-bold mb-4">A Legacy Of Advisory Discipline</p>
                <h2 class="font-serif text-3xl sm:text-4xl md:text-5xl text-navy-900 leading-tight mb-6">Guidance Built Around Property, Financing, And Long-Term Decisions</h2>
                <p class="text-slate-700 leading-relaxed text-base md:text-lg whitespace-pre-line mb-7"><?php echo htmlspecialchars($about['intro_text']); ?></p>
                <a href="<?php echo htmlspecialchars(lang_url('contact.php')); ?>" class="inline-flex items-center bg-navy-900 hover:bg-slate-800 text-white px-5 py-3 text-xs font-bold uppercase tracking-wider transition-colors">Learn More About Us</a>
            </div>
        </div>
    </section>

    <section class="bg-white text-slate-900 py-14 md:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 grid lg:grid-cols-2 gap-16 items-center">
            <div>
                <h2 class="font-serif text-3xl sm:text-4xl md:text-5xl text-navy-900 leading-tight mb-6">Navigating Asset And Financing Challenges With Integrity</h2>
                <p class="text-slate-600 leading-relaxed mb-8">Every case begins with context: land status, buyer readiness, financing structure, risk tolerance, and timing. The advisory process is designed to make each step clear before clients commit.</p>
                <div class="aspect-[5/3] overflow-hidden bg-slate-100">
                    <img src="<?php echo htmlspecialchars(about_asset_url($financeImage), ENT_QUOTES, 'UTF-8'); ?>" alt="Loans financing advisory" class="w-full h-full object-cover">
                </div>
            </div>
            <div class="space-y-8">
                <div class="aspect-[16/11] md:aspect-[4/5] overflow-hidden bg-slate-100 max-w-md mx-auto">
                    <img src="<?php echo htmlspecialchars(about_asset_url($landImage), ENT_QUOTES, 'UTF-8'); ?>" alt="Land advisory" class="w-full h-full object-cover">
                </div>
                <p class="text-slate-600 leading-relaxed max-w-md mx-auto">From land lots to loan eligibility, the focus stays on practical decisions, transparent numbers, and advisory support that fits Sabah market realities.</p>
            </div>
        </div>
    </section>

    <section class="bg-navy-900 py-10 md:py-12 border-y border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 grid md:grid-cols-3 gap-8">
            <div class="border-l border-gold-500/40 pl-6">
                <p class="text-gold-500 font-serif text-4xl mb-2">100%</p>
                <h3 class="text-white font-bold mb-2">Client Focus</h3>
                <p class="text-slate-400 text-sm">Clear recommendations shaped around client objectives.</p>
            </div>
            <div class="border-l border-gold-500/40 pl-6">
                <p class="text-gold-500 font-serif text-4xl mb-2">35+</p>
                <h3 class="text-white font-bold mb-2">Advisory Touchpoints</h3>
                <p class="text-slate-400 text-sm">Practical checkpoints from first enquiry to next action.</p>
            </div>
            <div class="border-l border-gold-500/40 pl-6">
                <p class="text-gold-500 font-serif text-4xl mb-2">50+</p>
                <h3 class="text-white font-bold mb-2">Structured Reviews</h3>
                <p class="text-slate-400 text-sm">Property and financing situations reviewed with care.</p>
            </div>
        </div>
    </section>

    <section class="bg-[#f7f5ef] text-slate-900 py-14 md:py-20">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-12">
                <p class="text-gold-700 uppercase tracking-[0.28em] text-xs font-bold mb-3">Advisory Areas</p>
                <h2 class="font-serif text-3xl sm:text-4xl md:text-5xl text-navy-900">Solutions Tailored To Your Needs</h2>
            </div>
            <div class="border-y border-slate-300 bg-white">
                <?php foreach ($expertiseItems as $index => $item): ?>
                    <div class="grid md:grid-cols-[72px_150px_1fr_52px] lg:grid-cols-[90px_170px_1fr_70px] items-center border-b border-slate-200 last:border-b-0 <?php echo $index === 1 ? 'bg-navy-900 text-white' : ''; ?>">
                        <div class="px-4 sm:px-6 py-4 md:py-5 text-sm font-bold <?php echo $index === 1 ? 'text-gold-500' : 'text-gold-700'; ?>"><?php echo str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT); ?></div>
                        <div class="h-44 sm:h-52 md:h-28 overflow-hidden bg-slate-200">
                            <img src="<?php echo htmlspecialchars(about_asset_url($index % 3 === 0 ? $storyImage : ($index % 3 === 1 ? $financeImage : $landImage)), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="w-full h-full object-cover">
                        </div>
                        <div class="px-4 sm:px-6 py-5 md:py-6">
                            <h3 class="font-serif text-2xl <?php echo $index === 1 ? 'text-white' : 'text-navy-900'; ?>"><?php echo htmlspecialchars($item['title']); ?></h3>
                            <p class="mt-2 text-sm <?php echo $index === 1 ? 'text-slate-300' : 'text-slate-600'; ?>"><?php echo htmlspecialchars($item['description']); ?></p>
                        </div>
                        <div class="px-4 sm:px-6 pb-5 md:pb-0 md:text-right text-sm <?php echo $index === 1 ? 'text-gold-500' : 'text-slate-500'; ?>">&rarr;</div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="bg-white text-slate-900 py-14 md:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="grid lg:grid-cols-[0.95fr_1.05fr] bg-navy-900 text-white overflow-hidden shadow-2xl">
                <div class="relative min-h-[360px] sm:min-h-[420px] lg:min-h-[520px] bg-slate-950 bg-cover bg-center" style="background-image: linear-gradient(rgba(2,6,23,0.25), rgba(2,6,23,0.25)), url('<?php echo htmlspecialchars(about_asset_url($contactImage), ENT_QUOTES, 'UTF-8'); ?>');">
                    <div class="absolute inset-0 bg-gradient-to-t from-navy-900 via-navy-900/45 to-transparent lg:bg-gradient-to-r lg:from-navy-900 lg:via-navy-900/55 lg:to-transparent"></div>
                    <div class="relative z-10 p-6 sm:p-8 md:p-10 lg:p-12 h-full flex flex-col justify-end max-w-xl">
                        <p class="text-gold-500 uppercase tracking-[0.28em] text-xs font-bold mb-4">Consultation</p>
                        <h2 class="font-serif text-3xl sm:text-4xl md:text-5xl leading-tight mb-5">Empowering You With Practical Advisory</h2>
                        <p class="text-slate-200 leading-relaxed">Move forward with clearer numbers, cleaner documentation, and guidance that keeps the next decision visible.</p>
                    </div>
                </div>
                <div class="bg-gold-500 text-navy-900 p-6 sm:p-8 md:p-10 lg:p-12">
                    <div class="max-w-xl lg:max-w-none mx-auto">
                        <p class="uppercase tracking-[0.25em] text-xs font-bold text-navy-900/70 mb-3">Common Concerns</p>
                        <h2 class="font-serif text-3xl sm:text-4xl md:text-5xl leading-tight mb-6"><?php echo htmlspecialchars($about['cta_heading']); ?></h2>
                        <p class="text-navy-900/75 leading-relaxed mb-8">Most enquiries begin with uncertainty around documents, loan readiness, land status, or the next practical step. These are the areas usually reviewed before any recommendation is made.</p>
                        <div class="grid gap-3 mb-8">
                            <div class="border border-navy-900/25 bg-white/15 px-4 py-3">
                                <h3 class="font-bold text-sm uppercase tracking-wider mb-1">Property Direction</h3>
                                <p class="text-sm text-navy-900/75">Clarify whether a property move fits your budget, timing, and long-term plan.</p>
                            </div>
                            <div class="border border-navy-900/25 bg-white/15 px-4 py-3">
                                <h3 class="font-bold text-sm uppercase tracking-wider mb-1">Loan Readiness</h3>
                                <p class="text-sm text-navy-900/75">Review income, commitments, margin expectations, and likely financing options.</p>
                            </div>
                            <div class="border border-navy-900/25 bg-white/15 px-4 py-3">
                                <h3 class="font-bold text-sm uppercase tracking-wider mb-1">Land Lot Checks</h3>
                                <p class="text-sm text-navy-900/75">Identify the important questions before dealing with land status, access, and documents.</p>
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-3">
                            <a href="<?php echo htmlspecialchars(lang_url('contact.php')); ?>" class="inline-flex items-center justify-center bg-navy-900 hover:bg-slate-900 text-white px-5 py-3 text-xs font-bold uppercase tracking-wider transition-colors">Open Contact Page</a>
                            <a href="<?php echo htmlspecialchars(lang_url('properties.php')); ?>" class="inline-flex items-center justify-center border border-navy-900/40 hover:border-navy-900 px-5 py-3 text-xs font-bold uppercase tracking-wider transition-colors">View Advisory Areas</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-[#f7f5ef] text-slate-900 py-14 md:py-20">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 grid lg:grid-cols-[0.75fr_1.25fr] gap-12 items-center">
            <div class="hidden lg:block aspect-[3/4] overflow-hidden bg-slate-200">
                <img src="<?php echo htmlspecialchars(about_asset_url($heroImage), ENT_QUOTES, 'UTF-8'); ?>" alt="Advisor portrait" class="w-full h-full object-cover">
            </div>
            <div>
                <h2 class="font-serif text-3xl sm:text-4xl md:text-5xl text-navy-900 mb-6 md:mb-8">Frequently Asked Questions</h2>
                <div class="divide-y divide-slate-300 border-y border-slate-300" data-faq-accordion>
                    <?php foreach ([
                        'Can I request a specific advisory review?',
                        'How soon can property or loan options be assessed?',
                        'Do you handle both land and financing enquiries?',
                        'What should I prepare before a consultation?',
                        'Can advisory support be done remotely?'
                    ] as $question): ?>
                        <details class="group py-5">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-5 text-navy-900 font-medium">
                                <?php echo htmlspecialchars($question); ?>
                                <span class="text-gold-700 group-open:rotate-45 transition-transform">+</span>
                            </summary>
                            <p class="pt-3 text-slate-600 text-sm leading-relaxed">Send the key details first. The advisory process will identify what is available, what is missing, and what the next practical step should be.</p>
                        </details>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
</main>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-faq-accordion]').forEach(function (accordion) {
            accordion.querySelectorAll('details').forEach(function (item) {
                item.addEventListener('toggle', function () {
                    if (!item.open) return;
                    accordion.querySelectorAll('details[open]').forEach(function (other) {
                        if (other !== item) other.open = false;
                    });
                });
            });
        });
    });
</script>
<?php include 'includes/footer.php'; ?>
