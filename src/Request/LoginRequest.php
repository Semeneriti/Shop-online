<?php

declare(strict_types=1);

namespace Request;

class LoginRequest extends Request
{
    protected function validate(): void
    {
        $email = $this->getString('email');
        $password = $this->getString('password');

        if (empty($email)) {
            $this->errors['email'] = 'Введите email';
        }

        if (empty($password)) {
            $this->errors['password'] = 'Введите пароль';
        }
    }

    public function getEmail(): string
    {
        return $this->getString('email');
    }

    public function getPassword(): string
    {
        return $this->getString('password');
    }

    public function getRemember(): bool
    {
        return isset($this->data['remember']) && $this->data['remember'] === 'on';
    }
}
