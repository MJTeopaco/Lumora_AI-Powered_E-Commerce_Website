<?php
// app/Models/Product.php

namespace App\Models;

use App\Core\Database;

class Product {
    
    protected $conn;

    public function __construct() {
        // FIX: Use the static getConnection() method for mysqli
        $this->conn = Database::getConnection();
    }

    public function getConnection() {
        return $this->conn;
    }

    /**
     * Get all published products with their variants
     */
    public function getAllProducts() {
        $query = "
            SELECT 
                p.product_id AS id,
                p.name,
                p.short_description,
                p.description,
                p.cover_picture AS image,
                GROUP_CONCAT(pc.name SEPARATOR ', ') AS categories,
                MIN(pv.price) AS price,
                SUM(pv.quantity) AS stock,
                p.slug
            FROM products p
            LEFT JOIN product_variants pv 
                ON p.product_id = pv.product_id 
                AND pv.is_active = 1

            LEFT JOIN product_category_links pcl 
                ON pcl.product_id = p.product_id

            LEFT JOIN product_categories pc 
                ON pc.category_id = pcl.category_id

            WHERE p.status = 'PUBLISHED' 
            AND p.is_deleted = 0

            GROUP BY p.product_id
            ORDER BY p.created_at DESC
        ";

        $result = $this->conn->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }


    /**
     * Get featured products (limit to specific number)
     */
    public function getFeaturedProducts($limit = 8) {
        $query = "SELECT 
                    p.product_id as id,
                    p.name,
                    p.cover_picture as image,
                    MIN(pv.price) as price,
                    p.slug
                  FROM products p
                  LEFT JOIN product_variants pv ON p.product_id = pv.product_id AND pv.is_active = 1
                  WHERE p.status = 'PUBLISHED' AND p.is_deleted = 0 AND p.is_featured = 1
                  GROUP BY p.product_id
                  ORDER BY p.created_at DESC
                  LIMIT ?";
        
        // FIX: Use mysqli prepared statement and bind_param
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $products;
    }

    // NOTE: The remaining methods will use mysqli prepared statements:
    
    /**
     * Get a single product details by slug
     */
public function getSingleProduct($slug) {
        $query = "SELECT 
                    p.product_id as id,
                    p.name,
                    p.short_description,
                    p.description,
                    p.cover_picture as image,
                    pc.name as category,
                    p.slug,
                    p.meta_title,
                    p.meta_description
                  FROM products p
                  LEFT JOIN product_category_links pcl ON p.product_id = pcl.product_id
                  LEFT JOIN product_categories pc ON pcl.category_id = pc.category_id
                  WHERE p.slug = ? AND p.status = 'PUBLISHED' AND p.is_deleted = 0"; 
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();
        
        return $product;
    }

    // ... continue converting all remaining methods in Product.php ...
    
    /**
     * Get product variants
     */
    public function getProductVariants($productId) {
        $query = "SELECT 
                    variant_id as id,
                    name,
                    price,
                    quantity,
                    sku
                  FROM product_variants
                  WHERE product_id = ? AND is_active = 1
                  ORDER BY price ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $variants = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $variants;
    }
    
    /**
     * Get products by search term
     */
public function getProductsBySearch($searchTerm) {
        $query = "SELECT 
                    p.product_id as id,
                    p.name,
                    p.cover_picture as image,
                    MIN(pv.price) as price,
                    SUM(pv.quantity) as stock,
                    p.slug
                  FROM products p
                  LEFT JOIN product_category_links pcl ON p.product_id = pcl.product_id
                  LEFT JOIN product_categories pc ON pcl.category_id = pc.category_id
                  LEFT JOIN product_variants pv ON p.product_id = pv.product_id AND pv.is_active = 1
                  WHERE p.status = 'PUBLISHED' AND p.is_deleted = 0
                    AND (p.name LIKE ? OR p.short_description LIKE ?)
                  GROUP BY p.product_id
                  ORDER BY p.created_at DESC";
        
        $likeSearchTerm = '%' . $searchTerm . '%';
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $likeSearchTerm, $likeSearchTerm); 
        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $products;
    }
    
    /**
     * Check if variant has stock
     */
    public function hasStock($variantId, $quantity = 1) {
        $query = "SELECT quantity FROM product_variants WHERE variant_id = ? AND is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $variantId);
        $stmt->execute();
        $result = $stmt->get_result();
        $variant = $result->fetch_assoc();
        $stmt->close();
        
        return $variant && $variant['quantity'] >= $quantity;
    }

    /**
     * Update variant stock (decrease)
     */
    public function updateStock($variantId, $quantity) {
        $query = "UPDATE product_variants 
                  SET quantity = quantity - ?,
                      updated_at = CURRENT_TIMESTAMP
                  WHERE variant_id = ? AND quantity >= ?";
        
        $stmt = $this->conn->prepare($query);
        // FIX: bind_param takes three 'i' for three integer parameters
        $stmt->bind_param("iii", $quantity, $variantId, $quantity);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    /**
     * Get all categories
     */
    public function getAllCategories() {
        $query = "SELECT 
                    category_id as id,
                    name,
                    slug 
                  FROM product_categories
                  ORDER BY name ASC";
        
        $result = $this->conn->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

        /**
     * Create a new product
     * @param array $data
     * @return int|false Product ID on success, false on failure
     */
    public function createProduct($data) {
        $stmt = $this->conn->prepare("
            INSERT INTO products (
                shop_id, name, slug, short_description, description, 
                cover_picture, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param(
            "issssss",
            $data['shop_id'],
            $data['name'],
            $data['slug'],
            $data['short_description'],
            $data['description'],
            $data['cover_picture'],
            $data['status']
        );
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        
        return false;
    }

    /**
     * Update product
     * @param int $productId
     * @param array $data
     * @return bool
     */
    public function updateProduct($productId, $data) {
        $stmt = $this->conn->prepare("
            UPDATE products 
            SET name = ?, 
                short_description = ?, 
                description = ?,
                status = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE product_id = ?
        ");
        
        $stmt->bind_param(
            "ssssi",
            $data['name'],
            $data['short_description'],
            $data['description'],
            $data['status'],
            $productId
        );
        
        return $stmt->execute();
    }

    /**
     * Delete product (soft delete)
     * @param int $productId
     * @return bool
     */
    public function deleteProduct($productId) {
        $stmt = $this->conn->prepare("
            UPDATE products 
            SET is_deleted = 1, updated_at = CURRENT_TIMESTAMP
            WHERE product_id = ?
        ");
        
        $stmt->bind_param("i", $productId);
        
        return $stmt->execute();
    }

    /**
     * Create a product variant
     * CRITICAL FIX: Removed variant_id from INSERT (it's AUTO_INCREMENT)
     * and corrected bind_param type string to match 9 parameters
     * @param array $data
     * @return int|false Variant ID on success, false on failure
     */
    public function createProductVariant($data) {
        // FIXED: Removed variant_id from INSERT statement
        $stmt = $this->conn->prepare("
            INSERT INTO product_variants (
                product_id, variant_name, sku, price, quantity, 
                color, size, material, product_picture, is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->conn->error);
            return false;
        }
        
        // FIXED: Changed from "issdissssi" (10 params) to "issdiisssi" (10 params)
        // Type string breakdown:
        // i - product_id (int)
        // s - variant_name (string)
        // s - sku (string)
        // d - price (decimal/double)
        // i - quantity (int)
        // s - color (string)
        // s - size (string)
        // s - material (string)
        // s - product_picture (string)
        // i - is_active (int)
        $stmt->bind_param(
            "issdiisssi",
            $data['product_id'],
            $data['variant_name'],
            $data['sku'],
            $data['price'],
            $data['quantity'],
            $data['color'],
            $data['size'],
            $data['material'],
            $data['product_picture'],
            $data['is_active']
        );
        
        if ($stmt->execute()) {
            $variantId = $this->conn->insert_id;
            $stmt->close();
            return $variantId;
        }
        
        // Get error message for debugging
        $error = $stmt->error;
        $stmt->close();
        
        if ($error) {
            error_log("SQL Error in createProductVariant: " . $error);
            throw new \Exception("Failed to create product variant: " . $error);
        }
        
        return false;
    }

    /**
     * Update product variant
     * @param int $variantId
     * @param array $data
     * @return bool
     */
    public function updateProductVariant($variantId, $data) {
        $stmt = $this->conn->prepare("
            UPDATE product_variants 
            SET variant_name = ?,
                sku = ?, 
                price = ?, 
                quantity = ?,
                color = ?,
                size = ?,
                material = ?,
                product_picture = ?,
                is_active = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE variant_id = ?
        ");
        
        $stmt->bind_param(
            "ssdiisssii",
            $data['variant_name'],
            $data['sku'],
            $data['price'],
            $data['quantity'],
            $data['color'],
            $data['size'],
            $data['material'],
            $data['product_picture'],
            $data['is_active'],
            $variantId
        );
        
        return $stmt->execute();
    }

    /**
     * Delete product variant
     * @param int $variantId
     * @return bool
     */
    public function deleteProductVariant($variantId) {
        $stmt = $this->conn->prepare("
            DELETE FROM product_variants WHERE variant_id = ?
        ");
        
        $stmt->bind_param("i", $variantId);
        
        return $stmt->execute();
    }

    /**
     * Link product to category
     * @param int $productId
     * @param int $categoryId
     * @return bool
     */
    public function linkProductToCategory($productId, $categoryId) {
        $stmt = $this->conn->prepare("
            INSERT INTO product_category_links (product_id, category_id)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE category_id = category_id
        ");
        
        $stmt->bind_param("ii", $productId, $categoryId);
        
        return $stmt->execute();
    }

    /**
     * Remove product category link
     * @param int $productId
     * @return bool
     */
    public function removeProductCategoryLinks($productId) {
        $stmt = $this->conn->prepare("
            DELETE FROM product_category_links WHERE product_id = ?
        ");
        
        $stmt->bind_param("i", $productId);
        
        return $stmt->execute();
    }

    /**
     * Get or create tag
     * @param string $tagName
     * @return int|false Tag ID on success, false on failure
     */
    public function getOrCreateTag($tagName) {
        // Check if tag exists
        $stmt = $this->conn->prepare("
            SELECT tag_id FROM product_tags WHERE name = ?
        ");
        
        $stmt->bind_param("s", $tagName);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $row['tag_id'];
        }
        
        // Create new tag
        $stmt = $this->conn->prepare("
            INSERT INTO product_tags (name) VALUES (?)
        ");
        
        $stmt->bind_param("s", $tagName);
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        
        return false;
    }

    /**
     * Link product to tag
     * @param int $productId
     * @param int $tagId
     * @return bool
     */
    public function linkProductToTag($productId, $tagId) {
        $stmt = $this->conn->prepare("
            INSERT INTO product_tag_links (product_id, tag_id)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE tag_id = tag_id
        ");
        
        $stmt->bind_param("ii", $productId, $tagId);
        
        return $stmt->execute();
    }

    /**
     * Remove product tag links
     * @param int $productId
     * @return bool
     */
    public function removeProductTagLinks($productId) {
        $stmt = $this->conn->prepare("
            DELETE FROM product_tag_links WHERE product_id = ?
        ");
        
        $stmt->bind_param("i", $productId);
        
        return $stmt->execute();
    }

    /**
     * Get product tags
     * @param int $productId
     * @return array
     */
    public function getProductTags($productId) {
        $stmt = $this->conn->prepare("
            SELECT pt.tag_id, pt.name
            FROM product_tags pt
            INNER JOIN product_tag_links ptl ON pt.tag_id = ptl.tag_id
            WHERE ptl.product_id = ?
        ");
        
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $tags = [];
        while ($row = $result->fetch_assoc()) {
            $tags[] = $row;
        }
        
        return $tags;
    }

    /**
     * Get product categories
     * @param int $productId
     * @return array
     */
    public function getProductCategories($productId) {
        $stmt = $this->conn->prepare("
            SELECT pc.category_id, pc.name, pc.slug
            FROM product_categories pc
            INNER JOIN product_category_links pcl ON pc.category_id = pcl.category_id
            WHERE pcl.product_id = ?
        ");
        
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        
        return $categories;
    }

    /**
     * Check if SKU exists
     * @param string $sku
     * @param int|null $excludeVariantId
     * @return bool
     */
    public function skuExists($sku, $excludeVariantId = null) {
        if ($excludeVariantId) {
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) as count FROM product_variants 
                WHERE sku = ? AND variant_id != ?
            ");
            $stmt->bind_param("si", $sku, $excludeVariantId);
        } else {
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) as count FROM product_variants WHERE sku = ?
            ");
            $stmt->bind_param("s", $sku);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'] > 0;
    }
}