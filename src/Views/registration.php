<?php
/** @var array $errors */
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
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
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            max-width: 500px;
            width: 100%;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 30px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
            font-size: 24px;
        }

        .error-box {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }

        .error-box p {
            margin: 5px 0;
        }

        .input-container {
            display: flex;
            width: 100%;
            margin-bottom: 15px;
        }

        .icon {
            padding: 12px;
            background: #3498db;
            color: white;
            min-width: 50px;
            text-align: center;
            border-radius: 4px 0 0 4px;
        }

        .input-field {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-left: none;
            border-radius: 0 4px 4px 0;
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s;
        }

        .input-field:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52,152,219,0.1);
        }

        .btn {
            background-color: #3498db;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            transition: background-color 0.3s;
            margin-top: 10px;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }

        .login-link a {
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .field-error {
            color: #e74c3c;
            font-size: 12px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>📝 Регистрация</h2>

    <?php if (!empty($errors)): ?>
        <div class="error-box">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars(is_array($error) ? implode(', ', $error) : $error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="/registration" method="POST">
        <div class="input-container">
            <i class="fa fa-user icon">👤</i>
            <input class="input-field" type="text" placeholder="Имя пользователя" name="name" required>
        </div>

        <div class="input-container">
            <i class="fa fa-envelope icon">📧</i>
            <input class="input-field" type="email" placeholder="Email" name="email" required>
        </div>

        <div class="input-container">
            <i class="fa fa-key icon">🔑</i>
            <input class="input-field" type="password" placeholder="Пароль" name="password" required>
        </div>

        <div class="input-container">
            <i class="fa fa-key icon">🔑</i>
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