<?php
namespace Models; // Модели - классы для работы с таблицами в базе данных

// Абстрактный класс Model - родитель для всех моделей (Product, User, Order и т.д.)
// abstract значит, что нельзя создать объект этого класса, только наследоваться от него
abstract class Model
{
    // PDO - это встроенный класс PHP для работы с базами данных
    protected \PDO $pdo;              // Соединение с БД для каждого объекта (protected - доступно в наследниках)
    protected static ?\PDO $staticPdo = null; // Статическое соединение с БД (общее для всех объектов)

    /**
     * Конструктор - вызывается при создании объекта модели
     * Устанавливает соединение с базой данных, если его еще нет
     */
    public function __construct()
    {
        // Проверяем, есть ли уже статическое соединение
        if (self::$staticPdo === null) {
            // Если нет - создаем новое соединение с PostgreSQL
            self::$staticPdo = new \PDO(
                "pgsql:host=db;port=5432;dbname=postgres", // Строка подключения: хост db, порт 5432, база postgres
                "semen",                                     // Имя пользователя
                "0000",                                      // Пароль
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,      // Режим ошибок - исключения
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC  // По умолчанию возвращать ассоциативные массивы
                ]
            );
        }

        // Копируем статическое соединение в обычное свойство объекта
        $this->pdo = self::$staticPdo;
    }

    /**
     * Статический метод для получения соединения с БД
     * Можно вызывать без создания объекта: Model::getConnection()
     * @return \PDO
     */
    public static function getConnection(): \PDO
    {
        // Проверяем, есть ли уже соединение
        if (self::$staticPdo === null) {
            // Если нет - создаем новое
            self::$staticPdo = new \PDO(
                "pgsql:host=db;port=5432;dbname=postgres",
                "semen",
                "0000",
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
                ]
            );
        }

        return self::$staticPdo; // Возвращаем соединение
    }

    /**
     * Абстрактный метод - должен быть реализован в каждой модели-наследнике
     * Возвращает имя таблицы в базе данных для этой модели
     * @return string
     */
    abstract protected static function getTableName(): string;
}