<?php
// handle_add_product.php

session_start();

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['userId'])) {
    header("Location: /login");
    exit;
}

$userId = $_SESSION['userId'];
$productId = $_POST['product-id'] ?? null;
$amount = $_POST['amount'] ?? null;

// Проверка данных
if (empty($productId) || empty($amount)) {
    echo "Необходимо заполнить все поля";
    exit;
}

// Подключение к базе данных
$pdo = new PDO("pgsql:host=db;port=5432;dbname=postgres", "semen", "0000");

// Вставляем товар в корзину пользователя
$sql = "INSERT INTO user_products (user_id, product_id, amount) VALUES (:user_id, :product_id, :amount)";
$stmt = $pdo->prepare($sql);

try {
    $stmt->execute([
        ':user_id' => $userId,
        ':product_id' => $productId,
        ':amount' => $amount
    ]);

    echo "Товар успешно добавлен!";
    echo "<br><a href='/catalog'>Вернуться в каталог</a>";
} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}
?>
