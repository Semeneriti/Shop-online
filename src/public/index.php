<?php

require_once __DIR__ . '/../Core/Autoloader.php';

\Core\Autoloader::register();

$app = new \Core\App();


$app->get('/registration', [\Controllers\UserController::class, 'getRegistrate']);
$app->post('/registration', [\Controllers\UserController::class, 'registrate']);
$app->get('/login', [\Controllers\UserController::class, 'login']);
$app->post('/login', [\Controllers\UserController::class, 'login']);
$app->get('/logout', [\Controllers\UserController::class, 'logout']);
$app->get('/catalog', [\Controllers\CatalogController::class, 'index']);
$app->get('/add-product', [\Controllers\ProductController::class, 'showForm']);
$app->post('/add-product', [\Controllers\ProductController::class, 'addToCart']);
$app->get('/cart', [\Controllers\CartController::class, 'showCart']);
$app->post('/cart/increase', [\Controllers\CartController::class, 'increaseProduct']);
$app->post('/cart/decrease', [\Controllers\CartController::class, 'decreaseProduct']);
$app->get('/profile', [\Controllers\UserController::class, 'getProfile']);
$app->get('/edit-profile', [\Controllers\UserController::class, 'showEditForm']);
$app->post('/edit-profile', [\Controllers\UserController::class, 'updateProfile']);
$app->get('/checkout', [\Controllers\CartController::class, 'showCheckout']);
$app->post('/checkout', [\Controllers\CartController::class, 'processCheckout']);
$app->post('/cart/remove', [\Controllers\CartController::class, 'removeItem']);
// Добавляем маршруты для отзывов
$app->get('/product', [\Controllers\ProductController::class, 'showProduct']);
$app->post('/product/review', [\Controllers\ProductController::class, 'addReview']);

$app->run();