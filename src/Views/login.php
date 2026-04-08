<?php
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход в систему</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #f5f5f5; font-family: Arial, sans-serif; }
        .login-container { background: white; border-radius: 12px; padding: 40px; width: 100%; max-width: 440px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { font-size: 28px; margin-bottom: 8px; text-align: center; }
        .error-message { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 20px; text-align: center; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; }
        input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        input:focus { border-color: #3498db; outline: none; }
        .checkbox-group { margin-bottom: 24px; }
        .checkbox-label { display: flex; align-items: center; cursor: pointer; }
        .checkbox-input { width: 16px; height: 16px; margin-right: 8px; }
        button { width: 100%; padding: 12px; background: #3498db; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
        button:hover { background: #2980b9; }
        .signup-text { text-align: center; margin-top: 20px; font-size: 14px; }
        .signup-link { color: #3498db; text-decoration: none; }
    </style>
</head>
<body>
<div class="login-container">
    <h1>Вход в аккаунт</h1>

    <?php if (isset($errors['general'])): ?>
        <div class="error-message"><?= htmlspecialchars($errors['general']) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            <?php if (isset($errors['email'])): ?>
                <div style="color: red; font-size: 12px;"><?= $errors['email'] ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="password">Пароль</label>
            <input type="password" id="password" name="password" required>
            <?php if (isset($errors['password'])): ?>
                <div style="color: red; font-size: 12px;"><?= $errors['password'] ?></div>
            <?php endif; ?>
        </div>

        <div class="checkbox-group">
            <label class="checkbox-label">
                <input type="checkbox" name="remember" class="checkbox-input">
                <span>Запомнить меня</span>
            </label>
        </div>

        <button type="submit">Войти</button>
    </form>

    <div class="signup-text">
        Нет аккаунта? <a href="/registration" class="signup-link">Зарегистрироваться</a>
    </div>
</div>
</body>
</html>