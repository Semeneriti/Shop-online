<?php
namespace Controllers;

use Models\Product;
use Models\Cart;

class CatalogController
{
    public function __construct(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userId = $_SESSION['userId'] ?? null;

        // Получаем все товары
        $products = Product::getAll();

        // Инициализируем переменные для корзины
        $cart = null;
        $cartItems = [];
        $cartTotalPrice = 0.0;
        $cartItemsCount = 0;

        // Если пользователь авторизован, загружаем корзину
        if ($userId) {
            $cart = new Cart($userId);
            $cartItems = $cart->getItems();
            $cartTotalPrice = $cart->getTotalPrice();
            $cartItemsCount = $cart->getTotalAmount();
        }

        // Получаем сообщения из сессии
        $successMessage = $_SESSION['success_message'] ?? null;
        $errorMessage = $_SESSION['error_message'] ?? null;

        // Очищаем сообщения после получения
        unset($_SESSION['success_message'], $_SESSION['error_message']);

        require_once __DIR__ . '/../Views/catalog.php';
    }
}