<?php
// app/Controllers/ProductController.php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Models\UserProfile;

class ProductController extends Controller {
    
    protected $productModel;
    protected $shopModel;
    protected $userModel;
    protected $profileModel;

    public function __construct() {
        $this->productModel = new Product();
        $this->shopModel = new Shop();
        $this->userModel = new User();
        $this->profileModel = new UserProfile();
    }

    /**
     * Show single product detail page (customer-facing)
     * @param string $slug - Product slug from URL
     */
    public function show($slug) {
        // --- 1. Get Product Data ---
        $product = $this->productModel->getSingleProduct($slug);

        if (!$product) {
            $this->redirect('/404');
        }

        $product['variants'] = $this->productModel->getProductVariants($product['id']);

        // Get shop information
        $conn = $this->productModel->getConnection();
        $stmt = $conn->prepare("SELECT shop_id FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $productData = $result->fetch_assoc();
        $stmt->close();

        $shop = null;
        if ($productData && $productData['shop_id']) {
            $shop = $this->shopModel->getShopById($productData['shop_id']);
        }

        $categories = $this->productModel->getProductCategories($product['id']);
        $tags = $this->productModel->getProductTags($product['id']);

        // --- 2. Get User/Header Data (Fixes "Sign In" Issue) ---
        $isLoggedIn = Session::has('user_id');
        $username = null;
        $userProfile = null;
        $notificationCount = 0;
        $cartCount = 0;
        $isSeller = false;

        if ($isLoggedIn) {
            $userId = Session::get('user_id');
            $username = Session::get('username');
            
            // Get user profile for avatar
            $userProfile = $this->profileModel->getByUserId($userId);
            if (!$userProfile) {
                $userProfile = ['profile_pic' => ''];
            }
            
            // Check seller status
            $isSeller = $this->userModel->checkRole($userId);
            
            // Placeholder counts (implement real logic as needed)
            $notificationCount = 0; 
            $cartCount = 0; 
        }

        $data = [
            'pageTitle' => $product['name'] . ' - Lumora',
            'product' => $product,
            'shop' => $shop,
            'categories' => $categories,
            'tags' => $tags,
            // Pass header data to view
            'isLoggedIn' => $isLoggedIn,
            'username' => $username,
            'userProfile' => $userProfile,
            'notificationCount' => $notificationCount,
            'cartCount' => $cartCount,
            'isSeller' => $isSeller
        ];

        $this->view('products/detail', $data);
    }

    /**
     * Search products (AJAX endpoint)
     */
    public function search() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        $searchTerm = $_GET['q'] ?? '';

        if (empty($searchTerm)) {
            echo json_encode(['success' => false, 'message' => 'Search term required']);
            exit;
        }

        $products = $this->productModel->getProductsBySearch($searchTerm);

        echo json_encode([
            'success' => true,
            'products' => $products,
            'count' => count($products)
        ]);
        exit;
    }

    /**
     * Get all products (for collections page)
     */
    public function index() {
        // --- Get User Data for Header ---
        $isLoggedIn = Session::has('user_id');
        $username = null;
        $userProfile = null;
        $notificationCount = 0;
        $cartCount = 0;
        $isSeller = false;

        if ($isLoggedIn) {
            $userId = Session::get('user_id');
            $username = Session::get('username');
            $userProfile = $this->profileModel->getByUserId($userId);
            $isSeller = $this->userModel->checkRole($userId);
        }

        // --- Get Product Data ---
        $category = $_GET['category'] ?? null;
        $sort = $_GET['sort'] ?? 'newest';
        $search = $_GET['search'] ?? '';

        if (!empty($search)) {
            $products = $this->productModel->getProductsBySearch($search);
        } else {
            $products = $this->productModel->getAllProducts();
        }

        if ($category) {
            $products = array_filter($products, function($product) use ($category) {
                return stripos($product['categories'], $category) !== false;
            });
        }

        switch ($sort) {
            case 'price-low':
                usort($products, function($a, $b) { return $a['price'] <=> $b['price']; });
                break;
            case 'price-high':
                usort($products, function($a, $b) { return $b['price'] <=> $a['price']; });
                break;
            case 'name':
                usort($products, function($a, $b) { return strcmp($a['name'], $b['name']); });
                break;
        }

        $categories = $this->productModel->getAllCategories();

        $data = [
            'pageTitle' => 'Shop All Products - Lumora',
            'products' => $products,
            'categories' => $categories,
            'currentCategory' => $category,
            'currentSort' => $sort,
            'searchTerm' => $search,
            // Header data
            'isLoggedIn' => $isLoggedIn,
            'username' => $username,
            'userProfile' => $userProfile,
            'notificationCount' => $notificationCount,
            'cartCount' => $cartCount,
            'isSeller' => $isSeller
        ];

        $this->view('collections/index', $data);
    }

    /**
     * Get product by category
     */
    public function byCategory($categorySlug) {
        // --- Get User Data for Header ---
        $isLoggedIn = Session::has('user_id');
        $username = null;
        $userProfile = null;
        $notificationCount = 0;
        $cartCount = 0;
        $isSeller = false;

        if ($isLoggedIn) {
            $userId = Session::get('user_id');
            $username = Session::get('username');
            $userProfile = $this->profileModel->getByUserId($userId);
            $isSeller = $this->userModel->checkRole($userId);
        }

        // --- Get Category Data ---
        $conn = $this->productModel->getConnection();
        $stmt = $conn->prepare("SELECT * FROM product_categories WHERE slug = ?");
        $stmt->bind_param("s", $categorySlug);
        $stmt->execute();
        $result = $stmt->get_result();
        $category = $result->fetch_assoc();
        $stmt->close();

        if (!$category) {
            $this->redirect('/collections/index');
        }

        $stmt = $conn->prepare("
            SELECT 
                p.product_id as id,
                p.name,
                p.short_description,
                p.cover_picture as image,
                MIN(pv.price) as price,
                SUM(pv.quantity) as stock,
                p.slug
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

        $categories = $this->productModel->getAllCategories();

        $data = [
            'pageTitle' => $category['name'] . ' - Lumora',
            'category' => $category,
            'products' => $products,
            'categories' => $categories,
            // Header data
            'isLoggedIn' => $isLoggedIn,
            'username' => $username,
            'userProfile' => $userProfile,
            'notificationCount' => $notificationCount,
            'cartCount' => $cartCount,
            'isSeller' => $isSeller
        ];

        $this->view('collections/category', $data);
    }
}