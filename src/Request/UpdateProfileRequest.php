<?php

namespace Request; // Пространство имен для классов-запросов

// Класс UpdateProfileRequest - отвечает за валидацию и получение данных из формы редактирования профиля
// Наследуется от базового класса Request
class UpdateProfileRequest extends Request
{
    /**
     * Метод валидации - вызывается автоматически в конструкторе родительского класса
     * Проверяет все поля формы редактирования профиля
     */
    protected function validate(): void
    {
        // Получаем данные из формы и очищаем от пробелов
        $name = $this->getString('name');                       // Имя пользователя
        $email = $this->getString('email');                     // Email
        $password = $this->getString('password');               // Новый пароль (может быть пустым)
        $passwordConfirm = $this->getString('password_confirm'); // Подтверждение нового пароля

        // ============ ВАЛИДАЦИЯ ИМЕНИ ============
        if (empty($name)) {
            // Имя не должно быть пустым
            $this->errors['name'] = 'Введите имя';
        }
        // Минимальная длина имени не проверяется, так как это уже было при регистрации

        // ============ ВАЛИДАЦИЯ EMAIL ============
        if (empty($email)) {
            // Email не должен быть пустым
            $this->errors['email'] = 'Введите email';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Проверяем формат email
            $this->errors['email'] = 'Неверный формат email';
        }

        // ============ ВАЛИДАЦИЯ ПАРОЛЯ (только если ввели новый) ============
        if (!empty($password)) {
            // Если поле пароля не пустое - значит пользователь хочет сменить пароль

            // Проверяем длину нового пароля
            if (strlen($password) < 6) {
                $this->errors['password'] = 'Пароль должен быть не менее 6 символов';
            }
            // Проверяем, совпадают ли пароль и подтверждение
            elseif ($password !== $passwordConfirm) {
                $this->errors['password_confirm'] = 'Пароли не совпадают';
            }
        }
        // Если поле пароля пустое - пароль не меняется, валидация не нужна
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
     * Возвращает новый пароль из формы (может быть пустым)
     * @return string
     */
    public function getPassword(): string
    {
        return $this->getString('password');
    }

    /**
     * Возвращает подтверждение нового пароля из формы
     * @return string
     */
    public function getPasswordConfirm(): string
    {
        return $this->getString('password_confirm');
    }
}