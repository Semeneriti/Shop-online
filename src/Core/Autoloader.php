<?php
namespace Core;

class Autoloader
{
    public static function register(): void
    {
        spl_autoload_register(function (string $className) {
            $prefixes = [
                'Controllers\\' => __DIR__ . '/../Controllers/',
                'Core\\' => __DIR__ . '/',
                'Models\\' => __DIR__ . '/../Models/',
            ];

            foreach ($prefixes as $prefix => $baseDir) {
                if (strpos($className, $prefix) === 0) {
                    $relativeClass = substr($className, strlen($prefix));
                    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

                    if (file_exists($file)) {
                        require_once $file;
                        return true;
                    }
                }
            }

            return false;
        });
    }
}