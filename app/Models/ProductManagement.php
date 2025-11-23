<?php
// app/Models/ProductManagement.php

namespace App\Models;

use App\Core\Database;

class ProductManagement {
    
    protected $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    /**
     * Get all products for a shop with variant information
     * @param int $shopId
     * @return array
     */
    public function getShopProducts($shopId) {
        $stmt = $this->conn->prepare("
            SELECT 
                p.product_id,
                p.name,
                p.slug,
                p.short_description,
                p.cover_picture,
                p.status,
                p.created_at,
                p.updated_at,
                COUNT(DISTINCT pv.variant_id) as variant_count,
                COALESCE(MIN(pv.price), 0) as min_price,
                COALESCE(MAX(pv.price), 0) as max_price,
                COALESCE(SUM(pv.quantity), 0) as total_stock,
                GROUP_CONCAT(DISTINCT pc.name SEPARATOR ', ') as categories
            FROM products p
            LEFT JOIN product_variants pv ON p.product_id = pv.product_id AND pv.is_active = 1
            LEFT JOIN product_category_links pcl ON p.product_id = pcl.product_id
            LEFT JOIN product_categories pc ON pcl.category_id = pc.category_id
            WHERE p.shop_id = ? AND p.is_deleted = 0
            GROUP BY p.product_id
            ORDER BY p.created_at DESC
        ");
        
        $stmt->bind_param("i", $shopId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        
        $stmt->close();
        return $products;
    }

    /**
     * Get single product details with all variants
     * @param int $productId
     * @param int $shopId (for security - ensure product belongs to shop)
     * @return array|null
     */
    public function getProductDetails($productId, $shopId) {
        $stmt = $this->conn->prepare("
            SELECT 
                p.*,
                GROUP_CONCAT(DISTINCT pc.name SEPARATOR ', ') as categories,
                GROUP_CONCAT(DISTINCT pt.name SEPARATOR ', ') as tags
            FROM products p
            LEFT JOIN product_category_links pcl ON p.product_id = pcl.product_id
            LEFT JOIN product_categories pc ON pcl.category_id = pc.category_id
            LEFT JOIN product_tag_links ptl ON p.product_id = ptl.product_id
            LEFT JOIN product_tags pt ON ptl.tag_id = pt.tag_id
            WHERE p.product_id = ? AND p.shop_id = ? AND p.is_deleted = 0
            GROUP BY p.product_id
        ");
        
        $stmt->bind_param("ii", $productId, $shopId);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();
        
        if ($product) {
            // Get variants for this product
            $product['variants'] = $this->getProductVariants($productId);
        }
        
        return $product;
    }

    /**
     * Get all variants for a product
     * @param int $productId
     * @return array
     */
    public function getProductVariants($productId) {
        $stmt = $this->conn->prepare("
            SELECT 
                variant_id,
                product_id,
                variant_name,
                sku,
                price,
                quantity,
                color,
                size,
                material,
                product_picture,
                is_active,
                created_at,
                updated_at
            FROM product_variants
            WHERE product_id = ?
            ORDER BY variant_id ASC
        ");
        
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $variants = [];
        while ($row = $result->fetch_assoc()) {
            $variants[] = $row;
        }
        
        $stmt->close();
        return $variants;
    }

    /**
     * Update product status
     * @param int $productId
     * @param int $shopId
     * @param string $status
     * @return bool
     */
    public function updateProductStatus($productId, $shopId, $status) {
        $validStatuses = ['DRAFT', 'PUBLISHED', 'UNPUBLISHED', 'ARCHIVED'];
        
        if (!in_array($status, $validStatuses)) {
            return false;
        }
        
        $stmt = $this->conn->prepare("
            UPDATE products 
            SET status = ?, updated_at = CURRENT_TIMESTAMP
            WHERE product_id = ? AND shop_id = ?
        ");
        
        $stmt->bind_param("sii", $status, $productId, $shopId);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    /**
     * Delete product (soft delete)
     * @param int $productId
     * @param int $shopId
     * @return bool
     */
    public function deleteProduct($productId, $shopId) {
        $stmt = $this->conn->prepare("
            UPDATE products 
            SET is_deleted = 1, updated_at = CURRENT_TIMESTAMP
            WHERE product_id = ? AND shop_id = ?
        ");
        
        $stmt->bind_param("ii", $productId, $shopId);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    /**
     * Get product statistics for a shop
     * @param int $shopId
     * @return array
     */
    public function getProductStats($shopId) {
        $stats = [
            'total_products' => 0,
            'published' => 0,
            'draft' => 0,
            'unpublished' => 0,
            'out_of_stock' => 0,
            'low_stock' => 0
        ];

        // Get product counts by status
        $stmt = $this->conn->prepare("
            SELECT 
                status,
                COUNT(*) as count
            FROM products
            WHERE shop_id = ? AND is_deleted = 0
            GROUP BY status
        ");
        
        $stmt->bind_param("i", $shopId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $stats['total_products'] += $row['count'];
            $statusKey = strtolower($row['status']);
            if (isset($stats[$statusKey])) {
                $stats[$statusKey] = $row['count'];
            }
        }
        $stmt->close();

        // Get out of stock and low stock counts
        $stmt = $this->conn->prepare("
            SELECT 
                SUM(CASE WHEN total_stock = 0 THEN 1 ELSE 0 END) as out_of_stock,
                SUM(CASE WHEN total_stock > 0 AND total_stock <= 5 THEN 1 ELSE 0 END) as low_stock
            FROM (
                SELECT 
                    p.product_id,
                    COALESCE(SUM(pv.quantity), 0) as total_stock
                FROM products p
                LEFT JOIN product_variants pv ON p.product_id = pv.product_id AND pv.is_active = 1
                WHERE p.shop_id = ? AND p.is_deleted = 0
                GROUP BY p.product_id
            ) as product_stocks
        ");
        
        $stmt->bind_param("i", $shopId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $stats['out_of_stock'] = (int)$row['out_of_stock'];
            $stats['low_stock'] = (int)$row['low_stock'];
        }
        $stmt->close();

        return $stats;
    }

    /**
     * Search products within a shop
     * @param int $shopId
     * @param string $searchTerm
     * @return array
     */
    public function searchProducts($shopId, $searchTerm) {
        $searchPattern = '%' . $searchTerm . '%';
        
        $stmt = $this->conn->prepare("
            SELECT 
                p.product_id,
                p.name,
                p.slug,
                p.short_description,
                p.cover_picture,
                p.status,
                p.created_at,
                COUNT(DISTINCT pv.variant_id) as variant_count,
                COALESCE(MIN(pv.price), 0) as min_price,
                COALESCE(MAX(pv.price), 0) as max_price,
                COALESCE(SUM(pv.quantity), 0) as total_stock
            FROM products p
            LEFT JOIN product_variants pv ON p.product_id = pv.product_id AND pv.is_active = 1
            WHERE p.shop_id = ? 
                AND p.is_deleted = 0
                AND (p.name LIKE ? OR p.short_description LIKE ?)
            GROUP BY p.product_id
            ORDER BY p.created_at DESC
        ");
        
        $stmt->bind_param("iss", $shopId, $searchPattern, $searchPattern);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        
        $stmt->close();
        return $products;
    }

    /**
     * Filter products by status
     * @param int $shopId
     * @param string $status
     * @return array
     */
    public function filterProductsByStatus($shopId, $status) {
        $stmt = $this->conn->prepare("
            SELECT 
                p.product_id,
                p.name,
                p.slug,
                p.short_description,
                p.cover_picture,
                p.status,
                p.created_at,
                COUNT(DISTINCT pv.variant_id) as variant_count,
                COALESCE(MIN(pv.price), 0) as min_price,
                COALESCE(MAX(pv.price), 0) as max_price,
                COALESCE(SUM(pv.quantity), 0) as total_stock
            FROM products p
            LEFT JOIN product_variants pv ON p.product_id = pv.product_id AND pv.is_active = 1
            WHERE p.shop_id = ? 
                AND p.status = ?
                AND p.is_deleted = 0
            GROUP BY p.product_id
            ORDER BY p.created_at DESC
        ");
        
        $stmt->bind_param("is", $shopId, $status);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        
        $stmt->close();
        return $products;
    }

    /**
     * Toggle variant active status
     * @param int $variantId
     * @param int $productId
     * @param int $shopId
     * @return bool
     */
    public function toggleVariantStatus($variantId, $productId, $shopId) {
        // First verify the variant belongs to a product owned by this shop
        $stmt = $this->conn->prepare("
            SELECT pv.is_active
            FROM product_variants pv
            INNER JOIN products p ON pv.product_id = p.product_id
            WHERE pv.variant_id = ? 
                AND pv.product_id = ?
                AND p.shop_id = ?
        ");
        
        $stmt->bind_param("iii", $variantId, $productId, $shopId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $newStatus = $row['is_active'] == 1 ? 0 : 1;
            $stmt->close();
            
            $updateStmt = $this->conn->prepare("
                UPDATE product_variants 
                SET is_active = ?, updated_at = CURRENT_TIMESTAMP
                WHERE variant_id = ?
            ");
            
            $updateStmt->bind_param("ii", $newStatus, $variantId);
            $result = $updateStmt->execute();
            $updateStmt->close();
            
            return $result;
        }
        
        $stmt->close();
        return false;
    }
}