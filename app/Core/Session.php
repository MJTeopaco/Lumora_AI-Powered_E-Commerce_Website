<?php
// app/Core/Session.php

namespace App\Core;

class Session {
    private static $timeout = 3600; //1 hour

    /**
     * Start a secure session with timeout handling.
     */
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
        }
        
        // Generate CSRF Token if it doesn't exist
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        // Check for session timeout
        self::checkTimeout();
    }

    /**
     * Check if session has timed out due to inactivity
     */
    private static function checkTimeout() {
        if (self::has('user_id')) {
            $lastActivity = self::get('last_activity', 0);
            $currentTime = time();
            
            // If more than 1 hour has passed since last activity
            if (($currentTime - $lastActivity) > self::$timeout) {
                // Session expired, destroy it
                self::destroyWithMessage('Your session has expired due to inactivity. Please login again.');
                return;
            }
            
            // Update last activity time
            self::set('last_activity', $currentTime);
        } else {
            // Set initial last activity for new sessions
            self::set('last_activity', time());
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
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
    
    /**
     * Destroy session and redirect with message
     */
    private static function destroyWithMessage($message) {
        self::destroy();
        header('Location: /login?status=error&message=' . urlencode($message) . '&tab=login&step=credentials');
        exit();
    }
    
    /**
     * Regenerate the session ID.
     */
    public static function regenerate() {
        session_regenerate_id(true);
        // Reset last activity on regeneration
        self::set('last_activity', time());
    }
    
    /**
     * Refresh activity timestamp (call on important actions)
     */
    public static function refreshActivity() {
        self::set('last_activity', time());
    }
}