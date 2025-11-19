<?php
// app/Core/Controller.php

namespace App\Core;

use App\Core\View; // <--- ADD THIS

class Controller {
    
    /**
     * Load a view file with data using the OOP View System.
     * @param string $view - Path to view (e.g., 'main_page/index')
     * @param array $data - Data to pass to the view
     * @param string $layout - Layout name (default is 'default')
     */
    protected function view($view, $data = [], $layout = 'default') { 
        
        // Use the new View class to handle rendering
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
}