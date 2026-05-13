<?php
session_start();
require_once 'dp.php';

$payment = null;
$error = '';
$payment_id = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_id = trim($_POST['payment_id'] ?? '');
    
    if (empty($payment_id)) {
        $error = 'Введите номер заказа';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM payments WHERE payment_id = :payment_id");
        $stmt->execute(['payment_id' => $payment_id]);
        $payment = $stmt->fetch();
        
        if (!$payment) {
            $error = 'Заказ не найден. Проверьте номер заказа.';
        }
    }
}

$status_labels = [
    'pending' => 'Ожидает оплаты',
    'paid' => 'Оплачен',
    'failed' => 'Отклонён',
    'refunded' => 'Возврат средств'
];

$status_colors = [
    'pending' => '#ffa502',
    'paid' => '#2ed573',
    'failed' => '#ff4757',
    'refunded' => '#00d2d3'
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Проверка статуса заказа - Lizo4kaGameStore</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        .header { padding: 10px 0; background: #1a1a2e; }
        .header-main-inner { display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 18px; display: flex; align-items: center; gap: 8px; color: #fff; text-decoration: none; }
        .status-container { max-width: 700px; margin: 120px auto 80px; background: #16213e; padding: 40px; border-radius: 12px; }
        .status-title { color: #fff; margin-bottom: 30px; font-size: 28px; text-align: center; }
        .search-form { background: #1a1a2e; padding: 30px; border-radius: 8px; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; color: #fff; margin-bottom: 10px; font-weight: 600; }
        .form-group input { width: 100%; padding: 15px; background: #0f0f0f; border: 2px solid #0f3460; border-radius: 8px; color: #fff; font-size: 16px; box-sizing: border-box; }
        .form-group input:focus { outline: none; border-color: #4361ee; }
        .btn-search { width: 100%; padding: 15px; background: #4361ee; color: #fff; border: none; border-radius: 8px; font-size: 16px; font-weight: 700; cursor: pointer; transition: 0.3s; }
        .btn-search:hover { background: #3a56d4; }
        .error-message { background: #ff4757; color: #fff; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .order-details { background: #1a1a2e; padding: 25px; border-radius: 8px; margin-bottom: 25px; }
        .detail-row { display: flex; justify-content: space-between; color: #fff; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #0f3460; }
        .detail-row:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .detail-label { color: #aaa; }
        .detail-value { font-weight: 600; }
        .status-badge { padding: 8px 20px; border-radius: 20px; font-size: 14px; font-weight: 600; display: inline-block; }
        .btn-group { display: flex; gap: 15px; flex-wrap: wrap; margin-top: 25px; }
        .btn { flex: 1; min-width: 200px; padding: 15px; border-radius: 8px; text-align: center; text-decoration: none; font-weight: 700; transition: 0.3s; display: inline-block; border: none; cursor: pointer; }
        .btn-support { background: #4361ee; color: #fff; }
        .btn-support:hover { background: #3a56d4; }
        .btn-refund { background: #ff4757; color: #fff; }
        .btn-refund:hover { background: #e84118; }
        .btn-home { background: #1a1a2e; color: #fff; border: 2px solid #0f3460; }
        .btn-home:hover { background: #0f3460; }
        .info-box { background: #16213e; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #4361ee; }
        .info-box h3 { color: #fff; margin: 0 0 10px; }
        .info-box p { color: #aaa; margin: 5px 0; }
        .no-order { text-align: center; color: #aaa; padding: 40px; }
        @media (max-width: 768px) { 
            .status-container { margin: 100px 20px 60px; padding: 30px 20px; } 
            .btn-group { flex-direction: column; } 
            .btn { min-width: 100%; } 
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container header-main-inner">
            <a href="index.php" class="logo">
                <span class="logo-icon">🎮</span>
                <span class="logo-text">Lizo4ka<span class="accent">GameStore</span></span>
            </a>
            <nav class="main-nav">
                <a href="index.php" class="nav-link">Главная</a>
                <a href="order_status.php" class="nav-link active">Статус заказа</a>
                <a href="support.php" class="nav-link">Поддержка</a>
            </nav>
        </div>
    </header>
    
    <main class="container">
        <div class="status-container">
            <h1 class="status-title">📊 Проверка статуса заказа</h1>
            
            <?php if ($error): ?>
                <div class="error-message">❌ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if (!$payment): ?>
                <div class="search-form">
                    <form method="POST" action="order_status.php">
                        <div class="form-group">
                            <label>Введите номер заказа</label>
                            <input type="text" name="payment_id" placeholder="GB-XXXXXXXXX" value="<?= htmlspecialchars($payment_id) ?>" required>
                            <small style="color:#aaa; display:block; margin-top:8px;">
                                Номер заказа вы получили в email после оплаты
                            </small>
                        </div>
                        <button type="submit" class="btn-search">🔍 Проверить статус</button>
                    </form>
                </div>
                
                <div class="info-box">
                    <h3>📧 Где найти номер заказа?</h3>
                    <p>• В письме от нас после успешной оплаты</p>
                    <p>• В формате: GB-XXXXXXXXX</p>
                    <p>• Пример: GB-69CF7F7F1401C</p>
                </div>
                
            <?php else: ?>
                <div class="order-details">
                    <div class="detail-row">
                        <span class="detail-label">Номер заказа:</span>
                        <span class="detail-value" style="color:#4361ee; font-size:18px;"><?= htmlspecialchars($payment['payment_id']) ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Дата создания:</span>
                        <span class="detail-value"><?= date('d.m.Y H:i', strtotime($payment['created_at'])) ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value"><?= htmlspecialchars($payment['user_email']) ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Сумма:</span>
                        <span class="detail-value"><?= number_format($payment['amount'], 0, '.', ' ') ?> ₽</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Статус:</span>
                        <span class="status-badge" style="background:<?= $status_colors[$payment['status']] ?>; color:#fff;">
                            <?= $status_labels[$payment['status']] ?? $payment['status'] ?>
                        </span>
                    </div>
                    <?php if ($payment['products']): ?>
                    <div class="detail-row" style="display:block;">
                        <span class="detail-label" style="display:block; margin-bottom:10px;">📦 Товары:</span>
                        <span class="detail-value" style="color:#aaa;"><?= htmlspecialchars($payment['products']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="info-box">
                    <h3> Что дальше?</h3>
                    <?php if ($payment['status'] === 'paid'): ?>
                        <p>✅ Ваш заказ оплачен! Менеджер свяжется с вами в ближайшее время.</p>
                        <p>💬 Если есть вопросы — напишите в поддержку</p>
                        <p>🔄 Если нужно оформить возврат — нажмите кнопку ниже</p>
                    <?php elseif ($payment['status'] === 'pending'): ?>
                        <p>⏳ Заказ ожидает оплаты. Перейдите к оплате или свяжитесь с поддержкой.</p>
                    <?php elseif ($payment['status'] === 'refunded'): ?>
                        <p>💸 Средства возвращены. Проверьте вашу карту (3-5 рабочих дней).</p>
                    <?php endif; ?>
                </div>
                
                <div class="btn-group">
                    <a href="support.php?order=<?= $payment['payment_id'] ?>" class="btn btn-support">
                        💬 Написать в поддержку
                    </a>
                    <?php if ($payment['status'] === 'paid'): ?>
                        <a href="refund.php?id=<?= $payment['payment_id'] ?>" class="btn btn-refund">
                            🔄 Оформить возврат
                        </a>
                    <?php endif; ?>
                    <a href="index.php" class="btn btn-home">
                        🏠 На главную
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>