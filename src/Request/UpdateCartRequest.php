<?php

declare(strict_types=1);


namespace Request; // Пространство имен для классов-запросов

// Класс UpdateCartRequest - отвечает за валидацию и получение данных из формы обновления корзины
// Используется при увеличении/уменьшении количества товара в корзине
// Наследуется от базового класса Request
class UpdateCartRequest extends Request
{
    /**
     * Метод валидации - вызывается автоматически в конструкторе родительского класса
     * Проверяет, что ID товара корректен
     */
    protected function validate(): void
    {
        // Получаем ID товара из формы (поле 'product_id') и преобразуем в число
        $productId = $this->getInt('product_id');

        // Проверка: ID товара должен быть положительным числом
        if ($productId <= 0) {
            $this->errors['product_id'] = 'Укажите корректный ID товара';
        }
    }

    /**
     * Возвращает ID товара из формы
     * @return int
     */
    public function getProductId(): int
    {
        return $this->getInt('product_id');
    }
}