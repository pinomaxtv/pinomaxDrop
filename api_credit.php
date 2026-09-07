<?php
require 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file_id = isset($_GET['file_id']) ? (int)$_GET['file_id'] : 0;

    if ($file_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid file ID']);
        exit;
    }

    try {
        // 1. Hanapin kung sino ang uploader ng file
        $stmt = $pdo->prepare("SELECT user_id FROM pd_files WHERE id = ? LIMIT 1");
        $stmt->execute([$file_id]);
        $file = $stmt->fetch();

        if ($file && !empty($file['user_id'])) {
            $uploader_id = $file['user_id'];

            // 💰 2. DIRETSO DAGDAG AGAD NG ₱0.15 SA WALLET (WALANG HARANG!)
            $updateWallet = $pdo->prepare("UPDATE pd_users SET wallet_balance = wallet_balance + 0.15 WHERE id = ?");
            $updateWallet->execute([$uploader_id]);

            // 📝 3. Subukang i-log (kung mag-error man ang log table, HINDI maaapektuhan ang pera)
            try {
                $downloader_ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
                $downloader_ip = substr($downloader_ip, 0, 45);
                $log = $pdo->prepare("INSERT INTO pd_downloads_log (file_id, downloader_ip) VALUES (?, ?)");
                $log->execute([$file_id, $downloader_ip]);
            } catch (Exception $e) {
                // Safe: Tuloy pa rin ang pera kahit mag-fail ang logs
            }

            echo json_encode(['status' => 'success', 'credited' => 0.15]);
            exit;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'File or Uploader not found']);
            exit;
        }

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
}
?>