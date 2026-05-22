#!/bin/bash
set -e

echo "=== Установка сайта Lizo4kaGameStore ==="

# Установка пакетов
sudo apt update
sudo apt install -y nginx mysql-server php8.5-fpm php8.5-mysql php8.5-curl php8.5-mbstring php8.5-xml php8.5-zip php8.5-gd php8.5-intl composer git curl wget unzip

# Клонирование (если не склонировано)
if [ ! -d "/opt/liza-gamestore" ]; then
    cd /opt
    sudo git clone https://github.com/Elizaveta-cher/ElizavetaChernagovaKursovaya-.git liza-gamestore
fi

cd /opt/liza-gamestore
composer require phpmailer/phpmailer

# Права
sudo chown -R www-data:www-data .
sudo find . -type d -exec chmod 755 {} \;
sudo find . -type f -exec chmod 644 {} \;
sudo chmod 775 uploads

# Создание БД (требуется ввод пароля root MySQL)
echo "Введите пароль root MySQL:"
read -s MYSQL_ROOT_PASS
mysql -u root -p$MYSQL_ROOT_PASS <<MYSQL_SCRIPT
CREATE DATABASE IF NOT EXISTS liza_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'liza_admin'@'localhost' IDENTIFIED BY 'StrongPasswordHere123';
GRANT ALL PRIVILEGES ON liza_store.* TO 'liza_admin'@'localhost';
FLUSH PRIVILEGES;
MYSQL_SCRIPT

# Импорт схемы
mysql -u liza_admin -pStrongPasswordHere123 liza_store < sql/schema.sql

# Копирование конфига Nginx
sudo cp deploy/nginx/liza-gamestore.conf /etc/nginx/sites-available/
sudo ln -sf /etc/nginx/sites-available/liza-gamestore.conf /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx

# Basic Auth
sudo apt install -y apache2-utils
sudo htpasswd -c /etc/nginx/.htpasswd liza_admin

echo "=== Установка завершена. Отредактируйте dp.php (пароль MySQL и SITE_URL)."
