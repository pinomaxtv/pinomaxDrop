<?php
require 'config.php';

// 🛡️ ADMIN CHECKER
if (!isset($_SESSION['pd_user_id'])) {
    header("Location: login.php");
    exit;
}

$admin_id = $_SESSION['pd_user_id'];
$stmtA = $pdo->prepare("SELECT * FROM pd_users WHERE id = ? LIMIT 1");
$stmtA->execute([$admin_id]);
$admin = $stmtA->fetch();

$allowed_emails = ['admin@pinomax.tv', 'roderickalmaras05@gmail.com'];

if ($admin['id'] != 1 && !in_array(strtolower($admin['email']), $allowed_emails)) {
    die("<div style='background:#050608;color:#ff4757;height:100vh;display:flex;align-items:center;justify-content:center;font-family:sans-serif;font-weight:bold;font-size:18px;'>⚠️ ACCESS DENIED: Para lamang ito sa PinoDrop Administrator!</div>");
}

$msg = $_GET['msg'] ?? '';

// 🗑️ ACTION: DELETE FILE
if (isset($_GET['del_file'])) {
    $del_id = (int)$_GET['del_file'];
    $pdo->prepare("DELETE FROM pd_files WHERE id = ?")->execute([$del_id]);
    $back = isset($_GET['return_user']) ? "admin.php?view_user=" . (int)$_GET['return_user'] . "&msg=File+Deleted" : "admin.php?msg=File+Deleted";
    header("Location: $back");
    exit;
}

// 💰 ACTION: APPROVE / REJECT CASHOUT
if (isset($_GET['cashout_action']) && isset($_GET['cid'])) {
    $cid = (int)$_GET['cid'];
    $action = $_GET['cashout_action'] === 'approve' ? 'approved' : 'rejected';
    
    // Kumuha ng cashout info
    $c_info = $pdo->prepare("SELECT user_id, amount, status FROM pd_cashouts WHERE id = ?");
    $c_info->execute([$cid]);
    $c_data = $c_info->fetch();

    if ($c_data && $c_data['status'] === 'pending') {
        if ($action === 'rejected') {
            $pdo->prepare("UPDATE pd_users SET wallet_balance = wallet_balance + ? WHERE id = ?")->execute([$c_data['amount'], $c_data['user_id']]);
        }
        $pdo->prepare("UPDATE pd_cashouts SET status = ? WHERE id = ?")->execute([$action, $cid]);
    }
    
    $back = isset($_GET['return_user']) ? "admin.php?view_user=" . (int)$_GET['return_user'] . "&msg=Cashout+Updated" : "admin.php?msg=Cashout+Updated";
    header("Location: $back");
    exit;
}

// ✏️ ACTION: EDIT USER (BALANCE & USERNAME)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_user') {
    $edit_uid = (int)$_POST['user_id'];
    $new_balance = (float)$_POST['wallet_balance'];
    $new_username = trim($_POST['username']);

    $updateU = $pdo->prepare("UPDATE pd_users SET wallet_balance = ?, username = ? WHERE id = ?");
    $updateU->execute([$new_balance, $new_username, $edit_uid]);

    header("Location: admin.php?view_user=$edit_uid&msg=User+Updated+Successfully");
    exit;
}

// DATA FETCHING
$total_users = $pdo->query("SELECT COUNT(*) FROM pd_users")->fetchColumn();
$total_files = $pdo->query("SELECT COUNT(*) FROM pd_files")->fetchColumn();
$total_payouts = $pdo->query("SELECT SUM(amount) FROM pd_cashouts WHERE status = 'approved'")->fetchColumn() ?: 0.00;
$total_system_balance = $pdo->query("SELECT SUM(wallet_balance) FROM pd_users")->fetchColumn() ?: 0.00;

// ALL USERS WITH ASSET COUNT & TOTAL WITHDRAWN
$all_users = $pdo->query("
    SELECT u.*, 
           COUNT(DISTINCT f.id) AS total_assets,
           COALESCE(SUM(CASE WHEN c.status = 'approved' THEN c.amount ELSE 0 END), 0) AS total_withdrawn,
           COALESCE(SUM(CASE WHEN c.status = 'pending' THEN c.amount ELSE 0 END), 0) AS pending_cashout
    FROM pd_users u
    LEFT JOIN pd_files f ON f.user_id = u.id
    LEFT JOIN pd_cashouts c ON c.user_id = u.id
    GROUP BY u.id
    ORDER BY u.id DESC
")->fetchAll();

// CASHOUT REQUESTS
$pending_cashouts = $pdo->query("SELECT c.*, u.username, u.email FROM pd_cashouts c JOIN pd_users u ON c.user_id = u.id ORDER BY c.id DESC")->fetchAll();

// USER INSPECTOR DATA
$view_user = null;
$view_files = [];
$view_cashouts = [];

if (isset($_GET['view_user'])) {
    $view_uid = (int)$_GET['view_user'];
    $u_stmt = $pdo->prepare("SELECT * FROM pd_users WHERE id = ? LIMIT 1");
    $u_stmt->execute([$view_uid]);
    $view_user = $u_stmt->fetch();

    if ($view_user) {
        $f_stmt = $pdo->prepare("SELECT * FROM pd_files WHERE user_id = ? ORDER BY id DESC");
        $f_stmt->execute([$view_uid]);
        $view_files = $f_stmt->fetchAll();

        $c_stmt = $pdo->prepare("SELECT * FROM pd_cashouts WHERE user_id = ? ORDER BY id DESC");
        $c_stmt->execute([$view_uid]);
        $view_cashouts = $c_stmt->fetchAll();

        $paid_stmt = $pdo->prepare("SELECT SUM(amount) FROM pd_cashouts WHERE user_id = ? AND status = 'approved'");
        $paid_stmt->execute([$view_uid]);
        $view_user['total_paid'] = $paid_stmt->fetchColumn() ?: 0.00;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>PinoDrop - Master Admin Panel</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>* { font-family: 'Plus Jakarta Sans', sans-serif; -webkit-tap-highlight-color: transparent; }</style>
</head>
<body class="bg-[#050608] text-gray-200 min-h-screen p-3 sm:p-8">

    <div class="max-w-6xl mx-auto space-y-6 sm:space-y-8">
        <!-- TOP NAV -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-[#0a0c10] border border-red-500/30 p-5 sm:p-6 rounded-3xl shadow-[0_0_30px_rgba(239,68,68,0.15)]">
            <div>
                <span class="text-[9px] font-black text-red-400 uppercase tracking-widest bg-red-500/10 border border-red-500/20 px-2.5 py-1 rounded-full">Super Admin Console</span>
                <h1 class="text-xl sm:text-2xl font-black text-white mt-2 flex items-center gap-2">
                    <i class="fa-solid fa-crown text-amber-400"></i> PinoDrop Master Control
                </h1>
                <p class="text-[11px] text-gray-500">Logged as <?= esc($admin['username']) ?> (<?= esc($admin['email']) ?>)</p>
            </div>
            <div class="flex items-center gap-2 sm:gap-3 w-full sm:w-auto">
                <a href="index.php" class="flex-1 sm:flex-none text-center text-xs bg-white/10 hover:bg-white/20 text-white font-bold px-4 py-2.5 rounded-xl transition-all">Store View</a>
                <a href="dashboard.php" class="flex-1 sm:flex-none text-center text-xs bg-[#00e5ff] text-black font-bold px-4 py-2.5 rounded-xl transition-all shadow-[0_0_15px_rgba(0,229,255,0.3)]">Creator Hub</a>
            </div>
        </div>

        <?php if ($msg): ?>
            <div class="p-3 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold rounded-xl text-center">
                ✅ <?= esc($msg) ?>
            </div>
        <?php endif; ?>

        <!-- STATS CARDS -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
            <div class="bg-[#0e1118] border border-white/5 p-4 rounded-2xl">
                <span class="text-[9px] text-gray-500 uppercase font-bold tracking-wider">Total Creators</span>
                <div class="text-lg sm:text-2xl font-black text-white mt-1"><?= number_format($total_users) ?></div>
            </div>
            <div class="bg-[#0e1118] border border-white/5 p-4 rounded-2xl">
                <span class="text-[9px] text-gray-500 uppercase font-bold tracking-wider">Uploaded Assets</span>
                <div class="text-lg sm:text-2xl font-black text-[#00e5ff] mt-1"><?= number_format($total_files) ?></div>
            </div>
            <div class="bg-[#0e1118] border border-white/5 p-4 rounded-2xl">
                <span class="text-[9px] text-gray-500 uppercase font-bold tracking-wider">Total Paid Out</span>
                <div class="text-lg sm:text-2xl font-black text-[#2ecc71] mt-1">₱<?= number_format($total_payouts, 2) ?></div>
            </div>
            <div class="bg-[#0e1118] border border-white/5 p-4 rounded-2xl">
                <span class="text-[9px] text-gray-500 uppercase font-bold tracking-wider">Unpaid Balances</span>
                <div class="text-lg sm:text-2xl font-black text-amber-400 mt-1">₱<?= number_format($total_system_balance, 2) ?></div>
            </div>
        </div>

        <!-- 🔍 USER INSPECTOR MODAL / CARD -->
        <?php if ($view_user): ?>
        <div class="bg-[#0e1118] border-2 border-[#00e5ff]/40 rounded-3xl p-5 sm:p-6 shadow-[0_0_50px_rgba(0,229,255,0.15)] relative">
            <div class="flex justify-between items-start border-b border-white/10 pb-4 mb-5">
                <div class="flex items-center gap-3">
                    <img src="<?= esc($view_user['avatar'] ?? 'https://ui-avatars.com/api/?name=User') ?>" class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl border border-white/10">
                    <div>
                        <span class="text-[9px] bg-[#00e5ff]/10 text-[#00e5ff] px-2 py-0.5 rounded font-bold uppercase">User #<?= $view_user['id'] ?></span>
                        <h2 class="text-lg sm:text-xl font-black text-white mt-0.5"><?= esc($view_user['username']) ?></h2>
                        <p class="text-[11px] text-gray-400 truncate max-w-[180px] sm:max-w-none"><?= esc($view_user['email']) ?></p>
                    </div>
                </div>
                <a href="admin.php" class="text-xs bg-white/10 hover:bg-white/20 text-white font-bold px-3 py-1.5 rounded-xl">✕ Close</a>
            </div>

            <!-- EDIT FORM -->
            <form method="POST" class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-[#050608] border border-white/10 p-4 rounded-2xl mb-5">
                <input type="hidden" name="action" value="edit_user">
                <input type="hidden" name="user_id" value="<?= $view_user['id'] ?>">

                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Username</label>
                    <input type="text" name="username" value="<?= esc($view_user['username']) ?>" required class="w-full bg-[#111318] border border-white/10 rounded-xl px-3 py-2 text-xs text-white outline-none focus:border-[#00e5ff]">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Wallet Balance (₱)</label>
                    <input type="number" step="0.01" name="wallet_balance" value="<?= $view_user['wallet_balance'] ?>" required class="w-full bg-[#111318] border border-white/10 rounded-xl px-3 py-2 text-xs text-emerald-400 font-bold font-mono outline-none focus:border-[#00e5ff]">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-[#00e5ff] hover:opacity-90 text-black font-black py-2.5 rounded-xl text-xs uppercase tracking-wider">
                        💾 Save Changes
                    </button>
                </div>
            </form>

            <!-- SUMMARY STATS -->
            <div class="grid grid-cols-3 gap-2 sm:gap-3 mb-5 text-center text-xs">
                <div class="bg-white/5 p-2.5 rounded-xl">
                    <span class="text-gray-400 text-[9px] uppercase">Assets</span>
                    <div class="font-bold text-white text-sm sm:text-base mt-0.5"><?= count($view_files) ?></div>
                </div>
                <div class="bg-white/5 p-2.5 rounded-xl">
                    <span class="text-gray-400 text-[9px] uppercase">Total Cashed Out</span>
                    <div class="font-bold text-emerald-400 text-sm sm:text-base mt-0.5">₱<?= number_format($view_user['total_paid'], 2) ?></div>
                </div>
                <div class="bg-white/5 p-2.5 rounded-xl">
                    <span class="text-gray-400 text-[9px] uppercase">Current Balance</span>
                    <div class="font-bold text-amber-400 text-sm sm:text-base mt-0.5">₱<?= number_format($view_user['wallet_balance'], 2) ?></div>
                </div>
            </div>

            <!-- TABS -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- User Files -->
                <div class="bg-[#0a0c10] border border-white/5 rounded-2xl p-4">
                    <h3 class="text-xs font-bold text-white uppercase mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-folder text-[#00e5ff]"></i> Uploaded Assets (<?= count($view_files) ?>)
                    </h3>
                    <div class="overflow-y-auto max-h-52 space-y-2">
                        <?php if (count($view_files) > 0): foreach($view_files as $vf): ?>
                        <div class="flex justify-between items-center bg-white/[0.02] p-2 rounded-xl text-xs">
                            <div class="truncate max-w-[200px]">
                                <div class="font-bold text-white truncate"><?= esc($vf['title']) ?></div>
                                <div class="text-[9px] text-gray-500 font-mono"><?= number_format($vf['total_downloads']) ?> dl • <?= esc($vf['file_size']) ?></div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="item.php?id=<?= $vf['id'] ?>" target="_blank" class="text-gray-400 hover:text-white"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                                <a href="admin.php?del_file=<?= $vf['id'] ?>&return_user=<?= $view_user['id'] ?>" onclick="return confirm('Burahin?')" class="text-red-400 hover:text-red-300 font-bold text-[10px]">Delete</a>
                            </div>
                        </div>
                        <?php endforeach; else: ?>
                            <p class="text-xs text-gray-600">Walang in-upload na files.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- User Cashouts -->
                <div class="bg-[#0a0c10] border border-white/5 rounded-2xl p-4">
                    <h3 class="text-xs font-bold text-white uppercase mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-money-bill text-[#2ecc71]"></i> Cashout History
                    </h3>
                    <div class="overflow-y-auto max-h-52 space-y-2">
                        <?php if (count($view_cashouts) > 0): foreach($view_cashouts as $vc): ?>
                        <div class="flex justify-between items-center bg-white/[0.02] p-2.5 rounded-xl text-xs">
                            <div>
                                <div class="font-bold text-emerald-400 font-mono">₱<?= number_format($vc['amount'], 2) ?></div>
                                <div class="text-[10px] text-amber-400 font-mono font-bold"><?= esc($vc['method']) ?></div>
                            </div>
                            <div class="text-right">
                                <span class="text-[9px] font-bold uppercase px-2 py-0.5 rounded <?= $vc['status'] === 'approved' ? 'bg-green-500/20 text-green-400' : ($vc['status'] === 'rejected' ? 'bg-red-500/20 text-red-400' : 'bg-yellow-500/20 text-yellow-400') ?>">
                                    <?= $vc['status'] ?>
                                </span>
                                <?php if ($vc['status'] === 'pending'): ?>
                                    <div class="mt-1 space-x-1">
                                        <a href="admin.php?cashout_action=approve&cid=<?= $vc['id'] ?>&return_user=<?= $view_user['id'] ?>" class="text-[9px] bg-green-500 text-black px-2 py-0.5 rounded font-bold">PAID</a>
                                        <a href="admin.php?cashout_action=reject&cid=<?= $vc['id'] ?>&return_user=<?= $view_user['id'] ?>" class="text-[9px] bg-red-500 text-white px-2 py-0.5 rounded font-bold">REJECT</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; else: ?>
                            <p class="text-xs text-gray-600">Walang cashout history.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- 👥 ALL CREATORS & USERS SECTION -->
        <div class="bg-[#0a0c10] border border-white/10 rounded-3xl p-4 sm:p-6 shadow-xl">
            <h2 class="text-base sm:text-lg font-bold text-white mb-4 flex items-center gap-2">
                <i class="fa-solid fa-users text-[#00e5ff]"></i> All Creators &amp; Users
            </h2>

            <!-- 📱 1. MOBILE VIEW (Cards layout para sa cellphone - HINDI NA MAIIPIT!) -->
            <div class="grid grid-cols-1 gap-3 md:hidden">
                <?php foreach($all_users as $u): ?>
                <div class="bg-white/[0.02] border border-white/5 p-4 rounded-2xl space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <img src="<?= esc($u['avatar'] ?? 'https://ui-avatars.com/api/?name=User') ?>" class="w-8 h-8 rounded-full">
                            <div>
                                <div class="font-bold text-white text-xs"><?= esc($u['username']) ?></div>
                                <div class="text-[10px] text-gray-400 font-mono"><?= esc($u['email']) ?></div>
                            </div>
                        </div>
                        <span class="text-[9px] font-mono text-gray-500 bg-white/5 px-2 py-0.5 rounded">#<?= $u['id'] ?></span>
                    </div>

                    <!-- Mini Stats Grid -->
                    <div class="grid grid-cols-3 gap-2 bg-[#050608] p-2.5 rounded-xl text-center">
                        <div>
                            <span class="text-[9px] text-gray-500 block uppercase">Assets</span>
                            <b class="text-[#00e5ff] text-xs font-mono"><?= number_format($u['total_assets']) ?></b>
                        </div>
                        <div>
                            <span class="text-[9px] text-gray-500 block uppercase">Balance</span>
                            <b class="text-emerald-400 text-xs font-mono">₱<?= number_format($u['wallet_balance'], 2) ?></b>
                        </div>
                        <div>
                            <span class="text-[9px] text-gray-500 block uppercase">Withdrawn</span>
                            <b class="text-gray-400 text-xs font-mono">₱<?= number_format($u['total_withdrawn'], 2) ?></b>
                        </div>
                    </div>

                    <a href="admin.php?view_user=<?= $u['id'] ?>" class="block text-center w-full bg-[#00e5ff]/10 hover:bg-[#00e5ff] text-[#00e5ff] hover:text-black border border-[#00e5ff]/30 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                        🔍 Inspect / Edit User
                    </a>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- 💻 2. DESKTOP VIEW (Table layout para sa malapad na screen) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-white/5 text-gray-400 uppercase font-bold text-[9px] tracking-wider">
                        <tr>
                            <th class="p-3">User</th>
                            <th class="p-3">Email</th>
                            <th class="p-3 text-center">Assets</th>
                            <th class="p-3 text-right">Balance</th>
                            <th class="p-3 text-right">Total Withdrawn</th>
                            <th class="p-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php foreach($all_users as $u): ?>
                        <tr class="hover:bg-white/[0.03] transition-colors <?= isset($_GET['view_user']) && $_GET['view_user'] == $u['id'] ? 'bg-[#00e5ff]/5' : '' ?>">
                            <td class="p-3 font-bold text-white flex items-center gap-2">
                                <img src="<?= esc($u['avatar'] ?? 'https://ui-avatars.com/api/?name=User') ?>" class="w-7 h-7 rounded-full">
                                <div>
                                    <?= esc($u['username']) ?>
                                    <div class="text-[9px] text-gray-500 font-mono">ID: #<?= $u['id'] ?></div>
                                </div>
                            </td>
                            <td class="p-3 text-gray-300 font-mono"><?= esc($u['email']) ?></td>
                            <td class="p-3 text-center font-bold text-[#00e5ff]"><?= number_format($u['total_assets']) ?></td>
                            <td class="p-3 text-right font-mono font-bold text-emerald-400">₱<?= number_format($u['wallet_balance'], 2) ?></td>
                            <td class="p-3 text-right font-mono text-gray-400">₱<?= number_format($u['total_withdrawn'], 2) ?></td>
                            <td class="p-3 text-center">
                                <a href="admin.php?view_user=<?= $u['id'] ?>" class="bg-[#00e5ff]/10 hover:bg-[#00e5ff] text-[#00e5ff] hover:text-black border border-[#00e5ff]/30 px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all whitespace-nowrap">
                                    🔍 Inspect / Edit
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 💰 ALL CASHOUT TRANSACTIONS -->
        <div class="bg-[#0a0c10] border border-white/10 rounded-3xl p-4 sm:p-6 shadow-xl">
            <h2 class="text-base sm:text-lg font-bold text-white mb-4 flex items-center gap-2">
                <i class="fa-solid fa-money-bill-wave text-[#2ecc71]"></i> All Payout Transactions
            </h2>

            <!-- 📱 Mobile View para sa Cashout -->
            <div class="grid grid-cols-1 gap-3 md:hidden">
                <?php if (count($pending_cashouts) > 0): foreach($pending_cashouts as $c): ?>
                <div class="bg-white/[0.02] border border-white/5 p-4 rounded-2xl space-y-2.5">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="font-bold text-white text-xs"><?= esc($c['username']) ?></div>
                            <div class="text-[10px] text-gray-400 font-mono"><?= esc($c['email']) ?></div>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase <?= $c['status'] === 'approved' ? 'bg-green-500/20 text-green-400' : ($c['status'] === 'rejected' ? 'bg-red-500/20 text-red-400' : 'bg-yellow-500/20 text-yellow-400 animate-pulse') ?>">
                            <?= $c['status'] ?>
                        </span>
                    </div>

                    <div class="bg-[#050608] p-2.5 rounded-xl flex justify-between items-center">
                        <span class="text-xs font-mono font-bold text-[#2ecc71]">₱<?= number_format($c['amount'], 2) ?></span>
                        <span class="text-[10px] text-amber-400 font-mono font-bold"><?= esc($c['method']) ?></span>
                    </div>

                    <?php if ($c['status'] === 'pending'): ?>
                    <div class="flex gap-2 pt-1">
                        <a href="admin.php?cashout_action=approve&cid=<?= $c['id'] ?>" onclick="return confirm('Nabayaran na sa GCash/PayPal?')" class="flex-1 text-center bg-[#2ecc71] text-black font-black py-2 rounded-xl text-xs">MARK PAID</a>
                        <a href="admin.php?cashout_action=reject&cid=<?= $c['id'] ?>" onclick="return confirm('I-reject at i-refund?')" class="flex-1 text-center bg-red-600 text-white font-black py-2 rounded-xl text-xs">REJECT</a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; else: ?>
                <p class="text-xs text-gray-600 text-center py-4">Walang mga cashout transaction.</p>
                <?php endif; ?>
            </div>

            <!-- 💻 Desktop Table View para sa Cashout -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-white/5 text-gray-400 uppercase font-bold text-[9px] tracking-wider">
                        <tr>
                            <th class="p-3">Creator</th>
                            <th class="p-3">Amount</th>
                            <th class="p-3">Payment Info</th>
                            <th class="p-3">Status</th>
                            <th class="p-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php if (count($pending_cashouts) > 0): foreach($pending_cashouts as $c): ?>
                        <tr class="hover:bg-white/[0.02]">
                            <td class="p-3 font-bold text-white">
                                <?= esc($c['username']) ?>
                                <div class="text-[9px] text-gray-500 font-mono"><?= esc($c['email']) ?></div>
                            </td>
                            <td class="p-3 font-mono font-bold text-[#2ecc71] text-sm">₱<?= number_format($c['amount'], 2) ?></td>
                            <td class="p-3 text-gray-300 font-mono">
                                <span class="bg-white/5 px-2 py-1 rounded text-amber-400 font-mono font-bold"><?= esc($c['method']) ?></span>
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase <?= $c['status'] === 'approved' ? 'bg-green-500/20 text-green-400' : ($c['status'] === 'rejected' ? 'bg-red-500/20 text-red-400' : 'bg-yellow-500/20 text-yellow-400 animate-pulse') ?>">
                                    <?= $c['status'] ?>
                                </span>
                            </td>
                            <td class="p-3 text-right space-x-2">
                                <?php if ($c['status'] === 'pending'): ?>
                                    <a href="admin.php?cashout_action=approve&cid=<?= $c['id'] ?>" onclick="return confirm('Nabayaran na sa GCash/PayPal?')" class="bg-[#2ecc71] hover:bg-green-600 text-black font-black px-3 py-1 rounded-xl text-[10px]">MARK PAID</a>
                                    <a href="admin.php?cashout_action=reject&cid=<?= $c['id'] ?>" onclick="return confirm('I-reject at i-refund?')" class="bg-red-600 hover:bg-red-700 text-white font-black px-3 py-1 rounded-xl text-[10px]">REJECT</a>
                                <?php else: ?>
                                    <span class="text-gray-600 text-[10px]">Done</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="5" class="p-4 text-center text-gray-600">Walang mga cashout transaction.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
