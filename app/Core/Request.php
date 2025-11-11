<?php
// app/Core/Request.php

namespace App\Core;

class Request {
    /**
     * Get the request URI.
     */
    public static function uri() {
        $uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        // Return '/' for homepage, otherwise return the trimmed URI
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