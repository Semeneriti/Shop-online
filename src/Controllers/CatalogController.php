<?php


class CatalogController
{
    public function __construct()
    {
        session_start();
        $userID = $_SESSION['userId'] ?? null;

        $productModel = new Product();
        $products = $productModel->getAll();

        $cartItems = [];
        $cartTotalPrice = 0;
        $cartItemsCount = 0;

        if ($userID) {
            $cartModel = new Cart();
            $cartItems = $cartModel->getUserCart($userID);

            foreach ($cartItems as $item) {
                $cartTotalPrice += $item['price'] * $item['amount'];
                $cartItemsCount += $item['amount'];
            }
        }

        require_once __DIR__ . '/../Views/catalog.php';
    }
}
