<?php
namespace Controllers;

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

        $cartModel = new \Models\Cart();

        $cartItems = $cartModel->getUserCart($userId);

        $totalPrice = 0;
        foreach ($cartItems as $item) {
            $totalPrice += $item['price'] * $item['amount'];
        }

        require_once __DIR__ . '/../Views/cart.php';
    }

    public function showCheckout()
    {
        session_start();

        if (!isset($_SESSION['userId'])) {
            header("Location: /login");
            exit;
        }

        $userId = $_SESSION['userId'];

        $cartModel = new \Models\Cart();
        $cartItems = $cartModel->getUserCart($userId);

        if (empty($cartItems)) {
            header("Location: /cart");
            exit;
        }

        $totalPrice = 0;
        foreach ($cartItems as $item) {
            $totalPrice += $item['price'] * $item['amount'];
        }

        require_once __DIR__ . '/../Views/checkout.php';
    }

    public function processCheckout()
    {
        session_start();

        if (!isset($_SESSION['userId'])) {
            header("Location: /login");
            exit;
        }

        $userId = $_SESSION['userId'];
        $address = $_POST['address'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $comment = $_POST['comment'] ?? '';

        if (empty($address) || empty($phone)) {
            echo "Заполните все обязательные поля!";
            echo "<br><a href='/checkout'>Вернуться к оформлению</a>";
            exit;
        }

        $cartModel = new \Models\Cart();
        $cartItems = $cartModel->getUserCart($userId);

        if (empty($cartItems)) {
            echo "Корзина пуста!";
            echo "<br><a href='/catalog'>Вернуться в каталог</a>";
            exit;
        }

        $totalPrice = 0;
        foreach ($cartItems as $item) {
            $totalPrice += $item['price'] * $item['amount'];
        }

        $pdo = new \PDO("pgsql:host=db;port=5432;dbname=postgres", "semen", "0000");

        $stmt = $pdo->prepare('
            INSERT INTO orders (user_id, address, phone, comment, total_price) 
            VALUES (:user_id, :address, :phone, :comment, :total_price)
            RETURNING id
        ');

        $stmt->execute([
            ':user_id' => $userId,
            ':address' => $address,
            ':phone' => $phone,
            ':comment' => $comment,
            ':total_price' => $totalPrice
        ]);

        $orderId = $stmt->fetch()['id'];

        foreach ($cartItems as $item) {
            $stmt = $pdo->prepare('
                INSERT INTO order_products (order_id, product_id, amount, price) 
                VALUES (:order_id, :product_id, :amount, :price)
            ');

            $stmt->execute([
                ':order_id' => $orderId,
                ':product_id' => $item['id'],
                ':amount' => $item['amount'],
                ':price' => $item['price']
            ]);
        }

        $stmt = $pdo->prepare('DELETE FROM user_products WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);

        echo "<h1>Заказ #$orderId оформлен!</h1>";
        echo "<p>Спасибо за покупку!</p>";
        echo "<p><strong>Номер заказа:</strong> #$orderId</p>";
        echo "<p><strong>Адрес доставки:</strong> " . htmlspecialchars($address) . "</p>";
        echo "<p><strong>Телефон:</strong> " . htmlspecialchars($phone) . "</p>";
        if (!empty($comment)) {
            echo "<p><strong>Комментарий:</strong> " . htmlspecialchars($comment) . "</p>";
        }
        echo "<p><strong>Сумма заказа:</strong> " . $totalPrice . " ₽</p>";

        echo "<h3>Состав заказа:</h3>";
        echo "<ul>";
        foreach ($cartItems as $item) {
            echo "<li>" . htmlspecialchars($item['name']) . " - " .
                $item['amount'] . " шт. × " . $item['price'] . " ₽ = " .
                ($item['amount'] * $item['price']) . " ₽</li>";
        }
        echo "</ul>";

        echo "<br><a href='/catalog'>Вернуться в каталог</a>";
        echo " | ";
        echo "<a href='/profile'>Перейти в профиль</a>";
    }
}