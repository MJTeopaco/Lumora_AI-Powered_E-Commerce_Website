<?php
// routes/web.php

// GET routes
$router->get('/', 'HomeController@index');
$router->get('/login', 'AuthController@showLogin');
$router->get('/logout', 'LogoutController@logout');
$router->get('/admin/dashboard', 'AdminController@dashboard');

// Profile routes
$router->get('/profile', 'ProfileController@index');
$router->post('/profile/update', 'ProfileController@update');
$router->get('/profile/addresses', 'ProfileController@addresses');
$router->get('/profile/settings', 'ProfileController@settings');
$router->post('/profile/change-password', 'ProfileController@changePassword');
$router->get('/profile/addresses/add', 'ProfileController@addAddressForm');
$router->post('/profile/addresses/add', 'ProfileController@addAddress');

// Address routes
$router->get('/profile/addresses', 'AddressController@index');
$router->get('/profile/addresses/add', 'AddressController@add');
$router->post('/profile/addresses/add', 'AddressController@store');
$router->get('/profile/addresses/edit/{id}', 'AddressController@edit');
$router->post('/profile/addresses/edit/{id}', 'AddressController@update');
$router->post('/profile/addresses/delete/{id}', 'AddressController@delete');
$router->post('/profile/addresses/set-default/{id}', 'AddressController@setDefault');

// Logout route
$router->post('/logout', 'AuthController@logout');

// POST routes (Form submissions)
$router->post('/auth/login-step-1', 'AuthController@handleLoginStep1');
$router->post('/auth/login-step-2', 'AuthController@handleLoginStep2');
$router->post('/auth/register-step-1', 'AuthController@handleRegisterStep1');
$router->post('/auth/register-step-2', 'AuthController@handleRegisterStep2');
$router->post('/auth/register-step-3', 'AuthController@handleRegisterStep3');
$router->post('/auth/register-step-4', 'AuthController@handleRegisterStep4');
$router->post('/auth/forgot-step-1', 'AuthController@handleForgotStep1');
$router->post('/auth/forgot-step-2', 'AuthController@handleForgotStep2');
$router->post('/auth/forgot-step-3', 'AuthController@handleForgotStep3');

// POST routes for AJAX (Resend OTP)
$router->post('/auth/resend-login-otp', 'AuthController@resendLoginOtp');
$router->post('/auth/resend-register-otp', 'AuthController@resendRegisterOtp');
$router->post('/auth/resend-forgot-otp', 'AuthController@resendForgotOtp');

// Placeholder routes for cart and notifications (to be implemented)
$router->get('/cart', 'CartController@index');
$router->get('/notifications', 'NotificationsController@index');
