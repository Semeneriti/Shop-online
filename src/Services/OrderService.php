<?php

declare(strict_types=1);

namespace Services;

use Models\Order;
use Models\Cart;
use DTO\OrderCreateDto;

class OrderService
{
    private const MIN_ORDER_TOTAL = 100;

    public function createOrderFromCart(int $userId, array $orderData): ?Order
    {
        $cart = new Cart($userId);

        if ($cart->isEmpty()) {
            throw new \RuntimeException("Корзина пуста");
        }

        $cartTotal = $cart->getTotalPrice();

        if ($cartTotal <= self::MIN_ORDER_TOTAL) {
            throw new \RuntimeException(
                "Сумма заказа должна быть более " . self::MIN_ORDER_TOTAL . " рублей. Сейчас: " . $cartTotal . " руб."
            );
        }

        $checkoutData = $cart->checkout();

        $orderDto = new OrderCreateDto(
            $userId,
            $orderData['address'],
            $orderData['phone'],
            $checkoutData['items'],
            $checkoutData['total_price'],
            $orderData['comment'] ?? null
        );

        return Order::createFromCart($orderDto);
    }

    public function getOrderById(int $orderId): ?Order
    {
        return Order::findById($orderId);
    }

    public function getOrdersByUserId(int $userId): array
    {
        return Order::findByUserId($userId);
    }

    public function getOrderDetails(int $orderId): array
    {
        $order = Order::findById($orderId);
        if (!$order) {
            return [];
        }
        return $order->getDetails();
    }
}
