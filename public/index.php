<?php

// Start the session
session_start();
// Autoload dependencies
//require_once __DIR__ . '/../vendor/autoload.php';
require '../app/Core/init.php';  



$app = new App();
$app->loadController();



?>