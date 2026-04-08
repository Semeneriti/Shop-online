<?php

declare(strict_types=1);

namespace Request;

class UpdateCartRequest extends Request
{
    protected function validate(): void
    {
        $productId = $this->getInt('product_id');

        if ($productId <= 0) {
            $this->errors['product_id'] = 'Укажите корректный ID товара';
        }
    }

    public function getProductId(): int
    {
        return $this->getInt('product_id');
    }
}