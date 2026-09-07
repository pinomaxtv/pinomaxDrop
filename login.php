<?php
require 'config.php';

if (isset($_SESSION['pd_user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['credential'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM pd_users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['pd_user_id']  = $user['id'];
            $_SESSION['pd_username'] = $user['username'];
            $_SESSION['pd_email']    = $user['email'];
            $_SESSION['pd_avatar']   = $user['avatar'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = 'Maling email o password!';
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
    <title>Sign In - PinoDrop</title>
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

    <div class="fixed top-0 right-0 w-[350px] h-[350px] bg-[#00e5ff0a] rounded-full blur-[100px] pointer-events-none"></div>

    <div class="w-full max-w-md bg-[#0e1118]/90 border border-[#00e5ff33] rounded-3xl p-6 sm:p-8 shadow-[0_0_40px_rgba(0,229,255,0.15)] relative overflow-hidden backdrop-blur-xl">
        <div class="text-center mb-6">
            <div class="w-12 h-12 bg-[#00e5ff] rounded-xl flex items-center justify-center mx-auto shadow-[0_0_20px_rgba(0,229,255,0.4)] mb-3">
                <i class="fa-solid fa-cloud-arrow-down text-black text-xl"></i>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Welcome to <span class="text-[#00e5ff]">PinoDrop</span></h1>
            <p class="text-xs text-gray-500 mt-1">Sign in to manage assets and track download earnings</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-4 p-3 bg-red-500/10 border border-red-500/30 rounded-xl text-red-400 text-xs font-semibold text-center">
                <?= esc($error) ?>
            </div>
        <?php endif; ?>

        <!-- 🔑 GOOGLE ONE-TAP & SIGN-IN BUTTON -->
        <div class="mb-5 flex flex-col items-center">
            <div id="g_id_onload"
                 data-client_id="470197140246-4g1ud4guk5dhi5fhrgcdgli8mp8u9u41.apps.googleusercontent.com"
                 data-context="signin"
                 data-ux_mode="popup"
                 data-callback="handleGoogleLogin"
                 data-auto_prompt="true">
            </div>

            <div class="g_id_signin w-full flex justify-center"
                 data-type="standard"
                 data-shape="pill"
                 data-theme="filled_black"
                 data-text="signin_with"
                 data-size="large"
                 data-logo_alignment="left"
                 data-width="320">
            </div>
        </div>

        <div class="relative flex py-2 items-center mb-5">
            <div class="flex-grow border-t border-white/10"></div>
            <span class="flex-shrink mx-3 text-[10px] text-gray-500 uppercase tracking-widest font-bold">Or with Email</span>
            <div class="flex-grow border-t border-white/10"></div>
        </div>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Email Address</label>
                <input type="email" name="email" required placeholder="creator@email.com" class="w-full bg-[#111318] border border-white/10 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-[#00e5ff] transition-colors">
            </div>

            <div>
                <div class="flex justify-between items-center mb-1">
                    <label class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Password</label>
                    <a href="javascript:void(0);" onclick="alert('Please contact admin to reset password.')" class="text-[10px] text-[#00e5ff] hover:underline">Forgot password?</a>
                </div>
                <input type="password" name="password" required placeholder="••••••••" class="w-full bg-[#111318] border border-white/10 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-[#00e5ff] transition-colors">
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-[#00e5ff] to-[#0072ff] hover:opacity-95 text-black font-black py-3 rounded-xl text-xs uppercase tracking-wider shadow-[0_4px_20px_rgba(0,229,255,0.3)] transition-transform active:scale-[0.98]">
                Sign In to Account
            </button>
        </form>

        <p class="text-center text-xs text-gray-500 mt-6">
            Don't have an account? 
            <a href="register.php" class="text-[#00e5ff] font-bold hover:underline">Register here</a>
        </p>
        <div class="text-center mt-3">
            <a href="index.php" class="text-[11px] text-gray-600 hover:text-gray-400">← Back to Store</a>
        </div>
    </div>

    <!-- JAVASCRIPT HANDLER PARA SA GOOGLE LOGIN -->
    <script>
        async function handleGoogleLogin(response) {
            if (!response.credential) {
                alert("Google Sign-In failed. Please try again.");
                return;
            }

            const formData = new FormData();
            formData.append('credential', response.credential);

            try {
                const res = await fetch('google_callback.php', {
                    method: 'POST',
                    body: formData
                });
                
                const text = await res.text();

                // Kung may 'success' sa response, diretso pasok na sa dashboard!
                if (text.includes('success')) {
                    window.location.href = 'dashboard.php';
                } else {
                    alert(text);
                }
            } catch (err) {
                // Fallback: ire-direct pa rin sa dashboard kung nakapasok naman
                window.location.href = 'dashboard.php';
            }
        }
    </script>
</body>
</html>