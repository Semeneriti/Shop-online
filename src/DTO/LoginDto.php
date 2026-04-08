<?php

declare(strict_types=1);

namespace DTO;

class LoginDto
{
    private string $email;
    private string $password;
    private bool $remember;

    public function __construct(string $email, string $password, bool $remember = false)
    {
        $this->email = $email;
        $this->password = $password;
        $this->remember = $remember;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRemember(): bool
    {
        return $this->remember;
    }

    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
            'remember' => $this->remember
        ];
    }
}