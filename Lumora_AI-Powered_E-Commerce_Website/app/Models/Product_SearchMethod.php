<?php
// app/Models/Product.php - ADD THESE METHODS TO YOUR EXISTING Product.php

namespace App\Models;

use App\Core\Database;

class Product {
    protected $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    // ... (your existing methods) ...

    /**
     * Get products by array of IDs (for smart search results)
     * 
     * @param array $productIds Array of product IDs
     * @return array Products
     */
    public function getProductsByIds($productIds) {
        if (empty($productIds)) {
            return [];
        }

        // Create placeholders for prepared statement
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        
        $query = "
            SELECT 
                p.product_id as id,
                p.name,
                p.slug,
                p.short_description,
                p.cover_picture as image,
                MIN(pv.price) as price,
                SUM(pv.quantity) as stock,
                GROUP_CONCAT(DISTINCT pc.name SEPARATOR ', ') as categories
            FROM products p
            LEFT JOIN product_variants pv ON p.product_id = pv.product_id AND pv.is_active = 1
            LEFT JOIN product_category_links pcl ON p.product_id = pcl.product_id
            LEFT JOIN product_categories pc ON pcl.category_id = pc.category_id
            WHERE p.product_id IN ($placeholders)
                AND p.status = 'PUBLISHED' 
                AND p.is_deleted = 0
            GROUP BY p.product_id
        ";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters dynamically
        $types = str_repeat('i', count($productIds));
        $stmt->bind_param($types, ...$productIds);
        
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
     * Get search suggestions for autocomplete
     * 
     * @param string $query Search query
     * @param int $limit Number of suggestions
     * @return array Product names
     */
    public function getSearchSuggestions($query, $limit = 5) {
        $searchTerm = '%' . $query . '%';
        
        $query = "
            SELECT DISTINCT 
                p.name,
                p.slug
            FROM products p
            WHERE p.name LIKE ?
                AND p.status = 'PUBLISHED'
                AND p.is_deleted = 0
            ORDER BY 
                CASE 
                    WHEN p.name LIKE ? THEN 1
                    ELSE 2
                END,
                p.name ASC
            LIMIT ?
        ";
        
        $stmt = $this->conn->prepare($query);
        $startsWith = $query . '%';
        $stmt->bind_param('ssi', $searchTerm, $startsWith, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $suggestions = [];
        while ($row = $result->fetch_assoc()) {
            $suggestions[] = [
                'name' => $row['name'],
                'slug' => $row['slug']
            ];
        }
        
        $stmt->close();
        return $suggestions;
    }

    /**
     * Get related products (fallback when ML search is unavailable)
     * 
     * @param int $productId Current product ID
     * @param int $limit Number of related products
     * @return array Related products
     */
    public function getRelatedProducts($productId, $limit = 5) {
        $query = "
            SELECT DISTINCT
                p2.product_id as id,
                p2.name,
                p2.slug,
                p2.short_description,
                p2.cover_picture as image,
                MIN(pv2.price) as price,
                SUM(pv2.quantity) as stock
            FROM products p1
            INNER JOIN product_category_links pcl1 ON p1.product_id = pcl1.product_id
            INNER JOIN product_category_links pcl2 ON pcl1.category_id = pcl2.category_id
            INNER JOIN products p2 ON pcl2.product_id = p2.product_id
            LEFT JOIN product_variants pv2 ON p2.product_id = pv2.product_id AND pv2.is_active = 1
            WHERE p1.product_id = ?
                AND p2.product_id != ?
                AND p2.status = 'PUBLISHED'
                AND p2.is_deleted = 0
            GROUP BY p2.product_id
            ORDER BY RAND()
            LIMIT ?
        ";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('iii', $productId, $productId, $limit);
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
     * Find product by ID
     * 
     * @param int $productId Product ID
     * @return array|null Product data or null
     */
    public function findById($productId) {
        $query = "
            SELECT 
                p.product_id as id,
                p.name,
                p.slug,
                p.short_description,
                p.long_description,
                p.cover_picture as image,
                MIN(pv.price) as price,
                SUM(pv.quantity) as stock
            FROM products p
            LEFT JOIN product_variants pv ON p.product_id = pv.product_id AND pv.is_active = 1
            WHERE p.product_id = ?
                AND p.status = 'PUBLISHED'
                AND p.is_deleted = 0
            GROUP BY p.product_id
        ";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $product = $result->fetch_assoc();
        $stmt->close();
        
        return $product;
    }

    /**
     * Get products by search term (standard SQL search - fallback)
     * 
     * @param string $searchTerm Search query
     * @return array Products
     */
    public function getProductsBySearch($searchTerm) {
        $searchPattern = '%' . $searchTerm . '%';
        
        $query = "
            SELECT 
                p.product_id as id,
                p.name,
                p.slug,
                p.short_description,
                p.cover_picture as image,
                MIN(pv.price) as price,
                SUM(pv.quantity) as stock,
                GROUP_CONCAT(DISTINCT pc.name SEPARATOR ', ') as categories
            FROM products p
            LEFT JOIN product_variants pv ON p.product_id = pv.product_id AND pv.is_active = 1
            LEFT JOIN product_category_links pcl ON p.product_id = pcl.product_id
            LEFT JOIN product_categories pc ON pcl.category_id = pc.category_id
            WHERE (p.name LIKE ? OR p.short_description LIKE ? OR p.long_description LIKE ?)
                AND p.status = 'PUBLISHED'
                AND p.is_deleted = 0
            GROUP BY p.product_id
            ORDER BY 
                CASE 
                    WHEN p.name LIKE ? THEN 1
                    WHEN p.short_description LIKE ? THEN 2
                    ELSE 3
                END,
                p.created_at DESC
            LIMIT 50
        ";
        
        $stmt = $this->conn->prepare($query);
        $startPattern = $searchTerm . '%';
        $stmt->bind_param('sssss', $searchPattern, $searchPattern, $searchPattern, $startPattern, $startPattern);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        
        $stmt->close();
        return $products;
    }
}