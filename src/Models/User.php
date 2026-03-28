<?php
namespace Models; // Модель - класс для работы с данными в базе данных

// Класс User - модель пользователя, наследуется от Model
class User extends Model
{
    // Приватные свойства - хранят данные пользователя
    private ?int $id;              // ID пользователя (null для нового пользователя)
    private string $name;          // Имя пользователя
    private string $email;         // Email пользователя (логин)
    private string $passwordHash;  // Хеш пароля (не сам пароль!)
    private ?\DateTime $createdAt; // Дата регистрации
    private ?\DateTime $updatedAt; // Дата последнего обновления

    /**
     * Конструктор - создает объект пользователя
     * @param string $name - Имя
     * @param string $email - Email
     * @param string $passwordHash - Хеш пароля
     * @param int|null $id - ID (для существующих пользователей)
     * @param string|null $createdAt - Дата регистрации (для существующих)
     * @param string|null $updatedAt - Дата обновления (для существующих)
     */
    public function __construct(
        string $name,
        string $email,
        string $passwordHash,
        ?int $id = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        parent::__construct(); // Вызываем конструктор родителя (подключаем БД)

        // Сохраняем все переданные значения
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->passwordHash = $passwordHash;

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
        return 'users'; // Таблица для хранения пользователей
    }

    // ============ ГЕТТЕРЫ ============

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    // ============ СТАТИЧЕСКИЕ МЕТОДЫ ДЛЯ РАБОТЫ С БД ============

    /**
     * Находит пользователя по email
     * @param string $email - Email пользователя
     * @return User|null - объект пользователя или null, если не найден
     */
    public static function findByEmail(string $email): ?User
    {
        $pdo = self::getConnection(); // Получаем соединение с БД
        $tableName = self::getTableName();

        // Подготавливаем запрос с параметром :email
        $stmt = $pdo->prepare("SELECT * FROM {$tableName} WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $data = $stmt->fetch();

        if (!$data) {
            return null; // Пользователь не найден
        }

        return self::fromArray($data); // Создаем объект из данных
    }

    /**
     * Находит пользователя по ID
     * @param int $id - ID пользователя
     * @return User|null - объект пользователя или null, если не найден
     */
    public static function findById(int $id): ?User
    {
        $pdo = self::getConnection();
        $tableName = self::getTableName();

        $stmt = $pdo->prepare("SELECT * FROM {$tableName} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return self::fromArray($data);
    }

    // ============ МЕТОДЫ ДЛЯ СОХРАНЕНИЯ В БД ============

    /**
     * Сохраняет пользователя в базу данных
     * Если есть ID - обновляет, если нет - создает нового
     * @return bool - успешно или нет
     */
    public function save(): bool
    {
        if ($this->id) {
            return $this->update(); // Обновляем существующего
        } else {
            return $this->create(); // Создаем нового
        }
    }

    /**
     * Создает нового пользователя в базе данных
     * @return bool
     */
    private function create(): bool
    {
        $tableName = self::getTableName();

        // SQL запрос: вставляем нового пользователя
        // RETURNING id, created_at, updated_at - просим базу вернуть созданные данные
        $sql = "INSERT INTO {$tableName} (name, email, password, created_at, updated_at) 
                VALUES (:name, :email, :password, NOW(), NOW()) 
                RETURNING id, created_at, updated_at";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':name' => $this->name,
            ':email' => $this->email,
            ':password' => $this->passwordHash
        ]);

        // Получаем данные созданной записи
        $result = $stmt->fetch();
        $this->id = $result['id'];
        $this->createdAt = new \DateTime($result['created_at']);
        $this->updatedAt = new \DateTime($result['updated_at']);

        return true;
    }

    /**
     * Обновляет данные существующего пользователя
     * @return bool
     */
    private function update(): bool
    {
        $tableName = self::getTableName();

        // SQL запрос: обновляем данные пользователя по ID
        // RETURNING updated_at - получаем новую дату обновления
        $sql = "UPDATE {$tableName} 
                SET name = :name, email = :email, password = :password, updated_at = NOW()
                WHERE id = :id
                RETURNING updated_at";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':name' => $this->name,
            ':email' => $this->email,
            ':password' => $this->passwordHash,
            ':id' => $this->id
        ]);

        $result = $stmt->fetch();
        $this->updatedAt = new \DateTime($result['updated_at']);

        return true;
    }

    // ============ МЕТОДЫ ДЛЯ РАБОТЫ С ПРОФИЛЕМ ============

    /**
     * Обновляет профиль пользователя
     * @param string $name - Новое имя
     * @param string $email - Новый email
     * @param string|null $password - Новый пароль (если меняется)
     * @return bool
     */
    public function updateProfile(string $name, string $email, ?string $password = null): bool
    {
        // Обновляем свойства объекта
        $this->name = $name;
        $this->email = $email;

        // Если передан новый пароль - хешируем его
        if ($password) {
            $this->passwordHash = password_hash($password, PASSWORD_DEFAULT);
        }

        // Сохраняем изменения в БД
        return $this->update();
    }

    /**
     * Проверяет, правильный ли пароль ввел пользователь
     * @param string $password - Пароль для проверки (не хеш)
     * @return bool
     */
    public function verifyPassword(string $password): bool
    {
        // password_verify - встроенная функция PHP для проверки пароля
        return password_verify($password, $this->passwordHash);
    }

    // ============ ПРЕОБРАЗОВАНИЕ ДАННЫХ ============

    /**
     * Создает объект User из массива данных (из результата запроса)
     * @param array $data
     * @return User
     */
    public static function fromArray(array $data): User
    {
        return new self(
            $data['name'],
            $data['email'],
            $data['password'], // В базе хранится хеш пароля
            $data['id'] ?? null,
            $data['created_at'] ?? null,
            $data['updated_at'] ?? null
        );
    }

    /**
     * Преобразует объект пользователя в массив (для передачи в шаблон)
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->createdAt ? $this->createdAt->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updatedAt ? $this->updatedAt->format('Y-m-d H:i:s') : null
        ];
    }
}