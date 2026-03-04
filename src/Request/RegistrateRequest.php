<?php

namespace Request; // Пространство имен для классов-запросов

// Класс RegistrateRequest - отвечает за валидацию и получение данных из формы регистрации
// Наследуется от базового класса Request
class RegistrateRequest extends Request
{
    /**
     * Метод валидации - вызывается автоматически в конструкторе родительского класса
     * Проверяет все поля формы регистрации
     */
    protected function validate(): void
    {
        // Получаем данные из формы и очищаем от пробелов
        $name = $this->getString('name');                 // Имя пользователя
        $email = $this->getString('email');               // Email
        $password = $this->getString('password');         // Пароль
        $passwordRepeat = $this->getString('passwordRepeat'); // Подтверждение пароля

        // ============ ВАЛИДАЦИЯ ИМЕНИ ============
        if (empty($name)) {
            // Имя не должно быть пустым
            $this->errors['name'] = 'Введите имя';
        } elseif (strlen($name) < 2) {
            // Имя должно быть не короче 2 символов
            $this->errors['name'] = 'Имя должно быть не меньше 2 символов';
        }

        // ============ ВАЛИДАЦИЯ EMAIL ============
        if (empty($email)) {
            // Email не должен быть пустым
            $this->errors['email'] = 'Введите email';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Проверяем формат email с помощью встроенной функции PHP
            $this->errors['email'] = 'Неверный формат email';
        }

        // ============ ВАЛИДАЦИЯ ПАРОЛЯ ============
        if (empty($password)) {
            // Пароль не должен быть пустым
            $this->errors['password'] = 'Введите пароль';
        } elseif (strlen($password) < 6) {
            // Пароль должен быть не короче 6 символов
            $this->errors['password'] = 'Пароль должен быть минимум 6 символов';
        }

        // ============ ВАЛИДАЦИЯ ПОДТВЕРЖДЕНИЯ ПАРОЛЯ ============
        if (empty($passwordRepeat)) {
            // Подтверждение не должно быть пустым
            $this->errors['passwordRepeat'] = 'Подтвердите пароль';
        } elseif ($password !== $passwordRepeat) {
            // Пароль и подтверждение должны совпадать
            $this->errors['passwordRepeat'] = 'Пароли не совпадают';
        }
    }

    /**
     * Возвращает имя пользователя из формы
     * @return string
     */
    public function getName(): string
    {
        return $this->getString('name');
    }

    /**
     * Возвращает email из формы
     * @return string
     */
    public function getEmail(): string
    {
        return $this->getString('email');
    }

    /**
     * Возвращает пароль из формы
     * @return string
     */
    public function getPassword(): string
    {
        return $this->getString('password');
    }

    /**
     * Возвращает подтверждение пароля из формы
     * @return string
     */
    public function getPasswordRepeat(): string
    {
        return $this->getString('passwordRepeat');
    }
}
