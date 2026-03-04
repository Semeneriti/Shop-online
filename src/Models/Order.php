<?php
namespace Models; // Модель - класс для работы с данными в базе данных

use DTO\OrderCreateDto; // Импортируем DTO для создания заказа

// Класс Order - модель заказа, наследуется от Model
class Order extends Model
{
    // Приватные свойства - хранят данные заказа
    private ?int $id;              // ID заказа (null для нового заказа)
    private int $userId;           // ID пользователя, который сделал заказ
    private string $address;       // Адрес доставки
    private string $phone;         // Телефон для связи
    private ?string $comment;      // Комментарий к заказу (может быть null)
    private float $totalPrice;     // Общая стоимость заказа
    private string $status;        // Статус заказа (новый, оплачен, отправлен и т.д.)
    private \DateTime $createdAt;  // Дата и время создания
    private \DateTime $updatedAt;  // Дата и время последнего обновления
    private array $items = [];      // Товары в заказе (загружаются отдельно)

    /**
     * Конструктор - создает объект заказа
     * @param int $userId - ID пользователя
     * @param string $address - Адрес доставки
     * @param string $phone - Телефон
     * @param float $totalPrice - Общая сумма
     * @param string|null $comment - Комментарий (необязательно)
     * @param int|null $id - ID заказа (для существующих)
     * @param string|null $status - Статус (по умолчанию 'новый')
     * @param string|null $createdAt - Дата создания (для существующих)
     * @param string|null $updatedAt - Дата обновления (для существующих)
     */
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
        parent::__construct(); // Вызываем конструктор родителя (подключаем БД)

        // Сохраняем все переданные значения
        $this->id = $id;
        $this->userId = $userId;
        $this->address = $address;
        $this->phone = $phone;
        $this->comment = $comment;
        $this->totalPrice = $totalPrice;
        $this->status = $status;

        // Преобразуем строки в объекты DateTime или создаем текущую дату
        $this->createdAt = $createdAt ? new \DateTime($createdAt) : new \DateTime();
        $this->updatedAt = $updatedAt ? new \DateTime($updatedAt) : new \DateTime();
    }

    /**
     * Возвращает имя таблицы в базе данных
     * @return string
     */
    protected static function getTableName(): string
    {
        return 'orders'; // Таблица для хранения заказов
    }

    // ============ ГЕТТЕРЫ ============

    public function getId(): ?int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getAddress(): string { return $this->address; }
    public function getPhone(): string { return $this->phone; }
    public function getComment(): ?string { return $this->comment; }
    public function getTotalPrice(): float { return $this->totalPrice; }
    public function getStatus(): string { return $this->status; }
    public function getCreatedAt(): \DateTime { return $this->createdAt; }
    public function getUpdatedAt(): \DateTime { return $this->updatedAt; }

    /**
     * Возвращает товары в заказе
     * Если товары еще не загружены и это существующий заказ - загружает их
     * @return array
     */
    public function getItems(): array
    {
        if (empty($this->items) && $this->id) {
            $this->loadItems(); // Загружаем товары из БД
        }
        return $this->items;
    }

    // ============ СТАТИЧЕСКИЕ МЕТОДЫ ============

    /**
     * Находит заказ по ID
     * @param int $id - ID заказа
     * @return Order|null - объект заказа или null, если не найден
     */
    public static function findById(int $id): ?Order
    {
        $pdo = self::getConnection(); // Получаем соединение с БД
        $tableName = self::getTableName();

        // SQL запрос: выбираем заказ по ID
        $stmt = $pdo->prepare("SELECT * FROM {$tableName} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch();

        if (!$data) {
            return null; // Заказ не найден
        }

        $order = self::fromArray($data); // Создаем объект из данных
        $order->loadItems(); // Загружаем товары заказа

        return $order;
    }

    /**
     * Находит все заказы пользователя
     * @param int $userId - ID пользователя
     * @return array - массив объектов Order
     */
    public static function findByUserId(int $userId): array
    {
        $pdo = self::getConnection();
        $tableName = self::getTableName();

        // SQL запрос: выбираем все заказы пользователя с количеством товаров
        $stmt = $pdo->prepare("
            SELECT o.*, 
                   (SELECT COUNT(*) FROM order_products op WHERE op.order_id = o.id) as items_count
            FROM {$tableName} o 
            WHERE o.user_id = :user_id 
            ORDER BY o.created_at DESC
        ");
        $stmt->execute(['user_id' => $userId]);

        $orders = [];
        while ($data = $stmt->fetch()) {
            $order = self::fromArray($data);
            $orders[] = $order;
        }

        return $orders;
    }

    /**
     * Создает заказ из данных корзины (через DTO)
     * @param OrderCreateDto $dto - объект с данными для заказа
     * @return Order|null - созданный заказ или null при ошибке
     */
    public static function createFromCart(OrderCreateDto $dto): ?Order
    {
        $pdo = self::getConnection();

        try {
            // Начинаем транзакцию - все операции выполнятся вместе или не выполнятся вообще
            $pdo->beginTransaction();

            // 1. Создаем запись в таблице orders
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
            $orderId = $result['id']; // Получаем ID созданного заказа

            // 2. Добавляем товары в таблицу order_products
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

            // Подтверждаем транзакцию - все изменения сохраняются
            $pdo->commit();

            // Получаем и возвращаем созданный заказ
            return self::findById($orderId);

        } catch (\Exception $e) {
            // Если ошибка - откатываем транзакцию (ничего не сохраняется)
            $pdo->rollBack();
            throw $e; // Пробрасываем исключение дальше
        }
    }

    // ============ ВСПОМОГАТЕЛЬНЫЕ МЕТОДЫ ============

    /**
     * Загружает товары, относящиеся к этому заказу
     */
    private function loadItems(): void
    {
        if (!$this->id) {
            return; // У нового заказа нет ID - нечего загружать
        }

        // SQL запрос: получаем все товары заказа с названиями продуктов
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

    /**
     * Возвращает детальную информацию о заказе (с товарами)
     * @return array
     */
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
            'items' => $this->getItems(), // Загружаем товары, если еще не загружены
            'items_count' => count($this->getItems())
        ];
    }

    /**
     * Создает объект Order из массива данных (например, из результата запроса)
     * @param array $data
     * @return Order
     */
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

    /**
     * Преобразует объект заказа в массив (для передачи в шаблон или API)
     * @return array
     */
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
            'items_count' => count($this->getItems()) // Только количество товаров, без деталей
        ];
    }
}