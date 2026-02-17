<?php

namespace Services;

use Models\User;

class AuthService
{
    private array $session = [];
    private ?int $userId = null;
    private ?string $userName = null;
    private ?User $user = null;

    public function __construct()
    {
        $this->startSession();
        $this->initSession();
        $this->initUser();
    }

    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function initSession(): void
    {
        $this->session = &$_SESSION;
    }

    private function initUser(): void
    {
        $this->userId = $this->session['userId'] ?? null;
        $this->userName = $this->session['userName'] ?? null;

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
        $this->session[$key] = $value;
    }

    public function getSessionValue(string $key, $default = null)
    {
        return $this->session[$key] ?? $default;
    }

    public function unsetSessionValue(string $key): void
    {
        unset($this->session[$key]);
    }

    private function clearSession(): void
    {
        $this->session = [];
    }

    private function destroySession(): void
    {
        $this->clearSession();

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();
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
        $this->setSessionValue('userId', $user->getId());
        $this->setSessionValue('userName', $user->getName());

        $this->userId = $user->getId();
        $this->userName = $user->getName();
        $this->user = $user;
    }

    public function logout(): void
    {
        $this->destroySession();

        $this->userId = null;
        $this->userName = null;
        $this->user = null;
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