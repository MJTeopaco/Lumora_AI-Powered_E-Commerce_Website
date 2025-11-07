<?php
// Start the session
session_start();
// Autoload dependencies
require_once __DIR__ . '/../vendor/autoload.php';


$controller = new HomeController();
$controller->index();
use App\Controllers\HomeController;

?>