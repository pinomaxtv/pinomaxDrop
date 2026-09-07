<?php
require 'config.php';

// Endpoint called silently when user successfully completes the 30s ad countdown.
// Credits uploader 0.15 PHP and logs the download.

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file_id = isset($_GET['file_id']) ? (int)$_GET['file_id'] : 0;
    $downloader_ip = $_SERVER['REMOTE_ADDR'];

    // Prevent duplicate crediting in a short timeframe (basic debounce)
    $stmt = $pdo->prepare("SELECT id FROM pd_downloads_log WHERE file_id = ? AND downloader_ip = ? AND downloaded_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    $stmt->execute([$file_id, $downloader_ip]);
    if ($stmt->fetch()) {
        die(json_encode(['status' => 'error', 'message' => 'Already credited recently from this IP']));
    }

    // Get file owner
    $stmt = $pdo->prepare("SELECT user_id FROM pd_files WHERE id = ?");
    $stmt->execute([$file_id]);
    $file = $stmt->fetch();

    if ($file) {
        $user_id = $file['user_id'];
        
        $pdo->beginTransaction();
        try {
            // Update wallet balance (0.15 PHP commission)
            $updateWallet = $pdo->prepare("UPDATE pd_users SET wallet_balance = wallet_balance + 0.15 WHERE id = ?");
            $updateWallet->execute([$user_id]);

            // Update file total downloads
            $updateDownloads = $pdo->prepare("UPDATE pd_files SET total_downloads = total_downloads + 1 WHERE id = ?");
            $updateDownloads->execute([$file_id]);

            // Log download
            $log = $pdo->prepare("INSERT INTO pd_downloads_log (file_id, downloader_ip) VALUES (?, ?)");
            $log->execute([$file_id, $downloader_ip]);

            $pdo->commit();
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Database error']);
        }
    }
}
?>
