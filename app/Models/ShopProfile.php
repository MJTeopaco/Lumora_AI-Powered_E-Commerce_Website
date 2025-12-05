<?php
// app/Models/ShopProfile.php

namespace App\Models;

use App\Core\Database;

class ShopProfile
{
    protected $conn;
    
    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    /**
     * Get shop profile data by user ID
     */
    public function getShopProfileData($user_id = null)
    {
        if ($user_id === null) {
            // Get from session if available
            if (isset($_SESSION['user_id'])) {
                $user_id = $_SESSION['user_id'];
            } else {
                return null;
            }
        }

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
            WHERE s.user_id = ? AND s.is_deleted = 0
        ");
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $shopData = $result->fetch_assoc();
        $stmt->close();
        
        return $shopData;
    }

    /**
     * Get shop by shop ID
     */
    public function getByShopId($shop_id)
    {
        $stmt = $this->conn->prepare("
            SELECT 
                s.*,
                a.address_line1,
                a.address_line2,
                a.barangay,
                a.city,
                a.province,
                a.region,
                a.postal_code
            FROM shops s
            LEFT JOIN addresses a ON s.address_id = a.address_id
            WHERE s.shop_id = ? AND s.is_deleted = 0
        ");
        
        $stmt->bind_param("i", $shop_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $shopData = $result->fetch_assoc();
        $stmt->close();
        
        return $shopData;
    }

    /**
     * Update shop profile
     */
    public function update($shop_id, $data)
    {
        $fieldsToUpdate = [];
        $params = [];
        $types = "";

        // Handle shop name
        if (isset($data['shop_name'])) {
            $fieldsToUpdate[] = "shop_name = ?";
            $params[] = $data['shop_name'];
            $types .= "s";
        }

        // Handle contact email
        if (isset($data['contact_email'])) {
            $fieldsToUpdate[] = "contact_email = ?";
            $params[] = $data['contact_email'];
            $types .= "s";
        }

        // Handle contact phone
        if (isset($data['contact_phone'])) {
            $fieldsToUpdate[] = "contact_phone = ?";
            $params[] = $data['contact_phone'] ?: null;
            $types .= "s";
        }

        // Handle shop description
        if (isset($data['shop_description'])) {
            $fieldsToUpdate[] = "shop_description = ?";
            $params[] = $data['shop_description'] ?: null;
            $types .= "s";
        }

        // Handle shop banner
        if (isset($data['shop_banner'])) {
            $fieldsToUpdate[] = "shop_banner = ?";
            $params[] = $data['shop_banner'] ?: null;
            $types .= "s";
        }

        // Handle shop profile picture
        if (isset($data['shop_profile'])) {
            $fieldsToUpdate[] = "shop_profile = ?";
            $params[] = $data['shop_profile'] ?: null;
            $types .= "s";
        }

        // Check if there is anything to update
        if (empty($fieldsToUpdate)) {
            return true;
        }

        // Build the query
        $sql = "UPDATE shops SET ";
        $sql .= implode(", ", $fieldsToUpdate);
        $sql .= ", updated_at = NOW() WHERE shop_id = ?";
        
        // Add shop_id to params
        $types .= "i";
        $params[] = $shop_id;

        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            throw new \Exception("Failed to prepare statement: " . $this->conn->error);
        }
        
        $stmt->bind_param($types, ...$params);
        
        $result = $stmt->execute();
        
        if (!$result) {
            throw new \Exception("Failed to execute statement: " . $stmt->error);
        }
        
        $stmt->close();
        
        return $result;
    }

    /**
     * Update shop banner
     */
    public function updateBanner($shop_id, $filename)
    {
        return $this->update($shop_id, ['shop_banner' => $filename]);
    }

    /**
     * Update shop profile picture
     */
    public function updateProfilePicture($shop_id, $filename)
    {
        return $this->update($shop_id, ['shop_profile' => $filename]);
    }

    /**
     * Check if shop name is already taken (excluding current shop)
     */
    public function isShopNameTaken($shop_name, $exclude_shop_id = null)
    {
        if ($exclude_shop_id) {
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) as count FROM shops 
                WHERE shop_name = ? AND shop_id != ? AND is_deleted = 0
            ");
            $stmt->bind_param("si", $shop_name, $exclude_shop_id);
        } else {
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) as count FROM shops 
                WHERE shop_name = ? AND is_deleted = 0
            ");
            $stmt->bind_param("s", $shop_name);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['count'] > 0;
    }

    /**
     * Check if email is already in use (excluding current shop)
     */
    public function isEmailInUse($email, $exclude_shop_id = null)
    {
        if ($exclude_shop_id) {
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) as count FROM shops 
                WHERE contact_email = ? AND shop_id != ? AND is_deleted = 0
            ");
            $stmt->bind_param("si", $email, $exclude_shop_id);
        } else {
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) as count FROM shops 
                WHERE contact_email = ? AND is_deleted = 0
            ");
            $stmt->bind_param("s", $email);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['count'] > 0;
    }

// app/Models/ShopProfile.php (getShopStats function)

    /**
     * Get shop statistics
     */
    public function getShopStats($shop_id)
    {
        $stats = [];

        // 1. Total products (Query is correct, only needs to reference products table)
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as total 
            FROM products 
            WHERE shop_id = ? AND is_deleted = 0
        ");
        $stmt->bind_param("i", $shop_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['total_products'] = $result->fetch_assoc()['total'];
        $stmt->close();

        // 2. Total orders (CORRECTED: Added join to product_variants)
        $stmt = $this->conn->prepare("
            SELECT COUNT(DISTINCT o.order_id) as total
            FROM orders o
            INNER JOIN order_items oi ON o.order_id = oi.order_id
            INNER JOIN product_variants pv ON oi.variant_id = pv.variant_id  
            INNER JOIN products p ON pv.product_id = p.product_id             
            WHERE p.shop_id = ?
        ");
        $stmt->bind_param("i", $shop_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['total_orders'] = $result->fetch_assoc()['total'];
        $stmt->close();

        // 3. Pending orders (CORRECTED: Added join to product_variants)
        $stmt = $this->conn->prepare("
            SELECT COUNT(DISTINCT o.order_id) as total
            FROM orders o
            INNER JOIN order_items oi ON o.order_id = oi.order_id
            INNER JOIN product_variants pv ON oi.variant_id = pv.variant_id  
            INNER JOIN products p ON pv.product_id = p.product_id             
            WHERE p.shop_id = ? AND o.order_status IN ('PENDING_PAYMENT', 'PROCESSING')
        ");
        $stmt->bind_param("i", $shop_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['pending_orders'] = $result->fetch_assoc()['total'];
        $stmt->close();

        // 4. Total revenue (CORRECTED: Added join to product_variants)
        // NOTE: This query references oi.subtotal which is not in the schema. I've updated it to use oi.total_price.
        $stmt = $this->conn->prepare("
            SELECT COALESCE(SUM(oi.total_price), 0) as total  
            FROM order_items oi
            INNER JOIN orders o ON oi.order_id = o.order_id
            INNER JOIN product_variants pv ON oi.variant_id = pv.variant_id  
            INNER JOIN products p ON pv.product_id = p.product_id             
            WHERE p.shop_id = ? AND o.order_status = 'DELIVERED'
        ");
        $stmt->bind_param("i", $shop_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['total_revenue'] = $result->fetch_assoc()['total'];
        $stmt->close();

        return $stats;
    }

    /**
     * Create or update shop address
     */
    public function updateAddress($shop_id, $addressData)
    {
        // Get current address_id
        $stmt = $this->conn->prepare("SELECT address_id FROM shops WHERE shop_id = ?");
        $stmt->bind_param("i", $shop_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $shop = $result->fetch_assoc();
        $stmt->close();

        if ($shop['address_id']) {
            // Update existing address
            $stmt = $this->conn->prepare("
                UPDATE addresses 
                SET address_line_1 = ?, 
                    address_line_2 = ?, 
                    barangay = ?, 
                    city = ?, 
                    province = ?, 
                    region = ?, 
                    postal_code = ?,
                    updated_at = NOW()
                WHERE address_id = ?
            ");
            $stmt->bind_param(
                "sssssssi",
                $addressData['address_line_1'],
                $addressData['address_line_2'],
                $addressData['barangay'],
                $addressData['city'],
                $addressData['province'],
                $addressData['region'],
                $addressData['postal_code'],
                $shop['address_id']
            );
            $stmt->execute();
            $stmt->close();
            
            return $shop['address_id'];
        } else {
            // Create new address
            $user_id = $_SESSION['user_id'] ?? null;
            $address_type = 'shop';
            
            $stmt = $this->conn->prepare("
                INSERT INTO addresses 
                (user_id, address_type, address_line_1, address_line_2, barangay, city, province, region, postal_code, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->bind_param(
                "issssssss",
                $user_id,
                $address_type,
                $addressData['address_line_1'],
                $addressData['address_line_2'],
                $addressData['barangay'],
                $addressData['city'],
                $addressData['province'],
                $addressData['region'],
                $addressData['postal_code']
            );
            $stmt->execute();
            $address_id = $this->conn->insert_id;
            $stmt->close();

            // Update shop with new address_id
            $stmt = $this->conn->prepare("UPDATE shops SET address_id = ? WHERE shop_id = ?");
            $stmt->bind_param("ii", $address_id, $shop_id);
            $stmt->execute();
            $stmt->close();

            return $address_id;
        }
    }
}