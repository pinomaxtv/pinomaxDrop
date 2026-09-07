<?php
require 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($email) && !empty($password)) {
        $check = $pdo->prepare("SELECT id FROM pd_users WHERE email = ? LIMIT 1");
        $check->execute([$email]);

        if ($check->fetch()) {
            $error = 'Ang email na ito ay may account na!';
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $avatar = "https://ui-avatars.com/api/?name=" . urlencode($username) . "&background=00e5ff&color=000";

            $ins = $pdo->prepare("INSERT INTO pd_users (username, email, password_hash, avatar, wallet_balance) VALUES (?, ?, ?, ?, 0.00)");
            if ($ins->execute([$username, $email, $password_hash, $avatar])) {
                $new_user_id = $pdo->lastInsertId();
                $_SESSION['pd_user_id'] = $new_user_id;
                $_SESSION['pd_username'] = $username;
                $_SESSION['pd_email'] = $email;
                $_SESSION['pd_avatar'] = $avatar;
                header("Location: dashboard.php");
                exit;
            } else {
                $error = 'Hindi ma-save ang account. Subukan muli.';
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - PinoDrop</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-[#050608] text-[#e0e0e0] font-['Plus_Jakarta_Sans'] flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-md bg-[#0e1118]/90 border border-[#00e5ff33] rounded-3xl p-8 shadow-[0_0_40px_rgba(0,229,255,0.15)] relative overflow-hidden backdrop-blur-xl">
        <div class="text-center mb-6">
            <div class="w-12 h-12 bg-[#2ecc71] rounded-xl flex items-center justify-center mx-auto shadow-[0_0_20px_rgba(46,204,113,0.4)] mb-3">
                <i class="fa-solid fa-user-plus text-black text-xl"></i>
            </div>
            <h2 class="text-2xl font-black text-white tracking-tight">Join as a <span class="text-[#2ecc71]">Creator</span></h2>
            <p class="text-xs text-gray-500 mt-1">Upload files and earn ₱0.15 every verified download</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-4 p-3 bg-red-500/10 border border-red-500/30 rounded-xl text-red-400 text-xs font-semibold text-center">
                <?= esc($error) ?>
            </div>
        <?php endif; ?>

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
                Create Creator Account 🚀
            </button>
        </form>

        <p class="text-center text-xs text-gray-500 mt-6">
            Already have an account? 
            <a href="login.php" class="text-[#2ecc71] font-bold hover:underline">Sign In</a>
        </p>
    </div>

</body>
</html>
