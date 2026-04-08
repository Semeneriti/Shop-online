<?php

declare(strict_types=1);

namespace Services\Auth; // Пространство имен для сервисов

use Models\User;

// Импортируем модель пользователя

// Класс SessionAuthService - реализация аутентификации через сессии PHP
// Реализует интерфейс AuthInterface
class SessionAuthService implements AuthInterface
{
    // Приватные свойства для хранения данных
    private array $session = [];      // Ссылка на суперглобальный массив $_SESSION
    private ?int $userId = null;      // ID пользователя
    private ?string $userName = null; // Имя пользователя
    private ?User $user = null;       // Объект пользователя

    /**
     * Конструктор - вызывается при создании объекта
     * Запускает сессию, инициализирует данные и загружает пользователя
     */
    public function __construct()
    {
        $this->startSession(); // Запускаем сессию
        $this->initSession();  // Получаем ссылку на $_SESSION
        $this->initUser();     // Загружаем пользователя из сессии
    }

    /**
     * Запускает сессию, если она еще не запущена
     */
    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Сохраняет ссылку на $_SESSION в свойство $session
     * Используется ссылка (&), чтобы изменения применялись к реальной сессии
     */
    private function initSession(): void
    {
        $this->session = &$_SESSION;
    }

    /**
     * Загружает данные пользователя из сессии
     */
    private function initUser(): void
    {
        // Получаем ID и имя из сессии, если они там есть
        $this->userId = $this->session['userId'] ?? null;
        $this->userName = $this->session['userName'] ?? null;

        // Если есть ID - загружаем пользователя из базы
        if ($this->userId) {
            $this->user = User::findById($this->userId);
        }
    }

    /**
     * Проверяет авторизацию, если нет - редирект на логин
     */
    public function requireAuth(): void
    {
        if (!$this->userId || !$this->user) {
            $this->redirect("/login");
        }
    }

    /**
     * Перенаправляет на другой URL
     */
    public function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }

    /**
     * Сохраняет значение в сессию
     */
    public function setSessionValue(string $key, $value): void
    {
        $this->session[$key] = $value;
    }

    /**
     * Получает значение из сессии
     */
    public function getSessionValue(string $key, $default = null)
    {
        return $this->session[$key] ?? $default;
    }

    /**
     * Удаляет значение из сессии
     */
    public function unsetSessionValue(string $key): void
    {
        unset($this->session[$key]);
    }

    /**
     * Очищает все данные сессии
     */
    private function clearSession(): void
    {
        $this->session = [];
    }

    /**
     * Полностью уничтожает сессию
     */
    private function destroySession(): void
    {
        $this->clearSession(); // Очищаем массив

        // Если используются куки для сессии - удаляем их
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),      // Имя сессионной куки
                '',                  // Пустое значение
                time() - 42000,      // Время в прошлом (удаление)
                $params["path"],     // Параметры куки
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy(); // Уничтожаем сессию на сервере
    }

    /**
     * Проверяет, является ли запрос POST
     */
    public function isPostRequest(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Получает параметр из POST-запроса
     */
    public function getPostParam(string $key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * Получает целое число из POST
     */
    public function getPostInt(string $key, int $default = 0): int
    {
        return (int)($this->getPostParam($key, $default));
    }

    /**
     * Получает строку из POST, обрезает пробелы и экранирует HTML
     */
    public function getPostString(string $key, string $default = ''): string
    {
        return htmlspecialchars(trim($this->getPostParam($key, $default)));
    }

    /**
     * Авторизует пользователя - сохраняет данные в сессию
     */
    public function login(User $user): void
    {
        // Сохраняем ID и имя в сессию
        $this->setSessionValue('userId', $user->getId());
        $this->setSessionValue('userName', $user->getName());

        // Обновляем локальные данные
        $this->userId = $user->getId();
        $this->userName = $user->getName();
        $this->user = $user;
    }

    /**
     * Разлогинивает пользователя - уничтожает сессию
     */
    public function logout(): void
    {
        $this->destroySession(); // Уничтожаем сессию

        // Очищаем локальные данные
        $this->userId = null;
        $this->userName = null;
        $this->user = null;
    }

    /**
     * Проверяет, является ли пользователь гостем (не авторизован)
     */
    public function isGuest(): bool
    {
        return !$this->userId || !$this->user;
    }

    /**
     * Возвращает объект текущего пользователя
     */
    public function getCurrentUser(): ?User
    {
        return $this->user;
    }

    /**
     * Возвращает ID текущего пользователя
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * Возвращает имя текущего пользователя
     */
    public function getUserName(): ?string
    {
        return $this->userName;
    }
}