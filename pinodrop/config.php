<?php
session_start();

// Database Configuration (TiDB / MySQL Compatible)
$host = '127.0.0.1';
$db   = 'pinodrop';
$user = 'root';
$pass = ''; // Set your database password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    // Uncomment below if TiDB requires SSL
    // PDO::MYSQL_ATTR_SSL_CA => '/path/to/tidb-ca.pem',
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // In production, log this error instead of displaying it
    die("Database connection failed: " . $e->getMessage());
}

// Utility function to sanitize outputs
function esc($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>
