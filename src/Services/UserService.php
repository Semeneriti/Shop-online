<?php

declare(strict_types=1);

namespace Services;

use Models\User;

class UserService
{
    public function register(string $name, string $email, string $password): User
    {
        $existingUser = User::findByEmail($email);
        if ($existingUser) {
            throw new \DomainException('Пользователь с таким email уже существует');
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $user = new User($name, $email, $passwordHash);
        $user->save();

        return $user;
    }

    public function authenticate(string $email, string $password): User
    {
        $user = User::findByEmail($email);

        if (!$user || !$user->verifyPassword($password)) {
            throw new \DomainException('Неверный email или пароль');
        }

        return $user;
    }

    public function getUserProfileData(int $userId): array
    {
        $user = User::findById($userId);
        return $user ? $user->toArray() : [];
    }

    public function updateProfile(int $userId, string $name, string $email, ?string $password = null): User
    {
        $user = User::findById($userId);
        if (!$user) {
            throw new \DomainException('Пользователь не найден');
        }

        if ($email !== $user->getEmail()) {
            $existingUser = User::findByEmail($email);
            if ($existingUser) {
                throw new \DomainException('Этот email уже используется другим пользователем');
            }
        }

        $user->updateProfile($name, $email, $password);
        return $user;
    }
}
