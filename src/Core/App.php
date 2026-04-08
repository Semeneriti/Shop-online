<?php

declare(strict_types=1);

namespace Core;

use Services\Loggers\LoggerService;

class App
{
    private array $routes = [];
    private LoggerService $logger;

    public function __construct()
    {
        $this->logger = new LoggerService('/var/www/html/src/Storage/Log/errors.txt');
    }

    public function get(string $route, $handler): void
    {
        $this->routes[$route]['GET'] = $handler;
    }

    public function post(string $route, $handler): void
    {
        $this->routes[$route]['POST'] = $handler;
    }

    public function put(string $route, $handler): void
    {
        $this->routes[$route]['PUT'] = $handler;
    }

    public function delete(string $route, $handler): void
    {
        $this->routes[$route]['DELETE'] = $handler;
    }

    public function run(): void
    {
        try {
            $url = $_SERVER['REQUEST_URI'];
            $method = $_SERVER['REQUEST_METHOD'];

            $pos = strpos($url, '?');
            if ($pos !== false) {
                $url = substr($url, 0, $pos);
            }

            if (!isset($this->routes[$url])) {
                throw new \Exception("Страница не найдена: " . $url, 404);
            }

            if (!isset($this->routes[$url][$method])) {
                throw new \Exception("Метод не разрешен: " . $method, 405);
            }

            $handler = $this->routes[$url][$method];

            if (is_callable($handler)) {
                $handler();
                return;
            }

            if (is_array($handler)) {
                $controllerName = $handler[0];
                $methodName = $handler[1];

                if (strpos($controllerName, '\\') === false) {
                    $controllerName = '\\Controllers\\' . $controllerName;
                }

                if (!class_exists($controllerName)) {
                    throw new \Exception("Контроллер не найден: " . $controllerName, 500);
                }

                $controller = new $controllerName();

                if (!method_exists($controller, $methodName)) {
                    throw new \Exception("Метод не найден: " . $methodName, 500);
                }

                try {
                    $controller->$methodName();
                } catch (\Throwable $e) {
                    $this->logger->error($e->getMessage(), [
                        'url' => $url,
                        'method' => $method,
                        'controller' => $controllerName,
                        'action' => $methodName,
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString()
                    ]);

                    if (!headers_sent()) {
                        if ($e->getCode() !== 404 && $e->getCode() !== 405) {
                            http_response_code(500);
                        }
                        header("Location: /500");
                    } else {
                        echo '<!DOCTYPE html>
                        <html>
                        <head><title>Ошибка</title></head>
                        <body>
                            <h1>Произошла ошибка</h1>
                            <p>' . htmlspecialchars($e->getMessage()) . '</p>
                            <p><a href="/">Вернуться на главную</a></p>
                        </body>
                        </html>';
                    }
                    exit;
                }
            }

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), [
                'url' => $url ?? 'неизвестно',
                'method' => $method ?? 'неизвестно',
                'code' => $e->getCode()
            ]);

            if (!headers_sent()) {
                http_response_code($e->getCode() ?: 500);
            }

            if ($e->getCode() == 404) {
                ob_start();
                require_once __DIR__ . '/../public/404.php';
                $content = ob_get_clean();
                echo $content;
            } else {
                echo "Ошибка: " . htmlspecialchars($e->getMessage());
            }
        }
    }
}