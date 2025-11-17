<?php
// app/Models/Product.php

namespace App\Models;

use App\Core\Database;

class Product {
    
    private $db;

    public function __construct() {
        // FIX: Use the static getConnection() method for mysqli
        $this->db = Database::getConnection();
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

        $result = $this->db->query($query);
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
        $stmt = $this->db->prepare($query);
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
                  LEFT JOIN product_categories pc ON p.category_id = pc.category_id
                  WHERE p.slug = ? AND p.status = 'PUBLISHED' AND p.is_deleted = 0";
        
        $stmt = $this->db->prepare($query);
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
        
        $stmt = $this->db->prepare($query);
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
                  LEFT JOIN product_categories pc ON p.category_id = pc.category_id
                  LEFT JOIN product_variants pv ON p.product_id = pv.product_id AND pv.is_active = 1
                  WHERE p.status = 'PUBLISHED' AND p.is_deleted = 0
                    AND (p.name LIKE ? OR p.short_description LIKE ?)
                  GROUP BY p.product_id
                  ORDER BY p.created_at DESC";
        
        $likeSearchTerm = '%' . $searchTerm . '%';
        $stmt = $this->db->prepare($query);
        // FIX: bind_param takes two 's' for two string parameters
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
        $stmt = $this->db->prepare($query);
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
        
        $stmt = $this->db->prepare($query);
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
        
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
}