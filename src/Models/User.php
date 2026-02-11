<?php
namespace Models;

class User extends Model
{
    private ?int $id;
    private string $name;
    private string $email;
    private string $passwordHash;
    private ?\DateTime $createdAt;
    private ?\DateTime $updatedAt;

    public function __construct(
        string $name,
        string $email,
        string $passwordHash,
        ?int $id = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        parent::__construct();

        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->createdAt = $createdAt ? new \DateTime($createdAt) : null;
        $this->updatedAt = $updatedAt ? new \DateTime($updatedAt) : null;
    }

    protected static function getTableName(): string
    {
        return 'users';
    }

    // Геттеры
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

    // Статические методы для работы с БД
    public static function findByEmail(string $email): ?User
    {
        $pdo = self::getConnection();
        $tableName = self::getTableName();
        $stmt = $pdo->prepare("SELECT * FROM {$tableName} WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return self::fromArray($data);
    }

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

    public function save(): bool
    {
        if ($this->id) {
            return $this->update();
        } else {
            return $this->create();
        }
    }

    private function create(): bool
    {
        $tableName = self::getTableName();
        $sql = "INSERT INTO {$tableName} (name, email, password, created_at, updated_at) 
                VALUES (:name, :email, :password, NOW(), NOW()) 
                RETURNING id, created_at, updated_at";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':name' => $this->name,
            ':email' => $this->email,
            ':password' => $this->passwordHash
        ]);

        $result = $stmt->fetch();
        $this->id = $result['id'];
        $this->createdAt = new \DateTime($result['created_at']);
        $this->updatedAt = new \DateTime($result['updated_at']);

        return true;
    }

    private function update(): bool
    {
        $tableName = self::getTableName();
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

    public function updateProfile(string $name, string $email, ?string $password = null): bool
    {
        $this->name = $name;
        $this->email = $email;

        if ($password) {
            $this->passwordHash = password_hash($password, PASSWORD_DEFAULT);
        }

        return $this->update();
    }

    // Валидация пароля
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->passwordHash);
    }

    // Преобразование из массива
    public static function fromArray(array $data): User
    {
        return new self(
            $data['name'],
            $data['email'],
            $data['password'],
            $data['id'] ?? null,
            $data['created_at'] ?? null,
            $data['updated_at'] ?? null
        );
    }

    // Преобразование в массив
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