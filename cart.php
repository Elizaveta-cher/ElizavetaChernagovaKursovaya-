<?php
session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Получение количества (AJAX)
if (isset($_GET['get_count'])) {
    echo count($_SESSION['cart']);
    exit;
}

// Добавление товара (AJAX)
if (isset($_POST['add_to_cart'])) {
    $product_id   = $_POST['product_id'] ?? '';
    $product_title= $_POST['product_title'] ?? '';
    $product_price= (float) ($_POST['product_price'] ?? 0);

    if ($product_id && $product_title) {
        $_SESSION['cart'][] = [
            'id'    => $product_id,
            'title' => $product_title,
            'price' => $product_price
        ];
    }

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo count($_SESSION['cart']);
        exit;
    }

    header('Location: cart.php');
    exit;
}

// Удаление товара
if (isset($_GET['remove'])) {
    $index = (int)$_GET['remove'];
    if (isset($_SESSION['cart'][$index])) {
        unset($_SESSION['cart'][$index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }
    header('Location: cart.php');
    exit;
}

// Очистка корзины
if (isset($_GET['clear'])) {
    $_SESSION['cart'] = [];
    header('Location: cart.php');
    exit;
}

$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина - Lizo4kaGameStore</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Дополнительные стили для корзины (если нужно усилить) */
        .cart-header {
            margin-bottom: 30px;
        }
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #1a1a2e;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 15px;
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid #0f3460;
        }
        .cart-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            border-color: #4361ee;
        }
        .cart-item-info {
            flex: 1;
        }
        .cart-item-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #fff;
            margin-bottom: 5px;
        }
        .cart-item-price {
            color: #ff007f;
            font-weight: 700;
            font-size: 1.2rem;
        }
        .btn-remove {
            background: none;
            border: none;
            color: #ff4757;
            font-size: 1.2rem;
            cursor: pointer;
            transition: 0.2s;
            padding: 8px 12px;
            border-radius: 8px;
        }
        .btn-remove:hover {
            background: rgba(255,71,87,0.2);
            color: #ff6b81;
        }
        .cart-total-card {
            background: #16213e;
            padding: 30px;
            border-radius: 12px;
            margin-top: 20px;
            text-align: right;
            border: 1px solid #0f3460;
        }
        .total-amount {
            font-size: 2rem;
            font-weight: 700;
            color: #ff007f;
        }
        .cart-actions {
            display: flex;
            gap: 20px;
            justify-content: flex-end;
            margin-top: 30px;
        }
        .empty-cart {
            text-align: center;
            padding: 80px 20px;
            background: #16213e;
            border-radius: 16px;
        }
        .empty-cart-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
        .btn-outline {
            background: transparent;
            border: 2px solid #4361ee;
            color: #4361ee;
        }
        .btn-outline:hover {
            background: #4361ee;
            color: #fff;
        }
        @media (max-width: 768px) {
            .cart-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            .cart-actions {
                flex-direction: column;
                gap: 12px;
            }
            .cart-actions .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>

    <!-- ========== ШАПКА (как на главной) ========== -->
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
                    <a href="support.php" class="nav-link">Поддержка</a>
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
        <h1 class="section-title">🛒 Моя корзина</h1>

        <?php if (empty($_SESSION['cart'])): ?>
            <div class="empty-cart">
                <div class="empty-cart-icon">🛍️</div>
                <h2>Корзина пуста</h2>
                <p style="color: #aaa; margin: 15px 0;">Добавьте товары, чтобы оформить заказ</p>
                <a href="index.php#services" class="btn btn-primary">Перейти к услугам</a>
            </div>
        <?php else: ?>
            <div class="cart-items">
                <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                    <div class="cart-item">
                        <div class="cart-item-info">
                            <div class="cart-item-title"><?= htmlspecialchars($item['title']) ?></div>
                            <div class="cart-item-price"><?= number_format($item['price'], 0, '.', ' ') ?> ₽</div>
                        </div>
                        <div>
                            <a href="cart.php?remove=<?= $index ?>" class="btn-remove" title="Удалить">✕ Удалить</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-total-card">
                <div style="font-size: 1.2rem; margin-bottom: 10px;">Итого к оплате:</div>
                <div class="total-amount"><?= number_format($total, 0, '.', ' ') ?> ₽</div>
                
                <div class="cart-actions">
                    <a href="cart.php?clear=1" class="btn btn-secondary btn-outline" style="background:transparent;">Очистить корзину</a>
                    <a href="checkout.php" class="btn btn-primary">Оформить заказ →</a>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!-- ========== ПОДВАЛ ========== -->
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
        // Обновляем счётчик корзины при загрузке
        document.addEventListener('DOMContentLoaded', function() {
            fetch('cart.php?get_count=1')
                .then(r => r.text())
                .then(c => { document.querySelector('.cart-count').textContent = c; })
                .catch(console.error);
        });
    </script>
</body>
</html>