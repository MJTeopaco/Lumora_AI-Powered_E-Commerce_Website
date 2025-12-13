<?php
// routes/web.php

// GET routes
$router->get('/', 'HomeController@index');
$router->get('/login', 'AuthController@showLogin');
$router->get('/logout', 'LogoutController@logout');

// Profile routes
$router->get('/profile', 'ProfileController@index');
$router->post('/profile/update', 'ProfileController@update');
$router->get('/profile/settings', 'ProfileController@settings');
$router->post('/profile/change-password', 'ProfileController@changePassword');

// Profile Orders
$router->get('/profile/orders', 'ProfileController@orders');
$router->get('/profile/orders/details', 'ProfileController@orderDetails');
$router->post('/profile/orders/cancel', 'ProfileController@cancelOrder');

// Address routes
$router->get('/profile/addresses', 'AddressController@index');
$router->get('/profile/addresses/add', 'AddressController@add');
$router->post('/profile/addresses/add', 'AddressController@store');

// FIXED: Added {id} parameter to the edit route so /edit/4 works
$router->get('/profile/addresses/edit/{id}', 'AddressController@edit');
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
$router->get('/auth/verify-email', 'AuthController@verifyEmail');

// POST routes for AJAX (Resend OTP)
$router->post('/auth/resend-login-otp', 'AuthController@resendLoginOtp');
$router->post('/auth/resend-register-otp', 'AuthController@resendRegisterOtp');
$router->post('/auth/resend-forgot-otp', 'AuthController@resendForgotOtp');

// <----------------------------------------------------------------------------------------->
// SUPPORT ROUTES (NEW)
$router->post('/support/submit', 'SupportController@submit');

// <----------------------------------------------------------------------------------------->
// CART ROUTES (Phase 2)
$router->get('/cart', 'CartController@index');
$router->post('/cart/add', 'CartController@add');
$router->post('/cart/update-quantity', 'CartController@updateQuantity');
$router->post('/cart/remove', 'CartController@remove');
$router->post('/cart/clear', 'CartController@clear');
$router->get('/cart/count', 'CartController@getCartCountAjax');
$router->post('/cart/validate', 'CartController@validateCart');

// <----------------------------------------------------------------------------------------->
// NOTIFICATION ROUTES (Consolidated)
$router->get('/notifications', 'NotificationsController@index');
$router->get('/notifications/get-latest', 'NotificationsController@getLatest');
$router->get('/notifications/get-counts', 'NotificationsController@getCounts');
$router->post('/notifications/mark-read', 'NotificationsController@markAsRead');
$router->post('/notifications/mark-all-read', 'NotificationsController@markAllAsRead');
$router->post('/notifications/delete', 'NotificationsController@deleteNotification');
$router->post('/notifications/delete-all-read', 'NotificationsController@deleteAllRead');

// <----------------------------------------------------------------------------------------->
// SELLER REGISTRATION ROUTES
$router->get('/main/seller-guidelines', 'SellerController@guidelines');
$router->get('/seller/register', 'SellerController@registerForm');
$router->post('/seller/register', 'SellerController@registerSubmit');

// <----------------------------------------------------------------------------------------->
// COLLECTIONS ROUTES (Customer Product Browsing)
$router->get('/collections', 'CollectionsController@index');
$router->get('/collections/index', 'CollectionsController@index');
$router->get('/collections/category/{slug}', 'CollectionsController@byCategory');
$router->post('/collections/smart-search', 'SearchController@smartSearch');

// Stores Directory Routes
$router->get('/stores', 'StoresController@index');
$router->get('/stores/{slug}', 'StoresController@show');

// PRODUCT DETAIL ROUTES (Customer)
$router->get('/products/{slug}', 'ProductController@show');

// <----------------------------------------------------------------------------------------->
// SHOP ROUTES
$router->get('/shop/dashboard', 'ShopController@dashboard');
$router->get('/shop/add-product', 'ShopController@addProduct');
$router->post('/shop/products/store', 'ShopController@storeProduct');
$router->post('/api/products/predict-tags', 'ShopController@predictTags');

$router->get('/shop/products', 'ProductManagementController@index');
$router->get('/shop/cancellations', 'ShopController@cancellations');
$router->get('/shop/addresses', 'ShopController@addresses');

// Shop Orders
$router->get('/shop/orders', 'ShopController@orders');
$router->get('/shop/orders/details', 'ShopController@getOrderDetails');
$router->post('/shop/orders/update-status', 'ShopController@updateOrderStatus');

// Shop Reviews Management
$router->get('/shop/reviews', 'ShopController@reviews');

// My Products Routes
$router->get('/shop/products/view/{id}', 'ProductManagementController@show');
$router->get('/shop/products/edit/{id}', 'ProductManagementController@edit');
$router->post('/shop/products/update/{id}', 'ProductManagementController@update');
$router->post('/shop/products/update-status', 'ProductManagementController@updateStatus');
$router->post('/shop/products/delete', 'ProductManagementController@delete');
$router->post('/shop/products/toggle-variant', 'ProductManagementController@toggleVariant');
$router->post('/shop/products/bulk-action', 'ProductManagementController@bulkAction');

// Shop Profile Routes
$router->get('/shop/shop-profile', 'ShopProfileController@index');
$router->post('/shop/profile/update-basic-info', 'ShopProfileController@updateBasicInfo');
$router->post('/shop/profile/update-address', 'ShopProfileController@updateAddress');
$router->post('/shop/profile/upload-banner', 'ShopProfileController@uploadBanner');
$router->post('/shop/profile/upload-profile', 'ShopProfileController@uploadProfile');

// <----------------------------------------------------------------------------------------->
// CHECKOUT ROUTES (Phase 3)
$router->get('/checkout', 'CheckoutController@index');
$router->post('/checkout/process', 'CheckoutController@process');
$router->get('/checkout/success', 'CheckoutController@success');
$router->get('/checkout/failed', 'CheckoutController@failed');

// <----------------------------------------------------------------------------------------->
// PAYMENT WEBHOOKS
$router->post('/webhooks/paymongo', 'WebhookController@paymongo');
$router->get('/webhooks/test', 'WebhookController@paymongo'); 

// <----------------------------------------------------------------------------------------->
// ADMIN ROUTES
$router->get('/admin/dashboard', 'AdminController@dashboard');
$router->get('/admin/sellers', 'AdminController@sellers');
$router->post('/admin/approve-seller', 'AdminController@approveSeller');
$router->post('/admin/reject-seller', 'AdminController@rejectSeller');
$router->post('/admin/suspend-seller', 'AdminController@suspendSeller');
$router->get('/admin/payouts', 'AdminController@payouts');
$router->post('/admin/payouts/process', 'AdminController@processPayout');
$router->get('/admin/settings', 'AdminController@settings');
$router->post('/admin/add-category', 'AdminController@addCategory');
$router->post('/admin/update-category', 'AdminController@updateCategory');
$router->post('/admin/delete-category', 'AdminController@deleteCategory');
$router->get('/admin/machine-learning-reports', 'AdminController@machineLearningReports');

// User Management Routes
$router->get('/admin/users', 'AdminController@users');
$router->post('/admin/users/unlock', 'AdminController@unlockUser');

// Support Routes (Admin Side)
$router->get('/admin/support', 'AdminController@support');
$router->post('/admin/support/resolve', 'AdminController@resolveTicket');

// Reporting Hub
$router->get('/admin/reports', 'AdminController@reports');

// Audit Logs
$router->get('/admin/audit-logs', 'AdminController@auditLogs');

// Sales Reports
$router->get('/admin/sales-reports', 'AdminController@salesReports');

// <----------------------------------------------------------------------------------------->
// CUSTOMER REVIEWS
$router->get('/reviews/create', 'ReviewController@showReviewForm');
$router->post('/reviews/submit', 'ReviewController@submitReview');
$router->get('/reviews/get-product-reviews', 'ReviewController@getProductReviews');
$router->post('/reviews/update', 'ReviewController@updateReview');
$router->post('/reviews/delete', 'ReviewController@deleteReview');
$router->post('/reviews/mark-helpful', 'ReviewController@markHelpful');
$router->post('/reviews/seller-response', 'ReviewController@addSellerResponse');
$router->get('/profile/reviews', 'ProfileController@myReviews');