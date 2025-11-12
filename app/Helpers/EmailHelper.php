<?php
// app/Controllers/HomeController.php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;

class HomeController extends Controller {
    
    public function __construct() {
        parent::__construct();
    }

    /**
     * Display the main shop page
     */
    public function index() {
        // Check if user is logged in
        $isLoggedIn = Session::has('user_id');
        $username = Session::get('username', null);
        
        // Get featured/all products (mock data for now)
        // In production: $products = $this->productModel->getAllProducts();
        $products = $this->getMockProducts();
        
        // Get any status messages from URL parameters
        $statusMessage = null;
        $statusType = null;
        if (isset($_GET['status']) && isset($_GET['message'])) {
            $statusType = $_GET['status']; // 'success' or 'error'
            $statusMessage = urldecode($_GET['message']);
        }
        
        $data = [
            'title' => 'Lumora - Exquisite Accessories',
            'isLoggedIn' => $isLoggedIn,
            'username' => $username,
            'products' => $products,
            'statusMessage' => $statusMessage,
            'statusType' => $statusType
        ];
        
        $this->view('home/index', $data);
    }
    
    /**
     * Mock product data (replace with actual database call later)
     */
    private function getMockProducts() {
        return [
            [
                'id' => 1,
                'name' => 'Elegant Pearl Necklace',
                'price' => 2499.00,
                'image' => '/assets/images/products/necklace1.jpg',
                'category' => 'Necklaces'
            ],
            [
                'id' => 2,
                'name' => 'Diamond Stud Earrings',
                'price' => 3999.00,
                'image' => '/assets/images/products/earrings1.jpg',
                'category' => 'Earrings'
            ],
            [
                'id' => 3,
                'name' => 'Gold Charm Bracelet',
                'price' => 1899.00,
                'image' => '/assets/images/products/bracelet1.jpg',
                'category' => 'Bracelets'
            ],
            [
                'id' => 4,
                'name' => 'Silver Ring Set',
                'price' => 1299.00,
                'image' => '/assets/images/products/ring1.jpg',
                'category' => 'Rings'
            ],
            [
                'id' => 5,
                'name' => 'Leather Handbag',
                'price' => 4599.00,
                'image' => '/assets/images/products/bag1.jpg',
                'category' => 'Bags'
            ],
            [
                'id' => 6,
                'name' => 'Designer Sunglasses',
                'price' => 2199.00,
                'image' => '/assets/images/products/sunglasses1.jpg',
                'category' => 'Accessories'
            ]
        ];
    }
    
    /**
     * Handle "Add to Cart" action
     * Redirects to login if not authenticated
     */
    public function addToCart() {
        // Check if user is logged in
        if (!Session::has('user_id')) {
            // Redirect to login with message
            $message = urlencode('Please login to add items to your cart');
            header("Location: /login?status=error&message={$message}&tab=login");
            exit();
        }
        
        // Handle add to cart logic here
        $productId = $_POST['product_id'] ?? null;
        
        if ($productId) {
            // Add to cart logic (you'll implement this later)
            // For now, just show success message
            $message = urlencode('Item added to cart successfully!');
            header("Location: /?status=success&message={$message}");
            exit();
        }
        
        header("Location: /");
        exit();
    }
}