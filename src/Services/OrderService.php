<?php
namespace Services;

use Models\Order;
use Models\Cart;
use DTO\OrderCreateDto;

class OrderService
{
    public function createOrderFromCart(int $userId, array $orderData): ?Order
    {
        $cart = new Cart($userId);

        if ($cart->isEmpty()) {
            throw new \RuntimeException("Корзина пуста");
        }

        $checkoutData = $cart->checkout();

        $dto = new OrderCreateDto(
            $userId,
            $orderData['address'],
            $orderData['phone'],
            $checkoutData['items'],
            $checkoutData['total_price'],
            $orderData['comment'] ?? null
        );

        return Order::createFromCart($dto);
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

    public function validateOrderData(array $data): array
    {
        $errors = [];

        if (empty($data['address'])) {
            $errors['address'] = 'Укажите адрес доставки';
        }

        if (empty($data['phone'])) {
            $errors['phone'] = 'Укажите номер телефона';
        } elseif (!preg_match('/^\+?[1-9]\d{1,14}$/', $data['phone'])) {
            $errors['phone'] = 'Неверный формат номера телефона';
        }

        return $errors;
    }
}