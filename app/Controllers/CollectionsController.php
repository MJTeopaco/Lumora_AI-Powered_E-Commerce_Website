<?php
// app/Controllers/CollectionsController.php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Product;
use App\Models\User;
use App\Models\UserProfile;

class CollectionsController extends Controller {
    
    protected $productModel;
    protected $userModel;
    protected $profileModel;

    public function __construct() {
        $this->productModel = new Product();
        $this->userModel = new User();
        $this->profileModel = new UserProfile();
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
            
            // Get user profile
            $userProfile = $this->profileModel->getByUserId($userId);
            
            // Default profile if empty
            if (!$userProfile) {
                $userProfile = [
                    'profile_pic' => '',
                    'full_name' => '', 
                    'phone_number' => '', 
                    'gender' => '', 
                    'birth_date' => ''
                ];
            }
            
            // Check if seller
            $isSeller = $this->userModel->checkRole($userId);
            
            // Optional: Add logic to fetch actual cart/notification counts here
            $cartCount = 0; 
            $notificationCount = 0;
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
     * Display all products with filters
     */
    public function index() {
        // Get filter parameters
        $category = $_GET['category'] ?? null;
        $sort = $_GET['sort'] ?? 'newest';
        $search = $_GET['search'] ?? '';
        $priceMin = $_GET['price_min'] ?? null;
        $priceMax = $_GET['price_max'] ?? null;

        // Get all products
        if (!empty($search)) {
            $products = $this->productModel->getProductsBySearch($search);
        } else {
            $products = $this->productModel->getAllProducts();
        }

        // Filter by category
        if ($category) {
            $products = array_filter($products, function($product) use ($category) {
                // FIXED: Check if 'categories' exists and is not null before checking string position
                return !empty($product['categories']) && stripos($product['categories'], $category) !== false;
            });
        }

        // Filter by price range
        if ($priceMin !== null || $priceMax !== null) {
            $products = array_filter($products, function($product) use ($priceMin, $priceMax) {
                $price = floatval($product['price']);
                if ($priceMin !== null && $price < floatval($priceMin)) {
                    return false;
                }
                if ($priceMax !== null && $price > floatval($priceMax)) {
                    return false;
                }
                return true;
            });
        }

        // Sort products
        switch ($sort) {
            case 'price-low':
                usort($products, function($a, $b) {
                    return floatval($a['price']) <=> floatval($b['price']);
                });
                break;
            case 'price-high':
                usort($products, function($a, $b) {
                    return floatval($b['price']) <=> floatval($a['price']);
                });
                break;
            case 'name':
                usort($products, function($a, $b) {
                    return strcmp($a['name'], $b['name']);
                });
                break;
            case 'popular':
                // For now, just random shuffle
                shuffle($products);
                break;
            // 'newest' is default, already sorted by created_at DESC
        }

        // Get all categories for filter
        $categories = $this->productModel->getAllCategories();

        // Get User Data for Header
        $userData = $this->getUserData();

        $data = array_merge($userData, [
            'pageTitle' => 'Shop All Products - Lumora',
            'products' => array_values($products), // Re-index array
            'categories' => $categories,
            'currentCategory' => $category,
            'currentSort' => $sort,
            'searchTerm' => $search,
            'priceMin' => $priceMin,
            'priceMax' => $priceMax,
            'totalProducts' => count($products)
        ]);

        $this->view('collections/index', $data);
    }

    /**
     * Get products by category
     */
    public function byCategory($categorySlug) {
        $conn = $this->productModel->getConnection();
        
        // Get category details
        $stmt = $conn->prepare("
            SELECT * FROM product_categories WHERE slug = ?
        ");
        $stmt->bind_param("s", $categorySlug);
        $stmt->execute();
        $result = $stmt->get_result();
        $category = $result->fetch_assoc();
        $stmt->close();

        if (!$category) {
            $this->redirect('/collections/index');
            return;
        }

        // Get products in this category
        $stmt = $conn->prepare("
            SELECT 
                p.product_id as id,
                p.name,
                p.short_description,
                p.cover_picture as image,
                MIN(pv.price) as price,
                SUM(pv.quantity) as stock,
                p.slug,
                GROUP_CONCAT(DISTINCT pc.name SEPARATOR ', ') as categories
            FROM products p
            INNER JOIN product_category_links pcl ON p.product_id = pcl.product_id
            INNER JOIN product_categories pc ON pcl.category_id = pc.category_id
            LEFT JOIN product_variants pv ON p.product_id = pv.product_id AND pv.is_active = 1
            WHERE pc.slug = ? 
                AND p.status = 'PUBLISHED' 
                AND p.is_deleted = 0
            GROUP BY p.product_id
            ORDER BY p.created_at DESC
        ");
        $stmt->bind_param("s", $categorySlug);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        $stmt->close();

        // Get all categories for navigation
        $categories = $this->productModel->getAllCategories();

        // Get User Data for Header
        $userData = $this->getUserData();

        $data = array_merge($userData, [
            'pageTitle' => $category['name'] . ' - Lumora',
            'category' => $category,
            'products' => $products,
            'categories' => $categories,
            'currentCategory' => $categorySlug,
            'totalProducts' => count($products)
        ]);

        $this->view('collections/index', $data);
    }
}