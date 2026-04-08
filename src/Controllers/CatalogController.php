<?php

declare(strict_types=1);

namespace Controllers;

use Models\Product;
use Services\CartService;

class CatalogController extends Controller
{
    private CartService $cartService;

    public function __construct()
    {
        parent::__construct();
        $this->cartService = new CartService();
    }

    public function index(): void
    {
        $products = Product::getAll();

        $cartItems = [];
        $cartTotalPrice = 0.0;
        $cartItemsCount = 0;

        if (!$this->auth->isGuest()) {
            $userId = $this->auth->getUserId();
            $cartItems = $this->cartService->getCartItems($userId);
            $cartTotalPrice = $this->cartService->getCartTotalPrice($userId);
            $cartItemsCount = $this->cartService->getCartTotalAmount($userId);
        }

        $successMessage = $this->auth->getSessionValue('success_message');
        $errorMessage = $this->auth->getSessionValue('error_message');

        $this->auth->unsetSessionValue('success_message');
        $this->auth->unsetSessionValue('error_message');

        $this->render('catalog', compact('products', 'cartItems', 'cartTotalPrice', 'cartItemsCount', 'successMessage', 'errorMessage'));
    }
}
