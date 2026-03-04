<?php
namespace Models; // Модель - класс для работы с данными в базе данных

// Класс Cart - модель корзины пользователя, наследуется от Model
class Cart extends Model
{
    // Приватные свойства - доступны только внутри класса
    private int $userId;      // ID пользователя, которому принадлежит корзина
    private array $items = []; // Массив товаров в корзине

    /**
     * Конструктор - вызывается при создании объекта корзины
     * @param int $userId - ID пользователя
     */
    public function __construct(int $userId)
    {
        parent::__construct(); // Вызываем конструктор родительского класса (подключаем БД)
        $this->userId = $userId; // Сохраняем ID пользователя
        $this->loadItems();      // Загружаем товары пользователя из базы данных
    }

    /**
     * Возвращает имя таблицы в базе данных
     * @return string
     */
    protected static function getTableName(): string
    {
        return 'user_products'; // Таблица для хранения товаров в корзине пользователя
    }

    // ============ ГЕТТЕРЫ ============

    /**
     * Возвращает ID пользователя
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * Возвращает все товары в корзине
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Возвращает общее количество товаров в корзине (штук)
     * @return int
     */
    public function getTotalAmount(): int
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item['amount']; // Суммируем количество каждого товара
        }
        return $total;
    }

    /**
     * Возвращает общую стоимость корзины
     * @return float
     */
    public function getTotalPrice(): float
    {
        $total = 0.0;
        foreach ($this->items as $item) {
            // Цена товара * количество
            $total += $item['product']->getPrice() * $item['amount'];
        }
        return $total;
    }

    // ============ МЕТОДЫ ДЛЯ РАБОТЫ С КОРЗИНОЙ ============

    /**
     * Добавляет товар в корзину
     * @param int $productId - ID товара
     * @param int $amount - количество
     * @return bool - успешно или нет
     */
    public function addItem(int $productId, int $amount): bool
    {
        // Проверяем, есть ли уже такой товар в корзине
        foreach ($this->items as $key => $item) {
            if ($item['product']->getId() === $productId) {
                // Если товар уже есть - увеличиваем количество
                $newAmount = $item['amount'] + $amount;
                return $this->updateItem($productId, $newAmount);
            }
        }

        // Добавляем новый товар
        $product = Product::findById($productId); // Ищем товар в базе
        if (!$product) {
            throw new \InvalidArgumentException("Товар не найден");
        }

        // Проверяем, хватает ли товара на складе
        if ($amount > $product->getStock()) {
            throw new \InvalidArgumentException("Недостаточно товара на складе");
        }

        $tableName = self::getTableName();
        // SQL запрос: вставляем или обновляем запись (UPSERT)
        $sql = "INSERT INTO {$tableName} (user_id, product_id, amount) 
                VALUES (:user_id, :product_id, :amount) 
                ON CONFLICT (user_id, product_id) 
                DO UPDATE SET amount = EXCLUDED.amount";

        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            ':user_id' => $this->userId,
            ':product_id' => $productId,
            ':amount' => $amount
        ]);

        if ($result) {
            $this->loadItems(); // Перезагружаем корзину, чтобы получить актуальные данные
        }

        return $result;
    }

    /**
     * Обновляет количество товара в корзине
     * @param int $productId - ID товара
     * @param int $amount - новое количество
     * @return bool - успешно или нет
     */
    public function updateItem(int $productId, int $amount): bool
    {
        // Если количество меньше или равно 0 - удаляем товар из корзины
        if ($amount <= 0) {
            return $this->removeItem($productId);
        }

        $product = Product::findById($productId);
        if (!$product) {
            throw new \InvalidArgumentException("Товар не найден");
        }

        // Проверяем, хватает ли товара на складе
        if ($amount > $product->getStock()) {
            throw new \InvalidArgumentException("Недостаточно товара на складе");
        }

        $tableName = self::getTableName();
        $sql = "UPDATE {$tableName} 
                SET amount = :amount 
                WHERE user_id = :user_id AND product_id = :product_id";

        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            ':user_id' => $this->userId,
            ':product_id' => $productId,
            ':amount' => $amount
        ]);

        if ($result) {
            $this->loadItems(); // Перезагружаем корзину
        }

        return $result;
    }

    /**
     * Удаляет товар из корзины
     * @param int $productId - ID товара
     * @return bool - успешно или нет
     */
    public function removeItem(int $productId): bool
    {
        $tableName = self::getTableName();
        $sql = "DELETE FROM {$tableName} 
                WHERE user_id = :user_id AND product_id = :product_id";

        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            ':user_id' => $this->userId,
            ':product_id' => $productId
        ]);

        if ($result) {
            $this->loadItems(); // Перезагружаем корзину
        }

        return $result;
    }

    /**
     * Очищает всю корзину пользователя
     * @return bool - успешно или нет
     */
    public function clear(): bool
    {
        $tableName = self::getTableName();
        $sql = "DELETE FROM {$tableName} WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([':user_id' => $this->userId]);

        if ($result) {
            $this->items = []; // Очищаем массив товаров
        }

        return $result;
    }

    /**
     * Проверяет, пуста ли корзина
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    // ============ ВСПОМОГАТЕЛЬНЫЕ МЕТОДЫ ============

    /**
     * Загружает товары пользователя из базы данных
     * Вызывается в конструкторе и после каждого изменения корзины
     */
    private function loadItems(): void
    {
        $this->items = []; // Очищаем текущий массив

        $tableName = self::getTableName();
        // SQL запрос: получаем все товары пользователя с информацией о продуктах
        $sql = "SELECT p.*, up.amount, up.id as cart_item_id 
                FROM {$tableName} up 
                JOIN products p ON up.product_id = p.id 
                WHERE up.user_id = :user_id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['user_id' => $this->userId]);

        // Для каждой записи создаем объект товара и добавляем в массив
        while ($data = $stmt->fetch()) {
            $product = Product::fromArray($data); // Создаем объект Product из данных
            $this->items[] = [
                'cart_item_id' => $data['cart_item_id'], // ID записи в корзине
                'product' => $product,                    // Объект товара
                'amount' => (int) $data['amount'],        // Количество
                'total_price' => $product->getPrice() * (int) $data['amount'] // Общая цена за эту позицию
            ];
        }
    }

    /**
     * Статический метод для получения корзины пользователя
     * @param int $userId - ID пользователя
     * @return Cart
     */
    public static function getByUserId(int $userId): Cart
    {
        return new self($userId);
    }

    /**
     * Метод для оформления заказа из корзины
     * Проверяет наличие товаров, уменьшает остатки и очищает корзину
     * @return array - данные для создания заказа
     */
    public function checkout(): array
    {
        if ($this->isEmpty()) {
            throw new \RuntimeException("Корзина пуста");
        }

        // Проверяем доступность всех товаров на складе
        foreach ($this->items as $item) {
            if ($item['amount'] > $item['product']->getStock()) {
                throw new \RuntimeException(
                    "Товар '{$item['product']->getName()}' недоступен в требуембом количестве"
                );
            }
        }

        $orderItems = [];

        // Резервируем товары (уменьшаем остатки на складе) и готовим данные для заказа
        foreach ($this->items as $item) {
            $product = $item['product'];
            $product->decreaseStock($item['amount']); // Уменьшаем остаток на складе

            // Добавляем товар в список для заказа
            $orderItems[] = [
                'product_id' => $product->getId(),
                'product_name' => $product->getName(),
                'amount' => $item['amount'],
                'price' => $product->getPrice(),
                'total' => $item['total_price']
            ];
        }

        // Очищаем корзину после оформления заказа
        $this->clear();

        // Возвращаем данные для создания заказа
        return [
            'user_id' => $this->userId,
            'items' => $orderItems,
            'total_amount' => $this->getTotalAmount(), // Общее количество товаров
            'total_price' => $this->getTotalPrice(),   // Общая стоимость
            'created_at' => date('Y-m-d H:i:s')        // Текущая дата и время
        ];
    }

    /**
     * Преобразует объект корзины в массив
     * @return array
     */
    public function toArray(): array
    {
        $itemsArray = [];
        foreach ($this->items as $item) {
            $itemsArray[] = [
                'cart_item_id' => $item['cart_item_id'],
                'product' => $item['product']->toArray(), // Преобразуем товар в массив
                'amount' => $item['amount'],
                'total_price' => $item['total_price']
            ];
        }

        return [
            'user_id' => $this->userId,
            'items' => $itemsArray,
            'total_amount' => $this->getTotalAmount(),
            'total_price' => $this->getTotalPrice(),
            'is_empty' => $this->isEmpty()
        ];
    }
}