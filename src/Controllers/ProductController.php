<?php
namespace Controllers;

use Models\Cart;
use Models\Product;

class ProductController
{
    public function showForm(): void
    {
        if (!isset($_SESSION['userId'])) {
            header("Location: /login");
            exit;
        }

        // Получаем все товары для отображения
        $products = Product::getAll();

        require_once __DIR__ . '/../Views/add_product.php';
    }

    public function addToCart(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /add-product");
            exit;
        }

        if (!isset($_SESSION['userId'])) {
            header("Location: /login");
            exit;
        }

        $userId = $_SESSION['userId'];
        $productId = (int)($_POST['product-id'] ?? 0);
        $amount = (int)($_POST['amount'] ?? 0);

        // Валидация
        if ($productId <= 0 || $amount <= 0) {
            $_SESSION['error_message'] = "Необходимо указать товар и количество";
            header("Location: /add-product");
            exit;
        }

        // Проверяем существование товара
        $product = Product::findById($productId);
        if (!$product) {
            $_SESSION['error_message'] = "Товар не найден";
            header("Location: /add-product");
            exit;
        }

        // Проверяем доступность товара
        if ($amount > $product->getStock()) {
            $_SESSION['error_message'] = "Недостаточно товара на складе. Доступно: " . $product->getStock() . " шт.";
            header("Location: /add-product");
            exit;
        }

        try {
            // Создаем или получаем корзину пользователя
            $cart = new Cart($userId);

            // Добавляем товар в корзину
            if ($cart->addItem($productId, $amount)) {
                $_SESSION['success_message'] = "Товар успешно добавлен в корзину!";
                header("Location: /catalog");
                exit;
            } else {
                $_SESSION['error_message'] = "Ошибка при добавлении товара в корзину";
                header("Location: /add-product");
                exit;
            }
        } catch (\InvalidArgumentException $e) {
            $_SESSION['error_message'] = $e->getMessage();
            header("Location: /add-product");
            exit;
        } catch (\Exception $e) {
            $_SESSION['error_message'] = "Произошла ошибка: " . $e->getMessage();
            header("Location: /add-product");
            exit;
        }
    }
}