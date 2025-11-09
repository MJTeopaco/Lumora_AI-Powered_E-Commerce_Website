<?php
session_start();
require_once 'db_connect.php';
require_once 'auth.php'; // To get the deleteRememberMeToken function

// Check for and delete the remember me token
if (isset($_COOKIE['remember_me'])) {
    list($selector, $validator) = explode(':', $_COOKIE['remember_me'], 2);
    if ($selector) {
        deleteRememberMeToken($selector);
    }
}

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page
header('Location: ../../frontend/pages/login.html?status=success&message=You+have+been+logged+out.');
exit();
?>