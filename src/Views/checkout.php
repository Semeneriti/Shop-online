<?php
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Оформление заказа</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; border-radius: 8px; padding: 30px; }
        h1 { margin-bottom: 25px; }
        .error-box { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .order-summary { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 25px; }
        .order-items li { padding: 10px 0; border-bottom: 1px solid #dee2e6; list-style: none; }
        .total-price { font-size: 20px; font-weight: bold; color: #27ae60; text-align: right; margin-top: 15px; padding-top: 15px; border-top: 2px solid #dee2e6; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; }
        .form-control { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; }
        .error-message { color: #e74c3c; font-size: 13px; margin-top: 5px; }
        .btn { background: #27ae60; color: white; padding: 14px 30px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-size: 16px; }
        .back-link { display: inline-block; margin-top: 20px; color: #3498db; }
    </style>
</head>
<body>
<div class="container">
    <h1>Оформление заказа</h1>

    <?php if (!empty($errors)): ?>
        <div class="error-box">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars(is_array($error) ? implode(', ', $error) : $error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($cartData['items'])): ?>
        <div class="order-summary">
            <h2>Ваш заказ:</h2>
            <ul class="order-items">
                <?php foreach ($cartData['items'] as $item): ?>
                    <?php $productData = $item['product']; $amount = $item['amount']; ?>
                    <li>
                        <strong><?= htmlspecialchars($productData->getName()) ?></strong><br>
                        <?= $amount ?> шт. × <?= number_format($productData->getPrice(), 2, '.', ' ') ?> ₽
                        = <strong><?= number_format($productData->getPrice() * $amount, 2, '.', ' ') ?> ₽</strong>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="total-price">Итого: <?= number_format($cartData['total_price'] ?? 0, 2, '.', ' ') ?> ₽</div>
        </div>

        <form method="POST" action="/checkout">
            <div class="form-group">
                <label for="address">Адрес доставки *</label>
                <input type="text" id="address" name="address" class="form-control" value="<?= htmlspecialchars($formData['address'] ?? '') ?>" required>
                <?php if (isset($errors['address'])): ?>
                    <div class="error-message"><?= $errors['address'] ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="phone">Номер телефона *</label>
                <input type="tel" id="phone" name="phone" class="form-control" value="<?= htmlspecialchars($formData['phone'] ?? '') ?>" required>
                <?php if (isset($errors['phone'])): ?>
                    <div class="error-message"><?= $errors['phone'] ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="comment">Комментарий</label>
                <textarea id="comment" name="comment" class="form-control"><?= htmlspecialchars($formData['comment'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn">Подтвердить заказ</button>
        </form>
    <?php else: ?>
        <p>Корзина пуста</p>
        <a href="/catalog">Перейти в каталог</a>
    <?php endif; ?>

    <a href="/cart" class="back-link">Вернуться в корзину</a>
</div>
</body>
</html>
