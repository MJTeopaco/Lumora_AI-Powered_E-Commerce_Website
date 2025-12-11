<?php

namespace App\Models;
use App\Core\Database;

class Seller {

    protected $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    /**
     * Check seller status for a user
     * Returns: ['status' => 'none'|'pending'|'approved', 'shop_name' => '', 'applied_at' => '']
     */
    public function getSellerStatus($userId) {
        $stmt = $this->conn->prepare("
            SELECT 
                s.shop_name,
                s.created_at as applied_at,
                ur.is_approved
            FROM user_roles ur
            LEFT JOIN shops s ON s.user_id = ur.user_id
            WHERE ur.user_id = ? AND ur.role_id = 2
            LIMIT 1
        ");
        
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $stmt->close();
            
            if ($row['is_approved'] == 1) {
                return [
                    'status' => 'approved',
                    'shop_name' => $row['shop_name'],
                    'applied_at' => $row['applied_at']
                ];
            } else {
                return [
                    'status' => 'pending',
                    'shop_name' => $row['shop_name'],
                    'applied_at' => $row['applied_at']
                ];
            }
        }
        
        $stmt->close();
        return ['status' => 'none'];
    }

    /**
     * Get shop ID by user ID
     */
    public function getShopIdByUserId($userId) {
        $stmt = $this->conn->prepare("SELECT shop_id FROM shops WHERE user_id = ? LIMIT 1");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $stmt->close();
            return $row['shop_id'];
        }
        
        $stmt->close();
        return null;
    }

    /**
     * Get shop details by user ID
     */
    public function getShopByUserId($userId) {
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
            LEFT JOIN addresses a ON a.user_id = s.user_id AND a.address_type = 'shop'
            WHERE s.user_id = ? AND s.is_deleted = 0
            LIMIT 1
        ");
        
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $shop = $result->fetch_assoc();
        $stmt->close();
        
        return $shop;
    }

    /**
     * Save shop registration
     */
    public function saveRegister($userId, $shopName, $contactEmail, $contactPhone, $shopDesc, $slug) {
        // Generate unique slug
        $slug = $this->generateUniqueSlug($shopName);

        $stmt = $this->conn->prepare("
            INSERT INTO shops (user_id, shop_name, contact_email, contact_phone, shop_description, slug)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param("isssss", $userId, $shopName, $contactEmail, $contactPhone, $shopDesc, $slug);
        
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            $stmt->close();
            return false;
        }
    }

    /**
     * Save shop address
     */
    public function saveShopAddress($userId, $addressLine1, $addressLine2, $barangay, $city, $province, $region, $postalCode) {
        $stmt = $this->conn->prepare("
            INSERT INTO addresses (user_id, address_type, address_line_1, address_line_2, barangay, city, province, region, postal_code, is_default)
            VALUES (?, 'shop', ?, ?, ?, ?, ?, ?, ?, 0)
        ");
        
        $stmt->bind_param("isssssss", $userId, $addressLine1, $addressLine2, $barangay, $city, $province, $region, $postalCode);
        
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            $stmt->close();
            return false;
        }
    }

    /**
     * Request seller role approval
     */
    public function requestApproval($userId) {
        // Check if already exists
        $checkStmt = $this->conn->prepare("SELECT COUNT(*) as count FROM user_roles WHERE user_id = ? AND role_id = 2");
        $checkStmt->bind_param("i", $userId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $row = $result->fetch_assoc();
        $checkStmt->close();
        
        if ($row['count'] > 0) {
            return true;
        }
        
        // Insert new approval request
        $stmt = $this->conn->prepare("
            INSERT INTO user_roles (user_id, role_id, is_approved) 
            VALUES (?, 2, 0)
        ");
        $stmt->bind_param("i", $userId);
        
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            $stmt->close();
            return false;
        }
    }

    /**
     * Generate unique slug
     */
    private function generateUniqueSlug($shopName) {
        $baseSlug = strtolower(str_replace(' ', '-', $shopName));
        $slug = $baseSlug;
        $counter = 1;
    
        while ($this->isSlugTaken($slug)) {
            $slug = $baseSlug . '-' . time() . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug is already taken
     */
    private function isSlugTaken($slug) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM shops WHERE slug = ?");
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['count'] > 0;
    }

    /**
     * Transaction methods
     */
    public function beginTransaction() {
        $this->conn->begin_transaction();
    }

    public function commit() {
        $this->conn->commit();
    }

    public function rollback() {
        $this->conn->rollback();
    }
}