<?php

spl_autoload_register(function (string $className) {
    $prefixes = [
        'Controllers\\' => '../Controllers/',
        'Core\\' => '../Core/',
        'Models\\' => '../Models/',
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

$app = new \Core\App();
$app->run();