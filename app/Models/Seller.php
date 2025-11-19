<?php

namespace App\Models;
use App\Core\Database;
use DateTime;

class Seller {

    protected $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    // Core functions
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




    // Seller Registration Model Functions
    public function saveRegister($userId, $shopName, $contactEmail, $contactPhone, $shopDesc, $slug) {

        $base_slug = strtolower(str_replace(' ', '-', $shopName));
        $slug = $base_slug;
        $counter = 1;
    
        while ($this->isSlugTaken($slug)) {
            $slug = $base_slug . '-' . $counter;
            $counter++;
        }

        $stmt = $this->conn->prepare("
            INSERT INTO shops (user_id, shop_name, contact_email, contact_phone, shop_description, slug)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $slug = strtolower(str_replace(' ', '-', $shopName)) . '-' . time();

        $stmt->bind_param("isssss", $userId, $shopName, $contactEmail, $contactPhone, $shopDesc, $slug);
        
        if($stmt->execute()) {
            //success
            $stmt->close();
            return true;
        } else {
            //error
            $stmt->close();
            return false;
        }
    }

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

    public function requestApproval($userId) {
        $checkStmt = $this->conn->prepare("SELECT COUNT(*) as count FROM user_roles WHERE user_id = ? AND role_id = 2");
        $checkStmt->bind_param("i", $userId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $row = $result->fetch_assoc();
        $checkStmt->close();
        
        if ($row['count'] > 0) {
            return true;
        }
        
        $stmt = $this->conn->prepare("
            INSERT INTO user_roles (user_id, role_id, is_approved) 
            VALUES (?, 2, FALSE)
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

    // Seller Registration Model Helper
    private function isSlugTaken($slug) {
        $stmt = $this->conn->prepare("SELECT COUNT(slug) FROM shops WHERE slug = ?");
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        $count = 0;
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        return $count > 0; 
    }














}



?>