<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Seller;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Cart;
use App\Models\Notification;

class SellerController extends Controller {

    protected $sellerModel;
    protected $userModel;

    public function __construct() {
        $this->sellerModel = new Seller(); 
        $this->userModel = new User();
    }

    /**
     * Helper to fetch common header data (Profile, Cart, Notifications)
     */
    private function getHeaderData($userId) {
        $userProfile = (new UserProfile())->getByUserId($userId);
        $cartCount = (new Cart())->getCartCount($userId);
        $notifCount = (new Notification())->getUnreadCount($userId);
        $isSeller = $this->userModel->checkRole($userId);
        $username = Session::get('username');

        return [
            'isLoggedIn' => true,
            'username' => $username,
            'userProfile' => $userProfile,
            'cartCount' => $cartCount,
            'notificationCount' => $notifCount,
            'isSeller' => $isSeller
        ];
    }

    /**
     * Display seller guidelines page
     */
    public function guidelines() {
        $isLoggedIn = Session::has('user_id');
        $data = [];

        if ($isLoggedIn) {
            $userId = Session::get('user_id');
            $data = $this->getHeaderData($userId);
        } else {
            // Default data for guests
            $data = [
                'isLoggedIn' => false,
                'cartCount' => 0,
                'notificationCount' => 0,
                'userProfile' => null,
                'isSeller' => false
            ];
        }
        
        $data['pageTitle'] = 'Seller Guidelines - Lumora';
        $this->view('main/seller-guidelines', $data);
    }

    /**
     * Display seller registration form or appropriate status page
     */
    public function registerForm() {
        // Check if user is logged in
        if (!Session::has('user_id')) {
            Session::set('error', 'You must be logged in to register as a seller');
            header('Location: /login');
            exit();
        }

        $userId = Session::get('user_id');
        
        // 1. Fetch Header Data so Navbar works
        $headerData = $this->getHeaderData($userId);

        // 2. Check seller status
        $sellerStatus = $this->sellerModel->getSellerStatus($userId);

        // 3. Route based on status
        switch ($sellerStatus['status']) {
            case 'approved':
                // FIX: Redirect to the correct shop dashboard route (was /seller/dashboard)
                header('Location: /shop/dashboard');
                exit();
                
            case 'pending':
                // Show pending approval page with header data
                $data = array_merge($headerData, [
                    'shopName' => $sellerStatus['shop_name'],
                    'appliedAt' => $sellerStatus['applied_at'],
                    'pageTitle' => 'Application Pending - Lumora'
                ]);
                $this->view('seller/pending-approval', $data);
                break;
                
            case 'none':
            default:
                // Show registration form with header data
                $data = array_merge($headerData, [
                    'regions' => $this->getPhilippineRegions(),
                    'pageTitle' => 'Become a Seller - Lumora'
                ]);
                $this->view('seller/register', $data);
                break;
        }
    }

    /**
     * Handle seller registration form submission
     */
    public function registerSubmit() {
        $this->verifyCsrfToken();

        // Check if user is logged in
        if (!Session::has('user_id')) {
            Session::set('error', 'You must be logged in to register as a seller');
            header('Location: /login');
            exit();
        }

        $userId = Session::get('user_id');

        // Check if user already has a shop
        $sellerStatus = $this->sellerModel->getSellerStatus($userId);
        if ($sellerStatus['status'] !== 'none') {
            Session::set('error', 'You already have a seller application');
            header('Location: /seller/register');
            exit();
        }

        // Validate and sanitize input
        $validationResult = $this->validateRegistrationInput($_POST);
        if (!$validationResult['valid']) {
            Session::set('error', $validationResult['error']);
            header('Location: /seller/register');
            exit();
        }

        $data = $validationResult['data'];

        // Begin transaction
        $this->sellerModel->beginTransaction();

        try {
            // Save shop information
            $shopSaved = $this->sellerModel->saveRegister(
                $userId,
                $data['shop_name'],
                $data['contact_email'],
                $data['contact_phone'],
                $data['shop_description'],
                $data['slug']
            );

            if (!$shopSaved) {
                throw new \Exception('Failed to register shop');
            }

            // Save shop address
            $addressSaved = $this->sellerModel->saveShopAddress(
                $userId,
                $data['address_line_1'],
                $data['address_line_2'],
                $data['barangay'],
                $data['city'],
                $data['province'],
                $data['region'],
                $data['postal_code']
            );

            if (!$addressSaved) {
                throw new \Exception('Failed to save shop address');
            }

            // Request seller role approval
            $approvalRequested = $this->sellerModel->requestApproval($userId);

            if (!$approvalRequested) {
                throw new \Exception('Failed to request approval');
            }

            // Commit transaction
            $this->sellerModel->commit();

            // Success message
            Session::set('success', 'Your seller application has been submitted successfully! Our team will review your application within 24-48 hours.');
            header('Location: /seller/register');
            exit();

        } catch (\Exception $e) {
            // Rollback transaction on error
            $this->sellerModel->rollback();
            Session::set('error', $e->getMessage());
            header('Location: /seller/register');
            exit();
        }
    }

    /**
     * Validate registration input
     */
    private function validateRegistrationInput($post) {
        $errors = [];

        // Required fields
        $requiredFields = [
            'shop_name', 'contact_email', 'contact_phone',
            'address_line_1', 'barangay', 'city', 'province', 'region', 'postal_code'
        ];

        foreach ($requiredFields as $field) {
            if (empty($post[$field])) {
                return ['valid' => false, 'error' => 'Please fill in all required fields'];
            }
        }

        // Validate terms checkbox
        if (!isset($post['terms'])) {
            return ['valid' => false, 'error' => 'You must agree to the Terms and Conditions'];
        }

        // Validate email format
        if (!filter_var($post['contact_email'], FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'error' => 'Invalid email format'];
        }

        // Validate postal code format (4 digits)
        if (!preg_match('/^\d{4}$/', $post['postal_code'])) {
            return ['valid' => false, 'error' => 'Postal code must be 4 digits'];
        }

        // Validate phone number
        if (!preg_match('/^[\+]?[0-9\s\-\(\)]{10,}$/', $post['contact_phone'])) {
            return ['valid' => false, 'error' => 'Invalid phone number format'];
        }

        // Sanitize and prepare data
        $data = [
            'shop_name' => trim($post['shop_name']),
            'contact_email' => trim($post['contact_email']),
            'contact_phone' => trim($post['contact_phone']),
            'shop_description' => trim($post['shop_description'] ?? ''),
            'address_line_1' => trim($post['address_line_1']),
            'address_line_2' => trim($post['address_line_2'] ?? ''),
            'barangay' => trim($post['barangay']),
            'city' => trim($post['city']),
            'province' => trim($post['province']),
            'region' => trim($post['region']),
            'postal_code' => trim($post['postal_code']),
            'slug' => strtolower(str_replace(' ', '-', trim($post['shop_name'])))
        ];

        return ['valid' => true, 'data' => $data];
    }

    /**
     * Get Philippine regions
     */
    private function getPhilippineRegions() {
        return [
            'NCR' => 'NCR - National Capital Region',
            'CAR' => 'CAR - Cordillera Administrative Region',
            'Region I' => 'Region I - Ilocos Region',
            'Region II' => 'Region II - Cagayan Valley',
            'Region III' => 'Region III - Central Luzon',
            'Region IV-A' => 'Region IV-A - CALABARZON',
            'Region IV-B' => 'Region IV-B - MIMAROPA',
            'Region V' => 'Region V - Bicol Region',
            'Region VI' => 'Region VI - Western Visayas',
            'Region VII' => 'Region VII - Central Visayas',
            'Region VIII' => 'Region VIII - Eastern Visayas',
            'Region IX' => 'Region IX - Zamboanga Peninsula',
            'Region X' => 'Region X - Northern Mindanao',
            'Region XI' => 'Region XI - Davao Region',
            'Region XII' => 'Region XII - SOCCSKSARGEN',
            'Region XIII' => 'Region XIII - Caraga',
            'BARMM' => 'BARMM - Bangsamoro Autonomous Region'
        ];
    }

    /**
     * Seller Dashboard
     */
    public function dashboard() {
        // Check if user is logged in
        if (!Session::has('user_id')) {
            header('Location: /login');
            exit();
        }

        $userId = Session::get('user_id');
        
        // Check if user is an approved seller
        $sellerStatus = $this->sellerModel->getSellerStatus($userId);
        
        if ($sellerStatus['status'] !== 'approved') {
            Session::set('error', 'You do not have access to the seller dashboard');
            header('Location: /');
            exit();
        }

        // Get shop data
        $shopData = $this->sellerModel->getShopByUserId($userId);

        $data = [
            'shop' => $shopData
        ];

        $this->view('seller/shop-dashboard', $data, 'seller');
    }
}
?>