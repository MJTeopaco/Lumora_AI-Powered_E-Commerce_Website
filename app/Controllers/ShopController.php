<?php
// app/Controllers/ShopController.php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Shop;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Notification;
use App\Helpers\AddProductHelper;
use App\Helpers\RedirectHelper;
use App\Helpers\EmailService;
use App\Models\Services\TaggingService;

class ShopController extends Controller {

    protected $shopModel;
    protected $productModel;
    protected $userModel;
    protected $orderModel;
    protected $orderItemModel;
    protected $notificationModel;
    protected $emailService;
    protected $taggingService;

    public function __construct() {
        $this->shopModel = new Shop(); 
        $this->productModel = new Product();
        $this->userModel = new User();
        $this->orderModel = new Order();
        $this->orderItemModel = new OrderItem();
        $this->notificationModel = new Notification();
        $this->emailService = new EmailService();
        $this->taggingService = new TaggingService();
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

        $currentFilter = $_GET['status'] ?? 'all';
        $searchTerm = $_GET['search'] ?? '';
        
        $orders = $this->shopModel->getShopOrders(
            $shopData['shop_id'], 
            $currentFilter, 
            $searchTerm
        );

        $orderStats = $this->shopModel->getOrderStatsByStatus($shopData['shop_id']);
        
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

    public function updateOrderStatus() {
        header('Content-Type: application/json');
        
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

        $input = json_decode(file_get_contents('php://input'), true);
        $orderId = $input['order_id'] ?? null;
        $newStatus = $input['status'] ?? null;

        if (!$orderId || !$newStatus) {
            echo json_encode(['success' => false, 'message' => 'Order ID and status required']);
            exit;
        }

        $validTransitions = [
            'PENDING_PAYMENT' => ['PROCESSING', 'CANCELLED'],
            'PROCESSING' => ['SHIPPED', 'READY_TO_SHIP', 'CANCELLED'],
            'READY_TO_SHIP' => ['SHIPPED'],
            'SHIPPED' => ['DELIVERED']
        ];

        $currentOrder = $this->shopModel->getOrderDetails($orderId, $shopData['shop_id']);
        
        if (!$currentOrder) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            exit;
        }

        $currentStatus = $currentOrder['order_status'];

        if (!isset($validTransitions[$currentStatus]) || 
            !in_array($newStatus, $validTransitions[$currentStatus])) {
            echo json_encode([
                'success' => false, 
                'message' => "Cannot change status from {$currentStatus} to {$newStatus}"
            ]);
            exit;
        }

        $success = $this->shopModel->updateOrderStatus($orderId, $shopData['shop_id'], $newStatus);

        if ($success) {
            if ($newStatus === 'CANCELLED' && $currentStatus === 'PROCESSING') {
                $items = $this->orderItemModel->getOrderItems($orderId);
                foreach ($items as $item) {
                    $this->productModel->increaseStock($item['variant_id'], $item['quantity']);
                }
            }
            
            $fullOrder = $this->orderModel->getOrderById($orderId);
            
            $this->notificationModel->notifyOrderStatusChange(
                $fullOrder['user_id'],
                $orderId,
                $newStatus
            );

            try {
                if (in_array($newStatus, ['SHIPPED', 'READY_TO_SHIP', 'DELIVERED', 'CANCELLED'])) {
                    $this->emailService->sendOrderStatusUpdate([
                        'order' => $fullOrder,
                        'status' => $newStatus,
                        'customerEmail' => $fullOrder['email'],
                        'customerName' => $fullOrder['full_name'] ?? $fullOrder['username']
                    ]);
                }
            } catch (\Exception $e) {
                error_log("Failed to send order status email: " . $e->getMessage());
            }

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

    /**
     * Shop Reviews Management Page
     * Updated to use the correct 'shop' layout
     */
    public function reviews() {
        // Check authentication and seller status
        if (!Session::has('user_id')) {
            Session::set('error', 'Please login to access this page');
            RedirectHelper::redirect('/login');
        }

        $userId = Session::get('user_id');
        $shopData = $this->shopModel->getShopByUserId($userId);
        
        if (!$shopData) {
            Session::set('error', 'Shop not found. Please contact support.');
            RedirectHelper::redirect('/');
        }
        
        $shopId = $shopData['shop_id'];
        
        // Get review model
        $reviewModel = new \App\Models\ProductReview();
        
        // Get all reviews for this shop's products
        $reviews = $reviewModel->getShopProductReviews($shopId, 50, 0);
        
        // Calculate statistics
        $stats = [
            'total_reviews' => count($reviews),
            'average_rating' => 0,
            'responded' => 0,
            'pending_response' => 0
        ];
        
        $totalRating = 0;
        foreach ($reviews as $review) {
            $totalRating += $review['rating'];
            if ($review['response_text']) {
                $stats['responded']++;
            } else {
                $stats['pending_response']++;
            }
        }
        
        if ($stats['total_reviews'] > 0) {
            $stats['average_rating'] = $totalRating / $stats['total_reviews'];
        }
        
        $data = [
            'pageTitle' => 'Manage Reviews',
            'currentPage' => 'reviews',
            'shop' => $shopData,
            'reviews' => $reviews,
            'stats' => $stats
        ];
        
        // Explicitly use the 'shop' layout to avoid the default header
        $this->view('shop/reviews', $data, 'shop');
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