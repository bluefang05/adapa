<?php

class Router {
    private $routes = [];
    private $basePath;

    public function __construct() {
        $this->basePath = BASE_URL;
    }

    public function add($method, $route, $controller, $action) {
        $this->routes[$method][$route] = ['controller' => $controller, 'action' => $action];
    }

    public function dispatch($uri, $method) {
        // Remove base path from URI
        $uri = $this->removeBasePath($uri);
        
        // Handle empty URI as root
        if (empty($uri)) {
            $uri = '/';
        }
        
        foreach ($this->routes[$method] as $route => $routeInfo) {
            $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';
            
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove the full match
                
                $controller = $routeInfo['controller'];
                $action = $routeInfo['action'];
                
                // Handle controller paths with subdirectories
                $controllerPath = $controller;
                if (strpos($controller, '/') !== false) {
                    $controllerPath = str_replace('/', DIRECTORY_SEPARATOR, $controller);
                }
                
                require_once __DIR__ . '/../controllers/' . $controllerPath . '.php';
                
                // Extract just the class name for instantiation
                $controllerClass = basename($controller);
                $controllerInstance = new $controllerClass();
                
                if (method_exists($controllerInstance, $action)) {
                    call_user_func_array([$controllerInstance, $action], $matches);
                } else {
                    http_response_code(404);
                    echo "404 Method Not Found";
                }
                return;
            }
        }
        
        http_response_code(404);
        echo "404 Not Found - Route: " . htmlspecialchars($uri);
    }
    
    private function removeBasePath($uri) {
        // Remove base path from the beginning of URI
        if (strpos($uri, $this->basePath) === 0) {
            $uri = substr($uri, strlen($this->basePath));
        }
        // Normalize: ensure leading slash
        if ($uri === '' || $uri === false) {
            return '/';
        }
        if ($uri[0] !== '/') {
            $uri = '/' . $uri;
        }
        // Normalize trailing slash except root
        if (strlen($uri) > 1) {
            $uri = rtrim($uri, '/');
        }
        return $uri;
    }
}
