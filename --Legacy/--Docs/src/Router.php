<?php

namespace App;

class Router {
    private array $routes = [];

    public function get(string $path, callable|array $handler): void {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): void {
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, callable|array $handler): void {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }

    public function dispatch(): void {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        // Remove project folder from URI if running in a subdirectory
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptDir !== '/' && strpos($uri, $scriptDir) === 0) {
            $uri = substr($uri, strlen($scriptDir));
        }
        if ($uri === '') $uri = '/';

        $method = $_SERVER['REQUEST_METHOD'];

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match('#^' . $route['path'] . '$#', $uri, $matches)) {
                array_shift($matches); // Remove full match
                
                // Instantiate the controller if it's a [Class, Method] array
                if (is_array($route['handler']) && is_string($route['handler'][0])) {
                    $route['handler'][0] = new $route['handler'][0]();
                }

                call_user_func_array($route['handler'], $matches);
                return;
            }
        }

        http_response_code(404);
        echo "404 Not Found";
    }
}
