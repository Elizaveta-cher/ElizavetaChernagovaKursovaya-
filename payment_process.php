<?php
session_start();
require_once 'dp.php';

// Алгоритм Луна для проверки номера карты
function luhn_check($card_number) {
    $number = preg_replace('/\D/', '', $card_number);
    $sum = 0;
    $alt = false;
    for ($i = strlen($number) - 1; $i >= 0; $i--) {
        $n = (int)$number[$i];
        if ($alt) {
            $n *= 2;
            if ($n > 9) $n -= 9;
        }
        $sum += $n;
        $alt = !$alt;
    }
    return ($sum % 10 === 0);
}

// Маскирование карты
function mask_card($card_number) {
    $clean = preg_replace('/\D/', '', $card_number);
    $last4 = substr($clean, -4);
    return '**** **** **** ' . $last4;
}

// Эмуляция банка
function mock_bank_request($card_number, $amount) {
    $clean = preg_replace('/\D/', '', $card_number);
    if (strlen($clean) < 13) {
        return ['status' => 'declined', 'code' => 'INVALID_CARD', 'message' => 'Неверный номер карты'];
    }
    if (substr($clean, 0, 1) === '5') {
        return ['status' => 'declined', 'code' => 'INSUFFICIENT_FUNDS', 'message' => 'Недостаточно средств'];
    }
    if (substr($clean, 0, 2) === '30') {
        return ['status' => 'declined', 'code' => 'FRAUD_SUSPECTED', 'message' => 'Подозрительная операция'];
    }
    return ['status' => 'approved', 'code' => '00', 'message' => 'Одобрено'];
}

// Отправка email клиенту
function send_order_email($payment_id, $email, $amount, $products) {
    $to = $email;
    $subject = "✅ Заказ #$payment_id подтверждён - Lizo4kaGameStore";
    
    $message = '
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; background: #0f0f0f; color: #fff; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 0 auto; background: #1a1a2e; padding: 30px; border-radius: 12px; }
            .header { background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%); padding: 30px; border-radius: 8px; margin-bottom: 30px; text-align: center; }
            .header h1 { margin: 0; color: #fff; font-size: 28px; }
            .header p { margin: 10px 0 0; color: #e0e0ff; }
            .order-box { background: #16213e; padding: 25px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #4361ee; }
            .order-id { font-size: 24px; font-weight: bold; color: #4361ee; margin-bottom: 15px; }
            .info-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #0f3460; }
            .info-row:last-child { border-bottom: none; }
            .label { color: #aaa; }
            .value { color: #fff; font-weight: 600; }
            .products { background: #0f0f0f; padding: 20px; border-radius: 8px; margin: 20px 0; }
            .product-item { padding: 10px 0; border-bottom: 1px solid #333; color: #fff; }
            .product-item:last-child { border-bottom: none; }
            .total { font-size: 20px; font-weight: bold; color: #4361ee; margin-top: 15px; text-align: right; }
            .actions { margin: 30px 0; text-align: center; }
            .btn { display: inline-block; padding: 15px 30px; background: #4361ee; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; margin: 5px; }
            .btn-refund { background: #ff4757; }
            .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #0f3460; text-align: center; color: #666; font-size: 12px; }
            .important { background: #ff4757; color: #fff; padding: 15px; border-radius: 8px; margin: 20px 0; text-align: center; font-weight: 600; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🎮 Lizo4kaGameStore</h1>
                <p>Спасибо за ваш заказ!</p>
            </div>
            
            <div class="order-box">
                <div class="order-id">📋 Заказ #' . htmlspecialchars($payment_id) . '</div>
                <div class="info-row">
                    <span class="label">Статус:</span>
                    <span class="value" style="color: #2ed573;">✅ Оплачен</span>
                </div>
                <div class="info-row">
                    <span class="label">Дата:</span>
                    <span class="value">' . date('d.m.Y H:i') . '</span>
                </div>
                <div class="info-row">
                    <span class="label">Сумма:</span>
                    <span class="value">' . number_format($amount, 0, '.', ' ') . ' ₽</span>
                </div>
            </div>
            
            <div class="products">
                <h3 style="margin-top:0; color:#fff;">📦 Товары:</h3>
                ' . implode('', array_map(function($p) {
                    return '<div class="product-item">' . htmlspecialchars($p) . '</div>';
                }, explode(', ', $products))) . '
                <div class="total">Итого: ' . number_format($amount, 0, '.', ' ') . ' ₽</div>
            </div>
            
            <div class="important">
                ⚠️ Сохраните номер заказа: <strong>' . htmlspecialchars($payment_id) . '</strong><br>
                Он понадобится для отслеживания статуса и оформления возврата
            </div>
            
            <div class="actions">
                <a href="' . SITE_URL . '/order_status.php" class="btn">📊 Проверить статус</a>
                <a href="' . SITE_URL . '/support.php" class="btn btn-refund">🔄 Оформить возврат</a>
            </div>
            
            <div style="background:#16213e; padding:20px; border-radius:8px; margin:20px 0;">
                <h3 style="margin-top:0; color:#fff;">📞 Нужна помощь?</h3>
                <p style="color:#aaa; margin:10px 0;">Наша поддержка работает 24/7</p>
                <p style="margin:10px 0;">📧 Email: 242185@edu.fa.ru</p>
                <p style="margin:10px 0;">💬 Telegram: @lizo4ka_support</p>
            </div>
            
            <div class="footer">
                <p>Это автоматическое письмо от Lizo4kaGameStore</p>
                <p>Если у вас есть вопросы, ответьте на это письмо или свяжитесь с поддержкой</p>
            </div>
        </div>
    </body>
    </html>';
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Lizo4kaGameStore <noreply@lizo4kagamestore.ru>\r\n";
    $headers .= "Reply-To: 242185@edu.fa.ru\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    return @mail($to, $subject, $message, $headers);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!isset($_SESSION['pending_payment'])) {
    header('Location: index.php');
    exit;
}

$payment_data = $_SESSION['pending_payment'];
$payment_id = $_POST['payment_id'] ?? '';
$amount = $_POST['amount'] ?? 0;
$card_number = $_POST['card_number'] ?? '';
$card_expiry = $_POST['card_expiry'] ?? '';
$card_cvv = $_POST['card_cvv'] ?? '';
$card_holder = $_POST['card_holder'] ?? '';
$error = '';

if ($payment_id !== $payment_data['payment_id']) {
    $error = 'Ошибка идентификатора платежа';
} elseif (empty($card_number) || !luhn_check($card_number)) {
    $error = 'Неверный номер карты';
} elseif (empty($card_expiry)) {
    $error = 'Укажите срок действия карты';
} elseif (empty($card_cvv) || strlen($card_cvv) !== 3) {
    $error = 'Неверный CVV код';
} elseif (empty($card_holder)) {
    $error = 'Укажите владельца карты';
}

if (!empty($error)) {
    $_SESSION['payment_error'] = $error;
    header('Location: payment_gateway.php');
    exit;
}

try {
    $bank_response = mock_bank_request($card_number, $amount);
    $status = $bank_response['status'] === 'approved' ? 'paid' : 'failed';
    $card_mask = mask_card($card_number);
    
    if ($status === 'paid') {
        $stmt = $pdo->prepare("
            UPDATE payments
            SET status = :status,
                card_mask = :card_mask,
                paid_at = NOW(),
                updated_at = NOW()
            WHERE payment_id = :payment_id
        ");
    } else {
        $stmt = $pdo->prepare("
            UPDATE payments
            SET status = :status,
                card_mask = :card_mask,
                updated_at = NOW()
            WHERE payment_id = :payment_id
        ");
    }
    
    $stmt->execute([
        'status' => $status,
        'card_mask' => $card_mask,
        'payment_id' => $payment_id
    ]);
    
    // ОТПРАВКА EMAIL КЛИЕНТУ
    if ($status === 'paid') {
        send_order_email(
            $payment_id,
            $payment_data['email'],
            $amount,
            implode(', ', $payment_data['products'])
        );
    }
    
    // Логирование
    $log_entry = date('Y-m-d H:i:s') . " | Payment: $payment_id | Status: $status | Amount: $amount | Card: $card_mask\n";
    file_put_contents('payment_logs.txt', $log_entry, FILE_APPEND);
    
    unset($_SESSION['pending_payment']);
    unset($_SESSION['cart']);
    
    if ($status === 'paid') {
        header('Location: payment_success.php?payment_id=' . $payment_id);
    } else {
        $_SESSION['payment_failed'] = [
            'payment_id' => $payment_id,
            'message' => $bank_response['message']
        ];
        header('Location: payment_failed.php');
    }
    exit;
    
} catch (PDOException $e) {
    $_SESSION['payment_error'] = 'Ошибка базы данных: ' . $e->getMessage();
    header('Location: payment_gateway.php');
    exit;
}
?>