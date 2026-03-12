<?php

namespace Services\Auth; // Пространство имен для сервисов

use Models\User;

// Импортируем модель пользователя

// Интерфейс AuthInterface - определяет контракт, который должны выполнять все сервисы аутентификации
// Любой класс, реализующий этот интерфейс, обязан иметь все эти методы
interface AuthInterface
{
    /**
     * Проверяет, авторизован ли пользователь
     * Если нет - перенаправляет на страницу входа
     */
    public function requireAuth(): void;

    /**
     * Перенаправляет пользователя на другой URL
     * @param string $url - куда перенаправить
     */
    public function redirect(string $url): void;

    /**
     * Сохраняет значение в сессию (или куки)
     * @param string $key - ключ
     * @param mixed $value - значение
     */
    public function setSessionValue(string $key, $value): void;

    /**
     * Получает значение из сессии (или кук)
     * @param string $key - ключ
     * @param mixed $default - значение по умолчанию
     * @return mixed
     */
    public function getSessionValue(string $key, $default = null);

    /**
     * Удаляет значение из сессии (или кук)
     * @param string $key - ключ
     */
    public function unsetSessionValue(string $key): void;

    /**
     * Проверяет, является ли текущий запрос POST
     * @return bool
     */
    public function isPostRequest(): bool;

    /**
     * Получает параметр из POST-запроса
     * @param string $key - ключ
     * @param mixed $default - значение по умолчанию
     * @return mixed
     */
    public function getPostParam(string $key, $default = null);

    /**
     * Получает параметр из POST и преобразует в целое число
     * @param string $key - ключ
     * @param int $default - значение по умолчанию
     * @return int
     */
    public function getPostInt(string $key, int $default = 0): int;

    /**
     * Получает параметр из POST, обрезает пробелы и экранирует спецсимволы
     * @param string $key - ключ
     * @param string $default - значение по умолчанию
     * @return string
     */
    public function getPostString(string $key, string $default = ''): string;

    /**
     * Авторизует пользователя (сохраняет данные в сессию/куки)
     * @param User $user - объект пользователя
     */
    public function login(User $user): void;

    /**
     * Разлогинивает пользователя (удаляет данные из сессии/кук)
     */
    public function logout(): void;

    /**
     * Проверяет, является ли пользователь гостем (не авторизован)
     * @return bool
     */
    public function isGuest(): bool;

    /**
     * Возвращает объект текущего пользователя
     * @return User|null
     */
    public function getCurrentUser(): ?User;

    /**
     * Возвращает ID текущего пользователя
     * @return int|null
     */
    public function getUserId(): ?int;

    /**
     * Возвращает имя текущего пользователя
     * @return string|null
     */
    public function getUserName(): ?string;
}