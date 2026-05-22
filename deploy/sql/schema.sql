-- Подключаемся к базе
USE liza_store;

-- Таблица администраторов
CREATE TABLE IF NOT EXISTS `admins` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(50) NOT NULL,
    `password_hash` varchar(255) NOT NULL,
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица товаров
CREATE TABLE IF NOT EXISTS `products` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `description` text,
    `price` decimal(10,2) NOT NULL,
    `image` varchar(500) DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Вставка товаров
INSERT INTO `products` (`title`, `description`, `price`, `image`) VALUES
('Буст рейтинга CS2', 'Профессиональный буст рейтинга в Counter-Strike 2. Быстро и безопасно.', 2500, '/uploads/1.png'),
('Прокачка аккаунта Valorant', 'Повышение ранга в Valorant от Iron до Radiant. Играют профессионалы.', 3500, '/uploads/2.png'),
('Фарм игровой валюты', 'Быстрый фарм игровой валюты в любых MMO играх. Любые суммы.', 1500, '/uploads/3.png'),
('Прокачка персонажа WoW', 'Комплексная прокачка персонажа в World of Warcraft. Любые классы.', 4000, '/uploads/4.png'),
('Буст ранга Dota 2', 'Поднятие MMR в Dota 2. Индивидуальный подход к каждому клиенту.', 3000, '/uploads/5.png'),
('Услуги в Apex Legends', 'Буст ранга и прокачка легенд в Apex Legends.', 2000, '/uploads/6.png');

-- Таблица платежей
CREATE TABLE IF NOT EXISTS `payments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `payment_id` varchar(50) NOT NULL,
    `user_email` varchar(255) NOT NULL,
    `user_contact` varchar(255) DEFAULT NULL,
    `amount` decimal(10,2) NOT NULL,
    `products` text NOT NULL,
    `status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
    `payment_method` varchar(50) DEFAULT 'card',
    `card_mask` varchar(50) DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `paid_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `payment_id` (`payment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица тикетов поддержки
CREATE TABLE IF NOT EXISTS `support_tickets` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `ticket_id` varchar(20) NOT NULL,
    `user_email` varchar(255) NOT NULL,
    `user_name` varchar(100) DEFAULT NULL,
    `subject` varchar(255) NOT NULL,
    `message` text NOT NULL,
    `status` enum('open','in_progress','resolved','closed') DEFAULT 'open',
    `priority` enum('low','normal','high') DEFAULT 'normal',
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `ticket_id` (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Администратор (пароль: 123456qQ)
INSERT INTO `admins` (`username`, `password_hash`) VALUES
('liza_admin', '$2y$12$VQ3uF91Nb.7EMJ2GnnDKD.dtFhu7pNXYDcw9J975GT5M/PCRWQSrO');
