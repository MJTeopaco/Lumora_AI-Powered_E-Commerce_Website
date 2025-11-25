<?php
// app/Models/Collections.php

namespace App\Models;

use App\Core\Database;

class Collections {
    
    protected $conn;

    public function __construct() {
        // FIX: Use the static getConnection() method for mysqli
        $this->conn = Database::getConnection();
    }

    public function getConnection() {
        return $this->conn;
    }

    // Get all product categories
    public function getAllCategories() {
        $query = "SELECT * FROM product_categories ORDER BY name ASC";
        $result = $this->conn->query($query);
        
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    // Get products by category with shop details and minimum price
    public function getProductsByCategory($categoryId) {
        $query = "
            SELECT 
                p.product_id,
                p.name,
                p.slug,
                p.cover_picture,
                p.short_description,
                s.shop_name,
                s.slug as shop_slug,
                MIN(pv.price) as price
            FROM products p
            INNER JOIN shops s ON p.shop_id = s.shop_id
            INNER JOIN product_variants pv ON p.product_id = pv.product_id
            INNER JOIN product_category_mapping pcm ON p.product_id = pcm.product_id
            WHERE pcm.category_id = ? 
                AND p.status = 'PUBLISHED' 
                AND p.is_deleted = 0
                AND s.is_deleted = 0
            GROUP BY p.product_id
            ORDER BY p.created_at DESC
            LIMIT 8
        ";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $categoryId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    // Get all products with shop details and minimum price (for "All" category)
    public function getAllProducts($limit = 24) {
        $query = "
            SELECT 
                p.product_id,
                p.name,
                p.slug,
                p.cover_picture,
                p.short_description,
                s.shop_name,
                s.slug as shop_slug,
                MIN(pv.price) as price
            FROM products p
            INNER JOIN shops s ON p.shop_id = s.shop_id
            INNER JOIN product_variants pv ON p.product_id = pv.product_id
            WHERE p.status = 'PUBLISHED' 
                AND p.is_deleted = 0
                AND s.is_deleted = 0
            GROUP BY p.product_id
            ORDER BY p.created_at DESC
            LIMIT ?
        ";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

}