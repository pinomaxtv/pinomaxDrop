<?php
require 'config.php';
$file_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Kunin ang info ng file para may magandang preview
$stmt = $pdo->prepare("SELECT f.*, u.username FROM pd_files f JOIN pd_users u ON f.user_id = u.id WHERE f.id = ? LIMIT 1");
$stmt->execute([$file_id]);
$file = $stmt->fetch();
// Dagdag: Kung pinindot ang download, mag +1 sa database
if (isset($_GET['action']) && $_GET['action'] === 'count') {
    $updateStmt = $pdo->prepare("UPDATE pd_files SET total_downloads = total_downloads + 1 WHERE id = ?");
    $updateStmt->execute([$file_id]);
    echo json_encode(['status' => 'success']);
    exit;
}

$title = $file['title'] ?? 'Asset Download';
$size = $file['file_size'] ?? 'N/A';
$category = $file['category'] ?? 'Asset';
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>Verify Download - PinoDrop</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- 🛡️ ADSTERRA DNS / ADBLOCK SCRIPT CHECKER -->
    <script src="https://tonicgoverness.com/f0/2b/64/f02b6459b919f52e6d5b802761ac9129.js"></script>

    <style>
      * { font-family: 'Plus Jakarta Sans', sans-serif; -webkit-tap-highlight-color: transparent; }
      body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #050608; overflow: hidden; user-select: none; }
    </style>
  </head>
  <body class="text-[#e0e0e0] p-4">
    
    <!-- 🚨 ANTI-DNS / ADBLOCK PERMANENT MODAL -->
    <div id="adblock-modal" class="fixed inset-0 bg-[#050608]/96 z-[99999] hidden flex-col items-center justify-center text-center p-6 backdrop-blur-xl">
        <div class="w-20 h-20 bg-red-500/20 rounded-full flex items-center justify-center mb-5 shadow-[0_0_40px_rgba(239,68,68,0.5)] animate-pulse">
            <i class="fa-solid fa-shield-halved text-red-500 text-3xl"></i>
        </div>
        <h1 class="text-2xl font-black text-red-500 tracking-tight uppercase mb-3">Adblock / Private DNS Detected</h1>
        <p class="text-gray-400 max-w-sm text-xs leading-relaxed mb-6">
            Paki-turn <b>OFF</b> ang iyong <b>Private DNS o AdBlocker</b> sa phone settings para ma-unlock at ma-verify ang direct download link.
        </p>
        <button onclick="location.reload()" class="bg-red-600 hover:bg-red-500 text-white font-bold py-2.5 px-6 rounded-xl text-xs uppercase tracking-wider transition-all">
            Subukan Muli
        </button>
    </div>

    <!-- MAIN CARD -->
    <div class="relative w-full max-w-md">
        <div class="absolute -inset-2 bg-[#00e5ff]/10 blur-3xl rounded-full pointer-events-none"></div>
        
        <div class="relative z-10 bg-[#0e1118]/90 border border-[#00e5ff33] p-6 sm:p-8 rounded-3xl shadow-[0_0_40px_rgba(0,229,255,0.12)] text-center backdrop-blur-xl">
            
            <!-- File Info Pill -->
            <div class="inline-flex items-center gap-2 bg-[#161a23] border border-white/10 px-3 py-1 rounded-full mb-5 text-[10px] text-gray-400">
                <span class="text-[#00e5ff] font-bold"><?= esc($category) ?></span>
                <span>•</span>
                <span><?= esc($size) ?></span>
            </div>

            <h1 class="text-lg font-extrabold text-white mb-2 leading-snug line-clamp-1"><?= esc($title) ?></h1>
            <p class="text-gray-500 text-[11px] mb-6">Free Access & Human Verification Gate</p>

            <!-- STEP 1: VISIT SPONSOR -->
            <div id="step1" class="transition-all duration-300">
                <div class="w-14 h-14 bg-[#00e5ff]/10 rounded-2xl flex items-center justify-center mx-auto mb-5 border border-[#00e5ff33] shadow-[0_0_15px_rgba(0,229,255,0.2)]">
                    <i class="fa-solid fa-lock text-[#00e5ff] text-xl"></i>
                </div>
                <h2 class="text-sm font-bold text-white uppercase tracking-wider mb-2">Security Verification</h2>
                <p class="text-gray-400 text-xs mb-6 leading-relaxed">
                    I-click ang button sa ibaba at magbabad nang <b>hindi bababa sa 15 segundo</b> sa bubuksang sponsor tab bago bumalik dito.
                </p>

                <button onclick="handleStartAd()" id="sponsor-btn" class="w-full py-3.5 bg-gradient-to-r from-[#0072ff] to-[#00c6ff] hover:opacity-95 text-white font-black rounded-xl uppercase tracking-wider text-xs shadow-[0_4px_20px_rgba(0,114,255,0.4)] transition-all active:scale-[0.98] flex items-center justify-center gap-2">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Step 1: Visit Sponsor Ad
                </button>
            </div>

            <!-- STEP 2: COUNTDOWN & AUTO-REFRESH BANNER -->
            <div id="step2" class="hidden transition-all duration-300">
                <div class="w-14 h-14 bg-[#2ecc71]/10 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-[#2ecc71]/30">
                    <i class="fa-solid fa-spinner fa-spin text-[#2ecc71] text-xl"></i>
                </div>
                <h2 class="text-sm font-bold text-white uppercase tracking-wider mb-1">Verifying Sponsor Visit</h2>
                <p class="text-gray-400 text-xs mb-4" id="countdown-desc">Tinatapos ang 30s verification para ma-credit ang uploader...</p>
                
                <div class="w-full h-1.5 bg-[#161a23] rounded-full overflow-hidden mb-3 border border-white/5">
                    <div id="progress-fill" class="h-full bg-gradient-to-r from-[#00e5ff] to-[#2ecc71] transition-all duration-1000 ease-linear shadow-[0_0_10px_rgba(0,229,255,0.6)]" style="width: 0%"></div>
                </div>

                <div id="timer" class="text-3xl font-mono text-[#00e5ff] mb-4 font-black">30s</div>

                <!-- 🔄 AUTO-REFRESHING ADSTERRA BANNER CONTAINER -->
                <div class="w-full min-h-[55px] bg-[#090b0e] border border-white/10 rounded-xl flex items-center justify-center overflow-hidden relative mb-2 shadow-inner">
                    <div id="adBannerBox" class="w-full flex justify-center items-center"></div>
                </div>
                <span class="text-[9px] text-gray-600 uppercase tracking-widest block mb-4">Sponsored Ad (Auto-Refreshes 10s)</span>
            </div>

            <!-- STEP 3: UNLOCKED & DOWNLOAD READY -->
            <div id="step3" class="hidden transition-all duration-300">
                <div class="w-14 h-14 bg-[#2ecc71]/10 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-[#2ecc71]/30 shadow-[0_0_20px_rgba(46,204,113,0.3)]">
                    <i class="fa-solid fa-circle-check text-[#2ecc71] text-2xl"></i>
                </div>
                <h2 class="text-sm font-bold text-white uppercase tracking-wider mb-1">Download Unlocked! 🎉</h2>
                <p class="text-gray-400 text-xs mb-6">Matagumpay na na-verify ang iyong access. Handa na ang iyong file!</p>

                <!-- Kukunin ang totoong download url -->
                <a href="<?= esc($file['download_url'] ?? '#') ?>" target="_blank" class="w-full py-4 bg-gradient-to-r from-[#2ecc71] to-[#00e5ff] hover:opacity-95 text-black font-black rounded-xl uppercase tracking-wider text-xs shadow-[0_4px_25px_rgba(46,204,113,0.5)] transition-all flex items-center justify-center gap-2 animate-bounce">
                    <i class="fa-solid fa-download"></i> Step 2: Direct Download Now 🚀
                </a>
            </div>

            <div class="mt-6 pt-4 border-t border-white/10">
                <a href="index.php" class="text-xs text-gray-500 hover:text-gray-300 transition-colors">← Cancel and return to store</a>
            </div>
        </div>
    </div>

    <!-- 🚨 CUSTOM RED WARNING POPUP (15 SECONDS CHECK) -->
    <div id="customWarningModal" class="fixed inset-0 bg-[#050608]/94 z-[9999] hidden items-center justify-center p-4 backdrop-blur-md">
        <div class="bg-[#11141A] border border-red-500/40 rounded-3xl p-6 text-center max-w-xs w-full shadow-[0_0_40px_rgba(239,68,68,0.35)]">
            <div class="text-4xl mb-2">⚠️</div>
            <h3 class="text-red-500 font-extrabold text-sm uppercase mb-2">Masyadong Mabilis Bumalik!</h3>
            <p id="warningMsg" class="text-gray-300 text-xs leading-relaxed mb-5">
                Kailangan mong magbabad nang hindi bababa sa <b>15 SECONDS</b> sa sponsor ad bago bumalik.
            </p>
            <button onclick="closeWarning()" class="w-full py-2.5 bg-gradient-to-r from-red-600 to-red-700 text-white font-black rounded-xl text-xs uppercase tracking-wider">
                Subukan Muli
            </button>
        </div>
    </div>

    <script>
        // 1. DNS & AdBlocker Detector
        async function checkAdBlock() {
            try {
                await fetch("https://tonicgoverness.com/f0/2b/64/f02b6459b919f52e6d5b802761ac9129.js", {
                    method: 'HEAD', mode: 'no-cors', cache: 'no-store'
                });
                return true;
            } catch (e) {
                return false;
            }
        }

        let adClicked = false;
        let adLeaveTime = 0;
        let verified = false;

        async function handleStartAd() {
            const isAllowed = await checkAdBlock();
            if (!isAllowed) {
                document.getElementById('adblock-modal').classList.remove('hidden');
                document.getElementById('adblock-modal').classList.add('flex');
                return;
            }

            adClicked = true;
            adLeaveTime = Date.now();

            // 💰 TOTOONG ADSTERRA DIRECT LINK MO
            window.open("https://entertainenslave.com/si3916b3n?key=7c1c6c7cf527860c2265a0bc006f4878", "_blank");
        }

        // 2. 15-SECOND ANTI-CHEAT TIME STAY LISTENER
        function checkUserReturn() {
            if (adClicked && !verified) {
                let timeAway = Math.floor((Date.now() - adLeaveTime) / 1000);

                if (timeAway < 15) {
                    adClicked = false;
                    document.getElementById('warningMsg').innerHTML = `<b>${timeAway} segundo</b> ka pa lang sa ad.<br><br>Kailangan mong manatili roon nang hindi bababa sa <b>15 SECONDS</b> para ma-verify ang iyong download.`;
                    document.getElementById('customWarningModal').classList.remove('hidden');
                    document.getElementById('customWarningModal').classList.add('flex');
                } else {
                    verified = true;
                    startCountdown();
                }
            }
        }

        window.addEventListener('focus', checkUserReturn);
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') checkUserReturn();
        });

        function closeWarning() {
            document.getElementById('customWarningModal').classList.add('hidden');
            document.getElementById('customWarningModal').classList.remove('flex');
        }

        // 3. 10s AUTO-REFRESH BANNER FUNCTION
        function refreshAdsterraBanner() {
            const box = document.getElementById('adBannerBox');
            if (!box) return;

            const iframe = document.createElement('iframe');
            iframe.style.width = "320px";
            iframe.style.height = "50px";
            iframe.style.border = "none";
            iframe.style.overflow = "hidden";
            iframe.scrolling = "no";
            const cb = Date.now();

            iframe.srcdoc = `
                <!DOCTYPE html>
                <html><body style="margin:0;padding:0;display:flex;justify-content:center;align-items:center;background:transparent;">
                    <script type="text/javascript">
                        atOptions = {
                            'key' : 'c158b1215b30e9113bdf69351dd49235',
                            'format' : 'iframe',
                            'height' : 50,
                            'width' : 320,
                            'params' : {}
                        };
                    <\/script>
                    <script type="text/javascript" src="https://tonicgoverness.com/c158b1215b30e9113bdf69351dd49235/invoke.js?cb=${cb}"><\/script>
                </body></html>
            `;
            box.innerHTML = '';
            box.appendChild(iframe);
        }

        // 4. 30s COUNTDOWN LOGIC
        function startCountdown() {
            document.getElementById('step1').classList.add('hidden');
            document.getElementById('step2').classList.remove('hidden');

            refreshAdsterraBanner();

            let timeLeft = 30;
            let timerEl = document.getElementById('timer');
            let fillEl = document.getElementById('progress-fill');
            
            let interval = setInterval(() => {
                timeLeft--;
                timerEl.innerText = `${timeLeft}s`;
                fillEl.style.width = (((30 - timeLeft) / 30) * 100) + '%';
                
                // Mag-refresh ang banner sa 20s at 10s
                if (timeLeft === 20 || timeLeft === 10) {
                    refreshAdsterraBanner();
                }
                
                if (timeLeft <= 0) {
                    clearInterval(interval);
                    document.getElementById('step2').classList.add('hidden');
                    document.getElementById('step3').classList.remove('hidden');

                    // 1. Automatic commission payout kay Uploader na may Alert
fetch('api_credit.php?file_id=<?= $file_id ?>', { method: 'POST' })
    .then(res => res.json())
    .then(data => {
        alert("PAYOUT STATUS: " + JSON.stringify(data));
    })
    .catch(err => {
        alert("FETCH ERROR: " + err.message);
    });

                    // 2. TOTOONG COUNTER: Dagdag +1 sa Download count
                    fetch('verify_download.php?id=<?= $file_id ?>&action=count');
                }
            }, 1000);
        }
    </script>
  </body>
</html>