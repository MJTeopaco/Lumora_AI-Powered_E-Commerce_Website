<?php
// app/Helpers/DatabaseSync.php

namespace App\Helpers;

use mysqli;

class DatabaseSync {
    private $railwayConn;
    private $infinityfreeConn;
    
    public function __construct($infinityfreeConn) {
        $this->infinityfreeConn = $infinityfreeConn;
        $this->railwayConn = $this->getRailwayConnection();
    }
    
    /**
     * Get Railway database connection
     */
    private function getRailwayConnection() {
        $host = 'mysql.railway.internal';
        $port = 3306;
        $database = 'lumora_db';
        $username = 'root';
        $password = 'EzvKehimLbchUExSxIshylCtTfHmMlel';
        
        $conn = new mysqli($host, $username, $password, $database, $port);
        
        if ($conn->connect_error) {
            error_log("Railway DB Connection failed: " . $conn->connect_error);
            return null;
        }
        
        $conn->set_charset("utf8mb4");
        return $conn;
    }
    
    /**
     * Check if Railway connection is available
     */
    public function isRailwayAvailable() {
        return $this->railwayConn !== null && $this->railwayConn->ping();
    }
    
    /**
     * Sync product to Railway database
     * Only syncs basic product info and category relationship
     */
    public function syncProductToRailway($productData, $categoryId) {
        if (!$this->isRailwayAvailable()) {
            error_log("Railway DB not available for product sync");
            return false;
        }
        
        try {
            // First, ensure the category exists in Railway
            $this->syncCategoryToRailway($categoryId);
            
            // Prepare product data for Railway (minimal fields needed for ML)
            $stmt = $this->railwayConn->prepare("
                INSERT INTO products (
                    product_id,
                    shop_id,
                    name,
                    slug,
                    short_description,
                    description,
                    cover_picture,
                    status,
                    created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    name = VALUES(name),
                    slug = VALUES(slug),
                    short_description = VALUES(short_description),
                    description = VALUES(description),
                    cover_picture = VALUES(cover_picture),
                    status = VALUES(status)
            ");
            
            $stmt->bind_param(
                "iissssss",
                $productData['product_id'],
                $productData['shop_id'],
                $productData['name'],
                $productData['slug'],
                $productData['short_description'],
                $productData['description'],
                $productData['cover_picture'],
                $productData['status'],
                $productData['created_at']
            );
            
            $productSynced = $stmt->execute();
            $stmt->close();
            
            if (!$productSynced) {
                error_log("Failed to sync product to Railway");
                return false;
            }
            
            // Sync product-category link
            $linkStmt = $this->railwayConn->prepare("
                INSERT INTO product_category_links (
                    product_id,
                    category_id
                ) VALUES (?, ?)
                ON DUPLICATE KEY UPDATE
                    category_id = VALUES(category_id)
            ");
            
            $linkStmt->bind_param("ii", $productData['product_id'], $categoryId);
            $linkSynced = $linkStmt->execute();
            $linkStmt->close();
            
            return $productSynced && $linkSynced;
            
        } catch (\Exception $e) {
            error_log("Error syncing product to Railway: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Sync category to Railway database
     */
    private function syncCategoryToRailway($categoryId) {
        if (!$this->isRailwayAvailable()) {
            return false;
        }
        
        // Get category data from InfinityFree
        $stmt = $this->infinityfreeConn->prepare("
            SELECT category_id, name, slug, description, parent_category_id, created_at
            FROM product_categories
            WHERE category_id = ?
        ");
        
        $stmt->bind_param("i", $categoryId);
        $stmt->execute();
        $result = $stmt->get_result();
        $category = $result->fetch_assoc();
        $stmt->close();
        
        if (!$category) {
            error_log("Category not found in InfinityFree DB: $categoryId");
            return false;
        }
        
        // Sync to Railway
        $railwayStmt = $this->railwayConn->prepare("
            INSERT INTO product_categories (
                category_id,
                name,
                slug,
                description,
                parent_category_id,
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                slug = VALUES(slug),
                description = VALUES(description),
                parent_category_id = VALUES(parent_category_id)
        ");
        
        $railwayStmt->bind_param(
            "isssis",
            $category['category_id'],
            $category['name'],
            $category['slug'],
            $category['description'],
            $category['parent_category_id'],
            $category['created_at']
        );
        
        $success = $railwayStmt->execute();
        $railwayStmt->close();
        
        return $success;
    }
    
    /**
     * Sync all existing products and categories to Railway
     * Useful for initial migration or bulk sync
     */
    public function syncAllProducts() {
        if (!$this->isRailwayAvailable()) {
            error_log("Railway DB not available for bulk sync");
            return ['success' => false, 'message' => 'Railway database not available'];
        }
        
        $synced = 0;
        $failed = 0;
        
        // Get all products from InfinityFree
        $stmt = $this->infinityfreeConn->prepare("
            SELECT p.*, pcl.category_id
            FROM products p
            LEFT JOIN product_category_links pcl ON p.product_id = pcl.product_id
            WHERE p.is_deleted = 0
        ");
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($product = $result->fetch_assoc()) {
            $productData = [
                'product_id' => $product['product_id'],
                'shop_id' => $product['shop_id'],
                'name' => $product['name'],
                'slug' => $product['slug'],
                'short_description' => $product['short_description'],
                'description' => $product['description'],
                'cover_picture' => $product['cover_picture'],
                'status' => $product['status'],
                'created_at' => $product['created_at']
            ];
            
            if ($this->syncProductToRailway($productData, $product['category_id'])) {
                $synced++;
            } else {
                $failed++;
            }
        }
        
        $stmt->close();
        
        return [
            'success' => true,
            'synced' => $synced,
            'failed' => $failed,
            'message' => "Synced $synced products, $failed failed"
        ];
    }
    
    /**
     * Close Railway connection
     */
    public function close() {
        if ($this->railwayConn) {
            $this->railwayConn->close();
        }
    }
    
    /**
     * Destructor to ensure connection is closed
     */
    public function __destruct() {
        $this->close();
    }
}