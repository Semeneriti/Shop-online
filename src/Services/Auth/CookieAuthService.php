<?php

declare(strict_types=1);

namespace Services\Auth;

use Models\User;

class CookieAuthService implements AuthInterface
{
    private ?int $userId = null;
    private ?string $userName = null;
    private ?User $user = null;
    private array $cookieData = [];

    public function __construct()
    {
        $this->initCookie();
        $this->initUser();
    }

    private function initCookie(): void
    {
        $this->cookieData = $_COOKIE;

        if (isset($this->cookieData['user_id'])) {
            $this->userId = (int)$this->cookieData['user_id'];
        }

        if (isset($this->cookieData['user_name'])) {
            $this->userName = $this->cookieData['user_name'];
        }
    }

    private function initUser(): void
    {
        if ($this->userId) {
            $this->user = User::findById($this->userId);
        }
    }

    public function requireAuth(): void
    {
        if (!$this->userId || !$this->user) {
            $this->redirect("/login");
        }
    }

    public function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }

    public function setSessionValue(string $key, $value): void
    {
        setcookie($key, $value, time() + 3600 * 24 * 30, '/');
        $this->cookieData[$key] = $value;
    }

    public function getSessionValue(string $key, $default = null)
    {
        return $this->cookieData[$key] ?? $default;
    }

    public function unsetSessionValue(string $key): void
    {
        setcookie($key, '', time() - 3600, '/');
        unset($this->cookieData[$key]);
    }

    public function isPostRequest(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    public function getPostParam(string $key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    public function getPostInt(string $key, int $default = 0): int
    {
        return (int)($this->getPostParam($key, $default));
    }

    public function getPostString(string $key, string $default = ''): string
    {
        return htmlspecialchars(trim($this->getPostParam($key, $default)));
    }

    public function login(User $user): void
    {
        setcookie('user_id', $user->getId(), time() + 3600 * 24 * 30, '/');
        setcookie('user_name', $user->getName(), time() + 3600 * 24 * 30, '/');

        $this->userId = $user->getId();
        $this->userName = $user->getName();
        $this->user = $user;
        $this->cookieData['user_id'] = $user->getId();
        $this->cookieData['user_name'] = $user->getName();
    }

    public function logout(): void
    {
        setcookie('user_id', '', time() - 3600, '/');
        setcookie('user_name', '', time() - 3600, '/');

        $this->userId = null;
        $this->userName = null;
        $this->user = null;
        unset($this->cookieData['user_id']);
        unset($this->cookieData['user_name']);
    }

    public function isGuest(): bool
    {
        return !$this->userId || !$this->user;
    }

    public function getCurrentUser(): ?User
    {
        return $this->user;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getUserName(): ?string
    {
        return $this->userName;
    }
}
