<?php

declare(strict_types=1);

namespace Controllers;

// Импортируем необходимые классы
use Models\Product;
use Services\CartService;

// Модель товара - для получения списка товаров
// Сервис корзины - для получения информации о корзине пользователя

// Контроллер каталога товаров - наследуется от BaseController
class CatalogController extends Controller
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
     * Главная страница каталога
     * GET /catalog
     *
     * Отображает все товары, а также информацию о корзине для авторизованных пользователей
     */
    public function index(): void
    {
        // Получаем все товары из базы данных (статический метод модели Product)
        $products = Product::getAll();

        // Инициализируем переменные для корзины значениями по умолчанию
        $cartItems = [];           // Товары в корзине пользователя
        $cartTotalPrice = 0.0;     // Общая сумма корзины
        $cartItemsCount = 0;        // Количество товаров в корзине

        // Если пользователь НЕ гость (то есть авторизован)
        if (!$this->auth->isGuest()) {
            // Получаем ID текущего пользователя из сервиса авторизации
            $userId = $this->auth->getUserId();

            // Получаем данные о корзине пользователя через сервис корзины
            $cartItems = $this->cartService->getCartItems($userId);           // Список товаров в корзине
            $cartTotalPrice = $this->cartService->getCartTotalPrice($userId); // Общая стоимость
            $cartItemsCount = $this->cartService->getCartTotalAmount($userId); // Общее количество
        }

        // Получаем сообщения об успехе/ошибке из сессии (если есть)
        $successMessage = $this->auth->getSessionValue('success_message');
        $errorMessage = $this->auth->getSessionValue('error_message');

        // Удаляем эти сообщения из сессии, чтобы они не показывались повторно
        $this->auth->unsetSessionValue('success_message');
        $this->auth->unsetSessionValue('error_message');

        // Подключаем шаблон (вид) каталога
        // В шаблоне будут доступны все переменные, которые мы объявили выше:
        // $products, $cartItems, $cartTotalPrice, $cartItemsCount, $successMessage, $errorMessage
        require_once __DIR__ . '/../Views/catalog.php';
    }
}