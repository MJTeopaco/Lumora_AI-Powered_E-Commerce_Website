<?php
// app/Controllers/ReviewController.php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\ProductReview;
use App\Models\Product;
use App\Models\Notification;
use App\Models\Shop;
use App\Models\User;        // Added
use App\Models\UserProfile; // Added
use App\Models\Cart;        // Added for header count

class ReviewController extends Controller {
    
    protected $reviewModel;
    protected $productModel;
    protected $userModel;       // Added
    protected $userProfileModel;// Added
    protected $cartModel;       // Added

    public function __construct() {
        $this->reviewModel = new ProductReview();
        $this->productModel = new Product();
        $this->userModel = new User();              // Init
        $this->userProfileModel = new UserProfile();// Init
        $this->cartModel = new Cart();              // Init
    }

    /**
     * Get reviews for a product (AJAX)
     */
    public function getProductReviews() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        $productId = $_GET['product_id'] ?? null;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $sortBy = $_GET['sort'] ?? 'newest';
        $limit = 10;
        $offset = ($page - 1) * $limit;

        if (!$productId) {
            echo json_encode(['success' => false, 'message' => 'Product ID required']);
            exit;
        }

        $reviews = $this->reviewModel->getProductReviews($productId, $limit, $offset, $sortBy);
        $stats = $this->reviewModel->getProductReviewStats($productId);

        echo json_encode([
            'success' => true,
            'reviews' => $reviews,
            'stats' => $stats,
            'current_page' => $page,
            'has_more' => count($reviews) === $limit
        ]);
        exit;
    }

    /**
     * Show review form
     */
    public function showReviewForm() {
        if (!Session::has('user_id')) {
            $this->redirect('/login');
        }

        $productId = $_GET['product_id'] ?? null;
        
        if (!$productId) {
            $this->redirect('/');
        }

        $userId = Session::get('user_id');
        
        // Use getProductById so we can find the product using the ID from the URL
        $product = $this->productModel->getProductById($productId);

        if (!$product) {
            $this->redirect('/');
        }

        // 1. Check if already reviewed
        if ($this->reviewModel->hasUserReviewed($userId, $productId)) {
            Session::set('error', 'You have already reviewed this product.');
            $this->redirect('/products/' . $product['slug']);
            return;
        }

        // 2. STRICT CHECK: Check if purchased and delivered
        $hasPurchased = $this->reviewModel->hasUserPurchased($userId, $productId);
        
        if (!$hasPurchased) {
            Session::set('error', 'You can only review products you have purchased and received.');
            $this->redirect('/products/' . $product['slug']);
            return;
        }

        $purchaseDetails = $this->reviewModel->getUserPurchaseDetails($userId, $productId);

        // --- NEW: Fetch User Data for Header ---
        $user = $this->userModel->findById($userId);
        $userProfile = $this->userProfileModel->getByUserId($userId);
        $isSeller = $this->userModel->checkRole($userId);
        $cartCount = $this->cartModel->getCartCount($userId);
        // ---------------------------------------

        $data = [
            'pageTitle' => 'Write a Review - ' . $product['name'],
            'product' => $product,
            'hasPurchased' => true, 
            'purchaseDetails' => $purchaseDetails,
            // Header Data
            'isLoggedIn' => true,
            'username' => $user['username'] ?? 'User',
            'userProfile' => $userProfile,
            'isSeller' => $isSeller,
            'cartCount' => $cartCount
        ];

        $this->view('reviews/form', $data);
    }

    /**
     * Submit a review
     */
    public function submitReview() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        // CSRF Check
        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid security token']);
            exit;
        }

        if (!Session::has('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Please login first', 'redirect' => '/login']);
            exit;
        }

        $userId = Session::get('user_id');
        $productId = (int)($_POST['product_id'] ?? 0);
        $rating = (int)($_POST['rating'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $comment = trim($_POST['comment'] ?? '');

        // Validation
        if ($productId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product']);
            exit;
        }

        if ($rating < 1 || $rating > 5) {
            echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5']);
            exit;
        }

        if (empty($comment) || strlen($comment) < 10) {
            echo json_encode(['success' => false, 'message' => 'Review must be at least 10 characters']);
            exit;
        }

        // Strict Purchase Check (Security)
        if (!$this->reviewModel->hasUserPurchased($userId, $productId)) {
            echo json_encode(['success' => false, 'message' => 'You must purchase this product to review it.']);
            exit;
        }

        // Check if already reviewed
        if ($this->reviewModel->hasUserReviewed($userId, $productId)) {
            echo json_encode(['success' => false, 'message' => 'You have already reviewed this product']);
            exit;
        }

        $purchaseDetails = $this->reviewModel->getUserPurchaseDetails($userId, $productId);

        $reviewData = [
            'user_id' => $userId,
            'product_id' => $productId,
            'order_id' => $purchaseDetails['order_id'] ?? null,
            'variant_id' => $purchaseDetails['variant_id'] ?? null,
            'title' => $title,
            'rating' => $rating,
            'comment' => $comment,
            'is_verified_purchase' => 1
        ];

        $reviewId = $this->reviewModel->createReview($reviewData);

        if ($reviewId) {
            // Handle image uploads
            if (!empty($_FILES['images']['name'][0])) {
                $this->handleReviewImages($reviewId, $_FILES['images']);
            }

            // Get product to redirect back (using getProductById)
            $product = $this->productModel->getProductById($productId);
            
            // --- ENHANCED NOTIFICATION SYSTEM INTEGRATION ---
            // Notify seller of new review
            $shopModel = new Shop();
            $notificationModel = new Notification();
            $shop = $shopModel->getShopById($product['shop_id']);
            
            if ($shop) {
                $notificationModel->notifyNewReview(
                    $shop['user_id'], // seller's user_id
                    $product['name'],
                    $rating,
                    $reviewId
                );
            }
            // ------------------------------------------------

            echo json_encode([
                'success' => true, 
                'message' => 'Review submitted successfully!',
                'redirect' => '/products/' . $product['slug']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to submit review']);
        }
        exit;
    }

    /**
     * Handle review image uploads
     */
    private function handleReviewImages($reviewId, $files) {
        $uploadDir = 'uploads/reviews/';
        
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $maxImages = 5;
        $count = 0;

        foreach ($files['name'] as $key => $filename) {
            if ($count >= $maxImages) break;

            if ($files['error'][$key] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                if (in_array($ext, $allowedExts)) {
                    $newFilename = 'review_' . $reviewId . '_' . time() . '_' . $count . '.' . $ext;
                    $destination = $uploadDir . $newFilename;

                    if (move_uploaded_file($files['tmp_name'][$key], $destination)) {
                        $this->reviewModel->addReviewImage($reviewId, $destination, $count);
                        $count++;
                    }
                }
            }
        }
    }

    /**
     * Update review
     */
    public function updateReview() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid security token']);
            exit;
        }

        if (!Session::has('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');
        $reviewId = (int)($_POST['review_id'] ?? 0);
        $rating = (int)($_POST['rating'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $comment = trim($_POST['comment'] ?? '');

        if ($rating < 1 || $rating > 5 || empty($comment)) {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            exit;
        }

        $data = [
            'title' => $title,
            'rating' => $rating,
            'comment' => $comment
        ];

        if ($this->reviewModel->updateReview($reviewId, $userId, $data)) {
            echo json_encode(['success' => true, 'message' => 'Review updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update review']);
        }
        exit;
    }

    /**
     * Delete review
     */
    public function deleteReview() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid security token']);
            exit;
        }

        if (!Session::has('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');
        $reviewId = (int)($_POST['review_id'] ?? 0);

        if ($this->reviewModel->deleteReview($reviewId, $userId)) {
            echo json_encode(['success' => true, 'message' => 'Review deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete review']);
        }
        exit;
    }

    /**
     * Mark review as helpful
     */
    public function markHelpful() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid security token']);
            exit;
        }

        if (!Session::has('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Please login first']);
            exit;
        }

        $userId = Session::get('user_id');
        $reviewId = (int)($_POST['review_id'] ?? 0);

        if ($this->reviewModel->markHelpful($reviewId, $userId)) {
            echo json_encode(['success' => true, 'message' => 'Thank you for your feedback!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'You have already marked this helpful']);
        }
        exit;
    }

    /**
     * Add seller response (for shop owners)
     */
    public function addSellerResponse() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid security token']);
            exit;
        }

        if (!Session::has('user_id') || !Session::has('shop_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $shopId = Session::get('shop_id');
        $reviewId = (int)($_POST['review_id'] ?? 0);
        $responseText = trim($_POST['response_text'] ?? '');

        if (empty($responseText) || strlen($responseText) < 10) {
            echo json_encode(['success' => false, 'message' => 'Response must be at least 10 characters']);
            exit;
        }

        if ($this->reviewModel->addSellerResponse($reviewId, $shopId, $responseText)) {
            
            // --- ENHANCED NOTIFICATION SYSTEM INTEGRATION ---
            // Fetch review details to get customer ID and product name for notification
            $conn = $this->reviewModel->getConnection();
            $stmt = $conn->prepare("SELECT pr.user_id, p.name as product_name FROM product_reviews pr JOIN products p ON pr.product_id = p.product_id WHERE pr.review_id = ?");
            $stmt->bind_param("i", $reviewId);
            $stmt->execute();
            $reviewData = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($reviewData) {
                // Get shop name
                $shopModel = new Shop();
                $shop = $shopModel->getShopById($shopId);
                
                $notificationModel = new Notification();
                $notificationModel->notifySellerResponse(
                    $reviewData['user_id'], // customer's user_id
                    $reviewData['product_name'],
                    $shop['shop_name'],
                    $reviewId
                );
            }
            // ------------------------------------------------

            echo json_encode(['success' => true, 'message' => 'Response added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add response']);
        }
        exit;
    }

    /**
     * Validate CSRF Token
     */
    private function validateCsrfToken($token) {
        return Session::has('csrf_token') && hash_equals(Session::get('csrf_token'), $token);
    }
}