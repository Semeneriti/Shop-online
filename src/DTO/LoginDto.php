<?php

namespace DTO; // DTO - Data Transfer Object (Объект передачи данных). Используется для передачи данных между слоями приложения.

// Класс LoginDto - используется для передачи данных при входе пользователя в систему
// Содержит email, пароль и флаг "запомнить меня"
class LoginDto
{
    // Приватные свойства - доступны только внутри класса
    private string $email;      // Email пользователя (логин)
    private string $password;   // Пароль пользователя (в чистом виде, не хешированный)
    private bool $remember;     // Флаг "запомнить меня" - нужно ли сохранять сессию надолго

    /**
     * Конструктор - вызывается при создании объекта DTO
     * @param string $email - Email пользователя
     * @param string $password - Пароль пользователя
     * @param bool $remember - Запомнить пользователя (true/false), по умолчанию false
     */
    public function __construct(
        string $email,
        string $password,
        bool $remember = false
    ) {
        // Сохраняем переданные значения в свойства объекта
        $this->email = $email;
        $this->password = $password;
        $this->remember = $remember;
    }

    /**
     * Геттер для email - получает email пользователя
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Геттер для password - получает пароль пользователя
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Геттер для remember - получает значение флага "запомнить меня"
     * @return bool
     */
    public function getRemember(): bool
    {
        return $this->remember;
    }

    /**
     * Преобразует объект DTO в массив
     * Удобно для передачи данных в базу данных, логирования или отладки
     * @return array
     */
    public function toArray(): array
    {
        return [
            'email' => $this->email,          // Email пользователя
            'password' => $this->password,     // Пароль пользователя
            'remember' => $this->remember      // Флаг "запомнить меня"
        ];
    }
}
