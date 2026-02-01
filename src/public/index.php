<?php

spl_autoload_register(function (string $className) {
    $prefixes = [
        'Controllers\\' => '../Controllers/',
        'Core\\' => '../Core/',
        'Models\\' => '../Models/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (strpos($className, $prefix) === 0) {
            $relativeClass = substr($className, strlen($prefix));
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

            if (file_exists($file)) {
                require_once $file;
                return true;
            }
        }
    }

    return false;
});

use Core\App;
use Controllers\UserController;
use Controllers\CatalogController;
use Controllers\ProductController;
use Controllers\CartController;
use Controllers\OrderController;

// Создаем экземпляр приложения
$app = new App();

// Регистрируем маршруты
$app->addRoute('/registration', 'GET', UserController::class, 'getRegistrate');
$app->addRoute('/registration', 'POST', UserController::class, 'registrate');
$app->addRoute('/login', 'GET', UserController::class, 'login');
$app->addRoute('/login', 'POST', UserController::class, 'login');
$app->addRoute('/logout', 'GET', UserController::class, 'logout');
$app->addRoute('/catalog', 'GET', CatalogController::class, '__construct');
$app->addRoute('/add-product', 'GET', ProductController::class, 'showForm');
$app->addRoute('/add-product', 'POST', ProductController::class, 'addToCart');
$app->addRoute('/cart', 'GET', CartController::class, '__construct');
$app->addRoute('/profile', 'GET', UserController::class, 'getProfile');
$app->addRoute('/edit-profile', 'GET', UserController::class, 'showEditForm');
$app->addRoute('/edit-profile', 'POST', UserController::class, 'updateProfile');
$app->addRoute('/checkout', 'GET', CartController::class, 'showCheckout');
$app->addRoute('/checkout', 'POST', CartController::class, 'processCheckout');
$app->addRoute('/user-orders', 'GET', OrderController::class, 'getAllOrders');


$app->run();