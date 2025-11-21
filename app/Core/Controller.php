<?php
// app/Core/Controller.php

namespace App\Core;

use App\Core\View;

class Controller {
    
    /**
     * Load a view file with data using the OOP View System.
     * @param string $view - Path to view (e.g., 'main/main' or 'admin/index')
     * @param array $data - Data to pass to the view
     * @param string $layout - Layout name (default is 'default', can be 'admin', 'auth', etc.)
     */
    protected function view($view, $data = [], $layout = 'default') { 
        // Use the View class to handle rendering
        $viewInstance = View::make($view, $data)->setLayout($layout);
        
        // Render the view and send output to the browser
        $viewInstance->render();
    }
    
    /**
     * Load a model
     * @param string $model - Model name
     * @return object - Model instance
     */
    protected function model($model) {
        $modelClass = "App\\Models\\$model";
        if (class_exists($modelClass)) {
            return new $modelClass();
        }
        die("Model not found: $model");
    }
    
    /**
     * Redirect to a URL
     * @param string $url - URL to redirect to
     * @param int $statusCode - HTTP status code (default: 302)
     */
    protected function redirect($url, $statusCode = 302) {
        header("Location: {$url}", true, $statusCode);
        exit();
    }
    
    /**
     * Return JSON response
     * @param mixed $data - Data to encode as JSON
     * @param int $statusCode - HTTP status code (default: 200)
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
}