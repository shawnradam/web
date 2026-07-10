<?php
// admin/dashboard.php
require_once 'auth_check.php';
require_once 'db_connect.php';

function dashboard_count(PDO $pdo, string $sql, array $params = []): int
{
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    } catch (Throwable $e) {
        return 0;
    }
}

function dashboard_rows(PDO $pdo, string $sql, array $params = []): array
{
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return [];
    }
}

function dashboard_pct_change(int $current, int $previous): string
{
    if ($previous <= 0) {
        return $current > 0 ? '+100%' : '0%';
    }

    $change = (($current - $previous) / $previous) * 100;
    return ($change >= 0 ? '+' : '') . number_format($change, 1) . '%';
}

function dashboard_trend_class(string $change): string
{
    return strpos($change, '-') === 0 ? 'text-red-500' : 'text-emerald-600';
}

function dashboard_initials(string $name): string
{
    $name = trim($name);
    if ($name === '') {
        return 'SR';
    }

    $parts = preg_split('/\s+/', $name);
    if (count($parts) >= 2) {
        return strtoupper(substr($parts[0], 0, 1) . substr(end($parts), 0, 1));
    }

    return strtoupper(substr($parts[0], 0, 2));
}

$today = date('Y-m-d');
$sevenDaysAgo = date('Y-m-d', strtotime('-6 days'));
$previousSevenStart = date('Y-m-d', strtotime('-13 days'));
$previousSevenEnd = date('Y-m-d', strtotime('-7 days'));
$thirtyDaysAgo = date('Y-m-d', strtotime('-29 days'));

$pageViews = dashboard_count($pdo, "SELECT COUNT(*) FROM page_views WHERE DATE(created_at) >= ?", [$sevenDaysAgo]);
$pageViewsPrev = dashboard_count($pdo, "SELECT COUNT(*) FROM page_views WHERE DATE(created_at) BETWEEN ? AND ?", [$previousSevenStart, $previousSevenEnd]);
$uniqueVisitors = dashboard_count($pdo, "SELECT COUNT(DISTINCT visitor_hash) FROM page_views WHERE DATE(created_at) >= ?", [$sevenDaysAgo]);
$newsletterSubscribers = dashboard_count($pdo, "SELECT COUNT(*) FROM newsletter_subscribers");
$newsletterNew = dashboard_count($pdo, "SELECT COUNT(*) FROM newsletter_subscribers WHERE DATE(subscribed_at) >= ?", [$sevenDaysAgo]);
$newsletterPrev = dashboard_count($pdo, "SELECT COUNT(*) FROM newsletter_subscribers WHERE DATE(subscribed_at) BETWEEN ? AND ?", [$previousSevenStart, $previousSevenEnd]);
$briefingRequests = dashboard_count($pdo, "SELECT COUNT(*) FROM briefing_submissions WHERE DATE(created_at) >= ?", [$thirtyDaysAgo]);
$briefingPrev = dashboard_count($pdo, "SELECT COUNT(*) FROM briefing_submissions WHERE DATE(created_at) BETWEEN DATE_SUB(?, INTERVAL 30 DAY) AND DATE_SUB(?, INTERVAL 1 DAY)", [$thirtyDaysAgo, $thirtyDaysAgo]);
$unreadFeedback = dashboard_count($pdo, "SELECT COUNT(*) FROM feedback_submissions WHERE is_read = 0");
$feedbackLastWeek = dashboard_count($pdo, "SELECT COUNT(*) FROM feedback_submissions WHERE DATE(created_at) >= ?", [$sevenDaysAgo]);
$totalPosts = dashboard_count($pdo, "SELECT COUNT(*) FROM posts WHERE status = 'published'");
$draftPosts = dashboard_count($pdo, "SELECT COUNT(*) FROM posts WHERE status = 'draft'");
$securityEvents = dashboard_count($pdo, "SELECT COUNT(*) FROM login_attempts WHERE success = 0 AND DATE(attempt_time) >= ?", [$sevenDaysAgo]);
$securityPrev = dashboard_count($pdo, "SELECT COUNT(*) FROM login_attempts WHERE success = 0 AND DATE(attempt_time) BETWEEN ? AND ?", [$previousSevenStart, $previousSevenEnd]);

$trafficRows = dashboard_rows($pdo, "
    SELECT DATE(created_at) AS date, COUNT(*) AS views, COUNT(DISTINCT visitor_hash) AS visitors
    FROM page_views
    WHERE DATE(created_at) >= ?
    GROUP BY DATE(created_at)
    ORDER BY date ASC
", [$sevenDaysAgo]);
$trafficByDate = [];
foreach ($trafficRows as $row) {
    $trafficByDate[$row['date']] = $row;
}
$trafficLabels = [];
$pageViewSeries = [];
$visitorSeries = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $trafficLabels[] = date('M j', strtotime($date));
    $pageViewSeries[] = (int) ($trafficByDate[$date]['views'] ?? 0);
    $visitorSeries[] = (int) ($trafficByDate[$date]['visitors'] ?? 0);
}

$newsletterRows = dashboard_rows($pdo, "
    SELECT YEARWEEK(subscribed_at, 1) AS week_key, MIN(DATE(subscribed_at)) AS week_start, COUNT(*) AS subscribers
    FROM newsletter_subscribers
    WHERE DATE(subscribed_at) >= DATE_SUB(CURDATE(), INTERVAL 8 WEEK)
    GROUP BY YEARWEEK(subscribed_at, 1)
    ORDER BY week_key ASC
");
$newsletterLabels = [];
$newsletterNewSeries = [];
$newsletterTotalSeries = [];
$runningNewsletterTotal = dashboard_count($pdo, "SELECT COUNT(*) FROM newsletter_subscribers WHERE DATE(subscribed_at) < DATE_SUB(CURDATE(), INTERVAL 8 WEEK)");
foreach ($newsletterRows as $row) {
    $runningNewsletterTotal += (int) $row['subscribers'];
    $newsletterLabels[] = date('M j', strtotime($row['week_start']));
    $newsletterNewSeries[] = (int) $row['subscribers'];
    $newsletterTotalSeries[] = $runningNewsletterTotal;
}
if (empty($newsletterLabels)) {
    $newsletterLabels = ['No data'];
    $newsletterNewSeries = [0];
    $newsletterTotalSeries = [$newsletterSubscribers];
}

$latestBriefings = dashboard_rows($pdo, "
    SELECT name, email, pillar AS subject, created_at, is_read, 'Briefing' AS type
    FROM briefing_submissions
    ORDER BY created_at DESC
    LIMIT 5
");
$latestFeedback = dashboard_rows($pdo, "
    SELECT name, email, 'Feedback' AS subject, created_at, is_read, 'Feedback' AS type
    FROM feedback_submissions
    ORDER BY created_at DESC
    LIMIT 5
");
$latestEnquiries = array_merge($latestBriefings, $latestFeedback);
usort($latestEnquiries, function ($a, $b) {
    return strtotime($b['created_at']) <=> strtotime($a['created_at']);
});
$latestEnquiries = array_slice($latestEnquiries, 0, 6);

$popularPages = dashboard_rows($pdo, "
    SELECT page_url, COUNT(*) AS views
    FROM page_views
    WHERE DATE(created_at) >= ?
    GROUP BY page_url
    ORDER BY views DESC
    LIMIT 5
", [$thirtyDaysAgo]);

$topPosts = dashboard_rows($pdo, "
    SELECT title, slug, views
    FROM posts
    WHERE status = 'published'
    ORDER BY views DESC, updated_at DESC
    LIMIT 5
");

$mailTransport = 'PHP mail()';
$mailStatus = 'Server dependent';
try {
    require_once __DIR__ . '/config/email.config.php';
    $mailTransport = USE_SMTP ? 'SMTP' : 'PHP mail()';
    $mailStatus = USE_SMTP ? (SMTP_USERNAME !== '' ? 'Configured' : 'Needs credentials') : 'Enabled in code';
} catch (Throwable $e) {}

$cards = [
    [
        'label' => 'Page Views',
        'value' => number_format($pageViews),
        'meta' => number_format($uniqueVisitors) . ' unique visitors',
        'change' => dashboard_pct_change($pageViews, $pageViewsPrev),
        'icon' => 'chart',
    ],
    [
        'label' => 'Newsletter Subscribers',
        'value' => number_format($newsletterSubscribers),
        'meta' => number_format($newsletterNew) . ' new this week',
        'change' => dashboard_pct_change($newsletterNew, $newsletterPrev),
        'icon' => 'users',
    ],
    [
        'label' => 'Briefing Requests',
        'value' => number_format($briefingRequests),
        'meta' => 'last 30 days',
        'change' => dashboard_pct_change($briefingRequests, $briefingPrev),
        'icon' => 'document',
    ],
    [
        'label' => 'Unread Feedback',
        'value' => number_format($unreadFeedback),
        'meta' => number_format($feedbackLastWeek) . ' total this week',
        'change' => $unreadFeedback > 0 ? 'Needs review' : 'Clear',
        'icon' => 'mail',
    ],
    [
        'label' => 'Blog Posts',
        'value' => number_format($totalPosts),
        'meta' => number_format($draftPosts) . ' drafts',
        'change' => 'Published',
        'icon' => 'post',
    ],
    [
        'label' => 'Security Events',
        'value' => number_format($securityEvents),
        'meta' => 'failed logins this week',
        'change' => dashboard_pct_change($securityEvents, $securityPrev),
        'icon' => 'shield',
    ],
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php $pageTitle = 'Admin Dashboard'; ?>
    <?php include 'includes/admin_head.php'; ?>
</head>

<body class="bg-slate-950 text-slate-300 antialiased">
    <div class="flex min-h-screen relative" x-data="{ 
            sidebarOpen: false, 
            collapsed: (function(){ try { return localStorage.getItem('sidebarCollapsed') === 'true' } catch(e){ return false } })() 
        }" x-init="$watch('collapsed', val => { try { localStorage.setItem('sidebarCollapsed', val) } catch(e){} })">

        <?php include 'dashboard_sidebar_partial.php'; ?>

        <main class="flex-1 min-h-screen md:ml-64 transition-all duration-300 ease-in-out min-w-0" :class="collapsed ? 'md:!ml-20' : 'md:!ml-64'">
            <div class="sticky top-0 z-30 bg-slate-900/95 backdrop-blur border-b border-slate-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 min-h-16 py-3 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <button @click="sidebarOpen = !sidebarOpen" class="md:hidden w-10 h-10 inline-flex items-center justify-center border border-slate-700 text-slate-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                        </button>
                        <div>
                            <h1 class="font-serif text-xl sm:text-2xl text-white leading-none">Admin Dashboard</h1>
                            <p class="text-xs sm:text-sm text-slate-400 mt-1">Shawn Radam</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                        <div class="hidden sm:flex items-center gap-2 border border-slate-700 px-3 py-2 text-sm text-slate-300 bg-slate-950/70">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z" /></svg>
                            <?php echo date('M j', strtotime($sevenDaysAgo)); ?> - <?php echo date('M j, Y'); ?>
                        </div>
                        <a href="../index.php" target="_blank" class="hidden sm:inline-flex items-center gap-2 border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm font-medium text-slate-300 hover:border-gold-500 hover:text-gold-500 transition-colors">View Site</a>
                        <div class="w-10 h-10 rounded-full bg-navy-900 text-gold-500 flex items-center justify-center text-sm font-bold border border-gold-500/40">SR</div>
                    </div>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-3 sm:px-5 lg:px-8 py-4 sm:py-6 lg:py-8 space-y-4 sm:space-y-6">
                <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3 sm:gap-4 lg:gap-5">
                    <?php foreach ($cards as $card): ?>
                        <?php $changeClass = in_array($card['change'], ['Needs review'], true) ? 'text-amber-600' : (in_array($card['change'], ['Clear', 'Published'], true) ? 'text-emerald-600' : dashboard_trend_class($card['change'])); ?>
                        <article class="bg-slate-900 border border-slate-800 p-4 sm:p-5 shadow-sm hover:border-gold-500/40 transition-colors min-w-0">
                            <div class="flex items-start justify-between gap-3 sm:gap-4 mb-4 sm:mb-5">
                                <div class="w-11 h-11 bg-gold-500/10 text-gold-700 flex items-center justify-center">
                                    <?php if ($card['icon'] === 'chart'): ?>
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19V5m0 14h16M8 16l3-4 3 2 4-7" /></svg>
                                    <?php elseif ($card['icon'] === 'users'): ?>
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m4-4a4 4 0 11-8 0 4 4 0 018 0zm8 0a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    <?php elseif ($card['icon'] === 'document'): ?>
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 3h7l5 5v13H7a2 2 0 01-2-2V5a2 2 0 012-2zm7 0v6h6" /></svg>
                                    <?php elseif ($card['icon'] === 'mail'): ?>
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l9 6 9-6M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z" /></svg>
                                    <?php elseif ($card['icon'] === 'shield'): ?>
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3l7 4v5c0 5-3 8-7 9-4-1-7-4-7-9V7l7-4z" /></svg>
                                    <?php else: ?>
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 11h10M7 15h6M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z" /></svg>
                                    <?php endif; ?>
                                </div>
                                <div class="h-8 w-20 flex items-end gap-1 opacity-80" aria-hidden="true">
                                    <span class="flex-1 bg-gold-500/35" style="height: 35%"></span>
                                    <span class="flex-1 bg-gold-500/55" style="height: 52%"></span>
                                    <span class="flex-1 bg-gold-500/75" style="height: 42%"></span>
                                    <span class="flex-1 bg-gold-500" style="height: 68%"></span>
                                </div>
                            </div>
                            <p class="text-sm font-medium text-slate-400 mb-1"><?php echo htmlspecialchars($card['label']); ?></p>
                            <div class="flex items-end justify-between gap-4">
                                <p class="text-3xl font-semibold text-white tracking-tight"><?php echo htmlspecialchars($card['value']); ?></p>
                                <p class="text-xs font-semibold <?php echo $changeClass; ?>"><?php echo htmlspecialchars($card['change']); ?></p>
                            </div>
                            <p class="text-xs text-slate-500 mt-2"><?php echo htmlspecialchars($card['meta']); ?></p>
                        </article>
                    <?php endforeach; ?>
                </section>

                <section class="bg-slate-900 border border-slate-800 shadow-sm">
                    <div class="px-4 sm:px-6 py-4 sm:py-5 border-b border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h2 class="font-serif text-xl text-white">Website Traffic</h2>
                            <p class="text-sm text-slate-500">Page views and unique visitors over the last 7 days.</p>
                        </div>
                        <span class="text-xs font-semibold text-slate-500 border border-slate-700 px-3 py-1.5 bg-slate-950/70">Daily</span>
                    </div>
                    <div class="p-3 sm:p-5 lg:p-6 h-[260px] sm:h-[315px] lg:h-[330px]"><canvas id="trafficChart"></canvas></div>
                </section>

                <section class="grid grid-cols-1 xl:grid-cols-[1.2fr_0.8fr] gap-4 sm:gap-6">
                    <article class="bg-slate-900 border border-slate-800 shadow-sm">
                        <div class="px-4 sm:px-6 py-4 sm:py-5 border-b border-slate-800 flex items-center justify-between">
                            <div>
                                <h2 class="font-serif text-xl text-white">Newsletter Growth</h2>
                                <p class="text-sm text-slate-500">New subscribers compared with total audience.</p>
                            </div>
                            <span class="text-xs font-semibold text-slate-500 border border-slate-700 px-3 py-1.5 bg-slate-950/70">Weekly</span>
                        </div>
                        <div class="p-3 sm:p-5 lg:p-6 h-[250px] sm:h-[300px] lg:h-[315px]"><canvas id="newsletterChart"></canvas></div>
                    </article>

                    <article class="bg-slate-900 border border-slate-800 shadow-sm">
                        <div class="px-4 sm:px-6 py-4 sm:py-5 border-b border-slate-800 flex items-center justify-between">
                            <div>
                                <h2 class="font-serif text-xl text-white">Latest Enquiries</h2>
                                <p class="text-sm text-slate-500">Briefing and feedback activity.</p>
                            </div>
                            <a href="feedback_manage.php" class="text-sm font-semibold text-gold-700 hover:text-navy-900">View</a>
                        </div>
                        <div class="divide-y divide-slate-800">
                            <?php if (empty($latestEnquiries)): ?>
                                <div class="p-6 text-sm text-slate-500">No enquiries yet.</div>
                            <?php else: ?>
                                <?php foreach ($latestEnquiries as $item): ?>
                                    <div class="p-4 sm:px-6 flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-full bg-slate-800 text-slate-300 flex items-center justify-center text-xs font-bold border border-slate-700"><?php echo htmlspecialchars(dashboard_initials($item['name'])); ?></div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-semibold text-white truncate"><?php echo htmlspecialchars($item['name']); ?></p>
                                            <p class="text-xs text-slate-500 truncate"><?php echo htmlspecialchars($item['email']); ?></p>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <p class="text-xs font-semibold <?php echo empty($item['is_read']) ? 'text-gold-700' : 'text-emerald-600'; ?>"><?php echo empty($item['is_read']) ? 'New' : 'Read'; ?></p>
                                            <p class="text-xs text-slate-400"><?php echo date('M j', strtotime($item['created_at'])); ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </article>
                </section>

                <section class="grid grid-cols-1 xl:grid-cols-2 gap-4 sm:gap-6">
                    <article class="bg-slate-900 border border-slate-800 shadow-sm">
                        <div class="px-4 sm:px-6 py-4 sm:py-5 border-b border-slate-800 flex items-center justify-between">
                            <h2 class="font-serif text-xl text-white">Popular Pages</h2>
                            <span class="text-xs text-slate-400">Last 30 days</span>
                        </div>
                        <div class="p-4 sm:p-6 space-y-3">
                            <?php if (empty($popularPages)): ?>
                                <p class="text-sm text-slate-500">No page view data yet.</p>
                            <?php else: ?>
                                <?php foreach ($popularPages as $index => $page): ?>
                                    <div class="flex items-center gap-4">
                                        <span class="w-7 h-7 rounded-full bg-gold-500/15 text-gold-800 flex items-center justify-center text-xs font-bold"><?php echo $index + 1; ?></span>
                                        <p class="flex-1 min-w-0 text-sm text-slate-300 truncate"><?php echo htmlspecialchars(parse_url($page['page_url'], PHP_URL_PATH) ?: $page['page_url']); ?></p>
                                        <p class="text-sm font-semibold text-white"><?php echo number_format((int) $page['views']); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </article>

                    <article class="bg-slate-900 border border-slate-800 shadow-sm">
                        <div class="px-4 sm:px-6 py-4 sm:py-5 border-b border-slate-800 flex items-center justify-between">
                            <h2 class="font-serif text-xl text-white">Mail Status</h2>
                            <span class="text-xs text-slate-400">Website forms</span>
                        </div>
                        <div class="p-4 sm:p-6 space-y-4">
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-sm text-slate-500">Transport</span>
                                <span class="text-sm font-semibold text-white"><?php echo htmlspecialchars($mailTransport); ?></span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-sm text-slate-500">Status</span>
                                <span class="text-sm font-semibold text-emerald-600"><?php echo htmlspecialchars($mailStatus); ?></span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-sm text-slate-500">Newsletter endpoint</span>
                                <span class="text-sm font-semibold text-emerald-600">Active</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-sm text-slate-500">Briefing endpoint</span>
                                <span class="text-sm font-semibold text-emerald-600">Active</span>
                            </div>
                            <a href="site_settings.php" class="mt-4 inline-flex w-full items-center justify-center border border-gold-500 text-gold-700 hover:bg-gold-500 hover:text-navy-900 px-4 py-3 text-sm font-semibold transition-colors">Review Site Settings</a>
                        </div>
                    </article>
                </section>

                <section class="bg-slate-900 border border-slate-800 shadow-sm">
                    <div class="px-4 sm:px-6 py-4 sm:py-5 border-b border-slate-800 flex items-center justify-between">
                        <h2 class="font-serif text-xl text-white">Top Blog Posts</h2>
                        <a href="posts.php" class="text-sm font-semibold text-gold-700 hover:text-navy-900">Manage posts</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[560px] text-sm">
                            <thead class="bg-slate-950/70 text-slate-500 border-b border-slate-800">
                                <tr>
                                    <th class="text-left px-5 py-3 font-semibold">Post</th>
                                    <th class="text-left px-5 py-3 font-semibold">Slug</th>
                                    <th class="text-right px-5 py-3 font-semibold">Views</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800">
                                <?php if (empty($topPosts)): ?>
                                    <tr><td colspan="3" class="px-5 py-6 text-center text-slate-500">No published posts yet.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($topPosts as $post): ?>
                                        <tr class="hover:bg-slate-800/50">
                                            <td class="px-5 py-4 font-semibold text-white"><?php echo htmlspecialchars($post['title']); ?></td>
                                            <td class="px-5 py-4 text-slate-500"><?php echo htmlspecialchars($post['slug']); ?></td>
                                            <td class="px-5 py-4 text-right font-semibold text-white"><?php echo number_format((int) $post['views']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script>
        Chart.defaults.font.family = 'Inter, ui-sans-serif, system-ui, sans-serif';
        Chart.defaults.color = '#94a3b8';
        Chart.defaults.borderColor = '#334155';

        const trafficCanvas = document.getElementById('trafficChart');
        if (trafficCanvas) {
            new Chart(trafficCanvas, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($trafficLabels); ?>,
                    datasets: [
                        {
                            label: 'Page Views',
                            data: <?php echo json_encode($pageViewSeries); ?>,
                            borderColor: '#d4af37',
                            backgroundColor: 'rgba(212, 175, 55, 0.08)',
                            pointBackgroundColor: '#d4af37',
                            pointRadius: 3,
                            borderWidth: 2,
                            tension: 0.35,
                            fill: true
                        },
                        {
                            label: 'Unique Visitors',
                            data: <?php echo json_encode($visitorSeries); ?>,
                            borderColor: '#d4af37',
                            backgroundColor: 'rgba(212, 175, 55, 0.08)',
                            pointBackgroundColor: '#d4af37',
                            pointRadius: 3,
                            borderWidth: 2,
                            borderDash: [6, 4],
                            tension: 0.35,
                            fill: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { labels: { boxWidth: 10, usePointStyle: true } } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } }, x: { grid: { display: false } } }
                }
            });
        }

        const newsletterCanvas = document.getElementById('newsletterChart');
        if (newsletterCanvas) {
            new Chart(newsletterCanvas, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($newsletterLabels); ?>,
                    datasets: [
                        {
                            label: 'New Subscribers',
                            data: <?php echo json_encode($newsletterNewSeries); ?>,
                            backgroundColor: '#334155',
                            borderRadius: 4,
                            maxBarThickness: 42
                        },
                        {
                            label: 'Total Subscribers',
                            data: <?php echo json_encode($newsletterTotalSeries); ?>,
                            backgroundColor: '#d4af37',
                            borderRadius: 4,
                            maxBarThickness: 42
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { labels: { boxWidth: 10, usePointStyle: true } } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } }, x: { grid: { display: false } } }
                }
            });
        }
    </script>
</body>

</html>