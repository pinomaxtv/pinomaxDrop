<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. KUNIN ANG CREDENTIALS MULA SA RENDER ENVIRONMENT O HARDCODED FALLBACK
$host    = getenv('DB_HOST') ?: (getenv('MYSQLHOST') ?: 'gateway01.ap-southeast-1.prod.aws.tidbcloud.com');
$port    = getenv('DB_PORT') ?: (getenv('MYSQLPORT') ?: 4000);
$db      = 'pinodrop_db'; // 👈 Dito nakatutok ang PinoDrop natin
$user    = getenv('DB_USER') ?: (getenv('MYSQLUSER') ?: '3ZqCQH1sioVp3Gf.root');
$pass    = getenv('DB_PASS') ?: (getenv('MYSQLPASSWORD') ?: 'HmPURKN3mvngVU48');
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
    $conn = $pdo; // Suporta sa $pdo at $conn
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

function esc($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}
?>
