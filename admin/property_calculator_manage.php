<?php
require_once 'db_connect.php';
require_once 'auth_check.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_presets') {
        try {
            $updateStmt = $pdo->prepare("UPDATE property_calculator_presets SET 
                name = ?,
                interest_rate_default = ?, 
                min_price = ?, 
                max_price = ?, 
                min_tenure = ?, 
                max_tenure = ?, 
                down_payment_pct = ?, 
                premium_rate = ?, 
                notes = ?,
                is_active = ?,
                display_order = ?
                WHERE id = ?");

            foreach ($_POST['preset'] as $id => $data) {
                $is_active = isset($data['is_active']) ? 1 : 0;
                $updateStmt->execute([
                    $data['name'],
                    floatval($data['interest_rate_default'] ?? 4.50),
                    floatval($data['min_price'] ?? 0.00),
                    floatval($data['max_price'] ?? 0.00),
                    intval($data['min_tenure'] ?? 5),
                    intval($data['max_tenure'] ?? 35),
                    floatval($data['down_payment_pct'] ?? 10.00),
                    floatval($data['premium_rate'] ?? 0.00),
                    $data['notes'] ?? '',
                    $is_active,
                    intval($data['display_order'] ?? 0),
                    $id
                ]);
            }
            header("Location: property_calculator_manage.php?success=updated");
            exit;
        } catch (PDOException $ex) {
            $error_message = "Update failed: " . $ex->getMessage();
        }
    }
    
    if ($action === 'add_preset') {
        try {
            $new_name = trim($_POST['new_name'] ?? '');
            $category = $_POST['new_category'] ?? 'mortgage';
            $region = $_POST['new_region'] ?? 'kl';
            
            if (empty($new_name)) {
                throw new Exception("Preset Name is required.");
            }
            
            $id = strtolower(trim(preg_replace('/[^A-Za-z0-9_]/', '', str_replace(' ', '_', $new_name))));
            if (empty($id)) {
                $id = 'preset_' . time();
            }
            
            // Check if ID already exists
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM property_calculator_presets WHERE id = ?");
            $checkStmt->execute([$id]);
            if ($checkStmt->fetchColumn() > 0) {
                $id = $id . '_' . time();
            }

            $insertStmt = $pdo->prepare("INSERT INTO property_calculator_presets 
                (id, name, category, region, interest_rate_default, min_price, max_price, min_tenure, max_tenure, down_payment_pct, premium_rate, notes, is_active, display_order) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 0)");
            $insertStmt->execute([
                $id,
                $new_name,
                $category,
                $region,
                floatval($_POST['new_interest_rate_default'] ?? 4.50),
                floatval($_POST['new_min_price'] ?? 0.00),
                floatval($_POST['new_max_price'] ?? 0.00),
                intval($_POST['new_min_tenure'] ?? 5),
                intval($_POST['new_max_tenure'] ?? 35),
                floatval($_POST['new_down_payment_pct'] ?? 10.00),
                floatval($_POST['new_premium_rate'] ?? 0.00),
                $_POST['new_notes'] ?? ''
            ]);
            
            header("Location: property_calculator_manage.php?success=added");
            exit;
        } catch (Exception $ex) {
            $error_message = "Add preset failed: " . $ex->getMessage();
        }
    }
    
    if ($action === 'delete_preset') {
        try {
            $delete_id = $_POST['delete_id'] ?? '';
            $deleteStmt = $pdo->prepare("DELETE FROM property_calculator_presets WHERE id = ?");
            $deleteStmt->execute([$delete_id]);
            header("Location: property_calculator_manage.php?success=deleted");
            exit;
        } catch (PDOException $ex) {
            $error_message = "Delete failed: " . $ex->getMessage();
        }
    }
}

$presets = $pdo->query("SELECT * FROM property_calculator_presets ORDER BY region ASC, category ASC, display_order ASC, name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = 'Property Calculator Presets'; ?>
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
                <div class="max-w-5xl mx-auto" x-data="{ deleteModalOpen: false, deleteId: '', deleteName: '' }">

                    <div class="flex justify-between items-end mb-8">
                        <div>
                            <h1 class="text-3xl font-serif text-white mb-2">Property Calculator Presets</h1>
                            <p class="text-slate-400">Configure parameters for Stamp Duty, Mortgage, Rental Yield, and land premiums</p>
                        </div>
                    </div>

                    <?php if (isset($_GET['success'])): ?>
                        <div class="mb-6 bg-emerald-950/40 border border-emerald-500/50 text-emerald-400 px-4 py-3 rounded-lg flex items-center gap-2">
                            <span>âœ…</span>
                            <span>
                                <?php 
                                    if ($_GET['success'] === 'added') echo "New calculator preset added successfully!";
                                    elseif ($_GET['success'] === 'deleted') echo "Preset deleted successfully!";
                                    else echo "Calculator presets updated successfully!";
                                ?>
                            </span>
                        </div>
                    <?php endif; ?>

                    <?php if ($error_message): ?>
                        <div class="mb-6 bg-red-950/40 border border-red-500/50 text-red-400 px-4 py-3 rounded-lg">
                            âŒ <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Add Preset Form -->
                    <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden shadow-lg mb-8" x-data="{ open: false }">
                        <div class="bg-slate-900/60 px-6 py-4 border-b border-slate-700 flex justify-between items-center">
                            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                                <span>âž•</span> Add New Calculator Preset
                            </h2>
                            <button type="button" @click="open = !open" 
                                class="bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold px-3 py-1.5 rounded transition-all cursor-pointer">
                                <span x-show="!open">Expand Form</span>
                                <span x-show="open">Collapse Form</span>
                            </button>
                        </div>
                        <div class="p-6" x-show="open" x-collapse style="display: none;">
                            <form method="POST">
                                <input type="hidden" name="action" value="add_preset">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label class="block text-slate-400 text-sm mb-2">Preset Name *</label>
                                        <input type="text" name="new_name" required placeholder="e.g. Maybank Mortgage Promo"
                                            class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white outline-none focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-sm mb-2">Category *</label>
                                        <select name="new_category" required
                                            class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white outline-none focus:border-blue-500">
                                            <option value="stamp_duty">Stamp Duty & Fees</option>
                                            <option value="mortgage">Mortgage Loan</option>
                                            <option value="rental_yield">Rental Yield</option>
                                            <option value="land_premium">Sabah Land Premium</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-sm mb-2">Region *</label>
                                        <select name="new_region" required
                                            class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white outline-none focus:border-blue-500">
                                            <option value="kl">KL / Semenanjung</option>
                                            <option value="sabah">Sabah</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-sm mb-2">Default Interest Rate (% p.a.)</label>
                                        <input type="number" step="0.01" name="new_interest_rate_default" value="4.50"
                                            class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white outline-none focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-sm mb-2">Default Down Payment (%)</label>
                                        <input type="number" step="1" name="new_down_payment_pct" value="10"
                                            class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white outline-none focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-sm mb-2">Premium Conversion Rate (%)</label>
                                        <input type="number" step="0.1" name="new_premium_rate" value="0.0"
                                            class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white outline-none focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-sm mb-2">Min Tenure / Max Tenure (Years)</label>
                                        <div class="flex gap-2">
                                            <input type="number" name="new_min_tenure" value="5" placeholder="Min"
                                                class="w-1/2 bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white outline-none">
                                            <input type="number" name="new_max_tenure" value="35" placeholder="Max"
                                                class="w-1/2 bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white outline-none">
                                        </div>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-slate-400 text-sm mb-2">Notes / Description</label>
                                        <input type="text" name="new_notes" placeholder="Internal notes about this preset"
                                            class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white outline-none">
                                    </div>
                                </div>
                                <div class="flex justify-end mt-6">
                                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-6 py-2.5 rounded shadow cursor-pointer">
                                        Add Preset
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Presets Form -->
                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="action" value="update_presets">

                        <div class="space-y-6">
                            <?php foreach ($presets as $preset): ?>
                                <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden shadow-lg">
                                    <div class="bg-slate-900/60 px-6 py-4 border-b border-slate-700 flex justify-between items-center gap-4">
                                        <div class="flex-grow flex items-center gap-3">
                                            <span class="text-xs font-bold uppercase px-2 py-1 rounded bg-slate-950 text-gold-500 border border-gold-500/20">
                                                <?= strtoupper($preset['region']) ?> / <?= str_replace('_', ' ', strtoupper($preset['category'])) ?>
                                            </span>
                                            <input type="text" required
                                                name="preset[<?php echo htmlspecialchars($preset['id']); ?>][name]"
                                                value="<?php echo htmlspecialchars($preset['name']); ?>"
                                                class="bg-slate-950 border border-slate-700 rounded px-3 py-1.5 text-white font-bold outline-none focus:border-blue-500 flex-grow max-w-md">
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <button type="button" @click="deleteId = '<?php echo htmlspecialchars($preset['id']); ?>'; deleteName = '<?php echo htmlspecialchars($preset['name'], ENT_QUOTES); ?>'; deleteModalOpen = true;"
                                                class="bg-rose-950/60 hover:bg-rose-900 text-rose-400 border border-rose-800/80 text-xs px-3 py-1.5 rounded transition-all cursor-pointer">
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                    <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                                        <div>
                                            <label class="block text-slate-400 text-xs uppercase tracking-wider mb-2">Default Interest Rate (%)</label>
                                            <input type="number" step="0.01" name="preset[<?php echo htmlspecialchars($preset['id']); ?>][interest_rate_default]" value="<?php echo htmlspecialchars($preset['interest_rate_default']); ?>"
                                                class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-1.5 text-white">
                                        </div>
                                        <div>
                                            <label class="block text-slate-400 text-xs uppercase tracking-wider mb-2">Default Down Payment (%)</label>
                                            <input type="number" step="1" name="preset[<?php echo htmlspecialchars($preset['id']); ?>][down_payment_pct]" value="<?php echo htmlspecialchars($preset['down_payment_pct']); ?>"
                                                class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-1.5 text-white">
                                        </div>
                                        <div>
                                            <label class="block text-slate-400 text-xs uppercase tracking-wider mb-2">Conversion Premium Rate (%)</label>
                                            <input type="number" step="0.1" name="preset[<?php echo htmlspecialchars($preset['id']); ?>][premium_rate]" value="<?php echo htmlspecialchars($preset['premium_rate']); ?>"
                                                class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-1.5 text-white">
                                        </div>
                                        <div>
                                            <label class="block text-slate-400 text-xs uppercase tracking-wider mb-2">Order / Active</label>
                                            <div class="flex items-center gap-4 mt-2">
                                                <input type="number" name="preset[<?php echo htmlspecialchars($preset['id']); ?>][display_order]" value="<?php echo htmlspecialchars($preset['display_order']); ?>"
                                                    class="w-20 bg-slate-900 border border-slate-700 rounded px-3 py-1 text-white">
                                                <label class="flex items-center gap-2 cursor-pointer">
                                                    <input type="checkbox" name="preset[<?php echo htmlspecialchars($preset['id']); ?>][is_active]" value="1" <?php echo $preset['is_active'] ? 'checked' : ''; ?>
                                                        class="accent-blue-500 w-4 h-4">
                                                    <span class="text-sm">Active</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="md:col-span-4">
                                            <label class="block text-slate-400 text-xs uppercase tracking-wider mb-2">Notes / Description</label>
                                            <input type="text" name="preset[<?php echo htmlspecialchars($preset['id']); ?>][notes]" value="<?php echo htmlspecialchars($preset['notes'] ?? ''); ?>"
                                                class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-1.5 text-white">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="flex justify-end pt-4">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold px-8 py-3 rounded-lg shadow-lg cursor-pointer transition-all">
                                Save All Calculator Changes
                            </button>
                        </div>
                    </form>

                    <!-- Delete Confirmation Modal -->
                    <div x-show="deleteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/75 backdrop-blur-sm p-4" x-cloak>
                        <div class="bg-slate-800 border border-slate-700 rounded-xl max-w-md w-full p-6 shadow-2xl" @click.away="deleteModalOpen = false">
                            <h3 class="text-xl font-serif text-white mb-2">Delete Preset?</h3>
                            <p class="text-slate-400 text-sm mb-6">Are you sure you want to permanently delete the preset <span class="text-white font-bold" x-text="deleteName"></span>?</p>
                            <form method="POST">
                                <input type="hidden" name="action" value="delete_preset">
                                <input type="hidden" name="delete_id" :value="deleteId">
                                <div class="flex justify-end gap-3">
                                    <button type="button" @click="deleteModalOpen = false" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded font-bold cursor-pointer">
                                        Cancel
                                    </button>
                                    <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded font-bold cursor-pointer">
                                        Delete Preset
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
