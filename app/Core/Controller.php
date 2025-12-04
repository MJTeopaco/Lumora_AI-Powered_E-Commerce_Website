<?php
// app/Core/Controller.php

namespace App\Core;

use App\Core\View;
use App\Core\Session;

class Controller {
    
    /**
     * Load a view file with data using the OOP View System.
     * Updated to support specific layouts (e.g., 'shop', 'admin').
     */
    protected function view($view, $data = [], $layout = 'default') { 
        // Create view, set the layout, and render it
        $viewInstance = View::make($view, $data)->setLayout($layout);
        $viewInstance->render();
    }
    
    /**
     * Load a model
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
     */
    protected function redirect($url, $statusCode = 302) {
        header("Location: {$url}", true, $statusCode);
        exit();
    }
    
    /**
     * Return JSON response
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    /**
     * Verify CSRF Token
     * Call this at the start of any method handling POST data
     */
    protected function verifyCsrfToken() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !hash_equals(Session::get('csrf_token', ''), $_POST['csrf_token'])) {
                die('CSRF validation failed. The request could not be verified.');
            }
        }
    }
}