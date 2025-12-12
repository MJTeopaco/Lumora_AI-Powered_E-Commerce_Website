<?php
// app/Controllers/SearchController.php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Product;
use App\Models\Search;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Services\SmartSearchService;

class SearchController extends Controller {
    
    protected $productModel;
    protected $userModel;
    protected $profileModel;
    protected $searchService;
    protected $searchMethodModel;

    public function __construct() {
        $this->productModel = new Product();
        $this->userModel = new User();
        $this->profileModel = new UserProfile();
        $this->searchService = new SmartSearchService();
        $this->searchMethodModel = new Search();
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
     * Smart search using ML-powered similarity search
     */
    public function smartSearch() {
        try {
            // Get search query from POST
            $query = $_POST['q'] ?? '';
            $query = trim($query);

            if (empty($query)) {
                // Redirect back to collections with error
                Session::setFlash('error', 'Please enter a search query');
                $this->redirect('/collections/index');
                return;
            }

            // Optional: Get additional parameters
            $topK = $_POST['top_k'] ?? 20;  // Number of results
            $minSimilarity = $_POST['min_similarity'] ?? 0.15;  // Minimum similarity threshold

            // Check if search service is available
            if (!$this->searchService->isServiceHealthy()) {
                // Fallback to regular search if ML service is down
                Session::setFlash('warning', 'Smart search temporarily unavailable. Using standard search.');
                $this->redirect('/collections/index?search=' . urlencode($query));
                return;
            }

            // Call ML search service
            $searchResults = $this->searchService->search($query, $topK, $minSimilarity);

            if (!$searchResults['success']) {
                // Fallback to regular search on error
                error_log("Smart search error: " . ($searchResults['error'] ?? 'Unknown error'));
                Session::setFlash('warning', 'Using standard search');
                $this->redirect('/collections/index?search=' . urlencode($query));
                return;
            }

            // Extract product IDs from search results
            $productIds = array_column($searchResults['results'], 'product_id');

            if (empty($productIds)) {
                // No results found
                $products = [];
            } else {
                // Fetch products from database in the order returned by ML
                $products = $this->searchMethodModel->getProductsByIds($productIds);
                
                // Sort products to match ML ranking
                $productMap = [];
                foreach ($products as $product) {
                    $productMap[$product['id']] = $product;
                }
                
                $sortedProducts = [];
                foreach ($productIds as $id) {
                    if (isset($productMap[$id])) {
                        $sortedProducts[] = $productMap[$id];
                    }
                }
                $products = $sortedProducts;
            }

            // Get all categories for filter
            $categories = $this->productModel->getAllCategories();

            // Get User Data for Header
            $userData = $this->getUserData();

            // Prepare view data
            $data = array_merge($userData, [
                'pageTitle' => 'Search Results: ' . htmlspecialchars($query) . ' - Lumora',
                'products' => $products,
                'categories' => $categories,
                'currentCategory' => null,
                'currentSort' => 'relevance',
                'searchTerm' => $query,
                'priceMin' => null,
                'priceMax' => null,
                'totalProducts' => count($products),
                'isSmartSearch' => true,
                'searchResults' => $searchResults['results'] ?? []
            ]);

            // Load collections view
            $this->view('collections/index', $data);

        } catch (\Exception $e) {
            error_log("Smart search exception: " . $e->getMessage());
            Session::setFlash('error', 'An error occurred during search');
            $this->redirect('/collections/index');
        }
    }

    /**
     * AJAX endpoint for autocomplete suggestions
     */
    public function autocomplete() {
        header('Content-Type: application/json');
        
        try {
            $query = $_GET['q'] ?? '';
            $query = trim($query);

            if (strlen($query) < 2) {
                echo json_encode([
                    'success' => true,
                    'suggestions' => []
                ]);
                return;
            }

            // Get quick suggestions from database
            $suggestions = $this->searchMethodModel->getSearchSuggestions($query, 5);

            echo json_encode([
                'success' => true,
                'suggestions' => $suggestions
            ]);

        } catch (\Exception $e) {
            error_log("Autocomplete error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => 'Failed to get suggestions'
            ]);
        }
    }

    /**
     * Get related products based on current product
     */
    public function relatedProducts($productId) {
        header('Content-Type: application/json');
        
        try {
            // Get product details
            $product = $this->searchMethodModel->findById($productId);
            
            if (!$product) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Product not found'
                ]);
                return;
            }

            // Create search query from product name and description
            $query = $product['name'] . ' ' . ($product['short_description'] ?? '');

            // Use smart search to find similar products
            if ($this->searchService->isServiceHealthy()) {
                $searchResults = $this->searchService->search($query, 6, 0.2);
                
                if ($searchResults['success']) {
                    $productIds = array_column($searchResults['results'], 'product_id');
                    
                    // Remove current product from results
                    $productIds = array_filter($productIds, function($id) use ($productId) {
                        return $id != $productId;
                    });
                    
                    // Get product details
                    $relatedProducts = $this->searchMethodModel->getProductsByIds(array_slice($productIds, 0, 5));
                    
                    echo json_encode([
                        'success' => true,
                        'products' => $relatedProducts
                    ]);
                    return;
                }
            }

            // Fallback: Get products from same category
            $relatedProducts = $this->searchMethodModel->getRelatedProducts($productId, 5);
            
            echo json_encode([
                'success' => true,
                'products' => $relatedProducts
            ]);

        } catch (\Exception $e) {
            error_log("Related products error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => 'Failed to get related products'
            ]);
        }
    }
}