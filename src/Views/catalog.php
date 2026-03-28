<?php
/** @var array $products */
/** @var array $cartItems */
/** @var float $cartTotalPrice */
/** @var int $cartItemsCount */
/** @var string|null $successMessage */
/** @var string|null $errorMessage */
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог товаров</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            color: #333;
            font-size: 24px;
        }

        .nav a {
            color: #3498db;
            text-decoration: none;
            margin-left: 20px;
            transition: color 0.3s;
        }

        .nav a:hover {
            color: #2980b9;
            text-decoration: underline;
        }

        .cart-badge {
            background-color: #e74c3c;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
            margin-left: 5px;
            display: inline-block;
        }

        .cart-info {
            background-color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .cart-info-empty {
            background-color: #fff3e0;
            border: 1px solid #ff9800;
        }

        .cart-info-user {
            background-color: #e8f5e9;
            border: 1px solid #4caf50;
        }

        .cart-link {
            padding: 8px 20px;
            border-radius: 4px;
            color: white;
            text-decoration: none;
            font-weight: bold;
            transition: opacity 0.3s;
        }

        .cart-link:hover {
            opacity: 0.9;
        }

        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .add-product-form {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 30px;
        }

        .add-product-form h4 {
            color: #333;
            margin-bottom: 20px;
            font-size: 18px;
        }

        .input-container {
            display: flex;
            margin-bottom: 15px;
        }

        .icon {
            padding: 12px;
            background-color: #3498db;
            color: white;
            min-width: 50px;
            text-align: center;
            border-radius: 4px 0 0 4px;
        }

        .input-field {
            flex: 1;
            padding: 12px;
            border: 1px solid #ddd;
            border-left: none;
            border-radius: 0 4px 4px 0;
            font-size: 14px;
            outline: none;
        }

        .input-field:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52,152,219,0.1);
        }

        .btn {
            background-color: #3498db;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }

        .btn-green {
            background-color: #27ae60;
        }

        .btn-green:hover {
            background-color: #229954;
        }

        .btn-red {
            background-color: #e74c3c;
        }

        .btn-red:hover {
            background-color: #c0392b;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .product-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .product-image-container {
            text-align: center;
            padding: 15px;
            background-color: #f9f9f9;
            border-bottom: 1px solid #eee;
        }

        .product-image {
            max-width: 180px;
            max-height: 180px;
            min-height: 180px;
            object-fit: contain;
            border-radius: 8px;
        }

        .product-body {
            padding: 20px;
        }

        .product-name {
            color: #2c3e50;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .product-description {
            color: #7f8c8d;
            font-size: 13px;
            line-height: 1.5;
            margin-bottom: 15px;
            height: 60px;
            overflow: hidden;
        }

        .product-footer {
            padding: 15px 20px;
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }

        .product-price {
            font-size: 20px;
            font-weight: bold;
            color: #27ae60;
            margin-bottom: 10px;
        }

        .product-actions {
            margin-top: 10px;
        }

        .cart-control {
            display: flex;
            align-items: center;
            gap: 10px;
            justify-content: center;
        }

        .cart-amount {
            padding: 5px 15px;
            background-color: #27ae60;
            color: white;
            border-radius: 3px;
            font-weight: bold;
        }

        .product-link {
            text-decoration: none;
            color: inherit;
        }

        .product-link:hover .product-name {
            color: #3498db;
        }

        .footer-links {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            display: flex;
            gap: 20px;
            justify-content: center;
        }

        .footer-links a {
            color: #3498db;
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-links a:hover {
            color: #2980b9;
            text-decoration: underline;
        }

        .rating {
            color: #f39c12;
            margin-top: 5px;
            font-size: 14px;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 4px;
            color: white;
            z-index: 1000;
            display: none;
            animation: slideIn 0.3s ease;
        }

        .notification.success {
            background-color: #27ae60;
        }

        .notification.error {
            background-color: #e74c3c;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🛍️ Каталог товаров</h1>
        <div class="nav">
            <a href="/cart">Корзина <span id="cart-badge" class="cart-badge" style="display: <?= $cartItemsCount > 0 ? 'inline-block' : 'none' ?>"><?= $cartItemsCount ?></span></a>
            <?php if (isset($_SESSION['userId'])): ?>
                <a href="/profile">Профиль</a>
                <a href="/logout">Выход</a>
            <?php else: ?>
                <a href="/login">Вход</a>
                <a href="/registration">Регистрация</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($successMessage): ?>
        <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        <div class="alert alert-error"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['userId'])): ?>
        <div class="cart-info <?= $cartItemsCount > 0 ? 'cart-info-user' : 'cart-info-empty' ?>" id="cart-info-block">
            <?php if ($cartItemsCount > 0): ?>
                <span style="font-weight: bold; color: #2e7d32;">
                    🛒 В корзине: <span class="cart-count"><?= $cartItemsCount ?></span> товар(ов) на сумму <span class="cart-total"><?= number_format($cartTotalPrice, 2, '.', ' ') ?></span> ₽
                </span>
                <a href="/cart" class="cart-link" style="background-color: #4caf50;">Перейти в корзину →</a>
            <?php else: ?>
                <span style="font-weight: bold; color: #ef6c00;">
                    🛒 Ваша корзина пуста
                </span>
                <a href="#products" class="cart-link" style="background-color: #ff9800;">Выберите товары ↓</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="cart-info">
            <span style="font-weight: bold; color: #616161;">
                🔐 Авторизуйтесь для добавления товаров в корзину
            </span>
            <a href="/login" class="cart-link" style="background-color: #2196f3;">Войти в систему</a>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['userId'])): ?>
        <div class="add-product-form">
            <h4>➕ Быстрое добавление в корзину</h4>
            <form class="ajax-add-to-cart" action="/api/cart/add" method="POST">
                <div class="input-container">
                    <span class="icon">🆔</span>
                    <input class="input-field" type="text" placeholder="ID товара" name="product_id" required>
                </div>
                <div class="input-container">
                    <span class="icon">🔢</span>
                    <input class="input-field" type="number" placeholder="Количество" name="amount" required min="1" value="1">
                </div>
                <button type="submit" class="btn">➕ Добавить в корзину</button>
            </form>
        </div>
    <?php endif; ?>

    <h2 style="margin-bottom: 20px; color: #333;" id="products">Товары</h2>

    <div class="products-grid">
        <?php foreach ($products as $product): ?>
            <?php
            $inCart = false;
            $cartAmount = 0;
            foreach ($cartItems as $cartItem) {
                if ($cartItem['product']->getId() == $product->getId()) {
                    $inCart = true;
                    $cartAmount = $cartItem['amount'];
                    break;
                }
            }
            ?>
            <div class="product-card" data-product-id="<?= $product->getId() ?>">
                <div class="product-image-container">
                    <a href="/product?id=<?= $product->getId() ?>">
                        <img src="<?= htmlspecialchars($product->getImageUrl()) ?>"
                             alt="<?= htmlspecialchars($product->getName()) ?>"
                             class="product-image"
                             onerror="this.onerror=null; this.src='https://via.placeholder.com/180x180?text=<?= urlencode($product->getName()) ?>';">
                    </a>
                </div>

                <a href="/product?id=<?= $product->getId() ?>" class="product-link">
                    <div class="product-body">
                        <div class="product-name"><?= htmlspecialchars($product->getName()) ?></div>
                        <div class="product-description">
                            <?= htmlspecialchars(substr($product->getDescription(), 0, 100)) ?>...
                        </div>
                        <div class="rating">
                            ⭐ <?= $product->getAverageRating() ?> (<?= count($product->getReviews()) ?> отзывов)
                        </div>
                    </div>
                </a>
                <div class="product-footer">
                    <div class="product-price"><?= number_format($product->getPrice(), 2, '.', ' ') ?> ₽</div>

                    <?php if (isset($_SESSION['userId'])): ?>
                        <div class="product-actions">
                            <?php if ($inCart && $cartAmount > 0): ?>
                                <div class="cart-control">
                                    <button type="button" class="btn btn-red btn-sm ajax-decrease" data-product-id="<?= $product->getId() ?>">−</button>
                                    <span class="cart-amount"><?= $cartAmount ?></span>
                                    <button type="button" class="btn btn-green btn-sm ajax-increase" data-product-id="<?= $product->getId() ?>">+</button>
                                    <button type="button" class="btn btn-red btn-sm ajax-remove" data-product-id="<?= $product->getId() ?>" style="background-color: #e74c3c;">🗑️</button>
                                </div>
                            <?php else: ?>
                                <form class="ajax-add-to-cart add-form" action="/api/cart/add" method="POST">
                                    <input type="hidden" name="product_id" value="<?= $product->getId() ?>">
                                    <div style="display: flex; gap: 5px;">
                                        <input type="number" name="amount" value="1" min="1" style="width: 60px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; text-align: center;">
                                        <button type="submit" class="btn btn-green btn-sm">➕ Добавить</button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="footer-links">
        <a href="/cart">🛒 Корзина</a>
        <?php if (isset($_SESSION['userId'])): ?>
            <a href="/profile">👤 Мой профиль</a>
            <a href="/logout">🚪 Выход</a>
        <?php else: ?>
            <a href="/login">🔐 Вход</a>
            <a href="/registration">📝 Регистрация</a>
        <?php endif; ?>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Добавление в корзину через AJAX
        $('.ajax-add-to-cart').submit(function(e) {
            e.preventDefault();

            var form = $(this);

            $.ajax({
                type: "POST",
                url: "/api/cart/add",
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    // Обновляем количество товаров в бейдже корзины
                    $(".badge").text(response.cart_count);
                },
                error: function(xhr, status, error) {
                    console.error('Ошибка при добавлении товара:', error);
                }
            });
        });
    });
</script>

</body>
</html>