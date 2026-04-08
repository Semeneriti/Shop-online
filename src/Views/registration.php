<?php
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #f5f5f5; font-family: Arial, sans-serif; }
        .container { background: white; border-radius: 12px; padding: 40px; width: 100%; max-width: 500px; }
        h2 { text-align: center; margin-bottom: 25px; }
        .error-box { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 20px; }
        .input-container { display: flex; margin-bottom: 15px; }
        .icon { padding: 12px; background: #3498db; color: white; min-width: 50px; text-align: center; border-radius: 4px 0 0 4px; }
        .input-field { width: 100%; padding: 12px; border: 1px solid #ddd; border-left: none; border-radius: 0 4px 4px 0; }
        .btn { width: 100%; padding: 12px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; margin-top: 10px; }
        .btn:hover { background: #2980b9; }
        .login-link { text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Регистрация</h2>

    <?php if (!empty($errors)): ?>
        <div class="error-box">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars(is_array($error) ? implode(', ', $error) : $error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="input-container">
            <span class="icon">👤</span>
            <input class="input-field" type="text" placeholder="Имя" name="name" required>
        </div>

        <div class="input-container">
            <span class="icon">📧</span>
            <input class="input-field" type="email" placeholder="Email" name="email" required>
        </div>

        <div class="input-container">
            <span class="icon">🔑</span>
            <input class="input-field" type="password" placeholder="Пароль" name="password" required>
        </div>

        <div class="input-container">
            <span class="icon">🔑</span>
            <input class="input-field" type="password" placeholder="Повторите пароль" name="passwordRepeat" required>
        </div>

        <button type="submit" class="btn">Зарегистрироваться</button>
    </form>

    <div class="login-link">
        Уже есть аккаунт? <a href="/login">Войти</a>
    </div>
</div>
</body>
</html>
