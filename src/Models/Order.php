<?php
namespace Models;

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

    // Геттеры
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getTotalPrice(): float
    {
        return $this->totalPrice;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function getItems(): array
    {
        if (empty($this->items) && $this->id) {
            $this->loadItems();
        }
        return $this->items;
    }

    // Статические методы
    public static function findById(int $id): ?Order
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = :id');
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
        $stmt = $pdo->prepare('
            SELECT o.*, 
                   (SELECT COUNT(*) FROM order_products op WHERE op.order_id = o.id) as items_count
            FROM orders o 
            WHERE o.user_id = :user_id 
            ORDER BY o.created_at DESC
        ');
        $stmt->execute(['user_id' => $userId]);

        $orders = [];
        while ($data = $stmt->fetch()) {
            $order = self::fromArray($data);
            $orders[] = $order;
        }

        return $orders;
    }

    public static function createFromCart(array $cartData): ?Order
    {
        $pdo = self::getConnection();

        try {
            $pdo->beginTransaction();

            // Создаем заказ
            $sql = 'INSERT INTO orders (user_id, address, phone, comment, total_price, status, created_at, updated_at) 
                    VALUES (:user_id, :address, :phone, :comment, :total_price, :status, NOW(), NOW())
                    RETURNING id, created_at, updated_at';

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':user_id' => $cartData['user_id'],
                ':address' => $cartData['address'],
                ':phone' => $cartData['phone'],
                ':comment' => $cartData['comment'],
                ':total_price' => $cartData['total_price'],
                ':status' => 'новый'
            ]);

            $result = $stmt->fetch();
            $orderId = $result['id'];

            // Добавляем товары из корзины
            foreach ($cartData['items'] as $item) {
                $sql = 'INSERT INTO order_products (order_id, product_id, amount, price) 
                        VALUES (:order_id, :product_id, :amount, :price)';

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':order_id' => $orderId,
                    ':product_id' => $item['product']->getId(),
                    ':amount' => $item['amount'],
                    ':price' => $item['product']->getPrice()
                ]);
            }

            $pdo->commit();

            // Получаем созданный заказ
            return self::findById($orderId);

        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    // Вспомогательные методы
    private function loadItems(): void
    {
        if (!$this->id) {
            return;
        }

        $sql = 'SELECT op.*, p.name as product_name 
                FROM order_products op 
                JOIN products p ON op.product_id = p.id 
                WHERE op.order_id = :order_id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['order_id' => $this->id]);

        $this->items = [];
        while ($data = $stmt->fetch()) {
            $this->items[] = [
                'product_id' => $data['product_id'],
                'product_name' => $data['product_name'],
                'amount' => (int)$data['amount'],
                'price' => (float)$data['price']
            ];
        }
    }

    public function getDetails(): array
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
            'items' => $this->getItems(),
            'items_count' => count($this->getItems())
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