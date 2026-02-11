<!DOCTYPE html>
<html lang='ru'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Заказ оформлен</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success {
            color: #2e7d32;
            text-align: center;
            margin-bottom: 30px;
        }
        .order-details {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            border-left: 4px solid #4caf50;
            margin-bottom: 30px;
        }
        .item {
            padding: 12px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .item:last-child {
            border-bottom: none;
        }
        .total {
            font-weight: bold;
            font-size: 1.3em;
            color: #2e7d32;
            text-align: right;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #dee2e6;
        }
        .order-number {
            font-size: 1.5em;
            font-weight: bold;
            color: #1976d2;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #4caf50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #388e3c;
        }
        .btn-secondary {
            background: #757575;
        }
        .btn-secondary:hover {
            background: #616161;
        }
        .status {
            display: inline-block;
            padding: 5px 15px;
            background: #e8f5e9;
            color: #2e7d32;
            border-radius: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container">
    <h1 class="success">✅ Заказ успешно оформлен!</h1>

    <p style="text-align: center; font-size: 1.1em; margin-bottom: 30px;">
        Спасибо за покупку! Мы свяжемся с вами для подтверждения заказа.
    </p>

    <div class="order-details">
        <h2 style="margin-top: 0;">📋 Детали заказа</h2>

        <p><strong>Номер заказа:</strong> <span class="order-number">#<?= $orderDetails['id'] ?? 'N/A' ?></span></p>
        <p><strong>Дата заказа:</strong> <?= $orderDetails['created_at'] ?? date('Y-m-d H:i:s') ?></p>
        <p><strong>Адрес доставки:</strong> <?= htmlspecialchars($address) ?></p>
        <p><strong>Телефон:</strong> <?= htmlspecialchars($phone) ?></p>

        <?php if (!empty($comment)): ?>
            <p><strong>Комментарий:</strong> <?= htmlspecialchars($comment) ?></p>
        <?php endif; ?>

        <p><strong>Статус:</strong> <span class="status"><?= $orderDetails['status'] ?? 'новый' ?></span></p>

        <h3 style="margin-top: 25px;">🛒 Состав заказа</h3>

        <?php if (!empty($orderDetails['items'])): ?>
            <?php foreach ($orderDetails['items'] as $item): ?>
                <div class='item'>
                    <strong><?= htmlspecialchars($item['product_name'] ?? $item['name'] ?? 'Товар') ?></strong><br>
                    <span style="color: #666;">
                            <?= $item['amount'] ?? 1 ?> шт. ×
                            <?= number_format($item['price'] ?? 0, 2) ?> ₽ =
                            <strong><?= number_format(($item['amount'] ?? 1) * ($item['price'] ?? 0), 2) ?> ₽</strong>
                        </span>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: #999; text-align: center;">Нет информации о товарах</p>
        <?php endif; ?>

        <div class='total'>
            <strong>Итого к оплате:</strong>
            <?= number_format($orderDetails['total_price'] ?? 0, 2) ?> ₽
        </div>
    </div>

    <div style="text-align: center; margin-top: 30px;">
        <a href='/catalog' class='btn'>🔄 Вернуться в каталог</a>
        <a href='/profile' class='btn btn-secondary'>👤 Перейти в профиль</a>
        <a href='/user-orders' class='btn'>📋 Мои заказы</a>
    </div>

    <div style="margin-top: 30px; padding: 15px; background: #e3f2fd; border-radius: 5px; font-size: 0.9em;">
        <p style="margin: 0; color: #1565c0;">
            <strong>ℹ️ Информация:</strong>
            Вы получите SMS-уведомление о статусе заказа.
            Ожидайте звонка оператора для подтверждения заказа в течение 30 минут.
        </p>
    </div>
</div>
</body>
</html>