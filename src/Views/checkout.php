<!DOCTYPE html>
<html>
<head>
    <title>Оформление заказа</title>
</head>
<body>
<h1>Оформление заказа</h1>

<?php if (!empty($cartItems)): ?>
    <h2>Ваш заказ:</h2>
    <ul>
        <?php foreach ($cartItems as $item): ?>
            <li><?php echo htmlspecialchars($item['name']); ?> -
                <?php echo $item['amount']; ?> шт. ×
                <?php echo $item['price']; ?> ₽ =
                <?php echo $item['amount'] * $item['price']; ?> ₽</li>
        <?php endforeach; ?>
    </ul>

    <h3>Итого: <?php echo $totalPrice; ?> ₽</h3>

    <form method="POST" action="/checkout">
        <h3>Данные для доставки:</h3>

        <label>Адрес доставки:</label><br>
        <input type="text" name="address" required style="width: 300px;"><br><br>

        <label>Телефон:</label><br>
        <input type="text" name="phone" required><br><br>

        <label>Комментарий к заказу:</label><br>
        <textarea name="comment" rows="3"></textarea><br><br>

        <button type="submit">Подтвердить заказ</button>
    </form>
<?php else: ?>
    <p>Корзина пуста</p>
<?php endif; ?>

<br>
<a href="/cart">Вернуться в корзину</a>
</body>
</html>