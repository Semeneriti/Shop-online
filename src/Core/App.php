<?php
namespace Core;

// Класс маршрутизатора (роутера) - отвечает за обработку входящих запросов и вызов нужных контроллеров
class App
{
    // Свойство для хранения всех зарегистрированных маршрутов
    // Структура: $routes['/url']['GET'] = [Контроллер::class, 'метод']
    private array $routes = [];

    /**
     * Регистрация GET-маршрута
     * @param string $route - URL путь (например, '/catalog')
     * @param array $handler - массив [класс контроллера, метод]
     */
    public function get(string $route, array $handler): void
    {
        $this->addRoute($route, 'GET', $handler);
    }

    /**
     * Регистрация POST-маршрута
     * @param string $route - URL путь
     * @param array $handler - массив [класс контроллера, метод]
     */
    public function post(string $route, array $handler): void
    {
        $this->addRoute($route, 'POST', $handler);
    }

    /**
     * Регистрация PUT-маршрута
     * @param string $route - URL путь
     * @param array $handler - массив [класс контроллера, метод]
     */
    public function put(string $route, array $handler): void
    {
        $this->addRoute($route, 'PUT', $handler);
    }

    /**
     * Регистрация DELETE-маршрута
     * @param string $route - URL путь
     * @param array $handler - массив [класс контроллера, метод]
     */
    public function delete(string $route, array $handler): void
    {
        $this->addRoute($route, 'DELETE', $handler);
    }

    /**
     * Внутренний метод для добавления маршрута в массив $routes
     * @param string $route - URL путь
     * @param string $routeMethod - HTTP метод (GET, POST и т.д.)
     * @param array $handler - обработчик
     */
    private function addRoute(string $route, string $routeMethod, array $handler): void
    {
        // Сохраняем маршрут в многомерном массиве: сначала URL, потом метод
        $this->routes[$route][$routeMethod] = $handler;
    }

    /**
     * Запуск приложения - обработка текущего запроса
     * Вызывается в index.php после регистрации всех маршрутов
     */
    public function run(): void
    {
        // Получаем текущий URL и метод запроса из суперглобальных переменных
        $requestUri = $_SERVER['REQUEST_URI'];
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        // Удаляем GET-параметры из URL (все, что после ?)
        // Например: /product?id=5 -> /product
        if (($pos = strpos($requestUri, '?')) !== false) {
            $requestUri = substr($requestUri, 0, $pos);
        }

        // Проверяем, есть ли такой маршрут в массиве
        if (!isset($this->routes[$requestUri])) {
            // Если нет - возвращаем ошибку 404 (страница не найдена)
            http_response_code(404);
            require_once __DIR__ . '/../public/404.php';
            return;
        }

        // Получаем все методы, зарегистрированные для этого URL
        $routeMethods = $this->routes[$requestUri];

        // Проверяем, поддерживается ли HTTP-метод запроса для этого URL
        if (!isset($routeMethods[$requestMethod])) {
            // Если метод не поддерживается - ошибка 405 (метод не разрешен)
            http_response_code(405);
            echo "Метод не разрешен";
            return;
        }

        // Получаем обработчик (контроллер и метод) для текущего URL и метода
        $handler = $routeMethods[$requestMethod];

        // Разбираем обработчик на класс и метод
        $class = $handler[0];
        $method = $handler[1];

        // Если класс указан без namespace (например, 'UserController'), добавляем 'Controllers\'
        if (is_string($class) && strpos($class, '\\') === false) {
            $class = '\\Controllers\\' . $class;
        }

        // Создаем объект контроллера
        $controller = new $class();

        // Используем рефлексию, чтобы узнать, какие параметры принимает метод контроллера
        $reflection = new \ReflectionMethod($controller, $method);
        $parameters = $reflection->getParameters();

        // Если метод не принимает параметров - просто вызываем его
        if (empty($parameters)) {
            $controller->$method();
            return;
        }

        // Если метод принимает параметры - подготавливаем аргументы для вызова
        $args = [];
        foreach ($parameters as $param) {
            // Получаем тип параметра (класс)
            $paramType = $param->getType();

            // Если тип указан и это не встроенный тип (не int, string и т.д.)
            if ($paramType && !$paramType->isBuiltin()) {
                // Получаем имя класса
                $className = $paramType->getName();

                // Если в имени класса есть 'Request' - это наш класс-запрос
                if (str_contains($className, 'Request')) {
                    // Создаем объект запроса, передавая в конструктор данные
                    // Для GET-запросов передаем $_GET, для POST - $_POST
                    if ($requestMethod === 'GET') {
                        $args[] = new $className($_GET);
                    } else {
                        $args[] = new $className($_POST);
                    }
                }
            } else {
                // Если тип не указан или это встроенный тип - передаем null
                $args[] = null;
            }
        }

        // Вызываем метод контроллера с подготовленными аргументами
        $controller->$method(...$args);
    }
}