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
        // Log bulk route addition for debugging
        if ($path === '/api/patient-folders/bulk' || $path === "/api/patient-folders/bulk") {
            error_log("Router Debug - Adding bulk route: $method $path -> $handler");
        }
        
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
        
        // Log after adding for bulk route
        if ($path === '/api/patient-folders/bulk' || $path === "/api/patient-folders/bulk") {
            error_log("Router Debug - Bulk route added. Total routes now: " . count($this->routes));
        }
    }

    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        
        // Handle different deployment scenarios
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        
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
        
        // Handle PUT/DELETE methods from forms
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        // Log routing attempt for debugging
        if (strpos($uri, '/api/patient-folders') !== false) {
            error_log("Router Debug - Method: $method, URI: $uri, Total routes: " . count($this->routes));
            
            // Log all DELETE routes for patient-folders with their index
            $deleteRoutes = [];
            foreach ($this->routes as $idx => $r) {
                if ($r['method'] === 'DELETE' && strpos($r['path'], '/api/patient-folders') !== false) {
                    $deleteRoutes[] = ['index' => $idx, 'path' => $r['path'], 'handler' => $r['handler']];
                    error_log("Router Debug - DELETE patient-folders route #$idx: {$r['path']} -> {$r['handler']}");
                }
            }
            
            // Specifically check for bulk route
            $bulkRouteFound = false;
            foreach ($this->routes as $idx => $r) {
                if ($r['method'] === 'DELETE' && ($r['path'] === '/api/patient-folders/bulk' || $r['path'] === "/api/patient-folders/bulk")) {
                    error_log("Router Debug - BULK ROUTE FOUND at index #$idx: {$r['path']} -> {$r['handler']}");
                    $bulkRouteFound = true;
                }
            }
            if (!$bulkRouteFound) {
                error_log("Router Debug - WARNING: Bulk route NOT FOUND in routes array!");
            }
        }

        // First pass: Check exact matches (routes without parameters)
        foreach ($this->routes as $index => $route) {
            if ($route['method'] === $method && strpos($route['path'], '{') === false) {
                // Exact path match (no parameters)
                if ($route['path'] === $uri) {
                    if (strpos($uri, '/api/patient-folders') !== false) {
                        error_log("Router Debug - EXACT MATCH FOUND: {$route['method']} {$route['path']} -> {$route['handler']}");
                    }
                    $this->executeHandler($route['handler']);
                    return;
                }
            }
        }
        
        // Second pass: Check parameterized routes
        foreach ($this->routes as $index => $route) {
            if (strpos($uri, '/api/patient-folders') !== false && $route['method'] === $method) {
                error_log("Router Debug - Checking route #$index: {$route['method']} {$route['path']}");
            }
            
            if ($route['method'] === $method && $this->matchRoute($route['path'], $uri)) {
                if (strpos($uri, '/api/patient-folders') !== false) {
                    error_log("Router Debug - PARAMETERIZED MATCH: {$route['method']} {$route['path']} -> {$route['handler']}");
                }
                $this->executeHandler($route['handler']);
                return; // Important: exit after executing handler
            }
        }

        // No route found
        if (strpos($uri, '/api/patient-folders') !== false) {
            error_log("Router Debug - NO MATCH FOUND for: $method $uri");
        }
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
        // Clear output buffers BEFORE executing handler to prevent HTML output
        while (ob_get_level()) {
            ob_end_clean();
        }
        
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

