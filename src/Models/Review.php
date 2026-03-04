<?php
namespace Models; // Модель - класс для работы с данными в базе данных

// Класс Review - модель отзыва, наследуется от Model
class Review extends Model
{
    // СВОЙСТВА КЛАССА - что хранит каждый отзыв
    private ?int $id;           // Уникальный номер отзыва в базе данных
    private int $productId;      // ID товара, к которому относится отзыв
    private int $userId;         // ID пользователя, который написал отзыв
    private string $userName;    // Имя пользователя (храним отдельно, чтобы отзыв не потерялся, если пользователь удалит аккаунт)
    private int $rating;         // Оценка от 1 до 5 звезд
    private string $text;        // Текст отзыва (что написал пользователь)
    private string $createdAt;   // Дата и время создания отзыва

    /**
     * Конструктор - создает объект отзыва
     * @param int $productId - ID товара
     * @param int $userId - ID пользователя
     * @param string $userName - Имя пользователя
     * @param int $rating - Оценка (1-5)
     * @param string $text - Текст отзыва
     * @param int|null $id - ID отзыва (для существующих)
     * @param string|null $createdAt - Дата создания (для существующих)
     */
    public function __construct(
        int $productId,
        int $userId,
        string $userName,
        int $rating,
        string $text,
        ?int $id = null,
        ?string $createdAt = null
    ) {
        parent::__construct(); // Вызываем конструктор родительского класса Model (подключаем БД)

        // Заполняем свойства переданными значениями
        $this->id = $id;
        $this->productId = $productId;
        $this->userId = $userId;
        $this->userName = $userName;
        $this->rating = $rating;
        $this->text = $text;
        // Если дата не передана, ставим текущую
        $this->createdAt = $createdAt ?: date('Y-m-d H:i:s');
    }

    /**
     * Возвращает имя таблицы в базе данных для отзывов
     * @return string
     */
    protected static function getTableName(): string
    {
        return 'reviews'; // Таблица называется "reviews"
    }

    // ============ ГЕТТЕРЫ ============
    // Методы для получения значений свойств, чтобы нельзя было менять свойства напрямую

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getUserName(): string
    {
        return $this->userName;
    }

    public function getRating(): int
    {
        return $this->rating;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    // ============ СТАТИЧЕСКИЕ МЕТОДЫ ============

    /**
     * Находит все отзывы для конкретного товара
     * @param int $productId - ID товара
     * @return array - массив объектов Review
     */
    public static function findByProductId(int $productId): array
    {
        // Получаем соединение с базой данных
        $pdo = self::getConnection();
        $tableName = self::getTableName();

        // Готовим SQL запрос: выбираем все отзывы для товара, сортируем от новых к старым
        $stmt = $pdo->prepare("SELECT * FROM {$tableName} WHERE product_id = :product_id ORDER BY created_at DESC");
        $stmt->execute(['product_id' => $productId]);

        $reviews = [];
        // Для каждой строки из базы создаем объект Review и добавляем в массив
        while ($data = $stmt->fetch()) {
            $reviews[] = self::fromArray($data);
        }

        return $reviews;
    }

    // ============ МЕТОДЫ ДЛЯ РАБОТЫ С БД ============

    /**
     * Сохраняет отзыв в базу данных
     * @return bool - успешно или нет
     */
    public function save(): bool
    {
        $tableName = self::getTableName();

        // SQL запрос на вставку нового отзыва
        // RETURNING id - просим базу вернуть ID созданной записи
        $sql = "INSERT INTO {$tableName} (product_id, user_id, user_name, rating, text, created_at) 
                VALUES (:product_id, :user_id, :user_name, :rating, :text, :created_at)
                RETURNING id";

        $stmt = $this->pdo->prepare($sql);

        // Выполняем запрос с данными из объекта
        $result = $stmt->execute([
            ':product_id' => $this->productId,
            ':user_id' => $this->userId,
            ':user_name' => $this->userName,
            ':rating' => $this->rating,
            ':text' => $this->text,
            ':created_at' => $this->createdAt
        ]);

        // Если запрос успешен, получаем ID новой записи и сохраняем в объект
        if ($result) {
            $data = $stmt->fetch();
            $this->id = $data['id'];
        }

        return $result;
    }

    // ============ ПРЕОБРАЗОВАНИЕ ДАННЫХ ============

    /**
     * Создает объект Review из массива данных (из результата запроса)
     * @param array $data
     * @return Review
     */
    public static function fromArray(array $data): Review
    {
        return new self(
            (int)$data['product_id'],
            (int)$data['user_id'],
            $data['user_name'],
            (int)$data['rating'],
            $data['text'],
            $data['id'] ?? null,
            $data['created_at'] ?? null
        );
    }

    /**
     * Преобразует объект отзыва в массив (для передачи в шаблон)
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->productId,
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'rating' => $this->rating,
            'text' => $this->text,
            'created_at' => $this->createdAt
        ];
    }
}