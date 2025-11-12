<?php
// app/Core/Controller.php

namespace App\Core;

class Controller {
    public function __construct() {
        // Base controller constructor
    }

    public function view($viewName, $data = []) {
        $viewPath = '../app/Views/' . $viewName . '.php';
        if (file_exists($viewPath)) {
            extract($data);
            require_once $viewPath;
        } else {
            // Handle error: view not found
            http_response_code(404);
            echo "View not found: {$viewName}";
            exit;
        }
    }
}