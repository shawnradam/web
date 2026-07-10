<?php
require_once 'db_connect.php';
require_once 'auth_check.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_card') {
        try {
            $full_name = trim($_POST['full_name'] ?? '');
            if (empty($full_name)) {
                throw new Exception("Full Name is required.");
            }

            // Generate slug
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]/', '_', $full_name)));
            if (empty($slug)) {
                $slug = 'card_' . time();
            } else {
                $slug = $slug . '_' . time();
            }

            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $is_default = isset($_POST['is_default']) ? 1 : 0;

            // Handle default logic
            if ($is_default) {
                $pdo->exec("UPDATE digital_business_cards SET is_default = 0");
            }

            // Portrait Upload
            $portrait_url = '';
            if (isset($_FILES['portrait']) && $_FILES['portrait']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../assets/uploads/profile/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $fileName = time() . '_portrait_' . preg_replace('/[^a-zA-Z0-9.-]/', '_', $_FILES['portrait']['name']);
                if (move_uploaded_file($_FILES['portrait']['tmp_name'], $uploadDir . $fileName)) {
                    $portrait_url = 'assets/uploads/profile/' . $fileName;
                }
            }

            $stmt = $pdo->prepare("INSERT INTO digital_business_cards (
                card_slug, full_name, title, company_name, portrait_url, card_bio,
                phone_primary, phone_secondary, whatsapp_number, email_address,
                address_line, website_url, linkedin_url, instagram_url, facebook_url,
                expertise_tag_1, expertise_tag_2, expertise_tag_3,
                years_experience, deals_closed, rating,
                primary_button_text, primary_button_link,
                secondary_button_text, secondary_button_link,
                is_active, is_default
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->execute([
                $slug,
                $full_name,
                $_POST['title'] ?? '',
                $_POST['company_name'] ?? '',
                $portrait_url,
                $_POST['card_bio'] ?? '',
                $_POST['phone_primary'] ?? '',
                $_POST['phone_secondary'] ?? '',
                $_POST['whatsapp_number'] ?? '',
                $_POST['email_address'] ?? '',
                $_POST['address_line'] ?? '',
                $_POST['website_url'] ?? '',
                $_POST['linkedin_url'] ?? '',
                $_POST['instagram_url'] ?? '',
                $_POST['facebook_url'] ?? '',
                $_POST['expertise_tag_1'] ?? '',
                $_POST['expertise_tag_2'] ?? '',
                $_POST['expertise_tag_3'] ?? '',
                $_POST['years_experience'] ?? '',
                $_POST['deals_closed'] ?? '',
                $_POST['rating'] ?? '',
                $_POST['primary_button_text'] ?? '',
                $_POST['primary_button_link'] ?? '',
                $_POST['secondary_button_text'] ?? '',
                $_POST['secondary_button_link'] ?? '',
                $is_active,
                $is_default
            ]);

            header("Location: business_cards_manage.php?success=added");
            exit;
        } catch (Exception $e) {
            $error_message = "Add card failed: " . $e->getMessage();
        }
    }

    if ($action === 'update_card') {
        try {
            $id = intval($_POST['id'] ?? 0);
            $full_name = trim($_POST['full_name'] ?? '');
            $slug = trim($_POST['card_slug'] ?? '');
            if (empty($full_name) || empty($slug)) {
                throw new Exception("Full Name and Card Slug are required.");
            }

            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $is_default = isset($_POST['is_default']) ? 1 : 0;

            if ($is_default) {
                $pdo->exec("UPDATE digital_business_cards SET is_default = 0");
            }

            // Portrait Upload
            $portrait_url = $_POST['existing_portrait'] ?? '';
            if (isset($_FILES['portrait']) && $_FILES['portrait']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../assets/uploads/profile/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $fileName = time() . '_portrait_' . preg_replace('/[^a-zA-Z0-9.-]/', '_', $_FILES['portrait']['name']);
                if (move_uploaded_file($_FILES['portrait']['tmp_name'], $uploadDir . $fileName)) {
                    $portrait_url = 'assets/uploads/profile/' . $fileName;
                }
            }

            $stmt = $pdo->prepare("UPDATE digital_business_cards SET 
                card_slug = ?, full_name = ?, title = ?, company_name = ?, portrait_url = ?, card_bio = ?,
                phone_primary = ?, phone_secondary = ?, whatsapp_number = ?, email_address = ?,
                address_line = ?, website_url = ?, linkedin_url = ?, instagram_url = ?, facebook_url = ?,
                expertise_tag_1 = ?, expertise_tag_2 = ?, expertise_tag_3 = ?,
                years_experience = ?, deals_closed = ?, rating = ?,
                primary_button_text = ?, primary_button_link = ?,
                secondary_button_text = ?, secondary_button_link = ?,
                is_active = ?, is_default = ?
                WHERE id = ?");

            $stmt->execute([
                $slug,
                $full_name,
                $_POST['title'] ?? '',
                $_POST['company_name'] ?? '',
                $portrait_url,
                $_POST['card_bio'] ?? '',
                $_POST['phone_primary'] ?? '',
                $_POST['phone_secondary'] ?? '',
                $_POST['whatsapp_number'] ?? '',
                $_POST['email_address'] ?? '',
                $_POST['address_line'] ?? '',
                $_POST['website_url'] ?? '',
                $_POST['linkedin_url'] ?? '',
                $_POST['instagram_url'] ?? '',
                $_POST['facebook_url'] ?? '',
                $_POST['expertise_tag_1'] ?? '',
                $_POST['expertise_tag_2'] ?? '',
                $_POST['expertise_tag_3'] ?? '',
                $_POST['years_experience'] ?? '',
                $_POST['deals_closed'] ?? '',
                $_POST['rating'] ?? '',
                $_POST['primary_button_text'] ?? '',
                $_POST['primary_button_link'] ?? '',
                $_POST['secondary_button_text'] ?? '',
                $_POST['secondary_button_link'] ?? '',
                $is_active,
                $is_default,
                $id
            ]);

            header("Location: business_cards_manage.php?success=updated");
            exit;
        } catch (Exception $e) {
            $error_message = "Update failed: " . $e->getMessage();
        }
    }

    if ($action === 'delete_card') {
        try {
            $id = intval($_POST['delete_id'] ?? 0);
            
            // Check count - cannot delete if it's the last card
            $count = $pdo->query("SELECT COUNT(*) FROM digital_business_cards")->fetchColumn();
            if ($count <= 1) {
                throw new Exception("You must keep at least one digital business card.");
            }

            // Check if default
            $is_default = $pdo->query("SELECT is_default FROM digital_business_cards WHERE id = $id")->fetchColumn();
            
            $stmt = $pdo->prepare("DELETE FROM digital_business_cards WHERE id = ?");
            $stmt->execute([$id]);

            // If deleted default, make the first remaining card default
            if ($is_default) {
                $nextId = $pdo->query("SELECT id FROM digital_business_cards LIMIT 1")->fetchColumn();
                if ($nextId) {
                    $pdo->exec("UPDATE digital_business_cards SET is_default = 1 WHERE id = $nextId");
                }
            }

            header("Location: business_cards_manage.php?success=deleted");
            exit;
        } catch (Exception $e) {
            $error_message = "Delete failed: " . $e->getMessage();
        }
    }
}

// Fetch edit card details if requested
$editCard = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM digital_business_cards WHERE id = ?");
    $stmt->execute([$editId]);
    $editCard = $stmt->fetch();
}

$cards = $pdo->query("SELECT * FROM digital_business_cards ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = 'Manage Digital Business Cards'; ?>
    <?php include 'includes/admin_head.php'; ?>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
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
                <div class="max-w-5xl mx-auto" x-data="{ deleteModalOpen: false, deleteId: '', deleteName: '' }">

                    <div class="flex justify-between items-end mb-8">
                        <div>
                            <h1 class="text-3xl font-serif text-white mb-2">Digital Business Cards</h1>
                            <p class="text-slate-400">Manage multiple offline / online digital vCard setups</p>
                        </div>
                    </div>

                    <?php if (isset($_GET['success'])): ?>
                        <div class="mb-6 bg-emerald-950/40 border border-emerald-500/50 text-emerald-400 px-4 py-3 rounded-lg flex items-center gap-2">
                            <span>✅</span>
                            <span>
                                <?php 
                                    if ($_GET['success'] === 'added') echo "New digital business card created!";
                                    elseif ($_GET['success'] === 'deleted') echo "Digital card deleted successfully!";
                                    else echo "Card details updated successfully!";
                                ?>
                            </span>
                        </div>
                    <?php endif; ?>

                    <?php if ($error_message): ?>
                        <div class="mb-6 bg-red-950/40 border border-red-500/50 text-red-400 px-4 py-3 rounded-lg">
                            ❌ <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Form section (Add/Edit) -->
                    <?php if ($editCard): ?>
                        <div class="bg-slate-800 rounded-xl border border-gold-500/40 overflow-hidden shadow-lg mb-8">
                            <div class="bg-slate-900/60 px-6 py-4 border-b border-gold-500/30 flex justify-between items-center">
                                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                                    <span>📝</span> Edit Card: <?= htmlspecialchars($editCard['full_name']) ?>
                                </h2>
                                <a href="business_cards_manage.php" class="text-xs bg-slate-700 hover:bg-slate-600 px-3 py-1.5 rounded text-white font-bold transition-all">Cancel Edit</a>
                            </div>
                            <form method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                                <input type="hidden" name="action" value="update_card">
                                <input type="hidden" name="id" value="<?= $editCard['id'] ?>">
                                <input type="hidden" name="existing_portrait" value="<?= htmlspecialchars($editCard['portrait_url'] ?? '') ?>">

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label class="block text-slate-400 text-sm mb-2">Full Name *</label>
                                        <input type="text" name="full_name" required value="<?= htmlspecialchars($editCard['full_name']) ?>"
                                            class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-sm mb-2">Card URL Slug *</label>
                                        <input type="text" name="card_slug" required value="<?= htmlspecialchars($editCard['card_slug']) ?>"
                                            class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white font-mono">
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-sm mb-2">Job Title / Designation</label>
                                        <input type="text" name="title" value="<?= htmlspecialchars($editCard['title'] ?? '') ?>"
                                            class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-sm mb-2">Company Name</label>
                                        <input type="text" name="company_name" value="<?= htmlspecialchars($editCard['company_name'] ?? '') ?>"
                                            class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-sm mb-2">Primary Phone</label>
                                        <input type="text" name="phone_primary" value="<?= htmlspecialchars($editCard['phone_primary'] ?? '') ?>"
                                            class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-sm mb-2">Secondary Phone (WhatsApp)</label>
                                        <input type="text" name="phone_secondary" value="<?= htmlspecialchars($editCard['phone_secondary'] ?? '') ?>"
                                            class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-sm mb-2">WhatsApp Direct Link Number (with country code)</label>
                                        <input type="text" name="whatsapp_number" value="<?= htmlspecialchars($editCard['whatsapp_number'] ?? '') ?>"
                                            placeholder="e.g. 601283386392"
                                            class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-sm mb-2">Email Address</label>
                                        <input type="email" name="email_address" value="<?= htmlspecialchars($editCard['email_address'] ?? '') ?>"
                                            class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-sm mb-2">Website URL</label>
                                        <input type="url" name="website_url" value="<?= htmlspecialchars($editCard['website_url'] ?? '') ?>"
                                            class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                    </div>
                                    <div class="md:col-span-3">
                                        <label class="block text-slate-400 text-sm mb-2">Location Address</label>
                                        <input type="text" name="address_line" value="<?= htmlspecialchars($editCard['address_line'] ?? '') ?>"
                                            class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-sm mb-2">LinkedIn Profile URL</label>
                                        <input type="url" name="linkedin_url" value="<?= htmlspecialchars($editCard['linkedin_url'] ?? '') ?>"
                                            class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-sm mb-2">Instagram URL</label>
                                        <input type="url" name="instagram_url" value="<?= htmlspecialchars($editCard['instagram_url'] ?? '') ?>"
                                            class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-sm mb-2">Facebook URL</label>
                                        <input type="url" name="facebook_url" value="<?= htmlspecialchars($editCard['facebook_url'] ?? '') ?>"
                                            class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-sm mb-2">Expertise Tags</label>
                                        <div class="flex gap-2">
                                            <input type="text" name="expertise_tag_1" placeholder="Tag 1" value="<?= htmlspecialchars($editCard['expertise_tag_1'] ?? '') ?>" class="w-1/3 bg-slate-900 border border-slate-600 rounded px-2 py-1.5 text-xs text-white">
                                            <input type="text" name="expertise_tag_2" placeholder="Tag 2" value="<?= htmlspecialchars($editCard['expertise_tag_2'] ?? '') ?>" class="w-1/3 bg-slate-900 border border-slate-600 rounded px-2 py-1.5 text-xs text-white">
                                            <input type="text" name="expertise_tag_3" placeholder="Tag 3" value="<?= htmlspecialchars($editCard['expertise_tag_3'] ?? '') ?>" class="w-1/3 bg-slate-900 border border-slate-600 rounded px-2 py-1.5 text-xs text-white">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-sm mb-2">Experience / Deals / Rating</label>
                                        <div class="flex gap-2">
                                            <input type="text" name="years_experience" placeholder="12+" value="<?= htmlspecialchars($editCard['years_experience'] ?? '') ?>" class="w-1/3 bg-slate-900 border border-slate-600 rounded px-2 py-1.5 text-xs text-white">
                                            <input type="text" name="deals_closed" placeholder="250+" value="<?= htmlspecialchars($editCard['deals_closed'] ?? '') ?>" class="w-1/3 bg-slate-900 border border-slate-600 rounded px-2 py-1.5 text-xs text-white">
                                            <input type="text" name="rating" placeholder="5.0" value="<?= htmlspecialchars($editCard['rating'] ?? '') ?>" class="w-1/3 bg-slate-900 border border-slate-600 rounded px-2 py-1.5 text-xs text-white">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-sm mb-2">Portrait Profile Image</label>
                                        <input type="file" name="portrait" accept="image/*" class="w-full text-xs text-white">
                                    </div>
                                    <div class="md:col-span-3">
                                        <label class="block text-slate-400 text-sm mb-2">Short Bio</label>
                                        <textarea name="card_bio" rows="2" class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white resize-none"><?= htmlspecialchars($editCard['card_bio'] ?? '') ?></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-sm mb-2">Primary CTA Button</label>
                                        <div class="flex gap-2">
                                            <input type="text" name="primary_button_text" placeholder="Text" value="<?= htmlspecialchars($editCard['primary_button_text'] ?? '') ?>" class="w-1/2 bg-slate-900 border border-slate-600 rounded px-2 py-1.5 text-xs text-white">
                                            <input type="text" name="primary_button_link" placeholder="Link" value="<?= htmlspecialchars($editCard['primary_button_link'] ?? '') ?>" class="w-1/2 bg-slate-900 border border-slate-600 rounded px-2 py-1.5 text-xs text-white">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-sm mb-2">Secondary CTA Button</label>
                                        <div class="flex gap-2">
                                            <input type="text" name="secondary_button_text" placeholder="Text" value="<?= htmlspecialchars($editCard['secondary_button_text'] ?? '') ?>" class="w-1/2 bg-slate-900 border border-slate-600 rounded px-2 py-1.5 text-xs text-white">
                                            <input type="text" name="secondary_button_link" placeholder="Link" value="<?= htmlspecialchars($editCard['secondary_button_link'] ?? '') ?>" class="w-1/2 bg-slate-900 border border-slate-600 rounded px-2 py-1.5 text-xs text-white">
                                        </div>
                                    </div>
                                    <div class="flex gap-6 items-center pt-6">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="is_active" value="1" <?= $editCard['is_active'] ? 'checked' : '' ?> class="w-4 h-4 accent-blue-500">
                                            <span>Active Card</span>
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="is_default" value="1" <?= $editCard['is_default'] ? 'checked' : '' ?> class="w-4 h-4 accent-blue-500">
                                            <span>Set Default Card</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="flex justify-end pt-4">
                                    <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold px-8 py-3 rounded-lg shadow cursor-pointer">
                                        Update Card Details
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php else: ?>
                        <!-- Add Form Accordion -->
                        <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden shadow-lg mb-8" x-data="{ open: false }">
                            <div class="bg-slate-900/60 px-6 py-4 border-b border-slate-700 flex justify-between items-center">
                                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                                    <span>➕</span> Add New Digital Business Card
                                </h2>
                                <button type="button" @click="open = !open" 
                                    class="bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold px-3 py-1.5 rounded transition-all cursor-pointer">
                                    <span x-show="!open">Expand Form</span>
                                    <span x-show="open">Collapse Form</span>
                                </button>
                            </div>
                            <div class="p-6" x-show="open" x-collapse style="display: none;">
                                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                                    <input type="hidden" name="action" value="add_card">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        <div>
                                            <label class="block text-slate-400 text-sm mb-2">Full Name *</label>
                                            <input type="text" name="full_name" required placeholder="e.g. Shawn Radam"
                                                class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                        </div>
                                        <div>
                                            <label class="block text-slate-400 text-sm mb-2">Job Title / Designation</label>
                                            <input type="text" name="title" placeholder="e.g. Private Advisory"
                                                class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                        </div>
                                        <div>
                                            <label class="block text-slate-400 text-sm mb-2">Company Name</label>
                                            <input type="text" name="company_name" placeholder="e.g. Shawn Radam Advisory"
                                                class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                        </div>
                                        <div>
                                            <label class="block text-slate-400 text-sm mb-2">Primary Phone</label>
                                            <input type="text" name="phone_primary" placeholder="012-833 8639"
                                                class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                        </div>
                                        <div>
                                            <label class="block text-slate-400 text-sm mb-2">Secondary Phone (WhatsApp)</label>
                                            <input type="text" name="phone_secondary" placeholder="011-1633 9399"
                                                class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                        </div>
                                        <div>
                                            <label class="block text-slate-400 text-sm mb-2">WhatsApp Direct Number (digits only)</label>
                                            <input type="text" name="whatsapp_number" placeholder="601283386392"
                                                class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                        </div>
                                        <div>
                                            <label class="block text-slate-400 text-sm mb-2">Email Address</label>
                                            <input type="email" name="email_address" placeholder="admin@shawnradam.com"
                                                class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                        </div>
                                        <div>
                                            <label class="block text-slate-400 text-sm mb-2">Website URL</label>
                                            <input type="url" name="website_url" value="https://shawnradam.com"
                                                class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                        </div>
                                        <div>
                                            <label class="block text-slate-400 text-sm mb-2">Portrait Profile Image</label>
                                            <input type="file" name="portrait" accept="image/*" class="w-full text-xs text-white mt-1">
                                        </div>
                                        <div class="md:col-span-3">
                                            <label class="block text-slate-400 text-sm mb-2">Location Address</label>
                                            <input type="text" name="address_line" placeholder="Kota Kinabalu, Sabah, Malaysia"
                                                class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                        </div>
                                        <div>
                                            <label class="block text-slate-400 text-sm mb-2">LinkedIn Profile URL</label>
                                            <input type="url" name="linkedin_url" class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                        </div>
                                        <div>
                                            <label class="block text-slate-400 text-sm mb-2">Instagram URL</label>
                                            <input type="url" name="instagram_url" class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                        </div>
                                        <div>
                                            <label class="block text-slate-400 text-sm mb-2">Facebook URL</label>
                                            <input type="url" name="facebook_url" class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white">
                                        </div>
                                        <div>
                                            <label class="block text-slate-400 text-sm mb-2">Expertise Tags</label>
                                            <div class="flex gap-2">
                                                <input type="text" name="expertise_tag_1" placeholder="Tag 1" value="Asset Acquisition" class="w-1/3 bg-slate-900 border border-slate-600 rounded px-2 py-1 text-xs text-white">
                                                <input type="text" name="expertise_tag_2" placeholder="Tag 2" value="Structured Lending" class="w-1/3 bg-slate-900 border border-slate-600 rounded px-2 py-1 text-xs text-white">
                                                <input type="text" name="expertise_tag_3" placeholder="Tag 3" value="Travel Logistics" class="w-1/3 bg-slate-900 border border-slate-600 rounded px-2 py-1 text-xs text-white">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-slate-400 text-sm mb-2">Experience / Deals / Rating</label>
                                            <div class="flex gap-2">
                                                <input type="text" name="years_experience" placeholder="12+" value="12+" class="w-1/3 bg-slate-900 border border-slate-600 rounded px-2 py-1 text-xs text-white">
                                                <input type="text" name="deals_closed" placeholder="250+" value="250+" class="w-1/3 bg-slate-900 border border-slate-600 rounded px-2 py-1 text-xs text-white">
                                                <input type="text" name="rating" placeholder="5.0" value="5.0" class="w-1/3 bg-slate-900 border border-slate-600 rounded px-2 py-1 text-xs text-white">
                                            </div>
                                        </div>
                                        <div class="flex gap-6 items-center pt-6">
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 accent-blue-500">
                                                <span>Active Card</span>
                                            </label>
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="checkbox" name="is_default" value="1" class="w-4 h-4 accent-blue-500">
                                                <span>Set Default</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="flex justify-end mt-4">
                                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-6 py-2.5 rounded shadow cursor-pointer">
                                            Create Card Preset
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Cards List -->
                    <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden shadow-lg">
                        <div class="px-6 py-4 border-b border-slate-700 bg-slate-900/60">
                            <h2 class="text-lg font-bold text-white">Active Digital Cards</h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-700 bg-slate-900/30 text-slate-400 text-xs uppercase tracking-wider">
                                        <th class="px-6 py-4">Avatar</th>
                                        <th class="px-6 py-4">Name</th>
                                        <th class="px-6 py-4">Title</th>
                                        <th class="px-6 py-4">Slug / Link</th>
                                        <th class="px-6 py-4">Status</th>
                                        <th class="px-6 py-4 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-700 text-sm">
                                    <?php foreach ($cards as $card): ?>
                                        <tr class="hover:bg-slate-700/20">
                                            <td class="px-6 py-4">
                                                <?php if ($card['portrait_url']): ?>
                                                    <img src="../<?= htmlspecialchars($card['portrait_url']) ?>" class="w-10 h-10 rounded-full object-cover border border-slate-600">
                                                <?php else: ?>
                                                    <div class="w-10 h-10 rounded-full bg-slate-900 flex items-center justify-center font-bold text-gold-500 border border-slate-700">
                                                        <?= strtoupper(substr($card['full_name'],0,2)) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 font-bold text-white"><?= htmlspecialchars($card['full_name']) ?></td>
                                            <td class="px-6 py-4 text-slate-400"><?= htmlspecialchars($card['title'] ?? '') ?></td>
                                            <td class="px-6 py-4">
                                                <a href="../card.php?slug=<?= urlencode($card['card_slug']) ?>" target="_blank" class="text-blue-400 hover:underline font-mono text-xs">
                                                    /card.php?slug=<?= htmlspecialchars($card['card_slug']) ?>
                                                </a>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex flex-wrap gap-1.5">
                                                    <?php if ($card['is_default']): ?>
                                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-gold-500/20 text-gold-400 border border-gold-500/30">DEFAULT</span>
                                                    <?php endif; ?>
                                                    <?php if ($card['is_active']): ?>
                                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">ACTIVE</span>
                                                    <?php else: ?>
                                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-rose-500/20 text-rose-400 border border-rose-500/30">INACTIVE</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-right space-x-1">
                                                <a href="business_cards_manage.php?edit=<?= $card['id'] ?>" class="bg-blue-600/20 hover:bg-blue-600/40 text-blue-400 border border-blue-800 text-xs px-2.5 py-1 rounded inline-block font-bold">Edit</a>
                                                <button type="button" @click="deleteId = '<?= $card['id'] ?>'; deleteName = '<?= htmlspecialchars($card['full_name'], ENT_QUOTES) ?>'; deleteModalOpen = true;" class="bg-rose-900/40 hover:bg-rose-900 text-rose-400 border border-rose-800 text-xs px-2.5 py-1 rounded font-bold cursor-pointer">Delete</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Delete Confirmation Modal -->
                    <div x-show="deleteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/75 backdrop-blur-sm p-4" x-cloak>
                        <div class="bg-slate-800 border border-slate-700 rounded-xl max-w-md w-full p-6 shadow-2xl" @click.away="deleteModalOpen = false">
                            <h3 class="text-xl font-serif text-white mb-2">Delete Digital Card?</h3>
                            <p class="text-slate-400 text-sm mb-6">Are you sure you want to permanently delete the card for <span class="text-white font-bold" x-text="deleteName"></span>?</p>
                            <form method="POST">
                                <input type="hidden" name="action" value="delete_card">
                                <input type="hidden" name="delete_id" :value="deleteId">
                                <div class="flex justify-end gap-3">
                                    <button type="button" @click="deleteModalOpen = false" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded font-bold cursor-pointer">
                                        Cancel
                                    </button>
                                    <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded font-bold cursor-pointer">
                                        Delete Card
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>
</body>
</html>
