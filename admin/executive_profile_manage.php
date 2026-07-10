<?php
require_once 'auth_check.php';
header("Location: business_cards_manage.php");
exit;

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $full_name = $_POST['full_name'];
        $title = $_POST['title'];
        $expertise_tag_1 = $_POST['expertise_tag_1'];
        $expertise_tag_2 = $_POST['expertise_tag_2'];
        $expertise_tag_3 = $_POST['expertise_tag_3'];
        $years_experience = $_POST['years_experience'];
        $deals_closed = $_POST['deals_closed'];
        $rating = $_POST['rating'];
        $primary_button_text = $_POST['primary_button_text'];
        $primary_button_link = $_POST['primary_button_link'];
        $secondary_button_text = $_POST['secondary_button_text'];
        $secondary_button_link = $_POST['secondary_button_link'];
        $phone_primary    = $_POST['phone_primary'] ?? '';
        $phone_secondary  = $_POST['phone_secondary'] ?? '';
        $whatsapp_number  = $_POST['whatsapp_number'] ?? '';
        $email_address    = $_POST['email_address'] ?? '';
        $company_name     = $_POST['company_name'] ?? '';
        $address_line     = $_POST['address_line'] ?? '';
        $website_url      = $_POST['website_url'] ?? '';
        $linkedin_url     = $_POST['linkedin_url'] ?? '';
        $instagram_url    = $_POST['instagram_url'] ?? '';
        $facebook_url     = $_POST['facebook_url'] ?? '';
        $card_bio         = $_POST['card_bio'] ?? '';

        $sql = "UPDATE executive_profile_settings SET 
                full_name = ?, title = ?, 
                expertise_tag_1 = ?, expertise_tag_2 = ?, expertise_tag_3 = ?,
                years_experience = ?, deals_closed = ?, rating = ?,
                primary_button_text = ?, primary_button_link = ?,
                secondary_button_text = ?, secondary_button_link = ?,
                phone_primary = ?, phone_secondary = ?, whatsapp_number = ?,
                email_address = ?, company_name = ?, address_line = ?,
                website_url = ?, linkedin_url = ?, instagram_url = ?,
                facebook_url = ?, card_bio = ?
                WHERE id = 1";

        $params = [
            $full_name, $title,
            $expertise_tag_1, $expertise_tag_2, $expertise_tag_3,
            $years_experience, $deals_closed, $rating,
            $primary_button_text, $primary_button_link,
            $secondary_button_text, $secondary_button_link,
            $phone_primary, $phone_secondary, $whatsapp_number,
            $email_address, $company_name, $address_line,
            $website_url, $linkedin_url, $instagram_url,
            $facebook_url, $card_bio
        ];

        // Handle portrait upload
        if (isset($_FILES['portrait']) && $_FILES['portrait']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../assets/uploads/profile/';
            if (!is_dir($uploadDir))
                mkdir($uploadDir, 0777, true);

            $fileName = time() . '_portrait_' . $_FILES['portrait']['name'];
            if (move_uploaded_file($_FILES['portrait']['tmp_name'], $uploadDir . $fileName)) {
                $sql = "UPDATE executive_profile_settings SET 
                        full_name = ?, title = ?, portrait_url = ?,
                        expertise_tag_1 = ?, expertise_tag_2 = ?, expertise_tag_3 = ?,
                        years_experience = ?, deals_closed = ?, rating = ?,
                        primary_button_text = ?, primary_button_link = ?,
                        secondary_button_text = ?, secondary_button_link = ?,
                        phone_primary = ?, phone_secondary = ?, whatsapp_number = ?,
                        email_address = ?, company_name = ?, address_line = ?,
                        website_url = ?, linkedin_url = ?, instagram_url = ?,
                        facebook_url = ?, card_bio = ?
                        WHERE id = 1";
                $params = [
                    $full_name, $title,
                    'assets/uploads/profile/' . $fileName,
                    $expertise_tag_1, $expertise_tag_2, $expertise_tag_3,
                    $years_experience, $deals_closed, $rating,
                    $primary_button_text, $primary_button_link,
                    $secondary_button_text, $secondary_button_link,
                    $phone_primary, $phone_secondary, $whatsapp_number,
                    $email_address, $company_name, $address_line,
                    $website_url, $linkedin_url, $instagram_url,
                    $facebook_url, $card_bio
                ];
            }
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        header("Location: executive_profile_manage.php?success=1");
        exit;
    }
}

// Fetch current profile data
$stmt = $pdo->query("SELECT * FROM executive_profile_settings LIMIT 1");
$profile = $stmt->fetch();

if (!$profile) {
    // Create default record if none exists
    $pdo->exec("INSERT INTO executive_profile_settings (full_name, title) VALUES ('Shawn Radam', 'Private Advisory')");
    $stmt = $pdo->query("SELECT * FROM executive_profile_settings LIMIT 1");
    $profile = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php $pageTitle = 'Executive Profile Card'; ?>
    <?php include 'includes/admin_head.php'; ?>
</head>

<body class="bg-navy-900 text-slate-300 font-sans antialiased">
    <div class="flex min-h-screen relative" x-data="{ 
            sidebarOpen: false, 
            collapsed: (function(){ try { return localStorage.getItem('sidebarCollapsed') === 'true' } catch(e){ return false } })() 
        }" x-init="$watch('collapsed', val => { try { localStorage.setItem('sidebarCollapsed', val) } catch(e){} })">

        <?php include 'dashboard_sidebar_partial.php'; ?>

        <div class="flex-1 flex flex-col min-h-screen transition-all duration-300 ease-in-out md:ml-64 overflow-x-hidden"
            :class="collapsed ? 'md:!ml-20' : 'md:!ml-64'">

            <?php include 'includes/admin_header_partial.php'; ?>

            <main class="flex-1 p-4 pb-24 md:p-8 md:pb-8 overflow-y-auto">
                <div class="max-w-4xl mx-auto">

                    <!-- Header -->
                    <div class="flex justify-between items-end mb-8">
                        <div>
                            <h1 class="text-3xl font-serif text-white mb-2">Executive Profile Card</h1>
                            <p class="text-slate-400">Manage your premium profile card content</p>
                        </div>
                        <a href="../card.php" target="_blank"
                           class="flex items-center gap-2 px-4 py-2 bg-gold-500/20 hover:bg-gold-500/30 border border-gold-500/40 text-gold-400 rounded-lg text-sm font-bold transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            Preview Card
                        </a>
                    </div>

                    <?php if (isset($_GET['success'])): ?>
                        <div class="mb-6 bg-green-900/20 border border-green-500/50 text-green-400 px-4 py-3 rounded-lg">
                            ✅ Profile updated successfully!
                        </div>
                    <?php endif; ?>

                    <!-- Form -->
                    <form method="POST" enctype="multipart/form-data" class="space-y-6">
                        <input type="hidden" name="action" value="update_profile">

                        <!-- Basic Information -->
                        <div class="bg-slate-800 rounded-xl border border-slate-700 p-6">
                            <h2 class="text-xl font-bold text-white mb-4">Basic Information</h2>

                            <div class="grid md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-slate-400 text-sm mb-2">Full Name *</label>
                                    <input type="text" name="full_name" required
                                        value="<?php echo htmlspecialchars($profile['full_name']); ?>"
                                        class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                </div>
                                <div>
                                    <label class="block text-slate-400 text-sm mb-2">Title *</label>
                                    <input type="text" name="title" required
                                        value="<?php echo htmlspecialchars($profile['title']); ?>"
                                        class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                </div>
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Portrait Image</label>
                                <?php if (!empty($profile['portrait_url'])): ?>
                                    <div class="mb-2">
                                        <img src="../<?php echo htmlspecialchars($profile['portrait_url']); ?>"
                                            class="w-24 h-24 rounded-full object-cover border-2 border-slate-600">
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="portrait" accept="image/*"
                                    class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                <p class="text-slate-500 text-xs mt-1">Recommended: Square image, at least 200x200px</p>
                            </div>
                        </div>

                        <!-- Expertise Tags -->
                        <div class="bg-slate-800 rounded-xl border border-slate-700 p-6">
                            <h2 class="text-xl font-bold text-white mb-4">Expertise Tags</h2>

                            <div class="grid md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-slate-400 text-sm mb-2">Tag 1 *</label>
                                    <input type="text" name="expertise_tag_1" required
                                        value="<?php echo htmlspecialchars($profile['expertise_tag_1']); ?>"
                                        class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                </div>
                                <div>
                                    <label class="block text-slate-400 text-sm mb-2">Tag 2 *</label>
                                    <input type="text" name="expertise_tag_2" required
                                        value="<?php echo htmlspecialchars($profile['expertise_tag_2']); ?>"
                                        class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                </div>
                                <div>
                                    <label class="block text-slate-400 text-sm mb-2">Tag 3 *</label>
                                    <input type="text" name="expertise_tag_3" required
                                        value="<?php echo htmlspecialchars($profile['expertise_tag_3']); ?>"
                                        class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                </div>
                            </div>
                        </div>

                        <!-- Trust Metrics -->
                        <div class="bg-slate-800 rounded-xl border border-slate-700 p-6">
                            <h2 class="text-xl font-bold text-white mb-4">Trust Metrics</h2>

                            <div class="grid md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-slate-400 text-sm mb-2">Years Experience *</label>
                                    <input type="text" name="years_experience" required
                                        value="<?php echo htmlspecialchars($profile['years_experience']); ?>"
                                        placeholder="e.g., 12+"
                                        class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                </div>
                                <div>
                                    <label class="block text-slate-400 text-sm mb-2">Deals Closed *</label>
                                    <input type="text" name="deals_closed" required
                                        value="<?php echo htmlspecialchars($profile['deals_closed']); ?>"
                                        placeholder="e.g., 250+"
                                        class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                </div>
                                <div>
                                    <label class="block text-slate-400 text-sm mb-2">Rating *</label>
                                    <input type="text" name="rating" required
                                        value="<?php echo htmlspecialchars($profile['rating']); ?>"
                                        placeholder="e.g., 5.0"
                                        class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                </div>
                            </div>
                        </div>

                        <!-- Button Configuration -->
                        <div class="bg-slate-800 rounded-xl border border-slate-700 p-6">
                            <h2 class="text-xl font-bold text-white mb-4">Action Buttons</h2>

                            <div class="space-y-4">
                                <div class="grid md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-slate-400 text-sm mb-2">Primary Button Text *</label>
                                        <input type="text" name="primary_button_text" required
                                            value="<?php echo htmlspecialchars($profile['primary_button_text']); ?>"
                                            class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-sm mb-2">Primary Button Link *</label>
                                        <input type="text" name="primary_button_link" required
                                            value="<?php echo htmlspecialchars($profile['primary_button_link']); ?>"
                                            placeholder="e.g., developer-briefing.php"
                                            class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                    </div>
                                </div>

                                <div class="grid md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-slate-400 text-sm mb-2">Secondary Button Text *</label>
                                        <input type="text" name="secondary_button_text" required
                                            value="<?php echo htmlspecialchars($profile['secondary_button_text']); ?>"
                                            class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-sm mb-2">Secondary Button Link *</label>
                                        <input type="text" name="secondary_button_link" required
                                            value="<?php echo htmlspecialchars($profile['secondary_button_link']); ?>"
                                            placeholder="e.g., contact.php"
                                            class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ══ Digital Business Card — Contact Details ══ -->
                        <div class="bg-slate-800 rounded-xl border border-gold-500/30 p-6">
                            <div class="flex items-center gap-3 mb-5">
                                <div class="w-8 h-8 rounded-lg bg-gold-500/20 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-gold-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                                </div>
                                <h2 class="text-xl font-bold text-white">Digital Business Card — Contact Info</h2>
                            </div>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-slate-400 text-sm mb-2">Company / Organisation</label>
                                    <input type="text" name="company_name" value="<?php echo htmlspecialchars($profile['company_name'] ?? ''); ?>"
                                        placeholder="e.g. Shawn Radam Advisory"
                                        class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                </div>
                                <div>
                                    <label class="block text-slate-400 text-sm mb-2">Email Address</label>
                                    <input type="email" name="email_address" value="<?php echo htmlspecialchars($profile['email_address'] ?? ''); ?>"
                                        placeholder="shawn@example.com"
                                        class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                </div>
                                <div>
                                    <label class="block text-slate-400 text-sm mb-2">Primary Phone</label>
                                    <input type="text" name="phone_primary" value="<?php echo htmlspecialchars($profile['phone_primary'] ?? ''); ?>"
                                        placeholder="012 8338 639"
                                        class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                </div>
                                <div>
                                    <label class="block text-slate-400 text-sm mb-2">Secondary Phone (WhatsApp)</label>
                                    <input type="text" name="phone_secondary" value="<?php echo htmlspecialchars($profile['phone_secondary'] ?? ''); ?>"
                                        placeholder="011 1633 9399"
                                        class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                </div>
                                <div>
                                    <label class="block text-slate-400 text-sm mb-2">WhatsApp Number (with country code)</label>
                                    <input type="text" name="whatsapp_number" value="<?php echo htmlspecialchars($profile['whatsapp_number'] ?? ''); ?>"
                                        placeholder="601283386392"
                                        class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                    <p class="text-slate-500 text-xs mt-1">No + or spaces. Used for WhatsApp direct link.</p>
                                </div>
                                <div>
                                    <label class="block text-slate-400 text-sm mb-2">Website URL</label>
                                    <input type="url" name="website_url" value="<?php echo htmlspecialchars($profile['website_url'] ?? ''); ?>"
                                        placeholder="https://shawnradam.com"
                                        class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-slate-400 text-sm mb-2">Address / Location</label>
                                    <input type="text" name="address_line" value="<?php echo htmlspecialchars($profile['address_line'] ?? ''); ?>"
                                        placeholder="Kota Kinabalu, Sabah, Malaysia"
                                        class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                </div>
                                <div>
                                    <label class="block text-slate-400 text-sm mb-2">LinkedIn URL</label>
                                    <input type="url" name="linkedin_url" value="<?php echo htmlspecialchars($profile['linkedin_url'] ?? ''); ?>"
                                        placeholder="https://linkedin.com/in/..."
                                        class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                </div>
                                <div>
                                    <label class="block text-slate-400 text-sm mb-2">Instagram URL</label>
                                    <input type="url" name="instagram_url" value="<?php echo htmlspecialchars($profile['instagram_url'] ?? ''); ?>"
                                        placeholder="https://instagram.com/..."
                                        class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                </div>
                                <div>
                                    <label class="block text-slate-400 text-sm mb-2">Facebook URL</label>
                                    <input type="url" name="facebook_url" value="<?php echo htmlspecialchars($profile['facebook_url'] ?? ''); ?>"
                                        placeholder="https://facebook.com/..."
                                        class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-slate-400 text-sm mb-2">Short Bio (shown on card)</label>
                                    <textarea name="card_bio" rows="3"
                                        placeholder="Personal Advisor specialising in..."
                                        class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white resize-none"><?php echo htmlspecialchars($profile['card_bio'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            <div class="mt-4 pt-4 border-t border-slate-700 flex items-center gap-3">
                                <a href="../card.php" target="_blank"
                                   class="inline-flex items-center gap-2 px-4 py-2 bg-gold-500 hover:bg-gold-400 text-navy-900 rounded-lg text-sm font-bold transition-colors">
                                    🪪 Preview Digital Card
                                </a>
                                <a href="../vcard.php" target="_blank"
                                   class="inline-flex items-center gap-2 px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg text-sm font-bold transition-colors">
                                    📥 Test VCF Download
                                </a>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end gap-4">
                            <a href="dashboard.php"
                                class="px-6 py-3 bg-slate-700 hover:bg-slate-600 text-white rounded-lg font-bold transition-colors">
                                Cancel
                            </a>
                            <button type="submit"
                                class="px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white rounded-lg font-bold transition-colors">
                                Save Changes
                            </button>
                        </div>

                    </form>

                </div>
            </main>
        </div>
    </div>
</body>

</html>