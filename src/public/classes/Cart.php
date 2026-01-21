<?php

class Cart
{
    public function __construct()
    {
        if (!isset($_SESSION['userId'])) {
            header("Location: /login");
            exit;
        }

        $this->userId = $_SESSION['userId'];

        $this->pdo = new PDO("pgsql:host=db;port=5432;dbname=postgres", "semen", "0000");

        $stmt = $this->pdo->prepare('
            SELECT p.*, up.amount, up.id as cart_id 
            FROM user_products up 
            JOIN products p ON up.product_id = p.id 
            WHERE up.user_id = :user_id
        ');
        $stmt->execute(['user_id' => $this->userId]);
        $this->cartItems = $stmt->fetchAll();

        $this->totalPrice = 0;
        foreach ($this->cartItems as $item) {
            $this->totalPrice += $item['price'] * $item['amount'];
        }

        require_once './cart_page.php';
    }
}

new Cart();