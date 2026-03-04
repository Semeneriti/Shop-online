<?php
namespace Controllers;

// Импортируем необходимые классы
use Models\Product;              // Модель товара - для работы с товарами
use Models\Review;               // Модель отзыва - для создания отзывов
use Services\CartService;        // Сервис корзины - для добавления товаров в корзину
use Request\AddProductRequest;   // Класс-запрос для добавления товара в корзину

// Контроллер товаров - наследуется от BaseController
class ProductController extends BaseController
{
    // Свойство для хранения сервиса корзины
    private CartService $cartService;

    /**
     * Конструктор - вызывается при создании объекта контроллера
     */
    public function __construct()
    {
        // Вызываем конструктор родительского класса (там создается $this->auth)
        parent::__construct();

        // Создаем объект сервиса корзины и сохраняем в свойство
        $this->cartService = new CartService();
    }

    /**
     * Отображение формы добавления товара в корзину (по ID товара)
     * GET /add-product
     */
    public function showForm(): void
    {
        // Проверяем, авторизован ли пользователь
        $this->auth->requireAuth();

        // Получаем все товары для отображения в выпадающем списке
        $products = Product::getAll();

        // Подключаем шаблон формы добавления товара
        require_once __DIR__ . '/../Views/add_product.php';
    }

    /**
     * Обработка добавления товара в корзину
     * POST /add-product
     */
    public function addToCart(AddProductRequest $request): void
    {
        // Проверяем, что запрос отправлен методом POST
        if (!$this->auth->isPostRequest()) {
            $this->auth->redirect("/add-product");
        }

        // Проверяем авторизацию пользователя
        $this->auth->requireAuth();

        // Получаем ID товара и количество из объекта запроса
        $productId = $request->getProductId();
        $amount = $request->getAmount();

        // Ищем товар в базе данных по ID
        $product = Product::findById($productId);

        // Если товар не найден - показываем ошибку
        if (!$product) {
            $this->auth->setSessionValue('error_message', "Товар не найден");
            $this->auth->redirect("/add-product");
        }

        // Проверяем, что запрашиваемое количество есть на складе
        if ($amount > $product->getStock()) {
            $this->auth->setSessionValue('error_message', "Недостаточно товара на складе. Доступно: " . $product->getStock() . " шт.");
            $this->auth->redirect("/add-product");
        }

        // Пытаемся добавить товар в корзину
        try {
            // Вызываем метод сервиса корзины для добавления товара
            if ($this->cartService->addItem($this->auth->getUserId(), $productId, $amount)) {
                // Если успешно - показываем сообщение об успехе и редиректим в каталог
                $this->auth->setSessionValue('success_message', "Товар успешно добавлен в корзину!");
                $this->auth->redirect("/catalog");
            } else {
                // Если ошибка при добавлении
                $this->auth->setSessionValue('error_message', "Ошибка при добавлении товара в корзину");
                $this->auth->redirect("/add-product");
            }
        } catch (\InvalidArgumentException $e) {
            // Ошибка неверных аргументов (например, отрицательное количество)
            $this->auth->setSessionValue('error_message', $e->getMessage());
            $this->auth->redirect("/add-product");
        } catch (\Exception $e) {
            // Любая другая ошибка
            $this->auth->setSessionValue('error_message', "Произошла ошибка: " . $e->getMessage());
            $this->auth->redirect("/add-product");
        }
    }

    /**
     * Отображение страницы конкретного товара
     * GET /product?id=XXX
     */
    public function showProduct(): void
    {
        // Получаем ID товара из GET-параметров (из адресной строки)
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

        // Получаем сообщения об успехе/ошибке из сессии
        $successMessage = $this->auth->getSessionValue('success_message');
        $errorMessage = $this->auth->getSessionValue('error_message');

        // Удаляем эти сообщения из сессии (чтобы не висели)
        $this->auth->unsetSessionValue('success_message');
        $this->auth->unsetSessionValue('error_message');

        // Передаем объект авторизации в шаблон (нужно для проверки прав на добавление отзыва)
        $auth = $this->auth;

        // Подключаем шаблон страницы товара
        require_once __DIR__ . '/../Views/product.php';
    }

    /**
     * Добавление отзыва к товару
     * POST /product/review
     */
    public function addReview(): void
    {
        // Проверяем, что запрос отправлен методом POST
        if (!$this->auth->isPostRequest()) {
            $this->auth->redirect("/catalog");
        }

        // Проверяем, что пользователь авторизован (только авторизованные могут оставлять отзывы)
        $this->auth->requireAuth();

        // Получаем данные из POST-запроса
        $productId = $this->auth->getPostInt('product_id');  // ID товара
        $rating = $this->auth->getPostInt('rating');         // Оценка (1-5)
        $text = $this->auth->getPostString('text');          // Текст отзыва

        // Проверяем, что товар с таким ID существует
        $product = Product::findById($productId);
        if (!$product) {
            $this->auth->setSessionValue('error_message', "Товар не найден");
            $this->auth->redirect("/catalog");
        }

        // Пытаемся сохранить отзыв
        try {
            // Получаем данные текущего пользователя
            $user = $this->auth->getCurrentUser();

            // Создаем новый объект отзыва
            $review = new Review(
                $productId,           // ID товара
                $user->getId(),       // ID пользователя
                $user->getName(),     // Имя пользователя (сохраняем отдельно, чтобы отзыв не потерялся при удалении аккаунта)
                $rating,              // Оценка
                $text                 // Текст отзыва
            );

            // Сохраняем отзыв в базу данных
            if ($review->save()) {
                // Если успешно - показываем сообщение об успехе
                $this->auth->setSessionValue('success_message', "Спасибо за ваш отзыв!");
            } else {
                // Если ошибка при сохранении
                $this->auth->setSessionValue('error_message', "Ошибка при сохранении отзыва");
            }
        } catch (\Exception $e) {
            // Любая ошибка при создании отзыва
            $this->auth->setSessionValue('error_message', "Произошла ошибка: " . $e->getMessage());
        }

        // Возвращаем пользователя обратно на страницу товара
        $this->auth->redirect("/product?id=" . $productId);
    }
}