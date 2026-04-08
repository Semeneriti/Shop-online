<?php

declare(strict_types=1);

namespace Core;

// Класс автозагрузчика - автоматически подключает файлы классов при их первом использовании
class Autoloader
{
    /**
     * Регистрирует метод autoload как автозагрузчик PHP
     * Вызывается один раз в index.php
     */
    public static function register(): void
    {
        // spl_autoload_register - встроенная функция PHP
        // [__CLASS__, 'autoload'] - вызываем метод autoload этого же класса
        // Теперь при использовании любого незнакомого класса PHP вызовет наш метод autoload
        spl_autoload_register([__CLASS__, 'autoload']);
    }

    /**
     * Автоматически вызывается PHP, когда встречается класс, который еще не загружен
     * @param string $class - полное имя класса с namespace (например, 'Controllers\UserController')
     */
    public static function autoload(string $class): void
    {
        // Убираем ведущий обратный слэш, если он есть
        // Например: '\Controllers\UserController' -> 'Controllers\UserController'
        $class = ltrim($class, '\\');

        // Преобразуем namespace в путь к файлу
        // Заменяем обратные слэши на обычные слэши
        // 'Controllers\UserController' -> 'Controllers/UserController'
        // Добавляем '../' в начало, чтобы выйти из папки Core и перейти в src
        // И добавляем '.php' в конце
        $file = __DIR__ . '/../' . str_replace('\\', '/', $class) . '.php';

        // Приводим разделители к правильному формату для текущей ОС
        // На Windows будет '\', на Linux '/'
        $file = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $file);

        // Проверяем, существует ли файл по этому пути
        if (file_exists($file)) {
            // Если файл существует - подключаем его один раз
            // Теперь класс будет определен, и PHP сможет его использовать
            require_once $file;
        }

        // Если файл не найден - ничего не делаем
        // PHP вызовет следующую автозагрузку или выдаст ошибку
    }
}