<?php
require 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file_id = isset($_GET['file_id']) ? (int)$_GET['file_id'] : 0;

    if ($file_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid file ID']);
        exit;
    }

    // 1. Kunin ang totoong visitor IP mula sa Cloudflare (Safe 45 chars para sa IPv6)
    $downloader_ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'));
    $downloader_ip = substr(trim($downloader_ip), 0, 45);

    try {
        // 2. Hanapin kung sino ang uploader ng file
        $stmt = $pdo->prepare("SELECT user_id FROM pd_files WHERE id = ? LIMIT 1");
        $stmt->execute([$file_id]);
        $file = $stmt->fetch();

        if (!$file || empty($file['user_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'File not found']);
            exit;
        }

        $uploader_id = $file['user_id'];

        // ⏱️ 3. STRICT 12-HOUR IP COOLDOWN
        $checkLog = $pdo->prepare("SELECT id FROM pd_downloads_log WHERE file_id = ? AND downloader_ip = ? AND downloaded_at > DATE_SUB(NOW(), INTERVAL 12 HOUR) LIMIT 1");
        $checkLog->execute([$file_id, $downloader_ip]);

        if ($checkLog->fetch()) {
            echo json_encode([
                'status' => 'cooldown', 
                'message' => 'Cooldown active: 1 credit per 12 hours lang sa parehong IP.'
            ]);
            exit;
        }

       $updateWallet = $pdo->prepare("UPDATE pd_users SET wallet_balance = wallet_balance + 0.10 WHERE id = ?");

        // 📈 5. DAGDAG TOTAL DOWNLOADS NG FILE
        $updateDownloads = $pdo->prepare("UPDATE pd_files SET total_downloads = total_downloads + 1 WHERE id = ?");
        $updateDownloads->execute([$file_id]);

        // 📝 6. I-LOG ANG DOWNLOAD RECORD SA BAGONG GAWANG TABLE
        try {
            $log = $pdo->prepare("INSERT INTO pd_downloads_log (file_id, downloader_ip, downloaded_at) VALUES (?, ?, NOW())");
            $log->execute([$file_id, $downloader_ip]);
        } catch (Exception $e) {
            // Ignore log error para tuloy pa rin ang pera
        }

        echo json_encode(['status' => 'success', 'credited' => 0.10, 'uploader' => $uploader_id]);
        exit;

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
        exit;
    }
}
?>