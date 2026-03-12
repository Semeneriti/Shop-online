<?php
namespace Services\Auth; // Пространство имен для сервисов

// Импортируем необходимые классы
use DTO\AddToCartDto;
use DTO\CartItemDto;
use Models\Cart;

// Модель корзины
// Модель товара
// DTO для обновления товара в корзине
// DTO для добавления товара в корзину

// Класс CartService - сервис для работы с корзиной
// Содержит методы для всех операций с корзиной
class CartService
{
    /**
     * Получает объект корзины для пользователя
     * @param int $userId - ID пользователя
     * @return Cart
     */
    public function getCart(int $userId): Cart
    {
        return new Cart($userId);
    }

    /**
     * Добавляет товар в корзину
     * @param AddToCartDto $dto - объект с данными (userId, productId, amount)
     * @return bool - успешно или нет
     */
    public function addItem(AddToCartDto $dto): bool
    {
        // Создаем корзину для пользователя
        $cart = new Cart($dto->getUserId());
        // Добавляем товар
        return $cart->addItem($dto->getProductId(), $dto->getAmount());
    }

    /**
     * Обновляет количество товара в корзине
     * @param CartItemDto $dto - объект с данными (userId, productId, amount)
     * @return bool - успешно или нет
     */
    public function updateItem(CartItemDto $dto): bool
    {
        $cart = new Cart($dto->getUserId());
        return $cart->updateItem($dto->getProductId(), $dto->getAmount());
    }

    /**
     * Удаляет товар из корзины
     * @param int $userId - ID пользователя
     * @param int $productId - ID товара
     * @return bool - успешно или нет
     */
    public function removeItem(int $userId, int $productId): bool
    {
        $cart = new Cart($userId);
        return $cart->removeItem($productId);
    }

    /**
     * Очищает всю корзину пользователя
     * @param int $userId - ID пользователя
     * @return bool - успешно или нет
     */
    public function clearCart(int $userId): bool
    {
        $cart = new Cart($userId);
        return $cart->clear();
    }

    /**
     * Возвращает все товары в корзине пользователя
     * @param int $userId - ID пользователя
     * @return array - массив товаров
     */
    public function getCartItems(int $userId): array
    {
        $cart = new Cart($userId);
        return $cart->getItems();
    }

    /**
     * Возвращает общую стоимость корзины
     * @param int $userId - ID пользователя
     * @return float
     */
    public function getCartTotalPrice(int $userId): float
    {
        $cart = new Cart($userId);
        return $cart->getTotalPrice();
    }

    /**
     * Возвращает общее количество товаров в корзине (штук)
     * @param int $userId - ID пользователя
     * @return int
     */
    public function getCartTotalAmount(int $userId): int
    {
        $cart = new Cart($userId);
        return $cart->getTotalAmount();
    }

    /**
     * Проверяет, пуста ли корзина
     * @param int $userId - ID пользователя
     * @return bool
     */
    public function isCartEmpty(int $userId): bool
    {
        $cart = new Cart($userId);
        return $cart->isEmpty();
    }

    /**
     * Возвращает все данные корзины в виде массива (для шаблона)
     * @param int $userId - ID пользователя
     * @return array
     */
    public function getCartData(int $userId): array
    {
        $cart = new Cart($userId);
        return $cart->toArray();
    }

    /**
     * Возвращает текущее количество конкретного товара в корзине
     * @param int $userId - ID пользователя
     * @param int $productId - ID товара
     * @return int - количество (0 если товара нет)
     */
    public function getCurrentAmount(int $userId, int $productId): int
    {
        $cart = new Cart($userId);

        // Ищем товар в корзине
        foreach ($cart->getItems() as $item) {
            if ($item['product']->getId() === $productId) {
                return $item['amount']; // Возвращаем количество
            }
        }

        return 0; // Товар не найден
    }

    /**
     * Валидирует данные из формы добавления в корзину
     * @param array $data - данные из POST
     * @return array - массив ошибок (пустой если ошибок нет)
     */
    public function validateAddToCartData(array $data): array
    {
        $errors = [];

        // Проверяем ID товара
        if (empty($data['product-id']) || $data['product-id'] <= 0) {
            $errors['product-id'] = 'Укажите корректный ID товара';
        }

        // Проверяем количество
        if (empty($data['amount']) || $data['amount'] <= 0) {
            $errors['amount'] = 'Укажите корректное количество';
        }

        return $errors;
    }
}