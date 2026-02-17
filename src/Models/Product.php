<?php
namespace Models;

class Product extends Model
{
    private ?int $id;
    private string $name;
    private string $description;
    private float $price;
    private int $stock;
    private ?string $imageUrl;
    private ?\DateTime $createdAt;
    private ?\DateTime $updatedAt;

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
        parent::__construct();

        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->stock = $stock;
        $this->imageUrl = $imageUrl ?? '/images/no-image.png';
        $this->createdAt = $createdAt ? new \DateTime($createdAt) : null;
        $this->updatedAt = $updatedAt ? new \DateTime($updatedAt) : null;
    }

    protected static function getTableName(): string
    {
        return 'products';
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

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function getImageUrl(): string
    {
        return $this->imageUrl;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function isAvailable(): bool
    {
        return $this->stock > 0;
    }

    public function getReviews(): array
    {
        return Review::findByProductId($this->id);
    }

    public function getAverageRating(): float
    {
        $reviews = $this->getReviews();
        if (empty($reviews)) {
            return 0;
        }

        $sum = 0;
        foreach ($reviews as $review) {
            $sum += $review->getRating();
        }

        return round($sum / count($reviews), 1);
    }

    // Статические методы для работы с БД
    public static function getAll(): array
    {
        $pdo = self::getConnection();
        $tableName = self::getTableName();
        $stmt = $pdo->query("SELECT * FROM {$tableName} ORDER BY id");
        $products = [];

        while ($data = $stmt->fetch()) {
            $products[] = self::fromArray($data);
        }

        return $products;
    }

    public static function findById(int $id): ?Product
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

    // Преобразование из массива - ЭТО САМОЕ ВАЖНОЕ!
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
            $data['image_url'] ?? null  // 👈 ВОТ ЭТА СТРОКА ДОЛЖНА БЫТЬ!
        );
    }

    // Преобразование в массив
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
            'average_rating' => $this->getAverageRating(),
            'reviews_count' => count($this->getReviews())
        ];
    }

    // Метод для обновления количества
    public function decreaseStock(int $quantity): bool
    {
        if ($quantity > $this->stock) {
            throw new \InvalidArgumentException("Недостаточно товара на складе");
        }

        $this->stock -= $quantity;

        $tableName = self::getTableName();
        $sql = "UPDATE {$tableName} SET stock = :stock, updated_at = NOW() WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':stock' => $this->stock,
            ':id' => $this->id
        ]);
    }
}