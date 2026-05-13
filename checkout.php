<?php
session_start();
require_once 'dp.php';

if (empty($_SESSION['cart'])) {
    header('Location: index.php');
    exit;
}

$total = 0;
$products_list = [];
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'];
    $products_list[] = $item['title'];
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $payment_method = $_POST['payment'] ?? 'card';
    
    if (empty($email)) {
        $error = 'Введите email';
    } else {
        try {
            // Генерируем уникальный ID заказа
            $payment_id = 'GB-' . strtoupper(uniqid());
            
            // Сохраняем заказ в БД со статусом 'pending'
            $stmt = $pdo->prepare("
                INSERT INTO payments (payment_id, user_email, user_contact, amount, products, status, payment_method, ip_address, created_at)
                VALUES (:payment_id, :email, :contact, :amount, :products, 'pending', :method, :ip, NOW())
            ");
            
            $stmt->execute([
                'payment_id' => $payment_id,
                'email' => $email,
                'contact' => $contact,
                'amount' => $total,
                'products' => implode(', ', $products_list),
                'method' => $payment_method,
                'ip' => $_SERVER['REMOTE_ADDR']
            ]);
            
            // Сохраняем данные в сессию для страницы оплаты
            $_SESSION['pending_payment'] = [
                'payment_id' => $payment_id,
                'email' => $email,
                'contact' => $contact,
                'amount' => $total,
                'products' => $products_list,
                'method' => $payment_method,
                'cart' => $_SESSION['cart']
            ];
            
            // Перенаправляем на страницу имитации оплаты
            header('Location: payment_gateway.php');
            exit;
            
        } catch (PDOException $e) {
            $error = 'Ошибка создания заказа: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Оформление заказа - GameBoost</title>
<link rel="stylesheet" href="style.css">
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
<style>
.header { padding: 10px 0; background: #1a1a2e; }
.header-main-inner { display: flex; justify-content: space-between; align-items: center; }
.logo { font-size: 18px; display: flex; align-items: center; gap: 8px; color: #fff; text-decoration: none; }
.checkout-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-top: 30px; }
.checkout-form, .order-summary { background: #16213e; padding: 25px; border-radius: 12px; }
.form-group { margin-bottom: 20px; }
.form-group label { display: block; margin-bottom: 8px; color: #fff; font-weight: 600; }
.form-group input, .form-group textarea { width: 100%; padding: 12px; border: 1px solid #0f3460; border-radius: 8px; background: #1a1a2e; color: #fff; font-size: 14px; }
.payment-methods { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; margin: 20px 0; }
.payment-method { display: flex; align-items: center; gap: 10px; padding: 15px; background: #1a1a2e; border: 2px solid #0f3460; border-radius: 8px; cursor: pointer; transition: 0.3s; }
.payment-method:hover { border-color: #4361ee; }
.payment-method input[type="radio"] { accent-color: #4361ee; }
.payment-method span { color: #fff; font-size: 14px; }
.btn-pay { width: 100%; padding: 15px; background: #4361ee; color: #fff; border: none; border-radius: 8px; font-size: 16px; font-weight: 700; cursor: pointer; transition: 0.3s; margin-top: 20px; }
.btn-pay:hover { background: #3a56d4; }
.order-item { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #0f3460; color: #fff; }
.order-total { display: flex; justify-content: space-between; padding: 20px 0 0; font-size: 18px; font-weight: 700; color: #4361ee; }
.error-message { background: #ff4757; color: #fff; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
@media (max-width: 768px) { .checkout-grid { grid-template-columns: 1fr; } }
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
<h1 class="section-title" style="color:#fff; text-align:center;">Оформление заказа</h1>

<?php if ($error): ?>
<div class="error-message">❌ <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="checkout-grid">
<div class="checkout-form">
<h3 style="color:#fff;">Контактные данные</h3>
<form method="POST" action="checkout.php">
<div class="form-group">
<label>Email *</label>
<input type="email" name="email" required placeholder="ваш@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
</div>
<div class="form-group">
<label>Telegram / Discord</label>
<input type="text" name="contact" placeholder="@username" value="<?= htmlspecialchars($_POST['contact'] ?? '') ?>">
</div>
<div class="form-group">
<label>Комментарий к заказу</label>
<textarea name="comment" rows="4" placeholder="Дополнительная информация"></textarea>
</div>

<h3 style="color:#fff;">Способ оплаты</h3>
<div class="payment-methods">
<label class="payment-method">
<input type="radio" name="payment" value="card" checked>
<span>💳 Карта</span>
</label>
<label class="payment-method">
<input type="radio" name="payment" value="yoomoney">
<span>💰 ЮMoney</span>
</label>
<label class="payment-method">
<input type="radio" name="payment" value="qiwi">
<span>📱 Qiwi</span>
</label>
</div>

<button type="submit" class="btn-pay">
Перейти к оплате <?= number_format($total, 0, '.', ' ') ?> ₽
</button>
</form>
</div>

<div class="order-summary">
<h3 style="color:#fff;">Ваш заказ</h3>
<div class="order-items">
<?php foreach ($_SESSION['cart'] as $item): ?>
<div class="order-item">
<span><?= htmlspecialchars($item['title']) ?></span>
<span><?= number_format($item['price'], 0, '.', ' ') ?> ₽</span>
</div>
<?php endforeach; ?>
</div>
<div class="order-total">
<span>Итого:</span>
<span><?= number_format($total, 0, '.', ' ') ?> ₽</span>
</div>
</div>
</div>
</main>
</body>
</html>