<?php

declare(strict_types=1);

namespace Services;

use Models\Order;
use Models\Cart;
use Models\Product;
use DTO\OrderCreateDto;

class OrderService
{
    private const MIN_ORDER_TOTAL = 100;

    private function validateCartStock(Cart $cart): void
    {
        foreach ($cart->getItems() as $item) {
            $product = $item['product'];
            $amount = $item['amount'];

            if ($amount > $product->getStock()) {
                throw new \RuntimeException(
                    "Товар '{$product->getName()}' недоступен в требуемом количестве. Доступно: {$product->getStock()} шт."
                );
            }
        }
    }

    private function decreaseStock(Cart $cart): array
    {
        $orderItems = [];

        foreach ($cart->getItems() as $item) {
            $product = $item['product'];
            $amount = $item['amount'];

            $product->decreaseStock($amount);

            $orderItems[] = [
                'product_id' => $product->getId(),
                'product_name' => $product->getName(),
                'amount' => $amount,
                'price' => $product->getPrice(),
                'total' => $product->getPrice() * $amount
            ];
        }

        return $orderItems;
    }

    private function getOrderDataFromCart(Cart $cart, int $userId, array $orderData): OrderCreateDto
    {
        $this->validateCartStock($cart);

        $cartTotal = $cart->getTotalPrice();

        if ($cartTotal <= self::MIN_ORDER_TOTAL) {
            throw new \RuntimeException(
                "Сумма заказа должна быть более " . self::MIN_ORDER_TOTAL . " рублей. Сейчас: " . $cartTotal . " руб."
            );
        }

        $orderItems = $this->decreaseStock($cart);

        return new OrderCreateDto(
            $userId,
            $orderData['address'],
            $orderData['phone'],
            $orderItems,
            $cartTotal,
            $orderData['comment'] ?? null
        );
    }

    public function createOrderFromCart(int $userId, array $orderData): ?Order
    {
        $cart = new Cart($userId);

        if ($cart->isEmpty()) {
            throw new \RuntimeException("Корзина пуста");
        }

        $orderDto = $this->getOrderDataFromCart($cart, $userId, $orderData);

        $order = Order::createFromCart($orderDto);

        $cart->clear();

        return $order;
    }

    public function getOrderById(int $orderId): ?Order
    {
        return Order::findById($orderId);
    }

    public function getOrdersByUserId(int $userId): array
    {
        $ordersData = Order::findByUserId($userId);
        $result = [];

        foreach ($ordersData as $orderData) {
            $result[] = [
                'id' => $orderData['order']->getId(),
                'address' => $orderData['order']->getAddress(),
                'phone' => $orderData['order']->getPhone(),
                'total_price' => $orderData['order']->getTotalPrice(),
                'status' => $orderData['order']->getStatus(),
                'created_at' => $orderData['order']->getCreatedAt()->format('Y-m-d H:i:s'),
                'items_count' => $orderData['items_count'],
                'total_items' => $orderData['total_items']
            ];
        }

        return $result;
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