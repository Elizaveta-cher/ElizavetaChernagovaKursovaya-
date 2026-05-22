# Быстрое развертывание сайта Lizo4kaGameStore

## 1. Подготовка сервера
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx mysql-server php8.5-fpm php8.5-mysql php8.5-curl php8.5-mbstring php8.5-xml php8.5-zip php8.5-gd php8.5-intl composer git curl wget unzip
2. Клонирование репозитория
bash
cd /opt
sudo git clone https://github.com/Elizaveta-cher/ElizavetaChernagovaKursovaya-.git liza-gamestore
3. Запуск установочного скрипта
bash
cd /opt/liza-gamestore
sudo bash deploy/scripts/setup.sh
4. Настройка подключения к БД
Отредактируйте dp.php: укажите пароль MySQL (StrongPasswordHere123) и SITE_URL.

5. Загрузка изображений
Поместите 1.png ... 6.png в /opt/liza-gamestore/uploads/

6. Домен и SSL
Настройте A-запись домена на IP сервера

Затем выполните:

bash
sudo certbot --nginx -d ваш-домен.ru -d www.ваш-домен.ru
Обновите SITE_URL в dp.php на https://ваш-домен.ru

7. Проверка
Главная страница: http://IP/

Админ-панель: http://IP/admin_payments.php (пароль от .htpasswd)

Подробности – в файлах docs/troubleshooting.md и комментариях в конфигах.
