<?php
namespace Core;

class Autoloader
{
    public static function register(): void
    {
        spl_autoload_register([__CLASS__, 'autoload']);
    }

    public static function autoload(string $class): void
    {
        // Убираем ведущий слэш
        $class = ltrim($class, '\\');

        // Заменяем обратные слэши на разделители
        $file = __DIR__ . '/../' . str_replace('\\', '/', $class) . '.php';

        // Делаем путь
        $file = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $file);

        // Проверяем существование файла
        if (file_exists($file)) {
            require_once $file;
        }
    }
}