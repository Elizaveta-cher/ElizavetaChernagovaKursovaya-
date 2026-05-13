// Обновление счётчика корзины
function updateCartCount() {
    fetch('cart.php?get_count=1')
        .then(response => response.text())
        .then(count => {
            const cartCount = document.querySelector('.cart-count');
            if (cartCount) cartCount.textContent = count;
        })
        .catch(e => console.error('Ошибка счётчика:', e));
}

// Добавление товара (через data-атрибуты)
function addToCart(id, title, price) {
    const formData = new FormData();
    formData.append('add_to_cart', '1');
    formData.append('product_id', id);
    formData.append('product_title', title);
    formData.append('product_price', price);

    fetch('cart.php', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.text())
    .then(count => {
        const cartCount = document.querySelector('.cart-count');
        if (cartCount) cartCount.textContent = count;
        alert('✅ Товар добавлен! В корзине ' + count + ' товаров.');
    })
    .catch(error => console.error('Ошибка:', error));
}

// Привязываем обработчики ко всем кнопкам .btn-buy (альтернатива onclick)
document.addEventListener('DOMContentLoaded', () => {
    updateCartCount();

    // Находим все кнопки "В корзину"
    const buttons = document.querySelectorAll('.btn-buy');
    buttons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const id = btn.getAttribute('data-id');
            const title = btn.getAttribute('data-title');
            const price = btn.getAttribute('data-price');
            if (id && title && price) {
                addToCart(id, title, price);
            } else {
                console.error('Нет data-атрибутов у кнопки', btn);
            }
        });
    });

    // Умная шапка
    let lastScroll = 0;
    const header = document.querySelector('.header');
    if (header) {
        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            if (currentScroll > lastScroll && currentScroll > 100) {
                header.classList.add('header-hidden');
            } else {
                header.classList.remove('header-hidden');
            }
            lastScroll = currentScroll;
        });
    }

    // Мобильное меню
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mainNav = document.querySelector('.main-nav');
    if (mobileMenuBtn && mainNav) {
        mobileMenuBtn.addEventListener('click', () => {
            mainNav.classList.toggle('active');
            mobileMenuBtn.textContent = mainNav.classList.contains('active') ? '✕' : '☰';
        });
    }

    // Закрытие меню при клике на ссылку
    document.querySelectorAll('.main-nav .nav-link').forEach(link => {
        link.addEventListener('click', () => {
            if (mainNav) mainNav.classList.remove('active');
            if (mobileMenuBtn) mobileMenuBtn.textContent = '☰';
        });
    });
});