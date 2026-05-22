<?php

// FIX: Усиление безопасности сессий
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_secure', 1);    
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_only_cookies', 1);

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$db   = 'liza_store';
$user = 'liza_admin';
$pass = '123456qQ';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$opt = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $opt);
} catch (\PDOException $e) {
    die("Ошибка подключения к базе: " . $e->getMessage());
}

// Настройки внутренней платежной системы
define('SITE_NAME', 'GameBoost');
define('SITE_URL', 'http://159.194.218.137:8081');
?>
