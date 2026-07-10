<?php
// post-view.php
require_once 'admin/db_connect.php';
require_once 'includes/lang.php';
require_once 'includes/analytics_tracking.php'; // Track page views

// Get Slug
$slug = $_GET['slug'] ?? '';
if (!$slug) {
    header("Location: " . lang_url('blog.php'));
    exit;
}

// Fetch Post
$sql = "SELECT p.*, c.name as category_name, c.slug as category_slug, u.username as author_name 
        FROM posts p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN users u ON p.author_id = u.id 
        WHERE p.slug = :slug AND p.status = 'published' 
        LIMIT 1";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':slug' => $slug]);
    $post = $stmt->fetch();

    if (!$post) {
        // 404 logic
        header("HTTP/1.0 404 Not Found");
        die("Article not found.");
    }

    // View Counter Logic (Unique Session)
    session_start();
    $view_key = 'viewed_post_' . $post['id'];
    if (!isset($_SESSION[$view_key])) {
        $updateSql = "UPDATE posts SET views = views + 1 WHERE id = :id";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([':id' => $post['id']]);

        $_SESSION['view_key'] = true;
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// SEO Variables Override for Header (Assuming header.php can use them)
// We might need to slightly modify header.php in a real scenario, 
// for MVP we rely on the implementation below or injected styles.
$page_title = $post['seo_title'] ?: $post['title'];
$page_desc = $post['seo_desc'] ?: $post['summary'];
$nofollow_meta = $post['is_nofollow'] ? '<meta name="robots" content="nofollow">' : '';
$meta_image = $post['image_url'] ? (strpos($post['image_url'], 'http') === 0 ? $post['image_url'] : SITE_URL . '/' . ltrim($post['image_url'], '/')) : '';

include 'includes/header.php';
include 'includes/navigation.php';
?>

<!-- Carousel Styles and Script -->
<style>
    /* Carousel Items - Only show active */
    .carousel-item {
        display: none;
    }

    .carousel-item.active {
        display: block;
    }

    /* Carousel Dots */
    .carousel-dot.active {
        background-color: #d4af37;
    }

    /* Popup Overlay */
    .popup-overlay {
        pointer-events: none;
    }

    .carousel-item:hover .popup-overlay {
        pointer-events: auto;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const carousel = document.getElementById('relatedCarousel');
        if (!carousel) return;

        const items = carousel.querySelectorAll('.carousel-item');
        const dots = carousel.querySelectorAll('.carousel-dot');
        let currentIndex = 0;
        let autoSlideInterval;
        let isPaused = false;

        function showSlide(index) {
            // Hide all items
            items.forEach(item => item.classList.remove('active'));
            dots.forEach(dot => dot.classList.remove('active'));

            // Show current item
            if (items[index]) {
                items[index].classList.add('active');
                dots[index].classList.add('active');
            }
        }

        function nextSlide() {
            currentIndex = (currentIndex + 1) % items.length;
            showSlide(currentIndex);
        }

        function startAutoSlide() {
            autoSlideInterval = setInterval(() => {
                if (!isPaused) {
                    nextSlide();
                }
            }, 5000); // 5 seconds
        }

        function stopAutoSlide() {
            clearInterval(autoSlideInterval);
        }

        // Dot click handlers
        dots.forEach(dot => {
            dot.addEventListener('click', () => {
                currentIndex = parseInt(dot.dataset.index);
                showSlide(currentIndex);
                stopAutoSlide();
                startAutoSlide(); // Restart auto-slide
            });
        });

        // Pause on hover
        carousel.addEventListener('mouseenter', () => {
            isPaused = true;
        });

        carousel.addEventListener('mouseleave', () => {
            isPaused = false;
        });

        // Start auto-slide
        startAutoSlide();
    });
</script>

<!-- Inject No Follow if active (Header might have already loaded, so we do best effort here or move include down) -->
<?php if ($post['is_nofollow']): ?>
    <!-- Note: content="nofollow" generally needs to be in <head>, 
         but modern browsers might respect it here or we rely on the header logic update later. 
         For strict compliance, move header include after this block or pass vars to header. -->
    <script>
        // JS Fallback to force nofollow behavior on links in content-body
        document.addEventListener('DOMContentLoaded', () => {
            const contentLinks = document.querySelectorAll('.catalyst-content a');
            contentLinks.forEach(link => link.setAttribute('rel', 'nofollow'));
        });
    </script>
<?php endif; ?>


<div class="pt-24 pb-20 px-6 min-h-screen bg-navy-900">

    <!-- Breadcrumb Navigation -->
    <div class="max-w-7xl mx-auto mb-6">
        <nav class="flex items-center space-x-2 text-sm text-slate-400">
            <a href="<?php echo htmlspecialchars(lang_url('index.php')); ?>" class="hover:text-gold-500 transition-colors">Home</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <a href="<?php echo htmlspecialchars(lang_url('blog.php?category=' . $post['category_slug'])); ?>"
                class="hover:text-gold-500 transition-colors">
                <?php echo htmlspecialchars($post['category_name'] ?? 'General'); ?>
            </a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-slate-500 truncate max-w-md"><?php echo htmlspecialchars($post['title']); ?></span>
        </nav>
    </div>

    <!-- Hero / Header -->
    <div class="max-w-4xl mx-auto text-center mb-12">
        <div
            class="flex items-center justify-center space-x-2 text-[10px] uppercase tracking-widest text-gold-500 mb-4">
            <span class="bg-navy-800 px-2 py-1 border border-gold-500/20">
                <?php echo htmlspecialchars($post['category_name'] ?? 'General'); ?>
            </span>
            <span>&bull;</span>
            <span>
                <?php echo date('F d, Y', strtotime($post['created_at'])); ?>
            </span>
        </div>

        <h1 class="text-3xl md:text-5xl font-serif text-white mb-6 leading-tight">
            <?php echo htmlspecialchars($post['title']); ?>
        </h1>

        <div class="flex items-center justify-center space-x-2 text-slate-500 text-xs">
            <span>By
                <?php echo htmlspecialchars($post['author_name']); ?>
            </span>
            <span>&bull;</span>
            <span>
                <?php echo number_format($post['views']); ?> Reads
            </span>
        </div>
    </div>

    <!-- Featured Image -->
    <?php if ($post['image_url']): ?>
        <div class="max-w-5xl mx-auto mb-16">
            <div class="aspect-video overflow-hidden rounded-lg border border-slate-700 shadow-2xl">
                <img src="<?php echo htmlspecialchars($post['image_url']); ?>"
                    alt="<?php echo htmlspecialchars($post['title']); ?>" class="w-full h-full object-cover">
            </div>
        </div>
    <?php endif; ?>

    <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-4 gap-12">

        <!-- Main Content -->
        <div class="lg:col-span-3">
            <article
                class="catalyst-content prose prose-invert prose-lg max-w-2xl mx-auto lg:mx-0 prose-headings:font-serif prose-headings:text-white prose-a:text-gold-500 hover:prose-a:text-gold-400 prose-blockquote:border-gold-500">
                <!-- Direct Content Output (Trusted Admin Input) -->
                <?php echo nl2br($post['content']); ?>
            </article>



            <!-- Share / Footer of Post -->
            <div class="mt-24 pt-12 border-t border-slate-800 flex justify-between items-center">
                <a href="<?php echo htmlspecialchars(lang_url('blog.php')); ?>"
                    class="text-slate-400 hover:text-white text-sm uppercase tracking-widest flex items-center gap-2">
                    <span>&larr;</span> <span>Back to Insights</span>
                </a>
                <!-- Share Buttons -->
                <div class="flex items-center gap-3">
                    <span class="text-slate-500 text-xs uppercase tracking-widest mr-2">Share:</span>
                    <?php
                    $currUrl = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                    $titleEnc = urlencode($post['title']);
                    ?>
                    <!-- WhatsApp -->
                    <a href="https://wa.me/?text=<?php echo $titleEnc . '%20' . urlencode($currUrl); ?>" target="_blank"
                        class="text-slate-400 hover:text-green-400 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471.148-.682.445-.21.297-.421.594-.595.644-.248.074-1.066-.372-2.031-1.238-.768-.669-1.287-1.437-1.461-1.807-.223-.421-.024-.644.173-.867.124-.124.272-.322.421-.52.149-.174.198-.297.297-.495.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a14.7 14.7 0 00-.594-.016c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487 1.982.856 2.378.694 3.22.619.644-.05 1.387-.569 1.585-1.115.198-.569.198-1.04.149-1.115-.05-.05-.223-.124-.471-.248zM12.001 22A9.95 9.95 0 011.66 16.51L0 24l7.636-1.635A9.96 9.96 0 0112 22a9.99 9.99 0 0010-9.99A10 10 0 0012 2.01a10 10 0 000 19.99z" />
                        </svg>
                    </a>
                    <!-- Twitter/X -->
                    <a href="https://twitter.com/intent/tweet?text=<?php echo $titleEnc; ?>&url=<?php echo urlencode($currUrl); ?>"
                        target="_blank" class="text-slate-400 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                        </svg>
                    </a>
                    <!-- Facebook -->
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($currUrl); ?>"
                        target="_blank" class="text-slate-400 hover:text-blue-500 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z" />
                        </svg>
                    </a>
                    <!-- LinkedIn -->
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode($currUrl); ?>"
                        target="_blank" class="text-slate-400 hover:text-blue-400 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Sidebar (Desktop Only) -->
        <aside class="hidden lg:block lg:col-span-1 space-y-6">

            <!-- Categories Section -->
            <?php
            // Fetch all categories
            $categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
            ?>
            <div class="bg-navy-800 border border-slate-700 p-6 rounded">
                <h3 class="text-white font-serif mb-4 text-lg">Categories</h3>
                <ul class="space-y-2">
                    <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="<?php echo htmlspecialchars(lang_url('blog.php?category=' . $cat['slug'])); ?>"
                                class="flex justify-between text-sm text-slate-400 hover:text-gold-500 transition-colors py-1 border-b border-slate-700/50 pb-2 <?php echo ($post['category_slug'] === $cat['slug']) ? 'text-gold-500 font-medium' : ''; ?>">
                                <span><?php echo htmlspecialchars($cat['name']); ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Tags Section -->
            <?php
            // Fetch tags for current post
            $tagsSql = "SELECT t.* FROM tags t 
                        INNER JOIN post_tags pt ON t.id = pt.tag_id 
                        WHERE pt.post_id = :post_id 
                        ORDER BY t.name ASC";
            $tagsStmt = $pdo->prepare($tagsSql);
            $tagsStmt->execute([':post_id' => $post['id']]);
            $postTags = $tagsStmt->fetchAll();
            ?>
            <?php if (!empty($postTags)): ?>
                <div class="bg-navy-800 border border-slate-700 p-6 rounded">
                    <h3 class="text-white font-serif mb-4 text-lg">Tags</h3>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($postTags as $tag): ?>
                            <span
                                class="inline-block bg-gold-500/10 text-gold-500 px-3 py-1 rounded-full text-xs font-medium border border-gold-500/20">
                                <?php echo htmlspecialchars($tag['name']); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Related Posts Carousel -->
            <?php
            // Fetch related posts from same category (excluding current post)
            $relatedSql = "SELECT p.*, c.name as category_name, c.slug as category_slug, u.username as author_name 
                          FROM posts p 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          LEFT JOIN users u ON p.author_id = u.id 
                          WHERE p.category_id = :category_id 
                          AND p.id != :current_id 
                          AND p.status = 'published' 
                          ORDER BY p.created_at DESC 
                          LIMIT 5";
            $relatedStmt = $pdo->prepare($relatedSql);
            $relatedStmt->execute([':category_id' => $post['category_id'], ':current_id' => $post['id']]);
            $relatedPosts = $relatedStmt->fetchAll();
            ?>
            <?php if (!empty($relatedPosts)): ?>
                <div class="bg-navy-800 border border-slate-700 p-6 rounded">
                    <h3 class="text-white font-serif mb-4 text-lg">Related Articles</h3>

                    <!-- Carousel Container -->
                    <div id="relatedCarousel" class="relative">
                        <?php foreach ($relatedPosts as $index => $related): ?>
                            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?> relative group"
                                data-index="<?php echo $index; ?>">
                                <!-- Card Image -->
                                <div class="h-40 overflow-hidden bg-slate-900 relative rounded mb-3">
                                    <?php if ($related['image_url']): ?>
                                        <img src="<?php echo htmlspecialchars($related['image_url']); ?>"
                                            alt="<?php echo htmlspecialchars($related['title']); ?>"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 opacity-80 group-hover:opacity-100">
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center text-slate-700">
                                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Card Info -->
                                <div class="text-xs text-slate-500 mb-2">
                                    <?php echo date('M d, Y', strtotime($related['created_at'])); ?>
                                </div>
                                <h4 class="text-white text-sm font-medium leading-tight line-clamp-2 mb-2">
                                    <?php echo htmlspecialchars($related['title']); ?>
                                </h4>
                                <p class="text-slate-400 text-xs line-clamp-2">
                                    <?php echo htmlspecialchars($related['summary']); ?>
                                </p>

                                <!-- Hover Popup Overlay -->
                                <a href="<?php echo htmlspecialchars(lang_url('post-view.php?slug=' . $related['slug'])); ?>"
                                    class="popup-overlay absolute inset-0 bg-navy-900 p-6 flex flex-col justify-center items-center text-center opacity-0 group-hover:opacity-100 transition-all duration-300 z-20 rounded">
                                    <h4 class="text-white font-serif text-sm mb-3 line-clamp-2">
                                        <?php echo htmlspecialchars($related['title']); ?>
                                    </h4>
                                    <p class="text-slate-300 text-xs mb-6 leading-relaxed line-clamp-4">
                                        <?php
                                        $clean = str_replace(['</p>', '<br>', '<br />'], "  ", $related['content']);
                                        $clean = strip_tags($clean);
                                        echo substr($clean, 0, 150) . '...';
                                        ?>
                                    </p>
                                    <span
                                        class="bg-gold-500 text-navy-900 px-4 py-1 uppercase tracking-widest text-[10px] font-bold hover:bg-gold-400 transition-colors">
                                        Read Article
                                    </span>
                                </a>
                            </div>
                        <?php endforeach; ?>

                        <!-- Navigation Dots -->
                        <div class="flex justify-center gap-2 mt-4">
                            <?php foreach ($relatedPosts as $index => $related): ?>
                                <button
                                    class="carousel-dot w-2 h-2 rounded-full bg-slate-600 hover:bg-gold-500 transition-colors <?php echo $index === 0 ? 'active' : ''; ?>"
                                    data-index="<?php echo $index; ?>"></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Vertical Ad Section -->
            <?php
            // Fetch active sidebar ads
            $adsSql = "SELECT * FROM ads WHERE position = 'sidebar' AND is_active = 1 ORDER BY sort_order ASC LIMIT 1";
            $adsStmt = $pdo->query($adsSql);
            $sidebarAd = $adsStmt->fetch();
            ?>
            <?php if ($sidebarAd): ?>
                <div class="bg-navy-900 border border-slate-700 p-4 text-center sticky top-24 group">
                    <span class="text-[0.6rem] text-slate-600 uppercase tracking-widest block mb-2">Advertisement</span>

                    <!-- Ad Container with Hover Effect -->
                    <div class="relative overflow-hidden rounded">
                        <a href="<?php echo htmlspecialchars($sidebarAd['link_url']); ?>" target="_blank"
                            rel="noopener noreferrer" class="block relative">
                            <img src="<?php echo htmlspecialchars($sidebarAd['image_url']); ?>"
                                alt="<?php echo htmlspecialchars($sidebarAd['title']); ?>"
                                class="w-full h-[600px] object-cover transition-opacity duration-300 group-hover:opacity-30">

                            <!-- Hover Info Overlay -->
                            <div
                                class="absolute inset-0 bg-navy-900/95 p-6 flex flex-col justify-center items-center text-center opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <h4 class="text-white font-serif text-lg mb-3">
                                    <?php echo htmlspecialchars($sidebarAd['title']); ?>
                                </h4>
                                <?php if ($sidebarAd['description']): ?>
                                    <p class="text-slate-300 text-sm mb-6 leading-relaxed">
                                        <?php echo htmlspecialchars($sidebarAd['description']); ?>
                                    </p>
                                <?php endif; ?>
                                <span
                                    class="bg-gold-500 text-navy-900 px-6 py-2 uppercase tracking-widest text-xs font-bold hover:bg-gold-400 transition-colors">
                                    Learn More
                                </span>
                            </div>
                        </a>
                    </div>
                </div>
            <?php endif; ?>

        </aside>

    </div>

</div>

<?php include 'includes/footer.php'; ?>