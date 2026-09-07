<?php
require 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM pd_users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['pd_user_id'] = $user['id'];
            $_SESSION['pd_username'] = $user['username'];
            $_SESSION['pd_email'] = $user['email'];
            $_SESSION['pd_avatar'] = $user['avatar'];
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PinoDrop</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-[#050608] text-[#e0e0e0] font-['Plus_Jakarta_Sans'] flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-md bg-[#0e1118]/90 border border-[#00e5ff33] rounded-3xl p-8 shadow-[0_0_40px_rgba(0,229,255,0.15)] relative overflow-hidden backdrop-blur-xl">
        <div class="text-center mb-6">
            <div class="w-12 h-12 bg-[#00e5ff] rounded-xl flex items-center justify-center mx-auto shadow-[0_0_20px_rgba(0,229,255,0.4)] mb-3">
                <i class="fa-solid fa-cloud-arrow-down text-black text-xl"></i>
            </div>
            <h2 class="text-2xl font-black text-white tracking-tight">Welcome to <span class="text-[#00e5ff]">PinoDrop</span></h2>
            <p class="text-xs text-gray-500 mt-1">Sign in to manage assets and track download earnings</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-4 p-3 bg-red-500/10 border border-red-500/30 rounded-xl text-red-400 text-xs font-semibold text-center">
                <?= esc($error) ?>
            </div>
        <?php endif; ?>

        <!-- GOOGLE ONE-TAP BUTTON SIMULATOR / OAUTH -->
        <a href="javascript:void(0);" onclick="alert('Google Client ID setup required. Use standard login for now!')" class="w-full flex items-center justify-center gap-3 bg-[#161a23] hover:bg-[#1c2230] border border-white/10 hover:border-[#00e5ff55] py-3 px-4 rounded-xl text-xs font-bold text-white transition-all mb-5 shadow-sm">
            <svg class="w-4 h-4" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/></svg>
            Sign in with Google
        </a>

        <div class="relative flex py-2 items-center mb-5">
            <div class="flex-grow border-t border-white/10"></div>
            <span class="flex-shrink mx-3 text-[10px] text-gray-500 uppercase tracking-widest font-bold">Or with Email</span>
            <div class="flex-grow border-t border-white/10"></div>
        </div>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Email Address</label>
                <input type="email" name="email" required placeholder="creator@pinomax.tv" class="w-full bg-[#111318] border border-white/10 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-[#00e5ff] transition-colors">
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

</body>
</html>
