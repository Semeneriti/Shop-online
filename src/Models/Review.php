<?php
namespace Models;

class Review extends Model
{
    private ?int $id;
    private int $productId;
    private int $userId;
    private string $userName;
    private int $rating;
    private string $text;
    private string $createdAt;

    public function __construct(
        int $productId,
        int $userId,
        string $userName,
        int $rating,
        string $text,
        ?int $id = null,
        ?string $createdAt = null
    ) {
        parent::__construct();

        $this->id = $id;
        $this->productId = $productId;
        $this->userId = $userId;
        $this->userName = $userName;
        $this->rating = $rating;
        $this->text = $text;
        $this->createdAt = $createdAt ?: date('Y-m-d H:i:s');
    }

    protected static function getTableName(): string
    {
        return 'reviews';
    }

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

    public static function findByProductId(int $productId): array
    {
        $pdo = self::getConnection();
        $tableName = self::getTableName();
        $stmt = $pdo->prepare("SELECT * FROM {$tableName} WHERE product_id = :product_id ORDER BY created_at DESC");
        $stmt->execute(['product_id' => $productId]);

        $reviews = [];
        while ($data = $stmt->fetch()) {
            $reviews[] = self::fromArray($data);
        }

        return $reviews;
    }

    public function save(): bool
    {
        $tableName = self::getTableName();
        $sql = "INSERT INTO {$tableName} (product_id, user_id, user_name, rating, text, created_at) 
                VALUES (:product_id, :user_id, :user_name, :rating, :text, :created_at)
                RETURNING id";

        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            ':product_id' => $this->productId,
            ':user_id' => $this->userId,
            ':user_name' => $this->userName,
            ':rating' => $this->rating,
            ':text' => $this->text,
            ':created_at' => $this->createdAt
        ]);

        if ($result) {
            $data = $stmt->fetch();
            $this->id = $data['id'];
        }

        return $result;
    }

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