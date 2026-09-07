<?php
require 'config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT f.*, u.username FROM pd_files f LEFT JOIN pd_users u ON f.user_id = u.id WHERE f.id = ?");
$stmt->execute([$id]);
$file = $stmt->fetch();

if (!$file) {
    die("<div style='background:#050608;color:#ff4757;height:100vh;display:flex;align-items:center;justify-content:center;font-family:sans-serif;font-weight:bold;'>⚠️ Error: Hindi mahanap ang file na ito o nabura na ng uploader.</div>");
}

// 🌐 Dynamic HTTPS Share Link Generator
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443 || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? "https://" : "http://";
$shareLink = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title><?= esc($file['title']) ?> - PinoDrop</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
      * { font-family: 'Plus Jakarta Sans', sans-serif; -webkit-tap-highlight-color: transparent; }
      body { margin: 0; min-height: 100vh; background: #050608; }
    </style>
  </head>
  <body class="text-[#e0e0e0] flex items-center justify-center p-4 sm:p-6 py-10 relative overflow-x-hidden">
    
    <!-- AMBIENT BACKGROUND GLOW -->
    <div class="fixed top-0 right-0 w-[350px] h-[350px] bg-[#00e5ff0a] rounded-full blur-[100px] pointer-events-none"></div>
    <div class="fixed bottom-0 left-0 w-[350px] h-[350px] bg-[#2ecc710a] rounded-full blur-[100px] pointer-events-none"></div>
    
    <div class="relative z-10 bg-[#0e1118]/90 border border-[#00e5ff33] p-6 sm:p-8 rounded-3xl shadow-[0_0_40px_rgba(0,229,255,0.1)] w-full max-w-lg backdrop-blur-xl">
      
      <!-- TOP HEADER -->
      <div class="flex items-center gap-3 mb-5">
        <a href="index.php" class="w-9 h-9 bg-white/5 hover:bg-white/10 rounded-xl flex items-center justify-center text-gray-400 hover:text-white transition-colors">
          <i class="fa-solid fa-arrow-left text-sm"></i>
        </a>
        <div class="flex-1 overflow-hidden">
          <h1 class="text-base sm:text-xl font-black text-white tracking-tight leading-snug line-clamp-1"><?= esc($file['title']) ?></h1>
        </div>
      </div>
      
      <!-- BANNER / PREVIEW BOX -->
      <div class="w-full h-36 sm:h-44 bg-[#141720] rounded-2xl mb-5 relative overflow-hidden flex items-center justify-center border border-white/5 shadow-inner">
        <i class="fa-solid fa-box-open text-4xl sm:text-5xl text-gray-700"></i>
        <div class="absolute top-3 right-3 bg-[#00e5ff] text-black text-[10px] font-black px-2.5 py-0.5 rounded-lg italic shadow-[0_0_10px_rgba(0,229,255,0.4)]">
          <?= esc($file['category']) ?>
        </div>
      </div>
      
      <!-- DESCRIPTION -->
      <div class="bg-[#141720]/60 border border-white/5 rounded-2xl p-4 mb-5">
        <span class="text-[10px] uppercase font-bold text-gray-500 tracking-wider block mb-1">About this asset:</span>
        <p class="text-gray-300 text-xs sm:text-sm leading-relaxed whitespace-pre-line"><?= nl2br(esc($file['description'])) ?: 'No description provided by uploader.' ?></p>
      </div>
      
      <!-- METRICS GRID -->
      <div class="grid grid-cols-2 gap-3 mb-5">
        <div class="bg-[#141720] rounded-2xl p-3 border border-white/5 flex items-center gap-3">
          <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center text-sm">
            <i class="fa-solid fa-file-zipper"></i>
          </div>
          <div>
            <div class="text-[9px] uppercase tracking-wider text-gray-500 font-bold">File Size</div>
            <div class="text-xs sm:text-sm text-gray-200 font-mono font-bold"><?= esc($file['file_size']) ?></div>
          </div>
        </div>

        <div class="bg-[#141720] rounded-2xl p-3 border border-white/5 flex items-center gap-3">
          <div class="w-8 h-8 rounded-xl bg-green-500/10 text-green-400 flex items-center justify-center text-sm">
            <i class="fa-solid fa-circle-user"></i>
          </div>
          <div>
            <div class="text-[9px] uppercase tracking-wider text-gray-500 font-bold">Uploader</div>
            <div class="text-xs sm:text-sm text-gray-200 font-mono font-bold">@<?= esc($file['username'] ?? 'Creator') ?></div>
          </div>
        </div>
      </div>
      
      <!-- SHARE BOX WITH CLIPBOARD API -->
      <div class="mb-5">
        <label class="block text-[10px] uppercase tracking-wider text-gray-500 font-bold mb-1">Shareable Asset URL</label>
        <div class="relative flex items-center">
          <input type="text" id="shareUrl" value="<?= esc($shareLink) ?>" readonly class="w-full bg-[#141720] border border-white/10 rounded-xl py-2.5 pl-3 pr-20 text-xs text-gray-400 focus:outline-none select-all font-mono" />
          <button onclick="copyShareLink()" class="absolute right-1.5 bg-[#00e5ff]/10 hover:bg-[#00e5ff] text-[#00e5ff] hover:text-black px-3 py-1.5 rounded-lg text-[10px] uppercase font-bold tracking-wider transition-all flex items-center gap-1.5">
            <i class="fa-regular fa-copy"></i> <span id="copyText">Copy</span>
          </button>
        </div>
      </div>

      <!-- PROCEED TO AD-GATE DOWNLOAD -->
      <a href="verify_download.php?id=<?= $file['id'] ?>" class="w-full py-4 bg-gradient-to-r from-[#2ecc71] to-[#00e5ff] hover:opacity-95 text-black font-black rounded-xl uppercase tracking-wider text-xs shadow-[0_4px_25px_rgba(46,204,113,0.4)] transition-all flex items-center justify-center gap-2 active:scale-98">
        <i class="fa-solid fa-shield-halved"></i> Proceed to Secure Download
      </a>
    </div>

    <script>
        async function copyShareLink() {
            const input = document.getElementById("shareUrl");
            const btnText = document.getElementById("copyText");
            try {
                await navigator.clipboard.writeText(input.value);
                btnText.innerText = "COPIED!";
                setTimeout(() => { btnText.innerText = "COPY"; }, 2000);
            } catch (err) {
                input.select();
                document.execCommand("copy");
                alert("Link copied to clipboard!");
            }
        }
    </script>
  </body>
</html>