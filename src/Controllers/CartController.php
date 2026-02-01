<?php
namespace Controllers;

use Models\Cart;
use Models\Order;

class CartController
{
    public function showCart(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['userId'])) {
            header("Location: /login");
            exit;
        }

        $userId = $_SESSION['userId'];

        $cart = new Cart($userId);
        $cartData = $cart->toArray();

        require_once __DIR__ . '/../Views/cart.php';
    }

    public function showCheckout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['userId'])) {
            header("Location: /login");
            exit;
        }

        $userId = $_SESSION['userId'];

        $cart = new Cart($userId);

        if ($cart->isEmpty()) {
            header("Location: /cart");
            exit;
        }

        $cartData = $cart->toArray();

        $errors = $_SESSION['checkout_errors'] ?? [];
        $formData = $_SESSION['checkout_data'] ?? [];

        unset($_SESSION['checkout_errors'], $_SESSION['checkout_data']);

        require_once __DIR__ . '/../Views/checkout.php';
    }

    public function processCheckout(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /checkout");
            exit;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['userId'])) {
            header("Location: /login");
            exit;
        }

        $userId = $_SESSION['userId'];

        $address = htmlspecialchars(trim($_POST['address'] ?? ''));
        $phone = htmlspecialchars(trim($_POST['phone'] ?? ''));
        $comment = htmlspecialchars(trim($_POST['comment'] ?? ''));

        $errors = [];
        if (empty($address)) {
            $errors['address'] = 'Укажите адрес доставки';
        }

        if (empty($phone)) {
            $errors['phone'] = 'Укажите номер телефона';
        } elseif (!preg_match('/^\+?[1-9]\d{1,14}$/', $phone)) {
            $errors['phone'] = 'Неверный формат номера телефона';
        }

        if (!empty($errors)) {
            $_SESSION['checkout_errors'] = $errors;
            $_SESSION['checkout_data'] = [
                'address' => $address,
                'phone' => $phone,
                'comment' => $comment
            ];
            header("Location: /checkout");
            exit;
        }

        try {
            $cart = new Cart($userId);

            if ($cart->isEmpty()) {
                throw new \RuntimeException("Корзина пуста");
            }

            $orderData = $cart->checkout();

            $orderData['address'] = $address;
            $orderData['phone'] = $phone;
            $orderData['comment'] = $comment;

            $order = Order::createFromCart($orderData);

            if (!$order) {
                throw new \RuntimeException("Ошибка при создании заказа");
            }

            unset($_SESSION['checkout_errors'], $_SESSION['checkout_data']);

            $orderDetails = $order->getDetails();

            require_once __DIR__ . '/../Views/order_success.php';

        } catch (\InvalidArgumentException $e) {
            $_SESSION['error_message'] = $e->getMessage();
            header("Location: /cart");
            exit;
        } catch (\RuntimeException $e) {
            $_SESSION['error_message'] = $e->getMessage();
            header("Location: /checkout");
            exit;
        } catch (\Exception $e) {
            $_SESSION['error_message'] = "Произошла ошибка при оформлении заказа: " . $e->getMessage();
            header("Location: /checkout");
            exit;
        }
    }

    public function increaseProduct(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /cart");
            exit;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['userId'])) {
            header("Location: /login");
            exit;
        }

        $userId = $_SESSION['userId'];
        $productId = (int)($_POST['product_id'] ?? 0);

        if ($productId <= 0) {
            header("Location: /catalog");
            exit;
        }

        $cart = new Cart($userId);

        $currentAmount = 0;
        foreach ($cart->getItems() as $item) {
            if ($item['product']->getId() === $productId) {
                $currentAmount = $item['amount'];
                break;
            }
        }

        $cart->updateItem($productId, $currentAmount + 1);

        header("Location: /catalog");
        exit;
    }

    public function decreaseProduct(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /cart");
            exit;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['userId'])) {
            header("Location: /login");
            exit;
        }

        $userId = $_SESSION['userId'];
        $productId = (int)($_POST['product_id'] ?? 0);

        if ($productId <= 0) {
            header("Location: /catalog");
            exit;
        }

        $cart = new Cart($userId);

        $currentAmount = 0;
        foreach ($cart->getItems() as $item) {
            if ($item['product']->getId() === $productId) {
                $currentAmount = $item['amount'];
                break;
            }
        }

        $newAmount = max(1, $currentAmount - 1);

        if ($newAmount !== $currentAmount) {
            $cart->updateItem($productId, $newAmount);
        }

        header("Location: /catalog");
        exit;
    }
}
