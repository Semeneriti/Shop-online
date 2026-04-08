<?php

declare(strict_types=1);

namespace Controllers;

use Models\Product;
use Models\Review;
use Services\CartService;
use DTO\AddToCartDto;

class ProductController extends Controller
{
    private CartService $cartService;

    public function __construct()
    {
        parent::__construct();
        $this->cartService = new CartService();
    }

    public function showForm(): void
    {
        $this->auth->requireAuth();
        $products = Product::getAll();
        require_once __DIR__ . '/../Views/add_product.php';
    }

    public function ajaxAddToCart(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($this->auth->isGuest()) {
            echo json_encode(['success' => false, 'error' => 'Необходимо авторизоваться'], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (!$this->auth->isPostRequest()) {
            echo json_encode(['success' => false, 'error' => 'Неверный метод запроса'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $productId = (int)($this->auth->getPostParam('product_id', 0));
        $amount = (int)($this->auth->getPostParam('amount', 1));

        if ($productId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Неверный ID товара'], JSON_UNESCAPED_UNICODE);
            return;
        }

        if ($amount <= 0) {
            echo json_encode(['success' => false, 'error' => 'Количество должно быть больше 0'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $product = Product::findById($productId);
        if (!$product) {
            echo json_encode(['success' => false, 'error' => 'Товар не найден'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $userId = $this->auth->getUserId();
        $currentAmount = $this->cartService->getCurrentAmount($userId, $productId);
        $newAmount = $currentAmount + $amount;

        if ($newAmount > $product->getStock()) {
            echo json_encode(['success' => false, 'error' => 'Недостаточно товара на складе. Доступно: ' . $product->getStock() . ' шт.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $dto = new AddToCartDto($userId, $productId, $newAmount);
            $result = $this->cartService->addItem($dto);

            if ($result) {
                $cartTotalAmount = $this->cartService->getCartTotalAmount($userId);
                $cartTotalPrice = $this->cartService->getCartTotalPrice($userId);

                echo json_encode([
                    'success' => true,
                    'cart_count' => $cartTotalAmount,
                    'cart_total' => $cartTotalPrice,
                    'product_id' => $productId,
                    'new_amount' => $newAmount
                ], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(['success' => false, 'error' => 'Ошибка при добавлении товара в корзину'], JSON_UNESCAPED_UNICODE);
            }
        } catch (\Exception $e) {
            $this->logger->error('AJAX add to cart error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Произошла ошибка: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    public function addToCart(): void
    {
        if (!$this->auth->isPostRequest()) {
            $this->auth->redirect("/add-product");
        }

        $this->auth->requireAuth();

        $productId = (int)($this->auth->getPostParam('product-id', 0));
        $amount = (int)($this->auth->getPostParam('amount', 1));

        if ($productId <= 0) {
            $this->auth->setSessionValue('error_message', "Неверный ID товара");
            $this->auth->redirect("/add-product");
            return;
        }

        if ($amount <= 0) {
            $this->auth->setSessionValue('error_message', "Неверное количество");
            $this->auth->redirect("/add-product");
            return;
        }

        $product = Product::findById($productId);

        if (!$product) {
            $this->auth->setSessionValue('error_message', "Товар не найден");
            $this->auth->redirect("/add-product");
            return;
        }

        if ($amount > $product->getStock()) {
            $this->auth->setSessionValue('error_message', "Недостаточно товара на складе. Доступно: " . $product->getStock() . " шт.");
            $this->auth->redirect("/add-product");
            return;
        }

        $userId = $this->auth->getUserId();
        $currentAmount = $this->cartService->getCurrentAmount($userId, $productId);
        $newAmount = $currentAmount + $amount;

        $dto = new AddToCartDto($userId, $productId, $newAmount);

        if ($this->cartService->addItem($dto)) {
            $this->auth->setSessionValue('success_message', "Товар успешно добавлен в корзину!");
            $this->auth->redirect("/catalog");
        } else {
            $this->auth->setSessionValue('error_message', "Ошибка при добавлении товара в корзину");
            $this->auth->redirect("/add-product");
        }
    }

    public function showProduct(): void
    {
        $productId = (int)($_GET['id'] ?? 0);

        if ($productId <= 0) {
            $this->auth->redirect("/catalog");
        }

        $product = Product::findById($productId);

        if (!$product) {
            $this->auth->redirect("/catalog");
        }

        $reviews = $product->getReviews();

        $successMessage = $this->auth->getSessionValue('success_message');
        $errorMessage = $this->auth->getSessionValue('error_message');

        $this->auth->unsetSessionValue('success_message');
        $this->auth->unsetSessionValue('error_message');

        $auth = $this->auth;

        require_once __DIR__ . '/../Views/product.php';
    }

    public function addReview(): void
    {
        if (!$this->auth->isPostRequest()) {
            $this->auth->redirect("/catalog");
        }

        $this->auth->requireAuth();

        $productId = $this->auth->getPostInt('product_id');
        $rating = $this->auth->getPostInt('rating');
        $text = $this->auth->getPostString('text');

        $product = Product::findById($productId);
        if (!$product) {
            $this->auth->setSessionValue('error_message', "Товар не найден");
            $this->auth->redirect("/catalog");
            return;
        }

        if ($rating < 1 || $rating > 5) {
            $this->auth->setSessionValue('error_message', "Оценка должна быть от 1 до 5");
            $this->auth->redirect("/product?id=" . $productId);
            return;
        }

        if (empty($text)) {
            $this->auth->setSessionValue('error_message', "Введите текст отзыва");
            $this->auth->redirect("/product?id=" . $productId);
            return;
        }

        try {
            $user = $this->auth->getCurrentUser();

            $review = new Review(
                $productId,
                $user->getId(),
                $user->getName(),
                $rating,
                $text
            );

            if ($review->save()) {
                $this->auth->setSessionValue('success_message', "Спасибо за ваш отзыв!");
            } else {
                $this->auth->setSessionValue('error_message', "Ошибка при сохранении отзыва");
            }
        } catch (\Exception $e) {
            $this->auth->setSessionValue('error_message', "Произошла ошибка: " . $e->getMessage());
        }

        $this->auth->redirect("/product?id=" . $productId);
    }
}