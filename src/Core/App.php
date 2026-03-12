<?php
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

            // Если это функция-замыкание
            if (is_callable($handler)) {
                $handler();
                return;
            }

            // Если это массив [контроллер, метод]
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

                // Оборачиваем вызов контроллера в try-catch
                try {
                    $controller->$methodName();
                } catch (\Throwable $e) {
                    // Логируем ошибку (теперь безопасно)
                    $this->logger->error($e->getMessage(), [
                        'url' => $url,
                        'method' => $method,
                        'controller' => $controllerName,
                        'action' => $methodName,
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString()
                    ]);

                    // Проверяем, не отправлены ли уже заголовки
                    if (!headers_sent()) {
                        // Если это не 404 или 405, показываем 500
                        if ($e->getCode() !== 404 && $e->getCode() !== 405) {
                            http_response_code(500);
                        }

                        // Перенаправляем на страницу ошибки
                        header("Location: /500");
                    } else {
                        // Если заголовки уже отправлены, выводим простой HTML
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
            // Ошибки роутинга
            $this->logger->error($e->getMessage(), [
                'url' => $url ?? 'неизвестно',
                'method' => $method ?? 'неизвестно',
                'code' => $e->getCode()
            ]);

            // Проверяем, не отправлены ли уже заголовки
            if (!headers_sent()) {
                http_response_code($e->getCode() ?: 500);
            }

            if ($e->getCode() == 404) {
                // Используем буферизацию вывода для чистого вывода
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