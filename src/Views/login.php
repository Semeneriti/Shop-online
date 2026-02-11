<?php
/** @var array $errors */
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в систему</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f5f5f5;
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        .login-wrapper {
            width: 100%;
            max-width: 440px;
        }

        .login-container {
            background: #ffffff;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border: 1px solid #e5e7eb;
        }

        .login-header {
            margin-bottom: 32px;
            text-align: center;
        }

        .brand-title {
            font-size: 28px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .brand-subtitle {
            font-size: 15px;
            color: #666;
            font-weight: 400;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
            font-size: 14px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .label-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #555;
        }

        .forgot-link {
            font-size: 13px;
            color: #3498db;
            text-decoration: none;
            transition: color 0.3s;
        }

        .forgot-link:hover {
            color: #2980b9;
            text-decoration: underline;
        }

        .form-input {
            width: 100%;
            padding: 12px;
            font-size: 14px;
            color: #333;
            background: #ffffff;
            border: 1px solid #ddd;
            border-radius: 4px;
            transition: border-color 0.3s;
            outline: none;
        }

        .form-input::placeholder {
            color: #999;
        }

        .form-input:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52,152,219,0.1);
        }

        .field-error {
            color: #e74c3c;
            font-size: 12px;
            margin-top: 5px;
        }

        .checkbox-group {
            margin-bottom: 24px;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        .checkbox-input {
            width: 16px;
            height: 16px;
            margin-right: 8px;
            cursor: pointer;
            accent-color: #3498db;
        }

        .checkbox-text {
            font-size: 14px;
            color: #666;
            user-select: none;
        }

        .submit-btn {
            width: 100%;
            padding: 12px;
            background: #3498db;
            color: #ffffff;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .submit-btn:hover {
            background: #2980b9;
        }

        .divider {
            position: relative;
            text-align: center;
            margin: 32px 0;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #dee2e6;
        }

        .divider-text {
            position: relative;
            display: inline-block;
            padding: 0 16px;
            background: #ffffff;
            font-size: 13px;
            color: #7f8c8d;
        }

        .social-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 24px;
        }

        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px;
            background: #ffffff;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            color: #555;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .social-btn:hover {
            background: #f8f9fa;
        }

        .social-icon {
            width: 18px;
            height: 18px;
        }

        .signup-text {
            text-align: center;
            font-size: 14px;
            color: #7f8c8d;
        }

        .signup-link {
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .signup-link:hover {
            color: #2980b9;
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }

            .brand-title {
                font-size: 24px;
            }

            .social-buttons {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="login-wrapper">
    <div class="login-container">
        <div class="login-header">
            <h1 class="brand-title">С возвращением!</h1>
            <p class="brand-subtitle">Войдите в свой аккаунт</p>
        </div>

        <?php if (isset($errors['general'])): ?>
            <div class="error-message"><?= htmlspecialchars($errors['general']) ?></div>
        <?php endif; ?>

        <form action="/login" method="POST" class="login-form">
            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-input"
                       placeholder="name@company.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       required>
                <?php if (isset($errors['email'])): ?>
                    <div class="field-error"><?= $errors['email'] ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <div class="label-row">
                    <label for="password" class="form-label">Пароль</label>
                    <a href="#" class="forgot-link">Забыли пароль?</a>
                </div>
                <input type="password" id="password" name="password" class="form-input"
                       placeholder="Введите пароль" required>
                <?php if (isset($errors['password'])): ?>
                    <div class="field-error"><?= $errors['password'] ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group checkbox-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember" class="checkbox-input">
                    <span class="checkbox-text">Запомнить меня</span>
                </label>
            </div>

            <button type="submit" class="submit-btn">
                <span class="btn-text">Войти</span>
            </button>
        </form>

        <div class="divider">
            <span class="divider-text">или войти через</span>
        </div>

        <div class="social-buttons">
            <button class="social-btn" onclick="alert('Google авторизация в разработке')">
                <svg class="social-icon" viewBox="0 0 24 24">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                Google
            </button>
            <button class="social-btn" onclick="alert('GitHub авторизация в разработке')">
                <svg class="social-icon" viewBox="0 0 24 24" fill="#000">
                    <path d="M12 2C6.477 2 2 6.477 2 12c0 4.42 2.865 8.17 6.839 9.49.5.092.682-.217.682-.482 0-.237-.008-.866-.013-1.7-2.782.603-3.369-1.34-3.369-1.34-.454-1.156-1.11-1.463-1.11-1.463-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.831.092-.646.35-1.086.636-1.336-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.578 9.578 0 0112 6.836c.85.004 1.705.114 2.504.336 1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.203 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.578.688.48C19.138 20.167 22 16.418 22 12c0-5.523-4.477-10-10-10z"/>
                </svg>
                GitHub
            </button>
        </div>

        <p class="signup-text">
            Нет аккаунта? <a href="/registration" class="signup-link">Зарегистрироваться</a>
        </p>
    </div>
</div>
</body>
</html>