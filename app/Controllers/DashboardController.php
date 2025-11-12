<?php
// app/Controllers/DashboardController.php

namespace App\Controllers;

use App\Core\Session;
use App\Helpers\RedirectHelper;
use App\Models\RememberMeToken;

class DashboardController extends \App\Core\Controller {

    public function __construct() {
        // Check session
        if (!Session::has('user_id')) {
            // No session, check for remember-me cookie
            $tokenModel = new RememberMeToken();
            if (!$tokenModel->validate()) {
                // No valid session or cookie, redirect to login
                RedirectHelper::redirect('/login');
            }
            // If validate() is true, session is set, and we can proceed.
        }
    }

    /**
     * Show the main dashboard (welcome page).
     */
    public function index() {
        $username = Session::get('username');
        require __DIR__ . '/../../views/dashboard/index.php';
    }
}