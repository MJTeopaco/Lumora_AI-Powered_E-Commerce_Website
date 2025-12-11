<?php
// app/Controllers/LogoutController.php

namespace App\Controllers;

use App\Core\Session;
use App\Models\RememberMeToken;
use App\Helpers\RedirectHelper;
use App\Core\Controller; // Added use statement for Controller base class

class LogoutController extends Controller { // Extends Controller to use verifyCsrfToken
    
    public function logout() {
        // ADDED: CSRF Protection
        $this->verifyCsrfToken();

        // Delete the remember me token from DB and cookie
        if (isset($_COOKIE['remember_me'])) {
            list($selector) = explode(':', $_COOKIE['remember_me'], 2);
            if ($selector) {
                $tokenModel = new RememberMeToken();
                $tokenModel->delete($selector);
            }
        }
        
        // Destroy the session
        Session::destroy();
        
        // Delete the remember me cookie
        if (isset($_COOKIE['remember_me'])) {
            setcookie('remember_me', '', time() - 3600, '/', '', false, true);
        }
        
        // Redirect to home page with success message
        RedirectHelper::redirectToIndexSuccess('You have been logged out successfully.');
    }
}