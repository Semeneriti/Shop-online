<?php
namespace Core;

class App
{
    private array $routes = [];

    public function get(string $route, string $className, string $method): void
    {
        $this->addRoute($route, 'GET', $className, $method);
    }

    public function post(string $route, string $className, string $method): void
    {
        $this->addRoute($route, 'POST', $className, $method);
    }

    public function put(string $route, string $className, string $method): void
    {
        $this->addRoute($route, 'PUT', $className, $method);
    }

    public function delete(string $route, string $className, string $method): void
    {
        $this->addRoute($route, 'DELETE', $className, $method);
    }

    private function addRoute(string $route, string $routeMethod, string $className, string $method): void
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
            require_once __DIR__ . '/../../public/404.php';
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

        if (strpos($class, '\\') === false) {
            $class = '\\Controllers\\' . $class;
        }

        $controller = new $class();

        if ($method !== '__construct') {
            $controller->$method();
        }
    }
}