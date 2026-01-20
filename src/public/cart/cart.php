<?php

session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: /login");
    exit;
}

$userId = $_SESSION['userId'];

$pdo = new PDO("pgsql:host=db;port=5432;dbname=postgres", "semen", "0000");

// Получаем товары в корзине пользователя
$stmt = $pdo->prepare('
    SELECT p.*, up.amount, up.id as cart_id 
    FROM user_products up 
    JOIN products p ON up.product_id = p.id 
    WHERE up.user_id = :user_id
');
$stmt->execute(['user_id' => $userId]);
$cartItems = $stmt->fetchAll();

// Считаем общую стоимость
$totalPrice = 0;
foreach ($cartItems as $item) {
    $totalPrice += $item['price'] * $item['amount'];
}

require_once './cart_page.php';
?>
