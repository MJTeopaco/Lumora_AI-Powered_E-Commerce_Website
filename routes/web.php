<?php
// routes/web.php

// <----------------------------------------------------------------------------------------->

// GET routes
$router->get('/', 'HomeController@index');
$router->get('/login', 'AuthController@showLogin');
$router->get('/logout', 'LogoutController@logout');

// Profile routes
$router->get('/profile', 'ProfileController@index');
$router->post('/profile/update', 'ProfileController@update');
$router->get('/profile/settings', 'ProfileController@settings');
$router->post('/profile/change-password', 'ProfileController@changePassword');

// Address routes
$router->get('/profile/addresses', 'AddressController@index');
$router->get('/profile/addresses/add', 'AddressController@add');
$router->post('/profile/addresses/add', 'AddressController@store');
$router->get('/profile/addresses/edit', 'AddressController@edit');
$router->post('/profile/addresses/edit', 'AddressController@update');
$router->post('/profile/addresses/delete', 'AddressController@delete');
$router->post('/profile/addresses/set-default', 'AddressController@setDefault');

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

// <----------------------------------------------------------------------------------------->
// SELLER REGISTRATION ROUTES
$router->get('/main/seller-guidelines', 'SellerController@guidelines');
$router->get('/seller/register', 'SellerController@registerForm');
$router->post('/seller/register', 'SellerController@registerSubmit');


// <----------------------------------------------------------------------------------------->
// COLLECTIONS ROUTES
$router->get('/collections/index', 'CollectionsController@index');



// <----------------------------------------------------------------------------------------->
// SHOP ROUTES
$router->get('/shop/dashboard', 'ShopController@dashboard');
$router->get('/shop/products', 'ShopController@products');
$router->get('/shop/add-product', 'ShopController@addProduct');
$router->post('/shop/products/store', 'ShopController@storeProduct');
$router->get('/shop/orders', 'ShopController@orders');
$router->get('/shop/cancellations', 'ShopController@cancellations');
$router->get('/shop/addresses', 'ShopController@addresses');

// My Products Routes
$router->get('/shop/products', 'ProductManagementController@index');
$router->get('/shop/products/show/{id}', 'ProductManagementController@show');
$router->post('/shop/products/update-status', 'ProductManagementController@updateStatus');
$router->post('/shop/products/delete', 'ProductManagementController@delete');
$router->post('/shop/products/toggle-variant', 'ProductManagementController@toggleVariant');
$router->post('/shop/products/bulk-action', 'ProductManagementController@bulkAction');

// <----------------------------------------------------------------------------------------->
// ADMIN ROUTES

// Admin Dashboard
$router->get('/admin/dashboard', 'AdminController@dashboard');

// Admin Sellers Management
$router->get('/admin/sellers', 'AdminController@sellers');
$router->post('/admin/approve-seller', 'AdminController@approveSeller');
$router->post('/admin/reject-seller', 'AdminController@rejectSeller');
$router->post('/admin/suspend-seller', 'AdminController@suspendSeller');

// Admin Settings
$router->get('/admin/settings', 'AdminController@settings');
$router->post('/admin/add-category', 'AdminController@addCategory');
$router->post('/admin/update-category', 'AdminController@updateCategory');
$router->post('/admin/delete-category', 'AdminController@deleteCategory');


