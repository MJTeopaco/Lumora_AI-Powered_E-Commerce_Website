<?php
// app/Controllers/CartController.php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Helpers\RedirectHelper;

class CartController extends Controller {
    
    public function __construct() {
        // Require authentication
        if (!Session::has('user_id')) {
            RedirectHelper::redirect('/login');
        }
    }
    
    /**
     * Show cart page (placeholder)
     */
    public function index() {
        $data = [
            'username' => Session::get('username'),
            'statusMessage' => 'Cart feature coming soon!',
            'statusType' => 'info'
        ];
        
        // For now, redirect back to home with message
        RedirectHelper::redirect('/?status=info&message=' . urlencode('Cart feature coming soon!'));
    }
}