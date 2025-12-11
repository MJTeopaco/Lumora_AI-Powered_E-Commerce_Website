<?php
// app/Core/Request.php

namespace App\Core;

class Request {
    /**
     * Get the request URI.
     */
    public static function uri() {
        // 1. Get the full URI (e.g., /lumora_shop/.../public/shop/products)
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // 2. Get the script's directory (e.g., /lumora_shop/.../public)
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $scriptDir = dirname($scriptName);
        
        // 3. Fix Windows slashes if needed
        $scriptDir = str_replace('\\', '/', $scriptDir);
        
        // 4. If the URI starts with the script directory, strip it out
        // This converts "/project/public/login" -> "/login"
        if ($scriptDir !== '/' && strpos($uri, $scriptDir) === 0) {
            $uri = substr($uri, strlen($scriptDir));
        }

        // 5. Trim standard slashes
        $uri = trim($uri, '/');
        
        // 6. Return standard format
        return $uri === '' ? '/' : '/' . $uri;
    }

    /**
     * Get the request method (GET, POST, etc.).
     */
    public static function method() {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Get a specific value from POST.
     */
    public static function post($key, $default = null) {
        return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
    }

    /**
     * Get all POST data.
     */
    public static function allPost() {
        // Trim all values
        return array_map('trim', $_POST);
    }
}