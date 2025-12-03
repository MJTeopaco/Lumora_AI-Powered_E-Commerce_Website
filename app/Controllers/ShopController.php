<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Shop;
use App\Models\Product;
use App\Models\User;
use App\Models\Transaction; // Added
use App\Helpers\AddProductHelper;
use App\Helpers\RedirectHelper;
use App\Helpers\PayMongoService; // Added

class ShopController extends Controller {

    protected $shopModel;
    protected $productModel;
    protected $userModel;
    protected $paymongoService; // Added
    protected $transactionModel; // Added

    public function __construct() {
        $this->shopModel = new Shop(); 
        $this->productModel = new Product();
        $this->userModel = new User();
        $this->paymongoService = new PayMongoService(); // Init
        $this->transactionModel = new Transaction(); // Init
    }

    public function dashboard() {
        if (!Session::has('user_id')) {
            Session::set('error', 'Please login to access the shop dashboard');
            RedirectHelper::redirect('/login');
        }

        $userId = Session::get('user_id');
        $sellerStatus = $this->shopModel->getSellerStatus($userId);
        
        if (!$sellerStatus || $sellerStatus['is_approved'] != 1) {
            Session::set('error', 'You do not have access to the seller dashboard. Please register as a seller first.');
            RedirectHelper::redirect('/seller/register');
        }

        $shopData = $this->shopModel->getShopByUserId($userId);
        
        if (!$shopData) {
            Session::set('error', 'Shop not found. Please contact support.');
            RedirectHelper::redirect('/');
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

    public function products() {
        if (!Session::has('user_id')) {
            Session::set('error', 'Please login to access this page');
            RedirectHelper::redirect('/login');
        }

        $userId = Session::get('user_id');
        $shopData = $this->shopModel->getShopByUserId($userId);
        
        if (!$shopData) {
            Session::set('error', 'Shop not found');
            RedirectHelper::redirect('/');
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

    public function addProduct() {
        if (!Session::has('user_id')) {
            Session::set('error', 'Please login to access this page');
            RedirectHelper::redirect('/login');
        }

        $userId = Session::get('user_id');
        $shopData = $this->shopModel->getShopByUserId($userId);
        
        if (!$shopData) {
            Session::set('error', 'Shop not found');
            RedirectHelper::redirect('/');
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

    public function storeProduct() {
        $this->verifyCsrfToken();

        if (!Session::has('user_id')) {
            Session::set('error', 'Please login to access this page');
            RedirectHelper::redirect('/login');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Session::set('error', 'Invalid request method');
            RedirectHelper::redirect('/shop/add-product');
        }

        $userId = Session::get('user_id');
        $shopData = $this->shopModel->getShopByUserId($userId);
        
        if (!$shopData) {
            Session::set('error', 'Shop not found');
            RedirectHelper::redirect('/');
        }

        $validation = AddProductHelper::validateProductData($_POST, $_FILES);
        
        if (!$validation['valid']) {
            Session::set('error', implode('<br>', $validation['errors']));
            RedirectHelper::redirect('/shop/add-product');
        }

        $coverUploadResult = AddProductHelper::uploadProductImage(
            $_FILES['cover_picture'], 
            $shopData['shop_id']
        );
        
        if (!$coverUploadResult['success']) {
            Session::set('error', 'Cover image upload failed: ' . $coverUploadResult['message']);
            RedirectHelper::redirect('/shop/add-product');
        }

        $productData = AddProductHelper::prepareProductData(
            $_POST, 
            $shopData['shop_id'], 
            $coverUploadResult['filename']
        );

        $conn = $this->shopModel->getConnection();
        $conn->begin_transaction();

        try {
            $productId = $this->shopModel->createProduct($productData);

            if (!$productId) {
                throw new \Exception('Failed to create product');
            }

            if (!$this->shopModel->linkProductToCategory($productId, $_POST['category_id'])) {
                throw new \Exception('Failed to link product to category');
            }

            $variantImages = AddProductHelper::processVariantImages($_FILES, $shopData['shop_id']);

            $variantsProcessed = 0;
            $uploadedFiles = [$coverUploadResult['filename']];
            
            foreach ($_POST['variants'] as $variantIndex => $variantData) {
                if (empty($variantData['price']) || empty($variantData['quantity'])) {
                    continue;
                }

                $variantImage = isset($variantImages[$variantIndex]) && $variantImages[$variantIndex]['success']
                    ? $variantImages[$variantIndex]['filename']
                    : null;

                if ($variantImage) {
                    $uploadedFiles[] = $variantImage;
                }

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

            if (!empty($_POST['tags'])) {
                AddProductHelper::processTags($_POST['tags'], $productId, $this->shopModel);
            }

            $conn->commit();

            Session::set('success', "Product created successfully with {$variantsProcessed} variant(s)!");
            RedirectHelper::redirect('/shop/products');

        } catch (\Exception $e) {
            $conn->rollback();
            AddProductHelper::cleanupFiles($uploadedFiles);

            Session::set('error', 'Failed to create product: ' . $e->getMessage());
            RedirectHelper::redirect('/shop/add-product');
        }
    }

    /**
     * Display all orders for the shop
     */
    public function orders() {
        if (!Session::has('user_id')) {
            Session::set('error', 'Please login to access this page');
            RedirectHelper::redirect('/login');
        }

        $userId = Session::get('user_id');
        $shopData = $this->shopModel->getShopByUserId($userId);
        
        if (!$shopData) {
            Session::set('error', 'Shop not found');
            RedirectHelper::redirect('/');
        }

        // Get filter parameters
        $currentFilter = $_GET['status'] ?? 'all';
        $searchTerm = $_GET['search'] ?? '';
        
        // Get orders with filters
        $orders = $this->shopModel->getShopOrders(
            $shopData['shop_id'], 
            $currentFilter, 
            $searchTerm
        );

        // Get order statistics
        $orderStats = $this->shopModel->getOrderStatsByStatus($shopData['shop_id']);
        
        // Calculate stats for display
        $stats = [
            'total_orders' => $this->shopModel->getTotalOrderCount($shopData['shop_id']),
            'processing' => $orderStats['PROCESSING']['count'] ?? 0,
            'ready_to_ship' => $orderStats['READY_TO_SHIP']['count'] ?? 0,
            'shipped' => $orderStats['SHIPPED']['count'] ?? 0,
            'delivered' => $orderStats['DELIVERED']['count'] ?? 0
        ];

        $data = [
            'pageTitle' => 'Manage Orders',
            'currentPage' => 'orders',
            'shop' => $shopData,
            'orders' => $orders,
            'stats' => $stats,
            'currentFilter' => $currentFilter,
            'searchTerm' => $searchTerm
        ];

        $this->view('shop/orders', $data, 'shop');
    }

    /**
     * Get order details via AJAX
     */
    public function getOrderDetails() {
        header('Content-Type: application/json');

        if (!Session::has('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');
        $shopData = $this->shopModel->getShopByUserId($userId);
        
        if (!$shopData) {
            echo json_encode(['success' => false, 'message' => 'Shop not found']);
            exit;
        }

        $orderId = $_GET['order_id'] ?? null;
        
        if (!$orderId) {
            echo json_encode(['success' => false, 'message' => 'Order ID required']);
            exit;
        }

        $orderDetails = $this->shopModel->getOrderDetails($orderId, $shopData['shop_id']);
        
        if (!$orderDetails) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            exit;
        }

        echo json_encode(['success' => true, 'data' => $orderDetails]);
        exit;
    }

    /**
     * Update order status
     * UPDATED: Handles Refund Logic
     */
    public function updateOrderStatus() {
        header('Content-Type: application/json');
        
        // Manual CSRF Check for JSON requests
        $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        
        if (!$csrfToken || !hash_equals(Session::get('csrf_token', ''), $csrfToken)) {
            echo json_encode(['success' => false, 'message' => 'CSRF validation failed']);
            exit;
        }

        if (!Session::has('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        $userId = Session::get('user_id');
        $shopData = $this->shopModel->getShopByUserId($userId);
        
        if (!$shopData) {
            echo json_encode(['success' => false, 'message' => 'Shop not found']);
            exit;
        }

        // Get POST data
        $input = json_decode(file_get_contents('php://input'), true);
        $orderId = $input['order_id'] ?? null;
        $newStatus = $input['status'] ?? null;

        if (!$orderId || !$newStatus) {
            echo json_encode(['success' => false, 'message' => 'Order ID and status required']);
            exit;
        }

        // Validate status transition
        $validTransitions = [
            'PENDING_PAYMENT' => ['PROCESSING', 'CANCELLED'],
            'PROCESSING' => ['SHIPPED', 'READY_TO_SHIP', 'CANCELLED'],
            'READY_TO_SHIP' => ['SHIPPED', 'CANCELLED'],
            'SHIPPED' => ['DELIVERED'],
            'REFUND_REQUESTED' => ['CANCELLED', 'PROCESSING'] // Approve (CANCELLED) or Reject (PROCESSING)
        ];

        // Get current order
        $currentOrder = $this->shopModel->getOrderDetails($orderId, $shopData['shop_id']);
        
        if (!$currentOrder) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            exit;
        }

        $currentStatus = $currentOrder['order_status'];

        // Check if transition is valid
        if (!isset($validTransitions[$currentStatus]) || 
            !in_array($newStatus, $validTransitions[$currentStatus])) {
            echo json_encode([
                'success' => false, 
                'message' => "Cannot change status from {$currentStatus} to {$newStatus}"
            ]);
            exit;
        }

        // --- REFUND LOGIC ---
        // If transitioning TO Cancelled FROM Refund Requested, trigger PayMongo
        if ($currentStatus === 'REFUND_REQUESTED' && $newStatus === 'CANCELLED') {
            try {
                $transaction = $this->transactionModel->getSuccessfulTransaction($orderId);
                
                if ($transaction && !empty($transaction['transaction_id'])) {
                    $paymentId = $this->paymongoService->getPaymentIdFromSession($transaction['transaction_id']);
                    if ($paymentId) {
                        $refundResult = $this->paymongoService->createRefund(
                            $paymentId, 
                            $currentOrder['total_amount'], 
                            'requested_by_customer'
                        );
                        
                        if (isset($refundResult['errors'])) {
                            throw new \Exception($refundResult['errors'][0]['detail'] ?? 'Refund API failed');
                        }
                    }
                }
            } catch (\Exception $e) {
                error_log("Refund Failed: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Refund failed: ' . $e->getMessage()]);
                exit;
            }
        }

        // Update status
        $success = $this->shopModel->updateOrderStatus($orderId, $shopData['shop_id'], $newStatus);

        if ($success) {
            echo json_encode([
                'success' => true, 
                'message' => 'Order status updated successfully',
                'new_status' => $newStatus
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update order status']);
        }
        exit;
    }

    public function cancellations() {
        if (!Session::has('user_id')) {
            Session::set('error', 'Please login to access this page');
            RedirectHelper::redirect('/login');
        }

        $userId = Session::get('user_id');
        $shopData = $this->shopModel->getShopByUserId($userId);
        
        if (!$shopData) {
            Session::set('error', 'Shop not found');
            RedirectHelper::redirect('/');
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

    public function addresses() {
        if (!Session::has('user_id')) {
            Session::set('error', 'Please login to access this page');
            RedirectHelper::redirect('/login');
        }

        $userId = Session::get('user_id');
        $shopData = $this->shopModel->getShopByUserId($userId);
        
        if (!$shopData) {
            Session::set('error', 'Shop not found');
            RedirectHelper::redirect('/');
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
}