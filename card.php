<?php
if (!headers_sent()) {
    header_remove('X-Powered-By');
    header('Content-Type: text/html; charset=utf-8');
}
ini_set('default_charset', 'UTF-8');
// card.php - Public Digital Business Card
require_once 'admin/db_connect.php';
require_once 'includes/lang.php';

$slug = $_GET['slug'] ?? $_GET['id'] ?? '';
$p = null;

try {
    if (!empty($slug)) {
        $stmt = $pdo->prepare("SELECT * FROM digital_business_cards WHERE card_slug = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$slug]);
        $p = $stmt->fetch();
    }
    
    if (!$p) {
        $p = $pdo->query("SELECT * FROM digital_business_cards WHERE is_default = 1 AND is_active = 1 LIMIT 1")->fetch();
    }
    
    if (!$p) {
        $p = $pdo->query("SELECT * FROM digital_business_cards WHERE is_active = 1 LIMIT 1")->fetch();
    }
    
    if (!$p) {
        // Fallback to executive_profile_settings if the table is empty
        $p = $pdo->query("SELECT * FROM executive_profile_settings LIMIT 1")->fetch();
    }
} catch (Exception $e) {
    $p = [];
}

// Fallback defaults
$name      = htmlspecialchars($p['full_name']    ?? 'Shawn Radam');
$title     = htmlspecialchars($p['title']         ?? 'Private Advisor');
$company   = htmlspecialchars($p['company_name']  ?? 'Shawn Radam Advisory');
$phone1    = htmlspecialchars($p['phone_primary']  ?? '0128338639');
$phone2    = htmlspecialchars($p['phone_secondary'] ?? '01116339399');
$wa        = $p['whatsapp_number'] ?? '601283386392';
$email     = htmlspecialchars($p['email_address'] ?? '');
$address   = htmlspecialchars($p['address_line']  ?? 'Kota Kinabalu, Sabah, Malaysia');
$website   = htmlspecialchars($p['website_url']   ?? 'https://shawnradam.com');
$linkedin  = htmlspecialchars($p['linkedin_url']  ?? '');
$instagram = htmlspecialchars($p['instagram_url'] ?? '');
$facebook  = htmlspecialchars($p['facebook_url']  ?? '');
$bio       = htmlspecialchars($p['card_bio']      ?? 'Personal Advisor specialising in Asset Acquisition, Structured Lending & Travel Logistics.');
$portrait  = htmlspecialchars($p['portrait_url']  ?? '');
$tag1      = htmlspecialchars($p['expertise_tag_1'] ?? 'Asset Acquisition');
$tag2      = htmlspecialchars($p['expertise_tag_2'] ?? 'Structured Lending');
$tag3      = htmlspecialchars($p['expertise_tag_3'] ?? 'Travel Logistics');
$years     = htmlspecialchars($p['years_experience'] ?? '12+');
$deals     = htmlspecialchars($p['deals_closed']     ?? '250+');
$rating    = htmlspecialchars($p['rating']           ?? '5.0');
$initials  = strtoupper(substr(strip_tags($name), 0, 2));

// Card URL for QR code
$cardUrl   = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
             . '://' . ($_SERVER['HTTP_HOST'] ?? 'shawnradam.com') . '/card.php' . (!empty($slug) ? '?slug=' . urlencode($slug) : '');

// VCard Download URL
$vcardUrl = 'vcard.php' . (!empty($slug) ? '?slug=' . urlencode($slug) : '');

// WhatsApp message
$waMsg = urlencode("Salam, saya telah melihat kad digital Shawn Radam. Saya ingin bertanya tentang perkhidmatan anda.");
$waLink = "https://wa.me/{$wa}?text={$waMsg}";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $name ?> - Digital Business Card</title>
    <meta name="description" content="<?= $title ?> | <?= $company ?>. Save contact & connect instantly.">
    <meta property="og:title" content="<?= $name ?> - Digital Business Card">
    <meta property="og:description" content="<?= $bio ?>">
    <?php
if ($portrait): ?><meta property="og:image" content="<?= $portrait ?>"><?php
endif; ?>
    <meta property="og:type" content="profile">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- QR Code library -->
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: { 950: '#050c1a', 900: '#0a192f', 800: '#0d1f3c' },
                        gold: { 500: '#d4af37', 400: '#e8c94b', 300: '#f0da76' }
                    },
                    fontFamily: {
                        serif: ['"Playfair Display"', 'Georgia', 'serif'],
                        sans: ['Inter', 'system-ui', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        * { -webkit-tap-highlight-color: transparent; }
        body { background: #050c1a; }

        /* Ambient glow blobs */
        .blob-1 { position:fixed; top:-120px; right:-120px; width:400px; height:400px; background:radial-gradient(circle, rgba(212,175,55,0.12) 0%, transparent 70%); pointer-events:none; z-index:0; }
        .blob-2 { position:fixed; bottom:-120px; left:-120px; width:350px; height:350px; background:radial-gradient(circle, rgba(16,185,129,0.08) 0%, transparent 70%); pointer-events:none; z-index:0; }
        .blob-3 { position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); width:600px; height:600px; background:radial-gradient(circle, rgba(59,130,246,0.05) 0%, transparent 70%); pointer-events:none; z-index:0; }

        /* Card shimmer border */
        @keyframes shimmer-border {
            0%   { background-position: 0% 50%; }
            50%  { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .shimmer-border {
            background: linear-gradient(135deg, #d4af37, #10b981, #3b82f6, #d4af37);
            background-size: 300% 300%;
            animation: shimmer-border 6s ease infinite;
        }

        /* Portrait pulse */
        @keyframes portrait-pulse {
            0%, 100% { opacity: 0.7; transform: scale(1); }
            50%       { opacity: 1;   transform: scale(1.05); }
        }
        .portrait-glow { animation: portrait-pulse 4s ease-in-out infinite; }

        /* Action button press */
        .btn-action:active { transform: scale(0.96); }

        /* Stat counter ticker */
        @keyframes countUp { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }
        .stat-val { animation: countUp 0.6s ease forwards; }

        /* Save button pulse */
        @keyframes save-pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(212,175,55,0.4); }
            50%       { box-shadow: 0 0 0 14px rgba(212,175,55,0); }
        }
        .save-pulse { animation: save-pulse 2.5s ease-in-out infinite; }

        /* Smooth entrance */
        @keyframes slide-up { from { opacity:0; transform:translateY(30px); } to { opacity:1; transform:translateY(0); } }
        .animate-in { animation: slide-up 0.7s ease forwards; }
        .animate-in-delay { animation: slide-up 0.7s ease 0.15s forwards; opacity:0; }
        .animate-in-delay-2 { animation: slide-up 0.7s ease 0.3s forwards; opacity:0; }

        /* QR scan line */
        @keyframes scan { 0%,100% { top:0; } 50% { top:calc(100% - 3px); } }
        .scan-line { animation: scan 2.5s ease-in-out infinite; }
    </style>
</head>
<body class="min-h-screen flex items-start justify-center py-8 px-4 font-sans">
    <div class="blob-1"></div>
    <div class="blob-2"></div>
    <div class="blob-3"></div>

    <div class="relative z-10 w-full max-w-md animate-in">

        <!-- Card -->
        <div class="relative rounded-3xl overflow-hidden shadow-2xl" style="background: linear-gradient(160deg, #0d1f3c 0%, #0a192f 50%, #081629 100%); border: 1px solid rgba(212,175,55,0.2);">

            <!-- Shimmer top bar -->
            <div class="shimmer-border h-1 w-full"></div>

            <!-- Background grid pattern -->
            <div class="absolute inset-0 opacity-[0.03]" style="background-image: linear-gradient(#d4af37 1px, transparent 1px), linear-gradient(90deg, #d4af37 1px, transparent 1px); background-size: 40px 40px;"></div>

            <div class="relative z-10 px-6 sm:px-8 pt-10 pb-8">

            <!-- Portrait and Name -->
                <div class="flex flex-col items-center text-center mb-6 animate-in">
                    <!-- Portrait -->
                    <div class="relative mb-5">
                        <div class="portrait-glow absolute inset-0 rounded-full shimmer-border scale-110 blur-sm opacity-60"></div>
                        <div class="relative w-28 h-28 rounded-full overflow-hidden border-2 border-white/10 shadow-2xl bg-navy-800">
                            <?php
if ($portrait): ?>
                                <img src="<?= $portrait ?>" alt="<?= $name ?>" class="w-full h-full object-cover">
                            <?php
else: ?>
                                <div class="w-full h-full flex items-center justify-center text-3xl font-bold text-gold-500 bg-navy-900"><?= $initials ?></div>
                            <?php
endif; ?>
                        </div>
                        <!-- Verified badge -->
                        <div class="absolute -bottom-1 -right-1 w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center border-2 border-navy-900 shadow-lg">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        </div>
                    </div>

                    <!-- Name -->
                    <h1 class="text-3xl font-serif font-bold text-white leading-tight mb-1"><?= $name ?></h1>
                    <!-- Title -->
                    <p class="text-xs uppercase tracking-[0.22em] text-emerald-400 font-semibold mb-1"><?= $title ?></p>
                    <!-- Company -->
                    <p class="text-sm text-gold-500/80 font-medium mb-4"><?= $company ?></p>

                    <!-- Bio -->
                    <p class="text-slate-400 text-sm leading-relaxed mb-5 max-w-[260px]"><?= $bio ?></p>

                    <!-- Expertise tags -->
                    <div class="flex flex-wrap justify-center gap-1.5 mb-6">
                        <?php
foreach ([$tag1,$tag2,$tag3] as $tag): if (!$tag) continue; ?>
                        <span class="px-3 py-1 rounded-full bg-white/5 border border-white/10 text-white/70 text-[10px] font-medium tracking-wide"><?= $tag ?></span>
                        <?php
endforeach; ?>
                    </div>
                </div>

            <!-- Stats Bar -->
                <div class="animate-in-delay bg-white/5 border border-white/8 rounded-2xl p-4 mb-6 backdrop-blur-sm">
                    <div class="grid grid-cols-3 gap-3 divide-x divide-white/10">
                        <div class="text-center">
                            <div class="stat-val text-2xl font-serif font-bold text-white"><?= $years ?></div>
                            <div class="text-[9px] uppercase tracking-widest text-white/40 mt-0.5">Years Exp</div>
                        </div>
                        <div class="text-center">
                            <div class="stat-val text-2xl font-serif font-bold text-white"><?= $deals ?></div>
                            <div class="text-[9px] uppercase tracking-widest text-white/40 mt-0.5">Deals Closed</div>
                        </div>
                        <div class="text-center">
                            <div class="stat-val text-2xl font-serif font-bold text-white flex items-center justify-center gap-0.5">
                                <?= $rating ?>
                                <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            </div>
                            <div class="text-[9px] uppercase tracking-widest text-white/40 mt-0.5">Rating</div>
                        </div>
                    </div>
                </div>

            <!-- Contact Details -->
                <div class="animate-in-delay-2 space-y-2 mb-6">
                    <?php
if ($phone1): ?>
                    <a href="tel:<?= preg_replace('/[^0-9+]/','',$phone1) ?>" class="btn-action flex items-center gap-3 px-4 py-3 rounded-xl bg-white/5 hover:bg-white/10 border border-white/8 transition-all group">
                        <div class="w-9 h-9 rounded-lg bg-emerald-500/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-[10px] text-slate-500 uppercase tracking-wider">Primary Phone</div>
                            <div class="text-sm font-medium text-white"><?= $phone1 ?></div>
                        </div>
                        <svg class="w-4 h-4 text-slate-600 group-hover:text-gold-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <?php
endif; ?>

                    <?php
if ($phone2): ?>
                    <a href="tel:<?= preg_replace('/[^0-9+]/','',$phone2) ?>" class="btn-action flex items-center gap-3 px-4 py-3 rounded-xl bg-white/5 hover:bg-white/10 border border-white/8 transition-all group">
                        <div class="w-9 h-9 rounded-lg bg-blue-500/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-[10px] text-slate-500 uppercase tracking-wider">WhatsApp</div>
                            <div class="text-sm font-medium text-white"><?= $phone2 ?></div>
                        </div>
                        <svg class="w-4 h-4 text-slate-600 group-hover:text-gold-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <?php
endif; ?>

                    <?php
if ($email): ?>
                    <a href="mailto:<?= $email ?>" class="btn-action flex items-center gap-3 px-4 py-3 rounded-xl bg-white/5 hover:bg-white/10 border border-white/8 transition-all group">
                        <div class="w-9 h-9 rounded-lg bg-gold-500/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-gold-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-[10px] text-slate-500 uppercase tracking-wider">Email</div>
                            <div class="text-sm font-medium text-white truncate"><?= $email ?></div>
                        </div>
                        <svg class="w-4 h-4 text-slate-600 group-hover:text-gold-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <?php
endif; ?>

                    <?php
if ($address): ?>
                    <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-white/5 border border-white/8">
                        <div class="w-9 h-9 rounded-lg bg-purple-500/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-[10px] text-slate-500 uppercase tracking-wider">Location</div>
                            <div class="text-sm font-medium text-white"><?= $address ?></div>
                        </div>
                    </div>
                    <?php
endif; ?>

                    <?php
if ($website): ?>
                    <a href="<?= $website ?>" target="_blank" class="btn-action flex items-center gap-3 px-4 py-3 rounded-xl bg-white/5 hover:bg-white/10 border border-white/8 transition-all group">
                        <div class="w-9 h-9 rounded-lg bg-teal-500/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-[10px] text-slate-500 uppercase tracking-wider">Website</div>
                            <div class="text-sm font-medium text-white truncate"><?= str_replace(['https://','http://'],'',$website) ?></div>
                        </div>
                        <svg class="w-4 h-4 text-slate-600 group-hover:text-gold-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <?php
endif; ?>
                </div>

            <!-- Social Links -->
                <?php
if ($linkedin || $instagram || $facebook): ?>
                <div class="flex justify-center gap-3 mb-6">
                    <?php
if ($linkedin): ?>
                    <a href="<?= $linkedin ?>" target="_blank" class="btn-action w-11 h-11 rounded-xl bg-[#0077B5]/20 border border-[#0077B5]/30 flex items-center justify-center hover:bg-[#0077B5]/40 transition-all">
                        <svg class="w-5 h-5 text-[#0077B5]" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                    </a>
                    <?php
endif; ?>
                    <?php
if ($instagram): ?>
                    <a href="<?= $instagram ?>" target="_blank" class="btn-action w-11 h-11 rounded-xl bg-pink-500/20 border border-pink-500/30 flex items-center justify-center hover:bg-pink-500/40 transition-all">
                        <svg class="w-5 h-5 text-pink-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                    </a>
                    <?php
endif; ?>
                    <?php
if ($facebook): ?>
                    <a href="<?= $facebook ?>" target="_blank" class="btn-action w-11 h-11 rounded-xl bg-[#1877F2]/20 border border-[#1877F2]/30 flex items-center justify-center hover:bg-[#1877F2]/40 transition-all">
                        <svg class="w-5 h-5 text-[#1877F2]" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <?php
endif; ?>
                    <a href="<?= $waLink ?>" target="_blank" class="btn-action w-11 h-11 rounded-xl bg-green-500/20 border border-green-500/30 flex items-center justify-center hover:bg-green-500/40 transition-all">
                        <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                    </a>
                </div>
                <?php
endif; ?>

            <!-- Contact Details -->
                <a href="<?= $vcardUrl ?>" download="<?= preg_replace('/[^a-zA-Z0-9_-]/', '_', strip_tags($name)) ?>.vcf"
                   class="save-pulse btn-action block w-full py-4 rounded-2xl text-center font-bold text-sm uppercase tracking-widest text-navy-900 transition-all mb-3"
                   style="background: linear-gradient(135deg, #d4af37 0%, #f0da76 50%, #d4af37 100%);">
                    <span class="flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                        Save Contact
                    </span>
                </a>

                <!-- WhatsApp CTA -->
                <a href="<?= $waLink ?>" target="_blank"
                   class="btn-action block w-full py-3.5 rounded-2xl text-center font-bold text-sm uppercase tracking-widest text-white transition-all border border-green-500/40 hover:bg-green-500/10 mb-6"
                   style="background: rgba(34,197,94,0.12);">
                    <span class="flex items-center justify-center gap-2">
                        <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                        Message on WhatsApp
                    </span>
                </a>

            <!-- QR Code Section -->
                <div class="border-t border-white/10 pt-5 text-center">
                    <p class="text-[10px] text-slate-500 uppercase tracking-widest mb-3">Scan to Share This Card</p>
                    <div class="flex justify-center">
                        <div class="relative inline-block">
                            <!-- QR frame -->
                            <div class="p-2 rounded-xl border border-gold-500/30 bg-white" style="box-shadow: 0 0 20px rgba(212,175,55,0.15);">
                                <canvas id="qrCanvas" width="130" height="130"></canvas>
                            </div>
                            <!-- Scanning line -->
                            <div class="absolute left-2 right-2 h-0.5 rounded-full scan-line" style="background: linear-gradient(90deg, transparent, #d4af37, transparent);"></div>
                        </div>
                    </div>
                    <p class="text-[10px] text-slate-600 mt-2">shawnradam.com/card.php</p>
                </div>

            </div>

            <!-- Shimmer bottom bar -->
            <div class="shimmer-border h-1 w-full"></div>
        </div>

        <!-- Footer credit -->
        <div class="text-center mt-6">
            <a href="<?php
echo htmlspecialchars(lang_url('index.php')); ?>" class="text-[10px] text-slate-600 hover:text-gold-500 transition-colors uppercase tracking-widest">&larr; Back to Main Website</a>
        </div>

    </div>

    <script>
        // Generate QR code pointing to this card page
        const cardUrl = <?= json_encode($cardUrl) ?>;
        QRCode.toCanvas(document.getElementById('qrCanvas'), cardUrl, {
            width: 130,
            margin: 0,
            color: { dark: '#0a192f', light: '#ffffff' },
            errorCorrectionLevel: 'M'
        }, function(err) {
            if (err) console.error(err);
        });
    </script>
</body>
</html>
