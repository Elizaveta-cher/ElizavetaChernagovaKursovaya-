<?php
// Простая HTTP-аутентификация для админ-панели
$valid_user = 'liza_admin';
$valid_password = '123456qQ'; // ЗАМЕНИТЕ НА РЕАЛЬНЫЙ ПАРОЛЬ

if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER'] != $valid_user || $_SERVER['PHP_AUTH_PW'] != $valid_password) {
    header('WWW-Authenticate: Basic realm="Admin Area"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Доступ запрещён';
    exit;
}
?>
