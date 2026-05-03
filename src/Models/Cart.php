<?php

declare(strict_types=1);

namespace Models;

class Cart extends Model
{
    private int $userId;
    private array $items = [];

    public function __construct(int $userId)
    {
        parent::__construct();
        $this->userId = $userId;
        $this->loadItems();
    }

    protected static function getTableName(): string
    {
        return 'user_products';
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getTotalAmount(): int
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item['amount'];
        }
        return $total;
    }

    public function getTotalPrice(): float
    {
        $total = 0.0;
        foreach ($this->items as $item) {
            $total += $item['product']->getPrice() * $item['amount'];
        }
        return $total;
    }

    public function addItem(int $productId, int $amount): bool
    {
        foreach ($this->items as $key => $item) {
            if ($item['product']->getId() === $productId) {
                $newAmount = $item['amount'] + $amount;
                return $this->updateItem($productId, $newAmount);
            }
        }

        $product = Product::findById($productId);
        if (!$product) {
            throw new \InvalidArgumentException("Товар не найден");
        }

        if ($amount > $product->getStock()) {
            throw new \InvalidArgumentException("Недостаточно товара на складе");
        }

        $tableName = self::getTableName();
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
            $this->loadItems();
        }

        return $result;
    }

    public function updateItem(int $productId, int $amount): bool
    {
        if ($amount <= 0) {
            return $this->removeItem($productId);
        }

        $product = Product::findById($productId);
        if (!$product) {
            throw new \InvalidArgumentException("Товар не найден");
        }

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
            $this->loadItems();
        }

        return $result;
    }

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
            $this->loadItems();
        }

        return $result;
    }

    public function clear(): bool
    {
        $tableName = self::getTableName();
        $sql = "DELETE FROM {$tableName} WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([':user_id' => $this->userId]);

        if ($result) {
            $this->items = [];
        }

        return $result;
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function getItemProductIds(): array
    {
        $productIds = [];
        foreach ($this->items as $item) {
            $productIds[] = $item['product']->getId();
        }
        return $productIds;
    }

    public function getItemAmount(int $productId): int
    {
        foreach ($this->items as $item) {
            if ($item['product']->getId() === $productId) {
                return $item['amount'];
            }
        }
        return 0;
    }

    private function loadItems(): void
    {
        $this->items = [];

        $tableName = self::getTableName();

        $sql = "SELECT 
                    up.id as cart_item_id,
                    up.amount,
                    p.id as product_id,
                    p.name as product_name,
                    p.description as product_description,
                    p.price as product_price,
                    p.stock as product_stock,
                    p.image_url as product_image_url,
                    p.created_at as product_created_at,
                    p.updated_at as product_updated_at
                FROM {$tableName} up 
                INNER JOIN products p ON up.product_id = p.id 
                WHERE up.user_id = :user_id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['user_id' => $this->userId]);

        while ($row = $stmt->fetch()) {
            $product = new Product(
                $row['product_name'],
                $row['product_description'],
                (float)$row['product_price'],
                (int)$row['product_stock'],
                (int)$row['product_id'],
                $row['product_created_at'],
                $row['product_updated_at'],
                $row['product_image_url']
            );

            $this->items[] = [
                'cart_item_id' => $row['cart_item_id'],
                'product' => $product,
                'amount' => (int)$row['amount'],
                'total_price' => $product->getPrice() * (int)$row['amount']
            ];
        }
    }

    public static function getByUserId(int $userId): Cart
    {
        return new self($userId);
    }

    public function toArray(): array
    {
        $itemsArray = [];
        foreach ($this->items as $item) {
            $itemsArray[] = [
                'cart_item_id' => $item['cart_item_id'],
                'product' => $item['product']->toArray(),
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

