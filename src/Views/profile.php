<!DOCTYPE html>
<html>
<head>
    <title>Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .profile-info {
            margin-bottom: 30px;
        }
        .profile-info h2 {
            color: #333;
        }
    </style>
</head>
<body>
<h1>User Profile</h1>

<div class="profile-info">
    <h2>Информация о пользователе:</h2>
    <?php if ($user): ?>
        <p><strong>Имя:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <a href="/edit-profile">Редактировать профиль</a>
    <?php else: ?>
        <p>Пользователь не найден</p>
    <?php endif; ?>
</div>

<h2>История заказов</h2>
<?php if (!empty($orders)): ?>
    <table border="1" cellpadding="10" cellspacing="0">
        <tr>
            <th>Номер заказа</th>
            <th>Дата</th>
            <th>Адрес</th>
            <th>Телефон</th>
            <th>Товаров</th>
            <th>Сумма</th>
        </tr>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td>#<?php echo $order['id']; ?></td>
                <td><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></td>
                <td><?php echo htmlspecialchars($order['address']); ?></td>
                <td><?php echo htmlspecialchars($order['phone']); ?></td>
                <td><?php echo $order['items_count']; ?> шт.</td>
                <td><?php echo $order['total_price']; ?> ₽</td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <p>У вас еще нет заказов</p>
<?php endif; ?>

<br>
<a href="/catalog">Вернуться в каталог</a>
</body>
</html>