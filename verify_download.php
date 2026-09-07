<?php
require 'config.php';
$file_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
?>
<!doctype html>
<html style="height: 100%; margin: 0;">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Verify Download - PinoDrop</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
      body { margin: 0; height: 100%; display: flex; align-items: center; justify-content: center; overflow: hidden; }
    </style>
  </head>
  <body class="bg-[#050608] text-[#e0e0e0] font-sans">
    
    <div id="adblock-modal" class="fixed inset-0 bg-[#050608]/95 z-[9999] hidden flex-col items-center justify-center text-center p-6 backdrop-blur-md">
        <div class="w-20 h-20 bg-red-500/20 rounded-full flex items-center justify-center mb-6 shadow-[0_0_30px_rgba(239,68,68,0.5)]">
            <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>
        <h1 class="text-3xl font-bold text-red-500 tracking-tighter uppercase mb-4 shadow-red-500 drop-shadow-lg">Adblock Detected</h1>
        <p class="text-gray-400 max-w-md text-sm">Please disable your AdBlocker or Private DNS to proceed with the download. Our creators rely on ad revenue to provide free assets.</p>
    </div>

    <div class="relative w-full max-w-lg p-6">
        <div class="absolute -inset-4 bg-[#00e5ff]/10 blur-3xl rounded-full z-0 pointer-events-none"></div>
        
        <div class="relative z-10 bg-[#0f1116] border border-[#ffffff0a] p-10 rounded-2xl shadow-[0_0_30px_rgba(0,229,255,0.05)] text-center w-full">
        
        <!-- Step 1 -->
        <div id="step1" class="transition-opacity duration-500">
            <div class="w-16 h-16 bg-[#00e5ff]/10 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-8 h-8 text-[#00e5ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            </div>
            <h2 class="text-xl font-bold text-white uppercase tracking-tighter mb-4">Security <span class="text-[#00e5ff]">Check</span></h2>
            <p class="text-gray-500 text-sm mb-8">To verify you are human and support the creator, please visit our sponsor.</p>
            <a href="https://example.com/adsterra-direct-link" target="_blank" id="sponsor-btn" class="block w-full py-4 bg-[#00e5ff11] border border-[#00e5ff33] text-[#00e5ff] font-bold rounded-xl uppercase tracking-widest text-xs hover:bg-[#00e5ff] hover:text-black hover:shadow-[0_0_20px_rgba(0,229,255,0.4)] transition-all cursor-pointer">
                Step 1: Visit Sponsor Ad
            </a>
        </div>

        <!-- Step 2 -->
        <div id="step2" class="hidden transition-opacity duration-500">
            <div class="w-16 h-16 bg-[#00e5ff]/10 rounded-full flex items-center justify-center mx-auto mb-6 animate-pulse">
                <svg class="w-8 h-8 text-[#00e5ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h2 class="text-xl font-bold text-white uppercase tracking-tighter mb-4">Verifying<span class="text-[#00e5ff]">...</span></h2>
            <p class="text-gray-500 text-sm mb-6">Please wait while we prepare your secure download.</p>
            
            <div class="w-full h-2 bg-[#1a1d24] rounded-full overflow-hidden mb-6 border border-[#ffffff0a]">
                <div id="progress-fill" class="h-full bg-gradient-to-r from-[#00e5ff] to-[#2ecc71] transition-all duration-1000 ease-linear shadow-[0_0_10px_rgba(0,229,255,0.5)]" style="width: 0%"></div>
            </div>
            <div id="timer" class="text-4xl font-mono text-white mb-8 font-light">30</div>

            <div class="w-full h-24 bg-[#1a1d24] border border-[#ffffff0a] rounded-xl flex items-center justify-center text-gray-600 text-xs font-medium uppercase tracking-widest overflow-hidden relative">
                <div class="absolute inset-0 bg-[linear-gradient(45deg,transparent_25%,rgba(255,255,255,0.02)_50%,transparent_75%)] bg-[length:250%_250%,100%_100%] animate-[shimmer_2s_infinite]"></div>
                <iframe id="ad-iframe" src="about:blank" class="w-full h-full border-none z-10 relative"></iframe>
            </div>
        </div>

        <!-- Step 3 -->
        <div id="step3" class="hidden transition-opacity duration-500">
            <div class="w-16 h-16 bg-[#2ecc71]/10 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-8 h-8 text-[#2ecc71]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h2 class="text-xl font-bold text-white uppercase tracking-tighter mb-4">Download <span class="text-[#2ecc71]">Ready</span></h2>
            <p class="text-gray-500 text-sm mb-8">Verification complete. Your file has been unlocked.</p>
            <a href="download_trigger.php?id=<?= $file_id ?>" class="block w-full py-4 bg-[#2ecc71]/10 border border-[#2ecc71]/30 text-[#2ecc71] font-bold rounded-xl uppercase tracking-widest text-xs hover:bg-[#2ecc71] hover:text-black hover:shadow-[0_0_20px_rgba(46,204,113,0.4)] transition-all">
                Step 2: Direct Download Now
            </a>
        </div>
        </div>
    </div>

    <script>
        // DNS & AdBlocker Detector
        fetch('https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js', { mode: 'no-cors' })
        .catch(() => {
            document.getElementById('adblock-modal').classList.remove('hidden');
            document.getElementById('adblock-modal').classList.add('flex');
        });

        let adClicked = false;
        let adLeaveTime = 0;
        let verified = false;

        document.getElementById('sponsor-btn').addEventListener('click', () => {
            adClicked = true;
            adLeaveTime = Date.now();
        });

        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible' && adClicked && !verified) {
                let timeAway = (Date.now() - adLeaveTime) / 1000;
                if (timeAway < 15) {
                    alert(`Masyadong Mabilis Kang Bumalik! ${Math.floor(timeAway)} segundo ka pa lang sa ad. Manatili roon nang hindi bababa sa 15 SECONDS bago bumalik para ma-verify ang download.`);
                    adClicked = false;
                } else {
                    verified = true;
                    startCountdown();
                }
            }
        });

        function startCountdown() {
            document.getElementById('step1').classList.add('hidden');
            document.getElementById('step2').classList.remove('hidden');
            
            document.getElementById('ad-iframe').src = "data:text/html,<html><body style='color:#555;text-align:center;font-family:sans-serif;background:transparent;margin:0;display:flex;align-items:center;justify-content:center;height:100%;font-size:10px;text-transform:uppercase;letter-spacing:2px;'>Adsterra Banner Space</body></html>";

            let timeLeft = 30;
            let timerEl = document.getElementById('timer');
            let fillEl = document.getElementById('progress-fill');
            
            let interval = setInterval(() => {
                timeLeft--;
                timerEl.innerText = timeLeft;
                fillEl.style.width = (((30 - timeLeft) / 30) * 100) + '%';
                
                if(timeLeft === 20 || timeLeft === 10) {
                    let iframe = document.getElementById('ad-iframe');
                    iframe.src = iframe.src; 
                }
                
                if (timeLeft <= 0) {
                    clearInterval(interval);
                    document.getElementById('step2').classList.add('hidden');
                    document.getElementById('step3').classList.remove('hidden');
                    fetch('api_credit.php?file_id=<?= $file_id ?>', { method: 'POST' });
                }
            }, 1000);
        }
    </script>
  </body>
</html>
