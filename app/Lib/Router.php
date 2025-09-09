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

    public function head($path, $handler)
    {
        $this->addRoute('HEAD', $path, $handler);
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
        
        
        // Handle different deployment scenarios
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        
        // Debug logging
        error_log("Router Debug - SCRIPT_NAME: $scriptName, REQUEST_URI: $requestUri, Original URI: $uri");
        

        

        
        // Remove base path if running in subdirectory or with alias
        if (strpos($scriptName, '/clinic/public/') !== false) {
            // Running via Apache alias /clinic/public
            $basePath = '/clinic/public';
            if (strpos($uri, $basePath) === 0) {
                $uri = substr($uri, strlen($basePath));
            }
        } elseif (strpos($uri, '/clinic/public') === 0) {
            // Handle case where URI contains /clinic/public but script doesn't
            $basePath = '/clinic/public';
            $uri = substr($uri, strlen($basePath));
        } else {
            // Running with DocumentRoot = /clinic/public
            $basePath = str_replace('/index.php', '', $scriptName);
            if ($basePath !== '/' && strpos($uri, $basePath) === 0) {
                $uri = substr($uri, strlen($basePath));
            }
        }
        
        // Ensure URI starts with /
        if (empty($uri) || $uri === '') {
            $uri = '/';
        }
        
        error_log("Router Debug - Final URI: $uri, Method: $method");
        
        // Debug: Log the URI for troubleshooting (remove in production)
        // if (($_ENV['APP_ENV'] ?? 'local') === 'local') {
        //     error_log("Router Debug - Original URI: " . $_SERVER['REQUEST_URI'] . ", Processed URI: " . $uri . ", Method: " . $method);
        // }
        
        // Handle PUT/DELETE methods from forms
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchRoute($route['path'], $uri)) {
                $this->executeHandler($route['handler']);
                return; // Important: exit after executing handler
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

        // Convert associative array to indexed array for call_user_func_array
        call_user_func_array([$controllerInstance, $method], array_values($this->params));
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
