<?php


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

        $cartModel = new Cart();

        $cartItems = $cartModel->getUserCart($userId);

        $totalPrice = 0;
        foreach ($cartItems as $item) {
            $totalPrice += $item['price'] * $item['amount'];
        }

        require_once __DIR__ . '/../Views/cart.php';
    }
}