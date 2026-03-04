<?php
namespace Services; // Пространство имен для сервисов

// Импортируем необходимые классы
use Models\Order;           // Модель заказа
use Models\Cart;            // Модель корзины
use DTO\OrderCreateDto;     // DTO для создания заказа

// Класс OrderService - сервис для работы с заказами
// Содержит методы для создания заказов, получения информации о заказах и валидации
class OrderService
{
    /**
     * Создает заказ из корзины пользователя
     * @param OrderCreateDto $dto - объект с данными для заказа (адрес, телефон и т.д.)
     * @return Order|null - созданный заказ или null при ошибке
     * @throws \RuntimeException если корзина пуста
     */
    public function createOrderFromCart(OrderCreateDto $dto): ?Order
    {
        // Получаем корзину пользователя
        $cart = new Cart($dto->getUserId());

        // Проверяем, что корзина не пуста
        if ($cart->isEmpty()) {
            throw new \RuntimeException("Корзина пуста");
        }

        // Оформляем заказ из корзины (уменьшаем остатки, получаем товары)
        $checkoutData = $cart->checkout();

        // Создаем новый DTO с полными данными для заказа
        $orderDto = new OrderCreateDto(
            $dto->getUserId(),              // ID пользователя
            $dto->getAddress(),              // Адрес доставки
            $dto->getPhone(),                // Телефон
            $checkoutData['items'],          // Товары из корзины
            $checkoutData['total_price'],    // Общая стоимость
            $dto->getComment()               // Комментарий
        );

        // Создаем заказ в базе данных и возвращаем его
        return Order::createFromCart($orderDto);
    }

    /**
     * Получает заказ по ID
     * @param int $orderId - ID заказа
     * @return Order|null - объект заказа или null, если не найден
     */
    public function getOrderById(int $orderId): ?Order
    {
        return Order::findById($orderId);
    }

    /**
     * Получает все заказы пользователя
     * @param int $userId - ID пользователя
     * @return array - массив объектов Order
     */
    public function getOrdersByUserId(int $userId): array
    {
        return Order::findByUserId($userId);
    }

    /**
     * Получает детальную информацию о заказе (с товарами)
     * @param int $orderId - ID заказа
     * @return array - массив с данными заказа или пустой массив, если заказ не найден
     */
    public function getOrderDetails(int $orderId): array
    {
        $order = Order::findById($orderId);
        if (!$order) {
            return []; // Заказ не найден
        }
        return $order->getDetails(); // Возвращаем детали заказа
    }

    /**
     * Валидирует данные формы оформления заказа
     * @param array $data - данные из POST (address, phone)
     * @return array - массив ошибок (пустой если ошибок нет)
     */
    public function validateOrderData(array $data): array
    {
        $errors = [];

        // Проверка адреса доставки
        if (empty($data['address'])) {
            $errors['address'] = 'Укажите адрес доставки';
        }

        // Проверка телефона
        if (empty($data['phone'])) {
            $errors['phone'] = 'Укажите номер телефона';
        } elseif (!preg_match('/^\+?[1-9]\d{1,14}$/', $data['phone'])) {
            // Регулярное выражение для проверки формата телефона:
            // ^\+? - может начинаться с плюса (или не начинаться)
            // [1-9] - первая цифра от 1 до 9 (не 0)
            // \d{1,14} - от 1 до 14 цифр
            // $ - конец строки
            $errors['phone'] = 'Неверный формат номера телефона';
        }

        return $errors;
    }
}