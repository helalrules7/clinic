<?php

namespace App\Lib;

class Router
{
    private $routes = [];
    private $params = [];

    public function get($path, $handler)
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post($path, $handler)
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function put($path, $handler)
    {
        $this->addRoute('PUT', $path, $handler);
    }

    public function delete($path, $handler)
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    private function addRoute($method, $path, $handler)
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }

    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Handle PUT/DELETE methods from forms
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchRoute($route['path'], $uri)) {
                return $this->executeHandler($route['handler']);
            }
        }

        // No route found
        $this->notFound();
    }

    private function matchRoute($routePath, $uri)
    {
        // Convert route parameters to regex pattern
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $uri, $matches)) {
            // Extract parameter values
            preg_match_all('/\{([^}]+)\}/', $routePath, $paramNames);
            
            for ($i = 1; $i < count($matches); $i++) {
                $this->params[$paramNames[1][$i - 1]] = $matches[$i];
            }
            
            return true;
        }

        return false;
    }

    private function executeHandler($handler)
    {
        list($controller, $method) = explode('@', $handler);
        $controllerClass = "\\App\\Controllers\\{$controller}";
        
        if (!class_exists($controllerClass)) {
            throw new \Exception("Controller {$controllerClass} not found");
        }

        $controllerInstance = new $controllerClass();
        
        if (!method_exists($controllerInstance, $method)) {
            throw new \Exception("Method {$method} not found in {$controllerClass}");
        }

        return call_user_func_array([$controllerInstance, $method], $this->params);
    }

    private function notFound()
    {
        http_response_code(404);
        
        if (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Not Found']);
        } else {
            // Show 404 page
            $view = new View();
            echo $view->render('errors/404');
        }
    }

    public function getParams()
    {
        return $this->params;
    }
}
