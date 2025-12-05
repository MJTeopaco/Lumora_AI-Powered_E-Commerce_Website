<?php
// app/Controllers/CheckoutController.php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\View;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use App\Models\Address;
use App\Models\UserProfile;
use App\Models\Notification; // Added Notification Model
use App\Helpers\PayMongoService;
use App\Helpers\RedirectHelper;

class CheckoutController extends Controller {
    
    private $cartModel;
    private $orderModel;
    private $orderItemModel;
    private $transactionModel;
    private $addressModel;
    private $paymongoService;
    
    public function __construct() {
        // Require authentication
        if (!Session::has('user_id')) {
            RedirectHelper::redirect('/login?redirect=/checkout');
        }
        
        $this->cartModel = new Cart();
        $this->orderModel = new Order();
        $this->orderItemModel = new OrderItem();
        $this->transactionModel = new Transaction();
        $this->addressModel = new Address();
        $this->paymongoService = new PayMongoService();
    }
    
    /**
     * Display checkout page
     */
    public function index() {
        $userId = Session::get('user_id');
        
        // Get and validate cart
        $cartItems = $this->cartModel->getUserCart($userId);
        
        if (empty($cartItems)) {
            RedirectHelper::redirect('/cart?status=error&message=' . urlencode('Your cart is empty'));
        }
        
        // Validate stock availability
        $stockIssues = $this->cartModel->validateCartStock($userId);
        if (!empty($stockIssues)) {
            $this->cartModel->autoAdjustQuantities($userId);
            RedirectHelper::redirect('/cart?status=warning&message=' . urlencode('Some items were adjusted due to stock availability'));
        }
        
        // Get user's saved addresses
        $addresses = $this->addressModel->getAddressesByUserId_User($userId);
        $defaultAddress = $this->addressModel->getDefaultAddress($userId);
        
        // Calculate totals
        $subtotal = 0;
        $processedItems = [];
        
        foreach ($cartItems as $item) {
            $itemTotal = $item['price'] * $item['quantity'];
            $subtotal += $itemTotal;
            
            $processedItems[] = [
                'variant_id' => $item['variant_id'],
                'product_name' => $item['product_name'],
                'variant_name' => $item['variant_name'],
                'color' => $item['color'],
                'size' => $item['size'],
                'material' => $item['material'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'product_picture' => $item['product_picture'],
                'cover_picture' => $item['cover_picture'],
                'item_total' => $itemTotal
            ];
        }
        
        // Shipping calculation
        $shippingFee = 50.00; // Flat rate
        $total = $subtotal + $shippingFee;
        
        // Fetch User Profile for Header
        $userProfileModel = new UserProfile();
        $userProfile = $userProfileModel->getByUserId($userId);

        $data = [
            'cartItems' => $processedItems,
            'addresses' => $addresses,
            'defaultAddress' => $defaultAddress,
            'subtotal' => $subtotal,
            'shippingFee' => $shippingFee,
            'total' => $total,
            'cartCount' => count($processedItems),
            'isLoggedIn' => true,
            'username' => Session::get('username'),
            'userProfile' => $userProfile,
            'csrfToken' => Session::get('csrf_token')
        ];
        
        View::make('checkout/index', $data)
            ->setLayout('default')
            ->render();
    }
    
    /**
     * Process checkout and create order
     */
    public function process() {
        // Validate CSRF token
        if (!$this->validateCsrfToken()) {
            RedirectHelper::redirect('/checkout?status=error&message=' . urlencode('Invalid security token'));
        }
        
        $userId = Session::get('user_id');
        
        // Get form data
        $addressId = (int)($_POST['address_id'] ?? 0);
        
        // Validate address
        if (!$addressId) {
            RedirectHelper::redirect('/checkout?status=error&message=' . urlencode('Please select a shipping address'));
        }
        
        $address = $this->addressModel->getAddressById($addressId, $userId);
        if (!$address) {
            RedirectHelper::redirect('/checkout?status=error&message=' . urlencode('Invalid shipping address'));
        }
        
        // Get cart items
        $cartItems = $this->cartModel->getUserCart($userId);
        
        if (empty($cartItems)) {
            RedirectHelper::redirect('/cart?status=error&message=' . urlencode('Your cart is empty'));
        }
        
        // Final stock validation
        $stockIssues = $this->cartModel->validateCartStock($userId);
        if (!empty($stockIssues)) {
            RedirectHelper::redirect('/cart?status=error&message=' . urlencode('Some items are no longer available'));
        }
        
        // Calculate totals
        $subtotal = $this->cartModel->getCartSubtotal($userId);
        $shippingFee = 50.00;
        $total = $subtotal + $shippingFee;
        
        // Get shop_id from first cart item
        $shopId = $cartItems[0]['shop_id'] ?? null;
        
        // Create order with PENDING_PAYMENT status
        $orderData = [
            'user_id' => $userId,
            'shop_id' => $shopId,
            'shipping_address_id' => $addressId,
            'order_status' => 'PENDING_PAYMENT',
            'total_amount' => $total,
            'shipping_fee' => $shippingFee
        ];
        
        $orderId = $this->orderModel->createOrder($orderData);
        
        if (!$orderId) {
            RedirectHelper::redirect('/checkout?status=error&message=' . urlencode('Failed to create order'));
        }
        
        // Add order items
        $itemsAdded = $this->orderItemModel->addOrderItemsFromCart($orderId, $cartItems);
        
        if ($itemsAdded === 0) {
            RedirectHelper::redirect('/checkout?status=error&message=' . urlencode('Failed to add items to order'));
        }
        
        // Create PayMongo checkout session
        $checkoutSessionData = $this->createPayMongoSession($orderId, $cartItems, $total);
        
        if (!$checkoutSessionData) {
            $this->orderModel->updateOrderStatus($orderId, 'CANCELLED');
            RedirectHelper::redirect('/checkout?status=error&message=' . urlencode('Failed to initialize payment'));
        }
        
        // Create transaction record
        $transactionData = [
            'order_id' => $orderId,
            'payment_method' => 'E_WALLET',
            'payment_gateway' => 'PayMongo',
            'transaction_id' => $checkoutSessionData['data']['id'],
            'amount_paid' => $total,
            'status' => 'PENDING'
        ];
        
        $this->transactionModel->createTransaction($transactionData);
        
        // --- CHANGED: Removed clearCart from here so it doesn't clear on back button ---
        
        // Redirect to PayMongo checkout
        $checkoutUrl = $checkoutSessionData['data']['attributes']['checkout_url'];
        header('Location: ' . $checkoutUrl);
        exit;
    }
    
    /**
     * Payment success page
     */
    public function success() {
        $userId = Session::get('user_id');
        $orderId = $_GET['order_id'] ?? null;
        
        if (!$orderId) {
            RedirectHelper::redirect('/orders');
        }
        
        $order = $this->orderModel->getOrderById($orderId, $userId);
        
        if (!$order) {
            RedirectHelper::redirect('/orders');
        }

        // --- CHANGED: Clear cart HERE (after successful payment/return) ---
        $this->cartModel->clearCart($userId);
        $this->cartModel->markCartCheckedOut($userId);
        
        // --- ENHANCED NOTIFICATION SYSTEM INTEGRATION ---
        $notificationModel = new Notification();
        // 1. Notify Payment Success
        $notificationModel->notifyPaymentSuccess(
            $userId,
            $orderId,
            $order['total_amount']
        );
        // 2. Notify Order Placed
        $notificationModel->notifyOrderPlaced(
            $userId,
            $orderId,
            $order['total_amount']
        );
        // ------------------------------------------------
        
        $orderItems = $this->orderItemModel->getOrderItems($orderId);
        $transaction = $this->transactionModel->getSuccessfulTransaction($orderId);
        
        $userProfileModel = new UserProfile();
        $userProfile = $userProfileModel->getByUserId($userId);
        
        $data = [
            'order' => $order,
            'orderItems' => $orderItems,
            'transaction' => $transaction,
            'isLoggedIn' => true,
            'username' => Session::get('username'),
            'userProfile' => $userProfile,
            'cartCount' => $this->cartModel->getCartCount($userId)
        ];
        
        View::make('checkout/success', $data)
            ->setLayout('default')
            ->render();
    }
    
    /**
     * Payment failed page
     */
    public function failed() {
        $userId = Session::get('user_id');
        $orderId = $_GET['order_id'] ?? null;
        
        $userProfileModel = new UserProfile();
        $userProfile = $userProfileModel->getByUserId($userId);
        
        $data = [
            'orderId' => $orderId,
            'isLoggedIn' => true,
            'username' => Session::get('username'),
            'userProfile' => $userProfile,
            'cartCount' => $this->cartModel->getCartCount($userId)
        ];
        
        View::make('checkout/failed', $data)
            ->setLayout('default')
            ->render();
    }
    
    /**
     * Create PayMongo checkout session
     */
    private function createPayMongoSession($orderId, $cartItems, $total) {
        $lineItems = $this->paymongoService->buildLineItems($cartItems);
        
        $lineItems[] = [
            'currency' => 'PHP',
            'amount' => $this->paymongoService->formatAmountToCents(50.00),
            'name' => 'Shipping Fee',
            'quantity' => 1
        ];
        
        $sessionData = [
            'description' => 'Lumora Order #' . $orderId,
            'line_items' => $lineItems,
            'payment_method_types' => ['gcash', 'paymaya', 'card'],
            // Use base_url() helper here
            'success_url' => base_url('/checkout/success?order_id=' . $orderId),
            'cancel_url' => base_url('/checkout/failed?order_id=' . $orderId),
            'metadata' => [
                'order_id' => $orderId,
                'user_id' => Session::get('user_id')
            ]
        ];
        
        return $this->paymongoService->createCheckoutSession($sessionData);
    }
    
    /**
     * Helper: Validate CSRF token
     */
    private function validateCsrfToken() {
        $token = $_POST['csrf_token'] ?? '';
        $sessionToken = Session::get('csrf_token');
        
        if (empty($sessionToken) || empty($token)) {
            return false;
        }
        
        return hash_equals($sessionToken, $token);
    }
}