<?php
/** @var array $cartData */
/** @var array $errors */
/** @var array $formData */
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оформление заказа</title>
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
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 30px;
        }

        h1 {
            color: #333;
            margin-bottom: 25px;
            font-size: 28px;
        }

        h2, h3 {
            color: #555;
            margin-bottom: 15px;
        }

        .order-summary {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .order-items {
            list-style: none;
            margin-bottom: 15px;
        }

        .order-items li {
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
            color: #666;
        }

        .order-items li:last-child {
            border-bottom: none;
        }

        .total-price {
            font-size: 20px;
            font-weight: bold;
            color: #27ae60;
            text-align: right;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #dee2e6;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: bold;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 2px rgba(52,152,219,0.1);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }

        .error-message {
            color: #e74c3c;
            font-size: 13px;
            margin-top: 5px;
        }

        .btn {
            background-color: #27ae60;
            color: white;
            padding: 14px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            width: 100%;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #229954;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #3498db;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .empty-cart {
            text-align: center;
            padding: 40px;
            color: #999;
            font-style: italic;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>✅ Оформление заказа</h1>

    <?php if (!empty($cartData['items'])): ?>
        <div class="order-summary">
            <h2>Ваш заказ:</h2>
            <ul class="order-items">
                <?php foreach ($cartData['items'] as $item): ?>
                    <?php
                    // Получаем данные о товаре (универсальный код)
                    $productData = $item['product'] ?? [];
                    $amount = $item['amount'] ?? 1;

                    // Определяем название и цену товара (работает и с объектом, и с массивом)
                    if (is_object($productData) && method_exists($productData, 'getName')) {
                        // Это объект Product с методами
                        $productName = $productData->getName();
                        $productPrice = $productData->getPrice();
                    } elseif (is_array($productData)) {
                        // Это массив с данными
                        $productName = $productData['name'] ?? 'Товар';
                        $productPrice = $productData['price'] ?? 0;
                    } else {
                        // Неизвестный формат
                        $productName = 'Товар';
                        $productPrice = 0;
                    }

                    $itemTotal = $item['total_price'] ?? ($productPrice * $amount);
                    ?>
                    <li>
                        <strong><?= htmlspecialchars($productName) ?></strong><br>
                        <span style="color: #666;">
                            <?= $amount ?> шт. × <?= number_format($productPrice, 2, '.', ' ') ?> ₽
                            = <strong><?= number_format($itemTotal, 2, '.', ' ') ?> ₽</strong>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="total-price">
                Итого: <?= number_format($cartData['total_price'] ?? 0, 2, '.', ' ') ?> ₽
            </div>
        </div>

        <h3>📦 Данные для доставки</h3>

        <form method="POST" action="/checkout">
            <div class="form-group">
                <label for="address">Адрес доставки *</label>
                <input type="text" id="address" name="address" class="form-control"
                       value="<?= htmlspecialchars($formData['address'] ?? '') ?>" required>
                <?php if (isset($errors['address'])): ?>
                    <div class="error-message"><?= $errors['address'] ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="phone">Номер телефона *</label>
                <input type="tel" id="phone" name="phone" class="form-control"
                       value="<?= htmlspecialchars($formData['phone'] ?? '') ?>"
                       placeholder="+7 (999) 999-99-99" required>
                <?php if (isset($errors['phone'])): ?>
                    <div class="error-message"><?= $errors['phone'] ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="comment">Комментарий к заказу</label>
                <textarea id="comment" name="comment" class="form-control"
                          placeholder="Укажите удобное время доставки, подъезд, этаж и т.д."><?= htmlspecialchars($formData['comment'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn">✅ Подтвердить заказ</button>
        </form>
    <?php else: ?>
        <div class="empty-cart">
            <p style="font-size: 18px; margin-bottom: 20px;">Ваша корзина пуста</p>
            <p style="margin-bottom: 30px;">Добавьте товары в корзину перед оформлением заказа</p>
            <a href="/catalog" class="btn" style="width: auto; background-color: #3498db;">Перейти в каталог</a>
        </div>
    <?php endif; ?>

    <a href="/cart" class="back-link">← Вернуться в корзину</a>
</div>
</body>
</html>