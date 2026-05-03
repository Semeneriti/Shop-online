<?php

declare(strict_types=1);

namespace Request;

class AddProductRequest extends Request
{
    protected function validate(): void
    {
        $productId = $this->getInt('product-id');
        $amount = $this->getInt('amount');

        if ($productId <= 0) {
            $this->errors['product-id'] = 'Укажите корректный ID товара';
        }

        if ($amount <= 0) {
            $this->errors['amount'] = 'Укажите корректное количество';
        }
    }

    public function getProductId(): int
    {
        return $this->getInt('product-id');
    }

    public function getAmount(): int
    {
        return $this->getInt('amount');
    }
}
