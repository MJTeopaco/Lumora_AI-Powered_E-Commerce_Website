<?php
// app/Controllers/ProductManagementController.php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Shop;
use App\Models\ProductManagement;

class ProductManagementController extends Controller {

    protected $shopModel;
    protected $productMgmtModel;

    public function __construct() {
        $this->shopModel = new Shop();
        $this->productMgmtModel = new ProductManagement();
    }

    /**
     * Display all products for the shop
     */
    public function index() {
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

        // Get filter and search parameters
        $status = $_GET['status'] ?? 'all';
        $search = $_GET['search'] ?? '';

        // Get products based on filters
        if (!empty($search)) {
            $products = $this->productMgmtModel->searchProducts($shopData['shop_id'], $search);
        } elseif ($status !== 'all') {
            $products = $this->productMgmtModel->filterProductsByStatus($shopData['shop_id'], strtoupper($status));
        } else {
            $products = $this->productMgmtModel->getShopProducts($shopData['shop_id']);
        }

        // Get product statistics
        $stats = $this->productMgmtModel->getProductStats($shopData['shop_id']);

        $data = [
            'pageTitle' => 'My Products',
            'currentPage' => 'products',
            'shop' => $shopData,
            'products' => $products,
            'stats' => $stats,
            'currentFilter' => $status,
            'searchTerm' => $search
        ];

        $this->view('shop/products', $data, 'shop');
    }

    /**
     * View single product details
     * FIXED: Changed parameter name from $productId to $id to match route
     */
    public function show($id) {
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

        // Use $id instead of $productId
        $product = $this->productMgmtModel->getProductDetails($id, $shopData['shop_id']);

        if (!$product) {
            Session::set('error', 'Product not found');
            $this->redirect('/shop/products');
        }

        $data = [
            'pageTitle' => $product['name'],
            'currentPage' => 'products',
            'shop' => $shopData,
            'product' => $product
        ];

        $this->view('shop/product-details', $data, 'shop');
    }

    /**
     * Update product status via AJAX
     */
    public function updateStatus() {
        header('Content-Type: application/json');

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

        $productId = $_POST['product_id'] ?? null;
        $status = $_POST['status'] ?? null;

        if (!$productId || !$status) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }

        $result = $this->productMgmtModel->updateProductStatus(
            $productId, 
            $shopData['shop_id'], 
            $status
        );

        if ($result) {
            echo json_encode([
                'success' => true, 
                'message' => 'Product status updated successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to update product status'
            ]);
        }
        exit;
    }

    /**
     * Delete product
     */
    public function delete() {
        if (!Session::has('user_id')) {
            Session::set('error', 'Please login to access this page');
            $this->redirect('/login');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Session::set('error', 'Invalid request method');
            $this->redirect('/shop/products');
        }

        $userId = Session::get('user_id');
        $shopData = $this->shopModel->getShopByUserId($userId);
        
        if (!$shopData) {
            Session::set('error', 'Shop not found');
            $this->redirect('/');
        }

        $productId = $_POST['product_id'] ?? null;

        if (!$productId) {
            Session::set('error', 'Product ID is required');
            $this->redirect('/shop/products');
        }

        $result = $this->productMgmtModel->deleteProduct($productId, $shopData['shop_id']);

        if ($result) {
            Session::set('success', 'Product deleted successfully');
        } else {
            Session::set('error', 'Failed to delete product');
        }

        $this->redirect('/shop/products');
    }

    /**
     * Toggle variant status via AJAX
     */
    public function toggleVariant() {
        header('Content-Type: application/json');

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

        $variantId = $_POST['variant_id'] ?? null;
        $productId = $_POST['product_id'] ?? null;

        if (!$variantId || !$productId) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }

        $result = $this->productMgmtModel->toggleVariantStatus(
            $variantId,
            $productId,
            $shopData['shop_id']
        );

        if ($result) {
            echo json_encode([
                'success' => true, 
                'message' => 'Variant status updated successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to update variant status'
            ]);
        }
        exit;
    }

    /**
     * Bulk actions for products
     */
    public function bulkAction() {
        if (!Session::has('user_id')) {
            Session::set('error', 'Please login to access this page');
            $this->redirect('/login');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Session::set('error', 'Invalid request method');
            $this->redirect('/shop/products');
        }

        $userId = Session::get('user_id');
        $shopData = $this->shopModel->getShopByUserId($userId);
        
        if (!$shopData) {
            Session::set('error', 'Shop not found');
            $this->redirect('/');
        }

        $action = $_POST['bulk_action'] ?? null;
        $productIds = $_POST['product_ids'] ?? [];

        if (!$action || empty($productIds)) {
            Session::set('error', 'Please select products and an action');
            $this->redirect('/shop/products');
        }

        $successCount = 0;
        $failCount = 0;

        foreach ($productIds as $productId) {
            $result = false;

            switch ($action) {
                case 'publish':
                    $result = $this->productMgmtModel->updateProductStatus(
                        $productId, 
                        $shopData['shop_id'], 
                        'PUBLISHED'
                    );
                    break;
                
                case 'unpublish':
                    $result = $this->productMgmtModel->updateProductStatus(
                        $productId, 
                        $shopData['shop_id'], 
                        'UNPUBLISHED'
                    );
                    break;
                
                case 'archive':
                    $result = $this->productMgmtModel->updateProductStatus(
                        $productId, 
                        $shopData['shop_id'], 
                        'ARCHIVED'
                    );
                    break;
                
                case 'delete':
                    $result = $this->productMgmtModel->deleteProduct(
                        $productId, 
                        $shopData['shop_id']
                    );
                    break;
            }

            if ($result) {
                $successCount++;
            } else {
                $failCount++;
            }
        }

        if ($successCount > 0) {
            Session::set('success', "$successCount product(s) updated successfully");
        }
        
        if ($failCount > 0) {
            Session::set('error', "$failCount product(s) failed to update");
        }

        $this->redirect('/shop/products');
    }
}