<?php
namespace Core;

class App
{
    private array $routes = [];

    public function get(string $route, array $handler): void
    {
        $this->addRoute($route, 'GET', $handler);
    }

    public function post(string $route, array $handler): void
    {
        $this->addRoute($route, 'POST', $handler);
    }

    public function put(string $route, array $handler): void
    {
        $this->addRoute($route, 'PUT', $handler);
    }

    public function delete(string $route, array $handler): void
    {
        $this->addRoute($route, 'DELETE', $handler);
    }

    private function addRoute(string $route, string $routeMethod, array $handler): void
    {
        $this->routes[$route][$routeMethod] = $handler;
    }

    public function run(): void
    {
        $requestUri = $_SERVER['REQUEST_URI'];
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        // Убираем GET-параметры из URI
        if (($pos = strpos($requestUri, '?')) !== false) {
            $requestUri = substr($requestUri, 0, $pos);
        }

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

        $class = $handler[0];
        $method = $handler[1];

        // Добавляем namespace если нужно
        if (is_string($class) && strpos($class, '\\') === false) {
            $class = '\\Controllers\\' . $class;
        }

        $controller = new $class();
        $controller->$method();
    }
}