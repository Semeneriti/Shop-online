<?php
// DTO - Data Transfer Object (Объект передачи данных)
// Используется для передачи данных между слоями приложения
namespace DTO;

class AddToCartDto
{
    // Приватные свойства - доступны только внутри класса
    private int $userId;      // ID пользователя, который добавляет товар
    private int $productId;    // ID товара, который добавляется в корзину
    private int $amount;       // Количество товара

    /**
     * Конструктор - вызывается при создании объекта DTO
     * @param int $userId - ID пользователя
     * @param int $productId - ID товара
     * @param int $amount - количество
     */
    public function __construct(
        int $userId,
        int $productId,
        int $amount
    ) {
        // Сохраняем переданные значения в свойства объекта
        $this->userId = $userId;
        $this->productId = $productId;
        $this->amount = $amount;
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
     * Геттер для productId - получает ID товара
     * @return int
     */
    public function getProductId(): int
    {
        return $this->productId;
    }

    /**
     * Геттер для amount - получает количество товара
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * Преобразует объект DTO в массив
     * Удобно для передачи данных в базу данных или сериализации
     * @return array
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,      // ID пользователя
            'product_id' => $this->productId, // ID товара
            'amount' => $this->amount         // Количество
        ];
    }
}
