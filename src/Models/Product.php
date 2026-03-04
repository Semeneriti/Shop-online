<?php

namespace Models; // Модель - класс для работы с данными в базе данных

// Класс Product - модель товара, наследуется от Model
class Product extends Model
{
    // Приватные свойства - хранят данные товара
    private ?int $id;              // ID товара (null для нового товара)
    private string $name;          // Название товара
    private string $description;   // Описание товара
    private float $price;          // Цена товара
    private int $stock;            // Количество на складе
    private ?string $imageUrl;     // URL картинки товара
    private ?\DateTime $createdAt; // Дата создания
    private ?\DateTime $updatedAt; // Дата обновления

    /**
     * Конструктор - создает объект товара
     * @param string $name - Название
     * @param string $description - Описание
     * @param float $price - Цена
     * @param int $stock - Остаток на складе
     * @param int|null $id - ID (для существующих товаров)
     * @param string|null $createdAt - Дата создания (для существующих)
     * @param string|null $updatedAt - Дата обновления (для существующих)
     * @param string|null $imageUrl - URL картинки
     */
    public function __construct(
        string $name,
        string $description,
        float $price,
        int $stock,
        ?int $id = null,
        ?string $createdAt = null,
        ?string $updatedAt = null,
        ?string $imageUrl = null
    ) {
        parent::__construct(); // Вызываем конструктор родителя (подключаем БД)

        // Сохраняем все переданные значения
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->stock = $stock;
        // Если картинка не передана - ставим картинку по умолчанию
        $this->imageUrl = $imageUrl ?? '/images/no-image.png';

        // Преобразуем строки в объекты DateTime, если они переданы
        $this->createdAt = $createdAt ? new \DateTime($createdAt) : null;
        $this->updatedAt = $updatedAt ? new \DateTime($updatedAt) : null;
    }

    /**
     * Возвращает имя таблицы в базе данных
     * @return string
     */
    protected static function getTableName(): string
    {
        return 'products'; // Таблица для хранения товаров
    }

    // ============ ГЕТТЕРЫ ============

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getDescription(): string { return $this->description; }
    public function getPrice(): float { return $this->price; }
    public function getStock(): int { return $this->stock; }
    public function getImageUrl(): string { return $this->imageUrl; }
    public function getCreatedAt(): ?\DateTime { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTime { return $this->updatedAt; }

    /**
     * Проверяет, есть ли товар в наличии
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->stock > 0;
    }

    /**
     * Возвращает все отзывы на этот товар
     * Вызывает статический метод модели Review
     * @return array
     */
    public function getReviews(): array
    {
        return Review::findByProductId($this->id);
    }

    /**
     * Считает среднюю оценку товара на основе всех отзывов
     * @return float
     */
    public function getAverageRating(): float
    {
        // Получаем все отзывы
        $reviews = $this->getReviews();

        // Если отзывов нет, возвращаем 0
        if (empty($reviews)) {
            return 0;
        }

        // Суммируем все оценки
        $sum = 0;
        foreach ($reviews as $review) {
            $sum += $review->getRating();
        }

        // Делим сумму на количество отзывов и округляем до 1 знака
        return round($sum / count($reviews), 1);
    }

    // ============ СТАТИЧЕСКИЕ МЕТОДЫ ДЛЯ РАБОТЫ С БД ============

    /**
     * Получает все товары из базы данных
     * @return array - массив объектов Product
     */
    public static function getAll(): array
    {
        $pdo = self::getConnection(); // Получаем соединение с БД
        $tableName = self::getTableName();

        // Простой запрос: выбираем все товары, сортируем по ID
        $stmt = $pdo->query("SELECT * FROM {$tableName} ORDER BY id");
        $products = [];

        // Для каждой строки создаем объект Product
        while ($data = $stmt->fetch()) {
            $products[] = self::fromArray($data);
        }

        return $products;
    }

    /**
     * Находит товар по ID
     * @param int $id - ID товара
     * @return Product|null - объект товара или null, если не найден
     */
    public static function findById(int $id): ?Product
    {
        $pdo = self::getConnection();
        $tableName = self::getTableName();

        // Подготавливаем запрос с параметром :id
        $stmt = $pdo->prepare("SELECT * FROM {$tableName} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch();

        if (!$data) {
            return null; // Товар не найден
        }

        return self::fromArray($data); // Создаем объект из данных
    }

    // ============ ПРЕОБРАЗОВАНИЕ ДАННЫХ ============

    /**
     * Создает объект Product из массива данных (из результата запроса)
     * @param array $data
     * @return Product
     */
    public static function fromArray(array $data): Product
    {
        return new self(
            $data['name'],
            $data['description'],
            (float) $data['price'],
            (int) $data['stock'],
            $data['id'] ?? null,
            $data['created_at'] ?? null,
            $data['updated_at'] ?? null,
            $data['image_url'] ?? null  // Картинка из базы или null
        );
    }

    /**
     * Преобразует объект товара в массив (для передачи в шаблон)
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'stock' => $this->stock,
            'image_url' => $this->imageUrl,
            'created_at' => $this->createdAt ? $this->createdAt->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updatedAt ? $this->updatedAt->format('Y-m-d H:i:s') : null,
            'average_rating' => $this->getAverageRating(), // Добавляем средний рейтинг
            'reviews_count' => count($this->getReviews())   // Добавляем количество отзывов
        ];
    }

    // ============ МЕТОДЫ ДЛЯ ИЗМЕНЕНИЯ ДАННЫХ ============

    /**
     * Уменьшает количество товара на складе
     * Используется при оформлении заказа
     * @param int $quantity - количество для списания
     * @return bool - успешно или нет
     */
    public function decreaseStock(int $quantity): bool
    {
        // Проверяем, хватает ли товара
        if ($quantity > $this->stock) {
            throw new \InvalidArgumentException("Недостаточно товара на складе");
        }

        // Уменьшаем остаток
        $this->stock -= $quantity;

        // Обновляем запись в базе данных
        $tableName = self::getTableName();
        $sql = "UPDATE {$tableName} SET stock = :stock, updated_at = NOW() WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':stock' => $this->stock,
            ':id' => $this->id
        ]);
    }
}