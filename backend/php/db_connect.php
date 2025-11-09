<?php
/** * db_connect.php - Database Connection Configuration
 * Establishes connection to MySQL database
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'lumora_db');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    // Log error to file (don't expose to user)
    error_log('Database connection failed: ' . $conn->connect_error);
        
    // Show generic error to user
    die('Database connection failed. Please try again later.');
}

// Set charset to UTF-8
$conn->set_charset('utf8mb4');
?>