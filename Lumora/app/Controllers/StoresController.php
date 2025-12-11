<?php
// app/Controllers/StoresController.php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Shop;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use App\Models\UserProfile;

class StoresController extends Controller {
    
    protected $shopModel;
    protected $productModel;
    protected $userModel;
    protected $profileModel;
    protected $reviewModel;

    public function __construct() {
        $this->shopModel = new Shop();
        $this->productModel = new Product();
        $this->userModel = new User();
        $this->profileModel = new UserProfile();
        $this->reviewModel = new ProductReview();
    }

    /**
     * Helper method to get user data for the header
     */
    private function getUserData() {
        $isLoggedIn = Session::has('user_id');
        $username = null;
        $userProfile = null;
        $isSeller = false;
        $cartCount = 0;
        $notificationCount = 0;

        if ($isLoggedIn) {
            $userId = Session::get('user_id');
            $username = Session::get('username');
            
            $userProfile = $this->profileModel->getByUserId($userId);
            
            if (!$userProfile) {
                $userProfile = [
                    'profile_pic' => '',
                    'full_name' => '', 
                    'phone_number' => '', 
                    'gender' => '', 
                    'birth_date' => ''
                ];
            }
            
            $isSeller = $this->userModel->checkRole($userId);
            // Cart/Notif counts handled by partials or models directly
        }

        return [
            'isLoggedIn' => $isLoggedIn,
            'username' => $username,
            'userProfile' => $userProfile,
            'isSeller' => $isSeller,
            'cartCount' => $cartCount,
            'notificationCount' => $notificationCount
        ];
    }

    /**
     * Display stores directory
     */
    public function index() {
        $region = $_GET['region'] ?? null;
        $specialty = $_GET['specialty'] ?? null;
        $search = $_GET['search'] ?? '';

        $allShops = $this->shopModel->getAllActiveShops();

        // Filter by search
        if (!empty($search)) {
            $allShops = array_filter($allShops, function($shop) use ($search) {
                return stripos($shop['shop_name'], $search) !== false || 
                       stripos($shop['shop_description'], $search) !== false ||
                       stripos($shop['city'], $search) !== false ||
                       stripos($shop['province'], $search) !== false;
            });
        }

        // Filter by region
        if ($region && $region !== 'all') {
            $allShops = array_filter($allShops, function($shop) use ($region) {
                return !empty($shop['region']) && stripos($shop['region'], $region) !== false;
            });
        }

        // Filter by specialty
        if ($specialty && $specialty !== 'all') {
            $allShops = array_filter($allShops, function($shop) use ($specialty) {
                return !empty($shop['specialties']) && stripos($shop['specialties'], $specialty) !== false;
            });
        }

        $featuredSellers = $this->shopModel->getFeaturedShops(3);
        $regions = $this->shopModel->getAvailableRegions();
        $specialties = $this->shopModel->getAvailableSpecialties();

        // Get product previews
        foreach ($allShops as &$shop) {
            $shop['product_previews'] = $this->shopModel->getShopProductPreviews($shop['shop_id'], 3);
            $shop['total_products'] = $this->shopModel->getShopProductCount($shop['shop_id']);
        }

        foreach ($featuredSellers as &$seller) {
            $seller['product_previews'] = $this->shopModel->getShopProductPreviews($seller['shop_id'], 3);
            $seller['total_products'] = $this->shopModel->getShopProductCount($seller['shop_id']);
        }

        $userData = $this->getUserData();

        $data = array_merge($userData, [
            'pageTitle' => 'Meet the Makers - Lumora',
            'shops' => array_values($allShops),
            'featuredSellers' => $featuredSellers,
            'regions' => $regions,
            'specialties' => $specialties,
            'currentRegion' => $region,
            'currentSpecialty' => $specialty,
            'searchTerm' => $search,
            'totalShops' => count($allShops)
        ]);

        $this->view('stores/index', $data);
    }

    /**
     * Display individual shop page
     */
    public function show($shopSlug) {
        $shop = $this->shopModel->getShopBySlug($shopSlug);
        
        if (!$shop) {
            $this->redirect('/stores');
            return;
        }

        $products = $this->shopModel->getShopProducts($shop['shop_id']);
        
        // Add review stats
        foreach ($products as &$product) {
            $stats = $this->reviewModel->getProductReviewStats($product['product_id']);
            $product['average_rating'] = $stats['average_rating'] ?? 0;
            $product['review_count'] = $stats['total_reviews'] ?? 0;
        }

        $stats = $this->shopModel->getDashboardStats($shop['shop_id']);
        $userData = $this->getUserData();

        $data = array_merge($userData, [
            'pageTitle' => $shop['shop_name'] . ' - Lumora',
            'shop' => $shop,
            'products' => $products,
            'stats' => $stats
        ]);

        $this->view('stores/show', $data);
    }
}