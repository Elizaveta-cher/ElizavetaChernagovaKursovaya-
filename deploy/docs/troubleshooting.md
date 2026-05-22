# Частые проблемы и решения

## 502 Bad Gateway
- `sudo systemctl restart php8.5-fpm`
- Проверьте сокет: `ls -la /var/run/php/php8.5-fpm.sock`

## 403 Forbidden
- `sudo chmod 755 /opt/liza-gamestore`

## Не отображаются картинки
- Проверьте файлы в `/opt/liza-gamestore/uploads/1.png` ... `6.png`
- Обновите пути в БД: `UPDATE products SET image = CONCAT('/uploads/', id, '.png');`

## Не идут письма
- Проверьте пароль приложения Яндекса
- Выполните `test_mail.php` из браузера

## Ошибка SSL (Certbot)
- Убедитесь, что DNS A-запись домена указывает на IP сервера
- Откройте порт 80: `sudo ufw allow 80/tcp`
- Временно отключите `auth_basic` в конфиге Nginx
