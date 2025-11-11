<?php
// app/Controllers/LogoutController.php

namespace App\Controllers;

use App\Core\Session;
use App\Models\RememberMeToken;
use App\Helpers\RedirectHelper;

class LogoutController {
    
    public function logout() {
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
        
        // Redirect to login page
        RedirectHelper::redirectWithSuccess('You have been logged out.', 'login', 'credentials');
    }
}