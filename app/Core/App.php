<?php

// Note: Ensure your Controllers have 'namespace App\Controllers;'
// If not, remove the 'App\\Controllers\\' prefix from $fullControllerName.

class App {

    protected $controller = 'HomeController';
    protected $method = 'index';
    protected $params = [];

    public function splitUrl() {
        // Sanitization and filtering would be added here in a production environment
        $URL = $_GET['url'] ?? 'home';
        $URL = filter_var($URL, FILTER_SANITIZE_URL);
        $URL = explode('/', $URL);
        return $URL;
    }

    public function loadController() {
        $URL = $this->splitUrl();
        $filename = ucfirst(array_shift($URL)) . 'Controller';
        $controllerPath = '../app/Controllers/' . $filename . '.php';

        // Check if the controller file exists
        if (file_exists($controllerPath)) {
            require_once $controllerPath;
            $fullControllerName = 'App\\Controllers\\' . $filename;
            $this->controller = new $fullControllerName();
            
            // 6. Store remaining URL parts as parameters (method and params)
            if (!empty($URL)) {
                $this->params = $URL;
            }

        } else {
            $filename404 = '_404';
            $controllerPath404 = '../app/Controllers/' . $filename404 . '.php';

            if (file_exists($controllerPath404)) {
                require_once $controllerPath404;
                $fullControllerName = 'app\\Controllers\\' . $filename404;
                $this->controller = new $fullControllerName();

                $this->method = 'index'; 
                $this->params = []; // Clear params
                
                http_response_code(404);
                
            } else {
                // Ultimate fallback
                http_response_code(500);
                echo "Fatal Error: Controller not found and _404 controller is missing.";
                exit;
            }
        } 
    }
    
    // You'll need a method to actually call the controller method
    public function dispatch() {
        $this->loadController();
        
        // Get the method name (if provided, default to 'index')
        $this->method = array_shift($this->params) ?? $this->method;
        
        // Check if the method exists in the controller object
        if (method_exists($this->controller, $this->method)) {
            // Call the method with remaining URL parts as parameters
            call_user_func_array([$this->controller, $this->method], $this->params);
        } else {
            // Handle error: method not found.
            http_response_code(404);
            echo "Method '{$this->method}' not found in controller.";
            exit;
        }
    }
}

// In index.php, you would call:
// $app = new App();
// $app->dispatch();