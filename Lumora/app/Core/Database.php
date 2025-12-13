<?php
// app/Core/Database.php

namespace App\Core;

use mysqli;

class Database {
    private static $conn;

    /**
     * Establishes and returns a database connection.
     * Local-only version for XAMPP.
     */
    public static function getConnection() {
        if (!self::$conn) {
            // LOCAL DATABASE CONFIG (XAMPP)
            $host = 'localhost';
            $user = 'root';
            $pass = ''; // default XAMPP password
            $name = 'lumora_db'; // Create/import in phpMyAdmin
            $port = 3306;

            // Debug (optional)
            error_log("Connecting to LOCAL DB: $host | $user | DB: $name");

            // Connect
            $conn = new mysqli($host, $user, $pass, $name, $port);

            if ($conn->connect_error) {
                error_log('Local DB connection failed: ' . $conn->connect_error);
                die('Database connection failed locally. Please check XAMPP settings.');
            }

            $conn->set_charset('utf8mb4');
            self::$conn = $conn;
        }

        return self::$conn;
    }
}

// Initialize connection immediately
Database::getConnection();
