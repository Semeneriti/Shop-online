<?php
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Заказ оформлен</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
        .success { color: #27ae60; text-align: center; margin-bottom: 30px; font-size: 28px; }
        .order-details { background: #f8f9fa; padding: 25px; border-radius: 8px; border-left: 4px solid #27ae60; margin-bottom: 30px; }
        .order-number { font-size: 24px; font-weight: bold; color: #2980b9; }
        .status { display: inline-block; padding: 5px 15px; background: #e8f5e9; color: #27ae60; border-radius: 20px; font-size: 14px; }
        .item { padding: 15px 0; border-bottom: 1px solid #dee2e6; }
        .total { font-weight: bold; font-size: 20px; color: #27ae60; text-align: right; margin-top: 20px; padding-top: 20px; border-top: 2px solid #dee2e6; }
        .btn { display: inline-block; padding: 12px 24px; background: #27ae60; color: white; text-decoration: none; border-radius: 4px; margin: 5px; }
        .btn-secondary { background: #7f8c8d; }
        .btn-blue { background: #3498db; }
        .actions { text-align: center; margin-top: 30px; }
        .info-box { margin-top: 30px; padding: 15px; background: #e3f2fd; border-radius: 4px; border-left: 4px solid #3498db; }
    </style>
</head>
<body>
<div class="container">
    <h1 class="success">Заказ успешно оформлен!</h1>

    <div class="order-details">
        <h2>Детали заказа</h2>
        <p><strong>Номер заказа:</strong> <span class="order-number">#<?= htmlspecialchars($orderDetails['id'] ?? '') ?></span></p>
        <p><strong>Дата заказа:</strong> <?= date('d.m.Y H:i', strtotime($orderDetails['created_at'] ?? date('Y-m-d H:i:s'))) ?></p>
        <p><strong>Адрес доставки:</strong> <?= htmlspecialchars($address) ?></p>
        <p><strong>Телефон:</strong> <?= htmlspecialchars($phone) ?></p>
        <?php if (!empty($comment)): ?>
            <p><strong>Комментарий:</strong> <?= htmlspecialchars($comment) ?></p>
        <?php endif; ?>
        <p><strong>Статус:</strong> <span class="status"><?= $orderDetails['status'] ?? 'новый' ?></span></p>

        <h3 style="margin-top: 25px;">Состав заказа</h3>
        <?php if (!empty($orderDetails['items'])): ?>
            <?php foreach ($orderDetails['items'] as $item): ?>
                <div class="item">
                    <strong><?= htmlspecialchars($item['product_name'] ?? 'Товар') ?></strong>
                    <div style="display: flex; justify-content: space-between; margin-top: 5px;">
                        <span><?= $item['amount'] ?> шт. × <?= number_format($item['price'], 2, '.', ' ') ?> ₽</span>
                        <span><strong><?= number_format($item['amount'] * $item['price'], 2, '.', ' ') ?> ₽</strong></span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="total">
            <strong>Итого к оплате:</strong> <?= number_format($orderDetails['total_price'] ?? 0, 2, '.', ' ') ?> ₽
        </div>
    </div>

    <div class="actions">
        <a href="/catalog" class="btn">Продолжить покупки</a>
        <a href="/profile" class="btn btn-secondary">Личный кабинет</a>
    </div>

    <div class="info-box">
        <p>Вы получите SMS-уведомление о статусе заказа. Ожидайте звонка оператора.</p>
    </div>
</div>
</body>
</html>
