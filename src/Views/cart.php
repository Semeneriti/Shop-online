<?php
$cartItems = $cartData['items'] ?? [];
$totalPrice = $cartData['total_price'] ?? 0;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Корзина</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background-color: #f5f5f5; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; background: white; border-radius: 8px; padding: 30px; }
        h1 { margin-bottom: 25px; }
        .success-message { background: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 20px; }
        .error-message { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8f9fa; padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6; }
        td { padding: 12px; border-bottom: 1px solid #dee2e6; }
        .total-row td { font-weight: bold; border-top: 2px solid #dee2e6; }
        .btn { display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; }
        .btn:hover { background: #2980b9; }
        .btn-red { background: #e74c3c; }
        .btn-red:hover { background: #c0392b; }
        .btn-green { background: #27ae60; }
        .btn-green:hover { background: #229954; }
        .quantity-control { display: flex; align-items: center; gap: 10px; }
        .quantity-form { display: inline; }
        .actions { display: flex; justify-content: space-between; margin-top: 20px; }
        .checkout-link { background: #27ae60; color: white; padding: 12px 30px; text-decoration: none; border-radius: 4px; }
        .empty-cart { text-align: center; padding: 40px; color: #999; }
    </style>
</head>
<body>
<div class="container">
    <h1>Корзина</h1>

    <?php if ($successMessage): ?>
        <div class="success-message"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        <div class="error-message"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <?php if (!empty($cartItems)): ?>
        <table>
            <thead>
            <tr><th>Товар</th><th>Цена</th><th>Количество</th><th>Сумма</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($cartItems as $item): ?>
                <?php
                $product = $item['product'];
                $amount = $item['amount'];

                if (is_array($product)) {
                    $productName = $product['name'] ?? 'Товар';
                    $productPrice = $product['price'] ?? 0;
                    $productId = $product['id'] ?? 0;
                } else {
                    $productName = $product->getName();
                    $productPrice = $product->getPrice();
                    $productId = $product->getId();
                }
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($productName) ?></strong></td>
                    <td><?= number_format($productPrice, 2, '.', ' ') ?> ₽</td>
                    <td>
                        <div class="quantity-control">
                            <form action="/cart/decrease" method="POST" class="quantity-form">
                                <input type="hidden" name="product_id" value="<?= $productId ?>">
                                <button type="submit" class="btn btn-red">-</button>
                            </form>
                            <span><?= $amount ?></span>
                            <form action="/cart/increase" method="POST" class="quantity-form">
                                <input type="hidden" name="product_id" value="<?= $productId ?>">
                                <button type="submit" class="btn btn-green">+</button>
                            </form>
                        </div>
                    </td>
                    <td><strong><?= number_format($productPrice * $amount, 2, '.', ' ') ?> ₽</strong></td>
                    <td>
                        <form action="/cart/remove" method="POST">
                            <input type="hidden" name="product_id" value="<?= $productId ?>">
                            <button type="submit" class="btn btn-red">Удалить</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <tr class="total-row"><td colspan="3"><strong>Итого:</strong></td><td><strong><?= number_format($totalPrice, 2, '.', ' ') ?> ₽</strong></td><td></td></tr>
            </tbody>
        </table>
        <div class="actions">
            <a href="/catalog">Продолжить покупки</a>
            <form action="/cart/clear" method="POST" onsubmit="return confirm('Очистить корзину?');">
                <button type="submit" class="btn btn-red">Очистить корзину</button>
            </form>
            <a href="/checkout" class="checkout-link">Оформить заказ</a>
        </div>
    <?php else: ?>
        <div class="empty-cart"><p>Корзина пуста</p><a href="/catalog" class="btn">Перейти в каталог</a></div>
    <?php endif; ?>
</div>
</body>
</html>
