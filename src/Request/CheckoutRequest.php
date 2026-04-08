<?php

declare(strict_types=1);

namespace Request;

class CheckoutRequest extends Request
{
    private const MIN_ADDRESS_LENGTH = 5;
    private const MIN_PHONE_LENGTH = 10;

    protected function validate(): void
    {
        $address = $this->getString('address');
        $phone = $this->getString('phone');
        $comment = $this->getString('comment');

        // Валидация адреса
        if (empty($address)) {
            $this->errors['address'] = 'Введите адрес доставки';
        } elseif (strlen($address) < self::MIN_ADDRESS_LENGTH) {
            $this->errors['address'] = 'Адрес должен содержать минимум ' . self::MIN_ADDRESS_LENGTH . ' символов';
        }

        // Валидация телефона
        if (empty($phone)) {
            $this->errors['phone'] = 'Введите номер телефона';
        } elseif (strlen($phone) < self::MIN_PHONE_LENGTH) {
            $this->errors['phone'] = 'Введите корректный номер телефона';
        } elseif (!preg_match('/^[\d\s\+\-\(\)]+$/', $phone)) {
            $this->errors['phone'] = 'Номер телефона может содержать только цифры, пробелы и символы + - ( )';
        }

        // Комментарий не обязателен, но если есть - проверяем длину
        if (!empty($comment) && strlen($comment) > 1000) {
            $this->errors['comment'] = 'Комментарий не должен превышать 1000 символов';
        }
    }

    public function getAddress(): string
    {
        return $this->getString('address');
    }

    public function getPhone(): string
    {
        return $this->getString('phone');
    }

    public function getComment(): string
    {
        return $this->getString('comment');
    }

    public function getOrderData(): array
    {
        return [
            'address' => $this->getAddress(),
            'phone' => $this->getPhone(),
            'comment' => $this->getComment()
        ];
    }
}