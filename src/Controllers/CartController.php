<?php
namespace Controllers;

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

        try {
            $order = $this->orderService->createOrderFromCart($this->auth->getUserId(), $orderData);

            if (!$order) {
                throw new \RuntimeException("Ошибка при создании заказа");
            }

            $this->auth->unsetSessionValue('checkout_errors');
            $this->auth->unsetSessionValue('checkout_data');

            $orderDetails = $order->getDetails();
            require_once __DIR__ . '/../Views/order_success.php';

        } catch (\InvalidArgumentException $e) {
            $this->auth->setSessionValue('error_message', $e->getMessage());
            $this->auth->redirect("/cart");
        } catch (\RuntimeException $e) {
            $this->auth->setSessionValue('error_message', $e->getMessage());
            $this->auth->redirect("/checkout");
        } catch (\Exception $e) {
            $this->auth->setSessionValue('error_message', "Произошла ошибка при оформлении заказа: " . $e->getMessage());
            $this->auth->redirect("/checkout");
        }
    }

    public function increaseProduct(): void
    {
        if (!$this->auth->isPostRequest()) {
            $this->auth->redirect("/cart");
        }

        $this->auth->requireAuth();

        $productId = $this->auth->getPostInt('product_id');

        if ($productId <= 0) {
            $this->auth->redirect("/catalog");
        }

        $currentAmount = $this->cartService->getCurrentAmount($this->auth->getUserId(), $productId);
        $this->cartService->updateItem($this->auth->getUserId(), $productId, $currentAmount + 1);

        $this->auth->redirect("/catalog");
    }

    public function decreaseProduct(): void
    {
        if (!$this->auth->isPostRequest()) {
            $this->auth->redirect("/cart");
        }

        $this->auth->requireAuth();

        $productId = $this->auth->getPostInt('product_id');

        if ($productId <= 0) {
            $this->auth->redirect("/catalog");
        }

        $currentAmount = $this->cartService->getCurrentAmount($this->auth->getUserId(), $productId);
        $newAmount = max(1, $currentAmount - 1);

        if ($newAmount !== $currentAmount) {
            $this->cartService->updateItem($this->auth->getUserId(), $productId, $newAmount);
        }

        $this->auth->redirect("/catalog");
    }
}