<?php

namespace DTO;

class OrderCreateDto
{
    private int $userId;
    private string $address;
    private string $phone;
    private ?string $comment;
    private array $items;
    private float $totalPrice;

    public function __construct(
        int $userId,
        string $address,
        string $phone,
        array $items,
        float $totalPrice,
        ?string $comment = null
    ) {
        $this->userId = $userId;
        $this->address = $address;
        $this->phone = $phone;
        $this->items = $items;
        $this->totalPrice = $totalPrice;
        $this->comment = $comment;
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

    public function getItems(): array
    {
        return $this->items;
    }

    public function getTotalPrice(): float
    {
        return $this->totalPrice;
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'address' => $this->address,
            'phone' => $this->phone,
            'comment' => $this->comment,
            'items' => $this->items,
            'total_price' => $this->totalPrice
        ];
    }
}