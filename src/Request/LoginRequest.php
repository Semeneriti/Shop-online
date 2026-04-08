<?php

declare(strict_types=1);


namespace Request; // Пространство имен для классов-запросов

// Класс LoginRequest - отвечает за валидацию и получение данных из формы входа
// Наследуется от базового класса Request
class LoginRequest extends Request
{
    /**
     * Метод валидации - вызывается автоматически в конструкторе родительского класса
     * Проверяет, что email и пароль не пустые
     */
    protected function validate(): void
    {
        // Получаем email из формы (поле 'email') и очищаем от пробелов
        $email = $this->getString('email');

        // Получаем пароль из формы (поле 'password')
        $password = $this->getString('password');

        // Проверка: email не должен быть пустым
        if (empty($email)) {
            $this->errors['email'] = 'Введите email';
        }
        // Примечание: формат email не проверяется, так как это делает база данных при поиске

        // Проверка: пароль не должен быть пустым
        if (empty($password)) {
            $this->errors['password'] = 'Введите пароль';
        }
        // Длина пароля не проверяется, так как это делается при регистрации
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
     * Возвращает значение чекбокса "Запомнить меня"
     * @return bool - true если чекбокс отмечен, false если нет
     */
    public function getRemember(): bool
    {
        // Проверяем, есть ли поле 'remember' в данных и равно ли оно 'on'
        // В HTML чекбокс при отправке формы имеет значение 'on' если отмечен
        return isset($this->data['remember']) && $this->data['remember'] === 'on';
    }
}
