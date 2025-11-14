<?php
// app/Core/Controller.php

namespace App\Core;

class Controller {
    
    /**
     * Load a view file with data
     * @param string $view - Path to view (e.g., 'main_page/index')
     * @param array $data - Data to pass to the view
     */
    protected function view($view, $data = []) {
        // Extract data array to variables
        extract($data);
        
        // Build the view file path
        $viewFile = __DIR__ . '/../Views/layouts/' . $view . '.view.php';
        
        // Check if view file exists
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            die("View not found: $viewFile");
        }
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
}