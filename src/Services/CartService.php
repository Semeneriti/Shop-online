<?php

declare(strict_types=1);

namespace Services;

use DTO\AddToCartDto;
use Models\Cart;
use Models\Product;

class CartService
{
    private array $cartCache = [];

    private function getCart(int $userId): Cart
    {
        if (!isset($this->cartCache[$userId])) {
            $this->cartCache[$userId] = new Cart($userId);
        }
        return $this->cartCache[$userId];
    }

    private function invalidateCartCache(int $userId): void
    {
        unset($this->cartCache[$userId]);
    }

    public function addItem(AddToCartDto $dto): bool
    {
        $product = Product::findById($dto->getProductId());
        if (!$product) {
            throw new \InvalidArgumentException("Товар не найден");
        }

        $cart = $this->getCart($dto->getUserId());
        $currentAmount = $this->getCurrentAmount($dto->getUserId(), $dto->getProductId());
        $newAmount = $currentAmount + $dto->getAmount();

        if ($newAmount > $product->getStock()) {
            throw new \InvalidArgumentException("Недостаточно товара на складе. Доступно: " . $product->getStock() . " шт.");
        }

        $result = $cart->addItem($dto->getProductId(), $newAmount);
        $this->invalidateCartCache($dto->getUserId());

        return $result;
    }

    public function updateItem(int $userId, int $productId, int $amount): bool
    {
        $cart = $this->getCart($userId);
        $result = $cart->updateItem($productId, $amount);
        $this->invalidateCartCache($userId);
        return $result;
    }

    public function increaseItem(int $userId, int $productId): bool
    {
        $cart = $this->getCart($userId);
        $currentAmount = $this->getCurrentAmount($userId, $productId);
        $newAmount = $currentAmount + 1;

        $product = Product::findById($productId);
        if ($product && $newAmount > $product->getStock()) {
            throw new \InvalidArgumentException("Недостаточно товара на складе");
        }

        $result = $cart->updateItem($productId, $newAmount);
        $this->invalidateCartCache($userId);
        return $result;
    }

    public function decreaseItem(int $userId, int $productId): bool
    {
        $cart = $this->getCart($userId);
        $currentAmount = $this->getCurrentAmount($userId, $productId);
        $newAmount = max(1, $currentAmount - 1);

        if ($newAmount === $currentAmount) {
            return true;
        }

        $result = $cart->updateItem($productId, $newAmount);
        $this->invalidateCartCache($userId);
        return $result;
    }

    public function removeItem(int $userId, int $productId): bool
    {
        $cart = $this->getCart($userId);
        $result = $cart->removeItem($productId);
        $this->invalidateCartCache($userId);
        return $result;
    }

    public function clearCart(int $userId): bool
    {
        $cart = $this->getCart($userId);
        $result = $cart->clear();
        $this->invalidateCartCache($userId);
        return $result;
    }

    public function getCartItems(int $userId): array
    {
        return $this->getCart($userId)->getItems();
    }

    public function getCartTotalPrice(int $userId): float
    {
        return $this->getCart($userId)->getTotalPrice();
    }

    public function getCartTotalAmount(int $userId): int
    {
        return $this->getCart($userId)->getTotalAmount();
    }

    public function isEmpty(int $userId): bool
    {
        return $this->getCart($userId)->isEmpty();
    }

    public function getCartData(int $userId): array
    {
        return $this->getCart($userId)->toArray();
    }

    public function getCurrentAmount(int $userId, int $productId): int
    {
        $cart = $this->getCart($userId);
        foreach ($cart->getItems() as $item) {
            $product = $item['product'];
            $productIdFromItem = is_array($product) ? ($product['id'] ?? 0) : $product->getId();
            if ($productIdFromItem === $productId) {
                return $item['amount'];
            }
        }
        return 0;
    }
}