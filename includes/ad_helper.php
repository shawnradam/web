<?php
// includes/ad_helper.php

function renderAds($pdo, $position)
{
    try {
        if (!isset($pdo)) {
            // Include db_connect if not passed, but usually it's available global
            // Assuming $pdo is passed from the page context
            return;
        }

        $stmt = $pdo->prepare("SELECT * FROM ads WHERE position = ? AND is_active = 1 ORDER BY sort_order ASC, created_at DESC");
        $stmt->execute([$position]);
        $ads = $stmt->fetchAll();

        if (empty($ads))
            return;

        foreach ($ads as $ad) {
            echo '<a href="' . htmlspecialchars($ad['link_url']) . '" target="_blank" class="block w-full mb-6 group relative overflow-hidden rounded border border-slate-700/50 hover:border-gold-500/50 transition-colors">';

            // Image
            echo '<img src="' . htmlspecialchars($ad['image_url']) . '" alt="' . htmlspecialchars($ad['title']) . '" class="w-full h-auto object-cover opacity-90 group-hover:opacity-100 transition-opacity">';

            // Optional Label
            echo '<div class="absolute top-0 right-0 bg-slate-900/80 text-[10px] text-slate-500 px-2 py-0.5 uppercase tracking-widest">Ad</div>';

            echo '</a>';
        }

    } catch (PDOException $e) {
        // Silently fail
    }
}
?>