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
        // Получаем ID товара из адресной строки
    {
        $productId = (int)($_GET['id'] ?? 0);
        // Если ID не передан или равен 0 - отправляем пользователя в каталог
        if ($productId <= 0) {
            $this->auth->redirect("/catalog");
        }
       // Ищем товар в базе данных по ID
        $product = Product::findById($productId);
        // Если товар не найден - отправляем в каталог
        if (!$product) {
            $this->auth->redirect("/catalog");
        }
// Получаем все отзывы для этого товара
        $reviews = $product->getReviews();
// Получаем сообщения из сессии
        $successMessage = $this->auth->getSessionValue('success_message');
        $errorMessage = $this->auth->getSessionValue('error_message');

        $this->auth->unsetSessionValue('success_message');
        $this->auth->unsetSessionValue('error_message');

        // Передаем auth в шаблон
        $auth = $this->auth;
        // Загружаем шаблон страницы товара
        require_once __DIR__ . '/../Views/product.php';
    }
//Добавление Отзыва
    public function addReview(): void
        // Проверяем, что запрос пришел методом POST
      {
        if (!$this->auth->isPostRequest()) {
            $this->auth->redirect("/catalog");
        }
// Проверяем, что пользователь авторизован
        $this->auth->requireAuth();
// Получаем данные из POST-запроса
        $productId = $this->auth->getPostInt('product_id');
        $rating = $this->auth->getPostInt('rating');
        $text = $this->auth->getPostString('text');
// ВАЛИДАЦИЯ: проверяем, что ID товара корректен
        if ($productId <= 0) {
            $this->auth->setSessionValue('error_message', "Товар не найден");
            $this->auth->redirect("/catalog");
        }
// Проверяем, что товар с таким ID существует в базе
        $product = Product::findById($productId);
        if (!$product) {
            $this->auth->setSessionValue('error_message', "Товар не найден");
            $this->auth->redirect("/catalog");
        }
        // ВАЛИДАЦИЯ: проверяем, что оценка от 1 до 5
        if ($rating < 1 || $rating > 5) {
            $this->auth->setSessionValue('error_message', "Оценка должна быть от 1 до 5");
            $this->auth->redirect("/product?id=" . $productId);
        }
        // ВАЛИДАЦИЯ: проверяем, что текст отзыва не пустой
        if (empty($text)) {
            $this->auth->setSessionValue('error_message', "Напишите текст отзыва");
            $this->auth->redirect("/product?id=" . $productId);
        }
// Пытаемся сохранить отзыв
        try {
            // Получаем данные текущего пользователя
            $user = $this->auth->getCurrentUser();
// СОЗДАЕМ НОВЫЙ ОТЗЫВ
            $review = new Review(
                $productId,
                $user->getId(),
                $user->getName(),
                $rating,
                $text
            );
// СОХРАНЯЕМ В БАЗУ ДАННЫХ
            if ($review->save()) {
                $this->auth->setSessionValue('success_message', "Спасибо за ваш отзыв!");
            } else {
                $this->auth->setSessionValue('error_message', "Ошибка при сохранении отзыва");
            }
        } catch (\Exception $e)
            //Exception — тип исключения, которое нужно ловить (с слешем указывается глобальный неймспейс
            //$e — переменная, в которую будет помещен объект исключения
            // Если произошло исключение - показываем ошибку
        {
            $this->auth->setSessionValue('error_message', "Произошла ошибка: " . $e->getMessage());
        }
// Возвращаем пользователя обратно на страницу товара
        $this->auth->redirect("/product?id=" . $productId);
    }
}