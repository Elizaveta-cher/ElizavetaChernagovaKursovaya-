<?php
require_once 'dp.php';
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '/opt/LizaGame/phpmailer/src/PHPMailer.php';
require_once '/opt/LizaGame/phpmailer/src/SMTP.php';
require_once '/opt/LizaGame/phpmailer/src/Exception.php';

$success = '';
$error = '';
$prefilled_order_id = $_GET['order'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $order_id = trim($_POST['order_id'] ?? '');
    
    if (empty($email) || empty($subject) || empty($message)) {
        $error = 'Заполните обязательные поля';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Введите корректный email';
    } else {
        try {
            $ticket_id = 'TKT-' . strtoupper(substr(uniqid(), -8));
            $stmt = $pdo->prepare("INSERT INTO support_tickets (ticket_id, user_email, user_name, subject, message, status, priority, created_at) VALUES (:ticket_id, :email, :name, :subject, :message, 'open', 'normal', NOW())");
            $stmt->execute([
                'ticket_id' => $ticket_id,
                'email' => $email,
                'name' => $name,
                'subject' => $subject,
                'message' => $message
            ]);
            
            // НАСТРОЙКИ SMTP (замените пароль на ваш)
            $smtp_username = 'sh1tikovm@yandex.ru';
            $smtp_password = 'wayvhktatteyshmf'; // <<<--- ВСТАВЬТЕ ПАРОЛЬ
            
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'smtp.yandex.ru';
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtp_username;
            $mail->Password   = $smtp_password;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            $mail->CharSet    = 'UTF-8';
            $mail->setFrom($smtp_username, 'Lizo4kaGameStore Support');
            
            // Письмо администратору
            $mail->addAddress('242185@edu.fa.ru', 'Admin');
            $mail->addReplyTo($email, $name);
            $mail->isHTML(true);
            $mail->Subject = "🎮 Новый тикет: $subject";
            $mail->Body = "<html><body><h2>Новый запрос</h2><p><strong>Тикет:</strong> #$ticket_id</p><p><strong>От:</strong> $name ($email)</p><p><strong>Тема:</strong> $subject</p><div>" . nl2br(htmlspecialchars($message)) . "</div></body></html>";
            $mail->send();
            
            // Письмо клиенту
            $mail->clearAddresses();
            $mail->addAddress($email, $name);
            $mail->Subject = "✅ Ваш запрос #$ticket_id принят";
            $mail->Body = "<html><body><h2>Запрос принят</h2><p>Здравствуйте, $name!<br>Мы ответим в течение 24 часов.</p><p>Ваш тикет: #$ticket_id</p><div>" . nl2br(htmlspecialchars($message)) . "</div><p>С уважением,<br>Lizo4kaGameStore</p></body></html>";
            $mail->send();
            
            $success = "✅ Тикет #$ticket_id создан! Уведомление отправлено на $email.";
            $_SESSION['support_success'] = $success;
            header('Location: support.php?success=1');
            exit;
        } catch (Exception $e) {
            $error = "Тикет #$ticket_id создан, но письмо не отправлено. Администратор свяжется с вами.";
            error_log("SMTP Error: " . $mail->ErrorInfo);
        } catch (PDOException $e) {
            $error = 'Ошибка базы данных: ' . $e->getMessage();
        }
    }
}

if (isset($_GET['success']) && isset($_SESSION['support_success'])) {
    $success = $_SESSION['support_success'];
    unset($_SESSION['support_success']);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Поддержка - Lizo4kaGameStore</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        .support-container {
            max-width: 700px;
            margin: 120px auto 80px;
            background: #16213e;
            padding: 40px;
            border-radius: 12px;
        }
        .support-title {
            color: #fff;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .support-subtitle {
            color: #aaa;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            color: #fff;
            margin-bottom: 8px;
            font-weight: 600;
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            background: #1a1a2e;
            border: 1px solid #0f3460;
            border-radius: 8px;
            color: #fff;
            font-family: inherit;
            box-sizing: border-box;
        }
        .btn-submit {
            width: 100%;
            padding: 15px;
            background: #4361ee;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-submit:hover {
            background: #3a56d4;
        }
        .success-message {
            background: #2ed573;
            color: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .error-message {
            background: #ff4757;
            color: #fff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .contact-info {
            background: #1a1a2e;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }
        .contact-item {
            color: #aaa;
            margin: 10px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        @media (max-width: 768px) {
            .support-container {
                margin: 100px 20px 60px;
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-top">
            <div class="container header-top-inner">
                <div class="contact-info">
                    <span class="contact-item">📧 242185@edu.fa.ru</span>
                    <span class="contact-item">📞 +7 (999) 000-00-00</span>
                </div>
                <div class="social-links">
                    <a href="#" class="social-link">VK</a>
                    <a href="#" class="social-link">TG</a>
                    <a href="#" class="social-link">DS</a>
                </div>
            </div>
        </div>
        <div class="header-main">
            <div class="container header-main-inner">
                <a href="index.php" class="logo">
                    <span class="logo-icon">🎮</span>
                    <span class="logo-text">Lizo4ka<span class="accent">GameStore</span></span>
                </a>
                <button class="mobile-menu-btn">☰</button>
                <nav class="main-nav">
                    <a href="index.php" class="nav-link">Главная</a>
                    <a href="index.php#services" class="nav-link">Услуги</a>
                    <a href="order_status.php" class="nav-link">Статус заказа</a>
                    <a href="support.php" class="nav-link active">Поддержка</a>
                </nav>
                <div class="header-actions">
                    <a href="cart.php" class="cart-btn">
                        🛒 Корзина <span class="cart-count">0</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="support-container">
            <h1 class="support-title">📞 Служба поддержки</h1>
            <p class="support-subtitle">Заполните форму – мы ответим в течение 24 часов</p>
            
            <?php if ($success): ?>
                <div class="success-message"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="error-message">❌ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Ваше имя</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Номер заказа (если есть)</label>
                    <input type="text" name="order_id" value="<?= htmlspecialchars($_POST['order_id'] ?? $prefilled_order_id) ?>">
                </div>
                <div class="form-group">
                    <label>Тема *</label>
                    <select name="subject" required>
                        <option value="">Выберите тему</option>
                        <option value="Вопрос по заказу">Вопрос по заказу</option>
                        <option value="Возврат средств">Возврат средств</option>
                        <option value="Техническая проблема">Техническая проблема</option>
                        <option value="Другой вопрос">Другой вопрос</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Сообщение *</label>
                    <textarea name="message" required rows="6"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                </div>
                <button type="submit" class="btn-submit">Отправить запрос</button>
            </form>
            
            <div class="contact-info">
                <h3>📧 Другие способы связи</h3>
                <div class="contact-item">📧 Email: 242185@edu.fa.ru</div>
                <div class="contact-item">💬 Telegram: @lizo4ka_support</div>
                <div class="contact-item">⏰ Время ответа: 24 часа</div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-column">
                    <div class="logo">
                        <span class="logo-icon">🎮</span>
                        <span class="logo-text">Lizo4ka<span class="accent">GameStore</span></span>
                    </div>
                    <p class="footer-desc">Профессиональный сервис игровых услуг с 2020 года</p>
                </div>
                <div class="footer-column">
                    <h4 class="footer-title">Контакты</h4>
                    <ul class="footer-contacts">
                        <li>📧 242185@edu.fa.ru</li>
                        <li>💬 Telegram: @lizo4ka_support</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 Lizo4kaGameStore. Все права защищены.</p>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            fetch('cart.php?get_count=1')
                .then(r => r.text())
                .then(c => { const el = document.querySelector('.cart-count'); if (el) el.textContent = c; })
                .catch(console.error);
        });
    </script>
</body>
</html>