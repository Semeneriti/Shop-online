<?php

declare(strict_types=1);

namespace Request;

class UpdateProfileRequest extends Request
{
    protected function validate(): void
    {
        $name = $this->getString('name');
        $email = $this->getString('email');
        $password = $this->getString('password');
        $passwordConfirm = $this->getString('password_confirm');

        if (empty($name)) {
            $this->errors['name'] = 'Введите имя';
        }

        if (empty($email)) {
            $this->errors['email'] = 'Введите email';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Неверный формат email';
        }

        if (!empty($password)) {
            if (strlen($password) < 6) {
                $this->errors['password'] = 'Пароль должен быть не менее 6 символов';
            } elseif ($password !== $passwordConfirm) {
                $this->errors['password_confirm'] = 'Пароли не совпадают';
            }
        }
    }

    public function getName(): string
    {
        return $this->getString('name');
    }

    public function getEmail(): string
    {
        return $this->getString('email');
    }

    public function getPassword(): string
    {
        return $this->getString('password');
    }

    public function getPasswordConfirm(): string
    {
        return $this->getString('password_confirm');
    }
}