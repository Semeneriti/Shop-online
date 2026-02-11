<?php
namespace Services;

use Models\Cart;
use Models\Product;

class CartService
{
    public function getCart(int $userId): Cart
    {
        return new Cart($userId);
    }

    public function addItem(int $userId, int $productId, int $amount): bool
    {
        $cart = new Cart($userId);
        return $cart->addItem($productId, $amount);
    }

    public function updateItem(int $userId, int $productId, int $amount): bool
    {
        $cart = new Cart($userId);
        return $cart->updateItem($productId, $amount);
    }

    public function removeItem(int $userId, int $productId): bool
    {
        $cart = new Cart($userId);
        return $cart->removeItem($productId);
    }

    public function clearCart(int $userId): bool
    {
        $cart = new Cart($userId);
        return $cart->clear();
    }

    public function getCartItems(int $userId): array
    {
        $cart = new Cart($userId);
        return $cart->getItems();
    }

    public function getCartTotalPrice(int $userId): float
    {
        $cart = new Cart($userId);
        return $cart->getTotalPrice();
    }

    public function getCartTotalAmount(int $userId): int
    {
        $cart = new Cart($userId);
        return $cart->getTotalAmount();
    }

    public function isCartEmpty(int $userId): bool
    {
        $cart = new Cart($userId);
        return $cart->isEmpty();
    }

    public function getCartData(int $userId): array
    {
        $cart = new Cart($userId);
        return $cart->toArray();
    }

    public function getCurrentAmount(int $userId, int $productId): int
    {
        $cart = new Cart($userId);
        foreach ($cart->getItems() as $item) {
            if ($item['product']->getId() === $productId) {
                return $item['amount'];
            }
        }
        return 0;
    }
}