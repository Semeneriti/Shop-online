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

        .cart-info.guest {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }

        .cart-info-user {
            background-color: #e8f5e9;
            border: 1px solid #4caf50;
        }

        .cart-info-empty {
            background-color: #fff3e0;
            border: 1px solid #ff9800;
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
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
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
            display: flex;
            justify-content: center;
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
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🛍️ Каталог товаров</h1>
        <div class="nav">
            <a href="/cart">Корзина</a>
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
        <?php if ($cartItemsCount > 0): ?>
            <div class="cart-info cart-info-user">
                    <span style="font-weight: bold; color: #2e7d32;">
                        🛒 В корзине: <?= $cartItemsCount ?> товар(ов) на сумму <?= number_format($cartTotalPrice, 2, '.', ' ') ?> ₽
                    </span>
                <a href="/cart" class="cart-link" style="background-color: #4caf50;">Перейти в корзину →</a>
            </div>
        <?php else: ?>
            <div class="cart-info cart-info-empty">
                    <span style="font-weight: bold; color: #ef6c00;">
                        🛒 Ваша корзина пуста
                    </span>
                <a href="#products" class="cart-link" style="background-color: #ff9800;">Выберите товары ↓</a>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="cart-info guest">
                <span style="font-weight: bold; color: #616161;">
                    🔐 Авторизуйтесь для добавления товаров в корзину
                </span>
            <a href="/login" class="cart-link" style="background-color: #2196f3;">Войти в систему</a>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['userId'])): ?>
        <div class="add-product-form">
            <h4>➕ Быстрое добавление в корзину</h4>
            <form action="/add-product" method="POST">
                <div class="input-container">
                    <span class="icon">🆔</span>
                    <input class="input-field" type="text" placeholder="ID товара" name="product-id" required>
                </div>
                <div class="input-container">
                    <span class="icon">🔢</span>
                    <input class="input-field" type="number" placeholder="Количество" name="amount" required min="1">
                </div>
                <button type="submit" class="btn">➕ Добавить в корзину</button>
            </form>
        </div>
    <?php endif; ?>

    <h2 style="margin-bottom: 20px; color: #333;" id="products">Товары</h2>

    <div class="products-grid">
        <?php foreach ($products as $product): ?>
            <div class="product-card">
                <!-- БЛОК С КАРТИНКОЙ -->
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
                        <div class="product-actions">
                            <?php if ($inCart && $cartAmount > 0): ?>
                                <div class="cart-control">
                                    <form action="/cart/decrease" method="POST">
                                        <input type="hidden" name="product_id" value="<?= $product->getId() ?>">
                                        <button type="submit" class="btn btn-red btn-sm">−</button>
                                    </form>
                                    <span class="cart-amount"><?= $cartAmount ?></span>
                                    <form action="/cart/increase" method="POST">
                                        <input type="hidden" name="product_id" value="<?= $product->getId() ?>">
                                        <button type="submit" class="btn btn-green btn-sm">+</button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <form action="/add-product" method="POST">
                                    <input type="hidden" name="product-id" value="<?= $product->getId() ?>">
                                    <div style="display: flex; gap: 5px;">
                                        <input type="number" name="amount" value="1" min="1"
                                               style="width: 60px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; text-align: center;">
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
</body>
</html>