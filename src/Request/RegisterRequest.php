<?php

declare(strict_types=1);

namespace Request;

class RegisterRequest extends Request
{
    protected function validate(): void
    {
        $name = $this->getString('name');
        $email = $this->getString('email');
        $password = $this->getString('password');
        $passwordRepeat = $this->getString('passwordRepeat');

        if (empty($name)) {
            $this->errors['name'] = 'Введите имя';
        } elseif (strlen($name) < 2) {
            $this->errors['name'] = 'Имя должно быть не меньше 2 символов';
        }

        if (empty($email)) {
            $this->errors['email'] = 'Введите email';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Неверный формат email';
        }

        if (empty($password)) {
            $this->errors['password'] = 'Введите пароль';
        } elseif (strlen($password) < 6) {
            $this->errors['password'] = 'Пароль должен быть минимум 6 символов';
        }

        if (empty($passwordRepeat)) {
            $this->errors['passwordRepeat'] = 'Подтвердите пароль';
        } elseif ($password !== $passwordRepeat) {
            $this->errors['passwordRepeat'] = 'Пароли не совпадают';
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

    public function getPasswordRepeat(): string
    {
        return $this->getString('passwordRepeat');
    }
}