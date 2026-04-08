<?php
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($product->getName()) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        .header { background: white; border-radius: 8px; padding: 20px; margin-bottom: 20px; display: flex; justify-content: space-between; }
        .product-card { background: white; border-radius: 8px; padding: 30px; margin-bottom: 30px; }
        .product-image { text-align: center; margin-bottom: 20px; }
        .product-image img { max-width: 300px; max-height: 300px; object-fit: contain; }
        .product-name { font-size: 28px; margin-bottom: 15px; }
        .product-description { font-size: 16px; color: #666; line-height: 1.6; margin-bottom: 20px; }
        .product-price { font-size: 24px; font-weight: bold; color: #27ae60; margin-bottom: 10px; }
        .product-stock { color: #e67e22; margin-bottom: 10px; }
        .product-rating { color: #f39c12; margin-bottom: 20px; }
        .btn { display: inline-block; padding: 12px 24px; background: #3498db; color: white; text-decoration: none; border-radius: 4px; }
        .reviews-section { background: white; border-radius: 8px; padding: 30px; }
        .review-form { background: #f8f9fa; padding: 25px; border-radius: 8px; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; }
        .form-control { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; }
        .review-item { border-bottom: 1px solid #eee; padding: 20px 0; }
        .review-author { font-weight: bold; }
        .review-rating { color: #f39c12; }
        .review-date { color: #999; font-size: 13px; }
        .review-text { margin-top: 10px; color: #666; }
        .no-reviews { text-align: center; padding: 40px; color: #999; }
        .login-prompt { text-align: center; padding: 30px; background: #f8f9fa; border-radius: 8px; margin-bottom: 30px; }
        .alert { padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1><?= htmlspecialchars($product->getName()) ?></h1>
        <div>
            <a href="/catalog">Каталог</a> | <a href="/cart">Корзина</a>
            <?php if (isset($auth) && !$auth->isGuest()): ?>
                | <a href="/profile">Профиль</a> | <a href="/logout">Выход</a>
            <?php else: ?>
                | <a href="/login">Вход</a> | <a href="/registration">Регистрация</a>
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
                <img src="<?= htmlspecialchars($product->getImageUrl()) ?>" alt="<?= htmlspecialchars($product->getName()) ?>">
            </div>
        <?php endif; ?>
        <div class="product-name"><?= htmlspecialchars($product->getName()) ?></div>
        <div class="product-description"><?= nl2br(htmlspecialchars($product->getDescription())) ?></div>
        <div class="product-price"><?= number_format($product->getPrice(), 2, '.', ' ') ?> ₽</div>
        <div class="product-stock">В наличии: <?= $product->getStock() ?> шт.</div>
        <div class="product-rating">⭐ <?= $product->getAverageRating() ?> (<?= count($reviews) ?> отзывов)</div>
        <a href="/catalog" class="btn">Назад в каталог</a>
    </div>

    <div class="reviews-section">
        <h3>Отзывы покупателей</h3>

        <?php if (isset($auth) && !$auth->isGuest()): ?>
            <div class="review-form">
                <h4>Оставить отзыв</h4>
                <form method="POST" action="/product/review">
                    <input type="hidden" name="product_id" value="<?= $product->getId() ?>">
                    <div class="form-group">
                        <label for="rating">Оценка:</label>
                        <select name="rating" id="rating" class="form-control" required>
                            <option value="">-- Выберите --</option>
                            <option value="5">5 - Отлично</option>
                            <option value="4">4 - Хорошо</option>
                            <option value="3">3 - Нормально</option>
                            <option value="2">2 - Плохо</option>
                            <option value="1">1 - Ужасно</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="text">Отзыв:</label>
                        <textarea name="text" id="text" class="form-control" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn">Отправить отзыв</button>
                </form>
            </div>
        <?php else: ?>
            <div class="login-prompt">
                <p>Чтобы оставить отзыв, пожалуйста, <a href="/login">войдите</a> в систему</p>
            </div>
        <?php endif; ?>

        <?php if (empty($reviews)): ?>
            <div class="no-reviews">У этого товара пока нет отзывов</div>
        <?php else: ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review-item">
                    <div class="review-author"><?= htmlspecialchars($review->getUserName()) ?></div>
                    <div class="review-rating">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?= $i <= $review->getRating() ? '★' : '☆' ?>
                        <?php endfor; ?>
                    </div>
                    <div class="review-date"><?= date('d.m.Y H:i', strtotime($review->getCreatedAt())) ?></div>
                    <div class="review-text"><?= nl2br(htmlspecialchars($review->getText())) ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
