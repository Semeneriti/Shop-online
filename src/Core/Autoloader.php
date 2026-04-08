<?php

declare(strict_types=1);

namespace Core;

class Autoloader
{
    public static function register(): void
    {
        spl_autoload_register([__CLASS__, 'autoload']);
    }

    public static function autoload(string $class): void
    {
        $class = ltrim($class, '\\');
        $file = __DIR__ . '/../' . str_replace('\\', '/', $class) . '.php';
        $file = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $file);

        if (file_exists($file)) {
            require_once $file;
        }
    }
}