<?php
// app/Core/Session.php

namespace App\Core;

class Session {
    /**
     * Start a secure session.
     */
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '', // Set your domain
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
        }
    }

    /**
     * Set a session value.
     */
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    /**
     * Get a session value.
     */
    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if a session key exists.
     */
    public static function has($key) {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove a session value.
     */
    public static function unset($key) {
        unset($_SESSION[$key]);
    }

    /**
     * Destroy the entire session.
     */
    public static function destroy() {
        $_SESSION = array();
        session_destroy();
    }
    
    /**
     * Regenerate the session ID.
     */
    public static function regenerate() {
        session_regenerate_id(true);
    }
}