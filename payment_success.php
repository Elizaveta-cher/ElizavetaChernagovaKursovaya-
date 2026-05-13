<?php
session_start();
require_once 'dp.php';
$payment_id = $_GET['payment_id'] ?? '';
$payment = null;
if ($payment_id) {
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE payment_id = :payment_id");
    $stmt->execute(['payment_id' => $payment_id]);
    $payment = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Статус заказа - Lizo4kaGameStore</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        .header { padding: 10px 0; background: #1a1a2e; }
        .header-main-inner { display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 18px; display: flex; align-items: center; gap: 8px; color: #fff; text-decoration: none; }
        .status-container { max-width: 600px; margin: 120px auto 80px; background: #16213e; padding: 40px; border-radius: 12px; }
        .status-title { color: #fff; margin-bottom: 30px; font-size: 28px; text-align: center; }
        .order-details { background: #1a1a2e; padding: 25px; border-radius: 8px; margin-bottom: 25px; }
        .detail-row { display: flex; justify-content: space-between; color: #fff; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #0f3460; }
        .detail-row:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .detail-label { color: #aaa; }
        .detail-value { font-weight: 600; }
        .status-badge { padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 600; }
        .status-paid { background: #2ed573; color: #fff; }
        .status-pending { background: #ffa502; color: #fff; }
        .status-failed { background: #ff4757; color: #fff; }
        .status-refunded { background: #00d2d3; color: #fff; }
        .btn-group { display: flex; gap: 15px; flex-wrap: wrap; }
        .btn { flex: 1; min-width: 200px; padding: 15px; border-radius: 8px; text-align: center; text-decoration: none; font-weight: 700; transition: 0.3s; display: inline-block; }
        .btn-home { background: #4361ee; color: #fff; }
        .btn-home:hover { background: #3a56d4; }
        .btn-refund { background: #ff4757; color: #fff; }
        .btn-refund:hover { background: #e84118; }
        .btn-support { background: #1a1a2e; color: #fff; border: 2px solid #0f3460; }
        .btn-support:hover { background: #0f3460; }
        .refund-info { background: #1a1a2e; padding: 20px; border-radius: 8px; margin: 25px 0; border-left: 4px solid #ff4757; }
        .refund-info h3 { color: #fff; margin-bottom: 10px; }
        .refund-info p { color: #aaa; margin: 5px 0; font-size: 14px; }
        .no-order { text-align: center; color: #aaa; padding: 40px; }
        @media (max-width: 768px) { .status-container { margin: 100px 20px 60px; padding: 30px 20px; } .btn-group { flex-direction: column; } .btn { min-width: 100%; } }
    </style>
</head>
<body>
    <header class="header">
        <div class="container header-main-inner">
            <a href="index.php" class="logo">
                <span class="logo-icon">🎮</span>
                <span class="logo-text">Lizo4ka<span class="accent">GameStore</span></span>
            </a>
        </div>
    </header>
    
    <main class="container">
        <div class="status-container">
            <h1 class="status-title">📋 Статус заказа</h1>
            
            <?php if ($payment): ?>
                <div class="order-details">
                    <div class="detail-row">
                        <span class="detail-label">Номер заказа:</span>
                        <span class="detail-value"><?= htmlspecialchars($payment['payment_id']) ?></span>
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
                        <span class="status-badge status-<?= htmlspecialchars($payment['status']) ?>">
                            <?php
                            $status_labels = [
                                'pending' => 'Ожидает оплаты',
                                'paid' => 'Оплачен',
                                'failed' => 'Отклонён',
                                'refunded' => 'Возврат средств'
                            ];
                            echo $status_labels[$payment['status']] ?? $payment['status'];
                            ?>
                        </span>
                    </div>
                    <?php if ($payment['products']): ?>
                    <div class="detail-row" style="display:block;">
                        <span class="detail-label" style="display:block; margin-bottom:10px;">Товары:</span>
                        <span class="detail-value"><?= htmlspecialchars($payment['products']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($payment['status'] === 'paid'): ?>
                <div class="refund-info">
                    <h3>🔄 Оформить возврат</h3>
                    <p>Если вы хотите оформить возврат средств, нажмите кнопку ниже</p>
                    <p style="color:#ff4757; margin-top:10px;">⚠️ Средства вернутся в течение 3-5 рабочих дней</p>
                </div>
                <?php endif; ?>
                
                <div class="btn-group">
                    <a href="index.php" class="btn btn-home">🏠 На главную</a>
                    <?php if ($payment['status'] === 'paid'): ?>
                        <a href="refund.php?id=<?= $payment['payment_id'] ?>" class="btn btn-refund">🔄 Оформить возврат</a>
                    <?php endif; ?>
                    <a href="support.php?order=<?= $payment['payment_id'] ?>" class="btn btn-support">💬 Написать в поддержку</a>
                </div>
                
            <?php else: ?>
                <div class="no-order">
                    <p style="font-size:48px; margin-bottom:20px;">🔍</p>
                    <p>Заказ не найден</p>
                    <p style="margin-top:10px;">Проверьте номер заказа или перейдите на главную</p>
                    <a href="index.php" class="btn btn-home" style="margin-top:20px; display:inline-block; padding:12px 30px;">На главную</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>