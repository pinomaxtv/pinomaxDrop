<?php
require 'config.php';
$user_id = $_SESSION['user_id'] ?? 1; 

$stmt = $pdo->prepare("SELECT * FROM pd_users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'upload') {
        $title = $_POST['title'];
        $desc = $_POST['description'];
        $cat = $_POST['category'];
        $size = $_POST['file_size'];
        $url = $_POST['download_url'];
        
        $insert = $pdo->prepare("INSERT INTO pd_files (user_id, title, description, category, file_size, download_url) VALUES (?, ?, ?, ?, ?, ?)");
        $insert->execute([$user_id, $title, $desc, $cat, $size, $url]);
        $message = "Asset published successfully!";
    } elseif ($_POST['action'] === 'cashout') {
        $amount = (float)$_POST['amount'];
        $method = $_POST['method'];
        if ($amount >= 100 && $user['wallet_balance'] >= $amount) {
            $pdo->beginTransaction();
            $pdo->prepare("UPDATE pd_users SET wallet_balance = wallet_balance - ? WHERE id = ?")->execute([$amount, $user_id]);
            $pdo->prepare("INSERT INTO pd_cashouts (user_id, amount, method) VALUES (?, ?, ?)")->execute([$user_id, $amount, $method]);
            $pdo->commit();
            $message = "Cashout requested successfully!";
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
<html style="height: 100%; margin: 0;">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Creator Dashboard - PinoDrop</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
      body { margin: 0; height: 100%; display: flex; flex-direction: column; overflow: hidden; }
    </style>
  </head>
  <body class="bg-[#050608] text-[#e0e0e0] font-sans flex overflow-hidden selection:bg-[#00e5ff] selection:text-black">
    
    <!-- Sidebar -->
    <aside class="w-64 bg-[#0a0c10] border-r border-[#00e5ff33] flex flex-col p-6 shadow-[4px_0_24px_rgba(0,229,255,0.05)] z-20 shrink-0">
      <div class="mb-10 flex items-center gap-3">
        <div class="w-10 h-10 bg-[#00e5ff] rounded-lg flex items-center justify-center shadow-[0_0_15px_rgba(0,229,255,0.4)]">
          <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path></svg>
        </div>
        <span class="text-xl font-bold tracking-tighter text-white uppercase">Pino<span class="text-[#00e5ff]">Drop</span></span>
      </div>
      <nav class="flex-1 space-y-2">
        <div class="text-[10px] uppercase tracking-widest text-[#00e5ff99] mb-4 font-semibold">Main Hub</div>
        <a href="index.php" class="flex items-center gap-3 px-4 py-3 text-gray-500 hover:text-white transition-colors">
          <span class="text-sm font-medium">Asset Market</span>
        </a>
        <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 bg-[#00e5ff11] border-l-2 border-[#00e5ff] text-white rounded-r-md transition-colors">
          <span class="text-sm font-medium">Creator Dashboard</span>
        </a>
      </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col relative overflow-hidden">
      <div class="absolute top-0 right-0 w-[400px] h-[400px] bg-[#00e5ff0a] rounded-full blur-[120px] -z-10 pointer-events-none"></div>

      <!-- Header -->
      <header class="h-20 border-b border-[#ffffff0a] px-8 flex items-center justify-between backdrop-blur-md z-10 shrink-0">
        <div class="relative w-96"></div>
        <div class="flex items-center gap-6">
          <div class="flex flex-col items-end">
            <span class="text-xs text-gray-500 uppercase font-semibold">Status</span>
            <span class="text-[10px] flex items-center gap-1.5 text-[#2ecc71]"><span class="w-1.5 h-1.5 bg-[#2ecc71] rounded-full animate-pulse"></span> Secure Node</span>
          </div>
        </div>
      </header>

      <section class="p-8 flex-1 overflow-y-auto">
        <div class="flex items-center justify-between mb-8">
          <h2 class="text-2xl font-bold tracking-tight text-white flex items-center gap-3">
            <span class="w-2 h-8 bg-[#00e5ff] rounded-full"></span>
            Creator Hub
          </h2>
        </div>
        
        <?php if(isset($message)): ?><div class="bg-[#2ecc71]/10 border border-[#2ecc71]/30 text-[#2ecc71] p-3 rounded-lg mb-6 text-sm"><?= $message ?></div><?php endif; ?>
        <?php if(isset($error)): ?><div class="bg-red-500/10 border border-red-500/30 text-red-500 p-3 rounded-lg mb-6 text-sm"><?= $error ?></div><?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Sidebar -->
          <div class="space-y-6">
            <div class="bg-[#0f1116] border border-[#ffffff0a] rounded-2xl p-6">
              <h2 class="text-xs uppercase tracking-widest text-gray-500 font-bold flex items-center gap-2 mb-4">Wallet Balance</h2>
              <div class="text-4xl font-mono text-[#2ecc71] mb-2 font-light">₱<?= number_format($user['wallet_balance'] ?? 0, 2) ?></div>
              <p class="text-gray-600 text-[10px] mb-6 uppercase tracking-widest font-bold">Min. Payout ₱100</p>
              
              <hr class="border-[#ffffff0a] mb-6" />
              
              <h3 class="text-xs uppercase tracking-widest text-gray-300 font-bold mb-4">Request Cashout</h3>
              <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="cashout">
                <div>
                  <label class="block text-[10px] uppercase tracking-widest text-gray-500 font-bold mb-1.5">Amount (PHP)</label>
                  <input type="number" name="amount" min="100" max="<?= $user['wallet_balance'] ?>" step="0.01" required class="w-full bg-[#1a1d24] border border-[#ffffff11] rounded-lg px-4 py-2.5 text-sm text-white focus:outline-none focus:border-[#00e5ff] transition-colors" />
                </div>
                <div>
                  <label class="block text-[10px] uppercase tracking-widest text-gray-500 font-bold mb-1.5">Method</label>
                  <select name="method" required class="w-full bg-[#1a1d24] border border-[#ffffff11] rounded-lg px-4 py-2.5 text-sm text-white focus:outline-none focus:border-[#00e5ff] transition-colors appearance-none">
                    <option value="gcash">GCash</option>
                    <option value="paypal">PayPal</option>
                  </select>
                </div>
                <button type="submit" class="w-full bg-[#00e5ff11] border border-[#00e5ff33] text-[#00e5ff] hover:bg-[#00e5ff] hover:text-black hover:shadow-[0_0_15px_rgba(0,229,255,0.4)] transition-all font-bold py-3 rounded-lg mt-2 text-xs uppercase tracking-widest">
                  Withdraw Funds
                </button>
              </form>
            </div>
          </div>

          <!-- Main Content -->
          <div class="lg:col-span-2 space-y-6">
            <div class="bg-[#0f1116] border border-[#ffffff0a] rounded-2xl p-6">
              <h2 class="text-xs uppercase tracking-widest text-gray-500 font-bold flex items-center gap-2 mb-6">Upload New Asset</h2>
              <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <input type="hidden" name="action" value="upload">
                <div class="space-y-1.5">
                  <label class="block text-[10px] uppercase tracking-widest text-gray-500 font-bold">Title</label>
                  <input type="text" name="title" required class="w-full bg-[#1a1d24] border border-[#ffffff11] rounded-lg px-4 py-2.5 text-sm focus:border-[#00e5ff] outline-none transition-colors" />
                </div>
                <div class="space-y-1.5">
                  <label class="block text-[10px] uppercase tracking-widest text-gray-500 font-bold">Category</label>
                  <select name="category" required class="w-full bg-[#1a1d24] border border-[#ffffff11] rounded-lg px-4 py-2.5 text-sm focus:border-[#00e5ff] outline-none transition-colors appearance-none">
                    <option>Android APKs</option>
                    <option>Reviewers</option>
                    <option>Tools & Software</option>
                    <option>Configs</option>
                  </select>
                </div>
                <div class="space-y-1.5">
                  <label class="block text-[10px] uppercase tracking-widest text-gray-500 font-bold">File Size</label>
                  <input type="text" name="file_size" placeholder="e.g. 15MB" required class="w-full bg-[#1a1d24] border border-[#ffffff11] rounded-lg px-4 py-2.5 text-sm focus:border-[#00e5ff] outline-none transition-colors" />
                </div>
                <div class="space-y-1.5">
                  <label class="block text-[10px] uppercase tracking-widest text-gray-500 font-bold">Download URL</label>
                  <input type="url" name="download_url" placeholder="Drive / Mediafire Link" required class="w-full bg-[#1a1d24] border border-[#ffffff11] rounded-lg px-4 py-2.5 text-sm focus:border-[#00e5ff] outline-none transition-colors" />
                </div>
                <div class="space-y-1.5 md:col-span-2">
                  <label class="block text-[10px] uppercase tracking-widest text-gray-500 font-bold">Description</label>
                  <textarea name="description" rows="3" class="w-full bg-[#1a1d24] border border-[#ffffff11] rounded-lg px-4 py-2.5 text-sm focus:border-[#00e5ff] outline-none transition-colors resize-none"></textarea>
                </div>
                <div class="md:col-span-2 mt-2">
                  <button type="submit" class="w-full bg-transparent border border-[#2ecc71] text-[#2ecc71] font-bold py-3.5 rounded-lg hover:bg-[#2ecc71] hover:text-black hover:shadow-[0_0_15px_rgba(46,204,113,0.4)] transition-all text-xs uppercase tracking-widest">
                    Publish Asset
                  </button>
                </div>
              </form>
            </div>

            <div class="bg-[#0f1116] border border-[#ffffff0a] rounded-2xl p-6">
              <h2 class="text-xs uppercase tracking-widest text-gray-500 font-bold flex items-center gap-2 mb-6">My Uploads</h2>
              <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                  <thead>
                    <tr class="border-b border-[#ffffff0a]">
                      <th class="pb-3 text-[10px] uppercase tracking-widest text-gray-500 font-bold">Title</th>
                      <th class="pb-3 text-[10px] uppercase tracking-widest text-gray-500 font-bold">Category</th>
                      <th class="pb-3 text-[10px] uppercase tracking-widest text-gray-500 font-bold text-right">Downloads</th>
                      <th class="pb-3 text-[10px] uppercase tracking-widest text-gray-500 font-bold text-right">Earnings</th>
                    </tr>
                  </thead>
                  <tbody class="text-sm">
                    <?php foreach($myFiles as $f): ?>
                    <tr class="border-b border-[#ffffff0a] hover:bg-[#1a1d24] transition-colors">
                      <td class="py-4 font-medium text-white"><?= esc($f['title']) ?></td>
                      <td class="py-4 text-gray-500"><?= esc($f['category']) ?></td>
                      <td class="py-4 text-gray-300 font-mono text-right"><?= number_format($f['total_downloads']) ?></td>
                      <td class="py-4 text-[#2ecc71] font-mono text-right font-medium">₱<?= number_format($f['total_downloads'] * 0.15, 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>
  </body>
</html>
