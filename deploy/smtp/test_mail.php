<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.yandex.ru';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'sh1tikovm@yandex.ru';
    $mail->Password   = 'vsgyqbsqqongedsa';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;
    $mail->CharSet    = 'UTF-8';
    $mail->setFrom('sh1tikovm@yandex.ru', 'GameBoost Support');
    $mail->addAddress('242185@edu.fa.ru');
    $mail->isHTML(true);
    $mail->Subject = 'Тест SMTP от сервера';
    $mail->Body    = '<html><body><p>Это тестовое письмо. Если вы его видите, SMTP работает!</p><p>Текст на русском языке.</p></body></html>';
    $mail->send();
    echo '✅ Письмо успешно отправлено!';
} catch (Exception $e) {
    echo "❌ Ошибка: {$mail->ErrorInfo}";
}
?>
