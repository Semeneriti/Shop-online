<?php

require_once __DIR__ . '/../Models/Cart.php';

session_start();

class ProductController
{
    public function addToCart()
    {
        if (!isset($_SESSION['userId'])) {
            header("Location: /login");
            exit;
        }

        $userId = $_SESSION['userId'];
        $productId = $_POST['product-id'] ?? null;
        $amount = $_POST['amount'] ?? null;

        if (empty($productId) || empty($amount)) {
            echo "Необходимо заполнить все поля";
            exit;
        }

        $pdo = new PDO("pgsql:host=db;port=5432;dbname=postgres", "semen", "0000");
        $cartModel = new Cart($pdo);

        try {
            $cartModel->addToCart($userId, $productId, $amount);

            echo "Товар успешно добавлен!";
            echo "<br><a href='/catalog'>Вернуться в каталог</a>";
        } catch (PDOException $e) {
            echo "Ошибка: " . $e->getMessage();
        }
    }
}