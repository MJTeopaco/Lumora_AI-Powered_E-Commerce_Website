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
     * Get database connection
     * @return mysqli
     */
    public function getConnection() {
        return $this->conn;
    }

    // ... [Previous methods remain unchanged: getSellerStatus, getShopByUserId, getShopById, updateBillingDetails, getDashboardStats, getTopProducts] ...

    /**
     * Get all shop products
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
                COUNT(DISTINCT pv.variant_id) as variant_count,
                MIN(pv.price) as min_price,
                MAX(pv.price) as max_price,
                SUM(pv.quantity) as total_stock
            FROM products p
            LEFT JOIN product_variants pv ON p.product_id = pv.product_id AND pv.is_active = 1
            WHERE p.shop_id = ? AND p.is_deleted = 0 AND p.status = 'PUBLISHED'
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

    // ... [Keep all other existing methods: getShopOrders, getRecentOrders, getOrderDetails, getOrderItems, updateOrderStatus, getOrderStatsByStatus, getTotalOrderCount, getCancelledOrders, getShopAddress, createShop, updateShop, deleteShop, getAllCategories, createProduct, linkProductToCategory, getOrCreateTag, linkProductToTag, getAllActiveShops, getFeaturedShops, getShopBySlug, getShopProductPreviews, getShopProductCount, getAvailableRegions, getAvailableSpecialties] ...
    
    /**
     * Get seller status for a user
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
     * Get shop by user ID (Updated with Payout Fields)
     */
    public function getShopByUserId($userId) {
        $stmt = $this->conn->prepare("
            SELECT s.*, 
                   a.address_line_1, a.address_line_2, a.barangay, 
                   a.city, a.province, a.region, a.postal_code,
                   s.payout_provider, s.payout_account_name, s.payout_account_number
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
     * Get shop by ID
     */
    public function getShopById($shopId) {
        $stmt = $this->conn->prepare("
            SELECT 
                s.*,
                a.address_line_1,
                a.address_line_2,
                a.barangay,
                a.city,
                a.province,
                a.region,
                a.postal_code
            FROM shops s
            LEFT JOIN addresses a ON s.address_id = a.address_id
            WHERE s.shop_id = ?
        ");
        
        $stmt->bind_param("i", $shopId);
        $stmt->execute();
        $result = $stmt->get_result();
        $shop = $result->fetch_assoc();
        $stmt->close();
        
        return $shop;
    }

    /**
     * Update Shop Billing Details (NEW)
     */
    public function updateBillingDetails($shopId, $provider, $accName, $accNumber) {
        $stmt = $this->conn->prepare("
            UPDATE shops 
            SET payout_provider = ?,
                payout_account_name = ?,
                payout_account_number = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE shop_id = ?
        ");
        
        $stmt->bind_param("sssi", $provider, $accName, $accNumber, $shopId);
        return $stmt->execute();
    }

    /**
     * Get dashboard statistics
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
     * Get top selling products
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
     * Get all orders for a specific shop
     */
    public function getShopOrders($shopId, $status = null, $searchTerm = null, $limit = 50, $offset = 0) {
        $query = "SELECT 
                    o.order_id,
                    o.user_id,
                    o.order_status,
                    o.total_amount,
                    o.shipping_fee,
                    o.created_at,
                    o.updated_at,
                    up.full_name as customer_name,
                    u.email as customer_email,
                    up.phone_number as customer_phone,
                    COUNT(DISTINCT oi.order_item_id) as item_count,
                    SUM(oi.quantity) as total_items,
                    CONCAT(a.address_line_1, ', ', a.city, ', ', a.province) as shipping_address
                  FROM orders o
                  LEFT JOIN users u ON o.user_id = u.user_id
                  LEFT JOIN user_profiles up ON o.user_id = up.user_id
                  LEFT JOIN order_items oi ON o.order_id = oi.order_id
                  LEFT JOIN addresses a ON o.shipping_address_id = a.address_id
                  WHERE o.shop_id = ? 
                  AND o.is_deleted = 0";
        
        $params = [$shopId];
        $types = "i";
        
        // Filter by status if provided
        if ($status !== null && $status !== 'all') {
            $query .= " AND o.order_status = ?";
            $params[] = strtoupper($status);
            $types .= "s";
        }
        
        // Search by order ID or customer name
        if ($searchTerm !== null && !empty($searchTerm)) {
            $query .= " AND (o.order_id LIKE ? OR up.full_name LIKE ? OR u.email LIKE ?)";
            $searchPattern = "%{$searchTerm}%";
            $params[] = $searchPattern;
            $params[] = $searchPattern;
            $params[] = $searchPattern;
            $types .= "sss";
        }
        
        $query .= " GROUP BY o.order_id
                    ORDER BY o.created_at DESC
                    LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $orders = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $orders;
    }

    /**
     * Get recent orders for dashboard display
     */
    public function getRecentOrders($shopId, $limit = 5) {
        $query = "SELECT 
                    o.order_id,
                    o.order_status,
                    o.total_amount,
                    o.created_at,
                    up.full_name as customer_name,
                    COUNT(oi.order_item_id) as item_count
                  FROM orders o
                  LEFT JOIN users u ON o.user_id = u.user_id
                  LEFT JOIN user_profiles up ON o.user_id = up.user_id
                  LEFT JOIN order_items oi ON o.order_id = oi.order_id
                  WHERE o.shop_id = ? 
                  AND o.is_deleted = 0
                  GROUP BY o.order_id
                  ORDER BY o.created_at DESC
                  LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $shopId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $orders = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $orders;
    }

    /**
     * Get detailed order information
     */
    public function getOrderDetails($orderId, $shopId) {
        $query = "SELECT 
                    o.*,
                    up.full_name as customer_name,
                    u.email as customer_email,
                    up.phone_number as customer_phone,
                    a.address_line_1,
                    a.address_line_2,
                    a.barangay,
                    a.city,
                    a.province,
                    a.region,
                    a.postal_code
                  FROM orders o
                  LEFT JOIN users u ON o.user_id = u.user_id
                  LEFT JOIN user_profiles up ON o.user_id = up.user_id
                  LEFT JOIN addresses a ON o.shipping_address_id = a.address_id
                  WHERE o.order_id = ? 
                  AND o.shop_id = ?
                  AND o.is_deleted = 0";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $orderId, $shopId);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        $stmt->close();
        
        if (!$order) {
            return null;
        }
        
        // Get order items
        $order['items'] = $this->getOrderItems($orderId);
        
        return $order;
    }

    /**
     * Get all items for a specific order
     */
    public function getOrderItems($orderId) {
        $query = "SELECT 
                    oi.*,
                    p.name as product_name,
                    p.cover_picture,
                    pv.color,
                    pv.size,
                    pv.material
                  FROM order_items oi
                  JOIN product_variants pv ON oi.variant_id = pv.variant_id
                  JOIN products p ON pv.product_id = p.product_id
                  WHERE oi.order_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $items = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $items;
    }

    /**
     * Update order status (seller side)
     */
    public function updateOrderStatus($orderId, $shopId, $newStatus) {
        if (empty($newStatus)) {
            return false;
        }

        $validStatuses = [
            'PENDING_PAYMENT',
            'PROCESSING', 
            'READY_TO_SHIP', 
            'SHIPPED', 
            'DELIVERED', 
            'CANCELLED',
            'COMPLETED'
        ];
        
        if (!in_array($newStatus, $validStatuses)) {
            return false;
        }
        
        $query = "UPDATE orders 
                  SET order_status = ?,
                      updated_at = CURRENT_TIMESTAMP
                  WHERE order_id = ? 
                  AND shop_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sii", $newStatus, $orderId, $shopId);
        
        $success = false;
        if ($stmt->execute()) {
            $success = true;
        }
        $stmt->close();
        
        return $success;
    }

    /**
     * Get order statistics by status for a shop
     */
    public function getOrderStatsByStatus($shopId) {
        $query = "SELECT 
                    order_status,
                    COUNT(*) as count,
                    SUM(total_amount) as total_revenue
                  FROM orders
                  WHERE shop_id = ? 
                  AND is_deleted = 0
                  GROUP BY order_status";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $shopId);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats = [];
        
        while ($row = $result->fetch_assoc()) {
            $stats[$row['order_status']] = $row;
        }
        
        $stmt->close();
        return $stats;
    }

    /**
     * Get total order count for shop
     */
    public function getTotalOrderCount($shopId, $status = null) {
        $query = "SELECT COUNT(*) as total 
                  FROM orders 
                  WHERE shop_id = ? 
                  AND is_deleted = 0";
        
        $params = [$shopId];
        $types = "i";
        
        if ($status !== null && $status !== 'all') {
            $query .= " AND order_status = ?";
            $params[] = strtoupper($status);
            $types .= "s";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['total'] ?? 0;
    }

    /**
     * Get cancelled orders
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
     */
    public function getOrCreateTag($tagName) {
        $stmt = $this->conn->prepare("SELECT tag_id FROM product_tags WHERE name = ?");
        $stmt->bind_param("s", $tagName);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $row['tag_id'];
        }
        
        $stmt = $this->conn->prepare("INSERT INTO product_tags (name) VALUES (?)");
        $stmt->bind_param("s", $tagName);
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }

    /**
     * Link product to tag
     */
    public function linkProductToTag($productId, $tagId) {
        $stmt = $this->conn->prepare("
            INSERT INTO product_tag_links (product_id, tag_id)
            VALUES (?, ?)
        ");
        
        $stmt->bind_param("ii", $productId, $tagId);
        
        return $stmt->execute();
    }

    /**
     * Get all active shops with location and product info
     */
    public function getAllActiveShops() {
        $stmt = $this->conn->prepare("
            SELECT 
                s.shop_id,
                s.user_id,
                s.shop_name,
                s.slug,
                s.shop_description,
                s.contact_email,
                s.contact_phone,
                s.shop_banner,
                s.shop_profile,
                s.created_at,
                a.city,
                a.province,
                a.region,
                up.profile_pic,
                up.full_name as owner_name,
                COUNT(DISTINCT p.product_id) as product_count,
                COUNT(DISTINCT o.order_id) as order_count,
                GROUP_CONCAT(DISTINCT pc.name SEPARATOR ', ') as specialties
            FROM shops s
            LEFT JOIN addresses a ON s.address_id = a.address_id
            LEFT JOIN users u ON s.user_id = u.user_id
            LEFT JOIN user_profiles up ON u.user_id = up.user_id
            LEFT JOIN products p ON s.shop_id = p.shop_id AND p.is_deleted = 0 AND p.status = 'PUBLISHED'
            LEFT JOIN orders o ON s.shop_id = o.shop_id AND o.is_deleted = 0
            LEFT JOIN product_category_links pcl ON p.product_id = pcl.product_id
            LEFT JOIN product_categories pc ON pcl.category_id = pc.category_id
            WHERE s.is_deleted = 0
            GROUP BY s.shop_id
            ORDER BY order_count DESC, product_count DESC
        ");
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $shops = [];
        while ($row = $result->fetch_assoc()) {
            $shops[] = $row;
        }
        
        $stmt->close();
        return $shops;
    }

    /**
     * Get featured shops (top sellers)
     */
    public function getFeaturedShops($limit = 3) {
        $stmt = $this->conn->prepare("
            SELECT 
                s.shop_id,
                s.shop_name,
                s.slug,
                s.shop_description,
                s.shop_banner,
                s.shop_profile,
                s.created_at,
                a.city,
                a.province,
                a.region,
                up.profile_pic,
                up.full_name as owner_name,
                COUNT(DISTINCT p.product_id) as product_count,
                COUNT(DISTINCT o.order_id) as order_count,
                COALESCE(SUM(CASE WHEN o.order_status IN ('DELIVERED', 'COMPLETED') THEN o.total_amount ELSE 0 END), 0) as total_revenue,
                GROUP_CONCAT(DISTINCT pc.name SEPARATOR ', ') as specialties
            FROM shops s
            LEFT JOIN addresses a ON s.address_id = a.address_id
            LEFT JOIN users u ON s.user_id = u.user_id
            LEFT JOIN user_profiles up ON u.user_id = up.user_id
            LEFT JOIN products p ON s.shop_id = p.shop_id AND p.is_deleted = 0 AND p.status = 'PUBLISHED'
            LEFT JOIN orders o ON s.shop_id = o.shop_id AND o.is_deleted = 0
            LEFT JOIN product_category_links pcl ON p.product_id = pcl.product_id
            LEFT JOIN product_categories pc ON pcl.category_id = pc.category_id
            WHERE s.is_deleted = 0
            GROUP BY s.shop_id
            ORDER BY total_revenue DESC, order_count DESC
            LIMIT ?
        ");
        
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $shops = [];
        while ($row = $result->fetch_assoc()) {
            $shops[] = $row;
        }
        
        $stmt->close();
        return $shops;
    }

    /**
     * Get shop by slug
     */
    public function getShopBySlug($slug) {
        $stmt = $this->conn->prepare("
            SELECT 
                s.*,
                a.address_line_1,
                a.address_line_2,
                a.barangay,
                a.city,
                a.province,
                a.region,
                a.postal_code,
                up.profile_pic,
                up.full_name as owner_name
            FROM shops s
            LEFT JOIN addresses a ON s.address_id = a.address_id
            LEFT JOIN users u ON s.user_id = u.user_id
            LEFT JOIN user_profiles up ON u.user_id = up.user_id
            WHERE s.slug = ? AND s.is_deleted = 0
        ");
        
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        $result = $stmt->get_result();
        $shop = $result->fetch_assoc();
        $stmt->close();
        
        return $shop;
    }

    /**
     * Get product previews for a shop
     */
    public function getShopProductPreviews($shopId, $limit = 3) {
        $stmt = $this->conn->prepare("
            SELECT 
                p.product_id,
                p.name,
                p.slug,
                p.cover_picture,
                MIN(pv.price) as price
            FROM products p
            LEFT JOIN product_variants pv ON p.product_id = pv.product_id AND pv.is_active = 1
            WHERE p.shop_id = ? 
            AND p.is_deleted = 0 
            AND p.status = 'PUBLISHED'
            GROUP BY p.product_id
            ORDER BY p.created_at DESC
            LIMIT ?
        ");
        
        $stmt->bind_param("ii", $shopId, $limit);
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
     * Get shop product count
     */
    public function getShopProductCount($shopId) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as count
            FROM products
            WHERE shop_id = ? 
            AND is_deleted = 0 
            AND status = 'PUBLISHED'
        ");
        
        $stmt->bind_param("i", $shopId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['count'] ?? 0;
    }

    /**
     * Get available regions
     */
    public function getAvailableRegions() {
        $stmt = $this->conn->prepare("
            SELECT DISTINCT a.region
            FROM shops s
            INNER JOIN addresses a ON s.address_id = a.address_id
            WHERE s.is_deleted = 0 
            AND a.region IS NOT NULL 
            AND a.region != ''
            ORDER BY a.region ASC
        ");
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $regions = [];
        while ($row = $result->fetch_assoc()) {
            $regions[] = $row['region'];
        }
        
        $stmt->close();
        return $regions;
    }

    /**
     * Get available specialties (categories)
     */
    public function getAvailableSpecialties() {
        $stmt = $this->conn->prepare("
            SELECT DISTINCT pc.name, pc.slug
            FROM product_categories pc
            INNER JOIN product_category_links pcl ON pc.category_id = pcl.category_id
            INNER JOIN products p ON pcl.product_id = p.product_id
            INNER JOIN shops s ON p.shop_id = s.shop_id
            WHERE s.is_deleted = 0 
            AND p.is_deleted = 0
            ORDER BY pc.name ASC
        ");
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $specialties = [];
        while ($row = $result->fetch_assoc()) {
            $specialties[] = $row;
        }
        
        $stmt->close();
        return $specialties;
    }
}