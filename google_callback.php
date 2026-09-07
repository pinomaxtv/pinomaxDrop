<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    die(json_encode(['status' => 'error', 'message' => 'Invalid request']));
}

$id_token = $_POST['credential'] ?? '';

if (empty($id_token)) {
    header('Content-Type: application/json');
    die(json_encode(['status' => 'error', 'message' => 'No credential provided']));
}

// 1. I-VERIFY ANG TOKEN SA GOOGLE API
$url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . urlencode($id_token);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

$payload = json_decode($response, true);

if (!isset($payload['sub']) || !isset($payload['email'])) {
    header('Content-Type: application/json');
    die(json_encode(['status' => 'error', 'message' => 'Failed to verify Google Token']));
}

$google_id = $payload['sub'];
$email     = strtolower(trim($payload['email']));
$name      = $payload['name'] ?? 'Google Creator';
$avatar    = $payload['picture'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=00e5ff&color=000';

// 2. TINGNAN KUNG MERON NANG ACCOUNT SA DATABASE
$stmt = $pdo->prepare("SELECT * FROM pd_users WHERE google_id = ? OR email = ? LIMIT 1");
$stmt->execute([$google_id, $email]);
$user = $stmt->fetch();

if ($user) {
    // Existing user: I-update ang google_id at avatar kung wala pa
    $update = $pdo->prepare("UPDATE pd_users SET google_id = ?, avatar = ? WHERE id = ?");
    $update->execute([$google_id, $avatar, $user['id']]);
    $user_id = $user['id'];
    $username = $user['username'];
} else {
    // New user: I-insert sa pd_users table
    $insert = $pdo->prepare("INSERT INTO pd_users (google_id, username, email, avatar, wallet_balance) VALUES (?, ?, ?, ?, 0.00)");
    $insert->execute([$google_id, $name, $email, $avatar]);
    $user_id = $pdo->lastInsertId();
    $username = $name;
}

// 3. I-SET ANG PHP SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['pd_user_id']  = $user_id;
$_SESSION['pd_username'] = $username;
$_SESSION['pd_email']    = $email;
$_SESSION['pd_avatar']   = $avatar;

// KUNG AJAX / FETCH (Kapag galing sa javascript ng desktop)
if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'redirect' => 'dashboard.php']);
    exit;
}

// KUNG BROWSER REDIRECT (Galing sa cellphone Google login)
// Diretso agad sa Dashboard!
header("Location: dashboard.php");
exit;
?>