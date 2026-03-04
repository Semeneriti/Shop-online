<?php

// Подключаем файл автозагрузчика (находится в папке Core)
require_once __DIR__ . '/../Core/Autoloader.php';

// Регистрируем автозагрузчик - теперь PHP будет автоматически подключать нужные классы
\Core\Autoloader::register();

// Создаем объект приложения (роутер)
$app = new \Core\App();

// ============ РЕГИСТРАЦИЯ МАРШРУТОВ ============

// Регистрация пользователя
$app->get('/registration', [\Controllers\UserController::class, 'getRegistrate']);  // Показать форму регистрации
$app->post('/registration', [\Controllers\UserController::class, 'registrate']);    // Обработать форму регистрации

// Вход и выход
$app->get('/login', [\Controllers\UserController::class, 'login']);     // Показать форму входа (и обработать)
$app->post('/login', [\Controllers\UserController::class, 'login']);    // Обработать форму входа
$app->get('/logout', [\Controllers\UserController::class, 'logout']);   // Выйти из системы

// Каталог товаров
$app->get('/catalog', [\Controllers\CatalogController::class, 'index']); // Главная страница каталога

// Добавление товара в корзину (форма и обработка)
$app->get('/add-product', [\Controllers\ProductController::class, 'showForm']);    // Показать форму добавления
$app->post('/add-product', [\Controllers\ProductController::class, 'addToCart']);  // Обработать добавление

// Корзина
$app->get('/cart', [\Controllers\CartController::class, 'showCart']);              // Показать корзину
$app->post('/cart/increase', [\Controllers\CartController::class, 'increaseProduct']); // Увеличить количество
$app->post('/cart/decrease', [\Controllers\CartController::class, 'decreaseProduct']); // Уменьшить количество
$app->post('/cart/remove', [\Controllers\CartController::class, 'removeItem']);    // Удалить товар из корзины

// Профиль пользователя
$app->get('/profile', [\Controllers\UserController::class, 'getProfile']);          // Показать профиль
$app->get('/edit-profile', [\Controllers\UserController::class, 'showEditForm']);   // Показать форму редактирования
$app->post('/edit-profile', [\Controllers\UserController::class, 'updateProfile']); // Обработать редактирование

// Оформление заказа
$app->get('/checkout', [\Controllers\CartController::class, 'showCheckout']);       // Показать форму оформления
$app->post('/checkout', [\Controllers\CartController::class, 'processCheckout']);   // Обработать оформление

// Страница товара и отзывы
$app->get('/product', [\Controllers\ProductController::class, 'showProduct']);      // Показать страницу товара
$app->post('/product/review', [\Controllers\ProductController::class, 'addReview']); // Добавить отзыв

// Запускаем приложение - начинаем обработку текущего запроса
$app->run();