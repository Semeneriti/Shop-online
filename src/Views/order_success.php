<?php
/** @var array $orderDetails */
/** @var string $address */
/** @var string $phone */
/** @var string|null $comment */
?>
<!DOCTYPE html>
<html lang='ru'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Заказ оформлен</title>
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
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .success {
            color: #27ae60;
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
        }

        .order-details {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            border-left: 4px solid #27ae60;
            margin-bottom: 30px;
        }

        .order-number {
            font-size: 24px;
            font-weight: bold;
            color: #2980b9;
        }

        .status {
            display: inline-block;
            padding: 5px 15px;
            background: #e8f5e9;
            color: #27ae60;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
        }

        .item {
            padding: 15px 0;
            border-bottom: 1px solid #dee2e6;
        }

        .item:last-child {
            border-bottom: none;
        }

        .total {
            font-weight: bold;
            font-size: 20px;
            color: #27ae60;
            text-align: right;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #dee2e6;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #27ae60;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 5px;
            transition: background-color 0.3s;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn:hover {
            background: #229954;
        }

        .btn-secondary {
            background: #7f8c8d;
        }

        .btn-secondary:hover {
            background: #6c7a7a;
        }

        .btn-blue {
            background: #3498db;
        }

        .btn-blue:hover {
            background: #2980b9;
        }

        .info-box {
            margin-top: 30px;
            padding: 15px;
            background: #e3f2fd;
            border-radius: 4px;
            border-left: 4px solid #3498db;
        }

        .actions {
            text-align: center;
            margin-top: 30px;
        }

        h2, h3 {
            color: #333;
            margin-bottom: 15px;
        }

        p {
            margin-bottom: 10px;
            color: #666;
        }

        strong {
            color: #333;
        }
    </style>
</head>
<body>
<div class="container">
    <h1 class="success">✅ Заказ успешно оформлен!</h1>

    <p style="text-align: center; font-size: 16px; margin-bottom: 30px; color: #666;">
        Спасибо за покупку! Мы свяжемся с вами для подтверждения заказа.
    </p>

    <div class="order-details">
        <h2 style="margin-top: 0;">📋 Детали заказа</h2>

        <p><strong>Номер заказа:</strong> <span class="order-number">#<?= htmlspecialchars($orderDetails['id'] ?? '') ?></span></p>
        <p><strong>Дата заказа:</strong> <?= date('d.m.Y H:i', strtotime($orderDetails['created_at'] ?? date('Y-m-d H:i:s'))) ?></p>
        <p><strong>Адрес доставки:</strong> <?= htmlspecialchars($address) ?></p>
        <p><strong>Телефон:</strong> <?= htmlspecialchars($phone) ?></p>

        <?php if (!empty($comment)): ?>
            <p><strong>Комментарий:</strong> <?= htmlspecialchars($comment) ?></p>
        <?php endif; ?>

        <p><strong>Статус:</strong> <span class="status"><?= $orderDetails['status'] ?? 'новый' ?></span></p>

        <h3 style="margin-top: 25px;">🛒 Состав заказа</h3>

        <?php if (!empty($orderDetails['items'])): ?>
            <?php foreach ($orderDetails['items'] as $item): ?>
                <div class="item">
                    <strong style="font-size: 16px;"><?= htmlspecialchars($item['product_name'] ?? $item['name'] ?? 'Товар') ?></strong>
                    <div style="display: flex; justify-content: space-between; margin-top: 5px;">
                            <span style="color: #666;">
                                <?= $item['amount'] ?? 1 ?> шт. × <?= number_format($item['price'] ?? 0, 2, '.', ' ') ?> ₽
                            </span>
                        <span style="font-weight: bold; color: #27ae60;">
                                <?= number_format(($item['amount'] ?? 1) * ($item['price'] ?? 0), 2, '.', ' ') ?> ₽
                            </span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: #999; text-align: center; padding: 20px;">Нет информации о товарах</p>
        <?php endif; ?>

        <div class="total">
            <strong>Итого к оплате:</strong> <?= number_format($orderDetails['total_price'] ?? 0, 2, '.', ' ') ?> ₽
        </div>
    </div>

    <div class="actions">
        <a href="/catalog" class="btn">🔄 Продолжить покупки</a>
        <a href="/profile" class="btn btn-secondary">👤 Личный кабинет</a>
        <a href="/orders" class="btn btn-blue">📋 Мои заказы</a>
    </div>

    <div class="info-box">
        <p style="margin: 0; color: #2c3e50;">
            <strong>ℹ️ Информация:</strong>
            Вы получите SMS-уведомление о статусе заказа.
            Ожидайте звонка оператора для подтверждения заказа в течение 30 минут.
        </p>
    </div>
</div>
</body>
</html>