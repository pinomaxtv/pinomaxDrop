<?php
require 'config.php';

// 🛡️ AUTH CHECK: Dapat naka-login bago makapasok sa dashboard
if (!isset($_SESSION['pd_user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['pd_user_id']; 

$stmt = $pdo->prepare("SELECT * FROM pd_users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: logout.php");
    exit;
}

$is_admin = ($user['id'] == 1 || in_array(strtolower($user['email']), ['admin@pinomax.tv', 'roderickalmaras05@gmail.com']));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'upload') {
        $title = trim($_POST['title']);
        $desc = trim($_POST['description'] ?? '');
        $cat = $_POST['category'];
        $size = trim($_POST['file_size']);
        $url = trim($_POST['download_url']);
        $img_url = trim($_POST['image_url'] ?? ''); 
        
        $insert = $pdo->prepare("INSERT INTO pd_files (user_id, title, description, category, file_size, download_url, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insert->execute([$user_id, $title, $desc, $cat, $size, $url, $img_url]);
        $message = "Asset published successfully! Ready na i-share ang link!";
    } elseif ($_POST['action'] === 'cashout') {
        $amount = (float)$_POST['amount'];
        $method = $_POST['method'];
        $account_details = trim($_POST['account_details'] ?? '');

        if (empty($account_details)) {
            $error = "Pakilagay ang iyong GCash Number o PayPal Email Address!";
        } elseif ($amount >= 100 && $user['wallet_balance'] >= $amount) {
            $pdo->beginTransaction();
            $pdo->prepare("UPDATE pd_users SET wallet_balance = wallet_balance - ? WHERE id = ?")->execute([$amount, $user_id]);
            $pdo->prepare("INSERT INTO pd_cashouts (user_id, amount, method, status) VALUES (?, ?, ?, 'pending')")->execute([$user_id, $amount, "$method: $account_details"]);
            $pdo->commit();
            $message = "Cashout requested successfully! I-veverify ito ng admin bago ipadala.";
            
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
        } else {
            $error = "Insufficient balance or minimum ₱100 not met.";
        }
    }
}

$filesStmt = $pdo->prepare("SELECT * FROM pd_files WHERE user_id = ? ORDER BY created_at DESC");
$filesStmt->execute([$user_id]);
$myFiles = $filesStmt->fetchAll();
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>Creator Dashboard - PinoDrop</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
      * { font-family: 'Plus Jakarta Sans', sans-serif; -webkit-tap-highlight-color: transparent; }
      body { margin: 0; min-height: 100vh; background: #050608; }
    </style>
  </head>
  <body class="text-[#e0e0e0] flex flex-col md:flex-row pb-20 md:pb-0 overflow-x-hidden">
    
    <!-- DESKTOP SIDEBAR -->
    <aside class="hidden md:flex w-64 bg-[#0a0c10] border-r border-[#00e5ff33] flex-col p-6 shadow-xl z-20 shrink-0 min-h-screen">
      <div class="mb-10 flex items-center gap-3">
        <div class="w-10 h-10 bg-[#00e5ff] rounded-lg flex items-center justify-center shadow-[0_0_15px_rgba(0,229,255,0.4)]">
          <i class="fa-solid fa-cloud-arrow-down text-black text-lg"></i>
        </div>
        <span class="text-xl font-bold tracking-tighter text-white uppercase">Pino<span class="text-[#00e5ff]">Drop</span></span>
      </div>
      <nav class="flex-1 space-y-2">
        <div class="text-[10px] uppercase tracking-widest text-[#00e5ff99] mb-4 font-semibold">Main Hub</div>
        <a href="index.php" class="flex items-center gap-3 px-4 py-3 text-gray-500 hover:text-white transition-colors">
          <span class="text-sm font-medium">Asset Market</span>
        </a>
        <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 bg-[#00e5ff11] border-l-2 border-[#00e5ff] text-white rounded-r-md">
          <span class="text-sm font-medium">Creator Dashboard</span>
        </a>
        <?php if ($is_admin): ?>
        <a href="admin.php" class="flex items-center gap-3 px-4 py-3 bg-red-500/10 border-l-2 border-red-500 text-red-400 rounded-r-md mt-6">
          <span class="text-sm font-black uppercase tracking-wider">👑 Admin Panel</span>
        </a>
        <?php endif; ?>
      </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 flex flex-col min-h-screen">
      <!-- Header -->
      <header class="h-16 md:h-20 border-b border-white/5 px-4 md:px-8 flex items-center justify-between backdrop-blur-md sticky top-0 z-30 bg-[#050608]/90">
        <div class="flex items-center gap-3 md:hidden">
            <div class="w-8 h-8 bg-[#00e5ff] rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-cloud-arrow-down text-black text-sm"></i>
            </div>
            <span class="text-lg font-black tracking-tighter text-white">PINO<span class="text-[#00e5ff]">DROP</span></span>
        </div>

        <div class="hidden sm:block">
            <span class="text-xs text-gray-500">Creator Account:</span>
            <span class="text-xs font-bold text-white ml-1"><?= esc($user['username']) ?></span>
        </div>

        <div class="flex items-center gap-4">
          <div class="flex items-center gap-2 bg-[#111318] border border-white/10 py-1.5 px-3 rounded-full">
            <img src="<?= esc($user['avatar'] ?? 'https://ui-avatars.com/api/?name=User') ?>" class="w-6 h-6 rounded-full object-cover">
            <span class="text-xs font-bold text-white"><?= esc($user['username']) ?></span>
            <a href="logout.php" title="Logout" class="text-red-400 hover:text-red-300 text-xs ml-2"><i class="fa-solid fa-power-off"></i></a>
          </div>
        </div>
      </header>

      <section class="p-4 sm:p-8 flex-1">
        <!-- 🌟 TITLE AT HOW TO USE BUTTON SA TAAS -->
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-xl sm:text-2xl font-black text-white flex items-center gap-3">
            <span class="w-1.5 h-6 bg-[#00e5ff] rounded-full"></span>
            Creator Hub
          </h2>

          <!-- 📘 HOW TO USE & GUIDELINES BUTTON (NASA TAAS NA!) -->
          <button onclick="openGuideModal()" class="inline-flex items-center gap-2 bg-[#00e5ff]/10 hover:bg-[#00e5ff] text-[#00e5ff] hover:text-black border border-[#00e5ff]/30 px-3.5 py-2 rounded-xl text-xs font-bold transition-all active:scale-95 shadow-[0_0_15px_rgba(0,229,255,0.15)]">
            <i class="fa-solid fa-circle-question"></i> How to Earn & Rules
          </button>
        </div>
        
        <?php if(isset($message)): ?><div class="bg-[#2ecc71]/10 border border-[#2ecc71]/30 text-[#2ecc71] p-3.5 rounded-xl mb-6 text-xs font-bold flex items-center gap-2"><i class="fa-solid fa-check"></i> <?= $message ?></div><?php endif; ?>
        <?php if(isset($error)): ?><div class="bg-red-500/10 border border-red-500/30 text-red-500 p-3.5 rounded-xl mb-6 text-xs font-bold flex items-center gap-2"><i class="fa-solid fa-triangle-exclamation"></i> <?= $error ?></div><?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- CASHOUT WALLET BOX -->
          <div class="space-y-6">
            <div class="bg-[#0e1118] border border-white/5 rounded-3xl p-6 shadow-xl relative overflow-hidden">
              <div class="absolute -right-6 -top-6 w-24 h-24 bg-[#2ecc71]/10 rounded-full blur-xl pointer-events-none"></div>

              <h2 class="text-[10px] uppercase tracking-widest text-gray-500 font-bold mb-2">Available Wallet Balance</h2>
              <div class="text-3xl sm:text-4xl font-mono text-[#2ecc71] font-black mb-1">₱<?= number_format($user['wallet_balance'] ?? 0, 2) ?></div>
              <p class="text-gray-500 text-[10px] uppercase tracking-wider font-bold mb-6">Min. Payout ₱100.00</p>
              
              <hr class="border-white/5 mb-6" />
              
              <h3 class="text-xs uppercase tracking-widest text-gray-300 font-bold mb-4 flex items-center gap-2">
                <i class="fa-solid fa-wallet text-[#00e5ff]"></i> Request Payout
              </h3>
              <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="cashout">
                <div>
                  <label class="block text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Amount (PHP)</label>
                  <input type="number" name="amount" min="100" max="<?= $user['wallet_balance'] ?>" step="0.01" required placeholder="₱100.00" class="w-full bg-[#161a23] border border-white/10 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-[#00e5ff]" />
                </div>
                <div>
                  <label class="block text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Payment Method</label>
                  <select name="method" required class="w-full bg-[#161a23] border border-white/10 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-[#00e5ff]">
                    <option value="GCash">GCash</option>
                    <option value="PayPal">PayPal</option>
                  </select>
                </div>
                <div>
                  <label class="block text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Account Number / Email</label>
                  <input type="text" name="account_details" required placeholder="09xxxxxxxxx or paypal@email.com" class="w-full bg-[#161a23] border border-white/10 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-[#00e5ff]" />
                </div>
                <button type="submit" class="w-full bg-gradient-to-r from-[#0072ff] to-[#00c6ff] text-white font-black py-3 rounded-xl text-xs uppercase tracking-wider shadow-lg transition-transform active:scale-95 mt-2">
                  Withdraw Funds
                </button>
              </form>
            </div>
          </div>

          <!-- UPLOAD FORM & ASSETS -->
          <div class="lg:col-span-2 space-y-6">
            <div class="bg-[#0e1118] border border-white/5 rounded-3xl p-6 shadow-xl">
              <h2 class="text-xs uppercase tracking-widest text-gray-400 font-bold flex items-center gap-2 mb-6">
                <i class="fa-solid fa-cloud-arrow-up text-[#2ecc71]"></i> Upload New Asset
              </h2>
              <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="hidden" name="action" value="upload">
                <div class="space-y-1">
                  <label class="block text-[10px] uppercase tracking-wider text-gray-400 font-bold">Asset Title</label>
                  <input type="text" name="title" placeholder="e.g. GTA V Mobile Mod APK" required class="w-full bg-[#161a23] border border-white/10 rounded-xl px-4 py-2.5 text-xs text-white focus:border-[#00e5ff] outline-none" />
                </div>
                <div class="space-y-1">
                  <label class="block text-[10px] uppercase tracking-wider text-gray-400 font-bold">Category</label>
                  <select name="category" required class="w-full bg-[#161a23] border border-white/10 rounded-xl px-4 py-2.5 text-xs text-white focus:border-[#00e5ff] outline-none">
                    <option>Android APKs</option>
                    <option>Reviewers</option>
                    <option>Tools & Software</option>
                    <option>Configs</option>
                  </select>
                </div>
                <div class="space-y-1">
                  <label class="block text-[10px] uppercase tracking-wider text-gray-400 font-bold">File Size</label>
                  <input type="text" name="file_size" placeholder="e.g. 45 MB" required class="w-full bg-[#161a23] border border-white/10 rounded-xl px-4 py-2.5 text-xs text-white focus:border-[#00e5ff] outline-none" />
                </div>
                
                <div class="space-y-1">
                  <label class="block text-[10px] uppercase tracking-wider text-gray-400 font-bold">Preview Image URL (Optional)</label>
                  <input type="url" name="image_url" placeholder="https://i.imgur.com/... o Postimages link" class="w-full bg-[#161a23] border border-white/10 rounded-xl px-4 py-2.5 text-xs text-white focus:border-[#00e5ff] outline-none" />
                </div>
                
                <div class="space-y-1">
                  <label class="block text-[10px] uppercase tracking-wider text-gray-400 font-bold">Download URL</label>
                  <input type="url" name="download_url" placeholder="Google Drive, Mediafire, or Mega URL" required class="w-full bg-[#161a23] border border-white/10 rounded-xl px-4 py-2.5 text-xs text-white focus:border-[#00e5ff] outline-none" />
                </div>
                <div class="space-y-1 md:col-span-2">
                  <label class="block text-[10px] uppercase tracking-wider text-gray-400 font-bold">Description (Optional)</label>
                  <textarea name="description" rows="2" placeholder="Tell downloaders what this file is about..." class="w-full bg-[#161a23] border border-white/10 rounded-xl px-4 py-2 text-xs text-white focus:border-[#00e5ff] outline-none resize-none"></textarea>
                </div>
                <div class="md:col-span-2 mt-2">
                  <button type="submit" class="w-full bg-gradient-to-r from-[#2ecc71] to-[#00e5ff] text-black font-black py-3.5 rounded-xl shadow-lg hover:opacity-95 text-xs uppercase tracking-wider transition-transform active:scale-98">
                    Publish Asset &amp; Generate Link 🚀
                  </button>
                </div>
              </form>
            </div>

            <!-- MY UPLOADS TABLE WITH SHAREABLE LINKS -->
            <div class="bg-[#0e1118] border border-white/5 rounded-3xl p-6 shadow-xl">
              <h2 class="text-xs uppercase tracking-widest text-gray-400 font-bold flex items-center gap-2 mb-4">
                <i class="fa-solid fa-list-check text-[#00e5ff]"></i> My Uploaded Assets
              </h2>
              <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                  <thead class="bg-white/5 text-gray-400 uppercase font-bold text-[9px] tracking-wider">
                    <tr>
                      <th class="p-3">Title</th>
                      <th class="p-3">Category</th>
                      <th class="p-3 text-right">Downloads</th>
                      <th class="p-3 text-right">Earnings</th>
                      <th class="p-3 text-right">Share Link</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-white/5">
                    <?php if (count($myFiles) > 0): foreach($myFiles as $f): 
                      $shareUrl = "https://pinodrop.pages.dev/item.php?id=" . $f['id'];
                    ?>
                    <tr class="hover:bg-white/[0.02]">
                      <td class="p-3 font-bold text-white"><?= esc($f['title']) ?></td>
                      <td class="p-3"><span class="bg-white/5 text-gray-300 px-2 py-0.5 rounded text-[10px]"><?= esc($f['category']) ?></span></td>
                      <td class="p-3 text-right font-mono text-[#00e5ff] font-bold"><?= number_format($f['total_downloads']) ?></td>
                      <td class="p-3 text-right font-mono text-[#2ecc71] font-bold">₱<?= number_format($f['total_downloads'] * 0.15, 2) ?></td>
                      <td class="p-3 text-right">
                        <button onclick="navigator.clipboard.writeText('<?= $shareUrl ?>'); alert('Link copied to clipboard! Share it now to earn!')" class="bg-[#00e5ff]/10 hover:bg-[#00e5ff] text-[#00e5ff] hover:text-black font-bold px-2.5 py-1 rounded-lg text-[10px] transition-all">
                          <i class="fa-solid fa-link"></i> Copy
                        </button>
                      </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="5" class="p-4 text-center text-gray-500 text-xs">Wala ka pang na-upload na file. Subukan mong mag-upload sa itaas!</td></tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>

    <!-- 📱 MOBILE BOTTOM NAVBAR -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 h-16 bg-[#0a0c10]/95 backdrop-blur-lg border-t border-white/10 flex items-center justify-around z-40 px-4">
        <a href="index.php" class="flex flex-col items-center gap-1 text-gray-400 hover:text-white">
            <i class="fa-solid fa-store text-base"></i>
            <span class="text-[9px] font-bold">Store</span>
        </a>
        <a href="dashboard.php" class="flex flex-col items-center gap-1 text-[#00e5ff]">
            <i class="fa-solid fa-chart-pie text-base"></i>
            <span class="text-[9px] font-bold">Creator</span>
        </a>
        <?php if ($is_admin): ?>
        <a href="admin.php" class="flex flex-col items-center gap-1 text-red-500">
            <i class="fa-solid fa-crown text-base"></i>
            <span class="text-[9px] font-bold">Admin</span>
        </a>
        <?php endif; ?>
    </div>
    
    <!-- 📘 HOW TO EARN & TERMS MODAL -->
    <div id="guideModal" class="fixed inset-0 bg-[#050608]/90 z-[9999] hidden items-center justify-center p-4 backdrop-blur-md">
        <div class="bg-[#0e1118] border border-[#00e5ff]/30 rounded-3xl p-5 sm:p-7 max-w-md w-full shadow-[0_0_50px_rgba(0,229,255,0.15)] relative max-h-[90vh] flex flex-col">
            
            <!-- Header -->
            <div class="flex items-center justify-between pb-3 border-b border-white/10 mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-[#00e5ff]/10 flex items-center justify-center text-[#00e5ff]">
                        <i class="fa-solid fa-book-open text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-white uppercase tracking-wider">Creator Earning Guide</h3>
                        <p class="text-[10px] text-gray-500">Mga Patakaran at Gabay sa Kitaan</p>
                    </div>
                </div>
                <button onclick="closeGuideModal()" class="text-gray-400 hover:text-white p-1 text-base">✕</button>
            </div>

            <!-- Scrollable Content -->
            <div class="overflow-y-auto space-y-3.5 text-xs text-gray-300 pr-1 text-left leading-relaxed">
                
                <!-- Rule 1: ₱0.15 per download -->
                <div class="bg-white/5 border border-white/5 rounded-2xl p-3.5">
                    <div class="flex items-center gap-2 text-[#00e5ff] font-bold text-xs mb-1">
                        <i class="fa-solid fa-coins"></i>
                        <h4>₱0.15 Bawat Valid Download</h4>
                    </div>
                    <p class="text-[11px] text-gray-400">
                        Kikita ka ng <b>₱0.15</b> sa bawat taong matagumpay na tatapos ng 30 seconds security timer sa in-upload mong asset bago makuha ang direct link.
                    </p>
                </div>

                <!-- Rule 2: 10-Minute Cooldown & Self-Download -->
                <div class="bg-white/5 border border-white/5 rounded-2xl p-3.5">
                    <div class="flex items-center gap-2 text-amber-400 font-bold text-xs mb-1">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <h4>10-Minute Cooldown sa Parehong File</h4>
                    </div>
                    <p class="text-[11px] text-gray-400">
                        Kung ikaw o iisang tao ang magda-download ng parehong file, <b>isang beses lang papasok ang kita</b>. Kailangang maghintay ng <b>10 minutes</b> bago pumasok ulit ang ₱0.15 para maiwasan ang spam.
                    </p>
                </div>

                <!-- Rule 3: Uninterrupted Downloads -->
                <div class="bg-white/5 border border-white/5 rounded-2xl p-3.5">
                    <div class="flex items-center gap-2 text-emerald-400 font-bold text-xs mb-1">
                        <i class="fa-solid fa-cloud-arrow-down"></i>
                        <h4>Tuloy-tuloy ang Download</h4>
                    </div>
                    <p class="text-[11px] text-gray-400">
                        Kahit naka-cooldown ang earnings, <b>tuloy-tuloy pa rin at hindi mahaharang</b> ang download ng file. Libreng ma-a-access ng bisita ang file kahit kailan!
                    </p>
                </div>

                <!-- Rule 4: Payout Rules -->
                <div class="bg-white/5 border border-white/5 rounded-2xl p-3.5">
                    <div class="flex items-center gap-2 text-purple-400 font-bold text-xs mb-1">
                        <i class="fa-solid fa-wallet"></i>
                        <h4>Minimum Cashout: ₱100.00</h4>
                    </div>
                    <p class="text-[11px] text-gray-400">
                        Kapag umabot na sa ₱100 ang iyong wallet balance, maaari mo na itong i-withdraw via <b>GCash</b> o <b>PayPal</b>. Pinoproseso ito ng admin sa loob ng 24 oras.
                    </p>
                </div>

                <!-- Rule 5: Anti-Cheat Policy -->
                <div class="bg-red-500/10 border border-red-500/20 rounded-2xl p-3.5">
                    <div class="flex items-center gap-2 text-red-400 font-bold text-xs mb-1">
                        <i class="fa-solid fa-shield-halved"></i>
                        <h4>Bawal ang Auto-Clicker & Bots</h4>
                    </div>
                    <p class="text-[11px] text-gray-400">
                        Mahigpit na ipinagbabawal ang paggamit ng bot scripts o auto-refreshers. Ang mahuhuling nandadaya ay automatic na <b>iba-ban ang account at mavo-void ang balance</b>.
                    </p>
                </div>
            </div>

            <!-- Footer Button -->
            <div class="pt-4 border-t border-white/10 mt-3">
                <button onclick="closeGuideModal()" class="w-full py-2.5 bg-gradient-to-r from-[#00e5ff] to-[#0072ff] hover:opacity-95 text-black font-black rounded-xl text-xs uppercase tracking-wider shadow-[0_0_20px_rgba(0,229,255,0.3)]">
                    Naiintindihan Ko 👍
                </button>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT LOGIC -->
    <script>
        function openGuideModal() {
            const modal = document.getElementById('guideModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeGuideModal() {
            const modal = document.getElementById('guideModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    </script>
  </body>
</html>