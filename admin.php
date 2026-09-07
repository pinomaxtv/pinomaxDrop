<?php
require 'config.php';

// 🛡️ ADMIN CHECKER: Ikaw lang ang makakapasok!
if (!isset($_SESSION['pd_user_id'])) {
    header("Location: login.php");
    exit;
}

$admin_id = $_SESSION['pd_user_id'];
$stmtA = $pdo->prepare("SELECT * FROM pd_users WHERE id = ? LIMIT 1");
$stmtA->execute([$admin_id]);
$admin = $stmtA->fetch();

// Pwede mong idagdag dito ang email mo:
$allowed_emails = ['admin@pinomax.tv', 'roderickalmaras05@gmail.com'];

if ($admin['id'] != 1 && !in_array(strtolower($admin['email']), $allowed_emails)) {
    die("<div style='background:#050608;color:#ff4757;height:100vh;display:flex;align-items:center;justify-content:center;font-family:sans-serif;font-weight:bold;font-size:18px;'>⚠️ ACCESS DENIED: Para lamang ito sa PinoDrop Administrator!</div>");
}

// ACTION: DELETE FILE
if (isset($_GET['del_file'])) {
    $del_id = (int)$_GET['del_file'];
    $pdo->prepare("DELETE FROM pd_files WHERE id = ?")->execute([$del_id]);
    header("Location: admin.php?msg=File+Deleted");
    exit;
}

// ACTION: APPROVE / REJECT CASHOUT
if (isset($_GET['cashout_action']) && isset($_GET['cid'])) {
    $cid = (int)$_GET['cid'];
    $action = $_GET['cashout_action'] === 'approve' ? 'approved' : 'rejected';
    
    // Kung rejected, ibalik ang balance kay user
    if ($action === 'rejected') {
        $c_info = $pdo->prepare("SELECT user_id, amount FROM pd_cashouts WHERE id = ?")->execute([$cid]);
        $c_data = $c_info->fetch();
        if ($c_data) {
            $pdo->prepare("UPDATE pd_users SET wallet_balance = wallet_balance + ? WHERE id = ?")->execute([$c_data['amount'], $c_data['user_id']]);
        }
    }
    
    $pdo->prepare("UPDATE pd_cashouts SET status = ? WHERE id = ?")->execute([$action, $cid]);
    header("Location: admin.php?msg=Cashout+Updated");
    exit;
}

// DATA FETCHING
$total_users = $pdo->query("SELECT COUNT(*) FROM pd_users")->fetchColumn();
$total_files = $pdo->query("SELECT COUNT(*) FROM pd_files")->fetchColumn();
$total_payouts = $pdo->query("SELECT SUM(amount) FROM pd_cashouts WHERE status = 'approved'")->fetchColumn() ?: 0;

$all_files = $pdo->query("SELECT f.*, u.username FROM pd_files f LEFT JOIN pd_users u ON f.user_id = u.id ORDER BY f.id DESC")->fetchAll();
$all_cashouts = $pdo->query("SELECT c.*, u.username, u.email FROM pd_cashouts c LEFT JOIN pd_users u ON c.user_id = u.id ORDER BY c.id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PinoDrop Admin Panel</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>* { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-[#050608] text-gray-200 min-h-screen p-4 sm:p-8">

    <div class="max-w-6xl mx-auto space-y-8">
        <!-- TOP NAV -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-[#0a0c10] border border-red-500/30 p-6 rounded-2xl shadow-[0_0_30px_rgba(229,9,20,0.15)]">
            <div>
                <span class="text-xs font-black text-red-500 uppercase tracking-widest bg-red-500/10 px-2 py-1 rounded">Super Admin</span>
                <h1 class="text-2xl font-black text-white mt-2">PinoDrop Master Control</h1>
                <p class="text-xs text-gray-500">Welcome, <?= esc($admin['username']) ?> (<?= esc($admin['email']) ?>)</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="index.php" class="text-xs bg-white/10 hover:bg-white/20 text-white font-bold px-4 py-2.5 rounded-xl transition-all">Store View</a>
                <a href="dashboard.php" class="text-xs bg-[#00e5ff] text-black font-bold px-4 py-2.5 rounded-xl transition-all">Creator Hub</a>
            </div>
        </div>

        <!-- STATS -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-[#0e1118] border border-white/5 p-5 rounded-2xl">
                <span class="text-xs text-gray-500 uppercase font-bold tracking-wider">Total Creators</span>
                <div class="text-2xl font-black text-white mt-1"><?= number_format($total_users) ?></div>
            </div>
            <div class="bg-[#0e1118] border border-white/5 p-5 rounded-2xl">
                <span class="text-xs text-gray-500 uppercase font-bold tracking-wider">Uploaded Assets</span>
                <div class="text-2xl font-black text-[#00e5ff] mt-1"><?= number_format($total_files) ?></div>
            </div>
            <div class="bg-[#0e1118] border border-white/5 p-5 rounded-2xl">
                <span class="text-xs text-gray-500 uppercase font-bold tracking-wider">Total Paid Out</span>
                <div class="text-2xl font-black text-[#2ecc71] mt-1">₱<?= number_format($total_payouts, 2) ?></div>
            </div>
        </div>

        <!-- 💰 CASHOUT REQUESTS SECTION -->
        <div class="bg-[#0a0c10] border border-white/10 rounded-2xl p-6 shadow-xl">
            <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <i class="fa-solid fa-money-bill-wave text-[#2ecc71]"></i> GCash &amp; PayPal Cashout Requests
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-white/5 text-gray-400 uppercase font-bold text-[10px] tracking-wider">
                        <tr>
                            <th class="p-3">User</th>
                            <th class="p-3">Amount</th>
                            <th class="p-3">Account Details</th>
                            <th class="p-3">Status</th>
                            <th class="p-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php if (count($all_cashouts) > 0): foreach($all_cashouts as $c): ?>
                        <tr class="hover:bg-white/[0.02]">
                            <td class="p-3 font-bold text-white"><?= esc($c['username']) ?> <br><span class="text-[10px] text-gray-500"><?= esc($c['email']) ?></span></td>
                            <td class="p-3 font-mono font-bold text-[#2ecc71]">₱<?= number_format($c['amount'], 2) ?></td>
                            <td class="p-3 text-gray-300 font-mono"><?= esc($c['method']) ?>: <?= esc($c['account_details']) ?></td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase <?= $c['status'] === 'approved' ? 'bg-green-500/20 text-green-400' : ($c['status'] === 'rejected' ? 'bg-red-500/20 text-red-400' : 'bg-yellow-500/20 text-yellow-400') ?>">
                                    <?= $c['status'] ?>
                                </span>
                            </td>
                            <td class="p-3 text-right space-x-2">
                                <?php if ($c['status'] === 'pending'): ?>
                                    <a href="admin.php?cashout_action=approve&cid=<?= $c['id'] ?>" onclick="return confirm('Approve this cashout?')" class="bg-[#2ecc71] hover:bg-green-600 text-black font-bold px-2.5 py-1 rounded text-[10px]">PAID</a>
                                    <a href="admin.php?cashout_action=reject&cid=<?= $c['id'] ?>" onclick="return confirm('Reject and refund balance?')" class="bg-red-600 hover:bg-red-700 text-white font-bold px-2.5 py-1 rounded text-[10px]">REJECT</a>
                                <?php else: ?>
                                    <span class="text-gray-600">Done</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="5" class="p-4 text-center text-gray-600">Walang pending cashout requests.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 📦 ALL FILES MANAGEMENT SECTION -->
        <div class="bg-[#0a0c10] border border-white/10 rounded-2xl p-6 shadow-xl">
            <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <i class="fa-solid fa-boxes-stacked text-[#00e5ff]"></i> Manage All Uploaded Assets
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-white/5 text-gray-400 uppercase font-bold text-[10px] tracking-wider">
                        <tr>
                            <th class="p-3">Asset Title</th>
                            <th class="p-3">Category</th>
                            <th class="p-3">Uploader</th>
                            <th class="p-3">Downloads</th>
                            <th class="p-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php foreach($all_files as $f): ?>
                        <tr class="hover:bg-white/[0.02]">
                            <td class="p-3 font-bold text-white"><?= esc($f['title']) ?> <br><span class="text-[10px] text-gray-500"><?= esc($f['file_size']) ?></span></td>
                            <td class="p-3"><span class="bg-[#00e5ff]/10 text-[#00e5ff] px-2 py-0.5 rounded text-[10px] font-bold"><?= esc($f['category']) ?></span></td>
                            <td class="p-3 text-gray-400"><?= esc($f['username'] ?? 'User') ?></td>
                            <td class="p-3 font-mono text-[#00e5ff]"><?= number_format($f['total_downloads']) ?></td>
                            <td class="p-3 text-right">
                                <a href="item.php?id=<?= $f['id'] ?>" target="_blank" class="text-gray-400 hover:text-white mr-3"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                                <a href="admin.php?del_file=<?= $f['id'] ?>" onclick="return confirm('Burahin ang file na ito?')" class="text-red-500 hover:text-red-400 font-bold"><i class="fa-solid fa-trash"></i> Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>