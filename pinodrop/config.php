<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Configuration (TiDB Cloud para sa PinoDrop)
$host    = 'gateway01.ap-southeast-1.prod.aws.tidbcloud.com';
$port    = 4000;
$db      = 'pinodrop_db'; // 👈 Ang bagong database natin
$user    = '3ZqCQH1sioVp3Gf.root';
$pass    = 'HmPURKN3mvngVU48';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_SSL_CA       => true,
    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $conn = $pdo; // Para suportado pareho ang $pdo at $conn
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Utility function to sanitize outputs
function esc($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}
?>
