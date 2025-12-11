<?php
// app/Core/Router.php

namespace App\Core;

use Exception;

class Router {
    protected $routes = [
        'GET' => [],
        'POST' => []
    ];
    protected $uri;
    protected $method;

    public function __construct($uri, $method) {
        $this->uri = $uri;
        $this->method = $method;
    }

    /**
     * Register a GET route
     */
    public function get($uri, $controllerAction) {
        $this->routes['GET'][$uri] = $controllerAction;
    }

    /**
     * Register a POST route
     */
    public function post($uri, $controllerAction) {
        $this->routes['POST'][$uri] = $controllerAction;
    }

    /**
     * Dispatch the current request to the appropriate controller
     */
    public function dispatch() {
        // First try exact match (e.g., /shop/products)
        if (array_key_exists($this->uri, $this->routes[$this->method])) {
            $action = $this->routes[$this->method][$this->uri];
            
            // Split 'Controller@method' into parts
            $parts = explode('@', $action);
            
            // Call controller method without parameters
            return $this->callAction($parts[0], $parts[1]);
        }

        // Try to match dynamic routes (e.g., /shop/products/view/{id})
        foreach ($this->routes[$this->method] as $route => $action) {
            $params = $this->matchRoute($route, $this->uri);
            
            if ($params !== false) {
                // Split 'Controller@method' into parts
                $parts = explode('@', $action);
                
                // Call controller method WITH parameters
                return $this->callAction($parts[0], $parts[1], $params);
            }
        }

        // No route matched - return 404
        http_response_code(404);
        echo "404 - Page not found for {$this->method} {$this->uri}";
    }

    /**
     * Match a route pattern against the current URI
     * @param string $route - Route pattern (e.g., '/shop/products/view/{id}')
     * @param string $uri - Current URI (e.g., '/shop/products/view/5')
     * @return array|false - Array of parameters or false if no match
     */
    protected function matchRoute($route, $uri) {
        // Convert route pattern to regex
        // {id} becomes ([^/]+) - matches anything except forward slash
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $route);
        $pattern = '#^' . $pattern . '$#';
        
        // Try to match the pattern
        if (preg_match($pattern, $uri, $matches)) {
            // Remove the full match (first element)
            array_shift($matches);
            
            // Extract parameter names from route pattern
            preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $route, $paramNames);
            
            // Create associative array of parameters
            // Example: ['id' => '5', 'slug' => 'my-product']
            $params = [];
            foreach ($paramNames[1] as $index => $name) {
                $params[$name] = $matches[$index] ?? null;
            }
            
            return $params;
        }
        
        return false;
    }

    /**
     * Call the controller action with optional parameters
     * @param string $controller - Controller name (e.g., 'ProductManagementController')
     * @param string $method - Method name (e.g., 'view')
     * @param array $params - Route parameters (e.g., ['id' => '5'])
     * @return mixed
     */
    protected function callAction($controller, $method, $params = []) {
        // Build full controller class name
        $controllerClass = "App\\Controllers\\{$controller}";

        // Check if controller exists
        if (!class_exists($controllerClass)) {
            throw new Exception("Controller {$controllerClass} does not exist.");
        }

        // Create controller instance
        $controllerInstance = new $controllerClass();

        // Check if method exists
        if (!method_exists($controllerInstance, $method)) {
            throw new Exception("Method {$method} does not exist on {$controllerClass}.");
        }

        // Call method with parameters if they exist
        if (!empty($params)) {
            // Convert associative array to indexed array of values
            // ['id' => '5'] becomes ['5']
            // Then pass to method: view('5')
            return call_user_func_array(
                [$controllerInstance, $method], 
                array_values($params)
            );
        }
        
        // Call method without parameters
        return $controllerInstance->$method();
    }
}