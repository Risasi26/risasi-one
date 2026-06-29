<?php
// Database configuration for risasi_one
$host = '127.0.0.1';
$db   = 'risasi_ones';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Use a simple friendly message for beginners
    echo 'Database connection failed: ' . htmlspecialchars($e->getMessage());
    exit;
}
