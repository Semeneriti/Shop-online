<!DOCTYPE html>
<html>
<head>
    <title>Оформление заказа</title>
</head>
<body>
<h1>Оформление заказа</h1>

<?php if (!empty($cartData['items'])): ?>
    <h2>Ваш заказ:</h2>
    <ul>
        <?php foreach ($cartData['items'] as $item): ?>
            <li>
                <?php echo htmlspecialchars($item['product']->getName()); ?> -
                <?php echo $item['amount']; ?> шт. ×
                <?php echo $item['product']->getPrice(); ?> ₽ =
                <?php echo $item['total_price']; ?> ₽
            </li>
        <?php endforeach; ?>
    </ul>

    <h3>Итого: <?php echo $cartData['total_price']; ?> ₽</h3>

    <form method="POST" action="/checkout">
        <h3>Данные для доставки:</h3>

        <label>Адрес доставки:</label><br>
        <input type="text" name="address" required
               value="<?php echo htmlspecialchars($formData['address'] ?? ''); ?>"
               style="width: 300px;">
        <?php if (isset($errors['address'])): ?>
            <span style="color: red;"><?php echo $errors['address']; ?></span>
        <?php endif; ?><br><br>

        <label>Телефон:</label><br>
        <input type="text" name="phone" required
               value="<?php echo htmlspecialchars($formData['phone'] ?? ''); ?>">
        <?php if (isset($errors['phone'])): ?>
            <span style="color: red;"><?php echo $errors['phone']; ?></span>
        <?php endif; ?><br><br>

        <label>Комментарий к заказу:</label><br>
        <textarea name="comment" rows="3"><?php echo htmlspecialchars($formData['comment'] ?? ''); ?></textarea><br><br>

        <button type="submit">Подтвердить заказ</button>
    </form>
<?php else: ?>
    <p>Корзина пуста</p>
<?php endif; ?>

<br>
<a href="/cart">Вернуться в корзину</a>
</body>
</html>