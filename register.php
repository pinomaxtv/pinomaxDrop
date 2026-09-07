<?php
require 'config.php';

if (isset($_SESSION['pd_user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($email) && !empty($password)) {
        if (strlen($password) < 6) {
            $error = 'Ang password ay dapat hindi bababa sa 6 characters.';
        } else {
            $check = $pdo->prepare("SELECT id FROM pd_users WHERE email = ? LIMIT 1");
            $check->execute([$email]);

            if ($check->fetch()) {
                $error = 'May account na ang email na ito! Mag-Sign in na lang.';
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $avatar = "https://ui-avatars.com/api/?name=" . urlencode($username) . "&background=2ecc71&color=000";

                $ins = $pdo->prepare("INSERT INTO pd_users (username, email, password_hash, avatar, wallet_balance) VALUES (?, ?, ?, ?, 0.00)");
                if ($ins->execute([$username, $email, $password_hash, $avatar])) {
                    $new_user_id = $pdo->lastInsertId();
                    $_SESSION['pd_user_id']  = $new_user_id;
                    $_SESSION['pd_username'] = $username;
                    $_SESSION['pd_email']    = $email;
                    $_SESSION['pd_avatar']   = $avatar;
                    header("Location: dashboard.php");
                    exit;
                } else {
                    $error = 'Hindi ma-save ang account. Pakisubukan muli.';
                }
            }
        }
    } else {
        $error = 'Pakisagutan ang lahat ng fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Create Account - PinoDrop</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- 🔑 OFFICIAL GOOGLE IDENTITY SERVICES SDK -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>

    <style>
      * { font-family: 'Plus Jakarta Sans', sans-serif; -webkit-tap-highlight-color: transparent; }
      body { margin: 0; min-height: 100vh; background: #050608; }
    </style>
</head>
<body class="text-[#e0e0e0] flex items-center justify-center min-h-screen p-4 relative overflow-hidden">

    <div class="fixed bottom-0 left-0 w-[350px] h-[350px] bg-[#2ecc710a] rounded-full blur-[100px] pointer-events-none"></div>

    <div class="w-full max-w-md bg-[#0e1118]/90 border border-[#2ecc7133] rounded-3xl p-6 sm:p-8 shadow-[0_0_40px_rgba(46,204,113,0.15)] relative overflow-hidden backdrop-blur-xl">
        <div class="text-center mb-6">
            <div class="w-12 h-12 bg-[#2ecc71] rounded-xl flex items-center justify-center mx-auto shadow-[0_0_20px_rgba(46,204,113,0.4)] mb-3">
                <i class="fa-solid fa-user-plus text-black text-xl"></i>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Join as a <span class="text-[#2ecc71]">Creator</span></h1>
            <p class="text-xs text-gray-500 mt-1">Earn ₱0.15 for every verified download on your assets</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-4 p-3 bg-red-500/10 border border-red-500/30 rounded-xl text-red-400 text-xs font-semibold text-center">
                <?= esc($error) ?>
            </div>
        <?php endif; ?>

        <!-- 🔑 GOOGLE ONE-TAP SIGNUP BUTTON -->
        <div class="mb-5 flex flex-col items-center">
            <div id="g_id_onload"
                 data-client_id="470197140246-4g1ud4guk5dhi5fhrgcdgli8mp8u9u41.apps.googleusercontent.com"
                 data-context="signup"
                 data-ux_mode="popup"
                 data-callback="handleGoogleSignup"
                 data-auto_prompt="false">
            </div>

            <div class="g_id_signin w-full flex justify-center"
                 data-type="standard"
                 data-shape="pill"
                 data-theme="filled_black"
                 data-text="signup_with"
                 data-size="large"
                 data-logo_alignment="left"
                 data-width="320">
            </div>
        </div>

        <div class="relative flex py-2 items-center mb-5">
            <div class="flex-grow border-t border-white/10"></div>
            <span class="flex-shrink mx-3 text-[10px] text-gray-500 uppercase tracking-widest font-bold">Or Register with Email</span>
            <div class="flex-grow border-t border-white/10"></div>
        </div>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Creator Username</label>
                <input type="text" name="username" required placeholder="PinoUploader" class="w-full bg-[#111318] border border-white/10 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-[#2ecc71] transition-colors">
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Email Address</label>
                <input type="email" name="email" required placeholder="you@email.com" class="w-full bg-[#111318] border border-white/10 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-[#2ecc71] transition-colors">
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Create Password</label>
                <input type="password" name="password" required placeholder="Minimum 6 characters" class="w-full bg-[#111318] border border-white/10 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-[#2ecc71] transition-colors">
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-[#2ecc71] to-[#00e5ff] hover:opacity-95 text-black font-black py-3 rounded-xl text-xs uppercase tracking-wider shadow-[0_4px_20px_rgba(46,204,113,0.3)] transition-transform active:scale-[0.98]">
                Create Account 🚀
            </button>
        </form>

        <p class="text-center text-xs text-gray-500 mt-6">
            Already have an account? 
            <a href="login.php" class="text-[#2ecc71] font-bold hover:underline">Sign In</a>
        </p>
        <div class="text-center mt-3">
            <a href="index.php" class="text-[11px] text-gray-600 hover:text-gray-400">← Back to Store</a>
        </div>
    </div>

   <!-- JAVASCRIPT HANDLER PARA SA GOOGLE LOGIN -->
    <script>
        async function handleGoogleLogin(response) {
            if (!response.credential) {
                return;
            }

            const formData = new FormData();
            formData.append('credential', response.credential);

            try {
                // I-save ang login sa background
                await fetch('google_callback.php', {
                    method: 'POST',
                    body: formData
                });
                
                // REKTA PASOK SA DASHBOARD! Walang cheche-bureche at walang popup!
                window.location.href = 'dashboard.php';
            } catch (err) {
                // Pasok pa rin sa dashboard
                window.location.href = 'dashboard.php';
            }
        }
    </script>
</body>
</html>