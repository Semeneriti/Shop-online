<?php

class App
{
    private array $routes = [
        '/registration' => [
            'GET' => [
                'class' => 'UserController',
                'method' => 'getRegistrate',
            ],
            'POST' => [
                'class' => 'UserController',
                'method' => 'registrate',
            ]
        ],
        '/login' => [
            'GET' => [
                'class' => 'UserController',
                'method' => 'login',
            ],
            'POST' => [
                'class' => 'UserController',
                'method' => 'login',
            ]
        ],
        '/logout' => [
            'GET' => [
                'class' => 'UserController',
                'method' => 'logout',
            ]
        ],
        '/catalog' => [
            'GET' => [
                'class' => 'CatalogController',
                'method' => '__construct',
            ]
        ],
        '/add-product' => [
            'GET' => [
                'class' => 'ProductController',
                'method' => 'showForm',
            ],
            'POST' => [
                'class' => 'ProductController',
                'method' => 'addToCart',
            ]
        ],
        '/cart' => [
            'GET' => [
                'class' => 'CartController',
                'method' => '__construct',
            ]
        ],
        '/profile' => [
            'GET' => [
                'class' => 'UserController',
                'method' => 'getProfile',
            ]
        ],
        '/edit-profile' => [
            'GET' => [
                'class' => 'UserController',
                'method' => 'showEditForm',
            ],
            'POST' => [
                'class' => 'UserController',
                'method' => 'updateProfile',
            ]
        ]
    ];

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

        $controllerPath = __DIR__ . '/../Controllers/' . $class . '.php';

        require_once $controllerPath;

        $controller = new $class();

        if ($method !== '__construct') {
            $controller->$method();
        }
    }
}