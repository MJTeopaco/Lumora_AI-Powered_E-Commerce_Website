<?php
// app/Core/Database.php

namespace App\Core;

use mysqli;

// Database configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'lumora_db');

class Database {
    private static $conn;

    /**
     * Establishes and returns the database connection.
     */
    public static function getConnection() {
        if (!self::$conn) {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

            if ($conn->connect_error) {
                error_log('Database connection failed: ' . $conn->connect_error);
                die('Database connection failed. Please try again later.');
            }
            
            $conn->set_charset('utf8mb4');
            self::$conn = $conn;
        }
        return self::$conn;
    }
}

// Initialize connection immediately
Database::getConnection();
