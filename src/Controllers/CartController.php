<?php

declare(strict_types=1);

namespace Controllers;

use Request\UpdateCartRequest;
use Request\CheckoutRequest;
use Services\CartService;
use Services\OrderService;

class CartController extends Controller
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

        $this->render('cart', compact('cartData', 'successMessage', 'errorMessage'));
    }

    public function showCheckout(): void
    {
        $this->auth->requireAuth();

        if ($this->cartService->isEmpty($this->auth->getUserId())) {
            $this->auth->redirect("/cart");
            return;
        }

        $cartData = $this->cartService->getCartData($this->auth->getUserId());
        $errors = $this->auth->getSessionValue('checkout_errors', []);
        $formData = $this->auth->getSessionValue('checkout_data', []);

        $this->auth->unsetSessionValue('checkout_errors');
        $this->auth->unsetSessionValue('checkout_data');

        $this->render('checkout', compact('cartData', 'errors', 'formData'));
    }

    public function processCheckout(CheckoutRequest $request): void
    {
        if (!$this->auth->isPostRequest()) {
            $this->auth->redirect("/checkout");
            return;
        }

        $this->auth->requireAuth();

        $errors = $request->getErrors();
        $orderData = $request->getOrderData();

        if (!empty($errors)) {
            $this->auth->setSessionValue('checkout_errors', $errors);
            $this->auth->setSessionValue('checkout_data', $orderData);
            $this->auth->redirect("/checkout");
            return;
        }

        $userId = $this->auth->getUserId();

        try {
            $order = $this->orderService->createOrderFromCart($userId, $orderData);

            if ($order === null) {
                throw new \Exception("Ошибка при создании заказа");
            }

            $this->auth->unsetSessionValue('checkout_errors');
            $this->auth->unsetSessionValue('checkout_data');

            $this->render('order_success', [
                'order' => $order,
                'orderDetails' => $order->getDetails(),
                'address' => $order->getAddress(),
                'phone' => $order->getPhone(),
                'comment' => $order->getComment()
            ]);

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), [
                'user_id' => $userId,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            $errors['general'] = $e->getMessage();
            $this->auth->setSessionValue('checkout_errors', $errors);
            $this->auth->redirect("/checkout");
            return;
        }
    }

    public function clearCart(): void
    {
        if (!$this->auth->isPostRequest()) {
            $this->auth->redirect("/cart");
            return;
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

    public function ajaxClearCart(): void
    {
        if (!$this->validateAjaxRequest()) {
            return;
        }

        $userId = $this->auth->getUserId();

        try {
            $result = $this->cartService->clearCart($userId);

            $this->jsonResponse([
                'success' => $result,
                'cart_count' => 0,
                'cart_total' => 0,
                'is_empty' => true,
                'message' => $result ? 'Корзина успешно очищена' : 'Ошибка при очистке корзины'
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function ajaxIncreaseProduct(UpdateCartRequest $request): void
    {
        if (!$this->validateAjaxRequest()) {
            return;
        }

        $productId = $request->getProductId();

        if ($productId <= 0) {
            $this->jsonResponse(['success' => false, 'error' => 'Неверный ID товара']);
            return;
        }

        $userId = $this->auth->getUserId();

        try {
            $result = $this->cartService->increaseItem($userId, $productId);

            if ($result) {
                $cartTotalAmount = $this->cartService->getCartTotalAmount($userId);
                $cartTotalPrice = $this->cartService->getCartTotalPrice($userId);

                $this->jsonResponse([
                    'success' => true,
                    'cart_count' => $cartTotalAmount,
                    'cart_total' => $cartTotalPrice,
                    'product_id' => $productId
                ]);
            } else {
                $this->jsonResponse(['success' => false, 'error' => 'Ошибка при обновлении корзины']);
            }
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function ajaxDecreaseProduct(UpdateCartRequest $request): void
    {
        if (!$this->validateAjaxRequest()) {
            return;
        }

        $productId = $request->getProductId();

        if ($productId <= 0) {
            $this->jsonResponse(['success' => false, 'error' => 'Неверный ID товара']);
            return;
        }

        $userId = $this->auth->getUserId();

        try {
            $result = $this->cartService->decreaseItem($userId, $productId);

            if ($result) {
                $cartTotalAmount = $this->cartService->getCartTotalAmount($userId);
                $cartTotalPrice = $this->cartService->getCartTotalPrice($userId);

                $this->jsonResponse([
                    'success' => true,
                    'cart_count' => $cartTotalAmount,
                    'cart_total' => $cartTotalPrice,
                    'product_id' => $productId
                ]);
            } else {
                $this->jsonResponse(['success' => false, 'error' => 'Ошибка при обновлении корзины']);
            }
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function ajaxRemoveItem(UpdateCartRequest $request): void
    {
        if (!$this->validateAjaxRequest()) {
            return;
        }

        $productId = $request->getProductId();

        if ($productId <= 0) {
            $this->jsonResponse(['success' => false, 'error' => 'Неверный ID товара']);
            return;
        }

        $userId = $this->auth->getUserId();

        try {
            $result = $this->cartService->removeItem($userId, $productId);

            if ($result) {
                $cartTotalAmount = $this->cartService->getCartTotalAmount($userId);
                $cartTotalPrice = $this->cartService->getCartTotalPrice($userId);
                $isEmpty = $this->cartService->isEmpty($userId);

                $this->jsonResponse([
                    'success' => true,
                    'cart_count' => $cartTotalAmount,
                    'cart_total' => $cartTotalPrice,
                    'is_empty' => $isEmpty,
                    'product_id' => $productId
                ]);
            } else {
                $this->jsonResponse(['success' => false, 'error' => 'Ошибка при удалении товара']);
            }
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function increaseProduct(UpdateCartRequest $request): void
    {
        if (!$this->auth->isPostRequest()) {
            $this->auth->redirect("/cart");
            return;
        }

        $this->auth->requireAuth();

        $productId = $request->getProductId();

        if ($productId <= 0) {
            $this->auth->redirect("/catalog");
            return;
        }

        $this->cartService->increaseItem($this->auth->getUserId(), $productId);
        $this->auth->redirect("/cart");
    }

    public function decreaseProduct(UpdateCartRequest $request): void
    {
        if (!$this->auth->isPostRequest()) {
            $this->auth->redirect("/cart");
            return;
        }

        $this->auth->requireAuth();

        $productId = $request->getProductId();

        if ($productId <= 0) {
            $this->auth->redirect("/catalog");
            return;
        }

        $this->cartService->decreaseItem($this->auth->getUserId(), $productId);
        $this->auth->redirect("/cart");
    }

    public function removeItem(UpdateCartRequest $request): void
    {
        if (!$this->auth->isPostRequest()) {
            $this->auth->redirect("/cart");
            return;
        }

        $this->auth->requireAuth();

        $productId = $request->getProductId();

        if ($productId <= 0) {
            $this->auth->redirect("/catalog");
            return;
        }

        $this->cartService->removeItem($this->auth->getUserId(), $productId);
        $this->auth->redirect("/cart");
    }
}
