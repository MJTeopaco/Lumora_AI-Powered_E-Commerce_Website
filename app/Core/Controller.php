<?php

namespace app\Core;

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
            $viewPath = '../app/Views/errors/404.php';
            require_once $viewPath;
            exit;
        }
    }



}