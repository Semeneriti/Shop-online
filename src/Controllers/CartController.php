<?php
namespace Controllers;

// Импортируем необходимые классы
use Services\CartService;      // Сервис для работы с корзиной
use Services\OrderService;     // Сервис для работы с заказами
use Request\UpdateCartRequest;  // Класс-запрос для обновления корзины (содержит данные из формы)

// Класс контроллера корзины - наследуется от BaseController
class CartController extends BaseController
{
    // Свойства класса - сервисы, с которыми будет работать контроллер
    private CartService $cartService;    // Для операций с корзиной
    private OrderService $orderService;  // Для операций с заказами

    // Конструктор - вызывается при создании объекта контроллера
    public function __construct()
    {
        parent::__construct();  // Вызываем конструктор родительского класса (там создается $this->auth)

        // Создаем объекты сервисов и сохраняем в свойства
        $this->cartService = new CartService();
        $this->orderService = new OrderService();
    }

    /**
     * Отображение корзины
     * GET /cart
     */
    public function showCart(): void
    {
        // Проверяем, авторизован ли пользователь
        $this->auth->requireAuth();

        // Получаем данные корзины для текущего пользователя
        $cartData = $this->cartService->getCartData($this->auth->getUserId());

        // Подключаем шаблон (вид) корзины
        require_once __DIR__ . '/../Views/cart.php';
    }

    /**
     * Отображение страницы оформления заказа
     * GET /checkout
     */
    public function showCheckout(): void
    {
        // Проверяем авторизацию
        $this->auth->requireAuth();

        // Если корзина пуста - редирект на страницу корзины
        if ($this->cartService->isCartEmpty($this->auth->getUserId())) {
            $this->auth->redirect("/cart");
        }

        // Получаем данные корзины для отображения в форме
        $cartData = $this->cartService->getCartData($this->auth->getUserId());

        // Получаем сохраненные в сессии ошибки и данные формы (если были ошибки при предыдущей отправке)
        $errors = $this->auth->getSessionValue('checkout_errors', []);
        $formData = $this->auth->getSessionValue('checkout_data', []);

        // Удаляем эти данные из сессии, чтобы они не висели там вечно
        $this->auth->unsetSessionValue('checkout_errors');
        $this->auth->unsetSessionValue('checkout_data');

        // Подключаем шаблон оформления заказа
        require_once __DIR__ . '/../Views/checkout.php';
    }

    /**
     * Обработка отправки формы оформления заказа
     * POST /checkout
     */
    public function processCheckout(): void
    {
        // Если запрос не POST - редирект на страницу оформления
        if (!$this->auth->isPostRequest()) {
            $this->auth->redirect("/checkout");
        }

        // Проверяем авторизацию
        $this->auth->requireAuth();

        // Получаем данные из POST-запроса (поля формы)
        $address = $this->auth->getPostString('address');
        $phone = $this->auth->getPostString('phone');
        $comment = $this->auth->getPostString('comment');

        // Формируем массив с данными заказа
        $orderData = [
            'address' => $address,
            'phone' => $phone,
            'comment' => $comment
        ];

        // Валидируем данные (проверяем, что все поля заполнены правильно)
        $errors = $this->orderService->validateOrderData($orderData);

        // Если есть ошибки валидации
        if (!empty($errors)) {
            // Сохраняем ошибки и введенные данные в сессию
            $this->auth->setSessionValue('checkout_errors', $errors);
            $this->auth->setSessionValue('checkout_data', $orderData);
            // Возвращаем пользователя обратно на страницу оформления
            $this->auth->redirect("/checkout");
        }

        // Пытаемся создать заказ
        try {
            // Создаем заказ из корзины текущего пользователя
            $order = $this->orderService->createOrderFromCart($this->auth->getUserId(), $orderData);

            // Если заказ не создался - выбрасываем исключение
            if (!$order) {
                throw new \RuntimeException("Ошибка при создании заказа");
            }

            // Очищаем данные оформления из сессии
            $this->auth->unsetSessionValue('checkout_errors');
            $this->auth->unsetSessionValue('checkout_data');

            // Получаем детали заказа и показываем страницу успеха
            $orderDetails = $order->getDetails();
            require_once __DIR__ . '/../Views/order_success.php';

        } catch (\InvalidArgumentException $e) {
            // Ошибка: неверные аргументы (например, товара нет в наличии)
            $this->auth->setSessionValue('error_message', $e->getMessage());
            $this->auth->redirect("/cart");
        } catch (\RuntimeException $e) {
            // Ошибка выполнения (например, корзина пуста)
            $this->auth->setSessionValue('error_message', $e->getMessage());
            $this->auth->redirect("/checkout");
        } catch (\Exception $e) {
            // Любая другая ошибка
            $this->auth->setSessionValue('error_message', "Произошла ошибка при оформлении заказа: " . $e->getMessage());
            $this->auth->redirect("/checkout");
        }
    }

    /**
     * Увеличение количества товара в корзине
     * POST /cart/increase
     */
    public function increaseProduct(UpdateCartRequest $request): void
    {
        // Проверяем метод запроса
        if (!$this->auth->isPostRequest()) {
            $this->auth->redirect("/cart");
        }

        // Проверяем авторизацию
        $this->auth->requireAuth();

        // Получаем ID товара из объекта запроса
        $productId = $request->getProductId();

        // Если ID некорректный - редирект в каталог
        if ($productId <= 0) {
            $this->auth->redirect("/catalog");
        }

        // Получаем текущее количество товара в корзине
        $currentAmount = $this->cartService->getCurrentAmount($this->auth->getUserId(), $productId);

        // Увеличиваем количество на 1 и обновляем корзину
        $this->cartService->updateItem($this->auth->getUserId(), $productId, $currentAmount + 1);

        // Возвращаемся в каталог
        $this->auth->redirect("/catalog");
    }

    /**
     * Уменьшение количества товара в корзине
     * POST /cart/decrease
     */
    public function decreaseProduct(UpdateCartRequest $request): void
    {
        // Проверяем метод запроса
        if (!$this->auth->isPostRequest()) {
            $this->auth->redirect("/cart");
        }

        // Проверяем авторизацию
        $this->auth->requireAuth();

        // Получаем ID товара
        $productId = $request->getProductId();

        // Если ID некорректный - редирект
        if ($productId <= 0) {
            $this->auth->redirect("/catalog");
        }

        // Получаем текущее количество
        $currentAmount = $this->cartService->getCurrentAmount($this->auth->getUserId(), $productId);

        // Новое количество (не меньше 1)
        $newAmount = max(1, $currentAmount - 1);

        // Если количество изменилось - обновляем корзину
        if ($newAmount !== $currentAmount) {
            $this->cartService->updateItem($this->auth->getUserId(), $productId, $newAmount);
        }

        // Возвращаемся в каталог
        $this->auth->redirect("/catalog");
    }
}