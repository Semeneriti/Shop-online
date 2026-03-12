<?php
/** @var array $user */
/** @var array $orders */
/** @var array $cartItems */
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет</title>
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
            max-width: 1000px;
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

        .profile-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
        }

        .profile-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .profile-header h2 {
            color: #333;
            font-size: 22px;
        }

        .profile-info {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            border-left: 4px solid #3498db;
        }

        .profile-info p {
            margin-bottom: 10px;
            color: #666;
        }

        .profile-info strong {
            color: #333;
            min-width: 100px;
            display: inline-block;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        .btn-green {
            background-color: #27ae60;
        }

        .btn-green:hover {
            background-color: #229954;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .orders-table th {
            background-color: #f8f9fa;
            color: #555;
            font-weight: 600;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
        }

        .orders-table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
            color: #666;
        }

        .orders-table tr:hover {
            background-color: #f8f9fa;
        }

        .empty-orders {
            text-align: center;
            padding: 40px;
            color: #999;
            font-style: italic;
            background-color: #f8f9fa;
            border-radius: 8px;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>👤 Личный кабинет</h1>
        <div class="nav">
            <a href="/catalog">📚 Каталог</a>
            <a href="/cart">🛒 Корзина</a>
            <a href="/logout">🚪 Выход</a>
        </div>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="success-message">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
            <?php unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <div class="profile-card">
        <div class="profile-header">
            <h2>📋 Информация о пользователе</h2>
            <a href="/edit-profile" class="btn">✏️ Редактировать</a>
        </div>

        <?php if (!empty($user)): ?>
            <div class="profile-info">
                <p><strong>Имя:</strong> <?= htmlspecialchars($user['name'] ?? '') ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email'] ?? '') ?></p>
                <p><strong>Дата регистрации:</strong> <?= isset($user['created_at']) ? date('d.m.Y', strtotime($user['created_at'])) : 'Не указана' ?></p>
            </div>
        <?php else: ?>
            <p>Пользователь не найден</p>
        <?php endif; ?>
    </div>

    <div class="profile-card">
        <h2>📦 История заказов</h2>

        <?php if (!empty($orders)): ?>
            <table class="orders-table">
                <thead>
                <tr>
                    <th>Номер заказа</th>
                    <th>Дата</th>
                    <th>Адрес</th>
                    <th>Телефон</th>
                    <th>Товаров</th>
                    <th>Сумма</th>
                    <th>Статус</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><strong>#<?= $order['id'] ?? '' ?></strong></td>
                        <td><?= isset($order['created_at']) ? date('d.m.Y H:i', strtotime($order['created_at'])) : '' ?></td>
                        <td><?= htmlspecialchars($order['address'] ?? '') ?></td>
                        <td><?= htmlspecialchars($order['phone'] ?? '') ?></td>
                        <td><?= $order['items_count'] ?? 0 ?> шт. (всего <?= $order['total_items'] ?? 0 ?> шт.)</td>
                        <td><strong><?= number_format($order['total_price'] ?? 0, 2, '.', ' ') ?> ₽</strong></td>
                        <td><span style="color: #27ae60;"><?= $order['status'] ?? 'новый' ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-orders">
                <p style="font-size: 16px; margin-bottom: 10px;">У вас пока нет заказов</p>
                <p style="color: #999;">Перейдите в <a href="/catalog" style="color: #3498db;">каталог</a>, чтобы сделать первый заказ</p>
            </div>
        <?php endif; ?>
    </div>

    <div style="text-align: center; margin-top: 20px;">
        <a href="/catalog" class="btn">🔄 Продолжить покупки</a>
    </div>
</div>
</body>
</html>