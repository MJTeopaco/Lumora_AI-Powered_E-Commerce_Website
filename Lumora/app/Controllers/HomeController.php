<?php
// app/Controllers/HomeController.php

namespace App\Controllers;

use App\Core\Session;
use App\Core\Controller;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\RememberMeToken;
use App\Models\User;
use App\Models\UserProfile;

class HomeController extends Controller {

    protected $productModel;
    protected $userModel;
    protected $profileModel;
    protected $reviewModel;

    public function __construct() {
        $this->productModel = new Product();
        $this->userModel = new User();
        $this->profileModel = new UserProfile();
        $this->reviewModel = new ProductReview();
        
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
        $username = null;
        $email = null;
        $userProfile = null;
        $notificationCount = 0;
        $cartCount = 0;
        $userId = null;

        if ($isLoggedIn) {
            $userId = Session::get('user_id');
            $username = Session::get('username');
            
            // Get user data for email
            $user = $this->userModel->findById($userId);
            $email = $user['email'] ?? '';
            
            // Get user profile
            $userProfile = $this->profileModel->getByUserId($userId);
            
            // If no profile exists, create default structure
            if (!$userProfile) {
                $userProfile = [
                    'profile_pic' => '',
                    'full_name' => '',
                    'phone_number' => '',
                    'gender' => '',
                    'birth_date' => ''
                ];
            }
            
            // TODO: Get actual notification count from notifications table
            // $notificationCount = $this->getUnreadNotificationCount($userId);
            $notificationCount = 0; // Placeholder
            
            // TODO: Get actual cart count from cart table
            // $cartCount = $this->getCartItemCount($userId);
            $cartCount = 0; // Placeholder
        }

        // Check if user is a seller
        $isSeller = $userId ? $this->userModel->checkRole($userId) : false;

        // 1. Get Real-Time Category Counts
        $categoryCounts = $this->productModel->getCategoryCounts();

        // 2. Get all products (or featured products)
        $products = $this->productModel->getAllProducts(8);

        // 3. Get Review Stats for each product
        foreach ($products as &$product) {
            $stats = $this->reviewModel->getProductReviewStats($product['id']);
            $product['average_rating'] = $stats['average_rating'] ?? 0;
            $product['review_count'] = $stats['total_reviews'] ?? 0;
        }
        unset($product); // Break reference

        // Check for status messages from URL
        $statusMessage = isset($_GET['message']) ? urldecode($_GET['message']) : null;
        $statusType = $_GET['status'] ?? 'success';

        // Pass data to view
        $data = [
            'isLoggedIn' => $isLoggedIn,
            'username' => $username,
            'email' => $email,
            'userProfile' => $userProfile,
            'notificationCount' => $notificationCount,
            'cartCount' => $cartCount,
            'products' => $products,
            'categoryCounts' => $categoryCounts, // Pass dynamic counts
            'statusMessage' => $statusMessage,
            'statusType' => $statusType,
            'isSeller' => $isSeller
        ];

        // Load the view
        $this->view('main/main', $data);
    }
    
    /**
     * Get unread notification count for user
     * TODO: Implement when notifications table is ready
     */
    private function getUnreadNotificationCount($userId) {
        // Placeholder - implement when notifications feature is ready
        return 0;
    }
    
    /**
     * Get cart item count for user
     * TODO: Implement when cart functionality is ready
     */
    private function getCartItemCount($userId) {
        // Placeholder - implement when cart is ready
        return 0;
    }
}