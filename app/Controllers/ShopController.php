<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Shop;
use App\Models\Product;
use App\Models\User;
use App\Helpers\AddProductHelper;
use App\Models\Services\TaggingService;

class ShopController extends Controller {

    protected $shopModel;
    protected $productModel;
    protected $userModel;
    protected $taggingService;

    public function __construct() {
        $this->shopModel = new Shop(); 
        $this->productModel = new Product();
        $this->userModel = new User();
        $this->taggingService = new TaggingService();
    }

    /**
     * Display the shop dashboard
     */
    public function dashboard() {
        if (!Session::has('user_id')) {
            Session::set('error', 'Please login to access the shop dashboard');
            $this->redirect('/login');
        }

        $userId = Session::get('user_id');
        $sellerStatus = $this->shopModel->getSellerStatus($userId);
        
        if (!$sellerStatus || $sellerStatus['is_approved'] != 1) {
            Session::set('error', 'You do not have access to the seller dashboard. Please register as a seller first.');
            $this->redirect('/seller/register');
        }

        $shopData = $this->shopModel->getShopByUserId($userId);
        
        if (!$shopData) {
            Session::set('error', 'Shop not found. Please contact support.');
            $this->redirect('/');
        }

        $stats = $this->shopModel->getDashboardStats($shopData['shop_id']);
        $recentOrders = $this->shopModel->getRecentOrders($shopData['shop_id'], 5);
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

        // Validate form data using helper
        $validation = AddProductHelper::validateProductData($_POST, $_FILES);
        
        if (!$validation['valid']) {
            Session::set('error', implode('<br>', $validation['errors']));
            $this->redirect('/shop/add-product');
        }

        // Handle cover image upload
        $coverUploadResult = AddProductHelper::uploadProductImage(
            $_FILES['cover_picture'], 
            $shopData['shop_id']
        );
        
        if (!$coverUploadResult['success']) {
            Session::set('error', 'Cover image upload failed: ' . $coverUploadResult['message']);
            $this->redirect('/shop/add-product');
        }

        // Prepare product data
        $productData = AddProductHelper::prepareProductData(
            $_POST, 
            $shopData['shop_id'], 
            $coverUploadResult['filename']
        );

        // Get database connection for transaction
        $conn = $this->shopModel->getConnection();
        
        // Start transaction
        $conn->begin_transaction();

        try {
            // Create product using shopModel (which has the createProduct method)
            $productId = $this->shopModel->createProduct($productData);

            if (!$productId) {
                throw new \Exception('Failed to create product');
            }

            // Link product to category using shopModel
            if (!$this->shopModel->linkProductToCategory($productId, $_POST['category_id'])) {
                throw new \Exception('Failed to link product to category');
            }

            // Process variant images
            $variantImages = AddProductHelper::processVariantImages($_FILES, $shopData['shop_id']);

            // Process variants
            $variantsProcessed = 0;
            $uploadedFiles = [$coverUploadResult['filename']];
            
            foreach ($_POST['variants'] as $variantIndex => $variantData) {
                // Skip invalid variants
                if (empty($variantData['price']) || empty($variantData['quantity'])) {
                    continue;
                }

                // Get variant image if uploaded
                $variantImage = isset($variantImages[$variantIndex]) && $variantImages[$variantIndex]['success']
                    ? $variantImages[$variantIndex]['filename']
                    : null;

                if ($variantImage) {
                    $uploadedFiles[] = $variantImage;
                }

                // Prepare variant data
                $variantInsertData = AddProductHelper::prepareVariantData(
                    $variantData,
                    $productId,
                    $variantsProcessed + 1,
                    $variantImage,
                    $this->productModel
                );

                $variantId = $this->productModel->createProductVariant($variantInsertData);

                if (!$variantId) {
                    throw new \Exception("Failed to create variant " . ($variantsProcessed + 1));
                }

                $variantsProcessed++;
            }

            if ($variantsProcessed === 0) {
                throw new \Exception('No valid variants were created. Please check variant data.');
            }

            // Process tags using shopModel
            if (!empty($_POST['tags'])) {
                AddProductHelper::processTags($_POST['tags'], $productId, $this->shopModel);
            }

            // Commit transaction
            $conn->commit();

            Session::set('success', "Product created successfully with {$variantsProcessed} variant(s)!");
            $this->redirect('/shop/products');

        } catch (\Exception $e) {
            // Rollback transaction
            $conn->rollback();
            
            // Clean up uploaded files
            AddProductHelper::cleanupFiles($uploadedFiles);

            Session::set('error', 'Failed to create product: ' . $e->getMessage());
            $this->redirect('/shop/add-product');
        }
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

        $shopAddress = $this->shopModel->getShopAddress($shopData['shop_id']);

        $data = [
            'pageTitle' => 'Shop Addresses',
            'currentPage' => 'addresses',
            'shop' => $shopData,
            'shopAddress' => $shopAddress
        ];

        $this->view('shop/addresses', $data, 'shop');
    }

        /**
     * AJAX endpoint to get predicted tags
     * This allows real-time tag suggestions in the frontend
     */
    public function predictTags() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            return;
        }

        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        $productName = $input['product_name'] ?? '';
        $description = $input['description'] ?? '';
        $shortDescription = $input['short_description'] ?? '';

        // Check if service is healthy
        if (!$this->taggingService->isServiceHealthy()) {
            echo json_encode([
                'success' => false,
                'error' => 'Tagging service is currently unavailable'
            ]);
            return;
        }

        // Get predictions
        $result = $this->taggingService->predictTags(
            $productName,
            $description,
            $shortDescription
        );

        echo json_encode($result);
    }
}