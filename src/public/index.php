<?php

require_once __DIR__ . '/../Core/Autoloader.php';

\Core\Autoloader::register();

$app = new \Core\App();

// Регистрация пользователя
$app->get('/registration', [\Controllers\UserController::class, 'getRegistrate']);
$app->post('/registration', [\Controllers\UserController::class, 'registrate']);

// Вход и выход
$app->get('/login', [\Controllers\UserController::class, 'login']);
$app->post('/login', [\Controllers\UserController::class, 'login']);
$app->get('/logout', [\Controllers\UserController::class, 'logout']);

// Каталог товаров
$app->get('/catalog', [\Controllers\CatalogController::class, 'index']);

// Добавление товара в корзину (обычный)
$app->get('/add-product', [\Controllers\ProductController::class, 'showForm']);
$app->post('/add-product', [\Controllers\ProductController::class, 'addToCart']);

// ========== AJAX МАРШРУТЫ ДЛЯ КОРЗИНЫ ==========
$app->post('/api/cart/add', [\Controllers\ProductController::class, 'ajaxAddToCart']);
$app->post('/api/cart/increase', [\Controllers\CartController::class, 'ajaxIncreaseProduct']);
$app->post('/api/cart/decrease', [\Controllers\CartController::class, 'ajaxDecreaseProduct']);
$app->post('/api/cart/remove', [\Controllers\CartController::class, 'ajaxRemoveItem']);
$app->post('/api/cart/clear', [\Controllers\CartController::class, 'ajaxClearCart']);

// Корзина (обычные)
$app->get('/cart', [\Controllers\CartController::class, 'showCart']);
$app->post('/cart/increase', [\Controllers\CartController::class, 'increaseProduct']);
$app->post('/cart/decrease', [\Controllers\CartController::class, 'decreaseProduct']);
$app->post('/cart/remove', [\Controllers\CartController::class, 'removeItem']);
$app->post('/cart/clear', [\Controllers\CartController::class, 'clearCart']);

// Профиль пользователя
$app->get('/profile', [\Controllers\UserController::class, 'getProfile']);
$app->get('/edit-profile', [\Controllers\UserController::class, 'showEditForm']);
$app->post('/edit-profile', [\Controllers\UserController::class, 'updateProfile']);

// Оформление заказа
$app->get('/checkout', [\Controllers\CartController::class, 'showCheckout']);
$app->post('/checkout', [\Controllers\CartController::class, 'processCheckout']);

// Страница товара и отзывы
$app->get('/product', [\Controllers\ProductController::class, 'showProduct']);
$app->post('/product/review', [\Controllers\ProductController::class, 'addReview']);

// Страница ошибки 500
$app->get('/500', function() {
    http_response_code(500);
    require_once __DIR__ . '/../public/500.php';
});

$app->run();