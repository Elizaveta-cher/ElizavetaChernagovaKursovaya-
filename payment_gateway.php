<?php
session_start();
require_once 'dp.php';

if (!isset($_SESSION['pending_payment'])) {
    header('Location: index.php');
    exit;
}

$payment = $_SESSION['pending_payment'];
$error = $_SESSION['payment_error'] ?? '';
unset($_SESSION['payment_error']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Оплата заказа - GameBoost</title>
<link rel="stylesheet" href="style.css">
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
<style>
.header { padding: 10px 0; background: #1a1a2e; }
.header-main-inner { display: flex; justify-content: space-between; align-items: center; }
.logo { font-size: 18px; display: flex; align-items: center; gap: 8px; color: #fff; text-decoration: none; }
.payment-container { max-width: 500px; margin: 50px auto; background: #16213e; padding: 30px; border-radius: 12px; }
.payment-title { color: #fff; text-align: center; margin-bottom: 30px; }
.order-info { background: #1a1a2e; padding: 20px; border-radius: 8px; margin-bottom: 25px; }
.order-info-row { display: flex; justify-content: space-between; color: #fff; margin-bottom: 10px; }
.order-total { font-size: 20px; font-weight: 700; color: #4361ee; border-top: 1px solid #0f3460; padding-top: 15px; }
.form-group { margin-bottom: 20px; }
.form-group label { display: block; color: #fff; margin-bottom: 8px; font-weight: 600; font-size: 14px; }
.form-group input { width: 100%; padding: 14px; background: #1a1a2e; border: 1px solid #0f3460; border-radius: 8px; color: #fff; font-size: 16px; box-sizing: border-box; }
.form-group input:focus { outline: none; border-color: #4361ee; }
.card-row { display: grid; grid-template-columns: 2fr 1fr; gap: 15px; }
.btn-pay { width: 100%; padding: 16px; background: #4361ee; color: #fff; border: none; border-radius: 8px; font-size: 16px; font-weight: 700; cursor: pointer; transition: 0.3s; margin-top: 10px; }
.btn-pay:hover { background: #3a56d4; }
.secure-badge { text-align: center; color: #2ed573; font-size: 13px; margin-top: 15px; display: flex; align-items: center; justify-content: center; gap: 5px; }
.error-message { background: #ff4757; color: #fff; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
.card-icons { display: flex; gap: 10px; margin-bottom: 20px; justify-content: center; }
.card-icon { background: #1a1a2e; padding: 8px 15px; border-radius: 6px; color: #fff; font-size: 12px; border: 1px solid #0f3460; }
@media (max-width: 600px) { .card-row { grid-template-columns: 1fr; } }
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
<div class="payment-container">
<h1 class="payment-title">💳 Оплата заказа</h1>

<div class="card-icons">
<span class="card-icon">VISA</span>
<span class="card-icon">MasterCard</span>
<span class="card-icon">МИР</span>
</div>

<div class="order-info">
<div class="order-info-row">
<span>Заказ №</span>
<span><?= htmlspecialchars($payment['payment_id']) ?></span>
</div>
<div class="order-info-row">
<span>Сумма:</span>
<span><?= number_format($payment['amount'], 0, '.', ' ') ?> ₽</span>
</div>
</div>

<?php if ($error): ?>
<div class="error-message">❌ <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" action="payment_process.php">
<input type="hidden" name="payment_id" value="<?= htmlspecialchars($payment['payment_id']) ?>">
<input type="hidden" name="amount" value="<?= $payment['amount'] ?>">

<div class="form-group">
<label>Номер карты</label>
<input type="text" name="card_number" placeholder="0000 0000 0000 0000" maxlength="19" required id="cardNumber">
</div>

<div class="card-row">
<div class="form-group">
<label>Срок действия</label>
<input type="text" name="card_expiry" placeholder="ММ/ГГ" maxlength="5" required id="cardExpiry">
</div>
<div class="form-group">
<label>CVV</label>
<input type="text" name="card_cvv" placeholder="123" maxlength="3" required id="cardCvv">
</div>
</div>

<div class="form-group">
<label>Владелец карты</label>
<input type="text" name="card_holder" placeholder="IVAN IVANOV" required>
</div>

<button type="submit" class="btn-pay">
Оплатить <?= number_format($payment['amount'], 0, '.', ' ') ?> ₽
</button>

<div class="secure-badge">
🔒 Защищено SSL-шифрованием. Данные карты не хранятся.
</div>
</form>
</div>
</main>

<script>
document.getElementById('cardNumber').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    value = value.replace(/(.{4})/g, '$1 ').trim();
    e.target.value = value;
});

document.getElementById('cardExpiry').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
    }
    e.target.value = value;
});

document.getElementById('cardCvv').addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/\D/g, '');
});
</script>
</body>
</html>