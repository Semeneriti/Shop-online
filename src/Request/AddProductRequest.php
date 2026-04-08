<?php

declare(strict_types=1);


namespace Request; // Пространство имен для классов-запросов (обработка данных из форм)

// Класс AddProductRequest - отвечает за валидацию и получение данных из формы добавления товара в корзину
// Наследуется от базового класса Request
class AddProductRequest extends Request
{
    /**
     * Метод валидации - вызывается автоматически в конструкторе родительского класса
     * Проверяет правильность введенных данных
     */
    protected function validate(): void
    {
        // Получаем ID товара из формы (поле 'product-id') и преобразуем в число
        $productId = $this->getInt('product-id');

        // Получаем количество товара из формы (поле 'amount') и преобразуем в число
        $amount = $this->getInt('amount');

        // Проверка: ID товара должен быть положительным числом
        if ($productId <= 0) {
            $this->errors['product-id'] = 'Укажите корректный ID товара';
        }

        // Проверка: количество должно быть положительным числом
        if ($amount <= 0) {
            $this->errors['amount'] = 'Укажите корректное количество';
        }
    }

    /**
     * Возвращает ID товара из формы
     * @return int
     */
    public function getProductId(): int
    {
        // Используем метод родительского класса для получения числа
        return $this->getInt('product-id');
    }

    /**
     * Возвращает количество товара из формы
     * @return int
     */
    public function getAmount(): int
    {
        return $this->getInt('amount');
    }
}