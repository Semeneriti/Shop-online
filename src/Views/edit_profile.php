<?php
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование профиля</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 500px; margin: 0 auto; background: white; border-radius: 8px; padding: 30px; }
        h2 { text-align: center; margin-bottom: 25px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; }
        .form-control { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; }
        .btn { width: 100%; padding: 12px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer; margin-top: 10px; }
        .btn-secondary { background: #95a5a6; margin-top: 10px; text-align: center; display: inline-block; text-decoration: none; }
        .error-message { color: #e74c3c; font-size: 13px; margin-top: 5px; }
        .success-message { background: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 20px; }
        .info-text { font-size: 13px; color: #7f8c8d; margin-top: 5px; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #7f8c8d; }
    </style>
</head>
<body>
<div class="container">
    <h2>Редактирование профиля</h2>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="success-message"><?= htmlspecialchars($_SESSION['success_message']) ?><?php unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 20px;">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars(is_array($error) ? implode(', ', $error) : $error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="name">Имя</label>
            <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($userData['name'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($userData['email'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Новый пароль (оставьте пустым, если не хотите менять)</label>
            <input type="password" id="password" name="password" class="form-control">
            <div class="info-text">Минимум 6 символов</div>
        </div>

        <div class="form-group">
            <label for="password_confirm">Подтверждение нового пароля</label>
            <input type="password" id="password_confirm" name="password_confirm" class="form-control">
        </div>

        <button type="submit" class="btn">Сохранить изменения</button>
        <a href="/profile" class="btn btn-secondary" style="display: block; text-align: center;">Отмена</a>
    </form>

    <a href="/profile" class="back-link">Вернуться в профиль</a>
</div>
</body>
</html>