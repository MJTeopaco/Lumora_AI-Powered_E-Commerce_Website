<?php
// app/Controllers/HomeController.php

namespace App\Controllers;

use App\Core\Session;
use App\Core\Controller;
use App\Models\Product;
use App\Models\RememberMeToken;

class HomeController extends Controller {

    protected $productModel;

    public function __construct() {
        $this->productModel = new Product();
        
        // Check for remember-me cookie (but don't force login)
        if (!Session::has('user_id')) {
            $tokenModel = new RememberMeToken();
            $tokenModel->validate(); // This will set session if valid
        }
    }

    /**
     * Show the main landing page (accessible to everyone).
     */
    public function index() {
        // Check if user is logged in
        $isLoggedIn = Session::has('user_id');
        $username = $isLoggedIn ? Session::get('username') : null;

        // Get all products (or featured products)
        $products = $this->productModel->getAllProducts();

        // Check for status messages from URL
        $statusMessage = isset($_GET['message']) ? urldecode($_GET['message']) : null;
        $statusType = $_GET['status'] ?? 'success';

        // Pass data to view
        $data = [
            'isLoggedIn' => $isLoggedIn,
            'username' => $username,
            'products' => $products,
            'statusMessage' => $statusMessage,
            'statusType' => $statusType
        ];

        // Load the view
        $this->view('main_page/index', $data);
    }
}