<?php
require_once __DIR__ . '/../Models/Product.php';
require_once __DIR__ . '/../Models/Cart.php';

class CatalogController
{
    public function __construct()
    {
        session_start();
        $userID = $_SESSION['userId'] ?? null;

        $pdo = new PDO("pgsql:host=db;port=5432;dbname=postgres", "semen", "0000");

        // Получаем товары
        $productModel = new Product($pdo);
        $products = $productModel->getAll();

        // Получаем данные корзины, если пользователь авторизован
        $cartItems = [];
        $cartTotalPrice = 0;
        $cartItemsCount = 0;

        if ($userID) {
            $cartModel = new Cart($pdo);
            $cartItems = $cartModel->getUserCart($userID);

            // Считаем общую стоимость и количество товаров
            foreach ($cartItems as $item) {
                $cartTotalPrice += $item['price'] * $item['amount'];
                $cartItemsCount += $item['amount'];
            }
        }

        // Передаем данные в шаблон
        require_once __DIR__ . '/../Views/catalog.php';
    }
}
