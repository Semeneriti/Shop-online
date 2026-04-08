<?php

declare(strict_types=1);

namespace Models;

use DTO\OrderCreateDto;

class Order extends Model
{
    private ?int $id;
    private int $userId;
    private string $address;
    private string $phone;
    private ?string $comment;
    private float $totalPrice;
    private string $status;
    private \DateTime $createdAt;
    private \DateTime $updatedAt;
    private array $items = [];

    public function __construct(
        int $userId,
        string $address,
        string $phone,
        float $totalPrice,
        ?string $comment = null,
        ?int $id = null,
        ?string $status = 'новый',
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        parent::__construct();

        $this->id = $id;
        $this->userId = $userId;
        $this->address = $address;
        $this->phone = $phone;
        $this->comment = $comment;
        $this->totalPrice = $totalPrice;
        $this->status = $status;

        $this->createdAt = $createdAt ? new \DateTime($createdAt) : new \DateTime();
        $this->updatedAt = $updatedAt ? new \DateTime($updatedAt) : new \DateTime();
    }

    protected static function getTableName(): string
    {
        return 'orders';
    }

    public function getId(): ?int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getAddress(): string { return $this->address; }
    public function getPhone(): string { return $this->phone; }
    public function getComment(): ?string { return $this->comment; }
    public function getTotalPrice(): float { return $this->totalPrice; }
    public function getStatus(): string { return $this->status; }
    public function getCreatedAt(): \DateTime { return $this->createdAt; }
    public function getUpdatedAt(): \DateTime { return $this->updatedAt; }

    public function getItems(): array
    {
        if (empty($this->items) && $this->id) {
            $this->loadItems();
        }
        return $this->items;
    }

    public static function findById(int $id): ?Order
    {
        $pdo = self::getConnection();
        $tableName = self::getTableName();

        $stmt = $pdo->prepare("SELECT * FROM {$tableName} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        $order = self::fromArray($data);
        $order->loadItems();

        return $order;
    }

    public static function findByUserId(int $userId): array
    {
        $pdo = self::getConnection();
        $tableName = self::getTableName();

        $sql = "SELECT 
                    o.*,
                    COUNT(op.id) as items_count,
                    COALESCE(SUM(op.amount), 0) as total_items
                FROM {$tableName} o 
                LEFT JOIN order_products op ON o.id = op.order_id 
                WHERE o.user_id = :user_id 
                GROUP BY o.id 
                ORDER BY o.created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        $orders = [];
        while ($data = $stmt->fetch()) {
            $order = self::fromArray($data);
            $orders[] = [
                'order' => $order,
                'items_count' => (int)$data['items_count'],
                'total_items' => (int)$data['total_items']
            ];
        }

        return $orders;
    }

    public static function createFromCart(OrderCreateDto $dto): ?Order
    {
        $pdo = self::getConnection();

        try {
            $pdo->beginTransaction();

            $tableName = self::getTableName();
            $sql = "INSERT INTO {$tableName} (user_id, address, phone, comment, total_price, status, created_at, updated_at) 
                    VALUES (:user_id, :address, :phone, :comment, :total_price, :status, NOW(), NOW())
                    RETURNING id, created_at, updated_at";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':user_id' => $dto->getUserId(),
                ':address' => $dto->getAddress(),
                ':phone' => $dto->getPhone(),
                ':comment' => $dto->getComment(),
                ':total_price' => $dto->getTotalPrice(),
                ':status' => 'новый'
            ]);

            $result = $stmt->fetch();
            $orderId = $result['id'];

            foreach ($dto->getItems() as $item) {
                $sql = 'INSERT INTO order_products (order_id, product_id, amount, price) 
                        VALUES (:order_id, :product_id, :amount, :price)';

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':order_id' => $orderId,
                    ':product_id' => $item['product_id'],
                    ':amount' => $item['amount'],
                    ':price' => $item['price']
                ]);
            }

            $pdo->commit();

            return self::findById($orderId);

        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    private function loadItems(): void
    {
        if (!$this->id) {
            return;
        }

        $sql = "SELECT 
                    op.id as order_product_id,
                    op.order_id,
                    op.product_id,
                    op.amount,
                    op.price,
                    p.name as product_name,
                    p.description as product_description,
                    p.stock as product_stock,
                    p.image_url as product_image_url
                FROM order_products op 
                INNER JOIN products p ON op.product_id = p.id 
                WHERE op.order_id = :order_id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['order_id' => $this->id]);

        $this->items = [];
        while ($row = $stmt->fetch()) {
            $this->items[] = [
                'order_product_id' => $row['order_product_id'],
                'product_id' => $row['product_id'],
                'product_name' => $row['product_name'],
                'product_description' => $row['product_description'],
                'amount' => (int)$row['amount'],
                'price' => (float)$row['price'],
                'total' => (float)$row['price'] * (int)$row['amount']
            ];
        }
    }

    public function getDetails(): array
    {
        $items = $this->getItems();

        $formattedItems = [];
        foreach ($items as $item) {
            $formattedItems[] = [
                'product_id' => $item['product_id'],
                'product_name' => $item['product_name'],
                'amount' => $item['amount'],
                'price' => $item['price'],
                'total' => $item['total']
            ];
        }

        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'address' => $this->address,
            'phone' => $this->phone,
            'comment' => $this->comment,
            'total_price' => $this->totalPrice,
            'status' => $this->status,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
            'items' => $formattedItems,
            'items_count' => count($formattedItems)
        ];
    }

    public static function fromArray(array $data): Order
    {
        return new self(
            (int)$data['user_id'],
            $data['address'],
            $data['phone'],
            (float)$data['total_price'],
            $data['comment'] ?? null,
            $data['id'] ?? null,
            $data['status'] ?? 'новый',
            $data['created_at'] ?? null,
            $data['updated_at'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'address' => $this->address,
            'phone' => $this->phone,
            'comment' => $this->comment,
            'total_price' => $this->totalPrice,
            'status' => $this->status,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
            'items_count' => count($this->getItems())
        ];
    }
}