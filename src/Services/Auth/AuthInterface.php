<?php

declare(strict_types=1);

namespace Services\Auth;

use Models\User;

interface AuthInterface
{
    public function requireAuth(): void;

    public function redirect(string $url): void;

    public function setSessionValue(string $key, $value): void; // где типизация $value?

    public function getSessionValue(string $key, $default = null); // где типизация $default? mixed или другой тип?

    public function unsetSessionValue(string $key): void;

    public function isPostRequest(): bool;

    public function getPostParam(string $key, $default = null); // где типизация $default?

    public function getPostInt(string $key, int $default = 0): int;

    public function getPostString(string $key, string $default = ''): string;

    public function login(User $user): void;

    public function logout(): void;

    public function isGuest(): bool;

    public function getCurrentUser(): ?User;

    public function getUserId(): ?int;

    public function getUserName(): ?string;
}