<?php
require 'config.php';

// Kunin ang files
$stmt = $pdo->query("SELECT f.*, u.username FROM pd_files f LEFT JOIN pd_users u ON f.user_id = u.id ORDER BY f.created_at DESC LIMIT 50");
$files = $stmt->fetchAll();

$is_admin = false;
if (isset($_SESSION['pd_user_id'])) {
    $uid = $_SESSION['pd_user_id'];
    $u_email = strtolower($_SESSION['pd_email'] ?? '');
    if ($uid == 1 || $u_email === 'admin@pinomax.tv' || $u_email === 'roderickalmaras05@gmail.com') {
        $is_admin = true;
    }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>PinoDrop - Asset Market</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
      * { font-family: 'Plus Jakarta Sans', sans-serif; -webkit-tap-highlight-color: transparent; }
      body { margin: 0; min-height: 100vh; background: #050608; }
    </style>
  </head>
  <body class="text-[#e0e0e0] flex flex-col md:flex-row pb-20 md:pb-0 overflow-x-hidden">
    
    <!-- DESKTOP SIDEBAR (Tago sa Mobile) -->
    <aside class="hidden md:flex w-64 bg-[#0a0c10] border-r border-[#00e5ff33] flex-col p-6 shadow-xl z-20 shrink-0 min-h-screen">
      <div class="mb-10 flex items-center gap-3">
        <div class="w-10 h-10 bg-[#00e5ff] rounded-lg flex items-center justify-center shadow-[0_0_15px_rgba(0,229,255,0.4)]">
          <i class="fa-solid fa-cloud-arrow-down text-black text-lg"></i>
        </div>
        <span class="text-xl font-bold tracking-tighter text-white uppercase">Pino<span class="text-[#00e5ff]">Drop</span></span>
      </div>
      <nav class="flex-1 space-y-2">
        <div class="text-[10px] uppercase tracking-widest text-[#00e5ff99] mb-4 font-semibold">Main Hub</div>
        <a href="index.php" class="flex items-center gap-3 px-4 py-3 bg-[#00e5ff11] border-l-2 border-[#00e5ff] text-white rounded-r-md">
          <span class="text-sm font-medium">Asset Market</span>
        </a>
        <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 text-gray-500 hover:text-white transition-colors">
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
      <!-- HEADER -->
      <header class="h-16 md:h-20 border-b border-white/5 px-4 md:px-8 flex items-center justify-between backdrop-blur-md sticky top-0 z-30 bg-[#050608]/90">
        <div class="flex items-center gap-3 md:hidden">
            <div class="w-8 h-8 bg-[#00e5ff] rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-cloud-arrow-down text-black text-sm"></i>
            </div>
            <span class="text-lg font-black tracking-tighter text-white">PINO<span class="text-[#00e5ff]">DROP</span></span>
        </div>

        <div class="relative w-full max-w-xs md:max-w-md hidden sm:block">
          <input type="text" id="searchInput" placeholder="Search secure assets..." class="w-full bg-[#111318] border border-white/10 rounded-full py-2 px-10 text-xs text-white focus:outline-none focus:border-[#00e5ff]"/>
          <i class="fa-solid fa-search absolute left-4 top-3 text-gray-500 text-xs"></i>
        </div>

        <div class="flex items-center gap-3">
          <?php if ($is_admin): ?>
            <a href="admin.php" class="md:hidden bg-red-600 text-white font-black text-[10px] px-2.5 py-1.5 rounded-lg uppercase">Admin</a>
          <?php endif; ?>

          <?php if (isset($_SESSION['pd_user_id'])): ?>
            <a href="dashboard.php" class="flex items-center gap-2 bg-[#111318] border border-white/10 py-1.5 px-3 rounded-full">
              <img src="<?= esc($_SESSION['pd_avatar'] ?? 'https://ui-avatars.com/api/?name=User') ?>" class="w-6 h-6 rounded-full object-cover">
              <span class="text-xs font-bold text-white hidden sm:inline"><?= esc($_SESSION['pd_username'] ?? 'Creator') ?></span>
            </a>
            <a href="logout.php" title="Logout" class="text-red-400 hover:text-red-300 text-xs"><i class="fa-solid fa-power-off"></i></a>
          <?php else: ?>
            <a href="login.php" class="text-xs font-bold text-gray-300 hover:text-white px-2 py-1">Sign In</a>
            <a href="register.php" class="text-xs font-bold bg-[#00e5ff] text-black px-3.5 py-1.5 rounded-xl shadow-[0_0_12px_rgba(0,229,255,0.3)]">Register</a>
          <?php endif; ?>
        </div>
      </header>

      <!-- MOBILE SEARCH -->
      <div class="p-4 sm:hidden bg-[#0a0c10] border-b border-white/5">
        <input type="text" id="mobileSearchInput" placeholder="Search secure assets..." class="w-full bg-[#111318] border border-white/10 rounded-xl py-2 px-4 text-xs text-white focus:outline-none focus:border-[#00e5ff]"/>
      </div>

      <!-- STORE FEED SECTION -->
      <section class="p-4 sm:p-8 flex-1">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-lg sm:text-2xl font-black text-white flex items-center gap-3">
            <span class="w-1.5 h-6 bg-[#00e5ff] rounded-full"></span>
            Top Performing Assets
          </h2>
          <span class="text-xs text-gray-500 font-mono font-bold"><?= count($files) ?> Assets</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6" id="assetGrid">
          <?php foreach($files as $file): ?>
          <div class="bg-[#0f1116] border border-white/5 rounded-2xl p-4 sm:p-5 hover:border-[#00e5ff44] transition-all group flex flex-col cursor-pointer asset-card shadow-lg" data-title="<?= strtolower(esc($file['title'])) ?>" onclick="window.location.href='item.php?id=<?= $file['id'] ?>'">
            <div class="w-full h-28 sm:h-32 bg-[#1a1d24] rounded-xl mb-3 relative overflow-hidden flex items-center justify-center">
              <span class="text-3xl opacity-20 group-hover:scale-110 transition-transform">📦</span>
              <div class="absolute top-2.5 right-2.5 bg-[#00e5ff] text-black text-[9px] font-black px-2 py-0.5 rounded italic shadow-md">
                <?= esc($file['category']) ?>
              </div>
            </div>
            <h3 class="text-white font-bold text-sm sm:text-base mb-1 line-clamp-1"><?= esc($file['title']) ?></h3>
            <p class="text-gray-500 text-[11px] mb-4 line-clamp-2 flex-grow"><?= esc($file['description']) ?: 'Download this verified digital asset on PinoDrop.' ?></p>
            <div class="flex items-center justify-between pt-3 border-t border-white/5">
              <div class="flex flex-col">
                <span class="text-[9px] text-gray-600 uppercase font-bold tracking-wider">Size</span>
                <span class="text-xs text-gray-300 font-mono"><?= esc($file['file_size']) ?></span>
              </div>
              <div class="flex flex-col items-end">
                <span class="text-[9px] text-gray-600 uppercase font-bold tracking-wider">Downloads</span>
                <span class="text-xs text-[#00e5ff] font-mono font-bold"><?= number_format($file['total_downloads']) ?></span>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </section>
    </main>

    <!-- 📱 MOBILE BOTTOM NAVIGATION BAR -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 h-16 bg-[#0a0c10]/95 backdrop-blur-lg border-t border-white/10 flex items-center justify-around z-40 px-4">
        <a href="index.php" class="flex flex-col items-center gap-1 text-[#00e5ff]">
            <i class="fa-solid fa-store text-base"></i>
            <span class="text-[9px] font-bold">Store</span>
        </a>
        <a href="dashboard.php" class="flex flex-col items-center gap-1 text-gray-400 hover:text-white">
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

    <script>
        function filterAssets(term) {
            document.querySelectorAll('.asset-card').forEach(card => {
                let title = card.getAttribute('data-title');
                card.style.display = title.includes(term.toLowerCase()) ? 'flex' : 'none';
            });
        }
        document.getElementById('searchInput')?.addEventListener('input', e => filterAssets(e.target.value));
        document.getElementById('mobileSearchInput')?.addEventListener('input', e => filterAssets(e.target.value));
    </script>
  </body>
</html>