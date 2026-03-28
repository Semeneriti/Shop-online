<?php

namespace Services\Auth; // Пространство имен для сервисов

use Models\User;

// Импортируем модель пользователя

// Класс CookieAuthService - реализация аутентификации через куки (в браузере)
// Реализует интерфейс AuthInterface
class CookieAuthService implements AuthInterface
{
    // Приватные свойства для хранения данных пользователя
    private ?int $userId = null;          // ID пользователя
    private ?string $userName = null;      // Имя пользователя
    private ?User $user = null;            // Объект пользователя
    private array $cookieData = [];        // Данные из кук

    /**
     * Конструктор - вызывается при создании объекта
     * Инициализирует данные из кук и загружает пользователя
     */
    public function __construct()
    {
        $this->initCookie(); // Читаем куки
        $this->initUser();   // Загружаем пользователя по ID из кук
    }

    /**
     * Читает данные из суперглобального массива $_COOKIE
     */
    private function initCookie(): void
    {
        $this->cookieData = $_COOKIE; // Сохраняем все куки

        // Если есть кука user_id - сохраняем ID
        if (isset($this->cookieData['user_id'])) {
            $this->userId = (int)$this->cookieData['user_id'];
        }

        // Если есть кука user_name - сохраняем имя
        if (isset($this->cookieData['user_name'])) {
            $this->userName = $this->cookieData['user_name'];
        }
    }

    /**
     * Загружает объект пользователя из базы по ID
     */
    private function initUser(): void
    {
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
     * Сохраняет значение в куку (на 30 дней)
     */
    public function setSessionValue(string $key, $value): void
    {
        // 3600 * 24 * 30 = 30 дней в секундах
        setcookie($key, $value, time() + 3600 * 24 * 30, '/');
        $this->cookieData[$key] = $value; // Обновляем локальные данные
    }

    /**
     * Получает значение из кук
     */
    public function getSessionValue(string $key, $default = null)
    {
        return $this->cookieData[$key] ?? $default;
    }

    /**
     * Удаляет значение из кук
     */
    public function unsetSessionValue(string $key): void
    {
        // Устанавливаем время в прошлом, чтобы кука удалилась
        setcookie($key, '', time() - 3600, '/');
        unset($this->cookieData[$key]); // Удаляем из локальных данных
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
     * Авторизует пользователя - сохраняет ID и имя в куки
     */
    public function login(User $user): void
    {
        // Сохраняем в куки на 30 дней
        setcookie('user_id', $user->getId(), time() + 3600 * 24 * 30, '/');
        setcookie('user_name', $user->getName(), time() + 3600 * 24 * 30, '/');

        // Обновляем локальные данные
        $this->userId = $user->getId();
        $this->userName = $user->getName();
        $this->user = $user;
        $this->cookieData['user_id'] = $user->getId();
        $this->cookieData['user_name'] = $user->getName();
    }

    /**
     * Разлогинивает пользователя - удаляет куки
     */
    public function logout(): void
    {
        // Устанавливаем время в прошлое, чтобы куки удалились
        setcookie('user_id', '', time() - 3600, '/');
        setcookie('user_name', '', time() - 3600, '/');

        // Очищаем локальные данные
        $this->userId = null;
        $this->userName = null;
        $this->user = null;
        unset($this->cookieData['user_id']);
        unset($this->cookieData['user_name']);
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