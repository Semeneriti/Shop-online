<?php

namespace Request; // Пространство имен для классов-запросов

// Абстрактный класс Request - родитель для всех классов-запросов
// abstract значит, что нельзя создать объект этого класса, только наследоваться от него
abstract class Request
{
    // Защищенные свойства - доступны в этом классе и в классах-наследниках
    protected array $data;      // Массив с данными из формы (POST или GET)
    protected array $errors = []; // Массив ошибок валидации

    /**
     * Конструктор - вызывается при создании объекта запроса
     * @param array $data - данные из формы ($_POST или $_GET)
     */
    public function __construct(array $data)
    {
        // Сохраняем переданные данные
        $this->data = $data;

        // Запускаем валидацию (метод будет вызван у класса-наследника)
        $this->validate();
    }

    /**
     * Абстрактный метод - должен быть реализован в каждом классе-наследнике
     * Содержит логику проверки конкретной формы
     */
    abstract protected function validate(): void;

    /**
     * Возвращает все ошибки валидации
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Проверяет, есть ли ошибки валидации
     * @return bool - true если ошибки есть, false если нет
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Возвращает все данные из формы
     * @return array
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Защищенный метод - получает значение по ключу или возвращает значение по умолчанию
     * @param string $key - ключ в массиве данных
     * @param mixed $default - значение по умолчанию, если ключа нет
     * @return mixed
     */
    protected function getValue(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Защищенный метод - получает значение и преобразует его в целое число
     * @param string $key - ключ в массиве данных
     * @param int $default - значение по умолчанию
     * @return int
     */
    protected function getInt(string $key, int $default = 0): int
    {
        return (int)($this->data[$key] ?? $default);
    }

    /**
     * Защищенный метод - получает значение, обрезает пробелы и преобразует в строку
     * @param string $key - ключ в массиве данных
     * @param string $default - значение по умолчанию
     * @return string
     */
    protected function getString(string $key, string $default = ''): string
    {
        return trim($this->data[$key] ?? $default);
    }
}
