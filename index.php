<?php
require 'config.php';
$stmt = $pdo->query("SELECT f.*, u.username FROM pd_files f JOIN pd_users u ON f.user_id = u.id ORDER BY f.created_at DESC LIMIT 50");
$files = $stmt->fetchAll();
?>
<!doctype html>
<html style="height: 100%; margin: 0;">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>PinoDrop - Asset Market</title>
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
        <a href="index.php" class="flex items-center gap-3 px-4 py-3 bg-[#00e5ff11] border-l-2 border-[#00e5ff] text-white rounded-r-md transition-colors">
          <span class="text-sm font-medium">Asset Market</span>
        </a>
        <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 text-gray-500 hover:text-white transition-colors">
          <span class="text-sm font-medium">Creator Dashboard</span>
        </a>
      </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col relative overflow-hidden">
      <div class="absolute top-0 right-0 w-[400px] h-[400px] bg-[#00e5ff0a] rounded-full blur-[120px] -z-10 pointer-events-none"></div>

     <!-- Header with Dynamic Auth Buttons -->
      <header class="h-20 border-b border-[#ffffff0a] px-8 flex items-center justify-between backdrop-blur-md z-10 shrink-0">
        <div class="relative w-96">
          <input type="text" id="searchInput" placeholder="Search secure assets..." class="w-full bg-[#111318] border border-[#ffffff11] rounded-full py-2 px-10 text-sm focus:outline-none focus:border-[#00e5ff] placeholder-gray-600 transition-colors"/>
          <svg class="w-4 h-4 absolute left-4 top-2.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>

        <div class="flex items-center gap-4">
          <div class="hidden sm:flex flex-col items-end">
            <span class="text-xs text-gray-500 uppercase font-semibold">Status</span>
            <span class="text-[10px] flex items-center gap-1.5 text-[#2ecc71]"><span class="w-1.5 h-1.5 bg-[#2ecc71] rounded-full animate-pulse"></span> Secure Node</span>
          </div>

          <?php if (isset($_SESSION['pd_user_id'])): ?>
            <!-- NAKA-LOGIN: Ipakita ang Avatar at Logout -->
            <div class="flex items-center gap-3 bg-[#111318] border border-white/10 py-1.5 px-3 rounded-full">
              <img src="<?= esc($_SESSION['pd_avatar'] ?? 'https://ui-avatars.com/api/?name=User') ?>" class="w-7 h-7 rounded-full object-cover">
              <span class="text-xs font-bold text-white"><?= esc($_SESSION['pd_username'] ?? 'Creator') ?></span>
              <a href="logout.php" title="Sign Out" class="text-red-400 hover:text-red-300 text-xs ml-1"><i class="fa-solid fa-arrow-right-from-bracket"></i></a>
            </div>
          <?php else: ?>
            <!-- HINDI PA NAKA-LOGIN: Ipakita ang Login at Register -->
            <div class="flex items-center gap-2">
              <a href="login.php" class="text-xs font-bold text-gray-300 hover:text-white px-3 py-2">Sign In</a>
              <a href="register.php" class="text-xs font-bold bg-[#00e5ff] hover:bg-[#00c6ff] text-black px-4 py-2 rounded-xl shadow-[0_0_12px_rgba(0,229,255,0.3)] transition-transform active:scale-95">Register</a>
            </div>
          <?php endif; ?>
        </div>
      </header>

      <section class="p-8 flex-1 overflow-y-auto">
        <div class="flex items-center justify-between mb-8">
          <h2 class="text-2xl font-bold tracking-tight text-white flex items-center gap-3">
            <span class="w-2 h-8 bg-[#00e5ff] rounded-full"></span>
            Top Performing Assets
          </h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="assetGrid">
          <?php foreach($files as $file): ?>
          <div class="bg-[#0f1116] border border-[#ffffff0a] rounded-2xl p-5 hover:border-[#00e5ff44] transition-all group flex flex-col cursor-pointer asset-card" data-title="<?= strtolower(esc($file['title'])) ?>" onclick="window.location.href='item.php?id=<?= $file['id'] ?>'">
            <div class="w-full h-32 bg-[#1a1d24] rounded-xl mb-4 relative overflow-hidden flex items-center justify-center">
              <span class="text-[40px] opacity-20 group-hover:scale-110 transition-transform">📦</span>
              <div class="absolute top-3 right-3 bg-[#00e5ff] text-black text-[10px] font-black px-2 py-0.5 rounded italic shadow-[0_0_8px_rgba(0,229,255,0.4)]">
                <?= esc($file['category']) ?>
              </div>
            </div>
            <h3 class="text-white font-bold text-lg mb-1 leading-tight"><?= esc($file['title']) ?></h3>
            <p class="text-gray-500 text-xs mb-4 line-clamp-2 flex-grow"><?= esc($file['description']) ?: 'No description available.' ?></p>
            <div class="flex items-center justify-between pt-4 border-t border-[#ffffff0a]">
              <div class="flex flex-col">
                <span class="text-[10px] text-gray-600 uppercase font-bold tracking-widest">Size</span>
                <span class="text-sm text-gray-300 font-mono"><?= esc($file['file_size']) ?></span>
              </div>
              <div class="flex flex-col items-end">
                <span class="text-[10px] text-gray-600 uppercase font-bold tracking-widest">Downloads</span>
                <span class="text-sm text-[#00e5ff] font-mono"><?= number_format($file['total_downloads']) ?></span>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </section>
    </main>

    <script>
        document.getElementById('searchInput').addEventListener('input', function(e) {
            let term = e.target.value.toLowerCase();
            document.querySelectorAll('.asset-card').forEach(card => {
                let title = card.getAttribute('data-title');
                card.style.display = title.includes(term) ? 'flex' : 'none';
            });
        });
    </script>
  </body>
</html>
