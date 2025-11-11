<?php
// Lumora - public/index.php
// The one and only entry point for all web requests.

// Load Composer's autoloader FIRST
require __DIR__ . '/../vendor/autoload.php';

use App\Core\Request;
use App\Core\Router;
use App\Core\Session;

// NOW start a secure session (after autoloader is loaded)
Session::start();

// Load the database configuration
require __DIR__ . '/../app/Core/Database.php';

// Load the routes definition
$router = new Router(Request::uri(), Request::method());
require __DIR__ . '/../routes/web.php';

// Run the router to dispatch the request to the correct controller
$router->dispatch();
