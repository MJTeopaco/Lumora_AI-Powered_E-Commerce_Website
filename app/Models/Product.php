<?php
// app/Models/Product.php

namespace App\Models;

use App\Core\Database;

class Product {
    
    protected $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    public function getConnection() {
        return $this->conn;
    }

    /**
     * Get all published products with basic info
     */
    public function getAllProducts($limit = 12, $offset = 0) {
        $query = "SELECT 
                    p.product_id as id,
                    p.name,
                    p.short_description as description,
                    p.cover_picture as image,
                    p.slug,
                    MIN(pv.price) as price,
                    NULL as old_price,
                    SUM(pv.quantity) as stock,
                    GROUP_CONCAT(DISTINCT pc.name SEPARATOR ', ') as categories
                  FROM products p
                  LEFT JOIN product_variants pv ON p.product_id = pv.product_id AND pv.is_active = 1
                  LEFT JOIN product_category_links pcl ON p.product_id = pcl.product_id
                  LEFT JOIN product_categories pc ON pcl.category_id = pc.category_id
                  WHERE p.status = 'PUBLISHED' AND p.is_deleted = 0
                  GROUP BY p.product_id
                  ORDER BY p.created_at DESC
                  LIMIT ? OFFSET ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $products;
    }

    /**
     * Get featured products for homepage
     */
    public function getFeaturedProducts($limit = 8) {
        $query = "SELECT 
                    p.product_id as id,
                    p.name,
                    p.short_description as description,
                    p.cover_picture as image,
                    p.slug,
                    MIN(pv.price) as price,
                    NULL as old_price,
                    SUM(pv.quantity) as stock,
                    GROUP_CONCAT(DISTINCT pc.name SEPARATOR ', ') as categories
                  FROM products p
                  LEFT JOIN product_variants pv ON p.product_id = pv.product_id AND pv.is_active = 1
                  LEFT JOIN product_category_links pcl ON p.product_id = pcl.product_id
                  LEFT JOIN product_categories pc ON pcl.category_id = pc.category_id
                  WHERE p.status = 'PUBLISHED' 
                    AND p.is_deleted = 0 
                    AND p.is_featured = 1
                  GROUP BY p.product_id
                  ORDER BY p.created_at DESC
                  LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $products;
    }

    /**
     * Get a single product details by slug
     */
    public function getSingleProduct($slug) {
        $query = "SELECT 
                    p.product_id as id,
                    p.name,
                    p.short_description,
                    p.description,
                    p.cover_picture,
                    GROUP_CONCAT(DISTINCT pc.name SEPARATOR ', ') as category,
                    p.slug,
                    p.meta_title,
                    p.meta_description,
                    p.shop_id
                  FROM products p
                  LEFT JOIN product_category_links pcl ON p.product_id = pcl.product_id
                  LEFT JOIN product_categories pc ON pcl.category_id = pc.category_id
                  WHERE p.slug = ? AND p.status = 'PUBLISHED' AND p.is_deleted = 0
                  GROUP BY p.product_id"; 
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();
        
        return $product;
    }

    /**
     * [FIX ADDED] Get product by ID (Required for Reviews)
     */
    public function getProductById($id) {
        $query = "SELECT 
                    p.product_id as id,
                    p.name,
                    p.short_description,
                    p.description,
                    p.cover_picture,
                    p.slug,
                    p.shop_id
                  FROM products p
                  WHERE p.product_id = ? AND p.is_deleted = 0"; 
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();
        
        return $product;
    }

    /**
     * Get product variants
     */
    public function getProductVariants($productId) {
        $query = "SELECT 
                    variant_id,
                    variant_name,
                    price,
                    quantity,
                    sku,
                    color,
                    size,
                    material,
                    product_picture,
                    is_active
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
                    p.slug,
                    GROUP_CONCAT(DISTINCT pc.name SEPARATOR ', ') as categories
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
     * Get products by category
     */
    public function getProductsByCategory($categorySlug, $limit = 20, $offset = 0) {
        $query = "SELECT 
                    p.product_id as id,
                    p.name,
                    p.short_description as description,
                    p.cover_picture as image,
                    p.slug,
                    MIN(pv.price) as price,
                    SUM(pv.quantity) as stock,
                    GROUP_CONCAT(DISTINCT pc.name SEPARATOR ', ') as categories
                  FROM products p
                  INNER JOIN product_category_links pcl ON p.product_id = pcl.product_id
                  INNER JOIN product_categories pc ON pcl.category_id = pc.category_id
                  LEFT JOIN product_variants pv ON p.product_id = pv.product_id AND pv.is_active = 1
                  WHERE p.status = 'PUBLISHED' 
                    AND p.is_deleted = 0
                    AND pc.slug = ?
                  GROUP BY p.product_id
                  ORDER BY p.created_at DESC
                  LIMIT ? OFFSET ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sii", $categorySlug, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $products;
    }
    
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
     * Decrease stock
     */
    public function updateStock($variantId, $quantity) {
        $query = "UPDATE product_variants 
                  SET quantity = quantity - ?,
                      updated_at = CURRENT_TIMESTAMP
                  WHERE variant_id = ? AND quantity >= ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iii", $quantity, $variantId, $quantity);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    /**
     * NEW: Increase stock (for cancellations/refunds)
     */
    public function increaseStock($variantId, $quantity) {
        $query = "UPDATE product_variants 
                  SET quantity = quantity + ?,
                      updated_at = CURRENT_TIMESTAMP
                  WHERE variant_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $quantity, $variantId);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

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

    public function getCategoryCounts() {
        $query = "SELECT 
                    pc.name as category_name,
                    COUNT(DISTINCT p.product_id) as product_count
                  FROM product_categories pc
                  LEFT JOIN product_category_links pcl ON pc.category_id = pcl.category_id
                  LEFT JOIN products p ON pcl.product_id = p.product_id 
                    AND p.status = 'PUBLISHED' 
                    AND p.is_deleted = 0
                  GROUP BY pc.category_id, pc.name
                  ORDER BY pc.name ASC";
        
        $result = $this->conn->query($query);
        $counts = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $counts[$row['category_name']] = $row['product_count'];
            }
        }
        
        return $counts;
    }
    
    public function createProduct($data) {
        $stmt = $this->conn->prepare("
            INSERT INTO products (
                shop_id, name, slug, short_description, description, 
                cover_picture, status, meta_title, meta_description, is_featured
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param(
            "issssssssi",
            $data['shop_id'],
            $data['name'],
            $data['slug'],
            $data['short_description'],
            $data['description'],
            $data['cover_picture'],
            $data['status'],
            $data['meta_title'],
            $data['meta_description'],
            $data['is_featured']
        );
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        
        return false;
    }

    public function updateProduct($productId, $data) {
        $stmt = $this->conn->prepare("
            UPDATE products 
            SET name = ?, 
                short_description = ?, 
                description = ?,
                status = ?,
                meta_title = ?,
                meta_description = ?,
                is_featured = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE product_id = ?
        ");
        
        $stmt->bind_param(
            "ssssssii",
            $data['name'],
            $data['short_description'],
            $data['description'],
            $data['status'],
            $data['meta_title'],
            $data['meta_description'],
            $data['is_featured'],
            $productId
        );
        
        return $stmt->execute();
    }

    public function deleteProduct($productId) {
        $stmt = $this->conn->prepare("
            UPDATE products 
            SET is_deleted = 1, updated_at = CURRENT_TIMESTAMP
            WHERE product_id = ?
        ");
        
        $stmt->bind_param("i", $productId);
        
        return $stmt->execute();
    }

    public function createProductVariant($data) {
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
        
        return false;
    }

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

    public function deleteProductVariant($variantId) {
        $stmt = $this->conn->prepare("
            DELETE FROM product_variants WHERE variant_id = ?
        ");
        
        $stmt->bind_param("i", $variantId);
        
        return $stmt->execute();
    }

    public function linkProductToCategory($productId, $categoryId) {
        $stmt = $this->conn->prepare("
            INSERT INTO product_category_links (product_id, category_id)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE category_id = category_id
        ");
        
        $stmt->bind_param("ii", $productId, $categoryId);
        
        return $stmt->execute();
    }

    public function removeProductCategoryLinks($productId) {
        $stmt = $this->conn->prepare("
            DELETE FROM product_category_links WHERE product_id = ?
        ");
        
        $stmt->bind_param("i", $productId);
        
        return $stmt->execute();
    }

    public function getOrCreateTag($tagName) {
        $stmt = $this->conn->prepare("
            SELECT tag_id FROM product_tags WHERE name = ?
        ");
        
        $stmt->bind_param("s", $tagName);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $row['tag_id'];
        }
        
        $stmt = $this->conn->prepare("
            INSERT INTO product_tags (name) VALUES (?)
        ");
        
        $stmt->bind_param("s", $tagName);
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        
        return false;
    }

    public function linkProductToTag($productId, $tagId) {
        $stmt = $this->conn->prepare("
            INSERT INTO product_tag_links (product_id, tag_id)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE tag_id = tag_id
        ");
        
        $stmt->bind_param("ii", $productId, $tagId);
        
        return $stmt->execute();
    }

    public function removeProductTagLinks($productId) {
        $stmt = $this->conn->prepare("
            DELETE FROM product_tag_links WHERE product_id = ?
        ");
        
        $stmt->bind_param("i", $productId);
        
        return $stmt->execute();
    }

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