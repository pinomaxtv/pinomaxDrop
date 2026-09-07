<?php
require 'config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT f.*, u.username FROM pd_files f LEFT JOIN pd_users u ON f.user_id = u.id WHERE f.id = ?");
$stmt->execute([$id]);
$file = $stmt->fetch();

if (!$file) {
    die("<div style='background:#050608;color:#ff4757;height:100vh;display:flex;align-items:center;justify-content:center;font-family:sans-serif;font-weight:bold;'>⚠️ Error: Hindi mahanap ang file na ito o nabura na ng uploader.</div>");
}

// 🛡️ CHECK KUNG IKAW ANG UPLOADER O ADMIN
$current_uid = $_SESSION['pd_user_id'] ?? null;
$current_email = strtolower($_SESSION['pd_email'] ?? '');
$is_admin = ($current_uid == 1 || in_array($current_email, ['admin@pinomax.tv', 'roderickalmaras05@gmail.com']));
$can_edit = ($current_uid && ($current_uid == $file['user_id'] || $is_admin));

// ✏️ PROCESS FORM PAG PININDOT ANG SAVE CHANGES
$successMsg = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_file' && $can_edit) {
    $new_title = trim($_POST['title']);
    $new_desc = trim($_POST['description'] ?? '');
    $new_cat = $_POST['category'];
    $new_size = trim($_POST['file_size']);
    $new_url = trim($_POST['download_url']);
    $new_img = trim($_POST['image_url'] ?? '');

    $upd = $pdo->prepare("UPDATE pd_files SET title = ?, description = ?, category = ?, file_size = ?, download_url = ?, image_url = ? WHERE id = ?");
    $upd->execute([$new_title, $new_desc, $new_cat, $new_size, $new_url, $new_img, $id]);

    // Refresh file data
    $stmt->execute([$id]);
    $file = $stmt->fetch();
    $successMsg = true;
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
      
      <!-- NOTIFICATION KUNG NA-EDIT -->
      <?php if ($successMsg): ?>
        <div class="bg-[#2ecc71]/15 border border-[#2ecc71]/30 text-[#2ecc71] p-3 rounded-xl mb-4 text-xs font-bold flex items-center gap-2">
          <i class="fa-solid fa-check"></i> Matagumpay na na-update ang file details!
        </div>
      <?php endif; ?>

      <!-- TOP HEADER -->
      <div class="flex items-center justify-between gap-3 mb-5">
        <div class="flex items-center gap-3 overflow-hidden">
          <a href="index.php" class="w-9 h-9 bg-white/5 hover:bg-white/10 rounded-xl flex items-center justify-center text-gray-400 hover:text-white transition-colors shrink-0">
            <i class="fa-solid fa-arrow-left text-sm"></i>
          </a>
          <h1 class="text-base sm:text-lg font-black text-white tracking-tight leading-snug line-clamp-1"><?= esc($file['title']) ?></h1>
        </div>

        <!-- 👑 EDIT BUTTON (LALABAS LANG SA OWNER AT ADMIN) -->
        <?php if ($can_edit): ?>
        <button onclick="toggleEditModal(true)" class="bg-[#00e5ff]/10 border border-[#00e5ff33] hover:bg-[#00e5ff] text-[#00e5ff] hover:text-black font-bold px-3 py-1.5 rounded-xl text-xs flex items-center gap-1.5 transition-all shrink-0">
          <i class="fa-solid fa-pen-to-square"></i> <span>Edit</span>
        </button>
        <?php endif; ?>
      </div>
      
      <!-- BANNER / PREVIEW BOX -->
      <div class="w-full h-40 sm:h-48 bg-[#141720] rounded-2xl mb-5 relative overflow-hidden flex items-center justify-center border border-white/5 shadow-inner">
        <?php if (!empty($file['image_url'])): ?>
          <img src="<?= esc($file['image_url']) ?>" alt="Cover" class="w-full h-full object-cover">
        <?php else: ?>
          <i class="fa-solid fa-box-open text-4xl sm:text-5xl text-gray-700"></i>
        <?php endif; ?>

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

    <!-- ✏️ EDIT FILE MODAL POPUP (Para sa Owner & Admin) -->
    <?php if ($can_edit): ?>
    <div id="editModal" class="fixed inset-0 bg-[#050608]/90 z-[999] hidden items-center justify-center p-4 backdrop-blur-md overflow-y-auto">
      <div class="bg-[#0e1118] border border-[#00e5ff33] rounded-3xl p-6 w-full max-w-md my-8 shadow-2xl">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-white/5">
          <h3 class="text-sm font-black text-white uppercase tracking-wider flex items-center gap-2">
            <i class="fa-solid fa-pen-to-square text-[#00e5ff]"></i> Edit Asset Information
          </h3>
          <button onclick="toggleEditModal(false)" class="text-gray-500 hover:text-white text-base">✕</button>
        </div>

        <form method="POST" class="space-y-3.5 text-left">
          <input type="hidden" name="action" value="edit_file">
          
          <div>
            <label class="block text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Title</label>
            <input type="text" name="title" value="<?= esc($file['title']) ?>" required class="w-full bg-[#161a23] border border-white/10 rounded-xl px-3.5 py-2 text-xs text-white focus:border-[#00e5ff] outline-none" />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Category</label>
              <select name="category" class="w-full bg-[#161a23] border border-white/10 rounded-xl px-3 py-2 text-xs text-white focus:border-[#00e5ff] outline-none">
                <option <?= $file['category'] === 'Android APKs' ? 'selected' : '' ?>>Android APKs</option>
                <option <?= $file['category'] === 'Reviewers' ? 'selected' : '' ?>>Reviewers</option>
                <option <?= $file['category'] === 'Tools & Software' ? 'selected' : '' ?>>Tools & Software</option>
                <option <?= $file['category'] === 'Configs' ? 'selected' : '' ?>>Configs</option>
              </select>
            </div>
            <div>
              <label class="block text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Size</label>
              <input type="text" name="file_size" value="<?= esc($file['file_size']) ?>" required class="w-full bg-[#161a23] border border-white/10 rounded-xl px-3.5 py-2 text-xs text-white focus:border-[#00e5ff] outline-none" />
            </div>
          </div>

          <div>
            <label class="block text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Download URL</label>
            <input type="url" name="download_url" value="<?= esc($file['download_url']) ?>" required class="w-full bg-[#161a23] border border-white/10 rounded-xl px-3.5 py-2 text-xs text-white focus:border-[#00e5ff] outline-none" />
          </div>

          <div>
            <label class="block text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Image Preview URL</label>
            <input type="url" name="image_url" value="<?= esc($file['image_url'] ?? '') ?>" placeholder="https://..." class="w-full bg-[#161a23] border border-white/10 rounded-xl px-3.5 py-2 text-xs text-white focus:border-[#00e5ff] outline-none" />
          </div>

          <div>
            <label class="block text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Description</label>
            <textarea name="description" rows="3" class="w-full bg-[#161a23] border border-white/10 rounded-xl px-3.5 py-2 text-xs text-white focus:border-[#00e5ff] outline-none resize-none"><?= esc($file['description']) ?></textarea>
          </div>

          <div class="flex gap-2 pt-2">
            <button type="button" onclick="toggleEditModal(false)" class="w-1/2 py-2.5 bg-white/5 hover:bg-white/10 text-gray-400 rounded-xl text-xs font-bold uppercase tracking-wider">Cancel</button>
            <button type="submit" class="w-1/2 py-2.5 bg-[#00e5ff] hover:bg-[#00c6ff] text-black font-black rounded-xl text-xs uppercase tracking-wider shadow-lg">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
    <?php endif; ?>

    <script>
        function toggleEditModal(show) {
            const modal = document.getElementById('editModal');
            if (!modal) return;
            if (show) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            } else {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

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