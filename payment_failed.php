<?php
session_start();
$message = $_SESSION['payment_failed']['message'] ?? 'Ошибка оплаты';
unset($_SESSION['payment_failed']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ошибка оплаты - GameBoost</title>
<link rel="stylesheet" href="style.css">
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
<style>
.header { padding: 10px 0; background: #1a1a2e; }
.header-main-inner { display: flex; justify-content: space-between; align-items: center; }
.logo { font-size: 18px; display: flex; align-items: center; gap: 8px; color: #fff; text-decoration: none; }
.failed-container { max-width: 500px; margin: 80px auto; text-align: center; background: #16213e; padding: 40px; border-radius: 12px; }
.failed-icon { font-size: 80px; margin-bottom: 20px; }
.failed-title { color: #ff4757; font-size: 28px; margin-bottom: 15px; }
.failed-text { color: #fff; margin-bottom: 30px; line-height: 1.6; }
.error-code { background: #1a1a2e; padding: 15px; border-radius: 8px; margin-bottom: 30px; color: #ff4757; }
.btn-try { display: inline-block; padding: 15px 40px; background: #4361ee; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 700; transition: 0.3s; margin-right: 10px; }
.btn-try:hover { background: #3a56d4; }
.btn-home { display: inline-block; padding: 15px 40px; background: #1a1a2e; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 700; transition: 0.3s; border: 1px solid #0f3460; }
.btn-home:hover { background: #0f3460; }
</style>
</head>
<body>
<header class="header">
<div class="container header-main-inner">
<a href="index.php" class="logo">
<span class="logo-icon">🎮</span>
<span class="logo-text">Game<span class="accent">Boost</span></span>
</a>
</div>
</header>

<main class="container">
<div class="failed-container">
<div class="failed-icon">❌</div>
<h1 class="failed-title">Ошибка оплаты</h1>
<p class="failed-text">К сожалению, платеж не был обработан.</p>

<div class="error-code">
📝 Причина: <?= htmlspecialchars($message) ?>
</div>

<div>
<a href="checkout.php" class="btn-try">Попробовать снова</a>
<a href="index.php" class="btn-home">На главную</a>
</div>
</div>
</main>
</body>
</html>