<?php
// routes/web.php

use App\Core\Router;
use App\Core\Request;

$uri = Request::uri();
$method = Request::method();

$router = new Router($uri, $method);

// ==================== PUBLIC ROUTES ====================

// Home / Shop Page
$router->get('/', 'HomeController@index');

// Authentication Routes
$router->get('/login', 'AuthController@showLogin');

// Login Flow
$router->post('/login/step1', 'AuthController@handleLoginStep1');
$router->post('/login/step2', 'AuthController@handleLoginStep2');

// Registration Flow
$router->post('/register/step1', 'AuthController@handleRegisterStep1');
$router->post('/register/step2', 'AuthController@handleRegisterStep2');
$router->post('/register/step3', 'AuthController@handleRegisterStep3');
$router->post('/register/step4', 'AuthController@handleRegisterStep4');

// Forgot Password Flow
$router->post('/forgot/step1', 'AuthController@handleForgotStep1');
$router->post('/forgot/step2', 'AuthController@handleForgotStep2');
$router->post('/forgot/step3', 'AuthController@handleForgotStep3');

// OTP Resend (AJAX)
$router->post('/resend-login-otp', 'AuthController@resendLoginOtp');
$router->post('/resend-register-otp', 'AuthController@resendRegisterOtp');
$router->post('/resend-forgot-otp', 'AuthController@resendForgotOtp');

// ==================== PROTECTED ROUTES ====================
// (These routes require authentication)

// Add to Cart (requires login)
$router->post('/add-to-cart', 'HomeController@addToCart');

// Cart Page (you'll create this later)
$router->get('/cart', 'CartController@index');

// Checkout (you'll create this later)
$router->get('/checkout', 'CheckoutController@index');
$router->post('/checkout/process', 'CheckoutController@process');

// User Profile (you'll create this later)
$router->get('/profile', 'ProfileController@index');

// Logout
$router->get('/logout', 'AuthController@logout');

// ==================== DISPATCH ====================
$router->dispatch();