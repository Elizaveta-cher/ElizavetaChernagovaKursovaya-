<?php
require_once 'dp.php';
try {
    $stmt = $pdo->query("SELECT * FROM products");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Ошибка БД: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lizo4kaGameStore - Игровой магазин</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
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
                    <a href="index.php" class="nav-link active">Главная</a>
                    <a href="#services" class="nav-link">Услуги</a>
                    <a href="order_status.php" class="nav-link">Статус заказа</a>
                    <a href="#faq" class="nav-link">Частые вопросы</a>
                    <a href="support.php" class="nav-link">Поддержка</a>
                    <a href="#contacts" class="nav-link">Контакты</a>
                </nav>
                <div class="header-actions">
                    <a href="cart.php" class="cart-btn">🛒 Корзина <span class="cart-count">0</span></a>
                </div>
            </div>
        </div>
    </header>

    <section class="hero">
        <div class="container hero-content">
            <h1 class="hero-title">Профессиональный <span class="accent">буст</span> в твоих любимых играх</h1>
            <p class="hero-subtitle">Быстро • Безопасно • Гарантия результата</p>
            <a href="#services" class="btn btn-primary">Выбрать услугу</a>
        </div>
    </section>

    <main class="container" id="services">
        <h2 class="section-title">Услуги</h2>
        <div class="products-grid">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['title']) ?>" class="product-img">
                        <div class="product-info">
                            <h3 class="product-title"><?= htmlspecialchars($product['title']) ?></h3>
                            <p class="product-desc"><?= htmlspecialchars($product['description']) ?></p>
                            <span class="product-price"><?= number_format($product['price'], 0, '.', ' ') ?> ₽</span>
                            <button class="btn-buy" data-id="<?= $product['id'] ?>" data-title="<?= htmlspecialchars($product['title']) ?>" data-price="<?= $product['price'] ?>">В корзину</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Товары не найдены.</p>
            <?php endif; ?>
        </div>
    </main>

    <section class="features" id="features">
        <div class="container">
            <h2 class="section-title">Почему выбирают нас</h2>
            <div class="features-grid">
                <div class="feature-card" onclick="this.classList.toggle('active')">
                    <div class="feature-icon">🔒</div>
                    <h3>Безопасно</h3>
                    <p class="feature-short">Без доступа к вашему аккаунту</p>
                    <div class="feature-full-desc">Мы используем только легальные методы повышения рейтинга.</div>
                </div>
                <div class="feature-card" onclick="this.classList.toggle('active')">
                    <div class="feature-icon">🏆</div>
                    <h3>Гарантия</h3>
                    <p class="feature-short">Возврат средств при невыполнении</p>
                    <div class="feature-full-desc">Вернём 100% суммы без лишних вопросов.</div>
                </div>
                <div class="feature-card" onclick="this.classList.toggle('active')">
                    <div class="feature-icon">🎧</div>
                    <h3>Поддержка 24/7</h3>
                    <p class="feature-short">Отвечаем в течение 15 минут</p>
                    <div class="feature-full-desc">Менеджеры онлайн круглосуточно.</div>
                </div>
            </div>
        </div>
    </section>

    <section class="faq" id="faq">
        <div class="container">
            <h2 class="section-title">Частые вопросы</h2>
            <div class="faq-list">
                <div class="faq-item"><h3 class="faq-question">Как оформить заказ?</h3><p class="faq-answer">Добавьте услугу в корзину, перейдите к оформлению.</p></div>
                <div class="faq-item"><h3 class="faq-question">Какие способы оплаты?</h3><p class="faq-answer">Карты Visa, Mastercard, МИР, ЮMoney, Qiwi.</p></div>
                <div class="faq-item"><h3 class="faq-question">Сколько выполняется?</h3><p class="faq-answer">От 1 часа до 7 дней.</p></div>
                <div class="faq-item"><h3 class="faq-question">Что если не устроит?</h3><p class="faq-answer">Бесплатная доработка или возврат.</p></div>
            </div>
        </div>
    </section>

    <footer class="footer" id="contacts">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-column"><div class="logo"><span class="logo-icon">🎮</span><span class="logo-text">Lizo4ka<span class="accent">GameStore</span></span></div><p class="footer-desc">Профессиональный сервис с 2020 года.</p></div>
                <div class="footer-column"><h4 class="footer-title">Услуги</h4><ul class="footer-links"><li><a href="#">Буст рейтинга</a></li><li><a href="#">Прокачка</a></li></ul></div>
                <div class="footer-column"><h4 class="footer-title">Контакты</h4><ul class="footer-contacts"><li>📧 242185@edu.fa.ru</li><li>💬 @lizo4ka_support</li></ul></div>
            </div>
            <div class="footer-bottom"><p>&copy; 2026 Lizo4kaGameStore</p></div>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>