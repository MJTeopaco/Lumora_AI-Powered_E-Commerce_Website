<?php 

namespace App\Models;

use App\Core\Database;
use App\Helpers\EncryptionHelper;

class Admin {
    protected $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    /**
     * Check if user is admin
     */
    public function isAdmin($userId) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as count 
            FROM user_roles 
            WHERE user_id = ? AND role_id = 3 AND is_approved = 1
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        
        return $data['count'] > 0;
    }

    // ==================== DASHBOARD STATISTICS ====================
    
    public function getTotalUsers() {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM users");
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        return $data['total'];
    }

    public function getTotalBuyers() {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as total 
            FROM user_roles 
            WHERE role_id = 1 AND is_approved = 1
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        return $data['total'];
    }

    public function getTotalSellers() {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as total 
            FROM user_roles 
            WHERE role_id = 2
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        return $data['total'];
    }

    public function getTotalAdmins() {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as total 
            FROM user_roles 
            WHERE role_id = 3 AND is_approved = 1
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        return $data['total'];
    }

    /**
     * Get recent users
     */
    public function getRecentUsers($limit = 10) {
        $stmt = $this->conn->prepare("
            SELECT u.user_id, u.username, u.email, u.lockout_until, u.created_at
            FROM users u
            ORDER BY u.created_at DESC
            LIMIT ?
        ");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $users = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $users;
    }

    /**
     * Get all users with details
     */
    public function getAllUsers() {
        $stmt = $this->conn->prepare("
            SELECT 
                u.user_id, 
                u.username, 
                u.email, 
                u.lockout_until,
                u.failed_login_attempts,
                u.created_at,
                GROUP_CONCAT(r.name) as roles
            FROM users u
            LEFT JOIN user_roles ur ON u.user_id = ur.user_id
            LEFT JOIN roles r ON ur.role_id = r.role_id
            GROUP BY u.user_id
            ORDER BY u.created_at DESC
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        $users = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $users;
    }

    // ==================== CATEGORY MANAGEMENT ====================
    
    public function getAllCategories() {
        $stmt = $this->conn->prepare("
            SELECT c.category_id, c.name, c.slug,
                COUNT(DISTINCT pcl.product_id) AS product_count,
                c.created_at 
            FROM product_categories c
            LEFT JOIN product_category_links pcl 
                ON pcl.category_id = c.category_id
            GROUP BY c.category_id
            ORDER BY c.name ASC
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        $categories = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $categories;
    }

    /**
     * Check if category exists
     */
    public function categoryExists($name) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as count 
            FROM product_categories 
            WHERE LOWER(name) = LOWER(?)
        ");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        
        return $data['count'] > 0;
    }

    /**
     * Check if category exists except current one
     */
    public function categoryExistsExcept($name, $categoryId) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as count 
            FROM product_categories 
            WHERE LOWER(name) = LOWER(?) AND category_id != ?
        ");
        $stmt->bind_param("si", $name, $categoryId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        
        return $data['count'] > 0;
    }

    /**
     * Get product count for category
     */
    public function getCategoryProductCount($categoryId) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as count
            FROM product_category_links
            WHERE category_id = ?
        ");
        $stmt->bind_param("i", $categoryId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        
        return $data['count'];
    }

    public function addCategory($name, $slug) {
        $stmt = $this->conn->prepare("
            INSERT INTO product_categories (name, slug)
            VALUES (?, ?)
        ");
        $stmt->bind_param("ss", $name, $slug);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function updateCategory($category_id, $name, $slug) {
        $stmt = $this->conn->prepare("
            UPDATE product_categories
            SET name = ?, slug = ?
            WHERE category_id = ?
        ");
        $stmt->bind_param("ssi", $name, $slug, $category_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function deleteCategory($category_id) {
        $stmt = $this->conn->prepare("
            DELETE FROM product_categories
            WHERE category_id = ?
        ");
        $stmt->bind_param("i", $category_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // ==================== SELLER MANAGEMENT ====================

    /**
     * Get pending sellers (with decrypted billing info)
     */
    public function getPendingSellers() {
        $stmt = $this->conn->prepare("
            SELECT 
                u.user_id,
                u.username,
                u.email,
                s.shop_id,
                s.shop_name,
                s.shop_description,
                s.contact_email,
                s.contact_phone,
                s.slug,
                s.payout_provider,
                s.payout_account_name,
                s.payout_account_number,
                s.created_at as applied_at,
                ur.assigned_at,
                a.address_line_1,
                a.address_line_2,
                a.barangay,
                a.city,
                a.province,
                a.region,
                a.postal_code
            FROM user_roles ur
            JOIN users u ON ur.user_id = u.user_id
            JOIN shops s ON s.user_id = u.user_id
            LEFT JOIN addresses a ON a.user_id = u.user_id AND a.address_type = 'shop'
            WHERE ur.role_id = 2 AND ur.is_approved = 0 AND s.is_deleted = 0
            ORDER BY ur.assigned_at DESC
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        $sellers = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        // DECRYPT SENSITIVE BILLING FIELDS
        foreach ($sellers as &$seller) {
            if (!empty($seller['payout_account_name'])) {
                $seller['payout_account_name'] = EncryptionHelper::decrypt($seller['payout_account_name']);
            }
            if (!empty($seller['payout_account_number'])) {
                $seller['payout_account_number'] = EncryptionHelper::decrypt($seller['payout_account_number']);
            }
        }
        
        return $sellers;
    }

    /**
     * Get approved sellers (with decrypted billing info)
     */
    public function getApprovedSellers() {
        $stmt = $this->conn->prepare("
            SELECT 
                u.user_id,
                u.username,
                u.email,
                s.shop_id,
                s.shop_name,
                s.shop_description,
                s.contact_email,
                s.contact_phone,
                s.slug,
                s.payout_provider,
                s.payout_account_name,
                s.payout_account_number,
                s.created_at as shop_created_at,
                ur.assigned_at as approved_at,
                a.address_line_1,
                a.address_line_2,
                a.barangay,
                a.city,
                a.province,
                a.region,
                a.postal_code
            FROM user_roles ur
            JOIN users u ON ur.user_id = u.user_id
            JOIN shops s ON s.user_id = u.user_id
            LEFT JOIN addresses a ON a.user_id = u.user_id AND a.address_type = 'shop'
            WHERE ur.role_id = 2 AND ur.is_approved = 1 AND s.is_deleted = 0
            ORDER BY ur.assigned_at DESC
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        $sellers = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        // DECRYPT SENSITIVE BILLING FIELDS
        foreach ($sellers as &$seller) {
            if (!empty($seller['payout_account_name'])) {
                $seller['payout_account_name'] = EncryptionHelper::decrypt($seller['payout_account_name']);
            }
            if (!empty($seller['payout_account_number'])) {
                $seller['payout_account_number'] = EncryptionHelper::decrypt($seller['payout_account_number']);
            }
        }
        
        return $sellers;
    }

    /**
     * Approve seller
     */
    public function approveSeller($userId) {
        $stmt = $this->conn->prepare("
            UPDATE user_roles 
            SET is_approved = 1 
            WHERE user_id = ? AND role_id = 2
        ");
        $stmt->bind_param("i", $userId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Reject seller
     */
    public function rejectSeller($userId) {
        // Delete seller role and mark shop as deleted
        $this->conn->begin_transaction();
        
        try {
            // Delete user role
            $stmt1 = $this->conn->prepare("DELETE FROM user_roles WHERE user_id = ? AND role_id = 2");
            $stmt1->bind_param("i", $userId);
            $stmt1->execute();
            $stmt1->close();
            
            // Mark shop as deleted
            $stmt2 = $this->conn->prepare("UPDATE shops SET is_deleted = 1 WHERE user_id = ?");
            $stmt2->bind_param("i", $userId);
            $stmt2->execute();
            $stmt2->close();
            
            $this->conn->commit();
            return true;
        } catch (\Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    /**
     * Suspend seller
     */
    public function suspendSeller($userId) {
        $this->conn->begin_transaction();
        
        try {
            // Revoke approval
            $stmt1 = $this->conn->prepare("
                UPDATE user_roles 
                SET is_approved = 0 
                WHERE user_id = ? AND role_id = 2
            ");
            $stmt1->bind_param("i", $userId);
            $stmt1->execute();
            $stmt1->close();
            
            // Mark shop as deleted (hide from marketplace)
            $stmt2 = $this->conn->prepare("UPDATE shops SET is_deleted = 1 WHERE user_id = ?");
            $stmt2->bind_param("i", $userId);
            $stmt2->execute();
            $stmt2->close();
            
            $this->conn->commit();
            return true;
        } catch (\Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    // ==================== PRODUCT CATEGORY LINKS ====================

    public function assignCategoriesToProduct($product_id, $categories) {
        // Remove old links
        $stmt = $this->conn->prepare("
            DELETE FROM product_category_links
            WHERE product_id = ?
        ");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stmt->close();

        // Insert new links
        $stmt = $this->conn->prepare("
            INSERT INTO product_category_links (product_id, category_id)
            VALUES (?, ?)
        ");

        foreach ($categories as $cat_id) {
            $stmt->bind_param("ii", $product_id, $cat_id);
            $stmt->execute();
        }

        $stmt->close();
        return true;
    }

    public function getCategoriesByProduct($product_id) {
        $stmt = $this->conn->prepare("
            SELECT c.*
            FROM product_category_links pcl
            JOIN product_categories c ON c.category_id = pcl.category_id
            WHERE pcl.product_id = ?
        ");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $categories = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $categories;
    }
    
    // ==================== REPORTING & SALES ====================

    public function getSalesOverview($startDate, $endDate) {
        $stmt = $this->conn->prepare("
            SELECT 
                COUNT(DISTINCT order_id) as total_orders,
                SUM(total_amount) as total_revenue,
                AVG(total_amount) as average_order_value
            FROM orders 
            WHERE order_status = 'DELIVERED' 
            AND created_at BETWEEN ? AND ?
        ");
        
        // Append time to dates to cover full days
        $start = $startDate . ' 00:00:00';
        $end = $endDate . ' 23:59:59';
        
        $stmt->bind_param("ss", $start, $end);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getDailySales($startDate, $endDate) {
        $stmt = $this->conn->prepare("
            SELECT 
                DATE(created_at) as date, 
                SUM(total_amount) as revenue,
                COUNT(order_id) as orders
            FROM orders 
            WHERE order_status = 'DELIVERED' 
            AND created_at BETWEEN ? AND ?
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ");
        
        $start = $startDate . ' 00:00:00';
        $end = $endDate . ' 23:59:59';
        
        $stmt->bind_param("ss", $start, $end);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getTopSellingProducts($limit = 5) {
        $stmt = $this->conn->prepare("
            SELECT 
                p.name,
                s.shop_name,
                SUM(oi.quantity) as total_sold,
                SUM(oi.total_price) as revenue_generated
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.order_id
            JOIN product_variants pv ON oi.variant_id = pv.variant_id
            JOIN products p ON pv.product_id = p.product_id
            JOIN shops s ON p.shop_id = s.shop_id
            WHERE o.order_status = 'DELIVERED'
            GROUP BY p.product_id
            ORDER BY total_sold DESC
            LIMIT ?
        ");
        
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // ==================== PAYOUT MANAGEMENT ====================

    /**
     * Get all pending payouts grouped by shop (with decrypted billing info)
     */
    public function getPendingPayouts() {
        $stmt = $this->conn->prepare("
            SELECT 
                s.shop_name,
                s.shop_id,
                s.payout_provider,
                s.payout_account_name,
                s.payout_account_number,
                s.contact_phone,
                s.contact_email,
                u.username as seller_name,
                u.email as seller_email,
                SUM(e.net_payout_amount) as total_payout,
                COUNT(e.earning_id) as order_count,
                MIN(e.created_at) as oldest_order
            FROM shop_earnings e
            JOIN shops s ON e.shop_id = s.shop_id
            JOIN users u ON s.user_id = u.user_id
            WHERE e.payout_status = 'PENDING'
            GROUP BY s.shop_id
            ORDER BY total_payout DESC
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        $payouts = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        // DECRYPT SENSITIVE BILLING FIELDS
        foreach ($payouts as &$payout) {
            if (!empty($payout['payout_account_name'])) {
                $payout['payout_account_name'] = EncryptionHelper::decrypt($payout['payout_account_name']);
            }
            if (!empty($payout['payout_account_number'])) {
                $payout['payout_account_number'] = EncryptionHelper::decrypt($payout['payout_account_number']);
            }
        }
        
        return $payouts;
    }

    /**
     * Mark earnings as PAID
     */
    public function markPayoutAsPaid($shopId) {
        // Generate a batch reference ID
        $batchRef = 'PAYOUT-' . date('Ymd') . '-' . strtoupper(uniqid());
        
        $stmt = $this->conn->prepare("
            UPDATE shop_earnings 
            SET payout_status = 'PAID',
                payout_date = CURRENT_TIMESTAMP,
                payout_reference = ?
            WHERE shop_id = ? AND payout_status = 'PENDING'
        ");
        
        $stmt->bind_param("si", $batchRef, $shopId);
        return $stmt->execute();
    }
}