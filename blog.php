<?php
require_once 'admin/db_connect.php';
include 'includes/header.php';
include 'includes/navigation.php';

$category_slug = $_GET['category'] ?? null;

// 1. Fetch categories
$cats_stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$cats = $cats_stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Fetch posts
if ($category_slug) {
    $posts_stmt = $pdo->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug, u.username as author_name 
                                 FROM posts p 
                                 LEFT JOIN categories c ON p.category_id = c.id 
                                 LEFT JOIN users u ON p.author_id = u.id 
                                 WHERE c.slug = ? AND p.status = 'published' 
                                 ORDER BY p.created_at DESC");
    $posts_stmt->execute([$category_slug]);
} else {
    $posts_stmt = $pdo->query("SELECT p.*, c.name as category_name, c.slug as category_slug, u.username as author_name 
                               FROM posts p 
                               LEFT JOIN categories c ON p.category_id = c.id 
                               LEFT JOIN users u ON p.author_id = u.id 
                               WHERE p.status = 'published' 
                               ORDER BY p.created_at DESC");
}
$posts = $posts_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    .blog-card:hover .popup-content {
        opacity: 1;
        transform: translateY(0);
    }
</style>

<div class="pt-24 pb-20 px-6 min-h-screen bg-navy-900">
    <div class="max-w-7xl mx-auto">
        
        <header class="mb-12 border-b border-slate-800 pb-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
                <div>
                    <h1 class="text-4xl font-serif text-white mb-2">Advisor Insights</h1>
                    <p class="text-slate-400">Strategic intelligence on Sabah real estate and finance.</p>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                    <div class="relative flex-1 md:w-64">
                        <input 
                            type="text" 
                            id="searchFilter" 
                            placeholder="Search articles..." 
                            class="w-full bg-navy-800 border border-slate-700 text-slate-300 text-sm p-2 pl-9 rounded focus:border-gold-500 focus:outline-none">
                        <svg class="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    
                    <div class="relative">
                        <select onchange="location = this.value;" class="bg-navy-800 border border-slate-700 text-slate-300 text-sm p-2 pl-9 pr-8 rounded appearance-none cursor-pointer focus:border-gold-500 focus:outline-none">
                            <option value="<?php echo htmlspecialchars(lang_url('blog.php')); ?>">All Topics</option>
                            <?php foreach ($cats as $c): ?>
                                <option value="<?php echo htmlspecialchars(lang_url('blog.php?category=' . $c['slug'])); ?>" <?php echo $category_slug === $c['slug'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <svg class="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        <svg class="w-4 h-4 absolute right-2 top-1/2 transform -translate-y-1/2 text-slate-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-12">
            
            <div class="lg:col-span-3">
                
                <?php if (empty($posts)): ?>
                    <div class="text-center py-20 bg-navy-800 border border-slate-700 rounded-lg">
                        <svg class="w-16 h-16 text-slate-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" /></svg>
                        <h3 class="text-white text-xl font-serif mb-2">No Articles Found</h3>
                        <p class="text-slate-400">Check back later for updates.</p>
                        <a href="<?php echo htmlspecialchars(lang_url('blog.php')); ?>" class="text-gold-500 hover:underline mt-4 inline-block">Clear Filters</a>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <?php foreach ($posts as $post): 
                            $image = !empty($post['image_url']) ? $post['image_url'] : null;
                            $author = !empty($post['author_name']) ? $post['author_name'] : 'Advisor';
                            $catName = $post['category_name'] ?? null;
                            $catSlug = $post['category_slug'] ?? null;
                            $postUrl = lang_url('post-view.php?slug=' . urlencode($post['slug']));
                        ?>
                            <div class="blog-card group relative bg-navy-800 border border-slate-700 overflow-hidden hover:border-gold-500/50 transition-all duration-300 h-[400px] flex flex-col">
                                
                                <div class="h-48 overflow-hidden bg-slate-900 relative">
                                    <?php if ($image): ?>
                                        <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 opacity-80 group-hover:opacity-100">
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center text-slate-700 bg-slate-900">
                                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($catName): ?>
                                        <span class="absolute top-4 right-4 bg-navy-900/80 backdrop-blur text-gold-500 text-[10px] uppercase font-bold px-2 py-1 tracking-widest border border-gold-500/20">
                                            <?php echo htmlspecialchars($catName); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="p-6 flex-1 flex flex-col relative">
                                    <div class="text-[10px] text-slate-500 uppercase tracking-widest mb-2 flex items-center space-x-2">
                                        <span><?php echo date('M d, Y', strtotime($post['created_at'])); ?></span>
                                        <span>&bull;</span>
                                        <span><?php echo htmlspecialchars($author); ?></span>
                                    </div>
                                    
                                    <h2 class="text-xl font-serif text-white mb-3 leading-tight group-hover:text-gold-500 transition-colors line-clamp-2">
                                        <a href="<?php echo $postUrl; ?>" class="focus:outline-none">
                                            <?php echo htmlspecialchars($post['title']); ?>
                                        </a>
                                    </h2>
                                    
                                    <p class="text-slate-400 text-sm line-clamp-3 mb-5">
                                        <?php echo htmlspecialchars(strip_tags($post['summary'] ?? '')); ?>
                                    </p>

                                    <a href="<?php echo htmlspecialchars($postUrl); ?>" class="mt-auto inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-gold-500 hover:text-gold-400 transition-colors">
                                        Read More
                                        <span aria-hidden="true">&rarr;</span>
                                    </a>
                                </div>

                                <a href="<?php echo $postUrl; ?>" class="popup-content hidden md:flex absolute inset-0 bg-navy-900 p-8 flex-col justify-center items-center text-center opacity-0 translate-y-4 transition-all duration-300 z-20 pointer-events-none group-hover:pointer-events-auto cursor-pointer text-decoration-none">
                                    <h3 class="text-white font-serif text-lg mb-4 line-clamp-2"><?php echo htmlspecialchars($post['title']); ?></h3>
                                    
                                    <p class="text-slate-300 text-sm mb-12 leading-relaxed line-clamp-5 px-4">
                                        <?php 
                                            $clean_content = strip_tags($post['content'] ?? '');
                                            echo substr($clean_content, 0, 350) . '...'; 
                                        ?>
                                    </p>
                                    
                                    <span class="bg-gold-500 text-navy-900 px-6 py-2 uppercase tracking-widest text-xs font-bold hover:bg-gold-400 transition-colors shadow-lg shadow-gold-500/20">
                                        Read Full Article
                                    </span>
                                </a>

                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
            </div>

            <aside class="hidden lg:block lg:col-span-1 space-y-8">
                
                <div class="bg-navy-800 border border-slate-700 p-6 rounded">
                    <h3 class="text-white font-serif mb-4">Search</h3>
                    <input type="text" placeholder="Keywords..." class="w-full bg-navy-900 border border-slate-600 p-2 text-white text-sm rounded focus:border-gold-500 outline-none">
                </div>

                <div class="bg-navy-800 border border-slate-700 p-6 rounded">
                    <h3 class="text-white font-serif mb-4">Topics</h3>
                    <ul class="space-y-2">
                        <li>
                            <a href="<?php echo htmlspecialchars(lang_url('blog.php')); ?>" class="flex justify-between text-sm text-slate-400 hover:text-gold-500 transition-colors py-1 border-b border-slate-700/50 pb-2">
                                <span>All Articles</span>
                            </a>
                        </li>
                        <?php foreach ($cats as $c): ?>
                            <li>
                                <a href="<?php echo htmlspecialchars(lang_url('blog.php?category=' . $c['slug'])); ?>" class="flex justify-between text-sm text-slate-400 hover:text-gold-500 transition-colors py-1 border-b border-slate-700/50 pb-2">
                                    <span><?php echo htmlspecialchars($c['name']); ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="bg-navy-900 border border-slate-700 p-4 text-center">
                    <span class="text-[0.6rem] text-slate-600 uppercase tracking-widest block mb-2">Advertisement</span>
                    <div class="h-64 bg-slate-800 flex items-center justify-center text-slate-600 text-xs">
                        Ad Space
                    </div>
                </div>

            </aside>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchFilter');
    if (!searchInput) return;

    searchInput.addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase();
        const blogCards = document.querySelectorAll('.blog-card');

        blogCards.forEach(card => {
            const title = card.querySelector('h2')?.textContent.toLowerCase() || '';
            const summary = card.querySelector('p')?.textContent.toLowerCase() || '';
            
            if (title.includes(searchTerm) || summary.includes(searchTerm)) {
                card.parentElement.style.display = '';
            } else {
                card.parentElement.style.display = 'none';
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
