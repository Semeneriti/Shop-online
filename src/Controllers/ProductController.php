<?php
namespace Controllers;

use Models\Product;
use Models\Review;
use Services\CartService;

class ProductController extends BaseController
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

    public function addToCart(): void
    {
        if (!$this->auth->isPostRequest()) {
            $this->auth->redirect("/add-product");
        }

        $this->auth->requireAuth();

        $productId = $this->auth->getPostInt('product-id');
        $amount = $this->auth->getPostInt('amount');

        if ($productId <= 0 || $amount <= 0) {
            $this->auth->setSessionValue('error_message', "Необходимо указать товар и количество");
            $this->auth->redirect("/add-product");
        }

        $product = Product::findById($productId);
        if (!$product) {
            $this->auth->setSessionValue('error_message', "Товар не найден");
            $this->auth->redirect("/add-product");
        }

        if ($amount > $product->getStock()) {
            $this->auth->setSessionValue('error_message', "Недостаточно товара на складе. Доступно: " . $product->getStock() . " шт.");
            $this->auth->redirect("/add-product");
        }

        try {
            if ($this->cartService->addItem($this->auth->getUserId(), $productId, $amount)) {
                $this->auth->setSessionValue('success_message', "Товар успешно добавлен в корзину!");
                $this->auth->redirect("/catalog");
            } else {
                $this->auth->setSessionValue('error_message', "Ошибка при добавлении товара в корзину");
                $this->auth->redirect("/add-product");
            }
        } catch (\InvalidArgumentException $e) {
            $this->auth->setSessionValue('error_message', $e->getMessage());
            $this->auth->redirect("/add-product");
        } catch (\Exception $e) {
            $this->auth->setSessionValue('error_message', "Произошла ошибка: " . $e->getMessage());
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

        // Передаем auth в шаблон
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

        if ($productId <= 0) {
            $this->auth->setSessionValue('error_message', "Товар не найден");
            $this->auth->redirect("/catalog");
        }

        $product = Product::findById($productId);
        if (!$product) {
            $this->auth->setSessionValue('error_message', "Товар не найден");
            $this->auth->redirect("/catalog");
        }

        if ($rating < 1 || $rating > 5) {
            $this->auth->setSessionValue('error_message', "Оценка должна быть от 1 до 5");
            $this->auth->redirect("/product?id=" . $productId);
        }

        if (empty($text)) {
            $this->auth->setSessionValue('error_message', "Напишите текст отзыва");
            $this->auth->redirect("/product?id=" . $productId);
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