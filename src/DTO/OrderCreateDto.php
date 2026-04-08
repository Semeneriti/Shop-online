<?php

declare(strict_types=1);


namespace DTO; // DTO - Data Transfer Object (Объект передачи данных). Используется для передачи данных между слоями приложения.

// Класс OrderCreateDto - используется для передачи данных при создании нового заказа
// Содержит всю информацию, необходимую для оформления заказа
class OrderCreateDto
{
    // Приватные свойства - доступны только внутри класса
    private int $userId;           // ID пользователя, который оформляет заказ
    private string $address;        // Адрес доставки
    private string $phone;          // Номер телефона для связи
    private ?string $comment;       // Комментарий к заказу (может быть null, если комментария нет)
    private array $items;           // Массив товаров в заказе
    private float $totalPrice;      // Общая стоимость заказа

    /**
     * Конструктор - вызывается при создании объекта DTO
     * @param int $userId - ID пользователя
     * @param string $address - Адрес доставки
     * @param string $phone - Номер телефона
     * @param array $items - Список товаров (каждый товар содержит product_id, amount, price)
     * @param float $totalPrice - Общая сумма заказа
     * @param string|null $comment - Комментарий к заказу (необязательный параметр)
     */
    public function __construct(
        int $userId,
        string $address,
        string $phone,
        array $items,
        float $totalPrice,
        ?string $comment = null
    ) {
        // Сохраняем переданные значения в свойства объекта
        $this->userId = $userId;
        $this->address = $address;
        $this->phone = $phone;
        $this->items = $items;
        $this->totalPrice = $totalPrice;
        $this->comment = $comment;
    }

    /**
     * Геттер для userId - получает ID пользователя
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * Геттер для address - получает адрес доставки
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * Геттер для phone - получает номер телефона
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * Геттер для comment - получает комментарий к заказу
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * Геттер для items - получает список товаров в заказе
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Геттер для totalPrice - получает общую стоимость заказа
     * @return float
     */
    public function getTotalPrice(): float
    {
        return $this->totalPrice;
    }

    /**
     * Преобразует объект DTO в массив
     * Удобно для передачи данных в базу данных или для логирования
     * @return array
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,        // ID пользователя
            'address' => $this->address,        // Адрес доставки
            'phone' => $this->phone,            // Телефон
            'comment' => $this->comment,        // Комментарий (может быть null)
            'items' => $this->items,            // Товары в заказе
            'total_price' => $this->totalPrice  // Общая сумма
        ];
    }
}
