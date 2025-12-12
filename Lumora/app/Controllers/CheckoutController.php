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
use App\Models\Notification;
use App\Models\ShopEarnings; // Added this import
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
    
    public function index() {
        $userId = Session::get('user_id');
        $cartItems = $this->cartModel->getUserCart($userId);
        
        if (empty($cartItems)) {
            RedirectHelper::redirect('/cart?status=error&message=' . urlencode('Your cart is empty'));
        }
        
        $stockIssues = $this->cartModel->validateCartStock($userId);
        if (!empty($stockIssues)) {
            $this->cartModel->autoAdjustQuantities($userId);
            RedirectHelper::redirect('/cart?status=warning&message=' . urlencode('Some items were adjusted due to stock availability'));
        }
        
        $addresses = $this->addressModel->getAddressesByUserId_User($userId);
        $defaultAddress = $this->addressModel->getDefaultAddress($userId);
        
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
        
        $shippingFee = 50.00;
        $total = $subtotal + $shippingFee;
        
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
    
    public function process() {
        if (!$this->validateCsrfToken()) {
            RedirectHelper::redirect('/checkout?status=error&message=' . urlencode('Invalid security token'));
        }
        
        $userId = Session::get('user_id');
        $addressId = (int)($_POST['address_id'] ?? 0);
        
        if (!$addressId) {
            RedirectHelper::redirect('/checkout?status=error&message=' . urlencode('Please select a shipping address'));
        }
        
        $address = $this->addressModel->getAddressById($addressId, $userId);
        if (!$address) {
            RedirectHelper::redirect('/checkout?status=error&message=' . urlencode('Invalid shipping address'));
        }
        
        $cartItems = $this->cartModel->getUserCart($userId);
        
        if (empty($cartItems)) {
            RedirectHelper::redirect('/cart?status=error&message=' . urlencode('Your cart is empty'));
        }
        
        $stockIssues = $this->cartModel->validateCartStock($userId);
        if (!empty($stockIssues)) {
            RedirectHelper::redirect('/cart?status=error&message=' . urlencode('Some items are no longer available'));
        }
        
        $subtotal = $this->cartModel->getCartSubtotal($userId);
        $shippingFee = 50.00;
        $total = $subtotal + $shippingFee;
        $shopId = $cartItems[0]['shop_id'] ?? null;
        
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
        
        $itemsAdded = $this->orderItemModel->addOrderItemsFromCart($orderId, $cartItems);
        
        if ($itemsAdded === 0) {
            RedirectHelper::redirect('/checkout?status=error&message=' . urlencode('Failed to add items to order'));
        }
        
        $checkoutSessionData = $this->createPayMongoSession($orderId, $cartItems, $total);
        
        if (!$checkoutSessionData) {
            $this->orderModel->updateOrderStatus($orderId, 'CANCELLED');
            RedirectHelper::redirect('/checkout?status=error&message=' . urlencode('Failed to initialize payment'));
        }
        
        $transactionData = [
            'order_id' => $orderId,
            'payment_method' => 'E_WALLET',
            'payment_gateway' => 'PayMongo',
            'transaction_id' => $checkoutSessionData['data']['id'], // This is the Session ID
            'amount_paid' => $total,
            'status' => 'PENDING'
        ];
        
        $this->transactionModel->createTransaction($transactionData);
        
        $checkoutUrl = $checkoutSessionData['data']['attributes']['checkout_url'];
        header('Location: ' . $checkoutUrl);
        exit;
    }
    
    /**
     * MODIFIED: Manual Status Check for InfinityFree
     */
    public function success() {
        $userId = Session::get('user_id');
        $orderId = $_GET['order_id'] ?? null;
        
        if (!$orderId) RedirectHelper::redirect('/orders');
        
        $order = $this->orderModel->getOrderById($orderId, $userId);
        if (!$order) RedirectHelper::redirect('/orders');

        // --- MANUAL CHECK LOGIC START ---
        // 1. We need to find the PayMongo Session ID associated with this order.
        // NOTE: You must ensure your Transaction Model can fetch pending transactions.
        // If getSuccessfulTransaction returns nothing, we manually look for the PENDING one.
        
        $transaction = $this->transactionModel->getSuccessfulTransaction($orderId);
        
        // If not already marked successful in DB, let's check with PayMongo
        if (!$transaction && $order['order_status'] === 'PENDING_PAYMENT') {
            
            // Try to find the pending transaction using a custom method (See Step 3 below)
            // If you haven't added getTransactionByOrderId yet, this next line is crucial:
            if (method_exists($this->transactionModel, 'getTransactionByOrderId')) {
                $pendingTx = $this->transactionModel->getTransactionByOrderId($orderId);
            } else {
                // FALLBACK: If you didn't update Transaction.php, we can't check.
                // You MUST add the method in Step 3 for this to work.
                $pendingTx = null; 
            }

            if ($pendingTx) {
                $sessionId = $pendingTx['transaction_id']; // The PayMongo Session ID
                
                // 2. Call PayMongo API to check status
                $sessionData = $this->paymongoService->getCheckoutSession($sessionId);
                $paymentStatus = $sessionData['data']['attributes']['payment_status'] ?? 'unpaid';

                if ($paymentStatus === 'paid') {
                    // 3. It is paid! Run all the updates here immediately.
                    
                    // A. Update Transaction Status
                    $this->transactionModel->updateTransactionStatus($sessionId, 'COMPLETED');
                    
                    // B. Update Order Status
                    $this->orderModel->updateOrderStatus($orderId, 'PROCESSING');
                    $this->orderItemModel->updateAllItemsStatus($orderId, 'PROCESSING');
                    
                    // C. Reduce Stock
                    $orderItems = $this->orderItemModel->getOrderItems($orderId);
                    $productModel = new \App\Models\Product();
                    foreach ($orderItems as $item) {
                        $productModel->updateStock($item['variant_id'], $item['quantity']);
                    }

                    // D. Record Earnings
                    $earningsModel = new ShopEarnings();
                    if ($order['shop_id']) {
                        $earningsModel->calculateAndRecord(
                            $order['order_id'],
                            $order['shop_id'],
                            $order['total_amount'],
                            $order['shipping_fee']
                        );
                    }

                    // E. Clear Cart
                    $this->cartModel->clearCart($userId);
                    $this->cartModel->markCartCheckedOut($userId);

                    // F. Notifications
                    $notificationModel = new Notification();
                    $notificationModel->notifyPaymentSuccess($userId, $orderId, $order['total_amount']);
                    $notificationModel->notifyOrderPlaced($userId, $orderId, $order['total_amount']);
                    
                    // Refresh data for the view
                    $order['order_status'] = 'PROCESSING';
                    $transaction = $pendingTx; // Show the now-valid transaction
                }
            }
        }
        // --- MANUAL CHECK LOGIC END ---
        
        // Fetch data for view
        $orderItems = $this->orderItemModel->getOrderItems($orderId);
        // Refresh transaction fetch
        if (!$transaction) {
            $transaction = $this->transactionModel->getSuccessfulTransaction($orderId);
        }
        
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
            'success_url' => base_url('/checkout/success?order_id=' . $orderId),
            'cancel_url' => base_url('/checkout/failed?order_id=' . $orderId),
            'metadata' => [
                'order_id' => $orderId,
                'user_id' => Session::get('user_id')
            ]
        ];
        
        return $this->paymongoService->createCheckoutSession($sessionData);
    }
    
    private function validateCsrfToken() {
        $token = $_POST['csrf_token'] ?? '';
        $sessionToken = Session::get('csrf_token');
        
        if (empty($sessionToken) || empty($token)) {
            return false;
        }
        
        return hash_equals($sessionToken, $token);
    }
}