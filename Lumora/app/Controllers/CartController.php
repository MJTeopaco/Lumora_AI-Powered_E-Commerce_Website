<?php
// app/Controllers/CartController.php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\View;
use App\Models\Cart;
use App\Models\Product;
use App\Helpers\RedirectHelper;
use App\Models\UserProfile;

class CartController extends Controller {
    
    private $cartModel;
    private $productModel;
    
    public function __construct() {
        // Require authentication
        if (!Session::has('user_id')) {
            // For AJAX requests, return JSON
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Please login to use the cart',
                    'redirect' => '/login'
                ]);
                exit;
            }
            RedirectHelper::redirect('/login');
        }
        
        $this->cartModel = new Cart();
        $this->productModel = new Product();
    }
    
    /**
     * Display the shopping cart page
     */
    public function index() {
        $userId = Session::get('user_id');
        
        // Clean up cart - remove unavailable items
        $this->cartModel->cleanupCart($userId);
        
        // Auto-adjust quantities if stock changed
        $adjusted = $this->cartModel->autoAdjustQuantities($userId);
        
        // Get cart items with full details
        $cartItems = $this->cartModel->getUserCart($userId);
        
        // Calculate totals
        $subtotal = 0;
        $processedItems = [];
        
        foreach ($cartItems as $item) {
            $itemTotal = $item['price'] * $item['quantity'];
            $subtotal += $itemTotal;
            
            $processedItems[] = [
                'cart_id' => $item['cart_id'],
                'variant_id' => $item['variant_id'],
                'product_id' => $item['product_id'],
                'product_name' => $item['product_name'],
                'variant_name' => $item['variant_name'],
                'color' => $item['color'],
                'size' => $item['size'],
                'material' => $item['material'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'max_quantity' => $item['max_quantity'],
                'product_picture' => $item['product_picture'],
                'cover_picture' => $item['cover_picture'],
                'slug' => $item['slug'],
                'item_total' => $itemTotal,
                'is_available' => $item['max_quantity'] > 0,
                'stock_changed' => false 
            ];
        }
        
        // FIXED: Shipping calculation to match JS (Free over 5000)
        $shippingFee = 0;
        if ($subtotal > 0) {
            $shippingFee = ($subtotal >= 5000) ? 0 : 50.00;
        }
        
        $total = $subtotal + $shippingFee;
        
        // Fetch User Profile for Header
        $userProfileModel = new UserProfile();
        $userProfile = $userProfileModel->getByUserId($userId);

        $data = [
            'cartItems' => $processedItems,
            'subtotal' => $subtotal,
            'shippingFee' => $shippingFee,
            'total' => $total,
            'cartCount' => count($processedItems),
            'isLoggedIn' => true,
            'username' => Session::get('username'),
            'userProfile' => $userProfile,
            'adjustedItems' => $adjusted['adjusted_count'] ?? 0
        ];
        
        // Show message if items were adjusted
        if (isset($adjusted['adjusted_count']) && $adjusted['adjusted_count'] > 0) {
            $data['statusMessage'] = 'Some item quantities were adjusted due to stock availability';
            $data['statusType'] = 'warning';
        }
        
        View::make('cart/index', $data)
            ->setLayout('default')
            ->render();
    }
    
    /**
     * Add item to cart (AJAX)
     */
    public function add() {
        header('Content-Type: application/json');
        
        // Validate CSRF token
        if (!$this->validateCsrfToken()) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid security token'
            ]);
            exit;
        }
        
        $userId = Session::get('user_id');
        $variantId = $_POST['variant_id'] ?? null;
        $quantity = (int)($_POST['quantity'] ?? 1);
        
        if (!$variantId || $quantity < 1) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid product or quantity'
            ]);
            exit;
        }
        
        // Check if variant exists and has stock
        $variant = $this->getVariantDetails($variantId);
        
        if (!$variant) {
            echo json_encode([
                'success' => false,
                'message' => 'Product not found'
            ]);
            exit;
        }
        
        // Check current quantity in cart
        $existingItem = $this->cartModel->getCartItem($userId, $variantId);
        $currentQuantity = $existingItem ? $existingItem['quantity'] : 0;
        $newTotalQuantity = $currentQuantity + $quantity;
        
        // Validate against stock
        if ($newTotalQuantity > $variant['quantity']) {
            echo json_encode([
                'success' => false,
                'message' => 'Not enough stock available. Maximum: ' . $variant['quantity']
            ]);
            exit;
        }
        
        // Add to cart
        $result = $this->cartModel->addToCart($userId, $variantId, $quantity);
        
        if ($result) {
            // Get updated cart count and totals
            $cartCount = $this->cartModel->getCartCount($userId);
            $subtotal = $this->cartModel->getCartSubtotal($userId);
            
            // Return raw numbers for JS
            echo json_encode([
                'success' => true,
                'message' => 'Product added to cart',
                'cartCount' => $cartCount,
                'cartSubtotal' => (float)$subtotal
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to add product to cart'
            ]);
        }
        exit;
    }
    
    /**
     * Update cart item quantity (AJAX)
     */
    public function updateQuantity() {
        header('Content-Type: application/json');
        
        if (!$this->validateCsrfToken()) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid security token'
            ]);
            exit;
        }
        
        $userId = Session::get('user_id');
        $variantId = $_POST['variant_id'] ?? null;
        $quantity = (int)($_POST['quantity'] ?? 1);
        
        if (!$variantId || $quantity < 1) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid request'
            ]);
            exit;
        }
        
        // Check stock availability
        $variant = $this->getVariantDetails($variantId);
        
        if (!$variant) {
            echo json_encode([
                'success' => false,
                'message' => 'Product not found'
            ]);
            exit;
        }
        
        if ($variant['quantity'] < $quantity) {
            echo json_encode([
                'success' => false,
                'message' => 'Not enough stock. Available: ' . $variant['quantity']
            ]);
            exit;
        }
        
        // Update cart quantity
        $success = $this->cartModel->updateCartQuantity($userId, $variantId, $quantity);
        
        if ($success) {
            $itemTotal = $variant['price'] * $quantity;
            $cartCount = $this->cartModel->getCartCount($userId);
            
            // Recalculate Totals Logic (Mirroring index)
            $subtotal = $this->cartModel->getCartSubtotal($userId);
            
            // Shipping Calculation
            $shippingFee = 0;
            if ($subtotal > 0) {
                 $shippingFee = ($subtotal >= 5000) ? 0 : 50.00;
            }
            $total = $subtotal + $shippingFee;
            
            echo json_encode([
                'success' => true,
                'message' => 'Quantity updated',
                'itemTotal' => (float)$itemTotal,    // Raw Number
                'cartSubtotal' => (float)$subtotal,  // Raw Number
                'shippingFee' => (float)$shippingFee, // Raw Number
                'grandTotal' => (float)$total,       // Raw Number
                'cartCount' => $cartCount
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update quantity'
            ]);
        }
        exit;
    }
    
    /**
     * Remove item from cart (AJAX)
     */
    public function remove() {
        header('Content-Type: application/json');
        
        if (!$this->validateCsrfToken()) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid security token'
            ]);
            exit;
        }
        
        $userId = Session::get('user_id');
        $variantId = $_POST['variant_id'] ?? null;
        
        if (!$variantId) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid request'
            ]);
            exit;
        }
        
        $success = $this->cartModel->removeFromCart($userId, $variantId);
        
        if ($success) {
            $cartCount = $this->cartModel->getCartCount($userId);
            
            // Recalculate Totals
            $subtotal = $this->cartModel->getCartSubtotal($userId);
            
            $shippingFee = 0;
            if ($subtotal > 0) {
                 $shippingFee = ($subtotal >= 5000) ? 0 : 50.00;
            }
            $total = $subtotal + $shippingFee;
            
            echo json_encode([
                'success' => true,
                'message' => 'Item removed from cart',
                'cartCount' => $cartCount,
                'cartSubtotal' => (float)$subtotal,
                'shippingFee' => (float)$shippingFee,
                'grandTotal' => (float)$total
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Item not found in cart'
            ]);
        }
        exit;
    }
    
    /**
     * Clear entire cart
     */
    public function clear() {
        if (!$this->validateCsrfToken()) {
            RedirectHelper::redirect('/cart?status=error&message=' . urlencode('Invalid security token'));
        }
        
        $userId = Session::get('user_id');
        $success = $this->cartModel->clearCart($userId);
        
        if ($success) {
            RedirectHelper::redirect('/cart?status=success&message=' . urlencode('Cart cleared successfully'));
        } else {
            RedirectHelper::redirect('/cart?status=error&message=' . urlencode('Failed to clear cart'));
        }
    }
    
    /**
     * Get cart count for header badge (AJAX)
     */
    public function getCartCountAjax() {
        header('Content-Type: application/json');
        
        $userId = Session::get('user_id');
        $count = $this->cartModel->getCartCount($userId);
        
        echo json_encode([
            'success' => true,
            'cartCount' => $count
        ]);
        exit;
    }
    
    /**
     * Validate cart before checkout
     * Check stock availability and adjust if needed
     */
    public function validateCart() {
        header('Content-Type: application/json');
        
        $userId = Session::get('user_id');
        
        // Cleanup unavailable items
        $this->cartModel->cleanupCart($userId);
        
        // Check for stock issues
        $stockIssues = $this->cartModel->validateCartStock($userId);
        
        if (!empty($stockIssues)) {
            // Auto-adjust quantities
            $this->cartModel->autoAdjustQuantities($userId);
            
            echo json_encode([
                'success' => false,
                'message' => 'Some items have been adjusted due to stock availability',
                'issues' => $stockIssues,
                'reload' => true
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'message' => 'Cart is valid'
            ]);
        }
        exit;
    }
    
    /**
     * Helper: Get variant details with product info
     */
    private function getVariantDetails($variantId) {
        $conn = $this->productModel->getConnection();
        
        $query = "SELECT 
                    pv.variant_id,
                    pv.product_id,
                    pv.variant_name,
                    pv.price,
                    pv.quantity,
                    pv.color,
                    pv.size,
                    pv.material,
                    pv.product_picture,
                    p.name as product_name,
                    p.cover_picture,
                    p.slug
                  FROM product_variants pv
                  INNER JOIN products p ON pv.product_id = p.product_id
                  WHERE pv.variant_id = ? 
                    AND pv.is_active = 1 
                    AND p.status = 'PUBLISHED'
                    AND p.is_deleted = 0";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $variantId);
        $stmt->execute();
        $result = $stmt->get_result();
        $variant = $result->fetch_assoc();
        $stmt->close();
        
        return $variant;
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