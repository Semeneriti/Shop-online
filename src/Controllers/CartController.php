<?php
namespace Controllers;

use Request\UpdateCartRequest;
use Services\CartService;
use Services\OrderService;

class CartController extends BaseController
{
    private CartService $cartService;
    private OrderService $orderService;

    public function __construct()
    {
        parent::__construct();
        $this->cartService = new CartService();
        $this->orderService = new OrderService();
    }

    public function showCart(): void
    {
        $this->auth->requireAuth();
        $cartData = $this->cartService->getCartData($this->auth->getUserId());

        $successMessage = $this->auth->getSessionValue('success_message');
        $errorMessage = $this->auth->getSessionValue('error_message');

        $this->auth->unsetSessionValue('success_message');
        $this->auth->unsetSessionValue('error_message');

        require_once __DIR__ . '/../Views/cart.php';
    }

    public function showCheckout(): void
    {
        $this->auth->requireAuth();

        if ($this->cartService->isCartEmpty($this->auth->getUserId())) {
            $this->auth->redirect("/cart");
        }

        $cartData = $this->cartService->getCartData($this->auth->getUserId());
        $errors = $this->auth->getSessionValue('checkout_errors', []);
        $formData = $this->auth->getSessionValue('checkout_data', []);

        $this->auth->unsetSessionValue('checkout_errors');
        $this->auth->unsetSessionValue('checkout_data');

        require_once __DIR__ . '/../Views/checkout.php';
    }

    public function processCheckout(): void
    {
        if (!$this->auth->isPostRequest()) {
            $this->auth->redirect("/checkout");
        }

        $this->auth->requireAuth();

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
            $this->auth->redirect("/checkout");
        }

        $userId = $this->auth->getUserId();
        $cartTotal = $this->cartService->getCartTotalPrice($userId);

        if ($cartTotal <= 100) {
            $errors['total'] = "Сумма заказа должна быть более 100 рублей. Сейчас: " . $cartTotal . " руб.";
            $this->auth->setSessionValue('checkout_errors', $errors);
            $this->auth->setSessionValue('checkout_data', $orderData);
            $this->auth->redirect("/checkout");
            return;
        }

        try {
            $order = $this->orderService->createOrderFromCart($userId, $orderData);

            if ($order == null) {
                throw new \Exception("Ошибка при создании заказа");
            }

            $this->auth->unsetSessionValue('checkout_errors');
            $this->auth->unsetSessionValue('checkout_data');

            $orderDetails = $order->getDetails();
            $address = $order->getAddress();
            $phone = $order->getPhone();
            $comment = $order->getComment();

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
            $this->auth->redirect("/checkout");
        }
    }

    public function clearCart(): void
    {
        if (!$this->auth->isPostRequest()) {
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