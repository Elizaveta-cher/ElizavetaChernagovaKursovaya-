<?php
session_start();
require_once 'dp.php';

$payment_id = $_GET['id'] ?? '';
$success = false;
$error = '';
$payment = null;

// Получаем информацию о заказе
if ($payment_id) {
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE payment_id = :payment_id");
    $stmt->execute(['payment_id' => $payment_id]);
    $payment = $stmt->fetch();
}

// Обработка подтверждения возврата
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $payment_id) {
    if (!$payment || $payment['status'] !== 'paid') {
        $error = 'Заказ не найден или уже обработан. Возврат невозможен.';
    } else {
        try {
            // Создаём тикет в поддержку
            $ticket_id = 'REF-' . strtoupper(substr(uniqid(), -8));
            
            $stmt = $pdo->prepare("
                INSERT INTO support_tickets (ticket_id, user_email, subject, message, status, priority, created_at)
                VALUES (:ticket_id, :email, :subject, :message, 'open', 'high', NOW())
            ");
            $stmt->execute([
                'ticket_id' => $ticket_id,
                'email' => $payment['user_email'],
                'subject' => 'Запрос на возврат средств',
                'message' => "Заказ: $payment_id\nСумма: " . $payment['amount'] . " ₽\nEmail: " . $payment['user_email'] . "\n\nКлиент запросил возврат через личный кабинет."
            ]);
            
            // Отправляем email админу
            $to = '242185@edu.fa.ru';
            $subject = "🔄 Запрос на возврат: $payment_id";
            $message = "
            <html>
            <body style='font-family:Arial; background:#0f0f0f; color:#fff;'>
                <div style='max-width:600px; margin:0 auto; background:#1a1a2e; padding:30px; border-radius:12px;'>
                    <h2 style='color:#ff4757;'>Запрос на возврат средств</h2>
                    <p><strong>Тикет:</strong> #$ticket_id</p>
                    <p><strong>Заказ:</strong> $payment_id</p>
                    <p><strong>Email клиента:</strong> {$payment['user_email']}</p>
                    <p><strong>Сумма:</strong> {$payment['amount']} ₽</p>
                    <p style='background:#16213e; padding:15px; border-radius:8px; margin:20px 0;'>
                        Клиент оформил возврат через личный кабинет. Необходимо обработать запрос в течение 24 часов.
                    </p>
                </div>
            </body>
            </html>
            ";
            
            $headers = "MIME-Version: 1.0\r\nContent-type: text/html; charset=UTF-8\r\nFrom: support@lizo4kagamestore.ru\r\n";
            @mail($to, $subject, $message, $headers);
            
            // Отправляем email клиенту
            $client_subject = "Заявка на возврат #$ticket_id принята";
            $client_message = "
            <html>
            <body style='font-family:Arial; background:#0f0f0f; color:#fff;'>
                <div style='max-width:600px; margin:0 auto; background:#1a1a2e; padding:30px; border-radius:12px;'>
                    <h2 style='color:#4361ee;'>Ваш запрос на возврат принят</h2>
                    <p>Здравствуйте!</p>
                    <p>Ваш запрос на возврат средств по заказу <strong>$payment_id</strong> принят в обработку.</p>
                    <div style='background:#16213e; padding:20px; border-radius:8px; margin:20px 0;'>
                        <p><strong>Сумма возврата:</strong> {$payment['amount']} ₽</p>
                        <p><strong>Номер тикета:</strong> #$ticket_id</p>
                        <p><strong>Срок обработки:</strong> 24 часа</p>
                    </div>
                    <p style='color:#2ed573;'>💸 Средства вернутся на вашу карту в течение 3-5 рабочих дней после обработки.</p>
                    <p style='margin-top:30px; padding-top:20px; border-top:1px solid #333; color:#666; font-size:12px;'>
                        Lizo4kaGameStore - Служба поддержки
                    </p>
                </div>
            </body>
            </html>
            ";
            
            @mail($payment['user_email'], $client_subject, $client_message, $headers);
            
            $success = true;
            $_SESSION['refund_msg'] = "✅ Запрос на возврат #$ticket_id создан! Средства вернутся в течение 3-5 рабочих дней.";
            
            // РЕДИРЕКТ НА ГЛАВНУЮ ЧЕРЕЗ 3 СЕКУНДЫ
            header('Refresh: 3; url=index.php');
            
        } catch (PDOException $e) {
            $error = 'Ошибка создания запроса: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Возврат средств - Lizo4kaGameStore</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .header { padding: 10px 0; background: #1a1a2e; position: fixed; width: 100%; top: 0; z-index: 1000; }
        .header-main-inner { display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 18px; display: flex; align-items: center; gap: 8px; color: #fff; text-decoration: none; }
        .refund-container { max-width: 600px; margin: 150px auto 80px; background: #16213e; padding: 40px; border-radius: 12px; text-align: center; }
        .refund-icon { font-size: 60px; margin-bottom: 20px; }
        h2 { color: #fff; margin-bottom: 20px; }
        .info-box { background: #1a1a2e; padding: 25px; border-radius: 8px; margin: 25px 0; text-align: left; }
        .info-box h3 { color: #ff4757; margin: 0 0 15px; font-size: 18px; }
        .info-box ul { list-style: none; padding: 0; margin: 0; }
        .info-box li { color: #aaa; margin: 12px 0; padding-left: 30px; position: relative; }
        .info-box li:before { content: "✓"; color: #2ed573; position: absolute; left: 0; font-weight: bold; }
        .success-msg { background: #2ed573; color: #fff; padding: 20px; border-radius: 8px; margin-bottom: 20px; text-align: left; }
        .error-msg { background: #ff4757; color: #fff; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .btn-confirm { 
            display: inline-block; 
            padding: 15px 40px; 
            background: #ff4757; 
            color: #fff; 
            border: none; 
            border-radius: 8px; 
            font-size: 16px; 
            font-weight: 700; 
            cursor: pointer; 
            transition: 0.3s;
            margin: 20px 10px;
        }
        .btn-confirm:hover { background: #e84118; transform: translateY(-2px); }
        .btn { 
            display: inline-block; 
            padding: 15px 30px; 
            border-radius: 8px; 
            text-decoration: none; 
            font-weight: 600; 
            margin: 10px;
            transition: 0.3s;
        }
        .btn-home { background: #4361ee; color: #fff; }
        .btn-home:hover { background: #3a56d4; }
        .btn-support { background: #1a1a2e; color: #fff; border: 2px solid #0f3460; }
        .btn-support:hover { background: #0f3460; }
        .redirect-info { background: #16213e; padding: 15px; border-radius: 8px; margin-top: 20px; color: #aaa; font-size: 14px; }
        .redirect-info strong { color: #2ed573; }
        @media (max-width: 768px) { 
            .refund-container { margin: 130px 20px 60px; padding: 30px 20px; } 
            .btn-confirm, .btn { display: block; width: 100%; margin: 10px 0; }
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
        </div>
    </header>
    
    <div class="refund-container">
        <?php if (isset($_GET['success']) && $success): ?>
            <!-- Успешное создание возврата + РЕДИРЕКТ -->
            <div class="refund-icon">💸</div>
            <h2>Запрос на возврат создан!</h2>
            <?php if (isset($_SESSION['refund_msg'])): ?>
                <div class="success-msg"><?= htmlspecialchars($_SESSION['refund_msg']) ?></div>
                <?php unset($_SESSION['refund_msg']); ?>
            <?php endif; ?>
            <div class="info-box">
                <h3>📧 Что дальше?</h3>
                <ul>
                    <li>Мы отправили подтверждение на ваш email</li>
                    <li>Срок рассмотрения: 24 часа</li>
                    <li>После одобрения средства вернутся в течение 3-5 дней</li>
                </ul>
            </div>
            <div class="redirect-info">
                ⏱️ <strong>Переадресация на главную через 3 секунды...</strong>
            </div>
            <div style="margin-top: 25px;">
                <a href="index.php" class="btn btn-home">🏠 Перейти сейчас</a>
            </div>
            
        <?php elseif ($error): ?>
            <!-- Ошибка -->
            <div class="refund-icon">⚠️</div>
            <h2>Ошибка возврата</h2>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
            <a href="index.php" class="btn btn-home">На главную</a>
            
        <?php elseif ($payment): ?>
            <!-- Форма подтверждения -->
            <?php if ($payment['status'] === 'paid'): ?>
                <div class="refund-icon">🔄</div>
                <h2>Подтверждение возврата</h2>
                <p style="color:#aaa; margin-bottom:20px;">
                    Вы уверены, что хотите оформить возврат для заказа <strong style="color:#4361ee;"><?= htmlspecialchars($payment_id) ?></strong>?
                </p>
                
                <div class="info-box">
                    <h3>📋 Информация о возврате</h3>
                    <ul>
                        <li>Средства вернутся на карту в течение 3-5 рабочих дней</li>
                        <li>Вы получите уведомление на email</li>
                        <li>Тикет будет создан автоматически</li>
                    </ul>
                </div>
                
                <form method="POST" action="refund.php?id=<?= htmlspecialchars($payment_id) ?>">
                    <button type="submit" class="btn-confirm">
                        🔴 Подтвердить возврат
                    </button>
                </form>
                
                <div>
                    <a href="index.php" class="btn btn-home">🏠 На главную</a>
                    <a href="support.php?order=<?= htmlspecialchars($payment_id) ?>" class="btn btn-support">💬 В поддержку</a>
                </div>
            <?php else: ?>
                <div class="refund-icon">⚠️</div>
                <h2>Возврат невозможен</h2>
                <p style="color:#aaa;">Статус заказа: <strong><?= htmlspecialchars($payment['status']) ?></strong></p>
                <p style="color:#aaa; margin-top:10px;">Возврат доступен только для оплаченных заказов.</p>
                <a href="order_status.php" class="btn btn-home">К статусу заказа</a>
            <?php endif; ?>
        <?php else: ?>
            <!-- Заказ не найден -->
            <div class="refund-icon">🔍</div>
            <h2>Заказ не найден</h2>
            <p style="color:#aaa;">Проверьте номер заказа или перейдите на главную.</p>
            <a href="index.php" class="btn btn-home">На главную</a>
        <?php endif; ?>
    </div>
</body>
</html>