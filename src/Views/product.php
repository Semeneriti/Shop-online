<?php
/** @var \Models\Product $product */
/** @var array $reviews */
/** @var string|null $successMessage */
/** @var string|null $errorMessage */
/** @var \Services\AuthService $auth */
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product->getName()) ?> - Каталог</title>
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

        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .product-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
        }

        .product-image {
            text-align: center;
            margin-bottom: 20px;
        }

        .product-image img {
            max-width: 300px;
            max-height: 300px;
            object-fit: contain;
            border-radius: 8px;
        }

        .product-name {
            font-size: 28px;
            margin-bottom: 15px;
            color: #333;
        }

        .product-description {
            font-size: 16px;
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .product-meta {
            display: flex;
            gap: 30px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .product-price {
            font-size: 24px;
            font-weight: bold;
            color: #27ae60;
        }

        .product-stock {
            font-size: 16px;
            color: #e67e22;
        }

        .product-rating {
            font-size: 18px;
            color: #f39c12;
        }

        .btn {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
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

        .reviews-section {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 30px;
        }

        .reviews-title {
            font-size: 22px;
            margin-bottom: 25px;
            color: #333;
        }

        .review-form {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid #dee2e6;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: bold;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 2px rgba(52,152,219,0.1);
        }

        select.form-control {
            width: auto;
            min-width: 200px;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        .review-item {
            border-bottom: 1px solid #eee;
            padding: 20px 0;
        }

        .review-item:last-child {
            border-bottom: none;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .review-author {
            font-weight: bold;
            color: #2c3e50;
        }

        .review-rating {
            color: #f39c12;
            font-size: 18px;
        }

        .review-date {
            color: #999;
            font-size: 13px;
        }

        .review-text {
            color: #666;
            line-height: 1.6;
            margin-top: 10px;
        }

        .no-reviews {
            text-align: center;
            padding: 40px;
            color: #999;
            font-style: italic;
            background-color: #f8f9fa;
            border-radius: 8px;
        }

        .login-prompt {
            text-align: center;
            padding: 30px;
            background-color: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 30px;
            border: 1px solid #dee2e6;
        }

        .login-prompt p {
            color: #666;
            margin-bottom: 15px;
        }

        .login-prompt a {
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
        }

        .login-prompt a:hover {
            text-decoration: underline;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #7f8c8d;
            text-decoration: none;
        }

        .back-link:hover {
            color: #3498db;
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .product-meta {
                flex-direction: column;
                gap: 15px;
            }

            .review-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1><?= htmlspecialchars($product->getName()) ?></h1>
        <div class="nav">
            <a href="/catalog">📚 Каталог</a>
            <a href="/cart">🛒 Корзина</a>
            <?php if (isset($auth) && !$auth->isGuest()): ?>
                <a href="/profile">👤 Профиль</a>
                <a href="/logout">🚪 Выход</a>
            <?php else: ?>
                <a href="/login">🔐 Вход</a>
                <a href="/registration">📝 Регистрация</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($successMessage): ?>
        <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        <div class="alert alert-error"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <div class="product-card">
        <?php if ($product->getImageUrl()): ?>
            <div class="product-image">
                <img src="<?= htmlspecialchars($product->getImageUrl()) ?>"
                     alt="<?= htmlspecialchars($product->getName()) ?>"
                     onerror="this.src='https://via.placeholder.com/300x300?text=No+Image'">
            </div>
        <?php endif; ?>

        <h2 class="product-name"><?= htmlspecialchars($product->getName()) ?></h2>
        <p class="product-description"><?= nl2br(htmlspecialchars($product->getDescription())) ?></p>

        <div class="product-meta">
            <div class="product-price">💰 <?= number_format($product->getPrice(), 2, '.', ' ') ?> ₽</div>
            <div class="product-stock">📦 В наличии: <?= $product->getStock() ?> шт.</div>
            <div class="product-rating">
                ⭐ <?= $product->getAverageRating() ?> (<?= count($reviews) ?> <?= count($reviews) == 1 ? 'отзыв' : (count($reviews) > 1 && count($reviews) < 5 ? 'отзыва' : 'отзывов') ?>)
            </div>
        </div>

        <a href="/catalog" class="btn">← Назад в каталог</a>
    </div>

    <div class="reviews-section">
        <!-- ========== БЛОК С ОТЗЫВАМИ ========== -->
        <h3 class="reviews-title">📝 Отзывы покупателей</h3>

        <?php if (isset($auth) && !$auth->isGuest()): ?>
            <div class="review-form">
                <h4 style="margin-bottom: 20px; color: #333;">Оставить отзыв</h4>
                <form method="POST" action="/product/review">
                    <input type="hidden" name="product_id" value="<?= $product->getId() ?>">

                    <div class="form-group">
                        <label for="rating">Оценка товара:</label>
                        <select name="rating" id="rating" class="form-control" required>
                            <option value="">-- Выберите оценку --</option>
                            <option value="5">5 ★★★★★ - Отлично</option>
                            <option value="4">4 ★★★★☆ - Хорошо</option>
                            <option value="3">3 ★★★☆☆ - Нормально</option>
                            <option value="2">2 ★★☆☆☆ - Плохо</option>
                            <option value="1">1 ★☆☆☆☆ - Ужасно</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="text">Ваш отзыв:</label>
                        <textarea name="text" id="text" class="form-control"
                                  placeholder="Расскажите о своем опыте использования товара..." required></textarea>
                    </div>

                    <button type="submit" class="btn btn-green">✉️ Отправить отзыв</button>
                </form>
            </div>
        <?php else: ?>
            <div class="login-prompt">
                <p style="font-size: 16px;">Чтобы оставить отзыв, пожалуйста, войдите в систему</p>
                <p>
                    <a href="/login" class="btn" style="margin-right: 10px;">🔐 Войти</a>
                    <a href="/registration" class="btn btn-green">📝 Зарегистрироваться</a>
                </p>
            </div>
        <?php endif; ?>

        <?php if (empty($reviews)): ?>
            <div class="no-reviews">
                <p style="font-size: 16px; margin-bottom: 10px;">У этого товара пока нет отзывов</p>
                <p style="color: #999;">Будьте первым, кто поделится своим мнением!</p>
            </div>
        <?php else: ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review-item">
                    <div class="review-header">
                        <span class="review-author">👤 <?= htmlspecialchars($review->getUserName()) ?></span>
                        <span class="review-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?= $i <= $review->getRating() ? '★' : '☆' ?>
                                <?php endfor; ?>
                            </span>
                        <span class="review-date">📅 <?= date('d.m.Y H:i', strtotime($review->getCreatedAt())) ?></span>
                    </div>
                    <div class="review-text">
                        <?= nl2br(htmlspecialchars($review->getText())) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>