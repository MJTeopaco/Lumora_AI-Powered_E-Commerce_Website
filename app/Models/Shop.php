<?php

namespace App\Models;

use App\Core\Database;
use mysqli;

class Shop {
    protected $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    /**
     * Get seller status for a user
     * @param int $userId
     * @return array|null
     */
    public function getSellerStatus($userId) {
        $stmt = $this->conn->prepare("
            SELECT ur.user_id, ur.role_id, ur.is_approved, r.name as role_name
            FROM user_roles ur
            INNER JOIN roles r ON ur.role_id = r.role_id
            WHERE ur.user_id = ? AND r.name = 'seller'
            LIMIT 1
        ");
        
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    /**
     * Get shop by user ID
     * @param int $userId
     * @return array|null
     */
    public function getShopByUserId($userId) {
        $stmt = $this->conn->prepare("
            SELECT s.*, a.address_line_1, a.address_line_2, a.barangay, 
                   a.city, a.province, a.region, a.postal_code
            FROM shops s
            LEFT JOIN addresses a ON s.address_id = a.address_id
            WHERE s.user_id = ? AND s.is_deleted = 0
            ORDER BY s.created_at DESC
            LIMIT 1
        ");
        
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    /**
     * Get dashboard statistics
     * @param int $shopId
     * @return array
     */
    public function getDashboardStats($shopId) {
        $stats = [
            'total_products' => 0,
            'total_orders' => 0,
            'pending_orders' => 0,
            'total_revenue' => 0.00
        ];

        // Get total products
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as total 
            FROM products 
            WHERE shop_id = ? AND is_deleted = 0
        ");
        $stmt->bind_param("i", $shopId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stats['total_products'] = $row['total'] ?? 0;

        // Get total orders
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as total 
            FROM orders 
            WHERE shop_id = ? AND is_deleted = 0
        ");
        $stmt->bind_param("i", $shopId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stats['total_orders'] = $row['total'] ?? 0;

        // Get pending orders
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as total 
            FROM orders 
            WHERE shop_id = ? 
            AND order_status IN ('PENDING_PAYMENT', 'PROCESSING')
            AND is_deleted = 0
        ");
        $stmt->bind_param("i", $shopId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stats['pending_orders'] = $row['total'] ?? 0;

        // Get total revenue (from completed orders)
        $stmt = $this->conn->prepare("
            SELECT COALESCE(SUM(total_amount), 0) as revenue 
            FROM orders 
            WHERE shop_id = ? 
            AND order_status IN ('DELIVERED', 'COMPLETED')
            AND is_deleted = 0
        ");
        $stmt->bind_param("i", $shopId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stats['total_revenue'] = $row['revenue'] ?? 0.00;

        return $stats;
    }

    /**
     * Get recent orders
     * @param int $shopId
     * @param int $limit
     * @return array
     */
    public function getRecentOrders($shopId, $limit = 5) {
        $stmt = $this->conn->prepare("
            SELECT 
                o.order_id as id,
                o.order_status as status,
                o.total_amount as amount,
                o.created_at,
                u.username as customer_name,
                p.name as product_name
            FROM orders o
            INNER JOIN users u ON o.user_id = u.user_id
            LEFT JOIN order_items oi ON o.order_id = oi.order_id
            LEFT JOIN product_variants pv ON oi.variant_id = pv.variant_id
            LEFT JOIN products p ON pv.product_id = p.product_id
            WHERE o.shop_id = ? AND o.is_deleted = 0
            ORDER BY o.created_at DESC
            LIMIT ?
        ");
        
        $stmt->bind_param("ii", $shopId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        
        return $orders;
    }

    /**
     * Get top selling products
     * @param int $shopId
     * @param int $limit
     * @return array
     */
    public function getTopProducts($shopId, $limit = 5) {
        $stmt = $this->conn->prepare("
            SELECT 
                p.product_id as id,
                p.name,
                p.cover_picture as image,
                pc.name as category,
                pv.price,
                COALESCE(SUM(pv.quantity), 0) as stock,
                COALESCE(SUM(oi.quantity), 0) as sales
            FROM products p
            LEFT JOIN product_variants pv ON p.product_id = pv.product_id
            LEFT JOIN order_items oi ON pv.variant_id = oi.variant_id
            LEFT JOIN product_category_links pcl ON p.product_id = pcl.product_id
            LEFT JOIN product_categories pc ON pcl.category_id = pc.category_id
            WHERE p.shop_id = ? AND p.is_deleted = 0
            GROUP BY p.product_id
            ORDER BY sales DESC
            LIMIT ?
        ");
        
        $stmt->bind_param("ii", $shopId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        
        return $products;
    }

    /**
     * Get all shop products
     * @param int $shopId
     * @return array
     */
    public function getShopProducts($shopId) {
        $stmt = $this->conn->prepare("
            SELECT 
                p.product_id,
                p.name,
                p.short_description,
                p.cover_picture,
                p.status,
                p.created_at,
                COUNT(DISTINCT pv.variant_id) as variant_count,
                MIN(pv.price) as min_price,
                MAX(pv.price) as max_price,
                SUM(pv.quantity) as total_stock
            FROM products p
            LEFT JOIN product_variants pv ON p.product_id = pv.product_id AND pv.is_active = 1
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
        
        return $products;
    }

    /**
     * Get all shop orders
     * @param int $shopId
     * @return array
     */
    public function getShopOrders($shopId) {
        $stmt = $this->conn->prepare("
            SELECT 
                o.order_id,
                o.order_status,
                o.total_amount,
                o.created_at,
                o.updated_at,
                u.username as customer_name,
                COUNT(oi.order_item_id) as item_count
            FROM orders o
            INNER JOIN users u ON o.user_id = u.user_id
            LEFT JOIN order_items oi ON o.order_id = oi.order_id
            WHERE o.shop_id = ? AND o.is_deleted = 0
            GROUP BY o.order_id
            ORDER BY o.created_at DESC
        ");
        
        $stmt->bind_param("i", $shopId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        
        return $orders;
    }

    /**
     * Get cancelled orders
     * @param int $shopId
     * @return array
     */
    public function getCancelledOrders($shopId) {
        $stmt = $this->conn->prepare("
            SELECT 
                o.order_id,
                o.order_status,
                o.total_amount,
                o.created_at,
                o.updated_at,
                u.username as customer_name,
                COUNT(oi.order_item_id) as item_count
            FROM orders o
            INNER JOIN users u ON o.user_id = u.user_id
            LEFT JOIN order_items oi ON o.order_id = oi.order_id
            WHERE o.shop_id = ? 
            AND o.order_status IN ('CANCELLED', 'RETURNED')
            AND o.is_deleted = 0
            GROUP BY o.order_id
            ORDER BY o.updated_at DESC
        ");
        
        $stmt->bind_param("i", $shopId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        
        return $orders;
    }

    /**
     * Get shop address
     * @param int $shopId
     * @return array|null
     */
    public function getShopAddress($shopId) {
        $stmt = $this->conn->prepare("
            SELECT a.*
            FROM shops s
            INNER JOIN addresses a ON s.address_id = a.address_id
            WHERE s.shop_id = ?
        ");
        
        $stmt->bind_param("i", $shopId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    /**
     * Create a new shop
     * @param array $data
     * @return int|false Shop ID on success, false on failure
     */
    public function createShop($data) {
        $stmt = $this->conn->prepare("
            INSERT INTO shops (
                user_id, shop_name, contact_email, contact_phone, 
                address_id, shop_description, slug
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param(
            "issssss",
            $data['user_id'],
            $data['shop_name'],
            $data['contact_email'],
            $data['contact_phone'],
            $data['address_id'],
            $data['shop_description'],
            $data['slug']
        );
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        
        return false;
    }

    /**
     * Update shop information
     * @param int $shopId
     * @param array $data
     * @return bool
     */
    public function updateShop($shopId, $data) {
        $stmt = $this->conn->prepare("
            UPDATE shops 
            SET shop_name = ?, 
                contact_email = ?, 
                contact_phone = ?, 
                shop_description = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE shop_id = ?
        ");
        
        $stmt->bind_param(
            "ssssi",
            $data['shop_name'],
            $data['contact_email'],
            $data['contact_phone'],
            $data['shop_description'],
            $shopId
        );
        
        return $stmt->execute();
    }

    /**
     * Delete shop (soft delete)
     * @param int $shopId
     * @return bool
     */
    public function deleteShop($shopId) {
        $stmt = $this->conn->prepare("
            UPDATE shops 
            SET is_deleted = 1, updated_at = CURRENT_TIMESTAMP
            WHERE shop_id = ?
        ");
        
        $stmt->bind_param("i", $shopId);
        
        return $stmt->execute();
    }

    /**
     * Get all categories
     * @return array
     */
    public function getAllCategories() {
        $stmt = $this->conn->prepare("
            SELECT category_id, name, slug
            FROM product_categories
            ORDER BY name ASC
        ");
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        
        return $categories;
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
     * Link product to category
     * @param int $productId
     * @param int $categoryId
     * @return bool
     */
    public function linkProductToCategory($productId, $categoryId) {
        $stmt = $this->conn->prepare("
            INSERT INTO product_category_links (product_id, category_id)
            VALUES (?, ?)
        ");
        
        $stmt->bind_param("ii", $productId, $categoryId);
        
        return $stmt->execute();
    }

    /**
     * Get or create tag
     * @param string $tagName
     * @return int Tag ID
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
        ");
        
        $stmt->bind_param("ii", $productId, $tagId);
        
        return $stmt->execute();
    }
}