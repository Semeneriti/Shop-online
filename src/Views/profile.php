<?php
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Личный кабинет</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        .header { background: white; border-radius: 8px; padding: 20px; margin-bottom: 20px; display: flex; justify-content: space-between; }
        .profile-card { background: white; border-radius: 8px; padding: 30px; margin-bottom: 30px; }
        .profile-info { background: #f8f9fa; padding: 20px; border-left: 4px solid #3498db; }
        .profile-info p { margin-bottom: 10px; }
        .btn { display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 4px; }
        .orders-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .orders-table th { background: #f8f9fa; padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6; }
        .orders-table td { padding: 12px; border-bottom: 1px solid #dee2e6; }
        .empty-orders { text-align: center; padding: 40px; color: #999; }
        .success-message { background: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 20px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Личный кабинет</h1>
        <div>
            <a href="/catalog">Каталог</a> | <a href="/cart">Корзина</a> | <a href="/logout">Выход</a>
        </div>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="success-message"><?= htmlspecialchars($_SESSION['success_message']) ?><?php unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>

    <div class="profile-card">
        <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
            <h2>Информация о пользователе</h2>
            <a href="/edit-profile" class="btn">Редактировать</a>
        </div>
        <div class="profile-info">
            <p><strong>Имя:</strong> <?= htmlspecialchars($userData['name'] ?? '') ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($userData['email'] ?? '') ?></p>
            <p><strong>Дата регистрации:</strong> <?= isset($userData['created_at']) ? date('d.m.Y', strtotime($userData['created_at'])) : '' ?></p>
        </div>
    </div>

    <div class="profile-card">
        <h2>История заказов</h2>
        <?php if (!empty($orders)): ?>
            <table class="orders-table">
                <thead>
                <tr><th>№ заказа</th><th>Дата</th><th>Адрес</th><th>Телефон</th><th>Сумма</th><th>Статус</th></tr>
                </thead>
                <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?= $order['id'] ?></td>
                        <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                        <td><?= htmlspecialchars($order['address']) ?></td>
                        <td><?= htmlspecialchars($order['phone']) ?></td>
                        <td><?= number_format($order['total_price'], 2, '.', ' ') ?> ₽</td>
                        <td><?= $order['status'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-orders"><p>У вас пока нет заказов</p><a href="/catalog">Перейти в каталог</a></div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
