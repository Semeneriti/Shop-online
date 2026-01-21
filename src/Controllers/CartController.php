<?php
require_once __DIR__ . '/../Models/Cart.php';

class CartController
{
    public function __construct()
    {
        session_start();

        if (!isset($_SESSION['userId'])) {
            header("Location: /login");
            exit;
        }

        $userId = $_SESSION['userId'];

        $pdo = new PDO("pgsql:host=db;port=5432;dbname=postgres", "semen", "0000");
        $cartModel = new Cart($pdo);

        $cartItems = $cartModel->getUserCart($userId);

        $totalPrice = 0;
        foreach ($cartItems as $item) {
            $totalPrice += $item['price'] * $item['amount'];
        }

        require_once __DIR__ . '/../Views/cart.php';
    }
}