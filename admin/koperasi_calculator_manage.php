<?php
require_once 'db_connect.php';
require_once 'auth_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_presets') {
        try {
            $updateStmt = $pdo->prepare("UPDATE koperasi_presets SET 
                name = ?,
                interest_rate = ?, 
                min_loan = ?, 
                max_loan = ?, 
                min_tenure = ?, 
                max_tenure = ?, 
                processing_fee_percent = ?, 
                insurance_percent = ?, 
                membership_fee = ?, 
                advance_payment_months = ? 
                WHERE id = ?");

            foreach ($_POST['preset'] as $id => $data) {
                $updateStmt->execute([
                    $data['name'],
                    floatval($data['interest_rate']),
                    intval($data['min_loan']),
                    intval($data['max_loan']),
                    intval($data['min_tenure']),
                    intval($data['max_tenure']),
                    floatval($data['processing_fee_percent']),
                    floatval($data['insurance_percent']),
                    floatval($data['membership_fee']),
                    intval($data['advance_payment_months']),
                    $id
                ]);
            }
            header("Location: koperasi_calculator_manage.php?success=updated");
            exit;
        } catch (PDOException $ex) {
            $error_message = "Update failed: " . $ex->getMessage();
        }
    }
    
    if ($action === 'add_preset') {
        try {
            $new_name = trim($_POST['new_name'] ?? '');
            if (empty($new_name)) {
                throw new Exception("Cooperative Name is required.");
            }
            
            $id = strtolower(trim(preg_replace('/[^A-Za-z0-9_]/', '', str_replace(' ', '_', $new_name))));
            if (empty($id)) {
                $id = 'preset_' . time();
            }
            
            // Check if ID already exists
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM koperasi_presets WHERE id = ?");
            $checkStmt->execute([$id]);
            if ($checkStmt->fetchColumn() > 0) {
                $id = $id . '_' . time();
            }

            $insertStmt = $pdo->prepare("INSERT INTO koperasi_presets (id, name, interest_rate, min_loan, max_loan, min_tenure, max_tenure, processing_fee_percent, insurance_percent, membership_fee, advance_payment_months) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $insertStmt->execute([
                $id,
                $new_name,
                floatval($_POST['new_interest_rate'] ?? 4.0),
                intval($_POST['new_min_loan'] ?? 1000),
                intval($_POST['new_max_loan'] ?? 150000),
                intval($_POST['new_min_tenure'] ?? 1),
                intval($_POST['new_max_tenure'] ?? 10),
                floatval($_POST['new_processing_fee_percent'] ?? 5.0),
                floatval($_POST['new_insurance_percent'] ?? 2.5),
                floatval($_POST['new_membership_fee'] ?? 50.0),
                intval($_POST['new_advance_payment_months'] ?? 2)
            ]);
            
            header("Location: koperasi_calculator_manage.php?success=added");
            exit;
        } catch (Exception $ex) {
            $error_message = "Add cooperative failed: " . $ex->getMessage();
        }
    }
    
    if ($action === 'delete_preset') {
        try {
            $delete_id = $_POST['delete_id'] ?? '';
            $deleteStmt = $pdo->prepare("DELETE FROM koperasi_presets WHERE id = ?");
            $deleteStmt->execute([$delete_id]);
            header("Location: koperasi_calculator_manage.php?success=deleted");
            exit;
        } catch (PDOException $ex) {
            $error_message = "Delete cooperative failed: " . $ex->getMessage();
        }
    }
}

$presets = $pdo->query("SELECT * FROM koperasi_presets ORDER BY display_order ASC, name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = 'Koperasi Calculator Settings'; ?>
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
                <div class="max-w-5xl mx-auto">

                    <div class="flex justify-between items-end mb-8">
                        <div>
                            <h1 class="text-3xl font-serif text-white mb-2">Koperasi Calculator Settings</h1>
                            <p class="text-slate-400">Configure cooperative presets, interest rates, limits, and upfront fee breakdowns</p>
                        </div>
                    </div>

                    <?php if (isset($_GET['success'])): ?>
                        <div class="mb-6 bg-emerald-950/40 border border-emerald-500/50 text-emerald-400 px-4 py-3 rounded-lg flex items-center gap-2">
                            <span>âœ…</span>
                            <span>
                                <?php 
                                    if ($_GET['success'] === 'added') echo "New cooperative preset added successfully!";
                                    elseif ($_GET['success'] === 'deleted') echo "Cooperative preset deleted successfully!";
                                    else echo "Calculator settings updated successfully and bound to the website!";
                                ?>
                            </span>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($error_message)): ?>
                        <div class="mb-6 bg-red-950/40 border border-red-500/50 text-red-400 px-4 py-3 rounded-lg">
                            âŒ <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Add Cooperative Accordion -->
                    <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden shadow-lg mb-8" x-data="{ open: false }">
                        <div class="bg-slate-900/60 px-6 py-4 border-b border-slate-700 flex justify-between items-center">
                            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                                <span>âž•</span> Add New Cooperative Preset
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
                                        <label class="block text-slate-400 text-sm mb-2">Cooperative Name</label>
                                        <input type="text" name="new_name" required placeholder="e.g. Koperasi Sabah Permai"
                                            class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white outline-none focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-sm mb-2">Interest Rate (% p.a.)</label>
                                        <input type="number" step="0.01" min="0" max="25" name="new_interest_rate" value="4.00" required
                                            class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white outline-none focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-sm mb-2">Membership Entrance Fee (RM)</label>
                                        <input type="number" step="1" min="0" name="new_membership_fee" value="50" required
                                            class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white outline-none focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-sm mb-2">Minimum Loan (RM)</label>
                                        <input type="number" step="100" min="500" name="new_min_loan" value="1000" required
                                            class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white outline-none focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-sm mb-2">Maximum Loan (RM)</label>
                                        <input type="number" step="1000" min="1000" name="new_max_loan" value="150000" required
                                            class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white outline-none focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-sm mb-2">Advance Payments (Months)</label>
                                        <input type="number" min="0" max="6" name="new_advance_payment_months" value="2" required
                                            class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white outline-none focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-sm mb-2">Minimum Tenure (Years)</label>
                                        <input type="number" min="1" max="15" name="new_min_tenure" value="1" required
                                            class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white outline-none focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-sm mb-2">Maximum Tenure (Years)</label>
                                        <input type="number" min="1" max="15" name="new_max_tenure" value="10" required
                                            class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white outline-none focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-sm mb-2">Processing / Admin Fee (%)</label>
                                        <input type="number" step="0.1" min="0" max="20" name="new_processing_fee_percent" value="5.0" required
                                            class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white outline-none focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-sm mb-2">Takaful / Insurance Fee (%)</label>
                                        <input type="number" step="0.1" min="0" max="20" name="new_insurance_percent" value="2.5" required
                                            class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white outline-none focus:border-blue-500">
                                    </div>
                                </div>
                                <div class="flex justify-end mt-6">
                                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-6 py-2.5 rounded shadow cursor-pointer">
                                        Add Cooperative Preset
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <form method="POST" class="space-y-8">
                        <input type="hidden" name="action" value="update_presets">

                        <div class="grid grid-cols-1 gap-8">
                            <?php foreach ($presets as $preset): ?>
                                <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden shadow-lg">
                                    
                                    <div class="bg-slate-900/60 px-6 py-4 border-b border-slate-700 flex justify-between items-center gap-4">
                                        <div class="flex-grow">
                                            <input type="text" required
                                                name="preset[<?php echo htmlspecialchars($preset['id']); ?>][name]"
                                                value="<?php echo htmlspecialchars($preset['name']); ?>"
                                                class="bg-slate-950 border border-slate-700 rounded px-3 py-1.5 text-white font-bold text-base outline-none focus:border-blue-500 w-full max-w-md">
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <span class="text-xs text-slate-500 font-mono hidden sm:inline">ID: <?php echo htmlspecialchars($preset['id']); ?></span>
                                            <button type="button" onclick="confirmDelete('<?php echo htmlspecialchars($preset['id']); ?>', '<?php echo htmlspecialchars($preset['name'], ENT_QUOTES); ?>')"
                                                class="bg-rose-950/60 hover:bg-rose-900 text-rose-400 border border-rose-800/80 text-xs px-3 py-1.5 rounded transition-all cursor-pointer">
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                                        
                                        <div>
                                            <label class="block text-slate-400 text-sm mb-2">Default Interest Rate (% p.a.)</label>
                                            <input type="number" step="0.01" min="0" max="25" required
                                                name="preset[<?php echo htmlspecialchars($preset['id']); ?>][interest_rate]"
                                                value="<?php echo htmlspecialchars($preset['interest_rate']); ?>"
                                                class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white outline-none focus:border-blue-500">
                                        </div>

                                        <div>
                                            <label class="block text-slate-400 text-sm mb-2">Minimum Loan Amount (RM)</label>
                                            <input type="number" step="100" min="500" required
                                                name="preset[<?php echo htmlspecialchars($preset['id']); ?>][min_loan]"
                                                value="<?php echo htmlspecialchars($preset['min_loan']); ?>"
                                                class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white outline-none focus:border-blue-500">
                                        </div>

                                        <div>
                                            <label class="block text-slate-400 text-sm mb-2">Maximum Loan Amount (RM)</label>
                                            <input type="number" step="1000" min="1000" required
                                                name="preset[<?php echo htmlspecialchars($preset['id']); ?>][max_loan]"
                                                value="<?php echo htmlspecialchars($preset['max_loan']); ?>"
                                                class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white outline-none focus:border-blue-500">
                                        </div>

                                        <div>
                                            <label class="block text-slate-400 text-sm mb-2">Minimum Tenure (Years)</label>
                                            <input type="number" min="1" max="15" required
                                                name="preset[<?php echo htmlspecialchars($preset['id']); ?>][min_tenure]"
                                                value="<?php echo htmlspecialchars($preset['min_tenure']); ?>"
                                                class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white outline-none focus:border-blue-500">
                                        </div>

                                        <div>
                                            <label class="block text-slate-400 text-sm mb-2">Maximum Tenure (Years)</label>
                                            <input type="number" min="1" max="15" required
                                                name="preset[<?php echo htmlspecialchars($preset['id']); ?>][max_tenure]"
                                                value="<?php echo htmlspecialchars($preset['max_tenure']); ?>"
                                                class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white outline-none focus:border-blue-500">
                                        </div>

                                        <div>
                                            <label class="block text-slate-400 text-sm mb-2">Processing / Admin Fee (%)</label>
                                            <input type="number" step="0.1" min="0" max="20" required
                                                name="preset[<?php echo htmlspecialchars($preset['id']); ?>][processing_fee_percent]"
                                                value="<?php echo htmlspecialchars($preset['processing_fee_percent']); ?>"
                                                class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white outline-none focus:border-blue-500">
                                        </div>

                                        <div>
                                            <label class="block text-slate-400 text-sm mb-2">Takaful / Insurance Fee (%)</label>
                                            <input type="number" step="0.1" min="0" max="20" required
                                                name="preset[<?php echo htmlspecialchars($preset['id']); ?>][insurance_percent]"
                                                value="<?php echo htmlspecialchars($preset['insurance_percent']); ?>"
                                                class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white outline-none focus:border-blue-500">
                                        </div>

                                        <div>
                                            <label class="block text-slate-400 text-sm mb-2">Membership Entrance Fee (RM)</label>
                                            <input type="number" step="1" min="0" required
                                                name="preset[<?php echo htmlspecialchars($preset['id']); ?>][membership_fee]"
                                                value="<?php echo htmlspecialchars($preset['membership_fee']); ?>"
                                                class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white outline-none focus:border-blue-500">
                                        </div>

                                        <div>
                                            <label class="block text-slate-400 text-sm mb-2">Advance Payments (Months)</label>
                                            <input type="number" min="0" max="6" required
                                                name="preset[<?php echo htmlspecialchars($preset['id']); ?>][advance_payment_months]"
                                                value="<?php echo htmlspecialchars($preset['advance_payment_months']); ?>"
                                                class="w-full bg-slate-900 border border-slate-600 rounded px-4 py-2 text-white outline-none focus:border-blue-500">
                                        </div>

                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="flex justify-end pt-4">
                            <button type="submit"
                                class="bg-blue-500 hover:bg-blue-600 text-white font-bold px-8 py-3 rounded-lg shadow-lg hover:shadow-blue-500/30 transition-all cursor-pointer">
                                Save All Cooperative Settings
                            </button>
                        </div>
                    </form>

                </div>
            </main>

        </div>
    </div>

    <!-- Hidden form for cooperative deletion -->
    <form id="delete-preset-form" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete_preset">
        <input type="hidden" name="delete_id" id="delete-preset-id" value="">
    </form>

    <script>
        function confirmDelete(id, name) {
            if (confirm('Are you sure you want to delete cooperative preset "' + name + '"? This will remove it from the calculator.')) {
                document.getElementById('delete-preset-id').value = id;
                document.getElementById('delete-preset-form').submit();
            }
        }
    </script>
</body>
</html>
