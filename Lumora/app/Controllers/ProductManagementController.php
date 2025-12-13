<?php
// app/Controllers/ProductManagementController.php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Shop;
use App\Models\Product;
use App\Models\ProductManagement;
use App\Helpers\AddProductHelper;

class ProductManagementController extends Controller {

    protected $shopModel;
    protected $productMgmtModel;
    protected $productModel;

    public function __construct() {
        $this->shopModel = new Shop();
        $this->productMgmtModel = new ProductManagement();
        $this->productModel = new Product();
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

        $product = $this->productMgmtModel->getProductDetails($id, $shopData['shop_id']);

        if (!$product) {
            Session::set('error', 'Product not found');
            $this->redirect('/shop/products');
        }

        // FIX: Extract variants from product data
        $variants = $product['variants'] ?? [];

        // FIX: Generate productPictures array from variants for the gallery
        // The view expects an array of arrays with 'picture_path'
        $productPictures = [];
        foreach ($variants as $variant) {
            if (!empty($variant['product_picture'])) {
                $productPictures[] = [
                    'picture_path' => $variant['product_picture']
                ];
            }
        }
        
        // Remove duplicate images
        $productPictures = array_map("unserialize", array_unique(array_map("serialize", $productPictures)));

        $data = [
            'pageTitle' => $product['name'],
            'currentPage' => 'products',
            'shop' => $shopData,
            'product' => $product,
            // Pass the missing variables expected by the view
            'variants' => $variants,
            'productPictures' => $productPictures 
        ];

        $this->view('shop/product-details', $data, 'shop');
    }

    /**
     * Show edit product form
     */
    public function edit($id) {
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

        // Get product details with variants
        $product = $this->productMgmtModel->getProductDetails($id, $shopData['shop_id']);

        if (!$product) {
            Session::set('error', 'Product not found or you do not have permission to edit it');
            $this->redirect('/shop/products');
        }

        // Get product categories to find the linked category
        $productCategories = $this->productModel->getProductCategories($id);
        if (!empty($productCategories)) {
            $product['category_id'] = $productCategories[0]['category_id'];
        }

        // Get all categories for dropdown
        $categories = $this->productModel->getAllCategories();

        $data = [
            'pageTitle' => 'Edit Product - ' . $product['name'],
            'currentPage' => 'products',
            'shop' => $shopData,
            'product' => $product,
            'categories' => $categories
        ];

        $this->view('shop/edit-product', $data, 'shop');
    }

    /**
     * Update product
     */
    public function update($id) {
        if (!Session::has('user_id')) {
            Session::set('error', 'Please login to access this page');
            $this->redirect('/login');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Session::set('error', 'Invalid request method');
            $this->redirect('/shop/products');
        }

        // CSRF Protection
        $this->verifyCsrfToken();

        $userId = Session::get('user_id');
        $shopData = $this->shopModel->getShopByUserId($userId);
        
        if (!$shopData) {
            Session::set('error', 'Shop not found');
            $this->redirect('/');
        }

        // Verify product belongs to this shop
        $existingProduct = $this->productMgmtModel->getProductDetails($id, $shopData['shop_id']);
        if (!$existingProduct) {
            Session::set('error', 'Product not found or you do not have permission to edit it');
            $this->redirect('/shop/products');
        }

        try {
            // Prepare basic product data
            $productData = [
                'name' => trim($_POST['product_name']),
                'short_description' => trim($_POST['short_description']),
                'description' => trim($_POST['description']),
                'status' => $_POST['status']
            ];

            // Update product basic info
            $updateResult = $this->productModel->updateProduct($id, $productData);

            if (!$updateResult) {
                throw new \Exception('Failed to update product');
            }

            // Handle cover picture upload (optional)
            if (isset($_FILES['cover_picture']) && $_FILES['cover_picture']['error'] === UPLOAD_ERR_OK) {
                $coverUpload = AddProductHelper::uploadProductImage(
                    $_FILES['cover_picture'], 
                    $shopData['shop_id'], 
                    'product'
                );

                if ($coverUpload['success']) {
                    // Update cover picture in database
                    $stmt = $this->productModel->getConnection()->prepare(
                        "UPDATE products SET cover_picture = ? WHERE product_id = ?"
                    );
                    $stmt->bind_param("si", $coverUpload['filename'], $id);
                    $stmt->execute();
                    $stmt->close();

                    // Delete old cover picture if exists
                    if (!empty($existingProduct['cover_picture']) && file_exists($existingProduct['cover_picture'])) {
                        @unlink($existingProduct['cover_picture']);
                    }
                }
            }

            // Update category link
            if (!empty($_POST['category_id'])) { // CHANGE: Use !empty instead of isset
    $this->productModel->removeProductCategoryLinks($id);
    $this->productModel->linkProductToCategory($id, intval($_POST['category_id']));
}

            // Update tags
            if (isset($_POST['tags'])) {
                $this->productModel->removeProductTagLinks($id);
                AddProductHelper::processTags($_POST['tags'], $id, $this->productModel);
            }

            // Handle variants update
            if (isset($_POST['variants']) && is_array($_POST['variants'])) {
                foreach ($_POST['variants'] as $index => $variantData) {
                    // Skip empty variants
                    if (empty($variantData['price']) || empty($variantData['quantity'])) {
                        continue;
                    }

                    // Handle variant image upload
                    $variantImage = null;
                    if (isset($_FILES['variants']['name'][$index]['product_picture']) && 
                        $_FILES['variants']['error'][$index]['product_picture'] === UPLOAD_ERR_OK) {
                        
                        $variantFile = [
                            'name' => $_FILES['variants']['name'][$index]['product_picture'],
                            'type' => $_FILES['variants']['type'][$index]['product_picture'],
                            'tmp_name' => $_FILES['variants']['tmp_name'][$index]['product_picture'],
                            'error' => $_FILES['variants']['error'][$index]['product_picture'],
                            'size' => $_FILES['variants']['size'][$index]['product_picture']
                        ];

                        $variantUpload = AddProductHelper::uploadProductImage(
                            $variantFile, 
                            $shopData['shop_id'], 
                            'variant'
                        );

                        if ($variantUpload['success']) {
                            $variantImage = $variantUpload['filename'];
                        }
                    }

                    // Check if this is an existing variant or new one
                    if (!empty($variantData['variant_id'])) {
                        // Update existing variant
                        $updateVariantData = [
                            'variant_name' => $variantData['name'] ?? null,
                            'sku' => $variantData['sku'] ?? AddProductHelper::generateSKU($id, $index + 1),
                            'price' => floatval($variantData['price']),
                            'quantity' => intval($variantData['quantity']),
                            'color' => $variantData['color'] ?? null,
                            'size' => $variantData['size'] ?? null,
                            'material' => $variantData['material'] ?? null,
                            'product_picture' => $variantImage ?? $existingProduct['variants'][$index]['product_picture'] ?? null,
                            'is_active' => isset($variantData['status']) ? intval($variantData['status']) : 1
                        ];

                        $this->productModel->updateProductVariant(
                            intval($variantData['variant_id']), 
                            $updateVariantData
                        );
                    } else {
                        // Create new variant
                        $newVariantData = AddProductHelper::prepareVariantData(
                            $variantData,
                            $id,
                            $index + 1,
                            $variantImage,
                            $this->productModel
                        );

                        $this->productModel->createProductVariant($newVariantData);
                    }
                }
            }

            Session::set('success', 'Product updated successfully');
            $this->redirect('/shop/products');

        } catch (\Exception $e) {
            Session::set('error', 'Failed to update product: ' . $e->getMessage());
            $this->redirect('/shop/products/edit/' . $id);
        }
    }

    /**
     * Update product status via AJAX
     */
    public function updateStatus() {
        // CSRF Protection
        $this->verifyCsrfToken();

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
        // CSRF Protection
        $this->verifyCsrfToken();

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
        // CSRF Protection
        $this->verifyCsrfToken();

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
        // CSRF Protection
        $this->verifyCsrfToken();

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