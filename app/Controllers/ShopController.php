<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Shop;
use App\Models\Product;
use App\Models\User;

class ShopController extends Controller {

    protected $shopModel;
    protected $productModel;
    protected $userModel;

    public function __construct() {
        $this->shopModel = new Shop(); 
        $this->productModel = new Product();
        $this->userModel = new User();
    }

    /**
     * Display the shop dashboard
     */
    public function dashboard() {
        // Check if user is logged in
        if (!Session::has('user_id')) {
            Session::set('error', 'Please login to access the shop dashboard');
            $this->redirect('/login');
        }

        $userId = Session::get('user_id');
        
        // Check if user is an approved seller
        $sellerStatus = $this->shopModel->getSellerStatus($userId);
        
        if (!$sellerStatus || $sellerStatus['is_approved'] != 1) {
            Session::set('error', 'You do not have access to the seller dashboard. Please register as a seller first.');
            $this->redirect('/seller/register');
        }

        // Get shop data
        $shopData = $this->shopModel->getShopByUserId($userId);
        
        if (!$shopData) {
            Session::set('error', 'Shop not found. Please contact support.');
            $this->redirect('/');
        }

        // Get dashboard statistics
        $stats = $this->shopModel->getDashboardStats($shopData['shop_id']);
        
        // Get recent orders
        $recentOrders = $this->shopModel->getRecentOrders($shopData['shop_id'], 5);
        
        // Get top selling products
        $topProducts = $this->shopModel->getTopProducts($shopData['shop_id'], 5);

        $data = [
            'pageTitle' => 'Shop Dashboard',
            'currentPage' => 'dashboard',
            'shop' => $shopData,
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'topProducts' => $topProducts
        ];

        $this->view('shop/shop-dashboard', $data, 'shop');
    }

    /**
     * Display products list
     */
    public function products() {
        if (!Session::has('user_id')) {
            Session::set('error', 'Please login to access this page');
            $this->redirect('/login');
        }

        $userId = Session::get('user_id');
        $shopData = $this->shopModel->getShopByUserId($userId);
        
        if (!$shopData) {
            Session::set('error', 'Shop not found');
            $this->redirect('/');
        }

        $products = $this->shopModel->getShopProducts($shopData['shop_id']);

        $data = [
            'pageTitle' => 'My Products',
            'currentPage' => 'products',
            'shop' => $shopData,
            'products' => $products
        ];

        $this->view('shop/products', $data, 'shop');
    }

    /**
     * Display add product form
     */
    public function addProduct() {
        if (!Session::has('user_id')) {
            Session::set('error', 'Please login to access this page');
            $this->redirect('/login');
        }

        $userId = Session::get('user_id');
        $shopData = $this->shopModel->getShopByUserId($userId);
        
        if (!$shopData) {
            Session::set('error', 'Shop not found');
            $this->redirect('/');
        }

        // Get all categories
        $categories = $this->shopModel->getAllCategories();

        $data = [
            'pageTitle' => 'Add New Product',
            'currentPage' => 'add-product',
            'shop' => $shopData,
            'categories' => $categories
        ];

        $this->view('shop/add-product', $data, 'shop');
    }

    /**
     * Store new product with multiple variants
     */
    public function storeProduct() {
        if (!Session::has('user_id')) {
            Session::set('error', 'Please login to access this page');
            $this->redirect('/login');
        }

        // Validate POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Session::set('error', 'Invalid request method');
            $this->redirect('/shop/add-product');
        }

        $userId = Session::get('user_id');
        $shopData = $this->shopModel->getShopByUserId($userId);
        
        if (!$shopData) {
            Session::set('error', 'Shop not found');
            $this->redirect('/');
        }

        // Validate required fields
        $requiredFields = ['product_name', 'short_description', 'description', 'category_id', 'status'];
        $errors = [];
        
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }

        // Validate variants
        if (empty($_POST['variants']) || !is_array($_POST['variants'])) {
            $errors[] = 'At least one product variant is required';
        }

        if (!empty($errors)) {
            Session::set('error', implode(', ', $errors));
            $this->redirect('/shop/add-product');
        }

        // Handle cover image upload
        $coverPicture = null;
        if (isset($_FILES['cover_picture']) && $_FILES['cover_picture']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->uploadProductImage($_FILES['cover_picture'], $shopData['shop_id']);
            
            if ($uploadResult['success']) {
                $coverPicture = $uploadResult['filename'];
            } else {
                Session::set('error', 'Cover image upload failed: ' . $uploadResult['message']);
                $this->redirect('/shop/add-product');
            }
        } else {
            Session::set('error', 'Cover image is required');
            $this->redirect('/shop/add-product');
        }

        // Generate slug from product name
        $slug = $this->generateSlug($_POST['product_name']);

        // Prepare product data
        $productData = [
            'shop_id' => $shopData['shop_id'],
            'name' => trim($_POST['product_name']),
            'slug' => $slug,
            'short_description' => trim($_POST['short_description']),
            'description' => trim($_POST['description']),
            'cover_picture' => $coverPicture,
            'status' => $_POST['status']
        ];

        // Start transaction
        $this->productModel->conn->begin_transaction();

        try {
            // Create product
            $productId = $this->productModel->createProduct($productData);

            if (!$productId) {
                throw new \Exception('Failed to create product');
            }

            // Link product to category
            if (!$this->productModel->linkProductToCategory($productId, $_POST['category_id'])) {
                throw new \Exception('Failed to link product to category');
            }

            // Process variants
            $variantsProcessed = 0;
            foreach ($_POST['variants'] as $variantIndex => $variantData) {
                // Validate variant required fields
                if (empty($variantData['price']) || empty($variantData['quantity'])) {
                    continue; // Skip invalid variants
                }

                // Handle variant image upload
                $variantImage = null;
                $fileKey = "variants_{$variantIndex}_image";
                
                if (isset($_FILES['variants']['name'][$variantIndex]['image']) && 
                    $_FILES['variants']['error'][$variantIndex]['image'] === UPLOAD_ERR_OK) {
                    
                    // Restructure the file array for easier handling
                    $variantFile = [
                        'name' => $_FILES['variants']['name'][$variantIndex]['image'],
                        'type' => $_FILES['variants']['type'][$variantIndex]['image'],
                        'tmp_name' => $_FILES['variants']['tmp_name'][$variantIndex]['image'],
                        'error' => $_FILES['variants']['error'][$variantIndex]['image'],
                        'size' => $_FILES['variants']['size'][$variantIndex]['image']
                    ];
                    
                    $uploadResult = $this->uploadProductImage($variantFile, $shopData['shop_id'], 'variant');
                    
                    if ($uploadResult['success']) {
                        $variantImage = $uploadResult['filename'];
                    }
                }

                // Generate SKU if not provided
                $sku = !empty($variantData['sku']) ? trim($variantData['sku']) : $this->generateSKU($productId, $variantsProcessed + 1);

                // Check for duplicate SKU
                if ($this->productModel->skuExists($sku)) {
                    $sku = $this->generateSKU($productId, $variantsProcessed + 1);
                }

                // Prepare variant data
                $variantInsertData = [
                    'product_id' => $productId,
                    'variant_name' => !empty($variantData['name']) ? trim($variantData['name']) : null,
                    'sku' => $sku,
                    'price' => floatval($variantData['price']),
                    'quantity' => intval($variantData['quantity']),
                    'color' => !empty($variantData['color']) ? trim($variantData['color']) : null,
                    'size' => !empty($variantData['size']) ? trim($variantData['size']) : null,
                    'material' => !empty($variantData['material']) ? trim($variantData['material']) : null,
                    'image' => $variantImage,
                    'is_active' => isset($variantData['status']) ? intval($variantData['status']) : 1
                ];

                $variantId = $this->productModel->createProductVariant($variantInsertData);

                if (!$variantId) {
                    throw new \Exception("Failed to create variant " . ($variantsProcessed + 1));
                }

                $variantsProcessed++;
            }

            // Check if at least one variant was created
            if ($variantsProcessed === 0) {
                throw new \Exception('No valid variants were created. Please check variant data.');
            }

            // Handle tags if provided
            if (!empty($_POST['tags'])) {
                $tags = array_map('trim', explode(',', $_POST['tags']));
                foreach ($tags as $tagName) {
                    if (!empty($tagName)) {
                        $tagId = $this->productModel->getOrCreateTag($tagName);
                        if ($tagId) {
                            $this->productModel->linkProductToTag($productId, $tagId);
                        }
                    }
                }
            }

            // Commit transaction
            $this->productModel->conn->commit();

            Session::set('success', "Product created successfully with {$variantsProcessed} variant(s)!");
            $this->redirect('/shop/products');

        } catch (\Exception $e) {
            // Rollback transaction
            $this->productModel->conn->rollback();
            
            // Clean up uploaded images
            if ($coverPicture && file_exists($coverPicture)) {
                unlink($coverPicture);
            }

            Session::set('error', 'Failed to create product: ' . $e->getMessage());
            $this->redirect('/shop/add-product');
        }
    }

    /**
     * Upload product image
     * @param array $file
     * @param int $shopId
     * @param string $type ('product' or 'variant')
     * @return array
     */
    private function uploadProductImage($file, $shopId, $type = 'product') {
        $uploadDir = 'uploads/products/' . $shopId . '/' . $type . 's/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, and WebP allowed.'];
        }

        // Validate file size (5MB max)
        if ($file['size'] > 5 * 1024 * 1024) {
            return ['success' => false, 'message' => 'File size exceeds 5MB limit.'];
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $type . '_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
        $filepath = $uploadDir . $filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => true, 'filename' => $filepath];
        }

        return ['success' => false, 'message' => 'Failed to move uploaded file.'];
    }

    /**
     * Generate URL-friendly slug
     * @param string $text
     * @return string
     */
    private function generateSlug($text) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text), '-'));
        $slug = $slug . '-' . time();
        return $slug;
    }

    /**
     * Generate SKU
     * @param int $productId
     * @param int $variantNumber
     * @return string
     */
    private function generateSKU($productId, $variantNumber = 1) {
        return 'PRD-' . str_pad($productId, 6, '0', STR_PAD_LEFT) . '-V' . str_pad($variantNumber, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Display orders list
     */
    public function orders() {
        if (!Session::has('user_id')) {
            Session::set('error', 'Please login to access this page');
            $this->redirect('/login');
        }

        $userId = Session::get('user_id');
        $shopData = $this->shopModel->getShopByUserId($userId);
        
        if (!$shopData) {
            Session::set('error', 'Shop not found');
            $this->redirect('/');
        }

        $orders = $this->shopModel->getShopOrders($shopData['shop_id']);

        $data = [
            'pageTitle' => 'My Orders',
            'currentPage' => 'orders',
            'shop' => $shopData,
            'orders' => $orders
        ];

        $this->view('shop/orders', $data, 'shop');
    }

    /**
     * Display cancellations
     */
    public function cancellations() {
        if (!Session::has('user_id')) {
            Session::set('error', 'Please login to access this page');
            $this->redirect('/login');
        }

        $userId = Session::get('user_id');
        $shopData = $this->shopModel->getShopByUserId($userId);
        
        if (!$shopData) {
            Session::set('error', 'Shop not found');
            $this->redirect('/');
        }

        $cancellations = $this->shopModel->getCancelledOrders($shopData['shop_id']);

        $data = [
            'pageTitle' => 'Cancellations',
            'currentPage' => 'cancellations',
            'shop' => $shopData,
            'cancellations' => $cancellations
        ];

        $this->view('shop/cancellations', $data, 'shop');
    }

    /**
     * Display addresses
     */
    public function addresses() {
        if (!Session::has('user_id')) {
            Session::set('error', 'Please login to access this page');
            $this->redirect('/login');
        }

        $userId = Session::get('user_id');
        $shopData = $this->shopModel->getShopByUserId($userId);
        
        if (!$shopData) {
            Session::set('error', 'Shop not found');
            $this->redirect('/');
        }

        // Get shop address
        $shopAddress = $this->shopModel->getShopAddress($shopData['shop_id']);

        $data = [
            'pageTitle' => 'Shop Addresses',
            'currentPage' => 'addresses',
            'shop' => $shopData,
            'shopAddress' => $shopAddress
        ];

        $this->view('shop/addresses', $data, 'shop');
    }
}