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

    public function get($uri, $controllerAction) {
        $this->routes['GET'][$uri] = $controllerAction;
    }

    public function post($uri, $controllerAction) {
        $this->routes['POST'][$uri] = $controllerAction;
    }

    public function dispatch() {
        if (array_key_exists($this->uri, $this->routes[$this->method])) {
            $action = $this->routes[$this->method][$this->uri];
            return $this->callAction(...explode('@', $action));
        }

        // Handle 404
        http_response_code(404);
        echo "404 - Page not found for {$this->method} {$this->uri}";
    }

    protected function callAction($controller, $method) {
        $controllerClass = "App\\Controllers\\{$controller}";

        if (!class_exists($controllerClass)) {
            throw new Exception("Controller {$controllerClass} does not exist.");
        }

        $controllerInstance = new $controllerClass();

        if (!method_exists($controllerInstance, $method)) {
            throw new Exception("Method {$method} does not exist on {$controllerClass}.");
        }

        return $controllerInstance->$method();
    }
}