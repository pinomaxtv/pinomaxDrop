<?php
require 'config.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT f.*, u.username FROM pd_files f JOIN pd_users u ON f.user_id = u.id WHERE f.id = ?");
$stmt->execute([$id]);
$file = $stmt->fetch();
if (!$file) die("File not found.");
$shareLink = "http://" . $_SERVER['HTTP_HOST'] . "/pinodrop/item.php?id=" . $id;
?>
<!doctype html>
<html style="height: 100%; margin: 0;">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= esc($file['title']) ?> - PinoDrop</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
      body { margin: 0; height: 100%; display: flex; align-items: center; justify-content: center; overflow: hidden; }
    </style>
  </head>
  <body class="bg-[#050608] text-[#e0e0e0] font-sans">
    <div class="absolute top-0 right-0 w-[400px] h-[400px] bg-[#00e5ff0a] rounded-full blur-[120px] -z-10 pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-[#2ecc710a] rounded-full blur-[120px] -z-10 pointer-events-none"></div>
    
    <div class="relative z-10 bg-[#0f1116] border border-[#ffffff0a] p-10 rounded-2xl shadow-[0_0_30px_rgba(0,229,255,0.05)] w-full max-w-lg">
      <div class="flex items-center gap-3 mb-6">
        <a href="index.php" class="text-gray-500 hover:text-[#00e5ff] transition-colors"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg></a>
        <h2 class="text-2xl font-bold text-white tracking-tight leading-tight"><?= esc($file['title']) ?></h2>
      </div>
      
      <div class="w-full h-40 bg-[#1a1d24] rounded-xl mb-6 relative overflow-hidden flex items-center justify-center border border-[#ffffff0a]">
        <span class="text-[60px] opacity-20">📦</span>
        <div class="absolute top-3 right-3 bg-[#00e5ff] text-black text-[10px] font-black px-2 py-0.5 rounded italic shadow-[0_0_8px_rgba(0,229,255,0.4)]">
          <?= esc($file['category']) ?>
        </div>
      </div>
      
      <p class="text-gray-400 text-sm mb-6"><?= nl2br(esc($file['description'])) ?: 'No description provided.' ?></p>
      
      <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="bg-[#1a1d24] rounded-lg p-3 border border-[#ffffff0a]">
          <div class="text-[10px] uppercase tracking-widest text-gray-600 font-bold mb-1">File Size</div>
          <div class="text-sm text-gray-300 font-mono"><?= esc($file['file_size']) ?></div>
        </div>
        <div class="bg-[#1a1d24] rounded-lg p-3 border border-[#ffffff0a]">
          <div class="text-[10px] uppercase tracking-widest text-gray-600 font-bold mb-1">Uploader</div>
          <div class="text-sm text-gray-300 font-mono">@<?= esc($file['username']) ?></div>
        </div>
      </div>
      
      <div class="mb-6 relative">
         <input type="text" id="shareUrl" value="<?= $shareLink ?>" readonly class="w-full bg-[#111318] border border-[#ffffff11] rounded-lg py-3 px-4 text-xs text-gray-400 focus:outline-none" />
         <button onclick="copyLink()" class="absolute right-2 top-2 bg-[#00e5ff11] text-[#00e5ff] hover:bg-[#00e5ff] hover:text-black px-3 py-1 rounded text-[10px] uppercase font-bold tracking-wider transition-all">Copy</button>
      </div>

      <a href="verify_download.php?id=<?= $file['id'] ?>" class="block w-full text-center py-4 bg-[#2ecc71]/10 border border-[#2ecc71]/30 text-[#2ecc71] font-bold rounded-xl uppercase tracking-widest text-xs hover:bg-[#2ecc71] hover:text-black hover:shadow-[0_0_20px_rgba(46,204,113,0.4)] transition-all">
        Proceed to Secure Download
      </a>
    </div>

    <script>
        function copyLink() {
            var copyText = document.getElementById("shareUrl");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            document.execCommand("copy");
            alert("Link copied to clipboard!");
        }
    </script>
  </body>
</html>
