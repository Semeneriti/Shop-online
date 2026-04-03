<?php // должна быть пустая строка между тегом и нэймспейс

// PSR-12: Отсутствует declare(strict_types=1) в начале файла.
// Добавь строку: declare(strict_types=1);
// Это обязательно для строгой типизации и является стандартом в современном PHP.

namespace Controllers;

use Request\UpdateCartRequest;
use Services\CartService;
use Services\OrderService;

class CartController extends BaseController // смениться на Controller
{
    private CartService $cartService;
    private OrderService $orderService;

    public function __construct()
    {
        parent::__construct();
        $this->cartService = new CartService();
        $this->orderService = new OrderService();
    }

    public function showCart(): void // Можно назвать метод просто show, так как мы и так находимся в классе cart
    {
        $this->auth->requireAuth();
        $cartData = $this->cartService->getCartData($this->auth->getUserId());

        $successMessage = $this->auth->getSessionValue('success_message');
        $errorMessage = $this->auth->getSessionValue('error_message');

        $this->auth->unsetSessionValue('success_message');
        $this->auth->unsetSessionValue('error_message');

        require_once __DIR__ . '/../Views/cart.php'; // Молодец! Сам разобрался с DIR, но ниже
        // Нарушение архитектуры MVC.
        // Контроллер не должен напрямую подключать файл представления через require_once.
        // В BaseController должен быть метод render(), который берёт на себя эту ответственность.
        // Пример: $this->render('cart', compact('cartData', 'successMessage', 'errorMessage'));
    }

    public function showCheckout(): void // тут так же как в 20 стрроке
    {
        $this->auth->requireAuth();

        if ($this->cartService->isCartEmpty($this->auth->getUserId())) { // isCartEmpote поменяй название на более понятную
            // Отсутствует return после redirect.
            // Метод redirect() скорее всего делает header('Location: ...'), но НЕ останавливает выполнение скрипта.
            // Без return весь код ниже продолжит выполняться после редиректа, что может привести к ошибкам.
            // Исправление: добавь return после каждого вызова redirect().
            $this->auth->redirect("/cart"); // сдесь добавть return или после exit так как код после редиректа продолжен выполнение и не известно к чему это модет привечти
        }

        $cartData = $this->cartService->getCartData($this->auth->getUserId());
        $errors = $this->auth->getSessionValue('checkout_errors', []);
        $formData = $this->auth->getSessionValue('checkout_data', []);

        $this->auth->unsetSessionValue('checkout_errors');
        $this->auth->unsetSessionValue('checkout_data');

        // Та же проблема, что в showCart — прямой require_once вместо вызова render().
        require_once __DIR__ . '/../Views/checkout.php';
    }

    public function processCheckout(): void // название проверка процесса, а какого процесса?
    {
        if (!$this->auth->isPostRequest()) {
            // Отсутствует return после redirect.
            // Без return выполнение продолжится, и ниже вызовется requireAuth(),
            // getPostString() и вся остальная логика — даже при GET-запросе.
            $this->auth->redirect("/checkout");
        }

        $this->auth->requireAuth();

        // ❌ Нарушение архитектуры: прямое чтение POST-данных в контроллере.
        // Для этого в проекте есть слой Request-объектов (например, UpdateCartRequest).
        // Создай CheckoutRequest (или OrderRequest), который инкапсулирует чтение и первичную
        // валидацию POST-данных, и принимай его как параметр метода.
        // Пример: public function processCheckout(CheckoutRequest $request): void
        $address = $this->auth->getPostString('address');
        $phone = $this->auth->getPostString('phone');
        $comment = $this->auth->getPostString('comment');

        $orderData = [
            'address' => $address,
            'phone' => $phone,
            'comment' => $comment
        ];

        $errors = $this->orderService->validateOrderData($orderData);

        if (!empty($errors)) {
            $this->auth->setSessionValue('checkout_errors', $errors);
            $this->auth->setSessionValue('checkout_data', $orderData);
            // Отсутствует return после redirect.
            $this->auth->redirect("/checkout");
        }

        $userId = $this->auth->getUserId();
        $cartTotal = $this->cartService->getCartTotalPrice($userId);

        // Магическое число (Magic Number).
        // Число 100 «зашито» прямо в код без объяснений — это нарушение читаемости и поддерживаемости.
        // Вынеси его в именованную константу, например в конфиг или в класс Order:
        //   const MIN_ORDER_TOTAL = 100;
        // Тогда условие станет: if ($cartTotal <= self::MIN_ORDER_TOTAL)
        if ($cartTotal <= 100) {
            $errors['total'] = "Сумма заказа должна быть более 100 рублей. Сейчас: " . $cartTotal . " руб.";
            $this->auth->setSessionValue('checkout_errors', $errors);
            $this->auth->setSessionValue('checkout_data', $orderData);
            $this->auth->redirect("/checkout");
            return;
        }

        try {
            $order = $this->orderService->createOrderFromCart($userId, $orderData);

            // Нестрогое сравнение (==) вместо строгого (===).
            // При использовании == PHP применяет приведение типов, что может дать неожиданный результат.
            // Например, объект с методом __toString() вернувший "0" будет равен null при ==.
            // PSR и best practices требуют строгого сравнения: if ($order === null)
            if ($order == null) {
                throw new \Exception("Ошибка при создании заказа");
            }

            $this->auth->unsetSessionValue('checkout_errors');
            $this->auth->unsetSessionValue('checkout_data');

            $orderDetails = $order->getDetails();
            $address = $order->getAddress();
            $phone = $order->getPhone();
            $comment = $order->getComment();

            // Та же проблема с прямым require_once вместо render().
            // Дополнительно: переменные $orderDetails, $address и т.д. передаются во view
            // неявно через область видимости — это плохая практика. Используй render() с явной
            require_once __DIR__ . '/../Views/order_success.php';
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), [
                'user_id' => $userId,
                'cart_total' => $cartTotal,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            $errors['general'] = $e->getMessage();
            $this->auth->setSessionValue('checkout_errors', $errors);
            // Отсутствует return после redirect (хотя здесь это последняя строка catch-блока,
            // добавь return для единообразия и явности намерения).
            $this->auth->redirect("/checkout");
        }
    }

    public function clearCart(): void
    {
        if (!$this->auth->isPostRequest()) {
            // Отсутствует return после redirect.
            $this->auth->redirect("/cart");
        }

        $this->auth->requireAuth();

        $userId = $this->auth->getUserId();

        if ($this->cartService->clearCart($userId)) {
            $this->auth->setSessionValue('success_message', "Корзина успешно очищена");
        } else {
            $this->auth->setSessionValue('error_message', "Ошибка при очистке корзины");
        }

        $this->auth->redirect("/cart");
    }

    /**
     * AJAX-метод для очистки корзины
     */
    public function ajaxClearCart(): void
    {
        // Дублирование кода (нарушение принципа DRY — Don't Repeat Yourself).
        // Блоки проверки авторизации и метода запроса (isGuest + isPostRequest) повторяются
        // во всех четырёх AJAX-методах: ajaxClearCart, ajaxIncreaseProduct, ajaxDecreaseProduct, ajaxRemoveItem.
        // Вынеси эту логику в приватный вспомогательный метод, например:
        //   private function validateAjaxRequest(): bool
        header('Content-Type: application/json; charset=utf-8');

        if ($this->auth->isGuest()) {
            echo json_encode([
                'success' => false,
                'error' => 'Необходимо авторизоваться'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (!$this->auth->isPostRequest()) {
            echo json_encode([
                'success' => false,
                'error' => 'Неверный метод запроса'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $userId = $this->auth->getUserId();

        try {
            $result = $this->cartService->clearCart($userId);

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'cart_count' => 0,
                    'cart_total' => 0,
                    'is_empty' => true,
                    'message' => 'Корзина успешно очищена'
                ], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Ошибка при очистке корзины'
                ], JSON_UNESCAPED_UNICODE);
            }
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * AJAX-метод для увеличения количества товара
     */
    public function ajaxIncreaseProduct(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($this->auth->isGuest()) {
            echo json_encode([
                'success' => false,
                'error' => 'Необходимо авторизоваться'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (!$this->auth->isPostRequest()) {
            echo json_encode([
                'success' => false,
                'error' => 'Неверный метод запроса'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Непоследовательность архитектуры (Inconsistency).
        // Не-AJAX версии этих методов (increaseProduct, decreaseProduct) принимают UpdateCartRequest,
        // который инкапсулирует чтение и валидацию product_id.
        // AJAX-версии читают POST напрямую — это нарушает единообразие.
        // Решение: используй тот же UpdateCartRequest и здесь, либо создай отдельный AjaxCartRequest.
        $productId = (int)($this->auth->getPostParam('product_id', 0));

        if ($productId <= 0) {
            echo json_encode([
                'success' => false,
                'error' => 'Неверный ID товара'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $userId = $this->auth->getUserId();
        $currentAmount = $this->cartService->getCurrentAmount($userId, $productId);
        $newAmount = $currentAmount + 1;

        try {
            $result = $this->cartService->updateItem($userId, $productId, $newAmount);

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
                echo json_encode([
                    'success' => false,
                    'error' => 'Ошибка при обновлении корзины'
                ], JSON_UNESCAPED_UNICODE);
            }
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * AJAX-метод для уменьшения количества товара
     */
    public function ajaxDecreaseProduct(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($this->auth->isGuest()) {
            echo json_encode([
                'success' => false,
                'error' => 'Необходимо авторизоваться'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (!$this->auth->isPostRequest()) {
            echo json_encode([
                'success' => false,
                'error' => 'Неверный метод запроса'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Непоследовательность архитектуры — см. комментарий в ajaxIncreaseProduct.
        $productId = (int)($this->auth->getPostParam('product_id', 0));

        if ($productId <= 0) {
            echo json_encode([
                'success' => false,
                'error' => 'Неверный ID товара'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $userId = $this->auth->getUserId();
        $currentAmount = $this->cartService->getCurrentAmount($userId, $productId);
        $newAmount = max(1, $currentAmount - 1);

        if ($newAmount === $currentAmount) {
            echo json_encode([
                'success' => true,
                // Вызовы сервисных методов прямо внутри json_encode — плохая читаемость.
                // Если getCartTotalAmount() или getCartTotalPrice() выбросит исключение,
                // оно не будет поймано, так как мы находимся вне блока try/catch.
                // Вынеси вызовы в переменные перед json_encode, как это сделано ниже в try-блоке.
                'cart_count' => $this->cartService->getCartTotalAmount($userId),
                'cart_total' => $this->cartService->getCartTotalPrice($userId),
                'product_id' => $productId,
                'new_amount' => $newAmount
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $result = $this->cartService->updateItem($userId, $productId, $newAmount);

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
                echo json_encode([
                    'success' => false,
                    'error' => 'Ошибка при обновлении корзины'
                ], JSON_UNESCAPED_UNICODE);
            }
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * AJAX-метод для удаления товара из корзины
     */
    public function ajaxRemoveItem(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($this->auth->isGuest()) {
            echo json_encode([
                'success' => false,
                'error' => 'Необходимо авторизоваться'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (!$this->auth->isPostRequest()) {
            echo json_encode([
                'success' => false,
                'error' => 'Неверный метод запроса'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $productId = (int)($this->auth->getPostParam('product_id', 0));

        if ($productId <= 0) {
            echo json_encode([
                'success' => false,
                'error' => 'Неверный ID товара'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $userId = $this->auth->getUserId();

        try {
            $result = $this->cartService->removeItem($userId, $productId);

            if ($result) {
                $cartTotalAmount = $this->cartService->getCartTotalAmount($userId);
                $cartTotalPrice = $this->cartService->getCartTotalPrice($userId);
                $isEmpty = $this->cartService->isCartEmpty($userId);

                echo json_encode([
                    'success' => true,
                    'cart_count' => $cartTotalAmount,
                    'cart_total' => $cartTotalPrice,
                    'is_empty' => $isEmpty,
                    'product_id' => $productId
                ], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Ошибка при удалении товара'
                ], JSON_UNESCAPED_UNICODE);
            }
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function increaseProduct(UpdateCartRequest $request): void
    {
        if (!$this->auth->isPostRequest()) {
            $this->auth->redirect("/cart");
        }

        $this->auth->requireAuth();

        $productId = $request->getProductId();

        if ($productId <= 0) {
            $this->auth->redirect("/catalog");
        }

        $currentAmount = $this->cartService->getCurrentAmount($this->auth->getUserId(), $productId);

        $this->cartService->updateItem($this->auth->getUserId(), $productId, $currentAmount + 1);

        $this->auth->redirect("/cart");
    }

    public function decreaseProduct(UpdateCartRequest $request): void
    {
        if (!$this->auth->isPostRequest()) {
            $this->auth->redirect("/cart");
        }

        $this->auth->requireAuth();

        $productId = $request->getProductId();

        if ($productId <= 0) {
            $this->auth->redirect("/catalog");
        }

        $currentAmount = $this->cartService->getCurrentAmount($this->auth->getUserId(), $productId);

        $newAmount = max(1, $currentAmount - 1);

        if ($newAmount !== $currentAmount) {
            $this->cartService->updateItem($this->auth->getUserId(), $productId, $newAmount);
        }

        $this->auth->redirect("/cart");
    }
}