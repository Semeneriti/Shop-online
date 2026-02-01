<?php
namespace Core;

class App
{
    private array $routes = [];

    /**
     * Добавляет маршрут в приложение
     */
    public function addRoute(string $route, string $routeMethod, string $className, string $method): void
    {
        $this->routes[$route][$routeMethod] = [
            'class' => $className,
            'method' => $method,
        ];
    }

    public function run(): void
    {
        $requestUri = $_SERVER['REQUEST_URI'];
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        if (!isset($this->routes[$requestUri])) {
            http_response_code(404);
            require_once __DIR__ . '/../public/404.php';
            return;
        }

        $routeMethods = $this->routes[$requestUri];

        if (!isset($routeMethods[$requestMethod])) {
            http_response_code(405);
            echo "Метод не разрешен";
            return;
        }

        $handler = $routeMethods[$requestMethod];
        $class = $handler['class'];
        $method = $handler['method'];

        // Проверяем, является ли класс полным пространством имен или нужно добавить пространство имен по умолчанию
        if (strpos($class, '\\') === false) {
            $class = '\\Controllers\\' . $class;
        }

        $controller = new $class();

        if ($method !== '__construct') {
            $controller->$method();
        }
    }
}